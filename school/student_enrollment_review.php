<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("../language/student.php"); // DIAM-1199 LDA
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){ // DIAM-1626
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	$temp = explode(",",$_POST['SELECTED_PK_STUDENT_MASTER']);
	$temp = array_unique($temp, SORT_NUMERIC);
	$PK_STUDENT_MASTER = implode(",",$temp);
	// $PK_STUDENT_ENROLLMENT 	= implode(",",$_POST['PK_STUDENT_ENROLLMENT']);

	if($_POST['FORMAT']==1){
		include('student_enrollment_review_pdf.php');
	}else if($_POST['FORMAT']==2){
		include('student_enrollment_review_excel.php');
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
	<title><?=MNU_STUDENT_ENROLLMENT_REVIEW ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_STUDENT_STATUS, #advice-required-entry-PK_CAMPUS,#advice-required-entry-PK_TERM_MASTER1{position: absolute;top: 57px;width: 140px}
		.dropdown-menu>li>a { white-space: nowrap; }
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
		.option_red > a > label{color:red !important}
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
							<?=MNU_STUDENT_ENROLLMENT_REVIEW?>
							<? if($_GET['m'] == 1) { ?><span style="color:red"> - No Records Found</span><? } ?>
						</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels" method="post" name="form1" id="form1" >
									<input type="hidden" name="SELECTED_PK_STUDENT_MASTER" id="SELECTED_PK_STUDENT_MASTER" value="" >
									<!-- <div class="row form-group"  > -->
										<!-- <div class="col-md-2"> -->
											<!-- Enrollment Type -->
											<!-- <select id="ENROLLMENT_TYPE" name="ENROLLMENT_TYPE"  class="form-control" > -->
												<!-- <option value="1">All Enrollments</option> -->
												<!-- <option value="2">Current Enrollments</option> -->
											<!-- </select> -->
										<!-- </div> -->
<!-- 										 -->
										<!-- <div class="col-md-2 align-self-center  "> -->
											<!-- <button type="button" onclick="submit_form(1)" id="btn_1" style="display:none;" class="btn waves-effect waves-light btn-info"><?=PDF?></button> -->
											<!-- <input type="hidden" name="FORMAT" id="FORMAT" > -->
										<!-- </div> -->
									<!-- </div> -->
									<br/><br/>
									<div class="row form-group" >
										
										<div class="col-md-2">
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option><!-- DIAM-2059 -->
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="clear_search()" >
												<!-- DIAM-2186 -->
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION, ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['DESCRIPTION'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$res_type->fields['CODE'].' - '.$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
												<!-- DIAM-2186 -->
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										
									</div>
											<div class="row form-group"  >
											<!-- DIAM-2059 -->
											<div class="col-md-2 mt-4" id="COURSE_TERM_START_DATE_DIV" >
												<select id="COURSE_PK_TERM_MASTER" name="COURSE_PK_TERM_MASTER[]" multiple class="form-control" >
													<? $res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");													
													while (!$res_type->EOF) { ?>
														<?php if($res_type->fields['BEGIN_DATE_1']!=""){ ?>
															<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1']." - ".$res_type->fields['TERM_DESCRIPTION']?></option>
														<?php } ?>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											<!-- DIAM-2059 -->
											<!--   DIAM-1199 LDA --> 
											<!-- <div class="col-md-2">
												<?=COURSE_TERM_START_DATE?>
												<input type="text" class="form-control date" id="COURSE_TERM_START_DATE" name="COURSE_TERM_START_DATE" value="" >
											</div>
										
											<div class="col-md-2">
												<?=COURSE_TERM_END_DATE?>
												<input type="text" class="form-control date" id="COURSE_TERM_END_DATE" name="COURSE_TERM_END_DATE" value="" >
											</div> -->
											<!--   DIAM-1199 LDA --> 

											<!--   DIAM-1199 LDA --> 
											<div class="col-md-2">
												<?=LDA_START_DATE?>
												<input type="text" class="form-control date" id="LDA_START_DATE" name="LDA_START_DATE" value="" >
											</div>
										
											<div class="col-md-2">
												<?=LDA_END_DATE?>
												<input type="text" class="form-control date" id="LDA_END_DATE" name="LDA_END_DATE" value="" >
											</div>
											<!--   DIAM-1199 LDA --> 
										
											<div class="col-md-2 align-self-center ">
											<button type="button" onclick="search()" class="btn waves-effect waves-light btn-info"><?=SEARCH?></button>
											<button type="button" onclick="submit_form(1)" id="btn_1" style="display:none;" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info" id="btn_2" style="display:none"><?= EXCEL ?></button>
											 <input type="hidden" name="FORMAT" id="FORMAT" >
											</div>
											</div>	
								
									<br />
									<div id="student_div" >
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

		function clear_search(){
			document.getElementById('student_div').innerHTML = '';
			show_btn()
		}
		
		function search(){
			document.getElementById('loaders').style.display = 'block';
			jQuery(document).ready(function($) {			
				var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&show_check=1&show_count=1&LDA_START_DATE='+$('#LDA_START_DATE').val()+'&LDA_END_DATE='+$('#LDA_END_DATE').val()+'&PK_COURSE_OFFERING_TERM_MASTER='+ $('#COURSE_PK_TERM_MASTER').val()+'&STUDENT_REVIEW=1&ENROLLMENT=0'; //DAIM-1199 LDA DIAM-2240
				//DIAM-2059
				
				var value = $.ajax({
					url: "ajax_search_student_for_reports",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('student_div').innerHTML = data;
						document.getElementById('loaders').style.display = 'none';
						show_btn()
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
			} else {
				document.getElementById('btn_1').style.display = 'none';
				document.getElementById('btn_2').style.display = 'none';
			}
		}
		
		function get_count(){
			var PK_STUDENT_MASTER_sel = '';
			var tot = 0
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true) {
					if(PK_STUDENT_MASTER_sel != '')
						PK_STUDENT_MASTER_sel += ',';
						
					PK_STUDENT_MASTER_sel += document.getElementById('S_PK_STUDENT_MASTER_'+PK_STUDENT_ENROLLMENT[i].value).value
					tot++;
				}
			}
			document.getElementById('SELECTED_PK_STUDENT_MASTER').value = PK_STUDENT_MASTER_sel
			//alert(PK_STUDENT_MASTER_sel)
			
			document.getElementById('SELECTED_COUNT').innerHTML = tot
			show_btn()
		}
	</script>
	
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

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {

		$('#PK_STUDENT_GROUP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=GROUP_CODE?>',
			nonSelectedText: '<?=GROUP_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=GROUP_CODE?> selected'
		});
		$('#PK_TERM_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FIRST_TERM?>',
			nonSelectedText: '<?=FIRST_TERM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=FIRST_TERM?> selected'
		});
		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROGRAM?>',
			nonSelectedText: '<?=PROGRAM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STATUS?>',
			nonSelectedText: '<?=STATUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STATUS?> selected'
		});

		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});

		//DIAM-2059
		$('#COURSE_PK_TERM_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_TERM?>',
			nonSelectedText: '<?=COURSE_TERM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_TERM?> selected',
			enableCaseInsensitiveFiltering: true
		});		
		//DIAM-2059
	});
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
</body>

</html>
