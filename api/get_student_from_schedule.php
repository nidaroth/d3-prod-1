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
	
	$res_type = $db->Execute("select PK_STUDENT_SCHEDULE, S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, HOURS, PK_STUDENT_ENROLLMENT from S_STUDENT_SCHEDULE, S_STUDENT_MASTER WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$PK_ACCOUNT' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$_GET[id]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_SCHEDULE.PK_STUDENT_MASTER ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC");
	$i = 0;
	while (!$res_type->EOF) { 
		
		$PK_STUDENT_SCHEDULE = $res_type->fields['PK_STUDENT_SCHEDULE']; 
		$res_att = $db->Execute("SELECT * FROM S_STUDENT_ATTENDANCE WHERE PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' "); 
		if($res_att->RecordCount() == 0) {
			$PK_STUDENT_ATTENDANCE 	= '';
			$ATTENDANCE_HOURS 		= number_format_value_checker($res_type->fields['HOURS'],5);
			$PK_ATTENDANCE_CODE		= 14;
			
			$ALL_COMPLETED 	= 0;
			$COMPLETED 		= 0;
		} else { 
			$PK_STUDENT_ATTENDANCE 	= $res_att->fields['PK_STUDENT_ATTENDANCE'];
			$ATTENDANCE_HOURS 		= $res_att->fields['ATTENDANCE_HOURS'];
			$PK_ATTENDANCE_CODE		= $res_att->fields['PK_ATTENDANCE_CODE'];
			$COMPLETED 				= $res_att->fields['COMPLETED'];
			
			if($res_att->fields['COMPLETED'] == 0)
				$ALL_COMPLETED = 0;
		} 
				
		$RET_DATA['STUDENT'][$i]['PK_STUDENT_SCHEDULE'] = $res_type->fields['PK_STUDENT_SCHEDULE'];
		$RET_DATA['STUDENT'][$i]['NAME'] 				= $res_type->fields['NAME'];
		$RET_DATA['STUDENT'][$i]['ATTENDANCE_HOURS'] 	= $ATTENDANCE_HOURS;
		$RET_DATA['STUDENT'][$i]['PK_ATTENDANCE_CODE'] 	= $PK_ATTENDANCE_CODE;
		$RET_DATA['STUDENT'][$i]['COMPLETED'] 			= $COMPLETED;
		
		$i++;
		$res_type->MoveNext();
	} 
	
	$RET_DATA['ALL_COMPLETED'] = $ALL_COMPLETED;
}

echo json_encode($RET_DATA);