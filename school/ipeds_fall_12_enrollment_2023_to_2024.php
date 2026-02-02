<?
require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/ipeds_fall_collections_setup.php");
require_once("check_access.php");
require_once('custom_excel_generator.php');
// ini_set('memory_limit' , 0);
$res_add_on = $db->Execute("SELECT COE,ECM,_1098T,_90_10,IPEDS,POPULATION_REPORT FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if ($res_add_on->fields['IPEDS'] == 0 || check_access('MANAGEMENT_IPEDS') == 0) {
	header("location:../index");
	exit;
}

if (!empty($_POST)) {
	header('Content-Type: application/json; charset=utf-8');

	ENABLE_DEBUGGING(false);

	$SURVEY_NAME = $_REQUEST['SURVEY_NAME'];
	$SURVEY_OPTION = $_REQUEST['SURVEY_OPTION'];
	$START_DATE = $_REQUEST['START_DATE'];
	$END_DATE = $_REQUEST['END_DATE'];
	$PK_CAMPUS = implode(',', $_REQUEST['PK_CAMPUS']);
	$FORMAT = $_REQUEST['FORMAT'];
	$PK_ACCOUNT = $_SESSION['PK_ACCOUNT'];
	$START_DATE = date("Y-m-d", strtotime($START_DATE));
	$END_DATE = date("Y-m-d", strtotime($END_DATE));
	if ($SURVEY_NAME == 'Student Financial Aid') {

		$PROCEDURE = 'COMP20004_NEW';

		if ($SURVEY_OPTION == 'Part A, B, C') {
			// $HEADERS[] = [
			// 	"IPEDS Survey Year  ",
			// 	"Cohort Begin Date ",
			// 	"Cohort End Date ",
			// 	"Student",
			// 	"Student ID",
			// 	"Campus",
			// 	"Program Code",
			// 	"First Term Date",
			// 	"Student Status",				
			// 	"Enrollment End Date",
			// 	"End Date Type",
			// 	"Credential Level",
			// 	"Part A 02 (1/0)",
			// 	"Part A 03 (1/0)",
			// 	"Part A 04 (1/0)",
			// 	"Part A 05 (1/0)",
			// 	"Part A 06 (1/0)",
			// 	"Part A 07 (1/0)",
			// 	"Part A 08 (1/0)",
			// 	"Part A 09 (1/0)",
			// 	"Part B 01 Col 1 (1/0)",
			// 	"Part B 01 Col 3 Awards (Amount)",
			// 	"Part B 01 Col 5 (1/0)",
			// 	"Part B 01 Col 7 Awards (Amount)",
			// 	"Part B 02 Col 1 (1/0)",
			// 	"Part B 02 Col 3 Awards (Amount)",
			// 	"Part B 02 Col 5 (1/0)",
			// 	"Part B 02 Col 7 Awards (Amount)",
			// 	"Part B 03 Col 1 (1/0)",
			// 	"Part B 03 Col 3 Awards (Amount)",
			// 	"Part B 03 Col 5 (1/0)",
			// 	"Part B 03 Col 7 Awards (Amount)",
			// 	"Part C 01 Col 1 (1/0)",
			// 	"Part C 01 Col 3 Awards (Amount)",
			// 	"Part C 02 Col 1 (1/0)",
			// 	"Part C 02 Col 3 Awards (Amount)",
			// 	"Part C 03 Col 1 (1/0)",
			// 	"Part C 03 Col 3 Awards (Amount)",
			// 	"Part C 04 Col 1 (1/0)",
			// 	"Part C 04 Col 3 Awards (Amount)",
			// 	"Part C 05 Col 1 (1/0)",
			// 	"Part C 05 Col 3 Awards (Amount)",
			// 	"Part C 06 Col 1 (1/0)",
			// 	"Part C 06 Col 3 Awards (Amount)",
			// 	"Part C 07 Col 1 (1/0)",
			// 	"Part C 07 Col 3 Awards (Amount)",
			// 	"Part C 08 Col 1 (1/0)",
			// 	"Part C 08 Col 3 Awards (Amount)",
			// 	"Part C 09 Col 1(1/0)",
			// 	"Part C 09 Col 3 Awards"
			// ];
			$style['header_style'] = 'no_background';
		}




        $query_call = "CALL $PROCEDURE( '$PK_ACCOUNT' , '$PK_CAMPUS' , '$START_DATE' , '$END_DATE' , '$SURVEY_OPTION' )" ;
		$results_r = $db->Execute($query_call);
		 
		$HEADERS = array_keys($results_r->fields);
		if (empty($HEADERS)) {
			// $data[] = array_keys($results_r->fields);
		}
		while (!$results_r->EOF) {
			$itemrow = [];
			foreach ($results_r->fields as $key1 => $value1) {
				$itemrow[] = $value1;
			}
			$data[] = $itemrow;
			$results_r->MoveNext();
		}
	} else if (
		$SURVEY_NAME == "Student Financial Aid OLD"
	) {
		$PROCEDURE = 'COMP20004_NEW_OLD';

		$query_call = "CALL $PROCEDURE( '$PK_ACCOUNT' , '$PK_CAMPUS' , '$START_DATE' , '$END_DATE' , '$SURVEY_OPTION' )";

		$results_r = $db->Execute($query_call);
		$HEADERS = array_keys($results_r->fields);
		$data[] = array_keys($results_r->fields);
		while (!$results_r->EOF) {
			$itemrow = [];
			foreach ($results_r->fields as $key1 => $value1) {
				$itemrow[] = $value1;
			}
			$data[] = $itemrow;
			$results_r->MoveNext();
		}
	} else if ($SURVEY_NAME == 'Graduation Rates') {

		// $HEADERS[] = [
		// 	"IPEDS Survey Year",
		// 	"Cohort Begin Date",
		// 	"Cohort End Date",
		// 	"Student",
		// 	"Student ID",
		// 	"Campus",
		// 	"IPEDS Category",
		// 	"First Term Date",
		// 	"Program Code",
		// 	"Student Status",
		// 	"End Date Type",
		// 	"Enrollment End Date",
		// 	"Enrollment Completed",
		// 	"Gender",
		// 	"Gender Reported",
		// 	"IPEDS Ethnicity",
		// 	"LOA Days",
		// 	"Break Days",
		// 	"Program Category",
		// 	"Program Hours",
		// 	"Cohort 100 End Date In Hours",
		// 	"Cohort 150 End Date In Hours",
		// 	"Cohort 200 End Date In Hours",
		// 	"Completion Ratio In Hours",
		// 	"Program Weeks",
		// 	"Cohort 100 End Date In Weeks",
		// 	"Cohort 150 End Date In Weeks",
		// 	"Cohort 200 End Date In Weeks",
		// 	"Completion Ratio In Weeks",
		// 	"Cohort Status",
		// 	"Cohort Status Description",
		// 	"Drop Reason",
		// 	"Transfer Out",
		// 	"Exclusion",
		// 	"Received Pell",
		// 	"Received Sub Loan"
		// ];

		$query_call = "CALL COMP20005_NEW( '$PK_ACCOUNT' , '$PK_CAMPUS', '$START_DATE' , '$END_DATE' , '$SURVEY_OPTION' )";
		if( $PK_ACCOUNT == 94){
			$query_call = "CALL COMP20005_NEW_I( '$PK_ACCOUNT' , '$PK_CAMPUS', '$START_DATE' , '$END_DATE' , '$SURVEY_OPTION' )";
		}
		$results_r = $db->Execute($query_call);
		$HEADERS = array_keys($results_r->fields);
		while (!$results_r->EOF) {
			$itemrow = [];
			foreach ($results_r->fields as $key1 => $value1) {
				$itemrow[] = $value1;
			}
			$data[] = $itemrow;
			$results_r->MoveNext();
		}
	} else if ($SURVEY_NAME == 'Graduation Rates 200') {

		// $HEADERS[] = [
		// 	"IPEDS Survey Year",
		// 	"Cohort Begin Date",
		// 	"Cohort End Date",
		// 	"Student",
		// 	"Student ID",
		// 	"Campus",
		// 	"Program Code",
		// 	"First Term Date",
		// 	"Student Status",
		// 	"End Date Type",
		// 	"Enrollment End Date",
		// 	"Enrollment Status",
		// 	"Program Category",
		// 	"Program Hours",
		// 	"Cohort 150 End Date In Hours",
		// 	"Cohort 200 End Date In Hours",
		// 	"Program Weeks",
		// 	"Cohort 150 End Date In Weeks",
		// 	"Cohort 200 End Date In Weeks",
		// 	"Cohort Status 200"
		// ];

		$query_call = "CALL COMP20006_NEW( '$PK_ACCOUNT' , '$PK_CAMPUS', '$START_DATE' , '$END_DATE' , '$SURVEY_OPTION' )";
		if( $PK_ACCOUNT == 94){
			// $query_call = "CALL COMP20006_NEW_I( '$PK_ACCOUNT' , '$PK_CAMPUS', '$START_DATE' , '$END_DATE' , '$SURVEY_OPTION' )";
		}
		$results_r = $db->Execute($query_call);
		$HEADERS = array_keys($results_r->fields);
		while (!$results_r->EOF) {
			$itemrow = [];
			foreach ($results_r->fields as $key1 => $value1) {
				$itemrow[] = $value1;
			}
			$data[] = $itemrow;
			$results_r->MoveNext();
		}
	} else if ($SURVEY_NAME == 'Admission') {
		$PROCEDURE = 'COMP20007_NEW';
        $query_call =  "CALL $PROCEDURE( '$PK_ACCOUNT' , '$PK_CAMPUS' , '$START_DATE' , '$END_DATE' , '$SURVEY_OPTION' )";
		$results_r = $db->Execute($query_call);
		$HEADERS = array_keys($results_r->fields);
		while (!$results_r->EOF) {
			$itemrow = [];
			foreach ($results_r->fields as $key1 => $value1) {
				$itemrow[] = $value1;
			}
			$data[] = $itemrow;
			$results_r->MoveNext();
		}
	} else if ($SURVEY_NAME == 'Outcome Measure') {
		$PROCEDURE = 'COMP20008_NEW';
		echo $query_call = "CALL $PROCEDURE( '$PK_ACCOUNT' , '$PK_CAMPUS' , '$START_DATE' , '$END_DATE' , '$SURVEY_OPTION' )"; exit;
		$results_r = $db->Execute($query_call);
		$HEADERS = array_keys($results_r->fields);
		while (!$results_r->EOF) {
			$itemrow = [];
			foreach ($results_r->fields as $key1 => $value1) {
				$itemrow[] = $value1;
			}
			$data[] = $itemrow;
			$results_r->MoveNext();
		}
	} else if ($SURVEY_NAME == 'Cost II') {
		/// dvb 29 01 2025
		$PROCEDURE = 'COMP20009_NEW';
		$query_call = "CALL $PROCEDURE( '$PK_ACCOUNT' , '$PK_CAMPUS' , '$START_DATE' , '$END_DATE' , '$SURVEY_NAME' )";
		echo $query_call;exit;
		$results_r = $db->Execute($query_call);
		$HEADERS = array_keys($results_r->fields);
		while (!$results_r->EOF) {
			$itemrow = [];
			foreach ($results_r->fields as $key1 => $value1) {
				$itemrow[] = $value1;
			}
			$data[] = $itemrow;
			$results_r->MoveNext();
		}
	} else {
		//Do nothing if no procedure is matched
		exit;
	}




	// dd("hi" , $data);
	// dump("Debugging is enabled !");
	// dump($query_call);
	if (empty($data)) {
		$response["error"] = "No data found !";
		echo json_encode($response);
		return;
	}
	// $file_name = str_replace('_', ' ', $SURVEY_NAME) . '.xlsx';
	if (empty($HEADERS)) {
		$HEADERS = false;
	}
	// echo "--->";
	// var_dump($HEADERS);
	// echo "<---";
 
	 
	// if (option_name == "Graduation Rates") {
	// 	element_new_html = '<option value="Graduation Rates">Graduation Rates</option>';
	// }
	// if (option_name == "Graduation Rates 200") {
	// 	element_new_html = '<option value="Graduation Rates 200">Graduation Rates 200</option>';
	// }
	// if (option_name == "Admission") {
	// 	element_new_html = '<option value="Selection Process - A/A/E">Selection Process - A/A/E </option><option value="Selection Process - Test Scores">Selection Process - Test Scores </option>';
	// }

	$file_name_array['Student Financial Aid']['Part A, B, C'] = 'IPEDS_Winter_2425_SFA_PartABC.xlsx';
	$file_name_array['Student Financial Aid']['Part D'] = 'IPEDS_Winter_2425_SFA_PartD.xlsx';
	$file_name_array['Student Financial Aid']['Part E'] = 'IPEDS_Winter_2425_SFA_PartE.xlsx';
	$file_name_array['Student Financial Aid']['Military Post 9/11'] = 'IPEDS_Winter_2425_SFA_Military_Post911.xlsx';
	$file_name_array['Student Financial Aid']['Military DOD'] = 'IPEDS_Winter_2425_SFA_Military_DOD.xlsx';


	$file_name_array['Outcome Measure']['Entering Undergraduate Cohort'] = 'IPEDS_Winter_2425_OM_Cohort.xlsx';
	$file_name_array['Outcome Measure']['Award Status at Four Years'] = 'IPEDS_Winter_2425_OM_4Year.xlsx';
	$file_name_array['Outcome Measure']['Award Status at Six Years'] = 'IPEDS_Winter_2425_OM_6Year.xlsx';
	$file_name_array['Outcome Measure']['Award and Enrollment Status at Eight Years'] = 'IPEDS_Winter_2425_OM_8Year.xlsx';

	$file_name_array['Admission']['Selection Process - A/A/E'] = 'IPEDS_Winter_2425_Admissions_AAE.xlsx';
	$file_name_array['Admission']['Selection Process - Test Scores'] = 'IPEDS_Winter_2425_Admissions_TestScores.xlsx';

	$file_name_array['Graduation Rates 200']['Graduation Rates 200'] = 'IPEDS_Winter_2425_Graduation_Rates_200.xlsx';
	$file_name_array['Graduation Rates']['Graduation Rates'] = 'IPEDS_Winter_2425_Graduation_Rates.xlsx';

	// dvb 29 01 2025 - IPEDS_Winter_2425_Cost_II_22_1733338563752.xlsx
	$file_name_array['Cost II']['Cost II'] = 'IPEDS_Winter_2425_Cost_II_22_1733338563752.xlsx';
	// ----------
	

	$file_name = $file_name_array[$SURVEY_NAME][$SURVEY_OPTION];
	if($file_name == ''){
		$file_name = str_replace('_', ' ', $SURVEY_NAME) . '.xlsx';
	}

	$outputFileName = $file_name;
	$outputFileName = str_replace(
		pathinfo($outputFileName, PATHINFO_FILENAME),
		pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . floor(microtime(true) * 1000),
		$outputFileName
	);
	$output = CustomExcelGenerator::makecustom('Excel2007', 'temp/', $outputFileName, $data, $HEADERS , $style);
	// dd("File Generated ", $output);
	$response["file_name"] = $outputFileName;
	$response["path"] =  $output;
	$response["sp"] =  $query_call;
	echo json_encode($response);
	return;
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
	<title>IPEDS Winter Collection 2025-2026 | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}

		#advice-required-entry-PK_CAMPUS {
			position: absolute;
			top: 55px;
			width: 142px
		}

		.dropdown-menu>li>a {
			white-space: nowrap;
			max-width: 90%
		}

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

	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
	<? require_once("pre_load.php"); ?>
	<div id="main-wrapper">
		<? require_once("menu.php"); ?>
		<div id="loaders" style="display: none;">
			<div class="lds-ring">
				<div></div>
				<div></div>
				<div></div>
				<div></div>
			</div>
		</div>
		<div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles">
					<div class="col-md-5 align-self-center">
						<h4 class="text-themecolor">
							IPEDS Winter Collection 2025-2026
						</h4>
					</div>
				</div>

				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-2">
											Survey
											<select id="SURVEY_NAME" name="SURVEY_NAME" class="form-control" onchange="fetch_survey_options()">
												<option value="Student Financial Aid">Student Financial Aid</option>
												<!-- DVB 29 01 2025 -->
												<option value="Cost II">Cost II</option>
												<option value="Graduation Rates" >Graduation Rates</option>
												<option value="Graduation Rates 200" >Graduation Rates 200</option>
												<option value="Admission" >Admissions</option>
												<option value="Outcome Measure" >Outcome Measures</option>
												<?php 
												// if($_SESSION['PK_ACCOUNT'] == 94){
												// 	echo '<option value="Graduation Rates" >Graduation Rates - II </option>';
												// 	echo '<option value="Graduation Rates 200" >Graduation Rates 200 - II </option>';
												// }
												?>
											</select>
										</div>
										<div class="col-md-2">
											Survey Options
											<select id="SURVEY_OPTION" name="SURVEY_OPTION" class="form-control">
												<option value="Part A, B, C">Part A, B, C</option>
												<option value="Part D">Part D</option>
												<option value="Part E">Part E</option>
												<option value="Military Post 9/11">Military Post 9/11</option>
												<option value="Military DOD">Military DOD</option>
											</select>
										</div>
										<div class="col-md-2">
											<?= START_DATE ?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="">
										</div>
										<div class="col-md-2">
											<?= END_DATE ?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="">
										</div>

										<div class="col-md-2">
											<?= CAMPUS ?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry">
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($res_type->RecordCount() == 1) echo "selected"; ?>><?= $res_type->fields['CAMPUS_CODE'] ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>

										<div class="col-md-2" style="padding: 0;">
											<br />
											<!-- <button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?= PDF ?></button> -->
											<button type="button" onclick="downloadreport()" class="btn waves-effect waves-light btn-info"><?= EXCEL ?></button>
											<input type="hidden" name="FORMAT" id="FORMAT">
										</div>
									</div>
									<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
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
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
		jQuery(document).ready(function($) {
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
		});
	</script>

	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		function submit_form(val) {
			jQuery(document).ready(function($) {
				var valid = new Validation('form1', {
					onSubmit: false
				});
				var result = valid.validate();
				if (result == true) {
					document.getElementById('FORMAT').value = val
					document.form1.submit();
				}
			});
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= CAMPUS ?>',
				nonSelectedText: '',
				numberDisplayed: 2,
				nSelectedText: '<?= CAMPUS ?> selected'
			});


		});

		function fetch_survey_options() {
			jQuery(document).ready(function($) {
				option_name = document.getElementById('SURVEY_NAME').value;
				element = document.getElementById('SURVEY_OPTION').innerHTML;
				$("#START_DATE").prop('disabled', false);
				$("#END_DATE").prop('disabled', false);

				if (option_name == "Student Financial Aid") {
					element_new_html = '<option value="Part A, B, C">Part A, B, C</option><option value="Part D">Part D</option><option value="Part E">Part E</option><option value="Military Post 9/11">Military Post 9/11</option><option value="Military DOD">Military DOD</option>';
				}
				if (option_name == "Student Financial Aid OLD") {
					element_new_html = '<option value="Section 2 Military Datasheet">Section 2 Military Datasheet</option><option value="Section 2 Military">Section 2 Military</option><option value="Section 2 Military Students">Section 2 Military Students</option>				';
				}
				if (option_name == "Graduation Rates") {
					element_new_html = '<option value="Graduation Rates">Graduation Rates</option>';
				}
				if (option_name == "Graduation Rates 200") {
					element_new_html = '<option value="Graduation Rates 200">Graduation Rates 200</option>';
				}
				if (option_name == "Admission") {
					element_new_html = '<option value="Selection Process - A/A/E">Selection Process - A/A/E </option><option value="Selection Process - Test Scores">Selection Process - Test Scores </option>';

					// $("#START_DATE").prop('disabled', true);
					// $("#END_DATE").prop('disabled', true);
				}
				if (option_name == "Outcome Measure") {
					element_new_html = '<option value="Entering Undergraduate Cohort">Entering Undergraduate Cohort</option><option value="Award Status at Four Years">Award Status at Four Years</option><option value="Award Status at Six Years">Award Status at Six Years</option><option value="Award and Enrollment Status at Eight Years">Award and Enrollment Status at Eight Years</option>';
				}
				// dvb 29 01 2025
				// console.log(option_name);
				if (option_name == "Cost II") {
					element_new_html = '<option value="Cost II">Cost II</option>';
				}

				document.getElementById('SURVEY_OPTION').innerHTML = element_new_html;

			});

		}



		function downloadreport() {
			document.getElementById('loaders').style.display = 'block';

			jQuery(document).ready(function($) {

				//Validations #advice-required-entry-PK_CAMPUSconst myArray = [1, 2, 3, 4, 5];
				var SURVEY_NAME = $('#SURVEY_NAME').val();
				var SURVEY_OPTION = $('#SURVEY_OPTION').val();
				var STARTDATE = $('#START_DATE').val();
				var ENDDATE = $('#END_DATE').val();
				var CAMPUS = $('#PK_CAMPUS').val();
				console.log(SURVEY_NAME,
				SURVEY_OPTION,
				STARTDATE,
				ENDDATE,
				CAMPUS);
				if(SURVEY_NAME == ''){
					alert("Please Select a Survey");
					return;
				}
				else if(SURVEY_OPTION == ''){
					alert("Please Select a Survey Option");
					return;
				}
				else if(STARTDATE == '' && SURVEY_NAME != 'Admission'){
					alert("Please Select a Start Date");
					return;
				}
				else if(ENDDATE == '' && SURVEY_NAME != 'Admission'){
					alert("Please Select a End Date");
					return;
				}else if(CAMPUS == ''){
					alert("Please Select a Campus");
					return;
				} 
				

				//Generate pdf
				var value = $.ajax({
					url: 'ipeds_fall_12_enrollment_2023_to_2024.php',
					type: "POST",
					data: $("#form1").serializeArray(),
					async: true,
					cache: false,
					beforeSend: function() {
						// document.getElementById('loaders').style.display = 'block';
					},
					success: function(data, textStatus, xhr) {
						document.getElementById('loaders').style.display = 'none';
						// console.log(data, textStatus, xhr, xhr.status);
						if (data.error == "No data found !") {
							alert("No data found for this report ! Check IPEDS setup and try again");
							return;
						}

						const text = window.location.href;
						const word = '/school';
						const textArray = text.split(word); // ['This is ', ' text...']
						const result = textArray.shift();
						console.log(data, data.file_name, data.path);
						downloadDataUrlFromJavascript(data.file_name, result + '/school/' + data.path)
						// alert(result + '/school/' + data.path); 

					},
					error: function() {
						document.getElementById('loaders').style.display = 'none';
						alert("Something went wrong , Check your IPEDS setup and try again");
					},
					complete: function() {
						document.getElementById('loaders').style.display = 'none';
						// document.getElementById('loaders').style.display = 'none';

					}
				});
			});
		}

		function downloadDataUrlFromJavascript(filename, dataUrl) {

			// Construct the 'a' element
			var link = document.createElement("a");
			link.download = filename;
			link.target = "_blank";

			// Construct the URI
			link.href = dataUrl;
			document.body.appendChild(link);
			link.click();

			// Cleanup the DOM
			document.body.removeChild(link);
			delete link;
		}
	</script>

</body>

</html>
