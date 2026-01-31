<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("DAILY_ROSTER_PAGE_TITLE", "Daily Roster");
	define("SELECT_COURSE_OFFERING", "Select Course Offering");
	define("SCHEDULED_CLASS_MEETING", "Scheduled Class Meeting");
	define("GENERATE_REPORT", "Generate Report");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("DAILY_ROSTER_PAGE_TITLE", "Daily Roster");
	define("SELECT_COURSE_OFFERING", "Select Course Offering");
	define("SCHEDULED_CLASS_MEETING", "Scheduled Class Meeting");
	define("GENERATE_REPORT", "Generate Report");
}