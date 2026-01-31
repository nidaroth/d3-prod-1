<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

require_once("../global/mail.php"); 
require_once("../global/texting.php"); 

$msg = ""; // Ticket # 1426
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	
	if($_POST['IS_ADMIN'] == 1)
		$PK_ROLES = 2;
	else
		$PK_ROLES = 3;
	
	
	$EMPLOYEE_MASTER['NEED_SCHOOL_ACCESS'] 	= $_POST['NEED_SCHOOL_ACCESS'];
	$EMPLOYEE_MASTER['IS_ADMIN'] 			= $_POST['IS_ADMIN'];
	$EMPLOYEE_MASTER['LOGIN_CREATED'] 		= 1;
	db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_GET[eid]' ");

	do {
		$USER_API_KEY = generateRandomString(60);
		$res_key = $db->Execute("SELECT PK_USER FROM Z_USER where USER_API_KEY = '$USER_API_KEY'");
	} while ($res_key->RecordCount() > 0);

	/* Ticket # 1426 */
	$res_user1 = $db->Execute("SELECT PK_USER FROM Z_USER WHERE USER_ID = '$_POST[USER_ID]' ");
	if($res_user1->RecordCount() == 0) {
		$res_user  = $db->Execute("SELECT PK_USER FROM Z_USER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_USER_TYPE = 2 AND ID = '$_GET[eid]' ");
		
		$salt = substr(strtr(base64_encode(openssl_random_pseudo_bytes(22)),'+','.'),0,22);
		$hash = crypt($_POST['PASSWORD'], '$2y$12$' . $salt);
		$USER['PASSWORD']  	 	= $hash;
		$USER['PK_ROLES']  		= $PK_ROLES;
		
		if($res_user->RecordCount() == 0) {
			$USER['ID']  	 	 	= $_GET['eid'];
			$USER['USER_API_KEY']  	= $USER_API_KEY;
			$USER['USER_ID']  		= $_POST['USER_ID'];
			$USER['PK_USER_TYPE']  	= 2;
			$USER['FIRST_LOGIN']  	= 1;
			$USER['PK_LANGUAGE']  	= $_POST['PK_LANGUAGE'];
			$USER['PK_ACCOUNT']  	= $_SESSION['PK_ACCOUNT'];
			$USER['CREATED_BY']  	= $_SESSION['PK_USER'];
			$USER['CREATED_ON']  	= date("Y-m-d H:i");
			db_perform('Z_USER', $USER, 'insert');
			$PK_USER = $db->insert_ID();
		} else {
			$USER['EDITED_BY']  	= $_SESSION['PK_USER'];
			$USER['EDITED_ON']  	= date("Y-m-d H:i");
			$PK_USER = $res_user->fields['PK_USER'];
			db_perform('Z_USER', $USER, 'update'," PK_USER = '$PK_USER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
		
		/* ticket #1711  */
		$res_emp = $db->Execute("SELECT IS_FACULTY FROM S_EMPLOYEE_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_GET[eid]' ");
		if($res_emp->fields['IS_FACULTY'] == 1)
			$PK_EVENT_TYPE = 15;
		else
			$PK_EVENT_TYPE = 18;
		
		$res_noti = $db->Execute("SELECT PK_EMAIL_TEMPLATE,PK_TEXT_TEMPLATE FROM S_NOTIFICATION_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EVENT_TYPE = '$PK_EVENT_TYPE' ");
		/* ticket #1711  */
		
		if($res_noti->RecordCount() > 0) {
			if($res_noti->fields['PK_EMAIL_TEMPLATE'] > 0) {
				send_instructor_portal_access_mail($_GET['eid'],$res_noti->fields['PK_EMAIL_TEMPLATE'],$USER['USER_ID'],$_POST['PASSWORD']);
			}
			
			if($res_noti->fields['PK_TEXT_TEMPLATE'] > 0) {
				send_instructor_portal_access_text($_GET['eid'],$res_noti->fields['PK_TEXT_TEMPLATE'],$USER['USER_ID'],$_POST['PASSWORD']);
			}
		}

		if($_POST['IS_ADMIN'] == 1) {
			$USER_ACCESS['ADMISSION_ACCESS']   			= 3;
			$USER_ACCESS['REGISTRAR_ACCESS']   			= 3;
			$USER_ACCESS['FINANCE_ACCESS']   			= 3;
			$USER_ACCESS['ACCOUNTING_ACCESS']   		= 3;
			$USER_ACCESS['PLACEMENT_ACCESS']   			= 3;
			$USER_ACCESS['MANAGEMENT_ADMISSION']   		= 1;
			$USER_ACCESS['MANAGEMENT_REGISTRAR']   		= 1;
			$USER_ACCESS['MANAGEMENT_FINANCE']   		= 1;
			$USER_ACCESS['MANAGEMENT_ACCOUNTING']   	= 1;
			$USER_ACCESS['MANAGEMENT_PLACEMENT']   		= 1;
			$USER_ACCESS['MANAGEMENT_ACCREDITATION']   	= 1;
			$USER_ACCESS['MANAGEMENT_TITLE_IV_SERVICER']= 1;
			
			$USER_ACCESS['MANAGEMENT_90_10']			= 1;
			$USER_ACCESS['MANAGEMENT_IPEDS']			= 1;
			$USER_ACCESS['MANAGEMENT_POPULATION_REPORT']= 1;
			$USER_ACCESS['MANAGEMENT_BULK_UPDATE']		= 1; //Ticket # 1911 
			$USER_ACCESS['MANAGEMENT_DIAMOND_PAY']		= 1; //Ticket # 1940
			
			$USER_ACCESS['MANAGEMENT_UPLOADS']   		= 1;
			$USER_ACCESS['REPORT_ADMISSION']   			= 1;
			$USER_ACCESS['REPORT_REGISTRAR']   			= 1;
			$USER_ACCESS['REPORT_FINANCE']   			= 1;
			$USER_ACCESS['REPORT_ACCOUNTING']   		= 1;
			$USER_ACCESS['REPORT_PLACEMENT']   			= 1;
			$USER_ACCESS['REPORT_CUSTOM_REPORT']   		= 1;
			$USER_ACCESS['REPORT_COMPLIANCE_REPORTS']	= 1;
			$USER_ACCESS['SETUP_SCHOOL']   				= 1;
			$USER_ACCESS['SETUP_ADMISSION']   			= 1;
			$USER_ACCESS['SETUP_STUDENT']   			= 1;
			$USER_ACCESS['SETUP_FINANCE']   			= 1;
			$USER_ACCESS['SETUP_REGISTRAR']   			= 1;
			$USER_ACCESS['SETUP_ACCOUNTING']   			= 1;
			$USER_ACCESS['SETUP_PLACEMENT']   			= 1;
			$USER_ACCESS['SETUP_COMMUNICATION']   		= 1;
			$USER_ACCESS['SETUP_TASK_MANAGEMENT']   	= 1;
		}
		
		$res_user = $db->Execute("SELECT PK_USER_ACCESS FROM Z_USER_ACCESS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_USER = '$PK_USER' ");
		if($res_user->RecordCount() == 0) {
			$USER_ACCESS['PK_USER']  	= $PK_USER;
			$USER_ACCESS['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
			$USER_ACCESS['CREATED_BY']  = $_SESSION['PK_USER'];
			$USER_ACCESS['CREATED_ON']  = date("Y-m-d H:i");
			db_perform('Z_USER_ACCESS', $USER_ACCESS, 'insert');
		} else {
			$USER_ACCESS['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
			$USER_ACCESS['EDITED_BY']  = $_SESSION['PK_USER'];
			$USER_ACCESS['EDITED_ON']  = date("Y-m-d H:i");
			db_perform('Z_USER_ACCESS', $USER_ACCESS, 'update', " PK_USER = '$PK_USER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
		
		header("location:employee?id=".$_GET['eid'].'&t='.$_GET['t'].'&tab=user_access');
		exit;
	} else {
		$msg = 'User ID '.$_POST['USER_ID'].' Already Exists';
	}
	/* Ticket # 1426 */
}


$res = $db->Execute("SELECT FIRST_NAME,LAST_NAME,EMAIL,CELL_PHONE,S_EMPLOYEE_MASTER.ACTIVE,LOGIN_CREATED,EMAIL, IS_ADMIN, IS_FACULTY, NEED_SCHOOL_ACCESS FROM S_EMPLOYEE_MASTER, S_EMPLOYEE_CONTACT WHERE S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = '$_GET[eid]' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_EMPLOYEE_CONTACT.PK_EMPLOYEE_MASTER AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->RecordCount() == 0 || $res->fields['LOGIN_CREATED']){
	header("location:manage_employee?t=".$_GET['t']);
	exit;
}

$FIRST_NAME 		= $res->fields['FIRST_NAME'];
$LAST_NAME 			= $res->fields['LAST_NAME'];
$EMAIL  			= $res->fields['EMAIL'];
$CELL_PHONE 		= $res->fields['CELL_PHONE'];
$USER_ID			= $res->fields['EMAIL'];
$IS_ADMIN			= $res->fields['IS_ADMIN'];
$NEED_SCHOOL_ACCESS = $res->fields['NEED_SCHOOL_ACCESS'];
$IS_FACULTY			= $res->fields['IS_FACULTY'];
$ACTIVE				= $res->fields['ACTIVE'];
$PK_LANGUAGE		= 1;

$res = $db->Execute("SELECT EMP_DEFAULT_PASSWORD FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
$EMP_DEFAULT_PASSWORD = $res->fields['EMP_DEFAULT_PASSWORD'];
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
	<title><?=CREATE_LOGIN?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=CREATE_LOGIN?> - <?=$FIRST_NAME.' '.$LAST_NAME?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<!-- Ticket # 1426 -->
									<? if($msg != ''){ ?>
										<div class="d-flex">
											<div class="col-12 col-sm-6 form-group" style="color:red" >
												<?=$msg ?>
											</div>
										</div>
									<? } ?>
									<!-- Ticket # 1426 -->
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<? if($IS_FACULTY == 0) { ?>
											<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
												<input type="checkbox" class="custom-control-input" id="IS_ADMIN" name="IS_ADMIN" value="1" <? if($IS_ADMIN == 1) echo "checked";?> >
												<label class="custom-control-label" for="IS_ADMIN"><?=IS_SCHOOL_ADMIN?></label>
											</div>
											<? } else { ?>
											<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
												<input type="checkbox" class="custom-control-input" id="NEED_SCHOOL_ACCESS" name="NEED_SCHOOL_ACCESS" value="1" <? if($NEED_SCHOOL_ACCESS == 1) echo "checked";?> >
												<label class="custom-control-label" for="NEED_SCHOOL_ACCESS"><?=NEED_SCHOOL_ACCESS?></label>
											</div>
											<? } ?>
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
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input id="USER_ID" name="USER_ID" type="text" class="form-control required" value="<?=$USER_ID?>" onBlur="duplicate_check()" >
											<span class="bar"></span> 
											<label for="USER_ID"><?=USER_ID?></label>
											<div id="already_exit" style="display:none;color:#ff0000;" >User ID already exists. Try with another.</div>
										</div>											
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input id="PASSWORD" name="PASSWORD" type="PASSWORD" class="form-control required" value="<?=$EMP_DEFAULT_PASSWORD?>" autocomplete="new-password">
											<span class="bar"></span> 
											<label for="PASSWORD"><?=PASSWORD?></label>
										</div>											
									</div>
									
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										
										<div class="col-9 col-sm-9">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											
											<button type="button" onclick="window.location.href='employee?id=<?=$_GET['eid']?>&t=<?=$_GET['t']?>'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
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

	<!-- <script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script> -->
	<script src="../backend_assets/dist/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="../backend_assets/dist/jquery-validation/dist/additional-methods.min.js"></script>

<script type="text/javascript">
$(function () {
  $.validator.addMethod("strong_password", function (value, element) {
    let password = value;
    if (!(/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[@#$%&])(.{8,20}$)/.test(password))) {
        return false;
    }
    return true;
}, function (value, element) {
    let password = $(element).val();
    if (!(/^(.{8,20}$)/.test(password))) {
        return 'Password must be between 8 and 20 characters long.';
    }
    else if (!(/^(?=.*[A-Z])/.test(password))) {
        return 'Password must contain atleast one uppercase.';
    }
    else if (!(/^(?=.*[a-z])/.test(password))) {
        return 'Password must contain atleast one lowercase.';
    }
    else if (!(/^(?=.*[0-9])/.test(password))) {
        return 'Password must contain atleast one digit.';
    }
    else if (!(/^(?=.*[@#$%&])/.test(password))) {
        return "Password must contain special characters from @#$%&.";
    }
    return false;
});
  $.validator.setDefaults({
    submitHandler: function () {
      document.form1.submit();
    }
  });
  $('#form1').validate({
    rules: {
      PASSWORD: {
        required: true,
        minlength: 8,
        strong_password:true
      }	
    },
    messages: {
      PASSWORD: {
        required: "Please enter new password",
        minlength: "Your password must be at least 8 characters long"

      }	
    },
    errorElement: 'span',
    errorPlacement: function (error, element) {
      error.addClass('invalid-feedback');
      var name = $(element).attr("name");
      //error.appendTo($("#" + name + "_validate"));
	  element.closest('.form-group').append(error);
   
    },
    highlight: function (element, errorClass, validClass) {
      $(element).addClass('is-invalid');
    },
    unhighlight: function (element, errorClass, validClass) {
      $(element).removeClass('is-invalid');
    }
  });
});
</script>	
	
	<script type="text/javascript">
		//var form1 = new Validation('form1');
		
		jQuery(document).ready(function($) {
			duplicate_check()
		});	
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
								document.getElementById('already_exit').style.display 	= "block";
								document.getElementById('USER_ID').value 				= "";
								return false;
							}else{
								document.getElementById('already_exit').style.display 	= "none";
								document.getElementById('USER_ID').readOnly 			= true;
							}
						}
					});
				}
			});	
		}
	</script>

</body>

</html>
