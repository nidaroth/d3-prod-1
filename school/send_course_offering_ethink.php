<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
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
	$RAND_ID = time().'_'.$_SESSION['PK_USER'];
	foreach($_POST['PK_COURSE_OFFERING'] as $PK_COURSE_OFFERING){
		create_course_offering($PK_COURSE_OFFERING,$_SESSION['PK_ACCOUNT'],$RAND_ID);
	}
	header("location:send_course_offering_ethink_result?id=".$RAND_ID);
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
	<title><?=MNU_MOODLE.' - '.MNU_SEND_COURSE_OFFERING ?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
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
                 <div class="row page-titles" style="padding-bottom: 5px;" >
                    <div class="col-md-12 align-self-center" >
                        <h4 class="text-themecolor"><?=MNU_MOODLE.' - '.MNU_SEND_COURSE_OFFERING?> </h4>
                    </div>
				</div>
				
				 <div class="row" style="padding-bottom: 10px;"  >
					<div class="col-md-2 " style="max-width:14%;flex: 14%;" >
						<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="doSearch()" >
							<? $res_type = $db->Execute("select PK_CAMPUS, CAMPUS_CODE, ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CAMPUS_CODE ASC");
							while (!$res_type->EOF) { 
								$str = $res_type->fields['CAMPUS_CODE'];
								if($res_type->fields['ACTIVE'] == 0)
									$str .= ' (Inactive)'; ?>
								<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>  ><?=$str ?></option>
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
					
					<div class="col-md-1" style="max-width:10%;flex: 10%;" >
						<select id="PK_SESSION" name="PK_SESSION[]" multiple class="form-control" onchange="doSearch();" >
							<? /* Ticket #1695  */
							$res_type = $db->Execute("select PK_SESSION, SESSION, ACTIVE from M_SESSION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, DISPLAY_ORDER ASC");
							while (!$res_type->EOF) { 
								$option_label = substr($res_type->fields['SESSION'],0,1).'-'.$res_type->fields['SESSION'];
								if($res_type->fields['ACTIVE'] == 0)
									$option_label .= " (Inactive)"; ?>
								<option value="<?=$res_type->fields['PK_SESSION'] ?>" <? if($_SESSION['SRC_PK_SESSION'] == $res_type->fields['PK_SESSION']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
							<?	$res_type->MoveNext();
							} /* Ticket #1695  */ ?>
						</select>
					</div>
					
					<div class="col-md-2 " style="max-width:14%;flex: 14%;" >
						<select id="INSTRUCTOR" name="INSTRUCTOR[]" multiple class="form-control" onchange="doSearch()" >
							<? /* Ticket #1695  */
							$res_type = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, S_EMPLOYEE_MASTER.ACTIVE from S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_FACULTY = 1 order by S_EMPLOYEE_MASTER.ACTIVE = 1 DESC, CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");
							while (!$res_type->EOF) { 
								$option_label = $res_type->fields['NAME'];
								if($res_type->fields['ACTIVE'] == 0)
									$option_label .= " (Inactive)"; ?>
								<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER'] ?>" <? if($_SESSION['SRC_INSTRUCTOR'] == $res_type->fields['PK_EMPLOYEE_MASTER']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
							<?	$res_type->MoveNext();
							} /* Ticket #1695  */ ?>
						</select>
					</div> 
					
					<div class="col-md-1 "  >
                       <select id="SENT" name="SENT" class="form-control " onchange="doSearch();" >
						  <option value=""><?=Sent?></option>
						  <option value="2">No</option>
						  <option value="1">Yes</option>
						</select>
					</div>  
					
					<div class="col-md-1 " style="max-width:11%;flex: 11%;" >
                       <select id="MESSAGE_TYPE" name="MESSAGE_TYPE" class="form-control " onchange="doSearch();" >
						  <option value="">Message Type</option>
						  <option value="2">Error</option>
						  <option value="1">Success</option>
						</select>
					</div>  
					
					<div class="col-md-1 " style="max-width:14%;flex: 14%;" >
						<input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome;" onkeypress="search(event)">
					</div>  
				</div>  
				
				 <div class="row" style="padding-bottom: 10px;"  >
					<div class="col-md-12 align-self-center text-right" >					   
					   <button type="button" onclick="window.location.href='send_course_offering_ethink_result_excel'" name="btn" class="btn waves-effect waves-light btn-info"  ><?=EXPORT_TO_EXCEL?></button>
						<button type="button" onclick="window.location.href='send_course_offering_ethink_result_pdf'" name="btn" class="btn waves-effect waves-light btn-info"  ><?=EXPORT_TO_PDF?></button>
						<button type="button" onclick="validate_form()" id="SEND_BTN" class="btn waves-effect waves-light btn-info"  ><?=SEND?></button>
                    </div>
                </div>
				<form class="floating-labels " method="post" name="form1" id="form1" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-12">
											<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_send_course_offering_ethink"
											toolbar="#tb" pagination="true" pageSize = 25 >
												<thead>
													<tr>
														<th field="ACTION" width="40px" align="left" sortable="false" >
															<input type="checkbox" id="CHECK_ALL" onclick="select_all()" >
														</th>
														<th field="PK_COURSE_OFFERING" width="150px" hidden="true" sortable="true" ></th>
														
														<th field="CAMPUS_CODE" width="150px" align="left" sortable="true" ><?=CAMPUS?></th>
														<th field="TERM_BEGIN_DATE" width="100px" align="left" sortable="true" ><?=TERM?></th>
														<th field="COURSE_CODE" width="150px" align="left" sortable="true" ><?=COURSE_CODE?></th>
														<th field="SESSION" width="100px" align="left" sortable="true" ><?=SESSION?></th>
														<th field="LMS_CODE" width="100px" align="left" sortable="true" ><?=LMS_CODE?></th>
														<th field="INSTRUCTOR_NAME" width="200px" align="left" sortable="true" ><?=INSTRUCTOR?></th>
														<th field="SENT_ON" width="130px" align="left" sortable="true" ><?=SENT_ON?></th>
														<th field="SENT_BY" width="150px" align="left" sortable="true" ><?=SENT_BY?></th>
														<th field="SENT" width="50px" align="left" sortable="true" ><?=SENT?></th>
														<th field="MESSAGE" width="200px" align="left" sortable="true" ><?=MESSAGE?></th>
														<!--<th field="ROOM_NO" width="150px" align="left" sortable="true" ><?=ROOM?></th>-->
														<!--<th field="COURSE_OFFERING_STATUS" width="100px" align="left" sortable="true" ><?=COURSE_OFFERING_STATUS?></th>-->
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
	<script type="text/javascript">
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
	
	function select_all(type){
		
		var str = '';
		if(document.getElementById('CHECK_ALL').checked == true) {
			str = true;
		} else {
			str = false;
		}
		
		var flag = 0;
		var CHK_PK_STUDENT_MASTER = document.getElementsByName('PK_COURSE_OFFERING[]')
		for(var i = 0 ; i < CHK_PK_STUDENT_MASTER.length ; i++){
			CHK_PK_STUDENT_MASTER[i].checked = str
			flag = 1;
		}
		show_btn()
	}
	
	function show_btn(){
		var flag = 0;
		var CHK_PK_STUDENT_MASTER = document.getElementsByName('PK_COURSE_OFFERING[]')
		for(var i = 0 ; i < CHK_PK_STUDENT_MASTER.length ; i++){
			if(CHK_PK_STUDENT_MASTER[i].checked == true) {
				flag = 1;
				break;
			}
		}
		
		if(flag == 1) {
			document.getElementById('SEND_BTN').style.display = 'inline';
		} else {
			document.getElementById('SEND_BTN').style.display = 'none';
		}
	}
	
	function validate_form(){
		var flag = 0;
		var CHK_PK_STUDENT_MASTER = document.getElementsByName('PK_COURSE_OFFERING[]')
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