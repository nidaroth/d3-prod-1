<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("STUDENT_CONTACT_TYPE_PAGE_TITLE", "Student Contact Types");
	define("STUDENT_CONTACT_TYPE", "Funding");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("STUDENT_CONTACT_TYPE_PAGE_TITLE", "Student Contact Types");
	define("STUDENT_CONTACT_TYPE", "Student Contact Type");
}