<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$res = $db->Execute("SELECT ENABLE_CANVAS FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['ENABLE_CANVAS'] == 0) {
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
	<title><?=MNU_SEND_STUDENTS_RESULT ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_SEND_STUDENTS_RESULT ?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
									<? if($msg1 != '' ){ ?>
									<div class="row">
										<div class="col-md-2">&nbsp;</div>
                                        <div class="col-md-6" style="color:red">
											<?=$msg1?>
										</div>
                                    </div>
									<? } ?>
									<div class="row">
										<div class="col-md-12">
											<table data-toggle="table" data-mobile-responsive="true" class="table-striped">
												<thead>
													<tr>
														<th ><?=NAME?></th>
														<th ><?=STATUS?></th>
														<th ><?=PROGRAM?></th>
														<th ><?=TERM?></th>
														<th ><?=STATUS?></th>
														<th ><?=MESSAGE?></th>
													</tr>
												</thead>
												<tbody>
													<? $query = "SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS NAME, STUDENT_ID,  IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y')) AS BEGIN_DATE ,STUDENT_STATUS, CONCAT(M_CAMPUS_PROGRAM.CODE,' - ',M_CAMPUS_PROGRAM.DESCRIPTION) as PROGRAM, MESSAGE, IF(S_STUDENT_MASTER_CANVAS.SUCCESS = 1,'Success','Failed') as STATUS 
													FROM 
													S_STUDENT_MASTER_CANVAS, S_STUDENT_MASTER 
													LEFT JOIN S_STUDENT_CONTACT On S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 
													, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT 
													LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
													LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
													LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
													LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
													WHERE 
													S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
													S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
													S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
													S_STUDENT_MASTER_CANVAS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
													IS_ACTIVE_ENROLLMENT = 1 AND BATCH_ID = '$_GET[id]' ";
													$_SESSION['query'] = $query;
													
													$res_disb = $db->Execute($query);
													$total = 0;
													while (!$res_disb->EOF) {  ?>
														<tr>
															<td><?=$res_disb->fields['NAME']?></td>
															<td><?=$res_disb->fields['STUDENT_STATUS']?></td>
															<td><?=$res_disb->fields['PROGRAM']?></td>
															<td><?=$res_disb->fields['BEGIN_DATE']?></td>
															<td><?=$res_disb->fields['STATUS']?></td>
															<td><?=$res_disb->fields['MESSAGE']?></td>
														</tr>
													<? $res_disb->MoveNext();
													} ?>
													
												</tbody>
											</table>
										</div>
                                    </div>
									<br />
									<div class="row">
                                        <div class="col-md-12">
											<div class="form-group m-b-5 text-right" >
											
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='management'" ><?=EXIT_1?></button>
												
												<!--<button type="button" onclick="window.location.href='send_student_canvas_result_excel'" name="btn" class="btn waves-effect waves-light btn-info"  ><?=EXPORT_TO_EXCEL?></button>
												
												<button type="button" onclick="window.location.href='send_student_canvas_result_pdf'" name="btn" class="btn waves-effect waves-light btn-info"  ><?=EXPORT_TO_PDF?></button>-->
											</div>
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
    </div>
   
	<? require_once("js.php"); ?>
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>

</body>

</html>