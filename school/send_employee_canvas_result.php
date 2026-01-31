<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/employee.php");
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
	<title><?=MNU_SEND_INSTRUCTOR ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_SEND_INSTRUCTOR ?> </h4>
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
														<th ><?=EMPLOYEE_ID?></th>
														<th ><?=EMAIL?></th>
														<th ><?=STATUS?></th>
														<th ><?=MESSAGE?></th>
													</tr>
												</thead>
												<tbody>
													<? $query = "SELECT  S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, EMPLOYEE_ID, COMPANY_EMP_ID, CELL_PHONE, EMAIL, IF(IS_FACULTY = 1, 'Yes','No') as IS_FACULTY, IF(S_EMPLOYEE_MASTER_CANVAS.SUCCESS = 1,'Success', 'Failed') as STATUS, MESSAGE FROM S_EMPLOYEE_MASTER_CANVAS, S_EMPLOYEE_MASTER,S_EMPLOYEE_CONTACT WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER_CANVAS.PK_EMPLOYEE_MASTER AND BATCH_ID = '$_GET[id]' AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_EMPLOYEE_CONTACT.PK_EMPLOYEE_MASTER ";
													$_SESSION['query'] = $query;
													$res_disb = $db->Execute($query);
													$total = 0;
													while (!$res_disb->EOF) {  ?>
														<tr>
															<td><?=$res_disb->fields['NAME']?></td>
															<td><?=$res_disb->fields['EMPLOYEE_ID']?></td>
															<td><?=$res_disb->fields['EMAIL']?></td>
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
												
												<!--<button type="button" onclick="window.location.href='send_course_offering_ethink_result_excel'" name="btn" class="btn waves-effect waves-light btn-info"  ><?=EXPORT_TO_EXCEL?></button>
												
												<button type="button" onclick="window.location.href='send_course_offering_ethink_result_pdf'" name="btn" class="btn waves-effect waves-light btn-info"  ><?=EXPORT_TO_PDF?></button>-->
												
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