<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("STUDENT_TEST_TYPE", "Student Test Type");
	define("TEST_START_DATE", "Test Start Date");
	define("TEST_END_DATE", "Test End Date");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
}