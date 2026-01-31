<? require_once("../global/config.php");
require_once("../global/payments_stax.php");

require_once("../language/common.php");
require_once("../language/make_payment.php");

$res_pay = $db->Execute("select ENABLE_DIAMOND_PAY from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 0) {
	header("location:../index");
	exit;
}

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_USER_TYPE'] != 3 ){
	header("location:../index");
	exit;
}

$sPK_STUDENT_MASTER = $_SESSION['PK_STUDENT_MASTER'];

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
	<title><?=ADD_CARD ?> | <?=$title?></title>
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
							<form class="floating-labels m-t-40" method="post" onsubmit="return false;" >
								
								<div class="p-20">
									<div class="row">

										<div class="col-6">
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
                                                <div class="col-12 col-sm-3 form-group" style="text-align: right;">
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
											<div class="row">
												<div class="col-3 col-sm-3">
												</div>
												
												<div class="col-9 col-sm-9">
                                                    <button type="button" class="btn waves-effect waves-light btn-info validate_payment_direct" id="tokenizebutton" >Tokenize Card</button>
													<button type="button" onclick="window.location.href='payment_info_stax'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
                                                    
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
						
						data = 's_id=<?=$sPK_STUDENT_MASTER?>&customer_id='+customer_id+'&card_type='+card_type+'&card_exp='+card_exp+'&card_name='+card_name+'&payment_method_id='+payment_method_id+'&card_last_four='+card_last_four+'&address_zip='+address_zip;

						var value = $.ajax({
							url: "add_card_stax.php",	
							type: "POST",		 
							data: data,		
							async: false,
							cache: false,
							success: function (data) {	
								//alert(data)
								data = data.split("|||")
								if(data[0] == 1)
									window.location.href = "payment_info_stax";
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