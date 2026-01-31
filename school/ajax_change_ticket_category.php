<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == '' || $_SESSION['PK_USER'] == '0' )
	header("location:../index");

$PK_TICKET_CATEGORY = $_REQUEST['PK_TICKET_CATEGORY'];
$INTERNAL_ID 		= $_REQUEST['INTERNAL_ID'];

$res = $db->Execute("SELECT * from Z_TICKET WHERE INTERNAL_ID = '$INTERNAL_ID' ");
$PK_TICKET_CATEGORY_OLD	= $res->fields['PK_TICKET_CATEGORY'];
$PK_TICKET				= $res->fields['PK_TICKET'];

$TICKET_1['PK_TICKET_CATEGORY'] = $PK_TICKET_CATEGORY;
db_perform('Z_TICKET', $TICKET_1, 'update'," INTERNAL_ID = '$INTERNAL_ID' ");

echo 1;