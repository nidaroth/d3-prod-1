<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/earnings_setup.php");
require_once("../language/menu.php");
require_once("check_access.php");

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

if (check_access('MANAGEMENT_CUSTOM_REPORT') == 0) { //DIAM-2090
	header("location:../index");
	exit;
}
$report_error = "";

$res_type1 = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");

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
	<title> <?=MNU_SPECIAL_SAP_REPORTING ?>| <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}

		.dropdown-menu>li>a {
			white-space: nowrap;
		}
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
	</style>
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
						<h4 class="text-themecolor">Custom SAP Report</h4>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
								<input type="hidden" name="SELECTED_PK_STUDENT_MASTER" id="SELECTED_PK_STUDENT_MASTER" value="">
								<div class="p-20">
									<div class="d-flex">
										<div class="col-12 col-sm-12 ">
											<div class="row">

												<div class="col-2 col-sm-2" id="PK_CAMPUS_DIV">
													<div class="form-group m-b-40">
														<select id="PK_CAMPUS" name="PK_CAMPUS[]" class="form-control" multiple>
															<?
															while (!$res_type1->EOF) {
																if ($res_type1->RecordCount() == 1)
																	$selected = 'selected'; ?>
																<option value="<?= $res_type1->fields['PK_CAMPUS'] ?>" <?= $selected ?>><?= $res_type1->fields['CAMPUS_CODE'] ?></option>
															<? $res_type1->MoveNext();
															} ?>
														</select>

														<span class="bar"></span>
														<!-- <label for="PK_CAMPUS"><?= CAMPUS ?></label> -->
													</div>
												</div>

												<div class="col-md-2 " id="PK_TERM_MASTER_DIV">

														<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control" >
															<option value=""><?=TERM?></option>
															<? /* Ticket #1149 - term */
															$res_type = $db->Execute("select PK_TERM_MASTER,BEGIN_DATE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, BEGIN_DATE, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
															while (!$res_type->EOF) { 
															$str = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'].' - '.$res_type->fields['TERM_DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$str .= ' (Inactive)'; ?>
															<option value="<?php echo $res_type->fields['PK_TERM_MASTER'].'_'.$res_type->fields['BEGIN_DATE_1']; ?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$str ?></option>
															<?	$res_type->MoveNext();
															} /* Ticket #1149 - term */ ?>
														</select>

												</div>

										<div class="col-md-2 " id="PK_TERM_MASTER_DIV">

											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control required-entry">
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION,ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC,STUDENT_STATUS ASC");
												while (!$res_type->EOF) {

													$ACTIVE 	= $res_type->fields['ACTIVE'];
													if ($ACTIVE == '0') {
														$Status = '(Inactive)';
													} else {
														$Status = '';
													}
												?>
													<option value="<?= $res_type->fields['PK_STUDENT_STATUS'] ?>"><?= $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['DESCRIPTION'] . ' ' . $Status ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
														
										</div>

								

										

											

												<div class="col-3 col-sm-3 ">
													<button type="button" onclick="get_report()" id="PDF_BTN" class="btn waves-effect waves-light btn-info">PDF</button>
													<input type="hidden" name="FORMAT" id="FORMAT">
												</div>

											</div>
											<div class="row">
												<div style="width:100%"  class="mt-5" id="student_div"></div>
												<input type="hidden" name="SELECTED_PK_STUDENT_MASTER" id="SELECTED_PK_STUDENT_MASTER" value="">
											</div>

										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>

			</div>
		</div>

		<? require_once("footer.php"); ?>

		<?php if ($report_error != "") { ?>
			<div class="modal" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="exampleModalLabel1">Warning</h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body">
							<div class="form-group" style="color: red;font-size: 15px;">
								<b><?php echo $report_error; ?></b>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" data-dismiss="modal" class="btn waves-effect waves-light btn-info">Cancel</button>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>

	</div>

	<? require_once("js.php"); ?>

	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>

	<script type="text/javascript">
		var error = '<?php echo  $report_error; ?>';
		jQuery(document).ready(function($) {
			if (error != "") {
				jQuery('#errorModal').modal();
			}

			$('#PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= "Campus" ?>',
				nonSelectedText: '<?= "Campus" ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= "Campus" ?> selected'
			});

			$('#PK_STUDENT_STATUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= STATUS ?>',
				nonSelectedText: '<?= STATUS ?>',
				numberDisplayed: 2,
				nSelectedText: '<?= STATUS ?> selected'
			});



	


		});


		function submit_form(val) {
			jQuery(document).ready(function($) {
				var valid = new Validation('form1', {
					onSubmit: false
				});
				var result = valid.validate();
				if (result == true) {
					document.getElementById('FORMAT').value = val
					document.form1.submit();
				}
			});
		}

		function get_report() {
			jQuery(document).ready(function($) {
				//get values 

				// var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
				// var eid = '';
				// for (var i = 0; i < PK_STUDENT_ENROLLMENT.length; i++) {
				// 	if (PK_STUDENT_ENROLLMENT[i].checked == true) {
				// 		console.log("checked", PK_STUDENT_ENROLLMENT[i], PK_STUDENT_ENROLLMENT[i].value);
				// 		if (eid == '') {
				// 			eid = eid + PK_STUDENT_ENROLLMENT[i].value
				// 		} else {
				// 			eid = eid + ',' + PK_STUDENT_ENROLLMENT[i].value;
				// 		}
				// 	}
				// }
				document.getElementById('loaders').style.display = 'block';


				var data = 'PK_TERM_MASTER=' + $('#PK_TERM_MASTER').val()+'&PK_CAMPUS=' + $('#PK_CAMPUS').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val();
				var value = $.ajax({
					url: "special_custom_sap_pdf",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						// document.getElementById('student_div').innerHTML = data
						document.getElementById('loaders').style.display = 'none';

						const text = window.location.href;
						const word = '/school';
						const textArray = text.split(word); // ['This is ', ' text...']
						const result = textArray.shift();
						// alert(result + '/school/' + data.path);
						downloadDataUrlFromJavascript("special_custom_sap", result + '/school/' + data.path)

					}
				}).responseText;
			});
		}


		// COMMON FUNCS 
		function search() {
			jQuery(document).ready(function($) {
				var data = 'PK_CAMPUS=' + $('#PK_CAMPUS').val() + '&PK_STUDENT_GROUP=' + $('#PK_STUDENT_GROUP').val() + '&PK_TERM_MASTER=' + $('#PK_TERM_MASTER').val() + '&PK_CAMPUS_PROGRAM=' + $('#PK_CAMPUS_PROGRAM').val() + '&PK_STUDENT_STATUS=' + $('#PK_STUDENT_STATUS').val() + '&show_check=1&show_count=1' + '&ENROLLMENT=' + $('#ENROLLMENT_TYPE_1').val() + '&dt=' + $('#AS_OF_DATE').val();
				var value = $.ajax({
					url: "ajax_search_student_for_reports",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						document.getElementById('student_div').innerHTML = data
					}
				}).responseText;
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

		function fun_select_all() {
			var str = '';
			if (document.getElementById('SEARCH_SELECT_ALL').checked == true)
				str = true;
			else
				str = false;

			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for (var i = 0; i < PK_STUDENT_ENROLLMENT.length; i++) {
				PK_STUDENT_ENROLLMENT[i].checked = str
			}
		}

		function get_count() {
			var PK_STUDENT_MASTER_sel = '';
			var tot = 0
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for (var i = 0; i < PK_STUDENT_ENROLLMENT.length; i++) {
				if (PK_STUDENT_ENROLLMENT[i].checked == true) {
					if (PK_STUDENT_MASTER_sel != '')
						PK_STUDENT_MASTER_sel += ',';

					PK_STUDENT_MASTER_sel += document.getElementById('S_PK_STUDENT_MASTER_' + PK_STUDENT_ENROLLMENT[i].value).value
					tot++;
				}
			}
			document.getElementById('SELECTED_PK_STUDENT_MASTER').value = PK_STUDENT_MASTER_sel
			//alert(PK_STUDENT_MASTER_sel)

			document.getElementById('SELECTED_COUNT').innerHTML = tot
			show_btn()
			return tot;
		}

		function show_btn() {

			document.getElementById('PDF').style.display = 'none';
			var flag = 0;
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for (var i = 0; i < PK_STUDENT_ENROLLMENT.length; i++) {
				if (PK_STUDENT_ENROLLMENT[i].checked == true) {
					flag++;
					break;
				}
			}

			if (flag == 1) {
				/* Ticket # 1508 */
				if (document.getElementById('REPORT_TYPE').value == 12) {
					document.getElementById('btn_3').style.display = 'inline';
					document.getElementById('btn_4').style.display = 'inline';
				} else {
					document.getElementById('btn_1').style.display = 'inline';

					if (document.getElementById('REPORT_TYPE').value == 2 || document.getElementById('REPORT_TYPE').value == 14 || document.getElementById('REPORT_TYPE').value == 5 || document.getElementById('REPORT_TYPE').value == 6 || document.getElementById('REPORT_TYPE').value == 7 || document.getElementById('REPORT_TYPE').value == 8 || document.getElementById('REPORT_TYPE').value == 10 || document.getElementById('REPORT_TYPE').value == 12)
						document.getElementById('btn_2').style.display = 'inline';
				}
				/* Ticket # 1508 */
			}
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />


	<?php $report_error = ""; ?>

</body>

</html>
