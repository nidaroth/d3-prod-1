<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("EMPLOYE_NOTE_TYPE_PAGE_TITLE", "Employee Note Types");
	define("NOTE_TYPE", "Note Type");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("EMPLOYE_NOTE_TYPE_PAGE_TITLE", "Employee Note Types");
	define("NOTE_TYPE", "Note Type");
}