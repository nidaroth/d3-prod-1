<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/abhes.php");
require_once("get_department_from_t.php");

require_once("check_access.php");

if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}
$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	$ABHES_ARRAY['EXCLUDED_PROGRAM'] 				= implode(",",$_POST['EXCLUDED_PROGRAM']);
	$ABHES_ARRAY['EXCLUDED_STUDENT_STATUS'] 		= implode(",",$_POST['EXCLUDED_STUDENT_STATUS']);
	$ABHES_ARRAY['GRADUATED_STUDENT_STATUS'] 		= implode(",",$_POST['GRADUATED_STUDENT_STATUS']);
	$ABHES_ARRAY['WITHDRWAL_DROP_STUDENT_STATUS'] 	= implode(",",$_POST['WITHDRWAL_DROP_STUDENT_STATUS']);
	$ABHES_ARRAY['LICENSURE_TYPE'] 				    = implode(",",$_POST['LICENSURE_TYPE']);
	$ABHES_ARRAY['TOOK_EXAM'] 					    = implode(",",$_POST['TOOK_EXAM']);
	$ABHES_ARRAY['FAILED_EXAM'] 					= implode(",",$_POST['FAILED_EXAM']);
	$ABHES_ARRAY['PASSED_EXAM'] 					= implode(",",$_POST['PASSED_EXAM']);
    $ABHES_ARRAY['RESULTS_PENDING'] 				= implode(",",$_POST['RESULTS_PENDING']);
	$ABHES_ARRAY['LICENSURE_EXAM'] 				    = implode(",",$_POST['LICENSURE_EXAM']);

    $res = $db->Execute("select * from ABHES_REPORT_SETUP  WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	if($res->RecordCount() == 0){
		$ABHES_ARRAY['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$ABHES_ARRAY['CREATED_BY'] = $_SESSION['PK_USER'];
		$ABHES_ARRAY['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('ABHES_REPORT_SETUP ', $ABHES_ARRAY, 'insert');
		$PK_ABHES_ARRAY = $db->insert_ID();
	} else {
		$ABHES_ARRAY['EDITED_BY'] = $_SESSION['PK_USER'];
		$ABHES_ARRAY['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('ABHES_REPORT_SETUP ', $ABHES_ARRAY, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_ABHES_ARRAY = $_GET['id'];
	}
	header("location:abhes_report_setup");
}
$res = $db->Execute("select * from ABHES_REPORT_SETUP  WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$EXCLUDED_PROGRAM_ARR 			= explode(",",$res->fields['EXCLUDED_PROGRAM']);
$EXCLUDED_STUDENT_STATUS_ARR 	= explode(",",$res->fields['EXCLUDED_STUDENT_STATUS']);
$GRADUATED_STUDENT_STATUS_ARR 	   = explode(",",$res->fields['GRADUATED_STUDENT_STATUS']);
$WITHDRWAL_DROP_STUDENT_STATUS_ARR = explode(",",$res->fields['WITHDRWAL_DROP_STUDENT_STATUS']);
$LICENSURE_TYPE_ARR 			   = explode(",",$res->fields['LICENSURE_TYPE']);
$TOOK_EXAM_ARR 					= explode(",",$res->fields['TOOK_EXAM']);
$FAILED_EXAM_ARR 				= explode(",",$res->fields['FAILED_EXAM']);
$PASSED_EXAM_ARR 				= explode(",",$res->fields['PASSED_EXAM']);
$RESULTS_PENDING_ARR 			= explode(",",$res->fields['RESULTS_PENDING']);
$LICENSURE_EXAM_ARR 			= explode(",",$res->fields['LICENSURE_EXAM']);
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
	<title><?=ABHES_DOC_SETUP?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><?=ABHES_DOC_SETUP?></h4>
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
                                        <button type="button" onclick="window.location.href='abhes_bureau_health_education_school_report'" class="btn waves-effect waves-light btn-info">Go To Report</button>
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
											
											<div class="row d-flex">
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=GRADUATED_STUDENT_STATUS?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-11 form-group">
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
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=WITHDRWAL_DROP_STUDENT_STATUS?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-11 form-group">
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

										</div>
										
										<div class="col-md-6 ">
										
											<div class="row d-flex">
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=STUDENT_EVENT_TYPE?></label>
												</div>
											</div>
											<br />
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=LICENSURE_TYPE?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="LICENSURE_TYPE" name="LICENSURE_TYPE[]" multiple class="form-control" >
														<? $PK_DEPARTMENT = get_department_from_t(6);	
														$res_type = $db->Execute("select PK_NOTE_TYPE,NOTE_TYPE,DESCRIPTION from M_NOTE_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) order by NOTE_TYPE ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_NOTE_TYPE 	= $res_type->fields['PK_NOTE_TYPE']; 
															foreach($LICENSURE_TYPE_ARR as $LICENSURE_TYPE){
																if($LICENSURE_TYPE == $PK_NOTE_TYPE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_NOTE_TYPE?>" <?=$selected?> ><?=$res_type->fields['NOTE_TYPE'].' - '.$res_type->fields['DESCRIPTION'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=STUDENT_EVENT_STATUS?></label>
												</div>
											</div>
											<br />
										
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=TOOK_EXAM?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="TOOK_EXAM" name="TOOK_EXAM[]" multiple class="form-control" >
														<? $PK_DEPARTMENT = get_department_from_t(6);	
														$res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS from M_NOTE_STATUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 3 AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) order by NOTE_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_NOTE_STATUS 	= $res_type->fields['PK_NOTE_STATUS']; 
															foreach($TOOK_EXAM_ARR as $TOOK_EXAM){
																if($TOOK_EXAM == $PK_NOTE_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_NOTE_STATUS?>" <?=$selected?> ><?=$res_type->fields['NOTE_STATUS'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=FAILED_EXAM?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="FAILED_EXAM" name="FAILED_EXAM[]" multiple class="form-control" >
														<? $PK_DEPARTMENT = get_department_from_t(6);	
														$res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS from M_NOTE_STATUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 3 AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) order by NOTE_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_NOTE_STATUS 	= $res_type->fields['PK_NOTE_STATUS']; 
															foreach($FAILED_EXAM_ARR as $FAILED_EXAM){
																if($FAILED_EXAM == $PK_NOTE_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_NOTE_STATUS?>" <?=$selected?> ><?=$res_type->fields['NOTE_STATUS'] ?></option>
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
														$res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS from M_NOTE_STATUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 3 AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) order by NOTE_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_NOTE_STATUS 	= $res_type->fields['PK_NOTE_STATUS']; 
															foreach($PASSED_EXAM_ARR as $PASSED_EXAM){
																if($PASSED_EXAM == $PK_NOTE_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_NOTE_STATUS?>" <?=$selected?> ><?=$res_type->fields['NOTE_STATUS'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

                                            <div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=RESULTS_PENDING?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="RESULTS_PENDING" name="RESULTS_PENDING[]" multiple class="form-control" >
														<? $PK_DEPARTMENT = get_department_from_t(6);	
														$res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS from M_NOTE_STATUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 3 AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) order by NOTE_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_NOTE_STATUS 	= $res_type->fields['PK_NOTE_STATUS']; 
															foreach($RESULTS_PENDING_ARR as $RESULTS_PENDING){
																if($RESULTS_PENDING == $PK_NOTE_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_NOTE_STATUS?>" <?=$selected?> ><?=$res_type->fields['NOTE_STATUS'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=STUDENT_EVENT_OTHER?></label>
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
														$res_type = $db->Execute("select PK_EVENT_OTHER,EVENT_OTHER,DESCRIPTION from M_EVENT_OTHER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) order by EVENT_OTHER ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_EVENT_OTHER 	= $res_type->fields['PK_EVENT_OTHER']; 
															foreach($LICENSURE_EXAM_ARR as $LICENSURE_EXAM){
																if($LICENSURE_EXAM == $PK_EVENT_OTHER) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_EVENT_OTHER?>" <?=$selected?> ><?=$res_type->fields['EVENT_OTHER'].' - '.$res_type->fields['DESCRIPTION'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
										</div>
									</div>
									
									<div class="row">
										<div class="col-3 col-sm-5">
										</div>
										<div class="col-6 col-sm-6">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											<button type="button" onclick="window.location.href='abhes_bureau_health_education_school_report'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
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
		
		$('#LICENSURE_TYPE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_EVENT_TYPE?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=STUDENT_EVENT_TYPE?> selected'
		});
		
		$('#LICENSURE_EXAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_EVENT_TYPE?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=STUDENT_EVENT_TYPE?> selected'
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
		
		$('#GRADUATED_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=GRADUATED_STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=GRADUATED_STUDENT_STATUS?> selected'
		});

		$('#WITHDRWAL_DROP_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=WITHDRWAL_DROP_STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=WITHDRWAL_DROP_STUDENT_STATUS?> selected'
		});		
		
		$('#TOOK_EXAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_EVENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=STUDENT_EVENT_STATUS?> selected'
		});
		
		$('#FAILED_EXAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_EVENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=STUDENT_EVENT_STATUS?> selected'
		});
		
		$('#PASSED_EXAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_EVENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=STUDENT_EVENT_STATUS?> selected'
		});

        $('#RESULTS_PENDING').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_EVENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=STUDENT_EVENT_STATUS?> selected'
		});

        
	});
	</script>
</body>

</html>