<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/cpl_setup.php");
require_once("check_access.php");
require_once("get_department_from_t.php");

if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}
$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("select * from S_CPL_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$CPL_SETUP['NON_GRADUATE_COMPLETER'] 		= implode(",",$_POST['NON_GRADUATE_COMPLETER']);
	$CPL_SETUP['WITHDRAWALS'] 					= implode(",",$_POST['WITHDRAWALS']);
	$CPL_SETUP['UNAVAILABLE_FOR_CREDENTIALS'] 	= implode(",",$_POST['UNAVAILABLE_FOR_CREDENTIALS']);
	$CPL_SETUP['EXCLUDE_PROGRAM'] 				= implode(",",$_POST['EXCLUDE_PROGRAM']);
	$CPL_SETUP['EXCLUDE_STUDENT_STATUS'] 		= implode(",",$_POST['EXCLUDE_STUDENT_STATUS']);
	$CPL_SETUP['LICENSURE_EXAM'] 				= $_POST['LICENSURE_EXAM'];
	$CPL_SETUP['WAITING_LICENSURE_EXAM'] 		= $_POST['WAITING_LICENSURE_EXAM'];
	$CPL_SETUP['TOOK_LICENSURE_EXAM'] 			= $_POST['TOOK_LICENSURE_EXAM'];
	$CPL_SETUP['PASSED_LICENSURE_EXAM'] 		= $_POST['PASSED_LICENSURE_EXAM'];
	$CPL_SETUP['REFUSED_EMPLOYEMENT'] 			= $_POST['REFUSED_EMPLOYEMENT'];
	$CPL_SETUP['FAILED_LICENSURE_EXAM'] 		= $_POST['FAILED_LICENSURE_EXAM'];
	
	if($res->RecordCount() == 0){
		$CPL_SETUP['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$CPL_SETUP['CREATED_BY'] = $_SESSION['PK_USER'];
		$CPL_SETUP['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_CPL_SETUP', $CPL_SETUP, 'insert');
		$PK_CPL_SETUP = $db->insert_ID();
	} else {
		$CPL_SETUP['EDITED_BY'] = $_SESSION['PK_USER'];
		$CPL_SETUP['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_CPL_SETUP', $CPL_SETUP, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_CPL_SETUP = $_GET['id'];
	}
	header("location:cpl_setup");
}
$res = $db->Execute("select * from S_CPL_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$NON_GRADUATE_COMPLETER_ARR 		= explode(",",$res->fields['NON_GRADUATE_COMPLETER']);
$WITHDRAWALS_ARR 					= explode(",",$res->fields['WITHDRAWALS']);
$LICENSURE_EXAM 					= $res->fields['LICENSURE_EXAM'];
$WAITING_LICENSURE_EXAM 			= $res->fields['WAITING_LICENSURE_EXAM'];
$TOOK_LICENSURE_EXAM 				= $res->fields['TOOK_LICENSURE_EXAM'];
$PASSED_LICENSURE_EXAM 				= $res->fields['PASSED_LICENSURE_EXAM'];
$REFUSED_EMPLOYEMENT 				= $res->fields['REFUSED_EMPLOYEMENT'];
$FAILED_LICENSURE_EXAM				= $res->fields['FAILED_LICENSURE_EXAM'];
$UNAVAILABLE_FOR_CREDENTIALS_ARR 	= explode(",",$res->fields['UNAVAILABLE_FOR_CREDENTIALS']);
$EXCLUDE_PROGRAM_ARR 				= explode(",",$res->fields['EXCLUDE_PROGRAM']);
$EXCLUDE_STUDENT_STATUS_ARR 		= explode(",",$res->fields['EXCLUDE_STUDENT_STATUS']);
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
	<title><?=CPL_SETUP_TITLE?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		.option_red > a > label{color:red !important} /* Ticket #1784 */
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
                        <h4 class="text-themecolor"><?=CPL_SETUP_TITLE?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<div class="d-flex">
										<div class="col-12 col-sm-12 focused">
											<span class="bar"></span> 
											<label ><?=EXCLUDE_PROGRAM?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="EXCLUDE_PROGRAM" name="EXCLUDE_PROGRAM[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE, DESCRIPTION, ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");
												while (!$res_type->EOF) { 
													$option_label 		= $res_type->fields['CODE']." - ".$res_type->fields['DESCRIPTION'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)";
														
													$selected 			= "";
													$PK_CAMPUS_PROGRAM 	= $res_type->fields['PK_CAMPUS_PROGRAM']; 
													foreach($EXCLUDE_PROGRAM_ARR as $EXCLUDE_PROGRAM){
														if($EXCLUDE_PROGRAM == $PK_CAMPUS_PROGRAM) {
															$selected = 'selected';
															break;
														}
													} ?>
													<option value="<?=$PK_CAMPUS_PROGRAM?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-12 focused">
											<span class="bar"></span> 
											<label ><?=EXCLUDE_STUDENT_STATUS?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="EXCLUDE_STUDENT_STATUS" name="EXCLUDE_STUDENT_STATUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION,ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (ADMISSIONS = 0) order by ACTIVE DESC, STUDENT_STATUS ASC");
												while (!$res_type->EOF) { 
													$option_label 		= $res_type->fields['STUDENT_STATUS']." - ".$res_type->fields['DESCRIPTION'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)";
														
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
													foreach($EXCLUDE_STUDENT_STATUS_ARR as $EXCLUDE_STUDENT_STATUS){
														if($EXCLUDE_STUDENT_STATUS == $PK_STUDENT_STATUS) {
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
										<div class="col-12 col-sm-6 form-group">
											<hr style="border-color:#000" />
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-12 focused">
											<span class="bar"></span> 
											<label ><?=NON_GRADUATE_COMPLETER?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="NON_GRADUATE_COMPLETER" name="NON_GRADUATE_COMPLETER[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION,ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  (ADMISSIONS = 0) order by ACTIVE DESC, STUDENT_STATUS ASC");
												while (!$res_type->EOF) { 
													$option_label 		= $res_type->fields['STUDENT_STATUS']." - ".$res_type->fields['DESCRIPTION'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)";
														
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
													foreach($NON_GRADUATE_COMPLETER_ARR as $NON_GRADUATE_COMPLETER){
														if($NON_GRADUATE_COMPLETER == $PK_STUDENT_STATUS) {
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
											<label ><?=WITHDRAWALS?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="WITHDRAWALS" name="WITHDRAWALS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  (ADMISSIONS = 0) order by ACTIVE DESC, STUDENT_STATUS ASC");
												while (!$res_type->EOF) { 
													$option_label 		= $res_type->fields['STUDENT_STATUS']." - ".$res_type->fields['DESCRIPTION'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)";
														
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
													foreach($WITHDRAWALS_ARR as $WITHDRAWALS){
														if($WITHDRAWALS == $PK_STUDENT_STATUS) {
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
										<div class="col-12 col-sm-6 form-group">
											<i><?=WITHDRAWAL_MESSAGE?></i>
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<hr style="border-color:#000" />
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-12 focused">
											<span class="bar"></span> 
											<label ><?=UNAVAILABLE_FOR_CREDENTIALS?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="UNAVAILABLE_FOR_CREDENTIALS" name="UNAVAILABLE_FOR_CREDENTIALS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS,PLACEMENT_STATUS, ACTIVE from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
												while (!$res_type->EOF) { 
													$option_label 		= $res_type->fields['PLACEMENT_STATUS'] ;
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)";
														
													$selected 			= "";
													$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
													foreach($UNAVAILABLE_FOR_CREDENTIALS_ARR as $UNAVAILABLE_FOR_CREDENTIALS){
														if($UNAVAILABLE_FOR_CREDENTIALS == $PK_PLACEMENT_STATUS) {
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
										<div class="col-12 col-sm-6 form-group">
											<select id="REFUSED_EMPLOYEMENT" name="REFUSED_EMPLOYEMENT" class="form-control">
												<option selected></option>
												<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS,PLACEMENT_STATUS, ACTIVE from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
												while (!$res_type->EOF) { 
													$option_label 		= $res_type->fields['PLACEMENT_STATUS'] ;
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
														
													<option value="<?=$res_type->fields['PK_PLACEMENT_STATUS']?>" <? if($res_type->fields['PK_PLACEMENT_STATUS'] == $REFUSED_EMPLOYEMENT) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
											<span class="bar"></span> 
											<label for="REFUSED_EMPLOYEMENT"><?=REFUSED_EMPLOYEMENT?></label>
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<hr style="border-color:#000" />
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="LICENSURE_EXAM" name="LICENSURE_EXAM" class="form-control">
												<option selected></option>
												<? $PK_DEPARTMENT = get_department_from_t(6);	
												$res_type = $db->Execute("select PK_NOTE_TYPE,NOTE_TYPE,DESCRIPTION, ACTIVE from M_NOTE_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = '-1' ) order by ACTIVE DESC, NOTE_TYPE ASC");
												while (!$res_type->EOF) { 
													$option_label 		= $res_type->fields['NOTE_TYPE'].' - '.$res_type->fields['DESCRIPTION'] ;
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_NOTE_TYPE']?>" <? if($res_type->fields['PK_NOTE_TYPE'] == $LICENSURE_EXAM) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
											<span class="bar"></span> 
											<label for="LICENSURE_EXAM"><?=LICENSURE_EXAM?></label>
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="WAITING_LICENSURE_EXAM" name="WAITING_LICENSURE_EXAM" class="form-control">
												<option selected></option>
												<? $PK_DEPARTMENT = get_department_from_t(6);	
												$res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS, ACTIVE from M_NOTE_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 3 AND (PK_DEPARTMENT = '$PK_DEPARTMENT') order by ACTIVE DESC, NOTE_STATUS ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['NOTE_STATUS'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_NOTE_STATUS']?>" <? if($res_type->fields['PK_NOTE_STATUS'] == $WAITING_LICENSURE_EXAM) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
											<span class="bar"></span> 
											<label for="WAITING_LICENSURE_EXAM"><?=WAITING_LICENSURE_EXAM?></label>
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="TOOK_LICENSURE_EXAM" name="TOOK_LICENSURE_EXAM" class="form-control">
												<option selected></option>
												<? $PK_DEPARTMENT = get_department_from_t(6);	
												$res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS, ACTIVE from M_NOTE_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 3 AND (PK_DEPARTMENT = '$PK_DEPARTMENT') order by ACTIVE DESC, NOTE_STATUS ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['NOTE_STATUS'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_NOTE_STATUS']?>" <? if($res_type->fields['PK_NOTE_STATUS'] == $TOOK_LICENSURE_EXAM) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
											<span class="bar"></span> 
											<label for="TOOK_LICENSURE_EXAM"><?=TOOK_LICENSURE_EXAM?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="PASSED_LICENSURE_EXAM" name="PASSED_LICENSURE_EXAM" class="form-control">
												<option selected></option>
												<? $PK_DEPARTMENT = get_department_from_t(6);	
												$res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS, ACTIVE from M_NOTE_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 3 AND (PK_DEPARTMENT = '$PK_DEPARTMENT') order by ACTIVE DESC, NOTE_STATUS ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['NOTE_STATUS'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_NOTE_STATUS']?>" <? if($res_type->fields['PK_NOTE_STATUS'] == $PASSED_LICENSURE_EXAM) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
											<span class="bar"></span> 
											<label for="PASSED_LICENSURE_EXAM"><?=PASSED_LICENSURE_EXAM?></label>
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="FAILED_LICENSURE_EXAM" name="FAILED_LICENSURE_EXAM" class="form-control">
												<option selected></option>
												<? $PK_DEPARTMENT = get_department_from_t(6);	
												$res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS, ACTIVE from M_NOTE_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 3 AND (PK_DEPARTMENT = '$PK_DEPARTMENT') order by ACTIVE DESC, NOTE_STATUS ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['NOTE_STATUS'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_NOTE_STATUS']?>" <? if($res_type->fields['PK_NOTE_STATUS'] == $FAILED_LICENSURE_EXAM) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
											<span class="bar"></span> 
											<label for="FAILED_LICENSURE_EXAM"><?=FAILED_LICENSURE_EXAM?></label>
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
        <div id="confirm-modal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><?=DELETE_CONFIRMATION?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <p><?=IMAGE_DELETE?></p>
                        </form>
                    </div>
                    <div class="modal-footer">
						<button type="button" onclick="conf_delete()" class="btn btn-danger waves-effect waves-light"><?=YES?></button>
                        <button type="button" class="btn btn-default waves-effect" data-dismiss="modal"><?=NO?></button>
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
		function conf_delete(){
			jQuery(document).ready(function($) {
				window.location.href = 'profile?act=delImg';
			});	
		}
	</script>
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#NON_GRADUATE_COMPLETER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=STUDENT_STATUS?> selected'
		});
		
		$('#WITHDRAWALS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=WITHDRAWALS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=WITHDRAWALS?> selected'
		});
		
		$('#UNAVAILABLE_FOR_CREDENTIALS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=UNAVAILABLE_FOR_CREDENTIALS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=UNAVAILABLE_FOR_CREDENTIALS?> selected'
		});
		
		$('#EXCLUDE_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDE_PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDE_PROGRAM?> selected'
		});
		
		$('#EXCLUDE_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDE_STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDE_STUDENT_STATUS?> selected'
		});
	});
	</script>
</body>

</html>