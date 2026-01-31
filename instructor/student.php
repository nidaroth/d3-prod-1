<? /*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

require_once("../global/config.php"); 
require_once("../global/create_notification.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/student_contact.php");
require_once("../school/get_department_from_t.php");
require_once("../global/mail.php"); 
require_once("../global/texting.php"); 

///email timezone////
$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}

$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
///email timezone////

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_ROLES'] != 3 ){  
	header("location:../index");
	exit;
}
$_SESSION['FROM_NSTRUCTOR_PANEL'] = 1;

if($_GET['act'] == 'task_del'){
	$db->Execute("DELETE FROM S_STUDENT_TASK WHERE PK_STUDENT_TASK = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' "); 
	header("location:student?id=".$_GET['id'].'&eid='.$_GET['eid'].'&tab=taskTab&t='.$_GET['t']);
} else if($_GET['act'] == 'notes_del' || $_GET['act'] == 'event_del'){
	$db->Execute("DELETE FROM S_STUDENT_NOTES WHERE PK_STUDENT_NOTES = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' "); 
	if($_GET['act'] == 'notes_del')
		$tab = 'noteTab';
	else
		$tab = 'eventTab';
	header("location:student?id=".$_GET['id'].'&eid='.$_GET['eid'].'&tab='.$tab.'&t='.$_GET['t']);
}

$res = $db->Execute("SELECT IMAGE,FIRST_NAME,LAST_NAME,MIDDLE_NAME,OTHER_NAME 
FROM 
S_COURSE_OFFERING 
LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING
, S_STUDENT_COURSE, S_STUDENT_MASTER   
WHERE 
S_STUDENT_MASTER.PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
(INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]') AND 
S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING AND 
S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER"); 

if($res->RecordCount() == 0){  
	header("location:../index");
	exit;
}

$IMAGE					= $res->fields['IMAGE'];
$FIRST_NAME 			= $res->fields['FIRST_NAME'];
$LAST_NAME 				= $res->fields['LAST_NAME'];
$MIDDLE_NAME	 		= $res->fields['MIDDLE_NAME'];
$OTHER_NAME	 			= $res->fields['OTHER_NAME'];

/* Ticket # 1762  */
$res = $db->Execute("SELECT STATUS_DATE,STUDENT_STATUS,CODE,IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, PK_LEAD_CONTACT_SOURCE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_MASTER = '$_GET[id]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' "); 
$STATUS_DATE 	 		= $res->fields['STATUS_DATE'];
$STUDENT_STATUS	 		= $res->fields['STUDENT_STATUS'];
$CAMPUS_PROGRAM  		= $res->fields['CODE'];
$FIRST_TERM_DATE 		= $res->fields['BEGIN_DATE_1'];
$EXPECTED_GRAD_DATE 	= $res->fields['EXPECTED_GRAD_DATE'];
$PK_LEAD_CONTACT_SOURCE	= $res->fields['PK_LEAD_CONTACT_SOURCE'];
/* Ticket # 1762  */

if($STATUS_DATE != '0000-00-00')
	$STATUS_DATE = date("m/d/Y",strtotime($STATUS_DATE));
else
	$STATUS_DATE = '';
	
/* Ticket # 1715 */
$res = $db->Execute("SELECT * FROM S_STUDENT_ACADEMICS WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
$HS_CLASS_RANK				= $res->fields['HS_CLASS_RANK'];
$HS_CGPA					= $res->fields['HS_CGPA'];
$POST_SEC_CUM_CGPA			= $res->fields['POST_SEC_CUM_CGPA'];
$PREVIOUS_COLLEGE			= $res->fields['PREVIOUS_COLLEGE'];
$PK_HIGHEST_LEVEL_OF_EDU	= $res->fields['PK_HIGHEST_LEVEL_OF_EDU'];
$FERPA_BLOCK				= $res->fields['FERPA_BLOCK'];
$STUDENT_ID					= $res->fields['STUDENT_ID'];
$PK_SECOND_REPRESENTATIVE	= $res->fields['PK_SECOND_REPRESENTATIVE'];
$ADM_USER_ID				= $res->fields['ADM_USER_ID'];

$res = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT > 0 ");
$HEADER_CAMPUS_CODE = $res->fields['CAMPUS_CODE'];
/* Ticket # 1715 */
	
$activities_tab = 'active';
$task_tab 		= '';
if($_GET['tab'] == 'taskTab')
	$task_tab = 'active';
else if($_GET['tab'] == 'noteTab')
	$note_tab = 'active';
else if($_GET['tab'] == 'eventTab')
	$event_tab = 'active';
else if($_GET['tab'] == 'LOATab')
	$loa_tab = 'active';
else if($_GET['tab'] == 'probationTab')
	$probation_tab = 'active';
else if($_GET['tab'] == 'placementEventsTab')
	$placement_events_tab = 'active';
else if($_GET['tab'] == 'placementNotesTab')
	$placement_notes_tab = 'active';
else if($_GET['tab'] == 'emailsTab')
	$emails_tab = 'active';
else if($_GET['tab'] == 'stuTexts')
	$texts_tab = 'active';
else if($_GET['tab'] == 'intMail') //Ticket # 967
	$intMail_tab = 'active'; //Ticket # 967
else
	$task_tab = 'active';

/* Ticket # 1748 */
$has_warning_notes 	= 0;
$warning_notes 		= '';
if($_GET['id'] > 0) {
	$res_note = $db->Execute("select NOTES,DEPARTMENT FROM S_STUDENT_NOTES LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_NOTES.PK_DEPARTMENT, M_NOTE_TYPE WHERE PK_NOTE_TYPE_MASTER = 1 AND S_STUDENT_NOTES.PK_NOTE_TYPE = M_NOTE_TYPE.PK_NOTE_TYPE AND S_STUDENT_NOTES.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_NOTES.PK_STUDENT_MASTER = '$_GET[id]' AND SATISFIED = 0 ");
	
	if($res_note->RecordCount() > 0) {
		$has_warning_notes = 1;
		$warning_notes = '';
		while (!$res_note->EOF){
			if($warning_notes != '')
				$warning_notes .= ', ';
				
			$warning_notes .= 'See '.$res_note->fields['DEPARTMENT'];
			$res_note->MoveNext();
		}
		$warning_notes = 'Warning - '.$warning_notes;
	}
	
	$res_probation = $db->Execute("select PK_STUDENT_PROBATION FROM S_STUDENT_PROBATION WHERE PK_PROBATION_STATUS = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[id]' ");
	if($res_probation->RecordCount() > 0) {
		$has_warning_notes = 1;
		if($warning_notes != '')
			$warning_notes .= '<br />';
			
		$warning_notes .= 'On Probation';
	}
}
/* Ticket # 1748 */

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
	<link href="../backend_assets/node_modules/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet">
	<link href="../backend_assets/dist/css/pages/user-card.css" rel="stylesheet">
	<title><?=STUDENT_PAGE_TITLE1?> | <?=$title?></title>
	<style>
		#advice-validate-one-required-by-name-PK_DOCUMENT_TYPE{position: absolute;top: 24px;}
		input::-webkit-outer-spin-button,
		input::-webkit-inner-spin-button {
		  -webkit-appearance: none;
		  margin: 0;
		}

		/* Firefox */
		input[type=number] {
		  -moz-appearance: textfield;
		}
		.no-records-found{display:none;}
		option.option_red {
			color: red !important;
		}
		li > a > label{position: unset !important;}
		
		.table_5 th, .table_5 td {padding: 5px;}
		
		.tableFixHead          { overflow-y: auto; height: 500px; }
		.tableFixHead thead th { position: sticky; top: 0; }
		.tableFixHead thead th { background:#E8E8E8; }
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles" <? if($has_warning_notes == 1){ ?> style="background-color: #d12323 !important;color: #fff;" <? } ?> > <!-- Ticket # 1748 -->
                    <!-- Ticket # 1715 -->
					<div class="col-md-9 align-self-center" >
						<? $res_cont = $db->Execute("SELECT CONCAT(ADDRESS,' ',ADDRESS_1) AS ADDRESS, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL, EMAIL_OTHER  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); ?>
						<table width="100%" >
							<!-- Ticket # 1748 -->
							<tr>
								<td width="13%" >
									<h4 class="text-themecolor" <? if($has_warning_notes == 1){ ?> style="color: #fff;" <? } ?>  ><?=STUDENT_PAGE_TITLE1;?></h4>
									<br />
								</td>
								<td  ><b ><?=$LAST_NAME.', '.$FIRST_NAME.' '.$MIDDLE_NAME?></b><br /><br /></td>
								<td colspan="3" valign="top" ><?=$warning_notes?></td>
							</tr>
							<!-- Ticket # 1748 -->
							<tr>
								<td rowspan="5" >
									<? if($IMAGE != '') { ?>
										<div class="row el-element-overlay" style="width: 100%;" >
											<div class="card" style="margin-bottom: 0;margin-left: 10px;" >
												<div class="el-card-item" style="padding-bottom:0" >
													<div class="el-card-avatar el-overlay-1" style="margin-bottom: 0;" > 
														<img src="<?=$IMAGE?>" alt="user" />
														<div class="el-overlay">
															<ul class="el-info">
																<li><a class="btn default btn-outline image-popup-vertical-fit" href="<?=$IMAGE?>"><i class="icon-magnifier"></i></a></li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</div>
									<? } ?>
								</td>
								<td width="19%" ><b ><?=STUDENT_ID.':' ?></b></td>
								<td width="29%" ><?=$STUDENT_ID; ?></td> 
								<td width="18%" ></b></td>
								<td width="11%" ></td>
							</tr>
							<tr>
								<td ><b  ><?=ENROLLMENT.':' ?></b></td>
								<td ><?=$FIRST_TERM_DATE.' - '.$CAMPUS_PROGRAM.' - '.$STUDENT_STATUS.' - '.$HEADER_CAMPUS_CODE; ?></td>
								<td >&nbsp;&nbsp;<b ><?=CELL_PHONE_SHORT.':' ?></b></td>
								<td ><?=$res_cont->fields['CELL_PHONE'] ?></td>
							</tr>
							<tr>
								<td ><b  ><?=STATUS_DATE.':' ?></b></td>
								<td ><?=$STATUS_DATE ?></td>
								<td >&nbsp;&nbsp;<b ><?=HOME_PHONE_SHORT.':' ?></b></td>
								<td ><?=$res_cont->fields['HOME_PHONE'] ?></td>
							</tr>
							<tr>
								<td ><b  ><?=EXPECTED_GRAD_DATE.':' ?></b></td>
								<td ><?=$EXPECTED_GRAD_DATE ?></td>
								<td >&nbsp;&nbsp;<b ><?=EMAIL.':' ?></b></td>
								<td ><?=$res_cont->fields['EMAIL'] ?></td>
							</tr>
							<tr>
								<td ><b  ><?=ORIGINAL_EXPECTED_GRAD_DATE_1.':' ?></b></td>
								<td ><?=$ORIGINAL_EXPECTED_GRAD_DATE ?></td>
								<td >&nbsp;&nbsp;<b ><?=EMAIL_OTHER.':' ?></b></td>
								<td ><?=$res_cont->fields['EMAIL_OTHER'] ?></td>
							</tr>
						</table>
					</div>
					<!-- Ticket # 1715 -->
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                           <form class="floating-labels m-t-20" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
                            <div class="tab-content">
								<ul class="nav nav-tabs customtab" role="tablist">
									<li class="nav-item"> <a class="nav-link <?=$task_tab?>" data-toggle="tab" href="#stuTasks" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down"><?=TAB_TASK?></span></a> </li>
									
									<li class="nav-item"> <a class="nav-link <?=$emails_tab?>" data-toggle="tab" href="#stuEmails" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_EMAILS?></span></a> </li>
									
									<li class="nav-item"> <a class="nav-link <?=$intMail_tab?>" data-toggle="tab" href="#intMail" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_INTMAIL?></span></a> </li>
									
									<li class="nav-item"> <a class="nav-link <?=$texts_tab?>" data-toggle="tab" href="#stuTexts" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_TEXTS?></span></a> </li>
									
									<li class="nav-item "> <a class="nav-link <?=$note_tab?>" data-toggle="tab" href="#stuNotes" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_NOTES?></span></a> </li>
									
									<li class="nav-item"> <a class="nav-link <?=$event_tab?>" data-toggle="tab" href="#stuEvents" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_EVENTS?></span></a> </li>
									
									<li class="nav-item"> <a class="nav-link <?=$loa_tab?>" data-toggle="tab" href="#stuLOA" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_LOA?></span></a> </li>
									
									<li class="nav-item"> <a class="nav-link <?=$probation_tab?>" data-toggle="tab" href="#stuProbation" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_PROBATION?></span></a> </li>
								</ul>

								<div class="tab-content">
									<div class="tab-pane <?=$task_tab?>" id="stuTasks" role="tabpanel">
										<div class="row">
											<div class="col-md-7 align-self-center">
											</div>  
											<div class="col-md-3 align-self-center ">
												<input id="TASK_SEARCH" name="TASK_SEARCH" type="text" class="form-control" placeholder="Search" onkeypress="search_task(event,'','')" >
											</div>
											<div class="col-md-2 align-self-center ">
												<div class="d-flex ">
													<a href="student_task?sid=<?=$_GET['id']?>&eid=<?=$_GET['eid']?>" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>&nbsp;&nbsp;
												</div>
											</div>
										</div>
										
										<div class="table-responsive p-20" id="task_div" >
											<? $_REQUEST['sid'] = $_GET['id']; 
											$_REQUEST['eid'] 	= $_GET['eid']; 
											$_REQUEST['field']	= '';
											$_REQUEST['order']	= '';
											include('../school/ajax_student_task.php'); ?>
										</div>
									</div>
									
									<div class="tab-pane <?=$emails_tab?>" id="stuEmails" role="tabpanel">
										<table class="table table-hover">
											<thead>
												<tr>
													<th><?=SUBJECT?></th>
													<th><?=SENT_TO_MAIL?></th>
													<th><?=SENT_ON?></th>
													<th><?=VIEW?></th>
												</tr>
											</thead>
											<tbody>
												<? $res_1 = $db->Execute("select PK_EMAIL_LOG, SUBJECT, SENT_ON, EMAIL_ID FROM S_EMAIL_LOG WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' ORDER BY SENT_ON DESC ");
												while (!$res_1->EOF) { ?>
													<tr>
														<td><?=$res_1->fields['SUBJECT']?></td>
														<td><?=$res_1->fields['EMAIL_ID']?></td>
														<td>
															<? ///email timezone////
															if($res_1->fields['SENT_ON'] != '0000-00-00 00:00:00')
																echo convert_to_user_date($res_1->fields['SENT_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get()); ?> 
														</td>
														<td align="left" >
															<a href="javascript:void(0)" onclick="show_mail(<?=$res_1->fields['PK_EMAIL_LOG']?>)" ><?=VIEW?></a>
														</td>
													</tr>
												<?	$res_1->MoveNext();
												} ?>
											</tbody>
										</table>
									</div>
									
									<!-- Ticket # 967 -->
									<div class="tab-pane <?=$intMail_tab?>" id="intMail" role="tabpanel">
										<table class="table table-hover">
											<thead>
												<tr>
													<th><?=SUBJECT?></th>
													<th><?=FROM?></th>
													<th><?=SENT_ON?></th>
													<th><?=VIEW?></th>
												</tr>
											</thead>
											<tbody>
												<? $res_stud_user = $res_1 = $db->Execute("SELECT PK_USER FROM Z_USER WHERE ID = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_USER_TYPE = 3 ");
												if($res_stud_user->RecordCount() > 0) {
													$PK_USER = $res_stud_user->fields['PK_USER'];
													$res_1 = $db->Execute("SELECT Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL,PK_INTERNAL_EMAIL_RECEPTION,VIWED, Z_INTERNAL_EMAIL.INTERNAL_ID, SUBJECT, IF(PK_USER_TYPE = 2, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) , IF(PK_USER_TYPE = 3, CONCAT(S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.LAST_NAME) , IF(PK_USER_TYPE = 1, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME),'') )) AS NAME, DATE_FORMAT( Z_INTERNAL_EMAIL_RECEPTION.CREATED_ON, '%m/%d/%Y %r') AS CREATED_ON,Z_USER.PK_USER, Z_INTERNAL_EMAIL.CREATED_BY 
													FROM 
													Z_INTERNAL_EMAIL_RECEPTION ,Z_INTERNAL_EMAIL, Z_USER 
													LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID AND PK_USER_TYPE IN (1,2) 
													LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = Z_USER.ID AND PK_USER_TYPE = 3 
													WHERE 
													(Z_INTERNAL_EMAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' OR Z_INTERNAL_EMAIL.PK_ACCOUNT = '1') AND 
													Z_INTERNAL_EMAIL_RECEPTION.PK_INTERNAL_EMAIL = Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL AND 
													Z_INTERNAL_EMAIL.CREATED_BY = Z_USER.PK_USER AND 
													S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' AND 
													PK_INTERNAL_EMAIL_RECEPTION IN (SELECT MAX(PK_INTERNAL_EMAIL_RECEPTION) AS PK_INTERNAL_EMAIL_RECEPTION FROM  Z_INTERNAL_EMAIL_RECEPTION WHERE SELF_ADDED = 0 AND  PK_USER = '$PK_USER' AND DELETED = 0) GROUP BY INTERNAL_ID ");
													
													while (!$res_1->EOF) { ?>
														<tr>
															<td><?=$res_1->fields['SUBJECT']?></td>
															<td><?=$res_1->fields['NAME']?></td>
															<td>
																<? ///email timezone////
																if($res_1->fields['CREATED_ON'] != '0000-00-00 00:00:00')
																	echo convert_to_user_date($res_1->fields['CREATED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get()); ?> 
															</td>
															<td align="left" >
																<a href="javascript:void(0)" onclick="show_int_mail(<?=$res_1->fields['INTERNAL_ID']?>)" ><?=VIEW?></a>
															</td>
														</tr>
													<?	$res_1->MoveNext();
													} 
												} ?>
											</tbody>
										</table>
									</div>
									
									<div class="tab-pane <?=$texts_tab?>" id="stuTexts" role="tabpanel">
										<!-- stuTexts-->
										<div class="row " style="margin-top:15px" >
											<div class="col-md-6 align-self-center"></div>  
											<div class="col-md-2 align-self-center">
												
											</div>  
											<div class="col-md-2 align-self-center">
												
											</div>  
											<div class="col-md-1 align-self-center ">
												<input id="TEXT_FROM_DATE" name="TEXT_FROM_DATE" type="text" class="form-control date" placeholder="From Date" onkeypress="search_text()" onchange="search_text()" >
											</div>
											<div class="col-md-1 align-self-center ">
												<input id="TEXT_TO_DATE" name="TEXT_TO_DATE" type="text" class="form-control date" placeholder="To Date" onkeypress="search_text()" onchange="search_text()" >
											</div>
										</div>
										<div class="table-responsive p-20" id="text_div" >
											<? $_REQUEST['sid'] = $_GET['id']; 
											include('../school/ajax_student_text.php'); ?>
										</div>
									</div>
									
									<div class="tab-pane <?=$note_tab?>" id="stuNotes" role="tabpanel">
										<div class="row">
											<div class="col-md-7 " style="text-align:right;text-align:right;padding-top: 10px;" >
												
											</div>  
											<div class="col-md-3 align-self-center ">
												<input id="NOTES_SEARCH" type="text" class="form-control" placeholder="Search" onkeypress="search_notes(event,0,'','')" >
											</div>
											<div class="col-md-2 align-self-center text-right">
												<div class="d-flex justify-content-end align-items-center">
													<a href="student_notes?sid=<?=$_GET['id']?>&eid=<?=$_GET['eid']?>&event=0" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>&nbsp;&nbsp;
												</div>
											</div>
										</div>
										<div class="table-responsive p-20" id="notes_div_0" >
											<? $_REQUEST['sid'] 	= $_GET['id']; 
											$_REQUEST['eid'] 		= $_GET['eid']; 
											$_REQUEST['t'] 			= $_GET['t'];
											$_REQUEST['event'] 		= 0; 
											$_REQUEST['field']		= '';
											$_REQUEST['order']		= '';
											$_REQUEST['all_dept']	= 0; 
											include('../school/ajax_student_notes.php'); ?>
										</div>
									</div>
									
									<div class="tab-pane <?=$event_tab?>" id="stuEvents" role="tabpanel">
										<div class="row">
											<div class="col-md-7 align-self-center">
											</div>  
											<div class="col-md-3 align-self-center ">
												<input id="EVENT_SEARCH" type="text" class="form-control" placeholder="Search" onkeypress="search_notes(event,1,'PK_STUDENT_NOTES','DESC')" >
											</div>
											<div class="col-md-2 align-self-center text-right">
												<div class="d-flex justify-content-end align-items-center">
													<a href="student_notes?sid=<?=$_GET['id']?>&eid=<?=$_GET['eid']?>&event=1" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>&nbsp;&nbsp;
												</div>
											</div>
										</div>
										<div class="table-responsive p-20" id="notes_div_1" >
											<? $_REQUEST['sid'] 	= $_GET['id']; 
											$_REQUEST['eid'] 		= $_GET['eid']; 
											$_REQUEST['t'] 			= $_GET['t'];
											$_REQUEST['event'] 		= 1; 
											$_REQUEST['field']		= '';
											$_REQUEST['order']		= '';
											$_REQUEST['all_dept']	= 0; 
											include('../school/ajax_student_notes.php'); ?>
										</div>
									</div>
									
									<div class="tab-pane <?=$loa_tab?>" id="stuLOA" role="tabpanel">
										<div class="row">
											<div class="col-md-9 align-self-center">
											</div>  
											<div class="col-md-3 align-self-center ">
												<input id="LOA_SEARCH" type="text" class="form-control" placeholder="Search" onkeypress="search_loa(event,'','')" >
											</div>
										</div>
										
										<div class="table-responsive p-20" id="loa_div" >
											<? $_REQUEST['sid'] = $_GET['id']; 
											$_REQUEST['eid'] 	= $_GET['eid']; 
											$_REQUEST['field']	= '';
											$_REQUEST['order']	= '';
											$_REQUEST['rd']		= $disabled1;
											include('../school/ajax_student_loa.php'); ?>
										</div>
									</div>
									
									<div class="tab-pane <?=$probation_tab?>" id="stuProbation" role="tabpanel">
										<div class="row">
											<div class="col-md-9 align-self-center">
											</div>  
											<div class="col-md-3 align-self-center ">
												<input id="PROBATION_SEARCH" type="text" class="form-control" placeholder="Search" onkeypress="search_probation(event,'','')" >
											</div>
										</div>
										
										<div class="table-responsive p-20" id="probation_div" >
											<? $_REQUEST['sid'] = $_GET['id']; 
											$_REQUEST['eid'] 	= $_GET['eid']; 
											$_REQUEST['field']	= '';
											$_REQUEST['order']	= '';
											$_REQUEST['rd']		= $disabled1;
											include('../school/ajax_student_probation.php'); ?>
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
	<script src="../backend_assets/node_modules/inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
	<script src="../backend_assets/dist/js/pages/mask.init.js"></script>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript">
	<? if($_GET['tab'] != '') { ?>
		var current_tab = '<?=$_GET['tab']?>';
	<? } else { ?>
		var current_tab = 'stuTasks';
	<? } ?>
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
		
		$('.timepicker').inputmask(
			"hh:mm t", {
				placeholder: "HH:MM AM/PM", 
				insertMode: false, 
				showMaskOnHover: false,
				hourFormat: 12
			}
		);
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			current_tab = $(e.target).attr("href") // activated tab
			//alert(current_tab)
		});
	});
	</script>
	
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		
		function search_task(e,field,order){
			if (e.keyCode == 13 || e == '') {
				jQuery(document).ready(function($) { 
					var data  = 'search='+$("#TASK_SEARCH").val()+'&sid=<?=$_GET['id']?>&eid=<?=$_GET['eid']?>&field='+field+'&order='+order;
					var value = $.ajax({
						url: "../school/ajax_student_task",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('task_div').innerHTML = data;
						}		
					}).responseText;
				});
			}
		}
		
		function search_notes(e,event,field,order){
			//console.log(e)
			if (e.keyCode == 13 || e == '') {
				jQuery(document).ready(function($) { 
					var search 	 = '';
					var all_dept = 0;
					if(event == 1)
						search = $("#EVENT_SEARCH").val();
					else {
						search = $("#NOTES_SEARCH").val();
					}
						
					var data  = 'search='+search+'&sid=<?=$_GET['id']?>&eid=<?=$_GET['eid']?>&event='+event+'&field='+field+'&order='+order+'&all_dept='+all_dept;
					var value = $.ajax({
						url: "../school/ajax_student_notes",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('notes_div_'+event).innerHTML = data;
						}		
					}).responseText;
				});
			}
		}
		
		function search_loa(e,field,order){
			if (e.keyCode == 13 || e == '') {
				jQuery(document).ready(function($) { 
					var data  = 'search='+$("#LOA_SEARCH").val()+'&sid=<?=$_GET['id']?>&eid=<?=$_GET['eid']?>&rd=<?=$disabled1?>&field='+field+'&order='+order;
					var value = $.ajax({
						url: "../school/ajax_student_loa",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('loa_div').innerHTML = data;
						}		
					}).responseText;
				});
			}
		}
		function search_probation(e,field,order){
			if (e.keyCode == 13 || e == '') {
				jQuery(document).ready(function($) { 
					var data  = 'search='+$("#PROBATION_SEARCH").val()+'&sid=<?=$_GET['id']?>&eid=<?=$_GET['eid']?>&rd=<?=$disabled1?>&field='+field+'&order='+order;
					var value = $.ajax({
						url: "../school/ajax_student_probation",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('probation_div').innerHTML = data;
						}		
					}).responseText;
				});
			}
		}
		
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				if(type == 'task')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.TASK?>?';
				else if(type == 'notes')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.NOTES?>?';	
				else if(type == 'event')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.EVENT?>?';	
				
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'task')
						window.location.href = 'student?act=task_del&t=<?=$_GET['t']?>&eid=<?=$_GET['eid']?>&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
					else if($("#DELETE_TYPE").val() == 'notes') {
						window.location.href = 'student?act=notes_del&t=<?=$_GET['t']?>&eid=<?=$_GET['eid']?>&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
					} else if($("#DELETE_TYPE").val() == 'event') {
						window.location.href = 'student?act=event_del&t=<?=$_GET['t']?>&eid=<?=$_GET['eid']?>&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
					}
				}
				$("#deleteModal").modal("hide");
			});
		}
	</script>

	<script src="../backend_assets/node_modules/Magnific-Popup-master/dist/jquery.magnific-popup.js"></script>
    <script src="../backend_assets/node_modules/Magnific-Popup-master/dist/jquery.magnific-popup-init.js"></script>

	<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
	<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.select2').select2();
		});
	</script>
	
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>

</body>

</html>