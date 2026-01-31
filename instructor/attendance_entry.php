<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/attendance_entry.php");
require_once("../school/function_attendance.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_ROLES'] != 3 ){ 
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	if($_POST['COMPLETE'] == 3) {
		$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $_POST['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
		$db->Execute("UPDATE S_STUDENT_ATTENDANCE SET COMPLETED = 0 WHERE PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$PK_COURSE_OFFERING_SCHEDULE_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
		$db->Execute("UPDATE S_COURSE_OFFERING_SCHEDULE_DETAIL SET COMPLETED = 0 WHERE PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$PK_COURSE_OFFERING_SCHEDULE_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
		
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
	header("location:attendance_entry?co=".$_POST['PK_COURSE_OFFERING']."&sch_id=".$_POST['PK_COURSE_OFFERING_SCHEDULE_DETAIL']."&t=".$_POST['PK_TERM_MASTER']);
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
	<style>
		.table th, .table td {padding: 7px;}
		
		.tableFixHead          { overflow-y: auto; height: 500px; }
		.tableFixHead thead th { position: sticky; top: 0; }
		.tableFixHead thead th { background:#E8E8E8; z-index: 999;}
	</style>
	<title><?=ATTENDANCE_ENTRY_PAGE_TITLE?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=ATTENDANCE_ENTRY_PAGE_TITLE?></h4>
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
														<input type="radio" id="TYPE_1" name="TYPE" value="1" class="custom-control-input" checked onchange="show_fields()" >
														<label class="custom-control-label" for="TYPE_1"><?=BY_TERM?></label>
													</div>
													<div class="custom-control custom-radio col-md-6">
														<input type="radio" id="TYPE_2" name="TYPE" value="2" class="custom-control-input" onchange="show_fields()" >
														<label class="custom-control-label" for="TYPE_2"><?=BY_CLASS_DATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row" id="DATE_DIV" style="display:none" >
												<div class="col-12 form-group">
													<input class="form-control date" type="text" value="" name="DATE" id="DATE" onchange="get_course_offering_from_date()"  >
													<span class="bar"></span> 
													<label for="DATE"><?=SELECT_DATE?></label>
												</div>
											</div>
											<!-- Ticket # 1800 -->
											
											<div class="row">
												<div class="col-12 form-group" id="PK_TERM_MASTER_DIV" > <!-- Ticket # 1800 -->
													<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control required-entry" onchange="get_course_offering(this.value)">
														<option value=""></option>
														<? $res_type = $db->Execute("select S_TERM_MASTER.PK_TERM_MASTER, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, IF(S_TERM_MASTER.END_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.END_DATE,'%m/%d/%Y'),'') AS TERM_END_DATE, TERM_DESCRIPTION from S_COURSE_OFFERING LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING , S_TERM_MASTER WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND  S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER GROUP BY S_TERM_MASTER.PK_TERM_MASTER ORDER BY BEGIN_DATE DESC ");
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
												<div class="col-12 form-group" id="PK_COURSE_OFFERING_SCHEDULE_DETAIL_LABEL" >
													<div id="PK_COURSE_OFFERING_SCHEDULE_DETAIL_DIV" >
														<? $_REQUEST['val'] = $_GET['co'];
														$_REQUEST['def'] 	= $_GET['sch_id'];
														include("ajax_get_course_schedule.php"); ?>
													</div>
													<span class="bar"></span> 
													<label for="PK_COURSE_OFFERING_SCHEDULE_DETAIL"><?=SCHEDULED_CLASS_MEETING?></label>
												</div>
												<div class="col-12 form-group text-right">
													<!--<button type="button" onclick="javascript:window.location.href='upload_attendance'" class="btn waves-effect waves-light btn-info"><?=UPLOAD?></button>-->
													
													<button type="button" onclick="get_student_from_schedule();get_course_details();" class="btn waves-effect waves-light btn-info"><?=SHOW?></button>
												</div>
											</div>
											<div id="course_details">
											</div>
										</div>
										<div class="col-sm-9 pt-25 theme-v-border" >
											<div id="STUDENT_DIV">
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
		});
	</script>
	<!-- Ticket # 1800 -->
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>

	<script type="text/javascript">
		jQuery(document).ready(function($) {
			<? if($_GET['sch_id'] != ''){ ?>
			get_student_from_schedule();
			get_course_details();
			<? } ?>
		});
		function get_schedule(val){
			jQuery(document).ready(function($) { 
				var data  = 'val='+val;
				
				/* Ticket # 1800 */
				if(document.getElementById('TYPE_2').checked == true)
					data += "&date="+document.getElementById('DATE').value;
				/* Ticket # 1800 */
				
				var value = $.ajax({
					url: "ajax_get_course_schedule",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_COURSE_OFFERING_SCHEDULE_DETAIL_DIV').innerHTML = data;
						document.getElementById('PK_COURSE_OFFERING_SCHEDULE_DETAIL_LABEL').classList.add("focused");
						
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
					var data  = 'val='+document.getElementById('PK_COURSE_OFFERING_SCHEDULE_DETAIL').value+'&panel=ins'; //Ticket # 1795
					//alert(data)
					var value = $.ajax({
						url: "ajax_get_student_from_schedule",	
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
			jQuery(document).ready(function($) { 
				if(document.getElementById('PK_COURSE_OFFERING_SCHEDULE_DETAIL').value != '') {
					var data  = 'val='+document.getElementById('PK_COURSE_OFFERING_SCHEDULE_DETAIL').value;
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
		function set_att_hour(val,id,def_att,def_att_hour){
			if(val == 1 || val == 7) {
				document.getElementById('ATTENDANCE_HOURS_'+id).value = '0.00';
			} else {
				if(val == def_att)
					document.getElementById('ATTENDANCE_HOURS_'+id).value = def_att_hour;
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
		
		/* Ticket # 1800 */
		function show_fields(){
			if(document.getElementById('TYPE_1').checked == true) {
				document.getElementById('DATE_DIV').style.display 			= 'none'
				document.getElementById('PK_TERM_MASTER_DIV').style.display = 'block'
				document.getElementById('DATE').value 						= "";
				get_course_offering('')
				get_schedule('')
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
			jQuery(document).ready(function($) { 
				var data  = 'date='+document.getElementById('DATE').value;
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
					}		
				}).responseText;
			});
		}
		/* Ticket # 1800 */
	</script>
</body>
</html>