<? require_once("../global/config.php"); 
require_once("../language/instructor_final_grade.php");
require_once("../language/common.php");
require_once("../language/course_offering.php"); // DIAM-785


if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING = $_REQUEST['val'];  ?>
<div class="table-responsive">
	<table class="table table-bordered">
		<thead>
			<tr>
				<th ><?=STUDENTS?></th>
				<th ><?=GRADE?></th>
			</tr>
		</thead>
		<tbody id="finalGradBook"><!--DIAM-785-->
			<? $grade_input_cunt = 0;
			$res_cs = $db->Execute("select S_STUDENT_COURSE.PK_STUDENT_COURSE,S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT,S_STUDENT_MASTER.PK_STUDENT_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS NAME,FINAL_GRADE,INACTIVE, PK_COURSE_OFFERING_STUDENT_STATUS, MIDPOINT_GRADE, NUMERIC_GRADE, FINAL_GRADE FROM S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_COURSE WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ARCHIVED = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME)"); 
			while (!$res_cs->EOF) { 
				$PK_COURSE_OFFERING_STUDENT_STATUS 	= $res_cs->fields['PK_COURSE_OFFERING_STUDENT_STATUS'];
				$PK_STUDENT_ENROLLMENT 				= $res_cs->fields['PK_STUDENT_ENROLLMENT'];
				$PK_STUDENT_COURSE    				= $res_cs->fields['PK_STUDENT_COURSE'];
				$INACTIVE    						= $res_cs->fields['INACTIVE'];
				$MIDPOINT_GRADE    					= $res_cs->fields['MIDPOINT_GRADE'];
				$NUMERIC_GRADE    					= $res_cs->fields['NUMERIC_GRADE'];
				$FINAL_GRADE    					= $res_cs->fields['FINAL_GRADE']; 
				
				$res_g = $db->Execute("select IS_DEFAULT, GRADE from S_GRADE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE = '$FINAL_GRADE' ");
				 
				$grade_input_cunt++; ?>
				<tr>
					<td>
						<? if($FINAL_GRADE == 0 || $res_g->fields['IS_DEFAULT'] == 1){ ?>
							<input type="hidden" name="GRADE_INPUT_PK_STUDENT_COURSE[]"  value="<?=$PK_STUDENT_COURSE?>" />
						<? } ?>
						<input type="hidden" name="grade_input_cunt[]"  value="<?=$grade_input_cunt?>" />
						<?  echo $res_cs->fields['NAME']; ?>
					</td>
					<td>
						<? if($FINAL_GRADE == 0 || $res_g->fields['IS_DEFAULT'] == 1){ ?>
						<select id="GRADE_INPUT_GRADE_<?=$grade_input_cunt?>" name="GRADE_INPUT_GRADE[]" class="form-control <? if($_REQUEST['required'] == 1) echo "required-entry"; ?> " style="width:150px;" >
							<option selected></option>
							<? $res_type = $db->Execute("select PK_GRADE,GRADE from S_GRADE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by GRADE ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_GRADE'] ?>" <? if($FINAL_GRADE == $res_type->fields['PK_GRADE']) echo "selected"; ?> ><?=$res_type->fields['GRADE'] ?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
						<? } else 
							echo $res_g->fields['GRADE']; ?>
					</td>
				</tr>
			<?	$res_cs->MoveNext();
			} ?>
		</tbody>
	</table>
</div>
<div class="col-12 form-group text-right">
<!-- DIAM-785 -->
<? //if(($FINAL_GRADE == 0 || $res_g->fields['IS_DEFAULT'] == 1) && $res_cs->RecordCount()>0){ ?>
<?
$final_grade_res = $db->Execute("SELECT PK_STUDENT_COURSE FROM S_STUDENT_COURSE_HISTROY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ORDER BY PK_STUDENT_COURSE ASC "); //Ticket #1290
$final_grade_cnt = $final_grade_res->RecordCount();
if ($final_grade_cnt > 0) {
?>														
<a href="javascript:void(0)" onclick="confirm_restore_final_grade()" class="btn waves-effect waves-light btn-info"><?= RESTORE_FINAL_GRADE ?></a>&nbsp;&nbsp;
<?php } ?>
<?php //} ?>
<!-- DIAM-785 -->
	<button type="submit" class="btn waves-effect waves-light btn-info" id="SAVE_BTN" style="float:right;margin-right:5px;" ><?=SAVE?></button>
</div>

<!--# DIAM-785 -->
<?php
			 function getUser($userId){
				global $db;
				$res_usr_name = $db->Execute("SELECT FIRST_NAME,LAST_NAME FROM S_EMPLOYEE_MASTER,Z_USER WHERE Z_USER.PK_USER = '$userId' AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER");
				return " ".$res_usr_name->fields['LAST_NAME'].', '.$res_usr_name->fields['FIRST_NAME'];
			 }
			?>		
			<div class="modal" id="restore_Modal_final_grade" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="exampleModalLabel1"><?= RESTORE_FINAL_GRADE_LABEL ?></h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body">
						<div style="" class="col-sm-4">
							<select id="RESTORE_FINAL_GRADE" name="RESTORE_FINAL_GRADE" class="form-control" style="width:180px">
								<option value="">Select Latest Date</option>
								<?
								$res_final_grade = $db->Execute("SELECT PK_STUDENT_COURSE_HISTROY_ID,CREATED_ON,CREATED_BY,EDITED_BY FROM S_STUDENT_COURSE_HISTROY WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  GROUP BY CREATED_ON ORDER BY CREATED_ON DESC LIMIT 5");
								while (!$res_final_grade->EOF) {   
																			
										if($res_final_grade->fields['CREATED_BY']!=0){		
											$usr =  getUser($res_final_grade->fields['CREATED_BY']);									
										}else if($res_final_grade->fields['EDITED_BY']!=0){									
											$usr =  getUser($res_final_grade->fields['EDITED_BY']);
											
										}else{
											$usr = "No User";
										}
										?>										
									<option value="<?= $res_final_grade->fields['CREATED_ON'] ?>"><?= $res_final_grade->fields['CREATED_ON'] ?>  <?=$usr?></option>
								<? $res_final_grade->MoveNext();
								}  
								?>
							</select>
							<div class="validation-advice" id="RESTORE_FINAL_GRADE_ERR" style="display:none;">This is a required field.</div>
						</div>
						</div>
						<div class="modal-footer">
							<button type="button" onclick="RestoreGradeBook(<?=$PK_COURSE_OFFERING?>,'FG_SETUP')" class="btn waves-effect waves-light btn-info"><?= YES ?></button>
							<button type="button" class="btn waves-effect waves-light btn-dark" onclick="jQuery('#restore_Modal_final_grade').modal('hide');"><?= NO ?></button>
						</div>
					</div>
				</div>
			</div>
			<!--# DIAM-785 -->
