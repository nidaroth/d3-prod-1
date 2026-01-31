<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("COURSE_HISTORY_PAGE_TITLE", "Course History");
	define("INSTRUCTOR", "Instructor");
	define("TERM_BEGIN_DATE", "Term Begin Date");
	define("COURSE", "Course");
	define("COURSE_DESCRIPTION", "Course Description");
	define("STUDENT_IN_PROGRESS", "Students In Progress");
	define("TOTAL_STUDENTS", "Total Students");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("COURSE_HISTORY_PAGE_TITLE", "Course History");
	define("INSTRUCTOR", "Instructor");
	define("TERM_BEGIN_DATE", "Term Begin Date");
	define("COURSE", "Course");
	define("COURSE_DESCRIPTION", "Course Description");
	define("STUDENT_IN_PROGRESS", "Students In Progress");
	define("TOTAL_STUDENTS", "Total Students");
}