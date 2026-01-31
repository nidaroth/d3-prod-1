<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("PDF_FOOTER_PAGE_TITLE", "Report Footers");
	define("CAMPUS", "Campus");
	define("NAME", "Name");
	define("REPORT_NAME", "Report Name");
	define("TEXT", "Text");
	define("FONT", "Font");
	define("FONT_SIZE", "Font Size");
	define("ALIGNMENT", "Alignment");
	define("BOLD", "Bold");
	define("ITALIC", "Italic");
	define("FOOTER_POSITION", "Footer Position");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("PDF_FOOTER_PAGE_TITLE", "Report Footers");
	define("CAMPUS", "Campus");
	define("NAME", "Name");
	define("REPORT_NAME", "Report Name");
	define("TEXT", "Text");
	define("FONT", "Font");
	define("FONT_SIZE", "Font Size");
	define("ALIGNMENT", "Alignment");
	define("BOLD", "Bold");
	define("ITALIC", "Italic");
	define("FOOTER_POSITION", "Footer Position");
}