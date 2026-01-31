<?php 
$path = '/var/www/html/D3/';
require_once($path."global/config.php");
require_once($path."global/payments.php");

require_once($path."global/mail.php");
require_once($path."global/texting.php");

require_once($path."school/function_student_ledger.php");
require_once($path."school/function_update_disbursement_status.php");

$lb = '<br />';
if (php_sapi_name() == 'cli') { $lb = "\n"; }

// DVB REMOVE INVOICE = 1 17/12/2024
// DVB ADD AND MLC.DIAMOND_PAY = 1 07 01 2025
// prepare the cron payment data this file will be run before 30 minutes to process payment request.
$date = date("Y-m-d");

$disb_sql="SELECT 
SD.PK_STUDENT_DISBURSEMENT, 
SD.PK_STUDENT_MASTER, 
SD.PK_STUDENT_ENROLLMENT, 
SD.PK_ACCOUNT, 
DISBURSEMENT_DATE, 
DISBURSEMENT_AMOUNT  
FROM 
S_STUDENT_MASTER SM
JOIN 
S_STUDENT_DISBURSEMENT SD ON SM.PK_STUDENT_MASTER = SD.PK_STUDENT_MASTER
JOIN 
M_AR_LEDGER_CODE MLC ON SD.PK_AR_LEDGER_CODE = MLC.PK_AR_LEDGER_CODE
JOIN 
Z_ACCOUNT ZA ON SD.PK_ACCOUNT = ZA.PK_ACCOUNT
WHERE 
SM.ARCHIVED = 0 
AND SD.PK_DISBURSEMENT_STATUS = 2 
-- AND MLC.INVOICE = 1 
AND MLC.DIAMOND_PAY = 1 
AND SM.ENABLE_AUTO_PAYMENT = 1 
AND ZA.ENABLE_DIAMOND_PAY = 1 
AND SD.DISBURSEMENT_DATE = '$date'";

//echo $disb_sql.''.$lb;

$res_auto_pmt = $db->Execute($disb_sql);

$i=0;

while (!$res_auto_pmt->EOF) 
{
	$PK_STUDENT_MASTER 	= $res_auto_pmt->fields['PK_STUDENT_MASTER'];
	$res_card = $db->Execute("SELECT PK_STUDENT_CREDIT_CARD FROM S_STUDENT_CREDIT_CARD WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND IS_PRIMARY = 1");
	
	if($res_card->RecordCount() > 0) 
	{

	    // double check for duplicates payment in queue for the same day.

		$sql= "SELECT PK_STUDENT_DISBURSEMENT FROM M_PYAMENT_CRON_QUEUE WHERE PK_STUDENT_DISBURSEMENT = ".$res_auto_pmt->fields['PK_STUDENT_DISBURSEMENT']." AND `DISBURSEMENT_DATE` = '$date' AND DISBURSEMENT_AMOUNT='".$res_auto_pmt->fields['DISBURSEMENT_AMOUNT']."'";
 		$res_auto_queue_pmt = $db->Execute($sql);

		if($res_auto_queue_pmt->RecordCount() == 0)
		{
			$PYAMENT_CRON_QUEUE_DATA['DATE_CREATED']   				= date("Y-m-d H:i");			
			$PYAMENT_CRON_QUEUE_DATA['PK_STUDENT_DISBURSEMENT']   	= $res_auto_pmt->fields['PK_STUDENT_DISBURSEMENT'];
			$PYAMENT_CRON_QUEUE_DATA['PK_STUDENT_MASTER'] 			= $res_auto_pmt->fields['PK_STUDENT_MASTER'];
			$PYAMENT_CRON_QUEUE_DATA['PK_STUDENT_ENROLLMENT'] 		= $res_auto_pmt->fields['PK_STUDENT_ENROLLMENT'];
			$PYAMENT_CRON_QUEUE_DATA['PK_STUDENT_CREDIT_CARD']  	= $res_card->fields['PK_STUDENT_CREDIT_CARD'];
			$PYAMENT_CRON_QUEUE_DATA['PK_ACCOUNT'] 					= $res_auto_pmt->fields['PK_ACCOUNT'];
			$PYAMENT_CRON_QUEUE_DATA['DISBURSEMENT_DATE'] 			= $date;
			$PYAMENT_CRON_QUEUE_DATA['DISBURSEMENT_AMOUNT']  		= $res_auto_pmt->fields['DISBURSEMENT_AMOUNT'];
			db_perform('M_PYAMENT_CRON_QUEUE', $PYAMENT_CRON_QUEUE_DATA, 'insert');
		}

	}

	echo $i."".$lb;

	$i++;
	
	$res_auto_pmt->MoveNext();
}

echo "done";