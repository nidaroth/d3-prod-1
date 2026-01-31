<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PLACEMENT_STUDENT_NOTE_TYPE_PAGE_TITLE", "Placement Student Note Type");
	define("PLACEMENT_STUDENT_NOTE_TYPE", "Placement Student Note Type");
	define("DISPLAY_ORDER", "Display Order");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("PLACEMENT_STUDENT_NOTE_TYPE_PAGE_TITLE", "Placement Student Note Type");
	define("PLACEMENT_STUDENT_NOTE_TYPE", "Placement Student Note Type");
	define("DISPLAY_ORDER", "Display Order");
}