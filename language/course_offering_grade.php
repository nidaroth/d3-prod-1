<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("GRADE_BOOK_COURSE_OFFERING_PAGE_TITLE", "Grade Book Course Offering Setup");
	define("CODE", "Code");
	define("DESCRIPTION", "Description");
	define("DATE", "Date");
	define("PERIOD", "Period");
	define("POINTS", "Points");
	define("WEIGHT", "Weight");
	define("WEIGHTED_POINTS", "Weighted Points");
	define("SORT_ORDER", "Sort Order");
	define("COURSE_OFFERING", "Course Offering");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("GRADE_BOOK_COURSE_OFFERING_PAGE_TITLE", "Grade Book Course Offering Setup");
	define("CODE", "Code");
	define("DESCRIPTION", "Description");
	define("DATE", "Date");
	define("PERIOD", "Period");
	define("POINTS", "Points");
	define("WEIGHT", "Weight");
	define("WEIGHTED_POINTS", "Weighted Points");
	define("SORT_ORDER", "Sort Order");
	define("COURSE_OFFERING", "Course Offering");
}