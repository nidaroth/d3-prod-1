<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("STUDENT_PORTAL_USER_PAGE_TITLE", "Student Portal User");
	define("STUDENT_STATUS", "Status");
	define("PROGRAM", "Program");
	define("FIRST_TERM_DATE", "First Term");
	define("NAME_SEARCH", "Name Search");
	define("STUDENT_ID", "Student ID");
	define("DATE_OF_BIRTH", "Date of Birth");
	define("LAST_LOGIN", "Last Login");
	define("LOGIN_STATUS", "Login Status");
	define("MAKE_INACTIVE_MESSAGE", "Are You Sure You Want To Block This User Login Access");
	define("MAKE_ACTIVE_MESSAGE", "Are You Sure You Want To Unblock This User Login Access");
	define("DELETE_LOGIN_MESSAGE", "Are You Sure You Want To Delete This Login Access");
	define("WEB_LOGIN_STATUS", "Login Status");
	define("ACTIVE", "Active");
	define("INACTIVE", "Inactive");
	define("MAKE_INACTIVE", "Make Inactive");
	define("MAKE_ACTIVE", "Make Active");
	define("VIEW_LOG", "View Log");
	define("LOGIN", "Login");
	define("LOGOUT", "Logout");
	define("IP_ADDRESS", "IP Address");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("STUDENT_PORTAL_USER_PAGE_TITLE", "Student Portal User");
	define("STUDENT_STATUS", "Status");
	define("PROGRAM", "Program");
	define("FIRST_TERM_DATE", "First Term");
	define("NAME_SEARCH", "Name Search");
	define("STUDENT_ID", "Student ID");
	define("DATE_OF_BIRTH", "Date of Birth");
	define("LAST_LOGIN", "Last Login");
	define("LOGIN_STATUS", "Login Status");
	define("MAKE_INACTIVE_MESSAGE", "Are You Sure You Want To Block This User Login Access");
	define("MAKE_ACTIVE_MESSAGE", "Are You Sure You Want To Unblock This User Login Access");
	define("DELETE_LOGIN_MESSAGE", "Are You Sure You Want To Delete This Login Access");
	define("WEB_LOGIN_STATUS", "Login Status");
	define("ACTIVE", "Active");
	define("INACTIVE", "Inactive");
	define("MAKE_INACTIVE", "Make Inactive");
	define("MAKE_ACTIVE", "Make Active");
	define("VIEW_LOG", "View Log");
	define("LOGIN", "Login");
	define("LOGOUT", "Logout");
	define("IP_ADDRESS", "IP Address");
}