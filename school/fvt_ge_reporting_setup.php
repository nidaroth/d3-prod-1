<?
ini_set("pcre.backtrack_limit", "5000000");

require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/transaction_summary.php");
require_once("../language/student_balance.php");
require_once("../language/ar_leder_code.php");
require_once("../language/_1098T_Setup.php");

require_once("check_access.php");


require_once("get_department_from_t.php");
require_once("../language/abhes.php");

define("HOUSING_AND_FOOD_ALLOWANCES", "Allowance for Housing and Food");
define('BOOK_ALLOWANCE', 'Allowance for Books, Supplies and Equipment');
define('ANNUAL_COST_OF_ATTENDANCE', 'Annual Cost of Attendance');
define("INSTITUTIONAL_GRANT", "Institutional Grants and Scholarships");
define("OTHER_GRANT", "Other State, Tribal or Private Grants");
define("PRIVATE_LOAN", "Private Loan");
define("GRADUATED_STUDENT_STATUS_LABEL", "Graduated Student Statuses");
define("WITHDRWAL_DROP_STUDENT_STATUS_LABEL", "Withdrawn Student Statuses");
define("STUDENT_EVENT_STATUS_LABEL", "Student Event Statuses");



if (check_access('REPORT_ACCOUNTING') == 0) {
	header("location:../index");
	exit;
}

if (!empty($_POST)) {

	// dump($_POST);
	$check_arr = array('EXCLUDED_PROGRAMS','EXCLUDED_STUDENT_STATUS','GRADUATED_STUDENT_STATUS','WITHDRWAL_DROP_STUDENT_STATUS','ANNUAL_COST_OF_ATTENDANCE',
	'TUITION_FEES_RELATED_EXPENSES','BOOK_ALLOWANCE','HOUSING_AND_FOOD_ALLOWANCES','INSTITUTIONAL_GRANT','OTHER_GRANT','PRIVATE_LOAN','LICENSURE_TYPE','TOOK_EXAM','PASSED_EXAM');
	$check_arr_po = array();
	// dump($_POST);
	if (isset($_POST)) {
		$setup_id = $db->Execute("SELECT * FROM S_FVT_GE_REPORTING_SETUP WHERE PK_ACCOUNT = " . $_SESSION['PK_ACCOUNT'])->fields['PK_S_FVT_GE_REPORTING_SETUP'];
		// dump($setup_id);
		foreach ($_POST as $key => $value) {
			$S_FVT_GE_REPORTING_SETUP[$key] = implode(',', $value);
			$check_arr_po [] = $key;
		}
		// dump("S_FVT_GE_REPORTING_SETUP", $S_FVT_GE_REPORTING_SETUP);

		$ddd = array_diff($check_arr, $check_arr_po);
		foreach ($ddd as $key => $value) {
			$S_FVT_GE_REPORTING_SETUP[$value] ='';
		}
		// dump("S_FVT_GE_REPORTING_SETUP", $S_FVT_GE_REPORTING_SETUP);
		if ($setup_id) {
			//update
			$S_FVT_GE_REPORTING_SETUP['EDITED_BY'] = $_SESSION['PK_USER'];
			$S_FVT_GE_REPORTING_SETUP['EDITED_ON'] = date("Y-m-d H:i:s");
			db_perform('S_FVT_GE_REPORTING_SETUP', $S_FVT_GE_REPORTING_SETUP, 'update', " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		} else {
			//insert
			$S_FVT_GE_REPORTING_SETUP['EDITED_BY'] = $_SESSION['PK_USER'];
			$S_FVT_GE_REPORTING_SETUP['EDITED_ON'] = date("Y-m-d H:i:s");
			$S_FVT_GE_REPORTING_SETUP['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
			db_perform('S_FVT_GE_REPORTING_SETUP', $S_FVT_GE_REPORTING_SETUP, 'insert');
		}
	}
}

$setup = $db->Execute("SELECT * FROM S_FVT_GE_REPORTING_SETUP WHERE PK_ACCOUNT = " . $_SESSION['PK_ACCOUNT']);
foreach ($setup->fields as $key => $value) {
	${$key . '_ARR'} = explode(',', $value);
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
	<title>FVT/GE Reporting Setup | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}

		#advice-required-entry-PK_STUDENT_STATUS,
		#advice-required-entry-PK_AR_LEDGER_CODE {
			position: absolute;
			top: 57px;
			width: 140px
		}

		.dropdown-menu>li>a {
			white-space: nowrap;
		}

		.option_red>a>label {
			color: red !important
		}
		/* #LICENSURE_TYPE .multiselect-container ul li{
			overflow-x: scroll;
			max-width: 650px !important;		
		} */

		.dropdown-menu.show{
			width:435px;
		}

		

		#LICENSURE_TYPE_DIV .dropdown-menu.show, #TOOK_EXAM_DIV .dropdown-menu.show,  #PASSED_EXAM_DIV .dropdown-menu.show{
			width:417px;
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
							FVT/GE Reporting Setup
						</h4>
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
                                        <button type="button" onclick="window.location.href='fvt_ge_reporting'" class="btn waves-effect waves-light btn-info">Go To Report</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
					</div>
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body pt-5">
									<br><br>
									<div class="row">
										<div class="col-md-4">
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span>
													<label><?= EXCLUDED_PROGRAMS ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="EXCLUDED_PROGRAMS" name="EXCLUDED_PROGRAMS[]" multiple class="form-control">
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

															foreach ($EXCLUDED_PROGRAMS_ARR as $EXCLUDED_PROGRAMS) {
																if ($EXCLUDED_PROGRAMS == $PK_CAMPUS_PROGRAM) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option data-id="test" value="<?= $PK_CAMPUS_PROGRAM ?>" <?= $selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['DESCRIPTION'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											<!--  -->
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
															<option value="<?= $PK_STUDENT_STATUS ?>" <?= $selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['DESCRIPTION'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span>
													<label><?= GRADUATED_STUDENT_STATUS_LABEL ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="GRADUATED_STUDENT_STATUS" name="GRADUATED_STUDENT_STATUS[]" multiple class="form-control">
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS'];
															foreach ($GRADUATED_STUDENT_STATUS_ARR as $GRADUATED_STUDENT_STATUS) {
																if ($GRADUATED_STUDENT_STATUS == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															}
															$option_labels = $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['DESCRIPTION'];
															if ($res_type->fields['ACTIVE'] == 0) {
																$option_labels .= " (Inactive)";
															}
														?>
															<option value="<?= $PK_STUDENT_STATUS ?>" <?= $selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> <? if ($res_type->fields['ACTIVE'] == 0) {
																															echo "class='option_red'";
																														} ?>><?= $option_labels ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span>
													<label><?= WITHDRWAL_DROP_STUDENT_STATUS_LABEL ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="WITHDRWAL_DROP_STUDENT_STATUS" name="WITHDRWAL_DROP_STUDENT_STATUS[]" multiple class="form-control">
														<? $res_type_ss = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
														while (!$res_type_ss->EOF) {
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type_ss->fields['PK_STUDENT_STATUS'];
															foreach ($WITHDRWAL_DROP_STUDENT_STATUS_ARR as $WITHDRWAL_DROP_STUDENT_STATUS) {
																if ($WITHDRWAL_DROP_STUDENT_STATUS == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															}

															$option_label = $res_type_ss->fields['STUDENT_STATUS'] . ' - ' . $res_type_ss->fields['DESCRIPTION'];
															if ($res_type_ss->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
															<option value="<?= $PK_STUDENT_STATUS ?>" <?= $selected ?> <? if($res_type_ss->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> <? if ($res_type_ss->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $option_label ?></option>
														<? $res_type_ss->MoveNext();
														} ?>
													</select>
												</div>
											</div>


										</div>

										<div class="col-sm-4">
										<div class="row d-flex">
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span>
													<label><h6>Ledger Codes</h6></label>
												</div>
											</div>
											<br />			
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span>
													<label>Annual Cost of Attendance</label>
												</div>
											</div>
											<div class="d-flex" style="margin-bottom: 20px;">
												<div class="col-12 col-sm-12 form-group">
													<select id="ANNUAL_COST_OF_ATTENDANCE" name="ANNUAL_COST_OF_ATTENDANCE[]" multiple class="form-control">
														<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION,ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach ($ANNUAL_COST_OF_ATTENDANCE_ARR as $EXCLUDED_FEE_LEDGER_CODES) {
																if ($EXCLUDED_FEE_LEDGER_CODES == $PK_AR_LEDGER_CODE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_AR_LEDGER_CODE ?>" <?= $selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['LEDGER_DESCRIPTION'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>


											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span>
													<label>Tuition & Fees</label>
												</div>
											</div>
											<div class="d-flex" style="margin-bottom: 20px;">
												<div class="col-12 col-sm-12 form-group">
													<select id="TUITION_FEES_RELATED_EXPENSES" name="TUITION_FEES_RELATED_EXPENSES[]" multiple class="form-control">
														<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION,ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach ($TUITION_FEES_RELATED_EXPENSES_ARR as $EXCLUDED_FEE_LEDGER_CODES) {
																if ($EXCLUDED_FEE_LEDGER_CODES == $PK_AR_LEDGER_CODE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_AR_LEDGER_CODE ?>" <?= $selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['LEDGER_DESCRIPTION'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span>
													<label>Allowance for Books, Supplies and Equipment</label>
												</div>
											</div>
											<div class="d-flex" style="margin-bottom: 20px;">
												<div class="col-12 col-sm-12 form-group">
													<select id="BOOK_ALLOWANCE" name="BOOK_ALLOWANCE[]" multiple class="form-control">
														<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION,ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach ($BOOK_ALLOWANCE_ARR as $EXCLUDED_FEE_LEDGER_CODES) {
																if ($EXCLUDED_FEE_LEDGER_CODES == $PK_AR_LEDGER_CODE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_AR_LEDGER_CODE ?>" <?= $selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['LEDGER_DESCRIPTION'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span>
													<label><?= HOUSING_AND_FOOD_ALLOWANCES ?></label>
												</div>
											</div>
											<div class="d-flex" style="margin-bottom: 20px;">
												<div class="col-12 col-sm-12 form-group">
													<select id="HOUSING_AND_FOOD_ALLOWANCES" name="HOUSING_AND_FOOD_ALLOWANCES[]" multiple class="form-control">
														<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION,ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach ($HOUSING_AND_FOOD_ALLOWANCES_ARR as $EXCLUDED_FEE_LEDGER_CODES) {
																if ($EXCLUDED_FEE_LEDGER_CODES == $PK_AR_LEDGER_CODE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_AR_LEDGER_CODE ?>" <?= $selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['LEDGER_DESCRIPTION'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span>
													<label><?= INSTITUTIONAL_GRANT ?></label>
												</div>
											</div>
											<div class="d-flex" style="margin-bottom: 20px;">
												<div class="col-12 col-sm-12 form-group">
													<select id="INSTITUTIONAL_GRANT" name="INSTITUTIONAL_GRANT[]" multiple class="form-control">
														<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION,ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach ($INSTITUTIONAL_GRANT_ARR as $EXCLUDED_FEE_LEDGER_CODES) {
																if ($EXCLUDED_FEE_LEDGER_CODES == $PK_AR_LEDGER_CODE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_AR_LEDGER_CODE ?>" <?= $selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['LEDGER_DESCRIPTION'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span>
													<label><?= OTHER_GRANT ?></label>
												</div>
											</div>
											<div class="d-flex" style="margin-bottom: 20px;">
												<div class="col-12 col-sm-12 form-group">
													<select id="OTHER_GRANT" name="OTHER_GRANT[]" multiple class="form-control">
														<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION,ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach ($OTHER_GRANT_ARR as $EXCLUDED_FEE_LEDGER_CODES) {
																if ($EXCLUDED_FEE_LEDGER_CODES == $PK_AR_LEDGER_CODE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_AR_LEDGER_CODE ?>" <?= $selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['LEDGER_DESCRIPTION'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span>
													<label><?= PRIVATE_LOAN ?></label>
												</div>
											</div>
											<div class="d-flex" style="margin-bottom: 20px;">
												<div class="col-12 col-sm-12 form-group">
													<select id="PRIVATE_LOAN" name="PRIVATE_LOAN[]" multiple class="form-control">
														<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION,ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach ($PRIVATE_LOAN_ARR as $EXCLUDED_FEE_LEDGER_CODES) {
																if ($EXCLUDED_FEE_LEDGER_CODES == $PK_AR_LEDGER_CODE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_AR_LEDGER_CODE ?>" <?= $selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['LEDGER_DESCRIPTION'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

										</div>


										<div class="col-sm-4">
											<div class="row d-flex">
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span>
													<label><h6>Student Event Type</h6></label>
												</div>
											</div>
											<br />

											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span>
													<label><?= LICENSURE_TYPE ?></label>
												</div>
											</div>
											<div class="row d-flex" id="LICENSURE_TYPE_DIV">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="LICENSURE_TYPE" name="LICENSURE_TYPE[]" multiple class="form-control">
														<? $PK_DEPARTMENT = get_department_from_t(6);
														$res_type = $db->Execute("select PK_NOTE_TYPE,NOTE_TYPE,DESCRIPTION,ACTIVE from M_NOTE_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 AND (PK_DEPARTMENT = '$PK_DEPARTMENT') order by ACTIVE DESC, NOTE_TYPE ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_NOTE_TYPE 	= $res_type->fields['PK_NOTE_TYPE'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach ($LICENSURE_TYPE_ARR as $LICENSURE_TYPE) {
																if ($LICENSURE_TYPE == $PK_NOTE_TYPE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_NOTE_TYPE ?>" <?= $selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['NOTE_TYPE'] . ' - ' . $res_type->fields['DESCRIPTION'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>


											<!-- FOR STUDENT EXAM RELATED EVENT STATUSES -->
											<div class="row d-flex">
												<div class="col-11 col-sm-11 focused" style="margin-top:7px;">
													<span class="bar"></span>
													<label><h6><?= STUDENT_EVENT_STATUS_LABEL ?></h6></label>
												</div>
											</div>
											<br />

											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span>
													<label>Attempted Licensure Exam</label>
												</div>
											</div>
											<div class="row d-flex" id="TOOK_EXAM_DIV">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="TOOK_EXAM" name="TOOK_EXAM[]" multiple class="form-control">
														<? $PK_DEPARTMENT = get_department_from_t(6);
														$res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS,ACTIVE from M_NOTE_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 3 AND (PK_DEPARTMENT = '$PK_DEPARTMENT') order by ACTIVE DESC, NOTE_STATUS ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_NOTE_STATUS 	= $res_type->fields['PK_NOTE_STATUS'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach ($TOOK_EXAM_ARR as $TOOK_EXAM) {
																if ($TOOK_EXAM == $PK_NOTE_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_NOTE_STATUS ?>" <?= $selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['NOTE_STATUS'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span>
													<label>Passed Licensure Exam</label>
												</div>
											</div>
											<div class="row d-flex" id="PASSED_EXAM_DIV">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="PASSED_EXAM" name="PASSED_EXAM[]" multiple class="form-control">
														<? $PK_DEPARTMENT = get_department_from_t(6);
														$res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS,ACTIVE from M_NOTE_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 3 AND (PK_DEPARTMENT = '$PK_DEPARTMENT') order by ACTIVE DESC, NOTE_STATUS ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_NOTE_STATUS 	= $res_type->fields['PK_NOTE_STATUS'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach ($PASSED_EXAM_ARR as $PASSED_EXAM) {
																if ($PASSED_EXAM == $PK_NOTE_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_NOTE_STATUS ?>" <?= $selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['NOTE_STATUS'] . ' ' . $Status ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

										</div>


									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group m-b-5" style="text-align:right">
												<button type="submit" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>

												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='fvt_ge_reporting'"><?= CANCEL ?></button>
											</div>
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
					document.form1.submit();
				}
			});
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
	<script>
		jQuery(document).ready(function($) {

			$('#EXCLUDED_PROGRAMS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= EXCLUDED_PROGRAM ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= EXCLUDED_PROGRAM ?> selected'
			});

			$('#EXCLUDED_STUDENT_STATUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= EXCLUDED_STUDENT_STATUS ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= EXCLUDED_STUDENT_STATUS ?> selected'
			});

			$('#GRADUATED_STUDENT_STATUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= GRADUATED_STUDENT_STATUS_LABEL ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= GRADUATED_STUDENT_STATUS_LABEL ?> selected'
			});

			$('#WITHDRWAL_DROP_STUDENT_STATUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= WITHDRWAL_DROP_STUDENT_STATUS_LABEL ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= WITHDRWAL_DROP_STUDENT_STATUS_LABEL ?> selected'
			});



			$('#ANNUAL_COST_OF_ATTENDANCE').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= ANNUAL_COST_OF_ATTENDANCE ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= ANNUAL_COST_OF_ATTENDANCE ?> selected'
			});

			$('#TUITION_FEES_RELATED_EXPENSES').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= TUITION_FEES_RELATED_EXPENSES ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= TUITION_FEES_RELATED_EXPENSES ?> selected'
			});

			$('#BOOK_ALLOWANCE').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= BOOK_ALLOWANCE ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= BOOK_ALLOWANCE ?> selected'
			});




			$('#HOUSING_AND_FOOD_ALLOWANCES').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= HOUSING_AND_FOOD_ALLOWANCES ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= HOUSING_AND_FOOD_ALLOWANCES ?> selected'
			});


			$('#INSTITUTIONAL_GRANT').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= INSTITUTIONAL_GRANT ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= INSTITUTIONAL_GRANT ?> selected'
			});

			$('#OTHER_GRANT').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= OTHER_GRANT ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= OTHER_GRANT ?> selected'
			});
			$('#PRIVATE_LOAN').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= PRIVATE_LOAN ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= PRIVATE_LOAN ?> selected'
			});

			$('#LICENSURE_TYPE').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= STUDENT_EVENT_TYPE ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= STUDENT_EVENT_TYPE ?> selected'
			});


			$('#TOOK_EXAM').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= STUDENT_EVENT_STATUS ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= STUDENT_EVENT_STATUS ?> selected'
			});


			$('#PASSED_EXAM').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= STUDENT_EVENT_STATUS ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= STUDENT_EVENT_STATUS ?> selected'
			});
		});
	</script>
</body>

</html>