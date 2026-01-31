<? require_once("../global/config.php"); 

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
} 
$PK_COURSE  = $_REQUEST['PK_COURSE'];
$res_type = $db->Execute("select COURSE_DESCRIPTION from S_COURSE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE = '$PK_COURSE' ");
echo $res_type->fields['COURSE_DESCRIPTION'];