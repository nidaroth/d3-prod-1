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
	
	$res = $db->Execute("select * from S_ACCSC_EMPLOYMENT_VERIFICATION_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['EXCLUDED_PROGRAM'] 				= implode(",",$_POST['EXCLUDED_PROGRAM']);
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['EXCLUDED_STUDENT_STATUS'] 		= implode(",",$_POST['EXCLUDED_STUDENT_STATUS']);
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['INCLUDED_PLACEMENT_STATUS'] 		= implode(",",$_POST['INCLUDED_PLACEMENT_STATUS']);
	$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['DISPLAY_OPTIONS'] 				= $_POST['DISPLAY_OPTIONS'];
	
	if($res->RecordCount() == 0){
		$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['CREATED_BY'] = $_SESSION['PK_USER'];
		$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_ACCSC_EMPLOYMENT_VERIFICATION_SOURCE', $ACCSC_EMPLOYMENT_VERIFICATION_SOURCE, 'insert');
		$PK_ACCSC_EMPLOYMENT_VERIFICATION_SOURCE = $db->insert_ID();
	} else {
		$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['EDITED_BY'] = $_SESSION['PK_USER'];
		$ACCSC_EMPLOYMENT_VERIFICATION_SOURCE['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_ACCSC_EMPLOYMENT_VERIFICATION_SOURCE', $ACCSC_EMPLOYMENT_VERIFICATION_SOURCE, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_ACCSC_EMPLOYMENT_VERIFICATION_SOURCE = $_GET['id'];
	}
	header("location:accsc_employment_verification_source_report_setup");
}
$res = $db->Execute("select * from S_ACCSC_EMPLOYMENT_VERIFICATION_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$DISPLAY_OPTIONS					= $res->fields['DISPLAY_OPTIONS'];
$INCLUDED_PLACEMENT_STATUS_ARR 		= explode(",",$res->fields['INCLUDED_PLACEMENT_STATUS']);
$EXCLUDED_PROGRAM_ARR 				= explode(",",$res->fields['EXCLUDED_PROGRAM']);
$EXCLUDED_STUDENT_STATUS_ARR 		= explode(",",$res->fields['EXCLUDED_STUDENT_STATUS']);

if($res->RecordCount() == 0)
	$DISPLAY_OPTIONS = 2;
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
	<title><?=MNU_ACCSC_EMP_VER_SOURCE_SETUP?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><?=MNU_ACCSC_EMP_VER_SOURCE_SETUP?></h4>
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
										</div>
										
										<div class="col-md-6 ">
											<div class="row d-flex">
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=INCLUSIONS?></label>
												</div>
											</div>
											<br /><br />
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=INCLUDED_PLACEMENT_STATUS?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="INCLUDED_PLACEMENT_STATUS" name="INCLUDED_PLACEMENT_STATUS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS,PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($INCLUDED_PLACEMENT_STATUS_ARR as $INCLUDED_PLACEMENT_STATUS){
																if($INCLUDED_PLACEMENT_STATUS == $PK_PLACEMENT_STATUS) {
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
													<label ><?=DISPLAY_OPTIONS?></label>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11">
													<div class="custom-control custom-radio col-md-6">
														<input type="radio" id="DISPLAY_OPTIONS_1" name="DISPLAY_OPTIONS" value="1" class="custom-control-input" <? if($DISPLAY_OPTIONS == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="DISPLAY_OPTIONS_1">CIP Definition</label>
													</div>
													<div class="custom-control custom-radio col-md-6">
														<input type="radio" id="DISPLAY_OPTIONS_2" name="DISPLAY_OPTIONS" value="2" class="custom-control-input" <? if($DISPLAY_OPTIONS == 2) echo "checked"; ?> >
														<label class="custom-control-label" for="DISPLAY_OPTIONS_2">Job Description</label>
													</div>
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
		
		$('#INCLUDED_PLACEMENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=INCLUDED_PLACEMENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=INCLUDED_PLACEMENT_STATUS?> selected'
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
	});
	</script>
</body>

</html>