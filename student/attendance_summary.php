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
	<title><?=ATTENDANCE_SUMMARY_PAGE_TITLE?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=ATTENDANCE_SUMMARY_PAGE_TITLE?></h4>
                    </div>
                </div>	
				
				<form class="floating-labels" method="get" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="card-group">
						<div class="card">
							<div class="card-body">
								<div class="row">
									<div class="col-md-2">
										<!--<select id="t" name="t" class="form-control" >
											<option value="" >All Terms</option>
											<? /*$res_type = $db->Execute("select S_STUDENT_COURSE.PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 from S_STUDENT_COURSE, S_TERM_MASTER WHERE S_STUDENT_COURSE.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER GROUP BY S_STUDENT_COURSE.PK_TERM_MASTER ORDER BY BEGIN_DATE ASC");
											while (!$res_type->EOF) { ?>
												<option value="<?=$res_type->fields['PK_TERM_MASTER'] ?>" <? if($_GET['t'] == $res_type->fields['PK_TERM_MASTER']) echo "selected"; ?> ><?=$res_type->fields['BEGIN_DATE_1'] ?></option>
											<?	$res_type->MoveNext();
											}*/ ?>
										</select>-->
										<select id="t" name="t" class="form-control" >
											<option value="" >All Enrollments</option>
											<? $res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
											while (!$res_type->EOF) { ?>
												<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT'] ?>" <? if($_GET['t'] == $res_type->fields['PK_STUDENT_ENROLLMENT']) echo "selected"; ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['STUDENT_STATUS']?></option>
											<?	$res_type->MoveNext();
											} ?>
										</select>
									</div>
									<div class="col-md-4">
										<button type="submit" class="btn waves-effect waves-light btn-info"><?=RUN?></button>
										<a href="../school/attendance_report" class="btn waves-effect waves-light btn-info" ><?=PDF?></a>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-12">
										<table data-toggle="table" data-mobile-responsive="true" class="table-striped" >
											<thead>
												<tr>
													<th ><?=TERM?></th>
													<th ><?=COURSE?></th>
													<th ><?=DESCRIPTION?></th>
													<th ><div style="padding-top: 11px;width:100%;text-align:right" ><?=SCHEDULED_TOTAL?></div></th>
													<th ><div style="padding-top: 11px;width:100%;text-align:right" ><?=SCHEDULED_TO_DATE?></div></th>
													<th ><div style="padding-top: 11px;width:100%;text-align:right" ><?=ATTENDED_TO_DATE?></div></th>
													<th ><div style="padding-top: 11px;width:100%;text-align:right" ><?=ATTENDED_PERCENTAGE?></div></th>
												</tr>
											</thead>
											<tbody>
												<? $cond = "";
												if($_GET['t'] != '')
													$cond = " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$_GET[t]' ";
												
												$res_course = $db->Execute("select IF(SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(SCHEDULE_DATE,'%m/%d/%Y'),'') AS SCHEDULE_DATE, IF(END_TIME != '00:00:00', DATE_FORMAT(END_TIME,'%h:%i %p'),'') AS END_TIME, IF(START_TIME != '00:00:00', DATE_FORMAT(START_TIME,'%h:%i %p'),'') AS START_TIME, COURSE_CODE, SCHEDULE_TYPE, IF(S_STUDENT_ATTENDANCE.COMPLETED = 1,'Y','') as COMPLETED , M_ATTENDANCE_CODE.CODE AS ATTENDANCE_CODE, SESSION, SESSION_NO ,SUM(ATTENDANCE_HOURS) as ATTENDANCE_HOURS, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, COURSE_DESCRIPTION, S_STUDENT_COURSE.PK_COURSE_OFFERING, S_STUDENT_COURSE.PK_STUDENT_COURSE  from 

												S_STUDENT_SCHEDULE 
												LEFT JOIN M_SCHEDULE_TYPE ON M_SCHEDULE_TYPE.PK_SCHEDULE_TYPE = S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE
												LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
												LEFT JOIN S_TERM_MASTER On S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER 
												LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
												LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION
												LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
												LEFT JOIN S_STUDENT_ATTENDANCE ON  S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE
												LEFT JOIN M_ATTENDANCE_CODE ON  M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE

												WHERE 
												S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]'  $cond GROUP BY S_STUDENT_COURSE.PK_COURSE_OFFERING ORDER BY SCHEDULE_DATE ASC, START_TIME ASC ");
																								
												$tot_sch 		= 0;
												$tot_comp_sch 	= 0;
												$tot_att_hour 	= 0;
												$tot_percentage = 0;
												$count 			= 0;
												while (!$res_course->EOF) {
													$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING']; 
													$PK_STUDENT_COURSE	= $res_course->fields['PK_STUDENT_COURSE']; 
													
													$res_tot_sch = $db->Execute("SELECT IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS),0) AS HOURS FROM S_STUDENT_SCHEDULE WHERE  S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

													$res_tot_comp_sch = $db->Execute("SELECT IFNULL(SUM(S_STUDENT_SCHEDULE.HOURS),0) AS HOURS FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE  S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE  AND COMPLETED = 1 AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE !=  7");
													
													$res = $db->Execute("SELECT IFNULL(SUM(ATTENDANCE_HOURS),0) AS ATTENDANCE_HOURS FROM S_STUDENT_ATTENDANCE,S_STUDENT_SCHEDULE WHERE S_STUDENT_SCHEDULE.PK_STUDENT_COURSE = '$PK_STUDENT_COURSE'  AND S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE = S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE AND (COMPLETED = 1 OR PK_SCHEDULE_TYPE = 2) AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE !=  7 ");
													
													$tot_sch 		+= $res_tot_sch->fields['HOURS'];
													$tot_comp_sch 	+= $res_tot_comp_sch->fields['HOURS']; 
													$tot_att_hour	+= $res->fields['ATTENDANCE_HOURS']; 
													
													if($res_tot_comp_sch->fields['HOURS'] > 0) {
														$percentage = $res->fields['ATTENDANCE_HOURS'] / $res_tot_comp_sch->fields['HOURS'] * 100; 
													} else
														$percentage = 0; 
													
													if($percentage > 0)	
														$count++;
														
													$tot_percentage += $percentage; ?>
													<tr >
														<td>
															<?=$res_course->fields['BEGIN_DATE_1'] ?>
														</td>
														<td >
															<?=$res_course->fields['COURSE_CODE'].' ('.substr($res_course->fields['SESSION'],0,1).' - '. $res_course->fields['SESSION_NO'].')' ?>
														</td>
														<td >
															<?=$res_course->fields['COURSE_DESCRIPTION'] ?>
														</td>
														<td>
															<div style="padding-top: 11px;width:100%;text-align:right" >
																<?=number_format_value_checker($res_tot_sch->fields['HOURS'],2)?>
															</div>
														</td>
														<td>
															<div style="padding-top: 11px;width:100%;text-align:right" >
																<?=number_format_value_checker($res_tot_comp_sch->fields['HOURS'],2)?>
															</div>
														</td>
														<td>
															<div style="padding-top: 11px;width:100%;text-align:right" >
																<?=number_format_value_checker($res->fields['ATTENDANCE_HOURS'],2)?>
															</div>
														</td>
														<td>
															<div style="padding-top: 11px;width:100%;text-align:right" >
																<?=number_format_value_checker($percentage,2)?>%
															</div>
														</td>
													</tr>
												<?	$res_course->MoveNext();
												} ?>
												<tr>
													<td colspan="3"><div style="padding-top: 11px;width:100%;text-align:right" ><?=TOTAL?></div></td>
													<td><div style="padding-top: 11px;width:100%;text-align:right" ><?=number_format_value_checker($tot_sch,2)?></div></td>
													<td><div style="padding-top: 11px;width:100%;text-align:right" ><?=number_format_value_checker($tot_comp_sch,2)?></div></td>
													<td><div style="padding-top: 11px;width:100%;text-align:right" ><?=number_format_value_checker($tot_att_hour,2)?></div></td>
													<td><div style="padding-top: 11px;width:100%;text-align:right" ><?=number_format_value_checker(($tot_att_hour / $tot_comp_sch * 100),2)?>%</div></td>
												</tr>
											</tbody>
										</table>
									</div> 
								</div>
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
</body>
</html>