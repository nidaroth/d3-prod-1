<? require_once("../global/config.php");
require_once("../school/function_calc_student_grade.php");

if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	$cond = "";
	if($_POST['PK_COURSE_OFFERING'] != -1)
		$cond = " AND PK_COURSE_OFFERING = '$_POST[PK_COURSE_OFFERING]' ";

	$res_co_1 = $db->Execute("select PK_COURSE_OFFERING FROM S_COURSE, S_COURSE_OFFERING WHERE S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND S_COURSE.PK_ACCOUNT = '$_POST[PK_ACCOUNT]' $cond ");
	while (!$res_co_1->EOF) {
		$PK_COURSE_OFFERING = $res_co_1->fields['PK_COURSE_OFFERING'];
		
		$res_stu = $db->Execute("select PK_STUDENT_COURSE, PK_STUDENT_MASTER, FINAL_GRADE FROM S_STUDENT_COURSE WHERE PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_POST[PK_ACCOUNT]' ");
		while (!$res_stu->EOF) {
			$PK_STUDENT_COURSE 	= $res_stu->fields['PK_STUDENT_COURSE'];
			$PK_STUDENT_MASTER 	= $res_stu->fields['PK_STUDENT_MASTER'];
			$FINAL_GRADE 		= $res_stu->fields['FINAL_GRADE'];
			
			$res_grade_data = $db->Execute("SELECT * FROM S_GRADE WHERE PK_GRADE = '$FINAL_GRADE' "); 
			$STUDENT_COURSE['FINAL_GRADE_GRADE'] 				= $res_grade_data->fields['GRADE'];
			$STUDENT_COURSE['FINAL_GRADE_NUMBER_GRADE'] 		= $res_grade_data->fields['NUMBER_GRADE'];
			$STUDENT_COURSE['FINAL_GRADE_CALCULATE_GPA'] 		= $res_grade_data->fields['CALCULATE_GPA'];
			$STUDENT_COURSE['FINAL_GRADE_UNITS_ATTEMPTED'] 		= $res_grade_data->fields['UNITS_ATTEMPTED'];
			$STUDENT_COURSE['FINAL_GRADE_UNITS_COMPLETED'] 		= $res_grade_data->fields['UNITS_COMPLETED'];
			$STUDENT_COURSE['FINAL_GRADE_UNITS_IN_PROGRESS'] 	= $res_grade_data->fields['UNITS_IN_PROGRESS'];
			$STUDENT_COURSE['FINAL_GRADE_WEIGHTED_GRADE_CALC'] 	= $res_grade_data->fields['WEIGHTED_GRADE_CALC'];
			$STUDENT_COURSE['FINAL_GRADE_RETAKE_UPDATE'] 		= $res_grade_data->fields['RETAKE_UPDATE'];
			db_perform('S_STUDENT_COURSE', $STUDENT_COURSE, 'update'," PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND PK_ACCOUNT = '$_POST[PK_ACCOUNT]' ");

			$res_stu->MoveNext();
		}
		
		$res_co_1->MoveNext();
	}

	$msg = 'Grade Updated';	
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewTOKEN" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title>Update Course Offering Grade Info| <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Update Course Offering Grade Info</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<? if($msg != ''){ ?>
										<div class="form-group">
											<label for="input-text" class="col-sm-2 control-label"></label>
											<div class="col-sm-10" style="color:red">
												<?=$msg?>
											</div>
										</div>
									<? } ?>
									
									<div class="d-flex">
										<div class="col-6 col-sm-6" >
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group" >
													<select name="PK_ACCOUNT" id="PK_ACCOUNT" class="form-control" onchange="get_course_offering(this.value)" >
														<option value=""></option>
														<? $res_dep = $db->Execute("select PK_ACCOUNT,SCHOOL_NAME from Z_ACCOUNT WHERE ACTIVE = '1' AND PK_ACCOUNT != 1 ORDER BY SCHOOL_NAME ASC ");
														while (!$res_dep->EOF) {  ?>
															<option class="<?=$class?>" value="<?=$res_dep->fields['PK_ACCOUNT']?>" ><?=$res_dep->fields['SCHOOL_NAME']?></option>
														<?	$res_dep->MoveNext();
														} 	?>
													</select>
													<span class="bar"></span> 
													<label for="PK_ACCOUNT">Account Name</label>
												</div>
											</div>
										
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group" >
													<div id="course_offering_div" >
														<select name="PK_COURSE_OFFERING" id="PK_COURSE_OFFERING" class="form-control" >
															<option value=""></option>
														</select>
													</div>
													<span class="bar"></span> 
													<label for="TOKEN">Course Offering</label>
												</div>
											</div>
																		
											<div class="row">
												<div class="col-3 col-sm-3">
												</div>
												
												<div class="col-9 col-sm-9">
													<button type="submit" class="btn waves-effect waves-light btn-info">Update Grade</button>
												</div>
											</div>
										</div>
										
										<div class="col-6 col-sm-6" >
											<b>Description:</b> For data conversion purposes only. Uses the Final Letter grade to recalculate the following items:
											<br />
											<ul>
												<li>NUMBER_GRADE</li>
												<li>CALCULATE_GPA</li>
												<li>UNITS_ATTEMPTED</li>
												<li>UNITS_COMPLETED</li>
												<li>UNITS_IN_PROGRESS</li>
												<li>WEIGHTED_GRADE_CALC</li>
												<li>RETAKE_UPDATE</li>
											</ul>
											<br />
											Grade Setup and Grade Scale Setup must be populated before using this tool.
										</div>
									</div>
									
									
								</div>
							</form>
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
	
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		function get_course_offering(val){
			jQuery(document).ready(function($) {
					var data = 'PK_ACCOUNT='+val;
					//alert(data);
					var value = $.ajax({
						url: "ajax_get_course_offering",	
						type: "POST",		 
						data: data,		
						async: false,
						cache :false,
						success: function (data) {//alert(data);
							document.getElementById('course_offering_div').innerHTML = data
							$('#PK_COURSE_OFFERING > option:first-child').text('All Course Offerings');
							$('#PK_COURSE_OFFERING > option:first-child').val('-1');
							
							$('.floating-labels .form-control').on('focus blur', function (e) {
								$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
							}).trigger('blur');
						}		
					}).responseText;
				})
		}
		
	</script>

</body>

</html>