<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("check_access.php");

$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
if($FINANCE_ACCESS != 2 && $FINANCE_ACCESS != 3){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$STUDENT_DISBURSEMENT['PK_AR_LEDGER_CODE'] 		= $_POST['PK_AR_LEDGER_CODE'];
	$STUDENT_DISBURSEMENT['ACADEMIC_YEAR'] 			= $_POST['AY'];
	$STUDENT_DISBURSEMENT['ACADEMIC_PERIOD'] 		= $_POST['AP'];
	$STUDENT_DISBURSEMENT['HOURS_REQUIRED'] 		= $_POST['HOURS_REQUIRED'];
	$STUDENT_DISBURSEMENT['PK_TERM_BLOCK'] 			= $_POST['PK_TERM_BLOCK'];
	$STUDENT_DISBURSEMENT['PK_DETAIL_TYPE'] 		= $_POST['PK_DETAIL_TYPE'];
	$STUDENT_DISBURSEMENT['FUNDS_REQUESTED'] 		= $_POST['FUNDS_REQUESTED'];
	$STUDENT_DISBURSEMENT['DETAIL'] 				= $_POST['DISBURSEMENT_DETAIL_1']; //$_POST['DETAIL']; //DIAM-2003
	$STUDENT_DISBURSEMENT['PK_STUDENT_ENROLLMENT']  = $_GET['eid'];
	$STUDENT_DISBURSEMENT['PK_STUDENT_MASTER'] 	 	= $_GET['id'];
	$STUDENT_DISBURSEMENT['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
	$STUDENT_DISBURSEMENT['CREATED_BY']  			= $_SESSION['PK_USER'];
	$STUDENT_DISBURSEMENT['CREATED_ON']  			= date("Y-m-d H:i");

	$ADD_FREQUENCY = '';	
	if($_POST['PK_PAYMENT_FREQUENCY'] == 1 )
		$ADD_FREQUENCY = " 1 weeks";
	else if($_POST['PK_PAYMENT_FREQUENCY'] == 2 )
		$ADD_FREQUENCY = " 14 days";
	else if($_POST['PK_PAYMENT_FREQUENCY'] == 3 )
		$ADD_FREQUENCY = " 5 weeks";
	else if($_POST['PK_PAYMENT_FREQUENCY'] == 4 )
		$ADD_FREQUENCY = " 1 Months";
	else if($_POST['PK_PAYMENT_FREQUENCY'] == 5 )
		$ADD_FREQUENCY = " 2 Months";
	else if($_POST['PK_PAYMENT_FREQUENCY'] == 6 )
		$ADD_FREQUENCY = " 60 days";
	else if($_POST['PK_PAYMENT_FREQUENCY'] == 7 )
		$ADD_FREQUENCY = " 12 weeks";
	else if($_POST['PK_PAYMENT_FREQUENCY'] == 8 )
		$ADD_FREQUENCY = " 13 weeks";
	else if($_POST['PK_PAYMENT_FREQUENCY'] == 9 )
		$ADD_FREQUENCY = " 20 weeks";
	else if($_POST['PK_PAYMENT_FREQUENCY'] == 10 )
		$ADD_FREQUENCY = " 3 Months";
		
	//$INST_AMT = ceil($_POST['TOTAL_AMOUNT'] / $_POST['NO_OF_PAYMENTS']);
	$INST_AMT = round(($_POST['TOTAL_AMOUNT'] / $_POST['NO_OF_PAYMENTS']),2);
	$TOT_AMT  = 0;
	for($i = 1 ; $i < $_POST['NO_OF_PAYMENTS'] ; $i++){
		if($i == 1)
			$DUE_DATE = date("Y-m-d",strtotime($_POST['FIRST_DATE']));
		else {
			if($_POST['PK_PAYMENT_FREQUENCY'] == 13 ) {
				$YEAR  = date("Y",strtotime($DUE_DATE));
				$MONTH = date("m",strtotime($DUE_DATE)) + 1;
				
				if($MONTH > 12) {
					$MONTH = 1;
					$YEAR++;
				}
				$DUE_DATE = date("Y-m-t",strtotime($YEAR."-".$MONTH.'-01'));
			} else {
				$DUE_DATE = date("Y-m-d",strtotime($DUE_DATE." +".$ADD_FREQUENCY));
			}
		}
		$TOT_AMT += $INST_AMT;
		//echo $DUE_DATE.' --- '.$INST_AMT.'<br />';
		$res_award = $db->Execute("select PK_AWARD_YEAR from M_AWARD_YEAR WHERE ACTIVE = 1 AND '$DUE_DATE' between BEGIN_DATE AND END_DATE");
		
		$STUDENT_DISBURSEMENT['PK_AWARD_YEAR'] 			= $res_award->fields['PK_AWARD_YEAR'];
		$STUDENT_DISBURSEMENT['DISBURSEMENT_DATE'] 		= $DUE_DATE;
		$STUDENT_DISBURSEMENT['DISBURSEMENT_AMOUNT'] 	= $INST_AMT;
		db_perform('S_STUDENT_DISBURSEMENT', $STUDENT_DISBURSEMENT, 'insert');
		
		//echo "<pre>";print_r($STUDENT_DISBURSEMENT);
	}
	if($_POST['PK_PAYMENT_FREQUENCY'] == 13 ) {
		$YEAR  = date("Y",strtotime($DUE_DATE));
		$MONTH = date("m",strtotime($DUE_DATE)) + 1;
		
		if($MONTH > 12) {
			$MONTH = 1;
			$YEAR++;
		}
		$DUE_DATE = date("Y-m-t",strtotime($YEAR."-".$MONTH.'-01'));
	} else {
		$DUE_DATE = date("Y-m-d",strtotime($DUE_DATE." +".$ADD_FREQUENCY));
	}
	
	$res_award = $db->Execute("select PK_AWARD_YEAR from M_AWARD_YEAR WHERE ACTIVE = 1 AND '$DUE_DATE' between BEGIN_DATE AND END_DATE");
	
	$STUDENT_DISBURSEMENT['PK_AWARD_YEAR'] 			= $res_award->fields['PK_AWARD_YEAR'];
	$STUDENT_DISBURSEMENT['DISBURSEMENT_DATE'] 		= $DUE_DATE;
	$STUDENT_DISBURSEMENT['DISBURSEMENT_AMOUNT'] 	= $_POST['TOTAL_AMOUNT'] - $TOT_AMT;
	db_perform('S_STUDENT_DISBURSEMENT', $STUDENT_DISBURSEMENT, 'insert');
	//echo "<pre>";print_r($STUDENT_DISBURSEMENT);exit;
	?>
	<script type="text/javascript">window.opener.go_to_disburment(this)</script>
<? } ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=MULTIPLE_DISBURSEMENT?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper" style="padding-top: 0;" >
            <div class="container-fluid">
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<form class="floating-labels" method="post" name="form1" id="form1" >
									<div class="row">
										<div class="col-md-12" style="background-color:#022561;color:#FFFFFF;" >
											<center><b><?=AWARDS?></b><center>
										</div>
									</div>
									<br />
									<div class="row">
										<div class="col-md-12">
											<div class="row form-group">
												<select id="PK_AR_LEDGER_CODE" name="PK_AR_LEDGER_CODE" class="form-control required-entry" >
													<option selected></option>
													 <? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_AR_LEDGER_CODE'] ?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['LEDGER_DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_AR_LEDGER_CODE"><?=LEDGER_CODE?></label>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<div class="row form-group">
												<input type="text" class="form-control required-entry" id="TOTAL_AMOUNT" name="TOTAL_AMOUNT" >
												<span class="bar"></span>
												<label for="TOTAL_AMOUNT"><?=TOTAL_AMOUNT?></label>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-3 col-sm-3">
											<div class="row form-group">
												<input type="text" class="form-control required-entry" id="AY" name="AY" value="1" >
												<span class="bar"></span>
												<label for="AY"><?=AY?></label>
											</div>
										</div>
										<div class="col-md-1 col-sm-1">&nbsp;</div>
										<div class="col-md-3 col-sm-3">
											<div class="row form-group">
												<input type="text" class="form-control required-entry" id="AP" name="AP" value="1" >
												<span class="bar"></span>
												<label for="AP"><?=AP?></label>
											</div>
										</div>
										<div class="col-md-1 col-sm-1">&nbsp;</div>
										<div class="col-md-4 col-sm-4">
											<div class="row form-group">
												<select id="PK_TERM_BLOCK" name="PK_TERM_BLOCK" class="form-control" >
													<option selected></option>
													 <? $res_type = $db->Execute("select PK_TERM_BLOCK,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_BLOCK WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_TERM_BLOCK']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_TERM_BLOCK"><?=TERM_BLOCK?></label>
											</div>
										</div>
									</div>
																	
									<div class="row">
										<div class="col-md-12" style="background-color:#022561;color:#FFFFFF;">
											<center><b><?=DISBURSEMENTS?></b><center>
										</div>
									</div>
									<br />
									
									<div class="row">
										<div class="col-md-12">
											<div class="row form-group">
												<input type="text" class="form-control required-entry date" id="FIRST_DATE" name="FIRST_DATE" >
												<span class="bar"></span>
												<label for="FIRST_DATE"><?=FIRST_DATE?></label>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-5 col-sm-5">
											<div class="row form-group">
												<input type="text" class="form-control required-entry" id="NO_OF_PAYMENTS" name="NO_OF_PAYMENTS" >
												<span class="bar"></span>
												<label for="NO_OF_PAYMENTS"><?=NO_OF_PAYMENTS?></label>
											</div>
										</div>
										
										<div class="col-md-1 col-sm-1">&nbsp;</div>
										
										<div class="col-md-12 col-sm-6">
											<div class="row form-group">
												<select id="PK_PAYMENT_FREQUENCY" name="PK_PAYMENT_FREQUENCY" class="form-control required-entry" >
													<option selected></option>
													 <? $res_type = $db->Execute("select PK_PAYMENT_FREQUENCY,PAYMENT_FREQUENCY from M_PAYMENT_FREQUENCY WHERE (ACTIVE = 1 OR PK_PAYMENT_FREQUENCY = 13) AND PK_PAYMENT_FREQUENCY != 11 order by DISPLAY_ORDER ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_PAYMENT_FREQUENCY']?>" ><?=$res_type->fields['PAYMENT_FREQUENCY']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_PAYMENT_FREQUENCY"><?=PAYMENT_FREQUENCY?></label>
											</div>
										</div>
									</div>
																			
									<div class="row">
										<div class="col-md-12" style="background-color:#022561;color:#FFFFFF;">
											<center><b><?=DETAILS?></b><center>
										</div>
									</div>
									<br />
									
									<div class="row">
										<div class="col-md-5 col-sm-5">
											<div class="row form-group">
												<select id="PK_DETAIL_TYPE" name="PK_DETAIL_TYPE" class="form-control" onchange="get_disbursement_detail(this.value,'1')" >
													<option selected></option>
													<? $res_type = $db->Execute("select PK_DETAIL_TYPE,DETAIL_TYPE from Z_DETAIL_TYPE WHERE ACTIVE = 1 ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_DETAIL_TYPE']?>" <? if($PK_DETAIL_TYPE == $res_type->fields['PK_DETAIL_TYPE']) echo "selected"; ?> ><?=$res_type->fields['DETAIL_TYPE']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_DETAIL_TYPE"><?=DETAIL_TYPE?></label>
											</div>
										</div>
										
										<div class="col-md-1 col-sm-1">&nbsp;</div>
										
										<div class="col-md-6 col-sm-6">
											<div class="form-group m-b-40" id="DISBURSEMENT_DETAIL_1_LABEL">
												<div id="DETAIL_DIV_1" >
													<select id="DETAIL" name="DETAIL" class="form-control">
														<option selected></option>
													</select>
												</div>
												<span class="bar"></span> 
												<label for="DISBURSEMENT_DETAIL_1"><?=DETAIL?></label>
											</div>
										</div>
									</div>
																	
									<div class="row">
										<div class="col-md-12" style="background-color:#022561;color:#FFFFFF;">
											<center><b><?=HOURS_REQUIRED?></b><center>
										</div>
									</div>
									<br />
									
									<div class="row">
										<div class="col-md-12">
											<div class="row form-group">
												<input type="text" class="form-control" id="HOURS_REQUIRED" name="HOURS_REQUIRED" >
												<span class="bar"></span>
												<label for="HOURS_REQUIRED"><?=HOURS_REQUIRED?></label>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12" style="background-color:#022561;color:#FFFFFF;">
											<center><b><?=FUNDS_REQUESTED?></b><center>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<div class="d-flex">
												<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
													<input type="checkbox" class="custom-control-input" id="FUNDS_REQUESTED" name="FUNDS_REQUESTED" value="1" >
													<label class="custom-control-label" for="FUNDS_REQUESTED"><?=FUNDS_REQUESTED?></label>
												</div>
											</div>
										</div>
									</div>
									<hr />
									<div class="row">
										<div class="col-md-12">
											<center>
												<button type="save" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button onclick="javascript:window.close()" type="button" class="btn waves-effect waves-light btn-dark" ><?=CANCEL?></button>
											<center>
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
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});
	
	function get_disbursement_detail(type,id){
		jQuery(document).ready(function($) { 
			var data  = 'detail_type='+type+'&detail_id='+id+'&sid=<?=$_GET['id']?>&eid=<?=$_GET['eid']?>';
			var value = $.ajax({
				url: "ajax_get_disbursement_detail_drop_down",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {
					
					data = data.replace("DISBURSEMENT_DETAIL[]","DETAIL");
					document.getElementById('DETAIL_DIV_1').innerHTML = data
					document.getElementById('DISBURSEMENT_DETAIL_1_LABEL').classList.add("focused");
					document.getElementById('DISBURSEMENT_DETAIL_1').style.width = "100%"
				}		
			}).responseText;
		});
	}
	</script>
</body>

</html>
