<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/ipeds_winter_collection_setup.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT COE,ECM,_1098T,_90_10,IPEDS,POPULATION_REPORT FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['IPEDS'] == 0 || check_access('MANAGEMENT_IPEDS') == 0){
	header("location:../index");
	exit;
}

$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("select * from S_IPEDS_WINTER_COLLECTION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$IPEDS_WINTER_COLLECTION['EXCLUDED_PROGRAM'] 		= implode(",",$_POST['EXCLUDED_PROGRAM']);
	$IPEDS_WINTER_COLLECTION['EXCLUDED_STUDENT_STATUS'] = implode(",",$_POST['EXCLUDED_STUDENT_STATUS']);
	$IPEDS_WINTER_COLLECTION['EXCLUDED_DROP_REASON'] 	= implode(",",$_POST['EXCLUDED_DROP_REASON']);
	$IPEDS_WINTER_COLLECTION['TRANSFER_OUT'] 			= implode(",",$_POST['TRANSFER_OUT']);
	$IPEDS_WINTER_COLLECTION['LARGEST_PROGRAM'] 		= implode(",",$_POST['LARGEST_PROGRAM']);
	$IPEDS_WINTER_COLLECTION['PART_A_GROUP_2A'] 		= implode(",",$_POST['PART_A_GROUP_2A']);
	$IPEDS_WINTER_COLLECTION['PART_A_GROUP_2B'] 		= implode(",",$_POST['PART_A_GROUP_2B']);
	$IPEDS_WINTER_COLLECTION['PART_A_GROUP_3'] 			= implode(",",$_POST['PART_A_GROUP_3']);
	$IPEDS_WINTER_COLLECTION['PART_B_GROUP_1'] 			= implode(",",$_POST['PART_B_GROUP_1']);
	$IPEDS_WINTER_COLLECTION['PELL'] 					= implode(",",$_POST['PELL']);
	$IPEDS_WINTER_COLLECTION['OTHER_FEDERAL_GRANTS'] 	= implode(",",$_POST['OTHER_FEDERAL_GRANTS']);
	$IPEDS_WINTER_COLLECTION['FEDERAL_STUDENT_LOANS'] 	= implode(",",$_POST['FEDERAL_STUDENT_LOANS']);
	$IPEDS_WINTER_COLLECTION['OTHER_LOANS'] 			= implode(",",$_POST['OTHER_LOANS']);
	$IPEDS_WINTER_COLLECTION['PART_C_GROUP_3'] 			= implode(",",$_POST['PART_C_GROUP_3']);
	$IPEDS_WINTER_COLLECTION['PART_C_GROUP_4'] 			= implode(",",$_POST['PART_C_GROUP_4']);
	$IPEDS_WINTER_COLLECTION['PART_D_GROUP_3'] 			= implode(",",$_POST['PART_D_GROUP_3']);
	$IPEDS_WINTER_COLLECTION['PART_E'] 					= implode(",",$_POST['PART_E']);
	$IPEDS_WINTER_COLLECTION['POST_911'] 				= implode(",",$_POST['POST_911']);
	$IPEDS_WINTER_COLLECTION['DEPARTMENT_OF_DEFENSE'] 	= implode(",",$_POST['DEPARTMENT_OF_DEFENSE']);
	$IPEDS_WINTER_COLLECTION['SUB_LOANS'] 				= implode(",",$_POST['SUB_LOANS']);
	$IPEDS_WINTER_COLLECTION['APPLICANT'] 				= implode(",",$_POST['APPLICANT']);
	$IPEDS_WINTER_COLLECTION['ADMISSIONS'] 				= implode(",",$_POST['ADMISSIONS']);
	
	if($res->RecordCount() == 0){
		$IPEDS_WINTER_COLLECTION['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$IPEDS_WINTER_COLLECTION['CREATED_BY'] = $_SESSION['PK_USER'];
		$IPEDS_WINTER_COLLECTION['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_IPEDS_WINTER_COLLECTION', $IPEDS_WINTER_COLLECTION, 'insert');
		$PK_IPEDES_SPRING_COLLECTION = $db->insert_ID();
	} else {
		$IPEDS_WINTER_COLLECTION['EDITED_BY'] = $_SESSION['PK_USER'];
		$IPEDS_WINTER_COLLECTION['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_IPEDS_WINTER_COLLECTION', $IPEDS_WINTER_COLLECTION, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_IPEDES_SPRING_COLLECTION = $_GET['id'];
	}
	header("location:ipeds_winter_collection_setup");
}
$res = $db->Execute("select * from S_IPEDS_WINTER_COLLECTION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$EXCLUDED_PROGRAM_ARR 		 = explode(",",$res->fields['EXCLUDED_PROGRAM']);
$EXCLUDED_STUDENT_STATUS_ARR = explode(",",$res->fields['EXCLUDED_STUDENT_STATUS']);
$EXCLUDED_DROP_REASON_ARR 	 = explode(",",$res->fields['EXCLUDED_DROP_REASON']);
$TRANSFER_OUT_ARR 		 	 = explode(",",$res->fields['TRANSFER_OUT']);
$LARGEST_PROGRAM_ARR 	 	 = explode(",",$res->fields['LARGEST_PROGRAM']);
$PART_A_GROUP_2A_ARR 	 	 = explode(",",$res->fields['PART_A_GROUP_2A']);
$PART_A_GROUP_2B_ARR 	 	 = explode(",",$res->fields['PART_A_GROUP_2B']);
$PART_A_GROUP_3_ARR 	 	 = explode(",",$res->fields['PART_A_GROUP_3']);
$PART_B_GROUP_1_ARR 	 	 = explode(",",$res->fields['PART_B_GROUP_1']);
$PELL_ARR 	 				 = explode(",",$res->fields['PELL']);
$OTHER_FEDERAL_GRANTS_ARR 	 = explode(",",$res->fields['OTHER_FEDERAL_GRANTS']);
$FEDERAL_STUDENT_LOANS_ARR 	 = explode(",",$res->fields['FEDERAL_STUDENT_LOANS']);
$OTHER_LOANS_ARR 	 		 = explode(",",$res->fields['OTHER_LOANS']);
$PART_C_GROUP_3_ARR 	 	 = explode(",",$res->fields['PART_C_GROUP_3']);
$PART_C_GROUP_4_ARR 	 	 = explode(",",$res->fields['PART_C_GROUP_4']);
$PART_D_GROUP_3_ARR 	 	 = explode(",",$res->fields['PART_D_GROUP_3']);
$PART_E_ARR 	 			 = explode(",",$res->fields['PART_E']);
$POST_911_ARR 			 	 = explode(",",$res->fields['POST_911']);
$DEPARTMENT_OF_DEFENSE_ARR 	 = explode(",",$res->fields['DEPARTMENT_OF_DEFENSE']);
$SUB_LOANS_ARR				 = explode(",",$res->fields['SUB_LOANS']);
$APPLICANT_ARR				 = explode(",",$res->fields['APPLICANT']);
$ADMISSIONS_ARR				 = explode(",",$res->fields['ADMISSIONS']);

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
	<title><?=MNU_IPEDS_WINTER_COLLECTIONS_SETUP_TITLE?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}

		/* added color for inactive text -  DIAM 22 */
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
                        <h4 class="text-themecolor"><?=MNU_IPEDS_WINTER_COLLECTIONS_SETUP_TITLE?></h4>
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
													<label ><?=EXCLUDED_PROGRAM?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="EXCLUDED_PROGRAM" name="EXCLUDED_PROGRAM[]" multiple class="form-control" >
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
															foreach($EXCLUDED_PROGRAM_ARR as $EXCLUDED_PROGRAM){
																if($EXCLUDED_PROGRAM == $PK_CAMPUS_PROGRAM) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_CAMPUS_PROGRAM?>" <?=$selected?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION'].' '.$Status?></option>
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
													<label ><?=EXCLUDED_DROP_REASON?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="EXCLUDED_DROP_REASON" name="EXCLUDED_DROP_REASON[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION,ACTIVE from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, DROP_REASON ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON'];
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															} 
															foreach($EXCLUDED_DROP_REASON_ARR as $EXCLUDED_DROP_REASON){
																if($EXCLUDED_DROP_REASON == $PK_DROP_REASON) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_DROP_REASON?>" <?=$selected?> ><?=$res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION'].' '.$Status?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=TRANSFER_OUT?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="TRANSFER_OUT" name="TRANSFER_OUT[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION,ACTIVE from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC,DROP_REASON ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON']; 
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach($TRANSFER_OUT_ARR as $TRANSFER_OUT){
																if($TRANSFER_OUT == $PK_DROP_REASON) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_DROP_REASON?>" <?=$selected?> ><?=$res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION'].' '.$Status?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=LARGEST_PROGRAM?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="LARGEST_PROGRAM" name="LARGEST_PROGRAM[]" multiple class="form-control" >
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
															foreach($LARGEST_PROGRAM_ARR as $LARGEST_PROGRAM){
																if($LARGEST_PROGRAM == $PK_CAMPUS_PROGRAM) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_CAMPUS_PROGRAM?>" <?=$selected?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION'].' '.$Status?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=APPLICANT?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="APPLICANT" name="APPLICANT[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION,ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC,STUDENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach($APPLICANT_ARR as $APPLICANT){
																if($APPLICANT == $PK_STUDENT_STATUS) {
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
													<label ><?=ADMISSIONS?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="ADMISSIONS" name="ADMISSIONS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION,ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, STUDENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															$ACTIVE 	= $res_type->fields['ACTIVE'];
															if ($ACTIVE == '0') {
																$Status = '(Inactive)';
															} else {
																$Status = '';
															}
															foreach($ADMISSIONS_ARR as $ADMISSIONS){
																if($ADMISSIONS == $PK_STUDENT_STATUS) {
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
										
										</div>
										<div class="col-6 col-sm-6 ">
											<div class="d-flex">
												<div class="col-12 col-sm-12 ">
													<span class="bar"></span> 
													<label ><?=STUDENT_FINANCIAL_AID_SETUP_CODES?></label>
												</div>
											</div>
											<br /><br /><br />
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=PART_A_GROUP_2A?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PART_A_GROUP_2A" name="PART_A_GROUP_2A[]" multiple class="form-control" >
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
															foreach($PART_A_GROUP_2A_ARR as $PART_A_GROUP_2A){
																if($PART_A_GROUP_2A == $PK_AR_LEDGER_CODE) {
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
													<label ><?=PART_A_GROUP_2B?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PART_A_GROUP_2B" name="PART_A_GROUP_2B[]" multiple class="form-control" >
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
															foreach($PART_A_GROUP_2B_ARR as $PART_A_GROUP_2B){
																if($PART_A_GROUP_2B == $PK_AR_LEDGER_CODE) {
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
													<label ><?=PART_A_GROUP_3?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PART_A_GROUP_3" name="PART_A_GROUP_3[]" multiple class="form-control" >
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
															foreach($PART_A_GROUP_3_ARR as $PART_A_GROUP_3){
																if($PART_A_GROUP_3 == $PK_AR_LEDGER_CODE) {
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
													<label ><?=PART_B_GROUP_1?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PART_B_GROUP_1" name="PART_B_GROUP_1[]" multiple class="form-control" >
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
															foreach($PART_B_GROUP_1_ARR as $PART_B_GROUP_1){
																if($PART_B_GROUP_1 == $PK_AR_LEDGER_CODE) {
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
													<label ><?=PELL?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PELL" name="PELL[]" multiple class="form-control" >
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
															foreach($PELL_ARR as $PELL){
																if($PELL == $PK_AR_LEDGER_CODE) {
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
													<label ><?=OTHER_FEDERAL_GRANTS?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="OTHER_FEDERAL_GRANTS" name="OTHER_FEDERAL_GRANTS[]" multiple class="form-control" >
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
															foreach($OTHER_FEDERAL_GRANTS_ARR as $OTHER_FEDERAL_GRANTS){
																if($OTHER_FEDERAL_GRANTS == $PK_AR_LEDGER_CODE) {
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
													<label ><?=FEDERAL_STUDENT_LOANS?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="FEDERAL_STUDENT_LOANS" name="FEDERAL_STUDENT_LOANS[]" multiple class="form-control" >
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
															foreach($FEDERAL_STUDENT_LOANS_ARR as $FEDERAL_STUDENT_LOANS){
																if($FEDERAL_STUDENT_LOANS == $PK_AR_LEDGER_CODE) {
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
													<label ><?=OTHER_LOANS?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="OTHER_LOANS" name="OTHER_LOANS[]" multiple class="form-control" >
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
															foreach($OTHER_LOANS_ARR as $OTHER_LOANS){
																if($OTHER_LOANS == $PK_AR_LEDGER_CODE) {
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
													<label ><?=PART_C_GROUP_3?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PART_C_GROUP_3" name="PART_C_GROUP_3[]" multiple class="form-control" >
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
															foreach($PART_C_GROUP_3_ARR as $PART_C_GROUP_3){
																if($PART_C_GROUP_3 == $PK_AR_LEDGER_CODE) {
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
													<label ><?=PART_C_GROUP_4?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PART_C_GROUP_4" name="PART_C_GROUP_4[]" multiple class="form-control" >
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
															foreach($PART_C_GROUP_4_ARR as $PART_C_GROUP_4){
																if($PART_C_GROUP_4 == $PK_AR_LEDGER_CODE) {
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
													<label ><?=PART_D_GROUP_3?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PART_D_GROUP_3" name="PART_D_GROUP_3[]" multiple class="form-control" >
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
															foreach($PART_D_GROUP_3_ARR as $PART_D_GROUP_3){
																if($PART_D_GROUP_3 == $PK_AR_LEDGER_CODE) {
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
													<label ><?=PART_E?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PART_E" name="PART_E[]" multiple class="form-control" >
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
															foreach($PART_E_ARR as $PART_E){
																if($PART_E == $PK_AR_LEDGER_CODE) {
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
													<label ><?=POST_911?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="POST_911" name="POST_911[]" multiple class="form-control" >
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
															foreach($POST_911_ARR as $POST_911){
																if($POST_911 == $PK_AR_LEDGER_CODE) {
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
													<label ><?=DEPARTMENT_OF_DEFENSE?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="DEPARTMENT_OF_DEFENSE" name="DEPARTMENT_OF_DEFENSE[]" multiple class="form-control" >
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
															foreach($DEPARTMENT_OF_DEFENSE_ARR as $DEPARTMENT_OF_DEFENSE){
																if($DEPARTMENT_OF_DEFENSE == $PK_AR_LEDGER_CODE) {
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
													<label ><?=SUBSIDIZED_LOAN_LEDGER_CODES?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="SUB_LOANS" name="SUB_LOANS[]" multiple class="form-control" >
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
															foreach($SUB_LOANS_ARR as $SUB_LOANS){
																if($SUB_LOANS == $PK_AR_LEDGER_CODE) {
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
		$('#EXCLUDED_DROP_REASON').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_DROP_REASON?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDED_DROP_REASON?> selected'
		});
		
		$('#TRANSFER_OUT').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=TRANSFER_OUT?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=TRANSFER_OUT?> selected'
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
		
		$('#LARGEST_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=LARGEST_PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=LARGEST_PROGRAM?> selected'
		});
		
		/////////////////
		$('#PART_A_GROUP_2A').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PART_A_GROUP_2A?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PART_A_GROUP_2A?> selected'
		});
		
		$('#PART_A_GROUP_2B').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PART_A_GROUP_2B?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PART_A_GROUP_2B?> selected'
		});
		
		$('#PART_A_GROUP_3').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PART_A_GROUP_3?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PART_A_GROUP_3?> selected'
		});
		
		$('#PART_B_GROUP_1').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PART_B_GROUP_1?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PART_B_GROUP_1?> selected'
		});
		
		$('#PELL').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PELL?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PELL?> selected'
		});
		
		$('#OTHER_FEDERAL_GRANTS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=OTHER_FEDERAL_GRANTS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=OTHER_FEDERAL_GRANTS?> selected'
		});
		
		$('#FEDERAL_STUDENT_LOANS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FEDERAL_STUDENT_LOANS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=FEDERAL_STUDENT_LOANS?> selected'
		});
		
		$('#OTHER_LOANS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=OTHER_LOANS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=OTHER_LOANS?> selected'
		});
		
		$('#PART_C_GROUP_3').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PART_C_GROUP_3?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PART_C_GROUP_3?> selected'
		});
		
		$('#PART_C_GROUP_4').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PART_C_GROUP_4?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PART_C_GROUP_4?> selected'
		});
		
		$('#PART_D_GROUP_3').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PART_D_GROUP_3?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PART_D_GROUP_3?> selected'
		});
		
		$('#PART_E').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PART_E?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PART_E?> selected'
		});
		
		$('#POST_911').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=POST_911?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=POST_911?> selected'
		});
		
		$('#DEPARTMENT_OF_DEFENSE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=DEPARTMENT_OF_DEFENSE?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=DEPARTMENT_OF_DEFENSE?> selected'
		});
		
		$('#SUB_LOANS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=SUBSIDIZED_LOAN_LEDGER_CODES?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=SUBSIDIZED_LOAN_LEDGER_CODES?> selected'
		});
		
		$('#APPLICANT').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=APPLICANT?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=APPLICANT?> selected'
		});
		
		$('#ADMISSIONS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=ADMISSIONS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=ADMISSIONS?> selected'
		});
		
		/////////////////
		
		// added color for inactive text -  DIAM 22
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