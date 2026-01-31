<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/term_master.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if($_GET['act'] == 'del')	{
	$res_check1 = $db->Execute("select PK_COURSE_OFFERING from S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$_GET[id]' ");
	$res_check2 = $db->Execute("select PK_STUDENT_COURSE from S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$_GET[id]' ");
	$res_check3 = $db->Execute("select PK_STUDENT_ENROLLMENT from S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$_GET[id]' ");
	$res_check4 = $db->Execute("select PK_STUDENT_FEE_BUDGET from S_STUDENT_FEE_BUDGET WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$_GET[id]' ");
	$res_check5 = $db->Execute("select PK_TUITION_BATCH_MASTER from S_TUITION_BATCH_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$_GET[id]' ");
	
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 && $res_check4->RecordCount() == 0 && $res_check5->RecordCount() == 0)
		$db->Execute("DELETE FROM S_TERM_MASTER WHERE PK_TERM_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	header("location:manage_term_master");
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
	<title><?=TERM_MASTER_PAGE_TITLE?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-6 align-self-center">
                        <h4 class="text-themecolor"><?=TERM_MASTER_PAGE_TITLE?> </h4>
                    </div>
					<div class="col-md-3 align-self-center text-right">
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div>  
                    <div class="col-md-3 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <a href="term_master" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>
							
							<a href="term_master_upload" class="btn btn-info d-none d-lg-block m-l-15"><i class="fas fa-upload"></i> <?=UPLOAD?></a>
							
							<a href="javascript:void(0)" class="btn btn-info d-none d-lg-block m-l-15" id="UPDATE_BTN" style="display:none !important" onclick="show_update_popup()" ><?=UPDATE?></a> <!-- Ticket # 1487 -->
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_term_master"
										toolbar="#tb" pagination="true" pageSize = 25 >
											<thead>
												<tr>
													<th field="PK_TERM_MASTER" width="150px" hidden="true" sortable="true" ></th>
													<th field="BEGIN_DATE" width="150px" align="left" sortable="true" ><?=BEGIN_DATE?></th>
													<th field="END_DATE" width="150px" align="left" sortable="true" ><?=END_DATE?></th>
													<th field="TERM_DESCRIPTION" width="150px" align="left" sortable="true" ><?=DESCRIPTION?></th>
													<th field="CAMPUS_CODE" width="350px" align="left" sortable="true" ><?=CAMPUS?></th>
													<th field="TERM_GROUP" width="150px" align="left" sortable="true" ><?=GROUP?></th>
													<th field="ALLOW_ONLINE_ENROLLMENT" width="200px" align="left" sortable="true" ><?=ALLOW_ONLINE_ENROLLMENT?></th>
													<th field="LMS_ACTIVE" width="100px" align="left" sortable="true" ><?=LMS_ACTIVE?></th>
													
													<!-- Ticket # 1487 -->
													<th field="SELECT" width="20px" sortable="true" >
														<input type="checkbox" id="CHECK_ALL" onclick="select_all()" >
													</th>
													<!-- Ticket # 1487 -->
													
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
					if(field != 'ACTION' && field != 'SELECT' ){ //Ticket # 1487
						var selected_row = $('#tt').datagrid('getSelected');
						window.location.href='term_master?id='+selected_row.PK_TERM_MASTER;
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
			var data  = 'id='+id+'&type=term_master';
			var value = $.ajax({
				url: "ajax_check_delete",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					if(data == "a") {
						$("#deleteModal").modal()
						$("#DELETE_ID").val(id)
					} else {
						alert("Cannot Delete as this Data is Used on Other Tables")
						$("#DELETE_ID").val('')
					}
				}		
			}).responseText;
		});
	}
	function conf_delete(val,id){
		if(val == 1)
			window.location.href = 'manage_term_master?act=del&id='+$("#DELETE_ID").val();
		else
			$("#deleteModal").modal("hide");
	}

	function refresh_win(win){
		win.close();
		doSearch()
	}
	/************************ Ticket # 1487 ***********************/
	function select_all(type){
		
		var str = '';
		if(document.getElementById('CHECK_ALL').checked == true) {
			str = true;
		} else {
			str = false;
		}
		
		var CHK_PK_TERM_MASTER = document.getElementsByName('CHK_PK_TERM_MASTER[]')
		for(var i = 0 ; i < CHK_PK_TERM_MASTER.length ; i++){
			CHK_PK_TERM_MASTER[i].checked = str
		}
		
		show_btn()
	}
	
	function show_btn(){
		var flag = 0;
		var CHK_PK_TERM_MASTER = document.getElementsByName('CHK_PK_TERM_MASTER[]')
		for(var i = 0 ; i < CHK_PK_TERM_MASTER.length ; i++){
			if(CHK_PK_TERM_MASTER[i].checked == true){
				flag = 1;
				break;
			}
		}
		
		if(flag == 1)
			document.getElementById('UPDATE_BTN').style.cssText  = '';
		else
			document.getElementById('UPDATE_BTN').style.cssText = 'display:none !important;';
	}
	
	function show_update_popup(){
		var w = 700;
		var h = 550;
		// var id = common_id;
		var left = (screen.width/2)-(w/2);
		var top = (screen.height/2)-(h/2);
		var ids = '';
		var CHK_PK_TERM_MASTER = document.getElementsByName('CHK_PK_TERM_MASTER[]')
		for(var i = 0 ; i < CHK_PK_TERM_MASTER.length ; i++){
			if(CHK_PK_TERM_MASTER[i].checked == true){
				if(ids != '')
					ids += ','
				ids += CHK_PK_TERM_MASTER[i].value
			}
		}

		if(ids != '') {
			var parameter = 'toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,width='+w+', height='+h+', top='+top+', left='+left;
			window.open('term_master_update?id='+ids,'',parameter);
			return false;
		}
	}
	function refresh_win(win){
		win.close();
		window.location.href = 'manage_term_master';
	}
	/************************ Ticket # 1487 ***********************/
	
	</script>

</body>

</html>