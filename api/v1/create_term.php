<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"BEGIN_DATE":"2021-01-01","END_DATE":"2021-06-30","DESCRIPTION":"","TERM_GROUP":"","ALLOW_ONLINE_ENROLLMENT":"","LMS_ACTIVE":"Yes","OLD_DSIS_ID":123}';

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
	
	if($DATA->BEGIN_DATE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Begin Date Missing';
	}
	
	/*if($DATA->END_DATE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'End Date Missing';
	}*/
	
	if($DATA->ALLOW_ONLINE_ENROLLMENT == '') {
		$ALLOW_ONLINE_ENROLLMENT = 0;
	} else {
		if(strtolower($DATA->ALLOW_ONLINE_ENROLLMENT) == 'yes') 
			$ALLOW_ONLINE_ENROLLMENT = 1;
		else if(strtolower($DATA->ALLOW_ONLINE_ENROLLMENT) == 'no') 
			$ALLOW_ONLINE_ENROLLMENT = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Allow Online Enrollment Value';
		}
	}
	
	if($DATA->LMS_ACTIVE == '') {
		$LMS_ACTIVE = 0;
	} else {
		if(strtolower($DATA->LMS_ACTIVE) == 'yes') 
			$LMS_ACTIVE = 1;
		else if(strtolower($DATA->LMS_ACTIVE) == 'no') 
			$LMS_ACTIVE = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid LMS Active Value';
		}
	}
	
	if($data['SUCCESS'] == 1) {

		$TERM_MASTER['BEGIN_DATE'] 				= trim($DATA->BEGIN_DATE);
		$TERM_MASTER['END_DATE'] 				= trim($DATA->END_DATE);
		$TERM_MASTER['TERM_DESCRIPTION'] 		= trim($DATA->DESCRIPTION);
		$TERM_MASTER['TERM_GROUP'] 				= trim($DATA->TERM_GROUP);
		$TERM_MASTER['OLD_DSIS_ID'] 			= trim($DATA->OLD_DSIS_ID);
		$TERM_MASTER['ALLOW_ONLINE_ENROLLMENT'] = $ALLOW_ONLINE_ENROLLMENT;
		$TERM_MASTER['LMS_ACTIVE'] 				= $LMS_ACTIVE;
		$TERM_MASTER['PK_ACCOUNT']  	 		= $PK_ACCOUNT;
		$TERM_MASTER['CREATED_ON'] 	 			= date("Y-m-d H:i");
		db_perform('S_TERM_MASTER', $TERM_MASTER, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		$data['MESSAGE'] 	 = 'Term Created';
	}
}

$data = json_encode($data);
echo $data;
