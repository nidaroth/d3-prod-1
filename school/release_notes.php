<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/release_notes.php");
require_once("check_access.php");

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
	<title><?=RELEASE_NOTES?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-2 align-self-center">
                        <h4 class="text-themecolor"><?=RELEASE_NOTES?></h4>
                    </div>
					
					<div class="col-md-2 align-self-center">
                        <select id="PK_RELEASE_CATEGORY" name="PK_RELEASE_CATEGORY[]" multiple class="form-control" onchange="doSearch()" >
							<? $res_type = $db->Execute("select PK_RELEASE_CATEGORY,RELEASE_CATEGORY from M_RELEASE_CATEGORY WHERE ACTIVE = 1 order by RELEASE_CATEGORY ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_RELEASE_CATEGORY']?>" ><?=$res_type->fields['RELEASE_CATEGORY'] ?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
                    </div>
					
					<div class="col-md-2 align-self-center">
						<select id="PK_RELEASE_TYPE" name="PK_RELEASE_TYPE" class="form-control" onchange="doSearch()" >
							<option value="" ><?=TYPE ?></option>
							<? $res_type = $db->Execute("select * from M_RELEASE_TYPE WHERE ACTIVE = 1 ORDER BY RELEASE_TYPE ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_RELEASE_TYPE'] ?>" <? if($PK_RELEASE_TYPE == $res_type->fields['PK_RELEASE_TYPE']) echo "selected"; ?> ><?=$res_type->fields['RELEASE_TYPE']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div>
					 
					<div class="col-md-2 align-self-center">
						<input type="text" class="form-control date" id="START_DATE" name="START_DATE" placeholder="Start Date" value="" onchange="doSearch()">
					</div>
					
					<div class="col-md-2 align-self-center">
						<input type="text" class="form-control date" id="END_DATE" name="END_DATE" placeholder="End Date" value="" onchange="doSearch()">
					</div>
					
					<div class="col-md-2 align-self-center">
						<input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="Search" style="font-family: FontAwesome;" onkeypress="search(event)" value="">
					</div>
					
                </div>
               <div class="card-group">
                    <div class="card">
                        <div class="card-body">
							<div id="release_notes_div" >
								<? require_once("ajax_get_release_notes.php"); ?>
							</div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>

        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>
	
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script type="text/javascript" src="../backend_assets/dist/js/jquery.easyui.min.js"></script>
	<script src="../backend_assets/dist/js/jquery-ui.js"></script> 
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});
	
	function doSearch(){
		jQuery(document).ready(function($) {
			var data  = 'PK_RELEASE_TYPE='+document.getElementById('PK_RELEASE_TYPE').value+'&START_DATE='+document.getElementById('START_DATE').value+'&END_DATE='+document.getElementById('END_DATE').value+'&SEARCH='+document.getElementById('SEARCH').value+'&PK_RELEASE_CATEGORY='+$('#PK_RELEASE_CATEGORY').val();
			var value = $.ajax({
				url: "ajax_get_release_notes",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					//alert(data)
					document.getElementById('release_notes_div').innerHTML = data;
				}		
			}).responseText;
				
		});
	}
	
	function search(e){
		if (e.keyCode == 13) {
			doSearch();
		}
	}
	
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
	
</body>

</html>