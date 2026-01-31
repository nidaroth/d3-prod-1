<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/grade_book.php");
require_once("../language/menu.php");
require_once("../school/check_access.php");

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
	<title><?=MNU_GRADE_BOOK ?> | <?=$title?></title>
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
							<?=MNU_GRADE_BOOK?>
						</h4>
                    </div>
                </div>	
				
				<form class="floating-labels" method="get" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="card-group">
						<div class="card">
							<div class="card-body">
								<div class="row" style="padding-bottom: 10px;">
									<div class="col-md-2">
										<select id="t" name="t" class="form-control" onchange="get_course_details()" >
											<option value="" >All Terms</option>
											<? $res_type = $db->Execute("SELECT S_STUDENT_COURSE.PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 from S_STUDENT_COURSE, S_TERM_MASTER WHERE S_STUDENT_COURSE.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER GROUP BY S_STUDENT_COURSE.PK_TERM_MASTER ORDER BY BEGIN_DATE DESC");
											while (!$res_type->EOF) { ?>
												<option value="<?=$res_type->fields['PK_TERM_MASTER'] ?>" <? if($_GET['t'] == $res_type->fields['PK_TERM_MASTER']) echo "selected"; ?> ><?=$res_type->fields['BEGIN_DATE_1'] ?></option>
											<?	$res_type->MoveNext();
											} ?>
										</select>
									</div>
									<!-- DIAM-1988 -->
									<div class="col-md-2">
										
										<div id="COURSE_OFFERING_DIV"></div>
										
									</div>
									<!-- DIAM-1988 -->
									<div class="col-md-4">
										<button type="button"  onclick="validate_form()" class="btn waves-effect waves-light btn-info">Search</button>
										<!-- <a href="grade_book_pdf" class="btn waves-effect waves-light btn-info" ><?=PDF?></a> -->
										<?php 
										$res_get_enrollment = $db->Execute("SELECT GROUP_CONCAT(PK_STUDENT_ENROLLMENT) AS PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' ");
										$PK_STUDENT_ENROLLMENT_current  = $res_get_enrollment->fields['PK_STUDENT_ENROLLMENT'];
										$term_id = $_REQUEST['t'];
										$stud_course_id = $_REQUEST['stud_course_id'];
										?>
										<button type="button" onclick="window.open('<?php echo rtrim($_ENV['BASE_URL'], '/') . '/'."student/course_offering_grade_book_progress_report_pdf?id=".$_SESSION['PK_STUDENT_MASTER']."&type=1&term_id=".$term_id."&stud_course_id=".$stud_course_id."&eid=".$PK_STUDENT_ENROLLMENT_current."&show=1&exclude_tc=0&report_type=1&rn=".time() ?>')" class="btn waves-effect waves-light btn-info" ><?=PDF?></button>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-12">
										<table data-toggle="table" data-mobile-responsive="true" class="table-striped" >
											<thead>
												<tr>
													<th ><?=TERM?></th>
													<th ><?=COURSE?></th>
													<th ><?=DESCRIPTION?></th>
													<th ><div style="text-align:right" ><?=STUDENT_POINTS?></div></th>
													<th ><div style="text-align:right" ><?=TOTAL_POINTS?></div></th>
													<th ><div style="text-align:right" >Weight</div></th>
													<th ><div style="text-align:right" >Weighted Points</div></th>
												</tr>
											</thead>
											<tbody>
												<? $cond = "";
												if($_GET['t'] != '')
												{
													$cond .= " AND S_STUDENT_COURSE.PK_TERM_MASTER = '$_GET[t]' ";
												}
												// DIAM-1988
												if($_GET['stud_course_id'] != '')
												{
													$cond .= " AND S_COURSE_OFFERING.PK_COURSE = '$_GET[stud_course_id]' ";
												}	
												// End DIAM-1988
													
												$query = "SELECT PK_COURSE_OFFERING_GRADE,S_COURSE_OFFERING_GRADE.CODE,S_COURSE_OFFERING_GRADE.POINTS,S_COURSE_OFFERING_GRADE.WEIGHT AS WEIGHTS,S_COURSE_OFFERING_GRADE.WEIGHTED_POINTS, S_COURSE_OFFERING_GRADE.DESCRIPTION, IF(BEGIN_DATE = '0000-00-00','', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, COURSE_CODE FROM S_STUDENT_COURSE, S_COURSE_OFFERING, S_COURSE, S_COURSE_OFFERING_GRADE, S_TERM_MASTER WHERE S_STUDENT_COURSE.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING AND S_COURSE_OFFERING_GRADE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE $cond ORDER BY BEGIN_DATE DESC, COURSE_CODE ASC, PROGRAM_COURSE_ORDER DESC ";
												$_SESSION['QUERY'] = $query;
												$stu_total 	= 0;
												$total 		= 0;
												$result1 = $db->Execute($query);
												$PK_STUDENT_GRADE_VALUE = array(); // DIAM-679
												while (!$result1->EOF) {
													$PK_COURSE_OFFERING_GRADE = $result1->fields['PK_COURSE_OFFERING_GRADE'];
													
													$res_stu_grade = $db->Execute("SELECT PK_STUDENT_GRADE,POINTS,PK_STUDENT_MASTER FROM S_STUDENT_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' "); 
													$PK_STUDENT_GRADE  = $res_stu_grade->fields['PK_STUDENT_GRADE']; 
													$PK_STUDENT_MASTER = $res_stu_grade->fields['PK_STUDENT_MASTER']; 

													// DIAM-679	
													if(has_ccmc_access($_SESSION['PK_ACCOUNT'],1)){ 

													}else{
														$PK_STUDENT_GRADE_VALUE []=$PK_STUDENT_GRADE;
													}												
													$res_w = $db->Execute("SELECT WEIGHT FROM S_STUDENT_GRADE,S_COURSE_OFFERING_GRADE where PK_STUDENT_GRADE = '$PK_STUDENT_GRADE' AND S_STUDENT_GRADE.PK_COURSE_OFFERING_GRADE = S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE "); 
													// DIAM-679

													//if($res_stu_grade->fields['POINTS'] != '') {
														if(has_ccmc_access($_SESSION['PK_ACCOUNT'],1)){
														$PK_STUDENT_GRADE_VALUE []=$PK_STUDENT_GRADE;
														}
														$stu_total 	+= $res_stu_grade->fields['POINTS'];
														//$total 		+= $result1->fields['POINTS'];
														$total += ($res_stu_grade->fields['POINTS'] * $res_w->fields['WEIGHT']); // DIAM-679
													//} ?>
													<tr>
														<td ><?=$result1->fields['BEGIN_DATE_1'] ?></td>
														<td ><?=$result1->fields['COURSE_CODE'] ?></td>
														<td ><?=$result1->fields['CODE'].' - '.$result1->fields['DESCRIPTION'] ?></td>
														<td ><div style="text-align:right" ><?=$res_stu_grade->fields['POINTS']?></div></td>
														<td ><div style="text-align:right" ><?=$result1->fields['POINTS'] ?></div></td>
														<td ><div style="text-align:right" ><?=$result1->fields['WEIGHTS']?></div></td>
														<td ><div style="text-align:right" ><?=round($result1->fields['WEIGHTED_POINTS'])?></div></td>
													</tr>
												
												<?	$result1->MoveNext();
												}
												// DIAM-679
											    $PK_STUDENT_GRADE_VALUE = implode(',',$PK_STUDENT_GRADE_VALUE);
												$res = $db->Execute("SELECT SUM(WEIGHTED_POINTS) as WEIGHTED_POINTS, SUM(WEIGHT) AS WEIGHTS, SUM(S_COURSE_OFFERING_GRADE.POINTS) AS POINTS,  SUM(S_STUDENT_GRADE.POINTS) AS STUDENT_POINTS FROM S_COURSE_OFFERING_GRADE,S_STUDENT_GRADE WHERE S_STUDENT_GRADE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_GRADE IN ($PK_STUDENT_GRADE_VALUE) AND S_COURSE_OFFERING_GRADE.PK_COURSE_OFFERING_GRADE = S_STUDENT_GRADE.PK_COURSE_OFFERING_GRADE "); 
												$MAX_CURRENT_POINTS = $res->fields['WEIGHTED_POINTS'];
												$MAX_WEIGHTS = $res->fields['WEIGHTS'];
												$MAX_POINTS = $res->fields['POINTS'];
												$MAX_STUDENT_POINTS = $res->fields['STUDENT_POINTS'];
												// if(has_ccmc_access($_SESSION['PK_ACCOUNT'],1)){
												// $MAX_CURRENT_POINTS = number_format_data_value($res->fields['WEIGHTED_POINTS'],2);  // DIAM-1527
												// $total =  number_format_data_value($total,2);  // DIAM-1527
												// }
												// DIAM-679
												?>
												<tr>
														<td ></td>
														<td ></td>
														<td ></td>
														<!-- DIAM-1527 -->
														<?
														if($_GET['t'] != '' || $_GET['stud_course_id'] != '')
														{
														?>
														<td ><div style="font-weight:bold;text-align:right" ><?=$MAX_STUDENT_POINTS?></div></td>
														<td ><div style="font-weight:bold;text-align:right" ><?=$MAX_POINTS?></div></td>
														<td ><div style="font-weight:bold;text-align:right" ><?=$MAX_WEIGHTS?></div></td>
														<td ><div style="font-weight:bold;text-align:right" ><?=$MAX_CURRENT_POINTS?></div></td>	
														<?
														}
														else{
														?>
														<td ></td>
														<td ></td>
														<td ></td>
														<td ></td>
														<?
														}
														?>
														<!-- End DIAM-1527 -->
													</tr>
											</tbody>
										</table>
									</div> 
									<?
									if($_GET['t'] != '' || $_GET['stud_course_id'] != '')
									{
										$cond = "";
										if($_GET['t'] != '')
										{
											$cond .= " AND S_STUDENT_COURSE.PK_TERM_MASTER = '$_GET[t]' ";
										}
										if($_GET['stud_course_id'] != '')
										{
											$cond .= " AND S_COURSE_OFFERING.PK_COURSE = '$_GET[stud_course_id]' ";
										}	
										$query = "SELECT 
														FINAL_TOTAL_OBTAINED, FINAL_MAX_TOTAL, FINAL_TOTAL_GRADE, CURRENT_TOTAL_OBTAINED, CURRENT_MAX_TOTAL, FINAL_GRADE_GRADE, FINAL_TOTAL_GRADE_SCALE_SETUP.GRADE AS FINAL_TOTAL_SCALE, CURRENT_SCALE_SETUP.GRADE AS CURRENT_SCALE
													FROM 
														S_COURSE_OFFERING, 
														S_STUDENT_COURSE
														LEFT JOIN S_GRADE AS CURRENT_SCALE_SETUP ON S_STUDENT_COURSE.CURRENT_TOTAL_GRADE = CURRENT_SCALE_SETUP.PK_GRADE 
														LEFT JOIN S_GRADE AS FINAL_TOTAL_GRADE_SCALE_SETUP ON S_STUDENT_COURSE.FINAL_TOTAL_GRADE = FINAL_TOTAL_GRADE_SCALE_SETUP.PK_GRADE
													WHERE 
														S_STUDENT_COURSE.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' 
														AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING 
														AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond  ";
													//echo $query;
										$res_stu = $db->Execute($query);
										while (!$res_stu->EOF) 
										{
											$FINAL_PERCENTAGE  = number_format_value_checker(($res_stu->fields['FINAL_TOTAL_OBTAINED'] / $res_stu->fields['FINAL_MAX_TOTAL'] * 100),2).' %';
											$FINAL_TOTAL = $res_stu->fields['FINAL_TOTAL_OBTAINED'].'/'.$res_stu->fields['FINAL_MAX_TOTAL'];
											$FINAL_TOTAL_SCALE = $res_stu->fields['FINAL_TOTAL_SCALE'];

											$CURRENT_PERCENTAGE = number_format_value_checker(($res_stu->fields['CURRENT_TOTAL_OBTAINED'] / $res_stu->fields['CURRENT_MAX_TOTAL'] * 100),2).' %';
											$CURRENT_TOTAL = $res_stu->fields['CURRENT_TOTAL_OBTAINED'].'/'.$res_stu->fields['CURRENT_MAX_TOTAL'];
											$CURRENT_SCALE = $res_stu->fields['CURRENT_SCALE'];

											$FINAL_GRADE = $res_stu->fields['FINAL_GRADE_GRADE'];
									?>
									<div class="col-md-12" style="padding-top: 10px;">
										<table cellspadding="0" cellspacing="0" width="100%">
											<td width="50%"></td>
											<td width="50%" style="float:right">
												<table cellspadding="10" cellspacing="0" width="100%">
													<tr>
														<td>Current Total :</td>
														<td><?=$CURRENT_TOTAL?></td>
														<td><?=$CURRENT_PERCENTAGE?></td>
														<td align="left"><?=$CURRENT_SCALE?></td>
													</tr>
													<tr>
														<td>Final Total :</td>
														<td><?=$FINAL_TOTAL?></td>
														<td><?=$FINAL_PERCENTAGE?></td>
														<td align="left"><?=$FINAL_TOTAL_SCALE?></td>
													</tr>
													<tr>
														<td></td>
														<td colspan="2" align="middle">Final Grade :</td>
														<td align="left"><?=$FINAL_GRADE?></td>	
													</tr>
												</table>
											</td>
										</table>
									</div>
									<?
										$res_stu->MoveNext();
										}
									}
									?>
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

	<!-- DIAM-1988 -->
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>

	<script>
	get_course_details();

	function get_course_details()
	{
		var PK_TERM_MASTER = document.getElementById('t').value;
		if(PK_TERM_MASTER != "")
		{
			var stud_course_id = '<?=$_REQUEST['stud_course_id']?>';
		}
		
		var data = "PK_TERM_MASTER="+PK_TERM_MASTER+'&stud_course_id='+stud_course_id;
		var url = "ajax_get_course_offering_from_term_student";

		var value = jQuery.ajax({
			url: url,
			type: "POST",
			data: data,
			async: false,
			cache: false,
			success: function(data) {
				document.getElementById('COURSE_OFFERING_DIV').innerHTML = data;
			}
		});
	}

	function validate_form(){
		var valid = new Validation('form1', {onSubmit:false});
		var result = valid.validate();
		if(result == true) 
		{
			document.form1.submit();
		}
	}
	</script>
	<!-- End DIAM-1988 -->
</body>
</html>
