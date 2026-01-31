<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/dashboard.php");

//echo date_default_timezone_get();exit;
//echo "<pre>";print_r($_SESSION);exit;
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_USER_TYPE'] != 2){ 
	header("location:../index");
	exit;
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
	<link href="../backend_assets/node_modules/calendar/dist/fullcalendar.css" rel="stylesheet" />
	 <link href="../backend_assets/dist/css/pages/chat-app-page.css" rel="stylesheet">
	<style>
	.fc-month-view span.fc-title{
		white-space: normal;
	}
	.fc-week{border-right-width: 1px !important;margin-right: 16px !important;}
	<? $res_type = $db->Execute("select PK_SESSION,COLOR from M_SESSION WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by DISPLAY_ORDER ASC");
	while (!$res_type->EOF) { ?>
		.bg-info-<?=$res_type->fields['PK_SESSION']?>{background-color: #<?=$res_type->fields['COLOR']?> !important; color:#000 !important;}
	<?	$res_type->MoveNext();
	} ?>
	</style>
	<title>Dashboard | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? //require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <!--<div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=DASHBOARD_PAGE_TITLE?></h4>
                    </div>
                </div>-->

				<? if($_SESSION['PK_ROLES'] != 4 && $_SESSION['PK_ROLES'] != 5){ 
					$res = $db->Execute("SELECT NEW_LEAD_STATUS,QUALIFIED_LEAD_STATUS,NEW_APPLICATIONS_STATUS,NEW_STUDENTS_STATUS, NEW_LEAD_FROM_DATE, NEW_LEAD_TO_DATE, QUALIFIED_LEAD_FROM_DATE, QUALIFIED_LEAD_TO_DATE, NEW_APPLICATIONS_FROM_DATE, NEW_APPLICATIONS_TO_DATE, NEW_STUDENTS_FROM_DATE, NEW_STUDENTS_TO_DATE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");  
					$NEW_LEAD_STATUS 		 = $res->fields['NEW_LEAD_STATUS']; 
					$QUALIFIED_LEAD_STATUS 	 = $res->fields['QUALIFIED_LEAD_STATUS'];
					$NEW_APPLICATIONS_STATUS = $res->fields['NEW_APPLICATIONS_STATUS'];
					$NEW_STUDENTS_STATUS 	 = $res->fields['NEW_STUDENTS_STATUS']; 
					
					$NEW_LEAD_FROM_DATE 	 	 = $res->fields['NEW_LEAD_FROM_DATE']; 
					$NEW_LEAD_TO_DATE 	 		 = $res->fields['NEW_LEAD_TO_DATE']; 
					$QUALIFIED_LEAD_FROM_DATE 	 = $res->fields['QUALIFIED_LEAD_FROM_DATE']; 
					$QUALIFIED_LEAD_TO_DATE 	 = $res->fields['QUALIFIED_LEAD_TO_DATE']; 
					$NEW_APPLICATIONS_FROM_DATE  = $res->fields['NEW_APPLICATIONS_FROM_DATE']; 
					$NEW_APPLICATIONS_TO_DATE 	 = $res->fields['NEW_APPLICATIONS_TO_DATE']; 
					$NEW_STUDENTS_FROM_DATE 	 = $res->fields['NEW_STUDENTS_FROM_DATE']; 
					$NEW_STUDENTS_TO_DATE 	 	 = $res->fields['NEW_STUDENTS_TO_DATE']; 
					
					$NEW_LEAD_COND = "";
					if($NEW_LEAD_FROM_DATE != '' && $NEW_LEAD_FROM_DATE != '0000-00-00' && $NEW_LEAD_TO_DATE != '' && $NEW_LEAD_TO_DATE != '0000-00-00' )
						$NEW_LEAD_COND = " AND STATUS_DATE BETWEEN '$NEW_LEAD_FROM_DATE' AND '$NEW_LEAD_TO_DATE' ";
					else if($NEW_LEAD_FROM_DATE != '' && $NEW_LEAD_FROM_DATE != '0000-00-00')
						$NEW_LEAD_COND = " AND STATUS_DATE >= '$NEW_LEAD_FROM_DATE' ";
					else if($NEW_LEAD_TO_DATE != '' && $NEW_LEAD_TO_DATE != '0000-00-00')
						$NEW_LEAD_COND = " AND STATUS_DATE <= '$NEW_LEAD_TO_DATE' ";
						
					$QUALIFIED_LEADS_COND = "";
					if($QUALIFIED_LEAD_FROM_DATE != '' && $QUALIFIED_LEAD_FROM_DATE != '0000-00-00' && $QUALIFIED_LEAD_TO_DATE != '' && $QUALIFIED_LEAD_TO_DATE != '0000-00-00' )
						$QUALIFIED_LEADS_COND = " AND STATUS_DATE BETWEEN '$QUALIFIED_LEAD_FROM_DATE' AND '$QUALIFIED_LEAD_TO_DATE' ";
					else if($QUALIFIED_LEAD_FROM_DATE != '' && $QUALIFIED_LEAD_FROM_DATE != '0000-00-00')
						$QUALIFIED_LEADS_COND = " AND STATUS_DATE >= '$QUALIFIED_LEAD_FROM_DATE' ";
					else if($QUALIFIED_LEAD_TO_DATE != '' && $QUALIFIED_LEAD_TO_DATE != '0000-00-00')
						$QUALIFIED_LEADS_COND = " AND STATUS_DATE <= '$QUALIFIED_LEAD_TO_DATE' ";
						
					$NEW_APPLICATIONS_COND = "";
					if($NEW_APPLICATIONS_FROM_DATE != '' && $NEW_APPLICATIONS_FROM_DATE != '0000-00-00' && $NEW_APPLICATIONS_TO_DATE != '' && $NEW_APPLICATIONS_TO_DATE != '0000-00-00' )
						$NEW_APPLICATIONS_COND = " AND STATUS_DATE BETWEEN '$NEW_APPLICATIONS_FROM_DATE' AND '$NEW_APPLICATIONS_TO_DATE' ";
					else if($NEW_APPLICATIONS_FROM_DATE != '' && $NEW_APPLICATIONS_FROM_DATE != '0000-00-00')
						$NEW_APPLICATIONS_COND = " AND STATUS_DATE >= '$NEW_APPLICATIONS_FROM_DATE' ";
					else if($NEW_APPLICATIONS_TO_DATE != '' && $NEW_APPLICATIONS_TO_DATE != '0000-00-00')
						$NEW_APPLICATIONS_COND = " AND STATUS_DATE <= '$NEW_APPLICATIONS_TO_DATE' ";
						
					$NEW_STUDENTS_COND = "";
					if($NEW_STUDENTS_FROM_DATE != '' && $NEW_STUDENTS_FROM_DATE != '0000-00-00' && $NEW_STUDENTS_TO_DATE != '' && $NEW_STUDENTS_TO_DATE != '0000-00-00' )
						$NEW_STUDENTS_COND = " AND STATUS_DATE BETWEEN '$NEW_STUDENTS_FROM_DATE' AND '$NEW_STUDENTS_TO_DATE' ";
					else if($NEW_STUDENTS_FROM_DATE != '' && $NEW_STUDENTS_FROM_DATE != '0000-00-00')
						$NEW_STUDENTS_COND = " AND STATUS_DATE >= '$NEW_STUDENTS_FROM_DATE' ";
					else if($NEW_STUDENTS_TO_DATE != '' && $NEW_STUDENTS_TO_DATE != '0000-00-00')
						$NEW_STUDENTS_COND = " AND STATUS_DATE <= '$NEW_STUDENTS_TO_DATE' ";
					
					?>
					<div class="card-group">
						<div class="card">
							<div class="card-body">
								<a href="dashboard_report.php?t=1" style="text-decoration:none">
									<div class="row">
										<div class="col-md-12">
											<div class="d-flex no-block align-items-center">
												<div>
													<h3><i class="icon-screen-desktop"></i></h3>
													<p class="text-muted"><?=NEW_LEADS?></p>
												</div>
												<div class="ml-auto">
													<? $res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND PK_STUDENT_STATUS IN ($NEW_LEAD_STATUS) AND ARCHIVED = 0 $NEW_LEAD_COND ");   ?>
													<h2 class="counter text-primary"><?=$res->RecordCount() ?></h2>
												</div>
											</div>
										</div>
										<div class="col-12">
											<div class="progress">
												<div class="progress-bar bg-primary" role="progressbar" style="width: 85%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>
									</div>
								</a>
							</div>
						</div>
						<div class="card">
							<div class="card-body">
								<a href="dashboard_report.php?t=2" style="text-decoration:none">
									<div class="row">
										<div class="col-md-12">
											<div class="d-flex no-block align-items-center">
												<div>
													<h3><i class="icon-note"></i></h3>
													<p class="text-muted"><?=QUALIFIED_LEADS?></p>
												</div>
												<div class="ml-auto">
													<? $res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND PK_STUDENT_STATUS IN ($QUALIFIED_LEAD_STATUS) AND ARCHIVED = 0 $QUALIFIED_LEADS_COND ");   ?>
													<h2 class="counter text-primary"><?=$res->RecordCount() ?></h2>
												</div>
											</div>
										</div>
										<div class="col-12">
											<div class="progress">
												<div class="progress-bar bg-cyan" role="progressbar" style="width: 85%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>
									</div>
								</a>
							</div>
						</div>
						<div class="card">
							<div class="card-body">
								<a href="dashboard_report.php?t=3" style="text-decoration:none">
									<div class="row">
										<div class="col-md-12">
											<div class="d-flex no-block align-items-center">
												<div>
													<h3><i class="icon-doc"></i></h3>
													<p class="text-muted"><?=NEW_APPLICATIONS?></p>
												</div>
												<div class="ml-auto">
													<? $res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND PK_STUDENT_STATUS IN ($NEW_APPLICATIONS_STATUS) AND ARCHIVED = 0 $NEW_APPLICATIONS_COND ");   ?>
													<h2 class="counter text-primary"><?=$res->RecordCount() ?></h2>
												</div>
											</div>
										</div>
										<div class="col-12">
											<div class="progress">
												<div class="progress-bar bg-purple" role="progressbar" style="width: 85%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>
									</div>
								</a>
							</div>
						</div>
						<div class="card">
							<div class="card-body">
								<a href="dashboard_report.php?t=4" style="text-decoration:none">
									<div class="row">
										<div class="col-md-12">
											<div class="d-flex no-block align-items-center">
												<div>
													<h3><i class="icon-bag"></i></h3>
													<p class="text-muted"><?=NEW_STUDENTS?></p>
												</div>
												<div class="ml-auto">
													<? $res = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_MASTER, S_STUDENT_ENROLLMENT WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND PK_STUDENT_STATUS IN ($NEW_STUDENTS_STATUS) AND ARCHIVED = 0 $NEW_STUDENTS_COND ");   ?>
													<h2 class="counter text-primary"><?=$res->RecordCount() ?></h2>
												</div>
											</div>
										</div>
										<div class="col-12">
											<div class="progress">
												<div class="progress-bar bg-success" role="progressbar" style="width: 85%; height: 6px;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>
									</div>
								</a>
							</div>
						</div>
					</div>
				<? } ?>
				<div class="row">
					<div class="col-lg-4 col-md-4">
						<div class="card">
							<div class="card-body">
								<div class="d-flex align-items-center no-block">
									<h5 class="card-title "><?=ANNOUNCEMENT?>
										<? if($_SESSION['PK_ROLES'] == 1 || $_SESSION['PK_ROLES'] == 2 || $_SESSION['PK_ROLES'] == 3){ ?>
										&nbsp;&nbsp;<a href="announcement.php?p=i"><i class="fa fa-plus-circle"></i></a>
										<? } ?>
									</h5>
								</div>
								<? //$cur_time_cet = convert_to_user_date(date('Y-m-d H:i:s'),'Y-m-d H:i:s','CET',date_default_timezone_get());
						
								$res_z_acc = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
								$PK_TIMEZONE = ($res_z_acc->fields['PK_TIMEZONE'])?$res_z_acc->fields['PK_TIMEZONE']:$_SESSION['PK_TIMEZONE'];							
								$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE PK_TIMEZONE = '$PK_TIMEZONE'"); 
								 $cur_time_cet = convert_to_user_date(date('Y-m-d H:i:s'), "Y-m-d h:i:s", $res_tz->fields['TIMEZONE'],date_default_timezone_get());															
								
								$table = "";
								$cond  = "";
								if($_SESSION['PK_ROLES'] == 1 || $_SESSION['PK_ROLES'] == 2 ||$_SESSION['PK_ROLES'] == 3 || $_SESSION['PK_ROLES'] == 4 || $_SESSION['PK_ROLES'] == 5) {
									if(isset($_SESSION['PK_CAMPUS']) && $_SESSION['PK_CAMPUS'] != '')
									{
										$cond .= " AND Z_ANNOUNCEMENT_CAMPUS.PK_CAMPUS IN ($_SESSION[PK_CAMPUS]) ";
									}
									$table = " ,Z_ANNOUNCEMENT_CAMPUS, Z_ANNOUNCEMENT_FOR, M_ANNOUNCEMENT_FOR_MASTER ";
									$cond  .= "  AND Z_ANNOUNCEMENT.PK_ANNOUNCEMENT = Z_ANNOUNCEMENT_CAMPUS.PK_ANNOUNCEMENT AND Z_ANNOUNCEMENT_FOR.PK_ANNOUNCEMENT = Z_ANNOUNCEMENT.PK_ANNOUNCEMENT AND 
									Z_ANNOUNCEMENT_FOR.PK_ANNOUNCEMENT_FOR_MASTER = M_ANNOUNCEMENT_FOR_MASTER.PK_ANNOUNCEMENT_FOR_MASTER ";
								}
								$pk_param = " AND PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' ";
								if($_SESSION['PK_ROLES'] == 2)
								{
									$pk_param = "";
								}
								$sQuery_Announcement = "SELECT 
																* 
															FROM 
																(
																	SELECT 
																		* 
																	FROM 
																		Z_ANNOUNCEMENT 
																	WHERE 
																		ACTIVE = 1 
																		AND ANNOUNCEMENT_FROM = 1 
																		AND '$cur_time_cet' BETWEEN START_DATE_TIME_CET 
																		AND END_DATE_TIME_CET 
																	UNION 
																	SELECT 
																		Z_ANNOUNCEMENT.* 
																	FROM 
																		Z_ANNOUNCEMENT, 
																		Z_ANNOUNCEMENT_EMPLOYEE $table 
																	WHERE 
																		Z_ANNOUNCEMENT.ACTIVE = 1 
																		AND ANNOUNCEMENT_FROM = 2 $cond 
																		AND Z_ANNOUNCEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
																		AND '$cur_time_cet' BETWEEN START_DATE_TIME_CET 
																		AND END_DATE_TIME_CET 
																		AND Z_ANNOUNCEMENT.PK_ANNOUNCEMENT = Z_ANNOUNCEMENT_EMPLOYEE.PK_ANNOUNCEMENT 
																		$pk_param
																) AS TEMPT 
															GROUP BY 
																PK_ANNOUNCEMENT 
															ORDER BY 
																START_DATE_TIME_CET ASC ";
								//echo $sQuery_Announcement;exit;
								$res = $db->Execute($sQuery_Announcement);  
								?>
								<div style="height: 400px;overflow-y: auto;" >
									<? while (!$res->EOF){ ?>
									<div class="col-12">
									   <a href="announcement_detail?id=<?=$res->fields['PK_ANNOUNCEMENT']?>">
											<? if($_SESSION['PK_LANGUAGE'] == 1)
												echo $res->fields['SHORT_DESC_ENG']; 
											else 
												echo $res->fields['SHORT_DESC_SPA'];  ?>
									   </a>
									   <hr />
									</div>
									<? $res->MoveNext();
									} ?>
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-lg-4 col-md-4">
						<div class="card">
							<div class="card-body">
								<div class="d-flex align-items-center no-block">
									<h5 class="card-title "><?=TO_DO_LIST ?>&nbsp;&nbsp;<a href="to_do.php?p=i"><i class="fa fa-plus-circle"></i></a></h5>
								</div>
								<div style="height: 400px;overflow-y: auto;">
								<? $DATE = date("Y-m-d");
								$res = $db->Execute("SELECT PK_TO_DO_LIST,HEADER ,IF(DATE = '0000-00-00', '',  DATE_FORMAT(DATE,'%m/%d/%Y')) AS DATE FROM S_TO_DO_LIST WHERE ACTIVE=1 AND PK_USER = '$_SESSION[PK_USER]' AND COMPLETED = 0 AND DATE <= '$DATE' ORDER BY DATE ASC");
								while (!$res->EOF){ ?>
									<div class="col-12">
									   <a href="to_do?id=<?=$res->fields['PK_TO_DO_LIST']?>&p=i"  >
											<?=$res->fields['HEADER'];  ?>
											<br /><div style="display: inline;margin-left: 0;font-size: 12px;" ><?=Date?>: <?=$res->fields['DATE'];  ?></div>
									   </a>
									   <hr />
									</div>
								<? $res->MoveNext();
								} ?>
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-lg-4 col-md-4">
						<div class="card">
							<div class="card-body">
								<div class="d-flex align-items-center no-block">
									<h5 class="card-title "><?=MNU_NOTIFICATIONS?></h5>
								</div>
								<? $res_noti = $db->Execute("select PK_NOTIFICATION_RECIPIENTS,TEXT,LINK,EVENT_TYPE from Z_NOTIFICATION LEFT JOIN S_EVENT_TEMPLATE ON S_EVENT_TEMPLATE.PK_EVENT_TEMPLATE = Z_NOTIFICATION.PK_EVENT_TEMPLATE LEFT JOIN Z_EVENT_TYPE ON Z_EVENT_TYPE.PK_EVENT_TYPE = S_EVENT_TEMPLATE.PK_EVENT_TYPE, Z_NOTIFICATION_RECIPIENTS WHERE NOTIFICATION_TO_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND Z_NOTIFICATION.PK_NOTIFICATION = Z_NOTIFICATION_RECIPIENTS.PK_NOTIFICATION AND Z_NOTIFICATION_RECIPIENTS.PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' AND NOTI_READ = 0 ORDER BY PK_NOTIFICATION_RECIPIENTS DESC");   ?>
								<div style="height: 400px;overflow-y: auto;" >
									<ul class="list-task todo-list list-group m-b-0" data-role="tasklist" >
										<? if($res_noti->RecordCount() == 0) { ?>
											<li class="list-group-item" data-role="task">
												<a href="javascript:void(0)" >
													<h5><?=NO_NOTIFICATION?></h5>
												</a>
												<hr />
											</li>
										<? } else {
											while (!$res_noti->EOF) { ?>
												<li class="list-group-item" data-role="task" style="padding-top: 0;padding-bottom: 0;" >
													<a href="set_notification_as_read?id=<?=$res_noti->fields['PK_NOTIFICATION_RECIPIENTS']?>" >
														<div class="mail-contnet">
															<h5><?=$res_noti->fields['EVENT_TYPE']?></h5>
															<span class="mail-desc" ><?=$res_noti->fields['TEXT']?></span>
														</div>
													</a>
													<hr />
												</li>
											<? $res_noti->MoveNext();
											} 
										} ?>
									</ul>
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-lg-4 col-md-4">
						<div class="card">
							<div class="card-body">
								<div class="d-flex align-items-center no-block">
									<div class="row" style="width: 100%;" >
										<div class="col-4">
											<h5 class="card-title "><?=ACTIVITIES?></h5>
										</div>
										<div class="col-7">
											<select id="PK_EMPLOYEE_MASTER" name="PK_EMPLOYEE_MASTER" class="form-control" onchange="get_activity(this.value)" >
												<option value="">All</option>
												<? $res_type = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME) AS EMP_NAME, PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE ACTIVE = 1 AND (PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' OR PK_SUPERVISOR = '$_SESSION[PK_EMPLOYEE_MASTER]') AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER'] ?>" ><?=$res_type->fields['EMP_NAME'] ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-1">
											<a href="student_task?p=i"><i class="fa fa-plus-circle"></i></a>
										</div>
									</div>
								</div>
								
								<div id="activity_div" >
									<? $_REQUEST['PK_EMPLOYEE_MASTER'] = '';
									include("ajax_dashboard_activity.php");?>
								</div>
							</div>
						</div>
					</div>

					<div class="col-lg-4 col-md-4">
						<div class="card">
							<div class="card-body">
								<div class="d-flex align-items-center no-block">
									<h5 class="card-title "><?=DOCUMENTS ?> &nbsp;&nbsp;<a href="collateral?p=i"><i class="fa fa-plus-circle"></i></a></h5>
								</div>
								<div style="height: 400px;overflow-y: auto;">
									<? $res = $db->Execute("SELECT FILE_NAME,FILE_LOCATION FROM S_COLLATERAL WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY FILE_NAME ASC ");
									while (!$res->EOF){ ?>
									<div class="col-12">
									   <a href="<?=$res->fields['FILE_LOCATION']?>" target="_blank" >
											<?=$res->fields['FILE_NAME'];  ?>
									   </a>
									   <hr />
									</div>
									<? $res->MoveNext();
									} ?>
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-lg-4 col-md-4">
						<div class="card">
							<div class="card-body">
								<div class="d-flex align-items-center no-block">
									<h5 class="card-title "><?=REVISION_RELEASE_NOTES?> </h5>
								</div>
								<div style="height: 400px;overflow-y: auto;">
									<ul class="list-task todo-list list-group m-b-0" data-role="tasklist">
										<? $date = date("Y-m-d");
										$res_type = $db->Execute("select PK_RELEASE_CATEGORY, RELEASE_TYPE, SUBJECT, PUSHED_TO_D3_DATE FROM Z_RELEASE_NOTES LEFT JOIN M_RELEASE_TYPE ON M_RELEASE_TYPE.PK_RELEASE_TYPE = Z_RELEASE_NOTES.PK_RELEASE_TYPE WHERE RELEASE_NOTES_PUSHED ORDER BY PUSHED_TO_D3_DATE DESC, RELEASE_TYPE ASC, SUBJECT ASC LIMIT 0,10 ");
										while (!$res_type->EOF) { 
											$PK_RELEASE_CATEGORY = $res_type->fields['PK_RELEASE_CATEGORY'];
											$CATEGORY = '';
											$res_type_1 = $db->Execute("select RELEASE_CATEGORY from M_RELEASE_CATEGORY WHERE PK_RELEASE_CATEGORY IN ($PK_RELEASE_CATEGORY) ORDER BY RELEASE_CATEGORY ASC");
											while (!$res_type_1->EOF) { 
												if($CATEGORY != '')
													$CATEGORY .= ', ';
													
												$CATEGORY .= $res_type_1->fields['RELEASE_CATEGORY'];
												
												$res_type_1->MoveNext();
											}
											
											$PUSHED_TO_D3_DATE = '';
											if($res_type->fields['PUSHED_TO_D3_DATE'] != '0000-00-00' && $res_type->fields['PUSHED_TO_D3_DATE'] != '') 
												$PUSHED_TO_D3_DATE = date("m/d/Y", strtotime($res_type->fields['PUSHED_TO_D3_DATE'])); ?>
											<li class="list-group-item" data-role="task">
												<a href="release_notes" >
													<span>
														<b style="font-weight: bold;" ><?=DATE?>: </b><?=$PUSHED_TO_D3_DATE ?><br />
														
														<b style="font-weight: bold;" ><?=CATEGORY?>: </b><?=$CATEGORY ?><br />
														
														<b style="font-weight: bold;"><?=TYPE?>: </b><?=$res_type->fields['RELEASE_TYPE']?><br />
														
														<b style="font-weight: bold;"><?=SUBJECT?>: </b><?=$res_type->fields['SUBJECT']?>
													</span> 
												</a>
												<hr />
											</li>
										<?	$res_type->MoveNext();
										} ?>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="row" style="display:none" >
					<!-- Column -->
					<div class="col-lg-8 col-md-12">
						<div class="card">
							<div class="card-body">
								<div class="d-flex m-b-40 align-items-center no-block">
									<h5 class="card-title "><?=YEARLY_TUITIONS?></h5>
									<div class="ml-auto">
										<ul class="list-inline font-12">
											<li><i class="fa fa-circle text-cyan"></i> Course 1</li>
											<li><i class="fa fa-circle text-primary"></i> Course 2</li>
											<li><i class="fa fa-circle text-purple"></i> Course 3</li>
										</ul>
									</div>
								</div>
								<div id="morris-area-chart" style="height: 340px;"></div>
							</div>
						</div>
					</div>
				</div>
			 
				<div class="row" style="display:none">
					<!-- Column -->

					<div class="col-lg-8 col-md-12">
						<div class="card">
							<div class="card-body">
								<div class="d-flex m-b-40 align-items-center no-block">
									<h5 class="card-title "><?=LEADS?></h5>
									<div class="ml-auto">
										<ul class="list-inline font-12">
											<li><i class="fa fa-circle text-cyan"></i> <?=NEW1 ?></li>
											<li><i class="fa fa-circle text-primary"></i> <?=QUALIFIED ?></li>
										</ul>
									</div>
								</div>
								<div id="morris-area-chart2" style="height: 340px;"></div>
							</div>
						</div>
					</div>

				</div>
				
				
				 <div class="card-group">
                    <div class="card">
                        <div class="card-body">
							<div class="row">
								<div class="col-md-12">
									<div class="card-body b-l calender-sidebar">
										<!-- Ticket # 1006 -->
										<form name="form1" method="get" >
											<div class="row" id="cal1">
												<div class="col-3">
													<select id="CAL_PK_EMPLOYEE_MASTER" name="CAL_PK_EMPLOYEE_MASTER" class="form-control" >
														<option value="">All Users</option>
														<? $res_type = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME) AS EMP_NAME, PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE ACTIVE = 1 AND (PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' OR PK_SUPERVISOR = '$_SESSION[PK_EMPLOYEE_MASTER]') AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER'] ?>" <? if($res_type->fields['PK_EMPLOYEE_MASTER'] == $_GET['CAL_PK_EMPLOYEE_MASTER']) echo "selected"; ?> ><?=$res_type->fields['EMP_NAME'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
												<div class="col-3">
													<select id="CAL_PK_TASK_TYPE" name="CAL_PK_TASK_TYPE[]" multiple class="form-control" >
														<? if($_GET['CAL_PK_TASK_TYPE'] != '')
															$CAL_PK_TASK_TYPE = explode(",",$_GET['CAL_PK_TASK_TYPE']);
														$res_type = $db->Execute("select PK_TASK_TYPE, TASK_TYPE from M_TASK_TYPE WHERE ACTIVE = 1  AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by trim(TASK_TYPE) ASC");
														while (!$res_type->EOF) { 
															$selected = "";
															if(!empty($CAL_PK_TASK_TYPE)){
																foreach($CAL_PK_TASK_TYPE as $CAL_PK_TASK_TYPE1){
																	if($CAL_PK_TASK_TYPE1 == $res_type->fields['PK_TASK_TYPE'])
																		$selected = "selected";
																}
															} ?>
															<option value="<?=$res_type->fields['PK_TASK_TYPE'] ?>" <?=$selected ?> ><?=$res_type->fields['TASK_TYPE'] ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
												<!-- Ticket # 1299 -->
												<div class="col-3">
													<select id="CAL_PK_CAMPUS" name="CAL_PK_CAMPUS[]" multiple class="form-control" >
														<? /* Ticket # 2032 */
														if($_GET['CAL_PK_CAMPUS'] != '')
															$CAL_PK_CAMPUS = explode(",",$_GET['CAL_PK_CAMPUS']);
														
														$cal_cond22 = "";
														if($_SESSION['ADMIN_PK_ROLES'] != 1 &&  $_SESSION['PK_ROLES'] != 2){
															$cal_cond22 = " AND PK_CAMPUS IN ($_SESSION[PK_CAMPUS]) ";
														}
														$res_type = $db->Execute("select PK_CAMPUS, CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1  AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cal_cond22 order by CAMPUS_CODE ASC");
														
														if($res_type->RecordCount() == 1 && $_GET['CAL_PK_CAMPUS'] == '')
															$CAL_PK_CAMPUS[] = $res_type->fields['PK_CAMPUS'];
															
														while (!$res_type->EOF) { 
															$selected = "";
															if(!empty($CAL_PK_CAMPUS)){
																foreach($CAL_PK_CAMPUS as $PK_CAMPUS1){
																	if($PK_CAMPUS1 == $res_type->fields['PK_CAMPUS'])
																		$selected = "selected";
																}
															} ?>
															<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" <?=$selected ?> ><?=$res_type->fields['CAMPUS_CODE'] ?></option>
														<?	$res_type->MoveNext();
														} /* Ticket # 2032 */ ?>
													</select>
												</div>
												<!-- Ticket # 1299 -->
												<div class="col-3">
													<button type="button"onclick="search_cal()" class="btn waves-effect waves-light btn-info">Go</button>
												</div>
											</div>
										</form>
										<!-- Ticket # 1006 -->
										<div id="calendar"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
            </div>
        </div>

         <? require_once("footer.php"); ?>
		
    </div>
   
     <? require_once("js.php"); ?>
	 
    <!-- ============================================================== -->
    <!-- This page plugins -->
    <!-- ============================================================== -->
    <!--morris JavaScript -->
    <script src="../backend_assets/node_modules/raphael/raphael-min.js"></script>
    <script src="../backend_assets/node_modules/morrisjs/morris.min.js"></script>
    <script src="../backend_assets/node_modules/jquery-sparkline/jquery.sparkline.min.js"></script>
    <!-- Chart JS -->
    <script>
        jQuery(document).ready(function($) {
            $('#chat, #msg, #comment, #todo').perfectScrollbar();
        });
		
		$(function () {
		"use strict";
		//This is for the Notification top right
	   /* $.toast({
				heading: 'Welcome to DiamonD DIS'
				, text: 'Use the predefined ones, or specify a custom position object.'
				, position: 'top-right'
				, loaderBg: '#ff6849'
				, icon: 'info'
				, hideAfter: 3500
				, stack: 6
			})*/
			// Dashboard 1 Morris-chart
		Morris.Area({
			element: 'morris-area-chart'
			, data: [{
					period: '2010'
					, iphone: 50
					, ipad: 80
					, itouch: 20
			}, {
					period: '2011'
					, iphone: 130
					, ipad: 100
					, itouch: 80
			}, {
					period: '2012'
					, iphone: 80
					, ipad: 60
					, itouch: 70
			}, {
					period: '2013'
					, iphone: 70
					, ipad: 200
					, itouch: 140
			}, {
					period: '2014'
					, iphone: 180
					, ipad: 150
					, itouch: 140
			}, {
					period: '2015'
					, iphone: 105
					, ipad: 100
					, itouch: 80
			}
				, {
					period: '2016'
					, iphone: 250
					, ipad: 150
					, itouch: 200
			}]
			, xkey: 'period'
			, ykeys: ['iphone', 'ipad', 'itouch']
			, labels: ['Course 1', 'Course 2', 'Course 3']
			, pointSize: 3
			, fillOpacity: 0
			, pointStrokeColors: ['#00bfc7', '#fb9678', '#9675ce']
			, behaveLikeLine: true
			, gridLineColor: '#e0e0e0'
			, lineWidth: 3
			, hideHover: 'auto'
			, lineColors: ['#00bfc7', '#fb9678', '#9675ce']
			, resize: true
		});
		Morris.Area({
			element: 'morris-area-chart2'
			, data: [{
					period: '2010'
					, SiteA: 0
					, SiteB: 0
			, }, {
					period: '2011'
					, SiteA: 130
					, SiteB: 100
			, }, {
					period: '2012'
					, SiteA: 80
					, SiteB: 60
			, }, {
					period: '2013'
					, SiteA: 70
					, SiteB: 200
			, }, {
					period: '2014'
					, SiteA: 180
					, SiteB: 150
			, }, {
					period: '2015'
					, SiteA: 105
					, SiteB: 90
			, }
				, {
					period: '2016'
					, SiteA: 250
					, SiteB: 150
			, }]
			, xkey: 'period'
			, ykeys: ['SiteA', 'SiteB']
			, labels: ['<?=NEW1?>', '<?=QUALIFIED?>']
			, pointSize: 0
			, fillOpacity: 0.4
			, pointStrokeColors: ['#b4becb', '#01c0c8']
			, behaveLikeLine: true
			, gridLineColor: '#e0e0e0'
			, lineWidth: 0
			, smooth: false
			, hideHover: 'auto'
			, lineColors: ['#b4becb', '#01c0c8']
			, resize: true
		});
	});    
		// sparkline
		var sparklineLogin = function() { 
			$('#sales1').sparkline([20, 40, 30], {
				type: 'pie',
				height: '90',
				resize: true,
				sliceColors: ['#01c0c8', '#7d5ab6', '#ffffff']
			});
			$('#sparkline2dash').sparkline([6, 10, 9, 11, 9, 10, 12], {
				type: 'bar',
				height: '154',
				barWidth: '4',
				resize: true,
				barSpacing: '10',
				barColor: '#25a6f7'
			});
			
		};    
		var sparkResize;
 
        $(window).resize(function(e) {
            clearTimeout(sparkResize);
            sparkResize = setTimeout(sparklineLogin, 500);
        });
        sparklineLogin();

    </script>
	
	<script src="../backend_assets/node_modules/moment/moment.js"></script>
	<script src='../backend_assets/node_modules/calendar/dist/fullcalendar.js'></script>
	
	<script>
	!function($) {
		"use strict";

		var CalendarApp = function() {
			this.$body = $("body")
			this.$calendar = $('#calendar'),
			this.$event = ('#calendar-events div.calendar-events'),
			this.$categoryForm = $('#add-new-event form'),
			this.$extEvents = $('#calendar-events'),
			this.$modal = $('#my-event'),
			this.$saveCategoryBtn = $('.save-category'),
			this.$calendarObj = null
		};


		/* on drop */
		CalendarApp.prototype.onDrop = function (eventObj, date) { 
		},
		/* on click on event */
		CalendarApp.prototype.onEventClick =  function (calEvent, jsEvent, view) {
			//alert(calEvent.id)
			if(calEvent.id != '')
				window.location.href = calEvent.id
		},
		/* on select */
		CalendarApp.prototype.onSelect = function (start, end, allDay) {
		},
		CalendarApp.prototype.enableDrag = function() {
		}
		/* Initializing */
		CalendarApp.prototype.init = function() {
			this.enableDrag();
			/*  Initialize the calendar  */
			var date = new Date();
			var d = date.getDate();
			var m = date.getMonth();
			var y = date.getFullYear();
			var form = '';
			var today = new Date($.now());

			var $this = this;
			$this.$calendarObj = $this.$calendar.fullCalendar({
				slotDuration: '00:15:00', /* If we want to split day time each 15minutes */
				minTime: '00:00:00',
				maxTime: '24:00:00',  
				defaultView: 'month',  
				handleWindowResize: true,   
				 
				header: {
					left: 'prev,next today',
					center: 'title',
					right: 'month,agendaWeek,agendaDay'
				},
				editable: false,
				droppable: false, // this allows things to be dropped onto the calendar !!!
				eventLimit: false, // allow "more" link when too many events
				selectable: false,
				drop: function(date) { $this.onDrop($(this), date); },
				select: function (start, end, allDay) { $this.onSelect(start, end, allDay); },
				eventClick: function(calEvent, jsEvent, view) { $this.onEventClick(calEvent, jsEvent, view); },
				
				events: function( start, end, timezone, callback ) { //include the parameters fullCalendar supplies to you!

					var st1 = new Date(start)
					//alert(st1.getMonth()+"-"+st1.getFullYear()+"-"+st1.getDate())
					var st = st1.getFullYear()+'-'+(st1.getMonth() + 1)+'-'+st1.getDate()
					
					var et1 = new Date(end)
					//alert(et1.getMonth()+" - "+et1.getFullYear())
					var et = et1.getFullYear()+'-'+(et1.getMonth() + 1)+'-'+et1.getDate()
					
					//alert(start)
					
					var data  = 'st='+st+'&et='+et+"&CAL_PK_EMPLOYEE_MASTER="+$('#CAL_PK_EMPLOYEE_MASTER').val()+"&CAL_PK_TASK_TYPE="+$('#CAL_PK_TASK_TYPE').val()+"&CAL_PK_CAMPUS="+$('#CAL_PK_CAMPUS').val();
					var value = $.ajax({
						url: "ajax_get_calendar_events",	
						type: "POST",		 
						data: data,	
						dataType: 'json',
						async: false,
						cache: false,
						success: function (result) {	
							//alert(result)
							var events = [];
							$.each(result, function (i, item) {
								//alert(result[i].title)
								//alert(result[i].start+"\n"+new Date(result[i].start))
								events.push({
									id: result[i].id,
									title: result[i].title,
									start: new Date(result[i].start),
									end: new Date(result[i].end),
									className: result[i].className,
								});
							})			
							callback(events);
							//alert('aa')
						}						
					}).responseText;
					
				}

			});
		},

	   //init CalendarApp
		$.CalendarApp = new CalendarApp, $.CalendarApp.Constructor = CalendarApp
		
	}(window.jQuery),

	//initializing CalendarApp
	function($) {
		"use strict";
		$.CalendarApp.init()
	}(window.jQuery);
	
	function get_activity(id){
		jQuery(document).ready(function($) {
			var data  = 'PK_EMPLOYEE_MASTER='+id;
			//alert(data)
			var value = $.ajax({
				url: "ajax_dashboard_activity",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {
					//alert(data)
					document.getElementById('activity_div').innerHTML = data
				}		
			}).responseText;
		});
	}
	</script>
	
	<script src="../backend_assets/dist/js/pages/chat.js"></script>
	
	<!-- Ticket # 1006 -->
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#CAL_PK_TASK_TYPE').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All Task Types',
				nonSelectedText: 'Task Type',
				numberDisplayed: 1,
				nSelectedText: 'Task Type selected'
			});
			
			/* Ticket # 1299 */
			$('#CAL_PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All Campuses',
				nonSelectedText: 'Campus',
				numberDisplayed: 1,
				nSelectedText: 'Campus selected'
			});
			/* Ticket # 1299 */
		});
		
		function search_cal(){
			jQuery(document).ready(function($) {
				$("#calendar").fullCalendar("refetchEvents");
				//window.location.href="index?CAL_PK_EMPLOYEE_MASTER="+$('#CAL_PK_EMPLOYEE_MASTER').val()+"&CAL_PK_TASK_TYPE="+$('#CAL_PK_TASK_TYPE').val()+"&CAL_PK_CAMPUS="+$('#CAL_PK_CAMPUS').val()+"#cal1" //Ticket # 1299
			});
		}
	</script>
		<!-- Ticket # 1006 -->

		<!-- dvb 14 11 2024 nubo pop up -->
	<?php 
	$sQuery_AnnouncementPopup = "SELECT 
									*
								FROM 
									Z_ANNOUNCEMENT
								WHERE
								ACTIVE = 1 
								AND ANNOUNCEMENT_FROM = 3 
								AND '$cur_time_cet' BETWEEN START_DATE_TIME_CET 
								AND END_DATE_TIME_CET 
										
								ORDER BY 
									START_DATE_TIME_CET ASC ";
	// echo $sQuery_AnnouncementPopup;exit;
	$res_annoucement_popup = $db->Execute($sQuery_AnnouncementPopup);  

	if($res_annoucement_popup->RecordCount() > 0){ ?>
		<!-- Button trigger modal -->
		<button type="button" class="btn btn-primary d-none btn_modalpopup" data-toggle="modal" data-target="#modalpopup">
		  Launch demo modal
		</button>
		<!-- Modal -->
		<div class="modal fade" id="modalpopup" tabindex="-1" role="dialog" aria-labelledby="modalpopupLabel" aria-hidden="true">
		  <div class="modal-dialog modal-lg" role="document">
		    <div class="modal-content">
		      <!-- <div class="modal-header">
		        <h5 class="modal-title" id="modalpopupLabel">Announcement POP UP</h5>
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true">&times;</span>
		        </button>
		      </div> -->
		      <div class="modal-body modalbodypopup">
		        <?php 
			        $idslooked = '';
			        while (!$res_annoucement_popup->EOF) {

			        	echo '<div id="contentpopup_'.$res_annoucement_popup->fields['PK_ANNOUNCEMENT'].'">';
							echo '<h2>'.$res_annoucement_popup->fields['HEADER'].'</h2>';
							
				        	if(!empty($res_annoucement_popup->fields['IMAGE'])){
			        			echo '<img src="'.$res_annoucement_popup->fields['IMAGE'].'" class="img-fluid mx-auto d-block" loading="lazy">';
				        	}
							

							if(!empty($res_annoucement_popup->fields['SHORT_DESC_ENG'])){
								echo '<h3>'.$res_annoucement_popup->fields['SHORT_DESC_ENG'].'</h3>';
							}
							if(!empty($res_annoucement_popup->fields['SHORT_DESC_SPA'])){
								echo '<h4>'.$res_annoucement_popup->fields['SHORT_DESC_SPA'].'</h4>';
							}
							if(!empty($res_annoucement_popup->fields['DESC_ENG'])){
								echo '<p class="lead">'.$res_annoucement_popup->fields['DESC_ENG'].'</p>';
							}
							if(!empty($res_annoucement_popup->fields['DESC_SPA'])){
								echo '<p>'.$res_annoucement_popup->fields['DESC_SPA'].'</p>';
							}
							echo '<hr>
						</div>';
						
						//
						$idslooked.=$res_annoucement_popup->fields['PK_ANNOUNCEMENT'].',';
						$res_annoucement_popup->MoveNext();
					}
					$idslooked = rtrim($idslooked, ',');
		        ?>
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-secondary btn-block" data-dismiss="modal">Close</button>
		      </div>
		    </div>
		  </div>
		</div>
		<script type="text/javascript">
		jQuery(document).ready(function($) {

			// Obtener los IDs de sessionStorage
            var idsLooked = sessionStorage.getItem("idsreadedannouncementpopup");
            if (idsLooked) {
                // Convertir los IDs en un array
                var idsArray = idsLooked.split(',');

                // Recorrer los IDs y eliminar las divs correspondientes
                idsArray.forEach(function(id) {
                    var divId = '#contentpopup_' + id.trim();
                    $(divId).remove();
                });
            }
            //
            if ($('.modalbodypopup').html().trim() !== '') {
				$('.btn_modalpopup').click();
			}
			//
			sessionStorage.setItem("idsreadedannouncementpopup", <?=$idslooked?>);
		});
		</script>
	<?php } ?>

<!-- dvb 14 11 2024 nubo pop up -->
</body>

</html>
