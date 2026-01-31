<?php require_once('../global/config.php'); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../global/mail.php"); 
require_once("../global/texting.php"); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING = $_REQUEST['id'];
$grade_input_cunt   = 1;

$res_stu1 = $db->Execute("select S_STUDENT_COURSE.PK_STUDENT_COURSE,S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT,S_STUDENT_MASTER.PK_STUDENT_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME,FINAL_GRADE,INACTIVE, RETURN_DATE, PK_COURSE_OFFERING_STUDENT_STATUS, MIDPOINT_GRADE, NUMERIC_GRADE, FINAL_GRADE, STUDENT_ID FROM S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_COURSE, S_STUDENT_ACADEMICS WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ARCHIVED = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME)");

while (!$res_stu1->EOF) {
	
	$PK_COURSE_OFFERING_STUDENT_STATUS 	= $res_stu1->fields['PK_COURSE_OFFERING_STUDENT_STATUS'];
	$PK_STUDENT_ENROLLMENT 				= $res_stu1->fields['PK_STUDENT_ENROLLMENT'];
	$PK_STUDENT_COURSE    				= $res_stu1->fields['PK_STUDENT_COURSE'];
	$INACTIVE    						= $res_stu1->fields['INACTIVE'];
	$RETURN_DATE    					= $res_stu1->fields['RETURN_DATE'];
	$MIDPOINT_GRADE    					= $res_stu1->fields['MIDPOINT_GRADE'];
	$NUMERIC_GRADE    					= $res_stu1->fields['NUMERIC_GRADE'];
	$FINAL_GRADE    					= $res_stu1->fields['FINAL_GRADE'];
	
	if($INACTIVE != '0000-00-00')
		$INACTIVE = date("m/d/Y",strtotime($INACTIVE));
	else
		$INACTIVE = '';
		
	if($RETURN_DATE != '0000-00-00')
		$RETURN_DATE = date("m/d/Y",strtotime($RETURN_DATE));
	else
		$RETURN_DATE = '';
	?>
	
	<tr id="grade_input_table_<?=$grade_input_cunt?>" >
		<td >
			<input type="hidden" name="GRADE_INPUT_PK_STUDENT_COURSE[]"  value="<?=$PK_STUDENT_COURSE?>" />
			<input type="hidden" name="grade_input_cunt[]"  value="<?=$grade_input_cunt?>" />
			
			<div style="width:200px"><?=$res_stu1->fields['NAME']; ?></div><!-- Ticket # 1685 -->
		</td>
		<td >
			<div style="width:120px"><?=$res_stu1->fields['STUDENT_ID']; ?></div>
		</td>
		<td>
			<select id="GRADE_INPUT_GRADE_<?=$grade_input_cunt?>" name="GRADE_INPUT_GRADE[]" class="form-control <? if($_REQUEST['required'] == 1) echo "required-entry"; ?> " style="width:100px;" > <!-- Ticket # 1685 --> <!-- Ticket # 1900 -->
				<? /* Ticket #1695  */
				$res_type = $db->Execute("select PK_GRADE, CONCAT(GRADE, ' - ', NUMBER_GRADE) AS GRADE, ACTIVE from S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, GRADE ASC");
				while (!$res_type->EOF) { 
					$option_label = $res_type->fields['GRADE'];
					if($res_type->fields['ACTIVE'] == 0)
						$option_label .= " (Inactive)"; ?>
					<option value="<?=$res_type->fields['PK_GRADE'] ?>" <? if($FINAL_GRADE == $res_type->fields['PK_GRADE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
				<?	$res_type->MoveNext();
				} /* Ticket #1695  */ ?>
			</select>
		</td>
		<td>
			<input type="text" class="form-control" placeholder="" name="GRADE_INPUT_NUMERIC_GRADE[]" id="GRADE_INPUT_NUMERIC_GRADE_<?=$grade_input_cunt?>" value="<?=$NUMERIC_GRADE?>"  />
		</td>
		<td>
			<select id="GRADE_INPUT_MIDPOINT_GRADE_<?=$grade_input_cunt?>" name="GRADE_INPUT_MIDPOINT_GRADE[]" class="form-control" style="width:110px;" > <!-- Ticket # 1685 -->
				<option selected></option>
				<? /* Ticket #1695  */
				$res_type = $db->Execute("select PK_GRADE, CONCAT(GRADE, ' - ', NUMBER_GRADE) AS GRADE, ACTIVE from S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, GRADE ASC");
				while (!$res_type->EOF) { 
					$option_label = $res_type->fields['GRADE'];
					if($res_type->fields['ACTIVE'] == 0)
						$option_label .= " (Inactive)"; ?>
					<option value="<?=$res_type->fields['PK_GRADE'] ?>" <? if($MIDPOINT_GRADE == $res_type->fields['PK_GRADE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
				<?	$res_type->MoveNext();
				} /* Ticket #1695  */ ?>
			</select>
		</td>

		<td>
			<select id="GRADE_PK_COURSE_OFFERING_STUDENT_STATUS_<?=$grade_input_cunt?>" name="GRADE_PK_COURSE_OFFERING_STUDENT_STATUS[]" class="form-control" style="width:110px;" > <!-- Ticket # 1685 -->
				<option selected></option>
				<? /* Ticket #1695  */
				$res_type = $db->Execute("select PK_COURSE_OFFERING_STUDENT_STATUS, CONCAT(COURSE_OFFERING_STUDENT_STATUS, ' - ', DESCRIPTION) AS COURSE_OFFERING_STUDENT_STATUS, ACTIVE FROM M_COURSE_OFFERING_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, COURSE_OFFERING_STUDENT_STATUS ASC");
				while (!$res_type->EOF) { 
					$option_label = $res_type->fields['COURSE_OFFERING_STUDENT_STATUS'];
					if($res_type->fields['ACTIVE'] == 0)
						$option_label .= " (Inactive)"; ?>
					<option value="<?=$res_type->fields['PK_COURSE_OFFERING_STUDENT_STATUS'] ?>" <? if($res_type->fields['PK_COURSE_OFFERING_STUDENT_STATUS'] == $PK_COURSE_OFFERING_STUDENT_STATUS) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
				<?	$res_type->MoveNext();
				} /* Ticket #1695  */ ?>
			</select>
		</td>
		<td>
			<input type="text" class="form-control date" placeholder="" name="GRADE_INPUT_INACTIVE[]" id="GRADE_INPUT_INACTIVE_<?=$grade_input_cunt?>" value="<?=$INACTIVE?>" style="width:100px;" onchange="show_inactive_message(<?=$grade_input_cunt?>)" />
			<input type="hidden" name="GRADE_INPUT_INACTIVE_RESET_ATTENDANCE_CODE[]" id="GRADE_INPUT_INACTIVE_RESET_ATTENDANCE_CODE_<?=$grade_input_cunt?>" value="0" /> <!-- Ticket # 1661 -->
		</td>
		<td>
			<input type="text" class="form-control date" placeholder="" name="GRADE_INPUT_RETURN_DATE[]" id="GRADE_INPUT_RETURN_DATE_<?=$grade_input_cunt?>" value="<?=$RETURN_DATE?>" style="width:100px;" onchange="validate_return_date(<?=$grade_input_cunt?>)" /> <!-- Ticket # 1661 -->
		</td>
		
		<td>
			<div id="STU_ENROLLMENT_<?=$grade_input_cunt?>" style="width:250px" > <!-- Ticket # 1685 -->
				<? /* Ticket # 1685 */
				$res_en = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 , IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE, IS_ACTIVE_ENROLLMENT, CAMPUS_CODE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
				echo $res_en->fields['BEGIN_DATE_1'].' - '.$res_en->fields['CODE'].' - '.$res_en->fields['STUDENT_STATUS'].' - '.$res_en->fields['CAMPUS_CODE'];  
				/* Ticket # 1685 */ ?>
			</div>
		</td>
		<td>
			<? $res_cour_sch_11 = $db->Execute("SELECT IF(START_DATE = '0000-00-00','',DATE_FORMAT(START_DATE, '%m/%d/%Y' )) AS START_DATE, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE FROM S_COURSE_OFFERING_SCHEDULE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
			echo $res_cour_sch_11->fields['START_DATE'] ?>
		</td>
		<td>
			<? echo $res_cour_sch_11->fields['END_DATE'] ?>
		</td>
		
	</tr>
	
	<? $grade_input_cunt++;
	$res_stu1->MoveNext();
}

