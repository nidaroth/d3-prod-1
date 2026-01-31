<? /*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

require_once("../global/config.php"); 
require_once("../language/common.php");

require_once("../global/mail.php"); 
require_once("../global/texting.php"); 
require_once("check_access.php");
$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

/* Ticket # 1122 */
$res = $db->Execute("SELECT HAS_STUDENT_PORTAL FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
$HAS_STUDENT_PORTAL = $res->fields['HAS_STUDENT_PORTAL'];
if($HAS_STUDENT_PORTAL == 0){ 
	header("location:../index");
	exit;
}
/* Ticket # 1122 */

if($ADMISSION_ACCESS != 2 && $ADMISSION_ACCESS != 3 && $REGISTRAR_ACCESS != 2 && $REGISTRAR_ACCESS != 3){ 
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	$STUDENT_MASTER['LOGIN_CREATED'] = 1;
	db_perform('S_STUDENT_MASTER', $STUDENT_MASTER, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[id]' ");
	
	do {
		$USER_API_KEY = generateRandomString(60);
		$res_key = $db->Execute("SELECT PK_USER FROM Z_USER where USER_API_KEY = '$USER_API_KEY'");
	} while ($res_key->RecordCount() > 0);

	$salt = substr(strtr(base64_encode(openssl_random_pseudo_bytes(22)),'+','.'),0,22);
	$hash = crypt($_POST['PASSWORD'], '$2y$12$' . $salt);
	$USER['PASSWORD']  	 	= $hash;
	$USER['ID']  	 	 	= $_GET['id'];
	$USER['USER_API_KEY']  	= $USER_API_KEY;
	$USER['USER_ID']  		= $_POST['USER_ID'];
	$USER['PK_USER_TYPE']  	= 3;
	$USER['FIRST_LOGIN']  	= 1;
	$USER['PK_LANGUAGE']  	= $_POST['PK_LANGUAGE'];
	$USER['PK_ACCOUNT']  	= $_SESSION['PK_ACCOUNT'];
	$USER['CREATED_BY']  	= $_SESSION['PK_USER'];
	$USER['CREATED_ON']  	= date("Y-m-d H:i");
	db_perform('Z_USER', $USER, 'insert');
	$PK_USER = $db->insert_ID();
	
	$res_noti = $db->Execute("SELECT PK_EMAIL_TEMPLATE,PK_TEXT_TEMPLATE FROM S_NOTIFICATION_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EVENT_TYPE = 10");
	if($res_noti->RecordCount() > 0) {
		if($res_noti->fields['PK_EMAIL_TEMPLATE'] > 0) {
			send_portal_access_mail($_GET['id'],$res_noti->fields['PK_EMAIL_TEMPLATE'],$USER['USER_ID'],$_POST['PASSWORD']);
		}
		
		if($res_noti->fields['PK_TEXT_TEMPLATE'] > 0) {
			send_portal_access_text($_GET['id'],$res_noti->fields['PK_TEXT_TEMPLATE'],$USER['USER_ID'],$_POST['PASSWORD']);
		}
	}
	
	header("location:student?id=".$_GET['id'].'&eid='.$_GET['eid'].'&t='.$_GET['t']);
	exit;
}


$res = $db->Execute("SELECT FIRST_NAME,LAST_NAME, STUDENT_ID FROM S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->RecordCount() == 0 || $res->fields['LOGIN_CREATED']){
	header("location:student?id=".$_GET['id'].'&eid='.$_GET['eid'].'&t='.$_GET['t']);
	exit;
}

$FIRST_NAME  = $res->fields['FIRST_NAME'];
$LAST_NAME 	 = $res->fields['LAST_NAME'];
$STUDENT_ID	 = $res->fields['STUDENT_ID'];
$PK_LANGUAGE = 1;
	
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
											<input id="USER_ID" name="USER_ID" type="text" class="form-control required" value="<?=$STUDENT_ID?>" onBlur="duplicate_check()" >
											<span class="bar"></span> 
											<label for="USER_ID"><?=USER_ID?></label>
											<div id="already_exit" style="display:none;color:#ff0000;" >User ID already exists. Try with another.</div>
										</div>											
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<input id="PASSWORD" name="PASSWORD" type="PASSWORD" class="form-control required" value="" autocomplete="new-password">
											<span class="bar"></span> 
											<label for="PASSWORD"><?=PASSWORD?></label>
										</div>											
									</div>
									
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										
										<div class="col-9 col-sm-9">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											
											<button type="button" onclick="window.location.href='student?id=<?=$_GET['id']?>&eid=<?=$_GET['eid']?>&t=<?=$_GET['t']?>'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
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
								document.getElementById('already_exit').style.display = "block";
								document.getElementById('USER_ID').value = "";
								return false;
							}else{
								document.getElementById('already_exit').style.display = "none";
								document.getElementById('USER_ID').readOnly 		  = true;
							}
						}
					});
				}
			});	
		}
	</script>

</body>

</html>
