<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/accsc.php");
require_once("get_department_from_t.php");

require_once("check_access.php");

if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}
$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("select * from S_ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES['EXCLUDED_PROGRAM'] 				= implode(",",$_POST['EXCLUDED_PROGRAM']);
	$ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES['GRADUATED_STUDENT_STATUS'] 		= implode(",",$_POST['GRADUATED_STUDENT_STATUS']);
	$ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES['LICENSURE_TYPE'] 				= implode(",",$_POST['LICENSURE_TYPE']);
	$ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES['TOOK_EXAM'] 					= implode(",",$_POST['TOOK_EXAM']);
	$ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES['FAILED_EXAM'] 					= implode(",",$_POST['FAILED_EXAM']);
	$ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES['PASSED_EXAM'] 					= implode(",",$_POST['PASSED_EXAM']);
	$ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES['LICENSURE_EXAM'] 				= implode(",",$_POST['LICENSURE_EXAM']);
	
	if($res->RecordCount() == 0){
		$ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES['CREATED_BY'] = $_SESSION['PK_USER'];
		$ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES', $ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES, 'insert');
		$PK_ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES = $db->insert_ID();
	} else {
		$ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES['EDITED_BY'] = $_SESSION['PK_USER'];
		$ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES', $ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES = $_GET['id'];
	}
	header("location:accsc_licensure_certification_exam_pass_rates_setup");
}
$res = $db->Execute("select * from S_ACCSC_LICENSURE_CERTIFICATION_EXAM_PASS_RATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$EXCLUDED_PROGRAM_ARR 			= explode(",",$res->fields['EXCLUDED_PROGRAM']);
$GRADUATED_STUDENT_STATUS_ARR 	= explode(",",$res->fields['GRADUATED_STUDENT_STATUS']);
$LICENSURE_TYPE_ARR 			= explode(",",$res->fields['LICENSURE_TYPE']);
$TOOK_EXAM_ARR 					= explode(",",$res->fields['TOOK_EXAM']);
$FAILED_EXAM_ARR 				= explode(",",$res->fields['FAILED_EXAM']);
$PASSED_EXAM_ARR 				= explode(",",$res->fields['PASSED_EXAM']);
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
	<title><?=MNU_ACCSC_LIC_CER_EXAM_PASS_RATE_SETUP?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><?=MNU_ACCSC_LIC_CER_EXAM_PASS_RATE_SETUP?></h4>
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
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDED_PROGRAM?></label>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-12 col-sm-12 form-group">
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
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=GRADUATED_STUDENT_STATUS?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-12 form-group">
													<select id="GRADUATED_STUDENT_STATUS" name="GRADUATED_STUDENT_STATUS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND (ADMISSIONS = 0) order by STUDENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															foreach($GRADUATED_STUDENT_STATUS_ARR as $GRADUATED_STUDENT_STATUS){
																if($GRADUATED_STUDENT_STATUS == $PK_STUDENT_STATUS) {
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
		
		$('#GRADUATED_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=STUDENT_STATUS?> selected'
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
	});
	</script>
</body>

</html>