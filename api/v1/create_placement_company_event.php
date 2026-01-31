<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"PLACEMENT_COMPANY_EVENT_TYPE_ID": 1,"EVENT_DATE": "2021-05-26","FOLLOW_UP_DATE": "2021-05-26","COMPANY_CONTACT_ID": 7,"SCHOOL_CONTACT_ID": 65,"COMPLETE": "Yes","NOTE": "This is my event note 1",}';

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
	
	if($DATA->PLACEMENT_COMPANY_EVENT_TYPE_ID == '') {
		$data['SUCCESS'] = 0;

		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Missing PLACEMENT_COMPANY_EVENT_TYPE_ID Value';
	} else {
		$res = $db->Execute("SELECT PK_PLACEMENT_COMPANY_EVENT_TYPE FROM M_PLACEMENT_COMPANY_EVENT_TYPE where PK_PLACEMENT_COMPANY_EVENT_TYPE = '".$DATA->PLACEMENT_COMPANY_EVENT_TYPE_ID."' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Invalid PLACEMENT_COMPANY_EVENT_TYPE_ID value - '.$DATA->PLACEMENT_COMPANY_EVENT_TYPE_ID;
		}
	}
	
	if($DATA->COMPANY_CONTACT_ID == '') {
		$data['SUCCESS'] = 0;

		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Missing COMPANY_CONTACT_ID Value';
	} else {
		$res = $db->Execute("SELECT PK_COMPANY FROM S_COMPANY_CONTACT where PK_COMPANY_CONTACT = '".$DATA->COMPANY_CONTACT_ID."' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Invalid COMPANY_CONTACT_ID value - '.$DATA->COMPANY_CONTACT_ID;
		}
		$PK_COMPANY = $res->fields['PK_COMPANY'];
	}
	
	if($DATA->SCHOOL_CONTACT_ID != '') {
		$res = $db->Execute("SELECT PK_EMPLOYEE_MASTER FROM S_EMPLOYEE_MASTER where PK_EMPLOYEE_MASTER = '".$DATA->SCHOOL_CONTACT_ID."' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
			$data['MESSAGE'] .= 'Invalid SCHOOL_CONTACT_ID value - '.$DATA->SCHOOL_CONTACT_ID;
		}
	}
	
	$COMPLETE = $DATA->COMPLETE;
	if(strtolower($COMPLETE) == 'yes')
		$COMPLETE = 1;
	else if(strtolower($COMPLETE) == 'no')
		$COMPLETE = 0;
	else if($COMPLETE != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid COMPLETE Value - '.$DATA->COMPLETE;
	} else
		$COMPLETE = 0;	
	
	if($data['SUCCESS'] == 1) {
		$EVENT['PK_COMPANY'] 							= $PK_COMPANY;
		$EVENT['PK_PLACEMENT_COMPANY_EVENT_TYPE'] 		= $DATA->PLACEMENT_COMPANY_EVENT_TYPE_ID;
		$EVENT['EVENT_DATE'] 							= $DATA->EVENT_DATE;
		$EVENT['FOLLOW_UP_DATE'] 						= $DATA->FOLLOW_UP_DATE;
		$EVENT['PK_COMPANY_CONTACT'] 					= $DATA->COMPANY_CONTACT_ID;
		$EVENT['COMPLETE'] 								= $COMPLETE;
		$EVENT['NOTE'] 									= $DATA->NOTE;
		$EVENT['PK_COMPANY_CONTACT_EMPLOYEE'] 			= $DATA->SCHOOL_CONTACT_ID;
		$EVENT['PK_ACCOUNT']  							= $PK_ACCOUNT;
		$EVENT['CREATED_ON']  							= date("Y-m-d H:i");	
		db_perform('S_COMPANY_EVENT', $EVENT, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		
		$data['MESSAGE'] = 'Placement Company Event Created';
	}
}

$data = json_encode($data);
echo $data;
