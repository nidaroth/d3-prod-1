<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/employee.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}
if($_GET['act'] == 'del')	{
	/*
	if($_SESSION['PK_ROLES'] == 2){
		$db->Execute("DELETE FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$db->Execute("DELETE FROM S_EMPLOYEE_CONTACT WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$db->Execute("DELETE FROM S_EMPLOYEE_CAMPUS WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
		
		$db->Execute("DELETE FROM Z_USER WHERE ID = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_USER_TYPE = 2 ");
	} else if($_SESSION['PK_ROLES'] == 3){
		$res  = $db->Execute("SELECT PK_CAMPUS FROM S_EMPLOYEE_CAMPUS WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
		$res1 = $db->Execute("SELECT PK_CAMPUS FROM S_EMPLOYEE_CAMPUS WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($_SESSION[PK_CAMPUS]) "); 
		if($res->RecordCount() == 1 && $res1->RecordCount() > 0){
			$db->Execute("DELETE FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_EMPLOYEE_CONTACT WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_EMPLOYEE_CAMPUS WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
			
			$db->Execute("DELETE FROM Z_USER WHERE ID = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_USER_TYPE = 2 ");
		} else {
			$db->Execute("DELETE FROM S_EMPLOYEE_CAMPUS WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_CAMPUS IN ($_SESSION[PK_CAMPUS]) "); 
		}
	}*/
	/*
	$res_check1 = $db->Execute("select PK_CUSTOM_REPORT from S_CUSTOM_REPORT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_GET[id]' ");
	$res_check2 = $db->Execute("select PK_EVENT_TEMPLATE_RECIPIENTS from S_EVENT_TEMPLATE_RECIPIENTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_GET[id]' ");
	$res_check3 = $db->Execute("select PK_INSTRUCTOR_STUDENT from S_INSTRUCTOR_STUDENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_GET[id]' ");
	$res_check4 = $db->Execute("select PK_NOTIFICATION_SETTINGS_DETAIL from S_NOTIFICATION_SETTINGS_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_GET[id]' ");
	$res_check5 = $db->Execute("select PK_STUDENT_DOCUMENTS from S_STUDENT_DOCUMENTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_GET[id]' ");
	$res_check6 = $db->Execute("select PK_STUDENT_TASK from S_STUDENT_TASK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_GET[id]' ");
	$res_check7 = $db->Execute("select PK_ANNOUNCEMENT_EMPLOYEE from Z_ANNOUNCEMENT_EMPLOYEE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_GET[id]' ");
	$res_check8 = $db->Execute("select PK_NOTIFICATION_RECIPIENTS from Z_NOTIFICATION_RECIPIENTS WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' ");
	$res_check9 = $db->Execute("select PK_COURSE_OFFERING from S_COURSE_OFFERING WHERE INSTRUCTOR = '$_GET[id]' ");
	$res_check10 = $db->Execute("select PK_COURSE_OFFERING from S_COURSE_OFFERING_ASSISTANT WHERE ASSISTANT = '$PK_EMPLOYEE_MASTER' ");
	
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 && $res_check4->RecordCount() == 0 && $res_check5->RecordCount() == 0 && $res_check6->RecordCount() == 0 && $res_check7->RecordCount() == 0 && $res_check8->RecordCount() == 0 && $res_check9->RecordCount() == 0  && $res_check10->RecordCount() == 0) {
		$db->Execute("DELETE FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$db->Execute("DELETE FROM S_EMPLOYEE_CONTACT WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$db->Execute("DELETE FROM S_EMPLOYEE_CAMPUS WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$db->Execute("DELETE FROM S_EMPLOYEE_DEPARTMENT WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$db->Execute("DELETE FROM S_EMPLOYEE_RACE WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$db->Execute("DELETE FROM S_EMPLOYEE_NOTES WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$db->Execute("DELETE FROM S_EMPLOYEE_CUSTOM_FIELDS WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
	*/
	header("location:manage_employee?t=".$_GET['t']);
} 
if($_GET['t'] == 1){
	$page_title = EMPLOYEE_PAGE_TITLE_1; //Ticket # 1608
} else if($_GET['t'] == 2){
	$page_title = TEACHER_PAGE_TITLE;
} 
//$page_title = $_SESSION['EMPLOYEE_LABEL'];
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
	<title><?=$page_title?> | <?=$title?></title>
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
				<!-- Ticket # 1353 -->
                <div class="row page-titles">
                    <div class="col-md-2 align-self-center" style="flex: 0 0 10%;max-width: 10%;" >
                        <h4 class="text-themecolor"><?=$page_title?> </h4>
                    </div>
					
					<div class="col-md-1 align-self-center">
                       <select name="SHOW_AVAILABLE_ONLY" id="SHOW_AVAILABLE_ONLY" onchange="doSearch()" class="form-control" style="margin-bottom: 0;" >
							<option value=""><?=AVAILABLE?></option>
							<option value="1">Yes</option>
							<option value="2">No</option>
						</select>
                    </div>
					
					<div class="col-md-1 align-self-center">
						 <select name="HAS_LOGIN" id="HAS_LOGIN" onchange="doSearch()" class="form-control" style="margin-bottom: 0;" >
							<option value=""><?=HAS_LOGIN?></option>
							<option value="1">Yes</option>
							<option value="2">No</option>
						</select>
					</div>
					
					<div class="col-md-1 align-self-center" style="flex: 0 0 10.66667%;max-width: 10.66667%;" >
						 <select name="SCHOOL_ADMIN" id="SCHOOL_ADMIN" onchange="doSearch()" class="form-control" style="margin-bottom: 0;" >
							<option value=""><?=SCHOOL_ADMIN?></option>
							<option value="1">Yes</option>
							<option value="2">No</option>
						</select>
					</div>
					
					<div class="col-md-1 align-self-center">
						 <select name="SHOW_ACTIVE_ONLY" id="SHOW_ACTIVE_ONLY" onchange="doSearch()" class="form-control" style="margin-bottom: 0;" >
							<option value=""><?=ACTIVE?></option>
							<option value="1">Yes</option>
							<option value="2">No</option>
						</select>
					</div>
					
					<div class="col-md-1 align-self-center">
						 <select name="IS_FACULTY" id="IS_FACULTY" onchange="doSearch()" class="form-control" style="margin-bottom: 0;" >
							<option value=""><?=INSTRUCTOR?></option>
							<option value="1">Yes</option>
							<option value="2">No</option>
						</select>
					</div>
					
					<!-- <div class="col-md-1 align-self-center"> -->
						<!--<select name="PAID_USER" id="PAID_USER" onchange="doSearch()" class="form-control" style="margin-bottom: 0;" >
							<option value=""><?=PAID_USER?></option>
							<option value="1">Yes</option>
							<option value="2">No</option>
						</select>-->
					<!-- </div> -->
					
					<div class="col-md-2 align-self-center text-right">
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div>  
			
                    <div class="col-md-0 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
							<!-- Ticket # 703 -->
							<a href="manage_employee?t=<?=$_GET['t']?>&clear=1" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-newspaper"></i> <?=CLEAR_FILTER?></a>
							<!-- Ticket # 703 -->
                            <a href="employee?t=<?=$_GET['t']?>" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>
							
							 <a href="employee_report" class="btn btn-info d-none d-lg-block m-l-15"><?=MNU_REPORTS?></a>
                        </div>
                    </div>
                </div>
				<!-- Ticket # 1353 -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" 
										url="grid_employee?t=<?=$_GET['t']?>" toolbar="#tb" pagination="true" pageSize = 25 autoRowHeight="true" nowrap="false"  >
											<thead>
												<tr>
													<th field="PK_EMPLOYEE_MASTER" width="150px" hidden="true" sortable="true" ></th>
													<th field="NAME" width="200px" align="left" sortable="true" ><?=NAME?></th>
													<th field="CAMPUS" width="150px" align="left" sortable="true" ><?=CAMPUS?></th>
													<th field="EMAIL" width="180px" align="left" sortable="true" ><?=EMAIL_USER_ID?></th>
													<th field="CELL_PHONE" width="130px" align="left" sortable="true" ><?=CELL_PHONE?></th>
													<th field="DEPARTMENT" width="180px" align="left" sortable="true" ><?=DEPARTMENT?></th>
													<th field="TITLE" width="200px" align="left" sortable="true" ><?=TITLE?></th>
													<th field="LOGIN_CREATED_1" width="60px" align="left" sortable="true" ><?=HAS_LOGIN_1?></th>
													<th field="IS_FACULTY_1" width="100px" align="left" sortable="true" ><?=INSTRUCTOR?></th>
													<th field="IS_ADMIN_1" width="80px" align="left" sortable="true" ><?=SCHOOL_ADMIN_1?></th>
													<th field="ACTION" width="100px" align="left" sortable="false" ><?=OPTIONS?></th>
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
						<h4 class="modal-title" id="exampleModalLabel1">Delete Confirmation</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							Are you sure want to Delete this Record?
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info">Yes</button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" >No</button>
					</div>
				</div>
			</div>
		</div>
    </div>
	<? require_once("js.php"); ?>
	
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script type="text/javascript" src="../backend_assets/dist/js/jquery.easyui.min.js"></script>
	<script src="../backend_assets/dist/js/jquery-ui.js"></script> 
	<script type="text/javascript">
	function doSearch(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid('load',{
				SEARCH  			: $('#SEARCH').val(),
				SHOW_AVAILABLE_ONLY	: $('#SHOW_AVAILABLE_ONLY').val(),
				SHOW_ACTIVE_ONLY	: $('#SHOW_ACTIVE_ONLY').val(),
				HAS_LOGIN			: $('#HAS_LOGIN').val(), //Ticket # 1353
				IS_FACULTY			: $('#IS_FACULTY').val(), //Ticket # 1353
				SCHOOL_ADMIN		: $('#SCHOOL_ADMIN').val(), //Ticket # 1353
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
						window.location.href='employee?id='+selected_row.PK_EMPLOYEE_MASTER+'&t=<?=$_GET['t']?>';
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
	function delete_row(id){
		jQuery(document).ready(function($) {
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
		});
	}
	function conf_delete(val,id){
		if(val == 1)
			window.location.href = 'manage_employee?act=del&id='+$("#DELETE_ID").val()+'&t=<?=$_GET['t']?>';
		else
			$("#deleteModal").modal("hide");
	}

	function refresh_win(win){
		win.close();
		doSearch()
	}
	</script>

</body>

</html>