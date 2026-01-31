<? require_once("../global/config.php"); 
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0){
	header("location:../index");
	exit;
}

$PK_CAMPUS = $_REQUEST['campus'];
$cond = "";
$pk_earning_type ="";
if($_REQUEST['id'] != ''){
	$cond = " AND PK_EARNINGS_SETUP != '$_REQUEST[id]' ";
}
if($_REQUEST['pk_earning_type'] != ''){
	$pk_earning_type = " AND PK_EARNING_TYPE = '$_REQUEST[pk_earning_type]' ";
}

// echo "SELECT PK_EARNINGS_SETUP_CAMPUS FROM S_EARNINGS_SETUP_CAMPUS AS T1 INNER JOIN S_EARNINGS_SETUP AS T2 ON T1.PK_EARNINGS_SETUP = T2.PK_EARNINGS_SETUP WHERE PK_CAMPUS = '$PK_CAMPUS' AND T1.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond $pk_earning_type ";exit;
$res = $db->Execute("SELECT PK_EARNINGS_SETUP_CAMPUS FROM S_EARNINGS_SETUP_CAMPUS AS T1 INNER JOIN S_EARNINGS_SETUP AS T2 ON T1.PK_EARNINGS_SETUP = T2.PK_EARNINGS_SETUP WHERE PK_CAMPUS = '$PK_CAMPUS' AND T1.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond $pk_earning_type ");
if($res->RecordCount() == 0)
	echo "a";
else
	echo "b";