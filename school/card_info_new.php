<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/make_payment.php");
require_once("../language/add-card.php");
require_once("check_access.php");

$res_pay = $db->Execute("select ENABLE_DIAMOND_PAY from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 0) {
	header("location:../index");
	exit;
}

if(check_access('FINANCE_ACCESS') == 0 && check_access('ACCOUNTING_ACCESS') == 0) {
	header("location:../index");
	exit;
}

if($_GET['act'] == 'del_cc'){

	// DIAM-2101
	$res_card = $db->Execute("SELECT CUSTOMER_ID,PAYMENT_METHOD_ID FROM S_STUDENT_CREDIT_CARD_STAX WHERE PK_STUDENT_CREDIT_CARD_STAX = '$_GET[iid]' ");
	if($res_card->RecordCount() > 0) 
	{
		$res_cardx = $db->Execute("SELECT API_KEY FROM S_STAX_X_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

		$Invoice_Id = $res_card->fields['PAYMENT_METHOD_ID'];
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://apiprod.fattlabs.com/payment-method/'.$Invoice_Id,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'DELETE',

			CURLOPT_HTTPHEADER => array(
			'Authorization: Bearer '.$res_cardx->fields['API_KEY'].'',
			'content-type: application/json'
			),
		));

		$response 	= curl_exec($curl);
		$err 		= curl_error($curl);

		curl_close($curl);
		if($err) {
			
		} else {
			$data = json_decode($response);
			// echo "<pre>";print_r($data);exit;
		}

		$db->Execute("DELETE from S_STUDENT_CREDIT_CARD_STAX WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CREDIT_CARD_STAX = '$_GET[iid]' ");
		header("location:card_info_new.php?s_id=".$_GET['s_id']."&eid=".$_GET['eid']."&t=".$_GET['t']);
		exit;
	}
	// End DIAM-2101

} else if($_GET['act'] == 'pri'){

	// DIAM-2359
	if($_GET['iid'] != "")
	{
		$db->Execute("UPDATE S_STUDENT_CREDIT_CARD_STAX SET IS_PRIMARY = 1 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CREDIT_CARD_STAX = '$_GET[iid]' ");
	}
	$res_type_pri = $db->Execute("SELECT PK_STUDENT_CREDIT_CARD_STAX FROM S_STUDENT_CREDIT_CARD_STAX WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[s_id]' AND PK_STUDENT_CREDIT_CARD_STAX != '$_GET[iid]' ");
	if($res_type_pri->RecordCount() > 0) 
	{
		while (!$res_type_pri->EOF) 
		{
			$PK_STUDENT_CREDIT_CARD_STAX = $res_type_pri->fields['PK_STUDENT_CREDIT_CARD_STAX'];
			$db->Execute("UPDATE S_STUDENT_CREDIT_CARD_STAX SET IS_PRIMARY = 0 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[s_id]' AND PK_STUDENT_CREDIT_CARD_STAX = '$PK_STUDENT_CREDIT_CARD_STAX' ");

			$res_type_pri->MoveNext();
		}
	}
	// $db->Execute("UPDATE S_STUDENT_CREDIT_CARD_STAX SET IS_PRIMARY = 0 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_SESSION[s_id]' ");
	// $db->Execute("UPDATE S_STUDENT_CREDIT_CARD_STAX SET IS_PRIMARY = 1 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CREDIT_CARD_STAX = '$_GET[iid]' ");
	// End DIAM-2359

	header("location:card_info_new.php?s_id=".$_GET['s_id']."&eid=".$_GET['eid']."&t=".$_GET['t']);
	exit;
}

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
	<title><?=ADD_CARD_TITLE?> | <?=$title?></title>
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
                        <h4 class="text-themecolor">Accept Credit Cards</h4>
                    </div>
					
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							 <form class="floating-labels m-t-40" onsubmit="return false;">
								
								<div class="p-20">
									<div class="row">
										<div class="col-5">
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<input type="text" class="form-control required-entry" id="cardholder-first-name" name="cardholder-first-name" value="" placeholder=""  >
													<span class="bar"></span> 
													<label for="cardholder-first-name">First Name</label>
												</div>
											</div>
                                            <div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<input type="text" class="form-control required-entry" id="cardholder-last-name" name="cardholder-last-name" value="" placeholder=""  >
													<span class="bar"></span> 
													<label for="cardholder-last-name">Last Name</label>
												</div>
											</div>
                                            <div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<input type="text" class="form-control required-entry" id="phone" value="" name="phone" placeholder=""  >
													<span class="bar"></span> 
													<label for="phone">Phone (Optional)</label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<input type="text" class="form-control required-entry" id="address" value="" name="address" placeholder=""  >
													<span class="bar"></span> 
													<label for="address">Address 1</label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<input type="text" class="form-control required-entry" id="address_2" value="" name="address_2" placeholder=""  >
													<span class="bar"></span> 
													<label for="address_2">Address 2 (Optional)</label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<input type="text" class="form-control required-entry" id="city" value="" name="city" placeholder=""  >
													<span class="bar"></span> 
													<label for="city">City</label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<input type="text" class="form-control required-entry" id="state" value="" name="state" placeholder=""  >
													<span class="bar"></span> 
													<label for="state">State</label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<input type="text" class="form-control required-entry" id="zipcode" value="" name="zipcode" onkeypress="return check_number_validation(event);" onchange="check_number_val(this);" placeholder=""  >
													<span class="bar"></span> 
													<label for="zipcode">Zipcode</label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<input type="text" class="form-control required-entry" id="country" value="" name="country" placeholder=""  >
													<span class="bar"></span> 
													<label for="country">Country (Optional)</label>
												</div>
											</div>
											<div class="d-flex">
												<!-- <div class="col-12 col-sm-9 form-group">
													<div style="width:80%; height:35px; display: inline-block;">
														<input type="text" class="form-control required-entry" id="staxjs-number" name="staxjs-number" value="" placeholder=""  >
														<span class="bar"></span> 
														<label for="staxjs-number">Card</label>
													</div>
													<div style="width:72px; height:35px; display: inline-block;">
                                                        <input name="staxjs-cvv" id="staxjs-cvv" value="" class="form-control required-entry" size="5" maxlength="3" placeholder="CVV" style="width: 43px; height:18px; border-radius: 3px; border: 1px solid #ccc; padding: .5em .5em; font-size: 91%;">
                                                    </div>
												</div> -->
												<div id="card-element" class="col-12 col-sm-7 form-group">
													    <div for="staxjs-number" style="width:50px; height:35px; display: inline-block; margin:3px;color: #0e79e5;">Card</div>
														<div id="staxjs-number" style="width:180px; height:35px; display: inline-block; margin:3px;border-bottom: 1px solid #e9ecef;"></div>
														<div id="staxjs-cvv" style="width:50px; height:35px; display: inline-block; margin:3px"></div>
												</div>
                                                <div class="col-12 col-sm-3 form-group" style="text-align: right;">
                                                    <div style="width:40px; height:35px; display: inline-block;">
                                                        <input type="text" name="month" id="month" value="" class="form-control required-entry" onkeypress="return check_number_validation(event);" onchange="check_number_val(this);" size="3" maxlength="2" placeholder="MM" style="width: 38px; height:18px; border-radius: 3px; border: 1px solid #ccc; padding: .5em .5em; font-size: 91%;">
                                                    </div>
                                                    &nbsp;/&nbsp;
                                                    <div style="width:55px; height:35px; display: inline-block;padding: 0 8px 0 0">
                                                        <input type="text" name="year" id="year" value="" class="form-control required-entry" onkeypress="return check_number_validation(event);" onchange="check_number_val(this);" size="5" maxlength="4" placeholder="YYYY" style="width: 47px; height:18px; border-radius: 3px; border: 1px solid #ccc; padding: .5em .5em; font-size: 91%">
                                                    </div>
                                                </div>
											</div>
											<div class="alert-errors alert-danger-errors" role="alert" id="errors" style="display: none;" ></div>
											<div class="row">
												<div class="col-3 col-sm-3">
												</div>
												
												<div class="col-9 col-sm-9">
                                                    <button type="button" class="btn waves-effect waves-light btn-info validate_payment_direct" id="tokenizebutton" >Tokenize Card</button>
													<button type="button" onclick="window.location.href='student?t=<?=$_GET['t']?>&eid=<?=$_GET['eid']?>&id=<?=$_GET['s_id']?>&tab=ledgerTab'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
                                                    
												</div>
											</div>
										</div>
										<div class="col-7">
											<table class="table table-hover">
												<thead>
													<tr>
														<th><?=NAME_ON_CARD?></th>
														<th><?=CARD_NO?></th>
														<th><?=CARD_EXP?></th>
														<th><?=CARD_TYPE?></th>
														<th><?=IS_PRIMARY?></th>
														<th><?=OPTIONS?></th>
													</tr>
												</thead>
												<tbody>
												<? $res_type = $db->Execute("select * from S_STUDENT_CREDIT_CARD_STAX WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[s_id]' ORDER BY PK_STUDENT_CREDIT_CARD_STAX DESC");
												while (!$res_type->EOF) { ?>
													<tr>
														<td><?=$res_type->fields['NAME_ON_CARD']?></td>
														<td><?=$res_type->fields['CARD_NO']?></td>
														<td><?=$res_type->fields['CARD_EXP']?></td>
														<td><?=$res_type->fields['CARD_TYPE']?></td>
														<td>
															<? if($res_type->fields['IS_PRIMARY'] == 1) 
																echo "Yes"; 
															else {
																echo "No<br />"; ?>
																<a href="javascript:void(0);" onclick="set_as_primary('<?=$res_type->fields['PK_STUDENT_CREDIT_CARD_STAX']?>')" title="<?=SET_AS_PRIMARY?>" ><?=SET_AS_PRIMARY?></a>
															<? } ?>
														</td>
														<td>
															<a href="javascript:void(0);" onclick="delete_card_popup('<?=$res_type->fields['PK_STUDENT_CREDIT_CARD_STAX']?>')" title="<?=DELETE_CARD?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
														</td>
													</tr>
												<?	$res_type->MoveNext();
												} ?>
												</tbody>
											</table>			
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
		
		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<?=DELETE_CONFIRMATION_MSG ?>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" id="DELETE_ID" value="" >
						<button type="button" onclick="conf_delete_card_popup(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete_card_popup(0)" ><?=NO?></button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal" id="enableModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=RECURRING_PAYMENTS?></h4>
					</div>
					<div class="modal-body">
						<div class="form-group" >
							<?=ENABLE_RECURRING_PAYMENTS?>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="confirm_enable_auto_payment(1,1)" class="btn waves-effect waves-light btn-info"><?=AGREE?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="confirm_enable_auto_payment(0,1)" ><?=CANCEL?></button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal" id="disableModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=RECURRING_PAYMENTS?></h4>
					</div>
					<div class="modal-body">
						<div class="form-group" >
							<?=DISABLE_RECURRING_PAYMENTS?>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="confirm_enable_auto_payment(1,0)" class="btn waves-effect waves-light btn-info"><?=AGREE?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="confirm_enable_auto_payment(0,0)" ><?=CANCEL?></button>
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

		var tokenizeButton = document.querySelector('#tokenizebutton');
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
			handler.setTestPan("");
    		handler.setTestCvv("");
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
			tokenizeButton.disabled = false;
			console.log(message);
		});

		staxJs.on('card_form_uncomplete', (message) => {
			// deactivate pay button
			tokenizeButton.disabled = true;
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
				// document.querySelector('#tokenizebutton').onclick = () => 
				// {
					var form = document.querySelector('form');
					var extraDetails = {
						// total: 1, // 1$
						firstname: form.querySelector('input[name=cardholder-first-name]').value,
						lastname: form.querySelector('input[name=cardholder-last-name]').value,
						month: form.querySelector('input[name=month]').value,
						year: form.querySelector('input[name=year]').value,
						phone: form.querySelector('input[name=phone]').value,
						address_1: form.querySelector('input[name=address]').value,
						address_2: form.querySelector('input[name=address_2]').value,
						address_city: form.querySelector('input[name=city]').value,
						address_state: form.querySelector('input[name=state]').value,
						address_zip: form.querySelector('input[name=zipcode]').value,
						address_country: form.querySelector('input[name=country]').value,
						// url: "https://app.staxpayments.com/#/bill/",
						url: "https://docs.staxpayments.com/staxjs/",
						method: 'card',
						validate: false, 
					};

					// call tokenize api
					staxJs.tokenize(extraDetails).then((result) => {
					
						console.log("tokenize invoice object:", result);
						
						var payment_method_id = result.id;
						var customer_id 	  = result.customer_id;
						var card_type   	  = result.card_type;
						var card_exp    	  = result.card_exp;
						var card_last_four    = result.card_last_four;
						var address_zip    	  = result.address_zip;
						var card_name         = result.customer.firstname+' '+result.customer.lastname;
						
						data = 's_id=<?=$_GET['s_id']?>&customer_id='+customer_id+'&card_type='+card_type+'&card_exp='+card_exp+'&card_name='+card_name+'&payment_method_id='+payment_method_id+'&card_last_four='+card_last_four+'&address_zip='+address_zip;
			
						var value = $.ajax({
							url: "add_card_stax.php",	
							type: "POST",		 
							data: data,		
							async: false,
							cache: false,
							success: function (data) {	
								
								data = data.split("|||")
								if(data[0] == 1)
									// alert(data)
									location.reload(); 
								else
									alert(data[1])
							}		
						}).responseText;
						
					})
					.catch((err) => {
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
	
	function creditCardValidation()
	{
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
	
		if(document.getElementById('phone')){
			if(document.getElementById('phone').value == ''){
				flag = 0;
				$("#phone").addClass("validation-failed")
				$("#advice-required-entry-phone").remove()
				$("#phone").parent().append('<div class="validation-advice" id="advice-required-entry-phone'+'" style="">This is a required field.</div>')
			} else {
				$("#phone").removeClass("validation-failed")
				$("#advice-required-entry-phone").remove()
			}
		}
				
	}

	function valCardExpiry(date) {
		// let dateformat = /^(0?[1-9]|[1-2][0-9]|3[01])[\/](0?[1-9]|1[0-2])/; // dd/mm/YYYY
		let dateformat = /^[0-9]{1,2}\/[0-9]{2}$/; // mm/YYYY
		//Matching the date through regular expression      
		if (date.match(dateformat)) {
			let operator = date.split('/');

			// Extract the string into month and year      
			let datepart = [];
			if (operator.length > 1) {
				datepart = date.split('/');
			}
			let month = parseInt(datepart[0]);
			let year = parseInt(datepart[1]);

			// Monthwise
			let ListofMonths = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
			if(ListofMonths.includes(month))
			{
				const d = new Date();
				let current_month = d.getMonth();
				if(month >= current_month)
				{
					// alert("valid Month!");
					//return true;
				}
				else{
					alert("Please enter month, greater than equal to current month!");
					return false;
				}

				// YearWise
				const strDate = new Date();
				let current_year = strDate.getFullYear();
				var twoDigitYear = current_year.toString().substr(-2);
				if(year >= twoDigitYear)
				{
					// alert("Valid Year!");
					// return true;
				}
				else{
					alert("Please enter year greater than equal to current year!");
					return false;
				}
			}
			else{
				alert("Please enter valid month!");
				return false;
			}
			
		} 
		else {
			alert("Invalid Card Expiry Format!");
			return false;
		}
	}
	
	function delete_card_popup(id){
		jQuery(document).ready(function($) {
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
		});
	}
	function conf_delete_card_popup(val){
		jQuery(document).ready(function($) {
			if(val == 1) {
				window.location.href = "card_info_new.php?s_id=<?=$_GET['s_id']?>&eid=<?=$_GET['eid']?>&t=<?=$_GET['t']?>&act=del_cc&iid="+$("#DELETE_ID").val();
			}
			$("#deleteModal").modal("hide");
		});
	}
	
	function set_as_primary(id){
		window.location.href = "card_info_new.php?s_id=<?=$_GET['s_id']?>&eid=<?=$_GET['eid']?>&t=<?=$_GET['t']?>&act=pri&iid="+id;
	}
	
	function enable_auto_payment(){
		/*jQuery(document).ready(function($) {
			var va1 = '';
			if(document.getElementById('ENABLE_AUTO_PAYMENT').checked == true) {
				$("#enableModal").modal()
			} else {
				$("#disableModal").modal()
			}
		});*/
		
		jQuery(document).ready(function($) {
			var enable = '';
			if(document.getElementById('ENABLE_AUTO_PAYMENT').checked == true)
				enable = 1;
			else
				enable = 0;
			var data = 'va1='+enable+'&NOTE_DATE='+document.getElementById('NOTE_DATE').value+'&NOTE_TIME='+document.getElementById('NOTE_TIME').value+'&sid=<?=$_GET['s_id']?>&eid=<?=$_GET['eid']?>';
			var value = $.ajax({
				url: "ajax_enable_auto_payment",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					//alert(data)
				}		
			}).responseText;
		});
	}
	
	function confirm_enable_auto_payment(va1,enable){
		jQuery(document).ready(function($) {
			if(va1 == 1){ 
				var data = 'va1='+enable+'&NOTE_DATE='+document.getElementById('NOTE_DATE').value+'&NOTE_TIME='+document.getElementById('NOTE_TIME').value+'&sid=<?=$_GET['s_id']?>&eid=<?=$_GET['eid']?>';
				var value = $.ajax({
					url: "ajax_enable_auto_payment",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
					}		
				}).responseText;
			} else {
				if(enable == 0)
					document.getElementById('ENABLE_AUTO_PAYMENT').checked = true
				else
					document.getElementById('ENABLE_AUTO_PAYMENT').checked = false
			}
			$("#enableModal").modal("hide");
			$("#disableModal").modal("hide");
		});
	}
	
	function timenow(){
		var now= new Date(), 
		ampm= 'am', 
		h= now.getHours(), 
		m= now.getMinutes(), 
		s= now.getSeconds();
		if(h >= 12){
			if(h > 12) h -= 12;
				ampm= 'pm';
		}

		if(m<10) m= '0'+m;
		if(s<10) s= '0'+s;
		//var t = now.toLocaleDateString('en-GB')
		var t = FixLocaleDateString(now.toLocaleDateString('en-GB'))
		var time = h + ':' + m + ' ' + ampm;
		t = t.split("/");
		//var t1 = t[2]+'-'+t[1]+'-'+t[0]+' '+time;
		//return t1; 
		
		// document.getElementById('NOTE_DATE').value = t[1]+'/'+t[0]+'/'+t[2]
		// document.getElementById('NOTE_TIME').value = time
	}
	
	function FixLocaleDateString(localeDate) {
		var newStr = "";
		for (var i = 0; i < localeDate.length; i++) {
			var code = localeDate.charCodeAt(i);
			if (code >= 47 && code <= 57) {
				newStr += localeDate.charAt(i);
			}
		}
		return newStr;
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