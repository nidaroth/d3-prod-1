<? require_once("../global/config.php"); 

if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	/*
	$CUSTOM_QUERY = $_POST;
	if($_GET['id'] == ''){
		$CUSTOM_QUERY['DATE_CREATED']  = date("Y-m-d H:i");
		db_perform('M_CUSTOM_QUERY', $CUSTOM_QUERY, 'insert');
	} else {
		db_perform('M_CUSTOM_QUERY', $CUSTOM_QUERY, 'update'," PK_CUSTOM_QUERY = '$_GET[id]'");
	}
	header("location:accounts?id=".$_GET['s_id'].'&tab=customQueriesTab');
	*/
}
if($_GET['id'] == ''){
	$CUSTOM_NAME 			= '';
	$EXTERNAL_DESCRIPTION 	= '';
	$INTERNAL_DESCRIPTION 	= '';
	$CUSTOM_QUERY 			= '';
	
} else {
	$res = $db->Execute("SELECT * FROM M_CUSTOM_QUERY WHERE PK_CUSTOM_QUERY = '$_GET[id]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_account");
		exit;
	}
	
	$CUSTOM_NAME 			= $res->fields['CUSTOM_NAME'];
	$EXTERNAL_DESCRIPTION 	= $res->fields['EXTERNAL_DESCRIPTION'];
	$INTERNAL_DESCRIPTION 	= $res->fields['INTERNAL_DESCRIPTION'];
	$CUSTOM_QUERY 			= $res->fields['CUSTOM_QUERY'];
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
	<title>Custom Query | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo "Add"; else echo "Edit"; ?> Custom Query </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="CUSTOM_NAME" name="CUSTOM_NAME" value="<?=$CUSTOM_NAME?>" >
												<span class="bar"></span>
												<label for="CUSTOM_NAME">Name</label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-12">
											<div class="form-group m-b-40">
												<textarea class="form-control" id="INTERNAL_DESCRIPTION" name="INTERNAL_DESCRIPTION" style="height:150px" ><?=$INTERNAL_DESCRIPTION?></textarea>
												<span class="bar"></span>
												<label for="INTERNAL_DESCRIPTION">Internal Description</label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-12">
											<div class="form-group m-b-40">
												<textarea class="form-control" id="EXTERNAL_DESCRIPTION" name="EXTERNAL_DESCRIPTION" style="height:150px" ><?=$EXTERNAL_DESCRIPTION?></textarea>
												<span class="bar"></span>
												<label for="EXTERNAL_DESCRIPTION">External Description</label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-12">
											<div class="form-group m-b-40">
												<textarea class="form-control" id="CUSTOM_QUERY" name="CUSTOM_QUERY" style="height:150px" ><?=$CUSTOM_QUERY?></textarea>
												<span class="bar"></span>
												<label for="CUSTOM_QUERY">Custom Query</label>
											</div>
										</div>
                                    </div>
						
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<!--<button type="submit" class="btn waves-effect waves-light btn-info">Submit</button> -->
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='accounts?id=<?=$_GET['s_id']?>&tab=customQueriesTab'" >Back</button>
												
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
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>

</body>

</html>