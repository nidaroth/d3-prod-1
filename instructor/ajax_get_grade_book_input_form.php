<? require_once("../global/config.php"); 
require_once("../language/instructor_grade_book_entry.php");
require_once("../language/common.php");
require_once("../language/course_offering.php"); //DIAM-785

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING = $_GET[id] = $_REQUEST['val']; // DIAM-785
$type 				= $_REQUEST['type'];  ?>

<div class="row">
	<div class="col-md-1">&nbsp;</div>
	<div class="col-md-11">
		<div class="row form-group">
			<div class="custom-control custom-radio col-md-3">
				<input type="radio" id="BY_ASSIGNMENT" name="VIEW" value="1" class="custom-control-input" <? if($type == 1) echo "checked"; ?> onclick="get_grade_book_form(1)" >
				<label class="custom-control-label" for="BY_ASSIGNMENT"><?=BY_ASSIGNMENT?></label>
			</div>
			<div class="custom-control custom-radio col-md-3">
				<input type="radio" id="BY_STUDENT" name="VIEW" value="2" class="custom-control-input" <? if($type == 2) echo "checked"; ?> onclick="get_grade_book_form(2)" >
				<label class="custom-control-label" for="BY_STUDENT"><?=BY_STUDENT?></label>
			</div>
			<!-- Ticket # 1472 -->
			<div class="col-md-6" style="float:right" >
				<a class="btn waves-effect waves-light btn-info" style="color:#FFFFFF;" target="_blank" href="../school/grade_book_report?co_id=<?=$PK_COURSE_OFFERING?>&FORMAT=1" >GRADE BOOK <?=PDF?></a><!--DIAM-1599 -->
				<a class="btn waves-effect waves-light btn-info" style="color:#FFFFFF;" target="_blank" href="../school/course_offering_grade_book_analysis_report?co_id=<?=$PK_COURSE_OFFERING?>&format=1" ><?=PDF?></a>
				<a class="btn waves-effect waves-light btn-info" style="color:#FFFFFF;" target="_blank" href="../school/course_offering_grade_book_analysis_report?co_id=<?=$PK_COURSE_OFFERING?>&format=2" ><?=EXCEL?></a>
			</div>
			<!-- Ticket # 1472 -->
		</div>
	</div>
</div>

<? 
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

<? if($type == 1) { ?>
<br />
<div class="row">
	<div class="col-md-1">&nbsp;</div>
	<div class="col-md-5 form-group">
		<select id="PK_COURSE_OFFERING_GRADE" name="PK_COURSE_OFFERING_GRADE" class="form-control required-entry" onchange="get_student_for_grade_book_input_by_assignment()">
			<option value=""></option>
			<? $res_type = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE,CODE,POINTS,WEIGHTED_POINTS, DESCRIPTION FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ORDER BY PK_COURSE_OFFERING_GRADE ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_COURSE_OFFERING_GRADE']?>" <? if($_REQUEST['cog'] == $res_type->fields['PK_COURSE_OFFERING_GRADE']) echo "selected"; ?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION'] ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
		<span class="bar"></span> 
		<label for="PK_COURSE_OFFERING_GRADE"><?=SELECT_ASSIGNMENT?></label>
	</div>
</div>
<br />
<? } else if($type == 2) { ?>
<br />
<div class="row">
	<div class="col-md-1">&nbsp;</div>
	<div class="col-md-5 form-group">
		<select id="PK_STUDENT_ENROLLMENT" name="PK_STUDENT_ENROLLMENT" class="form-control required-entry" onchange="get_assignment_for_grade_book_input_by_student()">
			<option value=""></option>
			<? //AND IS_ACTIVE_ENROLLMENT = 1
			$res_type = $db->Execute("select S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT, S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME from S_STUDENT_COURSE, S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT'] ?>" <? if($_REQUEST['eid'] == $res_type->fields['PK_STUDENT_ENROLLMENT']) echo "selected"; ?>  ><?=$res_type->fields['NAME']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
		<span class="bar"></span> 
		<label for="PK_STUDENT_ENROLLMENT"><?=SELECT_STUDENT?></label>
	</div>
</div>
<br />
<? } ?>

<div class="table-responsive" id="STUDENT_DIV" >
	
</div>
<!--Ticket #1505-->
<div class="row">
	<div class="col-6 form-group ">
		<button type="button" onclick="recalculate()" class="btn waves-effect waves-light btn-info" id="RECALCULATE_BTN"  ><?=RECALCULATE?></button>
	</div>
	<div class="col-6 form-group text-right">
	<!--DIAM-785-->
	<?
	$result_SGH = $db->Execute("SELECT PK_STUDENT_GRADE FROM S_STUDENT_GRADE_HISTROY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$_GET[id]'"); //Ticket #1290
	$reccnt_SGH = $result_SGH->RecordCount();
	if ($reccnt_SGH > 0 || !empty($_GET['cog']) || !empty($_GET['eid'])) {
	?>		
	<a class="btn waves-effect waves-light btn-info" style="color:#FFFFFF;" href="javascript:void(0)" onclick="confirm_restore_grade_book_entry()" id="RESTORE_GRADE_BOOK_ENTRY_BTN"><?= RESTORE_GRADE_BOOK_ENTRY ?></a>&nbsp;&nbsp;&nbsp;&nbsp;

	<?php } ?>
	<!--DIAM-785-->
		<button type="submit" class="btn waves-effect waves-light btn-info" id="SAVE_BTN" style="float:right;margin-right:5px;" ><?=SAVE?></button>
	</div>
</div>
<!--Ticket #1505-->
<!--DIAM-785-->
<?php
function getUser($userId){
	global $db;
	$res_usr_name = $db->Execute("SELECT FIRST_NAME,LAST_NAME FROM S_EMPLOYEE_MASTER,Z_USER WHERE Z_USER.PK_USER = '$userId' AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER");
	return " ".$res_usr_name->fields['LAST_NAME'].', '.$res_usr_name->fields['FIRST_NAME'];
}
?>		
<div class="modal" id="restore_Modal_grade_book_entry" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel1"><?= RESTORE_GRADE_BOOK_ENTRY_LABEL ?></h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<!-- <div class="form-group" id="restore_Modal_grade_message"></div> -->
				<div class="col-sm-4">
					<select id="RESTORE_GRADE_BOOK_ENTRY" name="RESTORE_GRADE_BOOK_ENTRY" class="form-control" style="width:180px">
						<option value="">Select Latest Date</option>
						<?
						$res_cos = $db->Execute("SELECT PK_STUDENT_GRADE_HISTROY_ID,CREATED_ON,CREATED_BY,EDITED_BY FROM S_STUDENT_GRADE_HISTROY WHERE PK_COURSE_OFFERING = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  GROUP BY CREATED_ON ORDER BY CREATED_ON DESC LIMIT 5");
						while (!$res_cos->EOF) { 
							if($res_cos->fields['CREATED_BY']!=0){		
								$usr =  getUser($res_cos->fields['CREATED_BY']);									
							}else if($res_cos->fields['EDITED_BY']!=0){									
								$usr =  getUser($res_cos->fields['EDITED_BY']);
								
							}else{
								$usr = "No User";
							}										
							?>
							<option value="<?= $res_cos->fields['CREATED_ON'] ?>"><?= $res_cos->fields['CREATED_ON'] ?>	 <?=$usr?>					
						</option>
						<? $res_cos->MoveNext();
						}  ?>
					</select>
					<div class="validation-advice" id="RESTORE_GRADE_BOOK_ENTRY_ERR" style="display:none;">This is a required field.</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" onclick="RestoreGradeBook(<?= $_GET['id'] ?>,'GB_ENTRY')" class="btn waves-effect waves-light btn-info"><?= YES ?></button>
				<button type="button" class="btn waves-effect waves-light btn-dark" onclick="jQuery('#restore_Modal_grade_book_entry').modal('hide');"><?= NO ?></button>
			</div>
		</div>
	</div>
</div>

<!--DIAM-785-->
