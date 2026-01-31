<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ACCOUNTING_LEDGER_EXPORT_TITLE", "Ledger Export");
	define("DATE_TYPE", "Date Type");
	define("DATE_RANGE", "Date Range");
	define("EXPORT_TYPE", "Export Type");
	define("CAMPUS", "Campus");
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("REVIEW", "Review");
	define("EXPORT", "EXPORT");
	define("PREVIOUSLY_EXPORTED_TRANSACTION", "Previously Exported Transaction");
	define("PREVIOUSLY_EXPORTED_TRANSACTION_MSG", "Some or all transactions in this date range have already been exported.<br />Select 'Export All' to export all transactions for the selected date range.<br />Select 'Export New' to export only transactions that have not been previously exported for the selected date range.");
	
	define("PREVIOUSLY_REVIEW_TRANSACTION_MSG", "Some or all transactions in this date range have already been exported.<br />Select 'Review All' to view all transactions for the selected date range.<br />Select 'Review New' to view only transactions that have not been previously exported for the selected date range.");
	
	define("EXPORT_ALL", "Export All");
	define("EXPORT_NEW", "Export New");
	define("REVIEW_ALL", "Review All");
	define("REVIEW_NEW", "Review New");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ACCOUNTING_LEDGER_EXPORT_TITLE", "Ledger Export");
	define("DATE_TYPE", "Date Type");
	define("DATE_RANGE", "Date Range");
	define("EXPORT_TYPE", "Export Type");
	define("CAMPUS", "Campus");
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("REVIEW", "Review");
	define("EXPORT", "EXPORT");
	define("PREVIOUSLY_EXPORTED_TRANSACTION", "Previously Exported Transaction");
	define("PREVIOUSLY_EXPORTED_TRANSACTION_MSG", "Some or all transactions in this date range have already been exported.<br />Select 'Export All' to export all transactions for the selected date range.<br />Select 'Export New' to export only transactions that have not been previously exported for the selected date range.");
	
	define("PREVIOUSLY_REVIEW_TRANSACTION_MSG", "Some or all transactions in this date range have already been exported.<br />Select 'Review All' to view all transactions for the selected date range.<br />Select 'Review New' to view only transactions that have not been previously exported for the selected date range.");
	
	define("EXPORT_ALL", "Export All");
	define("EXPORT_NEW", "Export New");
	define("REVIEW_ALL", "Review All");
	define("REVIEW_NEW", "Review New");
} ?>