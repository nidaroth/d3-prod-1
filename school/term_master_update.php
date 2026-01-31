<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/term_master.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	if($_POST['UPDATE_TYPE'] == 1){
		//ACTIVE
		$ids = explode(",",$_GET['id']);
		foreach($ids as $id) {
			$TERM_MASTER['ACTIVE'] = $_POST['UPDATE_VALUE'];
			db_perform('S_TERM_MASTER', $TERM_MASTER, 'update'," PK_TERM_MASTER = '$id' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
	} else if($_POST['UPDATE_TYPE'] == 2){
		//ALLOW_ONLINE_ENROLLMENT
		$ids = explode(",",$_GET['id']);
		foreach($ids as $id) {
			$TERM_MASTER['ALLOW_ONLINE_ENROLLMENT'] = $_POST['UPDATE_VALUE'];
			db_perform('S_TERM_MASTER', $TERM_MASTER, 'update'," PK_TERM_MASTER = '$id' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
	} else if($_POST['UPDATE_TYPE'] == 3){
		//CAMPUS
		$ids = explode(",",$_GET['id']);
		foreach($ids as $id) {
			$res = $db->Execute("SELECT PK_TERM_MASTER_CAMPUS FROM S_TERM_MASTER_CAMPUS WHERE PK_TERM_MASTER = '$id' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$_POST[UPDATE_VALUE]' "); 
			if($res->RecordCount() == 0) {
				$TERM_MASTER_CAMPUS['PK_TERM_MASTER']   = $id;
				$TERM_MASTER_CAMPUS['PK_CAMPUS'] 		= $_POST['UPDATE_VALUE'];
				$TERM_MASTER_CAMPUS['PK_ACCOUNT'] 		= $_SESSION['PK_ACCOUNT'];
				$TERM_MASTER_CAMPUS['CREATED_BY']  		= $_SESSION['PK_USER'];
				$TERM_MASTER_CAMPUS['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_TERM_MASTER_CAMPUS', $TERM_MASTER_CAMPUS, 'insert');
			}
		}
	} else if($_POST['UPDATE_TYPE'] == 4){
		//GROUP
		$ids = explode(",",$_GET['id']);
		foreach($ids as $id) {
			$TERM_MASTER['TERM_GROUP'] = $_POST['UPDATE_VALUE'];
			db_perform('S_TERM_MASTER', $TERM_MASTER, 'update'," PK_TERM_MASTER = '$id' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
	} else if($_POST['UPDATE_TYPE'] == 5){
		//LMS_ACTIVE
		$ids = explode(",",$_GET['id']);
		foreach($ids as $id) {
			$TERM_MASTER['LMS_ACTIVE'] = $_POST['UPDATE_VALUE'];
			db_perform('S_TERM_MASTER', $TERM_MASTER, 'update'," PK_TERM_MASTER = '$id' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
	} ?>
	<script type="text/javascript">window.opener.refresh_win(this)</script>
<? }
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
	<title><?=UPDATE.' '.TERM_MASTER_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-3 align-self-center">
                        <h4 class="text-themecolor">
							<?=UPDATE.' '.TERM_MASTER_PAGE_TITLE?>
						</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<form class="floating-labels" method="post" name="form1" id="form1" >
									<div class="row">
										<div class="col-6 form-group" >
											<select id="UPDATE_TYPE" name="UPDATE_TYPE" class="form-control required-entry" onchange="get_values(this.value)" >
												<option value="" >Select Update Type</option>
												<option value="1" ><?=ACTIVE ?></option>
												<option value="2" ><?=ALLOW_ONLINE_ENROLLMENT?></option>
												<option value="3" ><?=CAMPUS?></option>
												<option value="4" ><?=GROUP?></option>
												<option value="5" ><?=LMS_ACTIVE?></option>
											</select>
										</div>
										
										<div class="col-6 form-group" id="UPDATE_VALUE_DIV" >
											<select id="UPDATE_VALUE" name="UPDATE_VALUE" class="form-control required-entry" >
												<option value="" >Select Value</option>
											</select>
										</div>
									</div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=UPDATE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="javascript:window.close()" ><?=CANCEL?></button>
												
											</div>
										</div>
									</div>
								</form>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>

	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	var form1 = new Validation('form1');
	
	function get_values(val){
		jQuery(document).ready(function($) { 
			var data  = 'type='+val+'&campus=<?=$_GET['campus']?>';
			var value = $.ajax({
				url: "ajax_get_term_update_value",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {
					document.getElementById('UPDATE_VALUE_DIV').innerHTML = data;
				}		
			}).responseText;
		});
	}
	</script>
</body>

</html>