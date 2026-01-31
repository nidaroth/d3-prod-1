<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("EMPLOYEE", "Employee");
	define("COMPANY", "Company");
	
	define("EVENT_TYPE", "Event Type");
	define("EVENT_STATUS", "Event Status");
	define("EVENT_OTHER", "Event Other");
	define("EVENT_COMPLETED", "Event Completed");
	define("FIRST_TERM", "First Term");
	
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("EMPLOYEE", "Employee");
	define("COMPANY", "Company");
	
	define("EVENT_TYPE", "Event Type");
	define("EVENT_STATUS", "Event Status");
	define("EVENT_OTHER", "Event Other");
	define("EVENT_COMPLETED", "Event Completed");	
	define("FIRST_TERM", "First Term");
	
}