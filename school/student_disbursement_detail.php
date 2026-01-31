<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/batch_payment.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
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
	<title><?=DISBURSEMENTS ?> | <?=$title?></title>
	<style>
		.red{color:red !important;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor">
							<?=DISBURSEMENTS ?> 
						</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="table-responsive p-20">
									<div style="text-align:right" >
										<input type="checkbox" name="DO_NOT_SHOW_PAID" id="DO_NOT_SHOW_PAID" value="1" onclick="reload_data()" <? if($_GET['dont'] == 1) echo "checked"; ?> />&nbsp;<?=HIDE_PAID_DISBURSEMENTS?>
										
										<button type="button" onclick="set_disp()" class="btn waves-effect waves-light btn-info"><?=SELECT?></button>
									</div>
									<table data-toggle="table" data-mobile-responsive="true" class="table-striped" id="disbursement_table" >
										<thead>
											<tr>
												<th ><?=LEDGER_CODE?></th>
												<th ><?=AY_1?></th>
												<th ><?=AP_1?></th>
												<th ><?=DISBURSEMENT_DATE?></th>
												<th ><?=DISBURSEMENT_AMOUNT?></th>
												<th ><?=DEPOSITED?></th>
												<th ><?=STATUS?></th>
												<th ><?=BATCH?></th>
												<th ><?=OPTION?></th>
											</tr>
										</thead>
										<tbody>
											<? $cond = "";
											if($_GET['dont'] == 1)
												$cond .= " AND S_STUDENT_DISBURSEMENT.PK_DISBURSEMENT_STATUS = 2 ";
											if($_GET['led'] != '')
												$cond .= " AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = '$_GET[led]' ";
												
											$DISB_ID_ARR = explode(",",$_SESSION['DISB_ID']);
												
											$res_disb = $db->Execute("select S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT, BATCH_NO, CONCAT(M_AR_LEDGER_CODE.CODE,' - ', M_AR_LEDGER_CODE.LEDGER_DESCRIPTION) AS LEDGER, ACADEMIC_YEAR, ACADEMIC_PERIOD, IF(DISBURSEMENT_DATE = '0000-00-00','', DATE_FORMAT(DISBURSEMENT_DATE, '%m/%d/%Y' )) AS DISBURSEMENT_DATE_1,DISBURSEMENT_DATE, DISBURSEMENT_AMOUNT, IF(DEPOSITED_DATE = '0000-00-00','', DATE_FORMAT(DEPOSITED_DATE, '%m/%d/%Y' )) AS DEPOSITED_DATE, DISBURSEMENT_STATUS, BATCH_NO, S_STUDENT_DISBURSEMENT.PK_DISBURSEMENT_STATUS, S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL from S_STUDENT_DISBURSEMENT LEFT JOIN M_DISBURSEMENT_STATUS ON M_DISBURSEMENT_STATUS.PK_DISBURSEMENT_STATUS = S_STUDENT_DISBURSEMENT.PK_DISBURSEMENT_STATUS LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE LEFT JOIN S_PAYMENT_BATCH_DETAIL ON S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL LEFT JOIN S_PAYMENT_BATCH_MASTER ON S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER WHERE S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ");
											while (!$res_disb->EOF) { ?>
												<tr <? if($res_disb->fields['PK_PAYMENT_BATCH_DETAIL'] == 0 && $res_disb->fields['PK_DISBURSEMENT_STATUS'] != 1 && strtotime(date($res_disb->fields['DISBURSEMENT_DATE'])) < strtotime(date("Y-m-d"))) { ?> class="red" <? } ?> >
													<td><?=$res_disb->fields['LEDGER']?></td>
													<td><?=$res_disb->fields['ACADEMIC_YEAR'] ?></td>
													<td><?=$res_disb->fields['ACADEMIC_PERIOD'] ?></td>
													<td><?=$res_disb->fields['DISBURSEMENT_DATE_1']?></td>
													<td><?=$res_disb->fields['DISBURSEMENT_AMOUNT']?></td>
													<td><?=$res_disb->fields['DEPOSITED_DATE']?></td>
													<td><?=$res_disb->fields['DISBURSEMENT_STATUS']?></td>
													<td><?=$res_disb->fields['BATCH_NO']?></td>
													<td>
														<? if($res_disb->fields['PK_DISBURSEMENT_STATUS'] == 2) { 
															$checked = "";
															foreach($DISB_ID_ARR as $DISB_ID_1){
																if($DISB_ID_1 == $res_disb->fields['PK_STUDENT_DISBURSEMENT'])
																	$checked = "checked disabled";
															} ?> 
															
															<input type="checkbox" name="PK_STUDENT_DISBURSEMENT[]" value="<?=$res_disb->fields['PK_STUDENT_DISBURSEMENT']?>" <?=$checked?> >
														<? } ?>
													</td>
												</tr>
											<?	$res_disb->MoveNext();
											} ?>
										</tbody>
									</table>
								</div>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>
	<script type="text/javascript">
		function reload_data(){
			if(document.getElementById('DO_NOT_SHOW_PAID').checked == true)
				window.location.href = 'student_disbursement_detail?id=<?=$_GET['id']?>&dont=1&led=<?=$_GET['led']?>';
			else
				window.location.href = 'student_disbursement_detail?id=<?=$_GET['id']?>&led=<?=$_GET['led'] ?>';
		}
		
		function set_disp(){
			jQuery(document).ready(function($) { 
				var str = '';
				var PK_STUDENT_DISBURSEMENT = document.getElementsByName('PK_STUDENT_DISBURSEMENT[]')
				for(var i = 0 ; i < PK_STUDENT_DISBURSEMENT.length ; i++){
					if(PK_STUDENT_DISBURSEMENT[i].checked == true && PK_STUDENT_DISBURSEMENT[i].disabled == false) {
						if(str != '')
							str += ',';
							
						str += PK_STUDENT_DISBURSEMENT[i].value
					}
				}
				if(str == '')
					alert('Please Select At Least One Record')
				else {
					var data  = 'disb_id='+str+'&add_stud=1&table_id123=<?=$_GET['table_id']?>';
					var value = $.ajax({
						url: "ajax_get_unpaid_students_from_ledger",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							window.opener.change_disb(data,'<?=$_GET['dis_id']?>','<?=$_GET['table_id']?>')
							window.close();
						}		
					}).responseText;
				}
			});
		}
		
		/*function set_disp(id){
			jQuery(document).ready(function($) { 
				var data  = 'disb_id='+id;
				var value = $.ajax({
					url: "ajax_get_unpaid_students_from_ledger",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						window.opener.change_disb(data,'<?=$_GET['dis_id']?>',id)
						window.close();
					}		
				}).responseText;
			});
		}*/
		
	</script>
	
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
</body>

</html>