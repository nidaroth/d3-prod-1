<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/placement_status.php");
require_once("check_access.php");

if(check_access('SETUP_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$PLACEMENT_STATUS = $_POST;
	if($_GET['id'] == ''){
		$PLACEMENT_STATUS['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$PLACEMENT_STATUS['CREATED_BY']  = $_SESSION['PK_USER'];
		$PLACEMENT_STATUS['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_PLACEMENT_STATUS', $PLACEMENT_STATUS, 'insert');
	} else {
		$PLACEMENT_STATUS['EDITED_BY'] = $_SESSION['PK_USER'];
		$PLACEMENT_STATUS['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_PLACEMENT_STATUS', $PLACEMENT_STATUS, 'update'," PK_PLACEMENT_STATUS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	header("location:manage_placement_status");
}
if($_GET['id'] == ''){
	$PLACEMENT_STATUS 	= '';
	$EMPLOYED 	 		= '';
	$PK_PLACEMENT_STUDENT_STATUS_CATEGORY 	 		= '';
	$ACTIVE	 		 	= '';
} else {
	$res = $db->Execute("SELECT * FROM M_PLACEMENT_STATUS WHERE PK_PLACEMENT_STATUS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_placement_status");
		exit;
	}
	$PLACEMENT_STATUS 								= $res->fields['PLACEMENT_STATUS'];
	$EMPLOYED 	 									= $res->fields['EMPLOYED'];
	$PK_PLACEMENT_STUDENT_STATUS_CATEGORY 	 		= $res->fields['PK_PLACEMENT_STUDENT_STATUS_CATEGORY'];
	$ACTIVE  		 								= $res->fields['ACTIVE'];
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
	<title><?=PLACEMENT_STATUS_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=PLACEMENT_STATUS_PAGE_TITLE?> </h4>
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
												<input type="text" class="form-control required-entry" id="PLACEMENT_STATUS" name="PLACEMENT_STATUS" value="<?=$PLACEMENT_STATUS?>" >
												<span class="bar"></span>
												<label for="PLACEMENT_STATUS"><?=PLACEMENT_STATUS?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_PLACEMENT_STUDENT_STATUS_CATEGORY" name="PK_PLACEMENT_STUDENT_STATUS_CATEGORY" class="form-control">
													<option></option>
													<? $res_type = $db->Execute("select PK_PLACEMENT_STUDENT_STATUS_CATEGORY,PLACEMENT_STUDENT_STATUS_CATEGORY from M_PLACEMENT_STUDENT_STATUS_CATEGORY WHERE  ACTIVE = 1 order by PLACEMENT_STUDENT_STATUS_CATEGORY ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_PLACEMENT_STUDENT_STATUS_CATEGORY']?>" <? if($res_type->fields['PK_PLACEMENT_STUDENT_STATUS_CATEGORY'] == $PK_PLACEMENT_STUDENT_STATUS_CATEGORY) echo "selected"; ?> ><?=$res_type->fields['PLACEMENT_STUDENT_STATUS_CATEGORY'] ?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_PLACEMENT_STUDENT_STATUS_CATEGORY"><?=CATEGORY?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
										<div class="col-md-6">
											<div class="row form-group">
												<div class="custom-control col-md-3"><?=EMPLOYED?></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio11" name="EMPLOYED" value="1" <? if($EMPLOYED == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="customRadio11">Yes</label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio22" name="EMPLOYED" value="0" <? if($EMPLOYED == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="customRadio22">No</label>
												</div>
											</div>
										</div>
									</div>
									
									<? if($_GET['id'] != ''){ ?>
									<div class="row">
										<div class="col-md-6">
											<div class="row form-group">
												<div class="custom-control col-md-3"><?=ACTIVE?></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio111" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="customRadio111"><?=YES?></label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio221" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="customRadio221"><?=NO?></label>
												</div>
											</div>
										</div>
									</div>
									<? } ?>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_placement_status'" ><?=CANCEL?></button>
												
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