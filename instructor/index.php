<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/dashboard.php");
require_once("../language/instructor_attendance.php");
//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_ROLES'] != 3 ){  
	header("location:../index");
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=DASHBOARD_1?> | <?=$title?></title>
	
	<link href="../backend_assets/node_modules/calendar/dist/fullcalendar.css" rel="stylesheet" />
	<link href="../backend_assets/dist/css/pages/chat-app-page.css" rel="stylesheet">
	<style>
	.fc-month-view span.fc-title{
		white-space: normal;
	}
	.fc-time{display: none;}
	.chat-list li {margin-top: 5px;}
	.fc-week{border-right-width: 1px !important;margin-right: 16px !important;}
	<? $res_type = $db->Execute("select PK_SESSION,COLOR from M_SESSION WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by DISPLAY_ORDER ASC");
	while (!$res_type->EOF) { ?>
		.bg-info-<?=$res_type->fields['PK_SESSION']?>{background-color: #<?=$res_type->fields['COLOR']?> !important; color:#000 !important;}
	<?	$res_type->MoveNext();
	} ?>
	</style>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=DASHBOARD_1?></h4>
                    </div>
                </div>	

				<div class="row">
					<div class="col-lg-4 col-md-4">
						<div class="card">
							<div class="card-body">
								<div class="d-flex align-items-center no-block">
									<div class="row" style="width: 100%;" >
										<div class="col-4">
											<h5 class="card-title "><?=ACTIVITIES?></h5>
										</div>
										<div class="col-7">
											<select id="PK_EMPLOYEE_MASTER" name="PK_EMPLOYEE_MASTER" class="form-control" onchange="get_activity(this.value)" >
												<option value="">All</option>
												<? $res_type = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME) AS EMP_NAME, PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE ACTIVE = 1 AND (PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' OR PK_SUPERVISOR = '$_SESSION[PK_EMPLOYEE_MASTER]') AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER'] ?>" ><?=$res_type->fields['EMP_NAME'] ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-1">
											<!--<a href="student_task?p=i"><i class="fa fa-plus-circle"></i></a>-->
										</div>
									</div>
								</div>
								
								<div id="activity_div" >
									<? $_REQUEST['PK_EMPLOYEE_MASTER'] = '';
									include("../school/ajax_dashboard_activity.php");?>
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-lg-4 col-md-4">
						<div class="card">
							<div class="card-body">
								<div class="d-flex align-items-center no-block">
									<h5 class="card-title "><?=ANNOUNCEMENT?></h5>
								</div>
								<? //$cur_time_cet = convert_to_user_date(date('Y-m-d H:i:s'),'Y-m-d H:i:s','CET',date_default_timezone_get());
								$res_z_acc = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
								 $PK_TIMEZONE = ($res_z_acc->fields['PK_TIMEZONE'])?$res_z_acc->fields['PK_TIMEZONE']:$_SESSION['PK_TIMEZONE'];							
								$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE PK_TIMEZONE = '$PK_TIMEZONE'"); 
								$cur_time_cet = convert_to_user_date(date('Y-m-d H:i:s'), "Y-m-d h:i:s", $res_tz->fields['TIMEZONE'],date_default_timezone_get());										
								$table = "";
								$cond  = "";
								if($_SESSION['PK_ROLES'] == 3 || $_SESSION['PK_ROLES'] == 4 || $_SESSION['PK_ROLES'] == 5) {
									if(isset($_SESSION['PK_CAMPUS']) && $_SESSION['PK_CAMPUS'] != '')
									{
										$cond .= " AND Z_ANNOUNCEMENT_CAMPUS.PK_CAMPUS IN ($_SESSION[PK_CAMPUS]) ";
									}
									$table = " ,Z_ANNOUNCEMENT_CAMPUS, Z_ANNOUNCEMENT_FOR, M_ANNOUNCEMENT_FOR_MASTER ";
									$cond  .= "  AND Z_ANNOUNCEMENT.PK_ANNOUNCEMENT = Z_ANNOUNCEMENT_CAMPUS.PK_ANNOUNCEMENT AND Z_ANNOUNCEMENT_FOR.PK_ANNOUNCEMENT = Z_ANNOUNCEMENT.PK_ANNOUNCEMENT AND 
									Z_ANNOUNCEMENT_FOR.PK_ANNOUNCEMENT_FOR_MASTER = M_ANNOUNCEMENT_FOR_MASTER.PK_ANNOUNCEMENT_FOR_MASTER ";
								}
								
								$res = $db->Execute("SELECT * FROM (SELECT * FROM Z_ANNOUNCEMENT WHERE ACTIVE = 1 AND ANNOUNCEMENT_FROM = 1 AND '$cur_time_cet' BETWEEN START_DATE_TIME_CET AND END_DATE_TIME_CET  
								UNION 
								SELECT Z_ANNOUNCEMENT.* FROM Z_ANNOUNCEMENT,Z_ANNOUNCEMENT_EMPLOYEE $table WHERE Z_ANNOUNCEMENT.ACTIVE = 1 AND ANNOUNCEMENT_FROM = 2 $cond AND Z_ANNOUNCEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND '$cur_time_cet' BETWEEN START_DATE_TIME_CET AND END_DATE_TIME_CET AND Z_ANNOUNCEMENT.PK_ANNOUNCEMENT = Z_ANNOUNCEMENT_EMPLOYEE.PK_ANNOUNCEMENT AND PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]') AS TEMPT GROUP BY PK_ANNOUNCEMENT  ORDER BY START_DATE_TIME_CET ASC ");  ?>
								<div style="height: 400px;overflow-y: auto;" >
									<? while (!$res->EOF){ ?>
									<div class="col-12">
									   <a href="announcement_detail?id=<?=$res->fields['PK_ANNOUNCEMENT']?>">
											<? if($_SESSION['PK_LANGUAGE'] == 1)
												echo $res->fields['SHORT_DESC_ENG']; 
											else 
												echo $res->fields['SHORT_DESC_SPA'];  ?>
									   </a>
									   <hr />
									</div>
									<? $res->MoveNext();
									} ?>
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-lg-4 col-md-4">
						<div class="card">
							<div class="card-body">
								<div class="d-flex align-items-center no-block">
									<h5 class="card-title "><?=DOCUMENTS ?> &nbsp;&nbsp;<!--<a href="collateral?p=i"><i class="fa fa-plus-circle"></i></a> --></h5>
								</div>
								<div style="height: 400px;overflow-y: auto;">
									<? $res = $db->Execute("SELECT FILE_NAME,FILE_LOCATION FROM S_COLLATERAL WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY FILE_NAME ASC ");
									while (!$res->EOF){ ?>
									<div class="col-12">
									   <a href="<?=$res->fields['FILE_LOCATION']?>" target="_blank" >
											<?=$res->fields['FILE_NAME'];  ?>
									   </a>
									   <hr />
									</div>
									<? $res->MoveNext();
									} ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
							<div class="row">
								<div class="col-md-12">
									<div class="card-body b-l calender-sidebar">
										<!-- Ticket # 1006 -->
										<form name="form1" method="get" >
											<div class="row" id="cal1">
												<div class="col-2">
													<? 
													if($_GET['CAL_TYPE'] != '')
														$CAL_TYPE = explode(",",$_GET['CAL_TYPE']);
													else
														$CAL_TYPE = array();
													$ac_selected = "";
													$a_selected  = "";
													$s_selected  = "";
													if(!empty($CAL_TYPE)){
														foreach($CAL_TYPE as $CAL_TYPE1){
															if($CAL_TYPE1 == 1)
																$ac_selected = "selected";
															else if($CAL_TYPE1 == 2)
																$a_selected = "selected";
															else if($CAL_TYPE1 == 3)
																$s_selected = "selected";
														}
													} else {
														$ac_selected = "selected";
														$a_selected  = "selected";
														$s_selected  = "selected";
													} ?>
													<select id="CAL_TYPE" name="CAL_TYPE[]" multiple class="form-control" onchange="show_fields()" >
														<option value="1" <?=$ac_selected ?> >Academic Calendar</option>
														<option value="2" <?=$a_selected ?> >Activities</option>
														<option value="3" <?=$s_selected ?> >Schedule</option>
													</select>
												</div>
												
												<div class="col-2" id="CAL_PK_EMPLOYEE_MASTER_DIV" <? if($a_selected == '') { ?> style="display:none" <? } ?> >
													<select id="CAL_PK_EMPLOYEE_MASTER" name="CAL_PK_EMPLOYEE_MASTER" class="form-control" >
														<option value="">All Users</option>
														<? $res_type = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME) AS EMP_NAME, PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE ACTIVE = 1 AND (PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' OR PK_SUPERVISOR = '$_SESSION[PK_EMPLOYEE_MASTER]') AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER'] ?>" <? if($res_type->fields['PK_EMPLOYEE_MASTER'] == $_GET['CAL_PK_EMPLOYEE_MASTER']) echo "selected"; ?> ><?=$res_type->fields['EMP_NAME'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
												<div class="col-2" id="CAL_PK_TASK_TYPE_DIV" <? if($a_selected == '') { ?> style="display:none" <? } ?> >
													<select id="CAL_PK_TASK_TYPE" name="CAL_PK_TASK_TYPE[]" multiple class="form-control" >
														<? if($_GET['CAL_PK_TASK_TYPE'] != '')
															$CAL_PK_TASK_TYPE = explode(",",$_GET['CAL_PK_TASK_TYPE']);
														$res_type = $db->Execute("select PK_TASK_TYPE, TASK_TYPE from M_TASK_TYPE WHERE ACTIVE = 1  AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by TASK_TYPE ASC");
														while (!$res_type->EOF) { 
															$selected = "";
															if(!empty($CAL_PK_TASK_TYPE)){
																foreach($CAL_PK_TASK_TYPE as $CAL_PK_TASK_TYPE1){
																	if($CAL_PK_TASK_TYPE1 == $res_type->fields['PK_TASK_TYPE'])
																		$selected = "selected";
																}
															} ?>
															<option value="<?=$res_type->fields['PK_TASK_TYPE'] ?>" <?=$selected ?> ><?=$res_type->fields['TASK_TYPE'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
												
												<div class="col-2" id="CAL_PK_TERM_MASTER_DIV" <? if($s_selected == '') { ?> style="display:none" <? } ?> >
													<select id="CAL_PK_TERM_MASTER" name="CAL_PK_TERM_MASTER[]" multiple class="form-control" onchange="get_course_offering()" >
														<? if($_GET['CAL_PK_TERM_MASTER'] != '')
															$CAL_PK_TERM_MASTER = explode(",",$_GET['CAL_PK_TERM_MASTER']);
															
														$res_type = $db->Execute("select S_TERM_MASTER.PK_TERM_MASTER,IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, TERM_DESCRIPTION from S_COURSE_OFFERING LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING , S_TERM_MASTER WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND  S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER GROUP BY S_TERM_MASTER.PK_TERM_MASTER ORDER BY BEGIN_DATE DESC");
														while (!$res_type->EOF) { 
															$selected = "";
															if(!empty($CAL_PK_TERM_MASTER)){
																foreach($CAL_PK_TERM_MASTER as $CAL_PK_TERM_MASTER1){
																	if($CAL_PK_TERM_MASTER1 == $res_type->fields['PK_TERM_MASTER'])
																		$selected = "selected";
																}
															} ?>
															<option value="<?=$res_type->fields['PK_TERM_MASTER'] ?>" <?=$selected ?> ><?=$res_type->fields['TERM_BEGIN_DATE'].' - '.$res_type->fields['TERM_DESCRIPTION'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
												<div class="col-2" id="CAL_PK_COURSE_OFFERING_DIV" <? if($s_selected == '') { ?> style="display:none" <? } ?> >
													<? $_REQUEST['PK_TERM_MASTER'] 	= $_GET['CAL_PK_TERM_MASTER'];
													$_REQUEST['def_val'] 			= $_GET['CAL_PK_COURSE_OFFERING'];
													include("ajax_get_course_offering_calendar.php"); ?>
												</div>
												
												<div class="col-1">
													<button type="button" onclick="search_cal()" class="btn waves-effect waves-light btn-info">Go</button>
												</div>
												
												<div class="col-6">
													<br /><br />
													<div class="row">
														<div class="col-12"><h5 class="card-title " style="margin-bottom: 2px;" ><?=INSTRUCTOR_SCHEDULE ?></h5></div>
													</div>
													<div class="row">
														<div class="col-4 form-group">
															<input class="form-control date" type="text" value="<?=$_GET['sd']?>" id="sd" name="sd" placeholder="<?=SELECT_FIRST_DATE?>" >
														</div>
														<div class="col-4 form-group">
															<input class="form-control date" type="text" value="<?=$_GET['ed']?>" id="ed" name="ed"  placeholder="<?=SELECT_LAST_DATE?>" >
														</div>
														<div class="col-2 form-group">
															<a href="javascript:void(0)" onclick="generate_pdf()"  class="btn waves-effect waves-light btn-info" title="PDF">
																<?=PDF ?>
															</a>
														</div>
													</div>
												</div>
											</div>
										</form>
										<!-- Ticket # 1006 -->
										<div id="calendar"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
						
            </div>
        </div>
        <? require_once("footer.php"); ?>		
    </div>
    <? require_once("js.php"); ?>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) { 
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
		});
	</script>
	
	<script src="../backend_assets/node_modules/moment/moment.js"></script>
	<script src='../backend_assets/node_modules/calendar/dist/fullcalendar.js'></script>
	
	<script>
	!function($) {
		"use strict";

		var CalendarApp = function() {
			this.$body = $("body")
			this.$calendar = $('#calendar'),
			this.$event = ('#calendar-events div.calendar-events'),
			this.$categoryForm = $('#add-new-event form'),
			this.$extEvents = $('#calendar-events'),
			this.$modal = $('#my-event'),
			this.$saveCategoryBtn = $('.save-category'),
			this.$calendarObj = null
		};


		/* on drop */
		CalendarApp.prototype.onDrop = function (eventObj, date) { 
		},
		/* on click on event */
		CalendarApp.prototype.onEventClick =  function (calEvent, jsEvent, view) {
			//alert(calEvent.id)
			if(calEvent.id != '')
				window.location.href = calEvent.id
		},
		/* on select */
		CalendarApp.prototype.onSelect = function (start, end, allDay) {
		},
		CalendarApp.prototype.enableDrag = function() {
		}
		/* Initializing */
		CalendarApp.prototype.init = function() {
			this.enableDrag();
			/*  Initialize the calendar  */
			var date = new Date();
			var d = date.getDate();
			var m = date.getMonth();
			var y = date.getFullYear();
			var form = '';
			var today = new Date($.now());

			var defaultEvents =  [
				<? if($a_selected != '') {
					$date_cond = " AND TASK_DATE <= '".date("Y-m-d", strtotime("+7 days", strtotime(date("Y-m-d"))))."' "; 
					if($_GET['CAL_PK_EMPLOYEE_MASTER'] != '')
						$date_cond .= " AND S_STUDENT_TASK.PK_EMPLOYEE_MASTER = '$_GET[CAL_PK_EMPLOYEE_MASTER]' ";
						
					if($_GET['CAL_PK_TASK_TYPE'] != '')
						$date_cond .= " AND S_STUDENT_TASK.PK_TASK_TYPE IN ($_GET[CAL_PK_TASK_TYPE]) ";
						
					$res_type = $db->Execute("select PK_STUDENT_ENROLLMENT,PK_STUDENT_TASK,TASK_TIME, TASK_TYPE,TASK_STATUS,NOTES ,TASK_DATE ,S_STUDENT_TASK.PK_STUDENT_MASTER, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE,'%m/%d/%Y')) AS FOLLOWUP_DATE, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS EMP_NAME , CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME FROM S_STUDENT_MASTER,S_STUDENT_TASK LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_TASK.PK_EMPLOYEE_MASTER JOIN M_TASK_TYPE ON M_TASK_TYPE.PK_TASK_TYPE = S_STUDENT_TASK.PK_TASK_TYPE LEFT JOIN M_TASK_STATUS ON M_TASK_STATUS.PK_TASK_STATUS = S_STUDENT_TASK.PK_TASK_STATUS WHERE S_STUDENT_TASK.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND COMPLETED = 0 AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_TASK.PK_STUDENT_MASTER AND S_STUDENT_TASK.PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' $date_cond AND TASK_DATE != '0000-00-00' ORDER BY TASK_DATE ASC "); 
					while (!$res_type->EOF) { ?>
					{
						id: "student_task?sid=<?=$res_type->fields['PK_STUDENT_MASTER']?>&id=<?=$res_type->fields['PK_STUDENT_TASK']?>&eid=<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>&t=&p=i",
						title: '<?=$res_type->fields['STU_NAME'].' - '.$res_type->fields['TASK_TYPE'].' - '.$res_type->fields['TASK_STATUS']?>',
						start: new Date(<?=date("Y",strtotime($res_type->fields['TASK_DATE']))?>,<?=intval((date("m",strtotime($res_type->fields['TASK_DATE'])) - 1))?>,<?=intval(date("d",strtotime($res_type->fields['TASK_DATE'])))?>,<?=intval(date("H",strtotime($res_type->fields['TASK_TIME'])))?>,<?=intval(date("i",strtotime($res_type->fields['TASK_TIME'])))?>,1,1),
						end: new Date(<?=date("Y",strtotime($res_type->fields['TASK_DATE']))?>,<?=intval((date("m",strtotime($res_type->fields['TASK_DATE'])) - 1))?>,<?=intval(date("d",strtotime($res_type->fields['TASK_DATE'])))?>,<?=intval(date("H",strtotime($res_type->fields['TASK_TIME'])))?>,<?=(intval(date("i",strtotime($res_type->fields['TASK_TIME']))) + 30) ?>,1,1),
						//className: 'bg-warning'
					},
					<? $res_type->MoveNext();
					} 
				} ?>
				
				<? if($ac_selected != '') {
					$res_type = $db->Execute("SELECT PK_ACADEMIC_CALENDAR,IF(LEAVE_TYPE = 1,'Holiday',IF(LEAVE_TYPE = 2,'Break',IF(LEAVE_TYPE = 3,'Closure',''))) AS LEAVE_TYPE,START_DATE,END_DATE FROM M_ACADEMIC_CALENDAR WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
					while (!$res_type->EOF) { 
						$PK_ACADEMIC_CALENDAR = $res_type->fields['PK_ACADEMIC_CALENDAR']; 
						$res_type_s = $db->Execute("SELECT SESSION,M_SESSION.PK_SESSION FROM M_SESSION,M_ACADEMIC_CALENDAR_SESSION WHERE M_ACADEMIC_CALENDAR_SESSION.PK_SESSION = M_SESSION.PK_SESSION AND PK_ACADEMIC_CALENDAR = '$PK_ACADEMIC_CALENDAR' GROUP BY M_SESSION.PK_SESSION ORDER BY DISPLAY_ORDER ASC "); 
						while (!$res_type_s->EOF) { ?>
							{
								id: "",
								title: '<?=$res_type->fields['LEAVE_TYPE'].' - '.$res_type_s->fields['SESSION']?>',
								start: new Date(<?=date("Y",strtotime($res_type->fields['START_DATE']))?>,<?=intval((date("m",strtotime($res_type->fields['START_DATE'])) - 1))?>,<?=intval(date("d",strtotime($res_type->fields['START_DATE'])))?>,0,0,1,1),
								end: new Date(<?=date("Y",strtotime($res_type->fields['END_DATE']))?>,<?=intval((date("m",strtotime($res_type->fields['END_DATE'])) - 1))?>,<?=intval(date("d",strtotime($res_type->fields['END_DATE'])))?>,23,59,1,1),
								className: 'bg-info-<?=$res_type_s->fields['PK_SESSION']?>'
							},
						<? $res_type_s->MoveNext();
						}
						$res_type->MoveNext();
					} 
				} ?>
				
				<? if($s_selected != '') {
					$sch_cond = "";
					if($_GET['CAL_PK_TERM_MASTER'] != '')
						$sch_cond .= " AND S_COURSE_OFFERING.PK_TERM_MASTER = '$_GET[CAL_PK_TERM_MASTER]' ";
						
					if($_GET['CAL_PK_COURSE_OFFERING'] != '')
						$sch_cond .= " AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN ($_GET[CAL_PK_COURSE_OFFERING]) ";
						
					$res_sch = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL, IF(SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(SCHEDULE_DATE,'%m/%d/%Y'),'') AS SCHEDULE_DATE1, START_TIME, END_TIME, S_COURSE_OFFERING_SCHEDULE_DETAIL.HOURS, ROOM_NO, COURSE_CODE, ROOM_DESCRIPTION, SESSION, SESSION_NO, IF(BEGIN_DATE != '0000-00-00', DATE_FORMAT(BEGIN_DATE,'%m/%d/%Y'),'') AS BEGIN_DATE from 

					S_COURSE_OFFERING 
					LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
					LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING 
					LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
					LEFT JOIn M_SESSION On M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
					, S_COURSE_OFFERING_SCHEDULE_DETAIL 
					LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_CAMPUS_ROOM 

					WHERE 
					S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
					(INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND
					S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING  $sch_cond 
					GROUP BY PK_COURSE_OFFERING_SCHEDULE_DETAIL ORDER BY SCHEDULE_DATE ASC ");
					while (!$res_sch->EOF) { 
						$START_TIME = '';
						$END_TIME 	= '';
						
						if($res_sch->fields['START_TIME'] != '00:00::')
							$START_TIME = date("h:i A",strtotime($res_sch->fields['START_TIME']));
							
						if($res_sch->fields['END_TIME'] != '00:00::')
							$END_TIME = date("h:i A",strtotime($res_sch->fields['END_TIME'])); ?>
					{
						id: "",
						title: '<?=$START_TIME.' - '.$END_TIME.' ('.$res_sch->fields['ROOM_NO'].')' ?>'+"\n"+'<?=$res_sch->fields['BEGIN_DATE'].' - '.$res_sch->fields['COURSE_CODE'].' ('.substr($res_sch->fields['SESSION'],0,1).' - '.$res_sch->fields['SESSION_NO'].')' ?>',
						start: new Date(<?=date("Y",strtotime($res_sch->fields['SCHEDULE_DATE1']))?>,<?=intval((date("m",strtotime($res_sch->fields['SCHEDULE_DATE1'])) - 1))?>,<?=intval(date("d",strtotime($res_sch->fields['SCHEDULE_DATE1'])))?>,<?=intval(date("H",strtotime($res_sch->fields['START_TIME'])))?>,<?=intval(date("i",strtotime($res_sch->fields['START_TIME'])))?>,1,1),
						end: new Date(<?=date("Y",strtotime($res_sch->fields['SCHEDULE_DATE1']))?>,<?=intval((date("m",strtotime($res_sch->fields['SCHEDULE_DATE1'])) - 1))?>,<?=intval(date("d",strtotime($res_sch->fields['SCHEDULE_DATE1'])))?>,<?=intval(date("H",strtotime($res_sch->fields['END_TIME'])))?>,<?=intval(date("i",strtotime($res_sch->fields['END_TIME'])))?>,1,1),
						className: 'bg-warning'
					},
					<? $res_sch->MoveNext();
					} 
				} ?>
			];

			var $this = this;
			$this.$calendarObj = $this.$calendar.fullCalendar({
				slotDuration: '00:15:00', /* If we want to split day time each 15minutes */
				minTime: '00:00:00',
				maxTime: '24:00:00',  
				defaultView: 'month',  
				handleWindowResize: true,   
				 
				header: {
					left: 'prev,next today',
					center: 'title',
					right: 'month,agendaWeek,agendaDay'
				},
				events: defaultEvents,
				editable: false,
				droppable: false, // this allows things to be dropped onto the calendar !!!
				eventLimit: false, // allow "more" link when too many events
				selectable: false,
				drop: function(date) { $this.onDrop($(this), date); },
				select: function (start, end, allDay) { $this.onSelect(start, end, allDay); },
				eventClick: function(calEvent, jsEvent, view) { $this.onEventClick(calEvent, jsEvent, view); }

			});
		},

	   //init CalendarApp
		$.CalendarApp = new CalendarApp, $.CalendarApp.Constructor = CalendarApp
		
	}(window.jQuery),

	//initializing CalendarApp
	function($) {
		"use strict";
		$.CalendarApp.init()
	}(window.jQuery);
	
	function get_activity(id){
		jQuery(document).ready(function($) {
			var data  = 'PK_EMPLOYEE_MASTER='+id;
			//alert(data)
			var value = $.ajax({
				url: "../school/ajax_dashboard_activity",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {
					//alert(data)
					document.getElementById('activity_div').innerHTML = data
				}		
			}).responseText;
		});
	}
	</script>
	
	<!-- Ticket # 1006 -->
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#CAL_PK_TASK_TYPE').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All Task Types',
				nonSelectedText: 'Task Type',
				numberDisplayed: 1,
				nSelectedText: 'Task Type selected'
			});
			
			$('#CAL_TYPE').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'Calendar View',
				nonSelectedText: 'Calendar View',
				numberDisplayed: 1,
				nSelectedText: 'Calendar View selected'
			});
			
			$('#CAL_PK_TERM_MASTER').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'Term',
				nonSelectedText: 'Term',
				numberDisplayed: 1,
				nSelectedText: 'Terms selected'
			});
			
			$('#CAL_PK_COURSE_OFFERING').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'Course Offering',
				nonSelectedText: 'Course Offering',
				numberDisplayed: 1,
				nSelectedText: 'Course Offerings selected'
			});
		});
		
		function search_cal(){
			jQuery(document).ready(function($) {
				var str = "index?CAL_TYPE="+$('#CAL_TYPE').val();
				
				var CAL_TYPE = $('#CAL_TYPE').val()
				for(var i = 0; i < CAL_TYPE.length ; i++){
					if(CAL_TYPE[i] == 2) {
						str += "&CAL_PK_EMPLOYEE_MASTER="+$('#CAL_PK_EMPLOYEE_MASTER').val()+"&CAL_PK_TASK_TYPE="+$('#CAL_PK_TASK_TYPE').val()
					} else if(CAL_TYPE[i] == 3) {
						str += "&CAL_PK_TERM_MASTER="+$('#CAL_PK_TERM_MASTER').val()+"&CAL_PK_COURSE_OFFERING="+$('#CAL_PK_COURSE_OFFERING').val()
					}
				}
				
				window.location.href = str+"#cal1"
			});
		}
		<!-- Ticket # 1006 -->
		
		function generate_pdf(){
			jQuery(document).ready(function($) {
				if(document.getElementById('sd').value != '' && document.getElementById('ed').value != '') {
					
					var task 	 = 0;
					var schedule = 0;
					var ac 		 = 0;
					var CAL_TYPE = $('#CAL_TYPE').val()
					for(var i = 0; i < CAL_TYPE.length ; i++){
						if(CAL_TYPE[i] == 1) {
							ac = 1
						} else if(CAL_TYPE[i] == 2) {
							task = 1
						} else if(CAL_TYPE[i] == 3) {
							schedule = 1
						}
					}
					
					window.location.href = "instructor_calendar_pdf?sd="+document.getElementById('sd').value+"&ed="+document.getElementById('ed').value+"&ac="+ac+"&task="+task+"&schedule="+schedule
				} else {
					alert('Please Select First and Last Date')
				}
			});
		}
		
		function show_fields(){
			jQuery(document).ready(function($) {
				document.getElementById('CAL_PK_EMPLOYEE_MASTER_DIV').style.display = 'none'
				document.getElementById('CAL_PK_TASK_TYPE_DIV').style.display 		= 'none'
				document.getElementById('CAL_PK_TERM_MASTER_DIV').style.display 	= 'none'
				document.getElementById('CAL_PK_COURSE_OFFERING_DIV').style.display = 'none'
				
				var CAL_TYPE = $('#CAL_TYPE').val()
				for(var i = 0; i < CAL_TYPE.length ; i++){
					if(CAL_TYPE[i] == 2) {
						document.getElementById('CAL_PK_EMPLOYEE_MASTER_DIV').style.display = 'block'
						document.getElementById('CAL_PK_TASK_TYPE_DIV').style.display 		= 'block'
					} else if(CAL_TYPE[i] == 3) {
						document.getElementById('CAL_PK_TERM_MASTER_DIV').style.display 	= 'block'
						document.getElementById('CAL_PK_COURSE_OFFERING_DIV').style.display = 'block'
					}
				}
			});
		}
		
		function get_course_offering(){
			jQuery(document).ready(function($) { 
				var data  = 'PK_TERM_MASTER='+$('#CAL_PK_TERM_MASTER').val();
				var value = $.ajax({
					url: "ajax_get_course_offering_calendar",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('CAL_PK_COURSE_OFFERING_DIV').innerHTML = data;
						
						$('#CAL_PK_COURSE_OFFERING').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'Course Offering',
							nonSelectedText: 'Course Offering',
							numberDisplayed: 1,
							nSelectedText: 'Course Offerings selected'
						});
						
					}		
				}).responseText;
			});
		}
	</script>
</body>
</html>
