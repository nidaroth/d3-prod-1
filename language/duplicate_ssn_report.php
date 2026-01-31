<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("DUPLICATE_SSN_PAGE_TITLE", "Duplicate SSN Report");
	define("STUDENT_ID", "Student ID");
	define("EXCLUDE_SSN", "Exclude SSN");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("DUPLICATE_SSN_PAGE_TITLE", "Duplicate SSN Report");
	define("STUDENT_ID", "Student ID");
	define("EXCLUDE_SSN", "Exclude SSN");
}