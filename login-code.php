<? require_once("global/config.php");

$msg = '';

if (!empty($_POST)) {
	if ($_POST['MFA_CODE'] == $_SESSION['MFA_CODE']) {
		$USER['LAST_LOGGED_IN_IP'] = $_SESSION['TEMP_NEW_IP'];
		db_perform('Z_USER', $USER, 'update', " PK_USER = '$_SESSION[TEMP_PK_USER]' ");
		$_SESSION['TEMP_PK_USER_1'] = $_SESSION['TEMP_PK_USER'];

		$_SESSION['TEMP_NEW_IP'] 		= '';
		$_SESSION['TEMP_PK_USER_TYPE'] 	= '';
		$_SESSION['TEMP_PK_USER'] 		= '';
		$_SESSION['MFA_CODE'] 			= '';

		unset($_SESSION['TEMP_NEW_IP']);
		unset($_SESSION['TEMP_PK_USER_TYPE']);
		unset($_SESSION['TEMP_PK_USER']);
		unset($_SESSION['MFA_CODE']);

		header("location:signin");
	} else {
		$msg = 'Invalid MFA Code';
	}
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>MFA Code | <?= $title ?></title>

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
		<? //require_once("menu.php"); 
		?>
		<div class="login-register" style="background-image:url(backend_assets/images/background/login-register.jpg);">
			<div class="login-box card">
				<div class="card-body">
					<form class="form-horizontal form-material" method="post" id="loginform" name="loginform" action="">
						<h3 class="text-center m-b-20">MFA Code</h3>

						<? if ($msg != '') { ?>
							<div class="form-group ">
								<div class="col-xs-12" style="color:red">
									<?= $msg ?>
								</div>
							</div>
						<? } ?>

						<div class="form-group ">
							<div class="col-xs-12">
								<? //$_SESSION['MFA_CODE'] 
								?>
								<input class="form-control required-entry" id="MFA_CODE" name="MFA_CODE" type="text" placeholder="MFA Code">
							</div>
						</div>

						<div class="form-group text-center">
							<div class="col-xs-12 p-b-20">
								<input type="hidden" name="form_name" value="login">
								<button class="btn btn-block btn-lg btn-info btn-rounded" type="submit">Log In</button>
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