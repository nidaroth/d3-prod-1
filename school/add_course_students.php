<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("function_add_student_to_course_offering.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){  
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	add_student_to_course_offering($_GET['id'],$_POST['PK_STUDENT_ENROLLMENT']);
	 ?>
	<script type="text/javascript">window.opener.refresh_win(this)</script>
<? } ?>
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
	<style>
		li > a > label{position: unset !important;}
		/* Ticket # 1827 */
		.dropdown-menu>li>a { white-space: nowrap; } 
		.option_red > a > label{color:red !important}
		/* Ticket # 1827 */
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper1" id="page_wrapper" >
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=COURSE_OFFERING_PAGE_TITLE.' - '.ADD_STUDENTS?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row" style="padding-bottom:10px;" >
										<!-- DIAM - 79, Add PK CAMPUS Filter -->
										 <!-- <div class="col-md-2 ">
												<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control">
													<?
													//$res_type = $db->Execute("select PK_CAMPUS,CAMPUS_CODE, ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CAMPUS_CODE ASC");
													//while (!$res_type->EOF) { ?>
														<option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($PK_CAMPUS == $res_type->fields['PK_CAMPUS']) echo "selected"; ?> <? if ($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['CAMPUS_CODE'] ?></option>
													<? //$res_type->MoveNext();
													//} ?>
												</select>
										 </div> -->
										<!-- End DIAM - 79, Add PK CAMPUS Filter -->
										<div class="col-md-2 ">
											<select id="PK_COURSE" name="PK_COURSE[]" multiple class="form-control" onchange="get_course_offering(this.value)" >
												<? $res_type = $db->Execute("select PK_COURSE,COURSE_CODE,COURSE_DESCRIPTION from S_COURSE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by COURSE_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_COURSE']?>" ><?=$res_type->fields['COURSE_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_COURSE_OFFERING_DIV" >
											<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING[]" multiple class="form-control" >
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
									</div>
									<div class="row">
										<div class="col-md-2 ">
											<input id="STU_NAME" name="STU_NAME" value="" type="text" class="form-control" placeholder="<?=STUDENT?>" onkeypress="do_search(event)" > <!-- Ticket #1793 -->
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control">
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2 align-self-center ">
											<button type="button" class="btn waves-effect waves-light btn-dark" onclick="search()" ><?=SEARCH?></button>
										</div>
									</div>
									<br />
									<div id="student_div" >
                                        
									</div>
									
									<div class="row">
										<div class="col-sm-6 form-group">
										</div>
										<div class="col-sm-6 form-group">
											<button type="button" onclick="check_class_size()" class="btn waves-effect waves-light btn-info" id="btn" style="display:none" ><?=ASSIGN?></button> <!-- Ticket # 1325 -->
										</div>
									</div>
											
                                </form>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
		
		<!-- Ticket # 1325 -->
		<div class="modal" id="classSizeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="row">
							<div class="col-md-12 align-self-center"><?=CLASS_SIZE_ERROR?></div>
						</div>
						<br />
						<div class="row" id="MAX_CLASS_SIZE_DIV" >
							<div class="col-md-4 align-self-center"><?=MAX_CLASS_SIZE?></div>
							<div class="col-md-4 align-self-center" id="CLASS_SIZE_DIV" ></div>
						</div>
						
						<div class="row" id="MAX_ROOM_SIZE_DIV" >
							<div class="col-md-4 align-self-center"><?=MAX_ROOM_SIZE?></div>
							<div class="col-md-4 align-self-center" id="ROOM_SIZE_DIV" ></div>
						</div>
						
						<div class="row">
							<div class="col-md-4 align-self-center"><?=CURRENT_CLASS_SIZE?></div>
							<div class="col-md-4 align-self-center" id="CURRENT_CLASS_SIZE_DIV" ></div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="confirm_class_size(1)" class="btn waves-effect waves-light btn-info"><?=ADD_TO_COURSE?></button>
						<button type="button" onclick="confirm_class_size(2)" class="btn waves-effect waves-light btn-info"><?=ADD_TO_WAITING_LIST ?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="confirm_class_size(0)" ><?=CANCEL?></button>
					</div>
				</div>
			</div>
		</div>
		<!-- Ticket # 1325 -->
		
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		/* Ticket # 1325 */
		function check_class_size(){
			jQuery(document).ready(function($) {
				var data  = 'PK_COURSE_OFFERING=<?=$_GET['id']?>';
				//alert(data)
				var value = $.ajax({
					url: "ajax_check_course_offering_class_size",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {
						data = data.split('|||');
						
						/*if(data[0] == 'b'){
							document.getElementById('CLASS_SIZE_DIV').innerHTML 		= data[1];
							document.getElementById('CURRENT_CLASS_SIZE_DIV').innerHTML = data[2];
							document.getElementById('MAX_CLASS_SIZE_DIV').style.display = 'flex';
							$("#classSizeModal").modal()
						} else {
							document.getElementById('MAX_CLASS_SIZE_DIV').style.display = 'none';
						}
						
						
						if(data[3] == 'b'){
							document.getElementById('ROOM_SIZE_DIV').innerHTML 			= data[4];
							document.getElementById('CURRENT_CLASS_SIZE_DIV').innerHTML = data[2];
							document.getElementById('MAX_ROOM_SIZE_DIV').style.display  = 'flex';
							$("#classSizeModal").modal()
						} else {
							document.getElementById('MAX_ROOM_SIZE_DIV').style.display = 'none';
						}*/
					
						document.getElementById('MAX_CLASS_SIZE_DIV').style.display = 'none';
						document.getElementById('MAX_ROOM_SIZE_DIV').style.display = 'none';
						
						var sel_count = 0;
						var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
						for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
							if(PK_STUDENT_ENROLLMENT[i].checked == true) {
								sel_count++;
							}
						}
						var max_class_size = parseInt(data[1])
						var cur_class_size = parseInt(data[2]) + sel_count
						
						var flag = 1;
						if(cur_class_size > max_class_size) {
							document.getElementById('CLASS_SIZE_DIV').innerHTML 		= data[1];
							document.getElementById('CURRENT_CLASS_SIZE_DIV').innerHTML = data[2];
							document.getElementById('MAX_CLASS_SIZE_DIV').style.display = 'flex';
							flag = 0;
						} 
						
						var max_room_size = parseInt(data[4])
						if(cur_class_size > max_room_size) {
							document.getElementById('ROOM_SIZE_DIV').innerHTML 			= data[4];
							document.getElementById('CURRENT_CLASS_SIZE_DIV').innerHTML = data[2];
							document.getElementById('MAX_ROOM_SIZE_DIV').style.display 	= 'flex';
							flag = 0;
						} 
						
						if(flag == 1) {
							document.form1.submit();
						} else 
							$("#classSizeModal").modal()
					}		
				}).responseText;
			});
		}
		
		function confirm_class_size(val){
			jQuery(document).ready(function($) {
				if(val == 0) {
					window.close()
				} else if(val == 2) {
					var eid = '';
					var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
					for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
						if(PK_STUDENT_ENROLLMENT[i].checked == true) {
							if(eid != '')
								eid += ','
							eid += PK_STUDENT_ENROLLMENT[i].value
						}
					}
					
					var data  = 'PK_COURSE_OFFERING=<?=$_GET['id']?>&eids='+eid;
					var value = $.ajax({
						url: "ajax_add_multiple_student_course_offering_waiting_list",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {
							alert('<?=ADDED_TO_WAITING_LIST?>')
							window.opener.refresh_win_wait_list(window)
						}		
					}).responseText;
				} else if(val == 1) {
					document.form1.submit();
				}
				
				$("#classSizeModal").modal("hide");
			});
		}
		/* Ticket # 1325 */
		
		function search(){
			jQuery(document).ready(function($) {
				var data  = 'STU_NAME='+$('#STU_NAME').val()+'&PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_COURSE_OFFERING1=<?=$_GET['id']?>'+'&PK_COURSE='+$('#PK_COURSE').val()+'&PK_COURSE_OFFERING='+$('#PK_COURSE_OFFERING').val()+'&type=add_course_stu&show_count=1'; // +'&PK_CAMPUS_COUR='+$('#PK_CAMPUS').val() - DIAM - 79, Add PK CAMPUS Filter
				var value = $.ajax({
					url: "ajax_search_student",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('student_div').innerHTML = data
					}		
				}).responseText;
			});
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
			
			var count1 = 0;
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true) {
					count1++;
				}
			}
			if(document.getElementById('SELECTED_COUNT_SPAN'))
				document.getElementById('SELECTED_COUNT_SPAN').innerHTML = count1
			
			if(flag == 1)
				document.getElementById('btn').style.display = 'block';
			else
				document.getElementById('btn').style.display = 'none';
			
		}
		
		function get_course_offering(val){
			jQuery(document).ready(function($) { 
				var data  = 'val='+$('#PK_COURSE').val()+'&multiple=0&show_more_detail=1';
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
						
						$('#PK_COURSE_OFFERING').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
							nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
							numberDisplayed: 2,
							nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
						});
					}		
				}).responseText;
			});
		}
		function get_course_offering_session(){
		}
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
			show_btn()
		}
		
		/* Ticket #1793 */
		jQuery(document).ready(function($) { 
			$(window).keydown(function(event){
				if(event.keyCode == 13) {
					event.preventDefault();
					search();
					return false;
				}
			});
		});	
		
		function do_search(e){
			if (e.keyCode == 13) {
				search();
			}
		}
		/* Ticket #1793 */
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
			nSelectedText: '<?=PROGRAM?> selected',
			minHeight: '1800px'
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

		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
	});
	
	/* Ticket # 1827 */
	jQuery(document).ready(function($) {
		set_height()
	});
	
	window.addEventListener('resize', function(event){
		set_height()
	});
	
	function set_height(){
		var h1 = Math.round((window.innerHeight * 0.92));
		document.getElementById('page_wrapper').style.minHeight = h1+"px"
		//alert(document.getElementById('page_wrapper').style.minHeight)
	
		var h = Math.round((window.innerHeight * 0.95));
		var cont1 = document.getElementsByClassName("multiselect-container")
		for(var i = 0 ; i < cont1.length ; i++)
			cont1[i].style.maxHeight = h+"px"; 
			
		//document.getElementsByClassName("multiselect-container").style.maxHeight = h+"px"; 
	}
	/* Ticket # 1827 */
	</script>
</body>

</html>