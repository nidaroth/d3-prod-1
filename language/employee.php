<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("EMPLOYEE_PAGE_TITLE", "Employee");
	define("EMPLOYEE_PAGE_TITLE_1", "Employees");
	define("TEACHER_PAGE_TITLE", "Teacher");
	define("DEPARTMENT", "Department");
	define("EMPLOYEE_ID", "Employee ID");
	define("HAS_LOGIN", "Has Login");
	define("TAB_EMPLOYEE", "Employee");
	define("TAB_TEACHER", "Teacher");
	define("TAB_CAMPUS", "Campus");
	define("TAB_DETAILS", "Details");
	define("TAB_NOTES", "Notes");
	define("TAB_USER_ACCESS", "User Access");
	define("DOB", "Date Of Birth");
	define("MARITAL_STATUS", "Marital Status");
	define("IPEDS_ETHNICITY", "IPEDS Ethnicity");
	define("RACE", "Race");
	define("NETWORK_ID", "Network ID");
	define("COMPANY_EMP_ID", "Company Emp ID");
	define("SUPERVISOR", "Supervisor");
	define("TITLE", "Title");
	define("FULL_PART_TIME", "Full/Part Time");
	define("ELIGIBLE_FOR_REHIRE", "Eligible for Rehire");
	define("SOC_CODE", "SOC Code");
	define("DATE_HIRED", "Date Hired");
	define("DATE_TERMINATED", "Date Terminated");
	define("REMOVE_LOGIN_CONFIRMATION", "Are you sure you want to Remove Login for this User");
	define("IS_FACULTY", "Is Faculty");
	define("TEACHER", "Teacher");
	define("CAMPUS", "Campus");
	define("TURN_OFF_ASSIGNMENTS", "Turn Off New Assignments");
	define("IMAGE", "Image");
	define("GENDER", "Gender");
	define("AVAILABLE", "Available");
	define("SHOW_AVAILABLE_ONLY", "Show Available Only");
	define("SHOW_ACTIVE_ONLY", "Show Active Only");
	define("NOTE_STATUS", "Note Status");
	define("SESSION", "Session");
	define("COURSE_CODE", "Course");
	define("PLEASE_SELECT_DEPARTMENT_CAMPUS", "Please Select Campus & Department");
	define("PLEASE_SELECT_DEPARTMENT", "Please Select Department");
	define("PLEASE_SELECT_CAMPUS", "Please Select Campus");
	define("SCHOOL_ADMIN", "School Admin");
	
	define("CONSOLIDATE_EMPLOYEE", "Consolidate Employee");
	define("EMPLOYEE_TO_KEEP", "Employee To Keep");
	define("EMPLOYEE_TO_CONDOLIDATE", "Employee To Delete");
	define("CONSOLIDATE_EMPLOYEE_WARNING", "WARNING: Deleted records cannot be restored");
	define("POPULATION_REPORT", "Population Report");
	define("MOODLE_ID", "Moodle ID");
	define("INSTRUCTOR", "Instructor");
	define("EMAIL_USER_ID", "Email/User ID");
	define("HAS_LOGIN_1", "Has<br />Login");
	define("SCHOOL_ADMIN_1", "School<br />Admin");
	
	define("RETURN_TO_EMPLOYEE", "Return To Employee");

	define("MNU_90_10", "90/10", true);
	define("MNU_IPEDS", "IPEDS", true);
	define("PAID_USER", "Paid User", true);
	define("PAID_USER_1", "Paid<br />User", true);
	define("CLEAR_FILTER", "Clear Filter", true); //Ticket # 703
	define("MNU_UNPOST_BATCHES", "Unpost Batches");

	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("EMPLOYEE_PAGE_TITLE", "Employee");
	define("EMPLOYEE_PAGE_TITLE_1", "Employees");
	define("TEACHER_PAGE_TITLE", "Teacher");
	define("DEPARTMENT", "Departamento");
	define("EMPLOYEE_ID", "Employee ID");
	define("HAS_LOGIN", "Tiene inicio de sesión");
	define("TAB_EMPLOYEE", "Employee");
	define("TAB_TEACHER", "Teacher");
	define("TAB_CAMPUS", "Instalaciones");
	define("TAB_DETAILS", "Details");
	define("TAB_NOTES", "Notes");
	define("TAB_USER_ACCESS", "User Access");
	define("DOB", "Fecha de nacimiento");
	define("MARITAL_STATUS", "Estado civil");
	define("IPEDS_ETHNICITY", "IPEDS Etnicidad");
	define("RACE", "Carrera");
	define("NETWORK_ID", "Network ID");
	define("COMPANY_EMP_ID", "Empresa Emp ID");
	define("SUPERVISOR", "Supervisor");
	define("TITLE", "Título");
	define("FULL_PART_TIME", "Full/Part Time");
	define("ELIGIBLE_FOR_REHIRE", "Elegible para ser re-contratado");
	define("SOC_CODE", "SOC Code");
	define("DATE_HIRED", "Fecha de contratacion");
	define("DATE_TERMINATED", "Date Terminated");
	define("REMOVE_LOGIN_CONFIRMATION", "Are you sure you want to Remove Login for this User");
	
	define("IS_FACULTY", "Es facultad");
	define("TEACHER", "Teacher");
	define("CAMPUS", "Campus");
	define("TURN_OFF_ASSIGNMENTS", "Turn Off New Assignments");
	define("IMAGE", "Image");
	define("GENDER", "Gender");
	define("AVAILABLE", "Available");
	define("SHOW_AVAILABLE_ONLY", "Show Available Only");
	define("SHOW_ACTIVE_ONLY", "Show Active Only");
	define("NOTE_STATUS", "Note Status");
	define("SESSION", "Session");
	define("COURSE_CODE", "Course");
	define("PLEASE_SELECT_DEPARTMENT_CAMPUS", "Please Select Campus & Department");
	define("PLEASE_SELECT_DEPARTMENT", "Please Select Department");
	define("PLEASE_SELECT_CAMPUS", "Please Select Campus");
	define("SCHOOL_ADMIN", "School Admin");

	define("CONSOLIDATE_EMPLOYEE", "Consolidate Employee", true);
	define("EMPLOYEE_TO_KEEP", "Employee To Keep", true);
	define("EMPLOYEE_TO_CONDOLIDATE", "Employee To Delete", true);
	define("CONSOLIDATE_EMPLOYEE_WARNING", "WARNING: Deleted records cannot be restored", true);
	define("POPULATION_REPORT", "Population Report", true);
	define("MOODLE_ID", "Moodle ID", true);
	define("INSTRUCTOR", "Instructor", true);
	define("HAS_LOGIN_1", "Has<br />Login", true);
	define("SCHOOL_ADMIN_1", "School<br />Admin", true);
	define("RETURN_TO_EMPLOYEE", "Return To Employee", true);
	define("MNU_90_10", "90/10", true);
	define("MNU_IPEDS", "IPEDS", true);
	define("PAID_USER", "Paid User", true);
	define("PAID_USER_1", "Paid<br />User", true);
	define("CLEAR_FILTER", "Clear Filter", true); //Ticket # 703
	define("MNU_UNPOST_BATCHES", "Unpost Batches");

}
