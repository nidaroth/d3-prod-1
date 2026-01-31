<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/program.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3) ){ 
	header("location:../index.php");
	exit;
}
if($_GET['tab'] == 'CourseData' || $_GET['tab'] == '')
	$Course_Data_tab = 'active';
else if($_GET['tab'] == 'CourseFees')
	$Course_Fees_tab = 'active';
else if($_GET['tab'] == 'CourseOffering')
	$Course_Offering_tab = 'active';
else
	$general_tab = 'active';
	
if($_GET['id'] == ''){
} else {
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
	<title>Course | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=PROGRAM?> </h4>
                    </div>
                </div>
				<form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<ul class="nav nav-tabs customtab" role="tablist">
									<li class="nav-item"> <a class="nav-link <?=$Course_Data_tab?>" data-toggle="tab" href="#Course_Data_tab" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down">Course Data</span></a> </li>
									<li class="nav-item"> <a class="nav-link <?=$Course_Fees_tab?>" data-toggle="tab" href="#Course_Fees_tab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">Course Fees</span></a> </li>
									<li class="nav-item"> <a class="nav-link <?=$Course_Offering_tab?>" data-toggle="tab" href="#Course_Offering_tab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">Course Offering</span></a> </li>
									<li class="nav-item"> <a class="nav-link <?=$Grade_Book_tab?>" data-toggle="tab" href="#Grade_Book_tab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">Grade Book</span></a> </li>
								</ul>
								
								<div class="card-body">
									<div class="tab-content">
										<div class="tab-pane <?=$Course_Data_tab?>" id="Course_Data_tab" role="tabpanel">
											<div class="row">
												<div class="col-sm-6">
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<input type="text" class="form-control required-entry" id="COURSE_CODE" name="COURSE_CODE">
																<span class="bar"></span>
																<label for="COURSE_CODE">Course Code</label>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<input type="text" class="form-control required-entry" id="TRANSCRIPT_CODE" name="TRANSCRIPT_CODE">
																<span class="bar"></span>
																<label for="TRANSCRIPT_CODE">Transcript Code</label>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<textarea class="form-control required-entry" id="COURSE_DESCRIPTION" name="COURSE_DESCRIPTION"></textarea>
																<span class="bar"></span>
																<label for="COURSE_DESCRIPTION">Course Description</label>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<textarea rows="5" class="form-control required-entry" id="FULL_COURSE_DESCRIPTION" name="FULL_COURSE_DESCRIPTION"></textarea>
																<span class="bar"></span>
																<label for="FULL_COURSE_DESCRIPTION">Full Course Description</label>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-6">
															<div class="row form-group">
																<div class="custom-control col-md-6">Active</div>
																<div class="custom-control custom-radio col-md-3">
																	<input type="radio" id="customRadio11" name="ACTIVE" value="1" class="custom-control-input">
																	<label class="custom-control-label" for="customRadio11">Yes</label>
																</div>
																<div class="custom-control custom-radio col-md-3">
																	<input type="radio" id="customRadio22" name="ACTIVE" value="0" class="custom-control-input">
																	<label class="custom-control-label" for="customRadio22">No</label>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="col-sm-6 theme-v-border">
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<input type="text" class="form-control required-entry" id="UNITS" name="UNITS">
																<span class="bar"></span>
																<label for="UNITS">Units</label>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<input type="text" class="form-control required-entry" id="FA_UNITS" name="FA_UNITS">
																<span class="bar"></span>
																<label for="FA_UNITS">FA Units</label>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<input type="text" class="form-control required-entry" id="HOURS" name="HOURS">
																<span class="bar"></span>
																<label for="HOURS">Hours</label>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<input type="text" class="form-control required-entry" id="PREP_HOURS" name="PREP_HOURS">
																<span class="bar"></span>
																<label for="PREP_HOURS">Prep Hours</label>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<input type="text" class="form-control required-entry" id="CLASS_SIZE" name="CLASS_SIZE">
																<span class="bar"></span>
																<label for="CLASS_SIZE">Class Size</label>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<select id="PK_LAB_COURSE" name="PK_LAB_COURSE" class="form-control required-entry" >
																	<option>-Select-</option>
																</select>
																<span class="bar"></span>
																<label for="CLASS_SIZE">Lab Course</label>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<select id="PK_COURSE_GROUP" name="PK_COURSE_GROUP" class="form-control required-entry" >
																	<option>-Select-</option>
																</select>
																<span class="bar"></span>
																<label for="PK_COURSE_GROUP">Course Group</label>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<select id="PK_COURSE_TYPE" name="PK_COURSE_TYPE" class="form-control required-entry" >
																	<option>-Select-</option>
																</select>
																<span class="bar"></span>
																<label for="PK_COURSE_TYPE">Course Type</label>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<select id="PK_DEFAULT_ATTENDANCE" name="PK_DEFAULT_ATTENDANCE" class="form-control required-entry" >
																	<option>-Select-</option>
																</select>
																<span class="bar"></span>
																<label for="PK_DEFAULT_ATTENDANCE">Default Attendance</label>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<select id="ALLOW_ONLINE_ENROLLMENT" name="ALLOW_ONLINE_ENROLLMENT" class="form-control">
																	<option ></option>
																	<option value="1" <? if($ALLOW_ONLINE_ENROLLMENT == 1) echo "selected"; ?> >Yes</option>
																	<option value="2" <? if($ALLOW_ONLINE_ENROLLMENT == 2) echo "selected"; ?> >No</option>
																</select>
																<span class="bar"></span>
																<label for="ALLOW_ONLINE_ENROLLMENT">Allow Online Enrollment</label>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<input type="text" class="form-control" id="EXTERNAL_ID" name="EXTERNAL_ID">
																<span class="bar"></span>
																<label for="EXTERNAL_ID">External ID</label>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="tab-pane <?=$Course_Fees_tab?>" id="Course_Fees_tab" role="tabpanel">							
											<div class="row text-center">
												<div class="col-md-3">
													<b>Fee</b>
												</div> 
												<div class="col-md-3">
													<b>Description</b>
												</div>
												<div class="col-md-2">
													<b>Amount</b>
												</div> 
												<div class="col-md-2">
													<b>ISBN</b>
												</div>
												<div class="col-md-2">
													<b>Cost</b>
												</div>
											</div>
											<div id="Course_Fees_div">
												<div class="row" id="Course_Fees_0">
													<!-- <label for="input-text" class="col-sm-4 control-label">&nbsp;</label> -->
													<div class="col-sm-3">
														<select id="" name="FEE[]" class="form-control required-entry" >
															<option>-Select-</option>
														</select>
													</div>
													<div class="col-sm-3">
														<input type="text" class="form-control required-entry" id="" name="DESCRIPTION[]">
													</div>
													<div class="col-sm-2">
														<input type="text" class="form-control required-entry" id="" name="AMOUNT[]">
													</div>
													<div class="col-sm-2">
														<input type="text" class="form-control required-entry" id="" name="ISBN[]">
													</div>
													<div class="col-sm-2">
														<input type="text" class="form-control required-entry" id="" name="COST[]">
													</div>
												</div>
												<div class="row" id="Course_Fees_1">
													<!-- <label for="input-text" class="col-sm-4 control-label">&nbsp;</label> -->
													<div class="col-sm-3">
														<select id="" name="FEE[]" class="form-control required-entry" >
															<option>-Select-</option>
														</select>
													</div>
													<div class="col-sm-3">
														<input type="text" class="form-control required-entry" id="" name="DESCRIPTION[]">
													</div>
													<div class="col-sm-2">
														<input type="text" class="form-control required-entry" id="" name="AMOUNT[]">
													</div>
													<div class="col-sm-2">
														<input type="text" class="form-control required-entry" id="" name="ISBN[]">
													</div>
													<div class="col-sm-2">
														<input type="text" class="form-control required-entry" id="" name="COST[]">
													</div>
												</div>
												<div class="row" id="Course_Fees_2">
													<!-- <label for="input-text" class="col-sm-4 control-label">&nbsp;</label> -->
													<div class="col-sm-3">
														<select id="" name="FEE[]" class="form-control required-entry" >
															<option>-Select-</option>
														</select>
													</div>
													<div class="col-sm-3">
														<input type="text" class="form-control required-entry" id="" name="DESCRIPTION[]">
													</div>
													<div class="col-sm-2">
														<input type="text" class="form-control required-entry" id="" name="AMOUNT[]">
													</div>
													<div class="col-sm-2">
														<input type="text" class="form-control required-entry" id="" name="ISBN[]">
													</div>
													<div class="col-sm-2">
														<input type="text" class="form-control required-entry" id="" name="COST[]">
													</div>
												</div>
												<div class="row" id="Course_Fees_3">
													<!-- <label for="input-text" class="col-sm-4 control-label">&nbsp;</label> -->
													<div class="col-sm-3">
														<select id="" name="FEE[]" class="form-control required-entry" >
															<option>-Select-</option>
														</select>
													</div>
													<div class="col-sm-3">
														<input type="text" class="form-control required-entry" id="" name="DESCRIPTION[]">
													</div>
													<div class="col-sm-2">
														<input type="text" class="form-control required-entry" id="" name="AMOUNT[]">
													</div>
													<div class="col-sm-2">
														<input type="text" class="form-control required-entry" id="" name="ISBN[]">
													</div>
													<div class="col-sm-2">
														<input type="text" class="form-control required-entry" id="" name="COST[]">
													</div>
												</div>
											</div>										
										</div>
										<div class="tab-pane <?=$Course_Offering_tab?>" id="Course_Offering_tab" role="tabpanel" style="overflow: auto;">
											<table id="Course_Fees_div" style="table-layout:fixed; width: 100%; margin-bottom: 15px;">
												<tbody>
													<tr>
														<td width="200">Term</td>
														<td width="200">Status</td>
														<td width="200">Instructor</td>
														<td width="200">Times</td>
														<td width="200">Room</td>
														<td width="200">Class Size</td>
														<td width="200">Attendance Type</td>
														<td width="200">Attendance Default</td>
														<td width="200">Session</td>
														<td width="200">Session No.</td>
														<td width="200">Campus</td>
													</tr>
													<tr>
														<td width="200">
															<input type="text" class="form-control required-entry date" id="" name="TERM[]">
														</td>
														<td width="200">
															<select id="" name="STATUS[]" class="form-control required-entry" >
																<option>-Select-</option>
															</select>
														</td>
														<td width="200">
															<select id="" name="INSTRUCTOR[]" class="form-control required-entry" >
																<option>-Select-</option>
															</select>
														</td>
														<td width="200">
															<div class="d-flex">
																<input type="text" class="form-control required-entry timepicker" placeholder="Start Time" id="" name="START_TIME[]" style="width:50%" >
																<input type="text" class="form-control required-entry timepicker" placeholder="End Time" id="" name="END_TIME[]" style="width:50%">
															</div>
														</td>
														<td width="200">
															<select id="" name="ROOM[]" class="form-control required-entry" >
																<option>-Select-</option>
															</select>
														</td>
														<td width="200">
															<input type="text" class="form-control required-entry" id="" name="CLASS_SIZE[]">
														</td>
														<td width="200">
															<select id="PK_ATTENDANCE_TYPE" name="PK_ATTENDANCE_TYPE" class="form-control">
																<option selected></option>
																<? $res_type = $db->Execute("select * from M_ATTENDANCE_TYPE order by ATTENDANCE_TYPE ASC");
																while (!$res_type->EOF) { ?>
																	<option value="<?=$res_type->fields['PK_ATTENDANCE_TYPE']?>"  <? if($res_type->fields['PK_ATTENDANCE_TYPE'] == $PK_ATTENDANCE_TYPE) echo "selected"; ?> ><?=$res_type->fields['ATTENDANCE_TYPE']?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
														</td>
														<td width="200">
															<select id="ATTENDANCE_DEFAULT" name="ATTENDANCE_DEFAULT" class="form-control">
																<option selected></option>
																<? $res_type = $db->Execute("select * from M_ATTENDANCE_CODE order by ATTENDANCE_CODE ASC");
																while (!$res_type->EOF) { ?>
																	<option value="<?=$res_type->fields['PK_ATTENDANCE_CODE']?>"  <? if($res_type->fields['PK_ATTENDANCE_CODE'] == $PK_ATTENDANCE_CODE) echo "selected"; ?> ><?=$res_type->fields['ATTENDANCE_CODE']?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
														</td>
														<td width="200">
															<select id="PK_SESSION" name="PK_SESSION" class="form-control">
																<option selected></option>
																<? $res_type = $db->Execute("select * from M_SESSION order by DISPLAY_ORDER ASC");
																while (!$res_type->EOF) { ?>
																	<option value="<?=$res_type->fields['PK_SESSION']?>"  <? if($res_type->fields['PK_SESSION'] == $PK_SESSION) echo "selected"; ?> ><?=$res_type->fields['SESSION']?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
														</td>
														<td width="200">
															<input type="text" class="form-control required-entry" id="" name="SESSION_NO[]">
														</td>
														<td width="200">
															<select id="" name="CAMPUS[]" class="form-control required-entry" >
																<option>-Select-</option>
															</select>
														</td>
													</tr>
												</tbody>
											</table>
										</div>
										<div class="tab-pane <?=$Grade_Book_tab?>" id="Grade_Book_tab" role="tabpanel" style="overflow: auto;">
											<table id="Course_Fees_div" style="table-layout:fixed; width: 100%; margin-bottom: 15px;">
												<tbody>
													<tr>
														<td width="100">Column</td>
														<td width="100">Code</td>
														<td width="100">Description</td>
														<td width="100">Type</td>
														<td width="100">Period</td>
														<td width="100">Points</td>
														<td width="100">Weight</td>
														<td width="100">Weighted Pts.</td>
													</tr>
													<tr>
														<td width="100">
															<input type="text" class="form-control required-entry" id="" name="COLUMN[]">
														</td>
														<td width="100">
															<input type="text" class="form-control required-entry" id="" name="CODE[]">
														</td>
														<td width="100">
															<input type="text" class="form-control required-entry" id="" name="DESCRIPTION[]">
														</td>
														<td width="100">
															<select id="" name="TYPE[]" class="form-control required-entry" >
																<option>-Select-</option>
															</select>
														</td>
														<td width="100">
															<input type="text" class="form-control required-entry" id="" name="PERIOD[]">
														</td>
														<td width="100">
															<input type="text" class="form-control required-entry" id="" name="POINTS[]">
														</td>
														<td width="100">
															<input type="text" class="form-control required-entry" id="" name="WEIGHT[]">
														</td>
														<td width="100">
															<input type="text" class="form-control required-entry" id="" name="WEIGHTED_PTS[]">
														</td>
													</tr>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-md-12">
							<div class="form-group m-b-5"  style="text-align:right" >
								<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_CONTINUE?></button>
								<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_EXIT?></button>
								
								<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_program.php'" ><?=CANCEL?></button>
								
								<input type="hidden" name="SAVE_CONTINUE" id="SAVE_CONTINUE" value="0" />
								<input type="hidden" id="current_tab" name="current_tab" value="0" >
								<br /><br />
							</div>
						</div>
					</div>
				</form>
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
	<script type="text/javascript">
	<? if($_GET['tab'] != '') { ?>
		var current_tab = '<?=$_GET['tab']?>';
	<? } else { ?>
		var current_tab = 'generalTab';
	<? } ?>
	jQuery(document).ready(function($) { 
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			current_tab = $(e.target).attr("href") // activated tab
			//alert(current_tab)
		});
	});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	function validate_form(val){
		document.getElementById('current_tab').value   = current_tab;
		document.getElementById("SAVE_CONTINUE").value = val;
		
		var valid = new Validation('form1', {onSubmit:false});
		var result = valid.validate();
		if(result == true)
			document.form1.submit();
	}
	
	var requirement_id = '<?=$requirement_id?>';
	function add_requirement(){
		jQuery(document).ready(function($) { 
			var data  = 'requirement_id='+requirement_id;
			var value = $.ajax({
				url: "ajax_program_requirement.php",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					//alert(data)
					$('#requirement_div').append(data);
					requirement_id++;
				}		
			}).responseText;
		});
	}
	
	function delete_row(id,type){
		jQuery(document).ready(function($) {
			if(type == 'requirement')
				document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.REQUIREMENT?>?';
			
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
			$("#DELETE_TYPE").val(type)
		});
	}
	function conf_delete(val,id){
		jQuery(document).ready(function($) {
			if(val == 1) {
				if($("#DELETE_TYPE").val() == 'requirement')
					window.location.href = 'program.php?act=req_del&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
				
			} else
				$("#deleteModal").modal("hide");
		});
	}
	</script>

</body>

</html>