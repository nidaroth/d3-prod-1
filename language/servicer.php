<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("SERVICER_PAGE_TITLE", "Servicer");
	define("ITEM", "Item");
	define("DESCRIPTION", "Description");
	define("SERVICER_NAME", "Servicer Name");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("SERVICER_PAGE_TITLE", "Servicer");
	define("ITEM", "Item");
	define("DESCRIPTION", "Description");
	define("SERVICER_NAME", "Servicer Name");
}