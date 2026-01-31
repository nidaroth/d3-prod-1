<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == '' || $_SESSION['PK_USER'] == '0' )
	header("location:../index");
	
require_once('send_notification.php');

$DUE_DATE		= $_REQUEST['DUE_DATE'];
$INTERNAL_ID 	= $_REQUEST['INTERNAL_ID'];

if($DUE_DATE != '' ) {
	$TICKET_1['DUE_DATE'] = date("Y-m-d",strtotime($DUE_DATE));
	db_perform('Z_TICKET', $TICKET_1, 'update'," INTERNAL_ID = '$INTERNAL_ID' ");
}
echo 1;