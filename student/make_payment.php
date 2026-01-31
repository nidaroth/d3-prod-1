<? require_once("../global/config.php");
require_once("../global/payments.php");

require_once("../global/mail.php");
require_once("../global/texting.php");

require_once("../school/function_student_ledger.php");
require_once("../school/function_update_disbursement_status.php");
require_once("../school/receipt_pdf_function.php"); 
require_once("../language/add-card.php");
require_once("../language/common.php");
require_once("../language/make_payment.php");

$res_pay = $db->Execute("select ENABLE_DIAMOND_PAY, CHARGE_PROCESSING_FEE_FROM_STUDENT from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 0) {
	header("location:../index");
	exit;
}
$CHARGE_PROCESSING_FEE_FROM_STUDENT = $res_pay->fields['CHARGE_PROCESSING_FEE_FROM_STUDENT'];

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_USER_TYPE'] != 3 ){
	header("location:../index");
	exit;
}

if($_GET['act'] == 'del_cc'){
	$db->Execute("DELETE from S_STUDENT_CREDIT_CARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND PK_STUDENT_CREDIT_CARD = '$_GET[iid]' ");
	header("location:make_payment.php?id=".$_GET['id']."&page=".$_GET['id']);
	exit;
} else if($_GET['act'] == 'pri'){
	$db->Execute("UPDATE S_STUDENT_CREDIT_CARD SET IS_PRIMARY = 0 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' ");
	$db->Execute("UPDATE S_STUDENT_CREDIT_CARD SET IS_PRIMARY = 1 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CREDIT_CARD = '$_GET[iid]' AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' ");
	header("location:make_payment.php?id=".$_GET['id']."&page=".$_GET['page']);
	exit;
}

$URL = '';
if($_GET['page'] == 'p'){
	$URL = 'payments';
} else 
	$URL = 'index';

$msg = "";
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$data['PK_ACCOUNT'] 		= $_SESSION['PK_ACCOUNT'];
	$data['PK_STUDENT_MASTER'] 	= $_SESSION['PK_STUDENT_MASTER'];
	$data['TYPE'] 				= 'disp';
	$data['ID'] 				= $_GET['id'];
	
	if($data['TYPE'] == "disp") {
		$res = $db->Execute("select PK_DISBURSEMENT_STATUS, PK_STUDENT_ENROLLMENT from S_STUDENT_DISBURSEMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_DISBURSEMENT = '$_GET[id]' ");
		if($res->fields['PK_DISBURSEMENT_STATUS'] != 2){
			header("location:index");
			exit;
		}
		$data['PK_STUDENT_ENROLLMENT'] 	= $res->fields['PK_STUDENT_ENROLLMENT'];
	}
	
	if($_POST['PK_STUDENT_CREDIT_CARD'] == -1 || !isset($_POST['PK_STUDENT_CREDIT_CARD']))
		$data['PK_STUDENT_CREDIT_CARD'] = $_POST['NEW_CARD_ID'];
	else
		$data['PK_STUDENT_CREDIT_CARD'] = $_POST['PK_STUDENT_CREDIT_CARD'];
//echo "<pre>";print_r($data);exit;		
	$pn_res = make_payment($data);
	
	if($pn_res['STATUS'] == 1) {
		header("location:".$URL);
	} else {
		$msg = $pn_res['MSG'];
	}
		
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
	<title><?=MAKE_PAYMENT_TITLE?> | <?=$title?></title>
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
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								
								<div class="p-20">
									<div class="row">
										<div class="col-6">
											<? if($msg != ''){ ?>
											<div class="row">
													<div class="col-12 col-sm-12 form-group" style="text-align:center;color:red">
														<b>Error: <?=$msg?></b>
													</div>
												</div>
											<? } ?>
											
											<? $AMOUNT = 0;
											//if($_GET['type'] == 'disp'){ 
												$PK_STUDENT_DISBURSEMENT_ARR = explode(",",$_GET['id']);
												$i = 0;
												foreach($PK_STUDENT_DISBURSEMENT_ARR as $PK_STUDENT_DISBURSEMENT) {
													$i++;
													$res_disb = $db->Execute("select S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT, CONCAT(M_AR_LEDGER_CODE.CODE,' - ',LEDGER_DESCRIPTION) AS LEDGER, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, STUDENT_ID, ACADEMIC_YEAR, ACADEMIC_PERIOD, IF(DISBURSEMENT_DATE = '0000-00-00','', DATE_FORMAT(DISBURSEMENT_DATE, '%m/%d/%Y' )) AS DISBURSEMENT_DATE, DISBURSEMENT_AMOUNT, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_STUDENT_DISBURSEMENT LEFT JOIN S_TERM_BLOCK ON S_TERM_BLOCK.PK_TERM_BLOCK = S_STUDENT_DISBURSEMENT.PK_TERM_BLOCK LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE, S_STUDENT_MASTER LEFT JOIn S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER AND S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT' AND S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' "); 
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
															<b><?=DESCRIPTION ?></b>
														</div>
														<div class="col-6 col-sm-6 form-group">
															<?=$res_disb->fields['LEDGER']?>
														</div>
													</div>
													<div class="row">
														<div class="col-6 col-sm-6 form-group">
															<b><?=DUE_DATE?></b>
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
											<? //} ?>
										
											<? $style 			= "";
											$pay_style 		= "display:none";
											$add_card_style = "display:inline";
											if($res_card->RecordCount() > 0) { 
												$style 			= "display:none"; 
												$pay_style 		= "display:inline";
												$add_card_style = "display:none"; ?>
												<div class="row form-group" >
													<div class="col-md-6 align-self-center">
														<select id="PK_STUDENT_CREDIT_CARD" name="PK_STUDENT_CREDIT_CARD" class="form-control required-entry" onchange="show_cc_field(this.value)" >
															<option value="" ></option>
															<option value="-1" >Add New</option>
															<? $res_type = $db->Execute("select * from S_STUDENT_CREDIT_CARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' ORDER BY PK_STUDENT_CREDIT_CARD DESC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_STUDENT_CREDIT_CARD'] ?>" ><?=$res_type->fields['CARD_NO'].' - '.$res_type->fields['CARD_EXP'].' - '.$res_type->fields['CARD_TYPE'].' - '.$res_type->fields['NAME_ON_CARD']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span>
														<label for="PK_STUDENT_CREDIT_CARD"><?=CARD_NO?></label>
													</div>
												</div>
											<? } ?>
											
											<div id="CC_FIELDS" style="<?=$style?>" >
												<div class="row form-group" >
													<input type="text" id="card_name" value="" class="form-control required-entry" >
													<span class="bar"></span>
													<label for="card_name"><?=NAME_ON_CARD?></label>
												</div>
												
												<div class="row form-group" id="CREDIT_CARD_NO_DIV" >
													<input type="text" id="card_number" value="" class="form-control required-entry" >
													<span class="bar"></span>
													<label for="card_number"><?=CARD_NO?></label>
												</div>
												
												<div class="row form-group" id="EXP_DATE_DIV" >
													<div class="col-md-6 col-sm-6">
														<input type="text" id="card_exp" value="" class="form-control required-entry" >
														<span class="bar"></span>
														<label for="card_exp"><?=CARD_EXP?></label>
													</div>
													
													<div class="col-md-6 col-sm-6">
														<input type="text" id="card_cvv" value="" class="form-control required-entry">
														<span class="bar"></span>
														<label for="card_cvv"><?=CVV?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-3 col-sm-3">
												</div>
												
												<div class="col-9 col-sm-9">
													<input type="hidden" name="response" id="response" value="" >
													<input type="hidden" id="publisher_name" value="<?=$PUBLISHER_NAME?>">
													<input type="hidden" id="mode" value="auth">
													<input type="hidden" id="convert" value="underscores">
													
													<input type="hidden" id="card_amount" value="0" >
													<input type="hidden" id="NEW_CARD_ID" name="NEW_CARD_ID" value="0" >
														
													<button id="PAY_BTN" type="button" onClick="validate_payment_form(1);" class="btn waves-effect waves-light btn-info" style="<?=$pay_style?>" ><?=MAKE_PAYMENT ?></button>
												
													<button id="ADD_CARD_BTN" type="button" class="btn waves-effect waves-light btn-info" onClick="validate_payment_form(2);" style="<?=$add_card_style?>" ><?=ADD_CARD_MAKE_PAYMENT?></button>
												
													<button type="button" onclick="window.location.href='<?=$URL?>'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
												</div>
											</div>
											
										</div>
										<div class="col-6">
											
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
				url: "add_card.php",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					//alert(data)
					data = data.split("|||")
					if(data[0] == 1) {
						document.getElementById('NEW_CARD_ID').value = data[1]
						<? if($CHARGE_PROCESSING_FEE_FROM_STUDENT == 1){ ?>
							get_processing_fee()
						<? } else { ?>
							document.form1.submit();
						<? } ?>
					} else
						alert(data[1])
				}		
			}).responseText;

		})
	});
	
	function delete_card_popup(id){
		jQuery(document).ready(function($) {
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
		});
	}
	function conf_delete_card_popup(val){
		jQuery(document).ready(function($) {
			if(val == 1) {
				window.location.href = "make_payment.php?id=<?=$_GET['id']?>&page=<?=$_GET['page']?>&act=del_cc&iid="+$("#DELETE_ID").val();
			}
			$("#deleteModal").modal("hide");
		});
	}
	
	function set_as_primary(id){
		window.location.href = "make_payment.php?id=<?=$_GET['id']?>&page=<?=$_GET['page']?>&act=pri&iid="+id;
	}
	
	function validate_payment_form(type) {
		jQuery(document).ready(function($) {
			
			flag = 1;
			
			var add_new_card = 0;
			if(document.getElementById('PK_STUDENT_CREDIT_CARD')){
				if(document.getElementById('PK_STUDENT_CREDIT_CARD').value == -1)
					add_new_card = 1;
			} else
				add_new_card = 1;
			
			if(add_new_card == 1){
				if(document.getElementById('card_name')){
					if(document.getElementById('card_name').value == ''){
						flag = 0;
						$("#card_name").addClass("validation-failed")
						$("#advice-required-entry-card_name").remove()
						$("#card_name").parent().append('<div class="validation-advice" id="advice-required-entry-card_name'+'" style="">This is a required field.</div>')
					} else {
						$("#card_name").removeClass("validation-failed")
						$("#advice-required-entry-card_name").remove()
					}
				}
			
				if(document.getElementById('card_number')){
					if(document.getElementById('card_number').value == ''){
						flag = 0;
						$("#card_number").addClass("validation-failed")
						$("#advice-required-entry-card_number").remove()
						$("#card_number").parent().append('<div class="validation-advice" id="advice-required-entry-card_number'+'" style="">This is a required field.</div>')
					} else {
						$("#card_number").removeClass("validation-failed")
						$("#advice-required-entry-card_number").remove()
					}
				}
			
				if(document.getElementById('card_exp')){
					if(document.getElementById('card_exp').value == ''){
						flag = 0;
						$("#card_exp").addClass("validation-failed")
						$("#advice-required-entry-card_exp").remove()
						$("#card_exp").parent().append('<div class="validation-advice" id="advice-required-entry-card_exp'+'" style="">This is a required field.</div>')
					} else {
						$("#card_exp").removeClass("validation-failed")
						$("#advice-required-entry-card_exp").remove()
					}
				}
			
				if(document.getElementById('card_cvv')){
					if(document.getElementById('card_cvv').value == ''){
						flag = 0;
						$("#card_cvv").addClass("validation-failed")
						$("#advice-required-entry-card_cvv").remove()
						$("#card_cvv").parent().append('<div class="validation-advice" id="advice-required-entry-card_cvv'+'" style="">This is a required field.</div>')
					} else {
						$("#card_cvv").removeClass("validation-failed")
						$("#advice-required-entry-card_cvv").remove()
					}
				}
			} else {
				if(document.getElementById('PK_STUDENT_CREDIT_CARD')){
					if(document.getElementById('PK_STUDENT_CREDIT_CARD').value == ''){
						flag = 0;
						$("#PK_STUDENT_CREDIT_CARD").addClass("validation-failed")
						$("#advice-required-entry-PK_STUDENT_CREDIT_CARD").remove()
						$("#PK_STUDENT_CREDIT_CARD").parent().append('<div class="validation-advice" id="advice-required-entry-PK_STUDENT_CREDIT_CARD'+'" style="">This is a required field.</div>')
					} else {
						$("#PK_STUDENT_CREDIT_CARD").removeClass("validation-failed")
						$("#advice-required-entry-PK_STUDENT_CREDIT_CARD").remove()
					}
				}
			}
			
			if(flag == 1) {
				if(type == 1) {
					<? if($CHARGE_PROCESSING_FEE_FROM_STUDENT == 1){ ?>
						get_processing_fee()
					<? } else { ?>
					document.form1.submit();
					<? } ?>
				} else {
					payment_api.send();	
				}
			}
		});
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
				
			var value = $.ajax({
				url: "../school/ajax_calc_cc_transaction_charge",	
				type: "POST",		 
				data: 'id='+card_id+'&amt=<?=$AMOUNT?>',		
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
				document.form1.submit();
			}
			$("#feeModal").modal("hide");
		});
	}
	
	function show_cc_field(val){
		if(val == -1) {
			document.getElementById('CC_FIELDS').style.display = 'block'
			
			document.getElementById('ADD_CARD_BTN').style.display 	= 'inline'
			document.getElementById('PAY_BTN').style.display 		= 'none'
		} else {
			document.getElementById('CC_FIELDS').style.display = 'none'
			
			document.getElementById('ADD_CARD_BTN').style.display 	= 'none'
			document.getElementById('PAY_BTN').style.display 		= 'inline'
		}
	}
	</script>
	
</body>

</html>