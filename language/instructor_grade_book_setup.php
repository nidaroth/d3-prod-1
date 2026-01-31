<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("GRADE_BOOK_SETUP_PAGE_TITLE", "Grade Book Setup");
	define("SELECT_TERM", "Select Term");
	define("SELECT_COURSE_OFFERING", "Select Course Offering");

} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("GRADE_BOOK_SETUP_PAGE_TITLE", "Grade Book Setup");
	define("SELECT_TERM", "Select Term");
	define("SELECT_COURSE_OFFERING", "Select Course Offering");
}