<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("EMAIL_TEMPLATE_PAGE_TITLE", "Email Template");
	define("TEMPLATE_NAME", "Template Name");
	define("SUBJECT", "Subject");
	define("CONTENT", "Content");
	define("TAGS", "Tags");
	define("EMAIL_ACCOUNT", "Email Account");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("EMAIL_TEMPLATE_PAGE_TITLE", "Email Template");
	define("TEMPLATE_NAME", "Template Name");
	define("SUBJECT", "Subject");
	define("CONTENT", "Content");
	define("TAGS", "Tags");
	define("EMAIL_ACCOUNT", "Email Account");
}