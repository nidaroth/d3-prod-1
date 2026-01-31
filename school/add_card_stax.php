<? require_once("../global/config.php"); 

if($_SESSION['PK_USER'] == 0){ 
	header("location:../index");
	exit;
}

if($_POST['s_id'] != '' && $_POST['customer_id'] != '' && $_POST['payment_method_id'] != ''){
	global $db;
	$STUDENT_CREDIT_CARD['CARD_NO']  			= 'XXXXXXXXXXXX'.$_POST['card_last_four'];
	$STUDENT_CREDIT_CARD['NAME_ON_CARD']  		= $_POST['card_name'];
	$STUDENT_CREDIT_CARD['CARD_EXP']  			= $_POST['card_exp'];
	$STUDENT_CREDIT_CARD['CARD_TYPE']  			= $_POST['card_type'];
	$STUDENT_CREDIT_CARD['CARD_ZIP']  			= $_POST['address_zip'];
	$STUDENT_CREDIT_CARD['CUSTOMER_ID']  		= $_POST['customer_id'];
	$STUDENT_CREDIT_CARD['PAYMENT_METHOD_ID']   = $_POST['payment_method_id'];
	$STUDENT_CREDIT_CARD['PK_STUDENT_MASTER']  	= $_POST['s_id'];
	$STUDENT_CREDIT_CARD['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
	$STUDENT_CREDIT_CARD['CREATED_BY'] 			= $_SESSION['PK_USER'];
	$STUDENT_CREDIT_CARD['CREATED_ON'] 			= date("Y-m-d H:i:s");

	$res = $db->Execute("SELECT PK_STUDENT_CREDIT_CARD_STAX FROM S_STUDENT_CREDIT_CARD_STAX WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_POST[s_id]' ");
	if($res->RecordCount() == 0)
    {
        $STUDENT_CREDIT_CARD['IS_PRIMARY'] = 1;
    }
	else{
		$STUDENT_CREDIT_CARD['IS_PRIMARY'] = 0;
	}
	db_perform('S_STUDENT_CREDIT_CARD_STAX', $STUDENT_CREDIT_CARD, 'insert');
	$PK_STUDENT_CREDIT_CARD = $db->insert_ID();

	//echo "1|||".$_POST['payment_method_id'];
	echo "1|||".$PK_STUDENT_CREDIT_CARD;
} 
else 
{
    echo "0|||MErrMsg";
}