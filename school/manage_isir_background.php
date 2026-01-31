<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/isir_student.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_FINANCE') == 0 ){
	header("location:../index");
	exit;
}
if(isset($_GET['id'])){
	$db->Execute("DELETE FROM S_ISIR_BACKGROUND_PROCESS WHERE PK_ISIR_BACKGROND_PROCESS = '$_GET[id]' and STATUS = 1 and PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	header("location:manage_isir_background");
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
	<title><?=ISIR_PAGE_TITLE_BACKGROUND?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles d-flex align-items-center">
                    <div class="col-md-3 align-self-center">
                        <h4 class="text-themecolor"><?=ISIR_PAGE_TITLE_BACKGROUND?> </h4>
                    </div>


					<div class="col-md-2 align-self-center" style="max-width: 13.999%;" >
						<select id="STATUS" name="STATUS" class="form-control" onchange="doSearch()" >
							<option value="" selected>-- ALL --</option>
							<option value="1" >NEW</option>
							<option value="2" >IN PROGRESS</option>
							<option value="3" >DONE</option>
						</select>
					</div>

					<div class="col-md-4 align-self-center text-right d-flex align-items-center" style="margin-bottom: 10px">
                        <div class="d-none">
                        	<input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome" onkeypress="search(event)">
                        </div>
                        <a class="btn btn-info d-none d-lg-block m-l-15" href="nubo_isir_student_background"><i class="fas fa-upload"></i> <?=UPLOAD?></a>
                        <a class="btn btn-default d-none d-lg-block m-l-15" href="manage_isir"><i class="fas fa-upload"></i> Result Page</a>
					</div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" 
										url="grid_isir_background" toolbar="#tb" loadMsg="Processing, please wait..." pagination="true" pageSize = 25 data-options="
											onClickCell: function(rowIndex, field, value){
												//alert('here');
											},
											onLoadSuccess: function(){
												//alert('here')
											}
											"
										>
											<thead>
												<tr>
													<th field="PK_ISIR_BACKGROND_PROCESS" width="150px" hidden="true" sortable="true" ></th>
													<th field="FILE" width="335px" align="left" sortable="true" >File Name</th>
													<th field="STATUS" width="150px"  sortable="true" >Upload Status</th>
													<th field="CREATED_ON" width="250px" align="left" sortable="true" >Upload Date</th>
													<th field="EXECUTING_FINISH_DATE" width="250px" align="left" sortable="true" >Upload Ready</th>
													<th field="CREATED_BY_NAME" width="160px" align="left" sortable="true" >User</th>
													<th field="EMAIL" width="250px"  sortable="true" >Email</th>
													<th field="EXECUTING_START_DATE" hidden="true" width="150px" align="left" sortable="true" >Execugint start date</th>
													<th field="ACTION" width="100px" hidden="true" align="left" sortable="false" ><?=OPTIONS?></th>
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

	<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="exampleModalLabel1">Delete Confirmation</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="form-group" id="delete_message" ></div>
					<input type="hidden" id="DELETE_ID" value="0" />
					<input type="hidden" id="DELETE_TYPE" value="0" />
				</div>
				<div class="modal-footer">
					<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info">Yes</button>
					<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" >No</button>
				</div>
			</div>
		</div>
	</div>
	
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
			$('#tt').datagrid('load',{
				SEARCH  : $('#SEARCH').val(),
				STATUS   		: $('#STATUS').val(),
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
			// 			window.location.href='isir?id='+selected_row.PK_ISIR_STUDENT_MASTER+'&iid='+selected_row.PK_ISIR_SETUP_MASTER+'&sid=<?=$_GET['id']?>';
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

	function delete_row(id,type){
		jQuery(document).ready(function($) {
			document.getElementById('delete_message').innerHTML = 'Are you sure you want to Delete this Record?';
				
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
		});
	}
	function conf_delete(val,id){
		jQuery(document).ready(function($) {
			if(val == 1) {
				var id = $("#DELETE_ID").val();
				window.location.href = 'manage_isir_background?id='+id;
			} else
				$("#deleteModal").modal("hide");
		});
	}


	function show_popup(){
		jQuery(document).ready(function($) {
			$("#popupmodal").modal()
		});
	}

	<?php if(isset($_GET['showpopup'])){ ?>
		show_popup();
		function removeURLParameter(parameter) {
		    const url = new URL(window.location); // Obtenemos la URL actual
		    url.searchParams.delete(parameter);   // Eliminamos el parámetro
		    window.history.replaceState({}, '', url); // Actualizamos la URL sin recargar
		}

		// Ejemplo: eliminar el parámetro "foo"
		removeURLParameter('showpopup');
	<?php	} ?>
	</script>

	
	<div class="modal" id="popupmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="exampleModalLabel1">Information</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<p class="lead">
						The ISIR upload is currently in progress. <br> You will receive an email notification once the process is complete.
					</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

</body>

</html>