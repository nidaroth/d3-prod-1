<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("QUICK_PAYMENT_LEDGER_CODE", "Quick Payment Ledger Code");
	define("STUDENT_STATUS", "Student Status");
	define("LEDGER_CODE", "Ledger Code");
	define("DISBURSEMENT_DATE", "Disbursement Date");
	define("DISBURSEMENT_AMOUNT", "Disbursement Amount");
	define("TOTAL_COUNT", "Total Count");
	define("STUDENT_ID", "Student ID");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("QUICK_PAYMENT_LEDGER_CODE", "Quick Payment Ledger Code");
	define("STUDENT_STATUS", "Student Status");
	define("LEDGER_CODE", "Ledger Code");
	define("DISBURSEMENT_DATE", "Disbursement Date");
	define("DISBURSEMENT_AMOUNT", "Disbursement Amount");
	define("TOTAL_COUNT", "Total Count");
	define("STUDENT_ID", "Student ID");
}