<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("DOCUMENT_TYPE", "Document Type");
	define("EMPLOYEE", "Employee");
	define("TERM", "Term");
	define("DATE_TYPE", "Date Type");
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("RECEIVED", "Received");
	define("FIRST_TERM", "First Term");
	define("DOCUMENT", "Document");
	define("REQUESTED", "Requested");
	define("FOLLOW_UP", "Follow Up");
	define("DEPARTMENT", "Department");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("DOCUMENT_TYPE", "Document Type");
	define("EMPLOYEE", "Employee");
	define("TERM", "Term");
	define("DATE_TYPE", "Date Type");
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("RECEIVED", "Received");
	define("FIRST_TERM", "First Term");
	define("DOCUMENT", "Document");
	define("REQUESTED", "Requested");
	define("FOLLOW_UP", "Follow Up");
	define("DEPARTMENT", "Department");
}