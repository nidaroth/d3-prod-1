<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("TASK_STATUS_PAGE_TITLE", "Task Status");
	define("TASK_STATUS", "Task Status");
	define("DESCRIPTION", "Description");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("TASK_STATUS_PAGE_TITLE", "Task Status");
	define("TASK_STATUS", "Task Status");
	define("DESCRIPTION", "Description");
}