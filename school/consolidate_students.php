<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/student.php");
require_once("check_access.php");

$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

if($ADMISSION_ACCESS != 3 && $REGISTRAR_ACCESS != 3){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
		
	$res = $db->Execute("SELECT * FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_GET[sid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$STUDENT_MASTER['FIRST_NAME'] 				= $res->fields['FIRST_NAME'];
	$STUDENT_MASTER['LAST_NAME']				= $res->fields['LAST_NAME'];
	$STUDENT_MASTER['MIDDLE_NAME']	 			= $res->fields['MIDDLE_NAME'];
	$STUDENT_MASTER['OTHER_NAME']	 			= $res->fields['OTHER_NAME'];
	$STUDENT_MASTER['SSN']	 					= $res->fields['SSN'];
	$STUDENT_MASTER['SSN_VERIFIED']				= $res->fields['SSN_VERIFIED'];
	$STUDENT_MASTER['IMAGE']					= $res->fields['IMAGE'];
	$STUDENT_MASTER['DATE_OF_BIRTH']	 		= $res->fields['DATE_OF_BIRTH'];
	$STUDENT_MASTER['GENDER']					= $res->fields['GENDER'];
	$STUDENT_MASTER['DRIVERS_LICENSE']	 		= $res->fields['DRIVERS_LICENSE'];
	$STUDENT_MASTER['PK_DRIVERS_LICENSE_STATE'] = $res->fields['PK_DRIVERS_LICENSE_STATE'];
	$STUDENT_MASTER['PK_MARITAL_STATUS']	 	= $res->fields['PK_MARITAL_STATUS'];
	$STUDENT_MASTER['PK_COUNTRY_CITIZEN']	 	= $res->fields['PK_COUNTRY_CITIZEN'];
	$STUDENT_MASTER['PK_CITIZENSHIP']	 		= $res->fields['PK_CITIZENSHIP'];
	$STUDENT_MASTER['PLACE_OF_BIRTH']	 		= $res->fields['PLACE_OF_BIRTH'];
	$STUDENT_MASTER['IPEDS_ETHNICITY']	 		= $res->fields['IPEDS_ETHNICITY'];
	$STUDENT_MASTER['PK_STATE_OF_RESIDENCY']	= $res->fields['PK_STATE_OF_RESIDENCY'];
	$STUDENT_MASTER['BADGE_ID']					= $res->fields['BADGE_ID'];

	$res = $db->Execute("SELECT * FROM S_STUDENT_ACADEMICS WHERE PK_STUDENT_MASTER = '$_GET[sid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$STUDENT_ACADEMICS['STUDENT_ID']				= $res->fields['STUDENT_ID'];
	$STUDENT_ACADEMICS['ADM_USER_ID']				= $res->fields['ADM_USER_ID'];
	$STUDENT_ACADEMICS['PK_HIGHEST_LEVEL_OF_EDU']	= $res->fields['PK_HIGHEST_LEVEL_OF_EDU'];
	$STUDENT_ACADEMICS['PREVIOUS_COLLEGE']			= $res->fields['PREVIOUS_COLLEGE'];
	$STUDENT_ACADEMICS['FERPA_BLOCK']				= $res->fields['FERPA_BLOCK'];

	//////////////////////////////////////////////////////////////
	$res = $db->Execute("SELECT PK_LEAD_SOURCE,PK_REPRESENTATIVE,PK_STUDENT_MASTER FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$PK_STUDENT_MASTER 		= $res->fields['PK_STUDENT_MASTER'];
	$PK_STUDENT_MASTER_DEL  = $res->fields['PK_STUDENT_MASTER'];

	$res = $db->Execute("SELECT  * FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($STUDENT_MASTER['FIRST_NAME'] == '')
		$STUDENT_MASTER['FIRST_NAME'] = $res->fields['FIRST_NAME'];
	
	if($STUDENT_MASTER['LAST_NAME'] == '')
		$STUDENT_MASTER['LAST_NAME'] = $res->fields['LAST_NAME'];
		
	if($STUDENT_MASTER['MIDDLE_NAME'] == '')
		$STUDENT_MASTER['MIDDLE_NAME'] = $res->fields['MIDDLE_NAME'];
		
	if($STUDENT_MASTER['OTHER_NAME'] == '')
		$STUDENT_MASTER['OTHER_NAME'] = $res->fields['OTHER_NAME'];
		
	if($STUDENT_MASTER['SSN'] == '')
		$STUDENT_MASTER['SSN'] = $res->fields['SSN'];
		
	if($STUDENT_MASTER['SSN_VERIFIED'] == 0)
		$STUDENT_MASTER['SSN_VERIFIED'] = $res->fields['SSN_VERIFIED'];
		
	if($STUDENT_MASTER['IMAGE'] == '')
		$STUDENT_MASTER['IMAGE'] = $res->fields['IMAGE'];
		
	if($STUDENT_MASTER['DATE_OF_BIRTH'] == '0000-00-00')
		$STUDENT_MASTER['DATE_OF_BIRTH'] = $res->fields['DATE_OF_BIRTH'];
		
	if($STUDENT_MASTER['GENDER'] == '' || $STUDENT_MASTER['GENDER'] == 0)
		$STUDENT_MASTER['GENDER'] = $res->fields['GENDER'];
		
	if($STUDENT_MASTER['DRIVERS_LICENSE'] == '')
		$STUDENT_MASTER['DRIVERS_LICENSE'] = $res->fields['DRIVERS_LICENSE'];
		
	if($STUDENT_MASTER['PK_DRIVERS_LICENSE_STATE'] == '' || $STUDENT_MASTER['PK_DRIVERS_LICENSE_STATE'] == 0)
		$STUDENT_MASTER['PK_DRIVERS_LICENSE_STATE'] = $res->fields['PK_DRIVERS_LICENSE_STATE'];
		
	if($STUDENT_MASTER['PK_MARITAL_STATUS'] == '' || $STUDENT_MASTER['PK_MARITAL_STATUS'] == 0)
		$STUDENT_MASTER['PK_MARITAL_STATUS'] = $res->fields['PK_MARITAL_STATUS'];
		
	if($STUDENT_MASTER['PK_COUNTRY_CITIZEN'] == '' || $STUDENT_MASTER['PK_COUNTRY_CITIZEN'] == 0)
		$STUDENT_MASTER['PK_COUNTRY_CITIZEN'] = $res->fields['PK_COUNTRY_CITIZEN'];
		
	if($STUDENT_MASTER['PK_CITIZENSHIP'] == '' || $STUDENT_MASTER['PK_CITIZENSHIP'] == 0)
		$STUDENT_MASTER['PK_CITIZENSHIP'] = $res->fields['PK_CITIZENSHIP'];
		
	if($STUDENT_MASTER['PLACE_OF_BIRTH'] == '')
		$STUDENT_MASTER['PLACE_OF_BIRTH'] = $res->fields['PLACE_OF_BIRTH'];
		
	if($STUDENT_MASTER['IPEDS_ETHNICITY'] == '')
		$STUDENT_MASTER['IPEDS_ETHNICITY'] = $res->fields['IPEDS_ETHNICITY'];
		
	if($STUDENT_MASTER['PK_STATE_OF_RESIDENCY'] == '' || $STUDENT_MASTER['PK_STATE_OF_RESIDENCY'] == 0)
		$STUDENT_MASTER['PK_STATE_OF_RESIDENCY'] = $res->fields['PK_STATE_OF_RESIDENCY'];
		
	if($STUDENT_MASTER['BADGE_ID'] == '')
		$STUDENT_MASTER['BADGE_ID'] = $res->fields['BADGE_ID'];	
		
	$res = $db->Execute("SELECT * FROM S_STUDENT_ACADEMICS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($STUDENT_ACADEMICS['STUDENT_ID'] == '')
		$STUDENT_ACADEMICS['STUDENT_ID'] = $res->fields['STUDENT_ID'];
		
	if($STUDENT_ACADEMICS['STUDENT_ID'] != '') {
		$res_l = $db->Execute("select PK_STUDENT_MASTER from S_STUDENT_ACADEMICS where PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND STUDENT_ID = '$STUDENT_ACADEMICS[STUDENT_ID]' AND PK_STUDENT_MASTER NOT IN ($_GET[sid],$PK_STUDENT_MASTER_DEL ) ");
		if($res_l->RecordCount() > 0) {
			$STUDENT_ACADEMICS['STUDENT_ID'] = '';
		}
	}
		
	if($STUDENT_ACADEMICS['ADM_USER_ID'] == '')
		$STUDENT_ACADEMICS['ADM_USER_ID'] = $res->fields['ADM_USER_ID'];
		
	if($STUDENT_ACADEMICS['PK_HIGHEST_LEVEL_OF_EDU'] == '' || $STUDENT_ACADEMICS['PK_HIGHEST_LEVEL_OF_EDU'] == 0)
		$STUDENT_ACADEMICS['PK_HIGHEST_LEVEL_OF_EDU'] = $res->fields['PK_HIGHEST_LEVEL_OF_EDU'];
		
	if($STUDENT_ACADEMICS['PREVIOUS_COLLEGE'] == '' || $STUDENT_ACADEMICS['PREVIOUS_COLLEGE'] == 0)
		$STUDENT_ACADEMICS['PREVIOUS_COLLEGE'] = $res->fields['PREVIOUS_COLLEGE'];
		
	if($STUDENT_ACADEMICS['FERPA_BLOCK'] == '' || $STUDENT_ACADEMICS['FERPA_BLOCK'] == 0)
		$STUDENT_ACADEMICS['FERPA_BLOCK'] = $res->fields['FERPA_BLOCK'];

	$STUDENT_MASTER['EDITED_BY']   	= $_SESSION['PK_USER'];
	$STUDENT_MASTER['EDITED_ON']   	= date("Y-m-d H:i");
	db_perform('S_STUDENT_MASTER', $STUDENT_MASTER, 'update'," PK_STUDENT_MASTER = '$_GET[sid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

	$STUDENT_ACADEMICS['EDITED_BY']   = $_SESSION['PK_USER'];
	$STUDENT_ACADEMICS['EDITED_ON']   = date("Y-m-d H:i");
	db_perform('S_STUDENT_ACADEMICS', $STUDENT_ACADEMICS, 'update'," PK_STUDENT_MASTER = '$_GET[sid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
			
	$res = $db->Execute("select PK_RACE FROM S_STUDENT_RACE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_RACE NOT IN (select PK_RACE FROM S_STUDENT_RACE WHERE PK_STUDENT_MASTER = '$_GET[sid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]') ");
	while (!$res->EOF) {
		$STUDENT_RACE['PK_RACE']   			= $res->fields['PK_RACE'];
		$STUDENT_RACE['PK_STUDENT_MASTER'] 	= $_GET['sid'];
		$STUDENT_RACE['PK_ACCOUNT'] 		= $_SESSION['PK_ACCOUNT'];
		$STUDENT_RACE['CREATED_BY']  		= $_SESSION['PK_USER'];
		$STUDENT_RACE['CREATED_ON']  		= date("Y-m-d H:i");
		db_perform('S_STUDENT_RACE', $STUDENT_RACE, 'insert');
		
		$res->MoveNext();
	}
	
	$res_type = $db->Execute("select PK_CUSTOM_FIELDS from S_CUSTOM_FIELDS WHERE S_CUSTOM_FIELDS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TAB = 'Info' AND SECTION = 1 AND S_CUSTOM_FIELDS.ACTIVE = 1 ");
	while (!$res_type->EOF) {
		$PK_CUSTOM_FIELDS = $res_type->fields['PK_CUSTOM_FIELDS'];
		$CUSTOM_FIELDS	  = array();

		$res_1 = $db->Execute("select PK_STUDENT_CUSTOM_FIELDS,FIELD_VALUE from S_STUDENT_CUSTOM_FIELDS WHERE PK_STUDENT_MASTER = '$_GET[sid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' ");
		if($res_1->RecordCount() == 0){
			$res_2 = $db->Execute("select * from S_STUDENT_CUSTOM_FIELDS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' ");
			if($res_2->RecordCount() > 0){
				$CUSTOM_FIELDS['PK_ACCOUNT'] 		= $_SESSION['PK_ACCOUNT'];
				$CUSTOM_FIELDS['PK_STUDENT_MASTER'] = $_GET['sid'];
				$CUSTOM_FIELDS['PK_CUSTOM_FIELDS'] 	= $PK_CUSTOM_FIELDS;
				$CUSTOM_FIELDS['FIELD_VALUE']		= $res_2->fields['FIELD_VALUE'];
				$CUSTOM_FIELDS['FIELD_NAME'] 		= $res_2->fields['FIELD_NAME'];
				$CUSTOM_FIELDS['CREATED_BY'] 		= $res_2->fields['CREATED_BY'];
				$CUSTOM_FIELDS['CREATED_ON']  		= $res_2->fields['CREATED_ON'];
				db_perform('S_STUDENT_CUSTOM_FIELDS', $CUSTOM_FIELDS, 'insert');
			}
		} else if ($res_1->fields['FIELD_VALUE'] == ''){
			$PK_STUDENT_CUSTOM_FIELDS = $res_1->fields['PK_STUDENT_CUSTOM_FIELDS'];
			
			$res_2 = $db->Execute("select FIELD_VALUE from S_STUDENT_CUSTOM_FIELDS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' ");
			$CUSTOM_FIELDS['FIELD_VALUE'] = $res_2->fields['FIELD_VALUE'];
			db_perform('S_STUDENT_CUSTOM_FIELDS', $CUSTOM_FIELDS, 'update'," PK_STUDENT_CUSTOM_FIELDS = '$PK_STUDENT_CUSTOM_FIELDS' ");
		}
		
		$res_type->MoveNext();
	}
	
	$res = $db->Execute("select * FROM S_STUDENT_CONTACT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER NOT IN (select PK_STUDENT_CONTACT_TYPE_MASTER FROM S_STUDENT_CONTACT WHERE PK_STUDENT_MASTER = '$_GET[sid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]') ");
	while (!$res->EOF) {
		$STUDENT_CONTACT['PK_STUDENT_CONTACT_TYPE_MASTER']  = $res->fields['PK_STUDENT_CONTACT_TYPE_MASTER'];
		$STUDENT_CONTACT['PK_STUDENT_RELATIONSHIP_MASTER']  = $res->fields['PK_STUDENT_RELATIONSHIP_MASTER'];
		$STUDENT_CONTACT['COMPANY_NAME']   					= $res->fields['COMPANY_NAME'];
		$STUDENT_CONTACT['CONTACT_NAME']   					= $res->fields['CONTACT_NAME'];
		$STUDENT_CONTACT['CONTACT_TITLE']   				= $res->fields['CONTACT_TITLE'];
		$STUDENT_CONTACT['ADDRESS']   						= $res->fields['ADDRESS'];
		$STUDENT_CONTACT['ADDRESS_1']   					= $res->fields['ADDRESS_1'];
		$STUDENT_CONTACT['CITY']   							= $res->fields['CITY'];
		$STUDENT_CONTACT['PK_STATES']   					= $res->fields['PK_STATES'];
		$STUDENT_CONTACT['ZIP']   							= $res->fields['ZIP'];
		$STUDENT_CONTACT['PK_COUNTRY']   					= $res->fields['PK_COUNTRY'];
		$STUDENT_CONTACT['ADDRESS_INVALID']   				= $res->fields['ADDRESS_INVALID'];
		$STUDENT_CONTACT['HOME_PHONE']   					= $res->fields['HOME_PHONE'];
		$STUDENT_CONTACT['HOME_PHONE_INVALID']   			= $res->fields['HOME_PHONE_INVALID'];
		$STUDENT_CONTACT['WORK_PHONE']   					= $res->fields['WORK_PHONE'];
		$STUDENT_CONTACT['WORK_PHONE_INVALID']   			= $res->fields['WORK_PHONE_INVALID'];
		$STUDENT_CONTACT['CELL_PHONE']   					= $res->fields['CELL_PHONE'];
		$STUDENT_CONTACT['CELL_PHONE_INVALID']   			= $res->fields['CELL_PHONE_INVALID'];
		$STUDENT_CONTACT['OTHER_PHONE']   					= $res->fields['OTHER_PHONE'];
		$STUDENT_CONTACT['OTHER_PHONE_INVALID']   			= $res->fields['OTHER_PHONE_INVALID'];
		$STUDENT_CONTACT['FAX']   							= $res->fields['FAX'];
		$STUDENT_CONTACT['EMAIL']   						= $res->fields['EMAIL'];
		$STUDENT_CONTACT['EMAIL_INVALID']   				= $res->fields['EMAIL_INVALID'];
		$STUDENT_CONTACT['EMAIL_OTHER']   					= $res->fields['EMAIL_OTHER'];
		$STUDENT_CONTACT['EMAIL_OTHER_INVALID']   			= $res->fields['EMAIL_OTHER_INVALID'];
		$STUDENT_CONTACT['OPT_OUT']   						= $res->fields['OPT_OUT'];
		$STUDENT_CONTACT['USE_EMAIL']   					= $res->fields['USE_EMAIL'];
		$STUDENT_CONTACT['WEBSITE']   						= $res->fields['WEBSITE'];
		
		$STUDENT_CONTACT['PK_STUDENT_MASTER'] 				= $_GET['sid'];
		$STUDENT_CONTACT['PK_ACCOUNT'] 						= $_SESSION['PK_ACCOUNT'];
		$STUDENT_CONTACT['CREATED_BY']  					= $_SESSION['PK_USER'];
		$STUDENT_CONTACT['CREATED_ON']  					= date("Y-m-d H:i");
		db_perform('S_STUDENT_CONTACT', $STUDENT_CONTACT, 'insert');
		$res->MoveNext();
	}
	
	$res = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[sid]' AND IS_ACTIVE_ENROLLMENT = 1"); 
	if($res->RecordCount() == 0){
		$db->Execute("UPDATE S_STUDENT_ENROLLMENT SET IS_ACTIVE_ENROLLMENT = 1  WHERE PK_STUDENT_MASTER = '$_GET[sid]' "); 
	}
	
	$res = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$PK_STUDENT_MASTER = $res->fields['PK_STUDENT_MASTER'];
	
	$db->Execute("UPDATE S_COURSE_OFFERING_WAITING_LIST SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_EMAIL_LOG SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_MISC_BATCH_DETAIL SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_PAYMENT_BATCH_DETAIL SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_ACT_TEST SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_ATB_TEST SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_ATTENDANCE SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_AWARD SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_CAMPUS SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_COURSE SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_COURSE_ETHINK SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_CREDIT_TRANSFER SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_DISBURSEMENT SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_DOCUMENTS SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_FEE_BUDGET SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_FINAL_GRADE SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_FINANCIAL SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_FINANCIAL_ACADEMY SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_GRADE SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_JOB SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_LEDGER SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_LOA SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_NOTES SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_PLACEMENT_EVENTS SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_PLACEMENT_NOTES SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_PROBATION SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_PROGRAM_GRADE SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_PROGRAM_GRADE_BOOK_INPUT SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_QUESTIONNAIRE SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_REQUIREMENT SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_SAT_TEST SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_SCHEDULE SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_STATUS_LOG SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_TASK SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_TEST SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_TRACK_CHANGES SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_STUDENT_WAIVER SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_TEXT_LOG SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$db->Execute("UPDATE S_TUITION_BATCH_DETAIL SET PK_STUDENT_MASTER = '$_GET[sid]'  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	
	$res = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	$PK_STUDENT_MASTER = $res->fields['PK_STUDENT_MASTER'];
	$db->Execute("DELETE FROM S_STUDENT_ACADEMICS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");  
	$db->Execute("DELETE FROM S_STUDENT_CONTACT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");  
	$db->Execute("DELETE FROM S_STUDENT_CUSTOM_FIELDS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); 
	$db->Execute("DELETE FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); 
	 
	$db->Execute("UPDATE S_STUDENT_ENROLLMENT SET PK_STUDENT_MASTER = '$_GET[sid]', IS_ACTIVE_ENROLLMENT = 0  WHERE PK_STUDENT_ENROLLMENT = '$_POST[CONSOLIDATE_EID]' "); 
	?>
	<script type="text/javascript">window.opener.refresh_win(this)</script>
<? } ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=CONSOLIDATE_STUDENT?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
						<h4 class="text-themecolor">
							<?=CONSOLIDATE_STUDENT ?>
						</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels" method="post" name="form1" id="form1" >
									<div class="row">
										<div class="col-md-12 align-self-center">
											<h4 class="text-themecolor">
												<?=LEAD_TO_KEEP ?>
											</h4>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12 align-self-center">
											<? $res = $db->Execute("SELECT CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME,SSN FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_GET[sid]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
											$NAME = $res->fields['NAME'];
											$SSN  = $res->fields['SSN'];
											if($SSN != '') {
												$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$SSN);
												$SSN_ORG = $SSN;
												$SSN_ARR = explode("-",$SSN);
												$SSN 	 = 'xxx-xx-'.$SSN_ARR[2];
											}
											$res = $db->Execute("SELECT STATUS_DATE,STUDENT_STATUS,CODE FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE PK_STUDENT_MASTER = '$_GET[sid]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' "); 
											$STATUS_DATE 	= $res->fields['STATUS_DATE'];
											$STUDENT_STATUS	= $res->fields['STUDENT_STATUS'];
											$CAMPUS_PROGRAM = $res->fields['CODE'];

											if($STATUS_DATE != '0000-00-00')
												$STATUS_DATE = date("m/d/Y",strtotime($STATUS_DATE));
											else
												$STATUS_DATE = '';
											?>
											<table class="table table-hover">
												<thead>
													<tr>
														<th><?=LEAD_STUDENT?></th>
														<th><?=SSN?></th>
														<th><?=PROGRAM?></th>
														<th><?=STATUS?></th>
														<th><?=LEAD_DATE?></th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td><?=$NAME?></td>
														<td><?=$SSN?></td>
														<td><?=$CAMPUS_PROGRAM?></td>
														<td><?=$STUDENT_STATUS?></td>
														<td><?=$STATUS_DATE?></td>
													</tr>
												</tbody>
											</table>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12 align-self-center">
											<h4 class="text-themecolor">
												<?=SEARCH_LEAD_TO_CONDOLIDATE ?>
											</h4>
										</div>
									</div>
									
									<hr style="border:1px solid #000" />
									
									<div class="row" style="padding-bottom:10px;" >
										<div class="col-md-2 ">
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM" class="form-control" >
												<option value=""><?=PROGRAM?></option>
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									
										<div class="col-md-2 ">
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS" class="form-control">
												<option value=""><?=STATUS?></option>
												<? $statu_cond = "";
												if($REGISTRAR_ACCESS != 3 && $FINANCE_ACCESS != 3 && $ACCOUNTING_ACCESS != 3 && $PLACEMENT_ACCESS != 3)
													$statu_cond = " AND ADMISSIONS = 1 ";
												$res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 $statu_cond order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<input id="STU_NAME" name="STU_NAME" value="" type="text" class="form-control" placeholder="<?=STUDENT?>" >
										</div>
										
										<div class="col-md-2 align-self-center ">
											<button type="button" class="btn waves-effect waves-light btn-dark" onclick="search()" ><?=SEARCH?></button>
										</div>
									</div>
									<br />
									<div id="student_div" style="max-height:300px;overflow: auto;" >
                                        
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
	
	<div class="modal" id="consolidateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
		<div class="modal-dialog" role="document" style="max-width: 800px;" >
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="exampleModalLabel1"><?=CONSOLIDATE_STUDENT.'<br /><span style="color:red" >'.CONSOLIDATE_STUDENT_WARNING.'</span>' ?></h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12 align-self-center">
							<h4 class="text-themecolor" style="font-weight:bold">
								<?=LEAD_TO_KEEP ?>
							</h4>
						</div>
					</div>
					<div class="form-group" >
						<table class="table table-hover">
							<thead>
								<tr>
									<th><?=LEAD_STUDENT?></th>
									<th><?=SSN?></th>
									<th><?=PROGRAM?></th>
									<th><?=STATUS?></th>
									<th><?=LEAD_DATE?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><?=$NAME?></td>
									<td><?=$SSN?></td>
									<td><?=$CAMPUS_PROGRAM?></td>
									<td><?=$STUDENT_STATUS?></td>
									<td><?=$STATUS_DATE?></td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="row">
						<div class="col-md-12 align-self-center">
							<h4 class="text-themecolor" style="font-weight:bold">
								<?=LEAD_TO_CONDOLIDATE_AND_DELETE ?>
							</h4>
						</div>
					</div>
					<table class="table table-hover" >
						<thead>
							<tr>
								<th><?=LEAD_STUDENT?></th>
								<th><?=SSN?></th>
								<th><?=PROGRAM?></th>
								<th><?=STATUS?></th>
								<th><?=LEAD_DATE?></th>
							</tr>
						</thead>
						<tbody id="LEAD_TO_CONDOLIDATE_AND_DELETE_DIV" >
						</tbody>
					</table>
						
					<form class="floating-labels m-t-40" method="post" name="form2" id="form2" action="" >
						<input type="hidden" id="CONSOLIDATE_EID" name="CONSOLIDATE_EID" value="0" />
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" onclick="conf_consolidate_stud(1)" class="btn waves-effect waves-light btn-info"><?=PROCEED?></button>
					<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_consolidate_stud(0)" ><?=CANCEL?></button>
				</div>
			</div>
		</div>
	</div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		function search(){
			jQuery(document).ready(function($) {
				var data  = 'STU_NAME='+$('#STU_NAME').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&type=consolidate';
				var value = $.ajax({
					url: "ajax_search_student",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('student_div').innerHTML = data
					}		
				}).responseText;
			});
		}
		function consolidate_stud(eid){
			jQuery(document).ready(function($) { 
				var str = '<tr>'+document.getElementById("search_stu_det_"+eid).innerHTML+'</tr>'
				str = str.replace('type="button"','type="button" style="display:none" ');
				document.getElementById("LEAD_TO_CONDOLIDATE_AND_DELETE_DIV").innerHTML = str
				$("#consolidateModal").modal()
				$("#CONSOLIDATE_EID").val(eid)
			}).responseText;
		}
		function conf_consolidate_stud(val){
			jQuery(document).ready(function($) { 
				if(val == 1) {
					var id = $("#CONSOLIDATE_EID").val();
					document.form2.submit();
				}
				$("#consolidateModal").modal("hide");
			}).responseText;
		}
	</script>

</body>

</html>