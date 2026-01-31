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
	
	if($DATA->CAMPUS_NAME == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Campus Name Missing';
	}

	if($DATA->PRIMARY_CAMPUS == '') {
		$PRIMARY_CAMPUS = 0;
	} else {
		if(strtolower($DATA->PRIMARY_CAMPUS) == 'yes') 
			$PRIMARY_CAMPUS = 1;
		else if(strtolower($DATA->PRIMARY_CAMPUS) == 'no') 
			$PRIMARY_CAMPUS = 0;
		else {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Primary Campus Value';
		}
	}
	
	if($DATA->STATES_ID != '') {
		$PK_STATES = trim($DATA->STATES_ID);
		$res = $db->Execute("SELECT PK_STATES FROM Z_STATES WHERE  ACTIVE = 1 AND PK_STATES = '$PK_STATES' ");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid State ID';
		}
	}
	
	if($DATA->COUNTRY_ID != '') {
		$PK_COUNTRY = trim($DATA->COUNTRY_ID);
		$res = $db->Execute("SELECT PK_COUNTRY FROM Z_COUNTRY WHERE  ACTIVE = 1 AND PK_COUNTRY = '$PK_COUNTRY' ");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Country ID';
		}
	}
	
	if($DATA->TIMEZONE_ID != '') {
		$PK_TIMEZONE = trim($DATA->TIMEZONE_ID);
		$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_TIMEZONE WHERE  ACTIVE = 1 AND PK_TIMEZONE = '$PK_TIMEZONE' ");
		if($res->RecordCount() == 0) {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid Timezone ID';
		}
	}
	
	if($data['SUCCESS'] == 1) {
		if($PRIMARY_CAMPUS == 1){
			$CAMPUS1['PRIMARY_CAMPUS'] = 0;
			db_perform('S_CAMPUS', $CAMPUS1, 'update'," PK_ACCOUNT = '$PK_ACCOUNT' ");
		}
		$CAMPUS['OFFICIAL_CAMPUS_NAME'] 	= trim($DATA->OFFICIAL_CAMPUS_NAME);
		$CAMPUS['CAMPUS_NAME'] 				= trim($DATA->CAMPUS_NAME);
		$CAMPUS['CAMPUS_CODE'] 				= trim($DATA->CAMPUS_CODE);
		$CAMPUS['SCHOOL_CODE'] 				= trim($DATA->SCHOOL_CODE);
		$CAMPUS['INSTITUTION_CODE'] 		= trim($DATA->INSTITUTION_CODE);
		$CAMPUS['FEDERAL_SCHOOL_CODE'] 		= trim($DATA->FEDERAL_SCHOOL_CODE);
		$CAMPUS['FA_SCHOOL_CODE'] 			= trim($DATA->FA_SCHOOL_CODE);
		$CAMPUS['AMBASSADOR_SCHOOL_CODE'] 	= trim($DATA->AMBASSADOR_SCHOOL_CODE);
		$CAMPUS['COSMO_LICENSE'] 			= trim($DATA->COSMO_LICENSE);
		$CAMPUS['ADDRESS'] 					= trim($DATA->ADDRESS);
		$CAMPUS['ADDRESS_1'] 				= trim($DATA->ADDRESS_1);
		$CAMPUS['CITY'] 					= trim($DATA->CITY);
		$CAMPUS['PK_STATES'] 				= $PK_STATES;
		$CAMPUS['ZIP'] 						= trim($DATA->ZIP);
		$CAMPUS['PK_COUNTRY'] 				= $PK_COUNTRY;
		$CAMPUS['PHONE'] 					= trim($DATA->PHONE);
		
		$CAMPUS['FAX'] 						= trim($DATA->FAX);
		$CAMPUS['PRIMARY_CAMPUS'] 			= $PRIMARY_CAMPUS;
		$CAMPUS['ACCSC_SCHOOL_NUMBER'] 		= trim($DATA->ACCSC_SCHOOL_NUMBER);
		$CAMPUS['ACICS_SCHOOL_NUMBER'] 		= trim($DATA->ACICS_SCHOOL_NUMBER);
		$CAMPUS['NACCAS_SCHOOL_NUMBER'] 	= trim($DATA->NACCAS_SCHOOL_NUMBER);
		$CAMPUS['PK_TIMEZONE'] 				= $PK_TIMEZONE;
		
		if($CAMPUS['PHONE'] != '') {
			$CAMPUS['PHONE'] = preg_replace( '/[^0-9]/', '',$CAMPUS['PHONE']);
			$PHONE = $CAMPUS['PHONE'];
			
			$PHONE = '('.$PHONE[0].$PHONE[1].$PHONE[2].') '.$PHONE[3].$PHONE[4].$PHONE[5].'-'.$PHONE[6].$PHONE[7].$PHONE[8].$PHONE[9];
				
			$CAMPUS['PHONE'] = $PHONE;
		}
		
		if($CAMPUS['FAX'] != '') {
			$CAMPUS['FAX'] = preg_replace( '/[^0-9]/', '',$CAMPUS['FAX']);
			$FAX = $CAMPUS['FAX'];
			
			$FAX = '('.$FAX[0].$FAX[1].$FAX[2].') '.$FAX[3].$FAX[4].$FAX[5].'-'.$FAX[6].$FAX[7].$FAX[8].$FAX[9];
				
			$CAMPUS['FAX'] = $FAX;
		}
		
		$CAMPUS['PK_ACCOUNT']  	 			= $PK_ACCOUNT;
		$CAMPUS['CREATED_ON'] 	 			= date("Y-m-d H:i");
		db_perform('S_CAMPUS', $CAMPUS, 'insert');
		
		$data['INTERNAL_ID'] = $db->insert_ID();
		$data['MESSAGE'] 	 = 'Campus Created';
	}
}

$data = json_encode($data);
echo $data;
