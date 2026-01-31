<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");

$type 	= $_REQUEST['type'];
$t 		= $_REQUEST['t'];
if($type == 1 || $type == 13) { 
	//bulk assign ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ><?=REPRESENTATIVE?></option>
		<? $res_type = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.ACTIVE = 1 AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = 2 AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND TURN_OFF_ASSIGNMENTS = 0 order by CONCAT(FIRST_NAME,' ',LAST_NAME) ASC");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" ><?=$res_type->fields['NAME']?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
								
<? } else if($type == 2) {
	//bulk archive
} else if($type == 3) {
	//bulk unarchive
} else if($type == 4) {
	//Approve
} else if($type == 5) { 
	//bulk change status ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" onchange="show_end_date()" > <!-- Ticket # 1513 -->
		<option value="" ><?=STUDENT_STATUS?></option>
		<? $sts_cond = " AND ADMISSIONS = 0 ";
		if($t == 1)
			$sts_cond = " AND ADMISSIONS = 1 ";
		/* Ticket # 1513 */
		$res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, PK_END_DATE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 $sts_cond ");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" att_end_date="<?=$res_type->fields['PK_END_DATE']?>" ><?=$res_type->fields['STUDENT_STATUS']?></option>
		<?	$res_type->MoveNext();
		} /* Ticket # 1513 */ ?>
	</select>
<? } else if($type == 6) { 
	//bulk student group ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ><?=STUDENT_GROUP?></option>
		<? $res_type = $db->Execute("select PK_STUDENT_GROUP, STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($type == 7) { 
	//change campus ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ><?=CAMPUS?></option>
		<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by CAMPUS_CODE ASC");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_CAMPUS']?>" ><?=$res_type->fields['CAMPUS_CODE']?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($type == 8) { 
	//change CHANGE_DISTANCE_LEARNING ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ><?=DISTANCE_LEARNING?></option>
		<? $res_type = $db->Execute("select PK_DISTANCE_LEARNING, DISTANCE_LEARNING from M_DISTANCE_LEARNING WHERE ACTIVE = 1 order by DISTANCE_LEARNING ASC");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_DISTANCE_LEARNING']?>" ><?=$res_type->fields['DISTANCE_LEARNING']?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($type == 9) { 
	//change CHANGE_EXPECTED_GRAD_DATE ?>
	<input type="text" id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry date" value="" >
<? } else if($type == 10) { 
	//change CHANGE_FULL_PART_TIME ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ><?=FULL_PART_TIME ?></option>
		<? $res_type = $db->Execute("select PK_ENROLLMENT_STATUS, CONCAT(CODE,' - ', DESCRIPTION) as ENROLLMENT_STATUS from M_ENROLLMENT_STATUS WHERE ACTIVE = 1 order by ENROLLMENT_STATUS ASC");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_ENROLLMENT_STATUS']?>" ><?=$res_type->fields['ENROLLMENT_STATUS']?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($type == 11) { 
	//change CHANGE_MID_POINT_DATE ?>
	<input type="text" id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry date" value="" >
<? } else if($type == 12) { 
	//change CHANGE_SESSION ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ><?=SESSION ?></option>
		<? $res_type = $db->Execute("select PK_SESSION, SESSION from M_SESSION WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by SESSION ASC");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_SESSION']?>" ><?=$res_type->fields['SESSION']?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($type == 14) { 
	//change CHANGE_FIRST_TERM ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ><?=FIRST_TERM_DATE ?></option>
		<? $res_type = $db->Execute("select PK_TERM_MASTER, BEGIN_DATE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1,  IF(END_DATE = '0000-00-00','', DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by BEGIN_DATE DESC ");
		
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'].' - '.$res_type->fields['TERM_DESCRIPTION'] ?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($type == 15) { 
	//change CHANGE_PROGRAM ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ><?=PROGRAM ?></option>
		<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
		
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION'] ?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($type == 16) { 
	//CHANGE_FUNDING ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ><?=FUNDING ?></option> <!-- Ticket # 1906 -->
		<? $res_type = $db->Execute("select PK_FUNDING, CONCAT(FUNDING,' - ',DESCRIPTION) AS FUNDING from M_FUNDING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 ORDER BY FUNDING ");
		
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_FUNDING']?>" ><?=$res_type->fields['FUNDING'] ?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($type == 17) { 
	//CHANGE_1098T_REPORTING ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ><?=_1098T_REPORTING_TYPE ?></option>
		<? $res_type = $db->Execute("select PK_1098T_REPORTING_TYPE, REPORTING_TYPE from Z_1098T_REPORTING_TYPE WHERE ACTIVE = 1 ORDER BY REPORTING_TYPE ");
		
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_1098T_REPORTING_TYPE']?>" ><?=$res_type->fields['REPORTING_TYPE'] ?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($type == 18) { 
	//CHANGE_STRF_PAID_DATE ?>
	<input type="text" id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry date" value="" >
<? } else if($type == 19) { 
	//CHANGE_SPECIAL ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ><?=SPECIAL ?></option>
		<? $res_type = $db->Execute("select PK_SPECIAL, SPECIAL from Z_SPECIAL WHERE ACTIVE = 1 ORDER BY SPECIAL ");
		
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_SPECIAL']?>" ><?=$res_type->fields['SPECIAL'] ?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($type == 20) { 
	//CHANGE_PLACEMENT_STATUS ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ><?=PLACEMENT_STATUS ?></option>
		<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS, PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by PLACEMENT_STATUS ASC");
		
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_PLACEMENT_STATUS']?>" ><?=$res_type->fields['PLACEMENT_STATUS'] ?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else if($type == 21) { 
	//TERM_BLOCK ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ><?=TERM_BLOCK ?></option>
		<? $res_type = $db->Execute("select PK_TERM_BLOCK, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS END_DATE_1, DESCRIPTION from S_TERM_BLOCK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 Order by  BEGIN_DATE DESC");
		
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_TERM_BLOCK']?>" ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'].' - '.$res_type->fields['DESCRIPTION'] ?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
	
<? } else if ($type == 42 ) {  // DIAM-2366 ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" ><?=ORIGINAL_ENROLLMENT_STATUS ?></option>
		<? $res_type = $db->Execute("select PK_ENROLLMENT_STATUS, CONCAT(CODE,' - ', DESCRIPTION) as ENROLLMENT_STATUS from M_ENROLLMENT_STATUS WHERE ACTIVE = 1 order by ENROLLMENT_STATUS ASC");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_ENROLLMENT_STATUS']?>" ><?=$res_type->fields['ENROLLMENT_STATUS']?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<?php } else if ($type == 43 ) {  // DIAM-2370 ?>
	<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
		<option value="" >Not Set</option>
		<? $res_type = $db->Execute("select PK_RESIDENCY_TUITION_STATUS, CODE, DESCRIPTION from M_RESIDENCY_TUITION_STATUS WHERE ACTIVE = 1 ");
		while (!$res_type->EOF) 
		{
			$sName = $res_type->fields['DESCRIPTION']. " (".$res_type->fields['CODE'].")";
			 ?>
			<option value="<?=$res_type->fields['PK_RESIDENCY_TUITION_STATUS']?>" ><?=$sName?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<?php }?>
