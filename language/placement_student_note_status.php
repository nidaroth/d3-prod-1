<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PLACEMENT_STUDENT_NOTE_STATUS_PAGE_TITLE", "Student Note Status");
	define("PLACEMENT_STUDENT_NOTE_STATUS", "Student Note Status");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("PLACEMENT_STUDENT_NOTE_STATUS_PAGE_TITLE", "Student Note Status");
	define("PLACEMENT_STUDENT_NOTE_STATUS", "Student Note Status");
}