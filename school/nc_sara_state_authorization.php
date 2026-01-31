<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/population_report_setup.php");
require_once("check_access.php");

if (check_access('SETUP_COMMUNICATION') == 0) {
	header("location:../index");
	exit;
}

if (!empty($_POST)) {


	foreach ($_POST['COMPLETED_STATE_PROCESS'] as $PK_STATES => $STATUS) {


		$STATE_DATA = [];
		$STATE_DATA['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$STATE_DATA['PK_STATES'] = $PK_STATES;
		$STATE_DATA['COMMENT'] = $_POST['COMMENT'][$PK_STATES];
		if($_POST['DATE_APPROVED'][$PK_STATES] != ''){
			$STATE_DATA['DATE_APPROVED'] = date('Y-m-d' , strtotime( $_POST['DATE_APPROVED'][$PK_STATES] ) ) ;
		} 
		
		$STATE_DATA['STATUS'] = $STATUS;
		$STATE_DATA['CREATED_BY'] = $_SESSION['PK_USER'];
		$STATE_DATA['CREATED_ON'] = date('Y-m-d');
		$check_if_existing_state_setup = $db->Execute("SELECT PK_NC_SARA_STATES FROM NC_SARA_STATE_AUTHORIZATION WHERE PK_ACCOUNT = $_SESSION[PK_ACCOUNT] AND PK_STATES = $PK_STATES");
		//UPDATE IF EXSISTS 
		if ($check_if_existing_state_setup->RecordCount() > 0) {

			$PK_NC_SARA_STATES = $check_if_existing_state_setup->fields['PK_NC_SARA_STATES'];
			db_perform('NC_SARA_STATE_AUTHORIZATION', $STATE_DATA, 'update', " PK_NC_SARA_STATES = '$PK_NC_SARA_STATES' ");
		} else {
			//ADD NEW SETUP
			db_perform('NC_SARA_STATE_AUTHORIZATION', $STATE_DATA, 'insert');
		}
		// dd($STATE_DATA);
	}
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
	<title><?= MNU_SARA_STATE_AUTHORIZATION ?> | <?= $title ?></title>
	<style>
		.no-records-found {
			display: none;
		}

		.fixed-table-container tbody td .th-inner,
		.fixed-table-container thead th .th-inner {
			padding: 5px !important;
		}
	</style>
	<style>
		li>a>label {
			position: unset !important;
		}

		#advice-required-entry-PK_CAMPUS {
			position: absolute;
			top: 55px;
			width: 142px
		}

		.dropdown-menu>li>a {
			white-space: nowrap;
			max-width: 90%
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
	<div id="main-wrapper">
		<? require_once("menu.php"); ?>
		<div id="loaders" style="display: none;">
			<div class="lds-ring">
				<div></div>
				<div></div>
				<div></div>
				<div></div>
			</div>
		</div>
		<div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles">
					<div class="col-md-12 align-self-center">
						<h4 class="text-themecolor"><?= MNU_SARA_STATE_AUTHORIZATION ?> </h4>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form class="floating-labels m-t-10" method="post" name="form1" id="form1">

									<div class="row">
										<div class="col-md-12 text-right">
											<button type="button" onclick="export_excel()" class="btn waves-effect waves-light btn-info"><?= EXCEL ?></button>
											<br />
											<br />
										</div>
									</div>

									<div class="row">
										<div class="col-md-12">
											<table class="table table-bordered" style="width">
												<thead>
													<tr>
														<th width="16%"><?= STATE ?></th>
														<th><?= STATE ?> <br> Abbreviation</th>
														<th width="9%"><?= COUNTRY ?></th>
														<th width="16%">Completed State Process</th>
														<th width="9%">Date Authorized</th>
														<th>Comments</th>
													</tr>
												</thead>
												<tbody>


													<?php

													$states = $db->Execute("SELECT Z_STATES.PK_STATES,STATE_NAME,STATE_CODE,CODE FROM `Z_STATES` LEFT JOIN Z_COUNTRY ON Z_STATES.PK_COUNTRY = Z_COUNTRY.PK_COUNTRY ORDER BY CODE DESC,STATE_NAME,STATE_CODE;
															");

													while (!$states->EOF) {
														$comment = '';
														$DATE_APPROVED = '';
														$selected_1 = '';
														$selected_2 = '';
														$selected_3 = '';
														$selected_4 = '';
														$selected_5 = '';
														$selected_6 = '';
														$selected_7 = '';
														# code...
														$PK_STATES_C = $states->fields['PK_STATES'];
														$selected_data = $db->Execute("SELECT * FROM NC_SARA_STATE_AUTHORIZATION WHERE PK_ACCOUNT = $_SESSION[PK_ACCOUNT] AND PK_STATES = $PK_STATES_C");
														if ($selected_data->RecordCount() > 0) {

															$varname = 'selected_' . $selected_data->fields['STATUS'];

															$$varname = ' selected = "selected" '; 
															if($selected_data->fields['DATE_APPROVED'] != '' && $selected_data->fields['DATE_APPROVED'] != '0000-00-00'){
															$DATE_APPROVED = date('m/d/Y', strtotime($selected_data->fields['DATE_APPROVED']));
															}
															$comment = $selected_data->fields['COMMENT'];
														}

														echo 	'<tr>
																<td width="16%">' . $states->fields['STATE_NAME'] . '</td>
																<td width="9%">' . $states->fields['STATE_CODE'] . '</td>
																<td>' . $states->fields['CODE'] . '</td>
																<td  width="16%"> 
																<select id="COMPLETED_STATE_PROCESS" name="COMPLETED_STATE_PROCESS[' . $states->fields['PK_STATES'] . ']" class="form-control" >
																	<option value="0" >Select Status</option>
																	<option value="1" ' . $selected_1 . '>Authorized</option>
																	<option value="2" ' . $selected_2 . '>Exempt</option>
																	<option value="3" ' . $selected_3 . '>Licensed</option>
																	<option value="4" ' . $selected_4 . '>NC-SARA Authorized</option>
																	<option value="5" ' . $selected_5 . '>Not Applicable</option>
																	<option value="6" ' . $selected_6 . '>Not Authorized</option>
																	<option value="7" ' . $selected_7 . '>Pending</option>
																</select>
																</td>
																<td width="9%">
																<input id="DATE_APPROVED_'.$states->fields['PK_STATES'].'" name="DATE_APPROVED[' . $states->fields['PK_STATES'] . ']" class="form-control date" value="'.$DATE_APPROVED.'">
																</td>
																<td> 
																
																<!-- <div class="form-group p-0 m-0">
																	<input id="COMMENT_'.$states->fields['PK_STATES'].'" name="COMMENT[' . $states->fields['PK_STATES'] . ']" type="text" class="form-control" value="">
																	<span class="bar"></span>
																	<label for="COMMENT_'.$states->fields['PK_STATES'].'">Comment</label>
																</div> -->

																<input id="COMMENT_'.$states->fields['PK_STATES'].'" name="COMMENT[' . $states->fields['PK_STATES'] . ']" type="text" class="form-control" value="'.$comment.'">
																
															 </td>
															</tr>';
														$states->MoveNext();
													}


													?>

												</tbody>
											</table>
										</div>
									</div>


									<div class="row">
										<div class="col-md-12">
											<div class="form-group m-b-5" style="text-align:right">
												<button type="submit" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>

												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='nc_sara_state_authorization'"><?= CANCEL ?></button>

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

	<? require_once("js.php"); ?>
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
				<script>
					function export_excel() {
						jQuery(document).ready(function($) {
							//Generate pdf
							var value = $.ajax({
								url: 'nc_sara_state_excel.php',
								type: "POST",
								async: true,
								cache: false,
								beforeSend: function() {
									document.getElementById('loaders').style.display = 'block';
								},
								success: function(data, textStatus, xhr) {
									document.getElementById('loaders').style.display = 'none';
									// console.log(data, textStatus, xhr, xhr.status);
									// if (data.error == "No data found !") {
									// 	alert("No data found for this report ! Check IPEDS setup and try again");
									// 	return;
									// }

									const text = window.location.href;
									const word = '/school';
									const textArray = text.split(word); // ['This is ', ' text...']
									const result = textArray.shift();
									console.log(data, data.file_name, result + '/school/' + data.path);
									downloadDataUrlFromJavascript(data.file_name, result + '/school/' + data.path)
									// alert(result + '/school/' + data.path); 

								},
								error: function() {
									// document.getElementById('loaders').style.display = 'none';
									// alert("Something went wrong , Check your IPEDS setup and try again");
								},
								complete: function() {
									document.getElementById('loaders').style.display = 'none';
									// document.getElementById('loaders').style.display = 'none';

								}
							});
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