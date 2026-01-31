<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PLACEMENT_STUDENT_EVENT_STATUS_PAGE_TITLE", "Placement Student Event Status");
	define("PLACEMENT_STUDENT_EVENT_STATUS", "Placement Student Event Status");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("PLACEMENT_STUDENT_EVENT_STATUS_PAGE_TITLE", "Placement Student Event Status");
	define("PLACEMENT_STUDENT_EVENT_STATUS", "Placement Student Event Status");
}