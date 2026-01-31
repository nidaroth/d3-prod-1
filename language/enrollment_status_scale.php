<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ENROLLMENT_STATUS_SCALE_PAGE_TITLE", "Enrollment Status Scale");
	define("ENROLLMENT_STATUS_SCALE", "Enrollment Status Scale");
	define("ENROLLMENT_STATUS", "Enrollment Status");
	define("CODE", "Code");
	define("DESCRIPTION", "Description");
	define("MIN_UNITS_PER_TERM", "Minimum FA Units/Hours/Units Per Term");
	
	define("FA_UNITS", "FA Units");
	define("HOUR", "Hour");
	define("UNITS", "Units");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ENROLLMENT_STATUS_SCALE_PAGE_TITLE", "Enrollment Status Scale");
	define("ENROLLMENT_STATUS_SCALE", "Enrollment Status Scale");
	define("ENROLLMENT_STATUS", "Enrollment Status");
	define("CODE", "Code");
	define("DESCRIPTION", "Description");
	define("MIN_UNITS_PER_TERM", "Minimum FA Units/Hours/Units Per Term");
	define("FA_UNITS", "FA Units");
	define("HOUR", "Hour");
	define("UNITS", "Units");
}