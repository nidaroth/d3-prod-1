<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"EVENT_OTHER":"From","DESCRIPTION":"Desc","DEPARTMENT_ID":"-1"}';

$DATA = ($DATA);
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

	if($DATA->EVENT_OTHER == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Event Other Missing';
	} 
	
	$PK_DEPARTMENT = $DATA->DEPARTMENT_ID;
	if($PK_DEPARTMENT == '') {
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] .= 'Missing DEPARTMENT_ID Value';
	} else if($PK_DEPARTMENT != -1) {
		$res_st = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE PK_DEPARTMENT = '$PK_DEPARTMENT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= ' Invalid DEPARTMENT_ID Value - '.$PK_DEPARTMENT;
		}
	}
	
	if($data['SUCCESS'] == 1) {
		$EVENT_OTHER['TYPE']  	  		= 2;
		$EVENT_OTHER['EVENT_OTHER'] 	= trim($DATA->EVENT_OTHER);
		$EVENT_OTHER['DESCRIPTION'] 	= trim($DATA->DESCRIPTION);
		$EVENT_OTHER['PK_DEPARTMENT'] 	= $DATA->DEPARTMENT_ID;
		$EVENT_OTHER['PK_ACCOUNT']  	= $PK_ACCOUNT;
		$EVENT_OTHER['CREATED_ON']  	= date("Y-m-d H:i");
		db_perform('M_EVENT_OTHER', $EVENT_OTHER, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		$data['MESSAGE'] = 'Event Other Created';
	}
}

$data = json_encode($data);
echo $data;
