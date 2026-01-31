<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/ar_leder_code.php");
require_once("../language/_1098T_Setup.php");
require_once("../language/menu.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT _4807G FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if(check_access('MANAGEMENT_ACCOUNTING') == 0 || $res_add_on->fields['_4807G'] == 0){
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
	<title><?=MNU_FARE?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-4 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_FARE ?></h4>
                    </div>
					<div class="col-md-3 align-self-center text-right">
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>" onkeypress="search(event)" style="font-family: FontAwesome;margin-top:-8px" >
					</div>  
			
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_480G_ledger"
										toolbar="#tb" pagination="true" pageSize = 25 >
											<thead>
												<tr>

													<th field="PK_AR_LEDGER_CODE" width="100px" hidden="true" sortable="true" ></th>

													<th field="CODE" width="150px" align="left" sortable="true" ><?=LEDGER_CODE?></th>
													<th field="INCLUDE_IN_REPORTING_4807G" width="173px" align="left" sortable="true" ><?=INCLUDE_REPORT?></th>
													<th field="FINANCIAL_ASSISTANCE_TYPE" width="220px" align="left" sortable="true" ><?=FINANCIAL_ASSISTANCE_TYPE?></th>
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

	<? require_once("js.php"); ?>
	
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script type="text/javascript" src="../backend_assets/dist/js/jquery.easyui.min.js"></script>
	<script src="../backend_assets/dist/js/jquery-ui.js"></script> 
	<script type="text/javascript">
	function doSearch(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid('load',{
				SEARCH  : $('#SEARCH').val(),
				TYPE    : $('#TYPE').val(),
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
			$('#tt').datagrid({
				onClickCell: function(rowIndex, field, value){
					$('#tt').datagrid('selectRow',rowIndex);
					if(field != 'ACTION' ){
						var selected_row = $('#tt').datagrid('getSelected');
						window.location.href='480G_ledger?id='+selected_row.PK_AR_LEDGER_CODE;
					}
				}
			});
			
			$('#tt').datagrid({
				view: $.extend(true,{},$.fn.datagrid.defaults.view,{
					onAfterRender: function(target){
						$.fn.datagrid.defaults.view.onAfterRender.call(this,target);
						$('.datagrid-header-inner').width('100%') 
						$('.datagrid-btable').width('100%') 
						$('.datagrid-body').css({'overflow-y': 'hidden'});
					}
				})
			});

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