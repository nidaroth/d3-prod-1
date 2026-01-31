<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/dashboard.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_USER_TYPE'] != 3 ){
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
	<title><?=ANNOUNCEMENT?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=ANNOUNCEMENT?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<? $res = $db->Execute("SELECT * FROM Z_ANNOUNCEMENT WHERE ACTIVE = 1 AND PK_ANNOUNCEMENT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");  
										/* Ticket #1916 */
										if($res->fields['IMAGE'] != '') { 
											$extn 			= explode(".",$res->fields['IMAGE']);
											$iindex			= count($extn) - 1;
											$extension   	= strtolower($extn[$iindex]); 
											
											if($extension == 'jpg' || $extension == 'jpeg' || $extension == 'bmp'){ ?>
												<img src="<?=$res->fields['IMAGE']?>" style="max-width:100%" /><br /><br />
											<? } else { ?>
												<a href="<?=$res->fields['IMAGE']?>" target="_blank" >Click Here to view the Document</a>
										<?	}
										} 
										/* Ticket #1916 */
										if($_SESSION['PK_LANGUAGE'] == 2 )
											echo $res->fields['DESC_SPA']; 
										else 
											echo $res->fields['DESC_ENG']; ?>
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