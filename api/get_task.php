<?php require_once("../global/config.php"); 
$API_KEY = $_GET['key'];

$res_user = $db->Execute("SELECT PK_USER,PK_ACCOUNT,ID FROM Z_USER where USER_API_KEY = '$API_KEY' ");
$PK_ACCOUNT 		= $res_user->fields['PK_ACCOUNT'];
$PK_USER 			= $res_user->fields['PK_USER'];
$PK_EMPLOYEE_MASTER = $res_user->fields['ID'];

if($PK_USER == 0 || $PK_USER == '' || $PK_EMPLOYEE_MASTER == 0 || $PK_EMPLOYEE_MASTER == '') {
	$RET_DATA['STATUS']  = 0;
	$RET_DATA['MESSAGE'] = 'Something went Wrong';
} else {
	$RET_DATA['STATUS']  = 1;
	$RET_DATA['MESSAGE'] = '';
	
	$res_type = $db->Execute("select PK_STUDENT_ENROLLMENT,PK_STUDENT_TASK,TASK_TIME, TASK_TYPE,TASK_STATUS,NOTES ,IF(TASK_DATE = '0000-00-00', '',  DATE_FORMAT(TASK_DATE,'%m/%d/%Y')) AS TASK_DATE1,S_STUDENT_TASK.PK_STUDENT_MASTER, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE ,'%m/%d/%Y')) AS FOLLOWUP_DATE, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS EMP_NAME , CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME FROM S_STUDENT_MASTER,S_STUDENT_TASK LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_TASK.PK_EMPLOYEE_MASTER JOIN M_TASK_TYPE ON M_TASK_TYPE.PK_TASK_TYPE = S_STUDENT_TASK.PK_TASK_TYPE LEFT JOIN M_TASK_STATUS ON M_TASK_STATUS.PK_TASK_STATUS = S_STUDENT_TASK.PK_TASK_STATUS WHERE S_STUDENT_TASK.PK_ACCOUNT = '$PK_ACCOUNT' AND COMPLETED = 0 AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_TASK.PK_STUDENT_MASTER AND S_STUDENT_TASK.PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ORDER BY TASK_DATE ASC ");
	$i = 0;
	while (!$res_type->EOF) { 
		$TASK_TIME = '';
		if($res_type->fields['TASK_TIME'] != '00-00-00') 
			$TASK_TIME = date("h:i A", strtotime($res_type->fields['TASK_TIME']));
												
		$RET_DATA['TASKS'][$i]['ID'] 		= $res_type->fields['PK_STUDENT_TASK'];
		$RET_DATA['TASKS'][$i]['DUE'] 		= $res_type->fields['TASK_DATE1'].' '.$TASK_TIME;
		$RET_DATA['TASKS'][$i]['STATUS'] 	= $res_type->fields['TASK_STATUS'];
		$RET_DATA['TASKS'][$i]['STUDENT'] 	= $res_type->fields['STU_NAME'];
		$RET_DATA['TASKS'][$i]['TYPE'] 		= $res_type->fields['TASK_TYPE'];
		$RET_DATA['TASKS'][$i]['NOTES'] 	= $res_type->fields['NOTES'];
		
		$i++;
		$res_type->MoveNext();
	} 
}

echo json_encode($RET_DATA);