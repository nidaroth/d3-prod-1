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
	if($_POST['PK_ACCOUNT'] > 0)
		$cond = " AND PK_ACCOUNT = '$_POST[PK_ACCOUNT]' ";
		
	$res_stu = $db->Execute("select PK_STUDENT_COURSE, PK_ACCOUNT FROM S_STUDENT_COURSE WHERE  (FINAL_GRADE = '' OR FINAL_GRADE = 0) $cond ");
	while (!$res_stu->EOF) { 
		$PK_STUDENT_COURSE 	= $res_stu->fields['PK_STUDENT_COURSE'];
		$PK_ACCOUNT 		= $res_stu->fields['PK_ACCOUNT'];
		
		$res_grade_def 	= $db->Execute("SELECT PK_GRADE, GRADE  FROM S_GRADE WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND IS_DEFAULT = 1 ");
		
		$STUDENT_COURSE['FINAL_GRADE'] 			= $res_grade_def->fields['PK_GRADE'];
		$STUDENT_COURSE['FINAL_GRADE_GRADE']	= $res_grade_def->fields['GRADE'];
		db_perform('S_STUDENT_COURSE', $STUDENT_COURSE, 'update', " PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' ");

		$res_stu->MoveNext();
	}
	
	$msg = 'Update Default Grade Updated';	
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
	<title>Update Default Grade | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Update Default Grade</h4>
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
													<select name="PK_ACCOUNT" id="PK_ACCOUNT" class="form-control" >
														<option value="-1">All Accounts</option>
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
																	
											<div class="row">
												<div class="col-3 col-sm-3">
												</div>
												
												<div class="col-9 col-sm-9">
													<input type="hidden" name="hid" value="" >
													<button type="submit" class="btn waves-effect waves-light btn-info">Update Default Grade</button>
													<br />
													<br />
												</div>
											</div>
										</div>
										
										<div class="col-6 col-sm-6" >
											
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
		
	</script>

</body>

</html>