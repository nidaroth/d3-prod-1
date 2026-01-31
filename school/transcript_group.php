<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/transcript_group.php");
require_once("check_access.php");

if(check_access('SETUP_STUDENT') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$TRANSCRIPT_GROUP = $_POST;
	if($_GET['id'] == ''){
		$TRANSCRIPT_GROUP['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$TRANSCRIPT_GROUP['CREATED_BY']  = $_SESSION['PK_USER'];
		$TRANSCRIPT_GROUP['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_TRANSCRIPT_GROUP', $TRANSCRIPT_GROUP, 'insert');
	} else {
		$TRANSCRIPT_GROUP['EDITED_BY'] = $_SESSION['PK_USER'];
		$TRANSCRIPT_GROUP['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_TRANSCRIPT_GROUP', $TRANSCRIPT_GROUP, 'update'," PK_TRANSCRIPT_GROUP = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
	header("location:manage_transcript_group");
}
if($_GET['id'] == ''){
	$TRANSCRIPT_GROUP 	= '';
	$DESCRIPTION 		= '';
	$WEIGHTED			= '';
	$ACTIVE	 			= '';
	
	$TRANSCRIPT_DETAIL_SORT_ORDER_TYPE	= '';
	$TRANSCRIPT_GROUP_SORT_ORDER		= '';
	
} else {
	$res = $db->Execute("SELECT * FROM M_TRANSCRIPT_GROUP WHERE PK_TRANSCRIPT_GROUP = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_transcript_group");
		exit;
	}
	
	$TRANSCRIPT_GROUP 	= $res->fields['TRANSCRIPT_GROUP'];
	$DESCRIPTION 		= $res->fields['DESCRIPTION'];
	$WEIGHTED 			= $res->fields['WEIGHTED'];
	$ACTIVE  			= $res->fields['ACTIVE'];
	
	$TRANSCRIPT_DETAIL_SORT_ORDER_TYPE 	= $res->fields['TRANSCRIPT_DETAIL_SORT_ORDER_TYPE'];
	$TRANSCRIPT_GROUP_SORT_ORDER 		= $res->fields['TRANSCRIPT_GROUP_SORT_ORDER'];
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
	<title><?=TRANSCRIPT_GROUP_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else EDIT; ?> <?=TRANSCRIPT_GROUP_PAGE_TITLE?></h4>
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
														<input type="text" class="form-control required-entry" id="TRANSCRIPT_GROUP" name="TRANSCRIPT_GROUP" value="<?=$TRANSCRIPT_GROUP?>" >
														<span class="bar"></span>
														<label for="TRANSCRIPT_GROUP"><?=TRANSCRIPT_GROUP?></label>
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
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="TRANSCRIPT_GROUP_SORT_ORDER" name="TRANSCRIPT_GROUP_SORT_ORDER" value="<?=$TRANSCRIPT_GROUP_SORT_ORDER?>" >
														<span class="bar"></span>
														<label for="TRANSCRIPT_GROUP_SORT_ORDER"><?=ORDER?></label>
													</div>
												</div>
											</div>
											
										</div>
										
										 <div class="col-md-6">
											<div class="row">
												<div class="col-md-12">
													<div class="row form-group">
														<div class="custom-control col-md-4"><?=TRANSCRIPT_DETAIL_SORT_ORDER_TYPE?></div>
														<div class="custom-control custom-radio col-md-3">
															<input type="radio" id="TRANSCRIPT_DETAIL_SORT_ORDER_TYPE_1" name="TRANSCRIPT_DETAIL_SORT_ORDER_TYPE" value="1" <? if($TRANSCRIPT_DETAIL_SORT_ORDER_TYPE == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="TRANSCRIPT_DETAIL_SORT_ORDER_TYPE_1"><?=COURSE_CODE?></label>
														</div>
														<div class="custom-control custom-radio col-md-1">
															<input type="radio" id="TRANSCRIPT_DETAIL_SORT_ORDER_TYPE_2" name="TRANSCRIPT_DETAIL_SORT_ORDER_TYPE" value="2" <? if($TRANSCRIPT_DETAIL_SORT_ORDER_TYPE == 2) echo "checked"; ?>  class="custom-control-input">
															<label class="custom-control-label" for="TRANSCRIPT_DETAIL_SORT_ORDER_TYPE_2"><?=TERM?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="row form-group">
														<div class="custom-control col-md-4"><?=WEIGHTED?></div>
														<div class="custom-control custom-radio col-md-1">
															<input type="radio" id="WEIGHTED_1" name="WEIGHTED" value="1" <? if($WEIGHTED == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="WEIGHTED_1"><?=YES?></label>
														</div>
														<div class="custom-control custom-radio col-md-1">
															<input type="radio" id="WEIGHTED_2" name="WEIGHTED" value="0" <? if($WEIGHTED == 0) echo "checked"; ?>  class="custom-control-input">
															<label class="custom-control-label" for="WEIGHTED_2"><?=NO?></label>
														</div>
													</div>
												</div>
											</div>
											
											<? if($_GET['id'] != ''){ ?>
											<div class="row">
												<div class="col-md-12">
													<div class="row form-group">
														<div class="custom-control col-md-4"><?=ACTIVE?></div>
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
											
										</div>
									</div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_transcript_group'" ><?=CANCEL?></button>
												
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