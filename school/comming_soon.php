<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
	header("location:../index");
	exit;
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
	<title>Comming Soon | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<? if($_GET['id'] == 1) echo MNU_STUDENT;
							else if($_GET['id'] == 2) echo MNU_FINANCIAL_AID; 
							else if($_GET['id'] == 3) echo MNU_REGISTRAR;
							else if($_GET['id'] == 4) echo MNU_PLACEMENT;
							else if($_GET['id'] == 5) echo MNU_ACCOUNTING;
							?>
						</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<br /><br /><br /><br /><br /><br />
										<center>Comming Soon</center>
										<br /><br /><br /><br /><br /><br />
									</div>
								</div>
								
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>

</body>

</html>