<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("LEAD_SOURCE_PAGE_TITLE", "Lead Source");
	define("LEAD_SOURCE", "Lead Source");
	define("LEAD_GROUP", "Lead Source Group");
	define("DESCRIPTION", "Description");
	
	define("CONSOLIDATE_LEAD_SOURCE", "Consolidate Lead Source");
	define("LEAD_SOURCE_TO_KEEP", "Lead Source To Keep");
	define("LEAD_SOURCE_TO_CONDOLIDATE", "Lead Source To Delete");
	define("CONSOLIDATE_LEAD_SOURCE_WARNING", "WARNING: Deleted records cannot be restored");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("LEAD_SOURCE_PAGE_TITLE", "Lead Source");
	define("LEAD_SOURCE", "Lead Source");
	define("LEAD_GROUP", "Lead Source Group");
	define("DESCRIPTION", "Description");
	
	define("CONSOLIDATE_LEAD_SOURCE", "Consolidate Lead Source");
	define("LEAD_SOURCE_TO_KEEP", "Lead Source To Keep");
	define("LEAD_SOURCE_TO_CONDOLIDATE", "Lead Source To Delete");
	define("CONSOLIDATE_LEAD_SOURCE_WARNING", "WARNING: Deleted records cannot be restored");

}