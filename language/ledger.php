<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("LEDGER_PAGE_TITLE", "Student Ledger");
	define("TRANSACTION_DATE", "Transaction Date");
	define("TRANSACTION", "Transaction");
	define("DEBIT", "Debit");
	define("CREDIT", "Credit");
	define("BALANCE", "Balance");
	define("TOTALS", "Totals");
	define("LEDGER_CODE", "Ledger Code");
	define("RECEIPT_CHECK_NO", "Receipt/Check #");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("LEDGER_PAGE_TITLE", "Student Ledger");
	define("TRANSACTION_DATE", "Transaction Date");
	define("TRANSACTION", "Transaction");
	define("DEBIT", "Debit");
	define("CREDIT", "Credit");
	define("BALANCE", "Balance");
	define("TOTALS", "Totals");
	define("LEDGER_CODE", "Ledger Code");
	define("RECEIPT_CHECK_NO", "Receipt/Check #");
}