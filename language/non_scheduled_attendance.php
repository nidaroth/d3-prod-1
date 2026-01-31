<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("NON_SCHEDULED_ATTENDANCE_PAGE_TITLE", "Non-Scheduled Attendance");
	define("COURSE_OFFERING", "Course Offering");
	define("CLASS_DATE", "Class Date");
	define("STUDENTS", "Student");
	define("ATTENDANCE_HOURS", "Attended Hours");
	define("ATTENDANCE_CODE", "Attendance Code");
	define("SCHEDULED_HOUR", "Scheduled Hours");
	define("COURSE_CODE", "Course Code");
	define("SESSION", "Session");
	define("SESSION_NO", "Session #");
	define("SCHEDULED_CLASS_DATE", "Scheduled Class Date");
	define("CLASS_START_TIME", "Class Start Time");
	define("CLASS_END_TIME", "Class End Time");
	define("STUDENT_ID", "Student ID");
	define("COURSE_OFFERING", "Course Offering");
	define("CLASS_DATE", "Class Date");
	define("CLASS_HOUR", "Class Hour");
	define("START_TIME", "Start Time");
	define("END_TIME", "End Time");
	define("ROOM_NO", "Room #");
	define("INSTRUCTOR", "Instructor");
	define("TERM_START", "Term Start");
	define("ATTENDANCE", "Attendance");
	define("ADD_STUDENT", "Select Student");
	define("TERM", "Term");
	define("ACTIVITY_TYPE", "Activity Type");
	define("SCH_HOUR", "Sch. Hours");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("NON_SCHEDULED_ATTENDANCE_PAGE_TITLE", "Non-Scheduled Attendance");
	define("COURSE_OFFERING", "Course Offering");
	define("CLASS_DATE", "Class Date");
	define("STUDENTS", "Student");
	define("ATTENDANCE_HOURS", "Attended Hours");
	define("ATTENDANCE_CODE", "Attendance Code");
	define("SCHEDULED_HOUR", "Scheduled Hours");
	define("COURSE_CODE", "Course Code");
	define("SESSION", "Session");
	define("SESSION_NO", "Session #");
	define("SCHEDULED_CLASS_DATE", "Scheduled Class Date");
	define("CLASS_START_TIME", "Class Start Time");
	define("CLASS_END_TIME", "Class End Time");
	define("STUDENT_ID", "Student ID");
	define("COURSE_OFFERING", "Course Offering");
	define("CLASS_DATE", "Class Date");
	define("CLASS_HOUR", "Class Hour");
	define("START_TIME", "Start Time");
	define("END_TIME", "End Time");
	define("ROOM_NO", "Room #");
	define("INSTRUCTOR", "Instructor");
	define("TERM_START", "Term Start");
	define("ATTENDANCE", "Attendance");
	define("ADD_STUDENT", "Select Student");
	define("TERM", "Term");
}