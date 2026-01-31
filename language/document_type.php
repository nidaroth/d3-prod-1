<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("DOCUMENT_TYPE_PAGE_TITLE", "Document Type");
	define("DOCUMENT_TYPE", "Document Type");
	define("CODE", "Code");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("DOCUMENT_TYPE_PAGE_TITLE", "Document Type");
	define("DOCUMENT_TYPE", "Document Type");
	define("CODE", "Code");
}