<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');

if($REGISTRAR_ACCESS == 0){
	header("location:../index");
	exit;
}

$PK_STUDENT_MASTER 	= $_REQUEST['sid']; 
$att_cond = "";
if($_REQUEST['eid'] != '')
	$att_cond .= " AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$_REQUEST[eid]' ";
if($_REQUEST['att_code'] != '')
	$att_cond .= " AND  S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE  IN ($_REQUEST[att_code])  ";
if($_REQUEST['pkco'] != '')
	$att_cond .= " AND S_STUDENT_COURSE.PK_COURSE_OFFERING = '$_REQUEST[pkco]' ";
	
/*if($_REQUEST['completed'] == 1)
	$att_cond .= " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = '1' ";
else if($_REQUEST['completed'] == 2)
	$att_cond .= " AND S_COURSE_OFFERING_SCHEDULE_DETAIL.COMPLETED = '0' ";
	*/
	
if($_REQUEST['completed'] == 1)
	$att_cond .= " AND S_STUDENT_ATTENDANCE.COMPLETED = '1' ";
else if($_REQUEST['completed'] == 2)
	$att_cond .= " AND S_STUDENT_ATTENDANCE.COMPLETED = '0' ";	
	
if($_REQUEST['attendance_type'] == 1)
	$att_cond .= " AND S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE = '1' ";
else if($_REQUEST['attendance_type'] == 2)
	$att_cond .= " AND S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE = '2' ";

if($_REQUEST['st'] != '' && $_REQUEST['et'] != '') {
	$FROM_DATE 	= date('Y-m-d',strtotime($_REQUEST['st']));
	$TO_DATE 	= date('Y-m-d',strtotime($_REQUEST['et']));
	$att_cond .= " AND DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%Y-%m-%d') BETWEEN '$FROM_DATE' AND '$TO_DATE' ";
} else if($_REQUEST['st'] != ''){
	$FROM_DATE 	= date('Y-m-d',strtotime($_REQUEST['st']));
	$att_cond .= " AND DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%Y-%m-%d') >= '$FROM_DATE'  ";
} else if($_REQUEST['et'] != ''){
	$TO_DATE 	= date('Y-m-d',strtotime($_REQUEST['et']));
	$att_cond .= " AND DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%Y-%m-%d') <= '$TO_DATE'  ";
}

if($_REQUEST['attendance_type_summary'] == 1)
	$_REQUEST['detail_view'] = 0;

if($_REQUEST['detail_view'] == 1) {
	$group_by	= "";
	$field 		= ",ATTENDANCE_HOURS ";
} else {
	$field 		= " ,SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS ";
	if($_REQUEST['attendance_type_summary'] == 1){
		$group_by	= " GROUP BY S_STUDENT_COURSE.PK_COURSE_OFFERING, S_STUDENT_ATTENDANCE.PK_ATTENDANCE_ACTIVITY_TYPESS ";
	} else {
		$group_by	= " GROUP BY S_STUDENT_COURSE.PK_COURSE_OFFERING ";
	}
}

$present_att_code_arr = array();
$res_present_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PRESENT = 1");
while (!$res_present_att_code->EOF) {
	$present_att_code_arr[] = $res_present_att_code->fields['PK_ATTENDANCE_CODE'];
	$res_present_att_code->MoveNext();
}

if($_REQUEST['detail_view'] == 0) {
	$att_cond .= " AND (S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE = 2 OR S_STUDENT_ATTENDANCE.COMPLETED = 1) ";
	if(!empty($present_att_code_arr))
		$att_cond .= " AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE IN (".implode(",", $present_att_code_arr).") ";
}

$exc_att_code_arr = array();
$res_exc_att_code = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND CANCELLED = 1");
while (!$res_exc_att_code->EOF) {
	$exc_att_code_arr[] = $res_exc_att_code->fields['PK_ATTENDANCE_CODE'];
	$res_exc_att_code->MoveNext();
}

/* Ticket # 1298 */
$sort_order = " S_STUDENT_SCHEDULE.SCHEDULE_DATE ASC, S_STUDENT_SCHEDULE.START_TIME ASC ";
if($_REQUEST['detail_view'] == 0)
	$sort_order = " S_TERM_MASTER.BEGIN_DATE ASC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC ";

//Ticket # 1740 Ticket # 1601 DIAM-1422
$query = "select CONCAT(LAST_NAME,', ',FIRST_NAME) as STUD_NAME, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS BEGIN_DATE_1, IF(S_STUDENT_SCHEDULE.SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%m/%d/%Y'),'') AS SCHEDULE_DATE, IF(S_STUDENT_SCHEDULE.END_TIME != '00:00:00', DATE_FORMAT(S_STUDENT_SCHEDULE.END_TIME,'%h:%i %p'),'') AS END_TIME, IF(S_STUDENT_SCHEDULE.START_TIME != '00:00:00', DATE_FORMAT(S_STUDENT_SCHEDULE.START_TIME,'%h:%i %p'),'') AS START_TIME, S_STUDENT_SCHEDULE.HOURS, CONCAT(S_COURSE.COURSE_CODE, ' - ', S_COURSE.TRANSCRIPT_CODE) as COURSE_CODE, SCHEDULE_TYPE, S_STUDENT_ATTENDANCE.COMPLETED AS COMPLETED_1, IF(S_STUDENT_ATTENDANCE.COMPLETED = 1,'Y','') as COMPLETED , M_ATTENDANCE_CODE.CODE AS ATTENDANCE_CODE, SESSION, SESSION_NO, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE,  S_STUDENT_ATTENDANCE.PK_STUDENT_ATTENDANCE, S_COURSE_OFFERING.PK_TERM_MASTER, S_COURSE_OFFERING.PK_COURSE_OFFERING, S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE, S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL, IF(S_STUDENT_ATTENDANCE.PK_STUDENT_ATTENDANCE > 0, M_ATTENDANCE_ACTIVITY_TYPE_ATT.ATTENDANCE_ACTIVITY_TYPE, M_ATTENDANCE_ACTIVITY_TYPE_SCH.ATTENDANCE_ACTIVITY_TYPE) as ATTENDANCE_ACTIVITY_TYPE, S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT, S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE, ATTENDANCE_COMMENTS,S_COURSE_OFFERING.PK_CAMPUS $field from 

S_STUDENT_MASTER, S_STUDENT_SCHEDULE 
LEFT JOIN M_SCHEDULE_TYPE ON M_SCHEDULE_TYPE.PK_SCHEDULE_TYPE = S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE
LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE 
LEFT JOIN M_ATTENDANCE_ACTIVITY_TYPE as M_ATTENDANCE_ACTIVITY_TYPE_ATT ON  M_ATTENDANCE_ACTIVITY_TYPE_ATT.PK_ATTENDANCE_ACTIVITY_TYPE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_ACTIVITY_TYPESS  
LEFT JOIN M_ATTENDANCE_CODE ON  M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE
LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON  S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL 
LEFT JOIN M_ATTENDANCE_ACTIVITY_TYPE as M_ATTENDANCE_ACTIVITY_TYPE_SCH ON  M_ATTENDANCE_ACTIVITY_TYPE_SCH.PK_ATTENDANCE_ACTIVITY_TYPE = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_ATTENDANCE_ACTIVITY_TYPES   
WHERE 
S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $att_cond $group_by ORDER BY $sort_order ";
/* Ticket # 1298 */
$res_course_schedule = $db->Execute($query);
$_SESSION['query'] 	 = $query;
//echo $_SESSION['query'];
$TOTAL_HOURS 		= 0;
$ATTENDANCE_HOURS 	= 0;

$res_att_act = $db->Execute("SELECT ENABLE_ATTENDANCE_ACTIVITY_TYPES, ENABLE_ATTENDANCE_COMMENTS FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); //Ticket # 1037 Ticket # 1601 
while (!$res_course_schedule->EOF) { 
	$exc_att_flag = 0;
	foreach($exc_att_code_arr as $exc_att_code) {
		if($exc_att_code == $res_course_schedule->fields['PK_ATTENDANCE_CODE']) {
			$exc_att_flag = 1;
			break;
		}
	}
	
	$present_flag = 0;
	foreach($present_att_code_arr as $present_att_code) {
		if($present_att_code == $res_course_schedule->fields['PK_ATTENDANCE_CODE']) {
			$present_flag = 1;
			break;
		}
	}
	if($_REQUEST['show_inactive'] == 1 || ($_REQUEST['show_inactive'] == 0 && $res_course_schedule->fields['ATTENDANCE_CODE'] != 'I') ){ 
		if($res_course_schedule->fields['COMPLETED_1'] == 1 || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2){
			if($present_flag == 1) {
				$ATTENDANCE_HOURS 	+= $res_course_schedule->fields['ATTENDANCE_HOURS'];
			}
		}
		if($res_course_schedule->fields['ATTENDANCE_CODE'] != 'I' && $exc_att_flag == 0)
			$TOTAL_HOURS += $res_course_schedule->fields['HOURS']; 
			
		$PK_STUDENT_SCHEDULE = $res_course_schedule->fields['PK_STUDENT_SCHEDULE']; ?>
	<tr>
		<td>
			<div style="width:200px"><? if($_REQUEST['detail_view'] == 1) echo $res_course_schedule->fields['COURSE_CODE']; else echo $res_course_schedule->fields['COURSE_CODE'].' ('. $res_course_schedule->fields['SESSION'].' - '. $res_course_schedule->fields['SESSION_NO'].')' ;?></div><!-- Ticket # 1740  -->
		</td>
		<? /* Ticket # 1298 */
		if($_REQUEST['detail_view'] == 0){ ?>
			<td>
				<?=$res_course_schedule->fields['BEGIN_DATE_1']; ?>
			</td>
		<? } 
		 /* Ticket # 1298 */ ?>
		<td>
			<div style="width:80px"><? if($_REQUEST['detail_view'] == 1) echo $res_course_schedule->fields['SCHEDULE_TYPE']; else echo "Summary";?></div>
		</td>
		<td><div style="width:80px"><? if($_REQUEST['detail_view'] == 1) echo $res_course_schedule->fields['SCHEDULE_DATE']; ?></div></td><!-- Ticket # 1740  -->
		<td><div style="width:80px"><? 
		if($_REQUEST['detail_view'] == 1) {
			$start_time_fix =  $res_course_schedule->fields['START_TIME']; 
			$end_time_fix = $res_course_schedule->fields['END_TIME'];
			if($start_time_fix == '' && $end_time_fix != '' )
			{
				$start_time_fix =  "12:00 AM";
			}
			 echo $start_time_fix;
		}
		
		
		?></div></td><!-- Ticket # 1740  -->
		<td><div style="width:80px"><? 
		if($_REQUEST['detail_view'] == 1) {
			$start_time_fix =  $res_course_schedule->fields['START_TIME']; 
			$end_time_fix = $res_course_schedule->fields['END_TIME'];
			if($end_time_fix == '' && $start_time_fix != '' )
			{
				$end_time_fix =  "12:00 AM";
			}
			 echo $end_time_fix;
		} 
		?></div></td><!-- Ticket # 1740  -->
		<td><div style="width:80px"><? if($_REQUEST['detail_view'] == 1) echo $res_course_schedule->fields['HOURS']; ?></div></td><!-- Ticket # 1740  -->
		<td><div style="width:80px"><? if($_REQUEST['detail_view'] == 1) echo $res_course_schedule->fields['COMPLETED']; ?></div></td><!-- Ticket # 1740  -->
		<td><? if($_REQUEST['detail_view'] == 1 && ($res_course_schedule->fields['COMPLETED_1'] == 1 || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2 || $res_course_schedule->fields['ATTENDANCE_CODE'] == 'I')) echo $res_course_schedule->fields['ATTENDANCE_CODE']; ?></td>
		<td>
			<div style="width:70px">
			<? if($res_course_schedule->fields['COMPLETED_1'] == 1 || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2 || $res_course_schedule->fields['ATTENDANCE_CODE'] == 'I') 
				echo number_format_value_checker($res_course_schedule->fields['ATTENDANCE_HOURS'],2); 
			else 
				echo "0.00"; ?>
			</div>
		</td>
		<? if($res_att_act->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'] == 1){ ?>
		<td>
			<div style="width:100px"><?=$res_course_schedule->fields['ATTENDANCE_ACTIVITY_TYPE']?></div><!-- Ticket # 1740  -->
		</td>
		<? } ?>
		
		<? /* Ticket # 1601  */
		if($res_att_act->fields['ENABLE_ATTENDANCE_COMMENTS'] == 1){ ?>
		<td>
			<div style="width:100px"><?=$res_course_schedule->fields['ATTENDANCE_COMMENTS']?></div>
		</td>
		<? } 
		/* Ticket # 1601  */?>
		
		<td class="attendance_table_option" > <!-- Ticket # 1298 -->
			<div style="width:100px"><!-- Ticket # 1740  -->
			<? if($_REQUEST['detail_view'] == 1 && $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2 && $res_course_schedule->fields['PK_STUDENT_ATTENDANCE'] > 0){ ?>
				<a href="javascript:void(0)" title="<?=EDIT?>" onclick="edit_ns(<?=$res_course_schedule->fields['PK_STUDENT_ATTENDANCE']?>)" class="btn edit-color btn-circle"><i class="far fa-edit"></i></a>
				
				<a href="javascript:void(0);" onclick="delete_row('<?=$res_course_schedule->fields['PK_STUDENT_ATTENDANCE']?>','non_schedule')" title="<?=DELETE?>" class="btn delete-color btn-circle" style="width:25px; height:25px; padding: 2px;"><i class="far fa-trash-alt"></i> </a>
			<? } else if($res_course_schedule->fields['PK_STUDENT_ATTENDANCE'] > 0){ 
					$res_att_pre = $db->Execute("SELECT PK_STUDENT_ATTENDANCE FROM S_STUDENT_ATTENDANCE WHERE PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
					if($res_att_pre->RecordCount() > 1) { ?>
						<a href="javascript:void(0);" onclick="delete_row('<?=$res_course_schedule->fields['PK_STUDENT_ATTENDANCE']?>','schedule_att')" title="<?=DELETE?>" class="btn delete-color btn-circle" style="width:25px; height:25px; padding: 2px;"><i class="far fa-trash-alt"></i> </a>
				<? }
			}
			if($_REQUEST['detail_view'] == 1 && $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 1) { ?>
				<a href="attendance_entry?co=<?=$res_course_schedule->fields['PK_COURSE_OFFERING']?>&so=<?=$res_course_schedule->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL']?>&tm=<?=$res_course_schedule->fields['PK_TERM_MASTER']?>&campus=<?=$res_course_schedule->fields['PK_CAMPUS']?>" title="<?=ATTENDANCE?>" class="btn edit-color btn-circle"><i class="fas fa-align-justify"></i></a><!--DIAM-1422-->
			<? } ?>
			</div><!-- Ticket # 1740  -->
		</td>
	</tr>
<?	}
	$res_course_schedule->MoveNext();
} ?>
<tr>
	<td></td>
	<? /* Ticket # 1298 */
	if($_REQUEST['detail_view'] == 0){ ?>
		<td></td>
	<? } 
	 /* Ticket # 1298 */ ?>
	<td></td>
	<td></td>
	<td></td>
	<td><b>Total</b></td>
	<td><b><?  if($_REQUEST['detail_view'] == 1) echo number_format_value_checker($TOTAL_HOURS,2); ?></b></td>
	<td></td>
	<td></td>
	<td><b><?=number_format_value_checker($ATTENDANCE_HOURS,2)?></b></td>
	<? if($res_att_act->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'] == 1){ ?>
	<td></td>
	<? } ?>
	<? if($res_att_act->fields['ENABLE_ATTENDANCE_COMMENTS'] == 1){ ?>
	<td></td>
	<? } ?>
	<td class="attendance_table_option" ></td> <!-- Ticket # 1298 -->
</tr>
