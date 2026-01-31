<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/pdf_footer.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$PK_CAMPUS_ARR = $_POST['PK_CAMPUS'];
	unset($_POST['PK_CAMPUS']);
	
	$PDF_FOOTER 			= $_POST;
	$PDF_FOOTER['BOLD'] 	= $_POST['BOLD'];
	$PDF_FOOTER['ITALIC'] 	= $_POST['ITALIC'];
	
	if($_GET['id'] == ''){
		$PDF_FOOTER['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$PDF_FOOTER['CREATED_BY']  = $_SESSION['PK_USER'];
		$PDF_FOOTER['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_PDF_FOOTER', $PDF_FOOTER, 'insert');
		$PK_PDF_FOOTER = $db->Insert_ID();
	} else {
		$PK_PDF_FOOTER = $_GET['id'];
		$PDF_FOOTER['EDITED_BY'] = $_SESSION['PK_USER'];
		$PDF_FOOTER['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_PDF_FOOTER', $PDF_FOOTER, 'update'," PK_PDF_FOOTER = '$PK_PDF_FOOTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ");
	}
	
	foreach($PK_CAMPUS_ARR as $PK_CAMPUS){
		$res = $db->Execute("SELECT PK_PDF_FOOTER_CAMPUS FROM S_PDF_FOOTER_CAMPUS WHERE PK_PDF_FOOTER = '$PK_PDF_FOOTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$PK_CAMPUS' "); 
		if($res->RecordCount() == 0) {
			$PDF_FOOTER_CAMPUS['PK_ACCOUNT'] 	= $_SESSION['PK_ACCOUNT'];
			$PDF_FOOTER_CAMPUS['PK_PDF_FOOTER'] = $PK_PDF_FOOTER;
			$PDF_FOOTER_CAMPUS['PK_CAMPUS'] 	= $PK_CAMPUS;
			$PDF_FOOTER_CAMPUS['CREATED_BY']  	= $_SESSION['PK_USER'];
			$PDF_FOOTER_CAMPUS['CREATED_ON'] 	= date("Y-m-d H:i");
			db_perform('S_PDF_FOOTER_CAMPUS', $PDF_FOOTER_CAMPUS, 'insert');
			$PK_PDF_FOOTER_CAMPUS_ARR[] = $db->insert_ID();
		} else
			$PK_PDF_FOOTER_CAMPUS_ARR[] = $res->fields['PK_PDF_FOOTER_CAMPUS'];
	}
	
	$cond = "";
	if(!empty($PK_PDF_FOOTER_CAMPUS_ARR))
		$cond = " AND PK_PDF_FOOTER_CAMPUS NOT IN (".implode(",",$PK_PDF_FOOTER_CAMPUS_ARR).") ";
		
	$db->Execute("DELETE FROM S_PDF_FOOTER_CAMPUS WHERE PK_PDF_FOOTER = '$PK_PDF_FOOTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond"); 
	
	header("location:manage_pdf_footer");
}
if($_GET['id'] == ''){
	$PDF_FOR	= '';
	$NAME 		= '';
	$FONT_NAME	= 'helvetica';
	$FONT_SIZE 	= 7;
	$BOLD 		= '';
	$ITALIC 	= '';
	$ALIGNMENT 	= 'L';
	$CONTENT 	= '';
	$ACTIVE	 	= '';
	$FOOTER_LOC = 0;
} else {
	
	$res = $db->Execute("SELECT * FROM S_PDF_FOOTER WHERE PK_PDF_FOOTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	if($res->RecordCount() == 0){
		header("location:manage_pdf_footer");
		exit;
	}
	
	$PDF_FOR 	= $res->fields['PDF_FOR'];
	$NAME 		= $res->fields['NAME'];
	$FONT_NAME	= $res->fields['FONT_NAME'];
	$FONT_SIZE 	= $res->fields['FONT_SIZE'];
	$BOLD 		= $res->fields['BOLD'];
	$ITALIC 	= $res->fields['ITALIC'];
	$ALIGNMENT 	= $res->fields['ALIGNMENT'];
	$CONTENT 	= $res->fields['CONTENT'];
	$FOOTER_LOC	= $res->fields['FOOTER_LOC'];
	$ACTIVE  	= $res->fields['ACTIVE'];
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
	<title><?=PDF_FOOTER_PAGE_TITLE?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else EDIT; ?> <?=PDF_FOOTER_PAGE_TITLE?></h4>
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
												<input type="text" class="form-control required-entry" id="NAME" name="NAME" value="<?=$NAME?>" >
												<span class="bar"></span>
												<label for="NAME"><?=NAME?></label>
											</div>
										</div>
										
										<div class="col-md-6">
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { 
													$selected = '';
													$PK_CAMPUS = $res_type->fields['PK_CAMPUS'];
													$res = $db->Execute("select PK_PDF_FOOTER_CAMPUS FROM S_PDF_FOOTER_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_PDF_FOOTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
													if($res->RecordCount() > 0 || ($res_type->RecordCount() == 1 && $_GET['id'] == '')) //Ticket #849 
														$selected = 'selected'; ?>
													<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" <?=$selected ?> ><?=$res_type->fields['CAMPUS_CODE'] ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-40">
												<select id="PDF_FOR" name="PDF_FOR" class="form-control required-entry" >
													<option></option>
													<option value="17" <? if($PDF_FOR == 17) echo "selected"; ?> >Attendance Course Offering 2 Week</option><!-- Ticket # 1344 -->
													<option value="12" <? if($PDF_FOR == 12) echo "selected"; ?> >Attendance Daily Sign In Sheet</option><!-- Ticket # 1344 -->
													<option value="14" <? if($PDF_FOR == 14) echo "selected"; ?> >Balance Sheet</option><!-- Ticket # 1626 -->
													<option value="21" <? if($PDF_FOR == 21) echo "selected"; ?> >Course Offering Grade Book Transcript</option><!-- DIAM-2340 -->
													<option value="18" <? if($PDF_FOR == 18) echo "selected"; ?> >Financial Aid Estimate</option><!-- DIAM-1409 -->
													<option value="13" <? if($PDF_FOR == 13) echo "selected"; ?> >Ledger Worksheet</option><!-- Ticket # 1709 -->
													<option value="2" <? if($PDF_FOR == 2) echo "selected"; ?> >Offer Letter</option>
													<option value="7" <? if($PDF_FOR == 7) echo "selected"; ?> >Payments Due</option>
													<option value="9" <? if($PDF_FOR == 9) echo "selected"; ?> >Program Grade Book Progress Report Card</option> <!-- Ticket # 1183 -->
													<option value="19" <? if($PDF_FOR == 19) echo "selected"; ?> >Program Grade Book Transcript</option> <!-- DIAM-2018 -->
													<option value="3" <? if($PDF_FOR == 3) echo "selected"; ?> >Report Card</option>
													<option value="22" <? if($PDF_FOR == 22) echo "selected"; ?> >Satisfactory Progress Report Card</option><!-- DIAM-2340 -->
													<option value="8" <? if($PDF_FOR == 8) echo "selected"; ?> >Student Invoice</option>
													<option value="1" <? if($PDF_FOR == 1) echo "selected"; ?> >Student Schedule</option>
													<option value="15" <? if($PDF_FOR == 15) echo "selected"; ?> >Student Schedule with Books</option>
													<option value="4" <? if($PDF_FOR == 4) echo "selected"; ?> >Student Transcript</option>
													<option value="11" <? if($PDF_FOR == 11) echo "selected"; ?> >Student Transcript - FA Units</option><!-- Ticket # 1551 -->
													<option value="16" <? if($PDF_FOR == 16) echo "selected"; ?> >Student Transcript - Transcript Group</option><!-- Ticket # 1603 -->
													<option value="5" <? if($PDF_FOR == 5) echo "selected"; ?> >Student Transcript List</option>
													<option value="10" <? if($PDF_FOR == 10) echo "selected"; ?> >Student Transcript List - Numeric Grade</option> <!-- Ticket # 1234 -->
													<option value="6" <? if($PDF_FOR == 6) echo "selected"; ?> >Unofficial Student Transcript</option>
													<option value="20" <? if($PDF_FOR == 20) echo "selected"; ?> >Student SAP</option> <!-- DIAM-2043 -->
												</select>
												<span class="bar"></span>
												<label for="PDF_FOR"><?=REPORT_NAME?></label>
											</div>
										</div>
										
										<div class="col-md-1">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="FOOTER_LOC" name="FOOTER_LOC" value="<?=$FOOTER_LOC?>" >
												<span class="bar"></span>
												<label for="FOOTER_LOC"><?=FOOTER_POSITION?></label>
											</div>
										</div>
										<!--
										<div class="col-md-1">
											<div class="form-group m-b-40">
												<select id="FONT_NAME" name="FONT_NAME" class="form-control required-entry" >
													<option></option>
													<option value="courier" <? if($FONT_NAME == "courier") echo "selected"; ?> >Courier</option>
													<option value="helvetica" <? if($FONT_NAME == "helvetica") echo "selected"; ?> >Helvetica</option>
													<option value="times" <? if($FONT_NAME == "times") echo "selected"; ?> >Times</option>
												</select>
												<span class="bar"></span>
												<label for="FONT_NAME"><?=FONT?></label>
											</div>
										</div>
										
										<div class="col-md-1">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="FONT_SIZE" name="FONT_SIZE" value="<?=$FONT_SIZE?>" >
												<span class="bar"></span>
												<label for="FONT_SIZE"><?=FONT_SIZE?></label>
											</div>
										</div>
										
										<div class="col-md-1">
											<div class="form-group m-b-40">
												<select id="ALIGNMENT" name="ALIGNMENT" class="form-control required-entry" >
													<option></option>
													<option value="L" <? if($ALIGNMENT == "L") echo "selected"; ?> >Left</option>
													<option value="R" <? if($ALIGNMENT == "R") echo "selected"; ?> >Right</option>
													<option value="J" <? if($ALIGNMENT == "J") echo "selected"; ?> >Justified</option>
													<option value="C" <? if($ALIGNMENT == "C") echo "selected"; ?> >Center</option>
												</select>
												<span class="bar"></span>
												<label for="ALIGNMENT"><?=ALIGNMENT?></label>
											</div>
										</div>
										
										<div class="col-md-1 form-group custom-control custom-checkbox form-group" style="padding-right: 5px;text-align: center;max-width: 13%;" >
											<input type="checkbox" class="custom-control-input" id="BOLD" name="BOLD" <? if($BOLD == 1) echo "checked"; ?> value="1" >
											<label class="custom-control-label" for="BOLD"><?=BOLD?></label>
										</div>
										
										<div class="col-md-1 form-group custom-control custom-checkbox form-group" style="padding-right: 5px;text-align: center;max-width: 13%;" >
											<input type="checkbox" class="custom-control-input" id="ITALIC" name="ITALIC" <? if($ITALIC == 1) echo "checked"; ?> value="1" >
											<label class="custom-control-label" for="ITALIC"><?=ITALIC?></label>
										</div>-->
                                    </div>
									
									<div class="row">
                                        <div class="col-md-12">
											<div class="form-group m-b-40">
												<textarea id="CONTENT" name="CONTENT" class="form-control required-entry rich" rows="10"><?=$CONTENT?></textarea>
												<span class="bar"></span>
												<label for="DESCRIPTION"><?=DESCRIPTION?></label>
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
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_pdf_footer'" ><?=CANCEL?></button>
												
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

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
	});
	</script>
	
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
					'advlist hr pagebreak',
					'wordcount code',
					'nonbreaking save directionality',
					'template paste textcolor colorpicker textpattern '
				],
				toolbar1: 'bold italic | alignleft aligncenter alignright alignjustify | fontsizeselect | forecolor backcolor',	
				fontsize_formats: "8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 17pt 18pt 19pt 20pt",
				paste_data_images: false,
				height: 400,
			});
		});
	</script>
</body>

</html>
