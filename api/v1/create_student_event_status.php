<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"EVENT_STATUS":"From","DEPARTMENT":"all department"}';

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
	
	if($DATA->EVENT_STATUS == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Event Status Missing';
	} 
	
	if($DATA->DEPARTMENT == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Department Missing';
	} else {
		$DEPARTMENT = trim($DATA->DEPARTMENT);
		if(strtolower($DEPARTMENT) == 'all department') {
			$PK_DEPARTMENT = -1;
		} else {
			$res = $db->Execute("SELECT PK_DEPARTMENT FROM M_DEPARTMENT WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND ACTIVE = 1 AND DEPARTMENT = '$DEPARTMENT' ");
			if($res->RecordCount() == 0) {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid Department';
			}
			$PK_DEPARTMENT = $res->fields['PK_DEPARTMENT'];
		}
	}
	
	if($DATA->ACTIVE == '') {
		$ACTIVE = 1;
	} else {
		if(strtolower($DATA->ACTIVE) == 'yes') 
			$ACTIVE = 1;
		else if(strtolower($DATA->ACTIVE) == 'no') 
			$ACTIVE = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid ACTIVE Value';
		}
	}

	if($data['SUCCESS'] == 1) {
		$DEPARTMENT = trim($DATA->DEPARTMENT);
		$res = $db->Execute("SELECT PK_DEPARTMENT FROM M_DEPARTMENT WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND ACTIVE = 1 AND DEPARTMENT = '$DEPARTMENT' ");
		
		$EVENT_STATUS = array();
		$EVENT_STATUS['TYPE'] 			= 3;
		$EVENT_STATUS['PK_DEPARTMENT']  = $PK_DEPARTMENT;
		$EVENT_STATUS['NOTE_STATUS'] 	= trim($DATA->EVENT_STATUS);
		$EVENT_STATUS['ACTIVE']  		= $ACTIVE;
		$EVENT_STATUS['PK_ACCOUNT']  	= $PK_ACCOUNT;
		$EVENT_STATUS['CREATED_ON']  	= date("Y-m-d H:i");
		db_perform('M_NOTE_STATUS', $EVENT_STATUS, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		$data['MESSAGE'] = 'Student Event Status Created';
	}
}

$data = json_encode($data);
echo $data;
