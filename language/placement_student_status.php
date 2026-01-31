<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PLACEMENT_STUDENT_STATUS_PAGE_TITLE", "Placement Student Status");
	define("PLACEMENT_STUDENT_STATUS", "Placement Student Status");
	define("PLACEMENT_STUDENT_STATUS_CATEGORY", "Placement Student Status Category");
	define("QUESTIONS", "Questions");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("PLACEMENT_STUDENT_STATUS_PAGE_TITLE", "Placement Student Status");
	define("PLACEMENT_STUDENT_STATUS", "Placement Student Status");
	define("PLACEMENT_STUDENT_STATUS_CATEGORY", "Placement Student Status Category");
	define("QUESTIONS", "Questions");
}