<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/dashboard.php");

if($_SESSION['PK_ROLES'] == 4 || $_SESSION['PK_ROLES'] == 5){ 
	header("location:../index");
	exit;
}

$res = $db->Execute("SELECT NEW_LEAD_STATUS,QUALIFIED_LEAD_STATUS,NEW_APPLICATIONS_STATUS,NEW_STUDENTS_STATUS, NEW_LEAD_FROM_DATE, NEW_LEAD_TO_DATE, QUALIFIED_LEAD_FROM_DATE, QUALIFIED_LEAD_TO_DATE, NEW_APPLICATIONS_FROM_DATE, NEW_APPLICATIONS_TO_DATE, NEW_STUDENTS_FROM_DATE, NEW_STUDENTS_TO_DATE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");  
$NEW_LEAD_STATUS 		 = $res->fields['NEW_LEAD_STATUS']; 
$QUALIFIED_LEAD_STATUS 	 = $res->fields['QUALIFIED_LEAD_STATUS'];
$NEW_APPLICATIONS_STATUS = $res->fields['NEW_APPLICATIONS_STATUS'];
$NEW_STUDENTS_STATUS 	 = $res->fields['NEW_STUDENTS_STATUS']; 

$NEW_LEAD_FROM_DATE 	 	 = $res->fields['NEW_LEAD_FROM_DATE']; 
$NEW_LEAD_TO_DATE 	 		 = $res->fields['NEW_LEAD_TO_DATE']; 
$QUALIFIED_LEAD_FROM_DATE 	 = $res->fields['QUALIFIED_LEAD_FROM_DATE']; 
$QUALIFIED_LEAD_TO_DATE 	 = $res->fields['QUALIFIED_LEAD_TO_DATE']; 
$NEW_APPLICATIONS_FROM_DATE  = $res->fields['NEW_APPLICATIONS_FROM_DATE']; 
$NEW_APPLICATIONS_TO_DATE 	 = $res->fields['NEW_APPLICATIONS_TO_DATE']; 
$NEW_STUDENTS_FROM_DATE 	 = $res->fields['NEW_STUDENTS_FROM_DATE']; 
$NEW_STUDENTS_TO_DATE 	 	 = $res->fields['NEW_STUDENTS_TO_DATE']; 

$COND = "";

if($_GET['t'] == 1){
	if($NEW_LEAD_FROM_DATE != '' && $NEW_LEAD_FROM_DATE != '0000-00-00' && $NEW_LEAD_TO_DATE != '' && $NEW_LEAD_TO_DATE != '0000-00-00' )
		$COND = " AND STATUS_DATE BETWEEN '$NEW_LEAD_FROM_DATE' AND '$NEW_LEAD_TO_DATE' ";
	else if($NEW_LEAD_FROM_DATE != '' && $NEW_LEAD_FROM_DATE != '0000-00-00')
		$COND = " AND STATUS_DATE >= '$NEW_LEAD_FROM_DATE' ";
	else if($NEW_LEAD_TO_DATE != '' && $NEW_LEAD_TO_DATE != '0000-00-00')
		$COND = " AND STATUS_DATE <= '$NEW_LEAD_TO_DATE' ";
		
	$COND .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($NEW_LEAD_STATUS) ";
}	

if($_GET['t'] == 2){
	if($QUALIFIED_LEAD_FROM_DATE != '' && $QUALIFIED_LEAD_FROM_DATE != '0000-00-00' && $QUALIFIED_LEAD_TO_DATE != '' && $QUALIFIED_LEAD_TO_DATE != '0000-00-00' )
		$COND = " AND STATUS_DATE BETWEEN '$QUALIFIED_LEAD_FROM_DATE' AND '$QUALIFIED_LEAD_TO_DATE' ";
	else if($QUALIFIED_LEAD_FROM_DATE != '' && $QUALIFIED_LEAD_FROM_DATE != '0000-00-00')
		$COND = " AND STATUS_DATE >= '$QUALIFIED_LEAD_FROM_DATE' ";
	else if($QUALIFIED_LEAD_TO_DATE != '' && $QUALIFIED_LEAD_TO_DATE != '0000-00-00')
		$COND = " AND STATUS_DATE <= '$QUALIFIED_LEAD_TO_DATE' ";
		
	$COND .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($QUALIFIED_LEAD_STATUS) ";
}	

if($_GET['t'] == 3){
	if($NEW_APPLICATIONS_FROM_DATE != '' && $NEW_APPLICATIONS_FROM_DATE != '0000-00-00' && $NEW_APPLICATIONS_TO_DATE != '' && $NEW_APPLICATIONS_TO_DATE != '0000-00-00' )
		$COND = " AND STATUS_DATE BETWEEN '$NEW_APPLICATIONS_FROM_DATE' AND '$NEW_APPLICATIONS_TO_DATE' ";
	else if($NEW_APPLICATIONS_FROM_DATE != '' && $NEW_APPLICATIONS_FROM_DATE != '0000-00-00')
		$COND = " AND STATUS_DATE >= '$NEW_APPLICATIONS_FROM_DATE' ";
	else if($NEW_APPLICATIONS_TO_DATE != '' && $NEW_APPLICATIONS_TO_DATE != '0000-00-00')
		$COND = " AND STATUS_DATE <= '$NEW_APPLICATIONS_TO_DATE' ";
		
	$COND .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($NEW_APPLICATIONS_STATUS) ";
}	

if($_GET['t'] == 4){
	if($NEW_STUDENTS_FROM_DATE != '' && $NEW_STUDENTS_FROM_DATE != '0000-00-00' && $NEW_STUDENTS_TO_DATE != '' && $NEW_STUDENTS_TO_DATE != '0000-00-00' )
		$COND = " AND STATUS_DATE BETWEEN '$NEW_STUDENTS_FROM_DATE' AND '$NEW_STUDENTS_TO_DATE' ";
	else if($NEW_STUDENTS_FROM_DATE != '' && $NEW_STUDENTS_FROM_DATE != '0000-00-00')
		$COND = " AND STATUS_DATE >= '$NEW_STUDENTS_FROM_DATE' ";
	else if($NEW_STUDENTS_TO_DATE != '' && $NEW_STUDENTS_TO_DATE != '0000-00-00')
		$COND = " AND STATUS_DATE <= '$NEW_STUDENTS_TO_DATE' ";
	
	$COND .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($NEW_STUDENTS_STATUS) ";
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
		<? if($_GET['t'] == 1) echo NEW_LEADS; 
		else if($_GET['t'] == 2) echo QUALIFIED_LEADS; 
		else if($_GET['t'] == 3) echo NEW_APPLICATIONS;
		else if($_GET['t'] == 4) echo NEW_STUDENTS;	?> | <?=$title?>
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
							<? if($_GET['t'] == 1) echo NEW_LEADS; 
							else if($_GET['t'] == 2) echo QUALIFIED_LEADS; 
							else if($_GET['t'] == 3) echo NEW_APPLICATIONS;
							else if($_GET['t'] == 4) echo NEW_STUDENTS;	?>
						</h4>
                    </div>
					<div class="col-md-4" style="text-align:right" >
						<button onclick="javascript:window.location.href = 'dashboard_report_pdf?t=<?=$_GET['t']?>'" type="button" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
						
						<button onclick="javascript:window.location.href = 'dashboard_report_excel?t=<?=$_GET['t']?>'" type="button" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
					</div>
				</div>
				
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12" style="max-height:600px;overflow-x: auto;overflow-y: auto;">
										<table data-toggle="table" data-height="500" data-mobile-responsive="true" class="table-striped" id="table_1" >
											<thead>
												<tr>
													<th>#</th>
													<th><?=STUDENT?></th>
													<th><?=STUDENT_ID?></th>
													<th><?=STUDENT_STATUS?></th>
													<th><?=STATUS_DATE?></th>
													<th><?=LEAD_SOURCE?></th>
													<th><?=FIRST_TERM_DATE?></th>
													<th><?=PROGRAM?></th>
													<th><?=ADMISSION_REP?></th>
												</tr>
											</thead>
											<tbody>
												<? $query = "SELECT CONCAT(S_STUDENT_MASTER.LAST_NAME,' ', S_STUDENT_MASTER.FIRST_NAME) AS STUDENT_NAME, STUDENT_ID, M_STUDENT_STATUS.STUDENT_STATUS AS STUDENT_STATUS, M_LEAD_SOURCE.LEAD_SOURCE, IF(S_STUDENT_ENROLLMENT.STATUS_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.STATUS_DATE,'%m/%d/%Y' )) AS STATUS_DATE,IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS TERM_DATE ,M_CAMPUS_PROGRAM.CODE, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS EMP_NAME 
												FROM 
												S_STUDENT_MASTER 
												LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
												, S_STUDENT_ENROLLMENT 
												LEFT JOIN M_LEAD_SOURCE ON S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE = M_LEAD_SOURCE.PK_LEAD_SOURCE 
												LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
												LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
												LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
												LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE 
												WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER  AND ARCHIVED = 0 $COND 
												ORDER BY S_STUDENT_MASTER.LAST_NAME ASC, S_STUDENT_MASTER.FIRST_NAME ASC ";
												$_SESSION['REPORT_QUERY'] = $query;
												$res_type = $db->Execute($query);
												//echo $query;exit;
												$i = 0;
												while (!$res_type->EOF) { 
													$i++;  ?>
													<tr>
														<td><?=$i?></td>
														<td><?=$res_type->fields['STUDENT_NAME']?></td>
														<td><?=$res_type->fields['STUDENT_ID']?></td>
														<td><?=$res_type->fields['STUDENT_STATUS']?></td>
														<td><?=$res_type->fields['STATUS_DATE']?></td>
														<td><?=$res_type->fields['LEAD_SOURCE']?></td>
														<td><?=$res_type->fields['TERM_DATE']?></td>
														<td><?=$res_type->fields['CODE']?></td>
														<td><?=$res_type->fields['EMP_NAME']?></td>
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
	<script type="text/javascript" >

	</script>
</body>

</html>