<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/dashboard.php");
require_once("../language/instructor_calendar.php");
//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
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
	<title><?=INSTRUCTOR_CALENDAR_PAGE_TITLE?> | <?=$title?></title>
	<link href="../backend_assets/node_modules/calendar/dist/fullcalendar.css" rel="stylesheet" />
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=INSTRUCTOR_CALENDAR_PAGE_TITLE?></h4>
                    </div>
                </div>				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
								<form class="floating-labels w-100 m-t-40" method="get" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
									<div class="row">
										<div class="col-2 form-group">
											<input class="form-control date" type="text" value="<?=$_GET['sd']?>" id="st" name="sd">
											<span class="bar"></span> 
											<label for="sd"><?=SELECT_FIRST_DATE?></label>
										</div>
										<div class="col-2 form-group">
											<input class="form-control date" type="text" value="<?=$_GET['ed']?>" id="ed" name="ed">
											<span class="bar"></span> 
											<label for="ed"><?=SELECT_LAST_DATE?></label>
										</div>
										<div class="col-1 form-group text-right">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SHOW?></button>
										</div>
										<div class="col-1 form-group">
											<? if(!empty($_GET)){ ?>
												<a href="instructor_calendar_pdf?sd=<?=$_GET['sd']?>&ed=<?=$_GET['ed']?>" class="btn waves-effect waves-light btn-info" title="PDF">
													<?=PDF ?>
												</a>
											<? } ?>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<div class="card-body b-l calender-sidebar">
												<div id="calendar"></div>
											</div>
										</div>
									</div>
								</form>
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
	<? if(!empty($_GET)){ ?>
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
				<? $cond = "";
				if($_GET['sd'] != '' && $_GET['ed'] != '') {
					$SD = date("Y-m-d",strtotime($_GET['sd']));
					$ED = date("Y-m-d",strtotime($_GET['ed']));
					
					$cond = " AND SCHEDULE_DATE BETWEEN '$SD' AND '$ED' ";
				} else if($_GET['sd'] != '') {
					$SD = date("Y-m-d",strtotime($_GET['sd']));
					
					$cond = " AND SCHEDULE_DATE >= '$SD' ";
				} else if($_GET['ed'] != '') {
					$ED = date("Y-m-d",strtotime($_GET['ed']));
					
					$cond = " AND SCHEDULE_DATE <= '$ED' ";
				}
				
				$res_sch = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL, IF(SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(SCHEDULE_DATE,'%m/%d/%Y'),'') AS SCHEDULE_DATE1, START_TIME, END_TIME, S_COURSE_OFFERING_SCHEDULE_DETAIL.HOURS, ROOM_NO, COURSE_CODE, ROOM_DESCRIPTION, SESSION, SESSION_NO from 

				S_COURSE_OFFERING 
				LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING 
				LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
				LEFT JOIn M_SESSION On M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
				, S_COURSE_OFFERING_SCHEDULE_DETAIL 
				LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_CAMPUS_ROOM 

				WHERE 
				S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
				(INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND
				S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING  $cond 
				ORDER BY SCHEDULE_DATE ASC ");
				while (!$res_sch->EOF) { ?>
				{
					id: "",
					title: '<?=$res_sch->fields['COURSE_CODE'].'\n('.$res_sch->fields['SESSION'].' - '.$res_sch->fields['SESSION_NO'].')'.'\n[Room # '.$res_sch->fields['ROOM_NO'].']' ?>',
					start: new Date(<?=date("Y",strtotime($res_sch->fields['SCHEDULE_DATE1']))?>,<?=intval((date("m",strtotime($res_sch->fields['SCHEDULE_DATE1'])) - 1))?>,<?=intval(date("d",strtotime($res_sch->fields['SCHEDULE_DATE1'])))?>,<?=intval(date("H",strtotime($res_sch->fields['START_TIME'])))?>,<?=intval(date("i",strtotime($res_sch->fields['START_TIME'])))?>,1,1),
					end: new Date(<?=date("Y",strtotime($res_sch->fields['SCHEDULE_DATE1']))?>,<?=intval((date("m",strtotime($res_sch->fields['SCHEDULE_DATE1'])) - 1))?>,<?=intval(date("d",strtotime($res_sch->fields['SCHEDULE_DATE1'])))?>,<?=intval(date("H",strtotime($res_sch->fields['END_TIME'])))?>,<?=intval(date("i",strtotime($res_sch->fields['END_TIME'])))?>,1,1),
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
		
		<? if($_GET['sd'] != '' ) { 
			$year  = date('Y',strtotime($_GET['sd']));
			$month = date('m',strtotime($_GET['sd'])) - 1; ?>
			jQuery('#calendar').fullCalendar('gotoDate', new Date('<?=$year?>', <?=$month?>));
		<? } else if($_GET['ed'] != '' ) {  ?>
			$year  = date('Y',strtotime($_GET['ed']));
			$month = date('m',strtotime($_GET['ed'])) - 1; ?>
			jQuery('#calendar').fullCalendar('gotoDate', new Date('<?=$year?>', <?=$month?>));
		<? } ?>
	}(window.jQuery);
	<? } ?>
	</script>
</body>
</html>