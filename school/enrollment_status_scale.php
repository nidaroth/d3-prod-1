<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/enrollment_status_scale.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	// echo "<pre>";print_r($_POST);exit;
	
	$ENROLLMENT_STATUS_SCALE_MASTER['ENROLLMENT_STATUS'] 	= $_POST['ENROLLMENT_STATUS'];
	$ENROLLMENT_STATUS_SCALE_MASTER['FA_UNITS_HOUR_UNITS'] 	= $_POST['FA_UNITS_HOUR_UNITS'];
	if($_GET['id'] == ''){
		$ENROLLMENT_STATUS_SCALE_MASTER['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
		$ENROLLMENT_STATUS_SCALE_MASTER['CREATED_BY']  			= $_SESSION['PK_USER'];
		$ENROLLMENT_STATUS_SCALE_MASTER['CREATED_ON']  			= date("Y-m-d H:i");
		db_perform('M_ENROLLMENT_STATUS_SCALE_MASTER', $ENROLLMENT_STATUS_SCALE_MASTER, 'insert');
		$PK_ENROLLMENT_STATUS_SCALE_MASTER = $db->insert_ID();
	} else {
		$ENROLLMENT_STATUS_SCALE_MASTER['EDITED_BY'] 			= $_SESSION['PK_USER'];
		$ENROLLMENT_STATUS_SCALE_MASTER['EDITED_ON'] 			= date("Y-m-d H:i");
		$ENROLLMENT_STATUS_SCALE_MASTER['ACTIVE']				= $_POST['ACTIVE'];
		db_perform('M_ENROLLMENT_STATUS_SCALE_MASTER', $ENROLLMENT_STATUS_SCALE_MASTER, 'update'," PK_ENROLLMENT_STATUS_SCALE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$PK_ENROLLMENT_STATUS_SCALE_MASTER = $_GET['id'];
	}
	foreach($_POST['PK_SCHOOL_ENROLLMENT_STATUS'] as $key => $PK_SCHOOL_ENROLLMENT_STATUS){
		$ENROLLMENT_STATUS_SCALE['PK_ENROLLMENT_STATUS_SCALE_MASTER'] 	= $PK_ENROLLMENT_STATUS_SCALE_MASTER;
		$ENROLLMENT_STATUS_SCALE['PK_SCHOOL_ENROLLMENT_STATUS'] 		= $_POST['PK_SCHOOL_ENROLLMENT_STATUS'][$key];
		$ENROLLMENT_STATUS_SCALE['MIN_UNITS_PER_TERM']  				= $_POST['MIN_UNITS_PER_TERM'][$key];
		
		$res = $db->Execute("SELECT PK_ENROLLMENT_STATUS_SCALE, ACTIVE FROM M_ENROLLMENT_STATUS_SCALE WHERE PK_ENROLLMENT_STATUS_SCALE_MASTER = '$PK_ENROLLMENT_STATUS_SCALE_MASTER' AND PK_SCHOOL_ENROLLMENT_STATUS='".$_POST['PK_SCHOOL_ENROLLMENT_STATUS'][$key]."' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
		if($res->RecordCount() == 0){
			$ENROLLMENT_STATUS_SCALE['PK_ACCOUNT']	= $_SESSION['PK_ACCOUNT'];
			$ENROLLMENT_STATUS_SCALE['CREATED_BY']	= $_SESSION['PK_USER'];
			$ENROLLMENT_STATUS_SCALE['CREATED_ON']	= date("Y-m-d H:i");
			db_perform('M_ENROLLMENT_STATUS_SCALE', $ENROLLMENT_STATUS_SCALE, 'insert');
		} else {
			$ENROLLMENT_STATUS_SCALE['EDITED_BY']	= $_SESSION['PK_USER'];
			$ENROLLMENT_STATUS_SCALE['EDITED_ON']	= date("Y-m-d H:i");
			$ENROLLMENT_STATUS_SCALE['ACTIVE']		= $_POST['ACTIVE'];
			db_perform('M_ENROLLMENT_STATUS_SCALE', $ENROLLMENT_STATUS_SCALE, 'update', " PK_ENROLLMENT_STATUS_SCALE_MASTER = '$PK_ENROLLMENT_STATUS_SCALE_MASTER' AND PK_SCHOOL_ENROLLMENT_STATUS='".$_POST['PK_SCHOOL_ENROLLMENT_STATUS'][$key]."' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		}
	}
	header("location:manage_enrollment_status_scale");
}
if($_GET['id'] == ''){
	$ENROLLMENT_STATUS_SCALE		= '';
	$FA_UNITS_HOUR_UNITS			= 3;
	$PK_SCHOOL_ENROLLMENT_STATUS	= '';
	$ACTIVE  		 				= '';
	
} else {
	$res = $db->Execute("SELECT PK_ENROLLMENT_STATUS_SCALE_MASTER, ENROLLMENT_STATUS, FA_UNITS_HOUR_UNITS, ACTIVE FROM M_ENROLLMENT_STATUS_SCALE_MASTER WHERE PK_ENROLLMENT_STATUS_SCALE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	// echo "SELECT PK_ENROLLMENT_STATUS_SCALE_MASTER, ENROLLMENT_STATUS, ACTIVE FROM M_ENROLLMENT_STATUS_SCALE_MASTER WHERE PK_ENROLLMENT_STATUS_SCALE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";exit;
	
	if($res->RecordCount() == 0){
		header("location:manage_enrollment_status_scale");
		exit;
	}
	$ENROLLMENT_STATUS		= $res->fields['ENROLLMENT_STATUS'];
	$FA_UNITS_HOUR_UNITS	= $res->fields['FA_UNITS_HOUR_UNITS'];
	$ACTIVE  		 		= $res->fields['ACTIVE'];
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
	<title><?=ENROLLMENT_STATUS_SCALE_PAGE_TITLE?> | <?=$title?></title>
	<style>
		input::-webkit-outer-spin-button,
		input::-webkit-inner-spin-button {
		  -webkit-appearance: none;
		  margin: 0;
		}

		/* Firefox */
		input[type=number] {
		  -moz-appearance: textfield;
		}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=ENROLLMENT_STATUS_SCALE_PAGE_TITLE?> </h4>
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
												<input type="text" class="form-control required-entry" id="ENROLLMENT_STATUS" name="ENROLLMENT_STATUS" value="<?=$ENROLLMENT_STATUS?>" >
												<span class="bar"></span>
												<label for="ENROLLMENT_STATUS"><?=ENROLLMENT_STATUS?></label>
											</div>
										</div>
									</div>
									
									<div class="row form-group">
										<div class="custom-control custom-radio col-md-2">
											<input type="radio" id="FA_UNITS_HOUR_UNITS_1" name="FA_UNITS_HOUR_UNITS"  value="1" <? if($FA_UNITS_HOUR_UNITS == 1) echo "checked"; ?> class="custom-control-input">
											<label class="custom-control-label" for="FA_UNITS_HOUR_UNITS_1" ><?=FA_UNITS ?></label>
										</div>
										<div class="custom-control custom-radio col-md-2">
											<input type="radio" id="FA_UNITS_HOUR_UNITS_2" name="FA_UNITS_HOUR_UNITS" value="2"  <? if($FA_UNITS_HOUR_UNITS == 2) echo "checked"; ?> class="custom-control-input">
											<label class="custom-control-label" for="FA_UNITS_HOUR_UNITS_2" ><?=HOUR ?></label>
										</div>
										<div class="custom-control custom-radio col-md-2">
											<input type="radio" id="FA_UNITS_HOUR_UNITS_3" name="FA_UNITS_HOUR_UNITS" value="3"  <? if($FA_UNITS_HOUR_UNITS == 3) echo "checked"; ?> class="custom-control-input">
											<label class="custom-control-label" for="FA_UNITS_HOUR_UNITS_3" ><?=UNITS ?></label>
										</div>
									</div>
									
									
									<div class="row">
										<div class="col-md-8">
											<div class="form-group m-b-40">
												<div class="d-flex mb-1">
													<div class="col-md-3">
														<b><?=CODE ?></b>
													</div>
													<div class="col-md-3">
														<b><?=DESCRIPTION ?></b>
													</div>
													<div class="col-md-6">
														<b><?=MIN_UNITS_PER_TERM ?></b>
													</div>
												</div>
												<? $res_type = $db->Execute("select PK_SCHOOL_ENROLLMENT_STATUS, CODE, DESCRIPTION from M_SCHOOL_ENROLLMENT_STATUS WHERE ACTIVE = '1' ORDER BY CREATED_BY DESC ");
												while (!$res_type->EOF) { ?>
													<div class="d-flex mb-1">
														<div class="col-md-3">
															<?=$res_type->fields['CODE'] ?>
															<input type="hidden" name="PK_SCHOOL_ENROLLMENT_STATUS[]" value="<?=$res_type->fields['PK_SCHOOL_ENROLLMENT_STATUS'];?>">
														</div>
														<div class="col-md-3">
															<?=$res_type->fields['DESCRIPTION'] ?>
														</div>
														<div class="col-md-6">
															<?  $MIN_UNITS_PER_TERM = "";
															if($_GET['id'] != ''){
																$res_scale = $db->Execute("select * from M_ENROLLMENT_STATUS_SCALE WHERE PK_SCHOOL_ENROLLMENT_STATUS = '".$res_type->fields['PK_SCHOOL_ENROLLMENT_STATUS']."' AND PK_ENROLLMENT_STATUS_SCALE_MASTER='".$_GET['id']."' AND PK_ACCOUNT='$_SESSION[PK_ACCOUNT]'");
																$MIN_UNITS_PER_TERM = $res_scale->fields['MIN_UNITS_PER_TERM'];
															} ?>
															<input type="number" min="0" oninput="validity.valid||(value='');" class="form-control required-entry" name="MIN_UNITS_PER_TERM[]"  id="MIN_UNITS_PER_TERM_<?=$res_type->fields['PK_SCHOOL_ENROLLMENT_STATUS'];?>" value="<?=$MIN_UNITS_PER_TERM?>">
														</div>
													</div>
												<?	$res_type->MoveNext();
												} ?>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_enrollment_status_scale'" ><?=CANCEL?></button>
												
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