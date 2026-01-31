<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/oeds.php");
require_once("get_department_from_t.php");
require_once("../global/Models/S_OEDS_SETUP.php");

require_once("check_access.php");

if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}
$msg = '';	

$oeds= new OEDS_SETUP();
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	try {


	$OEDS_ARRAY['EXCLUDED_PROGRAMS']  = implode(",",$_POST['EXCLUDEDPrograms']);
	$OEDS_ARRAY['EXCLUDED_STUDENT_STATUS']  = implode(",",$_POST['EXCLUDED_STUDENT_STATUS']);
	
	// Adminssion
	$OEDS_ARRAY['APPLIED'] = implode(",",$_POST['REQUESTED_ADMISSION_STATUS']);
	$OEDS_ARRAY['ACCEPTED']   = implode(",",$_POST['ACCEPTED_ADMISSION_STATUS']);
	$OEDS_ARRAY['DENIED'] = implode(",",$_POST['DENIED_ADMISSION_STATUS']);
   // Registrar
	$OEDS_ARRAY['ENROLLED']    = implode(",",$_POST['ENROLLED_REGISTRAR_STATUS']);
	$OEDS_ARRAY['WITHDRAWN']   = implode(",",$_POST['NON_COMPLETER_REGISTRAR_STATUS']);
	$OEDS_ARRAY['GRADUATED']  = implode(",",$_POST['GRADUATED_REGISTRAR_STATUS']);

	// placement status

	$OEDS_ARRAY['EMPLOYED_IN_FIELD']   = implode(",",$_POST['EMPLOYED_IN_FIELD_PLACEMENT_STATUS']);
	$OEDS_ARRAY['EMPLOYED_IN_RELATED_FIELD']  = implode(",",$_POST['EMPLOYED_IN_SLIGHTFIELD_RELATED_PLACEMENT_STATUS']);
	$OEDS_ARRAY['EMPLOYED_IN_UNRELATED_FIELD']     = implode(",",$_POST['EMPLOYED_IN_UNRELATED_FIELD_PLACEMENT_STATUS']);
	$OEDS_ARRAY['SEEKING_EMPLOYMENT'] = implode(",",$_POST['SEEKING_EMPLOYMENT_PLACEMENT_STATUS']);
	$OEDS_ARRAY['STATUS_UNKNOWN'] = implode(",",$_POST['CONTINUING_EDUCATION_PLACEMENT_STATUS']);
	$OEDS_ARRAY['UNAVAILABLE_FOR_EMPLOYMENT'] = implode(",",$_POST['UNAVAILABLE_PLACEMENT_STATUS']);

	// Ledger codes
	$OEDS_ARRAY['TAP'] = implode(",",$_POST['TAP_LEDGER_CODE']);
	$OEDS_ARRAY['DIRECT_PLUS_LOANS'] = implode(",",$_POST['DIRECT_PLUS_LOANS']);
	$OEDS_ARRAY['FEDERAL_PELL_GRANT'] = implode(",",$_POST['FEDERAL_PELL_GRANT']);
	$OEDS_ARRAY['FEDERAL_TITLE_IV_LOANS'] = implode(",",$_POST['FEDERAL_TITLE_IV_LOANS']);
	$OEDS_ARRAY['ACCESS_VR'] = implode(",",$_POST['ACCESS_VR_LEDGER_CODE']);
	$OEDS_ARRAY['WIOA'] = implode(",",$_POST['WIA_LEDGER_CODE']);
	$OEDS_ARRAY['INCOME_SHARING_OR_DEFERRED_TUITION'] = implode(",",$_POST['INCOME_SHARING_OR_DEFERRED_TUITION']);
	$OEDS_ARRAY['PRIVATE_STUDENT_LOANS'] = implode(",",$_POST['PRIVATE_STUDENT_LOAN_LEDGER_CODE']);
	$OEDS_ARRAY['OTHER'] = implode(",",$_POST['OTHER_LEDGER_CODE']);
	$OEDS_ARRAY['VETERANS_BENEFITS'] = implode(",",$_POST['VETERANS_BENEFITS']);
	$OEDS_ARRAY['SCHOOL_ISSUED_CREDIT'] = implode(",",$_POST['SCHOOL_ISSUED_CREDIT']);
	$OEDS_ARRAY['EMPLOYER_SPONSORSHIP'] = implode(",",$_POST['EMPLOYER_SPONSORSHIP']);
	$OEDS_ARRAY['OTHER_CREDIT_EXTENDED'] = implode(",",$_POST['OTHER_CREDIT_EXTENDED']);
	$OEDS_ARRAY['SELF_FUNDED'] = implode(",",$_POST['SELF_FUNDED']);
	$OEDS_ARRAY['EDUCATION_OPPORTUNITY_GRANT'] = implode(",",$_POST['EDUCATION_OPPORTUNITY_GRANT']);

	$OEDS_ARRAY['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
	$OEDS_ARRAY['CREATED_BY'] = $_SESSION['PK_USER'];
	$OEDS_ARRAY['EDITED_BY'] = $_SESSION['PK_USER'];
	$OEDS_ARRAY['CREATED_ON'] = date("Y-m-d H:i:s");
    $OEDS_ARRAY['EDITED_ON'] = date("Y-m-d H:i:s");
	


	$query=$oeds::updateOrCreate(['PK_ACCOUNT'=>$_SESSION['PK_ACCOUNT']],$OEDS_ARRAY);
	if($query){
		flash('success', 'Record save successfully.', FLASH_SUCCESS);
	}else{
		flash('error', 'Error while saving the record. Please try again!', FLASH_ERROR);
	}
	}catch(Exception $e){
		//echo $e->getMessage();
		flash('error', 'Error while saving the record. Please try again!', FLASH_ERROR);		
	}
}

$res = $db->Execute("select * from NYOEDS_SETUP  WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$EXCLUDEDPrograms_ARR = explode(",",$res->fields['EXCLUDED_PROGRAMS']);
$EXCLUDED_STUDENT_STATUS_ARR = explode(",",$res->fields['EXCLUDED_STUDENT_STATUS']);
//Admission status
$REQUESTED_ADMISSION_STATUS_ARR = explode(",",$res->fields['APPLIED']);
$ACCEPTED_ADMISSION_STATUS_ARR = explode(",",$res->fields['ACCEPTED']);
$DENIED_ADMISSION_STATUS_ARR = explode(",",$res->fields['DENIED']);

//registrar status
$ENROLLED_REGISTRAR_STATUS_ARR = explode(",",$res->fields['ENROLLED']);
$NON_COMPLETER_REGISTRAR_STATUS_ARR = explode(",",$res->fields['WITHDRAWN']);
$GRADUATED_REGISTRAR_STATUS_ARR = explode(",",$res->fields['GRADUATED']);

//placement statues
$EMPLOYED_IN_FIELD_PLACEMENT_STATUS_ARR = explode(",",$res->fields['EMPLOYED_IN_FIELD']);
$EMPLOYED_IN_SLIGHTFIELD_RELATED_PLACEMENT_STATUS_ARR = explode(",",$res->fields['EMPLOYED_IN_RELATED_FIELD']);
$EMPLOYED_IN_UNRELATED_FIELD_PLACEMENT_STATUS_ARR = explode(",",$res->fields['EMPLOYED_IN_UNRELATED_FIELD_PLACEMENT_STATUS']);
$SEEKING_EMPLOYMENT_PLACEMENT_STATUS_ARR = explode(",",$res->fields['SEEKING_EMPLOYMENT']);
$CONTINUING_EDUCATION_PLACEMENT_STATUS_ARR = explode(",",$res->fields['STATUS_UNKNOWN']);
$UNAVAILABLE_PLACEMENT_STATUS_ARR = explode(",",$res->fields['UNAVAILABLE_FOR_EMPLOYMENT']);

// ledger codes
$TAP_LEDGER_CODE_ARR = explode(",",$res->fields['TAP']);
$DIRECT_PLUS_LOANS_ARR = explode(",",$res->fields['DIRECT_PLUS_LOANS']);
$FEDERAL_PELL_GRANT_ARR = explode(",",$res->fields['FEDERAL_PELL_GRANT']);
$FEDERAL_TITLE_IV_LOANS_ARR = explode(",",$res->fields['FEDERAL_TITLE_IV_LOANS']);

$ACCESS_VR_LEDGER_CODE_ARR = explode(",",$res->fields['ACCESS_VR']);
$WIA_LEDGER_CODE_ARR = explode(",",$res->fields['WIOA']);

$INCOME_SHARING_OR_DEFERRED_TUITION_ARR = explode(",",$res->fields['INCOME_SHARING_OR_DEFERRED_TUITION']);
$PRIVATE_STUDENT_LOAN_LEDGER_CODE_ARR = explode(",",$res->fields['PRIVATE_STUDENT_LOANS']);
$OTHER_LEDGER_CODE_ARR = explode(",",$res->fields['OTHER']);

$VETERANS_BENEFITS_ARR = explode(",",$res->fields['VETERANS_BENEFITS']);
$SCHOOL_ISSUED_CREDIT_ARR = explode(",",$res->fields['SCHOOL_ISSUED_CREDIT']);
$EMPLOYER_SPONSORSHIP_ARR = explode(",",$res->fields['EMPLOYER_SPONSORSHIP']);

$OTHER_CREDIT_EXTENDED_ARR = explode(",",$res->fields['OTHER_CREDIT_EXTENDED']);
$SELF_FUNDED_ARR = explode(",",$res->fields['SELF_FUNDED']);
$EDUCATION_OPPORTUNITY_GRANT_ARR = explode(",",$res->fields['EDUCATION_OPPORTUNITY_GRANT']);




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
	<title><?=OEDS_DOC_SETUP?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		.option_red > a > label{color:red !important}
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
                        <h4 class="text-themecolor"><?=OEDS_DOC_SETUP?></h4>
                    </div>
                </div>
                <div class="row">
                    
                    <div class="col-12">
                        <div class="card" style="margin-bottom: 0px !important;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">  
									<?php //echo flash(); ?>
                                    </div>
                                    <div class="col-md-4" style="text-align: right;">    
                                        <button type="button" onclick="window.location.href='oeds_report'" class="btn waves-effect waves-light btn-info">Go To Report</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<div class="row">
										<div class="col-md-4 ">

										
										
											<div class="row d-flex">
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDED_PROGRAM?></label>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													
													<select id="EXCLUDED_PROGRAM" name="EXCLUDEDPrograms[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION,ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_CAMPUS_PROGRAM 	= $res_type->fields['PK_CAMPUS_PROGRAM']; 
															foreach($EXCLUDEDPrograms_ARR as $EXCLUDED_PROGRAM_VAL){
																if($EXCLUDED_PROGRAM_VAL == $PK_CAMPUS_PROGRAM) {
																	$selected = 'selected';
																	break;
																}
															}
															 $option_label = $res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
															<option value="<?=$PK_CAMPUS_PROGRAM?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
															
															<!-- <option value="<?//=$PK_CAMPUS_PROGRAM?>" <?//=$selected?> ><?//=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option> -->

														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDED_STUDENT_STATUS?></label>
												</div>
											</div>

											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="EXCLUDED_STUDENT_STATUS" name="EXCLUDED_STUDENT_STATUS[]" multiple class="form-control" >
													<? $res_type_ess = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC, STUDENT_STATUS ASC");
													while (!$res_type_ess->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type_ess->fields['PK_STUDENT_STATUS']; 
													foreach($EXCLUDED_STUDENT_STATUS_ARR as $EXCLUDED_STUDENT_STATUS){
														if($EXCLUDED_STUDENT_STATUS == $PK_STUDENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 
													
													$option_label = $res_type_ess->fields['STUDENT_STATUS'].' - '.$res_type_ess->fields['DESCRIPTION'];
													if($res_type_ess->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type_ess->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type_ess->MoveNext();
												} ?>
													</select>
												</div>
											</div>
											

										

											<div class="row d-flex">
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=ADMISSION_STUDENT_STATUS?></label>
												</div>
											</div>
											<br />

											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Applied</label>
												</div>
											</div>
											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="REQUESTED_ADMISSION_STATUS" name="REQUESTED_ADMISSION_STATUS[]" multiple class="form-control" >
													<? $res_type_ss = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 1 order by ACTIVE DESC, STUDENT_STATUS ASC");
													while (!$res_type_ss->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type_ss->fields['PK_STUDENT_STATUS']; 
													foreach($REQUESTED_ADMISSION_STATUS_ARR as $STUDENT_STATUS){
														if($STUDENT_STATUS == $PK_STUDENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 
													
													$option_label = $res_type_ss->fields['STUDENT_STATUS'].' - '.$res_type_ss->fields['DESCRIPTION'];
													if($res_type_ss->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type_ss->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type_ss->MoveNext();
												} ?>
													</select>
												</div>
											</div>


											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Accepted</label>
												</div>
											</div>
											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="ACCEPTED_ADMISSION_STATUS" name="ACCEPTED_ADMISSION_STATUS[]" multiple class="form-control" >
													<? $res_type_ss = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 1 order by ACTIVE DESC, STUDENT_STATUS ASC");
													while (!$res_type_ss->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type_ss->fields['PK_STUDENT_STATUS']; 
													foreach($ACCEPTED_ADMISSION_STATUS_ARR as $STUDENT_STATUS){
														if($STUDENT_STATUS == $PK_STUDENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 
													
													$option_label = $res_type_ss->fields['STUDENT_STATUS'].' - '.$res_type_ss->fields['DESCRIPTION'];
													if($res_type_ss->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type_ss->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type_ss->MoveNext();
												} ?>
													</select>
												</div>
											</div>


											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Denied</label>
												</div>
											</div>
											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="DENIED_ADMISSION_STATUS" name="DENIED_ADMISSION_STATUS[]" multiple class="form-control" >
													<? $res_type_ss = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 1 order by ACTIVE DESC, STUDENT_STATUS ASC");
													while (!$res_type_ss->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type_ss->fields['PK_STUDENT_STATUS']; 
													foreach($DENIED_ADMISSION_STATUS_ARR as $STUDENT_STATUS){
														if($STUDENT_STATUS == $PK_STUDENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 
													
													$option_label = $res_type_ss->fields['STUDENT_STATUS'].' - '.$res_type_ss->fields['DESCRIPTION'];
													if($res_type_ss->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type_ss->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type_ss->MoveNext();
												} ?>
													</select>
												</div>
											</div>


											<div class="row d-flex">
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=REGISTAR_STUDENT_STATUS?></label>
												</div>
											</div>
											<br />

											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Enrolled</label>
												</div>
											</div>
											
											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
												<select id="ENROLLED_REGISTRAR_STATUS" name="ENROLLED_REGISTRAR_STATUS[]" multiple class="form-control" >
													<? $res_type_ss = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
													while (!$res_type_ss->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type_ss->fields['PK_STUDENT_STATUS']; 
													foreach($ENROLLED_REGISTRAR_STATUS_ARR as $STUDENT_STATUS){
														if($STUDENT_STATUS == $PK_STUDENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 
													
													$option_label = $res_type_ss->fields['STUDENT_STATUS'].' - '.$res_type_ss->fields['DESCRIPTION'];
													if($res_type_ss->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type_ss->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type_ss->MoveNext();
												} ?>
													</select>
												</div>
											</div>

										


										<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label>Withdrawn</label>
												</div>
											</div>
											
											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
												<select id="NON_COMPLETER_REGISTRAR_STATUS" name="NON_COMPLETER_REGISTRAR_STATUS[]" multiple class="form-control" >
													<? $res_type_ss = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
													while (!$res_type_ss->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type_ss->fields['PK_STUDENT_STATUS']; 
													foreach($NON_COMPLETER_REGISTRAR_STATUS_ARR as $STUDENT_STATUS){
														if($STUDENT_STATUS == $PK_STUDENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 
													
													$option_label = $res_type_ss->fields['STUDENT_STATUS'].' - '.$res_type_ss->fields['DESCRIPTION'];
													if($res_type_ss->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type_ss->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type_ss->MoveNext();
												} ?>
													</select>
												</div>
											</div>

										


										<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Graduated</label>
												</div>
											</div>
											
											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
												<select id="GRADUATED_REGISTRAR_STATUS" name="GRADUATED_REGISTRAR_STATUS[]" multiple class="form-control" >
													<? $res_type_ss = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
													while (!$res_type_ss->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type_ss->fields['PK_STUDENT_STATUS']; 
													foreach($GRADUATED_REGISTRAR_STATUS_ARR as $STUDENT_STATUS){
														if($STUDENT_STATUS == $PK_STUDENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 
													
													$option_label = $res_type_ss->fields['STUDENT_STATUS'].' - '.$res_type_ss->fields['DESCRIPTION'];
													if($res_type_ss->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type_ss->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type_ss->MoveNext();
												} ?>
													</select>
												</div>
											</div>

										

										


										</div>
									    <!-- STUDENT STATUSES END HERE -->

										
										<div class="col-md-4">										
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
													<label >Employed In Field</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="EMPLOYED_IN_FIELD_PLACEMENT_STATUS" name="EMPLOYED_IN_FIELD_PLACEMENT_STATUS[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
														while (!$res_type_placement->EOF) { 
														$selected 			= "";
														$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
														foreach($EMPLOYED_IN_FIELD_PLACEMENT_STATUS_ARR as $PLACEMENT_STATUS){
															if($PLACEMENT_STATUS == $PK_PLACEMENT_STATUS) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $res_type_placement->fields['PLACEMENT_STATUS'];
															if($res_type_placement->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
														
														<option value="<?=$res_type_placement->fields['PK_PLACEMENT_STATUS']?>" <?=$selected?> <? if($res_type_placement->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?	$res_type_placement->MoveNext();
													} ?>
													</select>
												</div>
											</div>	
											<!-- ============ -->
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Employed In Related Field</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="EMPLOYED_IN_SLIGHTFIELD_RELATED_PLACEMENT_STATUS" name="EMPLOYED_IN_SLIGHTFIELD_RELATED_PLACEMENT_STATUS[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
													while (!$res_type_placement->EOF) { 
														$selected 			= "";
														$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
														foreach($EMPLOYED_IN_SLIGHTFIELD_RELATED_PLACEMENT_STATUS_ARR as $PLACEMENT_STATUS){
															if($PLACEMENT_STATUS == $PK_PLACEMENT_STATUS) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $res_type_placement->fields['PLACEMENT_STATUS'];
															if($res_type_placement->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
														
														<option value="<?=$res_type_placement->fields['PK_PLACEMENT_STATUS']?>" <?=$selected?> <? if($res_type_placement->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?	$res_type_placement->MoveNext();
													} ?>
													</select>
												</div>
											</div>			

											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label>Employed In Unrelated Field</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="EMPLOYED_IN_UNRELATED_FIELD_PLACEMENT_STATUS" name="EMPLOYED_IN_UNRELATED_FIELD_PLACEMENT_STATUS[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
													while (!$res_type_placement->EOF) { 
													$selected 			= "";
													$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
													foreach($EMPLOYED_IN_SLIGHTFIELD_RELATED_PLACEMENT_STATUS_ARR as $PLACEMENT_STATUS){
														if($PLACEMENT_STATUS == $PK_PLACEMENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 	
													$option_label = $res_type_placement->fields['PLACEMENT_STATUS'];
															if($res_type_placement->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
														
														<option value="<?=$res_type_placement->fields['PK_PLACEMENT_STATUS']?>" <?=$selected?> <? if($res_type_placement->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
												<?	$res_type_placement->MoveNext();
												} ?>
													</select>
												</div>
											</div>			

											
													


											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Seeking Employment</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="SEEKING_EMPLOYMENT_PLACEMENT_STATUS" name="SEEKING_EMPLOYMENT_PLACEMENT_STATUS[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
													while (!$res_type_placement->EOF) { 
													$selected 			= "";
													$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
													foreach($SEEKING_EMPLOYMENT_PLACEMENT_STATUS_ARR as $PLACEMENT_STATUS){
														if($PLACEMENT_STATUS == $PK_PLACEMENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 	
													$option_label = $res_type_placement->fields['PLACEMENT_STATUS'];
															if($res_type_placement->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
														
														<option value="<?=$res_type_placement->fields['PK_PLACEMENT_STATUS']?>" <?=$selected?> <? if($res_type_placement->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
												<?	$res_type_placement->MoveNext();
												} ?>
													</select>
												</div>
											</div>			


											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Status Unknown</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="CONTINUING_EDUCATION_PLACEMENT_STATUS" name="CONTINUING_EDUCATION_PLACEMENT_STATUS[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
													while (!$res_type_placement->EOF) { 
													$selected 			= "";
													$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
													foreach($CONTINUING_EDUCATION_PLACEMENT_STATUS_ARR as $PLACEMENT_STATUS){
														if($PLACEMENT_STATUS == $PK_PLACEMENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 	
													$option_label = $res_type_placement->fields['PLACEMENT_STATUS'];
															if($res_type_placement->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
														
														<option value="<?=$res_type_placement->fields['PK_PLACEMENT_STATUS']?>" <?=$selected?> <? if($res_type_placement->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
												<?	$res_type_placement->MoveNext();
												} ?>
													</select>
												</div>
											</div>			

											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label>Unavailable For Employment</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="UNAVAILABLE_PLACEMENT_STATUS" name="UNAVAILABLE_PLACEMENT_STATUS[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
													while (!$res_type_placement->EOF) { 
														$selected 			= "";
														$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
														foreach($UNAVAILABLE_PLACEMENT_STATUS_ARR as $PLACEMENT_STATUS){
															if($PLACEMENT_STATUS == $PK_PLACEMENT_STATUS) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $res_type_placement->fields['PLACEMENT_STATUS'];
															if($res_type_placement->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
														
														<option value="<?=$res_type_placement->fields['PK_PLACEMENT_STATUS']?>" <?=$selected?> <? if($res_type_placement->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?	$res_type_placement->MoveNext();
													} ?>
													</select>
												</div>
											</div>			
											
											
																						
											
										</div>
										<!-- start ledger code -->
										<?php
										$ledger_codes=[];
										$query_result_ledger_code = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION,ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by ACTIVE DESC, CODE ASC");
										while (!$query_result_ledger_code->EOF) {
										
											$ledger_codes[] = $query_result_ledger_code->fields;
											$query_result_ledger_code->MoveNext();
										}

										
										
										?>
										<div class="col-md-4">										
											<div class="row d-flex">
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=LEDGER_CODE?></label>
												</div>
											</div>
											<br />
																		
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Tap</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="TAP_LEDGER_CODE" name="TAP_LEDGER_CODE[]" multiple class="form-control" >
													<? foreach ($ledger_codes as $key => $value) {															
														$selected 			= "";
														foreach($TAP_LEDGER_CODE_ARR as $lgdcode){
															if($lgdcode == $value['PK_AR_LEDGER_CODE']) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $value['CODE'].' '.$value['LEDGER_DESCRIPTION'];
														if($value['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; 
														?>
														<option value="<?=$value['PK_AR_LEDGER_CODE']?>" <?=$selected?> <? if($value['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?php } ?>
													</select>
												</div>
											</div>	

											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Direct Plus Loan</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="DIRECT_PLUS_LOANS" name="DIRECT_PLUS_LOANS[]" multiple class="form-control" >
													<? foreach ($ledger_codes as $key => $value) {															
														$selected 			= "";
														foreach($DIRECT_PLUS_LOANS_ARR as $lgdcode){
															if($lgdcode == $value['PK_AR_LEDGER_CODE']) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $value['CODE'].' '.$value['LEDGER_DESCRIPTION'];
														if($value['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; 
														?>
														<option value="<?=$value['PK_AR_LEDGER_CODE']?>" <?=$selected?> <? if($value['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?php } ?>
													</select>
												</div>
											</div>	
											<!-- ============ -->
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Federal Pell Grant</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="FEDERAL_PELL_GRANT" name="FEDERAL_PELL_GRANT[]" multiple class="form-control" >
													<? foreach ($ledger_codes as $key => $value) {															
														$selected 			= "";
														foreach($FEDERAL_PELL_GRANT_ARR as $lgdcode){
															if($lgdcode == $value['PK_AR_LEDGER_CODE']) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $value['CODE'].' '.$value['LEDGER_DESCRIPTION'];
														if($value['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; 
														?>
														<option value="<?=$value['PK_AR_LEDGER_CODE']?>" <?=$selected?> <? if($value['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?php } ?>
													</select>
												</div>
											</div>			

											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Federal Title IV Loans</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="FEDERAL_TITLE_IV_LOANS" name="FEDERAL_TITLE_IV_LOANS[]" multiple class="form-control" >
													<? foreach ($ledger_codes as $key => $value) {															
														$selected 			= "";
														foreach($FEDERAL_TITLE_IV_LOANS_ARR as $lgdcode){
															if($lgdcode == $value['PK_AR_LEDGER_CODE']) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $value['CODE'].' '.$value['LEDGER_DESCRIPTION'];
														if($value['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; 
														?>
														<option value="<?=$value['PK_AR_LEDGER_CODE']?>" <?=$selected?> <? if($value['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?php } ?>
													</select>
												</div>
											</div>			

						


											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Access VR</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="ACCESS_VR_LEDGER_CODE" name="ACCESS_VR_LEDGER_CODE[]" multiple class="form-control" >
													<?php foreach ($ledger_codes as $key => $value) {															
														$selected 			= "";
														foreach($ACCESS_VR_LEDGER_CODE_ARR as $lgdcode){
															if($lgdcode == $value['PK_AR_LEDGER_CODE']) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $value['CODE'].' '.$value['LEDGER_DESCRIPTION'];
														if($value['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; 
														?>
														<option value="<?=$value['PK_AR_LEDGER_CODE']?>" <?=$selected?> <? if($value['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?php } ?>
													</select>
												</div>
											</div>			


											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Income Sharing or Deferred Tuition Agreement</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="INCOME_SHARING_OR_DEFERRED_TUITION" name="INCOME_SHARING_OR_DEFERRED_TUITION[]" multiple class="form-control" >
														<?php foreach ($ledger_codes as $key => $value) {															
														$selected 			= "";
														foreach($INCOME_SHARING_OR_DEFERRED_TUITION_ARR as $lgdcode){
															if($lgdcode == $value['PK_AR_LEDGER_CODE']) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $value['CODE'].' '.$value['LEDGER_DESCRIPTION'];
														if($value['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; 
														?>
														<option value="<?=$value['PK_AR_LEDGER_CODE']?>" <?=$selected?> <? if($value['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?php } ?>
													</select>
												</div>
											</div>	

											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >WIOA</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="WIA_LEDGER_CODE" name="WIA_LEDGER_CODE[]" multiple class="form-control" >
													<?php foreach ($ledger_codes as $key => $value) {															
														$selected 			= "";
														foreach($WIA_LEDGER_CODE_ARR as $lgdcode){
															if($lgdcode == $value['PK_AR_LEDGER_CODE']) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $value['CODE'].' '.$value['LEDGER_DESCRIPTION'];
														if($value['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; 
														?>
														<option value="<?=$value['PK_AR_LEDGER_CODE']?>" <?=$selected?> <? if($value['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?php } ?>
													</select>
												</div>
											</div>		
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Veterans Benefits</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="VETERANS_BENEFITS" name="VETERANS_BENEFITS[]" multiple class="form-control" >
													<?php foreach ($ledger_codes as $key => $value) {															
														$selected 			= "";
														foreach($VETERANS_BENEFITS_ARR as $lgdcode){
															if($lgdcode == $value['PK_AR_LEDGER_CODE']) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $value['CODE'].' '.$value['LEDGER_DESCRIPTION'];
														if($value['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; 
														?>
														<option value="<?=$value['PK_AR_LEDGER_CODE']?>" <?=$selected?> <? if($value['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?php } ?>
													</select>
												</div>
											</div>	

											

										

											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Private Student Loan</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="PRIVATE_STUDENT_LOAN_LEDGER_CODE" name="PRIVATE_STUDENT_LOAN_LEDGER_CODE[]" multiple class="form-control" >
													<?php foreach ($ledger_codes as $key => $value) {															
														$selected 			= "";
														foreach($PRIVATE_STUDENT_LOAN_LEDGER_CODE_ARR as $lgdcode){
															if($lgdcode == $value['PK_AR_LEDGER_CODE']) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $value['CODE'].' '.$value['LEDGER_DESCRIPTION'];
														if($value['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; 
														?>
														<option value="<?=$value['PK_AR_LEDGER_CODE']?>" <?=$selected?> <? if($value['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?php } ?>
													</select>
												</div>
											</div>		
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >School Issued Credit</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="SCHOOL_ISSUED_CREDIT" name="SCHOOL_ISSUED_CREDIT[]" multiple class="form-control" >
													<?php foreach ($ledger_codes as $key => $value) {															
														$selected 			= "";
														foreach($SCHOOL_ISSUED_CREDIT_ARR as $lgdcode){
															if($lgdcode == $value['PK_AR_LEDGER_CODE']) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $value['CODE'].' '.$value['LEDGER_DESCRIPTION'];
														if($value['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; 
														?>
														<option value="<?=$value['PK_AR_LEDGER_CODE']?>" <?=$selected?> <? if($value['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?php } ?>
													</select>
												</div>
											</div>	


											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Employer Sponsorship</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="EMPLOYER_SPONSORSHIP" name="EMPLOYER_SPONSORSHIP[]" multiple class="form-control" >
													<?php foreach ($ledger_codes as $key => $value) {															
														$selected 			= "";
														foreach($EMPLOYER_SPONSORSHIP_ARR as $lgdcode){
															if($lgdcode == $value['PK_AR_LEDGER_CODE']) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $value['CODE'].' '.$value['LEDGER_DESCRIPTION'];
														if($value['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; 
														?>
														<option value="<?=$value['PK_AR_LEDGER_CODE']?>" <?=$selected?> <? if($value['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?php } ?>
													</select>
												</div>
											</div>	

											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Other</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="OTHER_LEDGER_CODE" name="OTHER_LEDGER_CODE[]" multiple class="form-control" >
													<?php foreach ($ledger_codes as $key => $value) {															
														$selected 			= "";
														foreach($OTHER_LEDGER_CODE_ARR as $lgdcode){
															if($lgdcode == $value['PK_AR_LEDGER_CODE']) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $value['CODE'].' '.$value['LEDGER_DESCRIPTION'];
														if($value['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; 
														?>
														<option value="<?=$value['PK_AR_LEDGER_CODE']?>" <?=$selected?> <? if($value['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?php } ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label>Other Credit Extended</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="OTHER_CREDIT_EXTENDED" name="OTHER_CREDIT_EXTENDED[]" multiple class="form-control" >
													<?php foreach ($ledger_codes as $key => $value) {															
														$selected 			= "";
														foreach($OTHER_CREDIT_EXTENDED_ARR as $lgdcode){
															if($lgdcode == $value['PK_AR_LEDGER_CODE']) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $value['CODE'].' '.$value['LEDGER_DESCRIPTION'];
														if($value['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; 
														?>
														<option value="<?=$value['PK_AR_LEDGER_CODE']?>" <?=$selected?> <? if($value['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?php } ?>
													</select>
												</div>
											</div>

											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Self Funded</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="SELF_FUNDED" name="SELF_FUNDED[]" multiple class="form-control" >
													<?php foreach ($ledger_codes as $key => $value) {															
														$selected 			= "";
														foreach($SELF_FUNDED_ARR as $lgdcode){
															if($lgdcode == $value['PK_AR_LEDGER_CODE']) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $value['CODE'].' '.$value['LEDGER_DESCRIPTION'];
														if($value['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; 
														?>
														<option value="<?=$value['PK_AR_LEDGER_CODE']?>" <?=$selected?> <? if($value['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?php } ?>
													</select>
												</div>
											</div>

											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Education Opportunity Grant</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="EDUCATION_OPPORTUNITY_GRANT" name="EDUCATION_OPPORTUNITY_GRANT[]" multiple class="form-control" >
													<?php foreach ($ledger_codes as $key => $value) {															
														$selected 			= "";
														foreach($EDUCATION_OPPORTUNITY_GRANT_ARR as $lgdcode){
															if($lgdcode == $value['PK_AR_LEDGER_CODE']) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $value['CODE'].' '.$value['LEDGER_DESCRIPTION'];
														if($value['ACTIVE'] == 0)
																		$option_label .= " (Inactive)"; 
														?>
														<option value="<?=$value['PK_AR_LEDGER_CODE']?>" <?=$selected?> <? if($value['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?php } ?>
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
											<button type="button" onclick="window.location.href='oeds_report'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
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

	</script>
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
					
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

		
		
		$('#REQUESTED_ADMISSION_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Applied',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Applied selected'
		});
		
		//Status
		$('#ACCEPTED_ADMISSION_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Accepted',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Accepted selected'
		});
		$('#DENIED_ADMISSION_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Denied',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Denied selected'
		});
		$('#ENROLLED_REGISTRAR_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Enrolled',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Enrolled selected'
		});
		
		//DROP
		$('#NON_COMPLETER_REGISTRAR_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Withdrawn',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Withdrawn selected'
		});

		$('#GRADUATED_REGISTRAR_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Graduated',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Graduated selected'
		});

		$('#EMPLOYED_IN_FIELD_PLACEMENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Employed In Field',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Employed In Field selected'
		});

		$('#EMPLOYED_IN_SLIGHTFIELD_RELATED_PLACEMENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Employed In Related Field',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Employed In Related Field selected'
		});

		$('#EMPLOYED_IN_UNRELATED_FIELD_PLACEMENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Employed In Unrelated Field',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Employed In Unrelated Field selected'
		});
		
	

		$('#SEEKING_EMPLOYMENT_PLACEMENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Seeking Employment',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Seeking Employment selected'
		});

		$('#CONTINUING_EDUCATION_PLACEMENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All status Unknown',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Status Unknown selected'
		});


		$('#UNAVAILABLE_PLACEMENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Unavailable For Employment',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Unavailable For Employment selected'
		});


		$('#TAP_LEDGER_CODE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Tap',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Tap selected'
		});



		$('#FEDERAL_STATE_STUDENT_LOAN_LEDGER_CODE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Federal/State Student Loan',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Federal/State Student Loan selected'
		});



		$('#PELL_LEDGER_CODE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Pell',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Pell selected'
		});



		$('#INCOME_SHARING_OR_DEFERRED_TUITION').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Income Sharing or Deferred Tuition Agreement',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Income Sharing or Deferred Tuition Agreement selected'
		});


		$('#ACCESS_VR_LEDGER_CODE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Access VR',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Access VR selected'
		});


		$('#WIA_LEDGER_CODE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All WIA',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'WIA selected'
		});

		// non credit
		$('#OTHER_STATE_OR_FEDERAL_LEDGER_CODE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Othere State or Federal',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Othere State or Federal selected'
		});

        $('#PRIVATE_STUDENT_LOAN_LEDGER_CODE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Private Student Loan',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Private Student Loan selected'
		});

		$('#OTHER_LEDGER_CODE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Other',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Other selected'
		});

		$('#DIRECT_PLUS_LOANS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Direct Plus Loan',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Direct Plus Loan selected'
		});

		$('#FEDERAL_TITLE_IV_LOANS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Federal Title IV Loans',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Federal Title IV Loans selected'
		});
		$('#FEDERAL_PELL_GRANT').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Federal Pell Grant',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Federal Pell Grant selected'
		});
		$('#VETERANS_BENEFITS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Veterans Benefits',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Veterans Benefits selected'
		});
		$('#SCHOOL_ISSUED_CREDIT').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All School Issued Credit',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'School Issued Credit selected'
		});
		$('#EMPLOYER_SPONSORSHIP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Employer Sponsorship',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Employer Sponsorship selected'
		});
		$('#OTHER_CREDIT_EXTENDED').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Other Credit Extended',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Other Credit Extended selected'
		});
		$('#SELF_FUNDED').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Self Funded',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Self Funded selected'
		});
		$('#EDUCATION_OPPORTUNITY_GRANT').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Education Opportunity Grant',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Education Opportunity Grant selected'
		});

        
	});
	</script>
</body>

</html>
