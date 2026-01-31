<? require_once("../global/config.php"); 
$INTERNAL_ID = $_REQUEST['id'];
$res = $db->Execute("select * from Z_INTERNAL_EMAIL_STARRED WHERE INTERNAL_ID = '$INTERNAL_ID' AND PK_USER = '$_SESSION[PK_USER]' ");
if($res->RecordCount() == 0){
	$EMAIL_STARRED['PK_USER'] 		= $_SESSION['PK_USER'];
	$EMAIL_STARRED['INTERNAL_ID'] 	= $INTERNAL_ID;
	$EMAIL_STARRED['STARRED'] 		= 1;
	$EMAIL_STARRED['CREATED_ON'] 	= date("Y-m-d h:i:s");
	db_perform('Z_INTERNAL_EMAIL_STARRED', $EMAIL_STARRED, 'insert');
	
	echo "gold";
} else if($res->fields['STARRED'] == 1){
	$EMAIL_STARRED['STARRED'] 		= 0;
	$EMAIL_STARRED['EDITED_ON'] 	= date("Y-m-d h:i:s");
	db_perform('Z_INTERNAL_EMAIL_STARRED', $EMAIL_STARRED, 'update'," PK_INTERNAL_EMAIL_STARRED = ".$res->fields['PK_INTERNAL_EMAIL_STARRED']);
	
	echo "#DDDDDD";
} else if($res->fields['STARRED'] == 0){
	$EMAIL_STARRED['STARRED'] 		= 1;
	$EMAIL_STARRED['EDITED_ON'] 	= date("Y-m-d h:i:s");
	db_perform('Z_INTERNAL_EMAIL_STARRED', $EMAIL_STARRED, 'update'," PK_INTERNAL_EMAIL_STARRED = ".$res->fields['PK_INTERNAL_EMAIL_STARRED']);
	
	echo "gold";
}
?>