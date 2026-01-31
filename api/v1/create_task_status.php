<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"TASK_STATUS":"From","DESCRIPTION":"Desc"}';

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
	
	if($DATA->TASK_STATUS == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Task Status Missing';
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
	
	if($data['SUCCESS'] == 1) {
		$TASK_STATUS['TASK_STATUS'] 	= trim($DATA->TASK_STATUS);
		$TASK_STATUS['DESCRIPTION'] 	= trim($DATA->DESCRIPTION);
		$TASK_STATUS['PK_DEPARTMENT'] 	= $DATA->DEPARTMENT_ID;
		$TASK_STATUS['PK_ACCOUNT']  	= $PK_ACCOUNT;
		$TASK_STATUS['CREATED_ON']  	= date("Y-m-d H:i");
		db_perform('M_TASK_STATUS', $TASK_STATUS, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		$data['MESSAGE'] = 'Task Status Created';
	}
}

$data = json_encode($data);
echo $data;
