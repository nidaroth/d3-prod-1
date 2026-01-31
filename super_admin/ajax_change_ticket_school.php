<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == '' || $_SESSION['PK_USER'] == '0' )
	header("location:../index");

$TICKET_FOR = $_REQUEST['TICKET_FOR'];
$INTERNAL_ID 		= $_REQUEST['INTERNAL_ID'];

$res = $db->Execute("SELECT * from Z_TICKET WHERE INTERNAL_ID = '$INTERNAL_ID' ");
$TICKET_FOR_OLD	= $res->fields['TICKET_FOR'];
$PK_TICKET		= $res->fields['PK_TICKET'];

$TICKET_1['TICKET_FOR'] = $TICKET_FOR;
db_perform('Z_TICKET', $TICKET_1, 'update'," INTERNAL_ID = '$INTERNAL_ID' ");

echo 1;