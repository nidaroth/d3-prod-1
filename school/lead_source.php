<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/lead_source.php");
require_once("check_access.php");

if(check_access('SETUP_ADMISSION') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$LEAD_SOURCE = $_POST;
	if($_GET['id'] == ''){
		$LEAD_SOURCE['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$LEAD_SOURCE['CREATED_BY']  = $_SESSION['PK_USER'];
		$LEAD_SOURCE['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_LEAD_SOURCE', $LEAD_SOURCE, 'insert');
	} else {
		$LEAD_SOURCE['EDITED_BY'] = $_SESSION['PK_USER'];
		$LEAD_SOURCE['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_LEAD_SOURCE', $LEAD_SOURCE, 'update'," PK_LEAD_SOURCE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	header("location:manage_lead_source");
}
if($_GET['id'] == ''){
	$PK_LEAD_SOURCE_GROUP 	= '';
	$LEAD_SOURCE 			= '';
	$LEAD_GROUP 			= '';
	$DESCRIPTION 			= '';
	$ACTIVE	 				= '';
	
} else {
	$res = $db->Execute("SELECT * FROM M_LEAD_SOURCE WHERE PK_LEAD_SOURCE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
	if($res->RecordCount() == 0){
		header("location:manage_lead_source");
		exit;
	}
	$PK_LEAD_SOURCE_GROUP 	= $res->fields['PK_LEAD_SOURCE_GROUP'];
	$LEAD_SOURCE 			= $res->fields['LEAD_SOURCE'];
	$LEAD_GROUP 			= $res->fields['LEAD_GROUP'];
	$DESCRIPTION 			= $res->fields['DESCRIPTION'];
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
	<title><?=LEAD_SOURCE_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=LEAD_SOURCE_PAGE_TITLE?> </h4>
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
												<input type="text" class="form-control required-entry" id="LEAD_SOURCE" name="LEAD_SOURCE" value="<?=$LEAD_SOURCE?>" >
												<span class="bar"></span>
												<label for="LEAD_SOURCE"><?=LEAD_SOURCE?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<select name="PK_LEAD_SOURCE_GROUP" id="PK_LEAD_SOURCE_GROUP" class="form-control required-entry" >
													<option value=""></option>
													<? $res_dep = $db->Execute("select PK_LEAD_SOURCE_GROUP,LEAD_SOURCE_GROUP from M_LEAD_SOURCE_GROUP WHERE ACTIVE = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY LEAD_SOURCE_GROUP ASC ");
													while (!$res_dep->EOF) {  ?>
														<option class="<?=$class?>" value="<?=$res_dep->fields['PK_LEAD_SOURCE_GROUP']?>" <? if($res_dep->fields['PK_LEAD_SOURCE_GROUP'] == $PK_LEAD_SOURCE_GROUP) echo "selected"; ?> ><?=$res_dep->fields['LEAD_SOURCE_GROUP']?></option>
													<?	$res_dep->MoveNext();
													} 	?>
												</select>
												<span class="bar"></span>
												<label for="LEAD_GROUP"><?=LEAD_GROUP?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="DESCRIPTION" name="DESCRIPTION" value="<?=$DESCRIPTION?>" >
												<span class="bar"></span>
												<label for="DESCRIPTION"><?=DESCRIPTION?></label>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_lead_source'" ><?=CANCEL?></button>
												
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