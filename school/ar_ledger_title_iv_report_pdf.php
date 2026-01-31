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
require_once("function_transcript_header.php");

require_once("pdf_custom_header.php");
class MYPDF extends TCPDF {
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

		$this->SetFont('helvetica', 'I', 17);
		$this->SetY(8);
		$this->SetTextColor(000, 000, 000);
		$this->SetX(115);
		$this->Cell(55, 8, "Student Ledger - Title IV Balance", 0, false, 'L', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(110, 13, 202, 13, $style);
		
		$str = "";
		if($_GET['eid'] == '') {
			if($_GET['en_type'] == 1)
				$str = " All Enrollments";
			else if($_GET['en_type'] == 2)
				$str = " Current Enrollment";
		} else {
			$res_type_all = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$_GET[id]' ");
			
			$res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_ENROLLMENT IN ($_GET[eid]) AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			
			if($res_type_all->RecordCount() == $res_type->RecordCount())
				$str = " All Enrollments";
			else {
				while (!$res_type->EOF) {
					if($str != '')
						$str .= ', ';
						
					$str .= $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['STUDENT_STATUS'];
					
					$res_type->MoveNext();
				}
			}
		}
		
		$this->SetFont('helvetica', 'I', 10);
		$this->SetY(14);
		$this->SetX(100);
		$this->SetTextColor(000, 000, 000);
		//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
		$this->MultiCell(102, 5, $str, 0, 'R', 0, 0, '', '', true);
		
		$campus_cond  = "";
		$campus_cond1 = "";
		$campus_id	  = "";
		$campus_name  = "";
		if(!empty($_GET['campus'])){
			$PK_CAMPUS 	  = $_GET['campus'];
			$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
			$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
		}
		$res_campus = $db->Execute("select OFFICIAL_CAMPUS_NAME from S_STUDENT_CAMPUS, S_CAMPUS WHERE  S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS  AND PK_STUDENT_ENROLLMENT = '$_SESSION[TEMP_ENROLL]'");
		
		$this->SetY(18);
		$this->SetX(100);
		$this->SetTextColor(000, 000, 000);
		//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
		$this->MultiCell(102, 5, "Campus: ".$res_campus->fields['OFFICIAL_CAMPUS_NAME'], 0, 'R', 0, 0, '', '', true);

    }
    public function Footer() {
		global $db;
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
function student_ledger_iv($PK_STUDENT_MASTERS, $PK_STUDENT_ENROLLMENT, $one_stud_per_pdf){
	global $db;
	
	$PK_STUDENT_MASTER_ARR = explode(",",$PK_STUDENT_MASTERS);
	
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(7, 31, 7);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->SetAutoPageBreak(TRUE, 20);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setLanguageArray($l);
	$pdf->setFontSubsetting(true);
	$pdf->SetFont('helvetica', '', 8, '', true);

	$PK_STUDENT_MASTER_ARR = explode(",",$PK_STUDENT_MASTERS);

	foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER){
		
		$PK_STUDENT_ENROLLMENT = '';
		if($_GET['t_id'] != '') {
			$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$_GET[t_id]' "); 
			$PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
		}
		
		$res = $db->Execute("SELECT  S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER "); 
		if($res->RecordCount() == 0){
			header("location:manage_student?t=".$_GET['t']);
			exit;
		}

		$DATE_OF_BIRTH = $res->fields['DATE_OF_BIRTH'];

		if($DATE_OF_BIRTH != '0000-00-00')
			$DATE_OF_BIRTH = date("m/d/Y",strtotime($DATE_OF_BIRTH));
		else
			$DATE_OF_BIRTH = '';
			
		$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	

		$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,CODE, M_CAMPUS_PROGRAM.DESCRIPTION,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_ACTIVE_ENROLLMENT = '1'  "); 
		
		$PK_STUDENT_ENROLLMENT2 = $res_enroll->fields['PK_STUDENT_ENROLLMENT'];
		$PK_CAMPUS_PROGRAM 		= $res_enroll->fields['PK_CAMPUS_PROGRAM'];
		$res_report_header = $db->Execute("SELECT * FROM M_CAMPUS_PROGRAM_REPORT_HEADER WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		$led_cond = "";
		if($PK_STUDENT_ENROLLMENT != '')
			$led_cond = " AND S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) ";
		else if($_GET['en_type'] == 2) {
			$led_cond = " AND S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = '".$res_enroll->fields['PK_STUDENT_ENROLLMENT']."' ";
		}

		$EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
		
		if($EXPECTED_GRAD_DATE != '0000-00-00')
			$EXPECTED_GRAD_DATE = date("m/d/Y",strtotime($EXPECTED_GRAD_DATE));
		else
			$EXPECTED_GRAD_DATE = '';

		$_SESSION['TEMP_ENROLL'] = $res_enroll->fields['PK_STUDENT_ENROLLMENT'];
		
		$pdf->STUD_NAME 			= $res->fields['LAST_NAME'].", ".$res->fields['FIRST_NAME'];
		$pdf->PK_STUDENT_MASTER 	= $PK_STUDENT_MASTER;
		$pdf->PK_STUDENT_ENROLLMENT = $res_enroll->fields['PK_STUDENT_ENROLLMENT'];
		$pdf->startPageGroup();
		$pdf->AddPage();
		
		$txt = '<table border="0" cellspacing="0" cellpadding="3" width="80%">
					<tr>
						<td width="100%" style="border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;" ><b>'.$res->fields['FIRST_NAME'].' '.$res->fields['MIDDLE_NAME'].' '.$res->fields['LAST_NAME'].'</b></td>
					</tr>
					<tr>
						<td style="border-left:0.5px solid #000;border-top:0.5px solid #000" width="100%" width="34%" >
							'.transcript_header($res_report_header->fields['BOX_1'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
						</td>
						<td style="border-left:0.5px solid #000;border-top:0.5px solid #000" width="100%" width="34%" >
							'.transcript_header($res_report_header->fields['BOX_4'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
						</td>
						<td style="border-left:0.5px solid #000;border-right:0.5px solid #000;border-top:0.5px solid #000" width="100%" width="32%" >
							'.transcript_header($res_report_header->fields['BOX_7'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
						</td>
					</tr>
					<tr>
						<td width="34%" style="border-left:0.5px solid #000;" >
							'.transcript_header($res_report_header->fields['BOX_2'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
						</td>
						<td  width="34%" style="border-left:0.5px solid #000;" >
							'.transcript_header($res_report_header->fields['BOX_5'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
						</td>
						<td  width="32%" style="border-left:0.5px solid #000;border-right:0.5px solid #000;" >
							'.transcript_header($res_report_header->fields['BOX_8'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
						</td>
					</tr>
					<tr>
						<td width="34%" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000" >
							'.transcript_header($res_report_header->fields['BOX_3'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
						</td>
						<td  width="34%" style="border-left:0.5px solid #000;border-bottom:0.5px solid #000" >
							'.transcript_header($res_report_header->fields['BOX_6'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
						</td>
						<td  width="32%" style="border-left:0.5px solid #000;border-right:0.5px solid #000;border-bottom:0.5px solid #000" width="100%" width="32%"  >
							'.transcript_header($res_report_header->fields['BOX_9'], " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT2' ").'
						</td>
					</tr>
				</table>
				<br /><br />
				<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<thead>
						<tr>
							<td width="10%" style="border-bottom:1px solid #000;">
								<br /><br /><b><i>Trans Date</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;">
								<br /><br /><b><i>Ledger Code</i></b>
							</td>
							<td width="39%" style="border-bottom:1px solid #000;">
								<br /><br /><b><i>Description</i></b>
							</td>';
							
							if($_GET['do'] == 1) {
								$txt .= '<td width="13%" style="border-bottom:1px solid #000;">
											<b><i>Award Year</i></b>
										</td>';
							} else if($_GET['do'] == 2) {
								$txt .= '<td width="6.5%" style="border-bottom:1px solid #000;">
											<b><i>AY</i></b>
										</td>
										<td width="6.5%" style="border-bottom:1px solid #000;">
											<b><i>AP</i></b>
										</td>';
							} else if($_GET['do'] == 3) {
								$txt .= '<td width="13%" style="border-bottom:1px solid #000;">
											<b><i>Description</i></b>
										</td>';
							} else if($_GET['do'] == 4) {
								$txt .= '<td width="13%" style="border-bottom:1px solid #000;">
											<b><i>Fee/Payment Type</i></b>
										</td>';
							} else if($_GET['do'] == 5) {
								$txt .= '<td width="6.5%" align="right" style="border-bottom:1px solid #000;">
											<b><i>Gross</i></b>
										</td>
										<td width="6.5%" align="right" style="border-bottom:1px solid #000;">
											<b><i>Fee</i></b>
										</td>';
							} else if($_GET['do'] == 6) {
								$txt .= '<td width="13%" style="border-bottom:1px solid #000;">
											<b><i>PYA</i></b>
										</td>';
							} else if($_GET['do'] == 7 || $_GET['do'] == '') {
								$txt .= '<td width="13%" style="border-bottom:1px solid #000;">
											<b><i>Receipt/Check #</i></b>
										</td>';
							} else if($_GET['do'] == 8) {
								$txt .= '<td width="13%" style="border-bottom:1px solid #000;">
											<b><i>Term Block</i></b>
										</td>';
							}
							
					$txt .= '<td width="10%" align="right" style="border-bottom:1px solid #000;">
								<br /><br /><b><i>Debit</i></b>
							</td>
							<td width="10%" align="right" style="border-bottom:1px solid #000;">
								<br /><br /><b><i>Credit</i></b>
							</td>
							<td width="10%" align="right" style="border-bottom:1px solid #000;">
								<b><i>Title IV Balance</i></b>
							</td>
						</tr>
					</thead>';
			
				$BALANCE 	= 0;
				$TOT_DEBIT	= 0;
				$TOT_CREDIT	= 0;

				$res_ledger = $db->Execute("select PK_STUDENT_LEDGER,LEDGER_DESCRIPTION ,IF(S_STUDENT_LEDGER.TRANSACTION_DATE = '0000-00-00','', DATE_FORMAT(S_STUDENT_LEDGER.TRANSACTION_DATE, '%m/%d/%Y' )) AS TRANSACTION_DATE_1, S_STUDENT_LEDGER.CREDIT, S_STUDENT_LEDGER.DEBIT, M_AR_LEDGER_CODE.CODE, RECEIPT_NO, CHECK_NO,  
				IF(S_STUDENT_LEDGER.PK_PAYMENT_BATCH_DETAIL > 0, S_PAYMENT_BATCH_DETAIL.BATCH_DETAIL_DESCRIPTION, IF(S_STUDENT_LEDGER.PK_MISC_BATCH_DETAIL > 0, S_MISC_BATCH_DETAIL.BATCH_DETAIL_DESCRIPTION, IF(S_STUDENT_LEDGER.PK_TUITION_BATCH_DETAIL > 0, S_TUITION_BATCH_DETAIL.BATCH_DETAIL_DESCRIPTION, ''))) as BATCH_DESCRIPTION, TITLE_IV, M_AR_LEDGER_CODE.TYPE, AWARD_YEAR, S_STUDENT_LEDGER.PK_PAYMENT_BATCH_DETAIL, S_STUDENT_LEDGER.PK_TUITION_BATCH_DETAIL, S_STUDENT_LEDGER.PK_MISC_BATCH_DETAIL, GROSS_AMOUNT, FEE_AMOUNT
				from 
				S_STUDENT_LEDGER 
				LEFT JOIN S_STUDENT_DISBURSEMENT ON S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT = S_STUDENT_LEDGER.PK_STUDENT_DISBURSEMENT 
				LEFT JOIN M_AWARD_YEAR ON M_AWARD_YEAR.PK_AWARD_YEAR = S_STUDENT_DISBURSEMENT.PK_AWARD_YEAR 
				
				LEFT JOIN S_PAYMENT_BATCH_DETAIL ON S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = S_STUDENT_LEDGER.PK_PAYMENT_BATCH_DETAIL 
				
				LEFT JOIN S_MISC_BATCH_DETAIL ON S_MISC_BATCH_DETAIL.PK_MISC_BATCH_DETAIL = S_STUDENT_LEDGER.PK_MISC_BATCH_DETAIL 
				LEFT JOIN S_TUITION_BATCH_DETAIL ON S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_DETAIL = S_STUDENT_LEDGER.PK_TUITION_BATCH_DETAIL 
				
				LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE 
				WHERE 
				S_STUDENT_LEDGER.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
				(S_STUDENT_LEDGER.PK_PAYMENT_BATCH_DETAIL > 0 OR S_STUDENT_LEDGER.PK_MISC_BATCH_DETAIL > 0 OR S_STUDENT_LEDGER.PK_TUITION_BATCH_DETAIL > 0 ) $led_cond ORDER BY S_STUDENT_LEDGER.TRANSACTION_DATE ASC"); 
				
				while (!$res_ledger->EOF) {
					
					$flag = 0;
					if($res_ledger->fields['DEBIT'] != 0 && $res_ledger->fields['TYPE'] == 2) {
						$BALANCE += $res_ledger->fields['DEBIT'];
						$flag = 1;
					}
						
					if($res_ledger->fields['CREDIT'] != 0 && $res_ledger->fields['TITLE_IV'] == 1 && $res_ledger->fields['TYPE'] == 1) {
						$BALANCE -= $res_ledger->fields['CREDIT'];
						$flag = 1;
					}

					if(round($BALANCE,2) == 0)
						$BALANCE = abs($BALANCE);
		
					if($flag == 1) {
						
						$DESC = $res_ledger->fields['LEDGER_DESCRIPTION'];
						
						$DEBIT_2  = $res_ledger->fields['DEBIT'];
						$CREDIT_2 = $res_ledger->fields['CREDIT'];
						
						if($res_ledger->fields['TYPE'] == 1) {
							$DEBIT_2 = 0;
						} else {
							$CREDIT_2 = 0;
						}
						
						$TOT_DEBIT  += $DEBIT_2;
						$TOT_CREDIT += $CREDIT_2;
						
						$DESCRIPTION 	= "";
						$DETAIL1		= '';
						$PRIOR_YEAR		= "";
						
						if($res_ledger->fields['PK_PAYMENT_BATCH_DETAIL'] > 0) {
							$res_det = $db->Execute("SELECT S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER, S_PAYMENT_BATCH_DETAIL.CHECK_NO, RECEIPT_NO, BATCH_DETAIL_DESCRIPTION,BATCH_NO, PRIOR_YEAR, ACADEMIC_YEAR, S_PAYMENT_BATCH_DETAIL.PK_TERM_BLOCK, S_PAYMENT_BATCH_DETAIL.CREATED_ON, ACADEMIC_PERIOD, PK_DETAIL_TYPE, DETAIL FROM S_PAYMENT_BATCH_MASTER, S_PAYMENT_BATCH_DETAIL LEFT JOIN S_STUDENT_DISBURSEMENT ON S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT = S_PAYMENT_BATCH_DETAIL.PK_STUDENT_DISBURSEMENT WHERE S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER AND S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = '".$res_ledger->fields['PK_PAYMENT_BATCH_DETAIL']."' "); 
							
							$ACADEMIC_YEAR 		= $res_det->fields['ACADEMIC_YEAR'];
							$ACADEMIC_PERIOD 	= $res_det->fields['ACADEMIC_PERIOD'];
							$DESCRIPTION 		= $res_det->fields['BATCH_DETAIL_DESCRIPTION'];
							$LED_PK_TERM_BLOCK 	= $res_det->fields['PK_TERM_BLOCK'];
							
							if($res_det->fields['PK_DETAIL_TYPE'] == 4) {
								$DETAIL = $res_det->fields['DETAIL'];
								$res_det1a = $db->Execute("select AR_PAYMENT_TYPE from M_AR_PAYMENT_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_PAYMENT_TYPE = '$DETAIL' ");
								$DETAIL1 = $res_det1a->fields['AR_PAYMENT_TYPE'];
							}
							
							if($res_det->fields['PRIOR_YEAR'] == 1)
								$PRIOR_YEAR = 'Yes';
							else
								$PRIOR_YEAR = 'No';
								
						} else if($res_ledger->fields['PK_MISC_BATCH_DETAIL'] > 0) {
							$res_det = $db->Execute("SELECT BATCH_NO,BATCH_DETAIL_DESCRIPTION, PK_AR_FEE_TYPE, PRIOR_YEAR, PK_AR_PAYMENT_TYPE, S_MISC_BATCH_DETAIL.CREATED_ON, AY, AP, PK_TERM_BLOCK, S_MISC_BATCH_MASTER.PK_MISC_BATCH_MASTER, MISC_RECEIPT_NO, S_MISC_BATCH_DETAIL.PK_STUDENT_CREDIT_CARD_PAYMENT, PAYMENT_MODE, REF_NUMBER FROM S_MISC_BATCH_MASTER,S_MISC_BATCH_DETAIL WHERE S_MISC_BATCH_MASTER.PK_MISC_BATCH_MASTER = S_MISC_BATCH_DETAIL.PK_MISC_BATCH_MASTER AND PK_MISC_BATCH_DETAIL = '".$res_ledger->fields['PK_MISC_BATCH_DETAIL']."' ");
							
							$ACADEMIC_YEAR 		= $res_det->fields['AY'];
							$ACADEMIC_PERIOD 	= $res_det->fields['AP'];
							$DESCRIPTION 		= $res_det->fields['BATCH_DETAIL_DESCRIPTION'];
							$LED_PK_TERM_BLOCK 	= $res_det->fields['PK_TERM_BLOCK'];
							
							$DETAIL1 = '';
							if($res_det->fields['PAYMENT_MODE'] == 1)
								$DETAIL1 = 'Check';
							else if($res_det->fields['PAYMENT_MODE'] == 2)
								$DETAIL1 = 'Cash';
							else if($res_det->fields['PAYMENT_MODE'] == 3)
								$DETAIL1 = 'Money Order';
							else if($res_det->fields['PAYMENT_MODE'] == 4 || $res_det->fields['PAYMENT_MODE'] == 5) //Ticket #1081
								$DETAIL1 = 'Credit Card';
							
							if($MISC_RECEIPT_NO == '')
								$RECEIPT_NO = '';
							else
								$RECEIPT_NO = $MISC_RECEIPT_NO;
							
							if($res_det->fields['PK_AR_FEE_TYPE'] > 0) {
								$res11 = $db->Execute("select AR_FEE_TYPE FROM M_AR_FEE_TYPE WHERE PK_AR_FEE_TYPE = '".$res_det->fields['PK_AR_FEE_TYPE']."' ");
								$DETAIL1 = $res11->fields['AR_FEE_TYPE'];
							} else if($res_det->fields['PK_AR_PAYMENT_TYPE'] > 0) {
								$res11 = $db->Execute("select AR_PAYMENT_TYPE FROM M_AR_PAYMENT_TYPE WHERE PK_AR_PAYMENT_TYPE = '".$res_det->fields['PK_AR_PAYMENT_TYPE']."' ");
								$DETAIL1 = $res11->fields['AR_PAYMENT_TYPE'];
							}
							
							if($res_det->fields['PRIOR_YEAR'] == 1)
								$PRIOR_YEAR = 'Yes';
							else
								$PRIOR_YEAR = 'No';
						} else if($res_ledger->fields['PK_TUITION_BATCH_DETAIL'] > 0) {
							$res_det = $db->Execute("SELECT BATCH_NO,AY,AP,BATCH_DETAIL_DESCRIPTION, S_TUITION_BATCH_DETAIL.CREATED_ON, PK_TERM_BLOCK, S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER FROM S_TUITION_BATCH_MASTER, S_TUITION_BATCH_DETAIL WHERE S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER = S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_MASTER AND PK_TUITION_BATCH_DETAIL = '".$res_ledger->fields['PK_TUITION_BATCH_DETAIL']."' ");
							
							$ACADEMIC_YEAR 	 	= $res_det->fields['AY'];
							$ACADEMIC_PERIOD	= $res_det->fields['AP'];
							$DESCRIPTION 		= $res_det->fields['BATCH_DETAIL_DESCRIPTION'];
							$LED_PK_TERM_BLOCK 	= $res_det->fields['PK_TERM_BLOCK'];
						}
						
						$res_term = $db->Execute("select IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE_1, DESCRIPTION from S_TERM_BLOCK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_BLOCK = '$LED_PK_TERM_BLOCK' ");
						
						$txt .= '<tr>
								<td width="10%" >'.$res_ledger->fields['TRANSACTION_DATE_1'].'</td>
								<td width="10%" >'.$res_ledger->fields['CODE'].'</td>
								<td width="39%" >'.$DESC.'</td>';
								
								if($_GET['do'] == 1) {
									$txt .= '<td width="13%" >'.$res_ledger->fields['AWARD_YEAR'].'</td>';
								} else if($_GET['do'] == 2) {
									$txt .= '<td width="6.5%" >'.$ACADEMIC_YEAR.'</td>
											 <td width="6.5%" >'.$ACADEMIC_PERIOD.'</td>';
								} else if($_GET['do'] == 3) {
									$txt .= '<td width="13%" >'.$DESCRIPTION.'</td>';
								} else if($_GET['do'] == 4) {
									$txt .= '<td width="13%" >'.$DETAIL1.'</td>';
								} else if($_GET['do'] == 5) {
									$txt .= '<td width="6.5%" align="right">'.$res_ledger->fields['GROSS_AMOUNT'].'</td>
											<td width="6.5%" align="right" >'.$res_ledger->fields['FEE_AMOUNT'].'</td>';
								} else if($_GET['do'] == 6) {
									$txt .= '<td width="13%" >'.$PRIOR_YEAR.'</td>';
								} else if($_GET['do'] == 7 || $_GET['do'] == '') {
									$txt .= '<td width="13%" >';
									if($res_ledger->fields['RECEIPT_NO'] != '' )
										$txt .= 'Receipt # '.$res_ledger->fields['RECEIPT_NO'];
									if($res_ledger->fields['CHECK_NO'] != '') {
										if($res_ledger->fields['RECEIPT_NO'] != '' )
											$txt .= '<br />';
										$txt .= 'Check # '.$res_ledger->fields['CHECK_NO'];
									}
									$txt .= '</td>';
								} else if($_GET['do'] == 8) {
										$txt .= '<td width="13%" >'.$res_term->fields['BEGIN_DATE_1'].' - '.$res_term->fields['END_DATE_1'].' - '.$res_term->fields['DESCRIPTION'].'</td>';
								}
							
						$txt .= '<td width="10%" align="right">$ '.number_format_value_checker($DEBIT_2,2).'</td>
								<td width="10%" align="right">$ '.number_format_value_checker($CREDIT_2,2).'</td>
								<td width="10%" align="right">$ '.number_format_value_checker($BALANCE,2).'</td>
							</tr>';
					}
					
					$res_ledger->MoveNext();
				}
				
				$txt .= '<tr>
							<td width="72%" ></td>
							<td width="10%" align="right" style="border-top:1px solid #000;"><b>$ '.number_format_value_checker($TOT_DEBIT,2).'</b></td>
							<td width="10%" align="right" style="border-top:1px solid #000;"><b>$ '.number_format_value_checker($TOT_CREDIT,2).'</b></td>
							<td width="10%" align="right" style="border-top:1px solid #000;"><b>$ '.number_format_value_checker($BALANCE,2).'</b></td>
						</tr>
					</table>';
				
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
	}

	if($one_stud_per_pdf == 0) {
		$file_name  = 'Student Ledger Title IV Report.pdf';
		$pdf->Output('temp/'.$file_name, 'FD');
	} else {
		// $file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/';
		$file_dir_1 = '../backend_assets/tmp_upload/';
		$file_name  = $res->fields['LAST_NAME'].' '.$res->fields['FIRST_NAME'].'_'.$PK_STUDENT_MASTER.'.pdf';
		$pdf->Output($file_dir_1.$file_name, 'F');
	}
	
	/*if($browser == 'Safari')
		$pdf->Output('temp/'.$file_name, 'FD');
	else	
		$pdf->Output($file_name, 'I');*/

	
	return $file_name;	
}

if($_GET['id'] == '') {
	$PK_STUDENT_MASTERS = $_SESSION['PK_STUDENT_MASTER'];
	$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
	$PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
	
	student_ledger_iv($PK_STUDENT_MASTERS, $PK_STUDENT_ENROLLMENT, 0);
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
		
		// $folder = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/student_ledger_iv';
		$folder = '../backend_assets/tmp_upload/student_ledger_iv';
		$zip_file_name  = $folder.'.zip';
		if($folder != '') {
			unlinkRecursive("$folder/",0);
			unlink($zip_file_name);
			@rmdir($folder);
		}
		mkdir($folder);
		
		$za = new FlxZipArchive;
		$res = $za->open($zip_file_name, ZipArchive::CREATE);
		if($res === TRUE) {
			$PK_STUDENT_MASTER_ARR = explode(",",$_GET['id']);
			foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER) {
				$file_name_1 = student_ledger_iv($PK_STUDENT_MASTER,'',1);
				
				// $za->addFile('../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/'.$file_name_1, $file_name_1);
				$za->addFile('../backend_assets/tmp_upload/'.$file_name_1, $file_name_1);
				
				// $file_name_arr[] = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/'.$file_name_1;
				$file_name_arr[] = '../backend_assets/tmp_upload/'.$file_name_1;
			}
			
			$za->close();
			
			unlinkRecursive("$folder/",0);
			@rmdir($folder);
			
			foreach($file_name_arr as $file_name_2)
				unlink($file_name_2);
			
			header("location:".$zip_file_name);
		}
	} else 
		student_ledger_iv($_GET['id'], $_GET['eid'], 0);
}