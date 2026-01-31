<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("FUNDING_PAGE_TITLE", "Funding");
	define("FUNDING", "Funding");
	define("CODE", "Code");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("FUNDING_PAGE_TITLE", "Funding");
	define("FUNDING", "Funding");
	define("CODE", "Code");
}