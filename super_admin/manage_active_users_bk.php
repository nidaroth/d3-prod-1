<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
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
	<title>Active Users | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-2 align-self-center">
                        <h4 class="text-themecolor">Active Users </h4>
                    </div>
					<div class="col-md-2 align-self-center text-right">
                       <select name="PK_ACCOUNT" id="PK_ACCOUNT" class="form-control" style="margin-top: 10px;" onchange="doSearch()" >
							<option value="">All School</option>
							<? $res_dep = $db->Execute("select PK_ACCOUNT,SCHOOL_NAME from Z_ACCOUNT WHERE ACTIVE = '1' ORDER BY SCHOOL_NAME ASC ");
							while (!$res_dep->EOF) { ?>
								<option value="<?=$res_dep->fields['PK_ACCOUNT']?>" ><?=$res_dep->fields['SCHOOL_NAME']?></option>
							<?	$res_dep->MoveNext();
							} 	?>
						</select>
					</div> 
					<div class="col-md-2 align-self-center text-right">
                       <select name="USER_TYPE" id="USER_TYPE" class="form-control" style="margin-top: 10px;" onchange="doSearch()" >
							<option value="">All User Type</option>
							<option value="1">School User</option>
							<option value="2">Faculty</option>
							<option value="3">Student</option>
						</select>
					</div> 
					<div class="col-md-2 align-self-center text-right">
                        <input type="text" class="form-control date" id="START_DATE" name="START_DATE" placeholder="From Date" onchange="doSearch()">
					</div>  
                    <div class="col-md-2 align-self-center text-right">
                        <input type="text" class="form-control date" id="TO_DATE" name="TO_DATE" placeholder="To Date" onchange="doSearch()">
                    </div>
					<div class="col-md-2 align-self-center text-right">
						<button type="button" onclick="generate_excel()" class="btn waves-effect waves-light btn-info">Export To Excel</button>
					</div> 
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_active_users"
										toolbar="#tb" pagination="true" pageSize = 25 >
											<thead>
												<tr>
													<th field="PK_ACTIVE_USERS" width="150px" hidden="true" sortable="true" ></th>
													<th field="DATE" width="100px" align="left" sortable="true" >Date</th>
													<th field="SCHOOL_NAME" width="400px" align="left" sortable="true" >School Name</th>
													<th field="USER_TYPE" width="200px" align="left" sortable="true" >User Type</th>
													<th field="NAME" width="300px" align="left" sortable="true" >Name</th>
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
	
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script type="text/javascript" src="../backend_assets/dist/js/jquery.easyui.min.js"></script>
	<script src="../backend_assets/dist/js/jquery-ui.js"></script> 
	<script type="text/javascript">
	function doSearch(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid('load',{
				PK_ACCOUNT  : $('#PK_ACCOUNT').val(),
				START_DATE  : $('#START_DATE').val(),
				TO_DATE  	: $('#TO_DATE').val(),
				USER_TYPE	: $('#USER_TYPE').val(),
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
			/*$('#tt').datagrid({
				onClickCell: function(rowIndex, field, value){
					$('#tt').datagrid('selectRow',rowIndex);
					if(field != 'ACTION' ){
						var selected_row = $('#tt').datagrid('getSelected');
						window.location.href='session?id='+selected_row.PK_SESSION_MASTER;
					}
				}
			});*/
			
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
			window.location.href = 'manage_session?act=del&id='+$("#DELETE_ID").val();
		else
			$("#deleteModal").modal("hide");
	}
	function generate_excel(){
		jQuery(document).ready(function($) {
			window.location.href = "active_user_excel.php?type="+$("#USER_TYPE").val()+"&acc="+$("#PK_ACCOUNT").val()+"&st="+$("#START_DATE").val()+"&ed="+$("#TO_DATE").val()
		});
	}
	</script>
	
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
</body>

</html>