<? require_once("../global/config.php"); 
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

if($_GET['act'] == 'delImg')	{
	$res = $db->Execute("SELECT IMAGE FROM Z_ANNOUNCEMENT WHERE PK_ANNOUNCEMENT = '$_GET[id]' ");
	unlink($res->fields['IMAGE']);
	$db->Execute("UPDATE Z_ANNOUNCEMENT SET IMAGE = '' WHERE PK_ANNOUNCEMENT = '$_GET[id]' ");
		
	header("location:announcement_popup?id=".$_GET['id']);
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_FILES);exit;
	
	$START_DATE = $_POST['START_DATE'];
	$START_TIME = $_POST['START_TIME'];
	$END_DATE 	= $_POST['END_DATE'];
	$END_TIME 	= $_POST['END_TIME'];
	
	unset($_POST['START_DATE']);
	unset($_POST['START_TIME']);
	unset($_POST['END_DATE']);
	unset($_POST['END_TIME']);
	
	$ANNOUNCEMENT = $_POST;
	
	if(!empty($_FILES)){ 
		// $file_dir_1 = '../backend_assets/help_image/';
		$file_dir_1 = '../backend_assets/tmp_upload/';
		$extn 			= explode(".",$_FILES['ATTACHMENT']['name']);
		$iindex			= count($extn) - 1;
		$rand_string 	= time()."_".rand(10000,99999);
		$file11			= $rand_string.".".$extn[$iindex];	
		$extension   	= strtolower($extn[$iindex]);
		
		if($extension == "png" || $extension == "jpg" || $extension == "jepg" ){ 
			$newfile1 = $file_dir_1.$file11;
			move_uploaded_file($_FILES['ATTACHMENT']['tmp_name'], $newfile1);

			// Upload file to S3 bucket
			$key_file_name = 'backend_assets/help_image/'.$file11;
			$s3ClientWrapper = new s3ClientWrapper();
			$url = $s3ClientWrapper->uploadFile($key_file_name, $newfile1);

			// $ANNOUNCEMENT['IMAGE'] 	= $newfile1;
			$ANNOUNCEMENT['IMAGE'] 	= $url;

			// delete tmp file
			unlink($image_path);
		}
	}
	
	$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE PK_TIMEZONE = '$_SESSION[ADMIN_PK_TIMEZONE]'"); 
	
	if($START_DATE != '') {
		$START_DATE = date("Y-m-d",strtotime($START_DATE));
		
		if($START_TIME != '')
			$START_TIME = " ".date("H:i:s",strtotime($START_TIME));
			
		$ANNOUNCEMENT['START_DATE_TIME'] 	 = $START_DATE.$START_TIME;
		$ANNOUNCEMENT['START_DATE_TIME_CET'] = convert_to_user_date($ANNOUNCEMENT['START_DATE_TIME'], "Y-m-d H:i:s", 'CET', $res_tz->fields['TIMEZONE']);
		
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
		$ANNOUNCEMENT['END_DATE_TIME_CET'] 	 = convert_to_user_date($ANNOUNCEMENT['END_DATE_TIME'], "Y-m-d H:i:s", 'CET', $res_tz->fields['TIMEZONE']);
	} else {
		$ANNOUNCEMENT['END_DATE_TIME'] 		= '';
		$ANNOUNCEMENT['END_DATE_TIME_CET']	= '';
	}
	
	if($_GET['id'] == ''){
		$ANNOUNCEMENT['ANNOUNCEMENT_FROM']  = 1;
		$ANNOUNCEMENT['CREATED_BY']  		= $_SESSION['ADMIN_PK_USER'];
		$ANNOUNCEMENT['CREATED_ON']  		= date("Y-m-d H:i");

		

		$ANNOUNCEMENT['ANNOUNCEMENT_FROM'] = 3; // 1 admin, 3 admin popup

		db_perform('Z_ANNOUNCEMENT', $ANNOUNCEMENT, 'insert');
		$PK_ANNOUNCEMENT = $db->insert_ID();
	} else {
		$ANNOUNCEMENT['EDITED_BY'] = $_SESSION['ADMIN_PK_USER'];
		$ANNOUNCEMENT['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('Z_ANNOUNCEMENT', $ANNOUNCEMENT, 'update'," PK_ANNOUNCEMENT = '$_GET[id]' AND ANNOUNCEMENT_FROM = 3 ");
		$PK_ANNOUNCEMENT = $_GET['id'];
	}
	
	header("location:manage_announcement_popup");
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
	
	$START_DATE = '';
	$START_TIME = '';
	$END_DATE 	= '';
	$END_TIME 	= '';
	
} else {
	$res = $db->Execute("SELECT * FROM Z_ANNOUNCEMENT WHERE PK_ANNOUNCEMENT = '$_GET[id]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_announcement_popup");
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
	<title>Announcement POP UP | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo "Add"; else echo "Edit"; ?> Announcement POP UP</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
									<div class="row">
                                        <div class="col-md-6">
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control " id="HEADER" name="HEADER" value="<?=$HEADER?>" >
														<span class="bar"></span>
														<label for="HEADER">Header</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<input type="text" class="form-control date" id="START_DATE" name="START_DATE" value="<?=$START_DATE?>" >
														<span class="bar"></span>
														<label for="START_DATE">Start Date</label>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<input type="text" class="form-control timepicker" id="START_TIME" name="START_TIME" value="<?=$START_TIME?>" >
														<span class="bar"></span>
														<label for="START_TIME">Start Time</label>
													</div>
												</div>
												
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<input type="text" class="form-control date" id="END_DATE" name="END_DATE" value="<?=$END_DATE?>" >
														<span class="bar"></span>
														<label for="END_DATE">End Date</label>
													</div>
												</div>
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<input type="text" class="form-control timepicker" id="END_TIME" name="END_TIME" value="<?=$END_TIME?>" >
														<span class="bar"></span>
														<label for="END_TIME">End Time</label>
													</div>
												</div>
												
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<textarea class="form-control " rows="2" id="SHORT_DESC_ENG" name="SHORT_DESC_ENG"><?=$SHORT_DESC_ENG?></textarea>
														<span class="bar"></span>
														<label for="SHORT_DESC_ENG">Short Description (Eng)</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<textarea class="form-control " rows="2" id="SHORT_DESC_SPA" name="SHORT_DESC_SPA"><?=$SHORT_DESC_SPA?></textarea>
														<span class="bar"></span>
														<label for="SHORT_DESC_SPA">Short Description (Spa)</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													Description (Eng)
												</div>
												<div class="col-md-12">
													<textarea class="form-control  rich" rows="2" id="DESC_ENG" name="DESC_ENG"><?=$DESC_ENG?></textarea>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													Description (Spa)
												</div>
												<div class="col-md-12">
													<textarea class="form-control  rich" rows="2" id="DESC_SPA" name="DESC_SPA"><?=$DESC_SPA?></textarea>
												</div>
											</div>
											<br />
											<div class="row" >
												<div class="col-lg-8">
													<? if($IMAGE == '') { ?>
													<input type="file" name="ATTACHMENT" />
													<? } else { ?>
													<table>
														<tr>
															<td><img src="<?=$IMAGE?>" style="height:80px;" /></td>
															<td>
																<a onclick="delete_row('','img')" href="javascript:void(0)" >
																	<i class="icon-trash"></i>
																</a>
															</td>
														</tr>
													</table>
													<? } ?>
												</div>
											</div>
											
											<? if($_GET['id'] != ''){ ?>
											<div class="row">
												<div class="col-md-12">
													<div class="row form-group">
														<div class="custom-control col-md-6">Active</div>
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
											
										</div>
                                    </div>
								
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<br />
												<button type="submit" class="btn waves-effect waves-light btn-info">Submit</button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_announcement_popup'" >Cancel</button>
												
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
                        <h4 class="modal-title">Confirmation.</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                    </div>
                    <div class="modal-body">
                            <p>Are you sure want to Delete this Image?</p>
							<input type="hidden" id="DELETE_ID" value="0" />
							<input type="hidden" id="DELETE_TYPE" value="0" />
                    </div>
                    <div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info">Yes</button>
							<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" >No</button>
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
						window.location.href = 'announcement_popup?act=delImg&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
											
				} else
					$("#deleteModal").modal("hide");
			});
		}
		
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
					'template paste textcolor colorpicker textpattern link'
				  ],
				toolbar1: 'bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link',	
				height: 400,
			});
		});
		
		
	</script>

</body>

</html>