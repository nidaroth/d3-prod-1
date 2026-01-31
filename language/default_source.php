<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("DEFAULT_SOURCE", "Default Source");
	define("DESCRIPTION", "Description");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("DEFAULT_SOURCE", "Default Source");
	define("DESCRIPTION", "Description");
}