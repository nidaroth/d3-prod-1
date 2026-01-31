<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/fee_type.php");
require_once("check_access.php");
require_once('custom_excel_generator.php');
if ($_GET['act'] == 'del') {
	$db->Execute("DELETE FROM S_LEDGER_CODE_GROUP WHERE PK_LEDGER_CODE_GROUP = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	header("location:manage_ledger_code_group");
}
if ($_REQUEST['ACTION'] == 'exportexcel') {
	header('Content-Type: application/json; charset=utf-8');
	$ledger_groups = $db->Execute("SELECT * FROM S_LEDGER_CODE_GROUP WHERE PK_ACCOUNT = $_SESSION[PK_ACCOUNT] ORDER BY LEDGER_CODE_GROUP");
	$data = [];
	$header =  ['Ledger Code Group', 'Ledger Code Group Description', 'Ledger Codes', 'Active', 'Created By', 'Created On' , 'Edited By' , 'Edited On'];
	$data[] = ['*bold*' => $header];
	while (!$ledger_groups->EOF) {
		# code...
		//$data_row['PK_LEDGER_CODE_GROUP'] = $ledger_groups->fields['PK_LEDGER_CODE_GROUP'];
		$data_row['LEDGER_CODE_GROUP'] = $ledger_groups->fields['LEDGER_CODE_GROUP'];
		$data_row['LEDGER_CODE_GROUP_DESC'] = $ledger_groups->fields['LEDGER_CODE_GROUP_DESC'];
		// $data_row['PK_AR_LEDGER_CODES'] = $ledger_groups->fields['PK_AR_LEDGER_CODES'];
		if ($ledger_groups->fields['PK_AR_LEDGER_CODES'] != '') {
			$sub_query = $db->Execute("SELECT GROUP_CONCAT(CODE) AS CODES FROM M_AR_LEDGER_CODE WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE IN (" . $ledger_groups->fields['PK_AR_LEDGER_CODES'] . ")");
			$data_row['LEDGER_CODE_GROUP_CODES'] = $sub_query->fields['CODES'] ?? '';
		} else {
			$data_row['LEDGER_CODE_GROUP_CODES'] = '';
		}

		if ($ledger_groups->fields['ACTIVE'] == 1) {
			$data_row['ACTIVE'] = "Active";
		} else {
			$data_row['ACTIVE'] = "Inactive";
		}
		//$data_row['PK_ACCOUNT'] = $ledger_groups->fields['PK_ACCOUNT'];
		$res_usr_name = $db->Execute("SELECT CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME FROM S_EMPLOYEE_MASTER,Z_USER WHERE Z_USER.PK_USER = '".$ledger_groups->fields['CREATED_BY']."' AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER");
		$data_row['CREATED_BY'] = $res_usr_name->fields['NAME'];

		$data_row['CREATED_ON'] =  date("m/d/Y", strtotime($ledger_groups->fields['CREATED_ON']));
		if($ledger_groups->fields['EDITED_BY'] != ''){
			$edited_by_name = $db->Execute("SELECT CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME FROM S_EMPLOYEE_MASTER,Z_USER WHERE Z_USER.PK_USER = '".$ledger_groups->fields['EDITED_BY']."' AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER");
			$data_row['EDITED_BY'] = $edited_by_name->fields['NAME'];
		}else{
			$data_row['EDITED_BY'] = '';
		}
		if( $ledger_groups->fields['EDITED_ON'] != '0000-00-00 00:00:00')
		{
			$data_row['EDITED_ON'] = date('m/d/Y', strtotime($ledger_groups->fields['EDITED_ON']));
		}else{
			$data_row['EDITED_ON'] = '';
		}
		$data[] = $data_row;
		$ledger_groups->MoveNext();
	}


	$file_name = 'NC_SARA_State_Authorization.xlsx';

	$outputFileName = $file_name;
	$outputFileName = str_replace(
		pathinfo($outputFileName, PATHINFO_FILENAME),
		pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . floor(microtime(true) * 1000),
		$outputFileName
	);
	// dd($data);
	$output = CustomExcelGenerator::makecustom('Excel2007', 'temp/', $outputFileName, $data);
	// dd("File Generated ", $output);
	$response["file_name"] = $outputFileName;
	$response["path"] =  $output;
	echo json_encode($response);
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
	<style>
		/* DIAM-1422 */
		.lds-ring {
			position: absolute;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			margin: auto;
			width: 64px;
			height: 64px;
		}

		.lds-ring div {
			box-sizing: border-box;
			display: block;
			position: absolute;
			width: 51px;
			height: 51px;
			margin: 6px;
			border: 6px solid #0066ac;
			border-radius: 50%;
			animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
			border-color: #007bff transparent transparent transparent;
		}

		.lds-ring div:nth-child(1) {
			animation-delay: -0.45s;
		}

		.lds-ring div:nth-child(2) {
			animation-delay: -0.3s;
		}

		.lds-ring div:nth-child(3) {
			animation-delay: -0.15s;
		}

		@keyframes lds-ring {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}

		#loaders {
			position: fixed;
			width: 100%;
			z-index: 9999;
			bottom: 0;
			background-color: #2c3e50;
			display: block;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			opacity: 0.6;
			display: none;
		}

		/* DIAM-1422 */
	</style>
	<title>Ledger Code Group | <?= $title ?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
	<? require_once("pre_load.php"); ?>
	<div id="loaders" style="display: none;">
		<div class="lds-ring">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
		</div>
	</div>
	<div id="main-wrapper">
		<? require_once("menu.php"); ?>
		<div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles">
					<div class="col-md-5 align-self-center">
						<h4 class="text-themecolor">Ledger Code Group </h4>
					</div>
					<div class="col-md-3 align-self-center text-right">
						<input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?= SEARCH ?>" style="font-family: FontAwesome" onkeypress="search(event)">
					</div>
					<div class="col-md-2 align-self-center text-right">
						<div class="d-flex justify-content-end align-items-center">
							<button onclick="exportexcel()" class="btn btn-info d-none d-lg-block m-l-15"><?= EXCEL ?></button>
						</div>
					</div>
					<div class=" align-self-center text-right">
						<div class="d-flex justify-content-end align-items-center">
							<a href="ledger_code_group" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?= CREATE_NEW ?></a>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped="true" class="easyui-datagrid table table-bordered table-striped" url="grid_ledger_group" toolbar="#tb" pagination="true" pageSize=25>
											<thead>
												<tr>
													<th field="PK_LEDGER_CODE_GROUP" width="0"  hidden="true" sortable="true"></th>
													<th field="LEDGER_CODE_GROUP" width="303px"  align="left" sortable="true">Ledger Code Group</th>
													<th field="LEDGER_CODE_GROUP_DESC" width="464px"   align="left" sortable="true">Ledger Code Group Description</th>
													<th field="LEDGER_CODE_GROUP_CODES" width="480px"   align="left" sortable="true">Ledger Codes</th>
													<th field="ACTION" width="100px"  align="center" sortable="false"><?= OPTION ?></th>
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
						<h4 class="modal-title" id="exampleModalLabel1"><?= DELETE_CONFIRMATION ?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<?= DELETE_MESSAGE_GENERAL ?>
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?= YES ?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)"><?= NO ?></button>
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
		function doSearch() {
			jQuery(document).ready(function($) {
				$('#tt').datagrid('load', {
					SEARCH: $('#SEARCH').val(),
					fitColumns: true, // Set fitColumns property to true

				});
			});
		}

		function search(e) {
			if (e.keyCode == 13) {
				doSearch();
			}
		}
		$(function() {
			jQuery(document).ready(function($) {
				$('#tt').datagrid({
					fitColumns: true, // Set fitColumns property to true
					onClickCell: function(rowIndex, field, value) {
						$('#tt').datagrid('selectRow', rowIndex);
						if (field != 'ACTION') {
							var selected_row = $('#tt').datagrid('getSelected');
							window.location.href = 'ledger_code_group?id=' + selected_row.PK_LEDGER_CODE_GROUP;
						}
					}
				});

				$('#tt').datagrid({
					fitColumns: true, // Set fitColumns property to true

					view: $.extend(true, {}, $.fn.datagrid.defaults.view, {
						onAfterRender: function(target) {
							$.fn.datagrid.defaults.view.onAfterRender.call(this, target);
							$('.datagrid-header-inner').width('100%')
							$('.datagrid-btable').width('100%')
							$('.datagrid-body').css({
								'overflow-y': 'hidden'
							});
						}
					}),
					fitColumns : true
				});

			});
		});
		jQuery(document).ready(function($) {
			$(window).resize(function() {
				$('#tt').datagrid('resize');
				$('#tb').panel('resize');
			})
		});

		function delete_row(id) {
			jQuery(document).ready(function($) {
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
			});
		}

		function conf_delete(val, id) {
			if (val == 1)
				window.location.href = 'manage_ledger_code_group?act=del&id=' + $("#DELETE_ID").val();
			else
				$("#deleteModal").modal("hide");
		}

		function exportexcel() {
			//Generate pdf
			var value = $.ajax({
				url: 'manage_ledger_code_group.php',
				type: "POST",
				data: {
					ACTION: 'exportexcel'
				},
				async: true,
				cache: false,
				beforeSend: function() {
					document.getElementById('loaders').style.display = 'block';
				},
				success: function(data) {
					const text = window.location.href;
					const word = '/school';
					const textArray = text.split(word); // ['This is ', ' text...']
					const result = textArray.shift();
					var report_download_name = "Ledger_Code_Groups.xlsx";
					downloadDataUrlFromJavascript(report_download_name, result + '/school/' + data.path)
					// alert(result + '/school/' + data.path); 

				},
				complete: function() {
					document.getElementById('loaders').style.display = 'none';

				}
			});
		}

		function downloadDataUrlFromJavascript(filename, dataUrl) {

			// Construct the 'a' element
			var link = document.createElement("a");
			link.download = filename;
			link.target = "_blank";

			// Construct the URI
			link.href = dataUrl;
			document.body.appendChild(link);
			link.click();

			// Cleanup the DOM
			document.body.removeChild(link);
			delete link;
		}
	</script>

</body>

</html>