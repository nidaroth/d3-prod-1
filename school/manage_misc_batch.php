<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/misc_batch.php");
require_once("function_student_ledger.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

if($_GET['act'] == 'del')	{
	$db->Execute("DELETE FROM S_MISC_BATCH_MASTER WHERE PK_MISC_BATCH_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	
	 $res_det = $db->Execute("select PK_MISC_BATCH_DETAIL from S_MISC_BATCH_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_MISC_BATCH_MASTER = '$_GET[id]' ");
	while (!$res_det->EOF) { 
		$PK_MISC_BATCH_DETAIL = $res_det->fields['PK_MISC_BATCH_DETAIL'];
		
		$db->Execute("DELETE FROM S_MISC_BATCH_DETAIL WHERE PK_MISC_BATCH_DETAIL = '$PK_MISC_BATCH_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$ledger_data_del['PK_MISC_BATCH_DETAIL'] = $PK_MISC_BATCH_DETAIL;
		delete_student_ledger($ledger_data_del);
	
		$res_det->MoveNext();
	}
	header("location:manage_misc_batch");
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
	<title><?=MISC_BATCH_PAGE_TITLE?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<style>
	.datagrid-header td {font-size: 14px !important; vertical-align: bottom;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-6 align-self-center"> <!-- DIAM-2158 --> 
						<div class="row">
							<div class="col-12 col-md-2">     
								<h4 class="text-themecolor"><?=MISC_BATCH_PAGE_TITLE?> </h4>
							</div>
							
						</div>
                    </div>
					<div class="col-md-3 align-self-center text-right">
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div>  
                    <div class="col-md-3 align-self-center text-right"> <!-- DIAM-2158 --> 
                        <div class="d-flex justify-content-end align-items-center">
							<!-- DIAM-2158 --> 
							<a href="misc_batch_pdf" class="btn btn-info d-none d-lg-block m-l-15"> <?=PDF?></a>
							<a href="misc_batch_excel" class="btn btn-info d-none d-lg-block m-l-15"><?=EXCEL?></a>
							<!-- DIAM-2158 --> 
                            <a href="misc_batch" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>
                        </div>
                    </div>
                </div>
				<div class="row pb-4">
					<div class="col-md-2">
						<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="doSearch_camp()">
							<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($res_type->RecordCount() == 1) echo "selected"; ?>><?= $res_type->fields['CAMPUS_CODE'] ?></option>
							<? $res_type->MoveNext();
							} ?>
						</select>
					</div>
					<div class="col-md-2">
						<select id="PK_BATCH_STATUS" name="PK_BATCH_STATUS[]" class="form-control" onchange="doSearch_camp()">
						<option value="">Batch Status</option>
						<? $res_batch_type = $db->Execute("SELECT * FROM `M_BATCH_STATUS` order by BATCH_STATUS ASC");
							while (!$res_batch_type->EOF) { ?>
								<option value="<?= $res_batch_type->fields['PK_BATCH_STATUS'] ?>" <? if ($res_batch_type->RecordCount() == 1) echo "selected"; ?>><?= $res_batch_type->fields['BATCH_STATUS'] ?></option>
							<? $res_batch_type->MoveNext();
							} ?>
						</select>
					</div>
					<div class="col-md-2">
						<input class="form-control date" id="BATCH_START_DATE" name="BATCH_START_DATE" placeholder="Batch Date (From)" onchange="doSearch_camp()"/>
					</div>
					<div class="col-md-2">
						<input class="form-control date" id="BATCH_END_DATE" name="BATCH_END_DATE" placeholder="Batch Date (Till)" onchange="doSearch_camp()"/>
					</div>
					<div class="col-md-2">
						<input class="form-control date" id="POSTED_START_DATE" name="POSTED_START_DATE" placeholder="Posted Date (From)" onchange="doSearch_camp()"/>
					</div>
					<div class="col-md-2">
						<input class="form-control date" id="POSTED_END_DATE" name="POSTED_END_DATE" placeholder="Posted Date (Till)" onchange="doSearch_camp()"/>
					</div>
				</div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_misc_batch"
										toolbar="#tb" pagination="true" pageSize = 25 autoRowHeight="true" nowrap="false" >
											<thead>
												<tr>
													<th field="PK_MISC_BATCH_MASTER" width="150px" hidden="true" sortable="true" ></th>
													<th field="BATCH_NO" width="120px" align="left" sortable="true" ><?=BATCH_NO?></th>
													<th field="CAMPUS" width="180px" align="left" sortable="true" ><?=CAMPUS?></th>
													<th field="BATCH_STATUS" width="150px" align="left" sortable="true" ><?=BATCH_STATUS?></th>
													<th field="BATCH_DATE" width="100px" align="left" sortable="true" ><?=BATCH_DATE?></th>
													<th field="POSTED_DATE" width="100px" align="left" sortable="true" ><?=POSTED_DATE?></th>
													
													<th field="DESCRIPTION" width="250px" align="left" sortable="true" ><?=DESCRIPTION?></th><!-- DIAM-2158 -->
													
													<th field="DEBIT" width="120px" align="right" sortable="true" ><?=BATCH_AMOUNT.'<br />('.DEBIT.')'?></th><!-- Ticket # 1494 -->
													<th field="CREDIT" width="120px" align="right" sortable="true" ><?=BATCH_AMOUNT.'<br />('.CREDIT.')'?></th><!-- Ticket # 1494 -->
													<th field="BALANCE" width="120px" align="right" sortable="true" ><?=BATCH_TOTAL.'<br />('.DEBIT.' - '.CREDIT.')' ?></th>
													<th field="ACTION" width="140px" align="center" sortable="false" ><?=OPTION?></th><!-- Ticket # 1494 --> <!-- DIAM-2158 --> 
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
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 1,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		// $('#PK_BATCH_STATUS').multiselect({
		// 	selectedList: 0,
		// 	includeSelectAllOption: true,
		// 	allSelectedText: 'All <?=BATCH_STATUS?>',
		// 	nonSelectedText: '<?=BATCH_STATUS?>',
		// 	numberDisplayed: 1,
		// 	nSelectedText: '<?=BATCH_STATUS?> selected'
		// });

		
	});
	</script>
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});
	function doSearch(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid('load',{
				SEARCH  : $('#SEARCH').val(),
				PK_CAMPUS_IDS_DRP  : $('#PK_CAMPUS').val(),
				PK_BATCH_STATUS: $('#PK_BATCH_STATUS').val(),
				BATCH_START_DATE: $('#BATCH_START_DATE').val(),
				BATCH_END_DATE: $('#BATCH_END_DATE').val(),
				POSTED_START_DATE: $('#POSTED_START_DATE').val(),
				POSTED_END_DATE: $('#POSTED_END_DATE').val()
			});
		});	
	}
	function doSearch_camp(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid('load',{
				SEARCH  : $('#SEARCH').val(),
				PK_CAMPUS_IDS_DRP  : $('#PK_CAMPUS').val(),
				PK_BATCH_STATUS: $('#PK_BATCH_STATUS').val(),
				BATCH_START_DATE: $('#BATCH_START_DATE').val(),
				BATCH_END_DATE: $('#BATCH_END_DATE').val(),
				POSTED_START_DATE: $('#POSTED_START_DATE').val(),
				POSTED_END_DATE: $('#POSTED_END_DATE').val()
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
						window.location.href='misc_batch?id='+selected_row.PK_MISC_BATCH_MASTER;
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
	function delete_row(id){
		jQuery(document).ready(function($) {
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
		});
	}
	function conf_delete(val,id){
		if(val == 1)
			window.location.href = 'manage_misc_batch?act=del&id='+$("#DELETE_ID").val();
		else
			$("#deleteModal").modal("hide");
	}
	</script>

</body>

</html>