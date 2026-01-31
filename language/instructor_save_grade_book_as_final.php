<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("MNU_SAVE_GRADE_BOOK_AS_FINAL", "Save Grade Book As Final");
	define("SELECT_TERM", "Select Term");
	define("SELECT_COURSE_OFFERING", "Select Course Offering");
	define("GRADE", "Grade");
	define("FINAL_GRADE", "Final Grade");
	define("COMPLETED_CLASS_MEETINGS", "Completed Class Meetings");
	define("SCHEDULED_CLASS_MEETINGS", "Scheduled Class Meetings");
	define("FIRST_CLASS_DATE", "First Class Date");
	define("LAST_CLASS_DATE", "Last Class Date");
	define("STUDENTS", "Students");
	define("POINTS", "Points");
	define("PERCENTAGE", "Percentage");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("MNU_SAVE_GRADE_BOOK_AS_FINAL", "Save Grade Book As Final");
	define("SELECT_TERM", "Select Term");
	define("SELECT_COURSE_OFFERING", "Select Course Offering");
	define("GRADE", "Grade");
	define("FINAL_GRADE", "Final Grade");
	define("COMPLETED_CLASS_MEETINGS", "Completed Class Meetings");
	define("SCHEDULED_CLASS_MEETINGS", "Scheduled Class Meetings");
	define("FIRST_CLASS_DATE", "First Class Date");
	define("LAST_CLASS_DATE", "Last Class Date");
	define("STUDENTS", "Students");
	define("POINTS", "Points");
	define("PERCENTAGE", "Percentage");
}