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

$res = $db->Execute("SELECT ENABLE_ETHINK FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['ENABLE_ETHINK'] == 0) {
	header("location:../index");
	exit;
} 

if(!empty($_POST)){
	require_once("../global/ethink.php"); 
	
	//echo "<pre>";print_r($_POST);exit;
	$BATCH_ID = time().'_'.$_SESSION['PK_USER'];
	foreach($_POST['CHK_PK_STUDENT_MASTER'] as $CHK_PK_STUDENT_MASTER){
		$CHK_PK_STUDENT_MASTER1 = explode("_",$CHK_PK_STUDENT_MASTER);
	
		import_ethink_attendance($CHK_PK_STUDENT_MASTER1[0],$CHK_PK_STUDENT_MASTER1[1],$CHK_PK_STUDENT_MASTER1[2],$_SESSION['PK_ACCOUNT'],$BATCH_ID);
	}
	//exit;
	header("location:import_attendance_ethink_result?id=".$BATCH_ID);
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
		<?=MNU_IMPORT_ATTENDANCE ?> | <?=$title?>
	</title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
				<div class="row page-titles" style="padding-bottom: 10px;" >
                    <div class="col-md-2 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_IMPORT_ATTENDANCE ?>
						</h4>
                    </div>
					<div class="col-md-2 align-self-center text-right" >
						<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="doSearch()" >
							<? $res_type = $db->Execute("select PK_CAMPUS,OFFICIAL_CAMPUS_NAME from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by OFFICIAL_CAMPUS_NAME ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" ><?=$res_type->fields['OFFICIAL_CAMPUS_NAME']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div>
					<div class="col-md-2 align-self-center text-right" >
						<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control " onchange="doSearch();">
							<? $res_type = $db->Execute("select PK_TERM_MASTER,DATE_FORMAT(BEGIN_DATE,'%m/%d/%Y') AS BEGIN_DATE_1 from S_TERM_MASTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND LMS_ACTIVE = '1' order by BEGIN_DATE DESC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_TERM_MASTER'] ?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div> 
					<div class="col-md-2 align-self-center text-right" >
                       <select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING[]" multiple class="form-control " onchange="doSearch();" >
						   <? $res_type = $db->Execute("select PK_COURSE_OFFERING,COURSE_CODE,SESSION,SESSION_NO FROM S_COURSE, S_COURSE_OFFERING LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION WHERE S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND LMS_ACTIVE = '1' ");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_COURSE_OFFERING'] ?>"><?=$res_type->fields['COURSE_CODE'].' ('.substr($res_type->fields['SESSION'],0,1).'-'.$res_type->fields['SESSION_NO'].')' ?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div> 
					
					<div class="col-md-2 align-self-center text-right" >
						 <select id="PK_SESSION" name="PK_SESSION" class="form-control " onchange="doSearch();" style="margin-top: 10px;" >
							<option value=""><?=SESSION?></option>
						   <? $res_type = $db->Execute("select PK_SESSION,SESSION FROM M_SESSION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY DISPLAY_ORDER ");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_SESSION'] ?>"><?=$res_type->fields['SESSION'] ?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div> 
					
					<div class="col-md-2 align-self-center text-right" >
						<select id="INSTRUCTOR" name="INSTRUCTOR" class="form-control " onchange="doSearch();" style="margin-top: 10px;" >
							<option value=""><?=INSTRUCTOR?></option>
						   <? $res_type = $db->Execute("select INSTRUCTOR, CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME FROM S_COURSE, S_COURSE_OFFERING, S_EMPLOYEE_MASTER WHERE S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND LMS_ACTIVE = '1' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = INSTRUCTOR GROUP BY INSTRUCTOR ");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['INSTRUCTOR'] ?>"><?=$res_type->fields['NAME'] ?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div> 
					
					<div class="col-md-12 text-right" >
						<button type="button" onclick="validate_form()" id="SEND_BTN" style="display:none;float:right" class="btn waves-effect waves-light btn-info"  ><?=IMPORT?></button>
					</div> 
				</div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data">
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-12">
											<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" 
											url="grid_import_attendance_ethink" toolbar="#tb" pagination="true" pageSize = 25 >
												<thead>
													<tr>
														<th field="PK_STUDENT_MASTER" width="150px" hidden="true" sortable="true" ></th>
														<th field="PK_STUDENT_ENROLLMENT" width="150px" hidden="true" sortable="true" ></th>
														<th field="PK_STUDENT_STATUS_MASTER" width="150px" hidden="true" sortable="true" ></th>
														
														<th field="BEGIN_DATE" width="150px" align="left" sortable="true" ><?=TERM?></th>
														<th field="COURSE_CODE" width="180px" align="left" sortable="true" ><?=COURSE_CODE?></th>
														<th field="SESSION" width="100px" align="left" sortable="true" ><?=SESSION?></th>
														<th field="NAME" width="225px" align="left" sortable="true" ><?=NAME?></th>
														<th field="ATTENDANCE" width="110px" align="left" sortable="true" ><?=Attendance?></th>
														<th field="CAMPUS" width="150px" align="left" sortable="true" ><?=CAMPUS?></th>
														
														<th field="IMPORTED" width="100px" align="left" sortable="true" ><?=IMPORTED?></th>
														<th field="IMPORTED_DATE" width="130px" align="left" sortable="true" ><?=IMPORTED_DATE?></th>
														<th field="IMPORTED_BY" width="130px" align="left" sortable="true" ><?=IMPORTED_BY?></th>
														
														<th field="SELECT" width="20px" sortable="true" >
															<input type="checkbox" id="CHECK_ALL" onclick="select_all()" >
														</th>
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
				PK_CAMPUS			: $('#PK_CAMPUS').val(),
				PK_TERM_MASTER		: $('#PK_TERM_MASTER').val(),
				PK_COURSE_OFFERING	: $('#PK_COURSE_OFFERING').val(),
				PK_SESSION			: $('#PK_SESSION').val(),
				INSTRUCTOR			: $('#INSTRUCTOR').val()
				
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
			
			$('#PK_COURSE_OFFERING').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
				nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
				numberDisplayed: 1,
				nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
			});
		});
	</script>
</body>

</html>