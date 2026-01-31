<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/change_password.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

$msg1 = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$salt = substr(strtr(base64_encode(openssl_random_pseudo_bytes(22)),'+','.'),0,22);
	$hash = crypt($_POST['PASSWORD'], '$2y$12$' . $salt);

	if($_GET['p'] == 's')
		$PK_USER_TYPE = 3;
	else
		$PK_USER_TYPE = 2;
		
	$res = $db->Execute("SELECT PK_USER FROM Z_USER WHERE ID = '$_GET[id]' AND PK_USER_TYPE = '$PK_USER_TYPE' "); 
	$PK_USER = $res->fields['PK_USER'];
	
	//Ticket # 873
	$USER['PASSWORD_CHANGED_ON']  	= date("Y-m-d");
	$USER['RESET_PASSWORD']  		= 0;
	//Ticket # 873
		
	$USER['PASSWORD']  = $hash;
	$USER['EDITED_BY'] = $_SESSION['PK_USER'];
	$USER['EDITED_ON'] = date("Y-m-d H:i");
	db_perform('Z_USER', $USER, 'update'," PK_USER =  '$PK_USER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$msg1 = 'Password Changed Sucessfully';
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
	<title><?=RESET_LOGIN?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=RESET_LOGIN?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<? if($msg1 != ''){ ?>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group" style="color:red" >
											<?=$msg1?>
										</div>
									</div>
									<? } ?>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input type="password" id="PASSWORD" name="PASSWORD" class="required form-control validate-admin-password" />
											<span class="bar"></span> 
											<label for="PASSWORD"><?=NEW_PASSWORD?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input type="password" id="CONF_PASSWORD" name="CONF_PASSWORD" class="form-control required validate-cpassword" />
											<span class="bar"></span> 
											<label for="CONF_PASSWORD"><?=CONFIRM_PASSWORD?></label>
										</div>
									</div>
									
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										
										<div class="col-9 col-sm-9">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											<? if($_GET['p'] == 'e') 
												$URL = 'employee?id='.$_GET['id'].'&t='.$_GET['t'];
											else if($_GET['p'] == 's') 
												$URL = 'student?id='.$_GET['id'].'&eid='.$_GET['eid'].'&t='.$_GET['t'];
											else
												$URL = 'user?id='.$_GET['id']; ?>
											<button type="button" onclick="window.location.href='<?=$URL?>'"  class="btn waves-effect waves-light btn-dark"><?=BACK?></button>
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
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script> -->
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

	// 	CURRENT_PASSWORD: {
    //     required: true,
    //     minlength: 8
    //   },
      
      PASSWORD: {
        required: true,
        minlength: 8,
        strong_password:true
      },
      CONF_PASSWORD:{
        required: true,
        minlength: 8,
        equalTo:"#PASSWORD"
      }
    },
    messages: {

	// 	CURRENT_PASSWORD: {
    //     required: "Please enter current password",
    //     minlength: "Your password must be at least 8 characters long"

    //   },
      PASSWORD: {
        required: "Please enter new password",
        minlength: "Your password must be at least 8 characters long"

      },
      CONF_PASSWORD: {
        required: "Please enter confirm password",
        equalTo: "Confirm password does not match with new password"
      },
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
</body>

</html>
