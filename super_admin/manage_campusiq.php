<? 
require_once("../global/config.php"); 

if ($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1) { 
	header("location:../index");
	exit;
}

// SYNC AWS DASHBOARDS (act=sync)
if (isset($_GET['act']) && $_GET['act'] == 'sync') {

	$apiUrl = "https://campusiq.diamondsis.io/api/v1/test/list-dashboards?token=campusiq-diamondsis-05122025"; // Ajusta si es otra URL

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $apiUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);

	$response = curl_exec($ch);

	if ($response === false) {
		// Error en cURL, podrías guardar en sesión un mensaje si quieres
		// $_SESSION['MSG_ERROR'] = 'Error connecting to AWS endpoint: ' . curl_error($ch);
		curl_close($ch);
		header("location:manage_campusiq");
		exit;
	}

	curl_close($ch);

	$data = json_decode($response, true);

	if (!is_array($data) || empty($data['success'])) {
		// Respuesta inválida
		// $_SESSION['MSG_ERROR'] = 'Invalid response from AWS endpoint.';
		header("location:manage_campusiq");
		exit;
	}

	$dashboards = $data['dashboards'] ?? [];
	$now = date("Y-m-d H:i:s");
	$adminId = (int)($_SESSION['ADMIN_PK_USER'] ?? 1);



	foreach ($dashboards as $d) {

		$dashboardId = $d['DashboardId'] ?? '';
		$name        = $d['Name'] ?? '';

		if ($dashboardId == '') {


			continue;
		}

		// Verificar si ya existe por DASHBOARDID
		$sqlCheck = "
			SELECT PK_DASHBOARD 
			FROM CAMPUSIQ_DASHBOARDS 
			WHERE DASHBOARDID = '" . ($dashboardId) . "'
			LIMIT 1
		";
		$rsCheck = $db->Execute($sqlCheck);


		if ($rsCheck && !$rsCheck->EOF) {
			// Ya existe, no insertamos
			continue;
		}

		// Insertar nuevo dashboard
		$sqlInsert = "
			INSERT INTO CAMPUSIQ_DASHBOARDS 
			(DASHBOARDID, ACTIVE, CREATED_ON, CREATED_BY, EDITED_ON, EDITED_BY, NAME)
			VALUES (
				'" . $dashboardId . "',
				1,
				'" . $now . "',
				" . $adminId . ",
				'" . $now . "',
				" . $adminId . ",
				'" . $name . "'
			)
		";
		$resultado = $db->Execute($sqlInsert);
	}

	// Después de sincronizar, regresamos a la misma pantalla
	header("location:manage_campusiq");
	exit;
}

// Eliminar registro de CAMPUSIQ_DASHBOARDS
if (isset($_GET['act']) && $_GET['act'] == 'del' && isset($_GET['id'])) {
	$PK_DASHBOARD = (int)$_GET['id'];
	$db->Execute("DELETE FROM CAMPUSIQ_DASHBOARDS WHERE PK_DASHBOARD = '$PK_DASHBOARD' ");
	header("location:manage_campusiq");
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
	<title>Dashboards | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Dashboards</h4>
                    </div>
					<div class="col-md-3 align-self-center text-right">
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; Search"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div>  
                    <div class="col-md-3 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
							<!-- NUEVO: botón Sync AWS Dashboard -->
							<a href="manage_campusiq?act=sync" class="btn btn-warning d-none d-lg-block m-l-15">
								<i class="fa fa-cloud-download"></i> Sync AWS Dashboard
							</a>

                            <a href="campusiq_dashboard" class="btn btn-info d-none d-lg-block m-l-15">
								<i class="fa fa-plus-circle"></i> Create New
							</a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped="true" 
											class="easyui-datagrid table table-bordered table-striped" 
											url="grid_campusiq_dashboards"
											toolbar="#tb" pagination="true" pageSize="25">
											<thead>
												<tr>
													<th field="PK_DASHBOARD" width="80px" hidden="true" sortable="true"></th>
													<th field="NAME" width="400px" align="left" sortable="true">Name</th>
													<th field="DASHBOARDID" width="400px" align="left" sortable="true">Dashboard ID</th>
													<th field="ACTIVE" width="100px" align="center" sortable="true">Active</th>
													<th field="ACTION" width="120px" align="center" sortable="false">Options</th>
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
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							Are you sure you want to delete this record?
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info">Yes</button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)">No</button>
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
				SEARCH  : $('#SEARCH').val()
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
					if(field != 'ACTION'){
						var selected_row = $('#tt').datagrid('getSelected');
						window.location.href = 'campusiq_dashboard?id=' + selected_row.PK_DASHBOARD;
					}
				}
			});
			
			$('#tt').datagrid({
				view: $.extend(true,{},$.fn.datagrid.defaults.view,{
					onAfterRender: function(target){
						$.fn.datagrid.defaults.view.onAfterRender.call(this,target);
						$('.datagrid-header-inner').width('100%');
						$('.datagrid-btable').width('100%');
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
		}); 
	});
	function delete_row(id){
		jQuery(document).ready(function($) {
			$("#deleteModal").modal();
			$("#DELETE_ID").val(id);
		});
	}
	function conf_delete(val){
		if(val == 1){
			window.location.href = 'manage_campusiq?act=del&id=' + $("#DELETE_ID").val();
		} else {
			$("#DELETEModal").modal("hide");
		}
	}
	</script>

</body>

</html>
