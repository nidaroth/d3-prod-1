<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/enrollment.php");
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
	<title><?=MNU_COURSE_ENROLLMENT ?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_COURSE_ENROLLMENT?></h4>
                    </div>
                </div>	
				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
								<div class="col-md-12">
									<table data-toggle="table" data-mobile-responsive="true" class="table-striped" >
										<thead>
											<tr>
												<th ><?=TERM?></th>
												<th ><?=COURSE?></th>
												<th ><?=COURSE_INFO?></th>
												<th ><?=REGISTRATION_MESSAGE?></th>
												<th ><?=ACTION?></th>
											</tr>
										</thead>
										<tbody>
											<? $res_course = $db->Execute("select S_COURSE_OFFERING.PK_COURSE_OFFERING, COURSE_CODE, SESSION, SESSION_NO, COURSE_DESCRIPTION, UNITS, FA_UNITS, CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) AS INSTRUCTOR, S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT, COURSE_DESCRIPTION, S_COURSE_OFFERING.CLASS_SIZE, ROOM_NO,UNITS, S_STUDENT_COURSE.PK_COURSE_OFFERING, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1  from 
											S_STUDENT_COURSE 
											LEFT JOIN S_TERM_MASTER On S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER 
											LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
											LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM
											LEFT JOIN M_SESSION On M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
											LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
											LEFT JOIN S_COURSE_OFFERING_SCHEDULE ON S_COURSE_OFFERING_SCHEDULE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING 
											LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR 
											WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]'
											ORDER BY BEGIN_DATE ASC, COURSE_CODE ASC");
											
											while (!$res_course->EOF) {  
												$PK_STUDENT_COURSE 		= $res_course->fields['PK_STUDENT_COURSE']; 
												$PK_STUDENT_ENROLLMENT  = $res_course->fields['PK_STUDENT_ENROLLMENT']; 
												$PK_COURSE_OFFERING  	= $res_course->fields['PK_COURSE_OFFERING']; 
												
												$res = $db->Execute("SELECT  * FROM S_COURSE_OFFERING_SCHEDULE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
												$SUNDAY	 	= $res->fields['SUNDAY'];
												$MONDAY 	= $res->fields['MONDAY'];
												$TUESDAY 	= $res->fields['TUESDAY'];
												$WEDNESDAY 	= $res->fields['WEDNESDAY'];
												$THURSDAY 	= $res->fields['THURSDAY'];
												$FRIDAY 	= $res->fields['FRIDAY'];
												$SATURDAY 	= $res->fields['SATURDAY'];
												
												$SCHEDULE_ARR = array();
												if($SUNDAY == 1) {
													$SUN_START_TIME = $res->fields['SUN_START_TIME'];
													$SUN_END_TIME 	= $res->fields['SUN_END_TIME'];
													$SUN_HOURS 		= $res->fields['SUN_HOURS'];
													
													if($SUN_START_TIME != '00:00:00')
														$SUN_START_TIME = date("h:i A",strtotime($SUN_START_TIME));
														
													if($SUN_END_TIME != '00:00:00')
														$SUN_END_TIME = date("h:i A",strtotime($SUN_END_TIME));
														
													$time = $SUN_START_TIME.' to '.$SUN_END_TIME;
													$SCHEDULE_ARR[$time] = $SCHEDULE_ARR[$time].'Su';
												}
												
												if($MONDAY == 1) {
													$MON_START_TIME = $res->fields['MON_START_TIME'];
													$MON_END_TIME 	= $res->fields['MON_END_TIME'];
													$MON_HOURS 		= $res->fields['MON_HOURS'];
													
													if($MON_START_TIME != '00:00:00')
														$MON_START_TIME = date("h:i A",strtotime($MON_START_TIME));
														
													if($MON_END_TIME != '00:00:00')
														$MON_END_TIME = date("h:i A",strtotime($MON_END_TIME));
														
													$time = $MON_START_TIME.' to '.$MON_END_TIME;
													$SCHEDULE_ARR[$time] = $SCHEDULE_ARR[$time].'M';
												}
												
												if($TUESDAY == 1) {
													$TUE_START_TIME = $res->fields['TUE_START_TIME'];
													$TUE_END_TIME 	= $res->fields['TUE_END_TIME'];
													$TUE_HOURS 		= $res->fields['TUE_HOURS'];
													
													if($TUE_START_TIME != '00:00:00')
														$TUE_START_TIME = date("h:i A",strtotime($TUE_START_TIME));
														
													if($TUE_END_TIME != '00:00:00')
														$TUE_END_TIME = date("h:i A",strtotime($TUE_END_TIME));
														
													$time = $TUE_START_TIME.' to '.$TUE_END_TIME;
													$SCHEDULE_ARR[$time] = $SCHEDULE_ARR[$time].'Tu';
												}
												
												if($WEDNESDAY == 1) {
													$WED_START_TIME = $res->fields['WED_START_TIME'];
													$WED_END_TIME 	= $res->fields['WED_END_TIME'];
													$WED_HOURS 		= $res->fields['WED_HOURS'];
													
													if($WED_START_TIME != '00:00:00')
														$WED_START_TIME = date("h:i A",strtotime($WED_START_TIME));
														
													if($WED_END_TIME != '00:00:00')
														$WED_END_TIME = date("h:i A",strtotime($WED_END_TIME));
														
													$time = $WED_START_TIME.' to '.$WED_END_TIME;
													$SCHEDULE_ARR[$time] = $SCHEDULE_ARR[$time].'W';
												}
												
												if($THURSDAY == 1) {
													$THU_START_TIME = $res->fields['THU_START_TIME'];
													$THU_END_TIME 	= $res->fields['THU_END_TIME'];
													$THU_HOURS 		= $res->fields['THU_HOURS'];
													
													if($THU_START_TIME != '00:00:00')
														$THU_START_TIME = date("h:i A",strtotime($THU_START_TIME));
														
													if($THU_END_TIME != '00:00:00')
														$THU_END_TIME = date("h:i A",strtotime($THU_END_TIME));
														
													$time = $THU_START_TIME.' to '.$THU_END_TIME;
													$SCHEDULE_ARR[$time] = $SCHEDULE_ARR[$time].'Th';
												} 

												if($FRIDAY == 1) {
													$FRI_START_TIME = $res->fields['FRI_START_TIME'];
													$FRI_END_TIME 	= $res->fields['FRI_END_TIME'];
													$FRI_HOURS 		= $res->fields['FRI_HOURS'];
													
													if($FRI_START_TIME != '00:00:00')
														$FRI_START_TIME = date("h:i A",strtotime($FRI_START_TIME));
														
													if($FRI_END_TIME != '00:00:00')
														$FRI_END_TIME = date("h:i A",strtotime($FRI_END_TIME));
														
													$time = $FRI_START_TIME.' to '.$FRI_END_TIME;
													$SCHEDULE_ARR[$time] = $SCHEDULE_ARR[$time].'F';
												}
												
												if($SATURDAY == 1) {
													$SAT_START_TIME = $res->fields['SAT_START_TIME'];
													$SAT_END_TIME 	= $res->fields['SAT_END_TIME'];
													$SAT_HOURS 		= $res->fields['SAT_HOURS'];
													
													if($SAT_START_TIME != '00:00:00')
														$SAT_START_TIME = date("h:i A",strtotime($SAT_START_TIME));
														
													if($SAT_END_TIME != '00:00:00')
														$SAT_END_TIME = date("h:i A",strtotime($SAT_END_TIME));
														
													$time = $SAT_START_TIME.' to '.$SAT_END_TIME;
													$SCHEDULE_ARR[$time] = $SCHEDULE_ARR[$time].'SA';
												} ?>
												<tr >
													<td>
														<?=$res_course->fields['BEGIN_DATE_1'] ?>
													</td>
													<td >
														<?=$res_course->fields['COURSE_CODE'].' ('. substr($res_course->fields['SESSION'],0,1).' - '. $res_course->fields['SESSION_NO'].')<br />'.$res_course->fields['COURSE_DESCRIPTION'] ?>
													</td>
													<td>
														<div class="row">
															<div class="col-sm-3"><?=CLASS_SIZE ?>:</div>
															<div class="col-sm-9"><?=$res_course->fields['CLASS_SIZE']?></div>
														</div>
														<div class="row">
															<div class="col-sm-3"><?=INSTRUCTOR ?>:</div>
															<div class="col-sm-9"><?=$res_course->fields['INSTRUCTOR']?></div>
														</div>
														<div class="row">
															<div class="col-sm-3"><?=ROOM ?>:</div>
															<div class="col-sm-9"><?=$res_course->fields['ROOM_NO']?></div>
														</div>
														<div class="row">
															<div class="col-sm-3"><?=SCHEDULE ?>:</div>
															<div class="col-sm-9"> 
																<? foreach($SCHEDULE_ARR as $key => $value)
																	echo $value.'  '.$key.'<br />';?>
															</div>
														</div>
														<div class="row">
															<div class="col-sm-3"><?=UNITS ?>:</div>
															<div class="col-sm-9"<?=$res_course->fields['UNITS']?></div>
														</div>
														
													</td>
													<td>
														
													</td>
													<td>
														
													</td>
												</tr>
											<?	$res_course->MoveNext();
											} ?>
										</tbody>
									</table>
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
	
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
</body>
</html>