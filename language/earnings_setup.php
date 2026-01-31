<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("EXCLUDED_PROGRAMS", "Excluded Programs");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Status");
	define("INCLUDED_FEE_LEDGER_CODES", "Included Fee Ledger Codes");
	define("IGNORE_FUTURE_TUITION", "Ignore Future Tuition");
	define("PRORATE_FIRST_MONTH", "Prorate First Month");
	define("PRORATE_LOA_STATUS", "Prorate LOA Status");
	define("PRORATE_BREAKS", "Prorate Breaks");
	define("PRORATE_CLOSURES", "Prorate Closures");
	define("PRORATE_HOLIDAYS", "Prorate Holidays");
	
	define("EXCLUDED_PROGRAMS_1", "Excluded<br />Programs");
	define("EXCLUDED_STUDENT_STATUS_1", "Excluded<br />Student Status");
	define("INCLUDED_FEE_LEDGER_CODES_1", "Included Fee<br />Ledger Codes");
	define("IGNORE_FUTURE_TUITION_1", "Ignore<br />Future Tuition");
	define("PRORATE_FIRST_MONTH_1", "Prorate<br />First Month");
	define("PRORATE_LOA_STATUS_1", "Prorate<br />LOA Status");
	define("PRORATE_BREAKS_1", "Prorate<br />Breaks");
	define("PRORATE_CLOSURES_1", "Prorate<br />Closures");
	define("PRORATE_HOLIDAYS_1", "Prorate<br />Holidays");
	
	define("YEAR_MONTH", "Year/Month");
	define("RECORD_COUNT", "Record Count");
	define("ON", "On");
	define("BY", "By");
	define("CREATED_ON", "Created On");
	define("CREATED_BY", "Created By");
	define("YEAR", "Year");
	define("MONTH", "Month");
	define("FIRST_TERM", "First Term");
	define("RETURN_TO_CALCULATION", "Return To Calculation");
	define("EARNINGS_TYPE", "Earning Type");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("EXCLUDED_PROGRAMS", "Excluded Programs");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Status");
	define("INCLUDED_FEE_LEDGER_CODES", "Included Fee Ledger Codes");
	define("IGNORE_FUTURE_TUITION", "Ignore Future Tuition");
	define("PRORATE_FIRST_MONTH", "Prorate First Month");
	define("PRORATE_LOA_STATUS", "Prorate LOA Status");
	define("PRORATE_BREAKS", "Prorate Breaks");
	define("PRORATE_CLOSURES", "Prorate Closures");
	define("PRORATE_HOLIDAYS", "Prorate Holidays");
	
	define("EXCLUDED_PROGRAMS_1", "Excluded<br />Programs");
	define("EXCLUDED_STUDENT_STATUS_1", "Excluded<br />Student Status");
	define("INCLUDED_FEE_LEDGER_CODES_1", "Included Fee<br />Ledger Codes");
	define("IGNORE_FUTURE_TUITION_1", "Ignore<br />Future Tuition");
	define("PRORATE_FIRST_MONTH_1", "Prorate<br />First Month");
	define("PRORATE_LOA_STATUS_1", "Prorate<br />LOA Status");
	define("PRORATE_BREAKS_1", "Prorate<br />Breaks");
	define("PRORATE_CLOSURES_1", "Prorate<br />Closures");
	define("PRORATE_HOLIDAYS_1", "Prorate<br />Holidays");
	
	define("YEAR_MONTH", "Year/Month");
	define("RECORD_COUNT", "Record Count");
	define("ON", "On");
	define("BY", "By");
	define("CREATED_ON", "Created On");
	define("CREATED_BY", "Created By");
	define("YEAR", "Year");
	define("MONTH", "Month");
	define("FIRST_TERM", "First Term");
	define("RETURN_TO_CALCULATION", "Return To Calculation");
	define("EARNINGS_TYPE", "Earning Type");
}