<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("QUESTIONNAIRE_PAGE_TITLE", "Questionnaire");
	define("QUESTIONNAIRE", "Questionnaire");
	define("DEPARTMENT", "Department");
	define("QUESTION", "Question");
	define("DISPLAY_ORDER", "Display Order");
	define("UPLOAD", "Upload");
	define("UPLOAD_FILE", "Upload File <br>(Excel and CSV only)");
	define("MAPPING", "Mapping");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("QUESTIONNAIRE_PAGE_TITLE", "Questionnaire");
	define("QUESTIONNAIRE", "Questionnaire");
	define("DEPARTMENT", "Department");
	define("QUESTION", "Question");
	define("DISPLAY_ORDER", "Display Order");
	define("UPLOAD", "Upload");
	define("UPLOAD_FILE", "Upload File <br>(Excel and CSV only)");
	define("MAPPING", "Mapping");
}