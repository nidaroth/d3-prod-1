<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/user_activity.php");
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
	<title><?=USER_ACTIVITY_TITLE?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-2 align-self-center" style="max-width: 11.3333%;flex: 11.3333%;" >
                        <h4 class="text-themecolor"><?=USER_ACTIVITY_TITLE?> </h4>
                    </div>
					<div class="col-md-2 align-self-center" >
						<select name="PK_ACCOUNT" id="PK_ACCOUNT" class="form-control " onchange="doSearch()" style="margin-top:10px" >
							<option value="">All Accounts</option>
							<? $res_dep = $db->Execute("select PK_ACCOUNT,SCHOOL_NAME from Z_ACCOUNT WHERE ACTIVE = '1' AND PK_ACCOUNT != 1 ORDER BY SCHOOL_NAME ASC ");
							while (!$res_dep->EOF) {  ?>
								<option value="<?=$res_dep->fields['PK_ACCOUNT']?>" ><?=$res_dep->fields['SCHOOL_NAME']?></option>
							<?	$res_dep->MoveNext();
							} 	?>
						</select>
					</div>
					<div class="col-md-1 align-self-center text-right" style="max-width: 11.3333%;flex: 11.3333%;"  >
                       <input type="text" class="form-control date" id="FROM_LOGIN_DATE" name="FROM_LOGIN_DATE" placeholder="<?=FROM_LOGIN_DATE?>" onchange="doSearch()">
					</div> 
					<div class="col-md-1 align-self-center text-right" style="max-width: 11.3333%;flex: 11.3333%;"  >
                         <input type="text" class="form-control date" id="TO_LOGIN_DATE" name="TO_LOGIN_DATE" placeholder="<?=TO_LOGIN_DATE?>" onchange="doSearch()">
					</div> 
					<div class="col-md-2 align-self-center">
						<select id="USER_TYPE" name="USER_TYPE[]" multiple class="form-control" onchange="doSearch()" >
							<option value="1" selected >School Admin</option>
							<option value="2" selected >Employee</option>
							<option value="3" selected >Faculty</option>
							<option value="4" selected >Student</option>
						</select>
					</div>
					<div class="col-md-2 align-self-center text-right">
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div>  
					<div class="col-md-2 align-self-center" style="max-width: 11.3333%;flex: 11.3333%;"  >
						<button onclick="javascript:window.location.href = 'user_activity_pdf'" type="button" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
						<button onclick="javascript:window.location.href = 'user_activity_excel'" type="button" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
					</div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_user_activity"
										toolbar="#tb" pagination="true" pageSize = 25 >
											<thead>
												<tr>
													<th field="PK_LOGIN_HISTORY" width="150px" hidden="true" sortable="true" ></th>
													<th field="SCHOOL_NAME" width="200px" align="left" sortable="true" >Account name</th>
													<th field="ROLES" width="200px" align="left" sortable="true" ><?=USER_TYPE?></th>
													<th field="NAME" width="200px" align="left" sortable="true" ><?=USER_NAME?></th>
													<th field="USER_ID" width="200px" align="left" sortable="true" ><?=LOGIN_ID?></th>
													<th field="LOGIN_TIME" width="250px" align="left" sortable="true" ><?=LOGIN_TIME?></th>
													<th field="LOGOUT_TIME" width="250px" align="left" sortable="true" ><?=LOGOUT_TIME?></th>
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
				SEARCH  		: $('#SEARCH').val(),
				USER_TYPE 		: $('#USER_TYPE').val(),
				FROM_LOGIN_DATE	: $('#FROM_LOGIN_DATE').val(),
				TO_LOGIN_DATE	: $('#TO_LOGIN_DATE').val(),
				PK_ACCOUNT		: $('#PK_ACCOUNT').val(),
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
						//window.location.href='funding?id='+selected_row.PK_FUNDING;
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
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#USER_TYPE').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=USER_TYPES?>',
				nonSelectedText: '<?=USER_TYPES?>',
				numberDisplayed: 1,
				nSelectedText: '<?=USER_TYPES?> selected'
			});
		});
	</script>	
</body>

</html>