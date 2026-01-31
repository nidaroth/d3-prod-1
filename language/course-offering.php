<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("STUDENT_PAGE_TITLE", "Student");
	define("COURSE_OFFERING", "Course Offering");
	define("DATE", "Date");
	define("DD", "DD");
	define("START_TIME", "Start Time");
	define("END_TIME", "End Time");
	define("HOURS_ROOM", "Hours Room");
	define("COMPLETE", "Complete");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("STUDENT_PAGE_TITLE", "Student");
	define("COURSE_OFFERING", "Course Offering");
	define("DATE", "Date");
	define("DD", "DD");
	define("START_TIME", "Start Time");
	define("END_TIME", "End Time");
	define("HOURS_ROOM", "Hours Room");
	define("COMPLETE", "Complete");
}