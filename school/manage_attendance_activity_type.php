<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/attendance_activity_type.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if($_GET['act'] == 'del')	{
	$res_check1 = $db->Execute("select PK_COURSE_OFFERING from S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]'");
	$res_check2 = $db->Execute("select PK_COURSE_DEFAULT_SCHEDULE from S_COURSE_DEFAULT_SCHEDULE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (SUN_PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' OR MON_PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' OR TUE_PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' OR WED_PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' OR THU_PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' OR FRI_PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' OR SAT_PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' ) ");
	$res_check3 = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE from S_COURSE_OFFERING_SCHEDULE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (SUN_PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' OR MON_PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' OR TUE_PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' OR WED_PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' OR THU_PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' OR FRI_PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' OR SAT_PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' ) ");
	$res_check4 = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_ATTENDANCE_ACTIVITY_TYPES = '$_GET[id]' ");
	
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 && $res_check4->RecordCount() == 0) 
		$db->Execute("DELETE FROM M_ATTENDANCE_ACTIVITY_TYPE WHERE PK_ATTENDANCE_ACTIVITY_TYPE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	header("location:manage_attendance_activity_type");
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
	<title><?=MNU_ATTENDANCE_ACTIVITY_TYPE?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><?=MNU_ATTENDANCE_ACTIVITY_TYPE?> </h4>
                    </div>
					<div class="col-md-3 align-self-center text-right">
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div>  
                    <div class="col-md-2 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <a href="attendance_activity_type" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_attendance_activity_type"
										toolbar="#tb" pagination="true" pageSize = 25 >
											<thead>
												<tr>
													<th field="PK_ATTENDANCE_ACTIVITY_TYPE" width="150px" hidden="true" sortable="true" ></th>
													<th field="ATTENDANCE_ACTIVITY_TYPE" width="700px" align="left" sortable="true" ><?=ACTIVITY_TYPE?></th>
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
						var selected_row = $('#tt').datagrid('getSelected');
						window.location.href='attendance_activity_type?id='+selected_row.PK_ATTENDANCE_ACTIVITY_TYPE;
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
			window.location.href = 'manage_attendance_activity_type?act=del&id='+$("#DELETE_ID").val();
		else
			$("#deleteModal").modal("hide");
	}
	</script>

</body>

</html>