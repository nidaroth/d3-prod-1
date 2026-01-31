<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/attendance_summary.php");
require_once("../language/menu.php");

//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_USER_TYPE'] != 3 ){ 
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
	<title><?=MNU_ATTENDANCE_REVIEW?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_ATTENDANCE_REVIEW?>
						</h4>
                    </div>
                </div>	
				
				<form class="floating-labels" method="get" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="card-group">
						<div class="card">
							<div class="card-body">
								<div class="row">
									<div class="col-md-2">
										<input type="text" class="form-control date" style="margin-bottom:5px" id="st" name="st" placeholder="<?=START_DATE?>" value="<?=$_GET['st']?>" >
									</div>
									<div class="col-md-2">
										<input type="text" class="form-control date" style="margin-bottom:5px" id="et" name="et" placeholder="<?=END_DATE?>" value="<?=$_GET['et']?>" >
									</div>
									<div class="col-md-2">
										<button type="submit" class="btn waves-effect waves-light btn-info"><?=RUN?></button>
										
										<? if(!empty($_GET)){ ?>
											<a href="attendance_review_pdf?st=<?=$_GET['st']?>&et=<?=$_GET['et']?>" class="btn waves-effect waves-light btn-info" ><?=PDF?></a>
										<? } ?>
									</div>
								</div>
								<? if(!empty($_GET)){ ?>
								<div id="div_1"></div>
								<div class="row">
									<div class="col-md-12">
										<table data-toggle="table" data-mobile-responsive="true" class="table-striped" >
											<thead>
												<tr>
													<th ><?=COURSE?></th>
													<th ><?=CLASS_DATE?></th>
													<th ><?=SCHEDULED_START_TIME?></th>
													<th ><?=SCHEDULED_END_TIME?></th>
													<th ><div style="text-align:right" ><?=SCHEDULED_HOUR?></div></th>
													<th ><div style="text-align:right" ><?=CODE?></div></th>
													<th ><div style="text-align:right" ><?=ATTENDED_HOURS?></div></th>
												</tr>
											</thead>
											<tbody>
												<? $cond = "";
												$date_tange = "";
												if($_GET['st'] != '' && $_GET['et'] != '') {
													$FROM_DATE 	= date('Y-m-d',strtotime($_GET['st']));
													$TO_DATE 	= date('Y-m-d',strtotime($_GET['et']));
													$cond .= " AND DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%Y-%m-%d') BETWEEN '$FROM_DATE' AND '$TO_DATE' ";
													$date_tange = $_GET['st']." - ".$_GET['et'];
												} else if($_GET['st'] != ''){
													$FROM_DATE 	= date('Y-m-d',strtotime($_GET['st']));
													$cond .= " AND DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%Y-%m-%d') >= '$FROM_DATE'  ";
													$date_tange = "From ".$_GET['st'];
												} else if($_GET['et'] != ''){
													$TO_DATE 	= date('Y-m-d',strtotime($_GET['et']));
													$cond .= " AND DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%Y-%m-%d') <= '$TO_DATE'  ";
													$date_tange = "To ".$_GET['et'];
												}
												
												$query = "select CONCAT(LAST_NAME,', ',FIRST_NAME) as STUD_NAME, IF(S_STUDENT_SCHEDULE.SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%m/%d/%Y'),'') AS SCHEDULE_DATE, IF(S_STUDENT_SCHEDULE.END_TIME != '00:00:00', DATE_FORMAT(S_STUDENT_SCHEDULE.END_TIME,'%h:%i %p'),'') AS END_TIME, IF(S_STUDENT_SCHEDULE.START_TIME != '00:00:00', DATE_FORMAT(S_STUDENT_SCHEDULE.START_TIME,'%h:%i %p'),'') AS START_TIME, S_STUDENT_SCHEDULE.HOURS, COURSE_CODE, SCHEDULE_TYPE, S_STUDENT_ATTENDANCE.COMPLETED AS COMPLETED_1, IF(S_STUDENT_ATTENDANCE.COMPLETED = 1,'Y','') as COMPLETED , M_ATTENDANCE_CODE.CODE AS ATTENDANCE_CODE, SESSION, SESSION_NO, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE, PK_STUDENT_ATTENDANCE,ATTENDANCE_HOURS from 

												S_STUDENT_MASTER, S_STUDENT_SCHEDULE 
												LEFT JOIN M_SCHEDULE_TYPE ON M_SCHEDULE_TYPE.PK_SCHEDULE_TYPE = S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE
												LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
												LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
												LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
												LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
												LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE
												LEFT JOIN M_ATTENDANCE_CODE ON  M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE
												LEFT JOIN S_COURSE_OFFERING_SCHEDULE_DETAIL ON  S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL
												WHERE 
												S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND 
												S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
												S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ORDER BY S_STUDENT_SCHEDULE.SCHEDULE_DATE ASC, S_STUDENT_SCHEDULE.START_TIME ASC";
												$_SESSION['QUERY'] = $query;
												$res_course_schedule = $db->Execute($query);
												
												$TOTAL_HOURS 		= 0;
												$ATTENDANCE_HOURS 	= 0;
												while (!$res_course_schedule->EOF) { 
													 
													if($res_course_schedule->fields['COMPLETED_1'] == 1 || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2){
														$ATTENDANCE_HOURS 	+= $res_course_schedule->fields['ATTENDANCE_HOURS'];
													}
													if(($res_course_schedule->fields['ATTENDANCE_CODE'] != 'I' && $res_course_schedule->fields['COMPLETED_1'] == 1) || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2)
														$TOTAL_HOURS += $res_course_schedule->fields['HOURS'];
													?>
													<tr>
														<td>
															<?=$res_course_schedule->fields['COURSE_CODE'].' ('.$res_course_schedule->fields['SESSION'].' - '.$res_course_schedule->fields['SESSION_NO'].')' ?>
														</td>
														<td><?=$res_course_schedule->fields['SCHEDULE_DATE']; ?></td>
														<td><?=$res_course_schedule->fields['START_TIME']; ?></td>
														<td><?=$res_course_schedule->fields['END_TIME']; ?></td>
														
														<td><div style="text-align:right" ><?=$res_course_schedule->fields['HOURS']; ?></div></td>
														<td>
															<div style="text-align:right" >
															<? if($res_course_schedule->fields['COMPLETED_1'] == 1) echo $res_course_schedule->fields['ATTENDANCE_CODE']; else echo "-"; ?>
															</div>
														</td>
														<td>
															<div style="text-align:right" >
															<? if($res_course_schedule->fields['COMPLETED_1'] == 1) echo number_format_value_checker($res_course_schedule->fields['ATTENDANCE_HOURS'],2); else echo "-"; ?>
															</div>
														</td>
													</tr>
												<?	$res_course_schedule->MoveNext();
												} ?>
												<tr>
													<td ></td>
													<td ></td>
													<td ></td>
													<td ><?=TOTAL?></td>
													<td ><div style="text-align:right" ><?=$TOTAL_HOURS?></div></td>
													<td ><div style="text-align:right" ></div></td>
													<td ><div style="text-align:right" ><?=$ATTENDANCE_HOURS?></div></th>
												</tr>
											</tbody>
										</table>
									</div> 
								</div>
								<div id="div_2">
									<div class="row">
										<div class="col-md-2">
											<b><?=DATE_RANGE?></b>
										</div> 
										<div class="col-md-2">
											<?=$date_tange?>
										</div> 
									</div>
									<div class="row">
										<div class="col-md-2">
											<b><?=SCHEDULED_COMPLETED?></b>
										</div> 
										<div class="col-md-2">
											<?=$TOTAL_HOURS?>
										</div> 
									</div>
									<div class="row">
										<div class="col-md-2">
											<b><?=ATTENDED_COMPLETED?></b>
										</div> 
										<div class="col-md-2">
											<?=$ATTENDANCE_HOURS?>
										</div> 
									</div>
									<div class="row">
										<div class="col-md-2">
											<b><?=ATTENDED_PERCENTAGE?></b>
										</div> 
										<div class="col-md-2">
											<?=number_format_value_checker(($ATTENDANCE_HOURS /$TOTAL_HOURS * 100),2).' %' ?>
										</div> 
									</div>
									<div class="row">
										<div class="col-md-12">
											<br />
											Attendance Code "-" is future attendance and not included in totals
										</div> 
									</div>
								</div>
								<? } ?>
							</div>
						</div>
					</div>
				</form>
            </div>
        </div>
        <? require_once("footer.php"); ?>		
    </div>
    <? require_once("js.php"); ?>
	
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
		<? if(!empty($_GET)){ ?>
			document.getElementById('div_1').innerHTML = document.getElementById('div_2').innerHTML
		<? } ?>
	});
	</script>
</body>
</html>