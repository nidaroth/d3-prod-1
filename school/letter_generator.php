<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/letter_generator.php");
require_once("check_access.php");

if(check_access('REPORT_CUSTOM_REPORT') == 0 ){
	header("location:../index");
	exit;
}

require_once("generate_pdf.php");

function unlinkRecursive($dir, $deleteRootToo){
    if(!$dh = @opendir($dir)){
        return;
    }
    while (false !== ($obj = readdir($dh))){
        if($obj == '.' || $obj == '..'){
            continue;
        }
        if (!@unlink($dir . '/' . $obj)){
            unlinkRecursive($dir.'/'.$obj, true);
        }
    }
    closedir($dh);
    if ($deleteRootToo){
        @rmdir($dir);
    }
    return;
}
class FlxZipArchive extends ZipArchive {
	public function addDir($location, $name) {
		$this->addEmptyDir($name);
		$this->addDirDo($location, $name);
	} 
	private function addDirDo($location, $name) {
		$name .= '/';
		$location .= '/';
		$dir = opendir ($location);
		while ($file = readdir($dir)){
			if ($file == '.' || $file == '..') 
				continue;
			$do = (filetype( $location . $file) == 'dir') ? 'addDir' : 'addFile';
			$this->$do($location . $file, $name . $file);
		}
	}
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	$PK_PDF_TEMPLATE = $_POST['PK_PDF_TEMPLATE'];
	foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT){
		$res = $db->Execute("select PK_STUDENT_MASTER from S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
		$files[] = generate_pdf($PK_PDF_TEMPLATE,$res->fields['PK_STUDENT_MASTER'],$PK_STUDENT_ENROLLMENT,1);
	}
	
	//echo "<pre>";print_r($files);exit;
	
	$res = $db->Execute("select TEMPLATE_NAME from S_PDF_TEMPLATE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_PDF_TEMPLATE = '$PK_PDF_TEMPLATE' ");
	$TEMPLATE_NAME = $res->fields['TEMPLATE_NAME'];
	$TEMPLATE_NAME = str_replace("/","-",$TEMPLATE_NAME);
	$TEMPLATE_NAME = str_replace(":","",$TEMPLATE_NAME);
	$TEMPLATE_NAME = str_replace("?","",$TEMPLATE_NAME);
	$TEMPLATE_NAME = str_replace("*","",$TEMPLATE_NAME);
	$TEMPLATE_NAME = str_replace("<","",$TEMPLATE_NAME);
	$TEMPLATE_NAME = str_replace(">","",$TEMPLATE_NAME);
	$TEMPLATE_NAME = str_replace("|","",$TEMPLATE_NAME);
	
	$folder 		= "temp/zip_generated/".$TEMPLATE_NAME.'_'.$_SESSION['PK_ACCOUNT'];
	$zip_file_name  = $folder.'.zip';
	mkdir($folder);
	
	if($folder != '') {
		unlinkRecursive("$folder/",0);
		unlink($zip_file_name);
		@rmdir($folder);
	}
	
	$za = new FlxZipArchive;
	$res = $za->open($zip_file_name, ZipArchive::CREATE);

	if($res === TRUE) {
		foreach($files as $file) {
			$za->addFile($file, str_replace("temp/","",$file));
		}
		
		$za->close();
	
		unlinkRecursive("$folder/",0);
		@rmdir($folder);
		
		foreach($files as $file) {
			unlink($file);
		}
		header("location:".$zip_file_name);
	} else {
		echo 'Could not create a zip archive';
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
	<title><?=LETTER_GENERATOR_PAGE_TITLE?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		.dropdown-menu>li>a { white-space: nowrap; }
		.option_red > a > label{color:red !important}
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
							<?=LETTER_GENERATOR_PAGE_TITLE?>
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
												<select id="PK_PDF_TEMPLATE" name="PK_PDF_TEMPLATE" class="form-control required-entry" onchange="show_tag(this.value)" >
													<option selected></option>
													<? $res_type = $db->Execute("select PK_PDF_TEMPLATE, TEMPLATE_NAME from S_PDF_TEMPLATE WHERE ACTIVE = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY TEMPLATE_NAME ASC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_PDF_TEMPLATE'] ?>" ><?=$res_type->fields['TEMPLATE_NAME']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_PDF_TEMPLATE"><?=TEMPLATE_NAME?></label>
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
												<select id="PK_REPRESENTATIVE1" name="PK_REPRESENTATIVE1[]" multiple class="form-control" onchange="clear_search()" >
													<? $res_type = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.ACTIVE = 1 AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = 2 AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER order by CONCAT(FIRST_NAME,' ',LAST_NAME) ASC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" ><?=$res_type->fields['NAME']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											
											<div class="col-md-2 ">
												<select id="PK_CAMPUS1" name="PK_CAMPUS1[]" multiple class="form-control" onchange="clear_search()">
													<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											
											<div class="col-md-2 ">
												<select id="PK_TERM_MASTER1" name="PK_TERM_MASTER1[]" multiple class="form-control" onchange="clear_search()" >
													<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											
											<div class="col-md-2 ">
												<select id="PK_CAMPUS_PROGRAM1" name="PK_CAMPUS_PROGRAM1[]" multiple class="form-control" onchange="clear_search()" >
													<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											
											<div class="col-md-2 ">
												<select id="PK_STUDENT_STATUS1" name="PK_STUDENT_STATUS1[]" multiple class="form-control" onchange="clear_search()" >
													<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 1 order by STUDENT_STATUS ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											
										</div>
										<div class="row">
											<div class="col-md-2 ">
												<input id="STU_NAME_LEAD" name="STU_NAME_LEAD" value="" type="text" class="form-control" placeholder="<?=LEAD?>" onkeypress="search1(event)" >
											</div>
											
											<div class="col-md-2 align-self-center ">
												<button type="button" class="btn waves-effect waves-light btn-info" onclick="search()" ><?=SEARCH?></button>
												<button type="submit" class="btn waves-effect waves-light btn-info" id="btn_lead" style="display:none" ><?=PDF?></button>
											</div>
										</div>
									</div>
									
									<div id="student_fields_div" >
										<div class="row" style="padding-bottom:10px;" >
											<div class="col-md-2 ">
												<select id="PK_CAMPUS2" name="PK_CAMPUS1[]" multiple class="form-control" onchange="clear_search()">
													<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											
											<div class="col-md-2 ">
												<select id="PK_COURSE" name="PK_COURSE[]" multiple class="form-control" onchange="get_course_offering(this.value);clear_search()" >
													<? $res_type = $db->Execute("select PK_COURSE,COURSE_CODE,COURSE_DESCRIPTION from S_COURSE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by COURSE_CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_COURSE']?>" ><?=$res_type->fields['COURSE_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											
											<div class="col-md-2 " id="PK_COURSE_OFFERING_DIV" >
												<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING[]" multiple class="form-control" >
													<option value=""><?=COURSE_OFFERING_PAGE_TITLE?></option>
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
											
										</div>
										<div class="row">
											<div class="col-md-2 ">
												<input id="STU_NAME" name="STU_NAME" value="" type="text" class="form-control" placeholder="<?=STUDENT?>" onkeypress="search1(event)" >
											</div>
											
											<div class="col-md-2 ">
												<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" onchange="clear_search()" >
													
													<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_STATUS ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
											</div>
											<div class="col-md-2 align-self-center ">
												<button type="button" class="btn waves-effect waves-light btn-info" onclick="search()" ><?=SEARCH?></button>
												<button type="submit" class="btn waves-effect waves-light btn-info" id="btn_student" style="display:none" ><?=PDF?></button>
											</div>
										</div>
									</div>
									<br />
									
									<div id="student_div" >
                                        <? /* $_REQUEST['NO_LEAD'] = 1;
										$_REQUEST['show_check'] = 1;
										$_REQUEST['page'] 		= 'letter_gen';
										require_once('ajax_search_student_for_reports.php');*/ ?>
									</div>
									
									<div class="row">
										<div class="col-sm-6 form-group">
										</div>
										<div class="col-sm-6 form-group">
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
		clear_search()
	}
	function clear_search(){
		document.getElementById('student_div').innerHTML = ''
		show_btn()
	}
	
	function search(){
		jQuery(document).ready(function($) {
			if(document.getElementById('LEAD').checked == true) {
				var data  = 'STU_NAME='+$('#STU_NAME_LEAD').val()+'&PK_REPRESENTATIVE='+$('#PK_REPRESENTATIVE1').val()+'&PK_CAMPUS='+$('#PK_CAMPUS1').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER1').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM1').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS1').val()+'&LEAD=1&show_check=1&page=letter_gen';
			} else if(document.getElementById('STUDENT').checked == true){
				var data  = 'STU_NAME='+$('#STU_NAME').val()+'&PK_CAMPUS='+$('#PK_CAMPUS2').val()+'&PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_COURSE='+$('#PK_COURSE').val()+'&PK_COURSE_OFFERING='+$('#PK_COURSE_OFFERING').val()+'&NO_LEAD=1&show_check=1&page=letter_gen';
			}
			var value = $.ajax({
				url: "ajax_search_student_for_reports",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {
					document.getElementById('student_div').innerHTML = data
				}		
			}).responseText;
		});
	}
	function search1(e){
		if (e.keyCode == 13) {
			search();
			e.preventDefault();
			return false;
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
			if(document.getElementById('LEAD').checked == true){
				document.getElementById('btn_lead').style.display 	 = 'inline';
				document.getElementById('btn_student').style.display = 'inline';
			} else {
				document.getElementById('btn_lead').style.display 	 = 'inline';
				document.getElementById('btn_student').style.display = 'inline';
			}
		} else {
			document.getElementById('btn_lead').style.display 	 = 'none';
			document.getElementById('btn_student').style.display = 'none';
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
		$('#PK_REPRESENTATIVE1').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=ADMISSION_REP?>',
			nonSelectedText: '<?=ADMISSION_REP?>',
			numberDisplayed: 2,
			nSelectedText: '<?=ADMISSION_REP?> selected'
		});
		$('#PK_CAMPUS1').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		$('#PK_CAMPUS2').multiselect({
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
	});
	</script>
</body>

</html>