<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$RELEASE_NOTES = $_POST;
	
	if($RELEASE_NOTES['RELEASE_NOTES_PUSHED_DATE'] != '')
		$RELEASE_NOTES['RELEASE_NOTES_PUSHED_DATE'] = date("Y-m-d",strtotime($RELEASE_NOTES['RELEASE_NOTES_PUSHED_DATE']));
		
	if($RELEASE_NOTES['PUSHED_TO_D3_DATE'] != '')
		$RELEASE_NOTES['PUSHED_TO_D3_DATE'] = date("Y-m-d",strtotime($RELEASE_NOTES['PUSHED_TO_D3_DATE']));
		
	$RELEASE_NOTES['PK_RELEASE_CATEGORY'] = implode(",",$_POST['PK_RELEASE_CATEGORY']);
		
	if($_GET['id'] == ''){
		$RELEASE_NOTES['CREATED_BY']  = $_SESSION['ADMIN_PK_USER'];
		$RELEASE_NOTES['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('Z_RELEASE_NOTES', $RELEASE_NOTES, 'insert');
	} else {
		$RELEASE_NOTES['EDITED_BY'] = $_SESSION['ADMIN_PK_USER'];
		$RELEASE_NOTES['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('Z_RELEASE_NOTES', $RELEASE_NOTES, 'update'," PK_RELEASE_NOTES = '$_GET[id]'");
	}
	header("location:manage_release_notes");
}
if($_GET['id'] == ''){
	$PK_RELEASE_CATEGORY_ARR	= array();
	$PK_RELEASE_TYPE 			= '';
	$PUSHED_TO_D3_DATE 			= '';
	$PROGRAMMING_NOTES 			= '';
	$LOCATION 					= '';
	$SUBJECT 					= '';
	$RELEASE_NOTES 				= '';
	$KNOWLEDGEBASE_URL			= '';
	$RELEASE_NOTES_PUSHED		= '';
	$RELEASE_NOTES_PUSHED_DATE 	= '';
		
} else {
	$res = $db->Execute("SELECT * FROM Z_RELEASE_NOTES WHERE PK_RELEASE_NOTES = '$_GET[id]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_release_notes");
		exit;
	}
	
	$PK_RELEASE_CATEGORY_ARR	= explode(",",$res->fields['PK_RELEASE_CATEGORY']);
	$PK_RELEASE_TYPE 			= $res->fields['PK_RELEASE_TYPE'];
	$PUSHED_TO_D3_DATE 			= $res->fields['PUSHED_TO_D3_DATE'];
	$PROGRAMMING_NOTES 			= $res->fields['PROGRAMMING_NOTES'];
	$LOCATION 					= $res->fields['LOCATION'];
	$SUBJECT 					= $res->fields['SUBJECT'];
	$RELEASE_NOTES 				= $res->fields['RELEASE_NOTES'];
	$KNOWLEDGEBASE_URL 			= $res->fields['KNOWLEDGEBASE_URL'];
	$RELEASE_NOTES_PUSHED 		= $res->fields['RELEASE_NOTES_PUSHED'];
	$RELEASE_NOTES_PUSHED_DATE 	= $res->fields['RELEASE_NOTES_PUSHED_DATE'];
	
	if($PUSHED_TO_D3_DATE != '0000-00-00')
		$PUSHED_TO_D3_DATE = date("m/d/Y",strtotime($PUSHED_TO_D3_DATE));
	else
		$PUSHED_TO_D3_DATE = '';
		
	if($RELEASE_NOTES_PUSHED_DATE != '0000-00-00')
		$RELEASE_NOTES_PUSHED_DATE = date("m/d/Y",strtotime($RELEASE_NOTES_PUSHED_DATE));
	else
		$RELEASE_NOTES_PUSHED_DATE = '';	
}
$current_date = date("m/d/Y");
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
	<title>Release Notes | <?=$title?></title>
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
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo "Add"; else echo "Edit"; ?> Release Notes </h4>
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
												<select id="PK_RELEASE_CATEGORY" name="PK_RELEASE_CATEGORY[]" multiple class="form-control" >
													<? $res_type = $db->Execute("select PK_RELEASE_CATEGORY,RELEASE_CATEGORY from M_RELEASE_CATEGORY WHERE ACTIVE = 1 order by RELEASE_CATEGORY ASC");
													while (!$res_type->EOF) { 
														$selected 			= "";
														$PK_RELEASE_CATEGORY 	= $res_type->fields['PK_RELEASE_CATEGORY']; 
														foreach($PK_RELEASE_CATEGORY_ARR as $PK_RELEASE_CATEGORY1){
															if($PK_RELEASE_CATEGORY1 == $PK_RELEASE_CATEGORY) {
																$selected = 'selected';
																break;
															}
														} ?>
														<option value="<?=$PK_RELEASE_CATEGORY?>" <?=$selected?> ><?=$res_type->fields['RELEASE_CATEGORY'] ?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
										</div>
                                   
                                        <div class="col-md-2">
											<div class="form-group m-b-40">
												<select id="PK_RELEASE_TYPE" name="PK_RELEASE_TYPE" class="form-control" >
													<option value="" ></option>
													<? $res_type = $db->Execute("select * from M_RELEASE_TYPE WHERE ACTIVE = 1 ORDER BY RELEASE_TYPE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_RELEASE_TYPE'] ?>" <? if($PK_RELEASE_TYPE == $res_type->fields['PK_RELEASE_TYPE']) echo "selected"; ?> ><?=$res_type->fields['RELEASE_TYPE']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_RELEASE_TYPE">Type</label>
											</div>
										</div>
                                   
                                        <div class="col-md-2">
											<div class="form-group m-b-40">
												<input type="text" class="form-control date" id="PUSHED_TO_D3_DATE" name="PUSHED_TO_D3_DATE" value="<?=$PUSHED_TO_D3_DATE?>" >
												<span class="bar"></span>
												<label for="PUSHED_TO_D3_DATE">Date Programming Pushed to D3</label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<textarea class="form-control" id="PROGRAMMING_NOTES" name="PROGRAMMING_NOTES" ><?=$PROGRAMMING_NOTES?></textarea>
														<span class="bar"></span>
														<label for="PROGRAMMING_NOTES">Programming Notes</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="SUBJECT" name="SUBJECT" value="<?=$SUBJECT?>"  >
														<span class="bar"></span>
														<label for="SUBJECT">Subject</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="LOCATION" name="LOCATION" value="<?=$LOCATION?>" >
														<span class="bar"></span>
														<label for="LOCATION">Location</label>
													</div>
												</div>
											 </div>
											
											<div class="row">	
												 <div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="KNOWLEDGEBASE_URL" name="KNOWLEDGEBASE_URL" value="<?=$KNOWLEDGEBASE_URL?>" >
														<span class="bar"></span>
														<label for="KNOWLEDGEBASE_URL">Knowledge Base ID</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="row form-group">
														<div class="custom-control col-md-8">Release Notes Pushed</div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="customRadio11" name="RELEASE_NOTES_PUSHED" value="1" <? if($RELEASE_NOTES_PUSHED == 1) echo "checked"; ?> class="custom-control-input" onclick="set_date()" >
															<label class="custom-control-label" for="customRadio11">Yes</label>
														</div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="customRadio22" name="RELEASE_NOTES_PUSHED" value="0" <? if($RELEASE_NOTES_PUSHED == 0) echo "checked"; ?>  class="custom-control-input" onclick="set_date()" >
															<label class="custom-control-label" for="customRadio22">No</label>
														</div>
													</div>
												</div>
												
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="text" class="form-control date" id="RELEASE_NOTES_PUSHED_DATE" name="RELEASE_NOTES_PUSHED_DATE" value="<?=$RELEASE_NOTES_PUSHED_DATE?>" >
														<span class="bar"></span>
														<label for="RELEASE_NOTES_PUSHED_DATE">Date Release Notes Pushed to D3</label>
													</div>
												</div>
											</div>
										</div>
										
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<textarea class="form-control" id="RELEASE_NOTES" name="RELEASE_NOTES" ><?=$RELEASE_NOTES?></textarea>
														<span class="bar"></span>
														<label for="RELEASE_NOTES">Release Notes</label>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info">Submit</button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_release_notes'" >Cancel</button>
												
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
		
		function generate_version(){
			jQuery(document).ready(function($) { 
				var PRODUCT = document.getElementById('PRODUCT').value;
				var MAJOR 	= document.getElementById('MAJOR').value;
				var BUILD 	= document.getElementById('BUILD').value;
				
				if(PRODUCT != '' && MAJOR != '' && BUILD != '') {
					document.getElementById('VERSION').value = PRODUCT+'.'+MAJOR+'.'+BUILD
					$("#VERSION").parent().addClass("focused")
				}
			});
		}
		function set_date(){
			jQuery(document).ready(function($) { 
				if(document.getElementById('customRadio11').checked == true) {
					document.getElementById('RELEASE_NOTES_PUSHED_DATE').value = "<?=$current_date?>";
					$("#RELEASE_NOTES_PUSHED_DATE").parent().addClass("focused")
				} else
					document.getElementById('RELEASE_NOTES_PUSHED_DATE').value = "";
			});
		}
	</script>

	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_RELEASE_CATEGORY').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Categories',
			nonSelectedText: 'Category',
			numberDisplayed: 3,
			nSelectedText: 'Categories selected'
		});
	});
	</script>
	
	<!-- <script src="https://cdn.tiny.cloud/1/d6quzxl18kigwmmr6z03zgk3w47922rw1epwafi19cfnj00i/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script> -->
	<? require_once("../global/tiny-cloud.php"); ?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			tinymce.init({ 
				selector:'#RELEASE_NOTES',
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
			
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
		});
	</script>
	
	
</body>

</html>
