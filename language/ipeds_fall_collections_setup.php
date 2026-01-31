<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("MNU_IPEDS_FALL_COLLECTIONS_SETUP_TITLE", "IPEDS Fall Collection Setup");
	define("REQUIRED_FIELDS", "Required Fields");
	define("SELECT_SETUP_CODES", "Select Setup Codes");
	define("PROGRAM_AWARD_LEVEL", "Program Award Level");
	define("PROGRAM_REVIEW", "Program Review");
	define("STUDENT_DATA_REVIEW", "Student Data Review");
	define("REQUIREMENTS", "Requirements");
	define("COMPLETION_STUDENT_STATUSES", "Completion Student Statuses");
	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Statuses");
	define("SETUPS", "Setups");
	define("PROGRAM_CODE", "Program Code");
	define("AWARD_LEVEL", "Award Level");
	define("CREDENTIAL_LEVEL", "Credential Level");
	define("REPORT_TYPE", "Report Type");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("MNU_IPEDS_FALL_COLLECTIONS_SETUP_TITLE", "IPEDS Fall Collection Setup");
	define("REQUIRED_FIELDS", "Required Fields");
	define("SELECT_SETUP_CODES", "Select Setup Codes");
	define("PROGRAM_AWARD_LEVEL", "Program Award Level");
	define("PROGRAM_REVIEW", "Program Review");
	define("STUDENT_DATA_REVIEW", "Student Data Review");
	define("REQUIREMENTS", "Requirements");
	define("COMPLETION_STUDENT_STATUSES", "Completion Student Statuses");
	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Statuses");
	define("SETUPS", "Setups");
	define("PROGRAM_CODE", "Program Code");
	define("AWARD_LEVEL", "Award Level");
	define("CREDENTIAL_LEVEL", "Credential Level");
	define("REPORT_TYPE", "Report Type");
}