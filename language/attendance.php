<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ATTENDNANCE_CODE", "Attendance Code");
	define("CODE", "Code");
	define("PRESENT", "Present");
	define("ABSENT", "Absent");
	define("CANCELLED", "Excluded");
	define("SCHEDULED", "Scheduled");
	define("INSTRUCTOR_PORTAL", "Instructor Portal");
	define("CODE", "Code");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ATTENDNANCE_CODE", "Attendance Code");
	define("CODE", "Code");
	define("PRESENT", "Present");
	define("ABSENT", "Absent");
	define("CANCELLED", "Excluded");
	define("SCHEDULED", "Scheduled");
	define("INSTRUCTOR_PORTAL", "Instructor Portal");
	define("CODE", "Code");
}