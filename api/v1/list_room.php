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

	$res = $db->Execute("select PK_CAMPUS_ROOM,ROOM_NO,ROOM_DESCRIPTION,CLASS_SIZE, OFFICIAL_CAMPUS_NAME, M_CAMPUS_ROOM.PK_CAMPUS from M_CAMPUS_ROOM LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = M_CAMPUS_ROOM.PK_CAMPUS where M_CAMPUS_ROOM.PK_ACCOUNT = '$PK_ACCOUNT' AND M_CAMPUS_ROOM.ACTIVE = 1 ");
	$i = 0;
	while (!$res->EOF) { 
		$data['ROOM'][$i]['ID'] 			= $res->fields['PK_CAMPUS_ROOM'];
		$data['ROOM'][$i]['NO'] 			= $res->fields['ROOM_NO'];
		$data['ROOM'][$i]['DESCRIPTION'] 	= $res->fields['ROOM_DESCRIPTION'];
		$data['ROOM'][$i]['CLASS_SIZE'] 	= $res->fields['CLASS_SIZE'];
		$data['ROOM'][$i]['CAMPUS_NAME'] 	= $res->fields['OFFICIAL_CAMPUS_NAME'];
		$data['ROOM'][$i]['CAMPUS_ID'] 		= $res->fields['PK_CAMPUS'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;