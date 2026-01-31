<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PLACEMENT_STUDENT_QUESTIONNAIRE_PAGE_TITLE", "Placement Student Questionnaire");
	define("PLACEMENT_STUDENT_QUESTIONNAIRE", "Placement Student Questionnaire");
	define("QUESTIONS", "Questions");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("PLACEMENT_STUDENT_QUESTIONNAIRE_PAGE_TITLE", "Placement Student Questionnaire");
	define("PLACEMENT_STUDENT_QUESTIONNAIRE", "Placement Student Questionnaire");
	define("QUESTIONS", "Questions");
}