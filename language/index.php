<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/dashboard.php");

//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index.php");
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
	<link href="../backend_assets/node_modules/calendar/dist/fullcalendar.css" rel="stylesheet" />
	<style>
	.fc-month-view span.fc-title{
		white-space: normal;
	}
	</style>
	<title>Dashboard | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=DASHBOARD_PAGE_TITLE?></h4>
                    </div>
                </div>
				<? $res = $db->Execute("SELECT * FROM Z_ANNOUNCEMENT WHERE ACTIVE = 1 ");  
				if($res->RecordCount() > 0) { ?>
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex no-block align-items-center">
                                        <div>
                                            <p class="text-muted"><?=ANNOUNCEMENT?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                   <a href="announcement.php">
										<? if($_SESSION['PK_LANGUAGE'] == 1)
											echo $res->fields['SHORT_DESC_ENG']; 
										else 
											echo $res->fields['SHORT_DESC_SPA'];  ?>
								   </a>
                                </div>
                            </div>
                        </div>
                    </div>
				</div>
				<? } ?>
				
                <div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex no-block align-items-center">
                                        <div>
                                            <h3><i class="icon-screen-desktop"></i></h3>
                                            <p class="text-muted"><?=NEW_LEADS?></p>
                                        </div>
                                        <div class="ml-auto">
                                            <h2 class="counter text-primary">23</h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 85%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex no-block align-items-center">
                                        <div>
                                            <h3><i class="icon-note"></i></h3>
                                            <p class="text-muted"><?=NEW_LEADS?></p>
                                        </div>
                                        <div class="ml-auto">
                                            <h2 class="counter text-cyan">169</h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="progress">
                                        <div class="progress-bar bg-cyan" role="progressbar" style="width: 85%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex no-block align-items-center">
                                        <div>
                                            <h3><i class="icon-doc"></i></h3>
                                            <p class="text-muted"><?=QUALIFIED_LEADS?></p>
                                        </div>
                                        <div class="ml-auto">
                                            <h2 class="counter text-purple">157</h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="progress">
                                        <div class="progress-bar bg-purple" role="progressbar" style="width: 85%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex no-block align-items-center">
                                        <div>
                                            <h3><i class="icon-bag"></i></h3>
                                            <p class="text-muted"><?=NEW_STUDENTS?></p>
                                        </div>
                                        <div class="ml-auto">
                                            <h2 class="counter text-success">431</h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 85%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				
                <div class="row">
                    <!-- Column -->
                    <div class="col-lg-8 col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex m-b-40 align-items-center no-block">
                                    <h5 class="card-title "><?=YEARLY_TUITIONS?></h5>
                                    <div class="ml-auto">
                                        <ul class="list-inline font-12">
                                            <li><i class="fa fa-circle text-cyan"></i> Course 1</li>
                                            <li><i class="fa fa-circle text-primary"></i> Course 2</li>
                                            <li><i class="fa fa-circle text-purple"></i> Course 3</li>
                                        </ul>
                                    </div>
                                </div>
                                <div id="morris-area-chart" style="height: 340px;"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Column -->
                    <div class="col-lg-4 col-md-12">
                        <div class="row">
                            <!-- Column -->
                            <div class="col-md-12">
                                <div class="card bg-cyan text-white">
                                    <div class="card-body ">
                                        <div class="row weather">
                                            <div class="col-6 m-t-40">
                                                <h3>&nbsp;</h3>
                                                <div class="display-4">73<sup>°F</sup></div>
                                                <p class="text-white">Los Angeles</p>
                                            </div>
                                            <div class="col-6 text-right">
                                                <h1 class="m-b-"><i class="wi wi-day-cloudy-high"></i></h1>
                                                <b class="text-white">SUNNEY DAY</b>
                                                <p class="op-5"><?=date("M, d")?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Column -->
                            <div class="col-md-12">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <div id="myCarouse2" class="carousel slide" data-ride="carousel">
                                            <!-- Carousel items -->
                                            <div class="carousel-inner">
                                                <div class="carousel-item active">
                                                    <h4 class="cmin-height"><?=RELEVANT_NEWS?></h4>
                                                    <div class="d-flex no-block">
                                                        <span><img src="../backend_assets/images/users/1.jpg" alt="user" width="50" class="img-circle"></span>
                                                        <span class="m-l-10">
                                                    <h4 class="text-white m-b-0">John</h4>
                                                    <p class="text-white">Accounting</p>
                                                    </span>
                                                    </div>
                                                </div>
                                                <div class="carousel-item">
                                                    <h4 class="cmin-height"><?=RELEVANT_NEWS?></h4>
                                                    <div class="d-flex no-block">
                                                        <span><img src="../backend_assets/images/users/2.jpg" alt="user" width="50" class="img-circle"></span>
                                                        <span class="m-l-10">
                                                    <h4 class="text-white m-b-0">Doe</h4>
                                                    <p class="text-white">Admission</p>
                                                    </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Column -->
                        </div>
                    </div>
                </div>
             
                <div class="row">
                    <!-- Column -->

                    <div class="col-lg-8 col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex m-b-40 align-items-center no-block">
                                    <h5 class="card-title "><?=LEADS?></h5>
                                    <div class="ml-auto">
                                        <ul class="list-inline font-12">
                                            <li><i class="fa fa-circle text-cyan"></i> <?=NEW1 ?></li>
                                            <li><i class="fa fa-circle text-primary"></i> <?=QUALIFIED ?></li>
                                        </ul>
                                    </div>
                                </div>
                                <div id="morris-area-chart2" style="height: 340px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Column -->
                    <div class="col-lg-4 col-md-12">
                        <div class="row">
                            <!-- Column -->
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?=POTENTIAL_REVENUE?></h5>
                                        <div class="row">
                                            <div class="col-6  m-t-30">
                                                <h1 class="text-info">$647</h1>
                                                <p class="text-muted">APRIL 2017</p>
                                                <b>(150 Sales)</b> </div>
                                            <div class="col-6">
                                                <div id="sparkline2dash" class="text-right"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Column -->
                            <div class="col-md-12">
                                <div class="card bg-purple text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Visit Statastics</h5>
                                        <div class="row">
                                            <div class="col-6  m-t-30">
                                                <h1 class="text-white">$347</h1>
                                                <p class="light_op_text">APRIL 2017</p>
                                                <b class="text-white">(150 Sales)</b> </div>
                                            <div class="col-6">
                                                <div id="sales1" class="text-right"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Column -->
                        </div>
                    </div>
                </div>
				
				<div class="row">
					<? $task_date[] = " AND TASK_DATE < '".date("Y-m-d")."' ";
					$task_title[] = PAST_DUE; 
					
					$task_date[]  = " AND TASK_DATE = '".date("Y-m-d")."' ";
					$task_title[] = DUE_TODAY; 
					
					$task_date[]  = " AND TASK_DATE BETWEEN '".date("Y-m-d", strtotime("+1 days", strtotime(date("Y-m-d"))))."' AND '".date("Y-m-d", strtotime("+7 days", strtotime(date("Y-m-d"))))."'  ";
					$task_title[] = DUE_7; 
					
					$i = 0;
					foreach($task_date as $date_cond){ ?>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex no-block align-items-center">
                                    <div>
                                        <h5 class="card-title m-b-0"><?=$task_title[$i]?></h5>
                                    </div>
                                </div>
                                <div class="to-do-widget m-t-20" id="todo" style="height: 400px;position: relative;">
                                    <ul class="list-task todo-list list-group m-b-0" data-role="tasklist">
										<? $date = date("Y-m-d");
										$res_type = $db->Execute("select PK_STUDENT_ENROLLMENT,PK_STUDENT_TASK,TASK_TIME, TASK_TYPE,TASK_STATUS,NOTES ,IF(TASK_DATE = '0000-00-00', '',  DATE_FORMAT(TASK_DATE,'%m/%d/%Y')) AS TASK_DATE1,S_STUDENT_TASK.PK_STUDENT_MASTER, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE,'%m/%d/%Y')) AS FOLLOWUP_DATE, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS EMP_NAME , CONCAT(S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.LAST_NAME) AS STU_NAME FROM S_STUDENT_MASTER,S_STUDENT_TASK LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_TASK.PK_EMPLOYEE_MASTER JOIN M_TASK_TYPE ON M_TASK_TYPE.PK_TASK_TYPE = S_STUDENT_TASK.PK_TASK_TYPE LEFT JOIN M_TASK_STATUS ON M_TASK_STATUS.PK_TASK_STATUS = S_STUDENT_TASK.PK_TASK_STATUS WHERE S_STUDENT_TASK.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND COMPLETED = 0 AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_TASK.PK_STUDENT_MASTER $date_cond ORDER BY TASK_DATE ASC ");
										while (!$res_type->EOF) { 
											$TASK_TIME = '';
											if($res_type->fields['TASK_TIME'] != '00-00-00') 
												$TASK_TIME = date("h:i A", strtotime($res_type->fields['TASK_TIME'])); ?>
											<li class="list-group-item" data-role="task">
												<a href="student_task.php?sid=<?=$res_type->fields['PK_STUDENT_MASTER']?>&id=<?=$res_type->fields['PK_STUDENT_TASK']?>&eid=<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>&t=" target="_blank" >
												<span>
													<?=$res_type->fields['STU_NAME']?><br />
													<?=$res_type->fields['TASK_TYPE']?><br />
													<?=$res_type->fields['TASK_STATUS']?><br />
													<?=$res_type->fields['NOTES']?><br />
												</span> 
												<span class="badge badge-pill badge-danger float-right"><?=$res_type->fields['TASK_DATE1'].' '.$TASK_TIME?></span>
												</a>
											</li>
										<?	$res_type->MoveNext();
										} 
										if($res_type->RecordCount() == 0) { ?>
										<li class="list-group-item" data-role="task">
											<span>
												<?=NO_DUE?>
											</span> 
										</li>
										<? } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
					<? $i++;
					} ?>
				 </div>
                
				 <div class="card-group">
                    <div class="card">
                        <div class="card-body">
							<div class="row">
								<div class="col-md-12">
									<div class="card-body b-l calender-sidebar">
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
	 
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <!--morris JavaScript -->
    <script src="../backend_assets/node_modules/raphael/raphael-min.js"></script>
    <script src="../backend_assets/node_modules/morrisjs/morris.min.js"></script>
    <script src="../backend_assets/node_modules/jquery-sparkline/jquery.sparkline.min.js"></script>
    <!-- Chart JS -->
    <script>
        jQuery(document).ready(function($) {
            $('#chat, #msg, #comment, #todo').perfectScrollbar();
        });
		
		$(function () {
		"use strict";
		//This is for the Notification top right
	   /* $.toast({
				heading: 'Welcome to Elite admin'
				, text: 'Use the predefined ones, or specify a custom position object.'
				, position: 'top-right'
				, loaderBg: '#ff6849'
				, icon: 'info'
				, hideAfter: 3500
				, stack: 6
			})*/
			// Dashboard 1 Morris-chart
		Morris.Area({
			element: 'morris-area-chart'
			, data: [{
					period: '2010'
					, iphone: 50
					, ipad: 80
					, itouch: 20
			}, {
					period: '2011'
					, iphone: 130
					, ipad: 100
					, itouch: 80
			}, {
					period: '2012'
					, iphone: 80
					, ipad: 60
					, itouch: 70
			}, {
					period: '2013'
					, iphone: 70
					, ipad: 200
					, itouch: 140
			}, {
					period: '2014'
					, iphone: 180
					, ipad: 150
					, itouch: 140
			}, {
					period: '2015'
					, iphone: 105
					, ipad: 100
					, itouch: 80
			}
				, {
					period: '2016'
					, iphone: 250
					, ipad: 150
					, itouch: 200
			}]
			, xkey: 'period'
			, ykeys: ['iphone', 'ipad', 'itouch']
			, labels: ['Course 1', 'Course 2', 'Course 3']
			, pointSize: 3
			, fillOpacity: 0
			, pointStrokeColors: ['#00bfc7', '#fb9678', '#9675ce']
			, behaveLikeLine: true
			, gridLineColor: '#e0e0e0'
			, lineWidth: 3
			, hideHover: 'auto'
			, lineColors: ['#00bfc7', '#fb9678', '#9675ce']
			, resize: true
		});
		Morris.Area({
			element: 'morris-area-chart2'
			, data: [{
					period: '2010'
					, SiteA: 0
					, SiteB: 0
			, }, {
					period: '2011'
					, SiteA: 130
					, SiteB: 100
			, }, {
					period: '2012'
					, SiteA: 80
					, SiteB: 60
			, }, {
					period: '2013'
					, SiteA: 70
					, SiteB: 200
			, }, {
					period: '2014'
					, SiteA: 180
					, SiteB: 150
			, }, {
					period: '2015'
					, SiteA: 105
					, SiteB: 90
			, }
				, {
					period: '2016'
					, SiteA: 250
					, SiteB: 150
			, }]
			, xkey: 'period'
			, ykeys: ['SiteA', 'SiteB']
			, labels: ['<?=NEW1?>', '<?=QUALIFIED?>']
			, pointSize: 0
			, fillOpacity: 0.4
			, pointStrokeColors: ['#b4becb', '#01c0c8']
			, behaveLikeLine: true
			, gridLineColor: '#e0e0e0'
			, lineWidth: 0
			, smooth: false
			, hideHover: 'auto'
			, lineColors: ['#b4becb', '#01c0c8']
			, resize: true
		});
	});    
		// sparkline
		var sparklineLogin = function() { 
			$('#sales1').sparkline([20, 40, 30], {
				type: 'pie',
				height: '90',
				resize: true,
				sliceColors: ['#01c0c8', '#7d5ab6', '#ffffff']
			});
			$('#sparkline2dash').sparkline([6, 10, 9, 11, 9, 10, 12], {
				type: 'bar',
				height: '154',
				barWidth: '4',
				resize: true,
				barSpacing: '10',
				barColor: '#25a6f7'
			});
			
		};    
		var sparkResize;
 
        $(window).resize(function(e) {
            clearTimeout(sparkResize);
            sparkResize = setTimeout(sparklineLogin, 500);
        });
        sparklineLogin();

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
				<? $date_cond = " AND TASK_DATE <= '".date("Y-m-d", strtotime("+7 days", strtotime(date("Y-m-d"))))."' "; 
				$res_type = $db->Execute("select PK_STUDENT_ENROLLMENT,PK_STUDENT_TASK,TASK_TIME, TASK_TYPE,TASK_STATUS,NOTES ,TASK_DATE ,S_STUDENT_TASK.PK_STUDENT_MASTER, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE,'%m/%d/%Y')) AS FOLLOWUP_DATE, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS EMP_NAME , CONCAT(S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.LAST_NAME) AS STU_NAME FROM S_STUDENT_MASTER,S_STUDENT_TASK LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_TASK.PK_EMPLOYEE_MASTER JOIN M_TASK_TYPE ON M_TASK_TYPE.PK_TASK_TYPE = S_STUDENT_TASK.PK_TASK_TYPE LEFT JOIN M_TASK_STATUS ON M_TASK_STATUS.PK_TASK_STATUS = S_STUDENT_TASK.PK_TASK_STATUS WHERE S_STUDENT_TASK.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND COMPLETED = 0 AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_TASK.PK_STUDENT_MASTER $date_cond AND TASK_DATE != '0000-00-00' ORDER BY TASK_DATE ASC "); 
				while (!$res_type->EOF) { ?>
				{
					id: "student_task.php?sid=<?=$res_type->fields['PK_STUDENT_MASTER']?>&id=<?=$res_type->fields['PK_STUDENT_TASK']?>&eid=<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>&t=",
					title: '<?=$res_type->fields['STU_NAME'].' - '.$res_type->fields['TASK_TYPE'].' - '.$res_type->fields['TASK_STATUS']?>',
					start: new Date(<?=date("Y",strtotime($res_type->fields['TASK_DATE']))?>,<?=intval((date("m",strtotime($res_type->fields['TASK_DATE'])) - 1))?>,<?=intval(date("d",strtotime($res_type->fields['TASK_DATE'])))?>,<?=intval(date("h",strtotime($res_type->fields['TASK_TIME'])))?>,<?=intval(date("i",strtotime($res_type->fields['TASK_TIME'])))?>,1,1),
					end: new Date(<?=date("Y",strtotime($res_type->fields['TASK_DATE']))?>,<?=intval((date("m",strtotime($res_type->fields['TASK_DATE'])) - 1))?>,<?=intval(date("d",strtotime($res_type->fields['TASK_DATE'])))?>,<?=intval(date("h",strtotime($res_type->fields['TASK_TIME'])))?>,<?=(intval(date("i",strtotime($res_type->fields['TASK_TIME']))) + 30) ?>,1,1),
					//className: 'bg-warning'
				},
				<? $res_type->MoveNext();
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
	</script>
</body>

</html>