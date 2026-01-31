<? require_once("global/config.php");
$CODE = $_GET['c'];
$msg  = '';
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);

	$result1 = $db->Execute("SELECT * FROM Z_USER where PK_USER = '$_SESSION[PK_USER]' AND ACTIVE='1'");
	if($result1->RecordCount() > 0){
		$salt =substr(strtr(base64_encode(openssl_random_pseudo_bytes(22)),'+','.'),0,22);
		$hash = crypt($_POST['PASSWORD'], '$2y$12$' . $salt);
	
		$USER_1['PASSWORD'] 			= $hash;
		$USER_1['RESET_PASSWORD'] 		= 0;
		$USER_1['FIRST_LOGIN'] 			= 0;
		$USER_1['PASSWORD_CHANGED_ON']  = date("Y-m-d");
		db_perform('Z_USER', $USER_1, 'update'," PK_USER = '$_SESSION[PK_USER]' ");
		
		if($_SESSION['SELECT_SITE'] == 1)
			header("location:select-site");
		else
			header("location:".$_SESSION['FOLDER']."index");
		exit;
	} else {
		$msg = 'Your Accouunt has been Blocked. Plase contact Admin';
	}
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Change Password | <?=$title?></title>
	
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
		<? require_once("menu.php");  ?>
        <div class="login-register" style="background-image:url(backend_assets/images/background/login-register.jpg);">
            <div class="login-box card">
                <div class="card-body">
                    <form class="form-horizontal form-material" method="post" id="loginform" name="loginform" action="">
                        <h3 class="text-center m-b-20">Change Password</h3>
						
						<? if($msg != ''){ ?>
						<div class="form-group ">
                            <div class="col-xs-12" style="color:red" >
								<?=$msg?>
								<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
							</div>
                        </div>
						<? } ?>
						
                        <div class="form-group">
                            <div class="col-xs-12">
                                <input class="form-control required-entry validate-admin-password" id="PASSWORD" name="PASSWORD" type="password" placeholder="New Password">
							</div>
                        </div>
						
						<div class="form-group">
                            <div class="col-xs-12">
                                <input class="form-control required-entry validate-cpassword" id="CONFIRM_PASS" name="CONFIRM_PASS" type="password" placeholder="Confirm Password">
							</div>
                        </div>
						
						 <div class="form-group text-center">
                            <div class="col-xs-12 p-b-20">
								<input type="hidden" name="form_name" value="login" >
                                <button class="btn btn-block btn-lg btn-info btn-rounded" type="submit">Change</button>
                            </div>
                        </div>
						
                    </form>
                </div>
            </div>
        </div>
    </section>
	<? require_once("footer.php"); ?>
	<? require_once("js.php"); ?>
   
    
	<script src="backend_assets/dist/js/validation_prototype.js"></script>
	<script src="backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('loginform');
	</script>
</body>

</html>