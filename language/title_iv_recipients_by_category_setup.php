<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("TITLE_IV_RECIPIENTS_CATEGORY", "Title IV Recipients By Category");
	define("LEDGER_CODES", "Ledger Codes");
	define("LEDGER_CODE", "Ledger Code");
	define("TITLE_IV_RECIPIENTS_CATEGORY_1", "Title IV Recipients Category");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("TITLE_IV_RECIPIENTS_CATEGORY", "Title IV Recipients By Category");
	define("LEDGER_CODES", "Ledger Codes");
	define("LEDGER_CODE", "Ledger Code");
	define("TITLE_IV_RECIPIENTS_CATEGORY_1", "Title IV Recipients Category");
}