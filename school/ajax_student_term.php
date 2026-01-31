<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3 && $_SESSION['PK_ROLES'] != 4 && $_SESSION['PK_ROLES'] != 5)){ 
	header("location:../index");
	exit;
}

$PK_STUDENT_ENROLLMENT = $_REQUEST['eid'];
$res_type = $db->Execute("select ENROLLMENT_PK_TERM_BLOCK  FROM S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");

echo $res_type->fields['ENROLLMENT_PK_TERM_BLOCK'];