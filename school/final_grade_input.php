<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/final_grade_input.php");

require_once("../global/mail.php"); 
require_once("../global/texting.php"); 
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$i = 0;
	foreach($_POST['GRADE_INPUT_PK_STUDENT_COURSE'] as $PK_STUDENT_COURSE){
		
		$res_course_unit = $db->Execute("SELECT UNITS FROM S_COURSE, S_COURSE_OFFERING, S_STUDENT_COURSE WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
		
		$STUDENT_COURSE = array();
		$STUDENT_COURSE['COURSE_UNITS'] 						= $res_course_unit->fields['UNITS'];
		$STUDENT_COURSE['FINAL_GRADE'] 							= $_POST['GRADE_INPUT_GRADE'][$i];
		$STUDENT_COURSE['PK_COURSE_OFFERING_STUDENT_STATUS'] 	= $_POST['GRADE_PK_COURSE_OFFERING_STUDENT_STATUS'][$i];
		$STUDENT_COURSE['INACTIVE'] 							= $_POST['GRADE_INPUT_INACTIVE'][$i];
		$STUDENT_COURSE['MIDPOINT_GRADE'] 						= $_POST['GRADE_INPUT_MIDPOINT_GRADE'][$i];
		$STUDENT_COURSE['NUMERIC_GRADE'] 						= $_POST['GRADE_INPUT_NUMERIC_GRADE'][$i];
		
		$flag = 0;
		$res_tc = $db->Execute("SELECT FINAL_GRADE FROM S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' "); 
		$CUR_PK_GRADE = $res_tc->fields['FINAL_GRADE'];
		if($CUR_PK_GRADE != $STUDENT_COURSE['FINAL_GRADE']) {
			$res_grade_data = $db->Execute("SELECT * FROM S_GRADE WHERE PK_GRADE = '$STUDENT_COURSE[FINAL_GRADE]' "); 
			$STUDENT_COURSE['FINAL_GRADE_GRADE'] 				= $res_grade_data->fields['GRADE'];
			$STUDENT_COURSE['FINAL_GRADE_NUMBER_GRADE'] 		= $res_grade_data->fields['NUMBER_GRADE'];
			$STUDENT_COURSE['FINAL_GRADE_CALCULATE_GPA'] 		= $res_grade_data->fields['CALCULATE_GPA'];
			$STUDENT_COURSE['FINAL_GRADE_UNITS_ATTEMPTED'] 		= $res_grade_data->fields['UNITS_ATTEMPTED'];
			$STUDENT_COURSE['FINAL_GRADE_UNITS_COMPLETED'] 		= $res_grade_data->fields['UNITS_COMPLETED'];
			$STUDENT_COURSE['FINAL_GRADE_UNITS_IN_PROGRESS'] 	= $res_grade_data->fields['UNITS_IN_PROGRESS'];
			$STUDENT_COURSE['FINAL_GRADE_WEIGHTED_GRADE_CALC'] 	= $res_grade_data->fields['WEIGHTED_GRADE_CALC'];
			$STUDENT_COURSE['FINAL_GRADE_RETAKE_UPDATE'] 		= $res_grade_data->fields['RETAKE_UPDATE'];
			
			$flag = 1;
		}
		
		if($STUDENT_COURSE['INACTIVE'] != '')
			$STUDENT_COURSE['INACTIVE'] = date("Y-m-d",strtotime($STUDENT_COURSE['INACTIVE']));
	
		db_perform('S_STUDENT_COURSE', $STUDENT_COURSE, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' ");
		
		if($flag == 1) {
			$res_noti = $db->Execute("SELECT PK_EMAIL_TEMPLATE,PK_TEXT_TEMPLATE FROM S_NOTIFICATION_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EVENT_TYPE = 9");
			if($res_noti->RecordCount() > 0) {
				if($res_noti->fields['PK_EMAIL_TEMPLATE'] > 0) {
					send_final_grade_posted_mail($PK_STUDENT_COURSE,$res_noti->fields['PK_EMAIL_TEMPLATE']);
				}
				
				if($res_noti->fields['PK_TEXT_TEMPLATE'] > 0) {
					send_final_grade_posted_text($PK_STUDENT_COURSE,$res_noti->fields['PK_TEXT_TEMPLATE']);
				}
			}
		}
		
		$i++;
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
	<title><?=FINAL_GRADE?> | <?=$title?></title>
	<style>
		.no-records-found{display:none;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else EDIT; ?> <?=FINAL_GRADE?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
								
									<div class="row">
                                        <div class="col-md-3">
											<? if(empty($_GET)){ ?>
											<div class="col-12 col-sm-12 form-group">
												<? $res_camp = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");?>
												<select id="PK_CAMPUS" name="PK_CAMPUS" class="form-control" onchange="get_course(this.value);" >
													<option></option>
													<? while (!$res_camp->EOF) { ?>
														<option value="<?=$res_camp->fields['PK_CAMPUS']?>" <? if($res_camp->RecordCount() == 1) echo "selected"; ?> ><?=$res_camp->fields['CAMPUS_CODE'] ?></option>
														<? $res_camp->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_CAMPUS"><?=CAMPUS?></label>
											</div>
											<? } ?>
											
											<div class="col-12 col-sm-12 form-group" id="PK_COURSE_LBL">
												<div id="PK_COURSE_DIV"  >
													<? /* Ticket # 1740  */
													$res_type = $db->Execute("select PK_COURSE, COURSE_CODE, TRANSCRIPT_CODE, COURSE_DESCRIPTION FROM S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 ORDER BY COURSE_CODE "); ?>
													<select id="PK_COURSE" name="PK_COURSE" class="form-control" onchange="get_course_offering();" >
														<option></option>
														<? while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_COURSE']?>" <? if($res_type->fields['PK_COURSE'] == $_GET['c']) echo "selected"; ?> ><?=$res_type->fields['COURSE_CODE'].' - '.$res_type->fields['TRANSCRIPT_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION'] ?></option>
															<? $res_type->MoveNext();
														} /* Ticket # 1740  */ ?>
													</select>
												</div>
												<span class="bar"></span> 
												<label for="PK_COURSE"><?=COURSE?></label>
											</div>
											
											<div class="col-12 col-sm-12 form-group" id="PK_TERM_MASTER_LBL" >
												<div id="PK_TERM_MASTER_DIV"  >
													<? $_REQUEST['PK_COURSE'] 	= $_GET['c'];
													$_REQUEST['SELECTED'] 		= $_GET['t'];
													include("ajax_get_term_from_course.php"); ?>
												</div>
												<span class="bar"></span> 
												<label for="PK_TERM_MASTER"><?=TERM_DATE?></label>
											</div>
											
											<div class="col-12 col-sm-12 form-group " id="PK_COURSE_OFFERING_LBL"  >
												<div id="PK_COURSE_OFFERING_DIV"  >
													<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control" >
														<option></option>
													</select>
												</div>
												<span class="bar"></span> 
												<label for="PK_COURSE_OFFERING"><?=COURSE_OFFERING?></label>
											</div>
											
										</div>
										 <div class="col-md-9" id="PK_COURSE_OFFERING_DETAIL_DIV" >
											
										</div>
                                    </div>
									
									<div class="p-20">
										<table data-toggle="table" data-mobile-responsive="true" class="table-striped" id="grade_input_table" >
											<thead>
												<!-- Ticket # 1963 -->
												<tr>
													<th ><?=STUDENT?></th>
													<th ><?=STUDENT_ID?></th>
													<th ><?=FINAL_GRADE?></th>
													<th ><?=FINAL_NUMERIC_GRADE_1?></th>
													<th ><?=MIDPOINT_GRADE?></th>
													<th ><?=STATUS?></th><!-- Ticket #1685 -->
													<th ><?=INACTIVE?></th>
													<th ><?=RETURN_DATE?></th> <!-- Ticket #1156 -->
													<th ><?=ENROLLMENT?></th>
													<th ><?=START_DATE?></th>
													<th ><?=END_DATE?></th>
												</tr>
												<!-- Ticket # 1963 -->
											</thead>
											<tbody>
												
											</tbody>
										</table>
										
									</div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='management'" ><?=CANCEL?></button>
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="goto_grade_book()" ><?=GRADE_BOOK?></button>
											</div>
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
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');

		<? if($_GET['c'] != '' && $_GET['t'] != ''){ ?>
			get_course_offering_from_term()
			get_course_offering_detail(<?=$_GET['co']?>)
			get_student(<?=$_GET['co']?>)
		<? } ?>
		
		jQuery(document).ready(function($) { 
			<? if($res_camp->RecordCount() == 1) { ?> 
			get_course(<?=$res_camp->fields['PK_CAMPUS']?>)
			<? } ?>
		});
		
		function get_course(val){
			jQuery(document).ready(function($) { 
				if(val != ''  ) {
					var data  = 'PK_CAMPUS='+val;
					//alert(data)
					var value = $.ajax({
						url: "ajax_get_course_from_campus",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('PK_COURSE_DIV').innerHTML = data;
							document.getElementById('PK_COURSE_LBL').classList.add("focused");
						}		
					}).responseText;
				}
			});
		}
		
		function get_course_offering(){
			get_course_term()
		}
		
		function get_course_offering_from_term(){
			jQuery(document).ready(function($) { 
				if(document.getElementById('PK_COURSE').value != '' && document.getElementById('PK_TERM_MASTER').value != '') {
					var data  = 'PK_COURSE='+document.getElementById('PK_COURSE').value+'&PK_TERM_MASTER='+document.getElementById('PK_TERM_MASTER').value;
					//alert(data)
					var value = $.ajax({
						url: "ajax_get_course_offering_for_course_term",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
							$('.floating-labels .form-control').on('focus blur', function (e) {
								$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
							}).trigger('blur');
							
							<? if($_GET['co'] != ''){ ?>
								document.getElementById('PK_COURSE_OFFERING').value = '<?=$_GET['co']?>';
								document.getElementById('PK_COURSE_OFFERING_LBL').classList.add("focused");
							<? } ?>
						}		
					}).responseText;
				}
			});
		}
		function get_course_term(){
			jQuery(document).ready(function($) { 
				var data  = 'PK_COURSE='+document.getElementById('PK_COURSE').value;
				//alert(data)
				var value = $.ajax({
					url: "ajax_get_term_from_course",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_TERM_MASTER_DIV').innerHTML = data;
						document.getElementById('PK_TERM_MASTER_LBL').classList.add("focused");
					}		
				}).responseText;
			});
		}
		
		function get_course_offering_detail(val){
			jQuery(document).ready(function($) { 
				if(val != ''  ) {
					var data  = 'PK_COURSE_OFFERING='+val+'&format=1';
					//alert(data)
					var value = $.ajax({
						url: "../instructor/ajax_get_course_details_for_attendance",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('PK_COURSE_OFFERING_DETAIL_DIV').innerHTML = data;
						}		
					}).responseText;
				}
			});
		}
		
		function get_student(val){
			jQuery(document).ready(function($) { 
				if(val != ''  ) {
					var data  = 'PK_COURSE_OFFERING='+val+'&required=1';
					//alert(data)
					var value = $.ajax({
						url: "ajax_build_final_grade_book_input",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							$('#grade_input_table tbody').empty();
							$('#grade_input_table tbody').append(data);
							
							jQuery('.date').datepicker({
								todayHighlight: true,
								orientation: "bottom auto"
							});
						}		
					}).responseText;
				}
			});
		}
		
		function delete_row(id,type){
		jQuery(document).ready(function($) {
			if(type == 'grade_input')
				document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE_GENERAL?>?';
			
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
			$("#DELETE_TYPE").val(type)
		});
	}
	function conf_delete(val,id){
		jQuery(document).ready(function($) {
			if(val == 1) {
				if($("#DELETE_TYPE").val() == 'grade_input') {
					var id = $("#DELETE_ID").val()
					$("#grade_input_table_"+id).remove()
				}
				
			}
			$("#deleteModal").modal("hide");
		});
	}
	
	function goto_grade_book(){
		if(document.getElementById('PK_COURSE_OFFERING').value != ''){
			window.location.href = 'course_offering?tab=gradeInputTab&id='+document.getElementById('PK_COURSE_OFFERING').value;
		} else {
			alert("<?=SELECT_COURSE_OFFERING_ERROR?>");
		}
		
	}
	</script>

	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
</body>

</html>