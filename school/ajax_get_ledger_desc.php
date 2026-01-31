<? require_once("../global/config.php"); 

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
} 
$PK_AR_LEDGER_CODE  = $_REQUEST['val'];
$res = $db->Execute("SELECT LEDGER_DESCRIPTION FROM M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
echo $res->fields['LEDGER_DESCRIPTION'];