<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/TWC_report.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT TWC FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['TWC'] == 0 ){
	header("location:../index");
	exit;
}

$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("select * from S_TWC_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$TWC_SETUP['EXCLUDED_STUDENT_STATUS'] 								= implode(",",$_POST['EXCLUDED_STUDENT_STATUS']);
	$TWC_SETUP['EXCLUDED_PROGRAM'] 										= implode(",",$_POST['EXCLUDED_PROGRAM']);
	$TWC_SETUP['STUDENT_STATUS_GRADUATES'] 								= implode(",",$_POST['STUDENT_STATUS_GRADUATES']);
	$TWC_SETUP['STUDENT_STATUS_DROPS'] 									= implode(",",$_POST['STUDENT_STATUS_DROPS']);
	$TWC_SETUP['STUDENT_STATUS_OTHER_WITHDRAWLS'] 						= implode(",",$_POST['STUDENT_STATUS_OTHER_WITHDRAWLS']);
	$TWC_SETUP['DROP_REASON_MILITARY'] 									= implode(",",$_POST['DROP_REASON_MILITARY']);
	$TWC_SETUP['DROP_REASON_INCARCERATED'] 								= implode(",",$_POST['DROP_REASON_INCARCERATED']);
	$TWC_SETUP['DROP_REASON_DECEASED'] 									= implode(",",$_POST['DROP_REASON_DECEASED']);
	$TWC_SETUP['PLACEMENT_STUDENT_STATUS_INCARCERATED'] 				= implode(",",$_POST['PLACEMENT_STUDENT_STATUS_INCARCERATED']);
	$TWC_SETUP['PLACEMENT_STUDENT_STATUS_DECEASED'] 					= implode(",",$_POST['PLACEMENT_STUDENT_STATUS_DECEASED']);
	$TWC_SETUP['PLACEMENT_STUDENT_STATUS_POSTSECONDARY_EDUCATION'] 		= implode(",",$_POST['PLACEMENT_STUDENT_STATUS_POSTSECONDARY_EDUCATION']);
	$TWC_SETUP['PLACEMENT_STUDENT_STATUS_PLACED'] 						= implode(",",$_POST['PLACEMENT_STUDENT_STATUS_PLACED']);
	$TWC_SETUP['PLACEMENT_STUDENT_STATUS_OTHER'] 						= implode(",",$_POST['PLACEMENT_STUDENT_STATUS_OTHER']);
	$TWC_SETUP['PLACEMENT_STUDENT_STATUS_NOT_PLACED'] 					= implode(",",$_POST['PLACEMENT_STUDENT_STATUS_NOT_PLACED']);
	$TWC_SETUP['PLACEMENT_STUDENT_STATUS_MILITARY'] 					= implode(",",$_POST['PLACEMENT_STUDENT_STATUS_MILITARY']);
	$TWC_SETUP['STUDENT_STATUS_OTHER_COMPLETERS'] 						= implode(",",$_POST['STUDENT_STATUS_OTHER_COMPLETERS']);
	$TWC_SETUP['STUDENT_STATUS_UNKNOWN'] 								= implode(",",$_POST['STUDENT_STATUS_UNKNOWN']);
	
	if($res->RecordCount() == 0){
		$TWC_SETUP['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$TWC_SETUP['CREATED_BY'] = $_SESSION['PK_USER'];
		$TWC_SETUP['CREATED_ON'] = date("Y-m-d H:i:s");
		$TWC_SETUP['EDITED_BY']  = $_SESSION['PK_USER'];
		$TWC_SETUP['EDITED_ON']  = date("Y-m-d H:i:s");
		db_perform('S_TWC_SETUP', $TWC_SETUP, 'insert');
		$PK_TWC_SETUP = $db->insert_ID();
	} else {
		$TWC_SETUP['EDITED_BY'] = $_SESSION['PK_USER'];
		$TWC_SETUP['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_TWC_SETUP', $TWC_SETUP, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_TWC_SETUP = $_GET['id'];
	}
	
	header("location:TWC_setup");
}
$res = $db->Execute("select * from S_TWC_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$EXCLUDED_STUDENT_STATUS_ARR 			 					= explode(",",$res->fields['EXCLUDED_STUDENT_STATUS']);
$EXCLUDED_PROGRAM_ARR 			 							= explode(",",$res->fields['EXCLUDED_PROGRAM']);
$STUDENT_STATUS_GRADUATES_ARR 			 					= explode(",",$res->fields['STUDENT_STATUS_GRADUATES']);
$STUDENT_STATUS_DROPS_ARR 			 						= explode(",",$res->fields['STUDENT_STATUS_DROPS']);
$STUDENT_STATUS_OTHER_WITHDRAWLS_ARR 			 			= explode(",",$res->fields['STUDENT_STATUS_OTHER_WITHDRAWLS']);
$DROP_REASON_MILITARY_ARR 			 						= explode(",",$res->fields['DROP_REASON_MILITARY']);
$DROP_REASON_INCARCERATED_ARR 			 					= explode(",",$res->fields['DROP_REASON_INCARCERATED']);
$DROP_REASON_DECEASED_ARR 									= explode(",",$res->fields['DROP_REASON_DECEASED']);
$PLACEMENT_STUDENT_STATUS_INCARCERATED_ARR 			 		= explode(",",$res->fields['PLACEMENT_STUDENT_STATUS_INCARCERATED']);
$PLACEMENT_STUDENT_STATUS_DECEASED_ARR 			 			= explode(",",$res->fields['PLACEMENT_STUDENT_STATUS_DECEASED']);
$PLACEMENT_STUDENT_STATUS_POSTSECONDARY_EDUCATION_ARR 		= explode(",",$res->fields['PLACEMENT_STUDENT_STATUS_POSTSECONDARY_EDUCATION']);
$PLACEMENT_STUDENT_STATUS_PLACED_ARR 						= explode(",",$res->fields['PLACEMENT_STUDENT_STATUS_PLACED']);
$PLACEMENT_STUDENT_STATUS_OTHER_ARR 			 			= explode(",",$res->fields['PLACEMENT_STUDENT_STATUS_OTHER']);
$PLACEMENT_STUDENT_STATUS_NOT_PLACED_ARR 			 		= explode(",",$res->fields['PLACEMENT_STUDENT_STATUS_NOT_PLACED']);
$PLACEMENT_STUDENT_STATUS_MILITARY_ARR 			 			= explode(",",$res->fields['PLACEMENT_STUDENT_STATUS_MILITARY']);
$STUDENT_STATUS_OTHER_COMPLETERS_ARR 			 			= explode(",",$res->fields['STUDENT_STATUS_OTHER_COMPLETERS']);
$STUDENT_STATUS_UNKNOWN_ARR 			 					= explode(",",$res->fields['STUDENT_STATUS_UNKNOWN']);
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
	<title><?=MNU_TWC_SETUP ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		.dropdown-menu>li>a { white-space: nowrap; }
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
                        <h4 class="text-themecolor">
							<?=MNU_TWC_SETUP ?>
						</h4>
                    </div>
                </div>
				
				<div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<div class="d-flex">
										<div class="col-6 col-sm-6 ">
										
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDED_STUDENT_STATUS?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="EXCLUDED_STUDENT_STATUS" name="EXCLUDED_STUDENT_STATUS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															foreach($EXCLUDED_STUDENT_STATUS_ARR as $EXCLUDED_STUDENT_STATUS){
																if($EXCLUDED_STUDENT_STATUS == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDED_PROGRAM?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="EXCLUDED_PROGRAM" name="EXCLUDED_PROGRAM[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,PROGRAM_TRANSCRIPT_CODE,DESCRIPTION, ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['CODE']." - ".$res_type->fields['PROGRAM_TRANSCRIPT_CODE']." - ".$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_CAMPUS_PROGRAM 	= $res_type->fields['PK_CAMPUS_PROGRAM']; 
															foreach($EXCLUDED_PROGRAM_ARR as $EXCLUDED_PROGRAM){
																if($EXCLUDED_PROGRAM == $PK_CAMPUS_PROGRAM) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_CAMPUS_PROGRAM?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=STUDENT_STATUS_GRADUATES?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="STUDENT_STATUS_GRADUATES" name="STUDENT_STATUS_GRADUATES[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															foreach($STUDENT_STATUS_GRADUATES_ARR as $STUDENT_STATUS_GRADUATES){
																if($STUDENT_STATUS_GRADUATES == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=STUDENT_STATUS_OTHER_COMPLETERS?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="STUDENT_STATUS_OTHER_COMPLETERS" name="STUDENT_STATUS_OTHER_COMPLETERS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															foreach($STUDENT_STATUS_OTHER_COMPLETERS_ARR as $STUDENT_STATUS_OTHER_COMPLETERS){
																if($STUDENT_STATUS_OTHER_COMPLETERS == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=STUDENT_STATUS_DROPS?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="STUDENT_STATUS_DROPS" name="STUDENT_STATUS_DROPS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															foreach($STUDENT_STATUS_DROPS_ARR as $STUDENT_STATUS_DROPS){
																if($STUDENT_STATUS_DROPS == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=STUDENT_STATUS_OTHER_WITHDRAWLS?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="STUDENT_STATUS_OTHER_WITHDRAWLS" name="STUDENT_STATUS_OTHER_WITHDRAWLS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															foreach($STUDENT_STATUS_OTHER_WITHDRAWLS_ARR as $STUDENT_STATUS_OTHER_WITHDRAWLS){
																if($STUDENT_STATUS_OTHER_WITHDRAWLS == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=STUDENT_STATUS_UNKNOWN?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="STUDENT_STATUS_UNKNOWN" name="STUDENT_STATUS_UNKNOWN[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															foreach($STUDENT_STATUS_UNKNOWN_ARR as $STUDENT_STATUS_UNKNOWN){
																if($STUDENT_STATUS_UNKNOWN == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=DROP_REASON_MILITARY ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="DROP_REASON_MILITARY" name="DROP_REASON_MILITARY[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION, ACTIVE from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC, DROP_REASON ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON']; 
															foreach($DROP_REASON_MILITARY_ARR as $DROP_REASON_MILITARY){
																if($DROP_REASON_MILITARY == $PK_DROP_REASON) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_DROP_REASON?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=DROP_REASON_INCARCERATED ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="DROP_REASON_INCARCERATED" name="DROP_REASON_INCARCERATED[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION, ACTIVE from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC, DROP_REASON ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON']; 
															foreach($DROP_REASON_INCARCERATED_ARR as $DROP_REASON_INCARCERATED){
																if($DROP_REASON_INCARCERATED == $PK_DROP_REASON) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_DROP_REASON?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=DROP_REASON_DECEASED ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="DROP_REASON_DECEASED" name="DROP_REASON_DECEASED[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION, ACTIVE from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC, DROP_REASON ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON']; 
															foreach($DROP_REASON_DECEASED_ARR as $DROP_REASON_DECEASED){
																if($DROP_REASON_DECEASED == $PK_DROP_REASON) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_DROP_REASON?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
										</div>
										<div class="col-6 col-sm-6 ">
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=PLACEMENT_STUDENT_STATUS_INCARCERATED?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PLACEMENT_STUDENT_STATUS_INCARCERATED" name="PLACEMENT_STUDENT_STATUS_INCARCERATED[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS, PLACEMENT_STATUS, ACTIVE from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['PLACEMENT_STATUS'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($PLACEMENT_STUDENT_STATUS_INCARCERATED_ARR as $PLACEMENT_STUDENT_STATUS_INCARCERATED){
																if($PLACEMENT_STUDENT_STATUS_INCARCERATED == $PK_PLACEMENT_STATUS) {
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
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=PLACEMENT_STUDENT_STATUS_DECEASED?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PLACEMENT_STUDENT_STATUS_DECEASED" name="PLACEMENT_STUDENT_STATUS_DECEASED[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS, PLACEMENT_STATUS, ACTIVE from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['PLACEMENT_STATUS'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($PLACEMENT_STUDENT_STATUS_DECEASED_ARR as $PLACEMENT_STUDENT_STATUS_DECEASED){
																if($PLACEMENT_STUDENT_STATUS_DECEASED == $PK_PLACEMENT_STATUS) {
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
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=PLACEMENT_STUDENT_STATUS_POSTSECONDARY_EDUCATION ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PLACEMENT_STUDENT_STATUS_POSTSECONDARY_EDUCATION" name="PLACEMENT_STUDENT_STATUS_POSTSECONDARY_EDUCATION[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS, PLACEMENT_STATUS, ACTIVE from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['PLACEMENT_STATUS'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($PLACEMENT_STUDENT_STATUS_POSTSECONDARY_EDUCATION_ARR as $PLACEMENT_STUDENT_STATUS_POSTSECONDARY_EDUCATION){
																if($PLACEMENT_STUDENT_STATUS_POSTSECONDARY_EDUCATION == $PK_PLACEMENT_STATUS) {
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
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=PLACEMENT_STUDENT_STATUS_PLACED ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PLACEMENT_STUDENT_STATUS_PLACED" name="PLACEMENT_STUDENT_STATUS_PLACED[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS, PLACEMENT_STATUS, ACTIVE from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['PLACEMENT_STATUS'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($PLACEMENT_STUDENT_STATUS_PLACED_ARR as $PLACEMENT_STUDENT_STATUS_PLACED){
																if($PLACEMENT_STUDENT_STATUS_PLACED == $PK_PLACEMENT_STATUS) {
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
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=PLACEMENT_STUDENT_STATUS_OTHER ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PLACEMENT_STUDENT_STATUS_OTHER" name="PLACEMENT_STUDENT_STATUS_OTHER[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS, PLACEMENT_STATUS, ACTIVE from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['PLACEMENT_STATUS'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($PLACEMENT_STUDENT_STATUS_OTHER_ARR as $PLACEMENT_STUDENT_STATUS_OTHER){
																if($PLACEMENT_STUDENT_STATUS_OTHER == $PK_PLACEMENT_STATUS) {
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
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=PLACEMENT_STUDENT_STATUS_NOT_PLACED ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PLACEMENT_STUDENT_STATUS_NOT_PLACED" name="PLACEMENT_STUDENT_STATUS_NOT_PLACED[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS, PLACEMENT_STATUS, ACTIVE from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['PLACEMENT_STATUS'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($PLACEMENT_STUDENT_STATUS_NOT_PLACED_ARR as $PLACEMENT_STUDENT_STATUS_NOT_PLACED){
																if($PLACEMENT_STUDENT_STATUS_NOT_PLACED == $PK_PLACEMENT_STATUS) {
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
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=PLACEMENT_STUDENT_STATUS_MILITARY ?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PLACEMENT_STUDENT_STATUS_MILITARY" name="PLACEMENT_STUDENT_STATUS_MILITARY[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS, PLACEMENT_STATUS, ACTIVE from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['PLACEMENT_STATUS'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($PLACEMENT_STUDENT_STATUS_MILITARY_ARR as $PLACEMENT_STUDENT_STATUS_MILITARY){
																if($PLACEMENT_STUDENT_STATUS_MILITARY == $PK_PLACEMENT_STATUS) {
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
									
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										<div class="col-3 col-sm-3">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											<button type="button" onclick="window.location.href='TWC_report'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
										</div>
										
										<div class="col-3 col-sm-3">
											<button type="button" onclick="window.location.href='TWC_report'"  class="btn waves-effect waves-light btn-info" ><?=GO_TO_REPORT?></button>
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
		$('#EXCLUDED_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=EXCLUDED_STUDENT_STATUS?> selected'
		});
		
		$('#EXCLUDED_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=EXCLUDED_PROGRAM?> selected'
		});
		
		$('#STUDENT_STATUS_GRADUATES').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS_GRADUATES?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=STUDENT_STATUS_GRADUATES?> selected'
		});
		
		$('#STUDENT_STATUS_DROPS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS_DROPS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=STUDENT_STATUS_DROPS?> selected'
		});
		
		$('#STUDENT_STATUS_OTHER_WITHDRAWLS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS_OTHER_WITHDRAWLS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=STUDENT_STATUS_OTHER_WITHDRAWLS?> selected'
		});
		
		$('#DROP_REASON_MILITARY').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=DROP_REASON_MILITARY?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=DROP_REASON_MILITARY?> selected'
		});
		
		$('#DROP_REASON_INCARCERATED').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=DROP_REASON_INCARCERATED?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=DROP_REASON_INCARCERATED?> selected'
		});
		
		$('#DROP_REASON_DECEASED').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=DROP_REASON_DECEASED?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=DROP_REASON_DECEASED?> selected'
		});
		
		$('#PLACEMENT_STUDENT_STATUS_INCARCERATED').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STUDENT_STATUS_INCARCERATED?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=PLACEMENT_STUDENT_STATUS_INCARCERATED?> selected'
		});
		
		$('#PLACEMENT_STUDENT_STATUS_DECEASED').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STUDENT_STATUS_DECEASED?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=PLACEMENT_STUDENT_STATUS_DECEASED?> selected'
		});
		
		$('#PLACEMENT_STUDENT_STATUS_POSTSECONDARY_EDUCATION').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STUDENT_STATUS_POSTSECONDARY_EDUCATION?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=PLACEMENT_STUDENT_STATUS_POSTSECONDARY_EDUCATION?> selected'
		});
		
		$('#PLACEMENT_STUDENT_STATUS_PLACED').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STUDENT_STATUS_PLACED?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=PLACEMENT_STUDENT_STATUS_PLACED?> selected'
		});
		
		$('#PLACEMENT_STUDENT_STATUS_OTHER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STUDENT_STATUS_OTHER?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=PLACEMENT_STUDENT_STATUS_OTHER?> selected'
		});
		
		$('#PLACEMENT_STUDENT_STATUS_NOT_PLACED').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STUDENT_STATUS_NOT_PLACED?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=PLACEMENT_STUDENT_STATUS_NOT_PLACED?> selected'
		});
		
		$('#PLACEMENT_STUDENT_STATUS_MILITARY').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACEMENT_STUDENT_STATUS_MILITARY?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=PLACEMENT_STUDENT_STATUS_MILITARY?> selected'
		});
		
		$('#STUDENT_STATUS_OTHER_COMPLETERS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS_OTHER_COMPLETERS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=STUDENT_STATUS_OTHER_COMPLETERS?> selected'
		});
		
		$('#STUDENT_STATUS_UNKNOWN').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS_UNKNOWN?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=STUDENT_STATUS_UNKNOWN?> selected'
		});
	});
	</script>
	
</body>

</html>