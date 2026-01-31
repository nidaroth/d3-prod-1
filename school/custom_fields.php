<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/custom_fields.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$CUSTOM_FIELDS = $_POST;
	if($_GET['id'] == ''){
		$CUSTOM_FIELDS['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$CUSTOM_FIELDS['CREATED_BY']  = $_SESSION['PK_USER'];
		$CUSTOM_FIELDS['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_CUSTOM_FIELDS', $CUSTOM_FIELDS, 'insert');
	} else {
		$CUSTOM_FIELDS['EDITED_BY'] = $_SESSION['PK_USER'];
		$CUSTOM_FIELDS['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_CUSTOM_FIELDS', $CUSTOM_FIELDS, 'update'," PK_CUSTOM_FIELDS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	header("location:manage_custom_fields");
}
if($_GET['id'] == ''){
	$SECTION 		= 1;
	$PK_DEPARTMENT 	= '';
	$TAB 			= '';
	$FIELD_NAME 	= '';
	$ACTIVE	 		= '';	
	$PK_DATA_TYPES 	= '';
	$PK_USER_DEFINED_FIELDS = '';
} else {
	$res = $db->Execute("SELECT * FROM S_CUSTOM_FIELDS WHERE PK_CUSTOM_FIELDS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_custom_fields");
		exit;
	}
	$SECTION 		= $res->fields['SECTION'];
	$PK_DEPARTMENT 	= $res->fields['PK_DEPARTMENT'];
	$TAB 			= $res->fields['TAB'];
	$FIELD_NAME 	= $res->fields['FIELD_NAME'];
	$ACTIVE  		= $res->fields['ACTIVE'];
	$PK_DATA_TYPES  = $res->fields['PK_DATA_TYPES'];
	$PK_USER_DEFINED_FIELDS = $res->fields['PK_USER_DEFINED_FIELDS'];
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
	<title><?=CUSTOM_FIELDS_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=CUSTOM_FIELDS_PAGE_TITLE?> </h4>
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
												<select id="SECTION" name="SECTION" class="form-control required-entry" onchange="show_fields(this.value)" >
													<!--<option value=""></option>-->
													<option value="1" <? if($SECTION == 1) echo "selected"; ?> >Student</option>
													<option value="2" <? if($SECTION == 2) echo "selected"; ?> >Employee</option>
													<!--<option value="3" <? if($SECTION == 3) echo "selected"; ?> >Teacher</option>-->
												</select>
												<span class="bar"></span>
												<label for="SECTION"><?=SECTION?></label>
											</div>
										</div>
                                    </div>
									
									<? if($SECTION == 1) {
										$div_style  = "display:block;";
										$class_name = "form-control required-entry";
									} else {
										$div_style  = "display:none;";
										$class_name = "form-control";
									} ?>
									
									<div class="row" id="PK_DEPARTMENT_DIV" style="<?=$div_style?>" >
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_DEPARTMENT" name="PK_DEPARTMENT" class="<?=$class_name?>" >
													<option selected></option>
													<? $res_type = $db->Execute("select PK_DEPARTMENT, DEPARTMENT from M_DEPARTMENT WHERE ACTIVE = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER IN (1,2,4,6,7) ORDER BY DEPARTMENT ASC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_DEPARTMENT'] ?>" <? if($res_type->fields['PK_DEPARTMENT'] == $PK_DEPARTMENT) echo "selected"; ?> ><?=$res_type->fields['DEPARTMENT']?></option>
													<?	$res_type->MoveNext();
													} ?>
													<option value="-1" <? if($PK_DEPARTMENT == -1) echo "selected"; ?> >All Modules</option>
												</select>
												<span class="bar"></span>
												<label for="PK_DEPARTMENT"><?=MODULE?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row" id="TAB_DIV" style="<?=$div_style?>" >
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="TAB" name="TAB" class="<?=$class_name?>">
													<option value=""></option>
													<?php if(has_wvjc_access($_SESSION['PK_ACCOUNT'])){ ?>	
													<option value="Default" <? if($TAB == "Default") echo "selected"; ?> >Default</option> <!--DIAM-921-->
													<?php } ?>
													<option value="Info" <? if($TAB == "Info") echo "selected"; ?> >Info</option>
													<option value="Other" <? if($TAB == "Other") echo "selected"; ?> >Enrollment</option>
													<option value="Financial Aid" <? if($TAB == "Financial Aid") echo "selected"; ?> >Financial Aid</option>
													<option value="Financial Aid (AY Specific)" <? if($TAB == "Financial Aid (AY Specific)") echo "selected"; ?> >Financial Aid (AY Specific)</option> <!-- Ticket # 1284 -->
												</select>
												<span class="bar"></span>
												<label for="TAB"><?=TAB?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="FIELD_NAME" name="FIELD_NAME" value="<?=$FIELD_NAME?>" >
												<span class="bar"></span>
												<label for="FIELD_NAME"><?=FIELD_NAME?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PK_DATA_TYPES" name="PK_DATA_TYPES" class="form-control required-entry" onchange="get_user_defined_fields(this.value)" >
													<option selected></option>
													<? $res_type = $db->Execute("select PK_DATA_TYPES, DATA_TYPES from M_DATA_TYPES WHERE ACTIVE = '1' ORDER BY DATA_TYPES ASC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_DATA_TYPES'] ?>" <? if($res_type->fields['PK_DATA_TYPES'] == $PK_DATA_TYPES) echo "selected"; ?> ><?=$res_type->fields['DATA_TYPES']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_DATA_TYPES"><?=DATA_TYPE?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row" id="field_div_1" <? if($PK_USER_DEFINED_FIELDS == '' || $PK_USER_DEFINED_FIELDS == 0) { ?> style="display:none" <? } ?> >
                                        <div class="col-md-6">
											<div class="form-group m-b-40" id="field_label" >
												<div id="field_div" >
													<? $_REQUEST['val'] 				= $PK_DATA_TYPES;
													$_REQUEST['PK_USER_DEFINED_FIELDS'] = $PK_USER_DEFINED_FIELDS;
													include('qr_get_user_define_fields.php');?>
												</div>
												<span class="bar"></span>
												<label for="PK_USER_DEFINED_FIELDS"><?=USER_DEFINED_FIELDS?></label>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_custom_fields'" ><?=CANCEL?></button>
												
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
		
		function get_user_defined_fields(val){
			jQuery(document).ready(function($) { 
				var data = "val="+val;
				//alert(data)
				$.ajax({
					type: "POST",
					url:"qr_get_user_define_fields",
					data:data,
					success: function(result1){ 
						//alert(result1)
						document.getElementById('field_div').innerHTML = result1;
						document.getElementById('field_label').classList.add("focused");
						
						if(document.getElementById('PK_USER_DEFINED_FIELDS').style.display == 'none')
							document.getElementById('field_div_1').style.display = 'none';
						else
							document.getElementById('field_div_1').style.display = 'inline';
					}
				});
			});
		}
		
		function show_fields(val) {
			if(val == 1){
				document.getElementById('PK_DEPARTMENT_DIV').style.display = 'inline';
				document.getElementById('PK_DEPARTMENT_DIV').className	   = 'form-control required-entry';
				
				document.getElementById('TAB_DIV').style.display = 'inline';
				document.getElementById('TAB_DIV').className	 = 'form-control required-entry';
			} else {
				document.getElementById('PK_DEPARTMENT_DIV').style.display = 'none';
				document.getElementById('PK_DEPARTMENT_DIV').className	   = 'form-control';
				
				document.getElementById('TAB_DIV').style.display = 'none';
				document.getElementById('TAB_DIV').className	 = 'form-control';
			}
		}
	</script>

</body>

</html>
