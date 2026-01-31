<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("CPL_SETUP_TITLE", "CPL Report Setup");
	define("NON_GRADUATE_COMPLETER", "Non Graduate Completer");
	define("WITHDRAWALS", "Withdrawals");
	define("LICENSURE_EXAM", "Licensure Exam(Student Event Type)");
	define("WAITING_LICENSURE_EXAM", "Waiting Licensure Exam(Student Event Status)");
	define("TOOK_LICENSURE_EXAM", "Took Licensure Exam(Student Event Status)");
	define("PASSED_LICENSURE_EXAM", "Passed Licensure Exam(Student Event Status)");
	define("REFUSED_EMPLOYEMENT", "Refused Employement(Placement Student Status)");
	define("UNAVAILABLE_FOR_CREDENTIALS", "Unavailable For Credential");
	define("EXCLUDE_PROGRAM", "Excluded Programs");
	define("EXCLUDE_STUDENT_STATUS", "Excluded Student Statuses");
	define("WITHDRAWAL_MESSAGE", "Withdrawals who are Employed In Field/Related Field will be automatically counted as Non-Grad Completers");
	define("FAILED_LICENSURE_EXAM", "Failed Licensure Exam");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("CPL_SETUP_TITLE", "CPL Report Setup");
	define("NON_GRADUATE_COMPLETER", "Non Graduate Completer");
	define("WITHDRAWALS", "Withdrawals");
	define("LICENSURE_EXAM", "Licensure Exam(Student Event Type)");
	define("WAITING_LICENSURE_EXAM", "Waiting Licensure Exam(Student Event Status)");
	define("TOOK_LICENSURE_EXAM", "Took Licensure Exam(Student Event Status)");
	define("PASSED_LICENSURE_EXAM", "Passed Licensure Exam(Student Event Status)");
	define("REFUSED_EMPLOYEMENT", "Refused Employement(Placement Student Status)");
	define("UNAVAILABLE_FOR_CREDENTIALS", "Unavailable For Credential");
	define("EXCLUDE_PROGRAM", "Excluded Programs");
	define("EXCLUDE_STUDENT_STATUS", "Excluded Student Statuses");
	define("WITHDRAWAL_MESSAGE", "Withdrawals who are Employed In Field/Related Field will be automatically counted as Non-Grad Completers");
	define("FAILED_LICENSURE_EXAM", "Failed Licensure Exam");
}