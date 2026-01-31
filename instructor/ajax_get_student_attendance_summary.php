<? require_once("../global/config.php"); 
require_once("../language/instructor_attendance_detail.php");
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING = $_REQUEST['val']; 
$PK_TERM_MASTER 	= $_REQUEST['tid']; 

$present_att_code_arr = array();
$res_present_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
while (!$res_present_att_code->EOF) {
	$present_att_code_arr[] = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
	$res_present_att_code->MoveNext();
}

$excluded_att_code  = "";
$exc_att_code_arr = array();
$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
while (!$res_exc_att_code->EOF) {
	$exc_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
	$res_exc_att_code->MoveNext();
}

$absent_att_code  = "";
$absent_att_code_arr = array();
$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ABSENT = 1");
while (!$res_exc_att_code->EOF) {
	$absent_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
	$res_exc_att_code->MoveNext();
}
?>

<table class="table-bordered" cellpadding=6 width="100%" >
	<thead>
		<tr>
			<th style="padding: 2px;text-align:center;" ><?=STUDENT?></th>
			<th style="padding: 2px;text-align:center;" ><?=HOURS_ATTENDED?></th>
			<th style="padding: 2px;text-align:center;" ><?=HOURS_SCHEDULED?></th>
			<th style="padding: 2px;text-align:center;" ><?=ATTENDANCE_PERCENTAGE?></th>
			<th style="padding: 2px;text-align:center;" ><?=ABSENTS?></th>
			<th style="padding: 2px;text-align:center;" ><?=DAYS_ABSENTS?></th>
			<th style="padding: 2px;text-align:center;" ><?=HOME_PHONE?></th>
			<th style="padding: 2px;text-align:center;" ><?=MOBILE_PHONE?></th>
			<th style="padding: 2px;text-align:center;" ><?=EMAIL?></th>
		</tr>
	</thead>
	<tbody>
		<? $res_cs = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME, S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT, S_STUDENT_COURSE.PK_STUDENT_COURSE from S_STUDENT_COURSE, S_STUDENT_MASTER WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC");
		while (!$res_cs->EOF) { 
			$PK_STUDENT_MASTER 		= $res_cs->fields['PK_STUDENT_MASTER']; 
			$PK_STUDENT_ENROLLMENT 	= $res_cs->fields['PK_STUDENT_ENROLLMENT']; 
			$PK_STUDENT_COURSE 		= $res_cs->fields['PK_STUDENT_COURSE'];
			
			$res_course_schedule = $db->Execute("select S_STUDENT_SCHEDULE.SCHEDULE_DATE,S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE, S_STUDENT_SCHEDULE.END_TIME, S_STUDENT_SCHEDULE.HOURS, SCHEDULE_TYPE, IF(S_STUDENT_ATTENDANCE.COMPLETED = 1,'Y','N') as COMPLETED, S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED AS SCHEDULE_COMPLETED , M_ATTENDANCE_CODE.CODE AS ATTENDANCE_CODE, ATTENDANCE_HOURS, S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE
			from 

			S_STUDENT_SCHEDULE 
			LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL On S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL  
			LEFT JOIN M_SCHEDULE_TYPE ON M_SCHEDULE_TYPE.PK_SCHEDULE_TYPE = S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE
			LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
			LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
			
			LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE
			LEFT JOIN M_ATTENDANCE_CODE ON  M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE

			WHERE 
			S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND 
			S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND 
			S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

			$total_absent_count			= 0;
			$total_absent 				= 0;
			$total_scheduled 			= 0;
			$total_completed_scheduled 	= 0;
			$total_attended 			= 0;
			$cum_total					= 0;
			while (!$res_course_schedule->EOF) { 
				$present_flag = 0;
				foreach($present_att_code_arr as $present_att_code) {
					if($present_att_code == $res_course_schedule->fields['PK_ATTENDANCE_CODE']) {
						$present_flag = 1;
						break;
					}
				}
				
				if($res_course_schedule->fields['COMPLETED'] == 'Y' || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2) {
					if($present_flag == 1) {
						$total_attended += $res_course_schedule->fields['ATTENDANCE_HOURS'];
						$cum_total		+= $res_course_schedule->fields['ATTENDANCE_HOURS'];
					}
				}
				
				$exc_att_flag = 0;
				foreach($exc_att_code_arr as $exc_att_code) {
					if($exc_att_code == $res_course_schedule->fields['PK_ATTENDANCE_CODE']) {
						$exc_att_flag = 1;
						break;
					}
				}
				
				$absent_att_flag = 0;
				foreach($absent_att_code_arr as $exc_att_code) {
					if($exc_att_code == $res_course_schedule->fields['PK_ATTENDANCE_CODE']) {
						$absent_att_flag = 1;
						break;
					}
				}
				if($res_course_schedule->fields['COMPLETED'] == 'Y' || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2) {
					if($absent_att_flag == 1) {
						$total_absent += $res_course_schedule->fields['HOURS'];
						$total_absent_count++;
					}
				}
				
				
				if($res_course_schedule->fields['PK_ATTENDANCE_CODE'] != 7 && $exc_att_flag == 0){
					if($res_course_schedule->fields['COMPLETED'] == 'Y' || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2 ) { 
						$total_scheduled	 += $res_course_schedule->fields['HOURS'];
						$total_completed_scheduled += $res_course_schedule->fields['HOURS'];	
					}
				}	
	
				if($res_course_schedule->fields['COMPLETED'] == 'N')
					$ATTENDANCE_CODE = 'P';
				else
					$ATTENDANCE_CODE = $res_course_schedule->fields['ATTENDANCE_CODE']; 
					
				$res_course_schedule->MoveNext();
			} 
			
			$res = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' ");  ?>
			<tr>
				<td><?=$res_cs->fields['NAME']?></td>
				<td><?=number_format_value_checker($total_attended,2)?></td>
				<td><?=number_format_value_checker($total_scheduled,2)?></td>
				<td><?=number_format_value_checker(($total_attended / $total_scheduled * 100),2)?></td>
				<td><?=number_format_value_checker(($total_absent),2)?></td>
				<td><?=number_format_value_checker(($total_absent_count),2)?></td>
				<td><?=$res->fields['HOME_PHONE']?></td>
				<td><?=$res->fields['CELL_PHONE']?></td>
				<td><?=$res->fields['EMAIL']?></td>
			</tr>
		<?	$res_cs->MoveNext();
		} ?>
	</tbody>
</table>