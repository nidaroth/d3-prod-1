<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("COMPANY_JOB_PAGE_TITLE", "Company Job");
	define("JOB_NUMBER", "Job Number");
	define("JOB_TITLE", "Job Title");
	define("JOB_TYPE", "Job Type");
	define("CONTACT", "Company Contact");
	define("ADVISOR", "School Employee");
	define("SOC_CODE", "SOC Code");
	define("JOB_POSTED", "Job Posted");
	define("JOB_FILLED", "Job Filled");
	define("JOB_CANCELED", "Job Canceled");
	define("EMPLOYMENT", "Employment Type");
	define("ENROLLMENT_STATUS", "Full/Part Time");
	define("PAY_TYPE", "Pay Type");
	define("PAY_AMOUNT", "Pay Amount");
	define("WEEKLY_HOURS", "Weekly Hours");
	define("JOB_DESCRIPTION", "Job Description");
	define("JOB_NOTES", "Job Notes");
	define("PLACEMENT_TYPE", "Job Type");
	define("BENEFITS", "Benefits");
	define("ANNUAL_SALARY", "Annual Salary");
	define("PLACEMENT_STATUS", "Placement Status");
	define("INSTITUTIONAL_EMPLOYMENT", "Institutional Employment");
	define("SELF_EMPLOYED", "Self Employed");
	
	define("JOB_POSTED_START_DATE", "Job Posted Start Date");
	define("JOB_POSTED_END_DATE", "Job Posted End Date");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("COMPANY_JOB_PAGE_TITLE", "Company Job");
	define("JOB_NUMBER", "Job Number");
	define("JOB_TITLE", "Job Title");
	define("JOB_TYPE", "Job Type");
	define("CONTACT", "Company Contact");
	define("ADVISOR", "School Employee");
	define("SOC_CODE", "SOC Code");
	define("JOB_POSTED", "Job Posted");
	define("JOB_FILLED", "Job Filled");
	define("JOB_CANCELLED", "Job Cancelled");
	define("EMPLOYMENT", "Employment Type");
	define("ENROLLMENT_STATUS", "Full/Part Time");
	define("PAY_TYPE", "Pay Type");
	define("PAY_AMOUNT", "Pay Amount");
	define("WEEKLY_HOURS", "Weekly Hours");
	define("JOB_DESCRIPTION", "Job Description");
	define("JOB_NOTES", "Job Notes");
	define("PLACEMENT_TYPE", "Job Type");
	define("BENEFITS", "Benefits");
	define("ANNUAL_SALARY", "Annual Salary");
	define("PLACEMENT_STATUS", "Placement Status");
	define("INSTITUTIONAL_EMPLOYMENT", "Institutional Employment");
	define("SELF_EMPLOYED", "Self Employed");
	define("JOB_POSTED_START_DATE", "Job Posted Start Date");
	define("JOB_POSTED_END_DATE", "Job Posted End Date");
}