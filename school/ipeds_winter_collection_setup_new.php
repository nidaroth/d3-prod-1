<?
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/ipeds_winter_collection_setup.php");
require_once("check_access.php");
// "Part A 06 Ledger Codes",
// 	"Part A 07 Ledger Codes",
// 	"Part A 08 Ledger Codes",
// 	"Part B 01 Ledger Codes",
// 	"Part B 03 Ledger Codes",
// 	"Part C 01 Ledger Codes",
// 	"Part C 02 Ledger Codes",
// 	"Part C 03 Ledger Codes",
// 	"Part C 04 Ledger Codes",
// 	"Part C 05 Ledger Codes",
// 	"Part C 06 Ledger Codes",
// 	"Part C 07 Ledger Codes",
// 	"Part C 08 Ledger Codes",
// 	"Part C 09 Ledger Codes",
// 	"Part D 06 Ledger Codes",
// 	"Federal Pell Grant",
// 	"Subsidized Loan",
// 	"Post 9/11 GI Bill Benefits",
// 	"Department of Defense Tuition Assistance Program"
$tooltips =  array(
	'Part A 01 Ledger Codes' => 'Grants or scholarships from the federal government, state/local government, or the institution (Do NOT include student loans)',
	'Part A 06 Ledger Codes' => 'Federal Work Study, loans to students, grant or scholarship aid from the federal government, state/local government, the institution, or other sources known to the institution',
	'Part A 07 Ledger Codes' => 'Loans to students or grant or scholarship aid from the federal government, state/local government, or the institution',
	'Part A 08 Ledger Codes' => 'Grant or scholarship aid from the federal government, state/local government, or the institution',
	'Part A 08 / Part E 02 Ledger Codes' => 'Grant or scholarship aid from the federal government, state/local government, or the institution',
	'Part B 01 Ledger Codes' => 'Grant or scholarship aid from other sources known to the institution (Do NOT include student loans)',
	'Part B 03 Ledger Codes' => 'State/local government grant or scholarship aid (includes fellowships, waivers, and employee exemptions)',
	'Part C 01 Ledger Codes' => 'Grants or scholarships from the federal government, state/local government, or the institution',
	'Part C 02 Ledger Codes' => 'Federal grants and scholarship aid',
	'Part C 04 Ledger Codes' => 'Other federal grants and scholarship aid',
	'Part C 05 Ledger Codes' => 'State/local government grant or scholarship aid (includes fellowships/tuition waivers/exemptions)',
	'Part C 06 Ledger Codes' => 'Institutional grant or scholarship aid (includes fellowships/tuition waivers/exemptions)',
	'Part C 07 Ledger Codes' => 'Loans to students',
	'Part C 08 Ledger Codes' => 'Federal loans to students',
	'Part C 09 Ledger Codes' => 'Other loans to students (including private loans)',
	'Part D 06 Ledger Codes' => 'Grant or scholarship aid from the federal government, state/local government, or the institution awarded to these students. Do not include HEERF grants.',
	'Part C 03 Ledger Codes' => 'Federal Pell Grants'
);

$LEDGER_CODES_FIELDS_LIST = [
	"Part A 01 Ledger Codes",
	"Part A 06 Ledger Codes",
	"Part A 07 Ledger Codes",
	"Part A 08 Ledger Codes",
	"Part B 01 Ledger Codes",
	"Part B 03 Ledger Codes",
	"Part C 01 Ledger Codes",
	"Part C 02 Ledger Codes",
	"Part C 03 Ledger Codes",
	"Part C 04 Ledger Codes",
	"Part C 05 Ledger Codes",
	"Part C 06 Ledger Codes",
	"Part C 07 Ledger Codes",
	"Part C 08 Ledger Codes",
	"Part C 09 Ledger Codes",
	"Part D 06 Ledger Codes",
	"Federal Pell Grant",
	"Subsidized Loan",
	"Post 9/11 GI Bill Benefits",
	"Department of Defense Tuition Assistance Program"
];

$res_add_on = $db->Execute("SELECT COE,ECM,_1098T,_90_10,IPEDS,POPULATION_REPORT FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if ($res_add_on->fields['IPEDS'] == 0 || check_access('MANAGEMENT_IPEDS') == 0) {
	header("location:../index");
	exit;
}

$msg = '';
if (!empty($_POST)) {
	//echo "<pre>";print_r($_POST);exit;

	$res = $db->Execute("select * from S_IPEDS_WINTER_COLLECTION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

	$IPEDS_WINTER_COLLECTION['EXCLUDED_PROGRAM'] 		= implode(",", $_POST['EXCLUDED_PROGRAM']);
	$IPEDS_WINTER_COLLECTION['EXCLUDED_STUDENT_STATUS'] = implode(",", $_POST['EXCLUDED_STUDENT_STATUS']);
	$IPEDS_WINTER_COLLECTION['EXCLUDED_DROP_REASON'] 	= implode(",", $_POST['EXCLUDED_DROP_REASON']);
	$IPEDS_WINTER_COLLECTION['TRANSFER_OUT'] 			= implode(",", $_POST['TRANSFER_OUT']);
	$IPEDS_WINTER_COLLECTION['APPLICANT'] 				= implode(",", $_POST['APPLICANT']);
	$IPEDS_WINTER_COLLECTION['ADMISSIONS'] 				= implode(",", $_POST['ADMISSIONS']);
	$IPEDS_WINTER_COLLECTION['LARGEST_PROGRAM'] 		= implode(",", $_POST['LARGEST_PROGRAM']);
	




	foreach ($LEDGER_CODES_FIELDS_LIST as $POSTED_FIELD) {
		$dynamic_field_id  = strtoupper(str_replace([' ', '/'], '_', $POSTED_FIELD));
		$IPEDS_WINTER_COLLECTION[$dynamic_field_id]  = implode(",", $_POST[$dynamic_field_id]);
		// echo strtoupper($dynamic_field_id) . "<br>";
	}






	if ($res->RecordCount() == 0) {
		$IPEDS_WINTER_COLLECTION['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$IPEDS_WINTER_COLLECTION['CREATED_BY'] = $_SESSION['PK_USER'];
		$IPEDS_WINTER_COLLECTION['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_IPEDS_WINTER_COLLECTION', $IPEDS_WINTER_COLLECTION, 'insert');
		$PK_IPEDES_SPRING_COLLECTION = $db->insert_ID();
	} else {
		$IPEDS_WINTER_COLLECTION['EDITED_BY'] = $_SESSION['PK_USER'];
		$IPEDS_WINTER_COLLECTION['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_IPEDS_WINTER_COLLECTION', $IPEDS_WINTER_COLLECTION, 'update', " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_IPEDES_SPRING_COLLECTION = $_GET['id'];
	}
	header("location:ipeds_winter_collection_setup_new");
}
$res = $db->Execute("select * from S_IPEDS_WINTER_COLLECTION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$EXCLUDED_PROGRAM_ARR 		 = explode(",", $res->fields['EXCLUDED_PROGRAM']);
$EXCLUDED_STUDENT_STATUS_ARR = explode(",", $res->fields['EXCLUDED_STUDENT_STATUS']);
$EXCLUDED_DROP_REASON_ARR 	 = explode(",", $res->fields['EXCLUDED_DROP_REASON']);
$TRANSFER_OUT_ARR 		 	 = explode(",", $res->fields['TRANSFER_OUT']);
$APPLICANT_ARR				 = explode(",", $res->fields['APPLICANT']);
$ADMISSIONS_ARR				 = explode(",", $res->fields['ADMISSIONS']);
$LARGEST_PROGRAM_ARR		 = explode(",", $res->fields['LARGEST_PROGRAM']);


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
	<title><?= MNU_IPEDS_WINTER_COLLECTIONS_SETUP_TITLE ?> | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}

		/* added color for inactive text -  DIAM 22 */
		.red a>label {
			color: red !important;
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
						<h4 class="text-themecolor"><?= MNU_IPEDS_WINTER_COLLECTIONS_SETUP_TITLE ?></h4>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
								<div class="p-20">
									<div class="d-flex">
										<div class="col-6 col-sm-6 ">

											<div class="d-flex">
												<div class="col-12 col-sm-12 ">
													<span class="bar"></span>
													<label><?= SELECT_SETUP_CODES ?></label>
												</div>
											</div>
											<br /><br /><br />

											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span>
													<label><?= EXCLUDED_PROGRAM ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="EXCLUDED_PROGRAM" name="EXCLUDED_PROGRAM[]" multiple class="form-control">
														<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION,ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_CAMPUS_PROGRAM 	= $res_type->fields['PK_CAMPUS_PROGRAM'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach ($EXCLUDED_PROGRAM_ARR as $EXCLUDED_PROGRAM) {
																if ($EXCLUDED_PROGRAM == $PK_CAMPUS_PROGRAM) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_CAMPUS_PROGRAM ?>" <?= $selected ?>><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['DESCRIPTION'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span>
													<label><?= EXCLUDED_STUDENT_STATUS ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="EXCLUDED_STUDENT_STATUS" name="EXCLUDED_STUDENT_STATUS[]" multiple class="form-control">
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION,ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach ($EXCLUDED_STUDENT_STATUS_ARR as $EXCLUDED_STUDENT_STATUS) {
																if ($EXCLUDED_STUDENT_STATUS == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_STUDENT_STATUS ?>" <?= $selected ?>><?= $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['DESCRIPTION'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span>
													<label><?= EXCLUDED_DROP_REASON ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="EXCLUDED_DROP_REASON" name="EXCLUDED_DROP_REASON[]" multiple class="form-control">
														<? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION,ACTIVE from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, DROP_REASON ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach ($EXCLUDED_DROP_REASON_ARR as $EXCLUDED_DROP_REASON) {
																if ($EXCLUDED_DROP_REASON == $PK_DROP_REASON) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_DROP_REASON ?>" <?= $selected ?>><?= $res_type->fields['DROP_REASON'] . ' - ' . $res_type->fields['DESCRIPTION'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span>
													<label><?= TRANSFER_OUT ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="TRANSFER_OUT" name="TRANSFER_OUT[]" multiple class="form-control">
														<? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION,ACTIVE from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC,DROP_REASON ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach ($TRANSFER_OUT_ARR as $TRANSFER_OUT) {
																if ($TRANSFER_OUT == $PK_DROP_REASON) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_DROP_REASON ?>" <?= $selected ?>><?= $res_type->fields['DROP_REASON'] . ' - ' . $res_type->fields['DESCRIPTION'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=LARGEST_PROGRAM?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="LARGEST_PROGRAM" name="LARGEST_PROGRAM[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION,ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_CAMPUS_PROGRAM 	= $res_type->fields['PK_CAMPUS_PROGRAM']; 
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach($LARGEST_PROGRAM_ARR as $LARGEST_PROGRAM){
																if($LARGEST_PROGRAM == $PK_CAMPUS_PROGRAM) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_CAMPUS_PROGRAM?>" <?=$selected?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION'].' '.$Status?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span>
													<label><?= APPLICANT ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="APPLICANT" name="APPLICANT[]" multiple class="form-control">
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION,ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC,STUDENT_STATUS ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach ($APPLICANT_ARR as $APPLICANT) {
																if ($APPLICANT == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_STUDENT_STATUS ?>" <?= $selected ?>><?= $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['DESCRIPTION'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span>
													<label><?= ADMISSIONS ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="ADMISSIONS" name="ADMISSIONS[]" multiple class="form-control">
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION,ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, STUDENT_STATUS ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach ($ADMISSIONS_ARR as $ADMISSIONS) {
																if ($ADMISSIONS == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_STUDENT_STATUS ?>" <?= $selected ?>><?= $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['DESCRIPTION'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

										</div>
										<div class="col-6 col-sm-6 ">
											<div class="d-flex">
												<div class="col-12 col-sm-12 ">
													<span class="bar"></span>
													<label>Student Financial Aid Ledger Code Setup</label>
												</div>
											</div>
											<br /><br /><br />

											<?php





											$ALL_ACTIVE_LEDGER_CODES_RES = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION,ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by ACTIVE DESC, CODE ASC");
											while (!$ALL_ACTIVE_LEDGER_CODES_RES->EOF) {
												$CODE_STATUS = '';
												if ($ALL_ACTIVE_LEDGER_CODES_RES->fields['ACTIVE'] == '0') {
													$CODE_STATUS = ' (Inactive) ';
												}
												$ALL_ACTIVE_LEDGER_CODES[] = [
													'PK_AR_LEDGER_CODE' => $ALL_ACTIVE_LEDGER_CODES_RES->fields['PK_AR_LEDGER_CODE'], 'OPTION_TEXT' => $ALL_ACTIVE_LEDGER_CODES_RES->fields['CODE'] . ' - ' . $ALL_ACTIVE_LEDGER_CODES_RES->fields['LEDGER_DESCRIPTION'] . ' ' . $CODE_STATUS

												];
												$ALL_ACTIVE_LEDGER_CODES_RES->MoveNext();
											}

											#GET ALL SAVED DATA 
											$SAVED_CODES = $db->Execute("select * from S_IPEDS_WINTER_COLLECTION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ")->fields;

											// dump($SAVED_CODES);

											foreach ($LEDGER_CODES_FIELDS_LIST as $dynamic_field_index => $dynamic_field_name) {
												# code...
												$dynamic_field_id  = strtoupper(str_replace([' ', '/'], '_', $dynamic_field_name));
												$diff_lable = $dynamic_field_name ;
												if ($dynamic_field_name == 'Part A 08 Ledger Codes') {
													$diff_lable = 'Part A 08 / Part E 02 Ledger Codes';
												}

												$select_html  = '<div class="d-flex">
												<div class="col-6 col-sm-6 focused d-flex align-items-center mb-3">
												<label style="display: flex; align-items: center;height:38px">
													' . $diff_lable;

												if (isset($tooltips[$dynamic_field_name]) && $tooltips[$dynamic_field_name] != '') {
													$select_html .= '<i class="mdi mdi-help-circle help_size" style="margin-left: 15px;color:#ff4617" title="' . $tooltips[$dynamic_field_name] . '" data-toggle="tooltip" data-placement="right"></i>';
												}
												$select_html .= '</label>
																</div>
															</div>';


												$select_html  .= '<div class="d-flex">';
												$select_html .= '<div class="col-12 col-sm-12 form-group">';
												$select_html .=  '<select id="' . $dynamic_field_id . '" name="' . $dynamic_field_id . '[]" multiple class="form-control" >';

												$exploded_saved_codes = '';
												$exploded_saved_codes = explode(',', $SAVED_CODES[$dynamic_field_id]);
												// dump($dynamic_field_id , $SAVED_CODES , $SAVED_CODES[$dynamic_field_id]);  
												foreach ($ALL_ACTIVE_LEDGER_CODES as $LEDGER_CODE) {
													$selected_str = '';
													if (in_array($LEDGER_CODE['PK_AR_LEDGER_CODE'], $exploded_saved_codes)) {
														$selected_str = ' selected ';
													}
													$select_html .= "<option value='$LEDGER_CODE[PK_AR_LEDGER_CODE]' $selected_str >$LEDGER_CODE[OPTION_TEXT]</option>";
												}


												$select_html .=  '</select>';
												$select_html .= '</div>';
												$select_html .= '</div>';

												echo $select_html;
											}



											?>


										</div>
									</div>

									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										<div class="col-9 col-sm-9">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>
											<button type="button" onclick="window.location.href='index'" class="btn waves-effect waves-light btn-dark"><?= CANCEL ?></button>
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

		function show_setup() {
			var w = 1300;
			var h = 550;
			// var id = common_id;
			var left = (screen.width / 2) - (w / 2);
			var top = (screen.height / 2) - (h / 2);
			var parameter = 'toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,width=' + w + ', height=' + h + ', top=' + top + ', left=' + left;
			window.open('program_award_level_setup', '', parameter);
			return false;
		}
	</script>
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			<?php
			foreach ($LEDGER_CODES_FIELDS_LIST as $dynamic_field_index => $dynamic_field_name) {
				# code... 
				$dynamic_field_id  = strtoupper(str_replace([' ', '/'], '_', $dynamic_field_name));
				echo "
				$('#" . $dynamic_field_id . "').multiselect({
						includeSelectAllOption: true,
						allSelectedText: 'All Ledger Codes',
						nonSelectedText: '',
						numberDisplayed: 3,
						nSelectedText: 'Ledger Codes selected'
					});
					
					";
			}
			?>
		});
		jQuery(document).ready(function($) {
			$('#EXCLUDED_DROP_REASON').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= EXCLUDED_DROP_REASON ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= EXCLUDED_DROP_REASON ?> selected'
			});

			$('#TRANSFER_OUT').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= TRANSFER_OUT ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= TRANSFER_OUT ?> selected'
			});

			$('#EXCLUDED_STUDENT_STATUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= EXCLUDED_STUDENT_STATUS ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= EXCLUDED_STUDENT_STATUS ?> selected'
			});

			$('#EXCLUDED_PROGRAM').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= EXCLUDED_PROGRAM ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= EXCLUDED_PROGRAM ?> selected'
			});

			$('#APPLICANT').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= APPLICANT ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= APPLICANT ?> selected'
			});

			$('#ADMISSIONS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= ADMISSIONS ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= ADMISSIONS ?> selected'
			});

			$('#LARGEST_PROGRAM').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=LARGEST_PROGRAM?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?=LARGEST_PROGRAM?> selected'
			});
			/////////////////

			// added color for inactive text -  DIAM 22
			child = $('.multiselect-container').children();
			child.each(function(i, val) {
				var str1 = val.innerText
				if (str1.indexOf("Inactive") != -1) {
					$(this).addClass('red')
				}

			});

		});
	</script>
</body>

</html>