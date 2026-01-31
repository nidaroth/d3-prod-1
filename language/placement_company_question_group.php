<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PLACEMENT_COMPANY_QUESTION_GROUP_PAGE_TITLE", "Company Question Group");
	define("PLACEMENT_COMPANY_QUESTION_GROUP", "Company Question Group");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("PLACEMENT_COMPANY_QUESTION_GROUP_PAGE_TITLE", "Company Question Group");
	define("PLACEMENT_COMPANY_QUESTION_GROUP", "Company Question Group");
}