<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("MAKE_PAYMENT_TITLE", "Make Payment");
	define("NAME_ON_CARD", "Name On Card");
	define("CARD_NO", "Card #");
	define("CARD_EXP", "Card Exp [MM/YY]");
	define("CVV", "CVV");
	define("CARD_TYPE", "Card Type");
	define("CARD_ON_FILE", "Card On File");
	define("TOTAL_DISBURSEMENT_AMOUNT", "Total Disbursement Amount");
	define("CARD_INFO", "Card Info");
	define("STUDENT", "Student");
	define("STUDENT_ID", "Student ID");
	define("LEDGER", "Ledger");
	define("DISBURSEMENT_DATE", "Disbursement Date");
	define("DISBURSEMENT_AMOUNT", "Disbursement Amount");
	define("SAVE_MAKE_PAYMENT", "Save & Make Payment");
	
	define("ADD_CARD", "Add Card");
	define("DELETE_CARD", "Delete Card");
	define("UPDATE_CARD", "Update Card");
	define("DELETE_CONFIRMATION_MSG", "Are you sure you want to Delete this Card?");
	define("ADD_CARD_MAKE_PAYMENT", "Add Card & Make Payment");
	
	define("RECURRING_PAYMENTS", "Recurring Payments");
	define("ENABLE_RECURRING_PAYMENTS", "By checking 'Enable Recurring Payments' you agree that the primary card on file will be charged the amount listed on the specified date along with any applicable processing fees. This option can be turned off at any time. Please allow 48 hours for changes to take effect. Adjustments to this payment plan must be processed  through your school's Finance department. ");
	define("DISABLE_RECURRING_PAYMENTS", "Due to processing time, Payments scheduled within 48 hours of disabling Recurring Payments may still be processed.");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("MAKE_PAYMENT_TITLE", "Make Payment");
	define("NAME_ON_CARD", "Name On Card");
	define("CARD_NO", "Card #");
	define("CARD_EXP", "Card Exp [MM/YY]");
	define("CVV", "CVV");
	define("CARD_TYPE", "Card Type");
	define("CARD_ON_FILE", "Card On File");
	define("TOTAL_DISBURSEMENT_AMOUNT", "Total Disbursement Amount");
	define("CARD_INFO", "Card Info");
	define("STUDENT", "Student");
	define("STUDENT_ID", "Student ID");
	define("LEDGER", "Ledger");
	define("DISBURSEMENT_DATE", "Disbursement Date");
	define("DISBURSEMENT_AMOUNT", "Disbursement Amount");
	define("SAVE_MAKE_PAYMENT", "Save & Make Payment");
	
	define("ADD_CARD", "Add Card");
	define("DELETE_CARD", "Delete Card");
	define("UPDATE_CARD", "Update Card");
	define("DELETE_CONFIRMATION_MSG", "Are you sure you want to Delete this Card?");
	define("ADD_CARD_MAKE_PAYMENT", "Add Card & Make Payment");
	
	define("RECURRING_PAYMENTS", "Recurring Payments");
	define("ENABLE_RECURRING_PAYMENTS", "By checking 'Enable Recurring Payments' you agree that the primary card on file will be charged the amount listed on the specified date along with any applicable processing fees. This option can be turned off at any time. Please allow 48 hours for changes to take effect. Adjustments to this payment plan must be processed  through your school's Finance department. ");
	define("DISABLE_RECURRING_PAYMENTS", "Due to processing time, Payments scheduled within 48 hours of disabling Recurring Payments may still be processed.");
}