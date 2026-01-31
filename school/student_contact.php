<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/student.php"); //ticket #1064
require_once("../language/student_contact.php");
require_once("check_access.php");

if (check_access('ADMISSION_ACCESS') == 0 && check_access('REGISTRAR_ACCESS') == 0 && check_access('FINANCE_ACCESS') == 0 && check_access('ACCOUNTING_ACCESS') == 0 && check_access('PLACEMENT_ACCESS') == 0) {
	header("location:../index");
	exit;
}

if (!empty($_POST)) {
	//echo "<pre>";print_r($_POST);exit;
	$STUDENT_CONTACT = $_POST;
	$STUDENT_CONTACT['OPT_OUT']   = $_POST['OPT_OUT'];
	$STUDENT_CONTACT['USE_EMAIL'] = $_POST['USE_EMAIL'];

	$STUDENT_CONTACT['HOME_PHONE_INVALID'] 	= $_POST['HOME_PHONE_INVALID'];
	$STUDENT_CONTACT['WORK_PHONE_INVALID'] 	= $_POST['WORK_PHONE_INVALID'];
	$STUDENT_CONTACT['CELL_PHONE_INVALID'] 	= $_POST['CELL_PHONE_INVALID'];
	$STUDENT_CONTACT['OTHER_PHONE_INVALID'] = $_POST['OTHER_PHONE_INVALID'];
	$STUDENT_CONTACT['ADDRESS_INVALID'] 	= $_POST['ADDRESS_INVALID'];
	$STUDENT_CONTACT['EMAIL_INVALID'] 		= $_POST['EMAIL_INVALID'];
	$STUDENT_CONTACT['EMAIL_OTHER_INVALID'] = $_POST['EMAIL_OTHER_INVALID'];
	$STUDENT_CONTACT['USE_SECONDARY_EMAIL_AS_DEFAULT_STD'] = $_POST['USE_SECONDARY_EMAIL_AS_DEFAULT_STD'];


	$STUDENT_CONTACT['HOME_PHONE_CODE'] = $_POST['HOME_PHONE_CODE'];
	$STUDENT_CONTACT['WORK_PHONE_CODE'] = $_POST['WORK_PHONE_CODE'];
	$STUDENT_CONTACT['CELL_PHONE_CODE'] = $_POST['CELL_PHONE_CODE'];
	$STUDENT_CONTACT['OTHER_PHONE_CODE'] = $_POST['OTHER_PHONE_CODE'];

	if ($_GET['id'] == '' || $_GET['act'] == 'dup') {

		if ($_POST['PK_STUDENT_CONTACT_TYPE_MASTER'] == 1 || $_POST['PK_STUDENT_CONTACT_TYPE_MASTER'] == 4) {
			if ($_POST['PK_STUDENT_CONTACT_TYPE_MASTER'] == 1)
				$PK_STUDENT_CONTACT_TYPE_MASTER = 6;

			if ($_POST['PK_STUDENT_CONTACT_TYPE_MASTER'] == 4)
				$PK_STUDENT_CONTACT_TYPE_MASTER = 5;

			$db->Execute("UPDATE S_STUDENT_CONTACT SET PK_STUDENT_CONTACT_TYPE_MASTER = '$PK_STUDENT_CONTACT_TYPE_MASTER' WHERE PK_STUDENT_MASTER = '$_GET[sid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '$_POST[PK_STUDENT_CONTACT_TYPE_MASTER]' ");
		}

		$STUDENT_CONTACT['PK_ACCOUNT']   		= $_SESSION['PK_ACCOUNT'];
		$STUDENT_CONTACT['PK_STUDENT_MASTER']   = $_GET['sid'];
		$STUDENT_CONTACT['CREATED_BY']  		= $_SESSION['PK_USER'];
		$STUDENT_CONTACT['CREATED_ON']  		= date("Y-m-d H:i");
		db_perform('S_STUDENT_CONTACT', $STUDENT_CONTACT, 'insert');
	} else {
		$STUDENT_CONTACT['EDITED_BY']  = $_SESSION['PK_USER'];
		$STUDENT_CONTACT['EDITED_ON']  = date("Y-m-d H:i");
		db_perform('S_STUDENT_CONTACT', $STUDENT_CONTACT, 'update', " PK_STUDENT_CONTACT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	//echo "<pre>";print_r($STUDENT_CONTACT);exit;
	account_is_updated($_GET['sid'] , $_GET["eid"]);
	header("location:student?id=" . $_GET['sid'] . '&tab=contactTab&eid=' . $_GET['eid'] . '&t=' . $_GET['t']);
}
if ($_GET['id'] == '') {
	$PK_STUDENT_CONTACT_TYPE_MASTER = '';
	$PK_STUDENT_RELATIONSHIP_MASTER = '';
	$COMPANY_NAME			= '';
	$CONTACT_NAME			= '';
	$CONTACT_TITLE			= '';
	$ADDRESS				= '';
	$ADDRESS_1				= '';
	$CITY					= '';
	$PK_STATES				= '';
	$ZIP					= '';
	$PK_COUNTRY				= '';
	$HOME_PHONE				= '';
	$WORK_PHONE				= '';
	$CELL_PHONE				= '';
	$OTHER_PHONE			= '';
	$FAX					= '';
	$EMAIL					= '';
	$EMAIL_OTHER			= '';
	$USE_EMAIL				= '';
	$OPT_OUT				= '';
	$WEBSITE				= '';

	$HOME_PHONE_INVALID 	= '';
	$WORK_PHONE_INVALID 	= '';
	$CELL_PHONE_INVALID 	= '';
	$OTHER_PHONE_INVALID 	= '';
	$ADDRESS_INVALID 		= '';
	$EMAIL_INVALID 			= '';
	$EMAIL_OTHER_INVALID 	= '';
	$CONTACT_DESCRIPTION	= '';
	$res_from_z_acc = $db->Execute("SELECT * FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	$USE_SECONDARY_EMAIL_AS_DEFAULT_STD = $res_from_z_acc->fields['USE_SECONDARY_EMAIL_AS_DEFAULT'];
	
} else {

	$res = $db->Execute("SELECT * FROM S_STUDENT_CONTACT WHERE PK_STUDENT_CONTACT = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if ($res->RecordCount() == 0) {
		header("location:student?id=" . $_GET['sid'] . '&tab=taskTab&t=' . $_GET['t']);
		exit;
	}

	$PK_STUDENT_CONTACT_TYPE_MASTER = $res->fields['PK_STUDENT_CONTACT_TYPE_MASTER'];
	$PK_STUDENT_RELATIONSHIP_MASTER = $res->fields['PK_STUDENT_RELATIONSHIP_MASTER'];
	$COMPANY_NAME			= $res->fields['COMPANY_NAME'];
	$CONTACT_NAME			= $res->fields['CONTACT_NAME'];
	$CONTACT_TITLE			= $res->fields['CONTACT_TITLE'];
	$ADDRESS				= $res->fields['ADDRESS'];
	$ADDRESS_1				= $res->fields['ADDRESS_1'];
	$CITY					= $res->fields['CITY'];
	$PK_STATES				= $res->fields['PK_STATES'];
	$ZIP					= $res->fields['ZIP'];
	$PK_COUNTRY				= $res->fields['PK_COUNTRY'];
	$HOME_PHONE				= $res->fields['HOME_PHONE'];
	$WORK_PHONE				= $res->fields['WORK_PHONE'];
	$CELL_PHONE				= $res->fields['CELL_PHONE'];
	$OTHER_PHONE			= $res->fields['OTHER_PHONE'];
	$FAX					= $res->fields['FAX'];
	$EMAIL					= $res->fields['EMAIL'];
	$EMAIL_OTHER			= $res->fields['EMAIL_OTHER'];
	$USE_EMAIL				= $res->fields['USE_EMAIL'];
	$ACTIVE					= $res->fields['ACTIVE'];
	$OPT_OUT				= $res->fields['OPT_OUT'];
	$WEBSITE				= $res->fields['WEBSITE'];

	$HOME_PHONE_INVALID 	= $res->fields['HOME_PHONE_INVALID'];
	$WORK_PHONE_INVALID 	= $res->fields['WORK_PHONE_INVALID'];
	$CELL_PHONE_INVALID 	= $res->fields['CELL_PHONE_INVALID'];
	$OTHER_PHONE_INVALID 	= $res->fields['OTHER_PHONE_INVALID'];
	$ADDRESS_INVALID 		= $res->fields['ADDRESS_INVALID'];
	$EMAIL_INVALID 			= $res->fields['EMAIL_INVALID'];
	$EMAIL_OTHER_INVALID 	= $res->fields['EMAIL_OTHER_INVALID'];
	$CONTACT_DESCRIPTION	= $res->fields['CONTACT_DESCRIPTION'];
	$USE_SECONDARY_EMAIL_AS_DEFAULT_STD	= $res->fields['USE_SECONDARY_EMAIL_AS_DEFAULT_STD'];
	
	
	$HOME_PHONE_CODE = $res->fields['HOME_PHONE_CODE'];
	$WORK_PHONE_CODE = $res->fields['WORK_PHONE_CODE'];
	$CELL_PHONE_CODE = $res->fields['CELL_PHONE_CODE'];
	$OTHER_PHONE_CODE = $res->fields['OTHER_PHONE_CODE'];
}

$res = $db->Execute("SELECT IMAGE,FIRST_NAME,LAST_NAME,MIDDLE_NAME,OTHER_NAME FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_GET[sid]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$IMAGE					= $res->fields['IMAGE'];
$FIRST_NAME 			= $res->fields['FIRST_NAME'];
$LAST_NAME 				= $res->fields['LAST_NAME'];
$MIDDLE_NAME	 		= $res->fields['MIDDLE_NAME'];
$OTHER_NAME	 			= $res->fields['OTHER_NAME'];

/* ticket #1116 */
$res = $db->Execute("SELECT STUDENT_ID, STATUS_DATE,STUDENT_STATUS,CODE, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IF(DETERMINATION_DATE = '0000-00-00','',DATE_FORMAT(DETERMINATION_DATE, '%m/%d/%Y' )) AS DETERMINATION_DATE, IF(DROP_DATE = '0000-00-00','',DATE_FORMAT(DROP_DATE, '%m/%d/%Y' )) AS DROP_DATE , IF(GRADE_DATE = '0000-00-00','',DATE_FORMAT(GRADE_DATE, '%m/%d/%Y' )) AS GRADE_DATE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA, '%m/%d/%Y' )) AS LDA, IF(ORIGINAL_EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(ORIGINAL_EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS ORIGINAL_EXPECTED_GRAD_DATE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$_GET[sid]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' "); //Ticket # 1537
$STATUS_DATE 	 	= $res->fields['STATUS_DATE'];
$STUDENT_STATUS	 	= $res->fields['STUDENT_STATUS'];
$CAMPUS_PROGRAM  	= $res->fields['CODE'];
$FIRST_TERM_DATE 	= $res->fields['BEGIN_DATE_1'];
$EXPECTED_GRAD_DATE = $res->fields['EXPECTED_GRAD_DATE'];
$STUDENT_ID 		= $res->fields['STUDENT_ID']; //Ticket # 1537

$ORIGINAL_EXPECTED_GRAD_DATE 	= $res->fields['ORIGINAL_EXPECTED_GRAD_DATE'];
$DETERMINATION_DATE 			= $res->fields['DETERMINATION_DATE'];
$DROP_DATE 						= $res->fields['DROP_DATE'];
$GRADE_DATE 					= $res->fields['GRADE_DATE'];
$LDA 							= $res->fields['LDA'];
/* ticket #1116 */

/* Ticket # 1534 */
$res = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT > 0 ");
$HEADER_CAMPUS_CODE = $res->fields['CAMPUS_CODE'];
/* Ticket # 1534 */

if ($STATUS_DATE != '0000-00-00')
	$STATUS_DATE = date("m/d/Y", strtotime($STATUS_DATE));
else
	$STATUS_DATE = '';

$has_warning_notes 	= 0;
$warning_notes 		= '';

$res_note = $db->Execute("select NOTES,DEPARTMENT FROM S_STUDENT_NOTES LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_NOTES.PK_DEPARTMENT, M_NOTE_TYPE WHERE PK_NOTE_TYPE_MASTER = 1 AND S_STUDENT_NOTES.PK_NOTE_TYPE = M_NOTE_TYPE.PK_NOTE_TYPE AND S_STUDENT_NOTES.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_NOTES.PK_STUDENT_MASTER = '$_GET[sid]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND SATISFIED = 0 ");

if ($res_note->RecordCount() > 0) {
	$has_warning_notes = 1;
	$warning_notes = '';
	while (!$res_note->EOF) {
		if ($warning_notes != '')
			$warning_notes .= ', ';

		$warning_notes .= 'See ' . $res_note->fields['DEPARTMENT'];
		$res_note->MoveNext();
	}
	$warning_notes = 'Warning - ' . $warning_notes;
}
$res_probation = $db->Execute("select PK_STUDENT_PROBATION FROM S_STUDENT_PROBATION WHERE PK_PROBATION_STATUS = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[sid]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' ");
if ($res_probation->RecordCount() > 0) {
	$has_warning_notes = 1;
	if ($warning_notes != '')
		$warning_notes .= '<br />';

	$warning_notes .= 'On Probation';
}

$country_options = '<option value="1">United States (+1)</option>';
$country_option_sql = "SELECT * FROM Z_COUNTRY WHERE ACTIVE = '1' ORDER BY NAME ASC";
$COUNTRY_CODES = $db->Execute($country_option_sql);
while (!$COUNTRY_CODES->EOF) {
	# code...
	$country_options .= "<option value='".$COUNTRY_CODES->fields['PK_COUNTRY']."'>".$COUNTRY_CODES->fields['NAME']." (+".$COUNTRY_CODES->fields['ISO_DIAL'].")</option>";
	$COUNTRY_CODES->MoveNext();
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
	<link href="../backend_assets/node_modules/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet">
	<link href="../backend_assets/dist/css/pages/user-card.css" rel="stylesheet">
	<title><?= STUDENT_CONTACT_PAGE_TITLE ?> | <?= $title ?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
	<? require_once("pre_load.php"); ?>
	<div id="main-wrapper">
		<? require_once("menu.php"); ?>
		<div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles" <? if ($has_warning_notes == 1) { ?> style="background-color: #d12323 !important;color: #fff;" <? } ?>>
					<!-- ticket 1116 -->
					<div class="col-md-8 align-self-center" style="flex: 0 0 65.0%;max-width: 65.0%;"> <!-- ticket #1534 -->
						<table width="100%">
							<tr>
								<td width="13%">
									<h4 class="text-themecolor" <? if ($has_warning_notes == 1) { ?> style="color: #fff;" <? } ?>><? if ($_GET['id'] == '') echo ADD;
																																else if ($_GET['act'] == 'dup') echo DUPLICATE;
																																else echo EDIT; ?> <?= STUDENT_CONTACT_PAGE_TITLE ?> </h4>
									<br />
								</td>
								<td><b><?= $LAST_NAME . ', ' . $FIRST_NAME . ' ' . $MIDDLE_NAME ?></b><br /><br /></td><!-- Ticket # 1715 -->
								<td colspan="3" valign="top"><?= $warning_notes ?></td>
							</tr>
							<!-- ticket #1537 -->
							<tr>
								<td rowspan="5">
									<? if ($IMAGE != '') { ?>
										<div class="row el-element-overlay" style="width: 85%;">
											<div class="card" style="margin-bottom: 0;margin-left: 10px;">
												<div class="el-card-item" style="padding-bottom:0">
													<div class="el-card-avatar el-overlay-1" style="margin-bottom: 0;">
														<img src="<?= $IMAGE ?>" alt="user" />
														<div class="el-overlay">
															<ul class="el-info">
																<li><a class="btn default btn-outline image-popup-vertical-fit" href="<?= $IMAGE ?>"><i class="icon-magnifier"></i></a></li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</div>
										<!--<img src="<?= $IMAGE ?>" style="height: 80px;" />-->
									<? } ?>
								</td>
								<td width="19%"><b><?= STUDENT_ID . ':' ?></b></td>
								<td width="29%"><?= $STUDENT_ID; ?></td>
								<td width="18%"></b></td>
								<td width="11%"></td>
							</tr>

							<!-- Ticket # 1715 -->
							<tr>
								<td><b><?= ENROLLMENT . ':' ?></b></td>
								<td><?= $FIRST_TERM_DATE . ' - ' . $CAMPUS_PROGRAM . ' - ' . $STUDENT_STATUS . ' - ' . $HEADER_CAMPUS_CODE; ?></td>
								<td>&nbsp;&nbsp;<b><?= DETERMINATION_DATE . ':' ?></b></td>
								<td><?= $DETERMINATION_DATE ?></td>
							</tr>
							<tr>
								<td><b><?= STATUS_DATE . ':' ?></b></td>
								<td><?= $STATUS_DATE ?></td>
								<td>&nbsp;&nbsp;<b><?= DROP_DATE . ':' ?></b></td>
								<td><?= $DROP_DATE ?></td>
							</tr>
							<!-- Ticket # 1715 -->

							<tr>
								<td><b><?= EXPECTED_GRAD_DATE . ':' ?></b></td>
								<td><?= $EXPECTED_GRAD_DATE ?></td>
								<td>&nbsp;&nbsp;<b><?= GRADE_DATE . ':' ?></b></td>
								<td><?= $GRADE_DATE ?></td>
							</tr>
							<tr>
								<td><b><?= ORIGINAL_EXPECTED_GRAD_DATE_1 . ':' ?></b></td>
								<td><?= $ORIGINAL_EXPECTED_GRAD_DATE ?></td>
								<td>&nbsp;&nbsp;<b><?= LDA . ':' ?></b></td>
								<td><?= $LDA ?></td>
							</tr>
						</table>
					</div>
					<!-- ticket 1116 -->
				</div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form class="floating-labels m-t-40" method="post" name="form1" id="form1" autocomplete="off">
									<div class="row">
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select id="PK_STUDENT_CONTACT_TYPE_MASTER" name="PK_STUDENT_CONTACT_TYPE_MASTER" class="form-control required-entry" onchange="show_emergency(this.value)">
													<option></option>
													<? /* Ticket #1149  */
													$act_type_cond = " AND ACTIVE = 1 ";
													if ($PK_STUDENT_CONTACT_TYPE_MASTER > 0)
														$act_type_cond = " AND (ACTIVE = 1 OR PK_STUDENT_CONTACT_TYPE_MASTER = '$PK_STUDENT_CONTACT_TYPE_MASTER' ) ";

													$res_type = $db->Execute("select PK_STUDENT_CONTACT_TYPE_MASTER, STUDENT_CONTACT_TYPE from M_STUDENT_CONTACT_TYPE_MASTER WHERE 1 = 1 $act_type_cond order by STUDENT_CONTACT_TYPE ASC");
													while (!$res_type->EOF) { ?>
													<?php if(has_wvjc_access($_SESSION['PK_ACCOUNT'])){ ?>

														<option value="<?= $res_type->fields['PK_STUDENT_CONTACT_TYPE_MASTER'] ?>" <? if ($PK_STUDENT_CONTACT_TYPE_MASTER == $res_type->fields['PK_STUDENT_CONTACT_TYPE_MASTER']) echo "selected"; ?>><?= $res_type->fields['STUDENT_CONTACT_TYPE'] ?></option>


														<? }else{ ?>

														<?php if($res_type->fields['STUDENT_CONTACT_TYPE']!='Parent Plus Loan'){ ?>	
														<option value="<?= $res_type->fields['PK_STUDENT_CONTACT_TYPE_MASTER'] ?>" <? if ($PK_STUDENT_CONTACT_TYPE_MASTER == $res_type->fields['PK_STUDENT_CONTACT_TYPE_MASTER']) echo "selected"; ?>><?= $res_type->fields['STUDENT_CONTACT_TYPE'] ?></option>
														<? } ?>

														<? } ?>
													<? $res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_STUDENT_CONTACT_TYPE_MASTER">
													<?= STUDENT_CONTACT_TYPE ?>
												</label>
											</div>
										</div>

										<? $style 			 = "display:none";
										$style_company  	 = "display:none";
										$style_relation 	 = "display:none";
										$style_contact_title = "display:none";
										$style_website		 = "display:none";

										if ($PK_STUDENT_CONTACT_TYPE_MASTER != 1 && $PK_STUDENT_CONTACT_TYPE_MASTER != 6 && $PK_STUDENT_CONTACT_TYPE_MASTER != '')
											$style = "display:block";

										if ($PK_STUDENT_CONTACT_TYPE_MASTER != 1 && $PK_STUDENT_CONTACT_TYPE_MASTER != 6 && $PK_STUDENT_CONTACT_TYPE_MASTER != 3 && $PK_STUDENT_CONTACT_TYPE_MASTER != '')
											$style_relation = "display:block";

										if ($PK_STUDENT_CONTACT_TYPE_MASTER == 3) {
											$style_company 		 = "display:block";
											$style_contact_title = "display:block";
											$style_website		 = "display:block";
										} ?>

										<div class="col-md-3" id="COMPANY_NAME_DIV" style="<?= $style_company ?>">
											<div class="form-group m-b-40">
												<input id="COMPANY_NAME" name="COMPANY_NAME" type="text" class="form-control <? if ($PK_STUDENT_CONTACT_TYPE_MASTER != 1) { ?> required-entry <? } ?> " value="<?= $COMPANY_NAME ?>">
												<span class="bar"></span>
												<label for="COMPANY_NAME"><?= COMPANY_NAME ?></label>
											</div>
										</div>

										<div class="col-md-3" id="CONTACT_NAME_DIV" style="<?= $style ?>">
											<div class="form-group m-b-40">
												<input id="CONTACT_NAME" name="CONTACT_NAME" type="text" class="form-control <? if ($PK_STUDENT_CONTACT_TYPE_MASTER != 1) { ?> required-entry <? } ?> " value="<?= $CONTACT_NAME ?>">
												<span class="bar"></span>
												<label for="CONTACT_NAME"><?= CONTACT_NAME ?></label>
											</div>
										</div>

										<div class="col-md-3" id="CONTACT_TITLE_DIV" style="<?= $style_contact_title ?>">
											<div class="form-group m-b-40">
												<input id="CONTACT_TITLE" name="CONTACT_TITLE" type="text" class="form-control " value="<?= $CONTACT_TITLE ?>">
												<span class="bar"></span>
												<label for="CONTACT_TITLE"><?= CONTACT_TITLE ?></label>
											</div>
										</div>

										<div class="col-md-3" id="PK_STUDENT_RELATIONSHIP_MASTER_DIV" style="<?= $style_relation ?>">
											<div class="form-group m-b-40" id="PK_STUDENT_RELATIONSHIP_MASTER_DIV1">
												<select id="PK_STUDENT_RELATIONSHIP_MASTER" name="PK_STUDENT_RELATIONSHIP_MASTER" class="form-control <? if ($PK_STUDENT_CONTACT_TYPE_MASTER != 1) { ?> required-entry <? } ?> ">
													<option></option>
													<? /* Ticket #1149  */
													$act_type_cond = " AND ACTIVE = 1 ";
													if ($PK_STUDENT_RELATIONSHIP_MASTER > 0)
														$act_type_cond = " AND (ACTIVE = 1 OR PK_STUDENT_RELATIONSHIP_MASTER = '$PK_STUDENT_RELATIONSHIP_MASTER' ) ";

													$res_type = $db->Execute("select PK_STUDENT_RELATIONSHIP_MASTER, STUDENT_RELATIONSHIP from M_STUDENT_RELATIONSHIP_MASTER WHERE 1 = 1 $act_type_cond order by STUDENT_RELATIONSHIP ASC"); //Ticket # 1895
													while (!$res_type->EOF) { ?>
														<option value="<?= $res_type->fields['PK_STUDENT_RELATIONSHIP_MASTER'] ?>" <? if ($PK_STUDENT_RELATIONSHIP_MASTER == $res_type->fields['PK_STUDENT_RELATIONSHIP_MASTER']) echo "selected"; ?>><?= $res_type->fields['STUDENT_RELATIONSHIP'] ?></option>
													<? $res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span>
												<label for="PK_STUDENT_RELATIONSHIP_MASTER">
													<?= STUDENT_RELATIONSHIP ?>
												</label>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-3">
													<h4 class="card-title" style="margin-bottom: 25px;"><?= ADDRESS ?></h4>
												</div>
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
															<input type="checkbox" class="custom-control-input" id="ADDRESS_INVALID" name="ADDRESS_INVALID" value="1" <? if ($ADDRESS_INVALID == 1) echo "checked"; ?>>
															<label class="custom-control-label" for="ADDRESS_INVALID"><?= INVALID ?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input id="ADDRESS" name="ADDRESS" type="text" class="form-control" value="<?= $ADDRESS ?>">
														<span class="bar"></span>
														<label for="ADDRESS"><?= ADDRESS ?></label>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input id="ADDRESS_1" name="ADDRESS_1" type="text" class="form-control" value="<?= $ADDRESS_1 ?>">
														<span class="bar"></span>
														<label for="ADDRESS_1"><?= ADDRESS_1 ?></label>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input id="CITY" name="CITY" type="text" class="form-control" value="<?= $CITY ?>">
														<span class="bar"></span>
														<label for="CITY"><?= CITY ?></label>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select id="PK_STATES" name="PK_STATES" class="form-control"> <!-- onchange="get_country(this.value,'PK_COUNTRY')" -->
															<option selected></option>
															<? $res_type = $db->Execute("select PK_STATES, STATE_NAME from Z_STATES WHERE ACTIVE = '1' ORDER BY STATE_NAME ASC ");
															while (!$res_type->EOF) { ?>
																<option value="<?= $res_type->fields['PK_STATES'] ?>" <? if ($PK_STATES == $res_type->fields['PK_STATES']) echo "selected"; ?>><?= $res_type->fields['STATE_NAME'] ?></option>
															<? $res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span>
														<label for="PK_STATES"><?= STATE ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input id="ZIP" name="ZIP" type="text" class="form-control" value="<?= $ZIP ?>">
														<span class="bar"></span>
														<label for="ZIP"><?= ZIP ?></label>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group m-b-40" id="PK_COUNTRY_LABEL">
														<div id="PK_COUNTRY_DIV">
															<select id="PK_COUNTRY" name="PK_COUNTRY" class="form-control">
																<option selected></option>
																<? $res_type1 = $db->Execute("select PK_COUNTRY, NAME from Z_COUNTRY WHERE ACTIVE = '1' ORDER BY NAME ASC ");
																while (!$res_type1->EOF) { ?>
																	<option value="<?= $res_type1->fields['PK_COUNTRY'] ?>" <? if ($PK_COUNTRY == $res_type1->fields['PK_COUNTRY']) echo "selected"; ?>><?= $res_type1->fields['NAME'] ?></option>
																<? $res_type1->MoveNext();
																}
																?>
															</select>
														</div>
														<span class="bar"></span>
														<label for="PK_COUNTRY"><?= COUNTRY ?></label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<textarea id="CONTACT_DESCRIPTION" name="CONTACT_DESCRIPTION" class="form-control"><?= $CONTACT_DESCRIPTION ?></textarea>
														<span class="bar"></span>
														<label for="CONTACT_DESCRIPTION"><?= CONTACT_DESCRIPTION ?></label>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-6">
													<h4 class="card-title" style="margin-bottom: 25px;"><?= PHONE_NUMBERS ?></h4>
												</div>
											</div>
											<div class="row">
												<div class="col-md-5 focused">
													<div class="form-group m-b-40">
														 
														<input id="HOME_PHONE_CODE_DISP" class="form-control d-inline" value="+1"  style="width: 60px;" disabled>	
														<select id="HOME_PHONE_CODE" name="HOME_PHONE_CODE" class="form-control d-inline" style="width: 20px;" onchange="updatePlaceholder(this.id)">
															<?php echo $country_options; ?>
														</select>  
														<input id="HOME_PHONE" name="HOME_PHONE" type="text" class="form-control phone-inputmask d-inline col-sm-7" value="<?= $HOME_PHONE ?>">
														<span class="bar"></span>
														<label for="HOME_PHONE" id="HOME_PHONE_LABEL">
															<? if ($PK_STUDENT_CONTACT_TYPE_MASTER == 3) echo MAIN;
															else echo HOME_PHONE; ?>
														</label> 
													</div>
												</div>
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
															<input type="checkbox" class="custom-control-input" id="HOME_PHONE_INVALID" name="HOME_PHONE_INVALID" value="1" <? if ($HOME_PHONE_INVALID == 1) echo "checked"; ?>>
															<label class="custom-control-label" for="HOME_PHONE_INVALID"><?= INVALID ?></label>
														</div>
													</div>
												</div>

												
											</div>
											<div class="row">
											<div class="col-md-5 focused">
													<div class="form-group m-b-40">
													<input id="WORK_PHONE_CODE_DISP" class="form-control d-inline" value="+1"  style="width: 60px;" disabled>	
														<select id="WORK_PHONE_CODE" name="WORK_PHONE_CODE" class="form-control d-inline" style="width: 20px;" onchange="updatePlaceholder(this.id)">
															<?php echo $country_options; ?>
														</select>  
														<input id="WORK_PHONE" name="WORK_PHONE" type="text" class="form-control phone-inputmask d-inline col-sm-7" value="<?= $WORK_PHONE ?>">
														<span class="bar"></span>
														<label for="WORK_PHONE"><?= WORK_PHONE ?></label>
													</div>
												</div>
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
															<input type="checkbox" class="custom-control-input" id="WORK_PHONE_INVALID" name="WORK_PHONE_INVALID" value="1" <? if ($WORK_PHONE_INVALID == 1) echo "checked"; ?>>
															<label class="custom-control-label" for="WORK_PHONE_INVALID"><?= INVALID ?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-5 focused">
													<div class="form-group m-b-40">
													<input id="CELL_PHONE_CODE_DISP" class="form-control d-inline" value="+1"  style="width: 60px;" disabled>	
														<select id="CELL_PHONE_CODE" name="CELL_PHONE_CODE" class="form-control d-inline" style="width: 20px;" onchange="updatePlaceholder(this.id)">
															<?php echo $country_options; ?>
														</select>  
														<input id="CELL_PHONE" name="CELL_PHONE" type="text" class="form-control phone-inputmask d-inline col-sm-7" value="<?= $CELL_PHONE ?>">
														<span class="bar"></span>
														<label for="CELL_PHONE"><?= CELL_PHONE ?></label>
													</div>
												</div>
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
															<input type="checkbox" class="custom-control-input" id="OPT_OUT" name="OPT_OUT" value="1" <? if ($OPT_OUT == 1) echo "checked"; ?>>
															<label class="custom-control-label" for="OPT_OUT"><?= OPTOUT ?></label>
														</div>
													</div>
												</div>
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
															<input type="checkbox" class="custom-control-input" id="CELL_PHONE_INVALID" name="CELL_PHONE_INVALID" value="1" <? if ($CELL_PHONE_INVALID == 1) echo "checked"; ?>>
															<label class="custom-control-label" for="CELL_PHONE_INVALID"><?= INVALID ?></label>
														</div>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-5 focused">
													<div class="form-group m-b-40">
														<input id="OTHER_PHONE_CODE_DISP" class="form-control d-inline" value="+1"  style="width: 60px;" disabled>	
														<select id="OTHER_PHONE_CODE" name="OTHER_PHONE_CODE" class="form-control d-inline" style="width: 20px;" onchange="updatePlaceholder(this.id)">
															<?php echo $country_options; ?>
														</select>  
														<input id="OTHER_PHONE" name="OTHER_PHONE" type="text" class="form-control phone-inputmask d-inline col-sm-7" value="<?= $OTHER_PHONE ?>">
														<span class="bar"></span>
														<label for="OTHER_PHONE"><?= OTHER_PHONE ?></label>
													</div>
												</div>
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
															<input type="checkbox" class="custom-control-input" id="OTHER_PHONE_INVALID" name="OTHER_PHONE_INVALID" value="1" <? if ($OTHER_PHONE_INVALID == 1) echo "checked"; ?>>
															<label class="custom-control-label" for="OTHER_PHONE_INVALID"><?= INVALID ?></label>
														</div>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input id="EMAIL" name="EMAIL" type="text" class="form-control validate-email" value="<?= $EMAIL ?>" onchange="show_use_mail()">
														<span class="bar"></span>
														<label for="EMAIL"><?= EMAIL ?></label>
													</div>
												</div>
												<? $style = "";
												/*if($EMAIL == '') 
													$style = "display:none";*/ ?>
												<div class="col-md-3" id="USE_MAIL_DIV" style="<?= $style ?>">
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
															<input type="checkbox" class="custom-control-input" id="USE_EMAIL" name="USE_EMAIL" value="1" <? if ($USE_EMAIL == 1) echo "checked"; ?>>
															<label class="custom-control-label" for="USE_EMAIL"><?= USE_EMAIL ?></label>
														</div>
													</div>
												</div>
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
															<input type="checkbox" class="custom-control-input" id="EMAIL_INVALID" name="EMAIL_INVALID" value="1" <? if ($EMAIL_INVALID == 1) echo "checked"; ?>>
															<label class="custom-control-label" for="EMAIL_INVALID"><?= INVALID ?></label>
														</div>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input id="EMAIL_OTHER" name="EMAIL_OTHER" type="text" class="form-control validate-email" value="<?= $EMAIL_OTHER ?>">
														<span class="bar"></span>
														<label for="EMAIL_OTHER"><?= OTHER_EMAIL ?></label>
													</div>
												</div>
												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
															<input type="checkbox" class="custom-control-input" id="USE_SECONDARY_EMAIL_AS_DEFAULT_STD" name="USE_SECONDARY_EMAIL_AS_DEFAULT_STD" value="1" <? if ($USE_SECONDARY_EMAIL_AS_DEFAULT_STD == 1) echo "checked"; ?>>
															<label class="custom-control-label" for="USE_SECONDARY_EMAIL_AS_DEFAULT_STD"><?= USE_SECONDARY_EMAIL_AS_DEFAULT_STD ?></label>
														</div>
													</div>
												</div>

												<div class="col-md-3">
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
															<input type="checkbox" class="custom-control-input" id="EMAIL_OTHER_INVALID" name="EMAIL_OTHER_INVALID" value="1" <? if ($EMAIL_OTHER_INVALID == 1) echo "checked"; ?>>
															<label class="custom-control-label" for="EMAIL_OTHER_INVALID"><?= INVALID ?></label>
														</div>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-md-6" id="WEBSITE_DIV" style="<?= $style_website ?>">
													<div class="form-group m-b-40">
														<input id="WEBSITE" name="WEBSITE" type="text" class="form-control" value="<?= $WEBSITE ?>" onfocus="set_web_default(1)" onblur="set_web_default(0)">
														<span class="bar"></span>
														<label for="WEBSITE"><?= WEBSITE ?></label>
													</div>
												</div>
											</div>

											<? if ($_GET['id'] != '') { ?>
												<div class="row">
													<div class="col-md-12">
														<div class="form-group m-b-40">
															<div class="row form-group">
																<div class="custom-control col-md-2"><?= ACTIVE ?></div>
																<div class="custom-control custom-radio col-md-1">
																	<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if ($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
																	<label class="custom-control-label" for="customRadio11">Yes</label>
																</div>
																<div class="custom-control custom-radio col-md-1">
																	<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if ($ACTIVE == 0) echo "checked"; ?> class="custom-control-input">
																	<label class="custom-control-label" for="customRadio22">No</label>
																</div>
															</div>
														</div>
													</div>
												</div>
											<? } ?>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="form-group m-b-5" style="text-align:right">
												<button type="submit" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>

												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='student?id=<?= $_GET['sid'] ?>&tab=contactTab&eid=<?= $_GET['eid'] ?>&t=<?= $_GET['t'] ?>'"><?= CANCEL ?></button>

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
		jQuery(document).ready(function($) {
			<? if ($_GET['id'] != '') { ?>
				//get_country('<?= $PK_STATES ?>','PK_COUNTRY')
			<? } ?>
		});

		var form1 = new Validation('form1');

		function get_country(val, id) {
			jQuery(document).ready(function($) {
				var data = 'state=' + val + '&id=' + id;
				var value = $.ajax({
					url: "../super_admin/ajax_get_country_from_state",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						//alert(data)
						document.getElementById(id).innerHTML = data;
						document.getElementById('PK_COUNTRY_LABEL').classList.add("focused");

					}
				}).responseText;
			});
		}

		function show_emergency(val) {
			if (val == 1 || val == 3 || val == 6) {
				document.getElementById('PK_STUDENT_RELATIONSHIP_MASTER_DIV').style.display = 'none';
				document.getElementById('PK_STUDENT_RELATIONSHIP_MASTER').className = 'form-control';
			} else {
				document.getElementById('PK_STUDENT_RELATIONSHIP_MASTER_DIV').style.display = 'flex';
				document.getElementById('PK_STUDENT_RELATIONSHIP_MASTER').className = 'form-control required-entry';
				document.getElementById('PK_STUDENT_RELATIONSHIP_MASTER_DIV1').style.width = '100%';
			}

			if (val == 3) {
				document.getElementById('COMPANY_NAME_DIV').style.display = 'flex';
				document.getElementById('COMPANY_NAME').className = 'form-control required-entry';

				document.getElementById('CONTACT_TITLE_DIV').style.display = 'flex';
				//document.getElementById('CONTACT_TITLE').className   	   = 'form-control required-entry';

				document.getElementById('HOME_PHONE_LABEL').innerHTML = '<?= MAIN ?>'

				document.getElementById('WEBSITE_DIV').style.display = 'flex';

			} else {
				document.getElementById('COMPANY_NAME_DIV').style.display = 'none';
				document.getElementById('COMPANY_NAME').className = 'form-control';

				document.getElementById('CONTACT_TITLE_DIV').style.display = 'none';
				//document.getElementById('CONTACT_TITLE').className   	   = 'form-control';

				document.getElementById('HOME_PHONE_LABEL').innerHTML = '<?= HOME_PHONE ?>'

				document.getElementById('WEBSITE_DIV').style.display = 'none';
			}

			if (val == 1 || val == 6) {
				document.getElementById('CONTACT_NAME_DIV').style.display = 'none';
				document.getElementById('CONTACT_NAME').className = 'form-control';
			} else {
				document.getElementById('CONTACT_NAME_DIV').style.display = 'flex';
				document.getElementById('CONTACT_NAME').className = 'form-control required-entry';
			}

			if (val != 1 && val != 4) {
				//document.getElementById('USE_MAIL_DIV').style.display = 'none';
				document.getElementById('USE_EMAIL').checked = false;
			} else {
				//document.getElementById('USE_MAIL_DIV').style.display = 'block';
			}

			show_use_mail()
		}

		function show_use_mail() {
			if (document.getElementById('EMAIL').value == '') {
				//document.getElementById('USE_MAIL_DIV').style.display = 'none';
				document.getElementById('USE_EMAIL').checked = false;
			} //else
			//document.getElementById('USE_MAIL_DIV').style.display = 'block';
		}

		function set_web_default(val) {
			if (val == 1) {
				if (document.getElementById('WEBSITE').value == '')
					document.getElementById('WEBSITE').value = 'http://';
			} else {
				if (document.getElementById('WEBSITE').value == 'http://')
					document.getElementById('WEBSITE').value = '';
			}
		}
		function updatePlaceholder(selectId) { 
			var select = document.getElementById(selectId);
			var selectedOption = select.options[select.selectedIndex];
			var countryCode = selectedOption.value;
			var countryName = selectedOption.textContent.match(/\((.*?)\)/)[1]; // Extract country code from within parentheses
			document.getElementById(selectId+'_DISP').value= ' '+countryName; 

		}
		function setCountryCodes(id,value){
			jQuery(document).ready(function($) {
				try {
					$('#'+id).val(value).change();
				} catch (error) {
					//do nothing
				}
				
			});
		}
		jQuery(document).ready(function($) {

			<? 
			if($HOME_PHONE_CODE != ''){ ?> 
				setCountryCodes( 'HOME_PHONE_CODE', <?php echo $HOME_PHONE_CODE; ?>);
			<? } 
			if($WORK_PHONE_CODE != ''){ ?> 
				setCountryCodes( 'WORK_PHONE_CODE', <?php echo $WORK_PHONE_CODE; ?>);
			<? } 
			if($CELL_PHONE_CODE != ''){ ?> 
				setCountryCodes( 'CELL_PHONE_CODE', <?php echo $CELL_PHONE_CODE; ?>);
			<? } 
			if($OTHER_PHONE_CODE != ''){ ?> 
				setCountryCodes( 'OTHER_PHONE_CODE', <?php echo $OTHER_PHONE_CODE; ?>);
			<? } ?>

			updatePlaceholder('HOME_PHONE_CODE');
			updatePlaceholder('WORK_PHONE_CODE');
			updatePlaceholder('CELL_PHONE_CODE');
			updatePlaceholder('OTHER_PHONE_CODE');
		});
	</script>

	<script src="../backend_assets/node_modules/Magnific-Popup-master/dist/jquery.magnific-popup.js"></script>
	<script src="../backend_assets/node_modules/Magnific-Popup-master/dist/jquery.magnific-popup-init.js"></script>
</body>

</html>
