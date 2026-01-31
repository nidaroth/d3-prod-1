<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/instructor_dashboard.php");

//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_USER_TYPE'] != 3 ){ 
	header("location:../index");
	exit;
}
$res_pay_access = $db->Execute("select ENABLE_DIAMOND_PAY from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

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
	<title><?=DASHBOARD_PAGE_TITLE?> | <?=$title?></title>
	<style>
		.table th, .table td {padding: 10px;}
	</style>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=WELCOME?> <?=' '.$_SESSION['NAME']?></h4>
                    </div>
                </div>	
		
				<? $cur_time_cet = convert_to_user_date(date('Y-m-d H:i:s'),'Y-m-d H:i:s','CET',date_default_timezone_get());
				
				$res = $db->Execute("SELECT Z_ANNOUNCEMENT.* FROM Z_ANNOUNCEMENT,Z_ANNOUNCEMENT_CAMPUS, Z_ANNOUNCEMENT_FOR WHERE Z_ANNOUNCEMENT.ACTIVE = 1 AND Z_ANNOUNCEMENT_CAMPUS.PK_CAMPUS IN ($_SESSION[PK_CAMPUS]) AND Z_ANNOUNCEMENT.PK_ANNOUNCEMENT = Z_ANNOUNCEMENT_CAMPUS.PK_ANNOUNCEMENT AND Z_ANNOUNCEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND '$cur_time_cet' BETWEEN START_DATE_TIME_CET AND END_DATE_TIME_CET AND Z_ANNOUNCEMENT_FOR.PK_ANNOUNCEMENT = Z_ANNOUNCEMENT.PK_ANNOUNCEMENT AND PK_ANNOUNCEMENT_FOR_MASTER = 3 GROUP BY  Z_ANNOUNCEMENT.PK_ANNOUNCEMENT");
				if($res->RecordCount() > 0) { ?>
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex no-block align-items-center">
                                        <div>
                                            <h5 class="card-title text-uppercase"><?=ANNOUNCEMENT?></h5>
                                        </div>
                                    </div>
                                </div>
								<? while (!$res->EOF){ ?>
                                <div class="col-12">
                                   <a href="announcement_detail.php?id=<?=$res->fields['PK_ANNOUNCEMENT']?>">
										<? if($_SESSION['PK_LANGUAGE'] == 2)
											echo $res->fields['SHORT_DESC_SPA']; 
										else 
											echo $res->fields['SHORT_DESC_ENG'];  ?>
								   </a>
                                </div>
								<? $res->MoveNext();
								} ?>
                            </div>
                        </div>
                    </div>
				</div>
				<? }?>
				
				<div class="row">
					<?/* Ticket # 1511 */
					/*
                    <div class="col-md-6">
						<div class="card-group">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-8">
											<div class="d-flex no-block align-items-center">
												<div>
													<h5 class="card-title"><?=INTERNAL_MESSAGE ?></h5>
												</div>
											</div>
										</div>
										<div class="col-md-4">
											<div class="d-flex no-block align-items-center">
												<a href="compose_mail" style="margin-top: -10px;" class="btn btn-danger btn-block waves-effect waves-light"><?=COMPOSE?></a>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-12">
											<table class="table table-hover">
												<tbody>
													<? $res_mail = $db->Execute("SELECT Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL,PK_INTERNAL_EMAIL_RECEPTION,VIWED, Z_INTERNAL_EMAIL.INTERNAL_ID, SUBJECT, IF(PK_USER_TYPE = 2, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) , IF(PK_USER_TYPE = 3, CONCAT(S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.LAST_NAME) , IF(PK_USER_TYPE = 1, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME),'') )) AS NAME, DATE_FORMAT( Z_INTERNAL_EMAIL_RECEPTION.CREATED_ON, '%m/%d/%Y %r') AS CREATED_ON,Z_USER.PK_USER, Z_INTERNAL_EMAIL.CREATED_BY 
													FROM 
													Z_INTERNAL_EMAIL_RECEPTION ,Z_INTERNAL_EMAIL, Z_USER 
													LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID AND PK_USER_TYPE IN (1,2) 
													LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = Z_USER.ID AND PK_USER_TYPE = 3
													WHERE
													(Z_INTERNAL_EMAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' OR Z_INTERNAL_EMAIL.PK_ACCOUNT = '1') AND 
													Z_INTERNAL_EMAIL_RECEPTION.PK_INTERNAL_EMAIL = Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL AND 
													Z_INTERNAL_EMAIL.CREATED_BY = Z_USER.PK_USER 
													AND 
													PK_INTERNAL_EMAIL_RECEPTION IN (SELECT MAX(PK_INTERNAL_EMAIL_RECEPTION) AS PK_INTERNAL_EMAIL_RECEPTION FROM  Z_INTERNAL_EMAIL_RECEPTION WHERE SELF_ADDED = 0 AND  PK_USER = '$_SESSION[PK_USER]' AND DELETED = 0 GROUP BY INTERNAL_ID) ORDER BY CREATED_ON DESC");
													while (!$res_mail->EOF) {  
														$style = '';
														if($res_mail->fields['VIWED'] == 0)
															$style = 'font-weight:bold;'; ?>
														<tr class="unread" onclick="window.location.href='email?type=&id=<?=$res_mail->fields['INTERNAL_ID']; ?>'" style="cursor: pointer;<?=$style?>" >
															<td class="hidden-xs-down"><?=$res_mail->fields['NAME']; ?></td>
															<td class="max-texts"><?=$res_mail->fields['SUBJECT']; ?></td>
															<td class="hidden-xs-down"><?=$res_mail->fields['ATTACHMENT']; ?></td>
															<td class="text-right"><?=$res_mail->fields['CREATED_ON']; ?> </td>
														</tr>
													<?	$res_mail->MoveNext();
													} ?>
													
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					/* Ticket # 1511 */?>
					
					<? if($_SESSION['PAYMENT_SCHEDULE'] == 1){ //Ticket # 1869  ?>
					<div class="col-md-6">
						<div class="card-group">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-12">
											<div class="d-flex no-block align-items-center">
												<div>
													<h5 class="card-title "><?=PAYMENT_SCHEDULE?></h5>
												</div>
											</div>
										</div>
										<div class="col-12">
											<table class="table table-hover">
												<thead>
													<tr>
														<th ><?=DUE_DATE?></th>
														<th ><?=DESCRIPTION?></th>
														<th ><?=AMOUNT?></th>
														<? if($res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 1 || $res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 2){ ?>
														<th ><?=OPTION?></th>
														<? } ?>
													</tr>
												</thead>
												<tbody>
													<? $res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
													$PK_STUDENT_ENROLLMENT  = $res->fields['PK_STUDENT_ENROLLMENT'];
													$TODAY					= date("Y-m-d");
													//$res_prog_fee = $db->Execute("select S_STUDENT_DISBURSEMENT.*,CODE,LEDGER_DESCRIPTION from S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND INVOICE = 1 AND PK_DISBURSEMENT_STATUS = 2 AND DISBURSEMENT_DATE <= '$TODAY'  ORDER BY DISBURSEMENT_DATE");
													$res_prog_fee = $db->Execute("select S_STUDENT_DISBURSEMENT.*,CODE,LEDGER_DESCRIPTION from S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]'  AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND INVOICE = 1 AND PK_DISBURSEMENT_STATUS = 2   ORDER BY DISBURSEMENT_DATE"); //DIAM-2238 for show all enrollment dis
													
													$total = 0;
													while (!$res_prog_fee->EOF) { 
														$total += $res_prog_fee->fields['DISBURSEMENT_AMOUNT']; ?>
															<tr >
																<td >
																	<? if($res_prog_fee->fields['DISBURSEMENT_DATE'] != '0000-00-00')
																		echo date("m/d/Y",strtotime($res_prog_fee->fields['DISBURSEMENT_DATE'])); ?>
																</td>
																<td >
																	<?=$res_prog_fee->fields['CODE'].' - '.$res_prog_fee->fields['LEDGER_DESCRIPTION']; ?>
																</td>
																<td >
																	<div style="text-align:right" >$ <?=number_format_value_checker($res_prog_fee->fields['DISBURSEMENT_AMOUNT'],2)?></div>
																</td>
																<? if($res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 1 && $res_prog_fee->fields['PK_DISBURSEMENT_STATUS'] == 2){ ?>
																<td >
																	<a href="make_payment.php?id=<?=$res_prog_fee->fields['PK_STUDENT_DISBURSEMENT'] ?>&page=i" >Make Payment</a>
																</td>
																<? }
																else if($res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 2 && $res_prog_fee->fields['PK_DISBURSEMENT_STATUS'] == 2){  // DIAM-2101
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
																	<td >
																		<a href="make_payment_stax.php?id=<?=$res_prog_fee->fields['PK_STUDENT_DISBURSEMENT'] ?>&page=i" ><?=$make_payment?></a>
																	</td>
															 <? } // End DIAM-2101 ?>
															</tr>
													<?	$res_prog_fee->MoveNext();
													} ?>
													
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<? } //Ticket # 1869  ?>
				</div>
            </div>
        </div>
        <? require_once("footer.php"); ?>		
    </div>
    <? require_once("js.php"); ?>
</body>
</html>
