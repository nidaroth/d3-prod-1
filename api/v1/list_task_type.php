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

	$res = $db->Execute("SELECT PK_TASK_TYPE,PK_TASK_TYPE_MASTER,TASK_TYPE, M_TASK_TYPE.DESCRIPTION, IF(M_TASK_TYPE.PK_DEPARTMENT = -1 , 'All Departments',DEPARTMENT) AS DEPARTMENT  FROM M_TASK_TYPE LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = M_TASK_TYPE.PK_DEPARTMENT WHERE M_TASK_TYPE.PK_ACCOUNT = '$PK_ACCOUNT' AND M_TASK_TYPE.ACTIVE = 1");
	$i = 0;
	while (!$res->EOF) { 
		$data['TASK_TYPE'][$i]['ID'] 			= $res->fields['PK_TASK_TYPE'];
		$data['TASK_TYPE'][$i]['TEXT'] 	 		= $res->fields['TASK_TYPE'];
		$data['TASK_TYPE'][$i]['DESCRIPTION'] 	= $res->fields['DESCRIPTION'];
		$data['TASK_TYPE'][$i]['DEPARTMENT'] 	= $res->fields['DEPARTMENT'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;