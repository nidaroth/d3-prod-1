<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("SESSION_PAGE_TITLE", "Session");
	define("SESSION", "Session");
	define("COLOR", "Color");
	define("DISPLAY_ORDER", "Display Order");
	define("SESSION_ABBREVIATION", "Session Abbreviation");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("SESSION_PAGE_TITLE", "Session");
	define("SESSION", "Session");
	define("COLOR", "Color");
	define("DISPLAY_ORDER", "Display Order");
	define("SESSION_ABBREVIATION", "Session Abbreviation");
}