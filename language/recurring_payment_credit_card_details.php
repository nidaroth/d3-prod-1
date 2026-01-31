<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("STUDENT_STATUS", "Student Status");
	define("NAME_ON_CARD", "Name on Card");
	define("CARD_TYPE", "Card Type");
	define("LAST_4_CC", "Last 4 of CC");
	define("CARD_EXP_DATE", "Card Exp Date");
	define("IS_PRIMARY", "Is Primary");
	
	define("EXP_START_DATE", "Exp Start Date");
	define("EXP_END_DATE", "Exp End Date");
	define("STUDENT_ID", "Student ID");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("STUDENT_STATUS", "Student Status");
	define("NAME_ON_CARD", "Name on Card");
	define("CARD_TYPE", "Card Type");
	define("LAST_4_CC", "Last 4 of CC");
	define("CARD_EXP_DATE", "Card Exp Date");
	define("IS_PRIMARY", "Is Primary");
	define("STUDENT_ID", "Student ID");
}