<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("LEAD_CONTACT_SOURCE_PAGE_TITLE", "Lead Contact Source");
	define("LEAD_CONTACT_SOURCE", "Lead Contact Source");
	define("DESCRIPTION", "Description");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("LEAD_CONTACT_SOURCE_PAGE_TITLE", "Lead Contact Source");
	define("LEAD_CONTACT_SOURCE", "Lead Contact Source");
	define("DESCRIPTION", "Description");
}