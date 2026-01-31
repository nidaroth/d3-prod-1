<? header("Access-Control-Allow-Origin: *");
require_once("../../global/config.php"); 
require_once("../../school/function_attendance.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"FIRST_NAME":"From","LAST_NAME":"API","MIDDLE_NAME":"M","EMAIL":"aaa@www.in","HOME_PHONE":"123-456-789","CELL_PHONE":"444-555-6666" ,"LEAD_SOURCE_ID":"1","ADM_USER_ID":"ADM user","OLD_DSIS_STU_NO":"","OLD_DSIS_LEAD_ID":"","ADDRESS":{"STREET":"2/F1 A.S.S.S.S Road","CITY":"VNR","STATE_CODE":"CA","ZIP":"62600","COUNTRY_CODE":"US"}}';

$API_KEY = '';
foreach (getallheaders() as $name => $value) {
    //echo "$name: $value<br />";
	if(strtolower(trim($name)) == 'apikey')
		$API_KEY = trim($value);
}

$DATA = urldecode($DATA);
$DATA = json_decode($DATA);

$flag = 1;
if($API_KEY == ''){
	$data['SUCCESS'] = 0;
	$data['MESSAGE'] = 'API Key Missing';
	
	$flag = 0;
} else {
	$res = $db->Execute("SELECT PK_ACCOUNT,ACTIVE FROM Z_ACCOUNT where API_KEY = '$API_KEY'");
	if($res->RecordCount() == 0){
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = 'Invalid API Key';
		
		$flag = 0;
	} else if($res->fields['ACTIVE'] == 0){
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = 'Your Account Is Blocked.';
		
		$flag = 0;
	}
	
	$PK_ACCOUNT = $res->fields['PK_ACCOUNT'];
}

if($flag == 1){
	$data['SUCCESS'] = 1;
	$data['MESSAGE'] = '';

	$FIRST_NAME 				= trim($DATA->FIRST_NAME);
	$LAST_NAME 					= trim($DATA->LAST_NAME);
	$MIDDLE_NAME 				= trim($DATA->MIDDLE_NAME);
	$SSN 						= trim($DATA->SSN);
	$DATE_OF_BIRTH 				= trim($DATA->DATE_OF_BIRTH);
	$DRIVERS_LICENSE 			= trim($DATA->DRIVERS_LICENSE);
	$PK_DRIVERS_LICENSE_STATE 	= trim($DATA->DRIVERS_LICENSE_STATE);
	$PK_MARITAL_STATUS 			= trim($DATA->MARITAL_STATUS);
	$GENDER 					= trim($DATA->GENDER);
	$PK_COUNTRY_CITIZEN			= trim($DATA->COUNTRY_CITIZEN);
	$PLACE_OF_BIRTH 			= trim($DATA->PLACE_OF_BIRTH);
	$PK_STATE_OF_RESIDENCY 		= trim($DATA->STATE_OF_RESIDENCY);
	$IPEDS_ETHNICITY 			= trim($DATA->IPEDS_ETHNICITY);
	$ADM_USER_ID 				= trim($DATA->ADM_USER_ID);
	$PK_HIGHEST_LEVEL_OF_EDU 	= trim($DATA->HIGHEST_LEVEL_OF_EDU);
	$PREVIOUS_COLLEGE 			= trim($DATA->PREVIOUS_COLLEGE);
	$CAMPUS 					= trim($DATA->CAMPUS);
	
	if($FIRST_NAME == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Missing First Name Value';
	}
	
	if($LAST_NAME == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Missing Last Name Value';
	}
	
	if($PK_DRIVERS_LICENSE_STATE != '') {
		$res_st = $db->Execute("select PK_STATES from Z_STATES WHERE STATE_CODE = '$PK_DRIVERS_LICENSE_STATE' OR STATE_NAME = '$PK_DRIVERS_LICENSE_STATE'");
		$PK_DRIVERS_LICENSE_STATE = $res_st->fields['PK_STATES'];
	}
	
	if($PK_MARITAL_STATUS != '') {
		$res_st = $db->Execute("select PK_MARITAL_STATUS from Z_MARITAL_STATUS WHERE MARITAL_STATUS = '$PK_MARITAL_STATUS' AND ACTIVE = 1 ");
		$PK_MARITAL_STATUS = $res_st->fields['PK_MARITAL_STATUS'];
	}
	
	if($PK_COUNTRY_CITIZEN != '') {
		$res_st = $db->Execute("select PK_COUNTRY from Z_COUNTRY WHERE NAME = '$PK_COUNTRY_CITIZEN' OR CODE = '$PK_COUNTRY_CITIZEN' ");
		$PK_COUNTRY_CITIZEN = $res_st->fields['PK_COUNTRY'];
	}

	if(strtolower($SSN_VERIFIED) == 'yes')
		$SSN_VERIFIED = 1;
	else if(strtolower($SSN_VERIFIED) == 'no')
		$SSN_VERIFIED = 0;
	else
		$SSN_VERIFIED = 0;
		
	if(strtolower($GENDER) == 'male')
		$GENDER = 1;
	else if(strtolower($GENDER) == 'female')
		$GENDER = 2;
	else
		$GENDER = 0;

	if(strtolower($PREVIOUS_COLLEGE) == 'yes')
		$PREVIOUS_COLLEGE = 1;
	else if(strtolower($PREVIOUS_COLLEGE) == 'no')
		$PREVIOUS_COLLEGE = 2;
	else
		$PREVIOUS_COLLEGE = 2;

	if($PK_CITIZENSHIP != '') {
		$res_st = $db->Execute("select PK_CITIZENSHIP from Z_CITIZENSHIP WHERE CITIZENSHIP = '$PK_CITIZENSHIP' ");
		$PK_CITIZENSHIP = $res_st->fields['PK_CITIZENSHIP'];
	}
	
	if($PK_STATE_OF_RESIDENCY != '') {
		$res_st = $db->Execute("select PK_STATES from Z_STATES WHERE STATE_CODE = '$PK_STATE_OF_RESIDENCY' OR STATE_NAME = '$PK_STATE_OF_RESIDENCY' ");
		$PK_STATE_OF_RESIDENCY = $res_st->fields['PK_STATES'];
	}
	
	$PK_RACE_ARR = array();
	if(!empty($DATA->RACE)){
		foreach($DATA->RACE as $PK_RACE) {
			$res_st = $db->Execute("select PK_RACE from Z_RACE WHERE RACE = '$PK_RACE' ");
			if($res_st->RecordCount() > 0)
				$PK_RACE_ARR[] = $res_st->fields['PK_RACE'];
		}
	}
	
	$PK_TERM_MASTER 			= trim($DATA->ENROLLMENT->FIRST_TERM_DATE);
	$PK_CAMPUS_PROGRAM 			= trim($DATA->ENROLLMENT->PROGRAM_CODE);
	$PK_REPRESENTATIVE 			= trim($DATA->ENROLLMENT->ADMISSION_REP);
	$PK_LEAD_SOURCE 			= trim($DATA->ENROLLMENT->LEAD_SOURCE);
	$PK_LEAD_CONTACT_SOURCE 	= trim($DATA->ENROLLMENT->CONTACT_SOURCE);
	$PK_ENROLLMENT_STATUS 		= trim($DATA->ENROLLMENT->PART_FULL_TIME_ID);
	$PK_SESSION 				= trim($DATA->ENROLLMENT->SESSION);
	$PK_FUNDING 				= trim($DATA->ENROLLMENT->FUNDING);

	if($PK_TERM_MASTER != '') {
		$res_st = $db->Execute("select PK_TERM_MASTER from S_TERM_MASTER WHERE BEGIN_DATE = '$PK_TERM_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		$PK_TERM_MASTER = $res_st->fields['PK_TERM_MASTER'];
	}
	
	if($PK_CAMPUS_PROGRAM != '') {
		$res_st = $db->Execute("select PK_CAMPUS_PROGRAM from M_CAMPUS_PROGRAM WHERE CODE = '$PK_CAMPUS_PROGRAM' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		$PK_CAMPUS_PROGRAM = $res_st->fields['PK_CAMPUS_PROGRAM'];
	}
	
	if($PK_REPRESENTATIVE != '') {
		$res_st = $db->Execute("select PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE TRIM(CONCAT(FIRST_NAME,' ',LAST_NAME)) = '$PK_REPRESENTATIVE' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		$PK_REPRESENTATIVE = $res_st->fields['PK_EMPLOYEE_MASTER'];
	}
	
	if($PK_LEAD_SOURCE != '') {
		$res_st = $db->Execute("select PK_LEAD_SOURCE from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND LEAD_SOURCE = '$PK_LEAD_SOURCE' ");
		$PK_LEAD_SOURCE = $res_st->fields['PK_LEAD_SOURCE'];
	}
	
	if($PK_LEAD_CONTACT_SOURCE != '') {
		$res_st = $db->Execute("select PK_LEAD_CONTACT_SOURCE from M_LEAD_CONTACT_SOURCE WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND LEAD_CONTACT_SOURCE = '$PK_LEAD_CONTACT_SOURCE' ");
		$PK_LEAD_CONTACT_SOURCE = $res_st->fields['PK_LEAD_CONTACT_SOURCE'];
	}
	
	if($PK_ENROLLMENT_STATUS != '') {
		$res_st = $db->Execute("select PK_ENROLLMENT_STATUS from M_ENROLLMENT_STATUS WHERE CODE = '$PK_ENROLLMENT_STATUS' ");
		$PK_ENROLLMENT_STATUS = $res_st->fields['PK_ENROLLMENT_STATUS'];
	}
	
	if($PK_SESSION != '') {
		$res_st = $db->Execute("select PK_SESSION from M_SESSION WHERE SESSION = '$PK_SESSION' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		$PK_SESSION = $res_st->fields['PK_SESSION'];
	}
	
	if($PK_FUNDING != '') {
		$res_st = $db->Execute("select PK_FUNDING from M_FUNDING WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND FUNDING = '$PK_FUNDING' ");
		$PK_FUNDING = $res_st->fields['PK_FUNDING'];
	}

	/*$PK_CAMPUS_ARRAY = array();
	if(!empty($DATA->CAMPUS)){
		foreach($DATA->CAMPUS as $PK_CAMPUS) {
			$res_st = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE (CAMPUS_CODE = '$PK_CAMPUS' OR OFFICIAL_CAMPUS_NAME = '$PK_CAMPUS' OR CAMPUS_NAME = '$PK_CAMPUS') AND PK_ACCOUNT='$PK_ACCOUNT'");
			
			if($res_st->RecordCount() > 0){
				$PK_CAMPUS_ARRAY[] = $res_st->fields['PK_CAMPUS'];
			}
		}
	}*/
	
	$PK_CAMPUS_ARRAY = array();
	if($CAMPUS != ''){
		$res_st = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE (CAMPUS_CODE = '$CAMPUS' OR OFFICIAL_CAMPUS_NAME = '$CAMPUS' OR CAMPUS_NAME = '$CAMPUS') AND PK_ACCOUNT='$PK_ACCOUNT'");
		if($res_st->RecordCount() > 0){
			$PK_CAMPUS_ARRAY[] = $res_st->fields['PK_CAMPUS'];
		}
	}
	
	if($data['SUCCESS'] == 1) {
		$STUDENT_MASTER['FIRST_NAME'] 				= $FIRST_NAME;
		$STUDENT_MASTER['LAST_NAME'] 				= $LAST_NAME;
		$STUDENT_MASTER['MIDDLE_NAME'] 				= $MIDDLE_NAME;
		$STUDENT_MASTER['DATE_OF_BIRTH'] 			= $DATE_OF_BIRTH;
		$STUDENT_MASTER['DRIVERS_LICENSE'] 			= $DRIVERS_LICENSE;
		$STUDENT_MASTER['PK_DRIVERS_LICENSE_STATE'] = $PK_DRIVERS_LICENSE_STATE;
		$STUDENT_MASTER['PK_MARITAL_STATUS']  		= $PK_MARITAL_STATUS;
		$STUDENT_MASTER['GENDER']  					= $GENDER;
		$STUDENT_MASTER['PK_COUNTRY_CITIZEN']  		= $PK_COUNTRY_CITIZEN;
		$STUDENT_MASTER['PK_CITIZENSHIP']  			= $PK_CITIZENSHIP;
		$STUDENT_MASTER['PLACE_OF_BIRTH']  			= $PLACE_OF_BIRTH;
		$STUDENT_MASTER['PK_STATE_OF_RESIDENCY']  	= $PK_STATE_OF_RESIDENCY;
	
		if(!empty($PK_RACE_ARR)){
			$IPEDS_ETHNICITY = '';
			foreach($PK_RACE_ARR as $PK_RACE_1){
				if($PK_RACE_1 == 1) {
					$IPEDS_ETHNICITY = 'Hispanic/Latino';
					break;
				}
			}
			if($IPEDS_ETHNICITY == ''){
				if(count($PK_RACE_ARR) > 1)
					$IPEDS_ETHNICITY = 'Two or more races';
				else {
					$res_l = $db->Execute("select RACE FROM Z_RACE WHERE PK_RACE = '$PK_RACE_ARR[0]'");
					$IPEDS_ETHNICITY = $res_l->fields['RACE'];
				}
			}
		}
		
		$STUDENT_MASTER['IPEDS_ETHNICITY']  		= $IPEDS_ETHNICITY;
		$STUDENT_MASTER['PK_ACCOUNT']  				= $PK_ACCOUNT;
		$STUDENT_MASTER['CREATED_ON']  				= date("Y-m-d H:i");
		db_perform('S_STUDENT_MASTER', $STUDENT_MASTER, 'insert');
		$PK_STUDENT_MASTER = $db->insert_ID();
		
		if($SSN != '') {
			$SSN1 = preg_replace( '/[^0-9]/', '',$SSN);
			$SSN1 = $SSN1[0].$SSN1[1].$SSN1[2].'-'.$SSN1[3].$SSN1[4].'-'.$SSN1[5].$SSN1[6].$SSN1[7].$SSN1[8];
				
			$STUDENT_MASTER1['SSN'] = my_encrypt($PK_ACCOUNT.$PK_EMPLOYEE_MASTER,$SSN1);
			db_perform('S_STUDENT_MASTER', $STUDENT_MASTER1, 'update'," PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		}
		
		$res_acc = $db->Execute("SELECT AUTO_GENERATE_STUD_ID,STUD_CODE,STUD_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$PK_ACCOUNT' "); 
		if($res_acc->fields['AUTO_GENERATE_STUD_ID'] == 1) {
			$STUDENT_ID = $res_acc->fields['STUD_CODE'].$res_acc->fields['STUD_NO'];
			$STUD_NO = $res_acc->fields['STUD_NO'] + 1;
			$db->Execute("UPDATE Z_ACCOUNT SET STUD_NO = '$STUD_NO' WHERE PK_ACCOUNT = '$PK_ACCOUNT' "); 
		}
		
		/* Ticket # 1595 
		$STUDENT_ACADEMICS['ENTRY_DATE'] 	 			= date("Y-m-d");
		$STUDENT_ACADEMICS['ENTRY_TIME'] 	 			= date("H:i:s");
		*/
		//$STUDENT_ACADEMICS['PK_LEAD_CONTACT_SOURCE'] 	= $PK_LEAD_CONTACT_SOURCE;
		$STUDENT_ACADEMICS['ADM_USER_ID']				= $ADM_USER_ID;
		$STUDENT_ACADEMICS['STUDENT_ID']				= $STUDENT_ID;
		$STUDENT_ACADEMICS['PK_HIGHEST_LEVEL_OF_EDU']	= $PK_HIGHEST_LEVEL_OF_EDU;
		$STUDENT_ACADEMICS['PREVIOUS_COLLEGE']			= $PREVIOUS_COLLEGE;
		$STUDENT_ACADEMICS['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
		$STUDENT_ACADEMICS['PK_ACCOUNT']  				= $PK_ACCOUNT;
		$STUDENT_ACADEMICS['CREATED_ON']  				= date("Y-m-d H:i");
		db_perform('S_STUDENT_ACADEMICS', $STUDENT_ACADEMICS, 'insert');
		
		$res_st = $db->Execute("SELECT PK_STUDENT_STATUS FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS_MASTER = '1' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		$PK_STUDENT_STATUS = $res_st->fields['PK_STUDENT_STATUS']; 
	
		/* Ticket # 1595 */
		$STUDENT_ENROLLMENT['ENTRY_DATE'] 	 			= date("Y-m-d");
		$STUDENT_ENROLLMENT['ENTRY_TIME'] 	 			= date("H:i:s");
		/* Ticket # 1595  */
		$STUDENT_ENROLLMENT['IS_ACTIVE_ENROLLMENT'] 	= 1;
		$STUDENT_ENROLLMENT['PK_LEAD_CONTACT_SOURCE'] 	= $PK_LEAD_CONTACT_SOURCE;
		$STUDENT_ENROLLMENT['PK_ENROLLMENT_STATUS'] 	= $PK_ENROLLMENT_STATUS;
		$STUDENT_ENROLLMENT['PK_STUDENT_MASTER'] 	 	= $PK_STUDENT_MASTER;
		$STUDENT_ENROLLMENT['PK_TERM_MASTER'] 	 		= $PK_TERM_MASTER;
		$STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'] 	 	= $PK_CAMPUS_PROGRAM;
		$STUDENT_ENROLLMENT['PK_STUDENT_STATUS'] 	 	= $PK_STUDENT_STATUS;
		$STUDENT_ENROLLMENT['STATUS_DATE'] 	 			= date("Y-m-d");
		$STUDENT_ENROLLMENT['PK_REPRESENTATIVE'] 	 	= $PK_REPRESENTATIVE;
		$STUDENT_ENROLLMENT['PK_LEAD_SOURCE'] 	 		= $PK_LEAD_SOURCE;
		$STUDENT_ENROLLMENT['PK_SESSION'] 	 			= $PK_SESSION;
		$STUDENT_ENROLLMENT['PK_FUNDING'] 	 			= $PK_FUNDING;
		$STUDENT_ENROLLMENT['PK_ACCOUNT']  		 		= $PK_ACCOUNT;
		$STUDENT_ENROLLMENT['CREATED_ON']  		 		= date("Y-m-d H:i");
		db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'insert');
		$PK_STUDENT_ENROLLMENT = $db->insert_ID();
		
		/* Ticket #1840 */
		$res_req = $db->Execute("select * from S_SCHOOL_REQUIREMENT WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND ACTIVE = 1 ");
		while (!$res_req->EOF) {
		
			$STUDENT_REQUIREMENT['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
			$STUDENT_REQUIREMENT['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
			$STUDENT_REQUIREMENT['TYPE'] 				  	= 1;
			$STUDENT_REQUIREMENT['ID'] 				  		= $res_req->fields['PK_SCHOOL_REQUIREMENT'];
			$STUDENT_REQUIREMENT['PK_REQUIREMENT_CATEGORY'] = $res_req->fields['PK_REQUIREMENT_CATEGORY'];
			$STUDENT_REQUIREMENT['REQUIREMENT'] 			= $res_req->fields['REQUIREMENT'];
			$STUDENT_REQUIREMENT['MANDATORY'] 				= $res_req->fields['MANDATORY'];
			$STUDENT_REQUIREMENT['PK_ACCOUNT']  			= $PK_ACCOUNT;
			$STUDENT_REQUIREMENT['CREATED_ON']  			= date("Y-m-d H:i");
			db_perform('S_STUDENT_REQUIREMENT', $STUDENT_REQUIREMENT, 'insert');
		
			$res_req->MoveNext();
		}
		
		if($PK_CAMPUS_PROGRAM > 0) {
			$res_req = $db->Execute("select * from M_CAMPUS_PROGRAM_REQUIREMENT WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND ACTIVE = 1 ");
			while (!$res_req->EOF) {
			
				$STUDENT_REQUIREMENT1['PK_STUDENT_MASTER'] 			= $PK_STUDENT_MASTER;
				$STUDENT_REQUIREMENT1['PK_STUDENT_ENROLLMENT'] 		= $PK_STUDENT_ENROLLMENT;
				$STUDENT_REQUIREMENT1['TYPE'] 				  		= 2;
				$STUDENT_REQUIREMENT1['ID'] 				  		= $res_req->fields['PK_CAMPUS_PROGRAM_REQUIREMENT'];
				$STUDENT_REQUIREMENT1['PK_REQUIREMENT_CATEGORY'] 	= $res_req->fields['PK_REQUIREMENT_CATEGORY'];
				$STUDENT_REQUIREMENT1['REQUIREMENT'] 				= $res_req->fields['REQUIREMENT'];
				$STUDENT_REQUIREMENT1['MANDATORY'] 					= $res_req->fields['MANDATORY'];
				$STUDENT_REQUIREMENT1['PK_ACCOUNT']  				= $PK_ACCOUNT;
				$STUDENT_REQUIREMENT1['CREATED_BY']  				= $_SESSION['PK_USER'];
				$STUDENT_REQUIREMENT1['CREATED_ON']  				= date("Y-m-d H:i");
				db_perform('S_STUDENT_REQUIREMENT', $STUDENT_REQUIREMENT1, 'insert');
			
				$res_req->MoveNext();
			}
		}
		/* Ticket #1840 */
		
		if(!empty($PK_RACE_ARR)){
			foreach($PK_RACE_ARR as $PK_RACE) {
				$STUDENT_RACE['PK_RACE']   			= $PK_RACE;
				$STUDENT_RACE['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
				$STUDENT_RACE['PK_ACCOUNT'] 		= $PK_ACCOUNT;
				$STUDENT_RACE['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_STUDENT_RACE', $STUDENT_RACE, 'insert');
			}
		}
		
		if(!empty($PK_CAMPUS_ARRAY)){
			foreach($PK_CAMPUS_ARRAY as $PK_CAMPUS) {
				$STUDENT_CAMPUS['PK_CAMPUS']   				= $PK_CAMPUS;
				$STUDENT_CAMPUS['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
				$STUDENT_CAMPUS['PK_STUDENT_ENROLLMENT']  	= $PK_STUDENT_ENROLLMENT;
				$STUDENT_CAMPUS['PK_ACCOUNT'] 				= $PK_ACCOUNT;
				$STUDENT_CAMPUS['CREATED_ON']  				= date("Y-m-d H:i");
				db_perform('S_STUDENT_CAMPUS', $STUDENT_CAMPUS, 'insert');
			}
		}

		$STUDENT_STATUS_LOG['PK_STUDENT_STATUS'] 		= $STUDENT_ENROLLMENT['PK_STUDENT_STATUS'];
		$STUDENT_STATUS_LOG['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
		$STUDENT_STATUS_LOG['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
		$STUDENT_STATUS_LOG['PK_ACCOUNT']  				= $PK_ACCOUNT;
		$STUDENT_STATUS_LOG['CHANGED_ON']  				= date("Y-m-d H:i");
		db_perform('S_STUDENT_STATUS_LOG', $STUDENT_STATUS_LOG, 'insert');
		
		if(!empty($DATA->NOTES)){
			foreach($DATA->NOTES as $NOTES) {
		
				$COMPLETED = $NOTES->COMPLETED;
				if(strtolower($COMPLETED) == 'yes')
					$COMPLETED = 1;
				else if(strtolower($COMPLETED) == 'no')
					$COMPLETED = 0;
				else
					$COMPLETED = 0;
					
				$PK_DEPARTMENT = $NOTES->DEPARTMENT;
				$res_st = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE DEPARTMENT = '$PK_DEPARTMENT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
				$PK_DEPARTMENT = $res_st->fields['PK_DEPARTMENT'];
				
				$PK_NOTE_STATUS = $NOTES->NOTE_STATUS;
				$res_st = $db->Execute("select PK_NOTE_STATUS from M_NOTE_STATUS WHERE NOTE_STATUS = '$PK_NOTE_STATUS' AND PK_ACCOUNT = '$PK_ACCOUNT'");
				$PK_NOTE_STATUS = $res_st->fields['PK_NOTE_STATUS'];
				
				$PK_NOTE_TYPE = $NOTES->NOTE_TYPE;
				$res_st = $db->Execute("select PK_NOTE_TYPE from M_NOTE_TYPE WHERE NOTE_TYPE = '$PK_NOTE_TYPE' AND PK_ACCOUNT = '$PK_ACCOUNT'");
				$PK_NOTE_TYPE = $res_st->fields['PK_NOTE_TYPE'];
				
				$PK_EMPLOYEE_MASTER = $NOTES->EMPLOYEE;
				$res_st = $db->Execute("select PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE TRIM(CONCAT(FIRST_NAME,' ',LAST_NAME)) = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT'");
				$PK_EMPLOYEE_MASTER = $res_st->fields['PK_EMPLOYEE_MASTER'];
					
				$STUDENT_NOTES['PK_ACCOUNT']   			= $PK_ACCOUNT;
				$STUDENT_NOTES['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
				$STUDENT_NOTES['PK_STUDENT_ENROLLMENT'] = $PK_STUDENT_ENROLLMENT;
				$STUDENT_NOTES['PK_DEPARTMENT'] 		= $PK_DEPARTMENT;
				$STUDENT_NOTES['PK_NOTE_STATUS'] 		= $PK_NOTE_STATUS;
				$STUDENT_NOTES['PK_NOTE_TYPE'] 			= $PK_NOTE_TYPE;
				$STUDENT_NOTES['NOTE_DATE'] 			= $NOTES->NOTE_DATE;
				$STUDENT_NOTES['NOTE_TIME'] 			= $NOTES->NOTE_TIME;
				$STUDENT_NOTES['FOLLOWUP_DATE'] 		= $NOTES->FOLLOWUP_DATE;
				$STUDENT_NOTES['FOLLOWUP_TIME'] 		= $NOTES->FOLLOWUP_TIME;
				$STUDENT_NOTES['PK_EMPLOYEE_MASTER'] 	= $PK_EMPLOYEE_MASTER;
				$STUDENT_NOTES['SATISFIED'] 			= $COMPLETED;
				$STUDENT_NOTES['NOTES'] 				= $NOTES->COMMENTS;
				$STUDENT_NOTES['IS_EVENT'] 				= 0;
				$STUDENT_NOTES['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_STUDENT_NOTES', $STUDENT_NOTES, 'insert');
			}
		}
		
		if(!empty($DATA->DOCUMENTS)){
			foreach($DATA->DOCUMENTS as $DOCUMENTS) {
			
				$RECEIVED = $DOCUMENTS->RECEIVED;
				if(strtolower($RECEIVED) == 'yes')
					$RECEIVED = 1;
				else if(strtolower($RECEIVED) == 'no')
					$RECEIVED = 0;
				else
					$RECEIVED = 0;
					
				if($DOCUMENTS->DOCUMENT_TYPE == '') {
					$res_st = $db->Execute("select PK_DOCUMENT_TYPE from M_DOCUMENT_TYPE WHERE DOCUMENT_TYPE  = '".$DOCUMENTS->DOCUMENT_TYPE_ID."' ");
					$PK_DOCUMENT_TYPE = $res_st->fields['PK_DOCUMENT_TYPEfrom'];
				} else
					$PK_DOCUMENT_TYPE = '';
			
				$COPY_TO		= '';
				$DOCUMENT_URL 	= $DOCUMENTS->DOCUMENT_URL;
				if($DOCUMENT_URL != '') {
					//$file_dir_1 = '../backend_assets/school/school_'.$PK_ACCOUNT.'/student/';
					$file_dir_1 = '../backend_assets/tmp_upload/';
					
					$DOCUMENT_TYPE = str_replace("/","-",$_POST['DOCUMENT_TYPE']);
					$DOCUMENT_TYPE = str_replace("\\","-",$DOCUMENT_TYPE);
					$DOCUMENT_TYPE = str_replace("&","-",$DOCUMENT_TYPE);
					$DOCUMENT_TYPE = str_replace("*","-",$DOCUMENT_TYPE);
					$DOCUMENT_TYPE = str_replace(":","-",$DOCUMENT_TYPE);
					$DOCUMENT_TYPE = str_replace("?","-",$DOCUMENT_TYPE);
					$DOCUMENT_TYPE = str_replace("<","-",$DOCUMENT_TYPE);
					$DOCUMENT_TYPE = str_replace(">","-",$DOCUMENT_TYPE);
					$DOCUMENT_TYPE = str_replace("|","-",$DOCUMENT_TYPE);
					$DOCUMENT_TYPE = str_replace(" ","_",$DOCUMENT_TYPE);
					$DOCUMENT_TYPE = str_replace("=","_",$DOCUMENT_TYPE);

					$extn 			= explode(".",$DOCUMENTS->DOCUMENT_NAME);
					$iindex			= count($extn) - 1;
					$rand_string 	= time()."_".rand(10000,99999);
					$file11			= $PK_STUDENT_MASTER.'_'.$DOCUMENT_TYPE.'_'.$rand_string.".".$extn[$iindex];	
					$extension   	= strtolower($extn[$iindex]);
					$COPY_TO		= $file_dir_1.$file11;
					
					file_put_contents("../".$COPY_TO, fopen($DOCUMENT_URL, 'r'));
				}
				
				$PK_EMPLOYEE_MASTER = $DOCUMENTS->EMPLOYEE;
				$res_st = $db->Execute("select PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE TRIM(CONCAT(FIRST_NAME,' ',LAST_NAME)) = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT'");
				$PK_EMPLOYEE_MASTER = $res_st->fields['PK_EMPLOYEE_MASTER'];
				
				$STUDENT_DOCUMENTS['PK_EMPLOYEE_MASTER']  	= $PK_EMPLOYEE_MASTER;
				$STUDENT_DOCUMENTS['PK_DOCUMENT_TYPE']  	= $PK_DOCUMENT_TYPE;
				$STUDENT_DOCUMENTS['DOCUMENT_TYPE']  		= $DOCUMENTS->DOCUMENT_TYPE;
				$STUDENT_DOCUMENTS['REQUESTED_DATE']  		= $DOCUMENTS->REQUESTED_DATE;
				$STUDENT_DOCUMENTS['RECEIVED']  			= $RECEIVED;
				$STUDENT_DOCUMENTS['DATE_RECEIVED']  		= $DOCUMENTS->DATE_RECEIVED;
				$STUDENT_DOCUMENTS['FOLLOWUP_DATE']  		= $DOCUMENTS->FOLLOWUP_DATE;
				$STUDENT_DOCUMENTS['NOTES']  				= $DOCUMENTS->NOTES;
				$STUDENT_DOCUMENTS['DOCUMENT_NAME']  		= $DOCUMENTS->DOCUMENT_NAME;
				$STUDENT_DOCUMENTS['DOCUMENT_PATH']  		= $COPY_TO;
				
				$STUDENT_DOCUMENTS['PK_ACCOUNT']  			= $PK_ACCOUNT;
				$STUDENT_DOCUMENTS['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
				$STUDENT_DOCUMENTS['PK_STUDENT_ENROLLMENT'] = $PK_STUDENT_ENROLLMENT;
				$STUDENT_DOCUMENTS['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_STUDENT_DOCUMENTS', $STUDENT_DOCUMENTS, 'insert');
				$PK_STUDENT_DOCUMENTS = $db->insert_ID();
				
				foreach($DOCUMENTS->DEPARTMENTS as $PK_DEPARTMENT){
					$res_st = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE DEPARTMENT = '$PK_DEPARTMENT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
					if($res_st->RecordCount() > 0) {
						$STUDENT_DOCUMENTS_DEPARTMENT['PK_DEPARTMENT']   		= $res_st->fields['PK_DEPARTMENT'];
						$STUDENT_DOCUMENTS_DEPARTMENT['PK_STUDENT_DOCUMENTS'] 	= $PK_STUDENT_DOCUMENTS;
						$STUDENT_DOCUMENTS_DEPARTMENT['PK_ACCOUNT'] 			= $PK_ACCOUNT;
						$STUDENT_DOCUMENTS_DEPARTMENT['CREATED_ON']  			= date("Y-m-d H:i");
						db_perform('S_STUDENT_DOCUMENTS_DEPARTMENT', $STUDENT_DOCUMENTS_DEPARTMENT, 'insert');
					}
				}
			}
		}
		
		if(!empty($DATA->QUESTIONNAIRE)){
			foreach($DATA->QUESTIONNAIRE as $QUESTIONNAIRE) {
				$QUESTION = $QUESTIONNAIRE->QUESTION;
				$res_st = $db->Execute("select PK_QUESTIONNAIRE from M_QUESTIONNAIRE WHERE QUESTION = '$QUESTION' AND PK_ACCOUNT = '$PK_ACCOUNT'");
				if($res_st->RecordCount() > 0) {
					$STUDENT_QUESTIONNAIRE['ANSWER'] 				 = $QUESTIONNAIRE->ANSWER;
					$STUDENT_QUESTIONNAIRE['PK_STUDENT_ENROLLMENT']  = $PK_STUDENT_ENROLLMENT;
					$STUDENT_QUESTIONNAIRE['PK_QUESTIONNAIRE']  	 = $res_st->fields['PK_QUESTIONNAIRE'];
					$STUDENT_QUESTIONNAIRE['PK_STUDENT_MASTER'] 	 = $PK_STUDENT_MASTER;
					$STUDENT_QUESTIONNAIRE['PK_ACCOUNT'] 			 = $PK_ACCOUNT;
					$STUDENT_QUESTIONNAIRE['CREATED_ON']  			 = date("Y-m-d H:i");
					db_perform('S_STUDENT_QUESTIONNAIRE', $STUDENT_QUESTIONNAIRE, 'insert');
				}
			}
		}
		
		if(!empty($DATA->OTHER_EDUCATION)){
			foreach($DATA->OTHER_EDUCATION as $OTHER_EDUCATION) {
				
				$GRADUATED = $OTHER_EDUCATION->GRADUATED;
				if(strtolower($GRADUATED) == 'yes')
					$GRADUATED = 1;
				else if(strtolower($GRADUATED) == 'no')
					$GRADUATED = 0;
				else
					$GRADUATED = 0;
					
				$TRANSCRIPT_REQUESTED = $OTHER_EDUCATION->TRANSCRIPT_REQUESTED;
				if(strtolower($TRANSCRIPT_REQUESTED) == 'yes')
					$TRANSCRIPT_REQUESTED = 1;
				else if(strtolower($TRANSCRIPT_REQUESTED) == 'no')
					$TRANSCRIPT_REQUESTED = 0;
				else
					$TRANSCRIPT_REQUESTED = 0;
					
				$TRANSCRIPT_RECEIVED = $OTHER_EDUCATION->TRANSCRIPT_RECEIVED;
				if(strtolower($TRANSCRIPT_RECEIVED) == 'yes')
					$TRANSCRIPT_RECEIVED = 1;
				else if(strtolower($TRANSCRIPT_RECEIVED) == 'no')
					$TRANSCRIPT_RECEIVED = 0;
				else
					$TRANSCRIPT_RECEIVED = 0;
					
				$SCHOOL_PHONE 	= preg_replace( '/[^0-9]/', '',$OTHER_EDUCATION->SCHOOL_PHONE);
				$SCHOOL_FAX 	= preg_replace( '/[^0-9]/', '',$OTHER_EDUCATION->SCHOOL_FAX);
					
				if($SCHOOL_PHONE != '')
					$SCHOOL_PHONE = '('.$SCHOOL_PHONE[0].$SCHOOL_PHONE[1].$SCHOOL_PHONE[2].') '.$SCHOOL_PHONE[3].$SCHOOL_PHONE[4].$SCHOOL_PHONE[5].'-'.$SCHOOL_PHONE[6].$SCHOOL_PHONE[7].$SCHOOL_PHONE[8].$SCHOOL_PHONE[9];
					
				if($SCHOOL_FAX != '')
					$SCHOOL_FAX = '('.$SCHOOL_FAX[0].$SCHOOL_FAX[1].$SCHOOL_FAX[2].') '.$SCHOOL_FAX[3].$SCHOOL_FAX[4].$SCHOOL_FAX[5].'-'.$SCHOOL_FAX[6].$SCHOOL_FAX[7].$SCHOOL_FAX[8].$SCHOOL_FAX[9];
					
				$PK_EDUCATION_TYPE = $OTHER_EDUCATION->EDUCATION_TYPE;
				$res_st = $db->Execute("select PK_EDUCATION_TYPE from M_EDUCATION_TYPE WHERE TRIM(EDUCATION_TYPE) = '$PK_EDUCATION_TYPE' ");
				$PK_EDUCATION_TYPE = $res_st->fields['PK_EDUCATION_TYPE'];
				
				$PK_STATES = $OTHER_EDUCATION->STATE;
				if($PK_STATES != '') {
					$res_st = $db->Execute("select PK_STATES from Z_STATES WHERE STATE_CODE = '$PK_STATES' OR STATE_NAME = '$PK_STATES' ");
					$PK_STATES = $res_st->fields['PK_STATES'];
				}
				
				$STUDENT_OTHER_EDU['PK_EDUCATION_TYPE'] 		= $PK_EDUCATION_TYPE;
				$STUDENT_OTHER_EDU['GRADUATED'] 				= $GRADUATED;
				$STUDENT_OTHER_EDU['GRADUATED_DATE'] 			= $OTHER_EDUCATION->GRADUATED_DATE;;
				$STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED'] 		= $TRANSCRIPT_REQUESTED;
				$STUDENT_OTHER_EDU['TRANSCRIPT_REQUESTED_DATE'] = $OTHER_EDUCATION->TRANSCRIPT_REQUESTED_DATE;
				$STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED'] 		= $TRANSCRIPT_RECEIVED;
				$STUDENT_OTHER_EDU['TRANSCRIPT_RECEIVED_DATE']  = $OTHER_EDUCATION->TRANSCRIPT_RECEIVED_DATE;
				$STUDENT_OTHER_EDU['SCHOOL_NAME']  				= $OTHER_EDUCATION->SCHOOL_NAME;
				$STUDENT_OTHER_EDU['ADDRESS'] 	 				= $OTHER_EDUCATION->ADDRESS;
				$STUDENT_OTHER_EDU['ADDRESS_1']  				= $OTHER_EDUCATION->ADDRESS_1;
				$STUDENT_OTHER_EDU['CITY']  					= $OTHER_EDUCATION->CITY;
				$STUDENT_OTHER_EDU['PK_STATE']  				= $PK_STATES;
				$STUDENT_OTHER_EDU['ZIP']  						= $OTHER_EDUCATION->ZIP;
				$STUDENT_OTHER_EDU['OTHER_SCHOOL_PHONE']  		= $SCHOOL_PHONE;
				$STUDENT_OTHER_EDU['OTHER_SCHOOL_FAX']  		= $SCHOOL_FAX;
				$STUDENT_OTHER_EDU['PK_STUDENT_MASTER'] 	 	= $PK_STUDENT_MASTER;
				$STUDENT_OTHER_EDU['PK_ACCOUNT'] 			 	= $PK_ACCOUNT;
				$STUDENT_OTHER_EDU['CREATED_ON']  			 = date("Y-m-d H:i");
				db_perform('S_STUDENT_OTHER_EDU', $STUDENT_OTHER_EDU, 'insert');
				
			}
		}
		
		if(!empty($DATA->CONTACTS)){
			foreach($DATA->CONTACTS as $CONTACTS) {
				$PK_STUDENT_CONTACT_TYPE_MASTER = $CONTACTS->CONTACT_TYPE;
				$PK_STUDENT_RELATIONSHIP_MASTER = $CONTACTS->RELATIONSHIP;
				$PK_STATES 						= $CONTACTS->STATE;
				$PK_COUNTRY 					= $CONTACTS->COUNTRY;
				
				$IS_ADDRESS_INVALID = $CONTACTS->IS_ADDRESS_INVALID;
				if(strtolower($IS_ADDRESS_INVALID) == 'yes')
					$IS_ADDRESS_INVALID = 1;
				else if(strtolower($IS_ADDRESS_INVALID) == 'no')
					$IS_ADDRESS_INVALID = 0;
				else
					$IS_ADDRESS_INVALID = 0;	
					
				$HOME_PHONE_INVALID = $CONTACTS->HOME_PHONE_INVALID;
				if(strtolower($HOME_PHONE_INVALID) == 'yes')
					$HOME_PHONE_INVALID = 1;
				else if(strtolower($HOME_PHONE_INVALID) == 'no')
					$HOME_PHONE_INVALID = 0;
				else
					$HOME_PHONE_INVALID = 0;	
					
				$WORK_PHONE_INVALID = $CONTACTS->WORK_PHONE_INVALID;
				if(strtolower($WORK_PHONE_INVALID) == 'yes')
					$WORK_PHONE_INVALID = 1;
				else if(strtolower($WORK_PHONE_INVALID) == 'no')
					$WORK_PHONE_INVALID = 0;
				else
					$WORK_PHONE_INVALID = 0;	
					
				$CELL_PHONE_INVALID = $CONTACTS->CELL_PHONE_INVALID;
				if(strtolower($CELL_PHONE_INVALID) == 'yes')
					$CELL_PHONE_INVALID = 1;
				else if(strtolower($CELL_PHONE_INVALID) == 'no')
					$CELL_PHONE_INVALID = 0;
				else
					$CELL_PHONE_INVALID = 0;
					
				$OTHER_PHONE_INVALID = $CONTACTS->OTHER_PHONE_INVALID;
				if(strtolower($OTHER_PHONE_INVALID) == 'yes')
					$OTHER_PHONE_INVALID = 1;
				else if(strtolower($OTHER_PHONE_INVALID) == 'no')
					$OTHER_PHONE_INVALID = 0;
				else
					$OTHER_PHONE_INVALID = 0;
					
				$EMAIL_INVALID = $CONTACTS->EMAIL_INVALID;
				if(strtolower($EMAIL_INVALID) == 'yes')
					$EMAIL_INVALID = 1;
				else if(strtolower($EMAIL_INVALID) == 'no')
					$EMAIL_INVALID = 0;
				else
					$EMAIL_INVALID = 0;
					
				$EMAIL_OTHER_INVALID = $CONTACTS->EMAIL_OTHER_INVALID;
				if(strtolower($EMAIL_OTHER_INVALID) == 'yes')
					$EMAIL_OTHER_INVALID = 1;
				else if(strtolower($EMAIL_OTHER_INVALID) == 'no')
					$EMAIL_OTHER_INVALID = 0;
				else
					$EMAIL_OTHER_INVALID = 0;
					
				$OPT_OUT = $CONTACTS->OPT_OUT;
				if(strtolower($OPT_OUT) == 'yes')
					$OPT_OUT = 1;
				else if(strtolower($OPT_OUT) == 'no')
					$OPT_OUT = 0;
				else
					$OPT_OUT = 0;
					
				$USE_EMAIL = $CONTACTS->USE_EMAIL;
				if(strtolower($USE_EMAIL) == 'yes')
					$USE_EMAIL = 1;
				else if(strtolower($USE_EMAIL) == 'no')
					$USE_EMAIL = 0;
				else
					$USE_EMAIL = 0;
					
				$ACTIVE = $CONTACTS->ACTIVE;
				if(strtolower($ACTIVE) == 'yes')
					$ACTIVE = 1;
				else if(strtolower($ACTIVE) == 'no')
					$ACTIVE = 0;
				else
					$ACTIVE = 1;
					
				$HOME_PHONE 	= preg_replace( '/[^0-9]/', '',$CONTACTS->HOME_PHONE);
				$WORK_PHONE 	= preg_replace( '/[^0-9]/', '',$CONTACTS->WORK_PHONE);
				$CELL_PHONE 	= preg_replace( '/[^0-9]/', '',$CONTACTS->CELL_PHONE);
				$OTHER_PHONE 	= preg_replace( '/[^0-9]/', '',$CONTACTS->OTHER_PHONE);
				$FAX			= preg_replace( '/[^0-9]/', '',$CONTACTS->FAX);
				
				if($HOME_PHONE != '')
					$HOME_PHONE = '('.$HOME_PHONE[0].$HOME_PHONE[1].$HOME_PHONE[2].') '.$HOME_PHONE[3].$HOME_PHONE[4].$HOME_PHONE[5].'-'.$HOME_PHONE[6].$HOME_PHONE[7].$HOME_PHONE[8].$HOME_PHONE[9];
					
				if($WORK_PHONE != '')
					$WORK_PHONE = '('.$WORK_PHONE[0].$WORK_PHONE[1].$WORK_PHONE[2].') '.$WORK_PHONE[3].$WORK_PHONE[4].$WORK_PHONE[5].'-'.$WORK_PHONE[6].$WORK_PHONE[7].$WORK_PHONE[8].$WORK_PHONE[9];
					
				if($CELL_PHONE != '')
					$CELL_PHONE = '('.$CELL_PHONE[0].$CELL_PHONE[1].$CELL_PHONE[2].') '.$CELL_PHONE[3].$CELL_PHONE[4].$CELL_PHONE[5].'-'.$CELL_PHONE[6].$CELL_PHONE[7].$CELL_PHONE[8].$CELL_PHONE[9];
					
				if($OTHER_PHONE != '')
					$OTHER_PHONE = '('.$OTHER_PHONE[0].$OTHER_PHONE[1].$OTHER_PHONE[2].') '.$OTHER_PHONE[3].$OTHER_PHONE[4].$OTHER_PHONE[5].'-'.$OTHER_PHONE[6].$OTHER_PHONE[7].$OTHER_PHONE[8].$OTHER_PHONE[9];
					
				if($FAX != '')
					$FAX = '('.$FAX[0].$FAX[1].$FAX[2].') '.$FAX[3].$FAX[4].$FAX[5].'-'.$FAX[6].$FAX[7].$FAX[8].$FAX[9];
					
				if($PK_STUDENT_CONTACT_TYPE_MASTER != '') {
					$res_st = $db->Execute("select PK_STUDENT_CONTACT_TYPE_MASTER from M_STUDENT_CONTACT_TYPE_MASTER WHERE STUDENT_CONTACT_TYPE = '$PK_STUDENT_CONTACT_TYPE_MASTER' ");
					if($res_st->RecordCount() > 0){
						$PK_STUDENT_CONTACT_TYPE_MASTER = $res_st->fields['PK_STUDENT_CONTACT_TYPE_MASTER'];
					}
				}
				
				if($PK_STUDENT_RELATIONSHIP_MASTER != '') {
					$res_st = $db->Execute("select PK_STUDENT_RELATIONSHIP_MASTER from M_STUDENT_RELATIONSHIP_MASTER WHERE STUDENT_RELATIONSHIP = '$PK_STUDENT_RELATIONSHIP_MASTER' ");
					if($res_st->RecordCount() > 0){
						$PK_STUDENT_RELATIONSHIP_MASTER = $res_st->fields['PK_STUDENT_RELATIONSHIP_MASTER'];
					}
				}
				
				if($PK_STATES != '') {
					$res_st = $db->Execute("select PK_STATES from Z_STATES WHERE STATE_CODE = '$PK_STATES' OR STATE_NAME = '$PK_STATES' ");
					$PK_STATES = $res_st->fields['PK_STATES'];
				}
				
				if($PK_COUNTRY != '') {
					$res_st = $db->Execute("select PK_COUNTRY from Z_COUNTRY WHERE NAME = '$PK_COUNTRY' OR CODE = '$PK_COUNTRY' ");
					$PK_COUNTRY = $res_st->fields['PK_COUNTRY'];
				}
		
				$STUDENT_CONTACT['PK_STUDENT_CONTACT_TYPE_MASTER']   	= $PK_STUDENT_CONTACT_TYPE_MASTER;
				$STUDENT_CONTACT['PK_STUDENT_RELATIONSHIP_MASTER']   	= $PK_STUDENT_RELATIONSHIP_MASTER;
				$STUDENT_CONTACT['COMPANY_NAME']   						= $CONTACTS->COMPANY_NAME;
				$STUDENT_CONTACT['CONTACT_TITLE']   					= $CONTACTS->CONTACT_TITLE;
				$STUDENT_CONTACT['ADDRESS']   							= $CONTACTS->ADDRESS;
				$STUDENT_CONTACT['ADDRESS_1']   						= $CONTACTS->ADDRESS_1;
				$STUDENT_CONTACT['CITY']   								= $CONTACTS->CITY;
				$STUDENT_CONTACT['PK_STATES']   						= $PK_STATES;
				$STUDENT_CONTACT['ZIP']   								= $CONTACTS->ZIP;
				$STUDENT_CONTACT['PK_COUNTRY']   						= $PK_COUNTRY;
				$STUDENT_CONTACT['CONTACT_NAME']   						= $CONTACTS->CONTACT_NAME;
				$STUDENT_CONTACT['ADDRESS_INVALID'] 					= $IS_ADDRESS_INVALID;
				$STUDENT_CONTACT['HOME_PHONE'] 							= $HOME_PHONE;
				$STUDENT_CONTACT['HOME_PHONE_INVALID'] 					= $HOME_PHONE_INVALID;
				$STUDENT_CONTACT['WORK_PHONE'] 							= $WORK_PHONE;
				$STUDENT_CONTACT['WORK_PHONE_INVALID'] 					= $WORK_PHONE_INVALID;
				$STUDENT_CONTACT['OTHER_PHONE'] 						= $OTHER_PHONE;
				$STUDENT_CONTACT['OTHER_PHONE_INVALID'] 				= $OTHER_PHONE_INVALID;
				$STUDENT_CONTACT['CELL_PHONE'] 							= $CELL_PHONE;
				$STUDENT_CONTACT['CELL_PHONE_INVALID'] 					= $CELL_PHONE_INVALID;
				$STUDENT_CONTACT['FAX'] 								= $FAX;
				$STUDENT_CONTACT['EMAIL'] 								= $CONTACTS->EMAIL;
				$STUDENT_CONTACT['EMAIL_INVALID'] 						= $EMAIL_INVALID;
				$STUDENT_CONTACT['EMAIL_OTHER'] 						= $CONTACTS->EMAIL_OTHER;
				$STUDENT_CONTACT['EMAIL_OTHER_INVALID'] 				= $EMAIL_OTHER_INVALID;
				$STUDENT_CONTACT['OPT_OUT']   							= $OPT_OUT;
				$STUDENT_CONTACT['USE_EMAIL'] 							= $USE_EMAIL;
				$STUDENT_CONTACT['ACTIVE'] 								= $ACTIVE;
				$STUDENT_CONTACT['WEBSITE'] 							= $CONTACTS->WEBSITE;
				$STUDENT_CONTACT['PK_ACCOUNT']   						= $PK_ACCOUNT;
				$STUDENT_CONTACT['PK_STUDENT_MASTER']   				= $PK_STUDENT_MASTER;
				$STUDENT_CONTACT['CREATED_ON']  						= date("Y-m-d H:i");
				db_perform('S_STUDENT_CONTACT', $STUDENT_CONTACT, 'insert');
			}
		}
		
		$data['MESSAGE'] 	 = 'Lead Created';
		$data['INTERNAL_ID'] = $PK_STUDENT_MASTER;
	}
}

$data = json_encode($data);
echo $data;