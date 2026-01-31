<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("NOTIFICATION_SENT_PAGE_TITLE", "Notification Sent");
	define("NOTIFICATION_TO", "Notification To");
	define("NOTIFICATION_TYPE", "Notification Type");
	define("NOTIFICATION", "Notification");
	define("VIEW", "View");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("NOTIFICATION_SENT_PAGE_TITLE", "Notification Sent");
	define("NOTIFICATION_TO", "Notification To");
	define("NOTIFICATION_TYPE", "Notification Type");
	define("NOTIFICATION", "Notification");
	define("VIEW", "View");
}