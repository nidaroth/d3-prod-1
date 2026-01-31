<?php require_once("../global/config.php"); 
require_once("../school/function_attendance.php");

$API_KEY = $_GET['key'];

$DATA = (file_get_contents('php://input'));

//$DATA = '{"STUDENT":[{"PK_STUDENT_SCHEDULE":"345","ATTENDANCE_HOURS":"3","PK_ATTENDANCE_CODE":"3","COMPLETED":"0"},{"PK_STUDENT_SCHEDULE":"248","ATTENDANCE_HOURS":"2","PK_ATTENDANCE_CODE":"4","COMPLETED":"1"}]}';

$DATA = urldecode($DATA);
$DATA = json_decode($DATA);
//print_r($DATA);

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
	
	foreach($DATA->STUDENT as $STUDENT) {
		/*echo $STUDENT->PK_STUDENT_SCHEDULE;
		echo "<br />";
		echo $STUDENT->ATTENDANCE_HOURS;
		echo "<br />";
		echo $STUDENT->PK_ATTENDANCE_CODE;
		echo "<br />";
		echo $STUDENT->COMPLETED;
		echo "<br />";*/
		
		$PK_STUDENT_SCHEDULE 	= $STUDENT->PK_STUDENT_SCHEDULE; 
		$ATTENDANCE_HOURS 		= $STUDENT->ATTENDANCE_HOURS; 
		$PK_ATTENDANCE_CODE 	= $STUDENT->PK_ATTENDANCE_CODE; 
		$COMPLETED 				= $STUDENT->COMPLETED; 
		
		$res_att = $db->Execute("SELECT PK_STUDENT_ATTENDANCE FROM S_STUDENT_ATTENDANCE WHERE PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' "); 
		$PK_STUDENT_ATTENDANCE 	= $res_att->fields['PK_STUDENT_ATTENDANCE']; 
		
		$res_att = $db->Execute("SELECT * FROM S_STUDENT_SCHEDULE WHERE PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' "); 
		$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $res_att->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL']; 
		$PK_STUDENT_MASTER 					= $res_att->fields['PK_STUDENT_MASTER']; 
		$PK_STUDENT_ENROLLMENT 				= $res_att->fields['PK_STUDENT_ENROLLMENT']; 
		
		attendance_entry($PK_COURSE_OFFERING_SCHEDULE_DETAIL,$COMPLETED,$PK_STUDENT_ATTENDANCE,$PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT,$PK_STUDENT_SCHEDULE,$ATTENDANCE_HOURS,$PK_ATTENDANCE_CODE,$PK_ACCOUNT,$PK_USER);
		
	}
}

echo json_encode($RET_DATA);