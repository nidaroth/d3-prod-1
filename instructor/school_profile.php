<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("SCHOOL_PAGE_TITLE", "School Setup", true);
	define("TAB_GENERAL", "General", true);
	define("TAB_CAMPUS", "Campus", true);
	define("TAB_ADMIN", "Admin", true);
	define("TAB_CONTACT", "Contacts", true);
	define("TAB_OTHER", "Other", true);
	define("TAB_REPORTS", "Report Settings", true);
	define("SCHOOL_NAME", "School Name", true);
	define("SCHOOL_CODE", "School Code", true);
	define("CONTACT_TYPE", "Contact Type", true);
	define("CAMPUS", "Campus", true);
	define("USER", "User", true);
	define("CONTACT", "Contact", true);
	define("SCHOOL_USER", "School User", true);
	define("TIMEZONE", "Timezone", true);
	
	define("SHOW_DSIS", "Share With DSIS", true);
	define("ALERT_DUPLICATE_SSN", "Alert Duplicate SSN", true);
	define("EMPLOYEE_LABEL", "Employee Label", true);
	define("API_KEY", "API Key", true);
	define("GENERATE_API_KEY", "Generate API Key", true);
	define("GENERATE_API_KEY_CONFIRM_MSG", "Are you sure, you want Regenerate the API Key?", true);
	define("AUTO_GENERATE_STUD_ID", "Auto Generate Student ID", true);
	define("STUD_CODE", "Student Code", true);
	define("STUD_NEXT_NO", "Student Next #", true);
	define("SEND_BIRTHDAY_NOTIFICATION", "Send Birthday Notification", true);
	define("SEND_COURSE_START_NOTIFICATION", "Send Course Start Notification", true);
	define("EMAIL_TEMPLATE", "Email Template", true);
	define("TEXT_TEMPLATE", "Text Template", true);
	define("SEND_BIRTHDAY_NOTIFICATION_BEFORE_DAYS", "Send Birthday Notification Before Days", true);
	define("SEND_COURSE_START_NOTIFICATION_BEFORE_DAYS", "Send Course Start Notification Before Days", true);
	define("DUP_STUDENT_NO", "Cannot use this Number as this will create duplicate Student ID in future", true);
	define("STU_DEFAULT_PASSWORD", "Student Default Password", true);
	define("NEW_LEAD_STATUS", "New Lead Status", true);
	define("QUALIFIED_LEAD_STATUS", "Qualified Lead Status", true);
	define("NEW_APPLICATIONS_STATUS", "New Applications Status", true);
	define("NEW_STUDENTS_STATUS", "New Student Status", true);
	define("SEND_ENROLLED_IN_CLASS", "Send Enrolled In Class Notification", true);
	define("SEND_FINAL_GRADE_POSTED", "Send Final Grade Posted", true);
	define("SEND_STUDENT_PORTAL_ACCOUNT_CREATED", "Send Student Portal Account Created", true);
	define("SEND_INSTRUCTOR_PORTAL_ACCOUNT_CREATED", "Send Instructor Portal Account Created", true);
	define("SEND_PAYMENT_RECEIPT", "Send Payment Receipt", true);
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("SCHOOL_PAGE_TITLE", "Colegio", true);
	define("TAB_GENERAL", "General", true);
	define("TAB_CAMPUS", "Instalaciones", true);
	define("TAB_ADMIN", "Admin", true);
	define("TAB_CONTACT", "Contacts", true);
	define("TAB_OTHER", "Other", true);
	define("TAB_REPORTS", "Report Settings", true);
	define("SCHOOL_NAME", "Colegio Name", true);
	define("SCHOOL_CODE", "Colegio Code", true);
	define("CONTACT_TYPE", "contacto Type", true);
	define("CAMPUS", "Instalaciones", true);
	define("USER", "User", true);
	define("CONTACT", "Contacto", true);
	define("SCHOOL_USER", "Colegio User", true);
	define("TIMEZONE", "Timezone", true);
	
	define("SHOW_DSIS", "Compartir con DSIS", true);
	define("ALERT_DUPLICATE_SSN", "Alert Duplicate SSN", true);
	define("EMPLOYEE_LABEL", "Employee Label", true);
	define("API_KEY", "API Key", true);
	define("GENERATE_API_KEY", "Generate API Key", true);
	define("GENERATE_API_KEY_CONFIRM_MSG", "Are you sure, you want Regenerate the API Key?", true);
	define("AUTO_GENERATE_STUD_ID", "Auto Generate Student ID", true);
	define("STUD_CODE", "Student Code", true);
	define("STUD_NEXT_NO", "Student Next #", true);
	define("SEND_BIRTHDAY_NOTIFICATION", "Send Birthday Notification", true);
	define("SEND_COURSE_START_NOTIFICATION", "Send Course Start Notification", true);
	define("EMAIL_TEMPLATE", "Email Template", true);
	define("TEXT_TEMPLATE", "Text Template", true);
	define("SEND_BIRTHDAY_NOTIFICATION_BEFORE_DAYS", "Send Birthday Notification Before Days", true);
	define("SEND_COURSE_START_NOTIFICATION_BEFORE_DAYS", "Send Course Start Notification Before Days", true);
	define("STU_DEFAULT_PASSWORD", "Student Default Password", true);
	define("DUP_STUDENT_NO", "Cannot use this Number as this will create duplicate Student ID in future", true);
	define("NEW_LEAD_STATUS", "New Lead Status", true);
	define("QUALIFIED_LEAD_STATUS", "Qualified Lead Status", true);
	define("NEW_APPLICATIONS_STATUS", "New Applications Status", true);
	define("NEW_STUDENTS_STATUS", "New Student Status", true);
	define("SEND_ENROLLED_IN_CLASS", "Send Enrolled In Class Notification", true);
	define("SEND_FINAL_GRADE_POSTED", "Send Final Grade Posted", true);
	define("SEND_STUDENT_PORTAL_ACCOUNT_CREATED", "Send Student Portal Account Created", true);
	define("SEND_INSTRUCTOR_PORTAL_ACCOUNT_CREATED", "Send Instructor Portal Account Created", true);
	define("SEND_PAYMENT_RECEIPT", "Send Payment Receipt", true);
}
?>