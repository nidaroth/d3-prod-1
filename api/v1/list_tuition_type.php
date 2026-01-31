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
	if($_GET['str'] != '')
		$cond = " AND RACE = '$_GET[str]' ";
		
	$res = $db->Execute("SELECT PK_TUITION_TYPE,TUITION_TYPE from M_TUITION_TYPE WHERE  ACTIVE = 1 $cond ");
	$i = 0;
	while (!$res->EOF) { 
		$data['TUITION_TYPE'][$i]['ID']    	= $res->fields['PK_TUITION_TYPE'];
		$data['TUITION_TYPE'][$i]['TEXT']  	= $res->fields['TUITION_TYPE'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;