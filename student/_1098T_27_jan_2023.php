<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/_1098T_Setup.php");

$res_add_on = $db->Execute("SELECT _1098T FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['_1098T'] == 0 || $_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_USER_TYPE'] != 3){
	header("location:../index");
	exit;
}

$report_error="";
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	if($_POST['PK_1098T_EIN']==0)
	{

	$PK_1098T_EIN = "";	
	$res_campus = $db->Execute("SELECT PK_1098T_EIN FROM _1098T_EIN_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	while (!$res_campus->EOF) { 
		if($PK_1098T_EIN != '')
			$PK_1098T_EIN .= ',';			
			$PK_1098T_EIN .= $res_campus->fields['PK_1098T_EIN'];
		$res_campus->MoveNext();
	}

}else{
	$PK_1098T_EIN=$_POST['PK_1098T_EIN'];
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
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>1098-T Ledger Transactions</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Between: 01/01/2022 and 12/31/2022</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>';
					
		
		$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE,date_default_timezone_get());
					
		$footer = '<table width="100%" >
						<tr>
							<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="center" >ACCT10982022</td>
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
		$res = $db->Execute("CALL ACCT10982022(".$_SESSION['PK_ACCOUNT'].",".$_SESSION['PK_USER'].",'".$PK_1098T_EIN."', 'Student 1098T Ledger',$PK_STUDENT_MASTER)");
		if($res->fields['ERROR']){
			$report_error=$res->fields['ERROR'];
		}else{
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
			$CODE  			= $row['PROGRAM_CODE'];
			$WillNotReceive1098T	= $row['WillNotReceive1098T'];
			
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
			S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.IS_ACTIVE_ENROLLMENT = 1 AND
			PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 

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
				
				if($WillNotReceive1098T != ''){
					$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<tr>
									<td width="100%" align="center" ><b style="font-size:20px" >'.$WillNotReceive1098T.'</b></td>
								</tr>
							</table>';
				}
				
				$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<thead>
								<tr>
									<td width="10%" style="border-bottom:1px solid #000;">
										<b><i>Trans Date</i></b>
									</td>
									<td width="17%" style="border-bottom:1px solid #000;">
										<b><i>Ledger Code</i></b>
									</td>
									<td width="17%" style="border-bottom:1px solid #000;">
										<b><i>Description</i></b>
									</td>
									<td width="12%" style="border-bottom:1px solid #000;" align="right">
										<b><i>Debit</i></b>
									</td>
									<td width="12%" style="border-bottom:1px solid #000;" align="right" >
										<b><i>Credit</i></b>
									</td>
									<td width="12%" style="border-bottom:1px solid #000;" align="center">
										<b><i>PYA</i></b>
									</td>
									<td width="20%" style="border-bottom:1px solid #000;">
										<b><i>1098-T Code</i></b>
									</td>
								</tr>
							</thead>';
							
			$DEBIT  = 0;
			$CREDIT = 0;
			
			$GRAND_TOTAL  			= 0;
			$PYA_TOTAL  			= 0;
			$GRANT_TOTAL  			= 0;
			$SCHOLARSHIP_TOTAL  	= 0;
			$INSURER_TOTAL  		= 0;
			$_1098_T_TOTAL  		= 0;
			$NON_QUALIFIED_TOTAL  	= 0;
			
			
	
			foreach ($transaction[$PK_STUDENT_MASTER] as $tran =>$t) { 
			
				
				$DEBIT  += $t['DEBIT'];
				$CREDIT += $t['CREDIT'];
				
				$GRAND_TOTAL  			+= $t['TotalAmt'];
				$PYA_TOTAL  			+= $t['PYAAmt'];
				$GRANT_TOTAL  			+= $t['GrantAmt'];
				$SCHOLARSHIP_TOTAL  	+= $t['ScholarshipAmt'];
				$INSURER_TOTAL  		+= $t['TuitionFromInsurerAmt'];
				$_1098_T_TOTAL  		+= $t['_1098TAmt'];
				$NON_QUALIFIED_TOTAL  	+= $t['NonQualifiedFeeAmt'];

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
							<td align="right" >$'.number_format($t['DEBIT'],2).'</td>
							<td align="right" >$'.number_format($t['CREDIT'],2).'</td>
							<td align="center" >'.(($t['PYAAmt']!="0.00")?'Y':'').'</td>
							<td>'.$t['_1098TCode'].'</td>
						</tr>';
				//$res_1->MoveNext();
				

			}
			
			//exit;
			$txt .= '<tr>
						<td style="border-top:1px solid #000;" ></td>
						<td style="border-top:1px solid #000;" ></td>
						<td style="border-top:1px solid #000;" ></td>
						<td style="border-top:1px solid #000;" align="right" >$'.number_format($DEBIT,2).'</td>
						<td style="border-top:1px solid #000;" align="right" >$'.number_format($CREDIT,2).'</td>
						<td style="border-top:1px solid #000;" ></td>
						<td style="border-top:1px solid #000;" ></td>
					</tr>
				</table>
				<br />
				<br />
				<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<tr>
						<td Width="100%" >
							<table border="0" cellspacing="0" cellpadding="3" width="100%">
								<tr>
									<td width="14%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >
										<br /><u><b>Grand Total</b></u><br />
										$'.number_format($GRAND_TOTAL1,2).'
									</td>
									<td width="14%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >
										<br /><u><b>PYA Total</b></u><br />
										$'.number_format($PYA_TOTAL,2).'
									</td>
									<td width="14%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >
										<br /><u><b>Grant Total</b></u><br />
										$'.number_format($GRANT_TOTAL,2).'
									</td>
									<td width="14%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >
										<u><b>Scholarship Total</b></u><br />
										$'.number_format($SCHOLARSHIP_TOTAL,2).'
									</td>
									<td width="14%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >
										<u><b>Tuition from Insurer Total</b></u><br />
										$'.number_format($INSURER_TOTAL,2).'
									</td>
									<td width="14%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;" >
										<br /><u><b>1098-T Total</b></u><br />
										$'.number_format($_1098_T_TOTAL,2).'
									</td>
									<td width="14%" align="center" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >
										<u><b>Non-Qualified Fee Total</b></u><br />
										$'.number_format($NON_QUALIFIED_TOTAL,2).'
									</td>
								</tr>
								<tr>
									<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;">Box 1</td>
									<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >Box 4</td>
									<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >Box 5</td>
									<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >Box 5</td>
									<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" >Box 10</td>
									<td align="right" style="border-left:1px solid #000;border-bottom:1px solid #000;" ></td>
									<td align="right" style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;" ></td>
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
	} else if($_POST['FORMAT'] == 2){ // 1098T Form
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
		
		$res_setup = $db->Execute("select CORRECTED, CHANGED_REPORTING_METHOD from _1098T_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$res = $db->Execute("CALL ACCT10982022(".$_SESSION['PK_ACCOUNT'].",0,'".$PK_1098T_EIN."', 'Student 1098T Form',$PK_STUDENT_MASTER)");
		if($res->fields['ERROR']){
			$report_error=$res->fields['ERROR'];
		}else{
		while (!$res->EOF) {

			$pdf->AddPage();
			$txt 	= '';
			
			$SSN 		= $res->fields['SSN_Encrypted'];
			$SSN_DE1  	= my_decrypt('',$SSN);			
			if($SSN != ''){
				$len = strlen($SSN_DE1);
				$_4 = $len - 1;
				$_3 = $len - 2;
				$_2 = $len - 3;
				$_1 = $len - 4;
				$SSN_DE = 'XXX-XX-'.$SSN_DE1[$_1].$SSN_DE1[$_2].$SSN_DE1[$_3].$SSN_DE1[$_4];
			}
			
			if($res->fields['CORRECTED'] == 1 || $res_setup->fields['CORRECTED'] == 1)
				$corrected_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:20px" />';
			else
				$corrected_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:20px" />';
				
			if($res->fields['Box3'] == 1 || $res_setup->fields['CHANGED_REPORTING_METHOD'] == 1)
				$box3_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:20px" />';
			else
				$box3_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:20px" />';
				
			if($res->fields['Box7'] == 1)
				$box7_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:20px" />';
			else
				$box7_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:20px" />';
				
			if($res->fields['Box8'] == 1)
				$box8_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:20px" />';
			else
				$box8_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:20px" />';
				
			if($res->fields['Box9'] == 1)
				$box9_img 	= '<img src="../backend_assets/images/box_check_icon.png" style="width:20px" />';
			else
				$box9_img 	= '<img src="../backend_assets/images/blank_check_box_icon.png" style="width:20px" />';
			
			$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
						<tr>
							<td width="40%" align="right" >
								'.$corrected_img.'
							</td>
							<td width="50%" >
								<b style="font-size:35px;line-height:6px" >CORRECTED</b>
							</td>
						</tr>
						<tr>
							<td width="100%" >
								<table border="0" cellspacing="0" cellpadding="0" width="100%" >
									<tr>
										<td width="46%" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="100%" >
														<div style="font-size:22px" >
															FILER\'S name, street address, city or town, state or province, country, ZIP or foreign postal code, and telephone number
														</div>
														
														<div style="font-size:25px;line-height:6px;" >
															&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$res->fields['SCHOOL_NAME'].'<br />
															&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$res->fields['SchoolAddress1'].' '.$res->fields['SchoolAddress2'].'<br />
															&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$res->fields['SchoolCSZ'].'<br />
															&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$res->fields['SchoolPhone'].'
														</div>
													</td>
												</tr>
											</table>
										</td>
										<td width="19%" style="border-top:1px solid #000;border-left:1px solid #000;">
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="15%" >
														<b style="font-size:30px;" >1</b>
													</td>
													<td width="85%" >
														<div style="font-size:22px;" >
															Payments received for<br />
															qualified tuition and<br />
															related expenses
														</div>
													</td>
												</tr>
												<tr>
													<td width="100%" align="right"  style="border-bottom:1px solid #000;" >
														<div style="font-size:25px;" >$'.number_format($res->fields['Box1'],2).'</div>
													</td>
												</tr>
												<tr>
													<td width="15%" >
														<b style="font-size:30px;" >2</b>
													</td>
													<td width="85%" >
														<div style="font-size:25px;" >'.$res->fields['Box2'].'</div>
													</td>
												</tr>
											</table>
										</td>
										<td width="19%" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="100%" align="center" >
														<div style="font-size:25px;" >OMB No. 1545-1574</div>
													</td>
												</tr>
												<tr>
													<td width="100%" align="center" >
														<b style="font-size:80px;line-height:6px" >2022</b>
													</td>
												</tr>
												<tr>
													<td width="100%" align="center" >
														<b style="font-size:40px;" >Form 1098-T</b>
													</td>
												</tr>
											</table>
										</td>
										<td width="17%" align="right" >
											<br /><br /><br />
											<b style="font-size:45px;" >Tuition Statement</b>
										</td>
									</tr>
									<tr>
										<td width="23%" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="100%" >
														<div style="font-size:22px;" >
															FILER\'S employer identification no.
														</div>
													</td>
												</tr>
												<tr>
													<td width="100%" align="center" >
														<div style="font-size:32px;" >'.$res->fields['FEDERAL_ID_NO'].'</div>
													</td>
												</tr>
											</table>
										</td>
										<td width="23%" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="100%" >
														<div style="font-size:22px;" >
															STUDENT\'S TIN
														</div>
													</td>
												</tr>
												<tr>
													<td width="100%" align="center" >
														<div style="font-size:32px;" >'.$SSN_DE.'</div>
													</td>
												</tr>
											</table>
										</td>
										<td width="38%" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="8%" >
														<b style="font-size:30px;" >3</b>
													</td>
													<td width="75%" >
														<div style="font-size:22px;" >
															
														</div>
													</td>
													<td width="15%" align="right" >&nbsp;</td>
												</tr>
											</table>
										</td>
										<td width="17%" align="right" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" rowspan="3" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="100%" >
														<b style="font-size:45px;" >Copy B<br />For Student</b>
														<br /><br />
														<div style="font-size:22px;" >
															This is important<br />
															tax information<br />
															and is being<br />
															furnished to the<br />
															IRS. This form<br />
															must be used to<br />
															complete Form 8863<br />
															to claim education<br />
															credits. Give it to the<br />
															tax preparer or use it to<br />
															prepare the tax return.
														</div>
													</td>
												</tr>
											</table>	
										</td>
									</tr>
									
									<tr>
										<td width="46%" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="100%" >
														<div style="font-size:22px;" >
															STUDENT\'S name
														</div>
													</td>
												</tr>
												<tr>
													<td width="100%" height="23px">
														<div style="font-size:32px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$res->fields['Sort'].'</div>
														
													</td>
												</tr>
												<tr>
													<td width="100%" style="border-top:1px solid #000;" >
														<div style="font-size:22px;" >
															Street address (including apt. no.)
														</div>
													</td>
												</tr>
												<tr>
													<td width="100%" height="23px">
														<div style="font-size:32px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$res->fields['StudentAddress1'].' '.$res->fields['StudentAddress2'].'</div>
														
													</td>
												</tr>
												<tr>
													<td width="100%" style="border-top:1px solid #000;" >
														<div style="font-size:22px;" >
															City or town, state or province, country, and ZIP or foreign postal code
														</div>
													</td>
												</tr>
												<tr>
													<td width="100%" height="23px">
														<div style="font-size:32px;" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$res->fields['StudentCSZ'].'</div>
														
													</td>
												</tr>
											</table>
										</td>
										<td width="38%" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="8%" >
														<b style="font-size:30px;" >4</b>
													</td>
													<td width="42%" >
														<div style="font-size:22px;" >
															Adjustments made for a<br />
															prior year
														</div>
													</td>
													<td width="8%" style="border-left:1px solid #000;" >
														<b style="font-size:30px;" >5</b>
													</td>
													<td width="42%" >
														<div style="font-size:22px;" >
															Scholarships or grants
														</div>
													</td>
												</tr>
												<tr>
													<td width="50%" align="right" style="border-bottom:1px solid #000;" >
														<div style="font-size:25px;" >$'.number_format($res->fields['Box4'],2).'</div>
													</td>
													<td width="50%" align="right" style="border-bottom:1px solid #000;border-left:1px solid #000;" >
														<div style="font-size:25px;" >$'.number_format($res->fields['Box5'],2).'</div>
													</td>
												</tr>
												
												<tr>
													<td width="8%" style="border-left:1px solid #000;" >
														<b style="font-size:30px;" >6</b>
													</td>
													<td width="42%" >
														<table border="0" cellspacing="0" cellpadding="3" width="100%" >
															<tr>
																<td width="100%" >
																	<div style="font-size:22px;" >
																		Adjustments to scholarships<br />
																		or grants for a prior year
																	</div>
																</td>
															</tr>
															<tr>
																<td width="100%" align="right" >
																	<div style="font-size:25px;" >$'.number_format($res->fields['Box6'],2).'</div>
																</td>
															</tr>
														</table>
													</td>
													<td width="8%" style="border-left:1px solid #000;" >
														<b style="font-size:30px;" >7</b>
													</td>
													<td width="42%" >
														<table border="0" cellspacing="0" cellpadding="0" width="100%" >
															<tr>
																<td width="100%" >
																	<div style="font-size:22px;" >
																		Checked if the amount<br />
																		in box 1 includes<br />
																		amounts for an<br />
																		academic period
																	</div>
																</td>
															</tr>
															<tr>
																<td width="60%" >
																	<div style="font-size:22px;" >
																		beginning January-<br />
																		March 2023
																	</div>
																</td>
																<td width="40%" align="right" >'.$box7_img .'&nbsp;</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									
									<tr>
										<td width="23%" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="100%" >
														<div style="font-size:22px;" >
															Service Provider/Acct. No. (see instr.)
														</div>
													</td>
												</tr>
											</table>
										</td>
										<td width="23%" style="border-top:1px solid #000;border-left:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="3" width="100%" >
												<tr>
													<td width="10%" >
														<b style="font-size:30px;" >8</b>
													</td>
													<td width="65%" >
														<div style="font-size:22px;" >
															Checked if at least<br />
															half-time student
														</div>
													</td>
													<td width="25%" align="right" >'.$box8_img .'&nbsp;</td>
												</tr>
											</table>
										</td>
										<td width="19%" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="2" width="100%" >
												<tr>
													<td width="15%" >
														<b style="font-size:30px;" >9</b>
													</td>
													<td width="60%" >
														<div style="font-size:22px;" >
															Checked if a<br />
															graduate student
														</div>
													</td>
													<td width="25%" align="right" >'.$box9_img .'&nbsp;</td>
												</tr>
											</table>
										</td>
										<td width="19%" style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;" >
											<table border="0" cellspacing="0" cellpadding="2" width="100%" >
												<tr>
													<td width="15%" >
														<b style="font-size:30px;" >10</b>
													</td>
													<td width="85%" >
														<div style="font-size:22px;" >
															Ins. contract reimb./refund
														</div>
													</td>
												</tr>
												<tr>
													<td width="100%" align="right" >
														<div style="font-size:25px;" >$'.number_format($res->fields['Box10'],2).'</div>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									
									<tr>
										<td width="23%" style="border-top:1px solid #000;" >
											<b style="font-size:30px;line-height:6px" >Form 1098-T</b>
										</td>
										<td width="23%" style="border-top:1px solid #000;" >
											<div style="line-height:6px" >(keep for your records)</div>
										</td>
										<td width="19%" style="border-top:1px solid #000;" >
											<div style="line-height:6px" >www.irs.gov/Form1098T</div>
										</td>
										<td width="36%" style="border-top:1px solid #000;" align="right" >
											<div style="line-height:6px" >Department of the Treasury - Internal Revenue Service</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						
						<tr>
							<td width="51%" >
								<br /><br /><b style="font-size:35px;line-height:6px" >Instructions for Student</b><br />
								
								<div style="line-height:5px" >
									You, or the person who can claim you as a dependent, may be able to claim
									an education credit on Form 1040 or 1040-SR. This statement has been
									furnished to you by an eligible educational institution in which you are enrolled, or by an insurer who makes reimbursements or refunds of qualified tuition and related expenses to you. This statement is required to support any claim for an education credit. Retain this statement for your records. To see if you qualify for a credit, and for help in calculating the amount of your credit, see Pub. 970, Form 8863, and the Instructions for Form 1040. Also, for more information, go to www.irs.gov/Credits-Deductions/Individuals/Qualified-Ed-Expenses.<br />
									&nbsp;&nbsp;Your institution must include its name, address, and information contact telephone number on this statement. It may also include contact information for a service provider. Although the filer or the service provider may be able to answer certain questions about the statement, do not contact the filer or the service provider for explanations of the requirements for (and how to figure) any education credit that you may claim. <b><br />Student\'s taxpayer identification number (TIN).</b> For your protection, this form may show only the last four digits of your TIN (SSN, ITIN, ATIN, or EIN). However, the issuer has reported your complete TIN to the IRS. <b>Caution:</b> If your TIN is not shown in this box, your school was not able to provide it. Contact your school if you have questions.<br />
									
									<b>Account number.</b> May show an account or other unique number the filer assigned to distinguish your account.<br />
									
									<b>Box 1.</b> Shows the total payments received by an eligible educational institution in 2022 from any source for qualified tuition and related expenses less any reimbursements or refunds made during 2022 that relate to those payments received during 2022.<br />
									
									<b>Box 2.</b> Reserved for future use.<br />
									
									<b>Box 3.</b> Reserved for future use.<br />
									
									<b>Box 4.</b> Shows any adjustment made by an eligible educational institution for a prior year for qualified tuition and related expenses that were reported on a prior year Form 1098-T. This amount may reduce any allowable education credit that you claimed for the prior year (may result in an increase in tax liability for the year of the refund). See "recapture" in the index to Pub. 970 to report a reduction in your education credit or tuition and fees deduction.<br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
									
									'.$res->fields['Sort'].'<br />
									'.$res->fields['StudentAddress1'].' '.$res->fields['StudentAddress2'].'<br />
									'.$res->fields['StudentCSZ'].'
								</div>
							</td>
							<td width="1%" >
							</td>
							<td width="48%" >
								<br />
								<div style="line-height:5px" >
									<b>Box 5.</b> Shows the total of all scholarships or grants administered and processed by the eligible educational institution. The amount of scholarships or grants for the calendar year (including those not reported by the institution) may reduce the amount of the education credit you claim for the year.<br />
									
									<b>TIP:</b> You may be able to increase the combined value of an education credit and certain educational assistance (including Pell Grants) if the student includes some or all of the educational assistance in income in the year it is received. For details, see Pub. 970.<br />
									
									<b>Box 6.</b> Shows adjustments to scholarships or grants for a prior year. This amount may affect the amount of any allowable tuition and fees deduction or education credit that you claimed for the prior year. You may have to file an amended income tax return (Form 1040-X) for the prior year.<br />
									
									<b>Box 7.</b> Shows whether the amount in box 1 includes amounts for an academic period beginning January–March 2023. See Pub. 970 for how to report these amounts.<br />
									
									<b>Box 8.</b> Shows whether you are considered to be carrying at least one-half the normal full-time workload for your course of study at the reporting institution.<br />
									
									<b>Box 9.</b> Shows whether you are considered to be enrolled in a program leading to a graduate degree, graduate-level certificate, or other recognized graduate-level educational credential.<br />
									
									<b>Box 10.</b> Shows the total amount of reimbursements or refunds of qualified tuition and related expenses made by an insurer. The amount of reimbursements or refunds for the calendar year may reduce the amount of any education credit you can claim for the year (may result in an increase in tax liability for the year of the refund).<br />
									
									<b>Future developments.</b> For the latest information about developments related to Form 1098-T and its instructions, such as legislation enacted after they were published, go to www.irs.gov/Form1098T..<br />
									
									<b>FreeFile.</b> Go to www.irs.gov/FreeFile to see if you qualify for no-cost online federal tax preparation, e-filing, and direct deposit or payment options.
								</div>
							</td>
						</tr>
					</table>';

				//echo $txt;exit;
			$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
			
			$res->MoveNext();
		}
		$file_name = '1098T Forms.pdf';
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
	<title><?=MNU_1098T ?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><?=MNU_1098T ?></h4>
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
													<input type="hidden" name="PK_1098T_EIN" id="PK_1098T_EIN" value="0" >
													<select id="CALENDAR_YEAR" name="CALENDAR_YEAR" class="form-control required-entry" onchange="show_btn(this.value)" >
														<option ></option>
														<option value="2022" >2022</option>
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
													<label ><?=PRINT_1098T_FORM?></label>
												</div>
											
												<div class="col-3 col-sm-3 focused">
													<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
												</div>
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
						<h4 class="modal-title" id="exampleModalLabel1">1098T Error Reporting</h4>
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