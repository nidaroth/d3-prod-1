<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ROOM_PAGE_TITLE", "Room");
	define("CAMPUS", "Campus");
	define("ROOM_NO", "Room Number");
	define("DESCRIPTION", "Description");
	define("CLASS_SIZE", "Class Size");
	define("ROOM_SIZE", "Room Size");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ROOM_PAGE_TITLE", "Room");
	define("CAMPUS", "Campus");
	define("ROOM_NO", "Room Number");
	define("DESCRIPTION", "Description");
	define("CLASS_SIZE", "Class Size");
	define("ROOM_SIZE", "Room Size");
}