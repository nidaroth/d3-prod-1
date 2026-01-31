<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("COLLATERAL_PAGE_TITLE", "Documents");
	define("COLLATERAL", "Document");
	define("FILE_NAME", "File Name");
	define("FILE", "File");
	define("FILE_DELETE", "Are you sure you want to Delete this File?");
	define("VIEW_FILE", "View File");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("COLLATERAL_PAGE_TITLE", "Documents");
	define("COLLATERAL", "Document");
	define("FILE_NAME", "File Name");
	define("FILE", "File");
	define("FILE_DELETE", "Are you sure you want to Delete this File?");
	define("VIEW_FILE", "View File");
}