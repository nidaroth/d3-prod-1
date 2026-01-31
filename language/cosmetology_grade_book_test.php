<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("TEST_DESCRIPTION", "Test Description");
	define("AVERAGE_GRADE", "Average Grade");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("TEST_DESCRIPTION", "Test Description");
	define("AVERAGE_GRADE", "Average Grade");
}