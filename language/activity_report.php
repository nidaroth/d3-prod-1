<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ACTIVITY_TYPE", "Activity Type");
	define("EMPLOYEE", "Employee");
	define("COMPANY", "Company");
	
	define("EVENT_TYPE", "Event Type");
	define("EVENT_STATUS", "Event Status");
	define("EVENT_OTHER", "Event Other");
	define("EVENT_COMPLETED", "Event Completed");
	
	define("NOTES_TYPE", "Notes Type");
	define("NOTES_STATUS", "Notes Status");
	define("NOTES_OTHER", "Notes Other");
	define("NOTES_COMPLETED", "Notes Completed");
	
	define("TASK_TYPE", "Task Type");
	define("TASK_STATUS", "Task Status");
	define("TASK_OTHER", "Task Other");
	define("TASK_COMPLETED", "Task Completed");
	
	define("SELECTED_COUNT", "Selected Count");
	define("FIRST_TERM", "First Term");
	define("PROBATION_STATUS", "Probation Status");
	define("PROBATION_LEVEL", "Probation Level");
	define("PROBATION_TYPE", "Probation Type");
	define("STUDENT_STATUS", "Student Status");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ACTIVITY_TYPE", "Activity Type");
	define("EMPLOYEE", "Employee");
	define("COMPANY", "Company");
	
	define("EVENT_TYPE", "Event Type");
	define("EVENT_STATUS", "Event Status");
	define("EVENT_OTHER", "Event Other");
	define("EVENT_COMPLETED", "Event Completed");
	
	define("NOTES_TYPE", "Notes Type");
	define("NOTES_STATUS", "Notes Status");
	define("NOTES_OTHER", "Notes Other");
	define("NOTES_COMPLETED", "Notes Completed");
	
	define("TASK_TYPE", "Task Type");
	define("TASK_STATUS", "Task Status");
	define("TASK_OTHER", "Task Other");
	define("TASK_COMPLETED", "Task Completed");
	
	define("SELECTED_COUNT", "Selected Count");
	define("FIRST_TERM", "First Term");
	define("PROBATION_STATUS", "Probation Status");
	define("PROBATION_LEVEL", "Probation Level");
	define("PROBATION_TYPE", "Probation Type");
	define("STUDENT_STATUS", "Student Status");
}