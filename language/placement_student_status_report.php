<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PLACEMENT_STUDENT_STATUS_TITLE", "Student Detail");
	define("PLACEMENT_STATUS", "Placement Status");
	define("JOB_TYPE", "Job Type");
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("DATE_TYPE", "Date Type");
	define("RUN", "Run");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("PLACEMENT_STUDENT_STATUS_TITLE", "Student Detail");
	define("PLACEMENT_STATUS", "Placement Status");
	define("JOB_TYPE", "Job Type");
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("DATE_TYPE", "Date Type");
	define("RUN", "Run");
} ?>