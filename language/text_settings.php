<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("TEXT_SETTINGS_PAGE_TITLE", "Text Settings");
	define("SID1", "SID");
	define("TOKEN", "Token");
	define("FROM_NO", "From #");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("TEXT_SETTINGS_PAGE_TITLE", "Text Settings");
	define("SID1", "SID");
	define("TOKEN", "Token");
	define("FROM_NO", "From #");
}