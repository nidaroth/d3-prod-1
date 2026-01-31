<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/dashboard.php");
require_once("../language/instructor_final_grade.php");

require_once("../global/mail.php"); 
require_once("../global/texting.php"); 

//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$CREATED_ON_HISTROY = date("Y-m-d H:i:s"); // DIAM-785
	$i = 0;
	foreach($_POST['GRADE_INPUT_PK_STUDENT_COURSE'] as $PK_STUDENT_COURSE){
	
		$res_course_unit = $db->Execute("SELECT UNITS FROM S_COURSE, S_COURSE_OFFERING, S_STUDENT_COURSE WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
		
		$STUDENT_COURSE = array();
		$STUDENT_COURSE['COURSE_UNITS'] = $res_course_unit->fields['UNITS'];
		$STUDENT_COURSE['FINAL_GRADE']  = $_POST['GRADE_INPUT_GRADE'][$i];

		$flag = 0;
		$res_tc = $db->Execute("SELECT FINAL_GRADE FROM S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' "); 
		$CUR_PK_GRADE = $res_tc->fields['FINAL_GRADE'];
		if($CUR_PK_GRADE != $STUDENT_COURSE['FINAL_GRADE']) {
			$res_grade_data = $db->Execute("SELECT * FROM S_GRADE WHERE PK_GRADE = '$STUDENT_COURSE[FINAL_GRADE]' "); 
			$STUDENT_COURSE['FINAL_GRADE_GRADE'] 				= $res_grade_data->fields['GRADE'];
			$STUDENT_COURSE['FINAL_GRADE_NUMBER_GRADE'] 		= $res_grade_data->fields['NUMBER_GRADE'];
			$STUDENT_COURSE['FINAL_GRADE_CALCULATE_GPA'] 		= $res_grade_data->fields['CALCULATE_GPA'];
			$STUDENT_COURSE['FINAL_GRADE_UNITS_ATTEMPTED'] 		= $res_grade_data->fields['UNITS_ATTEMPTED'];
			$STUDENT_COURSE['FINAL_GRADE_UNITS_COMPLETED'] 		= $res_grade_data->fields['UNITS_COMPLETED'];
			$STUDENT_COURSE['FINAL_GRADE_UNITS_IN_PROGRESS'] 	= $res_grade_data->fields['UNITS_IN_PROGRESS'];
			$STUDENT_COURSE['FINAL_GRADE_WEIGHTED_GRADE_CALC'] 	= $res_grade_data->fields['WEIGHTED_GRADE_CALC'];
			$STUDENT_COURSE['FINAL_GRADE_RETAKE_UPDATE'] 		= $res_grade_data->fields['RETAKE_UPDATE'];
			$flag = 1;
		}
		db_perform('S_STUDENT_COURSE', $STUDENT_COURSE, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' ");

		//DIAM-785
		$res_ssc = $db->Execute("SELECT PK_STUDENT_MASTER,PK_STUDENT_ENROLLMENT,PK_COURSE_OFFERING,PK_TERM_MASTER FROM S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' "); 
		$STUDENT_COURSE['PK_STUDENT_MASTER'] = $res_ssc->fields['PK_STUDENT_MASTER'];
		$STUDENT_COURSE['PK_STUDENT_ENROLLMENT'] = $res_ssc->fields['PK_STUDENT_ENROLLMENT'];			
		$STUDENT_COURSE['PK_COURSE_OFFERING'] = $res_ssc->fields['PK_COURSE_OFFERING'];			
		$STUDENT_COURSE['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$STUDENT_COURSE['PK_TERM_MASTER'] = $res_ssc->fields['PK_TERM_MASTER'];	
		$STUDENT_COURSE['PK_STUDENT_COURSE'] = $PK_STUDENT_COURSE;
		$STUDENT_COURSE['CREATED_ON']  			= $CREATED_ON_HISTROY;
		$STUDENT_COURSE['EDITED_BY'] = $_SESSION['PK_USER'];
		db_perform('S_STUDENT_COURSE_HISTROY', $STUDENT_COURSE, 'insert');
		//DIAM-785
		
		if($flag == 1) {
			$res_noti = $db->Execute("SELECT PK_EMAIL_TEMPLATE,PK_TEXT_TEMPLATE FROM S_NOTIFICATION_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EVENT_TYPE = 9");
			if($res_noti->RecordCount() > 0) {
				if($res_noti->fields['PK_EMAIL_TEMPLATE'] > 0) {
					send_final_grade_posted_mail($PK_STUDENT_COURSE,$res_noti->fields['PK_EMAIL_TEMPLATE']);
				}
				
				if($res_noti->fields['PK_TEXT_TEMPLATE'] > 0) {
					send_final_grade_posted_text($PK_STUDENT_COURSE,$res_noti->fields['PK_TEXT_TEMPLATE']);
				}
			}
		}
		
		$i++;
	}
	
	header("location:final_grade?tm=".$_POST['PK_TERM_MASTER']."&co=".$_POST['PK_COURSE_OFFERING']); //<!--DIAM-785 -->
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
	<style>
		.table th, .table td {padding: 7px;}
	</style>
	<title><?=MNU_FINAL_GRADE?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_FINAL_GRADE?></h4>
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
													<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control required-entry" onchange="get_course_offering(this.value)">
														<option value=""></option>
														<? $res_type = $db->Execute("select S_TERM_MASTER.PK_TERM_MASTER,IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00' ,DATE_FORMAT( S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, IF(S_TERM_MASTER.END_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.END_DATE,'%m/%d/%Y'),'') AS TERM_END_DATE, TERM_DESCRIPTION from S_COURSE_OFFERING LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING , S_TERM_MASTER WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND  S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER GROUP BY S_TERM_MASTER.PK_TERM_MASTER ORDER BY BEGIN_DATE ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($_GET['tm'] == $res_type->fields['PK_TERM_MASTER']) echo "selected"; ?> ><?=$res_type->fields['TERM_BEGIN_DATE'].' - '.$res_type->fields['TERM_END_DATE'].' - '.$res_type->fields['TERM_DESCRIPTION'] ?></option>  <!--DIAM-785 -->
														<?	$res_type->MoveNext();
														} ?>
													</select>
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
													<button type="button" onclick="get_student_from_co()" class="btn waves-effect waves-light btn-info"><?=SHOW?></button>
												</div>
											</div>
											<div id="course_details">
											</div>
										</div>
										<div class="col-sm-6 pt-25 theme-v-border" >
											<div id="STUDENT_DIV">
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
        </div>
        <? require_once("footer.php"); ?>		
    </div>
    <? require_once("js.php"); ?>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<!--DIAM-785 -->
	<?php if(isset($_GET['tm']) && !empty($_GET['tm'])){ ?>
	<script type="text/javascript">													
	jQuery(document).ready(function($) {  
		get_student_from_co();
	});
	</script>
	<?php } ?>	
	<!--DIAM-785 -->
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
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
		function get_student_from_co(){
			jQuery(document).ready(function($) { 
				if(document.getElementById('PK_COURSE_OFFERING').value != '') {
					var data  = 'val='+document.getElementById('PK_COURSE_OFFERING').value+'&required=1';
					//alert(data)
					var value = $.ajax({
						url: "ajax_get_student_from_course_offering_for_final_grade",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('STUDENT_DIV').innerHTML = data;
						}		
					}).responseText;
				}
			});
		}
		function get_schedule(val){
			document.getElementById('STUDENT_DIV').innerHTML 	= '';
		}

	// DIAM-785
	function confirm_restore_final_grade(){
	jQuery(document).ready(function($) {
		$("#restore_Modal_final_grade").modal();
	});
	}

	function RestoreGradeBook(val, TABVAL) {
	if (TABVAL == 'FG_SETUP') { //DIAM-785

			if (document.getElementById("RESTORE_FINAL_GRADE").value == '') {
				document.getElementById("RESTORE_FINAL_GRADE_ERR").style.display = 'block';
			} else {
				
				var last_date = document.getElementById("RESTORE_FINAL_GRADE").value;
				jQuery(document).ready(function($) {
					var data = 'id=' + val + '&last_date=' + last_date +'&required=1';
					var value = $.ajax({
						url: "ajax_get_final_grade_history",
						type: "POST",
						data: data,
						async: false,
						cache: false,
						success: function(data) {
							document.getElementById('finalGradBook').innerHTML = data;							
							jQuery('#restore_Modal_final_grade').modal('hide');								

						}
					}).responseText;
				});
			}
		} //DIAM-785
	}
	// DIAM-785
	</script>
</body>
</html>
