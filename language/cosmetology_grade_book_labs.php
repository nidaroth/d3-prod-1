<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("LAB_DESCRIPTION", "Lab Description");
	define("REQUIRED_SESSIONS", "Required Sessions");
	define("COMPLETED_SESSIONS", "Completed Sessions");
	define("REMAINING_SESSIONS", "Remaining Sessions");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("LAB_DESCRIPTION", "Lab Description");
	define("REQUIRED_SESSIONS", "Required Sessions");
	define("COMPLETED_SESSIONS", "Completed Sessions");
	define("REMAINING_SESSIONS", "Remaining Sessions");
}