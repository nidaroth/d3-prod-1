<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("TRANSCRIPT_GROUP_PAGE_TITLE", "Transcript Group");
	define("TRANSCRIPT_GROUP", "Transcript Group");
	define("DESCRIPTION", "Description");
	define("WEIGHTED", "Weighted");
	define("TRANSCRIPT_DETAIL_SORT_ORDER_TYPE", "Transcript Detail Sort Order");
	define("TRANSCRIPT_DETAIL_SORT_ORDER_TYPE_1", "Transcript Detail<br />Sort Order");
	define("ORDER", "Order");
	define("COURSE_CODE", "Course Code");
	define("TERM", "Term");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("TRANSCRIPT_GROUP_PAGE_TITLE", "Transcript Group");
	define("TRANSCRIPT_GROUP", "Transcript Group");
	define("DESCRIPTION", "Description");
	define("WEIGHTED", "Weighted");
	define("TRANSCRIPT_DETAIL_SORT_ORDER_TYPE", "Transcript Detail Sort Order");
	define("TRANSCRIPT_DETAIL_SORT_ORDER_TYPE_1", "Transcript Detail<br />Sort Order");
	define("ORDER", "Order");
	define("COURSE_CODE", "Course Code");
	define("TERM", "Term");
}