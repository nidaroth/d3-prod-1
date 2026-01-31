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

	$res = $db->Execute("SELECT PK_STUDENT_STATUS,STUDENT_STATUS, CODE,FA_STATUS,IF(ADMISSIONS = 1,'Yes','') AS ADMISSIONS, IF(POST_TUITION = 1,'Yes','') AS POST_TUITION, IF(DOC_28_1 = 1,'Yes','') AS DOC_28_1, IF(CLASS_ENROLLMENT = 1,'Yes','No') AS CLASS_ENROLLMENT, IF(ALLOW_ATTENDANCE = 1,'Yes','No') AS ALLOW_ATTENDANCE, IF(_1098T = 1,'Yes','No') AS _1098T, IF(COMPLETED = 1,'Yes','No') AS COMPLETED, M_STUDENT_STATUS.DESCRIPTION, IF(ALLOW_ATTENDANCE = 1,'Yes','No') AS ALLOW_ATTENDANCE, IF(_1098T = 1,'Yes','No') AS _1098T FROM M_STUDENT_STATUS LEFT JOIN M_END_DATE ON M_END_DATE.PK_END_DATE = M_STUDENT_STATUS.PK_END_DATE WHERE  M_STUDENT_STATUS.PK_ACCOUNT = '$PK_ACCOUNT' AND M_STUDENT_STATUS.ACTIVE = 1 ");
	
	$i = 0;
	while (!$res->EOF) { 
		$data['STUDENT_STATUS'][$i]['ID'] 					= $res->fields['PK_STUDENT_STATUS'];
		$data['STUDENT_STATUS'][$i]['STUDENT_STATUS'] 		= $res->fields['STUDENT_STATUS'];
		$data['STUDENT_STATUS'][$i]['DESCRIPTION'] 			= $res->fields['DESCRIPTION'];
		$data['STUDENT_STATUS'][$i]['END_DATE'] 			= $res->fields['CODE'];
		$data['STUDENT_STATUS'][$i]['FA_STATUS'] 			= $res->fields['FA_STATUS'];
		$data['STUDENT_STATUS'][$i]['ADMISSIONS'] 			= $res->fields['ADMISSIONS'];
		$data['STUDENT_STATUS'][$i]['POST_TUITION'] 		= $res->fields['POST_TUITION'];
		$data['STUDENT_STATUS'][$i]['DOC_28_1'] 			= $res->fields['DOC_28_1'];
		$data['STUDENT_STATUS'][$i]['CLASS_ENROLLMENT'] 	= $res->fields['CLASS_ENROLLMENT'];
		$data['STUDENT_STATUS'][$i]['_1098T'] 				= $res->fields['_1098T'];
		$data['STUDENT_STATUS'][$i]['ALLOW_ATTENDANCE'] 	= $res->fields['ALLOW_ATTENDANCE'];
		$data['STUDENT_STATUS'][$i]['COMPLETED'] 			= $res->fields['COMPLETED'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;