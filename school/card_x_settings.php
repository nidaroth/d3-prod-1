<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/card_x_settings.php");
require_once("../language/school_profile.php");
require_once("check_access.php");

header("location:index");
exit;

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}
$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("select * from S_CARD_X_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$CARD_X_SETTINGS = $_POST;
	if($res->RecordCount() == 0){
		$CARD_X_SETTINGS['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$CARD_X_SETTINGS['CREATED_BY'] 	= $_SESSION['PK_USER'];
		$CARD_X_SETTINGS['CREATED_ON'] 	= date("Y-m-d H:i:s");
		db_perform('S_CARD_X_SETTINGS', $CARD_X_SETTINGS, 'insert');
		$PK_CARD_X_SETTINGS = $db->insert_ID();
	} else {
		$CARD_X_SETTINGS['EDITED_BY'] = $_SESSION['PK_USER'];
		$CARD_X_SETTINGS['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_CARD_X_SETTINGS', $CARD_X_SETTINGS, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_CARD_X_SETTINGS = $_GET['id'];
	}
	
	$ACCOUNT['CHARGE_PROCESSING_FEE_FROM_STUDENT'] 	= $_POST['CHARGE_PROCESSING_FEE_FROM_STUDENT'];
	db_perform('Z_ACCOUNT', $ACCOUNT, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	header("location:card_x_settings");
}
$res = $db->Execute("select * from S_CARD_X_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$PUBLISHER_NAME 	= $res->fields['PUBLISHER_NAME'];
$PUBLISHER_PASSWORD = $res->fields['PUBLISHER_PASSWORD'];
$SITE_KEY 			= $res->fields['SITE_KEY'];
$API_KEY_NAME 		= $res->fields['API_KEY_NAME'];
$API_KEY 			= $res->fields['API_KEY'];

$res = $db->Execute("select CHARGE_PROCESSING_FEE_FROM_STUDENT from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$CHARGE_PROCESSING_FEE_FROM_STUDENT = $res->fields['CHARGE_PROCESSING_FEE_FROM_STUDENT'];
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
	<title><?=CARD_X_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=CARD_X_TITLE?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input type="text" class="form-control required-entry" id="PUBLISHER_NAME" name="PUBLISHER_NAME" value="<?=$PUBLISHER_NAME?>" placeholder=""  >
											<span class="bar"></span> 
											<label for="PUBLISHER_NAME"><?=PUBLISHER_NAME?></label>
										</div>
										
										<div class="col-12 col-sm-6 form-group">
											<div class="row">
												<div class="col-md-12">
													<h4><b><?=QUICK_PAYMENT_OPTIONS?></b></h4>
												</div>
											</div>
									
											<div class="d-flex">
												<div class="col-sm-6 form-group">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="CHARGE_PROCESSING_FEE_FROM_STUDENT" name="CHARGE_PROCESSING_FEE_FROM_STUDENT" value="1" <? if($CHARGE_PROCESSING_FEE_FROM_STUDENT == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="CHARGE_PROCESSING_FEE_FROM_STUDENT"><?=CHARGE_PROCESSING_FEE_FROM_STUDENT?>?</label>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input type="text" class="form-control required-entry" id="PUBLISHER_PASSWORD" name="PUBLISHER_PASSWORD" value="<?=$PUBLISHER_PASSWORD?>" placeholder=""  >
											<span class="bar"></span> 
											<label for="PUBLISHER_PASSWORD"><?=PUBLISHER_PASSWORD?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group focused">
											<input id="SITE_KEY" name="SITE_KEY" type="text" class="form-control required-entry" value="<?=$SITE_KEY?>">
											<span class="bar"></span> 
											<label for="SITE_KEY"><?=SITE_KEY?></label>
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group focused">
											<input id="API_KEY_NAME" name="API_KEY_NAME" type="text" class="form-control required-entry" value="<?=$API_KEY_NAME?>">
											<span class="bar"></span> 
											<label for="API_KEY_NAME"><?=API_KEY_NAME?></label>
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group focused">
											<input id="API_KEY" name="API_KEY" type="text" class="form-control required-entry" value="<?=$API_KEY?>">
											<span class="bar"></span> 
											<label for="API_KEY"><?=API_KEY?></label>
										</div>
									</div>
									
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										
										<div class="col-9 col-sm-9">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											<button type="button" onclick="window.location.href='index'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
										</div>
									</div>
								</div>
							</form>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <div id="confirm-modal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><?=DELETE_CONFIRMATION?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <p><?=IMAGE_DELETE?></p>
                        </form>
                    </div>
                    <div class="modal-footer">
						<button type="button" onclick="conf_delete()" class="btn btn-danger waves-effect waves-light"><?=YES?></button>
                        <button type="button" class="btn btn-default waves-effect" data-dismiss="modal"><?=NO?></button>
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
		function conf_delete(){
			jQuery(document).ready(function($) {
				window.location.href = 'profile?act=delImg';
			});	
		}
	</script>

</body>

</html>