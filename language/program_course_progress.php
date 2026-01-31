<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("COURSE", "Course");
	define("DESCRIPTION", "Description");
	define("UNITS", "Units");
	define("COMPLETED", "Completed");
	define("IN_PROGRESS", "In Progress");
	define("REQUIRED", "Required");
	define("COMPLETED_TRANSFERED", "Completed - Transferred");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("COURSE", "Course");
	define("DESCRIPTION", "Description");
	define("UNITS", "Units");
	define("COMPLETED", "Completed");
	define("IN_PROGRESS", "In Progress");
	define("REQUIRED", "Required");
	define("COMPLETED_TRANSFERED", "Completed - Transferred");
}