<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$COURSE_OFFERING_STUDENT_STATUS_MASTER = $_POST;
	
	if($_POST['MAKE_AS_DEFAULT'] == 1) {
		$db->Execute("UPDATE M_COURSE_OFFERING_STUDENT_STATUS_MASTER SET MAKE_AS_DEFAULT = 0 WHERE 1=1"); 
	}
	
	if($_GET['id'] == ''){
		$COURSE_OFFERING_STUDENT_STATUS_MASTER['CREATED_BY']  = $_SESSION['ADMIN_PK_USER'];
		$COURSE_OFFERING_STUDENT_STATUS_MASTER['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_COURSE_OFFERING_STUDENT_STATUS_MASTER', $COURSE_OFFERING_STUDENT_STATUS_MASTER, 'insert');
	} else {
		$COURSE_OFFERING_STUDENT_STATUS_MASTER['EDITED_BY'] = $_SESSION['ADMIN_PK_USER'];
		$COURSE_OFFERING_STUDENT_STATUS_MASTER['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_COURSE_OFFERING_STUDENT_STATUS_MASTER', $COURSE_OFFERING_STUDENT_STATUS_MASTER, 'update'," PK_COURSE_OFFERING_STUDENT_STATUS_MASTER = '$_GET[id]'");
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
	$res = $db->Execute("SELECT * FROM M_COURSE_OFFERING_STUDENT_STATUS_MASTER WHERE PK_COURSE_OFFERING_STUDENT_STATUS_MASTER = '$_GET[id]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_course_offering_student_status");
		exit;
	}
	
	$COURSE_OFFERING_STUDENT_STATUS 	= $res->fields['COURSE_OFFERING_STUDENT_STATUS'];
	$POST_TUITION 			= $res->fields['POST_TUITION'];
	$SHOW_ON_TRANSCRIPT 	= $res->fields['SHOW_ON_TRANSCRIPT'];
	$SHOW_ON_REPORT_CARD 	= $res->fields['SHOW_ON_REPORT_CARD'];
	$CALCULATE_SAP 			= $res->fields['CALCULATE_SAP'];
	$MAKE_AS_DEFAULT 		= $res->fields['MAKE_AS_DEFAULT'];
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
	<title>Course Offering Student Status | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo "Add"; else echo "Edit"; ?> Course Offering Student Status </h4>
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
												<label for="COURSE_OFFERING_STUDENT_STATUS">Course Offering Student Status</label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="DESCRIPTION" name="DESCRIPTION" value="<?=$DESCRIPTION?>" <? if($_GET['id'] != ''){ ?> readonly <? } ?> >
												<span class="bar"></span>
												<label for="DESCRIPTION">Description</label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
										<div class="col-md-8">
											<div class="row form-group">
												<div class="custom-control col-md-2">Post Tuition</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="POST_TUITION_1" name="POST_TUITION" value="1" <? if($POST_TUITION == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="POST_TUITION_1">Yes</label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="POST_TUITION_2" name="POST_TUITION" value="0" <? if($POST_TUITION == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="POST_TUITION_2">No</label>
												</div>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-8">
											<div class="row form-group">
												<div class="custom-control col-md-2">Show On Transcript</div>
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
												<div class="custom-control col-md-2">Show On Report Card</div>
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
												<div class="custom-control col-md-2">Calculate SAP</div>
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
												<div class="custom-control col-md-2">Make as Default</div>
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
												<button type="submit" class="btn waves-effect waves-light btn-info">Submit</button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_course_offering_student_status'" >Cancel</button>
												
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