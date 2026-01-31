<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("REPORT_OPTIONS", "Report Options");
	define("LEDGER_CODE", "Ledger Code");
	define("RUN", "Run");
	define("DETAIL_OPTION", "Detail Option");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("REPORT_OPTIONS", "Report Options");
	define("RUN", "Run");
	define("LEDGER_CODE", "Ledger Code");
	define("DETAIL_OPTION", "Detail Option");
}