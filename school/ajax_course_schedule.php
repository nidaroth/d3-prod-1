<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course.php");

$PK_COURSE 					= $_REQUEST['PK_COURSE'];
$schedule_count				= $_REQUEST['schedule_count'];
$PK_CAMPUS_mul				= $_REQUEST['PK_CAMPUS_mul'];
$PK_COURSE_DEFAULT_SCHEDULE = $_REQUEST['PK_COURSE_DEFAULT_SCHEDULE'];

$res = $db->Execute("SELECT ENABLE_ETHINK, ENABLE_ATTENDANCE_ACTIVITY_TYPES FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
$ENABLE_ATTENDANCE_ACTIVITY_TYPES 	= $res->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'];	

if($PK_COURSE_DEFAULT_SCHEDULE == '') {
	$PK_SESSION = '';
	$SUNDAY	 	= '';
	$MONDAY 	= '';
	$TUESDAY 	= '';
	$WEDNESDAY 	= '';
	$THURSDAY 	= '';
	$FRIDAY 	= '';
	$SATURDAY 	= '';

	$SUN_ROOM	= '';
	$MON_ROOM 	= '';
	$TUE_ROOM 	= '';
	$WED_ROOM 	= '';
	$THU_ROOM 	= '';
	$FRI_ROOM 	= '';
	$SAT_ROOM 	= '';
	
	$SUN_START_TIME = '';
	$SUN_END_TIME 	= '';
	$SUN_HOURS 		= '';
	
	$MON_START_TIME = '';
	$MON_END_TIME 	= '';
	$MON_HOURS 		= '';
	
	$TUE_START_TIME = '';
	$TUE_END_TIME 	= '';
	$TUE_HOURS 		= '';
	
	$WED_START_TIME = '';
	$WED_END_TIME 	= '';
	$WED_HOURS 		= '';
	
	$THU_START_TIME = '';
	$THU_END_TIME 	= '';
	$THU_HOURS 		= '';
	
	$FRI_START_TIME = '';
	$FRI_END_TIME 	= '';
	$FRI_HOURS 		= '';
	
	$SAT_START_TIME = '';
	$SAT_END_TIME 	= '';
	$SAT_HOURS 		= '';
	
	$SUN_PK_ATTENDANCE_ACTIVITY_TYPE = '';
	$MON_PK_ATTENDANCE_ACTIVITY_TYPE = '';
	$TUE_PK_ATTENDANCE_ACTIVITY_TYPE = '';
	$WED_PK_ATTENDANCE_ACTIVITY_TYPE = '';
	$THU_PK_ATTENDANCE_ACTIVITY_TYPE = '';
	$FRI_PK_ATTENDANCE_ACTIVITY_TYPE = '';
	$SAT_PK_ATTENDANCE_ACTIVITY_TYPE = '';
} else {

	$res_c_sch = $db->Execute("SELECT * FROM S_COURSE_DEFAULT_SCHEDULE WHERE PK_COURSE_DEFAULT_SCHEDULE = '$PK_COURSE_DEFAULT_SCHEDULE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");  
	$DEF_SCH_PK_SESSION = $res_c_sch->fields['PK_SESSION']; 
	$SUNDAY	 	= $res_c_sch->fields['SUNDAY'];
	$MONDAY 	= $res_c_sch->fields['MONDAY'];
	$TUESDAY 	= $res_c_sch->fields['TUESDAY'];
	$WEDNESDAY 	= $res_c_sch->fields['WEDNESDAY'];
	$THURSDAY 	= $res_c_sch->fields['THURSDAY'];
	$FRIDAY 	= $res_c_sch->fields['FRIDAY'];
	$SATURDAY 	= $res_c_sch->fields['SATURDAY'];

	$SUN_ROOM	= $res_c_sch->fields['SUN_ROOM'];
	$MON_ROOM 	= $res_c_sch->fields['MON_ROOM'];
	$TUE_ROOM 	= $res_c_sch->fields['TUE_ROOM'];
	$WED_ROOM 	= $res_c_sch->fields['WED_ROOM'];
	$THU_ROOM 	= $res_c_sch->fields['THU_ROOM'];
	$FRI_ROOM 	= $res_c_sch->fields['FRI_ROOM'];
	$SAT_ROOM 	= $res_c_sch->fields['SAT_ROOM'];
	
	$SUN_PK_ATTENDANCE_ACTIVITY_TYPE = $res_c_sch->fields['SUN_PK_ATTENDANCE_ACTIVITY_TYPE'];
	$MON_PK_ATTENDANCE_ACTIVITY_TYPE = $res_c_sch->fields['MON_PK_ATTENDANCE_ACTIVITY_TYPE'];
	$TUE_PK_ATTENDANCE_ACTIVITY_TYPE = $res_c_sch->fields['TUE_PK_ATTENDANCE_ACTIVITY_TYPE'];
	$WED_PK_ATTENDANCE_ACTIVITY_TYPE = $res_c_sch->fields['WED_PK_ATTENDANCE_ACTIVITY_TYPE'];
	$THU_PK_ATTENDANCE_ACTIVITY_TYPE = $res_c_sch->fields['THU_PK_ATTENDANCE_ACTIVITY_TYPE'];
	$FRI_PK_ATTENDANCE_ACTIVITY_TYPE = $res_c_sch->fields['FRI_PK_ATTENDANCE_ACTIVITY_TYPE'];
	$SAT_PK_ATTENDANCE_ACTIVITY_TYPE = $res_c_sch->fields['SAT_PK_ATTENDANCE_ACTIVITY_TYPE'];

	if($SUNDAY == 1) {
		$SUN_START_TIME = $res_c_sch->fields['SUN_START_TIME'];
		$SUN_END_TIME 	= $res_c_sch->fields['SUN_END_TIME'];
		$SUN_HOURS 		= $res_c_sch->fields['SUN_HOURS'];
		
		if($SUN_START_TIME != '00:00:00')
			$SUN_START_TIME = date("h:i A",strtotime($SUN_START_TIME));
			
		if($SUN_END_TIME != '00:00:00')
			$SUN_END_TIME = date("h:i A",strtotime($SUN_END_TIME));
	} else {
		$SUN_START_TIME = '';
		$SUN_END_TIME 	= '';
		$SUN_HOURS 		= '';
	}

	if($MONDAY == 1) {
		$MON_START_TIME = $res_c_sch->fields['MON_START_TIME'];
		$MON_END_TIME 	= $res_c_sch->fields['MON_END_TIME'];
		$MON_HOURS 		= $res_c_sch->fields['MON_HOURS'];
		
		if($MON_START_TIME != '00:00:00')
			$MON_START_TIME = date("h:i A",strtotime($MON_START_TIME));
			
		if($MON_END_TIME != '00:00:00')
			$MON_END_TIME = date("h:i A",strtotime($MON_END_TIME));
	} else {
		$MON_START_TIME = '';
		$MON_END_TIME 	= '';
		$MON_HOURS 		= '';
	}

	if($TUESDAY == 1) {
		$TUE_START_TIME = $res_c_sch->fields['TUE_START_TIME'];
		$TUE_END_TIME 	= $res_c_sch->fields['TUE_END_TIME'];
		$TUE_HOURS 		= $res_c_sch->fields['TUE_HOURS'];
		
		if($TUE_START_TIME != '00:00:00')
			$TUE_START_TIME = date("h:i A",strtotime($TUE_START_TIME));
			
		if($TUE_END_TIME != '00:00:00')
			$TUE_END_TIME = date("h:i A",strtotime($TUE_END_TIME));
	} else {
		$TUE_START_TIME = '';
		$TUE_END_TIME 	= '';
		$TUE_HOURS 		= '';
	}

	if($WEDNESDAY == 1) {
		$WED_START_TIME = $res_c_sch->fields['WED_START_TIME'];
		$WED_END_TIME 	= $res_c_sch->fields['WED_END_TIME'];
		$WED_HOURS 		= $res_c_sch->fields['WED_HOURS'];
		
		if($WED_START_TIME != '00:00:00')
			$WED_START_TIME = date("h:i A",strtotime($WED_START_TIME));
			
		if($WED_END_TIME != '00:00:00')
			$WED_END_TIME = date("h:i A",strtotime($WED_END_TIME));
	} else {
		$WED_START_TIME = '';
		$WED_END_TIME 	= '';
		$WED_HOURS 		= '';
	}

	if($THURSDAY == 1) {
		$THU_START_TIME = $res_c_sch->fields['THU_START_TIME'];
		$THU_END_TIME 	= $res_c_sch->fields['THU_END_TIME'];
		$THU_HOURS 		= $res_c_sch->fields['THU_HOURS'];
		
		if($THU_START_TIME != '00:00:00')
			$THU_START_TIME = date("h:i A",strtotime($THU_START_TIME));
			
		if($THU_END_TIME != '00:00:00')
			$THU_END_TIME = date("h:i A",strtotime($THU_END_TIME));
	} else {
		$THU_START_TIME = '';
		$THU_END_TIME 	= '';
		$THU_HOURS 		= '';
	}

	if($FRIDAY == 1) {
		$FRI_START_TIME = $res_c_sch->fields['FRI_START_TIME'];
		$FRI_END_TIME 	= $res_c_sch->fields['FRI_END_TIME'];
		$FRI_HOURS 		= $res_c_sch->fields['FRI_HOURS'];
		
		if($FRI_START_TIME != '00:00:00')
			$FRI_START_TIME = date("h:i A",strtotime($FRI_START_TIME));
			
		if($FRI_END_TIME != '00:00:00')
			$FRI_END_TIME = date("h:i A",strtotime($FRI_END_TIME));
	} else {
		$FRI_START_TIME = '';
		$FRI_END_TIME 	= '';
		$FRI_HOURS 		= '';
	}

	if($SATURDAY == 1) {
		$SAT_START_TIME = $res_c_sch->fields['SAT_START_TIME'];
		$SAT_END_TIME 	= $res_c_sch->fields['SAT_END_TIME'];
		$SAT_HOURS 		= $res_c_sch->fields['SAT_HOURS'];
		
		if($SAT_START_TIME != '00:00:00')
			$SAT_START_TIME = date("h:i A",strtotime($SAT_START_TIME));
			
		if($SAT_END_TIME != '00:00:00')
			$SAT_END_TIME = date("h:i A",strtotime($SAT_END_TIME));
	} else {
		$SAT_START_TIME = '';
		$SAT_END_TIME 	= '';
		$SAT_HOURS 		= '';
	} 
} ?>
<div id="default_schedule_div_<?=$schedule_count?>" >
	<div class="row">
		<div class="col-md-7">
			<div class="row">
				<div class="col-md-4 form-group">
					<select id="DEF_SCH_PK_SESSION_<?=$schedule_count?>" name="DEF_SCH_PK_SESSION_<?=$schedule_count?>" class="form-control required-entry sef_sch_session" onchange="change_dup_session(this.value,<?=$schedule_count?>)" >
						<option value="" >Select Session</option>
						<? /* Ticket # 1696 */
						$res_type = $db->Execute("select PK_SESSION, SESSION, ACTIVE from M_SESSION WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, SESSION ASC");
						while (!$res_type->EOF) { 
							$option_label = substr($res_type->fields['SESSION'],0,1).'-'.$res_type->fields['SESSION'];
							if($res_type->fields['ACTIVE'] == 0)
								$option_label .= " (Inactive)"; ?>
							<option value="<?=$res_type->fields['PK_SESSION'] ?>" <? if($DEF_SCH_PK_SESSION == $res_type->fields['PK_SESSION']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
						<?	$res_type->MoveNext();
						} /* Ticket # 1696 */ ?>
					</select>
					<input type="hidden" name="schedule_count[]" value="<?=$schedule_count?>" >
				</div>
				<div class="col-md-4 form-group">
					<button onclick="delete_row(<?=$schedule_count?>,'DEF_SCHEDULE')" type="button" class="btn waves-effect waves-light btn-info"><?=DELETE?></button>
				</div>
			</div>
			<div class="row">
				<div class="col-md-2 form-group">
					<b style="font-weight:bold" ><?=DAY?></b>
				</div>
				<div class="col-md-2 form-group" >
					<b style="font-weight:bold" ><?=START_TIME?></b>
				</div>
				<div class="col-md-2 form-group" >
					<b style="font-weight:bold" ><?=END_TIME?></b>
				</div>
				<div class="col-md-2 form-group" >
					<b style="font-weight:bold" ><?=HOUR?></b>
				</div>
				<div class="col-md-2 form-group" >
					<b style="font-weight:bold" ><?=ROOM?></b>
				</div>
				<? if($ENABLE_ATTENDANCE_ACTIVITY_TYPES == 1){ ?>
				<div class="col-md-2 form-group" >
					<b style="font-weight:bold" ><?=ACTIVITY_TYPE?></b>
				</div>
				<? } ?>
			</div>
			
			<div class="row">
				<div class="col-md-2 form-group custom-control custom-checkbox form-group">
					<input type="checkbox" class="custom-control-input" id="SUN_<?=$schedule_count?>" name="SUNDAY_<?=$schedule_count?>" value="1" <? if($SUNDAY == 1) echo "checked"; ?> onclick="enable_time('SUN_<?=$schedule_count?>')" >
					<label class="custom-control-label" for="SUN_<?=$schedule_count?>"><?=SUNDAY?></label>
				</div>
				
				<? if($SUNDAY == 1) $disable=""; else  $disable="disabled"; ?>
				<div class="col-2 form-group"  >
					<input type="text" id="SUN_<?=$schedule_count?>_START_TIME" name="SUN_<?=$schedule_count?>_START_TIME" value="<?=$SUN_START_TIME?>" class="form-control timepicker padding_2" onchange="get_hour('SUN_<?=$schedule_count?>')" <?=$disable ?> >
					<span class="bar"></span> 
				</div>
				<div class="col-2 form-group"  >
					<input type="text" id="SUN_<?=$schedule_count?>_END_TIME" name="SUN_<?=$schedule_count?>_END_TIME" value="<?=$SUN_END_TIME?>" class="form-control timepicker padding_2" onchange="get_hour('SUN_<?=$schedule_count?>')" <?=$disable ?> >
					<span class="bar"></span> 
				</div>
				<div class="col-2 form-group"  >
					<input type="text" id="SUN_<?=$schedule_count?>_HOURS" name="SUN_<?=$schedule_count?>_HOURS" value="<?=$SUN_HOURS?>" class="form-control padding_2" <?=$disable ?> >
					<span class="bar"></span> 
				</div>
				<div class="col-md-2 form-group"  >
					<? $_REQUEST['campus'] 		 = $PK_CAMPUS_mul;
					$_REQUEST['name'] 		 	 = 'SUN_'.$schedule_count.'_ROOM';
					$_REQUEST['id'] 		 	 = 'SUN_'.$schedule_count.'_ROOM';
					$_REQUEST['SELECTED_VALUE1'] = $SUN_ROOM;
					$_REQUEST['disable'] 		 = $disable;
					include("ajax_get_campus_room.php"); ?>
				</div>
				<? if($ENABLE_ATTENDANCE_ACTIVITY_TYPES == 1){ ?>
				<div class="col-md-2 form-group"  >
					<select id="SUN_<?=$schedule_count?>_PK_ATTENDANCE_ACTIVITY_TYPE" name="SUN_<?=$schedule_count?>_PK_ATTENDANCE_ACTIVITY_TYPE" class="form-control" <?=$disable ?> >
						<option value="" ></option>
						<? /* Ticket #1696  */
						$res_type = $db->Execute("select PK_ATTENDANCE_ACTIVITY_TYPE, ATTENDANCE_ACTIVITY_TYPE, ACTIVE from M_ATTENDANCE_ACTIVITY_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, ATTENDANCE_ACTIVITY_TYPE ASC");
						while (!$res_type->EOF) { 
							$option_label = $res_type->fields['ATTENDANCE_ACTIVITY_TYPE'];
							if($res_type->fields['ACTIVE'] == 0)
								$option_label .= " (Inactive)"; ?>
							<option value="<?=$res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE'] ?>" <? if($SUN_PK_ATTENDANCE_ACTIVITY_TYPE == $res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
						<?	$res_type->MoveNext();
						} /* Ticket #1696  */ ?>
					</select>
				</div>
				<? } ?>
			</div>
			
			<div class="row">
				<? if($MONDAY == 1) $disable=""; else  $disable="disabled"; ?>
				<div class="col-md-2 form-group custom-control custom-checkbox form-group">
					<input type="checkbox" class="custom-control-input" id="MON_<?=$schedule_count?>" name="MONDAY_<?=$schedule_count?>" value="1" <? if($MONDAY == 1) echo "checked"; ?> onclick="enable_time('MON_<?=$schedule_count?>')" >
					<label class="custom-control-label" for="MON_<?=$schedule_count?>"><?=MONDAY?></label>
				</div>
				
				<div class="col-2 form-group" >
					<input type="text" id="MON_<?=$schedule_count?>_START_TIME" name="MON_<?=$schedule_count?>_START_TIME" value="<?=$MON_START_TIME?>" class="form-control timepicker padding_2" onchange="get_hour('MON_<?=$schedule_count?>')" <?=$disable ?>  >
					<span class="bar"></span> 
				</div>
				<div class="col-2 form-group"  >
					<input type="text" id="MON_<?=$schedule_count?>_END_TIME" name="MON_<?=$schedule_count?>_END_TIME" value="<?=$MON_END_TIME?>" class="form-control timepicker padding_2" onchange="get_hour('MON_<?=$schedule_count?>')" <?=$disable ?>  >
					<span class="bar"></span> 
				</div>
				<div class="col-2 form-group"  >
					<input type="text" id="MON_<?=$schedule_count?>_HOURS" name="MON_<?=$schedule_count?>_HOURS" value="<?=$MON_HOURS?>" class="form-control padding_2" <?=$disable ?> >
					<span class="bar"></span> 
				</div>
				<div class="col-md-2 form-group"  >
					<? $_REQUEST['campus'] 		 = $PK_CAMPUS_mul;
					$_REQUEST['name'] 		 	 = 'MON_'.$schedule_count.'_ROOM';
					$_REQUEST['id'] 		 	 = 'MON_'.$schedule_count.'_ROOM';
					$_REQUEST['SELECTED_VALUE1'] = $MON_ROOM;
					$_REQUEST['disable'] 		 = $disable;
					include("ajax_get_campus_room.php"); ?>
				</div>
				<? if($ENABLE_ATTENDANCE_ACTIVITY_TYPES == 1){ ?>
				<div class="col-md-2 form-group"  >
					<select id="MON_<?=$schedule_count?>_PK_ATTENDANCE_ACTIVITY_TYPE" name="MON_<?=$schedule_count?>_PK_ATTENDANCE_ACTIVITY_TYPE" class="form-control" <?=$disable ?> >
						<option value="" ></option>
						<? /* Ticket #1696  */
						$res_type = $db->Execute("select PK_ATTENDANCE_ACTIVITY_TYPE, ATTENDANCE_ACTIVITY_TYPE, ACTIVE from M_ATTENDANCE_ACTIVITY_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, ATTENDANCE_ACTIVITY_TYPE ASC");
						while (!$res_type->EOF) { 
							$option_label = $res_type->fields['ATTENDANCE_ACTIVITY_TYPE'];
							if($res_type->fields['ACTIVE'] == 0)
								$option_label .= " (Inactive)"; ?>
							<option value="<?=$res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE'] ?>" <? if($MON_PK_ATTENDANCE_ACTIVITY_TYPE == $res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
						<?	$res_type->MoveNext();
						} /* Ticket #1696  */ ?>
					</select>
				</div>
				<? } ?>
			</div>
			
			<div class="row">
				<? if($TUESDAY == 1) $disable=""; else  $disable="disabled"; ?>
				<div class="col-md-2 form-group custom-control custom-checkbox form-group">
					<input type="checkbox" class="custom-control-input" id="TUE_<?=$schedule_count?>" name="TUESDAY_<?=$schedule_count?>" value="1" <? if($TUESDAY == 1) echo "checked"; ?> onclick="enable_time('TUE_<?=$schedule_count?>')" >
					<label class="custom-control-label" for="TUE_<?=$schedule_count?>"><?=TUESDAY?></label>
				</div>
				
				<div class="col-2 form-group"  >
					<input type="text" id="TUE_<?=$schedule_count?>_START_TIME" name="TUE_<?=$schedule_count?>_START_TIME" value="<?=$TUE_START_TIME?>" class="form-control timepicker padding_2" onchange="get_hour('TUE_<?=$schedule_count?>')" <?=$disable ?>  >
					<span class="bar"></span> 
				</div>
				<div class="col-2 form-group"  >
					<input type="text" id="TUE_<?=$schedule_count?>_END_TIME" name="TUE_<?=$schedule_count?>_END_TIME" value="<?=$TUE_END_TIME?>" class="form-control timepicker padding_2" onchange="get_hour('TUE_<?=$schedule_count?>')" <?=$disable ?>  >
					<span class="bar"></span> 
				</div>
				<div class="col-2 form-group"  >
					<input type="text" id="TUE_<?=$schedule_count?>_HOURS" name="TUE_<?=$schedule_count?>_HOURS" value="<?=$TUE_HOURS?>" class="form-control padding_2" <?=$disable ?> >
					<span class="bar"></span> 
				</div>
				<div class="col-md-2 form-group"  >
					<? $_REQUEST['campus'] 		 = $PK_CAMPUS_mul;
					$_REQUEST['name'] 		 	 = 'TUE_'.$schedule_count.'_ROOM';
					$_REQUEST['id'] 		 	 = 'TUE_'.$schedule_count.'_ROOM';
					$_REQUEST['SELECTED_VALUE1'] = $TUE_ROOM;
					$_REQUEST['disable'] 		 = $disable;
					include("ajax_get_campus_room.php"); ?>
				</div>
				<? if($ENABLE_ATTENDANCE_ACTIVITY_TYPES == 1){ ?>
				<div class="col-md-2 form-group"  >
					<select id="TUE_<?=$schedule_count?>_PK_ATTENDANCE_ACTIVITY_TYPE" name="TUE_<?=$schedule_count?>_PK_ATTENDANCE_ACTIVITY_TYPE" class="form-control" <?=$disable ?> >
						<option value="" ></option>
						<? /* Ticket #1696  */
						$res_type = $db->Execute("select PK_ATTENDANCE_ACTIVITY_TYPE, ATTENDANCE_ACTIVITY_TYPE, ACTIVE from M_ATTENDANCE_ACTIVITY_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, ATTENDANCE_ACTIVITY_TYPE ASC");
						while (!$res_type->EOF) { 
							$option_label = $res_type->fields['ATTENDANCE_ACTIVITY_TYPE'];
							if($res_type->fields['ACTIVE'] == 0)
								$option_label .= " (Inactive)"; ?>
							<option value="<?=$res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE'] ?>" <? if($TUE_PK_ATTENDANCE_ACTIVITY_TYPE == $res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
						<?	$res_type->MoveNext();
						} /* Ticket #1696  */ ?>=
					</select>
				</div>
				<? } ?>
			</div>
			
			<div class="row">
				<? if($WEDNESDAY == 1) $disable=""; else  $disable="disabled"; ?>
				<div class="col-md-2 form-group custom-control custom-checkbox form-group">
					<input type="checkbox" class="custom-control-input" id="WED_<?=$schedule_count?>" name="WEDNESDAY_<?=$schedule_count?>" value="1" <? if($WEDNESDAY == 1) echo "checked"; ?> onclick="enable_time('WED_<?=$schedule_count?>')" >
					<label class="custom-control-label" for="WED_<?=$schedule_count?>"><?=WEDNESDAY?></label>
				</div>
				
				<div class="col-2 form-group"  >
					<input type="text" id="WED_<?=$schedule_count?>_START_TIME" name="WED_<?=$schedule_count?>_START_TIME" value="<?=$WED_START_TIME?>" class="form-control timepicker padding_2" onchange="get_hour('WED_<?=$schedule_count?>')" <?=$disable ?>  >
					<span class="bar"></span> 
				</div>
				<div class="col-2 form-group"  >
					<input type="text" id="WED_<?=$schedule_count?>_END_TIME" name="WED_<?=$schedule_count?>_END_TIME" value="<?=$WED_END_TIME?>" class="form-control timepicker padding_2" onchange="get_hour('WED_<?=$schedule_count?>')" <?=$disable ?>  >
					<span class="bar"></span> 
				</div>
				<div class="col-2 form-group"  >
					<input type="text" id="WED_<?=$schedule_count?>_HOURS" name="WED_<?=$schedule_count?>_HOURS" value="<?=$WED_HOURS?>" class="form-control padding_2" <?=$disable ?> >
					<span class="bar"></span> 
				</div>
				<div class="col-md-2 form-group"  >
					<? $_REQUEST['campus'] 		 = $PK_CAMPUS_mul;
					$_REQUEST['name'] 		 	 = 'WED_'.$schedule_count.'_ROOM';
					$_REQUEST['id'] 		 	 = 'WED_'.$schedule_count.'_ROOM';
					$_REQUEST['SELECTED_VALUE1'] = $WED_ROOM;
					$_REQUEST['disable'] 		 = $disable;
					include("ajax_get_campus_room.php"); ?>
				</div>
				<? if($ENABLE_ATTENDANCE_ACTIVITY_TYPES == 1){ ?>
				<div class="col-md-2 form-group"  >
					<select id="WED_<?=$schedule_count?>_PK_ATTENDANCE_ACTIVITY_TYPE" name="WED_<?=$schedule_count?>_PK_ATTENDANCE_ACTIVITY_TYPE" class="form-control" <?=$disable ?> >
						<option value="" ></option>
						<? /* Ticket #1696  */
						$res_type = $db->Execute("select PK_ATTENDANCE_ACTIVITY_TYPE, ATTENDANCE_ACTIVITY_TYPE, ACTIVE from M_ATTENDANCE_ACTIVITY_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, ATTENDANCE_ACTIVITY_TYPE ASC");
						while (!$res_type->EOF) { 
							$option_label = $res_type->fields['ATTENDANCE_ACTIVITY_TYPE'];
							if($res_type->fields['ACTIVE'] == 0)
								$option_label .= " (Inactive)"; ?>
							<option value="<?=$res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE'] ?>" <? if($WED_PK_ATTENDANCE_ACTIVITY_TYPE == $res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
						<?	$res_type->MoveNext();
						} /* Ticket #1696  */ ?>
					</select>
				</div>
				<? } ?>
			</div>
			
			<div class="row">
				<? if($THURSDAY == 1) $disable=""; else  $disable="disabled"; ?>
				<div class="col-md-2 form-group custom-control custom-checkbox form-group">
					<input type="checkbox" class="custom-control-input" id="THU_<?=$schedule_count?>" name="THURSDAY_<?=$schedule_count?>" value="1" <? if($THURSDAY == 1) echo "checked"; ?> onclick="enable_time('THU_<?=$schedule_count?>')" >
					<label class="custom-control-label" for="THU_<?=$schedule_count?>"><?=THURSDAY?></label>
				</div>
				
				<div class="col-2 form-group"  >
					<input type="text" id="THU_<?=$schedule_count?>_START_TIME" name="THU_<?=$schedule_count?>_START_TIME" value="<?=$THU_START_TIME?>" class="form-control timepicker padding_2" onchange="get_hour('THU_<?=$schedule_count?>')" <?=$disable ?>  >
					<span class="bar"></span> 
				</div>
				<div class="col-2 form-group"  >
					<input type="text" id="THU_<?=$schedule_count?>_END_TIME" name="THU_<?=$schedule_count?>_END_TIME" value="<?=$THU_END_TIME?>" class="form-control timepicker padding_2" onchange="get_hour('THU_<?=$schedule_count?>')" <?=$disable ?>  >
					<span class="bar"></span> 
				</div>
				<div class="col-2 form-group"  >
					<input type="text" id="THU_<?=$schedule_count?>_HOURS" name="THU_<?=$schedule_count?>_HOURS" value="<?=$THU_HOURS?>" class="form-control padding_2" <?=$disable ?> >
					<span class="bar"></span> 
				</div>
				<div class="col-md-2 form-group"  >
					<? $_REQUEST['campus'] 		 = $PK_CAMPUS_mul;
					$_REQUEST['name'] 		 	 = 'THU_'.$schedule_count.'_ROOM';
					$_REQUEST['id'] 		 	 = 'THU_'.$schedule_count.'_ROOM';
					$_REQUEST['SELECTED_VALUE1'] = $THU_ROOM;
					$_REQUEST['disable'] 		 = $disable;
					include("ajax_get_campus_room.php"); ?>
				</div>
				<? if($ENABLE_ATTENDANCE_ACTIVITY_TYPES == 1){ ?>
				<div class="col-md-2 form-group"  >
					<select id="THU_<?=$schedule_count?>_PK_ATTENDANCE_ACTIVITY_TYPE" name="THU_<?=$schedule_count?>_PK_ATTENDANCE_ACTIVITY_TYPE" class="form-control" <?=$disable ?> >
						<option value="" ></option>
						<? /* Ticket #1696  */
						$res_type = $db->Execute("select PK_ATTENDANCE_ACTIVITY_TYPE, ATTENDANCE_ACTIVITY_TYPE, ACTIVE from M_ATTENDANCE_ACTIVITY_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, ATTENDANCE_ACTIVITY_TYPE ASC");
						while (!$res_type->EOF) { 
							$option_label = $res_type->fields['ATTENDANCE_ACTIVITY_TYPE'];
							if($res_type->fields['ACTIVE'] == 0)
								$option_label .= " (Inactive)"; ?>
							<option value="<?=$res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE'] ?>" <? if($THU_PK_ATTENDANCE_ACTIVITY_TYPE == $res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
						<?	$res_type->MoveNext();
						} /* Ticket #1696  */ ?>
					</select>
				</div>
				<? } ?>
			</div>
			
			<div class="row">
				<? if($FRIDAY == 1) $disable=""; else  $disable="disabled"; ?>
				<div class="col-md-2 form-group custom-control custom-checkbox form-group">
					<input type="checkbox" class="custom-control-input" id="FRI_<?=$schedule_count?>" name="FRIDAY_<?=$schedule_count?>" value="1" <? if($FRIDAY == 1) echo "checked"; ?> onclick="enable_time('FRI_<?=$schedule_count?>')" >
					<label class="custom-control-label" for="FRI_<?=$schedule_count?>"><?=FRIDAY?></label>
				</div>
				
				<div class="col-2 form-group"  >
					<input type="text" id="FRI_<?=$schedule_count?>_START_TIME" name="FRI_<?=$schedule_count?>_START_TIME" value="<?=$FRI_START_TIME?>" class="form-control timepicker padding_2" onchange="get_hour('FRI_<?=$schedule_count?>')" <?=$disable ?>  >
					<span class="bar"></span> 
				</div>
				<div class="col-2 form-group"  >
					<input type="text" id="FRI_<?=$schedule_count?>_END_TIME" name="FRI_<?=$schedule_count?>_END_TIME" value="<?=$FRI_END_TIME?>" class="form-control timepicker padding_2" onchange="get_hour('FRI_<?=$schedule_count?>')" <?=$disable ?>  >
					<span class="bar"></span> 
				</div>
				<div class="col-2 form-group"  >
					<input type="text" id="FRI_<?=$schedule_count?>_HOURS" name="FRI_<?=$schedule_count?>_HOURS" value="<?=$FRI_HOURS?>" class="form-control padding_2" <?=$disable ?> >
					<span class="bar"></span> 
				</div>
				<div class="col-md-2 form-group"  >
					<? $_REQUEST['campus'] 		 = $PK_CAMPUS_mul;
					$_REQUEST['name'] 		 	 = 'FRI_'.$schedule_count.'_ROOM';
					$_REQUEST['id'] 		 	 = 'FRI_'.$schedule_count.'_ROOM';
					$_REQUEST['SELECTED_VALUE1'] = $FRI_ROOM;
					$_REQUEST['disable'] 		 = $disable;
					include("ajax_get_campus_room.php"); ?>
				</div>	
				<? if($ENABLE_ATTENDANCE_ACTIVITY_TYPES == 1){ ?>
				<div class="col-md-2 form-group"  >
					<select id="FRI_<?=$schedule_count?>_PK_ATTENDANCE_ACTIVITY_TYPE" name="FRI_<?=$schedule_count?>_PK_ATTENDANCE_ACTIVITY_TYPE" class="form-control" <?=$disable ?> >
						<option value="" ></option>
							<? /* Ticket #1696  */
						$res_type = $db->Execute("select PK_ATTENDANCE_ACTIVITY_TYPE, ATTENDANCE_ACTIVITY_TYPE, ACTIVE from M_ATTENDANCE_ACTIVITY_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, ATTENDANCE_ACTIVITY_TYPE ASC");
						while (!$res_type->EOF) { 
							$option_label = $res_type->fields['ATTENDANCE_ACTIVITY_TYPE'];
							if($res_type->fields['ACTIVE'] == 0)
								$option_label .= " (Inactive)"; ?>
							<option value="<?=$res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE'] ?>" <? if($FRI_PK_ATTENDANCE_ACTIVITY_TYPE == $res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
						<?	$res_type->MoveNext();
						} /* Ticket #1696  */ ?>
					</select>
				</div>
				<? } ?>
			</div>
			
			<div class="row">
				<? if($SATURDAY == 1) $disable=""; else  $disable="disabled"; ?>
				<div class="col-md-2 form-group custom-control custom-checkbox form-group">
					<input type="checkbox" class="custom-control-input" id="SAT_<?=$schedule_count?>" name="SATURDAY_<?=$schedule_count?>" value="1" <? if($SATURDAY == 1) echo "checked"; ?> onclick="enable_time('SAT_<?=$schedule_count?>')" >
					<label class="custom-control-label" for="SAT_<?=$schedule_count?>"><?=SATURDAY?></label>
				</div>
				
				<div class="col-2 form-group"  >
					<input type="text" id="SAT_<?=$schedule_count?>_START_TIME" name="SAT_<?=$schedule_count?>_START_TIME" value="<?=$SAT_START_TIME?>" class="form-control timepicker padding_2" onchange="get_hour('SAT_<?=$schedule_count?>')" <?=$disable ?>  >
					<span class="bar"></span> 
				</div>
				<div class="col-2 form-group"  >
					<input type="text" id="SAT_<?=$schedule_count?>_END_TIME" name="SAT_<?=$schedule_count?>_END_TIME" value="<?=$SAT_END_TIME?>" class="form-control timepicker padding_2" onchange="get_hour('SAT_<?=$schedule_count?>')" <?=$disable ?>  >
					<span class="bar"></span> 
				</div>
				<div class="col-2 form-group"  >
					<input type="text" id="SAT_<?=$schedule_count?>_HOURS" name="SAT_<?=$schedule_count?>_HOURS" value="<?=$SAT_HOURS?>" class="form-control padding_2" <?=$disable ?> >
					<span class="bar"></span> 
				</div>
				<div class="col-md-2 form-group"  >
					<? $_REQUEST['campus'] 		 = $PK_CAMPUS_mul;
					$_REQUEST['name'] 		 	 = 'SAT_'.$schedule_count.'_ROOM';
					$_REQUEST['id'] 		 	 = 'SAT_'.$schedule_count.'_ROOM';
					$_REQUEST['SELECTED_VALUE1'] = $SAT_ROOM;
					$_REQUEST['disable'] 		 = $disable;
					include("ajax_get_campus_room.php"); ?>
				</div>
				<? if($ENABLE_ATTENDANCE_ACTIVITY_TYPES == 1){ ?>
				<div class="col-md-2 form-group"  >
					<select id="SAT_<?=$schedule_count?>_PK_ATTENDANCE_ACTIVITY_TYPE" name="SAT_<?=$schedule_count?>_PK_ATTENDANCE_ACTIVITY_TYPE" class="form-control" <?=$disable ?> >
						<option value="" ></option>
						<? /* Ticket #1696  */
						$res_type = $db->Execute("select PK_ATTENDANCE_ACTIVITY_TYPE, ATTENDANCE_ACTIVITY_TYPE, ACTIVE from M_ATTENDANCE_ACTIVITY_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, ATTENDANCE_ACTIVITY_TYPE ASC");
						while (!$res_type->EOF) { 
							$option_label = $res_type->fields['ATTENDANCE_ACTIVITY_TYPE'];
							if($res_type->fields['ACTIVE'] == 0)
								$option_label .= " (Inactive)"; ?>
							<option value="<?=$res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE'] ?>" <? if($SAT_PK_ATTENDANCE_ACTIVITY_TYPE == $res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
						<?	$res_type->MoveNext();
						} /* Ticket #1696  */ ?>
					</select>
				</div>
				<? } ?>
			</div>
		</div>
	</div>
</div>