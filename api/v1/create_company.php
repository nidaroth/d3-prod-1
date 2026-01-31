<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"COMPANY_NAME":"Type 1", "DESCRIPTION": "Description"}';

$DATA = urldecode($DATA);
$DATA = json_decode($DATA);

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
	
	$PK_PLACEMENT_TYPE 						= trim($DATA->PLACEMENT_TYPE);
	$PK_PLACEMENT_COMPANY_STATUS 			= trim($DATA->PLACEMENT_COMPANY_STATUS);
	$PK_PLACEMENT_COMPANY_QUESTION_GROUP 	= trim($DATA->PLACEMENT_COMPANY_QUESTION_GROUP);
	$PK_STATES 								= trim($DATA->STATE);
	$PK_COUNTRY 							= trim($DATA->COUNTRY);
	$PK_COMPANY_ADVISOR 					= trim($DATA->COMPANY_ADVISOR);

	if(trim($DATA->COMPANY_NAME) == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
		$data['MESSAGE'] .= 'Missing COMPANY value';
	}

	if($PK_PLACEMENT_TYPE != "") {
		$res = $db->Execute("SELECT * FROM M_PLACEMENT_TYPE where PK_PLACEMENT_TYPE = '$PK_PLACEMENT_TYPE' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Invalid PLACEMENT_TYPE value';
		}
	}
	if($PK_PLACEMENT_COMPANY_STATUS != "") {
		$res = $db->Execute("SELECT * FROM M_PLACEMENT_COMPANY_STATUS where PK_PLACEMENT_COMPANY_STATUS = '$PK_PLACEMENT_COMPANY_STATUS' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Invalid PLACEMENT_COMPANY_STATUS value';
		}
	}
	if($PK_PLACEMENT_COMPANY_QUESTION_GROUP != "") {
		$res = $db->Execute("SELECT * FROM M_PLACEMENT_COMPANY_QUESTION_GROUP where PK_PLACEMENT_COMPANY_QUESTION_GROUP = '$PK_PLACEMENT_COMPANY_QUESTION_GROUP' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Invalid PLACEMENT_COMPANY_QUESTION_GROUP value';
		}
	}
	if($PK_STATES != "") {
		$res = $db->Execute("SELECT * FROM Z_STATES where PK_STATES = '$PK_STATES'");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Invalid STATE value';
		}
	}
	if($PK_COUNTRY != "") {
		$res = $db->Execute("SELECT * FROM Z_COUNTRY where PK_COUNTRY = '$PK_COUNTRY'");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Invalid COUNTRY value';
		}
	}
	if($PK_COMPANY_ADVISOR != "") {
		$res = $db->Execute("SELECT * FROM S_EMPLOYEE_MASTER where PK_EMPLOYEE_MASTER = '$PK_COMPANY_ADVISOR' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Invalid COMPANY_ADVISOR value';
		}
	}

	foreach ($DATA->CONTACTS as $CONTACT_ITEM) {
		$PK_PLACEMENT_TYPE 					= $CONTACT_ITEM->PLACEMENT_TYPE;

		if($PK_PLACEMENT_TYPE != "") {
			$res = $db->Execute("SELECT * FROM M_PLACEMENT_TYPE where PK_PLACEMENT_TYPE = '$PK_PLACEMENT_TYPE' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			if($res->RecordCount() == 0) {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
				$data['MESSAGE'] .= 'Invalid PLACEMENT_TYPE value';
			}
		}

		$IS_MAIN_CONTACT = $CONTACT_ITEM->IS_MAIN_CONTACT;
		
		if(trim(strtolower($IS_MAIN_CONTACT)) != 'yes' && trim(strtolower($IS_MAIN_CONTACT)) != 'no' && strtolower($IS_MAIN_CONTACT) != '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Invalid CONTACTS->IS_MAIN_CONTACT Value - '.$IS_MAIN_CONTACT;
		}
	}

	foreach ($DATA->JOBS as $JOB_ITEM) {
		$JOB['PK_SOC_CODE'] 							= $JOB_ITEM->SOC_CODE;
		$JOB['PK_PLACEMENT_TYPE'] 						= $JOB_ITEM->PLACEMENT_TYPE;
		$JOB['PK_PAY_TYPE'] 							= $JOB_ITEM->PAY_TYPE;
		$JOB['PK_COMPANY_ADVISOR'] 						= $JOB_ITEM->COMPANY_ADVISOR;

		$OPEN_JOB = $JOB_ITEM->OPEN_JOB;
		if(trim(strtolower($OPEN_JOB)) != 'yes' && trim(strtolower($OPEN_JOB)) != 'no' && strtolower($OPEN_JOB) != '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Invalid JOBS->OPEN_JOB Value - '.$OPEN_JOB;
		}
		$IS_PERMANENT_EMPLOYMENT = $JOB_ITEM->IS_PERMANENT_EMPLOYMENT;
		if(trim(strtolower($IS_PERMANENT_EMPLOYMENT)) != 'yes' && trim(strtolower($IS_PERMANENT_EMPLOYMENT)) != 'no' && strtolower($IS_PERMANENT_EMPLOYMENT) != '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Invalid JOBS->IS_PERMANENT_EMPLOYMENT Value - '.$IS_PERMANENT_EMPLOYMENT;
		}
		$IS_FULLTIME_ENROLLMENT = $JOB_ITEM->IS_FULLTIME_ENROLLMENT;
		if(trim(strtolower($IS_FULLTIME_ENROLLMENT)) != 'yes' && trim(strtolower($IS_FULLTIME_ENROLLMENT)) != 'no' && strtolower($IS_FULLTIME_ENROLLMENT) != '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Invalid JOBS->IS_FULLTIME_ENROLLMENT Value - '.$IS_FULLTIME_ENROLLMENT;
		}
		$BENEFITS = $JOB_ITEM->BENEFITS;
		if(trim(strtolower($BENEFITS)) != 'yes' && trim(strtolower($BENEFITS)) != 'no' && strtolower($BENEFITS) != '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Invalid JOBS->BENEFITS Value - '.$BENEFITS;
		}

		if($JOB['PK_SOC_CODE'] != "") {
			$res = $db->Execute("SELECT * FROM M_SOC_CODE where PK_SOC_CODE = '$JOB[PK_SOC_CODE]' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			if($res->RecordCount() == 0) {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
				$data['MESSAGE'] .= 'Invalid JOB->SOC_CODE value - '.$JOB['PK_SOC_CODE'];
			}
		}
		if($JOB['PK_PLACEMENT_TYPE'] != "") {
			$res = $db->Execute("SELECT * FROM M_PLACEMENT_TYPE where PK_PLACEMENT_TYPE = '$JOB[PK_PLACEMENT_TYPE]' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			if($res->RecordCount() == 0) {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
				$data['MESSAGE'] .= 'Invalid JOB->PLACEMENT_TYPE value - '.$JOB['PK_PLACEMENT_TYPE'];
			}
		}
		if($JOB['PK_PAY_TYPE'] != "") {
			$res = $db->Execute("SELECT * FROM M_PAY_TYPE where PK_PAY_TYPE = '$JOB[PK_PAY_TYPE]'");
			if($res->RecordCount() == 0) {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
				$data['MESSAGE'] .= 'Invalid JOB->PAY_TYPE value - '.$JOB['PK_PAY_TYPE'];
			}
		}
		if($JOB['PK_COMPANY_ADVISOR'] != "") {
			$res = $db->Execute("SELECT * FROM S_EMPLOYEE_MASTER where PK_EMPLOYEE_MASTER = '$JOB[PK_COMPANY_ADVISOR]' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			if($res->RecordCount() == 0) {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
				$data['MESSAGE'] .= 'Invalid JOB->COMPANY_ADVISOR value - '.$JOB['PK_COMPANY_ADVISOR'];
			}
		}
		if( isset($JOB_ITEM->CONTACT) ) {
			$CONTACT = $JOB_ITEM->CONTACT;
			$JOB_CONTACT['PK_PLACEMENT_TYPE'] 			= $CONTACT->PLACEMENT_TYPE;

			if($JOB_CONTACT['PK_PLACEMENT_TYPE'] != "") {
				$res = $db->Execute("SELECT * FROM M_PLACEMENT_TYPE where PK_PLACEMENT_TYPE = '$JOB_CONTACT[PK_PLACEMENT_TYPE]' AND PK_ACCOUNT = '$PK_ACCOUNT'");
				if($res->RecordCount() == 0) {
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
					$data['MESSAGE'] .= 'Invalid JOB->CONTACT->PLACEMENT_TYPE value - '.$JOB_CONTACT['PK_PLACEMENT_TYPE'];
				}
			}
		}
	}

	foreach ($DATA->EVENTS as $EVENT_ITEM) {
		$EVENT['PK_PLACEMENT_COMPANY_EVENT_TYPE'] 		= $EVENT_ITEM->PLACEMENT_COMPANY_EVENT_TYPE;
		$EVENT['PK_COMPANY_CONTACT_EMPLOYEE'] 			= $EVENT_ITEM->COMPANY_CONTACT_EMPLOYEE;

		if($EVENT['PK_PLACEMENT_COMPANY_EVENT_TYPE'] != "") {
			$res = $db->Execute("SELECT * FROM M_PLACEMENT_COMPANY_EVENT_TYPE where PK_PLACEMENT_COMPANY_EVENT_TYPE = '$EVENT[PK_PLACEMENT_COMPANY_EVENT_TYPE]' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			if($res->RecordCount() == 0) {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
				$data['MESSAGE'] .= 'Invalid EVENTS->PLACEMENT_COMPANY_EVENT_TYPE value - '.$EVENT['PK_PLACEMENT_COMPANY_EVENT_TYPE'];
			}
		}
		if($EVENT['PK_COMPANY_CONTACT_EMPLOYEE'] != "") {
			$res = $db->Execute("SELECT * FROM S_EMPLOYEE_MASTER where PK_EMPLOYEE_MASTER = '$EVENT[PK_COMPANY_CONTACT_EMPLOYEE]' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			if($res->RecordCount() == 0) {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
				$data['MESSAGE'] .= 'Invalid EVENTS->COMPANY_CONTACT_EMPLOYEE value - '.$EVENT['PK_COMPANY_CONTACT_EMPLOYEE'];
			}
		}			

		if( isset($EVENT_ITEM->CONTACT) ) {
			$CONTACT 									= $EVENT_ITEM->CONTACT;
			$EVENT_CONTACT['PK_PLACEMENT_TYPE'] 		= $CONTACT->PLACEMENT_TYPE;

			if($EVENT_CONTACT['PK_PLACEMENT_TYPE'] != "") {
				$res = $db->Execute("SELECT * FROM M_PLACEMENT_TYPE where PK_PLACEMENT_TYPE = '$EVENT_CONTACT[PK_PLACEMENT_TYPE]' AND PK_ACCOUNT = '$PK_ACCOUNT'");
				if($res->RecordCount() == 0) {
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
					$data['MESSAGE'] .= 'Invalid EVENTS->CONTACT->PLACEMENT_TYPE value - '.$EVENT_CONTACT['PK_PLACEMENT_TYPE'];
				}
			}
		}
	}
	
	foreach ($DATA->QUESTIONNAIRES as $QUESTIONNAIRE_ITEM) {
		$QUESTIONNAIRE['PK_PLACEMENT_COMPANY_QUESTIONNAIRE'] 	= $QUESTIONNAIRE_ITEM->PLACEMENT_COMPANY_QUESTIONNAIRE;

		if($QUESTIONNAIRE['PK_PLACEMENT_COMPANY_QUESTIONNAIRE'] != "") {
			$res = $db->Execute("SELECT * FROM M_PLACEMENT_COMPANY_QUESTIONNAIRE where PK_PLACEMENT_COMPANY_QUESTIONNAIRE = '$QUESTIONNAIRE[PK_PLACEMENT_COMPANY_QUESTIONNAIRE]' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			if($res->RecordCount() == 0) {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
				$data['MESSAGE'] .= 'Invalid QUESTIONNAIRES->PLACEMENT_COMPANY_QUESTIONNAIRE value - '.$QUESTIONNAIRE['PK_PLACEMENT_COMPANY_QUESTIONNAIRE'];
			}
		}
	}

	if($data['SUCCESS'] == 1) {
		$COMPANY['OLD_DSIS_COMPANY_ID'] 					= trim($DATA->OLD_DSIS_COMPANY_ID);
		$COMPANY['COMPANY_NAME'] 							= trim($DATA->COMPANY_NAME);
		$COMPANY['PK_PLACEMENT_TYPE'] 						= trim($DATA->PLACEMENT_TYPE);		
		$COMPANY['PHONE'] 									= trim($DATA->PHONE);
		$COMPANY['FAX'] 									= trim($DATA->FAX);
		$COMPANY['PK_PLACEMENT_COMPANY_STATUS'] 			= trim($DATA->PLACEMENT_COMPANY_STATUS);
		$COMPANY['ADDRESS'] 								= trim($DATA->ADDRESS);
		$COMPANY['WEBSITE'] 								= trim($DATA->WEBSITE);
		$COMPANY['ADDRESS_1'] 								= trim($DATA->ADDRESS_1);
		$COMPANY['PK_PLACEMENT_COMPANY_QUESTION_GROUP'] 	= trim($DATA->PLACEMENT_COMPANY_QUESTION_GROUP);
		$COMPANY['CITY'] 									= trim($DATA->CITY);
		$COMPANY['PK_STATES'] 								= trim($DATA->STATE);
		$COMPANY['NOTES'] 									= trim($DATA->NOTES);
		$COMPANY['ZIP'] 									= trim($DATA->ZIP);
		$COMPANY['PK_COUNTRY'] 								= trim($DATA->COUNTRY);
		$COMPANY['EMAIL'] 									= trim($DATA->EMAIL);
		$COMPANY['PK_COMPANY_ADVISOR'] 						= trim($DATA->COMPANY_ADVISOR);
		$COMPANY['PK_ACCOUNT']  							= $PK_ACCOUNT;
		$COMPANY['CREATED_ON']  							= date("Y-m-d H:i");			

		// file_put_contents('logs-t.txt', json_encode($DATA->CONTACTS).PHP_EOL , FILE_APPEND | LOCK_EX); 
		// $CONTACTS = json_decode(json_encode($DATA->CONTACTS));
		// $JOBS = $DATA->JOBS;
		db_perform('S_COMPANY', $COMPANY, 'insert');
		
		$PK_COMPANY = $db->insert_ID();

		foreach ($DATA->CONTACTS as $CONTACT_ITEM) {
			$CONTACT = [];
			$CONTACT['PK_COMPANY'] 							= $PK_COMPANY;
			$CONTACT['NAME'] 								= $CONTACT_ITEM->NAME;
			$CONTACT['TITLE'] 								= $CONTACT_ITEM->TITLE;
			$CONTACT['DEPARTMENT'] 							= $CONTACT_ITEM->DEPARTMENT;
			$CONTACT['PK_PLACEMENT_TYPE'] 					= $CONTACT_ITEM->PLACEMENT_TYPE;
			$CONTACT['PHONE'] 								= $CONTACT_ITEM->PHONE;
			$CONTACT['MOBILE'] 								= $CONTACT_ITEM->MOBILE;
			$CONTACT['OTHER_PHONE'] 						= $CONTACT_ITEM->OTHER_PHONE;
			$CONTACT['FAX'] 								= $CONTACT_ITEM->FAX;
			$CONTACT['EMAIL'] 								= $CONTACT_ITEM->EMAIL;
			$CONTACT['COMMENT'] 							= $CONTACT_ITEM->COMMENT;
			$CONTACT['PK_ACCOUNT']  						= $PK_ACCOUNT;
			$CONTACT['CREATED_ON']  						= date("Y-m-d H:i");

			db_perform('S_COMPANY_CONTACT', $CONTACT, 'insert');
			$PK_CONTACT = $db->insert_ID();
			
			$COMPANY  	= [];
			$COMPANY['PK_COMPANY_CONTACT']  	= $PK_CONTACT;
			if(isset($CONTACT_ITEM->IS_MAIN_CONTACT) && (trim(strtolower($CONTACT_ITEM->IS_MAIN_CONTACT)) === "yes")) {
				db_perform('S_COMPANY', $COMPANY, 'update', " PK_COMPANY='$PK_COMPANY'");
			}
		}

		foreach ($DATA->JOBS as $JOB_ITEM) {
			$JOB = [];
			$JOB['PK_COMPANY'] 								= $PK_COMPANY;
			$JOB['OLD_DSIS_COMPANY_JOB_ID'] 				= $JOB_ITEM->OLD_DSIS_COMPANY_JOB_ID;
			$JOB['JOB_NUMBER'] 								= $JOB_ITEM->JOB_NUMBER;
			$JOB['PK_SOC_CODE'] 							= $JOB_ITEM->SOC_CODE;
			$JOB['PK_PLACEMENT_TYPE'] 						= $JOB_ITEM->PLACEMENT_TYPE;
			$JOB['PK_ENROLLMENT_STATUS'] 					= 2;
			$JOB['PK_PAY_TYPE'] 							= $JOB_ITEM->PAY_TYPE;
			$JOB['PK_COMPANY_ADVISOR'] 						= $JOB_ITEM->COMPANY_ADVISOR;
			$JOB['JOB_TITLE'] 								= $JOB_ITEM->JOB_TITLE;
			$JOB['JOB_POSTED'] 								= $JOB_ITEM->JOB_POSTED;
			$JOB['JOB_FILLED'] 								= $JOB_ITEM->JOB_FILLED;
			$JOB['JOB_CANCELED'] 							= $JOB_ITEM->JOB_CANCELED;
			$JOB['EMPLOYMENT'] 								= 2;
			$JOB['BENEFITS'] 								= 0;
			$JOB['PAY_AMOUNT'] 								= $JOB_ITEM->PAY_AMOUNT;
			$JOB['WEEKLY_HOURS'] 							= $JOB_ITEM->WEEKLY_HOURS;
			$JOB['ANNUAL_SALARY'] 							= $JOB_ITEM->ANNUAL_SALARY;
			$JOB['JOB_DESCRIPTION'] 						= $JOB_ITEM->JOB_DESCRIPTION;
			$JOB['JOB_NOTES'] 								= $JOB_ITEM->JOB_NOTES;
			$JOB['OPEN_JOB'] 								= "N";
			$JOB['PK_ACCOUNT']  							= $PK_ACCOUNT;
			$JOB['CREATED_ON']  							= date("Y-m-d H:i");

			if(isset($JOB_ITEM->IS_PERMANENT_EMPLOYMENT) && (trim(strtolower($JOB_ITEM->IS_PERMANENT_EMPLOYMENT)) === "yes")) {
				$JOB['EMPLOYMENT'] = 1;
			}

			if(isset($JOB_ITEM->IS_FULLTIME_ENROLLMENT) && (trim(strtolower($JOB_ITEM->IS_FULLTIME_ENROLLMENT)) === "yes")) {
				$JOB['PK_ENROLLMENT_STATUS'] = 1;
			}

			if(isset($JOB_ITEM->BENEFITS) && (trim(strtolower($JOB_ITEM->BENEFITS)) === "yes")) {
				$JOB['BENEFITS'] = 1;
			}

			if(isset($JOB_ITEM->OPEN_JOB) && (trim(strtolower($JOB_ITEM->OPEN_JOB)) === "yes")) {
				$JOB['EMPLOYMENT'] = "Y";
			}

			if( isset($JOB_ITEM->CONTACT) ) {
				$CONTACT = $JOB_ITEM->CONTACT;
				$JOB_CONTACT = [];
				$JOB_CONTACT['NAME'] 						= $CONTACT->NAME;
				$JOB_CONTACT['TITLE'] 						= $CONTACT->TITLE;
				$JOB_CONTACT['DEPARTMENT'] 					= $CONTACT->DEPARTMENT;
				$JOB_CONTACT['PK_PLACEMENT_TYPE'] 			= $CONTACT->PLACEMENT_TYPE;
				$JOB_CONTACT['PHONE'] 						= $CONTACT->PHONE;
				$JOB_CONTACT['MOBILE'] 						= $CONTACT->MOBILE;
				$JOB_CONTACT['OTHER_PHONE'] 				= $CONTACT->OTHER_PHONE;
				$JOB_CONTACT['FAX'] 						= $CONTACT->FAX;
				$JOB_CONTACT['EMAIL'] 						= $CONTACT->EMAIL;
				$JOB_CONTACT['COMMENT'] 					= $CONTACT->COMMENT;
				$JOB_CONTACT['PK_COMPANY'] 					= $PK_COMPANY;
				$JOB_CONTACT['PK_ACCOUNT']  				= $PK_ACCOUNT;
				$JOB_CONTACT['CREATED_ON']  				= date("Y-m-d H:i");

				db_perform('S_COMPANY_CONTACT', $JOB_CONTACT, 'insert');
				$PK_CONTACT = $db->insert_ID();				
				$JOB['PK_COMPANY_CONTACT'] = $PK_CONTACT;
			}
			db_perform('S_COMPANY_JOB', $JOB, 'insert');
			$PK_COMPANY_JOB = $db->insert_ID();
		}

		foreach ($DATA->EVENTS as $EVENT_ITEM) {
			$EVENT = [];
			$EVENT['PK_COMPANY'] 							= $PK_COMPANY;
			$EVENT['PK_PLACEMENT_COMPANY_EVENT_TYPE'] 		= $EVENT_ITEM->PLACEMENT_COMPANY_EVENT_TYPE;
			$EVENT['EVENT_DATE'] 							= $EVENT_ITEM->EVENT_DATE;
			$EVENT['FOLLOW_UP_DATE'] 						= $EVENT_ITEM->FOLLOW_UP_DATE;
			$EVENT['PK_COMPANY_CONTACT_EMPLOYEE'] 			= $EVENT_ITEM->COMPANY_CONTACT_EMPLOYEE;
			$EVENT['COMPLETE'] 								= $EVENT_ITEM->COMPLETE;
			$EVENT['NOTE'] 									= $EVENT_ITEM->NOTE;
			$EVENT['PK_ACCOUNT']  							= $PK_ACCOUNT;
			$EVENT['CREATED_ON']  							= date("Y-m-d H:i");				

			if( isset($EVENT_ITEM->CONTACT) ) {
				$CONTACT 									= $EVENT_ITEM->CONTACT;
				$EVENT_CONTACT['NAME'] 						= $CONTACT->NAME;
				$EVENT_CONTACT['TITLE'] 					= $CONTACT->TITLE;
				$EVENT_CONTACT['DEPARTMENT'] 				= $CONTACT->DEPARTMENT;
				$EVENT_CONTACT['PK_PLACEMENT_TYPE'] 		= $CONTACT->PLACEMENT_TYPE;
				$EVENT_CONTACT['PHONE'] 					= $CONTACT->PHONE;
				$EVENT_CONTACT['MOBILE'] 					= $CONTACT->MOBILE;
				$EVENT_CONTACT['OTHER_PHONE'] 				= $CONTACT->OTHER_PHONE;
				$EVENT_CONTACT['FAX'] 						= $CONTACT->FAX;
				$EVENT_CONTACT['EMAIL'] 					= $CONTACT->EMAIL;
				$EVENT_CONTACT['COMMENT'] 					= $CONTACT->COMMENT;
				$EVENT_CONTACT['PK_COMPANY'] 				= $PK_COMPANY;
				$EVENT_CONTACT['PK_ACCOUNT']  				= $PK_ACCOUNT;
				$EVENT_CONTACT['CREATED_ON']  				= date("Y-m-d H:i");

				db_perform('S_COMPANY_CONTACT', $EVENT_CONTACT, 'insert');
				$PK_CONTACT = $db->insert_ID();
				
				$EVENT['PK_COMPANY_CONTACT'] = $PK_CONTACT;
			}
			
			db_perform('S_COMPANY_EVENT', $EVENT, 'insert');
			$PK_COMPANY_EVENT = $db->insert_ID();
		}
		
		foreach ($DATA->QUESTIONNAIRES as $QUESTIONNAIRE_ITEM) {
			$QUESTIONNAIRE = [];
			$QUESTIONNAIRE['PK_COMPANY'] 							= $PK_COMPANY;
			$QUESTIONNAIRE['PK_ACCOUNT'] 							= $PK_ACCOUNT;
			$QUESTIONNAIRE['PK_PLACEMENT_COMPANY_QUESTIONNAIRE'] 	= $QUESTIONNAIRE_ITEM->PLACEMENT_COMPANY_QUESTIONNAIRE;
			$QUESTIONNAIRE['ANSWER'] 								= $QUESTIONNAIRE_ITEM->ANSWER;
			$QUESTIONNAIRE['CREATED_ON']  							= date("Y-m-d H:i");

			db_perform('S_COMPANY_QUESTIONNAIRE	', $QUESTIONNAIRE, 'insert');
		}
		$data['MESSAGE'] 	 = 'Company Created';
	}
}

$data = json_encode($data);
echo $data;
