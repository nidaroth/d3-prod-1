<? require_once("../global/config.php"); 
require_once("../language/instructor_points_session_entry.php");
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING 	= $_REQUEST['co'];
$PK_STUDENT_ENROLLMENT 	= $_REQUEST['PK_STUDENT_ENROLLMENT'];
$pgbc 					= $_REQUEST['pgbc'];  
$type 					= $_REQUEST['type'];  
$prog_grade_book_count	= $_REQUEST['count'];  

if($type == 1 || $type == 3)
	$query = "select M_GRADE_BOOK_CODE.*, GRADE_BOOK_TYPE FROM M_GRADE_BOOK_CODE LEFT JOIN M_GRADE_BOOK_TYPE ON M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = M_GRADE_BOOK_CODE.PK_GRADE_BOOK_TYPE WHERE M_GRADE_BOOK_CODE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_BOOK_CODE = '$pgbc' ";
else if($type == 2)
	$query = "";
	
$res_grade = $db->Execute($query);
?>
<tr>
	<input type="hidden" name="PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT[]" value="" />
	<? if($type == 1 || $type == 3){ ?>
	<td >
		<select id="GRADE_PK_STUDENT_ENROLLMENT_<?=$prog_grade_book_count?>" name="GRADE_PK_STUDENT_ENROLLMENT[]" class="form-control select2" >
			<option value=""  >Select </option>
			<? $res_type = $db->Execute("select S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT, S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME from S_STUDENT_COURSE, S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND IS_ACTIVE_ENROLLMENT = 1 ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT'] ?>" ><?=$res_type->fields['NAME']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<? } else { ?>
		<input type="hidden" name="GRADE_PK_STUDENT_ENROLLMENT[]" value="<?=$PK_STUDENT_ENROLLMENT?>" />
	<? } ?>
	<td >
		<? if($type == 1 || $type == 3){
			echo $res_grade->fields['CODE'];?>
			<input type="hidden" name="GRADE_PK_GRADE_BOOK_CODE[]" value="<?=$pgbc ?>" />
		<? } else { ?>
			<select id="GRADE_PK_GRADE_BOOK_CODE_<?=$prog_grade_book_count?>" name="GRADE_PK_GRADE_BOOK_CODE[]" class="form-control required-entry" onchange="get_grade_book_code_value(this.value,'<?=$prog_grade_book_count?>')" >
				<option ></option>
				<? $res_cs = $db->Execute("SELECT PK_GRADE_BOOK_CODE, CODE FROM M_GRADE_BOOK_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 ORDER BY CODE ASC");
				while (!$res_cs->EOF) { ?>
					<option value="<?=$res_cs->fields['PK_GRADE_BOOK_CODE']?>" ><?=$res_cs->fields['CODE'] ?></option>
				<?	$res_cs->MoveNext();
				} ?>
			</select>
		<? } ?>
	</td>
	<td >
		<div id="GRADE_DESCRIPTION_DIV_<?=$prog_grade_book_count?>" >
			<?=$res_grade->fields['DESCRIPTION']?>
		</div>
	</td>
	<td >
		<div id="GRADE_BOOK_TYPE_DIV_<?=$prog_grade_book_count?>" >
			<?=$res_grade->fields['GRADE_BOOK_TYPE']?>
		</div>
	</td>
	<td >
		<? if($type == 2){ ?>
			<input type="text" class="form-control date" placeholder="" name="PROGRAM_GRADE_COMPLETED_DATE[]" id="PROGRAM_GRADE_COMPLETED_DATE_<?=$prog_grade_book_count?>" value="<?=$COMPLETED_DATE?>" style="width:100px;" />
		<? }?>
	</td>
	<td >
		<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_SESSION_REQUIRED[]" id="PROGRAM_GRADE_SESSION_REQUIRED_<?=$prog_grade_book_count?>" value="<?=$res_grade->fields['SESSIONS']?>" style="text-align:right;width:80px;" onchange="calc_total()" />
	</td>
	<td >
		<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_SESSION_COMPLETED[]" id="PROGRAM_GRADE_SESSION_COMPLETED_<?=$prog_grade_book_count?>" value="<?=$SESSION_COMPLETED?>" style="text-align:right;width:80px;" onchange="calc_total()"  />
	</td>
	<td >
		<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_HOUR_REQUIRED[]" readonly id="PROGRAM_GRADE_HOUR_REQUIRED_<?=$prog_grade_book_count?>" value="<?=$res_grade->fields['HOUR']?>" style="text-align:right;width:80px;" onchange="calc_total()"  />
	</td>
	<td >
		<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_HOUR_COMPLETED[]" id="PROGRAM_GRADE_HOUR_COMPLETED_<?=$prog_grade_book_count?>" value="<?=$HOUR_COMPLETED?>" style="text-align:right;width:80px;" onchange="calc_total()"  />
	</td>
	<td >
		<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_POINTS_REQUIRED[]" readonly id="PROGRAM_GRADE_POINTS_REQUIRED_<?=$prog_grade_book_count?>" value="<?=$res_grade->fields['POINTS']?>" style="text-align:right;width:80px;" onchange="calc_total()"  />
	</td>
	<td >
		<input type="text" class="form-control" placeholder="" name="PROGRAM_GRADE_POINTS_COMPLETED[]" id="PROGRAM_GRADE_POINTS_COMPLETED_<?=$prog_grade_book_count?>" value="<?=$POINTS_COMPLETED?>" style="text-align:right;width:80px;" onchange="calc_total()"  />
	</td>
</tr> 