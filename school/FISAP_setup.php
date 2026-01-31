<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/FISAP_report.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT FISAP FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['FISAP'] == 0 || check_access('MANAGEMENT_FISAP') == 0){
	header("location:../index");
	exit;
}

$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("select * from S_FISAP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$FISAP['EXCLUDED_PROGRAM'] 			= implode(",",$_POST['EXCLUDED_PROGRAM']);
	$FISAP['EXCLUDED_STUDENT_STATUS'] 	= implode(",",$_POST['EXCLUDED_STUDENT_STATUS']);
	$FISAP['LEDGER_CODE'] 				= implode(",",$_POST['LEDGER_CODE']);
	
	$FISAP['FSEOG'] 					= implode(",",$_POST['FSEOG']);
	$FISAP['FWS'] 						= implode(",",$_POST['FWS']);
	$FISAP['PERKINS'] 					= implode(",",$_POST['PERKINS']);
	
	if($res->RecordCount() == 0){
		$FISAP['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$FISAP['CREATED_BY'] = $_SESSION['PK_USER'];
		$FISAP['CREATED_ON'] = date("Y-m-d H:i:s");
		$FISAP['EDITED_BY']  = $_SESSION['PK_USER'];
		$FISAP['EDITED_ON']  = date("Y-m-d H:i:s");
		db_perform('S_FISAP', $FISAP, 'insert');
		$PK_FISAP = $db->insert_ID();
	} else {
		$FISAP['EDITED_BY'] = $_SESSION['PK_USER'];
		$FISAP['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_FISAP', $FISAP, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_FISAP = $_GET['id'];
	}
	
	header("location:FISAP_setup");
}
$res = $db->Execute("select * from S_FISAP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$EXCLUDED_PROGRAM_ARR 			 = explode(",",$res->fields['EXCLUDED_PROGRAM']);
$EXCLUDED_STUDENT_STATUS_ARR 	 = explode(",",$res->fields['EXCLUDED_STUDENT_STATUS']);
$LEDGER_CODE_ARR 				 = explode(",",$res->fields['LEDGER_CODE']);

$FSEOG_ARR 				 		= explode(",",$res->fields['FSEOG']);
$FWS_ARR 						= explode(",",$res->fields['FWS']);
$PERKINS_ARR 				 	= explode(",",$res->fields['PERKINS']);
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
	<title><?=MNU_FISAP_SETUP ?> | <?=$title?></title>
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
							<?=MNU_FISAP_SETUP ?>
						</h4>
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
										<button type="button" onclick="window.location.href='FISAP_report'"  class="btn waves-effect waves-light btn-info" ><?=GO_TO_REPORT?></button>
									</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<div class="d-flex">
										<div class="col-6 col-sm-6 ">
										
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
													<label ><?=INCLUDED_LEDGER_CODE?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="LEDGER_CODE" name="LEDGER_CODE[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION, ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['CODE'].' - '.$res_type->fields['LEDGER_DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE']; 
															foreach($LEDGER_CODE_ARR as $LEDGER_CODE){
																if($LEDGER_CODE == $PK_AR_LEDGER_CODE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_AR_LEDGER_CODE?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
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
													<label ><?=FSEOG?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="FSEOG" name="FSEOG[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION, ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['CODE'].' - '.$res_type->fields['LEDGER_DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE']; 
															foreach($FSEOG_ARR as $FSEOG){
																if($FSEOG == $PK_AR_LEDGER_CODE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_AR_LEDGER_CODE?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=FWS?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="FWS" name="FWS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION, ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['CODE'].' - '.$res_type->fields['LEDGER_DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE']; 
															foreach($FWS_ARR as $FWS){
																if($FWS == $PK_AR_LEDGER_CODE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_AR_LEDGER_CODE?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=PERKINS?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PERKINS" name="PERKINS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION, ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['CODE'].' - '.$res_type->fields['LEDGER_DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE']; 
															foreach($PERKINS_ARR as $PERKINS){
																if($PERKINS == $PK_AR_LEDGER_CODE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_AR_LEDGER_CODE?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
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
											<button type="button" onclick="window.location.href='FISAP_report'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
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
		
		$('#LEDGER_CODE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=INCLUDED_LEDGER_CODE?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=INCLUDED_LEDGER_CODE?> selected'
		});
		
		$('#FSEOG').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FSEOG?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=FSEOG?> selected'
		});
		
		$('#FWS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FWS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=FWS?> selected'
		});
		
		$('#PERKINS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PERKINS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			enableCaseInsensitiveFiltering: true,
			nSelectedText: '<?=PERKINS?> selected'
		});
	});
	</script>
	
</body>

</html>