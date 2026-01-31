<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("LETTER_GENERATOR_PAGE_TITLE", "Document Generator");
	define("TEMPLATE_NAME", "Template Name");
	define("COURSE_CODE", "Course");
	define("COURSE_OFFERING_PAGE_TITLE", "Course Offering");
	define("GROUP_CODE", "Group Code");
	define("FIRST_TERM", "First Term");
	define("PROGRAM", "Program");
	define("STATUS", "Status");
	define("GENERATE", "Generate");
	define("LEAD", "Lead");
	define("STUDENT", "Student");
	define("ADMISSION_REP", "Admission Rep");
	define("CAMPUS", "Campus");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("LETTER_GENERATOR_PAGE_TITLE", "Document Generator");
	define("TEMPLATE_NAME", "Template Name");
	define("COURSE_CODE", "Course");
	define("COURSE_OFFERING_PAGE_TITLE", "Course Offering");
	define("GROUP_CODE", "Group Code");
	define("FIRST_TERM", "First Term");
	define("PROGRAM", "Program");
	define("STATUS", "Status");
	define("GENERATE", "Generate");
	define("LEAD", "Lead");
	define("STUDENT", "Student");
	define("ADMISSION_REP", "Admission Rep");
	define("CAMPUS", "Campus");
}