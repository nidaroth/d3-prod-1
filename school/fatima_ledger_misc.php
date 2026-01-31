<? 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once("../global/config.php"); 
require_once("function_student_ledger.php");
require_once("function_update_disbursement_status.php");

$sql="SELECT malc.CODE,smbd.*,smbm.BATCH_NO FROM S_MISC_BATCH_DETAIL smbd LEFT JOIN M_AR_LEDGER_CODE malc ON malc.PK_AR_LEDGER_CODE=smbd.PK_AR_LEDGER_CODE LEFT JOIN S_MISC_BATCH_MASTER smbm ON smbm .PK_MISC_BATCH_MASTER  = smbd .PK_MISC_BATCH_MASTER  WHERE smbd.PK_STUDENT_MASTER ='774736' AND smbd .PK_ACCOUNT =81"; 

$res_type = $db->Execute($sql);
$i=0;

while (!$res_type->EOF) { 

		$ledger_data['PK_MISC_BATCH_DETAIL'] 	= $res_type->fields['PK_MISC_BATCH_DETAIL'];
		$ledger_data['PK_AR_LEDGER_CODE'] 		= $res_type->fields['PK_AR_LEDGER_CODE'];
		$ledger_data['CREDIT_AMOUNT'] 			= $res_type->fields['CREDIT'];
		$ledger_data['DEBIT_AMOUNT'] 			= $res_type->fields['DEBIT'];
		$ledger_data['DATE'] 					= $res_type->fields['TRANSACTION_DATE'];
		$ledger_data['PK_STUDENT_ENROLLMENT'] 	= $res_type->fields['PK_STUDENT_ENROLLMENT'];
		$ledger_data['PK_STUDENT_MASTER'] 		= $res_type->fields['PK_STUDENT_MASTER'];
		$ledger_data['PK_ACCOUNT'] 				= '81';
		//student_ledger($ledger_data);

		echo "<pre>";
		print_r($ledger_data);
		echo "<br>";
		student_ledger($ledger_data);
		$res_type->MoveNext();

	}
//mysqli_close($db_uat);




?>

