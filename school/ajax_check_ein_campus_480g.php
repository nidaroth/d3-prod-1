<? require_once("../global/config.php"); 
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT _4807G FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 || $res_add_on->fields['_4807G'] == 0){
	header("location:../index");
	exit;
}

$PK_CAMPUS = $_REQUEST['campus'];
$cond = "";
if($_REQUEST['id'] != '')
	$cond = " AND PK_4807G_EIN != '$_REQUEST[id]' ";

$res = $db->Execute("select PK_4807G_EIN_CAMPUS FROM _4807G_EIN_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ");
if($res->RecordCount() == 0)
	echo "a";
else
	echo "b";