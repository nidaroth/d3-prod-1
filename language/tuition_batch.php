<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("TUITION_BATCH_PAGE_TITLE", "Tuition Batch");
	define("BATCH_NO", "Batch #");
	define("TRANS_DATE", "Transaction Date");
	define("TYPE", "Batch Type");
	define("TERM_MASTER", "Course Term");
	define("STUD_PROGRAM", "Student Program"); //DIAM-786
	define("STUDENT_TYPE", "Student Type");
	define("PROGRAM", "Program");
	define("COURSE", "Course");
	define("SAVE_AS_HOLD", "Save as Hold");
	define("SAVE_AS_POST", "Save as Post");
	define("STATUS", "Status");
	define("ADD_STUDENT", "Add Student");
	define("BUILD_BATCH", "Build Batch");
	define("POST_TO_LEDGER", "Post to Ledger");
	define("OPTION_2", "Option 2");
	define("COURSE_OFFERING", "Course Offering");
	define("WARNING", "Warning");
	define("NO_STUDENT_TO_POST", "There should be at least one Student in the batch");
	define("OPTION_1", "Option 1");
	define("UNPOST", "Unpost");
	define("UNPOST_MESSAGE", "Are you sure you Want to Unpost?");
	define("UNPOSTBATCH", "Unpost Batch");
	define("UNPOST_CONFIRMATION", "Confirmation");
	define("DOWNLOAD_REPORT", "Download Report");
	define("SELECT_CAMPUS_ERROR", "Please Select Campus");
	define("BATCH_AMOUNT", "Batch Amount");
	define("POSTED_DATE", "Posted Date");
	define("BATCH_STATUS", "Batch Status");
	define("BATCH_DATE", "Batch Date");
	define("DEBIT_TOTAL", "Debit Total");
	define("BATCH_TOTAL", "Batch Total");
	define("DUPLICATE_BATCH_COURSE", "Term, Course(s), and Course Offering(s) exist in another Tuition Batch.<br />Do you want to continue? ");
	define("DUPLICATE_BATCH_ESTIMATED_FEE", "Date Range, Program(s), Academic Year and Academic Period exist in another Tuition Batch.<br />Do you want to continue? ");
	
	define("PRIOR_YEAR", "Prior Year");
	define("FIRST_TERM_1", "First Term");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("TUITION_BATCH_PAGE_TITLE", "Tuition Batch");
	define("BATCH_NO", "Batch #");
	define("TRANS_DATE", "Transaction Date");
	define("TYPE", "Batch Type");
	define("TERM_MASTER", "Course Term");
	define("STUD_PROGRAM", "Student Program"); //DIAM-786
	define("STUDENT_TYPE", "Student Type");
	define("PROGRAM", "Program");
	define("COURSE", "Course");
	define("SAVE_AS_HOLD", "Save as Hold");
	define("SAVE_AS_POST", "Save as Post");
	define("STATUS", "Status");
	define("ADD_STUDENT", "Add Student");
	define("BUILD_BATCH", "Build Batch");
	define("POST_TO_LEDGER", "Post to Ledger");
	define("OPTION_2", "Option 2");
	define("COURSE_OFFERING", "Course Offering");
	define("WARNING", "Warning");
	define("NO_STUDENT_TO_POST", "There should be at least one Student in the batch");
	define("OPTION_1", "Option 1");
	define("UNPOST", "Unpost");
	define("UNPOST_MESSAGE", "Are you sure you Want to Unpost?");
	define("UNPOSTBATCH", "Unpost Batch");
	define("UNPOST_CONFIRMATION", "Confirmation");
	define("DOWNLOAD_REPORT", "Download Report");
	define("SELECT_CAMPUS_ERROR", "Please Select Campus");
	define("BATCH_AMOUNT", "Batch Amount");
	define("POSTED_DATE", "Posted Date");
	define("BATCH_STATUS", "Batch Status");
	define("BATCH_DATE", "Batch Date");
	define("DEBIT_TOTAL", "Debit Total");
	define("BATCH_TOTAL", "Batch Total");
	define("DUPLICATE_BATCH_COURSE", "Term, Course(s), and Course Offering(s) exist in another Tuition Batch.<br />Do you want to continue? ");
	define("DUPLICATE_BATCH_ESTIMATED_FEE", "Date Range, Program(s), Academic Year and Academic Period exist in another Tuition Batch.<br />Do you want to continue? ");
	
	define("PRIOR_YEAR", "Prior Year");
	define("FIRST_TERM_1", "First Term");
}
