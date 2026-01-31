<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("BPPE_SETUP_TITLE", "BPPE Report Setup");
	define("EXCLUDED_PROGRAMS", "Excluded Programs");
	define("EXCLUDE_STUDENT_STATUS", "Excluded Student Statuses");
	define("STUDENTS_NOT_AVAILABLE_FOR_GRADUATION", "Students Not Available For Graduation");
	define("STUDENTS_NOT_AVAILABLE_FOR_PLACEMENT", "Students Not Available For Placement");
	define("FEDERAL_LOAN_DEBT_LEDGER_CODES", "Federal Loan Debt Ledger Codes");
	define("TAKING_LICENSURE_EXAM", "Taking Exam");
	define("LICENSURE_EXAM", "Licensure Exam");
	define("LICENSURE_EXAM_NAME", "Licensure Exam Name");
	define("PASSED_FIRST_EXAM", "Passed First Available Exam");
	define("FAILED_FIRST_EXAM", "Failed First Available Exam");
	
	define("REQUIRED_FIELDS", "Required Fields");
	define("REQUIREMENTS", "Requirements");
	define("LICENSURE_EXAM_CONTINUALLY_ADMINISTERED", "Licensure Exams - Continually Administered");
	define("LICENSURE_EXAM_NOT_CONTINUALLY_ADMINISTERED", "Licensure Exams - Not Continually Administered");
	define("REPORT_TYPE", "Report Type");
	define("BPPE_TITLE", "BPPE - School Performance Fact Scheets");
	define("GO_TO_REPORT", "Go To Report");
	define("REPORT_SETUP", "Report Setup");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("BPPE_SETUP_TITLE", "BPPE Report Setup");
	define("EXCLUDED_PROGRAMS", "Excluded Programs");
	define("EXCLUDE_STUDENT_STATUS", "Excluded Student Statuses");
	define("STUDENTS_NOT_AVAILABLE_FOR_GRADUATION", "Students Not Available For Graduation");
	define("STUDENTS_NOT_AVAILABLE_FOR_PLACEMENT", "Students Not Available For Placement");
	define("FEDERAL_LOAN_DEBT_LEDGER_CODES", "Federal Loan Debt Ledger Codes");
	define("TAKING_LICENSURE_EXAM", "Taking Exam");
	define("LICENSURE_EXAM", "Licensure Exam");
	define("LICENSURE_EXAM_NAME", "Licensure Exam Name");
	define("PASSED_FIRST_EXAM", "Passed First Available Exam");
	define("FAILED_FIRST_EXAM", "Failed First Available Exam");
	
	define("REQUIRED_FIELDS", "Required Fields");
	define("REQUIREMENTS", "Requirements");
	define("LICENSURE_EXAM_CONTINUALLY_ADMINISTERED", "Licensure Exams - Continually Administered");
	define("LICENSURE_EXAM_NOT_CONTINUALLY_ADMINISTERED", "Licensure Exams - Not Continually Administered");
	define("REPORT_TYPE", "Report Type");
	define("BPPE_TITLE", "BPPE - School Performance Fact Scheets");
	define("GO_TO_REPORT", "Go To Report");
	define("REPORT_SETUP", "Report Setup");
}