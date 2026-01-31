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

	$res = $db->Execute("SELECT PK_COURSE_OFFERING_STUDENT_STATUS,PK_COURSE_OFFERING_STUDENT_STATUS_MASTER,COURSE_OFFERING_STUDENT_STATUS,DESCRIPTION ,IF(MAKE_AS_DEFAULT = 1,'Yes','No') AS MAKE_AS_DEFAULT ,IF(POST_TUITION = 1,'Yes','No') AS POST_TUITION,IF(SHOW_ON_TRANSCRIPT = 1,'Yes','No') AS SHOW_ON_TRANSCRIPT ,IF(SHOW_ON_REPORT_CARD = 1,'Yes','No') AS SHOW_ON_REPORT_CARD ,IF(CALCULATE_SAP = 1,'Yes','No') AS CALCULATE_SAP FROM M_COURSE_OFFERING_STUDENT_STATUS WHERE  PK_ACCOUNT = '$PK_ACCOUNT' AND ACTIVE = 1 ");
	$i = 0;
	while (!$res->EOF) { 
		$data['COURSE_OFFERING_STUDENT_STATUS'][$i]['ID'] 					= $res->fields['PK_COURSE_OFFERING_STUDENT_STATUS'];
		$data['COURSE_OFFERING_STUDENT_STATUS'][$i]['STATUS'] 				= $res->fields['COURSE_OFFERING_STUDENT_STATUS'];
		$data['COURSE_OFFERING_STUDENT_STATUS'][$i]['DESCRIPTION'] 			= $res->fields['DESCRIPTION'];
		$data['COURSE_OFFERING_STUDENT_STATUS'][$i]['POST_TUITION'] 		= $res->fields['POST_TUITION'];
		$data['COURSE_OFFERING_STUDENT_STATUS'][$i]['SHOW_ON_TRANSCRIPT'] 	= $res->fields['SHOW_ON_TRANSCRIPT'];
		$data['COURSE_OFFERING_STUDENT_STATUS'][$i]['SHOW_ON_REPORT_CARD'] 	= $res->fields['SHOW_ON_REPORT_CARD'];
		$data['COURSE_OFFERING_STUDENT_STATUS'][$i]['CALCULATE_SAP'] 		= $res->fields['CALCULATE_SAP'];
		$data['COURSE_OFFERING_STUDENT_STATUS'][$i]['MAKE_AS_DEFAULT'] 		= $res->fields['MAKE_AS_DEFAULT'];;
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;