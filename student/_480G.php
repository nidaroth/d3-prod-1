<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/_1098T_Setup.php");

$res_add_on = $db->Execute("SELECT _4807G FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['_4807G'] == 0 || $_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_USER_TYPE'] != 3){
	header("location:../index");
	exit;
}

$report_error="";
if(!empty($_POST)){
	$YEAR_SUFFIX = '';
		if($_REQUEST['CALENDAR_YEAR'] == '2023'){
			$YEAR_SUFFIX = '2023';
		}
	//echo "<pre>";print_r($_POST);exit;
	if($_POST['PK_4807G_EIN']==0)
	{

	$PK_4807G_EIN = "";	
	$res_campus = $db->Execute("SELECT PK_4807G_EIN FROM _4807G_EIN_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	while (!$res_campus->EOF) { 
		if($PK_4807G_EIN != '')
			$PK_4807G_EIN .= ',';			
			$PK_4807G_EIN .= $res_campus->fields['PK_4807G_EIN'];
		$res_campus->MoveNext();
	}

}else{
	$PK_4807G_EIN=$_POST['PK_4807G_EIN'];
}
	
	$timezone = $_SESSION['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0) {
		$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$timezone = $res->fields['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0)
			$timezone = 4;
	}
	
	$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
	$TIMEZONE = $res->fields['TIMEZONE'];

	if($_SESSION['PK_STUDENT_MASTER'] > 0)
		$PK_STUDENT_MASTER = $_SESSION['PK_STUDENT_MASTER'];
	else
		$PK_STUDENT_MASTER = 0;

	if($_POST['FORMAT'] == 1){    // This block for student ledger pdf  DIAM-11
		require_once '../global/mpdf/vendor/autoload.php';
		
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
		$PDF_LOGO 	 = $res->fields['PDF_LOGO'];
		
		$logo = "";
		if($PDF_LOGO != '')
			$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
			
		$header = '<table width="100%" >
						<tr>
							<td width="20%" valign="top" >'.$logo.'</td>
							<td width="40%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
							<td width="40%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>480.7G Ledger Transactions</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Between: 01/01/'.$_POST['CALENDAR_YEAR'].' and 12/31/'.$_POST['CALENDAR_YEAR'].'</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';
					
		
		$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE,date_default_timezone_get());
					
		$footer = '<table width="100%" >
						<tr>
							<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="center" >ACCT4807G'.$YEAR_SUFFIX.'</td>
							<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of [pagetotal]</i></td>
						</tr>
					</table>';
		
		$mpdf = new \Mpdf\Mpdf([
			'margin_left' => 7,
			'margin_right' => 5,
			'margin_top' => 35,
			'margin_bottom' => 15,
			'margin_header' => 3,
			'margin_footer' => 10,
			'default_font_size' => 9,
			'orientation' => 'P'
		]);
		$mpdf->autoPageBreak = true;
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		
		$res = $db->Execute("CALL ACCT4807G$YEAR_SUFFIX(".$_SESSION['PK_ACCOUNT'].",".$_SESSION['PK_USER'].",'".$PK_4807G_EIN."', 'Student 4807G Ledger',$PK_STUDENT_MASTER)");
		if($res->fields['ERROR']){
			$report_error=$res->fields['ERROR'];
		}
		else
		{
			// create generic array for student wise
			$student=array();
			$final_array=array();
			while (!$res->EOF) {
				$student[$res->fields['PK_STUDENT_MASTER']]=$res->fields;
				$final_array[]=$res->fields;
				$res->MoveNext();
			}
			$transaction=array();		
			foreach($student as $k=>$v){
				foreach($final_array as $val){
					if($val['PK_STUDENT_MASTER']==$k)
					{
					 $transaction[$k][]=$val;
					}
				}
			}
			$db->close();
			$db->connect($db_host,'root',$db_pass,$db_name);		
			foreach ($student as $key=>$row) {
				$mpdf->AddPage('','',1);
				
				$mpdf->AliasNbPageGroups('[pagetotal]');
				
				$PK_STUDENT_MASTER 		= $row['PK_STUDENT_MASTER'];
				$PK_STUDENT_ENROLLMENT  = $row['PK_STUDENT_ENROLLMENT'];
				$StudentName  			= $row['STUDENT_NAME'];
				$CODE  					= $row['PROGRAM_CODE'];
				
				$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,CODE, M_CAMPUS_PROGRAM.DESCRIPTION,
				STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, STUDENT_ID, LEAD_SOURCE, FUNDING, 
				IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER,
				IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH, '%m/%d/%Y' )) AS DATE_OF_BIRTH, 
				IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS  EXPECTED_GRAD_DATE,
				IF(GRADE_DATE = '0000-00-00','',DATE_FORMAT(GRADE_DATE, '%m/%d/%Y' )) AS  GRADE_DATE,
				IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%m/%d/%Y' )) AS  LDA,
				IF(DETERMINATION_DATE = '0000-00-00','',DATE_FORMAT(DETERMINATION_DATE, '%m/%d/%Y' )) AS  DETERMINATION_DATE,
				IF(DROP_DATE = '0000-00-00','',DATE_FORMAT(DROP_DATE, '%m/%d/%Y' )) AS  DROP_DATE
				FROM 
				S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT 
				LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
				LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
				LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE 
				LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
				LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS
				WHERE 
				S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
				S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
				PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); // S_STUDENT_ENROLLMENT.IS_ACTIVE_ENROLLMENT = 1 AND

				$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	
				
				$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td width="100%" colspan="4" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" ><b>'.$StudentName.'</b></td>
							</tr>
							<tr>
								<td width="100%" colspan="4" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >Program '.$res_enroll->fields['CODE'].' - '.$res_enroll->fields['DESCRIPTION'].'</td>
							</tr>
							<tr>
								<td width="30%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;" >
									<table border="0" cellspacing="0" cellpadding="3" width="100%">
										<tr>
											<td>Student ID:</td>
											<td>'.$res_enroll->fields['STUDENT_ID'].'</td>
										</tr>
										<tr>
											<td>DOB:</td>
											<td>'.$res_enroll->fields['DATE_OF_BIRTH'].'</td>
										</tr>
										<tr>
											<td>Phone:</td>
											<td>'.$res_address->fields['CELL_PHONE'].'</td>
										</tr>
									</table>
								</td>
								<td width="30%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;" >
									<table border="0" cellspacing="0" cellpadding="3" width="100%">
										<tr>
											<td>Status:</td>
											<td>'.$res_enroll->fields['STUDENT_STATUS'].'</td>
										</tr>
										<tr>
											<td>First Term Date:</td>
											<td>'.$res_enroll->fields['TERM_MASTER'].'</td>
										</tr>
										<tr>
											<td>Exp. Grad Date:</td>
											<td>'.$res_enroll->fields['EXPECTED_GRAD_DATE'].'</td>
										</tr>
									</table>
								</td>
								<td width="30%" style="border-top:1px solid #000;border-left:1px solid #000;border-bottom:1px solid #000;" >
									<table border="0" cellspacing="0" cellpadding="3" width="100%">
										<tr>
											<td>Grad Date:</td>
											<td>'.$res_enroll->fields['GRADE_DATE'].'</td>
										</tr>
										<tr>
											<td>LDA:</td>
											<td>'.$res_enroll->fields['LDA'].'</td>
										</tr>
										<tr>
											<td>Determination Date:</td>
											<td>'.$res_enroll->fields['DETERMINATION_DATE'].'</td>
										</tr>
										<tr>
											<td>Drop Date:</td>
											<td>'.$res_enroll->fields['DROP_DATE'].'</td>
										</tr>
									</table>
								</td>
								<td width="40%" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" >
									<table border="0" cellspacing="0" cellpadding="3" width="100%">
										<tr>
											<td>'.$res_address->fields['ADDRESS'].'</td>
										</tr>
										<tr>
											<td>'.$res_address->fields['ADDRESS_1'].'</td>
										</tr>
										<tr>
											<td>'.$res_address->fields['CITY'].', '.$res_address->fields['STATE_CODE'].' '.$res_address->fields['ZIP'].'<br />'.$res_address->fields['COUNTRY'].'</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					<br /><br />';
					
					$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<thead>
									<tr>
										<td width="10%" style="border-bottom:1px solid #000;">
											<b><i>Trans Date</i></b>
										</td>
										<td width="17%" style="border-bottom:1px solid #000;">
											<b><i>Ledger Code</i></b>
										</td>
										<td width="12%" style="border-bottom:1px solid #000;">
											<b><i>Description</i></b>
										</td>
										<td width="12%" style="border-bottom:1px solid #000;">
											<b><i>Debit</i></b>
										</td>
										<td width="12%" style="border-bottom:1px solid #000;" >
											<b><i>Credit</i></b>
										</td>
										<td width="25%" style="border-bottom:1px solid #000;">
											<b><i>Financial Assistance Received</i></b>
										</td>
									</tr>
								</thead>';
								
				$DEBIT  = 0;
				$CREDIT = 0;
				
				$GRAND_TOTAL  							= 0;
				$A_SCHOLARSHIPS_TOTAL     				= 0;
				$B_GRANTS_AMOUNT  						= 0;
				$C_CONSESSION  							= 0;
				$D_OTHER  								= 0;
				$TUITION_FEES_RELATED_EXPENSES_AMOUNT  	= 0;
				$FINANCIAL_AID_RECEIVED_AMOUNT  		= 0;
				$COST_STUD_AID  						= 0;
				
		
				foreach ($transaction[$PK_STUDENT_MASTER] as $tran =>$t) { 
				
					
					$DEBIT  += $t['DEBIT'];
					$CREDIT += $t['CREDIT'];
					
					$GRAND_TOTAL  			+= $t['GRAND_AMOUNT'];
					$A_SCHOLARSHIPS_TOTAL   += $t['A_SCHOLARSHIP_AMOUNT'];
					$B_GRANTS_AMOUNT  		+= $t['B_GRANTS_AMOUNT'];
					$C_CONSESSION  			+= $t['C_CONSESSION'];
					$D_OTHER  				+= $t['D_OTHER'];
					$TUITION_FEES_RELATED_EXPENSES_AMOUNT  	+= $t['TUITION_FEES_RELATED_EXPENSES_AMOUNT'];
					$FINANCIAL_AID_RECEIVED_AMOUNT  	+= $t['FINANCIAL_AID_RECEIVED_AMOUNT'];
					$COST_STUD_AID          += $t['GRAND_DEBIT'];

					if ($GRAND_TOTAL < 0.0001) {
						$GRAND_TOTAL1 = '0.000';
					}
					else{
						$GRAND_TOTAL1 = $GRAND_TOTAL;
					}
					
					$txt .= '<tr>
								<td>'.($t['TRANSACTION_DATE']?date('m/d/Y',strtotime($t['TRANSACTION_DATE'])):'').'</td>
								<td>'.$t['LEDGER_CODE'].'</td>
								<td>'.$t['Description'].'</td>
								<td >$'.number_format_value_checker($t['DEBIT'],2).'</td>
								<td >$'.number_format_value_checker($t['CREDIT'],2).'</td>
								<td align="center">'.$t['FINANCIAL_AID_RECEIVED_TYPE'].'</td>
							</tr>';
					//$res_1->MoveNext();
					

				}
				
				//exit;
				$txt .= '<tr>
							<td style="border-top:1px solid #000;" ></td>
							<td style="border-top:1px solid #000;" ></td>
							<td style="border-top:1px solid #000;" ></td>
							<td style="border-top:1px solid #000;">$'.number_format_value_checker($DEBIT,2).'</td>
							<td style="border-top:1px solid #000;">$'.number_format_value_checker($CREDIT,2).'</td>
							<td style="border-top:1px solid #000;" ></td>
							<td style="border-top:1px solid #000;" ></td>
						</tr>
					</table>
					<br />
					<br />
					<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td Width="100%" >
								<table border="1" cellspacing="0" cellpadding="3" width="100%">
									<tr>
										<td width="14%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<br /><b>Grand Total</b><br />
											
										</td>
										<td width="14%" align="right" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<br /><b>A-Scholarships Total</b><br />
											<b>Box 2</b>
										</td>
										<td width="14%" align="right" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<br /><b>B-Grants Total</b><br />
											<b>Box 2</b>
										</td>
										<td width="14%" align="right" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<b>C-Concessions Total</b><br />
											<b>Box 2</b>
										</td>
										<td width="14%" align="right" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<b>D-Other Total</b><br />
											<b>Box 2</b>
										</td>
										<td width="14%" align="right" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<br /><b>Amount Paid for Tuition, Fees, and Other Related Expenses Total</b><br />
											<b>Box 4</b>
										</td>
										<td width="14%" align="right" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >
											<b>Amount of Financial Aid Received By the Student Total</b><br />
											<b>Box 5</b>
										</td>
										<td width="14%" align="right" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >
											<b>Cost of Students Covered by Financial Aid Total</b><br />
											<b>Box 6</b>
										</td>
									</tr>
									<tr>
										<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;">$'.number_format_value_checker($GRAND_TOTAL1,2).'</td>
										<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >$'.number_format_value_checker($A_SCHOLARSHIPS_TOTAL,2).'</td>
										<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >$'.number_format_value_checker($B_GRANTS_AMOUNT,2).'</td>
										<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >$'.number_format_value_checker($C_CONSESSION,2).'</td>
										<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >$'.number_format_value_checker($D_OTHER,2).'</td>
										<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >$'.number_format_value_checker($TUITION_FEES_RELATED_EXPENSES_AMOUNT,2).'</td>
										<td align="right" style="border-left:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;" >$'.number_format_value_checker($FINANCIAL_AID_RECEIVED_AMOUNT,2).'</td>
										<td align="right" style="border-left:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;" >$'.number_format_value_checker($COST_STUD_AID).'</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';
				//echo $txt;
				//exit;
				$mpdf->WriteHTML($txt);
				//$res->MoveNext();
			}		
			$mpdf->Output('Student Ledgers.pdf', 'D');
		}
	} else if($_POST['FORMAT'] == 2){ // 4807G Form
		$browser = '';
		if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
			$browser =  "chrome";
		else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
			$browser = "Safari";
		else
			$browser = "firefox";
		require_once('../global/tcpdf/config/lang/eng.php');
		require_once('../global/tcpdf/tcpdf.php');

			
		class MYPDF extends TCPDF {
			public function Header() {
			}
			public function Footer() {
			}
		}

		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(21.5, 15, 9.5);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 10);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 7, '', true);
		
		$YEAR_SUFFIX = '';
		if($_REQUEST['CALENDAR_YEAR'] == '2023'){
			$YEAR_SUFFIX = '2023';
		}
		$res = $db->Execute("CALL ACCT4807G$YEAR_SUFFIX(".$_SESSION['PK_ACCOUNT'].",".$_SESSION['PK_USER'].",'".$PK_4807G_EIN."', 'Student 4807G Form',$PK_STUDENT_MASTER)");
		if($res->fields['ERROR']){
			$report_error=$res->fields['ERROR'];
		}
		else
		{
			while (!$res->EOF) 
			{

				$pdf->AddPage();
				$txt 	= '';
				
				$SSN 		= $res->fields['SSN'];
				$SSN_DE  	= my_decrypt('',$SSN);

				/*$SSN_DE1  	= my_decrypt('',$SSN);			
				if($SSN != ''){
					$len = strlen($SSN_DE1);
					$_4 = $len - 1;
					$_3 = $len - 2;
					$_2 = $len - 3;
					$_1 = $len - 4;
					$SSN_DE = 'XXX-XX-'.$SSN_DE1[$_1].$SSN_DE1[$_2].$SSN_DE1[$_3].$SSN_DE1[$_4];
				}*/
				
				$Final_Result  = $res->fields['FINANCIAL_AID_RECEIVED_TYPE'];

				//$AMEN_DATE         = $res->fields['AMENDED_DATE'];
				//$AMENDED_DATE      = $AMEN_DATE ? date("m/d/Y",strtotime($AMEN_DATE)) : "_____ /_____/_____";
				if ($res->fields['AMENDED_DATE'] != '' && $res->fields['AMENDED_DATE'] != '0000-00-00') {
					$AMENDED_DATE 				= date("m/d/Y",strtotime($AMEN_DATE));
				}
				else{

					$AMENDED_DATE 				= "_____ /_____/_____";
				}

				if ($res->fields['AMENDED'] == '1') {
					$box1_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:15px" />';
				}
				else{
					$box1_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:15px" />';
				}

				if ($res->fields['Financial_Assistance'] == 'Si') {
					$box2_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:15px" />';
				}
				else{
					$box2_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:15px" />';
				}

				if($res->fields['Financial_Assistance'] == 'No'){
					$box22_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:15px" />';
				}
				else{
					$box22_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:15px" />';
				}

				if (strpos($Final_Result, "A-Scholarship") !== false) {
					$box3_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:15px" />';
				}
				else{
					$box3_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:15px" />';
				}

				if(strpos($Final_Result, 'B-Grants') !== false){
					$box4_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:15px" />';
				}
				else{
					$box4_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:15px" />';
				}

				if(strpos($Final_Result, 'C-Consessions') !== false){
					$box5_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:15px" />';
				}
				else{
					$box5_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:15px" />';
				}

				if(strpos($Final_Result, 'D-Other') !== false){
					$box6_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:15px" />';
				}
				else{
					$box6_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:15px" />';
				}

				if($res->fields['AT_LEAST_HALF_DEGREE'] == 'Si'){
					$box7_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:15px" />';
				}
				else{
					$box7_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:15px" />';
				}
				

				$txt .= '<table border="0" cellspacing="0" cellpadding="3" style="font-size:22px;">
							<tr>
								<td align="left" width="15%">
									<b>Formulario</b><br>Form<br>Rev. 08.22
								</td>
								<td style="font-size:38px;" align="left" width="8%">
									<b>480.7G</b>
								</td>
								<td align="center" style="font-size:24px;" width="54%">
									<b>GOBIERNO DE PUERTO RICO -</b> GOVERNMENT OF PUERTO RICO<br>
									<b>Departamento de Hacienda -</b> Department of the Treasury<br>
									<b>DECLARACIÓN INFORMATIVA - CERTIFICACIÓN DE MATRÍCULA PARA EL CRÉDITO DE LA OPORTUNIDAD AMERICANA</b><br>
									INFORMATIVE RETURN - TUITION STATEMENT FOR THE AMERICAN OPPORTUNITY TAX CREDIT
								</td>
								
								<td width="23%" style="border-bottom: 1px solid #000;border-left: 1px solid #000;">
								</td>
							</tr>
							<tr>
								<td align="left" >
									<b>AÑO CONTRIBUTIVO:</b><br>TAXABLE YEAR:
								</td>
								<td align="left" style="font-size:32px;">
									<b>'.$_POST['CALENDAR_YEAR'].'</b>
								</td>
								<td align="center">
									<br><br>
									<table border="0" align="center" cellspacing="0" cellpadding="0">
										<tr align="center">
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											<td width="5%">'.$box1_img.'</td>
											<td width="65%" align="left">&nbsp;<b>Enmendado -</b> Amended: ( '.$AMENDED_DATE.' )</td>
										</tr>
									</table>
								</td>
								<td algin="center" style="border-left: 1px solid #000;">
									<b>Número de Confirmación de Radicación Electrónica Electronic</b> Filing Confirmation Number<br><br>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$res->fields['ELECTRONIC_FILLING_NO'].'
								</td>
							</tr>
						</table>	
						<table border="0">
							<tr>
								<!--left table -->
								<td width="40%">
									<table border="1" cellspacing="0" cellpadding="3" style="font-size:22px;">
										<tr>
											<td colspan="2" align="center" style="background-color:#d1d2d4;font-size:18px;">
												<b>INFORMACIÓN DE LA INSTITUCIÓN -</b> INSTITUTION\'S  INFORMATION 
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<div >
													<b>Número de Identificación Patronal -</b> Employer Identification Number
												</div>
												<div>'.$res->fields['EIN_NO'].'</div>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<div>
													<b>Nombre -</b> Name
												</div>
												<div>'.$res->fields['CONTACT_NAME'].'</div>
											</td>
										</tr>
										<tr>
											<td colspan="2" style="line-height:3px;">
												<div>
													<b>Dirección -</b> Address
												</div>
												<div>
													<p>'.$res->fields['EIN_ADDRESS'].'</p>
													<p>'.$res->fields['EIN_ADDRESS_1'].'</p>
													<p>'.$res->fields['EIN_CITY'].', '.$res->fields['vEIN_STATE'].' '.$res->fields['vEIN_COUNTRY'].'</p>
													<b>Código Postal -</b> Zip Code '.$res->fields['vEIN_ZIP'].'
												</div>
											</td>
										</tr>
										<tr>
											<td width="53%">
												<div>
													<b>Núm. de Teléfono -</b> Telephone No.
												</div>
												<div>'.$res->fields['CONTACT_PHONE'].'</div>
											</td>
											<td width="47%">
												<div>
													<b>Correo Electrónico -</b> E-mail 
												</div>
												<div>'.$res->fields['CONTACT_EMAIL'].'</div>
											</td>
										</tr>
										<tr>
											<td colspan="2" align="center" style="background-color:#d1d2d4;font-size:18px;">
												<b>INFORMACIÓN DEL ESTUDIANTE -</b> STUDENT\'S INFORMATION 
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<div>
													<b>Número de Seguro Social -</b> Social Security Number
												</div>
												<div>'.$SSN_DE.'</div>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<div>
													<b>Nombre -</b> Name
												</div>
												<div>'.$res->fields['STUDENT_NAME_LFM'].'</div>
											</td>
										</tr>
										<tr>
											<td colspan="2" style="line-height:3px;">
												<div>
													<b>Dirección -</b> Address
												</div>
												<div>
													<p>'.$res->fields['ADDRESS'].'</p>
													<p>'.$res->fields['ADDRESS_1'].'</p>
													<p>'.$res->fields['CITY'].', '.$res->fields['STATE_NAME'].' '.$res->fields['COUNTRY'].'</p>
													<b>Código Postal -</b> Zip Code '.$res->fields['POSTAL_CODE'].'
												</div>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<div>
													<b>Número Control -</b> Control Number
												</div>
												<div> </div>
											</td>
										</tr>
										<tr>
											<td colspan="2" style="line-height:4px;">
												<div>
													<b>Número Control Informativa Original -</b> Control No. Original Informative Return
												</div>
												<div> </div>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<div style="font-size:18px;">
													<b>FECHA DE RADICACIÓN: 28 DE FEBRERO, VEA INSTRUCCIONES </b><br>
													FILING DATE: FEBRUARY 28, SEE INSTRUCTIONS
												</div>
											</td>
										</tr>
									</table>
								</td>
								&nbsp;&nbsp;
								<!--End left table -->
								<!--Right table -->
								<td width="60%">
									<table border="1" cellspacing="0" cellpadding="3" style="font-size:22px;">
									  	<tr align="center" style="background-color:#d1d2d4;">
											<td width="70%">
												<b>Conceptos -</b> Concepts 
											</td>
											<td width="30%">
												<b>Información -</b> Information 
											</td>
										</tr>
										<tr>
											<td>
												<b>1. ¿Recibió el estudiante asistencia económica o reembolsos exentos, incluyendo becas, subvenciones o concesiones durante el año?</b><br> Did the student receive financial aid or exempt reimbursements, including scholarships, grants, or awards during the year?
											</td>
											<td>
												&nbsp;&nbsp;<br><br>
												<table border="0" cellspacing="0" cellpadding="0">
													<tr>
														<td width="15%">'.$box2_img.'</td>
														<td width="35%"><b> Sí / Yes</b></td>
														<td width="15%">'.$box22_img.'</td>
														<td><b> No</b></td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td>
												<br><br><br>
												<b>
													2. Tipo de asistencia económica recibida por el estudiante
												</b><br>
												Type of financial aid received by the student
											</td>
											<td>
												<table border="0" cellspacing="0" cellpadding="0" style="font-size:20px;">
													<tr>
														<td width="15%">'.$box3_img.'</td> 
														<td width="85%"><b>A - Becas -</b> Scholarships</td>
													</tr>
													<tr>	
														<td>'.$box4_img.'</td> 
														<td><b>B - Subvenciones -</b> Grants</td>
													</tr>
													<tr>
														<td>'.$box5_img.'</td> 
														<td><b>C - Concesiones -</b> Awards</td><br>
													</tr>
													<tr>
														<td>'.$box6_img.'</td> 
														<td><b>D - Otro -</b> Other _________</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td>
												<b>
													3. Marque aquí si el estudiante estaba completando por lo menos la mitad de los requisitos a tiempo completo del grado o certificación indicado en el Encasillado de Programa conducente a grado o certificación de estudiante
												</b><br>
													Check here if the student was completing at least half of the full-time requirements for the degree or certification indicated in the Program leading to the student\'s degree or certification Box
											</td>
											<td align="center">
											<br><br><br>
												'.$box7_img.'
											</td>
										</tr>
										<tr align="center" style="background-color:#d1d2d4;">
											<td>
												<b>Pago -</b> Payment 
											</td>
											<td>
												<b>Cantidad -</b> Amount
											</td>
										</tr>
										<tr>
											<td>
												<b>
													4. Cantidad total pagada durante el año por concepto de matrícula, cuotas y otros gastos relacionados
												</b><br>
												Total amount paid during the year for tuition, fees and other related expenses
											</td>
											<td>
												$'.$res->fields['TOTAL_AMOUNT_TUTION'].'
											</td>
										</tr>
										<tr>
											<td>
												<b>
													5. Cantidad total de asistencia económica recibida por el estudiante durante el año
												</b><br>
												Total amount of financial aid received by the student during the year
											</td>
											<td>
												$'.$res->fields['TOTAL_AID_RECIVED'].'
											</td>
										</tr>
										<tr>
											<td>
												<b>
													6. Costo de estudio cubierto por la asistencia económica indicada en el Encasillado 5
												</b><br>
												Cost of studies covered by financial aid indicated in Box 5
											</td>
											<td>
												$'.$res->fields['TOTAL_AID_RECIVED'].'
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<div>
													<b>Programa conducente a grado o certificación del estudiante -</b> Program leading to the student\'s degree or certification
												</div>
												<div>'.$res->fields['PROGRAM_NAME'].'</div>
												
											</td>
										</tr>
										<tr>
											<td colspan="2" style="line-height:6px;">
												<div>
													<b>Razones para el Cambio -</b> Reasons for the Change
												</div>
												<div> </div>
											</td>
											
										</tr>
										<tr>
											<td colspan="2">
												<b>Envíe electrónicamente al Departamento de Hacienda. Entregue copia al estudiante. Conserve copia para sus récords.</b><br>Send to Department of the Treasury electronically. Deliver copy to the student. Keep copy for your records.
											</td>
										</tr>
									</table>

								</td>
								<!--End Right table -->
							</tr>
						</table>
						<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
						<table border="0" cellspacing="0" cellpadding="3" style="font-size:32px;">
							<tr>
								<td align="left" >
									<div>
									'.$res->fields['STUDENT_NAME_LFM'].'<br />
									'.$res->fields['ADDRESS'].'<br />'.$res->fields['ADDRESS_1'].'<br />
									'.$res->fields['CITY'].', '.$res->fields['STATE_NAME'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Código Postal -</b> Zip Code '.$res->fields['POSTAL_CODE'].'<br>'.$res->fields['COUNTRY'].'
									</div>
								</td>
								
							</tr>
						</table>
				';

				//echo $txt;exit;
				$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
				
				$res->MoveNext();
			}
			$file_name = '480.7G Forms.pdf';
			/*if($browser == 'Safari')
				$pdf->Output('temp/'.$file_name, 'FD');
			else	
				$pdf->Output($file_name, 'I');*/

			$pdf->Output('temp/'.$file_name, 'FD');
			return $file_name;
	    }
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=MNU_480G ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_480G_TAX_FORM ?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" action="" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<div class="d-flex">
										<div class="col-6 col-sm-6 ">
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<input type="hidden" name="PK_4807G_EIN" id="PK_4807G_EIN" value="0" >
													<select id="CALENDAR_YEAR" name="CALENDAR_YEAR" class="form-control required-entry" onchange="show_btn(this.value)" > 
														<option ></option>
														
														<?php 
														$res_current = $db->Execute("SELECT * FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
														$FN_CALENDAR_YEARS_current	= explode(',' , $res_current->fields['_4807G_CALENDAR_YEARS']);//DIAM-1494 Added CALENDAR YEARS
														foreach ($FN_CALENDAR_YEARS_current as $value) {
															echo "<option value='$value'>$value</option>";
														}
														?> 
													</select>
													<span class="bar"></span> 
													<label for="CALENDAR_YEAR"><?=CALENDAR_YEAR?></label>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-6 col-sm-6 ">
													<span class="bar"></span> 
													<label ><?=VIEW_RELATED_STUDENT_LEDGER?></label>
												</div>
											
												<div class="col-3 col-sm-3 focused">
													<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
												</div>
											</div>
											<br /><br />
											
											<div class="d-flex">
												<div class="col-6 col-sm-6 ">
													<span class="bar"></span> 
													<label ><?=PRINT_480G_FORMS?></label>
												</div>
											
												<div class="col-3 col-sm-3 focused">
													<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
												</div>
											</div>
											<br />
											<div class="col-12 col-sm-12 ">
												<span style="color : red">Does not include the Control Number provided by Departamento de Hacienda.</span>
											</div>
										</div>
										<div class="col-6 col-sm-6 ">
											
										</div>
									</div>
								</div>
								<input type="hidden" name="FORMAT" id="FORMAT" >
							</form>
                        </div>
					</div>
				</div>
				
            </div>
        </div>

        <? require_once("footer.php"); ?>
		<?php if($report_error!="") {?>
		<div class="modal" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1">480.7G Error Reporting</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group" style="color: red;font-size: 15px;">
							<b><?php echo $report_error; ?></b>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn waves-effect waves-light btn-info">Cancel</button>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
    </div>
   
	<? require_once("js.php"); ?>

	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
		//var form1 = new Validation('form1');
		var error= '<?php echo  $report_error; ?>';

		function submit_form(val){
			jQuery(document).ready(function($) {
			var valid = new Validation('form1', {onSubmit:false});
				var result = valid.validate();
				if(result == true){ 
					document.getElementById('FORMAT').value = val
					document.form1.submit();
				}
			});
		}

		jQuery(document).ready(function($) {
			if(error!=""){
				jQuery('#errorModal').modal();
			}
		})
	</script>

</body>

</html>