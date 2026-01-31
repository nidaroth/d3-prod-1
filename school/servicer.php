<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/servicer.php");
require_once("check_access.php");

if(check_access('SETUP_FINANCE') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$SERVICER = $_POST;
	if($_GET['id'] == ''){
		$SERVICER['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$SERVICER['CREATED_BY']  = $_SESSION['PK_USER'];
		$SERVICER['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_SERVICER', $SERVICER, 'insert');
	} else {
		$SERVICER['EDITED_BY'] = $_SESSION['PK_USER'];
		$SERVICER['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_SERVICER', $SERVICER, 'update'," PK_SERVICER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	header("location:manage_servicer");
}
if($_GET['id'] == ''){
	$ITEM 			= '';
	$DESCRIPTION 	= '';
	$ADDRESS 		= '';
	$ADDRESS_1 		= '';
	$CITY 			= '';
	$PK_STATES 		= '';
	$ZIP 			= '';
	$PHONE 			= '';
	$ITEM 			= '';
	$EMAIL	 		= '';
	
} else {
	$res = $db->Execute("SELECT * FROM M_SERVICER WHERE PK_SERVICER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_servicer");
		exit;
	}
	
	$ITEM 			= $res->fields['ITEM'];
	$DESCRIPTION 	= $res->fields['DESCRIPTION'];
	$ADDRESS 		= $res->fields['ADDRESS'];
	$ADDRESS_1 		= $res->fields['ADDRESS_1'];
	$CITY 			= $res->fields['CITY'];
	$PK_STATES 		= $res->fields['PK_STATES'];
	$ZIP 			= $res->fields['ZIP'];
	$PHONE 			= $res->fields['PHONE'];
	$EMAIL 			= $res->fields['EMAIL'];

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
	<title><?=SERVICER_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=SERVICER_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row">
                                        <div class="col-md-6">
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control required-entry" id="ITEM" name="ITEM" value="<?=$ITEM?>" >
														<span class="bar"></span>
														<label for="ITEM"><?=SERVICER_NAME?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="DESCRIPTION" name="DESCRIPTION" value="<?=$DESCRIPTION?>" >
														<span class="bar"></span>
														<label for="DESCRIPTION"><?=DESCRIPTION?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input id="PHONE" name="PHONE" type="text" class="form-control phone-inputmask" value="<?=$PHONE?>">
														<span class="bar"></span> 
														<label for="PHONE"><?=PHONE?></label>
													</div>
												</div>
												
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input id="EMAIL" name="EMAIL" type="text" class="form-control" value="<?=$EMAIL?>" >
														<span class="bar"></span> 
														<label for="EMAIL"><?=EMAIL?></label>
													</div>
												</div>
											</div>
											
										</div>
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input id="ADDRESS" name="ADDRESS" type="text" class="form-control" value="<?=$ADDRESS?>">
														<span class="bar"></span>
														<label for="ADDRESS"><?=ADDRESS?></label>
													</div>
												</div>
										   
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input id="ADDRESS_1" name="ADDRESS_1" type="text" class="form-control" value="<?=$ADDRESS_1?>">
														<span class="bar"></span>
														<label for="ADDRESS_1"><?=ADDRESS_1?></label>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input id="CITY" name="CITY" type="text" class="form-control" value="<?=$CITY?>">
														<span class="bar"></span> 
														<label for="CITY"><?=CITY?></label>
													</div>
												</div>
										   
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_STATES" name="PK_STATES" class="form-control" >
															<option selected></option>
															 <? $res_type = $db->Execute("select PK_STATES, STATE_NAME from Z_STATES WHERE ACTIVE = '1' ORDER BY STATE_NAME ASC ");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_STATES'] ?>" <? if($PK_STATES == $res_type->fields['PK_STATES']) echo "selected"; ?> ><?=$res_type->fields['STATE_NAME']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_STATES"><?=STATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input id="ZIP" name="ZIP" type="text" class="form-control" value="<?=$ZIP?>">
														<span class="bar"></span> 
														<label for="ZIP"><?=ZIP?></label>
													</div>
												</div>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_servicer'" ><?=CANCEL?></button>
												
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