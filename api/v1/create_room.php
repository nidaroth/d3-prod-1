<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"ROOM_NO":"101","DESCRIPTION":"medical theory 1","CLASS_SIZE":"15","CAMPUS_ID":"2"}';

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
	
	if($DATA->ROOM_NO == '') {
		$data['SUCCESS'] = 0;

		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Missing ROOM_NO Vaue';
	}
	
	if($DATA->CAMPUS_ID != ''){
		$PK_CAMPUS = $DATA->CAMPUS_ID;
		$res_st = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] = 'Invalid CAMPUS_ID Value - '.$PK_CAMPUS;
		}
	} else {
		$data['SUCCESS'] = 0;

		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Missing CAMPUS_ID Vaue';
	}
	
	if($data['SUCCESS'] == 1) {
		$CAMPUS_ROOM['PK_CAMPUS'] 			= $PK_CAMPUS;
		$CAMPUS_ROOM['ROOM_NO'] 			= trim($DATA->ROOM_NO);
		$CAMPUS_ROOM['ROOM_DESCRIPTION'] 	= trim($DATA->DESCRIPTION);
		$CAMPUS_ROOM['CLASS_SIZE'] 			= trim($DATA->CLASS_SIZE);
		
		$CAMPUS_ROOM['PK_ACCOUNT']  = $PK_ACCOUNT;
		$CAMPUS_ROOM['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_CAMPUS_ROOM', $CAMPUS_ROOM, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		$data['MESSAGE'] 	 = 'Room Created';
	}
}

$data = json_encode($data);
echo $data;
