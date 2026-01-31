<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
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
	<title><?=MNU_FINAL_GRADE_IMPORT_REVIEW?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-7 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_FINAL_GRADE_IMPORT_REVIEW?> </h4>
                    </div>
					<div class="col-md-3 align-self-center text-right">
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div>  
                    <div class="col-md-2 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <a href="final_grade_import" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=UPLOAD?></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_final_grade_import_review"
										toolbar="#tb" pagination="true" pageSize = 25 loadMsg="Processing, please wait..." data-options="onClickCell: function(rowIndex, field, value){
											$('#tt').datagrid('selectRow',rowIndex);
											if(field != 'ACTION' ){
												var selected_row = $('#tt').datagrid('getSelected');
												window.location.href='final_grade_import_map_result?id='+selected_row.PK_FINAL_GRADE_IMPORT+'&t='+selected_row.MATCH_BY;
											}
										},
										view: $.extend(true,{},$.fn.datagrid.defaults.view,{
											onAfterRender: function(target){
												$.fn.datagrid.defaults.view.onAfterRender.call(this,target);
												$('.datagrid-header-inner').width('100%') 
												$('.datagrid-btable').width('100%') 
												$('.datagrid-body').css({'overflow-y': 'hidden'});
											}
										})" >
											<thead> 
												<tr>
													<th field="PK_FINAL_GRADE_IMPORT" width="150px" hidden="true" sortable="true" ></th>
													<th field="MATCH_BY" width="150px" hidden="true" sortable="true" ></th>
													<th field="FILE_NAME" width="350px" align="left" sortable="true" ><?=FILE_NAME?></th>
													<th field="MATCH_BY_1" width="150px" align="left" sortable="true" ><?=MATCH_ON?></th>
													<th field="UPLOADED_BY_NAME" width="250px" align="left" sortable="true" ><?=UPLOADED_BY?></th>
													<th field="UPLOADED_ON" width="150px" align="center" sortable="false" ><?=UPLOADED_ON?></th>
													<th field="POSTED" width="150px" align="left" sortable="false" ><?=STATUS?></th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
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
	<script type="text/javascript">
	function doSearch(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid('load',{
				SEARCH  : $('#SEARCH').val(),
			});
		});	
	}
	function search(e){
		if (e.keyCode == 13) {
			doSearch();
		}
	}
	$(function(){
		jQuery(document).ready(function($) {
			// $('#tt').datagrid({
			// 	onClickCell: function(rowIndex, field, value){
			// 		$('#tt').datagrid('selectRow',rowIndex);
			// 		if(field != 'ACTION' ){
			// 			var selected_row = $('#tt').datagrid('getSelected');
			// 			window.location.href='final_grade_import_map_result?id='+selected_row.PK_FINAL_GRADE_IMPORT+'&t='+selected_row.MATCH_BY;
			// 		}
			// 	}
			// });
			
			// $('#tt').datagrid({
			// 	view: $.extend(true,{},$.fn.datagrid.defaults.view,{
			// 		onAfterRender: function(target){
			// 			$.fn.datagrid.defaults.view.onAfterRender.call(this,target);
			// 			$('.datagrid-header-inner').width('100%') 
			// 			$('.datagrid-btable').width('100%') 
			// 			$('.datagrid-body').css({'overflow-y': 'hidden'});
			// 		}
			// 	})
			// });

		});
	});
	jQuery(document).ready(function($) {
		$(window).resize(function() {
			$('#tt').datagrid('resize');
			$('#tb').panel('resize');
		}) 
	});

	</script>

</body>

</html>