<? require_once("../global/config.php");
require_once("../school/function_calc_student_grade.php");

if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	if($_POST['PK_COURSE_OFFERING'] != '') {
		$PK_COURSE_OFFERING 	= $_POST['PK_COURSE_OFFERING'];
		$_SESSION['PK_ACCOUNT'] = $_POST['PK_ACCOUNT'];
		
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
			//echo $POINTS."<br />".$PK_STUDENT_GRADE."<br />".$PK_STUDENT_COURSE."<br />".$PK_STUDENT_MASTER;exit;
			
			if(trim($POINTS) != '')
				calc_stu_grade($POINTS,$PK_STUDENT_GRADE,$PK_STUDENT_COURSE,$PK_STUDENT_MASTER,2);
			
			$res_stu->MoveNext();
		}
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
	<title>Update Course Offering If Grade Book Data Exists | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Update Course Offering If Grade Book Data Exists</h4>
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
											<b>Description:</b> For data conversion purposes only. Updates grades for specific Course Offerings for an account using data from the Course Offering Grade Book. If Course Offering Grade Book is blank, the grade WILL NOT BE UPDATED.
											<br /><br />
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