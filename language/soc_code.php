<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("SOC_CODE_PAGE_TITLE", "SOC Code");
	define("SOC_CODE", "SOC Code");
	define("SOC_TITLE", "SOC Title");
	define("IPEDS_CATEGORY", "IPEDS Category");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("SOC_CODE_PAGE_TITLE", "SOC Code");
	define("SOC_CODE", "SOC Code");
	define("SOC_TITLE", "SOC Title");
	define("IPEDS_CATEGORY", "IPEDS Category");
}