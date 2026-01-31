<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"LEAD_CONTACT_SOURCE":"From","DESCRIPTION":"API"}';

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
	
	if($DATA->PLACEMENT_COMPANY_EVENT_TYPE == '') {
		
		$data['SUCCESS'] = 0;

		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Missing PLACEMENT_COMPANY_EVENT_TYPE Value';
	}
	
	if($data['SUCCESS'] == 1) {
		$PLACEMENT_COMPANY_EVENT_TYPE['PLACEMENT_COMPANY_EVENT_TYPE'] 	= trim($DATA->PLACEMENT_COMPANY_EVENT_TYPE);
		$PLACEMENT_COMPANY_EVENT_TYPE['DESCRIPTION']					= trim($DATA->DESCRIPTION);
		$PLACEMENT_COMPANY_EVENT_TYPE['PK_ACCOUNT']  					= $PK_ACCOUNT;
		$PLACEMENT_COMPANY_EVENT_TYPE['CREATED_ON']  					= date("Y-m-d H:i");
		db_perform('M_PLACEMENT_COMPANY_EVENT_TYPE', $PLACEMENT_COMPANY_EVENT_TYPE, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		
		$data['MESSAGE'] = 'Placement Company Event Type Created';
	}
}

$data = json_encode($data);
echo $data;
