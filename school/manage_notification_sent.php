<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/notification_sent.php");
require_once("check_access.php");

if(check_access('SETUP_COMMUNICATION') == 0 ){
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
	<title><?=NOTIFICATION_SENT_PAGE_TITLE ?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-3 align-self-center">
                        <h4 class="text-themecolor"><?=NOTIFICATION_SENT_PAGE_TITLE ?> </h4>
                    </div>
					 
					 <div class="col-md-3 align-self-center text-right">
                        <select name="PK_EVENT_TYPE" id="PK_EVENT_TYPE" class="form-control " onchange="doSearch()" >
							<option value=""><?=NOTIFICATION_TYPE?></option>
							<? $res_dd = $db->Execute("select * from Z_EVENT_TYPE WHERE ACTIVE = '1' ORDER BY EVENT_TYPE ASC ");
							while (!$res_dd->EOF) { ?>
								<option value="<?=$res_dd->fields['PK_EVENT_TYPE']?>" <? if($res_dd->fields['PK_EVENT_TYPE'] == $PK_EVENT_TYPE) echo 'selected = "selected"';?> ><?=$res_dd->fields['EVENT_TYPE']?></option>
							<?	$res_dd->MoveNext();
							}	?>
						</select>
                    </div>
                    <div class="col-md-3 align-self-center text-right">
                        <select id="PK_EMPLOYEE_MASTER" name="PK_EMPLOYEE_MASTER" class="form-control" onchange="doSearch()">
							<option value="" ><?=NOTIFICATION_TO?></option>
							<? $res_type = $db->Execute("SELECT S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) AS NAME, EMPLOYEE_ID FROM S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  S_EMPLOYEE_MASTER.ACTIVE = 1 ORDER BY CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" ><?=$res_type->fields['NAME']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
                    </div>
					<div class="col-md-3 align-self-center text-right">
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome;margin-top:-8px" onkeypress="search(event)">
					</div> 
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_notification_sent"
										toolbar="#tb" pagination="true" pageSize = 25 >
											<thead>
												<tr>
													<th field="PK_EVENT_TEMPLATE" width="150px" hidden="true" sortable="true" ></th>
													<th field="EVENT_TYPE" width="200px" align="left" sortable="true" ><?=NOTIFICATION_TYPE?></th>
													<th field="TEXT" width="400px" align="left" sortable="true" ><?=NOTIFICATION?></th>
													<th field="CREATED_ON" width="130px" align="left" sortable="true" ><?=DATE.'/'.TIME?></th>
													<th field="NOTIFICATION_TO" width="450px" align="left" sortable="true" ><?=NOTIFICATION_TO?></th>
													<th field="ACTION" width="80px" align="left" sortable="true" ><?=OPTION?></th>
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

	<? require_once("js.php"); ?>
	
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script type="text/javascript" src="../backend_assets/dist/js/jquery.easyui.min.js"></script>
	<script src="../backend_assets/dist/js/jquery-ui.js"></script> 
	<script type="text/javascript">
	function doSearch(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid('load',{
				PK_EVENT_TYPE  		: $('#PK_EVENT_TYPE').val(),
				PK_EMPLOYEE_MASTER  : $('#PK_EMPLOYEE_MASTER').val(),
				SEARCH  			: $('#SEARCH').val(),
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
						//window.location.href='notification_sent?id='+selected_row.PK_EVENT_TEMPLATE;
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
	</script>

</body>

</html>