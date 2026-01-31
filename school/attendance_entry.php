<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/attendance_entry.php");
require_once("../language/menu.php"); //Ticket # 1850
require_once("function_attendance.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	if($_POST['COMPLETE'] == 3) {
		$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $_POST['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
		$db->Execute("UPDATE S_COURSE_OFFERING_SCHEDULE_DETAIL SET COMPLETED = 0 WHERE PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$PK_COURSE_OFFERING_SCHEDULE_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
		
		foreach($_POST['PK_STUDENT_ATTENDANCE'] as $PK_STUDENT_ATTENDANCE){
			$db->Execute("UPDATE S_STUDENT_ATTENDANCE SET COMPLETED = 0 WHERE PK_STUDENT_ATTENDANCE = '$PK_STUDENT_ATTENDANCE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
		}
	} else {
		$i = 0;
		foreach($_POST['PK_STUDENT_ATTENDANCE'] as $PK_STUDENT_ATTENDANCE){
			$PK_STUDENT_ATTENDANCE = attendance_entry($_POST['PK_COURSE_OFFERING_SCHEDULE_DETAIL'],$_POST['COMPLETE'],$PK_STUDENT_ATTENDANCE,$_POST['PK_STUDENT_MASTER'][$i],$_POST['PK_STUDENT_ENROLLMENT'][$i],$_POST['PK_STUDENT_SCHEDULE'][$i],$_POST['ATTENDANCE_HOURS'][$i],$_POST['PK_ATTENDANCE_CODE'][$i],$_SESSION['PK_ACCOUNT'],$_SESSION['PK_USER']);
			
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
	}
	
	header("location:attendance_entry?co=".$_POST['PK_COURSE_OFFERING'].'&so='.$_POST['PK_COURSE_OFFERING_SCHEDULE_DETAIL'].'&tm='.$_POST['PK_TERM_MASTER'].'&TYPE='.$_POST['TYPE'].'&dt='.$_POST['DATE'].'&campus='.$_POST['PK_CAMPUS']); //Ticket # 1872 DIAM-1422
}

/* Ticket # 1872 */
if($_GET['TYPE'] == '') {
	$TYPE = 1;
	$DATE = '';
} else {
	$TYPE = $_GET['TYPE'];
	$DATE = $_GET['dt'];
}
/* Ticket # 1872 */
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
		/* DIAM-1422 */
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
	 /* DIAM-1422 */
	</style>
	<title><?=MNU_ATTENDANCE_BY_SCHEDLED_CLASS_MEETING ?> | <?=$title?></title><!-- Ticket # 1850 -->
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <!-- DIAM-1422 -->
	<div id="loaders" style="display: none;">
		<div class="lds-ring">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
		</div>
	</div>
	<!-- DIAM-1422 -->
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_ATTENDANCE_BY_SCHEDLED_CLASS_MEETING ?><!-- Ticket # 1850 -->
							<? if($_SESSION['PK_LANGUAGE'] == 1)
								$lan_field = "TOOL_CONTENT_ENG";
							else
								$lan_field = "TOOL_CONTENT_SPA"; 
							$res_help = $db->Execute("select $lan_field from Z_HELP WHERE PK_HELP = 52"); ?>
										
							<a href="help_docs?id=52" target="_blank"><i class="mdi mdi-help-circle help_size" style="margin-left:5px" title="<?=$res_help->fields[$lan_field] ?>" data-toggle="tooltip" data-placement="right" ></i></a>
						</h4>
                    </div>
                </div>				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
								<form class="floating-labels w-100 m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
									<div class="row">
										<div class="col-sm-3 pt-25">
											<!-- Ticket # 1800 -->
											<div class="row">
												<div class="col-12 col-md-12 form-group">
													<div class="custom-control custom-radio col-md-4">
														<input type="radio" id="TYPE_1" name="TYPE" value="1" class="custom-control-input" onchange="show_fields()" <? if($TYPE == 1) echo "checked"; ?> ><!-- Ticket # 1872 -->
														<label class="custom-control-label" for="TYPE_1"><?=BY_TERM?></label>
													</div>
													<div class="custom-control custom-radio col-md-6">
														<input type="radio" id="TYPE_2" name="TYPE" value="2" class="custom-control-input" onchange="show_fields()" <? if($TYPE == 2) echo "checked"; ?> ><!-- Ticket # 1872 -->
														<label class="custom-control-label" for="TYPE_2"><?=BY_CLASS_DATE?></label>
													</div>
												</div>
											</div>
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
											<div class="row" id="DATE_DIV" style="display:none" >
												<div class="col-12 form-group">
													<input class="form-control date" type="text" value="<?=$DATE?>" name="DATE" id="DATE" onchange="get_course_offering_from_date()"  > <!-- Ticket # 1872 -->
													<span class="bar"></span> 
													<label for="DATE"><?=SELECT_DATE?></label>
												</div>
											</div>
											<!-- Ticket # 1800 -->
											
											<div class="row">
												<div class="col-12 form-group" id="PK_TERM_MASTER_DIV" > <!-- Ticket # 1800 -->
													<?php if(isset($_GET['tm']) && !empty($_GET['tm'])){ ?>
													<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control" onchange="get_course_offering(this.value)" >
														<option ></option>
														<? /* Ticket #1149 - term */
															$res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
															while (!$res_type->EOF) { 
																$str = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'].' - '.$res_type->fields['TERM_DESCRIPTION'];
																if($res_type->fields['ACTIVE'] == 0)
																	$str .= ' (Inactive)'; ?>
																<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($res_type->fields['PK_TERM_MASTER'] == $_GET['tm']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$str ?></option>
															<?	$res_type->MoveNext();
															}  /* Ticket #1149 - term */ ?>
													</select>
													<?php }else{ ?>
													<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control" ><option ></option>
													</select>
													<?php } ?>
													<span class="bar"></span> <!-- //DIAM-1422-->
													<label for="PK_TERM_MASTER"><?=TERM?></label>
												</div>
												
												<div class="col-12 form-group" id="PK_COURSE_OFFERING_LABEL" >
													<div id="PK_COURSE_OFFERING_DIV" >
														<? if($TYPE == 1) { //Ticket # 1872
															$_REQUEST['PK_TERM_MASTER'] 	= $_GET['tm'];
															$_REQUEST['def_val'] 			= $_GET['co'];
															$_REQUEST['dont_show_term'] 	= 1;
															include("ajax_get_course_offering_from_term.php"); 
														} else { /* Ticket # 1872 */
															$_REQUEST['date'] 		= $_GET['dt'];
															$_REQUEST['def_val'] 	= $_GET['co'];
															include("ajax_get_course_offering_from_date.php"); 
														} /* Ticket # 1872 */ ?>
													</div>
													<span class="bar"></span> 
													<label for="PK_COURSE_OFFERING"><?=SELECT_COURSE_OFFERING?></label>
												</div>
												<div class="col-12 form-group" id="PK_COURSE_OFFERING_SCHEDULE_DETAIL_LABEL" >
													<div id="PK_COURSE_OFFERING_SCHEDULE_DETAIL_DIV" >
														<select id="PK_COURSE_OFFERING_SCHEDULE_DETAIL" name="PK_COURSE_OFFERING_SCHEDULE_DETAIL" class="form-control" >
															<option ></option>
														</select>
													</div>
													<span class="bar"></span> 
													<label for="PK_COURSE_OFFERING_SCHEDULE_DETAIL"><?=SCHEDULED_CLASS_MEETING?></label>
												</div>
												<div class="col-12 form-group text-right">
													<!--<button type="button" onclick="javascript:window.location.href='upload_attendance'" class="btn waves-effect waves-light btn-info"><?=UPLOAD?></button>-->
													
													<button type="button" onclick="get_student_from_schedule();get_course_details_for_att();" class="btn waves-effect waves-light btn-info"><?=SHOW?></button>
												</div>
											</div>
											<div id="course_details">
											</div>
										</div>
										<div class="col-sm-9 pt-25 theme-v-border" ><!-- Ticket # 1661 -->
											<div id="STUDENT_DIV" >
											</div>
										</div>
									</div>
									<input type="hidden" name="COMPLETE" id="COMPLETE" value="1" />
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
	
	<!-- Ticket # 1800 -->
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) { 
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto",
				autoclose: true,
			});
			
			show_fields_def() //Ticket # 1872
		});
	</script>
	<!-- Ticket # 1800 -->
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
		jQuery(document).ready(function($) { 
			<? if($_GET['co'] != ''){ ?>
			get_schedule(<?=$_GET['co']?>)
			<? } ?>
		});
		function get_course_offering(val){
			set_notification=false; // DIAM-1422 -->
			document.getElementById('loaders').style.display = 'block'; // DIAM-1422 -->
			jQuery(document).ready(function($) { 
				var data  = 'PK_TERM_MASTER='+val+'&dont_show_term=1&PK_CAMPUS='+$('#PK_CAMPUS').val();	//DIAM-1422;
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
						document.getElementById('PK_COURSE_OFFERING_LABEL').classList.add("focused");
						set_notification=true; // DIAM-1422 -->
						document.getElementById('loaders').style.display = 'none'; // DIAM-1422 -->
					}		
				}).responseText;
			});
		}
		function get_schedule(val){
			set_notification=false; // DIAM-1422 -->
			document.getElementById('loaders').style.display = 'block'; // DIAM-1422 -->
			jQuery(document).ready(function($) { 
				var data  = 'val='+val;
				
				/* Ticket # 1800 */
				if(document.getElementById('TYPE_2').checked == true)
					data += "&date="+document.getElementById('DATE').value;
				/* Ticket # 1800 */
				
				var value = $.ajax({
					url: "../instructor/ajax_get_course_schedule",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_COURSE_OFFERING_SCHEDULE_DETAIL_DIV').innerHTML = data;
						document.getElementById('PK_COURSE_OFFERING_SCHEDULE_DETAIL_LABEL').classList.add("focused");
						set_notification=true; // DIAM-1422 -->
						document.getElementById('loaders').style.display = 'none'; // DIAM-1422 -->
						<? if($_GET['so'] != ''){ ?>
							document.getElementById('PK_COURSE_OFFERING_SCHEDULE_DETAIL').value = <?=$_GET['so']?>;
							get_student_from_schedule();
							get_course_details_for_att();
						<? } ?>
					}		
				}).responseText;
			});
		}
		function clear_div(){
			document.getElementById('STUDENT_DIV').innerHTML 	= '';
			document.getElementById('course_details').innerHTML = '';
		}
		
		function get_student_from_schedule(val){
			jQuery(document).ready(function($) { 
				if(document.getElementById('PK_COURSE_OFFERING_SCHEDULE_DETAIL').value != '') {
					var data  = 'val='+document.getElementById('PK_COURSE_OFFERING_SCHEDULE_DETAIL').value;
					//alert(data)
					var value = $.ajax({
						url: "../instructor/ajax_get_student_from_schedule",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('STUDENT_DIV').innerHTML = data;
						}		
					}).responseText;
				}
			});
		}
		function get_course_details(){
			var PK_COURSE_OFFERING = document.getElementById('PK_COURSE_OFFERING').value
			get_schedule(PK_COURSE_OFFERING)
		}
		function get_course_details_for_att(){
			jQuery(document).ready(function($) { 
				if(document.getElementById('PK_COURSE_OFFERING_SCHEDULE_DETAIL').value != '') {
					var data  = 'val='+document.getElementById('PK_COURSE_OFFERING_SCHEDULE_DETAIL').value;
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
						}		
					}).responseText;
				}
			});
		}
		
		function save_form(val){
			document.getElementById('COMPLETE').value = val;
			if(val == 0 || val == 1) {
				var valid = new Validation('form1', {onSubmit:false});
				var result = valid.validate();
				if(result == true)
					document.form1.submit();
			} else 
				document.form1.submit();
		}
		
		function set_att_hour(val,id,def_att,def_att_hour){
			if(val == 1 || val == 7) {
				document.getElementById('ATTENDANCE_HOURS_'+id).value = '0.00';
			} else {
				//if(val == def_att)
					document.getElementById('ATTENDANCE_HOURS_'+id).value = def_att_hour;
			}
		}
		
		/* Ticket # 1872 */
		function show_fields_def(){
			if(document.getElementById('TYPE_1').checked == true) {
				document.getElementById('DATE_DIV').style.display 			= 'none'
				document.getElementById('PK_TERM_MASTER_DIV').style.display = 'block'
				document.getElementById('DATE').value 						= "";
			} else {
				document.getElementById('DATE_DIV').style.display 			= 'block'
				document.getElementById('PK_TERM_MASTER_DIV').style.display = 'none'
				document.getElementById('PK_TERM_MASTER').value 			= "";
			}
		}
		/* Ticket # 1872 */
		
		/* Ticket # 1800 */
		function show_fields(){
			if(document.getElementById('TYPE_1').checked == true) {
				document.getElementById('DATE_DIV').style.display 			= 'none'
				document.getElementById('PK_TERM_MASTER_DIV').style.display = 'block'
				document.getElementById('DATE').value 						= "";
				get_course_offering('')
				get_schedule('')
				jQuery("#PK_CAMPUS").val(jQuery("#PK_CAMPUS option:first").val('0'));//DIAM-1422
				get_course_term_from_campus(); //DIAM-1422
			} else {
				document.getElementById('DATE_DIV').style.display 			= 'block'
				document.getElementById('PK_TERM_MASTER_DIV').style.display = 'none'
				document.getElementById('PK_TERM_MASTER').value 			= "";
				get_course_offering_from_date()
				get_schedule('')
			}
			clear_div()
		}
		
		function get_course_offering_from_date(){
			set_notification=false; // DIAM-1422 -->
			document.getElementById('loaders').style.display = 'block'; // DIAM-1422 -->
			jQuery(document).ready(function($) { 
				var data  = 'date='+document.getElementById('DATE').value+'&PK_CAMPUS='+$('#PK_CAMPUS').val();	//DIAM-1422
				//alert(data)
				var value = $.ajax({
					url: "ajax_get_course_offering_from_date",
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
						document.getElementById('STUDENT_DIV').innerHTML 			= '';
						get_schedule('')
						clear_div()
						
						$('.floating-labels .form-control').on('focus blur', function (e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
						set_notification=true; // DIAM-1422 -->
						document.getElementById('loaders').style.display = 'none'; // DIAM-1422 -->
					}		
				}).responseText;
			});
		}
		/* Ticket # 1800 */

		//DIAM-1422
		function get_course_term_from_campus(){

			if(document.getElementById('TYPE_1').checked == true) {
				set_notification=false; // DIAM-1422 -->
				document.getElementById('loaders').style.display = 'block'; // DIAM-1422 -->
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
							document.getElementById(term_id).setAttribute("onchange", "get_course_offering(this.value)");	
							document.getElementById(term_id+'_DIV').classList.add("focused");
							set_notification=true; // DIAM-1422 -->
							document.getElementById('loaders').style.display = 'none'; // DIAM-1422 -->

						}		
					}).responseText;
				});
			}
		}
		//DIAM-1422
	</script>
</body>
</html>
