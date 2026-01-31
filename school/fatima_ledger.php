<? 

//$db_uat=mysqli_connect('184.73.75.183','root','DSISMySQLPa$$1!','DSIS');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once("../global/config.php"); 
require_once("function_student_ledger.php");
require_once("function_update_disbursement_status.php");

$sql="SELECT * FROM S_PAYMENT_BATCH_DETAIL spbd JOIN S_STUDENT_DISBURSEMENT ssd ON spbd .PK_STUDENT_DISBURSEMENT = ssd.PK_STUDENT_DISBURSEMENT WHERE ssd.PK_STUDENT_MASTER ='774736' AND ssd.PK_STUDENT_ENROLLMENT = '985991'"; 

$res_type = $db->Execute($sql);
$i=0;

while (!$res_type->EOF) { 

		$ledger_data['PK_PAYMENT_BATCH_DETAIL'] = $res_type->fields['PK_PAYMENT_BATCH_DETAIL'];
		$ledger_data['PK_STUDENT_DISBURSEMENT'] = $res_type->fields['PK_STUDENT_DISBURSEMENT'];
		$ledger_data['PK_AR_LEDGER_CODE'] 		= $res_type->fields['PK_AR_LEDGER_CODE'];
		$ledger_data['AMOUNT'] 					= $res_type->fields['RECEIVED_AMOUNT'];
		$ledger_data['DATE'] 					= $res_type->fields['BATCH_TRANSACTION_DATE'];
		$ledger_data['PK_STUDENT_ENROLLMENT'] 	= $res_type->fields['PK_STUDENT_ENROLLMENT'];
		$ledger_data['PK_STUDENT_MASTER'] 		= $res_type->fields['PK_STUDENT_MASTER'];
		$ledger_data['PK_ACCOUNT']='81';
		$ledger_data['DATE']=$res_type->fields['CREATED_ON'];
		// echo "<pre>";
		// print_r($ledger_data);
		student_ledger($ledger_data);
		$res_type->MoveNext();

	}
//mysqli_close($db_uat);




?>

