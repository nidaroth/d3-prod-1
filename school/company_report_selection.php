<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/custom_report.php");
require_once("../language/menu.php");
require_once("../language/student.php");
require_once("../language/student_contact.php");
require_once("../language/student_report_selection.php");
require_once("check_access.php");

if (check_access('REPORT_CUSTOM_REPORT') == 0) {
	header("location:../index");
	exit;
}

if ($_GET['id']) {
	$query_select = "SELECT * FROM S_CUSTOM_COMPANY_REPORT WHERE PK_CUSTOM_COMPANY_REPORT = " . $_GET['id'];
	$db_result_select  = $db->Execute($query_select);
	while (!$db_result_select->EOF) {
		$saved_filters =  (array) json_decode($db_result_select->fields['FILTERS']);
		$FILTER_NAME = $db_result_select->fields['FILTER_NAME'];
		$FIELDS_TO_SHOW =  $db_result_select->fields['FIELDS_TO_SHOW'];
		$db_result_select->MoveNext();
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
	<title><?= MNU_COMPANY_REPORT_SELECTION ?> | <?= $title ?></title>
	<link rel="stylesheet" href="../assets/css/cutom_report.css">
	<style>
		.dt-buttons .dt-button {
			background: #190962;
		}
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
							<?= MNU_COMPANY_REPORT_SELECTION ?>
						</h4>
					</div>
				</div>
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" autocomplete="off">
					<div class="row">
						<div class="col-3">
							<div class="card">
								<div class="card-body p-0" style="padding:10px !important;">
									<div class="row">
										<div class="col-md-12">
											<div class="d-flex">
												<div class="col-12 col-sm-12 mt-4 form-group">
													<input id="FILTER_NAME" name="FILTER_NAME" type="text" class="form-control required-entry" value="<?= $FILTER_NAME ?>">
													<span class="bar"></span>
													<label for="FILTER_NAME"><?= FILTER_NAME ?> <span style="color : red">*<span></label>
												</div>
											</div>
										</div>
										<div class="col-md-12 pb-3">
											<input type="hidden" name="SAVE_CONTINUE" id="SAVE_CONTINUE" value="0">
											<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info"><?= SAVE_CONTINUE ?></button>
											<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info"><?= SAVE_EXIT ?></button>
											<button type="button" onclick="window.location.href='manage_company_report_selection'" class="btn waves-effect waves-light btn-dark"><?= CANCEL ?></button>
										</div>
									</div>
								</div>
							</div>
							<div class="card d-none">
								<div class="card-body p-0" style="padding:10px !important;">
									<div class="row">
										<?php


										$filters = [
											["PK_CAMPUS", "Campus", "PK_CAMPUS[]", "multiple", "selected_value", "select PK_CAMPUS,OFFICIAL_CAMPUS_NAME from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by OFFICIAL_CAMPUS_NAME ASC"],
											["COMPANY_CITY", "Company City", "COMPANY_CITY", "INPUT", "selected_value"],
											["COMPANY_NAME", "Company Name", "COMPANY_NAME", "INPUT", "selected_value"],
											["COMPANY_SOURCE", "COMPANY SOURCE", "COMPANY_SOURCE[]", "multiple", "selected_value", "select PK_COMPANY_SOURCE, COMPANY_SOURCE from M_COMPANY_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY COMPANY_SOURCE ASC"],
											["COMPANY_DATE_CREATED_BEGIN_DATE", "Company Created Begin Date", "COMPANY_DATE_CREATED_BEGIN_DATE", "date", "selected_value"],
											["COMPANY_DATE_CREATED_END_DATE", "Company Created End Date", "COMPANY_DATE_CREATED_END_DATE", "date", "selected_value"],
											["COMPANY_FAX", "Company Fax", "COMPANY_FAX", "INPUT", "selected_value"],
											["COMPANY_MAIN_CONTACT", "MAIN CONTACT", "COMPANY_MAIN_CONTACT[]", "multiple", "selected_value", "select PK_COMPANY_CONTACT, NAME from S_COMPANY_CONTACT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY NAME ASC"],
											// ["COMPANY_OPEN_JOB", "Company Open Job", "COMPANY_OPEN_JOB", "INPUT", "selected_value"],
											["COMPANY_PHONE", "Company Phone", "COMPANY_PHONE", "INPUT", "selected_value"],
											["COMPANY_SCHOOL_EMPLOYEE", "SCHOOL EMPLOYEE", "COMPANY_SCHOOL_EMPLOYEE[]", "multiple", "selected_value", "SELECT * FROM (select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER) AS TEMP GROUP BY PK_EMPLOYEE_MASTER order by NAME ASC"],
											["COMPANY_STATE", "COMPANY STATE", "COMPANY_STATE[]", "multiple", "selected_value", "select PK_STATES, STATE_NAME from Z_STATES WHERE 1 = 1  ORDER BY STATE_NAME ASC"],
											["COMPANY_STATUS", "COMPANY STATUS", "COMPANY_STATUS[]", "multiple", "selected_value", "select PK_PLACEMENT_COMPANY_STATUS, PLACEMENT_COMPANY_STATUS from M_PLACEMENT_COMPANY_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY PLACEMENT_COMPANY_STATUS ASC"],
											// ["COMPANY_TOTAL_JOBS", "Company Total Jobs", "COMPANY_TOTAL_JOBS", "INPUT", "selected_value", "select PK_PLACEMENT_COMPANY_STATUS, PLACEMENT_COMPANY_STATUS from M_PLACEMENT_COMPANY_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY PLACEMENT_COMPANY_STATUS ASC"],
											["COMPANY_TYPE", "Type", "COMPANY_TYPE[]", "multiple", "selected_value", "select PK_PLACEMENT_TYPE, TYPE from M_PLACEMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ORDER BY TYPE ASC"],
											["COMPANY_WEBSITE", "COMPANY WEBSITE", "COMPANY_WEBSITE", "INPUT", "selected_value"],
											["COMPANY_EVENT_COMPANY_CONTACT", "Event Contact", "COMPANY_EVENT_COMPANY_CONTACT[]", "multiple", "selected_value", "select PK_COMPANY_CONTACT, NAME from S_COMPANY_CONTACT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY NAME ASC"],
											["COMPANY_EVENT_COMPLETE", "Company Event Completed", "COMPANY_EVENT_COMPLETE", "SELECT", "selected_value", [
												["2", "No"],
												["1", "Yes"]
											]], ["COMPANY_EVENT_BEGIN_DATE", "Company Event Begin Date", "COMPANY_EVENT_BEGIN_DATE", "date", "selected_value"],
											["COMPANY_EVENT_END_DATE", "Company Event End Date", "COMPANY_EVENT_END_DATE", "date", "selected_value"],
											["COMPANY_EVENT_TYPE", "EVENT TYPE", "COMPANY_EVENT_TYPE[]", "multiple", "selected_value", "select PK_PLACEMENT_COMPANY_EVENT_TYPE, PLACEMENT_COMPANY_EVENT_TYPE from M_PLACEMENT_COMPANY_EVENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY PLACEMENT_COMPANY_EVENT_TYPE ASC"],
											["COMPANY_EVENT_FOLLOWUP_BEGIN_DATE", "Company Event Follow Up Begin Date", "COMPANY_EVENT_FOLLOWUP_BEGIN_DATE", "date", "selected_value"],
											["COMPANY_EVENT_FOLLOWUP_END_DATE", "Company Event Follow Up End Date", "COMPANY_EVENT_FOLLOWUP_END_DATE", "date", "selected_value"],
											["COMPANY_EVENT_SCHOOL_EMPLOYEE", "EVENT SCHOOL EMPLOYEE", "COMPANY_EVENT_SCHOOL_EMPLOYEE[]", "multiple", "selected_value", "SELECT * FROM (select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER) AS TEMP GROUP BY PK_EMPLOYEE_MASTER order by NAME ASC"],
											["COMPANY_JOB_FULL_PART_TIME", "Company Job Full/Part Time", "COMPANY_JOB_FULL_PART_TIME", "SELECT", "selected_value", [
												["1", "Full Time"],
												["2", "Part Time"]
											]], ["COMPANY_JOB_CANCELLED_BEGIN_DATE", "Company Job Cancelled Begin Date", "COMPANY_JOB_CANCELLED_BEGIN_DATE", "date", "selected_value"],
											["COMPANY_JOB_CANCELLED_END_DATE", "Company Job Cancelled End Date", "COMPANY_JOB_CANCELLED_END_DATE", "date", "selected_value"],
											["COMPANY_JOB_FILLED_BEGIN_DATE", "Company Job Filled Begin Date", "COMPANY_JOB_FILLED_BEGIN_DATE", "date", "selected_value"],
											["COMPANY_JOB_FILLED_END_DATE", "Company Job Filled End Date", "COMPANY_JOB_FILLED_END_DATE", "date", "selected_value"],
											["COMPANY_JOB_NO", "Company Job No.", "COMPANY_JOB_NO", "INPUT", "selected_value"],
											["COMPANY_JOB_POSTED_DATE_BEGIN_DATE", "Company Job Posted Begin Date", "COMPANY_JOB_POSTED_DATE_BEGIN_DATE", "date", "selected_value"],
											["COMPANY_JOB_POSTED_DATE_END_DATE", "Company Job Posted End Date", "COMPANY_JOB_POSTED_DATE_END_DATE", "date", "selected_value"],
											["COMPANY_JOB_TITLE", "Company Job Title", "COMPANY_JOB_TITLE", "INPUT", "selected_value"],
											["COMPANY_JOB_OPEN_JOB", "Company Open Job", "COMPANY_JOB_OPEN_JOB", "INPUT", "selected_value"],
											["COMPANY_JOB_PAY_AMOUNT_FROM", "Company Job Pay Amount(From)", "COMPANY_JOB_PAY_AMOUNT_FROM", "INPUT", "selected_value"],
											["COMPANY_JOB_PAY_AMOUNT_TO", "Company Job Pay Amount(To)", "COMPANY_JOB_PAY_AMOUNT_TO", "INPUT", "selected_value"]

										];

										$manage_columns = [
											['DATE_CREATED', 'COMPANY CREATED DATE'],
											['FAX', 'Company Fax'],
											['PK_COMPANY_SOURCE', 'COMPANY SOURCE'],
											['PK_STATES', 'COMPANY STATE'],
											['PK_PLACEMENT_COMPANY_STATUS', 'COMPANY STATUS'],
											['PK_COMPANY_CONTACT', 'MAIN CONTACT'],
											['PK_PLACEMENT_TYPE', 'Type'],
											// ['COMPANY_CITY', 'Company City'],
											// ['COMPANY_NAME', 'Company Name'],
											
											// ['COMPANY_DATE_CREATED_BEGIN_DATE' , 'Company Created Begin Date'],
											// ['COMPANY_DATE_CREATED_END_DATE' , 'Company Created End Date'],
											
											
											// ['COMPANY_OPEN_JOB', 'Company Open Job'],
											// ['PHONE', 'Company Phone'],
											// ['PK_COMPANY_ADVISOR', 'SCHOOL EMPLOYEE'],
											
											
											
											// ['COMPANY_TOTAL_JOBS', 'Company Total Jobs'],
											
											// ['COMPANY_WEBSITE', 'COMPANY WEBSITE'],
											// ['COMPANY_EVENT_COMPANY_CONTACT', 'Event Contact'],
											// ['COMPANY_EVENT_COMPLETE', 'Company Event Completed'],
											// ['COMPANY_EVENT_BEGIN_DATE' , 'Company Event Begin Date'],
											// ['COMPANY_EVENT_END_DATE' , 'Company Event End Date'],
											// ['COMPANY_EVENT_TYPE', 'EVENT TYPE'],
											// ['COMPANY_EVENT_FOLLOWUP_BEGIN_DATE' , 'Company Event Follow Up Begin Date'],
											// ['COMPANY_EVENT_FOLLOWUP_END_DATE' , 'Company Event Follow Up End Date'],
											// ['COMPANY_EVENT_SCHOOL_EMPLOYEE', 'EVENT SCHOOL EMPLOYEE'],
											// ['COMPANY_JOB_FULL_PART_TIME', 'Company Job Full/Part Time'],
											// ['COMPANY_JOB_CANCELLED_BEGIN_DATE' , 'Company Job Cancelled Begin Date'],
											// ['COMPANY_JOB_CANCELLED_END_DATE' , 'Company Job Cancelled End Date'],
											// ['COMPANY_JOB_FILLED_BEGIN_DATE' , 'Company Job Filled Begin Date'],
											// ['COMPANY_JOB_FILLED_END_DATE' , 'Company Job Filled End Date'],
											// ['COMPANY_JOB_NO', 'Company Job No.'],
											// ['COMPANY_JOB_POSTED_DATE_BEGIN_DATE' , 'Company Job Posted Begin Date'],
											// ['COMPANY_JOB_POSTED_DATE_END_DATE' , 'Company Job Posted End Date'],
											// ['COMPANY_JOB_TITLE', 'Company Job Title'],
											// ['COMPANY_JOB_OPEN_JOB', 'Company Open Job'],
											// ['COMPANY_JOB_PAY_AMOUNT_FROM' , 'Company Job Pay Amount(From)'],
											// ['COMPANY_JOB_PAY_AMOUNT_TO' , 'Company Job Pay Amount(To)']
										];
										?>
										<!-- <div class="col-md-12">
											<h4>Manage Columns</h4>
											<select name="selected_columns[]" id="selected_columns" multiple class="form-control">
												<?php
												#used to get all filters
												foreach ($manage_columns as $key => $value) {
													if(in_array($value[0] , $FIELDS_TO_SHOW)){
														$selected  = 'selected';
													}else{
														$selected = '';
													}
													echo '<option value="' . $value[0] . '"'.$selected.' >' . $value[1] . '</option>';
												}


												?>
											</select>

										</div> -->

									</div>
								</div>
							</div>
							<div class="card">
								<div class="card-body p-0 chnagegrabber" style="padding:10px !important;">
									<h4><?= FILTER ?>
										<button onclick="search_companies()" type="button" class="btn waves-effect waves-light btn-info float-right"><?= SEARCH ?> <span class="badge" style=" margin-left : 5px;padding: 4px; padding-right: 8px; padding-left: 8px; color:white ; background-color: green;display:none;font-weight:600" id="new_filters">0</span></button>
									</h4>


									<input type="checkbox" class="mt-3 mb-2" name="show_contacts" id="show_contacts"> Show contacts
									<button class="accordion" type="button"><?= COMPANY ?></button>
									<div class="panel" id="company_filter_pane">
										<br /><br />
										<?php



										foreach ($filters as $ele) {
											$id = $ele[0];
											$lable = $ele[1];
											$name = $ele[2];
											$index = str_replace(']', '', str_replace('[', '', $name));

											$type = $ele[3];
											// $ele[4] == 'selected_value' ? $selected_values =  null : $selected_values =  $ele[4]; 
											$options = isset($ele[5]) ? $ele[5] : false;
											if ($type == 'INPUT') {
												makeinput($lable, $id, $name, $saved_filters[$index]);
											} else if ($type == 'SELECT') {
												makesingleselect($lable, $id, $name, $saved_filters[$index], $options);
											} elseif ($type == 'multiple') {
												makemultiselect($lable, $id, $name, $saved_filters[$index], $options);
											} elseif ($type == 'date') {
												makedateinput($lable, $id, $name, $saved_filters[$index]);
											}
										}


										function makeinput($lable, $id, $name, $selected_values)
										{
											echo '<div class="col-12 form-group">
											<input class="form-control" type="text" value="' . $selected_values . '" name="' . $name . '" id="' . $id . '">
											<span class="bar"></span>
											<label for="' . $id . '">' . $lable . '</label>
										</div>';
										}

										function makesingleselect($lable, $id, $name, $selected_values, $options)
										{
											echo '<div class="col-12 form-group">
										  <select id="' . $id . '" name="' . $name . '" class="form-control">';
											echo '<option></option>';
											foreach ($options as $key => $value) {

												if (gettype($options) == 'array')
													echo '<option value="' . $value[0] . '" >' . $value[1] . '</option>';
											}
											echo '</select>';
											echo '<label for="' . $id . '">' . $lable . ' </label></div>';
										}
										function makemultiselect($lable, $id, $name, $selected_values, $options)
										{
											global $db;
											// echo "<script>alert('".$selected_values."')</script>";
											echo '<div class="col-12 form-group">';

											echo '<select id="' . $id . '" name="' . $name . '" multiple class="form-control multi_selector">';
											$res_type = $db->Execute($options);
											while (!$res_type->EOF) {
												$selected = '';

												foreach ($selected_values as $selected_value) {

													if ($selected_value == array_values($res_type->fields)[0]) {
														$selected = 'selected';
														break;
													}
												}
												echo '<option value="' . array_values($res_type->fields)[0] . '" ' . $selected . '>' . array_values($res_type->fields)[1] . '</option>';
												$res_type->MoveNext();
											}
											echo '</select>';

											echo '</div>';
										}
										function makedateinput($lable, $id, $name, $selected_values)
										{
											echo '<div class="col-12 form-group">
											<input class="form-control date" type="text" value="' . $selected_values . '" name="' . $name . '" id="' . $id . '">
											<span class="bar"></span>
											<label for="' . $id . '">' . $lable . '</label>
										</div>';
										}

										?>

									</div>
								</div>


							</div>
						</div>
						<div class="col-9">
							<div class="row">
								<div class="col-12">
									<ul class="nav nav-pills">
										<li class="custom-pill-li active">
											<input id="optDaily" checked name="intervaltype" type="radio" data-target="#unselected-students">
											<label class="custom-lable" for="optDaily">Unselected Companies <b id="total_count_id"></b></label>
										</li>
										<li class="custom-pill-li">
											<input id="optWeekly" name="intervaltype" type="radio" data-target="#selected-students">
											<label class="custom-lable" for="optWeekly">Selected Companies <b id="selected_count_id"></b></label>
										</li>
									</ul>
								</div>
								<div class="col-12">
									<div class="card">
										<div class="card-body">
											<div class="row">
												<div class="container">

													<div class="tab-content">
														<div id="unselected-students" class="tab-pane active">
															<h4>Unselected</h4>
															<div class="col-md-3 mb-3 float-right">
															<span style="color : #0e79e5"><?= COLUMNS ?></span>

																<select name="selected_columns[]" id="selected_columns" multiple class="form-control">
																	<?php
																	#used to get all filters
																	foreach ($manage_columns as $key => $value) {
																		if(in_array($value[0] , explode(',',$FIELDS_TO_SHOW))){
																			$selected  = 'selected';
																		}else{
																			$selected = '';
																		}
																		echo '<option value="' . $value[0] . '"'.$selected.' >' . $value[1] . '</option>';
																	}


																	?>
																</select> 

															</div>
															<!-- ✅ M_COMPANY_SOURCE <br>
															✅ M_PLACEMENT_COMPANY_EVENT_TYPE <br>

															✅M_PLACEMENT_COMPANY_STATUS <br>
															✅(ommited taking values from above table) M_PLACEMENT_COMPANY_STATUS_MASTER <br>
															✅ S_COMPANY <br>
															✅S_COMPANY_CAMPUS <br>
															S_COMPANY_CONTACT <br>
															✅S_COMPANY_EVENT <br>
															❌S_COMPANY_EVENT_DOCUMENTS <br>
															S_COMPANY_JOB <br>
															S_COMPANY_JOB_CAMPUS <br>
															🍊 M_PLACEMENT_COMPANY_QUESTION_GROUP <br>
															❌ M_PLACEMENT_COMPANY_QUESTIONNAIRE <br>
															❌ S_COMPANY_QUESTIONNAIRE <br>
															❌ S_CUSTOM_COMPANY_REPORT <br> -->
															<div class="table-responsive">
																<!-- <button onclick="refresh_datatable()" class="btn " type="button"> Reset table</button> -->
																<button id="add_company_btn" type="button" class="btn waves-effect waves-light btn-info float-right d-none" onclick="add_companies()"> Add to selected</button>
																<table id="student_search_table" class="table table-striped table-bordered no-wrap mb-0">
																	<thead>
																		<tr id="dynamic_columns">
																			<th><input name="company_add_all" id="company_add_all" type="checkbox" /></th>
																			<th>Company Name</th>
																			<th>City</th>
																			<th>Date Created</th>
																			<th>Phone</th>
																			<th>Website</th>
																			<th>Total Jobs</th>
																			<th>Open Jobs</th>
																		</tr>
																	</thead>
																</table>
															</div>
															<div id="search_list" style="max-height:500px;overflow: auto;">
															</div>
														</div>
														<div id="selected-students" class="tab-pane">
															<h4>Selected</h4>
															<div id="added_list" style="max-height:500px;overflow: auto;">
																<button type="button" class="btn waves-effect waves-light btn-danger float-right mb-2" onclick="del_companies()">Bulk remove</button>
																<table id="company_selected_table" class="table table-striped table-bordered">
																	<thead>
																		<tr id="dynamic_columns">
																			<th><input name="company_del_all" id="company_del_all" type="checkbox" /></th>
																			<th>Company Name</th>
																			<th>City </th>
																			<th>Date Created </th>
																			<th>Phone </th>
																			<th>Website </th>
																		</tr>
																	</thead>
																	<tbody>
																		<?
																		//$_REQUEST['str'] = $PK_STUDENT_ENROLLMENT;
																		//$_REQUEST['str1'] = '';
																		//include("ajax_get_student_details_from_id_for_student_selection.php"); 
																		//include("ajax_load_selected_company_list.php")

																		?>
																	</tbody>
																</table>
															</div>
														</div>
													</div>
												</div>


											</div>

										</div>
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
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<style src="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css"></style>
	<style src="https://cdn.datatables.net/colreorder/1.6.2/css/colReorder.bootstrap4.min.css"></style>
	<!-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script> -->
	<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
	<!-- <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script> -->
	<script src="https://cdn.datatables.net/colreorder/1.6.2/js/dataTables.colReorder.min.js"></script>
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>

	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/colreorder/1.6.2/css/colReorder.bootstrap4.min.css">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedcolumns/4.2.2/css/fixedColumns.bootstrap4.min.css">
	
	<!-- <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> -->
	<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
	<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
	<script src="https://cdn.datatables.net/fixedcolumns/4.2.2/js/dataTables.fixedColumns.min.js"></script>

	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />

	<!-- for datepickers -->
	<!-- <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
	<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
	<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
	<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script> -->
	<script>
		// jQuery.noConflict();


		jQuery(document).ready(function() {
			jQuery(function() {
				// jQuery('input[name="daterange"]').daterangepicker({
				// 	opens: 'left',
				// 	showDropdowns: true,
				// 	locale: {
				// 		format: 'MM/DD/YYYY'
				// 	}
				// });

			});
		});
	</script>

	<script>
		var acc = document.getElementsByClassName("accordion");
		var i;
		var t;
		var tselected;
		for (i = 0; i < acc.length; i++) {
			acc[i].addEventListener("click", function() {
				this.classList.toggle("acc_active");
				var panel = this.nextElementSibling;
				if (panel.style.maxHeight) {
					panel.style.maxHeight = null;
					panel.style.overflow = 'hidden'
				} else {
					panel.style.maxHeight = panel.scrollHeight + "px";
					panel.style.overflow = 'visible'
				}
			});
		}

		var filters = [];

		function getallfilters(divID) {

			var div = document.getElementById(divID);
			jQuery(document).ready(function() {
				jQuery(div).find('input, select, textarea')
					.each(function() {

						element = jQuery(this).attr('id');
						// alert(jQuery(this).prop('nodeName'));
						console.log('element', element)
						// alert(jQuery(this).prop('id'));

						// alert('hello'+jQuery(this).prop('nodeName'));
						if (jQuery(this).prop('nodeName') == 'INPUT' && jQuery(this).hasClass('date')) {

							filters.push([
								element,
								jQuery(this).parent().find('label').text(),
								jQuery(this).attr('name'),
								'date',
								'selected_value'
							])
						} else if (jQuery(this).prop('nodeName') == 'INPUT') {
							filters.push([
								element,
								jQuery(this).parent().find('label').text(),
								jQuery(this).attr('name'),
								'INPUT',
								'selected_value'
							])
						} else if (jQuery(this).prop('multiple')) {
							filters.push([
								element,
								jQuery(this).parent().find('.multiselect-selected-text').text(),
								jQuery(this).attr('name'),
								'multiple',
								'selected_value'
							])
						} else if (jQuery(this).prop('nodeName') == 'SELECT') {
							var options = [];
							jQuery("#" + element + " option").each(function() {
								if (this.value != '')
									options.push([this.value, jQuery(this).text()])
								// alert(this.value) // or $(this).val()
							});
							filters.push([
								element,
								jQuery(this).parent().find('label').text(),
								jQuery(this).attr('name'),
								'SELECT',
								'selected_value',
								options
							])
						}




						// console.log('filters', filters);

					});
			});
			// $(div).find('input:radio, input:checkbox').each(function() {
			// 	$(this).removeAttr('checked');
			// 	$(this).removeAttr('selected');
			// });

		}

		jQuery(document).ready(function() {

			// getallfilters('company_filter_pane');
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});

			jQuery("select").map(function(i, el) {
				if (jQuery(el).prop('multiple')) {
					var id = jQuery(el).attr('id');
					console.log('id , lable', id, lable);
					var lable = jQuery(el).attr('name');
					lable = lable.replaceAll('[', '');
					lable = lable.replaceAll(']', '');
					lable = lable.replaceAll('_', ' ');

					if (id == 'selected_columns') {
						jQuery(document.getElementById(id)).multiselect({
							includeSelectAllOption: true,
							nonSelectedText: 'Show columns',
							numberDisplayed: 1,
							allSelectedText: 'All ' + lable,
							nSelectedText: lable + ' selected',
							onDropdownHide: function(event) {
								// alert($('#selected_columns').val());
								search_companies();
							}
						})
					} else {
						jQuery(document.getElementById(id)).multiselect({
							includeSelectAllOption: true,
							nonSelectedText: lable,
							numberDisplayed: 1,
							allSelectedText: 'All ' + lable,
							nSelectedText: lable + ' selected'
						})
					}


				}
			})

			//init selected dataTable
			tselected = jQuery('#company_selected_table').DataTable({
				colReorder: true,
				info: false,
				"sDom": 'Rlfrtip',
				"paging": false,
				searching: false,
				info: false,
				"bLengthChange": false,
				columnDefs: [{
					'targets': 0,
					'searchable': false,
					'orderable': false,
					'className': 'dt-body-center',
					'render': function(data, type, full, meta) {
						return '<input type="checkbox" name="PK_COMPANY_DEL[]"  id="PK_COMPANY_DEL_' + data + '" value="' + data + '"  class="company_del_check">';


					}
				}]
			});

		});

		function search_companies() {
			jQuery(document).ready(function() {
				jQuery('#student_search_table').DataTable().clear().destroy();
				document.getElementById('dynamic_columns').innerHTML = '<th><input name="company_add_all" id="company_add_all" type="checkbox" /></th><th>COMPANY NAME</th><th>CITY </th><th>DATE CREATED </th><th>PHONE </th><th>WEBSITE </th><th>Total Jobs</th><th>Open Jobs</th>';
				if (document.getElementById('show_contacts').checked) {
					document.getElementById('dynamic_columns').innerHTML += '<th>Contact List</th>'
				}
				jQuery('#selected_columns').val().forEach(filter_id => {
					document.getElementById('dynamic_columns').innerHTML += '<th>' + jQuery('#selected_columns option[value="' + filter_id + '"]').text() + '</th>'
				});

				t = jQuery('#student_search_table').DataTable({
					colReorder: true,
					info: false,
					"sDom": 'Rlfrtip',
					"paging": false,
					searching: false,
					info: false,
					"bLengthChange": false,
					columnDefs: [{
						'targets': 0,
						'searchable': false,
						'orderable': false,
						'className': 'dt-body-center',
						'render': function(data, type, full, meta) {
							return '<input type="checkbox" name="PK_COMPANY_ADD[]"  id="PK_COMPANY_' + data + '" value="' + data + '"  class="company_add_check">';


						}
					}],
					fixedColumns: {
						left: 2
					},
					scrollY: "450px",
					sScrollX: "100%",
					scrollCollapse: true,
					dom: 'Bfrtip',
					buttons: {
						buttons: [{
							extend: 'csv',
							className: 'btn waves-effect waves-light btn-info'
						}, ]
					},
				});
				// 				jQuery.fn.dataTable.ext.errMode = 'none';
				// 				new jQuery.fn.dataTable.Buttons( t, {
				//     buttons: [
				//         'copy', 'excel', 'pdf'
				//     ]
				// } );
				// alert(jQuery('#company_filter_pane input,#company_filter_pane select').serialize());
				var show_contacts = document.getElementById('show_contacts').checked;
				var payload = 'selected_columns=' + jQuery('#selected_columns').val() + '&show_contacts=' + show_contacts + '&PK_CAMPUS=' + jQuery('#PK_CAMPUS').val() + '&COMPANY_CITY=' + jQuery('#COMPANY_CITY').val() + '&COMPANY_NAME=' + jQuery('#COMPANY_NAME').val() + '&COMPANY_SOURCE=' + jQuery('#COMPANY_SOURCE').val() + '&COMPANY_DATE_CREATED_BEGIN_DATE=' + jQuery('#COMPANY_DATE_CREATED_BEGIN_DATE').val() + '&COMPANY_DATE_CREATED_END_DATE=' + jQuery('#COMPANY_DATE_CREATED_END_DATE').val() + '&COMPANY_FAX=' + jQuery('#COMPANY_FAX').val() + '&COMPANY_MAIN_CONTACT=' + jQuery('#COMPANY_MAIN_CONTACT').val() + '&COMPANY_OPEN_JOB=' + jQuery('#COMPANY_OPEN_JOB').val() + '&COMPANY_PHONE=' + jQuery('#COMPANY_PHONE').val() + '&COMPANY_SCHOOL_EMPLOYEE=' + jQuery('#COMPANY_SCHOOL_EMPLOYEE').val() + '&COMPANY_STATE=' + jQuery('#COMPANY_STATE').val() + '&COMPANY_STATUS=' + jQuery('#COMPANY_STATUS').val() + '&COMPANY_TOTAL_JOBS=' + jQuery('#COMPANY_TOTAL_JOBS').val() + '&COMPANY_TYPE=' + jQuery('#COMPANY_TYPE').val() + '&COMPANY_WEBSITE=' + jQuery('#COMPANY_WEBSITE').val() + '&COMPANY_EVENT_COMPANY_CONTACT=' + jQuery('#COMPANY_EVENT_COMPANY_CONTACT').val() + '&COMPANY_EVENT_COMPLETE=' + jQuery('#COMPANY_EVENT_COMPLETE').val() + '&COMPANY_EVENT_BEGIN_DATE=' + jQuery('#COMPANY_EVENT_BEGIN_DATE').val() + '&COMPANY_EVENT_END_DATE=' + jQuery('#COMPANY_EVENT_END_DATE').val() + '&COMPANY_EVENT_TYPE=' + jQuery('#COMPANY_EVENT_TYPE').val() + '&COMPANY_EVENT_FOLLOWUP_BEGIN_DATE=' + jQuery('#COMPANY_EVENT_FOLLOWUP_BEGIN_DATE').val() + '&COMPANY_EVENT_FOLLOWUP_END_DATE=' + jQuery('#COMPANY_EVENT_FOLLOWUP_END_DATE').val() + '&COMPANY_EVENT_SCHOOL_EMPLOYEE=' + jQuery('#COMPANY_EVENT_SCHOOL_EMPLOYEE').val() + '&COMPANY_JOB_FULL_PART_TIME=' + jQuery('#COMPANY_JOB_FULL_PART_TIME').val() + '&COMPANY_JOB_CANCELLED_BEGIN_DATE=' + jQuery('#COMPANY_JOB_CANCELLED_BEGIN_DATE').val() + '&COMPANY_JOB_CANCELLED_END_DATE=' + jQuery('#COMPANY_JOB_CANCELLED_END_DATE').val() + '&COMPANY_JOB_FILLED_BEGIN_DATE=' + jQuery('#COMPANY_JOB_FILLED_BEGIN_DATE').val() + '&COMPANY_JOB_FILLED_END_DATE=' + jQuery('#COMPANY_JOB_FILLED_END_DATE').val() + '&COMPANY_JOB_NO=' + jQuery('#COMPANY_JOB_NO').val() + '&COMPANY_JOB_POSTED_DATE_BEGIN_DATE=' + jQuery('#COMPANY_JOB_POSTED_DATE_BEGIN_DATE').val() + '&COMPANY_JOB_POSTED_DATE_END_DATE=' + jQuery('#COMPANY_JOB_POSTED_DATE_END_DATE').val() + '&COMPANY_JOB_TITLE=' + jQuery('#COMPANY_JOB_TITLE').val() + '&COMPANY_JOB_OPEN_JOB=' + jQuery('#COMPANY_JOB_OPEN_JOB').val() + '&COMPANY_JOB_PAY_AMOUNT_FROM=' + jQuery('#COMPANY_JOB_PAY_AMOUNT_FROM').val() + '&COMPANY_JOB_PAY_AMOUNT_TO=' + jQuery('#COMPANY_JOB_PAY_AMOUNT_TO').val()

				var value = jQuery.ajax({
					url: "ajax_search_compan_selections",
					type: "POST",
					data: payload,
					async: true,
					cache: false,
					success: function(data) {
						setTimeout(() => {
							// loader[0].style.display = "none";
						}, 1000);


						//###
						//Not working , please optimize : function_clear_n_init_datatable('student_search_table' , data['data'])

						data['data'].forEach(element => {
							console.log("data_row", element);
							jQuery(document).ready(function() {
								t.row.add(element).node().id = 'company_' + element[0]
								t.draw(true);
							});

						});
					}
				}).responseText;
			});

		}

		function function_clear_n_init_datatable(table_name, data = null) {
			jQuery(document).ready(function() {
				console.log("Adding data to DT", table_name, data);
				t = jQuery('#' + table_name).DataTable({
					colReorder: true,
					info: false,
					"sDom": 'Rlfrtip',
					"paging": false,
					searching: false,
					info: false,
					"bLengthChange": false,
					columnDefs: [{
						'targets': 0,
						'searchable': false,
						'orderable': false,
						'className': 'dt-body-center',
						'render': function(data, type, full, meta) {
							return '<input type="checkbox" name="PK_COMPANY_ADD[]"  id="PK_COMPANY_' + data + '" value="' + data + '"  class="company_add_check">';


						}
					}],
					ordering: false,
					fixedColumns: {
						left: 3
					},
					scrollY: "10px",
					sScrollX: "100%",
					scrollCollapse: true
				});

				t.draw();
				// check_tr_count();
			});
		}

		function init_selected_companies() {
			jQuery(document).ready(function() {




				var value = jQuery.ajax({
					url: "ajax_load_selected_company_list.php",
					type: "POST",
					data: "id=<?= $_GET['id'] ?>",
					async: true,
					cache: false,
					success: function(data) {
						setTimeout(() => {
							// loader[0].style.display = "none";
						}, 1000);
						data['data'].forEach(element => {
							console.log("data_row", element);
							jQuery(document).ready(function() {
								tselected.row.add(element).node().id = 'selected_' + element[0];
								tselected.draw(true);
							});

						});
					}
				}).responseText;

			})
		}

		jQuery(document).ready(function() {
			jQuery('input[name="intervaltype"]').click(function() {
				alert
				jQuery(this).tab('show');
				jQuery(this).removeClass('active');
			});

			jQuery('input[name="student_type"]').click(function() {
				alert
				jQuery(this).tab('show');
				jQuery(this).removeClass('active');
			});

			jQuery('input[name="student_type"]').click(function() {
				alert
				jQuery(this).tab('show');
				jQuery(this).removeClass('active');
			});


			jQuery(document).on('click', ".company_add_check", function() {

				//#remove checked all
				if (!jQuery(this).is(":checked")) {
					document.getElementById('company_add_all').checked = false;
				}

				show_add_btn()
			});

			jQuery(document).on('click', ".company_del_check", function() {
				//#remove checked all
				if (!jQuery(this).is(":checked")) {
					document.getElementById('company_del_all').checked = false;
				}
			});


		})

		jQuery(document).on('click', '#company_add_all', function() {
			checkboxes = document.getElementsByName('PK_COMPANY_ADD[]');
			for (var i = 0, n = checkboxes.length; i < n; i++) {
				checkboxes[i].checked = document.getElementById('company_add_all').checked;
			}
			show_add_btn()
		});


		jQuery(document).on('click', '#company_del_all', function() {
			checkboxes = document.getElementsByName('PK_COMPANY_DEL[]');
			for (var i = 0, n = checkboxes.length; i < n; i++) {
				checkboxes[i].checked = document.getElementById('company_del_all').checked;
			}
		});

		function show_add_btn() {

			jQuery(document).ready(function() {
				if (jQuery('.company_add_check').filter(':checked').length < 1) {
					jQuery('#add_company_btn').addClass('d-none');
				} else {
					jQuery('#add_company_btn').removeClass('d-none');
				}

			})


		}


		var selected_companies = [];

		function add_companies() {

			jQuery(document).ready(function() {
				var companies = [];
				var y = t.rows().data();
				var rowpointer = 0;
				document.querySelectorAll('.company_add_check').forEach(function(elem) {
					if (elem.checked == true) {
						// alert(elem);
						// companies.push(elem.value)
						if (!selected_companies.includes(y[rowpointer][0])) {
							companies.push(rowpointer)
							tselected.row.add(y[rowpointer]).node().id = 'selected_' + rowpointer;
							tselected.draw(true);
							selected_companies.push(y[rowpointer][0])
							// alert("included");
							jQuery('#company_' + y[rowpointer][0]).hide();
						}

					}
					rowpointer++;
				});
				checkboxes = document.getElementsByName('PK_COMPANY_ADD[]');
				document.getElementById('company_add_all').checked = false;
				for (var i = 0, n = checkboxes.length; i < n; i++) {
					checkboxes[i].checked = document.getElementById('company_add_all').checked;
				}
				show_add_btn()
				// alert("adding companies");
			});
		}

		function del_companies() {
			var rowpointer = 0;
			document.querySelectorAll('.company_del_check').forEach(function(elem) {
				if (elem.checked == true) {
					tselected
						.row(jQuery(elem).parents('tr'))
						.remove()
						.draw();


					if (jQuery("#company_" + elem.value).length != 0) {
						jQuery("#company_" + elem.value).show();
						selected_companies.splice(rowpointer, 1)
					}
				}
				rowpointer++;

			});


		}


		function validate_form(val) {
			jQuery(document).ready(function($) {
				document.getElementById("SAVE_CONTINUE").value = val;

				var valid = new Validation('form1', {
					onSubmit: false
				});
				var result = valid.validate();
				//alert(result)
				if (result == true) {
					// document.form1.submit(); 
					var data_selected = [];

					selected_lenght = tselected.rows().data();

					for (let index = 0; index < selected_lenght.length; index++) {

						data_selected.push(selected_lenght[index][0])

					}
					// alert(data_selected);

					var value = jQuery.ajax({
						url: "ajax_search_company_selections_save",
						type: "POST",
						data: jQuery('#form1').serialize() + '&data_selected=' + data_selected <?php if ($_GET['id']) {
																									echo "+'&filter_id='+" . $_GET['id'];
																								} ?>,
						async: true,
						cache: false,
						success: function(data) {
							// alert("Report is saved !")
							setTimeout(() => {
								// loader[0].style.display = "none";
							}, 1000);

							if (val == 0) {
								window.location = 'manage_company_report_selection';
							} else {
								window.location = 'company_report_selection?id=' + data
							}

						}
					}).responseText;
				}
			});
		}
		jQuery(document).ready(function() {
			<?php if ($_GET['id']) {
				echo 'search_companies()';
			} ?>
			;
			init_selected_companies()
		});
	</script>

</body>

</html>