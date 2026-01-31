<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("function_update_estimate_fee_status.php");
require_once("function_student_ledger.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){  
	header("location:../index");
	exit;
}

$PK_STUDENT_MASTER 		= $_REQUEST['sid'];
$PK_STUDENT_ENROLLMENT 	= $_REQUEST['eid'];

if($_REQUEST['type'] == 4) {
	//delete unpaid award
	$ledger_data_del = array();
	//AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT'
	$res_disb = $db->Execute("SELECT PK_STUDENT_FEE_BUDGET FROM S_STUDENT_FEE_BUDGET WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_ESTIMATE_FEE_STATUS IN (0,2) "); 
	while (!$res_disb->EOF) {
		$PK_STUDENT_FEE_BUDGET = $res_disb->fields['PK_STUDENT_FEE_BUDGET'];
		
		$db->Execute("DELETE FROM S_STUDENT_FEE_BUDGET WHERE PK_STUDENT_FEE_BUDGET = '$PK_STUDENT_FEE_BUDGET' "); 
		
		$ledger_data_del['PK_STUDENT_FEE_BUDGET'] = $PK_STUDENT_FEE_BUDGET;
		delete_student_ledger($ledger_data_del);
		
		$res_disb->MoveNext();
	}
} else if($_REQUEST['type'] == 5) {
	//unapprove
	$ledger_data_del = array();
	//AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT'
	$res_disb = $db->Execute("UPDATE S_STUDENT_FEE_BUDGET SET PK_ESTIMATE_FEE_STATUS = 0, FEE_BUDGET_APPROVED_DATE='', FEE_BUDGET_DEPOSITED_DATE=''  WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_ESTIMATE_FEE_STATUS IN (2) "); 
	
}
update_disbursement_status($PK_STUDENT_MASTER,'');