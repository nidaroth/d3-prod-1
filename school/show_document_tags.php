<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/pdf_template.php");
require_once("check_access.php");

if(check_access('SETUP_COMMUNICATION') == 0 ){
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
	<title><?=DOCUMENT_TAGS?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=DOCUMENT_TAGS ?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels " method="post" name="form1" id="form1" >
									<div class="row" style="padding-bottom:10px;" >
										<div class="col-md-12 " style="color:red" >
											<?=PLEASE_COPY_PAST ?>
										</div>
									</div>
									<div class="row" style="padding-bottom:10px;" >
										<div class="col-md-3 ">
											<select id="PK_DOCUMENT_TEMPLATE_CATEGORY" name="PK_DOCUMENT_TEMPLATE_CATEGORY[]" multiple class="form-control" onchange="get_sub_category()" >
												<? $res_type = $db->Execute("select PK_DOCUMENT_TEMPLATE_CATEGORY,DOCUMENT_TEMPLATE_CATEGORY from Z_DOCUMENT_TEMPLATE_CATEGORY WHERE ACTIVE = 1 ");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_DOCUMENT_TEMPLATE_CATEGORY']?>" ><?=$res_type->fields['DOCUMENT_TEMPLATE_CATEGORY'] ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-3 " id="PK_DOCUMENT_TEMPLATE_SUB_CATEGORY_DIV" >
											<select id="PK_DOCUMENT_TEMPLATE_SUB_CATEGORY" name="PK_DOCUMENT_TEMPLATE_SUB_CATEGORY[]" multiple class="form-control" >
											</select>
										</div>
										
										<div class="col-md-3 ">
											<input id="TAG_NAME" name="TAG_NAME" value="" type="text" class="form-control" placeholder="<?=TAGS?>" >
										</div>
										
										<div class="col-md-1 align-self-center ">
											<button type="button" class="btn waves-effect waves-light btn-dark" onclick="search()" ><?=SEARCH?></button>
										</div>
									</div>
								
									<br />
									<div id="tag_div" >
                                        
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
		
		function search(){
			jQuery(document).ready(function($) {
				var data  = 'PK_DOCUMENT_TEMPLATE_CATEGORY='+$('#PK_DOCUMENT_TEMPLATE_CATEGORY').val()+'&PK_DOCUMENT_TEMPLATE_SUB_CATEGORY='+$('#PK_DOCUMENT_TEMPLATE_SUB_CATEGORY').val()+'&TAG_NAME='+$('#TAG_NAME').val()+'&t=<?=$_GET['t']?>';
				var value = $.ajax({
					url: "ajax_search_document_tags",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('tag_div').innerHTML = data
					}		
				}).responseText;
			});
		}
		
		function get_sub_category(val){
			jQuery(document).ready(function($) { 
				var data  = 'PK_DOCUMENT_TEMPLATE_CATEGORY='+$('#PK_DOCUMENT_TEMPLATE_CATEGORY').val()+'&t=<?=$_GET['t']?>';;
				var value = $.ajax({
					url: "ajax_document_sub_category",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_DOCUMENT_TEMPLATE_SUB_CATEGORY_DIV').innerHTML = data;
					
						$('#PK_DOCUMENT_TEMPLATE_SUB_CATEGORY').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=SUBCATEGORY?>',
							nonSelectedText: '<?=SUBCATEGORY?>',
							numberDisplayed: 2,
							nSelectedText: '<?=SUBCATEGORY?> selected'
						});
					}		
				}).responseText;
			});
		}
		
	</script>
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_DOCUMENT_TEMPLATE_CATEGORY').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CATEGORY?>',
			nonSelectedText: '<?=CATEGORY?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CATEGORY?> selected'
		});
		
		$('#PK_DOCUMENT_TEMPLATE_SUB_CATEGORY').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=SUBCATEGORY?>',
			nonSelectedText: '<?=SUBCATEGORY?>',
			numberDisplayed: 2,
			nSelectedText: '<?=SUBCATEGORY?> selected'
		});
	});
	</script>
</body>

</html>