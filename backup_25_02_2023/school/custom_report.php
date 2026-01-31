<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student.php");
require_once("../language/custom_report.php");
require_once("../language/student_contact.php");
require_once("check_access.php");

if(check_access('REPORT_CUSTOM_REPORT') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$REPORT_MASTER['FONT_SIZE'] 			= $_POST['FONT_SIZE']; //Ticket # 1599
	$REPORT_MASTER['REPORT_NAME'] 			= $_POST['REPORT_NAME'];
	$REPORT_MASTER['GROUP_BY_FIELD'] 		= $_POST['GROUP_BY_FIELD'];
	$REPORT_MASTER['SSN'] 					= $_POST['SSN'];
	$REPORT_MASTER['SSN_VERIFIED'] 			= $_POST['SSN_VERIFIED'];
	$REPORT_MASTER['GENDER'] 				= $_POST['GENDER'];
	$REPORT_MASTER['PK_MARITAL_STATUS'] 	= implode(",",$_POST['PK_MARITAL_STATUS']);
	$REPORT_MASTER['PK_COUNTRY_CITIZEN'] 	= $_POST['PK_COUNTRY_CITIZEN'];
	$REPORT_MASTER['PK_CITIZENSHIP'] 		= implode(",",$_POST['PK_CITIZENSHIP']);
	$REPORT_MASTER['PK_STATE_OF_RESIDENCY'] = implode(",",$_POST['PK_STATE_OF_RESIDENCY']);
	$REPORT_MASTER['PK_STUDENT_STATUS']		= implode(",",$_POST['PK_STUDENT_STATUS']);
	$REPORT_MASTER['PK_CAMPUS_PROGRAM'] 	= implode(",",$_POST['PK_CAMPUS_PROGRAM']);
	$REPORT_MASTER['PK_TERM_MASTER'] 		= implode(",",$_POST['PK_TERM_MASTER']);
	$REPORT_MASTER['LEAD_ENTRY_FROM_DATE']	= $_POST['LEAD_ENTRY_FROM_DATE'];
	$REPORT_MASTER['LEAD_ENTRY_END_DATE']	= $_POST['LEAD_ENTRY_END_DATE'];
	
	$REPORT_MASTER['PK_EMPLOYEE_MASTER'] 	= implode(",",$_POST['PK_EMPLOYEE_MASTER']);
	$REPORT_MASTER['PK_CAMPUS'] 			= implode(",",$_POST['PK_CAMPUS']);
	$REPORT_MASTER['PK_FUNDING'] 			= implode(",",$_POST['PK_FUNDING']);
	$REPORT_MASTER['PK_LEAD_SOURCE'] 		= implode(",",$_POST['PK_LEAD_SOURCE']);
	$REPORT_MASTER['PK_SESSION'] 			= implode(",",$_POST['PK_SESSION']);
	$REPORT_MASTER['PK_STUDENT_GROUP'] 		= implode(",",$_POST['PK_STUDENT_GROUP']);
	
	if($REPORT_MASTER['LEAD_ENTRY_FROM_DATE'] != '')
		$REPORT_MASTER['LEAD_ENTRY_FROM_DATE'] = date("Y-m-d",strtotime($REPORT_MASTER['LEAD_ENTRY_FROM_DATE']));
	else
		$REPORT_MASTER['LEAD_ENTRY_FROM_DATE'] = '';
		
	if($REPORT_MASTER['LEAD_ENTRY_END_DATE'] != '')
		$REPORT_MASTER['LEAD_ENTRY_END_DATE'] = date("Y-m-d",strtotime($REPORT_MASTER['LEAD_ENTRY_END_DATE']));
	else
		$REPORT_MASTER['LEAD_ENTRY_END_DATE'] = '';
	
	if($_GET['id'] == '' || $_GET['duplicate'] == 1 ){
		$REPORT_MASTER['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$REPORT_MASTER['CREATED_BY']  = $_SESSION['PK_USER'];
		$REPORT_MASTER['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_CUSTOM_REPORT', $REPORT_MASTER, 'insert');
		$PK_CUSTOM_REPORT = $db->insert_ID();
	} else {
		$PK_CUSTOM_REPORT = $_GET['id'];
		$REPORT_MASTER['EDITED_BY']  = $_SESSION['PK_USER'];
		$REPORT_MASTER['EDITED_ON']  = date("Y-m-d H:i");
		db_perform('S_CUSTOM_REPORT', $REPORT_MASTER, 'update'," PK_CUSTOM_REPORT = '$PK_CUSTOM_REPORT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  " );
	}	
	
	$PK_CUSTOM_REPORT_DETAIL_ARR = array();
	foreach($_POST['INFO_HID'] as $INFO_HID){
		if($_POST['INFO_'.$INFO_HID] != '') {
			$REPORT_DETAILS = array();
			$REPORT_DETAILS['FIELDS'] 		= $_POST['INFO_'.$INFO_HID];
			$REPORT_DETAILS['FIELD_SIZE'] 	= $_POST['INFO_SIZE_'.$INFO_HID];
			$REPORT_DETAILS['SORT_ORDER'] 	= $_POST['INFO_SORT_ORDER_'.$INFO_HID];
			$REPORT_DETAILS['FIELD_FOR']	= 'INFO';
			
			if($REPORT_DETAILS['SORT_ORDER'] == '')
				$REPORT_DETAILS['SORT_ORDER'] = 1;
			
			if($_POST['INFO_PK_CUSTOM_REPORT_DETAIL_'.$INFO_HID] == '' || $_GET['duplicate'] == 1) {
				$REPORT_DETAILS['PK_CUSTOM_REPORT'] = $PK_CUSTOM_REPORT;
				$REPORT_DETAILS['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
				$REPORT_DETAILS['CREATED_BY']  		= $_SESSION['PK_USER'];
				$REPORT_DETAILS['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_CUSTOM_REPORT_DETAIL', $REPORT_DETAILS,'insert');
				$PK_CUSTOM_REPORT_DETAIL_ARR[] = $db->insert_ID();
			} else {
				$REPORT_DETAILS['EDITED_BY']  		= $_SESSION['PK_USER'];
				$REPORT_DETAILS['EDITED_ON']  		= date("Y-m-d H:i");
				db_perform('S_CUSTOM_REPORT_DETAIL', $REPORT_DETAILS,'update'," PK_CUSTOM_REPORT_DETAIL = '".$_POST['INFO_PK_CUSTOM_REPORT_DETAIL_'.$INFO_HID]."' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				$PK_CUSTOM_REPORT_DETAIL_ARR[] = $_POST['INFO_PK_CUSTOM_REPORT_DETAIL_'.$INFO_HID];
			}
		}	
	}
	$cond = "";
	if(!empty($PK_CUSTOM_REPORT_DETAIL_ARR))
		$cond = " AND PK_CUSTOM_REPORT_DETAIL NOT IN (".implode(",",$PK_CUSTOM_REPORT_DETAIL_ARR).") ";
		
	$db->Execute("DELETE FROM S_CUSTOM_REPORT_DETAIL WHERE PK_CUSTOM_REPORT = '$PK_CUSTOM_REPORT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND FIELD_FOR = 'INFO' $cond");
	
	$PK_CUSTOM_REPORT_DETAIL_ARR = array();
	foreach($_POST['CUSTOM_FIELDS_HID'] as $CUSTOM_FIELDS_HID){
		if($_POST['CUSTOM_FIELDS_'.$CUSTOM_FIELDS_HID] != '' || $_GET['duplicate'] == 1) {
			$REPORT_DETAILS = array();
			$REPORT_DETAILS['FIELDS'] 		= $_POST['CUSTOM_FIELDS_'.$CUSTOM_FIELDS_HID];
			$REPORT_DETAILS['FIELD_SIZE'] 	= $_POST['CUSTOM_FIELDS_SIZE_'.$CUSTOM_FIELDS_HID];
			$REPORT_DETAILS['SORT_ORDER'] 	= $_POST['CUSTOM_FIELDS_SORT_ORDER_'.$CUSTOM_FIELDS_HID];
			$REPORT_DETAILS['FIELD_FOR']	= 'CUSTOM_FIELDS';
			
			if($REPORT_DETAILS['SORT_ORDER'] == '')
				$REPORT_DETAILS['SORT_ORDER'] = 1;
			
			if($_POST['CUSTOM_FIELDS_PK_CUSTOM_REPORT_DETAIL_'.$CUSTOM_FIELDS_HID] == '') {
				$REPORT_DETAILS['PK_CUSTOM_REPORT'] = $PK_CUSTOM_REPORT;
				$REPORT_DETAILS['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
				$REPORT_DETAILS['CREATED_BY']  		= $_SESSION['PK_USER'];
				$REPORT_DETAILS['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_CUSTOM_REPORT_DETAIL', $REPORT_DETAILS,'insert');
				$PK_CUSTOM_REPORT_DETAIL_ARR[] = $db->insert_ID();
			} else {
				$REPORT_DETAILS['EDITED_BY']  		= $_SESSION['PK_USER'];
				$REPORT_DETAILS['EDITED_ON']  		= date("Y-m-d H:i");
				db_perform('S_CUSTOM_REPORT_DETAIL', $REPORT_DETAILS,'update'," PK_CUSTOM_REPORT_DETAIL = '".$_POST['CUSTOM_FIELDS_PK_CUSTOM_REPORT_DETAIL_'.$CUSTOM_FIELDS_HID]."' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				$PK_CUSTOM_REPORT_DETAIL_ARR[] = $_POST['CUSTOM_FIELDS_PK_CUSTOM_REPORT_DETAIL_'.$CUSTOM_FIELDS_HID];
			}
		}	
	}
	$cond = "";
	if(!empty($PK_CUSTOM_REPORT_DETAIL_ARR))
		$cond = " AND PK_CUSTOM_REPORT_DETAIL NOT IN (".implode(",",$PK_CUSTOM_REPORT_DETAIL_ARR).") ";
		
	$db->Execute("DELETE FROM S_CUSTOM_REPORT_DETAIL WHERE PK_CUSTOM_REPORT = '$PK_CUSTOM_REPORT' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND FIELD_FOR = 'CUSTOM_FIELDS' $cond");
	

	if($_POST['SAVE_CONTINUE'] == 0)
		header("location:manage_custom_report");
	else if($_POST['SAVE_CONTINUE'] == 1)
		header("location:custom_report?id=".$PK_CUSTOM_REPORT);
	else if($_POST['SAVE_CONTINUE'] == 2)
		header("location:custom_report_data_view?id=".$PK_CUSTOM_REPORT);
	else if($_POST['SAVE_CONTINUE'] == 3)
		header("location:custom_report_excel?id=".$PK_CUSTOM_REPORT);
	else if($_POST['SAVE_CONTINUE'] == 4)
		header("location:custom_report_pdf?id=".$PK_CUSTOM_REPORT);
}

function check_field($db,$FIELDS,$FIELD_FOR){
	$res = $db->Execute("SELECT * from S_CUSTOM_REPORT_DETAIL WHERE PK_CUSTOM_REPORT = '$_GET[id]' AND FIELDS=\"$FIELDS\" AND FIELD_FOR = '$FIELD_FOR' ");

	if($res->RecordCount() > 0) {
		$data[0] = 1;
		$data[1] = $res->fields['FIELD_SIZE'];
		$data[2] = $res->fields['PK_CUSTOM_REPORT_DETAIL'];
		$data[3] = $res->fields['SORT_ORDER'];
		
		if($data[3] == 0)
			$data[3] = '';
			
	} else {
		$data[0] = 0;
		$data[1] = '';
		$data[2] = '';
		$data[3] = '';
	}
	
	return $data;
}
if($_GET['id'] == ''){
	$FONT_SIZE				= 7; //Ticket # 1599
	$REPORT_NAME			= '';
	$GROUP_BY_FIELD			= '';
	$SSN					= '';
	$SSN_VERIFIED			= '';
	$GENDER					= '';
	$PK_MARITAL_STATUS		= '';
	$PK_COUNTRY_CITIZEN		= '';
	$PK_CITIZENSHIP			= '';
	$PK_STATE_OF_RESIDENCY	= '';
	$PK_STUDENT_STATUS		= '';
	$PK_EMPLOYEE_MASTER		= '';
	
	$PK_CAMPUS				= '';
	$PK_FUNDING				= '';
	$PK_LEAD_SOURCE			= '';
	$PK_SESSION				= '';
	$PK_STUDENT_GROUP		= '';
	
	$PK_CAMPUS_PROGRAM		= '';
	$PK_TERM_MASTER			= '';
	$LEAD_ENTRY_FROM_DATE	= '';
	$LEAD_ENTRY_END_DATE	= '';
	
	/* Ticket # 2030  */
	$res = $db->Execute("SELECT PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1");
	if($res->RecordCount() == 1)
		$PK_CAMPUS = $res->fields['PK_CAMPUS'];
	/* Ticket # 2030  */
	
} else {
	$res = $db->Execute("SELECT * from S_CUSTOM_REPORT WHERE PK_CUSTOM_REPORT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if($res->RecordCount() == 0){
		header("location:manage_custom_report");
		exit;
	}
	$FONT_SIZE				= $res->fields['FONT_SIZE']; //Ticket # 1599
	$REPORT_NAME			= $res->fields['REPORT_NAME'];
	$GROUP_BY_FIELD			= $res->fields['GROUP_BY_FIELD'];
	$SSN					= $res->fields['SSN'];
	$SSN_VERIFIED			= $res->fields['SSN_VERIFIED'];
	$GENDER					= $res->fields['GENDER'];
	$PK_MARITAL_STATUS		= $res->fields['PK_MARITAL_STATUS'];
	$PK_COUNTRY_CITIZEN		= $res->fields['PK_COUNTRY_CITIZEN'];
	$PK_CITIZENSHIP			= $res->fields['PK_CITIZENSHIP'];
	$PK_STATE_OF_RESIDENCY	= $res->fields['PK_STATE_OF_RESIDENCY'];
	$PK_STUDENT_STATUS		= $res->fields['PK_STUDENT_STATUS'];
	$PK_EMPLOYEE_MASTER		= $res->fields['PK_EMPLOYEE_MASTER'];
	
	$PK_CAMPUS				= $res->fields['PK_CAMPUS'];
	$PK_FUNDING				= $res->fields['PK_FUNDING'];
	$PK_LEAD_SOURCE			= $res->fields['PK_LEAD_SOURCE'];
	$PK_SESSION				= $res->fields['PK_SESSION'];
	$PK_STUDENT_GROUP		= $res->fields['PK_STUDENT_GROUP'];
	$PK_STUDENT_GROUP		= $res->fields['PK_STUDENT_GROUP'];
	
	$PK_CAMPUS_PROGRAM		= $res->fields['PK_CAMPUS_PROGRAM'];
	$PK_TERM_MASTER			= $res->fields['PK_TERM_MASTER'];
	$LEAD_ENTRY_FROM_DATE	= $res->fields['LEAD_ENTRY_FROM_DATE'];
	$LEAD_ENTRY_END_DATE	= $res->fields['LEAD_ENTRY_END_DATE'];
	
	if($LEAD_ENTRY_FROM_DATE != '' && $LEAD_ENTRY_FROM_DATE != '0000-00-00')
		$LEAD_ENTRY_FROM_DATE = date("m/d/Y",strtotime($LEAD_ENTRY_FROM_DATE));
	else
		$LEAD_ENTRY_FROM_DATE = '';
		
	if($LEAD_ENTRY_END_DATE != '' && $LEAD_ENTRY_END_DATE != '0000-00-00')
		$LEAD_ENTRY_END_DATE = date("m/d/Y",strtotime($LEAD_ENTRY_END_DATE));
	else
		$LEAD_ENTRY_END_DATE = '';
}

if($_GET['duplicate'] == 1) {
	$REPORT_NAME = '';
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
	<title><?=CUSTOM_REPORT_PAGE_TITLE?> | <?=$title?></title>
	<style>
		input::-webkit-outer-spin-button,
		input::-webkit-inner-spin-button {
		  -webkit-appearance: none;
		  margin: 0;
		}
		input[type=number] {
		  -moz-appearance: textfield;
		}
		.accordion {
		background-color: #022561;
		color: #FFF;
		cursor: pointer;
		padding: 5px;
		width: 100%;
		border: none;
		text-align: left;
		outline: none;
		font-size: 15px;
		transition: 0.4s;
	}

	.acc_active, .accordion:hover {
		background-color: #022561;
	}

	.accordion:after {
		content: '\002B';
		color: #FFF;
		font-weight: bold;
		float: right;
		margin-left: 5px;
		font-size: 20px;
	}

	.acc_active:after {
		content: "\2212";
		font-size: 20px;
	}

	.panel {
		padding: 0 18px;
		background-color: white;
		max-height: 0;
		overflow: hidden;
		transition: max-height 0.2s ease-out;
		margin-bottom: 1px;
		
	}
	li > a > label{position: unset !important;}
	
	/* Ticket # 1838 */
	.dropdown-menu>li>a { white-space: nowrap; }
	.option_red > a > label{color:red !important}
	/* Ticket # 1838 */
	
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
							<?=CUSTOM_REPORT_TITLE?>
						</h4>
                    </div>
                </div>
				<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" autocomplete="off" >
					<div class="row">
						<div class="col-6">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-12">
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<input id="REPORT_NAME" name="REPORT_NAME" type="text" class="form-control required-entry" value="<?=$REPORT_NAME?>">
													<span class="bar"></span> 
													<label for="REPORT_NAME"><?=REPORT_NAME?></label>
												</div>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<input id="FONT_SIZE" name="FONT_SIZE" type="text" class="form-control required-entry" value="<?=$FONT_SIZE?>">
													<span class="bar"></span> 
													<label for="FONT_SIZE"><?=PDF_FONT_SIZE?></label>
												</div>
											</div>
										</div>
									</div>
									
									<button class="accordion" type="button"><?=INFO?></button>
									<div class="panel">
										<br />
										<div class="row">
											<div class="col-md-2">
												<b><?=SELECT?></b>
											</div>
											<div class="col-md-6">
												<b><?=FIELD_NAME?></b>
											</div>
											<div class="col-md-2">
												<b><?=ORDER?></b>
											</div>
											<div class="col-md-2">
												<b><?=SIZE?> %</b>
											</div>
										</div>
										<hr />
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 1;
												$db_res = check_field($db,"S_STUDENT_MASTER.FIRST_NAME AS STU_FIRST_NAME","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_MASTER.FIRST_NAME AS STU_FIRST_NAME" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=FIRST_NAME?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 2;
												$db_res = check_field($db,"S_STUDENT_MASTER.LAST_NAME AS STU_LAST_NAME","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_MASTER.LAST_NAME AS STU_LAST_NAME" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=LAST_NAME?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 3;
												$db_res = check_field($db,"CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=STUDENT_NAME?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 4;
												$db_res = check_field($db,"S_STUDENT_MASTER.OTHER_NAME AS STU_OTHER_NAME","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_MASTER.OTHER_NAME AS STU_OTHER_NAME" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=OTHER_NAME?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 5;
												$db_res = check_field($db,"S_STUDENT_MASTER.SSN AS SSN","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_MASTER.SSN AS SSN" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=SSN?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 6;
												$db_res = check_field($db,"S_STUDENT_MASTER.SSN AS SSN_1","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_MASTER.SSN AS SSN_1" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=SSN_DISPLAY_FULL?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 7;
												$db_res = check_field($db,"IF(S_STUDENT_MASTER.SSN_VERIFIED = 1,'Yes','No') AS SSN_VERIFIED","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_MASTER.SSN_VERIFIED = 1,'Yes','No') AS SSN_VERIFIED" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=SSN_VERIFIED?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 8;
												$db_res = check_field($db,"IF(S_STUDENT_MASTER.DATE_OF_BIRTH != '0000-00-00',DATE_FORMAT(S_STUDENT_MASTER.DATE_OF_BIRTH,'%m/%d/%Y'),'') AS DATE_OF_BIRTH","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_MASTER.DATE_OF_BIRTH != '0000-00-00',DATE_FORMAT(S_STUDENT_MASTER.DATE_OF_BIRTH,'%m/%d/%Y'),'') AS DATE_OF_BIRTH" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=DATE_OF_BIRTH?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 9;
												$db_res = check_field($db,"TIMESTAMPDIFF(YEAR, S_STUDENT_MASTER.DATE_OF_BIRTH, CURDATE()) AS AGE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="TIMESTAMPDIFF(YEAR, S_STUDENT_MASTER.DATE_OF_BIRTH, CURDATE()) AS AGE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=AGE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<!-- Ticket # 1769   -->
												<? $index = 10;
												$db_res = check_field($db,"Z_GENDER.GENDER AS GENDER","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="Z_GENDER.GENDER AS GENDER" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
												<!-- Ticket # 1769   -->
											</div>
											<div class="col-md-6">
												<?=GENDER?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 11;
												$db_res = check_field($db,"S_STUDENT_MASTER.DRIVERS_LICENSE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_MASTER.DRIVERS_LICENSE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=DRIVERS_LICENSE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 12;
												$db_res = check_field($db,"Z_STATES_DRIVERS_LICENSE.STATE_NAME AS STATES_DRIVERS_LICENSE_STATE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="Z_STATES_DRIVERS_LICENSE.STATE_NAME AS STATES_DRIVERS_LICENSE_STATE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=DRIVERS_LICENSE_STATE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 13;
												$db_res = check_field($db,"Z_MARITAL_STATUS_STUD.MARITAL_STATUS AS STU_MARITAL_STATUS","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="Z_MARITAL_STATUS_STUD.MARITAL_STATUS AS STU_MARITAL_STATUS" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=MARITAL_STATUS?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 14;
												$db_res = check_field($db,"Z_COUNTRY_CITIZEN.NAME AS COUNTRY_CITIZEN","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="Z_COUNTRY_CITIZEN.NAME AS COUNTRY_CITIZEN" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=COUNTRY_CITIZEN?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 15;
												$db_res = check_field($db,"Z_CITIZENSHIP.CITIZENSHIP","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="Z_CITIZENSHIP.CITIZENSHIP" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=US_CITIZEN?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 16;
												$db_res = check_field($db,"PLACE_OF_BIRTH","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="PLACE_OF_BIRTH" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=PLACE_OF_BIRTH?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 17;
												$db_res = check_field($db,"Z_STATES_OF_RESIDENCY.STATE_NAME AS STATE_OF_RESIDENCY","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="Z_STATES_OF_RESIDENCY.STATE_NAME AS STATE_OF_RESIDENCY" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=STATE_OF_RESIDENCY?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 18;
												$db_res = check_field($db,"S_STUDENT_ACADEMICS.STUDENT_ID","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_ACADEMICS.STUDENT_ID" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=STUDENT_ID?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 19;
												$db_res = check_field($db,"S_STUDENT_ACADEMICS.ADM_USER_ID","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_ACADEMICS.ADM_USER_ID" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=ADM_USER_ID?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 20;
												$db_res = check_field($db,"M_HIGHEST_LEVEL_OF_EDU.HIGHEST_LEVEL_OF_EDU AS HIGHEST_LEVEL_OF_EDU","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="M_HIGHEST_LEVEL_OF_EDU.HIGHEST_LEVEL_OF_EDU AS HIGHEST_LEVEL_OF_EDU" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=HIGHEST_LEVEL_OF_ED?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 21;
												$db_res = check_field($db,"IF(PREVIOUS_COLLEGE = 1,'Yes','No') AS PREVIOUS_COLLEGE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(PREVIOUS_COLLEGE = 1,'Yes','No') AS PREVIOUS_COLLEGE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=PREVIOUS_COLLEGE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 22;
												$db_res = check_field($db,"S_STUDENT_MASTER.BADGE_ID","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_MASTER.BADGE_ID" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=BADGE_ID?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 23;
												$db_res = check_field($db,"IF(FERPA_BLOCK = 1,'Yes',IF(FERPA_BLOCK = 2,'No','')) AS FERPA_BLOCK","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(FERPA_BLOCK = 1,'Yes',IF(FERPA_BLOCK = 2,'No','')) AS FERPA_BLOCK" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=FERPA_BLOCK?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 24;
												$db_res = check_field($db,"S_STUDENT_MASTER.IPEDS_ETHNICITY AS IPEDS_ETHNICITY","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_MASTER.IPEDS_ETHNICITY AS IPEDS_ETHNICITY" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=IPEDS_ETHNICITY?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
									
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 25;
												$db_res = check_field($db,"'' AS RACE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="'' AS RACE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=RACE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 82;
												$db_res = check_field($db,"OLD_DSIS_STU_NO","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="OLD_DSIS_STU_NO" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=OLD_DSIS_STU_NO?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 83;
												$db_res = check_field($db,"OLD_DSIS_LEAD_ID","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="OLD_DSIS_LEAD_ID" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=OLD_DSIS_LEAD_ID?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
									</div>
									
									<button class="accordion" type="button"><?=ENROLLMENT?></button>
									<div class="panel">
											<br />
										<div class="row">
											<div class="col-md-2">
												<b><?=SELECT?></b>
											</div>
											<div class="col-md-6">
												<b><?=FIELD_NAME?></b>
											</div>
											<div class="col-md-2">
												<b><?=ORDER?></b>
											</div>
											<div class="col-md-2">
												<b><?=SIZE?> %</b>
											</div>
										</div>
										<hr />
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 26;
												$db_res = check_field($db,"M_STUDENT_STATUS.STUDENT_STATUS AS STUDENT_STATUS","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="M_STUDENT_STATUS.STUDENT_STATUS AS STUDENT_STATUS" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=STUDENT_STATUS?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 27;
												$db_res = check_field($db,"CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS EMP_NAME","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS EMP_NAME" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=ADMISSION_REP?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 28;
												$db_res = check_field($db,"IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS BEGIN_DATE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS BEGIN_DATE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=FIRST_TERM_DATE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 29;
												$db_res = check_field($db,"M_CAMPUS_PROGRAM.CODE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="M_CAMPUS_PROGRAM.CODE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=PROGRAM_CODE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 30;
												$db_res = check_field($db,"M_CAMPUS_PROGRAM.DESCRIPTION as PROGRAM_DESC","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="M_CAMPUS_PROGRAM.DESCRIPTION as PROGRAM_DESC" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=PROGRAM_DESCRIPTION?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 31;
												$db_res = check_field($db,"S_STUDENT_ENROLLMENT.STATUS_DATE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_ENROLLMENT.STATUS_DATE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=STATUS_DATE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 32;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.MIDPOINT_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.MIDPOINT_DATE,'%m/%d/%Y' )) AS MIDPOINT_DATE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.MIDPOINT_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.MIDPOINT_DATE,'%m/%d/%Y' )) AS MIDPOINT_DATE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=MIDPOINT_DATE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 33;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.EXTERN_START_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.EXTERN_START_DATE,'%m/%d/%Y' )) AS EXTERN_START_DATE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.EXTERN_START_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.EXTERN_START_DATE,'%m/%d/%Y' )) AS EXTERN_START_DATE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=EXTERN_START_DATE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 34;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.EXPECTED_GRAD_DATE,'%m/%d/%Y' )) AS EXPECTED_GRAD_DATE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.EXPECTED_GRAD_DATE,'%m/%d/%Y' )) AS EXPECTED_GRAD_DATE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=EXPECTED_GRAD_DATE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 35;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.GRADE_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.GRADE_DATE,'%m/%d/%Y' )) AS GRADE_DATE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.GRADE_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.GRADE_DATE,'%m/%d/%Y' )) AS GRADE_DATE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=GRADE_DATE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 36;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.LDA = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.LDA,'%m/%d/%Y' )) AS LDA","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.LDA = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.LDA,'%m/%d/%Y' )) AS LDA" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=LDA?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 37;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.DETERMINATION_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.DETERMINATION_DATE,'%m/%d/%Y' )) AS DETERMINATION_DATE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.DETERMINATION_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.DETERMINATION_DATE,'%m/%d/%Y' )) AS DETERMINATION_DATE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=DETERMINATION_DATE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 38;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.DROP_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.DROP_DATE,'%m/%d/%Y' )) AS DROP_DATE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.DROP_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.DROP_DATE,'%m/%d/%Y' )) AS DROP_DATE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=DROP_DATE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 39;
												$db_res = check_field($db,"M_DROP_REASON.DROP_REASON AS DROP_REASON","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="M_DROP_REASON.DROP_REASON AS DROP_REASON" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=DROP_REASON?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 40;
												$db_res = check_field($db,"M_LEAD_SOURCE.LEAD_SOURCE AS LEAD_SOURCE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="M_LEAD_SOURCE.LEAD_SOURCE AS LEAD_SOURCE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=LEAD_SOURCE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 41;
												$db_res = check_field($db,"M_LEAD_CONTACT_SOURCE.LEAD_CONTACT_SOURCE AS LEAD_CONTACT_SOURCE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="M_LEAD_CONTACT_SOURCE.LEAD_CONTACT_SOURCE AS LEAD_CONTACT_SOURCE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=CONTACT_SOURCE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 42;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.CONTRACT_SIGNED_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.CONTRACT_SIGNED_DATE,'%m/%d/%Y' )) AS CONTRACT_SIGNED_DATE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.CONTRACT_SIGNED_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.CONTRACT_SIGNED_DATE,'%m/%d/%Y' )) AS CONTRACT_SIGNED_DATE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=CONTRACT_SIGNED_DATE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 43;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.CONTRACT_END_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.CONTRACT_END_DATE,'%m/%d/%Y' )) AS CONTRACT_END_DATE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.CONTRACT_END_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.CONTRACT_END_DATE,'%m/%d/%Y' )) AS CONTRACT_END_DATE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=CONTRACT_END_DATE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<!-- Ticket # 1595 -->
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 44;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.ENTRY_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.ENTRY_DATE,'%m/%d/%Y' )) AS ENTRY_DATE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.ENTRY_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.ENTRY_DATE,'%m/%d/%Y' )) AS ENTRY_DATE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=ENTRY_DATE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 45;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.ENTRY_TIME = '00:00:00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.ENTRY_TIME,'%h:%i %p' )) AS ENTRY_TIME","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.ENTRY_TIME = '00:00:00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.ENTRY_TIME,'%h:%i %p' )) AS ENTRY_TIME" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=ENTRY_TIME?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										<!-- Ticket # 1595 -->

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 46;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.ORIGINAL_EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.ORIGINAL_EXPECTED_GRAD_DATE,'%m/%d/%Y' )) AS ORIGINAL_EXPECTED_GRAD_DATE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.ORIGINAL_EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.ORIGINAL_EXPECTED_GRAD_DATE,'%m/%d/%Y' )) AS ORIGINAL_EXPECTED_GRAD_DATE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=ORIGINAL_EXPECTED_GRAD_DATE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 47;
												$db_res = check_field($db,"M_ENROLLMENT_STATUS.CODE AS FULL_PART_TIME","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="M_ENROLLMENT_STATUS.CODE AS FULL_PART_TIME" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=FULL_PART_TIME?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 48;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.FT_PT_EFFECTIVE_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.FT_PT_EFFECTIVE_DATE,'%m/%d/%Y' )) AS FT_PT_EFFECTIVE_DATE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.FT_PT_EFFECTIVE_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.FT_PT_EFFECTIVE_DATE,'%m/%d/%Y' )) AS FT_PT_EFFECTIVE_DATE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=FT_PT_EFFECTIVE_DATE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 49;
												$db_res = check_field($db,"M_SESSION.SESSION AS SESSION","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="M_SESSION.SESSION AS SESSION" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=SESSION?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 50;
												$db_res = check_field($db,"M_FIRST_TERM.FIRST_TERM AS FIRST_TERM","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="M_FIRST_TERM.FIRST_TERM AS FIRST_TERM" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=FIRST_TERM?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 51;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.REENTRY = 1,'Yes','No') AS REENTRY","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.REENTRY = 1,'Yes','No') AS REENTRY" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=REENTRY?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 52;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.TRANSFER_IN = 1,'Yes','No') AS TRANSFER_IN","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.TRANSFER_IN = 1,'Yes','No') AS TRANSFER_IN" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=TRANSFER_IN?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 53;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.TRANSFER_OUT = 1,'Yes','No') AS TRANSFER_OUT","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.TRANSFER_OUT = 1,'Yes','No') AS TRANSFER_OUT" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=TRANSFER_OUT?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 54;
												$db_res = check_field($db,"M_DISTANCE_LEARNING.DISTANCE_LEARNING AS DISTANCE_LEARNING","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="M_DISTANCE_LEARNING.DISTANCE_LEARNING AS DISTANCE_LEARNING" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=DISTANCE_LEARNING?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 55;
												$db_res = check_field($db,"M_FUNDING.FUNDING AS FUNDING","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="M_FUNDING.FUNDING AS FUNDING" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=FUNDING?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 56;
												$db_res = check_field($db,"M_PLACEMENT_STATUS.PLACEMENT_STATUS AS PLACEMENT_STATUS","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="M_PLACEMENT_STATUS.PLACEMENT_STATUS AS PLACEMENT_STATUS" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=PLACEMENT_STATUS?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 57;
												$db_res = check_field($db,"IF(S_STUDENT_ENROLLMENT.STRF_PAID_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.STRF_PAID_DATE,'%m/%d/%Y' )) AS STRF_PAID_DATE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_ENROLLMENT.STRF_PAID_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.STRF_PAID_DATE,'%m/%d/%Y' )) AS STRF_PAID_DATE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=STRF_PAID_DATE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 58;
												$db_res = check_field($db,"M_STUDENT_GROUP.STUDENT_GROUP AS STUDENT_GROUP","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="M_STUDENT_GROUP.STUDENT_GROUP AS STUDENT_GROUP" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=STUDENT_GROUP?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 59;
												$db_res = check_field($db,"M_SPECIAL_PROGRAM_INDICATOR.CODE AS SPECIAL_PROGRAM_INDICATOR","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="M_SPECIAL_PROGRAM_INDICATOR.CODE AS SPECIAL_PROGRAM_INDICATOR" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=SPECIAL_PROGRAM_INDICATOR?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>

										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 60;
												$db_res = check_field($db,"''AS CAMPUS","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="''AS CAMPUS" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=CAMPUS?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
									</div>
									
									<button class="accordion" type="button"><?=CONTACT?></button>
									<div class="panel">
										<br />
										<div class="row">
											<div class="col-md-2">
												<b><?=SELECT?></b>
											</div>
											<div class="col-md-6">
												<b><?=FIELD_NAME?></b>
											</div>
											<div class="col-md-2">
												<b><?=ORDER?></b>
											</div>
											<div class="col-md-2">
												<b><?=SIZE?> %</b>
											</div>
										</div>
										<hr />
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 61;
												$db_res = check_field($db,"S_STUDENT_CONTACT.ADDRESS AS ADDRESS","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_CONTACT.ADDRESS AS ADDRESS" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=ADDRESS?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 62;
												$db_res = check_field($db,"S_STUDENT_CONTACT.ADDRESS_1 AS ADDRESS_1","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_CONTACT.ADDRESS_1 AS ADDRESS_1" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=ADDRESS_1?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 63;
												$db_res = check_field($db,"S_STUDENT_CONTACT.CITY AS CITY","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_CONTACT.CITY AS CITY" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=CITY?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 64;
												$db_res = check_field($db,"CONTACT_STATE.STATE_NAME AS CONTACT_STATE_NAME","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="CONTACT_STATE.STATE_NAME AS CONTACT_STATE_NAME" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=STATE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 65;
												$db_res = check_field($db,"S_STUDENT_CONTACT.ZIP AS ZIP","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_CONTACT.ZIP AS ZIP" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=ZIP?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 66;
												$db_res = check_field($db,"CONTACT_COUNTRY.NAME AS CONTACT_COUNTRY_NAME","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="CONTACT_COUNTRY.NAME AS CONTACT_COUNTRY_NAME" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=COUNTRY?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 67;
												$db_res = check_field($db,"IF(S_STUDENT_CONTACT.ADDRESS_INVALID = 1, 'Yes', '') AS ADDRESS_INVALID","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_CONTACT.ADDRESS_INVALID = 1, 'Yes', '') AS ADDRESS_INVALID" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=ADDRESS.' '.INVALID?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 68;
												$db_res = check_field($db,"S_STUDENT_CONTACT.HOME_PHONE AS HOME_PHONE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_CONTACT.HOME_PHONE AS HOME_PHONE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=HOME_PHONE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 69;
												$db_res = check_field($db,"If(S_STUDENT_CONTACT.HOME_PHONE_INVALID = 1, 'Yes', '') AS HOME_PHONE_INVALID","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="If(S_STUDENT_CONTACT.HOME_PHONE_INVALID = 1, 'Yes', '') AS HOME_PHONE_INVALID" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=HOME_PHONE.' '.INVALID?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 70;
												$db_res = check_field($db,"S_STUDENT_CONTACT.WORK_PHONE AS WORK_PHONE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_CONTACT.WORK_PHONE AS WORK_PHONE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=WORK_PHONE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 71;
												$db_res = check_field($db,"IF(S_STUDENT_CONTACT.WORK_PHONE_INVALID = 1, 'Yes', '') AS WORK_PHONE_INVALID","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_CONTACT.WORK_PHONE_INVALID = 1, 'Yes', '') AS WORK_PHONE_INVALID" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=WORK_PHONE.' '.INVALID?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 72;
												$db_res = check_field($db,"S_STUDENT_CONTACT.CELL_PHONE AS CELL_PHONE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_CONTACT.CELL_PHONE AS CELL_PHONE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=CELL_PHONE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 73;
												$db_res = check_field($db,"IF(S_STUDENT_CONTACT.OPT_OUT = 1, 'Yes', '') AS OPT_OUT","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_CONTACT.OPT_OUT = 1, 'Yes', '') AS OPT_OUT" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=OPTOUT?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 74;
												$db_res = check_field($db,"IF(S_STUDENT_CONTACT.CELL_PHONE_INVALID = 1, 'Yes', '') AS CELL_PHONE_INVALID","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_CONTACT.CELL_PHONE_INVALID = 1, 'Yes', '') AS CELL_PHONE_INVALID" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=CELL_PHONE.' '.INVALID?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 75;
												$db_res = check_field($db,"S_STUDENT_CONTACT.OTHER_PHONE AS OTHER_PHONE","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_CONTACT.OTHER_PHONE AS OTHER_PHONE" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=OTHER_PHONE?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 76;
												$db_res = check_field($db,"IF(S_STUDENT_CONTACT.OTHER_PHONE_INVALID = 1, 'Yes', '') AS OTHER_PHONE_INVALID","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_CONTACT.OTHER_PHONE_INVALID = 1, 'Yes', '') AS OTHER_PHONE_INVALID" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=OTHER_PHONE.' '.INVALID?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 77;
												$db_res = check_field($db,"S_STUDENT_CONTACT.EMAIL AS EMAIL","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_CONTACT.EMAIL AS EMAIL" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=EMAIL?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 78;
												$db_res = check_field($db,"IF(S_STUDENT_CONTACT.USE_EMAIL = 1, 'Yes', '') AS USE_EMAIL","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_CONTACT.USE_EMAIL = 1, 'Yes', '') AS USE_EMAIL" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=USE_EMAIL?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 79;
												$db_res = check_field($db,"IF(S_STUDENT_CONTACT.EMAIL_INVALID = 1, 'Yes', '') AS EMAIL_INVALID","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_CONTACT.EMAIL_INVALID = 1, 'Yes', '') AS EMAIL_INVALID" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=EMAIL.' '.INVALID?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 80;
												$db_res = check_field($db,"S_STUDENT_CONTACT.EMAIL_OTHER AS EMAIL_OTHER","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="S_STUDENT_CONTACT.EMAIL_OTHER AS EMAIL_OTHER" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=EMAIL_OTHER?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index = 81;
												$db_res = check_field($db,"IF(S_STUDENT_CONTACT.EMAIL_OTHER_INVALID = 1, 'Yes', '') AS EMAIL_OTHER_INVALID","INFO"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="INFO_<?=$index?>" id="INFO_<?=$index?>" value="IF(S_STUDENT_CONTACT.EMAIL_OTHER_INVALID = 1, 'Yes', '') AS EMAIL_OTHER_INVALID" onclick="enable_size('INFO','<?=$index?>')" />
												
												<input type="hidden" name="INFO_HID[]" value="<?=$index?>" >
												<input type="hidden" name="INFO_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=OTHER_EMAIL.' '.INVALID?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SORT_ORDER_<?=$index?>" id="INFO_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="INFO_SIZE_<?=$index?>" id="INFO_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
									</div>
									
									<button class="accordion" type="button"><?=CUSTOM_FIELDS?></button>
									<div class="panel">
										<br />
										<div class="row">
											<div class="col-md-2">
												<b><?=SELECT?></b>
											</div>
											<div class="col-md-6">
												<b><?=FIELD_NAME?></b>
											</div>
											<div class="col-md-2">
												<b><?=ORDER?></b>
											</div>
											<div class="col-md-2">
												<b><?=SIZE?> %</b>
											</div>
										</div>
										<hr />
										
										<? $res_dd = $db->Execute("select PK_CUSTOM_FIELDS, FIELD_NAME from S_CUSTOM_FIELDS WHERE ACTIVE = '1' AND SECTION = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY PK_DEPARTMENT ASC, FIELD_NAME ASC ");
										$index = 1;
										while (!$res_dd->EOF) { ?>
										<div class="row" style="margin-bottom:5px" >
											<div class="col-md-2">
												<? $index++;
												$db_res = check_field($db,$res_dd->fields['PK_CUSTOM_FIELDS'],"CUSTOM_FIELDS"); ?>
												<input type="checkbox" <? if($db_res[0] > 0 ) echo "checked='checked'"; ?> name="CUSTOM_FIELDS_<?=$index?>" id="CUSTOM_FIELDS_<?=$index?>" value="<?=$res_dd->fields['PK_CUSTOM_FIELDS'] ?>" onclick="enable_size('CUSTOM_FIELDS','<?=$index?>')" />
												
												<input type="hidden" name="CUSTOM_FIELDS_HID[]" value="<?=$index?>" >
												<input type="hidden" name="CUSTOM_FIELDS_PK_CUSTOM_REPORT_DETAIL_<?=$index?>" value="<?=$db_res[2]?>" />
											</div>
											<div class="col-md-6">
												<?=$res_dd->fields['FIELD_NAME']?>
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="CUSTOM_FIELDS_SORT_ORDER_<?=$index?>" id="CUSTOM_FIELDS_SORT_ORDER_<?=$index?>"  class="form-control sort_cls" value="<?=$db_res[3]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_disp()" />
											</div>
											<div class="col-md-2">
												<input type="number" min="0" oninput="validity.valid||(value='');" name="CUSTOM_FIELDS_SIZE_<?=$index?>" id="CUSTOM_FIELDS_SIZE_<?=$index?>"  class="form-control size_cls" value="<?=$db_res[1]?>" <? if($db_res[0] == 0 ) echo "disabled"; ?> onchange="calc_size()" />
											</div>
										</div>
										<?	$res_dd->MoveNext();
										}	?>
										
									</div>
									
									<hr />
									<div class="row" style="margin-bottom:5px" >
										<div class="col-md-2">
										</div>
										<div class="col-md-8">
											<?=TOTAL_SIZE?>
										</div>
										<div class="col-md-2" id="TOTAL_SIZE_DIV" >
										</div>
									</div>
									
									<div class="row" style="margin-bottom:5px" >
										<div class="col-md-2">
										</div>
										<div class="col-md-8">
											<?=BALANCE_SIZE?>
										</div>
										<div class="col-md-2" id="BALANCE_DIV" >
										</div>
									</div>
									
								</div>
							</div>
						</div>
						<div class="col-6">
							<div class="row">
								<div class="col-12">
									<div class="card">
										<div class="card-body">
											<div class="row">
												<div class="col-md-12">
													<h4 class="text-themecolor">
														<?=FILTER?>
													</h4>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="d-flex ">
														<div class="col-12 col-sm-6 form-group">
															<?=CAMPUS ?>
															<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="doSearch()">
																<? $PK_CAMPUS_ARR = explode(",",$PK_CAMPUS);
																$res_type = $db->Execute("select CAMPUS_CODE, PK_CAMPUS, ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CAMPUS_CODE ASC");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['CAMPUS_CODE'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)";
														
																	$selected = "";
																	if(!empty($PK_CAMPUS_ARR)){
																		foreach($PK_CAMPUS_ARR as $PK_CAMPUS1){
																			if($res_type->fields['PK_CAMPUS'] == $PK_CAMPUS1)
																				$selected = "selected";
																		}
																	} ?>
																	<option value="<?=$res_type->fields['PK_CAMPUS']?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
														</div>
														<div class="col-12 col-sm-6 form-group">
															<?=PROGRAM?>
															<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" >
																<? $PK_CAMPUS_PROGRAM_ARR = explode(",",$PK_CAMPUS_PROGRAM);
																$res_type = $db->Execute("select PK_CAMPUS_PROGRAM, CONCAT(CODE,' - ',DESCRIPTION) as CODE, ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, CONCAT(CODE,' - ',DESCRIPTION) ASC ");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['CODE'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)";
																		
																	$selected = "";
																	if(!empty($PK_CAMPUS_PROGRAM_ARR)){
																		foreach($PK_CAMPUS_PROGRAM_ARR as $PK_CAMPUS_PROGRAM_1) {
																			if($PK_CAMPUS_PROGRAM_1 == $res_type->fields['PK_CAMPUS_PROGRAM']) {
																				$selected = "selected";
																				break;
																			}
																		}
																	} ?>
																	<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM'] ?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
														</div>
													</div>
													
													<div class="d-flex ">
														<div class="col-12 col-sm-6 form-group">
															<?=FIRST_TERM_DATE ?>
															<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control">
																<? $PK_TERM_MASTER_ARR = explode(",",$PK_TERM_MASTER);
																$res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC, BEGIN_DATE DESC");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['TERM_DESCRIPTION'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)";
																		
																	$selected = "";
																	if(!empty($PK_TERM_MASTER_ARR)){
																		foreach($PK_TERM_MASTER_ARR as $PK_TERM_MASTER_1) {
																			if($PK_TERM_MASTER_1 == $res_type->fields['PK_TERM_MASTER']) {
																				$selected = "selected";
																				break;
																			}
																		}
																	} ?>
																	<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <?=$selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
														</div>
														<div class="col-12 col-sm-6 form-group">
															<?=FUNDING ?>
															<select id="PK_FUNDING" name="PK_FUNDING[]" multiple class="form-control">
																<? $PK_FUNDING_ARR = explode(",",$PK_FUNDING);
																$res_type = $db->Execute("select * from M_FUNDING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, FUNDING ASC");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['FUNDING'].' - '.$res_type->fields['DESCRIPTION'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)";
																		
																	$selected = "";
																	if(!empty($PK_FUNDING_ARR)){
																		foreach($PK_FUNDING_ARR as $PK_FUNDING) {
																			if($PK_FUNDING == $res_type->fields['PK_FUNDING']) {
																				$selected = "selected";
																				break;
																			}
																		}
																	} ?>
																	<option value="<?=$res_type->fields['PK_FUNDING']?>" <?=$selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
														</div>
													</div>
													
													<div class="d-flex ">
														<div class="col-12 col-sm-6 form-group">
															<?=GENDER?>
															<select id="GENDER" name="GENDER" class="form-control">
																<option value=""></option>
																<? /* Ticket # 1769   */
																$res_type = $db->Execute("select PK_GENDER, GENDER, ACTIVE from Z_GENDER WHERE 1=1 ORDER BY ACTIVE DESC ");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['GENDER'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)";  ?>
																	<option value="<?=$res_type->fields['PK_GENDER'] ?>" <? if($res_type->fields['PK_GENDER'] == $GENDER) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} /* Ticket # 1769   */ ?>
															</select>
														</div>
														<div class="col-12 col-sm-6 form-group">
															<?=LEAD_SOURCE ?>
															<select id="PK_LEAD_SOURCE" name="PK_LEAD_SOURCE[]" multiple class="form-control">
																<? $PK_LEAD_SOURCE_ARR = explode(",",$PK_LEAD_SOURCE);
																$res_type = $db->Execute("select PK_LEAD_SOURCE, LEAD_SOURCE, DESCRIPTION, ACTIVE from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, LEAD_SOURCE ASC");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['LEAD_SOURCE'].' - '.$res_type->fields['DESCRIPTION'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)";
																		
																	$selected = "";
																	if(!empty($PK_LEAD_SOURCE_ARR)){
																		foreach($PK_LEAD_SOURCE_ARR as $PK_LEAD_SOURCE) {
																			if($PK_LEAD_SOURCE == $res_type->fields['PK_LEAD_SOURCE']) {
																				$selected = "selected";
																				break;
																			}
																		}
																	} ?>
																	<option value="<?=$res_type->fields['PK_LEAD_SOURCE']?>" <?=$selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
														</div>
													</div>
													
													<div class="d-flex ">
														<div class="col-12 col-sm-6 form-group">
															<?=SESSION ?>
															<select id="PK_SESSION" name="PK_SESSION[]" multiple class="form-control">
																<? $PK_SESSION_ARR = explode(",",$PK_SESSION);
																$res_type = $db->Execute("select PK_SESSION, SESSION, ACTIVE from M_SESSION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, DISPLAY_ORDER ASC");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['SESSION'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)";
																		
																	$selected = "";
																	if(!empty($PK_SESSION_ARR)){
																		foreach($PK_SESSION_ARR as $PK_SESSION) {
																			if($PK_SESSION == $res_type->fields['PK_SESSION']) {
																				$selected = "selected";
																				break;
																			}
																		}
																	} ?>
																	<option value="<?=$res_type->fields['PK_SESSION']?>" <?=$selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
															
														</div>
														<div class="col-12 col-sm-6 form-group">
															<?=SSN?>
															<select id="SSN" name="SSN" class="form-control">
																<option></option>
																<option value="1" <? if($SSN == 1) echo "selected"; ?> >With SSN</option>
																<option value="2" <? if($SSN == 2) echo "selected"; ?> >Without SSN</option>
															</select>
														</div>
													</div>
													
													<div class="d-flex ">
														<div class="col-12 col-sm-6 form-group">
															<?=STUDENT_GROUP ?>
															<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control">
																<? $PK_STUDENT_GROUP_ARR = explode(",",$PK_STUDENT_GROUP);
																$res_type = $db->Execute("select PK_STUDENT_GROUP, STUDENT_GROUP, ACTIVE from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, STUDENT_GROUP ASC");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['STUDENT_GROUP'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)";
																		
																	$selected = "";
																	if(!empty($PK_STUDENT_GROUP_ARR)){
																		foreach($PK_STUDENT_GROUP_ARR as $PK_STUDENT_GROUP) {
																			if($PK_STUDENT_GROUP == $res_type->fields['PK_STUDENT_GROUP']) {
																				$selected = "selected";
																				break;
																			}
																		}
																	} ?>
																	<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" <?=$selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
														</div>
														<div class="col-12 col-sm-6 form-group">
															<?=STUDENT_STATUS ?>
															<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control">
																<? $PK_STUDENT_STATUS_ARR = explode(",",$PK_STUDENT_STATUS);
																$res_type = $db->Execute("select PK_STUDENT_STATUS, STUDENT_STATUS, DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, STUDENT_STATUS ASC");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)";
																		
																	$selected = "";
																	if(!empty($PK_STUDENT_STATUS_ARR)){
																		foreach($PK_STUDENT_STATUS_ARR as $PK_STUDENT_STATUS_1) {
																			if($PK_STUDENT_STATUS_1 == $res_type->fields['PK_STUDENT_STATUS']) {
																				$selected = "selected";
																				break;
																			}
																		}
																	} ?>
																	<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" <?=$selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
														</div>
													</div>
													
													<div class="d-flex ">
														<div class="col-12 col-sm-6 form-group">
															<?=ADMISSIONS_REP ?>
															<select id="PK_EMPLOYEE_MASTER" name="PK_EMPLOYEE_MASTER[]" multiple class="form-control">
																<? $PK_EMPLOYEE_MASTER_ARR = explode(",",$PK_EMPLOYEE_MASTER);
																$res_type = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, S_EMPLOYEE_MASTER.ACTIVE  from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = 2 AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER order by S_EMPLOYEE_MASTER.ACTIVE DESC, CONCAT(LAST_NAME,',  ', FIRST_NAME) ASC");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['NAME'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)";
																		
																	$selected = "";
																	if(!empty($PK_EMPLOYEE_MASTER_ARR)){
																		foreach($PK_EMPLOYEE_MASTER_ARR as $PK_EMPLOYEE_MASTER) {
																			if($PK_EMPLOYEE_MASTER == $res_type->fields['PK_EMPLOYEE_MASTER']) {
																				$selected = "selected";
																				break;
																			}
																		}
																	} ?>
																	<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" <?=$selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
														</div>
														<div class="col-12 col-sm-6 form-group">
														</div>
													</div>
														
													<div class="d-flex ">
														<div class="col-12 col-sm-6 form-group">
															<input id="LEAD_ENTRY_FROM_DATE" name="LEAD_ENTRY_FROM_DATE" type="text" class="form-control date" value="<?=$LEAD_ENTRY_FROM_DATE?>" >
															<span class="bar"></span> 
															<label for="LEAD_ENTRY_FROM_DATE"><?=LEAD_ENTRY_FROM_DATE?></label>
														</div>
														
														<div class="col-12 col-sm-6 form-group">
															<input id="LEAD_ENTRY_END_DATE" name="LEAD_ENTRY_END_DATE" type="text" class="form-control date" value="<?=$LEAD_ENTRY_END_DATE?>" >
															<span class="bar"></span> 
															<label for="LEAD_ENTRY_END_DATE"><?=LEAD_ENTRY_END_DATE?></label>
														</div>
													</div>
													
												</div>
											</div>
											
										</div>
									</div>
								</div>
								
								<div class="col-12">
									<div class="card">
										<div class="card-body">
											<div class="row">
												<div class="col-md-12">
													<h4 class="text-themecolor">
														<?=GROUP_BY?>
													</h4>
												</div>
											</div>
											<br />
											<div class="row">
												<div class="col-md-12">
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<select id="GROUP_BY_FIELD" name="GROUP_BY_FIELD" class="form-control">
																<option></option>
																<option value="1" <? if($GROUP_BY_FIELD == 1) echo "selected"; ?> ><?=CAMPUS?></option>
																<option value="2" <? if($GROUP_BY_FIELD == 2) echo "selected"; ?> ><?=FIRST_TERM_DATE?></option>
																<option value="3" <? if($GROUP_BY_FIELD == 3) echo "selected"; ?> ><?=FUNDING?></option>
																<option value="4" <? if($GROUP_BY_FIELD == 4) echo "selected"; ?> ><?=GENDER?></option>
																<option value="5" <? if($GROUP_BY_FIELD == 5) echo "selected"; ?> ><?=LEAD_SOURCE?></option>
																
																<option value="6" <? if($GROUP_BY_FIELD == 6) echo "selected"; ?> ><?=ADMISSIONS_REP?></option>
																<option value="7" <? if($GROUP_BY_FIELD == 7) echo "selected"; ?> ><?=PROGRAM?></option>
																<option value="8" <? if($GROUP_BY_FIELD == 8) echo "selected"; ?> ><?=SESSION?></option>
																<option value="9" <? if($GROUP_BY_FIELD == 9) echo "selected"; ?> ><?=STUDENT_GROUP?></option>
																<option value="10" <? if($GROUP_BY_FIELD == 10) echo "selected"; ?> ><?=STUDENT_STATUS?></option>
																
															</select>
															<span class="bar"></span> 
															<label for="GROUP_BY_FIELD"><?=GROUP_BY?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<input type="hidden" name="SAVE_CONTINUE" id="SAVE_CONTINUE" value="0" >
													<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_CONTINUE?></button>
													<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_EXIT?></button>
													<button onclick="validate_form(2)" type="button" class="btn waves-effect waves-light btn-info"><?=VIEW?></button>
													<button onclick="validate_form(3)" type="button" class="btn waves-effect waves-light btn-info"><?=EXPORT?></button>
													<button onclick="validate_form(4)" type="button" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
													<button type="button" onclick="window.location.href='manage_custom_report'" class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
												</div>
											</div>
										</div>
									</div>
								</div>
								
							</div>
						</div>
					</div>
				</form>
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
   <div class="modal" id="checkModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
		<div class="modal-dialog" role="document" >
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="exampleModalLabel1"><?=CONFIRMATION?></h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="form-group" id="duplicate_message" >
						<?=TOTAL_SIZE_EXCEED ?>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" onclick="conf_submit(1)" class="btn waves-effect waves-light btn-info"><?=PROCEED?></button>
					<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_submit(0)" ><?=CANCEL?></button>
				</div>
			</div>
		</div>
	</div>
		
	<? require_once("js.php"); ?>
	<script type="text/javascript" >
	function enable_size(type,id){
		if(document.getElementById(type+'_'+id).checked == true) {
			document.getElementById(type+'_SIZE_'+id).disabled  = false
			document.getElementById(type+'_SIZE_'+id).className	= 'form-control required-entry size_cls';
			document.getElementById(type+'_SIZE_'+id).value		= '5';
			
			document.getElementById(type+'_SORT_ORDER_'+id).disabled  = false
			//document.getElementById(type+'_SORT_ORDER_'+id).className	= 'form-control required-entry';
			document.getElementById(type+'_SORT_ORDER_'+id).className	= 'form-control';
			calc_size()
		} else {
			document.getElementById(type+'_SIZE_'+id).disabled 	= true
			document.getElementById(type+'_SIZE_'+id).value		= '';
			document.getElementById(type+'_SIZE_'+id).className	= 'form-control';
			
			
			document.getElementById(type+'_SORT_ORDER_'+id).disabled 	= true
			document.getElementById(type+'_SORT_ORDER_'+id).value		= '';
			document.getElementById(type+'_SORT_ORDER_'+id).className	= 'form-control';
			calc_size()
		}
	}
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		function validate_form(val){
			jQuery(document).ready(function($) {
				document.getElementById("SAVE_CONTINUE").value  = val;
				
				var valid = new Validation('form1', {onSubmit:false});
				var result = valid.validate();
				//alert(result)
				if(result == true) {
					var SIZE  = document.getElementsByClassName("size_cls");
					var total = 0;
					for(var i = 0 ; i < SIZE.length ; i++){
						if(SIZE[i].value != '')
							total += parseFloat(SIZE[i].value)
					}
					if(total > 100 && val == 4)
						show_message()
					else {
						$("#form1").removeClass('dirty');
						document.form1.submit();
					}
				}
			});
		}
		
		function calc_size(){
			var total_size 	= 100;
			var SIZE  		= document.getElementsByClassName("size_cls");
			var total 		= 0;
			for(var i = 0 ; i < SIZE.length ; i++){
				if(SIZE[i].value != '')
					total += parseFloat(SIZE[i].value)
			}
			total_size = parseFloat(total_size) - parseFloat(total)
			document.getElementById('TOTAL_SIZE_DIV').innerHTML = total+' %'
			document.getElementById('BALANCE_DIV').innerHTML 	= total_size+' %'
		}
		
		function show_message(){
			jQuery(document).ready(function($) { 
				$("#checkModal").modal()
			}).responseText;
		}
		
		function conf_submit(val){
			jQuery(document).ready(function($) {
				if(val == 1){
					$("#form1").removeClass('dirty');
					document.form1.submit();
				}
				$("#checkModal").modal("hide");
			});
		}
		
		jQuery(document).ready(function($) {
			calc_size()
		});
	</script>
	
	<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
	<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.select2').select2();
		});
		
		var acc = document.getElementsByClassName("accordion");
		var i;

		for (i = 0; i < acc.length; i++) {
			acc[i].addEventListener("click", function() {
				this.classList.toggle("acc_active");
				var panel = this.nextElementSibling;
				if (panel.style.maxHeight){
					panel.style.maxHeight = null;
				} else {
					panel.style.maxHeight = panel.scrollHeight + "px";
				} 
			});
		}
	</script>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) { 
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
		});
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=CAMPUS?>',
				nonSelectedText: '',
				numberDisplayed: 1,
				nSelectedText: '<?=CAMPUS?> selected'
			});
			$('#PK_CAMPUS_PROGRAM').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=PROGRAM?>',
				nonSelectedText: '',
				numberDisplayed: 1,
				nSelectedText: '<?=PROGRAM?> selected'
			});
			$('#PK_TERM_MASTER').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=FIRST_TERM_DATE?>',
				nonSelectedText: '',
				numberDisplayed: 1,
				nSelectedText: '<?=FIRST_TERM_DATE?> selected'
			});
			$('#PK_FUNDING').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=FUNDING?>',
				nonSelectedText: '',
				numberDisplayed: 1,
				nSelectedText: '<?=FUNDING?> selected'
			});
			$('#PK_LEAD_SOURCE').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=LEAD_SOURCE?>',
				nonSelectedText: '',
				numberDisplayed: 1,
				nSelectedText: '<?=LEAD_SOURCE?> selected'
			});
			$('#PK_SESSION').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=SESSION?>',
				nonSelectedText: '',
				numberDisplayed: 1,
				nSelectedText: '<?=SESSION?> selected'
			});
			$('#PK_STUDENT_GROUP').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=STUDENT_GROUP?>',
				nonSelectedText: '',
				numberDisplayed: 1,
				nSelectedText: '<?=STUDENT_GROUP?> selected'
			});
			$('#PK_STUDENT_STATUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=STUDENT_STATUS?>',
				nonSelectedText: '',
				numberDisplayed: 1,
				nSelectedText: '<?=STUDENT_STATUS?> selected'
			});
			$('#PK_EMPLOYEE_MASTER').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?=ADMISSIONS_REP?>',
				nonSelectedText: '',
				numberDisplayed: 1,
				nSelectedText: '<?=ADMISSIONS_REP?> selected', //Ticket # 1593
				enableCaseInsensitiveFiltering: true, //Ticket # 1593
			});
			
		});
	</script>
</body>

</html>