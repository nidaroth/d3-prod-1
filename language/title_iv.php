<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
}