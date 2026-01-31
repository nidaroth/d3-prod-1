<? require_once("../global/config.php"); 
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING = $_REQUEST['co'];
$res_type = $db->Execute("select PK_STUDENT_COURSE from S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
if($res_type->RecordCount() > 0)
	echo "a";
else
	echo "b";