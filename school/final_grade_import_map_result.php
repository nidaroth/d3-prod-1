<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/menu.php");
require_once("../language/final_grade_input.php");
require_once("check_access.php");
require_once("function_calc_student_grade.php"); 

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

include '../global/excel/Classes/PHPExcel/IOFactory.php'; 

if($_GET['act'] == 'del'){
	$db->Execute("DELETE FROM S_FINAL_GRADE_IMPORT_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_FINAL_GRADE_IMPORT_DETAIL IN ($_GET[iid]) ");
	
	$res_clock = $db->Execute("SELECT PK_FINAL_GRADE_IMPORT_DETAIL FROM S_FINAL_GRADE_IMPORT_DETAIL WHERE PK_FINAL_GRADE_IMPORT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$S_FINAL_GRADE_IMPORT_2['TOTAL_COUNT'] = $res_clock->RecordCount();
	db_perform('S_FINAL_GRADE_IMPORT', $S_FINAL_GRADE_IMPORT_2, 'update'," PK_FINAL_GRADE_IMPORT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$res_clock = $db->Execute("SELECT POSTED FROM S_FINAL_GRADE_IMPORT_DETAIL WHERE PK_FINAL_GRADE_IMPORT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND POSTED = 0");
	if($res_clock->RecordCount() == 0) {
		$FINAL_GRADE_IMPORT['POSTED'] = 1;
		$FINAL_GRADE_IMPORT['POSTED_BY'] = $_SESSION['PK_USER'];
		$FINAL_GRADE_IMPORT['POSTED_ON'] = date("Y-m-d H:i");
		db_perform('S_FINAL_GRADE_IMPORT', $FINAL_GRADE_IMPORT, 'update'," PK_FINAL_GRADE_IMPORT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
	
	header("location:final_grade_import_map_result?id=".$_GET['id'].'&exclude='.$_GET['exclude'].'&t='.$_GET['t']);
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	foreach($_POST['PK_FINAL_GRADE_IMPORT_DETAIL'] as $PK_FINAL_GRADE_IMPORT_DETAIL){
		$PK_FINAL_GRADE			= $_POST['PK_FINAL_GRADE_'.$PK_FINAL_GRADE_IMPORT_DETAIL];
		$FINAL_NUMERIC_GRADE	= $_POST['FINAL_NUMERIC_GRADE_'.$PK_FINAL_GRADE_IMPORT_DETAIL];
		
		$res = $db->Execute("SELECT PK_STUDENT_MASTER, PK_STUDENT_COURSE, POSTED FROM S_FINAL_GRADE_IMPORT_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_FINAL_GRADE_IMPORT_DETAIL = '$PK_FINAL_GRADE_IMPORT_DETAIL' ");
		$PK_STUDENT_MASTER 			= $res->fields['PK_STUDENT_MASTER'];
		$POSTED 					= $res->fields['POSTED'];
		$PK_STUDENT_COURSE 			= $res->fields['PK_STUDENT_COURSE'];
		
		if($POSTED == 0 && $res->RecordCount() > 0) { //DIAM-70
			$res_grade = $db->Execute("SELECT FINAL_GRADE, IS_DEFAULT FROM S_STUDENT_COURSE LEFT JOIN S_GRADE ON FINAL_GRADE = PK_GRADE WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND PK_STUDENT_COURSE > 0 AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			if($res_grade->fields['FINAL_GRADE'] == 0 || $res_grade->fields['FINAL_GRADE'] == '' || ($res_grade->fields['FINAL_GRADE'] > 0 && $res_grade->fields['IS_DEFAULT'] == 1) ){
			
				$FINAL_GRADE_IMPORT_DETAIL 		  					= array();
				$FINAL_GRADE_IMPORT_DETAIL['PK_FINAL_GRADE'] 		= $PK_FINAL_GRADE;
				$FINAL_GRADE_IMPORT_DETAIL['FINAL_NUMERIC_GRADE'] 	= $FINAL_NUMERIC_GRADE;
				db_perform('S_FINAL_GRADE_IMPORT_DETAIL', $FINAL_GRADE_IMPORT_DETAIL, 'update'," PK_FINAL_GRADE_IMPORT_DETAIL = '$PK_FINAL_GRADE_IMPORT_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
				if($_POST['POST_GRAD'] == 2 ) {
					$res_grade_data = $db->Execute("SELECT * FROM S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_GRADE = '$FINAL_GRADE_IMPORT_DETAIL[PK_FINAL_GRADE]' ");
					
					$STUDENT_COURSE = array();
					$STUDENT_COURSE['FINAL_GRADE'] 	 		= $FINAL_GRADE_IMPORT_DETAIL['PK_FINAL_GRADE'];
					$STUDENT_COURSE['NUMERIC_GRADE'] 		= $FINAL_GRADE_IMPORT_DETAIL['FINAL_NUMERIC_GRADE'];
					
					$STUDENT_COURSE['FINAL_GRADE_GRADE'] 				= $res_grade_data->fields['GRADE'];
					$STUDENT_COURSE['FINAL_GRADE_NUMBER_GRADE'] 		= $res_grade_data->fields['NUMBER_GRADE'];
					$STUDENT_COURSE['FINAL_GRADE_CALCULATE_GPA'] 		= $res_grade_data->fields['CALCULATE_GPA'];
					$STUDENT_COURSE['FINAL_GRADE_UNITS_ATTEMPTED'] 		= $res_grade_data->fields['UNITS_ATTEMPTED'];
					$STUDENT_COURSE['FINAL_GRADE_UNITS_COMPLETED'] 		= $res_grade_data->fields['UNITS_COMPLETED'];
					$STUDENT_COURSE['FINAL_GRADE_UNITS_IN_PROGRESS'] 	= $res_grade_data->fields['UNITS_IN_PROGRESS'];
					$STUDENT_COURSE['FINAL_GRADE_WEIGHTED_GRADE_CALC'] 	= $res_grade_data->fields['WEIGHTED_GRADE_CALC'];
					$STUDENT_COURSE['FINAL_GRADE_RETAKE_UPDATE'] 		= $res_grade_data->fields['RETAKE_UPDATE'];
					
					db_perform('S_STUDENT_COURSE', $STUDENT_COURSE, 'update'," PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				}
			} else {
				$FINAL_GRADE_IMPORT_DETAIL 		  	= array();
				$FINAL_GRADE_IMPORT_DETAIL['MESSAGE'] = 'Final Grade Posted';
				db_perform('S_FINAL_GRADE_IMPORT_DETAIL', $FINAL_GRADE_IMPORT_DETAIL, 'update'," PK_FINAL_GRADE_IMPORT_DETAIL = '$PK_FINAL_GRADE_IMPORT_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			}
		}
	}
	
	$res_grad = $db->Execute("SELECT PK_FINAL_GRADE_IMPORT_DETAIL FROM S_FINAL_GRADE_IMPORT_DETAIL WHERE PK_FINAL_GRADE_IMPORT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$FINAL_GRADE_IMPORT1['TOTAL_COUNT'] = $res_grad->RecordCount();
	db_perform('S_FINAL_GRADE_IMPORT', $FINAL_GRADE_IMPORT1, 'update'," PK_FINAL_GRADE_IMPORT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	//$res_clock = $db->Execute("SELECT POSTED FROM S_FINAL_GRADE_IMPORT_DETAIL WHERE PK_FINAL_GRADE_IMPORT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND POSTED = 0");
	//if($res_clock->RecordCount() == 0) {
	if($_POST['POST_GRAD'] == 2) {
		$FINAL_GRADE_IMPORT['POSTED'] = 1;
		$FINAL_GRADE_IMPORT['POSTED_BY'] = $_SESSION['PK_USER'];
		$FINAL_GRADE_IMPORT['POSTED_ON'] = date("Y-m-d H:i");
		db_perform('S_FINAL_GRADE_IMPORT', $FINAL_GRADE_IMPORT, 'update'," PK_FINAL_GRADE_IMPORT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		$FINAL_GRADE_IMPORT_DETAIL 		  	 = array();
		$FINAL_GRADE_IMPORT_DETAIL['POSTED'] = 1;
		db_perform('S_FINAL_GRADE_IMPORT_DETAIL', $FINAL_GRADE_IMPORT_DETAIL, 'update'," PK_FINAL_GRADE_IMPORT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
	//exit;
	header("location:final_grade_import_map_result?id=".$_GET['id'].'&t='.$_GET['t']);
	exit;
}
$res_grad = $db->Execute("SELECT IMPORTED_COUNT, TOTAL_COUNT, POSTED FROM S_FINAL_GRADE_IMPORT WHERE PK_FINAL_GRADE_IMPORT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
if($res_grad->RecordCount() == 0) {
	header("location:../index");
	exit;
}
$IMPORTED_COUNT = $res_grad->fields['IMPORTED_COUNT'];
$TOTAL_COUNT 	= $res_grad->fields['TOTAL_COUNT'];
$POSTED			= $res_grad->fields['POSTED'];

if($POSTED == 0) {
	$res = $db->Execute("SELECT PK_STUDENT_MASTER, PK_STUDENT_COURSE, POSTED, PK_FINAL_GRADE_IMPORT_DETAIL FROM S_FINAL_GRADE_IMPORT_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_FINAL_GRADE_IMPORT = '$_GET[id]' AND PK_STUDENT_COURSE > 0");
	while (!$res->EOF) {
		$PK_STUDENT_COURSE 				= $res->fields['PK_STUDENT_COURSE'];
		$PK_FINAL_GRADE_IMPORT_DETAIL 	= $res->fields['PK_FINAL_GRADE_IMPORT_DETAIL'];
	
		$res_grade = $db->Execute("SELECT FINAL_GRADE, IS_DEFAULT FROM S_STUDENT_COURSE LEFT JOIN S_GRADE ON FINAL_GRADE = PK_GRADE WHERE PK_STUDENT_COURSE = '$PK_STUDENT_COURSE' AND PK_STUDENT_COURSE > 0 AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		if($res_grade->fields['FINAL_GRADE'] == 0 || $res_grade->fields['FINAL_GRADE'] == '' || ($res_grade->fields['FINAL_GRADE'] > 0 && $res_grade->fields['IS_DEFAULT'] == 1) ){
		} else {
			$FINAL_GRADE_IMPORT_DETAIL 		  	= array();
			$FINAL_GRADE_IMPORT_DETAIL['PK_STUDENT_COURSE'] = 0;
			$FINAL_GRADE_IMPORT_DETAIL['MESSAGE'] 			= 'Final Grade Posted';
			db_perform('S_FINAL_GRADE_IMPORT_DETAIL', $FINAL_GRADE_IMPORT_DETAIL, 'update'," PK_FINAL_GRADE_IMPORT_DETAIL = '$PK_FINAL_GRADE_IMPORT_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
		
		$res->MoveNext();
	}
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
	<title><?=MNU_FINAL_GRADE_IMPORT_RESULT ?> | <?=$title?></title>
	<!-- //DIAM-70 -->
	<style>
		.inputgradehighlight{
			background-color:#faebe7;
			border-color:red;
		}
	</style>
	<!-- //DIAM-70 -->
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_FINAL_GRADE_IMPORT_RESULT ?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels " method="post" name="form1" id="form1" enctype="multipart/form-data">
									<? if($msg1 != '' ){ ?>
									<div class="row">
										<div class="col-md-2">&nbsp;</div>
                                        <div class="col-md-6" style="color:red">
											<?=$msg1?>
										</div>
                                    </div>
									<? } ?>
									<div class="row m-b-10 ">
										<div class="col-md-12">
											<div class="d-flex justify-content-end align-items-center">
												<a href="final_grade_import" class="btn btn-info d-none d-lg-block m-l-15">Back</a>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<table data-toggle="table" data-mobile-responsive="true" class="table-striped">
												<thead>
													<tr>
														<? if($POSTED == 0){ ?>
														<th >
															<?//=DELETE ?><br /><!-- DIAM-70 -->
															<input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" />
														</th>
														<? } ?>
														<th ><?=CAMPUS?></th>
														<th ><?=TERM?></th>
														<th ><?=COURSE?></th>
														<th ><?=EXTERNAL_ID?></th>
														<th ><?=MESSAGE?></th>
														<th ><?=BADGE_ID ?></th>
														<th ><?=STUDENT_ID ?></th>
														<th ><?=STUDENT?></th>
														<th ><?=CURRENT_FINAL_GRADE?></th>
														<th ><?=IMPORTED_FINAL_GRADE?></th>
														<th ><?=IMPORTED_FINAL_NUMERIC_GRADE?></th>
													</tr>
												</thead>
												<tbody>
													<? $cond = "";
													//if($POSTED == 0)
														//$cond = " AND POSTED = 0 ";
													$query = "SELECT PK_FINAL_GRADE_IMPORT_DETAIL, CONCAT(LAST_NAME,', ',FIRST_NAME) as NAME, MESSAGE,FOUND, S_STUDENT_MASTER.PK_STUDENT_MASTER, PK_STUDENT_ENROLLMENT, S_COURSE_OFFERING.PK_COURSE_OFFERING, STUDENT_ID, S_FINAL_GRADE_IMPORT_DETAIL.BADGE_ID, NOT_FOUND_ON_FILE, CAMPUS_CODE, DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y') as BEGIN_DATE, COURSE_CODE, S_FINAL_GRADE_IMPORT_DETAIL.EXTERNAL_ID, PK_FINAL_GRADE, FINAL_NUMERIC_GRADE,  M_SESSION.SESSION, S_COURSE_OFFERING.SESSION_NO, POSTED, S_FINAL_GRADE_IMPORT_DETAIL.PK_STUDENT_COURSE       
													FROM 
													S_FINAL_GRADE_IMPORT_DETAIL 
													LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_FINAL_GRADE_IMPORT_DETAIL.PK_STUDENT_MASTER 
													LEFT JOIN S_COURSE_OFFERING ON S_COURSE_OFFERING.PK_COURSE_OFFERING = S_FINAL_GRADE_IMPORT_DETAIL.PK_COURSE_OFFERING 
													LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS 
													LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER  
													LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE  
													LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
													WHERE 
													S_FINAL_GRADE_IMPORT_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
													S_FINAL_GRADE_IMPORT_DETAIL.PK_FINAL_GRADE_IMPORT = '$_GET[id]' $cond ";
													
													$res_grad = $db->Execute($query);
													$total = 0;
													while (!$res_grad->EOF) {  
														$PK_FINAL_GRADE_IMPORT_DETAIL 	= $res_grad->fields['PK_FINAL_GRADE_IMPORT_DETAIL'];
														$FOUND							= $res_grad->fields['FOUND'];
														$PK_STUDENT_MASTER 				= $res_grad->fields['PK_STUDENT_MASTER'];
														$PK_STUDENT_ENROLLMENT 			= $res_grad->fields['PK_STUDENT_ENROLLMENT'];
														$PK_COURSE_OFFERING 			= $res_grad->fields['PK_COURSE_OFFERING'];
														$NOT_FOUND_ON_FILE				= $res_grad->fields['NOT_FOUND_ON_FILE'];
														$PK_FINAL_GRADE					= $res_grad->fields['PK_FINAL_GRADE'];
														$FINAL_NUMERIC_GRADE			= $res_grad->fields['FINAL_NUMERIC_GRADE'];
														$GRADE							= $res_grad->fields['GRADE'];
														$POSTED1						= $res_grad->fields['POSTED'];
														$PK_STUDENT_COURSE				= $res_grad->fields['PK_STUDENT_COURSE'];
														
														$color_style = '';
														if($res_grad->fields['MESSAGE'] != '' )
															$color_style = 'color:red !important;';
														?>
														<tr>
															<? if($POSTED == 0){ ?>
															<td>
																<input type="checkbox" name="DELETE_ID[]" id="DELETE_ID_<?=$PK_FINAL_GRADE_IMPORT_DETAIL?>" value="<?=$PK_FINAL_GRADE_IMPORT_DETAIL?>" />
															</td>
															<? } ?>
															
															<td>
																<div style="width:80px;<?=$color_style?>" ><?=$res_grad->fields['CAMPUS_CODE']?></div>
																
																<? if($POSTED == 0){ ?>
																<input type="hidden" name="PK_FINAL_GRADE_IMPORT_DETAIL[]" id="PK_FINAL_GRADE_IMPORT_DETAIL_<?=$PK_FINAL_GRADE_IMPORT_DETAIL?>" value="<?=$PK_FINAL_GRADE_IMPORT_DETAIL?>" />
																<? } ?>
															</td>
															<td>
																<div style="width:80px;<?=$color_style?>" ><?=$res_grad->fields['BEGIN_DATE']?></div>
															</td>	
															<td><div style="width:100px;<?=$color_style?>" ><?=$res_grad->fields['COURSE_CODE'].' ('.substr($res_grad->fields['SESSION'],0,1).' - '.$res_grad->fields['SESSION_NO'].')' ?></div></td>
															<td>
																<div style="width:100px;<?=$color_style?>" ><?=$res_grad->fields['EXTERNAL_ID']?></div>
															</td>
															<td>
																<div style="width:140px;<?=$color_style?>" >
																	<?  if($POSTED == 1) echo "Final Grade Posted"; else echo $res_grad->fields['MESSAGE']; ?>
																</div>
															</td>
															
															<td><div style="width:80px;<?=$color_style?>" ><?=$res_grad->fields['BADGE_ID']?></div></td>
															<td><div style="width:80px;<?=$color_style?>" ><?=$res_grad->fields['STUDENT_ID']?></div></td>
															<td><div style="width:120px;<?=$color_style?>" ><?=$res_grad->fields['NAME']?></div></td>
															<td>
																<div style="width:100px;<?=$color_style?>" >
																	<? $res_grade_1 = $db->Execute("select GRADE from S_STUDENT_COURSE LEFT JOIN S_GRADE ON FINAL_GRADE = PK_GRADE, S_STUDENT_MASTER WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_COURSE_OFFERING > 0 AND S_STUDENT_COURSE.PK_STUDENT_MASTER > 0  AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER ");
																	echo $res_grade_1->fields['GRADE']; ?>
																</div>
															</td>
															
															<td>
																<select id="PK_FINAL_GRADE_<?=$PK_FINAL_GRADE_IMPORT_DETAIL?>" name="PK_FINAL_GRADE_<?=$PK_FINAL_GRADE_IMPORT_DETAIL?>" class="form-control required-entry-final-grade PK_FINAL_GRADE_DROPDOWN" style="width:100px;<?  if($POSTED1 == 1 || $POSTED == 1 || stripos(" ".trim($res_grad->fields['MESSAGE']), "final grade posted") ) echo "background-color: #e8e8e8;"; ?>" <?  if($POSTED1 == 1 || $POSTED == 1 || stripos(" ".trim($res_grad->fields['MESSAGE']), "final grade posted") ) echo "disabled"; ?> >
																	<option selected ></option>
																	<? $res_type = $db->Execute("select PK_GRADE, CONCAT(GRADE, ' - ', NUMBER_GRADE) AS GRADE, ACTIVE from S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, GRADE ASC");
																	while (!$res_type->EOF) { 
																		$option_label = $res_type->fields['GRADE'];
																		if($res_type->fields['ACTIVE'] == 0)
																			$option_label .= " (Inactive)"; ?>
																		<option value="<?=$res_type->fields['PK_GRADE'] ?>" <? if($res_grad->fields['PK_FINAL_GRADE'] == $res_type->fields['PK_GRADE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																	<?	$res_type->MoveNext(); 
																	} ?>
																</select>
															</td>
															
															<td>
																<input type="text" name="FINAL_NUMERIC_GRADE_<?=$PK_FINAL_GRADE_IMPORT_DETAIL?>" id="FINAL_NUMERIC_GRADE_<?=$PK_FINAL_GRADE_IMPORT_DETAIL?>" value="<?=$res_grad->fields['FINAL_NUMERIC_GRADE']?>"  batch-amt="<?=$res_grad->fields['FINAL_NUMERIC_GRADE']?>" style="width:50px;<?  if($POSTED1 == 1 || $POSTED == 1 || stripos(" ".trim($res_grad->fields['MESSAGE']), "final grade posted") ) echo "background-color: #e8e8e8;"; ?>" <?  if($POSTED1 == 1 || $POSTED == 1 || stripos(" ".trim($res_grad->fields['MESSAGE']), "final grade posted") ) echo "readonly"; ?> onchange="paid_amount_value_change(<?=$PK_FINAL_GRADE_IMPORT_DETAIL?>);check_number_validation(this);" class="FINAL_NUMERIC_GRADE_CLS" data-id="<?=$PK_FINAL_GRADE_IMPORT_DETAIL?>"/> <!-- DIAM-70-->
															</td>
														</tr>
													<? $res_grad->MoveNext();
													} ?>
											
												</tbody>
											</table>
										</div>
                                    </div>
									<br />
									<div class="row">
										<div class="col-md-2">
											<?=IMPORTED_COUNT.': '.$IMPORTED_COUNT ?>
										</div>
										<div class="col-md-2">
											<?=TOTAL_COUNT.': '.$TOTAL_COUNT ?>
										</div>
										<div class="col-md-1">
										</div>
                                        <div class="col-md-7">
											<div class="form-group m-b-5 text-right" >
												<? if($POSTED == 0) { ?>
													<button type="button" onclick="delete_row()" name="btn" class="btn waves-effect waves-light btn-info" ><?=DELETE_SELECTED_RECORDS?></button>
													
													<button type="button" onclick="validate_form(1)" name="btn" class="btn waves-effect waves-light btn-info" ><?=SAVE?></button>
													<button type="button" onclick="validate_form(2)" name="btn" class="btn waves-effect waves-light btn-info" ><?=POST_FINAL ?></button>
													
													<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_final_grade_import_review'" ><?=CANCEL?></button>
												<? } ?>
												
												<input type="hidden" name="POST_GRAD" id="POST_GRAD" value="" >
												
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
		
		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<div class="col-12 col-sm-12 form-group">
								<?=DELETE_MESSAGE_GENERAL?>
							</div>
							<input type="hidden" id="STUD_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete_row(1)" class="btn waves-effect waves-light btn-info"><?=DELETE?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete_row(0)" ><?=CANCEL?></button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal" id="postModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=GRADE_BOOK_IMPORT?></h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<div class="col-12 col-sm-12 form-group">
								<?=POST_FINAL_GRADE_MSG?>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_post(1)" class="btn waves-effect waves-light btn-info"><?=PROCEED?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_post(0)" ><?=CANCEL?></button>
					</div>
				</div>
			</div>
		</div>
		
		
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>

	<?php if(isset($_GET['id']) && $POSTED == 0) {  ?>
		<script type="text/javascript">
			jQuery('.FINAL_NUMERIC_GRADE_CLS').prop("readonly", false);
			jQuery('.PK_FINAL_GRADE_DROPDOWN').prop("disabled", false);			
			jQuery(".FINAL_NUMERIC_GRADE_CLS").attr("style", "width:50px;");
			jQuery(".PK_FINAL_GRADE_DROPDOWN").attr("style", "width:100px;");
		</script>
	<? } ?>
	<script type="text/javascript">
	//DIAM-70
	function check_number_validation(e) {
				const regex = /[^\d.]|\.(?=.*\.)/g;
				const numbers = /^\d+$/g;
				const subst = '';
				const str = e.value;
				const result = str.replace(regex, subst);
				if (str.match(numbers)) {
					e.value = result + '.00';
				} else {
					e.value = result;
				}

				return e.value;
			}

		function paid_amount_value_change(id) {
			var FINAL_NUMERIC_NEW = document.getElementById('FINAL_NUMERIC_GRADE_' + id).value;
			var FINAL_NUMERIC_CHECK = document.getElementById('FINAL_NUMERIC_GRADE_' + id);
			var FINAL_NUMERIC_OLD = FINAL_NUMERIC_CHECK.getAttribute('batch-amt');
			var numbers = /[^\d.]|\.(?=.*\.)/g;
			if (FINAL_NUMERIC_NEW.match(numbers)) {
				alert("Please enter a valid amount. Please avoid spaces or other characters.");
				document.getElementById('FINAL_NUMERIC_GRADE_' + id).value = FINAL_NUMERIC_OLD;
				
			}
		}
	//DIAM-70
	function fun_select_all(){
		var str = '';
		if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
			str = true;
		else
			str = false;
			
		var DELETE_ID = document.getElementsByName('DELETE_ID[]')
		for(var i = 0 ; i < DELETE_ID.length ; i++){
			DELETE_ID[i].checked = str
		}
	}
	//DIAM-70
	function post_only_selected(type) {
		
			if(checkEmptyField()===true){
				if (jQuery("input:checkbox:checked:not('#SEARCH_SELECT_ALL')").length >= 1) {
				jQuery("input:checkbox:not(:checked)").parents("tr").remove();

				if(type==1){
					jQuery('.PK_FINAL_GRADE_DROPDOWN').prop("disabled", false);
					document.form1.submit();
					return true;
				}else if(type==2){
					jQuery('.PK_FINAL_GRADE_DROPDOWN').prop("disabled", false);
					return true;
				}

				}else{
					alert('Please Select a Record');
					return false;
				}
		}		
				
	}

	function checkEmptyField()
	{
		var isFormValid = true;

		jQuery(".FINAL_NUMERIC_GRADE_CLS").each(function(){
			
			if (jQuery(this).val().length == 0){
				jQuery(this).addClass("inputgradehighlight");				
				jQuery(this).focus();
				paid_amount_value_change(jQuery(this).data("id"));
				check_number_validation(this);
				isFormValid = false;
			}
			else{

				if(check_number_validation(this)==""){
					jQuery(this).addClass("inputgradehighlight");				
					jQuery(this).focus();
					isFormValid = false;
				}else{
				jQuery(this).removeClass("inputgradehighlight");
				}
			}
		});

		if (!isFormValid) { 
			alert("Please fill in all the Imported Final Numeric Grade");
			jQuery('.FINAL_NUMERIC_GRADE_CLS').prop("readonly", false);
			jQuery('.PK_FINAL_GRADE_DROPDOWN').prop("disabled", false);
		}

		return isFormValid;
	} 
	//DIAM-70

	function validate_form(type){
		if(type == 1 ) {
			document.getElementById('POST_GRAD').value = type
			post_only_selected(type); //DIAM-70
			//document.form1.submit();			

		} else {
			
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true) {
				
			 if(post_only_selected(type)){ //DIAM-70

				jQuery(document).ready(function($) {
					$("#postModal").modal({
						backdrop: 'static',
						keyboard: false
					})
				});
			} //DIAM-70
				
				//document.getElementById('POST_GRAD').value = type
				//document.form1.submit();
			}else{
				jQuery('.PK_FINAL_GRADE_DROPDOWN').prop("disabled", false);
				jQuery('.FINAL_NUMERIC_GRADE_CLS').prop("readonly", false);
			}
		}
	}
	
	function conf_post(val){
		jQuery(document).ready(function($) {
			if(val == 1) {
				document.getElementById('POST_GRAD').value = 2
				document.form1.submit();
			}
			$("#postModal").modal("hide");
		});
	}

	function delete_row(id){
		jQuery(document).ready(function($) {
			var str = '';
			var DELETE_ID = document.getElementsByName('DELETE_ID[]') 
			for(var i = 0 ; i < DELETE_ID.length ; i++){
				if(DELETE_ID[i].checked == true) {
					if(str != '')
						str += ',';
						
					str += DELETE_ID[i].value
				}
			}
			
			if(str == '')
				alert('Please Select a Record');
			else
				$("#deleteModal").modal()
		});
	}
	function conf_delete_row(val){
		jQuery(document).ready(function($) {
			if(val == 1) {
				var str = '';
				var DELETE_ID = document.getElementsByName('DELETE_ID[]')
				for(var i = 0 ; i < DELETE_ID.length ; i++){
					if(DELETE_ID[i].checked == true) {
						if(str != '')
							str += ',';
							
						str += DELETE_ID[i].value
					}
				}
				window.location.href = 'final_grade_import_map_result?act=del&id=<?=$_GET['id']?>&exclude=<?=$_GET['exclude']?>&t=<?=$_GET['t']?>&iid='+str;
			} else
				$("#deleteModal").modal("hide");
		});
	}
	
	</script>
</body>

</html>
