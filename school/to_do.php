<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/to_do.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$TO_DO_LIST 				= $_POST;
	$TO_DO_LIST['COMPLETED'] 	= $_POST['COMPLETED'];
	if($TO_DO_LIST['DATE'] != '')
		$TO_DO_LIST['DATE'] = date("Y-m-d",strtotime($TO_DO_LIST['DATE']));
		
	if($_GET['id'] == ''){
		$TO_DO_LIST['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$TO_DO_LIST['PK_USER']     = $_SESSION['PK_USER'];
		$TO_DO_LIST['CREATED_BY']  = $_SESSION['PK_USER'];
		$TO_DO_LIST['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_TO_DO_LIST', $TO_DO_LIST, 'insert');
	} else {
		$TO_DO_LIST['EDITED_BY'] = $_SESSION['PK_USER'];
		$TO_DO_LIST['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_TO_DO_LIST', $TO_DO_LIST, 'update'," PK_TO_DO_LIST = '$_GET[id]' AND PK_USER = '$_SESSION[PK_USER]'");
	}
	
	if($_GET['p'] == 'i')
		$URL = "index";
	else
		$URL = "manage_to_do";
	header("location:".$URL);

}
if($_GET['id'] == ''){
	$HEADER 	= '';
	$CONTENT 	= '';
	$DATE 		= date("m/d/Y");
	$COMPLETED 	= '';
	$ACTIVE	 	= '';	
} else {
	$res = $db->Execute("SELECT * FROM S_TO_DO_LIST WHERE PK_TO_DO_LIST = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_to_do");
		exit;
	}
	$HEADER 	= $res->fields['HEADER'];
	$CONTENT 	= $res->fields['CONTENT'];
	$DATE 		= $res->fields['DATE'];
	$COMPLETED 	= $res->fields['COMPLETED'];
	$ACTIVE  	= $res->fields['ACTIVE'];
	
	if($DATE != '0000-00-00')
		$DATE = date("m/d/Y",strtotime($DATE));
	else
		$DATE = '';
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
	<title><?=TO_DO_LIST_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=TO_DO_LIST_PAGE_TITLE?> </h4>
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
												<input type="text" class="form-control required-entry" id="HEADER" name="HEADER" value="<?=$HEADER?>" />
												<span class="bar"></span>
												<label for="CONTENT"><?=HEADER?></label>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-6">
											<div class="form-group m-b-40">
												<textarea class="form-control" rows="2" id="CONTENT" name="CONTENT"><?=$CONTENT?></textarea>
												<span class="bar"></span>
												<label for="CONTENT"><?=COMMENTS?></label>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<input type="text" class="form-control date" id="DATE" name="DATE" value="<?=$DATE?>" >
												<span class="bar"></span>
												<label for="DATE"><?=DATE?></label>
											</div>
										</div>
										
										<div class="col-sm-3">
											<div class="d-flex">
												<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
													<input type="checkbox" class="custom-control-input" id="COMPLETED" name="COMPLETED" value="1" <? if($COMPLETED == 1) echo "checked"; ?> >
													<label class="custom-control-label" for="COMPLETED"><?=COMPLETED?></label>
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
												
												<? if($_GET['p'] == 'i')
													$URL = "index";
												else
													$URL = "manage_to_do"; ?>
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='<?=$URL?>'" ><?=CANCEL?></button>
												
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
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>

</body>

</html>