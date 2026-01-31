<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/accsc.php");

require_once("check_access.php");

if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}
$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("select * from S_ACCSC_GRADUATION_EMPLOYMENT_CHART WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['EXCLUDED_PROGRAM'] 						= implode(",",$_POST['EXCLUDED_PROGRAM']);
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['EXCLUDED_STUDENT_STATUS'] 				= implode(",",$_POST['EXCLUDED_STUDENT_STATUS']);
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['TRANSFER_TYPE'] 							= $_POST['TRANSFER_TYPE'];
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['TRANSFER_TYPE_TO_PK_DROP_REASON'] 		= '';
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['TRANSFER_TYPE_TO_PK_STUDENT_STATUS'] 	= '';
	
	if($ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['TRANSFER_TYPE'] == 1) {
		$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['TRANSFER_TYPE_TO_PK_DROP_REASON'] 	= implode(",",$_POST['TRANSFER_TYPE_TO_PK_DROP_REASON']);
		$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['TRANSFER_TYPE_FROM_PK_DROP_REASON'] 	= implode(",",$_POST['TRANSFER_TYPE_FROM_PK_DROP_REASON']);
	} else if($ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['TRANSFER_TYPE'] == 2) {
		$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['TRANSFER_TYPE_TO_PK_STUDENT_STATUS'] 	= implode(",",$_POST['TRANSFER_TYPE_TO_PK_STUDENT_STATUS']);
		$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['TRANSFER_TYPE_FROM_PK_STUDENT_STATUS'] 	= implode(",",$_POST['TRANSFER_TYPE_FROM_PK_STUDENT_STATUS']);
	}
	
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['UNAVAILABLE_FOR_GRADUATION'] 		= implode(",",$_POST['UNAVAILABLE_FOR_GRADUATION']);
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['GRADUATES_WITHIN_150'] 				= implode(",",$_POST['GRADUATES_WITHIN_150']);
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['WITHDRAW_TERMINATES_STUDENTS'] 		= implode(",",$_POST['WITHDRAW_TERMINATES_STUDENTS']);
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['GRADUATES_FURTHER_EDUCATION'] 		= implode(",",$_POST['GRADUATES_FURTHER_EDUCATION']);
	
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['GRADUATES_UNAVAILABLE_FOR_EMPLOYEMENT'] 		= implode(",",$_POST['GRADUATES_UNAVAILABLE_FOR_EMPLOYEMENT']);
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['GRADUATES_EMPLOYED_IN_FIELD'] 				= implode(",",$_POST['GRADUATES_EMPLOYED_IN_FIELD']);
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['GRADUATES_UNRELATED_OCCUPATIONS'] 			= implode(",",$_POST['GRADUATES_UNRELATED_OCCUPATIONS']);
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['GRADUATES_UNEMPLOYED'] 						= implode(",",$_POST['GRADUATES_UNEMPLOYED']);
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['GRADUATES_UNKNOWN'] 							= implode(",",$_POST['GRADUATES_UNKNOWN']);
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['NON_GRADUATED_STUDENT'] 						= implode(",",$_POST['NON_GRADUATED_STUDENT']);
	
	if($res->RecordCount() == 0){
		$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['CREATED_BY'] = $_SESSION['PK_USER'];
		$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_ACCSC_GRADUATION_EMPLOYMENT_CHART', $ACCSC_EMPLOYMENT_VERIFICATION_SOURCE, 'insert');
		$PK_ACCSC_EMPLOYMENT_VERIFICATION_SOURCE = $db->insert_ID();
	} else {
		$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['EDITED_BY'] = $_SESSION['PK_USER'];
		$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_ACCSC_GRADUATION_EMPLOYMENT_CHART', $ACCSC_EMPLOYMENT_VERIFICATION_SOURCE, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_ACCSC_EMPLOYMENT_VERIFICATION_SOURCE = $_GET['id'];
	}
	
	//exit;
	header("location:accsc_graduation_and_employment_chart_setup");
}
$res = $db->Execute("select * from S_ACCSC_GRADUATION_EMPLOYMENT_CHART WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$TRANSFER_TYPE								= $res->fields['TRANSFER_TYPE'];
$TRANSFER_TYPE_TO_PK_DROP_REASON_ARR 		= explode(",",$res->fields['TRANSFER_TYPE_TO_PK_DROP_REASON']);
$TRANSFER_TYPE_FROM_PK_DROP_REASON_ARR 		= explode(",",$res->fields['TRANSFER_TYPE_FROM_PK_DROP_REASON']);
$TRANSFER_TYPE_TO_PK_STUDENT_STATUS_ARR 	= explode(",",$res->fields['TRANSFER_TYPE_TO_PK_STUDENT_STATUS']);
$TRANSFER_TYPE_FROM_PK_STUDENT_STATUS_ARR 	= explode(",",$res->fields['TRANSFER_TYPE_FROM_PK_STUDENT_STATUS']);

$UNAVAILABLE_FOR_GRADUATION_ARR 	= explode(",",$res->fields['UNAVAILABLE_FOR_GRADUATION']);
$GRADUATES_WITHIN_150_ARR 			= explode(",",$res->fields['GRADUATES_WITHIN_150']);
$WITHDRAW_TERMINATES_STUDENTS_ARR 	= explode(",",$res->fields['WITHDRAW_TERMINATES_STUDENTS']);
$GRADUATES_FURTHER_EDUCATION_ARR 	= explode(",",$res->fields['GRADUATES_FURTHER_EDUCATION']);

$GRADUATES_UNAVAILABLE_FOR_EMPLOYEMENT_ARR 	= explode(",",$res->fields['GRADUATES_UNAVAILABLE_FOR_EMPLOYEMENT']);
$GRADUATES_EMPLOYED_IN_FIELD_ARR 			= explode(",",$res->fields['GRADUATES_EMPLOYED_IN_FIELD']);
$GRADUATES_UNRELATED_OCCUPATIONS_ARR 		= explode(",",$res->fields['GRADUATES_UNRELATED_OCCUPATIONS']);
$GRADUATES_UNEMPLOYED_ARR 					= explode(",",$res->fields['GRADUATES_UNEMPLOYED']);
$GRADUATES_UNKNOWN_ARR 						= explode(",",$res->fields['GRADUATES_UNKNOWN']);
$NON_GRADUATED_STUDENT_ARR 					= explode(",",$res->fields['NON_GRADUATED_STUDENT']);

$EXCLUDED_PROGRAM_ARR 				= explode(",",$res->fields['EXCLUDED_PROGRAM']);
$EXCLUDED_STUDENT_STATUS_ARR 		= explode(",",$res->fields['EXCLUDED_STUDENT_STATUS']);

if($res->RecordCount() == 0)
	$TRANSFER_TYPE = 1;
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
	<title><?=MNU_ACCSC_GRA_EMP_CHART_SETUP?> | <?=$title?></title>
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
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_ACCSC_GRA_EMP_CHART_SETUP?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<div class="row">
										<div class="col-md-6 ">
											<div class="row d-flex">
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUSIONS?></label>
												</div>
											</div>
											<br /><br />
											
											<div class="row d-flex">
												<div class="col-12 col-sm-1"></div>
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDED_PROGRAM?></label>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="EXCLUDED_PROGRAM" name="EXCLUDED_PROGRAM[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_CAMPUS_PROGRAM 	= $res_type->fields['PK_CAMPUS_PROGRAM']; 
															foreach($EXCLUDED_PROGRAM_ARR as $EXCLUDED_PROGRAM){
																if($EXCLUDED_PROGRAM == $PK_CAMPUS_PROGRAM) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_CAMPUS_PROGRAM?>" <?=$selected?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDED_STUDENT_STATUS?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="EXCLUDED_STUDENT_STATUS" name="EXCLUDED_STUDENT_STATUS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by STUDENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															foreach($EXCLUDED_STUDENT_STATUS_ARR as $EXCLUDED_STUDENT_STATUS){
																if($EXCLUDED_STUDENT_STATUS == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=TRANSFER_TYPE?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11">
													<div class="custom-control custom-radio col-md-6">
														<input type="radio" id="TRANSFER_TYPE_1" name="TRANSFER_TYPE" value="1" class="custom-control-input" <? if($TRANSFER_TYPE == 1) echo "checked"; ?> onclick="show_fields()" >
														<label class="custom-control-label" for="TRANSFER_TYPE_1">Drop Reason</label>
													</div>
													<div class="custom-control custom-radio col-md-6">
														<input type="radio" id="TRANSFER_TYPE_2" name="TRANSFER_TYPE" value="2" class="custom-control-input" <? if($TRANSFER_TYPE == 2) echo "checked"; ?> onclick="show_fields()" >
														<label class="custom-control-label" for="TRANSFER_TYPE_2">Student Status</label>
													</div>
													<div class="custom-control custom-radio col-md-6">
														<input type="radio" id="TRANSFER_TYPE_3" name="TRANSFER_TYPE" value="3" class="custom-control-input" <? if($TRANSFER_TYPE == 3) echo "checked"; ?> onclick="show_fields()" >
														<label class="custom-control-label" for="TRANSFER_TYPE_3">Transfer In / Transfer Out</label>
													</div>
												</div>
											</div>
											<br /><br />
											
											<div <? if($TRANSFER_TYPE != 1) { ?> style="display:none" <? } ?> id="TRANSFER_TYPE_TO_PK_DROP_REASON_DIV" >
												<div class="row d-flex">
													<div class="col-1 col-sm-1"></div>
													<div class="col-11 col-sm-11 focused">
														<span class="bar"></span> 
														<label ><?=TRANSFER_TO_ANOTHER_PROGRAM_COHORT?></label>
													</div>
												</div>
												<div class="row" >
													<div class="col-11 col-sm-1"></div>
													<div class="col-11 col-sm-11 form-group">
														<select id="TRANSFER_TYPE_TO_PK_DROP_REASON" name="TRANSFER_TYPE_TO_PK_DROP_REASON[]" multiple class="form-control" >
															<? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by DROP_REASON ASC");
															while (!$res_type->EOF) { 
																$selected 			= "";
																$PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON']; 
																foreach($TRANSFER_TYPE_TO_PK_DROP_REASON_ARR as $TRANSFER_TYPE_TO_PK_DROP_REASON){
																	if($TRANSFER_TYPE_TO_PK_DROP_REASON == $PK_DROP_REASON) {
																		$selected = 'selected';
																		break;
																	}
																} ?>
																<option value="<?=$PK_DROP_REASON?>" <?=$selected?> ><?=$res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
												</div>
											
												<div class="row d-flex">
													<div class="col-1 col-sm-1"></div>
													<div class="col-11 col-sm-11 focused">
														<span class="bar"></span> 
														<label ><?=TRANSFER_FROM_ANOTHER_PROGRAM_COHORT?></label>
													</div>
												</div>
												<div class="row"  >
													<div class="col-11 col-sm-1"></div>
													<div class="col-11 col-sm-11 form-group">
														<select id="TRANSFER_TYPE_FROM_PK_DROP_REASON" name="TRANSFER_TYPE_FROM_PK_DROP_REASON[]" multiple class="form-control" >
															<? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by DROP_REASON ASC");
															while (!$res_type->EOF) { 
																$selected 			= "";
																$PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON']; 
																foreach($TRANSFER_TYPE_FROM_PK_DROP_REASON_ARR as $TRANSFER_TYPE_FROM_PK_DROP_REASON){
																	if($TRANSFER_TYPE_FROM_PK_DROP_REASON == $PK_DROP_REASON) {
																		$selected = 'selected';
																		break;
																	}
																} ?>
																<option value="<?=$PK_DROP_REASON?>" <?=$selected?> ><?=$res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
												</div>
											</div>
											
											<div <? if($TRANSFER_TYPE != 2) { ?> style="display:none" <? } ?> id="TRANSFER_TYPE_TO_PK_STUDENT_STATUS_DIV" >
												<div class="row d-flex">
													<div class="col-1 col-sm-1"></div>
													<div class="col-11 col-sm-11 focused">
														<span class="bar"></span> 
														<label ><?=TRANSFER_TO_ANOTHER_PROGRAM_COHORT?></label>
													</div>
												</div>
												<div class="row" >
													<div class="col-11 col-sm-1"></div>
													<div class="col-11 col-sm-11 form-group">
														<select id="TRANSFER_TYPE_TO_PK_STUDENT_STATUS" name="TRANSFER_TYPE_TO_PK_STUDENT_STATUS[]" multiple class="form-control" >
															<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by STUDENT_STATUS ASC");
															while (!$res_type->EOF) { 
																$selected 			= "";
																$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
																foreach($TRANSFER_TYPE_TO_PK_STUDENT_STATUS_ARR as $TRANSFER_TYPE_TO_PK_STUDENT_STATUS){
																	if($TRANSFER_TYPE_TO_PK_STUDENT_STATUS == $PK_STUDENT_STATUS) {
																		$selected = 'selected';
																		break;
																	}
																} ?>
																<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
												</div>
												
												<div class="row d-flex">
													<div class="col-1 col-sm-1"></div>
													<div class="col-11 col-sm-11 focused">
														<span class="bar"></span> 
														<label ><?=TRANSFER_FROM_ANOTHER_PROGRAM_COHORT?></label>
													</div>
												</div>
												<div class="row" >
													<div class="col-11 col-sm-1"></div>
													<div class="col-11 col-sm-11 form-group">
														<select id="TRANSFER_TYPE_FROM_PK_STUDENT_STATUS" name="TRANSFER_TYPE_FROM_PK_STUDENT_STATUS[]" multiple class="form-control" >
															<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by STUDENT_STATUS ASC");
															while (!$res_type->EOF) { 
																$selected 			= "";
																$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
																foreach($TRANSFER_TYPE_FROM_PK_STUDENT_STATUS_ARR as $TRANSFER_TYPE_FROM_PK_STUDENT_STATUS){
																	if($TRANSFER_TYPE_FROM_PK_STUDENT_STATUS == $PK_STUDENT_STATUS) {
																		$selected = 'selected';
																		break;
																	}
																} ?>
																<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
												</div>
											</div>
											
										</div>
										
										<div class="col-md-6 ">
											<div class="row d-flex">
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=DROP_REASONS?></label>
												</div>
											</div>
											<br />
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=UNAVAILABLE_FOR_GRADUATION?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="UNAVAILABLE_FOR_GRADUATION" name="UNAVAILABLE_FOR_GRADUATION[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by DROP_REASON ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON']; 
															foreach($UNAVAILABLE_FOR_GRADUATION_ARR as $PK_DROP_REASON_1){
																if($PK_DROP_REASON_1 == $PK_DROP_REASON) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_DROP_REASON?>" <?=$selected?> ><?=$res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=STUDENT_STATUS?></label>
												</div>
											</div>
											<br />
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=GRADUATES_WITHIN_150?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="GRADUATES_WITHIN_150" name="GRADUATES_WITHIN_150[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by STUDENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															foreach($GRADUATES_WITHIN_150_ARR as $PK_STUDENT_STATUS_1){
																if($PK_STUDENT_STATUS_1 == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=WITHDRAW_TERMINATES_STUDENTS?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="WITHDRAW_TERMINATES_STUDENTS" name="WITHDRAW_TERMINATES_STUDENTS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by STUDENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															foreach($WITHDRAW_TERMINATES_STUDENTS_ARR as $PK_STUDENT_STATUS_1){
																if($PK_STUDENT_STATUS_1 == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=PLACEMENT_STATUS?></label>
												</div>
											</div>
											<br />
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=GRADUATES_FURTHER_EDUCATION?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="GRADUATES_FURTHER_EDUCATION" name="GRADUATES_FURTHER_EDUCATION[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS,PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 				= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($GRADUATES_FURTHER_EDUCATION_ARR as $PK_PLACEMENT_STATUS_1){
																if($PK_PLACEMENT_STATUS_1 == $PK_PLACEMENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_PLACEMENT_STATUS?>" <?=$selected?> ><?=$res_type->fields['PLACEMENT_STATUS'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=GRADUATES_UNAVAILABLE_FOR_EMPLOYEMENT?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="GRADUATES_UNAVAILABLE_FOR_EMPLOYEMENT" name="GRADUATES_UNAVAILABLE_FOR_EMPLOYEMENT[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS,PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 				= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($GRADUATES_UNAVAILABLE_FOR_EMPLOYEMENT_ARR as $PK_PLACEMENT_STATUS_1){
																if($PK_PLACEMENT_STATUS_1 == $PK_PLACEMENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_PLACEMENT_STATUS?>" <?=$selected?> ><?=$res_type->fields['PLACEMENT_STATUS'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=GRADUATES_EMPLOYED_IN_FIELD?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="GRADUATES_EMPLOYED_IN_FIELD" name="GRADUATES_EMPLOYED_IN_FIELD[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS,PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 				= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($GRADUATES_EMPLOYED_IN_FIELD_ARR as $PK_PLACEMENT_STATUS_1){
																if($PK_PLACEMENT_STATUS_1 == $PK_PLACEMENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_PLACEMENT_STATUS?>" <?=$selected?> ><?=$res_type->fields['PLACEMENT_STATUS'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=GRADUATES_UNRELATED_OCCUPATIONS?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="GRADUATES_UNRELATED_OCCUPATIONS" name="GRADUATES_UNRELATED_OCCUPATIONS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS,PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 				= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($GRADUATES_UNRELATED_OCCUPATIONS_ARR as $PK_PLACEMENT_STATUS_1){
																if($PK_PLACEMENT_STATUS_1 == $PK_PLACEMENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_PLACEMENT_STATUS?>" <?=$selected?> ><?=$res_type->fields['PLACEMENT_STATUS'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=GRADUATES_UNEMPLOYED?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="GRADUATES_UNEMPLOYED" name="GRADUATES_UNEMPLOYED[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS,PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 				= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($GRADUATES_UNEMPLOYED_ARR as $PK_PLACEMENT_STATUS_1){
																if($PK_PLACEMENT_STATUS_1 == $PK_PLACEMENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_PLACEMENT_STATUS?>" <?=$selected?> ><?=$res_type->fields['PLACEMENT_STATUS'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=GRADUATES_UNKNOWN?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="GRADUATES_UNKNOWN" name="GRADUATES_UNKNOWN[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS,PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 				= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($GRADUATES_UNKNOWN_ARR as $PK_PLACEMENT_STATUS_1){
																if($PK_PLACEMENT_STATUS_1 == $PK_PLACEMENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_PLACEMENT_STATUS?>" <?=$selected?> ><?=$res_type->fields['PLACEMENT_STATUS'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=NON_GRADUATED_STUDENT?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="NON_GRADUATED_STUDENT" name="NON_GRADUATED_STUDENT[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS,PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 				= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($NON_GRADUATED_STUDENT_ARR as $PK_PLACEMENT_STATUS_1){
																if($PK_PLACEMENT_STATUS_1 == $PK_PLACEMENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_PLACEMENT_STATUS?>" <?=$selected?> ><?=$res_type->fields['PLACEMENT_STATUS'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
										</div>
									</div>
									
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										<div class="col-9 col-sm-9">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											<button type="button" onclick="window.location.href='index'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
										</div>
									</div>
								</div>
							</form>
                        </div>
					</div>
				</div>
				
            </div>
        </div>

        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>

	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
		var form1 = new Validation('form1');
		function show_fields(){
			document.getElementById('TRANSFER_TYPE_TO_PK_DROP_REASON_DIV').style.display 	 = 'none'
			document.getElementById('TRANSFER_TYPE_TO_PK_STUDENT_STATUS_DIV').style.display  = 'none'
			
			if(document.getElementById('TRANSFER_TYPE_1').checked == true)
				document.getElementById('TRANSFER_TYPE_TO_PK_DROP_REASON_DIV').style.display 	 = 'block'
			else if(document.getElementById('TRANSFER_TYPE_2').checked == true)
				document.getElementById('TRANSFER_TYPE_TO_PK_STUDENT_STATUS_DIV').style.display = 'block'
			
		}
	</script>
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		
		$('#TRANSFER_TYPE_TO_PK_DROP_REASON').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=TRANSFER_TYPE_TO_PK_DROP_REASON?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=TRANSFER_TYPE_TO_PK_DROP_REASON?> selected'
		});
		
		$('#EXCLUDED_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDED_PROGRAM?> selected'
		});
		
		$('#EXCLUDED_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDED_STUDENT_STATUS?> selected'
		});
		
		$('#TRANSFER_TYPE_TO_PK_DROP_REASON').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=TRANSFER_TO_ANOTHER_PROGRAM_COHORT?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=TRANSFER_TO_ANOTHER_PROGRAM_COHORT?> selected'
		});
		
		$('#TRANSFER_TYPE_FROM_PK_DROP_REASON').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=DROP_REASONS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=DROP_REASONS?> selected'
		});
		
		$('#TRANSFER_TYPE_TO_PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=STUDENT_STATUS?> selected'
		});
		
		$('#TRANSFER_TYPE_FROM_PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=STUDENT_STATUS?> selected'
		});
		
		$('#UNAVAILABLE_FOR_GRADUATION').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=DROP_REASONS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=DROP_REASONS?> selected'
		});
		
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=STUDENT_STATUS?> selected'
		});
		
		$('#GRADUATES_WITHIN_150').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PLACEMENT_STATUS?> selected'
		});
		
		$('#WITHDRAW_TERMINATES_STUDENTS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PLACEMENT_STATUS?> selected'
		});
		
		$('#GRADUATES_FURTHER_EDUCATION').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PLACEMENT_STATUS?> selected'
		});
		
		$('#GRADUATES_UNAVAILABLE_FOR_EMPLOYEMENT').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PLACEMENT_STATUS?> selected'
		});
		
		$('#GRADUATES_EMPLOYED_IN_FIELD').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PLACEMENT_STATUS?> selected'
		});
		
		$('#GRADUATES_UNRELATED_OCCUPATIONS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PLACEMENT_STATUS?> selected'
		});
		
		$('#GRADUATES_UNEMPLOYED').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PLACEMENT_STATUS?> selected'
		});
		
		$('#GRADUATES_UNKNOWN').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PLACEMENT_STATUS?> selected'
		});
		
		$('#NON_GRADUATED_STUDENT').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PLACEMENT_STATUS?> selected'
		});
		
		
		
	});
	</script>
</body>

</html>