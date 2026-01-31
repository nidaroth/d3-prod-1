<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("SCHOOL_REQUIREMENTS_PAGE_TITLE", "School Requirements");
	define("REQUIREMENT", "Requirement");
	define("MANDATORY", "Mandatory");
	define("CATEGORY", "Category");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("SCHOOL_REQUIREMENTS_PAGE_TITLE", "School Requirements");
	define("REQUIREMENT", "Requirement");
	define("MANDATORY", "Mandatory");
	define("CATEGORY", "Category");
}