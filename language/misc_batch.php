<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("MISC_BATCH_PAGE_TITLE", "Misc Batch");
	define("BATCH_NO", "Batch #");
	define("BATCH_DATE", "Batch Date");
	define("LEDGER_CODE", "Ledger Code");
	define("SAVE_AS_HOLD", "Save as Hold");
	define("SAVE_AS_POST", "Save as Post");
	define("STATUS", "Status");
	define("SSN", "SSN");
	define("TRANS_DATE", "Trans. Date");
	define("ADD_STUDENT", "Add Student");
	define("BATCH_TOTAL", "Batch Totals");
	define("POST_TO_LEDGER", "Post to Ledger");
	define("CREDIT_AND_DEBIT_TOTAL_DOESNOT_MATCH", "Batch Credit & Debit Amount Does Not Match. Please update to continue");
	define("CREDIT_TOTAL_DOESNOT_MATCH", "Batch Credit Amount Does Not Match. Please update to continue");
	define("DEBIT_TOTAL_DOESNOT_MATCH", "Batch Debit Amount Does Not Match. Please update to continue");
	define("UNPOST_MESSAGE", "Are you sure you Want to Unpost?");
	define("UNPOST", "Unpost");
	define("UNPOSTBATCH", "Unpost Batch");
	define("UNPOST_CONFIRMATION", "Confirmation");

	define("DOWNLOAD_REPORT", "Download Report");
	define("TOTAL_AMOUNT_DOESNOT_MATCH", "Batch Amount does not match. Please update to continue");
	define("PRIOR_YEAR", "Prior Year");
	
	define("POSTED_DATE", "Posted Date");
	define("BATCH_AMOUNT", "Batch Amount");
	define("BATCH_STATUS", "Batch Status");
	define("DEBIT_TOTAL", "Debit Total");
	define("CREDIT_TOTAL", "Credit Total");
	define("BATCH_DESCRIPTION", "Batch Description");
	define("FEE_PAYMENT_TYPE", "Fee/Payment Type");
	define("EDIT_BATCH_DESCRIPTION", "Edit Batch Description");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("MISC_BATCH_PAGE_TITLE", "Misc Batch");
	define("BATCH_NO", "Batch #");
	define("BATCH_DATE", "Batch Date");
	define("LEDGER_CODE", "Ledger Code");
	define("SAVE_AS_HOLD", "Save as Hold");
	define("SAVE_AS_POST", "Save as Post");
	define("STATUS", "Status");
	define("SSN", "SSN");
	define("TRANS_DATE", "Trans. Date");
	define("ADD_STUDENT", "Add Student");
	define("BATCH_TOTAL", "Batch Totals");
	define("POST_TO_LEDGER", "Post to Ledger");
	define("CREDIT_AND_DEBIT_TOTAL_DOESNOT_MATCH", "Batch Credit & Debit Amount Does Not Match. Please update to continue");
	define("CREDIT_TOTAL_DOESNOT_MATCH", "Batch Credit Amount Does Not Match. Please update to continue");
	define("DEBIT_TOTAL_DOESNOT_MATCH", "Batch Debit Amount Does Not Match. Please update to continue");
	define("UNPOST_MESSAGE", "Are you sure you Want to Unpost?");
	define("UNPOST", "Unpost");
	define("UNPOSTBATCH", "Unpost Batch");
	define("UNPOST_CONFIRMATION", "Confirmation");
	define("DOWNLOAD_REPORT", "Download Report");
	define("TOTAL_AMOUNT_DOESNOT_MATCH", "Batch Amount does not match. Please update to continue");
	define("PRIOR_YEAR", "Prior Year");
	define("POSTED_DATE", "Posted Date");
	define("BATCH_AMOUNT", "Batch Amount");
	define("BATCH_STATUS", "Batch Status");
	define("DEBIT_TOTAL", "Debit Total");
	define("CREDIT_TOTAL", "Credit Total");
	define("BATCH_DESCRIPTION", "Batch Description");
	define("FEE_PAYMENT_TYPE", "Fee/Payment Type");
	define("EDIT_BATCH_DESCRIPTION", "Edit Batch Description");
}
