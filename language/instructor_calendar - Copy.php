<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("INSTRUCTOR_CALENDAR_PAGE_TITLE", "Instructor Calendar");
	define("SELECT_FIRST_DATE", "Select First Date");
	define("SELECT_LAST_DATE", "Select Last Date");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("INSTRUCTOR_CALENDAR_PAGE_TITLE", "Instructor Calendar");
	define("SELECT_FIRST_DATE", "Select First Date");
	define("SELECT_LAST_DATE", "Select Last Date");
}