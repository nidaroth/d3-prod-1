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

	$res = $db->Execute("select PK_LEAD_SOURCE,LEAD_SOURCE, LEAD_SOURCE_GROUP from M_LEAD_SOURCE LEFT JOIN M_LEAD_SOURCE_GROUP ON M_LEAD_SOURCE_GROUP.PK_LEAD_SOURCE_GROUP = M_LEAD_SOURCE.PK_LEAD_SOURCE_GROUP where M_LEAD_SOURCE.PK_ACCOUNT = '$PK_ACCOUNT' AND M_LEAD_SOURCE.ACTIVE = 1 ");
	$i = 0;
	while (!$res->EOF) { 
		$data['LEAD_SOURCE'][$i]['ID'] 		= $res->fields['PK_LEAD_SOURCE'];
		$data['LEAD_SOURCE'][$i]['TEXT'] 	= $res->fields['LEAD_SOURCE'];
		$data['LEAD_SOURCE'][$i]['GROUP'] 	= $res->fields['LEAD_SOURCE_GROUP'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;