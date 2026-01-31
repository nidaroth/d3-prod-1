<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

if($_REQUEST['type_id'] != "")
{
    $res_type = $db->Execute("select AR_PAYMENT_TYPE from M_AR_PAYMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_PAYMENT_TYPE = '$_REQUEST[type_id]' AND ACTIVE = 1 ");
    if($res_type->fields['AR_PAYMENT_TYPE'] == 'Credit Card')
    {
        echo "1";
    } 
    else
    {
        echo "2";
    }
}
else 
{  
    echo "0";
} 

?>