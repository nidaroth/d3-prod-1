<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("STUDENT_ACADEMIC_YEAR", "Student Academic Year");
	define("AWARD", "Award");
	define("AMOUNT", "Amount");
	define("TOTAL_AWARDS", "Total Awards");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("STUDENT_ACADEMIC_YEAR", "Student Academic Year");
	define("AWARD", "Award");
	define("AMOUNT", "Amount");
	define("TOTAL_AWARDS", "Total Awards");
}