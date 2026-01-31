<? require_once("../global/config.php");
require_once("../global/payments_stax.php");
require_once("../global/mail.php");
require_once("../global/texting.php");

require_once("function_student_ledger.php");
require_once("function_update_disbursement_status.php");
require_once("receipt_pdf_function.php"); 

require_once("../language/add-card.php");
require_once("../language/common.php");
require_once("../language/make_payment.php");

$res_pay = $db->Execute("select ENABLE_DIAMOND_PAY, CHARGE_PROCESSING_FEE_FROM_STUDENT from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 0) {
	header("location:../index");
	exit;
}
$CHARGE_PROCESSING_FEE_FROM_STUDENT = $res_pay->fields['CHARGE_PROCESSING_FEE_FROM_STUDENT'];

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$msg = "";
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$data['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
	$data['PK_STUDENT_MASTER'] 		= $_GET['sid'];
	$data['PK_STUDENT_ENROLLMENT'] 	= $_GET['eid'];
	$data['TYPE'] 					= $_GET['type'];
	$data['ID'] 					= $_GET['id'];
	
	if($_GET['type'] == "disp") {
		$res = $db->Execute("select PK_DISBURSEMENT_STATUS from S_STUDENT_DISBURSEMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_DISBURSEMENT = '$_GET[id]' ");
		if($res->fields['PK_DISBURSEMENT_STATUS'] != 2){
			header("location:student?t=".$_GET['t']."&eid=".$_GET['eid']."&id=".$_GET['sid']."&tab=disbursementTab");
			exit;
		}
	}
	
	if($_POST['PK_STUDENT_CREDIT_CARD_STAX'] == -1)
	{
		$data['PK_STUDENT_CREDIT_CARD'] = $_POST['NEW_CARD_ID'];
	}
	else
	{
		$data['PK_STUDENT_CREDIT_CARD'] = $_POST['PK_STUDENT_CREDIT_CARD_STAX'];
	}
	// echo "<pre>";print_r($data);exit;	
	$pn_res = make_payment_stax($data);
	
	if($pn_res['STATUS'] == 1) {
		if($_GET['type'] == 'disp'){
			header("location:batch_payment?id=".$pn_res['PK_PAYMENT_BATCH_MASTER']);
		}
		
	} else {
		$msg = $pn_res['MSG'];
	}
		
}

$res_card = $db->Execute("select * from S_STUDENT_CREDIT_CARD_STAX WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[sid]' ");

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
	<title><?=MAKE_PAYMENT_TITLE?> | <?=$title?></title>
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
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-6 align-self-center">
                        <h4 class="text-themecolor"><?=MAKE_PAYMENT_TITLE?></h4>
                    </div>
					<div class="col-md-6 align-self-center">
                        <h4 class="text-themecolor"><?=CARD_INFO?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" onsubmit="return false;" >
								
								<div class="p-20">
									<div class="row">
										<div class="col-5">
											<? if($msg != ''){ ?>
											<div class="row">
													<div class="col-12 col-sm-12 form-group" style="text-align:center;color:red">
														<b>Error: <?=$msg?></b>
													</div>
												</div>
											<? } ?>
											
											<? $AMOUNT = 0;
											if($_GET['type'] == 'disp'){ 
												$PK_STUDENT_DISBURSEMENT_ARR = explode(",",$_GET['id']);
												$i = 0;
												foreach($PK_STUDENT_DISBURSEMENT_ARR as $PK_STUDENT_DISBURSEMENT) {
													$i++;
													$res_disb = $db->Execute("select S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE.CODE AS LEDGER, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, STUDENT_ID, ACADEMIC_YEAR, ACADEMIC_PERIOD, IF(DISBURSEMENT_DATE = '0000-00-00','', DATE_FORMAT(DISBURSEMENT_DATE, '%m/%d/%Y' )) AS DISBURSEMENT_DATE, DISBURSEMENT_AMOUNT, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_STUDENT_DISBURSEMENT LEFT JOIN S_TERM_BLOCK ON S_TERM_BLOCK.PK_TERM_BLOCK = S_STUDENT_DISBURSEMENT.PK_TERM_BLOCK LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE, S_STUDENT_MASTER LEFT JOIn S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER AND S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT' "); 
													$AMOUNT += $res_disb->fields['DISBURSEMENT_AMOUNT']; 
													
													if($i == 1){ ?>
													<div class="row">
														<div class="col-6 col-sm-6 form-group">
															<b><?=STUDENT?></b>
														</div>
														<div class="col-6 col-sm-6 form-group">
															<?=$res_disb->fields['NAME']?>
														</div>
													</div>
													<div class="row">
														<div class="col-6 col-sm-6 form-group">
															<b><?=STUDENT_ID?></b>
														</div>
														<div class="col-6 col-sm-6 form-group">
															<?=$res_disb->fields['STUDENT_ID']?>
														</div>
													</div>
													<hr />
													<? } ?>
													<div class="row">
														<div class="col-6 col-sm-6 form-group">
															<b><?=LEDGER?></b>
														</div>
														<div class="col-6 col-sm-6 form-group">
															<?=$res_disb->fields['LEDGER']?>
														</div>
													</div>
													<div class="row">
														<div class="col-6 col-sm-6 form-group">
															<b><?=DISBURSEMENT_DATE?></b>
														</div>
														<div class="col-6 col-sm-6 form-group">
															<?=$res_disb->fields['DISBURSEMENT_DATE']?>
														</div>
													</div>
													<div class="row">
														<div class="col-6 col-sm-6 form-group">
															<b><?=DISBURSEMENT_AMOUNT?></b>
														</div>
														<div class="col-6 col-sm-6 form-group">
															$ <?=number_format_value_checker($res_disb->fields['DISBURSEMENT_AMOUNT'],2)?>
														</div>
													</div>
													<hr />
												<? } ?>
													<div class="row">
														<div class="col-6 col-sm-6 form-group">
															<b><?=TOTAL_DISBURSEMENT_AMOUNT?></b>
														</div>
														<div class="col-6 col-sm-6 form-group">
															$ <?=number_format_value_checker($AMOUNT,2)?>
														</div>
													</div>
											<? } ?>
											
											<input id="CHARGE_AMOUNT" name="CHARGE_AMOUNT" type="hidden" value="1" >
											
										</div>
										<div class="col-6">

											<? $style 			= "";
												$pay_style 		= "display:none";
												$add_card_style = "display:inline";
												if($res_card->RecordCount() > 0) { 
													$style 			= "display:none"; 
													$pay_style 		= "display:inline";
													$add_card_style = "display:none"; ?>
													<div class="row form-group" >
														<div class="col-md-6 align-self-center">
															<select id="PK_STUDENT_CREDIT_CARD_STAX" name="PK_STUDENT_CREDIT_CARD_STAX" class="form-control required-entry" onchange="show_cc_field(this.value)" >
																<option value="" ></option>
																<option value="-1" >Add New</option>
																<? $res_type = $db->Execute("select * from S_STUDENT_CREDIT_CARD_STAX WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[sid]' ORDER BY PK_STUDENT_CREDIT_CARD_STAX DESC");
																while (!$res_type->EOF) { ?>
																	<option value="<?=$res_type->fields['PK_STUDENT_CREDIT_CARD_STAX'] ?>" ><?=$res_type->fields['CARD_NO'].' - '.$res_type->fields['NAME_ON_CARD']?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
															<span class="bar"></span>
															<label for="PK_STUDENT_CREDIT_CARD"><?=CARD_NO?></label>
														</div>
													</div>
											<? } ?>

                                            <div id="CC_FIELDS" style="<?=$style?>" >			
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
													<input type="text" class="form-control required-entry" id="zipcode" value="" name="zipcode" onkeypress="return check_number_validation(event);" onchange="check_number_val(this);" placeholder=""  >
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
														<div style="width:80%; height:35px; display: inline-block;" >
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
											
											<div class="row">
												<div class="col-3 col-sm-3">
												</div>
												
												<div class="col-9 col-sm-9">
													<input type="hidden" name="response" id="response" value="" >
													<input type="hidden" id="mode" value="auth">
													<input type="hidden" id="convert" value="underscores">
													<input type="hidden" id="get_customer_id" value="">
													
													<input type="hidden" id="card_amount" value="0" >
													<input type="hidden" id="NEW_CARD_ID" name="NEW_CARD_ID" value="0" >

													<button id="PAY_BTN" type="button" onClick="validate_payment_form(1);" style="<?=$pay_style?>" class="btn waves-effect waves-light btn-info" ><?=MAKE_PAYMENT ?></button>
														
													<button id="ADD_CARD_BTN" type="button" style="<?=$add_card_style?>" class="btn waves-effect waves-light btn-info validate_payment_direct" ><?=MAKE_PAYMENT ?></button>
												
													<button type="button" onclick="window.location.href='student?t=<?=$_GET['t']?>&eid=<?=$_GET['eid']?>&id=<?=$_GET['sid']?>&tab=disbursementTab'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
												</div>
											</div>
											
											
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

    <!-- <script src="https://staxjs.staxpayments.com/stax.js?nocache=2"></script> -->
	<script type="text/javascript" src="../assets/js/stax.js"></script> <!-- DIAM-2347 -->

	<script language="javascript">

    jQuery(document).ready(function($) {

        // Stax Start
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
				style: 'width: 30px; height:90%; border-radius: 3px; border: 1px solid #ccc; padding: .5em .5em; font-size: 91%;'
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

					var form = document.querySelector('form');
					var extraDetails = {
						total: <?=$AMOUNT?>, // 1$
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
						
						data = 's_id=<?=$_GET['sid']?>&customer_id='+customer_id+'&card_type='+card_type+'&card_exp='+card_exp+'&card_name='+card_name+'&payment_method_id='+payment_method_id+'&card_last_four='+card_last_four+'&address_zip='+address_zip;

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
   
    });     
	
	function validate_payment_form(type) {

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

		if(flag == 1) 
		{
			if(type == 1) 
			{
				jQuery(document).ready(function($) {

					var card_id = '';
					if(document.getElementById('PK_STUDENT_CREDIT_CARD_STAX')){
						card_id = document.getElementById('PK_STUDENT_CREDIT_CARD_STAX').value;
					}
					
					var value = $.ajax({
						url: "ajax_calc_stax_transaction_charge",	
						type: "POST",		 
						data: 'id='+card_id+'&transaction_initiation_type=MIT&amt=<?=$AMOUNT?>',		
						async: false,
						cache: false,
						success: function (data) {	
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

	}
	
	function show_cc_field(val){
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
			
			document.getElementById('ADD_CARD_BTN').style.display 	= 'none'
			document.getElementById('PAY_BTN').style.display 		= 'inline'
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
	
	function conf_payment(val){
		jQuery(document).ready(function($) {
			if(val == 1) {
				document.form1.submit();
			}
			$("#feeModal").modal("hide");
		});
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
	
</body>

</html>