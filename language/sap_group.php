<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("SAP_GROUP_NAME", "SAP Group Name");
	define("SAP_GROUP_DESCRIPTION", "SAP Group Description");
	define("SAP_TYPE", "SAP Type");
	define("INCLUDED_STUDENT_STATUSES", "Included Student Statuses");
	define("CAMPUS", "Campus");
	define("IS_DEFAULT", "Is Default");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("SAP_GROUP_NAME", "SAP Group Name");
	define("SAP_GROUP_DESCRIPTION", "SAP Group Description");
	define("SAP_TYPE", "SAP Type");
	define("INCLUDED_STUDENT_STATUSES", "Included Student Statuses");
	define("CAMPUS", "Campus");
	define("IS_DEFAULT", "Is Default");
}