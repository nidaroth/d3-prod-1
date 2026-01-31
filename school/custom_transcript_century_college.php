<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if (has_management_access() == 0) {
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	// echo "<pre>";
	// print_r($_POST);exit;
	$stud_id = "";
	foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT) {
		if($stud_id != '')
			$stud_id .= ',';
		$stud_id .= $_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT];
	}
	
	$zip = 0;
	if($_POST['FORMAT'] == 3)
		$zip = 1;
		
	// if($_POST['REPORT_TYPE'] == 1)
	// 	header("location:student_transcript_pdf_arlington_na_program?id=".$stud_id.'&uno=0&exclude_tc='.$_POST['EXCLUDE_TRANSFERS_COURSE'].'&inc_att='.$_POST['DISPLAY_ATTENDNACE'].'&zip='.$zip);
	/*else*/ if($_POST['REPORT_TYPE'] == 2)
		header("location:student_transcript_list_pdf_century?id=".$stud_id.'&exclude_tc='.$_POST['EXCLUDE_TRANSFERS_COURSE'].'&inc_att='.$_POST['DISPLAY_ATTENDNACE'].'&zip='.$zip);	
	// else if($_POST['REPORT_TYPE'] == 3)
	// 	header("location:student_transcript_list_numeric_grade_pdf?id=".$stud_id.'&exclude_tc='.$_POST['EXCLUDE_TRANSFERS_COURSE'].'&inc_att='.$_POST['DISPLAY_ATTENDNACE'].'&zip='.$zip);	
	// else if($_POST['REPORT_TYPE'] == 5)
	// 	header("location:student_transcript_pdf?id=".$stud_id.'&uno=1&exclude_tc='.$_POST['EXCLUDE_TRANSFERS_COURSE'].'&inc_att='.$_POST['DISPLAY_ATTENDNACE'].'&zip='.$zip);
	// else if($_POST['REPORT_TYPE'] == 4)
	// 	header("location:course_offering_grade_book_progress_report_pdf?id=".$stud_id.'&report_type='.$_POST['REPORT_TYPE_1'].'&exclude_tc='.$_POST['EXCLUDE_TRANSFERS_COURSE'].'&zip='.$zip);
	// else if($_POST['REPORT_TYPE'] == 6) //Ticket # 1551 
	// 	header("location:student_transcript_fa_pdf?id=".$stud_id.'&uno=0&exclude_tc='.$_POST['EXCLUDE_TRANSFERS_COURSE'].'&inc_att='.$_POST['DISPLAY_ATTENDNACE'].'&zip='.$zip);
	// else if($_POST['REPORT_TYPE'] == 7) //Ticket # 1551 
	// 	header("location:student_transcript_group_pdf?id=".$stud_id.'&exclude_tc='.$_POST['EXCLUDE_TRANSFERS_COURSE'].'&inc_att='.$_POST['DISPLAY_ATTENDNACE'].'&inc_instructor='.$_POST['INCLUDE_INSTRUCTOR'].'&zip='.$zip); //Ticket # 1603
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
										<div class="col-md-2 ">
											<b><?=REPORT_TYPE?></b>
											<select id="REPORT_TYPE" name="REPORT_TYPE"  class="form-control d-none" onchange="show_filters(this.value)" >
												<!-- <option value="4"><?=MNU_COURSE_OFFERING_GRADE_BOOK_TRANSCRIPT?></option> -->
												<!-- <option value="1"><?=STUDENT_TRANSCRIPT?></option> -->
												<!-- Below : Ticket # 1551 -->
												<!-- <option value="6"><?=STUDENT_TRANSCRIPT_FA_UNITS?></option> -->
												<!-- Below : Ticket # 1834 --><!-- Ticket # 1603 -->
												<!-- <option value="7"><?=STUDENT_TRANSCRIPT_GROUP?></option> -->
												<option value="2"><?=STUDENT_TRANSCRIPT_LIST?></option>
												<!--<option value="3"><?=STUDENT_TRANSCRIPT_LIST_NUMBER_GRADE?></option>
												<option value="5"><?=STUDENT_UNOFFICIAL_TRANSCRIPT?></option> -->
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
										
										<!-- <div class="col-md-2 ">
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
											<select id="DISPLAY_ATTENDNACE" name="DISPLAY_ATTENDNACE"  class="form-control" >
												<option value="1" ><?=DISPLAY_ATTENDNACE?></option>
												<option value="2" >Do Not Display Attendance</option>
											</select>
										</div>
										
										<div class="col-md-2 align-self-center" style="display:none" id="REPORT_TYPE_1_div" >
											<select id="REPORT_TYPE_1" name="REPORT_TYPE_1" class="form-control" >
												<option value="1" >Detailed Report</option>
												<option value="2" >Summary Report</option>
											</select>
										</div>
										
										<div class="col-md-2 align-self-center " >
											<div class="custom-control custom-checkbox mr-sm-12">
												<input type="checkbox" class="custom-control-input" id="EXCLUDE_TRANSFERS_COURSE" name="EXCLUDE_TRANSFERS_COURSE" value="1" <? if($EXCLUDE_TRANSFERS_COURSE == 1) echo "checked"; ?> >
												<label class="custom-control-label" for="EXCLUDE_TRANSFERS_COURSE" ><?=EXCLUDE_TRANSFERS_COURSE?></label>
											</div>
										</div>
										
										 
										<div class="col-md-2 align-self-center " id="INCLUDE_INSTRUCTOR_div" style="display:none" >
											<div class="custom-control custom-checkbox mr-sm-12">
												<input type="checkbox" class="custom-control-input" id="INCLUDE_INSTRUCTOR" name="INCLUDE_INSTRUCTOR" value="1" <? if($INCLUDE_INSTRUCTOR == 1) echo "checked"; ?> >
												<label class="custom-control-label" for="INCLUDE_INSTRUCTOR" ><?=INCLUDE_INSTRUCTOR?></label>
											</div>
										</div>
										 
										 -->
										<div class="col-md-1 align-self-center ">
											<button type="button" onclick="search()" class="btn waves-effect waves-light btn-info"><?=SEARCH?></button>
										</div>
										
										<div class="col-md-2 ">
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info" id="btn_1" style="display:none" ><?=PDF?></button>
											<!-- <button type="button" onclick="submit_form(3)" class="btn waves-effect waves-light btn-info" id="btn_2" style="display:none" ><?=ZIP?></button> -->
											
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									
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
	<script type="text/javascript">
		var form1 = new Validation('form1');
		jQuery(document).ready(function($) { 
			show_filters(2)
		});
		
		function submit_form(val){
			document.getElementById('FORMAT').value = val
			document.form1.submit();
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

				<?php 
					$NA_PROGRAMS = $db->Execute("SELECT GROUP_CONCAT(PK_CAMPUS_PROGRAM) AS PK_CAMPUS_PROGRAMS FROM M_CAMPUS_PROGRAM WHERE (CODE like 'NA' OR CODE like '%-NA' OR CODE like 'NA-%' OR CODE like '%-NA-%') AND PK_ACCOUNT = '".$_SESSION['PK_ACCOUNT']."' GROUP BY PK_ACCOUNT ")->fields['PK_CAMPUS_PROGRAMS']; 
					
					?>
				var NA_PROGRAMS = '<?php echo $NA_PROGRAMS?>';
				if($('#PK_CAMPUS_PROGRAM').val() != ''){
					NA_PROGRAMS = $('#PK_CAMPUS_PROGRAM').val();
				}
				var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+NA_PROGRAMS+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&show_check=1&show_count=1&group_by=sid&ENROLLMENT=1';
				
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
		}
		function show_filters(val){
			
			
			if(val == 1 || val == 2 || val == 3 || val == 5 || val == 6 || val == 7) { //Ticket # 1551 // Ticket # 1834
				document.getElementById('REPORT_TYPE_1_div').style.display 				= 'none';
				document.getElementById('DISPLAY_ATTENDNACE_div').style.display 		= 'block';
			} else {
				document.getElementById('REPORT_TYPE_1_div').style.display 				= 'block';
				document.getElementById('DISPLAY_ATTENDNACE_div').style.display 		= 'none';
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
		}
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
	});
	</script>
</body>

</html>