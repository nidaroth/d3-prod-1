<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/pdf_template.php");
require_once("check_access.php");

if(check_access('SETUP_COMMUNICATION') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$PK_CAMPUS_ARR = $_POST['PK_CAMPUS'];
	unset($_POST['PK_CAMPUS']);
	
	$PDF_TEMPLATE = $_POST;
	if($_GET['id'] == ''){
		$PDF_TEMPLATE['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$PDF_TEMPLATE['CREATED_BY']  = $_SESSION['PK_USER'];
		$PDF_TEMPLATE['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_PDF_TEMPLATE', $PDF_TEMPLATE, 'insert');
		$PK_PDF_TEMPLATE = $db->Insert_ID();
		
	} else {
		$PDF_TEMPLATE['EDITED_BY'] = $_SESSION['PK_USER'];
		$PDF_TEMPLATE['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_PDF_TEMPLATE', $PDF_TEMPLATE, 'update'," PK_PDF_TEMPLATE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$PK_PDF_TEMPLATE = $_GET['id'];
	}
	
	foreach($PK_CAMPUS_ARR as $PK_CAMPUS){
		$res = $db->Execute("SELECT PK_PDF_TEMPLATE_CAMPUS FROM S_PDF_TEMPLATE_CAMPUS WHERE PK_PDF_TEMPLATE = '$PK_PDF_TEMPLATE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$PK_CAMPUS' "); 
		if($res->RecordCount() == 0) {
			$TEMPLATE_CAMPUS['PK_ACCOUNT'] 		= $_SESSION['PK_ACCOUNT'];
			$TEMPLATE_CAMPUS['PK_PDF_TEMPLATE'] = $PK_PDF_TEMPLATE;
			$TEMPLATE_CAMPUS['PK_CAMPUS'] 		= $PK_CAMPUS;
			$TEMPLATE_CAMPUS['CREATED_BY']  	= $_SESSION['PK_USER'];
			$TEMPLATE_CAMPUS['CREATED_ON'] 		= date("Y-m-d H:i");
			db_perform('S_PDF_TEMPLATE_CAMPUS', $TEMPLATE_CAMPUS, 'insert');
			$PK_PDF_TEMPLATE_CAMPUS_ARR[] = $db->insert_ID();
		} else
			$PK_PDF_TEMPLATE_CAMPUS_ARR[] = $res->fields['PK_PDF_TEMPLATE_CAMPUS'];
	}
	
	$cond = "";
	if(!empty($PK_PDF_TEMPLATE_CAMPUS_ARR))
		$cond = " AND PK_PDF_TEMPLATE_CAMPUS NOT IN (".implode(",",$PK_PDF_TEMPLATE_CAMPUS_ARR).") ";
		
	$db->Execute("DELETE FROM S_PDF_TEMPLATE_CAMPUS WHERE PK_PDF_TEMPLATE = '$PK_PDF_TEMPLATE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond"); 
	
	
	header("location:manage_pdf_template");
}
if($_GET['id'] == ''){
	$TEMPLATE_NAME 		= '';
	$PRINT_ORIENTATION 	= 'P';
	$CONTENT			= '';
	$ACTIVE	 			= '';
	
} else {
	$res = $db->Execute("SELECT * FROM S_PDF_TEMPLATE WHERE PK_PDF_TEMPLATE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_pdf_template");
		exit;
	}
	
	$TEMPLATE_NAME 	 	= $res->fields['TEMPLATE_NAME'];
	$PRINT_ORIENTATION 	= $res->fields['PRINT_ORIENTATION'];
	$CONTENT 		 	= $res->fields['CONTENT'];
	$ACTIVE  		 	= $res->fields['ACTIVE'];
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
	<title><?=MAIL_TEMPLATE_PAGE_TITLE?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=MAIL_TEMPLATE_PAGE_TITLE?> </h4>
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
												<input type="text" class="form-control required-entry" id="TEMPLATE_NAME" name="TEMPLATE_NAME" value="<?=$TEMPLATE_NAME?>" >
												<span class="bar"></span>
												<label for="TEMPLATE_NAME"><?=TEMPLATE_NAME?></label>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PRINT_ORIENTATION" name="PRINT_ORIENTATION" class="form-control required-entry" >
													<option value="" ></option>
													<option value="L" <? if($PRINT_ORIENTATION == 'L') echo "selected"; ?> >Landscape</option>
													<option value="P" <? if($PRINT_ORIENTATION == 'P') echo "selected"; ?> >Portrait</option>
												</select>
												<span class="bar"></span>
												<label for="PRINT_ORIENTATION"><?=PRINT_ORIENTATION?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
										<div class="col-12 col-sm-12 focused">
											<span class="bar"></span> 
											<label for="CAMPUS"><?=CAMPUS?></label>
										</div>
										<div class="form-group col-12 col-sm-6">
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { 
													$selected = '';
													$PK_CAMPUS = $res_type->fields['PK_CAMPUS'];
													$res = $db->Execute("select PK_PDF_TEMPLATE_CAMPUS FROM S_PDF_TEMPLATE_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_PDF_TEMPLATE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
													if($res->RecordCount() > 0 || ($res_type->RecordCount() == 1 && $_GET['id'] == '')) //Ticket #849 
														$selected = 'selected'; ?>
													<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" <?=$selected ?> ><?=$res_type->fields['CAMPUS_CODE'] ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-6">
											<button type="button" onclick="show_tags()" class="btn waves-effect waves-light btn-info"><?=TAGS?></button>
										</div>
									</div>
									
									<div class="row">
                                        <div class="col-md-12">
											<div class="row">
												<div class="col-md-12">
													<?=CONTENT?>
												</div>
												<div class="col-md-12">
													<textarea class="form-control required-entry rich" rows="2" id="CONTENT" name="CONTENT"><?=$CONTENT?></textarea>
												</div>
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
													<label class="custom-control-label" for="customRadio11"><?=YES?></label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="customRadio22"><?=NO?></label>
												</div>
											</div>
										</div>
									</div>
									<? } ?>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_pdf_template'" ><?=CANCEL?></button>
												
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
				toolbar1: 'bold italic | alignleft aligncenter alignright alignjustify | fontsizeselect | bullist numlist outdent indent | forecolor backcolor',	
				fontsize_formats: "8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 17pt 18pt 19pt 20pt",
				paste_data_images: false,
				height: 400,
			});
		});
		
		function show_tags(){
			var w = 1000;
			var h = 700;
			// var id = common_id;
			var left = (screen.width/2)-(w/2);
			var top = (screen.height/2)-(h/2);
			var parameter = 'toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,width='+w+', height='+h+', top='+top+', left='+left;
			window.open('show_document_tags','',parameter);
			return false;
		}
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
