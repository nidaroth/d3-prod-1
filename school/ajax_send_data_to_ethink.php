<? require_once("../global/config.php"); 
require_once("../global/ethink.php");

$res = $db->Execute("SELECT ENABLE_ETHINK FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
$ENABLE_ETHINK = $res->fields['ENABLE_ETHINK'];

if($ENABLE_ETHINK != 1) {
	header("location:../index");
	exit;
}

if($_REQUEST['type'] == "emp") {
	echo create_user($_REQUEST['id'],$_SESSION['PK_ACCOUNT'],1,'');
} else if($_REQUEST['type'] == "course_offering") {
	echo create_course_offering($_REQUEST['id'],$_SESSION['PK_ACCOUNT'],1);
} else if($_REQUEST['type'] == "student") {
	echo create_user($_REQUEST['id'],$_SESSION['PK_ACCOUNT'],2,'');
} else if($_REQUEST['type'] == "enroll") {
	echo create_enrollment($_REQUEST['id'],$_REQUEST['eid'],$_SESSION['PK_ACCOUNT'],'');
}

