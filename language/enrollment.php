<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ENROLLMENT_PAGE_TITLE", "Enrollment");
	define("TERM", "Term");
	define("COURSE", "Course");
	define("COURSE_INFO", "Course Information");
	define("REGISTRATION_MESSAGE", "Registration Message");
	define("ACTION", "Action");
	
	define("CLASS_SIZE", "Class Size");
	define("INSTRUCTOR", "Instructor");
	define("ROOM", "Room");
	define("SCHEDULE", "Schedule");
	define("UNITS", "Units");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ENROLLMENT_PAGE_TITLE", "Enrollment");
	define("TERM", "Term");
	define("COURSE", "Course");
	define("COURSE_INFO", "Course Information");
	define("REGISTRATION_MESSAGE", "Registration Message");
	define("ACTION", "Action");
	
	define("CLASS_SIZE", "Class Size");
	define("INSTRUCTOR", "Instructor");
	define("ROOM", "Room");
	define("SCHEDULE", "Schedule");
	define("UNITS", "Units");
}