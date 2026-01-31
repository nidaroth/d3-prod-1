<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PLACEMENT_COMPANY_EVENT_TYPE_PAGE_TITLE", "Company Event Type");
	define("PLACEMENT_COMPANY_EVENT_TYPE", "Company Event Type");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("PLACEMENT_COMPANY_EVENT_TYPE_PAGE_TITLE", "Company Event Type");
	define("PLACEMENT_COMPANY_EVENT_TYPE", "Company Event Type");
}