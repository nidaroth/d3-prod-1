<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("DEFAULT_COHORT_YEAR", "Default Cohort Year ");
	define("BEGIN_DATE", "Begin Date");
	define("END_DATE", "End Date");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("DEFAULT_COHORT_YEAR", "Default Cohort Year ");
	define("BEGIN_DATE", "Begin Date");
	define("END_DATE", "End Date");
}