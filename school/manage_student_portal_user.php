<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student_portal_user.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if($_GET['act'] == 'make_inactive')	{
	$db->Execute("UPDATE Z_USER SET ACTIVE = 0 WHERE ID = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_USER_TYPE = 3 "); 
	header("location:manage_student_portal_user");
}
if($_GET['act'] == 'make_active')	{
	$db->Execute("UPDATE Z_USER SET ACTIVE = 1 WHERE ID = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_USER_TYPE = 3 "); 
	header("location:manage_student_portal_user");
}
/*
if($_GET['act'] == 'delete')	{
	$db->Execute("DELETE FROM Z_USER WHERE ID = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_USER_TYPE = 3");
	$db->Execute("UPDATE S_STUDENT_MASTER SET LOGIN_CREATED = 0 WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	header("location:manage_student_portal_user");
}*/
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
		<?=STUDENT_PORTAL_USER_PAGE_TITLE; ?> | <?=$title?>
	</title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<style>
		/* Ticket # 1149 - term */
		.dropdown-menu>li>a { white-space: nowrap; }
		.option_red > a > label{color:red !important}
		/* Ticket # 1149 - term */
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
				<div class="row page-titles" style="padding-bottom: 10px;" >
                    <div class="col-md-8 align-self-center">
                        <h4 class="text-themecolor">
							<?=STUDENT_PORTAL_USER_PAGE_TITLE ?>
						</h4>
                    </div>
					<div class="col-md-4 align-self-center text-right">
                    </div>
				</div>
				
                <div class="row">
					<div class="col-md-12">
						<table>
							<tr>
								<td style="width:20%" >
									<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="doSearch()">
										<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
										while (!$res_type->EOF) { ?>
											<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
										<?	$res_type->MoveNext();
										} ?>
									</select>
								</td>
								<td style="width:20%" >
									<div id="status_div" >
										<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" onchange="doSearch()">
											<? $cond = "";
											if($_GET['t'] == 1)
												$cond = " AND (ADMISSIONS = 1) ";
											else if($_GET['t'] == 2 || $_GET['t'] == 3 || $_GET['t'] == 4 || $_GET['t'] == 5 || $_GET['t'] == 6)
												$cond = " AND (ADMISSIONS = 0) ";
												
											$res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 $cond order by STUDENT_STATUS ASC");
											while (!$res_type->EOF) { ?>
												<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
											<?	$res_type->MoveNext();
											} ?>
										</select>
									</div>
								</td>
								<td style="width:20%" >
									<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="doSearch()">
										<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
										while (!$res_type->EOF) { ?>
											<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>"  ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
										<?	$res_type->MoveNext();
										} ?>
									</select>
								</td>
								<td style="width:10%" >
									<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" onchange="doSearch()" >
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
								</td>
								<td style="width:10%">
									<select id="LOGIN_STATUS" name="LOGIN_STATUS" class="form-control" onchange="doSearch()" style="margin-bottom: 0;" >
										<option value="" ><?=WEB_LOGIN_STATUS?></option>
										<option value="1" ><?=ACTIVE?></option>
										<option value="2" ><?=INACTIVE?></option>
									</select>
								</td>
								<td style="width:20%">
									<input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=NAME_SEARCH?>"  style="font-family: FontAwesome;" onkeypress="search(event)">
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
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" 
										url="grid_student_portal_user" toolbar="#tb" pagination="true" pageSize = 25 >
											<thead>
												<tr>
													<th field="PK_STUDENT_MASTER" width="150px" hidden="true" sortable="true" ></th>
													
													<th field="STU_NAME" width="200px" align="left" sortable="true" ><?=STUDENT_NAME?></th>
													<th field="STUDENT_ID" width="150px" align="left" sortable="true" ><?=STUDENT_ID?></th>
													<th field="USER_ID" width="165px" align="left" sortable="true" ><?=LOGIN_ID?></th>
													<th field="DATE_OF_BIRTH" width="120px" align="left" sortable="true" ><?=DATE_OF_BIRTH?></th>
													<th field="EMAIL" width="230px" align="left" sortable="true" ><?=EMAIL?></th>
													<th field="LOGIN_TIME" width="130px" align="left" sortable="true" ><?=LAST_LOGIN?></th>
													<th field="LOGIN_STATUS" width="120px" align="left" sortable="true" ><?=LOGIN_STATUS?></th>
													<th field="CAMPUS" width="120px" align="left" sortable="true" ><?=CAMPUS?></th>
													<th field="ACTION" width="150px" align="center" sortable="false" ><?=OPTIONS?></th>
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
						<h4 class="modal-title" id="exampleModalLabel1"><?=CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<div id="delete_pop_msg_div"></div>
							<input type="hidden" id="DELETE_ID" value="0" />
							<input type="hidden" id="DELETE_TYPE" value="" />
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
	<script src="../backend_assets/dist/js/jquery-ui.js"></script> 
	<script type="text/javascript">
	function doSearch(){		
		jQuery(document).ready(function($) {
			$('#tt').datagrid('load',{
				SEARCH  			: $('#SEARCH').val(),
				PK_STUDENT_STATUS	: $('#PK_STUDENT_STATUS').val(),
				PK_CAMPUS_PROGRAM	: $('#PK_CAMPUS_PROGRAM').val(),
				PK_TERM_MASTER		: $('#PK_TERM_MASTER').val(),
				LOGIN_STATUS		: $('#LOGIN_STATUS').val(),
				PK_CAMPUS			: $('#PK_CAMPUS').val(),
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
					if(field != 'ACTION'){
						var selected_row = $('#tt').datagrid('getSelected');
						//window.location.href='student?id='+selected_row.PK_STUDENT_MASTER+'&eid='+selected_row.PK_STUDENT_ENROLLMENT+'&t=<?=$_GET['t']?>';
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
	function delete_row(id,type){
		jQuery(document).ready(function($) {
			
			$("#DELETE_ID").val(id)
			$("#DELETE_TYPE").val(type)
			
			if(type == 'make_inactive') {
				$("#deleteModal").modal()
				document.getElementById('delete_pop_msg_div').innerHTML = '<?=MAKE_INACTIVE_MESSAGE?>';
			} else if(type == 'make_active') {
				$("#deleteModal").modal()
				document.getElementById('delete_pop_msg_div').innerHTML = '<?=MAKE_ACTIVE_MESSAGE?>';
			} else if(type == 'delete') {
				$("#deleteModal").modal()
				document.getElementById('delete_pop_msg_div').innerHTML = '<?=DELETE_LOGIN_MESSAGE?>';
			} else if(type == 'reset_password') {
				$("#deleteModal").modal()
				document.getElementById('delete_pop_msg_div').innerHTML = '<?=RESET_PASSWORD_MESSAGE?>';
			} else if($("#DELETE_TYPE").val() == 'log')	{
				var w = 1300;
				var h = 700;
				// var id = common_id;
				var left = (screen.width/2)-(w/2);
				var top = (screen.height/2)-(h/2);
				var parameter = 'toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,width='+w+', height='+h+', top='+top+', left='+left;
				window.open('student_login_history?id='+$("#DELETE_ID").val(),'',parameter);
				return false;
			}
		});
	}
	function conf_delete(val){
		jQuery(document).ready(function($) {
			if(val == 1) {
				if($("#DELETE_TYPE").val() == 'make_inactive')
					window.location.href = 'manage_student_portal_user?act=make_inactive&id='+$("#DELETE_ID").val();
				else if($("#DELETE_TYPE").val() == 'make_active')
					window.location.href = 'manage_student_portal_user?act=make_active&id='+$("#DELETE_ID").val();
				else if($("#DELETE_TYPE").val() == 'delete')
					window.location.href = 'manage_student_portal_user?act=delete&id='+$("#DELETE_ID").val();
				else if($("#DELETE_TYPE").val() == 'reset_password')
					confirm_reset_password($("#DELETE_ID").val())
			} else
				$("#deleteModal").modal("hide");
		});
	}
	
	function confirm_reset_password(id){
		jQuery(document).ready(function($) { 
			var data = 'id='+id+'&type=s';
			var value = $.ajax({
				url: "ajax_reset_password",	
				type: "POST",
				data: data,		
				async: false,
				cache :false,
				success: function (data) {
					alert(data)
					$("#deleteModal").modal("hide");
				}		
			}).responseText;
		});
	}
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

			$('#PK_TERM_MASTER').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=FIRST_TERM_DATE?>',
				nonSelectedText: '<?=FIRST_TERM_DATE?>',
				numberDisplayed: 1,
				nSelectedText: '<?=FIRST_TERM_DATE?> selected'
			});
			
			$('#PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=CAMPUS?>',
				nonSelectedText: '<?=CAMPUS?>',
				numberDisplayed: 1,
				nSelectedText: '<?=CAMPUS?> selected'
			});
			
		});
	</script>
</body>

</html>