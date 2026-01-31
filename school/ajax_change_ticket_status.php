<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == '' || $_SESSION['PK_USER'] == '0' )
	header("location:../index");
	
require_once('send_notification.php');

$PK_TICKET_STATUS 	= $_REQUEST['PK_TICKET_STATUS'];
$INTERNAL_ID 		= $_REQUEST['INTERNAL_ID'];

$res = $db->Execute("SELECT * from Z_TICKET WHERE INTERNAL_ID = '$INTERNAL_ID' ");
$PK_TICKET_STATUS_OLD	= $res->fields['PK_TICKET_STATUS'];
$PK_TICKET				= $res->fields['PK_TICKET'];

if($PK_TICKET_STATUS_OLD != $PK_TICKET_STATUS ) {

	$TICKET_STATUS_CHANGE['PK_TICKET'] 			= $PK_TICKET;
	$TICKET_STATUS_CHANGE['INTERNAL_ID'] 		= $INTERNAL_ID;
	$TICKET_STATUS_CHANGE['PK_TICKET_STATUS'] 	= $PK_TICKET_STATUS;
	$TICKET_STATUS_CHANGE['CHANGED_BY'] 		= $_SESSION['PK_USER'];
	$TICKET_STATUS_CHANGE['CHANGED_ON']  		= date("Y-m-d H:i");
	db_perform('Z_TICKET_STATUS_CHANGE_HISTORY', $TICKET_STATUS_CHANGE, 'insert');
	
	if($PK_TICKET_STATUS == 3)
		$TICKET_1['CLOSED_DATE'] = date("Y-m-d");
		
	$TICKET_1['PK_TICKET_STATUS'] = $PK_TICKET_STATUS;
	db_perform('Z_TICKET', $TICKET_1, 'update'," INTERNAL_ID = '$INTERNAL_ID' ");
	
	$SEND_NOTIFICATION_DATA['PK_TICKET'] = $PK_TICKET;
	//echo "<pre>"; print_r($SEND_NOTIFICATION_DATA);
	send_notification($SEND_NOTIFICATION_DATA,'TICKET STATUS CHANGE');
	
}
echo 1;