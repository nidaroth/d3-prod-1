<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("NOTE_TYPE_PAGE_TITLE", "Student Note Types");
	define("EVENT_TYPE_PAGE_TITLE", "Student Event Types");
	define("DEPARTMENT", "Department");
	define("NOTE_TYPE", "Note Type");
	define("EVENT_TYPE", "Event Type");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("NOTE_TYPE_PAGE_TITLE", "Student Note Types");
	define("EVENT_TYPE_PAGE_TITLE", "Student Event Types");
	define("DEPARTMENT", "Department");
	define("NOTE_TYPE", "Note Type");
	define("EVENT_TYPE", "Event Type");
}