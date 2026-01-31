<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("SMTP_SETTINGS_PAGE_TITLE", "SMTP Settings");
	define("HOST", "Host Server Name");
	define("PORT", "Port");
	define("USER_NAME", "Email Address");
	define("PASSWORD", "Password");
	define("ENCRYPTION_TYPE", "Encryption Type");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("SMTP_SETTINGS_PAGE_TITLE", "SMTP Settings");
	define("HOST", "Host Server Name");
	define("PORT", "Port");
	define("USER_NAME", "Email Address");
	define("PASSWORD", "Password");
	define("ENCRYPTION_TYPE", "Encryption Type");
}