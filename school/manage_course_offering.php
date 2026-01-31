<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$current_page1 = $_SERVER['PHP_SELF'];
$parts1 = explode('/', $current_page1);
$current_page = $parts1[count($parts1) - 1];
if($current_page != $_SESSION['PREVIOUS_PAGE'] || $_GET['clear'] == 1 ){ //Ticket # 1826
	$_SESSION['SORT_FIELD'] 	 = 'S_TERM_MASTER.BEGIN_DATE DESC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC ';
	$_SESSION['SORT_ORDER'] 	 = '';
	$_SESSION['PAGE'] 			 = 1;
	$_SESSION['rows'] 			 = 25;
	$_SESSION['PREVIOUS_PAGE'] 	 = $current_page;
	$_SESSION['SRC_SEARCH'] 		= '';
	$_SESSION['SRC_PK_CAMPUS'] 		= '';
	$_SESSION['SRC_PK_TERM_MASTER'] = '';
	$_SESSION['SRC_PK_COURSE'] 		= '';
	$_SESSION['SRC_PK_SESSION'] 	= '';
	$_SESSION['SRC_INSTRUCTOR'] 	= '';
	$_SESSION['SRC_PK_CAMPUS_ROOM'] = '';
}

if($_GET['act'] == 'del')	{
	//Ticket # 1458
	/*$PK_COURSE_OFFERING = $_GET['id'];

	$res_def_grade 	= $db->Execute("SELECT PK_GRADE  FROM S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND IS_DEFAULT = 1 ");
	$PK_GRADE 		= $res_def_grade->fields['PK_GRADE'];

	$res_grade  = $db->Execute("select PK_STUDENT_COURSE from S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND (FINAL_GRADE > 0 AND FINAL_GRADE != '$PK_GRADE') ");
	$res_attend = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND COMPLETED = 1");
	$res_stu 	= $db->Execute("select PK_STUDENT_COURSE from S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");

	if($res_grade->RecordCount() == 0 && $res_attend->RecordCount() == 0 && $res_stu->RecordCount() == 0 ) {
		$db->Execute("DELETE FROM S_COURSE_OFFERING WHERE PK_COURSE_OFFERING = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$db->Execute("DELETE FROM S_COURSE_OFFERING_SCHEDULE WHERE PK_COURSE_OFFERING = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		$res = $db->Execute("SELECT PK_COURSE_OFFERING_SCHEDULE_DETAIL FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		while (!$res->EOF) {
			$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $res->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
			
			$db->Execute("DELETE FROM S_STUDENT_SCHEDULE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$PK_COURSE_OFFERING_SCHEDULE_DETAIL' ");
			$db->Execute("DELETE FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$PK_COURSE_OFFERING_SCHEDULE_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			
			$res->MoveNext();
		}
		
		$db->Execute("DELETE FROM S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$_GET[id]' ");
		$db->Execute("DELETE FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$_GET[id]' ");
		$db->Execute("DELETE FROM S_STUDENT_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$_GET[id]' ");
	}
	*/
	header("location:manage_course_offering");
} else if($_GET['act'] == 'assign')	{
	$ids = explode(",",$_GET['id']);
	foreach($ids as $id){
		$db->Execute("UPDATE S_COURSE_OFFERING SET INSTRUCTOR = '$_GET[e]' WHERE PK_COURSE_OFFERING = '$id' AND  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	}
	
	header("location:manage_course_offering");
} 

$PK_CAMPUS1 = '';
$res = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res->RecordCount() == 1)
	$PK_CAMPUS1 = $res->fields['PK_CAMPUS'];?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=COURSE_OFFERING_PAGE_TITLE?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<style>
	.datagrid-header td {font-size: 14px !important; vertical-align: bottom;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-2 align-self-center" style="max-width:13%;flex: 13%;" >
                        <h4 class="text-themecolor"><?=COURSE_OFFERING_PAGE_TITLE?> </h4>
                    </div>
					
					<div class="col-md-2 align-self-center text-right" style="max-width:14%;flex: 14%;">
						<select id="PK_CAMPUS" name="PK_CAMPUS" class="form-control" onchange="doSearch()" >
							<option value=""><?=CAMPUS?></option>
							<? /* Ticket #1695  */
							$res_type = $db->Execute("select PK_CAMPUS,CAMPUS_CODE, ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CAMPUS_CODE ASC"); 
							while (!$res_type->EOF) { 
								$option_label = $res_type->fields['CAMPUS_CODE'];
								if($res_type->fields['ACTIVE'] == 0)
									$option_label .= " (Inactive)"; ?>
								<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" <? if($_SESSION['SRC_PK_CAMPUS'] == $res_type->fields['PK_CAMPUS']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
							<?	$res_type->MoveNext();
							} /* Ticket #1695  */ ?>
						</select>
					</div>
					
					<div class="col-md-2 align-self-center text-right" style="max-width:14%;flex: 14%;" >
                       <select id="PK_COURSE" name="PK_COURSE" class="form-control " onchange="doSearch();" >
							<option value=""><?=COURSE_CODE?></option>
						   <? /* Ticket #1695  */
						   $res_type = $db->Execute("select PK_COURSE, CONCAT(COURSE_CODE, ' - ', TRANSCRIPT_CODE, ' - ', COURSE_DESCRIPTION) as TRANSCRIPT_CODE, ACTIVE from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, TRANSCRIPT_CODE ASC");
							while (!$res_type->EOF) { 
								$option_label = $res_type->fields['TRANSCRIPT_CODE'];
								if($res_type->fields['ACTIVE'] == 0)
									$option_label .= " (Inactive)"; ?>
								<option value="<?=$res_type->fields['PK_COURSE'] ?>" <? if($_SESSION['SRC_PK_COURSE'] == $res_type->fields['PK_COURSE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> > <?=$option_label ?></option>
							<?	$res_type->MoveNext();
							} /* Ticket #1695  */ ?>
						</select>
					</div> 
					<?php
					$where_instructor="(IS_FACULTY = 1 OR IS_ADMIN=1)"; 
					?>
					<div class="col-md-2 align-self-center text-right" style="max-width:14%;flex: 14%;">
						<select id="INSTRUCTOR" name="INSTRUCTOR" class="form-control" onchange="doSearch()" >
							<option value=""><?=INSTRUCTOR?></option>
							<option value="-1"><?=UNASSIGNED?></option>
							<? /* Ticket #1695  */
							$res_type = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, S_EMPLOYEE_MASTER.ACTIVE from S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND $where_instructor order by S_EMPLOYEE_MASTER.ACTIVE = 1 DESC, CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");
							while (!$res_type->EOF) { 
								$option_label = $res_type->fields['NAME'];
								if($res_type->fields['ACTIVE'] == 0)
									$option_label .= " (Inactive)"; ?>
								<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER'] ?>" <? if($_SESSION['SRC_INSTRUCTOR'] == $res_type->fields['PK_EMPLOYEE_MASTER']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
							<?	$res_type->MoveNext();
							} /* Ticket #1695  */ ?>
						</select>
					</div> 
					
					<div class="col-md-2 align-self-center text-right" style="max-width:12%;flex: 12%;">
						<select id="PK_CAMPUS_ROOM" name="PK_CAMPUS_ROOM" class="form-control" onchange="doSearch()" >
							<option value=""><?=ROOM?></option>
							<option value="-1"><?=UNASSIGNED?></option>
							<? /* Ticket #1695  */
							$res_type = $db->Execute("select PK_CAMPUS_ROOM, CONCAT(ROOM_NO, ' - ', ROOM_DESCRIPTION) as ROOM_NO, ACTIVE from M_CAMPUS_ROOM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC, ROOM_NO ASC");
							while (!$res_type->EOF) { 
								$option_label = $res_type->fields['ROOM_NO'];
								if($res_type->fields['ACTIVE'] == 0)
									$option_label .= " (Inactive)";  ?>
								<option value="<?=$res_type->fields['PK_CAMPUS_ROOM'] ?>" <? if($_SESSION['SRC_PK_CAMPUS_ROOM'] == $res_type->fields['PK_CAMPUS_ROOM']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
							<?	$res_type->MoveNext();
							} /* Ticket #1695  */ ?>
						</select>
					</div>  
					
					<div class="col-md-1 align-self-center text-right" style="max-width:10%;flex: 10%;" >
						<select id="PK_SESSION" name="PK_SESSION" class="form-control" onchange="doSearch();" >
							<option value="" ><?=SESSION?></option>
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
					
			
					
                    <div class="col-md-3 align-self-center text-right" style="max-width:33%;flex: 13%;">
						<input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome;margin-top: -11px" onkeypress="search(event)">
                    </div>
                </div>
				
				<div class="row ">
					<div class="col-md-12 align-self-center text-right" ><!-- Ticket # 1503-->
					<div class="d-flex align-items-center " style="margin-bottom:5px">
					<div><b>Term Begin Date Range</b></div>
					<div style="margin-left:12%"><b>Term End Date Range</b></div>

					</div>			
						<?php 
						$term_start_date=date('m/d/Y',strtotime("-3 months",strtotime(date('Y-m-d'))));
						$term_end_date=date('m/d/Y',strtotime("+3 months",strtotime(date('Y-m-d'))));

						?>	
						<div class="d-flex align-items-center " style="margin-bottom:10px">
						<input type="text" class="form-control date" name="term_begin_start_date" field="term_begin_start_date" id="term_begin_start_date" onchange="doSearch()" style="max-width:11%;" placeholder="Start Date" value="<?php echo $term_start_date; ?>" >
						<input type="text" class="form-control date" name="term_begin_end_date"  field="term_begin_end_date" id="term_begin_end_date" onchange="doSearch()" style="max-width:11%;" placeholder="End Date" value="<?php echo $term_end_date; ?>" >
						<input type="text" class="form-control date" name="term_end_start_date" id="term_end_start_date" onchange="doSearch()" style="max-width:11%;" placeholder="Start Date">
						<input type="text" class="form-control date" name="term_end_end_date" id="term_end_end_date" onchange="doSearch()" style="max-width:11%;margin-left:5px;" placeholder="End Date">
							<!-- <div style="max-width:11%;"></div> -->
							<? if($_SESSION['PK_LANGUAGE'] == 1)
								$lan_field = "TOOL_CONTENT_ENG";
							else
								$lan_field = "TOOL_CONTENT_SPA"; 
							$res_help = $db->Execute("select $lan_field from Z_HELP WHERE PK_HELP = 53"); ?>
										
							<a href="help_docs?id=53" target="_blank"><i class="mdi mdi-help-circle help_size"  title="<?=$res_help->fields[$lan_field] ?>" data-toggle="tooltip" data-placement="left" ></i></a>
							
							<!-- Ticket # 1826 -->
							<a href="manage_course_offering?clear=1" class="btn btn-info d-none d-lg-block m-l-15" style="margin-top: -8px;" ><i class="fas fa-newspaper"></i> <?=CLEAR_FILTER?></a>
							<a href="course_offering_data_view" class="btn btn-info d-none d-lg-block m-l-15" style="margin-top: -8px;" ><i class="fas fa-newspaper"></i> <?=DATA_VIEW?></a>
							<!-- Ticket # 1826 -->
							
							<a href="javascript:void(0)" class="btn btn-info d-none d-lg-block m-l-15" id="UPDATE_BTN" style="margin-top: -8px;display:none !important" onclick="show_update_popup()" ><?=UPDATE?></a>
							
							<a href="course_offering_copy_by_term" class="btn btn-info d-none d-lg-block m-l-15" style="margin-top: -8px;" ><i class="fa fa-plus-circle"></i> <?=COURSE_OFFERING_COPY_BY_TERM?></a><!-- Ticket # 1503-->
							<a href="course_offering" class="btn btn-info d-none d-lg-block m-l-15" style="margin-top: -8px;" ><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>
							
						</div>
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
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_course_offering"
										toolbar="#tb" pagination="true" loadMsg="Processing, please wait..."  pageNumber="<?=$_SESSION['PAGE']?>" pageSize="<?=$_SESSION['rows']?>" <?=$sort?> data-options="
				onClickCell: function(rowIndex, field, value){
					$('#tt').datagrid('selectRow',rowIndex);
					if(field != 'ACTION' && field != 'INSTRUCTOR_NAME' && field != 'SELECT'){
						var selected_row = $('#tt').datagrid('getSelected');
						window.location.href='course_offering?id='+selected_row.PK_COURSE_OFFERING;
					}
				},
				view: $.extend(true,{},$.fn.datagrid.defaults.view,{
					onAfterRender: function(target){
						$.fn.datagrid.defaults.view.onAfterRender.call(this,target);
						$('.datagrid-header-inner').width('100%') 
						$('.datagrid-btable').width('100%') 
						$('.datagrid-body').css({'overflow-y': 'hidden'});
					}
				}),
				onLoadSuccess: function(){
					document.getElementsByClassName('preloader_grid')[0].style.display = 'none';
			},
			queryParams:{
				TREM_BEGIN_START_DATE : $('#term_begin_start_date').val(),
				TREM_BEGIN_END_DATE	  : $('#term_begin_end_date').val()
			}
				" >
									
											<thead>
												<tr>
													<th field="PK_COURSE_OFFERING" width="150px" hidden="true" sortable="true" ></th>
													<th field="SELECT" width="20px" sortable="true" >
														<input type="checkbox" id="CHECK_ALL" onclick="select_all()" >
													</th>
													<th field="CAMPUS_CODE" width="120px" align="left" sortable="true" ><?=CAMPUS_CODE?></th> <!-- Ticket # 1515 -->
													<th field="TERM_BEGIN_DATE" width="80px" align="left" sortable="true" ><?=TERM?></th>
													<th field="COURSE_CODE" width="140px" align="left" sortable="true" ><?=COURSE_CODE_1?></th> <!-- Ticket # 1515 -->
													<th field="TRANSCRIPT_CODE" width="120px" align="left" sortable="true" ><?=TRANSCRIPT_CODE?></th> <!-- Ticket # 1741 -->
													<th field="SESSION" width="60px" align="left" sortable="true" ><?=SESSION?></th>
													<th field="SESSION_NO" width="80px" align="left" sortable="true" ><?=SESSION_NO_1?></th>
													<th field="INSTRUCTOR_NAME" width="140px" align="left" sortable="true" ><?=INSTRUCTOR?></th>
													<th field="ROOM_NO" width="120px" align="left" sortable="true" ><?=ROOM_NO_1?></th>
													<th field="COURSE_OFFERING_STATUS" width="100px" align="left" sortable="true" ><?=COURSE_OFFERING_STATUS?></th>
													<th field="NO_STUDENT" width="75px" align="left" sortable="true" ><?=STUDENTS?></th> <!-- Ticket # 1515 -->
													
													<th field="CLASS_SIZE" width="60px" align="left" sortable="true" ><?=CLASS_SIZE_2?></th>
													<th field="ROOM_SIZE" width="60px" align="left" sortable="true" ><?=ROOM_SIZE_2?></th>
													
													<th field="LMS_ACTIVE" width="60px" align="left" sortable="true" ><?=LMS_ACTIVE_1?></th>
													<th field="LMS_CODE" width="80px" align="left" sortable="true" ><?=LMS_CODE_1?></th>
													
													<th field="ACTION" width="80px" align="left" sortable="false" ><?=OPTION?></th>
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
		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=DELETE_CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body" id="modal_content_div" >
					</div>
					<div class="modal-footer">
						<input type="hidden" id="DELETE_ID" value="0" />
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info" disabled id="delete_button" ><?=YES?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" ><?=NO?></button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=ASSIGN_INSTRUCTOR?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<div class="col-12 col-sm-6 form-group" id="ASSIGN_INSTRUCTOR_DIV" >
								
							</div>
							<input type="hidden" id="ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_assign_ins(1)" class="btn waves-effect waves-light btn-info"><?=ASSIGN?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_assign_ins(0)" ><?=CANCEL?></button>
					</div>
				</div>
			</div>
		</div>
    </div>
	<? require_once("js.php"); ?>
	
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script type="text/javascript" src="../backend_assets/dist/js/jquery.easyui.min.js"></script>
	<script src="../backend_assets/dist/js/jquery-ui.js"></script>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
	function doSearch(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid('load',{
				PK_COURSE  		: $('#PK_COURSE').val(),
				PK_TERM_MASTER  : $('#PK_TERM_MASTER').val(),
				PK_CAMPUS  		: $('#PK_CAMPUS').val(),
				INSTRUCTOR  	: $('#INSTRUCTOR').val(),
				SEARCH  		: $('#SEARCH').val(),
				PK_SESSION		: $('#PK_SESSION').val(),
				PK_CAMPUS_ROOM	: $('#PK_CAMPUS_ROOM').val(),
				TREM_BEGIN_START_DATE : $('#term_begin_start_date').val(),
				TREM_BEGIN_END_DATE	  : $('#term_begin_end_date').val(),
				TREM_END_START_DATE	  : $('#term_end_start_date').val(),
				TREM_END_END_DATE	  : $('#term_end_end_date').val()
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
			jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
			// DIAM-589
			var loader = document.getElementsByClassName("preloader_grid");
			loader[0].style.display = "block";
			// End DIAM-589

		});
	});
	jQuery(document).ready(function($) {
		$(window).resize(function() {
			$('#tt').datagrid('resize');
			$('#tb').panel('resize');
		}) 
	});
	
	function enable_delete_btn(){
		var flag = 1;
		var DELETE_CHECK = document.getElementsByName('DELETE_CHECK[]')
		for(var i = 0 ; i < DELETE_CHECK.length ; i++){
			if(DELETE_CHECK[i].checked == false)
				flag = 0;
		}
		if(flag == 1)
			document.getElementById('delete_button').disabled = false;
		else
			document.getElementById('delete_button').disabled = true;
	}
	
	function delete_row(id){
		jQuery(document).ready(function($) { 
			var data  = 'id='+id;
			var value = $.ajax({
				url: "ajax_check_course_offering_data_for_delete",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					data = data.split("|||");
					if(data[0] == 0)
						document.getElementById('delete_button').disabled = false;
					else {
						document.getElementById('delete_button').disabled = true;
					}
					document.getElementById('modal_content_div').innerHTML = data[1]
					
					$("#deleteModal").modal()
					$("#DELETE_ID").val(id)
				}		
			}).responseText;
		});
	}
	function conf_delete(val,id){
		if(val == 1)
			window.location.href = 'manage_course_offering?act=del&id='+$("#DELETE_ID").val();
		else
			$("#deleteModal").modal("hide");
	}
	
	function assign_ins(id,campus_id){
		jQuery(document).ready(function($) {
			var data  = 'campus='+campus_id+'&id=ASSIGN_INSTRUCTOR&SELECTED_VALUE=';
			var value = $.ajax({
				url: "ajax_get_teacher_from_campus",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					document.getElementById('ASSIGN_INSTRUCTOR_DIV').innerHTML = data
					$("#assignModal").modal()
					$("#ID").val(id)
				}		
			}).responseText;
		});
	}
	function conf_assign_ins(val,id){
		jQuery(document).ready(function($) {
			if(val == 1) {
				if($("#ASSIGN_INSTRUCTOR").val() == '')
					alert('Please Select Instructor')
				else
					window.location.href = 'manage_course_offering?act=assign&e='+$("#ASSIGN_INSTRUCTOR").val()+'&id='+$("#ID").val();
			} else
				$("#assignModal").modal("hide");
		});
	}
	function select_all(type){
		
		var str = '';
		if(document.getElementById('CHECK_ALL').checked == true) {
			str = true;
		} else {
			str = false;
		}
		
		var CHK_PK_COURSE_OFFERING = document.getElementsByName('CHK_PK_COURSE_OFFERING[]')
		for(var i = 0 ; i < CHK_PK_COURSE_OFFERING.length ; i++){
			CHK_PK_COURSE_OFFERING[i].checked = str
		}
		
		show_btn()
	}
	
	function show_btn(){
		var flag = 0;
		var CHK_PK_COURSE_OFFERING = document.getElementsByName('CHK_PK_COURSE_OFFERING[]')
		for(var i = 0 ; i < CHK_PK_COURSE_OFFERING.length ; i++){
			if(CHK_PK_COURSE_OFFERING[i].checked == true){
				flag = 1;
				break;
			}
		}
		
		if(flag == 1)
			document.getElementById('UPDATE_BTN').style.cssText  = 'margin-top: -8px;';
		else
			document.getElementById('UPDATE_BTN').style.cssText = 'margin-top: -8px;display:none !important;';
	}
	
	function show_update_popup(){
		var w = 700;
		var h = 550;
		// var id = common_id;
		var left = (screen.width/2)-(w/2);
		var top = (screen.height/2)-(h/2);
		var ids = '';
		var CHK_PK_COURSE_OFFERING = document.getElementsByName('CHK_PK_COURSE_OFFERING[]')
		for(var i = 0 ; i < CHK_PK_COURSE_OFFERING.length ; i++){
			if(CHK_PK_COURSE_OFFERING[i].checked == true){
				if(ids != '')
					ids += ','
				ids += CHK_PK_COURSE_OFFERING[i].value
			}
		}
		
		var campus = '';
		if(document.getElementById('PK_CAMPUS').value != '')
			campus = document.getElementById('PK_CAMPUS').value
		else
			campus = '<?=$PK_CAMPUS1?>'
		
		if(ids != '') {
			var parameter = 'toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,width='+w+', height='+h+', top='+top+', left='+left;
			window.open('course_offering_update?id='+ids+'&campus='+campus,'',parameter);
			return false;
		}
	}
	function refresh_win(win){
		win.close();
		window.location.href = 'manage_course_offering';
	}
	</script>

</body>

</html>