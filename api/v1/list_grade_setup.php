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

	$res = $db->Execute("SELECT PK_GRADE,GRADE,NUMBER_GRADE, IF(CALCULATE_GPA = 1,'Yes','No') as CALCULATE_GPA, IF(UNITS_ATTEMPTED = 1,'Yes','No') as UNITS_ATTEMPTED, IF(UNITS_COMPLETED = 1,'Yes','No') as UNITS_COMPLETED, IF(UNITS_IN_PROGRESS = 1,'Yes','No') as UNITS_IN_PROGRESS, IF(WEIGHTED_GRADE_CALC = 1,'Yes','No') as WEIGHTED_GRADE_CALC, IF(RETAKE_UPDATE = 1,'Yes','No') as RETAKE_UPDATE, IF(IS_DEFAULT = 1,'Yes','No') as IS_DEFAULT, DISPLAY_ORDER FROM S_GRADE WHERE PK_ACCOUNT = '$PK_ACCOUNT' ORDER BY DISPLAY_ORDER ASC");
	$i = 0;
	while (!$res->EOF) { 
		$data['GRADE_SETUP'][$i]['ID'] 			 		= $res->fields['PK_GRADE'];
		$data['GRADE_SETUP'][$i]['GRADE'] 	 			= $res->fields['GRADE'];
		$data['GRADE_SETUP'][$i]['NUMBER_GRADE'] 	 	= $res->fields['NUMBER_GRADE'];
		
		$data['GRADE_SETUP'][$i]['CALCULATE_GPA'] 		= $res->fields['CALCULATE_GPA'];
		$data['GRADE_SETUP'][$i]['UNITS_ATTEMPTED'] 	= $res->fields['UNITS_ATTEMPTED'];
		$data['GRADE_SETUP'][$i]['UNITS_COMPLETED'] 	= $res->fields['UNITS_COMPLETED'];
		$data['GRADE_SETUP'][$i]['UNITS_IN_PROGRESS'] 	= $res->fields['UNITS_IN_PROGRESS'];
		$data['GRADE_SETUP'][$i]['WEIGHTED_GRADE_CALC'] = $res->fields['WEIGHTED_GRADE_CALC'];
		$data['GRADE_SETUP'][$i]['RETAKE_UPDATE'] 		= $res->fields['RETAKE_UPDATE'];
		$data['GRADE_SETUP'][$i]['IS_DEFAULT'] 			= $res->fields['IS_DEFAULT'];
		
		$data['GRADE_SETUP'][$i]['DISPLAY_ORDER'] 		= $res->fields['DISPLAY_ORDER'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;