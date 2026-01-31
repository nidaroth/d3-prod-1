<? 
/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/
require_once("../global/config.php"); 
require_once("../global/common_functions.php"); 
require_once('classes/api_key_authenticater.php');

header('Content-Type: application/json; charset=utf-8');

$DATA = $HEADERDATA = file_get_contents('php://input');

//$DATA = '{"SSN_Encrypted":"XXXX-123","SSN_Encrypted":"401111104"}';
$PK_ACCOUNT = API_KEY_AUTHENTICATER::api_auth($HEADERDATA);

//$DATA = urldecode($DATA);
$DATA = json_decode($DATA);

$SSN_EN_1 	= $DATA->SSN_Encrypted;
$SSN_DE_1 	= my_decrypt('',$SSN_EN_1);	
$SSN_DE_2 	= $DATA->SSN_Decrypted;
$SSN_EN_2 	= my_encrypt('',$SSN_DE_2);	
		
if($SSN_DE_1 != '')
{
	$data['SUCCESS'] = 1;
	$data['SSN_ENCRYPTED'] = $SSN_DE_1;
}
else if ($SSN_EN_2 != '') 
{
	$data['SUCCESS'] = 1;
	$data['SSN_DECRYPTED'] = $SSN_EN_2;
}
else{
	$data['ERROR'] = 0;
	$data['ERROR_MESSAGE'] = 'Your SSN Key Is Not Valid.';
}

$data = json_encode($data);
echo $data;
?>