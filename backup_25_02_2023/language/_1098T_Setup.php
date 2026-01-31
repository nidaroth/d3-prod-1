<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("SELECT_SETUP_CODES", "Select Setup Codes", true);
	define("EXCLUDED_PROGRAMS", "Excluded Programs", true);
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Statuses", true);
	define("AWARD_LEDGER_CODE_CATEGORY", "Award Ledger Codes & Categories", true);
	define("EXCLUDED_FEE_LEDGER_CODES", "Excluded Fee Ledger Codes", true);
	define("_1098T_CHANGED_REPORTING_METHOD", "1098T Changed Reporting Method", true);
	define("_1098_CORRECTED", "1098T Corrected", true);
	define("IGNORE_ENROLLMENT_REQUIREMENT", "Ignore Enrollment Requirement", true);
	define("IGNORE_POSITIVE_REQUIREMENT", "Ignore Positive Requirement", true);
	define("SETUP", "Setup", true);
	define("INCLUDE_IN_1098T_REPORTING", "Include in 1098T Reporting", true);
	define("CATEGORY", "Category", true);
	
	define("_1098T", "1098T", true);
	define("CALENDAR_YEAR", "Calendar Year", true);
	define("VIEW_RELATED_STUDENT_LEDGERS", "View Related Student Ledgers", true);
	define("PRINT_1098T_FORMS", "Print 1098T Forms", true);
	define("ERROR_REPORT", "Error Report", true);
	define("CREATE_ELECTRONIC_FILE", "Create Electronic File", true);
	define("CAMPUS", "Campus", true);
	define("EXPORT_DATA_TO_REVIEW", "Export Data to Review", true);
	define("TRANSMITTER_CONTROL_CODE", "Transmitter Control Code", true);
	define("TRANSMITTER", "Transmitter", true);
	define("TRANSMITTER_NAME", "Transmitter Name", true);
	define("COMPANY_ISSUER_NAME", "Company/Issuer Name", true);
	define("ADDRESS", "Address", true);
	define("ADDRESS_1", "Address 2nd Line", true);
	define("CITY", "City", true);
	define("PK_STATES", "State", true);
	define("ZIP", "Zip", true);
	define("FEDERAL_ID_NO", "Federal ID No.", true);
	define("CONTACT_NAME", "Contact Name", true);
	define("CONTACT_PHONE", "Contact Phone", true);
	define("CONTACT_EMAIL", "Contact Email", true);
	define("EIN", "EIN", true);
	define("EIN_1", "EIN (Employer Identification Number)", true);
	
	define("VIEW_RELATED_STUDENT_LEDGER", "View Related Student Ledger", true);
	define("PRINT_1098T_FORM", "Print 1098T Form", true);
	define("_1098T_CATEGORY", "1098T Category", true);
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("SELECT_SETUP_CODES", "Select Setup Codes", true);
	define("EXCLUDED_PROGRAMS", "Excluded Programs", true);
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Statuses", true);
	define("AWARD_LEDGER_CODE_CATEGORY", "Award Ledger Codes & Categories", true);
	define("EXCLUDED_FEE_LEDGER_CODES", "Excluded Fee Ledger Codes", true);
	define("_1098T_CHANGED_REPORTING_METHOD", "1098T Changed Reporting Method", true);
	define("_1098_CORRECTED", "1098T Corrected", true);
	define("IGNORE_ENROLLMENT_REQUIREMENT", "Ignore Enrollment Requirement", true);
	define("IGNORE_POSITIVE_REQUIREMENT", "Ignore Positive Requirement", true);
	define("SETUP", "Setup", true);
	define("INCLUDE_IN_1098T_REPORTING", "Include in 1098T Reporting", true);
	define("CATEGORY", "Category", true);
	
	define("_1098T", "1098T", true);
	define("CALENDAR_YEAR", "Calendar Year", true);
	define("VIEW_RELATED_STUDENT_LEDGERS", "View Related Student Ledgers", true);
	define("PRINT_1098T_FORMS", "Print 1098T Forms", true);
	define("ERROR_REPORT", "Error Report", true);
	define("CREATE_ELECTRONIC_FILE", "Create Electronic File", true);
	define("CAMPUS", "Campus", true);
	define("EXPORT_DATA_TO_REVIEW", "Export Data to Review", true);
	define("TRANSMITTER_CONTROL_CODE", "Transmitter Control Code", true);
	define("TRANSMITTER", "Transmitter", true);
	define("TRANSMITTER_NAME", "Transmitter Name", true);
	define("COMPANY_ISSUER_NAME", "Company/Issuer Name", true);
	define("ADDRESS", "Address", true);
	define("ADDRESS_1", "Address 2nd Line", true);
	define("CITY", "City", true);
	define("PK_STATES", "State", true);
	define("ZIP", "Zip", true);
	define("FEDERAL_ID_NO", "Federal ID No.", true);
	define("CONTACT_NAME", "Contact Name", true);
	define("CONTACT_PHONE", "Contact Phone", true);
	define("CONTACT_EMAIL", "Contact Email", true);
	define("EIN", "EIN", true);
	define("EIN_1", "EIN (Employer Identification Number)", true);
	define("VIEW_RELATED_STUDENT_LEDGER", "View Related Student Ledger", true);
	define("PRINT_1098T_FORM", "Print 1098T Form", true);
	define("_1098T_CATEGORY", "1098T Category", true);
}