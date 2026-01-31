<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("STUDENT_NOTES_PAGE_TITLE", "Student Notes");
	define("SELECT_STUDENT", "Select Student");
	define("NOTE_DATE", "Note Date");
	define("NOTE_TYPE", "Note Type");
	define("STATUS", "Status");
	define("EMPLOYEE", "Employee");
	define("FOLLOW_UP_DATE", "Follow-Up Date");
	define("COMPLETED", "Completed");
	define("COMMENTS", "Comments");
	define("CREATE_NOTES", "Create Note");
	define("NOTE_TIME", "Note TIme");
	define("FOLLOWUP_DATE", "Follow-Up Date");
	define("ADD_ATTACHMENTS", "Add Attachments");
	define("EVENT_PAGE_TITLE", "Student Event");
	define("TASK_PAGE_TITLE", "Student Event");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("STUDENT_NOTES_PAGE_TITLE", "Student Notes");
	define("SELECT_STUDENT", "Select Student");
	define("NOTE_DATE", "Note Date");
	define("NOTE_TYPE", "Note Type");
	define("STATUS", "Status");
	define("EMPLOYEE", "Employee");
	define("FOLLOW_UP_DATE", "Follow-Up Date");
	define("COMPLETED", "Completed");
	define("COMMENTS", "Comments");
	define("CREATE_NOTES", "Create Note");
	define("NOTE_TIME", "Note TIme");
	define("FOLLOWUP_DATE", "Follow-Up Date");
	define("ADD_ATTACHMENTS", "Add Attachments");
	define("EVENT_PAGE_TITLE", "Student Event");
}