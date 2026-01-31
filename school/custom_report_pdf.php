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

require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student.php");
require_once("../language/custom_report.php");
require_once("../language/student_contact.php");

require_once("check_access.php");

if(check_access('REPORT_CUSTOM_REPORT') == 0 ){
	header("location:../index");
	exit;
}

/* Ticket # 1623 */
$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}

$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
/* Ticket # 1623 */
	
class MYPDF extends TCPDF {
    public function Header() {
		global $db;
		
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		if($res->fields['PDF_LOGO'] != '') {
			$ext = explode(".",$res->fields['PDF_LOGO']);
			// $this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
		}
		
		$this->SetFont('helvetica', '', 15);
		$this->SetY(8);
		$this->SetX(55);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8, $res->fields['SCHOOL_NAME'], 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$this->SetFont('helvetica', 'I', 20);
		$this->SetY(8);
		$this->SetX(270);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(55, 8, "Report", 0, false, 'L', 0, '', 0, false, 'M', 'L');
		
		$res = $db->Execute("SELECT REPORT_NAME FROM S_CUSTOM_REPORT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CUSTOM_REPORT = '$_GET[id]' ");
		
		$this->SetFont('helvetica', 'I', 10);
		$this->SetY(16);
		$this->SetX(161);
		$this->SetTextColor(000, 000, 000);
		$this->Cell(130, 5, $res->fields['REPORT_NAME'].'', 0, false, 'R', 0, '', 0, false, 'M', 'L');
		
		$Y = 28;
		$X = 8;
		
		$this->SetFont('helvetica', 'BI', 8);
		$this->SetTextColor(000, 000, 000);
		$this->SetY($Y);
		
		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(220, 13, 292, 13, $style);
		
    }
    public function Footer() {
		global $db;
		
		$this->SetY(-15);
		$this->SetX(270);
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
		
		$this->SetFillColor(0, 0, 0);
		$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(5, 195, 292, 195, $style);
    }
}

$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(7, 25, 7);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setLanguageArray($l);
$pdf->setFontSubsetting(true);

$res = $db->Execute("SELECT * from S_CUSTOM_REPORT WHERE PK_CUSTOM_REPORT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res->RecordCount() == 0){
	header("location:manage_custom_report");
	exit;
}

$pdf->SetFont('helvetica', '', $res->fields['FONT_SIZE'], '', true);
$pdf->AddPage();

$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
		<thead>
			<tr>';

$fields = " S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
$res_fields = $db->Execute("SELECT FIELDS,FIELD_SIZE,SORT_ORDER from S_CUSTOM_REPORT_DETAIL WHERE PK_CUSTOM_REPORT = '$_GET[id]' AND FIELD_FOR = 'INFO' AND FIELDS != '' ORDER BY SORT_ORDER ASC, PK_CUSTOM_REPORT_DETAIL ASC");	

$SUM_OF_WIDTHS = $db->Execute("SELECT SUM(FIELD_SIZE) AS TOTAL_CONSUMED_WIDTH from S_CUSTOM_REPORT_DETAIL WHERE PK_CUSTOM_REPORT = '$_GET[id]' AND FIELDS != '' ORDER BY SORT_ORDER ASC, PK_CUSTOM_REPORT_DETAIL ASC");	
$adjustmentFactor = 100 / $SUM_OF_WIDTHS->fields['TOTAL_CONSUMED_WIDTH'];
$original_order_array = [];
while (!$res_fields->EOF){
	$field 			= $res_fields->fields['FIELDS'];
	$FIELD_SIZE[] 	= $res_fields->fields['FIELD_SIZE']*$adjustmentFactor;
	
	if($field == "CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME" ) {
		$val = STUDENT_NAME;
	} else if($field == "S_STUDENT_MASTER.OTHER_NAME AS STU_OTHER_NAME" ){
		$val = OTHER_NAME;
	} else if($field == "S_STUDENT_MASTER.PATERNAL_LAST_NAME AS PATERNAL_LAST_NAME" ){
		$val = PATERNAL_LAST_NAME;
	} else if($field == "S_STUDENT_MASTER.MATERNAL_LAST_NAME AS MATERNAL_LAST_NAME" ){
		$val = MATERNAL_LAST_NAME;
	} else if($field == "S_STUDENT_MASTER.SSN AS SSN" ){
		$val = SSN;
	} else if($field == "IF(S_STUDENT_MASTER.SSN_VERIFIED = 1,'Yes','No') AS SSN_VERIFIED" ){
		$val = SSN_VERIFIED;
	} else if($field == "IF(S_STUDENT_MASTER.DATE_OF_BIRTH != '0000-00-00',DATE_FORMAT(S_STUDENT_MASTER.DATE_OF_BIRTH,'%Y-%m-%d'),'') AS DATE_OF_BIRTH" ){
		$val = DATE_OF_BIRTH;
	} else if($field == "TIMESTAMPDIFF(YEAR, S_STUDENT_MASTER.DATE_OF_BIRTH, CURDATE()) AS AGE" ){
		$val = AGE;
	} else if($field == "Z_GENDER.GENDER AS GENDER" ){ //Ticket # 1769
		$val = GENDER;
	} else if($field == "S_STUDENT_MASTER.DRIVERS_LICENSE" ){
		$val = DRIVERS_LICENSE;
	} else if($field == "Z_STATES_DRIVERS_LICENSE.STATE_NAME AS STATES_DRIVERS_LICENSE_STATE" ){
		$val = DRIVERS_LICENSE_STATE;
	} else if($field == "Z_MARITAL_STATUS_STUD.MARITAL_STATUS AS STU_MARITAL_STATUS" ){
		$val = MARITAL_STATUS;
	} else if($field == "Z_COUNTRY_CITIZEN.NAME AS COUNTRY_CITIZEN" ){
		$val = COUNTRY_CITIZEN;
	} else if($field == "Z_CITIZENSHIP.CITIZENSHIP" ){
		$val = US_CITIZEN;
	} else if($field == "PLACE_OF_BIRTH" ){
		$val = PLACE_OF_BIRTH;
	} else if($field == "Z_STATES_OF_RESIDENCY.STATE_NAME AS STATE_OF_RESIDENCY" ){
		$val = STATE_OF_RESIDENCY;
	} else if($field == "M_STUDENT_STATUS.STUDENT_STATUS AS STUDENT_STATUS" ){
		$val = STUDENT_STATUS;
	} else if($field == "CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS EMP_NAME" ){
		$val = ADMISSION_REP;
	} else if($field == "S_STUDENT_ACADEMICS.STUDENT_ID" ){
		$val = STUDENT_ID;
	} else if($field == "S_STUDENT_ACADEMICS.ADM_USER_ID" ){
		$val = ADM_USER_ID;
	} else if($field == "M_HIGHEST_LEVEL_OF_EDU.HIGHEST_LEVEL_OF_EDU AS HIGHEST_LEVEL_OF_EDU" ){
		$val = HIGHEST_LEVEL_OF_ED;
	} else if($field == "IF(PREVIOUS_COLLEGE = 1,'Yes','No') AS PREVIOUS_COLLEGE" ){
		$val = PREVIOUS_COLLEGE;
	} else if($field == "S_STUDENT_MASTER.BADGE_ID" ){
		$val = BADGE_ID;
	} else if($field == "IF(FERPA_BLOCK = 1,'Yes',IF(FERPA_BLOCK = 2,'No','')) AS FERPA_BLOCK" ){
		$val = FERPA_BLOCK;
	} else if($field == "S_STUDENT_MASTER.IPEDS_ETHNICITY AS IPEDS_ETHNICITY" ){
		$val = IPEDS_ETHNICITY;
	} else if($field == "'' AS RACE" ){
		$val = RACE;
	} else if($field == "IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d' )) AS BEGIN_DATE" ){
		$val = FIRST_TERM_DATE;
	} else if($field == "M_CAMPUS_PROGRAM.CODE" ){
		$val = PROGRAM_CODE;
	} else if($field == "S_STUDENT_ENROLLMENT.STATUS_DATE" ){
		$val = STATUS_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.MIDPOINT_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.MIDPOINT_DATE,'%Y-%m-%d' )) AS MIDPOINT_DATE" ){
		$val = MIDPOINT_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.EXTERN_START_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.EXTERN_START_DATE,'%Y-%m-%d' )) AS EXTERN_START_DATE" ){
		$val = EXTERN_START_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.EXPECTED_GRAD_DATE,'%Y-%m-%d' )) AS EXPECTED_GRAD_DATE" ){
		$val = EXPECTED_GRAD_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.GRADE_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.GRADE_DATE,'%Y-%m-%d' )) AS GRADE_DATE" ){
		$val = GRADE_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.LDA = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.LDA,'%Y-%m-%d' )) AS LDA" ){
		$val = LDA;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.DETERMINATION_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.DETERMINATION_DATE,'%Y-%m-%d' )) AS DETERMINATION_DATE" ){
		$val = DETERMINATION_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.DROP_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.DROP_DATE,'%Y-%m-%d' )) AS DROP_DATE" ){
		$val = DROP_DATE;
	} else if($field == "M_DROP_REASON.DROP_REASON AS DROP_REASON" ){
		$val = DROP_REASON;
	} else if($field == "M_LEAD_SOURCE.LEAD_SOURCE AS LEAD_SOURCE" ){
		$val = LEAD_SOURCE;
	} else if($field == "M_LEAD_CONTACT_SOURCE.LEAD_CONTACT_SOURCE AS LEAD_CONTACT_SOURCE" ){
		$val = CONTACT_SOURCE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.CONTRACT_SIGNED_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.CONTRACT_SIGNED_DATE,'%Y-%m-%d' )) AS CONTRACT_SIGNED_DATE" ){
		$val = CONTRACT_SIGNED_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.CONTRACT_END_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.CONTRACT_END_DATE,'%Y-%m-%d' )) AS CONTRACT_END_DATE" ){
		$val = CONTRACT_END_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.ENTRY_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.ENTRY_DATE,'%Y-%m-%d' )) AS ENTRY_DATE" ){ //Ticket # 1595
		$val = ENTRY_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.ENTRY_TIME = '00:00:00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.ENTRY_TIME,'%h:%i %p' )) AS ENTRY_TIME" ){ //Ticket # 1595
		$val = ENTRY_TIME;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.ORIGINAL_EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.ORIGINAL_EXPECTED_GRAD_DATE,'%Y-%m-%d' )) AS ORIGINAL_EXPECTED_GRAD_DATE" ){
		$val = ORIGINAL_EXPECTED_GRAD_DATE;
	} else if($field == "M_ENROLLMENT_STATUS.CODE AS FULL_PART_TIME" ){
		$val = FULL_PART_TIME;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.FT_PT_EFFECTIVE_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.FT_PT_EFFECTIVE_DATE,'%Y-%m-%d' )) AS FT_PT_EFFECTIVE_DATE" ){
		$val = FT_PT_EFFECTIVE_DATE;
	} else if($field == "M_SESSION.SESSION AS SESSION" ){
		$val = SESSION;
	} else if($field == "IM_FIRST_TERM.FIRST_TERM AS FIRST_TERM" ){
		$val = FIRST_TERM;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.REENTRY = 1,'Yes','No') AS REENTRY" ){
		$val = REENTRY;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.TRANSFER_IN = 1,'Yes','No') AS TRANSFER_IN" ){
		$val = TRANSFER_IN;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.TRANSFER_OUT = 1,'Yes','No') AS TRANSFER_OUT" ){
		$val = TRANSFER_OUT;
	} else if($field == "M_DISTANCE_LEARNING.DISTANCE_LEARNING AS DISTANCE_LEARNING" ){
		$val = DISTANCE_LEARNING;
	} else if($field == "M_FUNDING.FUNDING AS FUNDING" ){
		$val = FUNDING;
	} else if($field == "M_PLACEMENT_STATUS.PLACEMENT_STATUS AS PLACEMENT_STATUS" ){
		$val = PLACEMENT_STATUS;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.STRF_PAID_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.STRF_PAID_DATE,'%Y-%m-%d' )) AS STRF_PAID_DATE" ){
		$val = STRF_PAID_DATE;
	} else if($field == "M_STUDENT_GROUP.STUDENT_GROUP AS STUDENT_GROUP" ){
		$val = STUDENT_GROUP;
	} else if($field == "M_SPECIAL_PROGRAM_INDICATOR.CODE AS SPECIAL_PROGRAM_INDICATOR" ){
		$val = SPECIAL_PROGRAM_INDICATOR;
	} else if($field == "''AS CAMPUS" ){
		$val = CAMPUS;
	} else if($field == "S_STUDENT_CONTACT.ADDRESS AS ADDRESS" ){
		$val = ADDRESS;
	} else if($field == "S_STUDENT_CONTACT.ADDRESS_1 AS ADDRESS_1" ){
		$val = ADDRESS_1;
	} else if($field == "S_STUDENT_CONTACT.CITY AS CITY" ){
		$val = CITY;
	} else if($field == "CONTACT_STATE.STATE_NAME AS CONTACT_STATE_NAME" ){
		$val = STATE;
	} else if($field == "S_STUDENT_CONTACT.ZIP AS ZIP" ){
		$val = ZIP;
	} else if($field == "CONTACT_COUNTRY.NAME AS CONTACT_COUNTRY_NAME" ){
		$val = COUNTRY;
	} else if($field == "IF(S_STUDENT_CONTACT.ADDRESS_INVALID = 1, 'Yes', '') AS ADDRESS_INVALID" ){
		$val = ADDRESS.' '.INVALID;
	} else if($field == "S_STUDENT_CONTACT.HOME_PHONE AS HOME_PHONE" ){
		$val = HOME_PHONE;
	} else if($field == "If(S_STUDENT_CONTACT.HOME_PHONE_INVALID = 1, 'Yes', '') AS HOME_PHONE_INVALID" ){
		$val = HOME_PHONE.' '.INVALID;
	} else if($field == "S_STUDENT_CONTACT.WORK_PHONE AS WORK_PHONE" ){
		$val = WORK_PHONE;
	} else if($field == "IF(S_STUDENT_CONTACT.WORK_PHONE_INVALID = 1, 'Yes', '') AS WORK_PHONE_INVALID" ){
		$val = WORK_PHONE.' '.INVALID;
	} else if($field == "S_STUDENT_CONTACT.CELL_PHONE AS CELL_PHONE" ){
		$val = CELL_PHONE;
	} else if($field == "IF(S_STUDENT_CONTACT.OPT_OUT = 1, 'Yes', '') AS OPT_OUT" ){
		$val = OPTOUT;
	} else if($field == "IF(S_STUDENT_CONTACT.CELL_PHONE_INVALID = 1, 'Yes', '') AS CELL_PHONE_INVALID" ){
		$val = CELL_PHONE.' '.INVALID;
	} else if($field == "S_STUDENT_CONTACT.OTHER_PHONE AS OTHER_PHONE" ){
		$val = OTHER_PHONE;
	} else if($field == "IF(S_STUDENT_CONTACT.OTHER_PHONE_INVALID = 1, 'Yes', '') AS OTHER_PHONE_INVALID" ){
		$val = OTHER_PHONE.' '.INVALID;
	} else if($field == "S_STUDENT_CONTACT.EMAIL AS EMAIL" ){
		$val = EMAIL;
	} else if($field == "IF(S_STUDENT_CONTACT.USE_EMAIL = 1, 'Yes', '') AS USE_EMAIL" ){
		$val = USE_EMAIL;
	} else if($field == "IF(S_STUDENT_CONTACT.EMAIL_INVALID = 1, 'Yes', '') AS EMAIL_INVALID" ){
		$val = EMAIL.' '.INVALID;
	} else if($field == "S_STUDENT_CONTACT.EMAIL_OTHER AS EMAIL_OTHER" ){
		$val = EMAIL_OTHER;
	} else if($field == "IF(S_STUDENT_CONTACT.EMAIL_OTHER_INVALID = 1, 'Yes', '') AS EMAIL_OTHER_INVALID" ){
		$val = OTHER_EMAIL.' '.INVALID;
	} else if($field == "S_STUDENT_MASTER.FIRST_NAME AS STU_FIRST_NAME" ){
		$val = FIRST_NAME;
	} else if($field == "S_STUDENT_MASTER.LAST_NAME AS STU_LAST_NAME" ){
		$val = LAST_NAME;
	} else if($field == "M_CAMPUS_PROGRAM.DESCRIPTION as PROGRAM_DESC" ){
		$val = PROGRAM_DESCRIPTION;
	} else if($field == "S_STUDENT_MASTER.SSN AS SSN_1" ){
		$val = SSN_DISPLAY_FULL;
	} else if($field == "OLD_DSIS_STU_NO" ){
		$val = OLD_DSIS_STU_NO;
	} else if($field == "OLD_DSIS_LEAD_ID" ){
		$val = OLD_DSIS_LEAD_ID;
	} else if($field == "MES.CODE AS ORIGINAL_ENROLLMENT_STATUS" ){ // DIAM-2366
		$val = ORIGINAL_ENROLLMENT_STATUS;
	} else if($field == "M_RESIDENCY_TUITION_STATUS.DESCRIPTION_CODE AS RESIDENCY_TUITION_STATUS" ){ // DIAM-2370
		$val = RESIDENCY_TUITION_STATUS;
    } else if($field == "S_STUDENT_MASTER.LSQ_ID AS LSQ_ID" ){
        $val = "LSQ ID";
    } else if($field == "(SELECT JSON_UNQUOTE(JSON_EXTRACT(LSQ_DATA, '$.ProspectAutoId')) FROM S_STUDENT_LSQ WHERE S_STUDENT_LSQ.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER LIMIT 1) AS PROSPECT_AUTO_ID" ){
        $val = "Prospect Auto ID";
	} else
		$val = "";
	
	$original_order_array[] = ['<td width="'.($res_fields->fields['FIELD_SIZE']*$adjustmentFactor).'%" style="border-top:1px solid #000;border-bottom:1px solid #000;"><b><i>'.$val.'</i></b></td>', $res_fields->fields['SORT_ORDER']];
	
	
	$fields .= ','.$field;

	$res_fields->MoveNext();
}

$custom_fields_type_arr = array();
$custom_fields_size_arr = array();
$custom_fields_arr 		= array();
$custom_fields_tab_arr 	= array(); //DIAM-2154

$res_fields = $db->Execute("SELECT FIELD_NAME, FIELDS, PK_DATA_TYPES, TAB, FIELD_SIZE , SORT_ORDER from S_CUSTOM_REPORT_DETAIL, S_CUSTOM_FIELDS WHERE PK_CUSTOM_REPORT = '$_GET[id]' AND FIELD_FOR = 'CUSTOM_FIELDS' AND FIELDS != '' AND S_CUSTOM_REPORT_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_CUSTOM_REPORT_DETAIL.FIELDS = S_CUSTOM_FIELDS.PK_CUSTOM_FIELDS  AND S_CUSTOM_FIELDS.ACTIVE = 1 ORDER BY SORT_ORDER ASC, PK_CUSTOM_REPORT_DETAIL ASC"); //DIAM-2154	
while (!$res_fields->EOF){
	$custom_fields_arr[] 		= $res_fields->fields['FIELDS']; 
	$custom_fields_type_arr[] 	= $res_fields->fields['PK_DATA_TYPES'];
	$custom_fields_size_arr[] 	= $res_fields->fields['FIELD_SIZE'];
	$custom_fields_tab_arr[] 	= strtolower($res_fields->fields['TAB']); //DIAM-2154
	
	$original_order_array[] = ['<td width="'.$res_fields->fields['FIELD_SIZE'].'%" style="border-top:1px solid #000;border-bottom:1px solid #000;"><b><i>'.$res_fields->fields['FIELD_NAME'].'</i></b></td>' , $res_fields->fields['SORT_ORDER']];
	// $txt .= '<td width="'.$res_fields->fields['FIELD_SIZE'].'%" style="border-top:1px solid #000;border-bottom:1px solid #000;"><b><i>'.$res_fields->fields['FIELD_NAME'].'</i></b></td>';
		
	$res_fields->MoveNext();
}

// print_r($original_order_array);exit;
function customSort(&$fields) {
	// echo "<pre>";
    // echo "original order";
    // dump($fields); 
    foreach ($fields as $key => $value) {
        $fields[$key]['original_order'] = $key;
    }
    // Sort fields array based on order values
    usort($fields, function ($a, $b) {
        if ($a[1] === $b[1]) {
            return 0;
        }
        if ($a[1] === '') {
            return 1;
        }
        if ($b[1] === '') {
            return -1;
        }
        return $a[1] < $b[1] ? -1 : 1;
    });

	// echo "After sorting order";
    // dump($fields);
    
}
customSort($original_order_array);
foreach ($original_order_array as $key => $value) {
	$txt .= $value[0];
}


$txt .= '</thead>
	</tr>';

$REPORT_NAME			= $res->fields['REPORT_NAME'];
$SSN					= $res->fields['SSN'];
$SSN_VERIFIED			= $res->fields['SSN_VERIFIED'];
$GENDER					= $res->fields['GENDER'];
$PK_MARITAL_STATUS		= $res->fields['PK_MARITAL_STATUS'];
$PK_COUNTRY_CITIZEN		= $res->fields['PK_COUNTRY_CITIZEN'];
$PK_CITIZENSHIP			= $res->fields['PK_CITIZENSHIP'];
$PK_STATE_OF_RESIDENCY	= $res->fields['PK_STATE_OF_RESIDENCY'];
$PK_STUDENT_STATUS		= $res->fields['PK_STUDENT_STATUS'];
$PK_EMPLOYEE_MASTER		= $res->fields['PK_EMPLOYEE_MASTER'];

$PK_CAMPUS				= $res->fields['PK_CAMPUS'];
$PK_FUNDING				= $res->fields['PK_FUNDING'];
$PK_LEAD_SOURCE			= $res->fields['PK_LEAD_SOURCE'];
$PK_SESSION				= $res->fields['PK_SESSION'];
$PK_STUDENT_GROUP		= $res->fields['PK_STUDENT_GROUP'];
$PK_STUDENT_GROUP		= $res->fields['PK_STUDENT_GROUP'];

$PK_CAMPUS_PROGRAM		= $res->fields['PK_CAMPUS_PROGRAM'];
$PK_TERM_MASTER			= $res->fields['PK_TERM_MASTER'];
$LEAD_ENTRY_FROM_DATE	= $res->fields['LEAD_ENTRY_FROM_DATE'];
$LEAD_ENTRY_END_DATE	= $res->fields['LEAD_ENTRY_END_DATE'];

if($LEAD_ENTRY_FROM_DATE == '0000-00-00')
	$LEAD_ENTRY_FROM_DATE = '';
	
if($LEAD_ENTRY_END_DATE == '0000-00-00')
	$LEAD_ENTRY_END_DATE = '';

$GROUP_BY_FIELD			= $res->fields['GROUP_BY_FIELD'];

$cond = "";
if($SSN == 1)
	$cond .= " AND S_STUDENT_MASTER.SSN != '' ";
else if($SSN == 2)
	$cond .= " AND S_STUDENT_MASTER.SSN = '' ";
	
if($SSN_VERIFIED == 1)
	$cond .= " AND S_STUDENT_MASTER.SSN_VERIFIED = 1 ";
else if($SSN == 2)
	$cond .= " AND S_STUDENT_MASTER.SSN_VERIFIED = 0 ";
	
if($GENDER > 0 )
	$cond .= " AND S_STUDENT_MASTER.GENDER = '$GENDER' ";

if($PK_MARITAL_STATUS != '' )
	$cond .= " AND S_STUDENT_MASTER.PK_MARITAL_STATUS IN ($PK_MARITAL_STATUS) ";
	
if($PK_COUNTRY_CITIZEN > 0 )
	$cond .= " AND S_STUDENT_MASTER.PK_COUNTRY_CITIZEN = '$PK_COUNTRY_CITIZEN' ";
	
if($PK_CITIZENSHIP != '' )
	$cond .= " AND S_STUDENT_MASTER.PK_CITIZENSHIP IN ($PK_CITIZENSHIP) ";
	
if($PK_STATE_OF_RESIDENCY != '' )
	$cond .= " AND S_STUDENT_MASTER.PK_STATE_OF_RESIDENCY IN ($PK_STATE_OF_RESIDENCY) ";
	
if($PK_STUDENT_STATUS != '' )
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($PK_STUDENT_STATUS) ";
	
if($PK_EMPLOYEE_MASTER != '' )
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE IN ($PK_EMPLOYEE_MASTER) ";	
	
if($PK_CAMPUS_PROGRAM != '' )
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN ($PK_CAMPUS_PROGRAM) ";
	
if($PK_TERM_MASTER != '' )
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER IN ($PK_TERM_MASTER) ";
	
if($LEAD_ENTRY_FROM_DATE != '' && $LEAD_ENTRY_END_DATE != '') {
	$cond .= " AND ENTRY_DATE BETWEEN '$LEAD_ENTRY_FROM_DATE' AND '$LEAD_ENTRY_END_DATE' ";
} else if($LEAD_ENTRY_FROM_DATE != '') {
	$cond .= " AND ENTRY_DATE >= '$LEAD_ENTRY_FROM_DATE' ";
} else if($LEAD_ENTRY_END_DATE != '') {
	$cond .= " AND ENTRY_DATE <='$LEAD_ENTRY_END_DATE' ";
}

$left_join  = "";
$camp_cond  = "";
$GROUP_BY 	= "";
/////////////////
if($PK_CAMPUS != '' || $GROUP_BY_FIELD == 1 ) {
	
	if($PK_CAMPUS != '') {
		$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
		$camp_cond = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
		$GROUP_BY  = " GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT";
	}
	
	$left_join .= " LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
}
	
if($PK_FUNDING != '' )
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_FUNDING IN ($PK_FUNDING) ";	
	
if($PK_LEAD_SOURCE != '' )
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE IN ($PK_LEAD_SOURCE) ";	
	
if($PK_SESSION != '' )
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_SESSION IN ($PK_SESSION) ";	
	
if($PK_STUDENT_GROUP != '' )
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP IN ($PK_STUDENT_GROUP) ";	
	
/////////////////
	
/*$ORDER_BY = "";
if($GROUP_BY_FIELD == 1)
	$ORDER_BY = " S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS ASC ";
else if($GROUP_BY_FIELD == 2)
	$ORDER_BY = " S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE ASC ";
	
if($ORDER_BY != '')
	$ORDER_BY = ' ORDER BY '.$ORDER_BY;*/
	
$ORDER_BY = " ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC ";
$GROUP_BY_COND  = array();
$GROUP_BY_TITLE = array();

/* Ticket # 1762 - contact source change */
/* Ticket # 1769 - gender change */
$query = "S_STUDENT_MASTER 
LEFT JOIN Z_GENDER On Z_GENDER.PK_GENDER = S_STUDENT_MASTER.GENDER  
LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1'
LEFT JOIN Z_STATES AS CONTACT_STATE ON CONTACT_STATE.PK_STATES = S_STUDENT_CONTACT.PK_STATES 
LEFT JOIN Z_COUNTRY AS CONTACT_COUNTRY ON CONTACT_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY 
LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
LEFT JOIN M_HIGHEST_LEVEL_OF_EDU ON M_HIGHEST_LEVEL_OF_EDU.PK_HIGHEST_LEVEL_OF_EDU = S_STUDENT_ACADEMICS.PK_HIGHEST_LEVEL_OF_EDU 
LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
LEFT JOIN M_LEAD_CONTACT_SOURCE ON M_LEAD_CONTACT_SOURCE.PK_LEAD_CONTACT_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_CONTACT_SOURCE 
$left_join 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER
LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
LEFT JOIN M_FIRST_TERM ON M_FIRST_TERM.PK_FIRST_TERM = S_STUDENT_ENROLLMENT.FIRST_TERM 
LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM
LEFT JOIN M_DROP_REASON ON M_DROP_REASON.PK_DROP_REASON = S_STUDENT_ENROLLMENT.PK_DROP_REASON
LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE 
LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE  
LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS
LEFT JOIN M_ENROLLMENT_STATUS AS MES ON S_STUDENT_ENROLLMENT.ORIGINAL_ENROLLMENT_STATUS = MES.PK_ENROLLMENT_STATUS
LEFT JOIN M_RESIDENCY_TUITION_STATUS ON M_RESIDENCY_TUITION_STATUS.PK_RESIDENCY_TUITION_STATUS = S_STUDENT_ENROLLMENT.PK_RESIDENCY_TUITION_STATUS
LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION 
LEFT JOIN M_DISTANCE_LEARNING ON M_DISTANCE_LEARNING.PK_DISTANCE_LEARNING = S_STUDENT_ENROLLMENT.PK_DISTANCE_LEARNING 
LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
LEFT JOIN M_PLACEMENT_STATUS ON M_PLACEMENT_STATUS.PK_PLACEMENT_STATUS = S_STUDENT_ENROLLMENT.PK_PLACEMENT_STATUS 
LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP  
LEFT JOIN M_SPECIAL_PROGRAM_INDICATOR ON M_SPECIAL_PROGRAM_INDICATOR.PK_SPECIAL_PROGRAM_INDICATOR = S_STUDENT_ENROLLMENT.PK_SPECIAL_PROGRAM_INDICATOR  
LEFT JOIN Z_STATES AS Z_STATES_DRIVERS_LICENSE ON Z_STATES_DRIVERS_LICENSE.PK_STATES = S_STUDENT_MASTER.PK_DRIVERS_LICENSE_STATE 
LEFT JOIN Z_MARITAL_STATUS AS Z_MARITAL_STATUS_STUD ON Z_MARITAL_STATUS_STUD.PK_MARITAL_STATUS = S_STUDENT_MASTER.PK_MARITAL_STATUS 
LEFT JOIN Z_COUNTRY AS Z_COUNTRY_CITIZEN ON Z_COUNTRY_CITIZEN.PK_COUNTRY = S_STUDENT_MASTER.PK_COUNTRY_CITIZEN 
LEFT JOIN Z_CITIZENSHIP ON Z_CITIZENSHIP.PK_CITIZENSHIP = S_STUDENT_MASTER.PK_CITIZENSHIP 
LEFT JOIN Z_STATES AS Z_STATES_OF_RESIDENCY ON Z_STATES_OF_RESIDENCY.PK_STATES = S_STUDENT_MASTER.PK_STATE_OF_RESIDENCY 
WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ACTIVE = 1 AND S_STUDENT_MASTER.ARCHIVED = 0 $cond ";
/* Ticket # 1762 - contact source change */
/* Ticket # 1769 - gender change */

if($GROUP_BY_FIELD == 1) {
	$res = $db->Execute("SELECT S_STUDENT_CAMPUS.PK_CAMPUS FROM ".$query." AND S_STUDENT_CAMPUS.PK_CAMPUS IS NOT NULL GROUP BY S_STUDENT_CAMPUS.PK_CAMPUS ");
	while (!$res->EOF){
		$res2 = $db->Execute("SELECT OFFICIAL_CAMPUS_NAME FROM S_CAMPUS WHERE PK_CAMPUS = '".$res->fields['PK_CAMPUS']."' ");
		
		$GROUP_BY_COND[]  = " AND S_STUDENT_CAMPUS.PK_CAMPUS = '".$res->fields['PK_CAMPUS']."' ";
		$GROUP_BY_TITLE[] = CAMPUS.": ".$res2->fields['OFFICIAL_CAMPUS_NAME'];
		
		$res->MoveNext();
	}
} else if($GROUP_BY_FIELD == 2) {
	$res = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_TERM_MASTER, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS BEGIN_DATE FROM ".$query." AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER IS NOT NULL GROUP BY S_STUDENT_ENROLLMENT.PK_TERM_MASTER ORDER BY BEGIN_DATE ASC");
	while (!$res->EOF){
		$GROUP_BY_COND[]  = " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER = '".$res->fields['PK_TERM_MASTER']."' ";
		$GROUP_BY_TITLE[] = FIRST_TERM_DATE.": ".$res->fields['BEGIN_DATE'];
		
		$res->MoveNext();
	}
} else if($GROUP_BY_FIELD == 3) {
	$res = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_FUNDING, FUNDING FROM ".$query." AND S_STUDENT_ENROLLMENT.PK_FUNDING IS NOT NULL GROUP BY S_STUDENT_ENROLLMENT.PK_FUNDING ORDER BY FUNDING ASC");
	while (!$res->EOF){
		$GROUP_BY_COND[]  = " AND S_STUDENT_ENROLLMENT.PK_FUNDING = '".$res->fields['PK_FUNDING']."' ";
		$GROUP_BY_TITLE[] = FUNDING.": ".$res->fields['FUNDING'];
		
		$res->MoveNext();
	}
} else if($GROUP_BY_FIELD == 4) {

	/* Ticket # 1769 */
	$res = $db->Execute("SELECT PK_GENDER, GENDER FROM Z_GENDER WHERE 1=1 ");
	while (!$res->EOF){
		$GROUP_BY_COND[]  = " AND S_STUDENT_MASTER.GENDER = '".$res->fields['PK_GENDER']."' ";
		$GROUP_BY_TITLE[] = GENDER.": ".$res->fields['GENDER'];
		
		$res->MoveNext();
	}
	/* Ticket # 1769 */

} else if($GROUP_BY_FIELD == 5) {

	$res = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE, LEAD_SOURCE FROM ".$query." AND S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE IS NOT NULL GROUP BY S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE ORDER BY LEAD_SOURCE ASC");
	while (!$res->EOF){
		$GROUP_BY_COND[]  = " AND S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE = '".$res->fields['PK_LEAD_SOURCE']."' ";
		$GROUP_BY_TITLE[] = LEAD_SOURCE.": ".$res->fields['LEAD_SOURCE'];
		
		$res->MoveNext();
	}

} else if($GROUP_BY_FIELD == 6) {

	$res = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME FROM ".$query." AND S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE IS NOT NULL GROUP BY S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE ORDER BY CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) ASC");
	while (!$res->EOF){
		$GROUP_BY_COND[]  = " AND S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE = '".$res->fields['PK_REPRESENTATIVE']."' ";
		$GROUP_BY_TITLE[] = ADMISSIONS_REP.": ".$res->fields['NAME'];
		
		$res->MoveNext();
	}

} else if($GROUP_BY_FIELD == 7) {

	$res = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM,M_CAMPUS_PROGRAM.CODE FROM ".$query." AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IS NOT NULL GROUP BY S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM ORDER BY M_CAMPUS_PROGRAM.CODE ASC");
	while (!$res->EOF){
		$GROUP_BY_COND[]  = " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = '".$res->fields['PK_CAMPUS_PROGRAM']."' ";
		$GROUP_BY_TITLE[] = PROGRAM.": ".$res->fields['CODE'];
		
		$res->MoveNext();
	}

} else if($GROUP_BY_FIELD == 8) {
	$res = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_SESSION,M_SESSION.SESSION FROM ".$query." AND S_STUDENT_ENROLLMENT.PK_SESSION IS NOT NULL GROUP BY S_STUDENT_ENROLLMENT.PK_SESSION ORDER BY M_SESSION.SESSION ASC");
	while (!$res->EOF){
		$GROUP_BY_COND[]  = " AND S_STUDENT_ENROLLMENT.PK_SESSION = '".$res->fields['PK_SESSION']."' ";
		$GROUP_BY_TITLE[] = SESSION.": ".$res->fields['SESSION'];
		
		$res->MoveNext();
	}

} else if($GROUP_BY_FIELD == 9) {
	$res = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP,M_STUDENT_GROUP.STUDENT_GROUP FROM ".$query." AND S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP IS NOT NULL GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP ORDER BY M_STUDENT_GROUP.STUDENT_GROUP ASC");
	while (!$res->EOF){
		$GROUP_BY_COND[]  = " AND S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP = '".$res->fields['PK_STUDENT_GROUP']."' ";
		$GROUP_BY_TITLE[] = STUDENT_GROUP.": ".$res->fields['STUDENT_GROUP'];
		
		$res->MoveNext();
	}

} else if($GROUP_BY_FIELD == 10) {
	$res = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS,STUDENT_STATUS FROM ".$query." AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IS NOT NULL  GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS ORDER BY STUDENT_STATUS ASC");
	while (!$res->EOF){
		$GROUP_BY_COND[]  = " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS = '".$res->fields['PK_STUDENT_STATUS']."' ";
		$GROUP_BY_TITLE[] = STUDENT_STATUS.": ".$res->fields['STUDENT_STATUS'];
		
		$res->MoveNext();
	}

} else {
	$GROUP_BY_COND[]  = " AND 1=1 ";
	$GROUP_BY_TITLE[] = "";
}

//echo "<pre>";print_r($GROUP_BY_COND);print_r($GROUP_BY_TITLE);exit;
$kln = 0;
$data_rows = [];
foreach($GROUP_BY_COND as $GROUP_BY_COND1){
	if($GROUP_BY_FIELD != '') {
		$txt .= '<tr >
					<td width="100%" ><b style="font-size:50px"><i>'.$GROUP_BY_TITLE[$kln].'</i></b></td>
				</tr>';
	}
	
	$res = $db->Execute("SELECT $fields FROM ".$query." $GROUP_BY_COND1 $GROUP_BY $ORDER_BY  "); 
	while (!$res->EOF){
		$current_row = [];

		$PK_STUDENT_MASTER 		= $res->fields['PK_STUDENT_MASTER'];
		$PK_STUDENT_ENROLLMENT 	= $res->fields['PK_STUDENT_ENROLLMENT'];
		$txt .= '<tr>';
		
		$i = 0;
		foreach($res->fields as $key => $value){
			if($key != 'PK_STUDENT_MASTER' && $key != 'PK_STUDENT_ENROLLMENT') {
				if($key == 'SSN') {
					if($value != '') {
						$SSN_1 	 = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$value);
						$SSN_ARR = explode("-",$SSN_1);
						$value 	 = 'xxx-xx-'.$SSN_ARR[2];
					}
				} else if($key == 'SSN_1') {
					if($value != '')
						$value = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$value);
				} else if($key == 'RACE') {
					$value = '';
					$res_race = $db->Execute("select RACE FROM S_STUDENT_RACE, Z_RACE WHERE S_STUDENT_RACE.PK_RACE = Z_RACE.PK_RACE AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					while (!$res_race->EOF){
						if($value != '')
							$value .= ', ';
						$value .= $res_race->fields['RACE'];
						
						$res_race->MoveNext();
					}
				} else if($key == 'CAMPUS') {
					$value = '';
					$res_race = $db->Execute("select OFFICIAL_CAMPUS_NAME FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_CAMPUS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT > 0 $camp_cond ");
					while (!$res_race->EOF){
						if($value != '')
							$value .= ', ';
						$value .= $res_race->fields['OFFICIAL_CAMPUS_NAME'];
						
						$res_race->MoveNext();
					}
				} else if($key == 'STATUS_DATE') {
					if($value != '' && $value != '0000-00-00')
						$value = date("Y-m-d",strtotime($value));
					else
						$value = '';
				} else if($key == 'ENTRY_TIME') { /* Ticket # 1623 */
					$ENTRY_TIME = trim($value);
					if($ENTRY_TIME != '' && $ENTRY_TIME != '00:00:00') {
						$ENTRY_TIME = date("H:i", strtotime($ENTRY_TIME));
						$ENTRY_TIME = convert_to_user_date(date("Y-m-d ").$ENTRY_TIME,'h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
						$value		= $ENTRY_TIME;
					}
				} else if($key == 'RESIDENCY_TUITION_STATUS') { /* DIAM-2370 */
					$RESIDENCY_TUITION_STATUS = trim($value);
					if($RESIDENCY_TUITION_STATUS != '' ) {
						$value		= $RESIDENCY_TUITION_STATUS;
					}
					else{
						$value 		= 'Not Set';
					}
				} 
				/* Ticket # 1623 */
				
				$width = $FIELD_SIZE[$i];
				$current_row[] = '<td width="'.$width.'%" >'.trim($value).'</td>';
				// $txt .= '<td width="'.$width.'%" >'.trim($value).'</td>';
				
				$i++;
				
			}
		}
		
		foreach($custom_fields_arr as $indes_2 => $PK_CUSTOM_FIELDS){ 
			$value = "";
			$cust_en_cond = ""; //DIAM-2154
		//DIAM-2154												
		if(in_array('other', $custom_fields_tab_arr[$indes_2]))
			$cust_en_cond = " AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "; //DIAM-2154
			

			if($custom_fields_type_arr[$indes_2] == 1 || $custom_fields_type_arr[$indes_2] == 4) { 
				//Text, Date
				$res_stu_cus = $db->Execute("select FIELD_VALUE FROM S_STUDENT_CUSTOM_FIELDS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' $cust_en_cond "); //DIAM-2154
				$value = $res_stu_cus->fields['FIELD_VALUE'];
				
				if($custom_fields_type_arr[$indes_2] == 4 && $value != ''){
					$value = date("m/d/Y",strtotime($value));
				}
			} else if($custom_fields_type_arr[$indes_2] == 2) { 
				//Drop Down
				$res_stu_cus = $db->Execute("select OPTION_NAME FROM S_STUDENT_CUSTOM_FIELDS, S_USER_DEFINED_FIELDS_DETAIL WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' AND PK_USER_DEFINED_FIELDS_DETAIL =  FIELD_VALUE $cust_en_cond"); //DIAM-2154
				$value = $res_stu_cus->fields['OPTION_NAME'];
				
			} else if($custom_fields_type_arr[$indes_2] == 3) { 
				//Multiple Choice
				$res_stu_cus = $db->Execute("select FIELD_VALUE FROM S_STUDENT_CUSTOM_FIELDS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' $cust_en_cond"); //DIAM-2154
				$value = $res_stu_cus->fields['FIELD_VALUE'];
				
				$res_stu_cus = $db->Execute("select GROUP_CONCAT(OPTION_NAME ORDER BY OPTION_NAME ASC SEPARATOR ', ') as OPTION_NAME FROM S_USER_DEFINED_FIELDS_DETAIL WHERE PK_USER_DEFINED_FIELDS_DETAIL IN ($value)  ");
				$value = $res_stu_cus->fields['OPTION_NAME'];
			}
			
			$width = $custom_fields_size_arr[$indes_2];
			$current_row[] = '<td width="'.$width.'%" >'.trim($value).'</td>';
			// $txt .= '<td width="'.$width.'%" >'.trim($value).'</td>';
		}
		$tmp_arry = [];
		foreach ($original_order_array as $org_key => $org_value) {
			$tmp_arry[$org_key] = $current_row[$org_value['original_order']];
		}
		$txt .= implode(" " , $tmp_arry);
		
		$txt .= '</tr>';
		
		$res->MoveNext();
	}
	$kln++;
}

$txt .= '</table>';

//echo $txt;exit;
$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

$REPORT_NAME = str_replace("/","_",$REPORT_NAME);
$REPORT_NAME = str_replace(":","_",$REPORT_NAME);
$REPORT_NAME = str_replace("?","_",$REPORT_NAME);
$REPORT_NAME = str_replace("*","_",$REPORT_NAME);
$REPORT_NAME = str_replace("<","_",$REPORT_NAME);
$REPORT_NAME = str_replace(">","_",$REPORT_NAME);
$REPORT_NAME = str_replace("|","_",$REPORT_NAME);

$file_name = $REPORT_NAME.'.pdf';
$file_name = str_replace(pathinfo($file_name,PATHINFO_FILENAME), pathinfo($file_name,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$file_name);
/*
if($browser == 'Safari')
	$pdf->Output('temp/'.$file_name, 'FD');
else	
	$pdf->Output('temp/'.$file_name, 'FI');
*/	
$pdf->Output('temp/'.$file_name, 'FD');
return $file_name;	
