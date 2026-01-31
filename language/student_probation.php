<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PROBATION_PAGE_TITLE", "Probation");
	define("BEGIN_DATE", "Begin Date");
	define("END_DATE", "End Date");
	define("PROBATION_TYPE", "Probation Type");
	define("PROBATION_LEVEL", "Probation Level");
	define("PROBATION_STATUS", "Probation Status");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("PROBATION_PAGE_TITLE", "Probation");
	define("BEGIN_DATE", "Begin Date");
	define("END_DATE", "End Date");
	define("PROBATION_TYPE", "Probation Type");
	define("PROBATION_LEVEL", "Probation Level");
	define("PROBATION_STATUS", "Probation Status");
}