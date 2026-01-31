<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("TO_DO_LIST_PAGE_TITLE", "To Do List");
	define("TO_DO_LIST", "To Do List");
	define("HEADER", "Header");
	define("COMPLETED", "Completed");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("TO_DO_LIST_PAGE_TITLE", "To Do List");
	define("TO_DO_LIST", "To Do List");
	define("HEADER", "Header");
	define("COMPLETED", "Completed");
}