<? require_once('global/config.php');  

$result = $db->Execute("SELECT * FROM Z_LOGIN_ACCESS WHERE CODE = '$_GET[code]' ");
if($result->RecordCount() == 0){
	header("location:/");
	exit;
} else {
	$PK_LOGIN_ACCESS = $result->fields['PK_LOGIN_ACCESS'];
	$DATA 			 = explode("^^^^",$result->fields['DATA']);
	
	$db->Execute("DELETE FROM Z_LOGIN_ACCESS WHERE PK_LOGIN_ACCESS = '$PK_LOGIN_ACCESS' ");
	
	foreach($DATA as $DATA1) {
		$DATA2 = explode("||||",$DATA1);
		
		$_SESSION[$DATA2[0]] = $DATA2[1];
	}

	if($_SESSION['PK_USER'] == '' || $_SESSION['PK_USER'] == 0) {
		header("location:../");
		exit;
	} else {
		header("location:".$_SESSION['FOLDER'].'index');
		exit;
	}
}
?>