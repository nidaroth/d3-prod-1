<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/sap_group.php");
require_once("../language/menu.php");
require_once("check_access.php");
// ENABLE_DEBUGGING(TRUE);
if (check_access('SETUP_REGISTRAR') == 0) {
	header("location:../index");
	exit;
}
// $sap_pk_array=array('15','67','72','64');
// if(!in_array($_SESSION['PK_ACCOUNT'],$sap_pk_array))
// {   
// 	header("location:../school/index");
// 	exit;
// }
if (!empty($_POST)) {
	// echo "<pre>";
	// print_r($_REQUEST);
	// exit;
	$LEDGER_GROUP = $_POST;
	$LEDGER_GROUP['PK_AR_LEDGER_CODES'] = implode(",", $_POST['PK_AR_LEDGER_CODES']);

	if ($_GET['id'] == '') {
		$LEDGER_GROUP['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$LEDGER_GROUP['CREATED_BY']  = $_SESSION['PK_USER'];
		$LEDGER_GROUP['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_LEDGER_CODE_GROUP', $LEDGER_GROUP, 'insert');
		$PK_SAP_GROUP = $db->insert_ID();
	} else {
		$LEDGER_GROUP['EDITED_BY'] = $_SESSION['PK_USER'];
		$LEDGER_GROUP['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_LEDGER_CODE_GROUP', $LEDGER_GROUP, 'update', " PK_LEDGER_CODE_GROUP = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$PK_SAP_GROUP = $_GET['id'];
	}
	// echo "<pre>";
	// print_r($LEDGER_GROUP);
	// exit;
	header("location:manage_ledger_code_group");
}
if ($_GET['id'] == '') {
	$LEDGER_CODE_GROUP = '';
	$ACTIVE = 1;
	$PK_AR_LEDGER_CODES_ARR = array();
} else {
	$res = $db->Execute("SELECT * FROM S_LEDGER_CODE_GROUP WHERE PK_LEDGER_CODE_GROUP = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if ($res->RecordCount() == 0) {
		header("location:manage_ledger_code_group");
		exit;
	}

	$LEDGER_CODE_GROUP 		= $res->fields['LEDGER_CODE_GROUP'];
	$LEDGER_CODE_GROUP_DESC = $res->fields['LEDGER_CODE_GROUP_DESC'];
	$ACTIVE  				= $res->fields['ACTIVE'];
	$PK_AR_LEDGER_CODES_ARR 	= explode(",", $res->fields['PK_AR_LEDGER_CODES']);
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
	<title>Ledger Code Group | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}

		.option_red > a > label{color:red !important}

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
						<h4 class="text-themecolor"><? if ($_GET['id'] == '') echo ADD;
													else echo EDIT; ?> Ledger Code Group </h4>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form class="floating-labels m-t-40" method="post" name="form1" id="form1">
									<div class="row">
											<div class="col-md-6">
												<div class="row">
													<div class="col-md-12">
														<div class="form-group">
															<input type="text" class="form-control required-entry" id="LEDGER_CODE_GROUP" name="LEDGER_CODE_GROUP" value="<?= $LEDGER_CODE_GROUP ?>">
															<span class="bar"></span>
															<label for="LEDGER_CODE_GROUP">Ledge Code Group</label>
														</div>
													</div>
													<div class="col-md-12">
														<div class="form-group m-b-40">
															<input type="text" class="form-control required-entry" id="LEDGER_CODE_GROUP_DESC" name="LEDGER_CODE_GROUP_DESC" value="<?= $LEDGER_CODE_GROUP_DESC ?>">
															<span class="bar"></span>
															<label for="LEDGER_CODE_GROUP_DESC">Ledge Code Group Description</label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-sm-6">
												<div class="d-flex">
													<div class="col-4 col-sm-4 focused">
														<span class="bar"></span>
														<label>Ledger Code</label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<select id="PK_AR_LEDGER_CODES" name="PK_AR_LEDGER_CODES[]" multiple class="form-control">
															<? $res_type = $db->Execute("SELECT PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION,ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");
															while (!$res_type->EOF) {
																$selected 			= "";
																$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE'];
																foreach ($PK_AR_LEDGER_CODES_ARR as $PK_AR_LEDGER_CODE1) {
																	if ($PK_AR_LEDGER_CODE1 == $PK_AR_LEDGER_CODE) {
																		$selected = 'selected';
																		break;
																	}
																} ?>
																<option value="<?= $PK_AR_LEDGER_CODE ?>" <?= $selected ?>  <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['CODE'] ?><? if($res_type->fields['ACTIVE'] == 0) echo " (Inactive)"; ?></option>
															<? $res_type->MoveNext();
															} ?>
														</select>
													</div>
												</div>
											</div>

											<div class="col-4 col-sm-4">
												<div class="row">
													<div class="col-md-2">
														<div class="form-group m-b-40">
															<span class="bar"></span>
															<label for="IS_DEFAULT"><?= ACTIVE ?></label>
														</div>
													</div>

													<div class="row form-group col-12 col-sm-10">
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if ($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="customRadio11"><?= YES ?></label>
														</div>
														<div class="custom-control custom-radio col-md-5">
															<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if ($ACTIVE == 0) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="customRadio22"><?= NO ?></label>
														</div>
													</div>
												</div>
											</div> 
									</div>

									<div class="row">
										<div class="col-md-3">&nbsp;</div>
										<div class="col-md-6">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>

											<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_ledger_code_group'"><?= CANCEL ?></button>
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

	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#PK_AR_LEDGER_CODES').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All Ledger Codes',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: 'Ledger Codes selected',
				maxHeight:250
			});

		});
	</script>

</body>

</html>