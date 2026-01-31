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

	$res = $db->Execute("SELECT PK_SESSION,SESSION,DISPLAY_ORDER,COLOR FROM M_SESSION WHERE ACTIVE = 1 AND PK_ACCOUNT = '$PK_ACCOUNT' ");
	$i = 0;
	while (!$res->EOF) { 
		$data['SESSIONS'][$i]['ID']    			= $res->fields['PK_SESSION'];
		$data['SESSIONS'][$i]['TEXT']  			= $res->fields['SESSION'];
		$data['SESSIONS'][$i]['DISPLAY_ORDER']  = $res->fields['DISPLAY_ORDER'];
		$data['SESSIONS'][$i]['COLOR']  		= $res->fields['COLOR'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;