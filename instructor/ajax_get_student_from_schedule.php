<? require_once("../global/config.php"); 
require_once("../language/attendance_entry.php");
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

/* Ticket # 1601  */
$res_set = $db->Execute("SELECT ENABLE_ATTENDANCE_ACTIVITY_TYPES, ENABLE_ATTENDANCE_COMMENTS FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
$ENABLE_ATTENDANCE_ACTIVITY_TYPES 	= $res_set->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'];
$ENABLE_ATTENDANCE_COMMENTS 		= $res_set->fields['ENABLE_ATTENDANCE_COMMENTS'];
/* Ticket # 1601  */

$res_set = $db->Execute("SELECT ALLOW_INSTRUCTORS_UNPOST_ATTENDANCE FROM Z_ACCOUNT_INSTRUCTOR_PORTAL_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
$ALLOW_INSTRUCTORS_UNPOST_ATTENDANCE = $res_set->fields['ALLOW_INSTRUCTORS_UNPOST_ATTENDANCE'];

?>
<div class="row">
	<div class="col-12 form-group" style="font-weight:bold" >
		<center>
		<? $res_cs = $db->Execute("select CONCAT(DATE_FORMAT(SCHEDULE_DATE,'%m/%d/%Y'),' ',DATE_FORMAT(START_TIME,'%h:%i %p'),' - ',DATE_FORMAT(END_TIME,'%h:%i %p')) AS SCHEDULE_DATE, PK_ATTENDANCE_CODE, COMPLETED from S_COURSE_OFFERING_SCHEDULE_DETAIL LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING WHERE S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$_REQUEST[val]' ");
		echo $res_cs->fields['SCHEDULE_DATE'];
		$PK_ATTENDANCE_CODE_DEF = $res_cs->fields['PK_ATTENDANCE_CODE'];
		$ALL_COMPLETED 			= $res_cs->fields['COMPLETED'];
		if($PK_ATTENDANCE_CODE_DEF == '' || $PK_ATTENDANCE_CODE_DEF == 0) 
			$PK_ATTENDANCE_CODE_DEF = 14;  
			
		$disabled_fields = '';
		if($ALL_COMPLETED == 1)
			$disabled_fields = 'disabled'; ?>
		</center>
	</div>
</div>

<div class="tableFixHead" >
	<table class="table table-hover table-striped" >
		<thead>
			<tr>
				<th class="sticky_header" scope="col" ><?=STUDENTS?></th>
				<th class="sticky_header" scope="col" ><?=ENROLLMENT?></th>
				<th class="sticky_header" scope="col" ><?=ATTENDANCE_HOURS?></th>
				<th class="sticky_header" scope="col" ><?=ATTENDANCE_CODE?></th>
				<? if($ENABLE_ATTENDANCE_ACTIVITY_TYPES == 1){ ?>
					<th class="sticky_header" scope="col" rowspan="2" ><?=ACTIVITY_TYPE?></th>
				<? } ?>
				<? /* Ticket # 1601  */
				if($ENABLE_ATTENDANCE_COMMENTS == 1){ ?>
					<th class="sticky_header" scope="col" rowspan="2" ><?=COMMENTS?></th>
				<? } 
				/* Ticket # 1601  */?>
			</tr>
		</thead>
		<tbody>
			<? $res_cs = $db->Execute("select PK_STUDENT_SCHEDULE, S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT, S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME, HOURS, PK_STUDENT_ENROLLMENT, PK_SCHEDULE_TYPE, PK_COURSE_OFFERING_SCHEDULE_DETAIL from S_STUDENT_SCHEDULE, S_STUDENT_MASTER WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$_REQUEST[val]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_SCHEDULE.PK_STUDENT_MASTER AND PK_SCHEDULE_TYPE = 1 ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC "); 
			
			$res_def_att_typ = $db->Execute("select PK_ATTENDANCE_ACTIVITY_TYPES from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$_REQUEST[val]' "); 
			$total_hour = 0;
			while (!$res_cs->EOF) { 
				$PK_STUDENT_SCHEDULE 	= $res_cs->fields['PK_STUDENT_SCHEDULE']; 
				$PK_STUDENT_ENROLLMENT 	= $res_cs->fields['PK_STUDENT_ENROLLMENT']; 
				
				$res_en = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00', '', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT, CAMPUS_CODE, ALLOW_ATTENDANCE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
				$ALLOW_ATTENDANCE = $res_en->fields['ALLOW_ATTENDANCE']; 
				
				/* Ticket #1145  */
				$att_added = 0;
				$res_att = $db->Execute("SELECT * FROM S_STUDENT_ATTENDANCE WHERE PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' "); 
				if($res_att->RecordCount() == 0) {
					$PK_STUDENT_ATTENDANCE 		 = '';
					$ATTENDANCE_HOURS 			 = number_format_value_checker($res_cs->fields['HOURS'],5);
					$PK_ATTENDANCE_CODE			 = $PK_ATTENDANCE_CODE_DEF; 
					$PK_ATTENDANCE_ACTIVITY_TYPE = $res_def_att_typ->fields['PK_ATTENDANCE_ACTIVITY_TYPES'];
					$ATTENDANCE_COMMENTS		 = ''; //Ticket # 1601 
				} else { 
					$att_added = 1;
					$PK_STUDENT_ATTENDANCE 		 = $res_att->fields['PK_STUDENT_ATTENDANCE'];
					$ATTENDANCE_HOURS 			 = $res_att->fields['ATTENDANCE_HOURS'];
					$PK_ATTENDANCE_CODE			 = $res_att->fields['PK_ATTENDANCE_CODE'];
					$PK_ATTENDANCE_ACTIVITY_TYPE = $res_att->fields['PK_ATTENDANCE_ACTIVITY_TYPESS'];
					$ATTENDANCE_COMMENTS		 = $res_att->fields['ATTENDANCE_COMMENTS']; //Ticket # 1601 
				} 
				
				$ATTENDANCE_HOURS  = str_replace(",","",number_format_value_checker($ATTENDANCE_HOURS,2));
				
				$total_hour += $ATTENDANCE_HOURS;?>
				<tr <? if($ALLOW_ATTENDANCE == 0) { ?> style="color:red" <? } ?> >
					<td style="width:20%" >
						<? if($ALL_COMPLETED == 0){ ?>
						<input type="hidden" name="PK_STUDENT_SCHEDULE[]" value="<?=$PK_STUDENT_SCHEDULE?>" />
						
						<input type="hidden" name="PK_STUDENT_MASTER[]" value="<?=$res_cs->fields['PK_STUDENT_MASTER']?>" />
						<input type="hidden" name="PK_STUDENT_ENROLLMENT[]" value="<?=$res_cs->fields['PK_STUDENT_ENROLLMENT']?>" />
						<? } ?>
						
						<input type="hidden" name="PK_STUDENT_ATTENDANCE[]" value="<?=$PK_STUDENT_ATTENDANCE?>" />
						<?=$res_cs->fields['NAME']?>
					</td>
					
					<td style="width:25%" >
						<? echo $res_en->fields['CODE'].' - '.$res_en->fields['BEGIN_DATE_1'].' - '.$res_en->fields['STUDENT_STATUS'].' - '.$res_en->fields['CAMPUS_CODE']; ?>
					</td>
					
					<td style="width:5%" >
						<input type="text" class="form-control" placeholder="" <? if($ALL_COMPLETED == 0){ ?> name="ATTENDANCE_HOURS[]" <? } else { ?> name="ATTENDANCE_HOURS_COMPLETED[]" disabled <? } ?> id="ATTENDANCE_HOURS_<?=$PK_STUDENT_SCHEDULE?>" value="<?=$ATTENDANCE_HOURS?>" style="text-align:right" <?=$disabled_fields?> />
					</td>
					<td style="width:15%" >
						<select id="PK_ATTENDANCE_CODE_<?=$PK_STUDENT_SCHEDULE?>" <? if($ALL_COMPLETED == 0){ ?> name="PK_ATTENDANCE_CODE[]" <? } else { ?> disabled <? } ?> class="form-control <? if($ALL_COMPLETED == 0) echo "required-entry"; ?>" onchange="set_att_hour(this.value,<?=$PK_STUDENT_SCHEDULE?>,'<?=$PK_ATTENDANCE_CODE_DEF?>', '<?=$res_cs->fields['HOURS']?>')" <?=$disabled_fields?> >
							<option selected></option>
							 <? $att_code_cond 	= "";
							$att_code_table 	= "";
							$union = "";
							/* Ticket #1145  */
							
							$cond22 = " AND M_ATTENDANCE_CODE.ACTIVE = 1 ";
							if($PK_ATTENDANCE_CODE != '' && $PK_ATTENDANCE_CODE > 0 && $att_added == 1){
								//$union = " UNION select PK_ATTENDANCE_CODE,CONCAT(CODE,' - ',ATTENDANCE_CODE) AS ATTENDANCE_CODE, CODE from M_ATTENDANCE_CODE WHERE PK_ATTENDANCE_CODE = '$PK_ATTENDANCE_CODE' ";
								$cond22 = " AND (M_ATTENDANCE_CODE.ACTIVE = 1 OR M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = '$PK_ATTENDANCE_CODE' ) ";
							}
							
							$res_type = $db->Execute("SELECT * FROM (select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE, CONCAT(CODE,' - ',S_ATTENDANCE_CODE.DESCRIPTION) AS ATTENDANCE_CODE, CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $union) AS TEMP order by CODE ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_ATTENDANCE_CODE'] ?>" <? if($res_type->fields['PK_ATTENDANCE_CODE'] == $PK_ATTENDANCE_CODE) echo "selected"; ?> ><?=$res_type->fields['ATTENDANCE_CODE']?></option>
							<?	$res_type->MoveNext();
							} /* Ticket #1145  */ ?>
						</select>
					</td>
					
					<? if($ENABLE_ATTENDANCE_ACTIVITY_TYPES == 1){ ?>
					<td style="width:15%" >
						<select id="PK_ATTENDANCE_ACTIVITY_TYPE_<?=$PK_STUDENT_SCHEDULE?>" <? if($ALL_COMPLETED == 0){ ?> name="PK_ATTENDANCE_ACTIVITY_TYPE[]" <? } else { ?> disabled <? } ?> class="form-control" <?=$disabled_fields?> >
							<option selected></option>
							<? $res_type = $db->Execute("select PK_ATTENDANCE_ACTIVITY_TYPE, ATTENDANCE_ACTIVITY_TYPE from M_ATTENDANCE_ACTIVITY_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ATTENDANCE_ACTIVITY_TYPE ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE'] ?>" <? if($res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE'] == $PK_ATTENDANCE_ACTIVITY_TYPE) echo "selected"; ?> ><?=$res_type->fields['ATTENDANCE_ACTIVITY_TYPE']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</td>
					<? } ?>
					
					<? /* Ticket # 1601  */
					if($ENABLE_ATTENDANCE_COMMENTS == 1){ ?>
						<td style="width:15%" >
							<input type="text" id="ATTENDANCE_COMMENTS_<?=$PK_STUDENT_SCHEDULE?>" <? if($ALL_COMPLETED == 0){ ?> name="ATTENDANCE_COMMENTS[]" <? } else { ?> disabled <? } ?> class="form-control" <?=$disabled_fields?>  value="<?=$ATTENDANCE_COMMENTS?>" />
						</td>
					<? } 
					/* Ticket # 1601  */?>
					
				</tr>
			<?	$res_cs->MoveNext();
			} ?>
		</tbody>
	</table>
</div>
<br />
<div class="col-12 form-group text-right">
	<? if($ALL_COMPLETED == 0){ ?>
	<button type="button" onclick="save_form(1)" class="btn waves-effect waves-light btn-info" id="SAVE_BTN" style="float:right" ><?=MARK_ATTENDANCE_COMPLETE?></button>
	
	<button type="button" onclick="save_form(0)" class="btn waves-effect waves-light btn-info" id="SAVE_BTN" style="float:right;margin-right:5px;" ><?=SAVE?></button>
	<? } else {
		/* Ticket # 1795 
		 if($_SESSION['PK_ROLES'] == 2 || $_SESSION['PK_ROLES'] == 3) { ?>
			<button type="button" onclick="save_form(3)" class="btn waves-effect waves-light btn-info" id="SAVE_BTN" style="float:right" ><?=UNPOST_ATTENDANCE?></button>
		<? } else 
			echo '<b style="color:red">Attendance has been posted. Only a user with access to the Registrar module can unpost attendance to make changes.</b>'; 
		Ticket # 1795 */
		
		/* Ticket # 1795  */
		/* Ticket # 1815  */
		if($_REQUEST['panel'] == "ins") {
			if($ALLOW_INSTRUCTORS_UNPOST_ATTENDANCE == 1) { ?>
				<button type="button" onclick="save_form(3)" class="btn waves-effect waves-light btn-info" id="SAVE_BTN" style="float:right" ><?=UNPOST_ATTENDANCE?></button>
			<? } else {
				echo '<b style="color:red">Attendance has been posted. Only a user with access to the Registrar module can unpost attendance to make changes.</b>'; 
			} 
		} else { ?>
			<button type="button" onclick="save_form(3)" class="btn waves-effect waves-light btn-info" id="SAVE_BTN" style="float:right" ><?=UNPOST_ATTENDANCE?></button>
		<? }
		/* Ticket # 1815  */
		/* Ticket # 1795  */
	} ?>
</div>