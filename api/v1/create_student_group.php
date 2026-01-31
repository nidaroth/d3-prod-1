<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"OFFICIAL_CAMPUS_NAME":"OFFICIAL_CAMPUS_NAME","CAMPUS_NAME":"CAMPUS_NAME","CAMPUS_CODE":"CAMPUS_CODE","SCHOOL_CODE":"SCHOOL_CODE","INSTITUTION_CODE":"INSTITUTION_CODE","FEDERAL_SCHOOL_CODE":"FEDERAL_SCHOOL_CODE","FA_SCHOOL_CODE":"FA_SCHOOL_CODE","AMBASSADOR_SCHOOL_CODE":"AMBASSADOR_SCHOOL_CODE","COSMO_LICENSE":"COSMO_LICENSE","ADDRESS":"Yes","COMPLETED":"ADDRESS","ADDRESS_1":"ADDRESS_1","CITY":"CITY","STATES_ID":"1","ZIP":"ZIP","COUNTRY_ID":"1","PHONE":"PHONE","FAX":"FAX","PRIMARY_CAMPUS":"Yes","ACCSC_SCHOOL_NUMBER":"ACCSC_SCHOOL_NUMBER","ACICS_SCHOOL_NUMBER":"ACICS_SCHOOL_NUMBER","NACCAS_SCHOOL_NUMBER":"NACCAS_SCHOOL_NUMBER,"TIMEZONE_ID":"21"}';

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
	
	if($DATA->GROUP_NAME == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'GROUP_NAME Missing';
	}

	if($DATA->PROGRAM_ID != '') {
		$PK_CAMPUS_PROGRAM = trim($DATA->PROGRAM_ID);
		$res = $db->Execute("SELECT PK_CAMPUS_PROGRAM FROM M_CAMPUS_PROGRAM WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' ");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid PROGRAM_ID Value';
		}
	}
	
	if($data['SUCCESS'] == 1) {
		
		$STUDENT_GROUP['STUDENT_GROUP'] 	= trim($DATA->GROUP_NAME);
		$STUDENT_GROUP['PK_CAMPUS_PROGRAM'] = trim($DATA->PROGRAM_ID);
		$STUDENT_GROUP['NOTES'] 			= trim($DATA->DESCRIPTION);
		$STUDENT_GROUP['PK_ACCOUNT']  		= $PK_ACCOUNT;
		$STUDENT_GROUP['CREATED_ON']  		= date("Y-m-d H:i");
		db_perform('M_STUDENT_GROUP', $STUDENT_GROUP, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		$data['MESSAGE'] 	 = 'Student Group Created';
	}
}

$data = json_encode($data);
echo $data;
