<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("LOA_PAGE_TITLE", "LOA");
	define("BEGIN_DATE", "Begin Date");
	define("END_DATE", "End Date");
	define("NO_DAYS", "# of days ");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("LOA_PAGE_TITLE", "LOA");
	define("BEGIN_DATE", "Begin Date");
	define("END_DATE", "End Date");
	define("NO_DAYS", "# of days ");
}