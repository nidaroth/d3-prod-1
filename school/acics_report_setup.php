<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/acics.php");
require_once("get_department_from_t.php");

require_once("check_access.php");

if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}
$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	$ACICS_ARRAY['EXCLUDED_PROGRAM']  = implode(",",$_POST['EXCLUDEDPrograms']);
	$ACICS_ARRAY['EXCLUDED_STUDENT_STATUS'] = implode(",",$_POST['EXCLUDEDStudentStatus']);
	$ACICS_ARRAY['COMPLETED_A_PROGRAM']   = implode(",",$_POST['COMPLETED_A_PROGRAM']);
	$ACICS_ARRAY['GRADUATED_FROM_A_PROGRAM'] = implode(",",$_POST['GRADUATED_FROM_A_PROGRAM']);
	//$ACICS_ARRAY['WITHDRAWAL_WAIVER']     = implode(",",$_POST['WITHDRAWAL_WAIVER']);
	$ACICS_ARRAY['WITHDRAWAL']    = implode(",",$_POST['WITHDRAWAL']);
	$ACICS_ARRAY['WITHDRAWAL_MILITARY']   = implode(",",$_POST['WITHDRAWAL_MILITARY']);
	$ACICS_ARRAY['WITHDRAWAL_DEATH']  = implode(",",$_POST['WITHDRAWAL_DEATH']);
	$ACICS_ARRAY['WITHDRAWAL_ENROLLED_IOC']   = implode(",",$_POST['WITHDRAWAL_ENROLLED_IOC']);
	$ACICS_ARRAY['WITHDRAWAL_INCARCERATION']  = implode(",",$_POST['WITHDRAWAL_INCARCERATION']);
	$ACICS_ARRAY['PLACED_JOB_TITLES']     = implode(",",$_POST['PLACED_JOB_TITLES']);
	$ACICS_ARRAY['NOT_AVAILABLE_MILITARY'] = implode(",",$_POST['NOT_AVAILABLE_MILITARY']);
	$ACICS_ARRAY['NOT_AVAILABLE_CONTINUING_ED'] = implode(",",$_POST['NOT_AVAILABLE_CONTINUING_ED']);
	$ACICS_ARRAY['NOT_AVAILABLE_ESL_PRORGAM'] = implode(",",$_POST['NOT_AVAILABLE_ESL_PRORGAM']);
	$ACICS_ARRAY['NOT_AVAILABLE_INCARCERATION'] = implode(",",$_POST['NOT_AVAILABLE_INCARCERATION']);
	$ACICS_ARRAY['NOT_AVAILABLE_PREGNANCY_DEATH_HEALTH'] = implode(",",$_POST['NOT_AVAILABLE_PREGNANCY_DEATH_HEALTH']);
	$ACICS_ARRAY['NOT_AVAILABLE_VISA'] = implode(",",$_POST['NOT_AVAILABLE_VISA']);
	$ACICS_ARRAY['PLACED_SKILLS'] = implode(",",$_POST['PLACED_SKILLS']);
	$ACICS_ARRAY['PLACED_BENEFIT_OF_TRAINING'] = implode(",",$_POST['PLACED_BENEFIT_OF_TRAINING']);
	//$ACICS_ARRAY['PLACED_WAIVER'] = implode(",",$_POST['PLACED_WAIVER']);
	$ACICS_ARRAY['NOT_PLACED'] = implode(",",$_POST['NOT_PLACED']);
	$ACICS_ARRAY['NON_CREDIT_PROGRAMS'] = implode(",",$_POST['NON_CREDIT_PROGRAMS']);
	$ACICS_ARRAY['NON_CREDIT_STATUS_COMPLETED'] = implode(",",$_POST['NON_CREDIT_STATUS_COMPLETED']);

    $res = $db->Execute("select * from S_ACICS_SETUP  WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	if($res->RecordCount() == 0){
		$ACICS_ARRAY['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$ACICS_ARRAY['CREATED_BY'] = $_SESSION['PK_USER'];
		$ACICS_ARRAY['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_ACICS_SETUP ', $ACICS_ARRAY, 'insert');
		$PK_S_ACICS_SETUP = $db->insert_ID();
	} else {
		$ACICS_ARRAY['EDITED_BY'] = $_SESSION['PK_USER'];
		$ACICS_ARRAY['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_ACICS_SETUP ', $ACICS_ARRAY, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_S_ACICS_SETUP = $_GET['id'];
	}
	header("location:acics_report_setup");
}

$res = $db->Execute("select * from S_ACICS_SETUP  WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$EXCLUDEDPrograms_ARR = explode(",",$res->fields['EXCLUDED_PROGRAM']);
$EXCLUDEDStudentStatus_ARR = explode(",",$res->fields['EXCLUDED_STUDENT_STATUS']);
$COMPLETED_A_PROGRAM_ARR = explode(",",$res->fields['COMPLETED_A_PROGRAM']);
$GRADUATED_FROM_A_PROGRAM_ARR = explode(",",$res->fields['GRADUATED_FROM_A_PROGRAM']);
//$WITHDRAWAL_WAIVER_ARR = explode(",",$res->fields['WITHDRAWAL_WAIVER']);
$WITHDRAWAL_ARR = explode(",",$res->fields['WITHDRAWAL']);
$WITHDRAWAL_MILITARY_ARR = explode(",",$res->fields['WITHDRAWAL_MILITARY']);
$WITHDRAWAL_DEATH_ARR = explode(",",$res->fields['WITHDRAWAL_DEATH']);
$WITHDRAWAL_ENROLLED_IOC_ARR = explode(",",$res->fields['WITHDRAWAL_ENROLLED_IOC']);
$WITHDRAWAL_INCARCERATION_ARR = explode(",",$res->fields['WITHDRAWAL_INCARCERATION']);
$PLACED_JOB_TITLES_ARR = explode(",",$res->fields['PLACED_JOB_TITLES']);
$NOT_AVAILABLE_MILITARY_ARR = explode(",",$res->fields['NOT_AVAILABLE_MILITARY']);
$NOT_AVAILABLE_CONTINUING_ED_ARR = explode(",",$res->fields['NOT_AVAILABLE_CONTINUING_ED']);
$NOT_AVAILABLE_ESL_PRORGAM_ARR = explode(",",$res->fields['NOT_AVAILABLE_ESL_PRORGAM']);
$NOT_AVAILABLE_INCARCERATION_ARR = explode(",",$res->fields['NOT_AVAILABLE_INCARCERATION']);
$NOT_AVAILABLE_PREGNANCY_DEATH_HEALTH_ARR = explode(",",$res->fields['NOT_AVAILABLE_PREGNANCY_DEATH_HEALTH']);
$NOT_AVAILABLE_VISA_ARR = explode(",",$res->fields['NOT_AVAILABLE_VISA']);
$PLACED_SKILLS_ARR = explode(",",$res->fields['PLACED_SKILLS']);
$PLACED_BENEFIT_OF_TRAINING_ARR = explode(",",$res->fields['PLACED_BENEFIT_OF_TRAINING']);
//$PLACED_WAIVER_ARR = explode(",",$res->fields['PLACED_WAIVER']);
$NOT_PLACED_ARR = explode(",",$res->fields['NOT_PLACED']);
$NON_CREDIT_PROGRAMS_ARR = explode(",",$res->fields['NON_CREDIT_PROGRAMS']);
$NON_CREDIT_STATUS_COMPLETED_ARR = explode(",",$res->fields['NON_CREDIT_STATUS_COMPLETED']);
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
	<title><?=ACICS_DOC_SETUP?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><?=ACICS_DOC_SETUP?></h4>
                    </div>
                </div>
                <div class="row">
                    
                    <div class="col-12">
                        <div class="card" style="margin-bottom: 0px !important;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">  
                                    </div>
                                    <div class="col-md-6" style="text-align: right;">    
                                        <button type="button" onclick="window.location.href='acics_report'" class="btn waves-effect waves-light btn-info">Go To Report</button>
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
										<div class="col-md-6 ">

										<div class="row d-flex">
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUSIONS?></label>
												</div>
											</div>
											<br />
										
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
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDED_STUDENT_STATUS?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="EXCLUDED_STUDENT_STATUS" name="EXCLUDEDStudentStatus[]" multiple class="form-control" >
													<? $res_type_ess = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
													while (!$res_type_ess->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type_ess->fields['PK_STUDENT_STATUS']; 
													foreach($EXCLUDEDStudentStatus_ARR as $EXCLUDED_STUDENT_STATUS){
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
													<label ><?=STUDENT_STATUS?></label>
												</div>
											</div>
											<br />

											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=COMPLETED_A_PROGRAM?></label>
												</div>
											</div>
											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="COMPLETED_A_PROGRAM" name="COMPLETED_A_PROGRAM[]" multiple class="form-control" >
													<? $res_type_ss = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
													while (!$res_type_ss->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type_ss->fields['PK_STUDENT_STATUS']; 
													foreach($COMPLETED_A_PROGRAM_ARR as $STUDENT_STATUS){
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
													<label ><?=GRADUATED_FROM_A_PROGRAM?></label>
												</div>
											</div>
											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="GRADUATED_FROM_A_PROGRAM" name="GRADUATED_FROM_A_PROGRAM[]" multiple class="form-control" >
													<? $res_type_ss = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
													while (!$res_type_ss->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type_ss->fields['PK_STUDENT_STATUS']; 
													foreach($GRADUATED_FROM_A_PROGRAM_ARR as $STUDENT_STATUS){
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
													<label ><?=WITHDRAWAL?></label>
												</div>
											</div>
											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="WITHDRAWAL" name="WITHDRAWAL[]" multiple class="form-control" >
													<? $res_type_ss = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
													while (!$res_type_ss->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type_ss->fields['PK_STUDENT_STATUS']; 
													foreach($WITHDRAWAL_ARR as $STUDENT_STATUS){
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
													<label ><?=DROP_REASONS?></label>
												</div>
											</div>
											<br />

											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=WITHDRAWAL_ACTIVE_MILITARY_SERVICE?></label>
												</div>
											</div>
											
											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="WITHDRAWAL_ACTIVE_MILITARY_SERVICE" name="WITHDRAWAL_MILITARY[]" multiple class="form-control" >
													<? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION,ACTIVE from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, DROP_REASON ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON']; 
															foreach($WITHDRAWAL_MILITARY_ARR as $PK_DROP_REASON_1){
																if($PK_DROP_REASON_1 == $PK_DROP_REASON) {
																	$selected = 'selected';
																	break;
																}
															} 	 
															$option_label = $res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
															<option value="<?=$PK_DROP_REASON?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>

															<!-- <option value="<?//=$PK_DROP_REASON?>" <?//=$selected?> ><?//=$res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION']?></option> -->
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

										


										<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=WITHDRAWAL_DEATH?></label>
												</div>
											</div>
											
											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="WITHDRAWAL_DEATH" name="WITHDRAWAL_DEATH[]" multiple class="form-control" >
													<? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION,ACTIVE from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, DROP_REASON ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON']; 
															foreach($WITHDRAWAL_DEATH_ARR as $PK_DROP_REASON_1){
																if($PK_DROP_REASON_1 == $PK_DROP_REASON) {
																	$selected = 'selected';
																	break;
																}
															} 	 
															$option_label = $res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
															<option value="<?=$PK_DROP_REASON?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

										


										<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=WITHDRAWAL_ENROLLED_IN_INSTITUTION_WITH_COMMON_OWNERSHIP?></label>
												</div>
											</div>
											
											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="WITHDRAWAL_ENROLLED_IN_INSTITUTION_WITH_COMMON_OWNERSHIP" name="WITHDRAWAL_ENROLLED_IOC[]" multiple class="form-control" >
													<? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION,ACTIVE from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, DROP_REASON ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON']; 
															foreach($WITHDRAWAL_ENROLLED_IOC_ARR as $PK_DROP_REASON_1){
																if($PK_DROP_REASON_1 == $PK_DROP_REASON) {
																	$selected = 'selected';
																	break;
																}
															} 	 
															$option_label = $res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
															<option value="<?=$PK_DROP_REASON?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

										

										<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=WITHDRAWAL_INCARCERATION?></label>
												</div>
											</div>
											
											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="WITHDRAWAL_INCARCERATION" name="WITHDRAWAL_INCARCERATION[]" multiple class="form-control" >
													<? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION,ACTIVE from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, DROP_REASON ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON']; 
															foreach($WITHDRAWAL_INCARCERATION_ARR as $PK_DROP_REASON_1){
																if($PK_DROP_REASON_1 == $PK_DROP_REASON) {
																	$selected = 'selected';
																	break;
																}
															} 	 
															$option_label = $res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
															<option value="<?=$PK_DROP_REASON?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>


										</div>


										
										<div class="col-md-6 ">										
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
													<label ><?=NOT_AVAILABLE_ACTIVE_DUTY_MILATARY_SERVICE?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="NOT_AVAILABLE_ACTIVE_DUTY_MILATARY_SERVICE" name="NOT_AVAILABLE_MILITARY[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
														while (!$res_type_placement->EOF) { 
														$selected 			= "";
														$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
														foreach($NOT_AVAILABLE_MILITARY_ARR as $PLACEMENT_STATUS){
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
													<label ><?=NOT_AVAILABLE_CONTINUING_EDUCATION?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="NOT_AVAILABLE_CONTINUING_EDUCATION" name="NOT_AVAILABLE_CONTINUING_ED[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
													while (!$res_type_placement->EOF) { 
														$selected 			= "";
														$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
														foreach($NOT_AVAILABLE_CONTINUING_ED_ARR as $PLACEMENT_STATUS){
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
													<label ><?=NOT_AVAILABLE_ENROLLMENT_IN_AN_ESL_PROGRAM?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="NOT_AVAILABLE_ENROLLMENT_IN_AN_ESL_PROGRAM" name="NOT_AVAILABLE_ESL_PRORGAM[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
													while (!$res_type_placement->EOF) { 
													$selected 			= "";
													$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
													foreach($NOT_AVAILABLE_ESL_PRORGAM_ARR as $PLACEMENT_STATUS){
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
													<label ><?=NOT_AVAILABLE_INCARCERATION?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="NOT_AVAILABLE_INCARCERATION" name="NOT_AVAILABLE_INCARCERATION[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
													while (!$res_type_placement->EOF) { 
													$selected 			= "";
													$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
													foreach($NOT_AVAILABLE_INCARCERATION_ARR as $PLACEMENT_STATUS){
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
													<label ><?=NOT_AVAILABLE_PREGNANCY_DEATH_OR_HEALTH_RELATED_ISSUES?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="NOT_AVAILABLE_PREGNANCY_DEATH_OR_HEALTH_RELATED_ISSUES" name="NOT_AVAILABLE_PREGNANCY_DEATH_HEALTH[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
													while (!$res_type_placement->EOF) { 
													$selected 			= "";
													$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
													foreach($NOT_AVAILABLE_PREGNANCY_DEATH_HEALTH_ARR as $PLACEMENT_STATUS){
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
													<label ><?=NOT_AVAILABLE_VISA_RESTRICATIONS?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="NOT_AVAILABLE_VISA_RESTRICATIONS" name="NOT_AVAILABLE_VISA[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
													while (!$res_type_placement->EOF) { 
													$selected 			= "";
													$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
													foreach($NOT_AVAILABLE_VISA_ARR as $PLACEMENT_STATUS){
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
													<label ><?=NOT_PLACED?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="NOT_PLACED" name="NOT_PLACED[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
													while (!$res_type_placement->EOF) { 
														$selected 			= "";
														$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
														foreach($NOT_PLACED_ARR as $PLACEMENT_STATUS){
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
													<label ><?=PLACED_BENEFIT_OF_TRAINING?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="PLACED_BENEFIT_OF_TRAINING" name="PLACED_BENEFIT_OF_TRAINING[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
													while (!$res_type_placement->EOF) { 
														$selected 			= "";
														$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
														foreach($PLACED_BENEFIT_OF_TRAINING_ARR as $PLACEMENT_STATUS){
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
													<label ><?=PLACED_JOB_TITLES?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="PLACED_JOB_TITLES" name="PLACED_JOB_TITLES[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
													while (!$res_type_placement->EOF) { 
														$selected 			= "";
														$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
														foreach($PLACED_JOB_TITLES_ARR as $PLACEMENT_STATUS){
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
													<label ><?=PLACED_SKILLS?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="PLACED_SKILLS" name="PLACED_SKILLS[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
													while (!$res_type_placement->EOF) { 
														$selected 			= "";
														$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
														foreach($PLACED_SKILLS_ARR as $PLACEMENT_STATUS){
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
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=NON_CREDIT_SHORT_TERM_MODULES?></label>
												</div>
											</div>
											<br />
													
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=NON_CREDIT_SHORT_TERM_MODULE_PROGRAMS?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="NON_CREDIT_SHORT_TERM_MODULE_PROGRAMS" name="NON_CREDIT_PROGRAMS[]" multiple class="form-control" >
													<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION,ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_CAMPUS_PROGRAM 	= $res_type->fields['PK_CAMPUS_PROGRAM']; 
															foreach($NON_CREDIT_PROGRAMS_ARR as $NON_CREDIT_PROGRAMS_VAL){
																if($NON_CREDIT_PROGRAMS_VAL == $PK_CAMPUS_PROGRAM) {
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
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=NON_CREDIT_SHORT_TERM_MODULE_STATUS_COMPLETED?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="NON_CREDIT_SHORT_TERM_MODULE_STATUS_COMPLETED" name="NON_CREDIT_STATUS_COMPLETED[]" multiple class="form-control" >
													<? $res_type_ess = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
													while (!$res_type_ess->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type_ess->fields['PK_STUDENT_STATUS']; 
													foreach($NON_CREDIT_STATUS_COMPLETED_ARR as $STUDENT_STATUS){
														if($STUDENT_STATUS == $PK_STUDENT_STATUS) {
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
																						
											
										</div>
									</div>
									
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										<div class="col-9 col-sm-9">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											<button type="button" onclick="window.location.href='acics_report'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
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
		
		//Status
		$('#COMPLETED_A_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COMPLETED_A_PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=COMPLETED_A_PROGRAM?> selected'
		});
		$('#GRADUATED_FROM_A_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=GRADUATED_FROM_A_PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=GRADUATED_FROM_A_PROGRAM?> selected'
		});
		$('#WITHDRAWAL').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=WITHDRAWAL?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=WITHDRAWAL?> selected'
		});
		
		//DROP
		$('#WITHDRAWAL_ACTIVE_MILITARY_SERVICE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=WITHDRAWAL_ACTIVE_MILITARY_SERVICE?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=WITHDRAWAL_ACTIVE_MILITARY_SERVICE?> selected'
		});

		$('#WITHDRAWAL_DEATH').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=WITHDRAWAL_DEATH?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=WITHDRAWAL_DEATH?> selected'
		});

		$('#WITHDRAWAL_ENROLLED_IN_INSTITUTION_WITH_COMMON_OWNERSHIP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=WITHDRAWAL_ENROLLED_IN_INSTITUTION_WITH_COMMON_OWNERSHIP?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=WITHDRAWAL_ENROLLED_IN_INSTITUTION_WITH_COMMON_OWNERSHIP?> selected'
		});

		$('#WITHDRAWAL_INCARCERATION').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=WITHDRAWAL_INCARCERATION?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=WITHDRAWAL_INCARCERATION?> selected'
		});
		
		//placement status
		$('#NOT_AVAILABLE_ACTIVE_DUTY_MILATARY_SERVICE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=NOT_AVAILABLE_ACTIVE_DUTY_MILATARY_SERVICE?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=NOT_AVAILABLE_ACTIVE_DUTY_MILATARY_SERVICE?> selected'
		});

		$('#NOT_AVAILABLE_CONTINUING_EDUCATION').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=NOT_AVAILABLE_CONTINUING_EDUCATION?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=NOT_AVAILABLE_CONTINUING_EDUCATION?> selected'
		});

		$('#NOT_AVAILABLE_ENROLLMENT_IN_AN_ESL_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=NOT_AVAILABLE_ENROLLMENT_IN_AN_ESL_PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=NOT_AVAILABLE_ENROLLMENT_IN_AN_ESL_PROGRAM?> selected'
		});


		$('#NOT_AVAILABLE_INCARCERATION').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=NOT_AVAILABLE_INCARCERATION?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=NOT_AVAILABLE_INCARCERATION?> selected'
		});


		$('#NOT_AVAILABLE_PREGNANCY_DEATH_OR_HEALTH_RELATED_ISSUES').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=NOT_AVAILABLE_PREGNANCY_DEATH_OR_HEALTH_RELATED_ISSUES?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=NOT_AVAILABLE_PREGNANCY_DEATH_OR_HEALTH_RELATED_ISSUES?> selected'
		});



		$('#NOT_AVAILABLE_VISA_RESTRICATIONS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=NOT_AVAILABLE_VISA_RESTRICATIONS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=NOT_AVAILABLE_VISA_RESTRICATIONS?> selected'
		});



		$('#NOT_PLACED').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=NOT_PLACED?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=NOT_PLACED?> selected'
		});



		$('#PLACED_BENEFIT_OF_TRAINING').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACED_BENEFIT_OF_TRAINING?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PLACED_BENEFIT_OF_TRAINING?> selected'
		});


		$('#PLACED_JOB_TITLES').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACED_JOB_TITLES?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PLACED_JOB_TITLES?> selected'
		});


		$('#PLACED_SKILLS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACED_SKILLS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PLACED_SKILLS?> selected'
		});

		// non credit
		$('#NON_CREDIT_SHORT_TERM_MODULE_PROGRAMS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=NON_CREDIT_SHORT_TERM_MODULE_PROGRAMS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=NON_CREDIT_SHORT_TERM_MODULE_PROGRAMS?> selected'
		});
        $('#NON_CREDIT_SHORT_TERM_MODULE_STATUS_COMPLETED').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=NON_CREDIT_SHORT_TERM_MODULE_STATUS_COMPLETED?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=NON_CREDIT_SHORT_TERM_MODULE_STATUS_COMPLETED?> selected'
		});

        
	});
	</script>
</body>

</html>