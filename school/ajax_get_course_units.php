<? require_once("../global/config.php"); 
require_once("../language/program.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
} 

$res_dd = $db->Execute("select * FROM S_COURSE WHERE PK_COURSE = '$_REQUEST[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
echo $res_dd->fields['UNITS']."|||".$res_dd->fields['FA_UNITS']."|||".$res_dd->fields['HOURS'];