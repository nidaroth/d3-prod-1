<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

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

	$res = $db->Execute("SELECT PK_CAMPUS, OFFICIAL_CAMPUS_NAME, CAMPUS_NAME, CAMPUS_CODE, SCHOOL_CODE, INSTITUTION_CODE, FEDERAL_SCHOOL_CODE, FA_SCHOOL_CODE, AMBASSADOR_SCHOOL_CODE, COSMO_LICENSE, ADDRESS, ADDRESS_1, CITY, ZIP, PHONE, IF(PRIMARY_CAMPUS = 1,'Yes','No') as PRIMARY_CAMPUS, ACCSC_SCHOOL_NUMBER, ACICS_SCHOOL_NUMBER, NACCAS_SCHOOL_NUMBER, TIMEZONE, S_CAMPUS.PK_TIMEZONE, S_CAMPUS.PK_STATES, S_CAMPUS.PK_COUNTRY, STATE_CODE, Z_COUNTRY.NAME as COUNTRY, Z_TIMEZONE.NAME AS TIMEZONE FROM S_CAMPUS LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_CAMPUS.PK_STATES LEFT JOIN Z_COUNTRY ON Z_COUNTRY.PK_COUNTRY = S_CAMPUS.PK_COUNTRY LEFT JOIN Z_TIMEZONE ON Z_TIMEZONE.PK_TIMEZONE = S_CAMPUS.PK_TIMEZONE WHERE  PK_ACCOUNT = '$PK_ACCOUNT' AND S_CAMPUS.ACTIVE = 1 ");
	
	$i = 0;
	while (!$res->EOF) { 
		$data['CAMPUS'][$i]['ID'] 						= $res->fields['PK_CAMPUS'];
		$data['CAMPUS'][$i]['OFFICIAL_CAMPUS_NAME'] 	= $res->fields['OFFICIAL_CAMPUS_NAME'];
		$data['CAMPUS'][$i]['CAMPUS_NAME'] 				= $res->fields['CAMPUS_NAME'];
		$data['CAMPUS'][$i]['CAMPUS_CODE'] 				= $res->fields['CAMPUS_CODE'];
		$data['CAMPUS'][$i]['SCHOOL_CODE'] 				= $res->fields['SCHOOL_CODE'];
		$data['CAMPUS'][$i]['INSTITUTION_CODE'] 		= $res->fields['INSTITUTION_CODE'];
		$data['CAMPUS'][$i]['FEDERAL_SCHOOL_CODE'] 		= $res->fields['FEDERAL_SCHOOL_CODE'];
		$data['CAMPUS'][$i]['FA_SCHOOL_CODE'] 			= $res->fields['FA_SCHOOL_CODE'];
		$data['CAMPUS'][$i]['AMBASSADOR_SCHOOL_CODE'] 	= $res->fields['AMBASSADOR_SCHOOL_CODE'];
		$data['CAMPUS'][$i]['COSMO_LICENSE'] 			= $res->fields['COSMO_LICENSE'];
		$data['CAMPUS'][$i]['ADDRESS'] 					= $res->fields['ADDRESS'];
		$data['CAMPUS'][$i]['ADDRESS_1'] 				= $res->fields['ADDRESS_1'];
		
		$data['CAMPUS'][$i]['CITY'] 					= $res->fields['CITY'];
		$data['CAMPUS'][$i]['STATE_NAME'] 				= $res->fields['STATE_CODE'];
		$data['CAMPUS'][$i]['STATES_ID'] 				= $res->fields['PK_STATES'];
		$data['CAMPUS'][$i]['ZIP'] 						= $res->fields['ZIP'];
		$data['CAMPUS'][$i]['COUNTRY_NAME'] 			= $res->fields['COUNTRY'];
		$data['CAMPUS'][$i]['COUNTRY_ID'] 				= $res->fields['PK_COUNTRY'];
		$data['CAMPUS'][$i]['PHONE'] 					= $res->fields['PHONE'];
		$data['CAMPUS'][$i]['FAX'] 						= $res->fields['FAX'];
		$data['CAMPUS'][$i]['PRIMARY_CAMPUS'] 			= $res->fields['PRIMARY_CAMPUS'];
		$data['CAMPUS'][$i]['ACCSC_SCHOOL_NUMBER'] 		= $res->fields['ACCSC_SCHOOL_NUMBER'];
		$data['CAMPUS'][$i]['ACICS_SCHOOL_NUMBER'] 		= $res->fields['ACICS_SCHOOL_NUMBER'];
		$data['CAMPUS'][$i]['NACCAS_SCHOOL_NUMBER'] 	= $res->fields['NACCAS_SCHOOL_NUMBER'];
		$data['CAMPUS'][$i]['TIMEZONE_ID'] 				= $res->fields['PK_TIMEZONE'];
		$data['CAMPUS'][$i]['TIMEZONE'] 				= $res->fields['TIMEZONE'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;