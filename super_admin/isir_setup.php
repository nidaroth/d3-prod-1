<? require_once("../global/config.php");
require_once("../language/common.php");
if ($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1) {
	header("location:../index");
	exit;
}
if (!empty($_POST)) {
	//echo "<pre>";print_r($_POST);exit;

	$ISIR_SETUP_MASTER['FROM_NAME']   		= $_POST['FROM_NAME'];
	$ISIR_SETUP_MASTER['YEAR_INDICATION']   = $_POST['YEAR_INDICATION'];
	$ISIR_SETUP_MASTER['ACTIVE']  		 	= $_POST['ACTIVE'];

	if ($_GET['id'] == '' || $_GET['copy'] == 1) {
		$ISIR_SETUP_MASTER['CREATED_BY']  = $_SESSION['PK_USER'];
		$ISIR_SETUP_MASTER['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('Z_ISIR_SETUP_MASTER', $ISIR_SETUP_MASTER, 'insert');
		$PK_ISIR_SETUP_MASTER = $db->insert_ID();
	} else {
		$ISIR_SETUP_MASTER['EDITED_BY'] = $_SESSION['PK_USER'];
		$ISIR_SETUP_MASTER['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('Z_ISIR_SETUP_MASTER', $ISIR_SETUP_MASTER, 'update', " PK_ISIR_SETUP_MASTER = '$_GET[id]' ");
		$PK_ISIR_SETUP_MASTER = $_GET['id'];
	}

	if (!empty($_POST['COUNT'])) {
		$i = 0;
		foreach ($_POST['COUNT'] as $COUNT) {
			$ISIR_SETUP_DETAIL 		= array();
			$PK_ISIR_SETUP_DETAIL  	= $_POST['PK_ISIR_SETUP_DETAIL_' . $COUNT];
			$ISIR_SETUP_DETAIL['FIELD_NO']  		= $_POST['FIELD_NO_' . $COUNT];
			$ISIR_SETUP_DETAIL['HEADING']  			= $_POST['HEADING_' . $COUNT];
			$ISIR_SETUP_DETAIL['START']  			= $_POST['START_' . $COUNT];
			$ISIR_SETUP_DETAIL['END']  				= $_POST['END_' . $COUNT];
			$ISIR_SETUP_DETAIL['DSIS_FIELD_NAME']  	= $_POST['DSIS_FIELD_NAME_' . $COUNT];
			$ISIR_SETUP_DETAIL['HAS_LEDGEND'] 		= $_POST['HAS_LEDGEND_' . $COUNT];
			$ISIR_SETUP_DETAIL['ACTIVE']  			= $_POST['ACTIVE_' . $COUNT];
			if ($PK_ISIR_SETUP_DETAIL == '' || $_GET['copy'] == 1) {
				$ISIR_SETUP_DETAIL['PK_ISIR_SETUP_MASTER'] 	= $PK_ISIR_SETUP_MASTER;
				$ISIR_SETUP_DETAIL['ACTIVE'] 				= 1;
				db_perform('Z_ISIR_SETUP_DETAIL', $ISIR_SETUP_DETAIL, 'insert');

				$PK_ISIR_SETUP_DETAIL 		= $db->insert_ID();
				$PK_ISIR_SETUP_DETAIL_IDS[] = $PK_ISIR_SETUP_DETAIL;
			} else {
				if ($_POST['ACTIVE_' . $COUNT] == '') {
					$ISIR_SETUP_DETAIL['ACTIVE'] = 0;
				} else {
					$ISIR_SETUP_DETAIL['ACTIVE'] = 1;
				}
				db_perform('Z_ISIR_SETUP_DETAIL', $ISIR_SETUP_DETAIL, 'update', " PK_ISIR_SETUP_DETAIL = '$PK_ISIR_SETUP_DETAIL'");
				$PK_ISIR_SETUP_DETAIL_IDS[] = $PK_ISIR_SETUP_DETAIL;
			}

			if ($ISIR_SETUP_DETAIL['HAS_LEDGEND']  == 1) {
				foreach ($_POST['ledgend_count_' . $COUNT] as $ledgend_count) {
					$ISIR_SETUP_LEGEND 		= array();
					$PK_ISIR_SETUP_LEGEND  	= $_POST['PK_ISIR_SETUP_LEGEND_' . $ledgend_count];

					$ISIR_SETUP_LEGEND['LEGEND']  	= $_POST['LEGEND_' . $ledgend_count];
					$ISIR_SETUP_LEGEND['TEXT']  	= $_POST['TEXT_' . $ledgend_count];

					if ($PK_ISIR_SETUP_LEGEND == '' || $_GET['copy'] == 1) {
						$ISIR_SETUP_LEGEND['PK_ISIR_SETUP_MASTER'] 	= $PK_ISIR_SETUP_MASTER;
						$ISIR_SETUP_LEGEND['PK_ISIR_SETUP_DETAIL'] 	= $PK_ISIR_SETUP_DETAIL;
						$ISIR_SETUP_LEGEND['ACTIVE'] 				= '1';
						db_perform('Z_ISIR_SETUP_LEGEND', $ISIR_SETUP_LEGEND, 'insert');

						$PK_ISIR_SETUP_LEGEND 		= $db->insert_ID();
						$PK_ISIR_SETUP_LEGEND_IDS[] = $PK_ISIR_SETUP_LEGEND;
					} else {
						db_perform('Z_ISIR_SETUP_LEGEND', $ISIR_SETUP_LEGEND, 'update', " PK_ISIR_SETUP_LEGEND = '$PK_ISIR_SETUP_LEGEND'");
						$PK_ISIR_SETUP_LEGEND_IDS[] = $PK_ISIR_SETUP_LEGEND;
					}
				}
			}

			$cond = '';
			if (!empty($PK_ISIR_SETUP_LEGEND_IDS))
				$cond = " AND PK_ISIR_SETUP_LEGEND NOT IN (" . implode(",", $PK_ISIR_SETUP_LEGEND_IDS) . ")";
			$db->Execute("DELETE from Z_ISIR_SETUP_LEGEND WHERE PK_ISIR_SETUP_DETAIL = '$PK_ISIR_SETUP_DETAIL' $cond");
			//echo "DELETE from Z_ISIR_SETUP_LEGEND WHERE PK_ISIR_SETUP_DETAIL = '$PK_ISIR_SETUP_DETAIL' $cond <br />";
			$i++;
		}
	}
	$cond = '';
	if (!empty($PK_ISIR_SETUP_DETAIL_IDS))
		$cond = " AND PK_ISIR_SETUP_DETAIL NOT IN (" . implode(",", $PK_ISIR_SETUP_DETAIL_IDS) . ")";
	$db->Execute("DELETE from Z_ISIR_SETUP_DETAIL WHERE PK_ISIR_SETUP_MASTER = '$PK_ISIR_SETUP_MASTER' $cond");
	//$db->Execute("DELETE from Z_ISIR_SETUP_LEGEND WHERE PK_ISIR_SETUP_MASTER = '$PK_ISIR_SETUP_MASTER' $cond");

	//echo "DELETE from Z_ISIR_SETUP_DETAIL WHERE PK_ISIR_SETUP_MASTER = '$PK_ISIR_SETUP_MASTER' $cond<br />";
	//echo "DELETE from Z_ISIR_SETUP_LEGEND WHERE PK_ISIR_SETUP_MASTER = '$PK_ISIR_SETUP_MASTER' $cond<br />";
	//exit;
	header("location:manage_isir_setup");
}
if ($_GET['id'] == '') {
	$YEAR_INDICATION = '';
	$FROM_NAME 		 = '';
	$ACTIVE1	 	 = 0;
} else {
	$res = $db->Execute("SELECT * FROM Z_ISIR_SETUP_MASTER WHERE PK_ISIR_SETUP_MASTER = '$_GET[id]' ");
	if ($res->RecordCount() == 0) {
		header("location:manage_isir_setup");
		exit;
	}

	$YEAR_INDICATION = $res->fields['YEAR_INDICATION'];
	$FROM_NAME 		 = $res->fields['FROM_NAME'];
	$ACTIVE1  		 = $res->fields['ACTIVE'];
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
	<title>ISIR Setup | <?= $title ?></title>

	<style>
		.btn-circle {
			border-radius: 100%;
			width: 25px;
			height: 25px;
			padding: 2px;
		}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
	<? require_once("pre_load.php"); ?>
	<div id="main-wrapper">
		<? require_once("menu.php"); ?>
		<div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles">
					<div class="col-md-12 align-self-center">
						<h4 class="text-themecolor">
							<? if (isset($_GET['copy']) == 1)
								echo "Copy";
							else {
								if (isset($_GET['id']) == '') echo "Add";
								else echo "Edit";
							} ?> ISIR Setup </h4>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form class="floating-labels m-t-40" method="post" name="form1" id="form1">
									<div class="row">
										<div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="FROM_NAME" name="FROM_NAME" value="<?= $FROM_NAME ?>">
												<span class="bar"></span>
												<label for="FROM_NAME">Form Name</label>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="YEAR_INDICATION" name="YEAR_INDICATION" value="<?= $YEAR_INDICATION ?>">
												<span class="bar"></span>
												<label for="YEAR_INDICATION">Year Indication</label>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-1">
											<b>Field #</b>
										</div>
										<div class="col-md-4">
											<b>Heading</b>
										</div>
										<div class="col-md-1">
											<b>Start</b>
										</div>
										<div class="col-md-1">
											<b>End</b>
										</div>
										<div class="col-md-3">
											<b>DSIS Fields</b>
										</div>
										<div class="col-md-1">
											<b>Has Legend</b>
										</div>
										<div class="col-md-1">
											<b>Option</b>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<hr />
										</div>
									</div>

									<div id="form_div">
										<? $fetch_fields_count = 1;
										$ledgend_count	= 1;
										$result1 = $db->Execute("select PK_ISIR_SETUP_DETAIL from Z_ISIR_SETUP_DETAIL WHERE PK_ISIR_SETUP_MASTER = '$_GET[id]' ");
										while (!$result1->EOF) {
											$_REQUEST['PK_ISIR_SETUP_DETAIL'] 	= $result1->fields['PK_ISIR_SETUP_DETAIL'];
											$_REQUEST['count']  				= $fetch_fields_count;

											include('fetch_isir_fields.php');

											$fetch_fields_count++;
											$result1->MoveNext();
										} ?>

									</div>
									<div class="row">
										<div class="col-md-10">&nbsp;</div>
										<div class="col-md-1">
											<button type="button" class="btn btn-primary" onClick="fetch_fields()" />Add </button>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="row form-group">
												<div class="custom-control col-md-2"><?= ACTIVE ?></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if ($ACTIVE1 == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="customRadio11"><?= YES ?></label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if ($ACTIVE1 == 0) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="customRadio22"><?= NO ?></label>
												</div>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="form-group m-b-5" style="text-align:right">
												<button type="submit" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>

												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_isir_setup'"><?= CANCEL ?></button>

											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
		<? require_once("footer.php"); ?>
	</div>

	<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="exampleModalLabel1"><?= DELETE_CONFIRMATION ?></h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="form-group" id="delete_message"></div>
					<input type="hidden" id="DELETE_ID" value="0" />
					<input type="hidden" id="DELETE_TYPE" value="0" />
				</div>
				<div class="modal-footer">
					<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?= YES ?></button>
					<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)"><?= NO ?></button>
				</div>
			</div>
		</div>
	</div>

	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>

	<script type="text/javascript">
		var form1 = new Validation('form1');

		var cunt = '<?= $fetch_fields_count ?>';

		function fetch_fields() {
			jQuery(document).ready(function($) {
				var data = 'count=' + cunt;
				var value = $.ajax({
					url: "fetch_isir_fields",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						$("#form_div").append(data);
						cunt++;
					}
				}).responseText;
			});
		}

		function delete_row(id, type) {
			jQuery(document).ready(function($) {
				if (type == 'detail')
					document.getElementById('delete_message').innerHTML = '<?= DELETE_MESSAGE_GENERAL ?>?';

				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}

		function conf_delete(val, id) {
			jQuery(document).ready(function($) {
				if (val == 1) {
					if ($("#DELETE_TYPE").val() == 'detail') {
						var iid = $("#DELETE_ID").val()
						$("#table_" + iid).remove()
					} else if ($("#DELETE_TYPE").val() == 'ledgend') {
						var iid = $("#DELETE_ID").val()
						$("#ledgend_table_" + iid).remove()
					}
				}
				$("#deleteModal").modal("hide");
			});
		}

		function show_ledgend(id) {
			if (document.getElementById('HAS_LEDGEND_' + id).checked == true)
				document.getElementById('LEDGEND_' + id).style.display = 'block';
			else
				document.getElementById('LEDGEND_' + id).style.display = 'none';
		}

		var ledgend_count = '<?= $ledgend_count ?>';

		function add_ledgend(id) {
			jQuery(document).ready(function($) {
				var data = 'ledgend_count=' + ledgend_count + '&det_count=' + id;
				var value = $.ajax({
					url: "fetch_isir_ledgend",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						$("#LEDGEND_1_" + id).append(data);
						ledgend_count++;
					}
				}).responseText;
			});
		}

		function import_ledgend(id) {
			jQuery(document).ready(function($) {
				var data = 'ledgend_count=' + ledgend_count + '&det_count=' + id + '&DSIS_FIELD_NAME=' + document.getElementById('DSIS_FIELD_NAME_' + id).value;
				var value = $.ajax({
					url: "import_isir_ledgend",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						data = data.split("|||");
						document.getElementById('LEDGEND_1_' + id).innerHTML = data[0]
						ledgend_count = data[1]

						ledgend_count++;
					}
				}).responseText;
			});
		}

		<? if ($_GET['id'] == '') { ?>
			jQuery(document).ready(function($) {
				fetch_fields();
			});
		<? } ?>
	</script>
</body>

</html>