<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/ar_leder_code.php");
require_once("../language/_1098T_Setup.php");
require_once("../language/menu.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT _1098T FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if(check_access('MANAGEMENT_ACCOUNTING') == 0 || $res_add_on->fields['_1098T'] == 0){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$AR_LEDGER_CODE = $_POST;
	db_perform('M_AR_LEDGER_CODE', $AR_LEDGER_CODE, 'update'," PK_AR_LEDGER_CODE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");

	header("location:manage_1098T_ledger");
}

$res = $db->Execute("SELECT PK_1098T_CATEGORY, INCLUDE_IN_1098T_REPORTING, CODE FROM M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->RecordCount() == 0){
	header("location:manage_1098T_ledger");
	exit;
}
$PK_1098T_CATEGORY  		= $res->fields['PK_1098T_CATEGORY'];
$INCLUDE_IN_1098T_REPORTING = $res->fields['INCLUDE_IN_1098T_REPORTING'];
$CODE 						= $res->fields['CODE'];
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
	<title><?=AWARD_LEDGER_CODE_CATEGORY ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=AWARD_LEDGER_CODE_CATEGORY ?> </h4>
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
														<select id="INCLUDE_IN_1098T_REPORTING" name="INCLUDE_IN_1098T_REPORTING" class="form-control required-entry">
															<option value="1" <? if($INCLUDE_IN_1098T_REPORTING == 1) echo "selected"; ?> >Yes</option>
															<option value="0" <? if($INCLUDE_IN_1098T_REPORTING == 0) echo "selected"; ?> >No</option>
														</select>
														<span class="bar"></span> 
														<label for="INCLUDE_IN_1098T_REPORTING"><?=INCLUDE_IN_1098T_REPORTING?></label>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<select id="PK_1098T_CATEGORY" name="PK_1098T_CATEGORY" class="form-control required-entry">
															<option value=""></option>
															<? $res_type = $db->Execute("select PK_1098T_CATEGORY,_1098T_CATEGORY from _1098T_CATEGORY WHERE ACTIVE = 1 ");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_1098T_CATEGORY'] ?>" <? if($res_type->fields['PK_1098T_CATEGORY'] == $PK_1098T_CATEGORY) echo "selected"; ?> ><?=$res_type->fields['_1098T_CATEGORY']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_1098T_CATEGORY"><?=_1098T_CATEGORY?></label><!-- Ticket # 1048--> 
													</div>
												</div>
												
											</div>
										</div>
                                    </div>
								
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_1098T_ledger'" ><?=CANCEL?></button>
												
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