<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"OLD_DIAMOND_ID":"111","CAMPUS_ID":"2","STATUS_ID":2,"COURSE_CODE_ID":"3","TERM_ID":"1","SESSION_ID":"65","SESSION_NO":"2","ROOM_ID":"2","INSTRUCTOR_ID":"17","ASSISTANT_ID":"19","ATTENDANCE_TYPE_ID":"2","DEFAULT_ATTENDANCE_CODE_ID":"11","CLASS_SIZE":"5","SCHEDULE":{"SCHEDULE_ON_HOLIDAY":"No","OVERWRITE_SCHEDULE_DATE":"No","DEFAULT_SCHEDULE":{"START_DATE":"2020-09-01","END_DATE":"2020-09-30","START_TIME":"05:30:00","END_TIME":"08:30:00","HOURS":"3.00"},"WEEKLY_SCHEDULE":{"SUNDAY":{"START_TIME":"05:30:00","END_TIME":"08:30:00","HOURS":"3.00","ROOM_ID":"2"},"MONDAY":{"START_TIME":"05:30:00","END_TIME":"08:30:00","HOURS":"3.00","ROOM_ID":"3"},"TUESDAY":{"START_TIME":"05:30:00","END_TIME":"08:30:00","HOURS":"6.00","ROOM_ID":"0"},"WEDNESDAY":{"START_TIME":"05:30:00","END_TIME":"08:30:00","HOURS":"3.00","ROOM_ID":"0"},"THURSDAY":{"START_TIME":"05:30:00","END_TIME":"08:30:00","HOURS":"8.50","ROOM_ID":"0"},"FRIDAY":{"START_TIME":"00:00:00","END_TIME":"00:00:00","HOURS":"0.00","ROOM_ID":"0"},"SATURDAY":{"START_TIME":"00:00:00","END_TIME":"","HOURS":"0.00","ROOM_ID":"0"}},"DAILY_SCHEDULE":[{"DATE":"2020-09-01","START_TIME":"05:30:00","END_TIME":"08:30:00","HOURS":"6.00","ROOM_ID":"2","COMPLETED":"No"},{"DATE":"2020-09-02","START_TIME":"05:30:00","END_TIME":"08:30:00","HOURS":"3.00","ROOM_ID":"2","COMPLETED":"No"},{"DATE":"2020-09-03","START_TIME":"05:30:00","END_TIME":"08:30:00","HOURS":"8.50","ROOM_ID":"2","COMPLETED":"Yes"}]}}';

$API_KEY = '';
foreach (getallheaders() as $name => $value) {
    //echo "$name: $value<br />";
	if(strtolower(trim($name)) == 'apikey')
		$API_KEY = trim($value);
}

$DATA = urldecode($DATA);
$DATA = json_decode($DATA);

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

	$PK_CAMPUS					= trim($DATA->CAMPUS_ID);
	$PK_COURSE 					= trim($DATA->COURSE_CODE_ID);
	$PK_TERM_MASTER 			= trim($DATA->TERM_ID);
	$PK_SESSION 				= trim($DATA->SESSION_ID);
	$SESSION_NO 				= trim($DATA->SESSION_NO);
	$PK_CAMPUS_ROOM 			= trim($DATA->ROOM_ID);
	$INSTRUCTOR 				= trim($DATA->INSTRUCTOR_ID);
	$ASSISTANT 					= trim($DATA->ASSISTANT_ID);
	$PK_ATTENDANCE_TYPE 		= trim($DATA->ATTENDANCE_TYPE_ID);
	$PK_ATTENDANCE_CODE 		= trim($DATA->DEFAULT_ATTENDANCE_CODE_ID);
	$CLASS_SIZE 				= trim($DATA->CLASS_SIZE);
	$OLD_DIAMOND_ID 			= trim($DATA->OLD_DIAMOND_ID);
	$PK_COURSE_OFFERING_STATUS 	= trim($DATA->STATUS_ID);
	if($PK_COURSE_OFFERING_STATUS == '')
		$PK_COURSE_OFFERING_STATUS = 1;

	if($PK_CAMPUS == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Missing CAMPUS_ID Value';
	} else {
		$res_st = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid CAMPUS_ID Value - '.$CAMPUS_ID;
		}
	}
	
	if($PK_TERM_MASTER == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Missing TERM_ID Value';
	} else {
		$res_st = $db->Execute("select PK_TERM_MASTER from S_TERM_MASTER WHERE PK_TERM_MASTER = '$PK_TERM_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid TERM_ID Value - '.$PK_TERM_MASTER;
		}
	}
	
	if($PK_SESSION == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Missing SESSION_ID Value';
	} else {
		$res_st = $db->Execute("select PK_SESSION from M_SESSION WHERE PK_SESSION = '$PK_SESSION' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid SESSION_ID Value - '.$PK_SESSION;
		}
	}
	
	if($PK_COURSE == '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Missing COURSE_CODE_ID Value';
	} else {
		$res_st = $db->Execute("select PK_COURSE from S_COURSE WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid COURSE_CODE_ID Value - '.$PK_COURSE;
		}
	}
	
	if($PK_COURSE_OFFERING_STATUS != '') {
		$res_st = $db->Execute("select PK_COURSE_OFFERING_STATUS from M_COURSE_OFFERING_STATUS WHERE PK_COURSE_OFFERING_STATUS = '$PK_COURSE_OFFERING_STATUS' ");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid STATUS Value - '.$PK_COURSE_OFFERING_STATUS;
		}
	}
	
	if($PK_CAMPUS_ROOM != '') {
		$res_st = $db->Execute("select PK_CAMPUS_ROOM from M_CAMPUS_ROOM WHERE PK_CAMPUS_ROOM = '$PK_CAMPUS_ROOM' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid ROOM_ID Value - '.$PK_CAMPUS_ROOM;
		}
	}
	
	if($INSTRUCTOR != '') {
		$res_st = $db->Execute("select PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$INSTRUCTOR' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid INSTRUCTOR_ID Value - '.$INSTRUCTOR;
		}
	}
	
	if($ASSISTANT != '') {
		$res_st = $db->Execute("select PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$ASSISTANT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid ASSISTANT_ID Value - '.$ASSISTANT;
		}
	}
	
	if($PK_ATTENDANCE_TYPE != '') {
		$res_st = $db->Execute("select PK_ATTENDANCE_TYPE from M_ATTENDANCE_TYPE WHERE PK_ATTENDANCE_TYPE = '$PK_ATTENDANCE_TYPE' ");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid ATTENDANCE_TYPE_ID Value - '.$PK_ATTENDANCE_TYPE;
		}
	}
	
	if($PK_ATTENDANCE_CODE != '') {
		$res_st = $db->Execute("select PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE WHERE PK_ATTENDANCE_CODE = '$PK_ATTENDANCE_CODE' ");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid DEFAULT_ATTENDANCE_CODE_ID Value - '.$PK_ATTENDANCE_CODE;
		}
	}

	$SCHEDULE_ON_HOLIDAY = 0;
	if(strtolower($DATA->SCHEDULE->SCHEDULE_ON_HOLIDAY) == 'yes')
		$SCHEDULE_ON_HOLIDAY = 1;
	else if(strtolower($DATA->SCHEDULE->SCHEDULE_ON_HOLIDAY) == 'no')
		$SCHEDULE_ON_HOLIDAY = 0;
	else if($DATA->SCHEDULE->SCHEDULE_ON_HOLIDAY != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid SCHEDULE_ON_HOLIDAY Value';
	}
	
	$OVERWRITE_SCHEDULE_DATE = 0;
	if(strtolower($DATA->SCHEDULE->OVERWRITE_SCHEDULE_DATE) == 'yes')
		$OVERWRITE_SCHEDULE_DATE = 1;
	else if(strtolower($DATA->SCHEDULE->OVERWRITE_SCHEDULE_DATE) == 'no')
		$OVERWRITE_SCHEDULE_DATE = 0;
	else if($DATA->SCHEDULE->OVERWRITE_SCHEDULE_DATE != '') {
		$data['SUCCESS'] = 0;
		if($data['MESSAGE'] != '')
			$data['MESSAGE'] .= ', ';
			
		$data['MESSAGE'] .= 'Invalid OVERWRITE_SCHEDULE_DATE Value';
	}
	
	$SUN_ROOM = $DATA->SCHEDULE->WEEKLY_SCHEDULE->SUNDAY->ROOM_ID;
	$MON_ROOM = $DATA->SCHEDULE->WEEKLY_SCHEDULE->MONDAY->ROOM_ID;
	$TUE_ROOM = $DATA->SCHEDULE->WEEKLY_SCHEDULE->TUESDAY->ROOM_ID;
	$WED_ROOM = $DATA->SCHEDULE->WEEKLY_SCHEDULE->WEDNESDAY->ROOM_ID;
	$THU_ROOM = $DATA->SCHEDULE->WEEKLY_SCHEDULE->THURSDAY->ROOM_ID;
	$FRI_ROOM = $DATA->SCHEDULE->WEEKLY_SCHEDULE->FRIDAY->ROOM_ID;
	$SAT_ROOM = $DATA->SCHEDULE->WEEKLY_SCHEDULE->SATURDAY->ROOM_ID;
	
	if($SUN_ROOM != '') {
		$res_st = $db->Execute("select PK_CAMPUS_ROOM from M_CAMPUS_ROOM WHERE PK_CAMPUS_ROOM = '$SUN_ROOM' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid SCHEDULE->WEEKLY_SCHEDULE->SUNDAY->ROOM_ID Value - '.$SUN_ROOM;
		}
	}
	
	if($MON_ROOM != '') {
		$res_st = $db->Execute("select PK_CAMPUS_ROOM from M_CAMPUS_ROOM WHERE PK_CAMPUS_ROOM = '$MON_ROOM' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid SCHEDULE->WEEKLY_SCHEDULE->MONDAY->ROOM_ID Value - '.$MON_ROOM;
		}
	}
	
	if($TUE_ROOM != '') {
		$res_st = $db->Execute("select PK_CAMPUS_ROOM from M_CAMPUS_ROOM WHERE PK_CAMPUS_ROOM = '$TUE_ROOM' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid SCHEDULE->WEEKLY_SCHEDULE->TUESDAY->ROOM_ID Value - '.$TUE_ROOM;
		}
	}
	
	if($WED_ROOM != '') {
		$res_st = $db->Execute("select PK_CAMPUS_ROOM from M_CAMPUS_ROOM WHERE PK_CAMPUS_ROOM = '$WED_ROOM' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid SCHEDULE->WEEKLY_SCHEDULE->WEDNESDAY->ROOM_ID Value - '.$WED_ROOM;
		}
	}
	
	if($THU_ROOM != '') {
		$res_st = $db->Execute("select PK_CAMPUS_ROOM from M_CAMPUS_ROOM WHERE PK_CAMPUS_ROOM = '$THU_ROOM' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid SCHEDULE->WEEKLY_SCHEDULE->THURSDAY->ROOM_ID Value - '.$THU_ROOM;
		}
	}
	
	if($FRI_ROOM != '') {
		$res_st = $db->Execute("select PK_CAMPUS_ROOM from M_CAMPUS_ROOM WHERE PK_CAMPUS_ROOM = '$FRI_ROOM' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid SCHEDULE->WEEKLY_SCHEDULE->FRIDAY->ROOM_ID Value - '.$FRI_ROOM;
		}
	}
	
	if($SAT_ROOM != '') {
		$res_st = $db->Execute("select PK_CAMPUS_ROOM from M_CAMPUS_ROOM WHERE PK_CAMPUS_ROOM = '$SAT_ROOM' AND PK_ACCOUNT = '$PK_ACCOUNT'");
		if($res_st->RecordCount() == 0){
			$data['SUCCESS'] = 0;
			if($data['MESSAGE'] != '')
				$data['MESSAGE'] .= ', ';
				
			$data['MESSAGE'] .= 'Invalid SCHEDULE->WEEKLY_SCHEDULE->SATURDAY->ROOM_ID Value - '.$SAT_ROOM;
		}
	}
	
	if(!empty($DATA->SCHEDULE->DAILY_SCHEDULE)){
		foreach($DATA->SCHEDULE->DAILY_SCHEDULE as $DAILY_SCHEDULE) {
			$PK_CAMPUS_ROOM1 = $DAILY_SCHEDULE->ROOM_ID;
			$COMPLETED		 = $DAILY_SCHEDULE->COMPLETED;
			
			$res_st = $db->Execute("select PK_CAMPUS_ROOM from M_CAMPUS_ROOM WHERE PK_CAMPUS_ROOM = '$PK_CAMPUS_ROOM1' AND PK_ACCOUNT = '$PK_ACCOUNT'");
			if($res_st->RecordCount() == 0){
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid SCHEDULE->DAILY_SCHEDULE->ROOM_ID Value - '.$PK_CAMPUS_ROOM1;
			}
			
			if(strtolower($COMPLETED) == 'yes')
				$COMPLETED = 1;
			else if(strtolower($COMPLETED) == 'no')
				$COMPLETED = 0;
			else if($COMPLETED != '') {
				$data['SUCCESS'] = 0;
				if($data['MESSAGE'] != '')
					$data['MESSAGE'] .= ', ';
					
				$data['MESSAGE'] .= 'Invalid SCHEDULE->DAILY_SCHEDULE->COMPLETED Value - '.$COMPLETED;
			}
		}
	}
	
	if($DATA->SCHEDULE->WEEKLY_SCHEDULE->SUNDAY->START_TIME != '')
		$SUNDAY = 1;
	if($DATA->SCHEDULE->WEEKLY_SCHEDULE->MONDAY->START_TIME != '')
		$MONDAY = 1;
	if($DATA->SCHEDULE->WEEKLY_SCHEDULE->TUESDAY->START_TIME != '')
		$TUESDAY = 1;
	if($DATA->SCHEDULE->WEEKLY_SCHEDULE->WEDNESDAY->START_TIME != '')
		$WEDNESDAY = 1;
	if($DATA->SCHEDULE->WEEKLY_SCHEDULE->THURSDAY->START_TIME != '')
		$THURSDAY = 1;
	if($DATA->SCHEDULE->WEEKLY_SCHEDULE->FRIDAY->START_TIME != '')
		$FRIDAY = 1;
	if($DATA->SCHEDULE->WEEKLY_SCHEDULE->SATURDAY->START_TIME != '')
		$SATURDAY = 1;

	if($data['SUCCESS'] == 1) {
		
		$COURSE_OFFERING['PK_COURSE']  					= $PK_COURSE;
		$COURSE_OFFERING['PK_CAMPUS']  					= $PK_CAMPUS;
		$COURSE_OFFERING['PK_TERM_MASTER']  			= $PK_TERM_MASTER;
		$COURSE_OFFERING['INSTRUCTOR']  				= $INSTRUCTOR;
		$COURSE_OFFERING['PK_CAMPUS_ROOM']  			= $PK_CAMPUS_ROOM;
		$COURSE_OFFERING['CLASS_SIZE']  				= $CLASS_SIZE;
		$COURSE_OFFERING['PK_SESSION']  				= $PK_SESSION;
		$COURSE_OFFERING['SESSION_NO']  				= $SESSION_NO;
		$COURSE_OFFERING['PK_ATTENDANCE_TYPE']  		= $PK_ATTENDANCE_TYPE;
		$COURSE_OFFERING['PK_ATTENDANCE_CODE']  		= $PK_ATTENDANCE_CODE;
		$COURSE_OFFERING['OLD_DIAMOND_ID']  			= $OLD_DIAMOND_ID;
		$COURSE_OFFERING['PK_COURSE_OFFERING_STATUS']  	= $PK_COURSE_OFFERING_STATUS;
		
		$COURSE_OFFERING['PK_ACCOUNT']  		= $PK_ACCOUNT;
		$COURSE_OFFERING['CREATED_ON']  		= date("Y-m-d H:i");
		db_perform('S_COURSE_OFFERING', $COURSE_OFFERING, 'insert');
		$PK_COURSE_OFFERING = $db->insert_ID();
		
		if($ASSISTANT > 0){
			$COURSE_OFFERING_ASSISTANT['PK_COURSE_OFFERING'] 	= $PK_COURSE_OFFERING;
			$COURSE_OFFERING_ASSISTANT['ASSISTANT'] 			= $ASSISTANT;
			$COURSE_OFFERING_ASSISTANT['PK_ACCOUNT'] 			= $PK_ACCOUNT;
			$COURSE_OFFERING_ASSISTANT['CREATED_ON'] 			= date("Y-m-d H:i:s");
			db_perform('S_COURSE_OFFERING_ASSISTANT', $COURSE_OFFERING_ASSISTANT, 'insert');
		}
		
		$OFFERING_SCHEDULE['SCHEDULE_ON_HOLIDAY']  		= $SCHEDULE_ON_HOLIDAY;
		$OFFERING_SCHEDULE['OVERWRITE_SCHEDULE_DATE']  	= $OVERWRITE_SCHEDULE_DATE;
		$OFFERING_SCHEDULE['START_DATE']  				= $DATA->SCHEDULE->DEFAULT_SCHEDULE->START_DATE;
		$OFFERING_SCHEDULE['END_DATE']  				= $DATA->SCHEDULE->DEFAULT_SCHEDULE->END_DATE;
		$OFFERING_SCHEDULE['DEF_START_TIME']  			= $DATA->SCHEDULE->DEFAULT_SCHEDULE->START_TIME;
		$OFFERING_SCHEDULE['DEF_END_TIME']  			= $DATA->SCHEDULE->DEFAULT_SCHEDULE->END_TIME;
		$OFFERING_SCHEDULE['DEF_HOURS']  				= $DATA->SCHEDULE->DEFAULT_SCHEDULE->HOURS;
		
		$OFFERING_SCHEDULE['SUNDAY']  				= $SUNDAY;
		$OFFERING_SCHEDULE['MONDAY']  				= $MONDAY;
		$OFFERING_SCHEDULE['TUESDAY']  				= $TUESDAY;
		$OFFERING_SCHEDULE['WEDNESDAY'] 			= $WEDNESDAY;
		$OFFERING_SCHEDULE['THURSDAY']  			= $THURSDAY;
		$OFFERING_SCHEDULE['FRIDAY']  				= $FRIDAY;
		$OFFERING_SCHEDULE['SATURDAY']  			= $SATURDAY;
		
		$OFFERING_SCHEDULE['SUN_ROOM']  			= $SUN_ROOM;
		$OFFERING_SCHEDULE['MON_ROOM']  			= $MON_ROOM;
		$OFFERING_SCHEDULE['TUE_ROOM']  			= $TUE_ROOM;
		$OFFERING_SCHEDULE['WED_ROOM']  			= $WED_ROOM;
		$OFFERING_SCHEDULE['THU_ROOM']  			= $THU_ROOM;
		$OFFERING_SCHEDULE['FRI_ROOM']  			= $FRI_ROOM;
		$OFFERING_SCHEDULE['SAT_ROOM']  			= $SAT_ROOM;
		
		$OFFERING_SCHEDULE['SUN_START_TIME'] 		= $DATA->SCHEDULE->WEEKLY_SCHEDULE->SUNDAY->START_TIME;
		$OFFERING_SCHEDULE['SUN_END_TIME']  		= $DATA->SCHEDULE->WEEKLY_SCHEDULE->SUNDAY->END_TIME;
		$OFFERING_SCHEDULE['SUN_HOURS']  			= $DATA->SCHEDULE->WEEKLY_SCHEDULE->SUNDAY->HOURS;
		
		$OFFERING_SCHEDULE['MON_START_TIME'] 		= $DATA->SCHEDULE->WEEKLY_SCHEDULE->MONDAY->START_TIME;
		$OFFERING_SCHEDULE['MON_END_TIME']  		= $DATA->SCHEDULE->WEEKLY_SCHEDULE->MONDAY->END_TIME;
		$OFFERING_SCHEDULE['MON_HOURS']  			= $DATA->SCHEDULE->WEEKLY_SCHEDULE->MONDAY->HOURS;
		
		$OFFERING_SCHEDULE['TUE_START_TIME'] 		= $DATA->SCHEDULE->WEEKLY_SCHEDULE->TUESDAY->START_TIME;
		$OFFERING_SCHEDULE['TUE_END_TIME']  		= $DATA->SCHEDULE->WEEKLY_SCHEDULE->TUESDAY->END_TIME;
		$OFFERING_SCHEDULE['TUE_HOURS']  			= $DATA->SCHEDULE->WEEKLY_SCHEDULE->TUESDAY->HOURS;
		
		$OFFERING_SCHEDULE['WED_START_TIME'] 		= $DATA->SCHEDULE->WEEKLY_SCHEDULE->WEDNESDAY->START_TIME;
		$OFFERING_SCHEDULE['WED_END_TIME']  		= $DATA->SCHEDULE->WEEKLY_SCHEDULE->WEDNESDAY->END_TIME;
		$OFFERING_SCHEDULE['WED_HOURS']  			= $DATA->SCHEDULE->WEEKLY_SCHEDULE->WEDNESDAY->HOURS;
		
		$OFFERING_SCHEDULE['THU_START_TIME'] 		= $DATA->SCHEDULE->WEEKLY_SCHEDULE->THURSDAY->START_TIME;
		$OFFERING_SCHEDULE['THU_END_TIME']  		= $DATA->SCHEDULE->WEEKLY_SCHEDULE->THURSDAY->END_TIME;
		$OFFERING_SCHEDULE['THU_HOURS']  			= $DATA->SCHEDULE->WEEKLY_SCHEDULE->THURSDAY->HOURS;
		
		$OFFERING_SCHEDULE['FRI_START_TIME'] 		= $DATA->SCHEDULE->WEEKLY_SCHEDULE->FRIDAY->START_TIME;
		$OFFERING_SCHEDULE['FRI_END_TIME']  		= $DATA->SCHEDULE->WEEKLY_SCHEDULE->FRIDAY->END_TIME;
		$OFFERING_SCHEDULE['FRI_HOURS']  			= $DATA->SCHEDULE->WEEKLY_SCHEDULE->FRIDAY->HOURS;
		
		$OFFERING_SCHEDULE['SAT_START_TIME'] 		= $DATA->SCHEDULE->WEEKLY_SCHEDULE->SATURDAY->START_TIME;
		$OFFERING_SCHEDULE['SAT_END_TIME']  		= $DATA->SCHEDULE->WEEKLY_SCHEDULE->SATURDAY->END_TIME;
		$OFFERING_SCHEDULE['SAT_HOURS']  			= $DATA->SCHEDULE->WEEKLY_SCHEDULE->SATURDAY->HOURS;
	
		$OFFERING_SCHEDULE['PK_COURSE_OFFERING']  	= $PK_COURSE_OFFERING;
		$OFFERING_SCHEDULE['PK_ACCOUNT']  			= $PK_ACCOUNT;
		$OFFERING_SCHEDULE['CREATED_ON']  			= date("Y-m-d H:i");
		db_perform('S_COURSE_OFFERING_SCHEDULE', $OFFERING_SCHEDULE, 'insert');
		$PK_COURSE_OFFERING_SCHEDULE = $db->insert_ID();
		
		foreach($DATA->SCHEDULE->DAILY_SCHEDULE as $DAILY_SCHEDULE) {
			$PK_CAMPUS_ROOM1 = $DAILY_SCHEDULE->ROOM_ID;
			$COMPLETED		 = $DAILY_SCHEDULE->COMPLETED;
			
			if(strtolower($COMPLETED) == 'yes')
				$COMPLETED = 1;
			else if(strtolower($COMPLETED) == 'no')
				$COMPLETED = 0;
			
			$SCHEDULE_DETAIL['OLD_DSIS_SCHEDULE_ID']  		= $DAILY_SCHEDULE->OLD_DSIS_SCHEDULE_ID;
			$SCHEDULE_DETAIL['SCHEDULE_DATE']  				= $DAILY_SCHEDULE->DATE;
			$SCHEDULE_DETAIL['START_TIME']  				= $DAILY_SCHEDULE->START_TIME;
			$SCHEDULE_DETAIL['END_TIME']  					= $DAILY_SCHEDULE->END_TIME;
			$SCHEDULE_DETAIL['HOURS']  						= $DAILY_SCHEDULE->HOURS;
			$SCHEDULE_DETAIL['PK_CAMPUS_ROOM']  			= $DAILY_SCHEDULE->ROOM_ID;
			$SCHEDULE_DETAIL['COMPLETED']  					= $COMPLETED;
			$SCHEDULE_DETAIL['PK_COURSE']  					= $COURSE_OFFERING['PK_COURSE'];
			$SCHEDULE_DETAIL['PK_COURSE_OFFERING_SCHEDULE'] = $PK_COURSE_OFFERING_SCHEDULE;
			$SCHEDULE_DETAIL['PK_COURSE_OFFERING']  		= $PK_COURSE_OFFERING;
			$SCHEDULE_DETAIL['PK_ACCOUNT']  				= $PK_ACCOUNT;
			$SCHEDULE_DETAIL['CREATED_ON']  				= date("Y-m-d H:i");
			db_perform('S_COURSE_OFFERING_SCHEDULE_DETAIL', $SCHEDULE_DETAIL, 'insert');
		}
		
		$data['MESSAGE'] = 'Course Offering Created';
		$data['INTERNAL_ID'] = $PK_COURSE_OFFERING;
		
	}
}

$data = json_encode($data);
echo $data;