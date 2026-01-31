<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/company_contact.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$COMPANY_CONTACT = $_POST;
	if($_GET['id'] == '') {
		$COMPANY_CONTACT['PK_COMPANY']  = $_GET['cid'];
		$COMPANY_CONTACT['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$COMPANY_CONTACT['CREATED_BY']  = $_SESSION['PK_USER'];
		$COMPANY_CONTACT['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_COMPANY_CONTACT', $COMPANY_CONTACT, 'insert');
		
		$PK_COMPANY_CONTACT = $db->insert_ID();
	} 
	else {
		$COMPANY_CONTACT['EDITED_BY'] = $_SESSION['PK_USER'];
		$COMPANY_CONTACT['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_COMPANY_CONTACT', $COMPANY_CONTACT, 'update'," PK_COMPANY_CONTACT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$_GET[cid]'");
		$PK_COMPANY_CONTACT = $_GET['id'];
	}

	header("location:company?id=".$_GET['cid']."&tab=contactsTab");
}
if($_GET['id'] == '') {
	$PK_PLACEMENT_TYPE = '';
	$NAME			   = '';
	$TITLE 			   = '';
	$DEPARTMENT 	   = '';
	$COMMENT 		   = '';
	$PHONE 			   = '';
	$OTHER_PHONE 	   = '';
	$MOBILE 		   = '';
	$FAX 			   = '';
	$EMAIL 			   = '';
	$ACTIVE  		   = '';
	
} 
else {
	$res = $db->Execute("SELECT * FROM S_COMPANY_CONTACT WHERE PK_COMPANY_CONTACT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$_GET[cid]'"); 
	if($res->RecordCount() == 0) {
		header("location:company?id=".$_GET['id']);
		exit;
	}

	$PK_PLACEMENT_TYPE = $res->fields['PK_PLACEMENT_TYPE'];
	$NAME			   = $res->fields['NAME'];
	$TITLE 			   = $res->fields['TITLE'];
	$DEPARTMENT 	   = $res->fields['DEPARTMENT'];
	$COMMENT 		   = $res->fields['COMMENT'];
	$PHONE 			   = $res->fields['PHONE'];
	$OTHER_PHONE 	   = $res->fields['OTHER_PHONE'];
	$MOBILE 		   = $res->fields['MOBILE'];
	$FAX 			   = $res->fields['FAX'];
	$EMAIL 			   = $res->fields['EMAIL'];
	$ACTIVE  		   = $res->fields['ACTIVE'];
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
	<title><?=COMPANY_CONTACT_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=COMPANY_CONTACT_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-3">
											<div class="form-group m-b-40">
												<input type="text" tabindex="1" class="form-control required-entry" id="NAME" name="NAME" value="<?=$NAME?>" >
												<span class="bar"></span>
												<label for="NAME"><?=NAME?></label>
											</div>
										</div>
										<div class="col-12 col-sm-3">
											<div class="form-group m-b-40">
												<input type="text" tabindex="2" class="form-control" id="TITLE" name="TITLE" value="<?=$TITLE?>" >
												<span class="bar"></span>
												<label for="TITLE"><?=TITLE?></label>
											</div>
										</div>
										<div class="col-12 col-sm-3">
											<div class="form-group m-b-40">
												<input type="text" tabindex="5" class="form-control " id="DEPARTMENT" name="DEPARTMENT" value="<?=$DEPARTMENT?>" >
												<span class="bar"></span>
												<label for="DEPARTMENT"><?=DEPARTMENT?></label>
											</div>
										</div>
										<div class="col-12 col-sm-3">
											<div class="form-group m-b-40">
												<select id="PK_PLACEMENT_TYPE" tabindex="6" name="PK_PLACEMENT_TYPE" class="form-control">
													<option selected></option>
														<? $res_type = $db->Execute("select PK_PLACEMENT_TYPE, TYPE from M_PLACEMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY TYPE ASC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_PLACEMENT_TYPE'] ?>" <? if($PK_PLACEMENT_TYPE == $res_type->fields['PK_PLACEMENT_TYPE']) echo "selected"; ?> ><?=$res_type->fields['TYPE']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_PLACEMENT_TYPE"><?=PLACEMENT_TYPE?></label>
											</div>
										</div>
                                    </div>

									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-3">
											<div class="form-group m-b-40">
												<input type="text" tabindex="3" class="form-control phone-inputmask" id="PHONE" name="PHONE" value="<?=$PHONE?>" >
												<span class="bar"></span>
												<label for="PHONE"><?=PHONE?></label>
											</div>
										</div>
										<div class="col-12 col-sm-3">
											<div class="form-group m-b-40">
												<input type="text" tabindex="4" class="form-control phone-inputmask" id="MOBILE" name="MOBILE" value="<?=$MOBILE?>" >
												<span class="bar"></span>
												<label for="MOBILE"><?=MOBILE?></label>
											</div>
										</div>
										<div class="col-12 col-sm-3">
											<div class="form-group m-b-40">
												<input type="text" tabindex="7" class="form-control phone-inputmask" id="OTHER_PHONE" name="OTHER_PHONE" value="<?=$OTHER_PHONE?>" >
												<span class="bar"></span>
												<label for="OTHER_PHONE"><?=OTHER_PHONE?></label>
											</div>
										</div>
										<div class="col-12 col-sm-3">
											<div class="form-group m-b-40">
												<input type="text" tabindex="8" class="form-control phone-inputmask" id="FAX" name="FAX" value="<?=$FAX?>" >
												<span class="bar"></span>
												<label for="FAX"><?=FAX?></label>
											</div>
										</div>
                                    </div>

									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-6">
											<div class="form-group m-b-40">
												<input type="text" tabindex="9" class="form-control" id="EMAIL" name="EMAIL" value="<?=$EMAIL?>" >
												<span class="bar"></span>
												<label for="EMAIL"><?=EMAIL?></label>
											</div>
										</div>
                                    </div>

									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-6">
											<div class="form-group m-b-40">
												<textarea class="form-control  rich" tabindex="10" id="COMMENT" name="COMMENT"><?=$COMMENT?></textarea>
												<span class="bar"></span> 
												<label for="COMMENT"><?=COMMENT?></label>
											</div>
										</div>
									</div>
								
									<? if($_GET['id'] != ''){ ?>
									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-6">
											<div class="row form-group">
												<div class="custom-control col-md-2 mb-2"><?=ACTIVE?></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" tabindex="11" for="customRadio11">Yes</label>
												</div>
												<div class="custom-control custom-radio col-md-1 ml-2">
													<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label ml-2" tabindex="12" for="customRadio22">No</label>
												</div>
											</div>
										</div>
									</div>
									<? } ?>
									
									<div class="row">
                                        <div class="col-md-5 submit-button-sec">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" tabindex="13" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												<button type="button" tabindex="14" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='company?id=<?=$_GET['cid']?>&tab=contactsTab'" ><?=CANCEL?></button>
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