<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");

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
	<title><?=TAB_SCHEDULE?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=TAB_SCHEDULE?></h4>
                    </div>
                </div>	
				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
								<div class="col-md-12">
									<div class="row form-group">
										<div class="custom-control custom-radio col-md-2">
											<input type="radio" id="DETAIL_VIEW" name="VIEW" value="1" class="custom-control-input" onclick="change_view()" >
											<label class="custom-control-label" for="DETAIL_VIEW"><?=DETAIL_VIEW?></label>
										</div>
										<div class="custom-control custom-radio col-md-3">
											<input type="radio" id="SUMMARY_VIEW" name="VIEW" value="2" class="custom-control-input" checked onclick="change_view()" >
											<label class="custom-control-label" for="SUMMARY_VIEW"><?=SUMMARY_VIEW?></label>
										</div>
										<div class="custom-control custom-radio col-md-3">
											<input type="radio" id="CALENDAR_VIEW" name="VIEW" value="3" class="custom-control-input" onclick="change_view()" >
											<label class="custom-control-label" for="CALENDAR_VIEW"><?=CALENDAR_VIEW?></label>
										</div>
									</div>
								</div>
							</div>
							
							<div id="schedule_summary" >
								<div class="row">
									<div class="col-md-1" ><b><?=START_DATE?></b></div>
									<div class="col-md-1" ><b><?=END_DATE?></b></div>
									<div class="col-md-2"><b><?=COURSE?></b></div>
									<div class="col-md-1"><b><?=TOTAL_HOUR?></b></div>
									<div class="col-md-2"><b><?=ROOM?></b></div>
								</div>
								<hr />
								<? $res = $db->Execute("SELECT GROUP_CONCAT( PK_STUDENT_ENROLLMENT
SEPARATOR ',' ) AS PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' "); //DIAM-1664
								$PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
								
								$res_course_schedule = $db->Execute("select S_COURSE.HOURS, CONCAT(S_COURSE.COURSE_CODE, ' - ', S_COURSE.TRANSCRIPT_CODE) as COURSE_CODE,ROOM_NO,ROOM_DESCRIPTION , S_STUDENT_COURSE.PK_STUDENT_COURSE,S_COURSE_OFFERING_SCHEDULE.START_DATE, S_COURSE_OFFERING_SCHEDULE.END_DATE from 

								S_STUDENT_COURSE 
								LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING
								LEFT JOIN S_COURSE_OFFERING_SCHEDULE ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE.PK_COURSE_OFFERING
								LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM 
								LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 

								WHERE 
								S_STUDENT_COURSE.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND 
								S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT IN($PK_STUDENT_ENROLLMENT) AND 
								S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY S_COURSE_OFFERING_SCHEDULE.START_DATE ASC "); //DIAM-1664
								
										
								while (!$res_course_schedule->EOF) { 
									$PK_STUDENT_COURSE = $res_course_schedule->fields['PK_STUDENT_COURSE']; 
									//$res_course_min = $db->Execute("SELECT MIN(SCHEDULE_DATE) as START_DATE FROM S_STUDENT_SCHEDULE WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' "); 
									//$res_course_max = $db->Execute("SELECT MAX(SCHEDULE_DATE) as END_DATE FROM S_STUDENT_SCHEDULE WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' ");?>
									<div class="row">
										<div class="col-md-1">
											<? if($res_course_schedule->fields['START_DATE'] != '0000-00-00')
												echo date("m/d/Y",strtotime($res_course_schedule->fields['START_DATE'])); ?>
										</div>
										<div class="col-md-1">
											<? if($res_course_schedule->fields['END_DATE'] != '0000-00-00')
												echo date("m/d/Y",strtotime($res_course_schedule->fields['END_DATE'])); ?>
										</div>
										<div class="col-md-2"><?=$res_course_schedule->fields['COURSE_CODE']?></div>
										<div class="col-md-1" ><?=$res_course_schedule->fields['HOURS']?></div>
										<div class="col-md-2"><?=$res_course_schedule->fields['ROOM_NO'].' - '.$res_course_schedule->fields['ROOM_DESCRIPTION']?></div>
									</div>
									<hr />
								<?	$res_course_schedule->MoveNext();
								} ?>
							</div>
							<div id="schedule_detail" style="display:none"  >
								<div class="row">
									<div class="col-md-2"><b><?=COURSE?></b></div>
									<div class="col-md-1"><b><?=DATE?></b></div>
									<div class="col-md-1" ><b><?=DD?></b></div>
									<div class="col-md-1" ><b><?=START_TIME?></b></div>
									<div class="col-md-1" ><b><?=END_TIME?></b></div>
									<div class="col-md-1"><b><?=HOUR?></b></div>
									<div class="col-md-2"><b><?=ROOM?></b></div>
									<!--<div class="col-md-1"><b><?=COMPLETED?></b></div>-->
								</div>
								<hr />
								<? $res_course_schedule = $db->Execute("select IF(SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(SCHEDULE_DATE,'%m/%d/%Y'),'') AS SCHEDULE_DATE, START_TIME, END_TIME, S_STUDENT_SCHEDULE.HOURS, ROOM_NO, CONCAT(S_COURSE.COURSE_CODE, ' - ', S_COURSE.TRANSCRIPT_CODE) as COURSE_CODE, ROOM_DESCRIPTION from 

								S_STUDENT_SCHEDULE 
								LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE 
								LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
								LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_STUDENT_SCHEDULE.PK_CAMPUS_ROOM 
								LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 

								WHERE 
								S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND 
								S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT IN($PK_STUDENT_ENROLLMENT) AND 
								S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY SCHEDULE_DATE ASC, START_TIME ASC "); // DIAM-1664
										
								while (!$res_course_schedule->EOF) { ?>
									<div class="row">
										<div class="col-md-2"><?=$res_course_schedule->fields['COURSE_CODE']?></div>
										<div class="col-md-1"><?=$res_course_schedule->fields['SCHEDULE_DATE']?></div>
										<div class="col-md-1"><?=date("D",strtotime($res_course_schedule->fields['SCHEDULE_DATE']))?></div>
										<div class="col-md-1" >
											<? if($res_course_schedule->fields['START_TIME'] != '00:00:00')
												echo date("h:i A",strtotime($res_course_schedule->fields['START_TIME'])); ?>
										</div>
										<div class="col-md-1" >
											<? if($res_course_schedule->fields['END_TIME'] != '00:00:00')
												echo date("h:i A",strtotime($res_course_schedule->fields['END_TIME'])); ?>
										</div>
										<div class="col-md-1" ><?=$res_course_schedule->fields['HOURS']?></div>
										<div class="col-md-2"><?=$res_course_schedule->fields['ROOM_NO'].' - '.$res_course_schedule->fields['ROOM_DESCRIPTION']?></div>
									</div>
									<hr />
								<?	$res_course_schedule->MoveNext();
								} ?>
							</div>
                        </div>
                    </div>
				</div>				
            </div>
        </div>
        <? require_once("footer.php"); ?>		
    </div>
    <? require_once("js.php"); ?>
	
	<script type="text/javascript" >
	function change_view(){
		if(document.getElementById('DETAIL_VIEW').checked == true) {
			document.getElementById('schedule_summary').style.display = 'none';
			document.getElementById('schedule_detail').style.display  = 'block';
		} else if(document.getElementById('SUMMARY_VIEW').checked == true){
			document.getElementById('schedule_summary').style.display = 'block';
			document.getElementById('schedule_detail').style.display  = 'none';
		} else if(document.getElementById('CALENDAR_VIEW').checked == true){
			var w = 1200;
			var h = 550;
			// var id = common_id;
			var left = (screen.width/2)-(w/2);
			var top = (screen.height/2)-(h/2);
			var parameter = 'toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,width='+w+', height='+h+', top='+top+', left='+left;
			window.open('student_schedule_calendar_view','',parameter);
			return false;
		}
	}
	</script>
</body>
</html>
