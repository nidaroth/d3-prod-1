<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PLACEMENT_VERIFICATION_SOURCE_PAGE_TITLE", "Verification Source");
	define("PLACEMENT_VERIFICATION_SOURCE", "Verification Source");
	define("VERIFICATION_SOURCE", "Verification Source");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("PLACEMENT_VERIFICATION_SOURCE_PAGE_TITLE", "Verification Source");
	define("PLACEMENT_VERIFICATION_SOURCE", "Verification Source");
	define("VERIFICATION_SOURCE", "Verification Source");
}