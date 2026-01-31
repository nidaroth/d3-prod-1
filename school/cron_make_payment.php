<? 
$path = '/var/www/html/D3/';
require_once($path."global/config.php");
require_once($path."global/payments.php");

require_once($path."global/mail.php");
require_once($path."global/texting.php");

require_once($path."school/function_student_ledger.php");
require_once($path."school/function_update_disbursement_status.php");

$date = date("Y-m-d");
// DVB REMOVE INVOICE = 1 17/12/2024
$res_auto_pmt = $db->Execute("SELECT PK_STUDENT_DISBURSEMENT, S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER, S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_DISBURSEMENT.PK_ACCOUNT, DISBURSEMENT_DATE, DISBURSEMENT_AMOUNT  
FROM 
S_STUDENT_MASTER, S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE, Z_ACCOUNT  
WHERE 
S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER AND ARCHIVED = 0 AND 
PK_DISBURSEMENT_STATUS = 2  AND ENABLE_AUTO_PAYMENT = 1 AND ENABLE_DIAMOND_PAY = 1 AND 
S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND 
Z_ACCOUNT.PK_ACCOUNT = S_STUDENT_DISBURSEMENT.PK_ACCOUNT AND DISBURSEMENT_DATE = '$date' ");

while (!$res_auto_pmt->EOF) {
	$PK_STUDENT_MASTER 	= $res_auto_pmt->fields['PK_STUDENT_MASTER'];
	
	$res_card = $db->Execute("SELECT PK_STUDENT_CREDIT_CARD FROM S_STUDENT_CREDIT_CARD WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND IS_PRIMARY = 1");
	if($res_card->RecordCount() > 0) {
		$data['PK_ACCOUNT'] 			= $res_auto_pmt->fields['PK_ACCOUNT'];
		$data['PK_STUDENT_MASTER'] 		= $res_auto_pmt->fields['PK_STUDENT_MASTER'];
		$data['PK_STUDENT_ENROLLMENT'] 	= $res_auto_pmt->fields['PK_STUDENT_ENROLLMENT'];
		$data['TYPE'] 					= 'disp';
		$data['FROM_CRON']				= 1;
		$data['ID'] 					= $res_auto_pmt->fields['PK_STUDENT_DISBURSEMENT'];
		$data['PK_STUDENT_CREDIT_CARD'] = $res_card->fields['PK_STUDENT_CREDIT_CARD'];
			
		$pn_res = make_payment($data);
	}
	$res_auto_pmt->MoveNext();
}
echo "done";