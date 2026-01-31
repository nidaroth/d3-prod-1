<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/academic_review.php");
require_once("../language/menu.php");

//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_USER_TYPE'] != 3 ){ 
	header("location:../index");
	exit;
}

$res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1");
$PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
$_SESSION['eid'] = '';

//ticket #1240
$res_sp_set = $db->Execute("SELECT GRADE_DISPLAY_TYPE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
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
	<title><?=ACADEMIC_REVIEW_PAGE_TITLE?> | <?=$title?></title>
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
							<?=ACADEMIC_REVIEW_PAGE_TITLE?>
						</h4>
                    </div>
                </div>	
				
				<form class="floating-labels" method="get" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="card-group">
						<div class="card">
							<div class="card-body">
								<div class="row">
									<div class="col-md-12" style="text-align:right" >
										<a href="../school/student_transcript_pdf" class="btn waves-effect waves-light btn-info" style="margin-bottom:5px" ><?=PDF?></a>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-12">
										<table data-toggle="table" data-mobile-responsive="true" class="table-striped" >
											<thead>
												<tr>
													<th ><?=COURSE?></th>
													<th ><?=COURSE_DESCRIPTION ?></th>
													
													<!-- Ticket # 1240 -->
													<? if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?>
															<th ><div style="text-align:right" ><?=GRADE?></div></th>
													<? } 
													if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?>
														<th ><div style="text-align:right" ><?=NUMERIC_GRADE?></div></th>
													<? } ?>
													<!-- Ticket # 1240 -->
													
													<th ><div style="text-align:right" ><?=UNITS_ATTEMPTED?></div></th>
													<th ><div style="text-align:right" ><?=UNITS_COMPLETED?></div></th>
													<th ><div style="text-align:right" ><?=GPA?></div></th>
												</tr>
											</thead>
											<tbody>
												<? $c_in_num_grade_tot = 0; //ticket #1240
												$c_in_att_tot 	= 0;
												$c_in_comp_tot 	= 0;
												$c_in_cu_gnu 	= 0;
												$c_in_gpa_tot 	= 0;

												// DIAM-2351
												$summation_of_gpa    = 0;
												$summation_of_weight = 0;
												// End DIAM-2351

												$res_tc = $db->Execute("SELECT 
																			S_COURSE.COURSE_CODE, 
																			CREDIT_TRANSFER_STATUS, 
																			S_COURSE.COURSE_DESCRIPTION, 
																			S_STUDENT_CREDIT_TRANSFER.UNITS, 
																			S_COURSE.FA_UNITS, 
																			S_GRADE.GRADE, 
																			PK_STUDENT_ENROLLMENT, 
																			S_STUDENT_CREDIT_TRANSFER.PK_GRADE, 
																			S_GRADE.NUMBER_GRADE, 
																			S_GRADE.CALCULATE_GPA, 
																			S_GRADE.UNITS_ATTEMPTED, 
																			S_GRADE.UNITS_COMPLETED, 
																			S_GRADE.UNITS_IN_PROGRESS, 
																			TC_NUMERIC_GRADE,
																			CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE  ELSE  0 END AS GPA_VALUE, 
																			CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN  POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)  ELSE  0 END AS GPA_WEIGHT
																		  FROM 
																			S_STUDENT_CREDIT_TRANSFER 
																			LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE 
																			LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER 
																			LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS 
																		  WHERE 
																			S_STUDENT_CREDIT_TRANSFER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
																			AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' 
																		  ORDER BY 
																			S_COURSE.COURSE_CODE ASC "); // DIAM-2351
												if($res_tc->RecordCount() > 0) { ?>
													<tr>
														<td <? if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?> colspan="7" <? } else { ?> colspan="6" <? } ?> ><i style="font-size:25px">Term: Transfer</i></td> <!-- Ticket # 1240 -->
													</tr>
												<? }
												
												$c_in_att_sub_tot 	= 0;
												$c_in_comp_sub_tot 	= 0;
												$c_in_cu_sub_gnu 	= 0;
												$c_in_gpa_sub_tot 	= 0;
												while (!$res_tc->EOF) {
													$PK_STUDENT_ENROLLMENT 	= $res_tc->fields['PK_STUDENT_ENROLLMENT']; 
													$PK_GRADE				= $res_tc->fields['PK_GRADE']; 
													$c_in_num_grade_tot		+= $res_tc->fields['TC_NUMERIC_GRADE'];  //Ticket # 1240
													
													$res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE  S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); 
													$ATTEMPTED_UNITS 	 = $res_tc->fields['UNITS'];
													$COMPLETED_UNITS	 = 0;
													$c_in_att_tot 		+= $ATTEMPTED_UNITS; 
													$c_in_att_sub_tot 	+= $ATTEMPTED_UNITS; 
													
													if($res_tc->fields['UNITS_COMPLETED'] == 1) {
														$COMPLETED_UNITS 	 = $res_tc->fields['UNITS'];
														$c_in_comp_tot  	+= $COMPLETED_UNITS;
														$c_in_comp_sub_tot  += $COMPLETED_UNITS;
													}
												
													$gnu = 0;
													if($res_tc->fields['CALCULATE_GPA'] == 1) {
														$gnu 				 = $ATTEMPTED_UNITS * $res_tc->fields['NUMBER_GRADE']; 
														$c_in_cu_gnu 		+= $gnu; 
														$c_in_cu_sub_gnu 	+= $gnu; 
														
														$gpa				= $gnu / $COMPLETED_UNITS;;
														$c_in_gpa_sub_tot 	+= $gpa;
														$c_in_gpa_tot 		+= $gpa;

														// DIAM-2351, Calulate GPA
														$TC_GPA_VALULE 				 = $res_tc->fields['GPA_VALUE']; 
														$summation_of_gpa 			+= $TC_GPA_VALULE; 
														$TC_GPA_WEIGHT 				 = $res_tc->fields['GPA_WEIGHT']; 
														$summation_of_weight 		+= $TC_GPA_WEIGHT; 
														// End DIAM-2351, Calulate GPA
													} ?>
													<tr>
														<td ><?=$res_tc->fields['COURSE_CODE'] ?></td>
														<td ></td>
														
														<!-- Ticket # 1240 -->
														<? if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?>
															<td ><div style="text-align:right" ><?=$res_tc->fields['GRADE']; ?></div></td>
														<? } 
														if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?>
															<td ><div style="text-align:right" ><?=$res_tc->fields['TC_NUMERIC_GRADE']; ?></div></td>
														<? } ?>
														<!-- Ticket # 1240 -->
														
														<td ><div style="text-align:right" ><?=number_format_value_checker($ATTEMPTED_UNITS,2)?></div></td>
														<td ><div style="text-align:right" ><?=number_format_value_checker($COMPLETED_UNITS,2)?></div></td>
														<td ><div style="text-align:right" ></div></td>
													</tr>
													
												<? $res_tc->MoveNext();
												}
												if($res_tc->RecordCount() > 0) { ?>
												<!-- Ticket # 1240 -->
												<tr>
													<td ></td>
													
													<td ><div style="text-align:right" ><?=TERM_TOTAL?></div></td>
													<? if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?>
													<td ></td>
													<? } 
													if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?>
													<td ></td>
													<? } ?>
											
													<td ><div style="text-align:right" ><?=number_format_value_checker($c_in_att_sub_tot,2)?></div></td>
													<td ><div style="text-align:right" ><?=number_format_value_checker($c_in_comp_sub_tot,2)?></div></td>
													<td ><div style="text-align:right" ><?=number_format_value_checker($c_in_gpa_sub_tot,2)?></div></td>
												</tr>
												<tr>
													<td ></td>
													
													<td ><div style="text-align:right" ><?=CUMULATIVE_TOTAL?></div></td>
													<? if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?>
													<td ></td>
													<? } 
													if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?>
													<td ></td>
													<? } ?>
													
													<td ><div style="text-align:right" ><?=number_format_value_checker($c_in_att_tot,2)?></div></td>
													<td ><div style="text-align:right" ><?=number_format_value_checker($c_in_comp_tot,2)?></div></td>
													<td ><div style="text-align:right" ><?=number_format_value_checker(($c_in_cu_gnu / $c_in_comp_tot),2)?></div></td>
												</tr>
												<!-- Ticket # 1240 -->
												<? } 
												
												$res_term = $db->Execute("SELECT DISTINCT(S_STUDENT_COURSE.PK_TERM_MASTER), BEGIN_DATE as BEGIN_DATE_1, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE FROM S_STUDENT_COURSE LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER WHERE S_STUDENT_COURSE.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' ORDER By BEGIN_DATE_1 ASC");
												while (!$res_term->EOF) {
													$PK_TERM_MASTER = $res_term->fields['PK_TERM_MASTER'];
													$BEGIN_DATE 	= $res_term->fields['BEGIN_DATE'];
													?>
													<tr>
														<td <? if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?> colspan="7" <? } else { ?> colspan="6" <? } ?> ><i style="font-size:25px">Term: <?=$res_term->fields['BEGIN_DATE'] ?></i></td>
													</tr>
													<?
													$c_in_num_grade_sub_tot = 0; //ticket #1240
													$c_in_att_sub_tot 		= 0;
													$c_in_comp_sub_tot 		= 0;
													$c_in_cu_sub_gnu 		= 0;
													$c_in_gpa_sub_tot 		= 0;

													// DIAM-2351
													$gpa_value_total        = 0;
													$gpa_weight_total		= 0;
													// End DIAM-2351
													
													$res_course = $db->Execute("SELECT 
																					COURSE_CODE, 
																					COURSE_DESCRIPTION, 
																					S_STUDENT_COURSE.PK_COURSE_OFFERING, 
																					FINAL_GRADE, 
																					NUMERIC_GRADE, 
																					GRADE, 
																					NUMBER_GRADE, 
																					CALCULATE_GPA, 
																					UNITS_ATTEMPTED, 
																					UNITS_COMPLETED, 
																					UNITS_IN_PROGRESS, 
																					COURSE_UNITS,
																					CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC)* S_GRADE.NUMBER_GRADE ELSE 0 END AS GPA_VALUE, 
																					CASE WHEN S_GRADE.CALCULATE_GPA = 1 THEN POWER (S_COURSE.UNITS, S_GRADE.WEIGHTED_GRADE_CALC) ELSE 0 END AS GPA_WEIGHT
																				  FROM 
																					S_STUDENT_COURSE 
																					LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE 
																					LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING 
																					LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE 
																				  WHERE 
																					PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' 
																					AND S_STUDENT_COURSE.PK_TERM_MASTER = '$PK_TERM_MASTER' 
																				  ORDER BY 
																					COURSE_CODE ASC ");	// DIAM-2351
													while (!$res_course->EOF) { 
														
														$PK_COURSE_OFFERING = $res_course->fields['PK_COURSE_OFFERING'];
														$FINAL_GRADE 		= $res_course->fields['FINAL_GRADE'];
														
														$ATTEMPTED_UNITS 	 = $res_course->fields['COURSE_UNITS'];
														$COMPLETED_UNITS	 = 0;
														$c_in_att_tot 		+= $ATTEMPTED_UNITS; 
														$c_in_att_sub_tot 	+= $ATTEMPTED_UNITS; 
														
														if($res_course->fields['UNITS_COMPLETED'] == 1) { // Ticket # 1152
															$COMPLETED_UNITS	 = $res_course->fields['COURSE_UNITS'];
															$c_in_comp_tot  	+= $COMPLETED_UNITS;
															$c_in_comp_sub_tot  += $COMPLETED_UNITS;
														}
														
														$c_in_num_grade_sub_tot += $res_course->fields['NUMERIC_GRADE']; 
														$c_in_num_grade_tot		+= $res_course->fields['NUMERIC_GRADE']; 
														
														$gnu = 0;
														$gpa = 0;
														if($res_course->fields['CALCULATE_GPA'] == 1) { // Ticket # 1152
															$gnu 				 = $ATTEMPTED_UNITS * $res_course->fields['NUMBER_GRADE']; // Ticket # 1152
															$c_in_cu_gnu 		+= $gnu; 
															$c_in_cu_sub_gnu 	+= $gnu; 
															
															$gpa				= $gnu / $COMPLETED_UNITS;;
															$c_in_gpa_sub_tot 	+= $gpa;
															$c_in_gpa_tot 		+= $gpa;

															// DIAM-2351, Calulate GPA
															$GPA_VALULE 			 = $res_course->fields['GPA_VALUE']; 
															$gpa_value_total 		+= $GPA_VALULE; 
															$GPA_WEIGHT 			 = $res_course->fields['GPA_WEIGHT']; 
															$gpa_weight_total 		+= $GPA_WEIGHT; 

															$summation_of_gpa    	+= $GPA_VALULE;
															$summation_of_weight 	+= $GPA_WEIGHT;
															// End DIAM-2351, Calulate GPA
														} ?>
													
														<tr>
															<td ><?=$res_course->fields['COURSE_CODE'] ?></td>
															<td ><?=$res_course->fields['COURSE_DESCRIPTION']?></td>
															<!-- Ticket # 1240 -->
															<? if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?>
															<td ><div style="text-align:right" ><?=$res_course->fields['GRADE']; ?></div></td> <!-- // Ticket # 1152 -->
															<? } 
															if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?>
															<td ><div style="text-align:right" ><?=$res_course->fields['NUMERIC_GRADE']; ?></div></td>
															<? } ?>
															<!-- Ticket # 1240 -->
															<td ><div style="text-align:right" ><?=number_format_value_checker($ATTEMPTED_UNITS,2)?></div></td>
															<td ><div style="text-align:right" ><?=number_format_value_checker($COMPLETED_UNITS,2)?></div></td>
															<td ><div style="text-align:right" ></div></td>
														</tr>
													<? $res_course->MoveNext();
													}  
													
													// DIAM-2351
													$gpa_weighted=0;
													if($gpa_value_total>0)
													{
														$gpa_weighted = $gpa_value_total/$gpa_weight_total;
													}
													// End DIAM-2351
													?>
													<!-- Ticket # 1240 -->
													<tr>
														<td ></td>
														<td ><div style="text-align:right" ><?=TERM_TOTAL?></div></td>
														<? if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?>
														<td ></td>
														<? } 
														if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?>
														<td ><div style="text-align:right" ><?=number_format_value_checker($c_in_num_grade_sub_tot,2)?></div></td>
														<? } ?>
														<td ><div style="text-align:right" ><?=number_format_value_checker($c_in_att_sub_tot,2)?></div></td>
														<td ><div style="text-align:right" ><?=number_format_value_checker($c_in_comp_sub_tot,2)?></div></td>
														<td ><div style="text-align:right" ><?=number_format_value_checker($gpa_weighted,2)?></div></td>
													</tr>
													<tr>
														<td ></td>
														<td ><div style="text-align:right" ><?=CUMULATIVE_TOTAL?></div></td>
														<? if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 1 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?>
														<td ></td>
														<? } 
														if($res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 2 || $res_sp_set->fields['GRADE_DISPLAY_TYPE'] == 3){ ?>
														<td ><div style="text-align:right" ><?=number_format_value_checker($c_in_num_grade_tot,2)?></div></td>
														<? } ?>
														<td ><div style="text-align:right" ><?=number_format_value_checker($c_in_att_tot,2)?></div></td>
														<td ><div style="text-align:right" ><?=number_format_value_checker($c_in_comp_tot,2)?></div></td>
														<td ><div style="text-align:right" ><?=number_format_value_checker(($summation_of_gpa/$summation_of_weight),2)?></div></td>
													</tr>
													<!-- Ticket # 1240 -->
												<? $res_term->MoveNext();
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