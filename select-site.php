<? require_once("global/config.php");
$msg = '';
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	if($_POST['HID'] == 1)
		$_SESSION['FOLDER'] = 'school/';
	else if($_POST['HID'] == 2)
		$_SESSION['FOLDER'] = 'instructor/';
	header("location:".$_SESSION['FOLDER']."index");
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Select Site | <?=$title?></title>
	
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
                    <form class="form-horizontal form-material" method="post" id="form1" name="form1" action="">
						<h3 class="text-center m-b-20">Select Site</h3>
						<div class="form-group ">
							<div class="col-12 col-sm-12">
								<button type="button" onclick="set_val(1)" class="btn btn-success btn-block" style="max-width:100%;background-color: #215CA0;" >Diamond</button>
							</div>
						</div>	
						<div class="form-group ">
							<div class="col-12 col-sm-12">
								<button type="button" onclick="set_val(2)" class="btn btn-success btn-block" style="max-width:100%;background-color: #215CA0;" >Instructor Portal</button>
							</div>
						</div>
						<input type="hidden" name="HID" id="HID" value="" >
                    </form>
                </div>
            </div>
        </div>
    </section>
	<? require_once("footer.php"); ?>
	<? require_once("js.php"); ?>
	 <script type="text/javascript">
		function set_val(val){
			document.getElementById('HID').value = val
			document.form1.submit()
		}
    </script>
</body>
	
</html>