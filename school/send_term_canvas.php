<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/term_master.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
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
	$RAND_ID = time().'_'.$_SESSION['PK_USER'];
	foreach($_POST['PK_TERM_MASTER'] as $PK_TERM_MASTER){
		create_term($PK_TERM_MASTER,$_SESSION['PK_ACCOUNT'],$RAND_ID);
	}
	header("location:send_term_canvas_result?id=".$RAND_ID);
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
	<title><?=MNU_SEND_TERM ?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<style>
		li > a > label{position: unset !important;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-7 align-self-center" >
                        <h4 class="text-themecolor"><?=MNU_SEND_TERM?> </h4>
                    </div>
				
					<div class="col-md-4 align-self-center text-right" >
                       <!-- <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome;" onkeypress="search(event)">-->
					   
					   <button type="button" onclick="window.location.href='send_term_canvas_result_excel'" name="btn" class="btn waves-effect waves-light btn-info"  ><?=EXPORT_TO_EXCEL?></button>
												
						<button type="button" onclick="window.location.href='send_term_canvas_result_pdf'" name="btn" class="btn waves-effect waves-light btn-info"  ><?=EXPORT_TO_PDF?></button>
						
						<button type="button" onclick="validate_form()" id="SEND_BTN" class="btn waves-effect waves-light btn-info" style="display:none" ><?=SEND?></button>
					
                    </div>
                </div>
				<form class="floating-labels " method="post" name="form1" id="form1" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-12">
											<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_send_term_canvas"
											toolbar="#tb" pagination="true" pageSize = 25 nowrap="false" autoRowHeight="true" >
												<thead>
													<tr>
														<th field="PK_TERM_MASTER" width="150px" hidden="true" sortable="true" ></th>
														<th field="BEGIN_DATE" width="150px" align="left" sortable="true" ><?=BEGIN_DATE?></th>
														<th field="END_DATE" width="150px" align="left" sortable="true" ><?=END_DATE?></th>
														<th field="TERM_DESCRIPTION" width="150px" align="left" sortable="true" ><?=DESCRIPTION?></th>
														<th field="SIS_ID" width="150px" align="left" sortable="true" ><?=SIS_ID?></th>
														
														<th field="SENT" width="50px" align="left" sortable="true" ><?=SENT?></th>
														<th field="SENT_ON" width="130px" align="left" sortable="true" ><?=SENT_ON?></th>
														<th field="SENT_BY" width="150px" align="left" sortable="true" ><?=SENT_BY?></th>
														<th field="MESSAGE" width="200px" align="left" sortable="true" ><?=MESSAGE?></th>
														
														<th field="ACTION" width="40px" align="left" sortable="false" >
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
	<script type="text/javascript">
	function doSearch(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid('load',{
				PK_COURSE_OFFERING  : $('#PK_COURSE_OFFERING').val(),
				PK_TERM_MASTER  	: $('#PK_TERM_MASTER').val(),
				PK_CAMPUS  			: $('#PK_CAMPUS').val(),
				SEARCH  			: $('#SEARCH').val(),
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
		var CHK_PK_TERM_MASTER = document.getElementsByName('PK_TERM_MASTER[]')
		for(var i = 0 ; i < CHK_PK_TERM_MASTER.length ; i++){
			CHK_PK_TERM_MASTER[i].checked = str
			flag = 1;
		}
		show_btn()
	}
	
	function show_btn(){
		var flag = 0;
		var CHK_PK_TERM_MASTER = document.getElementsByName('PK_TERM_MASTER[]')
		for(var i = 0 ; i < CHK_PK_TERM_MASTER.length ; i++){
			if(CHK_PK_TERM_MASTER[i].checked == true) {
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
		var CHK_PK_TERM_MASTER = document.getElementsByName('PK_TERM_MASTER[]')
		for(var i = 0 ; i < CHK_PK_TERM_MASTER.length ; i++){
			if(CHK_PK_TERM_MASTER[i].checked == true) {
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
</body>

</html>