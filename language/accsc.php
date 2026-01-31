<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ENROLLMENT_START_DATE", "Enrollment Start Date");
	define("ENROLLMENT_END_DATE", "Enrollment End Date");
	define("REPORT_OPTIONS", "Report Options");
	
	define("EXCLUSIONS", "Exclusions");
	define("INCLUSIONS", "Inclusions");
	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Status(es)");
	define("INCLUDED_PLACEMENT_STATUS", "Included Placement Status(es)");
	define("DISPLAY_OPTIONS", "Display Options");
	define("TRANSFER_TYPE", "Transfer Type");
	
	define("DROP_REASONS", "Drop Reason(s)");
	define("STUDENT_STATUS", "Student Status(es)");
	define("PLACEMENT_STATUS", "Placement Status(es)");
	
	define("ENROLLMENT_START_YEAR", "Enrollment Start Year");
	define("ENROLLMENT_START_MONTH", "Enrollment Start Month");
	define("GRADUATED_STUDENT_STATUS", "Graduated Student Status(es)");
	
	define("STUDENT_EVENT_TYPE", "Student Event Type(s)");
	define("STUDENT_EVENT_OTHER", "Student Event Other");
	define("STUDENT_EVENT_STATUS", "Student Event Status(es)");
	
	define("TRANSFER_TO_ANOTHER_PROGRAM_COHORT", "Transfer To Another Program/Cohort");
	define("TRANSFER_FROM_ANOTHER_PROGRAM_COHORT", "Transfer From Another Program/Cohort");
	
	define("UNAVAILABLE_FOR_GRADUATION", "Unavailable For Graduation");
	define("GRADUATES_WITHIN_150", "Graduates Within 150% of Program Length");
	define("WITHDRAW_TERMINATES_STUDENTS", "Withdraw/Terminates Students");
	define("GRADUATES_FURTHER_EDUCATION", "Graduates - Further Education");
	
	define("GRADUATES_UNAVAILABLE_FOR_EMPLOYEMENT", "Graduates - Unavailable for Employement");
	define("GRADUATES_EMPLOYED_IN_FIELD", "Graduates - Employed in Field");
	define("GRADUATES_UNRELATED_OCCUPATIONS", "Graduates - Unrelated Occupations");
	define("GRADUATES_UNEMPLOYED", "Graduates - Unemployed");
	define("GRADUATES_UNKNOWN", "Graduates - Unknown");
	define("NON_GRADUATED_STUDENT", "Non-Graduated Students Who Obtained Traning Related Employment");
	
	define("LICENSURE_TYPE", "Licensure Type");
	define("LICENSURE_EXAM", "Licensure Exam");
	define("TOOK_EXAM", "Took Exam");
	define("FAILED_EXAM", "Failed Exam");
	define("PASSED_EXAM", "Passed Exam");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("ENROLLMENT_START_DATE", "Enrollment Start Date");
	define("ENROLLMENT_END_DATE", "Enrollment End Date");
	define("REPORT_OPTIONS", "Report Options");
	
	define("EXCLUSIONS", "Exclusions");
	define("INCLUSIONS", "Inclusions");
	define("EXCLUDED_PROGRAM", "Excluded Programs");
	define("EXCLUDED_STUDENT_STATUS", "Excluded Student Status(es)");
	define("INCLUDED_PLACEMENT_STATUS", "Included Placement Status(es)");
	define("DISPLAY_OPTIONS", "Display Options");
	define("TRANSFER_TYPE", "Transfer Type");
	
	define("DROP_REASONS", "Drop Reason(s)");
	define("STUDENT_STATUS", "Student Status(es)");
	define("PLACEMENT_STATUS", "Placement Status(es)");
	
	define("ENROLLMENT_START_YEAR", "Enrollment Start Year");
	define("ENROLLMENT_START_MONTH", "Enrollment Start Month");
	define("GRADUATED_STUDENT_STATUS", "Graduated Student Status(es)");
	
	define("STUDENT_EVENT_TYPE", "Student Event Type(s)");
	define("STUDENT_EVENT_OTHER", "Student Event Other");
	define("STUDENT_EVENT_STATUS", "Student Event Status(es)");
	
	define("TRANSFER_TO_ANOTHER_PROGRAM_COHORT", "Transfer To Another Program/Cohort");
	define("TRANSFER_FROM_ANOTHER_PROGRAM_COHORT", "Transfer From Another Program/Cohort");
	
	define("UNAVAILABLE_FOR_GRADUATION", "Unavailable For Graduation");
	define("GRADUATES_WITHIN_150", "Graduates Within 150% of Program Length");
	define("WITHDRAW_TERMINATES_STUDENTS", "Withdraw/Terminates Students");
	define("GRADUATES_FURTHER_EDUCATION", "Graduates - Further Education");
	
	define("GRADUATES_UNAVAILABLE_FOR_EMPLOYEMENT", "Graduates - Unavailable for Employement");
	define("GRADUATES_EMPLOYED_IN_FIELD", "Graduates - Employed in Field");
	define("GRADUATES_UNRELATED_OCCUPATIONS", "Graduates - Unrelated Occupations");
	define("GRADUATES_UNEMPLOYED", "Graduates - Unemployed");
	define("GRADUATES_UNKNOWN", "Graduates - Unknown");
	define("NON_GRADUATED_STUDENT", "Non-Graduated Students Who Obtained Traning Related Employment");
	
	define("LICENSURE_TYPE", "Licensure Type");
	define("LICENSURE_EXAM", "Licensure Exam");
	define("TOOK_EXAM", "Took Exam");
	define("FAILED_EXAM", "Failed Exam");
	define("PASSED_EXAM", "Passed Exam");
}