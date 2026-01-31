<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("COMPANY_CONTACT_PAGE_TITLE", "Company Contact");
	define("DEPARTMENT", "Department");
	define("TITLE", "Title");
	define("PLACEMENT_TYPE", "Type");
	define("COMMENT", "Comment");
	define("MOBILE", "Mobile");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("COMPANY_CONTACT_PAGE_TITLE", "Company Contact");
	define("DEPARTMENT", "Department");
	define("TITLE", "Title");
	define("PLACEMENT_TYPE", "Type");
	define("COMMENT", "Comment");
	define("MOBILE", "Mobile");
}