<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/program.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if($_GET['act'] == 'del')	{
	if($_SESSION['PK_ROLES'] == 3)
		$cond = " AND PK_CAMPUS IN ($_SESSION[PK_CAMPUS]) ";
	else 
		$cond = " AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	/*
	$res = $db->Execute("SELECT PK_CAMPUS_PROGRAM FROM M_CAMPUS_PROGRAM WHERE PK_CAMPUS_PROGRAM = '$_GET[id]' $cond ");
	if($res->RecordCount() > 0) {
		$db->Execute("DELETE FROM M_CAMPUS_PROGRAM WHERE PK_CAMPUS_PROGRAM = '$_GET[id]' ");
		$db->Execute("DELETE FROM M_CAMPUS_PROGRAM_ANALYTICS_SETUP WHERE PK_CAMPUS_PROGRAM = '$_GET[id]' ");
		$db->Execute("DELETE FROM M_CAMPUS_PROGRAM_CAMPUS WHERE PK_CAMPUS_PROGRAM = '$_GET[id]' ");
		$db->Execute("DELETE FROM M_CAMPUS_PROGRAM_REQUIREMENT WHERE PK_CAMPUS_PROGRAM = '$_GET[id]' ");
		$db->Execute("DELETE FROM M_CAMPUS_PROGRAM_AY WHERE PK_CAMPUS_PROGRAM = '$_GET[id]' ");
		$db->Execute("DELETE FROM M_CAMPUS_PROGRAM_FEE WHERE PK_CAMPUS_PROGRAM = '$_GET[id]' ");
		$db->Execute("DELETE FROM M_CAMPUS_PROGRAM_COURSE WHERE PK_CAMPUS_PROGRAM = '$_GET[id]' ");
		$db->Execute("DELETE FROM M_CAMPUS_PROGRAM_AWARD WHERE PK_CAMPUS_PROGRAM = '$_GET[id]' ");
		$db->Execute("DELETE FROM S_PROGRAM_GRADE_BOOK WHERE PK_CAMPUS_PROGRAM = '$_GET[id]' ");
	}*/
	header("location:manage_program");
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
	<title><?=PROGRAM_PAGE_TITLE?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><?=PROGRAM_PAGE_TITLE?> </h4>
                    </div>
					<div class="col-md-3 align-self-center text-right">
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div>  
                    <div class="col-md-2 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <a href="program" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_program"
										toolbar="#tb" pagination="true" pageSize = 25 >
											<thead>
												<tr>
													<th field="PK_CAMPUS_PROGRAM" width="150px" hidden="true" sortable="true" ></th>

													<!-- Ticket # 1339 -->
													<th field="CAMPUS_CODE" width="200px" align="left" sortable="true" ><?=CAMPUS?></th>
													<th field="CODE" width="200px" align="left" sortable="true" ><?=CODE?></th>
													<th field="PROGRAM_TRANSCRIPT_CODE" width="150px" align="left" sortable="true" ><?=TRANSCRIPT_CODE?></th>
													<th field="DESCRIPTION" width="300px" align="left" sortable="true" ><?=DESCRIPTION?></th>
													
													<th field="MONTHS" width="80px" align="right" sortable="true" ><?=MONTHS?></th>
													<th field="WEEKS" width="80px" align="right" sortable="true" ><?=WEEKS?></th>
													<th field="HOURS" width="80px" align="right" sortable="true" ><?=HOURS?></th>
													<th field="UNITS" width="80px" align="right" sortable="true" ><?=UNITS?></th>
													<th field="FA_UNITS" width="80px" align="right" sortable="true" ><?=FA_UNITS?></th>
													<!-- Ticket # 1339 -->
													
													<th field="ACTION" width="130px" align="left" sortable="false" ><?=OPTION?></th>
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
						var selected_row = $('#tt').datagrid('getSelected');
						window.location.href='program?id='+selected_row.PK_CAMPUS_PROGRAM;
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
			window.location.href = 'manage_program?act=del&id='+$("#DELETE_ID").val();
		else
			$("#deleteModal").modal("hide");
	}

	function refresh_win(win){
		win.close();
		doSearch()
	}
	</script>

</body>

</html>