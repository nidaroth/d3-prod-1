<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PLACEMENT_COMPANY_STATUS_PAGE_TITLE", "Company Status");
	define("PLACEMENT_COMPANY_STATUS", "Company Status");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("PLACEMENT_COMPANY_STATUS_PAGE_TITLE", "Company Status");
	define("PLACEMENT_COMPANY_STATUS", "Company Status");
}