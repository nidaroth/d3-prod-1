<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("SAP_WARNING", "SAP Warning");

} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("SAP_WARNING", "SAP Warning");
}