<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("STUDENT_GROUP_PAGE_TITLE", "Student Group");
	define("STUDENT_GROUP", "Student Group");
	define("PROGRAM", "Program");
	define("COMMENTS", "Comments");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("STUDENT_GROUP_PAGE_TITLE", "Student Group");
	define("STUDENT_GROUP", "Student Group");
	define("PROGRAM", "Program");
	define("COMMENTS", "Comments");
}