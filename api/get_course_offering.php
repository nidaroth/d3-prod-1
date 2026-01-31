<?php require_once("../global/config.php"); 
$API_KEY = $_GET['key'];

$res_user = $db->Execute("SELECT PK_USER,PK_ACCOUNT,ID FROM Z_USER where USER_API_KEY = '$API_KEY' ");
$PK_ACCOUNT 		= $res_user->fields['PK_ACCOUNT'];
$PK_USER 			= $res_user->fields['PK_USER'];
$PK_EMPLOYEE_MASTER = $res_user->fields['ID'];

if($PK_USER == 0 || $PK_USER == '' || $PK_EMPLOYEE_MASTER == 0 || $PK_EMPLOYEE_MASTER == '') {
	$RET_DATA['STATUS']  = 0;
	$RET_DATA['MESSAGE'] = 'Something went Wrong';
} else {
	$RET_DATA['STATUS']  = 1;
	$RET_DATA['MESSAGE'] = '';
	
	$res_type = $db->Execute("select S_COURSE_OFFERING.PK_COURSE_OFFERING,COURSE_CODE,SESSION, SESSION_NO from 

	S_COURSE_OFFERING
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
	LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING 

	WHERE 
	S_COURSE_OFFERING.PK_ACCOUNT = '$PK_ACCOUNT' AND (INSTRUCTOR = '$PK_EMPLOYEE_MASTER' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$PK_EMPLOYEE_MASTER') GROUP BY S_COURSE_OFFERING.PK_COURSE_OFFERING ORDER BY COURSE_CODE ASC");
	$i = 0;
	while (!$res_type->EOF) { 
		
		$RET_DATA['COURSE_OFFERING'][$i]['PK_COURSE_OFFERING'] 	= $res_type->fields['PK_COURSE_OFFERING'];
		$RET_DATA['COURSE_OFFERING'][$i]['TEXT'] 				= $res_type->fields['COURSE_CODE'].' ('.$res_type->fields['SESSION'].' - '.$res_type->fields['SESSION_NO'].')';
		
		$i++;
		$res_type->MoveNext();
	} 
}

echo json_encode($RET_DATA);