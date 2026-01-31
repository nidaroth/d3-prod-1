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
	$query = "SELECT S_COMPANY.PK_COMPANY, S_COMPANY.PK_PLACEMENT_COMPANY_STATUS, S_COMPANY.PK_PLACEMENT_TYPE, OLD_DSIS_COMPANY_ID, S_COMPANY.PK_COMPANY_CONTACT, S_COMPANY.PK_PLACEMENT_COMPANY_QUESTION_GROUP, S_COMPANY.PK_COMPANY_ADVISOR, S_COMPANY.COMPANY_NAME, S_COMPANY.ADDRESS, S_COMPANY.ADDRESS_1, S_COMPANY.CITY, S_COMPANY.PK_STATES, S_COMPANY.ZIP, S_COMPANY.PK_COUNTRY, S_COMPANY.PHONE, S_COMPANY.FAX, S_COMPANY.EMAIL, S_COMPANY.WEBSITE, S_COMPANY.NOTES, S_COMPANY.ACTIVE,
	M_PLACEMENT_COMPANY_STATUS.PLACEMENT_COMPANY_STATUS,
	M_PLACEMENT_TYPE.TYPE AS PLACEMENT_TYPE,
	S_COMPANY_CONTACT.NAME AS CONTACT_NAME,
	M_PLACEMENT_COMPANY_QUESTION_GROUP.PLACEMENT_COMPANY_QUESTION_GROUP,
	CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS COMPANY_ADVISOR 
	FROM S_COMPANY LEFT JOIN M_PLACEMENT_COMPANY_STATUS ON S_COMPANY.PK_PLACEMENT_COMPANY_STATUS=M_PLACEMENT_COMPANY_STATUS.PK_PLACEMENT_COMPANY_STATUS 
	LEFT JOIN M_PLACEMENT_TYPE ON S_COMPANY.PK_PLACEMENT_TYPE=M_PLACEMENT_TYPE.PK_PLACEMENT_TYPE 
	LEFT JOIN S_COMPANY_CONTACT ON S_COMPANY.PK_COMPANY_CONTACT=S_COMPANY_CONTACT.PK_COMPANY_CONTACT
	LEFT JOIN M_PLACEMENT_COMPANY_QUESTION_GROUP ON S_COMPANY.PK_PLACEMENT_COMPANY_QUESTION_GROUP=M_PLACEMENT_COMPANY_QUESTION_GROUP.PK_PLACEMENT_COMPANY_QUESTION_GROUP
	LEFT JOIN S_EMPLOYEE_MASTER ON S_COMPANY.PK_COMPANY_ADVISOR=S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER 
	WHERE S_COMPANY.ACTIVE = 1 AND S_COMPANY.PK_ACCOUNT='$PK_ACCOUNT'";
	// echo $query; exit;
	$res = $db->Execute($query);
	
	// PK_COMPANY='$PK_COMPANY' AND 
	$i = 0;
	while (!$res->EOF) {
		$PK_COMPANY = $res->fields['PK_COMPANY'];
		$data['COMPANY'][$i]['ID']    								= $res->fields['PK_COMPANY'];
		$data['COMPANY'][$i]['OLD_DSIS_COMPANY_ID']    				= $res->fields['OLD_DSIS_COMPANY_ID'];
		$data['COMPANY'][$i]['PLACEMENT_COMPANY_STATUS_ID']  		= $res->fields['PK_PLACEMENT_COMPANY_STATUS'];
		$data['COMPANY'][$i]['PLACEMENT_COMPANY_STATUS']  			= $res->fields['PLACEMENT_COMPANY_STATUS'];
		$data['COMPANY'][$i]['PLACEMENT_TYPE_ID']  					= $res->fields['PK_PLACEMENT_TYPE'];
		$data['COMPANY'][$i]['PLACEMENT_TYPE']  					= $res->fields['PLACEMENT_TYPE'];
		$data['COMPANY'][$i]['COMPANY_CONTACT_ID']  				= $res->fields['PK_COMPANY_CONTACT'];
		$data['COMPANY'][$i]['COMPANY_CONTACT']  					= $res->fields['CONTACT_NAME'];
		$data['COMPANY'][$i]['PLACEMENT_COMPANY_QUESTION_GROUP_ID']	= $res->fields['PK_PLACEMENT_COMPANY_QUESTION_GROUP'];
		$data['COMPANY'][$i]['PLACEMENT_COMPANY_QUESTION_GROUP'] 	= $res->fields['PLACEMENT_COMPANY_QUESTION_GROUP'];
		$data['COMPANY'][$i]['COMPANY_ADVISOR_ID']  				= $res->fields['PK_COMPANY_ADVISOR'];
		$data['COMPANY'][$i]['COMPANY_ADVISOR']  					= $res->fields['COMPANY_ADVISOR'];
		$data['COMPANY'][$i]['COMPANY_NAME']  						= $res->fields['COMPANY_NAME'];
		$data['COMPANY'][$i]['ADDRESS']  							= $res->fields['ADDRESS'];
		$data['COMPANY'][$i]['ADDRESS_1']  							= $res->fields['ADDRESS_1'];
		$data['COMPANY'][$i]['CITY']  								= $res->fields['CITY'];
		$data['COMPANY'][$i]['STATES']  							= $res->fields['PK_STATES'];
		$data['COMPANY'][$i]['ZIP']  								= $res->fields['ZIP'];
		$data['COMPANY'][$i]['COUNTRY']  							= $res->fields['PK_COUNTRY'];
		$data['COMPANY'][$i]['PHONE']  								= $res->fields['PHONE'];
		$data['COMPANY'][$i]['FAX']  								= $res->fields['FAX'];
		$data['COMPANY'][$i]['EMAIL']  								= $res->fields['EMAIL'];
		$data['COMPANY'][$i]['WEBSITE']  							= $res->fields['WEBSITE'];
		$data['COMPANY'][$i]['NOTES']  								= $res->fields['NOTES'];
		$data['COMPANY'][$i]['ACTIVE']  							= $res->fields['ACTIVE'];
		
		$query = "SELECT S_COMPANY_CONTACT.PK_COMPANY_CONTACT, S_COMPANY_CONTACT.PK_PLACEMENT_TYPE, S_COMPANY_CONTACT.NAME, S_COMPANY_CONTACT.TITLE, S_COMPANY_CONTACT.DEPARTMENT, S_COMPANY_CONTACT.COMMENT, S_COMPANY_CONTACT.PHONE, S_COMPANY_CONTACT.OTHER_PHONE, S_COMPANY_CONTACT.MOBILE, S_COMPANY_CONTACT.FAX, S_COMPANY_CONTACT.EMAIL, S_COMPANY_CONTACT.ACTIVE,

		M_PLACEMENT_TYPE.TYPE AS PLACEMENT_TYPE
		
		FROM S_COMPANY_CONTACT LEFT JOIN M_PLACEMENT_TYPE ON S_COMPANY_CONTACT.PK_PLACEMENT_TYPE=M_PLACEMENT_TYPE.PK_PLACEMENT_TYPE WHERE 

		S_COMPANY_CONTACT.ACTIVE = 1 AND S_COMPANY_CONTACT.PK_ACCOUNT='$PK_ACCOUNT' AND S_COMPANY_CONTACT.PK_COMPANY='$PK_COMPANY'";
		// echo $query; exit;
		$res_comp_contact = $db->Execute($query);
			

		$i_inner = 0;
		while (!$res_comp_contact->EOF) {
			$data['COMPANY'][$i]['CONTACT'][$i_inner]['ID'] 				= $res_comp_contact->fields['PK_COMPANY_CONTACT'];
			$data['COMPANY'][$i]['CONTACT'][$i_inner]['PLACEMENT_TYPE_ID'] 	= $res_comp_contact->fields['PK_PLACEMENT_TYPE'];
			$data['COMPANY'][$i]['CONTACT'][$i_inner]['PLACEMENT_TYPE'] 	= $res_comp_contact->fields['PLACEMENT_TYPE'];
			$data['COMPANY'][$i]['CONTACT'][$i_inner]['NAME'] 				= $res_comp_contact->fields['NAME'];
			$data['COMPANY'][$i]['CONTACT'][$i_inner]['TITLE'] 				= $res_comp_contact->fields['TITLE'];
			$data['COMPANY'][$i]['CONTACT'][$i_inner]['DEPARTMENT'] 		= $res_comp_contact->fields['DEPARTMENT'];
			$data['COMPANY'][$i]['CONTACT'][$i_inner]['COMMENT'] 			= $res_comp_contact->fields['COMMENT'];
			$data['COMPANY'][$i]['CONTACT'][$i_inner]['PHONE'] 				= $res_comp_contact->fields['PHONE'];
			$data['COMPANY'][$i]['CONTACT'][$i_inner]['OTHER_PHONE'] 		= $res_comp_contact->fields['OTHER_PHONE'];
			$data['COMPANY'][$i]['CONTACT'][$i_inner]['MOBILE'] 			= $res_comp_contact->fields['MOBILE'];
			$data['COMPANY'][$i]['CONTACT'][$i_inner]['FAX'] 				= $res_comp_contact->fields['FAX'];
			$data['COMPANY'][$i]['CONTACT'][$i_inner]['EMAIL'] 				= $res_comp_contact->fields['EMAIL'];
			$data['COMPANY'][$i]['CONTACT'][$i_inner]['ACTIVE'] 			= $res_comp_contact->fields['ACTIVE'];
			
			$i_inner++;
			$res_comp_contact->MoveNext();
		}
		$query = "SELECT S_COMPANY_JOB.PK_COMPANY_JOB, S_COMPANY_JOB.PK_SOC_CODE, S_COMPANY_JOB.PK_PLACEMENT_TYPE, OLD_DSIS_COMPANY_JOB_ID, S_COMPANY_JOB.PK_COMPANY_CONTACT, S_COMPANY_JOB.PK_ENROLLMENT_STATUS, S_COMPANY_JOB.PK_PAY_TYPE, S_COMPANY_JOB.PK_COMPANY_ADVISOR, S_COMPANY_JOB.JOB_NUMBER, S_COMPANY_JOB.JOB_TITLE, S_COMPANY_JOB.JOB_POSTED, S_COMPANY_JOB.JOB_FILLED, S_COMPANY_JOB.JOB_CANCELED, S_COMPANY_JOB.EMPLOYMENT, S_COMPANY_JOB.BENEFITS, S_COMPANY_JOB.PAY_AMOUNT, S_COMPANY_JOB.WEEKLY_HOURS, S_COMPANY_JOB.ANNUAL_SALARY, S_COMPANY_JOB.JOB_DESCRIPTION, S_COMPANY_JOB.JOB_NOTES, S_COMPANY_JOB.OPEN_JOB, S_COMPANY_JOB.ACTIVE,
		M_SOC_CODE.SOC_CODE,
		M_PLACEMENT_TYPE.TYPE AS PLACEMENT_TYPE,
		S_COMPANY_CONTACT.NAME AS CONTACT_NAME,
		M_PAY_TYPE.PAY_TYPE,
		CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS COMPANY_ADVISOR 
		FROM S_COMPANY_JOB LEFT JOIN M_SOC_CODE ON S_COMPANY_JOB.PK_SOC_CODE=M_SOC_CODE.PK_SOC_CODE LEFT JOIN M_PLACEMENT_TYPE ON S_COMPANY_JOB.PK_PLACEMENT_TYPE=M_PLACEMENT_TYPE.PK_PLACEMENT_TYPE LEFT JOIN S_COMPANY_CONTACT ON S_COMPANY_JOB.PK_COMPANY_CONTACT=S_COMPANY_CONTACT.PK_COMPANY_CONTACT LEFT JOIN M_PAY_TYPE ON S_COMPANY_JOB.PK_PAY_TYPE=M_PAY_TYPE.PK_PAY_TYPE LEFT JOIN S_EMPLOYEE_MASTER ON  S_COMPANY_JOB.PK_COMPANY_ADVISOR=S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER WHERE S_COMPANY_JOB.ACTIVE = 1 AND S_COMPANY_JOB.PK_ACCOUNT='$PK_ACCOUNT' AND S_COMPANY_JOB.PK_COMPANY='$PK_COMPANY'";
		// echo $query; exit;

		$res_comp_contact = $db->Execute($query);
		
		$i_inner = 0;
		while (!$res_comp_contact->EOF) {
			$data['COMPANY'][$i]['JOB'][$i_inner]['ID'] 					= $res_comp_contact->fields['PK_COMPANY_JOB'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['OLD_DSIS_COMPANY_JOB_ID']= $res_comp_contact->fields['OLD_DSIS_COMPANY_JOB_ID'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['SOC_CODE_ID'] 			= $res_comp_contact->fields['PK_SOC_CODE'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['SOC_CODE'] 				= $res_comp_contact->fields['SOC_CODE'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['PLACEMENT_TYPE_ID'] 		= $res_comp_contact->fields['PK_PLACEMENT_TYPE'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['PLACEMENT_TYPE'] 		= $res_comp_contact->fields['PLACEMENT_TYPE'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['COMPANY_CONTACT_ID'] 	= $res_comp_contact->fields['PK_COMPANY_CONTACT'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['COMPANY_CONTACT'] 		= $res_comp_contact->fields['CONTACT_NAME'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['ENROLLMENT_STATUS_ID'] 	= $res_comp_contact->fields['PK_ENROLLMENT_STATUS'];
			if($res_comp_contact->fields['PK_ENROLLMENT_STATUS'] == 1) {
				$data['COMPANY'][$i]['JOB'][$i_inner]['ENROLLMENT_STATUS'] = "Full Time";
			}
			else if($res_comp_contact->fields['PK_ENROLLMENT_STATUS'] == 2) {
				$data['COMPANY'][$i]['JOB'][$i_inner]['ENROLLMENT_STATUS'] = "Part Time";
			}
			else {
				$data['COMPANY'][$i]['JOB'][$i_inner]['ENROLLMENT_STATUS'] = "";
			}
			$data['COMPANY'][$i]['JOB'][$i_inner]['PAY_TYPE_ID'] 			= $res_comp_contact->fields['PK_PAY_TYPE'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['PAY_TYPE'] 				= $res_comp_contact->fields['PAY_TYPE'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['COMPANY_ADVISOR_ID'] 	= $res_comp_contact->fields['PK_COMPANY_ADVISOR'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['COMPANY_ADVISOR'] 		= $res_comp_contact->fields['COMPANY_ADVISOR'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['JOB_NUMBER'] 			= $res_comp_contact->fields['JOB_NUMBER'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['JOB_TITLE'] 				= $res_comp_contact->fields['JOB_TITLE'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['JOB_POSTED'] 			= $res_comp_contact->fields['JOB_POSTED'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['JOB_FILLED'] 			= $res_comp_contact->fields['JOB_FILLED'];

			$data['COMPANY'][$i]['JOB'][$i_inner]['JOB_CANCELED'] 			= $res_comp_contact->fields['JOB_CANCELED'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['EMPLOYMENT'] 			= $res_comp_contact->fields['EMPLOYMENT'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['BENEFITS'] 				= $res_comp_contact->fields['BENEFITS'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['PAY_AMOUNT'] 			= $res_comp_contact->fields['PAY_AMOUNT'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['WEEKLY_HOURS'] 			= $res_comp_contact->fields['WEEKLY_HOURS'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['ANNUAL_SALARY'] 			= $res_comp_contact->fields['ANNUAL_SALARY'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['JOB_DESCRIPTION'] 		= $res_comp_contact->fields['JOB_DESCRIPTION'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['JOB_NOTES'] 				= $res_comp_contact->fields['JOB_NOTES'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['OPEN_JOB'] 				= $res_comp_contact->fields['OPEN_JOB'];
			$data['COMPANY'][$i]['JOB'][$i_inner]['ACTIVE'] 				= $res_comp_contact->fields['ACTIVE'];
			
			$i_inner++;
			$res_comp_contact->MoveNext();
		}
		
		$query = "SELECT S_COMPANY_EVENT.PK_COMPANY_EVENT, S_COMPANY_EVENT.PK_PLACEMENT_COMPANY_EVENT_TYPE, S_COMPANY_EVENT.EVENT_DATE, S_COMPANY_EVENT.FOLLOW_UP_DATE, S_COMPANY_EVENT.PK_COMPANY_CONTACT_EMPLOYEE, S_COMPANY_EVENT.PK_COMPANY_CONTACT, S_COMPANY_EVENT.COMPLETE, S_COMPANY_EVENT.NOTE, S_COMPANY_EVENT.ACTIVE,
		M_PLACEMENT_COMPANY_EVENT_TYPE.PLACEMENT_COMPANY_EVENT_TYPE,
		CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS COMPANY_CONTACT_EMPLOYEE,
		S_COMPANY_CONTACT.NAME AS CONTACT_NAME 
		FROM S_COMPANY_EVENT LEFT JOIN M_PLACEMENT_COMPANY_EVENT_TYPE ON S_COMPANY_EVENT.PK_PLACEMENT_COMPANY_EVENT_TYPE=M_PLACEMENT_COMPANY_EVENT_TYPE.PK_PLACEMENT_COMPANY_EVENT_TYPE LEFT JOIN S_EMPLOYEE_MASTER ON S_COMPANY_EVENT.PK_COMPANY_CONTACT_EMPLOYEE=S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER LEFT JOIN S_COMPANY_CONTACT ON S_COMPANY_EVENT.PK_COMPANY_CONTACT=S_COMPANY_CONTACT.PK_COMPANY_CONTACT WHERE 
		S_COMPANY_EVENT.ACTIVE = 1 AND S_COMPANY_EVENT.PK_ACCOUNT='$PK_ACCOUNT' AND S_COMPANY_EVENT.PK_COMPANY='$PK_COMPANY'";
		// echo $query; exit;
		$res_comp_contact = $db->Execute($query);
		$i_inner = 0;
		while (!$res_comp_contact->EOF) {
			$data['COMPANY'][$i]['EVENT'][$i_inner]['ID'] 								= $res_comp_contact->fields['PK_COMPANY_EVENT'];
			$data['COMPANY'][$i]['EVENT'][$i_inner]['PLACEMENT_COMPANY_EVENT_TYPE_ID'] 	= $res_comp_contact->fields['PK_PLACEMENT_COMPANY_EVENT_TYPE'];
			$data['COMPANY'][$i]['EVENT'][$i_inner]['PLACEMENT_COMPANY_EVENT_TYPE'] = $res_comp_contact->fields['PLACEMENT_COMPANY_EVENT_TYPE'];
			$data['COMPANY'][$i]['EVENT'][$i_inner]['EVENT_DATE'] 					= $res_comp_contact->fields['EVENT_DATE'];
			$data['COMPANY'][$i]['EVENT'][$i_inner]['FOLLOW_UP_DATE'] 				= $res_comp_contact->fields['FOLLOW_UP_DATE'];
			$data['COMPANY'][$i]['EVENT'][$i_inner]['COMPANY_CONTACT_EMPLOYEE_ID'] 	= $res_comp_contact->fields['PK_COMPANY_CONTACT_EMPLOYEE'];
			$data['COMPANY'][$i]['EVENT'][$i_inner]['COMPANY_CONTACT_EMPLOYEE'] 	= $res_comp_contact->fields['COMPANY_CONTACT_EMPLOYEE'];
			$data['COMPANY'][$i]['EVENT'][$i_inner]['COMPANY_CONTACT_ID'] 				= $res_comp_contact->fields['PK_COMPANY_CONTACT'];
			$data['COMPANY'][$i]['EVENT'][$i_inner]['COMPANY_CONTACT'] 				= $res_comp_contact->fields['CONTACT_NAME'];
			$data['COMPANY'][$i]['EVENT'][$i_inner]['COMPLETE'] 					= $res_comp_contact->fields['COMPLETE'];
			$data['COMPANY'][$i]['EVENT'][$i_inner]['NOTE'] 						= $res_comp_contact->fields['NOTE'];
			$data['COMPANY'][$i]['EVENT'][$i_inner]['ACTIVE'] 						= $res_comp_contact->fields['ACTIVE'];
			
			$i_inner++;
			$res_comp_contact->MoveNext();
		}

		$query = "SELECT S_COMPANY_QUESTIONNAIRE.PK_COMPANY_QUESTIONNAIRE, S_COMPANY_QUESTIONNAIRE.PK_PLACEMENT_COMPANY_QUESTIONNAIRE, S_COMPANY_QUESTIONNAIRE.ANSWER, S_COMPANY_QUESTIONNAIRE.ACTIVE,
		M_PLACEMENT_COMPANY_QUESTIONNAIRE.QUESTIONS
		FROM S_COMPANY_QUESTIONNAIRE LEFT JOIN M_PLACEMENT_COMPANY_QUESTIONNAIRE ON S_COMPANY_QUESTIONNAIRE.PK_PLACEMENT_COMPANY_QUESTIONNAIRE=M_PLACEMENT_COMPANY_QUESTIONNAIRE.PK_PLACEMENT_COMPANY_QUESTIONNAIRE WHERE S_COMPANY_QUESTIONNAIRE.ACTIVE = 1 AND S_COMPANY_QUESTIONNAIRE.PK_ACCOUNT='$PK_ACCOUNT' AND S_COMPANY_QUESTIONNAIRE.PK_COMPANY='$PK_COMPANY'";
		
		$res_comp_contact = $db->Execute($query);
		
		$i_inner = 0;
		while (!$res_comp_contact->EOF) {
			$data['COMPANY'][$i]['QUESTIONNAIRE'][$i_inner]['ID'] 								= $res_comp_contact->fields['PK_COMPANY_QUESTIONNAIRE'];
			$data['COMPANY'][$i]['QUESTIONNAIRE'][$i_inner]['PLACEMENT_COMPANY_QUESTIONNAIRE_ID'] 	= $res_comp_contact->fields['PK_PLACEMENT_COMPANY_QUESTIONNAIRE'];
			$data['COMPANY'][$i]['QUESTIONNAIRE'][$i_inner]['PLACEMENT_COMPANY_QUESTIONNAIRE'] 	= $res_comp_contact->fields['QUESTIONS'];
			$data['COMPANY'][$i]['QUESTIONNAIRE'][$i_inner]['ANSWER'] 							= $res_comp_contact->fields['ANSWER'];
			$data['COMPANY'][$i]['QUESTIONNAIRE'][$i_inner]['ACTIVE'] 							= $res_comp_contact->fields['ACTIVE'];
			
			$i_inner++;
			$res_comp_contact->MoveNext();
		}

		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;