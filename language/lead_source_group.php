<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("LEAD_SOURCE_GROUP_PAGE_TITLE", "Lead Source Group");
	define("LEAD_SOURCE_GROUP", "Lead Source Group");
	define("DESCRIPTION", "Description");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("LEAD_SOURCE_GROUP_PAGE_TITLE", "Lead Source Group");
	define("LEAD_SOURCE_GROUP", "Lead Source Group");
	define("DESCRIPTION", "Description");
}