<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
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
	$PK_COURSE_OFFERINGS = implode(",",$_POST['PK_COURSE_OFFERING']);
	
	$PK_COURSE_OFFERING_ARRAY = array();
	$res = $db->Execute("SELECT PK_COURSE_OFFERING, CO_EXTERNAL_ID FROM S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING IN ($PK_COURSE_OFFERINGS) "); 
	while (!$res->EOF) {
		$PK_COURSE_OFFERING = $res->fields['PK_COURSE_OFFERING'];
		$CO_EXTERNAL_ID 	= $res->fields['CO_EXTERNAL_ID'];
		
		if(empty($PK_COURSE_OFFERING_ARRAY) || $CO_EXTERNAL_ID == ''){
			$PK_COURSE_OFFERING_ARRAY[] = $PK_COURSE_OFFERING;
		} else {
			$temp = implode(",",$PK_COURSE_OFFERING_ARRAY);
			$res1 = $db->Execute("SELECT PK_COURSE_OFFERING FROM S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING IN ($temp) AND CO_EXTERNAL_ID = '$CO_EXTERNAL_ID' ");
			if($res1->RecordCount() == 0)
				$PK_COURSE_OFFERING_ARRAY[] = $PK_COURSE_OFFERING;
		}
		
		$res->MoveNext();
	}
	
	foreach($PK_COURSE_OFFERING_ARRAY as $PK_COURSE_OFFERING){
		create_course_offering($PK_COURSE_OFFERING,$_SESSION['PK_ACCOUNT'],$RAND_ID);
	}
	header("location:send_course_offering_canvas_result?id=".$RAND_ID);
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
	<title><?=MNU_SEND_COURSE_OFFERING ?> | <?=$title?></title>
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
                    <div class="col-md-2 align-self-center" >
                        <h4 class="text-themecolor"><?=MNU_SEND_COURSE_OFFERING?> </h4>
                    </div>
				
					<div class="col-md-2 align-self-center text-right" >
						<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="doSearch()" >
							<? $res_type = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" ><?=$res_type->fields['CAMPUS_CODE']?></option>
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
					
					<div class="col-md-4 align-self-center text-right" >
                       <!-- <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome;" onkeypress="search(event)">-->
					   
					   <button type="button" onclick="window.location.href='send_course_offering_canvas_result_excel'" name="btn" class="btn waves-effect waves-light btn-info"  ><?=EXPORT_TO_EXCEL?></button>
												
						<button type="button" onclick="window.location.href='send_course_offering_canvas_result_pdf'" name="btn" class="btn waves-effect waves-light btn-info"  ><?=EXPORT_TO_PDF?></button>
						
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
											<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_send_course_offering_canvas"
											toolbar="#tb" pagination="true" pageSize = 25 >
												<thead>
													<tr>
														<th field="PK_COURSE_OFFERING" width="150px" hidden="true" sortable="true" ></th>
														<th field="TERM_BEGIN_DATE" width="100px" align="left" sortable="true" ><?=TERM?></th>
														<th field="COURSE_CODE" width="150px" align="left" sortable="true" ><?=COURSE_CODE?></th>
														<th field="LMS_CODE" width="150px" align="left" sortable="true" ><?=LMS_CODE?></th>
														<th field="SESSION" width="100px" align="left" sortable="true" ><?=SESSION?></th>
														<th field="INSTRUCTOR_NAME" width="200px" align="left" sortable="true" ><?=INSTRUCTOR?></th>
														<!--<th field="ROOM_NO" width="150px" align="left" sortable="true" ><?=ROOM?></th>-->
														<th field="CAMPUS_CODE" width="250px" align="left" sortable="true" ><?=CAMPUS?></th>
														<!--<th field="COURSE_OFFERING_STATUS" width="100px" align="left" sortable="true" ><?=COURSE_OFFERING_STATUS?></th>-->
														
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