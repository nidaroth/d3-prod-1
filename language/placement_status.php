<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PLACEMENT_STATUS_PAGE_TITLE", "Placement Status");
	define("PLACEMENT_STATUS", "Placement Status");
	define("EMPLOYED", "Employed");
	define("CATEGORY", "Category");
	define("CBO_ACTIVE", "CBO Active");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("PLACEMENT_STATUS_PAGE_TITLE", "Placement Status");
	define("PLACEMENT_STATUS", "Placement Status");
	define("EMPLOYED", "Employed");
	define("CATEGORY", "Category");
	define("CBO_ACTIVE", "CBO Active");
}