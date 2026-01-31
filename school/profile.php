<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/profile.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	// $file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/';
	$file_dir_1 = '../backend_assets/tmp_upload/';
	if($_FILES['IMAGE']['name'] != ''){
		require_once("../global/image_fun.php");
		$extn 			= explode(".",$_FILES['IMAGE']['name']);
		$iindex			= count($extn) - 1;
		$rand_string 	= time()."-".rand(10000,99999);
		$file11			= 'profile_'.$_SESSION['PK_USER'].$rand_string.".".$extn[$iindex];	
		$extension   	= strtolower($extn[$iindex]);
		
		if($extension == "gif" || $extension == "jpeg" || $extension == "pjpeg" || $extension == "png" || $extension == "jpg"){ 
			$newfile1    = $file_dir_1.$file11;
			$image_path  = $newfile1;
					
			move_uploaded_file($_FILES['IMAGE']['tmp_name'], $image_path);
			$size = getimagesize($image_path);
			$new_w = 400;
			$new_h = 400;
			
			if($size['0'] > $new_w || $size['1'] >  $new_h) {
				$image_path = thumb_gallery($file11,$file11,$new_w,$new_h,$file_dir_1,1);
			}

			// Upload file to S3 bucket
			$key_file_name = 'backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/'.$file11;
			$s3ClientWrapper = new s3ClientWrapper();
			$url = $s3ClientWrapper->uploadFile($key_file_name, $image_path);

			// $EMPLOYEE_MASTER['IMAGE']  = $image_path;
			$EMPLOYEE_MASTER['IMAGE']  = $url;
			$_SESSION['PROFILE_IMAGE'] = $EMPLOYEE_MASTER['IMAGE'];

			// delete tmp file
			unlink($image_path);
		}
	}
	$EMPLOYEE_MASTER['FIRST_NAME']  = $_POST['FIRST_NAME'];
	$EMPLOYEE_MASTER['LAST_NAME']  	= $_POST['LAST_NAME'];
	$EMPLOYEE_MASTER['EMAIL']  		= $_POST['EMAIL'];
	$EMPLOYEE_MASTER['EDITED_BY']   = $_SESSION['PK_USER'];
	$EMPLOYEE_MASTER['EDITED_ON']   = date("Y-m-d H:i");
	db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER, 'update'," PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' ");
		
	$EMPLOYEE_CONTACT['CELL_PHONE'] = $_POST['CELL_PHONE'];
	$EMPLOYEE_CONTACT['EDITED_BY']   = $_SESSION['PK_USER'];
	$EMPLOYEE_CONTACT['EDITED_ON']   = date("Y-m-d H:i");
	db_perform('S_EMPLOYEE_CONTACT', $EMPLOYEE_CONTACT, 'update'," PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' ");
	
	
	$USER['PK_LANGUAGE'] = $_POST['PK_LANGUAGE'];
	db_perform('Z_USER', $USER, 'update'," PK_USER = '$_SESSION[PK_USER]'  ");
	$_SESSION['PK_LANGUAGE']= $USER['PK_LANGUAGE'];
	
	header("location:index");
}
if($_GET['act'] == 'delImg')	{
	
	$res = $db->Execute("SELECT IMAGE FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' ");
	unlink($res->fields['IMAGE']);
	$db->Execute("UPDATE S_EMPLOYEE_MASTER SET IMAGE = '' WHERE PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' ");
	
	$_SESSION['PROFILE_IMAGE'] = '';
	
	header("location:profile");
}	
$res = $db->Execute("SELECT FIRST_NAME,LAST_NAME,EMAIL,CELL_PHONE,PK_ROLES,S_EMPLOYEE_MASTER.ACTIVE,USER_ID,IMAGE,PK_LANGUAGE FROM S_EMPLOYEE_MASTER, S_EMPLOYEE_CONTACT, Z_USER WHERE  S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_EMPLOYEE_CONTACT.PK_EMPLOYEE_MASTER AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER = '$_SESSION[PK_USER]' AND PK_USER_TYPE = 2 "); 

$FIRST_NAME  = $res->fields['FIRST_NAME'];
$LAST_NAME 	 = $res->fields['LAST_NAME'];
$EMAIL 		 = $res->fields['EMAIL'];
$CELL_PHONE	 = $res->fields['CELL_PHONE'];
$IMAGE		 = $res->fields['IMAGE'];
$PK_LANGUAGE = $res->fields['PK_LANGUAGE'];
	
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
	<title><?=PROFILE_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=PROFILE_PAGE_TITLE?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input type="text" class="form-control required-entry" id="FIRST_NAME" name="FIRST_NAME" value="<?=$FIRST_NAME?>" placeholder=""  >
											<span class="bar"></span> 
											<label for="FIRST_NAME"><?=FIRST_NAME?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input type="text" class="form-control" id="LAST_NAME" name="LAST_NAME" value="<?=$LAST_NAME?>" placeholder=""  >
											<span class="bar"></span> 
											<label for="LAST_NAME"><?=LAST_NAME?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group focused">
											<input id="EMAIL" name="EMAIL" type="text" class="form-control " value="<?=$EMAIL?>">
											<span class="bar"></span> 
											<label for="EMAIL"><?=EMAIL?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group <? if($CELL_PHONE != '') echo 'focused'; ?> ">
											<input id="CELL_PHONE" name="CELL_PHONE" type="text" class="form-control phone-inputmask" value="<?=$CELL_PHONE?>">
											<span class="bar"></span> 
											<label for="CELL_PHONE"><?=CELL_PHONE?></label>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="control-label"><?=PREFERRED_LANGUAGE?></label>
												<br />
												<? $res_type = $db->Execute("select PK_LANGUAGE, LANGUAGE from Z_LANGUAGE WHERE ACTIVE = '1' ");
												while (!$res_type->EOF) { ?>
													<div class="custom-control custom-radio">
														<input type="radio" id="PK_LANGUAGE_<?=$res_type->fields['PK_LANGUAGE'] ?>" name="PK_LANGUAGE" value="<?=$res_type->fields['PK_LANGUAGE'] ?>" class="custom-control-input"  <? if($PK_LANGUAGE == $res_type->fields['PK_LANGUAGE']) echo "checked"; ?> >
														<label class="custom-control-label" for="PK_LANGUAGE_<?=$res_type->fields['PK_LANGUAGE'] ?>"><?=$res_type->fields['LANGUAGE']?></label>
													</div>
												<?	$res_type->MoveNext();
												} ?>
											</div>
										</div>
									</div>
									
									<div class="form-group">
										<label class="col-sm-2 position-relative"><?=IMAGE?></label>
										<div class="col-sm-5">
											<? if($IMAGE == '') { ?>
												<div class="input-group">
													<div class="custom-file student-profile-image">
														<input type="file" name="IMAGE" class="custom-file-input" id="inputGroupFile01">
														<label class="custom-file-label" for="inputGroupFile01"><img src="../backend_assets/images/user.png">
															<i class="fa fa-edit"></i>
														</label>
													</div>
												</div>
											<? } else { ?>
											<table>
												<tr>
													<td><img src="<?=$IMAGE?>" style="height:80px;" /></td>
													<td>
														<a data-toggle="modal" data-target="#confirm-modal" >
															<i class="icon-trash"></i>
														</a>
													</td>
												</tr>
											</table>
											<? } ?>
										</div>
									</div>
									
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										
										<div class="col-9 col-sm-9">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											<button type="button" onclick="window.location.href='index'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
										</div>
									</div>
								</div>
							</form>
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
                            <p><?=IMAGE_DELETE?></p>
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
		function conf_delete(){
			jQuery(document).ready(function($) {
				window.location.href = 'profile?act=delImg';
			});	
		}
	</script>

</body>

</html>