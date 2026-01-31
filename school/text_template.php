<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/text_template.php");
require_once("check_access.php");

if(check_access('SETUP_COMMUNICATION') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$TEXT_TEMPLATE = $_POST;
	if($_GET['id'] == ''){
		$TEXT_TEMPLATE['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$TEXT_TEMPLATE['CREATED_BY']  = $_SESSION['PK_USER'];
		$TEXT_TEMPLATE['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_TEXT_TEMPLATE', $TEXT_TEMPLATE, 'insert');
	} else {
		$TEXT_TEMPLATE['EDITED_BY'] = $_SESSION['PK_USER'];
		$TEXT_TEMPLATE['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_TEXT_TEMPLATE', $TEXT_TEMPLATE, 'update'," PK_TEXT_TEMPLATE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	header("location:manage_text_template");
}
if($_GET['id'] == ''){
	$TEMPLATE_NAME 	= '';
	$CONTENT		= '';
	$ACTIVE	 		= '';
	$PK_TEXT_SETTINGS = "";
} else {
	$res = $db->Execute("SELECT * FROM S_TEXT_TEMPLATE WHERE PK_TEXT_TEMPLATE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_text_template");
		exit;
	}
	
	$TEMPLATE_NAME 	 = $res->fields['TEMPLATE_NAME'];
	$CONTENT 		 = $res->fields['CONTENT'];
	$ACTIVE  		 = $res->fields['ACTIVE'];
	$PK_TEXT_SETTINGS = $res->fields['PK_TEXT_SETTINGS'];
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
	<title><?=TEXT_TEMPLATE_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=TEXT_TEMPLATE_PAGE_TITLE?> </h4>
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
                                    </div>
									
									<? $res_type = $db->Execute("select PK_TEXT_SETTINGS,FROM_NO from S_TEXT_SETTINGS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND FROM_NO != '' "); 
									//if($res_type->RecordCount() > 0){ ?>
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_TEXT_SETTINGS" name="PK_TEXT_SETTINGS" class="form-control required-entry" >
													<option value=""></option>
													<? while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_TEXT_SETTINGS']?>" <? if($PK_TEXT_SETTINGS == $res_type->fields['PK_TEXT_SETTINGS']) echo "selected"; ?> ><?=$res_type->fields['FROM_NO'] ?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_TEXT_SETTINGS"><?=FROM_NO?></label>
											</div>
										</div>
                                    </div>
									<? /*} else { ?>
										<input type="hidden" id="PK_TEXT_SETTINGS" name="PK_TEXT_SETTINGS" value="0" >
									<? }*/ ?>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<textarea class="form-control required-entry" rows="2" id="CONTENT" name="CONTENT"><?=$CONTENT?></textarea>
												<span class="bar"></span>
												<label for="CONTENT"><?=CONTENT?></label>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-12">
													<button type="button" onclick="show_tags()" class="btn waves-effect waves-light btn-info"><?=TAGS?></button>
												</div>
												<!--
												<div class="col-md-12">
													<?=TAGS?>
												</div>
												<div class="col-md-12">
													{First Name}<br />
													{Last Name}<br />
													{Course Name}<br />
													{Course Description}<br />
													{Course Start Date}<br />
													{Term}<br />
													{Grade Obtained}<br />
													{Login ID}<br />
													{Password}<br />
													{Instructor Name}<br />
													{Ledger Code}<br />
													{Receipt #}<br />
													{Amount Paid}<br />
													{Paid Date}<br />
													{Due Amount}<br />
													{Due Date}<br />
													{Student Status}<br />
												</div>
												-->
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_text_template'" ><?=CANCEL?></button>
												
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