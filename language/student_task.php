<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("TASK_PAGE_TITLE", "Tasks");
	define("TASK_DATE", "Task Date");
	define("TASK_TIME", "Time");
	define("TASK_TYPE", "Task Type");
	define("TASK_STATUS", "Task Status");
	define("FOLLOWUP_DATE", "Follow Up Date");
	define("COMPLETED", "Completed");
	define("EMPLOYEE", "Employee");
	define("COMMENTS", "Comments");
	define("ADD_ATTACHMENTS", "Add Attachments");
	define("DOCUMENT", "Document");
	define("PRIORITY", "Priority");
	define("TASK_OTHER", "Task Other");
	define("ENROLLMENT", "Enrollment");
	define("RECURRING_TYPE", "Recurring Type");
	define("NO_OF_TIMES", "No. of Times");
	
	define("FROM_TASK_DATE", "From Task Date");
	define("TO_TASK_DATE", "To Task Date");
	define("FROM_TASK_DATE", "From Task Date");
	define("TO_TASK_DATE", "To Task Date");
	define("FROM_FOLLOWUP_DATE", "From Follow Up Date");
	define("TO_FOLLOWUP_DATE", "To Follow Up Date");
	define("SHOW_ON_ALL_DEP", "Show On All Departments");
	define("TASK_COMPLETED", "Task Completed");
	define("SELECTED_COUNT", "Select Count");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("TASK_PAGE_TITLE", "Tasks");
	define("TASK_DATE", "Task Date");
	define("TASK_TIME", "Time");
	define("TASK_TYPE", "Task Type");
	define("TASK_STATUS", "Task Status");
	define("FOLLOWUP_DATE", "Follow Up Date");
	define("COMPLETED", "Completed");
	define("EMPLOYEE", "Employee");
	define("COMMENTS", "Comments");
	define("ADD_ATTACHMENTS", "Add Attachments");
	define("DOCUMENT", "Document");
	define("PRIORITY", "Priority");
	define("TASK_OTHER", "Task Other");
	define("RECURRING_TYPE", "Recurring Type");
	define("NO_OF_TIMES", "No. of Times");
	
	define("FROM_TASK_DATE", "From Task Date");
	define("TO_TASK_DATE", "To Task Date");
	define("FROM_TASK_DATE", "From Task Date");
	define("TO_TASK_DATE", "To Task Date");
	define("FROM_FOLLOWUP_DATE", "From Follow Up Date");
	define("TO_FOLLOWUP_DATE", "To Follow Up Date");
	define("SHOW_ON_ALL_DEP", "Show On All Departments");
	define("TASK_COMPLETED", "Task Completed");
	define("SELECTED_COUNT", "Select Count");
}