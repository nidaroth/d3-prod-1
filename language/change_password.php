<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("CHANGE_PASSWORD_PAGE_TITLE", "Change Password");
	define("CURRENT_PASSWORD", "Current Password");
	define("NEW_PASSWORD", "New Password");
	define("CONFIRM_PASSWORD", "Confirm Password");
	define("CHANGE_PASSWORD_SUCCESS_MSG", "Password Changed Successfully");
	define("INVALID_OLD_PASSWORD", "Invalid old password");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("CHANGE_PASSWORD_PAGE_TITLE", "Change Password");
	define("CURRENT_PASSWORD", "Current Password");
	define("NEW_PASSWORD", "New Password");
	define("CONFIRM_PASSWORD", "Confirm Password");
	define("CHANGE_PASSWORD_SUCCESS_MSG", "Password Changed Successfully");
	define("INVALID_OLD_PASSWORD", "Invalid old password");
}