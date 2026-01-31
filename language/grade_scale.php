<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("GRADE_SCALE_PAGE_TITLE", "Grade Scale Setup");
	define("GRADE_SCALE", "Grade Scale");
	define("MIN_PERCENTAGE", "Min. %");
	define("MAX_PERCENTAGE", "Max. %");
	define("GRADE_CALC", "Grade Calculation");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("GRADE_SCALE_PAGE_TITLE", "Grade Scale Setup");
	define("GRADE_SCALE", "Grade Scale");
	define("MIN_PERCENTAGE", "Min. %");
	define("MAX_PERCENTAGE", "Max. %");
	define("GRADE_CALC", "Grade Calculation");
}