<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/ecm_ledger.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT ECM FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

if(check_access('MANAGEMENT_TITLE_IV_SERVICER') == 0 || $res_add_on->fields['ECM'] == 0){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$ECM_LEDGER = $_POST;
	if($_GET['id'] == ''){
		$ECM_LEDGER['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$ECM_LEDGER['CREATED_BY']  = $_SESSION['ADMIN_PK_USER'];
		$ECM_LEDGER['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_ECM_LEDGER', $ECM_LEDGER, 'insert');
	} else {
		$ECM_LEDGER['EDITED_BY'] = $_SESSION['ADMIN_PK_USER'];
		$ECM_LEDGER['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_ECM_LEDGER', $ECM_LEDGER, 'update'," PK_ECM_LEDGER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
	header("location:manage_ecm_ledger");
}
if($_GET['id'] == ''){
	$PK_ECM_LEDGER_MASTER	= '';
	$ECM_LEDGER 			= '';
	$PK_AR_LEDGER_CODE		= '';
	$DESCRIPTION			= '';
	$PK_AWARD_YEAR			= '';
	$ACTIVE	 				= '';
	
} else {
	$res = $db->Execute("SELECT * FROM M_ECM_LEDGER WHERE PK_ECM_LEDGER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  "); 
	if($res->RecordCount() == 0){
		header("location:manage_ecm_ledger");
		exit;
	}
	$PK_ECM_LEDGER_MASTER 	= $res->fields['PK_ECM_LEDGER_MASTER'];
	$ECM_LEDGER 			= $res->fields['ECM_LEDGER'];
	$DESCRIPTION 			= $res->fields['DESCRIPTION'];
	$PK_AR_LEDGER_CODE 		= $res->fields['PK_AR_LEDGER_CODE'];
	$PK_AWARD_YEAR 			= $res->fields['PK_AWARD_YEAR'];
	$ACTIVE  		 		= $res->fields['ACTIVE'];
	
	$res = $db->Execute("SELECT ECM_LEDGER_TYPE FROM M_ECM_LEDGER_TYPE_MASTER, M_ECM_LEDGER_MASTER WHERE PK_ECM_LEDGER_MASTER = '$PK_ECM_LEDGER_MASTER' AND   M_ECM_LEDGER_TYPE_MASTER.PK_ECM_LEDGER_TYPE_MASTER = M_ECM_LEDGER_MASTER.PK_ECM_LEDGER_TYPE_MASTER "); 
	$ECM_TYPE 	= $res->fields['ECM_LEDGER_TYPE'];
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
	<title><?=MAP_ECM ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=MAP_ECM ?> </h4>
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
												<select id="PK_ECM_LEDGER_MASTER" name="PK_ECM_LEDGER_MASTER" class="form-control" onchange="get_type(this.value)" >
													<option selected></option>
													 <? $res_type = $db->Execute("select PK_ECM_LEDGER_MASTER,ECM_LEDGER from M_ECM_LEDGER_MASTER WHERE ACTIVE = 1 order by ECM_LEDGER ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_ECM_LEDGER_MASTER'] ?>" <? if($res_type->fields['PK_ECM_LEDGER_MASTER'] == $PK_ECM_LEDGER_MASTER) echo "selected"; ?> ><?=$res_type->fields['ECM_LEDGER'] ?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="ECM_LEDGER"><?=ECM_LEDGER?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="ECM_TYPE" value="<?=$ECM_TYPE?>" readonly >
												<span class="bar"></span>
												<label for="ECM_TYPE"><?=ECM_TYPE?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_AWARD_YEAR" name="PK_AWARD_YEAR" class="form-control required-entry" >
													<option selected></option>
													 <? $res_type = $db->Execute("select PK_AWARD_YEAR,AWARD_YEAR from M_AWARD_YEAR WHERE ACTIVE = 1 order by BEGIN_DATE DESC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_AWARD_YEAR'] ?>" <? if($res_type->fields['PK_AWARD_YEAR'] == $PK_AWARD_YEAR) echo "selected"; ?> ><?=$res_type->fields['AWARD_YEAR'] ?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_AWARD_YEAR"><?=AWARD_YEAR?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_AR_LEDGER_CODE" name="PK_AR_LEDGER_CODE" class="form-control" onchange="get_ledger_desc_1(this.value)" >
													<option selected></option>
													 <? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_AR_LEDGER_CODE'] ?>" <? if($res_type->fields['PK_AR_LEDGER_CODE'] == $PK_AR_LEDGER_CODE) echo "selected"; ?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['LEDGER_DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="LEDGER_CODE"><?=LEDGER_CODE?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="DESCRIPTION" name="DESCRIPTION" value="<?=$DESCRIPTION?>" readonly >
												<span class="bar"></span>
												<label for="DESCRIPTION"><?=LEDGER_DESCRIPTION?></label>
											</div>
										</div>
                                    </div>
							
									<? if($_GET['id'] != ''){ ?>
									<div class="row">
										<div class="col-md-6">
											<div class="row form-group">
												<div class="custom-control col-md-2"><?=ACTIVE?></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="customRadio11"><?=YES?></label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="customRadio22"><?=NO?></label>
												</div>
											</div>
										</div>
									</div>
									<? } ?>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_ecm_ledger'" ><?=CANCEL?></button>
												
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
		
		function get_ledger_desc_1(val){
			jQuery(document).ready(function($) {
				var data  = 'val='+val;
				var value = $.ajax({
					url: "ajax_get_ledger_desc",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('DESCRIPTION').value = data
						$("#DESCRIPTION").parent().addClass("focused");
					}		
				}).responseText;
			});
		}
		
		function get_type(val){
			jQuery(document).ready(function($) {
				var data  = 'val='+val;
				var value = $.ajax({
					url: "ajax_get_ecm_type",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('ECM_TYPE').value = data
						$("#ECM_TYPE").parent().addClass("focused");
					}		
				}).responseText;
			});
		}
		
		
	</script>

</body>

</html>