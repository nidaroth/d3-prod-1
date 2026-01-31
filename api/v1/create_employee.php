<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"PRE_FIX_ID":"","FIRST_NAME":"Julie","LAST_NAME":"Rep","MIDDLE_NAME":"","SSN":"","EMPLOYEE_ID":"","TITLE":"","EMAIL":"","DOB":"0000-00-00","GENDER_ID":"1","MARITAL_STATUS_ID":"0","IPEDS_ETHNICITY":"","NETWORK_ID":"","COMPANY_EMP_ID":"","SUPERVISOR":"","FULL_PART_TIME_ID":"0","ELIGIBLE_FOR_REHIRE":"","SOC_CODE_ID":"0","DATE_HIRED":"0000-00-00","DATE_TERMINATED":"0000-00-00","ACTIVE":"Yes","TURN_OFF_ASSIGNMENTS":"No","IS_FACULTY":"No","ADDRESS":"","ADDRESS_1":"","CITY":"","STATE_ID":"0","STATE_CODE":,"ZIP":"","COUNTRY_ID":"0","HOME_PHONE":"","WORK_PHONE":"","CELL_PHONE":"","CAMPUS":[2,3],"DEPARTMENT":[27,29],"NOTES":[{"NOTE_TYPE":"Absence","NOTE_STATUS":"xxxx","NOTE_DATE":"2021-02-28","NOTE_TIME":"17:37:00","FOLLOWUP_DATE":"2021-03-02","FOLLOWUP_TIME":"18:30:00","NOTES":"8888888888888888888 cccccccc dd"},{"NOTE_TYPE":"Absence","NOTE_STATUS":"new","NOTE_DATE":"2021-02-11","NOTE_TIME":"17:34:00","FOLLOWUP_DATE":"2021-02-25","FOLLOWUP_TIME":"10:30:00","NOTES":"aaaaaaaaa"}],"GENDER":"","FULL_PART_TIME":""}';

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
	
	$PK_PRE_FIX				= trim($DATA->PREFIX_ID);
	$FIRST_NAME 			= trim($DATA->FIRST_NAME);
	$LAST_NAME 				= trim($DATA->LAST_NAME);
	$MIDDLE_NAME 			= trim($DATA->MIDDLE_NAME);
	$SSN 					= trim($DATA->SSN);
	$EMPLOYEE_ID 			= trim($DATA->EMPLOYEE_ID);
	$TITLE 					= trim($DATA->TITLE);
	$DOB 					= trim($DATA->DOB);
	$GENDER 				= trim($DATA->GENDER_ID);
	$PK_MARITAL_STATUS 		= trim($DATA->MARITAL_STATUS_ID);
	$IPEDS_ETHNICITY 		= trim($DATA->IPEDS_ETHNICITY);
	$NETWORK_ID 			= trim($DATA->NETWORK_ID);
	$COMPANY_EMP_ID 		= trim($DATA->COMPANY_EMP_ID);
	$SUPERVISOR 			= trim($DATA->SUPERVISOR);
	$FULL_PART_TIME 		= trim($DATA->FULL_PART_TIME_ID);
	$ELIGIBLE_FOR_REHIRE 	= trim($DATA->ELIGIBLE_FOR_REHIRE);
	$PK_SOC_CODE 			= trim($DATA->SOC_CODE_ID);
	$DATE_HIRED 			= trim($DATA->DATE_HIRED);
	$DATE_TERMINATED 		= trim($DATA->DATE_TERMINATED);
	$ACTIVE 				= trim($DATA->ACTIVE);
	$TURN_OFF_ASSIGNMENTS 	= trim($DATA->TURN_OFF_ASSIGNMENTS);
	$IS_FACULTY 			= trim($DATA->IS_FACULTY);
	$ADDRESS 				= trim($DATA->ADDRESS);
	$ADDRESS_1 				= trim($DATA->ADDRESS_1);
	$CITY 					= trim($DATA->CITY);
	$PK_STATES 				= trim($DATA->STATE_ID);
	$PK_COUNTRY 			= trim($DATA->COUNTRY_ID);
	$ZIP 					= trim($DATA->ZIP);
	
	$EMAIL 					= trim($DATA->EMAIL);
	$EMAIL_OTHER 			= trim($DATA->EMAIL_OTHER);
	$HOME_PHONE 			= preg_replace( '/[^0-9]/', '',$DATA->HOME_PHONE);
	$WORK_PHONE 			= preg_replace( '/[^0-9]/', '',$DATA->WORK_PHONE);
	$CELL_PHONE 			= preg_replace( '/[^0-9]/', '',$DATA->CELL_PHONE);
	
	
	if($HOME_PHONE != '')
		$HOME_PHONE = '('.$HOME_PHONE[0].$HOME_PHONE[1].$HOME_PHONE[2].') '.$HOME_PHONE[3].$HOME_PHONE[4].$HOME_PHONE[5].'-'.$HOME_PHONE[6].$HOME_PHONE[7].$HOME_PHONE[8].$HOME_PHONE[9];
		
	if($WORK_PHONE != '')
		$WORK_PHONE = '('.$WORK_PHONE[0].$WORK_PHONE[1].$WORK_PHONE[2].') '.$WORK_PHONE[3].$WORK_PHONE[4].$WORK_PHONE[5].'-'.$WORK_PHONE[6].$WORK_PHONE[7].$WORK_PHONE[8].$WORK_PHONE[9];
		
	if($CELL_PHONE != '')
		$CELL_PHONE = '('.$CELL_PHONE[0].$CELL_PHONE[1].$CELL_PHONE[2].') '.$CELL_PHONE[3].$CELL_PHONE[4].$CELL_PHONE[5].'-'.$CELL_PHONE[6].$CELL_PHONE[7].$CELL_PHONE[8].$CELL_PHONE[9];
	
	
	if($FIRST_NAME == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'First Name - Missing';
	}
	
	if($LAST_NAME == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Last Name - Missing';
	}
	
	if($GENDER != '') {
		if($GENDER != 1 && $GENDER != 2) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid GENDER_ID Value';
		}
	}
	
	if($PK_MARITAL_STATUS != '') {
		$res_st = $db->Execute("select PK_MARITAL_STATUS from Z_MARITAL_STATUS WHERE PK_MARITAL_STATUS = '$PK_MARITAL_STATUS' AND ACTIVE = 1 ");
		$PK_MARITAL_STATUS = $res_st->fields['PK_MARITAL_STATUS'];
		
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid MARITAL_STATUS_ID Value';
		}
	}
	
	if($FULL_PART_TIME != '') {
		if($FULL_PART_TIME != 1 && $FULL_PART_TIME != 2) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid FULL_PART_TIME_ID Value';
		}
	}
	
	if($ELIGIBLE_FOR_REHIRE != '') {
		if($ELIGIBLE_FOR_REHIRE != 1 && $ELIGIBLE_FOR_REHIRE != 2) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid ELIGIBLE_FOR_REHIRE Value';
		}
	}
	
	if($PK_PRE_FIX != '') {
		$res_st = $db->Execute("select PK_PRE_FIX from Z_PRE_FIX WHERE PK_PRE_FIX = '$PK_PRE_FIX' AND ACTIVE = 1 ");
		$PK_PRE_FIX = $res_st->fields['PK_PRE_FIX'];
		
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid PREFIX_ID Value';
		}
	}
	
	if($PK_SOC_CODE != '') {
		$res_st = $db->Execute("select PK_SOC_CODE from M_SOC_CODE WHERE PK_SOC_CODE = '$PK_SOC_CODE' AND ACTIVE = 1 AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		$PK_SOC_CODE = $res_st->fields['PK_SOC_CODE'];
		
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid SOC_CODE_ID Value';
		}
	}
	
	if(strtolower($ACTIVE) == 'yes')
		$ACTIVE = 1;
	else if(strtolower($ACTIVE) == 'no')
		$ACTIVE = 0;
	else if($ACTIVE != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid ACTIVE Value';
	} else
		$ACTIVE = 1;
	
	if(strtolower($TURN_OFF_ASSIGNMENTS) == 'yes')
		$TURN_OFF_ASSIGNMENTS = 1;
	else if(strtolower($TURN_OFF_ASSIGNMENTS) == 'no')
		$TURN_OFF_ASSIGNMENTS = 0;
	else if($TURN_OFF_ASSIGNMENTS != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid TURN_OFF_ASSIGNMENTS Value';
	} else
		$TURN_OFF_ASSIGNMENTS = 0;
		
	if(strtolower($IS_FACULTY) == 'yes')
		$IS_FACULTY = 1;
	else if(strtolower($IS_FACULTY) == 'no')
		$IS_FACULTY = 0;
	else if($IS_FACULTY != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid IS_FACULTY Value';
	} else
		$IS_FACULTY = 0;
		

	if($PK_STATES != '') {
		$res_st = $db->Execute("select PK_STATES from Z_STATES WHERE PK_STATES = '$PK_STATES' ");
		$PK_STATES = $res_st->fields['PK_STATES'];
		
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid STATE_ID Value';
		}
	}
	
	if($PK_COUNTRY != '') {
		$res_st = $db->Execute("select PK_COUNTRY from Z_COUNTRY WHERE PK_COUNTRY = '$PK_COUNTRY' ");
		$PK_COUNTRY = $res_st->fields['PK_COUNTRY'];
		
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid COUNTRY_ID Value';
		}
	}
	
	if(!empty($DATA->CAMPUS)){
		foreach($DATA->CAMPUS as $PK_CAMPUS) {
			$res_st = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid CAMPUS Value - '.$PK_CAMPUS;
			} else 
				$PK_CAMPUS_ARR[] = $PK_CAMPUS;
		}
	}
	
	if(!empty($DATA->DEPARTMENT)){
		foreach($DATA->DEPARTMENT as $PK_DEPARTMENT) {
			$res_st = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE PK_DEPARTMENT = '$PK_DEPARTMENT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid DEPARTMENT Value - '.$PK_DEPARTMENT;
			} else 
				$PK_DEPARTMENT_ARR[] = $PK_DEPARTMENT;
		}
	}

	if(!empty($DATA->RACE)){
		foreach($DATA->RACE as $PK_RACE) {
			$res_st = $db->Execute("select PK_RACE from Z_RACE WHERE PK_RACE = '$PK_RACE' ");
			
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid RACE Value - '.$PK_RACE;
			} else 
				$PK_RACE_ARR[] = $PK_RACE;
		}
	}
	
	if(!empty($DATA->NOTES)){
		foreach($DATA->NOTES as $NOTES) {
			if($NOTES->NOTE_TYPE_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing NOTE_TYPE_ID Value';
			} else {
				$PK_EMPLOYEE_NOTE_TYPE = $NOTES->NOTE_TYPE_ID;
				$res_st = $db->Execute("select PK_EMPLOYEE_NOTE_TYPE from M_EMPLOYEE_NOTE_TYPE WHERE PK_EMPLOYEE_NOTE_TYPE='$PK_EMPLOYEE_NOTE_TYPE' AND PK_ACCOUNT='$PK_ACCOUNT'");
			
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid NOTE_TYPE_ID Value - '.$PK_EMPLOYEE_NOTE_TYPE;
				}
			}
			
			if($NOTES->NOTE_STATUS_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing NOTE_STATUS_ID Value';
			} else {
				$PK_NOTE_STATUS = $NOTES->NOTE_STATUS_ID;
				$res_st = $db->Execute("select PK_NOTE_STATUS from M_NOTE_STATUS WHERE PK_NOTE_STATUS='$PK_NOTE_STATUS' AND PK_ACCOUNT='$PK_ACCOUNT'");
			
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid NOTE_STATUS_ID Value - '.$PK_NOTE_STATUS;
				}
			}
			
			if($NOTES->DEPARTMENT_ID == '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Missing DEPARTMENT_ID Value';
			} else {
				$PK_DEPARTMENT = $NOTES->DEPARTMENT_ID;
				$res_st = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE PK_DEPARTMENT = '$PK_DEPARTMENT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid DEPARTMENT_ID Value - '.$PK_DEPARTMENT;
				}
			}
		}
	}
	
	if($data['SUCCESS'] == 1) {
		$EMPLOYEE_MASTER['IS_FACULTY']  		= $IS_FACULTY;
		$EMPLOYEE_MASTER['LAST_NAME']  			= $LAST_NAME;
		$EMPLOYEE_MASTER['FIRST_NAME']  		= $FIRST_NAME;
		$EMPLOYEE_MASTER['MIDDLE_NAME']  		= $MIDDLE_NAME;
		$EMPLOYEE_MASTER['EMPLOYEE_ID']  		= $EMPLOYEE_ID;
		$EMPLOYEE_MASTER['TITLE']  				= $TITLE;
		$EMPLOYEE_MASTER['EMAIL']  				= $EMAIL;
		$EMPLOYEE_MASTER['EMAIL_OTHER']  		= $EMAIL_OTHER;
		$EMPLOYEE_MASTER['DOB']  				= $DOB;
		$EMPLOYEE_MASTER['GENDER']  			= $GENDER;
		$EMPLOYEE_MASTER['PK_MARITAL_STATUS']  	= $PK_MARITAL_STATUS;
		$EMPLOYEE_MASTER['IPEDS_ETHNICITY']  	= $IPEDS_ETHNICITY;
		$EMPLOYEE_MASTER['NETWORK_ID']  		= $NETWORK_ID;
		$EMPLOYEE_MASTER['COMPANY_EMP_ID']  	= $COMPANY_EMP_ID;
		$EMPLOYEE_MASTER['SUPERVISOR']  		= $SUPERVISOR;
		$EMPLOYEE_MASTER['FULL_PART_TIME']		= $FULL_PART_TIME;
		$EMPLOYEE_MASTER['ELIGIBLE_FOR_REHIRE'] = $ELIGIBLE_FOR_REHIRE;
		$EMPLOYEE_MASTER['PK_SOC_CODE']  		= $PK_SOC_CODE;
		$EMPLOYEE_MASTER['DATE_HIRED']  		= $DATE_HIRED;
		$EMPLOYEE_MASTER['DATE_TERMINATED']  	= $DATE_TERMINATED;
		$EMPLOYEE_MASTER['PK_PRE_FIX']  		= $PK_PRE_FIX;
		$EMPLOYEE_MASTER['TURN_OFF_ASSIGNMENTS'] = $TURN_OFF_ASSIGNMENTS;
		
		$EMPLOYEE_CONTACT['ADDRESS'] 	= $ADDRESS;
		$EMPLOYEE_CONTACT['ADDRESS_1'] 	= $ADDRESS_1;
		$EMPLOYEE_CONTACT['CITY'] 		= $CITY;
		$EMPLOYEE_CONTACT['PK_STATES'] 	= $PK_STATES;
		$EMPLOYEE_CONTACT['ZIP'] 		= $ZIP;
		$EMPLOYEE_CONTACT['PK_COUNTRY'] = $PK_COUNTRY;
		$EMPLOYEE_CONTACT['HOME_PHONE'] = $HOME_PHONE;
		$EMPLOYEE_CONTACT['WORK_PHONE'] = $WORK_PHONE;
		$EMPLOYEE_CONTACT['CELL_PHONE'] = $CELL_PHONE;
		
		$EMPLOYEE_MASTER['PK_ACCOUNT']  = $PK_ACCOUNT;
		$EMPLOYEE_MASTER['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER, 'insert');
		$PK_EMPLOYEE_MASTER = $db->insert_ID();
		
		if($SSN != '') {
			$SSN1 = preg_replace( '/[^0-9]/', '',$SSN);
			$SSN1 = $SSN1[0].$SSN1[1].$SSN1[2].'-'.$SSN1[3].$SSN1[4].'-'.$SSN1[5].$SSN1[6].$SSN1[7].$SSN1[8];
				
			$EMPLOYEE_MASTER2['SSN'] = my_encrypt($PK_ACCOUNT.$PK_EMPLOYEE_MASTER,$SSN1);
			db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER2, 'update'," PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		}
		
		$EMPLOYEE_CONTACT['PK_EMPLOYEE_MASTER'] = $PK_EMPLOYEE_MASTER;
		$EMPLOYEE_CONTACT['PK_ACCOUNT']  		= $PK_ACCOUNT;
		$EMPLOYEE_CONTACT['CREATED_ON']  		= date("Y-m-d H:i");
		db_perform('S_EMPLOYEE_CONTACT', $EMPLOYEE_CONTACT, 'insert');
		
		if(!empty($PK_CAMPUS_ARR)){
			foreach($PK_CAMPUS_ARR as $PK_CAMPUS) {
				$EMPLOYEE_CAMPUS['PK_CAMPUS']   		= $PK_CAMPUS;
				$EMPLOYEE_CAMPUS['PK_EMPLOYEE_MASTER'] 	= $PK_EMPLOYEE_MASTER;
				$EMPLOYEE_CAMPUS['PK_ACCOUNT'] 			= $PK_ACCOUNT;
				$EMPLOYEE_CAMPUS['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_EMPLOYEE_CAMPUS', $EMPLOYEE_CAMPUS, 'insert');
			}
		}
		
		if(!empty($PK_DEPARTMENT_ARR)){
			foreach($PK_DEPARTMENT_ARR as $PK_DEPARTMENT) {
				$EMPLOYEE_DEPARTMENT['PK_DEPARTMENT']   	= $PK_DEPARTMENT;
				$EMPLOYEE_DEPARTMENT['PK_EMPLOYEE_MASTER'] 	= $PK_EMPLOYEE_MASTER;
				$EMPLOYEE_DEPARTMENT['PK_ACCOUNT'] 			= $PK_ACCOUNT;
				$EMPLOYEE_DEPARTMENT['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_EMPLOYEE_DEPARTMENT', $EMPLOYEE_DEPARTMENT, 'insert');
			}
		}
		
		if(!empty($PK_RACE_ARR)){
			foreach($PK_RACE_ARR as $PK_RACE) {
				$EMPLOYEE_RACE['PK_RACE']   			= $PK_RACE;
				$EMPLOYEE_RACE['PK_EMPLOYEE_MASTER'] 	= $PK_EMPLOYEE_MASTER;
				$EMPLOYEE_RACE['PK_ACCOUNT'] 			= $PK_ACCOUNT;
				$EMPLOYEE_RACE['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_EMPLOYEE_RACE', $EMPLOYEE_RACE, 'insert');
			}
		}
		
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
		$EMPLOYEE_MASTER3['IPEDS_ETHNICITY']  = $IPEDS_ETHNICITY;
		db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER3, 'update'," PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		
		////////////////
		if(!empty($DATA->NOTES)){
			foreach($DATA->NOTES as $NOTES) {
				
				$EMPLOYEE_NOTES['PK_ACCOUNT']   			= $PK_ACCOUNT;
				$EMPLOYEE_NOTES['PK_EMPLOYEE_MASTER'] 		= $PK_EMPLOYEE_MASTER;
				$EMPLOYEE_NOTES['PK_DEPARTMENT'] 			= $NOTES->DEPARTMENT_ID;
				$EMPLOYEE_NOTES['PK_EMPLOYEE_NOTE_TYPE'] 	= $NOTES->NOTE_TYPE_ID;
				$EMPLOYEE_NOTES['PK_NOTE_STATUS'] 			= $NOTES->NOTE_STATUS_ID;
				$EMPLOYEE_NOTES['NOTES'] 					= $NOTES->NOTES;
				
				$EMPLOYEE_NOTES['FOLLOWUP_DATE'] 			= $NOTES->FOLLOWUP_DATE;
				$EMPLOYEE_NOTES['FOLLOWUP_TIME'] 			= $NOTES->FOLLOWUP_TIME;
				$EMPLOYEE_NOTES['NOTE_DATE'] 				= $NOTES->NOTE_DATE;
				$EMPLOYEE_NOTES['NOTE_TIME'] 				= $NOTES->NOTE_TIME;
				
				// $EMPLOYEE_NOTES['CREATED_BY']  		= $_SESSION['PK_USER'];
				$EMPLOYEE_NOTES['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_EMPLOYEE_NOTES', $EMPLOYEE_NOTES, 'insert');
				
			}
		}
		/////////////////

		if(!empty($DATA->CUSTOM_FIELDS)){
			foreach($DATA->CUSTOM_FIELDS as $CUSTOM_FIELDS) {
				$res_st = $db->Execute("select FIELD_NAME from S_CUSTOM_FIELDS WHERE PK_CUSTOM_FIELDS = '".$CUSTOM_FIELDS->CUSTOM_FIELDS_ID."' AND PK_ACCOUNT = '$PK_ACCOUNT'");
				
				$CUSTOM_FIELDS_ARR['PK_ACCOUNT'] 		 = $PK_ACCOUNT;
				$CUSTOM_FIELDS_ARR['PK_EMPLOYEE_MASTER'] = $PK_EMPLOYEE_MASTER;
				$CUSTOM_FIELDS_ARR['PK_CUSTOM_FIELDS'] 	 = $CUSTOM_FIELDS->CUSTOM_FIELDS_ID;
				$CUSTOM_FIELDS_ARR['FIELD_VALUE'] 		 = $CUSTOM_FIELDS->FIELD_VALUE;
				$CUSTOM_FIELDS_ARR['FIELD_NAME'] 		 = $res_st->fields['FIELD_NAME'];
				$CUSTOM_FIELDS_ARR['CREATED_ON']  		 = date("Y-m-d H:i");
				db_perform('S_EMPLOYEE_CUSTOM_FIELDS', $CUSTOM_FIELDS_ARR, 'insert');
			}
		}
		
		$data['MESSAGE'] = 'Employee Created';
		$data['INTERNAL_ID'] = $PK_EMPLOYEE_MASTER;
		
	}
}

$data = json_encode($data);
echo $data;