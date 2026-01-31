<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("AR_PAYMENT_TYPE_PAGE_TITLE", "Payment Type");
	define("AR_PAYMENT_TYPE", "Payment Type");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("AR_PAYMENT_TYPE_PAGE_TITLE", "Payment Type");
	define("AR_PAYMENT_TYPE", "Payment Type");
}