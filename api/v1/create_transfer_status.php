<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"CREDIT_TRANSFER_STATUS":"From","DESCRIPTION":"desc","SHOW_ON_TRANSCRIPT":"yes"}';

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
	
	if($DATA->CREDIT_TRANSFER_STATUS == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Credit Transfer Status Missing';
	}
	
	if($DATA->SHOW_ON_TRANSCRIPT == '') {
		$SHOW_ON_TRANSCRIPT = 0;
	} else {
		if(strtolower($DATA->SHOW_ON_TRANSCRIPT) == 'yes') 
			$SHOW_ON_TRANSCRIPT = 1;
		else if(strtolower($DATA->SHOW_ON_TRANSCRIPT) == 'no') 
			$SHOW_ON_TRANSCRIPT = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Show On Transcript Value';
		}
	}
	
	if($data['SUCCESS'] == 1) {
		$CREDIT_TRANSFER_STATUS['CREDIT_TRANSFER_STATUS'] 	= trim($DATA->CREDIT_TRANSFER_STATUS);
		$CREDIT_TRANSFER_STATUS['DESCRIPTION'] 				= trim($DATA->DESCRIPTION);
		$CREDIT_TRANSFER_STATUS['SHOW_ON_TRANSCRIPT'] 		= $SHOW_ON_TRANSCRIPT;
		$CREDIT_TRANSFER_STATUS['PK_ACCOUNT']  				= $PK_ACCOUNT;
		$CREDIT_TRANSFER_STATUS['CREATED_ON']  				= date("Y-m-d H:i");
		db_perform('M_CREDIT_TRANSFER_STATUS', $CREDIT_TRANSFER_STATUS, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		$data['MESSAGE'] 	 = 'Credit Transfer Status Created';
	}
}

$data = json_encode($data);
echo $data;
