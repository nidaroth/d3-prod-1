<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/agreement_library.php");
require_once("check_access.php");

if(check_access('SETUP_COMMUNICATION') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$PK_CAMPUS_ARR = $_POST['PK_CAMPUS'];
	unset($_POST['PK_CAMPUS']);
	
	$AGREEMENT_LIBRARY = $_POST;
	if($_GET['id'] == ''){
		$AGREEMENT_LIBRARY['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$AGREEMENT_LIBRARY['CREATED_BY']  = $_SESSION['PK_USER'];
		$AGREEMENT_LIBRARY['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_AGREEMENT_LIBRARY', $AGREEMENT_LIBRARY, 'insert');
		$PK_AGREEMENT_LIBRARY = $db->Insert_ID();
	} else {
		$PK_AGREEMENT_LIBRARY = $_GET['id'];
		$AGREEMENT_LIBRARY['EDITED_BY'] = $_SESSION['PK_USER'];
		$AGREEMENT_LIBRARY['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_AGREEMENT_LIBRARY', $AGREEMENT_LIBRARY, 'update'," PK_AGREEMENT_LIBRARY = '$PK_AGREEMENT_LIBRARY' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
	
	foreach($PK_CAMPUS_ARR as $PK_CAMPUS){
		$res = $db->Execute("SELECT PK_AGREEMENT_LIBRARY_CAMPUS FROM S_AGREEMENT_LIBRARY_CAMPUS WHERE PK_AGREEMENT_LIBRARY = '$PK_AGREEMENT_LIBRARY' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$PK_CAMPUS' "); 
		if($res->RecordCount() == 0) {
			$TEMPLATE_RECIPIENTS['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
			$TEMPLATE_RECIPIENTS['PK_AGREEMENT_LIBRARY'] 	= $PK_AGREEMENT_LIBRARY;
			$TEMPLATE_RECIPIENTS['PK_CAMPUS'] 				= $PK_CAMPUS;
			$TEMPLATE_RECIPIENTS['CREATED_BY']  			= $_SESSION['PK_USER'];
			$TEMPLATE_RECIPIENTS['CREATED_ON'] 				= date("Y-m-d H:i");
			db_perform('S_AGREEMENT_LIBRARY_CAMPUS', $TEMPLATE_RECIPIENTS, 'insert');
			$PK_AGREEMENT_LIBRARY_CAMPUS_ARR[] = $db->insert_ID();
		} else
			$PK_AGREEMENT_LIBRARY_CAMPUS_ARR[] = $res->fields['PK_AGREEMENT_LIBRARY_CAMPUS'];
	}
	
	$cond = "";
	if(!empty($PK_AGREEMENT_LIBRARY_CAMPUS_ARR))
		$cond = " AND PK_AGREEMENT_LIBRARY_CAMPUS NOT IN (".implode(",",$PK_AGREEMENT_LIBRARY_CAMPUS_ARR).") ";
		
	$db->Execute("DELETE FROM S_AGREEMENT_LIBRARY_CAMPUS WHERE PK_AGREEMENT_LIBRARY = '$PK_AGREEMENT_LIBRARY' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond"); 
	
	header("location:manage_agreement_library");
}
if($_GET['id'] == ''){
	$NAME 	 = '';
	$CONTENT = '';
	$ACTIVE	 = '';
	
} else {
	$res = $db->Execute("SELECT * FROM S_AGREEMENT_LIBRARY WHERE PK_AGREEMENT_LIBRARY = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
	if($res->RecordCount() == 0){
		header("location:manage_agreement_library");
		exit;
	}
	
	$NAME 	 = $res->fields['NAME'];
	$CONTENT = $res->fields['CONTENT'];
	$ACTIVE  = $res->fields['ACTIVE'];
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
	<title><?=AGREEMENT_LIBRARY_PAGE_TITLE ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=AGREEMENT_LIBRARY_PAGE_TITLE ?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row">
                                        <div class="col-md-6">
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input id="NAME" name="NAME" type="text" class="form-control" value="<?=$NAME?>"   >
														<span class="bar"></span>
														<label for="NAME"><?=NAME?></label>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
                                        <div class="col-md-6">
											<div class="row">
												<div class="col-12 col-sm-6 focused">
													<span class="bar"></span> 
													<label for="CONTENT"><?=CONTENT?></label>
												</div>
												
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<textarea class="form-control rich" id="CONTENT" name="CONTENT"><?=$CONTENT?></textarea>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="col-12 col-sm-6 focused">
												<span class="bar"></span> 
												<label for="CAMPUS"><?=CAMPUS?></label>
											</div>
											
											<div class="col-12 col-sm-12 form-group row" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<div class="form-group col-12 col-sm-6">
														<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
															<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
															while (!$res_type->EOF) { 
																$selected = '';
																$PK_CAMPUS = $res_type->fields['PK_CAMPUS'];
																$res = $db->Execute("select PK_AGREEMENT_LIBRARY_CAMPUS FROM S_AGREEMENT_LIBRARY_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_AGREEMENT_LIBRARY = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
																if($res->RecordCount() > 0  || ($res_type->RecordCount() == 1 && $_GET['id'] == '')) //Ticket #849 
																	$selected = 'selected'; ?>
																<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" <?=$selected ?> ><?=$res_type->fields['CAMPUS_CODE'] ?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
												<?	$res_type->MoveNext();
												} ?>
											</div>
											
											<div class="row">
												<div class="col-12 col-sm-6 focused">
													<span class="bar"></span> 
													<label for="TAGS"><?=TAGS?></label>
												</div>
												<div class="col-md-12">
													<div class="form-group m-b-40">
														{Student Name}
													</div>
												</div>
											</div>
											
											<? if($_GET['id'] != ''){ ?>
											<div class="row">
												<div class="col-md-6">
													<div class="row form-group">
														<div class="custom-control col-md-4"><?=ACTIVE?></div>
														<div class="custom-control custom-radio col-md-3">
															<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="customRadio11"><?=YES?></label>
														</div>
														<div class="custom-control custom-radio col-md-3">
															<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
															<label class="custom-control-label" for="customRadio22"><?=NO?></label>
														</div>
													</div>
												</div>
											</div>
											<? } ?>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_agreement_library?t=<?=$_GET['t']?>'" ><?=CANCEL?></button>
												
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
	
	<!-- <script src="https://cdn.tiny.cloud/1/d6quzxl18kigwmmr6z03zgk3w47922rw1epwafi19cfnj00i/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script> -->
	<? require_once("../global/tiny-cloud.php"); ?>
	
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		jQuery(document).ready(function($) {
			tinymce.init({ 
				selector:'.rich',
				browser_spellcheck: true,
				menubar:false,
				statusbar: false,
				height: '300',
				plugins: [
					'advlist lists hr pagebreak',
					'wordcount code',
					'nonbreaking save table contextmenu directionality',
					'template paste textcolor colorpicker textpattern '
				],
				toolbar1: 'bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor',	
				paste_data_images: true,
				height: 400,
			});
		});
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
	});
	</script>
</body>

</html>
