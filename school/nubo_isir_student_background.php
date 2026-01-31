<? 
/**
 * dvb 16 11 2024
 */
require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/isir_student.php");
require_once("../global/common_functions.php");
require_once("../global/create_notification.php"); 
require_once("check_access.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

if(check_access('MANAGEMENT_FINANCE') == 0 ){
	header("location:../index");
	exit;
}
$msg = "";
if(!empty($_POST)) {
	$msg = ERROR_FILE_FORMAT;
	if($_FILES['txtFile']['name'] != ''){
		// $file_dir_1 	= 'temp/';
		// $file_dir_1 	= '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/';
		$file_dir_1 = '../backend_assets/tmp_upload/';
		$extn 			= explode(".",$_FILES['txtFile']['name']);
		$iindex			= count($extn) - 1;
		$rand_string 	= time()."-".rand(10000,99999);
		#$file11			= 'isir_student_'.$_SESSION['PK_USER'].$rand_string.".".$extn[$iindex];	
		$file11 = basename($_FILES['txtFile']['name']);;
		$extension   	= strtolower($extn[$iindex]);


		if($extension == "txt"){
			$msg = "";
			$newfile1    = $file_dir_1.$file11;
			$image_path  = $newfile1;
			move_uploaded_file($_FILES['txtFile']['tmp_name'], $image_path);
			
			// Upload file to S3 bucket
			$key_file_name = 'backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/'.$file11;
			$s3ClientWrapper = new s3ClientWrapper();
			$url = $s3ClientWrapper->uploadFile($key_file_name, $image_path);
			
			$file 		= file_get_contents($image_path, true);
			$text_arr 	= explode("\n", $file);

			// delete tmp file
			// unlink($image_path);
			// save to db

			$res_user = $db->Execute("
				SELECT
				CONCAT_WS(' ',SE.LAST_NAME,SE.FIRST_NAME) AS CREATED_BY_NAME
				,ZU.USER_ID AS EMAIL
				FROM Z_USER ZU
				LEFT JOIN S_EMPLOYEE_MASTER SE ON SE.PK_EMPLOYEE_MASTER = ZU.ID
				-- LIMIT 5
				 WHERE ZU.PK_USER = '$_SESSION[PK_USER]' 
			");
			// print_r($res_user);
			// echo $res_user->fields['EMAIL'];
			// exit;
			// insert into S_ISIR_BACKGROUND_PROCESS 
            $insert = [];
            $insert['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
            $insert['CREATE_LEAD'] = $_POST['CREATE_LEAD']??0;
            $insert['FILE'] = $image_path;
            $insert['STATUS'] = 1;
            $insert['EMAIL'] = $res_user->fields['EMAIL'];
            $insert['CREATED_ON'] = 'now()';
            $insert['CREATED_BY'] =$_SESSION['PK_USER'];
            $result=db_perform('S_ISIR_BACKGROUND_PROCESS', $insert, 'insert');
            // print_r($result);exit;
			header("location:manage_isir_background?showpopup=1");
		}
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
	<title><?=ISIR_UPLOAD_PAGE_TITLE_BACKGROUND?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<style>
		.lds-ring {
			position: absolute;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			margin: auto;
			width: 64px;
			height: 64px;
		}

		.lds-ring div {
			box-sizing: border-box;
			display: block;
			position: absolute;
			width: 51px;
			height: 51px;
			margin: 6px;
			border: 6px solid #0066ac;
			border-radius: 50%;
			animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
			border-color: #007bff transparent transparent transparent;
		}

		.lds-ring div:nth-child(1) {
			animation-delay: -0.45s;
		}

		.lds-ring div:nth-child(2) {
			animation-delay: -0.3s;
		}

		.lds-ring div:nth-child(3) {
			animation-delay: -0.15s;
		}

		@keyframes lds-ring {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}
		#loaders {
			position: fixed;
			width: 100%;
			z-index: 9999;
			bottom: 0;
			background-color: #2c3e50;
			display: block;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			opacity: 0.6;
			display: none;
		}

		.loader-text{
			position: absolute;
			left: 26px;
			top: 177px;
			right: 0;
			bottom: 0;
			margin: auto;
			width: 133px;
			height: 64px;
			color: #fff;
			font-weight: bold;
		}
	</style>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="loaders" style="display: none;">
		<div class="lds-ring">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
		</div>
		<div class="loader-text">Please wait.....!</div>
	</div>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-2 align-self-center">
                        <h4 class="text-themecolor"><?=ISIR_UPLOAD_PAGE_TITLE_BACKGROUND?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-12">
                        <div class="card">
                            <div class="p-20">
                            	<form enctype="multipart/form-data" class="floating-labels m-t-40" method="post" name="form1" id="form1">
                            		<? if ($msg != "") {
                        				echo '<div class="alert alert-danger" role="alert">'.$msg.'</div>';
                            		} ?>
									
									<div class="row" >
										<div class="col-md-2" >
											<div class="d-flex">
												<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
													<input type="checkbox" class="custom-control-input" id="CREATE_LEAD" name="CREATE_LEAD" value="1" >
													<label class="custom-control-label" for="CREATE_LEAD"><?=CREATE_LEAD?></label>
												</div>
											</div>
										</div>
										<div class=" col-sm-1 ">
											<span class="mytooltip tooltip-effect-1">
												<span class="tooltip-item tool_tip_custom">
													<i class="mdi mdi-help-circle help_size"></i>
												</span>
												<span class="tooltip-content clearfix">
													<span class="tooltip-text">
														<? if($_SESSION['PK_LANGUAGE'] == 1)
															$lan_field = "TOOL_CONTENT_ENG";
														else
															$lan_field = "TOOL_CONTENT_SPA"; 
														$res_help = $db->Execute("select $lan_field from Z_HELP WHERE PK_HELP = 7"); 
														echo $res_help->fields[$lan_field]; ?>
													</span>
												</span>
											</span>
										</div>	
									</div>
									
									<div class="row" >
										<div class="col-md-6" >
											<input type="file" name="txtFile" />
										</div>
									</div>
									<div class="d-flex mt-3">
										<input type="submit" name="Upload" value="<?=UPLOAD?>" class="btn btn-info d-none d-lg-block" />
										<a href="manage_isir_background" class="btn waves-effect waves-light btn-dark m-l-15"><?=CANCEL?></a>
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
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">	

	$(document).ready(function(){
		$("#form1").on("submit", function(){
			$("#loaders").fadeIn();
		});//submit
	});//document ready
	
	</script>
					
</body>
</html>