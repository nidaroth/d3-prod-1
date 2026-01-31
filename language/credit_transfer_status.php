<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("CREDIT_TRANSFER_STATUS_PAGE_TITLE", "Transfer Status");
	define("CREDIT_TRANSFER_STATUS", "Transfer Status");
	define("SHOW_ON_TRANSCRIPT", "Show on Transcript");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("CREDIT_TRANSFER_STATUS_PAGE_TITLE", "Transfer Status");
	define("CREDIT_TRANSFER_STATUS", "Transfer Status");
	define("SHOW_ON_TRANSCRIPT", "Show on Transcript");
}