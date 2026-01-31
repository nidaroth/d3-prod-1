<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
} 
$prog_grade_book_count = $_REQUEST['prog_grade_book_count']; ?>

<tr id="prog_grade_book_<?=$prog_grade_book_count?>" >
	<td>
		<input type="hidden" name="PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT[]" value="" />
		<input type="hidden" name="PROGRAM_GRADE_HID[]" value="<?=$prog_grade_book_count?>" />
		
		<select id="PROGRAM_GRADE_PK_GRADE_BOOK_CODE_<?=$prog_grade_book_count?>" name="PROGRAM_GRADE_PK_GRADE_BOOK_CODE_<?=$prog_grade_book_count?>" class="form-control required-entry" onchange="get_grade_book_code_value(this.value,'<?=$prog_grade_book_count?>')" >
			<option ></option>
			<? /* Ticket # 1689 */
			$res_cs = $db->Execute("SELECT PK_GRADE_BOOK_CODE, CONCAT(CODE, ' - ', DESCRIPTION) AS CODE, ACTIVE FROM M_GRADE_BOOK_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, CODE ASC");
			while (!$res_cs->EOF) { 
				$option_label = $res_cs->fields['CODE'];
				if($res_cs->fields['ACTIVE'] == 0)
					$option_label .= " (Inactive)"; ?>
				<option value="<?=$res_cs->fields['PK_GRADE_BOOK_CODE']?>" <? if($GRADE_PK_GRADE_BOOK_CODE == $res_cs->fields['PK_GRADE_BOOK_CODE'] ) echo "selected";?> <? if($res_cs->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
			<?	$res_cs->MoveNext();
			} /* Ticket # 1689 */ ?>
		</select>
	</td>
	<td>
		<input type="text" class="form-control required-entry" placeholder="" name="PROGRAM_GRADE_DESCRIPTION_<?=$prog_grade_book_count?>" id="PROGRAM_GRADE_DESCRIPTION_<?=$prog_grade_book_count?>" value="" />
	</td>
	<td>
		<select id="PROGRAM_GRADE_PK_GRADE_BOOK_TYPE_<?=$prog_grade_book_count?>" name="PROGRAM_GRADE_PK_GRADE_BOOK_TYPE_<?=$prog_grade_book_count?>" class="form-control required-entry" >
			<option ></option>
			<? /* Ticket # 1689 */
			$res_cs = $db->Execute("SELECT PK_GRADE_BOOK_TYPE, CONCAT(GRADE_BOOK_TYPE, ' - ', DESCRIPTION) as GRADE_BOOK_TYPE, ACTIVE FROM M_GRADE_BOOK_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ORDER BY ACTIVE DESC, GRADE_BOOK_TYPE ASC");
			while (!$res_cs->EOF) { 
				$option_label = $res_cs->fields['GRADE_BOOK_TYPE'];
				if($res_cs->fields['ACTIVE'] == 0)
					$option_label .= " (Inactive)";  ?>
				<option value="<?=$res_cs->fields['PK_GRADE_BOOK_TYPE']?>" <? if($res_cs->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
			<?	$res_cs->MoveNext();
			} /* Ticket # 1689 */ ?>
		</select>
	</td>
	<td>
		<input type="text" class="form-control date" placeholder="" name="PROGRAM_GRADE_COMPLETED_DATE[]" id="PROGRAM_GRADE_COMPLETED_DATE_<?=$prog_grade_book_count?>" value="" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_SESSION_REQUIRED[]" id="PROGRAM_GRADE_SESSION_REQUIRED_<?=$prog_grade_book_count?>" value="" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_SESSION_COMPLETED[]" id="PROGRAM_GRADE_SESSION_COMPLETED_<?=$prog_grade_book_count ?>" value="" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_HOUR_REQUIRED[]" id="PROGRAM_GRADE_HOUR_REQUIRED_<?=$prog_grade_book_count?>" value="" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_HOUR_COMPLETED[]" id="PROGRAM_GRADE_HOUR_COMPLETED_<?=$prog_grade_book_count?>" value="<?=$HOUR_COMPLETED?>" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_POINTS_REQUIRED[]" id="PROGRAM_GRADE_POINTS_REQUIRED_<?=$prog_grade_book_count?>" value="" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_POINTS_COMPLETED[]" id="PROGRAM_GRADE_POINTS_COMPLETED_<?=$prog_grade_book_count ?>" value="<?=$POINTS_COMPLETED?>" />
	</td>
	<td>
		<select id="PROGRAM_GRADE_PK_ENROLLMENT_<?=$res_grade->fields['PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT']?>" name="PROGRAM_GRADE_PK_ENROLLMENT[]" class="form-control" style="width:150px;" >
			<? /* Ticket # 1689 */
			$res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT, CAMPUS_CODE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$_REQUEST[sid]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" <? if($res_type->fields['IS_ACTIVE_ENROLLMENT'] == 1) echo "selected"; ?> <? if($res_type->fields['IS_ACTIVE_ENROLLMENT'] == 1) echo "class='option_red'";  ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['CAMPUS_CODE']?></option>
			<?	$res_type->MoveNext();
			} /* Ticket # 1689 */ ?>
		</select>
	</td>
	<td>
		<a href="javascript:void(0);" onclick="delete_row('<?=$prog_grade_book_count?>','program_grade_input_ajax')" title="Delete" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
	</td>
</tr>