<? 
require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student_job.php");
require_once("check_access.php");

$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');
if($PLACEMENT_ACCESS == 0){
	header("location:../index");
	exit;
}
if(!empty($_POST)) {
	$STUDENT_JOB = $_POST;
	unset($STUDENT_JOB['JOB_NUMBER']);
	
	$STUDENT_JOB['INSTITUTIONAL_EMPLOYMENT']   	= $_POST['INSTITUTIONAL_EMPLOYMENT'];
	$STUDENT_JOB['SELF_EMPLOYED']   			= $_POST['SELF_EMPLOYED'];
	
	$STUDENT_JOB['START_DATE']   		  = ($STUDENT_JOB['START_DATE'] != '' ? date("Y-m-d",strtotime($STUDENT_JOB['START_DATE'])) : '');
	$STUDENT_JOB['END_DATE'] 	 		  = ($STUDENT_JOB['END_DATE'] != '' ? date("Y-m-d",strtotime($STUDENT_JOB['END_DATE'])) : '');
	$STUDENT_JOB['DOCUMENTED']   		  = ($STUDENT_JOB['DOCUMENTED'] != '' ? date("Y-m-d",strtotime($STUDENT_JOB['DOCUMENTED'])) : '');
	$STUDENT_JOB['VERIFICATION_DATE']     = ($STUDENT_JOB['VERIFICATION_DATE'] != '' ? date("Y-m-d",strtotime($STUDENT_JOB['VERIFICATION_DATE'])) : '');
	
	if($STUDENT_JOB['PK_STUDENT_JOB'] == '') {
		$STUDENT_JOB['PK_STUDENT_MASTER']     = $_GET['id'];
		//$STUDENT_JOB['PK_STUDENT_ENROLLMENT'] = $_GET['eid'];
		$STUDENT_JOB['PK_ACCOUNT']  		  = $_SESSION['PK_ACCOUNT'];
		$STUDENT_JOB['CREATED_BY']  		  = $_SESSION['PK_USER'];
		$STUDENT_JOB['CREATED_ON']  		  = date("Y-m-d H:i");
		db_perform('S_STUDENT_JOB', $STUDENT_JOB, 'insert');
		$PK_STUDENT_JOB = $db->insert_ID();
	} else {
		$STUDENT_JOB['EDITED_BY'] = $_SESSION['PK_USER'];
		$STUDENT_JOB['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_STUDENT_JOB', $STUDENT_JOB, 'update'," PK_STUDENT_JOB = '$_GET[jid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[id]' ");
		$PK_STUDENT_JOB = $STUDENT_JOB['PK_STUDENT_JOB'];
	}
	
	if($STUDENT_JOB['CURRENT_JOB'] == 1 && $STUDENT_JOB['PK_PLACEMENT_STATUS'] > 0) {
		$STUDENT_ENROLLMENT['PK_PLACEMENT_STATUS'] = $STUDENT_JOB['PK_PLACEMENT_STATUS'];
		db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_POST[PK_STUDENT_ENROLLMENT]' ");
	}
	
	if($PK_STUDENT_JOB != '' && $STUDENT_JOB['CURRENT_JOB'] == 1) {
		$UPDATE_STUDENT_CURRENT_JOB['CURRENT_JOB'] = 0;
		$UPDATE_STUDENT_CURRENT_JOB['EDITED_BY']   = $_SESSION['PK_USER'];
		$UPDATE_STUDENT_CURRENT_JOB['EDITED_ON']   = date("Y-m-d H:i");
		db_perform('S_STUDENT_JOB', $UPDATE_STUDENT_CURRENT_JOB, 'update'," PK_STUDENT_JOB NOT IN ('$PK_STUDENT_JOB') AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[id]' ");
	}
	
	header("location:student?t=".$_GET['t']."&eid=".$_GET['eid']."&id=".$_GET['id']."&jid=".$PK_STUDENT_JOB."&tab=studentJobsTab");
}
if($_GET['jid'] == '') {
	$res = $db->Execute("SELECT PK_STUDENT_JOB FROM S_STUDENT_JOB WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[id]'  ORDER BY PK_STUDENT_JOB DESC LIMIT 1"); 

	if($res->RecordCount() == 0) 
		$jobNumber			= 1;
	else 
		$jobNumber			= $res->fields['PK_STUDENT_JOB'] + 1;
	
	$PK_PLACEMENT_TYPE 		= '';
	$PK_SOC_CODE		    = '';
	$PK_COMPANY_CONTACT 	= '';
	$PK_PLACEMENT_STATUS 	= '';
	$PK_ENROLLMENT_STATUS 	= '';
	$PK_FULL_PART_TIME		= '';
	$PK_PAY_TYPE 		    = '';
	$JOB_NUMBER 	   		= $jobNumber;
	$COMPANY_PHONE			= '';
	$ADDRESS				= '';
	$ADDRESS_1				= '';
	$CITY					= '';
	$ZIP					= '';
	$SUPERVISOR				= '';
	$NOTES					= '';
	$PK_COMPANY_JOB 		= '';
	$CURRENT_JOB 			= '';
	$JOB_TITLE 				= '';
	$START_DATE				= '';
	$END_DATE				= '';
	$DOCUMENTED 	   		= '';
	$PAY_AMOUNT 			= '';
	$WEEKLY_HOURS 			= '';
	$ANNUAL_SALARY 			= '';
	$VERIFICATION_DATE 		= '';
	$PK_STATES 				= '';
	$ACTIVE  		   		= '';
	$PK_STUDENT_JOB  		= '';
	$PK_COMPANY				= '';
	$REC_TYPE				= 'New';
	$PK_PLACEMENT_VERIFICATION_SOURCE = '';
	
	$INSTITUTIONAL_EMPLOYMENT	= '';
	$SELF_EMPLOYED				= '';
	$PK_STUDENT_ENROLLMENT		= $_GET['eid'];
} else {
	/* Ticket #638 // S_STUDENT_JOB.COMPANY_PHONE => S_COMPANY.PHONE */
	$res = $db->Execute("SELECT S_STUDENT_JOB.INSTITUTIONAL_EMPLOYMENT, S_STUDENT_JOB.SELF_EMPLOYED, S_STUDENT_JOB.PK_STUDENT_JOB, S_STUDENT_JOB.PK_PLACEMENT_TYPE, S_STUDENT_JOB.PK_SOC_CODE, S_STUDENT_JOB.PK_STUDENT_ENROLLMENT, S_STUDENT_JOB.PK_COMPANY_CONTACT, S_STUDENT_JOB.PK_PLACEMENT_STATUS, PK_FULL_PART_TIME, S_STUDENT_JOB.PK_PAY_TYPE, S_STUDENT_JOB.ADDRESS, S_STUDENT_JOB.ADDRESS_1, S_STUDENT_JOB.CITY, S_STUDENT_JOB.PK_STATES, S_STUDENT_JOB.ZIP,COALESCE(NULLIF(S_STUDENT_JOB.COMPANY_PHONE,''),  S_COMPANY.PHONE) AS COMPANY_PHONE, S_STUDENT_JOB.SUPERVISOR, S_STUDENT_JOB.NOTES, S_STUDENT_JOB.PK_COMPANY_JOB,S_STUDENT_JOB.CURRENT_JOB, S_STUDENT_JOB.JOB_TITLE, S_STUDENT_JOB.START_DATE, S_STUDENT_JOB.END_DATE, S_STUDENT_JOB.DOCUMENTED, S_STUDENT_JOB.PAY_AMOUNT ,S_STUDENT_JOB.WEEKLY_HOURS, S_STUDENT_JOB.ANNUAL_SALARY, S_STUDENT_JOB.VERIFICATION_DATE ,S_STUDENT_JOB.PK_COMPANY, S_STUDENT_JOB.ACTIVE, S_STUDENT_JOB.PK_PLACEMENT_VERIFICATION_SOURCE FROM 
	S_STUDENT_JOB 
	LEFT JOIN S_COMPANY_JOB ON S_STUDENT_JOB.PK_COMPANY_JOB = S_COMPANY_JOB.PK_COMPANY_JOB 
	LEFT JOIN S_COMPANY ON S_STUDENT_JOB.PK_COMPANY = S_COMPANY.PK_COMPANY 
	WHERE S_STUDENT_JOB.PK_STUDENT_JOB = '$_GET[jid]' AND S_STUDENT_JOB.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_JOB.PK_STUDENT_MASTER = '$_GET[id]' "); 

	if($res->RecordCount() == 0) {
		header("location:student?t=".$_GET['t']."&eid=".$_GET['eid']."&id=".$_GET['id']."&tab=studentJobsTab");
		exit;
	}

	$PK_PLACEMENT_VERIFICATION_SOURCE = $res->fields['PK_PLACEMENT_VERIFICATION_SOURCE'];
	$PK_STUDENT_JOB  		= $res->fields['PK_STUDENT_JOB'];
	$PK_PLACEMENT_TYPE 		= $res->fields['PK_PLACEMENT_TYPE'];
	$PK_SOC_CODE		    = $res->fields['PK_SOC_CODE'];
	$PK_COMPANY_CONTACT 	= $res->fields['PK_COMPANY_CONTACT'];
	$PK_PLACEMENT_STATUS 	= $res->fields['PK_PLACEMENT_STATUS'];
	$PK_ENROLLMENT_STATUS 	= $res->fields['PK_ENROLLMENT_STATUS'];
	$PK_FULL_PART_TIME		= $res->fields['PK_FULL_PART_TIME'];
	$PK_PAY_TYPE 		    = $res->fields['PK_PAY_TYPE'];
	$JOB_NUMBER 	   		= $res->fields['PK_STUDENT_JOB'];
	$COMPANY_PHONE			= $res->fields['COMPANY_PHONE'];
	$ADDRESS				= $res->fields['ADDRESS'];
	$ADDRESS_1				= $res->fields['ADDRESS_1'];
	$CITY					= $res->fields['CITY'];
	$ZIP					= $res->fields['ZIP'];
	$SUPERVISOR				= $res->fields['SUPERVISOR'];
	$NOTES					= $res->fields['NOTES'];
	$PK_COMPANY_JOB 		= $res->fields['PK_COMPANY_JOB'];
	$CURRENT_JOB 			= $res->fields['CURRENT_JOB'];
	$JOB_TITLE 				= $res->fields['JOB_TITLE'];
	$START_DATE				= $res->fields['START_DATE'];
	$END_DATE				= $res->fields['END_DATE'];
	$DOCUMENTED 	   		= $res->fields['DOCUMENTED'];
	$PAY_AMOUNT 			= $res->fields['PAY_AMOUNT'];
	$WEEKLY_HOURS 			= $res->fields['WEEKLY_HOURS'];
	$ANNUAL_SALARY 			= $res->fields['ANNUAL_SALARY'];
	$VERIFICATION_DATE 		= $res->fields['VERIFICATION_DATE'];
	$PK_STATES 				= $res->fields['PK_STATES'];
	$PK_COMPANY 			= $res->fields['PK_COMPANY'];
	$ACTIVE  		   		= $res->fields['ACTIVE'];
	$REC_TYPE				= 'Exists';
	
	$INSTITUTIONAL_EMPLOYMENT	= $res->fields['INSTITUTIONAL_EMPLOYMENT'];
	$SELF_EMPLOYED				= $res->fields['SELF_EMPLOYED'];
	$PK_STUDENT_ENROLLMENT		= $res->fields['PK_STUDENT_ENROLLMENT'];

	$START_DATE   		= ($START_DATE != '0000-00-00' && $START_DATE != '' ? date("m/d/Y",strtotime($START_DATE)) : '');
	$END_DATE     		= ($END_DATE != '0000-00-00' && $END_DATE != '' ? date("m/d/Y",strtotime($END_DATE)) : '');
	$DOCUMENTED   		= ($DOCUMENTED != '0000-00-00' && $DOCUMENTED != '' ? date("m/d/Y",strtotime($DOCUMENTED)) : '');
	$VERIFICATION_DATE  = ($VERIFICATION_DATE != '0000-00-00' && $VERIFICATION_DATE != '' ? date("m/d/Y",strtotime($VERIFICATION_DATE)) : '');
}
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
	<title><?=STUDENT_JOB_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=STUDENT_JOB_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<input type="hidden" class="form-control" id="PK_STUDENT_JOB" name="PK_STUDENT_JOB" value="<?=$PK_STUDENT_JOB?>" >
									<div class="d-flex flex-wrap">
										<!-- Ticket #1186 -->
										<div class="col-2">
											<div class="form-group m-b-40">
												<input type="text" tabindex="1" readonly class="form-control required-entry" id="JOB_NUMBER" name="JOB_NUMBER" value="<?=$JOB_NUMBER?>" >
												<span class="bar"></span>
												<label for="JOB_NUMBER"><?=JOB_NUMBER?></label>
											</div>
										</div>
										<div class="col-2">
											<button type="button" onclick="mark_job_as_filled()"  class="btn waves-effect waves-light btn-info"><?=MARK_JOB_AS_FILLED?></button>
										</div>
										<!-- Ticket #1186 -->
										
										<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
											<input type="checkbox" class="custom-control-input" id="SELF_EMPLOYED" name="SELF_EMPLOYED" value="1" <? if($SELF_EMPLOYED == 1) echo "checked"; ?>  >
											<label class="custom-control-label" for="SELF_EMPLOYED" ><?=SELF_EMPLOYED ?></label>
										</div>
										
										<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
											<input type="checkbox" class="custom-control-input" id="INSTITUTIONAL_EMPLOYMENT" name="INSTITUTIONAL_EMPLOYMENT" value="1" <? if($INSTITUTIONAL_EMPLOYMENT == 1) echo "checked"; ?>  >
											<label class="custom-control-label" for="INSTITUTIONAL_EMPLOYMENT" ><?=INSTITUTIONAL_EMPLOYMENT ?></label>
										</div>
										
									</div>
									<div class="d-flex flex-wrap">
										<div class="col-12 col-md-4">
											<div class="row">
												<div class="col-12 focused">
													<div class="form-group m-b-40">
														<select id="PK_COMPANY" tabindex="2" name="PK_COMPANY" class="form-control" style="width:100%;" onchange="get_company_info(this.value)">
															<option></option>
															<? /* Ticket # 1694  */
															$res_type = $db->Execute("select S_COMPANY.PK_COMPANY, COMPANY_NAME, COUNT(S_COMPANY_JOB.PK_COMPANY) AS TOTAL_JOBS, S_COMPANY.ACTIVE from S_COMPANY LEFT JOIN S_COMPANY_JOB ON S_COMPANY_JOB.PK_COMPANY = S_COMPANY.PK_COMPANY WHERE S_COMPANY.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' GROUP BY S_COMPANY.PK_COMPANY order by S_COMPANY.ACTIVE DESC, COMPANY_NAME ASC");
															while (!$res_type->EOF) { 
																$PK_COMPANY1 = $res_type->fields['PK_COMPANY'];
																$com_jobs = $db->Execute("SELECT COUNT(PK_COMPANY_JOB) AS OPEN_JOBS FROM S_COMPANY_JOB WHERE JOB_CANCELED = '0000-00-00' AND JOB_FILLED = '0000-00-00' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$PK_COMPANY1' AND ACTIVE = '1' "); 
																
																$option_label = $res_type->fields['COMPANY_NAME'].' (Open Jobs: '.$com_jobs->fields['OPEN_JOBS'].' &nbsp;&nbsp;&nbsp;Total Jobs: '.$res_type->fields['TOTAL_JOBS'].')';
																if($res_type->fields['ACTIVE'] == 0)
																	$option_label .= " (Inactive)"; ?>
																<option value="<?=$res_type->fields['PK_COMPANY']?>" <? if($PK_COMPANY == $res_type->fields['PK_COMPANY']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
															<?	$res_type->MoveNext();
															}  /* Ticket # 1694  */ ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_COMPANY"><?=COMPANY_NAME?></label>
													</div>
												</div>
												<div class="col-12 focused">
													<div class="form-group m-b-40">
														<select id="PK_COMPANY_JOB" tabindex="3" name="PK_COMPANY_JOB" class="form-control" style="width:100%;" onchange="get_job_info(this.value,'<?=$REC_TYPE?>')">
															<option></option>
															<? /* Ticket #1149  */
															$act_type_cond = " AND ACTIVE = 1 ";
															if($PK_COMPANY_JOB > 0)
																$act_type_cond = " AND (ACTIVE = 1 OR PK_COMPANY_JOB = '$PK_COMPANY_JOB' ) ";
																
															$res_type = $db->Execute("SELECT PK_COMPANY_JOB,JOB_TITLE,PK_PLACEMENT_TYPE,JOB_NUMBER FROM S_COMPANY_JOB WHERE JOB_CANCELED = '0000-00-00' AND JOB_FILLED = '0000-00-00' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '".$PK_COMPANY."' $act_type_cond ORDER BY JOB_TITLE ASC");															
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_COMPANY_JOB']?>" <? if($PK_COMPANY_JOB == $res_type->fields['PK_COMPANY_JOB']) echo "selected"; ?> ><?=$res_type->fields['JOB_TITLE'] ?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span>
														<label for="PK_COMPANY_JOB"><?=POSTED_JOB?></label>
													</div>
												</div>
												<div class="col-12 focused">
													<div class="form-group m-b-40">
														<input id="COMPANY_PHONE" tabindex="5" readonly name="COMPANY_PHONE" tabindex="2" type="text" class="form-control phone-inputmask " value="<?=$COMPANY_PHONE?>">
														<span class="bar"></span> 
														<label for="COMPANY_PHONE"><?=COMPANY_PHONE?></label>
													</div>
												</div>
												<div class="col-12 focused">
													<div class="form-group m-b-40" id="ADDRESS_LABEL">
														<input id="ADDRESS" tabindex="6" readonly name="ADDRESS" type="text" class="form-control " value="<?=$ADDRESS?>">
														<span class="bar"></span>
														<label for="ADDRESS"><?=ADDRESS?></label>
													</div>
												</div>
												<div class="col-12 focused">
													<div class="form-group m-b-40" id="ADDRESS_1_LABEL">
														<input id="ADDRESS_1" tabindex="7" readonly name="ADDRESS_1" type="text" class="form-control " value="<?=$ADDRESS_1?>">
														<span class="bar"></span>
														<label for="ADDRESS_1"><?=ADDRESS_1?></label>
													</div>
												</div>
												<div class="d-flex flex-wrap">
													<div class="col-6 focused">
														<div class="form-group m-b-40" id="CITY_LABEL">
															<input id="CITY" tabindex="8" readonly name="CITY" type="text" class="form-control " value="<?=$CITY?>">
															<span class="bar"></span> 
															<label for="CITY"><?=CITY?></label>
														</div>
													</div>
													<div class="col-3 focused">
														<div class="form-group m-b-40" id="PK_STATES_LABEL">
															<select id="PK_STATES" tabindex="9" name="PK_STATES" readonly class="form-control" onchange="get_country(this.value,'PK_COUNTRY')" >
																<option selected></option>
																	<? $res_type = $db->Execute("select PK_STATES, STATE_NAME from Z_STATES WHERE ACTIVE = '1' ORDER BY STATE_NAME ASC ");
																while (!$res_type->EOF) { ?>
																	<option value="<?=$res_type->fields['PK_STATES'] ?>" <? if($PK_STATES == $res_type->fields['PK_STATES']) echo "selected"; ?> ><?=$res_type->fields['STATE_NAME']?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
															<span class="bar"></span> 
															<label for="PK_STATES"><?=STATE?></label>
														</div>
													</div>
													<div class="col-3 focused">
														<div class="form-group m-b-40" id="ZIP_LABEL">
															<input id="ZIP" tabindex="10" name="ZIP" readonly type="text" class="form-control" value="<?=$ZIP?>">
															<span class="bar"></span> 
															<label for="ZIP"><?=ZIP?></label>
														</div>
													</div>
												</div>
												<div class="col-12 focused">
													<div class="form-group m-b-40">
														<select readonly id="PK_COMPANY_CONTACT" tabindex="11" name="PK_COMPANY_CONTACT" class="form-control" >
															<option selected></option>
															<? /* Ticket #1149  */
															$act_type_cond = " AND ACTIVE = 1 ";
															if($PK_COMPANY_CONTACT > 0)
																$act_type_cond = " AND (ACTIVE = 1 OR PK_COMPANY_CONTACT = '$PK_COMPANY_CONTACT' ) ";
																
															$res_type = $db->Execute("select PK_COMPANY_CONTACT, NAME from S_COMPANY_CONTACT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$PK_COMPANY' $act_type_cond ORDER BY NAME ASC ");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_COMPANY_CONTACT'] ?>" <? if($PK_COMPANY_CONTACT == $res_type->fields['PK_COMPANY_CONTACT']) echo "selected"; ?> ><?=$res_type->fields['NAME']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_COMPANY_CONTACT"><?=CONTACT?></label>
													</div>
												</div>
												<div class="col-12">
													<div class="form-group m-b-40" id="ADDRESS_LABEL">
														<input id="SUPERVISOR" tabindex="12" name="SUPERVISOR" type="text" class="form-control" value="<?=$SUPERVISOR?>">
														<span class="bar"></span> 
														<label for="SUPERVISOR"><?=SUPERVISOR?></label>
													</div>
												</div>
												<div class="col-12">
													<div class="form-group m-b-40">
														<textarea class="form-control rich" tabindex="30" id="NOTES" name="NOTES" rows="3"><?=$NOTES?></textarea>
														<span class="bar"></span> 
														<label for="NOTES"><?=NOTES?></label>
													</div>
												</div>
											</div>
										</div>

										<div class="col-12 col-md-4">
											<div class="row">
												<div class="col-12 focused">
													<div class="form-group m-b-40">
														<select id="PK_PLACEMENT_TYPE" readonly tabindex="13" name="PK_PLACEMENT_TYPE" class="form-control">
															<option selected></option>
															<? /* Ticket #1694  */
															$res_type = $db->Execute("select PK_PLACEMENT_TYPE,CONCAT(TYPE,' - ',DESCRIPTION) as  TYPE, ACTIVE from M_PLACEMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, TYPE ASC ");
															while (!$res_type->EOF) { 
																$option_label = $res_type->fields['TYPE'];
																if($res_type->fields['ACTIVE'] == 0)
																	$option_label .= " (Inactive)"; ?>
																<option value="<?=$res_type->fields['PK_PLACEMENT_TYPE'] ?>" <? if($PK_PLACEMENT_TYPE == $res_type->fields['PK_PLACEMENT_TYPE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
															<?	$res_type->MoveNext();
															} 
															/* Ticket #1694  */?>
														</select>
														<span class="bar"></span> 
														<label for="PK_PLACEMENT_TYPE"><?=PLACEMENT_TYPE?></label>
													</div>
												</div>
												<div class="col-12 focused">
													<div class="form-group m-b-40">
														<input type="text" tabindex="14" readonly class="form-control " id="JOB_TITLE" name="JOB_TITLE" value="<?=$JOB_TITLE?>" >
														<span class="bar"></span>
														<label for="JOB_TITLE"><?=JOB_TITLE?></label>
													</div>
												</div>
												<div class="d-flex flex-wrap">
													<div class="col-6">
														<div class="form-group m-b-40">
															<input type="text" tabindex="15" class="form-control date date-inputmask" id="START_DATE" name="START_DATE" value="<?=$START_DATE?>" >
															<span class="bar"></span>
															<label for="START_DATE"><?=START_DATE?></label>
														</div>
													</div>
													<div class="col-6">
														<div class="form-group m-b-40">
															<input type="text" tabindex="16" class="form-control date date-inputmask" id="END_DATE" name="END_DATE" value="<?=$END_DATE?>" >
															<span class="bar"></span>
															<label for="END_DATE"><?=END_DATE?></label>
														</div>
													</div>
												</div>
												<div class="col-12 focused">
													<div class="form-group m-b-40">
														<select readonly id="PK_FULL_PART_TIME" tabindex="17" name="PK_FULL_PART_TIME" class="form-control">
															<option selected></option>
															<? /*$res_type = $db->Execute("select PK_ENROLLMENT_STATUS, CODE, DESCRIPTION from M_ENROLLMENT_STATUS WHERE ACTIVE = '1' ORDER BY DESCRIPTION ASC ");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_ENROLLMENT_STATUS'] ?>" <? if($PK_ENROLLMENT_STATUS == $res_type->fields['PK_ENROLLMENT_STATUS']) echo "selected"; ?> ><?=$res_type->fields['DESCRIPTION']?></option>
															<?	$res_type->MoveNext();
															} */?>
															<option value="1" <? if($PK_FULL_PART_TIME== 1) echo "selected"; ?>>Full Time</option>
															<option value="2" <? if($PK_FULL_PART_TIME== 2) echo "selected"; ?>>Part Time</option>
														</select>
														<span class="bar"></span>
														<label for="PK_FULL_PART_TIME"><?=ENROLLMENT_STATUS?></label>
													</div>
												</div>
												<div class="col-12 focused">
													<div class="form-group m-b-40">
													<select readonly id="PK_PAY_TYPE" tabindex="18" name="PK_PAY_TYPE" class="form-control" >
														<option selected></option>
														<? /* Ticket #1149  */
														$act_type_cond = " AND ACTIVE = 1 ";
														if($PK_PAY_TYPE > 0)
															$act_type_cond = " AND (ACTIVE = 1 OR PK_PAY_TYPE = '$PK_PAY_TYPE' ) ";
															
														$res_type = $db->Execute("select PK_PAY_TYPE,PAY_TYPE FROM M_PAY_TYPE WHERE 1 = 1 $act_type_cond order by PAY_TYPE ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_PAY_TYPE'] ?>" <? if($PK_PAY_TYPE == $res_type->fields['PK_PAY_TYPE']) echo "selected"; ?> ><?=$res_type->fields['PAY_TYPE']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
													<span class="bar"></span>
													<label for="PK_PAY_TYPE"><?=PAY_TYPE?></label>
													</div>
												</div>
												<div class="col-12 focused">
													<div class="input-group m-b-40">
														<div class="input-group-prepend">
															<span class="input-group-text" style="padding: 5px 5px;height: 38px;" >$</span>
														</div>
														<input type="number" min="0" step=".01" tabindex="19" class="form-control" id="PAY_AMOUNT" name="PAY_AMOUNT" value="<?=$PAY_AMOUNT?>" style="text-align:right" onblur="calc_estimated_fee(this.value,'PAY_AMOUNT')">
														<span class="bar"></span>
														<label for="PAY_AMOUNT"><?=PAY_AMOUNT?></label>
													</div>
												</div>
												<div class="col-12 focused">
													<div class="form-group m-b-40">
														<input type="number" min="0" tabindex="20" class="form-control " id="WEEKLY_HOURS" name="WEEKLY_HOURS" value="<?=$WEEKLY_HOURS?>" >
														<span class="bar"></span>
														<label for="WEEKLY_HOURS"><?=WEEKLY_HOURS?></label>
													</div>
												</div>
												<div class="col-12 focused">
													<div class="input-group m-b-40">
														<div class="input-group-prepend">
															<span class="input-group-text" style="padding: 5px 5px;height: 38px;" >$</span>
														</div>
														<input type="number" min="0" tabindex="21" class="form-control" id="ANNUAL_SALARY" name="ANNUAL_SALARY" value="<?=$ANNUAL_SALARY?>" style="text-align:right"  onblur="calc_estimated_fee(this.value,'ANNUAL_SALARY')">
														<span class="bar"></span>
														<label for="ANNUAL_SALARY"><?=ANNUAL_SALARY?></label>
													</div>
												</div>
												<? /* Ticket # 1714
												if($_GET['jid'] != ''){ ?>
												<div class="col-12">
													<div class="row form-group">
														<div class="custom-control col-md-4 mt-2"><?=ACTIVE?></div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" tabindex="31" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="customRadio11">Yes</label>
														</div>
														<div class="custom-control custom-radio col-md-1">
															<input type="radio" tabindex="32" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
															<label class="custom-control-label ml-2" for="customRadio22">No</label>
														</div>
													</div>
												</div>
												<? } Ticket # 1714 */?>
											</div>
										</div>

										<div class="col-12 col-md-4">
											<div class="row">
												<div class="col-12">
													<div class="form-group m-b-40">
														<select id="CURRENT_JOB" tabindex="22" name="CURRENT_JOB" class="form-control">
															<option selected></option>
															<option value="1" <? if($CURRENT_JOB == 1) echo "selected"; ?>>Yes</option>
															<option value="0" <? if($CURRENT_JOB == 0) echo "selected"; ?>>No</option>
														</select>	
														<span class="bar"></span> 
														<label for="CURRENT_JOB"><?=CURRENT_JOB?></label>
													</div>
												</div>
												<div class="col-12">
													<div class="form-group m-b-40">
														<select id="PK_STUDENT_ENROLLMENT" tabindex="23" name="PK_STUDENT_ENROLLMENT" class="form-control">
															<? /* Ticket #1694  */
															$res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, CAMPUS_CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" <? if($res_type->fields['PK_STUDENT_ENROLLMENT'] == $PK_STUDENT_ENROLLMENT) echo "selected"; ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['CAMPUS_CODE']?></option>
															<?	$res_type->MoveNext();
															} /* Ticket #1694  */?>
														</select>
														<span class="bar"></span> 
														<label for="PK_STUDENT_ENROLLMENT"><?=ENROLLMENT?></label>
													</div>
												</div>
												<div class="col-12">
													<div class="form-group m-b-40">
														<select id="PK_PLACEMENT_STATUS" tabindex="24" name="PK_PLACEMENT_STATUS" class="form-control">
															<option value=""></option>
															<? /* Ticket #1694  */
															$res_type = $db->Execute("select PK_PLACEMENT_STATUS, CONCAT(PLACEMENT_STATUS, ' - ', PLACEMENT_STUDENT_STATUS_CATEGORY) as  PLACEMENT_STATUS, M_PLACEMENT_STATUS.ACTIVE from M_PLACEMENT_STATUS LEFT JOIN M_PLACEMENT_STUDENT_STATUS_CATEGORY ON M_PLACEMENT_STUDENT_STATUS_CATEGORY.PK_PLACEMENT_STUDENT_STATUS_CATEGORY = M_PLACEMENT_STATUS.PK_PLACEMENT_STUDENT_STATUS_CATEGORY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by M_PLACEMENT_STATUS.ACTIVE DESC, PLACEMENT_STATUS ASC");
															while (!$res_type->EOF) { 
																$option_label = $res_type->fields['PLACEMENT_STATUS'];
																if($res_type->fields['ACTIVE'] == 0)
																	$option_label .= " (Inactive)"; ?>
																<option value="<?=$res_type->fields['PK_PLACEMENT_STATUS']?>" <? if($PK_PLACEMENT_STATUS == $res_type->fields['PK_PLACEMENT_STATUS']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
															<?	$res_type->MoveNext();
															} /* Ticket #1694  */ ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_PLACEMENT_STATUS"><?=PLACEMENT_JOB_STATUS?></label><!-- Ticket # 1714 -->
													</div>
												</div>
												<div class="col-12">
													<div class="form-group m-b-40">
														<input type="text" tabindex="26" class="form-control date date-inputmask" id="DOCUMENTED" name="DOCUMENTED" value="<?=$DOCUMENTED?>" >
														<span class="bar"></span>
														<label for="DOCUMENTED"><?=DOCUMENTED?></label>
													</div>
												</div>
												<div class="col-12">
													<div class="form-group m-b-40">
														<select id="PK_PLACEMENT_VERIFICATION_SOURCE" tabindex="27" name="PK_PLACEMENT_VERIFICATION_SOURCE" class="form-control">
															<option value=""></option>
															<? /* Ticket #1694  */
															$res_type = $db->Execute("select PK_PLACEMENT_VERIFICATION_SOURCE, CONCAT(VERIFICATION_SOURCE, ' - ', DESCRIPTION) as VERIFICATION_SOURCE, ACTIVE from M_PLACEMENT_VERIFICATION_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC,  VERIFICATION_SOURCE ASC");
															while (!$res_type->EOF) { 
																$option_label = $res_type->fields['VERIFICATION_SOURCE'];
																if($res_type->fields['ACTIVE'] == 0)
																	$option_label .= " (Inactive)"; ?>
																<option value="<?=$res_type->fields['PK_PLACEMENT_VERIFICATION_SOURCE']?>" <? if($PK_PLACEMENT_VERIFICATION_SOURCE == $res_type->fields['PK_PLACEMENT_VERIFICATION_SOURCE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
															<?	$res_type->MoveNext();
															} /* Ticket #1694  */ ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_PLACEMENT_VERIFICATION_SOURCE"><?=VERIFICATION_SOURCE?></label>
													</div>
												</div>
												<div class="col-12">
													<div class="form-group m-b-40">
														<input type="text" tabindex="28" class="form-control date date-inputmask" id="VERIFICATION_DATE" name="VERIFICATION_DATE" value="<?=$VERIFICATION_DATE?>" >
														<span class="bar"></span>
														<label for="VERIFICATION_DATE"><?=VERIFICATION_DATE?></label>
													</div>
												</div>
												<div class="col-12 focused">
													<div class="form-group m-b-40">
														<select readonly id="PK_SOC_CODE" tabindex="29" name="PK_SOC_CODE" class="form-control">
															<option selected></option>
															<? /* Ticket #1694  */
															$res_type = $db->Execute("select PK_SOC_CODE, CONCAT(SOC_CODE, ' - ', SOC_TITLE) as SOC_CODE, ACTIVE from M_SOC_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, SOC_CODE ASC ");
															while (!$res_type->EOF) { 
																$option_label = $res_type->fields['SOC_CODE'];
																if($res_type->fields['ACTIVE'] == 0)
																	$option_label .= " (Inactive)"; ?>
																<option value="<?=$res_type->fields['PK_SOC_CODE'] ?>" <? if($PK_SOC_CODE == $res_type->fields['PK_SOC_CODE']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label?></option>
															<?	$res_type->MoveNext();
															} /* Ticket #1694  */ ?>
														</select>
														<span class="bar"></span>
														<label for="PK_SOC_CODE"><?=SOC_CODE?></label>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									<div class="row">
                                        <div class="col-md-7 submit-button-sec">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" tabindex="33" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												<button type="button" tabindex="34" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='student?t=<?=$_GET['t']?>&eid=<?=$_GET['eid']?>&id=<?=$_GET['id']?>&tab=studentJobsTab'" ><?=CANCEL?></button>
											</div>
										</div>
									</div>
                                </form>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
			
			<!--Ticket # 1186-->
			<div class="modal" id="MARK_JOB_AS_FILLED_POP" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
				<div class="modal-dialog" role="document">
					<form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title" id="exampleModalLabel1"><?=MARK_JOB_AS_FILLED?></h4>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							</div>
							<div class="modal-body">
								<div class="row form-group">
									<div class="col-md-4 align-self-center"><?=JOB_FILLED_DATE?></div>
									<div class="col-md-8 align-self-center">
										<input type="text" id="JOB_FILLED_DATE" name="JOB_FILLED_DATE" value="" class="form-control date required-enty">
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" onclick="confirm_mark_job_as_filled(1)" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
								<button type="button" class="btn waves-effect waves-light btn-dark" onclick="confirm_mark_job_as_filled(0)" ><?=CANCEL?></button>
							</div>
						</div>
					</form>
				</div>
			</div>
			<!--Ticket # 1186-->
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
	<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($) { 
			$(window).keydown(function(event){
				if(event.keyCode == 13) {
					event.preventDefault();
					return false;
				}
			});
		});		
											
		jQuery(document).ready(function($) { 
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
		});

		function calc_estimated_fee(val,id) {
			var resValue = 0;
			if (Number.isNaN(Number.parseFloat(val)))
				resValue = 0;
			else
				resValue = parseFloat(val);

			document.getElementById(id).value = resValue.toFixed(2);
		}

		function get_country(val,id){
			jQuery(document).ready(function($) { 
				var data  = 'state='+val+'&id='+id;
				var value = $.ajax({
					url: "ajax_get_country_from_state.php",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById(id).innerHTML = data;
						document.getElementById('PK_COUNTRY_LABEL').classList.add("focused");
						
					}		
				}).responseText;
			});
		}

		function get_job_info(id,recType) {
			jQuery(document).ready(function($) {
				if(id != '' && recType == 'New') {
					document.getElementById('CURRENT_JOB').selectedIndex = 1;
				}

				var cid   = document.getElementById('PK_COMPANY').value;
				var data  = 'id='+id+'&cid='+cid;
				var value = $.ajax({
					url: "ajax_get_company_job_info.php",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {
						if(data != '') {
							var dataRes = JSON.parse(data);
							if(dataRes['JOB_TITLE'] != '')
								document.getElementById('JOB_TITLE').value = dataRes['JOB_TITLE'];
							else 
								document.getElementById('JOB_TITLE').value = '';

							if(dataRes['PK_PLACEMENT_TYPE'] != '')
								document.getElementById('PK_PLACEMENT_TYPE').selectedIndex = dataRes['PK_PLACEMENT_TYPE'];
							else 
								document.getElementById('PK_PLACEMENT_TYPE').selectedIndex = '';
							
							if(dataRes['PK_COMPANY_CONTACT'] != '')
								document.getElementById('PK_COMPANY_CONTACT').value = dataRes['PK_COMPANY_CONTACT'];
							else 
								document.getElementById('PK_COMPANY_CONTACT').value = '';
							
							if(dataRes['PK_ENROLLMENT_STATUS'] != '')
								document.getElementById('PK_FULL_PART_TIME').selectedIndex = dataRes['PK_ENROLLMENT_STATUS'];
							else 
								document.getElementById('PK_FULL_PART_TIME').selectedIndex = '';
							
							if(dataRes['PK_PAY_TYPE'] != '')
								document.getElementById('PK_PAY_TYPE').selectedIndex = dataRes['PK_PAY_TYPE'];
							else 
								document.getElementById('PK_PAY_TYPE').selectedIndex = '';
							
							if(dataRes['PAY_AMOUNT'] != '')
								document.getElementById('PAY_AMOUNT').value = dataRes['PAY_AMOUNT'];
							else 
								document.getElementById('PAY_AMOUNT').value = '';
							
							if(dataRes['WEEKLY_HOURS'] != '')
								document.getElementById('WEEKLY_HOURS').value = dataRes['WEEKLY_HOURS'];
							else 
								document.getElementById('WEEKLY_HOURS').value = '';
							
							if(dataRes['PK_SOC_CODE'] != '')
								document.getElementById('PK_SOC_CODE').selectedIndex = dataRes['PK_SOC_CODE'];
							else 
								document.getElementById('PK_SOC_CODE').selectedIndex = '';

							if(dataRes['ANNUAL_SALARY'] != '')
								document.getElementById('ANNUAL_SALARY').value = dataRes['ANNUAL_SALARY'];
							else 
								document.getElementById('ANNUAL_SALARY').value = '';
								
							if(dataRes['PK_PLACEMENT_STATUS'] != '') {
								document.getElementById('PK_PLACEMENT_STATUS').value = dataRes['PK_PLACEMENT_STATUS'];
								$("#PK_PLACEMENT_STATUS").parent().addClass("focused")
							} else {
								document.getElementById('PK_PLACEMENT_STATUS').value = '';
								$("#PK_PLACEMENT_STATUS").parent().removeClass("focused")
							}
							
							if(dataRes['INSTITUTIONAL_EMPLOYMENT'] == 1)
								document.getElementById('INSTITUTIONAL_EMPLOYMENT').checked = true
							else
								document.getElementById('INSTITUTIONAL_EMPLOYMENT').checked = false
								
							if(dataRes['SELF_EMPLOYED'] == 1)
								document.getElementById('SELF_EMPLOYED').checked = true
							else
								document.getElementById('SELF_EMPLOYED').checked = false
						}
					}		
				}).responseText;
			});
		}

		function get_company_info(id) {
			jQuery(document).ready(function($) { 
				var data  = 'id='+id;
				var value = $.ajax({
					url: "ajax_get_company_contact.php",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						if(data != '') {
							var res = data.split("~!~");
							document.getElementById('PK_COMPANY_JOB').innerHTML = res[0];
							document.getElementById('PK_COMPANY_CONTACT').innerHTML = res[1];
						}
					}		
				}).responseText;
			});

			jQuery(document).ready(function($) { 
				var data  = 'id='+id;
				var value = $.ajax({
					url: "ajax_get_company_info.php",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						if(data != '') {
							var dataRes = JSON.parse(data);
							if(dataRes['ADDRESS'] != '')
								document.getElementById('ADDRESS').value = dataRes['ADDRESS'];
							else 
								document.getElementById('ADDRESS').value = '';

							if(dataRes['CITY'] != '')
								document.getElementById('CITY').value = dataRes['CITY'];
							else 
								document.getElementById('CITY').value = '';

							if(dataRes['PK_STATES'] != '')
								document.getElementById('PK_STATES').value = dataRes['PK_STATES'];
							else 
								document.getElementById('PK_STATES').value = '';

							if(dataRes['ZIP'] != '')
								document.getElementById('ZIP').value = dataRes['ZIP'];
							else 
								document.getElementById('ZIP').value = '';

							if(dataRes['PHONE'] != '')
								document.getElementById('COMPANY_PHONE').value = dataRes['PHONE'];
							else
								document.getElementById('COMPANY_PHONE').value = '';	

							if(dataRes['ADDRESS_1'] != '')
								document.getElementById('ADDRESS_1').value = dataRes['ADDRESS_1'];
							else 
								document.getElementById('ADDRESS_1').value = '';
						}
					}		
				}).responseText;
			});
		}
		
		/* Ticket # 1186 */
		function mark_job_as_filled(){
			jQuery(document).ready(function($) {
				$("#MARK_JOB_AS_FILLED_POP").modal()
			});
		}
		function confirm_mark_job_as_filled(val){
			jQuery(document).ready(function($) {
				if(val == 1){
					if(document.getElementById('JOB_FILLED_DATE').value == ''){
						alert("Please Enter Date");
					} else if(document.getElementById('PK_COMPANY_JOB').value == ''){
						alert("Please Select Posted Job");
					} else {
						var data  = 'date='+document.getElementById('JOB_FILLED_DATE').value+'&PK_COMPANY_JOB='+document.getElementById('PK_COMPANY_JOB').value;
						var value = $.ajax({
							url: "ajax_update_job_filled_date",
							type: "POST",		 
							data: data,		
							async: false,
							cache: false,
							success: function (data) {	
								alert("Updated");
							}		
						}).responseText;
					}
				}
				$("#MARK_JOB_AS_FILLED_POP").modal("hide");
			});
		}
		/* Ticket # 1186 */
	</script>
	
	<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
	<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#PK_COMPANY').select2({
				placeholder: "Select",
			});
		});
	</script>
	
</body>

</html>
