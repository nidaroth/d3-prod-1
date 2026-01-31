<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/earnings_setup.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}
$OPERATION_TYPE 	= $_REQUEST['type'];
$OPERATION_MONTH 	= $_REQUEST['month'];
$OPERATION_YEAR 	= $_REQUEST['year'];
$PK_CAMPUS 			= $_REQUEST['campus'];

$res = $db->Execute("CALL ACCT20013(".$_SESSION['PK_ACCOUNT'].",".$PK_CAMPUS.",".$_SESSION['PK_USER'].",".$OPERATION_YEAR.",".$OPERATION_MONTH.",'".$OPERATION_TYPE."')");
echo "1";