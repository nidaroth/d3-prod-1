<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("SELECT_STUDENT_PAGE_TITLE", "Select Student");
	define("STATUS", "Status");
	define("PROGRAM", "Program");
	define("FIRST_TERM", "First Term");
	define("GROUPS", "Groups");
	define("LAST_NAME", "Last Name");
	define("FIRST_NAME", "First Name");
	define("START_DATE", "Start Date");
	define("SELECTED_STUDENTS", "Selected Students");
	define("ALL_STUDENTS", "All Students");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("SELECT_STUDENT_PAGE_TITLE", "Select Student");
	define("STATUS", "Status");
	define("PROGRAM", "Program");
	define("FIRST_TERM", "First Term");
	define("GROUPS", "Groups");
	define("LAST_NAME", "Last Name");
	define("FIRST_NAME", "First Name");
	define("START_DATE", "Last Name");
	define("SELECTED_STUDENTS", "Selected Students");
	define("ALL_STUDENTS", "All Students");
}