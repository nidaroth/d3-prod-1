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
	
	$cond = "";
	if($_GET['emp_id'] != '')
		$cond .= " AND trim(EMPLOYEE_ID) = '".trim($_GET['emp_id'])."' ";
	if($_GET['fname'] != '')
		$cond .= " AND trim(FIRST_NAME) = '".trim($_GET['fname'])."' ";
	if($_GET['lname'] != '')
		$cond .= " AND trim(LAST_NAME) = '".trim($_GET['lname'])."' ";

		
	$res = $db->Execute("SELECT PK_EMPLOYEE_MASTER, EMPLOYEE_ID, FIRST_NAME, LAST_NAME  FROM S_EMPLOYEE_MASTER WHERE PK_ACCOUNT = '$PK_ACCOUNT' $cond ");
	if($res->RecordCount() == 0) {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = 'No Record Found';
	}
	
	$i = 0;
	while (!$res->EOF) { 
		$data['RESULT'][$i]['EMPLOYEE_ID'] 	= $res->fields['EMPLOYEE_ID'];
		$data['RESULT'][$i]['FIRST_NAME'] 	= $res->fields['FIRST_NAME'];
		$data['RESULT'][$i]['LAST_NAME'] 	= $res->fields['LAST_NAME'];
		$data['RESULT'][$i]['ID'] 			= $res->fields['PK_EMPLOYEE_MASTER'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;
