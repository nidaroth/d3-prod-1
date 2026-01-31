<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("TERM_BLOCK_PAGE_TITLE", "Term Block");
	define("BEGIN_DATE", "Begin Date");
	define("END_DATE", "End Date");
	define("DESCRIPTION", "Description");
	define("EARNINGS_DAYS", "Earnings Days");
	define("JAN", "Jan");
	define("FEB", "Feb");
	define("MAR", "Mar");
	define("APR", "Apr");
	define("MAY", "May");
	define("JUN", "Jun");
	define("JUL", "Jul");
	define("AUG", "Aug");
	define("SEP", "Sep");
	define("OCT", "Oct");
	define("NOV", "Nov");
	define("DEC", "Dec");
	define("DONOT_INCLUDE_WEEKEND", "Donot include Weekends");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("TERM_BLOCK_PAGE_TITLE", "Term Block");
	define("BEGIN_DATE", "Begin Date");
	define("END_DATE", "End Date");
	define("DESCRIPTION", "Description");
	define("EARNINGS_DAYS", "Earnings Days");
	define("JAN", "Jan");
	define("FEB", "Feb");
	define("MAR", "Mar");
	define("APR", "Apr");
	define("MAY", "May");
	define("JUN", "Jun");
	define("JUL", "Jul");
	define("AUG", "Aug");
	define("SEP", "Sep");
	define("OCT", "Oct");
	define("NOV", "Nov");
	define("DEC", "Dec");
	define("DONOT_INCLUDE_WEEKEND", "Donot include Weekends");
}