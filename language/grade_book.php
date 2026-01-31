<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("TERM", "Term");
	define("COURSE", "Course");
	define("DESCRIPTION", "Description");
	define("STUDENT_POINTS", "Student Points");
	define("TOTAL_POINTS", "Total Points");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("TERM", "Term");
	define("COURSE", "Course");
	define("DESCRIPTION", "Description");
	define("STUDENT_POINTS", "Student Points");
	define("TOTAL_POINTS", "Total Points");
}