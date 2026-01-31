<? require_once("../global/config.php"); 

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
} 
$PK_ECM_LEDGER_MASTER  = $_REQUEST['val'];
$res = $db->Execute("SELECT ECM_LEDGER_TYPE FROM M_ECM_LEDGER_TYPE_MASTER, M_ECM_LEDGER_MASTER WHERE PK_ECM_LEDGER_MASTER = '$PK_ECM_LEDGER_MASTER' AND   M_ECM_LEDGER_TYPE_MASTER.PK_ECM_LEDGER_TYPE_MASTER = M_ECM_LEDGER_MASTER.PK_ECM_LEDGER_TYPE_MASTER"); 
echo $res->fields['ECM_LEDGER_TYPE'];