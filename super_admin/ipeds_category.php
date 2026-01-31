<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$IPEDS_CATEGORY_MASTER = $_POST;
	if($_GET['id'] == ''){
		$IPEDS_CATEGORY_MASTER['CREATED_BY']  = $_SESSION['ADMIN_PK_USER'];
		$IPEDS_CATEGORY_MASTER['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_IPEDS_CATEGORY_MASTER', $IPEDS_CATEGORY_MASTER, 'insert');
	} else {
		$IPEDS_CATEGORY_MASTER['EDITED_BY'] = $_SESSION['ADMIN_PK_USER'];
		$IPEDS_CATEGORY_MASTER['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_IPEDS_CATEGORY_MASTER', $IPEDS_CATEGORY_MASTER, 'update'," PK_IPEDS_CATEGORY_MASTER = '$_GET[id]'");
	}
	header("location:manage_ipeds_category");
}
if($_GET['id'] == ''){
	$IPEDS_CATEGORY = '';
	$ACTIVE	 		= '';
	
} else {
	$res = $db->Execute("SELECT * FROM M_IPEDS_CATEGORY_MASTER WHERE PK_IPEDS_CATEGORY_MASTER = '$_GET[id]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_ipeds_category");
		exit;
	}
	
	$IPEDS_CATEGORY = $res->fields['IPEDS_CATEGORY'];
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
	<title>IPEDS Category | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo "Add"; else echo "Edit"; ?> IPEDS Category </h4>
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
												<input type="text" class="form-control required-entry" id="IPEDS_CATEGORY" name="IPEDS_CATEGORY" value="<?=$IPEDS_CATEGORY?>" >
												<span class="bar"></span>
												<label for="IPEDS_CATEGORY">IPEDS Category</label>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_ipeds_category'" >Cancel</button>
												
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