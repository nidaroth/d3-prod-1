<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("AWARD_LETTER_PAGE_TITLE", "Award Letter Text");
	define("CAMPUS", "Campus");
	define("NAME", "Name");
	define("TEXT", "Text");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("AWARD_LETTER_PAGE_TITLE", "Award Letter Text");
	define("CAMPUS", "Campus");
	define("NAME", "Name");
	define("TEXT", "Text");
}