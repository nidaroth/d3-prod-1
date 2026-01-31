<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("MNU_POINTS_SESSION_ENTRY", "Points Sessions Entry");
	define("SELECT_TERM", "Select Term");
	define("SELECT_COURSE_OFFERING", "Select Course Offering");
	define("SELECT_STUDENT", "Select Student");
	define("BY_ASSIGNMENT", "By Assignment");
	define("BY_LAB", "By Lab");
	define("BY_TEST", "By Test");
	define("BY_STUDENT", "By Student");
	define("GRADE_BOOK_CODE", "Grade Book Code");
	define("GRADE_BOOK_DESCRIPTION", "Grade Book Description");
	define("GRADE_BOOK_TYPE", "Grade Book Type");
	define("POINTS_REQUIRED", "Points Required");
	define("COMPLETED_DATE", "Completed Date");
	define("SESSION_REQUIRED", "Sessions Required");
	define("SESSION_COMPLETED", "Sessions Completed");
	define("HOURS_COMPLETED", "Hours Completed");
	define("POINTS_EARNED", "Points Earned");
	define("HOURS_REQUIRED", "Hours Required");
	define("SELECT_LAB", "Select Lab");
	define("SELECT_TEST", "Select Test");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("MNU_POINTS_SESSION_ENTRY", "Points Sessions Entry");
	define("SELECT_TERM", "Select Term");
	define("SELECT_COURSE_OFFERING", "Select Course Offering");
	define("SELECT_STUDENT", "Select Student");
	define("BY_ASSIGNMENT", "By Assignment");
	define("BY_STUDENT", "By Student");
	define("GRADE_BOOK_CODE", "Grade Book Code");
	define("GRADE_BOOK_DESCRIPTION", "Grade Book Description");
	define("GRADE_BOOK_TYPE", "Grade Book Type");
	define("POINTS_REQUIRED", "Points Required");
	define("COMPLETED_DATE", "Completed Date");
	define("SESSION_REQUIRED", "Sessions Required");
	define("SESSION_COMPLETED", "Sessions Completed");
	define("HOURS_COMPLETED", "Hours Completed");
	define("POINTS_EARNED", "Points Earned");
	define("HOURS_REQUIRED", "Hours Required");
}