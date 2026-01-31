<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("TEMPLATE_NAME", "Template Name");
	define("COURSE_CODE", "Course");
	define("COURSE_OFFERING_PAGE_TITLE", "Course Offering");
	define("GROUP_CODE", "Student Group Code");
	define("FIRST_TERM", "First Term");
	define("PROGRAM", "Program");
	define("STATUS", "Status");
	define("GENERATE", "Generate");
	define("LEAD", "Lead");
	define("STUDENT", "Student");
	define("ADMISSION_REP", "Admission Rep");
	define("CAMPUS", "Campus");
	define("LEAD_SOURCE", "Lead Source");
	define("LEAD_ENTRY_FROM_DATE", "Lead Entry From Date ");
	define("LEAD_ENTRY_TO_DATE", "Lead Entry To Date ");
	define("FUNDING", "Fundings");
	define("SELECTED_COUNT", "Selected Count");
	define("PLACEMENT_STATUS", "Placement Status");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("TEMPLATE_NAME", "Template Name");
	define("COURSE_CODE", "Course");
	define("COURSE_OFFERING_PAGE_TITLE", "Course Offering");
	define("GROUP_CODE", "Student Group Code");
	define("FIRST_TERM", "First Term");
	define("PROGRAM", "Program");
	define("STATUS", "Status");
	define("GENERATE", "Generate");
	define("LEAD", "Lead");
	define("STUDENT", "Student");
	define("ADMISSION_REP", "Admission Rep");
	define("CAMPUS", "Campus");
	define("LEAD_SOURCE", "Lead Source");
	define("LEAD_ENTRY_FROM_DATE", "Lead Entry From Date ");
	define("LEAD_ENTRY_TO_DATE", "Lead Entry To Date ");
	define("FUNDING", "Fundings");
	define("SELECTED_COUNT", "Selected Count");
}