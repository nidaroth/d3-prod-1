<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/population_report_setup.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT COE,ECM,_1098T,_90_10,IPEDS,POPULATION_REPORT FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['POPULATION_REPORT'] == 0 || check_access('MANAGEMENT_POPULATION_REPORT') == 0){
	header("location:../index");
	exit;
}

$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("select * from S_POPULATION_REPORT_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$POPULATION_REPORT_SETUP['GRADUATES'] 					= implode(",",$_POST['GRADUATES']);
	$POPULATION_REPORT_SETUP['OTHER_COMPLETERS'] 			= implode(",",$_POST['OTHER_COMPLETERS']);
	$POPULATION_REPORT_SETUP['DROPS'] 						= implode(",",$_POST['DROPS']);
	$POPULATION_REPORT_SETUP['OTHER_WITHDRAWS'] 			= implode(",",$_POST['OTHER_WITHDRAWS']);
	$POPULATION_REPORT_SETUP['EXCLUDED_STUDENT_STATUS'] 	= implode(",",$_POST['EXCLUDED_STUDENT_STATUS']);
	$POPULATION_REPORT_SETUP['EXCLUDED_PROGRAM'] 			= implode(",",$_POST['EXCLUDED_PROGRAM']);
	
	if($res->RecordCount() == 0){
		$POPULATION_REPORT_SETUP['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$POPULATION_REPORT_SETUP['CREATED_BY'] = $_SESSION['PK_USER'];
		$POPULATION_REPORT_SETUP['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_POPULATION_REPORT_SETUP', $POPULATION_REPORT_SETUP, 'insert');
		$PK_POPULATION_REPORT_SETUP = $db->insert_ID();
	} else {
		$POPULATION_REPORT_SETUP['EDITED_BY'] = $_SESSION['PK_USER'];
		$POPULATION_REPORT_SETUP['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_POPULATION_REPORT_SETUP', $POPULATION_REPORT_SETUP, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_POPULATION_REPORT_SETUP = $_GET['id'];
	}
	header("location:population_report_setup");
}
$res = $db->Execute("select * from S_POPULATION_REPORT_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$GRADUATES_ARR 					= explode(",",$res->fields['GRADUATES']);
$OTHER_COMPLETERS_ARR 			= explode(",",$res->fields['OTHER_COMPLETERS']);
$DROPS_ARR 						= explode(",",$res->fields['DROPS']);
$OTHER_WITHDRAWS_ARR 			= explode(",",$res->fields['OTHER_WITHDRAWS']);
$EXCLUDED_STUDENT_STATUS_ARR 	= explode(",",$res->fields['EXCLUDED_STUDENT_STATUS']);
$EXCLUDED_PROGRAM_ARR 			= explode(",",$res->fields['EXCLUDED_PROGRAM']);
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
	<title><?=POPULATION_REPORT_SETUP_TITLE?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><?=POPULATION_REPORT_SETUP_TITLE?></h4>
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
											<label ><?=GRADUATES?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="GRADUATES" name="GRADUATES[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
												while (!$res_type->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
													foreach($GRADUATES_ARR as $GRADUATES){
														if($GRADUATES == $PK_STUDENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 
													
													$option_label = $res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-12 col-sm-6 form-group text-right">
											<button type="button" onclick="window.location.href='population_report'" class="btn waves-effect waves-light btn-info"><?=GO_TO_REPORT?></button>
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-12 focused">
											<span class="bar"></span> 
											<label ><?=OTHER_COMPLETERS?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="OTHER_COMPLETERS" name="OTHER_COMPLETERS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
												while (!$res_type->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
													foreach($OTHER_COMPLETERS_ARR as $OTHER_COMPLETERS){
														if($OTHER_COMPLETERS == $PK_STUDENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 
													
													$option_label = $res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-12 focused">
											<span class="bar"></span> 
											<label ><?=DROPS?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="DROPS" name="DROPS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
												while (!$res_type->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
													foreach($DROPS_ARR as $DROPS){
														if($DROPS == $PK_STUDENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 
													
													$option_label = $res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-12 focused">
											<span class="bar"></span> 
											<label ><?=OTHER_WITHDRAWS?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="OTHER_WITHDRAWS" name="OTHER_WITHDRAWS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
												while (!$res_type->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
													foreach($OTHER_WITHDRAWS_ARR as $OTHER_WITHDRAWS){
														if($OTHER_WITHDRAWS == $PK_STUDENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 
													
													$option_label = $res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									</div>
									
									<div class="d-flex">
										<div class="col-12 col-sm-12 focused">
											<span class="bar"></span> 
											<label ><?=EXCLUDED_STUDENT_STATUS?></label>
										</div>
									</div>
									<div class="d-flex">
										<div class="col-12 col-sm-6 form-group">
											<select id="EXCLUDED_STUDENT_STATUS" name="EXCLUDED_STUDENT_STATUS[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
												while (!$res_type->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
													foreach($EXCLUDED_STUDENT_STATUS_ARR as $EXCLUDED_STUDENT_STATUS){
														if($EXCLUDED_STUDENT_STATUS == $PK_STUDENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 
													
													$option_label = $res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
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
										<div class="col-12 col-sm-6 form-group">
											<select id="EXCLUDED_PROGRAM" name="EXCLUDED_PROGRAM[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION, ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");
												while (!$res_type->EOF) { 
													$selected 			= "";
													$PK_CAMPUS_PROGRAM 	= $res_type->fields['PK_CAMPUS_PROGRAM']; 
													foreach($EXCLUDED_PROGRAM_ARR as $EXCLUDED_PROGRAM){
														if($EXCLUDED_PROGRAM == $PK_CAMPUS_PROGRAM) {
															$selected = 'selected';
															break;
														}
													} 
													
													$option_label = $res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$PK_CAMPUS_PROGRAM?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
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
		$('#GRADUATES').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=STUDENT_STATUS?> selected'
		});
		
		$('#OTHER_COMPLETERS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=OTHER_COMPLETERS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=OTHER_COMPLETERS?> selected'
		});
		
		$('#DROPS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=DROPS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=DROPS?> selected'
		});
		
		$('#OTHER_WITHDRAWS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=OTHER_WITHDRAWS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=OTHER_WITHDRAWS?> selected'
		});
		
		$('#EXCLUDED_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDED_STUDENT_STATUS?> selected'
		});
		
		$('#EXCLUDED_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDED_PROGRAM?> selected'
		});
	});
	</script>
</body>

</html>