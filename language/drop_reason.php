<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("DROP_REASON_PAGE_TITLE", "Drop Reason");
	define("DROP_REASON", "Drop Reason");
	define("CODE", "Code");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("DROP_REASON_PAGE_TITLE", "Drop Reason");
	define("DROP_REASON", "Drop Reason");
	define("CODE", "Code");
}