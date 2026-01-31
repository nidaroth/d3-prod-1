<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("REPORT_OPTIONS", "Report Options");
	define("RUN", "Run");
	define("STUDENT_STATUS", "Student Status");
	define("INCLUDE_ALL_LEADS", "Include All Leads");
	define("ALL_STUDENT_STATUS", "All Student Statuses");
	define("ALL_FIRST_TERM", "All First Terms");
	define("ALL_PROGRAM", "All Programs");
	define("FIRST_TERM", "First Term");
	define("STUDENT_GROUP", "Student Group");
	define("SELECTED_COUNT", "Selected Count");
	define("ENROLLMENT_OPTIONS", "Enrollment Options");
	define("REPORT_TYPE", "Report Type");
	define("GROUP_CODE", "Student Group");
	define("BALANCE_AS_OF_DATE", "Balance as of Date");
	define("INCLUDE_PROJECTED_DISBURSEMENTS", "Include Projected Disbursements");
	define("LEDGER_TYPE", "Ledger Type");
	define("FUNDING", "Funding");
	define("DETAIL_OPTION", "Detail Option");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("REPORT_OPTIONS", "Report Options");
	define("RUN", "Run");
	define("STUDENT_STATUS", "Student Status");
	define("INCLUDE_ALL_LEADS", "Include All Leads");
	define("ALL_STUDENT_STATUS", "All Student Statuses");
	define("ALL_FIRST_TERM", "All First Terms");
	define("ALL_PROGRAM", "All Programs");
	define("FIRST_TERM", "First Term");
	define("STUDENT_GROUP", "Student Group");
	define("SELECTED_COUNT", "Selected Count");
	define("ENROLLMENT_OPTIONS", "Enrollment Options");
	define("REPORT_TYPE", "Report Type");
	define("GROUP_CODE", "Student Group");
	define("BALANCE_AS_OF_DATE", "Balance as of Date");
	define("INCLUDE_PROJECTED_DISBURSEMENTS", "Include Projected Disbursements");
	define("LEDGER_TYPE", "Ledger Type");
	define("FUNDING", "Funding");
	define("DETAIL_OPTION", "Detail Option");
}
