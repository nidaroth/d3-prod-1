<? 
error_reporting(E_ALL);
ini_set('display_errors',1);
require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/announcement.php");
require_once("check_access.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

if(check_access('SETUP_COMMUNICATION') == 0 ){
	header("location:../index");
	exit;
}

if($_GET['act'] == 'delImg')	{
	$res = $db->Execute("SELECT IMAGE FROM Z_ANNOUNCEMENT WHERE PK_ANNOUNCEMENT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	unlink($res->fields['IMAGE']);
	$db->Execute("UPDATE Z_ANNOUNCEMENT SET IMAGE = '' WHERE PK_ANNOUNCEMENT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
	header("location:announcement?id=".$_GET['id']);
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$START_DATE = $_POST['START_DATE'];
	$START_TIME = $_POST['START_TIME'];
	$END_DATE 	= $_POST['END_DATE'];
	$END_TIME 	= $_POST['END_TIME'];
	
	$PK_CAMPUS_ARR 			 		= $_POST['PK_CAMPUS'];
	$PK_EMPLOYEE_MASTER_ARR  		= $_POST['PK_EMPLOYEE_MASTER'];
	$PK_ANNOUNCEMENT_FOR_MASTER_ARR = $_POST['PK_ANNOUNCEMENT_FOR_MASTER'];
	
	unset($_POST['START_DATE']);
	unset($_POST['START_TIME']);
	unset($_POST['END_DATE']);
	unset($_POST['END_TIME']);
	unset($_POST['PK_CAMPUS']);
	unset($_POST['PK_EMPLOYEE_MASTER']);
	unset($_POST['PK_ANNOUNCEMENT_FOR_MASTER']);
	
	$ANNOUNCEMENT = $_POST;
	//$ANNOUNCEMENT['PK_TIMEZONE'] = $_SESSION['PK_TIMEZONE'];
	$res_z_acc = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
	$PK_TIMEZONE = ($res_z_acc->fields['PK_TIMEZONE'])?$res_z_acc->fields['PK_TIMEZONE']:$_SESSION['PK_TIMEZONE'];

	$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE PK_TIMEZONE = '$PK_TIMEZONE'"); 
	
	if($START_DATE != '') {
		$START_DATE = date("Y-m-d",strtotime($START_DATE));
		
		if($START_TIME != '')
			$START_TIME = " ".date("H:i:s",strtotime($START_TIME));
			
		$ANNOUNCEMENT['START_DATE_TIME'] 	 = $START_DATE.$START_TIME;
		//$ANNOUNCEMENT['START_DATE_TIME_CET'] = convert_to_user_date($ANNOUNCEMENT['START_DATE_TIME'], "Y-m-d H:i:s", $res_tz->fields['TIMEZONE'],'CET');
		$ANNOUNCEMENT['START_DATE_TIME_CET'] = $START_DATE.$START_TIME;
		
		//echo $ANNOUNCEMENT['START_DATE_TIME'].'<br />'.$ANNOUNCEMENT['START_DATE_TIME_CET'].'<br />'.convert_to_user_date($ANNOUNCEMENT['START_DATE_TIME_CET'], "Y-m-d H:i:s", $res_tz->fields['TIMEZONE'], 'CET');exit;
	} else {
		$ANNOUNCEMENT['START_DATE_TIME'] 	 = '';
		$ANNOUNCEMENT['START_DATE_TIME_CET'] = '';
	}
	
	if($END_DATE != '') {
		$END_DATE = date("Y-m-d",strtotime($END_DATE));
		
		if($END_TIME != '')
			$END_TIME = " ".date("H:i:s",strtotime($END_TIME));
			
		$ANNOUNCEMENT['END_DATE_TIME'] 		 = $END_DATE.$END_TIME;
		//$ANNOUNCEMENT['END_DATE_TIME_CET'] 	 = convert_to_user_date($ANNOUNCEMENT['END_DATE_TIME'], "Y-m-d H:i:s", 'CET', $res_tz->fields['TIMEZONE']);
		$ANNOUNCEMENT['END_DATE_TIME_CET'] 		 = $END_DATE.$END_TIME;
	} else {
		$ANNOUNCEMENT['END_DATE_TIME'] 		= '';
		$ANNOUNCEMENT['END_DATE_TIME_CET']	= '';
	}

	//print_r($ANNOUNCEMENT); exit;
	
	if(!empty($_FILES))
	{ 

		if($_FILES['ATTACHMENT']['name'] != '')
		{
			// $file_dir_1 = '../backend_assets/help_image/';
			$file_dir_1 = '../backend_assets/tmp_upload/';
			$extn 			= explode(".",$_FILES['ATTACHMENT']['name']);
			$iindex			= count($extn) - 1;
			$rand_string 	= time()."_".rand(10000,99999);
			$file11			= $rand_string.".".$extn[$iindex];	
			$extension   	= strtolower($extn[$iindex]);
			
			if($extension != "html" || $extension != "php" || $extension != "js" ){ //Ticket #1916
				$newfile1 = $file_dir_1.$file11;
				move_uploaded_file($_FILES['ATTACHMENT']['tmp_name'], $newfile1);
				// Upload file to S3 bucket
				$key_file_name = 'backend_assets/help_image/'.$file11;
				$s3ClientWrapper = new s3ClientWrapper();
				$url = $s3ClientWrapper->uploadFile($key_file_name, $newfile1);

				// $ANNOUNCEMENT['IMAGE'] 	= $newfile1;
				$ANNOUNCEMENT['IMAGE'] 	= $url;

				// delete tmp file
				unlink($newfile1);
			}
		}
	}
	
	if($_GET['id'] == ''){
		$ANNOUNCEMENT['ANNOUNCEMENT_FROM']  = 2;
		$ANNOUNCEMENT['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
		$ANNOUNCEMENT['CREATED_BY']  		= $_SESSION['PK_USER'];
		$ANNOUNCEMENT['CREATED_ON']  		= date("Y-m-d H:i");
		db_perform('Z_ANNOUNCEMENT', $ANNOUNCEMENT, 'insert');
		$PK_ANNOUNCEMENT = $db->insert_ID();
		
	} else {
		$cond = "";

		$PK_ANNOUNCEMENT = $_GET['id'];
		$ANNOUNCEMENT['EDITED_BY'] = $_SESSION['PK_USER'];
		$ANNOUNCEMENT['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('Z_ANNOUNCEMENT', $ANNOUNCEMENT, 'update'," PK_ANNOUNCEMENT = '$PK_ANNOUNCEMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ");
	}
	
	$_POST['PK_CAMPUS'] = $_SESSION['PK_CAMPUS'];
	$PK_CAMPUS_ARR[]	= $_POST['PK_CAMPUS'];

	foreach($PK_CAMPUS_ARR as $PK_CAMPUS){
		$res = $db->Execute("SELECT PK_ANNOUNCEMENT_CAMPUS FROM Z_ANNOUNCEMENT_CAMPUS WHERE PK_ANNOUNCEMENT = '$PK_ANNOUNCEMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$PK_CAMPUS' "); 
		if($res->RecordCount() == 0) {
			$ANNOUNCEMENT_CAMPUS['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
			$ANNOUNCEMENT_CAMPUS['PK_ANNOUNCEMENT'] 	= $PK_ANNOUNCEMENT;
			$ANNOUNCEMENT_CAMPUS['PK_CAMPUS'] 			= $PK_CAMPUS;
			$ANNOUNCEMENT_CAMPUS['CREATED_BY']  		= $_SESSION['PK_USER'];
			$ANNOUNCEMENT_CAMPUS['CREATED_ON'] 			= date("Y-m-d H:i");
			db_perform('Z_ANNOUNCEMENT_CAMPUS', $ANNOUNCEMENT_CAMPUS, 'insert');
			$PK_ANNOUNCEMENT_CAMPUS_ARR[] = $db->insert_ID();
		} else
			$PK_ANNOUNCEMENT_CAMPUS_ARR[] = $res->fields['PK_ANNOUNCEMENT_CAMPUS'];
	}
	
	$cond = "";
	if(!empty($PK_ANNOUNCEMENT_CAMPUS_ARR))
		$cond = " AND PK_ANNOUNCEMENT_CAMPUS NOT IN (".implode(",",$PK_ANNOUNCEMENT_CAMPUS_ARR).") ";
	
	$db->Execute("DELETE FROM Z_ANNOUNCEMENT_CAMPUS WHERE PK_ANNOUNCEMENT = '$PK_ANNOUNCEMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	$PK_EMPLOYEE_MASTER_ARR[] = $_SESSION['PK_EMPLOYEE_MASTER']; //DIAM-941
	foreach($PK_EMPLOYEE_MASTER_ARR as $PK_EMPLOYEE_MASTER){
		$res = $db->Execute("SELECT PK_ANNOUNCEMENT_EMPLOYEE FROM Z_ANNOUNCEMENT_EMPLOYEE WHERE PK_ANNOUNCEMENT = '$PK_ANNOUNCEMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' "); 
		if($res->RecordCount() == 0) {
			$ANNOUNCEMENT_EMPLOYEE['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
			$ANNOUNCEMENT_EMPLOYEE['PK_ANNOUNCEMENT'] 		= $PK_ANNOUNCEMENT;
			$ANNOUNCEMENT_EMPLOYEE['PK_EMPLOYEE_MASTER'] 	= $PK_EMPLOYEE_MASTER;
			$ANNOUNCEMENT_EMPLOYEE['CREATED_BY']  			= $_SESSION['PK_USER'];
			$ANNOUNCEMENT_EMPLOYEE['CREATED_ON'] 			= date("Y-m-d H:i");
			db_perform('Z_ANNOUNCEMENT_EMPLOYEE', $ANNOUNCEMENT_EMPLOYEE, 'insert');
			$PK_ANNOUNCEMENT_EMPLOYEE_ARR[] = $db->insert_ID();
		} else
			$PK_ANNOUNCEMENT_EMPLOYEE_ARR[] = $res->fields['PK_ANNOUNCEMENT_EMPLOYEE'];
	}
	
	$cond = "";
	if(!empty($PK_ANNOUNCEMENT_EMPLOYEE_ARR))
		$cond = " AND PK_ANNOUNCEMENT_EMPLOYEE NOT IN (".implode(",",$PK_ANNOUNCEMENT_EMPLOYEE_ARR).") ";

	$db->Execute("DELETE FROM Z_ANNOUNCEMENT_EMPLOYEE WHERE PK_ANNOUNCEMENT = '$PK_ANNOUNCEMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	
	foreach($PK_ANNOUNCEMENT_FOR_MASTER_ARR as $PK_ANNOUNCEMENT_FOR_MASTER){
		$res = $db->Execute("SELECT PK_ANNOUNCEMENT_FOR FROM Z_ANNOUNCEMENT_FOR WHERE PK_ANNOUNCEMENT = '$PK_ANNOUNCEMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_ANNOUNCEMENT_FOR_MASTER = '$PK_ANNOUNCEMENT_FOR_MASTER' "); 
		if($res->RecordCount() == 0) {
			$ANNOUNCEMENT_FOR['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
			$ANNOUNCEMENT_FOR['PK_ANNOUNCEMENT'] 			= $PK_ANNOUNCEMENT;
			$ANNOUNCEMENT_FOR['PK_ANNOUNCEMENT_FOR_MASTER'] = $PK_ANNOUNCEMENT_FOR_MASTER;
			$ANNOUNCEMENT_FOR['CREATED_BY']  				= $_SESSION['PK_USER'];
			$ANNOUNCEMENT_FOR['CREATED_ON'] 				= date("Y-m-d H:i");
			db_perform('Z_ANNOUNCEMENT_FOR', $ANNOUNCEMENT_FOR, 'insert');
			$PK_ANNOUNCEMENT_FOR_ARR[] = $db->insert_ID();
		} else
			$PK_ANNOUNCEMENT_FOR_ARR[] = $res->fields['PK_ANNOUNCEMENT_FOR'];
	}
	
	$cond = "";
	if(!empty($PK_ANNOUNCEMENT_FOR_ARR))
		$cond = " AND PK_ANNOUNCEMENT_FOR NOT IN (".implode(",",$PK_ANNOUNCEMENT_FOR_ARR).") ";

	$db->Execute("DELETE FROM Z_ANNOUNCEMENT_FOR WHERE PK_ANNOUNCEMENT = '$PK_ANNOUNCEMENT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	
	
	if($_GET['p'] == 'i')
		$URL = "index";
	else
		$URL = "manage_announcement";
	header("location:".$URL);
}
if($_GET['id'] == ''){
	$HEADER 	  	  = '';
	$SHORT_DESC_ENG   = '';
	$SHORT_DESC_SPA   = '';
	$DESC_ENG 		  = '';
	$TOOL_CONTENT_SPA = '';
	$DESC_SPA 	  	  = '';
	$IMAGE 		 	  = '';
	$ACTIVE	 	 	  = '';
	$ANNOUNCEMENT_FOR = '';
	
	$START_DATE = '';
	$START_TIME = '';
	$END_DATE 	= '';
	$END_TIME 	= '';
	
} else {
	$cond = "";	
	$res = $db->Execute("SELECT * FROM Z_ANNOUNCEMENT WHERE PK_ANNOUNCEMENT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	if($res->RecordCount() == 0){
		header("location:manage_announcement");
		exit;
	}
	
	$HEADER	  		  = $res->fields['HEADER'];
	$SHORT_DESC_ENG   = $res->fields['SHORT_DESC_ENG'];
	$SHORT_DESC_SPA   = $res->fields['SHORT_DESC_SPA'];
	$DESC_ENG 		  = $res->fields['DESC_ENG'];
	$TOOL_CONTENT_SPA = $res->fields['TOOL_CONTENT_SPA'];
	$DESC_SPA 	  	  = $res->fields['DESC_SPA'];
	$IMAGE 		 	  = $res->fields['IMAGE'];
	$ACTIVE  	 	  = $res->fields['ACTIVE'];
	$ANNOUNCEMENT_FOR = $res->fields['ANNOUNCEMENT_FOR'];
	
	$START_DATE_TIME  = $res->fields['START_DATE_TIME'];
	$END_DATE_TIME    = $res->fields['END_DATE_TIME'];
	
	if($START_DATE_TIME != '0000-00-00 00:00:00'){
		$START_DATE = date("m/d/Y",strtotime($START_DATE_TIME));
		$START_TIME = date("h:i A",strtotime($START_DATE_TIME));
	} else {
		$START_DATE = '';
		$START_TIME = '';
	}
	
	if($END_DATE_TIME != '0000-00-00 00:00:00'){
		$END_DATE = date("m/d/Y",strtotime($END_DATE_TIME));
		$END_TIME = date("h:i A",strtotime($END_DATE_TIME));
	} else {
		$END_DATE = '';
		$END_TIME = '';
	}
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
	<title><?=ANNOUNCEMENT_PAGE_TITLE?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS, #advice-required-entry-PK_EMPLOYEE_MASTER{position: absolute;top: 38px;}
		.disabled > a > label{color:red !important}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo "Add"; else echo "Edit"; ?> <?=ANNOUNCEMENT_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
									<div class="row">
                                        <div class="col-md-12">
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="text" class="form-control required-entry" id="HEADER" name="HEADER" value="<?=$HEADER?>" >
														<span class="bar"></span>
														<label for="HEADER"><?=HEADER?></label>
													</div>
												</div>
												
												<? $sond = "";
												if($_SESSION['PK_ROLES'] != 2) 
													$cond = " AND PK_CAMPUS IN ($_SESSION[PK_CAMPUS]) "; ?>
												<div class="col-md-3">
													<div class="col-12 col-sm-12 focused">
														<span class="bar"></span> 
														<label for="CAMPUS"><?=CAMPUS?></label>
													</div>
													<div class="col-12 col-sm-12">
														<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" onchange="get_employee()" >
															<? $str = '';
															$res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond order by CAMPUS_CODE ASC");
															while (!$res_type->EOF) { 
																$selected = '';
																$PK_CAMPUS = $res_type->fields['PK_CAMPUS'];
																$res = $db->Execute("select PK_ANNOUNCEMENT_CAMPUS FROM Z_ANNOUNCEMENT_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_ANNOUNCEMENT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
																if($res->RecordCount() > 0 || ($res_type->RecordCount() == 1 && $_GET['id'] == '')) { //Ticket #849 
																	$selected = 'selected'; 
																	
																	if($str != '')
																		$str .= ',';
																	$str .= $PK_CAMPUS;
																} ?>
																<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" <?=$selected ?> ><?=$res_type->fields['CAMPUS_CODE'] ?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="col-12 col-sm-12 focused">
														<span class="bar"></span> 
														<label for="PK_ANNOUNCEMENT_FOR_MASTER"><?=ANNOUNCEMENT_FOR?></label>
													</div>
													<div class="form-group m-b-40">
														<select id="PK_ANNOUNCEMENT_FOR_MASTER" name="PK_ANNOUNCEMENT_FOR_MASTER[]" multiple class="form-control required-entry" onchange="get_employee()" >
															<? $STAFF_ANNOUNCEMENT = 0;
															$res_type = $db->Execute("select PK_ANNOUNCEMENT_FOR_MASTER,ANNOUNCEMENT_FOR from M_ANNOUNCEMENT_FOR_MASTER WHERE ACTIVE = 1 ");
															while (!$res_type->EOF) { 
																$selected = '';
																$PK_ANNOUNCEMENT_FOR_MASTER = $res_type->fields['PK_ANNOUNCEMENT_FOR_MASTER'];
																
																$res = $db->Execute("select PK_ANNOUNCEMENT_FOR FROM Z_ANNOUNCEMENT_FOR WHERE PK_ANNOUNCEMENT_FOR_MASTER = '$PK_ANNOUNCEMENT_FOR_MASTER' AND PK_ANNOUNCEMENT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
																if($res->RecordCount() > 0) {
																	if($PK_ANNOUNCEMENT_FOR_MASTER == 1)
																		$STAFF_ANNOUNCEMENT = 1;
																		
																	$selected = 'selected'; 
																} ?>
																<option value="<?=$res_type->fields['PK_ANNOUNCEMENT_FOR_MASTER'] ?>" <?=$selected ?> ><?=$res_type->fields['ANNOUNCEMENT_FOR'] ?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
												</div>
												
											</div>
											
											<div class="row">
												<div class="col-md-2">
													<div class="form-group m-b-40">
														<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="<?=$START_DATE?>" >
														<span class="bar"></span>
														<label for="START_DATE"><?=START_DATE?></label>
													</div>
												</div>
												<div class="col-md-1">
													<div class="form-group m-b-40">
														<input type="text" class="form-control timepicker required-entry" id="START_TIME" name="START_TIME" value="<?=$START_TIME?>" >
														<span class="bar"></span>
														<label for="START_TIME"><?=START_TIME?></label>
													</div>
												</div>
												
												<div class="col-md-2">
													<div class="form-group m-b-40">
														<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="<?=$END_DATE?>" >
														<span class="bar"></span>
														<label for="END_DATE"><?=END_DATE?></label>
													</div>
												</div>
												<div class="col-md-1">
													<div class="form-group m-b-40">
														<input type="text" class="form-control timepicker required-entry" id="END_TIME" name="END_TIME" value="<?=$END_TIME?>" >
														<span class="bar"></span>
														<label for="END_TIME"><?=END_TIME?></label>
													</div>
												</div>
												
												<div class="col-md-6" id="employee_div">
													<? if($_GET['id'] != '' && $STAFF_ANNOUNCEMENT  == 1){
														$_REQUEST['id']  = $_GET['id']; 
														$_REQUEST['str'] = $str; 
														include('ajax_get_employee_from_campus.php'); 
													} ?>
												</div>
												
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<textarea class="form-control required-entry" rows="2" id="SHORT_DESC_ENG" name="SHORT_DESC_ENG"><?=$SHORT_DESC_ENG?></textarea>
														<span class="bar"></span>
														<label for="SHORT_DESC_ENG"><?=SHORT_DESC_ENG?></label>
													</div>
												</div>
											
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<textarea class="form-control " rows="2" id="SHORT_DESC_SPA" name="SHORT_DESC_SPA"><?=$SHORT_DESC_SPA?></textarea>
														<span class="bar"></span>
														<label for="SHORT_DESC_SPA"><?=SHORT_DESC_SPA?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<?=DESC_ENG?>
													<textarea class="form-control required-entry rich" rows="2" id="DESC_ENG" name="DESC_ENG"><?=$DESC_ENG?></textarea>
												</div>
												
												<div class="col-md-6">
													<?=DESC_SPA?>
													<textarea class="form-control  rich" rows="2" id="DESC_SPA" name="DESC_SPA"><?=$DESC_SPA?></textarea>
												</div>
											</div>
											<br />
											<div class="row" >
												<div class="col-md-6">
													<? if($IMAGE == '') { ?>
													<input type="file" name="ATTACHMENT" />
													<? } else { ?>
													<table>
														<tr>
															<td>
																<!-- Ticket #1916 -->
																<? $extn 			= explode(".",$IMAGE);
																$iindex			= count($extn) - 1;
																$extension   	= strtolower($extn[$iindex]); 
																
																if($extension == 'jpg' || $extension == 'jpeg' || $extension == 'bmp'){ ?>
																	<img src="<?=$IMAGE?>" style="height:80px;" />
																<? } else { ?>
																	<a href="<?=$IMAGE?>" target="_blank" >view Attachment</a>
																<? } ?>
																<!-- Ticket #1916 -->
															</td>
															<td>
																<a onclick="delete_row('','img')" href="javascript:void(0)" >
																	<i class="icon-trash"></i>
																</a>
															</td>
														</tr>
													</table>
													<? } ?>
												</div>
												
												<? if($_GET['id'] != ''){ ?>
													<div class="col-md-3">
														<div class="row form-group">
															<div class="custom-control col-md-4"><?=ACTIVE?></div>
															<div class="custom-control custom-radio col-md-3">
																<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
																<label class="custom-control-label" for="customRadio11">Yes</label>
															</div>
															<div class="custom-control custom-radio col-md-3">
																<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
																<label class="custom-control-label" for="customRadio22">No</label>
															</div>
														</div>
													</div>
												<? } ?>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<br />
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												<? if($_GET['p'] == 'i')
													$URL = "index";
												else
													$URL = "manage_announcement"; ?>
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
		
		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><?=CONFIRMATION?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                    </div>
                    <div class="modal-body">
                            <p><?=DELETE_MESSAGE.' '.IMAGE?>?</p>
							<input type="hidden" id="DELETE_ID" value="0" />
							<input type="hidden" id="DELETE_TYPE" value="0" />
                    </div>
                    <div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
							<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" ><?=NO?></button>
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
		
		$('.timepicker').inputmask(
			"hh:mm t", {
				placeholder: "HH:MM AM/PM", 
				insertMode: false, 
				showMaskOnHover: false,
				hourFormat: 12
			}
		);
	});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<!-- <script src="https://cdn.tiny.cloud/1/d6quzxl18kigwmmr6z03zgk3w47922rw1epwafi19cfnj00i/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script> -->
	<? require_once("../global/tiny-cloud.php"); ?>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		jQuery(document).ready(function($) {
			<? if($_GET['id'] == ''){ ?>
			get_employee_1(<?=$_SESSION['PK_CAMPUS']?>)
			<? } ?>
		});
		
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}

		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'img')
						window.location.href = 'announcement?act=delImg&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
											
				} else
					$("#deleteModal").modal("hide");
			});
		}
		
		/* Ticket # 1199  */
		jQuery(document).ready(function($) {
			tinymce.init({ 
				selector:'.rich',
				browser_spellcheck: true,
				menubar:false,
				statusbar: false,
				height: '300',
				plugins: [
					'advlist lists hr pagebreak',
					'wordcount code',
					'nonbreaking save table contextmenu directionality',
					'template paste textcolor colorpicker textpattern ',
					'link'
				  ],
				toolbar1: 'bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link',
				height: 400,
			});
		});
		/* Ticket # 1199  */
		
		function get_employee(){
			jQuery(document).ready(function($) {
				var str = $('#PK_CAMPUS').val();
				//alert(str)
				if(str == '')
					document.getElementById('employee_div').innerHTML = '';
				else {
					var flag = 0;
					var PK_ANNOUNCEMENT_FOR_MASTER = $('#PK_ANNOUNCEMENT_FOR_MASTER').val()
					for(var i = 0 ; i < PK_ANNOUNCEMENT_FOR_MASTER.length ; i++){
						if(PK_ANNOUNCEMENT_FOR_MASTER[i] == 1)
							flag = 1
					}	
					if(flag == 1) 
						get_employee_1(str)
					else
						document.getElementById('employee_div').innerHTML = '';
				}
			});
		}
		function get_employee_1(str){
			jQuery(document).ready(function($) {
				var data = 'str='+str+'&id=<?=$_GET['id']?>';
				var value = $.ajax({
					url: "ajax_get_employee_from_campus",	
					type: "POST",
					data: data,		
					async: false,
					cache :false,
					success: function (data) {
						document.getElementById('employee_div').innerHTML = data;
						
						$('#PK_EMPLOYEE_MASTER').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=STAFF?>',
							nonSelectedText: '',
							numberDisplayed: 2,
							nSelectedText: '<?=STAFF?> selected', //Ticket # 1593
							enableCaseInsensitiveFiltering: true, //Ticket # 1593
						});
					}		
				}).responseText;
			});
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
		$('#PK_ANNOUNCEMENT_FOR_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=ANNOUNCEMENT_FOR?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=ANNOUNCEMENT_FOR?> selected'
		});
		
		$('#PK_EMPLOYEE_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STAFF?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=STAFF?> selected', //Ticket # 1593
			enableCaseInsensitiveFiltering: true, //Ticket # 1593
		});
	});
	</script>
</body>

</html>
