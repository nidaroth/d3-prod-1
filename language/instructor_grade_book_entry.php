<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("MNU_GRADE_BOOK_ENTRY", "Grade Book Entry");
	define("SELECT_TERM", "Select Term");
	define("SELECT_COURSE_OFFERING", "Select Course Offering");
	define("GRADE", "Grade");
	define("BY_ASSIGNMENT", "By Assignment");
	define("BY_STUDENT", "By Student");
	define("COMPLETED_CLASS_MEETINGS", "Completed Class Meetings");
	define("SCHEDULED_CLASS_MEETINGS", "Scheduled Class Meetings");
	define("FIRST_CLASS_DATE", "First Class Date");
	define("LAST_CLASS_DATE", "Last Class Date");
	define("SELECT_ASSIGNMENT", "Select Assignment");
	define("SELECT_STUDENT", "Select Student");
	define("STUDENTS", "Students");
	define("POINTS", "Points");
	define("PERCENTAGE", "Percentage");
	define("ASSIGNMENT", "Assignment");
	define("GRADE_BOOK_POINTS", "Grade Book Points");
	define("TOTAL_POINTS_FOR_CLASS", "Total Points For Class");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("MNU_GRADE_BOOK_ENTRY", "Grade Book Entry");
	define("SELECT_TERM", "Select Term");
	define("SELECT_COURSE_OFFERING", "Select Course Offering");
	define("GRADE", "Grade");
	define("BY_ASSIGNMENT", "By Assignment");
	define("BY_STUDENT", "By Student");
	define("COMPLETED_CLASS_MEETINGS", "Completed Class Meetings");
	define("SCHEDULED_CLASS_MEETINGS", "Scheduled Class Meetings");
	define("FIRST_CLASS_DATE", "First Class Date");
	define("LAST_CLASS_DATE", "Last Class Date");
	define("SELECT_ASSIGNMENT", "Select Assignment");
	define("SELECT_STUDENT", "Select Student");
	define("STUDENTS", "Students");
	define("POINTS", "Points");
	define("PERCENTAGE", "Percentage");
	define("ASSIGNMENT", "Assignment");
	define("GRADE_BOOK_POINTS", "Grade Book Points");
	define("TOTAL_POINTS_FOR_CLASS", "Total Points For Class");
}