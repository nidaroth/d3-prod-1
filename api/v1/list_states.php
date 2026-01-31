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

	$res = $db->Execute("SELECT PK_STATES,Z_COUNTRY.NAME AS COUNTRY ,STATE_NAME, STATE_CODE, Z_STATES.PK_COUNTRY FROM Z_STATES LEFT JOIN Z_COUNTRY ON Z_STATES.PK_COUNTRY = Z_COUNTRY.PK_COUNTRY WHERE Z_STATES.ACTIVE = 1 ");
	$i = 0;
	while (!$res->EOF) { 
		$data['STATES'][$i]['ID']   		= $res->fields['PK_STATES'];
		$data['STATES'][$i]['STATE_NAME'] 	= $res->fields['STATE_NAME'];
		$data['STATES'][$i]['STATE_CODE'] 	= $res->fields['STATE_CODE'];
		$data['STATES'][$i]['COUNTRY'] 		= $res->fields['COUNTRY'];
		$data['STATES'][$i]['COUNTRY_ID'] 	= $res->fields['PK_COUNTRY'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;