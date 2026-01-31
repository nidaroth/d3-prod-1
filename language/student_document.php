<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("STUDENT_DOCUMENT_PAGE_TITLE", "Documents");
	define("REQUESTED", "Requested");
	define("DOCUMENT", "Document");
	define("DATE_RECEIVED", "Date Received");
	define("RECEIVED", "Received");
	define("FOLLOW_UP", "Follow Up Date");
	define("UPLOAD", "Upload");
	define("NOTES", "Notes");
	define("EMPLOYEE", "Employee");
	define("ENROLLMENT", "Enrollment");
	define("DEPARTMENT", "Department");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("STUDENT_DOCUMENT_PAGE_TITLE", "Documents");
	define("REQUESTED", "Requested");
	define("DOCUMENT", "Document");
	define("DATE_RECEIVED", "Date Received");
	define("RECEIVED", "Received");
	define("FOLLOW_UP", "Follow Up Date");
	define("UPLOAD", "Upload");
	define("NOTES", "Notes");
	define("EMPLOYEE", "Employee");
	define("ENROLLMENT", "Enrollment");
	define("DEPARTMENT", "Department");
}