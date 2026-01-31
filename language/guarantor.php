<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("GUARANTOR_PAGE_TITLE", "Guarantor");
	define("ITEM", "Item");
	define("DESCRIPTION", "Description");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("GUARANTOR_PAGE_TITLE", "Guarantor");
	define("ITEM", "Item");
	define("DESCRIPTION", "Description");
}