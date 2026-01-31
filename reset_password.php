<? require_once("global/config.php");
$CODE = $_GET['c'];
$msg  = '';
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);
	$result = $db->Execute("SELECT * FROM Z_RESET_PASSWORD where CODE = '$CODE' AND ACTIVE = '1'");
	$PK_RESET_PASSWORD = $result->fields['PK_RESET_PASSWORD'];
	$PK_USER		   = $result->fields['PK_USER'];
	
	$result1 = $db->Execute("SELECT * FROM Z_USER where PK_USER = '$PK_USER' AND ACTIVE='1'");
	if($result1->RecordCount() > 0){
		$salt =substr(strtr(base64_encode(openssl_random_pseudo_bytes(22)),'+','.'),0,22);
		$hash = crypt($_POST['PASSWORD'], '$2y$12$' . $salt);
	
		$db->Execute("UPDATE Z_USER SET PASSWORD = '$hash' where PK_USER = '$PK_USER'");
		$db->Execute("UPDATE Z_RESET_PASSWORD SET ACTIVE = '0' where PK_RESET_PASSWORD = '$PK_RESET_PASSWORD'");
		
		$msg = 'Password has been changed successfully.';
	} else {
		$msg = 'Your Accouunt has been Blocked. Plase contact Admin';
	}
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Reset Password | <?=$title?></title>
	
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
   <? require_once("css.php"); ?>
</head>

<body class="horizontal-nav skin-default card-no-border">
    <? require_once("loader.php"); ?>
    <section id="wrapper">
		<? require_once("menu.php"); 
		$result = $db->Execute("SELECT * FROM Z_RESET_PASSWORD where CODE = '$CODE' AND ACTIVE = '1'"); ?>
        <div class="login-register" style="background-image:url(backend_assets/images/background/login-register.jpg);">
            <div class="login-box card">
                <div class="card-body">
                    <form class="form-horizontal form-material" method="post" id="loginform" name="loginform" action="">
                        <h3 class="text-center m-b-20">Reset Password</h3>
						
						<? if($msg != ''){ ?>
						<div class="form-group ">
                            <div class="col-xs-12" style="color:red" >
								<?=$msg?>
								<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
							</div>
                        </div>
						<? } ?>
						
                        <? if($result->RecordCount() > 0 ) { ?>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <input class="form-control required validate-admin-password" id="PASSWORD" name="PASSWORD" type="password" placeholder="New Password">
							</div>
                        </div>
						
						<div class="form-group">
                            <div class="col-xs-12">
                                <input class="form-control required validate-cpassword" id="CONFIRM_PASS" name="CONFIRM_PASS" type="password" placeholder="Confirm Password">
							</div>
                        </div>
						
						 <div class="form-group text-center">
                            <div class="col-xs-12 p-b-20">
								<input type="hidden" name="form_name" value="login" >
                                <button class="btn btn-block btn-lg btn-info btn-rounded" type="submit">Reset</button>
                            </div>
                        </div>
						<? } else if($result->RecordCount() == 0 && $msg == ''){ ?>
						<div class="form-group ">
                            <div class="col-xs-12" style="color:red" >
								<center style="color:red">This link has been Expired</center>
								<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
							</div>
                        </div>
						<? } ?>
					
                    </form>
                </div>
            </div>
        </div>
    </section>
	<? require_once("footer.php"); ?>
	<? require_once("js.php"); ?>
   
    
	<!-- <script src="backend_assets/dist/js/validation_prototype.js"></script>
	<script src="backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('loginform');
	</script> -->

	<script src="backend_assets/dist/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="backend_assets/dist/jquery-validation/dist/additional-methods.min.js"></script>

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
      document.loginform.submit();
    }
  });
  $('#loginform').validate({
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
      CONFIRM_PASS:{
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
      CONFIRM_PASS: {
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
