<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("USER_ACTIVITY_TITLE", "User Activity");
	define("USER_NAME", "User Name");
	define("LOGIN_ID", "Login ID");
	define("USER_TYPE", "User Type");
	define("USER_TYPES", "User Types");
	define("LOGIN_TIME", "Log In Time");
	define("LOGOUT_TIME", "Log Out Time");
	define("FROM_LOGIN_DATE", "From Log In Date");
	define("TO_LOGIN_DATE", "To Log In Date");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("USER_ACTIVITY_TITLE", "User Activity");
	define("USER_NAME", "User Name");
	define("LOGIN_ID", "Login ID");
	define("USER_TYPE", "User Type");
	define("USER_TYPES", "User Types");
	define("LOGIN_TIME", "Log In Time");
	define("LOGOUT_TIME", "Log Out Time");
	define("FROM_LOGIN_DATE", "From Log In Date");
	define("TO_LOGIN_DATE", "To Log In Date");
}