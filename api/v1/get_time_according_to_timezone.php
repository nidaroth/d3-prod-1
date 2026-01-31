<? header("Access-Control-Allow-Origin: *");
require_once("../../global/config.php");
require_once("../../school/function_attendance.php");
require_once('../classes/api_key_authenticater.php');
$DATA = $HEADERDATA = json_decode(urldecode(file_get_contents('php://input')));
header('Content-Type: application/json; charset=utf-8');
$PK_ACCOUNT = API_KEY_AUTHENTICATER::api_auth($HEADERDATA);
if ($DATA == null) {
	$data['ERROR'] = 1; 
	$data['MESSAGE'] ="Invalid JSON recived in incomming request. Please validate format of JSON data."; 
	$data = json_encode($data); 
}
$DATA = (file_get_contents('php://input'));
$DATA = urldecode($DATA);
$DATA = json_decode($DATA);
$PK_ACCOUNT = $DATA->pk_ACCOUNT;
if (isset($PK_ACCOUNT) && $PK_ACCOUNT != '') {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$PK_ACCOUNT' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if ($timezone == '' || $timezone == 0)
		$timezone = 4;
	$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

	$TIMEZONE = $res->fields['TIMEZONE'];
	$date = convert_to_user_date(date('Y-m-d H:i:s'), 'Y-m-d H:i:s', $TIMEZONE, date_default_timezone_get());

	$time = $date; 
	$data['TIME'] = $time;
	$data = json_encode($data);
	echo $data;
	exit;
} else {
	$data['ERROR'] = 1; 
	$data['MESSAGE'] ="PK ACCOUNT IS NOT PROVIDED OR INVALID"; 
	$data = json_encode($data);
	echo $data;
	exit;
}
