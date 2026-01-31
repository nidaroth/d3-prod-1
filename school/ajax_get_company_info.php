<? require_once("../global/config.php"); 
require_once("../language/common.php");

$PK_COMPANY = $_REQUEST['id'];

$res = $db->Execute("SELECT * FROM S_COMPANY WHERE PK_COMPANY = '$PK_COMPANY' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' LIMIT 1"); 

$result 				= array();
$result['ADDRESS'] 		= '';
$result['CITY']			= '';
$result['PK_STATES'] 	= '';
$result['PHONE'] 		= '';
$result['ADDRESS_1']	= '';
$result['ZIP']			= '';

if($res->RecordCount() > 0) {
	$result['ADDRESS'] 		= $res->fields['ADDRESS'];
	$result['CITY']  		= $res->fields['CITY'];
	$result['PK_STATES']  	= $res->fields['PK_STATES'];
	$result['ZIP']   		= $res->fields['ZIP'];
	$result['PHONE']   		= $res->fields['PHONE'];
	$result['ADDRESS_1']   	= $res->fields['ADDRESS_1'];
}

echo json_encode($result);
?>
