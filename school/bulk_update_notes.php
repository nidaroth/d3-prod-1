<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/notes.php");
require_once("../language/student.php");
require_once("get_department_from_t.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 && check_access('MANAGEMENT_BULK_UPDATE') == 0){
	header("location:../index");
	exit;
}

$title1 = MNU_UPDATE_NOTES;
if($_GET['event'] == 1)
	$title1 = MNU_UPDATE_EVENTS; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=$title1 ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
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
                        <h4 class="text-themecolor"><?=$title1?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row" style="padding-bottom:10px;" >
										<div class="col-md-2 ">
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
											
										<div class="col-md-2 ">
											<div class="form-group m-b-40">
												<select id="t" name="t" class="form-control required-entry" onchange="get_note_type(this.value);get_note_status(this.value); get_employee(this.value); <? if($_GET['event'] == 1) { ?>get_event_other(this.value)<? } ?> " >
													<option ></option>
													<option value="1" >Admissions</option>
													<option value="2" >Registrar</option>
													<option value="3" >Finance</option>
													<option value="5" >Accounting</option>
													<option value="6" >Placement</option>
												</select>
												<span class="bar"></span> 
												<label for="PK_NOTE_TYPE">
													<?=DEPARTMENT ?>
												</label>
											</div>
										</div>
										
										<div class="col-md-2 " >
											<!-- Ticket # 1593 -->
											<div class="form-group m-b-40" style="margin-top: -11px;" >
												<?=EMPLOYEE ?>
												<div id="PK_EMPLOYEE_MASTER_DIV" >
													<? $_REQUEST['show_inactive'] = 1;
													include("ajax_get_employee_from_department.php"); ?>
												</div>
											</div>
											<!-- Ticket # 1593 -->
										</div>
									</div>
									
									<div class="row" style="padding-bottom:10px;" >
										
										<div class="col-md-2 " >
											<div class="form-group m-b-40">
												<div id="PK_NOTE_TYPE_DIV" >
													<? $_REQUEST['event'] = $_GET['event'];
													$_REQUEST['show_inactive'] = 1;
													include("ajax_get_note_type_from_department.php"); ?>
												</div>
												<span class="bar"></span> 
												<label for="PK_NOTE_TYPE">
													<? if($_GET['event'] == 1) echo EVENT_TYPE; else echo NOTES_TYPE; ?>
												</label>
											</div>
										</div>
										
										<div class="col-md-2 ">
											<div class="form-group m-b-40">
												<div id="PK_NOTE_STATUS_DIV" >
													<? $_REQUEST['event'] = $_GET['event'];
													$_REQUEST['show_inactive'] = 1;
													include("ajax_get_note_status_from_department.php"); ?>
												</div>
												<span class="bar"></span> 
												<label for="PK_NOTE_STATUS">
													<? if($_GET['event'] == 1) echo EVENT_STATUS; else echo NOTE_STATUS;?>
												</label>
											</div>
										</div>
										
										<? if($_GET['event'] == 1) { ?>
										<div class="col-md-2">
											<div class="form-group m-b-40">
												<div id="PK_EVENT_OTHER_DIV" >
													<? $_REQUEST['event'] = $_GET['event'];
													$_REQUEST['show_inactive'] = 1;
													
													include("ajax_get_event_other_from_department.php"); ?>
												</div>
												<span class="bar"></span> 
												<label for="PK_EVENT_OTHER">
													<?=EVENT_OTHER?>
												</label>
											</div>
										</div>
										<? } ?>
										
										<div class="col-md-2 ">
											<div class="form-group m-b-40">
												<select id="NOTE_COMPLETED" name="NOTE_COMPLETED" class="form-control" >
													<option >Both</option>
													<option value="1" >Yes</option>
													<option value="2" >No</option>
												</select>
												<span class="bar"></span> 
												<label for="NOTE_COMPLETED">
													<? if($_GET['event'] == 0)  echo NOTE_COMPLETED; else echo EVENT_COMPLETED; ?>
												</label>
											</div>
										</div>
									</div>
									
									<div class="row" style="padding-bottom:10px;" >
										<div class="col-md-2">
											<div class="form-group m-b-40" id="NOTE_DATE_LABEL" >
												<input type="text" class="form-control date" id="FROM_NOTE_DATE" name="FROM_NOTE_DATE" value="" >
												<span class="bar"></span>
												<label for="FROM_NOTE_DATE">
													<? if($_GET['event'] == 1) echo FROM_EVENT_DATE; else echo FROM_NOTE_DATE;?>
												</label>
											</div>
										</div>
										
										<div class="col-md-2">
											<div class="form-group m-b-40" id="NOTE_DATE_LABEL" >
												<input type="text" class="form-control date" id="TO_NOTE_DATE" name="TO_NOTE_DATE" value="" >
												<span class="bar"></span>
												<label for="TO_NOTE_DATE">
													<? if($_GET['event'] == 1) echo TO_EVENT_DATE; else echo TO_NOTE_DATE;?>
												</label>
											</div>
										</div>
										
										<div class="col-md-2">
											<div class="form-group m-b-40">
												<input type="text" class="form-control date" id="FROM_FOLLOWUP_DATE" name="FROM_FOLLOWUP_DATE" value="" >
												<span class="bar"></span>
												<label for="FROM_FOLLOWUP_DATE"><?=FROM_FOLLOWUP_DATE?></label>
											</div>
										</div>
										
										<div class="col-md-2">
											<div class="form-group m-b-40">
												<input type="text" class="form-control date" id="TO_FOLLOWUP_DATE" name="TO_FOLLOWUP_DATE" value="" >
												<span class="bar"></span>
												<label for="TO_FOLLOWUP_DATE"><?=TO_FOLLOWUP_DATE?></label>
											</div>
										</div>
										
										<div class="col-md-1" >
											<button type="button" class="btn waves-effect waves-light btn-dark" onclick="search()" ><?=SEARCH?></button>
										</div>
										
										<div class="col-md-1 " >
											<button type="button" onclick="show_form()" class="btn waves-effect waves-light btn-dark" style="display:none" id="btn" ><?=UPDATE ?></button>
										</div>
									</div>
								
									<br />
									<div class="row" style="padding-bottom:10px;display:none" id="count_div" >
										<div class="col-md-10 "></div>
										<div class="col-md-2 " style="font-weight:bold;" >
											<?=TOTAL_COUNT.': ' ?><span id="TOTAL_COUNT"></span>
											<br /><?=SELECTED_COUNT.': ' ?><span id="SELECTED_COUNT"></span>
										</div>
									</div>
									<div id="student_div" style="max-height:300px;overflow: auto;"></div>
								
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
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');

		function search(){
			jQuery(document).ready(function($) {
				var data  = 't='+$('#t').val()+'&PK_NOTE_TYPE='+$('#PK_NOTE_TYPE').val()+'&PK_NOTE_STATUS='+$('#PK_NOTE_STATUS').val()+'&PK_EVENT_OTHER='+$('#PK_EVENT_OTHER').val()+'&FROM_NOTE_DATE='+$('#FROM_NOTE_DATE').val()+'&TO_NOTE_DATE='+$('#TO_NOTE_DATE').val()+'&FROM_FOLLOWUP_DATE='+$('#FROM_FOLLOWUP_DATE').val()+'&TO_FOLLOWUP_DATE='+$('#TO_FOLLOWUP_DATE').val()+'&PK_EMPLOYEE_MASTER='+$('#PK_EMPLOYEE_MASTER').val()+'&event=<?=$_GET['event']?>&NOTE_COMPLETED='+$('#NOTE_COMPLETED').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val();
				var value = $.ajax({
					url: "ajax_search_student_notes",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						data = data.split("|||");
						document.getElementById('student_div').innerHTML 	= data[0]
						document.getElementById('TOTAL_COUNT').innerHTML 	= data[1]
						document.getElementById('count_div').style.display 	= 'flex';
						
						get_count()
					}		
				}).responseText;
			});
		}
		function get_note_type(val){
			jQuery(document).ready(function($) {
				var data  = 't='+val+'&event=<?=$_GET['event']?>'+'&show_inactive=1';
				var value = $.ajax({
					url: "ajax_get_note_type_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_NOTE_TYPE_DIV').innerHTML = data
						$('.floating-labels .form-control').on('focus blur', function (e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
					}		
				}).responseText;
			});
		}
		function get_event_other(val){
			jQuery(document).ready(function($) {
				var data  = 't='+val+'&show_inactive=1';
				var value = $.ajax({
					url: "ajax_get_event_other_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_EVENT_OTHER_DIV').innerHTML = data
						$('.floating-labels .form-control').on('focus blur', function (e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
					}		
				}).responseText;
			});
		}
		function get_note_status(val){
			jQuery(document).ready(function($) {
				var data  = 't='+val+'&event=<?=$_GET['event']?>&show_inactive=1';
				var value = $.ajax({
					url: "ajax_get_note_status_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_NOTE_STATUS_DIV').innerHTML = data
						$('.floating-labels .form-control').on('focus blur', function (e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
					}		
				}).responseText;
			});
		}
		function get_employee(val){
			jQuery(document).ready(function($) {
				var data  = 't='+val+'&event=<?=$_GET['event']?>&show_inactive=1';
				var value = $.ajax({
					url: "ajax_get_employee_from_department",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('PK_EMPLOYEE_MASTER_DIV').innerHTML = data
						$('#PK_EMPLOYEE_MASTER').select2(); //Ticket # 1593
						
						$('.floating-labels .form-control').on('focus blur', function (e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
					}		
				}).responseText;
			});
		}
		
		function fun_select_all(){
			var str = '';
			if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
				str = true;
			else
				str = false;
				
			var PK_STUDENT_NOTES = document.getElementsByName('PK_STUDENT_NOTES[]')
			for(var i = 0 ; i < PK_STUDENT_NOTES.length ; i++){
				PK_STUDENT_NOTES[i].checked = str
			}
			get_count()
		}
		
		function get_count(){
			var tot = 0
			var PK_STUDENT_NOTES = document.getElementsByName('PK_STUDENT_NOTES[]')
			for(var i = 0 ; i < PK_STUDENT_NOTES.length ; i++){
				if(PK_STUDENT_NOTES[i].checked == true)
					tot++;
			}
			document.getElementById('SELECTED_COUNT').innerHTML = tot
			show_btn()
		}
		
		function show_btn(){
			
			var flag = 0;
			var PK_STUDENT_NOTES = document.getElementsByName('PK_STUDENT_NOTES[]')
			for(var i = 0 ; i < PK_STUDENT_NOTES.length ; i++){
				if(PK_STUDENT_NOTES[i].checked == true) {
					flag++;
					break;
				}
			}
			
			if(flag == 1)
				document.getElementById('btn').style.display = 'block';
			else
				document.getElementById('btn').style.display = 'none';
		}
		
		function show_form(){
			var w = 1200;
			var h = 550;
			// var id = common_id;
			var left = (screen.width/2)-(w/2);
			var top = (screen.height/2)-(h/2);
			var parameter = 'toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,width='+w+', height='+h+', top='+top+', left='+left;
			var t = document.getElementById('t').value
			window.open('update_notes?event=<?=$_GET['event']?>&t='+t,'',parameter);
			return false;
		}
		function close_win(win){
			win.close();
			search()
		}
		
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
	});
	</script>
	
	<!--  Ticket # 1593 -->
	<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
	<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#PK_EMPLOYEE_MASTER').select2();
		});
	</script>
	<!--  Ticket # 1593 -->
</body>

</html>