<? require_once("../global/config.php"); 

$res_type = $db->Execute("SELECT TYPE FROM M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE = '$_REQUEST[val]' AND ACTIVE = 1 ");
echo $res_type->fields['TYPE'];