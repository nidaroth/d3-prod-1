<? 
require_once("../global/config.php"); 
require_once("../language/company.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}

if($_GET['act'] == 'user')	{
	$db->Execute("DELETE FROM S_COMPANY_CONTACT WHERE PK_COMPANY_CONTACT = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$_GET[id]'");
		
	header("location:company?id=".$_GET['id'].'&tab=contactsTab');
}
else if($_GET['act'] == 'job')	{
	$db->Execute("DELETE FROM S_COMPANY_JOB WHERE PK_COMPANY_JOB = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$_GET[id]'");
		
	header("location:company?id=".$_GET['id'].'&tab=jobsTab');
}
else if($_GET['act'] == 'event')	{
	$db->Execute("DELETE FROM S_COMPANY_EVENT WHERE PK_COMPANY_EVENT = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$_GET[id]'");
		
	header("location:company?id=".$_GET['id'].'&tab=eventsTab');
}

if(!empty($_POST)) {
	//echo "<pre>";print_r($_POST);exit;
	$frm_name       = $_POST['frm_name'];
	$SAVE_CONTINUE	= $_POST['SAVE_CONTINUE'];
	$current_tab   	= $_POST['current_tab'];
	$PK_CAMPUS_ARR	= $_POST['PK_CAMPUS'];
	unset($_POST['SAVE_CONTINUE']);
	unset($_POST['current_tab']);
	unset($_POST['frm_name']);
	unset($_POST['PK_CAMPUS']);
	
	if($frm_name   == 'company') {
		$COMPANY = $_POST;
		
		if($COMPANY['DATE_CREATED'] != '')
			$COMPANY['DATE_CREATED'] = date("Y-m-d",strtotime($COMPANY['DATE_CREATED']));
			
		if($_GET['id'] == '') {
			$COMPANY['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
			$COMPANY['CREATED_BY']  = $_SESSION['PK_USER'];
			$COMPANY['CREATED_ON']  = date("Y-m-d H:i");
			db_perform('S_COMPANY', $COMPANY, 'insert');
			$PK_COMPANY 			= $db->insert_ID();
		} 
		else {
			$PK_COMPANY 			= $_GET['id'];
			$COMPANY['EDITED_BY']   = $_SESSION['PK_USER'];
			$COMPANY['EDITED_ON']   = date("Y-m-d H:i");
			db_perform('S_COMPANY', $COMPANY, 'update'," PK_COMPANY = '$PK_COMPANY' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
		
		$_SESSION['COMPANY_NAME'] 	= $_POST['COMPANY_NAME'];
		
		foreach($PK_CAMPUS_ARR as $PK_CAMPUS){
			$res = $db->Execute("SELECT PK_COMPANY_CAMPUS FROM S_COMPANY_CAMPUS WHERE PK_COMPANY = '$PK_COMPANY' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$PK_CAMPUS' "); 
			if($res->RecordCount() == 0) {
				$COMPANY_CAMPUS['PK_ACCOUNT'] 	= $_SESSION['PK_ACCOUNT'];
				$COMPANY_CAMPUS['PK_COMPANY']	= $PK_COMPANY;
				$COMPANY_CAMPUS['PK_CAMPUS'] 	= $PK_CAMPUS;
				$COMPANY_CAMPUS['CREATED_BY']  	= $_SESSION['PK_USER'];
				$COMPANY_CAMPUS['CREATED_ON'] 	= date("Y-m-d H:i");
				db_perform('S_COMPANY_CAMPUS', $COMPANY_CAMPUS, 'insert');
				$PK_COMPANY_CAMPUS_ARR[] = $db->insert_ID();
			} else
				$PK_COMPANY_CAMPUS_ARR[] = $res->fields['PK_COMPANY_CAMPUS'];
		}
		
		$cond = "";
		if(!empty($PK_COMPANY_CAMPUS_ARR))
			$cond = " AND PK_COMPANY_CAMPUS NOT IN (".implode(",",$PK_COMPANY_CAMPUS_ARR).") ";
			
		$db->Execute("DELETE FROM S_COMPANY_CAMPUS WHERE PK_COMPANY = '$PK_COMPANY' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond"); 
			
		if($SAVE_CONTINUE == 0) {
			header("location:manage_company");
		} 
		else {
			header("location:company?id=".$PK_COMPANY."&tab=homeTab");
		}
	} else if($frm_name   == 'company_question') {
		if(isset($_POST['PK_PLACEMENT_COMPANY_QUESTIONNAIRE']) && count($_POST['PK_PLACEMENT_COMPANY_QUESTIONNAIRE']) > 0) {
			for($i=1;$i<=count($_POST['PK_PLACEMENT_COMPANY_QUESTIONNAIRE']);$i++) {
				$COMPANY_QUESTIONNAIRE['PK_COMPANY_QUESTIONNAIRE'] 			 = $_POST['PK_COMPANY_QUESTIONNAIRE'][$i];
				$COMPANY_QUESTIONNAIRE['PK_PLACEMENT_COMPANY_QUESTIONNAIRE'] = $_POST['PK_PLACEMENT_COMPANY_QUESTIONNAIRE'][$i];
				$COMPANY_QUESTIONNAIRE['ANSWER'] 							 = $_POST['ANSWER'][$i];

				if($COMPANY_QUESTIONNAIRE['PK_COMPANY_QUESTIONNAIRE'] != '') {
					$COMPANY_QUESTIONNAIRE['EDITED_BY']  = $_SESSION['PK_USER'];
					$COMPANY_QUESTIONNAIRE['EDITED_ON']  = date("Y-m-d H:i");
					db_perform('S_COMPANY_QUESTIONNAIRE', $COMPANY_QUESTIONNAIRE, 'update'," PK_COMPANY_QUESTIONNAIRE = '$COMPANY_QUESTIONNAIRE[PK_COMPANY_QUESTIONNAIRE]' AND PK_COMPANY = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
				}
				else if($COMPANY_QUESTIONNAIRE['PK_COMPANY_QUESTIONNAIRE'] == '' && $COMPANY_QUESTIONNAIRE['ANSWER'] != '') {
					unset($COMPANY_QUESTIONNAIRE['PK_COMPANY_QUESTIONNAIRE']);

					$COMPANY_QUESTIONNAIRE['PK_COMPANY']  = $_GET['id'];
					$COMPANY_QUESTIONNAIRE['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
					$COMPANY_QUESTIONNAIRE['CREATED_BY']  = $_SESSION['PK_USER'];
					$COMPANY_QUESTIONNAIRE['CREATED_ON']  = date("Y-m-d H:i");
					db_perform('S_COMPANY_QUESTIONNAIRE	', $COMPANY_QUESTIONNAIRE, 'insert');
				}
				
				unset($COMPANY_QUESTIONNAIRE);
			}

			header("location:company?id=".$_GET['id']."&tab=homeTab");
		}
		else {
			header("location:manage_company");
		}
	}
}

if($_GET['id'] == '') {
	$PK_PLACEMENT_TYPE 					= '';
	$COMPANY_NAME 						= '';
	$ADDRESS	 						= '';
	$ADDRESS_1	 						= '';
	$CITY	 							= '';
	$PK_STATES 							= '';
	$ZIP	 							= '';
	$PK_COUNTRY							= '';
	$PHONE	 							= '';
	$EMAIL	 							= '';
	$FAX	 							= '';
	$WEBSITE 							= '';
	$PK_COMPANY_CONTACT					= '';
	$PK_PLACEMENT_COMPANY_QUESTION_GROUP= '';
	$PK_COMPANY_ADVISOR					= '';
	$NOTES								= '';
	$ACTIVE  		   					= '';
	
	$DATE_CREATED						= '';
	$PK_COMPANY_SOURCE					= '';
} 
else {
	$res = $db->Execute("SELECT * FROM S_COMPANY WHERE PK_COMPANY = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	
	if($res->RecordCount() == 0) {
		header("location:company?id='$_GET[id]'&tab=homeTab");
		exit;
	}
	
	$PK_PLACEMENT_TYPE 							= $res->fields['PK_PLACEMENT_TYPE'];
	$PK_PLACEMENT_COMPANY_STATUS				= $res->fields['PK_PLACEMENT_COMPANY_STATUS'];
	$COMPANY_NAME 								= $res->fields['COMPANY_NAME'];
	$ADDRESS  									= $res->fields['ADDRESS'];
	$ADDRESS_1  								= $res->fields['ADDRESS_1'];
	$CITY  										= $res->fields['CITY'];
	$PK_STATES  								= $res->fields['PK_STATES'];
	$ZIP  										= $res->fields['ZIP'];
	$PK_COUNTRY  								= $res->fields['PK_COUNTRY'];
	$PHONE  									= $res->fields['PHONE'];
	$EMAIL	 									= $res->fields['EMAIL'];
	$FAX  										= $res->fields['FAX'];
	$WEBSITE 									= $res->fields['WEBSITE'];
	$PK_PLACEMENT_COMPANY_QUESTION_GROUP		= $res->fields['PK_PLACEMENT_COMPANY_QUESTION_GROUP'];
	$PK_COMPANY_CONTACT							= $res->fields['PK_COMPANY_CONTACT'];
	$PK_COMPANY_ADVISOR							= $res->fields['PK_COMPANY_ADVISOR'];
	$NOTES										= $res->fields['NOTES'];
	$ACTIVE  		   							= $res->fields['ACTIVE'];
	
	$DATE_CREATED						= $res->fields['DATE_CREATED'];
	$PK_COMPANY_SOURCE					= $res->fields['PK_COMPANY_SOURCE'];
	
	if($DATE_CREATED != '' && $DATE_CREATED != '0000-00-00')
		$DATE_CREATED = date("m/d/Y",strtotime($DATE_CREATED));
	else
		$DATE_CREATED = '';
}

if($_GET['tab'] == '' || $_GET['tab'] == 'homeTab')
	$home_tab  = 'active';
else if($_GET['tab'] == 'contactsTab')
	$user_tab  = 'active';
else if($_GET['tab'] == 'jobsTab')
	$job_tab  = 'active';
else if($_GET['tab'] == 'eventsTab')
	$event_tab  = 'active';
else if($_GET['tab'] == 'questsTab')
	$quest_tab  = 'active';
else
	$home_tab  = 'active';
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
	<title><?=COMPANY_PAGE_TITLE?> | <?=$title?></title>
	<style>
	li > a > label{position: unset !important;}
	/* Ticket #1694  */
	.dropdown-menu>li>a { white-space: nowrap; }
	.option_red > a > label{color:red !important}
	/* Ticket #1694  */
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
					<!-- Ticket # 1451 -->
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor">
							<? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=COMPANY_PAGE_TITLE?> 
							<? if($_GET['id'] != '') echo " - ".$COMPANY_NAME; ?>
						</h4>
                    </div>
					<!-- Ticket # 1451 -->
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<ul class="nav nav-tabs customtab" role="tablist">
                                <li class="nav-item"> <a class="nav-link <?=$home_tab?>" data-toggle="tab" href="#homeTab" role="tab"><span class="hidden-sm-up"><i class="ti-homeTab"></i></span> <span class="hidden-xs-down"><?=TAB_INFO?></span></a> </li>
								<? if($_GET['id'] != ''){ ?>
                                <li class="nav-item"> <a class="nav-link <?=$user_tab?>" data-toggle="tab" href="#contactsTab" role="tab"><span class="hidden-sm-up"><i class="ti-email"></i></span> <span class="hidden-xs-down"><?=TAB_CONTACT?></span></a> </li>
								<li class="nav-item"> <a class="nav-link <?=$job_tab?>" data-toggle="tab" href="#jobsTab" role="tab"><span class="hidden-sm-up"><i class="ti-email"></i></span> <span class="hidden-xs-down"><?=JOB_CONTACT?></span></a> </li>
                                <li class="nav-item"> <a class="nav-link <?=$event_tab?>" data-toggle="tab" href="#eventsTab" role="tab"><span class="hidden-sm-up"><i class="ti-email"></i></span> <span class="hidden-xs-down"><?=EVENT_CONTACT?></span></a> </li>
                                <li class="nav-item"> <a class="nav-link <?=$quest_tab?>" data-toggle="tab" href="#questsTab" role="tab"><span class="hidden-sm-up"><i class="ti-email"></i></span> <span class="hidden-xs-down"><?=QUEST_CONTACT?></span></a> </li>
								<? } ?>
                            </ul>
                            <!-- Tab panes -->
							
								<div class="tab-content">
									<div class="tab-pane <?=$home_tab?>" id="homeTab" role="tabpanel">
										<div class="p-20">
											<form class="floating-labels m-t-40" method="post" name="form1" id="form1" autocomplete="off" >
												<input value="company" type="hidden" name="frm_name">
												
												<div class="d-flex flex-wrap p-b-10">
													<div class="col-6 col-sm-6 form-group">
														<div class="row">
															<div class="col-12 col-sm-12 form-group">
																<input id="DATE_CREATED" tabindex="1" name="DATE_CREATED" type="text" class="form-control date" value="<?=$DATE_CREATED?>">
																<span class="bar"></span> 
																<label for="DATE_CREATED"><?=DATE_CREATED?></label>
															</div>
														</div>
														
														<div class="row">
															<div class="col-12 col-sm-12 form-group">
																<input id="COMPANY_NAME" tabindex="1" name="COMPANY_NAME" type="text" class="form-control required-entry" value="<?=$COMPANY_NAME?>">
																<span class="bar"></span> 
																<label for="COMPANY_NAME"><?=COMPANY_NAME?></label>
															</div>
														</div>
														
														<div class="row">
															<div class="col-12 col-sm-6 form-group">
																<input id="PHONE" name="PHONE" tabindex="2" type="text" class="form-control phone-inputmask " value="<?=$PHONE?>">
																<span class="bar"></span> 
																<label for="PHONE"><?=PHONE?></label>
															</div>
															
															<div class="col-12 col-sm-6 form-group">
																<input id="FAX" name="FAX" tabindex="3" type="text" class="form-control phone-inputmask" value="<?=$FAX?>">
																<span class="bar"></span> 
																<label for="FAX"><?=FAX?></label>
															</div>
														</div>

														<div class="row">
															<div class="col-12 col-sm-12 form-group">
																<input id="EMAIL" tabindex="4" name="EMAIL" type="text" class="form-control validate-email" value="<?=$EMAIL?>">
																<span class="bar"></span>
																<label for="EMAIL"><?=EMAIL?></label>
															</div>
														</div>
														
														<div class="row">
															<div class="col-12 col-sm-12 form-group">
																<input id="ADDRESS" tabindex="4" name="ADDRESS" type="text" class="form-control " value="<?=$ADDRESS?>">
																<span class="bar"></span>
																<label for="ADDRESS"><?=ADDRESS?></label>
															</div>
														</div>
														
														<div class="row">
															<div class="col-12 col-sm-12 form-group">
																<input id="ADDRESS_1" tabindex="5" name="ADDRESS_1" type="text" class="form-control " value="<?=$ADDRESS_1?>">
																<span class="bar"></span>
																<label for="ADDRESS_1"><?=ADDRESS_1?></label>
															</div>
														</div>
														
														<div class="row">
															<div class="col-12 col-sm-6 form-group">
																<input id="CITY" tabindex="6" name="CITY" type="text" class="form-control " value="<?=$CITY?>">
																<span class="bar"></span> 
																<label for="CITY"><?=CITY?></label>
															</div>
															<div class="col-12 col-sm-6 form-group">
																<select id="PK_STATES" tabindex="7" name="PK_STATES" class="form-control "  > <!-- onchange="get_country(this.value,'PK_COUNTRY')" -->
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
														
														<div class="row">
															<div class="col-12 col-sm-6 form-group">
														<input id="ZIP" tabindex="8" name="ZIP" type="text" class="form-control " value="<?=$ZIP?>">
																<span class="bar"></span> 
																<label for="ZIP"><?=ZIP?></label>
															</div>
															<div class="col-12 col-sm-6 form-group" id="PK_COUNTRY_LABEL">
																<div id="PK_COUNTRY_DIV" >
																	<select id="PK_COUNTRY" tabindex="9" name="PK_COUNTRY" class="form-control ">
																		<option selected></option>
																			<?$res_type1 = $db->Execute("select PK_COUNTRY, NAME from Z_COUNTRY WHERE ACTIVE = '1' ORDER BY NAME ASC ");
																		    while (!$res_type1->EOF) { ?>
																			 <option value="<?=$res_type1->fields['PK_COUNTRY'] ?>" <? if($PK_COUNTRY == $res_type1->fields['PK_COUNTRY']) echo "selected"; ?> ><?=$res_type1->fields['NAME']?></option>
																		    <?	$res_type1->MoveNext();
																		    }
																		    ?>
																	</select>
																</div>
																<span class="bar"></span> 
																<label for="PK_COUNTRY"><?=COUNTRY?></label>
															</div>
														</div>
													</div>
													
													<div class="col-6 col-sm-6 form-group">
														<div class="row" style="margin-bottom: 25px;" >
															<div class="col-md-12">
																<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
																	<? /* Ticket #1694  */
																	$res_type = $db->Execute("select CAMPUS_CODE, PK_CAMPUS, ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CAMPUS_CODE ASC");
																	while (!$res_type->EOF) { 
																		$option_label = $res_type->fields['CAMPUS_CODE'];
																		if($res_type->fields['ACTIVE'] == 0)
																			$option_label .= " (Inactive)";
																				
																		$selected = '';
																		$PK_CAMPUS = $res_type->fields['PK_CAMPUS'];
																		$res = $db->Execute("select PK_COMPANY_CAMPUS FROM S_COMPANY_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_COMPANY = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
																		if($res->RecordCount() > 0 || ($res_type->RecordCount() == 1 && $_GET['id'] == ''))
																			$selected = 'selected'; ?>
																		<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" <?=$selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																	<?	$res_type->MoveNext();
																	} /* Ticket #1694  */ ?>
																</select>
															</div>
														</div>
														
														<div class="row">
															<div class="col-12 col-sm-12 form-group">
																<select id="PK_COMPANY_SOURCE" tabindex="10"  name="PK_COMPANY_SOURCE" class="form-control" >
																	<option selected></option>
																	<? $res_type = $db->Execute("select PK_COMPANY_SOURCE, COMPANY_SOURCE from M_COMPANY_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY COMPANY_SOURCE ASC ");
																	while (!$res_type->EOF) { ?>
																		<option value="<?=$res_type->fields['PK_COMPANY_SOURCE'] ?>" <? if($PK_COMPANY_SOURCE == $res_type->fields['PK_COMPANY_SOURCE']) echo "selected"; ?> ><?=$res_type->fields['COMPANY_SOURCE']?></option>
																	<?	$res_type->MoveNext();
																	} ?>
																</select>
																<span class="bar"></span> 
																<label for="PK_COMPANY_SOURCE"><?=COMPANY_SOURCE?></label>
															</div>
														</div>
														
														<div class="row">
															<div class="col-12 col-sm-12 form-group">
																<select id="PK_PLACEMENT_TYPE" tabindex="10"  name="PK_PLACEMENT_TYPE" class="form-control" >
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
														
														<div class="row">
															<div class="col-12 col-sm-12 form-group">
																<select id="PK_PLACEMENT_COMPANY_STATUS" tabindex="11" name="PK_PLACEMENT_COMPANY_STATUS" class="form-control">
																	<option selected></option>
																	<? $res_type = $db->Execute("select PK_PLACEMENT_COMPANY_STATUS, PLACEMENT_COMPANY_STATUS from M_PLACEMENT_COMPANY_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY PLACEMENT_COMPANY_STATUS ASC ");
																	while (!$res_type->EOF) { ?>
																		<option value="<?=$res_type->fields['PK_PLACEMENT_COMPANY_STATUS'] ?>" <? if($PK_PLACEMENT_COMPANY_STATUS == $res_type->fields['PK_PLACEMENT_COMPANY_STATUS']) echo "selected"; ?> ><?=$res_type->fields['PLACEMENT_COMPANY_STATUS']?></option>
																	<?	$res_type->MoveNext();
																	} ?>
																</select>
																<span class="bar"></span> 
																<label for="PK_PLACEMENT_COMPANY_STATUS"><?=PLACEMENT_COMPANY_STATUS?></label>
															</div>
														</div>
														
														<div class="row">
															<div class="col-12 col-sm-12 form-group">
																<input id="WEBSITE" tabindex="12" name="WEBSITE" type="text" class="form-control " value="<?=$WEBSITE?>">
																<span class="bar"></span> 
																<label for="WEBSITE"><?=WEBSITE?></label>
															</div>
														</div>
														
														<div class="row">
															<div class="col-12 col-sm-12 form-group">
																<select id="PK_PLACEMENT_COMPANY_QUESTION_GROUP" tabindex="13" name="PK_PLACEMENT_COMPANY_QUESTION_GROUP" class="form-control" >
																	<option selected></option>
																	<? $res_type = $db->Execute("select PK_PLACEMENT_COMPANY_QUESTION_GROUP, PLACEMENT_COMPANY_QUESTION_GROUP from M_PLACEMENT_COMPANY_QUESTION_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY PLACEMENT_COMPANY_QUESTION_GROUP ASC ");
																	while (!$res_type->EOF) { ?>
																		<option value="<?=$res_type->fields['PK_PLACEMENT_COMPANY_QUESTION_GROUP'] ?>" <? if($PK_PLACEMENT_COMPANY_QUESTION_GROUP == $res_type->fields['PK_PLACEMENT_COMPANY_QUESTION_GROUP']) echo "selected"; ?> ><?=$res_type->fields['PLACEMENT_COMPANY_QUESTION_GROUP']?></option>
																	<?	$res_type->MoveNext();
																	} ?>
																</select>
																<span class="bar"></span> 
																<label for="PK_PLACEMENT_COMPANY_QUESTION_GROUP"><?=PLACEMENT_COMPANY_QUESTIONNAIRE?></label>
															</div>
														</div>
														
														<div class="row">
															<div class="col-12 col-sm-12 form-group">
																<input id="NOTES" tabindex="14" name="NOTES" type="text" class="form-control " value="<?=$NOTES?>">
																<span class="bar"></span>
																<label for="NOTES"><?=NOTES?></label>
															</div>
														</div>
														
														<? if($_GET[id] != '') { ?>
														<div class="row">
															<div class="col-12 col-sm-6 form-group p-b-10">
																<select id="PK_COMPANY_CONTACT" tabindex="15" name="PK_COMPANY_CONTACT" class="form-control" >
																	<option selected></option>
																		<? $res_type = $db->Execute("select PK_COMPANY_CONTACT, NAME from S_COMPANY_CONTACT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COMPANY = '$_GET[id]' AND ACTIVE = '1' ORDER BY NAME ASC ");
																	while (!$res_type->EOF) { ?>
																		<option value="<?=$res_type->fields['PK_COMPANY_CONTACT'] ?>" <? if($PK_COMPANY_CONTACT == $res_type->fields['PK_COMPANY_CONTACT']) echo "selected"; ?> ><?=$res_type->fields['NAME']?></option>
																	<?	$res_type->MoveNext();
																	} ?>
																</select>
																<span class="bar"></span> 
																<label for="PK_COMPANY_CONTACT"><?=MAIN_CONTACT?></label>
															</div>
															<div class="col-12 col-sm-6 form-group p-b-10">
																	<select id="PK_COMPANY_ADVISOR" tabindex="16" name="PK_COMPANY_ADVISOR" class="form-control" >
																		<option selected></option>
																		<? $res_type = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, S_EMPLOYEE_MASTER.ACTIVE from S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by S_EMPLOYEE_MASTER.ACTIVE DESC, CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");
																		while (!$res_type->EOF) { 
																			$option_label = $res_type->fields['NAME'];
																			if($res_type->fields['ACTIVE'] == 0)
																				$option_label .= " (Inactive)"; ?>
																			<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER'] ?>" <? if($PK_COMPANY_ADVISOR == $res_type->fields['PK_EMPLOYEE_MASTER']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																		<?	$res_type->MoveNext();
																		} ?>
																	</select>
																	<span class="bar"></span> 
																	<label for="PK_COMPANY_ADVISOR"><?=COMPANY_ADVISOR?></label>
															</div>
														</div>
														<? } ?>	
													</div>
												</div>
											
												<? if($_GET['id'] != ''){ ?>
												<div class="d-flex flex-wrap">
													<div class="col-12 col-sm-6 form-group m-b-40">
														<div class="row">
															<div class="custom-control col-md-2 pt-1"><?=ACTIVE?></div>
															<div class="custom-control custom-radio col-md-1">
																<input type="radio" tabindex="17" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
																<label class="custom-control-label" for="customRadio11">Yes</label>
															</div>
															<div class="custom-control custom-radio col-md-1 ml-2">
																<input type="radio" tabindex="18" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
																<label class="custom-control-label ml-2" for="customRadio22">No</label>
															</div>
														</div>
													</div>
												</div>
												<? } ?>

												<div class="col-sm-7 p-b-25">
													<div class="d-flex justify-content-end submit-button-sec">
														<input type="hidden" name="SAVE_CONTINUE" id="SAVE_CONTINUE" value="0" />
														<input type="hidden" id="current_tab" name="current_tab" value="0" >
														<button onclick="validate_form(1)" tabindex="19" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_CONTINUE?></button>
														<button onclick="validate_form(0)" tabindex="20" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_EXIT?></button>
														
														<? if($_SESSION['PK_ROLES'] == 3) 
															$URL = "index";
														else
															$URL = "manage_company"; ?>
														<button type="button" tabindex="21" onclick="window.location.href='<?=$URL?>'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
													</div>
												</div>
												<div class="row">
													<div class="col-3 col-sm-3">
													</div>
												</div>
											</form>
										</div>
									</div>
									<? if($_GET['id'] != ''){ ?>
									<div class="tab-pane <?=$user_tab?>" id="contactsTab" role="tabpanel">
										<div class="row mt-2">
											<div class="col-md-10 align-self-center">
											</div>  
											<div class="col-md-2 align-self-center text-right pr-4 pt-4">
												<div class="d-flex justify-content-end align-items-center">
													<a href="company_contact?cid=<?=$_GET['id']?>" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>&nbsp;&nbsp;
												</div>
											</div>
										</div>
										
										<div class="table-responsive p-20">
											<table class="table table-hover">
												<thead>
													<tr>
														<th>#</th>
														<th><?=NAME?></th>
														<th><?=TITLE?></th>
														<th><?=DEPARTMENT?></th>
														<th><?=PHONE?></th>
														<th><?=PLACEMENT_TYPE?></th>
														<th><?=MAIN_CONTACT_1?></th>
														<th><?=OPTIONS?></th>
													</tr>
												</thead>
												<tbody>
													<? $res_type = $db->Execute("SELECT PK_COMPANY_CONTACT, NAME, TITLE, DEPARTMENT, PHONE, TYPE FROM S_COMPANY_CONTACT LEFT JOIN M_PLACEMENT_TYPE ON M_PLACEMENT_TYPE.PK_PLACEMENT_TYPE = S_COMPANY_CONTACT.PK_PLACEMENT_TYPE WHERE S_COMPANY_CONTACT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COMPANY_CONTACT.PK_COMPANY='$_GET[id]'");
													$i = 0;
													while (!$res_type->EOF) { 
														if($res_type->fields['IS_FACULTY'] == 1)
															$t = 2;
														else
															$t = 1;
														$i++; ?>
														<tr>
															<td><?=$i?></td>
															<td><?=$res_type->fields['NAME']?></td>
															<td><?=$res_type->fields['TITLE']?></td>
															<td><?=$res_type->fields['DEPARTMENT']?></td>
															<td><?=$res_type->fields['PHONE']?></td>
															<td><?=$res_type->fields['TYPE']?></td>
															<td>
																<? if($res_type->fields['PK_COMPANY_CONTACT'] == $PK_COMPANY_CONTACT)
																	echo "Y"; ?>
															</td>
															<td>
																<a href="company_contact?id=<?=$res_type->fields['PK_COMPANY_CONTACT']?>&cid=<?=$_GET['id']?>" title="<?=EDIT?>" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>
																<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_COMPANY_CONTACT']?>','user')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
															</td>
														</tr>
													<?	$res_type->MoveNext();
													} ?>
												</tbody>
											</table>
										</div>
									</div>

									<div class="tab-pane <?=$job_tab?>" id="jobsTab" role="tabpanel">
										<div class="row mt-2">
											<div class="col-md-10 align-self-center">
											</div>  
											<div class="col-md-2 align-self-center text-right pr-4 pt-4">
												<div class="d-flex justify-content-end align-items-center">
													<a href="company_job?cid=<?=$_GET['id']?>" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>&nbsp;&nbsp;
												</div>
											</div>
										</div>
										
										<div class="table-responsive p-20">
											<table class="table table-hover">
												<thead>
													<tr>
														<th><?=JOB_NUMBER?></th>
														<th><?=JOB_TITLE?></th>
														<th><?=PAY_AMOUNT?></th>
														<th><?=FULL_PART_TIME?></th>
														<th><?=JOB_POSTED?></th>
														<th><?=JOB_FILLED?></th>
														<th><?=JOB_CANCELED?></th>
														<th><?=OPEN_JOB?></th>
														<th><?=OPTIONS?></th>
													</tr>
												</thead>
												<tbody>
													<? $res_type = $db->Execute("SELECT OPEN_JOB,PK_COMPANY_JOB, JOB_NUMBER, JOB_TITLE, S_COMPANY_CONTACT.NAME AS PK_COMPANY_CONTACT, M_PLACEMENT_TYPE.DESCRIPTION AS PK_PLACEMENT_TYPE,Z_EMPLOYMENT_TYPE.EMPLOYMENT AS EMPLOYMENT,PAY_AMOUNT,PK_ENROLLMENT_STATUS,JOB_POSTED,JOB_FILLED,JOB_CANCELED FROM S_COMPANY_JOB LEFT JOIN Z_EMPLOYMENT_TYPE ON Z_EMPLOYMENT_TYPE.PK_EMPLOYMENT_TYPE = S_COMPANY_JOB.EMPLOYMENT LEFT JOIN M_PLACEMENT_TYPE ON M_PLACEMENT_TYPE.PK_PLACEMENT_TYPE = S_COMPANY_JOB.PK_PLACEMENT_TYPE LEFT JOIN S_COMPANY_CONTACT ON S_COMPANY_JOB.PK_COMPANY_CONTACT = S_COMPANY_CONTACT.PK_COMPANY_CONTACT WHERE S_COMPANY_JOB.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COMPANY_JOB.PK_COMPANY='$_GET[id]' ORDER BY OPEN_JOB,JOB_TITLE ASC"); //Ticket #1186
													$i = 0;
													while (!$res_type->EOF) { 
														if($res_type->fields['IS_FACULTY'] == 1)
															$t = 2;
														else
															$t = 1;
														$i++;
														
														$FULL_PART_TIME   = ($res_type->fields['PK_ENROLLMENT_STATUS'] == 1 ? 'Full Time' : 'Part Time');
														$PAY_AMOUNT   = ($res_type->fields['PAY_AMOUNT'] != 0 ? $res_type->fields['PAY_AMOUNT'] : '');
														$JOB_POSTED   = ($res_type->fields['JOB_POSTED'] != '0000-00-00' && $res_type->fields['JOB_POSTED'] != '' ? date("m/d/Y",strtotime($res_type->fields['JOB_POSTED'])) : '');
														$JOB_FILLED   = ($res_type->fields['JOB_FILLED'] != '0000-00-00' && $res_type->fields['JOB_FILLED'] != '' ? date("m/d/Y",strtotime($res_type->fields['JOB_FILLED'])) : '');
														$JOB_CANCELED   = ($res_type->fields['JOB_CANCELED'] != '0000-00-00' && $res_type->fields['JOB_CANCELED'] != '' ? date("m/d/Y",strtotime($res_type->fields['JOB_CANCELED'])) : '');
														$OPEN_JOB   = $res_type->fields['OPEN_JOB'];
														?>
														<tr>
															<td style="text-align: center;"><?=$res_type->fields['JOB_NUMBER']?></td>
															<td><?=$res_type->fields['JOB_TITLE']?></td>
															<td style="text-align: right;width:10%"><?=($PAY_AMOUNT != '' ? '$ '.$PAY_AMOUNT : '')?></td>
															<td><?=$FULL_PART_TIME?></td>
															<td><?=$JOB_POSTED?></td>
															<td><?=$JOB_FILLED?></td>
															<td><?=$JOB_CANCELED?></td>
															<td style="text-align: center;"><?=$OPEN_JOB?></td>
															<td>
																<a href="company_job?id=<?=$res_type->fields['PK_COMPANY_JOB']?>&cid=<?=$_GET['id']?>" title="<?=EDIT?>" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>
																<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_COMPANY_JOB']?>','job')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
															</td>
														</tr>
													<?	$res_type->MoveNext();
													} ?>
												</tbody>
											</table>
										</div>
									</div>

									<div class="tab-pane <?=$event_tab?>" id="eventsTab" role="tabpanel">
										<div class="row mt-2">
											<div class="col-md-10 align-self-center">
											</div>  
											<div class="col-md-2 align-self-center text-right pr-4 pt-4">
												<div class="d-flex justify-content-end align-items-center">
													<a href="company_event?cid=<?=$_GET['id']?>" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>&nbsp;&nbsp;
												</div>
											</div>
										</div>
										
										<div class="table-responsive p-20">
											<table class="table table-hover">
												<thead>
													<tr>
														<th>#</th>
														<th><?=EVENT_TYPE?></th>
														<th><?=EVENT_DATE?></th>
														<th><?=FOLLOW_UP_DATE?></th>
														<th><?=CONTACT?></th>
														<th><?=OPTIONS?></th>
													</tr>
												</thead>
												<tbody>
													<? $res_type = $db->Execute("SELECT PK_COMPANY_EVENT, PLACEMENT_COMPANY_EVENT_TYPE AS PK_PLACEMENT_COMPANY_EVENT_TYPE, EVENT_DATE, FOLLOW_UP_DATE, NAME AS PK_COMPANY_CONTACT_EMPLOYEE FROM S_COMPANY_EVENT LEFT JOIN S_COMPANY_CONTACT ON S_COMPANY_EVENT.PK_COMPANY_CONTACT = S_COMPANY_CONTACT.PK_COMPANY_CONTACT LEFT JOIN M_PLACEMENT_COMPANY_EVENT_TYPE ON S_COMPANY_EVENT.PK_PLACEMENT_COMPANY_EVENT_TYPE = M_PLACEMENT_COMPANY_EVENT_TYPE.PK_PLACEMENT_COMPANY_EVENT_TYPE WHERE S_COMPANY_EVENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COMPANY_EVENT.PK_COMPANY='$_GET[id]'");
													$i = 0;
													while (!$res_type->EOF) {
														if($res_type->fields['EVENT_DATE'] == '0000-00-00')
															$EVENT_DATE = '';
														else
															$EVENT_DATE = date("m/d/Y",strtotime($res_type->fields['EVENT_DATE']));
													
														if($res_type->fields['FOLLOW_UP_DATE'] == '0000-00-00')
															$FOLLOW_UP_DATE = '';
														else
															$FOLLOW_UP_DATE = date("m/d/Y",strtotime($res_type->fields['FOLLOW_UP_DATE']));

														if($res_type->fields['IS_FACULTY'] == 1)
															$t = 2;
														else
															$t = 1;
														$i++; ?>
														<tr>
															<td><?=$i?></td>
															<td><?=$res_type->fields['PK_PLACEMENT_COMPANY_EVENT_TYPE']?></td>
															<td><?=$EVENT_DATE?></td>
															<td><?=$FOLLOW_UP_DATE?></td>
															<td><?=$res_type->fields['PK_COMPANY_CONTACT_EMPLOYEE']?></td>
															<td>
																<a href="company_event?id=<?=$res_type->fields['PK_COMPANY_EVENT']?>&cid=<?=$_GET['id']?>" title="<?=EDIT?>" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>
																<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_COMPANY_EVENT']?>','event')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
															</td>
														</tr>
													<?	$res_type->MoveNext();
													} ?>
												</tbody>
											</table>
										</div>
									</div>

									<div class="tab-pane <?=$quest_tab?>" id="questsTab" role="tabpanel">
										<div class="p-20">
											<form class="floating-labels m-t-20" method="post" name="form2" id="form2" autocomplete="off" >
												<input class="form-control required-entry" value="company_question" type="hidden" name="frm_name">
												<? $res_type = $db->Execute("SELECT PK_PLACEMENT_COMPANY_QUESTIONNAIRE, QUESTIONS FROM M_PLACEMENT_COMPANY_QUESTIONNAIRE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_PLACEMENT_COMPANY_QUESTION_GROUP = '$PK_PLACEMENT_COMPANY_QUESTION_GROUP' AND ACTIVE = '1' ORDER BY DISPLAY_ORDER ASC");
												$i = 0;
												while (!$res_type->EOF) { 
												if($res_type->fields['IS_FACULTY'] == 1)
													$t = 2;
												else
													$t = 1;
												$i++; 
												
												$com_ques = $db->Execute("SELECT PK_COMPANY_QUESTIONNAIRE, ANSWER FROM S_COMPANY_QUESTIONNAIRE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_PLACEMENT_COMPANY_QUESTIONNAIRE = '".$res_type->fields['PK_PLACEMENT_COMPANY_QUESTIONNAIRE']."' AND PK_COMPANY = '$_GET[id]' AND ACTIVE = '1' ORDER BY ANSWER ASC LIMIT 1");

												if($com_ques->RecordCount() > 0) {													
													$QuestionId = $com_ques->fields['PK_COMPANY_QUESTIONNAIRE'];
													$Answer		= $com_ques->fields['ANSWER'];
												}
												else {
													$QuestionId = '';
													$Answer		= '';
												}
												?>
												<div class="d-flex flex-wrap p-b-40">
													<div class="col-12 col-sm-6 form-group">
														<label for="PK_PLACEMENT_COMPANY_QUESTIONNAIRE"><b>Q<?=$i?>. <?=$res_type->fields['QUESTIONS']?></b></label>
														<input type="hidden" name="PK_COMPANY_QUESTIONNAIRE[<?=$i?>]" id="PK_COMPANY_QUESTIONNAIRE[]" value="<?=$QuestionId?>" />
														<input type="hidden" name="PK_PLACEMENT_COMPANY_QUESTIONNAIRE[<?=$i?>]" id="PK_PLACEMENT_COMPANY_QUESTIONNAIRE[]" value="<?=$res_type->fields['PK_PLACEMENT_COMPANY_QUESTIONNAIRE']?>" />
													</div>
												</div>
												<div class="d-flex flex-wrap p-t-5 p-b-10">
													<div class="col-12 col-sm-6 form-group">
														<textarea class="form-control  rich" id="ANSWER[]" name="ANSWER[<?=$i?>]"><?=$Answer?></textarea>
														<span class="bar"></span> 
														<label for="ANSWER"><?=ANSWER?></label>
													</div>
												</div>
												<?	$res_type->MoveNext();
												} ?>
												<div class="row p-b-10">
													<div class="col-md-5 submit-button-sec">
														<div class="form-group m-b-5"  style="text-align:right" >
															<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
															<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='company?id=<?=$_GET['id']?>'" ><?=CANCEL?></button>
														</div>
													</div>
												</div>
											</form>
										</div>
									</div>
									<? } ?>
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
						<h4 class="modal-title" id="exampleModalLabel1"><?=DELETE_CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group" id="delete_message" ></div>
						<input type="hidden" id="DELETE_ID" value="0" />
						<input type="hidden" id="DELETE_TYPE" value="0" />
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" ><?=NO?></button>
					</div>
				</div>
			</div>
		</div>
    </div>
   
	<? require_once("js.php"); ?>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">

	<? if($_GET['tab'] != '') { ?>
		current_tab = '<?=$_GET['tab']?>';
	<? } else { ?>
		current_tab = 'homeTab';
	<? } ?>

	jQuery(document).ready(function($) {
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			current_tab = $(e.target).attr("href") // activated tab
			//alert(current_tab)
		});
		
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto",
			autoclose: true,
		});
		
		<? if($_GET['id'] != ''){ ?>
			//get_country(<?=$PK_STATES?>,'PK_COUNTRY')
		<? } ?>
	});
	</script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		function validate_form(val){
			document.getElementById('current_tab').value   = current_tab;
			document.getElementById("SAVE_CONTINUE").value = val;
			
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true)
				document.form1.submit();
		}
		
		function get_country(val,id){
			jQuery(document).ready(function($) { 
				var data  = 'state='+val+'&id='+id;
				var value = $.ajax({
					url: "../super_admin/ajax_get_country_from_state",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById(id+'_LABEL').classList.add("focused");
						document.getElementById(id).innerHTML = data;
					}		
				}).responseText;
			});
		}
		
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				if(type == 'user')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.COMPANY?>';	
				else if(type == 'job')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.EVENT?>';	
				else if(type == 'event')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.JOB?>';	
	
					
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'user')
						window.location.href = 'company?act='+$("#DELETE_TYPE").val()+'&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
					else if($("#DELETE_TYPE").val() == 'job')
						window.location.href = 'company?act='+$("#DELETE_TYPE").val()+'&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
					else if($("#DELETE_TYPE").val() == 'event')
						window.location.href = 'company?act='+$("#DELETE_TYPE").val()+'&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
				} else
					$("#deleteModal").modal("hide");
			});
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