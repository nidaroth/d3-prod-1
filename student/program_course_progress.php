<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/program_course_progress.php");
require_once("../language/menu.php");

//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_USER_TYPE'] != 3 ){ 
	header("location:../index");
	exit;
}

if($_GET['t'] == '') {
	if($_SESSION['PK_STUDENT_ENROLLMENT'] == 0 || $_SESSION['PK_STUDENT_ENROLLMENT'] == ''){
		$res_en = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM `S_STUDENT_ENROLLMENT` WHERE IS_ACTIVE_ENROLLMENT = 1 AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]'");
		$_SESSION['PK_STUDENT_ENROLLMENT'] = $res_en->fields['PK_STUDENT_ENROLLMENT'];
	}
	$_GET['t'] = $_SESSION['PK_STUDENT_ENROLLMENT'];
}



$res_en = $db->Execute("SELECT PK_CAMPUS_PROGRAM FROM `S_STUDENT_ENROLLMENT` WHERE PK_STUDENT_ENROLLMENT = '$_GET[t]'");
$PK_CAMPUS_PROGRAM = $res_en->fields['PK_CAMPUS_PROGRAM'];

$res_course = $db->Execute("SELECT GROUP_CONCAT(PK_COURSE) as PK_COURSE FROM M_CAMPUS_PROGRAM_COURSE WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND ACTIVE = 1 ");
$PK_COURSE_ACT = $res_course->fields['PK_COURSE'];

$res_course = $db->Execute("SELECT GROUP_CONCAT(PK_COURSE) as PK_COURSE FROM M_CAMPUS_PROGRAM_COURSE WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND ACTIVE = 0 ");
$PK_COURSE_INACT = $res_course->fields['PK_COURSE'];

$res_en = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER, PK_STUDENT_ENROLLMENT, CONCAT(FIRST_NAME,', ',LAST_NAME) AS NAME, STUDENT_ID, STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE,SESSION, BEGIN_DATE as BEGIN_DATE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%m/%d/%Y' )) AS LDA, M_ENROLLMENT_STATUS.DESCRIPTION AS ENROLLMENT_STATUS, UNITS, HOURS, S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM  FROM 
S_STUDENT_ENROLLMENT 
LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION 
LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS 
LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
, S_STUDENT_MASTER 
LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$_GET[t]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER ");

if($res_en->RecordCount() == 0){
	header("location:program_course_progress");
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
	<title><?=MNU_PROGRAM_COURSE_PROGRESS?> | <?=$title?></title>
	<style>
	.table th, .table td {
	  padding: 0.5rem;
	}
	</style>
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
							<?=MNU_PROGRAM_COURSE_PROGRESS?>
						</h4>
                    </div>
                </div>	
				
				<form class="floating-labels" method="get" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="card-group">
						<div class="card">
							<div class="card-body">
								<div class="row">
									<div class="col-md-9" style="text-align:right" ></div>
									<div class="col-md-2" style="text-align:right" >
										<select id="t" name="t" class="form-control" onchange="set_filter()" >
											<? $res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
											while (!$res_type->EOF) { ?>
												<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT'] ?>" <? if($_GET['t'] == $res_type->fields['PK_STUDENT_ENROLLMENT']) echo "selected"; ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['STUDENT_STATUS']?></option>
											<?	$res_type->MoveNext();
											} ?>
										</select>
									</div>
									<div class="col-md-1" style="text-align:right" >
										<a href="program_course_progress_pdf?t=<?=$_GET['t']?>" class="btn waves-effect waves-light btn-info" style="margin-bottom:5px" ><?=PDF?></a>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<table class="table-striped table table-hover" style="width:100%" >
											<thead>
												<tr>
													<td ><b><br />Term</b></td>
													<td ><b><br />Course</b></td>
													<td ><b>Course<br />Description</b></td>
													<td ><div style="text-align:right" ><b>Units<br />Required</b></td>
													<td ><div style="text-align:right" ><b>Units<br />Attempted</b></div></td>
													<td ><div style="text-align:right" ><b>Units<br />In Progress</b></div></td>
													<td ><div style="text-align:right" ><b>Units<br />Completed</b></div></td>
													<td ><div style="text-align:right" ><b><br />Grade</b></div></td>
													<td ><div style="text-align:right" ><b>Numeric<br />Grade</b></div></td>
													<td ><div style="text-align:right" ><b>Numeric<br />GPA</b></div></td>
												</tr>
											</thead>
											
											<tr >
												<td colspan="10" ><i style="font-size:25px">Program Courses - Passed</i></td>
											</tr>
											
											<? $GRAND_TOT_ATTEMPTED 	 = 0;
											$GRAND_TOT_IN_PROGRESS 	 = 0;
											$GRAND_TOT_COMPLETED 	 = 0;
											$GRAND_TOT_REQUIRED 	 = 0;

											$GRAND_TOT_Denominator 	 = 0;
											$GRAND_TOT_Numerator	 = 0;
											$GRAND_TOT_Numerator1	 = 0;
											$GRAND_TOT_GPA	 		 = 0;	
											$GRAND_TOT_NO_GPA	 	 = 0;

											$TOT_ATTEMPTED 	 = 0;
											$TOT_IN_PROGRESS = 0;
											$TOT_COMPLETED 	 = 0;
											$TOT_REQUIRED 	 = 0;

											$TOT_Denominator = 0;
											$TOT_Numerator	 = 0;
											$TOT_Numerator1	 = 0;
											$TOT_GPA	 	 = 0;
											$TOT_NO_GPA	 	 = 0;
		
											$assigned_co = array();
											$res_course_schedule = $db->Execute("SELECT * FROM (
												select S_COURSE.PK_COURSE, PK_STUDENT_COURSE, COURSE_UNITS, COURSE_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, BEGIN_DATE, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, GRADE, CALCULATE_GPA, NUMBER_GRADE  from S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, S_COURSE_OFFERING, S_COURSE, S_GRADE WHERE S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$_GET[t]' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND FINAL_GRADE = S_GRADE.PK_GRADE AND UNITS_COMPLETED = 1 AND S_COURSE.PK_COURSE IN ($PK_COURSE_ACT) 
												UNION 
												select S_COURSE.PK_COURSE, '' as PK_STUDENT_COURSE, S_STUDENT_CREDIT_TRANSFER.UNITS as COURSE_UNITS, S_COURSE.COURSE_CODE, S_COURSE.COURSE_DESCRIPTION, 'Transfer' AS BEGIN_DATE_1, '0000-00-00' as BEGIN_DATE, S_GRADE.UNITS_ATTEMPTED, S_GRADE.UNITS_COMPLETED, S_GRADE.UNITS_IN_PROGRESS, S_GRADE.GRADE, S_GRADE.CALCULATE_GPA, S_GRADE.NUMBER_GRADE  from S_STUDENT_CREDIT_TRANSFER, S_COURSE, S_GRADE WHERE S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT = '$_GET[t]' AND S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER AND S_STUDENT_CREDIT_TRANSFER.PK_GRADE = S_GRADE.PK_GRADE AND S_GRADE.UNITS_COMPLETED = 1 AND S_COURSE.PK_COURSE IN ($PK_COURSE_ACT) 
												) 
											AS TEMP ORDER BY BEGIN_DATE ASC, COURSE_CODE ASC");
											while (!$res_course_schedule->EOF) {
												$assigned_co[$res_course_schedule->fields['PK_COURSE']] = $res_course_schedule->fields['PK_COURSE'];
												
												$REQUIRED = $res_course_schedule->fields['COURSE_UNITS'];
												
												$TOT_REQUIRED 		+= $res_course_schedule->fields['COURSE_UNITS'];
												$GRAND_TOT_REQUIRED += $res_course_schedule->fields['COURSE_UNITS'];
												
												$ATTEMPTED = '';
												if($res_course_schedule->fields['UNITS_ATTEMPTED'] == 1) {
													$ATTEMPTED 				 = $res_course_schedule->fields['COURSE_UNITS'];
													$TOT_ATTEMPTED 			+= $res_course_schedule->fields['COURSE_UNITS'];
													$GRAND_TOT_ATTEMPTED 	+= $res_course_schedule->fields['COURSE_UNITS'];
												}
												
												$IN_PROGRESS = '';
												if($res_course_schedule->fields['UNITS_IN_PROGRESS'] == 1) {
													$IN_PROGRESS 	  		 = $res_course_schedule->fields['COURSE_UNITS'];
													$TOT_IN_PROGRESS 		+= $res_course_schedule->fields['COURSE_UNITS'];
													$GRAND_TOT_IN_PROGRESS 	+= $res_course_schedule->fields['COURSE_UNITS'];
												}
												
												$COMPLETED = '';
												if($res_course_schedule->fields['UNITS_COMPLETED'] == 1) {
													$COMPLETED 			 = $res_course_schedule->fields['COURSE_UNITS'];
													$TOT_COMPLETED 		 += $res_course_schedule->fields['COURSE_UNITS'];
													$GRAND_TOT_COMPLETED += $res_course_schedule->fields['COURSE_UNITS'];
												}
												
												$Numerator1  = 0;
												$Denominator = 0;
												if($res_course_schedule->fields['CALCULATE_GPA'] == 1) {
													$Numerator1	 = $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
													$Denominator = $res_course_schedule->fields['COURSE_UNITS'];
													
													$TOT_Denominator += $res_course_schedule->fields['COURSE_UNITS'];
													$TOT_Numerator	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMERIC_GRADE'];
													$TOT_Numerator1	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
													
													$GRAND_TOT_Denominator 	 += $res_course_schedule->fields['COURSE_UNITS'];
													$GRAND_TOT_Numerator	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMERIC_GRADE'];
													$GRAND_TOT_Numerator1	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
													
													$GRAND_TOT_NO_GPA++;
													$TOT_NO_GPA++;
													
													$GPA = 0;
													if($Numerator1 > 0 && $Denominator > 0)
														$GPA = $Numerator1/$Denominator;
														
													$TOT_GPA 		+= $GPA;
													$GRAND_TOT_GPA 	+= $GPA;
													
													$GPA = number_format_value_checker($GPA,2);
												} else
													$GPA = '';
												?>
												<tr >
													<td ><?=$res_course_schedule->fields['BEGIN_DATE_1'] ?></td>
													<td ><?=$res_course_schedule->fields['COURSE_CODE'] ?></td>
													<td ><?=$res_course_schedule->fields['COURSE_DESCRIPTION'] ?></td>
													<td ><div style="text-align:right" ><?=$res_course_schedule->fields['COURSE_UNITS'] ?></div></td>
													<td ><div style="text-align:right" ><?=$ATTEMPTED ?></div></td>
													<td ><div style="text-align:right" ><?=$IN_PROGRESS ?></div></td>
													<td ><div style="text-align:right" ><?=$COMPLETED ?></div></td>
													<td ><div style="text-align:right" ><?=$res_course_schedule->fields['GRADE'] ?></div></td>
													<td ><div style="text-align:right" ><?=$res_course_schedule->fields['NUMBER_GRADE'] ?></div></td>
													<td ><div style="text-align:right" ><?=$GPA ?></div></td>
												</tr>
				
											<? $res_course_schedule->MoveNext();
											}
									
											$GPA = 0;
											if($TOT_GPA > 0 && $TOT_NO_GPA > 0)
												$GPA = number_format_value_checker(($TOT_GPA/$TOT_NO_GPA),2);
											?>
		
											<tr >
												<td ></td>
												<td ></td>
												<td ></td>
												<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($TOT_REQUIRED,2)?></div></td>
												<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($TOT_ATTEMPTED,2)?></div></td>
												<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($TOT_IN_PROGRESS,2)?></div></td>
												<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($TOT_COMPLETED,2)?></div></td>
												<td style="border-top: 1px solid #000;" ></td>
												<td style="border-top: 1px solid #000;" ></td>
												<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=$GPA?></div></td>
											</tr>
												
											<tr >
												<td colspan="10" ><i style="font-size:25px">Program Courses - Not Passed</i></td>
											</tr>

											<? $TOT_ATTEMPTED 	 = 0;
											$TOT_IN_PROGRESS = 0;
											$TOT_COMPLETED 	 = 0;
											$TOT_REQUIRED 	 = 0;
											
											$TOT_Denominator = 0;
											$TOT_Numerator	 = 0;
											$TOT_Numerator1	 = 0;
											
											$TOT_GPA 	= 0;
											$TOT_NO_GPA = 0;
											
											$res_course_schedule = $db->Execute("SELECT * FROM (
												select S_COURSE.PK_COURSE, PK_STUDENT_COURSE, COURSE_UNITS, COURSE_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, BEGIN_DATE, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, GRADE, CALCULATE_GPA, NUMBER_GRADE  from S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, S_COURSE_OFFERING, S_COURSE, S_GRADE WHERE S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$_GET[t]' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND FINAL_GRADE = S_GRADE.PK_GRADE AND UNITS_ATTEMPTED = 1 AND (UNITS_COMPLETED = 0 OR IS_DEFAULT = 1) AND S_COURSE.PK_COURSE IN ($PK_COURSE_ACT) 
												UNION 
												select S_COURSE.PK_COURSE, '' as PK_STUDENT_COURSE, S_STUDENT_CREDIT_TRANSFER.UNITS as COURSE_UNITS, S_COURSE.COURSE_CODE, S_COURSE.COURSE_DESCRIPTION, 'Transfer' AS BEGIN_DATE_1, '0000-00-00' as BEGIN_DATE, S_GRADE.UNITS_ATTEMPTED, S_GRADE.UNITS_COMPLETED, S_GRADE.UNITS_IN_PROGRESS, S_GRADE.GRADE, S_GRADE.CALCULATE_GPA, S_GRADE.NUMBER_GRADE  from S_STUDENT_CREDIT_TRANSFER, S_COURSE, S_GRADE WHERE S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT = '$_GET[t]' AND S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER AND S_STUDENT_CREDIT_TRANSFER.PK_GRADE = S_GRADE.PK_GRADE AND S_GRADE.UNITS_ATTEMPTED = 1 AND (S_GRADE.UNITS_COMPLETED = 0 OR S_GRADE.IS_DEFAULT = 1) AND S_COURSE.PK_COURSE IN ($PK_COURSE_ACT) 
												) 
											AS TEMP ORDER BY BEGIN_DATE ASC, COURSE_CODE ASC");
											
											//$res_course_schedule = $db->Execute("select S_COURSE.PK_COURSE, PK_STUDENT_COURSE, COURSE_UNITS, COURSE_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, BEGIN_DATE, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, GRADE, CALCULATE_GPA, NUMBER_GRADE  from S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, S_COURSE_OFFERING, S_COURSE, S_GRADE WHERE S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$_GET[t]' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND FINAL_GRADE = S_GRADE.PK_GRADE AND UNITS_ATTEMPTED = 1 AND (UNITS_COMPLETED = 0 OR IS_DEFAULT = 1) AND S_COURSE.PK_COURSE IN ($PK_COURSE_ACT) AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY BEGIN_DATE ASC, COURSE_CODE ASC");
											while (!$res_course_schedule->EOF) {
												$assigned_co[$res_course_schedule->fields['PK_COURSE']] = $res_course_schedule->fields['PK_COURSE'];
												
												$REQUIRED = $res_course_schedule->fields['COURSE_UNITS'];
												
												$TOT_REQUIRED 		+= $res_course_schedule->fields['COURSE_UNITS'];
												$GRAND_TOT_REQUIRED += $res_course_schedule->fields['COURSE_UNITS'];
												
												$ATTEMPTED = '';
												if($res_course_schedule->fields['UNITS_ATTEMPTED'] == 1) {
													$ATTEMPTED 				= $res_course_schedule->fields['COURSE_UNITS'];
													$TOT_ATTEMPTED 			+= $res_course_schedule->fields['COURSE_UNITS'];
													$GRAND_TOT_ATTEMPTED 	+= $res_course_schedule->fields['COURSE_UNITS'];
												}
												
												$IN_PROGRESS = '';
												if($res_course_schedule->fields['UNITS_IN_PROGRESS'] == 1) {
													$IN_PROGRESS 	  		 = $res_course_schedule->fields['COURSE_UNITS'];
													$TOT_IN_PROGRESS 		+= $res_course_schedule->fields['COURSE_UNITS'];
													$GRAND_TOT_IN_PROGRESS 	+= $res_course_schedule->fields['COURSE_UNITS'];
												}
												
												$COMPLETED = '';
												if($res_course_schedule->fields['UNITS_COMPLETED'] == 1) {
													$COMPLETED 			 	= $res_course_schedule->fields['COURSE_UNITS'];
													$TOT_COMPLETED 		 	+= $res_course_schedule->fields['COURSE_UNITS'];
													$GRAND_TOT_COMPLETED 	+= $res_course_schedule->fields['COURSE_UNITS'];
												}
												
												$Numerator1  = 0;
												$Denominator = 0;
												if($res_course_schedule->fields['CALCULATE_GPA'] == 1) {
													$Numerator1	 = $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
													$Denominator = $res_course_schedule->fields['COURSE_UNITS'];
													
													$TOT_Denominator += $res_course_schedule->fields['COURSE_UNITS'];
													$TOT_Numerator	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMERIC_GRADE'];
													$TOT_Numerator1	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
													
													$GRAND_TOT_Denominator 	 += $res_course_schedule->fields['COURSE_UNITS'];
													$GRAND_TOT_Numerator	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMERIC_GRADE'];
													$GRAND_TOT_Numerator1	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
													
													$GRAND_TOT_NO_GPA++;
													$TOT_NO_GPA++;
													
													$GPA = 0;
													if($Numerator1 > 0 && $Denominator > 0)
														$GPA = $Numerator1/$Denominator;
														
													$TOT_GPA 		+= $GPA;
													$GRAND_TOT_GPA 	+= $GPA;
														
													$GPA = number_format_value_checker($GPA, 2);
												} else
													$GPA = '';
												?>
												<tr >
													<td ><?=$res_course_schedule->fields['BEGIN_DATE_1']?></td>
													<td ><?=$res_course_schedule->fields['COURSE_CODE']?></td>
													<td ><?=$res_course_schedule->fields['COURSE_DESCRIPTION']?></td>
													<td ><div style="text-align:right" ><?=$res_course_schedule->fields['COURSE_UNITS']?></div></td>
													<td ><div style="text-align:right" ><?=$ATTEMPTED?></div></td>
													<td ><div style="text-align:right" ><?=$IN_PROGRESS?></div></td>
													<td ><div style="text-align:right" ><?=$COMPLETED?></div></td>
													<td ><div style="text-align:right" ><?=$res_course_schedule->fields['GRADE']?></div></td>
													<td ><div style="text-align:right" ><?=$res_course_schedule->fields['NUMBER_GRADE']?></div></td>
													<td ><div style="text-align:right" ><?=$GPA?></div></td>
												</tr>
													
											<?	$res_course_schedule->MoveNext();
											}

											$PK_COURSE_ACT_ARR 	= explode(",",$PK_COURSE_ACT );
											$not_assigned_co 	= array();
											
											foreach($PK_COURSE_ACT_ARR as $PK_COURSE_ACT1){
												$found = 0;
												foreach($assigned_co as $assigned_co1) {
													if($assigned_co1 == $PK_COURSE_ACT1){
														$found = 1;
													}
												}
												if($found == 0) {
													$not_assigned_co[] = $PK_COURSE_ACT1;
												}
											}
											$not_assigned_co1 = implode(",",$not_assigned_co);

											$res_course_schedule = $db->Execute("select UNITS, COURSE_CODE, COURSE_DESCRIPTION from S_COURSE WHERE S_COURSE.PK_COURSE IN ($not_assigned_co1) AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY COURSE_CODE ASC");
											while (!$res_course_schedule->EOF) {
												
												$TOT_REQUIRED 		+= $res_course_schedule->fields['UNITS'];
												$GRAND_TOT_REQUIRED += $res_course_schedule->fields['UNITS'];
												?>
												<tr >
													<td ></td>
													<td ><?=$res_course_schedule->fields['COURSE_CODE']?></td>
													<td ><?=$res_course_schedule->fields['COURSE_DESCRIPTION']?></td>
													<td ><div style="text-align:right" ><?=$res_course_schedule->fields['UNITS']?></div></td>
													<td ></td>
													<td ></td>
													<td ></td>
													<td ></td>
													<td ></td>
													<td ></td>
												</tr>
													
											<?	$res_course_schedule->MoveNext();
											}
	
											/*$GPA = 0;
											if($TOT_GPA > 0 && $TOT_NO_GPA > 0)
												$GPA = $TOT_GPA/$TOT_NO_GPA;
											$GPA = number_format_value_checker($GPA, 2);*/
											
											$GPA = '';
											?>
											<tr >
												<td  ></td>
												<td ></td>
												<td ></td>
												<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($TOT_REQUIRED,2)?></div></td>
												<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($TOT_ATTEMPTED,2)?></div></td>
												<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($TOT_IN_PROGRESS,2)?></div></td>
												<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($TOT_COMPLETED,2)?></div></td>
												<td style="border-top: 1px solid #000;" ></td>
												<td style="border-top: 1px solid #000;" ></td>
												<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=$GPA?></div></td>
											</tr>
	
										<? $GPA = 0;
										if($GRAND_TOT_GPA > 0 && $GRAND_TOT_NO_GPA > 0)
											$GPA = $GRAND_TOT_GPA/$GRAND_TOT_NO_GPA;
										$GPA = number_format_value_checker($GPA, 2); ?>
										
										<tr >
											<td >Program Course Totals:</td>
											<td ></td>
											<td ></td>
											<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($GRAND_TOT_REQUIRED,2)?></div></td>
											<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($GRAND_TOT_ATTEMPTED,2)?></div></td>
											<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($GRAND_TOT_IN_PROGRESS,2)?></div></td>
											<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($GRAND_TOT_COMPLETED,2)?></div></td>
											<td style="border-top: 1px solid #000;" ></td>
											<td style="border-top: 1px solid #000;" ></td>
											<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=$GPA?></div></td>
										</tr>
			
										<? $PURSUIT_TOT_ATTEMPTED 	 = $GRAND_TOT_ATTEMPTED;
										$PURSUIT_TOT_IN_PROGRESS = $GRAND_TOT_IN_PROGRESS;
										$PURSUIT_TOT_COMPLETED 	 = $GRAND_TOT_COMPLETED;
										$PURSUIT_TOT_REQUIRED 	 = $GRAND_TOT_REQUIRED;
										
										$PURSUIT_TOT_Denominator = $GRAND_TOT_Denominator;
										$PURSUIT_TOT_Numerator	 = $GRAND_TOT_Numerator;
										$PURSUIT_TOT_Numerator1	 = $GRAND_TOT_Numerator1;
										
										$PURSUIT_TOT_GPA 	= $GRAND_TOT_GPA;
										$PURSUIT_TOT_NO_GPA = $GRAND_TOT_NO_GPA;
										
										$TOT_ATTEMPTED 	 = 0;
										$TOT_IN_PROGRESS = 0;
										$TOT_COMPLETED 	 = 0;
										$TOT_REQUIRED 	 = 0;
										
										$TOT_GPA 	= 0;
										$TOT_NO_GPA = 0;
										
										$TOT_Denominator = 0;
										$TOT_Numerator	 = 0;
										$TOT_Numerator1	 = 0;
										?>
										<tr >
											<td colspan="10" ><i style="font-size:25px">Program Courses - Inactive Requirements</i></td>
										</tr>
	
									<? $res_course_schedule = $db->Execute("SELECT * FROM (
										select S_COURSE.PK_COURSE, PK_STUDENT_COURSE, COURSE_UNITS, COURSE_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, BEGIN_DATE, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, GRADE, CALCULATE_GPA, NUMBER_GRADE  from S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, S_COURSE_OFFERING, S_COURSE, S_GRADE WHERE S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$_GET[t]' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND FINAL_GRADE = S_GRADE.PK_GRADE AND S_COURSE.PK_COURSE IN ($PK_COURSE_INACT) 
										UNION 
										select S_COURSE.PK_COURSE, '' as PK_STUDENT_COURSE, S_STUDENT_CREDIT_TRANSFER.UNITS as COURSE_UNITS, S_COURSE.COURSE_CODE, S_COURSE.COURSE_DESCRIPTION, 'Transfer' AS BEGIN_DATE_1, '0000-00-00' as BEGIN_DATE, S_GRADE.UNITS_ATTEMPTED, S_GRADE.UNITS_COMPLETED, S_GRADE.UNITS_IN_PROGRESS, S_GRADE.GRADE, S_GRADE.CALCULATE_GPA, S_GRADE.NUMBER_GRADE  from S_STUDENT_CREDIT_TRANSFER, S_COURSE, S_GRADE WHERE S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT = '$_GET[t]' AND S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER AND S_STUDENT_CREDIT_TRANSFER.PK_GRADE = S_GRADE.PK_GRADE AND S_COURSE.PK_COURSE IN ($PK_COURSE_INACT) 
										) 
									AS TEMP ORDER BY BEGIN_DATE ASC, COURSE_CODE ASC");
									
									//$res_course_schedule = $db->Execute("select S_COURSE.PK_COURSE, PK_STUDENT_COURSE, COURSE_UNITS, COURSE_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, BEGIN_DATE, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, GRADE, CALCULATE_GPA, NUMBER_GRADE  from S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, S_COURSE_OFFERING, S_COURSE, S_GRADE WHERE S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = '$_GET[t]' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND FINAL_GRADE = S_GRADE.PK_GRADE AND S_COURSE.PK_COURSE IN ($PK_COURSE_INACT) AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY BEGIN_DATE ASC, COURSE_CODE ASC");
									while (!$res_course_schedule->EOF) {

										$TOT_REQUIRED 		+= $res_course_schedule->fields['COURSE_UNITS'];
										$GRAND_TOT_REQUIRED += $res_course_schedule->fields['COURSE_UNITS'];
										
										$ATTEMPTED = '';
										if($res_course_schedule->fields['UNITS_ATTEMPTED'] == 1) {
											$ATTEMPTED 				 = $res_course_schedule->fields['COURSE_UNITS'];
											$TOT_ATTEMPTED 			+= $res_course_schedule->fields['COURSE_UNITS'];
											$GRAND_TOT_ATTEMPTED 	+= $res_course_schedule->fields['COURSE_UNITS'];
										}
										
										$IN_PROGRESS = '';
										if($res_course_schedule->fields['UNITS_IN_PROGRESS'] == 1) {
											$IN_PROGRESS 	  		 = $res_course_schedule->fields['COURSE_UNITS'];
											$TOT_IN_PROGRESS 		+= $res_course_schedule->fields['COURSE_UNITS'];
											$GRAND_TOT_IN_PROGRESS 	+= $res_course_schedule->fields['COURSE_UNITS'];
										}
										
										$COMPLETED = '';
										if($res_course_schedule->fields['UNITS_COMPLETED'] == 1) {
											$COMPLETED 			 = $res_course_schedule->fields['COURSE_UNITS'];
											$TOT_COMPLETED 		 += $res_course_schedule->fields['COURSE_UNITS'];
											$GRAND_TOT_COMPLETED += $res_course_schedule->fields['COURSE_UNITS'];
										}
										
										$Numerator1  = 0;
										$Denominator = 0;
										if($res_course_schedule->fields['CALCULATE_GPA'] == 1) {
											$Numerator1	 = $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
											$Denominator = $res_course_schedule->fields['COURSE_UNITS'];
											
											$TOT_Denominator += $res_course_schedule->fields['COURSE_UNITS'];
											$TOT_Numerator	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMERIC_GRADE'];
											$TOT_Numerator1	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
											
											$TOT_NO_GPA++;
											
											$GPA = 0;
											if($Numerator1 > 0 && $Denominator > 0)
												$GPA = $Numerator1/$Denominator;
												
											$TOT_GPA += $GPA;
										} else
											$GPA = '';
										?>
										
										<tr >
											<td ><?=$res_course_schedule->fields['BEGIN_DATE_1']?></td>
											<td ><?=$res_course_schedule->fields['COURSE_CODE']?></td>
											<td ><?=$res_course_schedule->fields['COURSE_DESCRIPTION']?></td>
											<td ><div style="text-align:right" ><?=$res_course_schedule->fields['COURSE_UNITS']?></div></td>
											<td ><div style="text-align:right" ><?=$ATTEMPTED?></div></td>
											<td ><div style="text-align:right" ><?=$IN_PROGRESS?></div></td>
											<td ><div style="text-align:right" ><?=$COMPLETED?></div></td>
											<td ><div style="text-align:right" ><?=$res_course_schedule->fields['GRADE']?></div></td>
											<td ><div style="text-align:right" ><?=$res_course_schedule->fields['NUMBER_GRADE']?></div></td>
											<td ><div style="text-align:right" ><?=$GPA?></div></td>
										</tr>
											
									<?	$res_course_schedule->MoveNext();
									}
	
									$GPA = 0;
									if($TOT_GPA > 0 && $TOT_NO_GPA > 0)
										$GPA = $TOT_GPA/$TOT_NO_GPA;
									$GPA = number_format_value_checker($GPA, 2);
									?>
									<tr >
										<td >Inactive Requirement Totals:</td>
										<td ></td>
										<td ></td>
										<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($TOT_REQUIRED,2)?></div></td>
										<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($TOT_ATTEMPTED,2)?></div></td>
										<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($TOT_IN_PROGRESS,2)?></div></td>
										<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($TOT_COMPLETED,2)?></div></td>
										<td style="border-top: 1px solid #000;" ></td>
										<td style="border-top: 1px solid #000;" ></td>
										<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=$GPA?></div></td>
									</tr>
									<?		
									//////////////////////	
									$TOT_ATTEMPTED 	 = 0;
									$TOT_IN_PROGRESS = 0;
									$TOT_COMPLETED 	 = 0;
									$TOT_REQUIRED 	 = 0;
									
									$TOT_GPA 	= 0;
									$TOT_NO_GPA = 0;
									
									$TOT_Denominator = 0;
									$TOT_Numerator	 = 0;
									$TOT_Numerator1	 = 0;
	
									$res_course = $db->Execute("SELECT GROUP_CONCAT(PK_COURSE) as PK_COURSE FROM M_CAMPUS_PROGRAM_COURSE WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' ");
									$PK_PROG_COURSE_ALL = $res_course->fields['PK_COURSE'];
									?>
									<tr >
										<td colspan="10" ><i style="font-size:25px">Non Program Courses</i></td>
									</tr>
			
								<? /* UNION 
										select S_COURSE.PK_COURSE, PK_STUDENT_COURSE, COURSE_UNITS, COURSE_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, BEGIN_DATE, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, GRADE, CALCULATE_GPA, NUMBER_GRADE  from S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, S_COURSE_OFFERING, S_COURSE, S_GRADE WHERE S_STUDENT_COURSE.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT != '$_GET[t]' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND FINAL_GRADE = S_GRADE.PK_GRADE AND UNITS_ATTEMPTED = 1 AND (UNITS_COMPLETED = 0 OR IS_DEFAULT = 1) 
										UNION 
										select S_COURSE.PK_COURSE, '' as PK_STUDENT_COURSE, S_STUDENT_CREDIT_TRANSFER.UNITS as COURSE_UNITS, S_COURSE.COURSE_CODE, S_COURSE.COURSE_DESCRIPTION, 'Transfer' AS BEGIN_DATE_1, '0000-00-00' as BEGIN_DATE, S_GRADE.UNITS_ATTEMPTED, S_GRADE.UNITS_COMPLETED, S_GRADE.UNITS_IN_PROGRESS, S_GRADE.GRADE, S_GRADE.CALCULATE_GPA, S_GRADE.NUMBER_GRADE  from S_STUDENT_CREDIT_TRANSFER, S_COURSE, S_GRADE WHERE S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT != '$_GET[t]' AND S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER AND S_STUDENT_CREDIT_TRANSFER.PK_GRADE = S_GRADE.PK_GRADE AND S_GRADE.UNITS_ATTEMPTED = 1 AND (S_GRADE.UNITS_COMPLETED = 0 OR S_GRADE.IS_DEFAULT = 1) */
								$res_course_schedule = $db->Execute("SELECT * FROM (
										select S_COURSE.PK_COURSE, PK_STUDENT_COURSE, COURSE_UNITS, COURSE_CODE, COURSE_DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, BEGIN_DATE, UNITS_ATTEMPTED, UNITS_COMPLETED, UNITS_IN_PROGRESS, GRADE, CALCULATE_GPA, NUMBER_GRADE  from S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER, S_COURSE_OFFERING, S_COURSE, S_GRADE WHERE S_STUDENT_COURSE.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE AND FINAL_GRADE = S_GRADE.PK_GRADE  AND S_COURSE.PK_COURSE NOT IN ($PK_PROG_COURSE_ALL) 
										UNION 
										select S_COURSE.PK_COURSE, '' as PK_STUDENT_COURSE, S_STUDENT_CREDIT_TRANSFER.UNITS as COURSE_UNITS, S_COURSE.COURSE_CODE, S_COURSE.COURSE_DESCRIPTION, 'Transfer' AS BEGIN_DATE_1, '0000-00-00' as BEGIN_DATE, S_GRADE.UNITS_ATTEMPTED, S_GRADE.UNITS_COMPLETED, S_GRADE.UNITS_IN_PROGRESS, S_GRADE.GRADE, S_GRADE.CALCULATE_GPA, S_GRADE.NUMBER_GRADE  from S_STUDENT_CREDIT_TRANSFER, S_COURSE, S_GRADE WHERE S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER AND S_STUDENT_CREDIT_TRANSFER.PK_GRADE = S_GRADE.PK_GRADE  AND S_COURSE.PK_COURSE NOT IN ($PK_PROG_COURSE_ALL) 
										
										) 
									AS TEMP ORDER BY BEGIN_DATE ASC, COURSE_CODE ASC");
									
								while (!$res_course_schedule->EOF) {
									
									$REQUIRED = $res_course_schedule->fields['COURSE_UNITS'];
									
									$TOT_REQUIRED 		+= $res_course_schedule->fields['COURSE_UNITS'];
									
									$ATTEMPTED = '';
									if($res_course_schedule->fields['UNITS_ATTEMPTED'] == 1) {
										$ATTEMPTED 				 = $res_course_schedule->fields['COURSE_UNITS'];
										$TOT_ATTEMPTED 			+= $res_course_schedule->fields['COURSE_UNITS'];
									}
									
									$IN_PROGRESS = '';
									if($res_course_schedule->fields['UNITS_IN_PROGRESS'] == 1) {
										$IN_PROGRESS 	  		 = $res_course_schedule->fields['COURSE_UNITS'];
										$TOT_IN_PROGRESS 		+= $res_course_schedule->fields['COURSE_UNITS'];
									}
									
									$COMPLETED = '';
									if($res_course_schedule->fields['UNITS_COMPLETED'] == 1) {
										$COMPLETED 			 = $res_course_schedule->fields['COURSE_UNITS'];
										$TOT_COMPLETED 		 += $res_course_schedule->fields['COURSE_UNITS'];
									}
									
									$Numerator1  = 0;
									$Denominator = 0;
									if($res_course_schedule->fields['CALCULATE_GPA'] == 1) {
										$Numerator1	 = $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];
										$Denominator = $res_course_schedule->fields['COURSE_UNITS'];
										
										$TOT_Denominator += $res_course_schedule->fields['COURSE_UNITS'];
										$TOT_Numerator	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMERIC_GRADE'];
										$TOT_Numerator1	 += $res_course_schedule->fields['COURSE_UNITS'] * $res_course_schedule->fields['NUMBER_GRADE'];

										$TOT_NO_GPA++;
										
										$GPA = 0;
										if($Numerator1 > 0 && $Denominator > 0)
											$GPA = $Numerator1/$Denominator;
											
										$TOT_GPA 		+= $GPA;
										$GPA = number_format_value_checker($GPA,2);
									} else
										$GPA = '';
									?>
									<tr >
										<td ><?=$res_course_schedule->fields['BEGIN_DATE_1']?></td>
										<td ><?=$res_course_schedule->fields['COURSE_CODE']?></td>
										<td ><?=$res_course_schedule->fields['COURSE_DESCRIPTION']?></td>
										<td ><div style="text-align:right" ><?=$res_course_schedule->fields['COURSE_UNITS']?></div></td>
										<td ><div style="text-align:right" ><?=$ATTEMPTED?></div></td>
										<td ><div style="text-align:right" ><?=$IN_PROGRESS?></div></td>
										<td ><div style="text-align:right" ><?=$COMPLETED?></div></td>
										<td ><div style="text-align:right" ><?=$res_course_schedule->fields['GRADE']?></div></td>
										<td ><div style="text-align:right" ><?=$res_course_schedule->fields['NUMBER_GRADE']?></div></td>
										<td ><div style="text-align:right" ><?=$GPA?></div></td>
									</tr>
										
								<?	$res_course_schedule->MoveNext();
								}
	
								$GPA = 0;
								if($TOT_GPA > 0 && $TOT_NO_GPA > 0)
									$GPA = $TOT_GPA/$TOT_NO_GPA;
								$GPA = number_format_value_checker($GPA, 2);
								?>
								<tr >
									<td >Non Program Course Totals:</td>
									<td ></td>
									<td ></td>
									<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($TOT_REQUIRED,2)?></div></td>
									<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($TOT_ATTEMPTED,2)?></div></td>
									<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($TOT_IN_PROGRESS,2)?></div></td>
									<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker($TOT_COMPLETED,2)?></div></td>
									<td style="border-top: 1px solid #000;" ></td>
									<td style="border-top: 1px solid #000;" ></td>
									<td style="border-top: 1px solid #000;" ><div style="text-align:right" ><?=$GPA?></div></td>
								</tr>
			
								<?
								//////////////////////	
								
								$UNITS_150 = number_format_value_checker(($res_en->fields['UNITS'] * 1.5),2,'.','');
								
								$RESULT = '';
								if(($UNITS_150 - $GRAND_TOT_ATTEMPTED) >= ($res_en->fields['UNITS'] - $GRAND_TOT_COMPLETED))
									$RESULT = 'PASS';
								else
									$RESULT = 'FAIL';
								?>
								<tr >
									<td colspan="10" ><br /><br /></td>
								</tr>
								<tr >
									<td style="border-bottom: 1px solid #000;" ><b>Student Totals:</b></td>
									<td style="border-bottom: 1px solid #000;" ></td>
									<td style="border-bottom: 1px solid #000;" ></td>
									<td style="border-bottom: 1px solid #000;" ><b><div style="text-align:right" ><?=number_format_value_checker($GRAND_TOT_REQUIRED,2)?></div></b></td>
									<td style="border-bottom: 1px solid #000;" ><b><div style="text-align:right" ><?=number_format_value_checker($GRAND_TOT_ATTEMPTED,2)?></div></b></td>
									<td style="border-bottom: 1px solid #000;" ><b><div style="text-align:right" ><?=number_format_value_checker($GRAND_TOT_IN_PROGRESS,2)?></div></b></td>
									<td style="border-bottom: 1px solid #000;" ><b><div style="text-align:right" ><?=number_format_value_checker($GRAND_TOT_COMPLETED,2)?></div></b></td>
									<td style="border-bottom: 1px solid #000;" ></td>
									<td style="border-bottom: 1px solid #000;" ></td>
									<td style="border-bottom: 1px solid #000;" ></td>
								</tr>
								
								<tr >
									<td ><b>Pursuit of Program:</b></td>
									<td colspan="2" >Program Units</td>
									<td ><div style="text-align:right" ><?=number_format_value_checker($res_en->fields['UNITS'], 2)?></div></td>
									<td colspan="6" ></td>
								</tr>
								<tr >
									<td  ></td>
									<td colspan="2" style="border-bottom: 1px solid #000;"  >Program Units @ 150%</div></td>
									<td style="border-bottom: 1px solid #000;"  ><div style="text-align:right" ><?=number_format_value_checker($UNITS_150,2) ?></div></td>
									<td colspan="6" ></td>
								</tr>
								<tr >
									<td colspan="10" ><br /></td>
								</tr>
								<tr >
									<td ></td>
									<td colspan="2" >Program Units Completed:</td>
									<td ><div style="text-align:right" ><?=number_format_value_checker($GRAND_TOT_COMPLETED,2) ?></div></td>
									<td colspan="6" ></td>
								</tr>
								<tr >
									<td ></td>
									<td colspan="2" >Program Units  Attempted:</td>
									<td ><div style="text-align:right" ><?=number_format_value_checker($GRAND_TOT_ATTEMPTED,2) ?></div></td>
									<td colspan="6" ></td>
								</tr>
								<tr >
									<td ></td>
									<td colspan="2" >Program Units Remaining:</td>
									<td align="right" ><div style="text-align:right" ><?=number_format_value_checker(($res_en->fields['UNITS'] - $GRAND_TOT_COMPLETED),2) ?></div></td>
									<td colspan="6" ></td>
								</tr>
								<tr >
									<td ></td>
									<td colspan="2" style="border-bottom: 1px solid #000;"  >Pursuit of Program Remaining Units:</td>
									<td style="border-bottom: 1px solid #000;" ><div style="text-align:right" ><?=number_format_value_checker(($UNITS_150 - $GRAND_TOT_ATTEMPTED),2) ?></div></td>
									<td colspan="6" ></td>
								</tr>
								<tr >
									<td ></td>
									<td colspan="2" >Pursuit of Program Status:</td>
									<td align="right" ><div style="text-align:right" ><?=$RESULT ?></div></td>
									<td colspan="6" ></td>
								</tr>';
										
											
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
    <? require_once("js.php");  ?>
	<script type="text/javascript" >
		function set_filter(){
			window.location.href = "program_course_progress?t="+document.getElementById('t').value;
		}
	</script>
	
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
</body>
</html>