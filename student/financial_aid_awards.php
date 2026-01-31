<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/financial_aid_awards.php");
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
	<title><?=MNU_FINANCIAL_AID_AWARDS?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_FINANCIAL_AID_AWARDS?>
							<!--<a target="_blank" href="cosmetology_grade_book_labs_pdf" class="btn pdf-color btn-circle" style="padding:0" ><i class="mdi mdi-file-pdf" style="font-size: 27px;" ></i> </a>-->
						</h4>
                    </div>
                </div>	
				
				<form class="floating-labels" method="get" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="card-group">
						<div class="card">
							<div class="card-body">
								<div class="row">
									<div class="col-md-12">
										
										<?	$res_ay = $db->Execute("select ACADEMIC_YEAR from S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE AND AWARD_LETTER = 1 AND TITLE_IV = 1 GROUP BY ACADEMIC_YEAR ORDER BY ACADEMIC_YEAR ASC ");
										while (!$res_ay->EOF) { 
											$ACADEMIC_YEAR = $res_ay->fields['ACADEMIC_YEAR']; ?>
											<table class="table-striped table table-hover" style="width:60%" >
												<tr >
													<td colspan="3" >
														<i style="font-size:25px">
														<?=STUDENT_ACADEMIC_YEAR.': '.$ACADEMIC_YEAR ?>
														</i>
													</td>
												</tr>
												<tr >
													<td style="width:70%" ><b><?=AWARD?></b></td>
													<td style="width:30%" >
														<div style="text-align:right" >
															<b><?=AMOUNT?></b>
														</div>
													</td>
												</tr>
												<?	$TOTAL_AMOUNT = 0;
												$res_course_schedule = $db->Execute("select ACADEMIC_YEAR,CODE,SUM(DISBURSEMENT_AMOUNT) as DISBURSEMENT_AMOUNT from S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE AND AWARD_LETTER = 1 AND TITLE_IV = 1 AND ACADEMIC_YEAR = '$ACADEMIC_YEAR' GROUP BY M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE ORDER BY DISBURSEMENT_DATE ASC, M_AR_LEDGER_CODE.CODE ASC ");
												while (!$res_course_schedule->EOF) { 
													$TOTAL_AMOUNT += $res_course_schedule->fields['DISBURSEMENT_AMOUNT']; ?>
													<tr >
														<td ><?=$res_course_schedule->fields['CODE']?></td>
														<td >
															<div style="text-align:right" >
																<?=number_format_value_checker($res_course_schedule->fields['DISBURSEMENT_AMOUNT'],2) ?>
															</div>
														</td>
													</tr> 
													<? $res_course_schedule->MoveNext();
												} ?>
												<tr >
													<td style="width:70%" ><b><?=TOTAL_AWARDS?></b></td>
													<td style="width:30%" >
														<div style="text-align:right" >
															<b><?=number_format_value_checker($TOTAL_AMOUNT,2) ?></b>
														</div>
													</td>
												</tr>
											</table>
										<? $res_ay->MoveNext();
										} ?>
									</div> 
								</div>
							</div>
						</div>
					</div>
				</form>
            </div>
        </div>
        <? require_once("footer.php"); ?>		
    </div>
    <? require_once("js.php"); ?>
	
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
</body>
</html>