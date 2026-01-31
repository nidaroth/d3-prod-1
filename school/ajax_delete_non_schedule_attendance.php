<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/non_scheduled_attendance.php");
require_once("function_attendance.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0 ){
	header("location:../index");
	exit;
}

$PK_STUDENT_ATTENDANCE = $_POST['id'];
$res = $db->Execute("select PK_STUDENT_SCHEDULE FROM S_STUDENT_ATTENDANCE WHERE PK_STUDENT_ATTENDANCE = '$PK_STUDENT_ATTENDANCE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = 0");
if($res->RecordCount() > 0) {
	$PK_STUDENT_SCHEDULE = $res->fields['PK_STUDENT_SCHEDULE'];
	$db->Execute("DELETE FROM S_STUDENT_ATTENDANCE WHERE PK_STUDENT_ATTENDANCE = '$PK_STUDENT_ATTENDANCE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$db->Execute("DELETE FROM S_STUDENT_SCHEDULE WHERE PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
}