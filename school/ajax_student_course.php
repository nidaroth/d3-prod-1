<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');

if($REGISTRAR_ACCESS == 0){
	header("location:../index");
	exit;
}

$PK_STUDENT_COURSE			= $_REQUEST['PK_STUDENT_COURSE'];
$PK_CAMPUS_PROGRAM_COURSE	= $_REQUEST['PK_CAMPUS_PROGRAM_COURSE'];
$course_id					= $_REQUEST['course_id'];
$PK_STUDENT_MASTER111		= $_REQUEST['sid'];

//ticket #1240
$res_sp_set = $db->Execute("SELECT GRADE_DISPLAY_TYPE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

if($PK_CAMPUS_PROGRAM_COURSE != '') {
	$res_11 = $db->Execute("select COURSE_CODE,M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM,COURSE_ORDER,S_COURSE.PK_COURSE from M_CAMPUS_PROGRAM_COURSE,M_CAMPUS_PROGRAM,S_COURSE WHERE PK_CAMPUS_PROGRAM_COURSE = '$PK_CAMPUS_PROGRAM_COURSE' AND M_CAMPUS_PROGRAM_COURSE.ACTIVE = 1 AND M_CAMPUS_PROGRAM_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_CAMPUS_PROGRAM_COURSE.PK_CAMPUS_PROGRAM = M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM AND S_COURSE.PK_COURSE = M_CAMPUS_PROGRAM_COURSE.PK_COURSE ");
	$PK_CAMPUS_PROGRAM 		= $res_11->fields['PK_CAMPUS_PROGRAM'];
	$PROGRAM_COURSE_ORDER 	= $res_11->fields['COURSE_ORDER'];
	$PK_COURSE 				= $res_11->fields['PK_COURSE'];
	$PK_STUDENT_ENROLLMENT  = $_REQUEST['eid'];
	$PK_COURSE_OFFERING		= '';
	$FINAL_GRADE			= '';
	$COURSE_UNITS			= '';
	
	$FINAL_GRADE_GRADE					= '';
	$FINAL_NUMERIC_GRADE				= ''; //Ticket #1240
	$FINAL_GRADE_NUMBER_GRADE			= '';
	$FINAL_GRADE_CALCULATE_GPA			= '';
	$FINAL_GRADE_UNITS_ATTEMPTED		= '';
	$FINAL_GRADE_UNITS_COMPLETED		= '';
	$FINAL_GRADE_UNITS_IN_PROGRESS		= '';
	$FINAL_GRADE_WEIGHTED_GRADE_CALC	= '';
	$FINAL_GRADE_RETAKE_UPDATE			= '';
	
	$res_sts1 = $db->Execute("SELECT PK_COURSE_OFFERING_STUDENT_STATUS FROM M_COURSE_OFFERING_STUDENT_STATUS WHERE PK_COURSE_OFFERING_STUDENT_STATUS_MASTER = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$PK_COURSE_OFFERING_STUDENT_STATUS = $res_sts1->fields['PK_COURSE_OFFERING_STUDENT_STATUS'];
} else if($PK_STUDENT_COURSE != '') {
	$res_11 = $db->Execute("select * from S_STUDENT_COURSE WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$PK_CAMPUS_PROGRAM 					= $res_11->fields['PK_CAMPUS_PROGRAM'];
	$PROGRAM_COURSE_ORDER 				= $res_11->fields['PROGRAM_COURSE_ORDER'];
	$PK_TERM_MASTER 					= $res_11->fields['PK_TERM_MASTER'];
	$PK_COURSE_OFFERING					= $res_11->fields['PK_COURSE_OFFERING'];
	$PK_STUDENT_MASTER					= $res_11->fields['PK_STUDENT_MASTER'];
	$PK_STUDENT_ENROLLMENT  			= $res_11->fields['PK_STUDENT_ENROLLMENT'];
	$COURSE_UNITS						= $res_11->fields['COURSE_UNITS'];
	$FINAL_GRADE						= $res_11->fields['FINAL_GRADE'];
	
	/* Ticket # 1251 
	$FINAL_GRADE_GRADE					= $res_11->fields['FINAL_GRADE_GRADE'];
	$FINAL_GRADE_NUMBER_GRADE			= $res_11->fields['FINAL_GRADE_NUMBER_GRADE'];
	$FINAL_GRADE_CALCULATE_GPA			= $res_11->fields['FINAL_GRADE_CALCULATE_GPA'];
	$FINAL_GRADE_UNITS_ATTEMPTED		= $res_11->fields['FINAL_GRADE_UNITS_ATTEMPTED'];
	$FINAL_GRADE_UNITS_COMPLETED		= $res_11->fields['FINAL_GRADE_UNITS_COMPLETED'];
	$FINAL_GRADE_UNITS_IN_PROGRESS		= $res_11->fields['FINAL_GRADE_UNITS_IN_PROGRESS'];
	$FINAL_GRADE_WEIGHTED_GRADE_CALC	= $res_11->fields['FINAL_GRADE_WEIGHTED_GRADE_CALC'];
	$FINAL_GRADE_RETAKE_UPDATE			= $res_11->fields['FINAL_GRADE_RETAKE_UPDATE'];
	*/
	
	/* Ticket # 1251 */
	$PK_COURSE_OFFERING_STUDENT_STATUS	= $res_11->fields['PK_COURSE_OFFERING_STUDENT_STATUS'];
	$FINAL_NUMERIC_GRADE				= $res_11->fields['NUMERIC_GRADE']; //Ticket #1240
	$res_11 = $db->Execute("select * from S_GRADE WHERE PK_GRADE = '$FINAL_GRADE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$FINAL_GRADE_GRADE					= $res_11->fields['GRADE'];
	$FINAL_GRADE_NUMBER_GRADE			= $res_11->fields['NUMBER_GRADE'];
	$FINAL_GRADE_CALCULATE_GPA			= $res_11->fields['CALCULATE_GPA'];
	$FINAL_GRADE_UNITS_ATTEMPTED		= $res_11->fields['UNITS_ATTEMPTED'];
	$FINAL_GRADE_UNITS_COMPLETED		= $res_11->fields['UNITS_COMPLETED'];
	$FINAL_GRADE_UNITS_IN_PROGRESS		= $res_11->fields['UNITS_IN_PROGRESS'];
	$FINAL_GRADE_WEIGHTED_GRADE_CALC	= $res_11->fields['WEIGHTED_GRADE_CALC'];
	$FINAL_GRADE_RETAKE_UPDATE			= $res_11->fields['RETAKE_UPDATE'];
	/* Ticket # 1251 */
		
	$c_in_num_grade_tot += $res_11->fields['NUMERIC_GRADE']; //Ticket #1240
} else {
	$PK_CAMPUS_PROGRAM 		= '';
	$PROGRAM_COURSE_ORDER 	= '';
	$PK_TERM_MASTER 		= '';
	$PK_COURSE_OFFERING		= '';
	$FINAL_GRADE			= '';
	$COURSE_UNITS			= '';
	
	$FINAL_GRADE_GRADE					= '';
	$FINAL_NUMERIC_GRADE				= ''; //Ticket #1240
	$FINAL_GRADE_NUMBER_GRADE			= '';
	$FINAL_GRADE_CALCULATE_GPA			= '';
	$FINAL_GRADE_UNITS_ATTEMPTED		= '';
	$FINAL_GRADE_UNITS_COMPLETED		= '';
	$FINAL_GRADE_UNITS_IN_PROGRESS		= '';
	$FINAL_GRADE_WEIGHTED_GRADE_CALC	= '';
	$FINAL_GRADE_RETAKE_UPDATE			= '';
	
	$PK_STUDENT_ENROLLMENT  = $_REQUEST['eid'];
	
	$res_sts1 = $db->Execute("SELECT PK_COURSE_OFFERING_STUDENT_STATUS FROM M_COURSE_OFFERING_STUDENT_STATUS WHERE PK_COURSE_OFFERING_STUDENT_STATUS_MASTER = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$PK_COURSE_OFFERING_STUDENT_STATUS = $res_sts1->fields['PK_COURSE_OFFERING_STUDENT_STATUS'];
}?>
<tr id="student_course_div_<?=$course_id?>" >
	<td >
		<input type="hidden" name="PK_CAMPUS_PROGRAM_COURSE[]"  value="<?=$PK_CAMPUS_PROGRAM_COURSE?>" />
		<input type="hidden" name="PK_STUDENT_COURSE[]" value="<?=$PK_STUDENT_COURSE?>" />
		<input type="hidden" name="course_id[]" id="course_id" value="<?=$course_id?>" />
		<? if($PK_TERM_MASTER == '' || $PK_TERM_MASTER == 0){
			/* Ticket #1149 - term */
			$_REQUEST['pk_campus_id'] = $_REQUEST['campus_id_1']; 
			$_REQUEST['obj_id']	  	  = 'STUDENT_COURSE_PK_TERM_MASTER_'.$course_id; 
			$_REQUEST['obj_name']	  = 'STUDENT_COURSE_PK_TERM_MASTER[]'; 
			$_REQUEST['required']	  = '1'; 
			$_REQUEST['style']	  	  = "width:100px"; 
			$_REQUEST['onchange']	  = "get_course_offering(this.value,'".$course_id."')";
			$_REQUEST['onclick_fun']  = 1; 
			include("ajax_get_term_master_from_campus_for_add_course.php");  //Ticket # 1781 
			/* Ticket #1149 - term */ ?>
		<? } else { 
			//Ticket #1205
			$res_type = $db->Execute("select IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$PK_TERM_MASTER' "); 
			echo $res_type->fields['BEGIN_DATE']; ?>
			<input type="hidden" id="STUDENT_COURSE_PK_TERM_MASTER_<?=$course_id?>" name="STUDENT_COURSE_PK_TERM_MASTER[]" value="<?=$PK_TERM_MASTER?>" />
		<? } ?>
	</td>
	<td >
		<div id="PK_COURSE_OFFERING_DIV_<?=$course_id?>" style="width:130px" >
			<? if($PK_COURSE_OFFERING == '' || $PK_COURSE_OFFERING == 0){
				$_REQUEST['val'] 				= $PK_TERM_MASTER;
				$_REQUEST['id'] 				= $course_id;
				$_REQUEST['multiple'] 			= 1;
				$_REQUEST['filter_campus'] 		= 1;
				$_REQUEST['s_id'] 				= $PK_STUDENT_MASTER111;
				$_REQUEST['PK_COURSE_OFFERING'] = $PK_COURSE_OFFERING; 
				$_REQUEST['make_required'] 		= 1; //Ticket #1663
				
				include("ajax_get_course_offering_from_course_term.php"); 
			} else { 
				/* Ticket # 1740  */
				$res_type = $db->Execute("select PK_COURSE_OFFERING, COURSE_CODE, S_COURSE.TRANSCRIPT_CODE, S_COURSE.COURSE_DESCRIPTION, SESSION, SESSION_NO from S_COURSE, S_COURSE_OFFERING LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION WHERE S_COURSE_OFFERING.ACTIVE = 1 AND S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_COURSE_OFFERING.ACTIVE = 1 AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE ");
				echo $res_type->fields['COURSE_CODE']." - ".$res_type->fields['TRANSCRIPT_CODE']; 
				/* Ticket # 1740  */ ?>
				<input type="hidden" id="PK_COURSE_OFFERING_<?=$course_id?>" name="PK_COURSE_OFFERING[]" value="<?=$PK_COURSE_OFFERING?>" />
				
			<? } ?>
		</div>
	</td>
	<td >
		<div id="COURSE_SESSION_DIV_<?=$course_id?>" style="padding-top: 11px;width:60px;" > <!-- Ticket # 1221   --> <!-- Ticket # 1686 -->
			<? $_REQUEST['PK_COURSE_OFFERING'] = $PK_COURSE_OFFERING; 
			include("ajax_get_course_offering_session.php"); ?>
		</div>
	</td>
	<td >
		<select id="PK_COURSE_OFFERING_STUDENT_STATUS_<?=$course_id?>" name="PK_COURSE_OFFERING_STUDENT_STATUS[]" class="form-control" style="width:130px" > <!-- Ticket # 1686 -->
			<option selected></option>
			<? /* Ticket #1689  */
			$res_type = $db->Execute("select PK_COURSE_OFFERING_STUDENT_STATUS, CONCAT(COURSE_OFFERING_STUDENT_STATUS, ' - ', DESCRIPTION) as COURSE_OFFERING_STUDENT_STATUS, ACTIVE FROM M_COURSE_OFFERING_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, COURSE_OFFERING_STUDENT_STATUS ASC");
			while (!$res_type->EOF) { 
				$option_label = $res_type->fields['CODE'];
				if($res_type->fields['ACTIVE'] == 0)
					$option_label .= " (Inactive)"; ?>
				<option value="<?=$res_type->fields['PK_COURSE_OFFERING_STUDENT_STATUS'] ?>" <? if($res_type->fields['PK_COURSE_OFFERING_STUDENT_STATUS'] == $PK_COURSE_OFFERING_STUDENT_STATUS) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$res_type->fields['COURSE_OFFERING_STUDENT_STATUS'] ?></option>
			<?	$res_type->MoveNext();
			} 
			 /* Ticket #1689  */?>
		</select>
	</td>
	<td >
		<div id="COURSE_DESCRIPTION_DIV_<?=$course_id?>" style="padding-top: 11px;width:270px;" ><!-- Ticket # 1686 -->
			<? $_REQUEST['id'] = $PK_COURSE_OFFERING; 
			include("ajax_get_course_desc_from_course_offering.php"); ?>
		</div>
	</td>
	<td >
		<div id="COURSE_UNITS_APPTEMPTED_DIV_<?=$course_id?>" style="padding-top: 11px;width:80px;text-align:right" > <!-- Ticket # 1240   -->
			<? /* Ticket #1146  */
			if($FINAL_GRADE_UNITS_ATTEMPTED == 1) {
				echo $COURSE_UNITS;
				$c_in_att_tot += $COURSE_UNITS;
			} else
				echo "0.00";
			/* Ticket #1146  */	
		
			if($FINAL_GRADE_CALCULATE_GPA == 1) {
				$c_in_cu_gnu += $COURSE_UNITS * $FINAL_GRADE_NUMBER_GRADE; 
				
				if($FINAL_GRADE_UNITS_COMPLETED == 1)
					$c_in_cu_tot += $COURSE_UNITS;
					
			} ?>
		</div>
	</td>
	<td >
		<div id="COURSE_UNITS_COMPLETED_DIV_<?=$course_id?>" style="padding-top: 11px;width:80px;text-align:right" > <!-- Ticket # 1240   -->
		<? if($FINAL_GRADE_UNITS_COMPLETED == 1) {
			echo $COURSE_UNITS;
			$c_in_comp_tot += $COURSE_UNITS;
		} else
			echo '0.00';
			
		if($FINAL_GRADE_UNITS_IN_PROGRESS == 1)
			$c_in_prog_tot += $COURSE_UNITS;	
		?>
		</div>
	</td>
	
	<!-- Ticket # 1240   -->
	<? if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?>
	<td >
		<div style="padding-top: 11px;width:50px;" >
		<?=$FINAL_GRADE_GRADE ?>
		</div>
	</td>
	
	<? } 
	?>
	<!-- <td ><div style="padding-top: 11px;width:50px;" ><?=MY.': '.number_format_value_checker(($summation_of_gpa/$summation_of_weight),2)?></div></td> -->
	<?
	if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?>
	<td >
		<div style="padding-top: 11px;width:50px;text-align:right;" > <!-- Ticket # 1686 -->
			<?=$FINAL_NUMERIC_GRADE?>
		</div>
	</td>
	<? } ?>
	<!-- Ticket # 1240   -->
	
	<td >
		<!-- Ticket # 1686 -->
		<select id="STUDENT_COURSE_PK_STUDENT_ENROLLMENT_<?=$course_id?>" name="STUDENT_COURSE_PK_STUDENT_ENROLLMENT[]" class="form-control" style="width:250px"  >
			<? $res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT, CAMPUS_CODE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER111' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" <? if($res_type->fields['PK_STUDENT_ENROLLMENT'] == $PK_STUDENT_ENROLLMENT) echo "selected"; ?> <? if($res_type->fields['IS_ACTIVE_ENROLLMENT'] == 1) echo "class='option_red'";  ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['CAMPUS_CODE']?></option> 
			<?	$res_type->MoveNext();
			} ?>
		</select>
		<!-- Ticket # 1686 -->
	</td>
	<td >
		<? if($REGISTRAR_ACCESS == 2 || $REGISTRAR_ACCESS == 3){
			/* Ticket # 1221   */
			if($PK_STUDENT_COURSE != '') { ?>
				<a href="javascript:void(0);" onclick="delete_row('<?=$PK_STUDENT_COURSE?>','stu_course')" title="<?=DELETE?>" class="btn delete-color btn-circle" style="width:25px; height:25px; padding: 2px;" ><i class="far fa-trash-alt"></i> </a>
				<a href="course_offering?id=<?=$PK_COURSE_OFFERING?>" title="Course" class="btn consolidate-color btn-circle" style="width:25px; height:25px; padding: 2px; background-color: #220A25 !important;" ><i class="mdi mdi-book-open-page-variant"></i> </a>
			<? } else { ?>
					<a href="javascript:void(0);" onclick="delete_row('<?=$course_id?>','stu_new_course')" title="<?=DELETE?>" class="btn delete-color btn-circle" style="width:25px; height:25px; padding: 2px;" ><i class="far fa-trash-alt"></i> </a>
			<? }
			/* Ticket # 1221   */
		} ?>
	</td>
</tr>