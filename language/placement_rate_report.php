<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("EXCLUDED_PROGRAMS", "Excluded Programs");
	define("EXCLUDED_PLACEMENT_STUDENT_STATUS", "Excluded Placement Student Status(es)");
	define("PLACED_PLACEMENT_STUDENT_STATUS", "Placed Placement Student Status(es)");
	define("PROGRAM", "Program");
	define("PLACEMENT_STUDENT_STATUS", "Placement Student Status");
	define("CATEGORY", "Category");
	define("EMPLOYED", "Employed");
	define("REPORT_SETUP", "Report Setup");
	define("GO_TO_REPORT", "Go To Report");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("EXCLUDED_PROGRAMS", "Excluded Programs");
	define("EXCLUDED_PLACEMENT_STUDENT_STATUS", "Excluded Placement Student Status(es)");
	define("PLACED_PLACEMENT_STUDENT_STATUS", "Placed Placement Student Status(es)");
	define("PROGRAM", "Program");
	define("PLACEMENT_STUDENT_STATUS", "Placement Student Status");
	define("CATEGORY", "Category");
	define("EMPLOYED", "Employed");
	define("REPORT_SETUP", "Report Setup");
	define("GO_TO_REPORT", "Go To Report");
}