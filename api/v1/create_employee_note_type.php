<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"EMPLOYEE_NOTE_TYPE":"From","DESCRIPTION":"API"}';

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
	
	if($DATA->EMPLOYEE_NOTE_TYPE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Employee Note Type Missing';
	} 
	
	if($data['SUCCESS'] == 1) {

		$EMPLOYEE_NOTE_TYPE = array();
		$EMPLOYEE_NOTE_TYPE['EMPLOYEE_NOTE_TYPE'] 	= trim($DATA->EMPLOYEE_NOTE_TYPE);
		$EMPLOYEE_NOTE_TYPE['DESCRIPTION'] 			= trim($DATA->DESCRIPTION);
		$EMPLOYEE_NOTE_TYPE['PK_ACCOUNT']  			= $PK_ACCOUNT;
		$EMPLOYEE_NOTE_TYPE['CREATED_ON']  			= date("Y-m-d H:i");
		db_perform('M_EMPLOYEE_NOTE_TYPE', $EMPLOYEE_NOTE_TYPE, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		$data['MESSAGE'] = 'Employee Note Type Created';
	}
}

$data = json_encode($data);
echo $data;
