<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$STATES = $_POST;
	if($_GET['id'] == ''){
		$STATES['CREATED_BY']  = $_SESSION['ADMIN_PK_USER'];
		$STATES['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('Z_STATES', $STATES, 'insert');
	} else {
		$STATES['EDITED_BY'] = $_SESSION['ADMIN_PK_USER'];
		$STATES['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('Z_STATES', $STATES, 'update'," PK_STATES = '$_GET[id]'");
	}
	header("location:manage_states");
}
if($_GET['id'] == ''){
	$PK_COUNTRY 	= '';
	$STATE_NAME 	= '';
	$STATE_CODE 	= '';
	$ACTIVE 		= '';
	
} else {
	$res = $db->Execute("SELECT * FROM Z_STATES WHERE PK_STATES = '$_GET[id]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_states");
		exit;
	}
	
	$PK_COUNTRY 	= $res->fields['PK_COUNTRY'];
	$STATE_NAME 	= $res->fields['STATE_NAME'];
	$STATE_CODE 	= $res->fields['STATE_CODE'];
	$ACTIVE 		= $res->fields['ACTIVE'];
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
	<title>States | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo "Add"; else echo "Edit"; ?> State </h4>
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
												<select class="form-control required-entry" id="PK_COUNTRY" name="PK_COUNTRY" >
													<option value="" ></option>
													<? $res_dd = $db->Execute("select PK_COUNTRY,NAME FROM Z_COUNTRY WHERE ACTIVE = 1"); 
													while (!$res_dd->EOF) { ?>
														<option value="<?=$res_dd->fields['PK_COUNTRY']?>" <? if($res_dd->fields['PK_COUNTRY'] == $PK_COUNTRY ) echo "selected"; ?> ><?=$res_dd->fields['NAME'] ?></option>
													<?	$res_dd->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_COUNTRY" >Country</label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="STATE_NAME" name="STATE_NAME" value="<?=$STATE_NAME?>" >
												<span class="bar"></span>
												<label for="STATE_NAME">State Name</label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="STATE_CODE" name="STATE_CODE" value="<?=$STATE_CODE?>" >
												<span class="bar"></span>
												<label for="STATE_CODE">State Code</label>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_states'" >Cancel</button>
												
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