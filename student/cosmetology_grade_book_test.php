<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/cosmetology_grade_book_test.php");
require_once("../language/menu.php");

//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_USER_TYPE'] != 3 ){ 
	header("location:../index");
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
	<title><?=MNU_COSMETOLOGY_GRADE_BOOK_TEST?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_COSMETOLOGY_GRADE_BOOK_TEST?>
						</h4>
                    </div>
                </div>	
				
				<form class="floating-labels" method="get" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="card-group">
						<div class="card">
							<div class="card-body">
								<div class="row">
									<div class="col-md-7" style="text-align:right" >
										<a href="cosmetology_grade_book_test_pdf" class="btn waves-effect waves-light btn-info" style="margin-bottom:5px" ><?=PDF?></a>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<table class="table-striped table table-hover" style="width:58%" >
											<tr >
												<td style="width:70%" ><?=TEST_DESCRIPTION?></td>
												<td style="width:30%" >
													<div style="text-align:right" >
														<?=AVERAGE_GRADE?>
													</div>
												</td>
											</tr>
											<? $query = "select SUM(POINTS_REQUIRED) as POINTS_REQUIRED, SUM(POINTS_COMPLETED) as POINTS_COMPLETED, CONCAT(M_GRADE_BOOK_CODE.CODE, ' - ', M_GRADE_BOOK_CODE.DESCRIPTION) AS TEST from 
											S_STUDENT_PROGRAM_GRADE_BOOK_INPUT, M_GRADE_BOOK_CODE, M_GRADE_BOOK_TYPE  
											WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
											M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE AND 
											M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE AND PK_GRADE_BOOK_TYPE_MASTER = 2 
											GROUP BY S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE ";
											$_SESSION['QUERY']	 = $query;
											$res_course_schedule = $db->Execute($query);
											$TOTAL_HOURS 		= 0;
											$ATTENDANCE_HOURS 	= 0;
											while (!$res_course_schedule->EOF) { ?>
												<tr >
													<td style="width:70%" ><?=$res_course_schedule->fields['TEST']?></td>
													<td style="width:30%" >
														<div style="text-align:right" >
															<?=number_format_value_checker(($res_course_schedule->fields['POINTS_COMPLETED'] / $res_course_schedule->fields['POINTS_REQUIRED'] * 100),2) ?>
														</div>
													</td>
												</tr> 
												<? $res_course_schedule->MoveNext();
											} ?>
										</table>
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
	
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
</body>
</html>