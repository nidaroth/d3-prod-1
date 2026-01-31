<?php require_once('../global/config.php');
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

include '../global/excel/Classes/PHPExcel/IOFactory.php';
$cell1  = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");		
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

$total_fields = 200;
for($i = 0 ; $i <= $total_fields ; $i++){
	if($i <= 25)
		$cell[] = $cell1[$i];
	else {
		$j = floor($i / 26) - 1;
		$k = ($i % 26);
		//echo $j."--".$k."<br />";
		$cell[] = $cell1[$j].$cell1[$k];
	}	
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

$res = $db->Execute("SELECT * from S_CUSTOM_REPORT WHERE PK_CUSTOM_REPORT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res->RecordCount() == 0){
	header("location:manage_custom_report");
	exit;
}

$SSN					= $res->fields['SSN'];
$SSN_VERIFIED			= $res->fields['SSN_VERIFIED'];
$GENDER					= $res->fields['GENDER'];
$PK_MARITAL_STATUS		= $res->fields['PK_MARITAL_STATUS'];
$PK_COUNTRY_CITIZEN		= $res->fields['PK_COUNTRY_CITIZEN'];
$PK_CITIZENSHIP			= $res->fields['PK_CITIZENSHIP'];
$PK_STATE_OF_RESIDENCY	= $res->fields['PK_STATE_OF_RESIDENCY'];
$PK_STUDENT_STATUS		= $res->fields['PK_STUDENT_STATUS'];
$PK_EMPLOYEE_MASTER		= $res->fields['PK_EMPLOYEE_MASTER'];

$PK_CAMPUS_PROGRAM		= $res->fields['PK_CAMPUS_PROGRAM'];
$PK_TERM_MASTER			= $res->fields['PK_TERM_MASTER'];
$LEAD_ENTRY_FROM_DATE	= $res->fields['LEAD_ENTRY_FROM_DATE'];
$LEAD_ENTRY_END_DATE	= $res->fields['LEAD_ENTRY_END_DATE'];

$PK_CAMPUS				= $res->fields['PK_CAMPUS'];
$PK_FUNDING				= $res->fields['PK_FUNDING'];
$PK_LEAD_SOURCE			= $res->fields['PK_LEAD_SOURCE'];
$PK_SESSION				= $res->fields['PK_SESSION'];
$PK_STUDENT_GROUP		= $res->fields['PK_STUDENT_GROUP'];
$PK_STUDENT_GROUP		= $res->fields['PK_STUDENT_GROUP'];

if($LEAD_ENTRY_FROM_DATE == '0000-00-00')
	$LEAD_ENTRY_FROM_DATE = '';
	
if($LEAD_ENTRY_END_DATE == '0000-00-00')
	$LEAD_ENTRY_END_DATE = '';

$GROUP_BY_FIELD			= $res->fields['GROUP_BY_FIELD'];

$dir 			= 'temp/';
$inputFileType  = 'Excel2007';

$REPORT_NAME = $res->fields['REPORT_NAME'];
$REPORT_NAME = str_replace("/","_",$REPORT_NAME);
$REPORT_NAME = str_replace(":","_",$REPORT_NAME);
$REPORT_NAME = str_replace("?","_",$REPORT_NAME);
$REPORT_NAME = str_replace("*","_",$REPORT_NAME);
$REPORT_NAME = str_replace("<","_",$REPORT_NAME);
$REPORT_NAME = str_replace(">","_",$REPORT_NAME);
$REPORT_NAME = str_replace("|","_",$REPORT_NAME);

$file_name 		= $REPORT_NAME.'.xlsx';
$outputFileName = $dir.$file_name ;

$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setIncludeCharts(TRUE);
//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
$objPHPExcel = new PHPExcel();
$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$line 	= 1;	
$index 	= -1;

$fields = "S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
$res_fields = $db->Execute("SELECT FIELDS from S_CUSTOM_REPORT_DETAIL WHERE PK_CUSTOM_REPORT = '$_GET[id]' AND FIELD_FOR = 'INFO' AND FIELDS != '' ORDER BY SORT_ORDER ASC, PK_CUSTOM_REPORT_DETAIL ASC ");	
while (!$res_fields->EOF){
	$field = $res_fields->fields['FIELDS'];
	
	if($field == "CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME" ) {
		$val = STUDENT_NAME;
	} else if($field == "S_STUDENT_MASTER.OTHER_NAME AS STU_OTHER_NAME" ){
		$val = OTHER_NAME;
	} else if($field == "S_STUDENT_MASTER.SSN AS SSN" ){
		$val = SSN;
	} else if($field == "IF(S_STUDENT_MASTER.SSN_VERIFIED = 1,'Yes','No') AS SSN_VERIFIED" ){
		$val = SSN_VERIFIED;
	} else if($field == "IF(S_STUDENT_MASTER.DATE_OF_BIRTH != '0000-00-00',DATE_FORMAT(S_STUDENT_MASTER.DATE_OF_BIRTH,'%m/%d/%Y'),'') AS DATE_OF_BIRTH" ){
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
	} else if($field == "IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS BEGIN_DATE" ){
		$val = FIRST_TERM_DATE;
	} else if($field == "M_CAMPUS_PROGRAM.CODE" ){
		$val = PROGRAM_CODE;
	} else if($field == "S_STUDENT_ENROLLMENT.STATUS_DATE" ){
		$val = STATUS_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.MIDPOINT_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.MIDPOINT_DATE,'%m/%d/%Y' )) AS MIDPOINT_DATE" ){
		$val = MIDPOINT_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.EXTERN_START_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.EXTERN_START_DATE,'%m/%d/%Y' )) AS EXTERN_START_DATE" ){
		$val = EXTERN_START_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.EXPECTED_GRAD_DATE,'%m/%d/%Y' )) AS EXPECTED_GRAD_DATE" ){
		$val = EXPECTED_GRAD_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.GRADE_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.GRADE_DATE,'%m/%d/%Y' )) AS GRADE_DATE" ){
		$val = GRADE_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.LDA = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.LDA,'%m/%d/%Y' )) AS LDA" ){
		$val = LDA;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.DETERMINATION_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.DETERMINATION_DATE,'%m/%d/%Y' )) AS DETERMINATION_DATE" ){
		$val = DETERMINATION_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.DROP_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.DROP_DATE,'%m/%d/%Y' )) AS DROP_DATE" ){
		$val = DROP_DATE;
	} else if($field == "M_DROP_REASON.DROP_REASON AS DROP_REASON" ){
		$val = DROP_REASON;
	} else if($field == "M_LEAD_SOURCE.LEAD_SOURCE AS LEAD_SOURCE" ){
		$val = LEAD_SOURCE;
	} else if($field == "M_LEAD_CONTACT_SOURCE.LEAD_CONTACT_SOURCE AS LEAD_CONTACT_SOURCE" ){
		$val = CONTACT_SOURCE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.CONTRACT_SIGNED_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.CONTRACT_SIGNED_DATE,'%m/%d/%Y' )) AS CONTRACT_SIGNED_DATE" ){
		$val = CONTRACT_SIGNED_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.CONTRACT_END_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.CONTRACT_END_DATE,'%m/%d/%Y' )) AS CONTRACT_END_DATE" ){
		$val = CONTRACT_END_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.ENTRY_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.ENTRY_DATE,'%m/%d/%Y' )) AS ENTRY_DATE" ){ //Ticket # 1595
		$val = ENTRY_DATE;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.ENTRY_TIME = '00:00:00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.ENTRY_TIME,'%h:%i %p' )) AS ENTRY_TIME" ){ //Ticket # 1595
		$val = ENTRY_TIME;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.ORIGINAL_EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.ORIGINAL_EXPECTED_GRAD_DATE,'%m/%d/%Y' )) AS ORIGINAL_EXPECTED_GRAD_DATE" ){
		$val = ORIGINAL_EXPECTED_GRAD_DATE;
	} else if($field == "M_ENROLLMENT_STATUS.CODE AS FULL_PART_TIME" ){
		$val = FULL_PART_TIME;
	} else if($field == "IF(S_STUDENT_ENROLLMENT.FT_PT_EFFECTIVE_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.FT_PT_EFFECTIVE_DATE,'%m/%d/%Y' )) AS FT_PT_EFFECTIVE_DATE" ){
		$val = FT_PT_EFFECTIVE_DATE;
	} else if($field == "M_SESSION.SESSION AS SESSION" ){
		$val = SESSION;
	} else if($field == "M_FIRST_TERM.FIRST_TERM AS FIRST_TERM" ){
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
	} else if($field == "IF(S_STUDENT_ENROLLMENT.STRF_PAID_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.STRF_PAID_DATE,'%m/%d/%Y' )) AS STRF_PAID_DATE" ){
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
	} else 
		$val = "";
	///////////////////////////////////
	
	$fields .= ','.$field;

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($val);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(15);
		
	$res_fields->MoveNext();
}

$custom_fields_type_arr = array();
$custom_fields_arr 		= array();
$custom_fields_tab_arr 	= array();
$res_fields = $db->Execute("SELECT FIELD_NAME, FIELDS, TAB, PK_DATA_TYPES from S_CUSTOM_REPORT_DETAIL, S_CUSTOM_FIELDS WHERE PK_CUSTOM_REPORT = '$_GET[id]' AND FIELD_FOR = 'CUSTOM_FIELDS' AND FIELDS != '' AND S_CUSTOM_REPORT_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_CUSTOM_REPORT_DETAIL.FIELDS = S_CUSTOM_FIELDS.PK_CUSTOM_FIELDS ORDER BY SORT_ORDER ASC, PK_CUSTOM_REPORT_DETAIL ASC");	
while (!$res_fields->EOF){
	$custom_fields_arr[] 		= $res_fields->fields['FIELDS']; 
	$custom_fields_tab_arr[] 	= $res_fields->fields['TAB']; 
	$custom_fields_type_arr[] 	= $res_fields->fields['PK_DATA_TYPES'];
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fields->fields['FIELD_NAME']);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(15);
		
	$res_fields->MoveNext();
}

$objPHPExcel->getActiveSheet()->freezePane('A2');

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

$left_join = "";
$camp_cond = "";
$GROUP_BY  = "";
/////////////////
if($PK_CAMPUS != '' ) {
	$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
	$camp_cond = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
	$GROUP_BY = " GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT";
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

/*
$ORDER_BY = "";
if($GROUP_BY_FIELD == 1)
	$ORDER_BY = " S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS ASC ";
else if($GROUP_BY_FIELD == 2)
	$ORDER_BY = " S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE ASC ";
	
if($ORDER_BY != '')
	$ORDER_BY = ' ORDER BY '.$ORDER_BY;
*/

$ORDER_BY = " ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC ";
	
/* Ticket # 1762 - contact source change */
/* Ticket # 1769 - gender change */
$res = $db->Execute("SELECT $fields FROM 
S_STUDENT_MASTER 
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
WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ACTIVE = 1 AND S_STUDENT_MASTER.ARCHIVED = 0 $cond $GROUP_BY $ORDER_BY "); 
/* Ticket # 1762 - contact source change */
/* Ticket # 1769 - gender change */

while (!$res->EOF){
	$line++;
	
	$PK_STUDENT_MASTER 		= $res->fields['PK_STUDENT_MASTER'];
	$PK_STUDENT_ENROLLMENT 	= $res->fields['PK_STUDENT_ENROLLMENT'];
	$index = -1;
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
					$value = date("m/d/Y",strtotime($value));
				else
					$value = '';
			} else if($key == 'ENTRY_TIME') { /* Ticket # 1623 */
				$ENTRY_TIME = trim($value);
				if($ENTRY_TIME != '' && $ENTRY_TIME != '00:00:00') {
					$ENTRY_TIME = date("H:i", strtotime($ENTRY_TIME));
					$ENTRY_TIME = convert_to_user_date(date("Y-m-d ").$ENTRY_TIME,'h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
					$value		= $ENTRY_TIME;
				}
			} /* Ticket # 1623 */
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($value);
		}
	}
	
	foreach($custom_fields_arr as $indes_2 => $PK_CUSTOM_FIELDS){ 
		$value = "";
		$cust_en_cond = "";
														
		if(strtolower($custom_fields_tab_arr[$indes_2] == 'other'))
			$cust_en_cond = " AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
			
		if($custom_fields_type_arr[$indes_2] == 1 || $custom_fields_type_arr[$indes_2] == 4) { 
			//Text, Date
			$res_stu_cus = $db->Execute("select FIELD_VALUE FROM S_STUDENT_CUSTOM_FIELDS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' $cust_en_cond ");
			$value = $res_stu_cus->fields['FIELD_VALUE'];
			
			if($custom_fields_type_arr[$indes_2] == 4 && $value != ''){
				$value = date("m/d/Y",strtotime($value));
			}
		} else if($custom_fields_type_arr[$indes_2] == 2) { 
			//Drop Down
			$res_stu_cus = $db->Execute("select OPTION_NAME FROM S_STUDENT_CUSTOM_FIELDS, S_USER_DEFINED_FIELDS_DETAIL WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' AND PK_USER_DEFINED_FIELDS_DETAIL =  FIELD_VALUE $cust_en_cond ");
			$value = $res_stu_cus->fields['OPTION_NAME'];
			
		} else if($custom_fields_type_arr[$indes_2] == 3) { 
			//Multiple Choice
			$res_stu_cus = $db->Execute("select FIELD_VALUE FROM S_STUDENT_CUSTOM_FIELDS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' $cust_en_cond ");
			$value = $res_stu_cus->fields['FIELD_VALUE'];
			
			$res_stu_cus = $db->Execute("select GROUP_CONCAT(OPTION_NAME ORDER BY OPTION_NAME ASC SEPARATOR ', ') as OPTION_NAME FROM S_USER_DEFINED_FIELDS_DETAIL WHERE PK_USER_DEFINED_FIELDS_DETAIL IN ($value)  ");
			$value = $res_stu_cus->fields['OPTION_NAME'];
		}
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($value);
	}
	
	$res->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);