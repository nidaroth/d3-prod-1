<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ACCUMULATIVE_GPA", "Accumulative GPA");
	define("SCHEDULED_HOURS", "Scheduled Hours");
	define("ATTENDED_HOUR", "Attended Hours");
	define("ATTENDANCE_PERCENTAGE", "Attendance Percentage");
	define("TRANSFER_HOURS", "Transfer Hours");
	define("TOTAL_REQUIRED_HOURS", "Total Required Hours");
	define("TOTAL_HOURS_REMAINING", "Total Hours Remaining");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ACCUMULATIVE_GP", "Accumulative GPA");
	define("SCHEDULED_HOURS", "Scheduled Hours");
	define("ATTENDED_HOUR", "Attended Hours");
	define("ATTENDANCE_PERCENTAGE", "Attendance Percentage");
	define("TRANSFER_HOURS", "Transfer Hours");
	define("TOTAL_REQUIRED_HOURS", "Total Required Hours");
	define("TOTAL_HOURS_REMAINING", "Total Hours Remaining");
}