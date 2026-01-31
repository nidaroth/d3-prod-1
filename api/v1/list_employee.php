<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

$API_KEY = '';
foreach (getallheaders() as $name => $value) {
    //echo "$name: $value<br />";
	if(strtolower(trim($name)) == 'apikey')
		$API_KEY = trim($value);
}

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

	$res = $db->Execute("SELECT PK_EMPLOYEE_MASTER,FIRST_NAME, LAST_NAME, MIDDLE_NAME, SSN, EMPLOYEE_ID, TITLE, EMAIL, EMAIL_OTHER, DOB, GENDER, S_EMPLOYEE_MASTER.PK_MARITAL_STATUS, MARITAL_STATUS, IPEDS_ETHNICITY, NETWORK_ID, COMPANY_EMP_ID, SUPERVISOR, FULL_PART_TIME, ELIGIBLE_FOR_REHIRE, S_EMPLOYEE_MASTER.PK_SOC_CODE, SOC_CODE, DATE_HIRED, DATE_TERMINATED, S_EMPLOYEE_MASTER.ACTIVE, LOGIN_CREATED, TURN_OFF_ASSIGNMENTS, IS_FACULTY, S_EMPLOYEE_MASTER.PK_PRE_FIX, PRE_FIX FROM S_EMPLOYEE_MASTER LEFT JOIN Z_MARITAL_STATUS ON Z_MARITAL_STATUS.PK_MARITAL_STATUS = S_EMPLOYEE_MASTER.PK_MARITAL_STATUS LEFT JOIN M_SOC_CODE ON M_SOC_CODE.PK_SOC_CODE = S_EMPLOYEE_MASTER.PK_SOC_CODE LEFT JOIN Z_PRE_FIX ON Z_PRE_FIX.PK_PRE_FIX = S_EMPLOYEE_MASTER.PK_PRE_FIX WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$PK_ACCOUNT' ");
	$i = 0;
	while (!$res->EOF) { 
		$PK_EMPLOYEE_MASTER = $res->fields['PK_EMPLOYEE_MASTER'];
		$data['EMPLOYEE'][$i]['ID'] 					= $PK_EMPLOYEE_MASTER;
		$data['EMPLOYEE'][$i]['PREFIX_ID'] 				= $res->fields['PK_PRE_FIX'];
		$data['EMPLOYEE'][$i]['PREFIX'] 				= $res->fields['PRE_FIX'];
		$data['EMPLOYEE'][$i]['FIRST_NAME'] 			= $res->fields['FIRST_NAME'];
		$data['EMPLOYEE'][$i]['LAST_NAME'] 				= $res->fields['LAST_NAME'];
		$data['EMPLOYEE'][$i]['MIDDLE_NAME'] 			= $res->fields['MIDDLE_NAME'];
		$data['EMPLOYEE'][$i]['SSN'] 					= $res->fields['SSN'];
		$data['EMPLOYEE'][$i]['EMPLOYEE_ID'] 			= $res->fields['EMPLOYEE_ID'];
		$data['EMPLOYEE'][$i]['TITLE'] 					= $res->fields['TITLE'];
		$data['EMPLOYEE'][$i]['EMAIL'] 					= $res->fields['EMAIL'];
		$data['EMPLOYEE'][$i]['EMAIL_OTHER'] 			= $res->fields['EMAIL_OTHER'];
		$data['EMPLOYEE'][$i]['DOB'] 					= $res->fields['DOB'];
		$data['EMPLOYEE'][$i]['GENDER_ID'] 				= $res->fields['GENDER'];
		$data['EMPLOYEE'][$i]['MARITAL_STATUS_ID'] 		= $res->fields['PK_MARITAL_STATUS'];
		$data['EMPLOYEE'][$i]['MARITAL_STATUS'] 		= $res->fields['MARITAL_STATUS'];
		$data['EMPLOYEE'][$i]['IPEDS_ETHNICITY'] 		= $res->fields['IPEDS_ETHNICITY'];
		$data['EMPLOYEE'][$i]['NETWORK_ID'] 			= $res->fields['NETWORK_ID'];
		$data['EMPLOYEE'][$i]['COMPANY_EMP_ID'] 		= $res->fields['COMPANY_EMP_ID'];
		$data['EMPLOYEE'][$i]['SUPERVISOR'] 			= $res->fields['SUPERVISOR'];
		$data['EMPLOYEE'][$i]['FULL_PART_TIME_ID'] 		= $res->fields['FULL_PART_TIME'];
		$data['EMPLOYEE'][$i]['ELIGIBLE_FOR_REHIRE'] 	= $res->fields['ELIGIBLE_FOR_REHIRE'];
		$data['EMPLOYEE'][$i]['SOC_CODE_ID'] 			= $res->fields['PK_SOC_CODE'];
		$data['EMPLOYEE'][$i]['SOC_CODE'] 				= $res->fields['SOC_CODE'];
		$data['EMPLOYEE'][$i]['DATE_HIRED'] 			= $res->fields['DATE_HIRED'];
		$data['EMPLOYEE'][$i]['DATE_TERMINATED'] 		= $res->fields['DATE_TERMINATED'];
		$data['EMPLOYEE'][$i]['ACTIVE'] 				= $res->fields['ACTIVE'];
		$data['EMPLOYEE'][$i]['LOGIN_CREATED'] 			= $res->fields['LOGIN_CREATED'];
		$data['EMPLOYEE'][$i]['TURN_OFF_ASSIGNMENTS'] 	= $res->fields['TURN_OFF_ASSIGNMENTS'];
		$data['EMPLOYEE'][$i]['IS_FACULTY'] 			= $res->fields['IS_FACULTY'];
		
		$res_det = $db->Execute("SELECT ADDRESS, ADDRESS_1, CITY, S_EMPLOYEE_CONTACT.PK_STATES, STATE_CODE, ZIP, S_EMPLOYEE_CONTACT.PK_COUNTRY, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE FROM S_EMPLOYEE_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_EMPLOYEE_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY ON Z_COUNTRY.PK_COUNTRY = S_EMPLOYEE_CONTACT.PK_COUNTRY WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' "); 
		$data['EMPLOYEE'][$i]['ADDRESS']				= $res_det->fields['ADDRESS'];
		$data['EMPLOYEE'][$i]['ADDRESS_1']				= $res_det->fields['ADDRESS_1'];
		$data['EMPLOYEE'][$i]['CITY']					= $res_det->fields['CITY'];
		$data['EMPLOYEE'][$i]['STATE_ID']				= $res_det->fields['PK_STATES'];
		$data['EMPLOYEE'][$i]['STATE_CODE']				= $res_det->fields['STATE_CODE'];
		$data['EMPLOYEE'][$i]['ZIP']					= $res_det->fields['ZIP'];
		$data['EMPLOYEE'][$i]['COUNTRY_ID']				= $res_det->fields['PK_COUNTRY'];
		$data['EMPLOYEE'][$i]['COUNTRY']				= $res_det->fields['COUNTRY'];
		$data['EMPLOYEE'][$i]['HOME_PHONE']				= $res_det->fields['HOME_PHONE'];
		$data['EMPLOYEE'][$i]['WORK_PHONE']				= $res_det->fields['WORK_PHONE'];
		$data['EMPLOYEE'][$i]['CELL_PHONE']				= $res_det->fields['CELL_PHONE'];
		
		$j = 0;
		$res_det = $db->Execute("select PK_EMPLOYEE_CAMPUS,OFFICIAL_CAMPUS_NAME,S_CAMPUS.PK_CAMPUS FROM S_CAMPUS,S_EMPLOYEE_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = S_EMPLOYEE_CAMPUS.PK_CAMPUS AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND S_EMPLOYEE_CAMPUS.PK_ACCOUNT = '$PK_ACCOUNT'"); 
		while (!$res_det->EOF) { 
			$data['EMPLOYEE'][$i]['CAMPUS'][$j]['NAME']					= $res_det->fields['OFFICIAL_CAMPUS_NAME'];
			$data['EMPLOYEE'][$i]['CAMPUS'][$j]['CAMPUS_ID']			= $res_det->fields['PK_CAMPUS'];
			//$data['EMPLOYEE'][$i]['CAMPUS'][$j]['EMPLOYEE_CAMPUS_ID']	= $res_det->fields['PK_EMPLOYEE_CAMPUS'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$j = 0;
		$res_det = $db->Execute("select PK_EMPLOYEE_DEPARTMENT,DEPARTMENT,M_DEPARTMENT.PK_DEPARTMENT FROM M_DEPARTMENT,S_EMPLOYEE_DEPARTMENT WHERE M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND S_EMPLOYEE_DEPARTMENT.PK_ACCOUNT = '$PK_ACCOUNT'"); 
		while (!$res_det->EOF) { 
			$data['EMPLOYEE'][$i]['DEPARTMENT'][$j]['NAME']						= $res_det->fields['DEPARTMENT'];
			$data['EMPLOYEE'][$i]['DEPARTMENT'][$j]['DEPARTMENT_ID']			= $res_det->fields['PK_DEPARTMENT'];
			//$data['EMPLOYEE'][$i]['CAMPUS'][$j]['EMPLOYEE_DEPARTMENT_ID']	= $res_det->fields['PK_EMPLOYEE_DEPARTMENT'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$j = 0;
		$res_det = $db->Execute("select PK_EMPLOYEE_RACE,RACE,Z_RACE.PK_RACE FROM Z_RACE,S_EMPLOYEE_RACE WHERE Z_RACE.PK_RACE = S_EMPLOYEE_RACE.PK_RACE AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND S_EMPLOYEE_RACE.PK_ACCOUNT = '$PK_ACCOUNT'"); 
		while (!$res_det->EOF) { 
			$data['EMPLOYEE'][$i]['RACE'][$j]['NAME']		= $res_det->fields['RACE'];
			$data['EMPLOYEE'][$i]['RACE'][$j]['RACE_ID']	= $res_det->fields['PK_RACE'];
			//$data['EMPLOYEE'][$i]['CAMPUS'][$j]['EMPLOYEE_DEPARTMENT_ID']	= $res_det->fields['PK_EMPLOYEE_RACE'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		$j = 0;
		$res_det = $db->Execute("select S_EMPLOYEE_NOTES.PK_EMPLOYEE_NOTES,NOTE_DATE,NOTE_TIME,FOLLOWUP_DATE, FOLLOWUP_TIME, DATE_FORMAT(S_EMPLOYEE_NOTES.CREATED_ON,'%m/%d/%Y<br />%r') AS CREATED_ON, NOTES, EMPLOYEE_NOTE_TYPE, CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME, NOTE_STATUS FROM 
		S_EMPLOYEE_NOTES 
		LEFT JOIN Z_USER ON Z_USER.PK_USER = S_EMPLOYEE_NOTES.CREATED_BY  
		LEFT JOIN S_EMPLOYEE_MASTER ON Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE = 2 
		LEFT JOIN M_NOTE_STATUS ON M_NOTE_STATUS.PK_NOTE_STATUS = S_EMPLOYEE_NOTES.PK_NOTE_STATUS
		, M_EMPLOYEE_NOTE_TYPE 
		
		WHERE 
		S_EMPLOYEE_NOTES.PK_EMPLOYEE_NOTE_TYPE = M_EMPLOYEE_NOTE_TYPE.PK_EMPLOYEE_NOTE_TYPE 
		AND S_EMPLOYEE_NOTES.PK_ACCOUNT = '$PK_ACCOUNT' 
		AND S_EMPLOYEE_NOTES.PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER'  ORDER BY S_EMPLOYEE_NOTES.CREATED_ON DESC"); 
		while (!$res_det->EOF) { 
			$data['EMPLOYEE'][$i]['NOTES'][$j]['NOTE_TYPE']		= $res_det->fields['EMPLOYEE_NOTE_TYPE'];
			$data['EMPLOYEE'][$i]['NOTES'][$j]['NOTE_STATUS']	= $res_det->fields['NOTE_STATUS'];
			$data['EMPLOYEE'][$i]['NOTES'][$j]['NOTE_DATE']		= $res_det->fields['NOTE_DATE'];
			$data['EMPLOYEE'][$i]['NOTES'][$j]['NOTE_TIME']		= $res_det->fields['NOTE_TIME'];
			$data['EMPLOYEE'][$i]['NOTES'][$j]['FOLLOWUP_DATE']	= $res_det->fields['FOLLOWUP_DATE'];
			$data['EMPLOYEE'][$i]['NOTES'][$j]['FOLLOWUP_TIME']	= $res_det->fields['FOLLOWUP_TIME'];
			$data['EMPLOYEE'][$i]['NOTES'][$j]['NOTES']			= $res_det->fields['NOTES'];
			
			$j++;
			$res_det->MoveNext();
		}
		
		if($res->fields['GENDER_ID'] == 1)
			$data['EMPLOYEE'][$i]['GENDER'] = "Male";
		else if($res->fields['GENDER_ID'] == 2)
			$data['EMPLOYEE'][$i]['GENDER'] = "Female";
		else 
			$data['EMPLOYEE'][$i]['GENDER'] = "";
			
		if($res->fields['FULL_PART_TIME_ID'] == 1)
			$data['EMPLOYEE'][$i]['FULL_PART_TIME'] = "Full Time";
		else if($res->fields['FULL_PART_TIME_ID'] == 2)
			$data['EMPLOYEE'][$i]['FULL_PART_TIME'] = "Part Time";
		else 
			$data['EMPLOYEE'][$i]['FULL_PART_TIME'] = "";
			
		if($res->fields['ELIGIBLE_FOR_REHIRE'] == 1)
			$data['EMPLOYEE'][$i]['ELIGIBLE_FOR_REHIRE'] = "Yes";
		else if($res->fields['ELIGIBLE_FOR_REHIRE'] == 2)
			$data['EMPLOYEE'][$i]['ELIGIBLE_FOR_REHIRE'] = "No";
		else
			$data['EMPLOYEE'][$i]['ELIGIBLE_FOR_REHIRE'] = "";
			
		if($res->fields['ACTIVE'] == 1)
			$data['EMPLOYEE'][$i]['ACTIVE'] = "Yes";
		else 
			$data['EMPLOYEE'][$i]['ACTIVE'] = "No";
			
		if($res->fields['LOGIN_CREATED'] == 1)
			$data['EMPLOYEE'][$i]['LOGIN_CREATED'] = "Yes";
		else 
			$data['EMPLOYEE'][$i]['LOGIN_CREATED'] = "No";
			
		if($res->fields['TURN_OFF_ASSIGNMENTS'] == 1)
			$data['EMPLOYEE'][$i]['TURN_OFF_ASSIGNMENTS'] = "Yes";
		else 
			$data['EMPLOYEE'][$i]['TURN_OFF_ASSIGNMENTS'] = "No";
			
		if($res->fields['IS_FACULTY'] == 1)
			$data['EMPLOYEE'][$i]['IS_FACULTY'] = "Yes";
		else 
			$data['EMPLOYEE'][$i]['IS_FACULTY'] = "No";
			
		$j = 0;
		$res_type = $db->Execute("select PK_CUSTOM_FIELDS,FIELD_NAME,PK_DATA_TYPES, PK_USER_DEFINED_FIELDS from S_CUSTOM_FIELDS WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND SECTION = 2"); 
		while (!$res_type->EOF) { 
			$PK_CUSTOM_FIELDS 		= $res_type->fields['PK_CUSTOM_FIELDS'];
			$PK_USER_DEFINED_FIELDS = $res_type->fields['PK_USER_DEFINED_FIELDS'];
			
			$res_1 = $db->Execute("select FIELD_VALUE from S_EMPLOYEE_CUSTOM_FIELDS WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' ");
			
			$FIELD_VALUE = '';
			if($res_type->fields['PK_DATA_TYPES'] == 1 || $res_type->fields['PK_DATA_TYPES'] == 4)
				$FIELD_VALUE = $res_1->fields['FIELD_VALUE'];
			else if($res_type->fields['PK_DATA_TYPES'] == 2 || $res_type->fields['PK_DATA_TYPES'] == 3) {
				$OPTIONS = explode(",",$res_1->fields['FIELD_VALUE']);
				$res_dd = $db->Execute("select OPTION_NAME from S_USER_DEFINED_FIELDS_DETAIL WHERE ACTIVE = '1' AND PK_USER_DEFINED_FIELDS_DETAIL IN (".$res_1->fields['FIELD_VALUE'].") ");
				while (!$res_dd->EOF) { 
					if($FIELD_VALUE != '')
						$FIELD_VALUE .= ', ';
					$FIELD_VALUE .= $res_dd->fields['OPTION_NAME'];
					
					$res_dd->MoveNext();
				}
			}
			
			$data['EMPLOYEE'][$i]['CUSTOM_FIELDS'][$j]['NAME']	= $res_type->fields['FIELD_NAME'];
			$data['EMPLOYEE'][$i]['CUSTOM_FIELDS'][$j]['VALUE']	= $FIELD_VALUE;
			
			$j++;
			$res_type->MoveNext();
		}
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;