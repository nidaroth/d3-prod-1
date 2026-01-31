<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PLACEMENT_STUDENT_EVENT_TYPE_PAGE_TITLE", "Placement Student Event Type");
	define("PLACEMENT_STUDENT_EVENT_TYPE", "Placement Student Event Type");
	define("EVENT_CODE", "Event Code");
	define("EVENT_DESCRIPTION", "Event Description");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("PLACEMENT_STUDENT_EVENT_TYPE_PAGE_TITLE", "Placement Student Event Type");
	define("PLACEMENT_STUDENT_EVENT_TYPE", "Placement Student Event Type");
	define("EVENT_CODE", "Event Code");
	define("EVENT_DESCRIPTION", "Event Description");
}