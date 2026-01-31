<? 
$path = '/var/www/html/D3/';
require_once($path."global/config.php");
require_once($path."global/payments.php");

require_once($path."global/mail.php");
require_once($path."global/texting.php");

require_once($path."school/function_student_ledger.php");
require_once($path."school/function_update_disbursement_status.php");

$date = date("Y-m-d");


$lb = '<br />';
if (php_sapi_name() == 'cli') {
	$lb = "\n";
}

$sql= "SELECT PK_STUDENT_DISBURSEMENT,PK_ACCOUNT,PK_STUDENT_MASTER,PK_STUDENT_ENROLLMENT,PK_STUDENT_CREDIT_CARD FROM M_PYAMENT_CRON_QUEUE WHERE `DISBURSEMENT_DATE` = '$date' AND CRON_STATUS=0";
$res_auto_pmt = $db->Execute($sql);

while (!$res_auto_pmt->EOF) 
{
	$PK_STUDENT_DISBURSEMENT = $res_auto_pmt->fields['PK_STUDENT_DISBURSEMENT'];
	$PK_STUDENT_MASTER 		 = $res_auto_pmt->fields['PK_STUDENT_MASTER'];
	$PK_ACCOUNT 			 = $res_auto_pmt->fields['PK_ACCOUNT'];

	$duble_chk = $db->Execute("SELECT PK_PAYMENT_LOG FROM M_PAYMENT_LOG WHERE PK_STUDENT_MASTER = ".$PK_STUDENT_MASTER." AND PK_ACCOUNT =".$PK_ACCOUNT." AND PK_STUDENT_DISBURSEMENT=".$PK_STUDENT_DISBURSEMENT."");

	if($duble_chk->RecordCount()==0) 
	{
	
			$res_card = $db->Execute("SELECT PK_STUDENT_CREDIT_CARD FROM S_STUDENT_CREDIT_CARD WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND IS_PRIMARY = 1");

			
			if($res_card->RecordCount() > 0) 
			{
				$data['PK_ACCOUNT'] 			= $res_auto_pmt->fields['PK_ACCOUNT'];
				$data['PK_STUDENT_MASTER'] 		= $res_auto_pmt->fields['PK_STUDENT_MASTER'];
				$data['PK_STUDENT_ENROLLMENT'] 	= $res_auto_pmt->fields['PK_STUDENT_ENROLLMENT'];
				$data['TYPE'] 					= 'disp';
				$data['FROM_CRON']				= 1;
				$data['ID'] 					= $res_auto_pmt->fields['PK_STUDENT_DISBURSEMENT'];
				$data['PK_STUDENT_CREDIT_CARD'] = $res_card->fields['PK_STUDENT_CREDIT_CARD'];

				$PYAMENT_CRON_QUEUE_DATA=array();
				$start = mktime();

				$PYAMENT_CRON_QUEUE_DATA['LAST_RUN_START_DATE']   = date("Y-m-d H:i");

				$pn_res = make_payment($data);

				if(!empty($pn_res))
				{
					if($pn_res['STATUS']==1){
						$payment_status="SUCCESS";
					}else{
						$payment_status="FAILED";
					}
					$PYAMENT_CRON_QUEUE_DATA['LAST_RUN_END_DATE']   		= date("Y-m-d H:i");
					$PYAMENT_CRON_QUEUE_DATA['LAST_UPDATED']   				= date("Y-m-d H:i");
					$PYAMENT_CRON_QUEUE_DATA['CRON_STATUS']   				= 1;
					$PYAMENT_CRON_QUEUE_DATA['PAYMENT_STATUS']   			= $payment_status;
					$end = mktime();
					$tm = number_format(($end - $start),0).' seconds';

					$PYAMENT_CRON_QUEUE_DATA['NOTE']   						= $tm;			
					
					db_perform('M_PYAMENT_CRON_QUEUE', $PYAMENT_CRON_QUEUE_DATA, 'update'," PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT' AND PK_ACCOUNT = '".$res_auto_pmt->fields['PK_ACCOUNT']."' ");

				}
				//sleep(1);
			}
	}

	$res_auto_pmt->MoveNext();
}



echo "done";