<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/sap_warning.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
// $sap_pk_array=array('15','67','72','64');
// if(!in_array($_SESSION['PK_ACCOUNT'],$sap_pk_array))
// {   
// 	header("location:../school/index");
// 	exit;
// }
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$SAP_WARNING = $_POST;

	if($_GET['id'] == ''){
		$SAP_WARNING['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$SAP_WARNING['CREATED_BY']  = $_SESSION['PK_USER'];
		$SAP_WARNING['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_SAP_WARNING', $SAP_WARNING, 'insert');
		$PK_SAP_WARNING = $db->insert_ID();
	} else {
		$SAP_WARNING['EDITED_BY'] = $_SESSION['PK_USER'];
		$SAP_WARNING['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_SAP_WARNING', $SAP_WARNING, 'update'," PK_SAP_WARNING = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		
		$PK_SAP_WARNING = $_GET['id'];
	}
	header("location:manage_sap_warning");
}
if($_GET['id'] == ''){
	$SAP_WARNING 	= '';
	$DESCRIPTION 	= '';
	$ACTIVE	 		= 1;
} else {
	$res = $db->Execute("SELECT * FROM S_SAP_WARNING WHERE PK_SAP_WARNING = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_sap_warning");
		exit;
	}
	
	$SAP_WARNING 	= $res->fields['SAP_WARNING'];
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
	<title><?=MNU_SAP_WARNING?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=MNU_SAP_WARNING?> </h4>
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
														<input type="text" class="form-control required-entry" id="SAP_WARNING" name="SAP_WARNING" value="<?=$SAP_WARNING?>" >
														<span class="bar"></span>
														<label for="SAP_WARNING"><?=SAP_WARNING?></label>
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
												<div class="col-md-2">
													<div class="form-group m-b-40">
														<span class="bar"></span>
														<label for="IS_DEFAULT"><?=ACTIVE?></label>
													</div>
												</div>
												
												<div class="row form-group col-12 col-sm-10">
													<div class="custom-control custom-radio col-md-2">
														<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
														<label class="custom-control-label" for="customRadio11"><?=YES?></label>
													</div>
													<div class="custom-control custom-radio col-md-5">
														<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
														<label class="custom-control-label" for="customRadio22"><?=NO?></label>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											
										</div>
                                    </div>
									
									<div class="row">
										<div class="col-md-3">&nbsp;</div>
                                        <div class="col-md-6">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											
											<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_sap_warning'" ><?=CANCEL?></button>
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