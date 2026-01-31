<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("STUDENT_CONTACT_PAGE_TITLE", "Contact");
	define("PHONE_NUMBERS", "Phone Numbers");
	define("STUDENT_CONTACT_TYPE", "Contact Type");
	define("STUDENT_RELATIONSHIP", "Relationship");
	define("OTHER_PHONE", "Other Phone");
	define("EMERGENCY_PHONE", "Emergency Phone");
	define("EMERGENCY_CONTACT", "Emergency Contact");
	define("STATE", "State");
	define("CONTACT_NAME", "Contact Name");
	define("COMPANY_NAME", "Company Name");
	define("INVALID", "Invalid");
	define("OTHER_EMAIL", "Other Email");
	define("CONTACT_TITLE", "Contact Title");
	define("MAIN", "Main");
	define("CONTACT_DESCRIPTION", "Contact Description");
	define("USE_SECONDARY_EMAIL_AS_DEFAULT_STD", "Set As Default", true);

} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("STUDENT_CONTACT_PAGE_TITLE", "Contact");
	define("PHONE_NUMBERS", "Phone Numbers");
	define("STUDENT_CONTACT_TYPE", "Contact Type");
	define("STUDENT_RELATIONSHIP", "Relationship");
	define("OTHER_PHONE", "Other Phone");
	define("EMERGENCY_PHONE", "Emergency Phone");
	define("EMERGENCY_CONTACT", "Emergency Contact");
	define("STATE", "State");
	define("CONTACT_NAME", "Contact Name");
	define("COMPANY_NAME", "Company Name");
	define("INVALID", "Invalid");
	define("OTHER_EMAIL", "Other Email");
	define("CONTACT_TITLE", "Contact Title");
	define("MAIN", "Main");
	define("CONTACT_DESCRIPTION", "Contact Description");
	define("USE_SECONDARY_EMAIL_AS_DEFAULT_STD", "Set As Default", true);

}