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
$db->Execute("DELETE FROM S_STUDENT_ATTENDANCE WHERE PK_STUDENT_ATTENDANCE = '$PK_STUDENT_ATTENDANCE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");