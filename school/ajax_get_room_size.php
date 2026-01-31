<? require_once("../global/config.php"); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}
$PK_CAMPUS_ROOM  = $_REQUEST['room'];
$res_type = $db->Execute("select CLASS_SIZE FROM M_CAMPUS_ROOM WHERE PK_CAMPUS_ROOM = '$PK_CAMPUS_ROOM' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
echo $res_type->fields['CLASS_SIZE'];