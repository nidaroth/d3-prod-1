<? require_once("../global/config.php"); 
require_once("../language/instructor_points_session_entry.php");
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING = $_REQUEST['val'];
$type 				= $_REQUEST['type'];  ?>

<div class="row">
	<div class="col-md-1">&nbsp;</div>
	<div class="col-md-11">
		<div class="row form-group">
			<div class="custom-control custom-radio col-md-3">
				<input type="radio" id="BY_ASSIGNMENT" name="VIEW" value="1" class="custom-control-input" <? if($type == 1) echo "checked"; ?> onclick="get_point_session_entry(1)" >
				<label class="custom-control-label" for="BY_ASSIGNMENT"><?=BY_LAB?></label>
			</div>
			<div class="custom-control custom-radio col-md-3">
				<input type="radio" id="BY_TEST" name="VIEW" value="3" class="custom-control-input" <? if($type == 3) echo "checked"; ?> onclick="get_point_session_entry(3)" >
				<label class="custom-control-label" for="BY_TEST"><?=BY_TEST?></label>
			</div>
			<div class="custom-control custom-radio col-md-3">
				<input type="radio" id="BY_STUDENT" name="VIEW" value="2" class="custom-control-input" <? if($type == 2) echo "checked"; ?> onclick="get_point_session_entry(2)" >
				<label class="custom-control-label" for="BY_STUDENT"><?=BY_STUDENT?></label>
			</div>
		</div>
	</div>
</div>

<br />
<div class="row">
<? if($type == 1) { ?>
	<div class="col-md-1">&nbsp;</div>
	<div class="col-md-5 form-group">
		<?=SELECT_LAB?><br />
		<select id="PK_GRADE_BOOK_CODE" name="PK_GRADE_BOOK_CODE" class="form-control required-entry select2" onchange="get_point_session_entry_input(1)" >
			<option value=""  >Select</option>
			<? $res_cs = $db->Execute("SELECT PK_GRADE_BOOK_CODE, CODE, M_GRADE_BOOK_CODE.DESCRIPTION FROM M_GRADE_BOOK_CODE LEFT JOIN M_GRADE_BOOK_TYPE ON M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE =  M_GRADE_BOOK_CODE.PK_GRADE_BOOK_TYPE WHERE M_GRADE_BOOK_CODE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_GRADE_BOOK_CODE.ACTIVE = 1 AND PK_GRADE_BOOK_TYPE_MASTER = 3 ORDER BY CODE ASC");
			while (!$res_cs->EOF) { ?>
				<option value="<?=$res_cs->fields['PK_GRADE_BOOK_CODE']?>" <? if($_REQUEST['pgbc'] == $res_cs->fields['PK_GRADE_BOOK_CODE'] ) echo "selected";?> ><?=$res_cs->fields['CODE'].' - '.$res_cs->fields['DESCRIPTION'] ?></option>
			<?	$res_cs->MoveNext();
			} ?>
		</select>
	</div>
<? } else if($type == 3) { ?>
	<div class="col-md-1">&nbsp;</div>
	<div class="col-md-5 form-group">
		<?=SELECT_TEST?><br />
		<select id="PK_GRADE_BOOK_CODE" name="PK_GRADE_BOOK_CODE" class="form-control required-entry select2" onchange="get_point_session_entry_input(3)" >
			<option value=""  >Select</option>
			<? $res_cs = $db->Execute("SELECT PK_GRADE_BOOK_CODE, CODE, M_GRADE_BOOK_CODE.DESCRIPTION FROM M_GRADE_BOOK_CODE LEFT JOIN M_GRADE_BOOK_TYPE ON M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE =  M_GRADE_BOOK_CODE.PK_GRADE_BOOK_TYPE WHERE M_GRADE_BOOK_CODE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_GRADE_BOOK_CODE.ACTIVE = 1 AND PK_GRADE_BOOK_TYPE_MASTER != 3 ORDER BY CODE ASC");
			while (!$res_cs->EOF) { ?>
				<option value="<?=$res_cs->fields['PK_GRADE_BOOK_CODE']?>" <? if($_REQUEST['pgbc'] == $res_cs->fields['PK_GRADE_BOOK_CODE'] ) echo "selected";?> ><?=$res_cs->fields['CODE'].' - '.$res_cs->fields['DESCRIPTION'] ?></option>
			<?	$res_cs->MoveNext();
			} ?>
		</select>
	</div>
<? } else if($type == 2) { ?>
	<div class="col-md-1">&nbsp;</div>
	<div class="col-md-5 form-group">
		<?=SELECT_STUDENT?><br />
		<select id="PK_STUDENT_ENROLLMENT" name="PK_STUDENT_ENROLLMENT" class="form-control required-entry select2" onchange="get_point_session_entry_input(2)">
			<option value=""  >Select </option>
			<? $res_type = $db->Execute("select S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT, S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME from S_STUDENT_COURSE, S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND IS_ACTIVE_ENROLLMENT = 1 ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME) ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT'] ?>" <? if($_REQUEST['eid'] == $res_type->fields['PK_STUDENT_ENROLLMENT']) echo "selected"; ?>  ><?=$res_type->fields['NAME']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</div>
<? }
if($type == 1 || $type == 3) { ?>
	<div class="col-md-3 form-group">
		<?=COMPLETED_DATE?><br />
		<input type="text" class="form-control date" placeholder="" name="COMPLETED_DATE" id="COMPLETED_DATE" value="<?=$COMPLETED_DATE?>" />
	</div>
<? } ?>
</div>
<br />