<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("DEPARTMENT_PAGE_TITLE", "Departments");
	define("DEPARTMENT", "Department");
	define("DEPARTMENT_NAME", "Department Name");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("DEPARTMENT_PAGE_TITLE", "Departments");
	define("DEPARTMENT", "Department");
	define("DEPARTMENT_NAME", "Department Name");
}