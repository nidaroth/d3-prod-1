<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("DEFAULT_TYPE", "Default Type");
	define("DESCRIPTION", "Description");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("DEFAULT_TYPE", "Default Type");
	define("DESCRIPTION", "Description");
}