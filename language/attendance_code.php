<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ATTENDANCE_CODE_PAGE_TITLE", "Attendance Code");
	define("ATTENDANCE_CODE", "Attendance Code");
	define("CODE", "Code");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ATTENDANCE_CODE_PAGE_TITLE", "Código de asistencia");
	define("ATTENDANCE_CODE", "Código de asistencia");
	define("CODE", "Código");
}