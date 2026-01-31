<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
	header("location:../index");
	exit;
}

$PK_CAMPUS_PROGRAM 	= $_REQUEST['PK_CAMPUS_PROGRAM'];
$PK_TERM_MASTER		= $_REQUEST['PK_TERM_MASTER'];
$fee_budge_id		= $_REQUEST['fee_budge_id'];

$res_prog_fee = $db->Execute("select PK_CAMPUS_PROGRAM_FEE from M_CAMPUS_PROGRAM_FEE WHERE PK_CAMPUS_PROGRAM IN ($PK_CAMPUS_PROGRAM) AND ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

while (!$res_prog_fee->EOF) { 
	$_REQUEST['fee_budge_id'] 	  		= $fee_budge_id;
	$_REQUEST['NEW'] 	  				= 1;
	$_REQUEST['PK_CAMPUS_PROGRAM_FEE'] 	= $res_prog_fee->fields['PK_CAMPUS_PROGRAM_FEE'];
	$_REQUEST['PK_TERM_MASTER'] 		= $PK_TERM_MASTER;

	include("ajax_student_fee_budget.php");
	$fee_budge_id++;
	
	$res_prog_fee->MoveNext();
}
echo "|||".$fee_budge_id;