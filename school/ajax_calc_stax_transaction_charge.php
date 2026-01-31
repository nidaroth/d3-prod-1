<?php require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/add-card.php");
require_once("check_access.php");

$res_pay = $db->Execute("select ENABLE_DIAMOND_PAY from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 0) {
	header("location:../index");
	exit;
}

// DIAM-2334
function generateMockToken() {
    // Generate a random string as a mock token
    $token = bin2hex(random_bytes(16)); // 32 characters long hex string
    return $token;
}
$sToken = generateMockToken();
// End DIAM-2334

$STAX_ID                     = $_REQUEST['id'];
$AMOUNT                      = $_REQUEST['amt'];
$Transaction_Initiation_Type = $_REQUEST['transaction_initiation_type'];

if($STAX_ID != '' && $AMOUNT != '')
{
    $res_cardx = $db->Execute("SELECT API_KEY FROM S_STAX_X_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

    if($res_cardx->fields['API_KEY'] != "")
    {

        $res_card_detail = $db->Execute("SELECT PK_STUDENT_CREDIT_CARD_STAX, PK_STUDENT_MASTER, PAYMENT_METHOD_ID, CUSTOMER_ID FROM S_STUDENT_CREDIT_CARD_STAX WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CREDIT_CARD_STAX = '$STAX_ID' ");

        $PK_STUDENT_MASTER           = $res_card_detail->fields['PK_STUDENT_MASTER'];
        $PAYMENT_METHOD_ID           = $res_card_detail->fields['PAYMENT_METHOD_ID'];
        $CUSTOMER_ID                 = $res_card_detail->fields['CUSTOMER_ID'];
        $PK_STUDENT_CREDIT_CARD_STAX = $res_card_detail->fields['PK_STUDENT_CREDIT_CARD_STAX'];

        $Request['DISBURSEMENT_AMOUNT'] = $AMOUNT;
        $Request['PAYMENT_METHOD_ID'] 	= $PAYMENT_METHOD_ID;
        $Request_Data = json_encode($Request);

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
                "meta": {
                    "tax":0,
                    "poNumber": "'.uniqid().'",
                    "shippingAmount": 0,
                    "payment_note": "This note displays in Stax Pay",
                    "subtotal":"'.$AMOUNT.'"
                },
                "total": "'.$AMOUNT.'",
                "pre_auth": "0",
                "transaction": {
                    "payment_method_token": "'.$sToken.'",
                    "amount": "'.$AMOUNT.'",
                    "currency_code": "USD",
                    "meta": {
                        "transaction_initiation_type": "'.$Transaction_Initiation_Type.'",
                        "transaction_schedule_type": "unscheduled"
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
            echo "success";
        }
        else{
            if($status == 'FAILED')
            {
                echo $record->error_description;
            }
            else{
                echo "Something Went Wrong. Please try again.";
            }
            
        }
                    
        $S_PAYMENT_STAX_LOG['PK_STUDENT_MASTER']  		    = $PK_STUDENT_MASTER;
        $S_PAYMENT_STAX_LOG['PK_STUDENT_DISBURSEMENT']  	= 0;
        $S_PAYMENT_STAX_LOG['PK_ACCOUNT']  				    = $_SESSION['PK_ACCOUNT'];
        $S_PAYMENT_STAX_LOG['DISBURSEMENT_AMOUNT ']  		= $AMOUNT;
        $S_PAYMENT_STAX_LOG['STATUS']  					    = $status; // status
        $S_PAYMENT_STAX_LOG['INVOICE_ID']  				    = $Data_ID; // ID
        $S_PAYMENT_STAX_LOG['REQUEST']  				    = $Request_Data;
        $S_PAYMENT_STAX_LOG['RESPONSE']  					= $response;
        $S_PAYMENT_STAX_LOG['TRANSACTION_START']  		    = $date;
        $S_PAYMENT_STAX_LOG['PK_STUDENT_CREDIT_CARD_STAX']  = $PK_STUDENT_CREDIT_CARD_STAX;
        $S_PAYMENT_STAX_LOG['CUSTOMER_ID'] 				    = $CUSTOMER_ID;
        $S_PAYMENT_STAX_LOG['CREATED_ON'] 				    = date("Y-m-d H:i:s");
        $S_PAYMENT_STAX_LOG['CREATED_BY']  			        = $_SESSION['PK_USER'];
            
        db_perform('S_PAYMENT_STAX_LOG', $S_PAYMENT_STAX_LOG, 'insert');
    }
    else
    {
        echo "Error: There is API key not found";
    }
}
else{
    echo "Error: There is Stax ID Or Amount Or DISBURSEMENT ID not found.";
}
?>