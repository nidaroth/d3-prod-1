<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PLACEMENT_TYPE_PAGE_TITLE", "Placement Type");
	define("PLACEMENT_TYPE", "Placement Type");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("PLACEMENT_TYPE_PAGE_TITLE", "Placement Type");
	define("PLACEMENT_TYPE", "Placement Type");
}