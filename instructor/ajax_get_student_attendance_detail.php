<? require_once("../global/config.php"); 
require_once("../language/instructor_attendance_detail.php");
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING = $_REQUEST['val']; 
$PK_TERM_MASTER 	= $_REQUEST['tid']; 
$start				= $_REQUEST['start']; 
$sign				= '+';
if($start < 0)
	$sign = '';
	
$res = $db->Execute("SELECT MIN(SCHEDULE_DATE) AS SCHEDULE_DATE FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
$START_DATE = date("Y-m-d", strtotime('monday this week', strtotime($res->fields['SCHEDULE_DATE'])));
$START_DATE = date("Y-m-d", strtotime($START_DATE.' '.$sign.' '.($start * 28).' days'));
?>
<div class="row">
	<div class="col-sm-12 " >
		<button type="button" onclick="window.location.href='attendnace_review_pdf.php?co_id=<?=$PK_COURSE_OFFERING?>'" class="btn waves-effect waves-light btn-info" style="float:right;margin-right:10px;" ><?=PDF ?></button>
		
		<button type="button" onclick="get_attendance_detail(<?=($start + 1)?>)" class="btn waves-effect waves-light btn-info" style="float:right;margin-right:10px;" ><?=NEXT?></button>
		
		<button type="button" onclick="get_attendance_detail(<?=($start - 1)?>)" class="btn waves-effect waves-light btn-info" style="float:right;margin-right:10px;" ><?=PREVIOUS?></button>
	</div>
</div>
<br />
<table class="table-bordered" cellpadding=6 width="100%" >
	<thead>
		<tr>
			<th rowspan="2" style="padding: 2px;text-align:center;" ><?=STUDENT?></th>
			<th colspan="7" style="padding: 2px;text-align:center;" ><?=date("m/d/Y", strtotime($START_DATE))?> - <?=date("m/d/Y", strtotime($START_DATE.' + 6 days')) ?></th>
			<th colspan="7" style="padding: 2px;text-align:center;" ><?=date("m/d/Y", strtotime($START_DATE.' + 7 days')) ?> - <?=date("m/d/Y", strtotime($START_DATE.' + 13 days')) ?></th>
			<th colspan="7" style="padding: 2px;text-align:center;" ><?=date("m/d/Y", strtotime($START_DATE.' + 14 days')) ?> - <?=date("m/d/Y", strtotime($START_DATE.' + 20 days')) ?></th>
			<th colspan="7" style="padding: 2px;text-align:center;" ><?=date("m/d/Y", strtotime($START_DATE.' + 21 days')) ?> - <?=date("m/d/Y", strtotime($START_DATE.' + 27 days')) ?></th>
		</tr>
		<tr>
			<th style="padding: 2px;text-align:center;" >M</th>
			<th style="padding: 2px;text-align:center;" >T</th>
			<th style="padding: 2px;text-align:center;" >W</th>
			<th style="padding: 2px;text-align:center;" >T</th>
			<th style="padding: 2px;text-align:center;" >F</th>
			<th style="padding: 2px;text-align:center;" >S</th>
			<th style="padding: 2px;text-align:center;" >S</th>
			
			<th style="padding: 2px;text-align:center;" >M</th>
			<th style="padding: 2px;text-align:center;" >T</th>
			<th style="padding: 2px;text-align:center;" >W</th>
			<th style="padding: 2px;text-align:center;" >T</th>
			<th style="padding: 2px;text-align:center;" >F</th>
			<th style="padding: 2px;text-align:center;" >S</th>
			<th style="padding: 2px;text-align:center;" >S</th>
			
			<th style="padding: 2px;text-align:center;" >M</th>
			<th style="padding: 2px;text-align:center;" >T</th>
			<th style="padding: 2px;text-align:center;" >W</th>
			<th style="padding: 2px;text-align:center;" >T</th>
			<th style="padding: 2px;text-align:center;" >F</th>
			<th style="padding: 2px;text-align:center;" >S</th>
			<th style="padding: 2px;text-align:center;" >S</th>
			
			<th style="padding: 2px;text-align:center;" >M</th>
			<th style="padding: 2px;text-align:center;" >T</th>
			<th style="padding: 2px;text-align:center;" >W</th>
			<th style="padding: 2px;text-align:center;" >T</th>
			<th style="padding: 2px;text-align:center;" >F</th>
			<th style="padding: 2px;text-align:center;" >S</th>
			<th style="padding: 2px;text-align:center;" >S</th>
		</tr>
	</thead>
	<tbody>
		<? $res_cs = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME,' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME, S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT, S_STUDENT_COURSE.PK_STUDENT_COURSE from S_STUDENT_COURSE, S_STUDENT_MASTER WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC");
		while (!$res_cs->EOF) { 
			$PK_STUDENT_ENROLLMENT 	= $res_cs->fields['PK_STUDENT_ENROLLMENT']; 
			$PK_STUDENT_COURSE 		= $res_cs->fields['PK_STUDENT_COURSE'];?>
			<tr>
				<td><?=$res_cs->fields['NAME']?></td>
				<? for($i = 0 ; $i <= 27 ; $i++){ 
					$DATE = date("Y-m-d", strtotime($START_DATE.' + '.$i.' days')); 
					
					$res = $db->Execute("select ATTENDANCE_HOURS, S_STUDENT_ATTENDANCE.COMPLETED, M_ATTENDANCE_CODE.CODE  from S_STUDENT_ATTENDANCE LEFT JOIN M_ATTENDANCE_CODE ON M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE , S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND SCHEDULE_DATE = '$DATE' AND S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE   AND S_STUDENT_ATTENDANCE.COMPLETED = 1 "); 
					//AND ((S_STUDENT_ATTENDANCE.COMPLETED = 1 AND M_ATTENDANCE_CODE.CODE = 'P') OR PK_SCHEDULE_TYPE = 2) ?>
					
					<td>
						<? if($res->RecordCount() > 0)
							echo number_format_value_checker($res->fields['ATTENDANCE_HOURS'],2); ?>
					</td>
				<? } ?>
			</tr>
		<?	$res_cs->MoveNext();
		} ?>
	</tbody>
</table>