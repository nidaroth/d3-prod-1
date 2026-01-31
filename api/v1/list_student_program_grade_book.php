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
	
	$cond = " AND PK_STUDENT_ENROLLMENT = '$_GET[id]' ";

	$res = $db->Execute("SELECT PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT, COMPLETED_DATE, SESSION_COMPLETED, HOUR_COMPLETED, POINTS_COMPLETED, SESSION_REQUIRED, HOUR_REQUIRED, POINTS_REQUIRED, M_GRADE_BOOK_CODE.CODE as GRADE_BOOK_CODE, GRADE_BOOK_TYPE, S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.DESCRIPTION FROM 
	S_STUDENT_PROGRAM_GRADE_BOOK_INPUT 
	LEFT JOIN M_GRADE_BOOK_CODE ON M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE  
	LEFT JOIN M_GRADE_BOOK_TYPE ON M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE  
	where M_GRADE_BOOK_CODE.PK_ACCOUNT = '$PK_ACCOUNT' $cond  ");
	$i = 0;
	while (!$res->EOF) { 
		$data['STUDENT_PROGRAM_GRADE_BOOK_INPUT'][$i]['ID'] 						= $res->fields['PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT'];
		$data['STUDENT_PROGRAM_GRADE_BOOK_INPUT'][$i]['COMPLETED_DATE'] 			= $res->fields['COMPLETED_DATE'];
		$data['STUDENT_PROGRAM_GRADE_BOOK_INPUT'][$i]['SESSIONS_COMPLETED'] 		= $res->fields['SESSION_COMPLETED'];
		$data['STUDENT_PROGRAM_GRADE_BOOK_INPUT'][$i]['HOURS_COMPLETED'] 			= $res->fields['HOUR_COMPLETED'];
		$data['STUDENT_PROGRAM_GRADE_BOOK_INPUT'][$i]['POINTS_EARNED'] 				= $res->fields['POINTS_COMPLETED'];
		$data['STUDENT_PROGRAM_GRADE_BOOK_INPUT'][$i]['SESSIONS_REQUIRED'] 			= $res->fields['SESSION_REQUIRED'];
		$data['STUDENT_PROGRAM_GRADE_BOOK_INPUT'][$i]['HOUR_REQUIRED'] 				= $res->fields['HOUR_REQUIRED'];
		$data['STUDENT_PROGRAM_GRADE_BOOK_INPUT'][$i]['POINTS_REQUIRED'] 			= $res->fields['POINTS_REQUIRED'];
		$data['STUDENT_PROGRAM_GRADE_BOOK_INPUT'][$i]['GRADE_BOOK_CODE_ID'] 		= $res->fields['GRADE_BOOK_CODE'];
		$data['STUDENT_PROGRAM_GRADE_BOOK_INPUT'][$i]['GRADE_BOOK_CODE_DESCRIPTION']= $res->fields['DESCRIPTION'];
		$data['STUDENT_PROGRAM_GRADE_BOOK_INPUT'][$i]['GRADE_BOOK_TYPE'] 			= $res->fields['GRADE_BOOK_TYPE'];
		
		$i++;
		$res->MoveNext();
	}
}

$data = json_encode($data);
echo $data;