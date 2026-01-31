<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
    define("ACCET_DOC_SETUP", "Accrediting Council for Continuing Education & Training (ACCET) Setup");	
	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Status(es)");
	define("STUDENT_STATUS", "Student Status");
	define("DROP_REASONS", "Drop Reason");
	define("PLACEMENT_STATUS", "Placement Status");
	define("REPORT_OPTION", "Report Option");
	define("EXCLUSIONS", "Exclusions");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//spanish
    define("ACCET_DOC_SETUP", "Accrediting Council for Continuing Education & Training (ACCET) Setup");	
	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Status(es)");
	define("STUDENT_STATUS", "Student Status");
	define("DROP_REASONS", "Drop Reason");
	define("PLACEMENT_STATUS", "Placement Status");
	define("REPORT_OPTION", "Report Option");
	define("EXCLUSIONS", "Exclusions");
	

}