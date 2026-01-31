<? require_once("../global/config.php"); 

if($_SESSION['PK_USER'] == 0){ 
	header("location:../index");
	exit;
}

if(strtolower($_POST['FinalStatus']) == 'success'){
	if($_POST['IS_PRIMARY'] == 1) {
		$db->Execute("UPDATE S_STUDENT_CREDIT_CARD SET IS_PRIMARY = 0 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' ");
	}
	
	$STUDENT_CREDIT_CARD['CARD_NO']  			= $_POST['receiptcc'];
	$STUDENT_CREDIT_CARD['NAME_ON_CARD']  		= $_POST['card_name'];
	$STUDENT_CREDIT_CARD['CARD_EXP']  			= $_POST['card_exp'];
	$STUDENT_CREDIT_CARD['CARD_TYPE']  			= $_POST['card_type'];
	$STUDENT_CREDIT_CARD['CARD_ZIP']  			= $_POST['card_zip'];
	$STUDENT_CREDIT_CARD['ORDER_ID']  			= $_POST['orderID'];
	$STUDENT_CREDIT_CARD['IS_PRIMARY']  		= $_POST['IS_PRIMARY'];
	$STUDENT_CREDIT_CARD['TOKEN']  				= $_POST['token'];
	$STUDENT_CREDIT_CARD['CREATED_BY'] 			= $_SESSION['PK_USER'];
	$STUDENT_CREDIT_CARD['CREATED_ON'] 			= date("Y-m-d H:i:s");
	$STUDENT_CREDIT_CARD['PK_STUDENT_MASTER']  	= $_SESSION['PK_STUDENT_MASTER'];
	$STUDENT_CREDIT_CARD['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
	
	$res = $db->Execute("SELECT PK_STUDENT_CREDIT_CARD FROM S_STUDENT_CREDIT_CARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' ");
	if($res->RecordCount() == 0)
		$STUDENT_CREDIT_CARD['IS_PRIMARY'] = 1;
		
	db_perform('S_STUDENT_CREDIT_CARD', $STUDENT_CREDIT_CARD, 'insert');
	$PK_STUDENT_CREDIT_CARD = $db->insert_ID();

	echo "1|||".$PK_STUDENT_CREDIT_CARD;
} else 
	echo "0|||".$_POST['MErrMsg'];