<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/company_job.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$PK_CAMPUS_ARR	= $_POST['PK_CAMPUS'];
	unset($_POST['PK_CAMPUS']);
	
	$COMPANY_JOB = $_POST;
	$COMPANY_JOB['INSTITUTIONAL_EMPLOYMENT']   	= $_POST['INSTITUTIONAL_EMPLOYMENT'];
	$COMPANY_JOB['SELF_EMPLOYED']   			= $_POST['SELF_EMPLOYED'];
	$COMPANY_JOB['JOB_POSTED']   = ($COMPANY_JOB['JOB_POSTED'] != '' ? date("Y-m-d",strtotime($COMPANY_JOB['JOB_POSTED'])) : '');
	$COMPANY_JOB['JOB_FILLED']   = ($COMPANY_JOB['JOB_FILLED'] != '' ? date("Y-m-d",strtotime($COMPANY_JOB['JOB_FILLED'])) : '');
	$COMPANY_JOB['JOB_CANCELED'] = ($COMPANY_JOB['JOB_CANCELED'] != '' ? date("Y-m-d",strtotime($COMPANY_JOB['JOB_CANCELED'])) : '');
	$COMPANY_JOB['OPEN_JOB'] 	 = (($COMPANY_JOB['JOB_FILLED'] == '' && $COMPANY_JOB['JOB_CANCELED'] == '') ? 'Y' : 'N');

	if($_GET['id'] == '') {
		$COMPANY_JOB['PK_COMPANY']  = $_GET['cid'];
		$COMPANY_JOB['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$COMPANY_JOB['CREATED_BY']  = $_SESSION['PK_USER'];
		$COMPANY_JOB['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_COMPANY_JOB', $COMPANY_JOB, 'insert');
		
		$PK_COMPANY_JOB = $db->insert_ID();
	} else {
		$COMPANY_JOB['EDITED_BY'] = $_SESSION['PK_USER'];
		$COMPANY_JOB['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_COMPANY_JOB', $COMPANY_JOB, 'update'," PK_COMPANY_JOB = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$_GET[cid]'");
		$PK_COMPANY_JOB = $_GET['id'];
	}
	
	foreach($PK_CAMPUS_ARR as $PK_CAMPUS){
		$res = $db->Execute("SELECT PK_COMPANY_JOB_CAMPUS FROM S_COMPANY_JOB_CAMPUS WHERE PK_COMPANY_JOB = '$PK_COMPANY_JOB' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$PK_CAMPUS' "); 
		if($res->RecordCount() == 0) {
			$COMPANY_JOB_CAMPUS['PK_ACCOUNT'] 		= $_SESSION['PK_ACCOUNT'];
			$COMPANY_JOB_CAMPUS['PK_COMPANY_JOB']	= $PK_COMPANY_JOB;
			$COMPANY_JOB_CAMPUS['PK_CAMPUS'] 		= $PK_CAMPUS;
			$COMPANY_JOB_CAMPUS['CREATED_BY']  		= $_SESSION['PK_USER'];
			$COMPANY_JOB_CAMPUS['CREATED_ON'] 		= date("Y-m-d H:i");
			db_perform('S_COMPANY_JOB_CAMPUS', $COMPANY_JOB_CAMPUS, 'insert');
			$PK_COMPANY_JOB_CAMPUS_ARR[] = $db->insert_ID();
		} else
			$PK_COMPANY_JOB_CAMPUS_ARR[] = $res->fields['PK_COMPANY_JOB_CAMPUS'];
	}
	
	$cond = "";
	if(!empty($PK_COMPANY_JOB_CAMPUS_ARR))
		$cond = " AND PK_COMPANY_JOB_CAMPUS NOT IN (".implode(",",$PK_COMPANY_JOB_CAMPUS_ARR).") ";
		
	$db->Execute("DELETE FROM S_COMPANY_JOB_CAMPUS WHERE PK_COMPANY_JOB = '$PK_COMPANY_JOB' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond"); 
	
	header("location:company?id=".$_GET['cid']."&tab=jobsTab");
}
if($_GET['id'] == '') {
	$res = $db->Execute("SELECT JOB_NUMBER FROM S_COMPANY_JOB WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$_GET[cid]' ORDER BY PK_COMPANY_JOB DESC LIMIT 1"); 

	if($res->RecordCount() == 0) 
		$jobNumber			= 1;
	else 
		$jobNumber			= $res->fields['JOB_NUMBER'] + 1;

	$PK_PLACEMENT_TYPE 		= '';
	$PK_SOC_CODE		    = '';
	$PK_COMPANY_CONTACT 	= '';
	$PK_ENROLLMENT_STATUS 	= '';
	$PK_PAY_TYPE 		    = '';
	$PK_COMPANY_ADVISOR 	= '';
	$JOB_NUMBER 	   		= $jobNumber;
	$JOB_TITLE 		   		= '';
	$JOB_POSTED 			= '';
	$JOB_FILLED 			= '';
	$JOB_CANCELED 	   		= '';
	$EMPLOYMENT 		    = '';
	$BENEFITS 			    = '';
	$PAY_AMOUNT 			= '';
	$ANNUAL_SALARY 			= '';
	$WEEKLY_HOURS 			= '';
	$JOB_DESCRIPTION 		= '';
	$JOB_NOTES 			    = '';
	$ACTIVE  		   		= '';
	$PK_PLACEMENT_STATUS	= '';
	
	$INSTITUTIONAL_EMPLOYMENT	= '';
	$SELF_EMPLOYED				= '';
	
	$COMPANY_JOB_CAMPUS_ARR = array();
	$res = $db->Execute("select PK_CAMPUS FROM S_COMPANY_CAMPUS WHERE PK_COMPANY = '$_GET[cid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	while (!$res->EOF) { 
		$COMPANY_JOB_CAMPUS_ARR[] = $res->fields['PK_CAMPUS'];
		$res->MoveNext();
	}
} 
else {
	$res = $db->Execute("SELECT * FROM S_COMPANY_JOB WHERE PK_COMPANY_JOB = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$_GET[cid]'"); 
	if($res->RecordCount() == 0) {
		header("location:company?id=".$_GET['id']);
		exit;
	}

	$PK_PLACEMENT_TYPE 		= $res->fields['PK_PLACEMENT_TYPE'];
	$PK_SOC_CODE		    = $res->fields['PK_SOC_CODE'];
	$PK_COMPANY_CONTACT 	= $res->fields['PK_COMPANY_CONTACT'];
	$PK_ENROLLMENT_STATUS 	= $res->fields['PK_ENROLLMENT_STATUS'];
	$PK_PAY_TYPE 		    = $res->fields['PK_PAY_TYPE'];
	$PK_COMPANY_ADVISOR 	= $res->fields['PK_COMPANY_ADVISOR'];
	$JOB_NUMBER 	   		= $res->fields['JOB_NUMBER'];
	$JOB_TITLE 		   		= $res->fields['JOB_TITLE'];
	$JOB_POSTED 			= $res->fields['JOB_POSTED'];
	$JOB_FILLED 			= $res->fields['JOB_FILLED'];
	$JOB_CANCELED 	   		= $res->fields['JOB_CANCELED'];
	$EMPLOYMENT 		    = $res->fields['EMPLOYMENT'];
	$BENEFITS 			    = $res->fields['BENEFITS'];
	$PAY_AMOUNT 			= $res->fields['PAY_AMOUNT'];
	$ANNUAL_SALARY 			= $res->fields['ANNUAL_SALARY'];
	$WEEKLY_HOURS 			= $res->fields['WEEKLY_HOURS'];
	$JOB_DESCRIPTION 		= $res->fields['JOB_DESCRIPTION'];
	$JOB_NOTES 			    = $res->fields['JOB_NOTES'];
	$ACTIVE  		   		= $res->fields['ACTIVE'];
	$PK_PLACEMENT_STATUS	= $res->fields['PK_PLACEMENT_STATUS'];
	
	$INSTITUTIONAL_EMPLOYMENT	= $res->fields['INSTITUTIONAL_EMPLOYMENT'];
	$SELF_EMPLOYED				= $res->fields['SELF_EMPLOYED'];

	$JOB_POSTED   = ($JOB_POSTED != '0000-00-00' && $JOB_POSTED != '' ? date("m/d/Y",strtotime($JOB_POSTED)) : '');
	$JOB_FILLED   = ($JOB_FILLED != '0000-00-00' && $JOB_FILLED != '' ? date("m/d/Y",strtotime($JOB_FILLED)) : '');
	$JOB_CANCELED = ($JOB_CANCELED != '0000-00-00' && $JOB_CANCELED != '' ? date("m/d/Y",strtotime($JOB_CANCELED)) : '');
	
	$COMPANY_JOB_CAMPUS_ARR = array();
	$res = $db->Execute("select PK_CAMPUS FROM S_COMPANY_JOB_CAMPUS WHERE PK_COMPANY_JOB = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	while (!$res->EOF) { 
		$COMPANY_JOB_CAMPUS_ARR[] = $res->fields['PK_CAMPUS'];
		$res->MoveNext();
	}
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
	<title><?=COMPANY_JOB_PAGE_TITLE?> | <?=$title?></title>
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
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=COMPANY_JOB_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
												<input type="text" readonly class="form-control required-entry" id="JOB_NUMBER" name="JOB_NUMBER" value="<?=$JOB_NUMBER?>" >
												<span class="bar"></span>
												<label for="JOB_NUMBER"><?=JOB_NUMBER?></label>
											</div>
										</div>
										
										<div class="col-12 col-sm-2 custom-control custom-checkbox form-group" >
											<input type="checkbox" class="custom-control-input" id="SELF_EMPLOYED" name="SELF_EMPLOYED" value="1" <? if($SELF_EMPLOYED == 1) echo "checked"; ?>  >
											<label class="custom-control-label" for="SELF_EMPLOYED" ><?=SELF_EMPLOYED ?></label>
										</div>
										
										<div class="col-12 col-sm-2 custom-control custom-checkbox form-group" >
											<input type="checkbox" class="custom-control-input" id="INSTITUTIONAL_EMPLOYMENT" name="INSTITUTIONAL_EMPLOYMENT" value="1" <? if($INSTITUTIONAL_EMPLOYMENT == 1) echo "checked"; ?>  >
											<label class="custom-control-label" for="INSTITUTIONAL_EMPLOYMENT" ><?=INSTITUTIONAL_EMPLOYMENT ?></label>
										</div>
										
										<div class="col-12 col-sm-4">
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
												<? $camp_cond = " AND ACTIVE = 1 ";
												if(!empty($COMPANY_JOB_CAMPUS_ARR)){
													$camp_cond = " AND (ACTIVE = 1 OR PK_CAMPUS IN (".implode(",", $COMPANY_JOB_CAMPUS_ARR).") )";
												}
												$res_type = $db->Execute("select OFFICIAL_CAMPUS_NAME,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $camp_cond order by OFFICIAL_CAMPUS_NAME ASC");
												while (!$res_type->EOF) { 
													$selected = '';
													$PK_CAMPUS = $res_type->fields['PK_CAMPUS'];
													$flag = 0;
													foreach($COMPANY_JOB_CAMPUS_ARR as $COMPANY_JOB_CAMPUS_1) {
														if($COMPANY_JOB_CAMPUS_1 == $PK_CAMPUS) {
															$flag = 1;
															break;
														}
													}
													if($flag == 1 || ($res_type->RecordCount() == 1 && $_GET['id'] == ''))
														$selected = 'selected'; ?>
													<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" <?=$selected ?> ><?=$res_type->fields['OFFICIAL_CAMPUS_NAME'] ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
									</div>
									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
												<input type="text" class="form-control " id="JOB_TITLE" name="JOB_TITLE" value="<?=$JOB_TITLE?>" >
												<span class="bar"></span>
												<label for="JOB_TITLE"><?=JOB_TITLE?></label>
											</div>
										</div>
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
												<select id="PK_SOC_CODE" name="PK_SOC_CODE" class="form-control">
													<option selected></option>
													<? $res_type = $db->Execute("select PK_SOC_CODE, SOC_CODE, SOC_TITLE from M_SOC_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY SOC_CODE ASC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_SOC_CODE'] ?>" <? if($PK_SOC_CODE == $res_type->fields['PK_SOC_CODE']) echo "selected"; ?> ><?=$res_type->fields['SOC_CODE'].' - '.$res_type->fields['SOC_TITLE']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_SOC_CODE"><?=SOC_CODE?></label>
											</div>
										</div>
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
												<select id="PK_PAY_TYPE" name="PK_PAY_TYPE" class="form-control" >
													<option selected></option>
													<? $res_type = $db->Execute("select PK_PAY_TYPE,PAY_TYPE FROM M_PAY_TYPE WHERE ACTIVE = 1 order by PAY_TYPE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_PAY_TYPE'] ?>" <? if($PK_PAY_TYPE == $res_type->fields['PK_PAY_TYPE']) echo "selected"; ?> ><?=$res_type->fields['PAY_TYPE']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_PAY_TYPE"><?=PAY_TYPE?></label>
											</div>
										</div>
                                    </div>
									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
												<select id="PK_PLACEMENT_TYPE" name="PK_PLACEMENT_TYPE" class="form-control">
													<option selected></option>
													<? $res_type = $db->Execute("select PK_PLACEMENT_TYPE, TYPE from M_PLACEMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY TYPE ASC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_PLACEMENT_TYPE'] ?>" <? if($PK_PLACEMENT_TYPE == $res_type->fields['PK_PLACEMENT_TYPE']) echo "selected"; ?> ><?=$res_type->fields['TYPE']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_PLACEMENT_TYPE"><?=PLACEMENT_TYPE?></label>
											</div>
										</div>
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
												<select id="PK_PLACEMENT_STATUS" name="PK_PLACEMENT_STATUS" class="form-control" >
													<option selected></option>
													<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS, PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE ACTIVE = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY PLACEMENT_STATUS ASC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_PLACEMENT_STATUS'] ?>" <? if($PK_PLACEMENT_STATUS == $res_type->fields['PK_PLACEMENT_STATUS']) echo "selected"; ?> ><?=$res_type->fields['PLACEMENT_STATUS']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_PLACEMENT_STATUS"><?=PLACEMENT_STATUS?></label>
											</div>
										</div>
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
													<input type="number" min="0" class="form-control " id="WEEKLY_HOURS" name="WEEKLY_HOURS" value="<?=$WEEKLY_HOURS?>" >
													<span class="bar"></span>
													<label for="WEEKLY_HOURS"><?=WEEKLY_HOURS?></label>
											</div>
										</div>
									</div>
									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
												<input type="text" class="form-control date date-inputmask" id="JOB_POSTED" name="JOB_POSTED" value="<?=$JOB_POSTED?>" >
												<span class="bar"></span>
												<label for="JOB_POSTED"><?=JOB_POSTED?></label>
											</div>
										</div>
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
												<select id="EMPLOYMENT" name="EMPLOYMENT" class="form-control" >
													<option selected></option>
													<? $res_type = $db->Execute("select PK_EMPLOYMENT_TYPE, EMPLOYMENT from Z_EMPLOYMENT_TYPE WHERE ACTIVE = '1' ORDER BY EMPLOYMENT ASC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_EMPLOYMENT_TYPE'] ?>" <? if($EMPLOYMENT == $res_type->fields['PK_EMPLOYMENT_TYPE']) echo "selected"; ?> ><?=$res_type->fields['EMPLOYMENT']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="EMPLOYMENT"><?=EMPLOYMENT?></label>
											</div>
										</div>
										
										<div class="col-12 col-sm-4 focused">
											<div class="input-group m-b-40">
												<div class="input-group-prepend">
													<span class="input-group-text" style="padding: 5px 5px;height: 38px;" >$</span>
												</div>
												<input type="text" class="form-control" id="PAY_AMOUNT" name="PAY_AMOUNT" value="<?=$PAY_AMOUNT?>" style="text-align:right" onblur="calc_estimated_fee(this.value,'PAY_AMOUNT')">
												<span class="bar"></span>
												<label for="PAY_AMOUNT"><?=PAY_AMOUNT?></label>
											</div>
										</div>
                                    </div>

									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
												<input type="text" class="form-control date date-inputmask" id="JOB_FILLED" name="JOB_FILLED" value="<?=$JOB_FILLED?>" >
												<span class="bar"></span>
												<label for="JOB_FILLED"><?=JOB_FILLED?></label>
											</div>
										</div>
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
												<select id="PK_ENROLLMENT_STATUS" name="PK_ENROLLMENT_STATUS" class="form-control">
													<option selected></option>
													<option value="1" <? if($PK_ENROLLMENT_STATUS == 1) echo "selected"; ?>>Full Time</option>
													<option value="2" <? if($PK_ENROLLMENT_STATUS == 2) echo "selected"; ?>>Part Time</option>
												</select>
												<span class="bar"></span>
												<label for="PK_ENROLLMENT_STATUS"><?=ENROLLMENT_STATUS?></label>
											</div>
										</div>
										
										<div class="col-12 col-sm-4 focused">
											<div class="input-group m-b-40">
												<div class="input-group-prepend">
													<span class="input-group-text" style="padding: 5px 5px;height: 38px;" >$</span>
												</div>
												<input type="text" class="form-control" id="ANNUAL_SALARY" name="ANNUAL_SALARY" value="<?=$ANNUAL_SALARY?>" style="text-align:right" onblur="calc_estimated_fee(this.value,'ANNUAL_SALARY')">
												<span class="bar"></span>
												<label for="ANNUAL_SALARY"><?=ANNUAL_SALARY?></label>
											</div>
										</div>
									</div>
									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
												<input type="text" class="form-control date date-inputmask" id="JOB_CANCELED" name="JOB_CANCELED" value="<?=$JOB_CANCELED?>" >
												<span class="bar"></span>
												<label for="JOB_CANCELED"><?=JOB_CANCELED?></label>
											</div>
										</div>
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
												<select id="BENEFITS" name="BENEFITS" class="form-control" >
													<option value="1" <? if($BENEFITS == 1) echo "selected"; ?>>Yes</option>
													<option value="0" <? if($BENEFITS == 0) echo "selected"; ?>>No</option>
												</select>
												<span class="bar"></span> 
												<label for="BENEFITS"><?=BENEFITS?></label>
											</div>
										</div>
										
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
												<select id="PK_COMPANY_ADVISOR" name="PK_COMPANY_ADVISOR" class="form-control" >
													<option selected></option>
													<? $res_type = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.ACTIVE = 1 AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CONCAT(FIRST_NAME,' ',LAST_NAME) ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER'] ?>" <? if($PK_COMPANY_ADVISOR == $res_type->fields['PK_EMPLOYEE_MASTER']) echo "selected"; ?> ><?=$res_type->fields['NAME']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_COMPANY_ADVISOR"><?=ADVISOR?></label>
											</div>
										</div>
									</div>
									
									<div class="d-flex flex-wrap">
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
												<textarea class="form-control  rich" id="JOB_DESCRIPTION" name="JOB_DESCRIPTION"><?=$JOB_DESCRIPTION?></textarea>
												<span class="bar"></span>
												<label for="JOB_DESCRIPTION"><?=JOB_DESCRIPTION?></label>
											</div>
										</div>
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
												<select id="PK_COMPANY_CONTACT" name="PK_COMPANY_CONTACT" class="form-control" >
													<option selected></option>
														<? $res_type = $db->Execute("select PK_COMPANY_CONTACT, NAME from S_COMPANY_CONTACT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$_GET[cid]' AND ACTIVE = '1' ORDER BY NAME ASC ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_COMPANY_CONTACT'] ?>" <? if($PK_COMPANY_CONTACT == $res_type->fields['PK_COMPANY_CONTACT']) echo "selected"; ?> ><?=$res_type->fields['NAME']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_COMPANY_CONTACT"><?=CONTACT?></label>
											</div>
										</div>
										<div class="col-12 col-sm-4">
											<div class="form-group m-b-40">
												<textarea class="form-control  rich" id="JOB_NOTES" name="JOB_NOTES"><?=$JOB_NOTES?></textarea>
												<span class="bar"></span> 
												<label for="JOB_NOTES"><?=JOB_NOTES?></label>
											</div>
										</div>
									</div>
									
									<div class="row">
                                        <div class="col-md-6 submit-button-sec">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='company?id=<?=$_GET['cid']?>&tab=jobsTab'" ><?=CANCEL?></button>
											</div>
										</div>
									</div>
                                </form>
                            </div>
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
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
		var form1 = new Validation('form1');

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
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
	});
	</script>

</body>

</html>