<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("AR_FEE_TYPE_PAGE_TITLE", "Fee Type");
	define("AR_FEE_TYPE", "Fee Type");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("AR_FEE_TYPE_PAGE_TITLE", "Fee Type");
	define("AR_FEE_TYPE", "Fee Type");
}