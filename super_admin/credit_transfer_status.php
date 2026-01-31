<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$CREDIT_TRANSFER_STATUS_MASTER = $_POST;
	if($_GET['id'] == ''){
		$CREDIT_TRANSFER_STATUS_MASTER['CREATED_BY']  = $_SESSION['ADMIN_PK_USER'];
		$CREDIT_TRANSFER_STATUS_MASTER['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_CREDIT_TRANSFER_STATUS_MASTER', $CREDIT_TRANSFER_STATUS_MASTER, 'insert');
	} else {
		$CREDIT_TRANSFER_STATUS_MASTER['EDITED_BY'] = $_SESSION['ADMIN_PK_USER'];
		$CREDIT_TRANSFER_STATUS_MASTER['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_CREDIT_TRANSFER_STATUS_MASTER', $CREDIT_TRANSFER_STATUS_MASTER, 'update'," PK_CREDIT_TRANSFER_STATUS_MASTER = '$_GET[id]'");
	}
	header("location:manage_credit_transfer_status");
}
if($_GET['id'] == ''){
	$CREDIT_TRANSFER_STATUS 	= '';
	$DESCRIPTION				= '';
	$SHOW_ON_TRANSCRIPT			= 1;
	$ACTIVE	 					= '';
	
} else {
	$res = $db->Execute("SELECT * FROM M_CREDIT_TRANSFER_STATUS_MASTER WHERE PK_CREDIT_TRANSFER_STATUS_MASTER = '$_GET[id]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_credit_transfer_status");
		exit;
	}
	
	$CREDIT_TRANSFER_STATUS = $res->fields['CREDIT_TRANSFER_STATUS'];
	$DESCRIPTION 			= $res->fields['DESCRIPTION'];
	$SHOW_ON_TRANSCRIPT 	= $res->fields['SHOW_ON_TRANSCRIPT'];
	$ACTIVE  		 		= $res->fields['ACTIVE'];
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
	<title>Credit Transfer Status | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo "Add"; else echo "Edit"; ?> Credit Transfer Status </h4>
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
												<input type="text" class="form-control required-entry" id="CREDIT_TRANSFER_STATUS" name="CREDIT_TRANSFER_STATUS" value="<?=$CREDIT_TRANSFER_STATUS?>" >
												<span class="bar"></span>
												<label for="CREDIT_TRANSFER_STATUS">Credit Transfer Status</label>
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
									
									<div class="row">
										<div class="col-md-6">
											<div class="row form-group">
												<div class="custom-control col-md-2">Show on Transcript</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="SHOW_ON_TRANSCRIPT_1" name="SHOW_ON_TRANSCRIPT" value="1" <? if($SHOW_ON_TRANSCRIPT == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="SHOW_ON_TRANSCRIPT_1">Yes</label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="SHOW_ON_TRANSCRIPT_2" name="SHOW_ON_TRANSCRIPT" value="0" <? if($SHOW_ON_TRANSCRIPT == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="SHOW_ON_TRANSCRIPT_2">No</label>
												</div>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_credit_transfer_status'" >Cancel</button>
												
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