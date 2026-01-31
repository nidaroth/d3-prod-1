<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
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


if(!empty($_POST)){
	require_once("../global/ethink.php"); 
	require_once("function_attendance.php"); 
	
	//echo "<pre>";print_r($_POST);exit;
	$i = 0;
	foreach($_POST['PK_STUDENT_ATTENDANCE_ETHINK'] as $PK_STUDENT_ATTENDANCE_ETHINK){
		$PK_STUDENT_MASTER		= $_POST['PK_STUDENT_MASTER'][$i];
		$PK_COURSE_OFFERING 	= $_POST['PK_COURSE_OFFERING'][$i];
		$PK_STUDENT_COURSE 		= $_POST['PK_STUDENT_COURSE'][$i];
		$PK_STUDENT_ENROLLMENT 	= $_POST['PK_STUDENT_ENROLLMENT'][$i];
		$SESSION_DATE 			= $_POST['SESSION_DATE'][$i];
		$DURATION 				= $_POST['DURATION'][$i];
		$START_TIME 			= '';
		$END_TIME 				= '';
		
		$res_type = $db->Execute("select POSTED FROM S_STUDENT_ATTENDANCE_ETHINK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ATTENDANCE_ETHINK = '$PK_STUDENT_ATTENDANCE_ETHINK' ");
		if($res_type->fields['POSTED'] == 0){
			$res_co = $db->Execute("SELECT PK_ATTENDANCE_CODE FROM S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");  
			$PK_ATTENDANCE_CODE = $res_co->fields['PK_ATTENDANCE_CODE'];
			$COMPLETED 			= 1;

			$PK_STUDENT_SCHEDULE 	= create_non_schedule('',$PK_COURSE_OFFERING,$SESSION_DATE,$START_TIME,$END_TIME,$DURATION,$PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT, 1,$_SESSION['PK_ACCOUNT'],0);
			$PK_STUDENT_ATTENDANCE	= attendance_entry(0,$COMPLETED,'',$PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT,$PK_STUDENT_SCHEDULE,$DURATION, $PK_ATTENDANCE_CODE,$_SESSION['PK_ACCOUNT'],0);
			
			$STUDENT_ATTENDANCE_ETHINK['PK_STUDENT_ATTENDANCE'] = $PK_STUDENT_ATTENDANCE;
			$STUDENT_ATTENDANCE_ETHINK['POSTED'] 				= 1;
			db_perform('S_STUDENT_ATTENDANCE_ETHINK', $STUDENT_ATTENDANCE_ETHINK, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ATTENDANCE_ETHINK = '$PK_STUDENT_ATTENDANCE_ETHINK' ");
		}
		
		$i++;
	}
	header("location:import_attendance_ethink_result?id=".$_GET['id'].'&posted=1');
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
	<title><?=MNU_IMPORT_ATTENDANCE_RESULT ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_IMPORT_ATTENDANCE_RESULT ?> </h4>
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
														<th ><?=ENROLLMENT ?></th>
														<th ><?=COURSE_OFFERING?></th>
														<th ><?=DATE?></th>
														<th ><?=ATTENDED_HOUR?></th>
														<th ><?=STATUS?></th>
														<th ><?=MESSAGE?></th>
														<? if($_GET['posted'] == 1){ ?>
														<th ><?=STATUS?></th>
														<? } ?>
													</tr>
												</thead>
												<tbody>
													<? $query = "SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS NAME, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.MIDDLE_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS REPRESENTATIVE , STUDENT_ID,  IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y')) AS BEGIN_DATE ,STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE, CONCAT(M_CAMPUS_PROGRAM.CODE,' - ',M_CAMPUS_PROGRAM.DESCRIPTION) as PROGRAM,  PK_COURSE_OFFERING, SESSION_DATE, DURATION, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, PK_STUDENT_ATTENDANCE_ETHINK, MESSAGE, IF(S_STUDENT_ATTENDANCE_ETHINK.SUCCESS = 1,'Success','Failed') as STATUS   
													FROM 
													S_STUDENT_ATTENDANCE_ETHINK, S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT 
													LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
													LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
													LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
													LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
													LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE 
													WHERE 
													S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
													S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
													S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
													S_STUDENT_ATTENDANCE_ETHINK.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND BATCH_ID = '$_GET[id]' ";
													$_SESSION['query'] = $query;
													//echo $query;
													$res_disb = $db->Execute($query);
													$total = 0;
													while (!$res_disb->EOF) {  
														$PK_COURSE_OFFERING 	= $res_disb->fields['PK_COURSE_OFFERING']; 
														$PK_STUDENT_ENROLLMENT 	= $res_disb->fields['PK_STUDENT_ENROLLMENT']; 
														$res_type = $db->Execute("select PK_STUDENT_COURSE,COURSE_CODE,SESSION,SESSION_NO FROM S_STUDENT_COURSE,S_COURSE, S_COURSE_OFFERING LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION WHERE S_STUDENT_COURSE.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); ?>
														<tr>
															<td>
																<?=$res_disb->fields['NAME']?>
																
																<input type="hidden" name="PK_STUDENT_MASTER[]" value="<?=$res_disb->fields['PK_STUDENT_MASTER']?>" >
																<input type="hidden" name="PK_COURSE_OFFERING[]" value="<?=$PK_COURSE_OFFERING?>" >
																<input type="hidden" name="PK_STUDENT_ENROLLMENT[]" value="<?=$PK_STUDENT_ENROLLMENT?>" >
																<input type="hidden" name="SESSION_DATE[]" value="<?=$res_disb->fields['SESSION_DATE']?>" >
																<input type="hidden" name="DURATION[]" value="<?=$res_disb->fields['DURATION']?>" >
																<input type="hidden" name="PK_STUDENT_ATTENDANCE_ETHINK[]" value="<?=$res_disb->fields['PK_STUDENT_ATTENDANCE_ETHINK']?>" >
																<input type="hidden" name="PK_STUDENT_COURSE[]" value="<?=$res_type->fields['PK_STUDENT_COURSE']?>" >
																
															</td>
															<td><?=$res_disb->fields['BEGIN_DATE'].' - '.$res_disb->fields['CODE'].' - '.$res_disb->fields['STUDENT_STATUS']?></td>
															<td><?=$res_type->fields['COURSE_CODE'].' ('.$res_type->fields['SESSION'].'-'.$res_type->fields['SESSION_NO'].')' ?></td>
															<td><?=$res_disb->fields['SESSION_DATE']?></td>
															<td><?=$res_disb->fields['DURATION']?></td>
															<td><?=$res_disb->fields['STATUS']?></td>
															<td><?=$res_disb->fields['MESSAGE']?></td>
															<? if($_GET['posted'] == 1){ ?>
															<td>Posted</td>
															<? } ?>
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
												<? if($_GET['posted'] != 1){ ?>
												<button type="submit" class="btn waves-effect waves-light btn-info" ><?=POST?></button>
												<? } ?>
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='management'" ><?=EXIT_1?></button>
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