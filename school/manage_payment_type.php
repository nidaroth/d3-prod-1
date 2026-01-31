<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/payment_type.php");
require_once("check_access.php");

if(check_access('SETUP_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

/* DIAM - 124, Only data access for Main Admin */
$PK_USER = $_SESSION['PK_USER'];
$PK_ACCOUNT_DATA = mysql_query("SELECT * FROM Z_USER WHERE PK_USER = ".$PK_USER." ") or die(mysql_error());
$Record = mysql_fetch_array($PK_ACCOUNT_DATA);
$PK_USER_TYPE = $Record['PK_USER_TYPE'];
/* End DIAM - 124, Only data access for Main Admin */

if($_GET['act'] == 'del')	{
	//$db->Execute("DELETE FROM M_AR_PAYMENT_TYPE WHERE PK_AR_PAYMENT_TYPE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	header("location:manage_payment_type");
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
	<title><?=AR_PAYMENT_TYPE_PAGE_TITLE?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<!-- DIAM-2131 -->
	<style>
		.disablelink{
			pointer-events:none;
		}
	</style>
	<!-- DIAM-2131 -->
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-7 align-self-center">
                        <h4 class="text-themecolor"><?=AR_PAYMENT_TYPE_PAGE_TITLE?> </h4>
                    </div>
					<div class="col-md-3 align-self-center text-right">
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div>  
					<?
						$disablelink = "disablelink"; //DIAM-2131
						if ($PK_USER_TYPE == 1) //DIAM-2131
						{
							$disablelink=""; //DIAM-2131
							?>
								<div class="col-md-2 align-self-center text-right">
			                        <div class="d-flex justify-content-end align-items-center">
			                            <a href="payment_type" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>
			                        </div>
			                    </div>
							<?

						}
					?>
                    
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_payment_type"
										toolbar="#tb" pagination="true" pageSize = 25 >
											<thead>
												<tr>
													<th field="PK_AR_PAYMENT_TYPE" width="150px" hidden="true" sortable="true" ></th>
													<th field="AR_PAYMENT_TYPE" width="780px" align="left" sortable="true" ><?=AR_PAYMENT_TYPE?></th>
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
			$('#tt').datagrid({
				onClickCell: function(rowIndex, field, value){
					$('#tt').datagrid('selectRow',rowIndex);
					if(field != 'ACTION' ){
						/*var PK_USER_TYPE = '<?php echo $PK_USER_TYPE;?>';
						if (PK_USER_TYPE == 1) 
						{*/
							var selected_row = $('#tt').datagrid('getSelected');
							window.location.href='payment_type?id='+selected_row.PK_AR_PAYMENT_TYPE;
						/*}*/
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
						$('.datagrid-row').addClass("<?=$disablelink?>"); //DIAM-2131
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
	function delete_row(id){
		jQuery(document).ready(function($) {
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
		});
	}
	function conf_delete(val,id){
		if(val == 1)
			window.location.href = 'manage_payment_type?act=del&id='+$("#DELETE_ID").val();
		else
			$("#deleteModal").modal("hide");
	}
	</script>

</body>

</html>
