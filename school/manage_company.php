<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/company.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}
if($_GET['act'] == 'del')	{
	$res_check = $db->Execute("select PK_STUDENT_JOB from S_STUDENT_JOB WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$_GET[id]' ");
	if($res_check->RecordCount() == 0) {
		$db->Execute("DELETE FROM S_COMPANY_CONTACT WHERE PK_COMPANY = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$db->Execute("DELETE FROM S_COMPANY_JOB WHERE PK_COMPANY = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$db->Execute("DELETE FROM S_COMPANY_EVENT WHERE PK_COMPANY = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$db->Execute("DELETE FROM S_COMPANY_QUESTIONNAIRE WHERE PK_COMPANY = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$db->Execute("DELETE FROM S_COMPANY WHERE PK_COMPANY = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	header("location:manage_company");
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
	<title><?=COMPANY_PAGE_TITLE?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row">
                    <div class="col-md-10 align-self-center">
                        <h4 class="text-themecolor"><?=COMPANY_PAGE_TITLE?> </h4>
                    </div>
					
					<div class="col-md-2 align-self-center " >
                        <div class="d-flex justify-content-end align-items-center">
                            <a href="company" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>
                        </div>
                    </div>
				</div>
				
				<div class="row" style="padding:10px 0" >
					<div class="col-md-2 align-self-center "  style="flex: 0 0 14.66667%;max-width: 14.66667%;" >
						<b><?=CAMPUS?></b>
						<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="doSearch()" >
							<? $res_type = $db->Execute("select CAMPUS_CODE, PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_CAMPUS'] ?>"><?=$res_type->fields['CAMPUS_CODE']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div>  
					
					<div class="col-md-2 align-self-center" style="flex: 0 0 14.66667%;max-width: 14.66667%;" >
						<b><?=ACTIVE?></b>
						<select id="ACTIVE_COMPANY" name="ACTIVE_COMPANY" class="form-control" onchange="doSearch()" style="margin-bottom: 0;" >
							<option value="" ></option>
							<option value="1"><?=ACTIVE_COMPANIES?></option>
							<option value="2"><?=INACTIVE_COMPANIES?></option>
						</select>
					</div>
					
					<div class="col-md-2 align-self-center " style="flex: 0 0 14.66667%;max-width: 14.66667%;" >
						<b><?=COMPANY_SOURCE?></b>
						<select id="PK_COMPANY_SOURCE" name="PK_COMPANY_SOURCE[]" multiple class="form-control" onchange="doSearch()" >
							<? $res_type = $db->Execute("select PK_COMPANY_SOURCE, COMPANY_SOURCE from M_COMPANY_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY COMPANY_SOURCE ASC ");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_COMPANY_SOURCE'] ?>" ><?=$res_type->fields['COMPANY_SOURCE']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div>
					
					<div class="col-md-2 align-self-center" style="flex: 0 0 14.66667%;max-width: 14.66667%;" >
						<b><?=OPEN_JOBS?></b>
						<select id="JOB_TYPE" name="JOB_TYPE" class="form-control" onchange="doSearch()" style="margin-bottom: 0;" >
							<option value="" ></option>
							<option value="1"><?=COMPANIES_WITH_OPEN_JOBS?></option>
							<option value="2"><?=COMPANIES_WITHOUT_OPEN_JOBS?></option>
							<option value="3"><?=BOTH?></option>
						</select>
					</div>
					
					<div class="col-md-2 align-self-center " style="flex: 0 0 14.66667%;max-width: 14.66667%;" >
						<b><?=PLACEMENT_COMPANY_STATUS?></b>
						<select id="PK_PLACEMENT_COMPANY_STATUS" name="PK_PLACEMENT_COMPANY_STATUS[]" multiple class="form-control" onchange="doSearch()" >
							<? $res_type = $db->Execute("select PK_PLACEMENT_COMPANY_STATUS, PLACEMENT_COMPANY_STATUS from M_PLACEMENT_COMPANY_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY PLACEMENT_COMPANY_STATUS ASC ");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_PLACEMENT_COMPANY_STATUS'] ?>" ><?=$res_type->fields['PLACEMENT_COMPANY_STATUS']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div>
					
					<div class="col-md-2 align-self-center " style="flex: 0 0 14.66667%;max-width: 14.66667%;" >
						<b><?=PLACEMENT_TYPE?></b>
						<select id="PK_PLACEMENT_TYPE" name="PK_PLACEMENT_TYPE[]" multiple class="form-control" onchange="doSearch()" >
							<? $res_type = $db->Execute("select PK_PLACEMENT_TYPE, TYPE from M_PLACEMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY TYPE ASC ");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_PLACEMENT_TYPE'] ?>"><?=$res_type->fields['TYPE']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div>  
					
					<div class="col-md-2 align-self-center" style="flex: 0 0 12%;max-width: 12%;" >
						<b><?=SEARCH?></b>
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; "  style="font-family: FontAwesome;margin-bottom: 0;" onkeypress="search(event)">
					</div> 
				</div>
		
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_company"
										toolbar="#tb" pagination="true" pageSize = 25 nowrap="false" autoRowHeight="true"  >
											<thead>
												<tr>
													<th field="PK_COMPANY" width="150px" hidden="true" sortable="true" ></th>
													<th field="COMPANY_NAME" width="180px" align="left" sortable="true" ><?=COMPANY_NAME?></th>
													<th field="ADDRESS" width="130px" align="left" sortable="false" ><?=STREET_ADDRESS?></th>
													<th field="CITY" width="130px" align="left" sortable="true" ><?=CITY?></th>
													<th field="STATE_CODE" width="70px" align="left" sortable="true" ><?=STATE?></th>
													<th field="PLACEMENT_COMPANY_STATUS" width="150px" align="left" sortable="true" ><?=STATUS?></th>
													<th field="PLACEMENT_TYPE" width="150px" align="left" sortable="true" ><?=PLACEMENT_TYPE?></th>
													<th field="COMPANY_SOURCE" width="170px" align="left" sortable="true" ><?=COMPANY_SOURCE?></th>
													<th field="OPEN_JOBS" width="100px" align="center" sortable="true" ><?=OPEN_JOBS?></th>
													<th field="TOTAL_JOBS" width="100px" align="center" sortable="true" ><?=TOTAL_JOBS?></th>
													<th field="CAMPUS" width="100px" align="left" sortable="true" ><?=CAMPUS?></th>
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
				SEARCH  						: $('#SEARCH').val(),
				PK_PLACEMENT_TYPE  				: $('#PK_PLACEMENT_TYPE').val(),
				PK_PLACEMENT_COMPANY_STATUS  	: $('#PK_PLACEMENT_COMPANY_STATUS').val(),
				JOB_TYPE  						: $('#JOB_TYPE').val(),
				ACTIVE_COMPANY  				: $('#ACTIVE_COMPANY').val(),
				PK_COMPANY_SOURCE				: $('#PK_COMPANY_SOURCE').val(),
				PK_CAMPUS						: $('#PK_CAMPUS').val(),
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
						window.location.href='company?id='+selected_row.PK_COMPANY;
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
			window.location.href = 'manage_company?act=del&id='+$("#DELETE_ID").val();
		else
			$("#deleteModal").modal("hide");
	}
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#PK_PLACEMENT_TYPE').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=PLACEMENT_TYPE?>',
				nonSelectedText: '',
				numberDisplayed: 1,
				nSelectedText: '<?=PLACEMENT_TYPE?> selected'
			});
			
			$('#PK_PLACEMENT_COMPANY_STATUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=PLACEMENT_COMPANY_STATUS?>',
				nonSelectedText: '',
				numberDisplayed: 1,
				nSelectedText: '<?=PLACEMENT_COMPANY_STATUS?> selected'
			});
			
			$('#PK_COMPANY_SOURCE').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=COMPANY_SOURCE?>',
				nonSelectedText: '',
				numberDisplayed: 1,
				nSelectedText: '<?=COMPANY_SOURCE?> selected'
			});
			
			$('#PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=CAMPUS?>',
				nonSelectedText: '',
				numberDisplayed: 1,
				nSelectedText: '<?=CAMPUS?> selected'
			});
			
			
			/*$('#JOB_TYPE').multiselect({
				includeSelectAllOption: true,
				allSelectedText: '',
				nonSelectedText: '<?=OPEN_JOBS?>',
				numberDisplayed: 1,
				nSelectedText: ''
			});
			
			$('#ACTIVE_COMPANY').multiselect({
				includeSelectAllOption: true,
				allSelectedText: '',
				nonSelectedText: 'Company Status',
				numberDisplayed: 1,
				nSelectedText: ''
			});*/
			
			
		});
	</script>

</body>

</html>