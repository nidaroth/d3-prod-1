<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PROFILE_PAGE_TITLE", "Profile");
	define("IMAGE", "Profile Image");
	define("IMAGE_DELETE", "Are you sure want to Delete this Profile Image?");
	define("LANGUAGE", "Language");
	define("PREFERRED_LANGUAGE", "Preferred Language");
	define("ENROLLMENT_INFO", "Enrollment Information");
	define("CONTACT_INFO", "Contact Information");
	define("DIGITAL_STUDENT_ID", "Digital Student ID");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("PROFILE_PAGE_TITLE", "Profile");
	define("IMAGE", "Profile Image");
	define("IMAGE_DELETE", "Are you sure want to Delete this Profile Image?");
	define("LANGUAGE", "Language");
	define("PREFERRED_LANGUAGE", "Preferred Language");
	define("ENROLLMENT_INFO", "Enrollment Information");
	define("CONTACT_INFO", "Contact Information");
	define("DIGITAL_STUDENT_ID", "Digital Student ID");
}