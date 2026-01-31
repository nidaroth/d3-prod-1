<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ATTENDANCE_ENTRY", "Attendance Entry");
	define("SELECT_COURSE_OFFERING", "Select Course Offering");
	define("SCHEDULED_CLASS_MEETING", "Scheduled Class Meeting");
	define("SHOW", "Show");
	define("SELECT_TERM", "Select Term");
	define("GENERATE", "Generate");
	define("SELECT_FIRST_DATE", "Select First Date");
	define("SELECT_LAST_DATE", "Select Last Date");
	define("SELECT_LAST_DATE", "Select Last Date");
	define("SELECT_STUDENT", "Select Student");
	define("COURSE_OFFERING", "Course Offering");
	define("INSTRUCTOR_SCHEDULE", "Instructor Schedule");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ATTENDANCE_ENTRY", "Attendance Entry");
	define("SELECT_COURSE_OFFERING", "Select Course Offering");
	define("SCHEDULED_CLASS_MEETING", "Scheduled Class Meeting");
	define("SHOW", "Show");
	define("SELECT_TERM", "Select Term");
	define("GENERATE", "Generate");
	define("SELECT_FIRST_DATE", "Select First Date");
	define("SELECT_LAST_DATE", "Select Last Date");
	define("SELECT_LAST_DATE", "Select Last Date");
	define("SELECT_STUDENT", "Select Student");
	define("COURSE_OFFERING", "Course Offering");
	define("INSTRUCTOR_SCHEDULE", "Instructor Schedule");
}