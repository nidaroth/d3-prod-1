<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$res = $db->Execute("SELECT ENABLE_ETHINK FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['ENABLE_ETHINK'] == 0) {
	header("location:../index");
	exit;
}


require_once("../global/ethink.php"); 

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
	<title><?=MNU_ATTENDANCE_REPORT ?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<style>
		li > a > label{position: unset !important;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-2 align-self-center" >
                        <h4 class="text-themecolor"><?=MNU_ATTENDANCE_REPORT?> </h4>
                    </div>
                </div>
				<form class="floating-labels " method="post" name="form1" id="form1" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-12">
											<div style="max-height:600px;overflow-x: auto;overflow-y: auto;" >
												<table data-toggle="table" data-height="500" data-mobile-responsive="true" class="table-striped" >
													<thead>
														<tr>
															<th>Full Name</th>
															<th>User Name</th>
															<th>User ID Number</th>
															<th>Course Fullname</th>
															<th>Course Short name</th>
															<th>Course ID Number</th>
															<th>Course ID</th>
															<th>Course Category</th>
															<th>Acronym</th>
															<th>Description</th>
															<th>Session Date</th>
															<th>Time Taken</th>
														</tr>
													</thead>
													<tbody>
														<? $json_data = attendance_report_ethink($_SESSION['PK_ACCOUNT']);
														$json_data = json_decode($json_data);
														//echo "<pre>"; print_r($json_data);exit;
														$data = $json_data->data;
														$data = json_decode($data);
														foreach($data as $json_data1){ ?>
															<tr>
																<td><?=$json_data1->lastname.', '.$json_data1->firstname?></td>
																<td><?=$json_data1->username?></td>
																<td><?=$json_data1->user_idnumber?></td>
																<td><?=$json_data1->course_fullname?></td>
																<td><?=$json_data1->course_shortname?></td>
																<td><?=$json_data1->course_idnumber?></td>
																<td><?=$json_data1->course_id?></td>
																<td><?=$json_data1->course_category?></td>
																<td><?=$json_data1->acronym?></td>
																<td><?=$json_data1->description?></td>
																<td>
																	<? if($json_data1->sessiondate != '') echo date("m/d/Y h:i A",$json_data1->sessiondate);?>
																</td>
																<td>
																	<? if($json_data1->timetaken != '') echo date("m/d/Y h:i A",$json_data1->timetaken);?>
																</td>
															</tr>
														<? } ?>
													</tbody>
												</table>
											</div>
										</div>
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
	<script type="text/javascript">
	
	</script>

</body>

</html>