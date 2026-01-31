<?php require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/add-card.php");
require_once("check_access.php");

$res_pay = $db->Execute("select ENABLE_DIAMOND_PAY from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 0) {
	header("location:../index");
	exit;
}

/*
http://localhost/dsis/school/ajax_calc_cc_transaction_charge.php?id=3&amt=1000
$_REQUEST[id]
$_REQUEST[amt]
*/

$res_cardx = $db->Execute("select * from S_CARD_X_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$res_token = $db->Execute("select TOKEN from S_STUDENT_CREDIT_CARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CREDIT_CARD = '$_REQUEST[id]' ");

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://paywithcardx.com/api/merchant/adjustment',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "transactionInformation":[
		  {
			 "token":"'.$res_token->fields['TOKEN'].'",
			 "paymentVehicleType":"card",
			 "transactionAmount":"'.$_REQUEST['amt'].'",
			 "paymentVehicleIdentifier":"paymentOptionC",
			 "transactionIdentifier":1
		  }
	   ]
	}',
  CURLOPT_HTTPHEADER => array(
    'x-gateway-account: '.$res_cardx->fields['PUBLISHER_NAME'],
    'x-gateway-api-key-name: '.$res_cardx->fields['API_KEY_NAME'],
    'x-gateway-api-key: '.$res_cardx->fields['API_KEY'],
    'content-type: application/json',
    'accept: application/json'
  ),
));

$response 	= curl_exec($curl);
$err 		= curl_error($curl);

curl_close($curl);

if($err) {
	
} else {
	$data = json_decode($response);
	//echo "<pre>";print_r($data);
	echo $data->content->data->paymentOptionC->calculatedAdjustment->adjustment;
}
