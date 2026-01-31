<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("AR_LEDGER_CODE_PAGE_TITLE", "Ledger Code");
	define("LEDGER_CODE", "Ledger Code");
	define("LEDGER_DESCRIPTION", "Ledger Description");
	define("INVOICE_DESCRIPTION", "Invoice Description");
	define("GL_CODE_DEBIT", "GL Code Debit");
	define("GL_CODE_CREDIT", "GL Code Credit");
	define("TYPE", "Type");
	define("UPLOAD", "Upload");
	define("MAPPING", "Mapping");
	define("MAP", "Map");
	define("UPLOAD_FILE", "Upload File <br>(Excel and CSV only)");
	define("NEED_ANALYSIS", "Need Analysis");
	
	define("AWARD_LETTER", "Award Letter");
	define("TITLE_IV", "Title IV");
	define("INVOICE", "Invoice");
	define("_90_10_GROUP", "90/10 Group");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Statuses");
	define("STUDENT_STATUS", "Student Status");
	define("QUICK_PAYMENT", "Quick Batch");
	define("DIAMOND_PAY", "Diamond Pay");
	define("OFFER_LETTER", "Offer Letter");
	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("DEFAULT_MANAGEMENT", "Default Management"); // DIAM-922
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("AR_LEDGER_CODE_PAGE_TITLE", "Ledger Code");
	define("LEDGER_CODE", "Ledger Code");
	define("LEDGER_DESCRIPTION", "Ledger Description");
	define("INVOICE_DESCRIPTION", "Invoice Description");
	define("GL_CODE_DEBIT", "GL Code Debit");
	define("GL_CODE_CREDIT", "GL Code Credit");
	define("TYPE", "Type");
	define("UPLOAD", "Upload");
	define("MAPPING", "Mapping");
	define("MAP", "Map");
	define("UPLOAD_FILE", "Upload File <br>(Excel and CSV only)");
	define("NEED_ANALYSIS", "Need Analysis");
	
	define("AWARD_LETTER", "Award Letter");
	define("TITLE_IV", "Title IV");
	define("INVOICE", "Invoice");
	define("_90_10_GROUP", "90/10 Group");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Statuses");
	define("STUDENT_STATUS", "Student Status");
	define("QUICK_PAYMENT", "Quick Batch");
	define("DIAMOND_PAY", "Diamond Pay");
	define("OFFER_LETTER", "Offer Letter");
	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("DEFAULT_MANAGEMENT", "Default Management"); // DIAM-922

}
