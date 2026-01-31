<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
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
	<title>
		<?=MNU_CREATE_STUDENT_LOGIN_DATA_VIEW ?> | <?=$title?>
	</title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<style>
	.custom_table{};
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
				<div class="row page-titles" style="padding-bottom: 10px;" >
                    <div class="col-md-8 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_CREATE_STUDENT_LOGIN_DATA_VIEW ?>
								
							<a href="student_bulk_login_data_sheet_excel?id=<?=$_GET['id']?>" title="<?=EXCEL?>" class="btn btn-info btn-circle"><i class="mdi mdi-file-excel-box"></i></a>
						</h4>
                    </div>
				</div>
				
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12" style="max-height:600px;overflow-x: auto;overflow-y: auto;">
										<table data-toggle="table" data-height="500" data-mobile-responsive="true" class="table-striped" >
											<thead>
												<tr>
													<th>#</th>
													<th><?=STUDENT?></th>
													<th><?=STATUS?></th>
													<th><?=FIRST_TERM?></th>
													<th><?=PROGRAM?></th>
													<th><?=LOGIN_ID?></th>
													<th><?=PASSWORD?></th>
												</tr>
											</thead>
											<tbody>
												<? $res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_BULK_LOGIN WHERE PK_STUDENT_BULK_LOGIN = '$_GET[id]' ");
												$PK_STUDENT_ENROLLMENT = $res_type->fields['PK_STUDENT_ENROLLMENT'];
												
												$res_type = $db->Execute("SELECT STU_DEFAULT_PASSWORD FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
												$STU_DEFAULT_PASSWORD = $res_type->fields['STU_DEFAULT_PASSWORD'];
												
												$query = "SELECT CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, STUDENT_STATUS, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS BEGIN_DATE, M_CAMPUS_PROGRAM.CODE, USER_ID FROM S_STUDENT_MASTER , S_STUDENT_ENROLLMENT  
												LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
												LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
												LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS
												, Z_USER WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) AND Z_USER.ID = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_USER_TYPE = 3 AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
												$_SESSION['REPORT_QUERY'] = $query;
												$res_type = $db->Execute($query);
											
												$i = 0;
												while (!$res_type->EOF) { 
													$i++; 
													$PK_STUDENT_MASTER = $res_type->fields['PK_STUDENT_MASTER']; ?>
													<tr>
														<td><?=$i?></td>
														<td><?=$res_type->fields['STU_NAME']?></td>
														<td><?=$res_type->fields['STUDENT_STATUS']?></td>
														<td><?=$res_type->fields['BEGIN_DATE']?></td>
														<td><?=$res_type->fields['CODE']?></td>
														<td><?=$res_type->fields['USER_ID']?></td>
														<td><?=$STU_DEFAULT_PASSWORD?></td>
													</tr>
												<?	$res_type->MoveNext();
												} ?>
											</tbody>
										</table>
									</div>
								</div>
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