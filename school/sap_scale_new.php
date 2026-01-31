<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/sap_scale.php");
require_once("../language/menu.php");
require_once("check_access.php");

if (check_access('SETUP_REGISTRAR') == 0) {
	header("location:../index");
	exit;
}
// $sap_pk_array=array('15','67','72','64');
// if(!in_array($_SESSION['PK_ACCOUNT'],$sap_pk_array))
// {   
// 	header("location:../school/index");
// 	exit;
// }

if (!empty($_POST)) {
	//echo "<pre>";print_r($_POST);exit;

	$SAP_SCALE['PK_CAMPUS'] 			= implode(",", $_POST['PK_CAMPUS']);
	$SAP_SCALE['SAP_SCALE_NAME']		= $_POST['SAP_SCALE_NAME'];
	$SAP_SCALE['SAP_SCALE_DESCRIPTION']	= $_POST['SAP_SCALE_DESCRIPTION'];
	$SAP_SCALE['IS_DEFAULT']			= $_POST['IS_DEFAULT'] ? $_POST['IS_DEFAULT'] : 0;
	$SAP_SCALE['ACTIVE']				= $_POST['ACTIVE'];
	$SAP_SCALE['PK_PROGRAM_PACE']		= $_POST['PROGRAM_PACE'];

	$SAP_SCALE['HOURS_COMPLETED_SCHEDULED']				= $_POST['HOURS_COMPLETED_SCHEDULED'] ? $_POST['HOURS_COMPLETED_SCHEDULED'] : 0;
	$SAP_SCALE['HOURS_COMPLETED_PROGRAM']				= $_POST['HOURS_COMPLETED_PROGRAM'] ? $_POST['HOURS_COMPLETED_PROGRAM'] : 0;
	$SAP_SCALE['HOURS_SCHEDULED_PROGRAM']				= $_POST['HOURS_SCHEDULED_PROGRAM'] ? $_POST['HOURS_SCHEDULED_PROGRAM'] : 0;
    
    $SAP_SCALE['SCHEDULE_HOURS'] = $_POST['SCHEDULE_HOURS'] ? $_POST['SCHEDULE_HOURS'] : 0;
    $SAP_SCALE['ABSENT_HOURS'] = $_POST['ABSENT_HOURS'] ? $_POST['ABSENT_HOURS'] : 0;

	$SAP_SCALE['UNITS_COMPLETED_ATTEMPTED']				= $_POST['STD_UNITS_COMPLETED_ATTEMPTED'] ? $_POST['STD_UNITS_COMPLETED_ATTEMPTED'] : 0;
	$SAP_SCALE['UNITS_COMPLETED_PROGRAM']				= $_POST['STD_UNITS_COMPLETED_PROGRAM'] ? $_POST['STD_UNITS_COMPLETED_PROGRAM'] : 0;
	$SAP_SCALE['UNITS_ATTEMPTED_PROGRAM']				= $_POST['STD_UNITS_ATTEMPTED_PROGRAM'] ? $_POST['STD_UNITS_ATTEMPTED_PROGRAM'] : 0;

	$SAP_SCALE['FA_UNITS_COMPLETED_ATTEMPTED']			= $_POST['FA_UNITS_COMPLETED_ATTEMPTED'] ? $_POST['FA_UNITS_COMPLETED_ATTEMPTED'] : 0;
	$SAP_SCALE['FA_UNITS_COMPLETED_PROGRAM']			= $_POST['FA_UNITS_COMPLETED_PROGRAM'] ? $_POST['FA_UNITS_COMPLETED_PROGRAM'] : 0;
	$SAP_SCALE['FA_UNITS_ATTEMPTED_PROGRAM']			= $_POST['FA_UNITS_ATTEMPTED_PROGRAM'] ? $_POST['FA_UNITS_ATTEMPTED_PROGRAM'] : 0;

	$SAP_SCALE['CUMULATIVE_GPA']						= $_POST['GPA_CUMULATIVE'] ? $_POST['GPA_CUMULATIVE'] : 0;

	$SAP_SCALE['PERIOD_HOURS_COMPLETED_SCHEDULED']		= $_POST['PERIOD_HOURS_COMPLETED_SCHEDULED'] ? $_POST['PERIOD_HOURS_COMPLETED_SCHEDULED'] : 0;
	$SAP_SCALE['PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED']	= $_POST['PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED'] ? $_POST['PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED'] : 0;
	$SAP_SCALE['PERIOD_FA_UNITS_COMPLETED_ATTEMPTED']	= $_POST['PERIOD_FA_UNITS_COMPLETED_ATTEMPTED'] ? $_POST['PERIOD_FA_UNITS_COMPLETED_ATTEMPTED'] : 0;
	$SAP_SCALE['PERIOD_GPA']							= $_POST['PERIOD_GPA'] ? $_POST['PERIOD_GPA'] : 0;
	$SAP_SCALE['INCLUDE_FIRST_PERIOD']					= $_POST['INCLUDE_FIRST_PERIOD'] ? $_POST['INCLUDE_FIRST_PERIOD'] : 0;

	if ($SAP_SCALE['IS_DEFAULT'] == 1) {
		$db->Execute("UPDATE S_SAP_SCALE_SETUP SET IS_DEFAULT = '0'  WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}

	if ($_GET['id'] == '') {
		$SAP_SCALE['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$SAP_SCALE['CREATED_BY']  = $_SESSION['PK_USER'];
		$SAP_SCALE['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_SAP_SCALE_SETUP', $SAP_SCALE, 'insert');
		$PK_SAP_SCALE = $db->insert_ID();
	} else {
		$SAP_SCALE['EDITED_BY'] = $_SESSION['PK_USER'];
		$SAP_SCALE['EDITED_ON'] = date("Y-m-d H:i");

		$upd_quer1 = db_perform('S_SAP_SCALE_SETUP', $SAP_SCALE, 'update', " PK_SAP_SCALE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		// dump($SAP_SCALE , $_REQUEST);
		// dd($upd_quer1);

		$PK_SAP_SCALE = $_GET['id'];
	}

	$i = 0;
	foreach ($_POST['PK_SAP_SCALE_DETAIL'] as $PK_SAP_SCALE_DETAIL) {
		$SAP_SCALE_DETAIL['PERIOD']  								= $_POST['PERIOD'][$i] ? $_POST['PERIOD'][$i] : 0;
		$SAP_SCALE_DETAIL['PROGRAM_PACE_PERCENTAGE']	 			= $_POST['PROGRAM_PERCENTAGE'][$i] ? $_POST['PROGRAM_PERCENTAGE'][$i] : 0.00;
		$SAP_SCALE_DETAIL['PK_SAP_WARNING'] 						= $_POST['PK_SAP_WARNING'][$i] ? $_POST['PK_SAP_WARNING'][$i] : 0;

		$SAP_SCALE_DETAIL['CUMULATIVE_HOURS_COMPLETED_SCHEDULED'] 	= $_POST['HOURS_COMPLETED_HOURS_SCHEDULED'][$i] ? $_POST['HOURS_COMPLETED_HOURS_SCHEDULED'][$i] : 0.00;
		$SAP_SCALE_DETAIL['CUMULATIVE_HOURS_COMPLETED_PROGRAM'] 	= $_POST['HOURS_COMPLETED_PROGRAM_HOURS'][$i] ? $_POST['HOURS_COMPLETED_PROGRAM_HOURS'][$i] : 0.00;
		$SAP_SCALE_DETAIL['CUMULATIVE_HOURS_SCHEDULED_PROGRAM'] 	= $_POST['HOURS_SCHEDULED_PROGRAM_HOURS'][$i] ? $_POST['HOURS_SCHEDULED_PROGRAM_HOURS'][$i] : 0.00;
        
        $SAP_SCALE_DETAIL['CUMULATIVE_SCHEDULE_HOURS'] = $_POST['SCHEDULE_HOURS_UNITS'][$i] ? $_POST['SCHEDULE_HOURS_UNITS'][$i] : 0.00;
        $SAP_SCALE_DETAIL['CUMULATIVE_ABSENT_HOURS'] = $_POST['ABSENT_HOURS_UNITS'][$i] ? $_POST['ABSENT_HOURS_UNITS'][$i] : 0.00;

		$SAP_SCALE_DETAIL['CUMULATIVE_UNITS_COMPLETED_ATTEMPTED'] 	= $_POST['STD_UNITS_COMPLETED_ATTEMPTED_UNITS'][$i] ? $_POST['STD_UNITS_COMPLETED_ATTEMPTED_UNITS'][$i] : 0.00;
		$SAP_SCALE_DETAIL['CUMULATIVE_UNITS_COMPLETED_PROGRAM'] 	= $_POST['STD_UNITS_COMPLETED_PROGRAM_UNITS'][$i] ? $_POST['STD_UNITS_COMPLETED_PROGRAM_UNITS'][$i] : 0.00;
		$SAP_SCALE_DETAIL['CUMULATIVE_UNITS_ATTEMPTED_PROGRAM'] 	= $_POST['STD_UNITS_ATTEMPTED_PROGRAM_UNITS'][$i] ? $_POST['STD_UNITS_ATTEMPTED_PROGRAM_UNITS'][$i] : 0.00;

		$SAP_SCALE_DETAIL['CUMULATIVE_FA_UNITS_COMPLETED_ATTEMPTED'] = $_POST['FA_UNITS_COMPLETED_PROGRAM_ATTEMPTED'][$i] ? $_POST['FA_UNITS_COMPLETED_PROGRAM_ATTEMPTED'][$i] : 0.00;
		$SAP_SCALE_DETAIL['CUMULATIVE_FA_UNITS_COMPLETED_PROGRAM'] 	 = $_POST['FA_UNITS_COMPLETED_PROGRAM_FA'][$i] ? $_POST['FA_UNITS_COMPLETED_PROGRAM_FA'][$i] : 0.00;
		$SAP_SCALE_DETAIL['CUMULATIVE_FA_UNITS_ATTEMPTED_PROGRAM'] 	 = $_POST['FA_UNITS_ATTEMPTED_PROGRAM_FA'][$i] ? $_POST['FA_UNITS_ATTEMPTED_PROGRAM_FA'][$i] : 0.00;

		$SAP_SCALE_DETAIL['CUMULATIVE_GPA'] 						 = $_POST['GPA_CUMULATIVE_UNITS'][$i] ? $_POST['GPA_CUMULATIVE_UNITS'][$i] : 0.00;

		$SAP_SCALE_DETAIL['PERIOD_HOURS_COMPLETED_SCHEDULED'] 		 = $_POST['PERIOD_HOURS_COMPLETED_SCHEDULED_INC'][$i] ? $_POST['PERIOD_HOURS_COMPLETED_SCHEDULED_INC'][$i] : 0;
		$SAP_SCALE_DETAIL['PERIOD_UNITS_COMPLETED_ATTEMPTED'] 		 = $_POST['PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_INC'][$i] ? $_POST['PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_INC'][$i] : 0;
		$SAP_SCALE_DETAIL['PERIOD_FA_UNITS_COMPLETED_ATTEMPTED'] 	 = $_POST['PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_INC'][$i] ? $_POST['PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_INC'][$i] : 0;
		$SAP_SCALE_DETAIL['PERIOD_GPA'] 							 = $_POST['PERIOD_GPA_INC'][$i] ? $_POST['PERIOD_GPA_INC'][$i] : 0;

		if ($PK_SAP_SCALE_DETAIL == '') {
			$SAP_SCALE_DETAIL['PK_SAP_SCALE'] 	= $PK_SAP_SCALE;
			$SAP_SCALE_DETAIL['PK_ACCOUNT'] 	= $_SESSION['PK_ACCOUNT'];
			$SAP_SCALE_DETAIL['CREATED_BY']  	= $_SESSION['PK_USER'];
			$SAP_SCALE_DETAIL['CREATED_ON']  	= date("Y-m-d H:i");
			db_perform('S_SAP_SCALE_SETUP_DETAIL', $SAP_SCALE_DETAIL, 'insert');
			$PK_SAP_SCALE_DETAIL_ARR[] = $db->insert_ID();
		} else {
			$SAP_SCALE_DETAIL['EDITED_BY']  = $_SESSION['PK_USER'];
			$SAP_SCALE_DETAIL['EDITED_ON']  = date("Y-m-d H:i");
			$upd_quer2 = db_perform('S_SAP_SCALE_SETUP_DETAIL', $SAP_SCALE_DETAIL, 'update', " PK_SAP_SCALE_DETAIL = '$PK_SAP_SCALE_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$PK_SAP_SCALE_DETAIL_ARR[] = $PK_SAP_SCALE_DETAIL;
			// dump($PK_SAP_SCALE_DETAIL , $_REQUEST);
			// dd($upd_quer2);
		}
		$i++;
		unset($SAP_SCALE_DETAIL);
	}

	$cond = "";
	if (!empty($PK_SAP_SCALE_DETAIL_ARR))
		$cond = " AND PK_SAP_SCALE_DETAIL NOT IN (" . implode(",", $PK_SAP_SCALE_DETAIL_ARR) . ") ";

	$db->Execute("DELETE FROM S_SAP_SCALE_SETUP_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_SAP_SCALE = '$_GET[id]' $cond ");

	if($_GET['id'] != '')
	{
		header("location:sap_scale_new?id=".$_GET['id']);
	}
	else{
		header("location:manage_sap_scale");
	}
	
}
if ($_GET['id'] == '') {
	$get_scale_id           = '';

	$SAP_SCALE_NAME 		= '';
	$SAP_SCALE_DESCRIPTION 	= '';
	$IS_DEFAULT 			= '0';
	$ACTIVE	 				= 1;
	$PK_CAMPUS_ARR 			= array();
	$PK_PROGRAM_PACE_ARR    = '';

	$HOURS_COMPLETED_SCHEDULED 		= '';
	$HOURS_COMPLETED_PROGRAM 		= '';
	$HOURS_SCHEDULED_PROGRAM 		= '';
    
    $SCHEDULE_HOURS = '';
    $ABSENT_HOURS = '';

	$FA_UNITS_COMPLETED_ATTEMPTED 	= '';
	$FA_UNITS_COMPLETED_PROGRAM 	= '';
	$FA_UNITS_ATTEMPTED_PROGRAM 	= '';

	$STD_UNITS_COMPLETED_ATTEMPTED 	= '';
	$STD_UNITS_COMPLETED_PROGRAM 	= '';
	$STD_UNITS_ATTEMPTED_PROGRAM 	= '';

	$GPA_CUMULATIVE 				= '';

	$PERIOD_HOURS_COMPLETED_SCHEDULED 			= '';
	$PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED 	= '';
	$PERIOD_FA_UNITS_COMPLETED_ATTEMPTED      	= '';
	$PERIOD_GPA    								= '';
	$INCLUDE_FIRST_PERIOD                       = '';

	$EDITED_BY    								= '';
	$EDITED_ON    								= '';

	$res = $db->Execute("SELECT PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1");
	if ($res->RecordCount() == 1) {
		$PK_CAMPUS_ARR[] = $res->fields['PK_CAMPUS'];
	}
} else {
	$get_scale_id  = $_GET['id'];

	$res = $db->Execute("SELECT * FROM S_SAP_SCALE_SETUP WHERE PK_SAP_SCALE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if ($res->RecordCount() == 0) {
		header("location:manage_sap_scale");
		exit;
	}

	$SAP_SCALE_NAME 		= $res->fields['SAP_SCALE_NAME'];
	$SAP_SCALE_DESCRIPTION 	= $res->fields['SAP_SCALE_DESCRIPTION'];
	$IS_DEFAULT 			= $res->fields['IS_DEFAULT'];
	$ACTIVE  				= $res->fields['ACTIVE'];
	$PK_CAMPUS_ARR 			= explode(",", $res->fields['PK_CAMPUS']);
	$PK_PROGRAM_PACE_ARR    = $res->fields['PK_PROGRAM_PACE'];

	$HOURS_COMPLETED_SCHEDULED 		= $res->fields['HOURS_COMPLETED_SCHEDULED'];
	$HOURS_COMPLETED_PROGRAM 		= $res->fields['HOURS_COMPLETED_PROGRAM'];
	$HOURS_SCHEDULED_PROGRAM 		= $res->fields['HOURS_SCHEDULED_PROGRAM'];
    
    $SCHEDULE_HOURS = $res->fields['SCHEDULE_HOURS'];
    $ABSENT_HOURS = $res->fields['ABSENT_HOURS'];

	$FA_UNITS_COMPLETED_ATTEMPTED 	= $res->fields['FA_UNITS_COMPLETED_ATTEMPTED'];
	$FA_UNITS_COMPLETED_PROGRAM 	= $res->fields['FA_UNITS_COMPLETED_PROGRAM'];
	$FA_UNITS_ATTEMPTED_PROGRAM 	= $res->fields['FA_UNITS_ATTEMPTED_PROGRAM'];

	$STD_UNITS_COMPLETED_ATTEMPTED 	= $res->fields['UNITS_COMPLETED_ATTEMPTED'];
	$STD_UNITS_COMPLETED_PROGRAM 	= $res->fields['UNITS_COMPLETED_PROGRAM'];
	$STD_UNITS_ATTEMPTED_PROGRAM 	= $res->fields['UNITS_ATTEMPTED_PROGRAM'];

	$GPA_CUMULATIVE 				= $res->fields['CUMULATIVE_GPA'];

	$PERIOD_HOURS_COMPLETED_SCHEDULED 			= $res->fields['PERIOD_HOURS_COMPLETED_SCHEDULED'];
	$PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED 	= $res->fields['PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED'];
	$PERIOD_FA_UNITS_COMPLETED_ATTEMPTED      	= $res->fields['PERIOD_FA_UNITS_COMPLETED_ATTEMPTED'];
	$PERIOD_GPA    								= $res->fields['PERIOD_GPA'];
	$INCLUDE_FIRST_PERIOD                       = $res->fields['INCLUDE_FIRST_PERIOD'];
	
	if($res->fields['EDITED_ON'] == '0000-00-00 00:00:00')
	{
		$EDITED_ON = '';
	}
	else{
		$EDITED_ON    			= date("m/d/Y",strtotime($res->fields['EDITED_ON']));
	}
	$EDITED_BY    			    = $res->fields['EDITED_BY'];
	
	
	$res_usr_name = $db->Execute("SELECT FIRST_NAME,LAST_NAME FROM S_EMPLOYEE_MASTER,Z_USER WHERE Z_USER.PK_USER = '$EDITED_BY' AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER");
	$Edited_Name = "";
	if ($res_usr_name->RecordCount() == 1) {
		$Edited_Name = $res_usr_name->fields['LAST_NAME'].', '.$res_usr_name->fields['FIRST_NAME'];
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
	<title><?= MNU_SAP_SCALE_SETUP ?> | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}

		.no-records-found {
			display: none;
		}

		.disable_class {
			background: #F4F4F4;
			pointer-events: none;
			opacity: 0.5;
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
					<div class="col-md-6 align-self-center">
						<h4 class="text-themecolor"><? if ($_GET['id'] == '') echo ADD;
													else echo EDIT; ?> <?=MNU_SAP_SCALE_SETUP ?> </h4>
					</div>
					<div class="col-md-6 align-self-center">
						<a href="sap_scale_excel_new?id=<?= $_GET['id'] ?>" style="float: right;" class="btn btn-info d-none d-lg-block"> <?=EXCEL?></a>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<div style="float:right;font-weight: 500;padding:2px;padding: 3px 13px;">Edited : 
								<? 
								if($EDITED_ON != ''){
									echo $Edited_Name.' '.$EDITED_ON;
								}
								else{
									echo 'N/A';
								}?>
								</div>
								<form class="floating-labels m-t-40" method="post" name="form1" id="form1" onsubmit="return calc_all_fields_higherThanBefore();">
									<div class="row">
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control required-entry" id="SAP_SCALE_NAME" name="SAP_SCALE_NAME" value="<?= $SAP_SCALE_NAME ?>">
														<span class="bar"></span>
														<label for="SAP_SCALE_NAME"><?= SAP_SCALE_NAME ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="SAP_SCALE_DESCRIPTION" name="SAP_SCALE_DESCRIPTION" value="<?= $SAP_SCALE_DESCRIPTION ?>">
														<span class="bar"></span>
														<label for="SAP_SCALE_DESCRIPTION"><?= SAP_SCALE_DESCRIPTION ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-12 col-sm-12 col-md-12 focused">
													<span class="bar"></span>
													<label><?= CAMPUS ?></label>
												</div>
											</div>
											<div class="row">
												<div class="col-12 col-sm-12 form-group">
													<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control">
														<? $res_type = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by CAMPUS_CODE ASC");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_CAMPUS 	= $res_type->fields['PK_CAMPUS'];
															foreach ($PK_CAMPUS_ARR as $PK_CAMPUS1) {
																if ($PK_CAMPUS1 == $PK_CAMPUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?= $PK_CAMPUS ?>" <?= $selected ?>><?= $res_type->fields['CAMPUS_CODE'] ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

											<div class="row">
												<div class="col-md-2">
													<div class="form-group m-b-40">
														<span class="bar"></span>
														<label for="IS_DEFAULT"><?= IS_DEFAULT ?></label>
													</div>
												</div>

												<div class="form-group row col-12 col-sm-10">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="IS_DEFAULT" name="IS_DEFAULT" value="1" <? if ($IS_DEFAULT == 1) echo "checked"; ?>>
														<label class="custom-control-label" for="IS_DEFAULT">Yes</label>
													</div>
												</div>
											</div>
											<br>
											<div class="row">
												<div class="col-12 col-sm-12 col-md-12 focused">
													<span class="bar"></span>
													<label><?= PROGRAM_PACE ?></label>
												</div>
											</div>
											<div class="row">
												<div class="col-5 col-sm-5 form-group">
													<select id="PROGRAM_PACE" name="PROGRAM_PACE" class="form-control">
														<? $res_type = $db->Execute("select PK_PROGRAM_PACE,PROGRAM_PACE_NAME from S_PROGRAM_PACE WHERE ACTIVE = 1");
														while (!$res_type->EOF) {
															$selected 			= "";
															$PK_PROGRAM_PACE 	= $res_type->fields['PK_PROGRAM_PACE'];
															if ($PK_PROGRAM_PACE_ARR == $PK_PROGRAM_PACE) {
																$selected = 'selected';
															}
														?>
															<option value="<?= $PK_PROGRAM_PACE ?>" <?=$selected?>><?= $res_type->fields['PROGRAM_PACE_NAME'] ?></option>
														<? $res_type->MoveNext();
														} ?>
													</select>

												</div>
											</div>

											<div class="row">
												<div class="col-md-2">
													<div class="form-group m-b-40">
														<span class="bar"></span>
														<label for="IS_DEFAULT"><?= ACTIVE ?></label>
													</div>
												</div>

												<div class="row form-group col-12 col-sm-10">
													<div class="custom-control custom-radio col-md-2">
														<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if ($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
														<label class="custom-control-label" for="customRadio11"><?= YES ?></label>
													</div>
													<div class="custom-control custom-radio col-md-5">
														<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if ($ACTIVE == 0) echo "checked"; ?> class="custom-control-input">
														<label class="custom-control-label" for="customRadio22"><?= NO ?></label>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-6">

											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<span class="bar"></span>
														<label for="SAP_SCALE_OPTION"><?= SAP_SCALE_OPTION ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="form-group col-4 col-sm-4">
													<label for="ATTENDANCE"><?= ATTENDANCE ?></label>
												</div>
											</div>
											<div class="row">
												<div class="form-group col-6 col-sm-6">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="HOURS_COMPLETED_SCHEDULED_1" name="HOURS_COMPLETED_SCHEDULED" value="1" <? if ($HOURS_COMPLETED_SCHEDULED == 1) echo "checked"; ?> onclick="enable_files('HOURS_COMPLETED_SCHEDULED')">
														<label class="custom-control-label" for="HOURS_COMPLETED_SCHEDULED_1"><?= HOURS_COMPLETED_SCHEDULED ?></label>
													</div>
												</div>
												<div class="form-group col-6 col-sm-6">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="HOURS_COMPLETED_PROGRAM_1" name="HOURS_COMPLETED_PROGRAM" value="1" <? if ($HOURS_COMPLETED_PROGRAM == 1) echo "checked"; ?> onclick="enable_files('HOURS_COMPLETED_PROGRAM')">
														<label class="custom-control-label" for="HOURS_COMPLETED_PROGRAM_1"><?= HOURS_COMPLETED_PROGRAM ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="form-group col-6 col-sm-6">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="HOURS_SCHEDULED_PROGRAM_1" name="HOURS_SCHEDULED_PROGRAM" value="1" <? if ($HOURS_SCHEDULED_PROGRAM == 1) echo "checked"; ?> onclick="enable_files('HOURS_SCHEDULED_PROGRAM')">
														<label class="custom-control-label" for="HOURS_SCHEDULED_PROGRAM_1"><?= HOURS_SCHEDULED_PROGRAM ?></label>
													</div>
												</div>
											</div>

                                            <div class="row">
                                                <div class="form-group col-6 col-sm-6">
                                                    <div class="custom-control custom-checkbox mr-sm-2">
                                                        <input type="checkbox" class="custom-control-input" id="SCHEDULE_HOURS_1" name="SCHEDULE_HOURS" value="1" <? if ($SCHEDULE_HOURS == 1) echo "checked"; ?> onclick="enable_files('SCHEDULE_HOURS')">
                                                        <label class="custom-control-label" for="SCHEDULE_HOURS_1"><?= SCHEDULE_HOURS ?></label>
                                                    </div>
                                                </div>
                                                <div class="form-group col-6 col-sm-6">
                                                    <div class="custom-control custom-checkbox mr-sm-2">
                                                        <input type="checkbox" class="custom-control-input" id="ABSENT_HOURS_1" name="ABSENT_HOURS" value="1" <? if ($ABSENT_HOURS == 1) echo "checked"; ?> onclick="enable_files('ABSENT_HOURS')">
                                                        <label class="custom-control-label" for="ABSENT_HOURS_1"><?= ABSENT_HOURS ?></label>
                                                    </div>
                                                </div>
                                            </div>

											<div class="row">
												<div class="form-group col-4 col-sm-4">
													<label for="CREDIT_UNIT_FA"><?= CREDIT_UNIT_FA ?></label>
												</div>
											</div>
											<div class="row">
												<div class="form-group col-6 col-sm-6">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="FA_UNITS_COMPLETED_ATTEMPTED_1" name="FA_UNITS_COMPLETED_ATTEMPTED" value="1" <? if ($FA_UNITS_COMPLETED_ATTEMPTED == 1) echo "checked"; ?> onclick="enable_files('FA_UNITS_COMPLETED_ATTEMPTED')">
														<label class="custom-control-label" for="FA_UNITS_COMPLETED_ATTEMPTED_1"><?= FA_UNITS_COMPLETED_ATTEMPTED ?></label>
													</div>
												</div>
												<div class="form-group col-6 col-sm-6">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="FA_UNITS_COMPLETED_PROGRAM_1" name="FA_UNITS_COMPLETED_PROGRAM" value="1" <? if ($FA_UNITS_COMPLETED_PROGRAM == 1) echo "checked"; ?> onclick="enable_files('FA_UNITS_COMPLETED_PROGRAM')">
														<label class="custom-control-label" for="FA_UNITS_COMPLETED_PROGRAM_1"><?= FA_UNITS_COMPLETED_PROGRAM ?></label>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="form-group col-6 col-sm-6">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="FA_UNITS_ATTEMPTED_PROGRAM_1" name="FA_UNITS_ATTEMPTED_PROGRAM" value="1" <? if ($FA_UNITS_ATTEMPTED_PROGRAM == 1) echo "checked"; ?> onclick="enable_files('FA_UNITS_ATTEMPTED_PROGRAM')">
														<label class="custom-control-label" for="FA_UNITS_ATTEMPTED_PROGRAM_1"><?= FA_UNITS_ATTEMPTED_PROGRAM ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="form-group col-4 col-sm-4">
													<label for="CREDIT_UNIT_STD"><?= CREDIT_UNIT_STD ?></label>
												</div>
											</div>
											<div class="row">
												<div class="form-group col-6 col-sm-6">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="STD_UNITS_COMPLETED_ATTEMPTED_1" name="STD_UNITS_COMPLETED_ATTEMPTED" value="1" <? if ($STD_UNITS_COMPLETED_ATTEMPTED == 1) echo "checked"; ?> onclick="enable_files('STD_UNITS_COMPLETED_ATTEMPTED')">
														<label class="custom-control-label" for="STD_UNITS_COMPLETED_ATTEMPTED_1"><?= STD_UNITS_COMPLETED_ATTEMPTED ?></label>
													</div>
												</div>
												<div class="form-group col-6 col-sm-6">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="STD_UNITS_COMPLETED_PROGRAM_1" name="STD_UNITS_COMPLETED_PROGRAM" value="1" <? if ($STD_UNITS_COMPLETED_PROGRAM == 1) echo "checked"; ?> onclick="enable_files('STD_UNITS_COMPLETED_PROGRAM')">
														<label class="custom-control-label" for="STD_UNITS_COMPLETED_PROGRAM_1"><?= STD_UNITS_COMPLETED_PROGRAM ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="form-group col-6 col-sm-6">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="STD_UNITS_ATTEMPTED_PROGRAM_1" name="STD_UNITS_ATTEMPTED_PROGRAM" value="1" <? if ($STD_UNITS_ATTEMPTED_PROGRAM == 1) echo "checked"; ?> onclick="enable_files('STD_UNITS_ATTEMPTED_PROGRAM')">
														<label class="custom-control-label" for="STD_UNITS_ATTEMPTED_PROGRAM_1"><?= STD_UNITS_ATTEMPTED_PROGRAM ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="form-group col-4 col-sm-4">
													<label for="GPA"><?= GPA ?></label>
												</div>
											</div>
											<div class="row">
												<div class="form-group col-6 col-sm-6">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="GPA_CUMULATIVE_1" name="GPA_CUMULATIVE" value="1" <? if ($GPA_CUMULATIVE == 1) echo "checked"; ?> onclick="enable_files('GPA_CUMULATIVE')">
														<label class="custom-control-label" for="GPA_CUMULATIVE_1"><?= GPA_CUMULATIVES ?></label>
													</div>
												</div>
											</div>

											<div class="row" >
												<div class="form-group col-4 col-sm-4">
													<label for="INCLUDE_TRANSFERS"><?= INCLUDE_TRANSFERS ?></label>
												</div>
											</div>
											<div class="row" >
												<div class="form-group col-6 col-sm-6">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="PERIOD_HOURS_COMPLETED_SCHEDULED_1" name="PERIOD_HOURS_COMPLETED_SCHEDULED" value="1" <? if ($PERIOD_HOURS_COMPLETED_SCHEDULED == 1) echo "checked"; ?> onclick="value_changes('PERIOD_HOURS_COMPLETED_SCHEDULED')">
														<label class="custom-control-label" for="PERIOD_HOURS_COMPLETED_SCHEDULED_1"><?= HOURS ?></label>
													</div>
												</div>

												<div class="form-group col-6 col-sm-6">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_1" name="PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED" value="1" <? if ($PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED == 1) echo "checked"; ?> onclick="value_changes('PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED')">
														<label class="custom-control-label" for="PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_1"><?= CREDIT_UNIT_STD ?></label>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="form-group col-6 col-sm-6">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_1" name="PERIOD_FA_UNITS_COMPLETED_ATTEMPTED" value="1" <? if ($PERIOD_FA_UNITS_COMPLETED_ATTEMPTED == 1) echo "checked"; ?> onclick="value_changes('PERIOD_FA_UNITS_COMPLETED_ATTEMPTED')">
														<label class="custom-control-label" for="PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_1"><?= CREDIT_UNIT_FA ?></label>
													</div>
												</div>

												<div class="form-group col-6 col-sm-6">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="PERIOD_GPA_1" name="PERIOD_GPA" value="1" <? if ($PERIOD_GPA == 1) echo "checked"; ?> onclick="value_changes('PERIOD_GPA')">
														<label class="custom-control-label" for="PERIOD_GPA_1"><?= GPA ?></label>
													</div>
												</div>

												<!-- <div class="form-group col-6 col-sm-6">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="INCLUDE_FIRST_PERIOD_1" name="INCLUDE_FIRST_PERIOD" <? if ($INCLUDE_FIRST_PERIOD == 1) echo "checked"; ?> value="1" onclick="inclue_first_period_only()" >
														<label class="custom-control-label" for="INCLUDE_FIRST_PERIOD_1"><?=INCLUDE_FIRST_PERIOD?></label>
													</div>
												</div> -->
											</div>

										</div>
									</div>
									<? $ATTENDANCE_cls = "disable_class";
									if ($ATTENDANCE == 1)
										$ATTENDANCE_cls = "";

									$CREDIT_UNIT_cls = "disable_class";
									if ($CREDIT_UNIT == 1)
										$CREDIT_UNIT_cls = "";

									$GPA_cls = "disable_class";
									if ($GPA == 1)
										$GPA_cls = ""; ?>

									<div class="row form-group">
										<div class="col-md-12">
											<table data-toggle="table" class="table-striped" id="detail_table">
												<thead>
													<tr>
														<th colspan="3">
															<div style="text-align:center;width:100%">
																<a href="javascript:void(0)" onclick="add_scale_detail()"><i style="font-size: 17px;" class="fa fa-plus-circle"></i></a>
															</div>
														</th>
														<th colspan="6">
															<div style="text-align:center;width:100%"><?= ATTENDANCE ?></div>
														</th>
														<th colspan="4">
															<div style="text-align:center;width:100%"><?= CREDIT_UNIT_FA ?></div>
														</th>
														<th colspan="4">
															<div style="text-align:center;width:100%"><?= CREDIT_UNIT_STD ?></div>
														</th>
														<th colspan="2">
															<div style="text-align:center;width:100%"><?= GPA ?></div>
														</th>

														<th></th>
													</tr>
													<tr>
														<th><?= PERIOD ?></th>
														<th><?= PROGRAM_PERCENTAGE ?></th>
														<th><?= SAP_WARNING_STATUS_IF_FAILED ?></th>

														<th>
															<div style="white-space: normal;width: 88px;"><?= HOURS_COMPLETED_SCHEDULED ?></div>
														</th>
														<th>
															<div style="white-space: normal;width: 88px;"><?= HOURS_COMPLETED_PROGRAM ?></div>
														</th>

														<th>
															<div style="white-space: normal;width: 88px;"><?= HOURS_SCHEDULED_PROGRAM ?></div>
														</th>

                                                        <th>
                                                            <div style="white-space: normal;width: 88px;"><?= SCHEDULE_HOURS ?></div>
                                                        </th>
                                                        <th>
                                                            <div style="white-space: normal;width: 88px;"><?= ABSENT_HOURS ?></div>
                                                        </th>
														<th>
															<div style="white-space: normal;width: 81px;"><?= INCLUDE_TRANSFERS ?></div>
														</th>

														<th>
															<div style="white-space: normal;width: 88px;"><?= FA_UNITS_COMPLETED_ATTEMPTED ?></div>
														</th>
														<th>
															<div style="white-space: normal;width: 88px;"><?= FA_UNITS_COMPLETED_PROGRAM ?></div>
														</th>

														<th>
															<div style="white-space: normal;width: 88px;"><?= FA_UNITS_ATTEMPTED_PROGRAM ?></div>
														</th>
														<th>
															<div style="white-space: normal;width: 81px;"><?= INCLUDE_TRANSFERS ?></div>
														</th>

														<th>
															<div style="white-space: normal;width: 88px;"><?= STD_UNITS_COMPLETED_ATTEMPTED ?></div>
														</th>
														<th>
															<div style="white-space: normal;width: 88px;"><?= STD_UNITS_COMPLETED_PROGRAM ?></div>
														</th>

														<th>
															<div style="white-space: normal;width: 88px;"><?= STD_UNITS_ATTEMPTED_PROGRAM ?></div>
														</th>
														<th>
															<div style="white-space: normal;width: 81px;"><?= INCLUDE_TRANSFERS ?></div>
														</th>

														<th>
															<div style="white-space: normal;width: 84px;"><?= GPA_CUMULATIVES ?></div>
														</th>
														<th>
															<div style="white-space: normal;width: 81px;"><?= INCLUDE_TRANSFERS ?></div>
														</th>

														<th><?= OPTIONS ?></th>

													</tr>
												</thead>
												<tbody>
													<? $count = 0;
													$res_det = $db->Execute("select PK_SAP_SCALE_DETAIL from S_SAP_SCALE_SETUP_DETAIL WHERE PK_SAP_SCALE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY PERIOD ASC ");
													while (!$res_det->EOF) {
														$_REQUEST['count_id'] 		 	 = $count;
														$_REQUEST['PK_SAP_SCALE_DETAIL'] = $res_det->fields['PK_SAP_SCALE_DETAIL'];

														include("ajax_sap_scale_setup_detail.php");
														$count++;

														$res_det->MoveNext();
													} ?>
												</tbody>
											</table>
										</div>
									</div>

									<div class="row ">
										<div class="col-md-6 text-right">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>

											<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_sap_scale'"><?= CANCEL ?></button>
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

		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?= DELETE_CONFIRMATION ?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<?= DELETE_MESSAGE_GENERAL ?>
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?= YES ?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)"><?= NO ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">

		jQuery(document).ready(function($) {
			table_fields();
			var get_scale_id = '<?=$get_scale_id?>';
			var include_data = '<?=$INCLUDE_FIRST_PERIOD?>';
			if(get_scale_id != '' && include_data == 1)
			{
				inclue_first_period_only();
			}
			
		});

		var count = '<?= $count ?>';

		function add_scale_detail() {
			jQuery(document).ready(function($) {
				var data = 'count_id=' + count;
				var value = $.ajax({
					url: "ajax_sap_scale_setup_detail",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						//alert(data)
						$('#detail_table tbody').append(data);

						var period_val = 1;
						var PERIOD = document.getElementsByName('PERIOD[]');
						document.getElementById('PERIOD_' + count).value = PERIOD.length

						var check_box_value = document.getElementById('PERIOD_HOURS_COMPLETED_SCHEDULED_1').checked;
						if(check_box_value)
						{
							selects('include_hours_chk', 'include_hours');
						}
						var check_box_value_2 = document.getElementById('PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_1').checked;
						if(check_box_value_2)
						{
							selects('include_stand_chk', 'include_stand');
						}
						var check_box_value_3 = document.getElementById('PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_1').checked;
						if(check_box_value_3)
						{
							selects('include_fa_chk', 'include_fa');
						}
						var check_box_value_4 = document.getElementById('PERIOD_GPA_1').checked;
						if(check_box_value_4)
						{
							selects('include_gpa_chk', 'include_gpa');
						}

						table_fields()

						count++
					}
				}).responseText;
			});
		}

		function delete_row(id) {
			jQuery(document).ready(function($) {
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
			});
		}

		function conf_delete(val) {
			jQuery(document).ready(function($) {
				if (val == 1)
					$("#detail_table_" + $("#DELETE_ID").val()).remove();

				$("#deleteModal").modal("hide");
			});
		}

		function enable_div(id) {
			if (document.getElementById(id).checked == true)
				document.getElementById(id + '_TYPE_DIV').style.display = 'block';
			else {
				document.getElementById(id + '_TYPE_DIV').style.display = 'none';

				document.getElementById(id + '_MIN').checked = false
				document.getElementById(id + '_MAX').checked = false

				enable_files(id + '_TYPE')
			}
		}


		function selects(name_of_checkbox, include) {

			jQuery(document).ready(function($) {

				$('.' + name_of_checkbox).each(function() {
					$('.' + name_of_checkbox).prop("disabled", false);
					$(this).parent().find('.'+include).text('Yes');
					$(this).val(1);
				});

			});
		}

		function deSelect(name_of_checkbox, include) {

			jQuery(document).ready(function($) {
				$('.' + name_of_checkbox).each(function() {
					$('.' + name_of_checkbox).prop("disabled", false);
					$(this).parent().find('.'+include).text('No');
					$(this).val(0);
				});
			});
		}

		function value_changes(type) {
			//alert(type);
			if (type == 'PERIOD_HOURS_COMPLETED_SCHEDULED') {
				var check_box_value = document.getElementById('PERIOD_HOURS_COMPLETED_SCHEDULED_1').checked;
				if(check_box_value)
					selects('include_hours_chk', 'include_hours');
				else 
					deSelect('include_hours_chk', 'include_hours');

			}
			else if (type == 'PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED') {
				var check_box_value = document.getElementById('PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_1').checked;
				if(check_box_value)
					selects('include_stand_chk', 'include_stand');
				else 
					deSelect('include_stand_chk', 'include_stand');

			}
			else if (type == 'PERIOD_FA_UNITS_COMPLETED_ATTEMPTED') {
				var check_box_value = document.getElementById('PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_1').checked;
				if(check_box_value)
					selects('include_fa_chk', 'include_fa');
				else 
					deSelect('include_fa_chk', 'include_fa');

			}
			else if (type == 'PERIOD_GPA') {
				var check_box_value = document.getElementById('PERIOD_GPA_1').checked;
				if(check_box_value)
					selects('include_gpa_chk', 'include_gpa');
				else 
					deSelect('include_gpa_chk', 'include_gpa');

			}
		}
		function inclue_first_period_only()
		{
			// var PERIOD_HOURS = document.getElementById('PERIOD_HOURS_COMPLETED_SCHEDULED_INC_0');
			// var PERIOD_FA    = document.getElementById('PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_INC_0');
			// var PERIOD_STD   = document.getElementById('PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_INC_0');
			// var PERIOD_GPA   = document.getElementById('PERIOD_GPA_INC_0');
			jQuery(document).ready(function($) {
				$('.include_hours').first().text('Yes');
				$('#PERIOD_HOURS_COMPLETED_SCHEDULED_INC_0').val(1);
				$('#PERIOD_HOURS_COMPLETED_SCHEDULED_INC_0').prop("disabled", false);

				$('.include_fa').first().text('Yes');
				$('#PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_INC_0').val(1);
				$('#PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_INC_0').prop("disabled", false);

				$('.include_stand').first().text('Yes');
				$('#PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_INC_0').val(1);
				$('#PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_INC_0').prop("disabled", false);

				$('.include_gpa').first().text('Yes');
				$('#PERIOD_GPA_INC_0').val(1);
				$('#PERIOD_GPA_INC_0').prop("disabled", false);
			});
		}

		function table_fields() {
			enable_files('HOURS_COMPLETED_SCHEDULED');
			enable_files('HOURS_COMPLETED_PROGRAM');
			enable_files('HOURS_SCHEDULED_PROGRAM');
            enable_files('SCHEDULE_HOURS');
            enable_files('ABSENT_HOURS');
			enable_files('FA_UNITS_COMPLETED_ATTEMPTED');
			enable_files('FA_UNITS_COMPLETED_PROGRAM');
			enable_files('FA_UNITS_ATTEMPTED_PROGRAM');
			enable_files('STD_UNITS_COMPLETED_ATTEMPTED');
			enable_files('STD_UNITS_COMPLETED_PROGRAM');
			enable_files('STD_UNITS_ATTEMPTED_PROGRAM');
			enable_files('GPA_CUMULATIVE');
			enable_files('PERIOD_HOURS_COMPLETED_SCHEDULED');
			enable_files('PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED');
			enable_files('PERIOD_FA_UNITS_COMPLETED_ATTEMPTED');
			enable_files('PERIOD_GPA');
		}

		function enable_files(type) {
			if (type == 'HOURS_COMPLETED_SCHEDULED') {
				var HOUR = document.getElementsByName('HOURS_COMPLETED_HOURS_SCHEDULED[]')
				var HOUR_ID = 'HOURS_COMPLETED_SCHEDULED_1';
			} else if (type == 'HOURS_COMPLETED_PROGRAM') {
				var HOUR = document.getElementsByName('HOURS_COMPLETED_PROGRAM_HOURS[]')
				var HOUR_ID = 'HOURS_COMPLETED_PROGRAM_1';
			} else if (type == 'HOURS_SCHEDULED_PROGRAM') {
				var HOUR = document.getElementsByName('HOURS_SCHEDULED_PROGRAM_HOURS[]')
				var HOUR_ID = 'HOURS_SCHEDULED_PROGRAM_1';

			} else if (type == 'SCHEDULE_HOURS') {
                var HOUR = document.getElementsByName('SCHEDULE_HOURS_UNITS[]')
                var HOUR_ID = 'SCHEDULE_HOURS_1';
            } else if (type == 'ABSENT_HOURS') {
                var HOUR = document.getElementsByName('ABSENT_HOURS_UNITS[]')
                var HOUR_ID = 'ABSENT_HOURS_1';
            }

            else if (type == 'FA_UNITS_COMPLETED_ATTEMPTED') {
				var HOUR = document.getElementsByName('FA_UNITS_COMPLETED_PROGRAM_ATTEMPTED[]')
				var HOUR_ID = 'FA_UNITS_COMPLETED_ATTEMPTED_1';
			} else if (type == 'FA_UNITS_COMPLETED_PROGRAM') {
				var HOUR = document.getElementsByName('FA_UNITS_COMPLETED_PROGRAM_FA[]')
				var HOUR_ID = 'FA_UNITS_COMPLETED_PROGRAM_1';
			} else if (type == 'FA_UNITS_ATTEMPTED_PROGRAM') {
				var HOUR = document.getElementsByName('FA_UNITS_ATTEMPTED_PROGRAM_FA[]')
				var HOUR_ID = 'FA_UNITS_ATTEMPTED_PROGRAM_1';
			} else if (type == 'STD_UNITS_COMPLETED_ATTEMPTED') {
				var HOUR = document.getElementsByName('STD_UNITS_COMPLETED_ATTEMPTED_UNITS[]')
				var HOUR_ID = 'STD_UNITS_COMPLETED_ATTEMPTED_1';
			} else if (type == 'STD_UNITS_COMPLETED_PROGRAM') {
				var HOUR = document.getElementsByName('STD_UNITS_COMPLETED_PROGRAM_UNITS[]')
				var HOUR_ID = 'STD_UNITS_COMPLETED_PROGRAM_1';
			} else if (type == 'STD_UNITS_ATTEMPTED_PROGRAM') {
				var HOUR = document.getElementsByName('STD_UNITS_ATTEMPTED_PROGRAM_UNITS[]')
				var HOUR_ID = 'STD_UNITS_ATTEMPTED_PROGRAM_1';
			} else if (type == 'GPA_CUMULATIVE') {
				var HOUR = document.getElementsByName('GPA_CUMULATIVE_UNITS[]')
				var HOUR_ID = 'GPA_CUMULATIVE_1';
			} else if (type == 'PERIOD_HOURS_COMPLETED_SCHEDULED') {
				var HOUR = document.getElementsByName('PERIOD_HOURS_COMPLETED_SCHEDULED_INC[]')
				var HOUR_ID = 'PERIOD_HOURS_COMPLETED_SCHEDULED_1';
			}
			else if (type == 'PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED') {
				var HOUR = document.getElementsByName('PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_INC[]')
				var HOUR_ID = 'PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_1';
			} else if (type == 'PERIOD_FA_UNITS_COMPLETED_ATTEMPTED') {
				var HOUR = document.getElementsByName('PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_INC[]')
				var HOUR_ID = 'PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_1';
			} else if (type == 'PERIOD_GPA') {
				var HOUR = document.getElementsByName('PERIOD_GPA_INC[]')
				var HOUR_ID = 'PERIOD_GPA_1';
			}
			//alert(document.getElementById(HOUR_ID));
			if (document.getElementById(HOUR_ID).checked == true) {
				for (var i = 0; i < HOUR.length; i++) {
					HOUR[i].disabled = false
				}
			} else {
				for (var i = 0; i < HOUR.length; i++) {
					HOUR[i].disabled = true
				}
			}
		}

		function format_number_2(id) {
			var val1 = document.getElementById(id).value
			if (val1 != '')
			{
				val1 = parseFloat(val1);
			}
			else
			{
				val1 = 0
			}
			document.getElementById(id).value = val1.toFixed(2)
		}

		function format_number_1(e) {
			var val1 = document.getElementById(e).value
			const regex = /[^\d.]|\.(?=.*\.)/g;
			const numbers = /^\d+$/g;
			const subst = '';
			const str = val1;
			const result = str.replace(regex, subst);
			//e.value = result;
			if (str.match(numbers)) {
				document.getElementById(e).value = result + '.00';
			} else {
				document.getElementById(e).value = result;
			}
		}

		function higherThanBefore(program_per,count_id){

			var current_value = document.getElementById(program_per).value;
			var count_data = count_id - 1;
			if (count_data != '-1') 
			{
				var previous_value = document.getElementById('PROGRAM_PERCENTAGE_'+count_data).value;
				if ( parseFloat(current_value) <= parseFloat(previous_value) ) // not higher than before
				{
					alert("Program percentage should be greater than the previous evaluation period.");
				}
			}
		}

		function calc_all_fields_higherThanBefore() {
			
			var PROGRAM_PERCENTAGE = document.getElementsByName('PROGRAM_PERCENTAGE[]')
			for (var i = 0; i < PROGRAM_PERCENTAGE.length; i++) {
				if (PROGRAM_PERCENTAGE[i].value != '')
				{
					var current_value = document.getElementById('PROGRAM_PERCENTAGE_'+i).value;
					var count_data    = i - 1;
					if (count_data != '-1') 
					{
						var previous_value = document.getElementById('PROGRAM_PERCENTAGE_'+count_data).value;
						if ( parseFloat(current_value) <= parseFloat(previous_value) ) // not higher than before
						{
							alert("Program percentage should be greater than the previous evaluation period.");
							return false;
						}
					}

				}
					
			}

		}
	</script>

	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= CAMPUS ?>',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: '<?= CAMPUS ?> selected'
			});
		});
	</script>

</body>

</html>
