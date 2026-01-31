<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ECM_LEDGER_TITLE", "ECM Mapping");
	define("MAP_ECM", "Map ECM Ledger Codes");
	define("ECM_LEDGER", "ECM Code");
	define("LEDGER_CODE", "Diamond Ledger Code");
	define("LEDGER_DESCRIPTION", "Diamond Ledger Code Description");
	define("ACTIVE", "Active");
	define("ECM_TYPE", "ECM Type");
	define("AWARD_YEAR", "Award Year");
	define("ECM_TRANSACTION_IMPORT_RESULT", "ECM Transaction Import Result");
	define("POST", "Post");
	
	define("IMPORT_RESULT", "Import Result");
	define("CODE", "Code");
	define("PAYMENT_AMOUNT", "Payment Amount");
	define("PAYMENT_BATCH", "Payment Batch");
	define("AWARD_AMOUNT", "Award Amount");
	
	define("LEDGER_CODE_1", "Ledger Code");
	define("DISBURSEMENT_DATE", "Disbursement Date");
	define("DISBURSEMENT_AMOUNT", "Disbursement Amount");
	define("ECM_DISBURSEMENT_DATE", "ECM Disbursement Date");
	define("ECM_DISBURSEMENT_AMOUNT", "ECM Amount");
	
	define("STUDENT", "Student");
	define("EXPORT_TO_PDF", "Export To PDF");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ECM_LEDGER_TITLE", "ECM Mapping");
	define("MAP_ECM", "Map ECM Ledger Codes");
	define("ECM_LEDGER", "ECM Code");
	define("LEDGER_CODE", "Diamond Ledger Code");
	define("LEDGER_DESCRIPTION", "Diamond Ledger Code Description");
	define("ACTIVE", "Active");
	define("ECM_TYPE", "ECM Type");
	define("AWARD_YEAR", "Award Year");
	define("ECM_TRANSACTION_IMPORT_RESULT", "ECM Transaction Import Result");
	define("POST", "Post");
	
	define("IMPORT_RESULT", "Import Result");
	define("CODE", "Code");
	define("PAYMENT_AMOUNT", "Payment Amount");
	define("PAYMENT_BATCH", "Payment Batch");
	define("AWARD_AMOUNT", "Award Amount");
	
	define("LEDGER_CODE_1", "Ledger Code");
	define("DISBURSEMENT_DATE", "Disbursement Date");
	define("DISBURSEMENT_AMOUNT", "Disbursement Amount");
	define("ECM_DISBURSEMENT_DATE", "ECM Disbursement Date");
	define("ECM_DISBURSEMENT_AMOUNT", "ECM Amount");
	define("STUDENT", "Student");
	define("EXPORT_TO_PDF", "Export To PDF");
}