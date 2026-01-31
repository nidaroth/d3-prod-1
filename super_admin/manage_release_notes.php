<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}
if($_GET['act'] == 'del')	{
	$db->Execute("DELETE FROM Z_RELEASE_NOTES WHERE PK_RELEASE_NOTES = '$_GET[id]' ");;
	header("location:manage_release_notes");
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
	<title>Release Notes | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<style>
	.datagrid-header td{font-size: 13px !important; vertical-align: bottom;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row ">
                    <div class="col-md-2 align-self-center" style="flex: 0 0 10%; max-width: 10%;" >
                        <h4 class="text-themecolor">Release Notes </h4>
                    </div>
					
					<div class="col-md-2 align-self-center text-right">
                       <select id="PK_RELEASE_TYPE" name="PK_RELEASE_TYPE" class="form-control" style="margin-top:5px;" onchange="doSearch()" >
							<option value="" >All Types</option>
							<? $res_type = $db->Execute("select PK_RELEASE_TYPE,RELEASE_TYPE from M_RELEASE_TYPE WHERE ACTIVE = 1 order by RELEASE_TYPE ASC");
							while (!$res_type->EOF) { 
								$selected 			= "";
								$PK_RELEASE_TYPE 	= $res_type->fields['PK_RELEASE_TYPE']; 
								/* foreach($PK_RELEASE_CATEGORY_ARR as $PK_RELEASE_CATEGORY1){
									if($PK_RELEASE_CATEGORY1 == $PK_RELEASE_TYPE) {
										$selected = 'selected';
										break;
									}
								}*/ ?>
								<option value="<?=$PK_RELEASE_TYPE?>" <?=$selected?> ><?=$res_type->fields['RELEASE_TYPE'] ?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div> 
					
					<div class="col-md-2 align-self-center text-right">
                       <select id="PK_RELEASE_CATEGORY" name="PK_RELEASE_CATEGORY" class="form-control" style="margin-top:5px;" onchange="doSearch()" >
							<option value="" >All Categories</option>
							<? $res_type = $db->Execute("select PK_RELEASE_CATEGORY,RELEASE_CATEGORY from M_RELEASE_CATEGORY WHERE ACTIVE = 1 order by RELEASE_CATEGORY ASC");
							while (!$res_type->EOF) { 
								$selected 			= "";
								$PK_RELEASE_CATEGORY 	= $res_type->fields['PK_RELEASE_CATEGORY']; 
								/* foreach($PK_RELEASE_CATEGORY_ARR as $PK_RELEASE_CATEGORY1){
									if($PK_RELEASE_CATEGORY1 == $PK_RELEASE_CATEGORY) {
										$selected = 'selected';
										break;
									}
								}*/ ?>
								<option value="<?=$PK_RELEASE_CATEGORY?>" <?=$selected?> ><?=$res_type->fields['RELEASE_CATEGORY'] ?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div>  
					
					<div class="col-md-2 align-self-center text-right">
                       <select id="DATE_FITER_TYPE" name="DATE_FITER_TYPE" class="form-control" style="margin-top:5px;" onchange="doSearch()" >
							<option value="1" >Date Programming Pushed to D3</option>
							<option value="2" >Date Release Notes Pushed to D3</option>
						</select>
					</div> 
					
					<div class="col-md-2 align-self-center text-right" style="flex: 0 0 10%; max-width: 10%;" >
						<input type="text" class="form-control date" id="START_DATE" name="START_DATE" placeholder="From Date" value="" onchange="doSearch()" >
					</div> 
					
					<div class="col-md-2 align-self-center text-right" style="flex: 0 0 10%; max-width: 10%;" >
						<input type="text" class="form-control date" id="TO_DATE" name="TO_DATE" placeholder="To Date" value="" onchange="doSearch()" >
					</div> 
					
					<div class="col-md-2 align-self-center text-right" >
						<select id="PUSHED_TO_D3" name="PUSHED_TO_D3" class="form-control" style="margin-top:5px;" onchange="doSearch()" >
							<option value="" >Release Notes Pushed</option>
							<option value="1" >Yes</option>
							<option value="2" >No</option>
						</select>
					</div> 
                </div>
				<div class="row page-titles" style="padding-top:0" >
                    <div class="col-md-2 align-self-center" style="flex: 0 0 10%; max-width: 10%;" ></div>
					
					<div class="col-md-2 align-self-center text-right">
                        <input type="text" class="form-control" id="SUBJECT" name="SUBJECT" placeholder="&#xF002; Subject"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div>  
					<div class="col-md-2 align-self-center text-right">
                        <input type="text" class="form-control" id="LOCATION" name="LOCATION" placeholder="&#xF002; Location"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div> 
					
					 <div class="col-md-2 align-self-center text-right">
                        <input type="text" class="form-control" id="KNOWLEDGEBASE_URL" name="KNOWLEDGEBASE_URL" placeholder="&#xF002; Knowledge Base ID"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div> 
					
                    <div class="col-md-2 align-self-center text-right" style="flex: 0 0 20%; max-width: 20%;" >
                        <input type="text" class="form-control" id="RELEASE_NOTES" name="RELEASE_NOTES" placeholder="&#xF002; Release Notes"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div> 
					
					<div class="col-md-2 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
							<a href="release_notes_excel" class="btn btn-info d-none d-lg-block m-l-15">Excel</a>
							
                            <a href="release_notes" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</a>
                        </div>
                    </div>
					
				</div>  
				
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_release_notes"
										toolbar="#tb" pagination="true" pageSize = 25 >
											<thead>
												<tr>
													<th field="PK_RELEASE_NOTES" width="150px" hidden="true" sortable="true" ></th>
													<th field="RELEASE_TYPE" width="140px" align="left" sortable="true" >Type</th>
													<th field="CATEGORY" width="140px" align="left" sortable="true" >Category</th>
													<th field="PUSHED_TO_D3_DATE" width="100px" align="left" sortable="true" >Date<br />Programming<br />Pushed to D3</th>
													<th field="SUBJECT" width="200px" align="left" sortable="true" >Subject</th>
													<th field="LOCATION" width="200px" align="left" sortable="true" >Location</th>
													<th field="RELEASE_NOTES_PUSHED" width="70px" align="left" sortable="true" >Release<br />Notes<br />Pushed</th>
													<th field="RELEASE_NOTES_PUSHED_DATE" width="100px" align="left" sortable="true" >Date<br />Release Notes<br />Pushed to D3</th>
													<th field="KNOWLEDGEBASE_URL" width="150px" align="left" sortable="true" >Knowledge<br />Base ID</th>
													<th field="PROGRAMMING_NOTES" width="200px" align="left" sortable="true" >Programming Notes</th>
													<th field="ACTION" width="100px" align="center" sortable="false" >Options</th>
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
						<h4 class="modal-title" id="exampleModalLabel1">Delete Confirmation</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							Are you sure want to Delete this Record?
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info">Yes</button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" >No</button>
					</div>
				</div>
			</div>
		</div>
    </div>
	<? require_once("js.php"); ?>
	
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
	</script>
	
	<script type="text/javascript">
	function doSearch(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid('load',{
				PK_RELEASE_CATEGORY  : $('#PK_RELEASE_CATEGORY').val(),
				PK_RELEASE_TYPE  	 : $('#PK_RELEASE_TYPE').val(),
				SUBJECT  		 	 : $('#SUBJECT').val(),
				LOCATION  		 	 : $('#LOCATION').val(),
				RELEASE_NOTES  	 	 : $('#RELEASE_NOTES').val(),
				DATE_FITER_TYPE  	 : $('#DATE_FITER_TYPE').val(),
				START_DATE  	 	 : $('#START_DATE').val(),
				TO_DATE  		 	 : $('#TO_DATE').val(),
				
				PUSHED_TO_D3  		 : $('#PUSHED_TO_D3').val(),
				KNOWLEDGEBASE_URL  	 : $('#KNOWLEDGEBASE_URL').val(),
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
						window.location.href='release_notes?id='+selected_row.PK_RELEASE_NOTES;
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
			window.location.href = 'manage_release_notes?act=del&id='+$("#DELETE_ID").val();
		else
			$("#deleteModal").modal("hide");
	}
	</script>

</body>

</html>