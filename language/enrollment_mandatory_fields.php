<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ENROLLMENT_MANDATORY_FIELDS_PAGE_TITLE", "Enrollment Mandatory Fields");
	define("INFO_FIELD_NAME", "Info Field Name");
	define("ENROLLMENT_FIELD_NAME", "Enrollment Field Name");
	define("CONTACT_TYPE", "Contact Types");
	define("MANDATORY", "Mandatory");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ENROLLMENT_MANDATORY_FIELDS_PAGE_TITLE", "Enrollment Mandatory Fields");
	define("INFO_FIELD_NAME", "Info Field Name");
	define("ENROLLMENT_FIELD_NAME", "Enrollment Field Name");
	define("CONTACT_TYPE", "Contact Types");
	define("MANDATORY", "Mandatory");
}