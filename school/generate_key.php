<? require_once("../global/config.php"); 

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_ROLES'] != 2 ){ 
	header("location:../index");
	exit;
}

$API_KEY = '';
do {
	$API_KEY = generateRandomString(40);
	$result = $db->Execute("SELECT PK_ACCOUNT FROM Z_ACCOUNT where API_KEY = '$API_KEY'");
} while ($result->RecordCount() > 0);

$ACCOUNT['API_KEY'] = $API_KEY;
db_perform('Z_ACCOUNT', $ACCOUNT, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

echo $API_KEY;