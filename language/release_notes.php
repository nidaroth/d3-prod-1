<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("RELEASE_NOTES", "Release Notes");
	define("DATE", "Date");
	define("CATEGORY", "Category");
	define("TYPE", "Type");
	define("SUBJECT", "Subject");
	define("LOCATION", "Location");
	define("KNOWLEDGE_BASE_LINK", "Knowledge Base Link");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("RELEASE_NOTES", "Release Notes");
	define("DATE", "Date");
	define("CATEGORY", "Category");
	define("TYPE", "Type");
	define("SUBJECT", "Subject");
	define("LOCATION", "Location");
	define("KNOWLEDGE_BASE_LINK", "Knowledge Base Link");
}