<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("GRADE_BOOK_TYPE_PAGE_TITLE", "Grade Book Type");
	define("GRADE_BOOK_TYPE", "Grade Book Type");
	define("DESCRIPTION", "Description");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("GRADE_BOOK_TYPE_PAGE_TITLE", "Grade Book Type");
	define("GRADE_BOOK_TYPE", "Grade Book Type");
	define("DESCRIPTION", "Description");
}