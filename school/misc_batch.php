<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/misc_batch.php");
require_once("function_student_ledger.php");
require_once("function_update_disbursement_status.php");
require_once("function_unpost_batch_history.php");

require_once("check_access.php");

if (check_access('MANAGEMENT_ACCOUNTING') == 0) {
	header("location:../index");
	exit;
}

if (!empty($_POST)) {
	//echo "<pre>";print_r($_POST);exit;
	// unpost batches
	if ($_POST['STS_HID'] == 3) {
		$PK_MISC_BATCH_MASTER = $_GET['id'];
		$MISC_BATCH_MASTER['POSTED_DATE'] 		= '';
		$MISC_BATCH_MASTER['PK_BATCH_STATUS'] 	= $_POST['STS_HID'];
		$MISC_BATCH_MASTER['EDITED_BY'] 		= $_SESSION['PK_USER'];
		$MISC_BATCH_MASTER['EDITED_ON'] 		= date("Y-m-d H:i");
		db_perform('S_MISC_BATCH_MASTER', $MISC_BATCH_MASTER, 'update', " PK_MISC_BATCH_MASTER = '$PK_MISC_BATCH_MASTER' AND PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' ");

		$UNPOSTED_HISTORY['PK_MISC_BATCH_MASTER']  	= $PK_MISC_BATCH_MASTER;
		$UNPOSTED_HISTORY['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
		$UNPOSTED_HISTORY['UNPOSTED_BY'] 			= $_SESSION['PK_USER'];
		$UNPOSTED_HISTORY['UNPOSTED_ON'] 			= date("Y-m-d H:i");
		db_perform('S_MISC_BATCH_UNPOSTED_HISTORY', $UNPOSTED_HISTORY, 'insert');

		$res_det = $db->Execute("SELECT PK_MISC_BATCH_DETAIL FROM S_MISC_BATCH_DETAIL WHERE PK_MISC_BATCH_MASTER = '$PK_MISC_BATCH_MASTER' AND PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' ");
		while (!$res_det->EOF) {
			$PK_MISC_BATCH_DETAIL = $res_det->fields['PK_MISC_BATCH_DETAIL'];

			$MISC_BATCH_DETAIL['MISC_RECEIPT_NO'] = '';
			db_perform('S_MISC_BATCH_DETAIL', $MISC_BATCH_DETAIL, 'update', " PK_MISC_BATCH_DETAIL = '$PK_MISC_BATCH_DETAIL' ");

			$ledger_data_del['PK_MISC_BATCH_DETAIL'] = $PK_MISC_BATCH_DETAIL;
			delete_student_ledger($ledger_data_del);

			$res_det->MoveNext();
		}

		header("location:misc_batch?id=" . $PK_MISC_BATCH_MASTER);
		exit;
	}

	/* Ticket # 1913   */
	if ($_GET['id'] != '') {
		$res = $db->Execute("SELECT PK_BATCH_STATUS FROM S_MISC_BATCH_MASTER WHERE PK_MISC_BATCH_MASTER = '$_GET[id]' AND PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' ");
		$PK_MISC_BATCH_MASTER = $_GET['id'];
		$OLD_PK_BATCH_STATUS = $res->fields['PK_BATCH_STATUS'];
		if ($res->fields['PK_BATCH_STATUS'] == 2) {

			$i = 0;
			foreach ($_POST['PK_MISC_BATCH_DETAIL'] as $PK_MISC_BATCH_DETAIL) {
				$MISC_BATCH_DETAIL['BATCH_DETAIL_DESCRIPTION'] 	= $_POST['BATCH_DETAIL_DESCRIPTION'][$i];
				$MISC_BATCH_DETAIL['EDITED_BY']  				= $_SESSION['PK_USER'];
				$MISC_BATCH_DETAIL['EDITED_ON']  				= date("Y-m-d H:i");
				db_perform('S_MISC_BATCH_DETAIL', $MISC_BATCH_DETAIL, 'update', " PK_MISC_BATCH_DETAIL = '$PK_MISC_BATCH_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

				$i++;
			}

			header("location:misc_batch?id=" . $PK_MISC_BATCH_MASTER);
			exit;
		}
	}
	/* Ticket # 1913   */

	$MISC_BATCH_MASTER['MISC_BATCH_PK_CAMPUS'] 	= implode(",", $_POST['MISC_BATCH_PK_CAMPUS']);
	$MISC_BATCH_MASTER['BATCH_DATE']  		= $_POST['BATCH_DATE'];
	$MISC_BATCH_MASTER['DESCRIPTION'] 		= $_POST['DESCRIPTION'];
	$MISC_BATCH_MASTER['COMMENTS'] 			= $_POST['COMMENTS'];
	$MISC_BATCH_MASTER['CREDIT'] 			= $_POST['CREDIT'];
	$MISC_BATCH_MASTER['DEBIT'] 			= $_POST['DEBIT'];
	$MISC_BATCH_MASTER['STUDENT_TYPE']  	= $_POST['STUDENT_TYPE'];

	if ($_POST['STS_HID'] == 2)
		$MISC_BATCH_MASTER['POSTED_DATE'] =  date("Y-m-d");

	if ($_POST['STS_HID'] > 0)
		$MISC_BATCH_MASTER['PK_BATCH_STATUS'] = $_POST['STS_HID'];

	if ($MISC_BATCH_MASTER['BATCH_DATE'] != '')
		$MISC_BATCH_MASTER['BATCH_DATE'] = date("Y-m-d", strtotime($MISC_BATCH_MASTER['BATCH_DATE']));

	if ($_GET['id'] == '') {
		$res_acc = $db->Execute("SELECT MISC_BATCH_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

		$MISC_BATCH_MASTER['BATCH_NO'] 			= 'M' . $res_acc->fields['MISC_BATCH_NO'];
		$MISC_BATCH_MASTER['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
		$MISC_BATCH_MASTER['CREATED_BY']  		= $_SESSION['PK_USER'];
		$MISC_BATCH_MASTER['CREATED_ON']  		= date("Y-m-d H:i");
		db_perform('S_MISC_BATCH_MASTER', $MISC_BATCH_MASTER, 'insert');
		$PK_MISC_BATCH_MASTER = $db->insert_ID();

		$NEW_BATCH_NO = $res_acc->fields['MISC_BATCH_NO'] + 1;
		$db->Execute("UPDATE Z_ACCOUNT SET MISC_BATCH_NO = '$NEW_BATCH_NO' WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	} else {
		$PK_MISC_BATCH_MASTER = $_GET['id'];

		$MISC_BATCH_MASTER['EDITED_BY'] = $_SESSION['PK_USER'];
		$MISC_BATCH_MASTER['EDITED_ON'] = date("Y-m-d H:i");
		// DIAM-993
		if ($OLD_PK_BATCH_STATUS == 3) {
			misc_unpost_batch_history($PK_MISC_BATCH_MASTER, $MISC_BATCH_MASTER);
		}

		db_perform('S_MISC_BATCH_MASTER', $MISC_BATCH_MASTER, 'update', " PK_MISC_BATCH_MASTER = '$PK_MISC_BATCH_MASTER' AND PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' ");
	}

	$res = $db->Execute("SELECT PK_BATCH_STATUS FROM S_MISC_BATCH_MASTER WHERE PK_MISC_BATCH_MASTER = '$PK_MISC_BATCH_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$MISC_BATCH_MASTER['PK_BATCH_STATUS'] = $res->fields['PK_BATCH_STATUS'];

	$i = 0;
	foreach ($_POST['PK_MISC_BATCH_DETAIL'] as $PK_MISC_BATCH_DETAIL) {

		$hid = $_POST['student_count'][$i];
		$MISC_BATCH_DETAIL = array();

		$MISC_BATCH_DETAIL['PK_STUDENT_ENROLLMENT'] 	= $_POST['BATCH_PK_STUDENT_ENROLLMENT'][$i];
		$MISC_BATCH_DETAIL['PK_TERM_BLOCK'] 			= $_POST['BATCH_PK_TERM_BLOCK'][$i];
		$MISC_BATCH_DETAIL['PK_AR_LEDGER_CODE']  		= $_POST['BATCH_PK_AR_LEDGER_CODE'][$i];
		$MISC_BATCH_DETAIL['TRANSACTION_DATE']  		= $_POST['BATCH_TRANSACTION_DATE'][$i];
		$MISC_BATCH_DETAIL['DEBIT']  					= $_POST['BATCH_DEBIT'][$i];
		$MISC_BATCH_DETAIL['CREDIT']  					= $_POST['BATCH_CREDIT'][$i];
		$MISC_BATCH_DETAIL['AY']  						= $_POST['BATCH_AY'][$i];
		$MISC_BATCH_DETAIL['AP']  						= $_POST['BATCH_AP'][$i];
		$MISC_BATCH_DETAIL['BATCH_DETAIL_DESCRIPTION'] 	= $_POST['BATCH_DETAIL_DESCRIPTION'][$i];
		$MISC_BATCH_DETAIL['PRIOR_YEAR'] 				= $_POST['PRIOR_YEAR'][$i]; //Ticket # 1047

		$MISC_BATCH_DETAIL['PK_AR_FEE_TYPE'] 			= $_POST['PK_AR_FEE_TYPE_' . $hid];
		$MISC_BATCH_DETAIL['PK_AR_PAYMENT_TYPE'] 		= $_POST['PK_AR_PAYMENT_TYPE_' . $hid];

		if ($MISC_BATCH_DETAIL['TRANSACTION_DATE'] != '')
			$MISC_BATCH_DETAIL['TRANSACTION_DATE'] = date("Y-m-d", strtotime($MISC_BATCH_DETAIL['TRANSACTION_DATE']));

		$res_stu = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$MISC_BATCH_DETAIL[PK_STUDENT_ENROLLMENT]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$MISC_BATCH_DETAIL['PK_STUDENT_MASTER'] = $res_stu->fields['PK_STUDENT_MASTER'];

		if ($PK_MISC_BATCH_DETAIL == '') {
			$MISC_BATCH_DETAIL['PK_MISC_BATCH_MASTER']  = $PK_MISC_BATCH_MASTER;
			$MISC_BATCH_DETAIL['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
			$MISC_BATCH_DETAIL['CREATED_BY']  			= $_SESSION['PK_USER'];
			$MISC_BATCH_DETAIL['CREATED_ON']  			= date("Y-m-d H:i");
			db_perform('S_MISC_BATCH_DETAIL', $MISC_BATCH_DETAIL, 'insert');
			$PK_MISC_BATCH_DETAIL = $db->insert_ID();

			$PK_MISC_BATCH_DETAIL_ARR[] = $PK_MISC_BATCH_DETAIL;
		} else {
			$MISC_BATCH_DETAIL['EDITED_BY']  = $_SESSION['PK_USER'];
			$MISC_BATCH_DETAIL['EDITED_ON']  = date("Y-m-d H:i");
			// DIAM-993
			if ($OLD_PK_BATCH_STATUS == 3) {
				misc_unpost_batch_history($PK_MISC_BATCH_MASTER, $MISC_BATCH_DETAIL, $PK_MISC_BATCH_DETAIL);
			}

			db_perform('S_MISC_BATCH_DETAIL', $MISC_BATCH_DETAIL, 'update', " PK_MISC_BATCH_DETAIL = '$PK_MISC_BATCH_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$PK_MISC_BATCH_DETAIL_ARR[] = $PK_MISC_BATCH_DETAIL;
		}



		if ($MISC_BATCH_MASTER['PK_BATCH_STATUS'] == 2) {

			if ($MISC_BATCH_DETAIL['CREDIT'] > 0) {
				$res_bat = $db->Execute("SELECT RECEIPT_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				$RECEIPT_NO = $res_bat->fields['RECEIPT_NO'];

				$RECEIPT_NO1 = $RECEIPT_NO + 1;
				$db->Execute("UPDATE Z_ACCOUNT SET RECEIPT_NO = '$RECEIPT_NO1' WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				$S_MISC_BATCH_DETAIL1['MISC_RECEIPT_NO'] = $RECEIPT_NO;
				db_perform('S_MISC_BATCH_DETAIL', $S_MISC_BATCH_DETAIL1, 'update', " PK_MISC_BATCH_DETAIL = '$PK_MISC_BATCH_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			}

			$ledger_data['PK_MISC_BATCH_DETAIL'] 	= $PK_MISC_BATCH_DETAIL;
			$ledger_data['PK_AR_LEDGER_CODE'] 		= $MISC_BATCH_DETAIL['PK_AR_LEDGER_CODE'];
			$ledger_data['CREDIT_AMOUNT'] 			= $MISC_BATCH_DETAIL['CREDIT'];
			$ledger_data['DEBIT_AMOUNT'] 			= $MISC_BATCH_DETAIL['DEBIT'];
			$ledger_data['DATE'] 					= $MISC_BATCH_DETAIL['TRANSACTION_DATE'];
			$ledger_data['PK_STUDENT_ENROLLMENT'] 	= $MISC_BATCH_DETAIL['PK_STUDENT_ENROLLMENT'];
			$ledger_data['PK_STUDENT_MASTER'] 		= $MISC_BATCH_DETAIL['PK_STUDENT_MASTER'];
			student_ledger($ledger_data);
		}

		$i++;
	}

	//echo $_POST['STS_HID'];exit;
	//if($_POST['STS_HID'] == 1 || $_POST['STS_HID'] == 2) {

	$cond = " ";
	if (!empty($PK_MISC_BATCH_DETAIL_ARR))
		$cond = " AND PK_MISC_BATCH_DETAIL NOT IN (" . implode(",", $PK_MISC_BATCH_DETAIL_ARR) . ") ";

	$res_det = $db->Execute("SELECT PK_MISC_BATCH_DETAIL FROM S_MISC_BATCH_DETAIL WHERE PK_MISC_BATCH_MASTER = '$PK_MISC_BATCH_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ");
	while (!$res_det->EOF) {
		$PK_MISC_BATCH_DETAIL = $res_det->fields['PK_MISC_BATCH_DETAIL'];
		$db->Execute("DELETE FROM S_MISC_BATCH_DETAIL WHERE PK_MISC_BATCH_DETAIL = '$PK_MISC_BATCH_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

		$ledger_data_del['PK_MISC_BATCH_DETAIL'] = $PK_MISC_BATCH_DETAIL;
		delete_student_ledger($ledger_data_del);

		$res_det->MoveNext();
	}
	//}
	//exit;
	if ($_POST['STS_HID'] == 1)
		header("location:misc_batch?id=" . $PK_MISC_BATCH_MASTER);
	else
		header("location:manage_misc_batch");
}
$credit_card_flag = 0; //DIAM-987
if ($_GET['id'] == '') {
	$BATCH_NO 				= '';
	$BATCH_DATE	 			= date("m/d/Y");
	$COMMENTS				= '';
	$CREDIT	 				= 0;
	$DEBIT					= 0;
	$PK_BATCH_STATUS		= '';
	$STUDENT_TYPE			= 1;
	$DESCRIPTION			= '';
	$POSTED_DATE			= '';
	$MISC_BATCH_PK_CAMPUS_ARR 	= array();
	$res_acc = $db->Execute("SELECT MISC_BATCH_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$BATCH_NO = 'M' . $res_acc->fields['MISC_BATCH_NO'];

	/* Ticket #849  */
	$res_camp = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if ($res_camp->RecordCount() == 1) {
		$MISC_BATCH_PK_CAMPUS1 		= $res_camp->fields['PK_CAMPUS'];
		$MISC_BATCH_PK_CAMPUS_ARR	= explode(",", $MISC_BATCH_PK_CAMPUS1);
	}
	/* Ticket #849  */
} else {
	$res = $db->Execute("SELECT * FROM S_MISC_BATCH_MASTER WHERE PK_MISC_BATCH_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if ($res->RecordCount() == 0) {
		header("location:manage_misc_batch");
		exit;
	}

	//DIAM-987
	$detect_card_sql = "SELECT PK_STUDENT_CREDIT_CARD_PAYMENT FROM S_MISC_BATCH_DETAIL WHERE PK_MISC_BATCH_MASTER = $_GET[id] AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CREDIT_CARD_PAYMENT!=0 LIMIT 1";
	$card_res = $db->Execute($detect_card_sql);
	if ($card_res->RecordCount() > 0) {
		$credit_card_flag = 1;
	}


	$BATCH_NO 				= $res->fields['BATCH_NO'];
	$BATCH_DATE  			= $res->fields['BATCH_DATE'];
	$POSTED_DATE			= $res->fields['POSTED_DATE'];
	$COMMENTS  				= $res->fields['COMMENTS'];
	$CREDIT  				= $res->fields['CREDIT'];
	$DEBIT					= $res->fields['DEBIT'];
	$PK_BATCH_STATUS		= $res->fields['PK_BATCH_STATUS'];
	$STUDENT_TYPE			= $res->fields['STUDENT_TYPE'];
	$DESCRIPTION			= $res->fields['DESCRIPTION'];
	$MISC_BATCH_PK_CAMPUS1		= $res->fields['MISC_BATCH_PK_CAMPUS'];
	$MISC_BATCH_PK_CAMPUS_ARR	= explode(",", $res->fields['MISC_BATCH_PK_CAMPUS']);

	if ($BATCH_DATE == '0000-00-00')
		$BATCH_DATE = '';
	else
		$BATCH_DATE = date("m/d/Y", strtotime($BATCH_DATE));

	if ($POSTED_DATE == '0000-00-00')
		$POSTED_DATE = '';
	else
		$POSTED_DATE = date("m/d/Y", strtotime($POSTED_DATE));
}
$res = $db->Execute("SELECT BATCH_STATUS FROM M_BATCH_STATUS WHERE PK_BATCH_STATUS = '$PK_BATCH_STATUS' ");
$BATCH_STATUS = $res->fields['BATCH_STATUS'];
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
	<title><?= MISC_BATCH_PAGE_TITLE ?> | <?= $title ?></title>
	<style>
		.no-records-found {
			display: none;
		}

		li>a>label {
			position: unset !important;
		}

		input::-webkit-outer-spin-button,
		input::-webkit-inner-spin-button {
			-webkit-appearance: none;
			margin: 0;
		}

		/* Firefox */
		input[type=number] {
			-moz-appearance: textfield;
		}

		/* Ticket # 1149 - term */
		.dropdown-menu>li>a {
			white-space: nowrap;
		}

		.option_red>a>label {
			color: red !important
		}

		/* Ticket # 1149 - term */

		.table th,
		.table td {
			padding: 0.5rem;
		}

		<? /* Ticket #2005 */
		for ($i = 0; $i <= 500; $i++) { ?>#advice-required-entry-BATCH_PK_STUDENT_MASTER_<?= $i ?> {
			position: relative;
			top: 42px;
			width: 142px
		}

		#advice-required-entry-BATCH_PK_AR_LEDGER_CODE_<?= $i ?> {
			position: relative;
			top: 42px;
			width: 142px
		}

		<? } /* Ticket #2005 */ ?>
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
						<h4 class="text-themecolor"><? if ($_GET['id'] == '') echo ADD;
													else echo EDIT; ?> <?= MISC_BATCH_PAGE_TITLE ?> </h4>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form class="floating-labels m-t-40" method="post" name="form1" id="form1">
									<div class="row">
										<div class="col-md-4">
											<div class="row">
												<div class="col-md-6 focused">
													<input type="text" class="form-control" value="<?= $BATCH_NO ?>" readonly>
													<span class="bar"></span>
													<label><?= BATCH_NO ?></label>
												</div>


												<div class="col-md-6 focused">
													<input type="text" class="form-control" value="<?= $BATCH_STATUS ?>" readonly>
													<span class="bar"></span>
													<label><?= STATUS ?></label>
												</div>
											</div>

											<br />
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="MISC_BATCH_PK_CAMPUS" name="MISC_BATCH_PK_CAMPUS[]" multiple class="form-control required-entry">
															<? /* Ticket #1612 */
															$res_type = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by CAMPUS_CODE ASC ");
															while (!$res_type->EOF) {
																$selected = "";
																foreach ($MISC_BATCH_PK_CAMPUS_ARR as $PK_CAMPUS) {
																	if ($res_type->fields['PK_CAMPUS'] == $PK_CAMPUS) {
																		$selected = "selected";
																		break;
																	}
																}
															?>
																<option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <?= $selected ?>><?= $res_type->fields['CAMPUS_CODE'] ?></option>
															<? $res_type->MoveNext();
															} /* Ticket #1612 */ ?>
														</select>
													</div>
												</div>
												<?php if (check_access('MANAGEMENT_UNPOST_BATCHES') == 1 && $PK_BATCH_STATUS == 2 && check_global_access() == 1 && $credit_card_flag == 0) {  ?>
													<div class="col-md-6">
														<button type="button" onclick="save_form(3)" id="UNPOST_BTN" class="btn waves-effect waves-light btn-info"><?= UNPOSTBATCH ?></button>
														<span class="bar"></span>
													</div>
												<?php } ?>


											</div>

											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40 ">
														<input type="text" class="form-control date required-entry" id="BATCH_DATE" name="BATCH_DATE" value="<?= $BATCH_DATE ?>" onchange="set_trans_date()"> <!-- Ticket # 1883 -->
														<span class="bar"></span>
														<label for="BATCH_DATE"><?= BATCH_DATE ?></label>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group m-b-40 ">
														<input type="text" class="form-control " id="POSTED_DATE" value="<?= $POSTED_DATE ?>" readonly>
														<span class="bar"></span>
														<label for="POSTED_DATE"><?= POSTED_DATE ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="DESCRIPTION" name="DESCRIPTION" value="<?= $DESCRIPTION ?>" onchange="set_desc()">
														<span class="bar"></span>
														<label for="DESCRIPTION"><?= DESCRIPTION ?></label>
													</div>
												</div>
											</div>
										</div>

										<div class="col-md-1" style="flex: 0 0 5.33333%;max-width: 5.33333%;"></div>
										<div class="col-md-2" style="flex: 0 0 21%;max-width: 21%;">
											<input type="hidden" id="CREDIT_0" name="CREDIT" value="<?= $CREDIT ?>" onblur="format_val('CREDIT',0)">
											<input type="hidden" id="DEBIT_0" name="DEBIT" value="<?= $DEBIT ?>" onblur="format_val('DEBIT',0)">

											<div class="row">
												<div class="col-md-6">
													<?= DEBIT_TOTAL ?>
												</div>
												<div class="col-md-6" id="DEBIT_TOTAL" style="text-align:right;">
												</div>
											</div>

											<div class="row">
												<div class="col-md-6">
													<?= CREDIT_TOTAL ?>
												</div>
												<div class="col-md-6" id="CREDIT_TOTAL" style="text-align:right;">
												</div>
											</div>

											<div class="row">
												<div class="col-md-6" style="border-top:1px solid #000;font-weight:bold">
													<?= BATCH_TOTAL ?>
												</div>
												<div class="col-md-6" id="BATCH_TOTAL" style="border-top:1px solid #000;text-align:right;font-weight:bold">
												</div>
											</div>
										</div>
										<div class="col-md-1" style="flex: 0 0 5.33333%;max-width: 5.33333%;"></div>

										<div class="col-md-4">
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-20">
														<textarea class="form-control" rows="11" name="COMMENTS" id="COMMENTS"><?= $COMMENTS ?></textarea>
														<span class="bar"></span>
														<label for="COMMENTS"><?= COMMENTS ?></label>
													</div>
												</div>
											</div>

											<!-- DIAM-1423 -->
											<? if ($PK_BATCH_STATUS == 2) { ?>
												<div class="row">
													<div class="col-md-12">
														<div class="form-group m-b-30" style="text-align: right;">
															<button type="button" onclick="create_student_notes()" id="CREATE_STUDENT_NOTES" class="btn waves-effect waves-light btn-info">Create Student Notes</button>
														</div>
													</div>
												</div>
											<? } ?>
											<!-- End DIAM-1423 -->

										</div>
									</div>


									<div class="row">
										<div class="col-md-4">
											<div class="row">
												<div class="col-md-12">
													<div class="row form-group">
														<div class="custom-control custom-radio col-md-3">
															<input type="radio" id="STUDENT_TYPE_1" name="STUDENT_TYPE" value="1" <? if ($STUDENT_TYPE == 1) echo "checked"; ?> class="custom-control-input" onchange="clear_data()">
															<label class="custom-control-label" for="STUDENT_TYPE_1"><?= STUDENT ?></label>
														</div>
														<div class="custom-control custom-radio col-md-3">
															<input type="radio" id="STUDENT_TYPE_2" name="STUDENT_TYPE" value="2" <? if ($STUDENT_TYPE == 2) echo "checked"; ?> class="custom-control-input" onchange="clear_data()">
															<label class="custom-control-label" for="STUDENT_TYPE_2"><?= LEAD ?></label>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<!-- Ticket # 1612 
									<div class="row form-group">
										<div class="col-md-2">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="SRC_SEARCH" value="" >
												<span class="bar"></span>
												<label for="SRC_SEARCH"><?= STUDENT ?></label>
											</div>
										</div>
								
										<div class="col-md-3">
											<select id="SRC_TERM_MASTER" name="SRC_TERM_MASTER[]" multiple class="form-control" >
												<? /* Ticket #1149 - term */
												/*$res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
												while (!$res_type->EOF) { 
													$str = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'].' - '.$res_type->fields['TERM_DESCRIPTION'];
													if($res_type->fields['ACTIVE'] == 0)
														$str .= ' (Inactive)'; ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>  ><?=$str ?></option>
												<?	$res_type->MoveNext();
												} */ /* Ticket #1149 - term */ ?>
											</select>
										</div>
										
										<div class="col-md-3">
											<select id="SRC_CAMPUS_PROGRAM" name="SRC_CAMPUS_PROGRAM[]" multiple class="form-control"  >
												<? /* $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) {  
													$selected = "";
													if(!empty($_REQUEST['SRC_CAMPUS_PROGRAM'])){
														foreach($_REQUEST['SRC_CAMPUS_PROGRAM'] as $SRC_PK_CAMPUS_PROGRAM1){
															if($res_type->fields['PK_CAMPUS_PROGRAM'] == $SRC_PK_CAMPUS_PROGRAM1)
																$selected = "selected";
														}
													} ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" <?=$selected?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} */ ?>
											</select>
										</div>
										
										<div class="col-md-3">
											<select id="SRC_STUDENT_STATUS" name="SRC_STUDENT_STATUS[]" multiple class="form-control" >
												<? /* $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { 
													$selected = "";
													if(!empty($_REQUEST['SRC_STUDENT_STATUS'])){
														foreach($_REQUEST['SRC_STUDENT_STATUS'] as $PK_STUDENT_STATUS1){
															if($res_type->fields['PK_STUDENT_STATUS'] == $PK_STUDENT_STATUS1)
																$selected = "selected";
														}
													} ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" <?=$selected?> ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} */ ?>
											</select>
										</div>
										
										<!--<div class="col-md-1">
											<button type="button" onclick="get_students('','')" class="btn waves-effect waves-light btn-info"><?= SEARCH ?></button>
										</div>-->

									<!-- </div> Ticket # 1612 -->

									<div class="table-responsive p-20">
										<table class="table-striped table table-hover table-bordered" id="student_table">
											<thead>
												<!-- Ticket # 1612 -->
												<tr>
													<th><?= OPTIONS ?></th>
													<th><?= STUDENT ?></th>
													<th><?= SSN ?></th>
													<th><?= LEDGER_CODE ?></th>
													<th><?= TRANS_DATE ?></th>
													<th><?= DEBIT ?></th>
													<th><?= CREDIT ?></th>
													<th><?= BATCH_DESCRIPTION ?></th>
													<th><?= FEE_PAYMENT_TYPE ?></th>

													<th><?= AY_1 ?></th>
													<th><?= AP_1 ?></th>
													<th><?= RECEIPT_NO ?></th>
													<th><?= ENROLLMENT ?></th>
													<th><?= TERM_BLOCK ?></th>

													<th><?= PRIOR_YEAR ?></th> <!-- Ticket # 1047 -->

												</tr>
												<!-- Ticket # 1612 -->
											</thead>
											<tbody>
												<? $student_count = 1;
												$res = $db->Execute("select PK_MISC_BATCH_DETAIL from S_MISC_BATCH_DETAIL LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_MISC_BATCH_DETAIL.PK_STUDENT_MASTER LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_MISC_BATCH_DETAIL.PK_AR_LEDGER_CODE WHERE PK_MISC_BATCH_MASTER = '$_GET[id]' AND S_MISC_BATCH_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC, TRANSACTION_DATE ASC, CODE ASC ");
												while (!$res->EOF) {
													$_REQUEST['student_count'] 			= $student_count;
													$_REQUEST['PK_MISC_BATCH_DETAIL'] 	= $res->fields['PK_MISC_BATCH_DETAIL'];
													$_REQUEST['student_type'] 			= $STUDENT_TYPE;
													$_REQUEST['PK_BATCH_STATUS'] 		= $PK_BATCH_STATUS;
													//$_REQUEST['campus_id'] 				= $MISC_BATCH_PK_CAMPUS1;

													include("ajax_misc_batch_detail.php");
													$student_count++;

													$res->MoveNext();
												} ?>
											</tbody>
											<tfoot>
												<tr>
													<th></th>
													<th></th>
													<th></th>
													<th></th>
													<th><?= TOTAL ?></th>
													<th>
														<div id="debit_total_div" style="text-align:right;">
													</th>
													<th>
														<div id="credit_total_div" style="text-align:right;">
													</th>
													<th></th>
													<th></th>
													<th></th>
													<th></th>
													<th></th>
													<th></th>
													<th></th>
													<th></th>
												</tr>
											</tfoot>
										</table>
									</div>
									<br />

									<div class="row">
										<div class="col-md-12">
											<div style="text-align:center">

												<? if ($PK_BATCH_STATUS == 1 || $PK_BATCH_STATUS == '' || $PK_BATCH_STATUS == 3) { ?>
													<button type="button" onclick="add_student()" class="btn waves-effect waves-light btn-info"><?= ADD_STUDENT ?></button>
													<button type="button" onclick="save_form(1)" class="btn waves-effect waves-light btn-info"><?= SAVE_AS_HOLD ?></button>

													<button type="button" onclick="save_form(2)" class="btn waves-effect waves-light btn-info"><?= POST_TO_LEDGER ?></button>
												<? } /* else if($PK_BATCH_STATUS == 2) { ?>
													<button type="button" onclick="save_form(3)" id="UNPOST_BTN" class="btn waves-effect waves-light btn-info"><?=UNPOST?></button>
												<? } */ ?>

												<? /* Ticket # 1913  */
												if ($PK_BATCH_STATUS == 2) { ?>
													<button type="button" onclick="save_form(4)" id="EDIT_BATCH_DESCRIPTION_SAVE_BTN" style="display:none" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>
													<button type="button" onclick="edit_batch_desc()" id="EDIT_BATCH_DESCRIPTION_BTN" class="btn waves-effect waves-light btn-info"><?= EDIT_BATCH_DESCRIPTION ?></button>
												<? } /* Ticket # 1913  */ ?>

												<? if ($_GET['id'] != '') { ?>
													<button id="DOWNLOAD_BTN" type="button" id="DOWNLOAD_BTN" class="btn waves-effect waves-light btn-info" onclick="window.location.href='misc_payment_pdf.php?id=<?= $_GET['id'] ?>'"><?= DOWNLOAD_REPORT ?></button>
												<? } ?>

												<button type="button" class="btn waves-effect waves-light btn-dark" id="CANCEL_BTN" onclick="window.location.href='manage_misc_batch'"><?= CANCEL ?></button>

												<input type="hidden" name="STS_HID" id="STS_HID" value='' />
												<input type="hidden" name="TOTAL_AMOUNT" id="TOTAL_AMOUNT" value='' />
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

		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?= DELETE_CONFIRMATION ?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<?= DELETE_MESSAGE_GENERAL ?>
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?= YES ?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)"><?= NO ?></button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal" id="unpostModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?= UNPOST_CONFIRMATION ?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							Are you sure you want to unpost this Batch?
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_unpost_message(1)" class="btn waves-effect waves-light btn-info"><?= YES ?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_unpost_message(0)"><?= NO ?></button>
					</div>
				</div>
			</div>
		</div>
		<!-- DIAM-920 -->
		<div class="modal" id="dulicateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?= WARNING ?></h4>
					</div>
					<div class="modal-body">
						<h5 class="modal-title" id="Mtitle"></h5>
						<div class="form-group" id="DUPLICATE_MSG_DIV"></div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="build_misc_batch()" class="btn waves-effect waves-light btn-info"><?= PROCEED ?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="close_popup_warning('dulicateModal')"><?= CANCEL ?></button>
					</div>
				</div>
			</div>
		</div>
		<!-- DIAM-920 -->
	</div>

	<? require_once("js.php"); ?>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			jQuery(".date").datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
			<? if ($_GET['id'] != '') { ?>
				calc_total(0)
			<? } ?>

			<? if ($PK_BATCH_STATUS == 2) { ?>
				disableForm(document.form1)
			<? } ?>

			$('#student_table').on('post-body.bs.table', function(e) {
				jQuery(".date").datepicker({
					todayHighlight: true,
					orientation: "bottom auto"
				});
			})

			search_student() //Ticket #1612

			$('.ledger_select2').select2(); //Ticket #2005
		});

		/** DIAM-1423 **/
		function create_student_notes() {
			var str = '';
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]');
			var unique = [];
			var distinct = [];
			for (var i = 0; i < PK_STUDENT_ENROLLMENT.length; i++) {

				if (!unique[PK_STUDENT_ENROLLMENT[i].value]) {
					distinct.push(PK_STUDENT_ENROLLMENT[i].value);
					unique[PK_STUDENT_ENROLLMENT[i].value] = 1;
				}

				// if(str != '')
				// {
				// 	str += ',';
				// }				
				// str += PK_STUDENT_ENROLLMENT[i].value;
			}
			// alert(distinct);
			if (distinct !== "") {
				jQuery(document).ready(function($) {

					var data = 'enrollment_ids=' + distinct;
					var value = $.ajax({
						url: "ajax_set_session_create_student_notes",
						type: "POST",
						data: data,
						async: true,
						cache: false,
						success: function(data) {
							//alert(data)
							if (data == 'success') {
								window.location.href = 'student_notes.php?p=m&batch=1';
							}

						}
					}).responseText;

				});

			}

		}
		/** End DIAM-1423 **/
	</script>

	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script> <!-- Ticket #1612 -->
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		//var form1 = new Validation('form1');
		//DIAM-920/748
		function check_dup_batch_student() {
			var PK_MISC_BATCH_DETAIL = document.getElementsByName('PK_MISC_BATCH_DETAIL[]');
			var json;
			if (PK_MISC_BATCH_DETAIL.length > 0) {
				jQuery(document).ready(function($) {
					var data = $("#form1").serialize(); //'student_count='+PK_MISC_BATCH_DETAIL.value;
					var value = $.ajax({
						url: "ajax_check_duplicate_student_misc_batch",
						type: "POST",
						data: data,
						async: false,
						cache: false,
						success: function(data) {
							var json = $.parseJSON(data);
							$("#DUPLICATE_MSG_DIV").html(json.EXIST_STUDENT_NAME);
							$("#Mtitle").text(json.Mtitle);
							//console.log(json.EXIST_STUDENT_COUNT);
							if (json.EXIST_STUDENT_COUNT > 0) {
								$("#dulicateModal").modal();
							} else {
								enableForm(document.form1)
								document.form1.submit();
							}
						}
					}).responseText;

				}) //document ready function.
			}

		}

		function close_popup_warning() {
			jQuery(document).ready(function($) {
				$("#dulicateModal").modal('hide');
			});

		}

		function build_misc_batch() {
			enableForm(document.form1)
			document.form1.submit();
			close_popup_warning();
		}
		//DIAM-920/748
		function save_form(val) {
			document.getElementById('STS_HID').value = val

			if (val == 3) {
				jQuery(document).ready(function($) {
					$("#unpostModal").modal()
				});
			} else {
				/*var BATCH_TRANSACTION_DATE = document.getElementsByName('BATCH_TRANSACTION_DATE[]')
				if(val == 2){
					for(var i = 0 ; i < BATCH_TRANSACTION_DATE.length ; i++){
						document.getElementById(BATCH_TRANSACTION_DATE[i].id).classList.add("required-entry");
					}
				} else {
					for(var i = 0 ; i < BATCH_TRANSACTION_DATE.length ; i++){
						document.getElementById(BATCH_TRANSACTION_DATE[i].id).classList.remove("required-entry");
					}
				}*/

				var valid = new Validation('form1', {
					onSubmit: false
				});
				var result = valid.validate();

				if (result == true) {
					calc_total(1)

					var DEBIT_TOTAL = document.getElementById('DEBIT_TOTAL').innerHTML
					var CREDIT_TOTAL = document.getElementById('CREDIT_TOTAL').innerHTML
					var DEBIT = document.getElementById('DEBIT_0').value
					var CREDIT = document.getElementById('CREDIT_0').value

					if (DEBIT_TOTAL == '')
						DEBIT_TOTAL = 0;
					else
						DEBIT_TOTAL = parseFloat(DEBIT_TOTAL)

					if (CREDIT_TOTAL == '')
						CREDIT_TOTAL = 0;
					else
						CREDIT_TOTAL = parseFloat(CREDIT_TOTAL)

					if (DEBIT == '')
						DEBIT = 0;
					else
						DEBIT = parseFloat(DEBIT)

					if (CREDIT == '')
						CREDIT = 0;
					else
						CREDIT = parseFloat(CREDIT)
					//alert(CREDIT_TOTAL+' --- '+CREDIT+'\n'+DEBIT_TOTAL+' --- '+DEBIT)					
					var error = '';
					/*if(CREDIT_TOTAL != CREDIT && DEBIT_TOTAL != DEBIT)
						error = '<?= TOTAL_AMOUNT_DOESNOT_MATCH ?>';
					else if(CREDIT_TOTAL != CREDIT )
						error = '<?= TOTAL_AMOUNT_DOESNOT_MATCH ?>';
					else if(DEBIT_TOTAL != DEBIT )
						error = '<?= TOTAL_AMOUNT_DOESNOT_MATCH ?>';*/

					if (error == '') {
						//  DIAM-920	
						check_dup_batch_student();
						//enableForm(document.form1)
						//document.form1.submit();	
						//  DIAM-920					
					} else {
						alert(error)
					}
				}
			}
		}

		var student_count = '<?= $student_count ?>';

		function add_student() {
			jQuery(document).ready(function($) {
				var MISC_BATCH_PK_CAMPUS = $("#MISC_BATCH_PK_CAMPUS").val()
				if (MISC_BATCH_PK_CAMPUS == '') {
					alert('Please Select Campus')
				} else {
					var student_type = '';

					if (document.getElementById('STUDENT_TYPE_1').checked == true)
						student_type = 1;
					else
						student_type = 2;

					//var data  = 'student_count='+student_count+'&student_type='+student_type+'&campus_id='+$("#MISC_BATCH_PK_CAMPUS").val()+'&SRC_TERM_MASTER='+$("#SRC_TERM_MASTER").val()+'&SRC_CAMPUS_PROGRAM='+$("#SRC_CAMPUS_PROGRAM").val()+'&SRC_STUDENT_STATUS='+$("#SRC_STUDENT_STATUS").val()+'&SRC_SEARCH='+$("#SRC_SEARCH").val() Ticket #1612

					/* Ticket # 1883 */
					var BATCH_DATE = '';
					if (document.getElementById('BATCH_DATE').value != '')
						BATCH_DATE = document.getElementById('BATCH_DATE').value;
					/* Ticket # 1883 */

					var data = 'student_count=' + student_count + '&student_type=' + student_type + '&campus_id=' + $("#MISC_BATCH_PK_CAMPUS").val() + '&def_date=' + BATCH_DATE //Ticket #1612 Ticket # 1883
					var value = $.ajax({
						url: "ajax_misc_batch_detail",
						type: "POST",
						data: data,
						async: false,
						cache: false,
						success: function(data) {
							//alert(data)

							$('#student_table tbody').append(data);
							document.getElementById('BATCH_DETAIL_DESCRIPTION_' + student_count).value = document.getElementById('DESCRIPTION').value
							document.getElementById('BATCH_AY_' + student_count).value = 1;
							document.getElementById('BATCH_AP_' + student_count).value = 1;

							search_student() //Ticket #1612
							$('.ledger_select2').select2(); //Ticket #2005

							student_count++;

							jQuery('.date').datepicker({
								todayHighlight: true,
								orientation: "bottom auto"
							});

							set_desc()
							calc_total(1)
						}
					}).responseText;
				}
			});
		}

		/* Ticket # 1883 */
		function set_trans_date() {
			var BATCH_DATE = '';
			if (document.getElementById('BATCH_DATE').value != '') {
				BATCH_DATE = document.getElementById('BATCH_DATE').value;

				var BATCH_TRANSACTION_DATE = document.getElementsByName('BATCH_TRANSACTION_DATE[]')
				for (var i = 0; i < BATCH_TRANSACTION_DATE.length; i++) {
					if (BATCH_TRANSACTION_DATE[i].value == '')
						BATCH_TRANSACTION_DATE[i].value = BATCH_DATE
				}

			}
		}
		/* Ticket # 1883 */

		function delete_row(id) {
			jQuery(document).ready(function($) {
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
			});
		}

		function conf_delete(val) {
			jQuery(document).ready(function($) {
				if (val == 1) {
					$("#misc_batch_detail_div_" + $("#DELETE_ID").val()).remove();
				}
				calc_total(1)
				$("#deleteModal").modal("hide");
			});
		}

		function format_val(field, id) {
			var AMOUNT = document.getElementById(field + '_' + id).value
			if (AMOUNT != '') {
				AMOUNT = parseFloat(AMOUNT)
				document.getElementById(field + '_' + id).value = AMOUNT.toFixed(2);
			}
		}

		function get_ssn(val, id) {
			jQuery(document).ready(function($) {
				var data = 'sid=' + val;
				var value = $.ajax({
					url: "ajax_student_ssn",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						document.getElementById('SSN_DIV_' + id).innerHTML = data
					}
				}).responseText;
			});
		}

		function get_term(val, id) {
			jQuery(document).ready(function($) {
				var data = 'eid=' + val;
				var value = $.ajax({
					url: "ajax_student_term",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						document.getElementById('BATCH_PK_TERM_BLOCK_' + id).value = data
					}
				}).responseText;
			});
		}

		/* Ticket #1612 */
		function get_enrollment_det(val, id) {
			jQuery(document).ready(function($) {
				var data = 'stud_id=' + val + '&count1=' + id;
				var value = $.ajax({
					url: "ajax_get_misc_batch_student_enrollment",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						document.getElementById('ENROLLMEN_DIV_' + id).innerHTML = data

						get_term(document.getElementById('BATCH_PK_STUDENT_ENROLLMENT_' + id).value, id)
					}
				}).responseText;
			});
		}
		/* Ticket #1612 */

		function calc_total(update_amt) {
			var BATCH_DEBIT = document.getElementsByName('BATCH_DEBIT[]')
			var total = 0;
			for (var i = 0; i < BATCH_DEBIT.length; i++) {
				if (BATCH_DEBIT[i].value != '')
					total = parseFloat(total) + parseFloat(BATCH_DEBIT[i].value)
			}
			total = parseFloat(total);
			var DEBIT_TOTAL = total
			document.getElementById('DEBIT_TOTAL').innerHTML = '$ ' + total.toFixed(2)
			document.getElementById('debit_total_div').innerHTML = '$ ' + total.toFixed(2)

			var BATCH_CREDIT = document.getElementsByName('BATCH_CREDIT[]')
			var total = 0;
			for (var i = 0; i < BATCH_CREDIT.length; i++) {
				if (BATCH_CREDIT[i].value != '')
					total = parseFloat(total) + parseFloat(BATCH_CREDIT[i].value)
			}
			total = parseFloat(total);
			var CREDIT_TOTAL = total
			document.getElementById('CREDIT_TOTAL').innerHTML = '$ ' + total.toFixed(2)
			document.getElementById('credit_total_div').innerHTML = '$ ' + total.toFixed(2)

			var BATCH_TOTAL = parseFloat(DEBIT_TOTAL) - parseFloat(CREDIT_TOTAL)
			document.getElementById('BATCH_TOTAL').innerHTML = '$ ' + BATCH_TOTAL.toFixed(2)

			if (update_amt == 1) {
				document.getElementById('CREDIT_0').value = parseFloat(CREDIT_TOTAL).toFixed(2);
				document.getElementById('DEBIT_0').value = parseFloat(DEBIT_TOTAL).toFixed(2);
			}
		}

		function clear_data() {
			jQuery(document).ready(function($) {
				//$('#student_table tbody').empty()

				/*var student_count 		 = document.getElementsByName('student_count[]')
				var PK_MISC_BATCH_DETAIL = document.getElementsByName('PK_MISC_BATCH_DETAIL[]')
				for(var i = 0 ; i < PK_MISC_BATCH_DETAIL.length ; i++){
					if(PK_MISC_BATCH_DETAIL[i].value == '') {
						var id = student_count[i].value;
						$("#misc_batch_detail_div_"+id).remove()
					}
				}*/
			});
		}

		function get_ledger_type(val, id) {
			jQuery(document).ready(function($) {
				var data = 'val=' + val;
				var value = $.ajax({
					url: "ajax_get_ledger_type",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						if (data == 2) {
							document.getElementById('BATCH_DEBIT_' + id).disabled = false
							document.getElementById('BATCH_CREDIT_' + id).disabled = true
							document.getElementById('BATCH_CREDIT_' + id).value = ''; //Ticket # 1619

							document.getElementById('BATCH_CREDIT_' + id).className = 'form-control';
							document.getElementById('BATCH_DEBIT_' + id).className = 'form-control required-entry';

							if (document.getElementById('advice-required-entry-BATCH_CREDIT_' + id))
								document.getElementById('advice-required-entry-BATCH_CREDIT_' + id).style.display = 'none'
							else
								document.getElementById('advice-required-entry-BATCH_CREDIT_' + id).style.display = 'block'
						} else if (data == 1) {
							document.getElementById('BATCH_DEBIT_' + id).disabled = true
							document.getElementById('BATCH_CREDIT_' + id).disabled = false
							document.getElementById('BATCH_DEBIT_' + id).value = ''; //Ticket # 1619

							document.getElementById('BATCH_DEBIT_' + id).className = 'form-control';
							document.getElementById('BATCH_CREDIT_' + id).className = 'form-control required-entry';

							if (document.getElementById('advice-required-entry-BATCH_DEBIT_' + id))
								document.getElementById('advice-required-entry-BATCH_DEBIT_' + id).style.display = 'none'
							else
								document.getElementById('advice-required-entry-BATCH_DEBIT_' + id).style.display = 'block'
						}
						//document.getElementById('SSN_DIV_'+id).innerHTML = data
						calc_total(1) //Ticket # 1619
					}
				}).responseText;
			});
		}

		function get_fee_payment_type(val, id) {
			jQuery(document).ready(function($) {
				var data = 'ledger_id=' + val + '&count_1=' + id + '&DEF_pk_ar_fee_type=&DEF_pk_ar_payment_type=';
				var value = $.ajax({
					url: "ajax_misc_batch_fee_payment_type",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						document.getElementById('FEE_PAYMENT_TYPE_DIV_' + id).innerHTML = data
					}
				}).responseText;
			});
		}

		function conf_unpost_message(val) {
			jQuery(document).ready(function($) {
				if (val == 1) {
					enableForm(document.form1)
					document.form1.submit();
				}
				$("#unpostModal").modal("hide");
			});
		}

		function disableForm(theform) {
			if (document.all || document.getElementById) {
				for (i = 0; i < theform.length; i++) {
					var formElement = theform.elements[i];
					if (true) {
						formElement.disabled = true;
					}
				}
			}
			if (document.getElementById('UNPOST_BTN'))
				document.getElementById('UNPOST_BTN').disabled = false;

			document.getElementById('CANCEL_BTN').disabled = false;
			document.getElementById('DOWNLOAD_BTN').disabled = false;

			/* Ticket # 1913   */
			if (document.getElementById('EDIT_BATCH_DESCRIPTION_SAVE_BTN'))
				document.getElementById('EDIT_BATCH_DESCRIPTION_SAVE_BTN').disabled = false;

			if (document.getElementById('EDIT_BATCH_DESCRIPTION_BTN'))
				document.getElementById('EDIT_BATCH_DESCRIPTION_BTN').disabled = false;
			/* Ticket # 1913   */

			document.getElementById('CREATE_STUDENT_NOTES').disabled = false; // DIAM-1423
			document.querySelectorAll('.pk_stud_enrol').forEach(el => el.disabled = false); // DIAM-1423
		}

		/* Ticket # 1913   */
		function edit_batch_desc() {
			var BATCH_DETAIL_DESCRIPTION = document.getElementsByName('BATCH_DETAIL_DESCRIPTION[]')
			for (var i = 0; i < BATCH_DETAIL_DESCRIPTION.length; i++) {
				BATCH_DETAIL_DESCRIPTION[i].disabled = false;
			}

			document.getElementById('EDIT_BATCH_DESCRIPTION_SAVE_BTN').style.display = 'inline';
		}
		/* Ticket # 1913   */

		function enableForm(theform) {
			if (document.all || document.getElementById) {
				for (i = 0; i < theform.length; i++) {
					var formElement = theform.elements[i];
					if (true) {
						formElement.disabled = false;
					}
				}
			}
		}

		function set_desc() {
			var DESCRIPTION = document.getElementById('DESCRIPTION').value
			var BATCH_DETAIL_DESCRIPTION = document.getElementsByName('BATCH_DETAIL_DESCRIPTION[]')
			if (DESCRIPTION != '') {

				for (var i = 0; i < BATCH_DETAIL_DESCRIPTION.length; i++) {
					if (BATCH_DETAIL_DESCRIPTION[i].value == '') {
						BATCH_DETAIL_DESCRIPTION[i].value = DESCRIPTION;
					}
				}
			}
		}
	</script>

	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#MISC_BATCH_PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= CAMPUS ?>',
				nonSelectedText: '<?= CAMPUS ?>',
				numberDisplayed: 1,
				nSelectedText: '<?= CAMPUS ?> selected'
			});

			/* Ticket #1612 
			$('#SRC_CAMPUS_PROGRAM').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= PROGRAM ?>',
				nonSelectedText: '<?= PROGRAM ?>',
				numberDisplayed: 1,
				nSelectedText: '<?= PROGRAM ?> selected'
			});
			
			$('#SRC_STUDENT_STATUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= STUDENT_STATUS ?>',
				nonSelectedText: '<?= STUDENT_STATUS ?>',
				numberDisplayed: 1,
				nSelectedText: '<?= STUDENT_STATUS ?> selected'
			});
			
			$('#SRC_TERM_MASTER').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= FIRST_TERM_DATE ?>',
				nonSelectedText: '<?= FIRST_TERM_DATE ?>',
				numberDisplayed: 1,
				nSelectedText: '<?= FIRST_TERM_DATE ?> selected'
			});
			Ticket #1612 */
		});
	</script>

	<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" /> <!-- Ticket #1612 -->
	<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script> <!-- Ticket #1612 -->
	<script type="text/javascript">
		/* Ticket #1612 */
		function search_student() {
			jQuery(document).ready(function($) {
				$('[id^=BATCH_PK_STUDENT_MASTER_]').select2({
					tags: [],
					placeholder: "",
					minimumInputLength: 2,
					createTag: function() { // DIAM-1778
						// Disable tagging
						return null;
					},
					ajax: {
						url: 'ajax_get_misc_sudent_name',
						dataType: 'json',
						type: "GET",
						quietMillis: 50,
						data: function(params) {
							var query = {
								search: params.term,
							}
							return query;
						},
						processResults: function(data) {
							var myResults = [];
							$.each(data, function(index, item) {
								myResults.push({
									'id': item.itemId,
									'text': item.itemName,
									'ssn': item.ssn
								});
							});
							return {
								results: myResults
							};
						}
					}
				});

				var dd = document.getElementsByClassName('select2-container--default');
				for (var i = 0; i < dd.length; i++) {
					dd[i].style.width = '200px';
				}

			});
		}
		/* Ticket #1612 */
	</script>
</body>

</html>