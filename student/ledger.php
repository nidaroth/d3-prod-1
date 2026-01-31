<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/ledger.php");
require_once("../language/menu.php");

//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_USER_TYPE'] != 3 ){ 
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
	<title><?=LEDGER_PAGE_TITLE?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=LEDGER_PAGE_TITLE?></h4>
                    </div>
                </div>	
				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
							<div class="row">
								<div class="col-md-12" style="text-align:right" >
									<a href="../school/ar_ledger_report_pdf" class="btn waves-effect waves-light btn-info" style="margin-bottom:5px" ><?=PDF?></a>
								</div>
							</div>
                            <div class="row">
								<div class="col-md-12">
									<table data-toggle="table" data-mobile-responsive="true" class="table-striped" >
										<thead>
											<tr>
												<th ><?=TRANSACTION_DATE?></th>
												<th ><?=LEDGER_CODE?></th>
												<th ><?=DESCRIPTION?></th>
												<th ><?=RECEIPT_CHECK_NO?></th>
												<th ><div style="padding-top: 11px;width:100%;text-align:right" ><?=DEBIT?></div></th>
												<th ><div style="padding-top: 11px;width:100%;text-align:right" ><?=CREDIT?></div></th>
												<th ><div style="padding-top: 11px;width:100%;text-align:right" ><?=BALANCE?></div></th>
											</tr>
										</thead>
										<tbody>
											<? $BALANCE = 0;
											$TOT_DEBIT	= 0;
											$TOT_CREDIT	= 0;
											$res_ledger = $db->Execute("select PK_STUDENT_LEDGER,LEDGER_DESCRIPTION ,IF(S_STUDENT_LEDGER.TRANSACTION_DATE = '0000-00-00','', DATE_FORMAT(S_STUDENT_LEDGER.TRANSACTION_DATE, '%m/%d/%Y' )) AS TRANSACTION_DATE_1, S_STUDENT_LEDGER.CREDIT, S_STUDENT_LEDGER.DEBIT, M_AR_LEDGER_CODE.CODE, RECEIPT_NO, CHECK_NO,  
											IF(S_STUDENT_LEDGER.PK_PAYMENT_BATCH_DETAIL > 0, S_PAYMENT_BATCH_DETAIL.BATCH_DETAIL_DESCRIPTION, IF(S_STUDENT_LEDGER.PK_MISC_BATCH_DETAIL > 0, S_MISC_BATCH_DETAIL.BATCH_DETAIL_DESCRIPTION, IF(S_STUDENT_LEDGER.PK_TUITION_BATCH_DETAIL > 0, S_TUITION_BATCH_DETAIL.BATCH_DETAIL_DESCRIPTION, ''))) as BATCH_DESCRIPTION
											from 
											S_STUDENT_LEDGER 
											LEFT JOIN S_PAYMENT_BATCH_DETAIL ON S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL = S_STUDENT_LEDGER.PK_PAYMENT_BATCH_DETAIL 
											
											LEFT JOIN S_MISC_BATCH_DETAIL ON S_MISC_BATCH_DETAIL.PK_MISC_BATCH_DETAIL = S_STUDENT_LEDGER.PK_MISC_BATCH_DETAIL 
											LEFT JOIN S_TUITION_BATCH_DETAIL ON S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_DETAIL = S_STUDENT_LEDGER.PK_TUITION_BATCH_DETAIL 
											
											LEFT JOIN M_AR_LEDGER_CODE On M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_LEDGER.PK_AR_LEDGER_CODE 
											WHERE 
											S_STUDENT_LEDGER.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
											(S_STUDENT_LEDGER.PK_PAYMENT_BATCH_DETAIL > 0 OR S_STUDENT_LEDGER.PK_MISC_BATCH_DETAIL > 0 OR S_STUDENT_LEDGER.PK_TUITION_BATCH_DETAIL > 0 ) $led_cond ORDER BY S_STUDENT_LEDGER.TRANSACTION_DATE ASC ");
											while (!$res_ledger->EOF) { 
												$TOT_DEBIT  += $res_ledger->fields['DEBIT'];
												$TOT_CREDIT += $res_ledger->fields['CREDIT'];
												if($res_ledger->fields['DEBIT'] != 0)
													$BALANCE += $res_ledger->fields['DEBIT'];
												if($res_ledger->fields['CREDIT'] != 0)
													$BALANCE -= $res_ledger->fields['CREDIT'];
													
												if(round($BALANCE,2) == 0)
													$BALANCE = abs($BALANCE);
													
												$txt = '';
												if($res_ledger->fields['RECEIPT_NO'] != '' )
													$txt .= 'Receipt # '.$res_ledger->fields['RECEIPT_NO'];
												if($res_ledger->fields['CHECK_NO'] != '') {
													if($res_ledger->fields['RECEIPT_NO'] != '' )
														$txt .= '<br />';
													$txt .= 'Check # '.$res_ledger->fields['CHECK_NO'];
												}
													
												?>
												<tr >
													<td>
														<?=$res_ledger->fields['TRANSACTION_DATE_1'] ?>
													</td>
													<td >
														<?=$res_ledger->fields['CODE'] ?>
													</td>
													<td >
														<?=$res_ledger->fields['BATCH_DESCRIPTION'] ?>
													</td>
													<td >
														<?=$txt ?>
													</td>
													<td>
														<div style="padding-top: 11px;width:100%;text-align:right" >
															$ <?=number_format_value_checker($res_ledger->fields['DEBIT'],2)?>
														</div>
													</td>
													<td>
														<div style="padding-top: 11px;width:100%;text-align:right" >
															$ <?=number_format_value_checker($res_ledger->fields['CREDIT'],2)?>
														</div>
													</td>
													<td>
														<div style="padding-top: 11px;width:100%;text-align:right" >
															$ <?=number_format_value_checker($BALANCE,2)?>
														</div>
													</td>
												</tr>
											<?	$res_ledger->MoveNext();
											} ?>
											<tr>
												<td colspan="4"><div style="padding-top: 11px;width:100%;text-align:right;font-weight:bold;" >Total</div></td>
												<td><div style="padding-top: 11px;width:100%;text-align:right;font-weight:bold;" >$ <?=number_format_value_checker($TOT_DEBIT,2)?></div></td>
												<td><div style="padding-top: 11px;width:100%;text-align:right;font-weight:bold;" >$ <?=number_format_value_checker($TOT_CREDIT,2)?></div></td>
												<td><div style="padding-top: 11px;width:100%;text-align:right;font-weight:bold;" >$ <?=number_format_value_checker($BALANCE,2)?></div></td>
											</tr>
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
	
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
</body>
</html>