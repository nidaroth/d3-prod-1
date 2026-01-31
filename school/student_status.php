<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student_status.php");
require_once("check_access.php");

if(check_access('SETUP_STUDENT') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	// echo "<pre>";print_r($_POST);exit;
	$STUDENT_STATUS = $_POST;
	
	if(isset($_POST['ADMISSIONS'])){
		$STUDENT_STATUS['ADMISSIONS'] = $_POST['ADMISSIONS'];
	}else{
		$STUDENT_STATUS['ADMISSIONS'] = 0;
	}
	
	
	$STUDENT_STATUS['POST_TUITION'] 	= $_POST['POST_TUITION'];
	//$STUDENT_STATUS['DOC_28_1'] 		= $_POST['DOC_28_1']; Ticket # 1810
	$STUDENT_STATUS['CLASS_ENROLLMENT'] = $_POST['CLASS_ENROLLMENT'];
	$STUDENT_STATUS['ALLOW_ATTENDANCE'] = $_POST['ALLOW_ATTENDANCE'];
	//$STUDENT_STATUS['_1098T'] 			= $_POST['_1098T']; //Ticket # 1048
	$STUDENT_STATUS['COMPLETED'] 		= $_POST['COMPLETED'];
	
	if($_GET['id'] == ''){
		$STUDENT_STATUS['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$STUDENT_STATUS['CREATED_BY']  = $_SESSION['PK_USER'];
		$STUDENT_STATUS['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_STUDENT_STATUS', $STUDENT_STATUS, 'insert');
	} else {
		$STUDENT_STATUS['EDITED_BY'] = $_SESSION['PK_USER'];
		$STUDENT_STATUS['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_STUDENT_STATUS', $STUDENT_STATUS, 'update'," PK_STUDENT_STATUS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
	header("location:manage_student_status");
}
if($_GET['id'] == ''){
	$STUDENT_STATUS 	= '';
	$DESCRIPTION 		= '';
	$ADMISSIONS 		= '';
	$PK_END_DATE 		= '';
	$FA_STATUS 			= '';
	$POST_TUITION 		= '';
	$DOC_28_1 			= '';
	$CLASS_ENROLLMENT 	= '';
	$ALLOW_ATTENDANCE 	= '';
	$_1098T 			= '';
	$COMPLETED 			= '';
	$ACTIVE	 			= '';
	
	$PK_STUDENT_STATUS_MASTER = '';
	
} else {
	$res = $db->Execute("SELECT * FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_student_status");
		exit;
	}
	
	$STUDENT_STATUS 	= $res->fields['STUDENT_STATUS'];
	$DESCRIPTION 		= $res->fields['DESCRIPTION'];
	$ADMISSIONS 		= $res->fields['ADMISSIONS'];
	$PK_END_DATE 		= $res->fields['PK_END_DATE'];
	$FA_STATUS 			= $res->fields['FA_STATUS'];
	$POST_TUITION 		= $res->fields['POST_TUITION'];
	$DOC_28_1 			= $res->fields['DOC_28_1'];
	$CLASS_ENROLLMENT 	= $res->fields['CLASS_ENROLLMENT'];
	$ALLOW_ATTENDANCE 	= $res->fields['ALLOW_ATTENDANCE'];
	$_1098T 			= $res->fields['_1098T'];
	$COMPLETED 			= $res->fields['COMPLETED'];
	$ACTIVE  			= $res->fields['ACTIVE'];
	
	$PK_STUDENT_STATUS_MASTER = $res->fields['PK_STUDENT_STATUS_MASTER'];
}

$disabled = "";
if($PK_STUDENT_STATUS_MASTER == 5 || $PK_STUDENT_STATUS_MASTER == 13)
	$disabled = "disabled";
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
	<title><?=STUDENT_STATUS_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=STUDENT_STATUS_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row">
                                        <div class="col-md-3">
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control required-entry" id="STUDENT_STATUS" name="STUDENT_STATUS" value="<?=$STUDENT_STATUS?>" <?=$disabled?> >
														<span class="bar"></span>
														<label for="STUDENT_STATUS">Student Status</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="DESCRIPTION" name="DESCRIPTION" value="<?=$DESCRIPTION?>"  >
														<span class="bar"></span>
														<label for="DESCRIPTION">Description</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<select id="PK_END_DATE" name="PK_END_DATE" class="form-control" >
															<option selected></option>
															<? $res_type = $db->Execute("select PK_END_DATE, CODE,DESCRIPTION from M_END_DATE WHERE ACTIVE = '1' ORDER BY CODE ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_END_DATE'] ?>" <? if($res_type->fields['PK_END_DATE'] == $PK_END_DATE) echo "selected"; ?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span>
														<label for="PK_END_DATE">End Date</label>
													</div>
												</div>
											</div>
											<!-- Ticket # 1810 
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="FA_STATUS" name="FA_STATUS" value="<?=$FA_STATUS?>"  >
														<span class="bar"></span>
														<label for="FA_STATUS">FA Status</label>
													</div>
												</div>
											</div>
											Ticket # 1810 -->
										
											<? if($_GET['id'] != ''){ ?>
											<div class="row">
												<div class="col-md-12">
													<div class="row form-group">
														<div class="custom-control col-md-3">Active</div>
														<div class="custom-control custom-radio col-md-3">
															<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input" <?=$disabled?> >
															<label class="custom-control-label" for="customRadio11">Yes</label>
														</div>
														<div class="custom-control custom-radio col-md-3">
															<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input" <?=$disabled?> >
															<label class="custom-control-label" for="customRadio22">No</label>
														</div>
													</div>
												</div>
											</div>
											<? } ?>
										</div>
										<div class="col-md-6">
											<div class="row" >
												<div class="col-md-12" >
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="ADMISSIONS" name="ADMISSIONS" value="1" <? if($ADMISSIONS == 1) echo "checked"; ?> <?=$disabled?> onclick="set_reguired()" >
															<label class="custom-control-label" for="ADMISSIONS">Admissions</label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row" >
												<div class="col-md-12" >
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="POST_TUITION" name="POST_TUITION" value="1" <? if($POST_TUITION == 1) echo "checked"; ?>  >
															<label class="custom-control-label" for="POST_TUITION">Post Tuition</label>
														</div>
													</div>
												</div>
											</div>
											
											<!-- Ticket # 1810
											<div class="row" >
												<div class="col-md-12" >
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="DOC_28_1" name="DOC_28_1" value="1" <? if($DOC_28_1 == 1) echo "checked"; ?>  >
															<label class="custom-control-label" for="DOC_28_1">Doc28.1</label>
														</div>
													</div>
												</div>
											</div>
											Ticket # 1810 -->
											
											<div class="row" >
												<div class="col-md-12" >
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="CLASS_ENROLLMENT" name="CLASS_ENROLLMENT" value="1" <? if($CLASS_ENROLLMENT == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="CLASS_ENROLLMENT">Class Enrollment</label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row" >
												<div class="col-md-12" >
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="ALLOW_ATTENDANCE" name="ALLOW_ATTENDANCE" value="1" <? if($ALLOW_ATTENDANCE == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="ALLOW_ATTENDANCE">Allow Attendance</label>
														</div>
													</div>
												</div>
											</div>
											
											<!-- Ticket # 1048 -->
											<!--<div class="row" >
												<div class="col-md-12" >
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="_1098T" name="_1098T" value="1" <? if($_1098T == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="_1098T">1098T</label>
														</div>
													</div>
												</div>
											</div>-->
											<!-- Ticket # 1048 -->
											
											<div class="row" >
												<div class="col-md-12" >
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="COMPLETED" name="COMPLETED" value="1" <? if($COMPLETED == 1) echo "checked"; ?>  >
															<label class="custom-control-label" for="COMPLETED">Completed</label>
														</div>
													</div>
												</div>
											</div>
											
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_student_status'" ><?=CANCEL?></button>
												
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
		
		jQuery(document).ready(function($) {
			set_reguired()
		});
		
		function set_reguired(){
			var str = "form-control"
			if(document.getElementById('ADMISSIONS').checked == true)
				str = "form-control"
			else
				str = "form-control required-entry"
				
			document.getElementById('PK_END_DATE').className = str
			
			if(document.getElementById('ADMISSIONS').checked == true){
				document.getElementById('COMPLETED').checked  = false
				document.getElementById('COMPLETED').disabled = true
			} else {
				document.getElementById('COMPLETED').disabled = false
			}
			
		}
	</script>

</body>

</html>