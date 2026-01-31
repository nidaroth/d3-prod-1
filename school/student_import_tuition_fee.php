<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("function_student_ledger.php");
require_once("check_access.php");

$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
if($FINANCE_ACCESS != 2 && $FINANCE_ACCESS != 3){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$cond = "";
	if($_POST['AY'] > 0)
	{
		$cond .= " AND AY = '$_POST[AY]' ";
	}
	if($_POST['PK_DEPENDENT_STATUS'] > 0)
	{
		$cond .= " AND PK_DEPENDENT_STATUS = '$_POST[PK_DEPENDENT_STATUS]' ";
	}
	if($_POST['HOUSING_TYPE'] > 0) // DIAM-1277
	{
		$cond .= " AND PK_HOUSING_TYPE = '$_POST[HOUSING_TYPE]' ";
	} // End DIAM-1277
		
	$res_type = $db->Execute("select * from M_CAMPUS_PROGRAM_FEE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_PROGRAM = '$_GET[prog]' $cond");
	while (!$res_type->EOF) {
		///////////////////////////////////
		$FEE_AMOUNT = $res_type->fields['AMOUNT'];
		$STUDENT_FEE_BUDGET['PK_AR_LEDGER_CODE'] 	 = $res_type->fields['PK_AR_LEDGER_CODE'];
		$STUDENT_FEE_BUDGET['ACADEMIC_YEAR'] 		 = $res_type->fields['AY'];
		$STUDENT_FEE_BUDGET['ACADEMIC_PERIOD'] 		 = $res_type->fields['AP'];
		$STUDENT_FEE_BUDGET['PK_FEE_TYPE'] 			 = $res_type->fields['PK_FEE_TYPE'];
		$STUDENT_FEE_BUDGET['PK_CAMPUS_PROGRAM'] 	 = $res_type->fields['PK_CAMPUS_PROGRAM'];
		//$STUDENT_FEE_BUDGET['PK_TERM_MASTER'] 		 = $res_type->fields['ACADEMIC_PERIOD'];
		$STUDENT_FEE_BUDGET['DESCRIPTION'] 			 = $res_type->fields['DESCRIPTION'];
		$STUDENT_FEE_BUDGET['PK_STUDENT_ENROLLMENT'] = $_GET['eid'];
		$STUDENT_FEE_BUDGET['PK_STUDENT_MASTER'] 	 = $_GET['id'];
		$STUDENT_FEE_BUDGET['PK_ACCOUNT'] 			 = $_SESSION['PK_ACCOUNT'];
		$STUDENT_FEE_BUDGET['CREATED_BY']  			 = $_SESSION['PK_USER'];
		$STUDENT_FEE_BUDGET['CREATED_ON']  			 = date("Y-m-d H:i");
		
		$ADD_FREQUENCY = '0 days';	
		if($res_type->fields['PROGRAM_FEE_PK_PAYMENT_FREQUENCY'] == 1 )
			$ADD_FREQUENCY = " 1 weeks";
		else if($res_type->fields['PROGRAM_FEE_PK_PAYMENT_FREQUENCY'] == 2 )
			$ADD_FREQUENCY = " 14 days";
		else if($res_type->fields['PROGRAM_FEE_PK_PAYMENT_FREQUENCY'] == 3 )
			$ADD_FREQUENCY = " 5 weeks";
		else if($res_type->fields['PROGRAM_FEE_PK_PAYMENT_FREQUENCY'] == 4 )
			$ADD_FREQUENCY = " 1 Months";
		else if($res_type->fields['PROGRAM_FEE_PK_PAYMENT_FREQUENCY'] == 5 )
			$ADD_FREQUENCY = " 2 Months";
		else if($res_type->fields['PROGRAM_FEE_PK_PAYMENT_FREQUENCY'] == 6 )
			$ADD_FREQUENCY = " 60 days";
		else if($res_type->fields['PROGRAM_FEE_PK_PAYMENT_FREQUENCY'] == 7 )
			$ADD_FREQUENCY = " 12 weeks";
		else if($res_type->fields['PROGRAM_FEE_PK_PAYMENT_FREQUENCY'] == 8 )
			$ADD_FREQUENCY = " 13 weeks";
		else if($res_type->fields['PROGRAM_FEE_PK_PAYMENT_FREQUENCY'] == 9 )
			$ADD_FREQUENCY = " 20 weeks";
		else if($res_type->fields['PROGRAM_FEE_PK_PAYMENT_FREQUENCY'] == 10 )
			$ADD_FREQUENCY = " 3 Months";
		
		$res_firm_term = $db->Execute("SELECT BEGIN_DATE FROM S_STUDENT_ENROLLMENT,S_TERM_MASTER WHERE PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER "); 
		$FIRST_DATE = $res_firm_term->fields['BEGIN_DATE'];
		$DUE_DATE 	= $res_firm_term->fields['BEGIN_DATE'];

		if($res_type->fields['DAYS_FROM_START'] > 0) {
			$FIRST_DATE = date("Y-m-d",strtotime($FIRST_DATE." +".$res_type->fields['DAYS_FROM_START']." days"));
			$DUE_DATE 	= date("Y-m-d",strtotime($DUE_DATE." +".$res_type->fields['DAYS_FROM_START']." days"));
		}
		
		$INST_AMT 	= ceil($FEE_AMOUNT / $res_type->fields['PROGRAM_FEE_NO_OF_PAYMENTS']);
		$TOT_AMT  	= 0;
		for($i = 1 ; $i < $res_type->fields['PROGRAM_FEE_NO_OF_PAYMENTS'] ; $i++){
			if($i == 1)
				$DUE_DATE = date("Y-m-d",strtotime($FIRST_DATE));
			else {
				if($res_type->fields['PROGRAM_FEE_PK_PAYMENT_FREQUENCY'] == 13 ){
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
			
			//echo "<pre>";print_r($STUDENT_FEE_BUDGET);
		}
		if($res_type->fields['PROGRAM_FEE_PK_PAYMENT_FREQUENCY'] == 13 ){
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
		$STUDENT_FEE_BUDGET['FEE_AMOUNT'] 		= $FEE_AMOUNT - $TOT_AMT;
		db_perform('S_STUDENT_FEE_BUDGET', $STUDENT_FEE_BUDGET, 'insert');
		$PK_STUDENT_FEE_BUDGET 	= $db->insert_ID();
			
		$ledger_data['PK_STUDENT_FEE_BUDGET'] 	= $PK_STUDENT_FEE_BUDGET;
		$ledger_data['PK_AR_LEDGER_CODE'] 		= $STUDENT_FEE_BUDGET['PK_AR_LEDGER_CODE'];
		$ledger_data['AMOUNT'] 					= $STUDENT_FEE_BUDGET['FEE_AMOUNT'];
		$ledger_data['DATE'] 					= date("Y-m-d");
		$ledger_data['PK_STUDENT_ENROLLMENT'] 	= $_GET['eid'];
		$ledger_data['PK_STUDENT_MASTER'] 		= $_GET['id'];
		
		student_ledger($ledger_data);
			
		$res_type->MoveNext();
	}
	
	
	
	//echo "<pre>";print_r($STUDENT_DISBURSEMENT);exit;
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
	<title><?=PROGRAM_TUITION_FEE?> | <?=$title?></title>
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
											<center><b><?=PROGRAM_TUITION_FEE?></b><center>
										</div>
									</div>
									<br />
									<div class="row">
										<div class="col-md-12">
											<div class="row form-group ">
												<select id="AY" name="AY" class="form-control" >
													<option value="-1"><?=ALL?></option>
													 <? $res_type = $db->Execute("select DISTINCT(AY) AS AY from M_CAMPUS_PROGRAM_FEE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_PROGRAM = '$_GET[prog]' order by AY ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['AY'] ?>" ><?=$res_type->fields['AY'] ?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="AY"><?=AY?></label>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<div class="row form-group">
												<select id="PK_DEPENDENT_STATUS" name="PK_DEPENDENT_STATUS" class="form-control required-entry" >
													 <? $res_type = $db->Execute("select PK_DEPENDENT_STATUS,CONCAT(CODE,' - ',DESCRIPTION) AS DESCRIPTION  from M_DEPENDENT_STATUS WHERE ACTIVE = 1 order by CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_DEPENDENT_STATUS'] ?>" ><?=$res_type->fields['DESCRIPTION'] ?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_DEPENDENT_STATUS"><?=DEPENDENCY_STATUS?></label>
											</div>
										</div>
									</div>

									<!-- DIAM-1277 -->
									<div class="row">
										<div class="col-md-12">
											<div class="row form-group">
												<select id="HOUSING_TYPE" name="HOUSING_TYPE" class="form-control required-entry" >
													<option value="-1"><?=ALL?></option>
													<? $res_type = $db->Execute("SELECT PK_HOUSING_TYPE,CODE,DESCRIPTION FROM M_HOUSING_TYPE WHERE 1 = 1 AND ACTIVE = 1 ORDER BY CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_HOUSING_TYPE'] ?>" ><?=$res_type->fields['DESCRIPTION'] ?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="HOUSING_TYPE">Housing</label>
											</div>
										</div>
									</div>
									<!-- End DIAM-1277 -->
									
									<div class="row">
										<div class="col-md-12">
											<center>
												<button type="save" class="btn waves-effect waves-light btn-info"><?=IMPORT?></button>
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
</body>

</html>