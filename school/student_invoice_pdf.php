<?php session_start();
$browser = '';
if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
	$browser =  "chrome";
else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
	$browser = "Safari";
else
	$browser = "firefox";
require_once('../global/tcpdf/config/lang/eng.php');
require_once('../global/tcpdf/tcpdf.php');
require_once('../global/config.php');

require_once("check_access.php");

if(check_access('REPORT_ACCOUNTING') == 0 && check_access('ACCOUNTING_ACCESS') == 0){
	header("location:../index");
	exit;
}

$stud_id = "";
	foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT) {
		if($stud_id != '')
			$stud_id .= ',';
		$stud_id .= $_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT];
	}
//DIAM-1438
if(!empty($stud_id))	
	$_GET['id']	= $stud_id;



require_once("pdf_custom_header.php"); //Ticket # 1588
class MYPDF extends TCPDF {
	/** DIAM-1438 **/
	public $campus;

	public function setCampus($var){
		$this->campus = $var;
	}
	/** DIAM-1438 **/
	public function Header() {
		global $db;
		
		if($_SESSION['temp_id'] == $this->PK_STUDENT_MASTER){
			$this->SetFont('helvetica', 'I', 15);
			$this->SetY(8);
			$this->SetX(10);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(75, 8, $this->STUD_NAME , 0, false, 'L', 0, '', 0, false, 'M', 'L');
			$this->SetMargins('', 25, '');
			
		} else {
			$CONTENT = pdf_custom_header($this->PK_STUDENT_MASTER, $this->PK_STUDENT_ENROLLMENT, 1);
			$this->MultiCell(150, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);
			$this->SetMargins('', 45, '');
			
			$_SESSION['temp_id'] = $this->PK_STUDENT_MASTER;
		}
		
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(153);
		$this->Cell(55, 8, "Student Invoice", 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(130, 13, 202, 13, $style);
		
		$str = "";
		if($_GET['st'] != '' && $_GET['et'] != '')
			$str = " Between ".$_GET['st'].' and '.$_GET['et'];
		else if($_GET['st'] != '')
			$str = " From ".$_GET['st'];
		else if($_GET['et'] != '')
			$str = " To ".$_GET['et'];
			
		$this->SetFont('helvetica', 'I', 10);
		$this->SetY(16);
		$this->SetX(100);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
		$this->SetFont('helvetica', 'I', 10);
		$this->SetY(22);
		$this->SetX(100);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(102, 5, 'Printed: '.date("m/d/Y"), 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
		$campus_cond  = "";
		$campus_cond1 = "";
		$campus_id	  = "";
		if(!empty($_GET['campus'])){
			$PK_CAMPUS 	  = $_GET['campus'];
			$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
			$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
		}
	}
	public function Footer() {
		global $db;
		
		$this->SetY(-28);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);
		// DIAM-1438
		$PK_CAMPUS = $this->campus;

		$res_type = $db->Execute("SELECT FOOTER_LOC, CONTENT FROM S_PDF_FOOTER,S_PDF_FOOTER_CAMPUS WHERE S_PDF_FOOTER.ACTIVE = 1 AND S_PDF_FOOTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 8 AND S_PDF_FOOTER.PK_PDF_FOOTER = S_PDF_FOOTER_CAMPUS.PK_PDF_FOOTER AND PK_CAMPUS = '$PK_CAMPUS'  ");
		// DIAM-1438
		//$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 8");
		
		$BASE = -10 - $res_type->fields['FOOTER_LOC'];
		$this->SetY($BASE);
		$this->SetX(10);
		$this->SetFont('helvetica', '', 7);
		
		// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
		$CONTENT = nl2br($res_type->fields['CONTENT']);
		$this->MultiCell(190, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true); //Ticket # 1234 
		
		$this->SetY(-15);
		$this->SetX(180);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page '.$this->getPageNumGroupAlias().' of '.$this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
		$this->SetY(-15);
		$this->SetX(10);
		$this->SetFont('helvetica', 'I', 7);
		
		$timezone = $_SESSION['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0) {
			$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$timezone = $res->fields['PK_TIMEZONE'];
			if($timezone == '' || $timezone == 0)
				$timezone = 4;
		}
		
		$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
		$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
			
		$this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}

$_SESSION['temp_id'] = '';
function student_invoice($PK_STUDENT_MASTERS, $one_stud_per_pdf)
{
	global $db,$http_path;
	
	$PK_STUDENT_MASTER_ARR = explode(",",$PK_STUDENT_MASTERS);
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(7, 23, 7);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	
	/* Ticket # 1217, 1234 */
	$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 8");
	$BREAK_VAL = 30 + $res_type->fields['FOOTER_LOC'];
	$pdf->SetAutoPageBreak(TRUE, $BREAK_VAL);
	/* Ticket # 1217, 1234 */
	
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setLanguageArray($l);
	$pdf->setFontSubsetting(true);
	$pdf->SetFont('helvetica', '', 9, '', true);
	$check_empty="";
	$i=0;
	// DIAM-1438
	$cnt=1;
	// DIAM-1438
	foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {
		//if ($i++ > 10) continue;

		$file_name = '';
		//echo "SELECT  S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER ";
		//exit;
		$res = $db->Execute("SELECT  S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER "); 
		
		if($res->RecordCount() == 0){
			header("location:student_invoice?m=1");
			exit;
		}
		
		$flag = 1;
		$cond 		= "";
		$FROM_DATE 	= $_GET['st'];
		$TO_DATE 	= $_GET['et'];
		if($FROM_DATE != '' && $TO_DATE != ''){
			$FROM_DATE 	= date('Y-m-d',strtotime($FROM_DATE));
			$TO_DATE 	= date('Y-m-d',strtotime($TO_DATE));
			
			$cond .= " AND DISBURSEMENT_DATE BETWEEN '$FROM_DATE' AND '$TO_DATE' ";
		} else if($FROM_DATE != ''){
			$FROM_DATE 	= date('Y-m-d',strtotime($FROM_DATE));
			$cond .= " AND DISBURSEMENT_DATE >= '$FROM_DATE' ";
		} else if($TO_DATE != ''){
			$TO_DATE 	= date('Y-m-d',strtotime($TO_DATE));
			$cond .= " AND DISBURSEMENT_DATE <= '$TO_DATE' ";
		}

		// Begin store query in table
		$sql_query="select S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT, DISBURSEMENT_AMOUNT, IF(DISBURSEMENT_DATE != '0000-00-00', DATE_FORMAT(DISBURSEMENT_DATE,'%m/%d/%Y'),'') AS  DISBURSEMENT_DATE_1, CODE, INVOICE_DESCRIPTION from S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE WHERE S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_PAYMENT_BATCH_DETAIL = 0 AND PK_DISBURSEMENT_STATUS IN (2) AND INVOICE = 1  AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE $cond ORDER BY DISBURSEMENT_DATE ASC";
		$log_array=array('SQL_QUERY'=>$sql_query,'PK_ACCOUNT'=>$_SESSION['PK_ACCOUNT'],'PK_USER'=>$_SESSION['PK_USER'],'REPORT_NAME'=>'Student Invoice');
		log_query($log_array);
		// End store query in table
		$res_ledger = $db->Execute("select S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT, DISBURSEMENT_AMOUNT, IF(DISBURSEMENT_DATE != '0000-00-00', DATE_FORMAT(DISBURSEMENT_DATE,'%m/%d/%Y'),'') AS  DISBURSEMENT_DATE_1, CODE, INVOICE_DESCRIPTION from S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE WHERE S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_PAYMENT_BATCH_DETAIL = 0 AND PK_DISBURSEMENT_STATUS IN (2) AND INVOICE = 1  AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE $cond ORDER BY DISBURSEMENT_DATE ASC");


		// Begin store query in table
		$sql_query="select SUM(DISBURSEMENT_AMOUNT) as DISBURSEMENT_AMOUNT from S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE WHERE S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_PAYMENT_BATCH_DETAIL = 0 AND PK_DISBURSEMENT_STATUS IN (2) AND INVOICE = 1  AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE ";
		$log_array=array('SQL_QUERY'=>$sql_query,'PK_ACCOUNT'=>$_SESSION['PK_ACCOUNT'],'PK_USER'=>$_SESSION['PK_USER'],'REPORT_NAME'=>'Student Invoice');
		log_query($log_array);
		// End store query in table

		$res_ledger1 = $db->Execute("select SUM(DISBURSEMENT_AMOUNT) as DISBURSEMENT_AMOUNT from S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE WHERE S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_PAYMENT_BATCH_DETAIL = 0 AND PK_DISBURSEMENT_STATUS IN (2) AND INVOICE = 1  AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE ");
		
		if($_GET['exclude_no_due'] == 1 && $res_ledger->RecordCount() == 0) {
			$flag = 0;
		}


		if($flag == 1) {
			$DATE_OF_BIRTH = $res->fields['DATE_OF_BIRTH'];

			if($DATE_OF_BIRTH != '0000-00-00')
				$DATE_OF_BIRTH = date("m/d/Y",strtotime($DATE_OF_BIRTH));
			else
				$DATE_OF_BIRTH = '';
				
			$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	

			$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,CODE,M_CAMPUS_PROGRAM.UNITS, M_CAMPUS_PROGRAM.HOURS, M_CAMPUS_PROGRAM.DESCRIPTION,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_ACTIVE_ENROLLMENT = '1'  "); 
			
			$PK_STUDENT_ENROLLMENT1 = $res_enroll->fields['PK_STUDENT_ENROLLMENT'];
			$res_campus = $db->Execute("select OFFICIAL_CAMPUS_NAME from S_STUDENT_CAMPUS, S_CAMPUS WHERE  S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS  AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT1'");

			$EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
			if($EXPECTED_GRAD_DATE != '0000-00-00')
				$EXPECTED_GRAD_DATE = date("m/d/Y",strtotime($EXPECTED_GRAD_DATE));
			else
				$EXPECTED_GRAD_DATE = '';
				
			$pdf->STUD_NAME 			= $res->fields['LAST_NAME'].", ".$res->fields['FIRST_NAME']." ".$res->fields['MIDDLE_NAME'];
			$pdf->PK_STUDENT_MASTER 	= $PK_STUDENT_MASTER;
			$pdf->PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT1;
			$pdf->startPageGroup();
			$pdf->AddPage();

			/** DIAM-1438 **/
			//if($_GET['current_enrol'] == ''){				
				$res_std_enroll_id = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY IS_ACTIVE_ENROLLMENT DESC LIMIT 1 "); 
				$_GET['current_enrol'] = $res_std_enroll_id->fields['PK_STUDENT_ENROLLMENT'];
				
			//}
			$res_camp = $db->Execute("select S_STUDENT_CAMPUS.PK_CAMPUS,CAMPUS_CODE FROM S_STUDENT_CAMPUS LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS WHERE S_STUDENT_CAMPUS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = $_GET[current_enrol] ");
			$pdf->setCampus($res_camp->fields['PK_CAMPUS']);
			$PK_CAMPUS_PDF =$res_camp->fields['PK_CAMPUS'];
			$CAMPUS_CODE =$res_camp->fields['CAMPUS_CODE'];

			$res_type = $db->Execute("SELECT FOOTER_LOC, CONTENT FROM S_PDF_FOOTER,S_PDF_FOOTER_CAMPUS WHERE S_PDF_FOOTER.ACTIVE = 1 AND S_PDF_FOOTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 8 AND S_PDF_FOOTER.PK_PDF_FOOTER = S_PDF_FOOTER_CAMPUS.PK_PDF_FOOTER AND PK_CAMPUS = '$PK_CAMPUS_PDF'  ");
			$BASE = 20 + $res_type->fields['FOOTER_LOC'];
			$margin_bottom=$BASE."mm";
			$txtfoot .='<span id="div_text_'.$cnt.'" style="display:none;">'.$res_type->fields['CONTENT'].'</span>';
			/** DIAM-1438 **/
			// DIAM-1438
			$txt ='';
			if($one_stud_per_pdf == 0)
				$txt .= '<div style="display:block; clear:both; page-break-after:always;" class="breakpage"></div>';
			// DIAM-1438

			$txt .= '<div style="border-bottom:3px solid #000; font-family:helvetica;font-size:16px;" ></div>
					<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td style="width:50%" >
								'.trim($res->fields['LAST_NAME']).', '.$res->fields['FIRST_NAME'].' '.$res->fields['MIDDLE_NAME'].'<br />'.$res_address->fields['ADDRESS'].' '.$res_address->fields['ADDRESS_1'].'<br />'.$res_address->fields['CITY'].', '.$res_address->fields['STATE_CODE'].' '.$res_address->fields['ZIP'].'<br />'.$res_address->fields['COUNTRY'].'
							</td>
							<td style="width:50%" >
								<table border="0" cellspacing="0" cellpadding="3" width="100%">
									<tr>
										<td style="width:40%" >ID</td>
										<td style="width:60%" >'.$res->fields['STUDENT_ID'].'</td>
									</tr>
									<tr>
										<td style="width:40%" >Status</td>
										<td style="width:60%" >'.$res_enroll->fields['STUDENT_STATUS'].'</td>
									</tr>
									<tr>
										<td style="width:40%" >Program</td>
										<td style="width:60%" >'.$res_enroll->fields['CODE'].' - '.$res_enroll->fields['DESCRIPTION'].'</td>
									</tr>
									<tr>
										<td style="width:40%" >Program Hours</td>
										<td style="width:60%" >'.$res_enroll->fields['HOURS'].'</td>
									</tr>
									<tr>
										<td style="width:40%" >Program Units</td>
										<td style="width:60%" >'.$res_enroll->fields['UNITS'].'</td>
									</tr>
									<tr>
										<td style="width:40%" >First Term</td>
										<td style="width:60%" >'.$res_enroll->fields['TERM_MASTER'].'</td>
									</tr>
									<tr>
										<td style="width:40%" >Expected Grad</td>
										<td style="width:60%" >'.$EXPECTED_GRAD_DATE.'</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					<div style="border-top:3px solid #000" ></div>
					
					<br /><br />
					<table border="0" cellspacing="0" cellpadding="2" width="75%" style="margin-bottom:353px;">
						<thead>
							<tr>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b>Date</b>
								</td>
								<td width="40%" style="border-bottom:1px solid #000;">
									<b>Description</b>
								</td>
								<td width="20%" align="right" style="border-bottom:1px solid #000;" >
									<b>Amount</b>
								</td>
							</tr>
						</thead>
						<tbody>';
				
					$total = 0;
					while (!$res_ledger->EOF) {
						$total += $res_ledger->fields['DISBURSEMENT_AMOUNT'];
						$txt .= '<tr nobr="true" >
									<td width="10%" >
										'.$res_ledger->fields['DISBURSEMENT_DATE_1'].'
									</td>
									<td width="40%" >
										'.$res_ledger->fields['CODE'].' - '.$res_ledger->fields['INVOICE_DESCRIPTION'].'
									</td>
									<td width="20%" align="right" >
										$ '.number_format_value_checker($res_ledger->fields['DISBURSEMENT_AMOUNT'],2).'
									</td>
								</tr>';
						$res_ledger->MoveNext();
					}
					
					$txt .= '<tr>
								<td style="border-top:1px solid #000;" width="10%"></td>
								<td width="40%" style="border-top:1px solid #000;" align="right" ><b>Current Balance Due</b></td>
								<td width="20%" style="border-top:1px solid #000;" align="right"><b>$ '.number_format_value_checker($total,2).'</b></td>
							</tr>
							<tr>
								<td style="border-top:1px solid #000;" width="10%"></td>
								<td width="40%" style="border-top:1px solid #000;" align="right" ><b>Total Balance Due</b></td>
								<td width="20%" style="border-top:1px solid #000;" align="right"><b>$ '.number_format_value_checker($res_ledger1->fields['DISBURSEMENT_AMOUNT'],2).'</b></td>
							</tr>
						</tbody>
					</table>';		
					$cnt++;				
				$check_empty.=$txt;
			$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		}
	}
	if($one_stud_per_pdf == 0 ) {
		$file_name  = 'Student_Invoice_'.uniqid().'.pdf';
		//$pdf->Output('temp/'.$file_name, 'FD');

		//10 may 23

		$header = invoice_pdf_custom_header($PK_STUDENT_MASTER, $PK_STUDENT_ENROLLMENT1, 1);	

		$timezone = $_SESSION['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0) {
			$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$timezone = $res->fields['PK_TIMEZONE'];
			if($timezone == '' || $timezone == 0)
				$timezone = 4;
		}
		
		$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
		$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());


		$res_type = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 8");
		$CONTENT = strip_tags(nl2br($res_type->fields['CONTENT']));
 
		$footer_txt = '<div style="font-size:12px; padding:5px; width:88%; position:relative;">'.$CONTENT.'</div>';

		$footer = $txtfoot.'	
		<table width="100%" border="0">
								<tr>
								<td valign="top" colspan="3" style="font-size:10px;"><span id="footer_content"></span></td>
								<td valign="top" style="font-size:10px;" >&nbsp;</td>
								<td valign="top" style="font-size:10px;"  >&nbsp;</td>											
							</tr>
							<tr>
								<td  valign="top" style="font-size:10px" ><i>'.$date.'</i></td>
								<td  valign="top" style="font-size:10px;" align="right" colspan="2" >Page <span id="page"></span> of 
								<span id="topage"></span></td>
								<td  valign="top" style="font-size:10px;" ></td>											
							</tr>
						</table>';	

			$header_cont= '<!DOCTYPE HTML>
			<html>
			<head>
			<style>
			div{ padding-bottom:20px !important; }	
			</style>
			</head>
			<body>
			<div> '.$header.' </div>
			</body>
			</html>';
			$html_body_cont = '<!DOCTYPE HTML>
			<html>
			<head> <style>
			body{ font-size:15px;}				
			table tr{  padding-top: 15px !important; }
			</style>
			</head>
			<body>'.$check_empty.'</body></html>';
			$footer_cont= '<!DOCTYPE HTML><html><head><style>
			tbody td{ font-size:14px !important; }
			</style></head><body><span></span>'.$footer.'
			  
			<script> 
			  var d=1;
			  var vars={};
			  var x=window.location.search.substring(1).split("&");
			  for (var i in x) {
				var z=x[i].split("=",2);
				vars[z[0]] = unescape(z[1]);
			  }
			 
			  document.getElementById("page").innerHTML = vars.page; 
			  document.getElementById("topage").innerHTML = vars.topage; 

			  if(document.getElementById("div_text_"+vars.page)){
			  	document.getElementById("footer_content").innerHTML = document.getElementById("div_text_"+vars.page).innerHTML;
			  }else{
				var pdd = vars.topage - vars.page;
				if(pdd==0){
					pdd=1;
					document.getElementById("footer_content").innerHTML = document.getElementById("div_text_"+pdd).innerHTML;
				}
			  }
			</script> </body></html>'; //DIAM-1438

		$header_path= create_html_file('header.html',$header_cont,"invoice");
		$content_path=create_html_file('content.html',$html_body_cont,"invoice");
		$footer_path= create_html_file('footer.html',$footer_cont,"invoice");
		
	
		$exec = 'xvfb-run -a wkhtmltopdf -T 0 -R 0 -B 0 -L 0 --enable-local-file-access --orientation portrait --page-size A4 --page-width 210  --page-height 296   --margin-top 50mm --margin-left 7mm --margin-right 5mm  --margin-bottom '.$margin_bottom.' --footer-font-size 8 --header-html '.$header_path.' --footer-html  '.$footer_path.' '.$content_path.' ../school/temp/invoice/'.$file_name.' 2>&1';
			
		//if return value
		$pdfdata =array('filepath'=>'temp/invoice/'.$file_name,'exec'=>$exec,'filename'=>$file_name,'filefullpath'=>$http_path.'school/temp/invoice/'.$file_name);		
		
		sleep(2);
		exec($pdfdata['exec']);	
		header('Content-Type: Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename="' . basename($pdfdata['filefullpath']) . '"');
		//header('Content-Length: ' . $pdfdata['filefullpath']);
		readfile($pdfdata['filepath']);
		exit;		
		
		//10 may 23
		/////////////////////////////////////////
	} else {
		if($flag == 1) {
			//$file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/';
			$file_dir_1 = '../backend_assets/tmp_upload/';
			$file_name  = $res->fields['LAST_NAME'].' '.$res->fields['FIRST_NAME'].'_'.$PK_STUDENT_MASTER.'.pdf';
			$pdf->Output($file_dir_1.$file_name, 'F');
		}

		return $file_name;	
	}
	
	
	
}

// 18 may 23
function invoice_pdf_custom_header($PK_STUDENT_MASTER, $PK_STUDENT_ENROLLMENT, $type){
	global $db;
	global $http_path;
	
	$res = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ,SCHOOL_HEADER_OPTION, LOGO_OPTION FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM_REPORT_HEADER ON S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = M_CAMPUS_PROGRAM_REPORT_HEADER.PK_CAMPUS_PROGRAM  WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND IS_ACTIVE_ENROLLMENT = 1");
	$PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
	if($res->fields['SCHOOL_HEADER_OPTION'] == 2){
		//campus
		$res_pdf_header = $db->Execute("SELECT OFFICIAL_CAMPUS_NAME as NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, FAX, CAMPUS_WEBSITE as WEBSITE FROM S_STUDENT_CAMPUS, S_CAMPUS LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_CAMPUS.PK_STATES WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_ENROLLMENT > 0");
		
	} else {
		//school
		$res_pdf_header = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME as NAME, IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ADDRESS) as ADDRESS,
		IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ADDRESS_1) as ADDRESS_1,
		IF(
        HIDE_ACCOUNT_ADDRESS_ON_REPORTS = '1',
        '',
        IF(CITY!='',CONCAT(CITY, ','),'')
   		 ) AS CITY,
		IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',STATE_CODE) as STATE_CODE,
		IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',ZIP) as ZIP,
		IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',PHONE) as PHONE, 
		IF(HIDE_ACCOUNT_ADDRESS_ON_REPORTS='1','',WEBSITE) as WEBSITE,HIDE_ACCOUNT_ADDRESS_ON_REPORTS FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); //DIAM-1421
	}
	
	if($res->fields['LOGO_OPTION'] == 2){
		//campus
		$res_pdf_header_logo = $db->Execute("SELECT CAMPUS_PDF_LOGO as LOGO FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_ENROLLMENT > 0");
		
	} else {
		//school
		$res_pdf_header_logo = $db->Execute("SELECT PDF_LOGO as LOGO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
	
	if($type == 1) {
		$logo_width	  = 35;
		$logo_height  = "100px";
		$school_font  = "25px";
		$address_font = "35px";
	} else if($type == 2) {
		$logo_width	  = 45;
		$logo_height = "100px";
		$school_font = "30px";
		$address_font = "30px";
	} 
	
	$address_width = 100 - $logo_width;


	$str = "";
	if($_GET['st'] != '' && $_GET['et'] != '')
		$str = " Between ".$_GET['st'].' and '.$_GET['et'];
	else if($_GET['st'] != '')
		$str = " From ".$_GET['st'];
	else if($_GET['et'] != '')
		$str = " To ".$_GET['et'];
			

	
	$LOGO = '';
	if($res_pdf_header_logo->fields['LOGO'] != ''){

		$PDF_LOGO=str_replace('../',$http_path,$res_pdf_header_logo->fields['LOGO']);

		$LOGO = '<img src="'.$PDF_LOGO.'" style="height:'.$logo_height.'" />';
	}
	$str1="";
	if(!empty($str)){ $str1=$str.'<br><br>'; }
		
	$CONTENT = '<table border="0" cellspacing="0" cellpadding="3" width="100%" >
					<tr>
						<td colspan="" style="font-size:'.$school_font.';width:35%" ><b>'.$res_pdf_header->fields['NAME'].'</b></td>
						<td style="width:40%" >&nbsp;</td>
						<td style="width:25%;font-size:30px;font-family:helvetica;font-style: italic;border-bottom:1px solid #000;"><span>Student Invoice</span></td>
						
					</tr>
					<tr>
						<td style="width:35%" >'.$LOGO.'</td>
						<td style="width:40%" >
							<span style="line-height:5px;'.$address_font.'" >'.$res_pdf_header->fields['ADDRESS'].' '.$res_pdf_header->fields['ADDRESS_1'].'<br />'.$res_pdf_header->fields['CITY'].' '.$res_pdf_header->fields['STATE_CODE'].' '.$res_pdf_header->fields['ZIP'].'<br /><br />'.$res_pdf_header->fields['PHONE'].'<br /><br />'.$res_pdf_header->fields['WEBSITE'].'</span>
						</td>
						<td style="style="width:25%;font-style: italic;font-family:helvetica;font-style: italic;">
							<span>
							'.$str1.'Printed: '.date("m/d/Y").'<br>
							</span>
						</td>
					</tr>
				</table>';//DIAM-1421
				
	return  $CONTENT; 
}

//18 may 23

if($_GET['id'] == '') {	

	if(isset($_SESSION['PK_STUDENT_MASTER']))
		student_invoice($_SESSION['PK_STUDENT_MASTER'],0);
	else
		student_invoice($_GET['id'],0);

} else {
	if($_GET['t'] == 2) {
		function unlinkRecursive($dir, $deleteRootToo){
			if(!$dh = @opendir($dir)){
				return;
			}
			while (false !== ($obj = readdir($dh))){
				if($obj == '.' || $obj == '..'){
					continue;
				}
				if (!@unlink($dir . '/' . $obj)){
					unlinkRecursive($dir.'/'.$obj, true);
				}
			}
			closedir($dh);
			if ($deleteRootToo){
				@rmdir($dir);
			}
			return;
		}
		
		class FlxZipArchive extends ZipArchive {
			public function addDir($location, $name) {
				$this->addEmptyDir($name);
				$this->addDirDo($location, $name);
			} 
			private function addDirDo($location, $name) {
				$name .= '/';
				$location .= '/';
				$dir = opendir ($location);
				while ($file = readdir($dir)){
					if ($file == '.' || $file == '..') 
						continue;
					$do = (filetype( $location . $file) == 'dir') ? 'addDir' : 'addFile';
					$this->$do($location . $file, $name . $file);
				}
			}
		}
		
		//$folder = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/student_invoice';
		$folder = '../backend_assets/tmp_upload/student_invoice';
		$zip_file_name  = $folder.'.zip';
		if($folder != '') {
			unlinkRecursive("$folder/",0);
			unlink($zip_file_name);
			@rmdir($folder);
		}
		mkdir($folder);
		
		$has_records = 0;
		$za = new FlxZipArchive;
		$res = $za->open($zip_file_name, ZipArchive::CREATE);
		if($res === TRUE) {
			$PK_STUDENT_MASTER_ARR = explode(",",$_GET['id']);
			foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {
				$file_name_1 = student_invoice($PK_STUDENT_MASTER,1);
				
				if($file_name_1 != '') {
					$has_records = 1;
					//$za->addFile('../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/'.$file_name_1, $file_name_1);
					$za->addFile('../backend_assets/tmp_upload/'.$file_name_1, $file_name_1);
					//$file_name_arr[] = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/'.$file_name_1;
					$file_name_arr[] = '../backend_assets/tmp_upload/'.$file_name_1;
				}
			}
			
			$za->close();
			
			unlinkRecursive("$folder/",0);
			@rmdir($folder);
			
			if($has_records == 0) {
				header("location:student_invoice?m=1");
			} else {
			
				foreach($file_name_arr as $file_name_2)
					unlink($file_name_2);
				
				header("location:".$zip_file_name);
			}
		}
	} else 	    		
           student_invoice($_GET['id'],0);
}
