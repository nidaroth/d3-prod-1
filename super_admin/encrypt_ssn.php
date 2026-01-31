<? require_once("../global/config.php");
require_once("../school/function_calc_student_grade.php");

if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	if($_POST['PK_ACCOUNT'] != '') {
		
		$res_stu = $db->Execute("select PK_STUDENT_MASTER, SSN FROM S_STUDENT_MASTER WHERE  PK_ACCOUNT = '$_POST[PK_ACCOUNT]' AND SSN != '' ");
		while (!$res_stu->EOF) {
			$PK_STUDENT_MASTER 	= $res_stu->fields['PK_STUDENT_MASTER'];
			$SSN 				= $res_stu->fields['SSN'];
			
			$SSN1 = preg_replace( '/[^0-9]/', '',$SSN);
			if(strlen($SSN1) == 9) {
				$SSN1 = $SSN1[0].$SSN1[1].$SSN1[2].'-'.$SSN1[3].$SSN1[4].'-'.$SSN1[5].$SSN1[6].$SSN1[7].$SSN1[8];
				
				$STUDENT_MASTER['SSN'] = my_encrypt($_POST['PK_ACCOUNT'].$PK_STUDENT_MASTER, $SSN1);
				db_perform('S_STUDENT_MASTER', $STUDENT_MASTER, 'update'," PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_POST[PK_ACCOUNT]' ");
			}
			
			$res_stu->MoveNext();
		}
		
		$res_emp = $db->Execute("select PK_EMPLOYEE_MASTER, SSN FROM S_EMPLOYEE_MASTER WHERE  PK_ACCOUNT = '$_POST[PK_ACCOUNT]' AND SSN != '' ");
		while (!$res_emp->EOF) {
			$PK_EMPLOYEE_MASTER 	= $res_emp->fields['PK_EMPLOYEE_MASTER'];
			$SSN 					= $res_emp->fields['SSN'];
			
			$SSN1 = preg_replace( '/[^0-9]/', '',$SSN);
			if(strlen($SSN1) == 9) {
				$SSN1 = $SSN1[0].$SSN1[1].$SSN1[2].'-'.$SSN1[3].$SSN1[4].'-'.$SSN1[5].$SSN1[6].$SSN1[7].$SSN1[8];
				
				$EMPLOYEE_MASTER['SSN'] = my_encrypt($_POST['PK_ACCOUNT'].$PK_EMPLOYEE_MASTER, $SSN1);
				db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER, 'update'," PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$_POST[PK_ACCOUNT]' ");
			}
			
			$res_emp->MoveNext();
		}
	}
	$msg = 'SSN Encrypted';	
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
	<title>Encrypt SSN | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Encrypt SSN</h4>
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
													<select name="PK_ACCOUNT" id="PK_ACCOUNT" class="form-control required-entry" >
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
																	
											<div class="row">
												<div class="col-3 col-sm-3">
												</div>
												
												<div class="col-9 col-sm-9">
													<button type="submit" class="btn waves-effect waves-light btn-info">Encrypt SSN</button>
													<br />
													<br />
												</div>
											</div>
										</div>
										
										<div class="col-6 col-sm-6" >
											<b>Description:</b> For data conversion purposes only. Encrypt all Student and Employee SSN on the selected account
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