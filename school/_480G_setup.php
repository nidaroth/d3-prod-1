<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/_1098T_Setup.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT _4807G FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 || $res_add_on->fields['_4807G'] == 0){
	header("location:../index");
	exit;
}

$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("select * from _4807G_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

	// DIAM - 400
	if ($_POST['AMENDED_DATE'] != '' && $_POST['AMENDED'] == '1') { 
		$sAMENDED_DATE 				= date("Y-m-d",strtotime($_POST['AMENDED_DATE']));
	}
	else{

		$sAMENDED_DATE 				= "";
	}
	// End DIAM - 400
	
	$IPEDES_FALL_COLLECTION['EXCLUDED_PROGRAMS'] 			= implode(",",$_POST['EXCLUDED_PROGRAMS']);
	$IPEDES_FALL_COLLECTION['EXCLUDED_STUDENT_STATUS'] 		= implode(",",$_POST['EXCLUDED_STUDENT_STATUS']);
	$IPEDES_FALL_COLLECTION['TUITION_FEES_RELATED_EXPENSES']= implode(",",$_POST['TUITION_FEES_RELATED_EXPENSES']);
	$IPEDES_FALL_COLLECTION['FINANCIAL_AID_RECEIVED']		= implode(",",$_POST['FINANCIAL_AID_RECEIVED']);
	$IPEDES_FALL_COLLECTION['AMENDED']						= $_POST['AMENDED'];

	$IPEDES_FALL_COLLECTION['AMENDED_DATE']					= $sAMENDED_DATE;
	$IPEDES_FALL_COLLECTION['IGNORE_ENROLLMENT_REQ']		= $_POST['IGNORE_ENROLLMENT_REQ'];
	
	if($res->RecordCount() == 0){
		$IPEDES_FALL_COLLECTION['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$IPEDES_FALL_COLLECTION['CREATED_BY'] = $_SESSION['PK_USER'];
		$IPEDES_FALL_COLLECTION['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('_4807G_SETUP', $IPEDES_FALL_COLLECTION, 'insert');
		$PK_IPEDES_SPRING_COLLECTION = $db->insert_ID();
	} else {
		$IPEDES_FALL_COLLECTION['EDITED_BY'] = $_SESSION['PK_USER'];
		$IPEDES_FALL_COLLECTION['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('_4807G_SETUP', $IPEDES_FALL_COLLECTION, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_IPEDES_SPRING_COLLECTION = $_GET['id'];
	}
	header("location:_480G_setup");
}
$res = $db->Execute("select * from _4807G_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$EXCLUDED_PROGRAMS_ARR 	 		= explode(",",$res->fields['EXCLUDED_PROGRAMS']);
$EXCLUDED_STUDENT_STATUS_ARR 	= explode(",",$res->fields['EXCLUDED_STUDENT_STATUS']);
$TUITION_FEES_RELATED_EXPENSES_ARR 	= explode(",",$res->fields['TUITION_FEES_RELATED_EXPENSES']);
$FINANCIAL_AID_RECEIVED_ARR 		= explode(",",$res->fields['FINANCIAL_AID_RECEIVED']);
$AMENDED 						= $res->fields['AMENDED'];
//echo $res->fields['AMENDED_DATE'];exit;
if ($res->fields['AMENDED_DATE'] != '' && $res->fields['AMENDED_DATE'] != '0000-00-00') {
	$AMENDED_DATE 				= date("m/d/Y",strtotime($res->fields['AMENDED_DATE']));
}
else{

	$AMENDED_DATE 				= ""; // date("m/d/Y")
}
$IGNORE_ENROLLMENT_REQ 			= $res->fields['IGNORE_ENROLLMENT_REQ'];

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
	<title><?=MNU_PUERTO_RICO_480G_SETUP?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}

		.red a > label {
			color: red !important;
		}
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
                        <h4 class="text-themecolor"><?=MNU_PUERTO_RICO_480G_SETUP?></h4>
                    </div>
                    <div class="col-md-6 align-self-center">
                    		<a href="manage_480G_ein" style="float: right;" class="btn btn-info d-none d-lg-block"> <?=GO_TO_EIN?></a>
					</div>
					<div class="align-self-center">
                        	<a href="_480G"class="btn btn-info d-none d-lg-block"> <?=GO_TO_REPORT?></a>
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
												<div class="col-12 col-sm-12 ">
													<span class="bar"></span> 
													<label ><?=EXCLUSIONS?></label>
												</div>
											</div>
											<br /><br /><br />

											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDED_STUDENT_STATUS?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="EXCLUDED_STUDENT_STATUS" name="EXCLUDED_STUDENT_STATUS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION,ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}

															foreach($EXCLUDED_STUDENT_STATUS_ARR as $EXCLUDED_STUDENT_STATUS){
																if($EXCLUDED_STUDENT_STATUS == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'].' '.$Status?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDED_PROGRAMS?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="EXCLUDED_PROGRAMS" name="EXCLUDED_PROGRAMS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION,ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_CAMPUS_PROGRAM 	= $res_type->fields['PK_CAMPUS_PROGRAM']; 
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															
															foreach($EXCLUDED_PROGRAMS_ARR as $EXCLUDED_PROGRAMS){
																if($EXCLUDED_PROGRAMS == $PK_CAMPUS_PROGRAM) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option data-id="test" value="<?=$PK_CAMPUS_PROGRAM?>" <?=$selected?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION'].' '.$Status?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-6 col-sm-6 ">
													<span class="bar"></span> 
													<label ><?=AWARD_LEDGER_CODES?></label>
												</div>
											</div>
											<br /><br />

											<div class="d-flex" style="margin-bottom: 31px;">
												<div class="col-7 col-sm-6 " style="font-size: 12px;">
													<span class="bar"></span> 
													<label ><?=MNU_FARE?></label>
												</div>
											
												<div class="col-3 col-sm-3">
													<button type="button" onclick="show_setup()" class="btn waves-effect waves-light btn-dark"><?=SETUP?></button>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=TUITION_FEE_OTHER_EXP?></label>
												</div>
											</div>
											<div class="d-flex" style="margin-bottom: 20px;">
												<div class="col-12 col-sm-12 form-group">
													<select id="TUITION_FEES_RELATED_EXPENSES" name="TUITION_FEES_RELATED_EXPENSES[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION,ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE']; 
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach($TUITION_FEES_RELATED_EXPENSES_ARR as $EXCLUDED_FEE_LEDGER_CODES){
																if($EXCLUDED_FEE_LEDGER_CODES == $PK_AR_LEDGER_CODE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_AR_LEDGER_CODE?>" <?=$selected?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['LEDGER_DESCRIPTION'].' '.$Status?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=FINANCIAL_AID_RECEIVED?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="FINANCIAL_AID_RECEIVED" name="FINANCIAL_AID_RECEIVED[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION,ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE']; 
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach($FINANCIAL_AID_RECEIVED_ARR as $EXCLUDED_FEE_LEDGER_CODES){
																if($EXCLUDED_FEE_LEDGER_CODES == $PK_AR_LEDGER_CODE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_AR_LEDGER_CODE?>" <?=$selected?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['LEDGER_DESCRIPTION'].' '.$Status?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											
										</div>
										<div class="col-6 col-sm-6 ">
											<div class="row form-group">
												<div class="custom-control col-md-6"><label ><?=AMENDED?></label></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="AMENDED_1" name="AMENDED" value="1" <? if($AMENDED == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="AMENDED_1"><?=YES?></label>
												</div>
												<div class="custom-control custom-radio col-md-2">
													<input type="radio" id="AMENDED_2" name="AMENDED" value="0" <? if($AMENDED == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="AMENDED_2"><?=NO?></label>
												</div>
											</div>
											
											<div class="row"  id="AMEND_DATE" <? if($AMENDED != 1) echo "style='display:none;'"; ?> >
												<div class="custom-control col-md-6"><label ><?=AMENDED_DATE?></label></div>
												<div class="form-group m-b-40">
													<input type="text" class="form-control required-entry date" id="AMENDED_DATE" name="AMENDED_DATE" value="<?=$AMENDED_DATE?>" >
												</div>
												
											</div>
											
											<div class="row form-group">
												<div class="custom-control col-md-6"><label ><?=IGNORE_ENROLLMENT_REQUIREMENT?></label></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="IGNORE_ENROLLMENT_REQ_1" name="IGNORE_ENROLLMENT_REQ" value="1" <? if($IGNORE_ENROLLMENT_REQ == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="IGNORE_ENROLLMENT_REQ_1"><?=YES?></label>
												</div>
												<div class="custom-control custom-radio col-md-2">
													<input type="radio" id="IGNORE_ENROLLMENT_REQ_2" name="IGNORE_ENROLLMENT_REQ" value="0" <? if($IGNORE_ENROLLMENT_REQ == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="IGNORE_ENROLLMENT_REQ_2"><?=NO?></label>
												</div>
											</div>

											<div class="row form-group" style="padding-top: 255px;">
												<div class="col-9 col-sm-9">
												</div>
												<div class="col-3 col-sm-3">
													<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
													<button type="button" onclick="window.location.href='index'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
												</div>
											</div>
											
											
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

		<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">

		jQuery(document).ready(function($) { 
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
		});

		var form1 = new Validation('form1');
		function show_required_fields(){
			if(document.getElementById('REQUIRED_FIELDS_DIV').style.display == 'none')
				document.getElementById('REQUIRED_FIELDS_DIV').style.display = 'block';
			else
				document.getElementById('REQUIRED_FIELDS_DIV').style.display = 'none';
		}
		
		function show_setup(){
			var w = 1300;
			var h = 550;
			// var id = common_id;
			var left = (screen.width/2)-(w/2);
			var top = (screen.height/2)-(h/2);
			var parameter = 'toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,width='+w+', height='+h+', top='+top+', left='+left;
			window.open('manage_480G_ledger','',parameter);
			return false;
		}
	</script>
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {

		// DIAM - 400
		$('#AMENDED_1').click(function(){
			$("#AMEND_DATE").show();
		});
		$('#AMENDED_2').click(function(){
			$("#AMEND_DATE").hide();
		});
		// End DIAM - 400

		$('#EXCLUDED_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDED_STUDENT_STATUS?> selected'
		});
		
		$('#EXCLUDED_PROGRAMS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_PROGRAMS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDED_PROGRAMS?> selected'
		});
		
		$('#TUITION_FEES_RELATED_EXPENSES').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=TUITION_FEES_RELATED_EXPENSES?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=TUITION_FEES_RELATED_EXPENSES?> selected'
		});

		$('#FINANCIAL_AID_RECEIVED').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FINANCIAL_AID_RECEIVED?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=FINANCIAL_AID_RECEIVED?> selected'
		});
		
		// added color for inactive text
		child=$('.multiselect-container').children();
		child.each(function (i,val) {
			var str1=val.innerText
			if(str1.indexOf("Inactive") != -1){
				$(this).addClass('red')				
			}

		});
	});


	</script>
</body>

</html>