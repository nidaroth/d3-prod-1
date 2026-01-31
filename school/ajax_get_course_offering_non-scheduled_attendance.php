<?php require_once('../global/config.php'); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../global/mail.php"); 
require_once("../global/texting.php"); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$id = $_REQUEST['id'];
?>
	
<? $res_type = $db->Execute("select PK_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE, S_STUDENT_MASTER.PK_STUDENT_MASTER, M_ATTENDANCE_CODE.CODE as ATTENDANCE_CODE, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS BEGIN_DATE, STUDENT_STATUS, IF(S_STUDENT_SCHEDULE.SCHEDULE_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%m/%d/%Y' )) AS SCHEDULE_DATE, IF(S_STUDENT_SCHEDULE.START_TIME = '00:00:00','',DATE_FORMAT(S_STUDENT_SCHEDULE.START_TIME,'%h:%i %a' )) AS START_TIME , IF(S_STUDENT_SCHEDULE.END_TIME = '00:00:00','',DATE_FORMAT(S_STUDENT_SCHEDULE.END_TIME,'%h:%i %a' )) AS END_TIME, ATTENDANCE_HOURS, IF(S_STUDENT_ATTENDANCE.COMPLETED = 1,'Y', '') AS COMPLETED, STUDENT_ID  
FROM 
S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_SCHEDULE 
LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE 
LEFT JOIN M_ATTENDANCE_CODE ON M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE
,S_STUDENT_COURSE, S_STUDENT_ENROLLMENT 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 

WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT AND PK_COURSE_OFFERING = '$id' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_SCHEDULE_TYPE = 2 AND S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = S_STUDENT_COURSE.PK_STUDENT_COURSE"); //Ticket # 1963

while (!$res_type->EOF) { ?>
	<tr id="non_schedule_<?=$res_type->fields['PK_STUDENT_ATTENDANCE']?>" >
		<td><?=$res_type->fields['STU_NAME']?></td>
		<td><?=$res_type->fields['STUDENT_ID']?></td> <!-- Ticket # 1963 -->
		<td><?=$res_type->fields['SCHEDULE_DATE']?></td>
		<td><?=$res_type->fields['START_TIME']?></td>
		<td><?=$res_type->fields['END_TIME']?></td>
		<td><?=$res_type->fields['ATTENDANCE_HOURS']?></td>
		<td><?=$res_type->fields['ATTENDANCE_CODE']?></td>
		<td><?=$res_type->fields['COMPLETED']?></td>
		<td>
			<a href="javascript:void(0)" title="<?=EDIT?>" onclick="edit_ns(<?=$res_type->fields['PK_STUDENT_ATTENDANCE']?>)" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>
			
			<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_STUDENT_ATTENDANCE']?>','non_schedule')" title="<?=DELETE?>" class="btn delete-color btn-circle" style="width:25px; height:25px; padding: 2px;"><i class="far fa-trash-alt"></i> </a>
		</td>
	</tr>
<?	$res_type->MoveNext();
} ?>

