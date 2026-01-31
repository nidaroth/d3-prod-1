<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("POPULATION_REPORT_SETUP_TITLE", "Population Report Setup");
	define("GRADUATES", "Graduates");
	define("OTHER_COMPLETERS", "Other Completers");
	define("DROPS", "Drops");
	define("OTHER_WITHDRAWS", "Withdraws");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Status");
	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("REPORT_OPTION", "Report Option");
	define("REPORT_SETUP", "Report Setup");
	define("GO_TO_REPORT", "Go To Report");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("POPULATION_REPORT_SETUP_TITLE", "Population Report Setup");
	define("GRADUATES", "Graduates");
	define("OTHER_COMPLETERS", "Other Completers");
	define("DROPS", "Drops");
	define("OTHER_WITHDRAWS", "Withdraws");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Status");
	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("REPORT_OPTION", "Report Option");
	define("REPORT_SETUP", "Report Setup");
	define("GO_TO_REPORT", "Go To Report");
}