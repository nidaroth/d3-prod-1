<? require_once("../global/config.php"); 

require_once("../global/mail.php");
require_once("../global/texting.php");

require_once("../language/common.php");
require_once("../language/student.php");
require_once("check_access.php");
require_once("function_student_ledger.php");

require_once("../global/payments_stax.php");
require_once("function_student_ledger.php");

$msg = "";
$res_pay = $db->Execute("select ENABLE_DIAMOND_PAY, CHARGE_PROCESSING_FEE_FROM_STUDENT from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

/*if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 0) {
	header("location:../index");
	exit;
} */
$CHARGE_PROCESSING_FEE_FROM_STUDENT = $res_pay->fields['CHARGE_PROCESSING_FEE_FROM_STUDENT'];

$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
if($FINANCE_ACCESS != 2 && $FINANCE_ACCESS != 3){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$data['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
	$data['PK_STUDENT_MASTER'] 		= $_GET['id'];
	$data['PK_STUDENT_ENROLLMENT'] 	= $_GET['eid'];
	$data['COMMENTS'] 				= $_POST['COMMENTS'];
	$data['PAYMENT_MODE'] 			= $_POST['PAYMENT_MODE_1']; // DIAM-1090
	$data['AY'] 					= $_POST['BATCH_AY'];
	$data['AP'] 					= $_POST['BATCH_AP'];
	$data['PK_TERM_BLOCK'] 			= $_POST['BATCH_PK_TERM_BLOCK']; // DIAM-1090
	$data['PRIOR_YEAR'] 			= $_POST['PRIOR_YEAR']; // DIAM-1090
	$data['PK_AR_PAYMENT_TYPE'] 	= $_POST['PK_AR_PAYMENT_TYPE']; // DIAM-1090
	$data['PK_AR_FEE_TYPE'] 		= $_POST['PK_AR_FEE_TYPE']; // DIAM-1090
	$data['BATCH_DETAIL_DESCRIPTION'] = $_POST['BATCH_DETAIL_DESCRIPTION']; // DIAM-1090
	$data['TRANS_DATE'] 			= $_POST['TRANS_DATE'];
	$data['TYPE'] 					= 'misc';
	
	if($_POST['PAYMENT_MODE'] == 1)
		$REF_NO = $_POST['CHECK_NO'];
	else if($_POST['PAYMENT_MODE'] == 3)
		$REF_NO = $_POST['MO_NO'];
	else if($_POST['PAYMENT_MODE'] == 5)
		$REF_NO = $_POST['CC_INFO_MANUAL'];
	
	$AMOUNT = 0;
	foreach($_POST['BATCH_CREDIT'] as $BATCH_CREDIT){
		$AMOUNT += $BATCH_CREDIT;
	}
	
	$data['PK_AR_LEDGER_CODE'] 		= $_POST['BATCH_PK_AR_LEDGER_CODE'];
	$data['DEBIT'] 					= $_POST['BATCH_DEBIT'];
	$data['CREDIT'] 				= $_POST['BATCH_CREDIT'];
	$data['AMOUNT'] 				= $AMOUNT;
	$data['REF_NUMBER'] 			= $REF_NO;
	
	// if($_POST['NEW_CARD_ID'] != 0)
	// {
	// 	$data['PK_STUDENT_CREDIT_CARD'] = $_POST['NEW_CARD_ID'];
	// }	

	if($_POST['PK_STUDENT_CREDIT_CARD_STAX'] == -1)
	{
		$data['PK_STUDENT_CREDIT_CARD'] = $_POST['NEW_CARD_ID'];
	}
	else
	{
		$data['PK_STUDENT_CREDIT_CARD'] = $_POST['PK_STUDENT_CREDIT_CARD_STAX'];
	}

	//echo "<pre>";print_r($data);exit;
	$pn_res = make_payment_stax($data);
	
	if($pn_res['STATUS'] == 1) { ?>
		<!-- <script type="text/javascript">window.opener.go_to_misc_batch(this,<?=$pn_res['PK_MISC_BATCH_MASTER'] ?>)</script> -->
		<script type="text/javascript">window.opener.go_to_student_page(this)</script> <!-- DIAM-1090 -->
	<? } else {
		$msg = $pn_res['MSG'];
	}
} 

// DIAM-1090
if ($_GET['id'] == '') 
{
	$FIRST_NAME 				= '';
	$LAST_NAME 					= '';
	$MIDDLE_NAME	 			= '';
	$STUDENT_ID					= '';
	$HEADER_CAMPUS_CODE			= '';
	$student_current_balance 	= '';
}
else
{

	$res = $db->Execute("SELECT  * FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if ($res->RecordCount() == 0) {
		//echo "DEBUG MS 3";exit;
		header("location:manage_student?t=" . $_GET['t']);
		exit;
	}

	$FIRST_NAME 				= $res->fields['FIRST_NAME'];
	$LAST_NAME 					= $res->fields['LAST_NAME'];
	$MIDDLE_NAME	 			= $res->fields['MIDDLE_NAME'];

	$res = $db->Execute("SELECT * FROM S_STUDENT_ACADEMICS WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

	$STUDENT_ID					= $res->fields['STUDENT_ID'];

	$res = $db->Execute("SELECT CAMPUS_CODE, S_CAMPUS.PK_CAMPUS  FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT > 0 ");
	$HEADER_CAMPUS_CODE = $res->fields['CAMPUS_CODE'];

	$current_balance = $_SESSION['student_ledger_balance'];

	if($current_balance < 0)
	{
		$color = 'color:red;';
	}
	else{
		$color = '';
	}

	$student_current_balance = '$'.number_format_value_checker($current_balance,2);

}
// End DIAM-1090

// Stax Web Token API Key
$res_card_x 	= $db->Execute("SELECT SITE_KEY FROM S_STAX_X_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$SITE_KEY 		= $res_card_x->fields['SITE_KEY'];
// End Stax Web Token API Key
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
	<title><?=QUICK_PAYMENT?> | <?=$title?></title>
	<style>
		.no-records-found{display:none;}
		input::-webkit-outer-spin-button,
		input::-webkit-inner-spin-button {
		  -webkit-appearance: none;
		  margin: 0;
		}

		/* Firefox */
		input[type=number] {
		  -moz-appearance: textfield;
		}
	</style>
	<style>
	.alert-danger-errors {
		color: #77373d;
		background-color: #fae1e4;
		border-color: #f7d5d9;
	}
	.alert-errors {
		position: relative;
		padding: 0.75rem 1.25rem;
		margin-bottom: 1rem;
		border: 1px solid transparent;
			border-top-color: transparent;
			border-right-color: transparent;
			border-bottom-color: transparent;
			border-left-color: transparent;
		border-radius: 0.25rem;
	}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper" style="padding-top: 0;" >
            <div class="container-fluid">
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<form class="floating-labels" method="post" name="form1" id="form1" onsubmit="return false;" >
									<div class="row">
										<div class="col-md-12" style="background-color:#022561;color:#FFFFFF;height:41px;padding-top: 7px;font-size: 18px;" >
											<center><b><?=QUICK_BATCH ?></b><center>
										</div>
									</div>
									<br />
									<? if($msg != ''){ ?>
									<div class="row">
											<div class="col-12 col-sm-12 form-group" style="text-align:center;color:red">
												<b>Error: <?=$msg?></b>
											</div>
										</div>
									<? } ?>
									<div class="form-group row">
										<div class="col-12 col-sm-12 form-group" >

											<table data-mobile-responsive="true" width="100%">
												<tr>
													<td width="40%" style="background-color: #e9ecef;opacity: 1;padding: 10px;font-size: 18px;">
														<table width="100%">
															<tr><td>Student: </td><td><?= $LAST_NAME . ', ' . $FIRST_NAME . ' ' . $MIDDLE_NAME ?></td></tr>
															<tr><td>Student ID: </td><td><?=$STUDENT_ID; ?></td></tr>
															<tr><td>Campus: </td><td><?= $HEADER_CAMPUS_CODE; ?></td></tr>
														</table>
													</td>
													<td width="30%"></td>
													<td width="30%" >
														
														<table width="100%">
															<tr><td style="background-color: #e9ecef;opacity: 1;padding: 10px;font-size: 18px; vertical-align:top;">Current Balance: </td><td style="background-color: #e9ecef;opacity: 1;padding: 10px;font-size: 18px; vertical-align:top;<?=$color?>" ><?=$student_current_balance?></td></tr>
															<tr><td colspan="2">&nbsp;</td></tr>
															<tr><td colspan="2" align="right"><a href="javascript:void(0)" onclick="add_ledger()" title="Add Ledger" style="font-size: 27px;" ><i class="fa fa-plus-circle"></i></a></td></tr>
														</table>
													</td>
												</tr>
											</table>
											<br>
											<table data-toggle="table" data-mobile-responsive="true" class="table-striped" id="student_table" >
												<thead>
													<tr>
														<th ><?=LEDGER_CODE?></th>
														<th ><?=TRANS_DATE?></th>
														<th ><?=DEBIT?></th>
														<th ><?=CREDIT?></th>
														<th >Fee/Payment<br>Type</th>
														<th >Batch Description</th>
														<th >AY</th>
														<th >AP</th>
														<th ><?=TAB_ENROLLMENT?></th>
														<th ><?=TERM_BLOCK?></th>
														<th >Prior<br>Year</th>
														<th >
															
														</th>
													</tr>
												</thead>
												<tbody>
												</tbody>
												<tfoot>
													<tr>
														<th ></th>
														<th ><?=TOTAL?></th>
														<th ><div id="debit_total_div" style="text-align:right;" ></th>
														<th ><div id="credit_total_div" style="text-align:right;" ></th>
														<th ></th>
														<th ></th>
														<th ></th>
														<th ></th>
														<th ></th>
														<th ></th>
														<th ></th>
														<th ></th>
													</tr>
												</tfoot>
											</table>
										</div>
									</div>
									
									<div class="row" >
										<div class="col-md-4 col-sm-4 form-group">
											<textarea class="form-control required-entry" rows="1" name="COMMENTS" id="COMMENTS" ><?=$COMMENTS?>No</textarea>
											<span class="bar"></span>
											<label for="COMMENTS"><?=COMMENTS?></label>
										</div>
										<input type="hidden" value="" name="PAYMENT_MODE_1" id="PAYMENT_MODE_1" >
										<div class="col-md-3 col-sm-3 form-group" style="margin-top: 20px;display:none;">
											<select id="PAYMENT_MODE" name="PAYMENT_MODE" class="form-control required-entry" onchange="show_payment_fields(this.value)"  >

												<? $res_type = $db->Execute("select * from M_AR_PAYMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY AR_PAYMENT_TYPE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['AR_PAYMENT_TYPE'] ?>" ><?=$res_type->fields['AR_PAYMENT_TYPE']?></option>
												<?	$res_type->MoveNext();
												} ?>

												<!-- <option value="2">Cash</option>
												<option value="1">Check</option>
												<?
												//if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 1){ ?>
													<option value="4" selected >Credit Card/Visa</option>
												<? //} else { ?>
													<option value="5" >Credit Card</option>
												<? //} ?>
												<option value="3">Money Order</option> -->
											</select>
											<span class="bar"></span>
											<label for="PAYMENT_TYPE"><?=PAYMENT_TYPE?></label>
										</div>
										<div class="col-md-5 col-sm-5 form-group" style="display:none;margin-top: 20px;" id="CHECK_NO_DIV">
											<input type="text" id="CHECK_NO" name="CHECK_NO" value="" class="form-control required-entry" >
											<span class="bar"></span>
											<label for="CHECK_NO"><?=CHECK_NO?></label>
										</div>
										<div class="col-md-5 col-sm-5 form-group" style="display:none;margin-top: 20px;" id="MO_NO_DIV">
											<input type="text" id="MO_NO" name="MO_NO" value="" class="form-control required-entry">
											<span class="bar"></span>
											<label for="MO_NO"><?=MO_NO?></label>
										</div>
										<div class="col-md-5 col-sm-5 form-group" style="display:none;margin-top: 20px;" id="CC_INFO_MANUAL_DIV">
											<input type="text" id="CC_INFO_MANUAL" name="CC_INFO_MANUAL" value="" class="form-control">
											<span class="bar"></span>
											<label for="CC_INFO"><?=CC_INFO?></label>
										</div>

										<? 
										if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 2){ ?>
										<div class="col-md-7 col-sm-7" id="CC_FIELDS_1" style="display:none">

											<? $res_card = $db->Execute("select * from S_STUDENT_CREDIT_CARD_STAX WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER= '$_GET[id]'");
											$style 			= "";
											
											if($res_card->RecordCount() > 0) { 
												$style 			= "display:none;"; 
												 ?>
												<div class="row form-group" >
													<div class="col-md-12 align-self-center" >
														<select id="PK_STUDENT_CREDIT_CARD_STAX" name="PK_STUDENT_CREDIT_CARD_STAX" class="form-control required-entry" onchange="show_cc_field(this.value)" >
															<option value="" ></option>
															<option value="-1" >Add New</option>
															<? $res_type = $db->Execute("select * from S_STUDENT_CREDIT_CARD_STAX WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER= '$_GET[id]' ORDER BY PK_STUDENT_CREDIT_CARD_STAX DESC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_STUDENT_CREDIT_CARD_STAX'] ?>" ><?=$res_type->fields['CARD_NO'].' - '.$res_type->fields['NAME_ON_CARD']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span>
														<label for="PK_STUDENT_CREDIT_CARD_STAX"><?=CREDIT_CARD_NO?></label>
													</div>
												</div>
											<? } ?>
											
											<div id="CC_FIELDS" style="<?=$style?>width: 100%;" >			
												<div class="col-md-12 col-sm-12 form-group" >
													<input type="text" id="cardholder-first-name" name="cardholder-first-name" value="" class="form-control required-entry" >
													<span class="bar"></span>
													<label for="cardholder-first-name">First Name</label>
												</div>
												<div class="col-md-12 col-sm-12 form-group" >
													<input type="text" id="cardholder-last-name" name="cardholder-last-name" value="" class="form-control required-entry" >
													<span class="bar"></span>
													<label for="cardholder-last-name">Last Name</label>
												</div>
												<div class="col-md-12 col-sm-12 form-group" >
													<input type="text" id="phone" name="phone" value="" class="form-control required-entry" >
													<span class="bar"></span>
													<label for="phone">Phone (Optional)</label>
												</div>
												<div class="col-12 col-sm-12 form-group">
													<input type="text" class="form-control required-entry" id="address" value="" name="address" placeholder=""  >
													<span class="bar"></span> 
													<label for="address">Address 1</label>
												</div>
												<div class="col-12 col-sm-12 form-group">
													<input type="text" class="form-control required-entry" id="address_2" value="" name="address_2" placeholder=""  >
													<span class="bar"></span> 
													<label for="address_2">Address 2 (Optional)</label>
												</div>
												<div class="col-12 col-sm-12 form-group">
													<input type="text" class="form-control required-entry" id="city" value="" name="city" placeholder=""  >
													<span class="bar"></span> 
													<label for="city">City</label>
												</div>
												<div class="col-12 col-sm-12 form-group">
													<input type="text" class="form-control required-entry" id="state" value="" name="state" placeholder=""  >
													<span class="bar"></span> 
													<label for="state">State</label>
												</div>
												<div class="col-12 col-sm-12 form-group">
													<input type="text" class="form-control required-entry" id="zipcode" value="" onkeypress="return check_number_validation(event);" onchange="check_number_val(this);" name="zipcode" placeholder=""  >
													<span class="bar"></span> 
													<label for="zipcode">Zipcode</label>
												</div>
												<div class="col-12 col-sm-12 form-group">
													<input type="text" class="form-control required-entry" id="country" value="" name="country" placeholder=""  >
													<span class="bar"></span> 
													<label for="country">Country (Optional)</label>
												</div>
												
												<div class="d-flex">
													<!-- <div class="col-12 col-sm-9 form-group" id="CREDIT_CARD_NO_DIV">
														<div style="width:70%; height:35px; display: inline-block;" >
															<input type="text" class="form-control required-entry" id="card_number" name="card_number" value="" placeholder=""  >
															<span class="bar"></span> 
															<label for="card_number">Card</label>
														</div>
														<div style="width:72px; height:35px; display: inline-block;">
															<input name="cvv" id="cvv" value="" class="form-control required-entry" size="5" maxlength="3" placeholder="CVV" style="width: 43px; height:18px; border-radius: 3px; border: 1px solid #ccc; padding: .5em .5em; font-size: 91%;">
														</div>
													</div> -->
													<div id="card-element" class="col-12 col-sm-7 form-group">
													    <div style="width:50px; height:35px; display: inline-block; margin:3px;color: #0e79e5;">Card</div>
														<div id="staxjs-number" style="width:180px; height:35px; display: inline-block; margin:3px;border-bottom: 1px solid #e9ecef;"></div>
														<div id="staxjs-cvv" style="width:50px; height:35px; display: inline-block; margin:3px"></div>
													</div>
													<div class="col-12 col-sm-3 form-group" style="text-align: right;" id="EXP_DATE_DIV">
														<div style="width:40px; height:35px; display: inline-block;">
															<input name="month" id="month" value="" class="form-control required-entry" onkeypress="return check_number_validation(event);" onchange="check_number_val(this);" size="3" maxlength="2" placeholder="MM" style="width: 38px; height:18px; border-radius: 3px; border: 1px solid #ccc; padding: .5em .5em; font-size: 91%;">
														</div>
														&nbsp;/&nbsp;
														<div style="width:55px; height:35px; display: inline-block;padding: 0 8px 0 0">
															<input name="year" id="year" value="" class="form-control required-entry" onkeypress="return check_number_validation(event);" onchange="check_number_val(this);" size="5" maxlength="4" placeholder="YYYY" style="width: 47px; height:18px; border-radius: 3px; border: 1px solid #ccc; padding: .5em .5em; font-size: 91%">
														</div>
													</div>
												</div>
												<div class="alert-errors alert-danger-errors" role="alert" id="errors" style="display: none;" ></div>	
												
											</div>
										</div>
										
										<? } ?>
									</div>
															
									<div class="row">
										<div class="col-md-12 text-right">
											
												<? //Ticket # 1081
												if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 2){ ?>
													<input type="hidden" name="response" id="response" value="" >
													<input type="hidden" id="mode" value="auth">
													<input type="hidden" id="convert" value="underscores">
													<input type="hidden" id="get_customer_id" value="">
													
													<input type="hidden" id="card_amount" value="0" >
													<input type="hidden" id="NEW_CARD_ID" name="NEW_CARD_ID" value="0" >
														
													<button id="PAY_BTN" style="display: none;" type="button" onClick="validate_payment_direct();" class="btn waves-effect waves-light btn-info" style="display:none" >Process Payment and Post to Ledger</button>
												
													<button id="ADD_CARD_BTN" type="button" class="btn waves-effect waves-light btn-info validate_payment_direct" onClick="validate_payment_form(2);" >Process Payment and Post to Ledger</button>
													
													<button id="BTN_SAVE" type="button" class="btn waves-effect waves-light btn-info" onClick="validate_payment_form(3);" >Post To Ledger</button>
												<? } else { ?>
													<button id="BTN_SAVE" type="button" class="btn waves-effect waves-light btn-info" onClick="validate_payment_form(3);" >Post To Ledger</button>
												<? } ?>
												
												<button id="BTN_CANCEL" onclick="javascript:window.close()" type="button" class="btn waves-effect waves-light btn-dark" ><?=CANCEL?></button>
											
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
		
		<div class="modal" id="feeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=CONFIRMATION?></h4>
					</div>
					<div class="modal-body">
						<div class="form-group" id="FEE_DIV" >
							
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_payment(1)" class="btn waves-effect waves-light btn-info"><?=PROCEED?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_payment(0)" ><?=NO?></button>
					</div>
				</div>
			</div>
		</div>
    </div>
   
	<? require_once("js.php"); ?>

	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<!-- <script src="https://staxjs.staxpayments.com/stax.js?nocache=2"></script> -->
	<script type="text/javascript" src="../assets/js/stax.js"></script> <!-- DIAM-2347 -->

	<script language="javascript">
		
	// StaxX Start	
	jQuery(document).ready(function($) {

		var payButton = document.querySelector('#ADD_CARD_BTN');
		var errorElement   = document.querySelector('#errors');
		// Init StaxJs SDK
		var staxJs = new StaxJs('<?=$SITE_KEY?>', {
			number: {
				id: 'staxjs-number',
				placeholder: '0000 0000 0000 0000',
				type: 'text',
				style: 'height: 30px; width: 100%; font-size: 15px;',
				format: 'prettyFormat'
			},
			cvv: {
				id: 'staxjs-cvv',
				placeholder: 'CVV',
				style: 'width: 33px; height:90%; border-radius: 3px; border: 1px solid #ccc; padding: .5em .5em; font-size: 91%;'
			}
		});

		// tell staxJs to load in the card fields
		staxJs.showCardForm().then(handler => {
			console.log('form loaded');

			// for testing!
			handler.setTestPan('');
			handler.setTestCvv('');
			var form = document.querySelector('form');
			form.querySelector('input[name=month]').value = document.getElementById('month').value;
			form.querySelector('input[name=year]').value = document.getElementById('year').value;
			form.querySelector('input[name=city]').value = document.getElementById('city').value;
			form.querySelector('input[name=zipcode]').value = document.getElementById('zipcode').value;
			form.querySelector('input[name=cardholder-first-name]').value = document.getElementById('cardholder-first-name').value;
			form.querySelector('input[name=cardholder-last-name]').value = document.getElementById('cardholder-last-name').value;
		})
		.catch(err => {
			console.log('error init form ' + err);
			// reinit form
		});

		staxJs.on('card_form_complete', (message) => {
			// activate pay button
			payButton.disabled = false;
			console.log(message);
		});

		staxJs.on('card_form_uncomplete', (message) => {
			// deactivate pay button
			payButton.disabled = true;
			console.log(message);
		});

		$(".validate_payment_direct").on('click', function(event)
		{
			flag = 1;
			if(document.getElementById('cardholder-first-name')){
				if(document.getElementById('cardholder-first-name').value == ''){
					flag = 0;
					$("#cardholder-first-name").addClass("validation-failed")
					$("#advice-required-entry-cardholder-first-name").remove()
					$("#cardholder-first-name").parent().append('<div class="validation-advice" id="advice-required-entry-cardholder-first-name'+'" style="">This is a required field.</div>')
				} else {
					$("#cardholder-first-name").removeClass("validation-failed")
					$("#advice-required-entry-cardholder-first-name").remove()
				}
			}

			if(document.getElementById('cardholder-last-name')){
				if(document.getElementById('cardholder-last-name').value == ''){
					flag = 0;
					$("#cardholder-last-name").addClass("validation-failed")
					$("#advice-required-entry-cardholder-last-name").remove()
					$("#cardholder-last-name").parent().append('<div class="validation-advice" id="advice-required-entry-cardholder-last-name'+'" style="">This is a required field.</div>')
				} else {
					$("#cardholder-last-name").removeClass("validation-failed")
					$("#advice-required-entry-cardholder-last-name").remove()
				}
			}

			if(document.getElementById('address')){
				if(document.getElementById('address').value == ''){
					flag = 0;
					$("#address").addClass("validation-failed")
					$("#advice-required-entry-address").remove()
					$("#address").parent().append('<div class="validation-advice" id="advice-required-entry-address'+'" style="">This is a required field.</div>')
				} else {
					$("#address").removeClass("validation-failed")
					$("#advice-required-entry-address").remove()
				}
			}

			if(document.getElementById('city')){
				if(document.getElementById('city').value == ''){
					flag = 0;
					$("#city").addClass("validation-failed")
					$("#advice-required-entry-city").remove()
					$("#city").parent().append('<div class="validation-advice" id="advice-required-entry-city'+'" style="">This is a required field.</div>')
				} else {
					$("#city").removeClass("validation-failed")
					$("#advice-required-entry-city").remove()
				}
			}

			if(document.getElementById('state')){
				if(document.getElementById('state').value == ''){
					flag = 0;
					$("#state").addClass("validation-failed")
					$("#advice-required-entry-state").remove()
					$("#state").parent().append('<div class="validation-advice" id="advice-required-entry-state'+'" style="">This is a required field.</div>')
				} else {
					$("#state").removeClass("validation-failed")
					$("#advice-required-entry-state").remove()
				}
			}

			if(document.getElementById('zipcode')){
				if(document.getElementById('zipcode').value == ''){
					flag = 0;
					$("#zipcode").addClass("validation-failed")
					$("#advice-required-entry-zipcode").remove()
					$("#zipcode").parent().append('<div class="validation-advice" id="advice-required-entry-zipcode'+'" style="">This is a required field.</div>')
				} else {
					$("#zipcode").removeClass("validation-failed")
					$("#advice-required-entry-zipcode").remove()
				}
			}

			if(flag == 1) 
			{

				// document.querySelector('#ADD_CARD_BTN').onclick = () => 
				// {

					var AMOUNT = 0;
					var BATCH_CREDIT = document.getElementsByName('BATCH_CREDIT[]')
					for(var i = 0 ; i < BATCH_CREDIT.length ; i++){
						if(BATCH_CREDIT[i].value != '')
							AMOUNT = parseFloat(AMOUNT) + parseFloat(BATCH_CREDIT[i].value)
					}
					AMOUNT = parseFloat(AMOUNT);

					var form = document.querySelector('form');
					var extraDetails = {
						total: AMOUNT, // 1$
						firstname: form.querySelector('input[name=cardholder-first-name]').value,
						lastname: form.querySelector('input[name=cardholder-last-name]').value,
						company: '',
						email: '',
						month: form.querySelector('input[name=month]').value,
						year: form.querySelector('input[name=year]').value,
						phone: form.querySelector('input[name=phone]').value,
						address_1: form.querySelector('input[name=address]').value,
						address_2: form.querySelector('input[name=address_2]').value,
						address_city: form.querySelector('input[name=city]').value,
						address_state: form.querySelector('input[name=state]').value,
						address_zip: form.querySelector('input[name=zipcode]').value,
						address_country: form.querySelector('input[name=country]').value,
						//url: "https://app.staxpayments.com/#/bill/",
						url: "https://docs.staxpayments.com/staxjs/",
						method: 'card',
						// validate is optional and can be true or false. 
						// determines whether or not stax.js does client-side validation.
						// the validation follows the sames rules as the api.
						// check the api documentation for more info:
						// https://staxpayments.com/api-documentation/
						validate: false,
						// meta is optional and each field within the POJO is optional also
						meta: {
							reference: 'invoice-reference-num',// optional - will show up in emailed receipts
							memo: 'notes about this transaction',// optional - will show up in emailed receipts
							otherField1: 'other-value-1', // optional - we don't care
							otherField2: 'other-value-2', // optional - we don't care
							subtotal: 1, // optional - will show up in emailed receipts
							tax: 0, // optional - will show up in emailed receipts
							lineItems: [ // optional - will show up in emailed receipts
								{"id": "optional-fm-catalog-item-id", "item":"Demo Item","details":"this is a regular, demo item","quantity":10,"price":.1}
							] 
						}
					};
					
					console.log(extraDetails);

					// call pay api
					staxJs.pay(extraDetails).then((result) => {

						console.log("invoice object:", result);
						console.log("transaction object:", result.child_transactions[0]);

						var customer_id       = result.customer_id;
						var card_type         = result.child_transactions[0].payment_method.card_type;
						var card_exp    	  = result.child_transactions[0].payment_method.card_exp;
						var card_name   	  = result.customer.firstname+' '+result.customer.lastname;
						var payment_method_id = result.payment_method_id;
						var card_last_four    = result.child_transactions[0].payment_method.card_last_four;
						var address_zip    	  = result.child_transactions[0].payment_method.address_zip;
						
						data = 's_id=<?=$_GET['id']?>&customer_id='+customer_id+'&card_type='+card_type+'&card_exp='+card_exp+'&card_name='+card_name+'&payment_method_id='+payment_method_id+'&card_last_four='+card_last_four+'&address_zip='+address_zip;

						var value = $.ajax({
							url: "add_card_stax.php",	
							type: "POST",		 
							data: data,		
							async: false,
							cache: false,
							success: function (data) {	
								//alert(data)
								data = data.split("|||")
								if(data[0] == 1) {
									document.getElementById('NEW_CARD_ID').value = data[1]

									enableForm(document.form1)
									document.form1.submit();
								} else
									alert(data[1])
							}		
						}).responseText;
						
					})
					.catch(err => {
						// err can contain an object where each key is a field name that points to an array of errors
						// such as {phone_number: ['The phone number is invalid']}
						errorElement.textContent = typeof err === 'object' ? err.message || Object.keys(err).map((k) => err[k].join(' ')).join(' ') : JSON.stringify(err);
						// errorElement.classList.add('visible');
						// loaderElement.classList.remove('visible');
						document.getElementById("errors").style.display = 'block';
					});
				// }
			}
			else{
				return false;
			}

		});
		add_ledger()
	});
	// StaxX End

	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});

	function date_picker()
	{
		jQuery(document).ready(function($) { 
			$(".date").datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
		});
	}

	function validate_payment_form(type) {
		jQuery(document).ready(function($) {

			flag = 1;

			$(".date").datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});

			var idNumber = 1;
			var BATCH_PK_AR_LEDGER_CODE = document.getElementsByName('BATCH_PK_AR_LEDGER_CODE[]');
			for (var i = 0; i < BATCH_PK_AR_LEDGER_CODE.length; i++) {

				if (BATCH_PK_AR_LEDGER_CODE[i].value == '') {
					flag = 0;
					$("#BATCH_PK_AR_LEDGER_CODE_" + idNumber).addClass("validation-failed")
					$("#advice-required-entry-BATCH_PK_AR_LEDGER_CODE_" + idNumber).remove()
					$("#BATCH_PK_AR_LEDGER_CODE_" + idNumber).parent().append('<div class="validation-advice" id="advice-required-entry-BATCH_PK_AR_LEDGER_CODE_'+idNumber+'" style="">This is a required field.</div>')
				} else {
					$("#BATCH_PK_AR_LEDGER_CODE_"+ idNumber).removeClass("validation-failed")
					$("#advice-required-entry-BATCH_PK_AR_LEDGER_CODE_"+ idNumber).remove()
				}
				idNumber++;
			}

			var idTransNumber = 1;
			var TRANS_DATE = document.getElementsByName('TRANS_DATE[]');
			for (var i = 0; i < TRANS_DATE.length; i++) {

				if (TRANS_DATE[i].value == '') {
					flag = 0;
					$("#TRANS_DATE_" + idTransNumber).addClass("validation-failed")
					$("#advice-required-entry-TRANS_DATE_" + idTransNumber).remove()
					$("#TRANS_DATE_" + idTransNumber).parent().append('<div class="validation-advice" id="advice-required-entry-TRANS_DATE_'+idTransNumber+'" style="">This is a required field.</div>')
				} else {
					$("#TRANS_DATE_"+ idTransNumber).removeClass("validation-failed")
					$("#advice-required-entry-TRANS_DATE_"+ idTransNumber).remove()
				}
				idTransNumber++;
			}

			var idBatchNumber = 1;
			var BATCH_DETAIL_DESCRIPTION = document.getElementsByName('BATCH_DETAIL_DESCRIPTION[]');
			for (var i = 0; i < BATCH_DETAIL_DESCRIPTION.length; i++) {

				if (BATCH_DETAIL_DESCRIPTION[i].value == '') {
					flag = 0;
					$("#BATCH_DETAIL_DESCRIPTION_" + idBatchNumber).addClass("validation-failed")
					$("#advice-required-entry-BATCH_DETAIL_DESCRIPTION_" + idBatchNumber).remove()
					$("#BATCH_DETAIL_DESCRIPTION_" + idBatchNumber).parent().append('<div class="validation-advice" id="advice-required-entry-BATCH_DETAIL_DESCRIPTION_'+ idBatchNumber+'" style="">This is a required field.</div>')
				} else {
					$("#BATCH_DETAIL_DESCRIPTION_"+ idBatchNumber).removeClass("validation-failed")
					$("#advice-required-entry-BATCH_DETAIL_DESCRIPTION_"+ idBatchNumber).remove()
				}
				idBatchNumber++;
			}

			var idAYNumber = 1;
			var BATCH_AY = document.getElementsByName('BATCH_AY[]');
			for (var i = 0; i < BATCH_AY.length; i++) {

				if (BATCH_AY[i].value == '') {
					flag = 0;
					$("#BATCH_AY_" + idAYNumber).addClass("validation-failed")
					$("#advice-required-entry-BATCH_AY_" + idAYNumber).remove()
					$("#BATCH_AY_" + idAYNumber).parent().append('<div class="validation-advice" id="advice-required-entry-BATCH_AY_'+ idAYNumber+'" style="">This is a required field.</div>')
				} else {
					$("#BATCH_AY_"+ idAYNumber).removeClass("validation-failed")
					$("#advice-required-entry-BATCH_AY_"+ idAYNumber).remove()
				}
				idAYNumber++;
			}

			var idAPNumber = 1;
			var BATCH_AP = document.getElementsByName('BATCH_AP[]');
			for (var i = 0; i < BATCH_AP.length; i++) {

				if (BATCH_AP[i].value == '') {
					flag = 0;
					$("#BATCH_AP_" + idAPNumber).addClass("validation-failed")
					$("#advice-required-entry-BATCH_AP_" + idAPNumber).remove()
					$("#BATCH_AP_" + idAPNumber).parent().append('<div class="validation-advice" id="advice-required-entry-BATCH_AP_'+ idAPNumber+'" style="">This is a required field.</div>')
				} else {
					$("#BATCH_AP_"+ idAPNumber).removeClass("validation-failed")
					$("#advice-required-entry-BATCH_AP_"+ idAPNumber).remove()
				}
				idAPNumber++;
			}

			var idENRNumber = 1;
			var BATCH_PK_STUDENT_ENROLLMENT = document.getElementsByName('BATCH_PK_STUDENT_ENROLLMENT[]');
			for (var i = 0; i < BATCH_PK_STUDENT_ENROLLMENT.length; i++) {

				if (BATCH_PK_STUDENT_ENROLLMENT[i].value == '') {
					flag = 0;
					$("#BATCH_PK_STUDENT_ENROLLMENT_" + idENRNumber).addClass("validation-failed")
					$("#advice-required-entry-BATCH_PK_STUDENT_ENROLLMENT_" + idENRNumber).remove()
					$("#BATCH_PK_STUDENT_ENROLLMENT_" + idENRNumber).parent().append('<div class="validation-advice" id="advice-required-entry-BATCH_PK_STUDENT_ENROLLMENT_'+ idENRNumber+'" style="">This is a required field.</div>')
				} else {
					$("#BATCH_PK_STUDENT_ENROLLMENT_"+ idENRNumber).removeClass("validation-failed")
					$("#advice-required-entry-BATCH_PK_STUDENT_ENROLLMENT_"+ idENRNumber).remove()
				}
				idENRNumber++;
			}

			var idPYNumber = 1;
			var PRIOR_YEAR = document.getElementsByName('PRIOR_YEAR[]');
			for (var i = 0; i < PRIOR_YEAR.length; i++) {

				if (PRIOR_YEAR[i].value == '') {
					flag = 0;
					$("#PRIOR_YEAR_" + idPYNumber).addClass("validation-failed")
					$("#advice-required-entry-PRIOR_YEAR_" + idPYNumber).remove()
					$("#PRIOR_YEAR_" + idPYNumber).parent().append('<div class="validation-advice" id="advice-required-entry-PRIOR_YEAR_'+ idPYNumber+'" style="">This is a required field.</div>')
				} else {
					$("#PRIOR_YEAR_"+ idPYNumber).removeClass("validation-failed")
					$("#advice-required-entry-PRIOR_YEAR_"+ idPYNumber).remove()
				}
				idPYNumber++;
			}
			if (document.getElementById('CC_FIELDS_1').style.display !== "none") { //DIAM-2418

			if(document.getElementById('cardholder-first-name')){
				if(document.getElementById('cardholder-first-name').value == ''){
					flag = 0;
					$("#cardholder-first-name").addClass("validation-failed")
					$("#advice-required-entry-cardholder-first-name").remove()
					$("#cardholder-first-name").parent().append('<div class="validation-advice" id="advice-required-entry-cardholder-first-name'+'" style="">This is a required field.</div>')
				} else {
					$("#cardholder-first-name").removeClass("validation-failed")
					$("#advice-required-entry-cardholder-first-name").remove()
				}
			}

			if(document.getElementById('cardholder-last-name')){
				if(document.getElementById('cardholder-last-name').value == ''){
					flag = 0;
					$("#cardholder-last-name").addClass("validation-failed")
					$("#advice-required-entry-cardholder-last-name").remove()
					$("#cardholder-last-name").parent().append('<div class="validation-advice" id="advice-required-entry-cardholder-last-name'+'" style="">This is a required field.</div>')
				} else {
					$("#cardholder-last-name").removeClass("validation-failed")
					$("#advice-required-entry-cardholder-last-name").remove()
				}
			}

			if(document.getElementById('address')){
				if(document.getElementById('address').value == ''){
					flag = 0;
					$("#address").addClass("validation-failed")
					$("#advice-required-entry-address").remove()
					$("#address").parent().append('<div class="validation-advice" id="advice-required-entry-address'+'" style="">This is a required field.</div>')
				} else {
					$("#address").removeClass("validation-failed")
					$("#advice-required-entry-address").remove()
				}
			}

			if(document.getElementById('city')){
				if(document.getElementById('city').value == ''){
					flag = 0;
					$("#city").addClass("validation-failed")
					$("#advice-required-entry-city").remove()
					$("#city").parent().append('<div class="validation-advice" id="advice-required-entry-city'+'" style="">This is a required field.</div>')
				} else {
					$("#city").removeClass("validation-failed")
					$("#advice-required-entry-city").remove()
				}
			}

			if(document.getElementById('state')){
				if(document.getElementById('state').value == ''){
					flag = 0;
					$("#state").addClass("validation-failed")
					$("#advice-required-entry-state").remove()
					$("#state").parent().append('<div class="validation-advice" id="advice-required-entry-state'+'" style="">This is a required field.</div>')
				} else {
					$("#state").removeClass("validation-failed")
					$("#advice-required-entry-state").remove()
				}
			}

			if(document.getElementById('zipcode')){
				if(document.getElementById('zipcode').value == ''){
					flag = 0;
					$("#zipcode").addClass("validation-failed")
					$("#advice-required-entry-zipcode").remove()
					$("#zipcode").parent().append('<div class="validation-advice" id="advice-required-entry-zipcode'+'" style="">This is a required field.</div>')
				} else {
					$("#zipcode").removeClass("validation-failed")
					$("#advice-required-entry-zipcode").remove()
				}
			}

			} //DIAM-2347
			
			var BATCH_DEBIT = document.getElementsByName('BATCH_DEBIT[]')
			var DEBIT = 0;
			for(var i = 0 ; i < BATCH_DEBIT.length ; i++){
				if(BATCH_DEBIT[i].value != '')
					DEBIT = parseFloat(DEBIT) + parseFloat(BATCH_DEBIT[i].value)
			}
			DEBIT = parseFloat(DEBIT);
			
			var BATCH_CREDIT = document.getElementsByName('BATCH_CREDIT[]')
			var CREDIT = 0;
			for(var i = 0 ; i < BATCH_CREDIT.length ; i++){
				if(BATCH_CREDIT[i].value != '')
					CREDIT = parseFloat(CREDIT) + parseFloat(BATCH_CREDIT[i].value)
			}
			CREDIT = parseFloat(CREDIT);
			
			//if(CREDIT == 0 && DEBIT == 0){
				//flag = 0;
				// alert('Please Enter value in Credit or Debit Field');
				// return false;
				flag = 1; //DIAM-2347
			//}

			
			if(flag == 1) 
			{
				if(type == 3) 
				{
					enableForm(document.form1);
					document.form1.submit();
				} 
			}
		
		});
	}


	function validate_payment_direct() 
	{
		flag = 1;
		if(document.getElementById('PK_STUDENT_CREDIT_CARD_STAX')){
			if(document.getElementById('PK_STUDENT_CREDIT_CARD_STAX').value == ''){
				flag = 0;
				$("#PK_STUDENT_CREDIT_CARD_STAX").addClass("validation-failed")
				$("#advice-required-entry-PK_STUDENT_CREDIT_CARD_STAX").remove()
				$("#PK_STUDENT_CREDIT_CARD_STAX").parent().append('<div class="validation-advice" id="advice-required-entry-PK_STUDENT_CREDIT_CARD_STAX'+'" style="">This is a required field.</div>')
			} else {
				$("#PK_STUDENT_CREDIT_CARD_STAX").removeClass("validation-failed")
				$("#advice-required-entry-PK_STUDENT_CREDIT_CARD_STAX").remove()
			}
		}

		var AMOUNT = 0;
		var BATCH_CREDIT = document.getElementsByName('BATCH_CREDIT[]')
		for(var i = 0 ; i < BATCH_CREDIT.length ; i++){
			if(BATCH_CREDIT[i].value != '')
				AMOUNT = parseFloat(AMOUNT) + parseFloat(BATCH_CREDIT[i].value)
		}
		AMOUNT = parseFloat(AMOUNT);
		if(AMOUNT == 0){
			flag = 0;
			alert('Please Enter value in Credit Field');
			return false;

		}

		if(flag == 1) 
		{
			jQuery(document).ready(function($) 
			{
				var card_id = '';
				if(document.getElementById('PK_STUDENT_CREDIT_CARD_STAX')){
					card_id = document.getElementById('PK_STUDENT_CREDIT_CARD_STAX').value;
				}
				
				var value = $.ajax({
					url: "ajax_calc_stax_transaction_charge",	
					type: "POST",		 
					data: 'id='+card_id+'&transaction_initiation_type=MIT&amt='+AMOUNT,		
					async: false,
					cache: false,
					success: function (data) 
					{	
						if(data == 'success')
						{
							document.form1.submit();
						}
						else{
							alert(data);
						}

					}		
				}).responseText;
					
			});
		}
	}

	</script>
	
	<script type="text/javascript">
	function show_payment_fields(val){ // This function not useable as per new requirement

		<? 
		if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 2){ ?>
		document.getElementById('CC_FIELDS_1').style.display 		 = 'none'
		document.getElementById('card-element').style.display  = 'none'
		document.getElementById('EXP_DATE_DIV').style.display 		 = 'none'
		<? } ?>
		document.getElementById('MO_NO_DIV').style.display 			 = 'none'
		document.getElementById('CHECK_NO_DIV').style.display 		 = 'none' 
		document.getElementById('CC_INFO_MANUAL_DIV').style.display  = 'none' 
		
		if(val == 'Check'){
			document.getElementById('CHECK_NO_DIV').style.display = 'flex'
		} else if(val == 'Money Order'){
			document.getElementById('MO_NO_DIV').style.display = 'flex'
		} else if(val == 'Credit Card/Visa'){
			document.getElementById('CC_FIELDS_1').style.display  		 = 'flex'
			document.getElementById('card-element').style.display  = 'flex'
			document.getElementById('EXP_DATE_DIV').style.display 		 = 'flex'
		} else if(val == 5){
			document.getElementById('CC_INFO_MANUAL_DIV').style.display = 'flex'
		}
		
		
		if(val != 'Credit Card/Visa'){
			<? 
			if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 2){ ?>
			document.getElementById('BTN_SAVE').style.display 		= 'inline'
			document.getElementById('ADD_CARD_BTN').style.display 	= 'none'
			//document.getElementById('PAY_BTN').style.display 		= 'none'
			<? } ?>
		} else {
			document.getElementById('BTN_SAVE').style.display 		= 'none'
			
			if(document.getElementById('EXP_DATE_DIV').style.display == 'flex') {
				document.getElementById('ADD_CARD_BTN').style.display 	= 'inline'
				//document.getElementById('PAY_BTN').style.display 		= 'none'
			} else {
				document.getElementById('ADD_CARD_BTN').style.display 	= 'none'
				//document.getElementById('PAY_BTN').style.display 		= 'inline'
			}
			
		}
	}
	//DIAM-2347
	function removeDollarSign(amountString) {
    // Remove any leading or trailing whitespace
		amountString = amountString.trim();

		// Check if the string starts with a dollar sign
		if (amountString.startsWith('$')) {
			// Remove the dollar sign using substring
			amountString = amountString.substring(1);
		}

		// Parse the remaining string to a float (assuming it's a valid amount format)
		return parseFloat(amountString);
		
	}
	function Tochecktotalzero(log){
		var tot_credit = document.getElementById('credit_total_div').innerText;
		var tot_debit = document.getElementById('debit_total_div').innerText;
		//alert(log);
		if((removeDollarSign(tot_credit)<=0 && removeDollarSign(tot_debit)===0) || (removeDollarSign(tot_credit)===0 && removeDollarSign(tot_debit)<=0)){			
			//alert('dis');
			document.getElementById('ADD_CARD_BTN').style.display 	= 'inline';
			document.getElementById('PAY_BTN').style.display 		= 'none'
			return false;
		}else {

				if(log>0){
					return true;
				}
		}
	}	
	//DIAM-2347
	function show_cc_field(val){
		// document.getElementById('BTN_SAVE').style.display = 'none'
		
		if(val == -1) {
			document.getElementById('CC_FIELDS').style.display = 'block'
			
			document.getElementById('ADD_CARD_BTN').style.display 	= 'inline'
			document.getElementById('PAY_BTN').style.display 		= 'none'

			// if(val != -1)
			// {
			// 	get_paymet_method_details(val);
			// }
			// else{
			// 	document.getElementById('get_customer_id').value = '';
			// 	document.getElementById('cardholder-first-name').value = '';
			// 	document.getElementById('cardholder-last-name').value = '';
			// 	document.getElementById('phone').value = '';
			// 	document.getElementById('address').value = '';
			// 	document.getElementById('address_2').value = '';
			// 	document.getElementById('city').value = '';
			// 	document.getElementById('state').value = '';
			// 	document.getElementById('zipcode').value = '';
			// 	document.getElementById('country').value = '';
			// 	document.getElementById('month').value = '';
			// 	document.getElementById('year').value = '';	
			// }

		} else {
			
			document.getElementById('CC_FIELDS').style.display = 'none'
			if(Tochecktotalzero(val)){ 	//DIAM-2347
			document.getElementById('ADD_CARD_BTN').style.display 	= 'none'
			document.getElementById('PAY_BTN').style.display 		= 'inline'
			}//DIAM-2347

		}
	}

	function get_paymet_method_details(val)
	{
		var value = $.ajax({
			url: "ajax_get_paymet_method_details",	
			type: "POST",		 
			data: 'payment_method_id='+val,		
			async: false,
			cache: false,
			success: function (data) {	
				// alert(data);
				var objJSON = JSON.parse(data);
				document.getElementById('get_customer_id').value = objJSON.customer_id;
				document.getElementById('cardholder-first-name').value = objJSON.firstname;
				document.getElementById('cardholder-last-name').value = objJSON.lastname;
				document.getElementById('phone').value = objJSON.phone;
				document.getElementById('address').value = objJSON.address_1;
				document.getElementById('address_2').value = objJSON.address_2;
				document.getElementById('city').value = objJSON.address_city;
				document.getElementById('state').value = objJSON.address_state;
				document.getElementById('zipcode').value = objJSON.address_zip;
				document.getElementById('country').value = objJSON.address_country;
				document.getElementById('month').value = objJSON.month;
				document.getElementById('year').value = objJSON.year;
				// document.getElementById('card_number').value = objJSON.card_last_four;
			}		
		}).responseText;
	}
	
	function get_processing_fee(){
		jQuery(document).ready(function($) {
			var card_id = '';
			if(document.getElementById('PK_STUDENT_CREDIT_CARD')){
				if(document.getElementById('PK_STUDENT_CREDIT_CARD').value == -1)
					card_id = document.getElementById('NEW_CARD_ID').value
				else
					card_id = document.getElementById('PK_STUDENT_CREDIT_CARD').value
			} else
				card_id = document.getElementById('NEW_CARD_ID').value

			var AMOUNT = 0;
			var BATCH_CREDIT = document.getElementsByName('BATCH_CREDIT[]')
			for(var i = 0 ; i < BATCH_CREDIT.length ; i++){
				if(BATCH_CREDIT[i].value != '')
					AMOUNT = parseFloat(AMOUNT) + parseFloat(BATCH_CREDIT[i].value)
			}
			AMOUNT = parseFloat(AMOUNT);
	
			var value = $.ajax({
				url: "ajax_calc_cc_transaction_charge",	
				type: "POST",		 
				data: 'id='+card_id+'&amt='+AMOUNT,		
				async: false,
				cache: false,
				success: function (data) {	
					//alert(data)
					$("#feeModal").modal()
					document.getElementById('FEE_DIV').innerHTML = 'Amount of <b>$'+data+'</b> will be charged as Processing Fee.<br />Do you want to continue?'
				}		
			}).responseText;
				
		});
	}
	
	function conf_payment(val){
		jQuery(document).ready(function($) {
			if(val == 1) {
				enableForm(document.form1)
				document.form1.submit();
			}
			$("#feeModal").modal("hide");
		});
	}
	
	var student_count = 1;
	function add_ledger(){
		jQuery(document).ready(function($) { 
			var data  = 'student_count='+student_count
			var value = $.ajax({
				url: "ajax_quick_payment_detail",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					
					get_enrollment_det(<?=$_GET['id']?>,student_count); // DIAM-1090
					$('#student_table tbody').append(data);
					student_count++;

					calc_total(1)
				}		
			}).responseText;
		});
	}
		
	function delete_row(id){
		jQuery(document).ready(function($) {
			$("#misc_batch_detail_div_"+id).remove();
		});
	}
	
	function format_val(field,id){
		var AMOUNT = document.getElementById(field+'_'+id).value
		if(AMOUNT != '') {
			AMOUNT = parseFloat(AMOUNT)
			document.getElementById(field+'_'+id).value = AMOUNT.toFixed(2);
		}	
	}
	
	function calc_total(update_amt){
		var BATCH_DEBIT = document.getElementsByName('BATCH_DEBIT[]')
		var total = 0;
		for(var i = 0 ; i < BATCH_DEBIT.length ; i++){
			if(BATCH_DEBIT[i].value != '')
				total = parseFloat(total) + parseFloat(BATCH_DEBIT[i].value)
		}
		total = parseFloat(total);
		document.getElementById('debit_total_div').innerHTML = '$ '+total.toFixed(2)
		
		var BATCH_CREDIT = document.getElementsByName('BATCH_CREDIT[]')
		var total = 0;
		for(var i = 0 ; i < BATCH_CREDIT.length ; i++){
			if(BATCH_CREDIT[i].value != '')
				total = parseFloat(total) + parseFloat(BATCH_CREDIT[i].value)
		}
		total = parseFloat(total);
		document.getElementById('credit_total_div').innerHTML = '$ '+total.toFixed(2)
		
		//DIAM-2347
		if(document.getElementById('PK_STUDENT_CREDIT_CARD_STAX')){
			if(document.getElementById('PK_STUDENT_CREDIT_CARD_STAX').value!="" && document.getElementById('PK_STUDENT_CREDIT_CARD_STAX').value > 0){
				var st = document.getElementById('PK_STUDENT_CREDIT_CARD_STAX').value;
				show_cc_field(st);
			}
		}
		//DIAM-2347
	}
	
	function get_ledger_type(val,id){
		document.getElementById('BATCH_DEBIT_'+id).value  = 0
		document.getElementById('BATCH_CREDIT_'+id).value = 0
		calc_total(1)
		jQuery(document).ready(function($) {
			var data  = 'val='+val;
			var value = $.ajax({
				url: "ajax_get_ledger_type",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					if(data == 2) {

						document.getElementById('BATCH_DEBIT_'+id).disabled  = false
						document.getElementById('BATCH_CREDIT_'+id).disabled = true
					} else if(data == 1) {
						document.getElementById('BATCH_DEBIT_'+id).disabled  = true
						document.getElementById('BATCH_CREDIT_'+id).disabled = false
					}
					//document.getElementById('SSN_DIV_'+id).innerHTML = data
				}		
			}).responseText;
		});
	}

	// DIAM-1090
	function get_fee_payment_type(val,id){
		jQuery(document).ready(function($) {
			var data  = 'ledger_id='+val+'&count_1='+id+'&DEF_pk_ar_fee_type=&DEF_pk_ar_payment_type=';
			var value = $.ajax({
				url: "ajax_payment_type",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					document.getElementById('FEE_PAYMENT_TYPE_DIV_'+id).innerHTML = data
				}		
			}).responseText;
		});

		document.getElementById('CC_FIELDS_1').style.display 		 = 'none';
	}

	function get_enrollment_det(val, id){
		
		jQuery(document).ready(function($) {
			var data  = 'stud_id='+val+'&count1='+id;
			var value = $.ajax({
				url: "ajax_get_misc_batch_student_enrollment",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	

					document.getElementById('ENROLLMEN_DIV_'+id).innerHTML = data;
					
					get_term(document.getElementById('BATCH_PK_STUDENT_ENROLLMENT_'+id).value,id)
				}		
			}).responseText;
		});
	}

	function get_term(val, id){
		jQuery(document).ready(function($) {
			var data  = 'eid='+val;
			var value = $.ajax({
				url: "ajax_student_term",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					document.getElementById('BATCH_PK_TERM_BLOCK_'+id).value = data
				}		
			}).responseText;
		});
	}

	function get_buttons(val)
	{
		jQuery(document).ready(function($) {
			var data  = 'ledger_id='+val;
			var value = $.ajax({
				url: "ajax_get_buttons",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					if(data == 1) { // DIAMOND PAY
						document.getElementById('CC_FIELDS_1').style.display 		= 'flex'
						document.getElementById('EXP_DATE_DIV').style.display 		= 'flex'
						document.getElementById('BTN_CANCEL').style.display 		= 'inline'
						document.getElementById('ADD_CARD_BTN').style.display 		= 'inline'
						//document.getElementById('PAY_BTN').style.display 			= 'none'
						document.getElementById('BTN_SAVE').style.display 			= 'none'
						document.getElementById('PAYMENT_MODE_1').value             = "Credit Card"
						
					} else if(data == 2) { // QUICK BATCH
						document.getElementById('BTN_CANCEL').style.display 		= 'inline'
						document.getElementById('BTN_SAVE').style.display 			= 'inline'
						//document.getElementById('PAY_BTN').style.display 			= 'none'
						document.getElementById('ADD_CARD_BTN').style.display 		= 'none'
						document.getElementById('CC_FIELDS_1').style.display 		= 'none'
						document.getElementById('EXP_DATE_DIV').style.display 		= 'none'
						document.getElementById('PAYMENT_MODE_1').value             = ''
					} else if(data == 0) {
						// document.getElementById('BTN_SAVE').style.display 			= 'none'
						// document.getElementById('ADD_CARD_BTN').style.display 	    = 'none'
						// document.getElementById('BTN_CANCEL').style.display 		= 'none'
						// document.getElementById('PAY_BTN').style.display 			= 'none'

						document.getElementById('CC_FIELDS_1').style.display 		= 'none'
						document.getElementById('EXP_DATE_DIV').style.display 		= 'none'
						document.getElementById('PAYMENT_MODE_1').value             = ''
					}
				}		
			}).responseText;
		});
	}

	function button_enable_disable(val)
	{
		
		jQuery(document).ready(function($) {
			var data  = 'type_id='+val;
			var value = $.ajax({
				url: "ajax_buttons_enable_disable",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					if(data == 1) { // DIAMOND PAY
						document.getElementById('CC_FIELDS_1').style.display 		= 'flex'
						document.getElementById('PK_STUDENT_CREDIT_CARD_STAX').value = ''; // DIAM-2347
						document.getElementById('EXP_DATE_DIV').style.display 		= 'flex'
						document.getElementById('ADD_CARD_BTN').style.display 		= 'inline'
						document.getElementById('PAY_BTN').style.display 			= 'none'
						document.getElementById('BTN_CANCEL').style.display 		= 'inline'
						document.getElementById('BTN_SAVE').style.display 			= 'inline'
						document.getElementById('PAYMENT_MODE_1').value             = "Credit Card"
						
					} else if(data == 2) { // QUICK BATCH
						 document.getElementById('CC_FIELDS_1').style.display 		= 'none' //DIAM-2347
						// document.getElementById('EXP_DATE_DIV').style.display 		= 'none'
						 document.getElementById('ADD_CARD_BTN').style.display 		= 'inline' //DIAM-2347
						 document.getElementById('PAY_BTN').style.display 			= 'none' //DIAM-2347
						// document.getElementById('BTN_CANCEL').style.display 		= 'inline'
						// document.getElementById('BTN_SAVE').style.display 			= 'inline'
						document.getElementById('PAYMENT_MODE_1').value             = ''
					} else if(data == 0) {
						// document.getElementById('BTN_SAVE').style.display 			= 'none'
						// document.getElementById('ADD_CARD_BTN').style.display 	    = 'none'
						// document.getElementById('BTN_CANCEL').style.display 		= 'none'
						// document.getElementById('PAY_BTN').style.display 			= 'none'

						document.getElementById('CC_FIELDS_1').style.display 		= 'none'
						document.getElementById('EXP_DATE_DIV').style.display 		= 'none'
						document.getElementById('PAYMENT_MODE_1').value             = ''
					}
				}		
			}).responseText;
		});
	}
	// End DIAM-1090
	
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

	function check_number_val(e)
	{
		const regex  = /[^\d]|\.(?=.*\.)/g;
		const subst  = '';
		const str    = e.value;
		const result = str.replace(regex, subst);
		e.value      = result;
	}

	function check_number_validation(e)
	{
		const pattern = /^[0-9]$/;
		return pattern.test(e.key);
	}
	</script>
	
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
</body>

</html>