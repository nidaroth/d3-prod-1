<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("USER_DEFINED_FIELDS_PAGE_TITLE", "User Defined Lists");
	define("NAME", "Name");
	define("DATA_TYPE", "Data Type");
	define("DISPLAY_ORDER", "Display Order");
	define("OPTION", "Option");
	define("ACTION", "Action");
	define("ADD_OPTION", "Add Option");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("USER_DEFINED_FIELDS_PAGE_TITLE", "User Defined Lists");
	define("NAME", "Name");
	define("DATA_TYPE", "Data Type");
	define("DISPLAY_ORDER", "Display Order");
	define("OPTION", "Option");
	define("ACTION", "Action");
	define("ADD_OPTION", "Add Option");
}