<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("STUDENT_STATUS_PAGE_TITLE", "Student Status");
	define("STUDENT_STATUS", "Student Status");
	define("DEPARTMENT", "Department");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("STUDENT_STATUS_PAGE_TITLE", "Student Status");
	define("STUDENT_STATUS", "Student Status");
	define("DEPARTMENT", "Department");
}