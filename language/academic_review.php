<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ACADEMIC_REVIEW_PAGE_TITLE", "Academic Review");
	define("TERM", "Term");
	define("COURSE", "Course");
	define("DESCRIPTION", "Information");
	define("UNITS_ATTEMPTED", "Units Attempted");
	define("UNITS_COMPLETED", "Units Completed");
	define("GPA", "GPA");
	define("GRADE", "Grade");
	define("TERM_TOTAL", "Term Total");
	define("CUMULATIVE_TOTAL", "Cumulative Total");
	define("COURSE_DESCRIPTION", "Course Description");
	define("NUMERIC_GRADE", "Numeric Grade");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ACADEMIC_REVIEW_PAGE_TITLE", "Academic Review");
	define("TERM", "Term");
	define("COURSE", "Course");
	define("DESCRIPTION", "Information");
	define("UNITS_ATTEMPTED", "Units Attempted");
	define("UNITS_COMPLETED", "Units Completed");
	define("GPA", "GPA");
	define("GRADE", "Grade");
	define("TERM_TOTAL", "Term Total");
	define("CUMULATIVE_TOTAL", "Cumulative Total");
	define("COURSE_DESCRIPTION", "Course Description");
	define("NUMERIC_GRADE", "Numeric Grade");
}