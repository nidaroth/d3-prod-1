<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/bppe_report_setup.php");
require_once("check_access.php");
require_once("get_department_from_t.php");

$res = $db->Execute("SELECT BPPE FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['BPPE'] == 0 || $res->fields['BPPE'] == '') {
	header("location:../index");
	exit;
}

if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}
$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("select * from S_BPPE_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$BPPE_SETUP['EXCLUDED_PROGRAMS'] 					 = implode(",",$_POST['EXCLUDED_PROGRAMS']);
	$BPPE_SETUP['EXCLUDE_STUDENT_STATUS'] 				 = implode(",",$_POST['EXCLUDE_STUDENT_STATUS']);
	$BPPE_SETUP['STUDENTS_NOT_AVAILABLE_FOR_GRADUATION'] = implode(",",$_POST['STUDENTS_NOT_AVAILABLE_FOR_GRADUATION']);
	$BPPE_SETUP['STUDENTS_NOT_AVAILABLE_FOR_PLACEMENT']  = implode(",",$_POST['STUDENTS_NOT_AVAILABLE_FOR_PLACEMENT']);
	$BPPE_SETUP['FEDERAL_LOAN_DEBT_LEDGER_CODES'] 		 = implode(",",$_POST['FEDERAL_LOAN_DEBT_LEDGER_CODES']);
	$BPPE_SETUP['TAKING_LICENSURE_EXAM'] 				 = implode(",",$_POST['TAKING_LICENSURE_EXAM']);
	$BPPE_SETUP['LICENSURE_EXAM'] 					 	 = implode(",",$_POST['LICENSURE_EXAM']);
	$BPPE_SETUP['PASSED_FIRST_EXAM'] 				 	 = implode(",",$_POST['PASSED_FIRST_EXAM']);
	$BPPE_SETUP['FAILED_FIRST_EXAM'] 				 	 = implode(",",$_POST['FAILED_FIRST_EXAM']);
	
	if($res->RecordCount() == 0){
		$BPPE_SETUP['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$BPPE_SETUP['CREATED_BY'] = $_SESSION['PK_USER'];
		$BPPE_SETUP['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_BPPE_SETUP', $BPPE_SETUP, 'insert');
		$PK_BPPE_SETUP = $db->insert_ID();
	} else {
		$BPPE_SETUP['EDITED_BY'] = $_SESSION['PK_USER'];
		$BPPE_SETUP['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_BPPE_SETUP', $BPPE_SETUP, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_BPPE_SETUP = $_GET['id'];
	}
	header("location:bppe_report_setup");
}
$res = $db->Execute("select * from S_BPPE_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$EXCLUDED_PROGRAMS_ARR 						= explode(",",$res->fields['EXCLUDED_PROGRAMS']);
$EXCLUDE_STUDENT_STATUS_ARR 				= explode(",",$res->fields['EXCLUDE_STUDENT_STATUS']);
$STUDENTS_NOT_AVAILABLE_FOR_GRADUATION_ARR 	= explode(",",$res->fields['STUDENTS_NOT_AVAILABLE_FOR_GRADUATION']);
$STUDENTS_NOT_AVAILABLE_FOR_PLACEMENT_ARR 	= explode(",",$res->fields['STUDENTS_NOT_AVAILABLE_FOR_PLACEMENT']);
$FEDERAL_LOAN_DEBT_LEDGER_CODES_ARR 		= explode(",",$res->fields['FEDERAL_LOAN_DEBT_LEDGER_CODES']);
$TAKING_LICENSURE_EXAM_ARR 					= explode(",",$res->fields['TAKING_LICENSURE_EXAM']);
$LICENSURE_EXAM_ARR 						= explode(",",$res->fields['LICENSURE_EXAM']);
$PASSED_FIRST_EXAM_ARR 						= explode(",",$res->fields['PASSED_FIRST_EXAM']);
$FAILED_FIRST_EXAM_ARR 						= explode(",",$res->fields['FAILED_FIRST_EXAM']);

$REQUIRED_FIELDS = "Registrar > Student > Info Tab > Last Name

Registrar > Student > Info Tab > First Name

Registrar > Student > Enrollment Tab > First Term Date

Registrar > Student > Enrollment Tab > Program

Registrar > Student > Enrollment Tab > Status

Registrar > Student > Enrollment Tab > End Date (Grad Date, LDA, Determination Date, Drop Date)

Registrar > Student > Enrollment Tab > Drop Reason(Where Applicable)

Registrar > Student > Enrollment Tab > Campus

Finance > Student > Finance Plan > Disbursements > Gross & Fee (Loans = Disbursement Amount + Fees)

Accounting > Student > Ledger (Via Payment or Miscellaneous Batch) 

Placement > Student > Enrollment Tab > Placement Status

Placement > Student > Student Jobs (Where Applicable)

Placement > Student > Student Jobs > Self Employed (Where Applicable)

Placement > Student > Student Jobs > Institutional Employment (Where Applicable)

Placement > Student > Student Jobs > Weekly Hours

Placement > Student > Student Jobs > Annual Salary

Placement > Student > Activities > Events (Where Applicable for Licensure Exams)

Setup > Registrar > Program > Info Tab > Program Code

Setup > Registrar > Program > Info Tab > Program Description

Setup > Registrar > Program > Info Tab > Program Length > Months

Setup > Student > Student Status > End Date

Setup > Placement > Placement Status
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
	<title><?=BPPE_SETUP_TITLE?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><?=BPPE_SETUP_TITLE?></h4>
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
													<label ><?=EXCLUDED_PROGRAMS?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="EXCLUDED_PROGRAMS" name="EXCLUDED_PROGRAMS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_CAMPUS_PROGRAM 	= $res_type->fields['PK_CAMPUS_PROGRAM']; 
															foreach($EXCLUDED_PROGRAMS_ARR as $EXCLUDED_PROGRAMS){
																if($EXCLUDED_PROGRAMS == $PK_CAMPUS_PROGRAM) {
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
									
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDE_STUDENT_STATUS?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="EXCLUDE_STUDENT_STATUS" name="EXCLUDE_STUDENT_STATUS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															foreach($EXCLUDE_STUDENT_STATUS_ARR as $EXCLUDE_STUDENT_STATUS){
																if($EXCLUDE_STUDENT_STATUS == $PK_STUDENT_STATUS) {
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
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=STUDENTS_NOT_AVAILABLE_FOR_GRADUATION?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="STUDENTS_NOT_AVAILABLE_FOR_GRADUATION" name="STUDENTS_NOT_AVAILABLE_FOR_GRADUATION[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by DROP_REASON ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON']; 
															foreach($STUDENTS_NOT_AVAILABLE_FOR_GRADUATION_ARR as $STUDENTS_NOT_AVAILABLE_FOR_GRADUATION){
																if($STUDENTS_NOT_AVAILABLE_FOR_GRADUATION == $PK_DROP_REASON) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_DROP_REASON?>" <?=$selected?> ><?=$res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=STUDENTS_NOT_AVAILABLE_FOR_PLACEMENT?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="STUDENTS_NOT_AVAILABLE_FOR_PLACEMENT" name="STUDENTS_NOT_AVAILABLE_FOR_PLACEMENT[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS,PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($STUDENTS_NOT_AVAILABLE_FOR_PLACEMENT_ARR as $STUDENTS_NOT_AVAILABLE_FOR_PLACEMENT){
																if($STUDENTS_NOT_AVAILABLE_FOR_PLACEMENT == $PK_PLACEMENT_STATUS) {
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
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=FEDERAL_LOAN_DEBT_LEDGER_CODES?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="FEDERAL_LOAN_DEBT_LEDGER_CODES" name="FEDERAL_LOAN_DEBT_LEDGER_CODES[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND TYPE = 1 order by CODE ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE']; 
															foreach($FEDERAL_LOAN_DEBT_LEDGER_CODES_ARR as $FEDERAL_LOAN_DEBT_LEDGER_CODES){
																if($FEDERAL_LOAN_DEBT_LEDGER_CODES == $PK_AR_LEDGER_CODE) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_AR_LEDGER_CODE?>" <?=$selected?> ><?=$res_type->fields['CODE'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
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
											
											<div class="col-12 col-sm-12 form-group">
												<h4 class="card-title"><?=LICENSURE_EXAM?></h4>
											</div>
											
											<div class="d-flex">
												<div class="col-1 col-sm-1">
												</div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=TAKING_LICENSURE_EXAM?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-1 col-sm-1">
												</div>
												<div class="col-11 col-sm-11 form-group">
													<select id="TAKING_LICENSURE_EXAM" name="TAKING_LICENSURE_EXAM[]" multiple class="form-control" >
														<? $PK_DEPARTMENT = get_department_from_t(6);	
														$res_type = $db->Execute("select PK_NOTE_TYPE,NOTE_TYPE,DESCRIPTION from M_NOTE_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 AND (PK_DEPARTMENT = '$PK_DEPARTMENT' ) order by NOTE_TYPE ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_NOTE_TYPE 	= $res_type->fields['PK_NOTE_TYPE']; 
															foreach($TAKING_LICENSURE_EXAM_ARR as $TAKING_LICENSURE_EXAM){
																if($TAKING_LICENSURE_EXAM == $PK_NOTE_TYPE) {
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
											
											<div class="row">
												<div class="col-1 col-sm-1">
												</div>
												<div class="col-11 col-sm-11">
													<div class="d-flex">
														<div class="col-12 col-sm-12 focused">
															<span class="bar"></span> 
															<label ><?=LICENSURE_EXAM_NAME?></label>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<select id="LICENSURE_EXAM" name="LICENSURE_EXAM[]" multiple class="form-control" >
																<? $PK_DEPARTMENT = get_department_from_t(6);	
																$res_type = $db->Execute("select PK_EVENT_OTHER,EVENT_OTHER from M_EVENT_OTHER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 AND PK_DEPARTMENT = '$PK_DEPARTMENT' order by EVENT_OTHER ASC");
																while (!$res_type->EOF) { 
																	$selected 			= "";
																	$PK_EVENT_OTHER 	= $res_type->fields['PK_EVENT_OTHER']; 
																	foreach($LICENSURE_EXAM_ARR as $LICENSURE_EXAM){
																		if($LICENSURE_EXAM == $PK_EVENT_OTHER) {
																			$selected = 'selected';
																			break;
																		}
																	} ?>
																	<option value="<?=$PK_EVENT_OTHER?>" <?=$selected?> ><?=$res_type->fields['EVENT_OTHER'] ?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-12 focused">
															<span class="bar"></span> 
															<label ><?=PASSED_FIRST_EXAM?></label>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<select id="PASSED_FIRST_EXAM" name="PASSED_FIRST_EXAM[]" multiple class="form-control" >
																<? $PK_DEPARTMENT = get_department_from_t(6);	
																$res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS from M_NOTE_STATUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 3 AND (PK_DEPARTMENT = '$PK_DEPARTMENT') order by NOTE_STATUS ASC");
																while (!$res_type->EOF) { 
																	$selected 			= "";
																	$PK_NOTE_STATUS 	= $res_type->fields['PK_NOTE_STATUS']; 
																	foreach($PASSED_FIRST_EXAM_ARR as $PASSED_FIRST_EXAM){
																		if($PASSED_FIRST_EXAM == $PK_NOTE_STATUS) {
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
													
													<div class="d-flex">
														<div class="col-12 col-sm-12 focused">
															<span class="bar"></span> 
															<label ><?=FAILED_FIRST_EXAM?></label>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<select id="FAILED_FIRST_EXAM" name="FAILED_FIRST_EXAM[]" multiple class="form-control" >
																<? $PK_DEPARTMENT = get_department_from_t(6);	
																$res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS from M_NOTE_STATUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 3 AND (PK_DEPARTMENT = '$PK_DEPARTMENT') order by NOTE_STATUS ASC");
																while (!$res_type->EOF) { 
																	$selected 			= "";
																	$PK_NOTE_STATUS 	= $res_type->fields['PK_NOTE_STATUS']; 
																	foreach($FAILED_FIRST_EXAM_ARR as $FAILED_FIRST_EXAM){
																		if($FAILED_FIRST_EXAM == $PK_NOTE_STATUS) {
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
												</div>
											</div>
											
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
										<div class="col-3 col-sm-3">
											<button type="submit" name="btn" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											<button type="button" onclick="window.location.href='management'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
										</div>
										
										<div class="col-3 col-sm-3">
											<button type="button" onclick="window.location.href='bppe_school_performance_fact_sheets'"  class="btn waves-effect waves-light btn-info" ><?=GO_TO_REPORT?></button>
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
		function show_required_fields(){
			if(document.getElementById('REQUIRED_FIELDS_DIV').style.display == 'none')
				document.getElementById('REQUIRED_FIELDS_DIV').style.display = 'block';
			else
				document.getElementById('REQUIRED_FIELDS_DIV').style.display = 'none';
		}
	</script>
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#EXCLUDED_PROGRAMS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_PROGRAMS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDED_PROGRAMS?> selected'
		});
		
		$('#EXCLUDE_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDE_STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDE_STUDENT_STATUS?> selected'
		});
		
		$('#STUDENTS_NOT_AVAILABLE_FOR_GRADUATION').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENTS_NOT_AVAILABLE_FOR_GRADUATION?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=STUDENTS_NOT_AVAILABLE_FOR_GRADUATION?> selected'
		});
		
		$('#STUDENTS_NOT_AVAILABLE_FOR_PLACEMENT').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENTS_NOT_AVAILABLE_FOR_PLACEMENT?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=STUDENTS_NOT_AVAILABLE_FOR_PLACEMENT?> selected'
		});
		
		$('#FEDERAL_LOAN_DEBT_LEDGER_CODES').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FEDERAL_LOAN_DEBT_LEDGER_CODES?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=FEDERAL_LOAN_DEBT_LEDGER_CODES?> selected'
		});
		
		$('#TAKING_LICENSURE_EXAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=TAKING_LICENSURE_EXAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=TAKING_LICENSURE_EXAM?> selected'
		});
		
		$('#LICENSURE_EXAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=LICENSURE_EXAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=LICENSURE_EXAM?> selected'
		});
		
		$('#PASSED_FIRST_EXAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PASSED_FIRST_EXAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PASSED_FIRST_EXAM?> selected'
		});
		
		$('#FAILED_FIRST_EXAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FAILED_FIRST_EXAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=FAILED_FIRST_EXAM?> selected'
		});
		
	});
	</script>
</body>

</html>
