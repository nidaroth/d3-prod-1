<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("GRADES_PAGE_TITLE", "Grade Setup");
	define("GRADE", "Grade");
	define("NUMBER_GRADE", "Number Grade");
	define("CALCULATE_GPA", "Calculate GPA");
	define("UNITS_ATTEMPTED", "Units Attempted");
	define("UNITS_COMPLETED", "Units Completed");
	define("UNITS_IN_PROGRESS", "Units in Progress");
	define("WEIGHTED_GRADE_CALC", "Weighted Grade Calc");
	define("RETAKE_UPDATE", "Retake Update");
	define("CF_GRADE", "CF Grade");
	define("DISPLAY_ORDER", "Sort Order");
	define("IS_DEFAULT", "Is Default");
	define("RETAKE_GRADE", "Retake Grade");
	define("WEIGHTED_GRADE_CALC_1", "Weighted<br />Grade Calc");
	define("UNITS_COMPLETED_1", "Units<br />Completed");
	define("UNITS_IN_PROGRESS_1", "Units<br />in Progress");
	define("UNITS_ATTEMPTED_1", "Units<br />Attempted");
	define("CALCULATE_GPA_1", "Calculate<br />GPA");
	define("RETAKE_UPDATE_1", "Retake<br />Update");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("GRADES_PAGE_TITLE", "Grade Setup");
	define("GRADE", "Grade");
	define("NUMBER_GRADE", "Number Grade");
	define("CALCULATE_GPA", "Calculate GPA");
	define("UNITS_ATTEMPTED", "Units Attempted");
	define("UNITS_COMPLETED", "Units Completed");
	define("UNITS_IN_PROGRESS", "Units in Progress");
	define("WEIGHTED_GRADE_CALC", "Weighted Grade Calc");
	define("RETAKE_UPDATE", "Retake Update");
	define("CF_GRADE", "CF Grade");
	define("DISPLAY_ORDER", "Sort Order");
	define("IS_DEFAULT", "Is Default");
	define("RETAKE_GRADE", "Retake Grade");
	define("WEIGHTED_GRADE_CALC_1", "Weighted<br />Grade Calc");
	define("UNITS_COMPLETED_1", "Units<br />Completed");
	define("UNITS_IN_PROGRESS_1", "Units<br />in Progress");
	define("UNITS_ATTEMPTED_1", "Units<br />Attempted");
	define("CALCULATE_GPA_1", "Calculate<br />GPA");
	define("RETAKE_UPDATE_1", "Retake<br />Update");
}