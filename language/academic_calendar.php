<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ACADEMIC_CALENDAR_PAGE_TITLE", "Academic Calendar");
	define("LEAVE_TYPE", "Type");
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("SESSION", "Session");
	define("TITLE", "Title");
	define("SELECT_ALL", "Select All");
	define("VIEW_IN_PDF", "View In PDF");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ACADEMIC_CALENDAR_PAGE_TITLE", "Academic Calendar");
	define("LEAVE_TYPE", "Type");
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("SESSION", "Session");
	define("TITLE", "Title");
	define("SELECT_ALL", "Select All");
	define("VIEW_IN_PDF", "View In PDF");
}