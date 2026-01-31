<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_ROLES'] != 2 ){ 
	header("location:../index");
	exit;
}

$grade_input_cunt  		 	= $_REQUEST['grade_input_cunt'];
$PK_STUDENT_PROGRAM_GRADE  	= $_REQUEST['PK_STUDENT_PROGRAM_GRADE'];
$PK_GRADE_SCALE_MASTER		= $_REQUEST['scale'];
$PK_CAMPUS_PROGRAM			= $_REQUEST['PK_CAMPUS_PROGRAM'];

if($PK_STUDENT_PROGRAM_GRADE == '') {
	$PK_STUDENT_MASTER  				= '';
	$PK_STUDENT_ENROLLMENT   			= '';
	$GRADE   							= '';
	$PK_COURSE_OFFERING_STUDENT_STATUS  = '';
	$INACTIVE    						= '';
	$MIDPOINT_GRADE						= '';
	$DETAIL								= '';
	$NUMERIC_GRADE						= '';
} else {
	$result = $db->Execute("SELECT * FROM S_STUDENT_PROGRAM_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_PROGRAM_GRADE = '$PK_STUDENT_PROGRAM_GRADE' ");
	
	$PK_STUDENT_MASTER  				= $result->fields['PK_STUDENT_MASTER'];
	$PK_STUDENT_ENROLLMENT    			= $result->fields['PK_STUDENT_ENROLLMENT'];
	$GRADE    							= $result->fields['GRADE'];
	$PK_COURSE_OFFERING_STUDENT_STATUS	= $result->fields['PK_COURSE_OFFERING_STUDENT_STATUS'];
	$INACTIVE    						= $result->fields['INACTIVE'];
	$MIDPOINT_GRADE    					= $result->fields['MIDPOINT_GRADE'];
	$DETAIL    							= $result->fields['DETAIL'];
	$NUMERIC_GRADE    					= $result->fields['NUMERIC_GRADE'];
	
	if($INACTIVE != '0000-00-00')
		$INACTIVE = date("m/d/Y",strtotime($INACTIVE));
	else
		$INACTIVE = '';
} ?>
<tr id="grade_input_table_<?=$grade_input_cunt?>" >
	<td >
		<input type="hidden" name="GRADE_INPUT_PK_STUDENT_PROGRAM_GRADE[]"  value="<?=$PK_STUDENT_PROGRAM_GRADE?>" />
		<input type="hidden" name="grade_input_cunt[]"  value="<?=$grade_input_cunt?>" />
		
		<? if($_REQUEST['no_student_edit'] == 1){ 
			if($PK_STUDENT_ENROLLMENT == '')
				$PK_STUDENT_ENROLLMENT = $_REQUEST['PK_STUDENT_ENROLLMENT']; 
				
			$res_type = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME FROM S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ARCHIVED = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME)");  
			echo $res_type->fields['NAME']; ?>
			<input type="hidden" name="GRADE_INPUT_PK_STUDENT_ENROLLMENT[]"  value="<?=$PK_STUDENT_ENROLLMENT?>" />
		<? } else { ?>
		<select id="GRADE_INPUTPK_STUDENT_ENROLLMENT_<?=$grade_input_cunt?>" name="GRADE_INPUT_PK_STUDENT_ENROLLMENT[]" class="form-control" style="width:200px;"  >
			<option selected></option>
			 <? $res_type = $db->Execute("select PK_STUDENT_ENROLLMENT,S_STUDENT_MASTER.PK_STUDENT_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME FROM S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ARCHIVED = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND IS_ACTIVE_ENROLLMENT = 1 AND PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME)");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT'] ?>" <? if($res_type->fields['PK_STUDENT_ENROLLMENT'] == $PK_STUDENT_ENROLLMENT) echo "selected"; ?> ><?=$res_type->fields['NAME'] ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
		<? } ?>
	</td>
	<td>
		<select id="GRADE_INPUT_GRADE_<?=$grade_input_cunt?>" name="GRADE_INPUT_GRADE[]" class="form-control" style="width:150px;" <? if($_REQUEST['read_only'] == 1) echo "disabled" ?> >
			<option selected></option>
			<? $res_type = $db->Execute("select PK_GRADE_SCALE_DETAIL,CONCAT(GRADE, ' (',MIN_PERCENTAGE,' to ',MAX_PERCENTAGE,')' ) AS GRADE from S_GRADE_SCALE_DETAIL LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_GRADE_SCALE_DETAIL.PK_GRADE WHERE S_GRADE_SCALE_DETAIL.ACTIVE = 1 AND S_GRADE_SCALE_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_SCALE_MASTER = '$PK_GRADE_SCALE_MASTER' order by GRADE ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_GRADE_SCALE_DETAIL'] ?>" <? if($GRADE == $res_type->fields['PK_GRADE_SCALE_DETAIL']) echo "selected"; ?> ><?=$res_type->fields['GRADE'] ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>

	<td>
		<select id="GRADE_PK_COURSE_OFFERING_STUDENT_STATUS_<?=$grade_input_cunt?>" name="GRADE_PK_COURSE_OFFERING_STUDENT_STATUS[]" class="form-control" style="width:150px;" <? if($_REQUEST['read_only'] == 1) echo "disabled" ?> >
			<option selected></option>
			 <? $res_type = $db->Execute("select PK_COURSE_OFFERING_STUDENT_STATUS,COURSE_OFFERING_STUDENT_STATUS FROM M_COURSE_OFFERING_STUDENT_STATUS WHERE ACTIVE = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by COURSE_OFFERING_STUDENT_STATUS ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_COURSE_OFFERING_STUDENT_STATUS'] ?>" <? if($res_type->fields['PK_COURSE_OFFERING_STUDENT_STATUS'] == $PK_COURSE_OFFERING_STUDENT_STATUS) echo "selected"; ?> ><?=$res_type->fields['COURSE_OFFERING_STUDENT_STATUS'] ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td>
		<input type="text" class="form-control date" placeholder="" name="GRADE_INPUT_INACTIVE[]" id="GRADE_INPUT_INACTIVE_<?=$grade_input_cunt?>" value="<?=$INACTIVE?>" style="width:100px;" <? if($_REQUEST['read_only'] == 1) echo "disabled" ?> />
	</td>
	<td>
		<select id="GRADE_INPUT_MIDPOINT_GRADE_<?=$grade_input_cunt?>" name="GRADE_INPUT_MIDPOINT_GRADE[]" class="form-control" style="width:150px;" <? if($_REQUEST['read_only'] == 1) echo "disabled" ?> >
			<option selected></option>
			<? $res_type = $db->Execute("select PK_GRADE_SCALE_DETAIL,CONCAT(GRADE, ' (',MIN_PERCENTAGE,' to ',MAX_PERCENTAGE,')' ) AS GRADE from S_GRADE_SCALE_DETAIL LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_GRADE_SCALE_DETAIL.PK_GRADE WHERE S_GRADE_SCALE_DETAIL.ACTIVE = 1 AND S_GRADE_SCALE_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_SCALE_MASTER = '$PK_GRADE_SCALE_MASTER' order by GRADE ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_GRADE_SCALE_DETAIL'] ?>" <? if($MIDPOINT_GRADE == $res_type->fields['PK_GRADE_SCALE_DETAIL']) echo "selected"; ?> ><?=$res_type->fields['GRADE'] ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td>
		<div id="STU_ENROLLMENT_<?=$grade_input_cunt?>" >
			<? if($_REQUEST['read_only'] == 1) { 
				$res_en = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
				echo $res_en->fields['BEGIN_DATE_1'].' - '.$res_en->fields['CODE'].' - '.$res_en->fields['STUDENT_STATUS']; 
			} ?>
		</div>
	</td>
	<td>
		
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="GRADE_INPUT_DETAIL[]" id="GRADE_INPUT_DETAIL_<?=$grade_input_cunt?>" value="<?=$DETAIL?>" style="width:200px;" <? if($_REQUEST['read_only'] == 1) echo "disabled" ?> />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="GRADE_INPUT_NUMERIC_GRADE[]" id="GRADE_INPUT_NUMERIC_GRADE_<?=$grade_input_cunt?>" value="<?=$NUMERIC_GRADE?>" style="width:100px;" <? if($_REQUEST['read_only'] == 1) echo "disabled" ?> />
	</td>
	<? if($_REQUEST['read_only'] != 1 && $_REQUEST['no_student_edit'] != 1) { ?>
	<td>
		<a href="javascript:void(0);" onclick="delete_row('<?=$grade_input_cunt?>','grade_input')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
	</td>
	<? } ?>
</tr>