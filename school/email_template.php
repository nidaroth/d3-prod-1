<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/email_template.php");
require_once("check_access.php");

if(check_access('SETUP_COMMUNICATION') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$EMAIL_TEMPLATE = $_POST;
	if($_GET['id'] == ''){
		$EMAIL_TEMPLATE['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$EMAIL_TEMPLATE['CREATED_BY']  = $_SESSION['PK_USER'];
		$EMAIL_TEMPLATE['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_EMAIL_TEMPLATE', $EMAIL_TEMPLATE, 'insert');
	} else {
		$EMAIL_TEMPLATE['EDITED_BY'] = $_SESSION['PK_USER'];
		$EMAIL_TEMPLATE['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_EMAIL_TEMPLATE', $EMAIL_TEMPLATE, 'update'," PK_EMAIL_TEMPLATE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	header("location:manage_email_template");
}
if($_GET['id'] == ''){
	$TEMPLATE_NAME 		= '';
	$SUBJECT			= '';
	$CONTENT			= '';
	$ACTIVE	 			= '';
	$PK_EMAIL_ACCOUNT 	= '';
} else {
	$res = $db->Execute("SELECT * FROM S_EMAIL_TEMPLATE WHERE PK_EMAIL_TEMPLATE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_email_template");
		exit;
	}
	
	$TEMPLATE_NAME 	 	= $res->fields['TEMPLATE_NAME'];
	$SUBJECT 		 	= $res->fields['SUBJECT'];
	$CONTENT 		 	= $res->fields['CONTENT'];
	$ACTIVE  		 	= $res->fields['ACTIVE'];
	$PK_EMAIL_ACCOUNT	= $res->fields['PK_EMAIL_ACCOUNT'];
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
	<title><?=EMAIL_TEMPLATE_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=EMAIL_TEMPLATE_PAGE_TITLE?> </h4>
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
												<input type="text" class="form-control required-entry" id="TEMPLATE_NAME" name="TEMPLATE_NAME" value="<?=$TEMPLATE_NAME?>" >
												<span class="bar"></span>
												<label for="TEMPLATE_NAME"><?=TEMPLATE_NAME?></label>
											</div>
										</div>
										
										<div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_EMAIL_ACCOUNT" name="PK_EMAIL_ACCOUNT" class="form-control required-entry">
													<option></option>
													<? $res_type = $db->Execute("select PK_EMAIL_ACCOUNT,USER_NAME from Z_EMAIL_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by USER_NAME ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_EMAIL_ACCOUNT']?>" <? if($PK_EMAIL_ACCOUNT == $res_type->fields['PK_EMAIL_ACCOUNT']) echo "selected"; ?> ><?=$res_type->fields['USER_NAME'] ?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_EMAIL_ACCOUNT"><?=EMAIL_ACCOUNT?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="SUBJECT" name="SUBJECT" value="<?=$SUBJECT?>" >
												<span class="bar"></span>
												<label for="SUBJECT"><?=SUBJECT?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="row">
												<div class="col-md-12">
													<?=CONTENT?>
												</div>
												<div class="col-md-12">
													<textarea class="form-control required-entry rich" rows="2" id="CONTENT" name="CONTENT"><?=$CONTENT?></textarea>
												</div>
											</div>
										</div>
										<!-- Ticket # 1429 
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-12">
													<?=TAGS?>
												</div>
												<div class="col-md-12">
													{Logo}<br />
													{Student Name}<br />
													{Course Name}<br />
													{Course Description}<br />
													{Course Start Date}<br />
													{Term}<br />
													{Session}<br />
													{Grade Obtained}<br />
													{Login ID}<br />
													{Password}<br />
													{Ledger Code}<br />
													{Receipt #}<br />
													{Amount Paid}<br />
													{Paid Date}<br />
													{Due Amount}<br />
													{Due Date}<br />
													{Student Status}<br />
												</div>
											</div>
										</div> Ticket # 1429  -->
										
										<!-- Ticket # 1429 -->
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-12">
													<button type="button" onclick="show_tags()" class="btn waves-effect waves-light btn-info"><?=TAGS?></button>
												</div>
											</div>
										</div>
										<!-- Ticket # 1429 -->
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_email_template'" ><?=CANCEL?></button>
												
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
	
	<!-- <script src="https://cdn.tiny.cloud/1/d6quzxl18kigwmmr6z03zgk3w47922rw1epwafi19cfnj00i/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script> -->
	<? require_once("../global/tiny-cloud.php"); ?>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
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
					'template paste textcolor colorpicker textpattern '
				],
				toolbar1: 'bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor',	
				paste_data_images: true,
				height: 400,
			});
		});
		
		/* Ticket # 1429  */
		function show_tags(){
			var w = 1000;
			var h = 700;
			// var id = common_id;
			var left = (screen.width/2)-(w/2);
			var top = (screen.height/2)-(h/2);
			var parameter = 'toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,width='+w+', height='+h+', top='+top+', left='+left;
			window.open('show_document_tags?t=1','',parameter);
			return false;
		}
		/* Ticket # 1429  */
	</script>
	

</body>

</html>
