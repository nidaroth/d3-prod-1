<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/non_scheduled_attendance.php");
require_once("function_attendance.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 && check_access('REGISTRAR_ACCESS') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	//Ticket # 670
	if(empty($_POST['SCHEDULED_HOUR'])){
		$_POST['SCHEDULED_HOUR']='0.00';
	}
	//Ticket # 670
	$PK_STUDENT_SCHEDULE = create_non_schedule($_POST['PK_STUDENT_SCHEDULE'], $_POST['PK_COURSE_OFFERING'],$_POST['SCHEDULE_DATE'],$_POST['START_TIME'],$_POST['END_TIME'],$_POST['SCHEDULED_HOUR'],$_POST['PK_STUDENT_MASTER'],$_POST['PK_STUDENT_ENROLLMENT'], 1,$_SESSION['PK_ACCOUNT'],$_SESSION['PK_USER']);

	attendance_entry('',1,$_GET['id'],$_POST['PK_STUDENT_MASTER'],$_POST['PK_STUDENT_ENROLLMENT'],$PK_STUDENT_SCHEDULE,$_POST['ATTENDANCE_HOURS'], $_POST['PK_ATTENDANCE_CODE'],$_SESSION['PK_ACCOUNT'],$_SESSION['PK_USER']);
	?>
	<script type="text/javascript">window.opener.go_to_ns(this)</script>
<? }
$res_ns = $db->Execute("select PK_STUDENT_ATTENDANCE, START_TIME, END_TIME, HOURS, ATTENDANCE_HOURS, PK_ATTENDANCE_CODE, S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT, S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE, S_STUDENT_SCHEDULE.SCHEDULE_DATE   
from 
S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_STUDENT_COURSE, S_STUDENT_MASTER, S_STUDENT_ENROLLMENT   
WHERE 
S_STUDENT_ATTENDANCE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE  = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
PK_STUDENT_ATTENDANCE = '$_GET[id]' AND 
S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND 
S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ");
if($res_ns->RecordCount() == 0) {
	header("location:../index");
	exit;
}

$PK_STUDENT_SCHEDULE 	= $res_ns->fields['PK_STUDENT_SCHEDULE'];
$PK_STUDENT_ENROLLMENT 	= $res_ns->fields['PK_STUDENT_ENROLLMENT'];
$PK_STUDENT_MASTER 		= $res_ns->fields['PK_STUDENT_MASTER'];
$SCHEDULE_DATE 			= $res_ns->fields['SCHEDULE_DATE'];
$START_TIME 			= $res_ns->fields['START_TIME'];
$END_TIME 				= $res_ns->fields['END_TIME'];
$HOURS 					= $res_ns->fields['HOURS'];
$ATTENDANCE_HOURS 		= $res_ns->fields['ATTENDANCE_HOURS'];
$PK_ATTENDANCE_CODE 	= $res_ns->fields['PK_ATTENDANCE_CODE'];

if($SCHEDULE_DATE == '' || $SCHEDULE_DATE == '0000-00-00')
	$SCHEDULE_DATE = '';
else
	$SCHEDULE_DATE = date("m/d/Y",strtotime($SCHEDULE_DATE));

if($START_TIME == '' || $START_TIME == '00:00:00')
	$START_TIME = '';
else
	$START_TIME = date("H:i A",strtotime($START_TIME)); //Ticket #670
	
if($END_TIME == '' || $END_TIME == '00:00:00')
	$END_TIME = '';
else
	$END_TIME = date("H:i A",strtotime($END_TIME)); //Ticket #670

if($START_TIME != '' || $END_TIME != ''){
	if($START_TIME == ''){
		$START_TIME = date("H:i A",strtotime('00:00:00'));
	}
	if($END_TIME == ''){
		$END_TIME = date("H:i A",strtotime('00:00:00'));
	}
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
	<title><?=EDIT_NON_SCHEDULE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <!--<div class="row page-titles">
                    <div class="col-md-3 align-self-center">
                        <h4 class="text-themecolor">
							<?=EDIT_NON_SCHEDULE ?> 
						</h4>
                    </div>
                </div>-->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<form class="floating-labels" method="post" name="form1" id="form1" onsubmit="return checkvalidNonSheduldedForm()"><!--Ticket # 670-->
									<div class="row">
										<div class="col-md-12">
											<div class="row form-group">
												<input type="text" id="SCHEDULE_DATE" name="SCHEDULE_DATE" value="<?=$SCHEDULE_DATE?>" class="form-control date " >
												<span class="bar"></span>
												<label for="SCHEDULE_DATE"><?=CLASS_DATE?></label>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<div class="row form-group">
												<input type="text" id="START_TIME" name="START_TIME" value="<?=$START_TIME?>" class="form-control timepicker " onchange="get_hour()" >
												<span class="bar"></span>
												<label for="START_TIME"><?=START_TIME?></label>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<div class="row form-group">
												<input type="text" id="END_TIME" name="END_TIME" value="<?=$END_TIME?>" class="form-control timepicker " onchange="get_hour('<?=$stu_id?>')" <?php if(empty($START_TIME)){ ?>disabled="false"<?php } ?>><!-- Ticket # 670 -->
												<div id="err_for_time" style=""></div><!-- Ticket # 670 -->
												<span class="bar"></span>
												<label for="END_TIME"><?=END_TIME?></label>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<div class="row form-group">
											
												<input type="text" id="SCHEDULED_HOUR" name="SCHEDULED_HOUR" value="<?php echo ($HOURS != '') ? $HOURS : '0.00'; ?>" class="form-control " placeholder="0.00" onchange="chnageDecimalVal(this.value,'SCHEDULED_HOUR')" ><!-- Ticket # 670 -->
												<span class="bar"></span>
												<label for="SCHEDULED_HOUR"><?=SCHEDULED_HOUR?></label>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<div class="row form-group">
												<input type="text" id="ATTENDANCE_HOURS" name="ATTENDANCE_HOURS" value="<?php echo ($ATTENDANCE_HOURS != '') ? $ATTENDANCE_HOURS : '0.00'; ?>" class="form-control required-entry" placeholder="0.00" onchange="chnageDecimalVal(this.value,'ATTENDANCE_HOURS')" ><!-- Ticket # 670 -->
												<span class="bar"></span>
												<label for="ATTENDANCE_HOURS"><?=ATTENDANCE_HOURS?></label>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<div class="row form-group">
												<select id="PK_ATTENDANCE_CODE" name="PK_ATTENDANCE_CODE" class="form-control required-entry" >
													<option selected></option>
													 <? 
													 // Ticket # 670
													 //$res_type = $db->Execute("select PK_ATTENDANCE_CODE,CONCAT(CODE,'; ',ATTENDANCE_CODE) AS ATTENDANCE_CODE from M_ATTENDANCE_CODE WHERE ACTIVE = 1 order by CODE ASC");

													 $res_type = $db->Execute("select PK_ATTENDANCE_CODE,CONCAT(CODE,': ',ATTENDANCE_CODE) AS ATTENDANCE_CODE,ACTIVE from M_ATTENDANCE_CODE order by ACTIVE DESC,CODE ASC");


													while (!$res_type->EOF) { 
														// Ticket # 670
														$disabled='';
														$ACTIVE 	= $res_type->fields['ACTIVE'];
														if ($ACTIVE == '0') {
														$Status = '(Inactive)';
														$disabled="disabled";
														} else {
														$Status = '';
														} 
														?>
														<option value="<?=$res_type->fields['PK_ATTENDANCE_CODE'] ?>" <? if($PK_ATTENDANCE_CODE == $res_type->fields['PK_ATTENDANCE_CODE']) echo "selected"; ?> <?=$disabled?>><?=$res_type->fields['ATTENDANCE_CODE'].' '.$Status?></option><!-- Ticket # 670 -->
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_ATTENDANCE_CODE"><?=ATTENDANCE_CODE?></label>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<center>
												
												<input type="hidden" name="PK_STUDENT_SCHEDULE" value="<?=$PK_STUDENT_SCHEDULE?>" />
												<input type="hidden" name="PK_STUDENT_ENROLLMENT" value="<?=$PK_STUDENT_ENROLLMENT?>" />
												<input type="hidden" name="PK_STUDENT_MASTER" value="<?=$PK_STUDENT_MASTER?>" />
												
												<button type="save" class="btn waves-effect waves-light btn-info" id="save_btn"><?=SAVE?></button> <!--// DIAM-670 -->
												
												<button onclick="javascript:window.close()" type="button" class="btn waves-effect waves-light btn-dark" ><?=CANCEL?></button>
											<center>
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
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
		
		$('.timepicker').inputmask(
			"hh:mm t", {
				placeholder: "HH:MM AM/PM", 
				insertMode: false, 
				showMaskOnHover: false,
				hourFormat: 12
			}
		);

		// DIAM-670
		$( '#form1' ).bind( 'keypress keydown keyup', function(e) {
			if (e.keyCode == 13) {
			e.preventDefault();
			}
		});
		// DIAM-670
		
	});
	// Ticket # 670
	function checkvalidNonSheduldedForm(){			
			if(document.getElementById('err_for_time').childNodes.length===1){ 				
				document.getElementById('advice-required-entry-START_TIME1').style.display='block';
				return false;
			}else{
				return true;
			}
		}

		function chnageDecimalVal(val,ele_id){
			var sh = parseFloat(val);
				if (isNaN(sh)) {
					document.getElementById(ele_id).value = '';
				} else {
					document.getElementById(ele_id).value = sh.toFixed(2);
				}
		}

		function convert12HourTo24Hour(time) {
			let [timeStr, modifier] = time.split(' ');
			let [hours, minutes] = timeStr.split(':');
			
			if (hours === '12' &&  modifier.toLowerCase() === 'am') {
				hours = '00';
			}
			
			if (modifier.toLowerCase() === 'pm' && hours !== '12') {
				hours = parseInt(hours, 10) + 12;
			}
			
			return `${hours}:${minutes}`;
		}

		var invalid_time_flag = true;
		// Ticket # 670
	function get_hour(id){
		var START_TIME = document.getElementById('START_TIME').value
		var END_TIME   = document.getElementById('END_TIME').value

		// Ticket # 670
		invalid_time_flag = true;
			//Strat of G-code
			//#unable disabled end time div
			if(START_TIME != ''){
				jQuery('#END_TIME').prop("disabled", false); 
				jQuery('#END_TIME').focus();
			}else{
				jQuery('#END_TIME').prop("disabled", true); 
			}
			//#comapre time 

			var is_start_time_in_am = START_TIME.toLowerCase().includes('am');  
			
			var start_24 = convert12HourTo24Hour(START_TIME);
			// alert(start_24)
			if(END_TIME != ''){				
				var end_24 = convert12HourTo24Hour(END_TIME);
				let [ sh , sm ] = start_24.split(':');
				let [ eh , em ] = end_24.split(':');
				//console.log( " Calculations " , sh+":"+sm , eh+":"+em);
				document.getElementById('err_for_time').innerHTML = '';
				if(sh > eh){
					jQuery('#err_for_time').append('<div class="validation-advice" id="advice-required-entry-START_TIME1" style="">Invalid time</div>');					
					return
				}else if( (sh == eh) && sm > em){
					jQuery('#err_for_time').append('<div class="validation-advice" id="advice-required-entry-START_TIME1" style="">Invalid time</div>');					
					return

				}else{
					invalid_time_flag = false;
				}			

			}
			 
			//// END OF G code 
			// Ticket # 670

		var HOURS	   = '';
		if(START_TIME != '' && END_TIME != ''){
			jQuery(document).ready(function($) { 
				var data  = 'START_TIME='+START_TIME+'&END_TIME='+END_TIME;
				var value = $.ajax({
					url: "ajax_get_hour_from_time",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('ATTENDANCE_HOURS').value = data;
						$("#ATTENDANCE_HOURS").parent().addClass("focused");
					}		
				}).responseText;
			});
		}
	}
	</script>
</body>

</html>
