<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
    define("ACICS_DOC_SETUP", "ACICS Campus Accountability Report (CAR) Setup");
	
	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Status(es)");
	define("STUDENT_STATUS", "Student Status");
	define("COMPLETED_A_PROGRAM", "Completed a Program");
	define("GRADUATED_FROM_A_PROGRAM", "Graduated From a Program");
	define("WITHDRAWAL", "Withdrawal");

	define("DROP_REASONS", "Drop Reason");
	define("WITHDRAWAL_ACTIVE_MILITARY_SERVICE", "Withdrawal Active Military Service");
	define("WITHDRAWAL_DEATH", "Withdrawal Death");
	define("WITHDRAWAL_ENROLLED_IN_INSTITUTION_WITH_COMMON_OWNERSHIP", "Withdrawal Enrolled in Institution with Common Ownership ");
	define("WITHDRAWAL_INCARCERATION", "Withdrawal Incarceration");

	define("PLACEMENT_STATUS", "Placement Status");
	define("NOT_AVAILABLE_ACTIVE_DUTY_MILATARY_SERVICE", "Not Available Active Duty Military Service");
	define("NOT_AVAILABLE_CONTINUING_EDUCATION", "Not Available Continuing Education");
	define("NOT_AVAILABLE_ENROLLMENT_IN_AN_ESL_PROGRAM", "Not Available Enrollment in an ESL Program");
	define("NOT_AVAILABLE_INCARCERATION", "Not Available Incarceration");
	define("NOT_AVAILABLE_PREGNANCY_DEATH_OR_HEALTH_RELATED_ISSUES", "Not Available Pregnancy, Death or Health Related Issues");
	define("NOT_AVAILABLE_VISA_RESTRICATIONS", "Not Available Visa Restrictions (International Students)");
	define("NOT_PLACED", "Not Placed");
	define("PLACED_BENEFIT_OF_TRAINING", "Placed Benefit of the Training");
	define("PLACED_JOB_TITLES", "Placed Job Titles");
	define("PLACED_SKILLS", "Placed Skills");

	define("NON_CREDIT_SHORT_TERM_MODULES", "Non Credit Short Term Modules");
	define("NON_CREDIT_SHORT_TERM_MODULE_PROGRAMS", "Non Credit Short Term Module Programs");
	define("NON_CREDIT_SHORT_TERM_MODULE_STATUS_COMPLETED", "Non Credit Short Term Module Status Completed");

	define("REPORT_OPTION", "Report Option");
	define("EXCLUSIONS", "Exclusions");

	

		
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	

}