<? 

//$db_uat=mysqli_connect('184.73.75.183','root','DSISMySQLPa$$1!','DSIS');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once("../global/config.php"); 
require_once("function_student_ledger.php");
require_once("function_update_disbursement_status.php");

$sql="SELECT malc.CODE,stbm.PK_BATCH_STATUS ,stbd.* FROM S_TUITION_BATCH_DETAIL stbd LEFT JOIN M_AR_LEDGER_CODE malc ON malc.PK_AR_LEDGER_CODE=stbd.PK_AR_LEDGER_CODE LEFT JOIN S_TUITION_BATCH_MASTER stbm ON stbm .PK_TUITION_BATCH_MASTER = stbd .PK_TUITION_BATCH_MASTER  WHERE stbd .PK_TUITION_BATCH_MASTER  = 77704 AND  stbd.PK_STUDENT_MASTER !=1233163
AND stbd .PK_ACCOUNT = 67 GROUP By stbd.PK_TUITION_BATCH_DETAIL"; 

$res_type = $db->Execute($sql);
$i=0;

while (!$res_type->EOF) { 

		// $ledger_data['PK_PAYMENT_BATCH_DETAIL'] = $res_type->fields['PK_PAYMENT_BATCH_DETAIL'];
		// $ledger_data['PK_STUDENT_DISBURSEMENT'] = $res_type->fields['PK_STUDENT_DISBURSEMENT'];
		// $ledger_data['PK_AR_LEDGER_CODE'] 		= $res_type->fields['PK_AR_LEDGER_CODE'];
		// $ledger_data['AMOUNT'] 					= $res_type->fields['RECEIVED_AMOUNT'];
		// $ledger_data['DATE'] 					= $res_type->fields['BATCH_TRANSACTION_DATE'];
		// $ledger_data['PK_STUDENT_ENROLLMENT'] 	= $res_type->fields['PK_STUDENT_ENROLLMENT'];
		// $ledger_data['PK_STUDENT_MASTER'] 		= $res_type->fields['PK_STUDENT_MASTER'];
		// $ledger_data['PK_ACCOUNT']='81';
		// $ledger_data['DATE']=$res_type->fields['CREATED_ON'];
		//student_ledger($ledger_data);

		if ($res_type->fields['PK_BATCH_STATUS'] == 2) {
			$ledger_data['PK_TUITION_BATCH_DETAIL'] = $res_type->fields['PK_TUITION_BATCH_DETAIL'];
			$ledger_data['PK_AR_LEDGER_CODE'] 		= $res_type->fields['PK_AR_LEDGER_CODE'];
			$ledger_data['AMOUNT'] 					= $res_type->fields['AMOUNT'];
			$ledger_data['DATE'] 					= $res_type->fields['TRANSACTION_DATE'];
			$ledger_data['PK_STUDENT_ENROLLMENT'] 	= $res_type->fields['PK_STUDENT_ENROLLMENT'];
			$ledger_data['PK_STUDENT_MASTER'] 		= $res_type->fields['PK_STUDENT_MASTER'];
			$ledger_data['PK_ACCOUNT']='67';
			$ledger_data['DATE']=$res_type->fields['CREATED_ON'];

			echo "<pre>";
			print_r($ledger_data);
			echo "<br>";
	
			//student_ledger($ledger_data);
		}

		$res_type->MoveNext();

	}
//mysqli_close($db_uat);




?>

