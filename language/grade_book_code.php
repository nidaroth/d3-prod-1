<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("GRADE_BOOK_CODE_PAGE_TITLE", "Program Grade Book Code");
	define("GRADE_BOOK_CODE", "Code");
	define("HOUR", "Hours");
	define("SESSION", "Sessions");
	define("POINTS", "Points");
	define("DESCRIPTION", "Description");
	define("GRADE_BOOK_TYPE", "Grade Book Type");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("GRADE_BOOK_CODE_PAGE_TITLE", "Program Grade Book Code");
	define("GRADE_BOOK_CODE", "Code");
	define("HOUR", "Hours");
	define("SESSION", "Sessions");
	define("POINTS", "Points");
	define("DESCRIPTION", "Description");
	define("GRADE_BOOK_TYPE", "Grade Book Type");
}