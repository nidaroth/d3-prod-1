<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("AGREEMENT_LIBRARY_PAGE_TITLE", "Agreement Library");
	define("NAME", "Name");
	define("CONTENT", "Content");
	define("CAMPUS", "Campus");
	define("TAGS", "Tags");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("AGREEMENT_LIBRARY_PAGE_TITLE", "Agreement Library");
	define("NAME", "Name");
	define("CONTENT", "Content");
	define("CAMPUS", "Campus");
	define("TAGS", "Tags");
}