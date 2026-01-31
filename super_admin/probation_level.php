<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$PROBATION_LEVEL = $_POST;
	if($_GET['id'] == ''){
		$PROBATION_LEVEL['CREATED_BY']  = $_SESSION['ADMIN_PK_USER'];
		$PROBATION_LEVEL['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_PROBATION_LEVEL', $PROBATION_LEVEL, 'insert');
	} else {
		$PROBATION_LEVEL['EDITED_BY'] = $_SESSION['ADMIN_PK_USER'];
		$PROBATION_LEVEL['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_PROBATION_LEVEL', $PROBATION_LEVEL, 'update'," PK_PROBATION_LEVEL = '$_GET[id]'");
	}
	header("location:manage_probation_level");
}
if($_GET['id'] == ''){
	$PROBATION_LEVEL = '';
	$SORT_ORDER		 = '';
	$ACTIVE			 = '';
	
} else {
	$res = $db->Execute("SELECT * FROM M_PROBATION_LEVEL WHERE PK_PROBATION_LEVEL = '$_GET[id]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_probation_level");
		exit;
	}
	
	$PROBATION_LEVEL = $res->fields['PROBATION_LEVEL'];
	$SORT_ORDER 	 = $res->fields['SORT_ORDER'];
	$ACTIVE 		 = $res->fields['ACTIVE'];
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
	<title>Probation Level | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo "Add"; else echo "Edit"; ?> Probation Level </h4>
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
												<input type="text" class="form-control required-entry" id="PROBATION_LEVEL" name="PROBATION_LEVEL" value="<?=$PROBATION_LEVEL?>" >
												<span class="bar"></span>
												<label for="PROBATION_LEVEL">Probation Level</label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="SORT_ORDER" name="SORT_ORDER" value="<?=$SORT_ORDER?>" >
												<span class="bar"></span>
												<label for="SORT_ORDER">Sort Order</label>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_probation_level'" >Cancel</button>
												
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