<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/title_iv_recipients_by_category_setup.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('SETUP_ACCOUNTING') == 0 && check_access('SETUP_FINANCE') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	foreach($_POST['PK_AR_LEDGER_CODE'] as $PK_AR_LEDGER_CODE){
		$res = $db->Execute("SELECT PK_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER FROM M_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER WHERE PK_TITLE_IV_RECIPIENTS_CATEGORY = '$_GET[id]' AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
		if($res->RecordCount() == 0) {
			$CATEGORY_LEDGER['PK_TITLE_IV_RECIPIENTS_CATEGORY']	= $_GET['id'];
			$CATEGORY_LEDGER['PK_AR_LEDGER_CODE']				= $PK_AR_LEDGER_CODE;
			$CATEGORY_LEDGER['PK_ACCOUNT']  					= $_SESSION['PK_ACCOUNT'];
			$CATEGORY_LEDGER['CREATED_BY']  					= $_SESSION['PK_USER'];
			$CATEGORY_LEDGER['CREATED_ON']  					= date("Y-m-d H:i");
			db_perform('M_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER', $CATEGORY_LEDGER, 'insert');
			$PK_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER[] = $db->insert_ID();
		} else
			$PK_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER[] = $res->fields['PK_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER'];
	}
	$cond = "";
	if(!empty($PK_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER))
		$cond = " AND PK_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER NOT IN (".implode(",",$PK_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER).") ";

	$db->Execute("DELETE FROM M_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER WHERE PK_TITLE_IV_RECIPIENTS_CATEGORY = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	
	header("location:manage_title_iv_recipients_by_category_setup");
}
if($_GET['id'] == ''){
	$CODE = '';
	
} else {
	$res = $db->Execute("SELECT CODE FROM M_TITLE_IV_RECIPIENTS_CATEGORY WHERE PK_TITLE_IV_RECIPIENTS_CATEGORY = '$_GET[id]' AND ACTIVE = 1  "); 
	if($res->RecordCount() == 0){
		header("location:manage_title_iv_recipients_by_category_setup");
		exit;
	}
	$CODE = $res->fields['CODE'];
	
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
	<title><?=MNU_TITLE_IV_RECIPIENTS_BY_CATEGORY_SETUP ?> | <?=$title?></title>
	
	<style>
	li > a > label{position: unset !important;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_TITLE_IV_RECIPIENTS_BY_CATEGORY_SETUP ?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="CODE" value="<?=$CODE?>" readonly >
												<span class="bar"></span>
												<label for="CODE"><?=TITLE_IV_RECIPIENTS_CATEGORY_1?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<?=LEDGER_CODE?><br />
												<select id="PK_AR_LEDGER_CODE" name="PK_AR_LEDGER_CODE[]" multiple class="form-control"  >
													<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by CODE ASC");
													while (!$res_type->EOF) { 
														$selected 			= ""; 
														$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE'];
														$res_led = $db->Execute("SELECT CODE FROM M_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER, M_AR_LEDGER_CODE WHERE PK_TITLE_IV_RECIPIENTS_CATEGORY = '$_GET[id]' AND M_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND M_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_TITLE_IV_RECIPIENTS_CATEGORY_LEDGER.PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ");
														if($res_led->RecordCount() > 0)
															$selected = "selected"; ?>
														<option value="<?=$PK_AR_LEDGER_CODE ?>" <?=$selected?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['LEDGER_DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button name="btn" type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_title_iv_recipients_by_category_setup'" ><?=CANCEL?></button>
												
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

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#PK_AR_LEDGER_CODE').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=LEDGER_CODE?>',
				nonSelectedText: '',
				numberDisplayed: 1,
				nSelectedText: '<?=LEDGER_CODE?> selected'
			});
		});
	</script>
</body>

</html>