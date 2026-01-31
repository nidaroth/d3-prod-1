<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/enrollment_mandatory_fields.php");
require_once("../language/student.php");
require_once("check_access.php");

if(check_access('SETUP_ADMISSION') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	//$ENROLL_MANDATE_FIELDS['FIRST_NAME'] 				= $_POST['FIRST_NAME'];
	//$ENROLL_MANDATE_FIELDS['LAST_NAME'] 				= $_POST['LAST_NAME'];
	$ENROLL_MANDATE_FIELDS['MIDDLE_NAME'] 				= $_POST['MIDDLE_NAME'];
	$ENROLL_MANDATE_FIELDS['OTHER_NAME'] 				= $_POST['OTHER_NAME'];
	$ENROLL_MANDATE_FIELDS['PATERNAL_LAST_NAME'] 	    = $_POST['PATERNAL_LAST_NAME']; // DIAM 393
	$ENROLL_MANDATE_FIELDS['MATERNAL_LAST_NAME'] 	    = $_POST['MATERNAL_LAST_NAME']; // DIAM 393
	$ENROLL_MANDATE_FIELDS['SSN'] 						= $_POST['SSN'];
	$ENROLL_MANDATE_FIELDS['DATE_OF_BIRTH'] 			= $_POST['DATE_OF_BIRTH'];
	$ENROLL_MANDATE_FIELDS['GENDER'] 					= $_POST['GENDER'];
	$ENROLL_MANDATE_FIELDS['DRIVERS_LICENSE'] 			= $_POST['DRIVERS_LICENSE'];
	$ENROLL_MANDATE_FIELDS['PK_MARITAL_STATUS'] 		= $_POST['PK_MARITAL_STATUS'];
	$ENROLL_MANDATE_FIELDS['PK_COUNTRY_CITIZEN'] 		= $_POST['PK_COUNTRY_CITIZEN'];
	$ENROLL_MANDATE_FIELDS['PK_CITIZENSHIP'] 			= $_POST['PK_CITIZENSHIP'];
	$ENROLL_MANDATE_FIELDS['PLACE_OF_BIRTH'] 			= $_POST['PLACE_OF_BIRTH'];
	$ENROLL_MANDATE_FIELDS['IPEDS_ETHNICITY'] 			= $_POST['IPEDS_ETHNICITY'];
	//$ENROLL_MANDATE_FIELDS['PK_TERM_BLOCK'] 			= $_POST['PK_TERM_BLOCK'];
	//$ENROLL_MANDATE_FIELDS['PK_CAMPUS_PROGRAM'] 		= $_POST['PK_CAMPUS_PROGRAM'];
	//$ENROLL_MANDATE_FIELDS['PK_STUDENT_STATUS'] 		= $_POST['PK_STUDENT_STATUS'];
	//$ENROLL_MANDATE_FIELDS['PK_SESSION'] 				= $_POST['PK_SESSION'];
	$ENROLL_MANDATE_FIELDS['PK_ENROLLMENT_STATUS'] 		= $_POST['PK_ENROLLMENT_STATUS'];
	$ENROLL_MANDATE_FIELDS['ORIGINAL_ENROLLMENT_STATUS'] = $_POST['ORIGINAL_ENROLLMENT_STATUS']; // DIAM-2366
	$ENROLL_MANDATE_FIELDS['PK_STUDENT_GROUP'] 			= $_POST['PK_STUDENT_GROUP'];
	$ENROLL_MANDATE_FIELDS['PK_DISTANCE_LEARNING'] 		= $_POST['PK_DISTANCE_LEARNING'];
	$ENROLL_MANDATE_FIELDS['PK_FUNDING'] 				= $_POST['PK_FUNDING'];
	$ENROLL_MANDATE_FIELDS['PK_REPRESENTATIVE'] 		= $_POST['PK_REPRESENTATIVE'];
	$ENROLL_MANDATE_FIELDS['PK_LEAD_SOURCE'] 			= $_POST['PK_LEAD_SOURCE'];
	$ENROLL_MANDATE_FIELDS['PK_SECOND_REPRESENTATIVE'] 	= $_POST['PK_SECOND_REPRESENTATIVE'];
	$ENROLL_MANDATE_FIELDS['PK_CONTACT_SOURCE'] 		= $_POST['PK_CONTACT_SOURCE'];
	//$ENROLL_MANDATE_FIELDS['EXPECTED_GRAD_DATE'] 		= $_POST['EXPECTED_GRAD_DATE'];
	//$ENROLL_MANDATE_FIELDS['PK_CAMPUS'] 				= $_POST['PK_CAMPUS'];
	$ENROLL_MANDATE_FIELDS['PK_PREVIOUS_COLLEGE'] 		= $_POST['PK_PREVIOUS_COLLEGE'];
	$ENROLL_MANDATE_FIELDS['PK_HIGHEST_LEVEL_OF_ED'] 	= $_POST['PK_HIGHEST_LEVEL_OF_ED'];
	$ENROLL_MANDATE_FIELDS['PK_FERPA_BLOCK'] 			= $_POST['PK_FERPA_BLOCK'];
	$ENROLL_MANDATE_FIELDS['STUDENT_ID'] 				= $_POST['STUDENT_ID'];
	$ENROLL_MANDATE_FIELDS['ADM_USER_ID'] 				= $_POST['ADM_USER_ID'];
	$ENROLL_MANDATE_FIELDS['FIRST_TERM'] 				= $_POST['FIRST_TERM'];
	
	$ENROLL_MANDATE_FIELDS['CONTACT_TYPE_1'] 			= $_POST['CONTACT_TYPE_1'];
	$ENROLL_MANDATE_FIELDS['CONTACT_TYPE_2'] 			= $_POST['CONTACT_TYPE_2'];
	$ENROLL_MANDATE_FIELDS['CONTACT_TYPE_3'] 			= $_POST['CONTACT_TYPE_3'];
	$ENROLL_MANDATE_FIELDS['CONTACT_TYPE_4'] 			= $_POST['CONTACT_TYPE_4'];
	$ENROLL_MANDATE_FIELDS['CONTACT_TYPE_5'] 			= $_POST['CONTACT_TYPE_5'];
	$ENROLL_MANDATE_FIELDS['CONTACT_TYPE_6'] 			= $_POST['CONTACT_TYPE_6'];
	$ENROLL_MANDATE_FIELDS['CONTACT_TYPE_7'] 			= $_POST['CONTACT_TYPE_7'];
	
	$ENROLL_MANDATE_FIELDS['PK_DRIVERS_LICENSE_STATE'] 		= $_POST['PK_DRIVERS_LICENSE_STATE'];
	$ENROLL_MANDATE_FIELDS['PK_STATE_OF_RESIDENCY'] 		= $_POST['PK_STATE_OF_RESIDENCY'];
	$ENROLL_MANDATE_FIELDS['BADGE_ID'] 						= $_POST['BADGE_ID'];
	$ENROLL_MANDATE_FIELDS['ORIGINAL_EXPECTED_GRAD_DATE'] 	= $_POST['ORIGINAL_EXPECTED_GRAD_DATE'];
	$ENROLL_MANDATE_FIELDS['MIDPOINT_DATE'] 				= $_POST['MIDPOINT_DATE'];
	$ENROLL_MANDATE_FIELDS['CONTRACT_SIGNED_DATE'] 			= $_POST['CONTRACT_SIGNED_DATE'];
	$ENROLL_MANDATE_FIELDS['CONTRACT_END_DATE'] 			= $_POST['CONTRACT_END_DATE'];
	
	$res = $db->Execute("SELECT * FROM S_ENROLL_MANDATE_FIELDS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if($res->RecordCount() == 0) {	
		$ENROLL_MANDATE_FIELDS['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$ENROLL_MANDATE_FIELDS['CREATED_BY'] = $_SESSION['PK_USER'];
		$ENROLL_MANDATE_FIELDS['CREATED_ON'] = date("Y-m-d H:i");
		db_perform('S_ENROLL_MANDATE_FIELDS', $ENROLL_MANDATE_FIELDS, 'insert');
	} else {
		$ENROLL_MANDATE_FIELDS['EDITED_BY'] = $_SESSION['PK_USER'];
		$ENROLL_MANDATE_FIELDS['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_ENROLL_MANDATE_FIELDS', $ENROLL_MANDATE_FIELDS, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}

	header("location:enrollment_mandatory_fields");
}

$res = $db->Execute("SELECT * FROM S_ENROLL_MANDATE_FIELDS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res->RecordCount() == 0) {
	$FIRST_NAME 				= '';
	$LAST_NAME 					= '';
	$MIDDLE_NAME 				= '';
	$OTHER_NAME 				= '';
	$PATERNAL_LAST_NAME	        = ''; // DIAM 393
	$MATERNAL_LAST_NAME	        = ''; // DIAM 393
	$SSN 						= '';
	$DATE_OF_BIRTH 				= '';
	$GENDER 					= '';
	$DRIVERS_LICENSE 			= '';
	$PK_MARITAL_STATUS 			= '';
	$PK_COUNTRY_CITIZEN 		= '';
	$PK_CITIZENSHIP 			= '';
	$PLACE_OF_BIRTH 			= '';
	$IPEDS_ETHNICITY 			= '';
	
	$FIRST_TERM					= '';
	$PK_TERM_BLOCK 				= '';
	$PK_CAMPUS_PROGRAM 			= '';
	$PK_STUDENT_STATUS 			= '';
	$PK_SESSION 				= '';
	$PK_ENROLLMENT_STATUS 		= '';
	$ORIGINAL_ENROLLMENT_STATUS = ''; // DIAM-2366
	$PK_STUDENT_GROUP 			= '';
	$PK_DISTANCE_LEARNING 		= '';
	$PK_FUNDING 				= '';
	$PK_REPRESENTATIVE 			= '';
	$PK_LEAD_SOURCE 			= '';
	$PK_SECOND_REPRESENTATIVE 	= '';
	$PK_CONTACT_SOURCE 			= '';
	$EXPECTED_GRAD_DATE 		= '';
	$PK_CAMPUS 					= '';
	$PK_PREVIOUS_COLLEGE 		= '';
	$PK_HIGHEST_LEVEL_OF_ED 	= '';
	$PK_FERPA_BLOCK 			= '';
	$STUDENT_ID 				= '';
	$ADM_USER_ID 				= '';
	$CONTACT_TYPE_1 			= '';
	$CONTACT_TYPE_2 			= '';
	$CONTACT_TYPE_3 			= '';
	$CONTACT_TYPE_4 			= '';
	$CONTACT_TYPE_5 			= '';
	$CONTACT_TYPE_6 			= '';
	$CONTACT_TYPE_7 			= '';
	
	$PK_DRIVERS_LICENSE_STATE 		= '';
	$PK_STATE_OF_RESIDENCY 			= '';
	$BADGE_ID 						= '';
	$ORIGINAL_EXPECTED_GRAD_DATE	= '';
	$MIDPOINT_DATE 					= '';
	$CONTRACT_SIGNED_DATE 			= '';
	$CONTRACT_END_DATE 				= '';
	
} else {
	$FIRST_NAME 				= $res->fields['FIRST_NAME'];
	$LAST_NAME 					= $res->fields['LAST_NAME'];
	$MIDDLE_NAME 				= $res->fields['MIDDLE_NAME'];
	$OTHER_NAME 				= $res->fields['OTHER_NAME'];
	$PATERNAL_LAST_NAME	        = $res->fields['PATERNAL_LAST_NAME']; // DIAM 393
	$MATERNAL_LAST_NAME	        = $res->fields['MATERNAL_LAST_NAME']; // DIAM 393
	$SSN 						= $res->fields['SSN'];
	$DATE_OF_BIRTH 				= $res->fields['DATE_OF_BIRTH'];
	$GENDER 					= $res->fields['GENDER'];
	$DRIVERS_LICENSE 			= $res->fields['DRIVERS_LICENSE'];
	$PK_MARITAL_STATUS 			= $res->fields['PK_MARITAL_STATUS'];
	$PK_COUNTRY_CITIZEN 		= $res->fields['PK_COUNTRY_CITIZEN'];
	$PK_CITIZENSHIP 			= $res->fields['PK_CITIZENSHIP'];
	$PLACE_OF_BIRTH 			= $res->fields['PLACE_OF_BIRTH'];
	$IPEDS_ETHNICITY 			= $res->fields['IPEDS_ETHNICITY'];
	
	$FIRST_TERM					= $res->fields['FIRST_TERM'];
	$PK_TERM_BLOCK 				= $res->fields['PK_TERM_BLOCK'];
	$PK_CAMPUS_PROGRAM 			= $res->fields['PK_CAMPUS_PROGRAM'];
	$PK_STUDENT_STATUS 			= $res->fields['PK_STUDENT_STATUS'];
	$PK_SESSION 				= $res->fields['PK_SESSION'];
	$PK_ENROLLMENT_STATUS 		= $res->fields['PK_ENROLLMENT_STATUS'];
	$ORIGINAL_ENROLLMENT_STATUS = $res->fields['ORIGINAL_ENROLLMENT_STATUS']; // DIAM-2366
	$PK_STUDENT_GROUP 			= $res->fields['PK_STUDENT_GROUP'];
	$PK_DISTANCE_LEARNING 		= $res->fields['PK_DISTANCE_LEARNING'];
	$PK_FUNDING 				= $res->fields['PK_FUNDING'];
	$PK_REPRESENTATIVE 			= $res->fields['PK_REPRESENTATIVE'];
	$PK_LEAD_SOURCE 			= $res->fields['PK_LEAD_SOURCE'];
	$PK_SECOND_REPRESENTATIVE 	= $res->fields['PK_SECOND_REPRESENTATIVE'];
	$PK_CONTACT_SOURCE 			= $res->fields['PK_CONTACT_SOURCE'];
	$EXPECTED_GRAD_DATE 		= $res->fields['EXPECTED_GRAD_DATE'];
	$PK_CAMPUS 					= $res->fields['PK_CAMPUS'];
	$PK_PREVIOUS_COLLEGE 		= $res->fields['PK_PREVIOUS_COLLEGE'];
	$PK_HIGHEST_LEVEL_OF_ED 	= $res->fields['PK_HIGHEST_LEVEL_OF_ED'];
	$PK_FERPA_BLOCK 			= $res->fields['PK_FERPA_BLOCK'];
	$STUDENT_ID 				= $res->fields['STUDENT_ID'];
	$ADM_USER_ID 				= $res->fields['ADM_USER_ID'];
	$CONTACT_TYPE_1 			= $res->fields['CONTACT_TYPE_1'];
	$CONTACT_TYPE_2 			= $res->fields['CONTACT_TYPE_2'];
	$CONTACT_TYPE_3 			= $res->fields['CONTACT_TYPE_3'];
	$CONTACT_TYPE_4 			= $res->fields['CONTACT_TYPE_4'];
	$CONTACT_TYPE_5 			= $res->fields['CONTACT_TYPE_5'];
	$CONTACT_TYPE_6 			= $res->fields['CONTACT_TYPE_6'];
	$CONTACT_TYPE_7 			= $res->fields['CONTACT_TYPE_7'];
	
	$PK_DRIVERS_LICENSE_STATE 		= $res->fields['PK_DRIVERS_LICENSE_STATE'];
	$PK_STATE_OF_RESIDENCY 			= $res->fields['PK_STATE_OF_RESIDENCY'];
	$BADGE_ID 						= $res->fields['BADGE_ID'];
	$ORIGINAL_EXPECTED_GRAD_DATE 	= $res->fields['ORIGINAL_EXPECTED_GRAD_DATE'];
	$MIDPOINT_DATE 					= $res->fields['MIDPOINT_DATE'];
	$CONTRACT_SIGNED_DATE 			= $res->fields['CONTRACT_SIGNED_DATE'];
	$CONTRACT_END_DATE 				= $res->fields['CONTRACT_END_DATE'];
}

$FIRST_NAME 				= 1;
$LAST_NAME 					= 1;
$PK_TERM_BLOCK 				= 1;
$PK_CAMPUS_PROGRAM 			= 1;
$PK_STUDENT_STATUS 			= 1;
$PK_SESSION 				= 1;
$EXPECTED_GRAD_DATE 		= 1;
$PK_CAMPUS 					= 1;

// DIAM 393 
$res_results = $db->Execute("SELECT ENABLE_PATERNAL_MATERNAL FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 

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
	<title><?=ENROLLMENT_MANDATORY_FIELDS_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"> <?=ENROLLMENT_MANDATORY_FIELDS_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels " method="post" name="form1" id="form1" >
									<div class="row" >
										<div class="col-md-12">
											<? if($_SESSION['PK_LANGUAGE'] == 1)
												$lan_field = "TOOL_CONTENT_ENG";
											else
												$lan_field = "TOOL_CONTENT_SPA"; 
											$res_help = $db->Execute("select $lan_field from Z_HELP WHERE PK_HELP = 31"); ?>
														
											<a href="help_docs?id=31" target="_blank"><i class="mdi mdi-help-circle help_size" style="float: right;margin-right:5px" title="<?=$res_help->fields[$lan_field] ?>" data-toggle="tooltip" data-placement="left" ></i></a>
										</div>
									</div>
									<div class="row">
										<div class="col-md-4">
											<div class="row">
												<div class="col-md-6">
													<b><?=INFO_FIELD_NAME?></b>
												</div> 
												<div class="col-md-4">
													<b><?=MANDATORY?></b>
													<input type="checkbox" id="INFO_SELECT_ALL" onclick="select_all('INFO')" >
												</div> 
											</div> 
											
											<div class="row">
												<div class="col-md-10">
													<hr />
												</div> 
											</div> 
											
											<div class="row"  >
												<div class="col-md-6">
													<?=FIRST_NAME?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input " id="FIRST_NAME" name="FIRST_NAME" value="1" <? if($FIRST_NAME == 1) echo "checked"; ?> disabled >
															<label class="custom-control-label" for="FIRST_NAME"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row"  >
												<div class="col-md-6">
													<?=LAST_NAME?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input " id="LAST_NAME" name="LAST_NAME" value="1" <? if($LAST_NAME == 1) echo "checked"; ?> disabled >
															<label class="custom-control-label" for="LAST_NAME"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=MIDDLE_NAME?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="MIDDLE_NAME" name="MIDDLE_NAME" value="1" <? if($MIDDLE_NAME == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="MIDDLE_NAME"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=OTHER_NAME?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="OTHER_NAME" name="OTHER_NAME" value="1" <? if($OTHER_NAME == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="OTHER_NAME"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>

											<!-- DIAM 393 -->
											<? //if ($res_results->fields['ENABLE_PATERNAL_MATERNAL'] == '1') {?>
											<div class="row"  >
												<div class="col-md-6">
													<?=PATERNAL_LAST_NAME?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="PATERNAL_LAST_NAME" name="PATERNAL_LAST_NAME" value="1" <? if($PATERNAL_LAST_NAME == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PATERNAL_LAST_NAME"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=MATERNAL_LAST_NAME?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="MATERNAL_LAST_NAME" name="MATERNAL_LAST_NAME" value="1" <? if($MATERNAL_LAST_NAME == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="MATERNAL_LAST_NAME"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
										    <? //} ?>
											<!-- End DIAM 393 -->

											<div class="row"  >
												<div class="col-md-6">
													<?=SSN?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="SSN" name="SSN" value="1" <? if($SSN == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="SSN"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=DATE_OF_BIRTH?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="DATE_OF_BIRTH" name="DATE_OF_BIRTH" value="1" <? if($DATE_OF_BIRTH == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="DATE_OF_BIRTH"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=GENDER?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="GENDER" name="GENDER" value="1" <? if($GENDER == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="GENDER"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=DRIVERS_LICENSE?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="DRIVERS_LICENSE" name="DRIVERS_LICENSE" value="1" <? if($DRIVERS_LICENSE == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="DRIVERS_LICENSE"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row"  >
												<div class="col-md-6">
													<?=DRIVERS_LICENSE_STATE?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="PK_DRIVERS_LICENSE_STATE" name="PK_DRIVERS_LICENSE_STATE" value="1" <? if($PK_DRIVERS_LICENSE_STATE == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PK_DRIVERS_LICENSE_STATE"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row"  >
												<div class="col-md-6">
													<?=MARITAL_STATUS?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="PK_MARITAL_STATUS" name="PK_MARITAL_STATUS" value="1" <? if($PK_MARITAL_STATUS == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PK_MARITAL_STATUS"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=STATE_OF_RESIDENCY?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="PK_STATE_OF_RESIDENCY" name="PK_STATE_OF_RESIDENCY" value="1" <? if($PK_STATE_OF_RESIDENCY == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PK_STATE_OF_RESIDENCY"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row"  >
												<div class="col-md-6">
													<?=COUNTRY_CITIZEN?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="PK_COUNTRY_CITIZEN" name="PK_COUNTRY_CITIZEN" value="1" <? if($PK_COUNTRY_CITIZEN == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PK_COUNTRY_CITIZEN"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=US_CITIZEN?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="PK_CITIZENSHIP" name="PK_CITIZENSHIP" value="1" <? if($PK_CITIZENSHIP == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PK_CITIZENSHIP"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=PLACE_OF_BIRTH?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="PLACE_OF_BIRTH" name="PLACE_OF_BIRTH" value="1" <? if($PLACE_OF_BIRTH == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PLACE_OF_BIRTH"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=IPEDS_ETHNICITY?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="IPEDS_ETHNICITY" name="IPEDS_ETHNICITY" value="1" <? if($IPEDS_ETHNICITY == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="IPEDS_ETHNICITY"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											
											<!--
											<div class="row"  >
												<div class="col-md-6">
													<?=STUDENT_ID?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="STUDENT_ID" name="STUDENT_ID" value="1" <? if($STUDENT_ID == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="STUDENT_ID"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>-->
											
											<div class="row"  >
												<div class="col-md-6">
													<?=HIGHEST_LEVEL_OF_ED?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="PK_HIGHEST_LEVEL_OF_ED" name="PK_HIGHEST_LEVEL_OF_ED" value="1" <? if($PK_HIGHEST_LEVEL_OF_ED == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PK_HIGHEST_LEVEL_OF_ED"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=PREVIOUS_COLLEGE?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="PK_PREVIOUS_COLLEGE" name="PK_PREVIOUS_COLLEGE" value="1" <? if($PK_PREVIOUS_COLLEGE == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PK_PREVIOUS_COLLEGE"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row"  >
												<div class="col-md-6">
													<?=BADGE_ID?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="BADGE_ID" name="BADGE_ID" value="1" <? if($BADGE_ID == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="BADGE_ID"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row"  >
												<div class="col-md-6">
													<?=FERPA_BLOCK?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input INFO_check_box" id="PK_FERPA_BLOCK" name="PK_FERPA_BLOCK" value="1" <? if($PK_FERPA_BLOCK == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PK_FERPA_BLOCK"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											
										</div> 
										<div class="col-md-4">
											<div class="row">
												<div class="col-md-6">
													<b><?=ENROLLMENT_FIELD_NAME?></b>
												</div> 
												<div class="col-md-4">
													<b><?=MANDATORY?></b>
													<input type="checkbox" id="ENROLLMENT_SELECT_ALL" onclick="select_all('ENROLLMENT')" >
												</div> 
											</div> 
											
											<div class="row">
												<div class="col-md-10">
													<hr />
												</div> 
											</div> 
											
											<div class="row"  >
												<div class="col-md-6">
													<?=FIRST_TERM?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="FIRST_TERM" name="FIRST_TERM" value="1" <? if($FIRST_TERM == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="FIRST_TERM"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row"  >
												<div class="col-md-6">
													<?=FIRST_TERM_DATE?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input " id="PK_TERM_BLOCK" name="PK_TERM_BLOCK" value="1" <? if($PK_TERM_BLOCK == 1) echo "checked"; ?> disabled >
															<label class="custom-control-label" for="PK_TERM_BLOCK"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row"  >
												<div class="col-md-6">
													<?=CONTACT_SOURCE?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="PK_CONTACT_SOURCE" name="PK_CONTACT_SOURCE" value="1" <? if($PK_CONTACT_SOURCE == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PK_CONTACT_SOURCE"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row"  >
												<div class="col-md-6">
													<?=ORIGINAL_EXPECTED_GRAD_DATE?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="ORIGINAL_EXPECTED_GRAD_DATE" name="ORIGINAL_EXPECTED_GRAD_DATE" value="1" <? if($ORIGINAL_EXPECTED_GRAD_DATE == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="ORIGINAL_EXPECTED_GRAD_DATE"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row"  >
												<div class="col-md-6">
													<?=MIDPOINT_DATE?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="MIDPOINT_DATE" name="MIDPOINT_DATE" value="1" <? if($MIDPOINT_DATE == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="MIDPOINT_DATE"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row"  >
												<div class="col-md-6">
													<?=CONTRACT_SIGNED_DATE?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="CONTRACT_SIGNED_DATE" name="CONTRACT_SIGNED_DATE" value="1" <? if($CONTRACT_SIGNED_DATE == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="CONTRACT_SIGNED_DATE"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row"  >
												<div class="col-md-6">
													<?=CONTRACT_END_DATE?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="CONTRACT_END_DATE" name="CONTRACT_END_DATE" value="1" <? if($CONTRACT_END_DATE == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="CONTRACT_END_DATE"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row"  >
												<div class="col-md-6">
													<?=PROGRAM?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input " id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM" value="1" <? if($PK_CAMPUS_PROGRAM == 1) echo "checked"; ?> disabled >
															<label class="custom-control-label" for="PK_CAMPUS_PROGRAM"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=STUDENT_STATUS?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input " id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS" value="1" <? if($PK_STUDENT_STATUS == 1) echo "checked"; ?> disabled >
															<label class="custom-control-label" for="PK_STUDENT_STATUS"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=SESSION?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input " id="PK_SESSION" name="PK_SESSION" value="1" <? if($PK_SESSION == 1) echo "checked"; ?> disabled >
															<label class="custom-control-label" for="PK_SESSION"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=FULL_PART_TIME?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="PK_ENROLLMENT_STATUS" name="PK_ENROLLMENT_STATUS" value="1" <? if($PK_ENROLLMENT_STATUS == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PK_ENROLLMENT_STATUS"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<!-- DIAM-2366 -->
											<div class="row"  >
												<div class="col-md-6">
												<?=ORIGINAL_ENROLLMENT_STATUS?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="ORIGINAL_ENROLLMENT_STATUS" name="ORIGINAL_ENROLLMENT_STATUS" value="1" <? if($ORIGINAL_ENROLLMENT_STATUS == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="ORIGINAL_ENROLLMENT_STATUS"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<!-- End DIAM-2366 -->
											<div class="row"  >
												<div class="col-md-6">
													<?=STUDENT_GROUP?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP" value="1" <? if($PK_STUDENT_GROUP == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PK_STUDENT_GROUP"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=DISTANCE_LEARNING?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="PK_DISTANCE_LEARNING" name="PK_DISTANCE_LEARNING" value="1" <? if($PK_DISTANCE_LEARNING == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PK_DISTANCE_LEARNING"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=FUNDING?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="PK_FUNDING" name="PK_FUNDING" value="1" <? if($PK_FUNDING == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PK_FUNDING"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=ADMISSION_REP?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="PK_REPRESENTATIVE" name="PK_REPRESENTATIVE" value="1" <? if($PK_REPRESENTATIVE == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PK_REPRESENTATIVE"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=ADMISSION_SOURCE?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="PK_LEAD_SOURCE" name="PK_LEAD_SOURCE" value="1" <? if($PK_LEAD_SOURCE == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PK_LEAD_SOURCE"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=SECOND_REP?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="PK_SECOND_REPRESENTATIVE" name="PK_SECOND_REPRESENTATIVE" value="1" <? if($PK_SECOND_REPRESENTATIVE == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="PK_SECOND_REPRESENTATIVE"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row"  >
												<div class="col-md-6">
													<?=EXPECTED_GRAD_DATE?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input " id="EXPECTED_GRAD_DATE" name="EXPECTED_GRAD_DATE" value="1" <? if($EXPECTED_GRAD_DATE == 1) echo "checked"; ?> disabled >
															<label class="custom-control-label" for="EXPECTED_GRAD_DATE"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row"  >
												<div class="col-md-6">
													<?=CAMPUS?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input " id="PK_CAMPUS" name="PK_CAMPUS" value="1" <? if($PK_CAMPUS == 1) echo "checked"; ?> disabled >
															<label class="custom-control-label" for="PK_CAMPUS"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<!--
											<div class="row"  >
												<div class="col-md-6">
													<?=ADM_USER_ID?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="ADM_USER_ID" name="ADM_USER_ID" value="1" <? if($ADM_USER_ID == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="ADM_USER_ID"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>-->
										</div> 
										<div class="col-md-4">
											<div class="row">
												<div class="col-md-6">
													<b><?=CONTACT_TYPE?></b>
												</div> 
												<div class="col-md-4">
													<b><?=MANDATORY?></b> <input type="checkbox" id="CONTACT_TYPE_SELECT_ALL" onclick="select_all('CONTACT_TYPE')" >
												</div> 
											</div> 
											
											<div class="row">
												<div class="col-md-10">
													<hr />
												</div> 
											</div> 
											
											<? $res_type = $db->Execute("select * from M_STUDENT_CONTACT_TYPE_MASTER WHERE ACTIVE = 1 AND PK_STUDENT_CONTACT_TYPE_MASTER IN (1,2,3,4,5) ");
											while (!$res_type->EOF) { 
												$checked = ''; 
												if($res_type->fields['PK_STUDENT_CONTACT_TYPE_MASTER'] == 1 && $CONTACT_TYPE_1 == 1) {
													$checked = 'checked'; 
												} else if($res_type->fields['PK_STUDENT_CONTACT_TYPE_MASTER'] == 2 && $CONTACT_TYPE_2 == 1) {
													$checked = 'checked'; 
												} else if($res_type->fields['PK_STUDENT_CONTACT_TYPE_MASTER'] == 3 && $CONTACT_TYPE_3 == 1) {
													$checked = 'checked'; 
												} else if($res_type->fields['PK_STUDENT_CONTACT_TYPE_MASTER'] == 4 && $CONTACT_TYPE_4 == 1) {
													$checked = 'checked'; 
												} else if($res_type->fields['PK_STUDENT_CONTACT_TYPE_MASTER'] == 5 && $CONTACT_TYPE_5 == 1) {
													$checked = 'checked'; 
												} else if($res_type->fields['PK_STUDENT_CONTACT_TYPE_MASTER'] == 6 && $CONTACT_TYPE_6 == 1) {
													$checked = 'checked'; 
												} else if($res_type->fields['PK_STUDENT_CONTACT_TYPE_MASTER'] == 7 && $CONTACT_TYPE_7 == 1) {
													$checked = 'checked'; 
												} ?>
											<div class="row"  >
												<div class="col-md-6">
													<?=$res_type->fields['STUDENT_CONTACT_TYPE'] ?>
												</div>
												
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input CONTACT_TYPE_check_box" id="CONTACT_TYPE_<?=$res_type->fields['PK_STUDENT_CONTACT_TYPE_MASTER'] ?>" name="CONTACT_TYPE_<?=$res_type->fields['PK_STUDENT_CONTACT_TYPE_MASTER'] ?>" value="1" <?=$checked ?> >
															<label class="custom-control-label" for="CONTACT_TYPE_<?=$res_type->fields['PK_STUDENT_CONTACT_TYPE_MASTER'] ?>"><?=YES?></label>
														</div>
													</div>
												</div>
											</div>
											<?	$res_type->MoveNext();
											} ?>
										</div> 
									</div> 
									
									
									
									
									
									<div class="row">
                                        <div class="col-md-3">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='setup'" ><?=CANCEL?></button>
												
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
	<script type="text/javascript">
		var form1 = new Validation('form1');
		function select_all(type){
			var str = '';
			if(document.getElementById(type+'_SELECT_ALL').checked == true)
				str = true;
			else
				str = false;
				
			var check_box = document.getElementsByClassName(type+'_check_box')
			for(var i = 0 ; i < check_box.length ; i++){
				check_box[i].checked = str
			}
		}
		
	</script>

</body>

</html>
