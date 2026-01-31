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
	if($_GET['company_name'] != '')
		$cond .= " AND trim(COMPANY_NAME) = '".trim($_GET['company_name'])."' ";
		
	if($_GET['old_id'] != '')
		$cond .= " AND trim(OLD_DSIS_COMPANY_ID) = '".trim($_GET['old_id'])."' ";
	
	$res = $db->Execute("SELECT PK_COMPANY,COMPANY_NAME FROM S_COMPANY WHERE PK_ACCOUNT = '$PK_ACCOUNT' $cond ");
	if($res->RecordCount() == 0) {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = 'No Record Found';
	}
	
	$i = 0;
	while (!$res->EOF) { 
		$data['RESULT'][$i]['COMPANY_NAME'] 	= $res->fields['COMPANY_NAME'];
		$data['RESULT'][$i]['ID'] 				= $res->fields['PK_COMPANY'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;
