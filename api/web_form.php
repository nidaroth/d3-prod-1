<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"FIRST_NAME":"From","LAST_NAME":"web","MIDDLE_NAME":"M","ADMISSION_REP_ID":"30","EMAIL":"aaa@www.in","HOME_PHONE":"123-456-789","CELL_PHONE":"444-555-6666" ,"LEAD_SOURCE_ID":"1","ADM_USER_ID":"ADM user","OLD_DSIS_STU_NO":"","OLD_DSIS_LEAD_ID":"","ADDRESS":{"STREET":"2/F1 A.S.S.S.S Road","CITY":"VNR","STATE_CODE":"CA","ZIP":"62600","COUNTRY_CODE":"US"},"CAMPUS":[2,3]}';

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
	
	$FIRST_NAME 		= trim($DATA->FIRST_NAME);
	$LAST_NAME 			= trim($DATA->LAST_NAME);
	$MIDDLE_NAME 		= trim($DATA->MIDDLE_NAME);
	$EMAIL 				= trim($DATA->EMAIL);
	$HOME_PHONE 		= trim($DATA->HOME_PHONE);
	$CELL_PHONE 		= trim($DATA->CELL_PHONE);
	$LEAD_SOURCE_ID 	= trim($DATA->LEAD_SOURCE_ID);
	$ADM_USER_ID 		= trim($DATA->ADM_USER_ID);
	$OLD_DSIS_STU_NO 	= trim($DATA->OLD_DSIS_STU_NO);
	$OLD_DSIS_LEAD_ID 	= trim($DATA->OLD_DSIS_LEAD_ID);
	$PK_REPRESENTATIVE 	= trim($DATA->ADMISSION_REP_ID);
	
	$ADDRESS 			= trim($DATA->ADDRESS->STREET);
	$CITY 				= trim($DATA->ADDRESS->CITY);
	$STATE_CODE 		= trim($DATA->ADDRESS->STATE_CODE);
	$ZIP 				= trim($DATA->ADDRESS->ZIP);
	$COUNTRY_CODE 		= trim($DATA->ADDRESS->COUNTRY_CODE);
	
	if($FIRST_NAME == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Missing FIRST_NAME Value';
	}
	
	if($LAST_NAME == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Missing LAST_NAME Value';
	}
	
	if($STATE_CODE != '') {
		$res_st = $db->Execute("select PK_STATES from Z_STATES WHERE STATE_CODE = '$STATE_CODE' ");
		$PK_STATES = $res_st->fields['PK_STATES'];
		
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid STATE_CODE Value';
		}
	}
	
	if($COUNTRY_CODE != '') {
		$res_st = $db->Execute("select PK_COUNTRY from Z_COUNTRY WHERE CODE = '$COUNTRY_CODE' ");
		$PK_COUNTRY = $res_st->fields['PK_COUNTRY'];
		
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid COUNTRY_CODE Value';
		}
	}
	
	if($LEAD_SOURCE_ID != '') {
		$res_st = $db->Execute("select PK_LEAD_SOURCE from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_LEAD_SOURCE = '$LEAD_SOURCE_ID' ");
		$PK_LEAD_SOURCE = $res_st->fields['PK_LEAD_SOURCE'];
		
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
	
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid LEAD_SOURCE_ID Value';
		}
	}
	
	if($PK_REPRESENTATIVE != '') {
		$res_st = $db->Execute("select PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$PK_REPRESENTATIVE' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		$PK_REPRESENTATIVE = $res_st->fields['PK_EMPLOYEE_MASTER'];
		
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid ADMISSION_REP_ID Value';
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
	
	$STUDENT_MASTER['FIRST_NAME'] 				= $FIRST_NAME;
	$STUDENT_MASTER['LAST_NAME'] 				= $LAST_NAME;
	$STUDENT_MASTER['MIDDLE_NAME'] 				= $MIDDLE_NAME;
	//$STUDENT_MASTER['OLD_DSIS_STU_NO']  		= $OLD_DSIS_STU_NO;
	//$STUDENT_MASTER['OLD_DSIS_LEAD_ID']  		= $OLD_DSIS_LEAD_ID;
	$STUDENT_MASTER['PK_ACCOUNT']  				= $PK_ACCOUNT;
	$STUDENT_MASTER['CREATED_ON']  				= date("Y-m-d H:i");
	db_perform('S_STUDENT_MASTER', $STUDENT_MASTER, 'insert');
	$PK_STUDENT_MASTER = $db->insert_ID();
	
	$res_acc = $db->Execute("SELECT AUTO_GENERATE_STUD_ID,STUD_CODE,STUD_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$PK_ACCOUNT' "); 
	if($res_acc->fields['AUTO_GENERATE_STUD_ID'] == 1) {
		$STUDENT_ACADEMICS['STUDENT_ID'] = $res_acc->fields['STUD_CODE'].$res_acc->fields['STUD_NO'];
		$STUD_NO = $res_acc->fields['STUD_NO'] + 1;
		$db->Execute("UPDATE Z_ACCOUNT SET STUD_NO = '$STUD_NO' WHERE PK_ACCOUNT = '$PK_ACCOUNT' "); 
	}
	$STUDENT_ACADEMICS['PREVIOUS_COLLEGE'] 	= 2;
	$STUDENT_ACADEMICS['FERPA_BLOCK'] 		= 2;
	$STUDENT_ACADEMICS['ENTRY_DATE'] 	 			= date("Y-m-d");
	$STUDENT_ACADEMICS['ENTRY_TIME'] 	 			= date("H:i:s");
	$STUDENT_ACADEMICS['ADM_USER_ID']				= $ADM_USER_ID;
	$STUDENT_ACADEMICS['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
	$STUDENT_ACADEMICS['PK_ACCOUNT']  				= $PK_ACCOUNT;
	$STUDENT_ACADEMICS['CREATED_ON']  				= date("Y-m-d H:i");
	db_perform('S_STUDENT_ACADEMICS', $STUDENT_ACADEMICS, 'insert');

	$res = $db->Execute("SELECT PK_STUDENT_STATUS FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS_MASTER = '1' AND PK_ACCOUNT = '$PK_ACCOUNT'");
	$STUDENT_ENROLLMENT['IS_ACTIVE_ENROLLMENT'] 	= 1;
	$STUDENT_ENROLLMENT['PK_STUDENT_STATUS'] 		= $res->fields['PK_STUDENT_STATUS'];
	$STUDENT_ENROLLMENT['PK_LEAD_SOURCE'] 	 		= $PK_LEAD_SOURCE;
	$STUDENT_ENROLLMENT['PK_REPRESENTATIVE'] 	 	= $PK_REPRESENTATIVE;
	$STUDENT_ENROLLMENT['PK_ACCOUNT']  		 		= $PK_ACCOUNT;
	$STUDENT_ENROLLMENT['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
	$STUDENT_ENROLLMENT['CREATED_ON']  		 		= date("Y-m-d H:i");
	db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'insert');
	$PK_STUDENT_ENROLLMENT = $db->insert_ID();
	
	$STUDENT_STATUS_LOG['PK_STUDENT_STATUS'] 		= $STUDENT_ENROLLMENT['PK_STUDENT_STATUS'];
	$STUDENT_STATUS_LOG['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
	$STUDENT_STATUS_LOG['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
	$STUDENT_STATUS_LOG['PK_ACCOUNT']  				= $PK_ACCOUNT;
	$STUDENT_STATUS_LOG['CHANGED_ON']  				= date("Y-m-d H:i");
	db_perform('S_STUDENT_STATUS_LOG', $STUDENT_STATUS_LOG, 'insert');
	
	if(!empty($PK_CAMPUS_ARR)){
		foreach($PK_CAMPUS_ARR as $PK_CAMPUS) {
			$STUDENT_CAMPUS['PK_CAMPUS']   				= $PK_CAMPUS;
			$STUDENT_CAMPUS['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
			$STUDENT_CAMPUS['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
			$STUDENT_CAMPUS['PK_ACCOUNT'] 				= $PK_ACCOUNT;
			$STUDENT_CAMPUS['CREATED_ON']  				= date("Y-m-d H:i");
			db_perform('S_STUDENT_CAMPUS', $STUDENT_CAMPUS, 'insert');
		}
	}
		
	$HOME_PHONE 	= preg_replace( '/[^0-9]/', '',$HOME_PHONE);
	$CELL_PHONE 	= preg_replace( '/[^0-9]/', '',$CELL_PHONE);
	
	if($HOME_PHONE != '')
		$HOME_PHONE = '('.$HOME_PHONE[0].$HOME_PHONE[1].$HOME_PHONE[2].') '.$HOME_PHONE[3].$HOME_PHONE[4].$HOME_PHONE[5].'-'.$HOME_PHONE[6].$HOME_PHONE[7].$HOME_PHONE[8].$HOME_PHONE[9];

	if($CELL_PHONE != '')
		$CELL_PHONE = '('.$CELL_PHONE[0].$CELL_PHONE[1].$CELL_PHONE[2].') '.$CELL_PHONE[3].$CELL_PHONE[4].$CELL_PHONE[5].'-'.$CELL_PHONE[6].$CELL_PHONE[7].$CELL_PHONE[8].$CELL_PHONE[9];

	$STUDENT_CONTACT['PK_STUDENT_CONTACT_TYPE_MASTER']   	= 1;
	$STUDENT_CONTACT['ADDRESS']   							= $ADDRESS;
	$STUDENT_CONTACT['CITY']   								= $CITY;
	$STUDENT_CONTACT['PK_STATES']   						= $PK_STATES;
	$STUDENT_CONTACT['ZIP']   								= $CONTACTS->ZIP;
	$STUDENT_CONTACT['PK_COUNTRY']   						= $PK_COUNTRY;
	$STUDENT_CONTACT['EMAIL'] 								= $EMAIL;
	$STUDENT_CONTACT['HOME_PHONE'] 							= $HOME_PHONE;
	$STUDENT_CONTACT['CELL_PHONE'] 							= $CELL_PHONE;
	$STUDENT_CONTACT['PK_ACCOUNT']   						= $PK_ACCOUNT;
	$STUDENT_CONTACT['PK_STUDENT_MASTER']   				= $PK_STUDENT_MASTER;
	$STUDENT_CONTACT['CREATED_ON']  						= date("Y-m-d H:i");
	db_perform('S_STUDENT_CONTACT', $STUDENT_CONTACT, 'insert');
	
	$data['MESSAGE'] 	 = 'Lead Created';
	$data['INTERNAL_ID'] = $PK_STUDENT_MASTER;
}

$data = json_encode($data);
echo $data;