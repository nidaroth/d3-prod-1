<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/lead_task_report.php");
require_once("check_access.php");

if(check_access('REPORT_ADMISSION') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//header("location:lead_task_report_pdf?st=".$_POST['START_DATE'].'&et='.$_POST['END_DATE'].'&dt='.$_POST['DATE_TYPE'].'&e='.$_POST['PK_EMPLOYEE_MASTER'].'&tc='.$_POST['TASK_COMPLETED']);
	
	/* Ticket # 1216 */
	if($_POST['PK_STUDENT_MASTER'] != '') {
		$PK_STUDENT_MASTER 	= implode(",",$_POST['PK_STUDENT_MASTER']);
		$PK_CAMPUS			= implode(",",$_POST['PK_CAMPUS']);
		if($_POST['FORMAT'] == 1)
			header("location:lead_task_report_pdf?st=".$_POST['START_DATE'].'&et='.$_POST['END_DATE'].'&tc='.$_POST['TASK_COMPLETED'].'&type='.$_POST['DATE_TYPE']."&sid=".$PK_STUDENT_MASTER."&campus=".$PK_CAMPUS);
		else if($_POST['FORMAT'] == 2)
			header("location:lead_task_report_excel?sid=".$PK_STUDENT_MASTER."&campus=".$PK_CAMPUS);
	}
		
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
	<title><?=MNU_LEAD_TASK_REPORT?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS {position: absolute;top: 55px;width: 142px}
		
		/* Ticket # 1751 */
		.dropdown-menu>li>a { white-space: nowrap; }
		.option_red > a > label{color:red !important}
		/* Ticket # 1751 */
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_LEAD_TASK_REPORT?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels " method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<? if($_GET['sid'] == ''){ ?>
									<div class="row" style="margin-bottom:20px" >
										<div class="col-md-2 form-group"  >
											<?=DATE_TYPE?>
											<select id="TASK_DATE_TYPE" name="TASK_DATE_TYPE" class="form-control" onchange="clear_search()" >
												<option value="TD" >Task Date</option>
												<option value="FD" >Follow Up Date</option>
											</select>
										</div>
										
										<div class="col-md-2" >
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" onchange="clear_search()" >
										</div>
										<div class="col-md-2" >
											<?=END_DATE?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" onchange="clear_search()" >
										</div>
									</div>
									
									<div class="row" style="margin-bottom:20px" >
										<div class="col-md-2 form-group" id="PK_TASK_TYPE_DIV" >
											<?=TASK_TYPE?>
											<div id="PK_TASK_TYPE_DIV_1" >
												<select id="PK_TASK_TYPE" name="PK_TASK_TYPE[]" class="form-control" multiple  >
												</select>
											</div>
										</div>
										
										<div class="col-md-2 form-group" id="PK_TASK_STATUS_DIV"  >
											<?=TASK_STATUS?>
											<div id="PK_TASK_STATUS_DIV_1" >
												<select id="PK_TASK_STATUS" name="PK_TASK_STATUS[]" multiple class="form-control"  >
												</select>
											</div>
										</div>
										
										<div class="col-md-2 form-group" id="PK_EVENT_OTHER_DIV" >
											<div id="PK_EVENT_OTHER_LABEL" ><?=TASK_OTHER?></div>
											<div id="PK_EVENT_OTHER_DIV_1" >
												<select id="PK_EVENT_OTHER" name="PK_EVENT_OTHER[]" multiple class="form-control"  >
												</select>
											</div>
										</div>
										
										<div class="col-md-2 form-group " >
											<div id="COMPLETED_LABEL" ><?=TASK_COMPLETED?></div>
											<select id="COMPLETED" name="COMPLETED" class="form-control"  onchange="clear_search()" >
												<option value="0" >Both</option>
												<option value="1" >Yes</option>
												<option value="2" >No</option>
											</select>
										</div>
									</div>
									<? } ?>
									
									<div class="row" style="margin-bottom:20px" >
										<? if($_GET['sid'] == ''){ ?>
										<div class="col-md-2" id="PK_CAMPUS_DIV" >
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" onchange="clear_search()" >
												<? /* Ticket # 1751 */
												$res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option> <!-- Ticket # 1923 -->
												<?	$res_type->MoveNext();
												} /* Ticket # 1751 */ ?>
											</select>
										</div>
										
										<div class="col-md-2" id="PK_DEPARTMENT_DIV" >
											<?=DEPARTMENT?>
											<select id="PK_DEPARTMENT" name="PK_DEPARTMENT[]" multiple class="form-control" onchange="fetch_values();clear_search()" >
												<option value="5">Accounting</option>
												<option value="1">Admissions</option>
												<option value="3">Finance</option>
												<option value="6">Placement</option>
												<option value="2">Registrar</option>
											</select>
										</div>
										
										<div class="col-md-2" id="PK_EMPLOYEE_MASTER_DIV" >
											<?=EMPLOYEE?>
											<div id="PK_EMPLOYEE_MASTER_DIV_1" >
												<select id="PK_EMPLOYEE_MASTER" name="PK_EMPLOYEE_MASTER[]" multiple class="form-control required-entry" >
												</select>
											</div>
										</div>
										
										<div class="col-md-2" id="CREATED_BY_DIV" >
											<?=CREATED_BY?>
											<div id="CREATED_BY_DIV_1" >
												<select id="CREATED_BY" name="CREATED_BY[]" multiple class="form-control" >
												</select>
											</div>
										</div>
										
										<!-- Ticket # 1590 -->
										<div class="col-md-1 align-self-center ">
											<br />
											<button type="button" onclick="search()" class="btn waves-effect waves-light btn-info"><?=SEARCH?></button>
										</div>
										<!-- Ticket # 1590 -->
										
										<? } else { ?>
										<div class="col-md-10 "></div>
										<? } ?>
										
										<div class="col-md-2 ">
											<br />
											<button type="button" onclick="submit_form(1)" style="display:none" id="btn_1" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" style="display:none" id="btn_2" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									
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
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
		
		//search(); Ticket # 1216
		
		/* Ticket # 1300 */
		<? if($_GET['sid'] != ''){ ?>
			search();
		<? } ?>
		/* Ticket # 1300 */
		
		var t = $("#PK_DEPARTMENT").val()
		get_task_type(t,0)
		get_task_status(t,0)
		get_event_other(t,1)
		get_employee(t,0)
		get_created_by(t,0)
	});
	
	function fetch_values(){
		jQuery(document).ready(function($) { 
			var t = $("#PK_DEPARTMENT").val()
			get_task_type(t,0)
			get_task_status(t,0)
			get_event_other(t,1)
			get_employee(t,0)
			get_created_by(t,0)
		});	
	}
	
	function get_task_type(val,events){
		jQuery(document).ready(function($) {
			var data  = 't='+val+'&event='+events+'&show_inactive=1';
			var value = $.ajax({
				url: "ajax_get_task_type_from_department",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					document.getElementById('PK_TASK_TYPE_DIV_1').innerHTML = data
					document.getElementById('PK_TASK_TYPE').setAttribute('multiple', true);
					document.getElementById('PK_TASK_TYPE').setAttribute('onchange', "clear_search()");
					document.getElementById('PK_TASK_TYPE').name = "PK_TASK_TYPE[]"
					$("#PK_TASK_TYPE option[value='']").remove();
					
					$("#PK_TASK_TYPE").children().first().remove();
	
					$('#PK_TASK_TYPE').multiselect({
						includeSelectAllOption: true,
						allSelectedText: 'All <?=TASK_TYPE?>',
						nonSelectedText: '',
						numberDisplayed: 2,
						nSelectedText: '<?=TASK_TYPE?> selected'
					});
				}		
			}).responseText;
		});
	}
	function get_task_status(val,events){
		jQuery(document).ready(function($) {
			var data  = 't='+val+'&event='+events+'&show_inactive=1';
			var value = $.ajax({
				url: "ajax_get_task_status_from_department",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					document.getElementById('PK_TASK_STATUS_DIV_1').innerHTML 	= data
					
					document.getElementById('PK_TASK_STATUS').setAttribute('multiple', true);
					document.getElementById('PK_TASK_TYPE').setAttribute('onchange', "clear_search()");
					document.getElementById('PK_TASK_STATUS').name = "PK_TASK_STATUS[]"
					$("#PK_TASK_STATUS option[value='']").remove();
					
					$("#PK_TASK_STATUS").children().first().remove();
	
					$('#PK_TASK_STATUS').multiselect({
						includeSelectAllOption: true,
						allSelectedText: 'All <?=TASK_STATUS?>',
						nonSelectedText: '',
						numberDisplayed: 2,
						nSelectedText: '<?=TASK_STATUS?> selected'
					});
				}		
			}).responseText;
		});
	}
	function get_event_other(val,task){
		jQuery(document).ready(function($) {
			var data  = 't='+val+'&task='+task+'&show_inactive=1';
			var value = $.ajax({
				url: "ajax_get_event_other_from_department",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					document.getElementById('PK_EVENT_OTHER_DIV_1').innerHTML = data
					
					document.getElementById('PK_EVENT_OTHER').setAttribute('multiple', true);
					document.getElementById('PK_TASK_TYPE').setAttribute('onchange', "clear_search()");
					document.getElementById('PK_EVENT_OTHER').name = "PK_EVENT_OTHER[]"
					$("#PK_EVENT_OTHER option[value='']").remove();
					
					$("#PK_EVENT_OTHER").children().first().remove();
	
					$('#PK_EVENT_OTHER').multiselect({
						includeSelectAllOption: true,
						allSelectedText: 'All <?=EVENT_OTHER?>',
						nonSelectedText: '',
						numberDisplayed: 2,
						nSelectedText: '<?=EVENT_OTHER?> selected'
					});	
					
				}		
			}).responseText;
		});
	}
	
	function get_employee(val,events){
		jQuery(document).ready(function($) {
			var data  = 't='+val+'&event='+events+'&show_inactive=1';
			var value = $.ajax({
				url: "ajax_get_employee_from_department",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					document.getElementById('PK_EMPLOYEE_MASTER_DIV_1').innerHTML = data
					
					document.getElementById('PK_EMPLOYEE_MASTER').setAttribute('multiple', true);
					document.getElementById('PK_EMPLOYEE_MASTER').setAttribute('onchange', "clear_search()");
					document.getElementById('PK_EMPLOYEE_MASTER').name = "PK_EMPLOYEE_MASTER[]"
					$("#PK_EMPLOYEE_MASTER option[value='']").remove();
					
					$("#PK_EMPLOYEE_MASTER").children().first().remove();
	
					$('#PK_EMPLOYEE_MASTER').multiselect({
						includeSelectAllOption: true,
						allSelectedText: 'All <?=EMPLOYEE?>',
						nonSelectedText: '',
						numberDisplayed: 2,
						nSelectedText: '<?=EMPLOYEE?> selected', //Ticket # 1593
						enableCaseInsensitiveFiltering: true, //Ticket # 1593
					});
			
				}		
			}).responseText;
		});
	}
	
	function get_created_by(val,events){
		jQuery(document).ready(function($) {
			var data  = 't='+val+'&event='+events+'&show_inactive=1';
			var value = $.ajax({
				url: "ajax_get_employee_from_department",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					data = data.replace("PK_EMPLOYEE_MASTER","CREATED_BY")
					document.getElementById('CREATED_BY_DIV_1').innerHTML = data
					
					document.getElementById('CREATED_BY').setAttribute('multiple', true);
					document.getElementById('PK_TASK_TYPE').setAttribute('onchange', "clear_search()");
					document.getElementById('CREATED_BY').name = "CREATED_BY[]"
					$("#CREATED_BY option[value='']").remove();
					
					$("#CREATED_BY").children().first().remove();
	
					$('#CREATED_BY').multiselect({
						includeSelectAllOption: true,
						allSelectedText: 'All <?=CREATED_BY?>',
						nonSelectedText: '',
						numberDisplayed: 2,
						nSelectedText: '<?=CREATED_BY?> selected', //Ticket # 1593
						enableCaseInsensitiveFiltering: true, //Ticket # 1593
					});
			
				}		
			}).responseText;
		});
	}
	
	function clear_search(){
		document.getElementById('student_div').innerHTML = ''
	}

	/* Ticket # 1923  */
	function search(){
		jQuery(document).ready(function($) {
			if($('#PK_CAMPUS').val() == '')
				alert("Please Select Campus");
			else {
				var data  = 'DATE_TYPE='+$('#TASK_DATE_TYPE').val()+'&START_DATE='+$('#START_DATE').val()+'&END_DATE='+$('#END_DATE').val()+'&PK_TASK_TYPE='+$('#PK_TASK_TYPE').val()+'&PK_TASK_STATUS='+$('#PK_TASK_STATUS').val()+'&PK_EVENT_OTHER='+$('#PK_EVENT_OTHER').val()+'&TASK_COMPLETED='+$('#COMPLETED').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_DEPARTMENT='+$('#PK_DEPARTMENT').val()+'&PK_EMPLOYEE_MASTER='+$('#PK_EMPLOYEE_MASTER').val()+'&CREATED_BY='+$('#CREATED_BY').val(); //Ticket # 1552 
				//alert(data)
				var value = $.ajax({
					url: "ajax_search_lead_task_report?sid=<?=$_GET['sid']?>",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('student_div').innerHTML = data
						show_btn()
					}		
				}).responseText;
			}
		});
	}
	/* Ticket # 1923  */
	
	/* Ticket # 1216 */
	function fun_select_all(){
		var str = '';
		if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
			str = true;
		else
			str = false;
			
		var PK_STUDENT_MASTER = document.getElementsByName('PK_STUDENT_MASTER[]')
		for(var i = 0 ; i < PK_STUDENT_MASTER.length ; i++){
			PK_STUDENT_MASTER[i].checked = str
		}
		get_count()
	}
	
	function show_btn(){
		
		var flag = 0;
		var PK_STUDENT_MASTER = document.getElementsByName('PK_STUDENT_MASTER[]')
		for(var i = 0 ; i < PK_STUDENT_MASTER.length ; i++){
			if(PK_STUDENT_MASTER[i].checked == true) {
				flag++;
				break;
			}
		}
		
		if(flag == 1) {
			document.getElementById('btn_1').style.display = 'inline';
			document.getElementById('btn_2').style.display = 'inline';
		} else {
			document.getElementById('btn_1').style.display = 'none';
			document.getElementById('btn_2').style.display = 'none';
		}
	}
	
	function get_count(){
		var tot = 0
		var PK_STUDENT_MASTER = document.getElementsByName('PK_STUDENT_MASTER[]')
		for(var i = 0 ; i < PK_STUDENT_MASTER.length ; i++){
			if(PK_STUDENT_MASTER[i].checked == true)
				tot++;
		}
		document.getElementById('SELECTED_COUNT').innerHTML = tot
		show_btn()
	}
	/* Ticket # 1216 */
	</script>

	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
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
		
		$('#PK_TASK_TYPE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=TASK_TYPE?>',
			nonSelectedText: '<?=TASK_TYPE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=TASK_TYPE?> selected'
		});
		
		$('#PK_TASK_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=TASK_STATUS?>',
			nonSelectedText: '<?=TASK_STATUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=TASK_STATUS?> selected'
		});
		
		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROGRAM?>',
			nonSelectedText: '<?=PROGRAM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});
		
		$('#PK_TERM_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FIRST_TERM?>',
			nonSelectedText: '<?=FIRST_TERM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=FIRST_TERM?> selected'
		});
		
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: '<?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: '<?=STATUS?>',
			nonSelectedText: '<?=STATUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STATUS?> selected'
		});
		
		$('#PK_DEPARTMENT').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=DEPARTMENT?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=DEPARTMENT?> selected'
		});
		
		$('#CREATED_BY').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CREATED_BY?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=CREATED_BY?> selected', //Ticket # 1593
			enableCaseInsensitiveFiltering: true, //Ticket # 1593
		});
	});
	</script>
</body>

</html>