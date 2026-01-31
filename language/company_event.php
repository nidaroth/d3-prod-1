<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("COMPANY_EVENT_PAGE_TITLE", "Company Event");
	define("EVENT_TYPE", "Event Type");
	define("EVENT_DATE", "Event Date");
	define("FOLLOW_UP_DATE", "Follow Up Date");
	define("EMPLOYEE", "School Employee");
	define("CONTACT", "Company Contact");
	define("COMPLETE", "Complete");
	define("NOTE", "Notes");
	define("ADD_ATTACHMENTS", "Add Attachments");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("COMPANY_EVENT_PAGE_TITLE", "Company Event");
	define("EVENT_TYPE", "Event Type");
	define("EVENT_DATE", "Event Date");
	define("FOLLOW_UP_DATE", "Follow Up Date");
	define("EMPLOYEE", "School Employee");
	define("CONTACT", "Company Contact");
	define("COMPLETE", "Complete");
	define("NOTE", "Notes");
	define("ADD_ATTACHMENTS", "Add Attachments");
}