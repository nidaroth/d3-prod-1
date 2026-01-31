<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("DUPLICATE_PHONE_PAGE_TITLE", "Duplicate Phone Report");
	define("STUDENT_ID", "Student ID");
	define("EXCLUDE_PHONE", "Exclude Phone");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("DUPLICATE_PHONE_PAGE_TITLE", "Duplicate Phone Report");
	define("STUDENT_ID", "Student ID");
	define("EXCLUDE_PHONE", "Exclude Phone");
}