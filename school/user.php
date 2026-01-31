<? require_once("../global/config.php"); 
require_once("../global/image_fun.php");
require_once("../language/common.php");
require_once("../language/school_profile.php");

require_once("../global/mail.php"); 
require_once("../global/texting.php"); 

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_ROLES'] != 2 ){ 
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	$EMPLOYEE_MASTER['FIRST_NAME']  = $_POST['FIRST_NAME'];
	$EMPLOYEE_MASTER['LAST_NAME']  	= $_POST['LAST_NAME'];
	$EMPLOYEE_MASTER['EMAIL']  		= $_POST['EMAIL'];
	$EMPLOYEE_CONTACT['CELL_PHONE'] = $_POST['CELL_PHONE'];
	$USER['PK_ROLES']  				= 2;
	$USER['PK_LANGUAGE']  			= $_POST['PK_LANGUAGE'];
	if($_GET['id'] == ''){
		
		$EMPLOYEE_MASTER['IS_ADMIN']    = 1;
		$EMPLOYEE_MASTER['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$EMPLOYEE_MASTER['CREATED_BY']  = $_SESSION['PK_USER'];
		$EMPLOYEE_MASTER['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER, 'insert');
		$PK_EMPLOYEE_MASTER = $db->insert_ID();
		
		$EMPLOYEE_CONTACT['PK_EMPLOYEE_MASTER'] = $PK_EMPLOYEE_MASTER;
		$EMPLOYEE_CONTACT['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
		$EMPLOYEE_CONTACT['CREATED_BY']  		= $_SESSION['PK_USER'];
		$EMPLOYEE_CONTACT['CREATED_ON']  		= date("Y-m-d H:i");
		db_perform('S_EMPLOYEE_CONTACT', $EMPLOYEE_CONTACT, 'insert');
		
		do {
			$USER_API_KEY = generateRandomString(60);
			$res_key = $db->Execute("SELECT PK_USER FROM Z_USER where USER_API_KEY = '$USER_API_KEY'");
		} while ($res_key->RecordCount() > 0);
		
		$salt = substr(strtr(base64_encode(openssl_random_pseudo_bytes(22)),'+','.'),0,22);
		$hash = crypt($_POST['PASSWORD'], '$2y$12$' . $salt);
		$USER['PASSWORD']  	 	= $hash;
		$USER['USER_API_KEY']  	= $USER_API_KEY;
		$USER['ID']  	 	 	= $PK_EMPLOYEE_MASTER;
		$USER['USER_ID']  		= $_POST['USER_ID'];
		$USER['PK_USER_TYPE']  	= 2;
		$USER['FIRST_LOGIN']  	= 1;
		$USER['PK_ACCOUNT']  	= $_SESSION['PK_ACCOUNT'];
		$USER['CREATED_BY']  	= $_SESSION['PK_USER'];
		$USER['CREATED_ON']  	= date("Y-m-d H:i");
		db_perform('Z_USER', $USER, 'insert');
		$PK_USER = $db->insert_ID();
		
		$res_noti = $db->Execute("SELECT PK_EMAIL_TEMPLATE,PK_TEXT_TEMPLATE FROM S_NOTIFICATION_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EVENT_TYPE = 15");
		if($res_noti->RecordCount() > 0) {
			if($res_noti->fields['PK_EMAIL_TEMPLATE'] > 0) {
				send_instructor_portal_access_mail($PK_EMPLOYEE_MASTER,$res_noti->fields['PK_EMAIL_TEMPLATE'],$USER['USER_ID'],$_POST['PASSWORD']);
			}
			
			if($res_noti->fields['PK_TEXT_TEMPLATE'] > 0) {
				send_instructor_portal_access_text($PK_EMPLOYEE_MASTER,$res_noti->fields['PK_TEXT_TEMPLATE'],$USER['USER_ID'],$_POST['PASSWORD']);
			}
		}

	} else {
		$EMPLOYEE_MASTER['ACTIVE']  	= $_POST['ACTIVE'];
		$EMPLOYEE_MASTER['EDITED_BY']   = $_SESSION['PK_USER'];
		$EMPLOYEE_MASTER['EDITED_ON']   = date("Y-m-d H:i");
		db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER, 'update'," PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		$EMPLOYEE_CONTACT['ACTIVE']  	 = $_POST['ACTIVE'];
		$EMPLOYEE_CONTACT['EDITED_BY']   = $_SESSION['PK_USER'];
		$EMPLOYEE_CONTACT['EDITED_ON']   = date("Y-m-d H:i");
		db_perform('S_EMPLOYEE_CONTACT', $EMPLOYEE_CONTACT, 'update'," PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
		
		$USER['ACTIVE']    = $_POST['ACTIVE'];
		$USER['EDITED_BY'] = $_SESSION['PK_USER'];
		$USER['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('Z_USER', $USER, 'update'," ID = '$_GET[id]' AND PK_USER_TYPE = 2 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
	}
//echo "<pre>";print_r($CAMPUS);exit;	
	if($SAVE_CONTINUE == 0)
		header("location:school_profile?&tab=usersTab");
	else
		header("location:user?id=".$PK_USER);
}

if($_GET['id'] == ''){
	$PK_ROLES	 = '';
	$FIRST_NAME  = '';
	$LAST_NAME 	 = '';
	$EMAIL	 	 = '';
	$PHONE	 	 = '';
	$PK_LANGUAGE = 1;
	
} else {
	$res = $db->Execute("SELECT FIRST_NAME,LAST_NAME,EMAIL,CELL_PHONE,PK_ROLES,S_EMPLOYEE_MASTER.ACTIVE,USER_ID,PK_LANGUAGE FROM S_EMPLOYEE_MASTER, S_EMPLOYEE_CONTACT, Z_USER WHERE S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = '$_GET[id]' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_EMPLOYEE_CONTACT.PK_EMPLOYEE_MASTER AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_USER_TYPE = 2 "); 
	if($res->RecordCount() == 0){
		header("location:school_profile");
		exit;
	}
	
	$PK_ROLES 		= $res->fields['PK_ROLES'];
	$USER_ID 		= $res->fields['USER_ID'];
	$FIRST_NAME 	= $res->fields['FIRST_NAME'];
	$LAST_NAME 		= $res->fields['LAST_NAME'];
	$EMAIL  		= $res->fields['EMAIL'];
	$CELL_PHONE 	= $res->fields['CELL_PHONE'];
	$PK_LANGUAGE	= $res->fields['PK_LANGUAGE'];
	$ACTIVE			= $res->fields['ACTIVE'];
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
	<title><?=SCHOOL_USER?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=SCHOOL_USER?></h4>
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
											<label for="FIRST_NAME"><?=FIRST_NAME?></label>
										</div>
										<div class="col-12 col-sm-3 form-group">
											<input id="LAST_NAME" name="LAST_NAME" type="text" class="form-control" value="<?=$LAST_NAME?>">
											<span class="bar"></span> 
											<label for="LAST_NAME"><?=LAST_NAME?></label>
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input id="EMAIL" name="EMAIL" type="text" class="form-control " value="<?=$EMAIL?>">
											<span class="bar"></span> 
											<label for="EMAIL"><?=EMAIL?></label>
										</div>	
										<div class="col-12 col-sm-1 form-group">
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
														$res_help = $db->Execute("select $lan_field from Z_HELP WHERE PK_HELP = 4"); 
														echo $res_help->fields[$lan_field]; ?>
													</span>
												</span>
											</span>
										</div>										
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input id="CELL_PHONE" name="CELL_PHONE" type="text" class="form-control phone-inputmask" value="<?=$CELL_PHONE?>">
											<span class="bar"></span> 
											<label for="CELL_PHONE"><?=CELL_PHONE?></label>
										</div>											
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
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
										<div class="col-12 col-sm-1 form-group">
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
														$res_help = $db->Execute("select $lan_field from Z_HELP WHERE PK_HELP = 5"); 
														echo $res_help->fields[$lan_field]; ?>
													</span>
												</span>
											</span>
										</div>	
									</div>
									
									<? if($_GET['id'] == ''){ ?>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input id="USER_ID" name="USER_ID" type="text" class="form-control required-entry" value="<?=$USER_ID?>" onBlur="duplicate_check()" >
											<span class="bar"></span> 
											<label for="USER_ID"><?=USER_ID?></label>
											<div id="already_exit" style="display:none;color:#ff0000;" ><?=USER_ID_EXISTS?></div>
										</div>		
										<div class="col-12 col-sm-1 form-group">
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
														$res_help = $db->Execute("select $lan_field from Z_HELP WHERE PK_HELP = 6"); 
														echo $res_help->fields[$lan_field]; ?>
													</span>
												</span>
											</span>
										</div>	
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input id="PASSWORD" name="PASSWORD" type="PASSWORD" class="form-control required-entry" value="" autocomplete="new-password">
											<span class="bar"></span> 
											<label for="PASSWORD"><?=PASSWORD?></label>
										</div>											
									</div>
									<? } else { ?>
										<div class="d-flex">
											<div class="col-12 col-sm-6 form-group focused">
												<label for="" class="position-relative1"><?=USER_ID?></label>
												<div class="form-control hover-not-allowed"><?=$USER_ID?></div>
												<span class="bar"></span> 
											</div>
										</div>
										
										<div class="row">
											<div class="col-md-6">
												<div class="row form-group">
													<div class="custom-control col-md-4"><?=ACTIVE?></div>
													<div class="custom-control custom-radio col-md-2">
														<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
														<label class="custom-control-label" for="customRadio11"><?=YES?></label>
													</div>
													<div class="custom-control custom-radio col-md-2">
														<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
														<label class="custom-control-label" for="customRadio22"><?=NO?></label>
													</div>
												</div>
											</div>
										</div>
									<? } ?>
									
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										
										<div class="col-9 col-sm-9">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											
											<button type="button" onclick="window.location.href='school_profile?&tab=usersTab'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
											<? if ($_GET['id'] != '') { ?>
											<button type="button" onclick="window.location.href='reset_password?id=<?=$_GET['id']?>'"  class="btn waves-effect waves-light btn-dark"><?=RESET_PASSWORD?></button>
											<? } ?>
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
		
		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=DELETE_CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group" id="delete_message" ></div>
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
    </div>
   
	<? require_once("js.php"); ?>

	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		function duplicate_check(){
			jQuery(document).ready(function($) {
				if (document.form1.USER_ID.value  != ""){
					var USER_ID = document.form1.USER_ID.value;
					var data="USER_ID="+USER_ID+'&type=USER_ID';
					$.ajax({
						type: "POST",
						url:"../check_duplicate",
						data:data,
						success: function(result1){ 
							if(result1==1){
								document.getElementById('already_exit').style.display="block";
								document.getElementById('USER_ID').value = "";
								return false;
							}else{
								document.getElementById('already_exit').style.display="none";
							}
						}
					});
				}
			});	
		}
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				if(type == 'logo')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.LOGO?>?';
					
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'logo')
						window.location.href = 'user?act=logo&id=<?=$_GET['id']?>';
				} else
					$("#deleteModal").modal("hide");
			});
		}
	</script>

</body>

</html>