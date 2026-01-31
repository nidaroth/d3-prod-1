<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("CUSTOM_REPORT_PAGE_TITLE", "User Customized Reports");
	define("CUSTOM_REPORT_TITLE", "User Customized Reports");
	define("FIELDS", "Fields");
	define("FILTER", "Filters");
	define("GROUP_BY", "Group By");
	define("SELECT", "Select");
	define("FIELD_NAME", "Field Name");
	define("SIZE", "Size");
	define("STUDENT_NAME", "Student Name");
	define("REPORT_NAME", "Report Name");
	define("TOTAL_SIZE_EXCEED", "Total size exceeds 100% allowed for printing to PDF.<br />Please export to Excel to see the full details");
	define("PROCEED", "Proceed");
	define("TOTAL_SIZE", "Total Size");
	define("BALANCE_SIZE", "Balance Size");
	define("LEAD_ENTRY_FROM_DATE", "Lead Entry From Date");
	define("LEAD_ENTRY_END_DATE", "Lead Entry End Date");
	define("INFO", "Info");
	define("CONTACT", "Contact");
	define("PROGRAM_CODE", "Program Code");
	define("PROGRAM_DESCRIPTION", "Program Description");
	define("SSN_DISPLAY_FULL", "SSN (Display Full)");
	define("CUSTOM_FIELDS", "Custom Fields");
	define("PDF_FONT_SIZE", "PDF Font Size");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("CUSTOM_REPORT_PAGE_TITLE", "User Customized Reports");
	define("CUSTOM_REPORT_TITLE", "User Customized Reports");
	define("FIELDS", "Fields");
	define("FILTER", "Filters");
	define("GROUP_BY", "Group By");
	define("SELECT", "Select");
	define("FIELD_NAME", "Field Name");
	define("SIZE", "Size");
	define("STUDENT_NAME", "Student Name");
	define("REPORT_NAME", "Report Name");
	define("TOTAL_SIZE_EXCEED", "Total size exceeds 100% allowed for printing to PDF.<br />Please export to Excel to see the full details");
	define("PROCEED", "Proceed");
	define("TOTAL_SIZE", "Total Size");
	define("BALANCE_SIZE", "Balance Size");
	define("LEAD_ENTRY_FROM_DATE", "Lead Entry From Date");
	define("LEAD_ENTRY_END_DATE", "Lead Entry End Date");
	define("INFO", "Info");
	define("CONTACT", "Contact");
	define("PROGRAM_CODE", "Program Code");
	define("PROGRAM_DESCRIPTION", "Program Description");
	define("SSN_DISPLAY_FULL", "SSN (Display Full)");
	define("CUSTOM_FIELDS", "Custom Fields");
	define("PDF_FONT_SIZE", "PDF Font Size");
}
