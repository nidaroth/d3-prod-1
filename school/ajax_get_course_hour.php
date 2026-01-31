<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
	header("location:../index");
	exit;
}

$res = $db->Execute("SELECT HOURS,PK_ATTENDANCE_CODE,PK_ATTENDANCE_TYPE,MAX_CLASS_SIZE, LMS_COURSE_TEMPLATE_ID FROM S_COURSE WHERE PK_COURSE = '$_REQUEST[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
echo $res->fields['HOURS'].'|||'.$res->fields['PK_ATTENDANCE_CODE'].'|||'.$res->fields['PK_ATTENDANCE_TYPE'].'|||'.$res->fields['MAX_CLASS_SIZE'].'|||'.$res->fields['LMS_COURSE_TEMPLATE_ID'];