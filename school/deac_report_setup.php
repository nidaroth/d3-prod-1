<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/deac.php");
require_once("get_department_from_t.php");

require_once("check_access.php");
// ENABLE_DEBUGGING(TRUE);
if (check_access('MANAGEMENT_ACCREDITATION') == 0) {
	header("location:../index");
	exit;
}
if (!empty($_POST)) {
// dd($_POST);

	## !!!!!! >> EXCLUDED_PROGRAMS - nothing to map ? 
	## !!!!!! >> STUDENT_STATUS_Excluded_Student_Status Nothing to be mapped back 
	// ACTIVE_STUDENT_STATUS --> STUDENT_STATUS_Excluded_Student_Status
	// GRADUATED_STUDENT_STATUS --> STUDENT_STATUS_Graduated_Student_Status
	// INACTIVE_STUDENT_STATUS --> STUDENT_STATUS_Inactive_Student_Status
	// WITHDRAWN_STUDENT_STATUS --> STUDENT_STATUS_Withdrawn_Student_Status
	// HIGH_SCHOOL_PROGRAMS --> PROGRAM_High_School_Programs
	// NON_DEGREE_PROGRAMS --> 	PROGRAM_Non_Degree_Programs
	// ASSOCIATE_DEGREE_PROGRAMS --> 	PROGRAM_Associate_Degree_Programs
	// BACHELOR_DEGREE_PROGRAMS --> 	PROGRAM_Bachelor_Degree_Programs
	// MASTERS_DEGREE_PROGRAMS --> 	PROGRAM_Masters_Degree_Programs
	// FIRST_PROF_DEGREE_PROGRAMS --> 	PROGRAM_First_Professional_Degree_Programs
	// DOCTORATE_PROGRAMS --> 	PROGRAM_Doctorate_Degree_Programs
	// HS_START_DATE --> START_DATE_High_School
	// HS_END_DATE --> 	END_DATE_High_School
	// NON_DEGREE_START_DATE --> 	START_DATE_Non_Degree
	// NON_DEGREE_END_DATE --> 	END_DATE_Non_Degree
	// ASSOCIATE_DEGREE_START_DATE --> 	START_DATE_Associate_Degree
	// ASSOCIATE_DEGREE_END_DATE --> 	END_DATE_Associate_Degree
	// BACHELOR_DEGREE_START_DATE --> 	START_DATE_Bachelor_Degree
	// BACHEHLOR_DEGREE_END_DATE --> 	END_DATE_Bachelor_Degree
	// MASTERS_DEGREE_START_DATE --> 	START_DATE_Masters_Degree
	// MASTERS_DEGREE_END_DATE --> 	END_DATE_Masters_Degree
	// FIRST_PROF_DEGREE_START_DATE --> 	START_DATE_First_Professional_Degree
	// FIRST_PROF_DEGREE_END_DATE --> 	END_DATE_First_Professional_Degree
	// DOCTORATE_DEGREE_START_DATE --> 	START_DATE_Doctorate_Degree
	// DOCTORATE_DEGREE_END_DATE --> 	END_DATE_Doctorate_Degree
	// DEAC_EXCLUSIONS --> DROP_REASON_DEAC_Exclusions
	// IPEDS_EXCLUSIONS --> 	DROP_REASON_IPEDS_Exclusions
	// WITHDRAWAL_EMPLOYED_RELATED_FIELD --> 	DROP_REASON_Withdrawal_Employed_Related_Field
	// WITHDRAWAL_EMPLOYED_UNRELATED_FIELD --> 	DROP_REASON_Withdrawal_Employed_Unrelated_Field
	// TRANSFERRED_TO_ANOTHER_INSTITUTION --> 	DROP_REASON_Transferred_to_Another_Institution
	// ACTIVE_DUTY_MILITARY --> 	DROP_REASON_Active_Duty_Military_Service
	// SATISFACTORY_ACADEMIC_PROGRESS --> 	DROP_REASON_Satisfactory_Academic_Progress
	// FINANCIAL --> 	DROP_REASON_Financial
	// PERSONAL --> 	DROP_REASON_Personal
	// UNKNOWN_DROP_REASON --> 	DROP_REASON_Unknown
	// OTHER --> 	DROP_REASON_Other
	// EMPLOYED_IN_FIELD --> PLACEMENT_STATUS_Employed_In_Field
	// EMPLOYED_iN_RELATED_FIELD --> PLACEMENT_STATUS_Employed_In_Related_Field
	// EMPLOYED_IN_UNRELATED_FIELD --> PLACEMENT_STATUS_Employed_In_Unrelated_Field
	// CONTINUED_EDUCATION_AT_ANOTHER_INSTITUTION --> PLACEMENT_STATUS_Continued_Education_at_Another_Institution
	// ACTIVE_DUTY_MILITARY_SERVICE_PLACEMENT --> PLACEMENT_STATUS_Active_Duty_Military_Service
	// UNEMPLOYED --> PLACEMENT_STATUS_Unemployed
	// NOT_SEEKING_EMPLOYMENT --> PLACEMENT_STATUS_Not_Seeking_Employment
	// UNKNOWN_PLACEMENT --> PLACEMENT_STATUS_Unknown
	####>>>>>>  !!!!!!! MISSING MAP FOR -> PLACEMENT_STATUS_Other

	#convert fields 
	$DEAC_SETUP["COMPLETED_STUDENT_STATUS"] = implode(',', $_POST["STUDENT_STATUS_Completed_Student_Status"]) ?? '';
	$DEAC_SETUP["ACTIVE_STUDENT_STATUS"] = implode(',', $_POST["STUDENT_STATUS_Active_Student_Status"]) ?? '';
	$DEAC_SETUP["GRADUATED_STUDENT_STATUS"] = implode(',', $_POST["STUDENT_STATUS_Graduated_Student_Status"]) ?? '';
	$DEAC_SETUP["INACTIVE_STUDENT_STATUS"] = implode(',', $_POST["STUDENT_STATUS_Inactive_Student_Status"]) ?? '';
	$DEAC_SETUP["WITHDRAWN_STUDENT_STATUS"] = implode(',', $_POST["STUDENT_STATUS_Withdrawn_Student_Status"]) ?? '';
	$DEAC_SETUP["HIGH_SCHOOL_PROGRAMS"] = implode(',', $_POST["PROGRAM_High_School_Programs"]) ?? '';
	$DEAC_SETUP["NON_DEGREE_PROGRAMS"] = implode(',', $_POST["PROGRAM_Non_Degree_Programs"]) ?? '';
	$DEAC_SETUP["ASSOCIATE_DEGREE_PROGRAMS"] = implode(',', $_POST["PROGRAM_Associate_Degree_Programs"]) ?? '';
	$DEAC_SETUP["BACHELOR_DEGREE_PROGRAMS"] = implode(',', $_POST["PROGRAM_Bachelor_Degree_Programs"]) ?? '';
	$DEAC_SETUP["MASTERS_DEGREE_PROGRAMS"] = implode(',', $_POST["PROGRAM_Masters_Degree_Programs"]) ?? '';
	$DEAC_SETUP["FIRST_PROF_DEGREE_PROGRAMS"] = implode(',', $_POST["PROGRAM_First_Professional_Degree_Programs"]) ?? '';
	$DEAC_SETUP["DOCTORATE_PROGRAMS"] = implode(',', $_POST["PROGRAM_Doctorate_Degree_Programs"]) ?? '';

	if ($_POST['START_DATE_High_School'] != '')
		$DEAC_SETUP["HS_START_DATE"] = date('Y-m-d', strtotime($_POST["START_DATE_High_School"])) ?? '';
	if ($_POST['END_DATE_High_School'] != '')
		$DEAC_SETUP["HS_END_DATE"] = date('Y-m-d', strtotime($_POST["END_DATE_High_School"])) ?? '';
	if ($_POST['START_DATE_Non_Degree'] != '')
		$DEAC_SETUP["NON_DEGREE_START_DATE"] = date('Y-m-d', strtotime($_POST["START_DATE_Non_Degree"])) ?? '';
	if ($_POST['END_DATE_Non_Degree'] != '')
		$DEAC_SETUP["NON_DEGREE_END_DATE"] = date('Y-m-d', strtotime($_POST["END_DATE_Non_Degree"])) ?? '';
	if ($_POST['START_DATE_Associate_Degree'] != '')
		$DEAC_SETUP["ASSOCIATE_DEGREE_START_DATE"] = date('Y-m-d', strtotime($_POST["START_DATE_Associate_Degree"])) ?? '';
	if ($_POST['END_DATE_Associate_Degree'] != '')
		$DEAC_SETUP["ASSOCIATE_DEGREE_END_DATE"] = date('Y-m-d', strtotime($_POST["END_DATE_Associate_Degree"])) ?? '';
	if ($_POST['START_DATE_Bachelor_Degree'] != '')
		$DEAC_SETUP["BACHELOR_DEGREE_START_DATE"] = date('Y-m-d', strtotime($_POST["START_DATE_Bachelor_Degree"])) ?? '';
	if ($_POST['END_DATE_Bachelor_Degree'] != '')
		$DEAC_SETUP["BACHEHLOR_DEGREE_END_DATE"] = date('Y-m-d', strtotime($_POST["END_DATE_Bachelor_Degree"])) ?? '';
	if ($_POST['START_DATE_Masters_Degree'] != '')
		$DEAC_SETUP["MASTERS_DEGREE_START_DATE"] = date('Y-m-d', strtotime($_POST["START_DATE_Masters_Degree"])) ?? '';
	if ($_POST['END_DATE_Masters_Degree'] != '')
		$DEAC_SETUP["MASTERS_DEGREE_END_DATE"] = date('Y-m-d', strtotime($_POST["END_DATE_Masters_Degree"])) ?? '';
	if ($_POST['START_DATE_First_Professional_Degree'] != '')
		$DEAC_SETUP["FIRST_PROF_DEGREE_START_DATE"] = date('Y-m-d', strtotime($_POST["START_DATE_First_Professional_Degree"])) ?? '';
	if ($_POST['END_DATE_First_Professional_Degree'] != '')
		$DEAC_SETUP["FIRST_PROF_DEGREE_END_DATE"] = date('Y-m-d', strtotime($_POST["END_DATE_First_Professional_Degree"])) ?? '';
	if ($_POST['START_DATE_Doctorate_Degree'] != '')
		$DEAC_SETUP["DOCTORATE_DEGREE_START_DATE"] = date('Y-m-d', strtotime($_POST["START_DATE_Doctorate_Degree"])) ?? '';
	if ($_POST['END_DATE_Doctorate_Degree'] != '')
		$DEAC_SETUP["DOCTORATE_DEGREE_END_DATE"] = date('Y-m-d', strtotime($_POST["END_DATE_Doctorate_Degree"])) ?? '';

	$DEAC_SETUP["DEAC_EXCLUSIONS"] = implode(',', $_POST["DROP_REASON_DEAC_Exclusions"]) ?? '';
	$DEAC_SETUP["IPEDS_EXCLUSIONS"] = implode(',', $_POST["DROP_REASON_IPEDS_Exclusions"]) ?? '';
	$DEAC_SETUP["WITHDRAWAL_EMPLOYED_RELATED_FIELD"] = implode(',', $_POST["DROP_REASON_Withdrawal_Employed_Related_Field"]) ?? '';
	$DEAC_SETUP["WITHDRAWAL_EMPLOYED_UNRELATED_FIELD"] = implode(',', $_POST["DROP_REASON_Withdrawal_Employed_Unrelated_Field"]) ?? '';
	$DEAC_SETUP["TRANSFERRED_TO_ANOTHER_INSTITUTION"] = implode(',', $_POST["DROP_REASON_Transferred_to_Another_Institution"]) ?? '';
	$DEAC_SETUP["ACTIVE_DUTY_MILITARY"] = implode(',', $_POST["DROP_REASON_Active_Duty_Military_Service"]) ?? '';
	$DEAC_SETUP["SATISFACTORY_ACADEMIC_PROGRESS"] = implode(',', $_POST["DROP_REASON_Satisfactory_Academic_Progress"]) ?? '';
	$DEAC_SETUP["FINANCIAL"] = implode(',', $_POST["DROP_REASON_Financial"]) ?? '';
	$DEAC_SETUP["PERSONAL"] = implode(',', $_POST["DROP_REASON_Personal"]) ?? '';
	$DEAC_SETUP["UNKNOWN_DROP_REASON"] = implode(',', $_POST["DROP_REASON_Unknown"]) ?? '';
	$DEAC_SETUP["OTHER"] = implode(',', $_POST["DROP_REASON_Other"]) ?? '';
	$DEAC_SETUP["EMPLOYED_IN_FIELD"] = implode(',', $_POST["PLACEMENT_STATUS_Employed_In_Field"]) ?? '';
	$DEAC_SETUP["EMPLOYED_iN_RELATED_FIELD"] = implode(',', $_POST["PLACEMENT_STATUS_Employed_In_Related_Field"]) ?? '';
	$DEAC_SETUP["EMPLOYED_IN_UNRELATED_FIELD"] = implode(',', $_POST["PLACEMENT_STATUS_Employed_In_Unrelated_Field"]) ?? '';
	$DEAC_SETUP["CONTINUED_EDUCATION_AT_ANOTHER_INSTITUTION"] = implode(',', $_POST["PLACEMENT_STATUS_Continued_Education_at_Another_Institution"]) ?? '';
	$DEAC_SETUP["ACTIVE_DUTY_MILITARY_SERVICE_PLACEMENT"] = implode(',', $_POST["PLACEMENT_STATUS_Active_Duty_Military_Service"]) ?? '';
	$DEAC_SETUP["UNEMPLOYED"] = implode(',', $_POST["PLACEMENT_STATUS_Unemployed"]) ?? '';
	$DEAC_SETUP["NOT_SEEKING_EMPLOYMENT"] = implode(',', $_POST["PLACEMENT_STATUS_Not_Seeking_Employment"]) ?? '';
	$DEAC_SETUP["UNKNOWN_PLACEMENT"] = implode(',', $_POST["PLACEMENT_STATUS_Unknown"]) ?? '';

	// MISMATCHED 
	$DEAC_SETUP["EXCLUDED_PROGRAMS"] = implode( ',' ,$_POST["PROGRAM_Excluded_Programs"] ) ?? '';
	$DEAC_SETUP["EXCLUDED_STUDENT_STATUS"] = implode(',', $_POST["STUDENT_STATUS_Excluded_Student_Status"]) ?? '';
	$DEAC_SETUP["OTHER_PLACEMENT"] = implode(',', $_POST["PLACEMENT_STATUS_Other"]) ?? '';


	$DEAC_SETUP["ACTIVE"] = 1;
	$DEAC_SETUP["PK_ACCOUNT"] = $_SESSION['PK_ACCOUNT'];


	#check if setup exists 

	$check_if_setup_exist  = $db->Execute("SELECT * FROM DEAC_SETUP WHERE PK_ACCOUNT = $_SESSION[PK_ACCOUNT]");


	if ($check_if_setup_exist->RecordCount() > 0) {
		$DEAC_SETUP["EDITED_ON"] = date('Y-m-d');
		$DEAC_SETUP["EDITED_BY"] = $_SESSION["PK_USER"];
		db_perform('DEAC_SETUP', $DEAC_SETUP, 'update', " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	} else {
		$DEAC_SETUP["CREATED_ON"] = date('Y-m-d');
		$DEAC_SETUP["CREATED_BY"] = $_SESSION["PK_USER"];
		db_perform('DEAC_SETUP', $DEAC_SETUP, 'insert');
	}

	// dd($_POST , $DEAC_SETUP);



	// exit;

}


$DEAC_STORED_DATA = $db->Execute("SELECT * FROM DEAC_SETUP WHERE PK_ACCOUNT = $_SESSION[PK_ACCOUNT]");
if ($DEAC_STORED_DATA->RecordCount() > 0) {



	$STUDENT_STATUS_Completed_Student_Status = explode(',', $DEAC_STORED_DATA->fields["COMPLETED_STUDENT_STATUS"]) ?? '';
	$STUDENT_STATUS_Active_Student_Status = explode(',', $DEAC_STORED_DATA->fields["ACTIVE_STUDENT_STATUS"]) ?? '';
	$STUDENT_STATUS_Graduated_Student_Status = explode(',', $DEAC_STORED_DATA->fields["GRADUATED_STUDENT_STATUS"]) ?? '';
	$STUDENT_STATUS_Inactive_Student_Status = explode(',', $DEAC_STORED_DATA->fields["INACTIVE_STUDENT_STATUS"]) ?? '';
	$STUDENT_STATUS_Withdrawn_Student_Status = explode(',', $DEAC_STORED_DATA->fields["WITHDRAWN_STUDENT_STATUS"]) ?? '';
	$PROGRAM_High_School_Programs = explode(',', $DEAC_STORED_DATA->fields["HIGH_SCHOOL_PROGRAMS"]) ?? '';
	$PROGRAM_Non_Degree_Programs = explode(',', $DEAC_STORED_DATA->fields["NON_DEGREE_PROGRAMS"]) ?? '';
	$PROGRAM_Associate_Degree_Programs = explode(',', $DEAC_STORED_DATA->fields["ASSOCIATE_DEGREE_PROGRAMS"]) ?? '';
	$PROGRAM_Bachelor_Degree_Programs = explode(',', $DEAC_STORED_DATA->fields["BACHELOR_DEGREE_PROGRAMS"]) ?? '';
	$PROGRAM_Masters_Degree_Programs = explode(',', $DEAC_STORED_DATA->fields["MASTERS_DEGREE_PROGRAMS"]) ?? '';
	$PROGRAM_First_Professional_Degree_Programs = explode(',', $DEAC_STORED_DATA->fields["FIRST_PROF_DEGREE_PROGRAMS"]) ?? '';
	$PROGRAM_Doctorate_Degree_Programs = explode(',', $DEAC_STORED_DATA->fields["DOCTORATE_PROGRAMS"]) ?? '';

	if ($DEAC_STORED_DATA->fields['HS_START_DATE'] != '')
		$START_DATE_High_School = date('m/d/Y', strtotime($DEAC_STORED_DATA->fields["HS_START_DATE"])) ?? '';
	if ($DEAC_STORED_DATA->fields['HS_END_DATE'] != '')
		$END_DATE_High_School = date('m/d/Y', strtotime($DEAC_STORED_DATA->fields["HS_END_DATE"])) ?? '';
	if ($DEAC_STORED_DATA->fields['NON_DEGREE_START_DATE'] != '')
		$START_DATE_Non_Degree = date('m/d/Y', strtotime($DEAC_STORED_DATA->fields["NON_DEGREE_START_DATE"])) ?? '';
	if ($DEAC_STORED_DATA->fields['NON_DEGREE_END_DATE'] != '')
		$END_DATE_Non_Degree = date('m/d/Y', strtotime($DEAC_STORED_DATA->fields["NON_DEGREE_END_DATE"])) ?? '';
	if ($DEAC_STORED_DATA->fields['ASSOCIATE_DEGREE_START_DATE'] != '')
		$START_DATE_Associate_Degree = date('m/d/Y', strtotime($DEAC_STORED_DATA->fields["ASSOCIATE_DEGREE_START_DATE"])) ?? '';
	if ($DEAC_STORED_DATA->fields['ASSOCIATE_DEGREE_END_DATE'] != '')
		$END_DATE_Associate_Degree = date('m/d/Y', strtotime($DEAC_STORED_DATA->fields["ASSOCIATE_DEGREE_END_DATE"])) ?? '';
	if ($DEAC_STORED_DATA->fields['BACHELOR_DEGREE_START_DATE'] != '')
		$START_DATE_Bachelor_Degree = date('m/d/Y', strtotime($DEAC_STORED_DATA->fields["BACHELOR_DEGREE_START_DATE"])) ?? '';
	if ($DEAC_STORED_DATA->fields['BACHEHLOR_DEGREE_END_DATE'] != '')
		$END_DATE_Bachelor_Degree = date('m/d/Y', strtotime($DEAC_STORED_DATA->fields["BACHEHLOR_DEGREE_END_DATE"])) ?? '';
	if ($DEAC_STORED_DATA->fields['MASTERS_DEGREE_START_DATE'] != '')
		$START_DATE_Masters_Degree = date('m/d/Y', strtotime($DEAC_STORED_DATA->fields["MASTERS_DEGREE_START_DATE"])) ?? '';
	if ($DEAC_STORED_DATA->fields['MASTERS_DEGREE_END_DATE'] != '')
		$END_DATE_Masters_Degree = date('m/d/Y', strtotime($DEAC_STORED_DATA->fields["MASTERS_DEGREE_END_DATE"])) ?? '';
	if ($DEAC_STORED_DATA->fields['FIRST_PROF_DEGREE_START_DATE'] != '')
		$START_DATE_First_Professional_Degree = date('m/d/Y', strtotime($DEAC_STORED_DATA->fields["FIRST_PROF_DEGREE_START_DATE"])) ?? '';
	if ($DEAC_STORED_DATA->fields['FIRST_PROF_DEGREE_END_DATE'] != '')
		$END_DATE_First_Professional_Degree = date('m/d/Y', strtotime($DEAC_STORED_DATA->fields["FIRST_PROF_DEGREE_END_DATE"])) ?? '';
	if ($DEAC_STORED_DATA->fields['DOCTORATE_DEGREE_START_DATE'] != '')
		$START_DATE_Doctorate_Degree = date('m/d/Y', strtotime($DEAC_STORED_DATA->fields["DOCTORATE_DEGREE_START_DATE"])) ?? '';
	if ($DEAC_STORED_DATA->fields['DOCTORATE_DEGREE_END_DATE'] != '')
		$END_DATE_Doctorate_Degree = date('m/d/Y', strtotime($DEAC_STORED_DATA->fields["DOCTORATE_DEGREE_END_DATE"])) ?? '';

	$DROP_REASON_DEAC_Exclusions = explode(',', $DEAC_STORED_DATA->fields["DEAC_EXCLUSIONS"]) ?? '';
	$DROP_REASON_IPEDS_Exclusions = explode(',', $DEAC_STORED_DATA->fields["IPEDS_EXCLUSIONS"]) ?? '';
	$DROP_REASON_Withdrawal_Employed_Related_Field = explode(',', $DEAC_STORED_DATA->fields["WITHDRAWAL_EMPLOYED_RELATED_FIELD"]) ?? '';
	$DROP_REASON_Withdrawal_Employed_Unrelated_Field = explode(',', $DEAC_STORED_DATA->fields["WITHDRAWAL_EMPLOYED_UNRELATED_FIELD"]) ?? '';
	$DROP_REASON_Transferred_to_Another_Institution = explode(',', $DEAC_STORED_DATA->fields["TRANSFERRED_TO_ANOTHER_INSTITUTION"]) ?? '';
	$DROP_REASON_Active_Duty_Military_Service = explode(',', $DEAC_STORED_DATA->fields["ACTIVE_DUTY_MILITARY"]) ?? '';
	$DROP_REASON_Satisfactory_Academic_Progress = explode(',', $DEAC_STORED_DATA->fields["SATISFACTORY_ACADEMIC_PROGRESS"]) ?? '';
	$DROP_REASON_Financial = explode(',', $DEAC_STORED_DATA->fields["FINANCIAL"]) ?? '';
	$DROP_REASON_Personal = explode(',', $DEAC_STORED_DATA->fields["PERSONAL"]) ?? '';
	$DROP_REASON_Unknown = explode(',', $DEAC_STORED_DATA->fields["UNKNOWN_DROP_REASON"]) ?? '';
	$DROP_REASON_Other = explode(',', $DEAC_STORED_DATA->fields["OTHER"]) ?? '';
	$PLACEMENT_STATUS_Employed_In_Field = explode(',', $DEAC_STORED_DATA->fields["EMPLOYED_IN_FIELD"]) ?? '';
	$PLACEMENT_STATUS_Employed_In_Related_Field = explode(',', $DEAC_STORED_DATA->fields["EMPLOYED_iN_RELATED_FIELD"]) ?? '';
	$PLACEMENT_STATUS_Employed_In_Unrelated_Field = explode(',', $DEAC_STORED_DATA->fields["EMPLOYED_IN_UNRELATED_FIELD"]) ?? '';
	$PLACEMENT_STATUS_Continued_Education_at_Another_Institution = explode(',', $DEAC_STORED_DATA->fields["CONTINUED_EDUCATION_AT_ANOTHER_INSTITUTION"]) ?? '';
	$PLACEMENT_STATUS_Active_Duty_Military_Service = explode(',', $DEAC_STORED_DATA->fields["ACTIVE_DUTY_MILITARY_SERVICE_PLACEMENT"]) ?? '';
	$PLACEMENT_STATUS_Unemployed = explode(',', $DEAC_STORED_DATA->fields["UNEMPLOYED"]) ?? '';
	$PLACEMENT_STATUS_Not_Seeking_Employment = explode(',', $DEAC_STORED_DATA->fields["NOT_SEEKING_EMPLOYMENT"]) ?? '';
	$PLACEMENT_STATUS_Unknown = explode(',', $DEAC_STORED_DATA->fields["UNKNOWN_PLACEMENT"]) ?? '';

	#Extra mapped fields - similar to insert

	$PROGRAM_Excluded_Programs = explode( ',' ,$DEAC_STORED_DATA->fields["EXCLUDED_PROGRAMS"] ) ?? '';
	$STUDENT_STATUS_Excluded_Student_Status = explode(',', $DEAC_STORED_DATA->fields["EXCLUDED_STUDENT_STATUS"]) ?? '';
	$PLACEMENT_STATUS_Other = explode(',', $DEAC_STORED_DATA->fields["OTHER_PLACEMENT"]) ?? '';
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
	<title><?= DEAC_DOC_SETUP ?> | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}

		.option_red>a>label {
			color: red !important
		}

		.custom_lable_av {
			color: #0e79e5;
			position: absolute;
			cursor: auto;
			top: 5px;
			transition: 0.2s ease all;
			-moz-transition: 0.2s ease all;
			-webkit-transition: 0.2s ease all;
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
						<h4 class="text-themecolor"><?= DEAC_DOC_SETUP ?></h4>
					</div>
				</div>
				<div class="row">

					<div class="col-12">
						<div class="card" style="margin-bottom: 0px !important;">
							<div class="card-body">
								<div class="row">
									<div class="col-md-6">
									</div>
									<div class="col-md-6" style="text-align: right;">
										<button type="button" onclick="window.location.href='deac_report'" class="btn waves-effect waves-light btn-info">Go To Report</button>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-12">
						<div class="card">
							<form class="floating-labels m-t-10" method="post" name="form1" id="form1" enctype="multipart/form-data">
								<div class="p-20">
									<div class="row">

										<div class="col-sm-4">
											<div class="row d-flex">
												<div class="col-12 col-sm-11 mb-5">
													<span class="bar"></span>
													<label><?= STUDENT_STATUS ?></label>
												</div>
											</div>

											<?php

											$options['STUDENT_STATUS'] = [
												"Excluded Student Status(es)",
												"Active Student Status(es)",
												"Completed Student Status(es)",
												"Graduated Student Status(es)",
												"Inactive Student Status(es)",
												"Withdrawn Student Status(es)"
											];

											foreach ($options['STUDENT_STATUS'] as $option) {
												# Option lable

												$option_id = $option_name = 'STUDENT_STATUS_' . str_replace(' ', '_', str_replace('(es)', '', $option));
												echo '
												<div class="row d-flex">
													<div class="col-1 col-sm-1"></div>
													<div class="col-11 col-sm-11 focused">
														 ' . $option . ' 
													</div>
												</div>
												';

												echo '<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
												<select id="' . $option_id . '" name="' . $option_name . '[]" multiple class="form-control">
												';

												$res_type_ess = $db->Execute("SELECT PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND Admissions = 0  order by ACTIVE DESC, STUDENT_STATUS ASC");


												while (!$res_type_ess->EOF) {
													$selected = $option_label = $option_class = "";
													$PK_STUDENT_STATUS 	= $res_type_ess->fields['PK_STUDENT_STATUS'];
													if (in_array($PK_STUDENT_STATUS, $$option_id)) {
														$selected = ' selected ';
													}

													$option_label = $res_type_ess->fields['STUDENT_STATUS'] . ' - ' . $res_type_ess->fields['DESCRIPTION'];
													if ($res_type_ess->fields['ACTIVE'] == 0) {
														$option_label .= " (Inactive)";
														$option_class .= " class='option_red' ";
													}

													echo '<option value="' . $PK_STUDENT_STATUS . '"  ' . $selected  . ' ' . $option_class . ' >' . $option_label . '</option>';
													$res_type_ess->MoveNext();
												}

												echo '</select> 
												</div>
											</div>';


												$js_inits .= '
											
											$("#' . $option_id . '").multiselect({
												includeSelectAllOption: true,
												allSelectedText: "All ' . STUDENT_STATUS . '",
												nonSelectedText: "",
												numberDisplayed: 1,
												nSelectedText: "' . STUDENT_STATUS . ' selected"
											});

											';
											}

											?>
											<!-- FOR PROGRAM OPTIONS  -->

											<div class="row d-flex">
												<div class="col-12 col-sm-11 mb-5">
													<label class="custom-lable">Programs</label>
												</div>
											</div>

											<?php

											$options['PROGRAM'] = [
												"Excluded Programs",
												"High School Programs",
												"Non Degree Programs",
												"Associate Degree Programs",
												"Bachelor Degree Programs",
												"Masters Degree Programs",
												"First Professional Degree Programs",
												"Doctorate Degree Programs",
											];

											foreach ($options['PROGRAM'] as $option) {
												# Option lable

												$option_id = $option_name = 'PROGRAM_' . str_replace(' ', '_', str_replace('(es)', '', $option));
												echo '
												<div class="row d-flex">
													<div class="col-1 col-sm-1"></div>
													<div class="col-11 col-sm-11 custom_lable">
													 
														 ' . $option . ' 
													</div>
												</div>
												';

												echo '<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
												<select id="' . $option_id . '" name="' . $option_name . '[]" multiple class="form-control">
												';

												$res_type_ess = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION,ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");


												while (!$res_type_ess->EOF) {
													$selected = $option_label = $option_class = "";
													$PK_STUDENT_STATUS 	= $res_type_ess->fields['PK_CAMPUS_PROGRAM'];
													if (in_array($PK_STUDENT_STATUS, $$option_id)) {
														$selected = ' selected ';
													}

													$option_label = $res_type_ess->fields['CODE'] . ' - ' . $res_type_ess->fields['DESCRIPTION'];
													if ($res_type_ess->fields['ACTIVE'] == 0) {
														$option_label .= " (Inactive)";
														$option_class .= " class='option_red' ";
													}

													echo '<option value="' . $PK_STUDENT_STATUS . '"  ' . $selected . '  ' . $option_label . ' ' . $option_class . ' >' . $option_label . '</option>';
													$res_type_ess->MoveNext();
												}

												echo '</select> 
												</div>
											</div>';


												$js_inits .= '
											
											$("#' . $option_id . '").multiselect({
												includeSelectAllOption: true,
												allSelectedText: "All ' . PROGRAM . '",
												nonSelectedText: "",
												numberDisplayed: 1,
												nSelectedText: "' . PROGRAM . ' selected"
											});

											';
											}


											?>




										</div>

										<div class="col-sm-4">
											<div class="row d-flex">
												<div class="col-12 col-sm-11  mb-5">
													<span class="bar"></span>
													<label>Drop Reasons</label>
												</div>
											</div>

											<?php

											$options['DROP_REASON'] = [
												"DEAC Exclusions",
												"IPEDS Exclusions",
												"Withdrawal Employed Related Field",
												"Withdrawal Employed Unrelated Field",
												"Transferred to Another Institution",
												"Active Duty Military Service",
												"Satisfactory Academic Progress",
												"Financial",
												"Personal",
												"Unknown",
												"Other"
											];

											foreach ($options['DROP_REASON'] as $option) {
												# Option lable

												$option_id = $option_name = 'DROP_REASON_' . str_replace(' ', '_', str_replace('(es)', '', $option));
												echo '
												<div class="row d-flex">
													<div class="col-1 col-sm-1"></div>
													<div class="col-11 col-sm-11 focused">
													 
														 ' . $option . ' 
													</div>
												</div>
												';

												echo '<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
												<select id="' . $option_id . '" name="' . $option_name . '[]" multiple class="form-control">
												';

												$res_type_ess = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION,ACTIVE from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, DROP_REASON ASC");


												while (!$res_type_ess->EOF) {
													$selected = $option_label = $option_class = "";
													$PK_STUDENT_STATUS 	= $res_type_ess->fields['PK_DROP_REASON'];
													if (in_array($PK_STUDENT_STATUS, $$option_id)) {
														$selected = ' selected ';
													}

													$option_label = $res_type_ess->fields['DROP_REASON'] . ' - ' . $res_type_ess->fields['DESCRIPTION'];
													if ($res_type_ess->fields['ACTIVE'] == 0) {
														$option_label .= " (Inactive)";
														$option_class .= " class='option_red' ";
													}

													echo '<option value="' . $PK_STUDENT_STATUS . '"  ' . $selected . '  ' . $option_label . ' ' . $option_class . ' >' . $option_label . '</option>';
													$res_type_ess->MoveNext();
												}

												echo '</select> 
												</div>
											</div>';

												define(DROP_REASON, 'Drop Reasons');
												$js_inits .= '
											
											$("#' . $option_id . '").multiselect({
												includeSelectAllOption: true,
												allSelectedText: "All ' . DROP_REASON . '",
												nonSelectedText: "",
												numberDisplayed: 1,
												nSelectedText: "' . DROP_REASON . ' selected"
											});

											';
											}


											?>

										</div>


										<div class="col-sm-4">
											<div class="row d-flex">
												<div class="col-12 col-sm-11  mb-5">
													<span class="bar"></span>
													<label>Placement Status</label>
												</div>
											</div>

											<?php

											$options['PLACEMENT_STATUS'] = [
												"Employed In Field",
												"Employed In Related Field",
												"Employed In Unrelated Field",
												"Continued Education at Another Institution",
												"Active Duty Military Service",
												"Unemployed",
												"Not Seeking Employment",
												"Unknown",
												"Other",
											];

											foreach ($options['PLACEMENT_STATUS'] as $option) {
												# Option lable

												$option_id = $option_name = 'PLACEMENT_STATUS_' . str_replace(' ', '_', str_replace('(es)', '', $option));
												echo '
												<div class="row d-flex">
													<div class="col-1 col-sm-1"></div>
													<div class="col-11 col-sm-11 focused">
														<span class="bar"></span>
														 ' . $option . ' 
													</div>
												</div>
												';

												echo '<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
												<select id="' . $option_id . '" name="' . $option_name . '[]" multiple class="form-control">
												';

												$res_type_ess = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");


												while (!$res_type_ess->EOF) {
													$selected = $option_label = $option_class = "";
													$PK_STUDENT_STATUS 	= $res_type_ess->fields['PK_PLACEMENT_STATUS'];
													if (in_array($PK_STUDENT_STATUS, $$option_id)) {
														$selected = ' selected ';
													}

													$option_label = $res_type_ess->fields['PLACEMENT_STATUS'];
													if ($res_type_ess->fields['ACTIVE'] == 0) {
														$option_label .= " (Inactive)";
														$option_class .= " class='option_red' ";
													}

													echo '<option value="' . $PK_STUDENT_STATUS . '"  ' . $selected . '  ' . $option_label . ' ' . $option_class . ' >' . $option_label . '</option>';
													$res_type_ess->MoveNext();
												}

												echo '</select> 
												</div>
											</div>';


												$js_inits .= '
											
											$("#' . $option_id . '").multiselect({
												includeSelectAllOption: true,
												allSelectedText: "All ' . PLACEMENT_STATUS . '",
												nonSelectedText: "",
												numberDisplayed: 1,
												nSelectedText: "' . PLACEMENT_STATUS . ' selected"
											});

											';
											}


											?>

										</div>

										<div class="col-sm-12">

											<!-- FOR DATE OPTIONS  -->

											<div class="row d-flex">
												<div class="col-12 col-sm-11  mb-5">
													<label>Date Ranges</label>
												</div>
											</div>

											<?php

											$options['DATE'] = [
												"High School Programs",
												"Non Degree Programs",
												"Associate Degree Programs",
												"Bachelor Degree Programs",
												"Masters Degree Programs",
												"First Professional Degree Programs",
												"Doctorate Degree Programs",
											];

											foreach ($options['DATE'] as $option) {
												# Option lable

												$option_id = $option_name = 'DATE_' . str_replace(' ', '_', str_replace(' Programs', '', $option));
												$date_val = '';
												$date_val_start = 'START_' . $option_id;
												$date_val_end = 'END_' . $option_id;
												$date_val_start = $$date_val_start;
												$date_val_end = $$date_val_end;

												// dump($date_val_start,$date_val_end);;
												// echo '
												// <div class="row d-flex">
												// 	<div class="col-1 col-sm-1"></div>
												// 	<div class="col-11 col-sm-11 focused">
												// 		<span class="bar"></span>
												// 		<label>' . $option . '</label>
												// 	</div>
												// </div>
												// ';

												echo '<div class="row d-flex">
													 
													<div class="col-sm-3 p-2 ml-5 pl-0">
													 ' . str_replace('Programs' , 'Dates' , $option) . ' 
													</div>
													<div class="col-sm-2 form-group">
													<input type="text" class="form-control date " id="START_' . $option_id . '" name="START_' . $option_name . '" value="' . $date_val_start . '" placeholder="Start Date">
													</div>

													<div class="col-sm-2 form-group">
													<input type="text" class="form-control date " id="END_' . $option_id . '" name="END_' . $option_name . '" value="' . $date_val_end . '" placeholder="End Date">
													</div>
												</div>
												';
											}
											?>
										</div>


									</div>


									<div class="row">

										<div class="col-12 col-sm-12 text-center ">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>
											<button type="button" onclick="window.location.href='deac_report'" class="btn waves-effect waves-light btn-dark"><?= CANCEL ?></button>
										</div>
									</div>
								</div>
							</form>
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
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />

	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {



			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});


			<?php echo $js_inits ?>
		});
	</script>
</body>

</html>