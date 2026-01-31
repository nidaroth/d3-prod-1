<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("REPORTING_YEAR", "Reporting Year");
	define("AWARD_YEAR", "Award Year");
	define("REPORT_SETUP", "Report Setup");
	
	define("EXCLUDED_PROGRAM", "Excluded Program");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Status");
	define("INCLUDED_LEDGER_CODE", "Included Ledger Code");
	define("GO_TO_REPORT", "Go To Report");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("REPORTING_YEAR", "Reporting Year");
	define("AWARD_YEAR", "Award Year");
	define("REPORT_SETUP", "Report Setup");
	
	define("EXCLUDED_PROGRAM", "Excluded Program");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Status");
	define("INCLUDED_LEDGER_CODE", "Included Ledger Code");
	define("GO_TO_REPORT", "Go To Report");
}