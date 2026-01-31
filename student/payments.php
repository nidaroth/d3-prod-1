<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/menu.php");
require_once("../language/make_payment.php");

//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_USER_TYPE'] != 3 ){ 
	header("location:../index");
	exit;
}
$res_pay_access = $db->Execute("select ENABLE_DIAMOND_PAY from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");


// DIAM-2101
$res_enable = $db->Execute("select ENABLE_AUTO_PAYMENT from S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]'");

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
	<title><?=MNU_PAYMENT_SCHEDULE?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-10 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_PAYMENT_SCHEDULE?></h4>
                    </div>
					<? if($res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 1){ 
						?>
					<div class="col-md-2 text-right">
                       <a href="payment_info" class="btn waves-effect waves-light btn-info" ><?=PAYMENT_INFO ?></a>
                    </div>
					<? }
					else if($res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 2)
					{
						?>
					<div class="col-md-2 text-right">
                       <a href="payment_info_stax" class="btn waves-effect waves-light btn-info" ><?=PAYMENT_INFO ?></a>
                    </div>
					<?

					} ?>
                </div>	
				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
								<div class="col-md-9 "></div>
								<div class="col-md-3 ">
									<? if($res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 1 || $res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 2){ ?>
									<div class="d-flex">
										<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
											<input type="checkbox" class="custom-control-input" id="ENABLE_AUTO_PAYMENT" name="ENABLE_AUTO_PAYMENT" value="1" onclick="enable_auto_payment()" <? if($res_enable->fields['ENABLE_AUTO_PAYMENT'] == 1) echo "checked" ?> >
											<label class="custom-control-label" for="ENABLE_AUTO_PAYMENT"><?=ENABLE_AUTO_PAYMENT?></label>
										</div>
									</div>
									<? } ?>
								</div>
								<div class="col-md-12">
									<table data-toggle="table" data-mobile-responsive="true" class="table-striped" id="disbursement_table" >
										<thead>
											<tr>
												<th ><?=DUE_DATE?></th>
												<th ><?=DESCRIPTION?></th>
												<th ><?=AY?></th>
												<th ><?=AP?></th>
												<th ><?=AMOUNT?></th>
												<th ><?=PAID_DATE?></th>
												<th ><?=RECEIPT?></th>
												<th >Payment Mode</th>
												<th ><?=OPTION?></th>
											</tr>
										</thead>
										<tbody>
											<? $res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
											$PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
											
											/*$res_prog_fee = $db->Execute("select S_STUDENT_DISBURSEMENT.*,CODE,LEDGER_DESCRIPTION from S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND INVOICE = 1");*/
											$res_prog_fee = $db->Execute("select S_STUDENT_DISBURSEMENT.*,CODE,LEDGER_DESCRIPTION from S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]'  AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND INVOICE = 1 AND PK_DISBURSEMENT_STATUS IN(1,2,3) ORDER BY DISBURSEMENT_DATE"); //DIAM-2238 for show all enrollment dis
													
											
											$total = 0;
											while (!$res_prog_fee->EOF) { 
												$total += $res_prog_fee->fields['DISBURSEMENT_AMOUNT']; 
												
												$PK_STUDENT_DISBURSEMENT = $res_prog_fee->fields['PK_STUDENT_DISBURSEMENT'];
												$res_bat = $db->Execute("SELECT PK_PAYMENT_BATCH_DETAIL, RECEIPT_NO, S_PAYMENT_BATCH_MASTER.COMMENTS FROM S_PAYMENT_BATCH_MASTER, S_PAYMENT_BATCH_DETAIL WHERE S_PAYMENT_BATCH_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT' AND S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER AND PK_BATCH_STATUS = 2 ");  ?>
												<tr >
													<td>
														<? if($res_prog_fee->fields['DISBURSEMENT_DATE'] != '0000-00-00')
															echo date("m/d/Y",strtotime($res_prog_fee->fields['DISBURSEMENT_DATE'])); ?>
													</td>
													<td >
														<?=$res_prog_fee->fields['CODE'].' - '.$res_prog_fee->fields['LEDGER_DESCRIPTION']; ?>
													</td>
													<td>
														<?=$res_prog_fee->fields['ACADEMIC_YEAR']?>
													</td>
													<td>
														<?=$res_prog_fee->fields['ACADEMIC_PERIOD']?>
													</td>
													<td>
														<div style="text-align:right" >$ <?=number_format_value_checker($res_prog_fee->fields['DISBURSEMENT_AMOUNT'],2)?></div>
													</td>
													<td>
														<? if($res_prog_fee->fields['DEPOSITED_DATE'] != '0000-00-00')
															echo date("m/d/Y",strtotime($res_prog_fee->fields['DEPOSITED_DATE'])); ?>
													</td>
													<td>
														<? if($res_bat->RecordCount() > 0){ ?>
															<a href="receipt_pdf?did=<?=$res_bat->fields['PK_PAYMENT_BATCH_DETAIL']?>"><?=$res_bat->fields['RECEIPT_NO']?></a>
														<? } ?>
													</td>
													<!-- DIAM-2101 -->
													<td>
														<?
															$pattern = "/Automated Recurring/i";
															$pattern2 = "/CC Payments/i";
															if(preg_match($pattern,$res_bat->fields['COMMENTS']))
															{
																echo "Auto";
															}
															else if(preg_match($pattern2,$res_bat->fields['COMMENTS']))
															{
																echo "Manual";
															}
															// else
															// {

															// 	$current_date = date("Y-m-d");
															// 	if($res_prog_fee->fields['DISBURSEMENT_DATE'] != '0000-00-00')
															// 	{
															// 		$disb_date = date("Y-m-d",strtotime($res_prog_fee->fields['DISBURSEMENT_DATE']));
															// 	}
															// 	if($res_enable->fields['ENABLE_AUTO_PAYMENT'] == 1 && ($current_date <= $disb_date) )
															// 	{
															// 		echo "Auto";
															// 	}
															// 	else{
															// 		echo "Manual";
															// 	}

																
															// }
															
														?>
													</td>
													<!-- End DIAM-2101 -->
													<td>
														<?  
															
															if($res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 1 && $res_prog_fee->fields['PK_DISBURSEMENT_STATUS'] == 2)
															{
																?>
																<a href="make_payment.php?id=<?=$PK_STUDENT_DISBURSEMENT ?>&page=p" >Make Payment</a>
																<? 
															}
															else if($res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 2 && $res_prog_fee->fields['PK_DISBURSEMENT_STATUS'] == 2)
															{

																$current_date = date("Y-m-d");
																if($res_prog_fee->fields['DISBURSEMENT_DATE'] != '0000-00-00')
																{
																	$disb_date = date("Y-m-d",strtotime($res_prog_fee->fields['DISBURSEMENT_DATE']));
																}

																if($current_date <= $disb_date)
																{
																	$make_payment = "Make Early Payments";
																}
																else{
																	$make_payment = "Make Payments";
																}

																?>
																<a href="make_payment_stax.php?id=<?=$PK_STUDENT_DISBURSEMENT ?>&page=p" ><?=$make_payment?></a>
															<? 
															}// ENABLE_DIAMOND_PAY = 3: CYBERSOURCE (NUEVO)
                                                            else if($res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 3 && $res_prog_fee->fields['PK_DISBURSEMENT_STATUS'] == 2) {
                                                                ?>
                                                                <a href="make_payment_cybersource.php?id=<?=$PK_STUDENT_DISBURSEMENT?>&page=p">Make Payment</a>
                                                                <?
                                                            }
															 
													     ?>
													</td>
												</tr>
											<?	$res_prog_fee->MoveNext();
											} ?>
											<tr>
												<td ></td>
												<td ></td>
												<td ></td>
												<td>
													<b><?=TOTAL?></b>
												</td>
												<td>
													<div style="text-align:right" ><b>$ <?=number_format_value_checker($total,2)?></b></div>
												</td>
												<td ></td>
												<td ></td>
												<td ></td>
												<td ></td>
											</tr>
										</tbody>
									</table>
									<input type="hidden" value="" id="NOTE_TIME" >
									<input type="hidden" value="" id="NOTE_DATE" >
								</div> 
							</div>
                        </div>
                    </div>
				</div>				
            </div>
        </div>
        <? require_once("footer.php"); ?>		
		
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
	
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
	<script language="javascript">
	function enable_auto_payment(){
		jQuery(document).ready(function($) {
			var va1 = '';
			if(document.getElementById('ENABLE_AUTO_PAYMENT').checked == true) {
				$("#enableModal").modal()
			} else {
				$("#disableModal").modal()
			}
		});
	}
	
	function confirm_enable_auto_payment(va1,enable){
		jQuery(document).ready(function($) {
			if(va1 == 1){ 
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
		
				var data = 'va1='+enable+'&NOTE_DATE='+document.getElementById('NOTE_DATE').value+'&NOTE_TIME='+document.getElementById('NOTE_TIME').value;
				var value = $.ajax({
					url: "ajax_enable_auto_payment",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						location.reload(); // DIAM-2101
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
