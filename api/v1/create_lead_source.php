<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"LEAD_SOURCE":"From","DESCRIPTION":"API"}';

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
	
	if($DATA->LEAD_SOURCE == '') {
		
		$data['SUCCESS'] = 0;

		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Lead Source Name Missing';
	}
	
	if($data['SUCCESS'] == 1) {
		$LEAD_SOURCE_GROUP = trim($DATA->LEAD_SOURCE_GROUP);
		if($LEAD_SOURCE_GROUP != '') {
			$res_1 = $db->Execute("select PK_LEAD_SOURCE_GROUP from M_LEAD_SOURCE_GROUP WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND LEAD_SOURCE_GROUP = '$LEAD_SOURCE_GROUP' ");
			if($res_1->RecordCount() > 0)
				$PK_LEAD_SOURCE_GROUP = $res_1->fields['PK_LEAD_SOURCE_GROUP'];
			else {
				$LEAD_SOURCE_GROUP_ARR['LEAD_SOURCE_GROUP'] = $LEAD_SOURCE_GROUP;
				$LEAD_SOURCE_GROUP_ARR['PK_ACCOUNT']  		= $PK_ACCOUNT;
				$LEAD_SOURCE_GROUP_ARR['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('M_LEAD_SOURCE_GROUP', $LEAD_SOURCE_GROUP_ARR, 'insert');
				
				$PK_LEAD_SOURCE_GROUP = $db->insert_ID();
			}
		}
		
		$LEAD_SOURCE['LEAD_SOURCE'] 			= trim($DATA->LEAD_SOURCE);
		$LEAD_SOURCE['DESCRIPTION'] 			= trim($DATA->DESCRIPTION);
		$LEAD_SOURCE['PK_LEAD_SOURCE_GROUP']  	= $PK_LEAD_SOURCE_GROUP;
		$LEAD_SOURCE['PK_ACCOUNT']  			= $PK_ACCOUNT;
		$LEAD_SOURCE['CREATED_ON'] 				= date("Y-m-d H:i");
		db_perform('M_LEAD_SOURCE', $LEAD_SOURCE, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		
		$data['MESSAGE'] = 'Lead Source Created';
	}
}

$data = json_encode($data);
echo $data;
