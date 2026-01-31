<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/student_contact.php");
require_once("../language/menu.php");
require_once("../global/create_notification.php"); 
require_once("check_access.php");

if(check_access('MANAGEMENT_ADMISSION') == 0 ){
	header("location:../index");
	exit;
}

include '../global/excel/Classes/PHPExcel/IOFactory.php'; 

$msg 	= '';
$error 	= array();
$flag 	= 0;
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	$i 		= 0;
	$flag 	= 1;
	foreach($_POST as $FIELD_NAME => $EXCEL_COLUMN ){
		if($FIELD_NAME != '') {
			$MAP_DETAIL['TABLE_COLUMN'] = $FIELD_NAME;
			db_perform('Z_EXCEL_MAP_DETAIL', $MAP_DETAIL, 'update'," PK_MAP_MASTER = '$_GET[id]' AND EXCEL_COLUMN = '$EXCEL_COLUMN' ");
		}		
		$i++;
	}
	$db->Execute("DELETE FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = '' ");

	$res = $db->Execute("SELECT FILE_LOCATION,HEADING_ROW_NO FROM Z_EXCEL_MAP_MASTER WHERE PK_MAP_MASTER = '$_GET[id]' ");
	$newfile1 		= $res->fields['FILE_LOCATION'];
	$HEADING_ROW_NO = $res->fields['HEADING_ROW_NO'];

	if ($newfile1 != ""){
		$extn = explode(".",$newfile1);
		$ii = count($extn) - 1;

		if(strtolower($extn[$ii]) == 'xlsx' || strtolower($extn[$ii]) == 'xls' || strtolower($extn[$ii]) == 'csv'){
			$inputFileName = $newfile1;
			
			if(strtolower($extn[$ii]) == 'csv'){
				$inputFileType = 'CSV';
				$objReader = PHPExcel_IOFactory::createReader($inputFileType);
				$objPHPExcel = $objReader->load($inputFileName);
				$objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			}else{
				//echo $inputFileName.'--';exit;	
				$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
			}
			$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		}
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'FIRST_NAME' ");
		$FIRST_NAME_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'LAST_NAME' ");
		$LAST_NAME_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'MIDDLE_NAME' ");
		$MIDDLE_NAME_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_NAME' ");
		$OTHER_NAME_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'SSN' ");
		$SSN_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'SSN_VERIFIED' ");
		$SSN_VERIFIED_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'DATE_OF_BIRTH' ");
		$DATE_OF_BIRTH_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'GENDER' ");
		$GENDER_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'DRIVERS_LICENSE' ");
		$DRIVERS_LICENSE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_DRIVERS_LICENSE_STATE' ");
		$PK_DRIVERS_LICENSE_STATE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_MARITAL_STATUS' ");
		$PK_MARITAL_STATUS_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_COUNTRY_CITIZEN' ");
		$PK_COUNTRY_CITIZEN_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_CITIZENSHIP' ");
		$PK_CITIZENSHIP_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PLACE_OF_BIRTH' ");
		$PLACE_OF_BIRTH_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_STATE_OF_RESIDENCY' ");
		$PK_STATE_OF_RESIDENCY_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'STUDENT_ID' ");
		$STUDENT_ID_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'ADM_USER_ID' ");
		$ADM_USER_ID_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_HIGHEST_LEVEL_OF_EDU' ");
		$PK_HIGHEST_LEVEL_OF_EDU_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PREVIOUS_COLLEGE' ");
		$PREVIOUS_COLLEGE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'BADGE_ID' ");
		$BADGE_ID_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'FERPA_BLOCK' ");
		$FERPA_BLOCK_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'IPEDS_ETHNICITY' ");
		$IPEDS_ETHNICITY_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'RACE' ");
		$RACE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'TRANSFER_IN' ");
		$TRANSFER_IN_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'TRANSFER_OUT' ");
		$TRANSFER_OUT_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'FIRST_TERM' ");
		$FIRST_TERM_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'REENTRY' ");
		$REENTRY_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_REPRESENTATIVE' ");
		$PK_REPRESENTATIVE_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_LEAD_SOURCE' ");
		$PK_LEAD_SOURCE_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'STATUS_DATE' ");
		$STATUS_DATE_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_CAMPUS_PROGRAM' ");
		$PK_CAMPUS_PROGRAM_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_TERM_MASTER' ");
		$PK_TERM_MASTER_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_FUNDING' ");
		$PK_FUNDING_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'FT_PT_EFFECTIVE_DATE' ");
		$FT_PT_EFFECTIVE_DATE_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EXPECTED_GRAD_DATE' ");
		$EXPECTED_GRAD_DATE_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'ORIGINAL_EXPECTED_GRAD_DATE' ");
		$ORIGINAL_EXPECTED_GRAD_DATE_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_SESSION' ");
		$PK_SESSION_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_ENROLLMENT_STATUS' ");
		$PK_ENROLLMENT_STATUS_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_STUDENT_GROUP' ");
		$PK_STUDENT_GROUP_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'CONTRACT_SIGNED_DATE' ");
		$CONTRACT_SIGNED_DATE_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'CONTRACT_END_DATE' ");
		$CONTRACT_END_DATE_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_STUDENT_STATUS' ");
		$PK_STUDENT_STATUS_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_DISTANCE_LEARNING' ");
		$PK_DISTANCE_LEARNING_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_DROP_REASON' ");
		$PK_DROP_REASON_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_PLACEMENT_STATUS' ");
		$PK_PLACEMENT_STATUS_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_SPECIAL_PROGRAM_INDICATOR' ");
		$PK_SPECIAL_PROGRAM_INDICATOR_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'MIDPOINT_DATE' ");
		$MIDPOINT_DATE_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EXTERN_START_DATE' ");
		$EXTERN_START_DATE_COL = $res_fields->fields['EXTERN_START_DATE'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'GRADE_DATE' ");
		$GRADE_DATE_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'LDA' ");
		$LDA_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'DROP_DATE' ");
		$DROP_DATE_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'STRF_PAID_DATE' ");
		$STRF_PAID_DATE_COL = $res_fields->fields['EXCEL_COLUMN'];

		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'DETERMINATION_DATE' ");
		$DETERMINATION_DATE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_LEAD_CONTACT_SOURCE' ");
		$PK_LEAD_CONTACT_SOURCE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'CAMPUS' ");
		$CAMPUS_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		//////////////////////////
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'ADDRESS' ");
		$ADDRESS_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'ADDRESS_1' ");
		$ADDRESS_1_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'CITY' ");
		$CITY_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'STATE' ");
		$STATE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'ZIP' ");
		$ZIP_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'COUNTRY' ");
		$COUNTRY_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'HOME_PHONE' ");
		$HOME_PHONE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'WORK_PHONE' ");
		$WORK_PHONE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'MOBILE_PHONE' ");
		$MOBILE_PHONE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_PHONE' ");
		$OTHER_PHONE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMAIL' ");
		$EMAIL_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'USE_EMAIL' ");
		$USE_EMAIL_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMAIL_OTHER' ");
		$EMAIL_OTHER_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'HOME_PHONE_INVALID' ");
		$HOME_PHONE_INVALID_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'WORK_PHONE_INVALID' ");
		$WORK_PHONE_INVALID_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OPT_OUT' ");
		$OPT_OUT_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'CELL_PHONE_INVALID' ");
		$CELL_PHONE_INVALID_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_PHONE_INVALID' ");
		$OTHER_PHONE_INVALID_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMAIL_INVALID' ");
		$EMAIL_INVALID_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMAIL_OTHER_INVALID' ");
		$EMAIL_OTHER_INVALID_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		///////////////////////////////////////
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_CONTACT_NAME' ");
		$EMERGENCY_CONTACT_NAME_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_RELATIONSHIP' ");
		$EMERGENCY_RELATIONSHIP_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_ADDRESS' ");
		$EMERGENCY_ADDRESS_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_ADDRESS_1' ");
		$EMERGENCY_ADDRESS_1_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_CITY' ");
		$EMERGENCY_CITY_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_STATE' ");
		$EMERGENCY_STATE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_ZIP' ");
		$EMERGENCY_ZIP_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_COUNTRY' ");
		$EMERGENCY_COUNTRY_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_HOME_PHONE' ");
		$EMERGENCY_HOME_PHONE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_WORK_PHONE' ");
		$EMERGENCY_WORK_PHONE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_MOBILE_PHONE' ");
		$EMERGENCY_MOBILE_PHONE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_OTHER_PHONE' ");
		$EMERGENCY_OTHER_PHONE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_EMAIL' ");
		$EMERGENCY_EMAIL_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_USE_EMAIL' ");
		$EMERGENCY_USE_EMAIL_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_EMAIL_OTHER' ");
		$EMERGENCY_EMAIL_OTHER_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'CAMPUS' ");
		$CAMPUS_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_HOME_PHONE_INVALID' ");
		$EMERGENCY_HOME_PHONE_INVALID_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_WORK_PHONE_INVALID' ");
		$EMERGENCY_WORK_PHONE_INVALID_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_OPT_OUT' ");
		$EMERGENCY_OPT_OUT_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_CELL_PHONE_INVALID' ");
		$EMERGENCY_CELL_PHONE_INVALID_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_OTHER_PHONE_INVALID' ");
		$EMERGENCY_OTHER_PHONE_INVALID_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_EMAIL_INVALID' ");
		$EMERGENCY_EMAIL_INVALID_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'EMERGENCY_EMAIL_OTHER_INVALID' ");
		$EMERGENCY_EMAIL_OTHER_INVALID_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		///////////////////////////////////////
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_EDUCATION_SCHOOL_NAME' ");
		$OTHER_EDUCATION_SCHOOL_NAME_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_EDUCATION_ADDRESS' ");
		$OTHER_EDUCATION_ADDRESS_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_EDUCATION_ADDRESS_1' ");
		$OTHER_EDUCATION_ADDRESS_1_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_EDUCATION_CITY' ");
		$OTHER_EDUCATION_CITY_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_EDUCATION_STATE' ");
		$OTHER_EDUCATION_STATE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_EDUCATION_ZIP' ");
		$OTHER_EDUCATION_ZIP_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_SCHOOL_PHONE' ");
		$OTHER_SCHOOL_PHONE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_SCHOOL_FAX' ");
		$OTHER_SCHOOL_FAX_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_EDUCATION_COUNTRY' ");
		$OTHER_EDUCATION_COUNTRY_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_EDUCATION_EDUCATION_TYPE' ");
		$OTHER_EDUCATION_EDUCATION_TYPE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_EDUCATION_GRADUATED' ");
		$OTHER_EDUCATION_GRADUATED_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_EDUCATION_TRANSCRIPT_REQUESTED' ");
		$OTHER_EDUCATION_TRANSCRIPT_REQUESTED_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_EDUCATION_TRANSCRIPT_RECEIVED' ");
		$OTHER_EDUCATION_TRANSCRIPT_RECEIVED_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_EDUCATION_GRADUATED_DATE' ");
		$OTHER_EDUCATION_GRADUATED_DATE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_EDUCATION_TRANSCRIPT_REQUESTED_DATE' ");
		$OTHER_EDUCATION_TRANSCRIPT_REQUESTED_DATE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$res_fields = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OTHER_EDUCATION_TRANSCRIPT_RECEIVED_DATE' ");
		$OTHER_EDUCATION_TRANSCRIPT_RECEIVED_DATE_COL = $res_fields->fields['EXCEL_COLUMN'];
		
		$i = 0;
		foreach($sheetData as $row ){
			$i++;
			if($i <= $HEADING_ROW_NO){
				continue;
			}
			//echo "<pre>".$DATE_OF_BIRTH_COL;print_r($row);exit;
			$STUDENT_MASTER 	= array();
			$STUDENT_ENROLLMENT = array();
			$STUDENT_ACADEMICS 	= array();
			$PK_RACE			= array();
			$PK_CAMPUS			= array();
			
			$STUDENT_MASTER['PK_MAP_MASTER'] 			= $_GET['id'];
			$STUDENT_MASTER['FIRST_NAME']  			 	= trim($row[$FIRST_NAME_COL]);
			$STUDENT_MASTER['LAST_NAME']  			 	= trim($row[$LAST_NAME_COL]);
			$STUDENT_MASTER['MIDDLE_NAME']  		 	= trim($row[$MIDDLE_NAME_COL]);
			$STUDENT_MASTER['OTHER_NAME']  			 	= trim($row[$OTHER_NAME_COL]);
			$STUDENT_MASTER['DATE_OF_BIRTH']  		 	= trim($row[$DATE_OF_BIRTH_COL]);
			$STUDENT_MASTER['GENDER']  				 	= trim($row[$GENDER_COL]);
			$STUDENT_MASTER['DRIVERS_LICENSE']  	 	= trim($row[$DRIVERS_LICENSE_COL]);
			$STUDENT_MASTER['PK_DRIVERS_LICENSE_STATE'] = trim($row[$PK_DRIVERS_LICENSE_STATE_COL]);
			$STUDENT_MASTER['PK_MARITAL_STATUS']  	 	= trim($row[$PK_MARITAL_STATUS_COL]);
			$STUDENT_MASTER['PK_COUNTRY_CITIZEN']  	 	= trim($row[$PK_COUNTRY_CITIZEN_COL]);
			$STUDENT_MASTER['PK_CITIZENSHIP']  		 	= trim($row[$PK_CITIZENSHIP_COL]);
			$STUDENT_MASTER['IPEDS_ETHNICITY']  	 	= trim($row[$IPEDS_ETHNICITY_COL]);
			$STUDENT_MASTER['PLACE_OF_BIRTH']  		 	= trim($row[$PLACE_OF_BIRTH_COL]);
			$STUDENT_MASTER['PK_STATE_OF_RESIDENCY'] 	= trim($row[$PK_STATE_OF_RESIDENCY_COL]);
			$STUDENT_MASTER['SSN']  		 			= trim($row[$SSN_COL]);
			$STUDENT_MASTER['SSN_VERIFIED']  		 	= trim($row[$SSN_VERIFIED_COL]);
			$STUDENT_MASTER['BADGE_ID']  			 	= trim($row[$BADGE_ID_COL]);
			
			$error_str = "";

			if($STUDENT_MASTER['DATE_OF_BIRTH'] != '') {
				$DATE_OF_BIRTH = str_replace("/","-",$STUDENT_MASTER['DATE_OF_BIRTH']);
				$DATE_OF_BIRTH = explode("-",$DATE_OF_BIRTH);
				
				if($DATE_OF_BIRTH[2] < 100)
					$year = 2000 + $DATE_OF_BIRTH[2];
				else
					$year = $DATE_OF_BIRTH[2];
				
				$STUDENT_MASTER['DATE_OF_BIRTH'] = $year.'-'.$DATE_OF_BIRTH[0].'-'.$DATE_OF_BIRTH[1];
			}
	
			/* Ticket # 1769  */
			if($STUDENT_MASTER['GENDER'] != '') {
				if(strtolower($STUDENT_MASTER['GENDER']) != '') {
					$res_st = $db->Execute("select PK_GENDER from Z_GENDER WHERE GENDER = '".$STUDENT_MASTER['GENDER']."' ");
					$STUDENT_MASTER['GENDER'] = $res_st->fields['PK_GENDER'];
				} else 
					$error_str .= GENDER.' <b>'.$STUDENT_MASTER['GENDER'].'</b>';
			}
			/* Ticket # 1769  */
			
			if($STUDENT_MASTER['PK_DRIVERS_LICENSE_STATE'] != '') {
				$res_l = $db->Execute("select PK_STATES from Z_STATES WHERE trim(STATE_CODE) = '$STUDENT_MASTER[PK_DRIVERS_LICENSE_STATE]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= DRIVERS_LICENSE_STATE.' <b>'.$STUDENT_MASTER['PK_DRIVERS_LICENSE_STATE'].'</b>';
				} else {
					$STUDENT_MASTER['PK_DRIVERS_LICENSE_STATE'] = $res_l->fields['PK_STATES'];
				}
			}
			
			if($STUDENT_MASTER['PK_MARITAL_STATUS'] != '') {
				$res_l = $db->Execute("select PK_MARITAL_STATUS from Z_MARITAL_STATUS WHERE trim(MARITAL_STATUS) = '$STUDENT_MASTER[PK_MARITAL_STATUS]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= MARITAL_STATUS.' <b>'.$STUDENT_MASTER['PK_MARITAL_STATUS'].'</b>';
				} else {
					$STUDENT_MASTER['PK_MARITAL_STATUS'] = $res_l->fields['PK_MARITAL_STATUS'];
				}
			}
			
			if($STUDENT_MASTER['PK_COUNTRY_CITIZEN'] != '') {
				$res_l = $db->Execute("select PK_COUNTRY from Z_COUNTRY WHERE trim(CODE) = '$STUDENT_MASTER[PK_COUNTRY_CITIZEN]' OR NAME = '$STUDENT_MASTER[PK_COUNTRY_CITIZEN]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= COUNTRY_CITIZEN.' <b>'.$STUDENT_MASTER['PK_COUNTRY_CITIZEN'].'</b>';
				} else {
					$STUDENT_MASTER['PK_COUNTRY_CITIZEN'] = $res_l->fields['PK_COUNTRY'];
				}
			}
			
			if($STUDENT_MASTER['PK_CITIZENSHIP'] != '') {
				$res_l = $db->Execute("select PK_CITIZENSHIP from Z_CITIZENSHIP WHERE trim(CITIZENSHIP) = '$STUDENT_MASTER[PK_CITIZENSHIP]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= CITIZENSHIP.' <b>'.$STUDENT_MASTER['PK_CITIZENSHIP'].'</b>';
				} else {
					$STUDENT_MASTER['PK_CITIZENSHIP'] = $res_l->fields['PK_CITIZENSHIP'];
				}
			}
			
			if($STUDENT_MASTER['PK_STATE_OF_RESIDENCY'] != '') {
				$res_l = $db->Execute("select PK_STATES from Z_STATES WHERE trim(STATE_CODE) = '$STUDENT_MASTER[PK_STATE_OF_RESIDENCY]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= DRIVERS_LICENSE_STATE.' <b>'.$STUDENT_MASTER['PK_STATE_OF_RESIDENCY'].'</b>';
				} else {
					$STUDENT_MASTER['PK_STATE_OF_RESIDENCY'] = $res_l->fields['PK_STATES'];
				}
			}
		
			if($STUDENT_MASTER['SSN'] != '') {
				$SSN1 = $STUDENT_MASTER['SSN'];
				$SSN1 = $SSN1[0].$SSN1[1].$SSN1[2].'-'.$SSN1[3].$SSN1[4].'-'.$SSN1[5].$SSN1[6].$SSN1[7].$SSN1[8];
				
				$STUDENT_MASTER['SSN'] = my_encrypt($_SESSION['PK_ACCOUNT'].$PK_STUDENT_MASTER,$SSN1);
			}
		
			if($STUDENT_MASTER['SSN_VERIFIED'] != '') {
				if(strtolower($STUDENT_MASTER['SSN_VERIFIED']) == 'yes' || strtolower($STUDENT_MASTER['SSN_VERIFIED']) == 'y' )
					$STUDENT_MASTER['SSN_VERIFIED'] = 1;
				else if(strtolower($STUDENT_MASTER['SSN_VERIFIED']) == 'no' || strtolower($STUDENT_MASTER['SSN_VERIFIED']) == 'n')
					$STUDENT_MASTER['SSN_VERIFIED'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= SSN_VERIFIED.' <b>'.$STUDENT_MASTER['SSN_VERIFIED'].'</b>';
				}
			}
			
			$STUDENT_ACADEMICS['STUDENT_ID'] 			 	= trim($row[$STUDENT_ID_COL]);
			$STUDENT_ACADEMICS['ADM_USER_ID'] 				= trim($row[$ADM_USER_ID_COL]);
			$STUDENT_ACADEMICS['PK_HIGHEST_LEVEL_OF_EDU'] 	= trim($row[$PK_HIGHEST_LEVEL_OF_EDU_COL]);
			$STUDENT_ACADEMICS['PREVIOUS_COLLEGE'] 	 		= trim($row[$PREVIOUS_COLLEGE_COL]);
			$STUDENT_ACADEMICS['FERPA_BLOCK'] 		 		= trim($row[$FERPA_BLOCK_COL]);

			if($STUDENT_ACADEMICS['PK_HIGHEST_LEVEL_OF_EDU'] != '') {
				$res_l = $db->Execute("select PK_HIGHEST_LEVEL_OF_EDU from M_HIGHEST_LEVEL_OF_EDU WHERE trim(HIGHEST_LEVEL_OF_EDU) = '$STUDENT_ACADEMICS[PK_HIGHEST_LEVEL_OF_EDU]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= HIGHEST_LEVEL_OF_EDU.' <b>'.$STUDENT_ACADEMICS['PK_HIGHEST_LEVEL_OF_EDU'].'</b>';
				} else {
					$STUDENT_ACADEMICS['PK_HIGHEST_LEVEL_OF_EDU'] = $res_l->fields['PK_HIGHEST_LEVEL_OF_EDU'];
				}
			}
			
			if($STUDENT_ACADEMICS['PREVIOUS_COLLEGE'] != '') {
				if(strtolower($STUDENT_ACADEMICS['PREVIOUS_COLLEGE']) == 'yes' || strtolower($STUDENT_ACADEMICS['PREVIOUS_COLLEGE']) == 'y' )
					$STUDENT_ACADEMICS['PREVIOUS_COLLEGE'] = 1;
				else if(strtolower($STUDENT_ACADEMICS['PREVIOUS_COLLEGE']) == 'no' || strtolower($STUDENT_ACADEMICS['PREVIOUS_COLLEGE']) == 'n')
					$STUDENT_ACADEMICS['PREVIOUS_COLLEGE'] = 2;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= PREVIOUS_COLLEGE.' <b>'.$STUDENT_ACADEMICS['PREVIOUS_COLLEGE'].'</b>';
				}
			} else
				$STUDENT_ACADEMICS['PREVIOUS_COLLEGE'] 	= 2;
			
			if($STUDENT_ACADEMICS['FERPA_BLOCK'] != '') {
				if(strtolower($STUDENT_ACADEMICS['FERPA_BLOCK']) == 'yes' || strtolower($STUDENT_ACADEMICS['FERPA_BLOCK']) == 'y' )
					$STUDENT_ACADEMICS['FERPA_BLOCK'] = 1;
				else if(strtolower($STUDENT_ACADEMICS['FERPA_BLOCK']) == 'no' || strtolower($STUDENT_ACADEMICS['FERPA_BLOCK']) == 'n')
					$STUDENT_ACADEMICS['FERPA_BLOCK'] = 2;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= FERPA_BLOCK.' <b>'.$STUDENT_ACADEMICS['FERPA_BLOCK'].'</b>';
				}
			} else
				$STUDENT_ACADEMICS['FERPA_BLOCK'] = 2;
				
			$RACE = $row[$RACE_COL];
			if($RACE != ''){
				$RACE_ARR = explode(',',$RACE);
				foreach($RACE_ARR as $RACE_1){
					$res_l = $db->Execute("select PK_RACE FROM Z_RACE WHERE TRIM(RACE) = '$RACE_1'");
					
					if($res_l->RecordCount() == 0) {
						if($error_str != '')
							$error_str .= ', ';
						$error_str .= RACE.' <b>'.$RACE_1.'</b>';
					} else {
						$PK_RACE[] = $res_l->fields['PK_RACE'];
					}
				}
			}
			
			$CAMPUS = $row[$CAMPUS_COL];
			if($CAMPUS != ''){
				$CAMPUS_ARR = explode(',',$CAMPUS);
				foreach($CAMPUS_ARR as $CAMPUS_1){
					$res_l = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (TRIM(CAMPUS_CODE) = '$CAMPUS_1' OR TRIM(CAMPUS_NAME) = '$CAMPUS_1')");
					
					if($res_l->RecordCount() == 0) {
						/*if($error_str != '')
							$error_str .= ', ';
						$error_str .= CAMPUS.' <b>'.$CAMPUS_1.'</b>';*/
						
						$CAMPUS['CAMPUS_CODE'] = $CAMPUS_1;
						$CAMPUS['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
						$CAMPUS['CREATED_BY']  = $_SESSION['PK_USER'];
						$CAMPUS['CREATED_ON']  = date("Y-m-d H:i");
						db_perform('S_CAMPUS', $CAMPUS, 'insert');
						$PK_CAMPUS[] = $db->insert_ID();
					} else {
						$PK_CAMPUS[] = $res_l->fields['PK_CAMPUS'];
					}
				}
			}
			
			if($STUDENT_ACADEMICS['STUDENT_ID'] != '') {
				$res_l = $db->Execute("select PK_STUDENT_MASTER from S_STUDENT_ACADEMICS where PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND STUDENT_ID = '$STUDENT_ACADEMICS[STUDENT_ID]' ");
				if($res_l->RecordCount() > 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= STUDENT_ID.' <b>'.$STUDENT_ACADEMICS['STUDENT_ID'].'</b> already Exists';
				}
			}
			
			$STUDENT_ENROLLMENT['TRANSFER_OUT'] 			 	= trim($row[$TRANSFER_OUT_COL]);
			$STUDENT_ENROLLMENT['TRANSFER_IN'] 			 		= trim($row[$TRANSFER_IN_COL]);
			$STUDENT_ENROLLMENT['FIRST_TERM'] 			 		= trim($row[$FIRST_TERM_COL]);
			$STUDENT_ENROLLMENT['REENTRY'] 			 			= trim($row[$REENTRY_COL]);
			$STUDENT_ENROLLMENT['PK_REPRESENTATIVE'] 			= trim($row[$PK_REPRESENTATIVE_COL]);
			$STUDENT_ENROLLMENT['PK_LEAD_SOURCE'] 			 	= trim($row[$PK_LEAD_SOURCE_COL]);
			$STUDENT_ENROLLMENT['STATUS_DATE'] 			 		= trim($row[$STATUS_DATE_COL]);
			$STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'] 			= trim($row[$PK_CAMPUS_PROGRAM_COL]);
			$STUDENT_ENROLLMENT['PK_TERM_MASTER'] 			 	= trim($row[$PK_TERM_MASTER_COL]);
			$STUDENT_ENROLLMENT['PK_FUNDING'] 			 		= trim($row[$PK_FUNDING_COL]);
			$STUDENT_ENROLLMENT['FT_PT_EFFECTIVE_DATE'] 		= trim($row[$FT_PT_EFFECTIVE_DATE_COL]);
			$STUDENT_ENROLLMENT['EXPECTED_GRAD_DATE'] 			= trim($row[$EXPECTED_GRAD_DATE_COL]);
			$STUDENT_ENROLLMENT['ORIGINAL_EXPECTED_GRAD_DATE'] 	= trim($row[$ORIGINAL_EXPECTED_GRAD_DATE_COL]);
			$STUDENT_ENROLLMENT['PK_SESSION'] 			 		= trim($row[$PK_SESSION_COL]);
			$STUDENT_ENROLLMENT['PK_ENROLLMENT_STATUS'] 		= trim($row[$PK_ENROLLMENT_STATUS_COL]);
			$STUDENT_ENROLLMENT['PK_STUDENT_GROUP'] 			= trim($row[$PK_STUDENT_GROUP_COL]);
			$STUDENT_ENROLLMENT['CONTRACT_SIGNED_DATE'] 		= trim($row[$CONTRACT_SIGNED_DATE_COL]);
			$STUDENT_ENROLLMENT['CONTRACT_END_DATE'] 			= trim($row[$CONTRACT_END_DATE_COL]);
			$STUDENT_ENROLLMENT['PK_STUDENT_STATUS'] 			= trim($row[$PK_STUDENT_STATUS_COL]);
			$STUDENT_ENROLLMENT['PK_DISTANCE_LEARNING'] 		= trim($row[$PK_DISTANCE_LEARNING_COL]);
			$STUDENT_ENROLLMENT['PK_DROP_REASON'] 			 	= trim($row[$PK_DROP_REASON_COL]);
			$STUDENT_ENROLLMENT['PK_PLACEMENT_STATUS']			= trim($row[$PK_PLACEMENT_STATUS_COL]);
			$STUDENT_ENROLLMENT['PK_SPECIAL_PROGRAM_INDICATOR']	= trim($row[$PK_SPECIAL_PROGRAM_INDICATOR_COL]);
			$STUDENT_ENROLLMENT['MIDPOINT_DATE'] 			 	= trim($row[$MIDPOINT_DATE_COL]);
			$STUDENT_ENROLLMENT['EXTERN_START_DATE'] 			= trim($row[$EXTERN_START_DATE_COL]);
			$STUDENT_ENROLLMENT['GRADE_DATE'] 			 		= trim($row[$GRADE_DATE_COL]);
			$STUDENT_ENROLLMENT['LDA'] 			 				= trim($row[$LDA_COL]);
			$STUDENT_ENROLLMENT['DROP_DATE'] 			 		= trim($row[$DROP_DATE_COL]);
			$STUDENT_ENROLLMENT['STRF_PAID_DATE'] 			 	= trim($row[$STRF_PAID_DATE_COL]);
			$STUDENT_ENROLLMENT['DETERMINATION_DATE'] 			= trim($row[$DETERMINATION_DATE_COL]);
			
			/* Ticket # 1762 */
			$STUDENT_ENROLLMENT['PK_LEAD_CONTACT_SOURCE'] 	= trim($row[$PK_LEAD_CONTACT_SOURCE_COL]); 
			
			if($STUDENT_ENROLLMENT['PK_LEAD_CONTACT_SOURCE'] != '') {
				$res_l = $db->Execute("select PK_LEAD_CONTACT_SOURCE from M_LEAD_CONTACT_SOURCE WHERE trim(LEAD_CONTACT_SOURCE) = '$STUDENT_ENROLLMENT[PK_LEAD_CONTACT_SOURCE]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					
				if($res_l->RecordCount() == 0) {
					$LEAD_CONTACT_SOURCE['LEAD_CONTACT_SOURCE']  	= $STUDENT_ENROLLMENT['PK_LEAD_CONTACT_SOURCE'];
					$LEAD_CONTACT_SOURCE['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
					$LEAD_CONTACT_SOURCE['CREATED_BY']  			= $_SESSION['PK_USER'];
					$LEAD_CONTACT_SOURCE['CREATED_ON']  			= date("Y-m-d H:i");
					db_perform('M_LEAD_CONTACT_SOURCE', $LEAD_CONTACT_SOURCE, 'insert');
					$STUDENT_ENROLLMENT['PK_LEAD_CONTACT_SOURCE'] = $db->insert_ID();
				} else {
					$STUDENT_ENROLLMENT['PK_LEAD_CONTACT_SOURCE'] = $res_l->fields['PK_LEAD_CONTACT_SOURCE'];
				}
			}
			/* Ticket # 1762 */
			
			if($STUDENT_ENROLLMENT['CONTRACT_SIGNED_DATE'] != '') {
				$CONTRACT_SIGNED_DATE = str_replace("/","-",$STUDENT_ENROLLMENT['CONTRACT_SIGNED_DATE']);
				$CONTRACT_SIGNED_DATE = explode("-",$CONTRACT_SIGNED_DATE);
				
				if($CONTRACT_SIGNED_DATE[2] < 100)
					$year = 2000 + $CONTRACT_SIGNED_DATE[2];
				else
					$year = $CONTRACT_SIGNED_DATE[2];
				
				$STUDENT_ENROLLMENT['CONTRACT_SIGNED_DATE'] = $year.'-'.$CONTRACT_SIGNED_DATE[0].'-'.$CONTRACT_SIGNED_DATE[1];
			}
			
			if($STUDENT_ENROLLMENT['CONTRACT_END_DATE'] != '') {
				$CONTRACT_END_DATE = str_replace("/","-",$STUDENT_ENROLLMENT['CONTRACT_END_DATE']);
				$CONTRACT_END_DATE = explode("-",$CONTRACT_END_DATE);
				
				if($CONTRACT_END_DATE[2] < 100)
					$year = 2000 + $CONTRACT_END_DATE[2];
				else
					$year = $CONTRACT_END_DATE[2];
				
				$STUDENT_ENROLLMENT['CONTRACT_END_DATE'] = $year.'-'.$CONTRACT_END_DATE[0].'-'.$CONTRACT_END_DATE[1];
			}
			
			if($STUDENT_ENROLLMENT['TRANSFER_IN'] != '') {
				if(strtolower($STUDENT_ENROLLMENT['TRANSFER_IN']) == 'yes' || strtolower($STUDENT_ENROLLMENT['TRANSFER_IN']) == 'y' )
					$STUDENT_ENROLLMENT['TRANSFER_IN'] = 1;
				else if(strtolower($STUDENT_ENROLLMENT['TRANSFER_IN']) == 'no' || strtolower($STUDENT_ENROLLMENT['TRANSFER_IN']) == 'n')
					$STUDENT_ENROLLMENT['TRANSFER_IN'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= TRANSFER_IN.' <b>'.$STUDENT_ENROLLMENT['TRANSFER_IN'].'</b>';
				}
			} else
				$STUDENT_ENROLLMENT['TRANSFER_IN'] = 0;
				
			if($STUDENT_ENROLLMENT['TRANSFER_OUT'] != '') {
				if(strtolower($STUDENT_ENROLLMENT['TRANSFER_OUT']) == 'yes' || strtolower($STUDENT_ENROLLMENT['TRANSFER_OUT']) == 'y' )
					$STUDENT_ENROLLMENT['TRANSFER_OUT'] = 1;
				else if(strtolower($STUDENT_ENROLLMENT['TRANSFER_OUT']) == 'no' || strtolower($STUDENT_ENROLLMENT['TRANSFER_OUT']) == 'n')
					$STUDENT_ENROLLMENT['TRANSFER_OUT'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= TRANSFER_OUT.' <b>'.$STUDENT_ENROLLMENT['TRANSFER_OUT'].'</b>';
				}
			} else
				$STUDENT_ENROLLMENT['TRANSFER_OUT'] = 0;
				
			if($STUDENT_ENROLLMENT['FIRST_TERM'] != '') {
				$FIRST_TERM1 = trim($STUDENT_ENROLLMENT['FIRST_TERM']);
				$res_l = $db->Execute("select PK_FIRST_TERM from M_FIRST_TERM WHERE ACTIVE = 1 AND TRIM(FIRST_TERM) = '$FIRST_TERM1' ");
				
				if($res_l->RecordCOunt() == 1)
					$STUDENT_ENROLLMENT['FIRST_TERM'] = $res_l->fields['PK_FIRST_TERM'];
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= FIRST_TERM.' <b>'.$STUDENT_ENROLLMENT['FIRST_TERM'].'</b>';
				}
			} else
				$STUDENT_ENROLLMENT['FIRST_TERM'] = 0;
				
			if($STUDENT_ENROLLMENT['REENTRY'] != '') {
				if(strtolower($STUDENT_ENROLLMENT['REENTRY']) == 'yes' || strtolower($STUDENT_ENROLLMENT['REENTRY']) == 'y' )
					$STUDENT_ENROLLMENT['REENTRY'] = 1;
				else if(strtolower($STUDENT_ENROLLMENT['REENTRY']) == 'no' || strtolower($STUDENT_ENROLLMENT['REENTRY']) == 'n')
					$STUDENT_ENROLLMENT['REENTRY'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= REENTRY.' <b>'.$STUDENT_ENROLLMENT['REENTRY'].'</b>';
				}
			} else
				$STUDENT_ENROLLMENT['REENTRY'] = 0;
				
			if($STUDENT_ENROLLMENT['PK_REPRESENTATIVE'] != '') {
				$res_l = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.ACTIVE = 1 AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = 2 AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND TRIM(CONCAT(FIRST_NAME,' ',LAST_NAME)) = '$STUDENT_ENROLLMENT[PK_REPRESENTATIVE]' ");
					
				if($res_l->RecordCount() == 0) {
					/*if($error_str != '')
						$error_str .= ', ';
					$error_str .= ADMISSION_REP.' <b>'.$STUDENT_ENROLLMENT['PK_EMPLOYEE_MASTER'].'</b>';*/
					
					$res_dep = $db->Execute("select PK_DEPARTMENT FROM M_DEPARTMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = 2 ");
					
					$EMP_NAME = explode(" ",trim($STUDENT_ENROLLMENT['PK_REPRESENTATIVE']));
					$EMPLOYEE_MASTER['FIRST_NAME']  	= $EMP_NAME[0];
					$EMPLOYEE_MASTER['LAST_NAME']  		= trim($EMP_NAME[1].' '.$EMP_NAME[2].' '.$EMP_NAME[2]);
					$EMPLOYEE_MASTER['PK_ACCOUNT']  	= $_SESSION['PK_ACCOUNT'];
					$EMPLOYEE_MASTER['CREATED_BY']  	= $_SESSION['PK_USER'];
					$EMPLOYEE_MASTER['CREATED_ON']  	= date("Y-m-d H:i");
					db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER, 'insert');
					$PK_EMPLOYEE_MASTER = $db->insert_ID();
					
					$EMPLOYEE_CONTACT['PK_EMPLOYEE_MASTER'] = $PK_EMPLOYEE_MASTER;
					$EMPLOYEE_CONTACT['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
					$EMPLOYEE_CONTACT['CREATED_BY']  		= $_SESSION['PK_USER'];
					$EMPLOYEE_CONTACT['CREATED_ON']  		= date("Y-m-d H:i");
					db_perform('S_EMPLOYEE_CONTACT', $EMPLOYEE_CONTACT, 'insert');
					
					$EMPLOYEE_DEPARTMENT['PK_DEPARTMENT']   	= $res_dep->fields['PK_DEPARTMENT'];
					$EMPLOYEE_DEPARTMENT['PK_EMPLOYEE_MASTER'] 	= $PK_EMPLOYEE_MASTER;
					$EMPLOYEE_DEPARTMENT['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
					$EMPLOYEE_DEPARTMENT['CREATED_BY']  		= $_SESSION['PK_USER'];
					$EMPLOYEE_DEPARTMENT['CREATED_ON']  		= date("Y-m-d H:i");
					db_perform('S_EMPLOYEE_DEPARTMENT', $EMPLOYEE_DEPARTMENT, 'insert');
					
					$STUDENT_ENROLLMENT['PK_REPRESENTATIVE'] = $PK_EMPLOYEE_MASTER;
				} else {
					$STUDENT_ENROLLMENT['PK_REPRESENTATIVE'] = $res_l->fields['PK_EMPLOYEE_MASTER'];
				}
			}
			
			if($STUDENT_ENROLLMENT['PK_LEAD_SOURCE'] != '') {
				$res_l = $db->Execute("select PK_LEAD_SOURCE from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND TRIM(LEAD_SOURCE) = '$STUDENT_ENROLLMENT[PK_LEAD_SOURCE]' ");
					
				if($res_l->RecordCount() == 0) {
					/*if($error_str != '')
						$error_str .= ', ';
					$error_str .= LEAD_SOURCE.' <b>'.$STUDENT_ENROLLMENT['PK_LEAD_SOURCE'].'</b>';*/
					
					
					$LEAD_SOURCE['LEAD_SOURCE'] = $STUDENT_ENROLLMENT['PK_LEAD_SOURCE'];
					$LEAD_SOURCE['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
					$LEAD_SOURCE['CREATED_BY']  = $_SESSION['PK_USER'];
					$LEAD_SOURCE['CREATED_ON']  = date("Y-m-d H:i");
					db_perform('M_LEAD_SOURCE', $LEAD_SOURCE, 'insert');
					$STUDENT_ENROLLMENT['PK_LEAD_SOURCE'] = $db->insert_ID();
				} else {
					$STUDENT_ENROLLMENT['PK_LEAD_SOURCE'] = $res_l->fields['PK_LEAD_SOURCE'];
				}
			}
			
			if($STUDENT_ENROLLMENT['STATUS_DATE'] != '') {
				$STATUS_DATE = str_replace("/","-",$STUDENT_ENROLLMENT['STATUS_DATE']);
				$STATUS_DATE = explode("-",$STATUS_DATE);
				if($STATUS_DATE[2] < 100)
					$year = 2000 + $STATUS_DATE[2];
				else
					$year = $STATUS_DATE[2];
				
				$STUDENT_ENROLLMENT['STATUS_DATE'] = $year.'-'.$STATUS_DATE[0].'-'.$STATUS_DATE[1];
			}
			
			if($STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'] != '') {
				$res_l = $db->Execute("select PK_CAMPUS_PROGRAM from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TRIM(CODE) = '$STUDENT_ENROLLMENT[PK_CAMPUS_PROGRAM]'");
					
				if($res_l->RecordCount() == 0) {
					/*if($error_str != '')
						$error_str .= ', ';
					$error_str .= PROGRAM.' <b>'.$STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'].'</b>';*/
					
					$CAMPUS_PROGRAM['CODE'] 	   = $STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'];
					$CAMPUS_PROGRAM['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
					$CAMPUS_PROGRAM['CREATED_BY']  = $_SESSION['PK_USER'];
					$CAMPUS_PROGRAM['CREATED_ON']  = date("Y-m-d H:i");
					db_perform('M_CAMPUS_PROGRAM', $CAMPUS_PROGRAM, 'insert');
					$PK_CAMPUS_PROGRAM = $db->insert_ID();
					
					$PROGRAM_ANALYTICS_SETUP['PK_CAMPUS_PROGRAM'] 	= $PK_CAMPUS_PROGRAM;
					$PROGRAM_ANALYTICS_SETUP['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
					$PROGRAM_ANALYTICS_SETUP['CREATED_BY']  		= $_SESSION['PK_USER'];
					$PROGRAM_ANALYTICS_SETUP['CREATED_ON']  		= date("Y-m-d H:i");
					db_perform('M_CAMPUS_PROGRAM_ANALYTICS_SETUP', $PROGRAM_ANALYTICS_SETUP, 'insert');
					
					$STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'] = $PK_CAMPUS_PROGRAM;
				} else {
					$STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'] = $res_l->fields['PK_CAMPUS_PROGRAM'];
				}
			}
			
			if($STUDENT_ENROLLMENT['PK_TERM_MASTER'] != '') {
				$PK_TERM_MASTER = str_replace("/","-",$STUDENT_ENROLLMENT['PK_TERM_MASTER']);
				$PK_TERM_MASTER = explode("-",$PK_TERM_MASTER);
				if($PK_TERM_MASTER[2] < 100)
					$year = 2000 + $PK_TERM_MASTER[2];
				else
					$year = $PK_TERM_MASTER[2];
				
				$STUDENT_ENROLLMENT['PK_TERM_MASTER'] = $year.'/'.$PK_TERM_MASTER[0].'/'.$PK_TERM_MASTER[1];
				
				$res_l = $db->Execute("select PK_TERM_MASTER from S_TERM_MASTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BEGIN_DATE = '$STUDENT_ENROLLMENT[PK_TERM_MASTER]' ");
					
				if($res_l->RecordCount() == 0) {
					/*if($error_str != '')
						$error_str .= ', ';
					$error_str .= FIRST_TERM_DATE.' <b>'.$STUDENT_ENROLLMENT['PK_TERM_MASTER'].'</b>';*/
					
					$TERM_MASTER['BEGIN_DATE']  = $STUDENT_ENROLLMENT['PK_TERM_MASTER'];
					$TERM_MASTER['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
					$TERM_MASTER['CREATED_BY']  = $_SESSION['PK_USER'];
					$TERM_MASTER['CREATED_ON']  = date("Y-m-d H:i");
					db_perform('S_TERM_MASTER', $TERM_MASTER, 'insert');
					$STUDENT_ENROLLMENT['PK_TERM_MASTER'] = $db->insert_ID();
				} else {
					$STUDENT_ENROLLMENT['PK_TERM_MASTER'] = $res_l->fields['PK_TERM_MASTER'];
				}
			}
			
			if($STUDENT_ENROLLMENT['PK_FUNDING'] != '') {
				$res_l = $db->Execute("select PK_FUNDING from M_FUNDING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND TRIM(FUNDING) = '$STUDENT_ENROLLMENT[PK_FUNDING]' ");
					
				if($res_l->RecordCount() == 0) {
					/*if($error_str != '')
						$error_str .= ', ';
					$error_str .= FUNDING.' <b>'.$STUDENT_ENROLLMENT['PK_FUNDING'].'</b>';*/
					
					$FUNDING['FUNDING']  	= $STUDENT_ENROLLMENT['PK_FUNDING'];
					$FUNDING['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
					$FUNDING['CREATED_BY']  = $_SESSION['PK_USER'];
					$FUNDING['CREATED_ON']  = date("Y-m-d H:i");
					db_perform('M_FUNDING', $FUNDING, 'insert');
					$STUDENT_ENROLLMENT['PK_FUNDING'] = $db->insert_ID();
				} else {
					$STUDENT_ENROLLMENT['PK_FUNDING'] = $res_l->fields['PK_FUNDING'];
				}
			}
			
			if($STUDENT_ENROLLMENT['FT_PT_EFFECTIVE_DATE'] != '') {
				$FT_PT_EFFECTIVE_DATE = str_replace("/","-",$STUDENT_ENROLLMENT['FT_PT_EFFECTIVE_DATE']);
				$FT_PT_EFFECTIVE_DATE = explode("-",$FT_PT_EFFECTIVE_DATE);
				if($FT_PT_EFFECTIVE_DATE[2] < 100)
					$year = 2000 + $FT_PT_EFFECTIVE_DATE[2];
				else
					$year = $FT_PT_EFFECTIVE_DATE[2];
				
				$STUDENT_ENROLLMENT['FT_PT_EFFECTIVE_DATE'] = $year.'-'.$FT_PT_EFFECTIVE_DATE[0].'-'.$FT_PT_EFFECTIVE_DATE[1];
			}
			
			if($STUDENT_ENROLLMENT['EXPECTED_GRAD_DATE'] != '') {
				$EXPECTED_GRAD_DATE = str_replace("/","-",$STUDENT_ENROLLMENT['EXPECTED_GRAD_DATE']);
				$EXPECTED_GRAD_DATE = explode("-",$EXPECTED_GRAD_DATE);
				if($EXPECTED_GRAD_DATE[2] < 100)
					$year = 2000 + $EXPECTED_GRAD_DATE[2];
				else
					$year = $EXPECTED_GRAD_DATE[2];
				
				$STUDENT_ENROLLMENT['EXPECTED_GRAD_DATE'] = $year.'-'.$EXPECTED_GRAD_DATE[0].'-'.$EXPECTED_GRAD_DATE[1];
			}
			
			if($STUDENT_ENROLLMENT['ORIGINAL_EXPECTED_GRAD_DATE'] != '') {
				$ORIGINAL_EXPECTED_GRAD_DATE = str_replace("/","-",$STUDENT_ENROLLMENT['ORIGINAL_EXPECTED_GRAD_DATE']);
				$ORIGINAL_EXPECTED_GRAD_DATE = explode("-",$ORIGINAL_EXPECTED_GRAD_DATE);
				if($ORIGINAL_EXPECTED_GRAD_DATE[2] < 100)
					$year = 2000 + $ORIGINAL_EXPECTED_GRAD_DATE[2];
				else
					$year = $ORIGINAL_EXPECTED_GRAD_DATE[2];
				
				$STUDENT_ENROLLMENT['ORIGINAL_EXPECTED_GRAD_DATE'] = $year.'-'.$ORIGINAL_EXPECTED_GRAD_DATE[0].'-'.$ORIGINAL_EXPECTED_GRAD_DATE[1];
			}
			
			if($STUDENT_ENROLLMENT['PK_SESSION'] != '') {
				$res_l = $db->Execute("select PK_SESSION from M_SESSION WHERE ACTIVE = 1 AND TRIM(SESSION) = '$STUDENT_ENROLLMENT[PK_SESSION]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= SESSION.' <b>'.$STUDENT_ENROLLMENT['PK_SESSION'].'</b>';
				} else {
					$STUDENT_ENROLLMENT['PK_SESSION'] = $res_l->fields['PK_SESSION'];
				}
			}
			
			if($STUDENT_ENROLLMENT['PK_ENROLLMENT_STATUS'] != '') {
				$res_l = $db->Execute("select PK_ENROLLMENT_STATUS from M_ENROLLMENT_STATUS WHERE ACTIVE = 1 AND TRIM(CODE) = '$STUDENT_ENROLLMENT[PK_ENROLLMENT_STATUS]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= FULL_PART_TIME.' <b>'.$STUDENT_ENROLLMENT['PK_ENROLLMENT_STATUS'].'</b>';
				} else {
					$STUDENT_ENROLLMENT['PK_ENROLLMENT_STATUS'] = $res_l->fields['PK_ENROLLMENT_STATUS'];
				}
			}
			
			if($STUDENT_ENROLLMENT['PK_STUDENT_GROUP'] != '') {
				$res_l = $db->Execute("select PK_STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND TRIM(STUDENT_GROUP) = '$STUDENT_ENROLLMENT[PK_STUDENT_GROUP]' ");
					
				if($res_l->RecordCount() == 0) {
					/*if($error_str != '')
						$error_str .= ', ';
					$error_str .= STUDENT_GROUP.' <b>'.$STUDENT_ENROLLMENT['PK_STUDENT_GROUP'].'</b>';*/
					
					$STUDENT_GROUP['STUDENT_GROUP'] = $STUDENT_ENROLLMENT['PK_STUDENT_GROUP'];
					$STUDENT_GROUP['PK_ACCOUNT']  	= $_SESSION['PK_ACCOUNT'];
					$STUDENT_GROUP['CREATED_BY']  	= $_SESSION['PK_USER'];
					$STUDENT_GROUP['CREATED_ON']  	= date("Y-m-d H:i");
					db_perform('M_STUDENT_GROUP', $STUDENT_GROUP, 'insert');
					$STUDENT_ENROLLMENT['PK_STUDENT_GROUP'] = $db->insert_ID();
				} else {
					$STUDENT_ENROLLMENT['PK_STUDENT_GROUP'] = $res_l->fields['PK_STUDENT_GROUP'];
				}
			}
			
			if($STUDENT_ENROLLMENT['CONTRACT_END_DATE'] != '') {
				$CONTRACT_END_DATE = str_replace("/","-",$STUDENT_ENROLLMENT['CONTRACT_END_DATE']);
				$CONTRACT_END_DATE = explode("-",$CONTRACT_END_DATE);
				if($CONTRACT_END_DATE[2] < 100)
					$year = 2000 + $CONTRACT_END_DATE[2];
				else
					$year = $CONTRACT_END_DATE[2];
				
				$STUDENT_ENROLLMENT['CONTRACT_END_DATE'] = $year.'-'.$CONTRACT_END_DATE[0].'-'.$CONTRACT_END_DATE[1];
			}
			
			if($STUDENT_ENROLLMENT['PK_STUDENT_STATUS'] != '') {
				$res_l = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND TRIM(STUDENT_STATUS) = '$STUDENT_ENROLLMENT[PK_STUDENT_STATUS]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= STATUS.' <b>'.$STUDENT_ENROLLMENT['PK_STUDENT_STATUS'].'</b>';
					
					//$res = $db->Execute("SELECT PK_STUDENT_STATUS FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS_MASTER = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
					//$STUDENT_ENROLLMENT['PK_STUDENT_STATUS'] = $res->fields['PK_STUDENT_STATUS'];
				} else {
					$STUDENT_ENROLLMENT['PK_STUDENT_STATUS'] = $res_l->fields['PK_STUDENT_STATUS'];
				}
			} else {
				$res = $db->Execute("SELECT PK_STUDENT_STATUS FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS_MASTER = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
				$STUDENT_ENROLLMENT['PK_STUDENT_STATUS'] = $res->fields['PK_STUDENT_STATUS'];
			}
			
			if($STUDENT_ENROLLMENT['PK_DISTANCE_LEARNING'] != '') {
				$res_l = $db->Execute("select PK_DISTANCE_LEARNING from M_DISTANCE_LEARNING WHERE ACTIVE = 1 AND ACTIVE = 1 AND TRIM(DISTANCE_LEARNING) = '$STUDENT_ENROLLMENT[PK_DISTANCE_LEARNING]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= DISTANCE_LEARNING.' <b>'.$STUDENT_ENROLLMENT['PK_DISTANCE_LEARNING'].'</b>';
				} else {
					$STUDENT_ENROLLMENT['PK_DISTANCE_LEARNING'] = $res_l->fields['PK_DISTANCE_LEARNING'];
				}
			}
			
			if($STUDENT_ENROLLMENT['PK_DROP_REASON'] != '') {
				$res_l = $db->Execute("select PK_DROP_REASON from M_DROP_REASON WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TRIM(DROP_REASON) = '$STUDENT_ENROLLMENT[PK_DROP_REASON]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= DROP_REASON.' <b>'.$STUDENT_ENROLLMENT['PK_DROP_REASON'].'</b>';
				} else {
					$STUDENT_ENROLLMENT['PK_DROP_REASON'] = $res_l->fields['PK_DROP_REASON'];
				}
			}
			
			if($STUDENT_ENROLLMENT['PK_PLACEMENT_STATUS'] != '') {
				$res_l = $db->Execute("select PK_PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND TRIM(PLACEMENT_STATUS) = '$STUDENT_ENROLLMENT[PK_PLACEMENT_STATUS]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= PLACEMENT_STATUS.' <b>'.$STUDENT_ENROLLMENT['PK_PLACEMENT_STATUS'].'</b>';
				} else {
					$STUDENT_ENROLLMENT['PLACEMENT_STATUS'] = $res_l->fields['PK_PLACEMENT_STATUS'];
				}
			}
			
			if($STUDENT_ENROLLMENT['PK_SPECIAL_PROGRAM_INDICATOR'] != '') {
				$res_l = $db->Execute("select PK_SPECIAL_PROGRAM_INDICATOR from M_SPECIAL_PROGRAM_INDICATOR WHERE ACTIVE = 1 AND TRIM(CODE) = '$STUDENT_ENROLLMENT[PK_SPECIAL_PROGRAM_INDICATOR]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= SPECIAL_PROGRAM_INDICATOR.' <b>'.$STUDENT_ENROLLMENT['PK_SPECIAL_PROGRAM_INDICATOR'].'</b>';
				} else {
					$STUDENT_ENROLLMENT['PLACEMENT_STATUS'] = $res_l->fields['PK_SPECIAL_PROGRAM_INDICATOR'];
				}
			}
			
			if($STUDENT_ENROLLMENT['MIDPOINT_DATE'] != '') {
				$MIDPOINT_DATE = str_replace("/","-",$STUDENT_ENROLLMENT['MIDPOINT_DATE']);
				$MIDPOINT_DATE = explode("-",$MIDPOINT_DATE);
				if($MIDPOINT_DATE[2] < 100)
					$year = 2000 + $MIDPOINT_DATE[2];
				else
					$year = $MIDPOINT_DATE[2];
				
				$STUDENT_ENROLLMENT['MIDPOINT_DATE'] = $year.'-'.$MIDPOINT_DATE[0].'-'.$MIDPOINT_DATE[1];
			}
			
			if($STUDENT_ENROLLMENT['EXTERN_START_DATE'] != '') {
				$EXTERN_START_DATE = str_replace("/","-",$STUDENT_ENROLLMENT['EXTERN_START_DATE']);
				$EXTERN_START_DATE = explode("-",$EXTERN_START_DATE);
				if($EXTERN_START_DATE[2] < 100)
					$year = 2000 + $EXTERN_START_DATE[2];
				else
					$year = $EXTERN_START_DATE[2];
				
				$STUDENT_ENROLLMENT['EXTERN_START_DATE'] = $year.'-'.$EXTERN_START_DATE[0].'-'.$EXTERN_START_DATE[1];
			}
			
			if($STUDENT_ENROLLMENT['GRADE_DATE'] != '') {
				$GRADE_DATE = str_replace("/","-",$STUDENT_ENROLLMENT['GRADE_DATE']);
				$GRADE_DATE = explode("-",$GRADE_DATE);
				if($GRADE_DATE[2] < 100)
					$year = 2000 + $GRADE_DATE[2];
				else
					$year = $GRADE_DATE[2];
				
				$STUDENT_ENROLLMENT['GRADE_DATE'] = $year.'-'.$GRADE_DATE[0].'-'.$GRADE_DATE[1];
			}
			
			if($STUDENT_ENROLLMENT['LDA'] != '') {
				$LDA = str_replace("/","-",$STUDENT_ENROLLMENT['LDA']);
				$LDA = explode("-",$LDA);
				if($LDA[2] < 100)
					$year = 2000 + $LDA[2];
				else
					$year = $LDA[2];
				
				$STUDENT_ENROLLMENT['LDA'] = $year.'-'.$LDA[0].'-'.$LDA[1];
			}
			
			if($STUDENT_ENROLLMENT['DROP_DATE'] != '') {
				$DROP_DATE = str_replace("/","-",$STUDENT_ENROLLMENT['DROP_DATE']);
				$DROP_DATE = explode("-",$DROP_DATE);
				if($DROP_DATE[2] < 100)
					$year = 2000 + $DROP_DATE[2];
				else
					$year = $DROP_DATE[2];
				
				$STUDENT_ENROLLMENT['DROP_DATE'] = $year.'-'.$DROP_DATE[0].'-'.$DROP_DATE[1];
			}
			
			if($STUDENT_ENROLLMENT['STRF_PAID_DATE'] != '') {
				$STRF_PAID_DATE = str_replace("/","-",$STUDENT_ENROLLMENT['STRF_PAID_DATE']);
				$STRF_PAID_DATE = explode("-",$STRF_PAID_DATE);
				if($STRF_PAID_DATE[2] < 100)
					$year = 2000 + $STRF_PAID_DATE[2];
				else
					$year = $STRF_PAID_DATE[2];
				
				$STUDENT_ENROLLMENT['STRF_PAID_DATE'] = $year.'-'.$STRF_PAID_DATE[0].'-'.$STRF_PAID_DATE[1];
			}
			
			if($STUDENT_ENROLLMENT['DETERMINATION_DATE'] != '') {
				$DETERMINATION_DATE = str_replace("/","-",$STUDENT_ENROLLMENT['DETERMINATION_DATE']);
				$DETERMINATION_DATE = explode("-",$DETERMINATION_DATE);
				if($DETERMINATION_DATE[2] < 100)
					$year = 2000 + $DETERMINATION_DATE[2];
				else
					$year = $DETERMINATION_DATE[2];
				
				$STUDENT_ENROLLMENT['DETERMINATION_DATE'] = $year.'-'.$DETERMINATION_DATE[0].'-'.$DETERMINATION_DATE[1];
			}
			
			$STUDENT_CONTACT['ADDRESS'] 	=  trim($row[$ADDRESS_COL]);
			$STUDENT_CONTACT['ADDRESS_1']	=  trim($row[$ADDRESS_1_COL]);
			$STUDENT_CONTACT['CITY'] 		=  trim($row[$CITY_COL]);
			$STUDENT_CONTACT['PK_STATES'] 	=  trim($row[$STATE_COL]);
			$STUDENT_CONTACT['ZIP'] 		=  trim($row[$ZIP_COL]);
			$STUDENT_CONTACT['PK_COUNTRY'] 	=  trim($row[$COUNTRY_COL]);
			$STUDENT_CONTACT['HOME_PHONE'] 	=  trim($row[$HOME_PHONE_COL]);
			$STUDENT_CONTACT['WORK_PHONE'] 	=  trim($row[$WORK_PHONE_COL]);
			$STUDENT_CONTACT['CELL_PHONE'] 	=  trim($row[$MOBILE_PHONE_COL]);
			$STUDENT_CONTACT['OTHER_PHONE'] =  trim($row[$OTHER_PHONE_COL]);
			$STUDENT_CONTACT['EMAIL'] 		=  trim($row[$EMAIL_COL]);
			$STUDENT_CONTACT['USE_EMAIL'] 	=  trim($row[$USE_EMAIL_COL]);
			$STUDENT_CONTACT['EMAIL_OTHER'] =  trim($row[$EMAIL_OTHER_COL]);
			
			$STUDENT_CONTACT['HOME_PHONE_INVALID'] 	=  trim($row[$HOME_PHONE_INVALID_COL]);
			$STUDENT_CONTACT['WORK_PHONE_INVALID'] 	=  trim($row[$WORK_PHONE_INVALID_COL]);
			$STUDENT_CONTACT['OPT_OUT'] 			=  trim($row[$OPT_OUT_COL]);
			$STUDENT_CONTACT['CELL_PHONE_INVALID'] 	=  trim($row[$CELL_PHONE_INVALID_COL]);
			$STUDENT_CONTACT['OTHER_PHONE_INVALID'] =  trim($row[$OTHER_PHONE_INVALID_COL]);
			$STUDENT_CONTACT['EMAIL_INVALID'] 		=  trim($row[$EMAIL_INVALID_COL]);
			$STUDENT_CONTACT['EMAIL_OTHER_INVALID'] =  trim($row[$EMAIL_OTHER_INVALID_COL]);
			
			if($STUDENT_CONTACT['HOME_PHONE'] != '') {
				$STUDENT_CONTACT['HOME_PHONE'] = preg_replace( '/[^0-9]/', '',$STUDENT_CONTACT['HOME_PHONE']);
				$HOME_PHONE = $STUDENT_CONTACT['HOME_PHONE'];
				
				$HOME_PHONE = '('.$HOME_PHONE[0].$HOME_PHONE[1].$HOME_PHONE[2].') '.$HOME_PHONE[3].$HOME_PHONE[4].$HOME_PHONE[5].'-'.$HOME_PHONE[6].$HOME_PHONE[7].$HOME_PHONE[8].$HOME_PHONE[9];
					
				$STUDENT_CONTACT['HOME_PHONE'] = $HOME_PHONE;
			}
			
			if($STUDENT_CONTACT['WORK_PHONE'] != '') {
				$STUDENT_CONTACT['WORK_PHONE'] = preg_replace( '/[^0-9]/', '',$STUDENT_CONTACT['WORK_PHONE']);
				$WORK_PHONE = $STUDENT_CONTACT['WORK_PHONE'];
				
				$WORK_PHONE = '('.$WORK_PHONE[0].$WORK_PHONE[1].$WORK_PHONE[2].') '.$WORK_PHONE[3].$WORK_PHONE[4].$WORK_PHONE[5].'-'.$WORK_PHONE[6].$WORK_PHONE[7].$WORK_PHONE[8].$WORK_PHONE[9];
					
				$STUDENT_CONTACT['WORK_PHONE'] = $WORK_PHONE;
			}
			
			if($STUDENT_CONTACT['CELL_PHONE'] != '') {
				$STUDENT_CONTACT['CELL_PHONE'] = preg_replace( '/[^0-9]/', '',$STUDENT_CONTACT['CELL_PHONE']);
				$CELL_PHONE = $STUDENT_CONTACT['CELL_PHONE'];
				
				$CELL_PHONE = '('.$CELL_PHONE[0].$CELL_PHONE[1].$CELL_PHONE[2].') '.$CELL_PHONE[3].$CELL_PHONE[4].$CELL_PHONE[5].'-'.$CELL_PHONE[6].$CELL_PHONE[7].$CELL_PHONE[8].$CELL_PHONE[9];
					
				$STUDENT_CONTACT['CELL_PHONE'] = $CELL_PHONE;
			}
			
			if($STUDENT_CONTACT['OTHER_PHONE'] != '') {
				$STUDENT_CONTACT['OTHER_PHONE'] = preg_replace( '/[^0-9]/', '',$STUDENT_CONTACT['OTHER_PHONE']);
				$OTHER_PHONE = $STUDENT_CONTACT['OTHER_PHONE'];
				
				$OTHER_PHONE = '('.$OTHER_PHONE[0].$OTHER_PHONE[1].$OTHER_PHONE[2].') '.$OTHER_PHONE[3].$OTHER_PHONE[4].$OTHER_PHONE[5].'-'.$OTHER_PHONE[6].$OTHER_PHONE[7].$OTHER_PHONE[8].$OTHER_PHONE[9];
					
				$STUDENT_CONTACT['OTHER_PHONE'] = $OTHER_PHONE;
			}
			
			if($STUDENT_CONTACT['PK_STATES'] != '') {
				$res_l = $db->Execute("select PK_STATES from Z_STATES WHERE trim(STATE_CODE) = '$STUDENT_CONTACT[PK_STATES]' ");
			
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= STATE.' <b>'.$STUDENT_CONTACT['PK_STATES'].'</b>';
				} else {
					$STUDENT_CONTACT['PK_STATES'] = $res_l->fields['PK_STATES'];
				}
			}
			
			if($STUDENT_CONTACT['PK_COUNTRY'] != '') {
				$res_l = $db->Execute("select PK_COUNTRY from Z_COUNTRY WHERE trim(CODE) = '$STUDENT_CONTACT[PK_COUNTRY]' OR NAME = '$STUDENT_CONTACT[PK_COUNTRY]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= COUNTRY.' <b>'.$STUDENT_CONTACT['PK_COUNTRY'].'</b>';
				} else {
					$STUDENT_CONTACT['PK_COUNTRY'] = $res_l->fields['PK_COUNTRY'];
				}
			}
			
			if($STUDENT_CONTACT['USE_EMAIL'] != '') {
				if(strtolower($STUDENT_CONTACT['USE_EMAIL']) == 'yes' || strtolower($STUDENT_CONTACT['USE_EMAIL']) == 'y' )
					$STUDENT_CONTACT['USE_EMAIL'] = 1;
				else if(strtolower($STUDENT_CONTACT['USE_EMAIL']) == 'no' || strtolower($STUDENT_CONTACT['USE_EMAIL']) == 'n')
					$STUDENT_CONTACT['USE_EMAIL'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= USE_EMAIL.' <b>'.$STUDENT_CONTACT['USE_EMAIL'].'</b>';
				}
			}
			
			if($STUDENT_CONTACT['HOME_PHONE_INVALID'] != '') {
				if(strtolower($STUDENT_CONTACT['HOME_PHONE_INVALID']) == 'yes' || strtolower($STUDENT_CONTACT['HOME_PHONE_INVALID']) == 'y' )
					$STUDENT_CONTACT['HOME_PHONE_INVALID'] = 1;
				else if(strtolower($STUDENT_CONTACT['HOME_PHONE_INVALID']) == 'no' || strtolower($STUDENT_CONTACT['HOME_PHONE_INVALID']) == 'n')
					$STUDENT_CONTACT['HOME_PHONE_INVALID'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= HOME_PHONE.' '.INVALID.' <b>'.$STUDENT_CONTACT['HOME_PHONE_INVALID'].'</b>';
				}
			}
			
			if($STUDENT_CONTACT['WORK_PHONE_INVALID'] != '') {
				if(strtolower($STUDENT_CONTACT['WORK_PHONE_INVALID']) == 'yes' || strtolower($STUDENT_CONTACT['WORK_PHONE_INVALID']) == 'y' )
					$STUDENT_CONTACT['WORK_PHONE_INVALID'] = 1;
				else if(strtolower($STUDENT_CONTACT['WORK_PHONE_INVALID']) == 'no' || strtolower($STUDENT_CONTACT['WORK_PHONE_INVALID']) == 'n')
					$STUDENT_CONTACT['WORK_PHONE_INVALID'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= WORK_PHONE.' '.INVALID.' <b>'.$STUDENT_CONTACT['WORK_PHONE_INVALID'].'</b>';
				}
			}
			
			if($STUDENT_CONTACT['OPT_OUT'] != '') {
				if(strtolower($STUDENT_CONTACT['OPT_OUT']) == 'yes' || strtolower($STUDENT_CONTACT['OPT_OUT']) == 'y' )
					$STUDENT_CONTACT['OPT_OUT'] = 1;
				else if(strtolower($STUDENT_CONTACT['OPT_OUT']) == 'no' || strtolower($STUDENT_CONTACT['OPT_OUT']) == 'n')
					$STUDENT_CONTACT['OPT_OUT'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= OPTOUT.' <b>'.$STUDENT_CONTACT['OPT_OUT'].'</b>';
				}
			}
			
			if($STUDENT_CONTACT['OTHER_PHONE_INVALID'] != '') {
				if(strtolower($STUDENT_CONTACT['OTHER_PHONE_INVALID']) == 'yes' || strtolower($STUDENT_CONTACT['OTHER_PHONE_INVALID']) == 'y' )
					$STUDENT_CONTACT['OTHER_PHONE_INVALID'] = 1;
				else if(strtolower($STUDENT_CONTACT['OTHER_PHONE_INVALID']) == 'no' || strtolower($STUDENT_CONTACT['OTHER_PHONE_INVALID']) == 'n')
					$STUDENT_CONTACT['OTHER_PHONE_INVALID'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= OTHER_PHONE.' '.INVALID.' <b>'.$STUDENT_CONTACT['OTHER_PHONE_INVALID'].'</b>';
				}
			}
			
			if($STUDENT_CONTACT['CELL_PHONE_INVALID'] != '') {
				if(strtolower($STUDENT_CONTACT['CELL_PHONE_INVALID']) == 'yes' || strtolower($STUDENT_CONTACT['CELL_PHONE_INVALID']) == 'y' )
					$STUDENT_CONTACT['CELL_PHONE_INVALID'] = 1;
				else if(strtolower($STUDENT_CONTACT['CELL_PHONE_INVALID']) == 'no' || strtolower($STUDENT_CONTACT['CELL_PHONE_INVALID']) == 'n')
					$STUDENT_CONTACT['CELL_PHONE_INVALID'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= CELL_PHONE.' '.INVALID.' <b>'.$STUDENT_CONTACT['CELL_PHONE_INVALID'].'</b>';
				}
			}
			
			if($STUDENT_CONTACT['EMAIL_INVALID'] != '') {
				if(strtolower($STUDENT_CONTACT['EMAIL_INVALID']) == 'yes' || strtolower($STUDENT_CONTACT['EMAIL_INVALID']) == 'y' )
					$STUDENT_CONTACT['EMAIL_INVALID'] = 1;
				else if(strtolower($STUDENT_CONTACT['EMAIL_INVALID']) == 'no' || strtolower($STUDENT_CONTACT['EMAIL_INVALID']) == 'n')
					$STUDENT_CONTACT['EMAIL_INVALID'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= EMAIL.' '.INVALID.' <b>'.$STUDENT_CONTACT['EMAIL_INVALID'].'</b>';
				}
			}

			if($STUDENT_CONTACT['EMAIL_OTHER_INVALID'] != '') {
				if(strtolower($STUDENT_CONTACT['EMAIL_OTHER_INVALID']) == 'yes' || strtolower($STUDENT_CONTACT['EMAIL_OTHER_INVALID']) == 'y' )
					$STUDENT_CONTACT['EMAIL_OTHER_INVALID'] = 1;
				else if(strtolower($STUDENT_CONTACT['EMAIL_OTHER_INVALID']) == 'no' || strtolower($STUDENT_CONTACT['EMAIL_OTHER_INVALID']) == 'n')
					$STUDENT_CONTACT['EMAIL_OTHER_INVALID'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= OTHER_EMAIL.' '.INVALID.' <b>'.$STUDENT_CONTACT['EMAIL_OTHER_INVALID'].'</b>';
				}
			}

			$E_STUDENT_CONTACT['CONTACT_NAME'] 						= trim($row[$EMERGENCY_CONTACT_NAME_COL]);
			$E_STUDENT_CONTACT['PK_STUDENT_RELATIONSHIP_MASTER'] 	= trim($row[$EMERGENCY_RELATIONSHIP_COL]);
			$E_STUDENT_CONTACT['ADDRESS'] 							= trim($row[$EMERGENCY_ADDRESS_COL]);
			$E_STUDENT_CONTACT['ADDRESS_1']							= trim($row[$EMERGENCY_ADDRESS_1_COL]);
			$E_STUDENT_CONTACT['CITY'] 								= trim($row[$EMERGENCY_CITY_COL]);
			$E_STUDENT_CONTACT['PK_STATES'] 						= trim($row[$EMERGENCY_STATE_COL]);
			$E_STUDENT_CONTACT['ZIP'] 								= trim($row[$EMERGENCY_ZIP_COL]);
			$E_STUDENT_CONTACT['PK_COUNTRY'] 						= trim($row[$EMERGENCY_COUNTRY_COL]);
			$E_STUDENT_CONTACT['HOME_PHONE'] 						= trim($row[$EMERGENCY_HOME_PHONE_COL]);
			$E_STUDENT_CONTACT['WORK_PHONE'] 						= trim($row[$EMERGENCY_WORK_PHONE_COL]);
			$E_STUDENT_CONTACT['CELL_PHONE'] 						= trim($row[$EMERGENCY_MOBILE_PHONE_COL]);
			$E_STUDENT_CONTACT['OTHER_PHONE'] 						= trim($row[$EMERGENCY_OTHER_PHONE_COL]);
			$E_STUDENT_CONTACT['EMAIL'] 							= trim($row[$EMERGENCY_EMAIL_COL]);
			$E_STUDENT_CONTACT['USE_EMAIL'] 						= trim($row[$EMERGENCY_USE_EMAIL_COL]);
			$E_STUDENT_CONTACT['EMAIL_OTHER'] 						= trim($row[$EMERGENCY_EMAIL_OTHER_COL]);
			
			$E_STUDENT_CONTACT['HOME_PHONE_INVALID'] 		=  trim($row[$EMERGENCY_HOME_PHONE_INVALID_COL]);
			$E_STUDENT_CONTACT['WORK_PHONE_INVALID'] 		=  trim($row[$EMERGENCY_WORK_PHONE_INVALID_COL]);
			$E_STUDENT_CONTACT['OPT_OUT'] 					=  trim($row[$EMERGENCY_OPT_OUT_COL]);
			$E_STUDENT_CONTACT['CELL_PHONE_INVALID'] 		=  trim($row[$EMERGENCY_CELL_PHONE_INVALID_COL]);
			$E_STUDENT_CONTACT['OTHER_PHONE_INVALID'] 		=  trim($row[$EMERGENCY_OTHER_PHONE_INVALID_COL]);
			$E_STUDENT_CONTACT['EMAIL_INVALID'] 			=  trim($row[$EMERGENCY_EMAIL_INVALID_COL]);
			$E_STUDENT_CONTACT['EMAIL_OTHER_INVALID'] 		=  trim($row[$EMERGENCY_EMAIL_OTHER_INVALID_COL]);
	
			if($E_STUDENT_CONTACT['HOME_PHONE'] != '') {
				$E_STUDENT_CONTACT['HOME_PHONE'] = preg_replace( '/[^0-9]/', '',$E_STUDENT_CONTACT['HOME_PHONE']);
				$HOME_PHONE = $E_STUDENT_CONTACT['HOME_PHONE'];
				
				$HOME_PHONE = '('.$HOME_PHONE[0].$HOME_PHONE[1].$HOME_PHONE[2].') '.$HOME_PHONE[3].$HOME_PHONE[4].$HOME_PHONE[5].'-'.$HOME_PHONE[6].$HOME_PHONE[7].$HOME_PHONE[8].$HOME_PHONE[9];
					
				$E_STUDENT_CONTACT['HOME_PHONE'] = $HOME_PHONE;
			}
			
			if($E_STUDENT_CONTACT['WORK_PHONE'] != '') {
				$E_STUDENT_CONTACT['WORK_PHONE'] = preg_replace( '/[^0-9]/', '',$E_STUDENT_CONTACT['WORK_PHONE']);
				$WORK_PHONE = $E_STUDENT_CONTACT['WORK_PHONE'];
				
				$WORK_PHONE = '('.$WORK_PHONE[0].$WORK_PHONE[1].$WORK_PHONE[2].') '.$WORK_PHONE[3].$WORK_PHONE[4].$WORK_PHONE[5].'-'.$WORK_PHONE[6].$WORK_PHONE[7].$WORK_PHONE[8].$WORK_PHONE[9];
					
				$E_STUDENT_CONTACT['WORK_PHONE'] = $WORK_PHONE;
			}
			
			if($E_STUDENT_CONTACT['CELL_PHONE'] != '') {
				$E_STUDENT_CONTACT['CELL_PHONE'] = preg_replace( '/[^0-9]/', '',$E_STUDENT_CONTACT['CELL_PHONE']);
				$CELL_PHONE = $E_STUDENT_CONTACT['CELL_PHONE'];
				
				$CELL_PHONE = '('.$CELL_PHONE[0].$CELL_PHONE[1].$CELL_PHONE[2].') '.$CELL_PHONE[3].$CELL_PHONE[4].$CELL_PHONE[5].'-'.$CELL_PHONE[6].$CELL_PHONE[7].$CELL_PHONE[8].$CELL_PHONE[9];
					
				$E_STUDENT_CONTACT['CELL_PHONE'] = $CELL_PHONE;
			}
			
			if($E_STUDENT_CONTACT['OTHER_PHONE'] != '') {
				$E_STUDENT_CONTACT['OTHER_PHONE'] = preg_replace( '/[^0-9]/', '',$E_STUDENT_CONTACT['OTHER_PHONE']);
				$OTHER_PHONE = $E_STUDENT_CONTACT['OTHER_PHONE'];
				
				$OTHER_PHONE = '('.$OTHER_PHONE[0].$OTHER_PHONE[1].$OTHER_PHONE[2].') '.$OTHER_PHONE[3].$OTHER_PHONE[4].$OTHER_PHONE[5].'-'.$OTHER_PHONE[6].$OTHER_PHONE[7].$OTHER_PHONE[8].$OTHER_PHONE[9];
					
				$E_STUDENT_CONTACT['OTHER_PHONE'] = $OTHER_PHONE;
			}
			
			if($E_STUDENT_CONTACT['PK_STUDENT_RELATIONSHIP_MASTER'] != '') {
				$res_l = $db->Execute("select PK_STUDENT_RELATIONSHIP_MASTER from M_STUDENT_RELATIONSHIP_MASTER WHERE trim(STUDENT_RELATIONSHIP) = '$E_STUDENT_CONTACT[PK_STUDENT_RELATIONSHIP_MASTER]' AND ACTIVE = 1");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= EMERGENCY.' '.STUDENT_RELATIONSHIP.' <b>'.$E_STUDENT_CONTACT['PK_STUDENT_RELATIONSHIP_MASTER'].'</b>';
				} else {
					$E_STUDENT_CONTACT['PK_STUDENT_RELATIONSHIP_MASTER'] = $res_l->fields['PK_STUDENT_RELATIONSHIP_MASTER'];
				}
			}
			
			if($E_STUDENT_CONTACT['PK_STATES'] != '') {
				$res_l = $db->Execute("select PK_STATES from Z_STATES WHERE trim(STATE_CODE) = '$E_STUDENT_CONTACT[PK_STATES]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= EMERGENCY.' '.STATE.' <b>'.$E_STUDENT_CONTACT['PK_STATES'].'</b>';
				} else {
					$E_STUDENT_CONTACT['PK_STATES'] = $res_l->fields['PK_STATES'];
				}
			}
			
			if($E_STUDENT_CONTACT['PK_COUNTRY'] != '') {
				$res_l = $db->Execute("select PK_COUNTRY from Z_COUNTRY WHERE trim(CODE) = '$E_STUDENT_CONTACT[PK_COUNTRY]' OR NAME = '$E_STUDENT_CONTACT[PK_COUNTRY]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= EMERGENCY.' '.COUNTRY.' <b>'.$E_STUDENT_CONTACT['PK_COUNTRY'].'</b>';
				} else {
					$E_STUDENT_CONTACT['PK_COUNTRY'] = $res_l->fields['PK_COUNTRY'];
				}
			}
			
			if($E_STUDENT_CONTACT['USE_EMAIL'] != '') {
				if(strtolower($E_STUDENT_CONTACT['USE_EMAIL']) == 'yes' || strtolower($E_STUDENT_CONTACT['USE_EMAIL']) == 'y' )
					$E_STUDENT_CONTACT['USE_EMAIL'] = 1;
				else if(strtolower($E_STUDENT_CONTACT['USE_EMAIL']) == 'no' || strtolower($E_STUDENT_CONTACT['USE_EMAIL']) == 'n')
					$E_STUDENT_CONTACT['USE_EMAIL'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= EMERGENCY.' '.USE_EMAIL.' <b>'.$E_STUDENT_CONTACT['USE_EMAIL'].'</b>';
				}
			}
			
			if($E_STUDENT_CONTACT['HOME_PHONE_INVALID'] != '') {
				if(strtolower($E_STUDENT_CONTACT['HOME_PHONE_INVALID']) == 'yes' || strtolower($E_STUDENT_CONTACT['HOME_PHONE_INVALID']) == 'y' )
					$E_STUDENT_CONTACT['HOME_PHONE_INVALID'] = 1;
				else if(strtolower($E_STUDENT_CONTACT['HOME_PHONE_INVALID']) == 'no' || strtolower($E_STUDENT_CONTACT['HOME_PHONE_INVALID']) == 'n')
					$E_STUDENT_CONTACT['HOME_PHONE_INVALID'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= EMERGENCY.' '.HOME_PHONE.' '.INVALID.' <b>'.$E_STUDENT_CONTACT['HOME_PHONE_INVALID'].'</b>';
				}
			}
			
			if($E_STUDENT_CONTACT['WORK_PHONE_INVALID'] != '') {
				if(strtolower($E_STUDENT_CONTACT['WORK_PHONE_INVALID']) == 'yes' || strtolower($E_STUDENT_CONTACT['WORK_PHONE_INVALID']) == 'y' )
					$E_STUDENT_CONTACT['WORK_PHONE_INVALID'] = 1;
				else if(strtolower($E_STUDENT_CONTACT['WORK_PHONE_INVALID']) == 'no' || strtolower($E_STUDENT_CONTACT['WORK_PHONE_INVALID']) == 'n')
					$E_STUDENT_CONTACT['WORK_PHONE_INVALID'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= EMERGENCY.' '.WORK_PHONE.' '.INVALID.' <b>'.$E_STUDENT_CONTACT['WORK_PHONE_INVALID'].'</b>';
				}
			}
			
			if($E_STUDENT_CONTACT['OPT_OUT'] != '') {
				if(strtolower($E_STUDENT_CONTACT['OPT_OUT']) == 'yes' || strtolower($E_STUDENT_CONTACT['OPT_OUT']) == 'y' )
					$E_STUDENT_CONTACT['OPT_OUT'] = 1;
				else if(strtolower($E_STUDENT_CONTACT['OPT_OUT']) == 'no' || strtolower($E_STUDENT_CONTACT['OPT_OUT']) == 'n')
					$E_STUDENT_CONTACT['OPT_OUT'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= EMERGENCY.' '.OPTOUT.' <b>'.$E_STUDENT_CONTACT['OPT_OUT'].'</b>';
				}
			}
			
			if($E_STUDENT_CONTACT['OTHER_PHONE_INVALID'] != '') {
				if(strtolower($E_STUDENT_CONTACT['OTHER_PHONE_INVALID']) == 'yes' || strtolower($E_STUDENT_CONTACT['OTHER_PHONE_INVALID']) == 'y' )
					$E_STUDENT_CONTACT['OTHER_PHONE_INVALID'] = 1;
				else if(strtolower($E_STUDENT_CONTACT['OTHER_PHONE_INVALID']) == 'no' || strtolower($E_STUDENT_CONTACT['OTHER_PHONE_INVALID']) == 'n')
					$E_STUDENT_CONTACT['OTHER_PHONE_INVALID'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= EMERGENCY.' '.OTHER_PHONE.' '.INVALID.' <b>'.$E_STUDENT_CONTACT['OTHER_PHONE_INVALID'].'</b>';
				}
			}
			
			if($E_STUDENT_CONTACT['CELL_PHONE_INVALID'] != '') {
				if(strtolower($E_STUDENT_CONTACT['CELL_PHONE_INVALID']) == 'yes' || strtolower($E_STUDENT_CONTACT['CELL_PHONE_INVALID']) == 'y' )
					$E_STUDENT_CONTACT['CELL_PHONE_INVALID'] = 1;
				else if(strtolower($E_STUDENT_CONTACT['CELL_PHONE_INVALID']) == 'no' || strtolower($E_STUDENT_CONTACT['CELL_PHONE_INVALID']) == 'n')
					$E_STUDENT_CONTACT['CELL_PHONE_INVALID'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= EMERGENCY.' '.CELL_PHONE.' '.INVALID.' <b>'.$E_STUDENT_CONTACT['CELL_PHONE_INVALID'].'</b>';
				}
			}
			
			if($E_STUDENT_CONTACT['EMAIL_INVALID'] != '') {
				if(strtolower($E_STUDENT_CONTACT['EMAIL_INVALID']) == 'yes' || strtolower($E_STUDENT_CONTACT['EMAIL_INVALID']) == 'y' )
					$E_STUDENT_CONTACT['EMAIL_INVALID'] = 1;
				else if(strtolower($E_STUDENT_CONTACT['EMAIL_INVALID']) == 'no' || strtolower($E_STUDENT_CONTACT['EMAIL_INVALID']) == 'n')
					$E_STUDENT_CONTACT['EMAIL_INVALID'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= EMERGENCY.' '.EMAIL.' '.INVALID.' <b>'.$E_STUDENT_CONTACT['EMAIL_INVALID'].'</b>';
				}
			}
			
			if($E_STUDENT_CONTACT['EMAIL_OTHER_INVALID'] != '') {
				if(strtolower($E_STUDENT_CONTACT['EMAIL_OTHER_INVALID']) == 'yes' || strtolower($E_STUDENT_CONTACT['EMAIL_OTHER_INVALID']) == 'y' )
					$E_STUDENT_CONTACT['EMAIL_OTHER_INVALID'] = 1;
				else if(strtolower($E_STUDENT_CONTACT['EMAIL_OTHER_INVALID']) == 'no' || strtolower($E_STUDENT_CONTACT['EMAIL_OTHER_INVALID']) == 'n')
					$E_STUDENT_CONTACT['EMAIL_OTHER_INVALID'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= EMERGENCY.' '.OTHER_EMAIL.' '.INVALID.' <b>'.$E_STUDENT_CONTACT['EMAIL_OTHER_INVALID'].'</b>';
				}
			}

			$STUDENT_OTHER_EDU['SCHOOL_NAME']			 	=  trim($row[$OTHER_EDUCATION_SCHOOL_NAME_COL]);
			$STUDENT_OTHER_EDU['ADDRESS'] 					=  trim($row[$OTHER_EDUCATION_ADDRESS_COL]);
			$STUDENT_OTHER_EDU['ADDRESS_1'] 				=  trim($row[$OTHER_EDUCATION_ADDRESS_1_COL]);
			$STUDENT_OTHER_EDU['CITY'] 						=  trim($row[$OTHER_EDUCATION_CITY_COL]);
			$STUDENT_OTHER_EDU['PK_STATE'] 					=  trim($row[$OTHER_EDUCATION_STATE_COL]);
			$STUDENT_OTHER_EDU['ZIP'] 						=  trim($row[$OTHER_EDUCATION_ZIP_COL]);
			$STUDENT_OTHER_EDU['OTHER_SCHOOL_PHONE'] 		=  trim($row[$OTHER_SCHOOL_PHONE_COL]);
			$STUDENT_OTHER_EDU['OTHER_SCHOOL_FAX'] 			=  trim($row[$OTHER_SCHOOL_FAX_COL]);
			$STUDENT_OTHER_EDU['PK_EDUCATION_TYPE'] 		=  trim($row[$OTHER_EDUCATION_EDUCATION_TYPE_COL]);
			$STUDENT_OTHER_EDU['GRADUATED'] 				=  trim($row[$OTHER_EDUCATION_GRADUATED_COL]);
			$STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED'] 		=  trim($row[$OTHER_EDUCATION_TRANSCRIPT_REQUESTED_COL]);
			$STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED'] 		=  trim($row[$OTHER_EDUCATION_TRANSCRIPT_RECEIVED_COL]);
			$STUDENT_OTHER_EDU['GRADUATED_DATE'] 			=  trim($row[$OTHER_EDUCATION_GRADUATED_DATE_COL]);
			$STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED_DATE'] =  trim($row[$OTHER_EDUCATION_TRANSCRIPT_REQUESTED_DATE_COL]);
			$STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED_DATE'] 	=  trim($row[$OTHER_EDUCATION_TRANSCRIPT_RECEIVED_DATE_COL]);
			
			if($STUDENT_OTHER_EDU['OTHER_SCHOOL_PHONE'] != '') {
				$STUDENT_OTHER_EDU['OTHER_SCHOOL_PHONE'] = preg_replace( '/[^0-9]/', '',$STUDENT_OTHER_EDU['OTHER_SCHOOL_PHONE']);
				$OTHER_SCHOOL_PHONE = $STUDENT_OTHER_EDU['OTHER_SCHOOL_PHONE'];
				
				$OTHER_SCHOOL_PHONE = '('.$OTHER_SCHOOL_PHONE[0].$OTHER_SCHOOL_PHONE[1].$OTHER_SCHOOL_PHONE[2].') '.$OTHER_SCHOOL_PHONE[3].$OTHER_SCHOOL_PHONE[4].$OTHER_SCHOOL_PHONE[5].'-'.$OTHER_SCHOOL_PHONE[6].$OTHER_SCHOOL_PHONE[7].$OTHER_SCHOOL_PHONE[8].$OTHER_SCHOOL_PHONE[9];
					
				$STUDENT_OTHER_EDU['OTHER_SCHOOL_PHONE'] = $OTHER_SCHOOL_PHONE;
			}

			if($STUDENT_OTHER_EDU['OTHER_SCHOOL_FAX'] != '') {
				$STUDENT_OTHER_EDU['OTHER_SCHOOL_FAX'] = preg_replace( '/[^0-9]/', '',$STUDENT_OTHER_EDU['OTHER_SCHOOL_FAX']);
				$OTHER_SCHOOL_FAX = $STUDENT_OTHER_EDU['OTHER_SCHOOL_FAX'];
				
				$OTHER_SCHOOL_FAX = '('.$OTHER_SCHOOL_FAX[0].$OTHER_SCHOOL_FAX[1].$OTHER_SCHOOL_FAX[2].') '.$OTHER_SCHOOL_FAX[3].$OTHER_SCHOOL_FAX[4].$OTHER_SCHOOL_FAX[5].'-'.$OTHER_SCHOOL_FAX[6].$OTHER_SCHOOL_FAX[7].$OTHER_SCHOOL_FAX[8].$OTHER_SCHOOL_FAX[9];
					
				$STUDENT_OTHER_EDU['OTHER_SCHOOL_FAX'] = $OTHER_SCHOOL_FAX;
			}
			
			if($STUDENT_OTHER_EDU['GRADUATED_DATE'] != '') {
				$GRADUATED_DATE = str_replace("/","-",$STUDENT_OTHER_EDU['GRADUATED_DATE']);
				$GRADUATED_DATE = explode("-",$GRADUATED_DATE);
				if($GRADUATED_DATE[2] < 99)
					$year = 2000 + $GRADUATED_DATE[2];
				else
					$year = $GRADUATED_DATE[2];
				
				$STUDENT_OTHER_EDU['GRADUATED_DATE'] = $year.'-'.$GRADUATED_DATE[0].'-'.$GRADUATED_DATE[1];
			}
			
			if($STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED_DATE'] != '') {
				$TRANSCRIPT_REQUESTED_DATE = str_replace("/","-",$STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED_DATE']);
				$TRANSCRIPT_REQUESTED_DATE = explode("-",$TRANSCRIPT_REQUESTED_DATE);
				if($TRANSCRIPT_REQUESTED_DATE[2] < 99)
					$year = 2000 + $TRANSCRIPT_REQUESTED_DATE[2];
				else
					$year = $TRANSCRIPT_REQUESTED_DATE[2];
				
				$STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED_DATE'] = $year.'-'.$TRANSCRIPT_REQUESTED_DATE[0].'-'.$TRANSCRIPT_REQUESTED_DATE[1];
			}
			
			if($STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED_DATE'] != '') {
				$TRANSCRIPT_RECEIVED_DATE = str_replace("/","-",$STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED_DATE']);
				$TRANSCRIPT_RECEIVED_DATE = explode("-",$TRANSCRIPT_RECEIVED_DATE);
				if($TRANSCRIPT_RECEIVED_DATE[2] < 99)
					$year = 2000 + $TRANSCRIPT_RECEIVED_DATE[2];
				else
					$year = $TRANSCRIPT_RECEIVED_DATE[2];
				
				$STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED_DATE'] = $year.'-'.$TRANSCRIPT_RECEIVED_DATE[0].'-'.$TRANSCRIPT_RECEIVED_DATE[1];
			}
			
			if($STUDENT_OTHER_EDU['PK_STATE'] != '') {
				$res_l = $db->Execute("select PK_STATES from Z_STATES WHERE trim(STATE_CODE) = '$STUDENT_OTHER_EDU[PK_STATE]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .= OTHER_EDUCATION.' '.STATE.' <b>'.$STUDENT_OTHER_EDU['PK_STATE'].'</b>';
				} else {
					$STUDENT_OTHER_EDU['PK_STATE'] = $res_l->fields['PK_STATES'];
				}
			}
			
			if($STUDENT_OTHER_EDU['PK_EDUCATION_TYPE'] != '') {
				$res_l = $db->Execute("select PK_EDUCATION_TYPE from M_EDUCATION_TYPE WHERE trim(EDUCATION_TYPE) = '$STUDENT_OTHER_EDU[PK_EDUCATION_TYPE]' ");
					
				if($res_l->RecordCount() == 0) {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .=  OTHER_EDUCATION.' '.EDUCATION_TYPE.' <b>'.$STUDENT_OTHER_EDU['PK_EDUCATION_TYPE'].'</b>';
				} else {
					$STUDENT_OTHER_EDU['PK_EDUCATION_TYPE'] = $res_l->fields['PK_EDUCATION_TYPE'];
				}
			}
			
			if($STUDENT_OTHER_EDU['GRADUATED'] != '') {
				if(strtolower($STUDENT_OTHER_EDU['GRADUATED']) == 'yes' || strtolower($STUDENT_OTHER_EDU['GRADUATED']) == 'y' )
					$STUDENT_OTHER_EDU['GRADUATED'] = 1;
				else if(strtolower($STUDENT_OTHER_EDU['GRADUATED']) == 'no' || strtolower($STUDENT_OTHER_EDU['GRADUATED']) == 'n')
					$STUDENT_OTHER_EDU['GRADUATED'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .=  OTHER_EDUCATION.' '.GRADUATED.' <b>'.$STUDENT_OTHER_EDU['GRADUATED'].'</b>';
				}
			} else
				$STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED'] = 0;
				
			if($STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED'] != '') {
				if(strtolower($STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED']) == 'yes' || strtolower($STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED']) == 'y' )
					$STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED'] = 1;
				else if(strtolower($STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED']) == 'no' || strtolower($STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED']) == 'n')
					$STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .=  OTHER_EDUCATION.' '.TRANSCRIPT_REQUESTED.' <b>'.$STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED'].'</b>';
				}
			} else
				$STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED'] = 0;
				
			if($STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED'] != '') {
				if(strtolower($STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED']) == 'yes' || strtolower($STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED']) == 'y' )
					$STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED'] = 1;
				else if(strtolower($STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED']) == 'no' || strtolower($STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED']) == 'n')
					$STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED'] = 0;
				else {
					if($error_str != '')
						$error_str .= ', ';
					$error_str .=  OTHER_EDUCATION.' '.TRANSCRIPT_RECEIVED.' <b>'.$STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED'].'</b>';
				}
			} else
				$STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED'] = 0;
			
			$PK_CUSTOM_FIELDS_ARR 				= array();
			$PK_CUSTOM_FIELDS_EXCEL_COLUM_ARR 	= array();
			foreach($_POST['CUSTOM_FIELD'] as $PK_CUSTOM_FIELDS){
				if($_POST['CUSTOM_FIELD_'.$PK_CUSTOM_FIELDS] != '') {
					$PK_CUSTOM_FIELDS_ARR[] 			= $PK_CUSTOM_FIELDS;
					$PK_CUSTOM_FIELDS_EXCEL_COLUM_ARR[] = $_POST['CUSTOM_FIELD_'.$PK_CUSTOM_FIELDS];
				}
			}
//echo "<pre>-----";print_r($PK_CUSTOM_FIELDS_ARR);print_r($PK_CUSTOM_FIELDS_EXCEL_COLUM_ARR);exit;

			$STUDENT_MASTER['IMPORT_ERROR']  = '';
			$STUDENT_MASTER['IMPORT_STATUS'] = 1;
			if($error_str != '') {
				$error[] = 'Row #'.$i.' - Invalid '.$error_str;
				
				$STUDENT_MASTER['IMPORT_ERROR']  = $error_str;
				$STUDENT_MASTER['IMPORT_STATUS'] = 2;
			}
		
			$FULL_NAME = $STUDENT_MASTER['FIRST_NAME'].' '.$STUDENT_MASTER['LAST_NAME'];
			$res_dup = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CONCAT(TRIM(FIRST_NAME),' ',TRIM(LAST_NAME)) = '$FULL_NAME' AND SSN = '$STUDENT_MASTER[SSN]' AND ARCHIVED = 0 AND SSN != ''");

			if($res_dup->RecordCount() > 0) {
				$STUDENT_MASTER['IMPORT_STATUS'] = 3;
			} else {
				$MOBILE_PHONE11 = preg_replace( '/[^0-9]/', '', trim($row[$MOBILE_PHONE_COL]));
				$EMAIL11 		= preg_replace( '/[^0-9]/', '', trim($row[$EMAIL_COL]));
				
				if($MOBILE_PHONE11 != '' || $EMAIL11 != '') {
					
					if($MOBILE_PHONE11 != '' && $EMAIL11 != '')
						$dup_cond  = " AND ((REPLACE(REPLACE(REPLACE(REPLACE(CELL_PHONE, '(', ''), ')', ''), '-', ''),' ','') = '$MOBILE_PHONE11') OR TRIM(EMAIL) = '$EMAIL11' ) ";
					else if($MOBILE_PHONE11 != '')
						$dup_cond  = " AND (REPLACE(REPLACE(REPLACE(REPLACE(CELL_PHONE, '(', ''), ')', ''), '-', ''),' ','') = '$MOBILE_PHONE11') ";
					else if($EMAIL11 != '')
						$dup_cond  = " AND TRIM(EMAIL) = '$EMAIL11' ";
						
					$res_dup = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER FROM S_STUDENT_MASTER, S_STUDENT_CONTACT WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CONCAT(TRIM(FIRST_NAME),' ',TRIM(LAST_NAME)) = '$STUDENT_MASTER[LAST_NAME]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_CONTACT.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 $dup_cond AND ARCHIVED = 0");
					if($res_dup->RecordCount() > 0) {
						$STUDENT_MASTER['IMPORT_STATUS'] = 3;
					}
				}
			}
		
			//else {
				$ETHNICITY = '';
				foreach($PK_RACE as $PK_RACE_1){
					if($PK_RACE_1 == 1) {
						$ETHNICITY = 'Hispanic/Latino';
						break;
					}
				}
				if($ETHNICITY == ''){
					if(count($PK_RACE) > 1)
						$ETHNICITY = 'Two or more races';
					else {
						$res_l = $db->Execute("select RACE FROM Z_RACE WHERE PK_RACE = '$PK_RACE[0]'");
						$ETHNICITY = $res_l->fields['RACE'];
					}
				}
				$STUDENT_MASTER['IPEDS_ETHNICITY'] = $ETHNICITY;
				
				if($STUDENT_MASTER['FIRST_NAME'] != '' || $STUDENT_MASTER['LAST_NAME'] != '' || $STUDENT_MASTER['MIDDLE_NAME'] != ''){ 
					$STUDENT_MASTER['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
					$STUDENT_MASTER['CREATED_BY']  = $_SESSION['PK_USER'];
					$STUDENT_MASTER['CREATED_ON']  = date("Y-m-d H:i");
					db_perform('S_STUDENT_MASTER', $STUDENT_MASTER, 'insert');
					$PK_STUDENT_MASTER = $db->insert_ID();
					
					if($STUDENT_ACADEMICS['STUDENT_ID'] == '') {
						$res_acc = $db->Execute("SELECT AUTO_GENERATE_STUD_ID,STUD_CODE,STUD_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
						if($res_acc->fields['AUTO_GENERATE_STUD_ID'] == 1 ) {
							$STUDENT_ACADEMICS['STUDENT_ID'] = $res_acc->fields['STUD_CODE'].$res_acc->fields['STUD_NO'];
							$STUD_NO = $res_acc->fields['STUD_NO'] + 1;
							$db->Execute("UPDATE Z_ACCOUNT SET STUD_NO = '$STUD_NO' WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
						}
					}
					
					/* Ticket # 1595 
					$STUDENT_ACADEMICS['ENTRY_DATE'] 		= date("Y-m-d");
					$STUDENT_ACADEMICS['ENTRY_TIME'] 		= date("H:i:s",strtotime(date("Y-m-d H:i:s")));
					*/
					
					$STUDENT_ACADEMICS['PK_STUDENT_MASTER'] = $PK_STUDENT_MASTER;
					$STUDENT_ACADEMICS['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
					$STUDENT_ACADEMICS['CREATED_BY']  		= $_SESSION['PK_USER'];
					$STUDENT_ACADEMICS['CREATED_ON']  		= date("Y-m-d H:i");
					db_perform('S_STUDENT_ACADEMICS', $STUDENT_ACADEMICS, 'insert');
					
					/* Ticket # 1595 */
					$STUDENT_ENROLLMENT['ENTRY_DATE'] 		= date("Y-m-d");
					$STUDENT_ENROLLMENT['ENTRY_TIME'] 		= date("H:i:s",strtotime(date("Y-m-d H:i:s")));
					/* Ticket # 1595 */
					$STUDENT_ENROLLMENT['IS_ACTIVE_ENROLLMENT'] = 1;
					$STUDENT_ENROLLMENT['STATUS_DATE'] 		 	= date("Y-m-d");
					$STUDENT_ENROLLMENT['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
					$STUDENT_ENROLLMENT['PK_ACCOUNT']  		 	= $_SESSION['PK_ACCOUNT'];
					$STUDENT_ENROLLMENT['CREATED_BY']  		 	= $_SESSION['PK_USER'];
					$STUDENT_ENROLLMENT['CREATED_ON']  		 	= date("Y-m-d H:i");
					db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'insert');
					$EID = $db->insert_ID();
					
					foreach($PK_RACE as $PK_RACE_1){
						$STUDENT_RACE['PK_RACE']   			= $PK_RACE_1;
						$STUDENT_RACE['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
						$STUDENT_RACE['PK_ACCOUNT'] 		= $_SESSION['PK_ACCOUNT'];
						$STUDENT_RACE['CREATED_BY']  		= $_SESSION['PK_USER'];
						$STUDENT_RACE['CREATED_ON']  		= date("Y-m-d H:i");
						db_perform('S_STUDENT_RACE', $STUDENT_RACE, 'insert');
					}
				
				
					foreach($PK_CAMPUS as $PK_CAMPUS_1) {
						$STUDENT_CAMPUS['PK_CAMPUS']   				= $PK_CAMPUS_1;
						$STUDENT_CAMPUS['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
						$STUDENT_CAMPUS['PK_STUDENT_ENROLLMENT'] 	= $EID;
						$STUDENT_CAMPUS['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
						$STUDENT_CAMPUS['CREATED_BY']  				= $_SESSION['PK_USER'];
						$STUDENT_CAMPUS['CREATED_ON']  				= date("Y-m-d H:i");
						db_perform('S_STUDENT_CAMPUS', $STUDENT_CAMPUS, 'insert');
					}
					
					if(empty($PK_CAMPUS)) {
						$res_camp = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
						if($res_camp->RecordCount() == 1) {
							$STUDENT_CAMPUS['PK_CAMPUS']   				= $res_camp->fields['PK_CAMPUS'];
							$STUDENT_CAMPUS['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
							$STUDENT_CAMPUS['PK_STUDENT_ENROLLMENT'] 	= $EID;
							$STUDENT_CAMPUS['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
							$STUDENT_CAMPUS['CREATED_BY']  				= $_SESSION['PK_USER'];
							$STUDENT_CAMPUS['CREATED_ON']  				= date("Y-m-d H:i");
							db_perform('S_STUDENT_CAMPUS', $STUDENT_CAMPUS, 'insert');
							$PK_STUDENT_CAMPUS_ARR[] = $db->insert_ID();
						}
					}
					
					$flag = 0;
					foreach($STUDENT_CONTACT as $key => $val){
						if($val != '') {
							$flag = 1;
							break;
						}	
					}
					
					if($flag == 1) {
						$STUDENT_CONTACT['PK_STUDENT_CONTACT_TYPE_MASTER'] 	= 1;
						$STUDENT_CONTACT['PK_ACCOUNT']   					= $_SESSION['PK_ACCOUNT'];
						$STUDENT_CONTACT['PK_STUDENT_MASTER']   			= $PK_STUDENT_MASTER;
						$STUDENT_CONTACT['CREATED_BY']  					= $_SESSION['PK_USER'];
						$STUDENT_CONTACT['CREATED_ON']  					= date("Y-m-d H:i");
						db_perform('S_STUDENT_CONTACT', $STUDENT_CONTACT, 'insert');
					}
					
					$flag = 0;
					foreach($E_STUDENT_CONTACT as $key => $val){
						if($val != '') {
							$flag = 1;
							break;
						}	
					}
		
					if($flag == 1) {
						$E_STUDENT_CONTACT['PK_STUDENT_CONTACT_TYPE_MASTER'] 	= 2;
						$E_STUDENT_CONTACT['PK_ACCOUNT']   						= $_SESSION['PK_ACCOUNT'];
						$E_STUDENT_CONTACT['PK_STUDENT_MASTER'] 				= $PK_STUDENT_MASTER;
						$E_STUDENT_CONTACT['CREATED_BY']  						= $_SESSION['PK_USER'];
						$E_STUDENT_CONTACT['CREATED_ON']  						= date("Y-m-d H:i");
						db_perform('S_STUDENT_CONTACT', $E_STUDENT_CONTACT, 'insert');
					}
					
					$flag = 0;
					foreach($STUDENT_OTHER_EDU as $key => $val){
						if($val != '') {
							$flag = 1;
							break;
						}	
					}
					
					if($flag == 1) {
						$STUDENT_OTHER_EDU['PK_ACCOUNT']   		= $_SESSION['PK_ACCOUNT'];
						$STUDENT_OTHER_EDU['PK_STUDENT_MASTER'] = $PK_STUDENT_MASTER;
						$STUDENT_OTHER_EDU['CREATED_BY']  		= $_SESSION['PK_USER'];
						$STUDENT_OTHER_EDU['CREATED_ON']  		= date("Y-m-d H:i");
						db_perform('S_STUDENT_OTHER_EDU', $STUDENT_OTHER_EDU, 'insert');
					}
					
					$res_req = $db->Execute("select * from S_SCHOOL_REQUIREMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by REQUIREMENT ASC");
					while (!$res_req->EOF) {
						$STUDENT_REQUIREMENT['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
						$STUDENT_REQUIREMENT['PK_STUDENT_ENROLLMENT'] 	= $EID;
						$STUDENT_REQUIREMENT['TYPE'] 				  	= 1;
						$STUDENT_REQUIREMENT['ID'] 				  		= $res_req->fields['PK_SCHOOL_REQUIREMENT'];
						$STUDENT_REQUIREMENT['PK_REQUIREMENT_CATEGORY'] = $res_req->fields['PK_REQUIREMENT_CATEGORY']; //ticket #1059
						$STUDENT_REQUIREMENT['REQUIREMENT'] 			= $res_req->fields['REQUIREMENT'];
						$STUDENT_REQUIREMENT['MANDATORY'] 				= $res_req->fields['MANDATORY'];
						$STUDENT_REQUIREMENT['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
						$STUDENT_REQUIREMENT['CREATED_BY']  			= $_SESSION['PK_USER'];
						$STUDENT_REQUIREMENT['CREATED_ON']  			= date("Y-m-d H:i");
						db_perform('S_STUDENT_REQUIREMENT', $STUDENT_REQUIREMENT, 'insert');
					
						$res_req->MoveNext();
					}
					
					$klm = 0;
					if(!empty($PK_CUSTOM_FIELDS_ARR)) {
						foreach($PK_CUSTOM_FIELDS_ARR as $PK_CUSTOM_FIELDS_2){
							$COL = $PK_CUSTOM_FIELDS_EXCEL_COLUM_ARR[$klm];
							$VAL = trim($row[$COL]);
							if($VAL != '') {
								$CUSTOM_FIELDS 	 = array();
								$FIELD_VALUE_CHK = array();
								
								$res_cf = $db->Execute("select PK_DATA_TYPES, PK_USER_DEFINED_FIELDS,FIELD_NAME from S_CUSTOM_FIELDS WHERE S_CUSTOM_FIELDS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS_2' ");
								$PK_USER_DEFINED_FIELDS = $res_cf->fields['PK_USER_DEFINED_FIELDS'];
								
								if($res_cf->fields['PK_DATA_TYPES'] == 1){
									//text
									$CUSTOM_FIELDS['FIELD_VALUE'] = $VAL;
								} else if($res_cf->fields['PK_DATA_TYPES'] == 2){
									//Drop Down
									$res_val = $db->Execute("select PK_USER_DEFINED_FIELDS_DETAIL from S_USER_DEFINED_FIELDS_DETAIL WHERE PK_USER_DEFINED_FIELDS = '$PK_USER_DEFINED_FIELDS' AND TRIM(OPTION_NAME) = '$VAL' ");
									if($res_val->RecordCount() == 0) {
										if($error_str != '')
											$error_str .= ', ';
										$error_str .= $res_cf->fields['FIELD_NAME'].' <b>'.$VAL.'</b>';
									} else {
										$CUSTOM_FIELDS['FIELD_VALUE'] = $res_val->fields['PK_USER_DEFINED_FIELDS_DETAIL'];
									}
								} else if($res_cf->fields['PK_DATA_TYPES'] == 3){
									//Multiple Choice
									$VAL_ARR = explode(",",$VAL);
									
									foreach($VAL_ARR as $VAL_3) {
										$VAL_3 = trim($VAL_3);
										$res_val = $db->Execute("select PK_USER_DEFINED_FIELDS_DETAIL from S_USER_DEFINED_FIELDS_DETAIL WHERE PK_USER_DEFINED_FIELDS = '$PK_USER_DEFINED_FIELDS' AND TRIM(OPTION_NAME) = '$VAL_3' ");
										if($res_val->RecordCount() == 0) {
											if($error_str != '')
												$error_str .= ', ';
											$error_str .= $res_cf->fields['FIELD_NAME'].' <b>'.$VAL_3.'</b>';
										} else {
											$FIELD_VALUE_CHK[] = $res_val->fields['PK_USER_DEFINED_FIELDS_DETAIL'];
										}
									}
									if(!empty($FIELD_VALUE_CHK)){
										$CUSTOM_FIELDS['FIELD_VALUE'] = implode(",",$FIELD_VALUE_CHK);
									}
								} else if($res_cf->fields['PK_DATA_TYPES'] == 4){
									//Date
									$CUST_DATE = str_replace("/","-",$VAL);
									$CUST_DATE = explode("-",$CUST_DATE);
									if($CUST_DATE[2] < 99)
										$year = 2000 + $CUST_DATE[2];
									else
										$year = $CUST_DATE[2];
									
									$CUSTOM_FIELDS['FIELD_VALUE'] = $year.'-'.$CUST_DATE[0].'-'.$CUST_DATE[1];
								}
								
								if($CUSTOM_FIELDS['FIELD_VALUE'] != '') {
									$CUSTOM_FIELDS['PK_ACCOUNT'] 		= $_SESSION['PK_ACCOUNT'];
									$CUSTOM_FIELDS['PK_STUDENT_MASTER'] = $PK_STUDENT_MASTER;
									$CUSTOM_FIELDS['PK_CUSTOM_FIELDS'] 	= $PK_CUSTOM_FIELDS_2;
									$CUSTOM_FIELDS['FIELD_NAME'] 		= $res_cf->fields['FIELD_NAME'];
									$CUSTOM_FIELDS['CREATED_BY'] 		= $_SESSION['PK_USER'];
									$CUSTOM_FIELDS['CREATED_ON']  		= date("Y-m-d H:i");
									db_perform('S_STUDENT_CUSTOM_FIELDS', $CUSTOM_FIELDS, 'insert');
								}
							}
							
							$klm++;
						}
					}
					
					$STUD_PK_CAMPUS = '';
					$res_campus = $db->Execute("SELECT PK_CAMPUS FROM S_STUDENT_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$EID' ");
					while (!$res_campus->EOF) { 
						if($STUD_PK_CAMPUS != '')
							$STUD_PK_CAMPUS .= ', ';
							
						$STUD_PK_CAMPUS .= $res_campus->fields['PK_CAMPUS'];
						$res_campus->MoveNext();
					}
					
					$res_not = $db->Execute("select S_EVENT_TEMPLATE.PK_EVENT_TEMPLATE from S_EVENT_TEMPLATE, S_EVENT_TEMPLATE_CAMPUS WHERE S_EVENT_TEMPLATE.PK_ACCOUNT = '$PK_ACCOUNT' AND S_EVENT_TEMPLATE.ACTIVE = 1 AND PK_EVENT_TYPE = '1' AND S_EVENT_TEMPLATE.PK_EVENT_TEMPLATE = S_EVENT_TEMPLATE_CAMPUS.PK_EVENT_TEMPLATE AND PK_CAMPUS IN ($PK_CAMPUS)");
					if($res_not->RecordCount() > 0) {
						$noti_data['PK_EVENT_TEMPLATE'] 	= $res_not->fields['PK_EVENT_TEMPLATE'];
						$noti_data['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
						$noti_data['PK_STUDENT_ENROLLMENT'] = $EID;
						create_notification($noti_data);
					}
				}
				//exit;
				//echo "<pre>";print_r($STUDENT_MASTER);print_r($STUDENT_ACADEMICS);print_r($STUDENT_ENROLLMENT);print_r($STUDENT_RACE);exit;
			//}
		}

		//if(empty($error)){
			$_SESSION['GROUP_BY'] = '';
			header("location:student_data_view?t=100&stud_map=1&id=".$_GET['id']);
			exit;
		//}
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
	<title><?=LEAD.' '.MAPPING?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=LEAD.' '.MAPPING?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="FIRST_NAME" name="FIRST_NAME" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",LEAD.' '.FIRST_NAME))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="FIRST_NAME"><?=LEAD.' '.FIRST_NAME?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="LAST_NAME" name="LAST_NAME" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",LEAD.' '.LAST_NAME))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="LAST_NAME"><?=LEAD.' '.LAST_NAME?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="MIDDLE_NAME" name="MIDDLE_NAME" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",LEAD.' '.MIDDLE_NAME))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="MIDDLE_NAME"><?=LEAD.' '.MIDDLE_NAME?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_NAME" name="OTHER_NAME" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",LEAD.' '.OTHER_NAME))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_NAME"><?=LEAD.' '.OTHER_NAME?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="SSN" name="SSN" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",SSN))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="SSN"><?=SSN?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="SSN_VERIFIED" name="SSN_VERIFIED" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",SSN_VERIFIED))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="SSN_VERIFIED"><?=SSN_VERIFIED?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="DATE_OF_BIRTH" name="DATE_OF_BIRTH" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",DATE_OF_BIRTH))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="DATE_OF_BIRTH"><?=DATE_OF_BIRTH?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="GENDER" name="GENDER" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",GENDER))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="GENDER"><?=GENDER?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="DRIVERS_LICENSE" name="DRIVERS_LICENSE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",DRIVERS_LICENSE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="DRIVERS_LICENSE"><?=DRIVERS_LICENSE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_DRIVERS_LICENSE_STATE" name="PK_DRIVERS_LICENSE_STATE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",DRIVERS_LICENSE_STATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_DRIVERS_LICENSE_STATE"><?=DRIVERS_LICENSE_STATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_MARITAL_STATUS" name="PK_MARITAL_STATUS" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",MARITAL_STATUS))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_MARITAL_STATUS"><?=MARITAL_STATUS?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_COUNTRY_CITIZEN" name="PK_COUNTRY_CITIZEN" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",COUNTRY_CITIZEN))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_COUNTRY_CITIZEN"><?=COUNTRY_CITIZEN?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_CITIZENSHIP" name="PK_CITIZENSHIP" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",US_CITIZEN))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_CITIZENSHIP"><?=US_CITIZEN?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PLACE_OF_BIRTH" name="PLACE_OF_BIRTH" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",PLACE_OF_BIRTH))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PLACE_OF_BIRTH"><?=PLACE_OF_BIRTH?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_STATE_OF_RESIDENCY" name="PK_STATE_OF_RESIDENCY" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",STATE_OF_RESIDENCY))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_STATE_OF_RESIDENCY"><?=STATE_OF_RESIDENCY?></label>
													</div>
												</div>
											</div>
											
											<!--
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="STUDENT_ID" name="STUDENT_ID" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",STUDENT_ID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} */?>
														</select>
														<span class="bar"></span> 
														 <label for="STUDENT_ID"><?=STUDENT_ID?></label>
													</div>
												</div>
											</div>
											-->
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="ADM_USER_ID" name="ADM_USER_ID" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",ADM_USER_ID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="ADM_USER_ID"><?=ADM_USER_ID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_HIGHEST_LEVEL_OF_EDU" name="PK_HIGHEST_LEVEL_OF_EDU" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",HIGHEST_LEVEL_OF_ED))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_HIGHEST_LEVEL_OF_EDU"><?=HIGHEST_LEVEL_OF_ED?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PREVIOUS_COLLEGE" name="PREVIOUS_COLLEGE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",PREVIOUS_COLLEGE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PREVIOUS_COLLEGE"><?=PREVIOUS_COLLEGE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="BADGE_ID" name="BADGE_ID" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",BADGE_ID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="BADGE_ID"><?=BADGE_ID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="FERPA_BLOCK" name="FERPA_BLOCK" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",FERPA_BLOCK))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="FERPA_BLOCK"><?=FERPA_BLOCK?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="IPEDS_ETHNICITY" name="IPEDS_ETHNICITY" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",IPEDS_ETHNICITY))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="IPEDS_ETHNICITY"><?=IPEDS_ETHNICITY?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="RACE" name="RACE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",RACE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="RACE"><?=RACE?></label>
													</div>
												</div>
											</div>
											<!--
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="TRANSFER_IN" name="TRANSFER_IN" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",TRANSFER_IN))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} */?>
														</select>
														<span class="bar"></span> 
														 <label for="TRANSFER_IN"><?=TRANSFER_IN?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="TRANSFER_OUT" name="TRANSFER_OUT" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",TRANSFER_OUT))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															}*/ ?>
														</select>
														<span class="bar"></span> 
														 <label for="TRANSFER_OUT"><?=TRANSFER_OUT?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="FIRST_TERM" name="FIRST_TERM" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",FIRST_TERM))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															}*/ ?>
														</select>
														<span class="bar"></span> 
														 <label for="FIRST_TERM"><?=FIRST_TERM?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="REENTRY" name="REENTRY" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",REENTRY))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															}*/ ?>
														</select>
														<span class="bar"></span> 
														 <label for="REENTRY"><?=REENTRY?></label>
													</div>
												</div>
											</div>
											-->
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_REPRESENTATIVE" name="PK_REPRESENTATIVE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",ADMISSION_REP))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_REPRESENTATIVE"><?=ADMISSION_REP?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_LEAD_SOURCE" name="PK_LEAD_SOURCE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",LEAD_SOURCE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_LEAD_SOURCE"><?=LEAD_SOURCE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="STATUS_DATE" name="STATUS_DATE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",STATUS_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="STATUS_DATE"><?=STATUS_DATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",PROGRAM))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_CAMPUS_PROGRAM"><?=PROGRAM?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",FIRST_TERM_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_TERM_MASTER"><?=FIRST_TERM_DATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_FUNDING" name="PK_FUNDING" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",FUNDING))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_FUNDING"><?=FUNDING?></label>
													</div>
												</div>
											</div>
											<!--
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="FT_PT_EFFECTIVE_DATE" name="FT_PT_EFFECTIVE_DATE" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",FT_PT_EFFECTIVE_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															}*/ ?>
														</select>
														<span class="bar"></span> 
														 <label for="FT_PT_EFFECTIVE_DATE"><?=FT_PT_EFFECTIVE_DATE?></label>
													</div>
												</div>
											</div>
											-->
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EXPECTED_GRAD_DATE" name="EXPECTED_GRAD_DATE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EXPECTED_GRAD_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EXPECTED_GRAD_DATE"><?=EXPECTED_GRAD_DATE?></label>
													</div>
												</div>
											</div>
											<!--
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="ORIGINAL_EXPECTED_GRAD_DATE" name="ORIGINAL_EXPECTED_GRAD_DATE" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",ORIGINAL_EXPECTED_GRAD_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} */ ?>
														</select>
														<span class="bar"></span> 
														 <label for="ORIGINAL_EXPECTED_GRAD_DATE"><?=ORIGINAL_EXPECTED_GRAD_DATE?></label>
													</div>
												</div>
											</div>
											-->
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_SESSION" name="PK_SESSION" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",SESSION))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_SESSION"><?=SESSION?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_ENROLLMENT_STATUS" name="PK_ENROLLMENT_STATUS" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",FULL_PART_TIME))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_ENROLLMENT_STATUS"><?=FULL_PART_TIME?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",STUDENT_GROUP))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_STUDENT_GROUP"><?=STUDENT_GROUP?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="CONTRACT_SIGNED_DATE" name="CONTRACT_SIGNED_DATE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",CONTRACT_SIGNED_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="CONTRACT_SIGNED_DATE"><?=CONTRACT_SIGNED_DATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="CONTRACT_END_DATE" name="CONTRACT_END_DATE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",CONTRACT_END_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="CONTRACT_END_DATE"><?=CONTRACT_END_DATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",STATUS))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_STUDENT_STATUS"><?=STATUS?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_LEAD_CONTACT_SOURCE" name="PK_LEAD_CONTACT_SOURCE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",CONTACT_SOURCE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_LEAD_CONTACT_SOURCE"><?=CONTACT_SOURCE?></label>
													</div>
												</div>
											</div>
											<!--
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_DISTANCE_LEARNING" name="PK_DISTANCE_LEARNING" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",DISTANCE_LEARNING))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															}*/ ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_DISTANCE_LEARNING"><?=DISTANCE_LEARNING?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_DROP_REASON" name="PK_DROP_REASON" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",DROP_REASON))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															}*/ ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_DROP_REASON"><?=DROP_REASON?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_PLACEMENT_STATUS" name="PK_PLACEMENT_STATUS" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",PLACEMENT_STATUS))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															}*/ ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_PLACEMENT_STATUS"><?=PLACEMENT_STATUS?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_SPECIAL_PROGRAM_INDICATOR" name="PK_SPECIAL_PROGRAM_INDICATOR" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",SPECIAL_PROGRAM_INDICATOR))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															}*/ ?>
														</select>
														<span class="bar"></span> 
														 <label for="PK_SPECIAL_PROGRAM_INDICATOR"><?=SPECIAL_PROGRAM_INDICATOR?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="MIDPOINT_DATE" name="MIDPOINT_DATE" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",MIDPOINT_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															}*/ ?>
														</select>
														<span class="bar"></span> 
														 <label for="MIDPOINT_DATE"><?=MIDPOINT_DATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EXTERN_START_DATE" name="EXTERN_START_DATE" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EXTERN_START_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															}*/ ?>
														</select>
														<span class="bar"></span> 
														 <label for="EXTERN_START_DATE"><?=EXTERN_START_DATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="GRADE_DATE" name="GRADE_DATE" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",GRADE_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															}*/ ?>
														</select>
														<span class="bar"></span> 
														 <label for="GRADE_DATE"><?=GRADE_DATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="LDA" name="PK_SPECIAL_PROGRAM_INDICATOR" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",LDA))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															}*/ ?>
														</select>
														<span class="bar"></span> 
														 <label for="LDA"><?=LDA?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="DROP_DATE" name="DROP_DATE" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",DROP_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} */?>
														</select>
														<span class="bar"></span> 
														 <label for="DROP_DATE"><?=DROP_DATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="STRF_PAID_DATE" name="STRF_PAID_DATE" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",STRF_PAID_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															}*/ ?>
														</select>
														<span class="bar"></span> 
														 <label for="STRF_PAID_DATE"><?=STRF_PAID_DATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="DETERMINATION_DATE" name="DETERMINATION_DATE" class="form-control">
															<option value=""></option>
															<? /*$res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",DETERMINATION_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															}*/ ?>
														</select>
														<span class="bar"></span> 
														 <label for="DETERMINATION_DATE"><?=DETERMINATION_DATE?></label>
													</div>
												</div>
											</div>
											-->
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="CAMPUS" name="CAMPUS" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",CAMPUS))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="CAMPUS"><?=CAMPUS?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="ADDRESS" name="ADDRESS" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",ADDRESS))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="ADDRESS"><?=ADDRESS?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="ADDRESS_1" name="ADDRESS_1" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",ADDRESS_1))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="ADDRESS_1"><?=ADDRESS_1?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="CITY" name="CITY" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",CITY))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="CITY"><?=CITY?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="STATE" name="STATE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",STATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="STATE"><?=STATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="ZIP" name="ZIP" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",ZIP))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="ZIP"><?=ZIP?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="COUNTRY" name="COUNTRY" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",COUNTRY))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="COUNTRY"><?=LEAD.' '.COUNTRY?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="HOME_PHONE" name="HOME_PHONE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",HOME_PHONE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="HOME_PHONE"><?=HOME_PHONE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="HOME_PHONE_INVALID" name="HOME_PHONE_INVALID" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",HOME_PHONE.' '.INVALID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="HOME_PHONE_INVALID"><?=HOME_PHONE.' '.INVALID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="WORK_PHONE" name="WORK_PHONE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",WORK_PHONE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="v"><?=WORK_PHONE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="WORK_PHONE_INVALID" name="WORK_PHONE_INVALID" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",WORK_PHONE.' '.INVALID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="WORK_PHONE_INVALID"><?=WORK_PHONE.' '.INVALID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="MOBILE_PHONE" name="MOBILE_PHONE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",CELL_PHONE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="MOBILE_PHONE"><?=CELL_PHONE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OPT_OUT" name="OPT_OUT" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",CELL_PHONE.' '.OPTOUT))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OPT_OUT"><?=CELL_PHONE.' '.OPTOUT?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="CELL_PHONE_INVALID" name="CELL_PHONE_INVALID" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",CELL_PHONE.' '.INVALID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="CELL_PHONE_INVALID"><?=CELL_PHONE.' '.INVALID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_PHONE" name="OTHER_PHONE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_PHONE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_PHONE"><?=OTHER_PHONE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_PHONE_INVALID" name="OTHER_PHONE_INVALID" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_PHONE.' '.INVALID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_PHONE_INVALID"><?=OTHER_PHONE.' '.INVALID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMAIL" name="EMAIL" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMAIL))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMAIL"><?=EMAIL?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMAIL_INVALID" name="EMAIL_INVALID" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMAIL.' '.INVALID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMAIL_INVALID"><?=EMAIL.' '.INVALID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="USE_EMAIL" name="USE_EMAIL" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",USE_EMAIL))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="USE_EMAIL"><?=USE_EMAIL?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMAIL_OTHER" name="EMAIL_OTHER" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMAIL_OTHER))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMAIL_OTHER"><?=EMAIL_OTHER?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMAIL_OTHER_INVALID" name="EMAIL_OTHER_INVALID" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMAIL_OTHER.' '.INVALID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMAIL_OTHER_INVALID"><?=EMAIL_OTHER.' '.INVALID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_CONTACT_NAME" name="EMERGENCY_CONTACT_NAME" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.CONTACT_NAME))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_CONTACT_NAME"><?=EMERGENCY.' '.CONTACT_NAME?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_RELATIONSHIP" name="EMERGENCY_RELATIONSHIP" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.STUDENT_RELATIONSHIP))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_RELATIONSHIP"><?=EMERGENCY.' '.STUDENT_RELATIONSHIP?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_ADDRESS" name="EMERGENCY_ADDRESS" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.ADDRESS))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_ADDRESS"><?=EMERGENCY.' '.ADDRESS?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_ADDRESS_1" name="EMERGENCY_ADDRESS_1" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.ADDRESS_1))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_ADDRESS_1"><?=EMERGENCY.' '.ADDRESS_1?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_CITY" name="EMERGENCY_CITY" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.CITY))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_CITY"><?=EMERGENCY.' '.CITY?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_STATE" name="EMERGENCY_STATE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.STATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_STATE"><?=EMERGENCY.' '.STATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_ZIP" name="EMERGENCY_ZIP" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.ZIP))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_ZIP"><?=EMERGENCY.' '.ZIP?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_COUNTRY" name="EMERGENCY_COUNTRY" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.COUNTRY))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_COUNTRY"><?=EMERGENCY.' '.COUNTRY?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_HOME_PHONE" name="EMERGENCY_HOME_PHONE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.HOME_PHONE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_HOME_PHONE"><?=EMERGENCY.' '.HOME_PHONE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_HOME_PHONE_INVALID" name="EMERGENCY_HOME_PHONE_INVALID" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.HOME_PHONE.' '.INVALID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_HOME_PHONE_INVALID"><?=EMERGENCY.' '.HOME_PHONE.' '.INVALID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_WORK_PHONE" name="EMERGENCY_WORK_PHONE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.WORK_PHONE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_WORK_PHONE"><?=EMERGENCY.' '.WORK_PHONE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_WORK_PHONE_INVALID" name="EMERGENCY_WORK_PHONE_INVALID" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.WORK_PHONE.' '.INVALID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_WORK_PHONE_INVALID"><?=EMERGENCY.' '.WORK_PHONE.' '.INVALID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_MOBILE_PHONE" name="EMERGENCY_MOBILE_PHONE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.CELL_PHONE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_MOBILE_PHONE"><?=EMERGENCY.' '.CELL_PHONE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_OPT_OUT" name="EMERGENCY_OPT_OUT" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.CELL_PHONE.' '.OPTOUT))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_OPT_OUT"><?=EMERGENCY.' '.CELL_PHONE.' '.OPTOUT?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_CELL_PHONE_INVALID" name="EMERGENCY_CELL_PHONE_INVALID" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.CELL_PHONE.' '.INVALID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_CELL_PHONE_INVALID"><?=EMERGENCY.' '.CELL_PHONE.' '.INVALID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_OTHER_PHONE" name="EMERGENCY_OTHER_PHONE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.OTHER_PHONE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_OTHER_PHONE"><?=EMERGENCY.' '.OTHER_PHONE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_OTHER_PHONE_INVALID" name="EMERGENCY_OTHER_PHONE_INVALID" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.OTHER_PHONE.' '.INVALID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_OTHER_PHONE_INVALID"><?=EMERGENCY.' '.OTHER_PHONE.' '.INVALID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_EMAIL" name="EMERGENCY_EMAIL" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.EMAIL))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_EMAIL"><?=EMERGENCY.' '.EMAIL?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_EMAIL_INVALID" name="EMERGENCY_EMAIL_INVALID" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.EMAIL.' '.INVALID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_EMAIL_INVALID"><?=EMERGENCY.' '.EMAIL.' '.INVALID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_USE_EMAIL" name="EMERGENCY_USE_EMAIL" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.USE_EMAIL))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_USE_EMAIL"><?=EMERGENCY.' '.USE_EMAIL?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_EMAIL_OTHER" name="EMERGENCY_EMAIL_OTHER" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.EMAIL_OTHER))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_EMAIL_OTHER"><?=EMERGENCY.' '.EMAIL_OTHER?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="EMERGENCY_EMAIL_OTHER_INVALID" name="EMERGENCY_EMAIL_OTHER_INVALID" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",EMERGENCY.' '.EMAIL_OTHER.' '.INVALID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="EMERGENCY_EMAIL_OTHER_INVALID"><?=EMERGENCY.' '.EMAIL_OTHER.' '.INVALID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_EDUCATION_SCHOOL_NAME" name="OTHER_EDUCATION_SCHOOL_NAME" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_EDUCATION.' '.SCHOOL_NAME))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_EDUCATION_SCHOOL_NAME"><?=OTHER_EDUCATION.' '.SCHOOL_NAME?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_EDUCATION_ADDRESS" name="OTHER_EDUCATION_ADDRESS" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_EDUCATION.' '.ADDRESS))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_EDUCATION_ADDRESS"><?=OTHER_EDUCATION.' '.ADDRESS?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_EDUCATION_ADDRESS_1" name="OTHER_EDUCATION_ADDRESS_1" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_EDUCATION.' '.ADDRESS_1))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_EDUCATION_ADDRESS_1"><?=OTHER_EDUCATION.' '.ADDRESS_1?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_EDUCATION_CITY" name="OTHER_EDUCATION_CITY" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_EDUCATION.' '.CITY))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_EDUCATION_CITY"><?=OTHER_EDUCATION.' '.CITY?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_EDUCATION_STATE" name="OTHER_EDUCATION_STATE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_EDUCATION.' '.STATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_EDUCATION_STATE"><?=OTHER_EDUCATION.' '.STATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_EDUCATION_ZIP" name="OTHER_EDUCATION_ZIP" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_EDUCATION.' '.ZIP))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_EDUCATION_ZIP"><?=OTHER_EDUCATION.' '.ZIP?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_SCHOOL_PHONE" name="OTHER_SCHOOL_PHONE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_EDUCATION.' '.SCHOOL_PHONE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_SCHOOL_PHONE"><?=OTHER_EDUCATION.' '.SCHOOL_PHONE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_SCHOOL_FAX" name="OTHER_SCHOOL_FAX" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_EDUCATION.' '.SCHOOL_FAX))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_SCHOOL_FAX"><?=OTHER_EDUCATION.' '.SCHOOL_FAX?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_EDUCATION_EDUCATION_TYPE" name="OTHER_EDUCATION_EDUCATION_TYPE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_EDUCATION.' '.EDUCATION_TYPE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_EDUCATION_EDUCATION_TYPE"><?=OTHER_EDUCATION.' '.EDUCATION_TYPE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_EDUCATION_GRADUATED" name="OTHER_EDUCATION_GRADUATED" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_EDUCATION.' '.GRADUATED))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_EDUCATION_GRADUATED"><?=OTHER_EDUCATION.' '.GRADUATED?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_EDUCATION_GRADUATED_DATE" name="OTHER_EDUCATION_GRADUATED_DATE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_EDUCATION.' '.GRADUATED_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_EDUCATION_GRADUATED_DATE"><?=OTHER_EDUCATION.' '.GRADUATED_DATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_EDUCATION_TRANSCRIPT_REQUESTED" name="OTHER_EDUCATION_TRANSCRIPT_REQUESTED" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_EDUCATION.' '.TRANSCRIPT_REQUESTED))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_EDUCATION_TRANSCRIPT_REQUESTED"><?=OTHER_EDUCATION.' '.TRANSCRIPT_REQUESTED?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_EDUCATION_TRANSCRIPT_REQUESTED_DATE" name="OTHER_EDUCATION_TRANSCRIPT_REQUESTED_DATE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_EDUCATION.' '.TRANSCRIPT_REQUESTED_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_EDUCATION_TRANSCRIPT_REQUESTED_DATE"><?=OTHER_EDUCATION.' '.TRANSCRIPT_REQUESTED_DATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_EDUCATION_TRANSCRIPT_RECEIVED" name="OTHER_EDUCATION_TRANSCRIPT_RECEIVED" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_EDUCATION.' '.TRANSCRIPT_RECEIVED))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_EDUCATION_TRANSCRIPT_RECEIVED"><?=OTHER_EDUCATION.' '.TRANSCRIPT_RECEIVED?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="OTHER_EDUCATION_TRANSCRIPT_RECEIVED_DATE" name="OTHER_EDUCATION_TRANSCRIPT_RECEIVED_DATE" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OTHER_EDUCATION.' '.TRANSCRIPT_RECEIVED_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OTHER_EDUCATION_TRANSCRIPT_RECEIVED_DATE"><?=OTHER_EDUCATION.' '.TRANSCRIPT_RECEIVED_DATE?></label>
													</div>
												</div>
											</div>
											
											<? $res_type = $db->Execute("select PK_CUSTOM_FIELDS,FIELD_NAME,PK_DATA_TYPES, PK_USER_DEFINED_FIELDS from S_CUSTOM_FIELDS WHERE S_CUSTOM_FIELDS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TAB = 'Info' AND SECTION = 1 ");
											while (!$res_type->EOF) { ?>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="CUSTOM_FIELD[]" value="<?=$res_type->fields['PK_CUSTOM_FIELDS']?>" />
														<select id="CUSTOM_FIELD_<?=$res_type->fields['PK_CUSTOM_FIELDS']?>" name="CUSTOM_FIELD_<?=$res_type->fields['PK_CUSTOM_FIELDS']?>" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",$res_type->fields['FIELD_NAME']))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="CUSTOM_FIELD_<?=$res_type->fields['PK_CUSTOM_FIELDS']?>"><?=$res_type->fields['FIELD_NAME']?></label>
													</div>
												</div>
											</div>
											<?	$res_type->MoveNext();
											} ?>
											
										</div>
										 <div class="col-md-6">
											<div class="col-lg-12" style="color:red" >
											<? if(!empty($error)){
												echo "<u>Below Data Not Imported due to below Reason</u><br />";
												foreach($error as $error1)
													echo $error1."<br />"; ?>
													
													<a href="student_data_view?t=100&stud_map=1&id=<?=$_GET['id']?>" class="btn btn-info d-none d-lg-block m-l-15"><i class="fas fa-newspaper"></i> <?=DATA_VIEW?></a>
													
											<? } else 
												if($flag == 1)
													echo "Uploaded Successfully"; ?>
											</div>
										</div>
									</div>
									
									
									<br />
									<div class="row">
                                        <div class="col-md-4">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" name="btn" class="btn waves-effect waves-light btn-info"><?=UPLOAD?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_student?t=1'" ><?=CANCEL?></button>
												
											</div>
										</div>
									</div>
                                </form>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>

</body>

</html>