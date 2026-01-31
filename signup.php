<? require_once("global/config.php"); ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Sign Up | <?=$title?></title>
	
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
		<? require_once("menu.php"); ?>
        <div class="login-register" style="background-image:url(backend_assets/images/background/login-register.jpg);">
            <div class="login-box card">
                <div class="card-body">
                    <form class="form-horizontal form-material" method="post" id="loginform" name="loginform" action="">
                        <h3 class="text-center m-b-20">Sign Up</h3>
						
                        <div class="form-group ">
                            <div class="col-xs-12">
                                <input class="form-control required-entry" id="EMAIL" name="EMAIL" type="text" placeholder="Email">
							</div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <input class="form-control required-entry" id="PASSWORD" name="PASSWORD" type="password" autocomplete="new-password" placeholder="Password">
							</div>
                        </div>
                       
                        <div class="form-group text-center">
                            <div class="col-xs-12 p-b-20">
                                <button class="btn btn-block btn-lg btn-info btn-rounded" type="submit">Sign Up</button>
                            </div>
                        </div>
						
                        <div class="form-group m-b-0">
                            <div class="col-sm-12 text-center">
                                Already have an account? <a href="signin" class="text-info m-l-5"><b>Sign in</b></a>
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