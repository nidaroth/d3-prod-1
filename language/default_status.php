<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("DEFAULT_STATUS", "Default status");
	define("IN_DEFAULT", "In Default");

	define("DESCRIPTION", "Description");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("DEFAULT_STATUS", "Default status");
	define("IN_DEFAULT", "In Default");
	define("DESCRIPTION", "Description");
}