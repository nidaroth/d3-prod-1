<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){  
	header("location:../index");
	exit;
}

$PK_STUDENT_ENROLLMENT 	= $_REQUEST['PK_STUDENT_ENROLLMENT'];
$fee_budge_id			= $_REQUEST['fee_budge_id'];
$sid					= $_REQUEST['sid'];

$res_prog_fee = $db->Execute("select PK_CAMPUS_PROGRAM_FEE,PK_STUDENT_ENROLLMENT from S_STUDENT_ENROLLMENT, M_CAMPUS_PROGRAM_FEE WHERE PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) AND M_CAMPUS_PROGRAM_FEE.ACTIVE = 1 AND M_CAMPUS_PROGRAM_FEE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = M_CAMPUS_PROGRAM_FEE.PK_CAMPUS_PROGRAM ");

while (!$res_prog_fee->EOF) { 
	$_REQUEST['fee_budge_id'] 	  		= $fee_budge_id;
	$_REQUEST['sid'] 	  				= $sid;
	$_REQUEST['NEW'] 	  				= 1;
	$_REQUEST['PK_CAMPUS_PROGRAM_FEE'] 	= $res_prog_fee->fields['PK_CAMPUS_PROGRAM_FEE'];
	$_REQUEST['PK_STUDENT_ENROLLMENT'] 	= $res_prog_fee->fields['PK_STUDENT_ENROLLMENT'];

	include("ajax_student_fee_budget.php");
	$fee_budge_id++;
	
	$res_prog_fee->MoveNext();
}
echo "|||".$fee_budge_id;