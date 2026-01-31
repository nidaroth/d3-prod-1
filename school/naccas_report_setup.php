<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/naccas.php");
require_once("get_department_from_t.php");

require_once("check_access.php");

if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}
$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	$NACCAS_ARRAY['EXCLUDED_PROGRAM'] 				= implode(",",$_POST['EXCLUDED_PROGRAM']);
	$NACCAS_ARRAY['EXCLUDED_STUDENT_STATUS'] 		= implode(",",$_POST['EXCLUDED_STUDENT_STATUS']);
	$NACCAS_ARRAY['GRADUATED_STUDENT_STATUS'] 		= implode(",",$_POST['GRADUATED_STUDENT_STATUS']);
	$NACCAS_ARRAY['WITHDRWAL_DROP_STUDENT_STATUS'] 	= implode(",",$_POST['WITHDRWAL_DROP_STUDENT_STATUS']);

	$NACCAS_ARRAY['START_DATE_TYPE'] 				= $_POST['START_DATE_TYPE'];
	$NACCAS_ARRAY['END_DATE_TYPE'] 					= $_POST['END_DATE_TYPE'];
	$NACCAS_ARRAY['ORIGINAL_SCH_GRAD_DATE_TYPE'] 	= $_POST['ORIGINAL_SCH_GRAD_DATE_TYPE'];

	$NACCAS_ARRAY['ELIGIBLE_PLACEMENT'] 			= implode(",",$_POST['ELIGIBLE_PLACEMENT']);
    $NACCAS_ARRAY['INELIGIBLE_PLACEMENT'] 			= implode(",",$_POST['INELIGIBLE_PLACEMENT']);
	$NACCAS_ARRAY['PLACED'] 				    	= implode(",",$_POST['PLACED']);

	$NACCAS_ARRAY['LICENSURE_EXAM'] 				= implode(",",$_POST['LICENSURE_EXAM']);
    $NACCAS_ARRAY['SAT_ALL_PART_EXAM'] 				= implode(",",$_POST['SAT_ALL_PART_EXAM']);
	$NACCAS_ARRAY['PASSED_EXAM'] 				    = implode(",",$_POST['PASSED_EXAM']);

	$NACCAS_ARRAY['EXEMPTION_TYPE'] 				= $_POST['EXEMPTION_TYPE'];
    $NACCAS_ARRAY['DROP_REASON'] 					= implode(",",$_POST['DROP_REASON']);
	$NACCAS_ARRAY['PLACEMENT_STUDENT_STATUS'] 		= implode(",",$_POST['PLACEMENT_STUDENT_STATUS']);

    $res = $db->Execute("select * from NACCAS_REPORT_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	if($res->RecordCount() == 0){
		$NACCAS_ARRAY['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$NACCAS_ARRAY['CREATED_BY'] = $_SESSION['PK_USER'];
		$NACCAS_ARRAY['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('NACCAS_REPORT_SETUP ', $NACCAS_ARRAY, 'insert');
		$PK_NACCAS_ARRAY = $db->insert_ID();
	} else {
		$NACCAS_ARRAY['EDITED_BY'] = $_SESSION['PK_USER'];
		$NACCAS_ARRAY['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('NACCAS_REPORT_SETUP ', $NACCAS_ARRAY, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		//$PK_NACCAS_ARRAY = $_GET['id'];
	}
	header("location:naccas_report_setup");
}

$res = $db->Execute("select * from NACCAS_REPORT_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 ");

$EXCLUDED_PROGRAM_ARR 			   = explode(",",$res->fields['EXCLUDED_PROGRAM']);
$EXCLUDED_STUDENT_STATUS_ARR 	   = explode(",",$res->fields['EXCLUDED_STUDENT_STATUS']);
$GRADUATED_STUDENT_STATUS_ARR 	   = explode(",",$res->fields['GRADUATED_STUDENT_STATUS']);
$WITHDRWAL_DROP_STUDENT_STATUS_ARR = explode(",",$res->fields['WITHDRWAL_DROP_STUDENT_STATUS']);

$START_DATE_TYPE_ARR 			   = $res->fields['START_DATE_TYPE'];
$END_DATE_TYPE_ARR 				   = $res->fields['END_DATE_TYPE'];
$ORIGINAL_SCH_GRAD_DATE_TYPE_ARR   = $res->fields['ORIGINAL_SCH_GRAD_DATE_TYPE'];

$ELIGIBLE_PLACEMENT_ARR 		   = explode(",",$res->fields['ELIGIBLE_PLACEMENT']);
$INELIGIBLE_PLACEMENT_ARR 		   = explode(",",$res->fields['INELIGIBLE_PLACEMENT']);
$PLACED_ARR 			           = explode(",",$res->fields['PLACED']);

$LICENSURE_EXAM_ARR 			   = explode(",",$res->fields['LICENSURE_EXAM']);
$SAT_ALL_PART_EXAM_ARR 			   = explode(",",$res->fields['SAT_ALL_PART_EXAM']);
$PASSED_EXAM_ARR 			       = explode(",",$res->fields['PASSED_EXAM']);

$EXEMPTION_TYPE 			   	   = $res->fields['EXEMPTION_TYPE'];
$DROP_REASON_ARR 			       = explode(",",$res->fields['DROP_REASON']);
$PLACEMENT_STUDENT_STATUS_ARR 	   = explode(",",$res->fields['PLACEMENT_STUDENT_STATUS']);

if($res->RecordCount() == 0)
{
    $EXEMPTION_TYPE 				   = 1;
	$START_DATE_TYPE_ARR 			   = 1;
	$END_DATE_TYPE_ARR 				   = 1;
	$ORIGINAL_SCH_GRAD_DATE_TYPE_ARR   = 1;
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
	<title><?=NACCAS_SETUP?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><?=NACCAS_SETUP?></h4>
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
                                        <button type="button" onclick="window.location.href='naccas_national_accrediting_commission_career_report'" class="btn waves-effect waves-light btn-info">Go To Report</button>
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
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDED_PROGRAM?></label>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-11 col-sm-11 form-group">
													<select id="EXCLUDED_PROGRAM" name="EXCLUDED_PROGRAM[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION,ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_CAMPUS_PROGRAM 	= $res_type->fields['PK_CAMPUS_PROGRAM']; 
															foreach($EXCLUDED_PROGRAM_ARR as $EXCLUDED_PROGRAM){
																if($EXCLUDED_PROGRAM == $PK_CAMPUS_PROGRAM) {
																	$selected = 'selected';
																	break;
																}
															} 
															$option_labels = $res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
															{
																$option_labels .= " (Inactive)";
															}
															?>
															<option value="<?=$PK_CAMPUS_PROGRAM?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) {echo "class='option_red'"; } ?> ><?=$option_labels?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="row d-flex">
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDED_STUDENT_STATUS?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-11 form-group">
													<select id="EXCLUDED_STUDENT_STATUS" name="EXCLUDED_STUDENT_STATUS[]" multiple class="form-control" >
													<? $res_type_ess = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
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
                                            <br>        
                                            <div class="row d-flex">
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=STUDENTS_STATUS?></label>
												</div>
											</div>
											<br>
											<div class="row d-flex">
                                                <div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=GRADUATE?></label>
												</div>
											</div>
											<div class="row d-flex">
                                                <div class="col-1 col-sm-1"></div>
												<div class="col-10 col-sm-10 form-group">
													<select id="GRADUATED_STUDENT_STATUS" name="GRADUATED_STUDENT_STATUS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															foreach($GRADUATED_STUDENT_STATUS_ARR as $GRADUATED_STUDENT_STATUS){
																if($GRADUATED_STUDENT_STATUS == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} 
															$option_labels = $res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
															{
																$option_labels .= " (Inactive)";
															}
															?>
															<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) {echo "class='option_red'"; } ?> ><?=$option_labels?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="row d-flex">
                                                <div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=WITHDRAWN?></label>
												</div>
											</div>
											<div class="row d-flex">
                                                <div class="col-1 col-sm-1"></div>
												<div class="col-10 col-sm-10 form-group">
													<select id="WITHDRWAL_DROP_STUDENT_STATUS" name="WITHDRWAL_DROP_STUDENT_STATUS[]" multiple class="form-control" >
													<? $res_type_ss = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
													while (!$res_type_ss->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type_ss->fields['PK_STUDENT_STATUS']; 
													foreach($WITHDRWAL_DROP_STUDENT_STATUS_ARR as $WITHDRWAL_DROP_STUDENT_STATUS){
														if($WITHDRWAL_DROP_STUDENT_STATUS == $PK_STUDENT_STATUS) {
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

                                            <br>    
                                            <div class="row d-flex">
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=START_DATE_TYPE?></label>
												</div>
											</div>

                                            <div class="row">
                                                <div class="col-1 col-sm-1"></div>
                                                <div class="col-md-10">
                                                    <div class="row form-group">
                                                        <div class="custom-control custom-radio col-md-4">
                                                            <input type="radio" id="START_DATE_TYPE_1" name="START_DATE_TYPE" value="1" <? if($START_DATE_TYPE_ARR == 1) echo "checked"; ?>  class="custom-control-input">
                                                            <label class="custom-control-label" for="START_DATE_TYPE_1"><?=CONTRACT_START_DATE?></label>
                                                        </div>
                                                        <div class="custom-control custom-radio col-md-4">
                                                            <input type="radio" id="START_DATE_TYPE_2" name="START_DATE_TYPE" value="0" <? if($START_DATE_TYPE_ARR == 0) echo "checked"; ?>  class="custom-control-input">
                                                            <label class="custom-control-label" for="START_DATE_TYPE_2"><?=FIRST_TERM_DATE?></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <br>    
                                            <div class="row d-flex">
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=END_DATE_TYPE?></label>
												</div>
											</div>

                                            <div class="row">
                                                <div class="col-1 col-sm-1"></div>
                                                <div class="col-md-10">
                                                    <div class="row form-group">
                                                        <div class="custom-control custom-radio col-md-4">
                                                            <input type="radio" id="END_DATE_TYPE_1" name="END_DATE_TYPE" value="1" <? if($END_DATE_TYPE_ARR == 1) echo "checked"; ?>  class="custom-control-input">
                                                            <label class="custom-control-label" for="END_DATE_TYPE_1"><?=CONTRACT_END_DATE?></label>
                                                        </div>
                                                        <div class="custom-control custom-radio col-md-4">
                                                            <input type="radio" id="END_DATE_TYPE_2" name="END_DATE_TYPE" value="0" <? if($END_DATE_TYPE_ARR == 0) echo "checked"; ?>  class="custom-control-input">
                                                            <label class="custom-control-label" for="END_DATE_TYPE_2"><?=STUDENT_STATUS_END_DATE?></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <br>    
                                            <div class="row d-flex">
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=ORIGINAL_SCH_GRAD_DATE_TYPE?></label>
												</div>
											</div>

                                            <div class="row">
                                                <div class="col-1 col-sm-1"></div>
                                                <div class="col-md-10">
                                                    <div class="row form-group">
                                                        <div class="custom-control custom-radio col-md-4">
                                                            <input type="radio" id="ORIGINAL_SCH_GRAD_DATE_TYPE_1" name="ORIGINAL_SCH_GRAD_DATE_TYPE" value="1" <? if($ORIGINAL_SCH_GRAD_DATE_TYPE_ARR == 1) echo "checked"; ?>  class="custom-control-input">
                                                            <label class="custom-control-label" for="ORIGINAL_SCH_GRAD_DATE_TYPE_1"><?=CONTRACT_END_DATE?></label>
                                                        </div>
                                                        <div class="custom-control custom-radio col-md-5">
                                                            <input type="radio" id="ORIGINAL_SCH_GRAD_DATE_TYPE_2" name="ORIGINAL_SCH_GRAD_DATE_TYPE" value="0" <? if($ORIGINAL_SCH_GRAD_DATE_TYPE_ARR == 0) echo "checked"; ?>  class="custom-control-input">
                                                            <label class="custom-control-label" for="ORIGINAL_SCH_GRAD_DATE_TYPE_2"><?=ORIGINAL_EXPECTED_GRAD_DATE?></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

										</div>
										
										<div class="col-md-6 ">
										
											<div class="row d-flex">
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=PLACEMENT_STATUS?></label>
												</div>
											</div>
											<br />
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=ELIGIBLE_PLACEMENT?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
                                                <select id="ELIGIBLE_PLACEMENT" name="ELIGIBLE_PLACEMENT[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS, PLACEMENT_STATUS, ACTIVE from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['PLACEMENT_STATUS'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($ELIGIBLE_PLACEMENT_ARR as $ELIGIBLE_PLACEMENT){
																if($ELIGIBLE_PLACEMENT == $PK_PLACEMENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_PLACEMENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
                                             
                                            <div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=INELIGIBLE_PLACEMENT?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="INELIGIBLE_PLACEMENT" name="INELIGIBLE_PLACEMENT[]" multiple class="form-control" >
                                                      <? $res_type = $db->Execute("select PK_PLACEMENT_STATUS, PLACEMENT_STATUS, ACTIVE from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['PLACEMENT_STATUS'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($INELIGIBLE_PLACEMENT_ARR as $INELIGIBLE_PLACEMENT){
																if($INELIGIBLE_PLACEMENT == $PK_PLACEMENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_PLACEMENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

                                            <div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=PLACED?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="PLACED" name="PLACED[]" multiple class="form-control" >
                                                        <? $res_type = $db->Execute("select PK_PLACEMENT_STATUS, PLACEMENT_STATUS, ACTIVE from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC,PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['PLACEMENT_STATUS'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($PLACED_ARR as $PLACED){
																if($PLACED == $PK_PLACEMENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_PLACEMENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

                                            <br>
                                            <div class="row d-flex">
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=LICENSURE?></label>
												</div>
											</div>
											<br />
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=LICENSURE_EXAM?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="LICENSURE_EXAM" name="LICENSURE_EXAM[]" multiple class="form-control" >
														<? $PK_DEPARTMENT = get_department_from_t(6);	
														$res_type = $db->Execute("select PK_NOTE_TYPE,NOTE_TYPE,DESCRIPTION,ACTIVE from M_NOTE_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) order by ACTIVE DESC, NOTE_TYPE ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['NOTE_TYPE'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
															
															$selected 			= "";
															$PK_NOTE_TYPE 	= $res_type->fields['PK_NOTE_TYPE']; 
															foreach($LICENSURE_EXAM_ARR as $LICENSURE_EXAM){
																if($LICENSURE_EXAM == $PK_NOTE_TYPE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_NOTE_TYPE?>" <?=$selected?>  <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
                                             
                                            <div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=SAT_ALL_PART_EXAM?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="SAT_ALL_PART_EXAM" name="SAT_ALL_PART_EXAM[]" multiple class="form-control" >
                                                      <? $PK_DEPARTMENT = get_department_from_t(6);	
														$res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS,ACTIVE from M_NOTE_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 3 AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) order by ACTIVE DESC, NOTE_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['NOTE_STATUS'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";

															$selected 			= "";
															$PK_NOTE_STATUS 	= $res_type->fields['PK_NOTE_STATUS']; 
															foreach($SAT_ALL_PART_EXAM_ARR as $SAT_ALL_PART_EXAM){
																if($SAT_ALL_PART_EXAM == $PK_NOTE_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_NOTE_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

                                            <div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=PASSED_EXAM?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="PASSED_EXAM" name="PASSED_EXAM[]" multiple class="form-control" >
                                                     <? $PK_DEPARTMENT = get_department_from_t(6);	
														$res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS,ACTIVE from M_NOTE_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 3 AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) order by ACTIVE DESC,NOTE_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['NOTE_STATUS'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";

															$selected 			= "";
															$PK_NOTE_STATUS 	= $res_type->fields['PK_NOTE_STATUS']; 
															foreach($PASSED_EXAM_ARR as $PASSED_EXAM){
																if($PASSED_EXAM == $PK_NOTE_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_NOTE_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

                                            <br>    
                                            <div class="row d-flex">
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=EXEMPTION_TYPE?></label>
												</div>
											</div>

                                            <div class="row">
                                                <div class="col-1 col-sm-1"></div>
                                                <div class="col-md-10">
                                                    <div class="row form-group">
                                                        <div class="custom-control custom-radio col-md-4">
                                                            <input type="radio" id="EXEMPTION_TYPE_1" name="EXEMPTION_TYPE" value="1" <? if($EXEMPTION_TYPE == 1) echo "checked"; ?>  class="custom-control-input" onclick="show_fields()" >
                                                            <label class="custom-control-label" for="EXEMPTION_TYPE_1"><?=DROP_REASON?></label>
                                                        </div>
                                                        <div class="custom-control custom-radio col-md-5">
                                                            <input type="radio" id="EXEMPTION_TYPE_2" name="EXEMPTION_TYPE" value="2" <? if($EXEMPTION_TYPE == 2) echo "checked"; ?>  class="custom-control-input" onclick="show_fields()" >
                                                            <label class="custom-control-label" for="EXEMPTION_TYPE_2"><?=PLACEMENT_STUDENT_STATUS?></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <br>

                                            <div <? if($EXEMPTION_TYPE != 1) { ?> style="display:none" <? } ?> id="TRANSFER_TYPE_TO_PK_DROP_REASON_DIV" >
                                                <div class="row d-flex">
                                                    <div class="col-1 col-sm-1"></div>
                                                    <div class="col-11 col-sm-11 focused">
                                                        <span class="bar"></span> 
                                                        <label ><?=DROP_REASON?></label>
                                                    </div>
                                                </div>
                                                <div class="row d-flex">
                                                    <div class="col-11 col-sm-1"></div>
                                                    <div class="col-11 col-sm-11 form-group">
                                                        <select id="DROP_REASON" name="DROP_REASON[]" multiple class="form-control" >
                                                             <? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION,ACTIVE from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, DROP_REASON ASC");
                                                                while (!$res_type->EOF) { 
																	$option_label 		= $res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)";
																	
                                                                    $selected 			= "";
                                                                    $PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON']; 
                                                                    foreach($DROP_REASON_ARR as $DROP_REASON){
                                                                        if($DROP_REASON == $PK_DROP_REASON) {
                                                                            $selected = 'selected';
                                                                            break;
                                                                        }
                                                                    } ?>
                                                                    <option value="<?=$PK_DROP_REASON?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
                                                                <?	$res_type->MoveNext();
                                                                } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div <? if($EXEMPTION_TYPE != 2) { ?> style="display:none" <? } ?> id="TRANSFER_TYPE_TO_PK_STUDENT_STATUS_DIV" >
                                                <div class="row d-flex">
                                                    <div class="col-1 col-sm-1"></div>
                                                    <div class="col-11 col-sm-11 focused">
                                                        <span class="bar"></span> 
                                                        <label ><?=PLACEMENT_STUDENT_STATUS?></label>
                                                    </div>
                                                </div>
                                                <div class="row d-flex">
                                                    <div class="col-11 col-sm-1"></div>
                                                    <div class="col-11 col-sm-11 form-group">
                                                        <select id="PLACEMENT_STUDENT_STATUS" name="PLACEMENT_STUDENT_STATUS[]" multiple class="form-control" >
                                                            <? $res_type = $db->Execute("select PK_PLACEMENT_STATUS, PLACEMENT_STATUS, ACTIVE from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE Desc,PLACEMENT_STATUS ASC");
                                                            while (!$res_type->EOF) { 
                                                                $option_label 		= $res_type->fields['PLACEMENT_STATUS'];
                                                                if($res_type->fields['ACTIVE'] == 0)
                                                                    $option_label .= " (Inactive)";
                                                                    
                                                                $selected 			= "";
                                                                $PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
                                                                foreach($PLACEMENT_STUDENT_STATUS_ARR as $PLACEMENT_STUDENT_STATUS){
                                                                    if($PLACEMENT_STUDENT_STATUS == $PK_PLACEMENT_STATUS) {
                                                                        $selected = 'selected';
                                                                        break;
                                                                    }
                                                                } ?>
                                                                <option value="<?=$PK_PLACEMENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
                                                            <?	$res_type->MoveNext();
                                                            } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
											
										</div>
									</div>
									<br><br>
									<div class="row">
										<div class="col-3 col-sm-5">
										</div>
										<div class="col-6 col-sm-6">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											<button type="button" onclick="window.location.href='naccas_national_accrediting_commission_career_report'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
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
        function show_fields()
        {
			document.getElementById('TRANSFER_TYPE_TO_PK_DROP_REASON_DIV').style.display 	 = 'none'
			document.getElementById('TRANSFER_TYPE_TO_PK_STUDENT_STATUS_DIV').style.display  = 'none'
			
			if(document.getElementById('EXEMPTION_TYPE_1').checked == true)
            {
                document.getElementById('TRANSFER_TYPE_TO_PK_DROP_REASON_DIV').style.display 	 = 'block'
            }
			else if(document.getElementById('EXEMPTION_TYPE_2').checked == true)
            {
                document.getElementById('TRANSFER_TYPE_TO_PK_STUDENT_STATUS_DIV').style.display = 'block'
            }
			
		}                                                
	</script>
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {

        show_fields();
		
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
		
		$('#GRADUATED_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=GRADUATE?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=GRADUATE?> selected'
		});

		$('#WITHDRWAL_DROP_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=WITHDRAWN?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=WITHDRAWN?> selected'
		});		
		
		$('#ELIGIBLE_PLACEMENT').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=ELIGIBLE_PLACEMENT?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=ELIGIBLE_PLACEMENT?> selected'
		});

        $('#INELIGIBLE_PLACEMENT').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=INELIGIBLE_PLACEMENT?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=INELIGIBLE_PLACEMENT?> selected'
		});
		
        $('#PLACED').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACED?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PLACED?> selected'
		});
        
        $('#LICENSURE_EXAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=LICENSURE_EXAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=LICENSURE_EXAM?> selected'
		});

        $('#SAT_ALL_PART_EXAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=SAT_ALL_PART_EXAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=SAT_ALL_PART_EXAM?> selected'
		});

        $('#PASSED_EXAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PASSED_EXAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PASSED_EXAM?> selected'
		});

        $('#DROP_REASON').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=DROP_REASON?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=DROP_REASON?> selected'
		});

        $('#PLACEMENT_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PLACEMENT_STUDENT_STATUS?> selected'
		});
        
	});
	</script>
</body>

</html>