<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/ipeds_fall_collections_setup.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT COE,ECM,_1098T,_90_10,IPEDS,POPULATION_REPORT FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['IPEDS'] == 0 || check_access('MANAGEMENT_IPEDS') == 0){
	header("location:../index");
	exit;
}

$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("select * from S_IPEDES_FALL_COLLECTION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$IPEDES_FALL_COLLECTION['COMPLETION_STUDENT_STATUSES'] 	= implode(",",$_POST['COMPLETION_STUDENT_STATUSES']);
	$IPEDES_FALL_COLLECTION['EXCLUDED_PROGRAM'] 			= implode(",",$_POST['EXCLUDED_PROGRAM']);
	$IPEDES_FALL_COLLECTION['EXCLUDED_STUDENT_STATUS'] 		= implode(",",$_POST['EXCLUDED_STUDENT_STATUS']);
	
	if($res->RecordCount() == 0){
		$IPEDES_FALL_COLLECTION['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$IPEDES_FALL_COLLECTION['CREATED_BY'] = $_SESSION['PK_USER'];
		$IPEDES_FALL_COLLECTION['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_IPEDES_FALL_COLLECTION', $IPEDES_FALL_COLLECTION, 'insert');
		$PK_IPEDES_FALL_COLLECTION = $db->insert_ID();
	} else {
		$IPEDES_FALL_COLLECTION['EDITED_BY'] = $_SESSION['PK_USER'];
		$IPEDES_FALL_COLLECTION['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_IPEDES_FALL_COLLECTION', $IPEDES_FALL_COLLECTION, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_IPEDES_FALL_COLLECTION = $_GET['id'];
	}
	header("location:ipeds_fall_collections_setup");
}
$res = $db->Execute("select * from S_IPEDES_FALL_COLLECTION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$COMPLETION_STUDENT_STATUSES_ARR = explode(",",$res->fields['COMPLETION_STUDENT_STATUSES']);
$EXCLUDED_PROGRAM_ARR 			 = explode(",",$res->fields['EXCLUDED_PROGRAM']);
$EXCLUDED_STUDENT_STATUS_ARR 	 = explode(",",$res->fields['EXCLUDED_STUDENT_STATUS']);

$REQUIRED_FIELDS = "Registrar > Student > Info Tab > Last Name
Registrar > Student > Info Tab > First Name
Registrar > Student > Info Tab > Date of Birth
Registrar > Student > Info Tab > Gender
Registrar > Student > Info Tab > IPEDS Ethnicity 

Registrar > Student > Enrollment Tab > IPEDS Enrollment Status
Registrar > Student > Enrollment Tab > Transfer In(Where Applicable)
Registrar > Student > Enrollment Tab > Enrollment End Date(Where Applicable)
Registrar > Student > Enrollment Tab > Campus

Setup > Registrar > Program > Info Tab > Program Code
Setup > Registrar > Program > Info Tab > Program Description
Setup > Registrar > Program > Info Tab > Diploma/Certificate
Setup > Registrar > Program > Info Tab > CIP Code
Setup > Registrar > Program > Info Tab > Credential Level 

Setup > Student > Student Status > End Date
Setup > Student > Student Status > Completed (Y/N) 
";
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
	<title><?=MNU_IPEDS_FALL_COLLECTIONS_SETUP_TITLE?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><?=MNU_IPEDS_FALL_COLLECTIONS_SETUP_TITLE?></h4>
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
													<label ><?=SELECT_SETUP_CODES?></label>
												</div>
											</div>
											<br /><br /><br />
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=COMPLETION_STUDENT_STATUSES?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="COMPLETION_STUDENT_STATUSES" name="COMPLETION_STUDENT_STATUSES[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION,ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC"); //DIAM-2416
														while (!$res_type->EOF) { 
															//DIAM-2416
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															//DIAM-2416
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															foreach($COMPLETION_STUDENT_STATUSES_ARR as $COMPLETION_STUDENT_STATUSES){
																if($COMPLETION_STUDENT_STATUSES == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?>  <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']. ' ' .$Status?></option> <!-- //DIAM-2416 -->
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
														<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION,ACTIVE from M_CAMPUS_PROGRAM WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC"); //DIAM-2416
														while (!$res_type->EOF) { 
															//DIAM-2416
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															//DIAM-2416

															$selected 			= "";
															$PK_CAMPUS_PROGRAM 	= $res_type->fields['PK_CAMPUS_PROGRAM']; 
															foreach($EXCLUDED_PROGRAM_ARR as $EXCLUDED_PROGRAM){
																if($EXCLUDED_PROGRAM == $PK_CAMPUS_PROGRAM) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_CAMPUS_PROGRAM?>" <?=$selected?>  <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']. ' ' .$Status?></option>
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
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION,ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC"); //DIAM-2416
														while (!$res_type->EOF) { 
															//DIAM-2416
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															//DIAM-2416
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															foreach($EXCLUDED_STUDENT_STATUS_ARR as $EXCLUDED_STUDENT_STATUS){
																if($EXCLUDED_STUDENT_STATUS == $PK_STUDENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?>  <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']. ' ' .$Status?></option> <!-- //DIAM-2416 -->
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<hr />
											
											<div class="d-flex">
												<div class="col-3 col-sm-3 ">
													<span class="bar"></span> 
													<label ><?=PROGRAM_AWARD_LEVEL?></label>
												</div>
											
												<div class="col-3 col-sm-3 focused">
													<button type="button" onclick="show_setup()" class="btn waves-effect waves-light btn-dark"><?=SETUPS?></button>
												</div>
											</div>
											<br /><br />
											
											<div class="d-flex">
												<div class="col-3 col-sm-3 ">
													<span class="bar"></span> 
													<label ><?=REQUIRED_FIELDS?></label>
												</div>
											
												<div class="col-3 col-sm-3 focused">
													<button type="button" onclick="show_required_fields()"  class="btn waves-effect waves-light btn-dark"><?=REQUIREMENTS?></button>
												</div>
											</div>
										</div>
										<div class="col-6 col-sm-6 ">
											<div class="d-flex">
												<div class="col-12 col-sm-12 " style="display:none" id="REQUIRED_FIELDS_DIV" >
													<?=nl2br($REQUIRED_FIELDS) ?>
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
			window.open('program_award_level_setup','',parameter);
			return false;
		}
	</script>
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#COMPLETION_STUDENT_STATUSES').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COMPLETION_STUDENT_STATUSES?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=COMPLETION_STUDENT_STATUSES?> selected'
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