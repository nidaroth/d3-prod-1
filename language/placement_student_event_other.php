<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PLACEMENT_STUDENT_EVENT_OTHER_PAGE_TITLE", "Placement Student Event Other");
	define("PLACEMENT_STUDENT_EVENT_OTHER", "Placement Student Event Other");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("PLACEMENT_STUDENT_EVENT_OTHER_PAGE_TITLE", "Placement Student Event Other");
	define("PLACEMENT_STUDENT_EVENT_OTHER", "Placement Student Event Other");
}