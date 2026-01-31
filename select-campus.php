<? require_once("global/config.php");
$msg = '';
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	if(isset($_POST['PK_CAMPUS'])) {
		$_SESSION['PK_CAMPUS'] 	= $_POST['PK_CAMPUS'];
		
		$res_camp = $db->Execute("SELECT OFFICIAL_CAMPUS_NAME, PK_TIMEZONE FROM S_CAMPUS WHERE PK_CAMPUS = '$_SESSION[PK_CAMPUS]' ");
		$_SESSION['CAMPUS_NAME'] = $res_camp->fields['OFFICIAL_CAMPUS_NAME'];
		$_SESSION['PK_TIMEZONE'] = $res_camp->fields['PK_TIMEZONE'];
	}
	
	header("location:".$_SESSION['FOLDER']."index");
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Select Campus | <?=$title?></title>
	
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
                       
						<? if($msg != ''){ ?>
						<div class="form-group ">
                            <div class="col-xs-12" style="color:red" >
								<?=$msg?>
							</div>
                        </div>
						<? } ?>
						
						<? $result = $db->Execute("SELECT S_EMPLOYEE_CAMPUS.PK_CAMPUS,OFFICIAL_CAMPUS_NAME FROM S_EMPLOYEE_CAMPUS, S_CAMPUS WHERE PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' AND S_EMPLOYEE_CAMPUS.ACTIVE = 1 AND S_CAMPUS.PK_CAMPUS = S_EMPLOYEE_CAMPUS.PK_CAMPUS");
						if($result->RecordCount() > 1) { ?>
							<h3 class="text-center m-b-20">Select Campus</h3>
							<? while (!$result->EOF) { ?>
							<div class="form-group ">
								<div class="col-xs-12">
									<select id="PK_CAMPUS" name="PK_CAMPUS" class="form-control required-entry" >
										<? while (!$result->EOF) { ?>
											<option value="<?=$result->fields['PK_CAMPUS'] ?>" ><?=$result->fields['OFFICIAL_CAMPUS_NAME']?></option>
										<?	$result->MoveNext();
										} ?>
									</select>
								</div>
							</div>
							<?	$result->MoveNext();
							} 
						} ?>
						
						<div class="form-group ">
							<div class="col-xs-12">
								<button type="submit" class="btn btn-success btn-block">Submit</button>
							</div>
						</div>
                    </form>
                    <form class="form-horizontal" id="recoverform" action="index.html">
                        <div class="form-group ">
                            <div class="col-xs-12">
                                <h3>Recover Password</h3>
                                <p class="text-muted">Enter your Email and instructions will be sent to you! </p>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="col-xs-12">
                                <input class="form-control" type="text" required="" placeholder="Email"> </div>
                        </div>
                        <div class="form-group text-center m-t-20">
                            <div class="col-xs-12">
                                <button class="btn btn-primary btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
	<? require_once("footer.php"); ?>
	<? require_once("js.php"); ?>
  
</body>

</html>