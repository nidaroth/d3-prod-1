<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("USE_SECONDARY_EMAIL_AS_DEFAULT", "Use Secondary Email As Default", true);

	define("SCHOOL_PAGE_TITLE", "School Setup");
	define("TAB_GENERAL", "General");
	define("TAB_CAMPUS", "Campus");
	define("TAB_ADMIN", "Admin");
	define("TAB_CONTACT", "Contacts");
	define("TAB_OTHER", "Other");
	define("TAB_REPORTS", "Report Settings");
	define("TAB_ACCOUNT_OPTIONS", "Account Options");
	define("TAB_INSTRUCTOR_SETTINGS", "Instructor Portal Settings");
	define("TAB_DIAMOND_PAY_SETTINGS", "Diamond Pay Settings");
	define("TAB_COMMUNICATIONS", "Communications");
	define("TAB_LSQ", "Diamond CRM Powered by LeadSquared");
	
	define("SCHOOL_NAME", "School Name");
	define("SCHOOL_CODE", "School Code");
	define("CONTACT_TYPE", "Contact Type");
	define("CAMPUS", "Campus");
	define("USER", "User");
	define("CONTACT", "Contact");
	define("SCHOOL_USER", "School User");
	define("TIMEZONE", "Timezone");
	
	define("SHOW_DSIS", "Share With DSIS");
	define("ALERT_DUPLICATE_SSN", "Alert Student Duplicate SSN");
	define("EMPLOYEE_LABEL", "Employee Label");
	define("API_KEY", "API Key");
	define("GENERATE_API_KEY", "Generate API Key");
	define("GENERATE_API_KEY_CONFIRM_MSG", "Are you sure, you want Regenerate the API Key?");
	define("AUTO_GENERATE_STUD_ID", "Auto Generate Student ID");
	define("STUD_CODE", "Student Code");
	define("STUD_NEXT_NO", "Student Next #");
	define("SEND_BIRTHDAY_NOTIFICATION", "Send Birthday Alert");
	define("SEND_COURSE_START_NOTIFICATION", "Send Course Start Alert");
	define("EMAIL_TEMPLATE", "Email Template");
	define("TEXT_TEMPLATE", "Text Template");
	define("SEND_BIRTHDAY_NOTIFICATION_BEFORE_DAYS", "Send Birthday Alert Before Days");
	define("SEND_COURSE_START_NOTIFICATION_BEFORE_DAYS", "Send Course Start Alert Before Days");
	define("DUP_STUDENT_NO", "Cannot use this Number as this will create duplicate Student ID in future");
	define("STU_DEFAULT_PASSWORD", "Student Portal Default Password");
	define("EMP_DEFAULT_PASSWORD", "Employee Default Password");
	
	define("NEW_LEAD_STATUS", "New Lead Status");
	define("QUALIFIED_LEAD_STATUS", "Qualified Lead Status");
	define("NEW_APPLICATIONS_STATUS", "New Applications Status");
	define("NEW_STUDENTS_STATUS", "New Student Status");
	define("SEND_ENROLLED_IN_CLASS", "Send Enrolled In Class Alert");
	define("SEND_FINAL_GRADE_POSTED", "Send Final Grade Posted");
	define("SEND_STUDENT_PORTAL_ACCOUNT_CREATED", "Send Student Portal Account Created");
	define("SEND_INSTRUCTOR_PORTAL_ACCOUNT_CREATED", "Send Instructor Portal Account Created");
	define("SEND_PAYMENT_RECEIPT", "Send Payment Receipt");
	define("SEND_PAST_DUE_PAYMENT", "Send Past Due Payment");
	define("SEND_PAYMENT_REMINDER", "Send Payment Reminder");
	define("SEND_PAYMENT_REMINDER_NOTIFICATION_BEFORE_DAYS", "Send Payment Reminder Alert Before Days");
	define("SEND_STATUS_CHANGE", "Send Status Change");
	define("EMPLOYEE", "Employee");
	define("ADD_STATUS", "Add Status");
	define("PDF_LOGO", "PDF Logo");
	define("TWILIO_FROM_NO", "Twilio From Mobile #");
	
	define("ADMISSION_ACTIVE_LEAD", "Admissions - Active Leads");
	define("ADMISSION_APPLICATIONS", "Admissions - Applications");
	define("ADMISSION_ENROLLED", "Admissions - Enrolled");
	define("REGISTRAR_ENROLLED", "Registrar - Enrolled");
	define("REGISTRAR_LOA", "Registrar - LOA");
	define("REGISTRAR_DROPS", "Registrar - Drops");
	define("FINANCE_CURRENT_MONTH", "Accounting - Current Month");
	define("FINANCE_NEXT_MONTH", "Accounting - Next Month");
	define("FINANCE_TITLE_IV", "Accounting - Title IV");
	define("FINANCE_NON_TITLE_IV", "Accounting - Non Title IV");
	define("PLACEMENT_PLACED", "Placement - Placed");
	define("PLACEMENT_PENDING", "Placement - Pending");
	define("PLACEMENT_ACTIVE_SEEKING", "Placement - Active Seeking");
	define("PLACEMENT_INACTIVE", "Placement - Inactive");
	define("CHARGE_PROCESSING_FEE_FROM_STUDENT", "Charge Processing Fees To Student");
	define("TAB_COMMUNICATION", "System Alerts");
	define("TAB_EMP_SETTINGS", "Employee Settings");
	define("TAB_STUD_SETTINGS", "Student Portal Settings");
	define("ENABLE_ATTENDANCE_ACTIVITY_TYPES", "Enable Attendance Activity Types");
	define("ENABLE_ATTENDANCE_COMMENTS", "Enable Attendance Comments");
	
	define("STUDENT_ID_BARCODE_TYPE", "Student ID Barcode");
	define("BADGE_ID", "Badge ID");
	define("STUDENT_ID", "Student ID");

	define("STUDENT_NAME", "Student Name");
	define("ENABLE_PATERNAL_MATERNAL", "Enable Paternal/Maternal Last Name");
	
	define("MFA", "Multi Factor Authentication(MFA)");
	define("_1098T_TAX_FORM", "1098T Tax Form");
	define("_480G_TAX_FORM", "480.7G Tax Form");
	define("STUDENT_PORTAL", "Student Portal");
	
	define("STUDENT_OPTIONS", "Student Options");
	define("EMPLOYEE_OPTIONS", "Employee Options");
	define("PORTAL_OPTIONS", "Portal Options");
	define("INSRUCTOR_OPTIONS", "Instructor Options");
	define("QUICK_PAYMENT_OPTIONS", "Quick Payment Options");
	
	define("STUDENT_ID_PROGRAM_DISPLAY_TYPE", "Student ID Program Display Type");
	define("PROGRAM_CODE", "Program Code");
	define("PROGRAM_DESCRIPTION", "Program Description");
	
	define("STUDENT_PORTAL_OPTIONS", "Student Portal Menu");
	define("ACADEMICS", "Academics");
	define("ACADEMIC_REVIEW", "Academic Review");
	define("ACADEMIC_REVIEW_BY_TERM", "Academic Review By Term");
	define("COSMETOLOGY_GRADE_BOOK_LABS", "Grade Book Labs");
	define("COSMETOLOGY_GRADE_BOOK_SUMMARY", "Grade Book Summary");
	define("COSMETOLOGY_GRADE_BOOK_TEST", "Grade Book Test");
	define("PROGRAM_COURSE_PROGRESS", "Program Course Progress");
	define("ATTENDANCE", "Attendance");
	define("ATTENDANCE_REVIEW", "Attendance Review");
	define("ATTENDANCE_SUMMARY", "Attendance Summary");
	define("FINANCE", "Finance");
	define("FINANCIAL_AID_AWARDS", "Financial Aid Awards");
	define("PAYMENT_SCHEDULE", "Payment Schedule");
	define("STUDENT_LEDGER", "Student Ledger");
	define("SCHEDULE", "Schedule");
	define("GRADE_BOOK", "Grade Book");
	define("GRADE_DISPLAY", "Grade Display");
	
	define("GRADE", "Grade");
	define("NUMERIC_GRADE", "Numeric Grade");
	define("INSTRUCTOR_PORTAL_OPTIONS", "Instructor Portal Menu");
	define("MNU_STUDENTS_1", "Students");
	define("TAB_FORM_POST_SETTINGS", "Form Post Settings");
	define("ITEMS", "Items");
	define("DESTINATION", "Destination");
	define("TYPE_MAX", "Type(Max)");
	define("FORMAT_SOURCE", "Format/Source");
	define("GENERATE_HTML", "Generate HTML");
	define("DISCLAIMER", "DISCLAIMER");
	define("DISCLAIMER_TEXT", "The sample HTML below is for informative purposes only. It is the sole responsibility of the institution (school) to ensure proper security measures are in place when using this code.");
	define("VIEW_LOG", "View Log");
	define("FORM_POST_LOG", "Form Post Log");
	define("NOTIFICATIONS", "Notifications");
	
	define("IP_ACCESS", "IP Access");
	define("FORM_BUILDER", "Form Builder");
	define("IP_ADDRESS", "IP Address");
	define("ADD_IP", "Add IP");
	define("GENERATE_PHP", "Generate PHP");
	define("NEXT_STUD_ID_NO", "Next Student ID #");
	
	define("TAB_LMS_SETTINGS", "LMS Settings");
	define("LMS_USERNAME_OPTIONS", "What field would you like to use to create your LMS usernames?");
	define("LMS_PASSWORD_OPTIONS", "Password creation and user Notification");
	define("CREATE_DEFAULT_PASSWORD_AND_NOTIFY", "Create default password and notify user (Moodle will generate password)");
	define("CREATE_DEFAULT_PASSWORD_AND_DO_NOT_NOTIFY", "Create default password and DO NOT notify user");
	define("DO_NOT_CREATE_DEFAULT_PASSWORD", "DO NOT create default password and DO NOT notify user (Moodle password will be blank)");
	define("FORCE_PASSWORD_RESET", "Force Password Reset");
	define("DEFAULT_PASSWORD_FORMAT", "(Default password = First 2 letters of first name (Upper and lower case), First 2 letters of last name (Upper and lower case), Last 4 digits of the student's Badge ID and A '!' )");
	
	define("SEND_PORTAL_ACCOUNT_CREATED", "Send Login Created?");
	define("ALLOW_INSTRUCTORS_UNPOST", "Allow Instructors to Unpost Attendance");
	define("SEND_ABSENT_ALERT", "Send Absent Alert");
	define("DEFAULT_PASSWORD", "Default Password");
	define("DEFAULT_PASSWORD_FIELD", "Field to use as part of default password");
	
	define("DEFAULT_PASSWORD_FORMAT_BADGE_ID", "(Default password = First 2 letters of first name (Upper and lower case), First 2 letters of last name (Upper and lower case), Last 4 digits of the student's Badge ID and A '!' )");
	define("DEFAULT_PASSWORD_FORMAT_SSN", "(Default password = First 2 letters of first name (Upper and lower case), First 2 letters of last name (Upper and lower case), Last 4 digits of the student's SSN and A '!' )");
	define("DEFAULT_PASSWORD_FORMAT_STUDENT_ID", "(Default password = First 2 letters of first name (Upper and lower case), First 2 letters of last name (Upper and lower case), Last 4 digits of the student's Student ID and A '!' )");
	
	define("STUDENT_STATUS", "Student Status");
	define("PLACEMENT_STATUS", "Placement Status");
	define("STUDENT_STATUS_1", "Selected Student Statuses to Send to LeadSquared");
	define("PLACEMENT_STATUS_1", "Selected Placement Statuses to Send to LeadSquared");
	define("ASSIGN_STUDENT_STATUS", "Select Student Status to be assigned to imported leads from LeadSquared");
	define("HIDE_ACCOUNT_ADDRESS_ON_REPORTS", "Hide Account Address On Reports"); //DIAM-1421
	define("STATE_AUTHORIZATION", "State Authorization"); //DIAM-1939
	define("NC_SARA_STATE_AUTHORIZATION", "NC-SARA State Authorization"); //DIAM-1939	

	define("LMS_GRADE_OPTIONS", "Grade Import Options", true); 
	define("LMS_FINAL_GRADE_ONLY", "Final Grades Only", true);
	define("LMS_ALL_GRADE", "All Grades (The gradebook in D3 must match the gradebook in Moodle)", true);

} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("USE_SECONDARY_EMAIL_AS_DEFAULT", "Use Secondary Email As Default", true);

	define("SCHOOL_PAGE_TITLE", "Colegio");
	define("TAB_GENERAL", "General");
	define("TAB_CAMPUS", "Instalaciones");
	define("TAB_ADMIN", "Admin");
	define("TAB_CONTACT", "Contacts");
	define("TAB_OTHER", "Other");
	define("TAB_REPORTS", "Report Settings");
	define("TAB_ACCOUNT_OPTIONS", "Account Options");
	define("TAB_INSTRUCTOR_SETTINGS", "Instructor Portal Settings");
	define("TAB_DIAMOND_PAY_SETTINGS", "Diamond Pay Settings");
	define("TAB_COMMUNICATIONS", "Communications");
	define("TAB_LSQ", "Diamond CRM Powered by LeadSquared");
	
	define("SCHOOL_NAME", "Colegio Name");
	define("SCHOOL_CODE", "Colegio Code");
	define("CONTACT_TYPE", "contacto Type");
	define("CAMPUS", "Instalaciones");
	define("USER", "User");
	define("CONTACT", "Contacto");
	define("SCHOOL_USER", "Colegio User");
	define("TIMEZONE", "Timezone");
	
	define("SHOW_DSIS", "Compartir con DSIS");
	define("ALERT_DUPLICATE_SSN", "Alert Student Duplicate SSN");
	define("EMPLOYEE_LABEL", "Employee Label");
	define("API_KEY", "API Key");
	define("GENERATE_API_KEY", "Generate API Key");
	define("GENERATE_API_KEY_CONFIRM_MSG", "Are you sure, you want Regenerate the API Key?");
	define("AUTO_GENERATE_STUD_ID", "Auto Generate Student ID");
	define("STUD_CODE", "Student Code");
	define("STUD_NEXT_NO", "Student Next #");
	define("SEND_BIRTHDAY_NOTIFICATION", "Send Birthday Alert");
	define("SEND_COURSE_START_NOTIFICATION", "Send Course Start Alert");
	define("EMAIL_TEMPLATE", "Email Template");
	define("TEXT_TEMPLATE", "Text Template");
	define("SEND_BIRTHDAY_NOTIFICATION_BEFORE_DAYS", "Send Birthday Alert Before Days");
	define("SEND_COURSE_START_NOTIFICATION_BEFORE_DAYS", "Send Course Start Alert Before Days");
	define("STU_DEFAULT_PASSWORD", "Student Portal Default Password");
	define("EMP_DEFAULT_PASSWORD", "Employee Default Password");
	define("DUP_STUDENT_NO", "Cannot use this Number as this will create duplicate Student ID in future");
	define("NEW_LEAD_STATUS", "New Lead Status");
	define("QUALIFIED_LEAD_STATUS", "Qualified Lead Status");
	define("NEW_APPLICATIONS_STATUS", "New Applications Status");
	define("NEW_STUDENTS_STATUS", "New Student Status");
	define("SEND_ENROLLED_IN_CLASS", "Send Enrolled In Class Alert");
	define("SEND_FINAL_GRADE_POSTED", "Send Final Grade Posted");
	define("SEND_STUDENT_PORTAL_ACCOUNT_CREATED", "Send Student Portal Account Created");
	define("SEND_INSTRUCTOR_PORTAL_ACCOUNT_CREATED", "Send Instructor Portal Account Created");
	define("SEND_PAYMENT_RECEIPT", "Send Payment Receipt");
	define("SEND_PAST_DUE_PAYMENT", "Send Past Due Payment");
	define("SEND_PAYMENT_REMINDER", "Send Payment Reminder");
	define("SEND_PAYMENT_REMINDER_NOTIFICATION_BEFORE_DAYS", "Send Payment Reminder Alert Before Days");
	define("SEND_STATUS_CHANGE", "Send Status Change");
	
	define("EMPLOYEE", "Employee");
	define("ADD_STATUS", "Add Status");
	define("PDF_LOGO", "PDF Logo");
	define("TWILIO_FROM_NO", "Twilio From Mobile #");
	
	define("ADMISSION_ACTIVE_LEAD", "Admissions - Active Leads");
	define("ADMISSION_APPLICATIONS", "Admissions - Applications");
	define("ADMISSION_ENROLLED", "Admissions - Enrolled");
	define("REGISTRAR_ENROLLED", "Registrar - Enrolled");
	define("REGISTRAR_LOA", "Registrar - LOA");
	define("REGISTRAR_DROPS", "Registrar - Drops");
	define("FINANCE_CURRENT_MONTH", "Accounting - Current Month");
	define("FINANCE_NEXT_MONTH", "Accounting - Next Month");
	define("FINANCE_TITLE_IV", "Accounting - Title IV");
	define("FINANCE_NON_TITLE_IV", "Accounting - Non Title IV");
	define("PLACEMENT_PLACED", "Placement - Placed");
	define("PLACEMENT_PENDING", "Placement - Pending");
	define("PLACEMENT_ACTIVE_SEEKING", "Placement - Active Seeking");
	define("PLACEMENT_INACTIVE", "Placement - Inactive");
	define("CHARGE_PROCESSING_FEE_FROM_STUDENT", "Charge Processing Fees To Student");
	define("TAB_COMMUNICATION", "System Alerts");
	define("TAB_EMP_SETTINGS", "Employee Settings");
	define("TAB_STUD_SETTINGS", "Student Portal Settings");
	define("ENABLE_ATTENDANCE_ACTIVITY_TYPES", "Enable Attendance Activity Types");
	define("ENABLE_ATTENDANCE_COMMENTS", "Enable Attendance Comments");
	define("STUDENT_ID_BARCODE_TYPE", "Student ID Barcode");
	define("BADGE_ID", "Badge ID");
	define("STUDENT_ID", "Student ID");

	define("STUDENT_NAME", "Student Name");
	define("ENABLE_PATERNAL_MATERNAL", "Enable Paternal/Maternal Last Name");

	define("MFA", "Multi Factor Authentication(MFA)");
	define("_1098T_TAX_FORM", "1098T Tax Form");
	define("_480G_TAX_FORM", "480.7G Tax Form");
	define("STUDENT_PORTAL", "Student Portal");
	
	define("STUDENT_OPTIONS", "Student Options");
	define("EMPLOYEE_OPTIONS", "Employee Options");
	define("PORTAL_OPTIONS", "Portal Options");
	define("INSRUCTOR_OPTIONS", "Instructor Options");
	define("QUICK_PAYMENT_OPTIONS", "Quick Payment Options");
	define("STUDENT_ID_PROGRAM_DISPLAY_TYPE", "Student ID Program Display Type");
	define("PROGRAM_CODE", "Program Code");
	define("PROGRAM_DESCRIPTION", "Program Description");
	
	define("STUDENT_PORTAL_OPTIONS", "Student Portal Menu");
	define("ACADEMICS", "Academics");
	define("ACADEMIC_REVIEW", "Academic Review");
	define("ACADEMIC_REVIEW_BY_TERM", "Academic Review By Term");
	define("COSMETOLOGY_GRADE_BOOK_LABS", "Grade Book Labs");
	define("COSMETOLOGY_GRADE_BOOK_SUMMARY", "Grade Book Summary");
	define("COSMETOLOGY_GRADE_BOOK_TEST", "Grade Book Test");
	define("PROGRAM_COURSE_PROGRESS", "Program Course Progress");
	define("ATTENDANCE", "Attendance");
	define("ATTENDANCE_REVIEW", "Attendance Review");
	define("ATTENDANCE_SUMMARY", "Attendance Summary");
	define("FINANCE", "Finance");
	define("FINANCIAL_AID_AWARDS", "Financial Aid Awards");
	define("PAYMENT_SCHEDULE", "Payment Schedule");
	define("STUDENT_LEDGER", "Student Ledger");
	define("SCHEDULE", "Schedule");
	define("GRADE_BOOK", "Grade Book");
	define("GRADE_DISPLAY", "Grade Display");
	define("GRADE", "Grade");
	define("NUMERIC_GRADE", "Numeric Grade");
	define("INSTRUCTOR_PORTAL_OPTIONS", "Instructor Portal Menu");
	define("MNU_STUDENTS_1", "Students");
	define("TAB_FORM_POST_SETTINGS", "Form Post Settings");
	define("ITEMS", "Items");
	define("DESTINATION", "Destination");
	define("TYPE_MAX", "Type(Max)");
	define("FORMAT_SOURCE", "Format/Source");
	define("GENERATE_HTML", "Generate HTML");
	define("DISCLAIMER", "DISCLAIMER");
	define("DISCLAIMER_TEXT", "The sample HTML below is for informative purposes only. It is the sole responsibility of the institution (school) to ensure proper security measures are in place when using this code.");
	define("VIEW_LOG", "View Log");
	define("FORM_POST_LOG", "Form Post Log");
	define("NOTIFICATIONS", "Notifications");
	define("IP_ACCESS", "IP Access");
	define("FORM_BUILDER", "Form Builder");
	define("IP_ADDRESS", "IP Address");
	define("ADD_IP", "Add IP");
	define("GENERATE_PHP", "Generate PHP");
	define("NEXT_STUD_ID_NO", "Next Student ID #");
	
	define("TAB_LMS_SETTINGS", "LMS Settings");
	define("LMS_USERNAME_OPTIONS", "What field would you like to use to create your LMS usernames?");
	define("LMS_PASSWORD_OPTIONS", "Password creation and user Notification");
	define("CREATE_DEFAULT_PASSWORD_AND_NOTIFY", "Create default password and notify user (Moodle will generate password)");
	define("CREATE_DEFAULT_PASSWORD_AND_DO_NOT_NOTIFY", "Create default password and DO NOT notify user");
	define("DO_NOT_CREATE_DEFAULT_PASSWORD", "DO NOT create default password and DO NOT notify user (Moodle password will be blank)");
	define("FORCE_PASSWORD_RESET", "Force Password Reset");
	define("DEFAULT_PASSWORD_FORMAT", "(Default password = First 2 letters of first name (Upper and lower case), First 2 letters of last name (Upper and lower case), Last 4 digits of the student's Badge ID and A '!' )");
	define("SEND_PORTAL_ACCOUNT_CREATED", "Send Login Created?");
	define("ALLOW_INSTRUCTORS_UNPOST", "Allow Instructors to Unpost Attendance");
	define("SEND_ABSENT_ALERT", "Send Absent Alert");
	define("DEFAULT_PASSWORD", "Default Password");
	define("DEFAULT_PASSWORD_FIELD", "Field to use as part of default password");
	define("DEFAULT_PASSWORD_FORMAT_BADGE_ID", "(Default password = First 2 letters of first name (Upper and lower case), First 2 letters of last name (Upper and lower case), Last 4 digits of the student's Badge ID and A '!' )");
	define("DEFAULT_PASSWORD_FORMAT_SSN", "(Default password = First 2 letters of first name (Upper and lower case), First 2 letters of last name (Upper and lower case), Last 4 digits of the student's SSN and A '!' )");
	define("DEFAULT_PASSWORD_FORMAT_STUDENT_ID", "(Default password = First 2 letters of first name (Upper and lower case), First 2 letters of last name (Upper and lower case), Last 4 digits of the student's Student ID and A '!' )");
	define("STUDENT_STATUS", "Student Status");
	define("PLACEMENT_STATUS", "Placement Status");
	define("STUDENT_STATUS_1", "Selected Student Statuses to Send to LeadSquared");
	define("PLACEMENT_STATUS_1", "Selected Placement Statuses to Send to LeadSquared");
	define("ASSIGN_STUDENT_STATUS", "Select Student Status to be assigned to imported leads from LeadSquared");
	define("HIDE_ACCOUNT_ADDRESS_ON_REPORTS", "Hide Account Address On Reports"); //DIAM-1421
	define("STATE_AUTHORIZATION", "State Authorization"); //DIAM-1939
	define("NC_SARA_STATE_AUTHORIZATION", "NC-SARA State Authorization"); //DIAM-1939

	define("LMS_GRADE_OPTIONS", "Grade Import Options", true); 
	define("LMS_FINAL_GRADE_ONLY", "Final Grades Only", true);
	define("LMS_ALL_GRADE", "All Grades (The gradebook in D3 must match the gradebook in Moodle)", true);

}
?>
