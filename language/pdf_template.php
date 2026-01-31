<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("MAIL_TEMPLATE_PAGE_TITLE", "Document Template");
	define("TEMPLATE_NAME", "Template Name");
	define("SUBJECT", "Subject");
	define("CONTENT", "Content");
	define("TAGS", "Tags");
	define("DOCUMENT_TAGS", "Document Tags");
	define("DEPARTMENT", "Department");
	define("PRINT_ORIENTATION", "Print Orientation");
	
	define("CATEGORY", "Category");
	define("SUBCATEGORY", "Subcategory");
	define("PLEASE_COPY_PAST", "Please copy and paste the selected tag into the content editor window");
	define("NOTIFICATION", "Notification");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("MAIL_TEMPLATE_PAGE_TITLE", "Document Template");
	define("TEMPLATE_NAME", "Template Name");
	define("SUBJECT", "Subject");
	define("CONTENT", "Content");
	define("TAGS", "Tags");
	define("DOCUMENT_TAGS", "Document Tags");
	define("DEPARTMENT", "Department");
	define("PRINT_ORIENTATION", "Print Orientation");
	
	define("CATEGORY", "Category");
	define("SUBCATEGORY", "Subcategory");
	define("PLEASE_COPY_PAST", "Please copy and paste the selected tag into the content editor window");
	define("NOTIFICATION", "Notification");
}