<? require_once("../global/config.php"); 
require_once("../language/instructor_save_grade_book_as_final.php");
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING = $_REQUEST['val'];

$res_sch = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");

$res_sch_comp = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL, IF(COMPLETED = 1,' - Completed','') AS COMPLETED from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND COMPLETED = 1");

$res_cs = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE,DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE.START_DATE,'%m/%d/%Y') AS CLASS_START, DATE_FORMAT(S_COURSE_OFFERING_SCHEDULE.END_DATE, '%m/%d/%Y') AS CLASS_END from  S_COURSE_OFFERING_SCHEDULE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");

if($res_cs->fields['CLASS_END'] == '00/00/0000') {
	$PK_COURSE_OFFERING_SCHEDULE = $res_cs->fields['PK_COURSE_OFFERING_SCHEDULE'];
	$res_cs1 = $db->Execute("SELECT  DATE_FORMAT(MAX(SCHEDULE_DATE), '%m/%d/%Y') AS CLASS_END FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING_SCHEDULE = '$PK_COURSE_OFFERING_SCHEDULE' ");
	
	$CLASS_END = $res_cs1->fields['CLASS_END'];
} else
	$CLASS_END = $res_cs->fields['CLASS_END'];
?>

<div class="row">
	<div class="col-md-1">&nbsp;</div>
	<div class="col-md-5">
		<?=COMPLETED_CLASS_MEETINGS.' <b>'.$res_sch_comp->RecordCount().'</b>' ?>
	</div>
	<div class="col-md-5">
		<?=FIRST_CLASS_DATE.' <b>'.$res_cs->fields['CLASS_START'].'</b>' ?>
	</div>
</div>
<div class="row">
	<div class="col-md-1">&nbsp;</div>
	<div class="col-md-5">
		<?=SCHEDULED_CLASS_MEETINGS.' <b>'.$res_sch->RecordCount().'</b>' ?>
	</div>
	<div class="col-md-5">
		<?=LAST_CLASS_DATE.' <b>'.$CLASS_END.'</b>' ?>
	</div>
</div>

<table class="table table-bordered">
	<thead>
		<tr>
			<th ><?=STUDENTS?></th>
			<th ><?=FINAL_GRADE?></th>
			<th ><?=POINTS?></th>
			<th ><?=PERCENTAGE?></th>
		</tr>
	</thead>
	<tbody>
	<? $res_stu = $db->Execute("select PK_STUDENT_COURSE, S_STUDENT_MASTER.PK_STUDENT_MASTER,FINAL_GRADE_GRADE, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, FINAL_TOTAL_OBTAINED, FINAL_MAX_TOTAL, FINAL_TOTAL_GRADE, CURRENT_TOTAL_OBTAINED, CURRENT_MAX_TOTAL, CURRENT_TOTAL_GRADE, FINAL_GRADE, FINAL_SCALE_SETUP.GRADE AS FINAL_SCALE, CURRENT_SCALE_SETUP.GRADE AS CURRENT_SCALE, FINAL_TOTAL_GRADE_SCALE_SETUP.GRADE AS FINAL_TOTAL_SCALE, CURRENT_TOTAL_PK_GRADE_SCALE_DETAIL, FINAL_TOTAL_PK_GRADE_SCALE_DETAIL FROM  S_STUDENT_MASTER, 
	S_STUDENT_COURSE 
	
	LEFT JOIN S_GRADE as FINAL_SCALE_SETUP ON FINAL_GRADE = FINAL_SCALE_SETUP.PK_GRADE 
	LEFT JOIN S_GRADE as CURRENT_SCALE_SETUP ON CURRENT_TOTAL_GRADE = CURRENT_SCALE_SETUP.PK_GRADE 
	LEFT JOIN S_GRADE as FINAL_TOTAL_GRADE_SCALE_SETUP ON FINAL_TOTAL_GRADE = FINAL_TOTAL_GRADE_SCALE_SETUP.PK_GRADE 
	
	WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	while (!$res_stu->EOF) { 
		$PK_STUDENT_COURSE 	= $res_stu->fields['PK_STUDENT_COURSE'];
		$PK_STUDENT_MASTER 	= $res_stu->fields['PK_STUDENT_MASTER']; 
		$FINAL_PERCENTAGE	= ''; ?>
		<tr>
			<td >
				<input type="hidden" name="GRADE_INPUT_PK_STUDENT_COURSE[]" value="<?=$PK_STUDENT_COURSE?>" >
				<input type="hidden" name="GRADE_FINAL_GRADE[]" value="<?=$res_stu->fields['FINAL_TOTAL_GRADE']?>" >
				<?=$res_stu->fields['STU_NAME']?>
			</td>
			<td >
				<?=$res_stu->fields['FINAL_TOTAL_SCALE'];?>
			</td>
			<td >
				<? //if($res_stu->fields['FINAL_TOTAL_OBTAINED'] > 0) {
					$FINAL_PERCENTAGE  = number_format_value_checker(($res_stu->fields['FINAL_TOTAL_OBTAINED'] / $res_stu->fields['FINAL_MAX_TOTAL'] * 100),2).' %';
					echo $res_stu->fields['FINAL_TOTAL_OBTAINED'].'/'.$res_stu->fields['FINAL_MAX_TOTAL'];
				//} ?>
			</td>
			<td >
				<?=$FINAL_PERCENTAGE ?>
			</td>
		</tr>
		<?	$res_stu->MoveNext();
	} ?>
	</tbody>
</table>


<div class="col-12 form-group text-right">
	<button type="submit" class="btn waves-effect waves-light btn-info" id="SAVE_BTN" style="float:right;margin-right:5px;" ><?=SAVE?></button>
</div>