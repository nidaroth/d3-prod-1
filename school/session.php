<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/session.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$SESSION = $_POST;
	if($_GET['id'] == ''){
		$SESSION['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$SESSION['CREATED_BY']  = $_SESSION['PK_USER'];
		$SESSION['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_SESSION', $SESSION, 'insert');
	} else {
		$SESSION['EDITED_BY'] = $_SESSION['PK_USER'];
		$SESSION['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_SESSION', $SESSION, 'update'," PK_SESSION = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
	header("location:manage_session");
}
if($_GET['id'] == ''){
	$SESSION 				= '';
	$SESSION_ABBREVIATION	= '';
	$DISPLAY_ORDER 			= '';
	$SESSION 				= '';
	$ACTIVE	 				= '';
	
	$res = $db->Execute("SELECT MAX(DISPLAY_ORDER) AS DISPLAY_ORDER FROM M_SESSION WHERE PK_SESSION = '$_GET[id]' "); 
	$DISPLAY_ORDER 	= $res->fields['DISPLAY_ORDER'] + 1 ;
} else {
	$res = $db->Execute("SELECT * FROM M_SESSION WHERE PK_SESSION = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_session");
		exit;
	}
	
	$SESSION 				= $res->fields['SESSION'];
	$SESSION_ABBREVIATION	= $res->fields['SESSION_ABBREVIATION'];
	$DISPLAY_ORDER 			= $res->fields['DISPLAY_ORDER'];
	$COLOR 					= $res->fields['COLOR'];
	$ACTIVE  				= $res->fields['ACTIVE'];
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
	<title><?=SESSION_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=SESSION_PAGE_TITLE?> </h4>
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
												<input type="text" class="form-control required-entry" id="SESSION" name="SESSION" value="<?=$SESSION?>" >
												<span class="bar"></span>
												<label for="SESSION"><?=SESSION?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control " id="SESSION_ABBREVIATION" name="SESSION_ABBREVIATION" value="<?=$SESSION_ABBREVIATION?>" >
												<span class="bar"></span>
												<label for="SESSION_ABBREVIATION"><?=SESSION_ABBREVIATION?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control jscolor" id="COLOR" name="COLOR" value="<?=$COLOR?>" >
												<span class="bar"></span>
												<label for="COLOR"><?=COLOR?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="DISPLAY_ORDER" name="DISPLAY_ORDER" value="<?=$DISPLAY_ORDER?>" >
												<span class="bar"></span>
												<label for="DISPLAY_ORDER"><?=DISPLAY_ORDER?></label>
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
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_session'" ><?=CANCEL?></button>
												
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

	<script src="../backend_assets/dist/js/jscolor.js"></script>
</body>

</html>