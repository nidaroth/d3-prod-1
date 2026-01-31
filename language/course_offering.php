<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	
	define("COURSE_OFFERING_PAGE_TITLE", "Course Offering");
	define("COURSE_OFFERING_SCHEDULE_PAGE_TITLE", "Course Offering Schedule");
	define("TAB_SETTINGS", "Settings");
	define("TAB_SCHEDULE", "Schedule");
	define("TAB_STUDENTS", "Students");
	define("TAB_GRADE", "Grade Book Setup");
	define("TAB_GRADE_INPUT", "Grade Book Entry");
	define("TAB_NON_SCHEDULED_ATTENDANCE", "Non-Scheduled Attendance");
	define("TAB_WAITING_LIST", "Waiting List");
	define("TAB_FINAL_GRADE", "Final Grade");
	define("COURSE_CODE", "Course");
	define("TERM", "Term");
	define("CAMPUS", "Campus");
	define("INSTRUCTOR", "Instructor");
	define("ASSISTANT", "Secondary Instructor");
	define("ROOM", "Room");
	define("CLASS_SIZE", "Max Class Size");
	define("SESSION", "Session");
	define("SESSION_NO", "Session No");
	define("ATTENDENCE_TYPE", "Attendance Type");
	define("ATTENDENCE_CODE", "Default Attendance Code");
	define("FROM_DATE", "From Date");
	define("TO_DATE", "To Date");
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("START_TIME", "Start Time");
	define("END_TIME", "End Time");
	define("HOUR", "Hours");
	define("DATES", "Date");
	define("DEFAULT_TIMES", "Default Times");
	define("APPLY_DEFAULT", "Apply Default");
	define("RESET_BLANK", "Reset Blank");
	define("SUNDAY", "Sunday");
	define("MONDAY", "Monday");
	define("TUESDAY", "Tuesday");
	define("WEDNESDAY", "Wednesday");
	define("THURSDAY", "Thursday");
	define("FRIDAY", "Friday");
	define("SATURDAY", "Saturday");
	define("SCHEDULE_ON_HOLIDAY", "Schedule on Holidays");
	define("OVERWRITE_SCHEDULE_DATE", "Overwrite Scheduled Dates");
	define("BUILD_SCHEDULE", "Build Schedule");
	define("SCHEDULE", "Schedule");
	define("COMPLETED", "Complete");
	define("COURSE_HOURS", "Course Hours");
	define("TOTAL_SCHEDULED_HOURS", "Total Scheduled Hours");
	define("TOTAL_SCHEDULED_HOURS_EXCEEDS_ERROR", "Total Scheduled Hours is greater than the Course Hours. Please review");
	define("STUDENT", "Student");
	define("GROUP_CODE", "Student Group");
	define("FIRST_TERM", "First Term");
	define("PROGRAM", "Program");
	define("STATUS", "Status");
	define("OPTIONS", "Options");
	define("ADD_STUDENTS", "Add Students");
	define("SELECT", "Select");
	define("Day", "Day");
	define("OVERWRITE_STUDENT_SCHEDULE", "Overwrite Students Schedule");
	define("ADD_GRADE", "Add Grade");
	define("IMPORT_GRADE", "Import Default");
	define("CODE", "Code");
	define("PERIOD", "Period");
	define("POINTS", "Points");
	define("WEIGHT", "Weight");
	define("WEIGHTED_POINTS", "Weighted Points");
	define("DELETE_UNCOMP_SCHEDULE", "Delete Incomplete Scheduled Days");
	define("CLASS_DATE", "Class Date");
	define("ATTENDED_HOUR", "Attended Hours");
	define("ATTENDED_CODE", "Attendance Code");
	define("COMPLETED_1", "Completed");
	define("FINAL_GRADE", "Final Grade");
	define("FINAL_TOTAL", "Final Total");
	define("CURRENT_TOTAL", "Current Total");
	define("POST_GRADE", "Post Grade");
	define("SCHEDULED_CLASS_DATE", "Scheduled Class Date");
	define("DELETE_STUDENT_FROM_WAITING_LIST", "Are you sure you want to delete this Student from Waiting List");
	define("ADD_STUDENT_TO_COURSE_OFFERING_CONFIRMATION_MSG", "'Are you sure you want to add this Student to the Course?");
	define("ENROLLMENT", "Enrollment");
	define("NO_STUDENTS_IN_WAITING_LIST", "Number of Student(s) in Waiting List");
	define("ADDED_DATE", "Added Date");
	define("ADD_TO_COURSE", "Add to Course");
	define("NO_OF_STUDENTS", "No. of Student(s)");
	define("POST_FINAL_TOTAL", "Post Final Total");
	define("POST_CURRENT_TOTAL", "Post Current Total");
	define("DELETE_ALL_GRADE_FOR_THIS_COURSE", "Delete ALL Grades for this Course");
	define("DELETE_ALL_ATTENDANCE_FOR_THIS_COURSE", "Delete ALL Attendance for this Course");
	define("REMOVE_ALL_STUDENT_FROM_THIS_COURSE", "Remove ALL Students from this Course");
	define("DELETE_WARNING", "WARNING: Deleted records cannot be restored");
	define("DELETE_THE_COURSE_OFFERING", "Delete the Course Offering");
	define("ADD_SCHEDULE", "Add Schedule");
	define("SAVE", "Save");
	define("GRADE", "Grade");
	define("INACTIVE", "Inactive Date");
	define("MIDPOINT_GRADE", "Midpoint Grade");
	define("BEGIN_DATE", "Begin Date");
	define("NUMERIC_GRADE", "Numeric Grade");
	define("OLD_DIAMOND_ID", "Old Diamond ID");
	define("COURSE_OFFERING_STATUS", "Status");
	define("LMS_ACTIVE", "LMS Active");
	define("EXTERNAL_ID", "External ID");
	define("INACTIVE_CONFIRMATION_MSG", "This will set all future attendance that is not completed to 'I'.<br />Do you want to continue? ");
	define("LMS_CODE", "LMS Code");
	define("MNU_SEND_COURSE_OFFERING_RESULT", "Send Course Offering Result");
	define("MNU_IMPORT_GRADE_RESULT", "Import Grade Result");
	define("IMPORT_SCHEDULE", "Import Schedule");
	define("EXCLUDE_TRANSFERS_COURSE", "Exclude Transfer Courses");
	define("DEFAULT_ACTIVITY_TYPE", "Default Activity Type");
	define("ACTIVITY_TYPE", "Activity Type");
	define("ASSIGN_INSTRUCTOR", "Assign Instructor");
	define("COURSE_OFFERING_STATUS_1", "Course Offering Status");
	define("TOOL_BUILD_GRADE_BOOKS", "Tool: Build Grade Books");
	define("SELECT_UPDATE_TYPE", "Select Update Type");
	define("SELECT_VALUE", "Select Value");
	define("UNASSIGNED", "Unassigned");
	define("RETURN_DATE", "Return Date");
	define("REPORT_OPTION", "Report Option");
	define("COURSE_OFFERING_ROSTER", "Course Offering Roster");
	define("COURSE_TERM", "Course Term");
	define("ROOM_SIZE", "Max Room Size");
	define("CLASS_SIZE_ERROR", "The class size limit will be exceeded with this addition");
	define("CLASS_SIZE", "Class Size");
	define("MAX_CLASS_SIZE", "Maximum Class Size");
	define("CURRENT_CLASS_SIZE", "Current Class Size");
	define("ADD_TO_WAITING_LIST", "Add to Waiting List");
	define("ADDED_TO_WAITING_LIST", "Added to Waiting List");
	define("LMS_COURSE_TEMPLATE_ID", "LMS Course Template");
	define("COURSE_REPORT_TYPE", "Course Report Type");
	define("CHANGE_TERM", "Term change will affect all underlying data for the Course Offering. Do you want to Proceed?");
	define("MAX_ROOM_SIZE", "Max Room Size");
	define("EXCLUDE_INACTIVE_ATT_CODE", "Exclude Inactive Attendance Code");
	define("COPY_BY_TERM", "Copy By Term");
	
	define("CUSTOM_START_END_TIME", "Custom Start & End Time");
	define("USE_DEFAULT_SCHEDULE", "Use Default Schedule");
	define("SELECTED_COUNT", "Selected Count");
	define("SESSION_NUMBER", "Session Number");
	define("TERM_TO_COPY_FROM", "Term To Copy From");
	define("TERM_TO_COPY_TO", "Term To Copy To");
	define("PREVIOUS_SIS_COURSE_OFFERING_ID", "Previous SIS Course Offering ID/No");
	
	define("DELETE_MESSAGE_STUDENT", "Are you sure you want to Delete this Course Offering?<br /><br /><span style=\'color:red\'>All associated Final Grades, Grade Book items and Attendance will also be deleted and cannot be restored.</span>");
	
	define("COURSE_OFFERING_STUDENT_STATUS", "Course Offering<br />Student Status");
	
	define("TRANSCRIPT_CODE", "Transcript Code");
	define("ROOM_NO", "Room No");
	define("SESSION", "Session");
	define("CLASS_SIZE_2", "Max<br />Class<br />Size");
	define("ROOM_SIZE_2", "Max<br />Room<br />Size");
	define("LMS_ACTIVE_1", "LMS<br />Active");
	
	define("COURSE_CODE_1", "Course Code");
	define("SESSION_NO_1", "Session<br />Number");
	define("ROOM_NO_1", "Room");
	define("LMS_CODE_1", "LMS<br />Code");
	define("CAMPUS_CODE", "Campus Code");
	define("COURSE_CAMPUS", "Course Campus");
	define("FIRST_TERM_1", "First Term");
	define("CLEAR_FILTER", "Clear Filter");
	define("MATCH_ON", "Match On");
	define("GRADE_BOOK_IMPORT_TEMPLATE", "Grade Book Import Template");
	define("COURSE_OFFERING_TEMPLATE", "Course Offering Template");
	define("EXTERNAL_ID_TEMPLATE", "External ID Template");
	define("EXCLUDE_FIRST_ROW", "Exclude First Row");
	define("GRADE_BOOK_CODE", "Grade Book Code");
	define("GRADE_BOOK_CODE_DESCRIPTION", "Grade Book Code Description");
	define("BADGE_ID", "Badge ID");
	
	define("DELETE_SELECTED_RECORDS", "Delete Selected Records");
	define("IMPORTED_COUNT", "Imported Count");
	define("FILE_NAME", "File Name");
	define("UPLOADED_BY", "Uploaded By");
	define("UPLOADED_ON", "Uploaded On");
	define("INACTIVE_REMOVE_CONFIRMATION_MSG", "This will set all future attendance that is not completed to {Default_Code} as shown on the Settings tab.<br /><br />Do you want to continue? ");
	define("GO_TO_IMPORT", "Go To Import");
	define("NO_OF_STUDENT", "No. Of Students");
	define("FINAL_NUMERIC_GRADE", "Final Numeric Grade");
	define("FINAL_NUMERIC_GRADE_1", "Final<br />Numeric Grade");
	define("INCLUDE_ATTENDANCE_COMMENTS", "Include Attendance Comments");
	
	define("GRADE_BOOK_IMPORT", "Grade Book Import");
	define("POST_FINAL_GRADE_MSG", "This will post the Final Grade for the associated Course Offering(s).<br /><br />'<span style='color:red' >Once 'Proceed is clicked this process cannot be undone.</span> ");
	define("SAVE_TO_GRADE_BOOK", "Save To Grade Book");
	define("CURRENT_ENROLLMENT_ONLY", "Show Courses for Student's Current Enrollment Only");
	define("RESTORE_GRADE_BOOK_SETUP", "Restore Grade Book Setup"); //27 June
	define("RESTORE_GRADE_BOOK_ENTRY", "Restore Grade Book Entry"); //27 June
	define("RESTORE_GRADE_BOOK_SETUP_LABEL", "Restore Grade Book Setup"); //27 June
	define("RESTORE_GRADE_BOOK_ENTRY_LABEL", "Restore Grade Book Entry"); //27 June
	define("RESTORE_FINAL_GRADE", "Restore Final Grade"); // DIAM-785 -->
	define("RESTORE_FINAL_GRADE_LABEL", "Restore Final Grade"); // DIAM-785 -->
	define("DELETE_MESSAGE_GRADE", "If you delete this Grade Book, Related student grades will also get removed.");
	define("COURSE_OFFERING_CAMPUS", "Course Offering Campus"); // DIAM-1313 -->
	define("COURSE_OFFERING_TERM", "Course Offering Term"); // DIAM-1313 -->
	define("COURSE_TERM_START_DATE", "Course Term Start Date"); // DIAM-1199 LDA -->
	define("COURSE_TERM_END_DATE", "Course Term End Date"); // DIAM-1199 LDA -->

} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("COURSE_OFFERING_PAGE_TITLE", "Course Offering");
	define("COURSE_OFFERING_SCHEDULE_PAGE_TITLE", "Course Offering Schedule");
	define("TAB_SETTINGS", "Settings");
	define("TAB_SCHEDULE", "Schedule");
	define("TAB_STUDENTS", "Students");
	define("TAB_GRADE", "Grade Book Setup");
	define("TAB_GRADE_INPUT", "Grade Book Entry");
	define("TAB_NON_SCHEDULED_ATTENDANCE", "Non-Scheduled Attendance");
	define("TAB_WAITING_LIST", "Waiting List");
	define("TAB_FINAL_GRADE", "Final Grade");
	define("COURSE_CODE", "Course");
	define("TERM", "Term");
	define("CAMPUS", "Campus");
	define("INSTRUCTOR", "Instructor");
	define("ASSISTANT", "Secondary Instructor");
	define("ROOM", "Room");
	define("CLASS_SIZE", "Max Class Size");
	define("SESSION", "Session");
	define("SESSION_NO", "Session No");
	define("ATTENDENCE_TYPE", "Attendance Type");
	define("ATTENDENCE_CODE", "Default Attendance Code");
	define("FROM_DATE", "From Date");
	define("TO_DATE", "To Date");
	define("START_DATE", "Start Date");
	define("END_DATE", "End Date");
	define("START_TIME", "Start Time");
	define("END_TIME", "End Time");
	define("HOUR", "Hours");
	define("DATES", "Date");
	define("DEFAULT_TIMES", "Default Times");
	define("APPLY_DEFAULT", "Apply Default");
	define("RESET_BLANK", "Reset Blank");
	define("SUNDAY", "Sunday");
	define("MONDAY", "Monday");
	define("TUESDAY", "Tuesday");
	define("WEDNESDAY", "Wednesday");
	define("THURSDAY", "Thursday");
	define("FRIDAY", "Friday");
	define("SATURDAY", "Saturday");
	define("SCHEDULE_ON_HOLIDAY", "Schedule on Holidays");
	define("OVERWRITE_SCHEDULE_DATE", "Overwrite Scheduled Dates");
	define("BUILD_SCHEDULE", "Build Schedule");
	define("SCHEDULE", "Schedule");
	define("COMPLETED", "Complete");
	define("COURSE_HOURS", "Course Hours");
	define("TOTAL_SCHEDULED_HOURS", "Total Scheduled Hours");
	define("TOTAL_SCHEDULED_HOURS_EXCEEDS_ERROR", "Total Scheduled Hours is greater than the Course Hours. Please review");
	define("STUDENT", "Student");
	define("GROUP_CODE", "Student Group");
	define("FIRST_TERM", "First Term");
	define("PROGRAM", "Program");
	define("STATUS", "Status");
	define("OPTIONS", "Options");
	define("ADD_STUDENTS", "Add Students");
	define("SELECT", "Select");
	define("Day", "Day");
	define("OVERWRITE_STUDENT_SCHEDULE", "Overwrite Students Schedule");
	define("ADD_GRADE", "Add Grade");
	define("IMPORT_GRADE", "Import Default");
	define("CODE", "Code");
	define("PERIOD", "Period");
	define("POINTS", "Points");
	define("WEIGHT", "Weight");
	define("WEIGHTED_POINTS", "Weighted Points");
	define("DELETE_UNCOMP_SCHEDULE", "Delete Incomplete Scheduled Days");
	define("CLASS_DATE", "Class Date");
	define("ATTENDED_HOUR", "Attended Hours");
	define("ATTENDED_CODE", "Attendance Code");
	define("COMPLETED_1", "Completed");
	define("FINAL_GRADE", "Final Grade");
	define("FINAL_TOTAL", "Final Total");
	define("CURRENT_TOTAL", "Current Total");
	define("POST_GRADE", "Post Grade");
	define("SCHEDULED_CLASS_DATE", "Scheduled Class Date");
	define("DELETE_STUDENT_FROM_WAITING_LIST", "Are you sure you want to delete this Student from Waiting List");
	define("ADD_STUDENT_TO_COURSE_OFFERING_CONFIRMATION_MSG", "'Are you sure you want to add this Student to the Course?");
	define("ENROLLMENT", "Enrollment");
	define("NO_STUDENTS_IN_WAITING_LIST", "Number of Student(s) in Waiting List");
	define("ADDED_DATE", "Added Date");
	define("ADD_TO_COURSE", "Add to Course");
	define("NO_OF_STUDENTS", "No. of Student(s)");
	define("POST_FINAL_TOTAL", "Post Final Total");
	define("POST_CURRENT_TOTAL", "Post Current Total");
	define("DELETE_ALL_GRADE_FOR_THIS_COURSE", "Delete ALL Grades for this Course");
	define("DELETE_ALL_ATTENDANCE_FOR_THIS_COURSE", "Delete ALL Attendance for this Course");
	define("REMOVE_ALL_STUDENT_FROM_THIS_COURSE", "Remove ALL Students from this Course");
	define("DELETE_WARNING", "WARNING: Deleted records cannot be restored");
	define("DELETE_THE_COURSE_OFFERING", "Delete the Course Offering");
	define("ADD_SCHEDULE", "Add Schedule");
	define("SAVE", "Save");
	define("GRADE", "Grade");
	define("INACTIVE", "Inactive Date");
	define("MIDPOINT_GRADE", "Midpoint Grade");
	define("BEGIN_DATE", "Begin Date");
	define("NUMERIC_GRADE", "Numeric Grade");
	define("OLD_DIAMOND_ID", "Old Diamond ID");
	define("COURSE_OFFERING_STATUS", "Status");
	define("LMS_ACTIVE", "LMS Active");
	define("EXTERNAL_ID", "External ID");
	define("INACTIVE_CONFIRMATION_MSG", "This will set all future attendance that is not completed to 'I'.<br />Do you want to continue? ");
	define("LMS_CODE", "LMS Code");
	define("MNU_SEND_COURSE_OFFERING_RESULT", "Send Course Offering Result");
	define("MNU_IMPORT_GRADE_RESULT", "Import Grade Result");
	define("IMPORT_SCHEDULE", "Import Schedule");
	define("EXCLUDE_TRANSFERS_COURSE", "Exclude Transfer Courses");
	define("DEFAULT_ACTIVITY_TYPE", "Default Activity Type");
	define("ACTIVITY_TYPE", "Activity Type");
	define("ASSIGN_INSTRUCTOR", "Assign Instructor");
	define("COURSE_OFFERING_STATUS_1", "Course Offering Status");
	define("TOOL_BUILD_GRADE_BOOKS", "Tool: Build Grade Books");
	define("SELECT_UPDATE_TYPE", "Select Update Type");
	define("SELECT_VALUE", "Select Value");
	define("UNASSIGNED", "Unassigned");
	define("RETURN_DATE", "Return Date");
	define("REPORT_OPTION", "Report Option");
	define("COURSE_OFFERING_ROSTER", "Course Offering Roster");
	define("COURSE_TERM", "Course Term");
	define("ROOM_SIZE", "Max Room Size");
	define("CLASS_SIZE_ERROR", "The class size limit will be exceeded with this addition");
	define("CLASS_SIZE", "Class Size");
	define("MAX_CLASS_SIZE", "Maximum Class Size");
	define("CURRENT_CLASS_SIZE", "Current Class Size");
	define("ADD_TO_WAITING_LIST", "Add to Waiting List");
	define("ADDED_TO_WAITING_LIST", "Added to Waiting List");
	define("LMS_COURSE_TEMPLATE_ID", "LMS Course Template");
	define("COURSE_REPORT_TYPE", "Course Report Type");
	define("CHANGE_TERM", "Term change will affect all underlying data for the Course Offering. Do you want to Proceed?");
	define("MAX_ROOM_SIZE", "Max Room Size");
	define("EXCLUDE_INACTIVE_ATT_CODE", "Exclude Inactive Attendance Code");
	define("COPY_BY_TERM", "Copy By Term");
	
	define("CUSTOM_START_END_TIME", "Custom Start & End Time");
	define("USE_DEFAULT_SCHEDULE", "Use Default Schedule");
	define("SELECTED_COUNT", "Selected Count");
	define("SESSION_NUMBER", "Session Number");
	define("TERM_TO_COPY_FROM", "Term To Copy From");
	define("TERM_TO_COPY_TO", "Term To Copy To");
	define("PREVIOUS_SIS_COURSE_OFFERING_ID", "Previous SIS Course Offering ID/No");
	
	define("DELETE_MESSAGE_STUDENT", "Are you sure you want to Delete this Course Offering?<br /><br /><span style=\'color:red\'>All associated Final Grades, Grade Book items and Attendance will also be deleted and cannot be restored.</span>");
	
	define("COURSE_OFFERING_STUDENT_STATUS", "Course Offering<br />Student Status");
	
	define("TRANSCRIPT_CODE", "Transcript Code");
	define("ROOM_NO", "Room No");
	define("SESSION", "Session");
	define("CLASS_SIZE_2", "Max<br />Class<br />Size");
	define("ROOM_SIZE_2", "Max<br />Room<br />Size");
	define("LMS_ACTIVE_1", "LMS<br />Active");
	
	define("COURSE_CODE_1", "Course Code");
	define("SESSION_NO_1", "Session<br />Number");
	define("ROOM_NO_1", "Room");
	define("LMS_CODE_1", "LMS<br />Code");
	define("CAMPUS_CODE", "Campus Code");
	define("COURSE_CAMPUS", "Course Campus");
	define("FIRST_TERM_1", "First Term");
	define("CLEAR_FILTER", "Clear Filter");
	define("MATCH_ON", "Match On");
	define("GRADE_BOOK_IMPORT_TEMPLATE", "Grade Book Import Template");
	define("COURSE_OFFERING_TEMPLATE", "Course Offering Template");
	define("EXTERNAL_ID_TEMPLATE", "External ID Template");
	define("EXCLUDE_FIRST_ROW", "Exclude First Row");
	define("GRADE_BOOK_CODE", "Grade Book Code");
	define("GRADE_BOOK_CODE_DESCRIPTION", "Grade Book Code Description");
	define("BADGE_ID", "Badge ID");
	
	define("DELETE_SELECTED_RECORDS", "Delete Selected Records");
	define("IMPORTED_COUNT", "Imported Count");
	define("FILE_NAME", "File Name");
	define("UPLOADED_BY", "Uploaded By");
	define("UPLOADED_ON", "Uploaded On");
	define("INACTIVE_REMOVE_CONFIRMATION_MSG", "This will set all future attendance that is not completed to {Default_Code} as shown on the Settings tab.<br /><br />Do you want to continue? ");
	define("GO_TO_IMPORT", "Go To Import");
	define("NO_OF_STUDENT", "No. Of Students");
	define("FINAL_NUMERIC_GRADE", "Final Numeric Grade");
	define("FINAL_NUMERIC_GRADE_1", "Final<br />Numeric Grade");
	define("INCLUDE_ATTENDANCE_COMMENTS", "Include Attendance Comments");
	
	define("GRADE_BOOK_IMPORT", "Grade Book Import");
	define("POST_FINAL_GRADE_MSG", "This will post the Final Grade for the associated Course Offering(s).<br /><br />'<span style='color:red' >Once 'Proceed is clicked this process cannot be undone.</span> ");
	define("SAVE_TO_GRADE_BOOK", "Save To Grade Book");
	define("CURRENT_ENROLLMENT_ONLY", "Show Courses for Student's Current Enrollment Only");
	define("RESTORE_GRADE_BOOK_SETUP", "Restore Grade Book Setup"); //27 June
	define("RESTORE_GRADE_BOOK_ENTRY", "Restore Grade Book Entry"); //27 June
	define("RESTORE_GRADE_BOOK_SETUP_LABEL", "Restore Grade Book Setup"); //27 June
	define("RESTORE_GRADE_BOOK_ENTRY_LABEL", "Restore Grade Book Entry"); //27 June
	define("RESTORE_FINAL_GRADE", "Restore Final Grade"); // DIAM-785 -->
	define("RESTORE_FINAL_GRADE_LABEL", "Restore Final Grade"); // DIAM-785 -->
	define("DELETE_MESSAGE_GRADE", "If you delete this Grade Book, Related student grades will also get removed.");
	define("COURSE_OFFERING_CAMPUS", "Course Offering Campus"); // DIAM-1313 -->
	define("COURSE_OFFERING_TERM", "Course Offering Term"); // DIAM-1313 -->
	define("COURSE_TERM_START_DATE", "Course Term Start Date"); // DIAM-1199 LDA -->
	define("COURSE_TERM_END_DATE", "Course Term End Date"); // DIAM-1199 LDA -->

}
