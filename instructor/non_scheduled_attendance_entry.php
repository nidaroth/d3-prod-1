<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/attendance_entry.php");
require_once("../language/non_scheduled_attendance.php");
require_once("../school/function_attendance.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_ROLES'] != 3 ){ 
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
	
	header("location:non_scheduled_attendance_entry?t=".$_POST['PK_TERM_MASTER'].'&co='.$_POST['PK_COURSE_OFFERING']);
}
/* Ticket # 1601 */
$res_set = $db->Execute("SELECT ENABLE_ATTENDANCE_ACTIVITY_TYPES, ENABLE_ATTENDANCE_COMMENTS FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
$ENABLE_ATTENDANCE_ACTIVITY_TYPES 	= $res_set->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'];
$ENABLE_ATTENDANCE_COMMENTS 		= $res_set->fields['ENABLE_ATTENDANCE_COMMENTS'];
/* Ticket # 1601 */
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
	</style>
	<title><?=MNU_ATTENDANCE_ENTRY_NON_SCHEDULED?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_ATTENDANCE_ENTRY_NON_SCHEDULED?></h4>
                    </div>
                </div>				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
								<form class="floating-labels w-100 m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
									<div class="row">
										<div class="col-sm-3 pt-25">
											<div class="row">
												<div class="col-12 form-group">
													<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control required-entry" onchange="get_course_offering(this.value)">
														<option value=""></option>
														<? $res_type = $db->Execute("select S_TERM_MASTER.PK_TERM_MASTER,IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, IF(S_TERM_MASTER.END_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.END_DATE,'%m/%d/%Y'),'') AS TERM_END_DATE, TERM_DESCRIPTION from S_COURSE_OFFERING LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING , S_TERM_MASTER WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND  S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER GROUP BY S_TERM_MASTER.PK_TERM_MASTER ORDER BY BEGIN_DATE DESC ");
														//$res_type = $db->Execute("select S_TERM_MASTER.PK_TERM_MASTER,IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE from S_COURSE_OFFERING, S_TERM_MASTER WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND  S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER GROUP BY S_TERM_MASTER.PK_TERM_MASTER ORDER BY BEGIN_DATE ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($_GET['t'] == $res_type->fields['PK_TERM_MASTER']) echo "selected" ?> ><?=$res_type->fields['TERM_BEGIN_DATE'].' - '.$res_type->fields['TERM_END_DATE'].' - '.$res_type->fields['TERM_DESCRIPTION'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
													<span class="bar"></span> 
													<label for="PK_TERM_MASTER"><?=SELECT_TERM?></label>
												</div>
												
												<div class="col-12 form-group" id="PK_COURSE_OFFERING_LABEL"  >
													<div id="PK_COURSE_OFFERING_DIV" >
														<? $_REQUEST['val'] = $_GET['t'];
														$_REQUEST['def'] 	= $_GET['co'];
														include("ajax_get_course_offering.php"); ?>
													</div>
													<span class="bar"></span> 
													<label for="PK_COURSE_OFFERING"><?=SELECT_COURSE_OFFERING?></label>
												</div>
												
												<div class="col-12 form-group text-right">
													<!--<button type="button" onclick="javascript:window.location.href='upload_attendance'" class="btn waves-effect waves-light btn-info"><?=UPLOAD?></button>-->
													
													<button type="button" onclick="get_student_from_co();get_course_details();" class="btn waves-effect waves-light btn-info"><?=SHOW?></button>
												</div>
											</div>
											<div id="course_details">
											</div>
										</div>
										
										<div class="col-sm-9 pt-25 theme-v-border" >
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
												
												<? if($ENABLE_ATTENDANCE_ACTIVITY_TYPES == 1){ ?>
													<div class="col-md-2" style="flex: 11%;max-width: 11%;" >
														<b><?=ACTIVITY_TYPE?></b>
													</div>
												<? } ?>
												
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
											<div id="delete_div" style="display:none" ></div>
											<br />
											<div class="row">
												<div class="col-md-5"></div>
												<div class="col-md-5">
													
													<button name="btn" name="btn" type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												</div>
											</div>
										</div>
									</div>
									
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
	<script src="../backend_assets/node_modules/inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
	<script src="../backend_assets/dist/js/pages/mask.init.js"></script>
	
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
	});
	</script>
	
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>

	<script type="text/javascript">
		jQuery(document).ready(function($) {
			<? if($_GET['co'] != ''){ ?>
			get_student_from_co();
			get_course_details();
			<? } ?>
		});
		function get_schedule(val){
			
		}
		function clear_div(){
			document.getElementById('STUDENT_DIV').innerHTML 	= '';
			document.getElementById('course_details').innerHTML = '';
		}
		
		function get_student_from_co(){
			jQuery(document).ready(function($) { 
				var PK_TERM_MASTER 		= document.getElementById('PK_TERM_MASTER').value
				var PK_COURSE_OFFERING 	= document.getElementById('PK_COURSE_OFFERING').value
				
				if(PK_TERM_MASTER != '' && PK_COURSE_OFFERING != '') {
					var data  = 'PK_TERM_MASTER='+PK_TERM_MASTER+'&PK_COURSE_OFFERING='+PK_COURSE_OFFERING;
					//alert(data)
					var value = $.ajax({
						url: "../school/ajax_get_non_scheduled_attendance",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							data = data.split("|||")
							document.getElementById('STUDENT_DIV').innerHTML = data[0];
							stu_id = data[1]
							document.getElementById('ADD_STUDENT_BTN').style.display = 'inline'
						}		
					}).responseText;
				}
			});
		}
		
		var stu_id = 0;
		function add_student(){
			jQuery(document).ready(function($) { 
				var style2 = '';
				if(document.getElementById('PK_COURSE_OFFERING').value != '') {
					var data  = 'PK_COURSE_OFFERING='+document.getElementById('PK_COURSE_OFFERING').value+'&show_date=1&stu_id='+stu_id;
					//alert(data)
					var value = $.ajax({
						url: "ajax_get_student_from_course_offering",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							$('#STUDENT_DIV').append(data)
							
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
							
							style2 = 'inline';
							
							stu_id++;
						}		
					}).responseText;
				} else {
					document.getElementById('STUDENT_DIV').innerHTML = '';
					style2 = 'none';
				}	
				document.getElementById('ADD_STUDENT_BTN').style.display 	= style2
			});
		}
		
		function get_course_details(){
			jQuery(document).ready(function($) { 
				if(document.getElementById('PK_COURSE_OFFERING').value != '') {
					var data  = 'PK_COURSE_OFFERING='+document.getElementById('PK_COURSE_OFFERING').value;
					//alert(data)
					var value = $.ajax({
						url: "ajax_get_course_details_for_attendance",	
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
		function set_att_hour(val,id){
			if(val == 1) {
				document.getElementById('ATTENDANCE_HOURS_'+id).value = '';
			}
		}
		function get_course_offering(val){
			jQuery(document).ready(function($) { 
				var data  = 'val='+val;
				var value = $.ajax({
					url: "ajax_get_course_offering",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
						document.getElementById('PK_COURSE_OFFERING_LABEL').classList.add("focused");
						
					}		
				}).responseText;
			});
		}
		
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				if(type == 'student')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.STUDENT?>?';
					
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}
		
		function get_hour(id){
			var START_TIME = document.getElementById('SCHEDULE_'+id+'_START_TIME').value
			var END_TIME   = document.getElementById('SCHEDULE_'+id+'_END_TIME').value
			var HOURS	   = '';
			if(START_TIME != '' && END_TIME != ''){
				jQuery(document).ready(function($) { 
					var data  = 'START_TIME='+START_TIME+'&END_TIME='+END_TIME;
					var value = $.ajax({
						url: "../school/ajax_get_hour_from_time",	
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
		
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'student') {
						var id = $("#DELETE_ID").val()
						
						var DELETE_PK_STUDENT_ATTENDANCE = document.getElementById('PK_STUDENT_ATTENDANCE_'+id).value
						
						var data = '<input type="text" name="DELETE_PK_STUDENT_ATTENDANCE[]" value="'+DELETE_PK_STUDENT_ATTENDANCE+'" >';
						$('#delete_div').append(data)
						
						$("#student_"+id).remove();
					}
				}
				$("#deleteModal").modal("hide");
			});
		}
		
		function get_enrollment(val, id){
			jQuery(document).ready(function($) { 
				var data  = 'val='+val;
				//alert(data)
				var value = $.ajax({
					url: "../school/ajax_get_student_enrollment_detail",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('enrollment_div_'+id).innerHTML = data;
					}		
				}).responseText;
			});
		}
	</script>
</body>
</html>