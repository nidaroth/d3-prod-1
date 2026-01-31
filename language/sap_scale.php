<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("SAP_SCALE_NAME", "SAP Scale Name");
	define("SAP_SCALE_DESCRIPTION", "SAP Scale Description");
	define("SAP_SCALE_OPTION", "SAP Scale Options");
	define("CAMPUS", "Campus");
	define("IS_DEFAULT", "Is Default");
	define("ATTENDANCE", "Attendance");
	define("CREDIT_UNIT", "Credits/Units");
	define("GPA", "GPA");
	define("INCLUDE_TRANSFERS", "Include Transfers");
	define("HOURS", "Hours");
	define("PERIOD", "Period");
	define("RATE", "Rate");
	define("MIN", "Min");
	define("MIN_GPA", "Min GPA");
	define("HOURS_COMPLETED", "Hours Completed");
	define("HOURS_SCHEDULED", "Hours Scheduled");
    define("SCHEDULE_HOURS", "Schedule Hours");
    define("ABSENT_HOURS", "Absent Hours");


	define("HOURS_COMPLETED_MIN", "Hours Completed Minimum");
	define("HOURS_COMPLETED_MAX", "Hours Completed Maximum");
	define("HOURS_SCHEDULED_MIN", "Hours Scheduled Minimum");
	define("HOURS_SCHEDULED_MAX", "Hours Scheduled Maximum");
	define("CREDIT_UNIT_FA", "Credits/Units - FA");
	define("CREDIT_UNIT_FA_MIN", "Credits/Units - FA Minimum");
	define("CREDIT_UNIT_FA_MAX", "Credits/Units - FA Maximum");
	define("CREDIT_UNIT_STD", "Credits/Units - Standard");
	define("CREDIT_UNIT_STD_MIN", "Credits/Units - Standard Minimum");
	define("CREDIT_UNIT_STD_MAX", "Credits/Units - Standard Maximum");
	define("GPA_CUMULATIVE", "GAP - Cumulative");
	define("GPA_TERM_MIN", "GPA - Term Minimum");
	define("GPA_TERM_MAX", "GPA - Term Maximum");
	define("GPA_TERM", "GAP - Term");
	define("GPA_CUL_MIN", "GPA - Cumulative Minimum");
	define("GPA_CUL_MAX", "GPA - Cumulative Maximum");
	define("MIDPOINT", "Midpoint");
	define("MIDPOINT_START_DATE", "Midpoint Start Date");
	define("MIDPOINT_END_DATE", "Midpoint End Date");
	define("SAP_GROUP", "SAP Group");

	define("STANDARD_CREDIT_UNIT", "Standard Credits/Units");
	define("FA_CREDIT_UNIT", "FA Credits/Units");
	define("HOURS_FA_CREDIT_UNIT", "FA<br />Credits/Units");
	define("HOURS_STANDARD_CREDIT_UNIT", "Standard<br />Credits/Units");
	define("MIN_CREDIT_UNIT", "Minimum Credits/Units");
	define("MIN_HOUR", "Minimum Hours");
	define("MIN_PERCENTAGE", "Minimum Percentage");
	define("GPA_BY_TERM", "By Term");
	define("MIN_NUMBER_GRADE", "Minimum Number Grade");
	define("MIN_NUMERIC_GRADE", "Minimum Numeric Grade");
	define("GPA_BY_CUMULATIVE", "By Cumulative");
	define("TERM", "Term");
	define("CUMULATIVE", "Cumulative");
	define("PROGRAM_PERCENTAGE", "Program<br />Percentage");
	define("SAP_WARNING_STATUS_IF_FAILED", "SAP Warning<br />Status If Failed");
	define("MIN_HOUR_1", "Minimum<br />Hours");
	define("MIN_CREDIT_UNIT_1", "Minimum<br />Credits/Units");
	define("MIN_PERCENTAGE_1", "Minimum<br />Percentage");
	define("MIN_NUMBER_GRADE_1", "Min Number<br />Grade");
	define("MIN_NUMERIC_GRADE_1", "Min Numeric<br />Grade");
	define("FIRST_TERM", "First Term");
	define("GROUP_CODE", "Group Code");

	define("PROGRAM_PACE", "Program Pace");

	define("HOURS_COMPLETED_SCHEDULED", "Hours Completed/Hours Scheduled");
	define("HOURS_COMPLETED_PROGRAM", "Hours Completed/Program Hours");
	define("HOURS_SCHEDULED_PROGRAM", "Hours Scheduled/Program Hours");

	define("FA_UNITS_COMPLETED_ATTEMPTED", "FA Units Completed/FA Units Attempted");
	define("FA_UNITS_COMPLETED_PROGRAM", "FA Units Completed/Program FA Units");
	define("FA_UNITS_ATTEMPTED_PROGRAM", "FA Units Attempted/Program FA Units");

	define("STD_UNITS_COMPLETED_ATTEMPTED", "Units Completed/Hours Attempted");
	define("STD_UNITS_COMPLETED_PROGRAM", "Units Completed/Program Units");
	define("STD_UNITS_ATTEMPTED_PROGRAM", "Units Attempted/Program Units");

	define("GPA_CUMULATIVES", "Cumulative GPA");
	define("INCLUDE_FIRST_PERIOD", "Include In First Period Only");
	define("COURSE_CODE", "Course");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("SAP_SCALE_NAME", "SAP Scale Name");
	define("SAP_SCALE_DESCRIPTION", "SAP Scale Description");
	define("SAP_SCALE_OPTION", "SAP Scale Options");
	define("CAMPUS", "Campus");
	define("IS_DEFAULT", "Is Default");
	define("ATTENDANCE", "Attendance");
	define("CREDIT_UNIT", "Credits/Units");
	define("GPA", "GPA");
	define("INCLUDE_TRANSFERS", "Include Transfers");
	define("HOURS", "Hours");
	define("PERIOD", "Period");
	define("RATE", "Rate");
	define("MIN", "Min");
	define("MIN_GPA", "Min GPA");
	define("HOURS_COMPLETED", "Hours Completed");
	define("HOURS_SCHEDULED", "Hours Scheduled");

	define("HOURS_COMPLETED_MIN", "Hours Completed Minimum");
	define("HOURS_COMPLETED_MAX", "Hours Completed Maximum");
	define("HOURS_SCHEDULED_MIN", "Hours Scheduled Minimum");
	define("HOURS_SCHEDULED_MAX", "Hours Scheduled Maximum");
	define("CREDIT_UNIT_FA", "Credits/Units - FA");
	define("CREDIT_UNIT_FA_MIN", "Credits/Units - FA Minimum");
	define("CREDIT_UNIT_FA_MAX", "Credits/Units - FA Maximum");
	define("CREDIT_UNIT_STD", "Credits/Units - Standard");
	define("CREDIT_UNIT_STD_MIN", "Credits/Units - Standard Minimum");
	define("CREDIT_UNIT_STD_MAX", "Credits/Units - Standard Maximum");
	define("GPA_CUMULATIVE", "GAP - Cumulative");
	define("GPA_TERM_MIN", "GPA - Term Minimum");
	define("GPA_TERM_MAX", "GPA - Term Maximum");
	define("GPA_TERM", "GAP - Term");
	define("GPA_CUL_MIN", "GPA - Cumulative Minimum");
	define("GPA_CUL_MAX", "GPA - Cumulative Maximum");
	define("MIDPOINT", "Midpoint");
	define("MIDPOINT_START_DATE", "Midpoint Start Date");
	define("MIDPOINT_END_DATE", "Midpoint End Date");
	define("SAP_GROUP", "SAP Group");

	define("STANDARD_CREDIT_UNIT", "Standard Credits/Units");
	define("FA_CREDIT_UNIT", "FA Credits/Units");
	define("HOURS_FA_CREDIT_UNIT", "FA<br />Credits/Units");
	define("HOURS_STANDARD_CREDIT_UNIT", "Standard<br />Credits/Units");
	define("MIN_CREDIT_UNIT", "Minimum Credits/Units");
	define("MIN_HOUR", "Minimum Hours");
	define("MIN_PERCENTAGE", "Minimum Percentage");
	define("GPA_BY_TERM", "By Term");
	define("MIN_NUMBER_GRADE", "Minimum Number Grade");
	define("MIN_NUMERIC_GRADE", "Minimum Numeric Grade");
	define("GPA_BY_CUMULATIVE", "By Cumulative");
	define("TERM", "Term");
	define("CUMULATIVE", "Cumulative");
	define("PROGRAM_PERCENTAGE", "Program<br />Percentage");
	define("SAP_WARNING_STATUS_IF_FAILED", "SAP Warning<br />Status If Failed");
	define("MIN_HOUR_1", "Minimum<br />Hours");
	define("MIN_CREDIT_UNIT_1", "Minimum<br />Credits/Units");
	define("MIN_PERCENTAGE_1", "Minimum<br />Percentage");
	define("MIN_NUMBER_GRADE_1", "Min Number<br />Grade");
	define("MIN_NUMERIC_GRADE_1", "Min Numeric<br />Grade");
	define("FIRST_TERM", "First Term");
	define("GROUP_CODE", "Group Code");

	define("PROGRAM_PACE", "Program Pace");

	define("HOURS_COMPLETED_SCHEDULED", "Hours Completed/Hours Scheduled");
	define("HOURS_COMPLETED_PROGRAM", "Hours Completed/Program Hours");
	define("HOURS_SCHEDULED_PROGRAM", "Hours Scheduled/Program Hours");

	define("FA_UNITS_COMPLETED_ATTEMPTED", "FA Units Completed/FA Units Attempted");
	define("FA_UNITS_COMPLETED_PROGRAM", "FA Units Completed/Program FA Units");
	define("FA_UNITS_ATTEMPTED_PROGRAM", "FA Units Attempted/Program FA Units");

	define("STD_UNITS_COMPLETED_ATTEMPTED", "Units Completed/Hours Attempted");
	define("STD_UNITS_COMPLETED_PROGRAM", "Units Completed/Program Units");
	define("STD_UNITS_ATTEMPTED_PROGRAM", "Units Attempted/Program Units");

	define("GPA_CUMULATIVES", "Cumulative GPA");
	define("INCLUDE_FIRST_PERIOD", "Include In First Period Only");
	define("COURSE_CODE", "Course");
	
}
