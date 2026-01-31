<?php require_once('../global/config.php'); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../global/mail.php"); 
require_once("../global/texting.php"); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$_GET['id'] 	 = $_REQUEST['id'];
$GI_PK_STUDENT_GRADE = array();
$GI_PK_STUDENT_COURSE = array();
$GI_PK_STUDENT_MASTER = array();
//20 june 2023 disapper grade issue fixed 
$res_grade = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE FROM S_COURSE_OFFERING_GRADE WHERE PK_COURSE_OFFERING = '".$_GET['id'] ."' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
while (!$res_grade->EOF) {
	$PK_COURSE_OFFERING_GRADE = $res_grade->fields['PK_COURSE_OFFERING_GRADE'];
	$res_stu = $db->Execute("select PK_STUDENT_MASTER,PK_STUDENT_ENROLLMENT FROM S_STUDENT_COURSE WHERE PK_COURSE_OFFERING = '".$_GET['id'] ."' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	while (!$res_stu->EOF) {
		$PK_STUDENT_MASTER 		= $res_stu->fields['PK_STUDENT_MASTER'];
		$PK_STUDENT_ENROLLMENT 	= $res_stu->fields['PK_STUDENT_ENROLLMENT'];
		$res_stu_grade = $db->Execute("select PK_STUDENT_GRADE FROM S_STUDENT_GRADE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' ");
		if($res_stu_grade->RecordCount() == 0) {
			$STUDENT_GRADE['PK_COURSE_OFFERING_GRADE'] 	= $PK_COURSE_OFFERING_GRADE;
			$STUDENT_GRADE['PK_COURSE_OFFERING']		= $_GET['id'];
			$STUDENT_GRADE['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
			$STUDENT_GRADE['PK_STUDENT_MASTER'] 	 	= $PK_STUDENT_MASTER;
			$STUDENT_GRADE['PK_ACCOUNT'] 			 	= $_SESSION['PK_ACCOUNT'];
			$STUDENT_GRADE['CREATED_BY']  			 	= $_SESSION['PK_USER'];
			$STUDENT_GRADE['CREATED_ON']  			 	= date("Y-m-d H:i");
			db_perform('S_STUDENT_GRADE', $STUDENT_GRADE, 'insert');
			$recal_grade = 1;
		}

		$res_stu->MoveNext();
	}	
	$res_grade->MoveNext();
}

?>
	<table class="main-table stickyHead" id="grade_input_table">
		<thead class="sticky-header hl2">
			<tr>
			<th class="fixed-side" style="background:#9992A0;"><br /><?=STUDENT?></th>
			<th class="fixed-side" style="background:#9992A0;"><br /><?=STUDENT_ID?></th><!-- Ticket # 1963 -->
			<th style="background:#9992A0;" ><br /><?=FINAL_GRADE?></th>
			<th style="background:#9992A0;" colspan="2"><br /><?=FINAL_TOTAL?></th>
			<th style="background:#9992A0;" colspan="2"><br /><?=CURRENT_TOTAL?></th>
			<? $PK_COURSE_OFFERING_GRADE_ARR_123 = array(); //Ticket # 1290
			$result1 = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE,CODE,POINTS,WEIGHTED_POINTS FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$_GET[id]' ORDER BY GRADE_ORDER ASC, PK_COURSE_OFFERING_GRADE ASC "); //Ticket # 1290
			while (!$result1->EOF) { 
			$PK_COURSE_OFFERING_GRADE_ARR_123[] = $result1->fields['PK_COURSE_OFFERING_GRADE']; //Ticket # 1290 ?>
			<th style="background:#9992A0;" >
			<?=$result1->fields['CODE'].'<br />PTS:'.$result1->fields['POINTS'].'<br />WTD:'.$result1->fields['WEIGHTED_POINTS'] ?>
			<input type="hidden" name="GRADE_FINAL_PK_COURSE_OFFERING_GRADE[]" id="GRADE_FINAL_PK_COURSE_OFFERING_GRADE_<?=$result1->fields['PK_COURSE_OFFERING_GRADE']?>" value="<?=$result1->fields['PK_COURSE_OFFERING_GRADE']?>" >
			</th>
			<? $result1->MoveNext();
			} 

			$PK_COURSE_OFFERING_GRADE_ids = implode(",",$PK_COURSE_OFFERING_GRADE_ARR_123); //Ticket #1505 ?>

			</tr>
		</thead>
	<tbody >
	<? $res_stu = $db->Execute("select PK_STUDENT_COURSE, S_STUDENT_MASTER.PK_STUDENT_MASTER,FINAL_GRADE_GRADE, STUDENT_ID,  CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, FINAL_TOTAL_OBTAINED, FINAL_MAX_TOTAL, FINAL_TOTAL_GRADE, CURRENT_TOTAL_OBTAINED, CURRENT_MAX_TOTAL, CURRENT_TOTAL_GRADE, FINAL_GRADE, FINAL_SCALE_SETUP.GRADE AS FINAL_SCALE, CURRENT_SCALE_SETUP.GRADE AS CURRENT_SCALE, FINAL_TOTAL_GRADE_SCALE_SETUP.GRADE AS FINAL_TOTAL_SCALE, CURRENT_TOTAL_PK_GRADE_SCALE_DETAIL, FINAL_TOTAL_PK_GRADE_SCALE_DETAIL FROM  S_STUDENT_MASTER, S_STUDENT_ACADEMICS, 
	S_STUDENT_COURSE 
	
	LEFT JOIN S_GRADE as FINAL_SCALE_SETUP ON FINAL_GRADE = FINAL_SCALE_SETUP.PK_GRADE 
	LEFT JOIN S_GRADE as CURRENT_SCALE_SETUP ON CURRENT_TOTAL_GRADE = CURRENT_SCALE_SETUP.PK_GRADE 
	LEFT JOIN S_GRADE as FINAL_TOTAL_GRADE_SCALE_SETUP ON FINAL_TOTAL_GRADE = FINAL_TOTAL_GRADE_SCALE_SETUP.PK_GRADE 
	
	WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND  PK_COURSE_OFFERING = '$_GET[id]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC "); //Ticket # 1963
	while (!$res_stu->EOF) { 
		$PK_STUDENT_COURSE = $res_stu->fields['PK_STUDENT_COURSE'];
		$PK_STUDENT_MASTER = $res_stu->fields['PK_STUDENT_MASTER']; ?>
		<tr>
			<td class="fixed-side" style="background:#FFFFFF !important;" ><div style="width:150px"><?=$res_stu->fields['STU_NAME']?></div></td>
			<td class="fixed-side" style="background:#FFFFFF !important;" ><div style="width:150px"><?=$res_stu->fields['STUDENT_ID']?></div></td><!-- Ticket # 1963 -->
			<td>
				<div style="width:100px" id="FINAL_GRADE_<?=$PK_STUDENT_COURSE?>" ><?=$res_stu->fields['FINAL_GRADE_GRADE'];?></div>
				<input type="hidden" name="GRADE_INPUT_PK_STUDENT_COURSE_1[]" id="GRADE_INPUT_PK_STUDENT_COURSE_1_<?=$PK_STUDENT_COURSE?>" value="<?=$PK_STUDENT_COURSE?>" >
				<input type="hidden" name="GRADE_FINAL_GRADE[]" id="GRADE_FINAL_GRADE_<?=$PK_STUDENT_COURSE?>" value="<?=$res_stu->fields['FINAL_GRADE']?>" >
			</td>
			<td>
				<div style="width:200px" id="FINAL_TOTAL_<?=$PK_STUDENT_COURSE?>" >
					<? /* Ticket #1505 */
					//if($res_stu->fields['FINAL_TOTAL_OBTAINED'] > 0) {
					$res_stu_grade_1a = $db->Execute("SELECT PK_STUDENT_GRADE FROM S_STUDENT_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE IN ($PK_COURSE_OFFERING_GRADE_ids) AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND POINTS != ''"); 
					if($res_stu_grade_1a->RecordCount() > 0) {
					/* Ticket #1505 */
						$FINAL_PERCENTAGE  = number_format_value_checker(($res_stu->fields['FINAL_TOTAL_OBTAINED'] / $res_stu->fields['FINAL_MAX_TOTAL'] * 100),2);
						echo $res_stu->fields['FINAL_TOTAL_OBTAINED'].'/'.$res_stu->fields['FINAL_MAX_TOTAL'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$FINAL_PERCENTAGE.' %';
					} ?>
				</div>
				<input type="hidden" name="GRADE_INPUT_FINAL_TOTAL_OBTAINED[]" id="GRADE_INPUT_FINAL_TOTAL_OBTAINED_<?=$PK_STUDENT_COURSE?>" value="<?=$res_stu->fields['FINAL_TOTAL_OBTAINED']?>" >
				<input type="hidden" name="GRADE_INPUT_FINAL_MAX_TOTAL[]" id="GRADE_INPUT_FINAL_MAX_TOTAL_<?=$PK_STUDENT_COURSE?>" value="<?=$res_stu->fields['FINAL_MAX_TOTAL']?>" >
			</td>
			<td>
				<div style="width:30px" id="FINAL_TOTAL_GRADE_<?=$PK_STUDENT_COURSE?>" ><?=$res_stu->fields['FINAL_TOTAL_SCALE'];?></div>
				<input type="hidden" name="GRADE_INPUT_FINAL_TOTAL_GRADE[]" id="GRADE_INPUT_FINAL_TOTAL_GRADE_<?=$PK_STUDENT_COURSE?>" value="<?=$res_stu->fields['FINAL_TOTAL_GRADE']?>" >
				
				<input type="hidden" name="GRADE_INPUT_FINAL_TOTAL_PK_GRADE_SCALE_DETAIL[]" id="GRADE_INPUT_FINAL_TOTAL_PK_GRADE_SCALE_DETAIL_<?=$PK_STUDENT_COURSE?>" value="<?=$res_stu->fields['FINAL_TOTAL_PK_GRADE_SCALE_DETAIL']?>" >
				
			</td>
			<td>
				<div style="width:200px" id="CURRENT_TOTAL_<?=$PK_STUDENT_COURSE?>" >
					<? //if($res_stu->fields['CURRENT_TOTAL_OBTAINED'] > 0) { Ticket #1505
					if($res_stu_grade_1a->RecordCount() > 0) { //Ticket #1505
						$CURRENT_PERCENTAGE = number_format_value_checker(($res_stu->fields['CURRENT_TOTAL_OBTAINED'] / $res_stu->fields['CURRENT_MAX_TOTAL'] * 100),2);
						echo $res_stu->fields['CURRENT_TOTAL_OBTAINED'].'/'.$res_stu->fields['CURRENT_MAX_TOTAL'].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$CURRENT_PERCENTAGE.' %';
					} ?>
				</div>
				<input type="hidden" name="GRADE_INPUT_CURRENT_TOTAL_OBTAINED[]" id="GRADE_INPUT_CURRENT_TOTAL_OBTAINED_<?=$PK_STUDENT_COURSE?>" value="<?=$res_stu->fields['CURRENT_TOTAL_OBTAINED']?>" >
				<input type="hidden" name="GRADE_INPUT_CURRENT_MAX_TOTAL[]" id="GRADE_INPUT_CURRENT_MAX_TOTAL_<?=$PK_STUDENT_COURSE?>" value="<?=$res_stu->fields['CURRENT_MAX_TOTAL']?>" >
			</td>
			<td>
				<div style="width:30px" id="CURRENT_TOTAL_GRADE_<?=$PK_STUDENT_COURSE?>" ><?=$res_stu->fields['CURRENT_SCALE'];?></div>
				<input type="hidden" name="GRADE_INPUT_CURRENT_TOTAL_GRADE[]" id="GRADE_INPUT_CURRENT_TOTAL_GRADE_<?=$PK_STUDENT_COURSE?>" value="<?=$res_stu->fields['CURRENT_TOTAL_GRADE']?>" >
				
				<input type="hidden" name="GRADE_INPUT_CURRENT_TOTAL_PK_GRADE_SCALE_DETAIL[]" id="GRADE_INPUT_CURRENT_TOTAL_PK_GRADE_SCALE_DETAIL_<?=$PK_STUDENT_COURSE?>" value="<?=$res_stu->fields['CURRENT_TOTAL_PK_GRADE_SCALE_DETAIL']?>" >
			</td>
			<? /* Ticket # 1290 */
			foreach($PK_COURSE_OFFERING_GRADE_ARR_123 as $PK_COURSE_OFFERING_GRADE){
				$res_stu_grade = $db->Execute("SELECT PK_STUDENT_GRADE,POINTS FROM S_STUDENT_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); 
				
				$PK_STUDENT_GRADE = $res_stu_grade->fields['PK_STUDENT_GRADE'];  ?>
				<td >
					<input type="text" data-id="<?=$PK_STUDENT_COURSE?>" class="form-control grade_check stu_points_<?=$PK_STUDENT_MASTER?> COG_<?=$PK_COURSE_OFFERING_GRADE?>" placeholder="" name="GRADE_INPUT_POINTS[]" id="GRADE_INPUT_POINTS_<?=$PK_STUDENT_GRADE?>" value="<?=$res_stu_grade->fields['POINTS']?>" onchange="calc_grade(<?=$PK_STUDENT_GRADE?>,<?=$PK_STUDENT_COURSE?>,<?=$PK_STUDENT_MASTER?>,0)" onkeyup="check_number_validation(this)" style="width:80px;text-align:right;padding:0;margin: 0;height: 21px;min-height: 0;" />
					<input type="hidden" class="stu_grade_<?=$PK_STUDENT_MASTER?>" name="GRADE_INPUT_PK_STUDENT_GRADE[]" id="GRADE_INPUT_PK_STUDENT_GRADE_<?=$PK_STUDENT_GRADE?>" value="<?=$PK_STUDENT_GRADE?>" >
				</td>
			<? } 
			/* Ticket # 1290 */
			
			if($PK_STUDENT_GRADE!="") $GI_PK_STUDENT_GRADE[]  = $PK_STUDENT_GRADE ;
			if($PK_STUDENT_COURSE!="") $GI_PK_STUDENT_COURSE[] = $PK_STUDENT_COURSE ;
			if($PK_STUDENT_MASTER!="") $GI_PK_STUDENT_MASTER[] = $PK_STUDENT_MASTER ; ?>
		</tr>
	<?	$res_stu->MoveNext();
	} 
	?>
	<input type="hidden" name="GI_PK_STUDENT_GRADE" id="GI_PK_STUDENT_GRADE" value="<?php if(!empty($GI_PK_STUDENT_GRADE)) echo implode(',',$GI_PK_STUDENT_GRADE); ?>">	
	<input type="hidden" name="GI_PK_STUDENT_COURSE" id="GI_PK_STUDENT_COURSE" value="<?php if(!empty($GI_PK_STUDENT_COURSE)) echo implode(',',$GI_PK_STUDENT_COURSE); ?>">
	<input type="hidden" name="GI_PK_STUDENT_MASTER" id="GI_PK_STUDENT_MASTER" value="<?php  if(!empty($GI_PK_STUDENT_MASTER)) echo implode(',',$GI_PK_STUDENT_MASTER); ?>">
	</tbody>
</table>
