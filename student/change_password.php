<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/change_password.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$msg1 = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$res = $db->Execute("SELECT PASSWORD FROM Z_USER WHERE PK_USER = '$_SESSION[PK_USER]' ");
	$CUR_PASSWORD = $res->fields['PASSWORD'];	
	$OLD_PASS  = crypt($_POST['OLD_PASSWORD'], $CUR_PASSWORD);
	
	if($CUR_PASSWORD == $OLD_PASS) {
		$salt =substr(strtr(base64_encode(openssl_random_pseudo_bytes(22)),'+','.'),0,22);
		$hash = crypt($_POST['PASSWORD'], '$2y$12$' . $salt);
	
		//Ticket # 873
		$USER['PASSWORD_CHANGED_ON']  	= date("Y-m-d");
		$USER['RESET_PASSWORD']  		= 0;
		//Ticket # 873
		$USER['PASSWORD']  = $hash;
		$USER['EDITED_BY'] = $_SESSION['PK_USER'];
		$USER['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('Z_USER', $USER, 'update'," PK_USER = '$_SESSION[PK_USER]'  ");
		$msg1 = CHANGE_PASSWORD_SUCCESS_MSG;
	} else {
		$msg1 = INVALID_OLD_PASSWORD;
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
	<title><?=CHANGE_PASSWORD_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=CHANGE_PASSWORD_PAGE_TITLE?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<? if($msg1 != '') { ?>
										<div class="form-group">
											<div class="col-lg-2">
												<label>&nbsp;</label>
											</div>
											<div class="col-lg-6">
												<span style="color:#ff0000"><?=$msg1?></span>
											</div>
										</div>
									<? } ?>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input type="password" id="OLD_PASSWORD" name="OLD_PASSWORD" class="required form-control" />
											<span class="bar"></span> 
											<label for="OLD_PASSWORD"><?=CURRENT_PASSWORD?></label>
										</div>
									</div>
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
											<button type="button" onclick="window.location.href='index'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
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

		OLD_PASSWORD: {
        required: true,
        minlength: 8
      },
      
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

		OLD_PASSWORD: {
        required: "Please enter current password",
        minlength: "Your password must be at least 8 characters long"

      },
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
