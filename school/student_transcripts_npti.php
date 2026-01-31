<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//print_r($_POST);exit;
		$stud_id = "";
		foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT) {
			if($stud_id != '')
				$stud_id .= ',';
			$stud_id .= $_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT];
		}
		
		//http://localhost/UAT/school/student_transcript_pdf?id=175&uno=0&exclude_tc=&inc_att=1&zip=0
		header("location:student_transcript_pdf_npti?id=".$stud_id.'&uno=0&exclude_tc='.$_POST['EXCLUDE_TRANSFERS_COURSE'].'&inc_att='.$_POST['DISPLAY_ATTENDNACE']);	
		exit;
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
	<title><?=STUDENT_TRANSCRIPT?>-NPTI | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">

	<style>
		li > a > label{position: unset !important;}
		.dropdown-menu>li>a { white-space: nowrap; } 
		.lds-ring {
			position: absolute;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			margin: auto;
			width: 64px;
			height: 64px;
		}

		.lds-ring div {
			box-sizing: border-box;
			display: block;
			position: absolute;
			width: 51px;
			height: 51px;
			margin: 6px;
			border: 6px solid #0066ac;
			border-radius: 50%;
			animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
			border-color: #007bff transparent transparent transparent;
		}

		.lds-ring div:nth-child(1) {
			animation-delay: -0.45s;
		}

		.lds-ring div:nth-child(2) {
			animation-delay: -0.3s;
		}

		.lds-ring div:nth-child(3) {
			animation-delay: -0.15s;
		}

		@keyframes lds-ring {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}

		#loaders {
			position: fixed;
			width: 100%;
			z-index: 9999;
			bottom: 0;
			background-color: #2c3e50;
			display: block;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			opacity: 0.6;
			display: none;
		}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
   <div id="loaders" style="display: none;">
		<div class="lds-ring">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
		</div>
	</div>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=STUDENT_TRANSCRIPT?>-NPTI
						</h4>
                    </div>
                </div>
				
				<div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels" method="post" name="form1" id="form1" >
										<div class="row" style="padding-bottom:10px;" >
										<div class="col-md-2 " id="PK_CAMPUS_DIV"   >
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
												<? $res_type = $db->Execute("select CAMPUS_CODE, PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2 ">
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2 ">
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
									</div>
									<div class="row">
										
										<div class="col-md-2 ">
											<select id="PK_COURSE" name="PK_COURSE[]" multiple class="form-control" onchange="get_course_offering(this.value);clear_search()" >
												<? 
												$res_type = $db->Execute("select PK_COURSE, COURSE_CODE, TRANSCRIPT_CODE, COURSE_DESCRIPTION from S_COURSE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by COURSE_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_COURSE']?>" ><?=$res_type->fields['COURSE_CODE'].' - '.$res_type->fields['TRANSCRIPT_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} 
												 ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_COURSE_OFFERING_DIV" >
											<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control" >
												<option value=""><?=COURSE_OFFERING_PAGE_TITLE?></option>
											</select>
										</div>
										
										<div class="col-md-2 align-self-center" id="DISPLAY_ATTENDNACE_div" >
											<select id="DISPLAY_ATTENDNACE" name="DISPLAY_ATTENDNACE" class="form-control" >
												<option value="1" ><?=DISPLAY_ATTENDNACE?></option>
												<option value="2" >Do Not Display Attendance</option>
											</select>
										</div>									
										
										<div class="col-md-2 align-self-center " >
											<div class="custom-control custom-checkbox mr-sm-12">
												<input type="checkbox" class="custom-control-input" id="EXCLUDE_TRANSFERS_COURSE" name="EXCLUDE_TRANSFERS_COURSE" value="1" <? if($EXCLUDE_TRANSFERS_COURSE == 1) echo "checked"; ?> >
												<label class="custom-control-label" for="EXCLUDE_TRANSFERS_COURSE" ><?=EXCLUDE_TRANSFERS_COURSE?></label>
											</div>
										</div>
										
										<div class="col-md-2 align-self-center " id="INCLUDE_INSTRUCTOR_div" style="display:none" >
											<div class="custom-control custom-checkbox mr-sm-12">
												<input type="checkbox" class="custom-control-input" id="INCLUDE_INSTRUCTOR" name="INCLUDE_INSTRUCTOR" value="1" <? if($INCLUDE_INSTRUCTOR == 1) echo "checked"; ?> >
												<label class="custom-control-label" for="INCLUDE_INSTRUCTOR" ><?=INCLUDE_INSTRUCTOR?></label>
											</div>
										</div>
										
									</div>
									
									<!-- <div class="row" style="margin-top:10px">
										
									</div> -->
									<br />
									<div class="row"> 	
										<div class="col-md-2 mt-2 " id="SEARCH_TXT_DIV" style="display:block;">
											<input type="text" class="form-control" id="SEARCH_TXT" name="SEARCH_TXT" placeholder="&#xF002; <?= SEARCH ?>" style="font-family: FontAwesome;" onkeypress="do_search(event)">
										</div>										
										<div class="col-md-3 align-self-center ">
											<button type="button" onclick="search()" class="btn waves-effect waves-light btn-info"><?=SEARCH?></button>
										
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info" id="btn_1" style="display:none" ><?=PDF?></button>
											<!-- <button type="button" onclick="submit_form(3)" class="btn waves-effect waves-light btn-info" id="btn_2" style="display:none" ><?=ZIP?></button> -->											
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>

									</div>										
									<br />
									<div id="student_div" >
											<? 
											$_REQUEST['show_check'] 	= 1;
											$_REQUEST['show_count'] 	= 1;											
											?>
											<div class="col-md-12" style="text-align: right;padding-top: 8px;padding-bottom: 8px;font-weight: 500;">
												<?=TOTAL_COUNT.': '; ?><span id="TOTAL_COUNT" ></span>
												<br /><?=SELECTED_COUNT.': ' ?><span id="SELECTED_COUNT"></span>
                                            </div>

											<div class="col-md-12">
												<? if($_SESSION['SORT_FIELD'] != '')
													$sort = 'sortName = "'.$_SESSION['SORT_FIELD'].'" sortOrder="'.$_SESSION['SORT_ORDER'].'" ';
												else
													$sort = '' ?>
												<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" nowrap="false" autoRowHeight="true" url="grid_transcript_report_npti"
												toolbar="#tb" pagination="true" loadMsg="Processing, please wait..."  pageNumber="<?=$_SESSION['PAGE']?>" pageSize="<?=$_SESSION['rows']?>" <?=$sort?> data-options="
												
													onClickCell: function(rowIndex, field, value){
														$('#tt').datagrid('selectRow',rowIndex);
														
													},
													view: $.extend(true,{},$.fn.datagrid.defaults.view,{
														onAfterRender: function(target){
															$.fn.datagrid.defaults.view.onAfterRender.call(this,target);
															$('.datagrid-header-inner').width('100%') 
															$('.datagrid-btable').width('100%') 
															$('.datagrid-body').css({'overflow-y': 'hidden'});

														
															var myData = $('#tt').datagrid('getData');
															var total_record = JSON.stringify(myData.total);
															document.getElementById('TOTAL_COUNT').innerText = total_record;
															
														}
													})
												
					 							" >
										
												<thead>
													<tr>
														<th field="SELECT" width="25px" sortable="false" >
															<input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" >
														</th>
														<th field="STU_NAME" width="280px" align="left" sortable="true" ><?=STUDENT?></th> 
														<th field="STUDENT_ID" width="180px" align="left" sortable="true" ><?=STUDENT_ID?></th>
														<th field="CAMPUS_CODE" width="150px" align="left"  sortable="false"><?=CAMPUS?></th> 
														<th field="BEGIN_DATE_1" width="150px" align="left" sortable="true" ><?=FIRST_TERM?></th> 
														<th field="CODE" width="200px" align="left" sortable="true" ><?=PROGRAM?></th>
														<th field="STUDENT_STATUS" width="130px" align="left" sortable="true" ><?=STATUS?></th>
														<th field="STUDENT_GROUP" width="250px" align="left" sortable="true" ><?=STUDENT_GROUP?></th>
														
													</tr>
												</thead>
											</table>
										</div>									
                                </form>
                            </div>
                        </div>
					</div>
				</div>
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>
	
	<!-- <script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script> -->

	<!-- DIAM-1463 -->
	<!-- <script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" /> -->
	<!-- DIAM-1463 -->
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script type="text/javascript" src="../backend_assets/dist/js/jquery.easyui.min.js"></script>
	<script src="../backend_assets/dist/js/jquery-ui.js"></script>

	<script type="text/javascript">
		var form1 = new Validation('form1');	
		function submit_form(val){
			document.getElementById('FORMAT').value = val
			document.form1.submit();
		}
	
		function get_course_offering(val){
			var set_notification=false; // DIAM-1422 -->
			jQuery(document).ready(function($) { 
				var data  = 'val='+$('#PK_COURSE').val()+'&multiple=0';
				var value = $.ajax({
					url: "ajax_get_course_offering",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
						document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
						document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"
						$("#PK_COURSE_OFFERING option[value='']").remove();
						
						document.getElementById('PK_COURSE_OFFERING').setAttribute("onchange", "search()");
						
						$('#PK_COURSE_OFFERING').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
							nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
							numberDisplayed: 2,
							nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
						});
						set_notification=true; // DIAM-1422 -->
					}		
				}).responseText;
			});
		}

		function clear_search(){
			//document.getElementById('student_div').innerHTML = '';
			show_btn()
		}
		
		/* DIAM-1003 */
		function do_search(e) {
			if (e.keyCode == 13) {
				search();
			}
		}
		/*  DIAM-1003 */
		function search(){

			jQuery(document).ready(function($) {				

				$('#tt').datagrid('load',{
					PK_CAMPUS				: $('#PK_CAMPUS').val(),
					PK_STUDENT_GROUP  		: $('#PK_STUDENT_GROUP').val(),
					PK_TERM_MASTER  		: $('#PK_TERM_MASTER').val(),
					PK_CAMPUS_PROGRAM  		: $('#PK_CAMPUS_PROGRAM').val(),
					PK_STUDENT_STATUS  	    : $('#PK_STUDENT_STATUS').val(),
					PK_COURSE  			    : $('#PK_COURSE').val(),
					PK_COURSE_OFFERING		: $('#PK_COURSE_OFFERING').val(),
					show_check 				: 1,
					show_count 				: 1,
					SEARCH_TXT				: $('#SEARCH_TXT').val()

				});
			});	
}
		/*function search(){
			set_notification=false; // DIAM-1422 -->
			document.getElementById('loaders').style.display = 'block';
			jQuery(document).ready(function($) {
				var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_COURSE='+$('#PK_COURSE').val()+'&PK_COURSE_OFFERING='+$('#PK_COURSE_OFFERING').val()+'&show_check=1&show_count=1&group_by=sid&ENROLLMENT=1&SEARCH_TXT=' + $('#SEARCH_TXT').val();			
		
					var value = $.ajax({
						url: "ajax_search_student_for_reports",	
						type: "POST",		 
						data: data,		
						async: true,
						cache: false,
						success: function (data) {	
							document.getElementById('student_div').innerHTML = data;
							set_notification=true; // DIAM-1422 -->
							document.getElementById('loaders').style.display = 'none';
							document.getElementById('SEARCH_TXT_DIV').style.display = 'block'; 
							show_btn()
						}		
					}).responseText;
				
			});
		}*/
		function fun_select_all(){
			var str = '';
			if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
				str = true;
			else
				str = false;
				
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				PK_STUDENT_ENROLLMENT[i].checked = str
			}
			get_count()
		}
		function get_count(){
			var tot = 0
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true)
					tot++;
			}
			document.getElementById('SELECTED_COUNT').innerHTML = tot
			show_btn()
		}
		
		function show_btn(){
			
			var flag = 0;
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true) {
					flag++;
					break;
				}
			}
			
			if(flag == 1) {
				document.getElementById('btn_1').style.display = 'inline';
				document.getElementById('btn_2').style.display = 'inline';
			} else {
				document.getElementById('btn_1').style.display = 'none';
				document.getElementById('btn_2').style.display = 'none';
			}
		}
		
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_COURSE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_CODE?>',
			nonSelectedText: '<?=COURSE_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_CODE?> selected'
		});
		$('#PK_STUDENT_GROUP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=GROUP_CODE?>',
			nonSelectedText: '<?=GROUP_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=GROUP_CODE?> selected'
		});
		$('#PK_TERM_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FIRST_TERM?>',
			nonSelectedText: '<?=FIRST_TERM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=FIRST_TERM?> selected'
		});
		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROGRAM?>',
			nonSelectedText: '<?=PROGRAM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STATUS?>',
			nonSelectedText: '<?=STATUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STATUS?> selected'
		});
		$('#PK_COURSE_OFFERING').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
			nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
		});
		
		/* Ticket # 1603 */
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		/* Ticket # 1603 */

		/* DIAM-1463 */
		$('#START_DATE').datepicker({
		autoclose: true,
		todayHighlight: true,
		orientation: "bottom auto"
		});

		$('#END_DATE').datepicker({
		autoclose: true,
		todayHighlight: true,
		orientation: "bottom auto"
		});
		/* DIAM-1463 */

	});
	</script>
</body>

</html>
