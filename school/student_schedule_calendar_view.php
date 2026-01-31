<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("check_access.php");

$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');

if($REGISTRAR_ACCESS == 0){ 
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
	<title><?=CALENDAR_VIEW?> | <?=$title?></title>
	<link href="../backend_assets/node_modules/calendar/dist/fullcalendar.css" rel="stylesheet" />
	<style>
	.fc-month-view span.fc-title{
		white-space: normal;
	}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-3 align-self-center">
                        <h4 class="text-themecolor">
							<?=CALENDAR_VIEW ?> 
							<!--<a target="_blank" href="student_schedule_calendar_view_pdf?id=<?=$_GET['id']?>&eid=<?=$_GET['eid']?>" title="<?=PDF?>" ><i class="mdi mdi-file-pdf" style="font-size:25px" ></i> </a>-->
						</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<div class="card-body b-l calender-sidebar">
											<div class="row">
												<div class="col-2 form-group">
													<input class="form-control date" type="text" value="<?=$_GET['sd']?>" id="sd" name="sd" placeholder="Select First Date" >
												</div>
												<div class="col-2 form-group">
													<input class="form-control date" type="text" value="<?=$_GET['ed']?>" id="ed" name="ed"  placeholder="Select Last Date" >
												</div>
												<div class="col-2 form-group">
													<a href="javascript:void(0)" onclick="generate_pdf()"  class="btn waves-effect waves-light btn-info" title="PDF">
														<?=PDF ?>
													</a>
												</div>
											</div>
											<div id="calendar"></div>
										</div>
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
				window.open(calEvent.id);
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
				<? $res_sch = $db->Execute("select PK_STUDENT_SCHEDULE, IF(SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(SCHEDULE_DATE,'%m/%d/%Y'),'') AS SCHEDULE_DATE, START_TIME, END_TIME, S_STUDENT_SCHEDULE.HOURS, ROOM_NO, COURSE_CODE, ROOM_DESCRIPTION, CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) AS NAME from 

				S_STUDENT_SCHEDULE 
				LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
				LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING
				LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR
				LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_STUDENT_SCHEDULE.PK_CAMPUS_ROOM 
				LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 

				WHERE 
				S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$_GET[id]' AND 
				S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND 
				S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY SCHEDULE_DATE ASC");
				while (!$res_sch->EOF) { ?>
				{
					id: "",
					title: '<?=$res_sch->fields['COURSE_CODE'].' [Room # '.$res_sch->fields['ROOM_NO'].'] [Inst: '.$res_sch->fields['NAME'].']'?>',
					start: new Date(<?=date("Y",strtotime($res_sch->fields['SCHEDULE_DATE']))?>,<?=intval((date("m",strtotime($res_sch->fields['SCHEDULE_DATE'])) - 1))?>,<?=intval(date("d",strtotime($res_sch->fields['SCHEDULE_DATE'])))?>,<?=intval(date("H",strtotime($res_sch->fields['START_TIME'])))?>,<?=intval(date("i",strtotime($res_sch->fields['START_TIME'])))?>,1,1),
					end: new Date(<?=date("Y",strtotime($res_sch->fields['SCHEDULE_DATE']))?>,<?=intval((date("m",strtotime($res_sch->fields['SCHEDULE_DATE'])) - 1))?>,<?=intval(date("d",strtotime($res_sch->fields['SCHEDULE_DATE'])))?>,<?=intval(date("H",strtotime($res_sch->fields['END_TIME'])))?>,<?=intval(date("i",strtotime($res_sch->fields['END_TIME'])))?>,1,1),
					//className: 'bg-warning'
				},
				<? $res_sch->MoveNext();
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
				displayEventEnd: true,
				displayEventTime: true,
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
	
	function generate_pdf(){
		jQuery(document).ready(function($) {
			if(document.getElementById('sd').value != '' && document.getElementById('ed').value != '') {
				window.open("student_schedule_calendar_view_pdf?id=<?=$_GET['id']?>&eid=<?=$_GET['eid']?>&sd="+document.getElementById('sd').value+"&ed="+document.getElementById('ed').value)
			} else {
				alert('Please Select First and Last Date')
			}
		});
	}
	</script>
</body>

</html>