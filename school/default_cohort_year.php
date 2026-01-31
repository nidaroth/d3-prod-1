<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/default_cohort_year.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('SETUP_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$DEFAULT_COHORT_YEAR = $_POST;
	if($DEFAULT_COHORT_YEAR['BEGIN_DATE'] != '')
		$DEFAULT_COHORT_YEAR['BEGIN_DATE'] = date("Y-m-d",strtotime($DEFAULT_COHORT_YEAR['BEGIN_DATE']));
	if($DEFAULT_COHORT_YEAR['END_DATE'] != '')
		$DEFAULT_COHORT_YEAR['END_DATE'] = date("Y-m-d",strtotime($DEFAULT_COHORT_YEAR['END_DATE']));
		
	if($_GET['id'] == ''){
		$DEFAULT_COHORT_YEAR['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$DEFAULT_COHORT_YEAR['CREATED_BY']  = $_SESSION['ADMIN_PK_USER'];
		$DEFAULT_COHORT_YEAR['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_DEFAULT_COHORT_YEAR', $DEFAULT_COHORT_YEAR, 'insert');
	} else {
		$DEFAULT_COHORT_YEAR['EDITED_BY'] = $_SESSION['ADMIN_PK_USER'];
		$DEFAULT_COHORT_YEAR['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_DEFAULT_COHORT_YEAR', $DEFAULT_COHORT_YEAR, 'update'," PK_DEFAULT_COHORT_YEAR = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	header("location:manage_default_cohort_year");
}
if($_GET['id'] == ''){
	$DEFAULT_COHORT_YEAR 	= '';
	$BEGIN_DATE		= '';
	$END_DATE		= '';
	$ACTIVE	 		= '';
	
} else {
	$res = $db->Execute("SELECT * FROM S_DEFAULT_COHORT_YEAR WHERE PK_DEFAULT_COHORT_YEAR = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_default_cohort_year");
		exit;
	}
	
	$DEFAULT_COHORT_YEAR 	= $res->fields['DEFAULT_COHORT_YEAR'];
	$BEGIN_DATE 	= $res->fields['BEGIN_DATE'];
	$END_DATE 		= $res->fields['END_DATE'];
	$ACTIVE  		= $res->fields['ACTIVE'];

	if($BEGIN_DATE == '0000-00-00')
		$BEGIN_DATE = '';
	else
		$BEGIN_DATE = date("m/d/Y",strtotime($BEGIN_DATE));
		
	if($END_DATE == '0000-00-00')
		$END_DATE = '';
	else
		$END_DATE = date("m/d/Y",strtotime($END_DATE));
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
	<title><?=MNU_DEFAULT_COHORT_YEAR?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=MNU_DEFAULT_COHORT_YEAR?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row">
                                        <div class="col-md-2">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="DEFAULT_COHORT_YEAR" name="DEFAULT_COHORT_YEAR" value="<?=$DEFAULT_COHORT_YEAR?>" >
												<span class="bar"></span>
												<label for="DEFAULT_COHORT_YEAR">Default Cohort Year</label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-2">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry date" id="BEGIN_DATE" name="BEGIN_DATE" value="<?=$BEGIN_DATE?>" >
												<span class="bar"></span>
												<label for="BEGIN_DATE">Begin Date</label>
											</div>
										</div>
										<div class="col-md-2">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry date" id="END_DATE" name="END_DATE" value="<?=$END_DATE?>" >
												<span class="bar"></span>
												<label for="END_DATE">End Date</label>
											</div>
										</div>
                                    </div>
									
								
																		
									<? if($_GET['id'] != ''){ ?>
									<div class="row">
										<div class="col-md-6">
											<div class="row form-group">
												<div class="custom-control col-md-2"><?=ACTIVE?></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="customRadio11">Yes</label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="customRadio22">No</label>
												</div>
											</div>
										</div>
									</div>
									<? } ?>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_default_cohort_year'" ><?=CANCEL?></button>
												
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

	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});

		$("#BEGIN_DATE").datepicker({      
			autoclose: true,
		}).on('changeDate', function (selected) {
			var minDate = new Date(selected.date.valueOf());
			$('#END_DATE').datepicker('setStartDate', minDate);
		});

		$("#END_DATE").datepicker({     
			autoclose: true,
		}).on('changeDate', function (selected) {
				var minDate = new Date(selected.date.valueOf());
				$('#BEGIN_DATE').datepicker('setEndDate', minDate);
		});
	});
	</script>
</body>

</html>
