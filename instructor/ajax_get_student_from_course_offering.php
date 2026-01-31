<? require_once("../global/config.php"); 
require_once("../language/attendance_entry.php");
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

/* Ticket # 1459 Ticket # 1601 */
$res_set = $db->Execute("SELECT ENABLE_ATTENDANCE_ACTIVITY_TYPES, ENABLE_ATTENDANCE_COMMENTS FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
$ENABLE_ATTENDANCE_ACTIVITY_TYPES 	= $res_set->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'];
$ENABLE_ATTENDANCE_COMMENTS 		= $res_set->fields['ENABLE_ATTENDANCE_COMMENTS'];
/* Ticket # 1459 Ticket # 1601 */

$PK_COURSE_OFFERING = $_REQUEST['PK_COURSE_OFFERING'];
$stu_id 			= $_REQUEST['stu_id'];?>
<div class="row" id="student_<?=$stu_id?>" >
	<div class="col-md-2" style="flex: 12%;max-width: 12%;" >
		<input type="hidden" name="PK_STUDENT_ATTENDANCE[]" id="PK_STUDENT_ATTENDANCE_<?=$stu_id?>" value="<?=$_REQUEST['PK_STUDENT_ATTENDANCE']?>" >
		<input type="hidden" name="PK_STUDENT_SCHEDULE[]" id="PK_STUDENT_SCHEDULE_<?=$stu_id?>" value="<?=$_REQUEST['PK_STUDENT_SCHEDULE']?>" >
		
		<select id="PK_STUDENT_ENROLLMENT_<?=$stu_id?>" name="PK_STUDENT_ENROLLMENT[]" class="form-control required-entry" onchange="get_enrollment(this.value, '<?=$stu_id?>')" >
			<option selected></option>
			<? $res_type = $db->Execute("select S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT, S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME from S_STUDENT_COURSE, S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$_REQUEST[PK_COURSE_OFFERING]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND IS_ACTIVE_ENROLLMENT = 1 ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT'] ?>" <? if($_REQUEST['PK_STUDENT_ENROLLMENT'] == $res_type->fields['PK_STUDENT_ENROLLMENT']) echo "selected"; ?> ><?=$res_type->fields['NAME']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</div> 
	<div class="col-md-1" style="flex: 10%;max-width: 10%;" id="enrollment_div_<?=$stu_id?>" >
		<? if($_REQUEST['PK_STUDENT_ENROLLMENT'] > 0) {
			$_REQUEST['val'] = $_REQUEST['PK_STUDENT_ENROLLMENT'];
			include("ajax_get_student_enrollment_detail.php");
		}?>
	</div> 
	<? if($_REQUEST['show_date'] == 1){ ?>
	<div class="col-md-1" style="flex: 10%;max-width:10%;" >
		<input type="text" id="CLASS_DATE_<?=$stu_id?>" name="CLASS_DATE[]" value="<?=$_REQUEST['SCHEDULE_DATE']?>" class="form-control date required-entry" >
	</div>
	<? } ?>
	<div class="col-md-2" style="max-width: 6%;flex: 6%;" >
		<!-- Ticket # 670 --><input type="text" id="SCHEDULE_<?=$stu_id?>_HOURS" name="SCHEDULE_HOURS[]" value="<?php echo ($_REQUEST['HOURS'] != '') ? $_REQUEST['HOURS'] : '0.00'; ?>" class="form-control " placeholder="0.00" onchange="chnageDecimalVal(this.value,'SCHEDULE_<?=$stu_id?>_HOURS')" >
	</div>
	<div class="col-md-1" style="padding-right:0;max-width:8%;flex:8%;" >
		<input type="text" id="SCHEDULE_<?=$stu_id?>_START_TIME" name="SCHEDULE_START_TIME[]" value="<?=strtolower($_REQUEST['START_TIME'])?>" class="form-control timepicker" onchange="get_hour('<?=$stu_id?>')"><!-- Ticket # 670 -->
	</div>
	<div class="col-md-1" style="padding-right:0;max-width:8%;flex:8%;" >
		<input type="text" id="SCHEDULE_<?=$stu_id?>_END_TIME" name="SCHEDULE_END_TIME[]" value="<?=strtolower($_REQUEST['END_TIME'])?>" class="form-control timepicker" onchange="get_hour('<?=$stu_id?>')" ><!-- Ticket # 670 -->
		<div id="err_for_time_<?=$stu_id?>" style=""></div><!-- Ticket # 670 -->
	</div>
	
	<div class="col-md-2" style="flex: 8.5%;max-width: 8.5%;"  >
		<!-- Ticket # 670 --><input type="text" id="ATTENDANCE_<?=$stu_id?>_HOURS" name="ATTENDANCE_HOURS[]" value="<?php echo ($_REQUEST['ATTENDANCE_HOURS'] != '') ? $_REQUEST['ATTENDANCE_HOURS'] : '0.00'; ?>" class="form-control required-entry" placeholder="0.00" onchange="chnageDecimalVal(this.value,'ATTENDANCE_<?=$stu_id?>_HOURS')" >
	</div>
	<div class="col-md-2" style="flex: 9%;max-width: 9%;"  >
		<select id="PK_ATTENDANCE_CODE_<?=$stu_id?>" name="PK_ATTENDANCE_CODE[]" class="form-control required-entry" >
			<option selected></option>
			<? /* Ticket #1145  */
			$union = "";
			if($_REQUEST['PK_ATTENDANCE_CODE'] != '' && $_REQUEST['PK_ATTENDANCE_CODE'] > 0 && $_REQUEST['PK_STUDENT_ATTENDANCE'] > 0){
				$union = " UNION select PK_ATTENDANCE_CODE,CONCAT(CODE,' - ',ATTENDANCE_CODE) AS ATTENDANCE_CODE, CODE,M_ATTENDANCE_CODE.ACTIVE from M_ATTENDANCE_CODE WHERE PK_ATTENDANCE_CODE = '$_REQUEST[PK_ATTENDANCE_CODE]' ";
			}
			//Ticket # 670
			//$res_type = $db->Execute("SELECT * FROM (select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE, CONCAT(CODE,' - ',S_ATTENDANCE_CODE.DESCRIPTION) AS ATTENDANCE_CODE, CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $union) AS TEMP order by CODE ASC");

			
		
			$res_type = $db->Execute("SELECT * FROM (select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE, CONCAT(CODE,' - ',S_ATTENDANCE_CODE.DESCRIPTION) AS ATTENDANCE_CODE, CODE,M_ATTENDANCE_CODE.ACTIVE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE  AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $union) AS TEMP order by ACTIVE DESC,CODE ASC");

			while (!$res_type->EOF) {
				// Ticket # 670
				$disabled='';
				$ACTIVE 	= $res_type->fields['ACTIVE'];
				if ($ACTIVE == '0') {
				$Status = '(Inactive)';
				$disabled="disabled";
				} else {
				$Status = '';
				} ?>
				<option value="<?=$res_type->fields['PK_ATTENDANCE_CODE'] ?>" <? if($_REQUEST['PK_ATTENDANCE_CODE'] == $res_type->fields['PK_ATTENDANCE_CODE']) echo "selected"; ?> <?=$disabled?>><?=$res_type->fields['ATTENDANCE_CODE'].' '.$Status?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</div>
	<? /* Ticket # 1459 */
	if($ENABLE_ATTENDANCE_ACTIVITY_TYPES == 1){ ?>
		<div class="col-md-2" style="flex: 11%;max-width: 11%;" >
			<? if($_REQUEST['PK_STUDENT_ATTENDANCE'] == '' || $_REQUEST['PK_STUDENT_ATTENDANCE'] == 0){
				$res_def = $db->Execute("SELECT PK_ATTENDANCE_ACTIVITY_TYPE FROM S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
				$_REQUEST['PK_ATTENDANCE_ACTIVITY_TYPE'] = $res_def->fields['PK_ATTENDANCE_ACTIVITY_TYPE'];
			} ?>
			<select id="PK_ATTENDANCE_ACTIVITY_TYPE_<?=$stu_id?>" name="PK_ATTENDANCE_ACTIVITY_TYPE[]" class="form-control"  >
				<option selected></option>
				<? $res_type = $db->Execute("select PK_ATTENDANCE_ACTIVITY_TYPE, ATTENDANCE_ACTIVITY_TYPE from M_ATTENDANCE_ACTIVITY_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ATTENDANCE_ACTIVITY_TYPE ASC");
				while (!$res_type->EOF) { ?>
					<option value="<?=$res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE'] ?>" <? if($res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE'] == $_REQUEST['PK_ATTENDANCE_ACTIVITY_TYPE']) echo "selected"; ?> ><?=$res_type->fields['ATTENDANCE_ACTIVITY_TYPE']?></option>
				<?	$res_type->MoveNext();
				} ?>
			</select>
		</div>
	<? } 
	/* Ticket # 1459 */?>
	
	<? /* Ticket # 1601  */
	if($ENABLE_ATTENDANCE_COMMENTS == 1){ ?>
		<div class="col-md-2" style="flex: 12%;max-width: 12%;"  >
			<input type="text" id="ATTENDANCE_COMMENTS_<?=$stu_id?>"  name="ATTENDANCE_COMMENTS[]" class="form-control" value="<?=$_REQUEST['ATTENDANCE_COMMENTS']?>" />
		</div>
	<? } 
	/* Ticket # 1601  */?>
	
	<div class="col-md-1" style="flex: 4%;max-width: 4%;" >
		<a href="javascript:void(0);" id="del_link_<?=$stu_id?>" onclick="delete_row('<?=$stu_id?>','student')" title="<?=DELETE?>" ><i class="far fa-trash-alt help_size" style="font-size: 22px;" ></i> </a>
	</div>
</div>
