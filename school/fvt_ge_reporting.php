<?
require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_FINANCE') == 0 ){
	header("location:../index");
	exit;
}

if (!empty($_POST)) {
	
	$REPORT_OPTION = $_REQUEST['REPORT_OPTION'];
	//$STUDENT_OPTION = $_REQUEST['STUDENT_OPTION'];
	
	if($REPORT_OPTION == 1)
	{
		include 'fvt_get_completers_excel.php';
	}

	if($REPORT_OPTION == 2)
	{
		include 'fvt_get_programs_excel.php';
	}

	if($REPORT_OPTION == 3)
	{
		include 'fvt_get_students_excel.php';
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
	<title>FVT/GE Reporting | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}

		#advice-required-entry-PK_CAMPUS {
			position: absolute;
			top: 55px;
			width: 142px
		}
		.multiselect-container>li{
			width: 200px;
		}
		.multiselect-container > li > a >label{
			padding: 3px 20px 3px 12px !important;
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

		.loader-text{
			position: absolute;
			left: 26px;
			top: 177px;
			right: 0;
			bottom: 0;
			margin: auto;
			width: 133px;
			height: 64px;
			color: #fff;
			font-weight: bold;
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
		<div class="loader-text">Please wait.....!</div>
	</div>
	<div id="main-wrapper">
		<? require_once("menu.php"); ?>
		<div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles">
					<div class="col-md-5 align-self-center">
						<h4 class="text-themecolor">
							FVT/GE Reporting
						</h4>
					</div>
				</div>

				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-2">
											Report Option
											<select id="REPORT_OPTION" name="REPORT_OPTION" class="form-control" >
												<option value="1">Completers</option>
												<option value="2">Programs</option>
												<option value="3">Students</option>
											</select>
										</div>
										<div class="col-md-2">
											<!-- <div id="STUDENT_OPTION_DIV" style="display: none;">
												Student Option
												<select id="STUDENT_OPTION" name="STUDENT_OPTION" class="form-control">
													<option value="1">Enrolled, Graduated, Withdrawn</option>
													<option value="2">Graduated or Withdraw Only</option>
												</select>
											</div> -->
										</div>
										<div class="col-md-8 text-right">
											<button type="button" onclick="window.location.href='fvt_ge_reporting_setup'" class="btn waves-effect waves-light btn-info">Report Setup</button>
											<br />
											<? $res = $db->Execute("select * from S_FVT_GE_REPORTING_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
											if($res->fields['EDITED_ON'] != '' && $res->fields['EDITED_ON'] != '0000-00-00 00:00:00'){
												$EDITED_BY	= $res->fields['EDITED_BY'];
												$EDITED_ON	= $res->fields['EDITED_ON'];
												$res_user = $db->Execute("SELECT CONCAT(LAST_NAME,', ', FIRST_NAME) as NAME FROM S_EMPLOYEE_MASTER, Z_USER WHERE PK_USER = '$EDITED_BY' AND Z_USER.ID =  S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE IN (1,2) "); 

												$EDITED_BY	= $res_user->fields['NAME']; 
												echo "<b>Edited: ".$EDITED_BY." ".date("m/d/Y",strtotime($EDITED_ON))."</b>";
											} ?>
										</div>
									</div>
									<br><br>
									<div class="row">
										<div class="col-md-2">
											<?= CAMPUS ?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry">
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS,ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by  ACTIVE DESC,CAMPUS_CODE ASC");
												while (!$res_type->EOF) { 
													
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
													?>
													<option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($res_type->RecordCount() == 1) echo "selected"; ?>  <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['CAMPUS_CODE']. ' ' .$Status ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>


										<div class="col-md-2">
											Award Year <?= START_DATE ?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="">
										</div>
										<div class="col-md-2">
											Award Year <?= END_DATE ?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="">
										</div>


										<div class="col-md-2" style="flex: 0 0 12.667%;max-width: 12.667%;">
											<br />
											<!-- <button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?= PDF ?></button> -->
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?= EXCEL ?></button>
											<input type="hidden" name="FORMAT" id="FORMAT">
											<input type="hidden" name="INCLUDE_ALREADY_EXPORTED" id="INCLUDE_ALREADY_EXPORTED" value="TRUE">
										</div>
									</div>
									<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
		<? require_once("footer.php"); ?>

		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?= PREVIOUSLY_EXPORTED_TRANSACTION ?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group" id="exported_msg" style="display:none"><?= PREVIOUSLY_EXPORTED_TRANSACTION_MSG ?></div>
						<div class="form-group" id="pdf_msg" style="display:none"><?= PREVIOUSLY_REVIEW_TRANSACTION_MSG ?></div>

						<input type="hidden" name="FORMAT_1" id="FORMAT_1">
					</div>
					<div class="modal-footer">
						<div id="exported_btn" style="display:none">
							<button type="button" onclick="conf_submit_form(1)" class="btn waves-effect waves-light btn-info"><?= EXPORT_ALL ?></button>
							<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_submit_form(0)"><?= EXPORT_NEW ?></button>
						</div>
						<div id="pdf_btn" style="display:none">
							<button type="button" onclick="conf_submit_form(1)" class="btn waves-effect waves-light btn-info"><?= REVIEW_ALL ?></button>
							<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_submit_form(0)"><?= REVIEW_NEW ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
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

		function check_trans(val) {
			jQuery(document).ready(function($) {
				var data = 'DATE_TYPE=' + $('#DATE_TYPE').val() + '&START_DATE=' + $('#START_DATE').val() + '&END_DATE=' + $('#END_DATE').val() + '&PK_CAMPUS=' + $('#PK_CAMPUS').val();
				var value = $.ajax({
					url: "ajax_check_ledger_transaction",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						//alert(data)
						if (data == "a")
							document.form1.submit();
						else {
							if (document.getElementById('FORMAT').value == 1) {
								document.getElementById('pdf_msg').style.display = 'block'
								document.getElementById('pdf_btn').style.display = 'block'

								document.getElementById('exported_msg').style.display = 'none'
								document.getElementById('exported_btn').style.display = 'none'
							} else {
								document.getElementById('pdf_msg').style.display = 'none'
								document.getElementById('pdf_btn').style.display = 'none'

								document.getElementById('exported_msg').style.display = 'block'
								document.getElementById('exported_btn').style.display = 'block'
							}
							show_popup(val)
						}
					}
				}).responseText;
			});
		}

		function show_popup(val) {
			jQuery(document).ready(function($) {
				$("#deleteModal").modal()
				$("#FORMAT_1").val(val)
			});
		}

		function conf_submit_form(val) {
			jQuery(document).ready(function($) {
				if (val == 1) {
					document.getElementById('INCLUDE_ALREADY_EXPORTED').value = 'TRUE';
				} else
					document.getElementById('INCLUDE_ALREADY_EXPORTED').value = 'FALSE';

				$("#deleteModal").modal("hide");
				var val1 = $("#FORMAT_1").val();
				document.form1.submit();
			});
		}

	/*	function student_option_change(val)
		{
			if(val == 2 || val == 3)
			{
				document.getElementById('STUDENT_OPTION_DIV').style.display = 'block';
			}
			else{
				document.getElementById('STUDENT_OPTION_DIV').style.display = 'none';
			}
		}*/
	</script>

	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		function submit_form(val) {
			jQuery(document).ready(function($) {
				var valid = new Validation('form1', {
					onSubmit: false
				});
				var result = valid.validate();
				if (result == true) {
					document.getElementById('FORMAT').value = val
					//document.form1.submit();
					//check_trans(val)
					//$("#loaders").fadeIn();					
					//document.getElementById('loaders').style.display = "block";
				
					downloadExcel();
					

				}
			});
		}

		function downloadExcel() {

			document.getElementById('loaders').style.display = 'block';

			jQuery(document).ready(function($) {
				set_notification=false; // DIAM-1753

				if($('#REPORT_OPTION').val()==1){
				 	var excelURL = 'fvt_get_completers_excel.php';					
				}else if($('#REPORT_OPTION').val()==2){
					var excelURL = 'fvt_get_programs_excel.php';
				}else if($('#REPORT_OPTION').val()==3){
					var excelURL = 'fvt_get_students_excel.php';
				}

				

				var data = 'START_DATE=' + $('#START_DATE').val() + '&END_DATE=' + $('#END_DATE').val() + '&PK_CAMPUS=' + $('#PK_CAMPUS').val()+'&STUDENT_OPTION='+$('#STUDENT_OPTION').val();
				var value = $.ajax({
					url: excelURL,
					type: "POST",
					data: data,
					async: true,
					cache: false,
					success: function(data) {
						//alert(data)
						//console.log('221231');
						var $a = $("<a>");
						$a.attr("href",data.hrefpath);
						$("body").append($a);
						$a.attr("download",data.filename);
						$a[0].click();
						$a.remove();
						document.getElementById('loaders').style.display = "none";
						set_notification=true;
					}
				}).responseText;
			});
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= CAMPUS ?>',
				nonSelectedText: '',
				numberDisplayed: 2,
				nSelectedText: '<?= CAMPUS ?> selected'
			});
		});
	</script>

</body>

</html>
