<? require_once("../global/config.php"); 

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
} 
$PK_COURSE_OFFERING  = $_REQUEST['id'];
$res_type = $db->Execute("select COURSE_DESCRIPTION from S_COURSE_OFFERING, S_COURSE WHERE S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE ");
echo $res_type->fields['COURSE_DESCRIPTION'];