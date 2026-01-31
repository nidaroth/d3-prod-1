<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("TERM_MASTER_PAGE_TITLE", "Term");
	define("BEGIN_DATE", "Begin Date");
	define("END_DATE", "End Date");
	define("DESCRIPTION", "Description");
	define("GROUP", "Group");
	define("ALLOW_ONLINE_ENROLLMENT", "Allow Online Enrollment");
	define("LMS_ACTIVE", "LMS Active");
	define("OLD_DSIS_ID", "Old DSIS ID");
	define("SIS_ID", "SIS ID");
	
	define("CONSOLIDATE_TERM", "Consolidate Term");
	define("TERM_TO_KEEP", "Term To Keep");
	define("TERM_TO_CONDOLIDATE", "Term To Delete");
	define("CONSOLIDATE_TERM_WARNING", "WARNING: Deleted records cannot be restored");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("TERM_MASTER_PAGE_TITLE", "Term");
	define("BEGIN_DATE", "Begin Date");
	define("END_DATE", "End Date");
	define("DESCRIPTION", "Description");
	define("GROUP", "Group");
	define("ALLOW_ONLINE_ENROLLMENT", "Allow Online Enrollment");
	define("LMS_ACTIVE", "LMS Active");
	define("OLD_DSIS_ID", "Old DSIS ID");
	define("SIS_ID", "SIS ID");
	
	define("CONSOLIDATE_TERM", "Consolidate Term");
	define("TERM_TO_KEEP", "Term To Keep");
	define("TERM_TO_CONDOLIDATE", "Term To Delete");
	define("CONSOLIDATE_TERM_WARNING", "WARNING: Deleted records cannot be restored");
}