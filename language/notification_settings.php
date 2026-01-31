<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("NOTIFICATION_SETTINGS_PAGE_TITLE", "Notification Settings");
	define("EVENT_TYPE", "Notification Type");
	define("MESSAGE", "Message");
	define("RECIPIENTS", "Recipients");
	define("TAGS", "Tags");
	define("CREATE_TASK", "Create Task");
	define("TASK_TYPE", "Task type");
	define("MARK_TASK_AS_COMPLETE", "Mark Task As Complete");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("NOTIFICATION_SETTINGS_PAGE_TITLE", "Notification Settings");
	define("EVENT_TYPE", "Notification Type");
	define("MESSAGE", "Message");
	define("RECIPIENTS", "Recipients");
	define("TAGS", "Tags");
	define("CREATE_TASK", "Create Task");
	define("TASK_TYPE", "Task type");
	define("MARK_TASK_AS_COMPLETE", "Mark Task As Complete");
}