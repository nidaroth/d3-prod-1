<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/batch_payment.php");
require_once("function_student_ledger.php");
require_once("function_update_disbursement_status.php");
require_once("function_unpost_batch_history.php");

require_once("receipt_pdf_function.php");

require_once("../global/mail.php");
require_once("../global/texting.php");
require_once("check_access.php");

if (check_access('MANAGEMENT_ACCOUNTING') == 0) {
	header("location:../index");
	exit;
}

function logCheckoutPayment($logtype, $logvalue)
{

	$dataOut =  date('Y-m-d H:i:s') . "|" . $logtype . "|" . $logvalue . "\n";
	//stop writing logs for checkout process as we don't required this logs
	$logFileName = "./temp/payment_batch.txt";
	$logFile = fopen($logFileName, 'a+') or die("can't open file");
	fwrite($logFile, $dataOut);
	fclose($logFile);
}

$_SESSION['DISB_ID'] = '';
if (!empty($_POST)) {
	//echo "<pre>";print_r($_POST);exit;

	if ($_POST['STS_HID'] == 3) {

		$PK_PAYMENT_BATCH_MASTER = $_GET['id'];
		$PAYMENT_BATCH_MASTER['POSTED_DATE'] 		= '';
		$PAYMENT_BATCH_MASTER['PK_BATCH_STATUS'] 	= $_POST['STS_HID'];
		$PAYMENT_BATCH_MASTER['EDITED_BY'] 			= $_SESSION['PK_USER'];
		$PAYMENT_BATCH_MASTER['EDITED_ON'] 			= date("Y-m-d H:i");
		db_perform('S_PAYMENT_BATCH_MASTER', $PAYMENT_BATCH_MASTER, 'update'," PK_PAYMENT_BATCH_MASTER = '$PK_PAYMENT_BATCH_MASTER' AND PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' ");
		
		$UNPOSTED_HISTORY['PK_PAYMENT_BATCH_MASTER']  	= $PK_PAYMENT_BATCH_MASTER;
		$UNPOSTED_HISTORY['PK_ACCOUNT']  				= $_SESSION['PK_ACCOUNT'];
		$UNPOSTED_HISTORY['UNPOSTED_BY'] 				= $_SESSION['PK_USER'];
		$UNPOSTED_HISTORY['UNPOSTED_ON'] 				= date("Y-m-d H:i");
		db_perform('S_PAYMENT_BATCH_UNPOSTED_HISTORY', $UNPOSTED_HISTORY, 'insert');
		
		$res_det = $db->Execute("SELECT PK_PAYMENT_BATCH_DETAIL,PK_STUDENT_DISBURSEMENT FROM S_PAYMENT_BATCH_DETAIL WHERE PK_PAYMENT_BATCH_MASTER = '$PK_PAYMENT_BATCH_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		while (!$res_det->EOF) { 
			$PK_PAYMENT_BATCH_DETAIL = $res_det->fields['PK_PAYMENT_BATCH_DETAIL'];
			$PK_STUDENT_DISBURSEMENT = $res_det->fields['PK_STUDENT_DISBURSEMENT'];
			
			$PAYMENT_BATCH_DETAIL['RECEIPT_NO'] 			 = '';
			$PAYMENT_BATCH_DETAIL['PK_BATCH_PAYMENT_STATUS'] = 2;
			db_perform('S_PAYMENT_BATCH_DETAIL', $PAYMENT_BATCH_DETAIL, 'update'," PK_PAYMENT_BATCH_DETAIL = '$PK_PAYMENT_BATCH_DETAIL' ");

			
			
			$ledger_data_del['PK_PAYMENT_BATCH_DETAIL'] = $PK_PAYMENT_BATCH_DETAIL;
			delete_student_ledger($ledger_data_del);
		
			//$STUDENT_DISBURSEMENT['PK_PAYMENT_BATCH_DETAIL'] = '';
			$STUDENT_DISBURSEMENT['DEPOSITED_DATE'] 		 = '';
			$STUDENT_DISBURSEMENT['PK_DISBURSEMENT_STATUS']  = 3;
			db_perform('S_STUDENT_DISBURSEMENT', $STUDENT_DISBURSEMENT, 'update'," PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT' ");
		
			$res_det->MoveNext();
		}

		header("location:batch_payment?id=" . $PK_PAYMENT_BATCH_MASTER);
		exit;
	}

	$PAYMENT_BATCH_MASTER['PK_AR_LEDGER_CODE'] 	= implode(",", $_POST['PK_AR_LEDGER_CODE']);
	$PAYMENT_BATCH_MASTER['BATCH_PK_CAMPUS'] 	= implode(",", $_POST['BATCH_PK_CAMPUS']);
	$PAYMENT_BATCH_MASTER['DATE_RECEIVED'] 	 	= $_POST['DATE_RECEIVED'];
	$PAYMENT_BATCH_MASTER['CHECK_NO'] 		 	= $_POST['CHECK_NO'];
	$PAYMENT_BATCH_MASTER['AMOUNT'] 		 	= $_POST['AMOUNT'];
	$PAYMENT_BATCH_MASTER['COMMENTS'] 		 	= $_POST['COMMENTS'];
	$PAYMENT_BATCH_MASTER['TRANS_DATA_TYPE'] 	= $_POST['TRANS_DATA_TYPE']; // DAIM - 86,88


	$PAYMENT_BATCH_MASTER['PAYMENT_BATCH_START_DATE'] 	 	= $_POST['START_DATE'];
	$PAYMENT_BATCH_MASTER['PAYMENT_BATCH_END_DATE'] 	 	= $_POST['END_DATE'];

	if ($PAYMENT_BATCH_MASTER['PAYMENT_BATCH_START_DATE'] != '')
		$PAYMENT_BATCH_MASTER['PAYMENT_BATCH_START_DATE'] = date("Y-m-d", strtotime($PAYMENT_BATCH_MASTER['PAYMENT_BATCH_START_DATE']));

	if ($PAYMENT_BATCH_MASTER['PAYMENT_BATCH_END_DATE'] != '')
		$PAYMENT_BATCH_MASTER['PAYMENT_BATCH_END_DATE'] = date("Y-m-d", strtotime($PAYMENT_BATCH_MASTER['PAYMENT_BATCH_END_DATE']));

	if ($_POST['STS_HID'] > 0)
		$PAYMENT_BATCH_MASTER['PK_BATCH_STATUS'] = $_POST['STS_HID'];

	if ($PAYMENT_BATCH_MASTER['PK_BATCH_STATUS'] == 2)
		$PAYMENT_BATCH_MASTER['POSTED_DATE'] = date("Y-m-d");

	if ($PAYMENT_BATCH_MASTER['DATE_RECEIVED'] != '')
		$PAYMENT_BATCH_MASTER['DATE_RECEIVED'] = date("Y-m-d", strtotime($PAYMENT_BATCH_MASTER['DATE_RECEIVED']));
	//hold payment
	if ($_POST['STS_HID'] == 1) {
		include('batch_payment_hold.php');
	}
	//post ledger
	if ($_POST['STS_HID'] == 2) {
	include('batch_payment_post.php');
	}

	header("location:batch_payment?id=" . $PK_PAYMENT_BATCH_MASTER);
}
$credit_card_flag=0; //DIAM-987
// default value code
if ($_GET['id'] == '') {
	$BATCH_NO 			= '';
	$DATE_RECEIVED	 	= '';
	$POSTED_DATE  		= '';
	$PK_AR_LEDGER_CODE	= '';
	$CHECK_NO	 		= '';
	$AMOUNT				= 0;
	$COMMENTS	 		= '';
	$PK_BATCH_STATUS	= '';
	$START_DATE	 		= '';
	$END_DATE	 		= '';
	$TRANS_DATA_TYPE    = ''; // DAIM - 86,88
	$BATCH_PK_CAMPUS_ARR 	= array();
	$PK_AR_LEDGER_CODE_ARR 	= array();

	$res_acc = $db->Execute("SELECT PAYMENT_BATCH_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$BATCH_NO = 'P' . $res_acc->fields['PAYMENT_BATCH_NO'];

	/* Ticket #849  */
	$res_camp = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if ($res_camp->RecordCount() == 1) {
		$BATCH_PK_CAMPUS1 		= $res_camp->fields['PK_CAMPUS'];
		$BATCH_PK_CAMPUS_ARR	= explode(",", $BATCH_PK_CAMPUS1);
	}
	/* Ticket #849  */
} else {
	//echo "SELECT * FROM S_PAYMENT_BATCH_MASTER WHERE PK_PAYMENT_BATCH_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	//exit;
	$res = $db->Execute("SELECT * FROM S_PAYMENT_BATCH_MASTER WHERE PK_PAYMENT_BATCH_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if ($res->RecordCount() == 0) {
		header("location:manage_batch_payment");
		exit;
	}
	//DIAM-987
	$detect_card_sql="SELECT ORDER_ID,PK_STUDENT_CREDIT_CARD_PAYMENT FROM S_PAYMENT_BATCH_DETAIL WHERE PK_PAYMENT_BATCH_MASTER = $_GET[id] AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CREDIT_CARD_PAYMENT!=0 LIMIT 1";
	$card_res=$db->Execute($detect_card_sql);
	if ($card_res->RecordCount() > 0) {
		$credit_card_flag=1;
	}
	
	$BATCH_NO 			= $res->fields['BATCH_NO'];
	$DATE_RECEIVED  	= $res->fields['DATE_RECEIVED'];
	$POSTED_DATE  		= $res->fields['POSTED_DATE'];
	$PK_AR_LEDGER_CODE  = $res->fields['PK_AR_LEDGER_CODE'];
	$CHECK_NO  			= $res->fields['CHECK_NO'];
	$AMOUNT				= $res->fields['AMOUNT'];
	$COMMENTS  			= $res->fields['COMMENTS'];
	$PK_BATCH_STATUS	= $res->fields['PK_BATCH_STATUS'];
	$BATCH_PK_CAMPUS1	= $res->fields['BATCH_PK_CAMPUS'];
	$TRANS_DATA_TYPE    = $res->fields['TRANS_DATA_TYPE']; // DAIM - 86,88
	$BATCH_PK_CAMPUS_ARR	= explode(",", $res->fields['BATCH_PK_CAMPUS']);
	$PK_AR_LEDGER_CODE_ARR  = explode(",", $res->fields['PK_AR_LEDGER_CODE']);

	$START_DATE	 		= $res->fields['PAYMENT_BATCH_START_DATE'];
	$END_DATE	 		= $res->fields['PAYMENT_BATCH_END_DATE'];

	if ($START_DATE == '0000-00-00')
		$START_DATE = '';
	else
		$START_DATE = date("m/d/Y", strtotime($START_DATE));

	if ($END_DATE == '0000-00-00')
		$END_DATE = '';
	else
		$END_DATE = date("m/d/Y", strtotime($END_DATE));

	$res = $db->Execute("SELECT BATCH_STATUS FROM M_BATCH_STATUS WHERE PK_BATCH_STATUS = '$PK_BATCH_STATUS' ");
	$BATCH_STATUS = $res->fields['BATCH_STATUS'];

	if ($DATE_RECEIVED == '0000-00-00')
		$DATE_RECEIVED = '';
	else
		$DATE_RECEIVED = date("m/d/Y", strtotime($DATE_RECEIVED));

	if ($POSTED_DATE == '0000-00-00')
		$POSTED_DATE = '';
	else
		$POSTED_DATE = date("m/d/Y", strtotime($POSTED_DATE));
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
	<title><?= PAYMENT_BATCH_PAGE_TITLE ?> | <?= $title ?></title>
	<style>
		.table th,
		.table td {
			padding: 0.5rem;
		}

		li>a>label {
			position: unset !important;
		}

/* Ticket # 1149 - term */
.dropdown-menu>li>a {
	white-space: nowrap;
}

.option_red>a>label {
	color: red !important
}

/* Ticket # 1149 - term */

.tableFixHead {
	overflow-y: auto;
	max-height: 500px;
}

.tableFixHead thead th {
	position: sticky;
	top: 0;
}

.tableFixHead thead th {
	background: #E8E8E8;
}

#advice-required-entry-BATCH_PK_CAMPUS,
#advice-required-entry-PK_AR_LEDGER_CODE {
	position: relative;
	top: 56px;
	width: 142px
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
<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
	<? require_once("pre_load.php"); ?>
	<div id="loaders" style="display: none;">
				<div class="lds-ring">
					<div></div>
					<div></div>
					<div></div>
					<div></div>
				</div>
</div>
	<div id="main-wrapper">
		<? require_once("menu.php"); ?>
		
		<div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles">
					<div class="col-md-12 align-self-center">
						<h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=PAYMENT_BATCH_PAGE_TITLE?> </h4>
						<?php if(isset($_GET['msg']) && $_GET['msg']==1) { ?>
						<div class="alert alert-danger mt-2 alert-dismissible fade show" role="alert" >
						 This payment batch already exists! <a href="batch_payment?id=<?=$_GET['batch_id']?>">Pleaes click here to open existing payment batch</a>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
					</div>
						<?php } ?>
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
														<? if ($PK_BATCH_STATUS != 2) { ?>
															<select id="BATCH_PK_CAMPUS" name="BATCH_PK_CAMPUS[]" multiple class="form-control required-entry">
																<? $res_type = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by CAMPUS_CODE ASC");
																while (!$res_type->EOF) {
																	$selected = "";
																	foreach ($BATCH_PK_CAMPUS_ARR as $PK_CAMPUS) {
																		if ($res_type->fields['PK_CAMPUS'] == $PK_CAMPUS) {
																			$selected = "selected";
																			break;
																		}
																	}
																	?>
																	<option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <?= $selected ?>><?= $res_type->fields['CAMPUS_CODE'] ?></option>
																	<? $res_type->MoveNext();
																} ?>
															</select>
														<? } else {
															$str = '';
															$res_type = $db->Execute("select CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($BATCH_PK_CAMPUS1)  order by CAMPUS_CODE ASC");
															while (!$res_type->EOF) {
																if ($str != '')
																	$str .= ", ";
																$str .= $res_type->fields['CAMPUS_CODE'];
																$res_type->MoveNext();
															} ?>
															<div class="form-group m-b-40 focused">
																<?= $str ?>
																<span class="bar"></span>
																<label for="CAMPUS"><?= CAMPUS ?></label>
															</div>
														<? } ?>
													</div>
												</div>

												<?php 
												if(check_access('MANAGEMENT_UNPOST_BATCHES')==1 && $PK_BATCH_STATUS == 2 && check_global_access()==1 && $credit_card_flag==0){  ?>
												<div class="col-md-6">
													<button type="button" onclick="save_form(3)" id="UNPOST_BTN" class="btn waves-effect waves-light btn-info"><?=UNPOSTBATCH?></button>
													<span class="bar"></span>
												</div>
												<?php } ?>
											</div>

											<div class="row">
												<div class="col-md-12">
													<label for="TRANS_DATE_TYPE"><?= TRANS_DATE_TYPE ?></label><br><br>
													<div class="form-group m-b-40">
														<?php
														$checked = '';
														$get_id = $_GET['id'];
														if ($get_id == '') {
															$checked = 'checked';
														}
														?>
														<div class="row form-group">
															<div style="padding-left: 2rem;" class="custom-control custom-radio col-md-6">
																<input type="radio" id="BATCH_DATESaa" name="TRANS_DATA_TYPE" value="1" <?= $checked ?> <? if ($TRANS_DATA_TYPE == '1') {echo "checked";} ?> class="custom-control-input" onchange="set_batch_date()">
																<label class="custom-control-label" for="BATCH_DATESaa"><?= BATCH_DATE ?></label>
															</div>
															<div class="custom-control custom-radio col-md-6">
																<input type="radio" id="DISBURSEMENT_DATEbb" name="TRANS_DATA_TYPE" value="2" <? if ($TRANS_DATA_TYPE == '2') {echo "checked";} ?> class="custom-control-input" onchange="set_disbursement_date()">
																<label class="custom-control-label" for="DISBURSEMENT_DATEbb"><?= DISBURSEMENT_DATE ?></label>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-6">
													<?php
													$get_id = $_GET['id'];
													if ($get_id != '' && $TRANS_DATA_TYPE == '2') {
														$required_entry = '';
													} else {
														$required_entry = 'required-entry';
													}
													?>
													<div class="form-group m-b-40 ">
														<input type="text" class="form-control date validate-date date-inputmask <?= $required_entry ?>" id="DATE_RECEIVED" name="DATE_RECEIVED" value="<?= $DATE_RECEIVED ?>" onchange="set_trans_date()">
														<span class="bar"></span>
														<label for="DATE_RECEIVED"><?= BATCH_DATE ?></label>
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
												<div class="col-md-6">
													<div class="form-group m-b-40 ">
														<input type="text" class="form-control" id="CHECK_NO" name="CHECK_NO" value="<?= $CHECK_NO ?>" onchange="set_check_no()">
														<span class="bar"></span>
														<label for="CHECK_NO"><?= CHECK_NO ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40 ">
														<input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH ?>" style="font-family: FontAwesome;" value=""> <!-- onkeypress="search(event)" -->
														<span class="bar"></span>
													</div>

												</div>
											</div>
										</div>

										<div class="col-md-1" style="flex: 0 0 2.33333%;max-width: 2.33333%;"></div>
										<div class="col-md-2" style="flex: 0 0 27%;max-width: 27%;">
											<div class="row">
												<div class="col-md-6">
													<?= CREDIT_TOTAL ?>
													<input type="hidden" class="form-control required-entry" id="AMOUNT" name="AMOUNT" value="<?= $AMOUNT ?>">
													<input type="hidden" name="STS_HID" id="STS_HID" value='' />
													<input type="hidden" name="TOTAL_AMOUNT" id="TOTAL_AMOUNT" value='' />
													<input type="hidden" name="disbusement_students" id="disbusement_students" />
												</div>
												<div class="col-md-6" id="CREDIT_TOTAL" style="text-align:right;" ><?=number_format_value_checker($AMOUNT, 2)?></div>
											</div>

											<div class="row">
												<div class="col-md-6" style="border-top:1px solid #000;font-weight:bold">
													<?= BATCH_TOTAL ?>
												</div>
												<div class="col-md-6" id="BATCH_TOTAL" style="border-top:1px solid #000;text-align:right;font-weight:bold" ><?=number_format_value_checker($AMOUNT, 2)?></div>
											</div>
											<br />

											<div class="row">
												<div class="col-md-12">
													<? if ($PK_BATCH_STATUS != 2) { ?>
														<div class="form-group m-b-40">
															<select id="PK_AR_LEDGER_CODE" name="PK_AR_LEDGER_CODE[]" multiple class="form-control required-entry" onchange="">
																<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by CODE ASC");
																while (!$res_type->EOF) {
																	$selected = "";
																	if (!empty($PK_AR_LEDGER_CODE_ARR)) {
																		foreach ($PK_AR_LEDGER_CODE_ARR as $PK_AR_LEDGER_CODE11) {
																			if ($PK_AR_LEDGER_CODE11 == $res_type->fields['PK_AR_LEDGER_CODE'])
																				$selected = "selected";
																		}
																	} ?>
																	<option value="<?= $res_type->fields['PK_AR_LEDGER_CODE'] ?>" <? if ($res_type->fields['PK_AR_LEDGER_CODE'] == $PK_AR_LEDGER_CODE) echo "selected"; ?> <?= $selected ?>><?= $res_type->fields['CODE'] . ' - ' . $res_type->fields['LEDGER_DESCRIPTION'] ?></option>
																	<? $res_type->MoveNext();
																} ?>
															</select>
														</div>
													<? } else { ?>
														<div class="form-group m-b-40 focused">
															<? $res_type = $db->Execute("select CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE) ");
															while (!$res_type->EOF) {
																echo $res_type->fields['CODE'] . ' - ' . $res_type->fields['LEDGER_DESCRIPTION'];
																$res_type->MoveNext();
															} ?>
															<span class="bar"></span>
															<label for="PK_AR_LEDGER_CODE"><?= LEDGER_CODES ?></label>
														</div>
													<? } ?>
												</div>
											</div>

											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="text" class="form-control date" id="START_DATE" name="START_DATE" value="<?= $START_DATE ?>">
														<span class="bar"></span>
														<label for="START_DATE"><?= DISBURSEMENT_START_DATE ?></label>
													</div>
												</div>
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="text" class="form-control date" id="END_DATE" name="END_DATE" value="<?= $END_DATE ?>">
														<span class="bar"></span>
														<label for="END_DATE"><?= DISBURSEMENT_END_DATE ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-12 text-right">
											<? //if($PK_BATCH_STATUS != 2){ 
												if ($_GET['id'] == '') { ?>
													<button type="button" onclick="get_students()" class="btn waves-effect waves-light btn-info"><?= BUILD_BATCH ?></button>
												<? } ?>
											</div>
										</div>
										<br>
										<!-- DIAM - 88,86 -->
										<div class="row" id="VIEW_STUDENTS" style="display:none;">
											<div class="col-md-12 text-right">
												<button type="button" onclick="get_view_students()" class="btn waves-effect waves-light btn-info"><?= VIEW_STUDENTS ?></button>
											</div>
										</div>
										<!-- End DIAM - 88,86 -->
									</div>
									<div class="col-md-1" style="flex: 0 0 2.33333%;max-width: 2.33333%;"></div>

									<div class="col-md-4">
										<div class="row">
											<div class="col-md-12">
												Applies projected (unpaid) disbursements (awards) from <b style="font-weight:bold;">Finance > Finance Plan > Disbursements.</b>
											</div>
										</div>

										<br /><br />
										<div class="row">
											<div class="col-md-12">
												<div class="form-group m-b-20">
													<textarea class="form-control" rows="6" id="COMMENTS" name="COMMENTS"><?= $COMMENTS ?></textarea>
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

								<? if ($_GET['id'] != '') { ?>
									<div class="table-responsive tableFixHead">
										<? if ($PK_BATCH_STATUS == 2) {
											$_REQUEST['pms'] 			 = $_GET['id'];
											$_REQUEST['PK_BATCH_STATUS'] = $PK_BATCH_STATUS;
											include('ajax_get_batch_payment_detail.php');
										} else {
											$_REQUEST['ledger'] = $PK_AR_LEDGER_CODE;
											$_REQUEST['BID'] 	= $_GET['id'];
											include('ajax_get_unpaid_students_from_ledger.php');
										} ?>
									</div>
									<br />

									<div class="row">
										<div class="col-md-6" style="flex: 0 0 53%;max-width: 53%;">
											<div style="font-weight:bold;text-align:right;">Total $</div>
										</div>
										<div class="col-md-4">
											<div id="total_div" style="font-weight:bold;">
												<? if ($PK_BATCH_STATUS == 2) echo "$ " . number_format($posted_total, 2) ?>
											</div>
										</div>
									</div>
									<br />
								<?	} ?>

								<div class="table-responsive tableFixHead" id="student_div">
								</div>
								<div class="row" id="add_total_1_div" style="display:none">
									<div class="col-md-6" style="flex: 0 0 53%;max-width: 53%;">
										<div style="font-weight:bold;text-align:right;">Total $</div>
									</div>
									<div class="col-md-4">
										<div id="add_total_div" style="font-weight:bold;"></div>
									</div>
								</div>
								<br />

								<div class="row">
									<div class="col-md-12">
										<div style="text-align:center">
											<? if ($PK_BATCH_STATUS != 2 && $_GET['id'] > 0) { ?>
												<button type="button" onclick="get_students()" class="btn waves-effect waves-light btn-info"><i class="fa fa-plus-circle"></i> <?= ADD_STUDENTS ?></button>
											<? } ?>

											<? if ($PK_BATCH_STATUS == 1 || $PK_BATCH_STATUS == '' || $PK_BATCH_STATUS == 3) { ?>
												<button type="button" onclick="save_form(1)" class="btn waves-effect waves-light btn-info"><?= SAVE_AS_HOLD ?></button>

												<button type="button" onclick="save_form(2)" class="btn waves-effect waves-light btn-info"><?= SAVE_AS_POST ?></button>
											<? } else if ($PK_BATCH_STATUS == 2) { ?>
												<!--<button type="button" onclick="save_form(0)" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>-->

											<? /*
											<button type="button" onclick="save_form(3)" id="UNPOST_BTN" class="btn waves-effect waves-light btn-info"><?=UNPOST?></button>
											*/ ?>

											<button type="button" id="DOWNLOAD_BTN" class="btn waves-effect waves-light btn-info" onclick="window.location.href='receipt_pdf.php?mid=<?= $_GET['id'] ?>'"><?= DOWNLOAD_RECEIPTS ?></button>
										<? } ?>

										<? if ($_GET['id'] != '') { ?>
											<button type="button" id="DOWNLOAD_REPORT" class="btn waves-effect waves-light btn-info" onclick="window.location.href='batch_payment_pdf.php?id=<?= $_GET['id'] ?>'"><?= DOWNLOAD_REPORT ?></button>
										<? } ?>

										<!--<button type="button" onclick="make_payment_by_card()" id="UNPOST_BTN" class="btn waves-effect waves-light btn-info"><?= MAKE_PAYMENT_BY_CARD ?></button>-->

										<button type="button" id="CANCEL_BTN" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_batch_payment'"><?= CANCEL ?></button>

									</div>
								</div>
							</div>

							<div id="delete_div"></div>
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

<div class="modal" id="deleteBatchDetail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel1"><?= CONFIRMATION ?></h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<?= DELETE_MESSAGE_GENERAL ?>
					<input type="hidden" id="DELETE_BATCH_ID" value="0" />
					<input type="hidden" id="DELETE_DISP_ID" value="" />

				</div>
			</div>
			<div class="modal-footer">
				<button type="button" onclick="conf_delete_batch_detail(1)" class="btn waves-effect waves-light btn-info"><?= YES ?></button>
				<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete_batch_detail(0)"><?= NO ?></button>
			</div>
		</div>
	</div>
</div>

<!-- DIAM - 88, 68 -->
<div class="modal" id="StudentDetail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel1">selected Payment Batch Student Details</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<table class="table-striped table table-hover table-bordered table-batch">
						<thead style="position: sticky;top: 0;z-index: 9;">
							<th class="sticky_header" scope="col"><?= NAME ?></th>
							<th class="sticky_header" scope="col"><?= STUDENT_ID ?></th>
						</thead>
						<tbody id="table-header-batch-detail">
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" aria-label="Close" class="btn waves-effect waves-light btn-dark"><?= CANCEL ?></button>
			</div>
		</div>
	</div>
</div>
<!-- End DIAM - 88, 68 -->

<!-- Ticket # 1871 -->
<div class="modal" id="dupDisbDetail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
	<div class="modal-dialog" role="document" style="max-width: 90%;">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel1">Disbursement Exists In Another Batch</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				The disbursement(s) below are in another batch and cannot be added to this batch. Please remove the disbursement(s) from this batch to continue.<br />
				<div class="form-group" id="dupDisbDetail_div">
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" onclick="close_popup()" class="btn waves-effect waves-light btn-info"><?= OK ?></button>
			</div>
		</div>
	</div>
</div>
<!-- Ticket # 1871 -->

</div>

<? require_once("js.php"); ?>
<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
	/** DIAM - 88 **/

	jQuery(document).ready(function($) {
		$('#DATE_RECEIVED .date').inputmask({
			yearrange: {
				minyear: '0000',
				maxyear: '9999'
			}
		})

	});
	jQuery('.date').datepicker({
		todayHighlight: true,
		orientation: "bottom auto"
	}).change(dateChanged);

	function dateChanged(ev) {

		var some_date = jQuery(this).val();
		var trans_date = jQuery(".TRANSACTION_DATE").val();
	//console.log("Date is changed " + some_date)
		try {
			var expldoed = some_date.split('/');
			exploded = parseInt(expldoed[2])
			if (exploded < 1900 || exploded > 9999) {
				alert("Please enter a valid year greater than or equal to 1900.");
				jQuery(this).val(trans_date).datepicker("update");
				return null;
			}
		} catch (err) {
			document.getElementById("DATE_RECEIVED").innerHTML = err.message;
		}
	}
	/** End DIAM - 88 **/
	jQuery(document).ready(function($) {

		<? if ($_GET['id'] != '' && $PK_BATCH_STATUS != 2) { ?>
			calc_total(0)

		//if(document.getElementById('TOTAL_AMOUNT_1'))
		//document.getElementById('TOTAL_AMOUNT').value = document.getElementById('TOTAL_AMOUNT_1').value

		<? } ?>
		<? if ($PK_BATCH_STATUS == 2) { ?>
			disableForm(document.form1)
		<? } ?>

	});

	/** DIAM-1423 **/
	function create_student_notes()
	{
		var str = '';
		var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]');
		var unique = [];
		var distinct = [];
		for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++)
		{

			if( !unique[PK_STUDENT_ENROLLMENT[i].value]){
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
		if(distinct !== "")
		{
			jQuery(document).ready(function($) {

				var data = 'enrollment_ids='+distinct;
				var value = $.ajax({
						url: "ajax_set_session_create_student_notes",
						type: "POST",
						data: data,
						async: true,
						cache: false,
						success: function(data) {
							//alert(data)
							if(data == 'success')
							{
								window.location.href='student_notes.php?p=m&t=5&batch=<?=$_GET['id']?>';
							}
								
						}
				}).responseText;
				
			});
			
		}
		
	}
	/** End DIAM-1423 **/
</script>

<script src="../backend_assets/dist/js/validation_prototype.js"></script>
<script src="../backend_assets/dist/js/validation.js"></script>
<script type="text/javascript">
//var form1 = new Validation('form1');
//29 june 2023
var pk_disbusement_arr=[];

// DIAM - 88, 68	
	jQuery(document).ready(function($) {

		$('#SEARCH').blur(function() {
			var $rows = $('#table-header-batch tr');
			var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();

			$rows.show().filter(function() {

				var text = $(this).find('td:nth-child(2),td:nth-child(3)').text().replace(/\s+/g, ' ').toLowerCase();
				//console.log('row ->')
				//console.log($(this).find('td:nth-child(2),td:nth-child(3)').text())
				return !~text.indexOf(val);
			}).hide();
		});
	});

// DIAM - 88, 68
	function search(e) {
		if (e.keyCode == 13) {
			get_students();
		}
	}
// End DIAM - 88, 68

/* Ticket # 1670  */
	function save_form(val) {
		
		document.getElementById('STS_HID').value = val

		if (val == 3) {
			
				jQuery("#unpostModal").modal()
	
		} else {
		//alert('aaa')
			jQuery(document).ready(function($) {

			/* Ticket # 1871 */
				var disb_id = '';
				var PK_STUDENT_DISBURSEMENT = document.getElementsByName('PK_STUDENT_DISBURSEMENT[]')
				for (var i = 0; i < PK_STUDENT_DISBURSEMENT.length; i++) {
					if (PK_STUDENT_DISBURSEMENT[i].checked == true) {
						if (disb_id != '')
							disb_id += ',';

						disb_id += PK_STUDENT_DISBURSEMENT[i].value;
					}
				}
				
				var data  = 'pk_id=<?=$_GET['id']?>&disb_id='+disb_id; //Ticket # 1871
				var value = $.ajax({
					url: "ajax_check_batch_payment_status",
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {
						data = data.split("!----@");
						
						//alert(data)
					
						if(data[0] == "c") {
							$('#loaders').css('display','none');
							document.getElementById('dupDisbDetail_div').innerHTML = data[1]
							$("#dupDisbDetail").modal()
						} else if(data[0] == "a") {
							var BATCH_TRANSACTION_DATE = document.getElementsByClassName('TRANSACTION_DATE')
							/* if(val == 2){
								for(var i = 0 ; i < BATCH_TRANSACTION_DATE.length ; i++){
									document.getElementById(BATCH_TRANSACTION_DATE[i].id).classList.remove("required-entry");
								}
								document.getElementById('DATE_RECEIVED').classList.remove("required-entry");
							}*/

						var valid = new Validation('form1', {
							onSubmit: false
						});
						var result = valid.validate();

						if (result == true) {
							//29 june 2023
							show_only_selected();
							var submit = 0
							var PK_STUDENT_DISBURSEMENT = document.getElementsByName('PK_STUDENT_DISBURSEMENT[]')
							for (var i = 0; i < PK_STUDENT_DISBURSEMENT.length; i++) {

								if (PK_STUDENT_DISBURSEMENT[i].checked == true) {
									submit = 1
									break;
								}
							}

							if (submit == 0) {
								for (var i = 0; i < PK_STUDENT_DISBURSEMENT.length; i++) {
									if (PK_STUDENT_DISBURSEMENT[i].type == "hidden") {
										submit = 1;
										break;
									}
								}
							}

							if (submit == 1)
								document.form1.submit();
							else {
								alert("Please Add Student To Batch");
							}
						}
					} else {
						alert("Batch Already Posted");
						location.reload();
					}
				}
				/* Ticket # 1871 */
			}).responseText;
		});
		}
	}
/* Ticket # 1670  */

/* Ticket # 1871 */
	function close_popup() {
		jQuery(document).ready(function($) {
			$("#dupDisbDetail").modal("hide");
		});
	}
/* Ticket # 1871 */

	function loader(id) {
		document.getElementById(id).innerHTML = '<tr><td><div style="position: inherit;margin-top: 0;height: 46px;"><div class="datagrid-mask" style="display:block;top:74%;"></div><div class="datagrid-mask-msg" style="display:block;left:44%;top:80%"> Please wait...</div></div></td></tr>';

	}

	function get_students() {
		loader('student_div');
		jQuery(document).ready(function($) {

			var SEARCH = document.getElementById('SEARCH').value
			var START_DATE = document.getElementById('START_DATE').value
			var END_DATE = document.getElementById('END_DATE').value
			var data = 'ledger=' + $("#PK_AR_LEDGER_CODE").val() + '&FROM_DATE=' + START_DATE + '&END_DATE=' + END_DATE + '&campus_id=' + $("#BATCH_PK_CAMPUS").val() + '&BID=&show_search=' + SEARCH + '&batch_check_no=' + $("#CHECK_NO").val() + '&batch_date=' + $("#DATE_RECEIVED").val()
			var value = $.ajax({
				url: "ajax_get_unpaid_students_from_ledger",
				type: "POST",
				data: data,
				async: true,
				cache: false,
				success: function(data) {
				//alert(data)
					document.getElementById('student_div').innerHTML = data
					document.getElementById('add_total_1_div').style.display = 'flex'
					jQuery('.date').datepicker({
						todayHighlight: true,
						orientation: "bottom auto"
					}) //DIAM-1158
					calc_total(1);
				}
			}).responseText;
		});
	}

	function calc_total(update_amt) {
		var PK_STUDENT_DISBURSEMENT = document.getElementsByName('PK_STUDENT_DISBURSEMENT[]')
		var total = 0;
		//29 june 2023
		// calculate the total for the select checkboxes
		for (var i = 0; i < PK_STUDENT_DISBURSEMENT.length; i++) {
			if (PK_STUDENT_DISBURSEMENT[i].checked == true) {
				var id = PK_STUDENT_DISBURSEMENT[i].value;
				//29 june 2023
				if(!pk_disbusement_arr.includes(id)){
					pk_disbusement_arr.push(id)
				}
				var PAID_AMOUNT = document.getElementById('PAID_AMOUNT_' + id).value

				if (PAID_AMOUNT == '')
					PAID_AMOUNT = 0;
				else
					PAID_AMOUNT = parseFloat(PAID_AMOUNT)

				total += PAID_AMOUNT
			}else{
				//29 june 2023
				let elementRemove = PK_STUDENT_DISBURSEMENT[i].value;
				if(pk_disbusement_arr.includes(elementRemove)){
				pk_disbusement_arr = pk_disbusement_arr.filter(element => element !== elementRemove);
				}
			}
		}
		


		if (document.getElementById('add_total_div'))
			document.getElementById('add_total_div').innerHTML = total.toFixed(2)

		var posted_tot = 0;
		//29 june 2023
	    // calculate the total for the hidden field
		for (var i = 0; i < PK_STUDENT_DISBURSEMENT.length; i++) {
			if (PK_STUDENT_DISBURSEMENT[i].type == "hidden") {
				var id = PK_STUDENT_DISBURSEMENT[i].value;
				var PAID_AMOUNT = document.getElementById('PAID_AMOUNT_' + id).value
				//29 june 2023
				if(!pk_disbusement_arr.includes(id)){
					pk_disbusement_arr.push(id)
				}
				if (PAID_AMOUNT == '')
					PAID_AMOUNT = 0;
				else
					PAID_AMOUNT = parseFloat(PAID_AMOUNT)

				total += PAID_AMOUNT
				posted_tot += PAID_AMOUNT
			}
		}

		if (document.getElementById('total_div'))
			document.getElementById('total_div').innerHTML = posted_tot.toFixed(2);

		document.getElementById('TOTAL_AMOUNT').value = total.toFixed(2);
		document.getElementById('AMOUNT').value = total.toFixed(2);
		document.getElementById('CREDIT_TOTAL').innerHTML = total.toFixed(2);
		document.getElementById('BATCH_TOTAL').innerHTML = total.toFixed(2);
		//29 june 2023
		document.getElementById('disbusement_students').value = pk_disbusement_arr.toString();		

		if (update_amt == 1)
			document.getElementById('AMOUNT').value = total.toFixed(2);
	}

// DIAM - 88,86
function paid_amount_value_change(id) 
{
	if(document.getElementById('DISBURSEMENT_TYPE_'+id).value == 2) // Adjust
	{
		var PAID_AMOUNT_NEW = document.getElementById('PAID_AMOUNT_' + id).value;
		var PAID_AMOUNT_CHECK = document.getElementById('PAID_AMOUNT_' + id);
		var PAID_AMOUNT_OLD = PAID_AMOUNT_CHECK.getAttribute('paid-amt');
		var numbers = /[^-?\d.]|\.(?=.*\.)/g;
		if (PAID_AMOUNT_NEW.match(numbers)) {
			alert("Please enter a valid amount. Please avoid spaces or other characters.");
			document.getElementById('PAID_AMOUNT_' + id).value = PAID_AMOUNT_OLD;
			calc_total(1);
		}
	}

	if(document.getElementById('DISBURSEMENT_TYPE_'+id).value == 1) // split
	{ 
		var PAID_AMOUNT_NEW = document.getElementById('PAID_AMOUNT_' + id).value;
		var PAID_AMOUNT_CHECK = document.getElementById('PAID_AMOUNT_' + id);
		var PAID_AMOUNT_OLD = PAID_AMOUNT_CHECK.getAttribute('paid-amt');
		// console.log("New="+PAID_AMOUNT_NEW);
		// console.log("Old="+PAID_AMOUNT_OLD);
		var numbers = /[^-?\d.]|\.(?=.*\.)/g;
		if (PAID_AMOUNT_NEW.match(numbers)) {
			alert("Please enter a valid amount. Please avoid spaces or other characters.");
			document.getElementById('PAID_AMOUNT_' + id).value = PAID_AMOUNT_OLD;
			calc_total(1);
		}
		if (parseFloat(PAID_AMOUNT_NEW) > parseFloat(PAID_AMOUNT_OLD)) {
			alert("Please enter an amount less than or equal to the disbursement amount.");
			document.getElementById('PAID_AMOUNT_' + id).value = PAID_AMOUNT_OLD;
			calc_total(1);
		}
	}

}

function check_number_validation(e)
{
	const regex  = /[^-?\d.]|\.(?=.*\.)/g;
	const numbers = /^-?\d+$/g;
	const subst  = '';
	const str    = e.value;
	const result = str.replace(regex, subst);
	if (str.match(numbers)) {
		e.value      = result + '.00';
	}
	else{
		e.value      = result;	
	}
	
}
// End DIAM - 88,86

function enable_button() {
	var flag = 0;
	var PK_STUDENT_DISBURSEMENT = document.getElementsByName('PK_STUDENT_DISBURSEMENT[]')
	for (var i = 0; i < PK_STUDENT_DISBURSEMENT.length; i++) {
		if (PK_STUDENT_DISBURSEMENT[i].checked == true) {
			flag = 1;
			break;
		}
	}

	if (document.getElementById('VIEW_STUDENTS')) {
		document.getElementById('VIEW_STUDENTS').setAttribute('style', 'display:none !important');
	}

	if (flag == 1) {
		if (document.getElementById('VIEW_STUDENTS'))
			document.getElementById('VIEW_STUDENTS').setAttribute('style', 'display:block !important');
	}
}

function get_view_students() {
	document.getElementById('table-header-batch-detail').innerHTML = '';
	jQuery(document).ready(function($) {
		$("#StudentDetail").modal();

		jQuery('input[type="checkbox"][name="PK_STUDENT_DISBURSEMENT[]"]:checked').map(function() {
			var name = jQuery(this).parent().parent().parent().find('td:nth-child(2)').text();
			var stud_id = jQuery(this).parent().parent().parent().find('td:nth-child(3)').text();
			document.getElementById('table-header-batch-detail').innerHTML = document.getElementById('table-header-batch-detail').innerHTML + '<tr><td>' + name + '</td><td>' + stud_id + '</td></tr>';

		}).get();

	});
}
// End DIAM - 88,86

function adjust_disbursement(id, type) {

	document.getElementById('PAID_AMOUNT_' + id).readOnly = false
	if (type == 'adjust') {
		document.getElementById('DISBURSEMENT_TYPE_' + id).value = 2
		document.getElementById('DISBURSEMENT_TYPE_DIV_' + id).innerHTML = 'Adjust'

		document.getElementById('adjust_' + id).onclick = null
		document.getElementById('split_' + id).onclick = null

		document.getElementById('adjust_' + id).style.color = "rgb(126, 125, 125)";
		document.getElementById('split_' + id).style.color = "rgb(126, 125, 125)";
	} else if (type == 'split') {
		document.getElementById('DISBURSEMENT_TYPE_' + id).value = 1
		document.getElementById('DISBURSEMENT_TYPE_DIV_' + id).innerHTML = 'Split'

		document.getElementById('adjust_' + id).onclick = null
		document.getElementById('split_' + id).onclick = null

		document.getElementById('adjust_' + id).style.color = "rgb(126, 125, 125)";
		document.getElementById('split_' + id).style.color = "rgb(126, 125, 125)";
	}

}

function show_stu_payment_det(id, dis_id, led, table_id) {
	var w = 1200;
	var h = 550;
	// var id = common_id;
	var left = (screen.width / 2) - (w / 2);
	var top = (screen.height / 2) - (h / 2);
	var parameter = 'toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,width=' + w + ', height=' + h + ', top=' + top + ', left=' + left;
	window.open('student_disbursement_detail?id=' + id + '&dis_id=' + dis_id + '&led=' + led + '&table_id=' + table_id, '', parameter);
	return false;
}

function delete_row(id) {
	jQuery(document).ready(function($) {
		$("#deleteModal").modal()
		$("#DELETE_ID").val(id)
	});
}

function conf_delete(val) {
	jQuery(document).ready(function($) {
		if (val == 1) {
			var data = 'id=' + $("#DELETE_ID").val();
			var value = $.ajax({
				url: "ajax_delete_batch_payment_detail",
				type: "POST",
				data: data,
				async: false,
				cache: false,
				success: function(data) {
					//alert(data)
					get_detail();
				}
			}).responseText;
		}
		$("#deleteModal").modal("hide");
	});
}

function delete_batch_detail(batch_id, disp_id) {
	jQuery(document).ready(function($) {
		$("#deleteBatchDetail").modal()
		$("#DELETE_BATCH_ID").val(batch_id)
		$("#DELETE_DISP_ID").val(disp_id)
	});
}

function conf_delete_batch_detail(val) {
	if (val == 1) {
	var el = document.getElementById("loaders");
	el.style.display="block";
	}
	//diam-775
	pk_disbusement_arr=[];
    setTimeout(() => {
		jQuery(document).ready(function($) {
		if (val == 1) {
			var DELETE_BATCH_ID = $("#DELETE_BATCH_ID").val()
			var DELETE_DISP_ID = $("#DELETE_DISP_ID").val()

			var data = 'id=' + DELETE_BATCH_ID;
			var value = $.ajax({
				url: "ajax_delete_batch_payment_detail",
				type: "POST",
				data: data,
				async: false,
				cache: false,

				success: function(data) {
					$('#loaders').hide();
					$("#TR_PK_STUDENT_DISBURSEMENT_" + DELETE_DISP_ID).remove();
					calc_total(1)
				}
			}).responseText;
		}
		$("#deleteBatchDetail").modal("hide");
	});
	}, 100);
	
}

function get_detail() {
	jQuery(document).ready(function($) {
		var data = 'pms=<?= $_GET['id'] ?>';
		var value = $.ajax({
			url: "ajax_get_batch_payment_detail",
			type: "POST",
			data: data,
			async: false,
			cache: false,
			success: function(data) {
				document.getElementById('student_div').innerHTML = data
				$('#disbursement_table').bootstrapTable({})

				document.getElementById('TOTAL_AMOUNT').value = document.getElementById('TOTAL_AMOUNT_1').value
			}
		}).responseText;
	});
}

function format_val() {
	var AMOUNT = document.getElementById('AMOUNT').value
	if (AMOUNT != '') {
		AMOUNT = parseFloat(AMOUNT)
		document.getElementById('AMOUNT').value = AMOUNT.toFixed(2);
	}
}

function set_required(id) {
	if (document.getElementById('PK_STUDENT_DISBURSEMENT_' + id).checked == true) {

		document.getElementById('PAID_AMOUNT_' + id).className = 'form-control required-entry';
	} else {
		document.getElementById('PAID_AMOUNT_' + id).className = 'form-control';
	}
}

function change_disb(str, old_id, table_id) {
	jQuery(document).ready(function($) {
		var DELETE_BATCH_ID = $("#PK_PAYMENT_BATCH_DETAIL_" + old_id).val()

		/*if(DELETE_BATCH_ID != '') {
			var data = '<input type="hidden" name="DELETE_PK_PAYMENT_BATCH_DETAIL[]" value="'+DELETE_BATCH_ID+'" >';
			$('#delete_div').append(data)
		}
		$('#TR_PK_STUDENT_DISBURSEMENT_'+old_id).remove();*/

		$('#' + table_id + ' tbody').append(str)

		calc_total(1);

		var PK_STUDENT_DISBURSEMENT = document.getElementsByName('PK_STUDENT_DISBURSEMENT[]')
		for (var i = 0; i < PK_STUDENT_DISBURSEMENT.length; i++) {
			if (PK_STUDENT_DISBURSEMENT[i].checked == true)
				set_required(PK_STUDENT_DISBURSEMENT[i].value)
		}
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
	if(document.getElementById('UNPOST_BTN')){ // DIAM-1423
		document.getElementById('UNPOST_BTN').disabled = false;
	}
	document.getElementById('CANCEL_BTN').disabled = false;
	document.getElementById('DOWNLOAD_BTN').disabled = false;
	document.getElementById('DOWNLOAD_REPORT').disabled = false;
	document.getElementById('CREATE_STUDENT_NOTES').disabled = false; // DIAM-1423
	document.querySelectorAll('.pk_stud_enrol').forEach(el => el.disabled = false);  // DIAM-1423
}

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

function set_check_no() {
	var CHECK_NO = document.getElementById('CHECK_NO').value
	var STUD_CHECK_NO = document.getElementsByClassName('STUD_CHECK_NO')
	if (CHECK_NO != '') {
		for (var i = 0; i < STUD_CHECK_NO.length; i++) {
			if (STUD_CHECK_NO[i].value == '') {
				STUD_CHECK_NO[i].value = CHECK_NO;
			}
		}
	}
}


function set_trans_date() {
	var DATE_RECEIVED = document.getElementById('DATE_RECEIVED').value
	var TRANSACTION_DATE = document.getElementsByClassName('TRANSACTION_DATE')
	//if(DATE_RECEIVED != '') {
	for (var i = 0; i < TRANSACTION_DATE.length; i++) {
		//if(TRANSACTION_DATE[i].value == '') {
		TRANSACTION_DATE[i].value = DATE_RECEIVED;
		//}
	}
	//}
}

function set_batch_date() {
	document.getElementById('DATE_RECEIVED').classList.add("required-entry");
	// jQuery('.nullable_on_batch_select').each(function() 
	// {
	// 	 this.value = '';
	// });
	var DATE_RECEIVED = document.getElementById('DATE_RECEIVED').value
	var TRANSACTION_DATE = document.getElementsByClassName('TRANSACTION_DATE')
	for (var i = 0; i < TRANSACTION_DATE.length; i++) {
		TRANSACTION_DATE[i].value = DATE_RECEIVED;
	}
}

function set_disbursement_date() {
	//document.getElementById('DATE_RECEIVED').value = '';
	document.getElementById('DATE_RECEIVED').classList.remove("required-entry");

	var PK_STUDENT_DISBURSEMENT = document.getElementsByName('PK_STUDENT_DISBURSEMENT[]')
	for (var i = 0; i < PK_STUDENT_DISBURSEMENT.length; i++) {
		var id = PK_STUDENT_DISBURSEMENT[i].value;
		var DISBURSEMENT_DT = document.getElementById('DISBURSEMENT_DT_' + id).value;
		if (DISBURSEMENT_DT != '') {
			document.getElementById('BATCH_TRANSACTION_DATE_' + id).value = DISBURSEMENT_DT;
		}
	}

}
//29 june 2023
function show_only_selected(){
		//RUN DELETE ONLY IF ANY SINGLE IS SELECTED  
		//alert(jQuery(".delete_if_not_selected:checked").length);
		if( jQuery(".delete_if_not_selected:checked").length> 0)
		{
			jQuery(".delete_if_not_selected:not(:checked)").parent().parent().parent().remove();
		} 
	}

</script>

<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>

<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#BATCH_PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?= CAMPUS ?>',
			nonSelectedText: '<?= CAMPUS ?>',
			numberDisplayed: 1,
			nSelectedText: '<?= CAMPUS ?> selected'
		});
		$('#PK_AR_LEDGER_CODE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?= LEDGER_CODES ?>',
			nonSelectedText: '<?= LEDGER_CODES ?>',
			numberDisplayed: 1,
			nSelectedText: '<?= LEDGER_CODES ?> selected'
		});
	});
</script>

</body>

</html>
