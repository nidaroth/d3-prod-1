<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('REPORT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

//print_r($_POST);die;
if(!empty($_POST)){
	/* Ticket # 1217 */
	$stud_id = "";
	foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT) {
		if($stud_id != '')
			$stud_id .= ',';
		$stud_id .= $_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT];
	}
	$PK_STUDENT_MASTER=$_GET['id']		= $stud_id;
	
	$PK_STUDENT_ENROLLMENT 	= implode(",",$_POST['PK_STUDENT_ENROLLMENT']);
	
	$PK_CAMPUS = $_GET['campus'] = implode(",",$_POST['PK_CAMPUS']);

	if($PK_STUDENT_MASTER != '') {
		if($_POST['FORMAT'] == 1)
			header("location:student_invoice_pdf?id=".$PK_STUDENT_MASTER."&st=".$_POST['START_DATE']."&et=".$_POST['END_DATE'].'&t=1&campus='.$PK_CAMPUS."&exclude_no_due=".$_POST['EXCLUDE_STUDENTS_WITH_NO_PAYMENTS_DUE']);
		else if($_POST['FORMAT'] == 3)
			header("location:student_invoice_pdf?id=".$PK_STUDENT_MASTER."&st=".$_POST['START_DATE']."&et=".$_POST['END_DATE'].'&t=2&campus='.$PK_CAMPUS."&exclude_no_due=".$_POST['EXCLUDE_STUDENTS_WITH_NO_PAYMENTS_DUE']);
		else if($_POST['FORMAT'] == 2)
		require_once("student_invoice_excel.php"); // Ticket 591 excel
		exit;

		//else if($_POST['FORMAT'] == 2)
		//header("location:student_invoice_excel?id=".$PK_STUDENT_MASTER."&st=".$_POST['START_DATE']."&et=".$_POST['END_DATE'].'&campus='.$PK_CAMPUS);			
		//exit;
	}
	/* Ticket # 1217 */
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
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<title><?=MNU_STUDENT_INVOICE ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_STUDENT_STATUS, #advice-required-entry-PK_CAMPUS{position: absolute;top: 57px;width: 140px}
		.dropdown-menu>li>a { white-space: nowrap; }

		.select2-results__option .wrap:before{
    font-family:fontAwesome;
    color:#999;
    content:"\f096";
    width:25px;
    height:25px;
    padding-right: 10px;
    
}
.select2-container--default .select2-selection--single{
	border: 1px solid #e9e9e9 !important;
}
.select2-container .select2-selection--single{
	height: 34px;
}
.select2-container .select2-selection--single .select2-selection__rendered{
	padding-top: 3px !important;;
}
.multi-checkboxes_wrap:before{
    font-family:fontAwesome;
    color:#999;
    content:"\f096";
    width:25px;
    height:25px;
    padding-right: 10px;
    
}
.multi-checkboxes_wrap[aria-selected=true]:before{
    content:"\f14a";
}

/* not required css */

.row
{
  padding: 10px;
}

.select2-multiple, .select2-multiple2
{
  width: 50%
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
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_STUDENT_INVOICE?>
							<? if($_GET['m'] == 1) { ?><span style="color:red"> - No Records Found</span><? } ?>
						</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels"  method="post" name="form1" id="form1" >
									<div class="row form-group" >
										<div class="col-md-2">
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="clear_search()" >
											<option value="0">All</option>

												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										
										
										<div class="col-md-2 ">
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="clear_search()" >
											<option value="0">All</option>

												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									
										<div class="col-md-2 ">
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" onchange="clear_search()" >
											<option value="0">All</option>

												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" onchange="clear_search()" >
											<option value="0">All</option>
												<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									</div>
									<div class="row form-group">
										<div class="col-md-3 ">
											<b  style="margin-bottom:5px">Term Begin Date Range</b>
												<div class="d-flex " style="margin-bottom:5px">
												<input type="text" class="form-control date" name="term_begin_start_date" field="term_begin_start_date" id="term_begin_start_date"  placeholder="Start Date" value="" >
												<input type="text" class="form-control date" name="term_begin_end_date"  field="term_begin_end_date" id="term_begin_end_date"   placeholder="End Date" value="" >
											</div>
										</div>	
										<div class="col-md-3">
										<b  style="margin-bottom:5px">Term End Date Range</b>
												<div class="d-flex ">
												<input type="text" class="form-control date" name="term_end_start_date" id="term_end_start_date"  placeholder="Start Date">
												<input type="text" class="form-control date" name="term_end_end_date" id="term_end_end_date"   placeholder="End Date">							
								
											</div>
										</div>											
									</div>
									
									<div class="row form-group" >	
										<div class="col-md-2 ">
										Disbursement <?=START_DATE?>
											<input type="text" class="form-control date" id="START_DATE" name="START_DATE" value="01/01/2000" >
										</div>
										<div class="col-md-2 ">
										Disbursement <?=END_DATE?>
											<input type="text" class="form-control date" id="END_DATE" name="END_DATE" value="<?php echo date('m/d/Y'); ?>" >
										</div>
																		
										
										<div class="col-md-1 align-self-center ">
											<button type="button" onclick="search()" class="btn waves-effect waves-light btn-info"><?=SEARCH?></button>
										</div>
									
										
										<div class="col-md-2 align-self-center ">
											<!-- Ticket # 1217 -->
											<button type="button" onclick="submit_form(1)" id="btn_1" style="display:none;" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(3)" id="btn_3" style="display:none;" class="btn waves-effect waves-light btn-info">ZIP</button>
											<button type="button" onclick="submit_form(2)"id="btn_2"  style="display:none;" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									
									<br />
									
									<div id="student_div">
										
										<? /* Ticket # 1217 */
										$_REQUEST['show_check'] 	= 1;
										$_REQUEST['show_count'] 	= 1;
										/* Ticket # 1217 */
										//require_once('ajax_search_student_for_reports.php'); Ticket # 1278 ?>
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
	<script type="text/javascript">


		
		function get_course_offering_session(){
		}
		
		function clear_search(){
			document.getElementById('student_div').innerHTML = '';
			show_btn()
		}
		
		function search(){
			// DIAM-589
			var loader = document.getElementsByClassName("preloader_grid");
			loader[0].style.display = "block";
			// alert("checking first trigger");
			// End DIAM-589			
			jQuery(document).ready(function($) {
				var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&TREM_BEGIN_START_DATE='+$('#term_begin_start_date').val()+'&TREM_BEGIN_END_DATE='+$('#term_begin_end_date').val()+'&TREM_END_START_DATE='+$('#term_end_start_date').val()+'&TREM_END_END_DATE='+$('#term_end_end_date').val()+'&END_DATE='+$('#END_DATE').val()+'&START_DATE='+$('#START_DATE').val()+'&show_check=1&show_count=1'; //Ticket # 1552; //Ticket # 1217
				var value = $.ajax({
					url: "ajax_search_student_for_student_invoice_reports",	
					type: "POST",		 
					data: data,		
					async: true,
					cache: false,
					success: function (data) {	
						document.getElementById('student_div').innerHTML = data
						show_btn()
						loader[0].style.display = "none";
					}		
				}).responseText;
			});
		}
		
		/* Ticket # 1217 */
		function fun_select_all(){
			var str = '';
			if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
				str = true;
			else
				str = false;
				
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				PK_STUDENT_ENROLLMENT[i].checked = str
			}
			get_count()
		}
		
		function show_btn(){
			
			var flag = 0;
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true) {
					flag++;
					break;
				}
			}
			
			if(flag == 1) {
				document.getElementById('btn_1').style.display = 'inline';
				document.getElementById('btn_2').style.display = 'inline';
				document.getElementById('btn_3').style.display = 'inline';
			} else {
				document.getElementById('btn_1').style.display = 'none';
				document.getElementById('btn_2').style.display = 'none';
				document.getElementById('btn_3').style.display = 'none';
			}
		}
		
		function get_count(){
			var tot = 0
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true)
					tot++;
			}
			document.getElementById('SELECTED_COUNT').innerHTML = tot
			show_btn()
		}
		/* Ticket # 1217 */

	</script>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});

	</script>
	<script src="../backend_assets/dist/js/select2.multi-checkboxes.js"></script>

	<!-- <script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/> -->
	<script type="text/javascript">
	jQuery(document).ready(function($) {

	});
	function get_course_offering(val){
			/*$.fn.select2.amd.require(
      [
        'select2/multi-checkboxes/dropdown',
        'select2/multi-checkboxes/selection',
        'select2/multi-checkboxes/results'
      ],
      function(DropdownAdapter, SelectionAdapter, ResultsAdapter) {
				console.log($('#PK_COURSE').val());
				var data  = 'val='+$('#PK_COURSE').val()+'&multiple=0';
				var value = $.ajax({
					url: "ajax_get_course_offering",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
						document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
						document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"
						$("#PK_COURSE_OFFERING option[value='']").remove();
						
						document.getElementById('PK_COURSE_OFFERING').setAttribute("onchange", "clear_search()");
						
						$('#PK_COURSE_OFFERING').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
							nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
							numberDisplayed: 2,
							nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
						});
					}		
				}).responseText;
			});*/
		}
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
				const exclude=$('#EXCLUDE_STUDENTS_WITH_NO_PAYMENTS_DUE').val();
				const campus=$('#PK_CAMPUS').val();
				const st=$('#START_DATE').val();
				const et=$('#END_DATE').val();
				if(val==1){					 
					document.getElementById('form1').action="student_invoice_pdf?st="+st+"&et="+et+"&t=1&campus="+campus+"&exclude_no_due="+exclude;
				}
				if(val==3){
					document.getElementById('form1').action="student_invoice_pdf?st="+st+"&et="+et+"&t=2&campus="+campus+"&exclude_no_due="+exclude;
				}
				if(val==2){
					document.getElementById('form1').action="";
				}
				
				document.form1.submit();				
			}
		});
	}
	</script>
</body>

</html>