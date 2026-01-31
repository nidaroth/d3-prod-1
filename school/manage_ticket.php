<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/ticket.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '') { 
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
	<title><?=TICKET_PAGE_TITLE?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-4 align-self-center">
                        <h4 class="text-themecolor"><?=TICKET_PAGE_TITLE?> </h4>
                    </div>
					<div class="col-md-3 align-self-center text-right">
                       <select name="PK_TICKET_STATUS" id="PK_TICKET_STATUS" class="form-control" style="margin-top: 10px;" onchange="doSearch()" >
							<option value=""><?=STATUS?></option>
							<? $res_dep = $db->Execute("select PK_TICKET_STATUS,TICKET_STATUS from Z_TICKET_STATUS WHERE ACTIVE = '1' ORDER BY TICKET_STATUS ASC ");
							while (!$res_dep->EOF) { ?>
								<option value="<?=$res_dep->fields['PK_TICKET_STATUS']?>" ><?=$res_dep->fields['TICKET_STATUS']?></option>
							<?	$res_dep->MoveNext();
							} 	?>
						</select>
					</div> 
					<div class="col-md-3 align-self-center text-right">
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div>  
                    <div class="col-md-2 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <a href="ticket" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
											<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_ticket" toolbar="#tb" rownumbers="false" pagination="true" pageSize="25" <?=$sort?> autoRowHeight="true" >
											<thead>
												<tr>
													<th field="PK_TICKET_PRIORITY" width="150px" hidden="true" ></th>
													<th field="PK_TICKET" width="150px" hidden="true" ></th>
													<th field="INTERNAL_ID" width="150px" hidden="true" ></th>
													
													<th field="TICKET_NO" width="80px" sortable="true" ><?=TICKET_NO?></th>
													<th field="SUBJECT" width="220px" sortable="true" align="left" ><?=SUBJECT?></th>
													<th field="TICKET_PRIORITY" width="80px" sortable="true" align="left" ><?=PRIORITY?></th>
													<th field="TICKET_STATUS" width="90px" sortable="true" align="left" ><?=STATUS?></th>
													
													<th field="LAST_UPDATE_ON" width="150px" sortable="false" ><?=LAST_UPDATE_ON?></th>
													<th field="LAST_UPDATE_BY" width="150px" sortable="false" ><?=LAST_UPDATE_BY?></th>
													
													<th field="CREATED_DATE" width="120px" sortable="true" ><?=CREATED_ON?></th>
													<th field="NAME" width="90px" sortable="true" ><?=CREATED_BY?></th>
													<th field="ACTION" width="90px" sortable="false" align="left" ><?=OPTION?></th>
													
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
					SEARCH  			: $('#SEARCH').val(),
					PK_TICKET_STATUS  	: $('#PK_TICKET_STATUS').val(),
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
							window.location.href = 'view_ticket?id='+selected_row.INTERNAL_ID;
						}
					}
				});
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
		jQuery(document).ready(function($) {
			$(window).resize(function() {
				$('#tt').datagrid('resize');
				$('#tb').panel('resize');
			});
		});
	</script>

</body>

</html>