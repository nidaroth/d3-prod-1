<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("MNU_IPEDS_WINTER_COLLECTIONS_SETUP_TITLE", "IPEDS Winter Collection Setup");
	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Statuses");
	define("EXCLUDED_DROP_REASON", "Excluded Drop Reasons");
	define("SELECT_SETUP_CODES", "Select Setup Codes");
	define("REQUIRED_FIELDS", "Required Fields");
	define("REQUIREMENTS", "Requirements");
	define("STUDENT_FINANCIAL_AID_SETUP_CODES", "Student Financial Aid Setup Codes");
	define("TRANSFER_OUT", "Transfer Out");
	define("LARGEST_PROGRAM", "Largest Program");
	define("PART_A_GROUP_2A", "Part A Group 2a Ledger Codes");
	define("PART_A_GROUP_2B", "Part A Group 2b Ledger Codes");
	define("PART_A_GROUP_3", "Part A Group 3 Ledger Codes");
	define("PART_B_GROUP_1", "Part B Group 1 Ledger Codes");
	define("PELL", "Pell Ledger Codes");
	define("OTHER_FEDERAL_GRANTS", "Other Federal Grant Ledger Codes");
	define("FEDERAL_STUDENT_LOANS", "Federal Student Loan Ledger Codes");
	define("OTHER_LOANS", "Other Loan Ledger Codes");
	define("PART_C_GROUP_3", "Part C Group 3 Ledger Codes");
	define("PART_C_GROUP_4", "Part C Group 4 Ledger Codes");
	define("PART_D_GROUP_3", "Part D Group 3 Ledger Codes");
	define("PART_E", "Part E Ledger Codes");
	define("POST_911", "Post 9/11 Gi Bill Ledger Codes");
	define("DEPARTMENT_OF_DEFENSE", "Department of Defense Ledger Codes");
	define("SUBSIDIZED_LOAN_LEDGER_CODES", "Subsidized Loan Ledger Codes");
	
	define("APPLICANT", "Applicant Student Status");
	define("ADMISSIONS", "Admissions Student Status");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("MNU_IPEDS_WINTER_COLLECTIONS_SETUP_TITLE", "IPEDS Winter Collection Setup");
	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Statuses");
	define("EXCLUDED_DROP_REASON", "Excluded Drop Reasons");
	define("SELECT_SETUP_CODES", "Select Setup Codes");
	define("REQUIRED_FIELDS", "Required Fields");
	define("REQUIREMENTS", "Requirements");
	define("STUDENT_FINANCIAL_AID_SETUP_CODES", "Student Financial Aid Setup Codes");
	define("TRANSFER_OUT", "Transfer Out");
	define("LARGEST_PROGRAM", "Largest Program");
	define("PART_A_GROUP_2A", "Part A Group 2a Ledger Codes");
	define("PART_A_GROUP_2B", "Part A Group 2b Ledger Codes");
	define("PART_A_GROUP_3", "Part A Group 3 Ledger Codes");
	define("PART_B_GROUP_1", "Part B Group 1 Ledger Codes");
	define("PELL", "Pell Ledger Codes");
	define("OTHER_FEDERAL_GRANTS", "Other Federal Grant Ledger Codes");
	define("FEDERAL_STUDENT_LOANS", "Federal Student Loan Ledger Codes");
	define("OTHER_LOANS", "Other Loan Ledger Codes");
	define("PART_C_GROUP_3", "Part C Group 3 Ledger Codes");
	define("PART_C_GROUP_4", "Part C Group 4 Ledger Codes");
	define("PART_D_GROUP_3", "Part D Group 3 Ledger Codes");
	define("PART_E", "Part E Ledger Codes");
	define("POST_911", "Post 9/11 Gi Bill Ledger Codes");
	define("DEPARTMENT_OF_DEFENSE", "Department of Defense Ledger Codes");
	define("SUBSIDIZED_LOAN_LEDGER_CODES", "Subsidized Loan Ledger Codes");
	define("APPLICANT", "Applicant Student Status");
	define("ADMISSIONS", "Admissions Student Status");
}