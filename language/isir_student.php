<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ISIR_UPLOAD_PAGE_TITLE", "ISIR Upload Info");
	define("ISIR_UPLOAD_PAGE_TITLE_BACKGROUND", "ISIR Upload Info");
	define("ISIR_PAGE_TITLE", "ISIR");
	define("ISIR_PAGE_TITLE_BACKGROUND", "ISIR BACKGROUND PROCESS");
	define("ERROR_FILE_FORMAT", "Invalid File");
	define("CREATE_LEAD", "Create Lead");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ISIR_UPLOAD_PAGE_TITLE", "ISIR Upload Info");
	define("ISIR_PAGE_TITLE", "ISIR");
	define("ERROR_FILE_FORMAT", "Invalid File");
	define("CREATE_LEAD", "Create Lead");
	define("ISIR_UPLOAD_PAGE_TITLE_BACKGROUND", "ISIR Upload Info");
	define("ISIR_PAGE_TITLE_BACKGROUND", "ISIR BACKGROUND PROCESS");
}