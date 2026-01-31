<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
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
	<title><?=MNU_SEND_COURSE_OFFERING_RESULT ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_SEND_COURSE_OFFERING_RESULT ?> </h4>
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
														<th ><?=TERM?></th>
														<th ><?=COURSE_CODE?></th>
														<th ><?=SESSION?></th>
														<th ><?=INSTRUCTOR?></th>
														<th ><?=STATUS?></th>
														<th ><?=MESSAGE?></th>
													</tr>
												</thead>
												<tbody>
													<? $query = "SELECT S_COURSE_OFFERING.PK_COURSE_OFFERING,COURSE_CODE,IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, OFFICIAL_CAMPUS_NAME ,CONCAT(EMP_INSTRUCTOR.FIRST_NAME,' ',EMP_INSTRUCTOR.LAST_NAME) AS INSTRUCTOR_NAME, CONCAT(SESSION,' - ',SESSION_NO) as SESSION , CONCAT(ROOM_NO,' - ', ROOM_DESCRIPTION) AS ROOM_NO, COURSE_OFFERING_STATUS, IF(S_COURSE_OFFERING_CANVAS.SUCCESS = 1,'Success','Failed') as STATUS, MESSAGE FROM S_COURSE_OFFERING_CANVAS,S_COURSE_OFFERING LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM  LEFT JOIN S_COURSE ON S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS LEFT JOIN S_EMPLOYEE_MASTER AS EMP_INSTRUCTOR ON EMP_INSTRUCTOR.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR WHERE
													S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_CANVAS.PK_COURSE_OFFERING AND BATCH_ID = '$_GET[id]'";
													$_SESSION['query'] = $query;
													$res_disb = $db->Execute($query);
													$total = 0;
													while (!$res_disb->EOF) {  ?>
														<tr>
															<td><?=$res_disb->fields['TERM_BEGIN_DATE']?></td>
															<td><?=$res_disb->fields['COURSE_CODE']?></td>
															<td><?=$res_disb->fields['SESSION']?></td>
															<td><?=$res_disb->fields['INSTRUCTOR_NAME']?></td>
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
												
												<!--<button type="button" onclick="window.location.href='send_course_offering_canvas_result_excel'" name="btn" class="btn waves-effect waves-light btn-info"  ><?=EXPORT_TO_EXCEL?></button>
												
												<button type="button" onclick="window.location.href='send_course_offering_canvas_result_pdf'" name="btn" class="btn waves-effect waves-light btn-info"  ><?=EXPORT_TO_PDF?></button>-->
												
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