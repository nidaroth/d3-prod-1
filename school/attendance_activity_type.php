<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/attendance_activity_type.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$ATTENDANCE_ACTIVITY_TYPE = $_POST;
	if($_GET['id'] == ''){
		$ATTENDANCE_ACTIVITY_TYPE['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$ATTENDANCE_ACTIVITY_TYPE['CREATED_BY']  = $_SESSION['PK_USER'];
		$ATTENDANCE_ACTIVITY_TYPE['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_ATTENDANCE_ACTIVITY_TYPE', $ATTENDANCE_ACTIVITY_TYPE, 'insert');
	} else {
		$ATTENDANCE_ACTIVITY_TYPE['EDITED_BY'] = $_SESSION['PK_USER'];
		$ATTENDANCE_ACTIVITY_TYPE['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_ATTENDANCE_ACTIVITY_TYPE', $ATTENDANCE_ACTIVITY_TYPE, 'update'," PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	header("location:manage_attendance_activity_type");
}
if($_GET['id'] == ''){
	$ATTENDANCE_ACTIVITY_TYPE 	= '';
	$ACTIVE	 		 			= '';
	
} else {
	$res = $db->Execute("SELECT * FROM M_ATTENDANCE_ACTIVITY_TYPE WHERE PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_attendance_activity_type");
		exit;
	}
	
	$ATTENDANCE_ACTIVITY_TYPE 	= $res->fields['ATTENDANCE_ACTIVITY_TYPE'];
	$ACTIVE  		 			= $res->fields['ACTIVE'];
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
	<title><?=MNU_ATTENDANCE_ACTIVITY_TYPE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=MNU_ATTENDANCE_ACTIVITY_TYPE?> </h4>
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
												<input type="text" class="form-control required-entry" id="ATTENDANCE_ACTIVITY_TYPE" name="ATTENDANCE_ACTIVITY_TYPE" value="<?=$ATTENDANCE_ACTIVITY_TYPE?>" >
												<span class="bar"></span>
												<label for="ATTENDANCE_ACTIVITY_TYPE"><?=ACTIVITY_TYPE?></label>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_attendance_activity_type'" ><?=CANCEL?></button>
												
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