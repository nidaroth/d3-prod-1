<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/cosmetology_grade_book_labs.php");
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
	<title><?=MNU_COSMETOLOGY_GRADE_BOOK_LABS?> | <?=$title?></title>
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
							<?=MNU_COSMETOLOGY_GRADE_BOOK_LABS?>
						</h4>
                    </div>
                </div>	
				
				<form class="floating-labels" method="get" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="card-group">
						<div class="card">
							<div class="card-body">
								<div class="row">
									<div class="col-md-12" style="text-align:right" >
										<a href="cosmetology_grade_book_labs_pdf" class="btn waves-effect waves-light btn-info" style="margin-bottom:5px" ><?=PDF?></a>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<table data-toggle="table" data-mobile-responsive="true" class="table-striped" >
											<thead>
												<tr>
													<th ><?=LAB_DESCRIPTION?></th>
													<th ><div style="padding-top: 11px;width:100%;text-align:right" ><?=REQUIRED_SESSIONS?></div></th>
													<th ><div style="padding-top: 11px;width:100%;text-align:right" ><?=COMPLETED_SESSIONS?></div></th>
													<th ><div style="padding-top: 11px;width:100%;text-align:right" ><?=REMAINING_SESSIONS?></div></th>
												</tr>
											</thead>
											<tbody>
												<? $query = "select PK_STUDENT_PROGRAM_GRADE_BOOK_INPUT, CODE, GRADE_BOOK_TYPE, S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.DESCRIPTION,  SESSION_REQUIRED,  SUM(SESSION_COMPLETED) as SESSION_COMPLETED, HOUR_REQUIRED, HOUR_COMPLETED, POINTS_REQUIRED, POINTS_COMPLETED, PK_STUDENT_ENROLLMENT 
												from 
												S_STUDENT_PROGRAM_GRADE_BOOK_INPUT 
												LEFT JOIN M_GRADE_BOOK_CODE ON M_GRADE_BOOK_CODE.PK_GRADE_BOOK_CODE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE 
												,M_GRADE_BOOK_TYPE  
												WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_GRADE_BOOK_TYPE.PK_GRADE_BOOK_TYPE = S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_TYPE AND PK_GRADE_BOOK_TYPE_MASTER = 3 GROUP BY S_STUDENT_PROGRAM_GRADE_BOOK_INPUT.PK_GRADE_BOOK_CODE  ORDER BY CODE ASC, COMPLETED_DATE ASC "; //Ticket # 441
												$_SESSION['QUERY'] = $query;
												$res_grade = $db->Execute($query);
												$TOT_SESSION_REQUIRED 	= 0;
												$TOT_SESSION_COMPLETED 	= 0;
												$TOT_SESSION_REMAINING 	= 0;
												
												while (!$res_grade->EOF) { 
													$TOT_SESSION_REQUIRED 	+= $res_grade->fields['SESSION_REQUIRED'];
													$TOT_SESSION_COMPLETED 	+= $res_grade->fields['SESSION_COMPLETED']; 
													?>
													<tr >
														<td>
															<?=$res_grade->fields['DESCRIPTION'] ?>
														</td>
														<td>
															<div style="padding-top: 11px;width:100%;text-align:right" >
																<?=number_format_value_checker($res_grade->fields['SESSION_REQUIRED'],2)?>
															</div>
														</td>
														<td>
															<div style="padding-top: 11px;width:100%;text-align:right" >
																<?=number_format_value_checker($res_grade->fields['SESSION_COMPLETED'],2)?>
															</div>
														</td>
														<td>
															<div style="padding-top: 11px;width:100%;text-align:right" >
																<?=number_format_value_checker(($res_grade->fields['SESSION_REQUIRED'] - $res_grade->fields['SESSION_COMPLETED']),2)?>
															</div>
														</td>
													</tr>
												<?	$res_grade->MoveNext();
												} ?>
											</tbody>
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