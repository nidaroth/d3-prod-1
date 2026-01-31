<? require_once("../global/config.php"); 
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3 && $_SESSION['PK_ROLES'] != 4 && $_SESSION['PK_ROLES'] != 5)){ 
	header("location:../index");
	exit;
}

if($_REQUEST['eid'] != '') {
	$PK_STUDENT_ENROLLMENT = $_REQUEST['eid'];
	$res_type = $db->Execute("select SSN  FROM S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
} else {
	$PK_STUDENT_MASTER = $_REQUEST['sid'];
	$res_type = $db->Execute("select SSN  FROM S_STUDENT_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
}
$SSN = $res_type->fields['SSN'];
if($SSN != '') {
	$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'],$SSN);
	$SSN_ORG = $SSN;
	$SSN_ARR = explode("-",$SSN);
	$SSN 	 = 'xxx-xx-'.$SSN_ARR[2];
}
echo $SSN;