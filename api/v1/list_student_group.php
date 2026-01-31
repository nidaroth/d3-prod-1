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

	$res = $db->Execute("SELECT PK_STUDENT_GROUP,STUDENT_GROUP,CODE,M_STUDENT_GROUP.NOTES, M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM FROM M_STUDENT_GROUP LEFT JOIN M_CAMPUS_PROGRAM on M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = M_STUDENT_GROUP.PK_CAMPUS_PROGRAM WHERE  M_STUDENT_GROUP.PK_ACCOUNT = '$PK_ACCOUNT' AND M_STUDENT_GROUP.ACTIVE = 1 ");
	
	$i = 0;
	while (!$res->EOF) { 
		$data['STUDENT_GROUP'][$i]['ID'] 			= $res->fields['PK_STUDENT_GROUP'];
		$data['STUDENT_GROUP'][$i]['GROUP_NAME'] 	= $res->fields['STUDENT_GROUP'];
		$data['STUDENT_GROUP'][$i]['PROGRAM_CODE'] 	= $res->fields['CODE'];
		$data['STUDENT_GROUP'][$i]['PROGRAM_ID'] 	= $res->fields['PK_CAMPUS_PROGRAM'];
		$data['STUDENT_GROUP'][$i]['DESCRIPTION'] 	= $res->fields['NOTES'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;