<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ATTENDANCE_REVIEW_PAGE_TITLE", "Attendance Review");
	define("SELECT_TERM", "Select Term");
	define("SELECT_COURSE_OFFERING", "Select Course Offering");
	define("SUMMARY", "Summary");
	define("DETAIL_VIEW", "Detail View");
	define("HOURS_ATTENDED", "Hours Attended");
	define("HOURS_SCHEDULED", "Hours Schedule");
	define("ATTENDANCE_PERCENTAGE", "Attendance Percentage");
	define("ABSENTS", "Hours Absent");
	define("MOBILE_PHONE", "Mobile Phone");
	define("PREVIOUS", "Previous");
	define("EXPORT_TO_PDF", "Export To Pdf");
	define("DAYS_ABSENTS", "Days Absent");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ATTENDANCE_REVIEW_PAGE_TITLE", "Attendance Review");
	define("SELECT_TERM", "Select Term");
	define("SELECT_COURSE_OFFERING", "Select Course Offering");
	define("SUMMARY", "Summary");
	define("DETAIL_VIEW", "Detail View");
	define("HOURS_ATTENDED", "Hours Attended");
	define("HOURS_SCHEDULED", "Hours Schedule");
	define("ATTENDANCE_PERCENTAGE", "Attendance Percentage");
	define("ABSENTS", "Hours Absent");
	define("MOBILE_PHONE", "Mobile Phone");
	define("PREVIOUS", "Previous");
	define("EXPORT_TO_PDF", "Export To Pdf");
	define("DAYS_ABSENTS", "Days Absent");
}