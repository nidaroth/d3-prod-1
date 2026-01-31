<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("REPORTING_YEAR", "Reporting Year");
	define("AWARD_YEAR", "Award Year");
	define("REPORT_SETUP", "Report Setup");
	define("GO_TO_REPORT", "Go To Report");
	
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Status(es)");
	define("EXCLUDED_PROGRAM", "Excluded Program(s)");
	define("STUDENT_STATUS_GRADUATES", "Student Status(es) - Graduates");
	define("STUDENT_STATUS_DROPS", "Student Status(es) - Drops");
	define("STUDENT_STATUS_OTHER_WITHDRAWLS", "Student Status(es) - Other Withdrawals");
	define("DROP_REASON_MILITARY", "Drop Reason - Military");
	define("DROP_REASON_INCARCERATED", "Drop Reason - Incarcerated");
	define("DROP_REASON_DECEASED", "Drop Reason - Deceased");
	define("PLACEMENT_STUDENT_STATUS_INCARCERATED", "Placement Student Status - Incarcerated");
	define("PLACEMENT_STUDENT_STATUS_DECEASED", "Placement Student Status - Deceased");
	define("PLACEMENT_STUDENT_STATUS_POSTSECONDARY_EDUCATION", "Placement Student Status - Postsecondary Education");
	define("PLACEMENT_STUDENT_STATUS_PLACED", "Placement Student Status - Placed");
	define("PLACEMENT_STUDENT_STATUS_OTHER", "Placement Student Status - Other");
	define("PLACEMENT_STUDENT_STATUS_NOT_PLACED", "Placement Student Status - Not Placed");
	define("PLACEMENT_STUDENT_STATUS_MILITARY", "Placement Student Status - Military");
	define("STUDENT_STATUS_OTHER_COMPLETERS", "Student Status(es) - Other Completers");
	define("STUDENT_STATUS_UNKNOWN", "Student Status(es) - Unknown");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
}