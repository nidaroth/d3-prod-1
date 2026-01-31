<? require_once("../global/config.php"); 
require_once("../language/instructor_final_grade.php");
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING = $_REQUEST['id']; 
$last_date = $_REQUEST['last_date'];
?>

<? $grade_input_cunt = 0;
$res_cs = $db->Execute("select S_STUDENT_COURSE_HISTROY.PK_STUDENT_COURSE,S_STUDENT_COURSE_HISTROY.PK_STUDENT_ENROLLMENT,S_STUDENT_MASTER.PK_STUDENT_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS NAME,FINAL_GRADE,INACTIVE, PK_COURSE_OFFERING_STUDENT_STATUS, MIDPOINT_GRADE, NUMERIC_GRADE, FINAL_GRADE FROM S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_COURSE_HISTROY WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ARCHIVED = 0 AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_COURSE_HISTROY.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_STUDENT_COURSE_HISTROY.CREATED_ON = '$last_date' ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME)"); 

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
			<? //if($FINAL_GRADE == 0 || $res_g->fields['IS_DEFAULT'] == 1){ ?>
				<input type="hidden" name="GRADE_INPUT_PK_STUDENT_COURSE[]"  value="<?=$PK_STUDENT_COURSE?>" />
			<? //} ?>
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
			<? } else{ 
				echo $res_g->fields['GRADE']; ?>
				<input type="hidden" name="GRADE_INPUT_GRADE[]"  value="<?=$FINAL_GRADE?>" />
				<? } ?>
		</td>
	</tr>
<?	$res_cs->MoveNext();
} ?>
