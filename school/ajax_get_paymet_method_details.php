<?php require_once("../global/config.php");
require_once("check_access.php");

$res_pay = $db->Execute("select ENABLE_DIAMOND_PAY from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 0) {
	header("location:../index");
	exit;
}

$payment_method_id = $_REQUEST['payment_method_id'];

if($payment_method_id != '')
{

    $res_card_details = $db->Execute("SELECT * FROM S_STUDENT_CREDIT_CARD_STAX WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CREDIT_CARD_STAX = '$payment_method_id' ");

    $res_cardx = $db->Execute("SELECT API_KEY FROM S_STAX_X_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

    $sPAYMENT_METHOD_ID 	= $res_card_details->fields['PAYMENT_METHOD_ID'];

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://apiprod.fattlabs.com/payment-method/'.$sPAYMENT_METHOD_ID.'',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
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
        // echo "<pre>";print_r($data);
        
    }

    $card_exp = $data->card_exp;
    $month = substr($card_exp,0,2);
    $year = substr($card_exp,2,6);

    $STUDENT_RECORD = array();
    $STUDENT_RECORD['customer_id']  	   = $data->customer_id; 
    $STUDENT_RECORD['firstname']           = $data->customer->firstname; 
    $STUDENT_RECORD['lastname']            = $data->customer->lastname;
    $STUDENT_RECORD['phone']               = $data->customer->phone;
    $STUDENT_RECORD['address_1']           = $data->address_1; 
    $STUDENT_RECORD['address_2']           = $data->address_2; 
    $STUDENT_RECORD['address_city']        = $data->address_city; 
    $STUDENT_RECORD['address_state']       = $data->address_state; 
	$STUDENT_RECORD['address_zip']         = $data->address_zip; 
    $STUDENT_RECORD['address_country']     = $data->address_country;
    $STUDENT_RECORD['address_country']     = $data->address_country; 
    $STUDENT_RECORD['card_last_four']      = $data->card_last_four; 
    $STUDENT_RECORD['month']               = $month; 
    $STUDENT_RECORD['year']                = $year; 
    
    echo json_encode($STUDENT_RECORD);

}
?>