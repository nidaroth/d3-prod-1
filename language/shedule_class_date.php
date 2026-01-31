<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("STUDENT_PAGE_TITLE", "Student");
	define("SHEDULE_CLASS_DATE", "Shedule class date");
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("START_TIME", "Start Time");
	define("END_TIME", "End Time");
	define("HOURS", "Hours");
	define("SUNDAY", "Sunday");
	define("MONDAY", "Monday");
	define("TUESDAY", "Tuesday");
	define("WEDNESDAY", "Wednesday");
	define("THURSDAY", "Thursday");
	define("FRIDAY", "Friday");
	define("SATURDAY", "Saturday");
	define("CHK_SHEDULE_HOLIDAYS", "Shedule on holidays?");
	define("CHK_SHEDULE_OVERWRITE", "Overwrite Scheduled dates?");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("STUDENT_PAGE_TITLE", "Student");
	define("SHEDULE_CLASS_DATE", "Shedule class date");
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("START_TIME", "Start Time");
	define("END_TIME", "End Time");
	define("HOURS", "Hours");
	define("SUNDAY", "Sunday");
	define("MONDAY", "Monday");
	define("TUESDAY", "Tuesday");
	define("WEDNESDAY", "Wednesday");
	define("THURSDAY", "Thursday");
	define("FRIDAY", "Friday");
	define("SATURDAY", "Saturday");
	define("CHK_SHEDULE_HOLIDAYS", "Shedule on holidays?");
	define("CHK_SHEDULE_OVERWRITE", "Overwrite Scheduled dates?");
}