<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"PK_STUDENT_MASTER":"123","TEST_LABEL":"Test 1", "TEST_RESULT":"aaa", "PASSED":"Yes", "TEST_DATE":"2021-03-02"}';

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

	if($DATA->PK_STUDENT_MASTER == '' ) {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] .= 'Missing PK_STUDENT_MASTER Value';
	} else if($DATA->PK_STUDENT_MASTER != '') {
		$PK_STUDENT_MASTER = $DATA->PK_STUDENT_MASTER;
		
		$res_st = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, PK_STUDENT_MASTER FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_MASTER > 0 AND IS_ACTIVE_ENROLLMENT = 1");
		if($res_st->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			$data['MESSAGE'] .= 'No Active Enrollment for - '.$PK_STUDENT_MASTER;
		} else {
			$PK_STUDENT_MASTER 		= $res_st->fields['PK_STUDENT_MASTER'];
			$PK_STUDENT_ENROLLMENT 	= $res_st->fields['PK_STUDENT_ENROLLMENT'];
		}
		
	}
	
	if(strtolower($DATA->PASSED) == 'yes')
		$PASSED = 1;
	else if(strtolower($DATA->PASSED) == 'no')
		$PASSED = 0;
	else if($DATA->PASSED != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid PASSED Value';
	} else
		$PASSED = 0;

	if($data['SUCCESS'] == 1) {
		$PASSED = $DATA->PASSED;
		if(strtolower($PASSED) == 'yes')
			$PASSED = 1;
		else if(strtolower($PASSED) == 'no')
			$PASSED = 0;
		else
			$PASSED = 0;
			
		$STUDENT_TEST['TEST_LABEL'] 			= $DATA->TEST_LABEL;
		$STUDENT_TEST['TEST_RESULT'] 			= $DATA->TEST_RESULT;
		$STUDENT_TEST['PASSED'] 				= $PASSED;
		$STUDENT_TEST['TEST_DATE'] 				= $DATA->TEST_DATE;
		$STUDENT_TEST['PK_STUDENT_ENROLLMENT']  = $PK_STUDENT_ENROLLMENT;
		$STUDENT_TEST['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
		$STUDENT_TEST['PK_ACCOUNT'] 			= $PK_ACCOUNT;
		$STUDENT_TEST['CREATED_ON']  			= date("Y-m-d H:i");
		db_perform('S_STUDENT_TEST', $STUDENT_TEST, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		
		$data['MESSAGE'] = 'Test Created';
	}
}

$data = json_encode($data);
echo $data;
