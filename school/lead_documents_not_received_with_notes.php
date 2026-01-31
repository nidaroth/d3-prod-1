<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/lead_documents_not_received_with_notes.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ADMISSION') == 0 && check_access('REPORT_CUSTOM_REPORT') == 0 && check_access('MANAGEMENT_ADMISSION') == 0){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//header("location:lead_task_report_pdf?st=".$_POST['START_DATE'].'&et='.$_POST['END_DATE'].'&dt='.$_POST['DATE_TYPE'].'&e='.$_POST['PK_EMPLOYEE_MASTER'].'&tc='.$_POST['TASK_COMPLETED']);
	
	if($_POST['FORMAT'] == 1)
		header("location:lead_documents_not_received_with_notes_pdf?sid=".$_GET['sid']."eid=".$_GET['eid']); // Ticket # 1588
	else if($_POST['FORMAT'] == 2)
		header("location:lead_documents_not_received_with_notes_excel");
		
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
	<title><?=MNU_LEAD_DOC_NOT_RECEIVED_WITH_NOTES?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_STUDENT_STATUS, #advice-required-entry-PK_CAMPUS{position: absolute;top: 30px;width: 140px}
		
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
       <? if($_GET['no_menu'] != 1)
			require_once("menu.php"); ?>

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
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_LEAD_DOC_NOT_RECEIVED_WITH_NOTES?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels " method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<br/>
									<div class="row">
										<? if($_GET['sid'] == ''){ ?>
										<!-- DIAM-1439 -->
										<div class="col-md-2 " id="PK_CAMPUS_DIV"   >
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control " >
												<? $res_type = $db->Execute("select CAMPUS_CODE, PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<!-- DIAM-1439 -->
										<div class="col-md-2 form-group">
											<select id="DATE_TYPE" name="DATE_TYPE" class="form-control"  >
												<option value="1" >Request Date</option>
												<option value="2" >Follow Up Date</option>
												<option value="3" >Received Date Date</option>
											</select>
											<span class="bar"></span> 
											<label for="DATE_TYPE"><?=DATE_TYPE?></label>
										</div>
										<div class="col-md-2 form-group">
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value=""  >
											<span class="bar"></span> 
											<label for="START_DATE"><?=START_DATE?></label>
										</div>
										<div class="col-md-2 form-group">
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value=""  >
											<span class="bar"></span> 
											<label for="END_DATE"><?=END_DATE?></label>
										</div>
										<div class="col-md-2 form-group ">
											<select id="PK_EMPLOYEE_MASTER" name="PK_EMPLOYEE_MASTER[]" class="form-control" multiple  >
												<? $emp_cond = "";
												
												$res_type = $db->Execute("select PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $emp_cond order by CONCAT(FIRST_NAME,' ',LAST_NAME) ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" ><?=$res_type->fields['NAME']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2 form-group ">
											<select id="RECEIVED" name="RECEIVED" class="form-control"  >
												<option value="0" >Both</option>
												<option value="1" >Yes</option>
												<option value="2" >No</option>
											</select>
											<span class="bar"></span> 
											<label for="RECEIVED"><?=RECEIVED?></label>
										</div>
										<? } else { ?>
										<div class="col-md-10 ">&nbsp;</div>
										<? } ?>
									
									</div>
									<? if($_GET['sid'] == ''){ ?>
									<div class="row">
										<div class="col-md-2 form-group">
											<select id="PK_DOCUMENT_TYPE" name="PK_DOCUMENT_TYPE[]" class="form-control" multiple  >
												<? $res_type = $db->Execute("select PK_DOCUMENT_TYPE,DOCUMENT_TYPE from M_DOCUMENT_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by DOCUMENT_TYPE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_DOCUMENT_TYPE']?>" ><?=$res_type->fields['DOCUMENT_TYPE'] ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 form-group">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control"  >
												<option value="" ></option>
												<? /* Ticket #1149 - term */
												$res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
												while (!$res_type->EOF) { 
													$str = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'].' - '.$res_type->fields['TERM_DESCRIPTION'];
													if($res_type->fields['ACTIVE'] == 0)
														$str .= ' (Inactive)'; ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($res_type->fields['PK_TERM_MASTER'] == $PK_TERM_MASTER) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$str ?></option>
												<?	$res_type->MoveNext();
												} /* Ticket #1149 - term */ ?>
											</select>
											<span class="bar"></span> 
											<label for="PK_TERM_MASTER"><?=TERM?></label>
										</div>
											<!-- DIAM-1439 -->										
											<div class="col-md-2 form-group">
												<select id="DOC_DEPARTMENT" name="DOC_DEPARTMENT[]" multiple class="form-control">
													<? $res_type = $db->Execute("select PK_DEPARTMENT, DEPARTMENT FROM M_DEPARTMENT WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER IN (1,2,4,6,7) ORDER BY DEPARTMENT ASC ");
													while (!$res_type->EOF) {
														$selected = '';
														foreach ($DOC_DEPARTMENT as $DOC_DEPARTMENT_1) {
															if ($DOC_DEPARTMENT_1 == $res_type->fields['PK_DEPARTMENT']) {
																$selected = 'selected';
																break;
															}
														} ?>
														<option value="<?= $res_type->fields['PK_DEPARTMENT'] ?>" <?= $selected ?>><?= $res_type->fields['DEPARTMENT'] ?></option>
													<? $res_type->MoveNext();
													} ?>
												</select>
											</div>
											<!-- DIAM-1439 -->

										<div class="col-md-2" style="padding: 0;" >
											<? if($_GET['sid'] == ''){ ?>
											<button type="button" onclick="search()" class="btn waves-effect waves-light btn-info"><?=SEARCH?></button>
											<? } ?>
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									
									</div>
									<? } ?>
									<br />
									<div id="student_div">
										<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
									</div>
									
								</div>
							</div>
						</div>
					</div>
				</form>
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>
	<!-- <script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script> -->
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>

	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
		
		<? if($_GET['sid'] != ''){ ?>
		search();
		<? } ?>
	});

	function search(){
		set_notification=false;
		document.getElementById('loaders').style.display = 'block'; 

		jQuery(document).ready(function($) {
			
			var data  = 'DATE_TYPE='+$('#DATE_TYPE').val()+'&START_DATE='+$('#START_DATE').val()+'&END_DATE='+$('#END_DATE').val()+'&PK_EMPLOYEE_MASTER='+$('#PK_EMPLOYEE_MASTER').val()+'&RECEIVED='+$('#RECEIVED').val()+'&PK_DOCUMENT_TYPE='+$('#PK_DOCUMENT_TYPE').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+ '&DOC_DEPARTMENT=' + $('#DOC_DEPARTMENT').val(); //DIAM-1439
			//alert(data)
			var value = $.ajax({
				url: "ajax_lead_documents_not_received_with_notes?sid=<?=$_GET['sid']?>",	
				type: "POST",		 
				data: data,		
				async: true,
				cache: false,				
				success: function (data) {	
					set_notification = true;
					document.getElementById('student_div').innerHTML = data
				},complete: function() {   
							document.getElementById('loaders').style.display = 'none'; 

						}			
			}).responseText;
		});
	}
	</script>	
	<script type="text/javascript">
	function submit_form(val){
		jQuery(document).ready(function($) {
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true){ 
				document.getElementById('FORMAT').value = val
				document.form1.submit();
			}
		});
	}
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_EMPLOYEE_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EMPLOYEE?>',
			nonSelectedText: '<?=EMPLOYEE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=EMPLOYEE?> selected', //Ticket # 1593
			enableCaseInsensitiveFiltering: true, //Ticket # 1593
		});
		
		$('#PK_DOCUMENT_TYPE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=DOCUMENT_TYPE?>',
			nonSelectedText: '<?=DOCUMENT_TYPE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=DOCUMENT_TYPE?> selected'
		});

			/* DIAM-1439 */
			$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
		$('#DOC_DEPARTMENT').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= DEPARTMENT ?>',
				nonSelectedText: '<?= DEPARTMENT ?>',
				numberDisplayed: 1,
				nSelectedText: '<?= DEPARTMENT ?> selected'
			});
			/* DIAM-1439 */

	});
	</script>
</body>

</html>
