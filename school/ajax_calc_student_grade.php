<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}
require_once("function_calc_student_grade.php"); 
echo calc_stu_grade($_REQUEST['points'],$_REQUEST['pk_grade'],$_REQUEST['sc'],$_REQUEST['sm'],0);
?>