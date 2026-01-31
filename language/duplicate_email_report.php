<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("DUPLICATE_EMAIL_PAGE_TITLE", "Duplicate Email Report");
	define("STUDENT_ID", "Student ID");
	define("EXCLUDE_EMAIL", "Exclude Email");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("DUPLICATE_EMAIL_PAGE_TITLE", "Duplicate Email Report");
	define("STUDENT_ID", "Student ID");
	define("EXCLUDE_EMAIL", "Exclude Email");
}