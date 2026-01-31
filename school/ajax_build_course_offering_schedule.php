<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

function displayDates($date1, $date2, $format, $SCHEDULE_ON_HOLIDAY, $PK_SESSION ) {
	global $db;
	
	$dates = array();
	$current = strtotime($date1);
	$date2 	 = strtotime($date2);
	$stepVal = '+1 day';
	while( $current <= $date2 ) {
	
		$temp_date = date($format, $current);
		if($SCHEDULE_ON_HOLIDAY == 1)
			$dates[] = $temp_date;
		else {
			$temp_date1 = date("Y-m-d",strtotime($temp_date));
			$res_type = $db->Execute("select PK_ACADEMIC_CALENDAR_SESSION from M_ACADEMIC_CALENDAR_SESSION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_SESSION = '$PK_SESSION' AND ACADEMY_DATE = '$temp_date1'; "); 
			
			if($res_type->RecordCount() == 0)
				$dates[] = $temp_date;
		}
		
		$current = strtotime($stepVal, $current);
	}
	return $dates;
}

if($_REQUEST['BUILD'] == 1) {
	$START_DATE = $_REQUEST['sd'];
	$END_DATE 	= $_REQUEST['ed'];

	$PK_CAMPUS = $_REQUEST['PK_CAMPUS'];

	if($START_DATE != '')
		$START_DATE = date("Y-m-d",strtotime($START_DATE));

	if($END_DATE != '')
		$END_DATE = date("Y-m-d",strtotime($END_DATE));
		
	$res_type = $db->Execute("select HOURS from S_COURSE WHERE PK_COURSE = '$_REQUEST[PK_COURSE]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$COURSE_HOUR 	= $res_type->fields['HOURS'];
	$TOTAL_HOUR 	= 0;
	
	$HAS_END_DATE = 1;
	if($END_DATE == ''){
		$HAS_END_DATE 	= 0;
		$END_DATE 		= date('Y-m-d', strtotime($START_DATE. ' + 5 years'));
	}

	
	if($START_DATE != '' && $END_DATE != '') {
		$SCHEDULE_DATES = displayDates($START_DATE, $END_DATE,'m/d/Y', $_REQUEST['SCHEDULE_ON_HOLIDAY'], $_REQUEST['PK_SESSION']);
		
		$sun	= $_REQUEST['sun'];
		$mon 	= $_REQUEST['mon'];
		$tue 	= $_REQUEST['tue'];
		$wed 	= $_REQUEST['wed'];
		$thu	= $_REQUEST['thu'];
		$fri 	= $_REQUEST['fri'];
		$sat 	= $_REQUEST['sat'];
		
		$i = 0;
		foreach($SCHEDULE_DATES as $SCHEDULE_DATE){ 
			$day = date("N",strtotime($SCHEDULE_DATE)); 
			$i++;
			
			if(($day == 1 && $mon == 1) || ($day == 2 && $tue == 1) || ($day == 3 && $wed == 1) || ($day == 4 && $thu == 1) || ($day == 5 && $fri == 1) || ($day == 6 && $sat == 1) || ($day == 7 && $sun == 1) ){ 
				if($day == 1 && $mon == 1) {
					$START_TIME 	= $_REQUEST['mon_st'];
					$END_TIME 		= $_REQUEST['mon_et'];
					$HOURS 			= $_REQUEST['mon_hr'];
					$PK_CAMPUS_ROOM = $_REQUEST['mon_r'];
					$PK_ATTENDANCE_ACTIVITY_TYPES = $_REQUEST['mon_at'];
				} else if($day == 2 && $tue == 1) {
					$START_TIME 	= $_REQUEST['tue_st'];
					$END_TIME 		= $_REQUEST['tue_et'];
					$HOURS 			= $_REQUEST['tue_hr'];
					$PK_CAMPUS_ROOM = $_REQUEST['tue_r'];
					$PK_ATTENDANCE_ACTIVITY_TYPES = $_REQUEST['tue_at'];
				} else if($day == 3 && $wed == 1) {
					$START_TIME	 = $_REQUEST['wed_st'];
					$END_TIME 		= $_REQUEST['wed_et'];
					$HOURS 			= $_REQUEST['wed_hr'];
					$PK_CAMPUS_ROOM = $_REQUEST['wed_r'];
					$PK_ATTENDANCE_ACTIVITY_TYPES = $_REQUEST['wed_at'];
				} else if($day == 4 && $thu == 1) {
					$START_TIME 	= $_REQUEST['thu_st'];
					$END_TIME 		= $_REQUEST['thu_et'];
					$HOURS 			= $_REQUEST['thu_hr'];
					$PK_CAMPUS_ROOM = $_REQUEST['thu_r'];
					$PK_ATTENDANCE_ACTIVITY_TYPES = $_REQUEST['thu_at'];
				} else if($day == 5 && $fri == 1) {
					$START_TIME 	= $_REQUEST['fri_st'];
					$END_TIME 		= $_REQUEST['fri_et'];
					$HOURS 			= $_REQUEST['fri_hr'];
					$PK_CAMPUS_ROOM = $_REQUEST['fri_r'];
					$PK_ATTENDANCE_ACTIVITY_TYPES = $_REQUEST['fri_at'];
				} else if($day == 6 && $sat == 1) {
					$START_TIME 	= $_REQUEST['sat_st'];
					$END_TIME 		= $_REQUEST['sat_et'];
					$HOURS 			= $_REQUEST['sat_hr'];
					$PK_CAMPUS_ROOM = $_REQUEST['sat_r'];
					$PK_ATTENDANCE_ACTIVITY_TYPES = $_REQUEST['sat_at'];
				} else if($day == 7 && $sun == 1) {
					$START_TIME 	= $_REQUEST['sun_st'];
					$END_TIME 		= $_REQUEST['sun_et'];
					$HOURS 			= $_REQUEST['sun_hr'];
					$PK_CAMPUS_ROOM = $_REQUEST['sun_r'];
					$PK_ATTENDANCE_ACTIVITY_TYPES = $_REQUEST['sun_at'];
				} 
				$starttimestamp = strtotime($START_TIME);
				$endtimestamp 	= strtotime($END_TIME);
				if($HOURS == '' && ($starttimestamp != '' && $endtimestamp != '') )
					$HOURS 	= abs($endtimestamp - $starttimestamp)/3600;
				
				$PK_COURSE_OFFERING_SCHEDULE_DETAIL_ARR[] = '';
				$PK_ATTENDANCE_ACTIVITY_TYPES_ARR[] = $PK_ATTENDANCE_ACTIVITY_TYPES;
				$PK_CAMPUS_ROOM_ARR[]				= $PK_CAMPUS_ROOM;
				$SCHEDULE_DATE_ARR[] 				= $SCHEDULE_DATE;
				$START_TIME_ARR[] 					= $START_TIME;
				$END_TIME_ARR[] 					= $END_TIME;
				$HOURS_ARR[] 						= $HOURS;
				$COMP_ARR[] 						= '';
				
				if($HAS_END_DATE == 0) {
					$TOTAL_HOUR += $HOURS;
					if($TOTAL_HOUR >= $COURSE_HOUR)
						break;
				}
			}
		} 
	}
} else if($_REQUEST['BUILD'] == 2) {
	$PK_COURSE_OFFERING_SCHEDULE_DETAIL_ARR[] = '';
	$PK_ATTENDANCE_ACTIVITY_TYPES_ARR[]		  = '';
	$PK_CAMPUS_ROOM_ARR[]	= $_REQUEST['PK_CAMPUS_ROOM'];
	$SCHEDULE_DATE_ARR[] 	= '';
	$START_TIME_ARR[] 		= '';
	$END_TIME_ARR[] 		= '';
	$HOURS_ARR[] 			= '';
	$COMP_ARR[] 			= '';
	$PK_CAMPUS 				= $_REQUEST['PK_CAMPUS'];
} else {
	$PK_COURSE_OFFERING = $_REQUEST['PK_COURSE_OFFERING'];
	$res_type = $db->Execute("select * from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY SCHEDULE_DATE ASC, START_TIME ASC");

	while (!$res_type->EOF) {
		$PK_COURSE_OFFERING_SCHEDULE_DETAIL_ARR[] = $res_type->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
		$PK_ATTENDANCE_ACTIVITY_TYPES_ARR[]		  = $res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPES'];
		$PK_CAMPUS_ROOM_ARR[] 					  = $res_type->fields['PK_CAMPUS_ROOM'];
		$COMP_ARR[] 							  = $res_type->fields['COMPLETED'];
		
		if($res_type->fields['SCHEDULE_DATE'] != '0000-00-00')
			$SCHEDULE_DATE_ARR[] = date("m/d/Y",strtotime($res_type->fields['SCHEDULE_DATE']));
		else
			$SCHEDULE_DATE_ARR[] = '';
			
		if($res_type->fields['START_TIME'] != '00:00:00')
			$START_TIME_ARR[] = date("h:i A",strtotime($res_type->fields['START_TIME']));
		else
			$START_TIME_ARR[] = '';
			
		if($res_type->fields['END_TIME'] != '00:00:00')
			$END_TIME_ARR[] = date("h:i A",strtotime($res_type->fields['END_TIME']));
		else
			$END_TIME_ARR[] = '';
		
		$HOURS_ARR[] = $res_type->fields['HOURS'];
		
		$res_type->MoveNext();
	}
}

$res_set = $db->Execute("SELECT ENABLE_ATTENDANCE_ACTIVITY_TYPES FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
$ENABLE_ATTENDANCE_ACTIVITY_TYPES = $res_set->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'];	

$i = $_REQUEST['index'];
$j = 0;
if(!empty($SCHEDULE_DATE_ARR)){ 
	foreach($SCHEDULE_DATE_ARR as $SCHEDULE_DATE_1){ ?>
		<div class="row" id="SCHEDULE_DIV_<?=$i?>" >
			<div class="col-md-2" style="max-width:13.667%" >
				<input type="hidden" name="SCHEDULE_HID[]" value="<?=$i?>"  />
				<input type="hidden" name="PK_COURSE_OFFERING_SCHEDULE_DETAIL[]" value="<?=$PK_COURSE_OFFERING_SCHEDULE_DETAIL_ARR[$j]?>"  />
				
				<input type="text" id="SCHEDULE_DATE_<?=$i?>" name="SCHEDULE_DATE[]" value="<?=$SCHEDULE_DATE_1?>" class="form-control date required-entry">
			</div>
			<div class="col-md-1" style="max-width:7%;padding-top: 11px;" >
				<? if($SCHEDULE_DATE_1 != ''){ ?>
				<b><?=date("D",strtotime($SCHEDULE_DATE_1))?></b>
				<? } ?>
			</div>
			<div class="col-md-2" style="max-width:13.667%">
				<input type="text" id="SCHEDULE_<?=$i?>_START_TIME" name="SCHEDULE_START_TIME[]" value="<?=$START_TIME_ARR[$j]?>" class="form-control timepicker required-entry" onchange="get_hour('SCHEDULE_<?=$i?>')" >
			</div>
			<div class="col-md-2" style="max-width:13.667%">
				<input type="text" id="SCHEDULE_<?=$i?>_END_TIME" name="SCHEDULE_END_TIME[]" value="<?=$END_TIME_ARR[$j] ?>" class="form-control timepicker required-entry" onchange="get_hour('SCHEDULE_<?=$i?>')" >
			</div>
			<div class="col-md-1">
				<input type="text" id="SCHEDULE_<?=$i?>_HOURS" name="SCHEDULE_HOURS[]" value="<?=$HOURS_ARR[$j] ?>" class="form-control " onchange="calc_total_scheduled_hours(1)" >
			</div>
			<div class="col-md-1">
				<select id="SCHEDULE_PK_CAMPUS_ROOM_<?=$i?>" name="SCHEDULE_PK_CAMPUS_ROOM[]" class="form-control">
					<option value=""></option>
					<? /* Ticket #1695  */
					$res_type = $db->Execute("select PK_CAMPUS_ROOM, ROOM_NO, ROOM_DESCRIPTION, ACTIVE from M_CAMPUS_ROOM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$PK_CAMPUS' order by ACTIVE DESC, ROOM_NO ASC");
					while (!$res_type->EOF) { 
						$option_label = $res_type->fields['ROOM_NO'].' - '.$res_type->fields['ROOM_DESCRIPTION'];
						if($res_type->fields['ACTIVE'] == 0)
							$option_label .= " (Inactive)"; ?>
						<option value="<?=$res_type->fields['PK_CAMPUS_ROOM'] ?>" <? if($PK_CAMPUS_ROOM_ARR[$j] == $res_type->fields['PK_CAMPUS_ROOM']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
					<?	$res_type->MoveNext();
					} /* Ticket #1695  */ ?>
				</select>
			</div>
			
			<? if($ENABLE_ATTENDANCE_ACTIVITY_TYPES == 1){ ?>
			<div class="col-md-2">
				<select id="SCHEDULE_PK_ATTENDANCE_ACTIVITY_TYPES_<?=$i?>" name="SCHEDULE_PK_ATTENDANCE_ACTIVITY_TYPES[]" class="form-control">
					<option value=""></option>
					<? /* Ticket #1695  */
					$res_type = $db->Execute("select PK_ATTENDANCE_ACTIVITY_TYPE, ATTENDANCE_ACTIVITY_TYPE, ACTIVE from M_ATTENDANCE_ACTIVITY_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC,  ATTENDANCE_ACTIVITY_TYPE ASC");
					while (!$res_type->EOF) { 
						$option_label = $res_type->fields['ATTENDANCE_ACTIVITY_TYPE'];
						if($res_type->fields['ACTIVE'] == 0)
							$option_label .= " (Inactive)"; ?>
						<option value="<?=$res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE'] ?>" <? if($PK_ATTENDANCE_ACTIVITY_TYPES_ARR[$j] == $res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
					<?	$res_type->MoveNext();
					} /* Ticket #1695  */ ?>
				</select>
			</div>
			<? } ?>
			
			<div class="col-md-1 form-group custom-control custom-checkbox form-group" style="padding-right: 5px;text-align: center;max-width: 13%;" >
				<input type="checkbox" class="custom-control-input" id="COMPLETED_<?=$i?>" name="COMPLETED_<?=$i?>" <? if($COMP_ARR[$j] == 1) echo "checked"; ?> value="1" onclick="hide_delete(<?=$i?>)" >
				<label class="custom-control-label" for="COMPLETED_<?=$i?>"></label>
			</div>
			<div class="col-md-1" style="padding:0;max-width: 7.333%;" >
				<? $delete_style = "";
				if($COMP_ARR[$j] == 1) $delete_style = "display:none"; ?>
				<!--<div class="form-group custom-control custom-checkbox form-group" style="width:10px;float: left;<?=$delete_style?>" id="MUL_SEL_DIV_<?=$i?>" >
					<input type="checkbox" class="custom-control-input" id="MUL_SEL_<?=$i?>" name="MUL_SEL[]" onclick="show_bulk_del()" value="<?=$i?>" >
					<label class="custom-control-label" for="MUL_SEL_<?=$i?>"></label>
				</div>-->
				<a style="width: 23px;height: 23px;padding:1px;<?=$delete_style?>" href="javascript:void(0);" id="del_link_<?=$i?>" onclick="delete_row('<?=$i?>','schedule_det')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
			</div>
		</div>
	<? 	$i++;
		$j++;
	} 
} ?>
