<? require_once("../../global/config.php"); 
require_once("../../school/function_attendance.php"); 

//$DATA = '';
$DATA = urldecode($DATA);
$DATA = json_decode($DATA);

$API_KEY = '';
foreach (getallheaders() as $name => $value) {
    //echo "$name: $value<br />";
	if(strtolower(trim($name)) == 'apikey')
		$API_KEY = trim($value);
}
$flag = 1;
if($API_KEY == ''){
	$data['SUCCESS'] = 0;
	$data['MESSAGE'] = 'API Key Missing';
	
	$flag = 0;
} else {
	$res = $db->Execute("SELECT PK_ACCOUNT,ACTIVE FROM Z_ACCOUNT where API_KEY = '$API_KEY'");
	if($res->RecordCount() == 0){
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = 'Invalid API Key';
		
		$flag = 0;
	} else if($res->fields['ACTIVE'] == 0){
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = 'Your Account Is Blocked.';
		
		$flag = 0;
	}
	
	$PK_ACCOUNT = $res->fields['PK_ACCOUNT'];
}

if($flag == 1){
	$data['SUCCESS'] = 1;
	$data['MESSAGE'] = '';
	
	foreach($COURSE_OFFERING->ATTENDANCE as $ATTENDANCE) {
	
		if($ATTENDANCE->PK_STUDENT_COURSE == '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= ' Missing ATTENDANCE->PK_STUDENT_COURSE Value';
		}
		
		if($ATTENDANCE->ENROLLMENT_ID == '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= ' Missing ATTENDANCE->ENROLLMENT_ID Value';
		} else {
			$PK_STUDENT_ENROLLMENT = $ATTENDANCE->ENROLLMENT_ID;
			$res_st = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT'");
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ATTENDANCE->ENROLLMENT_ID Value - '.$PK_STUDENT_ENROLLMENT;
			} else {
				$PK_STUDENT_COURSE = $ATTENDANCE->PK_STUDENT_COURSE;
				$res_st = $db->Execute("SELECT PK_STUDENT_COURSE FROM S_STUDENT_COURSE WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' ");
				if($res_st->RecordCount() == 0){
					$data['SUCCESS'] = 0;
					if($data['MESSAGE'] != '')
						$data['MESSAGE'] .= ', ';
						
					$data['MESSAGE'] .= 'Invalid ATTENDANCE->PK_STUDENT_COURSE Value - '.$PK_STUDENT_COURSE;
				}
			}
		}
		
		if($ATTENDANCE->DATE == '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= ' Missing ATTENDANCE->DATE Value';
		}
		
		if($ATTENDANCE->START_TIME == '') {
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= ' Missing ATTENDANCE->START_TIME Value';
		}
		
		$PK_ATTENDANCE_CODE = $ATTENDANCE->ATTENDANCE_CODE_ID;
		if($PK_ATTENDANCE_CODE != '') {
			$res_st = $db->Execute("select PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE WHERE PK_ATTENDANCE_CODE = '$PK_ATTENDANCE_CODE' ");
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid ATTENDANCE->ATTENDANCE_CODE_ID Value - '.$PK_ATTENDANCE_CODE;
			}
		}
		
		$COMPLETED = $ATTENDANCE->COMPLETED;
		if(strtolower($COMPLETED) != 'yes' && strtolower($COMPLETED) != 'no' && strtolower($COMPLETED) != '') {
			$data['SUCCESS'] = 0;
			$data['MESSAGE'] .= 'Invalid ATTENDANCE->COMPLETED Value - '.$COMPLETED;
		}
		
	}
}

if($data['SUCCESS'] == 1) {
	foreach($COURSE_OFFERING->ATTENDANCE as $ATTENDANCE) {
		$DATE 					= $ATTENDANCE->DATE;
		$START_TIME 			= $ATTENDANCE->START_TIME.':00';
		$ATTENDED_HOUR 			= $ATTENDANCE->ATTENDED_HOUR;
		$PK_ATTENDANCE_CODE 	= $ATTENDANCE->ATTENDANCE_CODE_ID;
		$COMPLETED 				= $ATTENDANCE->COMPLETED;
		$PK_STUDENT_COURSE 		= $ATTENDANCE->PK_STUDENT_COURSE;
		$PK_STUDENT_ENROLLMENT 	= $ATTENDANCE->ENROLLMENT_ID;
		
		$res_sch = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 
		$PK_STUDENT_MASTER = $res_sch->fields['PK_STUDENT_MASTER'];
		
		if(strtolower($COMPLETED) == 'yes')
			$COMPLETED = 1;
		else
			$COMPLETED = 0;
		
		$res_sch = $db->Execute("SELECT PK_STUDENT_SCHEDULE, PK_COURSE_OFFERING_SCHEDULE_DETAIL FROM S_STUDENT_SCHEDULE WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND PK_ACCOUNT = '$PK_ACCOUNT' AND SCHEDULE_DATE = '$DATE' AND START_TIME = '$START_TIME' "); 
		if($res_sch->RecordCount() > 0){
			$PK_STUDENT_SCHEDULE 				= $res_sch->fields['PK_STUDENT_SCHEDULE'];
			$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $res_sch->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
			
			attendance_entry($PK_COURSE_OFFERING_SCHEDULE_DETAIL,$COMPLETED,'',$PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT,$PK_STUDENT_SCHEDULE,$ATTENDED_HOUR, $PK_ATTENDANCE_CODE,$PK_ACCOUNT,0);
			
		}
	}
	$data['SUCCESS'] = 1;
	$data['MESSAGE'] = 'Notes Created';
}
$data = json_encode($data);
echo $data;
?>