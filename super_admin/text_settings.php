<? require_once("../global/config.php");
require_once("../language/text_settings.php");

if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("select * from S_TEXT_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$TEXT_SETTINGS = $_POST;
	if($res->RecordCount() == 0){
		$TEXT_SETTINGS['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$TEXT_SETTINGS['CREATED_BY'] = $_SESSION['PK_USER'];
		$TEXT_SETTINGS['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_TEXT_SETTINGS', $TEXT_SETTINGS, 'insert');
		$PK_TEXT_SETTINGS = $db->insert_ID();
	} else {
		$TEXT_SETTINGS['EDITED_BY'] = $_SESSION['PK_USER'];
		$TEXT_SETTINGS['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_TEXT_SETTINGS', $TEXT_SETTINGS, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_TEXT_SETTINGS = $_GET['id'];
	}
	
}
$res = $db->Execute("select * from S_TEXT_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$SID 		= $res->fields['SID'];
$TOKEN 		= $res->fields['TOKEN'];
$FROM_NO 	= $res->fields['FROM_NO'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewTOKEN" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=TEXT_SETTINGS_PAGE_TITLE ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=TEXT_SETTINGS_PAGE_TITLE ?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<? if($msg != ''){ ?>
										<div class="form-group">
											<label for="input-text" class="col-sm-2 control-label"></label>
											<div class="col-sm-10" style="color:red">
												<?=$msg?>
											</div>
										</div>
									<? } ?>
								
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group focused" id="SID_LBL">
											<input type="text" class="form-control required-entry" id="SID" name="SID" value="<?=$SID?>" >
											<span class="bar"></span> 
											<label for="SID"><?=SID1?></label>
										</div>
									</div>
								
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group focused" id="TOKEN_LBL">
											<input type="text" class="required-entry form-control" id="TOKEN" name="TOKEN" value="<?=$TOKEN?>" >
											<span class="bar"></span> 
											<label for="TOKEN"><?=TOKEN?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group focused" id="FROM_NO_LBL">
											<input type="text" class="required-entry form-control" id="FROM_NO" name="FROM_NO" value="<?=$FROM_NO?>" >
											<span class="bar"></span> 
											<label for="FROM_NO"><?=FROM_NO?></label>
										</div>
									</div>
								
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										
										<div class="col-9 col-sm-9">
											<button type="submit" class="btn waves-effect waves-light btn-info">Save</button>
											<button type="button" onclick="window.location.href='index'"  class="btn waves-effect waves-light btn-dark">Cancel</button>
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

	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		/*jQuery(document).ready(function($) {
			document.getElementById('SID_LBL').classList.add("focused");
			document.getElementById('TOKEN_LBL').classList.add("focused");
			document.getElementById('FROM_NO_LBL').classList.add("focused");
		});*/
	</script>

</body>

</html>