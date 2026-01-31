<?php require_once('global/config.php'); 
$LOGIN_HISTORY['LOGOUT_TIME'] = date("Y-m-d H:i:s");
db_perform('Z_LOGIN_HISTORY', $LOGIN_HISTORY, 'update'," PK_LOGIN_HISTORY = '$_SESSION[PK_LOGIN_HISTORY]' ");

unset($_SESSION);
session_unset();
if($_GET['s'] == 1){
	header("location:signin");
	exit;
} else {
	header("location:index");
	exit;
}
?>