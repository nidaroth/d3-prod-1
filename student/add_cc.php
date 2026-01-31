<? require_once("../global/config.php");
require_once("../global/payments.php");

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

$msg = "";
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
}
$res_card = $db->Execute("select * from S_STUDENT_CREDIT_CARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' ");

$res = $db->Execute("select * from S_CARD_X_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$PUBLISHER_NAME 	= $res->fields['PUBLISHER_NAME'];
$PUBLISHER_PASSWORD = $res->fields['PUBLISHER_PASSWORD'];
$SITE_KEY 			= $res->fields['SITE_KEY'];
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
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
					<div class="col-md-6 align-self-center">
                        <h4 class="text-themecolor"><?=CARD_INFO?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								
								<div class="p-20">
									<div class="row">
										
										<div class="col-6">
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<input type="hidden" id="publisher_name" value="<?=$PUBLISHER_NAME?>">
													<input type="hidden" id="mode" value="auth">
													<input type="hidden" id="convert" value="underscores">
													
													<input type="hidden" id="card_amount" value="0" >
													
													<input type="text" class="form-control required-entry" id="card_name" value="" placeholder=""  >
													<span class="bar"></span> 
													<label for="card_name"><?=NAME_ON_CARD?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<input type="text" class="form-control required-entry" id="card_number" value="" placeholder=""  >
													<span class="bar"></span> 
													<label for="card_number"><?=CARD_NO?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group">
													<input type="text" class="form-control required-entry" id="card_exp" value="" >
													<span class="bar"></span> 
													<label for="card_exp"><?=CARD_EXP?></label>
												</div>
											
												<div class="col-12 col-sm-6 form-group focused">
													<input id="card_cvv" type="text" class="form-control required-entry" value="">
													<span class="bar"></span> 
													<label for="card_cvv"><?=CVV?></label>
												</div>
											</div>
											
											<div class="row">
												<div class="col-3 col-sm-3">
												</div>
												
												<div class="col-9 col-sm-9">
													<button type="button" class="btn waves-effect waves-light btn-info" onClick="payment_api.send();" ><?=ADD ?></button>
													<button type="button" onclick="window.location.href='payment_info'" class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
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

	<script language="javascript" src="https://pay1.plugnpay.com/api/iframe/<?=$SITE_KEY?>/client/"></script>
	<script language="javascript">
	jQuery(document).ready(function($) {
		payment_api.setCallback(function(data) {

			// 'data' is the querystring returned by the payment request.
			// Perform any response handling you would like to do here.
			// For example, such as putting the value of data into a field
			//   and calling submit on the form.
			//alert(data);
			data = data
			var value = $.ajax({
				url: "add_card",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					//alert(data)
					data = data.split("|||")
					if(data[0] == 1) 
						window.location.href = "payment_info";
					else
						alert(data[1])
				}		
			}).responseText;

		})
	});

	</script>
	
</body>

</html>