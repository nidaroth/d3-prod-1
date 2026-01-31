<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("MNU_FINAL_GRADE", "Final Grade");
	define("SELECT_TERM", "Select Term");
	define("SELECT_COURSE_OFFERING", "Select Course Offering");
	define("GRADE", "Grade");
	define("STUDENTS", "Students");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("MNU_FINAL_GRADE", "Final Grade");
	define("SELECT_TERM", "Select Term");
	define("SELECT_COURSE_OFFERING", "Select Course Offering");
	define("GRADE", "Grade");
	define("STUDENTS", "Students");
}