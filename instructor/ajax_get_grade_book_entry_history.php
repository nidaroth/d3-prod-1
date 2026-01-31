<? require_once("../global/config.php"); 
require_once("../language/instructor_grade_book_entry.php");
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}


//$PK_COURSE_OFFERING 		= $_REQUEST['co'];
$PK_COURSE_OFFERING_GRADE 	= $_REQUEST['cog'];  
$PK_STUDENT_ENROLLMENT 	= $_REQUEST['eid'];  
$view 	= $_REQUEST['view']; 
$PK_COURSE_OFFERING 	= $_REQUEST['id']; 
$last_date 	= $_REQUEST['last_date']; 

if($view == 1){ //By assignment

/* 20 june 2023 */
$res_grade = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE FROM S_COURSE_OFFERING_GRADE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND `PK_COURSE_OFFERING_GRADE` = '$PK_COURSE_OFFERING_GRADE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
while (!$res_grade->EOF) {
	$PK_COURSE_OFFERING_GRADE = $res_grade->fields['PK_COURSE_OFFERING_GRADE'];
	$res_stu = $db->Execute("select PK_STUDENT_MASTER,PK_STUDENT_ENROLLMENT FROM S_STUDENT_COURSE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	while (!$res_stu->EOF) {
		$PK_STUDENT_MASTER 		= $res_stu->fields['PK_STUDENT_MASTER'];
		$PK_STUDENT_ENROLLMENT 	= $res_stu->fields['PK_STUDENT_ENROLLMENT'];
		$res_stu_grade = $db->Execute("select PK_STUDENT_GRADE FROM S_STUDENT_GRADE_HISTROY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' AND `CREATED_ON` = '$last_date'");
		if($res_stu_grade->RecordCount() == 0) {
			$STUDENT_GRADE['PK_COURSE_OFFERING_GRADE'] 	= $PK_COURSE_OFFERING_GRADE;
			$STUDENT_GRADE['PK_COURSE_OFFERING']		= $PK_COURSE_OFFERING;
			$STUDENT_GRADE['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
			$STUDENT_GRADE['PK_STUDENT_MASTER'] 	 	= $PK_STUDENT_MASTER;
			$STUDENT_GRADE['PK_ACCOUNT'] 			 	= $_SESSION['PK_ACCOUNT'];
			$STUDENT_GRADE['CREATED_BY']  			 	= $_SESSION['PK_USER'];
			$STUDENT_GRADE['CREATED_ON']  			 	= date("Y-m-d H:i");
			db_perform('S_STUDENT_GRADE_HISTROY', $STUDENT_GRADE, 'insert');
			$recal_grade = 1;
		}

		$res_stu->MoveNext();
	}	
	$res_grade->MoveNext();
}
/* 20 june 2023 */

$result1 = $db->Execute("SELECT CODE,POINTS,WEIGHTED_POINTS FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' ");?>

<input type="hidden" id="GRADE_POINT" value="<?=$result1->fields['POINTS']?>" >
<table class="table table-bordered">
	<thead>
		<tr>
			<th ><?=STUDENTS?></th>
			<th ><?=GRADE?></th>
			<th ><?=POINTS?></th>
			<th ><?=PERCENTAGE?></th>
		</tr>
	</thead>
	<tbody>
		<? $res_stu = $db->Execute("select PK_STUDENT_COURSE, S_STUDENT_MASTER.PK_STUDENT_MASTER,CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS STU_NAME FROM  S_STUDENT_MASTER, S_STUDENT_COURSE WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC ");
		while (!$res_stu->EOF) { 
			$PK_STUDENT_COURSE = $res_stu->fields['PK_STUDENT_COURSE'];
			$PK_STUDENT_MASTER = $res_stu->fields['PK_STUDENT_MASTER']; 
			
			$res_stu_grade = $db->Execute("SELECT PK_STUDENT_GRADE,POINTS FROM S_STUDENT_GRADE_HISTROY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND `CREATED_ON` = '$last_date'"); 
			$PK_STUDENT_GRADE = $res_stu_grade->fields['PK_STUDENT_GRADE']; ?>
			
			<tr>
				<td ><?=$res_stu->fields['STU_NAME']?></td>
				<td >
					<input type="hidden" name="PK_STUDENT_GRADE[]" value="<?=$PK_STUDENT_GRADE?>" >
					<input type="hidden" id="TEMP_ASS_POINT_<?=$PK_STUDENT_GRADE?>" value="<?=$result1->fields['POINTS']?>" >
					
					<input type="text" class="form-control" placeholder="" name="GRADE_INPUT_POINTS[]" id="GRADE_INPUT_POINTS_<?=$PK_STUDENT_GRADE?>" value="<?=$res_stu_grade->fields['POINTS']?>" onchange="calc_per(<?=$PK_STUDENT_GRADE?>,<?=$result1->fields['POINTS']?>)" style="text-align:right;padding:0;margin: 0;height: 21px;min-height: 0;" />
				</td>
				<td ><div id="point_div_<?=$PK_STUDENT_GRADE?>" ><?=$res_stu_grade->fields['POINTS']?> / <?=$result1->fields['POINTS']?></div></td>
				<td ><div id="per_div_<?=$PK_STUDENT_GRADE?>" ><? echo number_format_value_checker((($res_stu_grade->fields['POINTS']) / $result1->fields['POINTS']) * 100).' %' ?></div></td>
			</tr>
		
		<?	$res_stu->MoveNext();
		} ?>
	</tbody>
</table>
<? }else if($view == 2){ // By student ?>
<?	
$result1 = $db->Execute("SELECT PK_STUDENT_COURSE FROM S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 
$PK_STUDENT_COURSE  = $result1->fields['PK_STUDENT_COURSE']; 

$result1 = $db->Execute("SELECT CODE,POINTS,WEIGHTED_POINTS FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' ");?>

<input type="hidden" id="GRADE_POINT" value="<?=$result1->fields['POINTS']?>" >
<table class="table table-bordered">
	<thead>
		<tr>
			<th ><?=ASSIGNMENT?></th>
			<th ><?=GRADE?></th>
			<th ><?=GRADE_BOOK_POINTS?></th>
		</tr>
	</thead>
	<tbody>
		<?  $result1 = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE,CODE,POINTS,WEIGHTED_POINTS,DESCRIPTION FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ORDER BY PK_COURSE_OFFERING_GRADE ASC ");
		while (!$result1->EOF) {
			$PK_COURSE_OFFERING_GRADE = $result1->fields['PK_COURSE_OFFERING_GRADE'];
			
			$res_stu_grade = $db->Execute("SELECT PK_STUDENT_GRADE,POINTS,PK_STUDENT_MASTER FROM S_STUDENT_GRADE_HISTROY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT'  AND `CREATED_ON` = '$last_date'"); 
			$PK_STUDENT_GRADE  = $res_stu_grade->fields['PK_STUDENT_GRADE']; 
			$PK_STUDENT_MASTER = $res_stu_grade->fields['PK_STUDENT_MASTER']; ?>
			<tr>
				<td ><?=$result1->fields['CODE'].' - '.$result1->fields['DESCRIPTION'] ?></td>
				<td >
					<input type="hidden" name="PK_STUDENT_GRADE[]" value="<?=$PK_STUDENT_GRADE?>" >
					
					<input type="text" class="form-control stu_points_<?=$PK_STUDENT_MASTER?> COG_<?=$PK_COURSE_OFFERING_GRADE?> " placeholder="" name="GRADE_INPUT_POINTS[]" id="GRADE_INPUT_POINTS_<?=$PK_STUDENT_GRADE?>" value="<?=$res_stu_grade->fields['POINTS']?>" onchange="calc_grade(<?=$PK_STUDENT_GRADE?>,<?=$PK_STUDENT_COURSE?>,<?=$PK_STUDENT_MASTER?>,0)" style="text-align:right;padding:0;margin: 0;height: 21px;min-height: 0;" />
					
					<input type="hidden" class="stu_grade_<?=$PK_STUDENT_MASTER?>" name="GRADE_INPUT_PK_STUDENT_GRADE[]" id="GRADE_INPUT_PK_STUDENT_GRADE_<?=$PK_STUDENT_GRADE?>" value="<?=$PK_STUDENT_GRADE?>" >
				</td>
				<td ><?=$result1->fields['POINTS']?></td>
			</tr>
		
		<?	$result1->MoveNext();
		} ?>
		<tr>
			<td colspan="3">
				<input type="hidden" id="TEMP_PK_STUDENT_GRADE" value="<?=$PK_STUDENT_GRADE?>" >
				<input type="hidden" id="TEMP_PK_STUDENT_COURSE" value="<?=$PK_STUDENT_COURSE?>" >
				<input type="hidden" id="TEMP_PK_STUDENT_MASTER" value="<?=$PK_STUDENT_MASTER?>" >
				
				<div id="CURRENT_TOTAL_<?=$PK_STUDENT_COURSE?>"></div>
			</td>
		</tr>
	</tbody>
</table>
<? } ?>