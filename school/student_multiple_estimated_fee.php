<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("function_student_ledger.php");
require_once("check_access.php");

$FINANCE_ACCESS = check_access('FINANCE_ACCESS');
if($FINANCE_ACCESS != 2 && $FINANCE_ACCESS != 3){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("select PK_CAMPUS_PROGRAM from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$_GET[eid]' and PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$FEE_AMOUNT = $_POST['TOTAL_AMOUNT'];
	$STUDENT_FEE_BUDGET['PK_AR_LEDGER_CODE'] 	 = $_POST['PK_AR_LEDGER_CODE'];
	$STUDENT_FEE_BUDGET['ACADEMIC_YEAR'] 		 = $_POST['AY'];
	$STUDENT_FEE_BUDGET['ACADEMIC_PERIOD'] 		 = $_POST['AP'];
	$STUDENT_FEE_BUDGET['PK_FEE_TYPE'] 			 = $_POST['PK_FEE_TYPE'];
	$STUDENT_FEE_BUDGET['PK_CAMPUS_PROGRAM'] 	 = $res->fields['PK_CAMPUS_PROGRAM'];
	//$STUDENT_FEE_BUDGET['DESCRIPTION'] 			 = $res_type->fields['DESCRIPTION'];
	$STUDENT_FEE_BUDGET['PK_STUDENT_ENROLLMENT'] = $_GET['eid'];
	$STUDENT_FEE_BUDGET['PK_STUDENT_MASTER'] 	 = $_GET['id'];
	$STUDENT_FEE_BUDGET['PK_ACCOUNT'] 			 = $_SESSION['PK_ACCOUNT'];
	$STUDENT_FEE_BUDGET['CREATED_BY']  			 = $_SESSION['PK_USER'];
	$STUDENT_FEE_BUDGET['CREATED_ON']  			 = date("Y-m-d H:i");
		
	$ADD_FREQUENCY = '0 days';	
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

	$INST_AMT = ceil(($_POST['TOTAL_AMOUNT'] / $_POST['NO_OF_PAYMENTS']));

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
	
		$STUDENT_FEE_BUDGET['FEE_BUDGET_DATE']	= $DUE_DATE;
		$STUDENT_FEE_BUDGET['FEE_AMOUNT']  		= $INST_AMT;
		db_perform('S_STUDENT_FEE_BUDGET', $STUDENT_FEE_BUDGET, 'insert');
		$PK_STUDENT_FEE_BUDGET 	= $db->insert_ID();

		$ledger_data['PK_STUDENT_FEE_BUDGET'] 	= $PK_STUDENT_FEE_BUDGET;
		$ledger_data['PK_AR_LEDGER_CODE'] 		= $STUDENT_FEE_BUDGET['PK_AR_LEDGER_CODE'];
		$ledger_data['AMOUNT'] 					= $STUDENT_FEE_BUDGET['FEE_AMOUNT'];
		$ledger_data['DATE'] 					= date("Y-m-d");
		$ledger_data['PK_STUDENT_ENROLLMENT'] 	= $_GET['eid'];
		$ledger_data['PK_STUDENT_MASTER'] 		= $_GET['id'];
		
		student_ledger($ledger_data);
		
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
	
	$STUDENT_FEE_BUDGET['FEE_BUDGET_DATE'] 	= $DUE_DATE;
	$STUDENT_FEE_BUDGET['FEE_AMOUNT'] 		= $_POST['TOTAL_AMOUNT'] - $TOT_AMT;
	db_perform('S_STUDENT_FEE_BUDGET', $STUDENT_FEE_BUDGET, 'insert');
	$PK_STUDENT_FEE_BUDGET 	= $db->insert_ID();
		
	$ledger_data['PK_STUDENT_FEE_BUDGET'] 	= $PK_STUDENT_FEE_BUDGET;
	$ledger_data['PK_AR_LEDGER_CODE'] 		= $STUDENT_FEE_BUDGET['PK_AR_LEDGER_CODE'];
	$ledger_data['AMOUNT'] 					= $STUDENT_FEE_BUDGET['FEE_AMOUNT'];
	$ledger_data['DATE'] 					= date("Y-m-d");
	$ledger_data['PK_STUDENT_ENROLLMENT'] 	= $_GET['eid'];
	$ledger_data['PK_STUDENT_MASTER'] 		= $_GET['id'];
	
	student_ledger($ledger_data);
	?>
	<script type="text/javascript">window.opener.go_to_est_fee(this)</script>
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
											<center><b><?=FEES?></b><center>
										</div>
									</div>
									<br />
									<div class="row">
										<div class="col-md-12">
											<div class="row form-group">
												<select id="PK_AR_LEDGER_CODE" name="PK_AR_LEDGER_CODE" class="form-control required-entry" >
													<option selected></option>
													 <? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 order by CODE ASC");
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
										<div class="col-md-5 col-sm-5">
											<div class="row form-group">
												<input type="text" class="form-control required-entry" id="AY" name="AY" value="1" >
												<span class="bar"></span>
												<label for="AY"><?=AY?></label>
											</div>
										</div>
										<div class="col-md-1 col-sm-1">&nbsp;</div>
										<div class="col-md-12 col-sm-6">
											<div class="row form-group">
												<input type="text" class="form-control required-entry" id="AP" name="AP" value="1" >
												<span class="bar"></span>
												<label for="AP"><?=AP?></label>
											</div>
										</div>
									</div>
								
									<div class="row">
										<div class="col-md-5 col-sm-5">
											<div class="row form-group">
												<input type="text" class="form-control required-entry date" id="FIRST_DATE" name="FIRST_DATE" >
												<span class="bar"></span>
												<label for="FIRST_DATE"><?=FIRST_DATE?></label>
											</div>
										</div>
										
										<div class="col-md-1 col-sm-1">&nbsp;</div>
										
										<div class="col-md-12 col-sm-6">
											<div class="row form-group">
												<select id="PK_FEE_TYPE" name="PK_FEE_TYPE" class="form-control" >
													<option selected></option>
													<? $act_type_cond = " AND ACTIVE = 1 ";
													if($PK_FEE_TYPE > 0)
														$act_type_cond = " AND (ACTIVE = 1 OR PK_FEE_TYPE = '$PK_FEE_TYPE' ) ";
														
													$res_type = $db->Execute("select PK_FEE_TYPE,FEE_TYPE from M_FEE_TYPE WHERE 1 = 1 $act_type_cond  order by FEE_TYPE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_FEE_TYPE'] ?>" ><?=$res_type->fields['FEE_TYPE']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_FEE_TYPE"><?=FEE_TYPE?></label>
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
												<label for="PK_PAYMENT_FREQUENCY"><?=CHARGE_FREQUENCY?></label>
											</div>
										</div>
									</div>
									
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