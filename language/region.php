<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("REGION_PAGE_TITLE", "Region");
	define("REGION", "Region");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("REGION_PAGE_TITLE", "Region");
	define("REGION", "Region");
}