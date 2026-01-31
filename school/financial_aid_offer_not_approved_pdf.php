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

require_once("pdf_custom_header.php"); //Ticket # 1588
class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
		/* Ticket # 1588 */
		if($_GET['id'] != ''){
			if($this->PageNo() > 0) {
				$CONTENT = pdf_custom_header($_GET['id'], $_GET['eid'], 1);
				$CONTENT = '<b>'.str_replace("<br /><br />", "<br />", $CONTENT).'</b>'; //DAIM-1164
				$this->MultiCell(150, 20, $CONTENT, 0, 'L', 0, 0, '', '', true,'',true,true);				
				$this->SetMargins('', 45, '');
			} else {
				$res = $db->Execute("SELECT FIRST_NAME, LAST_NAME FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
				
				$STUD_NAME = $res->fields['LAST_NAME'].', '.$res->fields['FIRST_NAME'];
				$this->SetFont('helvetica', 'I', 15);
				$this->SetY(8);
				$this->SetX(10);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(75, 8, $STUD_NAME , 0, false, 'L', 0, '', 0, false, 'M', 'L');
				$this->SetMargins('', 25, '');
			}
			
		} else {
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			
			if($res->fields['PDF_LOGO'] != '') {
				$ext = explode(".",$res->fields['PDF_LOGO']);
				$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
			}
			
			$this->SetFont('helvetica', '', 15);
			$this->SetY(8);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetFont('helvetica', '', 8);
			$this->SetY(13);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8,$res->fields['ADDRESS'].' '.$res->fields['ADDRESS_1'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetY(17);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8,$res->fields['CITY'].', '.$res->fields['STATE_CODE'].' '.$res->fields['ZIP'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
			
			$this->SetY(21);
			$this->SetX(55);
			$this->SetTextColor(000, 000, 000);
			$this->Cell(55, 8,$res->fields['PHONE'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		}
		/* Ticket # 1588 */
		
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(8);
		$this->SetX(137);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(65, 8, "Offer Letter", 0, false, 'R', 0, '', 0, false, 'M', 'L');

		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(130, 13, 202, 13, $style);
		
		$this->SetFont('helvetica', 'I', 15);
		$this->SetY(17);
		$this->SetX(137);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(65, 8, "Unapproved Awards", 0, false, 'R', 0, '', 0, false, 'M', 'L'); //DIAM-1164
		
    }
    public function Footer() {
		global $db;
		
		$this->SetY(-15);
		$this->SetX(180);
		$this->SetFont('helvetica', 'I', 7);
		$this->Cell(30, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		
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

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 31, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 30);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);
$pdf->SetFont('helvetica', '', 9, '', true);
//$pdf->AddPage(); //DIAM-1164

$res = $db->Execute("SELECT S_STUDENT_MASTER.*,STUDENT_ID FROM S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->RecordCount() == 0){
	header("location:manage_student?t=".$_GET['t']);
	exit;
}

$SSN	 				= $res->fields['SSN'];
$SSN_VERIFIED			= $res->fields['SSN_VERIFIED'];
$DATE_OF_BIRTH	 		= $res->fields['DATE_OF_BIRTH'];

if($DATE_OF_BIRTH != '0000-00-00')
	$DATE_OF_BIRTH = date("m/d/Y",strtotime($DATE_OF_BIRTH));
else
	$DATE_OF_BIRTH = '';

if($SSN != '') {
	$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$SSN);
	$SSN_ORG = $SSN;
	$SSN_ARR = explode("-",$SSN);
	$SSN 	 = 'xxx-xx-'.$SSN_ARR[2];
}

if($SSN_VERIFIED == 1)
	$SSN_VERIFIED = 'Verified';
else
	$SSN_VERIFIED = '';
	
$ay_cond = "";
if($_REQUEST['ay'] != '')
	$ay_cond = " AND ACADEMIC_YEAR = '$_REQUEST[ay]' ";
		

$ap_cond = "";
if ($_REQUEST['ap'] != '')
	$ap_cond = " AND ACADEMIC_PERIOD = '$_REQUEST[ap]' ";
	
//DIAM-1164
$efc_sai_cond ="";
if ($_REQUEST['EFC_SAI'] != ''){
	$efc_sai_cond = " AND PK_STUDENT_FINANCIAL = '$_REQUEST[EFC_SAI]' ";
}
	
if($_REQUEST['PLACEMENT_EN_FILTER'] != ""){
	$arr_of_enid = $_REQUEST['PLACEMENT_EN_FILTER'];
}else{
	$arr_of_enid = $_REQUEST['eid'];
}
$arr_enid = explode(",",$arr_of_enid);	

$txt = '';
foreach ($arr_enid as $enroll_id) {
$enroll_cond = "  AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ='$enroll_id' ";
$enroll_cond1 = "  AND PK_STUDENT_ENROLLMENT ='$enroll_id' ";
	
//$res_fin = $db->Execute("select ACADEMIC_YEAR, IF(ACADEMIC_YEAR_BEGIN = '0000-00-00','',DATE_FORMAT(ACADEMIC_YEAR_BEGIN, '%m/%d/%Y' )) AS ACADEMIC_YEAR_BEGIN, IF(ACADEMIC_YEAR_END = '0000-00-00','',DATE_FORMAT(ACADEMIC_YEAR_END, '%m/%d/%Y' )) AS ACADEMIC_YEAR_END, EFC_NO, IF(COA = '', 0, COA) AS COA FROM S_STUDENT_FINANCIAL WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $ay_cond AND PK_STUDENT_MASTER = '$_GET[id]' ");

//DIAM-1164
$res_fin = $db->Execute("SELECT PK_STUDENT_FINANCIAL,ACADEMIC_YEAR,AWARD_YEAR, IF(ACADEMIC_YEAR_BEGIN = '0000-00-00','',DATE_FORMAT(ACADEMIC_YEAR_BEGIN, '%m/%d/%Y' )) AS ACADEMIC_YEAR_BEGIN, IF(ACADEMIC_YEAR_END = '0000-00-00','',DATE_FORMAT(ACADEMIC_YEAR_END, '%m/%d/%Y' )) AS ACADEMIC_YEAR_END, EFC_NO, IF(COA = '', 0, COA) AS COA,S_STUDENT_FINANCIAL.NEED,S_STUDENT_FINANCIAL.PK_STUDENT_ENROLLMENT FROM S_STUDENT_FINANCIAL LEFT JOIN M_AWARD_YEAR ON M_AWARD_YEAR.PK_AWARD_YEAR = S_STUDENT_FINANCIAL.PK_AWARD_YEAR WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND PK_STUDENT_MASTER = '$_GET[id]' $efc_sai_cond $ay_cond ");
$fin_enroll = $res_fin->fields['PK_STUDENT_ENROLLMENT'];
$PK_STUDENT_FINANCIAL = $res_fin->fields['PK_STUDENT_FINANCIAL'];
	
$res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 	

$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.*,CODE,STUDENT_STATUS,PK_STUDENT_STATUS_MASTER, LEAD_SOURCE, FUNDING,UNITS,HOURS,MONTHS,WEEKS,FA_UNITS, EXPECTED_GRAD_DATE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS EMP_NAME,CAMPUS_CODE,M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM,M_CAMPUS_PROGRAM.CLOCK_CREDIT,M_CAMPUS_PROGRAM.DESCRIPTION FROM S_STUDENT_ENROLLMENT LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$_GET[id]'  AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$fin_enroll' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); //DIAM-1164 //DIAM-2124

//$res_prog_fee = $db->Execute("select SUM(FEE_AMOUNT) as FEE_AMOUNT from S_STUDENT_FEE_BUDGET WHERE PK_STUDENT_MASTER = '$_GET[id]'   AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $ay_cond  $ap_cond $enroll_cond");

//DIAM-2124
$PK_CAMPUS_PROGRAM = $res_enroll->fields['PK_CAMPUS_PROGRAM'];
$res_anay_setup = $db->Execute("SELECT PROGRAM_TYPE_OPT,PROGRAM_HOURS_OPT,PROGRAM_FA_UNITS_OPT,PROGRAM_UNITS_OPT,PROGRAM_LENGTH_IN_HOURS_OPT,PROGRAM_LENGTH_IN_MONTHS_OPT,PROGRAM_LENGTH_IN_WEEKS_OPT,OFFER_LETTER_COA_OPT FROM M_CAMPUS_PROGRAM_ANALYTICS_SETUP WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
$PROGRAM_TYPE_OPT 					= $res_anay_setup->fields['PROGRAM_TYPE_OPT'];
$PROGRAM_HOURS_OPT 					= $res_anay_setup->fields['PROGRAM_HOURS_OPT'];
$PROGRAM_FA_UNITS_OPT 				= $res_anay_setup->fields['PROGRAM_FA_UNITS_OPT'];
$PROGRAM_UNITS_OPT 					= $res_anay_setup->fields['PROGRAM_UNITS_OPT'];
$PROGRAM_LENGTH_IN_HOURS_OPT 		= $res_anay_setup->fields['PROGRAM_LENGTH_IN_HOURS_OPT'];
$PROGRAM_LENGTH_IN_MONTHS_OPT 		= $res_anay_setup->fields['PROGRAM_LENGTH_IN_MONTHS_OPT'];
$PROGRAM_LENGTH_IN_WEEKS_OPT 		= $res_anay_setup->fields['PROGRAM_LENGTH_IN_WEEKS_OPT'];
$OFFER_LETTER_COA_OPT 				= $res_anay_setup->fields['OFFER_LETTER_COA_OPT'];
//DIAM-2124

//DIAM-1164
$res_prog_fee = $db->Execute("select SUM(FEE_AMOUNT) as FEE_AMOUNT from S_STUDENT_FEE_BUDGET LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_FEE_BUDGET.PK_AR_LEDGER_CODE WHERE PK_STUDENT_MASTER = '$_GET[id]' $enroll_cond1  AND M_AR_LEDGER_CODE.TYPE = 2 AND NEED_ANALYSIS = 1 AND S_STUDENT_FEE_BUDGET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $ay_cond $ap_cond");

$EXPECTED_GRAD_DATE = $res_enroll->fields['EXPECTED_GRAD_DATE'];
if($EXPECTED_GRAD_DATE != '0000-00-00' && $EXPECTED_GRAD_DATE != '')
	$EXPECTED_GRAD_DATE = date("m/d/Y",strtotime($EXPECTED_GRAD_DATE));
else
	$EXPECTED_GRAD_DATE = '';


$CLOCK_CREDIT='';
if($res_enroll->fields['CLOCK_CREDIT'] == 1){
	$CLOCK_CREDIT='Clock';
}else if($res_enroll->fields['CLOCK_CREDIT'] == 2){
	$CLOCK_CREDIT='Credit';
}else if($res_enroll->fields['CLOCK_CREDIT'] == 3){
	$CLOCK_CREDIT='N/A';
}


//DIAM-1164
if ($_REQUEST['ay'] != '') {
	$ACADEMIC_YEAR_BEGIN = $res_fin->fields['ACADEMIC_YEAR_BEGIN'];
	$ACADEMIC_YEAR_END = $res_fin->fields['ACADEMIC_YEAR_END'];

	if($ACADEMIC_YEAR_BEGIN!="" && $ACADEMIC_YEAR_END!=""){
	$ACADEMIC_YEAR = 'AY'.$_REQUEST['ay'].':'.$ACADEMIC_YEAR_BEGIN.'-'.$ACADEMIC_YEAR_END;
	}else{
		$ACADEMIC_YEAR = 'AY'.$_REQUEST['ay'].'';
	}
}else{
	$ACADEMIC_YEAR = 'ALL';
}

if ($_REQUEST['ap'] != '') {
	$AP = $_REQUEST['ap'];
	$res_fin_aca = $db->Execute("SELECT PERIOD,IF(PERIOD_BEGIN = '0000-00-00','',DATE_FORMAT(PERIOD_BEGIN, '%m/%d/%Y' )) AS PERIOD_BEGIN, IF(PERIOD_END = '0000-00-00','',DATE_FORMAT(PERIOD_END, '%m/%d/%Y' )) AS PERIOD_END FROM S_STUDENT_FINANCIAL_ACADEMY WHERE `PK_ACCOUNT` = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[id]'  AND PK_STUDENT_FINANCIAL = '$PK_STUDENT_FINANCIAL' AND PERIOD = '$AP'");
	$PERIOD_BEGIN = $res_fin_aca->fields['PERIOD_BEGIN'];
	$PERIOD_END = $res_fin_aca->fields['PERIOD_END'];
	$PERIOD = $res_fin_aca->fields['PERIOD'];
	if($PERIOD_BEGIN!="" && $PERIOD_END!=""){
	$ACADEMIC_PERIODS = 'AP'.$AP.':'.$PERIOD_BEGIN.'-'.$PERIOD_END;
	}else{
		$ACADEMIC_PERIODS = 'AP'.$AP.'';
	}
}else{
	$ACADEMIC_PERIODS = 'ALL';
}

$pdf->AddPage();
//DIAM-2124
$COST_OF_ATTENDANCE = $res_prog_fee->fields['FEE_AMOUNT'];
if($OFFER_LETTER_COA_OPT==1){
	$COST_OF_ATTENDANCE = (!empty($res_fin->fields['COA'])) ? $res_fin->fields['COA'] : "0.00";
}
// Remaining Need formula Cost of Attendance - EFC/SAI = Remaining Need
$REMAINING_NEED = $COST_OF_ATTENDANCE  - $res_fin->fields['EFC_NO'];
//DIAM-2124
$txt = '';
//DIAM-2124
$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%" style="font-size:30px;">
			<tr>
				<td width="50%">
					<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="100%"><b>'.$res->fields['FIRST_NAME'].' '.$res->fields['MIDDLE_NAME'].' '.$res->fields['LAST_NAME'].'</b></td>
						</tr>
						<tr>
							<td width="100%"><b>'.$res_address->fields['ADDRESS'].' '.$res_address->fields['ADDRESS_1'].'</b></td>
						</tr>
						<tr>
							<td width="100%"><b>'.$res_address->fields['CITY'].', '.$res_address->fields['STATE_CODE'].' '.$res_address->fields['ZIP'].'</b></td>
						</tr>
						<tr>
							<td width="100%"><b>'.$res_address->fields['COUNTRY'].'</b></td>
						</tr>
					</table>';
					$txt .= '<br/><br/>
						<table border="0" cellspacing="0" cellpadding="3" width="100%">
							<tr>
								<td width="100%">
								
									<table border="0" cellspacing="0" cellpadding="3" width="100%">
									<tr>
										<td width="50%"><b>Academic Year</b></td>
										<td width="50%" style="text-align:right;"><b>' . $ACADEMIC_YEAR . '</b></td>
									</tr>
									<tr>
									<td width="50%"><b>Academic Period</b></td>
									<td width="50%" style="text-align:right;"><b>' . $ACADEMIC_PERIODS . '</b></td>
								</tr>
									<tr>
										<td width="50%" ><b>Cost of Attendance (COA)</b></td>
										<td width="50%" style="text-align:right;"><b>' . number_format_value_checker($COST_OF_ATTENDANCE, 2) . '</b></td>
									</tr>';//DIAM-2124
	
									//if($res_fin->fields['AWARD_YEAR']!="" &&  $res_fin->fields['EFC_NO']!="" && $_REQUEST['EFC_SAI'] != ""){
										$txt .= '<tr>
											<td width="50%"><b>EFC/SAI '.$res_fin->fields['AWARD_YEAR'].'</b></td>
											<td width="50%"  style="text-align:right;"><b>' . $res_fin->fields['EFC_NO'] . '</b></td>
										</tr>';
									//}
	
									$txt .= '<tr>
										<td width="50%" ><b>Remaining Need</b></td>
										<td width="50%" style="text-align:right;"><b>' . number_format_value_checker($REMAINING_NEED, 2). '</b></td>
									</tr>
									</table>
								
								</td>
								
							</tr>
							
							</table>';
							$txt .= '</td>
							<td width="65%">
								<table border="0" cellspacing="0" cellpadding="3" width="100%">
									<tr>
										<td width="45%"><b>Student ID</b></td>
										<td width="55%"><b>' . $res->fields['STUDENT_ID'] . '</b></td>
									</tr>
									<tr>
										<td width="45%"><b>Campus</b></td>
										<td width="55%"><b>' .$res_enroll->fields['CAMPUS_CODE'] . '</b></td>
									</tr>
									<tr>
										<td width="45%"><b>First Term</b></td>
										<td width="55%"><b>' . $res_enroll->fields['TERM_MASTER'] . '</b></td>
									</tr>
									<tr>
										<td width="45%"><b>Expected Grad Date</b></td>
										<td width="55%"><b>' . $EXPECTED_GRAD_DATE . '</b></td>
									</tr>
									<tr>
										<td width="45%"><b>Status</b></td>
										<td width="55%"><b>' . $res_enroll->fields['STUDENT_STATUS'] . '</b></td>
									</tr>
									<tr>
										<td width="45%"><b>Program</b></td>
										<td width="55%"><b>' . $res_enroll->fields['CODE'] . '</b></td>
									</tr>';
					
									//DIAM-2124
									if($PROGRAM_TYPE_OPT==1){
									$txt .= '<tr>
										<td width="45%"><b>Program Type</b></td>
										<td width="55%"><b>' . $CLOCK_CREDIT . '</b></td>
									</tr>';
									}
			
									if($PROGRAM_HOURS_OPT==1){
									$txt .= '<tr>
										<td width="45%"><b>Program Hours</b></td>
										<td width="55%"><b>' .$res_enroll->fields['HOURS']. '</b></td>
									</tr>';
									}
			
									if($PROGRAM_FA_UNITS_OPT==1){
									$txt .= '<tr>
										<td width="45%"><b>Program FA Units</b></td>
										<td width="55%"><b>' . $res_enroll->fields['FA_UNITS'] . '</b></td>
									</tr>';
									}
			
									if($PROGRAM_UNITS_OPT==1){
									$txt .= '<tr>
										<td width="45%"><b>Program Units</b></td>
										<td width="55%"><b>' . $res_enroll->fields['UNITS'] . '</b></td>
									</tr>';
									}
			
									if($PROGRAM_LENGTH_IN_HOURS_OPT==1){
									$txt .= '<tr>
										<td width="45%"><b>Program Length In Hours</b></td>
										<td width="55%"><b>' . $res_enroll->fields['HOURS'] . '</b></td>
									</tr>';
									}
			
									if($PROGRAM_LENGTH_IN_MONTHS_OPT==1){
									$txt .= '<tr>
										<td width="45%"><b>Program Length In Months</b></td>
										<td width="55%"><b>' . $res_enroll->fields['MONTHS'] . '</b></td>
									</tr>';
									}
			
									if($PROGRAM_LENGTH_IN_WEEKS_OPT==1){
									$txt .= '<tr>
										<td width="45%"><b>Program Length In Weeks</b></td>
										<td width="55%"><b>' .  $res_enroll->fields['WEEKS'] . '</b></td>
									</tr>';
									}
									//DIAM-2124
								$txt .= '</table>
							</td>
						</tr>
					</table>
					<br /><br />
					<table border="0" cellspacing="0" cellpadding="3" width="100%">';
					$txt .= '<tr><td width="100%" style="margin-bottom:10px;font-size:32px;height:25px;"><b>Program Description: ' .  $res_enroll->fields['DESCRIPTION'] . '</b></td></tr>'; //DIAM-2124				
			/*if($_REQUEST['ay'] != '') {
				$txt .= '<tr>
							<td width="25%" style="border-bottom:3px solid #a5a5a5;"><b>Student Academic Year</b></td>
							<td width="10%" style="border-bottom:3px solid #a5a5a5;" >'.$res_fin->fields['ACADEMIC_YEAR'].'</td>
							<td width="15%" style="border-bottom:3px solid #a5a5a5;" >'.$res_fin->fields['ACADEMIC_YEAR_BEGIN'].'</td>
							<td width="15%" style="border-bottom:3px solid #a5a5a5;" >'.$res_fin->fields['ACADEMIC_YEAR_END'].'</td>
							<td width="10%" style="border-bottom:3px solid #a5a5a5;" ><b>EFC No</b></td>
							<td width="10%" style="border-bottom:3px solid #a5a5a5;text-align:right;" >'.$res_fin->fields['EFC_NO'].'</td>
							<td width="15%" style="border-bottom:3px solid #a5a5a5;text-align:right;" ></td>
						</tr>';
			} else {
				$txt .= '<tr>
							<td width="25%" style="border-bottom:3px solid #a5a5a5;"><b>Student Academic Year</b></td>
							<td width="10%" style="border-bottom:3px solid #a5a5a5;" >All</td>
							<td width="15%" style="border-bottom:3px solid #a5a5a5;" ></td>
							<td width="15%" style="border-bottom:3px solid #a5a5a5;" ></td>
							<td width="10%" style="border-bottom:3px solid #a5a5a5;" ><b>EFC No</b></td>
							<td width="10%" style="border-bottom:3px solid #a5a5a5;text-align:right;" >'.$res_fin->fields['EFC_NO'].'</td>
							<td width="15%" style="border-bottom:3px solid #a5a5a5;text-align:right;" ></td>
						</tr>';
			}*/
			
		/*$txt .= '<tr>
				<td width="75%" style="border-bottom:0.5px solid #000;"><b>Cost of Attendance</b><br /></td>
				<td width="10%" style="border-bottom:0.5px solid #000;text-align:right;" >$'.number_format_value_checker($res_prog_fee->fields['FEE_AMOUNT'],2).'</td>
			</tr>';*/
			/*$txt .= '<tr>
							<td width="85%" style="border-bottom:3px solid #a5a5a5;"></td>							
						</tr>';*/ //DIAM-1164

			$txt .= '<tr>
				<td width="55%" style="border-bottom:3px solid #a5a5a5;border-top:3px solid #a5a5a5;"><b>Unapproved Awards</b></td>
				<td width="30%" style="text-align:right;border-bottom:3px solid #a5a5a5;border-top:3px solid #a5a5a5;" ><b>Disbursement Date</b></td>
				<td width="15%" style="text-align:right;border-bottom:3px solid #a5a5a5;border-top:3px solid #a5a5a5;" ><b>Amount</b></td>
			</tr>'; //DIAM-1164
			
			$total		   = 0;
			//AND PK_STUDENT_ENROLLMENT = '$_GET[eid]'
			$res_prog_fee = $db->Execute("select DISBURSEMENT_AMOUNT, IF(DISBURSEMENT_DATE = '0000-00-00','',DATE_FORMAT(DISBURSEMENT_DATE, '%m/%d/%Y' )) AS DISBURSEMENT_DATE_1, CONCAT(M_AR_LEDGER_CODE.CODE, ' - ', M_AR_LEDGER_CODE.LEDGER_DESCRIPTION),M_AR_LEDGER_CODE.LEDGER_DESCRIPTION AS LEDGER, S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE from S_STUDENT_DISBURSEMENT LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE WHERE PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $ay_cond $ap_cond $enroll_cond1 AND PK_DISBURSEMENT_STATUS = 0 AND AWARD_LETTER = 1 ORDER BY LEDGER ASC, DISBURSEMENT_DATE ASC");
			while (!$res_prog_fee->EOF) { 
				$total  += $res_prog_fee->fields['DISBURSEMENT_AMOUNT'];
				
				$txt .= '<tr>
							<td width="55%" >'.$res_prog_fee->fields['LEDGER'].'</td>
							<td width="30%" style="text-align:right;" >'.$res_prog_fee->fields['DISBURSEMENT_DATE_1'].'</td>
							<td width="15%" style="text-align:right;" >$'.number_format_value_checker($res_prog_fee->fields['DISBURSEMENT_AMOUNT'],2).'</td>
						</tr>';
				$res_prog_fee->MoveNext();
			}
		//DIAM-1164
		$txt .= '<tr>
				<td width="55%" style="border-top:0.5px solid #000;">Total Unapproved Awards</td>
				<td width="30%" style="border-top:0.5px solid #000;text-align:right;" ></td>
				<td width="15%" style="border-top:0.5px solid #000;text-align:right;" >$'.number_format_value_checker($total,2).'</td>
			</tr>
		</table><br /><br />';
		
		$res_acc = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PDF_FOR = 2 ");
		/* Ticket # 1234 */
		//$txt .= nl2br(str_replace(" ","&nbsp;",$res->fields['CONTENT']));
		$txt .= '<table><tr><td style="white-space: pre-wrap;text-align: justify;">'.nl2br($res_acc->fields['CONTENT']).'</td></tr></table>';
		/* Ticket # 1234 */
		
	//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

} //FOR END

$file_name = 'Financial_Aid.pdf';
if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output($file_name, 'I');
	
//$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	
