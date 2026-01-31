<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/ecm_ledger.php");
require_once("../school/function_student_ledger.php"); 
require_once("../school/function_update_disbursement_status.php");

require_once("check_access.php");

$res_add_on = $db->Execute("SELECT ECM FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

if(check_access('MANAGEMENT_TITLE_IV_SERVICER') == 0 || $res_add_on->fields['ECM'] == 0){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res_disb = $db->Execute("SELECT S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE  FROM 
	S_ECM_PROCESSOR_DETAIL, S_STUDENT_DISBURSEMENT  
	WHERE 
	S_ECM_PROCESSOR_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_ECM_PROCESSOR_DETAIL.PK_ECM_PROCESSOR = '$_GET[id]' AND S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT = S_ECM_PROCESSOR_DETAIL.PK_STUDENT_DISBURSEMENT GROUP BY S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE ");
	while (!$res_disb->EOF) {
		$PK_AR_LEDGER_CODE = $res_disb->fields['PK_AR_LEDGER_CODE'];
		$res_disb_det = $db->Execute("SELECT PK_ECM_PROCESSOR_DETAIL,S_STUDENT_DISBURSEMENT.DISBURSEMENT_AMOUNT, S_ECM_PROCESSOR_DETAIL.PK_STUDENT_DISBURSEMENT, S_ECM_PROCESSOR_DETAIL.PK_AR_LEDGER_CODE, S_ECM_PROCESSOR_DETAIL.PK_STUDENT_MASTER, S_ECM_PROCESSOR_DETAIL.PK_STUDENT_ENROLLMENT  FROM 
		S_ECM_PROCESSOR_DETAIL, S_STUDENT_DISBURSEMENT  
		WHERE 
		S_ECM_PROCESSOR_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_ECM_PROCESSOR_DETAIL.PK_ECM_PROCESSOR = '$_GET[id]' AND S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT = S_ECM_PROCESSOR_DETAIL.PK_STUDENT_DISBURSEMENT AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ");
		$i = 0;
		while (!$res_disb_det->EOF) {  
			$DISBURSEMENT_AMOUNT 	 = $res_disb_det->fields['DISBURSEMENT_AMOUNT'];
			$PK_STUDENT_DISBURSEMENT = $res_disb_det->fields['PK_STUDENT_DISBURSEMENT'];
			$PK_AR_LEDGER_CODE 		 = $res_disb_det->fields['PK_AR_LEDGER_CODE'];
			$PK_STUDENT_MASTER 		 = $res_disb_det->fields['PK_STUDENT_MASTER'];
			$PK_STUDENT_ENROLLMENT 	 = $res_disb_det->fields['PK_STUDENT_ENROLLMENT'];
			$PK_ECM_PROCESSOR_DETAIL = $res_disb_det->fields['PK_ECM_PROCESSOR_DETAIL'];
			
			if($i == 0){
				$res_acc = $db->Execute("SELECT PAYMENT_BATCH_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
				$PAYMENT_BATCH_MASTER['BATCH_NO'] 			= 'P'.$res_acc->fields['PAYMENT_BATCH_NO'];
				$PAYMENT_BATCH_MASTER['DATE_RECEIVED'] 	 	= date('Y-m-d');
				$PAYMENT_BATCH_MASTER['COMMENTS'] 		 	= 'ECM Import';
				$PAYMENT_BATCH_MASTER['PK_AR_LEDGER_CODE'] 	= $PK_AR_LEDGER_CODE;
				$PAYMENT_BATCH_MASTER['PK_BATCH_STATUS']	= 1;
				$PAYMENT_BATCH_MASTER['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
				$PAYMENT_BATCH_MASTER['CREATED_BY'] 		= $_SESSION['PK_USER'];
				$PAYMENT_BATCH_MASTER['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_PAYMENT_BATCH_MASTER', $PAYMENT_BATCH_MASTER, 'insert');
				$PK_PAYMENT_BATCH_MASTER = $db->insert_ID();
				
				$NEW_BATCH_NO = $res_acc->fields['PAYMENT_BATCH_NO'] + 1;
				$db->Execute("UPDATE Z_ACCOUNT SET PAYMENT_BATCH_NO = '$NEW_BATCH_NO' WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
			}
			
			$res_st = $db->Execute("select PK_STUDENT_ENROLLMENT,ENROLLMENT_PK_TERM_BLOCK from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' AND IS_ACTIVE_ENROLLMENT = 1");
			$ENROLLMENT_PK_TERM_BLOCK 	= $res_st->fields['ENROLLMENT_PK_TERM_BLOCK'];
			
			$PAYMENT_BATCH_DETAIL['PK_TERM_BLOCK'] 				= $ENROLLMENT_PK_TERM_BLOCK;
			$PAYMENT_BATCH_DETAIL['PK_STUDENT_MASTER']  		= $PK_STUDENT_MASTER;
			$PAYMENT_BATCH_DETAIL['PK_STUDENT_ENROLLMENT']  	= $PK_STUDENT_ENROLLMENT;
			$PAYMENT_BATCH_DETAIL['PK_PAYMENT_BATCH_MASTER']  	= $PK_PAYMENT_BATCH_MASTER;
			$PAYMENT_BATCH_DETAIL['PK_STUDENT_DISBURSEMENT']  	= $PK_STUDENT_DISBURSEMENT;
			$PAYMENT_BATCH_DETAIL['DUE_AMOUNT']  				= $DISBURSEMENT_AMOUNT;
			$PAYMENT_BATCH_DETAIL['RECEIVED_AMOUNT']  			= $DISBURSEMENT_AMOUNT;
			$PAYMENT_BATCH_DETAIL['PK_BATCH_PAYMENT_STATUS'] 	= 2;
			$PAYMENT_BATCH_DETAIL['BATCH_TRANSACTION_DATE'] 	= $PAYMENT_BATCH_MASTER['DATE_RECEIVED'];
			$PAYMENT_BATCH_DETAIL['PK_ACCOUNT']  				= $_SESSION['PK_ACCOUNT'];
			$PAYMENT_BATCH_DETAIL['CREATED_BY'] 				= $_SESSION['PK_USER'];
			$PAYMENT_BATCH_DETAIL['CREATED_ON']  				= date("Y-m-d H:i");
			db_perform('S_PAYMENT_BATCH_DETAIL', $PAYMENT_BATCH_DETAIL, 'insert');
			$PK_PAYMENT_BATCH_DETAIL = $db->insert_ID();
			
			$STUDENT_DISBURSEMENT['PK_DISBURSEMENT_STATUS']  	= 3; 
			$STUDENT_DISBURSEMENT['PK_DETAIL_TYPE']  		 	= 4; 
			$STUDENT_DISBURSEMENT['PK_PAYMENT_BATCH_DETAIL'] 	= $PK_PAYMENT_BATCH_DETAIL;
			db_perform('S_STUDENT_DISBURSEMENT', $STUDENT_DISBURSEMENT, 'update'," PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			
			$ledger_data['PK_PAYMENT_BATCH_DETAIL'] = $PK_PAYMENT_BATCH_DETAIL;
			$ledger_data['PK_STUDENT_DISBURSEMENT'] = $PK_STUDENT_DISBURSEMENT;
			$ledger_data['PK_AR_LEDGER_CODE'] 		= $PK_AR_LEDGER_CODE;
			$ledger_data['AMOUNT'] 					= $PAYMENT_BATCH_DETAIL['RECEIVED_AMOUNT'];
			$ledger_data['DATE'] 					= $PAYMENT_BATCH_MASTER['DATE_RECEIVED'];
			$ledger_data['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
			$ledger_data['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
			$ledger_data['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
			student_ledger($ledger_data);
			
			
			$ECM_PROCESSOR_DETAIL['PK_PAYMENT_BATCH_DETAIL'] = $PK_PAYMENT_BATCH_DETAIL;
			db_perform('S_ECM_PROCESSOR_DETAIL', $ECM_PROCESSOR_DETAIL, 'update'," PK_ECM_PROCESSOR_DETAIL = '$PK_ECM_PROCESSOR_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			
			$i++;
			$res_disb_det->MoveNext();
		}
		
		$res_st = $db->Execute("select SUM(RECEIVED_AMOUNT) as RECEIVED_AMOUNT FROM S_PAYMENT_BATCH_DETAIL WHERE PK_PAYMENT_BATCH_MASTER = '$PK_PAYMENT_BATCH_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		$PAYMENT_BATCH_MASTER1['AMOUNT'] = $res_st->fields['RECEIVED_AMOUNT'];
		db_perform('S_PAYMENT_BATCH_MASTER', $PAYMENT_BATCH_MASTER1, 'update'," PK_PAYMENT_BATCH_MASTER = '$PK_PAYMENT_BATCH_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		$res_disb->MoveNext();
	}
	
	$ECM_PROCESSOR['POSTED'] = 1;
	$ECM_PROCESSOR['POSTED_BY'] = $_SESSION['PK_USER'];
	$ECM_PROCESSOR['POSTED_ON'] = date("Y-m-d H:i");
	db_perform('S_ECM_PROCESSOR', $ECM_PROCESSOR, 'update'," PK_ECM_PROCESSOR = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	header("location:batch_payment?id=".$PK_PAYMENT_BATCH_MASTER);
}
$res_disb = $db->Execute("SELECT POSTED FROM S_ECM_PROCESSOR WHERE PK_ECM_PROCESSOR = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
if($res_disb->RecordCount() == 0) {
	header("location:../index");
	exit;
}
$POSTED = $res_disb->fields['POSTED'];

$CAN_POST = 1;
$res_disb = $db->Execute("SELECT PK_ECM_PROCESSOR_DETAIL FROM S_ECM_PROCESSOR_DETAIL WHERE PK_ECM_PROCESSOR = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_DISBURSEMENT > 0");
if($res_disb->RecordCount() == 0)
	$CAN_POST = 0;
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
	<title><?=ECM_TRANSACTION_IMPORT_RESULT ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=ECM_TRANSACTION_IMPORT_RESULT ?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
									<? if($msg1 != '' ){ ?>
									<div class="row">
										<div class="col-md-2">&nbsp;</div>
                                        <div class="col-md-6" style="color:red">
											<?=$msg1?>
										</div>
                                    </div>
									<? } ?>
									<div class="row">
										<div class="col-md-12">
											<table data-toggle="table" data-mobile-responsive="true" class="table-striped">
												<thead>
													<tr>
														<th ><?=SSN?></th>
														<th ><?=STUDENT?></th>
														<th ><?=IMPORT_RESULT?></th>
														
														<th ><?=LEDGER_CODE_1?></th>
														<th ><?=DISBURSEMENT_DATE?></th>
														<th ><?=DISBURSEMENT_AMOUNT?></th>
														
														<th ><?=ECM_DISBURSEMENT_DATE?></th>
														<th ><?=ECM_DISBURSEMENT_AMOUNT?></th>
														
													</tr>
												</thead>
												<tbody>
													<? $TOT_DISBURSEMENT_AMOUNT = 0;
													$TOT_ECM_DISBURSEMENT_AMOUNT = 0;
													$query = "SELECT S_ECM_PROCESSOR_DETAIL.SSN, CONCAT(LAST_NAME,' ',FIRST_NAME) as NAME,M_AR_LEDGER_CODE.CODE, MESSAGE, S_ECM_PROCESSOR_DETAIL.PK_STUDENT_MASTER, ECM_LEDGER, S_STUDENT_DISBURSEMENT.DISBURSEMENT_DATE, S_ECM_PROCESSOR_DETAIL.DISBURSEMENT_DATE as ECM_DISBURSEMENT_DATE, S_STUDENT_DISBURSEMENT.DISBURSEMENT_AMOUNT, S_ECM_PROCESSOR_DETAIL.DISBURSE_AMOUNT as ECM_DISBURSEMENT_AMOUNT, BATCH_NO
													FROM 
													S_ECM_PROCESSOR_DETAIL 
													LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_ECM_PROCESSOR_DETAIL.PK_STUDENT_MASTER 
													LEFT JOIN S_STUDENT_DISBURSEMENT ON S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT = S_ECM_PROCESSOR_DETAIL.PK_STUDENT_DISBURSEMENT 
													LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE 
													LEFT JOIN S_PAYMENT_BATCH_DETAIL ON S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = S_ECM_PROCESSOR_DETAIL.PK_PAYMENT_BATCH_DETAIL 
													LEFT JOIN S_PAYMENT_BATCH_MASTER ON S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER 
													WHERE 
													S_ECM_PROCESSOR_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_ECM_PROCESSOR_DETAIL.PK_ECM_PROCESSOR = '$_GET[id]'";
													$_SESSION['query'] = $query;
													$res_disb = $db->Execute($query);
													$total = 0;
													while (!$res_disb->EOF) {  
														$PK_STUDENT_MASTER = $res_disb->fields['PK_STUDENT_MASTER'];
														?>
														<tr>
															<td><?=my_decrypt('',$res_disb->fields['SSN']) ?></td>
															<td><?=$res_disb->fields['NAME']?></td>
															<td><?=$res_disb->fields['MESSAGE']?></td>
															<td><?=$res_disb->fields['CODE']?></td>
															<td>
																<? if($res_disb->fields['DISBURSEMENT_DATE'] != '' && $res_disb->fields['DISBURSEMENT_DATE'] != '0000-00-00')
																	echo date("m/d/Y",strtotime($res_disb->fields['DISBURSEMENT_DATE'])); ?>
															</td>
															<td>
																<div style="text-align:right;" >
																	$<?=number_format_value_checker($res_disb->fields['DISBURSEMENT_AMOUNT'],2)?>
																</div>
															</td>
															<td>
																<? if($res_disb->fields['ECM_DISBURSEMENT_DATE'] != '' && $res_disb->fields['ECM_DISBURSEMENT_DATE'] != '0000-00-00')
																	echo date("m/d/Y",strtotime($res_disb->fields['ECM_DISBURSEMENT_DATE'])); ?>
															</td>
															<td>
																<div style="text-align:right;" >
																	$<?=number_format_value_checker($res_disb->fields['ECM_DISBURSEMENT_AMOUNT'],2)?>
																</div>
															</td>
														</tr>
													<?	$TOT_DISBURSEMENT_AMOUNT 	 += $res_disb->fields['DISBURSEMENT_AMOUNT'];
														$TOT_ECM_DISBURSEMENT_AMOUNT += $res_disb->fields['ECM_DISBURSEMENT_AMOUNT'];
														$res_disb->MoveNext();
													} ?>
													<tr>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
														<td><b><?=TOTAL?></b></td>
														<td>
															<div style="text-align:right;" >
																<b>$<?=number_format_value_checker($TOT_DISBURSEMENT_AMOUNT,2)?></b>
															</div>
														</td>
														<td></td>
														<td>
															<div style="text-align:right;" >
																<b>$<?=number_format_value_checker($TOT_ECM_DISBURSEMENT_AMOUNT,2)?></b>
															</div>
														</td>
													</tr>
												</tbody>
											</table>
										</div>
                                    </div>
									<br />
									<div class="row">
                                        <div class="col-md-12">
											<div class="form-group m-b-5 text-right" >
												<? if($POSTED == 0 && $CAN_POST == 1){ ?>
												<button type="submit" name="btn" class="btn waves-effect waves-light btn-info" ><?=POST?></button>
												<? } ?>
												<button type="button" onclick="window.location.href='ecm_import_result_excel'" name="btn" class="btn waves-effect waves-light btn-info"  ><?=EXPORT_TO_EXCEL?></button>
												
												<button type="button" onclick="window.location.href='ecm_import_result_pdf'" name="btn" class="btn waves-effect waves-light btn-info"  ><?=EXPORT_TO_PDF?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='management'" ><?=CANCEL?></button>
											</div>
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
    </div>
   
	<? require_once("js.php"); ?>
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>

</body>

</html>