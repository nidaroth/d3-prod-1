<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"ENROLLMENT_ID":"123","PK_STUDENT_MASTER":"123","DEPARTMENT_ID":"27","NOTE_STATUS_ID":"3","NOTE_TYPE_ID":"2","NOTE_DATE":"2021-04-23","NOTE_TIME":"19:30:00","FOLLOWUP_DATE":"2021-04-28","FOLLOWUP_TIME":"20:30:00","EMPLOYEE_ID":50,"COMPLETED":"No","COMMENTS":"COMMENTS"}';

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

	if($DATA->ENROLLMENT_ID == '' ) {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] .= 'Missing ENROLLMENT_ID Value';
	} else if($DATA->ENROLLMENT_ID != '') {
		$ENROLLMENT_ID = $DATA->ENROLLMENT_ID;
		
		$res_st = $db->Execute("SELECT PK_STUDENT_MASTER,PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_STUDENT_ENROLLMENT = '$ENROLLMENT_ID' ");
		if($res_st->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			$data['MESSAGE'] .= 'Invalid ENROLLMENT_ID Value - '.$ENROLLMENT_ID;
		} else {
			$PK_STUDENT_MASTER 		= $res_st->fields['PK_STUDENT_MASTER'];
			$PK_STUDENT_ENROLLMENT 	= $res_st->fields['PK_STUDENT_ENROLLMENT'];
		}
		
	}

	$PK_DEPARTMENT = $DATA->DEPARTMENT_ID;
	if($PK_DEPARTMENT == '') {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] .= 'Missing DEPARTMENT_ID Value';
	} else if($PK_DEPARTMENT != -1) {
		$res_st = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE PK_DEPARTMENT = '$PK_DEPARTMENT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid DEPARTMENT_ID Value - '.$PK_DEPARTMENT;
		}
	}
	
	$PK_NOTE_STATUS = $DATA->NOTE_STATUS_ID;
	if($PK_NOTE_STATUS != '') {
		$res_st = $db->Execute("select PK_NOTE_STATUS from M_NOTE_STATUS WHERE PK_NOTE_STATUS = '$PK_NOTE_STATUS' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid NOTE_STATUS_ID Value - '.$PK_NOTE_STATUS;
		}
	}
	
	$PK_NOTE_TYPE = $DATA->NOTE_TYPE_ID;
	if($PK_NOTE_TYPE == '') {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] .= 'Missing NOTE_TYPE_ID Value';
	} else {
		$res_st = $db->Execute("select PK_NOTE_TYPE from M_NOTE_TYPE WHERE PK_NOTE_TYPE = '$PK_NOTE_TYPE' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid NOTE_TYPE_ID Value - '.$PK_NOTE_TYPE;
		}
	}
	
	$PK_EMPLOYEE_MASTER = $DATA->EMPLOYEE_ID;
	if($PK_EMPLOYEE_MASTER != ''){ 
		$res_st = $db->Execute("select PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid EMPLOYEE_ID Value - '.$PK_EMPLOYEE_MASTER;
		}
	}
	
	$COMPLETED = $DATA->COMPLETED;
	if(strtolower($COMPLETED) != 'yes' && strtolower($COMPLETED) != 'no' && strtolower($COMPLETED) != '') {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] .= 'Invalid COMPLETED Value';
	}

	if($data['SUCCESS'] == 1) {
		$COMPLETED = $DATA->COMPLETED;
		if(strtolower($COMPLETED) == 'yes')
			$COMPLETED = 1;
		else if(strtolower($COMPLETED) == 'no')
			$COMPLETED = 0;
		else
			$COMPLETED = 0;
			
		$STUDENT_NOTES['PK_ACCOUNT']   			= $PK_ACCOUNT;
		$STUDENT_NOTES['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
		$STUDENT_NOTES['PK_STUDENT_ENROLLMENT'] = $PK_STUDENT_ENROLLMENT;
		$STUDENT_NOTES['PK_DEPARTMENT'] 		= $DATA->DEPARTMENT_ID;
		$STUDENT_NOTES['PK_NOTE_STATUS'] 		= $DATA->NOTE_STATUS_ID;
		$STUDENT_NOTES['PK_NOTE_TYPE'] 			= $DATA->NOTE_TYPE_ID;
		$STUDENT_NOTES['NOTE_DATE'] 			= $DATA->NOTE_DATE;
		$STUDENT_NOTES['NOTE_TIME'] 			= $DATA->NOTE_TIME;
		$STUDENT_NOTES['FOLLOWUP_DATE'] 		= $DATA->FOLLOWUP_DATE;
		$STUDENT_NOTES['FOLLOWUP_TIME'] 		= $DATA->FOLLOWUP_TIME;
		$STUDENT_NOTES['PK_EMPLOYEE_MASTER'] 	= $DATA->EMPLOYEE_ID;
		$STUDENT_NOTES['SATISFIED'] 			= $COMPLETED;
		$STUDENT_NOTES['NOTES'] 				= $DATA->COMMENTS;
		$STUDENT_NOTES['IS_EVENT'] 				= 0;
		$STUDENT_NOTES['CREATED_ON']  			= date("Y-m-d H:i");
		db_perform('S_STUDENT_NOTES', $STUDENT_NOTES, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		
		$data['MESSAGE'] = 'Notes Created';
	}
}

$data = json_encode($data);
echo $data;
