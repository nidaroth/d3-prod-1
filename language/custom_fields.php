<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("CUSTOM_FIELDS_PAGE_TITLE", "Custom Fields");
	define("CUSTOM_FIELDS", "Custom Fields");
	define("FIELD_NAME", "Field Name");
	define("SECTION", "Section");
	define("MODULE", "Module");
	define("TAB", "Tab");
	define("DATA_TYPE", "Data Type");
	define("USER_DEFINED_FIELDS", "User Defined List");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("CUSTOM_FIELDS_PAGE_TITLE", "Custom Fields");
	define("CUSTOM_FIELDS", "Custom Fields");
	define("FIELD_NAME", "Field Name");
	define("SECTION", "Section");
	define("MODULE", "Module");
	define("TAB", "Tab");
	define("DATA_TYPE", "Data Type");
	define("USER_DEFINED_FIELDS", "User Defined List");
	
}