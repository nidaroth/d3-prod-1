<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"CODE":"from","TITLE":"api","IPEDS_CATEGORY_ID":"1"}';

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
	
	if($DATA->PLACEMENT_STATUS == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= ' Missing PLACEMENT_STATUS Value';
	}

	$PK_PLACEMENT_STUDENT_STATUS_CATEGORY = trim($DATA->PLACEMENT_STUDENT_STATUS_CATEGORY_ID);
	if(strtolower($PK_PLACEMENT_STUDENT_STATUS_CATEGORY) == 'null')
		$PK_PLACEMENT_STUDENT_STATUS_CATEGORY = '';
		
	if($PK_PLACEMENT_STUDENT_STATUS_CATEGORY != '') {
		$res = $db->Execute("SELECT PK_PLACEMENT_STUDENT_STATUS_CATEGORY FROM M_PLACEMENT_STUDENT_STATUS_CATEGORY WHERE ACTIVE = 1 AND PK_PLACEMENT_STUDENT_STATUS_CATEGORY = '$PK_PLACEMENT_STUDENT_STATUS_CATEGORY' ");
		if($res->RecordCount() == 0) {
			$PK_PLACEMENT_STUDENT_STATUS_CATEGORY = $res->fields['PK_PLACEMENT_STUDENT_STATUS_CATEGORY'];
			
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid PLACEMENT_STUDENT_STATUS_CATEGORY_ID Value - '.$DATA->PLACEMENT_STUDENT_STATUS_CATEGORY_ID;
		}
	}
	
	$EMPLOYED = trim($DATA->EMPLOYED);
	if(strtolower($EMPLOYED) == 'yes')
		$EMPLOYED = 1;
	else if(strtolower($EMPLOYED) == 'no')
		$EMPLOYED = 0;
	else if($SSN_VERIFIED != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid EMPLOYED Value - '.$DATA->EMPLOYED;
	} else
		$EMPLOYED = 0;
	
	if($data['SUCCESS'] == 1) {
		$PLACEMENT_STATUS['PLACEMENT_STATUS'] 						= trim($DATA->PLACEMENT_STATUS);
		$PLACEMENT_STATUS['PK_PLACEMENT_STUDENT_STATUS_CATEGORY']	= $PK_PLACEMENT_STUDENT_STATUS_CATEGORY;
		$PLACEMENT_STATUS['EMPLOYED']								= $EMPLOYED;
		$PLACEMENT_STATUS['PK_ACCOUNT']  							= $PK_ACCOUNT;
		$PLACEMENT_STATUS['CREATED_ON']  							= date("Y-m-d H:i");
		db_perform('M_PLACEMENT_STATUS', $PLACEMENT_STATUS, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		
		$data['MESSAGE'] = 'Placement Status Created';
	}
}

$data = json_encode($data);
echo $data;
