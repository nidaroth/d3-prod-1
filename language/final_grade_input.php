<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("STUDENT_GRADE_INPUT_PAGE_TITLE", "Student Grade Input");
	define("PROGRAM", "Program");
	define("TERM_DATE", "Term");
	define("COURSE_OFFERING", "Course Offering");
	define("HOUR", "Hours");
	define("GRADE", "Grade");
	define("INACTIVE", "Inactive Date");
	define("MIDPOINT_GRADE", "Midpoint Grade");
	define("ENROLLMENT", "Enrollment");
	define("COURSE_DATE", "Course Date");
	define("DETAIL", "Detail");
	define("NUMERIC_GRADE", "Numeric Grade");
	define("FINAL_GRADE", "Final Grade");
	define("COURSE", "Course");
	define("SELECT_COURSE_OFFERING_ERROR", "Please Select Course Offering");
	define("GRADE_BOOK", "Grade Book");
	define("FA_UNITS", "FA Units");
	define("BEGIN_DATE", "Begin Date");
	define("END_DATE", "End Date");
	define("RETURN_DATE", "Return Date");
	define("FIRST_TERM", "First Term");
	define("FINAL_NUMERIC_GRADE_1", "Final<br />Numeric Grade");
	define("FINAL_GRADE_IMPORT_TEMPLATE", "Final Grade Import Template");
	define("POST_FINAL", "Post Final");
	define("CURRENT_FINAL_GRADE", "Current Final<br />Grade");
	define("IMPORTED_FINAL_GRADE", "Imported Final<br />Grade");
	define("IMPORTED_FINAL_NUMERIC_GRADE", "Imported Final<br />Numeric Grade");

} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("STUDENT_GRADE_INPUT_PAGE_TITLE", "Student Grade Input");
	define("PROGRAM", "Program");
	define("TERM_DATE", "Term");
	define("COURSE_OFFERING", "Course Offering");
	define("HOUR", "Hours");
	define("GRADE", "Grade");
	define("INACTIVE", "Inactive Date");
	define("MIDPOINT_GRADE", "Midpoint Grade");
	define("ENROLLMENT", "Enrollment");
	define("COURSE_DATE", "Course Date");
	define("DETAIL", "Detail");
	define("NUMERIC_GRADE", "Numeric Grade");
	define("FINAL_GRADE", "Final Grade");
	define("COURSE", "Course");
	define("SELECT_COURSE_OFFERING_ERROR", "Please Select Course Offering");
	define("GRADE_BOOK", "Grade Book");
	define("FA_UNITS", "FA Units");
	define("BEGIN_DATE", "Begin Date");
	define("END_DATE", "End Date");
	define("RETURN_DATE", "Return Date");
	define("FIRST_TERM", "First Term");
	define("FINAL_NUMERIC_GRADE_1", "Final<br />Numeric Grade");
	define("FINAL_GRADE_IMPORT_TEMPLATE", "Final Grade Import Template");
	define("POST_FINAL", "Post Final");
	define("CURRENT_FINAL_GRADE", "Current Final<br />Grade");
	define("IMPORTED_FINAL_GRADE", "Imported Final<br />Grade");
	define("IMPORTED_FINAL_NUMERIC_GRADE", "Imported Final<br />Numeric Grade");

}
