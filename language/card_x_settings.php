<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("CARD_X_TITLE", "CardX Settings");
	define("PUBLISHER_NAME", "Username");
	define("PUBLISHER_PASSWORD", "Remote Client Password");
	define("SITE_KEY", "Site Key");
	define("API_KEY_NAME", "API Key Name");
	define("API_KEY", "API Key");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("CARD_X_TITLE", "CardX Settings");
	define("PUBLISHER_NAME", "Username");
	define("PUBLISHER_PASSWORD", "Remote Client Password");
	define("SITE_KEY", "Site Key");
	define("API_KEY_NAME", "API Key Name");
	define("API_KEY", "API Key");
}