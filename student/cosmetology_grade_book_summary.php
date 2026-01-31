<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/cosmetology_grade_book_summary.php");
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
	<title><?=MNU_COSMETOLOGY_GRADE_BOOK_SUMMARY?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
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
                        <h4 class="text-themecolor">
							<?=MNU_COSMETOLOGY_GRADE_BOOK_SUMMARY?>
							<!--<a target="_blank" href="cosmetology_grade_book_labs_pdf" class="btn pdf-color btn-circle" style="padding:0" ><i class="mdi mdi-file-pdf" style="font-size: 27px;" ></i> </a>-->
						</h4>
                    </div>
                </div>	
				
				<form class="floating-labels" method="get" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="card-group">
						<div class="card">
							<div class="card-body">
							
								<div class="row" style="padding-bottom:10px" >
									<div class="col-md-3">
										<b>Enrollment</b>
										<select id="t" name="t[]" multiple class="form-control" >
											<? $res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
											while (!$res_type->EOF) { 
												$selected = "";
												if(!empty($_GET)){
													foreach($_GET['t'] as $t){
														if($t == $res_type->fields['PK_STUDENT_ENROLLMENT']) {
															$selected = "selected";
															break;
														}
													}
												} ?>
												<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT'] ?>" <?=$selected ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['STUDENT_STATUS']?></option>
											<?	$res_type->MoveNext();
											} ?>
										</select>
									</div>
									<div class="col-md-2">
										<br />
										<button type="submit" class="btn waves-effect waves-light btn-info"><?=RUN?></button>
									</div>
								</div>
								
								<? $cond1 = "";
								$cond2 = "";
								$cond3 = "";
								$cond4 = "";
								if(!empty($_GET)){
									$PK_STUDENT_ENROLLMENT = implode(",",$_GET['t']);
									$cond1 = " AND S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) ";
									$cond2 = " AND S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) ";
									$cond3 = " AND S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) ";
									$cond4 = " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) ";
								} ?>
								<div class="row">
									<div class="col-md-12">
										<table class="table-striped table table-hover" style="width:40%" >
											<tr >
												<td style="width:50%" ><?=ACCUMULATIVE_GPA?></td>
												<td style="width:50%" >
													<div style="text-align:right" >
														<? $res_grade = $db->Execute("select SUM(POINTS_REQUIRED) as POINTS_REQUIRED, SUM(POINTS_COMPLETED) as POINTS_COMPLETED from S_STUDENT_PROGRAM_GRADE_BOOK_INPUT LEFT JOIN M_GRADE_BOOK_CODE ON M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE LEFT JOIN M_GRADE_BOOK_TYPE ON M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND COMPLETED_DATE != '0000-00-00' $cond1 ");
														$per = $res_grade->fields['POINTS_COMPLETED'] / $res_grade->fields['POINTS_REQUIRED'] * 100; 
														echo number_format_value_checker($per,2).' %'?>
													</div>
												</td>
											</tr>
										</table>
										<br /><br />
										
										<? $res_course_schedule = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME) as STUD_NAME, IF(S_STUDENT_SCHEDULE.SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%m/%d/%Y'),'') AS SCHEDULE_DATE, IF(S_STUDENT_SCHEDULE.END_TIME != '00:00:00', DATE_FORMAT(S_STUDENT_SCHEDULE.END_TIME,'%h:%i %p'),'') AS END_TIME, IF(S_STUDENT_SCHEDULE.START_TIME != '00:00:00', DATE_FORMAT(S_STUDENT_SCHEDULE.START_TIME,'%h:%i %p'),'') AS START_TIME, S_STUDENT_SCHEDULE.HOURS, COURSE_CODE, SCHEDULE_TYPE, S_STUDENT_ATTENDANCE.COMPLETED AS COMPLETED_1, IF(S_STUDENT_ATTENDANCE.COMPLETED = 1,'Y','') as COMPLETED , M_ATTENDANCE_CODE.CODE AS ATTENDANCE_CODE, SESSION, SESSION_NO, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE, PK_STUDENT_ATTENDANCE,ATTENDANCE_HOURS from 

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
										S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond2 ");
										$TOTAL_HOURS 		= 0;
										$ATTENDANCE_HOURS 	= 0;
										while (!$res_course_schedule->EOF) { 
											 
											if($res_course_schedule->fields['COMPLETED_1'] == 1 || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2){
												$ATTENDANCE_HOURS 	+= $res_course_schedule->fields['ATTENDANCE_HOURS'];
											}
											if(($res_course_schedule->fields['ATTENDANCE_CODE'] != 'I' && $res_course_schedule->fields['COMPLETED_1'] == 1) || $res_course_schedule->fields['PK_SCHEDULE_TYPE'] == 2)
												$TOTAL_HOURS += $res_course_schedule->fields['HOURS'];
												
											$res_course_schedule->MoveNext();
										} ?>
										<table class="table-striped table table-hover" style="width:40%">
											<tr >
												<td><?=SCHEDULED_HOURS?></td>
												<td>
													<div style="text-align:right" >
														<?=number_format_value_checker($TOTAL_HOURS,2) ?>
													</div>
												</td>
											</tr>
											<tr >
												<td><?=ATTENDED_HOUR?></td>
												<td>
													<div style="text-align:right" >
														<?=number_format_value_checker($ATTENDANCE_HOURS,2) ?>
													</div>
												</td>
											</tr>
											<tr >
												<td><?=ATTENDANCE_PERCENTAGE?></td>
												<td>
													<div style="text-align:right" >
														<?=number_format_value_checker(($ATTENDANCE_HOURS /$TOTAL_HOURS * 100),2).' %' ?>
													</div>
												</td>
											</tr>
										</table>
										<br /><br />
										
										<table class="table-striped table table-hover" style="width:40%">
											<tr >
												<td><?=TRANSFER_HOURS?></td>
												<td>
													<div style="text-align:right" >
														<? $res_trans = $db->Execute("SELECT IFNULL(SUM(HOUR),0) as HOUR FROM S_STUDENT_CREDIT_TRANSFER WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' $cond3 ");
														$TRANSFER_HOURS = $res_trans->fields['HOUR'];
														echo number_format_value_checker($TRANSFER_HOURS,2);
														?>
													</div>
												</td>
											</tr>
											<tr >
												<td><?=TOTAL_REQUIRED_HOURS?></td>
												<td>
													<div style="text-align:right" >
														<? $res_prog = $db->Execute("SELECT SUM(HOURS) as HOURS FROM S_STUDENT_ENROLLMENT, M_CAMPUS_PROGRAM WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM $cond4 "); 
														$TOTAL_REQUIRED_HOURS = $res_prog->fields['HOURS'];
														echo number_format_value_checker($TOTAL_REQUIRED_HOURS,2); ?>
													</div>
												</td>
											</tr>
											<tr >
												<td><?=TOTAL_HOURS_REMAINING?></td>
												<td>
													<div style="text-align:right" >
														<? $TOTAL_HOURS_REMAINING = $TOTAL_REQUIRED_HOURS - $ATTENDANCE_HOURS - $TRANSFER_HOURS;
														echo number_format_value_checker($TOTAL_HOURS_REMAINING,2); ?>
													</div>
												</td>
											</tr>
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
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#t').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Enrollments',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: 'Enrollment selected'
		});
	});
	</script>
</body>
</html>