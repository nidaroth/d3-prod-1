<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("TEXT_TEMPLATE_PAGE_TITLE", "Text Template");
	define("TEMPLATE_NAME", "Template Name");
	define("SUBJECT", "Subject");
	define("CONTENT", "Content");
	define("TAGS", "Tags");
	define("FROM_NO", "From #");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("TEXT_TEMPLATE_PAGE_TITLE", "Text Template");
	define("TEMPLATE_NAME", "Template Name");
	define("SUBJECT", "Subject");
	define("CONTENT", "Content");
	define("TAGS", "Tags");
	define("FROM_NO", "From #");
}