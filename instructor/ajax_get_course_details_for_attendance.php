<? require_once("../global/config.php"); 
require_once("../language/attendance_entry.php");
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

if($_REQUEST['val'] > 0) {
	$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $_REQUEST['val'];
	$res_cs = $db->Execute("select DATE_FORMAT(SCHEDULE_DATE,'%m/%d/%Y') AS SCHEDULE_DATE, DATE_FORMAT(START_TIME,'%h:%i %p') AS START_TIME, DATE_FORMAT(END_TIME,'%h:%i %p') AS END_TIME, HOURS, ATTENDANCE_TYPE, CONCAT(ROOM_NO,' - ',ROOM_DESCRIPTION) AS ROOM_NO, CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) AS NAME, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_COURSE_OFFERING LEFT JOIN M_ATTENDANCE_TYPE ON M_ATTENDANCE_TYPE.PK_ATTENDANCE_TYPE = S_COURSE_OFFERING.PK_ATTENDANCE_TYPE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = INSTRUCTOR ,S_COURSE_OFFERING_SCHEDULE_DETAIL LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_CAMPUS_ROOM WHERE S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$PK_COURSE_OFFERING_SCHEDULE_DETAIL' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING");
} else if($_REQUEST['PK_COURSE_OFFERING'] > 0) {
	$PK_COURSE_OFFERING = $_REQUEST['PK_COURSE_OFFERING'];
	$res_cs = $db->Execute("select DATE_FORMAT(DEF_START_TIME,'%h:%i %p') AS START_TIME, DATE_FORMAT(DEF_END_TIME,'%h:%i %p') AS END_TIME, HOURS, CONCAT(ROOM_NO,' - ',ROOM_DESCRIPTION) AS ROOM_NO,FA_UNITS,  UNITS, CONCAT(S_EMPLOYEE_MASTER_INST.FIRST_NAME,' ',S_EMPLOYEE_MASTER_INST.MIDDLE_NAME,' ',S_EMPLOYEE_MASTER_INST.LAST_NAME) AS INSTRUCTOR_NAME,ATTENDANCE_TYPE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1,SESSION,SESSION_NO from S_COURSE_OFFERING LEFT JOIN M_ATTENDANCE_TYPE ON M_ATTENDANCE_TYPE.PK_ATTENDANCE_TYPE = S_COURSE_OFFERING.PK_ATTENDANCE_TYPE LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER LEFT JOIN S_EMPLOYEE_MASTER AS S_EMPLOYEE_MASTER_INST ON S_EMPLOYEE_MASTER_INST.PK_EMPLOYEE_MASTER = INSTRUCTOR LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM, S_COURSE_OFFERING_SCHEDULE,S_COURSE WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_COURSE_OFFERING_SCHEDULE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE");
	
	$ASSISTANT_NAME = '';
	$res_ass = $db->Execute("SELECT CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) AS ASSISTANT_NAME FROM S_COURSE_OFFERING_ASSISTANT, S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING_ASSISTANT.ASSISTANT AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_COURSE_OFFERING_ASSISTANT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	while (!$res_ass->EOF) { 
		if($ASSISTANT_NAME != '')
			$ASSISTANT_NAME .= ', ';
		$ASSISTANT_NAME .= $res_ass->fields['ASSISTANT_NAME'];
		
		$res_ass->MoveNext();
	}
}

if($_REQUEST['format'] == 1) { ?>
	<div class="row">
		<div class="col-1 form-group" style="padding:0" >
			<b style="font-weight:bold"><?=ROOM_NO?></b>
		</div>
		<div class="col-2 form-group">
			<?=$res_cs->fields['ROOM_NO']?>
		</div>
		
		<div class="col-1 form-group" style="padding-right: 0;">
			<b style="font-weight:bold" ><?=CLASS_TIME?></b>
		</div>
		<div class="col-2 form-group">
			<?=$res_cs->fields['START_TIME']?> - <?=$res_cs->fields['END_TIME']?>
		</div>
	</div>
	
	<div class="row">
		<div class="col-1 form-group" style="padding:0" >
			<b style="font-weight:bold"><?=INSTRUCTOR?></b>
		</div>
		<div class="col-2 form-group">
			<?=$res_cs->fields['INSTRUCTOR_NAME']?>
		</div>
		
		<div class="col-1 form-group">
			<b style="font-weight:bold"><?=HOUR?></b>
		</div>
		<div class="col-2 form-group">
			<?=$res_cs->fields['HOURS']?>
		</div>
	</div>
	
	<div class="row">
		<div class="col-1 form-group" style="padding:0">
			<b style="font-weight:bold"><?=ASSISTANT?></b>
		</div>
		<div class="col-2 form-group">
			<?=$$ASSISTANT_NAME?>
		</div>
		
		<div class="col-1 form-group">
			<b style="font-weight:bold"><?=UNITS?></b>
		</div>
		<div class="col-2 form-group">
			<?=$res_cs->fields['UNITS']?>
		</div>
	</div>
	
	<div class="row">	
		<div class="col-1 form-group" style="padding:0">
			<b style="font-weight:bold"><?=FA_UNITS?></b>
		</div>
		<div class="col-2 form-group">
			<?=$res_cs->fields['FA_UNITS']?>
		</div>
	</div>	

<? }  else { 
	if($res_cs->fields['SCHEDULE_DATE'] != ''){ ?>
	<div class="row">
		<div class="col-5 form-group">
			<b style="font-weight:bold"><?=CLASS_DATE?></b>
		</div>
		<div class="col-7 form-group">
			<?=$res_cs->fields['SCHEDULE_DATE']?>
		</div>
	</div>
	<? } ?>
	<div class="row">
		<div class="col-5 form-group">
			<b style="font-weight:bold"><?=CLASS_HOUR?></b>
		</div>
		<div class="col-7 form-group">
			<?=$res_cs->fields['HOURS']?>
		</div>
	</div>
	<div class="row">
		<div class="col-5 form-group">
			<b style="font-weight:bold"><?=START_TIME?></b>
		</div>
		<div class="col-7 form-group">
			<?=$res_cs->fields['START_TIME']?>
		</div>
	</div>
	<div class="row">
		<div class="col-5 form-group">
			<b style="font-weight:bold"><?=END_TIME?></b>
		</div>
		<div class="col-7 form-group">
			<?=$res_cs->fields['END_TIME']?>
		</div>
	</div>
	<div class="row">
		<div class="col-5 form-group">
			<b style="font-weight:bold"><?=ATTENDANCE?></b>
		</div>
		<div class="col-7 form-group">
			<?=$res_cs->fields['ATTENDANCE_TYPE']?>
		</div>
	</div>
	<div class="row">
		<div class="col-5 form-group">
			<b style="font-weight:bold"><?=ROOM_NO?></b>
		</div>
		<div class="col-7 form-group">
			<?=$res_cs->fields['ROOM_NO']?>
		</div>
	</div>
	<? if($_SESSION['PK_ROLES'] != 3){ ?>
	<div class="row">
		<div class="col-5 form-group">
			<b style="font-weight:bold"><?=INSTRUCTOR?></b>
		</div>
		<div class="col-7 form-group">
			<?=$res_cs->fields['INSTRUCTOR_NAME']?>
		</div>
	</div>
	<? } ?>
	<div class="row">
		<div class="col-5 form-group">
			<b style="font-weight:bold"><?=TERM_START?></b>
		</div>
		<div class="col-7 form-group">
			<?=$res_cs->fields['BEGIN_DATE_1']?>
		</div>
	</div>
<? } ?>