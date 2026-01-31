<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ATTENDANCE_SUMMARY_PAGE_TITLE", "Attendance Summary");
	define("TERM", "Term");
	define("COURSE", "Course");
	define("DESCRIPTION", "Information");
	define("SCHEDULED_TOTAL", "Scheduled Total");
	define("SCHEDULED_TO_DATE", "Scheduled To Date");
	define("ATTENDED_TO_DATE", "Attended To Date");
	define("ATTENDED_PERCENTAGE", "Attended Percentage");
	define("CLASS_DATE", "Class Date");
	define("SCHEDULED_START_TIME", "Scheduled Start Time");
	define("SCHEDULED_END_TIME", "Scheduled End Time");
	define("SCHEDULED_HOUR", "Scheduled Hour");
	define("CODE", "Code");
	define("ATTENDED_HOURS", "Attended Hours");
	define("SCHEDULED_COMPLETED", "Scheduled Complete");
	define("ATTENDED_COMPLETED", "Attended Complete");
	define("DATE_RANGE", "Date Range");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ATTENDANCE_SUMMARY_PAGE_TITLE", "Attendance Summary");
	define("TERM", "Term");
	define("COURSE", "Course");
	define("DESCRIPTION", "Information");
	define("SCHEDULED_TOTAL", "Scheduled Total");
	define("SCHEDULED_TO_DATE", "Scheduled To Date");
	define("ATTENDED_TO_DATE", "Attended To Date");
	define("ATTENDED_PERCENTAGE", "Attended Percentage");
	define("CLASS_DATE", "Class Date");
	define("SCHEDULED_START_TIME", "Scheduled Start Time");
	define("SCHEDULED_END_TIME", "Scheduled End Time");
	define("SCHEDULED_HOUR", "Scheduled Hour");
	define("CODE", "Code");
	define("ATTENDED_HOURS", "Attended Hours");
	define("SCHEDULED_COMPLETED", "Scheduled Complete");
	define("ATTENDED_COMPLETED", "Attended Complete");
	define("DATE_RANGE", "Date Range");
}