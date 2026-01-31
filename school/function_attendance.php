<? include_once("../global/mail.php"); // Ticket # 1822
include_once("../global/texting.php"); // Ticket # 1822
function attendance_log($logtype, $logvalue) {
	$dataOut =  date('Y-m-d H:i:s') . "     " . $logtype . "     " . $logvalue . "\n";
	//stop writing logs for checkout process as we don't required this logs
	$path = env('REAL_PATH').'school/temp/';
	$logFileName = $path."attendance.txt";			
	$logFile = fopen($logFileName, 'a+') or die("can't open file");
	if ($logtype == "init store") fwrite($logFile, "\n");
	fwrite($logFile, $dataOut);
	fclose($logFile);

}

function attendance_entry($PK_COURSE_OFFERING_SCHEDULE_DETAIL,$COMPLETE,$PK_STUDENT_ATTENDANCE,$PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT,$PK_STUDENT_SCHEDULE,$ATTENDANCE_HOURS, $PK_ATTENDANCE_CODE,$PK_ACCOUNT,$PK_USER,$update_completed = 0){
	global $db;

	$res_sec = $db->Execute("SELECT PK_STUDENT_SCHEDULE,PK_SCHEDULE_TYPE FROM S_STUDENT_SCHEDULE WHERE PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' AND PK_ACCOUNT = '$PK_ACCOUNT'");
	if($res_sec->RecordCount() > 0) {
		$cond = "";
		if($res_sec->fields['PK_SCHEDULE_TYPE'] == 1 && $update_completed == 0)
			$cond = " AND COMPLETED = 0 ";
		
		//Ticket # 1100
		$res_att_pre = $db->Execute("SELECT PK_STUDENT_ATTENDANCE FROM S_STUDENT_ATTENDANCE WHERE PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
		if($res_att_pre->RecordCount() == 0)
			$PK_STUDENT_ATTENDANCE = '';
		else
			$PK_STUDENT_ATTENDANCE = $res_att_pre->fields['PK_STUDENT_ATTENDANCE'];
			
		$ATTENDANCE_DETAIL = array();
		if($PK_ATTENDANCE_CODE==1){
			$ATTENDANCE_HOURS=0;
		}
		$ATTENDANCE_DETAIL['PK_STUDENT_MASTER'] 					= $PK_STUDENT_MASTER;
		$ATTENDANCE_DETAIL['PK_STUDENT_ENROLLMENT'] 				= $PK_STUDENT_ENROLLMENT;
		$ATTENDANCE_DETAIL['PK_STUDENT_SCHEDULE'] 					= $PK_STUDENT_SCHEDULE;
		$ATTENDANCE_DETAIL['ATTENDANCE_HOURS'] 						= $ATTENDANCE_HOURS;
		$ATTENDANCE_DETAIL['PK_ATTENDANCE_CODE'] 					= $PK_ATTENDANCE_CODE;
		$ATTENDANCE_DETAIL['PK_COURSE_OFFERING_SCHEDULE_DETAIL'] 	= $PK_COURSE_OFFERING_SCHEDULE_DETAIL;
		$ATTENDANCE_DETAIL['COMPLETED']   							= $COMPLETE;
		if($PK_STUDENT_ATTENDANCE == '') {
			$ATTENDANCE_DETAIL['PK_ACCOUNT']  	= $PK_ACCOUNT;
			$ATTENDANCE_DETAIL['CREATED_BY']  	= $PK_USER;
			$ATTENDANCE_DETAIL['CREATED_ON']  	= date("Y-m-d H:i");
			db_perform('S_STUDENT_ATTENDANCE', $ATTENDANCE_DETAIL, 'insert');
			$PK_STUDENT_ATTENDANCE = $db->insert_ID();
			
			/* Ticket # 1120   */
			$OLD_ATTENDANCE_HOURS 	= "";
			$OLD_PK_ATTENDANCE_CODE = "";
			/* Ticket # 1120   */
			
		} else {
			/* Ticket # 1120   */
			$res_att_old = $db->Execute("SELECT ATTENDANCE_HOURS, PK_ATTENDANCE_CODE  FROM S_STUDENT_ATTENDANCE WHERE PK_STUDENT_ATTENDANCE = '$PK_STUDENT_ATTENDANCE' AND PK_ACCOUNT = '$PK_ACCOUNT' $cond"); 
			$OLD_ATTENDANCE_HOURS 	= $res_att_old->fields['ATTENDANCE_HOURS'];
			$OLD_PK_ATTENDANCE_CODE = $res_att_old->fields['PK_ATTENDANCE_CODE'];
			/* Ticket # 1120   */
			
			$ATTENDANCE_DETAIL['EDITED_BY']  = $PK_USER;
			$ATTENDANCE_DETAIL['EDITED_ON']  = date("Y-m-d H:i");

			attendance_log("attendance entry = ",json_encode($ATTENDANCE_DETAIL));
			db_perform('S_STUDENT_ATTENDANCE', $ATTENDANCE_DETAIL, 'update'," PK_STUDENT_ATTENDANCE = '$PK_STUDENT_ATTENDANCE' AND PK_ACCOUNT = '$PK_ACCOUNT' $cond ");
		}
//echo "<pre>".$PK_STUDENT_ATTENDANCE;print_r($ATTENDANCE_DETAIL);exit;
		if($COMPLETE == 1){
			$db->Execute("UPDATE S_COURSE_OFFERING_SCHEDULE_DETAIL SET COMPLETED = 1 WHERE PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$PK_COURSE_OFFERING_SCHEDULE_DETAIL' AND PK_ACCOUNT = '$PK_ACCOUNT' "); 
			
			/* Ticket # 1822 */
			$res_att = $db->Execute("SELECT ABSENT FROM S_ATTENDANCE_CODE WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_ATTENDANCE_CODE = '$PK_ATTENDANCE_CODE' "); 
			if($res_att->fields['ABSENT'] == 1 && $OLD_PK_ATTENDANCE_CODE != $PK_ATTENDANCE_CODE){ //Ticket # 1872
				$res_noti = $db->Execute("SELECT PK_EMAIL_TEMPLATE,PK_TEXT_TEMPLATE FROM S_NOTIFICATION_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EVENT_TYPE = 19");
				if($res_noti->RecordCount() > 0) {
					if($res_noti->fields['PK_EMAIL_TEMPLATE'] > 0) {
						send_attendance_absent_mail($PK_STUDENT_ATTENDANCE,$res_noti->fields['PK_EMAIL_TEMPLATE']);
					}
					
					if($res_noti->fields['PK_TEXT_TEMPLATE'] > 0) {
						send_attendance_absent_text($PK_STUDENT_ATTENDANCE,$res_noti->fields['PK_TEXT_TEMPLATE']);
					}
				}
			}
			/* Ticket # 1822 */
		}
		
		return $PK_STUDENT_ATTENDANCE;
	}
}

function create_non_schedule($PK_STUDENT_SCHEDULE,$PK_COURSE_OFFERING,$CLASS_DATE,$START_TIME,$END_TIME,$HOURS,$PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT, $COMPLETED,$PK_ACCOUNT,$PK_USER){
	global $db;

	$res_co = $db->Execute("SELECT PK_CAMPUS_ROOM FROM S_COURSE_OFFERING WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$PK_ACCOUNT' "); 
	$res_sc = $db->Execute("SELECT PK_STUDENT_COURSE FROM S_STUDENT_COURSE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
	
	$STUDENT_SCHEDULE = array();
	$STUDENT_SCHEDULE['SCHEDULE_DATE'] 		= $CLASS_DATE;
	$STUDENT_SCHEDULE['START_TIME'] 		= $START_TIME;
	$STUDENT_SCHEDULE['END_TIME'] 	 		= $END_TIME;
	
	if($STUDENT_SCHEDULE['SCHEDULE_DATE'] != '')
		$STUDENT_SCHEDULE['SCHEDULE_DATE'] = date("Y-m-d",strtotime($STUDENT_SCHEDULE['SCHEDULE_DATE']));
		
	if($STUDENT_SCHEDULE['START_TIME'] != '')
		$STUDENT_SCHEDULE['START_TIME'] = date("H:i:s",strtotime($STUDENT_SCHEDULE['START_TIME']));
		
	if($STUDENT_SCHEDULE['END_TIME'] != '')
		$STUDENT_SCHEDULE['END_TIME'] = date("H:i:s",strtotime($STUDENT_SCHEDULE['END_TIME']));
	
	if($HOURS != '')
		$STUDENT_SCHEDULE['HOURS'] = $HOURS;
	else {
		//Ticket #670
		// $starttimestamp = strtotime($STUDENT_SCHEDULE['START_TIME']);
		// $endtimestamp 	= strtotime($STUDENT_SCHEDULE['END_TIME']);
		// $STUDENT_SCHEDULE['HOURS'] = number_format((abs($endtimestamp - $starttimestamp)/3600),2);

		$starttimestamp = $STUDENT_SCHEDULE['START_TIME'];
		$endtimestamp 	= $STUDENT_SCHEDULE['END_TIME'];
		$sst = strtotime($starttimestamp);
		$eet=  strtotime($endtimestamp);
		$diff= $eet-$sst;
		$timeElapsed= gmdate("H:i",$diff); //H-24 hours
		$timeElapsed = str_replace(":",".",$timeElapsed);
		//$STUDENT_SCHEDULE['HOURS'] = number_format(abs($timeElapsed),2);
		$calculated_time = number_format(abs($timeElapsed),2);
		if($calculated_time < 1){
			$STUDENT_SCHEDULE['HOURS'] = number_format((($calculated_time *100)/60),2);
		}else{
			//$STUDENT_SCHEDULE['HOURS'] = $calculated_time;
			$exptimeElapsed = explode(".",$calculated_time);
			$hours =$exptimeElapsed[0]; 
			$minutes=$exptimeElapsed[1] ; 
			$CalculateHours = $hours + round($minutes / 60, 2); 
			$STUDENT_SCHEDULE['HOURS']=number_format($CalculateHours,2);
		}
		//Ticket #670
	}
	
	if($PK_STUDENT_SCHEDULE == '') {
		$STUDENT_SCHEDULE['PK_SCHEDULE_TYPE'] 		 = 2;
		$STUDENT_SCHEDULE['PK_CAMPUS_ROOM'] 		 = $res_co->fields['PK_CAMPUS_ROOM'];
		$STUDENT_SCHEDULE['PK_STUDENT_COURSE'] 	 	 = $res_sc->fields['PK_STUDENT_COURSE'];		
		$STUDENT_SCHEDULE['PK_STUDENT_ENROLLMENT'] 	 = $PK_STUDENT_ENROLLMENT;
		$STUDENT_SCHEDULE['PK_STUDENT_MASTER'] 	 	 = $PK_STUDENT_MASTER;
		$STUDENT_SCHEDULE['PK_ACCOUNT'] 			 = $PK_ACCOUNT;
		$STUDENT_SCHEDULE['CREATED_BY']  			 = $PK_USER;
		$STUDENT_SCHEDULE['CREATED_ON']  			 = date("Y-m-d H:i");
		db_perform('S_STUDENT_SCHEDULE', $STUDENT_SCHEDULE, 'insert');
		$PK_STUDENT_SCHEDULE = $db->insert_ID();
	} else {
		$STUDENT_SCHEDULE['EDITED_BY']  			 = $PK_USER;
		$STUDENT_SCHEDULE['EDITED_ON']  			 = date("Y-m-d H:i");
		db_perform('S_STUDENT_SCHEDULE', $STUDENT_SCHEDULE, 'update'," PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
	}
	
	return $PK_STUDENT_SCHEDULE;
}
