<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');

if($REGISTRAR_ACCESS == 0) {
	header("location:../index");
	exit;
} 

$res = $db->Execute("SELECT ENABLE_CANVAS FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['ENABLE_CANVAS'] == 0) {
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	require_once("../global/canvas.php"); 
	
	//echo "<pre>";print_r($_POST);exit;
	$BATCH_ID = time().'_'.$_SESSION['PK_USER'];
	if($_POST['CHK_PK_STUDENT_MASTER'] != ''){
		foreach($_POST['CHK_PK_STUDENT_MASTER'] as $CHK_PK_STUDENT_MASTER){
			$CHK_PK_STUDENT_MASTER1 = explode("_",$CHK_PK_STUDENT_MASTER);
		
			$imported_grade_resp = import_canvas_grade($CHK_PK_STUDENT_MASTER1[0],$CHK_PK_STUDENT_MASTER1[1],$CHK_PK_STUDENT_MASTER1[2],$_SESSION['PK_ACCOUNT'],$BATCH_ID);
		}
	}
	
	
	$sql = "SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS NAME, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.MIDDLE_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS REPRESENTATIVE , STUDENT_ID,  IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y')) AS BEGIN_DATE ,STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE, CONCAT(M_CAMPUS_PROGRAM.CODE,' - ',M_CAMPUS_PROGRAM.DESCRIPTION) as PROGRAM,  PK_COURSE_OFFERING, GRADE, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, PK_STUDENT_GRADE_CANVAS  
	FROM 
	S_STUDENT_GRADE_CANVAS, S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT 
	LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE 
	WHERE 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	S_STUDENT_GRADE_CANVAS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND BATCH_ID = '$BATCH_ID' ";

	$res_grade = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS NAME, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.MIDDLE_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS REPRESENTATIVE , STUDENT_ID,  IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y')) AS BEGIN_DATE ,STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE, CONCAT(M_CAMPUS_PROGRAM.CODE,' - ',M_CAMPUS_PROGRAM.DESCRIPTION) as PROGRAM,  PK_COURSE_OFFERING, GRADE, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, PK_STUDENT_GRADE_CANVAS  
	FROM 
	S_STUDENT_GRADE_CANVAS, S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT 
	LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE 
	WHERE 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
	S_STUDENT_GRADE_CANVAS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND BATCH_ID = '$BATCH_ID' ");
	while (!$res_grade->EOF) {
		$PK_STUDENT_GRADE_CANVAS 	= $res_grade->fields['PK_STUDENT_GRADE_CANVAS'];
		$PK_STUDENT_ENROLLMENT 		= $res_grade->fields['PK_STUDENT_ENROLLMENT'];
		$PK_COURSE_OFFERING 		= $res_grade->fields['PK_COURSE_OFFERING'];
		$GRADE 						= $res_grade->fields['GRADE'];
		
		$res_type = $db->Execute("select PK_STUDENT_COURSE,COURSE_CODE,SESSION,SESSION_NO FROM S_STUDENT_COURSE,S_COURSE, S_COURSE_OFFERING LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION WHERE S_STUDENT_COURSE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ");
		$PK_STUDENT_COURSE	= $res_type->fields['PK_STUDENT_COURSE'];
	
		$res_type = $db->Execute("select POSTED FROM S_STUDENT_GRADE_CANVAS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_GRADE_CANVAS = '$PK_STUDENT_GRADE_CANVAS' ");
		if($res_type->fields['POSTED'] == 0){
			$res_course_unit = $db->Execute("SELECT UNITS FROM S_COURSE, S_COURSE_OFFERING, S_STUDENT_COURSE WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
			
			$STUDENT_COURSE = array();
			$STUDENT_COURSE['COURSE_UNITS'] = $res_course_unit->fields['UNITS'];
			
			//echo "SELECT S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT,S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM,PK_GRADE_SCALE_MASTER,PK_COURSE_OFFERING,FINAL_GRADE FROM S_STUDENT_COURSE, S_STUDENT_ENROLLMENT, M_CAMPUS_PROGRAM WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM <br /><br />";
			$res = $db->Execute("SELECT S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT,S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM,PK_GRADE_SCALE_MASTER,PK_COURSE_OFFERING,FINAL_GRADE FROM S_STUDENT_COURSE, S_STUDENT_ENROLLMENT, M_CAMPUS_PROGRAM WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM = M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM "); 
			$PK_GRADE_SCALE_MASTER  = $res->fields['PK_GRADE_SCALE_MASTER'];
			
			$res_grade_data = $db->Execute("SELECT S_GRADE.* FROM S_GRADE_SCALE_DETAIL LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_GRADE_SCALE_DETAIL.PK_GRADE WHERE S_GRADE_SCALE_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_SCALE_MASTER = '$PK_GRADE_SCALE_MASTER' AND MAX_PERCENTAGE >= '$GRADE' AND MIN_PERCENTAGE <= '$GRADE' "); 
			
			//echo "SELECT S_GRADE.* FROM S_GRADE_SCALE_DETAIL LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_GRADE_SCALE_DETAIL.PK_GRADE WHERE S_GRADE_SCALE_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE_SCALE_MASTER = '$PK_GRADE_SCALE_MASTER' AND MAX_PERCENTAGE >= '$GRADE' AND MIN_PERCENTAGE <= '$GRADE' <br /><br />";
		
			$STUDENT_COURSE['FINAL_GRADE'] 						= $res_grade_data->fields['PK_GRADE'];
			$STUDENT_COURSE['FINAL_GRADE_GRADE'] 				= $res_grade_data->fields['GRADE'];
			$STUDENT_COURSE['FINAL_GRADE_NUMBER_GRADE'] 		= $res_grade_data->fields['NUMBER_GRADE'];
			$STUDENT_COURSE['FINAL_GRADE_CALCULATE_GPA'] 		= $res_grade_data->fields['CALCULATE_GPA'];
			$STUDENT_COURSE['FINAL_GRADE_UNITS_ATTEMPTED'] 		= $res_grade_data->fields['UNITS_ATTEMPTED'];
			$STUDENT_COURSE['FINAL_GRADE_UNITS_COMPLETED'] 		= $res_grade_data->fields['UNITS_COMPLETED'];
			$STUDENT_COURSE['FINAL_GRADE_UNITS_IN_PROGRESS'] 	= $res_grade_data->fields['UNITS_IN_PROGRESS'];
			$STUDENT_COURSE['FINAL_GRADE_WEIGHTED_GRADE_CALC'] 	= $res_grade_data->fields['WEIGHTED_GRADE_CALC'];
			$STUDENT_COURSE['FINAL_GRADE_RETAKE_UPDATE'] 		= $res_grade_data->fields['RETAKE_UPDATE'];
			$STUDENT_COURSE['NUMERIC_GRADE'] 					= $GRADE;
			db_perform('S_STUDENT_COURSE', $STUDENT_COURSE, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' ");
			
			//echo "<pre>";print_r($STUDENT_COURSE);
			
			$STUDENT_GRADE_ETHINK['POSTED'] = 1;
			db_perform('S_STUDENT_GRADE_CANVAS', $STUDENT_GRADE_ETHINK, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_GRADE_CANVAS = '$PK_STUDENT_GRADE_CANVAS' ");
		}
		
		$res_grade->MoveNext();
	}
	
	header("location:import_grade_canvas_result?id=".$BATCH_ID);
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
	<title>
		<?=MNU_CANVAS.' - '.MNU_IMPORT_GRADE ?> | <?=$title?>
	</title>
	<style>
		li > a > label{position: unset !important;}
		/* Ticket # 1149 - term */
		.dropdown-menu>li>a { white-space: nowrap; }
		.option_red > a > label{color:red !important}
		/* Ticket # 1149 - term */
	</style>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
				<div class="row page-titles" style="padding-bottom: 10px;" >
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_CANVAS.' - '.MNU_IMPORT_GRADE ?>
						</h4>
                    </div>
				</div>
				
				<div class="row" style="padding-bottom: 10px;"  >
					<div class="col-md-2 " style="max-width:14%;flex: 14%;" >
						<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="doSearch()" >
							<? $res_type = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" ><?=$res_type->fields['CAMPUS_CODE']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div>
					<div class="col-md-2 " style="max-width:14%;flex: 14%;" >
						<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control " onchange="doSearch();">
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
					<div class="col-md-2 " style="max-width:14%;flex: 14%;" >
                      <select id="PK_COURSE" name="PK_COURSE[]" multiple class="form-control " onchange="doSearch();" >
						   <? $res_type = $db->Execute("select PK_COURSE, COURSE_CODE, ACTIVE FROM S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, COURSE_CODE ASC ");
							while (!$res_type->EOF) { 
								$option_label = $res_type->fields['COURSE_CODE'];
								if($res_type->fields['ACTIVE'] == 0)
									$option_label .= " (Inactive)"; ?>
								<option value="<?=$res_type->fields['PK_COURSE'] ?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div> 
					
					<div class="col-md-2 " style="max-width:10%;flex: 10%;" >
						 <select id="PK_SESSION" name="PK_SESSION[]" multiple class="form-control " onchange="doSearch();" style="margin-top: 10px;" >
						   <? $res_type = $db->Execute("select PK_SESSION,SESSION FROM M_SESSION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY DISPLAY_ORDER ");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_SESSION'] ?>"><?=$res_type->fields['SESSION'] ?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div> 
					
					<div class="col-md-2 " style="max-width:14%;flex: 14%;" >
						<select id="INSTRUCTOR" name="INSTRUCTOR[]" multiple class="form-control " onchange="doSearch();" style="margin-top: 10px;" >
						   <? $res_type = $db->Execute("select INSTRUCTOR, CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME FROM S_COURSE, S_COURSE_OFFERING, S_EMPLOYEE_MASTER WHERE S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND LMS_ACTIVE = '1' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = INSTRUCTOR GROUP BY INSTRUCTOR ");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['INSTRUCTOR'] ?>"><?=$res_type->fields['NAME'] ?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div> 
					
					<div class="col-md-1 "  >
                       <select id="SENT" name="SENT" class="form-control " onchange="doSearch();" >
						  <option value=""><?=Sent?></option>
						  <option value="2">No</option>
						  <option value="1">Yes</option>
						</select>
					</div>  
					
					<div class="col-md-1 " style="max-width:14%;flex: 14%;" >
						<input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome;" onkeypress="search(event)">
					</div>  
				
					<div class="col-md-1 text-right" >
						<button type="button" onclick="validate_form()" id="SEND_BTN" style="display:none;float:right" class="btn waves-effect waves-light btn-info"  ><?=IMPORT?></button>
					</div> 
				</div>
				
				<form class="floating-labels " method="post" name="form1" id="form1" enctype="multipart/form-data">
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-12">
											<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" 
											url="grid_import_grade_canvas" toolbar="#tb" pagination="true" pageSize = 25 >
												<thead>
													<tr>
														<th field="PK_STUDENT_MASTER" width="150px" hidden="true" sortable="true" ></th>
														<th field="PK_STUDENT_ENROLLMENT" width="150px" hidden="true" sortable="true" ></th>
														<th field="PK_STUDENT_STATUS_MASTER" width="150px" hidden="true" sortable="true" ></th>
														
														<th field="SELECT" width="20px" sortable="true" >
															<input type="checkbox" id="CHECK_ALL" onclick="select_all()" >
														</th>
														
														<th field="CAMPUS_CODE" width="100px" align="left" sortable="true" ><?=CAMPUS?></th>
														<th field="BEGIN_DATE" width="90px" align="left" sortable="true" ><?=TERM?></th>
														<th field="COURSE_CODE" width="180px" align="left" sortable="true" ><?=COURSE?></th>
														<th field="SESSION" width="90px" align="left" sortable="true" ><?=SESSION?></th>
														<th field="LMS_CODE" width="100px" align="left" sortable="true" ><?=LMS_CODE?></th>
														<th field="INSTRUCTOR_NAME" width="150px" align="left" sortable="true" ><?=INSTRUCTOR?></th>
														
														<th field="NAME" width="200px" align="left" sortable="true" ><?=STUDENT?></th>
														<th field="FINAL_GRADE_GRADE" width="110px" align="left" sortable="true" ><?=FINAL_GRADE?></th>
														<th field="IMPORTED" width="100px" align="left" sortable="true" ><?=IMPORTED?></th>
														<th field="IMPORTED_DATE" width="130px" align="left" sortable="true" ><?=IMPORTED_DATE?></th>
														<th field="IMPORTED_BY" width="130px" align="left" sortable="true" ><?=IMPORTED_BY?></th>
														
														
													</tr>
												</thead>
											</table>
										</div>
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
	
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script type="text/javascript" src="../backend_assets/dist/js/jquery.easyui.min.js"></script>
	<script src="../backend_assets/dist/js/jquery-ui.js"></script> 
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});
	
	function doSearch(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid('load',{
				PK_CAMPUS  		: $('#PK_CAMPUS').val(),
				PK_TERM_MASTER  : $('#PK_TERM_MASTER').val(),
				PK_COURSE  		: $('#PK_COURSE').val(),
				PK_SESSION  	: $('#PK_SESSION').val(),
				INSTRUCTOR  	: $('#INSTRUCTOR').val(),
				SENT  			: $('#SENT').val(),
				MESSAGE_TYPE  	: $('#MESSAGE_TYPE').val(),
				SEARCH  		: $('#SEARCH').val(),
			});
		});	
	}
	function search(e){
		if (e.keyCode == 13) {
			doSearch();
		}
	}
	$(function(){
		jQuery(document).ready(function($) {

			$('#tt').datagrid({
				view: $.extend(true,{},$.fn.datagrid.defaults.view,{
					onAfterRender: function(target){
						$.fn.datagrid.defaults.view.onAfterRender.call(this,target);
						$('.datagrid-header-inner').width('100%') 
						$('.datagrid-btable').width('100%') 
						$('.datagrid-body').css({'overflow-y': 'hidden'});
					}
				})
			});

		});
	});
	jQuery(document).ready(function($) {
		$(window).resize(function() {
			$('#tt').datagrid('resize');
			$('#tb').panel('resize');
		}) 
	});
	
	function select_all(){
		
		var str = '';
		if(document.getElementById('CHECK_ALL').checked == true) {
			str = true;
		} else {
			str = false;
		}
		
		var CHK_PK_STUDENT_MASTER = document.getElementsByName('CHK_PK_STUDENT_MASTER[]')
		for(var i = 0 ; i < CHK_PK_STUDENT_MASTER.length ; i++){
			CHK_PK_STUDENT_MASTER[i].checked = str
		}
		
		show_btn()
	}
	
	function show_btn(){
		var flag = 0;
		var CHK_PK_STUDENT_MASTER = document.getElementsByName('CHK_PK_STUDENT_MASTER[]')
		for(var i = 0 ; i < CHK_PK_STUDENT_MASTER.length ; i++){
			if(CHK_PK_STUDENT_MASTER[i].checked == true) {
				flag = 1;
				break;
			}
		}
		
		if(flag == 1) {
			document.getElementById('SEND_BTN').style.display = 'block';
		} else {
			document.getElementById('SEND_BTN').style.display = 'none';
		}
	}
	
	function validate_form(){
		var flag = 0;
		var CHK_PK_STUDENT_MASTER = document.getElementsByName('CHK_PK_STUDENT_MASTER[]')
		for(var i = 0 ; i < CHK_PK_STUDENT_MASTER.length ; i++){
			if(CHK_PK_STUDENT_MASTER[i].checked == true) {
				flag = 1;
				break;
			}
		}
		
		if(flag == 1)
			document.form1.submit()
		else
			alert('Please Select At Least One Record');
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
				numberDisplayed: 1,
				nSelectedText: '<?=CAMPUS?> selected'
			});
			
			$('#PK_TERM_MASTER').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=TERM?>',
				nonSelectedText: '<?=TERM?>',
				numberDisplayed: 1,
				nSelectedText: '<?=TERM?> selected'
			});
			
			$('#PK_COURSE').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=COURSE?>',
				nonSelectedText: '<?=COURSE?>',
				numberDisplayed: 1,
				nSelectedText: '<?=COURSE?> selected'
			});
			
			$('#PK_SESSION').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=SESSION?>',
				nonSelectedText: '<?=SESSION?>',
				numberDisplayed: 1,
				nSelectedText: '<?=SESSION?> selected'
			});
			
			$('#INSTRUCTOR').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=INSTRUCTOR?>',
				nonSelectedText: '<?=INSTRUCTOR?>',
				numberDisplayed: 1,
				nSelectedText: '<?=INSTRUCTOR?> selected'
			});
		});
	</script>
</body>

</html>