<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/soc_code.php");
require_once("check_access.php");

if(check_access('SETUP_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$SOC_CODE = $_POST;
	if($_GET['id'] == ''){
		$SOC_CODE['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$SOC_CODE['CREATED_BY']  = $_SESSION['PK_USER'];
		$SOC_CODE['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_SOC_CODE', $SOC_CODE, 'insert');
	} else {
		$SOC_CODE['EDITED_BY'] = $_SESSION['PK_USER'];
		$SOC_CODE['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_SOC_CODE', $SOC_CODE, 'update'," PK_SOC_CODE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	header("location:manage_soc_code");
}
if($_GET['id'] == ''){
	$SOC_CODE 					= '';
	$SOC_TITLE 	 				= '';
	$PK_IPEDS_CATEGORY_MASTER 	= '';
	$ACTIVE	 		 			= '';
} else {
	$res = $db->Execute("SELECT * FROM M_SOC_CODE WHERE PK_SOC_CODE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_soc_code");
		exit;
	}
	$SOC_CODE 					= $res->fields['SOC_CODE'];
	$SOC_TITLE 	 				= $res->fields['SOC_TITLE'];
	$PK_IPEDS_CATEGORY_MASTER 	= $res->fields['PK_IPEDS_CATEGORY_MASTER'];
	$ACTIVE  		 			= $res->fields['ACTIVE'];
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
	<title><?=SOC_CODE_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=SOC_CODE_PAGE_TITLE?> </h4>
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
												<input type="text" class="form-control required-entry" id="SOC_CODE" name="SOC_CODE" value="<?=$SOC_CODE?>" >
												<span class="bar"></span>
												<label for="SOC_CODE"><?=SOC_CODE?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="SOC_TITLE" name="SOC_TITLE" value="<?=$SOC_TITLE?>" >
												<span class="bar"></span>
												<label for="SOC_TITLE"><?=SOC_TITLE?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_IPEDS_CATEGORY_MASTER" name="PK_IPEDS_CATEGORY_MASTER" class="form-control" >
													<option selected></option>
													<? $res_type = $db->Execute("SELECT PK_IPEDS_CATEGORY_MASTER, IPEDS_CATEGORY FROM M_IPEDS_CATEGORY_MASTER WHERE ACTIVE = 1");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_IPEDS_CATEGORY_MASTER']?>" <? if($res_type->fields['PK_IPEDS_CATEGORY_MASTER'] == $PK_IPEDS_CATEGORY_MASTER) echo "selected: "; ?>  ><?=$res_type->fields['IPEDS_CATEGORY'] ?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_IPEDS_CATEGORY_MASTER"><?=IPEDS_CATEGORY?></label>
											</div>
										</div>
                                    </div>
				
									<? if($_GET['id'] != ''){ ?>
									<div class="row">
										<div class="col-md-6">
											<div class="row form-group">
												<div class="custom-control col-md-3"><?=ACTIVE?></div>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_soc_code'" ><?=CANCEL?></button>
												
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