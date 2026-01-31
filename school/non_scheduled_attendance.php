<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/non_scheduled_attendance.php");
require_once("../language/menu.php"); //Ticket # 1850
require_once("function_attendance.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	$i = 0;
	foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT){
		
		$res = $db->Execute("select PK_STUDENT_MASTER from S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
		$PK_STUDENT_MASTER = $res->fields['PK_STUDENT_MASTER'];
	
		$PK_STUDENT_SCHEDULE = create_non_schedule($_POST['PK_STUDENT_SCHEDULE'][$i], $_POST['PK_COURSE_OFFERING'],$_POST['CLASS_DATE'][$i],$_POST['SCHEDULE_START_TIME'][$i],$_POST['SCHEDULE_END_TIME'][$i],$_POST['SCHEDULE_HOURS'][$i],$PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT, 1,$_SESSION['PK_ACCOUNT'],$_SESSION['PK_USER']);

		$PK_STUDENT_ATTENDANCE = attendance_entry('',1,$_POST['PK_STUDENT_ATTENDANCE'][$i],$PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT,$PK_STUDENT_SCHEDULE,$_POST['ATTENDANCE_HOURS'][$i], $_POST['PK_ATTENDANCE_CODE'][$i],$_SESSION['PK_ACCOUNT'],$_SESSION['PK_USER']); //Ticket # 1459
		
		/* Ticket # 1601   */
		//$PK_ATTENDANCE_ACTIVITY_TYPESS = $_POST['PK_ATTENDANCE_ACTIVITY_TYPE'][$i];
		//$db->Execute("UPDATE S_STUDENT_ATTENDANCE SET PK_ATTENDANCE_ACTIVITY_TYPESS = '$PK_ATTENDANCE_ACTIVITY_TYPESS' WHERE PK_STUDENT_ATTENDANCE = '$PK_STUDENT_ATTENDANCE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
		
		$ATTEN = array();
		if(isset($_POST['PK_ATTENDANCE_ACTIVITY_TYPE'][$i]))
			$ATTEN['PK_ATTENDANCE_ACTIVITY_TYPESS'] = $_POST['PK_ATTENDANCE_ACTIVITY_TYPE'][$i];
			
		if(isset($_POST['ATTENDANCE_COMMENTS'][$i]))
			$ATTEN['ATTENDANCE_COMMENTS'] = $_POST['ATTENDANCE_COMMENTS'][$i];
			
		if(!empty($ATTEN)) {
			db_perform('S_STUDENT_ATTENDANCE', $ATTEN, 'update'," PK_STUDENT_ATTENDANCE = '$PK_STUDENT_ATTENDANCE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
		/* Ticket # 1601   */

		$i++;
	}
	
	if(!empty($_POST['DELETE_PK_STUDENT_ATTENDANCE'])) {
		foreach($_POST['DELETE_PK_STUDENT_ATTENDANCE'] as $PK_STUDENT_ATTENDANCE){
			$res = $db->Execute("select PK_STUDENT_SCHEDULE FROM S_STUDENT_ATTENDANCE WHERE PK_STUDENT_ATTENDANCE = '$PK_STUDENT_ATTENDANCE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = 0");
			if($res->RecordCount() > 0) {
				$PK_STUDENT_SCHEDULE = $res->fields['PK_STUDENT_SCHEDULE'];
				$db->Execute("DELETE FROM S_STUDENT_ATTENDANCE WHERE PK_STUDENT_ATTENDANCE = '$PK_STUDENT_ATTENDANCE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				$db->Execute("DELETE FROM S_STUDENT_SCHEDULE WHERE PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			}
		}
	}
	
	if($_GET['no_menu'] == 1) { ?>
		<script type="text/javascript">window.opener.refresh_win_1(this)</script>
	<? } else
		header("location:non_scheduled_attendance?tid=".$_POST['PK_TERM_MASTER'].'&id='.$_POST['PK_COURSE_OFFERING'].'&campus='.$_POST['PK_CAMPUS']); //DIAM-1422
}
/* Ticket # 1459 Ticket # 1601 */
$res_set = $db->Execute("SELECT ENABLE_ATTENDANCE_ACTIVITY_TYPES, ENABLE_ATTENDANCE_COMMENTS FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
$ENABLE_ATTENDANCE_ACTIVITY_TYPES 	= $res_set->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'];
$ENABLE_ATTENDANCE_COMMENTS 		= $res_set->fields['ENABLE_ATTENDANCE_COMMENTS'];
/* Ticket # 1459 Ticket # 1601 */
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
	<style>
		.table th, .table td {padding: 7px;}
		
		.tableFixHead          { overflow-y: auto; height: 500px; }
		.tableFixHead thead th { position: sticky; top: 0; }
		.tableFixHead thead th { background:#E8E8E8; z-index: 999;}
		/* DIAM-949 */
		.lds-ring {
			position: absolute;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			margin: auto;
			width: 64px;
			height: 64px;
		}

		.lds-ring div {
			box-sizing: border-box;
			display: block;
			position: absolute;
			width: 51px;
			height: 51px;
			margin: 6px;
			border: 6px solid #0066ac;
			border-radius: 50%;
			animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
			border-color: #007bff transparent transparent transparent;
		}

		.lds-ring div:nth-child(1) {
			animation-delay: -0.45s;
		}

		.lds-ring div:nth-child(2) {
			animation-delay: -0.3s;
		}

		.lds-ring div:nth-child(3) {
			animation-delay: -0.15s;
		}

		@keyframes lds-ring {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}

		#loaders {
			position: fixed;
			width: 100%;
			z-index: 9999;
			bottom: 0;
			background-color: #2c3e50;
			display: block;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			opacity: 0.6;
			display: none;
		} 
	 /* DIAM-949 */
	</style>
	<title><?=MNU_NON_SCHEDULED_ATTENDANCE?> | <?=$title?></title><!-- Ticket # 1850 -->
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
   <!-- DIAM-949 -->
   <div id="loaders" style="display: none;">
		<div class="lds-ring">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
		</div>
	</div>
	<!-- DIAM-949 -->
    <div id="main-wrapper">
       <? if($_GET['no_menu'] != 1)
			require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_NON_SCHEDULED_ATTENDANCE ?><!-- Ticket # 1850 -->
							<? if($_SESSION['PK_LANGUAGE'] == 1)
								$lan_field = "TOOL_CONTENT_ENG";
							else
								$lan_field = "TOOL_CONTENT_SPA"; 
							$res_help = $db->Execute("select $lan_field from Z_HELP WHERE PK_HELP = 54"); ?>
										
							<a href="help_docs?id=54" target="_blank"><i class="mdi mdi-help-circle help_size" style="margin-left:5px" title="<?=$res_help->fields[$lan_field] ?>" data-toggle="tooltip" data-placement="right" ></i></a>
						</h4>
                    </div>
                </div>				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
								<form class="floating-labels w-100 m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" onsubmit="return checkvalidNonSheduldedForm()"><!--Ticket # 670-->
									<div class="row">
										<div class="col-sm-3 pt-25" style="flex: 0 0 25%;max-width: 20%;" > <!-- Ticket # 1601 -->
										<!-- DIAM-1422 -->
										<div class="row" id="PK_CAMPUS_DIV"  >
												<div class="col-12 form-group">
													<select id="PK_CAMPUS" name="PK_CAMPUS" class="form-control" onchange="get_course_term_from_campus()" >
													<option value=""></option>
													<? 
													$res_type = $db->Execute("select PK_CAMPUS,CAMPUS_CODE, ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CAMPUS_CODE ASC"); 
													while (!$res_type->EOF) { 
													$option_label = $res_type->fields['CAMPUS_CODE'];
													if($res_type->fields['ACTIVE'] == 0)
													$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" <? if($_GET['campus'] == $res_type->fields['PK_CAMPUS']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
													<?	$res_type->MoveNext();
													}  ?>
													</select>
													<span class="bar"></span> 
													<label for="PK_TERM_MASTER"><?=CAMPUS?></label>
												</div>
											</div>
											<!-- DIAM-1422 -->

											<div class="row">
												<div class="col-12 form-group " id="PK_TERM_MASTER_DIV">

													<?php if(isset($_GET['tid']) && !empty($_GET['tid'])){ ?>
													<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control" onchange="get_offering(this.value);get_non_schedule();" >
														<option></option>
														<? /* Ticket #1149 - term */
														$res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
														while (!$res_type->EOF) { 
															$str = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'].' - '.$res_type->fields['TERM_DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$str .= ' (Inactive)'; ?>
															<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($_GET['tid'] == $res_type->fields['PK_TERM_MASTER'] ) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$str ?></option>
														<?	$res_type->MoveNext();
														} /* Ticket #1149 - term */ ?>
													</select>
													<?php }else{ ?>
													<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control" ><option ></option>
													</select>
													<?php } ?><!-- //DIAM-1422-->

													<span class="bar"></span> 
													<label for="PK_TERM_MASTER"><?=TERM?></label>
												</div>
												<div class="col-12 form-group " id="PK_COURSE_OFFERING_LBL"  >
													<div id="PK_COURSE_OFFERING_DIV" >
														<? $_REQUEST['PK_TERM_MASTER'] = $_GET['tid']; 
														$_REQUEST['def_val'] = $_GET['id']; 
														include('ajax_get_course_offering_from_term.php'); ?>
													</div>
													<span class="bar"></span> 
													<label for="PK_COURSE_OFFERING"><?=COURSE_OFFERING?></label>
												</div>
												<!--<div class="col-12 form-group" >
													<input type="text" id="CLASS_DATE" name="CLASS_DATE" value="<?=$_GET['dt']?>" class="form-control date required-entry" onchange="get_non_schedule();" onkeyup="get_non_schedule();" readonly>
													<span class="bar"></span> 
													<label for="CLASS_DATE"><?=CLASS_DATE?></label>
												</div>-->
											</div>
											<div id="course_details">
											</div>
										</div>
										<div class="col-sm-9 pt-25 theme-v-border" style="flex: 0 0 80%;max-width: 80%;" > <!-- Ticket # 1601 -->
											<div class="row">
												<div class="col-md-2" style="flex: 12%;max-width: 12%;" >
													<b><?=STUDENTS?></b>
												</div> 
												<div class="col-md-1" style="flex: 10%;max-width: 10%;"  >
													<b><?=ENROLLMENT?></b>
												</div>
												<div class="col-md-1" style="flex: 10%;max-width: 10%;"  >
													<b><?=DATE?></b>
												</div>
												<div class="col-md-2" style="flex: 6%;max-width: 6%;" >
													<b><?=SCH_HOUR?></b>
												</div>
												<div class="col-md-1" style="padding-right:0;max-width:8%;flex:8%;" >
													<b><?=START_TIME?></b>
												</div>
												<div class="col-md-1" style="padding-right:0;max-width:8%;flex:8%;" >
													<b><?=END_TIME?></b>
												</div>
												
												<div class="col-md-2" style="flex: 8.5%;max-width: 8.5%;"  >
													<b><?=ATTENDANCE_HOURS?></b>
												</div>
												<div class="col-md-2" style="flex: 9%;max-width: 9%;"  >
													<b><?=ATTENDANCE_CODE?></b>
												</div>
												<? /* Ticket # 1459 */
												if($ENABLE_ATTENDANCE_ACTIVITY_TYPES == 1){ ?>
													<div class="col-md-2" style="flex: 11%;max-width: 11%;" >
														<b><?=ACTIVITY_TYPE?></b>
													</div>
												<? } 
												/* Ticket # 1459 */?>
												
												<? /* Ticket # 1601  */
												if($ENABLE_ATTENDANCE_COMMENTS == 1){ ?>
													<div class="col-md-2" style="flex: 12%;max-width: 12%;"  >
														<b><?=COMMENTS?></b>
													</div>
												<? } 
												/* Ticket # 1601  */?>
												
												<div class="col-md-1" style="flex: 4%;max-width: 4%;" >
													<a href="javascript:void(0);" onclick="add_student();" title="<?=ADD?>" id="ADD_STUDENT_BTN" style="display:none"><i class="fa fa-plus-circle help_size"></i> </a>
												</div>
											</div>
											<hr />
											<div id="STUDENT_DIV" style="max-height:400px;overflow-scroll;overflow-x: hidden;overflow-y: auto;" >
											</div>
											<br />
											<div class="row">
												<div class="col-md-5"></div>
												<div class="col-md-5">
													
													<button name="btn" type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												</div>
											</div>
										</div>
									</div>
									
									<div id="delete_div"></div>
								</form>
                            </div>
                        </div>
                    </div>
				</div>				
            </div>
			
			<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="exampleModalLabel1"><?=DELETE_CONFIRMATION?></h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body">
							<div class="form-group" id="delete_message" ></div>
							<input type="hidden" id="DELETE_ID" value="0" />
							<input type="hidden" id="DELETE_TYPE" value="0" />
						</div>
						<div class="modal-footer">
							<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
							<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" ><?=NO?></button>
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
		
		$('.timepicker').inputmask(
			"hh:mm t", {
				placeholder: "HH:MM AM/PM", 
				insertMode: false, 
				showMaskOnHover: false,
				hourFormat: 12
			}
		);
		<? if($_GET['id'] != ''){ ?>
		get_course_details()
		<? } ?>
		
		<? if($_GET['tid'] != '' && $_GET['id'] != ''){ ?>
		get_non_schedule()
		<? } ?>
	});
	
	function delete_row(id,type){
		jQuery(document).ready(function($) {
			if(type == 'student')
				document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.STUDENT?>?';
				
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
			$("#DELETE_TYPE").val(type)
		});
	}
	
	function conf_delete(val,id){
		jQuery(document).ready(function($) {
			if(val == 1) {
				if($("#DELETE_TYPE").val() == 'student') {
					var id = $("#DELETE_ID").val()
					
					var DELETE_PK_STUDENT_ATTENDANCE = document.getElementById('PK_STUDENT_ATTENDANCE_'+id).value
					
					var data = '<input type="hidden" name="DELETE_PK_STUDENT_ATTENDANCE[]" value="'+DELETE_PK_STUDENT_ATTENDANCE+'" >';
					$('#delete_div').append(data)
					
					$("#student_"+id).remove();
				}
			}
			$("#deleteModal").modal("hide");
		});
	}
	</script>
	
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		function get_course_details(){
			var set_notification=false; // DIAM-949 -->
			document.getElementById('loaders').style.display = 'block'; // DIAM-949 -->
			jQuery(document).ready(function($) { 
				var style2 = '';
				if(document.getElementById('PK_COURSE_OFFERING').value != '') {
					document.getElementById('PK_COURSE_OFFERING_LBL').classList.add("focused");
					var data  = 'PK_COURSE_OFFERING='+document.getElementById('PK_COURSE_OFFERING').value;
					//alert(data)
					var value = $.ajax({
						url: "../instructor/ajax_get_course_details_for_attendance",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('course_details').innerHTML = data;
							style2 = 'inline';
							set_notification=true; // DIAM-949 -->
							document.getElementById('loaders').style.display = 'none'; // DIAM-949 -->
							get_non_schedule();
							
						}		
					}).responseText;
				} else
					style2 = 'none';
					
				document.getElementById('ADD_STUDENT_BTN').style.display 	= style2
				document.getElementById('STUDENT_DIV').innerHTML 			= '';
			});
		}
		function get_non_schedule(){
			var set_notification=false; // DIAM-949 -->
			document.getElementById('loaders').style.display = 'block'; // DIAM-949 -->
			jQuery(document).ready(function($) { 
				var PK_TERM_MASTER 		= document.getElementById('PK_TERM_MASTER').value
				var PK_COURSE_OFFERING 	= document.getElementById('PK_COURSE_OFFERING').value
				
				if(PK_TERM_MASTER != '' && PK_COURSE_OFFERING != '') {
					var data  = 'PK_TERM_MASTER='+PK_TERM_MASTER+'&PK_COURSE_OFFERING='+PK_COURSE_OFFERING;
					//alert(data)
					var value = $.ajax({
						url: "ajax_get_non_scheduled_attendance",	
						type: "POST",		 
						data: data,		
						async: true,
						cache: false,
						success: function (data) {	
							//alert(data)
							data = data.split("|||");
							document.getElementById('STUDENT_DIV').innerHTML = data[0];
							stu_id = data[1];

							set_notification=true; // DIAM-949 -->
							document.getElementById('loaders').style.display = 'none'; // DIAM-949 -->
						}		
					}).responseText;
				}
			});
		}
		var stu_id = 0;
		function add_student(){
			set_notification=false; // DIAM-949 -->
			document.getElementById('loaders').style.display = 'block'; // DIAM-949 -->
			jQuery(document).ready(function($) { 
				if(document.getElementById('PK_COURSE_OFFERING').value != '') {
					var data  = 'PK_COURSE_OFFERING='+document.getElementById('PK_COURSE_OFFERING').value+'&show_date=1&stu_id='+stu_id;
					//alert(data)
					var value = $.ajax({
						url: "../instructor/ajax_get_student_from_course_offering",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							$('#STUDENT_DIV').append(data)
							set_notification=true; // DIAM-949 -->
							document.getElementById('loaders').style.display = 'none'; // DIAM-949 -->
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
							
							stu_id++;
						}		
					}).responseText;
				} else
					document.getElementById('STUDENT_DIV').innerHTML = '';
			});
		}
		// Ticket # 670
		function checkvalidNonSheduldedForm(){							
			if(jQuery("#STUDENT_DIV .timepicker").hasClass("validation-failed")){ 				
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
			var START_TIME = document.getElementById('SCHEDULE_'+id+'_START_TIME').value;
			var END_TIME   = document.getElementById('SCHEDULE_'+id+'_END_TIME').value;

			// Ticket # 670
			invalid_time_flag = true;
			//Strat of G-code
			//#unable disabled end time div
			if(START_TIME != ''){
				//jQuery('#SCHEDULE_'+id+'_END_TIME').prop("disabled", false); 
				jQuery('#SCHEDULE_'+id+'_END_TIME').focus();
			}else{
				//jQuery('#SCHEDULE_'+id+'_END_TIME').prop("disabled", true); 
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
				document.getElementById('err_for_time_'+id).innerHTML = '';
			
				if(sh > eh){
					jQuery('#err_for_time_'+id).append('<div class="validation-advice validation-advice" id="advice-required-entry-ADD_SCHEDULE_'+id+'_START_TIME" style="">Invalid time</div>');
					jQuery('#SCHEDULE_'+id+'_END_TIME').addClass('validation-failed');
					
					return
				}else if( (sh == eh) && sm > em){
					jQuery('#err_for_time_'+id).append('<div class="validation-advice" id="advice-required-entry-ADD_SCHEDULE_'+id+'_START_TIME" style="">Invalid time</div>');
					jQuery('#SCHEDULE_'+id+'_END_TIME').addClass('validation-failed');
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
							document.getElementById('ATTENDANCE_'+id+'_HOURS').value = data;
							$("#ATTENDANCE_"+id+"_HOURS").parent().addClass("focused");
							
							calc_total_scheduled_hours(1)
						}		
					}).responseText;
				});
			} else {
			}
			document.getElementById(id+'_HOURS').value = HOURS;
		}
		function get_offering(val){
			set_notification=false; // DIAM-949 -->
			document.getElementById('loaders').style.display = 'block'; // DIAM-949 -->
			jQuery(document).ready(function($) { 
				var data  = 'PK_TERM_MASTER='+val;
				//alert(data)
				var value = $.ajax({
					url: "ajax_get_course_offering_from_term",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
						document.getElementById('STUDENT_DIV').innerHTML 			= '';
						set_notification=true; // DIAM-949 -->
						document.getElementById('loaders').style.display = 'none'; // DIAM-949 -->
					}		
				}).responseText;
			});
		}
		
		
		function get_enrollment(val, id){
			set_notification=false; // DIAM-949 -->
			document.getElementById('loaders').style.display = 'block';  // DIAM-949 -->
			jQuery(document).ready(function($) { 
				var data  = 'val='+val;
				//alert(data)
				var value = $.ajax({
					url: "ajax_get_student_enrollment_detail",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('enrollment_div_'+id).innerHTML = data;
						set_notification=true; // DIAM-949 -->
						document.getElementById('loaders').style.display = 'none'; // DIAM-949 -->
					}		
				}).responseText;
			});
		}

		//DIAM-1422
		function get_course_term_from_campus(){			
			var set_notification=true;
			jQuery(document).ready(function($) {
			var data  = 'PK_CAMPUS='+$('#PK_CAMPUS').val();				
			var value = $.ajax({
				url: "ajax_get_term_from_campus",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					var term_id = 'PK_TERM_MASTER';						
					data = data.replace('id="PK_TERM_MASTER"', 'id="'+term_id+'"');
					var add_html = '<span class="bar"></span><label for="PK_TERM_MASTER"><?=TERM?></label>';
					document.getElementById(term_id+'_DIV').innerHTML 	= data + add_html;
					document.getElementById(term_id).setAttribute("onchange", "get_offering(this.value);get_non_schedule();");	
					document.getElementById(term_id+'_DIV').classList.add("focused");
					set_notification=false;

				}		
			}).responseText;
			});
			
		}
		//DIAM-1422
	</script>
	
	<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
	<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.select2').select2();
		});
	</script>
</body>
</html>
