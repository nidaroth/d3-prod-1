<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/mail.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
}

$res = $db->Execute("SELECT ENABLE_INTERNAL_MESSAGE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res->fields['ENABLE_INTERNAL_MESSAGE'] == 0){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "pre";print_r($_POST);exit;
	foreach($_POST['INTERNAL_ID_SELECT'] as $INTERNAL_ID){
		$db->Execute("UPDATE Z_INTERNAL_EMAIL_RECEPTION SET DELETED = 1, VIWED = 1 WHERE INTERNAL_ID = '$INTERNAL_ID' AND PK_USER = '$_SESSION[PK_USER]' ");
		//echo "UPDATE Z_INTERNAL_EMAIL_RECEPTION SET DELETED = 1 WHERE INTERNAL_ID = '$INTERNAL_ID' AND PK_USER = '$_SESSION[PK_USER]' ";exit;
	}
	unset($_POST);
	header("location:my_mails?type".$_GET['type']);	
} 
if($_GET['act'] == 'del'){
	/*$db->Execute("UPDATE Z_INTERNAL_EMAIL_RECEPTION SET DELETED = 1, VIWED = 1 WHERE INTERNAL_ID = '$_GET[id]' AND PK_USER = '$_SESSION[PK_USER]' ");
	header("location:my_mails?type".$_GET['type']);	*/
} else if($_GET['act'] == 'del_draft'){
	//echo "DELETE FROM Z_INTERNAL_EMAIL WHERE PK_INTERNAL_EMAIL = '$_GET[id]' AND CREATED_BY = '$_SESSION[PK_USER]' ";exit;
	$db->Execute("DELETE FROM Z_INTERNAL_EMAIL WHERE PK_INTERNAL_EMAIL = '$_GET[id]' AND CREATED_BY = '$_SESSION[PK_USER]' ");
	header("location:my_mails?type".$_GET['type']);	
} else if($_GET['act'] == 'update'){
	if($_GET['iid'] == 1)
		$VIEW = 1;
	else
		$VIEW = 0;
	$INTERNAL_ID_ARR = explode(",",$_GET['id']);
	foreach($INTERNAL_ID_ARR as $INTERNAL_ID)
		$db->Execute("UPDATE Z_INTERNAL_EMAIL_RECEPTION SET VIWED = '$VIEW' WHERE INTERNAL_ID = '$INTERNAL_ID' AND PK_USER = '$_SESSION[PK_USER]' ");
		
	header("location:my_mails?type".$_GET['type']);	
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
		<?=MAIL_TITLE ?>
	</title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/pages/inbox.css">
	
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
	<? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
        <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=MAILBOX ?></h4>
                    </div>
                </div>
				<form name="form1" id="form1" method="post">
					<div class="row">
						<div class="col-lg-12">
							<div class="card">
								<div class="row">
									<div class="col-lg-3 col-md-4">
										<? include('mail_left_menu.php') ?>
									</div>
									<div class="col-lg-9 col-md-8 bg-light border-left">
										<div class="card-body">
											<div class="btn-group m-b-10 m-r-10" role="group" aria-label="Button group with nested dropdown">
												<!-- <button type="button"  class="btn btn-secondary font-18" onclick="delete_row('',2)" ><i class="mdi mdi-delete"></i></button> -->
											</div>
											
											<button type="button" class="btn btn-secondary m-r-10 m-b-10" onclick="doSearch()" ><i class="mdi mdi-reload font-18"></i></button>
											
											<div class="btn-group" role="group">
												<button id="btnGroupDrop11" type="button" class="btn m-b-10 btn-secondary font-18 dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <?=MORE?> </button>
												<div class="dropdown-menu" aria-labelledby="btnGroupDrop11"> 
													<a class="dropdown-item" href="javascript:void(0)" onclick="change_mail_status(1)" ><?=MARK_AS_ALL_READ?></a> 
													<a class="dropdown-item" href="javascript:void(0)" onclick="change_mail_status(2)" ><?=MARK_AS_ALL_UNREAD?></a> 
												</div>
											</div>
										</div>
										<div class="card-body p-t-0">
											<div class="card b-all shadow-none">
												<div class="inbox-center table-responsive">
													<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" <? if($_GET['type'] == 'sent' || $_GET['type'] == 'draft') { ?> url="grid_my_mails?type=<?=$_GET['type']?>" <? } else { ?> url="grid_mails.php?type=<?=$_GET['type']?>" <? } ?> toolbar="#tb" pagination="true" pageSize = 25 nowrap="false" autoRowHeight="true" >
														<thead>
															<tr>
																<th field="PK_INTERNAL_EMAIL" width="150px" hidden="true" sortable="true" ></th>
																<th field="INTERNAL_ID" width="150px" hidden="true" sortable="true" ></th>
																<th field="SELECT" width="60px"  sortable="false" >
																	<input type="checkbox" id="SELECT_ALL" onclick="select_all()" style="float:left;">
																</th>
																<? if($_GET['type'] == 'sent' || $_GET['type'] == 'draft') { ?>
																<th field="SENT_TO" width="200px" align="left" sortable="true" ><?=TO_1?></th>
																<? } else { ?>
																<th field="NAME" width="200px" align="left" sortable="true" ><?=FROM?></th>
																<? } ?>
																
																<th field="SUBJECT" width="550px" align="left" sortable="true" ><?=SUBJECT?></th>
																<th field="ATTACHMENT" width="50px" align="center" sortable="true" >&nbsp;</th>
																<th field="CREATED_ON" width="150px" align="center" sortable="true" ><?=DATE_TIME ?></th>
																<!-- <th field="ACTION" width="75px" align="left" sortable="false" ></th>-->
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
				</form>
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
							<?=DELETE_MESSAGE_MAIL ?>
							<input type="hidden" id="DELETE_ID" value="0" />
							<input type="hidden" id="DELETE_TYPE" value="0" />
							
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
				SEARCH  : $('#SEARCH').val(),
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
					if(field != 'ACTION' && field != 'SELECT' ){
						var selected_row = $('#tt').datagrid('getSelected');
						
						<? if($_GET['type'] == 'draft') { ?>
							window.location.href='compose_mail?type=<?=$_GET['type']?>&id='+selected_row.PK_INTERNAL_EMAIL;
						<? } else { ?>
							window.location.href='email?type=<?=$_GET['type']?>&id='+selected_row.INTERNAL_ID;
						<? } ?>
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
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
			$("#DELETE_TYPE").val(type)
		});
	}
	function conf_delete(val,id){
		if(val == 1){
			if($("#DELETE_TYPE").val() == 1)
				window.location.href = 'my_mails.php?type=<?=$_GET['type']?>&act=del&id='+$("#DELETE_ID").val();	
			else if($("#DELETE_TYPE").val() == 3)
				window.location.href = 'my_mails.php?type=<?=$_GET['type']?>&act=del_draft&id='+$("#DELETE_ID").val();
			else
				document.form1.submit();
		} else
			$("#deleteModal").modal("hide");
	}
	
	function star(id,div_id){
		jQuery(document).ready(function($) { 
			var data  = 'id='+id;
			var value = $.ajax({
				url: "../school/set_stared",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					//alert(data)
					document.getElementById('star_id_'+div_id).style.color = data;
				}		
			}).responseText;
		});
	}
	function select_all(){
		var str = '';
		if(document.getElementById('SELECT_ALL').checked == true)
			str = true;
		else
			str = false;
		var chk = document.getElementsByName("INTERNAL_ID_SELECT[]");
		for(var i = 0 ; i < chk.length ; i++)
			chk[i].checked = str;
	}
	
	function change_mail_status(type){
		var str = '';
		var chk = document.getElementsByName("INTERNAL_ID_SELECT[]");
		for(var i = 0 ; i < chk.length ; i++) {
			if(chk[i].checked == true) {
				if(str != '')
					str += ',';
				str += chk[i].value
			}
		}
		window.location.href = 'my_mails.php?type=<?=$_GET['type']?>&act=update&iid='+type+'&id='+str;
	}
	</script>
	
</body>

</html>