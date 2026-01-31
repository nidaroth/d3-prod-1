<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/dashboard.php");
require_once("check_access.php");

//echo date_default_timezone_get();exit;
//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$st_date  	= $_REQUEST['st'];
$et_date  	= $_REQUEST['et'];

$date_cond = " AND TASK_DATE BETWEEN '$st_date' and '$et_date' $cal_cond  "; 
/* Ticket # 1006 */
if($_REQUEST['CAL_PK_EMPLOYEE_MASTER'] != '')
	$date_cond .= " AND S_STUDENT_TASK.PK_EMPLOYEE_MASTER = '$_REQUEST[CAL_PK_EMPLOYEE_MASTER]' ";
else
	$date_cond .= " AND (S_STUDENT_TASK.PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' OR PK_SUPERVISOR = '$_SESSION[PK_EMPLOYEE_MASTER]' ) ";
	
if($_REQUEST['CAL_PK_TASK_TYPE'] != '')
	$date_cond .= " AND S_STUDENT_TASK.PK_TASK_TYPE IN ($_REQUEST[CAL_PK_TASK_TYPE]) ";
/* Ticket # 1006 */

$res_type = $db->Execute("select PK_STUDENT_ENROLLMENT,PK_STUDENT_TASK,TASK_TIME, TASK_TYPE,TASK_STATUS,NOTES ,TASK_DATE ,S_STUDENT_TASK.PK_STUDENT_MASTER, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE,'%m/%d/%Y')) AS FOLLOWUP_DATE, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS EMP_NAME , CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, S_STUDENT_TASK.PK_DEPARTMENT FROM S_STUDENT_MASTER,S_STUDENT_TASK LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_TASK.PK_EMPLOYEE_MASTER JOIN M_TASK_TYPE ON M_TASK_TYPE.PK_TASK_TYPE = S_STUDENT_TASK.PK_TASK_TYPE LEFT JOIN M_TASK_STATUS ON M_TASK_STATUS.PK_TASK_STATUS = S_STUDENT_TASK.PK_TASK_STATUS WHERE S_STUDENT_TASK.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND COMPLETED = 0 AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_TASK.PK_STUDENT_MASTER $date_cond AND TASK_DATE != '0000-00-00' ORDER BY TASK_DATE DESC "); 
$index = 0;
while (!$res_type->EOF) { 
	$YEAR 	= intval(date("Y",strtotime($res_type->fields['TASK_DATE'])));
	$MONTH 	= intval(date("m",strtotime($res_type->fields['TASK_DATE'])));
	$DAY 	= intval(date("d",strtotime($res_type->fields['TASK_DATE'])));
	$HOUR	= intval(date("H",strtotime($res_type->fields['TASK_TIME'])));
	$MIN	= intval(date("i",strtotime($res_type->fields['TASK_TIME'])));
	
	if($MONTH < 10)
		$MONTH = '0'.$MONTH;
		
	if($DAY < 10)
		$DAY = '0'.$DAY;
		
	if($HOUR < 10)
		$HOUR = '0'.$HOUR;
		
	if($MIN < 10)
		$MIN = '0'.$MIN;
		
	if($res_type->fields['PK_DEPARTMENT'] == -1) 
		$t = 2;
	else { 
		$res_dep = $db->Execute("SELECT PK_DEPARTMENT_MASTER FROM M_DEPARTMENT WHERE PK_DEPARTMENT = '".$res_type->fields['PK_DEPARTMENT']."' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
		$PK_DEPARTMENT_MASTER = $res_dep->fields['PK_DEPARTMENT_MASTER'];
		
		$edit_flag = 0;
		if($PK_DEPARTMENT_MASTER == 2) {
			//admission
			$t = 1;
		} else if($PK_DEPARTMENT_MASTER == 7) {
			//Registrar
			$t = 2;
		} else if($PK_DEPARTMENT_MASTER == 4) {
			//Finance
			$t = 3;
		} else if($PK_DEPARTMENT_MASTER == 1) {
			//Accounting
			$t = 5;
		} else if($PK_DEPARTMENT_MASTER == 6) {
			//Placement
			$t = 6;
		}
	}
	
	$cal_res[$index]['id'] = "student_task?sid=".$res_type->fields['PK_STUDENT_MASTER']."&id=".$res_type->fields['PK_STUDENT_TASK']."&eid=".$res_type->fields['PK_STUDENT_ENROLLMENT']."&t=".$t."&p=i";
	$cal_res[$index]['title'] = addslashes($res_type->fields['STU_NAME'].' - '.$res_type->fields['TASK_TYPE'].' - '.$res_type->fields['TASK_STATUS']);
	$cal_res[$index]['start'] = $YEAR.'-'.$MONTH.'-'.$DAY.' '.$HOUR.':'.$MIN.":00";
	$cal_res[$index]['end']   = $YEAR.'-'.$MONTH.'-'.$DAY.' '.$HOUR.':'.$MIN.":59";

	$index++;
	$res_type->MoveNext();
}


$cal_table = "";
$cal_cond  = "";
if($_REQUEST['CAL_PK_CAMPUS'] != '') {
	$cal_table = ",M_ACADEMIC_CALENDAR_CAMPUS ";
	$cal_cond .= " AND M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR = M_ACADEMIC_CALENDAR_CAMPUS.PK_ACADEMIC_CALENDAR AND M_ACADEMIC_CALENDAR_CAMPUS.PK_CAMPUS IN ($_REQUEST[CAL_PK_CAMPUS]) ";
} else {
	if($_SESSION['ADMIN_PK_ROLES'] != 1 &&  $_SESSION['PK_ROLES'] != 2){
		$cal_table = ",M_ACADEMIC_CALENDAR_CAMPUS ";
		$cal_cond .= " AND M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR = M_ACADEMIC_CALENDAR_CAMPUS.PK_ACADEMIC_CALENDAR AND M_ACADEMIC_CALENDAR_CAMPUS.PK_CAMPUS IN ($_SESSION[PK_CAMPUS]) ";
	}
}

$res_type = $db->Execute("SELECT M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR,IF(LEAVE_TYPE = 1,'Holiday',IF(LEAVE_TYPE = 2,'Break',IF(LEAVE_TYPE = 3,'Closure',''))) AS LEAVE_TYPE,START_DATE,END_DATE FROM M_ACADEMIC_CALENDAR $cal_table WHERE M_ACADEMIC_CALENDAR.ACTIVE = 1 AND M_ACADEMIC_CALENDAR.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND START_DATE BETWEEN '$st_date' and '$et_date' $cal_cond GROUP BY M_ACADEMIC_CALENDAR.PK_ACADEMIC_CALENDAR "); 

/* Ticket # 1299 */
while (!$res_type->EOF) { 
	$PK_ACADEMIC_CALENDAR = $res_type->fields['PK_ACADEMIC_CALENDAR']; 
	$res_type_s = $db->Execute("SELECT SESSION,M_SESSION.PK_SESSION FROM M_SESSION,M_ACADEMIC_CALENDAR_SESSION WHERE M_ACADEMIC_CALENDAR_SESSION.PK_SESSION = M_SESSION.PK_SESSION AND PK_ACADEMIC_CALENDAR = '$PK_ACADEMIC_CALENDAR' GROUP BY M_SESSION.PK_SESSION ORDER BY DISPLAY_ORDER ASC "); 
	while (!$res_type_s->EOF) { 
		$ST_YEAR 	= intval(date("Y",strtotime($res_type->fields['START_DATE'])));
		$ST_MONTH 	= intval(date("m",strtotime($res_type->fields['START_DATE'])));
		$ST_DAY 	= intval(date("d",strtotime($res_type->fields['START_DATE'])));

		if($ST_MONTH < 10)
			$ST_MONTH = '0'.$ST_MONTH;
			
		if($ST_DAY < 10)
			$ST_DAY = '0'.$ST_DAY;
			
		$ET_YEAR 	= intval(date("Y",strtotime($res_type->fields['END_DATE'])));
		$ET_MONTH 	= intval(date("m",strtotime($res_type->fields['END_DATE'])));
		$ET_DAY 	= intval(date("d",strtotime($res_type->fields['END_DATE'])));

		if($ET_MONTH < 10)
			$ET_MONTH = '0'.$ET_MONTH;
			
		if($ET_DAY < 10)
			$ET_DAY = '0'.$ET_DAY;

		$cal_res[$index]['id'] 			= "";
		$cal_res[$index]['title'] 		= addslashes($res_type->fields['LEAVE_TYPE'].' - '.$res_type_s->fields['SESSION']);
		$cal_res[$index]['start'] 		= $ST_YEAR.'-'.$ST_MONTH.'-'.$ST_DAY." 00:00:00";
		$cal_res[$index]['end']   		= $ET_YEAR.'-'.$ET_MONTH.'-'.$ET_DAY." 23:59:59";
		$cal_res[$index]['className']   = "bg-info-".$res_type_s->fields['PK_SESSION'];

		$index++; 
		$res_type_s->MoveNext();
	}
	$res_type->MoveNext();
}

echo json_encode($cal_res);
