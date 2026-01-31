<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_ROLES'] != 3 ){  
	header("location:../index");
	exit;
}

$current_page1 = $_SERVER['PHP_SELF'];
$parts1 = explode('/', $current_page1);
$current_page = $parts1[count($parts1) - 1];
if($current_page != $_SESSION['PREVIOUS_PAGE'] || $_GET['clear'] == 1){
	$_SESSION['SORT_FIELD'] 	 = 'S_STUDENT_MASTER.LAST_NAME ASC, S_STUDENT_MASTER.FIRST_NAME ASC ';
	$_SESSION['SORT_ORDER'] 	 = '';
	$_SESSION['PAGE'] 			 = 1;
	$_SESSION['rows'] 			 = 25;
	$_SESSION['PREVIOUS_PAGE'] 	 = $current_page;
	
	$_SESSION['SRC_PK_CAMPUS'] 		 			= '';
	$_SESSION['SRC_PK_STUDENT_STATUS'] 		 	= '';
	$_SESSION['SRC_PK_CAMPUS_PROGRAM'] 		 	= '';
	$_SESSION['SRC_PK_TERM_MASTER'] 		 	= '';
	$_SESSION['SRC_PK_STUDENT_GROUP'] 		 	= '';
	$_SESSION['SRC_PK_COURSE'] 		 			= '';
	$_SESSION['SRC_PK_SESSION'] 		 		= '';
	$_SESSION['SRC_SEARCH'] 			 		= '';
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
		<?=STUDENT_PAGE_TITLE; ?> | <?=$title?>
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
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=STUDENT_PAGE_TITLE; ?>
						</h4>
                    </div>
					<div class="col-md-7 align-self-center text-right">
					
                        <div class="d-flex justify-content-end align-items-center">
							<a href="manage_student?t=<?=$_GET['t']?>&clear=1" class="btn btn-info d-none d-lg-block m-l-15"><i class="fas fa-newspaper"></i> <?=CLEAR_FILTER?></a>
                        </div>
                    </div>
				</div>
				
                <div class="row">
					<div class="col-md-12">
						<table>
							<tr>
								<td style="width:20%" >
									<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="doSearch()">
										<? $res_type = $db->Execute("select OFFICIAL_CAMPUS_NAME,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by OFFICIAL_CAMPUS_NAME ASC");
										while (!$res_type->EOF) { 
											$selected = "";
											if(!empty($_SESSION['SRC_PK_CAMPUS'])){
												foreach($_SESSION['SRC_PK_CAMPUS'] as $PK_CAMPUS1){
													if($res_type->fields['PK_CAMPUS'] == $PK_CAMPUS1)
														$selected = "selected";
												}
											} ?>
											<option value="<?=$res_type->fields['PK_CAMPUS']?>" <?=$selected?> ><?=$res_type->fields['OFFICIAL_CAMPUS_NAME']?></option>
										<?	$res_type->MoveNext();
										} ?>
									</select>
								</td>
								<td style="width:20%" >
									<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" onchange="doSearch()">
										<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
										while (!$res_type->EOF) { 
											$selected = "";
											if(!empty($_SESSION['SRC_PK_STUDENT_STATUS'])){
												foreach($_SESSION['SRC_PK_STUDENT_STATUS'] as $PK_STUDENT_STATUS1){
													if($res_type->fields['PK_STUDENT_STATUS'] == $PK_STUDENT_STATUS1)
														$selected = "selected";
												}
											} ?>
											<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" <?=$selected?> ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
										<?	$res_type->MoveNext();
										} ?>
									</select>
								</td>
								<td style="width:20%" >
									<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="doSearch()">
										<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
										while (!$res_type->EOF) { 
											$selected = "";
											if(!empty($_SESSION['SRC_PK_CAMPUS_PROGRAM'])){
												foreach($_SESSION['SRC_PK_CAMPUS_PROGRAM'] as $SRC_PK_CAMPUS_PROGRAM1){
													if($res_type->fields['PK_CAMPUS_PROGRAM'] == $SRC_PK_CAMPUS_PROGRAM1)
														$selected = "selected";
												}
											} ?>
											<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" <?=$selected?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
										<?	$res_type->MoveNext();
										} ?>
									</select>
								</td>
								<td style="width:20%" >
									<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" onchange="doSearch()" >
										<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
										while (!$res_type->EOF) {
											$selected = "";
											if(!empty($_SESSION['SRC_PK_TERM_MASTER'])){
												foreach($_SESSION['SRC_PK_TERM_MASTER'] as $PK_TERM_MASTER1){
													if($res_type->fields['PK_TERM_MASTER'] == $PK_TERM_MASTER1)
														$selected = "selected";
												}
											} ?>
											<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <?=$selected?> ><?=$res_type->fields['BEGIN_DATE_1']?></option>
										<?	$res_type->MoveNext();
										} ?>
									</select>
								</td>
								
								<td style="width:20%" >
									<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" onchange="doSearch()" >
										<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
										while (!$res_type->EOF) {
											$selected = "";
											if(!empty($_SESSION['SRC_PK_STUDENT_GROUP'])){
												foreach($_SESSION['SRC_PK_STUDENT_GROUP'] as $PK_STUDENT_GROUP1){
													if($res_type->fields['PK_STUDENT_GROUP'] == $PK_STUDENT_GROUP1)
														$selected = "selected";
												}
											} ?>
											<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" <?=$selected?> ><?=$res_type->fields['STUDENT_GROUP']?></option>
										<?	$res_type->MoveNext();
										} ?>
									</select>
								</td>
							</tr>
							<tr>
								<td >
									<select id="PK_COURSE" name="PK_COURSE[]" multiple class="form-control" onchange="doSearch()" >
										<? $res_type = $db->Execute("select S_COURSE.PK_COURSE, COURSE_CODE from S_COURSE, S_COURSE_OFFERING WHERE S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.ACTIVE = 1 AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND (INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') GROUP BY S_COURSE.PK_COURSE order by COURSE_CODE ASC");
										while (!$res_type->EOF) {
											$selected = "";
											if(!empty($_SESSION['SRC_PK_COURSE'])){
												foreach($_SESSION['SRC_PK_COURSE'] as $PK_COURSE1){
													if($res_type->fields['PK_COURSE'] == $PK_COURSE1)
														$selected = "selected";
												}
											} ?>
											<option value="<?=$res_type->fields['PK_COURSE']?>" <?=$selected?> ><?=$res_type->fields['COURSE_CODE']?></option>
										<?	$res_type->MoveNext();
										} ?>
									</select>
								</td>
								
								<td >
									<select id="PK_SESSION" name="PK_SESSION[]" multiple class="form-control" onchange="doSearch()" >
										<? $res_type = $db->Execute("select PK_SESSION,SESSION from M_SESSION WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by DISPLAY_ORDER ASC");
										while (!$res_type->EOF) {
											$selected = "";
											if(!empty($_SESSION['SRC_PK_SESSION'])){
												foreach($_SESSION['SRC_PK_SESSION'] as $PK_SESSION1){
													if($res_type->fields['PK_SESSION'] == $PK_SESSION1)
														$selected = "selected";
												}
											} ?>
											<option value="<?=$res_type->fields['PK_SESSION']?>" <?=$selected?> ><?=$res_type->fields['SESSION']?></option>
										<?	$res_type->MoveNext();
										} ?>
									</select>
								</td>
								
								<td >
									<input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome;" onkeypress="search(event)" value="<?=$_SESSION['SRC_SEARCH']?>"  >
								</td>
							</tr>
						</table>
					</div>
					
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<? if($_SESSION['SORT_FIELD'] != '')
											$sort = 'sortName = "'.$_SESSION['SORT_FIELD'].'" sortOrder="'.$_SESSION['SORT_ORDER'].'" ';
										else
											$sort = '' ?>
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" 
										url="grid_student?t=<?=$_GET['t']?>" toolbar="#tb" pagination="true"  pageNumber="<?=$_SESSION['PAGE']?>" pageSize="<?=$_SESSION['rows']?>" <?=$sort?> >
											<thead>
												<tr>
													<th field="PK_STUDENT_MASTER" width="150px" hidden="true" sortable="true" ></th>
													<th field="PK_STUDENT_ENROLLMENT" width="150px" hidden="true" sortable="true" ></th>
													<th field="PK_STUDENT_STATUS_MASTER" width="150px" hidden="true" sortable="true" ></th>
													
													<th field="NAME" width="225px" align="left" sortable="true" ><?=NAME?></th>
													<th field="STUDENT_STATUS" width="150px" align="left" sortable="true" ><?=STATUS?></th>
													<th field="PROGRAM" width="250px" align="left" sortable="true" ><?=PROGRAM?></th>
													<th field="FUNDING" width="200px" align="left" sortable="true" ><?=FUNDING?></th>
													<th field="BEGIN_DATE" width="150px" align="left" sortable="true" ><?=FIRST_TERM_DATE?></th>
													<th field="CAMPUS" width="200px" align="left" sortable="true" ><?=CAMPUS?></th>
													
												</tr>
											</thead>
										</table>
									</div>
								</div>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>

        <? require_once("footer.php"); ?>
		
    </div>
	<? require_once("js.php"); ?>
	
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script type="text/javascript" src="../backend_assets/dist/js/jquery.easyui.min.js"></script>
	<script src="../backend_assets/dist/js/jquery-ui.js"></script> 

	<script type="text/javascript">
	function doSearch(){
		
		jQuery(document).ready(function($) {
			if(document.getElementById('PK_STUDENT_STATUS')){
				var PK_STUDENT_STATUS = $('#PK_STUDENT_STATUS').val();
				if(PK_STUDENT_STATUS.length == 0)
					var PK_STUDENT_STATUS = '';
			} else
				var PK_STUDENT_STATUS = '';
				
			if(document.getElementById('PK_CAMPUS_PROGRAM')){
				var PK_CAMPUS_PROGRAM = $('#PK_CAMPUS_PROGRAM').val();
				if(PK_CAMPUS_PROGRAM.length == 0)
					var PK_CAMPUS_PROGRAM = '';
			} else
				var PK_CAMPUS_PROGRAM = '';

			if(document.getElementById('PK_TERM_MASTER')){
				var PK_TERM_MASTER = $('#PK_TERM_MASTER').val();
				if(PK_TERM_MASTER.length == 0)
					var PK_TERM_MASTER = '';
			} else
				var PK_TERM_MASTER = '';
				
			if(document.getElementById('PK_STUDENT_GROUP')){
				var PK_STUDENT_GROUP = $('#PK_STUDENT_GROUP').val();
				if(PK_STUDENT_GROUP.length == 0)
					var PK_STUDENT_GROUP = '';
			} else
				var PK_STUDENT_GROUP = '';
				
			if(document.getElementById('PK_COURSE')){
				var PK_COURSE = $('#PK_COURSE').val();
				if(PK_COURSE.length == 0)
					var PK_COURSE = '';
			} else
				var PK_COURSE = '';	
				
			if(document.getElementById('PK_SESSION')){
				var PK_SESSION = $('#PK_SESSION').val();
				if(PK_SESSION.length == 0)
					var PK_SESSION = '';
			} else
				var PK_SESSION = '';	
			
			if(document.getElementById('PK_CAMPUS')){
				var PK_CAMPUS = $('#PK_CAMPUS').val();
				if(PK_CAMPUS.length == 0)
					var PK_CAMPUS = '';
			} else
				var PK_CAMPUS = '';
				
			if(document.getElementById('PK_FUNDING')){
				var PK_FUNDING = $('#PK_FUNDING').val();
				if(PK_FUNDING.length == 0)
					var PK_FUNDING = '';
			} else
				var PK_FUNDING = '';	
			
			$('#tt').datagrid('load',{
				SEARCH  			: $('#SEARCH').val(),
				PK_STUDENT_STATUS	: PK_STUDENT_STATUS,
				PK_CAMPUS_PROGRAM	: PK_CAMPUS_PROGRAM,
				PK_CAMPUS			: PK_CAMPUS,
				PK_FUNDING			: PK_FUNDING,
				PK_TERM_MASTER		: PK_TERM_MASTER,
				PK_STUDENT_GROUP	: PK_STUDENT_GROUP,
				PK_COURSE			: PK_COURSE,
				PK_SESSION			: PK_SESSION
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
				onClickCell: function(rowIndex, field, value){
					$('#tt').datagrid('selectRow',rowIndex);
					if(field != 'ACTION' ){
						var selected_row = $('#tt').datagrid('getSelected');
						var tab = ''
						if($('#SHOW_MULTIPLE_ENROLLMENT').is(":checked") == true)
							tab = '&tab=otherTab'
						window.location.href='student?id='+selected_row.PK_STUDENT_MASTER+'&eid='+selected_row.PK_STUDENT_ENROLLMENT+'&t=<?=$_GET['t']?>'+tab;
					}
				}
			});
			
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
	
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#PK_CAMPUS_PROGRAM').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=PROGRAM?>',
				nonSelectedText: '<?=PROGRAM?>',
				numberDisplayed: 1,
				nSelectedText: '<?=PROGRAM?> selected'
			});
			
			$('#PK_STUDENT_STATUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=STUDENT_STATUS?>',
				nonSelectedText: '<?=STUDENT_STATUS?>',
				numberDisplayed: 1,
				nSelectedText: '<?=STUDENT_STATUS?> selected'
			});
			
			$('#PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=CAMPUS?>',
				nonSelectedText: '<?=CAMPUS?>',
				numberDisplayed: 1,
				nSelectedText: '<?=CAMPUS?> selected'
			});

			$('#PK_TERM_MASTER').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=FIRST_TERM_DATE?>',
				nonSelectedText: '<?=FIRST_TERM_DATE?>',
				numberDisplayed: 1,
				nSelectedText: '<?=FIRST_TERM_DATE?> selected'
			});
			
			$('#PK_STUDENT_GROUP').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=STUDENT_GROUP?>',
				nonSelectedText: '<?=STUDENT_GROUP?>',
				numberDisplayed: 1,
				nSelectedText: '<?=STUDENT_GROUP?> selected'
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
		});
	</script>
</body>

</html>