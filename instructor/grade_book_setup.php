<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/dashboard.php");
require_once("../language/instructor_grade_book_setup.php");
require_once("../language/course_offering.php");

//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$i = 0;
	$PK_COURSE_OFFERING = $_POST['PK_COURSE_OFFERING'];
	$CREATED_ON_HISTROY = date("Y-m-d H:i:s"); // DIAM-785
	foreach($_POST['GRADE_CUNT'] as $GRADE_CUNT){
		$COURSE_OFFERING_GRADE = array();
		$COURSE_OFFERING_GRADE['GRADE_ORDER'] 		 = $_POST['GRADE_ORDER'][$i]; //Ticket # 1437 
		$COURSE_OFFERING_GRADE['CODE'] 				 = $_POST['CODE'][$i];
		$COURSE_OFFERING_GRADE['DESCRIPTION'] 		 = $_POST['DESCRIPTION'][$i];
		$COURSE_OFFERING_GRADE['PK_GRADE_BOOK_TYPE'] = $_POST['PK_GRADE_BOOK_TYPE'][$i];
		$COURSE_OFFERING_GRADE['DATE'] 				 = $_POST['DATE'][$i];
		//$COURSE_OFFERING_GRADE['PERIOD'] 			 = $_POST['PERIOD'][$i];
		$COURSE_OFFERING_GRADE['POINTS'] 			 = $_POST['POINTS'][$i];
		$COURSE_OFFERING_GRADE['WEIGHT'] 			 = $_POST['WEIGHT'][$i];
		$COURSE_OFFERING_GRADE['WEIGHTED_POINTS'] 	 = $_POST['POINTS'][$i] * $_POST['WEIGHT'][$i];
		$COURSE_OFFERING_GRADE['SORT_ORDER'] 		 = $_POST['SORT_ORDER'][$i];
		
		if($COURSE_OFFERING_GRADE['DATE'] != '')
			$COURSE_OFFERING_GRADE['DATE'] = date("Y-m-d",strtotime($COURSE_OFFERING_GRADE['DATE']));
		else
			$COURSE_OFFERING_GRADE['DATE'] = '';

		if($_POST['PK_COURSE_OFFERING_GRADE'][$i] == ''){
			$COURSE_OFFERING_GRADE['PK_COURSE_OFFERING']  	= $PK_COURSE_OFFERING;
			$COURSE_OFFERING_GRADE['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
			$COURSE_OFFERING_GRADE['CREATED_BY']  			= $_SESSION['PK_USER'];
			$COURSE_OFFERING_GRADE['CREATED_ON']  			= date("Y-m-d H:i");
			db_perform('S_COURSE_OFFERING_GRADE', $COURSE_OFFERING_GRADE, 'insert');
			$PK_COURSE_OFFERING_GRADE = $db->insert_ID();
			
			//To Store S_COURSE_OFFERING_GRADE history start 27 june
			$COURSE_OFFERING_GRADE['PK_COURSE_OFFERING_GRADE'] = $PK_COURSE_OFFERING_GRADE;
			$COURSE_OFFERING_GRADE['CREATED_ON']  			= $CREATED_ON_HISTROY;
			db_perform('S_COURSE_OFFERING_GRADE_HISTROY', $COURSE_OFFERING_GRADE, 'insert');
			//To Store S_COURSE_OFFERING_GRADE history end 2 7 june

			$PK_COURSE_OFFERING_GRADE_ARR[] = $PK_COURSE_OFFERING_GRADE;
			
		} else {
			$PK_COURSE_OFFERING_GRADE = $_POST['PK_COURSE_OFFERING_GRADE'][$i];
			$COURSE_OFFERING_GRADE['EDITED_BY'] = $_SESSION['PK_USER'];
			$COURSE_OFFERING_GRADE['EDITED_ON'] = date("Y-m-d H:i");
			db_perform('S_COURSE_OFFERING_GRADE', $COURSE_OFFERING_GRADE, 'update'," PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");

			//To update S_COURSE_OFFERING_GRADE history start 27 june
			$COURSE_OFFERING_GRADE['PK_COURSE_OFFERING_GRADE'] = $PK_COURSE_OFFERING_GRADE;
			$COURSE_OFFERING_GRADE['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
			$COURSE_OFFERING_GRADE['PK_COURSE_OFFERING'] = $PK_COURSE_OFFERING;
			$COURSE_OFFERING_GRADE['CREATED_ON']  			= $CREATED_ON_HISTROY;
			db_perform('S_COURSE_OFFERING_GRADE_HISTROY', $COURSE_OFFERING_GRADE, 'insert');
			//To update S_COURSE_OFFERING_GRADE history end 27 june
			
			$PK_COURSE_OFFERING_GRADE_ARR[] = $PK_COURSE_OFFERING_GRADE;
		}
		
		$i++;
	}

	$recal_grade = 0;
	/* Ticket # 1437  */
	$cond = "";
	/*if(!empty($PK_COURSE_OFFERING_GRADE_ARR)) {
		$cond = " AND PK_COURSE_OFFERING_GRADE NOT IN (".implode(",",$PK_COURSE_OFFERING_GRADE_ARR).")";
	}
	$res_grade = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE FROM S_COURSE_OFFERING_GRADE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ");
	while (!$res_grade->EOF) {
		$PK_COURSE_OFFERING_GRADE = $res_grade->fields['PK_COURSE_OFFERING_GRADE'];
		
	// 	$db->Execute("DELETE from S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING='$PK_COURSE_OFFERING' AND PK_COURSE_OFFERING_GRADE='$PK_COURSE_OFFERING_GRADE'");
		
	// 	//delete grade from student
	// 	$db->Execute("DELETE from S_STUDENT_GRADE WHERE PK_ACCOUNT='$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING='$PK_COURSE_OFFERING' AND PK_COURSE_OFFERING_GRADE='$PK_COURSE_OFFERING_GRADE'");
		
		$recal_grade = 1;
		$res_grade->MoveNext();
	}*/
	/* Ticket # 1437  */
	////////////////////////////////
	
	//insert new grade to student
	$res_grade = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE FROM S_COURSE_OFFERING_GRADE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
	while (!$res_grade->EOF) {
		$PK_COURSE_OFFERING_GRADE = $res_grade->fields['PK_COURSE_OFFERING_GRADE'];
		
		$res_stu = $db->Execute("select PK_STUDENT_MASTER,PK_STUDENT_ENROLLMENT FROM S_STUDENT_COURSE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		while (!$res_stu->EOF) {
			$PK_STUDENT_MASTER 		= $res_stu->fields['PK_STUDENT_MASTER'];
			$PK_STUDENT_ENROLLMENT 	= $res_stu->fields['PK_STUDENT_ENROLLMENT'];
			
			$res_stu_grade = $db->Execute("select PK_STUDENT_GRADE FROM S_STUDENT_GRADE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' ");
			if($res_stu_grade->RecordCount() == 0) {
				$STUDENT_GRADE['PK_COURSE_OFFERING_GRADE'] 	= $PK_COURSE_OFFERING_GRADE;
				$STUDENT_GRADE['PK_COURSE_OFFERING']		= $PK_COURSE_OFFERING;
				$STUDENT_GRADE['PK_STUDENT_ENROLLMENT'] 	= $PK_STUDENT_ENROLLMENT;
				$STUDENT_GRADE['PK_STUDENT_MASTER'] 	 	= $PK_STUDENT_MASTER;
				$STUDENT_GRADE['PK_ACCOUNT'] 			 	= $_SESSION['PK_ACCOUNT'];
				$STUDENT_GRADE['CREATED_BY']  			 	= $_SESSION['PK_USER'];
				$STUDENT_GRADE['CREATED_ON']  			 	= date("Y-m-d H:i");
				db_perform('S_STUDENT_GRADE', $STUDENT_GRADE, 'insert');
				
				//To Store STUDENT_GRADE history start 27 june
				$PK_STUDENT_GRADE = $db->insert_ID();
				$STUDENT_GRADE['PK_STUDENT_GRADE'] = $PK_STUDENT_GRADE;
				$STUDENT_GRADE['CREATED_ON']  			 	= $CREATED_ON_HISTROY;
				db_perform('S_STUDENT_GRADE_HISTROY', $STUDENT_GRADE, 'insert');
				//To Store STUDENT_GRADE history end 27 june

				$recal_grade = 1;
			}
			
			$res_stu->MoveNext();
		}
		
		$res_grade->MoveNext();
	}
	
	if($recal_grade == 1) {
		require_once("../school/function_calc_student_grade.php"); 
		$res_stu = $db->Execute("select PK_STUDENT_COURSE, PK_STUDENT_MASTER FROM S_STUDENT_COURSE WHERE PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		while (!$res_stu->EOF) {
			$PK_STUDENT_COURSE = $res_stu->fields['PK_STUDENT_COURSE'];
			$PK_STUDENT_MASTER = $res_stu->fields['PK_STUDENT_MASTER'];
			
			$PK_STUDENT_GRADE 	= '';
			$POINTS 			= '';
			 $res_grade = $db->Execute("SELECT PK_COURSE_OFFERING_GRADE FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ORDER BY PK_COURSE_OFFERING_GRADE ASC ");
			while (!$res_grade->EOF) { 
				$PK_COURSE_OFFERING_GRADE = $res_grade->fields['PK_COURSE_OFFERING_GRADE']; 
				$res_stu_grade = $db->Execute("SELECT PK_STUDENT_GRADE,POINTS FROM S_STUDENT_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' "); 
				if($res_stu_grade->fields['POINTS'] != ''){
					if($PK_STUDENT_GRADE != '')
						$PK_STUDENT_GRADE .= ',';
					
					$PK_STUDENT_GRADE .= $res_stu_grade->fields['PK_STUDENT_GRADE'];
					
					if($POINTS != '')
						$POINTS .= ',';
					
					$POINTS .= $res_stu_grade->fields['POINTS'];
				}
				
				$res_grade->MoveNext();
			}
			
			calc_stu_grade($POINTS,$PK_STUDENT_GRADE,$PK_STUDENT_COURSE,$PK_STUDENT_MASTER,1);
			
			$res_stu->MoveNext();
		}
	}
	
	header("location:grade_book_setup?tm=".$_POST['PK_TERM_MASTER']."&co=".$_POST['PK_COURSE_OFFERING']); //<!--DIAM-785 -->
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
	<title><?=GRADE_BOOK_SETUP_PAGE_TITLE?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=GRADE_BOOK_SETUP_PAGE_TITLE?></h4>
                    </div>
                </div>				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
								<form class="floating-labels w-100 m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
									<div class="row">
										<div class="col-sm-3 pt-25">
											<div class="row">
												<div class="col-12 form-group">
													<!-- Ticket # 1437  -->
													<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control required-entry" onchange="get_course_offering(this.value)">
														<option value=""></option>
														<? $res_type = $db->Execute("select S_TERM_MASTER.PK_TERM_MASTER, TERM_DESCRIPTION, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00', DATE_FORMAT( S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, IF(S_TERM_MASTER.END_DATE != '0000-00-00', DATE_FORMAT( S_TERM_MASTER.END_DATE,'%m/%d/%Y'),'') AS TERM_END_DATE from S_COURSE_OFFERING LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING, S_TERM_MASTER WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND  S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER GROUP BY S_TERM_MASTER.PK_TERM_MASTER ORDER BY BEGIN_DATE DESC");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_TERM_MASTER']?>"  <? if($_GET['tm'] == $res_type->fields['PK_TERM_MASTER']) echo "selected"; ?> ><?=$res_type->fields['TERM_BEGIN_DATE'].' - '.$res_type->fields['TERM_END_DATE'].' - '.$res_type->fields['TERM_DESCRIPTION'] ?></option> <!--DIAM-785 -->
														<?	$res_type->MoveNext();
														} ?>
													</select>
													<!-- Ticket # 1437  -->
													<span class="bar"></span> 
													<label for="PK_TERM_MASTER"><?=SELECT_TERM?></label>
												</div>
												<div class="col-12 form-group" id="PK_COURSE_OFFERING_LABEL" >
													<div id="PK_COURSE_OFFERING_DIV" >
														<?php if(isset($_GET['co']) && !empty($_GET['co'])){ //<!--DIAM-785 -->
															$_REQUEST['val'] = $_GET['tm']; 
															$_REQUEST['def'] 	= $_GET['co']; 
															include("ajax_get_course_offering.php");
															?>
															
														<?php }else{ ?> <!--DIAM-785 -->
														<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control required-entry" >
															<option value=""></option>
														</select>
														<? } ?>
														<!--DIAM-785 -->
													</div>
													<span class="bar"></span> 
													<label for="PK_COURSE_OFFERING"><?=SELECT_COURSE_OFFERING?></label>
												</div>
												<div class="col-12 form-group text-right">
													<button type="button" onclick="get_grade_book()" class="btn waves-effect waves-light btn-info"><?=SHOW?></button>
												</div>
											</div>
											<div id="course_details">
											</div>
										</div>
										<div class="col-sm-9 pt-25 theme-v-border" >
											<div id="GRADE_BOOK_DIV">
											</div>
										</div>
									</div>
									<input type="hidden" name="COMPLETE" id="COMPLETE" value="1" />
								</form>
                            </div>
                        </div>
                    </div>
				</div>				
            </div>
			
			<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="exampleModalLabel1"><?=DELETE_CONFIRMATION?></h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body">
							<div class="form-group" id="delete_message" ></div>
							<input type="hidden" id="DELETE_ID" value="0" />
							<input type="hidden" id="DELETE_TYPE" value="0" />
						</div>
						<div class="modal-footer">
							<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
							<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" ><?=NO?></button>
						</div>
					</div>
				</div>
			</div>
			<!--DIAM-785 -->											
			<div class="modal" id="deleteModal_grade" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="exampleModalLabel1"><?= DELETE_CONFIRMATION ?></h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body">
							<div class="form-group" id="deleteModal_grade_message"></div> 
						</div>
						<div class="modal-footer">
							<button type="button" onclick="confirm_delete_grade()" class="btn waves-effect waves-light btn-info"><?= YES ?></button>
							<button type="button" class="btn waves-effect waves-light btn-dark" onclick="grade_to_be_deleted = false;jQuery('#deleteModal_grade').modal('hide');"><?= NO ?></button>
						</div>
					</div>
				</div>
			</div>

			<div class="modal" id="deleteModal_grade_after_msg" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						 
						<div class="modal-body">
							<div class="form-group text-center mt-5 font-weight-bold" id="deleteModal_grade_after_msg_message"  data-backdrop="static" data-keyboard="false" style="color
							: green">
								Grade book setup saved successfully !
							</div> 
						</div>
						<div class="modal-footer"> 
							<button type="button" class="btn waves-effect waves-light btn-info" onclick="jQuery('#deleteModal_grade_after_msg').modal('hide');document.getElementById('SAVE_BTN').click();">Save</button>
						</div>
					</div>
				</div>
			</div>
			<!--DIAM-785 -->
			
        </div>
        <? require_once("footer.php"); ?>		
    </div>
    <? require_once("js.php"); ?>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<!--DIAM-785 -->
	<?php if(isset($_GET['tm']) && !empty($_GET['tm'])){ ?>
	<script type="text/javascript">													
	jQuery(document).ready(function($) {  
		get_grade_book();
	});
	</script>
	<?php } ?>	
	<!--DIAM-785 -->
	<script type="text/javascript">
		function get_course_offering(val){
			jQuery(document).ready(function($) { 
				var data  = 'val='+val;
				var value = $.ajax({
					url: "ajax_get_course_offering",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
						document.getElementById('PK_COURSE_OFFERING_LABEL').classList.add("focused");
						
					}		
				}).responseText;
			});
		}
		function get_grade_book(){
			jQuery(document).ready(function($) { 
				if(document.getElementById('PK_COURSE_OFFERING').value != '') {
					var data  = 'val='+document.getElementById('PK_COURSE_OFFERING').value;
					//alert(data)
					var value = $.ajax({
						url: "ajax_get_grade_book",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('GRADE_BOOK_DIV').innerHTML = data;
							jQuery('.date').datepicker({
							todayHighlight: true,
							orientation: "bottom auto"
						}); // DIAM-785 -->
							
						}		
					}).responseText;
				}
			});
		}
		function get_schedule(val){
			document.getElementById('GRADE_BOOK_DIV').innerHTML = ''
		}
		
		var grade_cunt = '<?=$grade_cunt?>';
		function add_grade(){
			jQuery(document).ready(function($) {
				var data = 'grade_cunt='+grade_cunt+'&ACTION=<?=$_GET['act']?>';
				var value = $.ajax({
					url: "../school/ajax_course_offering_grade",	
					type: "POST",
					data: data,		
					async: false,
					cache :false,
					success: function (data) {
						$('#grade_table tbody').append(data);
						grade_cunt++;
						
						jQuery('.date').datepicker({
							todayHighlight: true,
							orientation: "bottom auto"
						});
					}		
				}).responseText;
			});
		}
		var import_disable=false; // DIAM-785 -->

		function import_grade(PK_COURSE){
			if(import_disable==false){ // DIAM-785 -->
			jQuery(document).ready(function($) {
				var data = 'grade_cunt='+grade_cunt+'&PK_COURSE='+PK_COURSE;
				var value = $.ajax({
					url: "../school/ajax_import_course_grade",	
					type: "POST",
					data: data,		
					async: false,
					cache :false,
					success: function (data) {
						$('#grade_table tbody').append(data);
						import_disable=true;	// DIAM-785 -->
						var big = 0;
						var GRADE_CUNT 	= document.getElementsByName('GRADE_CUNT[]')
						for(var i = 0 ; i < GRADE_CUNT.length ; i++){
							if(parseFloat(GRADE_CUNT[i].value) > parseFloat(big))
								big = GRADE_CUNT[i].value
						}
						grade_cunt = big
						grade_cunt++;
						//alert(grade_cunt)
						calc_wp()
					}		
				}).responseText;
			});
			}// DIAM-785 -->
		}
		function calc_wp(){
			var POINTS 				= document.getElementsByName('POINTS[]')
			var WEIGHT 				= document.getElementsByName('WEIGHT[]')
			var WEIGHTED_POINTS 	= document.getElementsByName('WEIGHTED_POINTS[]')
			var tot_wp = 0;
			for(var i = 0 ; i < POINTS.length ; i++){
				var wp = '';
				if(POINTS[i].value != '' && WEIGHT[i].value != '') {
					wp = parseFloat(POINTS[i].value) * parseFloat(WEIGHT[i].value)
					
					tot_wp = parseFloat(tot_wp) + parseFloat(wp)
				}
					
				WEIGHTED_POINTS[i].value = wp
			}
			document.getElementById('WEIGHTED_POINTS_TOTAL').innerHTML = tot_wp
			
		}
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				if(type == 'grade')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.TAB_GRADE?>?';
							
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'grade') {
						var id = $("#DELETE_ID").val()
						$("#grade_table_"+id).remove()
						calc_wp()
					}
				}
				$("#deleteModal").modal("hide");
			});
		}
		//<!--27 June-->
		function confirm_restore_grade_book_setup(){
		jQuery(document).ready(function($) {
			$("#restore_Modal_grade_book_setup").modal();
		});
		}

	function RestoreGradeBook(val, TABVAL) {

		if (TABVAL == 'GB_SETUP') {

			if (document.getElementById("RESTORE_GRADE_BOOK_SETUP").value == '') {
				document.getElementById("RESTORE_GRADE_BOOK_SETUP_ERR").style.display = 'block';
			} else {
				
				var last_date = document.getElementById("RESTORE_GRADE_BOOK_SETUP").value;
				jQuery(document).ready(function($) {
					var data = 'id=' + val + '&last_date=' + last_date;
					var value = $.ajax({
						url: "ajax_get_grade_book_history",
						type: "POST",
						data: data,
						async: false,
						cache: false,
						success: function(data) {
							document.getElementById('grad_list').innerHTML = data;
							calc_wp();
							jQuery('#restore_Modal_grade_book_setup').modal('hide');								
						}
					}).responseText;
				});

			}

		} 
	}
	var grade_to_be_deleted = false;
	function ajax_del_grade_modal(id) {
	jQuery(document).ready(function($) {
		grade_to_be_deleted = id; 
		document.getElementById('deleteModal_grade_message').innerHTML = '<?= DELETE_MESSAGE . TAB_GRADE ?>? <br> <span style="color : red"> <?= DELETE_MESSAGE_GRADE ?> </span>';
		$("#deleteModal_grade").modal() 

	})
	}
	function confirm_delete_grade() {
			jQuery(document).ready(function($) { 
				var data = 'PK_COURSE_OFFERING_GRADE=' + grade_to_be_deleted;
				var value = $.ajax({
					url: "course_offering_del",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						console.log('data from grade delete func', data);
						jQuery('#deleteModal_grade').modal('hide'); 
						jQuery('#deleteModal_grade_after_msg').modal({
	backdrop: 'static',
	keyboard: false
	}); 
						jQuery('input[value="'+grade_to_be_deleted+'"][name="PK_COURSE_OFFERING_GRADE[]"]').parent().parent().remove();
						calc_wp()
						
					}
				}).responseText;
			})
		}
	//<!--27 June-->
	</script>
	
</body>
</html>
