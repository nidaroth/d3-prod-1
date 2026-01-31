<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

$res_type = $db->Execute("select QUICK_PAYMENT,DIAMOND_PAY from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_REQUEST[ledger_id]' AND ACTIVE = 1 ");
if($res_type->fields['QUICK_PAYMENT'] == 1)
{
    echo "2";
}
else 
{  
    echo "0";
} 

?>