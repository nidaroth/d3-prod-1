<?php require_once('../global/config.php');
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/student_contact.php");
require_once("check_access.php");

$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

if($_GET['t'] == 1 && $ADMISSION_ACCESS == 0) {
	header("location:../index");
	exit;
} else if($_GET['t'] == 2 && $REGISTRAR_ACCESS == 0) {
	header("location:../index");
	exit;
} else if($_GET['t'] == 3 && $FINANCE_ACCESS == 0) {
	header("location:../index");
	exit;
} else if($_GET['t'] == 5 && $ACCOUNTING_ACCESS == 0) {
	header("location:../index");
	exit;
} else if($_GET['t'] == 6 && $PLACEMENT_ACCESS == 0) {
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

include '../global/excel/Classes/PHPExcel/IOFactory.php';
$cell1  = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");		
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

$total_fields = 320;
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

$dir 			= 'temp/';
$inputFileType  = 'Excel2007';

$file_name = '';
if($_GET['t'] == 1) 
	$file_name = LEAD.' '.DATA_VIEW;
else if($_GET['t'] == 100)
	$file_name = 'Lead Import Results';
else
	$file_name = STUDENT_PAGE_TITLE1.' '.DATA_VIEW;
	
if($file_name == '')
	$file_name 	= 'data_view.xlsx';
else
	$file_name 	.= '.xlsx';
	
$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  
	
$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
$objReader->setIncludeCharts(TRUE);
//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
$objPHPExcel = new PHPExcel();
$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$line 	= 1;	
$index 	= -1;

$heading[] = FIRST_NAME;
$width[]   = 20;
$heading[] = MIDDLE_NAME;
$width[]   = 20;
$heading[] = LAST_NAME;
$width[]   = 20;
$heading[] = OTHER_NAME;
$width[]   = 20;
$heading[] = PATERNAL_LAST_NAME;
$width[]   = 20;
$heading[] = MATERNAL_LAST_NAME;
$width[]   = 20;
$heading[] = SSN;
$width[]   = 20;
$heading[] = SSN_VERIFIED;
$width[]   = 20;
$heading[] = COMMENTS; // DIAM-1184
$width[]   = 20;
$heading[] = ADDRESS;
$width[]   = 20;
$heading[] = ADDRESS_1;
$width[]   = 20;
$heading[] = CITY;
$width[]   = 20;
$heading[] = STATE;
$width[]   = 20;
$heading[] = ZIP;
$width[]   = 20;
$heading[] = COUNTRY;
$width[]   = 20;
$heading[] = CELL_PHONE;
$width[]   = 20;
$heading[] = CELL_PHONE.' '.OPTOUT;
$width[]   = 20;
$heading[] = CELL_PHONE.' '.INVALID;
$width[]   = 20;
$heading[] = HOME_PHONE;
$width[]   = 20;
$heading[] = HOME_PHONE.' '.INVALID;
$width[]   = 20;
$heading[] = WORK_PHONE;
$width[]   = 20;
$heading[] = WORK_PHONE.' '.INVALID;
$width[]   = 20;
$heading[] = OTHER_PHONE;
$width[]   = 20;
$heading[] = OTHER_PHONE.' '.OPTOUT;
$width[]   = 20;
$heading[] = EMAIL;
$width[]   = 20;
$heading[] = USE_EMAIL;
$width[]   = 20;
$heading[] = EMAIL.' '.INVALID;
$width[]   = 20;
$heading[] = OTHER_EMAIL;
$width[]   = 20;
$heading[] = OTHER_EMAIL.' '.INVALID;
$width[]   = 20;

$heading[] = DATE_OF_BIRTH;
$width[]   = 20;
$heading[] = GENDER;
$width[]   = 20;
$heading[] = DRIVERS_LICENSE;
$width[]   = 20;
$heading[] = DRIVERS_LICENSE_STATE;
$width[]   = 20;
$heading[] = MARITAL_STATUS;
$width[]   = 20;
$heading[] = COUNTRY_CITIZEN;
$width[]   = 20;
$heading[] = US_CITIZEN;
$width[]   = 20;
$heading[] = PLACE_OF_BIRTH;
$width[]   = 20;
$heading[] = STATE_OF_RESIDENCY;
$width[]   = 20;
$heading[] = STUDENT_ID;
$width[]   = 20;
$heading[] = ADM_USER_ID;
$width[]   = 20;
$heading[] = HIGHEST_LEVEL_OF_ED;
$width[]   = 20;
$heading[] = PREVIOUS_COLLEGE;
$width[]   = 20;
$heading[] = BADGE_ID;
$width[]   = 20;
$heading[] = FERPA_BLOCK;
$width[]   = 20;
$heading[] = IPEDS_ETHNICITY;
$width[]   = 20;
$heading[] = RACE;
$width[]   = 20;
$heading[] = FIRST_TERM_DATE;
$width[]   = 20;
$heading[] = PROGRAM;
$width[]   = 20;
$heading[] = STUDENT_STATUS;
$width[]   = 20;
$heading[] = STATUS_DATE;
$width[]   = 20;
$heading[] = ADMISSION_REP;
$width[]   = 20;
$heading[] = LEAD_SOURCE;
$width[]   = 20;
$heading[] = CONTACT_SOURCE;
$width[]   = 20;
$heading[] = CONTRACT_SIGNED_DATE;
$width[]   = 20;
$heading[] = CONTRACT_END_DATE;
$width[]   = 20;
$heading[] = ENTRY_DATE;
$width[]   = 20;
$heading[] = ENTRY_TIME;
$width[]   = 20;
if($_GET['t'] != 1 && $_GET['t'] != 100){
	$heading[] = ORIGINAL_EXPECTED_GRAD_DATE;
	$width[]   = 20;
}
$heading[] = EXPECTED_GRAD_DATE;
$width[]   = 20;
$heading[] = ORIGINAL_ENROLLMENT_STATUS;
$width[]   = 20;
$heading[] = FULL_PART_TIME;
$width[]   = 20;

if($_GET['t'] != 1 && $_GET['t'] != 100){
	$heading[] = FT_PT_EFFECTIVE_DATE;
	$width[]   = 20;
	$heading[] = MIDPOINT_DATE;
	$width[]   = 20;
}

$heading[] = SESSION;
$width[]   = 20;

if($_GET['t'] != 1 && $_GET['t'] != 100){
	$heading[] = FIRST_TERM;
	$width[]   = 20;
}

$heading[] = RESIDENCY_TUITION_STATUS; // DIAM-2370
$width[]   = 20;

if($_GET['t'] != 1 && $_GET['t'] != 100){
	$heading[] = REENTRY;
	$width[]   = 20;
	$heading[] = TRANSFER_IN;
	$width[]   = 20;
	$heading[] = TRANSFER_OUT;
	$width[]   = 20;
	$heading[] = DISTANCE_LEARNING;
	$width[]   = 20;
}

$heading[] = FUNDING;
$width[]   = 20;

if($_GET['t'] != 1 && $_GET['t'] != 100){
	$heading[] = PLACEMENT_STATUS;
	$width[]   = 20;
	$heading[] = STRF_PAID_DATE;
	$width[]   = 20;
}

$heading[] = STUDENT_GROUP;
$width[]   = 20;

if($_GET['t'] != 1 && $_GET['t'] != 100){
	$heading[] = GRADE_DATE;
	$width[]   = 20;
	$heading[] = LDA;
	$width[]   = 20;
	$heading[] = DETERMINATION_DATE;
	$width[]   = 20;
	$heading[] = DROP_DATE;
	$width[]   = 20;
	$heading[] = DROP_REASON;
	$width[]   = 20;
	
}

$heading[] = CAMPUS;
$width[]   = 20;

$res_type = $db->Execute("select FIELD_NAME, IF(TAB = 'info',1,2) as TAB_ORDER from S_CUSTOM_FIELDS WHERE SECTION = 1 AND S_CUSTOM_FIELDS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 ORDER BY TAB_ORDER ASC, PK_CUSTOM_FIELDS ASC ");
while (!$res_type->EOF) { 
	$heading[] = $res_type->fields['FIELD_NAME'];
	$width[]   = 20;
	$res_type->MoveNext();
}

if($_GET['t'] == 100){
	$heading[] = ERROR;
	$width[]   = 20;
	
	$heading[] = STATUS;
	$width[]   = 20;
}

$i = 0;
foreach($heading as $title) {
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
	$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
	$i++;
}
$objPHPExcel->getActiveSheet()->freezePane('A2');

$res_type = $db->Execute($_SESSION['REPORT_QUERY']);
while (!$res_type->EOF){
	$line++;
	$index = -1;
	$PK_STUDENT_MASTER 		= $res_type->fields['PK_STUDENT_MASTER'];
	$PK_STUDENT_ENROLLMENT	= $res_type->fields['PK_STUDENT_ENROLLMENT'];
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['FIRST_NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['MIDDLE_NAME']);

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['LAST_NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['STU_OTHER_NAME']);

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['PATERNAL_LAST_NAME']);

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['MATERNAL_LAST_NAME']);
	
	$SSN = '';
	if($res_type->fields['SSN'] != ''){ 
		$SSN_1 	 = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$res_type->fields['SSN']);
		$SSN_ARR = explode("-",$SSN_1);
		$SSN = 'xxx-xx-'.$SSN_ARR[2];
	}
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SSN);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['SSN_VERIFIED']);

	// DIAM-1184
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['COMMENTS']);
	// End DIAM-1184
	
	$res_cont = $db->Execute("SELECT ADDRESS,ADDRESS_1,CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, IF(HOME_PHONE_INVALID = 1,'Yes','No') AS HOME_PHONE_INVALID, WORK_PHONE, IF(WORK_PHONE_INVALID = 1,'Yes','No') AS WORK_PHONE_INVALID, CELL_PHONE, IF(OPT_OUT = 1,'Yes','No') AS OPT_OUT , IF(CELL_PHONE_INVALID = 1,'Yes','No') AS CELL_PHONE_INVALID, OTHER_PHONE, IF(OTHER_PHONE_INVALID = 1,'Yes','No') AS OTHER_PHONE_INVALID, EMAIL, IF(USE_EMAIL = 1,'Yes','No') AS USE_EMAIL , IF(EMAIL_INVALID = 1,'Yes','No') AS EMAIL_INVALID, EMAIL_OTHER, IF(EMAIL_OTHER_INVALID = 1,'Yes','No') AS EMAIL_OTHER_INVALID FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' ");
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['ADDRESS']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['ADDRESS_1']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['CITY']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['STATE_CODE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['ZIP']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['COUNTRY']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['CELL_PHONE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['OPT_OUT']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['CELL_PHONE_INVALID']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['HOME_PHONE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['HOME_PHONE_INVALID']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['WORK_PHONE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['WORK_PHONE_INVALID']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['OTHER_PHONE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['OTHER_PHONE_INVALID']);
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['EMAIL']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['USE_EMAIL']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['EMAIL_INVALID']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['EMAIL_OTHER']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_cont->fields['EMAIL_OTHER_INVALID']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['DATE_OF_BIRTH']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['GENDER']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['DRIVERS_LICENSE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['STATES_DRIVERS_LICENSE_STATE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['STU_MARITAL_STATUS']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['COUNTRY_CITIZEN']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['CITIZENSHIP']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['PLACE_OF_BIRTH']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['STATE_OF_RESIDENCY']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['STUDENT_ID']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['ADM_USER_ID']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['HIGHEST_LEVEL_OF_EDU']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['PREVIOUS_COLLEGE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['BADGE_ID']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['FERPA_BLOCK']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['IPEDS_ETHNICITY']);
	
	$race = '';
	$res_race = $db->Execute("select RACE FROM S_STUDENT_RACE, Z_RACE WHERE S_STUDENT_RACE.PK_RACE = Z_RACE.PK_RACE AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	while (!$res_race->EOF){
		if($race != '')
			$race .= ', ';
		$race .= $res_race->fields['RACE'];
		
		$res_race->MoveNext();
	} 
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($race);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['TERM_DATE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['CODE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['STUDENT_STATUS']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['STATUS_DATE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['EMP_NAME']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['LEAD_SOURCE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['LEAD_CONTACT_SOURCE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['CONTRACT_SIGNED_DATE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['CONTRACT_END_DATE']);
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['ENTRY_DATE']);
	
	/* Ticket # 1623 */
	$ENTRY_TIME = $res_type->fields['ENTRY_TIME'];
	if($ENTRY_TIME != '' && $ENTRY_TIME != '00:00:00') {
		$ENTRY_TIME = date("H:i", strtotime($ENTRY_TIME));
		$ENTRY_TIME = convert_to_user_date(date("Y-m-d ").$ENTRY_TIME,'h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
	} 
	/* Ticket # 1623 */
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($ENTRY_TIME); // Ticket # 1623
	
	if($_GET['t'] != 1 && $_GET['t'] != 100){
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['ORIGINAL_EXPECTED_GRAD_DATE']);
	}
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['EXPECTED_GRAD_DATE']);

	// DIAM-2366
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['ORIGINAL_ENROLLMENT_STATUS']);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(27);
	// End DIAM-2366
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['FULL_PART_TIME']);
	
	if($_GET['t'] != 1 && $_GET['t'] != 100){
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['FT_PT_EFFECTIVE_DATE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['MIDPOINT_DATE']);
	}
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['SESSION']);

	if($_GET['t'] != 1 && $_GET['t'] != 100){
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['FIRST_TERM']);
	}

	// DIAM-2370
	$M_CODE = $res_type->fields['M_CODE'];
	$M_DESCRIPTION = $res_type->fields['M_DESCRIPTION'];
	if($res_type->fields['RESIDENCY_TUITION_STATUS'] != "")
	{
		$RESIDENCY_TUITION_STATUS = $M_DESCRIPTION. " (". $M_CODE. ")";
	}
	else{
		$RESIDENCY_TUITION_STATUS = 'Not Set';
	}

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($RESIDENCY_TUITION_STATUS);
	$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth(35);
	// End DIAM-2370

	if($_GET['t'] != 1 && $_GET['t'] != 100){
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['REENTRY']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['TRANSFER_IN']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['TRANSFER_OUT']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['DISTANCE_LEARNING']);
	}
	
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['FUNDING']);
	
	if($_GET['t'] != 1 && $_GET['t'] != 100){
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['PLACEMENT_STATUS']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['STRF_PAID_DATE']);
	}

	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['STUDENT_GROUP']);
	
	if($_GET['t'] != 1 && $_GET['t'] != 100){
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['GRADE_DATE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['LDA']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['DETERMINATION_DATE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['DROP_DATE']);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['DROP_REASON']);
	}
	
	$campus = '';
	$res_campus = $db->Execute("select OFFICIAL_CAMPUS_NAME FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_CAMPUS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	while (!$res_campus->EOF){
		if($campus != '')
			$campus .= ', ';
		$campus .= $res_campus->fields['OFFICIAL_CAMPUS_NAME'];
		
		$res_campus->MoveNext();
	} 
	$index++;
	$cell_no = $cell[$index].$line;
	$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($campus);
	
	$res_cust = $db->Execute("select PK_CUSTOM_FIELDS,FIELD_NAME,PK_DATA_TYPES, PK_USER_DEFINED_FIELDS, TAB, IF(TAB = 'info',1,2) as TAB_ORDER from S_CUSTOM_FIELDS WHERE SECTION = 1 AND S_CUSTOM_FIELDS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 ORDER BY TAB_ORDER ASC, PK_CUSTOM_FIELDS ASC ");
	while (!$res_cust->EOF) { 
		$PK_CUSTOM_FIELDS 		= $res_cust->fields['PK_CUSTOM_FIELDS'];
		$PK_USER_DEFINED_FIELDS = $res_cust->fields['PK_USER_DEFINED_FIELDS'];
		
		$cust_en_cond = "";
		if(strtolower($res_cust->fields['TAB']) == 'other')
			$cust_en_cond = " AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
		
		$res_1 = $db->Execute("select FIELD_VALUE from S_STUDENT_CUSTOM_FIELDS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' $cust_en_cond  "); 
		$FIELD_VALUE = $res_1->fields['FIELD_VALUE']; 
		
		if($res_cust->fields['PK_DATA_TYPES'] == 4) {
			if($FIELD_VALUE != '')
				$FIELD_VALUE = date("Y-m-d",strtotime($FIELD_VALUE));
		} else if($res_cust->fields['PK_DATA_TYPES'] == 2) {
			$res_dd = $db->Execute("select OPTION_NAME from S_USER_DEFINED_FIELDS_DETAIL WHERE PK_USER_DEFINED_FIELDS_DETAIL = '$FIELD_VALUE' ");
			$FIELD_VALUE = $res_dd->fields['OPTION_NAME']; 
		} else if($res_cust->fields['PK_DATA_TYPES'] == 3) {
			$res_dd = $db->Execute("select OPTION_NAME from S_USER_DEFINED_FIELDS_DETAIL WHERE PK_USER_DEFINED_FIELDS_DETAIL IN ($FIELD_VALUE) ");
			$FIELD_VALUE = '';
			while (!$res_dd->EOF) { 
				if($FIELD_VALUE != '')
					$FIELD_VALUE .= ', ';
					
				$FIELD_VALUE .= $res_dd->fields['OPTION_NAME']; 
				$res_dd->MoveNext();
			}
		}
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($FIELD_VALUE);
		
		$res_cust->MoveNext();
	}
	
	if($_GET['t'] == 100){
		$IMPORT_ERROR = str_replace("<br />",", ",$res_type->fields['IMPORT_ERROR']);
		$IMPORT_ERROR = str_replace("<b>"," - ",$res_type->fields['IMPORT_ERROR']);
		$IMPORT_ERROR = strip_tags($IMPORT_ERROR);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($IMPORT_ERROR);
		
		$index++;
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_type->fields['IMPORT_STATUS']);
	}
	
	$res_type->MoveNext();
}

$objWriter->save($outputFileName);
$objPHPExcel->disconnectWorksheets();
header("location:".$outputFileName);
