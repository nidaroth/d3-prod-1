<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/earnings_setup.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}
if($_GET['act'] == 'del')	{
	$db->Execute("DELETE FROM S_EARNINGS_SETUP WHERE PK_EARNINGS_SETUP = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$db->Execute("DELETE FROM S_EARNINGS_SETUP_CAMPUS WHERE PK_EARNINGS_SETUP = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	header("location:manage_earnings_setup");
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=MNU_EARNINGS_SETUP?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<style>
	.datagrid-header td {font-size: 14px !important;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-7 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_EARNINGS_SETUP?> </h4>
                    </div>
					<div class="col-md-3 align-self-center text-right">
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div>  
                    <div class="col-md-2 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <a href="earnings_setup" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_earnings_setup" loadMsg="Processing, please wait..."
										toolbar="#tb" pagination="true" pageSize = 25 data-options="onClickCell: function(rowIndex, field, value){
											$('#tt').datagrid('selectRow',rowIndex);
											if(field != 'ACTION' ){
												var selected_row = $('#tt').datagrid('getSelected');
												window.location.href='earnings_setup?id='+selected_row.PK_EARNINGS_SETUP;
											}
										},
										view: $.extend(true,{},$.fn.datagrid.defaults.view,{
											onAfterRender: function(target){
												$.fn.datagrid.defaults.view.onAfterRender.call(this,target);
												$('.datagrid-header-inner').width('100%') 
												$('.datagrid-btable').width('100%') 
												$('.datagrid-body').css({'overflow-y': 'hidden'});
											}
										})
										" >
										<thead>
											<tr>
												<th field="PK_EARNINGS_SETUP" width="150px" hidden="true" sortable="true" ></th>
												
												<th field="CAMPUS" width="150px" align="left" sortable="true" ><?=CAMPUS?></th>
												<!-- <th field="EXCLUDED_PROGRAMS" width="150px" align="left" sortable="true" ><?//=EXCLUDED_PROGRAMS_1?></th> -->
												<th field="EARNING_TYPE" width="130px" align="left" sortable="true" ><?=EARNINGS_TYPE?></th>
												<th field="EXCLUDED_STUDENT_STATUS" width="230px" align="left" sortable="true" ><?=EXCLUDED_STUDENT_STATUS_1?></th>
												<th field="INCLUDED_FEE_LEDGER_CODES" width="240" align="left" sortable="true" ><?=INCLUDED_FEE_LEDGER_CODES_1?></th>
												<th field="IGNORE_FUTURE_TUITION" width="120px" align="left" sortable="true" ><?=IGNORE_FUTURE_TUITION_1?></th>
												<th field="PRORATE_FIRST_MONTH" width="100px" align="left" sortable="true" ><?=PRORATE_FIRST_MONTH_1?></th>
												<th field="PRORATE_LOA_STATUS" width="80px" align="left" sortable="true" ><?=PRORATE_LOA_STATUS_1?></th>
												<th field="PRORATE_BREAKS" width="80px" align="left" sortable="true" ><?=PRORATE_BREAKS_1?></th>
												<th field="PRORATE_CLOSURES" width="80px" align="left" sortable="true" ><?=PRORATE_CLOSURES_1?></th>
												<th field="PRORATE_HOLIDAYS" width="80px" align="left" sortable="true" ><?=PRORATE_HOLIDAYS_1?></th>
												
												<th field="ACTION" width="100px" align="left" sortable="false" ><?=OPTION?></th>
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
		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=DELETE_CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<?=DELETE_MESSAGE_GENERAL?>
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" ><?=NO?></button>
					</div>
				</div>
			</div>
		</div>
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
			// 			window.location.href='earnings_setup?id='+selected_row.PK_EARNINGS_SETUP;
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
	function delete_row(id){
		jQuery(document).ready(function($) {
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
		});
	}
	function conf_delete(val,id){
		if(val == 1)
			window.location.href = 'manage_earnings_setup?act=del&id='+$("#DELETE_ID").val();
		else
			$("#deleteModal").modal("hide");
	}
	</script>

</body>

</html>