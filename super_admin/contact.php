<? require_once("../global/config.php"); 
require_once("../global/image_fun.php");
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	$SCHOOL_CONTACT = $_POST;
	if($_GET['id'] == ''){
		$SCHOOL_CONTACT['SHOW_DSIS']   = 1;
		$SCHOOL_CONTACT['PK_ACCOUNT']  = $_GET['s_id'];
		$SCHOOL_CONTACT['CREATED_BY']  = $_SESSION['ADMIN_PK_USER'];
		$SCHOOL_CONTACT['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_SCHOOL_CONTACT', $SCHOOL_CONTACT, 'insert');
		$PK_SCHOOL_CONTACT = $db->insert_ID();
	} else {
		$SCHOOL_CONTACT['EDITED_BY']   = $_SESSION['ADMIN_PK_USER'];
		$SCHOOL_CONTACT['EDITED_ON']   = date("Y-m-d H:i");
		db_perform('S_SCHOOL_CONTACT', $SCHOOL_CONTACT, 'update'," PK_SCHOOL_CONTACT = '$_GET[id]' ");
	}
	header("location:accounts?id=".$_GET['s_id'].'&tab=contactTab');
}

if($_GET['id'] == ''){
	$PK_CONTACT_TYPES	= '';
	$FIRST_NAME 		= '';
	$LAST_NAME 			= '';
	$EMAIL	 			= '';
	$PHONE				= '';
	$PK_CAMPUS			= '';
} else {
	$res = $db->Execute("SELECT * FROM S_SCHOOL_CONTACT WHERE PK_SCHOOL_CONTACT = '$_GET[id]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_campus");
		exit;
	}
	
	$PK_CONTACT_TYPES 	= $res->fields['PK_CONTACT_TYPES'];
	$FIRST_NAME 		= $res->fields['FIRST_NAME'];
	$LAST_NAME 			= $res->fields['LAST_NAME'];
	$EMAIL  			= $res->fields['EMAIL'];
	$PHONE 				= $res->fields['PHONE'];
	$ACTIVE				= $res->fields['ACTIVE'];
	$PK_CAMPUS			= $res->fields['PK_CAMPUS'];
	//echo $PRIMARY_CAMPUS;exit;
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
	<title>School Contact | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">School Contact</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<div class="d-flex">
										<div class="col-12 col-sm-3 form-group">
											<input id="FIRST_NAME" name="FIRST_NAME" type="text" class="form-control required-entry" value="<?=$FIRST_NAME?>">
											<span class="bar"></span> 
											<label for="FIRST_NAME">First Name</label>
										</div>
										<div class="col-12 col-sm-3 form-group">
											<input id="LAST_NAME" name="LAST_NAME" type="text" class="form-control" value="<?=$LAST_NAME?>">
											<span class="bar"></span> 
											<label for="LAST_NAME">Last Name</label>
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="PK_CAMPUS" name="PK_CAMPUS" class="form-control" >
												<option selected></option>
												<option value="-1" <? if($PK_CAMPUS == -1) echo "selected"; ?>  >School Level</option>
												 <? $res_type = $db->Execute("select OFFICIAL_CAMPUS_NAME,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_GET[s_id]' order by OFFICIAL_CAMPUS_NAME ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" <? if($PK_CAMPUS == $res_type->fields['PK_CAMPUS']) echo "selected"; ?> ><?=$res_type->fields['OFFICIAL_CAMPUS_NAME']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
											<span class="bar"></span> 
											<label for="PK_CAMPUS">Campus</label>
										</div>											
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="PK_CONTACT_TYPES" name="PK_CONTACT_TYPES" class="form-control required-entry" >
												<option selected></option>
												 <? $res_type = $db->Execute("select PK_CONTACT_TYPES, CONTACT_TYPES from M_CONTACT_TYPES WHERE ACTIVE = '1' ORDER BY CONTACT_TYPES ASC ");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CONTACT_TYPES'] ?>" <? if($PK_CONTACT_TYPES == $res_type->fields['PK_CONTACT_TYPES']) echo "selected"; ?> ><?=$res_type->fields['CONTACT_TYPES']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
											<span class="bar"></span> 
											<label for="PK_CONTACT_TYPES">Contact Type</label>
										</div>											
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input id="EMAIL" name="EMAIL" type="text" class="form-control validate-email" value="<?=$EMAIL?>">
											<span class="bar"></span> 
											<label for="EMAIL">Email</label>
										</div>											
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input id="PHONE" name="PHONE" type="text" class="form-control phone-inputmask" value="<?=$PHONE?>">
											<span class="bar"></span> 
											<label for="PHONE">Phone</label>
										</div>											
									</div>
									
									<? if($_GET['id'] != ''){ ?>
										<div class="row">
											<div class="col-md-6">
												<div class="row form-group">
													<div class="custom-control col-md-4">Active</div>
													<div class="custom-control custom-radio col-md-2">
														<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
														<label class="custom-control-label" for="customRadio11">Yes</label>
													</div>
													<div class="custom-control custom-radio col-md-2">
														<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
														<label class="custom-control-label" for="customRadio22">No</label>
													</div>
												</div>
											</div>
										</div>
									<? } ?>
									
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										
										<div class="col-9 col-sm-9">
											<button type="button" onclick="validate_form()" class="btn waves-effect waves-light btn-info">Save</button>
											
											<button type="button" onclick="window.location.href='accounts?id=<?=$_GET['s_id'].'&tab=contactTab'?>'"  class="btn waves-effect waves-light btn-dark">Cancel</button>
										</div>
									</div>
								</div>
							</form>
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
		function validate_form(){
			if(document.getElementById('EMAIL').value == '' && document.getElementById('PHONE').value == '') {
				alert('Please enter Email ID or Phone')
			} else {
				var valid = new Validation('form1', {onSubmit:false});
				var result = valid.validate();
				if(result == true)
					document.form1.submit();
			}
		}
	</script>

</body>

</html>