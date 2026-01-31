<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$ECM_LEDGER_MASTER = $_POST;
	if($_GET['id'] == ''){
		$ECM_LEDGER_MASTER['CREATED_BY']  = $_SESSION['ADMIN_PK_USER'];
		$ECM_LEDGER_MASTER['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_ECM_LEDGER_MASTER', $ECM_LEDGER_MASTER, 'insert');
	} else {
		$ECM_LEDGER_MASTER['EDITED_BY'] = $_SESSION['ADMIN_PK_USER'];
		$ECM_LEDGER_MASTER['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_ECM_LEDGER_MASTER', $ECM_LEDGER_MASTER, 'update'," PK_ECM_LEDGER_MASTER = '$_GET[id]'");
	}
	header("location:manage_ecm_ledger");
}
if($_GET['id'] == ''){
	$ECM_LEDGER 	= '';
	$DESCRIPTION	= '';
	$ACTIVE	 		= '';
	
} else {
	$res = $db->Execute("SELECT * FROM M_ECM_LEDGER_MASTER WHERE PK_ECM_LEDGER_MASTER = '$_GET[id]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_ecm_ledger");
		exit;
	}
	
	$ECM_LEDGER 	= $res->fields['ECM_LEDGER'];
	$DESCRIPTION 	= $res->fields['DESCRIPTION'];
	$ACTIVE  		= $res->fields['ACTIVE'];
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
	<title>ECM Ledger | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo "Add"; else echo "Edit"; ?> ECM Ledger </h4>
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
												<input type="text" class="form-control required-entry" id="ECM_LEDGER" name="ECM_LEDGER" value="<?=$ECM_LEDGER?>" >
												<span class="bar"></span>
												<label for="ECM_LEDGER">ECM Code</label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_ECM_LEDGER_TYPE_MASTER" name="PK_ECM_LEDGER_TYPE_MASTER" class="form-control required-entry" >
													<option selected></option>
													 <? $res_type = $db->Execute("select PK_ECM_LEDGER_TYPE_MASTER,ECM_LEDGER_TYPE from M_ECM_LEDGER_TYPE_MASTER WHERE ACTIVE = 1 order by ECM_LEDGER_TYPE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_ECM_LEDGER_TYPE_MASTER'] ?>" <? if($res_type->fields['PK_ECM_LEDGER_TYPE_MASTER'] == $PK_ECM_LEDGER_TYPE_MASTER) echo "selected"; ?> ><?=$res_type->fields['ECM_LEDGER_TYPE'] ?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_ECM_LEDGER_TYPE_MASTER">ECM Type</label>
											</div>
										</div>
                                    </div>
							
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="DESCRIPTION" name="DESCRIPTION" value="<?=$DESCRIPTION?>" >
												<span class="bar"></span>
												<label for="DESCRIPTION">Description</label>
											</div>
										</div>
                                    </div>
								
									<? if($_GET['id'] != ''){ ?>
									<div class="row">
										<div class="col-md-6">
											<div class="row form-group">
												<div class="custom-control col-md-2">Active</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="customRadio11">Yes</label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="customRadio22">No</label>
												</div>
											</div>
										</div>
									</div>
									<? } ?>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info">Submit</button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_ecm_ledger'" >Cancel</button>
												
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