<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("DASHBOARD_PAGE_TITLE", "Dashboard");
	define("WELCOME", "Welcome");
	define("ANNOUNCEMENT", "Announcements");
	define("INTERNAL_MAILS", "Internal Mails");
	define("PAYMENT_SCHEDULE", "Payment Schedule");
	define("INTERNAL_MESSAGE", "Internal Messages");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("DASHBOARD_PAGE_TITLE", "Dashboard");
	define("WELCOME", "Welcome");
	define("ANNOUNCEMENT", "Announcements");
	define("INTERNAL_MAILS", "Internal Mails");
	define("PAYMENT_SCHEDULE", "Payment Schedule");
	define("MESSAGE", "Internal Messages");
}