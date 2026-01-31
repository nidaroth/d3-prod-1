<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering_student_status.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$COURSE_OFFERING_STUDENT_STATUS = $_POST;
	
	if($_POST['MAKE_AS_DEFAULT'] == 1) {
		$db->Execute("UPDATE M_COURSE_OFFERING_STUDENT_STATUS SET MAKE_AS_DEFAULT = 0 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
	}
	
	if($_GET['id'] == ''){
		$COURSE_OFFERING_STUDENT_STATUS['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$COURSE_OFFERING_STUDENT_STATUS['CREATED_BY']  = $_SESSION['PK_USER'];
		$COURSE_OFFERING_STUDENT_STATUS['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_COURSE_OFFERING_STUDENT_STATUS', $COURSE_OFFERING_STUDENT_STATUS, 'insert');
	} else {
		$COURSE_OFFERING_STUDENT_STATUS['EDITED_BY'] = $_SESSION['PK_USER'];
		$COURSE_OFFERING_STUDENT_STATUS['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_COURSE_OFFERING_STUDENT_STATUS', $COURSE_OFFERING_STUDENT_STATUS, 'update'," PK_COURSE_OFFERING_STUDENT_STATUS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	header("location:manage_course_offering_student_status");
}
if($_GET['id'] == ''){
	$COURSE_OFFERING_STUDENT_STATUS 	= '';
	$POST_TUITION 			= 0;
	$SHOW_ON_TRANSCRIPT 	= 0;
	$SHOW_ON_REPORT_CARD 	= 0;
	$CALCULATE_SAP 			= 0;
	$MAKE_AS_DEFAULT		= 0;
	$DESCRIPTION 			= '';
	$ACTIVE	 				= '';
	
} else {
	$res = $db->Execute("SELECT * FROM M_COURSE_OFFERING_STUDENT_STATUS WHERE PK_COURSE_OFFERING_STUDENT_STATUS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	
	if($res->RecordCount() == 0){
		header("location:manage_course_offering_student_status");
		exit;
	}
	
	$COURSE_OFFERING_STUDENT_STATUS 	= $res->fields['COURSE_OFFERING_STUDENT_STATUS'];
	$DESCRIPTION 			= $res->fields['DESCRIPTION'];
	$POST_TUITION 			= $res->fields['POST_TUITION'];
	$SHOW_ON_TRANSCRIPT 	= $res->fields['SHOW_ON_TRANSCRIPT'];
	$SHOW_ON_REPORT_CARD 	= $res->fields['SHOW_ON_REPORT_CARD'];
	$CALCULATE_SAP 			= $res->fields['CALCULATE_SAP'];
	$MAKE_AS_DEFAULT 		= $res->fields['MAKE_AS_DEFAULT'];
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
	<title><?=COURSE_OFFERING_STUDENT_STATUS_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=COURSE_OFFERING_STUDENT_STATUS_PAGE_TITLE?> </h4>
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
												<input type="text" class="form-control required-entry" id="COURSE_OFFERING_STUDENT_STATUS" name="COURSE_OFFERING_STUDENT_STATUS" value="<?=$COURSE_OFFERING_STUDENT_STATUS?>" <? if($_GET['id'] != ''){ ?> readonly <? } ?> >
												<span class="bar"></span>
												<label for="COURSE_OFFERING_STUDENT_STATUS"><?=COURSE_OFFERING_STUDENT_STATUS?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="DESCRIPTION" name="DESCRIPTION" value="<?=$DESCRIPTION?>" <? if($_GET['id'] != ''){ ?> readonly <? } ?> >
												<span class="bar"></span>
												<label for="DESCRIPTION"><?=DESCRIPTION?></label>
											</div>
										</div>
                                    </div>
								
									<div class="row">
										<div class="col-md-4">
											<div class="row form-group">
												<div class="custom-control col-md-4"><?=POST_TUITION?></div>
												<div class="custom-control custom-radio col-md-2">
													<input type="radio" id="POST_TUITION_1" name="POST_TUITION" value="1" <? if($POST_TUITION == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="POST_TUITION_1">Yes</label>
												</div>
												<div class="custom-control custom-radio col-md-2">
													<input type="radio" id="POST_TUITION_2" name="POST_TUITION" value="0" <? if($POST_TUITION == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="POST_TUITION_2">No</label>
												</div>
											</div>
										</div>
										
										<? if($_GET['id'] != ''){ ?>
										<div class="col-md-6">
											<div class="row form-group">
												<div class="custom-control col-md-2"><?=ACTIVE?></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="ACTIVE_1" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="ACTIVE_1">Yes</label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="ACTIVE_2" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="ACTIVE_2">No</label>
												</div>
											</div>
										</div>
										<? } ?>
										
									</div>
									
									<div class="row">
										<div class="col-md-8">
											<div class="row form-group">
												<div class="custom-control col-md-2"><?=SHOW_ON_TRANSCRIPT?></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="SHOW_ON_TRANSCRIPT_1" name="SHOW_ON_TRANSCRIPT" value="1" <? if($SHOW_ON_TRANSCRIPT == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="SHOW_ON_TRANSCRIPT_1">Yes</label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="SHOW_ON_TRANSCRIPT_2" name="SHOW_ON_TRANSCRIPT" value="0" <? if($SHOW_ON_TRANSCRIPT == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="SHOW_ON_TRANSCRIPT_2">No</label>
												</div>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-8">
											<div class="row form-group">
												<div class="custom-control col-md-2"><?=SHOW_ON_REPORT_CARD?></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="SHOW_ON_REPORT_CARD_1" name="SHOW_ON_REPORT_CARD" value="1" <? if($SHOW_ON_REPORT_CARD == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="SHOW_ON_REPORT_CARD_1">Yes</label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="SHOW_ON_REPORT_CARD_2" name="SHOW_ON_REPORT_CARD" value="0" <? if($SHOW_ON_REPORT_CARD == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="SHOW_ON_REPORT_CARD_2">No</label>
												</div>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-8">
											<div class="row form-group">
												<div class="custom-control col-md-2"><?=CALCULATE_SAP?></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="CALCULATE_SAP_1" name="CALCULATE_SAP" value="1" <? if($CALCULATE_SAP == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="CALCULATE_SAP_1">Yes</label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="CALCULATE_SAP_2" name="CALCULATE_SAP" value="0" <? if($CALCULATE_SAP == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="CALCULATE_SAP_2">No</label>
												</div>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-8">
											<div class="row form-group">
												<div class="custom-control col-md-2"><?=MAKE_AS_DEFAULT?></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="MAKE_AS_DEFAULT_1" name="MAKE_AS_DEFAULT" value="1" <? if($MAKE_AS_DEFAULT == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="MAKE_AS_DEFAULT_1">Yes</label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="MAKE_AS_DEFAULT_2" name="MAKE_AS_DEFAULT" value="0" <? if($MAKE_AS_DEFAULT == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="MAKE_AS_DEFAULT_2">No</label>
												</div>
											</div>
										</div>
									</div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_course_offering_student_status'" ><?=CANCEL?></button>
												
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