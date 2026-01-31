<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("EMPLOYEE_NOTE_STATUS_PAGE_TITLE", "Employee Note Status");
	define("STUDENT_NOTE_STATUS_PAGE_TITLE", "Student Note Status");
	define("STUDENT_EVENT_STATUS_PAGE_TITLE", "Student Event Status");
	define("NOTE_STATUS", "Note Status");
	define("EVENT_STATUS", "Event Status");
	define("DEPARTMENT", "Department");
	define("DEPARTMENTS", "Department");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("EMPLOYEE_NOTE_STATUS_PAGE_TITLE", "Employee Note Status");
	define("STUDENT_NOTE_STATUS_PAGE_TITLE", "Student Note Status");
	define("STUDENT_EVENT_STATUS_PAGE_TITLE", "Student Event Status");
	define("NOTE_STATUS", "Note Status");
	define("EVENT_STATUS", "Event Status");
	define("DEPARTMENT", "Department");
	define("DEPARTMENTS", "Department");
}