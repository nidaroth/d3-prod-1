<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("EVENT_OTHER_PAGE_TITLE", "Student Event Other");
	define("TASK_OTHER_PAGE_TITLE", "Student Task Other");
	define("EVENT_OTHER", "Event Other");
	define("TASK_OTHER", "Task Other");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("EVENT_OTHER_PAGE_TITLE", "Student Event Other");
	define("TASK_OTHER_PAGE_TITLE", "Student Task Other");
	define("EVENT_OTHER", "Event Other");
	define("TASK_OTHER", "Task Other");

}