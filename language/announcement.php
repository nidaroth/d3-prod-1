<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ANNOUNCEMENT_PAGE_TITLE", "Announcements");
	define("HEADER", "Header");
	define("SHORT_DESC_ENG", "Short Description (English)");
	define("SHORT_DESC_SPA", "Short Description (Spanish)");
	define("DESC_ENG", "Description (English)");
	define("DESC_SPA", "Description (Spanish)");
	define("CAMPUS", "Campus");
	define("START_DATE", "Start Date");
	define("START_TIME", "Start Time");
	define("END_DATE", "End Date");
	define("END_TIME", "End Time");
	define("EMPLOYEES", "Employees");
	define("ANNOUNCEMENT_FOR", "Announcement For");
	define("STAFF", "Staff");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ANNOUNCEMENT_PAGE_TITLE", "Announcements");
	define("HEADER", "Header");
	define("SHORT_DESC_ENG", "Short Description (English)");
	define("SHORT_DESC_SPA", "Short Description (Spanish)");
	define("DESC_ENG", "Description (English)");
	define("DESC_SPA", "Description (Spanish)");
	define("CAMPUS", "Campus");
	define("START_DATE", "Start Date");
	define("START_TIME", "Start Time");
	define("END_DATE", "End Date");
	define("END_TIME", "End Time");
	define("EMPLOYEES", "Employees");
	define("ANNOUNCEMENT_FOR", "Announcement For");
	define("STAFF", "Staff");
}