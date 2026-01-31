<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("TASK_TYPE_PAGE_TITLE", "Task Type");
	define("TASK_TYPE", "Task Type");
	define("DESCRIPTION", "Description");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("TASK_TYPE_PAGE_TITLE", "Task Type");
	define("TASK_TYPE", "Task Type");
	define("DESCRIPTION", "Description");
}