<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("MNU_IPEDS_SPRING_COLLECTIONS_SETUP_TITLE", "IPEDS Spring Collection Setup");
	define("MNU_IPEDS_SPRING_COLLECTIONS_FALL_ENRO", "IPEDS Spring Collection - Fall Enrollment");
	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Statuses");
	define("EXCLUDED_DROP_REASON", "Excluded Drop Reasons");
	define("FOUR_YEAR_PROGRAMS", "4-Year Programs");
	define("TWO_YEAR_PROGRAMS", "2-Year Programs");
	define("REQUIREMENTS", "Requirements");
	define("SELECT_SETUP_CODES", "Select Setup Codes");
	define("REQUIRED_FIELDS", "Required Fields");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("MNU_IPEDS_SPRING_COLLECTIONS_SETUP_TITLE", "IPEDS Spring Collection Setup");
	define("MNU_IPEDS_SPRING_COLLECTIONS_FALL_ENRO", "IPEDS Spring Collection - Fall Enrollment");
	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Statuses");
	define("EXCLUDED_DROP_REASON", "Excluded Drop Reasons");
	define("FOUR_YEAR_PROGRAMS", "4-Year Programs");
	define("TWO_YEAR_PROGRAMS", "2-Year Programs");
	define("REQUIREMENTS", "Requirements");
	define("SELECT_SETUP_CODES", "Select Setup Codes");
	define("REQUIRED_FIELDS", "Required Fields");
}