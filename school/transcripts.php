<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	$stud_id = "";
	foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT) {
		if($stud_id != '')
			$stud_id .= ',';
		$stud_id .= $_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT];
	}

	$attendance_type=($_POST['ATTENDANCE_TYPE_1']?$_POST['ATTENDANCE_TYPE_1']:'');

	$zip = 0;
	if($_POST['FORMAT'] == 3)
		$zip = 1;
		
	if($_POST['REPORT_TYPE'] == 1)
		header("location:student_transcript_pdf?id=".$stud_id.'&uno=0&exclude_tc='.$_POST['EXCLUDE_TRANSFERS_COURSE'].'&inc_att='.$_POST['DISPLAY_ATTENDNACE'].'&zip='.$zip);
	else if($_POST['REPORT_TYPE'] == 2)
		header("location:student_transcript_list_pdf?id=".$stud_id.'&exclude_tc='.$_POST['EXCLUDE_TRANSFERS_COURSE'].'&inc_att='.$_POST['DISPLAY_ATTENDNACE'].'&zip='.$zip.'&ATTENDANCE_TYPE_1='.$attendance_type);	
	else if($_POST['REPORT_TYPE'] == 3)
		header("location:student_transcript_list_numeric_grade_pdf?id=".$stud_id.'&exclude_tc='.$_POST['EXCLUDE_TRANSFERS_COURSE'].'&inc_att='.$_POST['DISPLAY_ATTENDNACE'].'&zip='.$zip);	
	else if($_POST['REPORT_TYPE'] == 5)
		header("location:student_transcript_pdf?id=".$stud_id.'&uno=1&exclude_tc='.$_POST['EXCLUDE_TRANSFERS_COURSE'].'&inc_att='.$_POST['DISPLAY_ATTENDNACE'].'&zip='.$zip);
	else if($_POST['REPORT_TYPE'] == 4)
		header("location:course_offering_grade_book_transcript_pdf?id=".$stud_id.'&report_type='.$_POST['REPORT_TYPE_1'].'&exclude_tc='.$_POST['EXCLUDE_TRANSFERS_COURSE'].'&zip='.$zip);
	else if($_POST['REPORT_TYPE'] == 6) //Ticket # 1551 
		header("location:student_transcript_fa_pdf?id=".$stud_id.'&uno=0&exclude_tc='.$_POST['EXCLUDE_TRANSFERS_COURSE'].'&inc_att='.$_POST['DISPLAY_ATTENDNACE'].'&zip='.$zip);
	else if($_POST['REPORT_TYPE'] == 7) //Ticket # 1551 
		header("location:student_transcript_group_pdf?id=".$stud_id.'&exclude_tc='.$_POST['EXCLUDE_TRANSFERS_COURSE'].'&inc_att='.$_POST['DISPLAY_ATTENDNACE'].'&inc_instructor='.$_POST['INCLUDE_INSTRUCTOR'].'&zip='.$zip); //Ticket # 1603
	else if($_POST['REPORT_TYPE'] == 8) //DIAM-2018
		header("location:program_grade_book_transcript_report_pdf?id=".$stud_id.'&report_type='.$_POST['DISPLAY_ATTENDNACE'].'&dt='.$_POST['AS_OF_DATE'].'&eid='.implode(",",$_POST['PK_STUDENT_ENROLLMENT']).'&zip='.$zip);	//DIAM-2018
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
	<title><?=MNU_TRANSCRIPTS?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		.dropdown-menu>li>a { white-space: nowrap; } /* Ticket # 1740  */
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
							<?=MNU_TRANSCRIPTS?>
						</h4>
                    </div>
                </div>
				
				<div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels" method="post" name="form1" id="form1" >
									<div class="row" style="padding-bottom:10px;" >
										<div class="col-md-3">
											<b><?=REPORT_TYPE?></b>
											<select id="REPORT_TYPE" name="REPORT_TYPE"  class="form-control" onchange="show_filters(this.value)" >
												<option value="4"><?=MNU_COURSE_OFFERING_GRADE_BOOK_TRANSCRIPT?></option>
												<option value="8"><?=MNU_PROGRAM_GRADE_BOOK_TRANSCRIPT?></option>
												<option value="1"><?=STUDENT_TRANSCRIPT?></option>
												<option value="6"><?=STUDENT_TRANSCRIPT_FA_UNITS?></option><!-- Ticket # 1551 -->
												<option value="7"><?=STUDENT_TRANSCRIPT_GROUP?></option><!-- Ticket # 1834 --><!-- Ticket # 1603 -->
												<option value="2"><?=STUDENT_TRANSCRIPT_LIST?></option>
												<option value="3"><?=STUDENT_TRANSCRIPT_LIST_NUMBER_GRADE?></option>
												<option value="5"><?=STUDENT_UNOFFICIAL_TRANSCRIPT?></option>
											</select>
										</div>
									</div>
									<div class="row" style="padding-bottom:10px;" >
										<!-- Ticket # 1603 -->
										<div class="col-md-2 " id="PK_CAMPUS_DIV"   >
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
												<? $res_type = $db->Execute("select CAMPUS_CODE, PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<!-- Ticket # 1603 -->
										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2 ">
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
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
									<div class="row">
										
										<div class="col-md-2 ">
											<select id="PK_COURSE" name="PK_COURSE[]" multiple class="form-control" onchange="get_course_offering(this.value);clear_search()" >
												<? /* Ticket # 1740  */
												$res_type = $db->Execute("select PK_COURSE, COURSE_CODE, TRANSCRIPT_CODE, COURSE_DESCRIPTION from S_COURSE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by COURSE_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_COURSE']?>" ><?=$res_type->fields['COURSE_CODE'].' - '.$res_type->fields['TRANSCRIPT_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} /* Ticket # 1740  */ ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_COURSE_OFFERING_DIV" >
											<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control" >
												<option value=""><?=COURSE_OFFERING_PAGE_TITLE?></option>
											</select>
										</div>
										
										<div class="col-md-2 align-self-center" id="DISPLAY_ATTENDNACE_div" >
											<select id="DISPLAY_ATTENDNACE" name="DISPLAY_ATTENDNACE" onchange="attendance_transcript_list(this.value);"  class="form-control" >
												<option value="1" ><?=DISPLAY_ATTENDNACE?></option>
												<option value="2" >Do Not Display Attendance</option>
											</select>
										</div>
										<div class="col-md-2 align-self-center" id="ATTENDANCE_type_div"  style="display:none;">
										<select id="ATTENDANCE_TYPE_1" name="ATTENDANCE_TYPE_1" class="form-control">
											<option value="1">Detailed</option>
											<option value="2">Summary</option>
										</select>
										</div>

										
										<div class="col-md-2 align-self-center" style="display:none" id="REPORT_TYPE_1_div" >
											<select id="REPORT_TYPE_1" name="REPORT_TYPE_1" class="form-control" >
												<option value="1" >Detailed Report</option>
												<option value="2" >Summary Report</option>
											</select>
										</div>
										<!--DIAM-2018 -->
										 <div class="col-md-2 align-self-center" id="AS_OF_DATE_DIV" style="display:none;" >											
											<?=AS_OF_DATE ?>
											<input type="text" class="form-control date required-entry" id="AS_OF_DATE" name="AS_OF_DATE" value="" >										
										</div> 
										<!--DIAM-2018 -->
										<div class="col-md-2 align-self-center " id="EXCLUDE_TRANSFERS_COURSE_div">
											<div class="custom-control custom-checkbox mr-sm-12">
												<input type="checkbox" class="custom-control-input" id="EXCLUDE_TRANSFERS_COURSE" name="EXCLUDE_TRANSFERS_COURSE" value="1" <? if($EXCLUDE_TRANSFERS_COURSE == 1) echo "checked"; ?> >
												<label class="custom-control-label" for="EXCLUDE_TRANSFERS_COURSE" ><?=EXCLUDE_TRANSFERS_COURSE?></label>
											</div>
										</div>
										
										<!-- Ticket # 1603 -->
										<div class="col-md-2 align-self-center " id="INCLUDE_INSTRUCTOR_div" style="display:none" >
											<div class="custom-control custom-checkbox mr-sm-12">
												<input type="checkbox" class="custom-control-input" id="INCLUDE_INSTRUCTOR" name="INCLUDE_INSTRUCTOR" value="1" <? if($INCLUDE_INSTRUCTOR == 1) echo "checked"; ?> >
												<label class="custom-control-label" for="INCLUDE_INSTRUCTOR" ><?=INCLUDE_INSTRUCTOR?></label>
											</div>
										</div>
										<!-- Ticket # 1603 -->
										
									</div>
									<br />
									<div class="row"> <!-- DIAM-1463   -->
											<!-- <div class="col-md-12 m-b-40"> <b>Term Start Range</b> </div> -->
											<?
											// $term_start_date = date('m/d/Y', strtotime("-3 months", strtotime(date('Y-m-d'))));
											// $term_end_date = date('m/d/Y', strtotime("+3 months", strtotime(date('Y-m-d'))));
											?>
											<div class="col-md-2 focused">
												 <?=START_DATE?> 
												<input type="text" name="START_DATE" id="START_DATE" class="form-control date" value="" >
												<span class="bar"></span>
											</div>
											<div class="col-md-2 focused">
												<?=END_DATE?> 
												<input type="text" name="END_DATE" id="END_DATE" class="form-control date" value="" >
												<span class="bar"></span>
											</div>
											<!-- DIAM-1463   -->
											
										<div class="col-md-1 align-self-center ">
											<button type="button" onclick="search()" class="btn waves-effect waves-light btn-info"><?=SEARCH?></button>
										</div>
										
										<div class="col-md-2 align-self-center">
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info" id="btn_1" style="display:none" ><?=PDF?></button>
											<button type="button" onclick="submit_form(3)" class="btn waves-effect waves-light btn-info" id="btn_2" style="display:none" ><?=ZIP?></button>
											
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>

									</div>	<!-- DIAM-1463   -->
									
									<br />
									<div id="student_div" >
										<? /*$_REQUEST['show_check'] 	= 1;
										$_REQUEST['show_count']		= 1;
										$_REQUEST['group_by']		= 'sid';
										require_once('ajax_search_student_for_reports.php');*/ ?>
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

	<!-- DIAM-1463 -->
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<!-- DIAM-1463 -->

	<script type="text/javascript">
		var form1 = new Validation('form1');
		jQuery(document).ready(function($) { 
			show_filters(4)
		});
		
		function submit_form(val){
			// DIAM-2239
			var report_type = jQuery('#REPORT_TYPE').val();
			if(report_type == 1 || report_type == 6 || report_type == 7 || report_type == 2 || report_type == 3 || report_type == 5)
			{
				confirm_select_enrollment_transcript(val);
			}
			else{
				document.getElementById('FORMAT').value = val
				document.form1.submit();
			}
			// End DIAM-2239
		}

		function attendance_transcript_list(val)
		{
			if(val == 1 &&  jQuery('#REPORT_TYPE').val() == 2){
				document.getElementById('ATTENDANCE_type_div').style.display = 'flex';
			}else{
				document.getElementById('ATTENDANCE_type_div').style.display = 'none';

			}

		}

		function get_course_offering(val){
			jQuery(document).ready(function($) { 
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
			});
		}
		function get_course_offering_session(){
		}
		
		function clear_search(){
			document.getElementById('student_div').innerHTML = '';
			show_btn()
		}
		
		function search(){
			jQuery(document).ready(function($) {
				//DIAM-2018
				var sid = 'sid';
				if($('#REPORT_TYPE').val()==8){
					var sid = '';
				}
				//DIAM-2018

				var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_COURSE='+$('#PK_COURSE').val()+'&PK_COURSE_OFFERING='+$('#PK_COURSE_OFFERING').val()+'&TREM_BEGIN_START_DATE='+$('#START_DATE').val()+'&TREM_BEGIN_END_DATE='+$('#END_DATE').val()+'&show_check=1&show_count=1&group_by='+sid+'&ENROLLMENT=1'; //DIAM-1463 //DIAM-2018
				
				/* Ticket # 1603 */
				flag = 1
				if(document.getElementById('REPORT_TYPE').value == 7) {
					data += '&PK_CAMPUS='+$('#PK_CAMPUS').val()
					if($('#PK_CAMPUS').val() == '') {
						flag = 0
						alert("Please Select Campus");
					} else
						flag = 1
				}
				/* Ticket # 1603 */
				
				if(flag == 1) { //Ticket # 1603
					var value = $.ajax({
						url: "ajax_search_student_for_reports",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							document.getElementById('student_div').innerHTML = data
							show_btn()
							try {get_count();} catch (error) { /* do nothing */}
						}		
					}).responseText;
				} //Ticket # 1603
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

			//DIAM-2018
			if(document.getElementById('REPORT_TYPE').value==8){				
				document.getElementById('btn_2').style.display = 'none';
			}
			//DIAM-2018
		}
		function show_filters(val){
			
			
			if(val == 1 || val == 2 || val == 3 || val == 5 || val == 6 || val == 7 || val == 8) { //Ticket # 1551 // Ticket # 1834 
				document.getElementById('REPORT_TYPE_1_div').style.display 				= 'none';
				document.getElementById('DISPLAY_ATTENDNACE_div').style.display 		= 'block';
			} else {
				document.getElementById('REPORT_TYPE_1_div').style.display 				= 'block';
				document.getElementById('DISPLAY_ATTENDNACE_div').style.display 		= 'none';
			}
			
			if(val == 2) { //Ticket # 1551 // Ticket # 1834
				document.getElementById('ATTENDANCE_type_div').style.display 		= 'block';
			} else {
				document.getElementById('ATTENDANCE_type_div').style.display 		= 'none';
			}

			/* Ticket # 1603 */
			if(val == 7){
				document.getElementById('INCLUDE_INSTRUCTOR_div').style.display 	= 'block';
				document.getElementById('PK_CAMPUS_DIV').style.display 				= 'block';
			} else {
				document.getElementById('INCLUDE_INSTRUCTOR_div').style.display 	= 'none';
				//document.getElementById('PK_CAMPUS_DIV').style.display 				= 'none';
			}
			/* Ticket # 1603 */
			//DIAM-2018
			if(val == 8) {
				//document.getElementById('REPORT_TYPE_1_div').style.display = 'none';
				document.getElementById('EXCLUDE_TRANSFERS_COURSE_div').style.display = 'none';				
				document.getElementById('AS_OF_DATE_DIV').style.display = 'block';
				document.getElementById('student_div').innerHTML='';
			}else{
				document.getElementById('EXCLUDE_TRANSFERS_COURSE_div').style.display = 'block';
				document.getElementById('AS_OF_DATE_DIV').style.display = 'none';
				document.getElementById('student_div').innerHTML='';
			}
			//DIAM-2018
		}

		// DIAM-2239
		function confirm_select_enrollment_transcript(val) {
			jQuery(document).ready(function($) {
				var str = "";
				if (val == 1 || val == 3) {
					var dd = document.getElementsByName('PK_STUDENT_ENROLLMENT[]');
					for (var i = 0; i < dd.length; i++) {
						if (dd[i].checked == true) {
							if (str != '')
							{
								str += ',';
							}
							rec = dd[i].value;
							str += jQuery('#S_PK_STUDENT_MASTER_'+rec).val();
						}
					}
					if (str == '') {
						alert('Please Select At Least One Enrollment');
					} else {

						var zip = 0;
						if(val == 3)
						{
							var zip = 1;
						}

						var report_types = jQuery('#REPORT_TYPE').val();
						if (report_types == 1) {
							var url = "student_transcript_pdf.php";
							var data = 'id=' + str + '&inc_att=' + jQuery('#DISPLAY_ATTENDNACE').val() + '&uno=0&exclude_tc=' + (jQuery('#EXCLUDE_TRANSFERS_COURSE').is(":checked")?1:0)+ '&zip=' + zip + '&json_check=1';
							confirm_download_data(url,data);
						} else if (report_types == 2) {
							var url = "student_transcript_list_pdf.php";
							var data = 'id=' + str + '&inc_att=' + jQuery('#DISPLAY_ATTENDNACE').val() + '&exclude_tc=' + (jQuery('#EXCLUDE_TRANSFERS_COURSE').is(":checked")?1:0)+'&ATTENDANCE_TYPE_1='+jQuery('#ATTENDANCE_TYPE_1').val()+ '&zip='+ zip +'&json_check=1';
							confirm_download_data(url,data);
						} else if (report_types == 3) {
							var url = "student_transcript_list_numeric_grade_pdf.php";
							var data = 'id=' + str + '&inc_att=' + jQuery('#DISPLAY_ATTENDNACE').val() + '&exclude_tc=' + (jQuery('#EXCLUDE_TRANSFERS_COURSE').is(":checked")?1:0)+ '&zip='+ zip +'&json_check=1';
							confirm_download_data(url,data);
						} else if (report_types == 5) {
							var url = "student_transcript_pdf.php";
							var data = 'id=' + str + '&uno=1&inc_att=' + jQuery('#DISPLAY_ATTENDNACE').val() + '&exclude_tc=' + (jQuery('#EXCLUDE_TRANSFERS_COURSE').is(":checked")?1:0)+ '&zip='+ zip +'&json_check=1';
							confirm_download_data(url,data);
						} else if (report_types == 6) {	
							var url = "student_transcript_fa_pdf.php";
							var data = 'id=' + str + '&inc_att=' + jQuery('#DISPLAY_ATTENDNACE').val() + '&uno=0&exclude_tc=' + (jQuery('#EXCLUDE_TRANSFERS_COURSE').is(":checked")?1:0)+'&zip='+ zip +'&json_check=1';
							confirm_download_data(url,data);
						} else if (report_types == 7) {
							var url = "student_transcript_group_pdf.php";
							var data = 'id=' + str + '&inc_att=' + jQuery('#DISPLAY_ATTENDNACE').val() + '&exclude_tc=' + (jQuery('#EXCLUDE_TRANSFERS_COURSE').is(":checked")?1:0) + '&inc_instructor=' + jQuery('#INCLUDE_INSTRUCTOR').val() + '&zip='+ zip + '&json_check=1';
							confirm_download_data(url,data);
						}
					}
				}
			});
		}

		function confirm_download_data(url,data) {
			jQuery(document).ready(function($) {
				
					var final_data = data;
					var value = $.ajax({
						url: url,
						type: "GET",
						data: final_data,
						async: false,
						cache: false,
						success: function(data) {
							if(data.error)
							{
								alert(data.error);
								return false;
							}
							else{
								const text = window.location.href;
								const word = '/school';
								const textArray = text.split(word);
								const result = textArray.shift();
								// console.log(data, data.file_name, result + '/school/' + data.path);
								downloadDataUrlFromJavascript_Main(data.file_name, result + '/school/' + data.path)
							}
							
						}
					}).responseText;
				
			});
		}

		function downloadDataUrlFromJavascript_Main(filename, dataUrl) {

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
		// End DIAM-2239
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_COURSE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_CODE?>',
			nonSelectedText: '<?=COURSE_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_CODE?> selected'
		});
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
		$('#PK_COURSE_OFFERING').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
			nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
		});
		
		/* Ticket # 1603 */
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		/* Ticket # 1603 */

		/* DIAM-1463 */
		$('#START_DATE').datepicker({
		autoclose: true,
		todayHighlight: true,
		orientation: "bottom auto"
		});

		$('#END_DATE').datepicker({
		autoclose: true,
		todayHighlight: true,
		orientation: "bottom auto"
		});
		/* DIAM-1463 */
		/* DIAM-2018 */		
		$('#AS_OF_DATE').datepicker({
		autoclose: true,
		todayHighlight: true,
		orientation: "bottom auto"
		});
		/* DIAM-2018 */

	});
	</script>
</body>

</html>
