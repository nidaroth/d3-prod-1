<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == '' || $_SESSION['PK_USER'] == '0' )
	header("location:../index");
	
require_once('send_notification.php');

$PK_TICKET_PRIORITY	= $_REQUEST['PK_TICKET_PRIORITY'];
$INTERNAL_ID 		= $_REQUEST['INTERNAL_ID'];

$res = $db->Execute("SELECT * from Z_TICKET WHERE INTERNAL_ID = '$INTERNAL_ID' ");
$PK_TICKET_PRIORITY_OLD	= $res->fields['PK_TICKET_PRIORITY'];
$PK_TICKET				= $res->fields['PK_TICKET'];

if($PK_TICKET_PRIORITY_OLD != $PK_TICKET_PRIORITY ) {

	$TICKET_1['PK_TICKET_PRIORITY'] = $PK_TICKET_PRIORITY;
	db_perform('Z_TICKET', $TICKET_1, 'update'," INTERNAL_ID = '$INTERNAL_ID' ");
	
	$SEND_NOTIFICATION_DATA['PK_TICKET'] = $PK_TICKET;
	//echo "<pre>"; print_r($SEND_NOTIFICATION_DATA);
	send_notification($SEND_NOTIFICATION_DATA,'TICKET STATUS CHANGED');
	
}
echo 1;