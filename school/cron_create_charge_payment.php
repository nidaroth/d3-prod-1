<?php
$path = '/var/www/html/D3/';
require_once($path."global/config.php");
require_once($path."global/payments_stax.php");

require_once($path."global/mail.php");
require_once($path."global/texting.php");

require_once($path."school/function_student_ledger.php");
require_once($path."school/function_update_disbursement_status.php");

// DIAM-2334
function generateMockToken() {
    // Generate a random string as a mock token
    $token = bin2hex(random_bytes(16)); // 32 characters long hex string
    return $token;
}
$sToken = generateMockToken();
// End DIAM-2334

$date 		= date("Y-m-d");
// $date 		= '2024-05-14';
$res_auto_pmt = $db->Execute("SELECT PK_STUDENT_DISBURSEMENT, 
									S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER, 
									S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT, 
									S_STUDENT_DISBURSEMENT.PK_ACCOUNT, 
									DISBURSEMENT_DATE, 
									DISBURSEMENT_AMOUNT,
									S_STUDENT_CREDIT_CARD_STAX.PK_STUDENT_CREDIT_CARD_STAX,
									S_STUDENT_CREDIT_CARD_STAX.CUSTOMER_ID,
                                    S_STUDENT_CREDIT_CARD_STAX.PAYMENT_METHOD_ID
								FROM 
									S_STUDENT_MASTER, 
									S_STUDENT_DISBURSEMENT, 
									M_AR_LEDGER_CODE, 
									Z_ACCOUNT, 
									S_STUDENT_CREDIT_CARD_STAX 
								WHERE 
									ARCHIVED = 0 
									AND PK_DISBURSEMENT_STATUS = 2 
									AND INVOICE = 1 
									AND S_STUDENT_MASTER.ENABLE_AUTO_PAYMENT = 1 
									AND ENABLE_DIAMOND_PAY = 2 
                                    AND S_STUDENT_CREDIT_CARD_STAX.IS_PRIMARY = 1
									AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER
									AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE 
									AND Z_ACCOUNT.PK_ACCOUNT = S_STUDENT_DISBURSEMENT.PK_ACCOUNT 
									AND S_STUDENT_CREDIT_CARD_STAX.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
									AND DISBURSEMENT_DATE = '$date' ");

while (!$res_auto_pmt->EOF) {
	
	$PK_ACCOUNT 				 		 = $res_auto_pmt->fields['PK_ACCOUNT'];
	$PK_STUDENT_MASTER 					 = $res_auto_pmt->fields['PK_STUDENT_MASTER'];
	$PK_STUDENT_ENROLLMENT		 		 = $res_auto_pmt->fields['PK_STUDENT_ENROLLMENT'];
	$PK_STUDENT_DISBURSEMENT	 		 = $res_auto_pmt->fields['PK_STUDENT_DISBURSEMENT'];
	$DISBURSEMENT_AMOUNT		 	     = $res_auto_pmt->fields['DISBURSEMENT_AMOUNT'];
	$CUSTOMER_ID 		 		 		 = $res_auto_pmt->fields['CUSTOMER_ID'];
    $PAYMENT_METHOD_ID 		 		     = $res_auto_pmt->fields['PAYMENT_METHOD_ID'];
	$PK_STUDENT_CREDIT_CARD_STAX 		 = $res_auto_pmt->fields['PK_STUDENT_CREDIT_CARD_STAX'];

    // double check if the payment happened against disburstment.
	$duble_chk = $db->Execute("SELECT INVOICE_ID FROM S_PAYMENT_STAX_LOG WHERE PK_STUDENT_MASTER = ".$PK_STUDENT_MASTER." AND PK_ACCOUNT = ".$PK_ACCOUNT." AND PK_STUDENT_DISBURSEMENT = ".$PK_STUDENT_DISBURSEMENT." AND TRANSACTION_START = '$date' ");
	if($duble_chk->RecordCount()==0) 
	{

        $res_cardx = $db->Execute("SELECT API_KEY FROM S_STAX_X_SETTINGS WHERE PK_ACCOUNT = '$PK_ACCOUNT' ");

        $Request['DISBURSEMENT_AMOUNT'] = $DISBURSEMENT_AMOUNT;
        $Request['PAYMENT_METHOD_ID'] 	= $PAYMENT_METHOD_ID;
        $Request_Data = json_encode($Request);

        if($res_cardx->fields['API_KEY']!="" && $PAYMENT_METHOD_ID!="" && $DISBURSEMENT_AMOUNT!="")
        {

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://apiprod.fattlabs.com/charge',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',

                CURLOPT_POSTFIELDS =>'{
                    "payment_method_id": "'.$PAYMENT_METHOD_ID.'",
                    "currency_code": "USD",
                    "meta": {
                        "tax":0,
                        "poNumber": "'.uniqid().'",
                        "shippingAmount": 0,
                        "payment_note": "This note displays in Stax Pay",
                        "subtotal":"'.$DISBURSEMENT_AMOUNT.'"
                    },
                    "total": "'.$DISBURSEMENT_AMOUNT.'",
                    "pre_auth": "0",
                    "transaction": {
                        "payment_method_token": "'.$sToken.'",
                        "amount": "'.$DISBURSEMENT_AMOUNT.'",
                        "currency_code": "USD",
                        "meta": {
                            "transaction_initiation_type": "MIT",
                            "transaction_schedule_type": "scheduled"
                        }
                    }
                }',
                CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$res_cardx->fields['API_KEY'].'',
                'content-type: application/json'
                ),
            ));

            $response 	= curl_exec($curl);
            $err 		= curl_error($curl);

            curl_close($curl);
            if($err) {
                
            } else {
                $data = json_decode($response);
                // echo "<pre>";print_r($data);exit;
            }

            $record = $data;
            // echo "<pre>";print_r($record);exit;
            $status 	                = strtoupper($record->status);
            $response_customer_id       = $record->customer_id;
            $response_payment_method_id = $record->payment_method_id;
            $response_type              = strtoupper($record->type);
            $Data_ID                    = $record->id;

            // echo $response_payment_method_id.'|'.$response_customer_id.'|'.$status.'|'.$response_type;exit;
            
            if($status == 'SUCCESS' && $response_type == 'CHARGE' && $response_customer_id == $CUSTOMER_ID && $response_payment_method_id == $PAYMENT_METHOD_ID)
            {
                $Input['PK_ACCOUNT'] 			 = $PK_ACCOUNT;
                $Input['PK_STUDENT_MASTER'] 	 = $PK_STUDENT_MASTER;
                $Input['PK_STUDENT_ENROLLMENT']  = $PK_STUDENT_ENROLLMENT;
                $Input['TYPE'] 					 = 'disp';
                $Input['FROM_CRON']				 = 1;
                $Input['ID'] 					 = $PK_STUDENT_DISBURSEMENT;
                $Input['PK_STUDENT_CREDIT_CARD'] = $PK_STUDENT_CREDIT_CARD_STAX;
                // print_r($Input);exit;
                    
                $pn_res = make_payment_stax($Input);
                if($pn_res['STATUS'] == 1)
                {
                    echo "success | ";
                }
                else{
                    echo "Error | ";
                }
            }
                        
            $S_PAYMENT_STAX_LOG['PK_STUDENT_MASTER']  		    = $PK_STUDENT_MASTER;
            $S_PAYMENT_STAX_LOG['PK_STUDENT_DISBURSEMENT']  	= $PK_STUDENT_DISBURSEMENT;
            $S_PAYMENT_STAX_LOG['PK_ACCOUNT']  				    = $PK_ACCOUNT;
            $S_PAYMENT_STAX_LOG['DISBURSEMENT_AMOUNT ']  		= $DISBURSEMENT_AMOUNT;
            $S_PAYMENT_STAX_LOG['STATUS']  					    = $status; // status
            $S_PAYMENT_STAX_LOG['INVOICE_ID']  				    = $Data_ID; // ID
            $S_PAYMENT_STAX_LOG['REQUEST']  				    = $Request_Data;
            $S_PAYMENT_STAX_LOG['RESPONSE']  					= $response;
            $S_PAYMENT_STAX_LOG['TRANSACTION_START']  		    = $date;
            $S_PAYMENT_STAX_LOG['PK_STUDENT_CREDIT_CARD_STAX']  = $PK_STUDENT_CREDIT_CARD_STAX;
            $S_PAYMENT_STAX_LOG['CUSTOMER_ID'] 				    = $CUSTOMER_ID;
            $S_PAYMENT_STAX_LOG['CREATED_ON'] 				    = date("Y-m-d H:i:s");
                
            db_perform('S_PAYMENT_STAX_LOG', $S_PAYMENT_STAX_LOG, 'insert');
        } 
        else
        {
            echo "Error: There is API key or payment method Id not found";
        }
    }

	$res_auto_pmt->MoveNext();

}
echo "done";
?>