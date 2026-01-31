<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PROGRAM_GROUP_PAGE_TITLE", "Program Group");
	define("PROGRAM_GROUP", "Program Group");
	define("DESCRIPTION", "Description");
	define("CAPACITY", "Capacity");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("PROGRAM_GROUP_PAGE_TITLE", "Program Group");
	define("PROGRAM_GROUP", "Program Group");
	define("DESCRIPTION", "Description");
	define("CAPACITY", "Capacity");
}