<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("AMOUNT", "Amount");
	define("FEE", "Fee");
	define("TOTAL_CHARGE", "Total Charge");
	define("TRANSACTION_ID", "Transaction ID");
	define("STUDENT", "Student");
	define("DATE_PAID", "Date Paid");
	define("LAST_4_CC", "Last 4 of CC");
	define("TOTAL_COUNT", "Total Count");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("AMOUNT", "Amount");
	define("FEE", "Fee");
	define("TOTAL_CHARGE", "Total Charge");
	define("TRANSACTION_ID", "Transaction ID");
	define("STUDENT", "Student");
	define("STUDENT", "Student");
	define("DATE_PAID", "Date Paid");
	define("LAST_4_CC", "Last 4 of CC");
	define("TOTAL_COUNT", "Total Count");
}