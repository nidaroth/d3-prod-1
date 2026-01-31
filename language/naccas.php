<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
    define("NACCAS_SETUP", "NACCAS Annual Report Setup");

	define("REPORT_OPTIONS", "Report Options");

	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("INCLUDED_PROGRAM", "Included Programs");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Status(es)");
	define("INCLUDED_PLACEMENT_STATUS", "Included Placement Status(es)");
	define("DISPLAY_OPTIONS", "Display Options");
	define("TRANSFER_TYPE", "Transfer Type");
	
	define("DROP_REASONS", "Drop Reason(s)");
	define("STUDENTS_STATUS", "Student Status");
	
	define("GRADUATE", "Graduate");
	define("WITHDRAWN", "Withdrawn");

    define("START_DATE_TYPE", "Start Date Type");
    define("CONTRACT_START_DATE", "Contract Start Date");
    define("FIRST_TERM_DATE", "First Term Date");

    define("END_DATE_TYPE", "End Date Type");
    define("CONTRACT_END_DATE", "Contract End Date");
    define("STUDENT_STATUS_END_DATE", "Student Status End Date");

    define("ORIGINAL_SCH_GRAD_DATE_TYPE", "Original Scheduled to Graduate Date Type");
    define("ORIGINAL_EXPECTED_GRAD_DATE", "Original Expected Grad Date");

    define("PLACEMENT_STATUS", "Placement Status");
	define("ELIGIBLE_PLACEMENT", "Eligible for Placement");
    define("INELIGIBLE_PLACEMENT", "Ineligible for Placement");
    define("PLACED", "Placed");

    define("LICENSURE", "Licensure");
    define("LICENSURE_EXAM", "Licensure Exam (Student Event Type)");
    define("SAT_ALL_PART_EXAM", "Sat for All Parts of Exam (Student Event Status)");
    define("PASSED_EXAM", "Passed Exam (Student Event Status)");

    define("EXEMPTION_TYPE", "Exemption Type");
    define("DROP_REASON", "Drop Reason");
    define("PLACEMENT_STUDENT_STATUS", "Placement Student Status");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("NACCAS_SETUP", "NACCAS Annual Report Setup");

	define("REPORT_OPTIONS", "Report Options");

	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("INCLUDED_PROGRAM", "Included Programs");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Status(es)");
	define("INCLUDED_PLACEMENT_STATUS", "Included Placement Status(es)");
	define("DISPLAY_OPTIONS", "Display Options");
	define("TRANSFER_TYPE", "Transfer Type");
	
	define("DROP_REASONS", "Drop Reason(s)");
	define("STUDENTS_STATUS", "Student Status");
	define("PLACEMENT_STATUS", "Placement Status");
	
	define("GRADUATE", "Graduate");
	define("WITHDRAWN", "Withdrawn");

    define("START_DATE_TYPE", "Start Date Type");
    define("CONTRACT_START_DATE", "Contract Start Date");
    define("FIRST_TERM_DATE", "First Term Date");

    define("END_DATE_TYPE", "End Date Type");
    define("CONTRACT_END_DATE", "Contract End Date");
    define("STUDENT_STATUS_END_DATE", "Student Status End Date");

    define("ORGINAL_SCH_GRAD_DATE_TYPE", "Original Scheduled to Graduate Date Type");
    define("ORIGINAL_EXPECTED_GRAD_DATE", "Original Expected Grad Date");

    define("PLACEMENT_STATUS", "Placement Status");
	define("ELIGIBLE_PLACEMENT", "Eligible for Placement");
    define("INELIGIBLE_PLACEMENT", "Ineligible for Placement");
    define("PLACED", "Placed");

    define("LICENSURE", "Licensure");
    define("LICENSURE_EXAM", "Licensure Exam (Student Event Type)");
    define("SAT_ALL_PART_EXAM", "Sat for All Parts of Exam (Student Event Status)");
    define("PASSED_EXAM", "Passed Exam (Student Event Status)");

    define("EXEMPTION_TYPE", "Exemption Type");
    define("DROP_REASON", "Drop Reason");
    define("PLACEMENT_STUDENT_STATUS", "Placement Student Status");
	
}