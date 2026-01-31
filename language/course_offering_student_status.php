<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("COURSE_OFFERING_STUDENT_STATUS_PAGE_TITLE", "Course Offering Student Status");
	define("COURSE_OFFERING_STUDENT_STATUS", "Course Offering Student Status");
	define("DESCRIPTION", "Description");
	define("STATUS", "Status");
	
	define("POST_TUITION", "Post Tuition");
	define("SHOW_ON_TRANSCRIPT", "Show On Transcript");
	define("SHOW_ON_REPORT_CARD", "Show On Report Card");
	define("CALCULATE_SAP", "Calculate SAP");
	define("MAKE_AS_DEFAULT", "Default");
	define("IS_DEFAULT", "Is Default");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("COURSE_OFFERING_STUDENT_STATUS_PAGE_TITLE", "Course Offering Student Status");
	define("COURSE_OFFERING_STUDENT_STATUS", "Course Offering Student Status");
	define("DESCRIPTION", "Description");
	define("STATUS", "Status");
	define("POST_TUITION", "Post Tuition");
	define("SHOW_ON_TRANSCRIPT", "Show On Transcript");
	define("SHOW_ON_REPORT_CARD", "Show On Report Card");
	define("CALCULATE_SAP", "Calculate SAP");
	define("MAKE_AS_DEFAULT", "Default");
	define("IS_DEFAULT", "Is Default");
}