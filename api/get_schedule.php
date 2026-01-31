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
	
	$res_type = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL, CONCAT(DATE_FORMAT(SCHEDULE_DATE,'%m/%d/%Y'),' ',DATE_FORMAT(START_TIME,'%h:%i %p'),' - ',DATE_FORMAT(END_TIME,'%h:%i %p')) AS SCHEDULE_DATE from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_COURSE_OFFERING = '$_GET[id]' ORDER BY SCHEDULE_DATE ASC, START_TIME ASC");
	$i = 0;
	while (!$res_type->EOF) { 
		
		$RET_DATA['SCHEDULE'][$i]['PK_COURSE_OFFERING_SCHEDULE_DETAIL'] 	= $res_type->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
		$RET_DATA['SCHEDULE'][$i]['TEXT'] 									= $res_type->fields['SCHEDULE_DATE'];
		
		$i++;
		$res_type->MoveNext();
	} 
}

echo json_encode($RET_DATA);