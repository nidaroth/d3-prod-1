<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PLACEMENT_COMPANY_QUESTIONNAIRE_PAGE_TITLE", "Company Questionnaire");
	define("PLACEMENT_COMPANY_QUESTIONNAIRE", "Company Questionnaire");
	define("PLACEMENT_COMPANY_QUESTION_GROUP", "Company Question Group");
	define("QUESTIONS", "Question");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("PLACEMENT_COMPANY_QUESTIONNAIRE_PAGE_TITLE", "Company Questionnaire");
	define("PLACEMENT_COMPANY_QUESTIONNAIRE", "Company Questionnaire");
	define("PLACEMENT_COMPANY_QUESTION_GROUP", "Company Question Group");
	define("QUESTIONS", "Question");
}