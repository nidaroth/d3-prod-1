<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/bulk_text.php");
require_once("check_access.php");
require_once("../global/texting.php");
require_once('replace_student_tags.php'); //Ticket # 1429 

if(check_access('MANAGEMENT_BULK_UPDATE') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res_template = $db->Execute("SELECT CONTENT FROM S_TEXT_TEMPLATE WHERE PK_TEXT_TEMPLATE = '$_POST[PK_TEXT_TEMPLATE]' ");
	$CONTENT = $res_template->fields['CONTENT'];
		
	foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT){
		$res = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, LAST_NAME,FIRST_NAME from S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER ");
		$PK_STUDENT_MASTER = $res->fields['PK_STUDENT_MASTER'];
		
		$res_phone = $db->Execute("SELECT CELL_PHONE FROM S_STUDENT_CONTACT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' AND CELL_PHONE != '' ");
		if($res_phone->RecordCount() > 0){
			$TEXT_CONTENT = str_ireplace("{First Name}",$res->fields['FIRST_NAME'],$CONTENT);
			$TEXT_CONTENT = str_ireplace("{Last Name}",$res->fields['LAST_NAME'],$TEXT_CONTENT);
			$TEXT_CONTENT = str_ireplace("{Student Name}",$res->fields['NAME'],$TEXT_CONTENT);
			
			$TEXT_CONTENT = replace_mail_content($TEXT_CONTENT, $PK_STUDENT_ENROLLMENT, $_SESSION['PK_ACCOUNT']); //Ticket # 1429
		
			$text_sent = send_text($res_phone->fields['CELL_PHONE'],$_SESSION['PK_ACCOUNT'],$TEXT_CONTENT,$_POST['PK_TEXT_TEMPLATE'],'');
			
			if($text_sent == 1) {
				$text_data['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
				$text_data['PK_EMPLOYEE_MASTER']	= $_SESSION['PK_USER'];
				$text_data['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
				$text_data['PK_STUDENT_ENROLLMENT'] = $PK_STUDENT_ENROLLMENT;
				$text_data['TEXT_CONTENT'] 			= $TEXT_CONTENT;
				$text_data['TO_PHONE'] 				= $res_phone->fields['CELL_PHONE'];
				text_log($text_data);
			}
		}
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
	<title><?=MNU_BULK_TEXT ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		/* Ticket # 1149 - term */
		.dropdown-menu>li>a { white-space: nowrap; }
		.option_red > a > label{color:red !important}
		/* Ticket # 1149 - term
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
							<?=MNU_BULK_TEXT ?>
						</h4>
                    </div>
                </div>
				
                 <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row">
                                        <div class="col-md-3">
											<div class="form-group m-b-40">
												<select id="PK_TEXT_TEMPLATE" name="PK_TEXT_TEMPLATE" class="form-control required-entry" onchange="show_tag(this.value)" >
													<option selected></option>
													<? $res_type = $db->Execute("select PK_TEXT_TEMPLATE, TEMPLATE_NAME from S_TEXT_TEMPLATE WHERE ACTIVE = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY TEMPLATE_NAME ASC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_TEXT_TEMPLATE'] ?>" ><?=$res_type->fields['TEMPLATE_NAME']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_TEXT_TEMPLATE"><?=TEMPLATE_NAME?></label>
											</div>
										</div>
										<div class="col-md-3">
											<div class="row form-group">
												<div class="custom-control custom-radio col-md-4">
													<input type="radio" id="LEAD" name="STUDENT_TYPE" value="1" class="custom-control-input" onclick="change_view()" >
													<label class="custom-control-label" for="LEAD"><?=LEAD?></label>
												</div>
												<div class="custom-control custom-radio col-md-3">
													<input type="radio" id="STUDENT" name="STUDENT_TYPE" value="2" class="custom-control-input" checked onclick="change_view()" >
													<label class="custom-control-label" for="STUDENT"><?=STUDENT?></label>
												</div>
											</div>
										</div>
									</div>
									
									<div id="lead_fields_div" style="display:none" >
										<div class="row" style="padding-bottom:10px;" >
											<div class="col-md-2 ">
												<select id="PK_CAMPUS1" name="PK_CAMPUS1[]" multiple class="form-control" onchange="search()">
													<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											
											<div class="col-md-2 ">
												<select id="PK_TERM_MASTER1" name="PK_TERM_MASTER1[]" multiple class="form-control" onchange="search()" >
													<? /* Ticket #1149 - term */
													$res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
													while (!$res_type->EOF) { 
														$str = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'].' - '.$res_type->fields['TERM_DESCRIPTION'];
														if($res_type->fields['ACTIVE'] == 0)
															$str .= ' (Inactive)'; ?>
														<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>  ><?=$str ?></option>
													<?	$res_type->MoveNext();
													} /* Ticket #1149 - term */ ?>
												</select>
											</div>
											
											<div class="col-md-2 ">
												<select id="PK_LEAD_SOURCE" name="PK_LEAD_SOURCE[]" multiple class="form-control" onchange="search()" >
													<? $res_type = $db->Execute("select PK_LEAD_SOURCE,LEAD_SOURCE,DESCRIPTION from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by LEAD_SOURCE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_LEAD_SOURCE']?>" ><?=$res_type->fields['LEAD_SOURCE']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
									
											<div class="col-md-2 ">
												<select id="PK_CAMPUS_PROGRAM1" name="PK_CAMPUS_PROGRAM1[]" multiple class="form-control" onchange="search()" >
													<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											
											<div class="col-md-2 ">
												<select id="PK_STUDENT_STATUS1" name="PK_STUDENT_STATUS1[]" multiple class="form-control" onchange="search()" >
													<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 1 order by STUDENT_STATUS ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											<div class="col-md-2 align-self-center ">
												<button type="submit" class="btn waves-effect waves-light btn-info" id="btn" ><?=SEND ?></button>
											</div>
											
										</div>
										
										<div class="row" style="padding-bottom:10px;" >
											<div class="col-md-2 ">
												<?=LEAD_ENTRY_FROM_DATE ?>
												<input type="text" class="form-control date" id="LEAD_ENTRY_FROM_DATE" name="LEAD_ENTRY_FROM_DATE" value="" onchange="search();" >
											</div>
											
											<div class="col-md-2 ">
												<?=LEAD_ENTRY_TO_DATE ?>
												<input type="text" class="form-control date" id="LEAD_ENTRY_TO_DATE" name="LEAD_ENTRY_TO_DATE" value="" onchange="search();">
											</div>
										</div>
									</div>
									
									<div id="student_fields_div" >
										<div class="row" style="padding-bottom:10px;" >
											<div class="col-md-2 ">
												<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="search()">
													<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											
											<div class="col-md-2 ">
												<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" onchange="search()" >
													<? /* Ticket #1149 - term */
													$res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
													while (!$res_type->EOF) { 
														$str = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'].' - '.$res_type->fields['TERM_DESCRIPTION'];
														if($res_type->fields['ACTIVE'] == 0)
															$str .= ' (Inactive)'; ?>
														<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>  ><?=$str ?></option>
													<?	$res_type->MoveNext();
													} /* Ticket #1149 - term */ ?>
												</select>
											</div>
											
											<div class="col-md-2 ">
												<select id="PK_FUNDING" name="PK_FUNDING[]" multiple class="form-control" onchange="search()" >
													<? $res_type = $db->Execute("select * from M_FUNDING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by FUNDING ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_FUNDING']?>" ><?=$res_type->fields['FUNDING'].' - '.$res_type->fields['DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											
											<div class="col-md-2 ">
												<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="search()" >
													
													<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											
											<div class="col-md-2 ">
												<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" onchange="search()" >
													
													<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											
											<div class="col-md-2 ">
												<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" onchange="search()" >
													<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											
										</div>
										<div class="row">
											<div class="col-md-2 ">
												<select id="PK_PLACEMENT_STATUS" name="PK_PLACEMENT_STATUS[]" multiple class="form-control" onchange="search()" >
													<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS,PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_PLACEMENT_STATUS']?>" ><?=$res_type->fields['PLACEMENT_STATUS'] ?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											
											<div class="col-md-2 align-self-center ">
												<button type="submit" class="btn waves-effect waves-light btn-info" id="btn" ><?=SEND ?></button>
											</div>
										</div>
									</div>
									<br />
									
									<div id="student_div" >
                                        <? $_REQUEST['NO_LEAD'] = 1;
										$_REQUEST['show_check'] = 1;
										require_once('ajax_search_student_for_reports.php'); ?>
									</div>
									
									<div class="row">
										<div class="col-sm-6 form-group">
										</div>
										<div class="col-sm-6 form-group">
											<button type="submit" class="btn waves-effect waves-light btn-info" id="btn" style="display:none" ><?=PDF?></button>
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
		
		search();
	});
	
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
	var form1 = new Validation('form1');
	function change_view(){
		if(document.getElementById('LEAD').checked == true) {
			document.getElementById('lead_fields_div').style.display 		= 'block';
			document.getElementById('student_fields_div').style.display  	= 'none';
		} else if(document.getElementById('STUDENT').checked == true){
			document.getElementById('lead_fields_div').style.display 		= 'none';
			document.getElementById('student_fields_div').style.display  	= 'block';
		}
		search()
	}
	function search(){
		jQuery(document).ready(function($) {
			if(document.getElementById('LEAD').checked == true) {
				var data  = 'PK_CAMPUS='+$('#PK_CAMPUS1').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER1').val()+'&PK_LEAD_SOURCE='+$('#PK_LEAD_SOURCE').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM1').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS1').val()+'&LEAD_ENTRY_FROM_DATE='+$('#LEAD_ENTRY_FROM_DATE').val()+'&LEAD_ENTRY_TO_DATE='+$('#LEAD_ENTRY_TO_DATE').val()+'&LEAD=1&bulk_text=11&bulk_text=1&show_check=1';
			} else if(document.getElementById('STUDENT').checked == true){
				var data  = '&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_FUNDING='+$('#PK_FUNDING').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_PLACEMENT_STATUS='+$('#PK_PLACEMENT_STATUS').val()+'&NO_LEAD=1&bulk_text=1&show_check=1';
			}
			var value = $.ajax({
				url: "ajax_search_student_for_reports",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {
					document.getElementById('student_div').innerHTML = data
					get_count()
				}		
			}).responseText;
		});
	}
	function search1(e){
		if (e.keyCode == 13) {
			search();
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
					
					document.getElementById('PK_COURSE_OFFERING').setAttribute("onchange", "search()");
					
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
		$('#PK_FUNDING').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FUNDING?>',
			nonSelectedText: '<?=FUNDING?>',
			numberDisplayed: 2,
			nSelectedText: '<?=FUNDING?> selected'
		});

		$('#PK_CAMPUS1').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		$('#PK_TERM_MASTER1').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FIRST_TERM?>',
			nonSelectedText: '<?=FIRST_TERM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=FIRST_TERM?> selected'
		});
		$('#PK_CAMPUS_PROGRAM1').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROGRAM?>',
			nonSelectedText: '<?=PROGRAM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});
		$('#PK_STUDENT_STATUS1').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STATUS?>',
			nonSelectedText: '<?=STATUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STATUS?> selected'
		});
		$('#PK_LEAD_SOURCE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=LEAD_SOURCE?>',
			nonSelectedText: '<?=LEAD_SOURCE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=LEAD_SOURCE?> selected'
		});
		$('#PK_PLACEMENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STATUS?>',
			nonSelectedText: '<?=PLACEMENT_STATUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=PLACEMENT_STATUS?> selected'
		});
		
	});
	</script>
</body>

</html>