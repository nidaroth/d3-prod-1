<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$HELP_SUB_CATEGORY = $_POST;
	if($_GET['id'] == ''){
		$HELP_SUB_CATEGORY['CREATED_BY']  = $_SESSION['ADMIN_PK_USER'];
		$HELP_SUB_CATEGORY['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_HELP_SUB_CATEGORY', $HELP_SUB_CATEGORY, 'insert');
	} else {
		$HELP_SUB_CATEGORY['EDITED_BY'] = $_SESSION['ADMIN_PK_USER'];
		$HELP_SUB_CATEGORY['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_HELP_SUB_CATEGORY', $HELP_SUB_CATEGORY, 'update'," PK_HELP_SUB_CATEGORY = '$_GET[id]'");
	}
	header("location:manage_help_sub_category");
}
if($_GET['id'] == ''){
	$PK_HELP_CATEGORY	= '';
	$HELP_SUB_CATEGORY 	= '';
	$DISPLAY_ORDER 		= '';
	$ACTIVE			 	= '';
	
} else {
	$res = $db->Execute("SELECT * FROM M_HELP_SUB_CATEGORY WHERE PK_HELP_SUB_CATEGORY = '$_GET[id]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_help_sub_category");
		exit;
	}
	
	$PK_HELP_CATEGORY 	= $res->fields['PK_HELP_CATEGORY'];
	$HELP_SUB_CATEGORY 	= $res->fields['HELP_SUB_CATEGORY'];
	$DISPLAY_ORDER 		= $res->fields['DISPLAY_ORDER'];
	$ACTIVE 		 	= $res->fields['ACTIVE'];
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
	<title>Help Subcategory | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo "Add"; else echo "Edit"; ?> Help Subcategory</h4>
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
												<select class="form-control required-entry" id="PK_HELP_CATEGORY" name="PK_HELP_CATEGORY" >
													<option value="" ></option>
													<? $res_dd = $db->Execute("select PK_HELP_CATEGORY,HELP_CATEGORY FROM M_HELP_CATEGORY WHERE ACTIVE = 1 ORDER BY HELP_CATEGORY ASC"); 
													while (!$res_dd->EOF) { ?>
														<option value="<?=$res_dd->fields['PK_HELP_CATEGORY']?>" <? if($res_dd->fields['PK_HELP_CATEGORY'] == $PK_HELP_CATEGORY ) echo "selected"; ?> ><?=$res_dd->fields['HELP_CATEGORY'] ?></option>
													<?	$res_dd->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_HELP_CATEGORY">Help Category</label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="HELP_SUB_CATEGORY" name="HELP_SUB_CATEGORY" value="<?=$HELP_SUB_CATEGORY?>" >
												<span class="bar"></span>
												<label for="HELP_SUB_CATEGORY">Help Subcategory</label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="DISPLAY_ORDER" name="DISPLAY_ORDER" value="<?=$DISPLAY_ORDER?>" >
												<span class="bar"></span>
												<label for="DISPLAY_ORDER">Dipsplay Order</label>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_help_sub_category'" >Cancel</button>
												
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