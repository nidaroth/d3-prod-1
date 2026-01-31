<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("AMOUNT", "Amount");
	define("NAME_ON_CARD", "Name on Card");
	define("STUDENT", "Student");
	define("PAYMENT_DATE", "Payment Date");
	define("LAST_4_CC", "Last 4 of CC");
	define("MESSAGE", "Message");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("AMOUNT", "Amount");
	define("NAME_ON_CARD", "Name on Card");
	define("STUDENT", "Student");
	define("PAYMENT_DATE", "Payment Date");
	define("LAST_4_CC", "Last 4 of CC");
	define("MESSAGE", "Message");
}