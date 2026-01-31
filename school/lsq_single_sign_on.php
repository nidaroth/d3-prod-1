<? require_once("../global/config.php"); 
require_once("check_access.php");

$res = $db->Execute("SELECT ENABLE_LSQ FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['ENABLE_LSQ'] == 0) {
	header("location:../index");
	exit;
}

$res_lsq = $db->Execute("SELECT * FROM Z_ACCOUNT_LSQ_SETTINGS  WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$LSQ_ACCESS_KEY = $res_lsq->fields['ACCESS_KEY'];
$LSQ_SECRET_KEY = $res_lsq->fields['SECRET_KEY'];
$LSQ_USER_NAME 	= $res_lsq->fields['USER_NAME'];
$LSQ_PASSWORD 	= $res_lsq->fields['PASSWORD'];
$LSQ_BASE_URL 	= $res_lsq->fields['BASE_URL'];

$URL = $LSQ_BASE_URL."Telephony.svc/GetSingleSignOnKey?accessKey=".$LSQ_ACCESS_KEY."&secretKey=".$LSQ_SECRET_KEY."&userName=".$LSQ_USER_NAME."&password=".$LSQ_PASSWORD;
//echo $URL;exit;
$curl = curl_init();
curl_setopt_array($curl, array(
	CURLOPT_URL => $URL,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_SSL_VERIFYPEER => 0,
	CURLOPT_CUSTOMREQUEST => "GET"
));	
$result = (curl_exec($curl));
$err 	= curl_error($curl);

$auth_key = json_decode($result);
//echo $auth_key;
header("location:https://us11.leadsquared.com/SingleSignOn/LeadDetails?key=".$auth_key);
exit;