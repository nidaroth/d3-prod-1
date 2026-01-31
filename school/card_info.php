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
	$db->Execute("DELETE from S_STUDENT_CREDIT_CARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CREDIT_CARD = '$_GET[iid]' ");
	header("location:card_info.php?s_id=".$_GET['s_id']."&eid=".$_GET['eid']."&t=".$_GET['t']);
	exit;
} else if($_GET['act'] == 'pri'){

	// DIAM-2359
	if($_GET['iid'] != "")
	{
		$db->Execute("UPDATE S_STUDENT_CREDIT_CARD SET IS_PRIMARY = 1 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CREDIT_CARD = '$_GET[iid]' ");
	}
	$res_type_pri = $db->Execute("SELECT PK_STUDENT_CREDIT_CARD FROM S_STUDENT_CREDIT_CARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[s_id]' AND PK_STUDENT_CREDIT_CARD != '$_GET[iid]' ");
	if($res_type_pri->RecordCount() > 0) 
	{
		while (!$res_type_pri->EOF) 
		{
			$PK_STUDENT_CREDIT_CARD = $res_type_pri->fields['PK_STUDENT_CREDIT_CARD'];
			$db->Execute("UPDATE S_STUDENT_CREDIT_CARD SET IS_PRIMARY = 0 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[s_id]' AND PK_STUDENT_CREDIT_CARD = '$PK_STUDENT_CREDIT_CARD' ");

			$res_type_pri->MoveNext();
		}
	}
	// $db->Execute("UPDATE S_STUDENT_CREDIT_CARD SET IS_PRIMARY = 0 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_SESSION[s_id]' ");
	// $db->Execute("UPDATE S_STUDENT_CREDIT_CARD SET IS_PRIMARY = 1 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CREDIT_CARD = '$_GET[iid]' ");
	// End DIAM-2359

	header("location:card_info.php?s_id=".$_GET['s_id']."&eid=".$_GET['eid']."&t=".$_GET['t']);
	exit;
}

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
	<title><?=ADD_CARD_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-6 align-self-center">
                        <h4 class="text-themecolor"><?=ADD_CARD_TITLE?></h4>
                    </div>
					<? $res = $db->Execute("select * from S_STUDENT_CREDIT_CARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[s_id]' ");
					if($res->RecordCount() > 0){ ?>
					<div class="col-md-6 align-self-center">
                        <h4 class="text-themecolor"><?=CARD_ON_FILE?></h4>
                    </div>
					<? } ?>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								
								<div class="p-20">
									<div class="row">
										<div class="col-5">
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<input type="hidden" name="response" id="response" value="" >
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
											
											<? if($res->RecordCount() > 0){ ?>
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group">
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="IS_PRIMARY" name="IS_PRIMARY" value="1" >
															<label class="custom-control-label" for="IS_PRIMARY"><?=IS_PRIMARY?></label>
														</div>
													</div>
												</div>
											</div>
											<? } ?>
											
											<div class="row">
												<div class="col-3 col-sm-3">
												</div>
												
												<div class="col-9 col-sm-9">
													<button type="button" class="btn waves-effect waves-light btn-info" onClick="creditCardValidation();payment_api.send();" ><?=SAVE?></button>
													<button type="button" onclick="window.location.href='student?t=<?=$_GET['t']?>&eid=<?=$_GET['eid']?>&id=<?=$_GET['s_id']?>&tab=ledgerTab'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
												</div>
											</div>
										</div>
										<div class="col-7">
											<div class="row">
												<div class="col-md-6 "></div>
												<div class="col-md-6">
													<? if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 1){ ?>
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
															<? $res = $db->Execute("select ENABLE_AUTO_PAYMENT from S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_GET[s_id]'"); ?>
															<input type="checkbox" class="custom-control-input" id="ENABLE_AUTO_PAYMENT" name="ENABLE_AUTO_PAYMENT" value="1" onclick="enable_auto_payment()" <? if($res->fields['ENABLE_AUTO_PAYMENT'] == 1) echo "checked" ?> >
															<label class="custom-control-label" for="ENABLE_AUTO_PAYMENT"><?=ENABLE_AUTO_PAYMENT?></label>
														</div>
													</div>
													<? } ?>
												</div>
											</div>
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
												<? $res_type = $db->Execute("select * from S_STUDENT_CREDIT_CARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[s_id]' ORDER BY PK_STUDENT_CREDIT_CARD DESC");
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
																<a href="javascript:void(0);" onclick="set_as_primary('<?=$res_type->fields['PK_STUDENT_CREDIT_CARD']?>')" title="<?=SET_AS_PRIMARY?>" ><?=SET_AS_PRIMARY?></a>
															<? } ?>
														</td>
														<td>
															<a href="javascript:void(0);" onclick="delete_card_popup('<?=$res_type->fields['PK_STUDENT_CREDIT_CARD']?>')" title="<?=DELETE_CARD?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
														</td>
													</tr>
												<?	$res_type->MoveNext();
												} ?>
												</tbody>
											</table>
											<input type="hidden" value="" id="NOTE_TIME" >
											<input type="hidden" value="" id="NOTE_DATE" >
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

	<script language="javascript" src="https://pay1.plugnpay.com/api/iframe/<?=$SITE_KEY?>/client/"></script>
	<script language="javascript">
	jQuery(document).ready(function($) {
		payment_api.setCallback(function(data) {
			var IS_PRIMARY = 1;
			if(document.getElementById('IS_PRIMARY')) {
				if(document.getElementById('IS_PRIMARY').checked == true)
					IS_PRIMARY = 1;
				else
					IS_PRIMARY = 0;
			}
			// 'data' is the querystring returned by the payment request.
			// Perform any response handling you would like to do here.
			// For example, such as putting the value of data into a field
			//   and calling submit on the form.
			//alert(data);
			data = data+'&s_id=<?=$_GET['s_id']?>&IS_PRIMARY='+IS_PRIMARY
		
			var value = $.ajax({
				url: "add_card.php",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					//alert(data)
					data = data.split("|||")
					if(data[0] == 1)
						location.reload(); 
					else
						alert(data[1])
				}		
			}).responseText;
		})
		
		timenow()
	});

	function creditCardValidation()
	{

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
				
				// DIAM-1090
				var date = document.getElementById('card_exp').value;
				let dateformat = /^[0-9]{1,2}\/[0-9]{2}$/; // mm/YYYY
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
							$("#card_exp").removeClass("validation-failed");
							$("#advice-required-entry-card_exp").remove();

							// alert("valid Month!");
							//return true;
						}
						else{
							flag = 0;
							$("#card_exp").addClass("validation-failed");
							$("#advice-required-entry-card_exp").remove();
							$("#card_exp").parent().append('<div class="validation-advice" id="advice-required-entry-card_exp'+'" style="">Please enter month, greater than equal to current month!</div>');

							// alert("Please enter month, greater than equal to current month!");
							// return false;
						}

						// YearWise
						const strDate = new Date();
						let current_year = strDate.getFullYear();
						var twoDigitYear = current_year.toString().substr(-2);
						if(year >= twoDigitYear)
						{
							$("#card_exp").removeClass("validation-failed");
							$("#advice-required-entry-card_exp").remove();

							// alert("Valid Year!");
							// return true;
						}
						else{
							flag = 0;
							$("#card_exp").addClass("validation-failed");
							$("#advice-required-entry-card_exp").remove();
							$("#card_exp").parent().append('<div class="validation-advice" id="advice-required-entry-card_exp'+'" style="">Please enter year greater than equal to current year!</div>');

							// alert("Please enter year greater than equal to current year!");
							// return false;
						}
					}
					else{
						flag = 0;
						$("#card_exp").addClass("validation-failed");
						$("#advice-required-entry-card_exp").remove();
						$("#card_exp").parent().append('<div class="validation-advice" id="advice-required-entry-card_exp'+'" style="">Please enter valid month!</div>');

						// alert("Please enter valid month!");
						// return false;
					}
					
				} 
				else {
					flag = 0;
					$("#card_exp").addClass("validation-failed");
					$("#advice-required-entry-card_exp").remove();
					$("#card_exp").parent().append('<div class="validation-advice" id="advice-required-entry-card_exp'+'" style="">Invalid Card Expiry Format!</div>');

					// alert("Invalid Card Expiry Format!");
					// return false;
				}
				// End DIAM-1090
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
				window.location.href = "card_info.php?s_id=<?=$_GET['s_id']?>&eid=<?=$_GET['eid']?>&t=<?=$_GET['t']?>&act=del_cc&iid="+$("#DELETE_ID").val();
			}
			$("#deleteModal").modal("hide");
		});
	}
	
	function set_as_primary(id){
		window.location.href = "card_info.php?s_id=<?=$_GET['s_id']?>&eid=<?=$_GET['eid']?>&t=<?=$_GET['t']?>&act=pri&iid="+id;
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
		
		document.getElementById('NOTE_DATE').value = t[1]+'/'+t[0]+'/'+t[2]
		document.getElementById('NOTE_TIME').value = time
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
	</script>
	
</body>

</html>