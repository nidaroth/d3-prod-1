<?php
require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/sap_scale.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if(has_custom_sap_report($_SESSION['PK_ACCOUNT'])==0){
	header("location:../index");
	exit;
}
//$sap_pk_array=array('15','67','72');
// if(!in_array($_SESSION['PK_ACCOUNT'],$sap_pk_array))
// {   
// 	header("location:../school/index");
// 	exit;
// }


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
	<title><?=MNU_CUSTOM_REPORTS?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		/* Ticket # 1149 - term */
		.dropdown-menu>li>a { white-space: nowrap; }
		.option_red > a > label{color:red !important}
		/* Ticket # 1149 - term */
		
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
						<?=MNU_CUSTOM_REPORTS ?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row">
										<div class="col-md-3 m-b-30">
												<label>Report</label>
												<select id="report_type" name=""  onchange="changeOption(this.value)"  class="form-control m-t-30" style="width:350px;">
													<!-- DIAM-1460-->
													<?
													if(av_check_access('has_attendance_makeup_report'))
													{
													
														if($_SESSION['PK_ACCOUNT'] == 15)
														{

															?>
															<option value="1">Custom SAP</option>
															<option value="2">Course Offering Grade Book Transcript</option>
															<option value="3">Course Offering Program Grade Book Report</option><!-- DIAM-981-->
															<option value="6">Custom Transcript Report</option>
															<?

														}
														?>
														<option value="4">Attendance Weekly Posting</option>
														<option value="5">Make Up Forms Overdue</option>
														<? 
														
													}
													else if(av_check_access('has_transcript_report')) // DIAM-1151
													{
														?>
														<option value="6">Custom Transcript Report</option>
														<?
													}
													else if(av_check_access('has_custom_report')){
														?>
														<option value="1">Custom SAP</option>
														<option value="2">Course Offering Grade Book Transcript</option>
														<option value="3">Course Offering Program Grade Book Report</option><!-- DIAM-981-->
														<?
													}
													 ?>
													<!-- DIAM-1460-->
												</select>
										</div>
									</div>
									<div class="row">
										<div class="col-md-2 m-b-30">
												<label>Report Options</label>
												<select id="report_option" name="report_option"  onchange="reportOption(this.value)" class="form-control m-t-30" >
													<option value="1">All Enrollments</option>
													<option value="2">Current Enrollment</option>
													<option value="3">By Term</option>
												</select>
										</div>
									</div>



									

									<div class="row">
										<span class="row m-b-40" style="display:none;width:93%" id="by_term_date">
													<div class="col-md-12 m-b-40"> <b>Term Start Range</b> </div>
									
													<div class="col-md-2 focused">
														<label > Course First term Date</label> <!-- DIAM-1045 -->
														<input type="text" name="MIDPOINT_START_DATE" id="MIDPOINT_START_DATE" class="form-control date" value="" >
														<span class="bar"></span>
													</div>
													<div class="col-md-2 focused">
														<label > Course Last term Date</label> <!-- DIAM-1045 -->
														<input type="text" name="MIDPOINT_END_DATE" id="MIDPOINT_END_DATE" class="form-control date" value="" >
														<span class="bar"></span>
													</div>
													
													<!-- DIAM-1045 -->
													<div class="col-md-3" id="COURSE_COURSE_DIV" style="display:none" >			 
														<!-- <select id="COURSE_PK_COURSE" name="COURSE_PK_COURSE[]" multiple class="form-control required-entry"></select> -->
														<select id="COURSE_PK_COURSE" name="COURSE_PK_COURSE[]" multiple class="form-control" onchange="get_course_offering_by_course(this.value);">
															<? 
															$res_type = $db->Execute("select ACTIVE,PK_COURSE,COURSE_CODE, TRANSCRIPT_CODE,COURSE_DESCRIPTION from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC, COURSE_CODE ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?= $res_type->fields['PK_COURSE'] ?>" <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['COURSE_CODE'] . ' - ' . $res_type->fields['TRANSCRIPT_CODE'] . ' - ' . $res_type->fields['COURSE_DESCRIPTION'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
																</option>
															<? $res_type->MoveNext();
															} ?>
														</select>
														<?php
														$PK_TERM_MASTER_VALUES = array();
														$sql = "select S_TERM_MASTER.PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1,  IF(END_DATE = '0000-00-00','', DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER LEFT JOIN S_TERM_MASTER_CAMPUS ON S_TERM_MASTER_CAMPUS.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER WHERE S_TERM_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' GROUP BY S_TERM_MASTER.PK_TERM_MASTER order by BEGIN_DATE DESC";
													   //  echo $sql;exit;
													   $res_type = $db->Execute($sql);
												   
													   while (!$res_type->EOF) { 
														   $PK_TERM_MASTER_VALUES[] = $res_type->fields['PK_TERM_MASTER'];	
														   $res_type->MoveNext();
													   }
													   $PK_TERM = implode(",",$PK_TERM_MASTER_VALUES);

														?>
														<input type="hidden" name="COURSE_PK_TERM" id="COURSE_PK_TERM" value="<?=$PK_TERM?>"> 

													</div>

													<div class="col-md-3" id="COURSE_COURSE_OFFERING_DIV" style="display:none">
														<select id="COURSE_PK_COURSE_OFFERING" name="COURSE_PK_COURSE_OFFERING[]" multiple class="form-control required-entry"></select>
													</div>
												<!-- DIAM-1045 -->													
										</span>					
                                     
                                    </div>
									<div style="display:none" id="transcript_option">
											<div class="row"  >
												<div class="col-md-2 m-b-30">
														<label>Show Attendance Summary</label>
														<select id="show_attenance_summary" name="show_attenance_summary" class="form-control m-t-30" style="width:170px">
															<option value="yes">Yes</option>
															<option value="no">No</option>
														</select>
												</div>
												<!-- DIAM-981 -->
												<div class="col-md-2 m-b-30">
														<label>Official Signature </label>
														<select id="show_signature_line" name="show_signature_line" class="form-control m-t-30" style="width:170px">
															<option value="yes">Yes</option>
															<option value="no">No</option>
														</select>
												</div>
												
												<div class="col-md-2 m-b-30" id="student_sign_option" style="display:none">
														<label>Show Student Signature</label>
														<select id="show_student_signature_line" name="show_student_signature_line" class="form-control m-t-30" style="width:170px">
															<option value="yes">Yes</option>
															<option value="no">No</option>
														</select>
												</div>

												<div class="col-md-2 m-b-30"  id="trans_credit_option" style="display:none">
													<label>Show Transfer Credit</label>
													<select id="GB_DISPLAY_TRANSFER_CREDIT" name="GB_DISPLAY_TRANSFER_CREDIT" class="form-control m-t-30" style="width:170px">
														<option value="0">Yes</option>
														<option value="1">No</option>
													</select>
												</div>

												<div class="col-md-2 m-b-30"  id="progress_option" style="display:none">
													<label></label>
													<select id="GB_SHOW" name="GB_SHOW" class="form-control m-t-30" style="width: 270px;">
														<option value="1">Both Completed and In Progress</option>
														<option value="2">Completed Only</option>
														<option value="3">In Progress</option>
													</select>
												</div>
												<!-- DIAM-981 -->

											</div>
									</div>

									



									<div class="row" style="padding-bottom:10px;" >
									   <!-- DIAM-1045 -->
										<div class="col-md-12 m-b-30">
											<label>Student Filters</label>
										</div>
										<!-- DIAM-1045 -->
										<div class="col-md-2 ">
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("SELECT CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" >
												<? /* Ticket #1149 - term */
												$res_type = $db->Execute("SELECT PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
												while (!$res_type->EOF) { 
													$str = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'];
													if($res_type->fields['ACTIVE'] == 0)
														$str .= ' (Inactive)'; ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>  ><?=$str ?></option>
												<?	$res_type->MoveNext();
												} /* Ticket #1149 - term */ ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" >
												<? $res_type = $db->Execute("SELECT PK_CAMPUS_PROGRAM,CODE, DESCRIPTION, ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC,CODE ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION'];
                                                    if($res_type->fields['ACTIVE'] == 0)
                                                    {
                                                        $option_label .= " (Inactive)"; 
                                                    }
													?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>

          
										
										<div class="col-md-2" >
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control">
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION, ADMISSIONS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' AND ADMISSIONS = 0 order by ADMISSIONS DESC, STUDENT_STATUS ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['STUDENT_STATUS'] . ' - ' . $res_type->fields['DESCRIPTION'];
													
													?>
													<option value="<?= $res_type->fields['PK_STUDENT_STATUS'] ?>" ><?=$option_label ?></option>
												<? $res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" >
												<? $res_type = $db->Execute("SELECT PK_STUDENT_GROUP,STUDENT_GROUP,ACTIVE from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, STUDENT_GROUP ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['STUDENT_GROUP'];
                                                    if($res_type->fields['ACTIVE'] == 0)
                                                    {
                                                        $option_label .= " (Inactive)"; 
                                                    }
													?>
													<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
												
									</div>
									<!--  DIAM-1003 -->
									<div class="row" style="margin-top:10px">
										<div class="col-md-2 mt-2 " id="SEARCH_TXT_DIV" style="display:none;">
											<input type="text" class="form-control" id="SEARCH_TXT" name="SEARCH_TXT" placeholder="&#xF002; <?= SEARCH ?>" style="font-family: FontAwesome;" onkeypress="do_search(event)">
										</div>
									</div>
									<!--  DIAM-1003 -->
                                    <br /><br />
									<div class="row">
										<div class="col-md-2">										
											<!-- <button type="button" class="btn waves-effect waves-light btn-info" id="btn" style="display:none" onclick="submit_form(1)" ><?=EXCEL?></button> -->
											<button type="button" onclick="submit_form(2)" id="btn_1" class="btn waves-effect waves-light btn-info" style="display:none"><?=PDF?></button>
											<button type="button" class="btn waves-effect waves-light btn-info" onclick="search()" ><?=SEARCH?></button>
                                            <input type="hidden" name="FORMAT" id="FORMAT">
										</div>
									</div>
									
									
                                   
									<br />
									<div class="row">
										<div class="col-sm-12 pt-25 " >
											<div id="student_div">
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
   
	<? 
	require_once("js.php"); 
	$file_name      = 'SAP Global Report.xlsx';
	$outputFileName = $file_name;
	$outputFileName = str_replace(
		pathinfo($outputFileName, PATHINFO_FILENAME),
		pathinfo($outputFileName, PATHINFO_FILENAME) . "_" . $_SESSION['PK_USER'] . "_" . time(),
		$outputFileName
	);

	?>
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

		});


		function reportOption(val){
			if(val==3){
				jQuery('#by_term_date').show();
			}else{
				jQuery('#by_term_date').hide();
				jQuery('#MIDPOINT_START_DATE').val('');
				jQuery('#MIDPOINT_END_DATE').val('');
				jQuery('#COURSE_PK_COURSE').multiselect( 'clearSelection' );
				jQuery('#COURSE_PK_COURSE_OFFERING').multiselect( 'clearSelection' );
			}

		}

		function changeOption(val){
			if(val==2 || val==3){ //DIAM-981
				jQuery('#transcript_option').show();

				if(val==3){
					jQuery('#trans_credit_option,#student_sign_option,#progress_option,#COURSE_COURSE_DIV,#COURSE_COURSE_OFFERING_DIV').show();				
				}else{
					jQuery('#trans_credit_option,#student_sign_option,#progress_option,#COURSE_COURSE_DIV,#COURSE_COURSE_OFFERING_DIV').hide();	
				}

			}else{
				jQuery('#transcript_option').hide();
				jQuery('#trans_credit_option,#student_sign_option,#progress_option,#COURSE_COURSE_DIV,#COURSE_COURSE_OFFERING_DIV').hide();	
			}
		}
		/* DIAM-1003 */
		function do_search(e) {
			if (e.keyCode == 13) {
				search();
			}
		}
		/*  DIAM-1003 */
		function search(){
			document.getElementById('loaders').style.display = 'block';

			jQuery(document).ready(function($) {

				//DIAM-1045
				var dataparam  = '';
				if(document.getElementById('report_type').value==3){

					var COURSE_PK_COURSE_OFFERING = $('#COURSE_PK_COURSE_OFFERING').val();
					if (COURSE_PK_COURSE_OFFERING === undefined) {
						COURSE_PK_COURSE_OFFERING = "";
					}

					var COURSE_PK_COURSE = $('#COURSE_PK_COURSE').val();
					if (COURSE_PK_COURSE === undefined) {
						COURSE_PK_COURSE = "";
					}

					dataparam = '&PK_COURSE=' + COURSE_PK_COURSE + '&PK_COURSE_OFFERING=' + COURSE_PK_COURSE_OFFERING;
				}
				//DIAM-1045

                var data  = 'PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&MIDPOINT_START_DATE='+$('#MIDPOINT_START_DATE').val()+'&MIDPOINT_END_DATE='+$('#MIDPOINT_END_DATE').val()+'&report_option='+$('#report_option').val()+ '&SEARCH_TXT=' + $('#SEARCH_TXT').val()+dataparam;// DIAM-1003
				var value = $.ajax({
					url: "ajax_custom_sap_report",	
					type: "POST",		 
					data: data,		
					async: true,
					cache: false,
					success: function (data) {	
						document.getElementById('student_div').innerHTML = data
						document.getElementById('loaders').style.display = 'none';
						document.getElementById('SEARCH_TXT_DIV').style.display = 'block'; //DIAM-1003

					}		
				}).responseText;
			});
		}

		function show_btn()
        {
			var flag = 0;
			var PK_STUDENT_ENROLLMENT = document.getElementsByClassName('stud_enr')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true) {
					flag++;
					break;
				}
			}
			
			if(flag == 1){
				//document.getElementById('btn').style.display = 'inline';
				document.getElementById('btn_1').style.display = 'inline';
			}else{
				//document.getElementById('btn').style.display = 'none';
				document.getElementById('btn_1').style.display = 'none';
			}			
		}
		
		function get_count(){
			var tot = 0
			var PK_STUDENT_ENROLLMENT = document.getElementsByClassName('stud_enr')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true)
                {
                    tot++;
                }
			}
			document.getElementById('SELECTED_COUNT').innerHTML = tot
			show_btn()
		}

		function fun_select_all(){
			var str = '';
			if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
			{
				str = true;
			}
			else
			{
				str = false;
			}
				
			var PK_STUDENT_ENROLLMENT = document.getElementsByClassName('stud_enr');
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++)
			{
				PK_STUDENT_ENROLLMENT[i].checked = str
			}
			get_count()
		}
		
		function submit_form(val)
        {
			document.getElementById('loaders').style.display = 'block';
            var valid = new Validation('form1', {
                onSubmit: false
            });
            var result = valid.validate();
            if (result == true) {
                val=document.getElementById('report_type').value;
                if(val==1)
                {
				 	var ajax_url = 'custom_sap_report_pdf';
				}else if(val==2){
					var ajax_url = 'custom_course_offering_grade_book_transcript_report_pdf';
					
				}else if(val==3){ //DIAM-981
					var ajax_url = 'custom_course_offering_grade_book_progress_report_pdf';
					//jQuery('#MIDPOINT_START_DATE').val('');
					//jQuery('#MIDPOINT_END_DATE').val('');
				}
				else if(val==4){ //DIAM-1460
					var ajax_url = 'custom_attendance_weekly_posting_report';
					
				}
				else if(val==5){ //DIAM-1460
					var ajax_url = 'custom_makeup_forms_overdue_report';
					
				}
				else if(val==6){ //DIAM-1151
					var ajax_url = 'custom_transcript_report';
					
				}
					var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
					var PK_STUDENT_MASTER = document.getElementsByName('PK_STUDENT_MASTER[]')
					var data = { 'PK_STUDENT_ENROLLMENT[]' : [], 'PK_STUDENT_MASTER[]' : [],'REPORT_OPTION':jQuery('#report_option').val(),'MIDPOINT_START_DATE':jQuery('#MIDPOINT_START_DATE').val(),'MIDPOINT_END_DATE':jQuery('#MIDPOINT_END_DATE').val(),'show_attenance_summary':jQuery('#show_attenance_summary').val(),'show_signature_line':jQuery('#show_signature_line').val(),'show_student_signature_line':jQuery('#show_student_signature_line').val(),'show':jQuery('#GB_SHOW').val(),'exclude_tc':jQuery('#GB_DISPLAY_TRANSFER_CREDIT').val()};
					for (var i = 0; i < PK_STUDENT_ENROLLMENT.length; i++) {
							if (PK_STUDENT_ENROLLMENT[i].checked == true) {
								var id = PK_STUDENT_ENROLLMENT[i].value;
								data['PK_STUDENT_ENROLLMENT[]'].push(id);
								data['PK_STUDENT_MASTER[]'].push(PK_STUDENT_MASTER[i].value);
							}
					}
					//console.log(data);
                    // document.getElementById('form1').setAttribute('action','<?php echo $http_path; ?>school/sap_gloabl_report_excel.php');
					// const form = document.getElementById("form1");
					// let formData = new FormData(form);
					jQuery.ajax({
						url: ajax_url,	
						type: "POST",		 
						data: data,		
						async: true,
						cache: false,
						success: function (data) {	
							//console.log(data.path);
							// document.form1.submit();

							  //if(val==1){		
							
								downloadDataUrlFromJavascript(data.filename,data.path);
							  //}

							  document.getElementById('loaders').style.display = 'none';
						}		
					});

					// var data = { 'PK_STUDENT_ENROLLMENT[]' : [], 'PK_STUDENT_MASTER[]' : []};
					// $('input[name="PK_STUDENT_ENROLLMENT"]:checked').each(function() {
					// 	data['PK_STUDENT_ENROLLMENT[]'].push($(this).val());
					// });
					// $.post("sap_gloabl_report_excel.php", data);

					// let response = fetch('<?php echo $http_path; ?>school/sap_gloabl_report_excel.php', {
					// 	method: 'POST',
					// 	headers: {"Content-Type": "application/x-www-form-urlencoded"},
					// 	body: new FormData(form1)
					// });
               
				
            }
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
		function show_only_selected() {
			//RUN DELETE ONLY IF ANY SINGLE IS SELECTED  
			if (jQuery(".delete_if_not_selected:checked").length > 0) {

					jQuery(".delete_if_not_selected:not(:checked)").parent().parent().remove();
			}
		}
		
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {

		$('#PK_STUDENT_GROUP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_GROUP?>',
			nonSelectedText: '<?=STUDENT_GROUP?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STUDENT_GROUP?> selected'
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
		$('#PK_COURSE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE?>',
			nonSelectedText: '<?=COURSE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE?> selected'
		});
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS ?>',
			nonSelectedText: '<?=STUDENT_STATUS ?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STUDENT_STATUS ?> selected'
		});
		//DIAM-1045
		$('#COURSE_PK_COURSE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?= COURSE ?>',
			nonSelectedText: '<?= COURSE ?>',
			numberDisplayed: 2,
			nSelectedText: '<?= COURSE ?> selected'
		});


		$('#COURSE_PK_COURSE_OFFERING').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?= COURSE_OFFERING ?>',
			nonSelectedText: '<?= COURSE_OFFERING ?>',
			numberDisplayed: 2,
			nSelectedText: '<?= COURSE_OFFERING ?> selected'
		});			

		$('#MIDPOINT_START_DATE').datepicker({
		autoclose: true,
		todayHighlight: true,
		orientation: "bottom auto"
		}).on('change', function(sdate) {

			if(document.getElementById('report_type').value==3){
				get_course_by_term_date(document.getElementById('report_type').value);
			}
		});

		$('#MIDPOINT_END_DATE').datepicker({
		autoclose: true,
		todayHighlight: true,
		orientation: "bottom auto"
		}).on('change', function(edate) {
			if(document.getElementById('report_type').value==3){
				get_course_by_term_date(document.getElementById('report_type').value);
			}
		});

		//DIAM-1045
	});

	//DIAM-1045
	function get_course_by_term_date(report_type){
			
			jQuery(document).ready(function($) {

			if ($('#MIDPOINT_START_DATE').val() === '' && $('#MIDPOINT_END_DATE').val() === '') return false;

			var PK_CAMPUS = '';
			PK_CAMPUS = $('#PK_CAMPUS').val(); 

			var data = 'report_type=' + report_type + '&PK_CAMPUS=' + PK_CAMPUS + '&COURSE_FIRST_TERM_START_DATE=' + $('#MIDPOINT_START_DATE').val() + '&COURSE_LAST_TERM_END_DATE=' + $('#MIDPOINT_END_DATE').val();
			var value = $.ajax({
				url: "ajax_custom_sap_report_get_course_by_term_date",
				type: "POST",
				data: data,
				async: false,
				cache: false,
				success: function(data) {

					data = data.replace('id="PK_COURSE"', 'id="COURSE_PK_COURSE"');
					document.getElementById('COURSE_COURSE_DIV').innerHTML = data;
					document.getElementById('COURSE_PK_COURSE').className = '';
					document.getElementById('COURSE_PK_COURSE').name = "COURSE_PK_COURSE[]"
					document.getElementById('COURSE_PK_COURSE').setAttribute('multiple', true);
					document.getElementById('COURSE_PK_COURSE').setAttribute("onchange", "get_course_offering_by_course()");

					$("#COURSE_PK_COURSE option[value='']").remove();

					$('#COURSE_PK_COURSE').multiselect({
						includeSelectAllOption: true,
						allSelectedText: 'All <?= COURSE ?>',
						nonSelectedText: '<?= COURSE ?>',
						numberDisplayed: 2,
						nSelectedText: '<?= COURSE ?> selected'
					});


				}
			});

		});
	} //function end

	function get_course_offering_by_course(){

		jQuery(document).ready(function($) {	

			var data = 'val=' + $('#COURSE_PK_COURSE').val() + '&multiple=1&PK_TERM_MASTER=' + $('#COURSE_PK_TERM').val();
			var url = "ajax_get_course_offering";
			var value = $.ajax({
				url: url,
				type: "POST",
				data: data,
				async: false,
				cache: false,
				success: function(data) {
				
					data = data.replace('id="PK_COURSE_OFFERING"', 'id="COURSE_PK_COURSE_OFFERING"');
					document.getElementById('COURSE_COURSE_OFFERING_DIV').innerHTML = data;
					document.getElementById('COURSE_PK_COURSE_OFFERING').className = '';
					document.getElementById('COURSE_PK_COURSE_OFFERING').setAttribute('multiple', true);
					document.getElementById('COURSE_PK_COURSE_OFFERING').name = "COURSE_PK_COURSE_OFFERING[]"
					$("#COURSE_PK_COURSE_OFFERING option[value='']").remove();
					$('#COURSE_PK_COURSE_OFFERING').multiselect({
						includeSelectAllOption: true,
						allSelectedText: 'All <?= COURSE_OFFERING ?>',
						nonSelectedText: '<?= COURSE_OFFERING ?>',
						numberDisplayed: 2,
						nSelectedText: '<?= COURSE_OFFERING ?> selected'
					});		

					var dd = document.getElementsByClassName('multiselect-native-select');
					for (var i = 0; i < dd.length; i++) {
						dd[i].style.width = '90%';
					}
				}
			}).responseText;
		});

	}//function end
	//DIAM-1045
	</script>
</body>

</html>
