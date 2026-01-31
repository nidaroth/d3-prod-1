<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/collateral.php");
require_once("check_access.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

if($_GET['act'] == 'del_file'){

	$res = $db->Execute("SELECT FILE_LOCATION FROM S_COLLATERAL WHERE PK_COLLATERAL = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	if($res->fields['FILE_LOCATION'] != '')
		unlink($res->fields['FILE_LOCATION']);
		
	$db->Execute("UPDATE S_COLLATERAL SET FILE_LOCATION = '' WHERE PK_COLLATERAL = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	header("location:collateral?id=".$_GET['id']);
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$COLLATERAL = $_POST;
	// $file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/collateral/';
	$file_dir_1 = '../backend_assets/tmp_upload/';
	if($_FILES['FILE']['name'] != ''){
		$extn 			= explode(".",$_FILES['FILE']['name']);
		$iindex			= count($extn) - 1;
		$rand_string 	= time()."-".rand(10000,99999);
		$file11			= 'collateral_'.$_SESSION['PK_USER'].$rand_string.".".$extn[$iindex];	
		$extension   	= strtolower($extn[$iindex]);
		
		if($extension != "php" && $extension != "js" ){ 
			$newfile1    = $file_dir_1.$file11;
			$image_path  = $newfile1;
					
			move_uploaded_file($_FILES['FILE']['tmp_name'], $image_path);

			// Upload file to S3 bucket
			$key_file_name = 'backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/collateral/'.$file11;
			$s3ClientWrapper = new s3ClientWrapper();
			$url = $s3ClientWrapper->uploadFile($key_file_name, $image_path);
		
			// $COLLATERAL['FILE_LOCATION'] = $image_path;
			$COLLATERAL['FILE_LOCATION'] = $url;

			// delete tmp file
			unlink($image_path);
		}
	}
	
	if($_GET['id'] == ''){
		$COLLATERAL['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$COLLATERAL['CREATED_BY']  = $_SESSION['PK_USER'];
		$COLLATERAL['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_COLLATERAL', $COLLATERAL, 'insert');
	} else {
		$COLLATERAL['EDITED_BY'] = $_SESSION['PK_USER'];
		$COLLATERAL['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_COLLATERAL', $COLLATERAL, 'update'," PK_COLLATERAL = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	
	if($_GET['p'] == 'i')
		$URL = "index";
	else
		$URL = "manage_collateral";
	header("location:".$URL);
}
if($_GET['id'] == ''){
	$FILE_NAME 		= '';
	$DESCRIPTION 	= '';
	$FILE_LOCATION 	= '';
	$ACTIVE	 		= '';	
} else {
	$res = $db->Execute("SELECT * FROM S_COLLATERAL WHERE PK_COLLATERAL = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_collateral");
		exit;
	}
	$FILE_NAME 		= $res->fields['FILE_NAME'];
	$DESCRIPTION 	= $res->fields['DESCRIPTION'];
	$FILE_LOCATION 	= $res->fields['FILE_LOCATION'];
	$ACTIVE  		= $res->fields['ACTIVE'];
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
	<title><?=COLLATERAL_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=COLLATERAL_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
									<div class="row">
										<div class="form-group col-6">
											<? if($FILE_LOCATION == '') { ?>
											<label><?=LOGO?></label>
											<div class="input-group">
												<div class="input-group-prepend" style="margin-top: 5px;" >
													<span class="input-group-text"><?=FILE?></span>
												</div>
												<div class="custom-file">
													<input type="file" name="FILE" id="FILE" class="custom-file-input" >
													<label class="custom-file-label" for="FILE"><?=CHOOSE_FILE?></label>
												</div>
											</div>
											<? } ?>
										</div>
									</div>
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40" id="FILE_NAME_DIV" >
												<input type="text" class="form-control required-entry" id="FILE_NAME" name="FILE_NAME" value="<?=$FILE_NAME?>" >
												<span class="bar"></span>
												<label for="FILE_NAME"><?=FILE_NAME?></label>
											</div>
										</div>
                                    </div>
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<textarea class="form-control required-entry" id="DESCRIPTION" name="DESCRIPTION" ><?=$DESCRIPTION?></textarea>
												<span class="bar"></span>
												<label for="DESCRIPTION"><?=DESCRIPTION?></label>
											</div>
										</div>
                                    </div>
									<? if($FILE_LOCATION != '') { ?>
										<table>
											<tr>
												<td><a href="<?=$FILE_LOCATION?>" target="_blank" ><?=VIEW_FILE?></a></td>
												<td>
													<a data-toggle="modal" data-target="#confirm-modal" >
														<i class="icon-trash"></i>
													</a>
												</td>
											</tr>
										</table>
										<? } ?>
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
												
												<? if($_GET['p'] == 'i')
													$URL = "index";
												else
													$URL = "manage_collateral"; ?>
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
		
		<div id="confirm-modal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><?=DELETE_CONFIRMATION?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <p><?=FILE_DELETE?></p>
                        </form>
                    </div>
                    <div class="modal-footer">
						<button type="button" onclick="conf_delete()" class="btn btn-danger waves-effect waves-light"><?=YES?></button>
                        <button type="button" class="btn btn-default waves-effect" data-dismiss="modal"><?=NO?></button>
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
			$('#FILE').change(function(e){
				var fileName = e.target.files[0].name;
				$("#FILE_NAME").val(fileName);
				document.getElementById('FILE_NAME_DIV').classList.add("focused");
			});
		});
		
		function conf_delete(){
			jQuery(document).ready(function($) {
				window.location.href = 'collateral?act=del_file&id=<?=$_GET['id']?>';
				$("#confirm-modal").modal("hide");
			});
		}
	</script>

</body>

</html>