<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("DATE_TYPE", "Date Type");
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("EMPLOYEE", "Employee");
	define("TASK_COMPLETED", "Task Completed");
	define("RUN", "Run");
	define("EXPORT_TO_EXCEL", "Export To Excel");
	define("TASK_TYPE", "Task Type");
	define("TASK_STATUS", "Task Status");
	define("TASK_OTHER", "Task Other");
	
	define("PROGRAM", "Program");
	define("FIRST_TERM", "First Term");
	define("COMPLETED", "Completed");
	define("SELECTED_COUNT", "Selected Count");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("DATE_TYPE", "Date Type");
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("EMPLOYEE", "Employee");
	define("TASK_COMPLETED", "Task Completed");
	define("RUN", "Run");
	define("EXPORT_TO_EXCEL", "Export To Excel");
	define("TASK_TYPE", "Task Type");
	define("TASK_STATUS", "Task Status");
	define("TASK_OTHER", "Task Other");
	
	define("PROGRAM", "Program");
	define("FIRST_TERM", "First Term");
	define("COMPLETED", "Completed");
	define("SELECTED_COUNT", "Selected Count");
}