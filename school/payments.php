<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
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
	<title><?=MNU_PAYMENT_SCHEDULE?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_PAYMENT_SCHEDULE?></h4>
                    </div>
                </div>	
				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
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
												<th ><?=OPTION?></th>
											</tr>
										</thead>
										<tbody>
											<? $res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
											$PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
											
											$res_prog_fee = $db->Execute("select S_STUDENT_DISBURSEMENT.*,CODE,LEDGER_DESCRIPTION from S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND INVOICE = 1");
											
											$total = 0;
											while (!$res_prog_fee->EOF) { 
												$total += $res_prog_fee->fields['DISBURSEMENT_AMOUNT']; ?>
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
														<a href="comming_soon">Make Payment</a>
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