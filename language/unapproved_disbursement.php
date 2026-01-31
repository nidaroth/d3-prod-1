<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("DATE_TYPE", "Date Type");
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("AWARD_LEDGER_CODES", "Award Ledger Codes");
	define("STUDENT_STATUS", "Student Status");
	define("INCLUDE_ALL_LEADS", "Include All Leads");
	define("STUDENT_STATUS", "Student Status");
	define("RUN", "Run");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("DATE_TYPE", "Date Type");
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("AWARD_LEDGER_CODES", "Award Ledger Codes");
	define("STUDENT_STATUS", "Student Status");
	define("INCLUDE_ALL_LEADS", "Include All Leads");
	define("STUDENT_STATUS", "Student Status");
	define("RUN", "Run");
}