<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/ar_leder_code.php");
require_once("../language/_1098T_Setup.php");
require_once("../language/menu.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT _4807G FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if(check_access('MANAGEMENT_ACCOUNTING') == 0 || $res_add_on->fields['_4807G'] == 0){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	/*echo $_GET[id];
	echo "<br>";
	echo $_SESSION[PK_ACCOUNT];
	echo "<pre>";print_r($_POST);exit;*/
	$AR_LEDGER_CODE = $_POST;
	db_perform('M_AR_LEDGER_CODE', $AR_LEDGER_CODE, 'update'," PK_AR_LEDGER_CODE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");

	header("location:manage_480G_ledger");
}

$res = $db->Execute("SELECT PK_FINANCIAL_ASSISTANCE_TYPE_4807G, INCLUDE_IN_REPORTING_4807G, CODE FROM M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->RecordCount() == 0){
	header("location:manage_480G_ledger");
	exit;
}
$FINANCIAL_ASSISTANCE_TYPE_4807G  	= $res->fields['PK_FINANCIAL_ASSISTANCE_TYPE_4807G'];
$INCLUDE_IN_REPORTING_4807G 		= $res->fields['INCLUDE_IN_REPORTING_4807G'];
$CODE 								= $res->fields['CODE'];
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
	<title><?=MNU_FARE ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_FARE ?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row">
                                        <div class="col-md-12">
											<div class="row">
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="CODE" disabled value="<?=$CODE?>" >
														<span class="bar"></span>
														<label for="CODE"><?=LEDGER_CODE?></label>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<select id="INCLUDE_IN_REPORTING_4807G" name="INCLUDE_IN_REPORTING_4807G" class="form-control required-entry">
															<option value="1" <? if($INCLUDE_IN_REPORTING_4807G == 1) echo "selected"; ?> >Yes</option>
															<option value="0" <? if($INCLUDE_IN_REPORTING_4807G == 0) echo "selected"; ?> >No</option>
														</select>
														<span class="bar"></span> 
														<label for="INCLUDE_REPORT"><?=INCLUDE_REPORT?></label>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<select id="FINANCIAL_ASSISTANCE_TYPE_4807G" name="PK_FINANCIAL_ASSISTANCE_TYPE_4807G" class="form-control required-entry">
															<option value=""></option>
															<? $res_type = $db->Execute("select PK_FINANCIAL_ASSISTANCE_TYPE_4807G,FINANCIAL_ASSISTANCE_TYPE from _4807G_CATEGORY_FINANCIAL_ASSISTANCE_TYPE WHERE ACTIVE = 1 ");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_FINANCIAL_ASSISTANCE_TYPE_4807G'] ?>" <? if($res_type->fields['PK_FINANCIAL_ASSISTANCE_TYPE_4807G'] == $FINANCIAL_ASSISTANCE_TYPE_4807G) echo "selected"; ?> ><?=$res_type->fields['FINANCIAL_ASSISTANCE_TYPE']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="FINANCIAL_ASSISTANCE_TYPE"><?=FINANCIAL_ASSISTANCE_TYPE?></label><!-- Ticket # 1048--> 
													</div>
												</div>
												
											</div>
										</div>
                                    </div>
								
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_480G_ledger'" ><?=CANCEL?></button>
												
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
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>

</body>

</html>