<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("COMPANY_SOURCE", "Company Source");
	define("DESCRIPTION", "Description");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("COMPANY_SOURCE", "Company Source");
	define("DESCRIPTION", "Description");
}