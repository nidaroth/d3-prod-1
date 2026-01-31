<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("SELECT_ENROLLMENT", "Select Enrollment");
	define("AS_OF_DATE", "As of Date");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("SELECT_ENROLLMENT", "Select Enrollment");
	define("AS_OF_DATE", "As of Date");
}