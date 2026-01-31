<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student_status.php");
require_once("check_access.php");

if(check_access('SETUP_STUDENT') == 0 ){
	header("location:../index");
	exit;
}
if($_GET['act'] == 'del')	{
	$res_check1 = $db->Execute("select PK_CUSTOM_REPORT from S_CUSTOM_REPORT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_STATUS = '$_GET[id]' ");
	$res_check2 = $db->Execute("select PK_ENROLL_MANDATE_FIELDS from S_ENROLL_MANDATE_FIELDS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_STATUS = '$_GET[id]' ");
	$res_check3 = $db->Execute("select PK_NOTIFICATION_SETTINGS_DETAIL from S_NOTIFICATION_SETTINGS_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_STATUS = '$_GET[id]' ");
	$res_check4 = $db->Execute("select PK_STUDENT_ENROLLMENT from S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_STATUS = '$_GET[id]'");
	$res_check5 = $db->Execute("select PK_STUDENT_STATUS_LOG from S_STUDENT_STATUS_LOG WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_STATUS = '$_GET[id]' ");
	
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 && $res_check4->RecordCount() == 0 && $res_check5->RecordCount() == 0) 
		$db->Execute("DELETE FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_STATUS_MASTER = 0 ");
	header("location:manage_student_status");
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=STUDENT_STATUS_PAGE_TITLE?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<style>
	.dropdown-menu>li>a { white-space: nowrap; } /* Ticket # 1607 */
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-2 align-self-center">
                        <h4 class="text-themecolor"><?=STUDENT_STATUS_PAGE_TITLE?> </h4>
                    </div>
					
					<div class="col-md-2 align-self-center">
						<input type="checkbox" id="ADMISSIONS" value="1" onclick="doSearch()" >
						Admissions<br />
						
						<input type="checkbox" id="POST_TUITION" value="1" onclick="doSearch()" >
						Post Tuition<br />
						
						<input type="checkbox" id="CLASS_ENROLLMENT" value="1" onclick="doSearch()" >
						Class Enrollment<br />
					</div>  
					
					<div class="col-md-2 align-self-center">	
						<input type="checkbox" id="ALLOW_ATTENDANCE" value="1" onclick="doSearch()" >
						Allow Attendance<br />
						
						<input type="checkbox" id="COMPLETED" value="1" onclick="doSearch()" >
						Completed<br />
						
						<input type="checkbox" id="ACTIVE" value="1" onclick="doSearch()" >
						Active<br />
					</div>
					
					<div class="col-md-2 align-self-center">
						<select id="PK_END_DATE" name="PK_END_DATE[]" multiple class="form-control" onchange="doSearch();" >
							<? $res_type = $db->Execute("select PK_END_DATE, CODE,DESCRIPTION from M_END_DATE WHERE ACTIVE = '1' ORDER BY CODE ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_END_DATE']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION'] ?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div> 
						
					<div class="col-md-2 align-self-center text-right">
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div>  
					
                    <div class="col-md-2 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
							<? if($_SESSION['PK_LANGUAGE'] == 1)
								$lan_field = "TOOL_CONTENT_ENG";
							else
								$lan_field = "TOOL_CONTENT_SPA"; 
							$res_help = $db->Execute("select $lan_field from Z_HELP WHERE PK_HELP = 17"); ?>
										
							<a href="help_docs?id=17" target="_blank"><i class="mdi mdi-help-circle help_size" style="float: right;margin-right:5px" title="<?=$res_help->fields[$lan_field] ?>" data-toggle="tooltip" data-placement="left" ></i></a>
							
                            <a href="student_status" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_student_status"
										toolbar="#tb" pagination="true" pageSize = 25 >
											<thead>
												<tr>
													<th field="PK_STUDENT_STATUS" width="150px" hidden="true" sortable="true" ></th>
													<th field="STUDENT_STATUS" width="150px" align="left" sortable="true" >Student Status</th>
													<th field="CODE" width="100px" align="left" sortable="true" >End Date</th>
													<!--<th field="FA_STATUS" width="100px" align="left" sortable="true" >FA Status</th> Ticket # 1810-->
													<th field="ADMISSIONS_1" width="100px" align="left" sortable="true" >Admissions</th>
													<th field="POST_TUITION_1" width="100px" align="left" sortable="true" >Post Tuition</th>
													<!--<th field="DOC_28_1" width="100px" align="left" sortable="true" >Doc28.1</th> Ticket # 1810-->
													<th field="CLASS_ENROLLMENT_1" width="150px" align="left" sortable="true" >Class Enrollment</th>
													<th field="ALLOW_ATTENDANCE_1" width="160px" align="left" sortable="true" >Allow Attendance</th>
													<!--<th field="_1098T" width="50px" align="left" sortable="true" >1098T</th>--> <!-- Ticket # 1048 -->
													<th field="COMPLETED_1" width="100px" align="left" sortable="true" >Completed</th>
													<th field="ACTION" width="100px" align="left" sortable="false" ><?=OPTION?></th>
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
					<div class="modal-body">
						<div class="form-group">
							<?=DELETE_MESSAGE_GENERAL?>
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" ><?=NO?></button>
					</div>
				</div>
			</div>
		</div>
    </div>
	<? require_once("js.php"); ?>
	
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script type="text/javascript" src="../backend_assets/dist/js/jquery.easyui.min.js"></script>
	<!--<script src="../backend_assets/dist/js/jquery-ui.js"></script> -->
	<script type="text/javascript">
	function doSearch(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid('load',{
				SEARCH  : $('#SEARCH').val(),
				
				ADMISSIONS			: $('#ADMISSIONS').is(":checked"),
				POST_TUITION		: $('#POST_TUITION').is(":checked"),
				CLASS_ENROLLMENT	: $('#CLASS_ENROLLMENT').is(":checked"),
				COMPLETED			: $('#COMPLETED').is(":checked"),
				ACTIVE				: $('#ACTIVE').is(":checked"),
				ALLOW_ATTENDANCE	: $('#ALLOW_ATTENDANCE').is(":checked"),
				PK_END_DATE  : $('#PK_END_DATE').val()
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
						window.location.href='student_status?id='+selected_row.PK_STUDENT_STATUS;
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
			window.location.href = 'manage_student_status?act=del&id='+$("#DELETE_ID").val();
		else
			$("#deleteModal").modal("hide");
	}
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_END_DATE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=END_DATE?>',
			nonSelectedText: '<?=END_DATE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=END_DATE?> selected'
		});
	});
	</script>
</body>

</html>