<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("TRANSFER_CREDIT_PAGE_TITLE", "Transfer Credit");
	define("SCHOOL_NAME", "School Name");
	define("YEAR", "Year");
	define("HOUR", "Hours");
	define("ENROLLMENT", "Enrollment");
	define("TERM", "Term");
	define("PREP", "Prep Hours");
	define("COURSE_CODE", "Course Code");
	define("TRANSFER_STATUS", "Transfer Status");
	define("UNITS", "Units");
	define("EQV_COURSE", "Equivalent Course");
	define("GRADE", "Grade");
	define("FA_UNITS", "FA Units");
	define("COURSE_DESC", "Course Description");
	define("NOTES", "Notes");
	define("NUMERIC_GRADE", "Numeric Grade");
	define("EQUIVALENT_COURSE_DESC", "Equivalent Course Description");
	define("THIS_INSTITUTION", "This Institution");
	define("PRIOR_INSTITUTION", "Prior Institution");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("TRANSFER_CREDIT_PAGE_TITLE", "Transfer Credit");
	define("SCHOOL_NAME", "School Name");
	define("YEAR", "Year");
	define("HOUR", "Hours");
	define("ENROLLMENT", "Enrollment");
	define("TERM", "Term");
	define("PREP", "Prep Hours");
	define("COURSE_CODE", "Course Code");
	define("TRANSFER_STATUS", "Transfer Status");
	define("UNITS", "Units");
	define("EQV_COURSE", "Equivalent Course");
	define("GRADE", "Grade");
	define("FA_UNITS", "FA Units");
	define("COURSE_DESC", "Course Description");
	define("NOTES", "Notes");
	define("NUMERIC_GRADE", "Numeric Grade");
	define("EQUIVALENT_COURSE_DESC", "Equivalent Course Description");
	define("THIS_INSTITUTION", "This Institution");
	define("PRIOR_INSTITUTION", "Prior Institution");
}