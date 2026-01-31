<? require_once("../global/config.php"); 
require_once("function_add_student_to_course_offering.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2) ){ 
	header("location:../index");
	exit;
}

$PK_STUDENT_ENROLLMENT[] = $_REQUEST['eid'];
add_student_to_course_offering($_REQUEST['cid'],$PK_STUDENT_ENROLLMENT);
$db->Execute("DELETE FROM S_COURSE_OFFERING_WAITING_LIST WHERE PK_COURSE_OFFERING_WAITING_LIST = '$_REQUEST[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");