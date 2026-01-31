<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ATTENDANCE_ACTIVITY_TYPE", "Attendance Activity Type");
	define("ACTIVITY_TYPE", "Activity Type");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ATTENDANCE_ACTIVITY_TYPE", "Attendance Activity Type");
	define("ACTIVITY_TYPE", "Activity Type");
}