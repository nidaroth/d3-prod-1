<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/student_contact.php");
require_once("check_access.php");

$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

if($_GET['t'] == 1 && $ADMISSION_ACCESS == 0) {
	header("location:../index");
	exit;
} else if($_GET['t'] == 2 && $REGISTRAR_ACCESS == 0) {
	header("location:../index");
	exit;
} else if($_GET['t'] == 3 && $FINANCE_ACCESS == 0) {
	header("location:../index");
	exit;
} else if($_GET['t'] == 5 && $ACCOUNTING_ACCESS == 0) {
	header("location:../index");
	exit;
} else if($_GET['t'] == 6 && $PLACEMENT_ACCESS == 0) {
	header("location:../index");
	exit;
}

/* Ticket # 1623 */
$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}

$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
/* Ticket # 1623 */

if($_GET['act'] == 'del') {
	$db->Execute("DELETE from S_STUDENT_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER in ($_GET[iid]) ");
	$db->Execute("DELETE from S_STUDENT_ACADEMICS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER in ($_GET[iid]) ");
	$db->Execute("DELETE from S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER in ($_GET[iid]) ");
	$db->Execute("DELETE from S_STUDENT_RACE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER in ($_GET[iid]) ");
	$db->Execute("DELETE from S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER in ($_GET[iid]) ");
	$db->Execute("DELETE from S_STUDENT_CONTACT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER in ($_GET[iid]) ");
	$db->Execute("DELETE from S_STUDENT_OTHER_EDU WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER in ($_GET[iid]) ");
	$db->Execute("DELETE from S_STUDENT_REQUIREMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER in ($_GET[iid]) ");
	$db->Execute("DELETE from S_STUDENT_CUSTOM_FIELDS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER in ($_GET[iid]) ");
	header("location:student_data_view?t=".$_GET['t']."&id=".$_GET['id']);
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
	<title>
		<?	if($_GET['arch'] == 1) echo ARCHIVED.' '.DATA_VIEW; 
		else if($_GET['t'] == 1) echo LEAD.' '.DATA_VIEW; 
		else if($_GET['t'] == 100) echo 'Lead Import Results'; 
		else echo STUDENT_PAGE_TITLE1.' '.DATA_VIEW; ?> | <?=$title?>
	</title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<link href="../backend_assets/dist/css/pages/other-pages.css" rel="stylesheet">
	<style>
	.custom_table{};
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
				<div class="row page-titles" style="padding-bottom: 10px;" >
                    <div class="col-md-8 align-self-center">
                        <h4 class="text-themecolor">
							<?	if($_GET['arch'] == 1) echo ARCHIVED.' '.DATA_VIEW; 
							else if($_GET['t'] == 1) echo LEAD.' '.DATA_VIEW; 
							else if($_GET['t'] == 100) echo 'Lead Import Results'; 
							else echo STUDENT_PAGE_TITLE1.' '.DATA_VIEW; ?>
						</h4>
                    </div>
					<div class="col-md-4" style="text-align:right" >
						<? if($_GET['t'] == 100){ ?>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="delete_row()" ><?=DELETE?></button>
						<? } ?>
						
						<button onclick="javascript:window.location.href = 'student_data_view_excel?t=<?=$_GET['t']?>&arch=<?=$_GET['arch']?>'" type="button" class="btn waves-effect waves-light btn-info"><?=Excel?></button>
					</div>
				</div>
				
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12" style="max-height:600px;overflow-x: auto;overflow-y: auto;">
										<table data-toggle="table" data-height="500" data-mobile-responsive="true" class="table-striped" id="table_1" >
											<thead>
												<tr>
													<th>#</th>
													<th><?=FIRST_NAME?></th>
													<th><?=MIDDLE_NAME?></th>
													<th><?=LAST_NAME?></th>
													<th><?=OTHER_NAME?></th>
													<th><?=PATERNAL_LAST_NAME?></th>
													<th><?=MATERNAL_LAST_NAME?></th>
													<th><?=SSN?></th>
													<th><?=SSN_VERIFIED?></th>
													<th><?=COMMENTS?></th>
													<th><?=ADDRESS?></th>
													<th><?=ADDRESS_1?></th>
													<th><?=CITY?></th>
													<th><?=STATE?></th>
													<th><?=ZIP?></th>
													<th><?=COUNTRY?></th>
													<th><?=CELL_PHONE?></th>
													<th><?=CELL_PHONE.' '.OPTOUT?></th>
													<th><?=CELL_PHONE.' '.INVALID?></th>
													<th><?=HOME_PHONE?></th>
													<th><?=HOME_PHONE.' '.INVALID?></th>
													<th><?=WORK_PHONE?></th>
													<th><?=WORK_PHONE.' '.INVALID?></th>
													<th><?=OTHER_PHONE?></th>
													<th><?=OTHER_PHONE.' '.OPTOUT?></th>
													<th><?=EMAIL?></th>
													<th><?=USE_EMAIL?></th>
													<th><?=EMAIL.' '.INVALID?></th>
													<th><?=OTHER_EMAIL?></th>
													<th><?=OTHER_EMAIL.' '.INVALID?></th>
													<th><?=DATE_OF_BIRTH?></th>
													<th><?=GENDER?></th>
													<th><?=DRIVERS_LICENSE?></th>
													<th><?=DRIVERS_LICENSE_STATE?></th>
													<th><?=MARITAL_STATUS?></th>
													<th><?=COUNTRY_CITIZEN?></th>
													<th><?=US_CITIZEN?></th>
													<th><?=PLACE_OF_BIRTH?></th>
													<th><?=STATE_OF_RESIDENCY?></th>
													<th><?=STUDENT_ID?></th>
													<th><?=ADM_USER_ID?></th>
													<th><?=HIGHEST_LEVEL_OF_ED?></th>
													<th><?=PREVIOUS_COLLEGE?></th>
													<th><?=BADGE_ID?></th>
													<th><?=FERPA_BLOCK?></th>
													<th><?=IPEDS_ETHNICITY?></th>
													<th><?=RACE?></th>
													<th><?=FIRST_TERM_DATE?></th>
													<th><?=PROGRAM?></th>
													<th><?=STUDENT_STATUS?></th>
													<th><?=STATUS_DATE?></th>
													<th><?=ADMISSION_REP?></th>
													<th><?=LEAD_SOURCE?></th>
													<th><?=CONTACT_SOURCE?></th>
													
													<th><?=CONTRACT_SIGNED_DATE?></th>
													<th><?=CONTRACT_END_DATE?></th>
													<th><?=ENTRY_DATE?></th>
													<th><?=ENTRY_TIME?></th>
													<? if($_GET['t'] != 1 && $_GET['t'] != 100){ ?>
													<th><?=ORIGINAL_EXPECTED_GRAD_DATE?></th>
													<? } ?>
													<th><?=EXPECTED_GRAD_DATE?></th>
													<th><?=ORIGINAL_ENROLLMENT_STATUS?></th> <!-- DIAM-2366 -->
													<th><?=FULL_PART_TIME?></th>
													
													<? if($_GET['t'] != 1 && $_GET['t'] != 100){ ?>
													<th><?=FT_PT_EFFECTIVE_DATE?></th>
													<th><?=MIDPOINT_DATE?></th>
													<? } ?>
													<th><?=SESSION?></th>
													<? if($_GET['t'] != 1 && $_GET['t'] != 100){ ?>
													<th><?=FIRST_TERM?></th>
													<? } ?>
													<th><?=RESIDENCY_TUITION_STATUS?></th> <!-- DIAM-2370 -->	
													<? if($_GET['t'] != 1 && $_GET['t'] != 100){ ?>
													<th><?=REENTRY?></th>
													<th><?=TRANSFER_IN?></th>
													<th><?=TRANSFER_OUT?></th>
													<th><?=DISTANCE_LEARNING?></th>
													<? } ?>
													<th><?=FUNDING?></th>
													<? if($_GET['t'] != 1 && $_GET['t'] != 100){ ?>
													<th><?=PLACEMENT_STATUS?></th>
													<th><?=STRF_PAID_DATE?></th>
													<? } ?>
													<th><?=STUDENT_GROUP?></th>
													<? if($_GET['t'] != 1 && $_GET['t'] != 100){ ?>
													<th><?=GRADE_DATE?></th>
													<th><?=LDA?></th>
													<th><?=DETERMINATION_DATE?></th>
													<th><?=DROP_DATE?></th>
													<th><?=DROP_REASON?></th>
													<? } ?>
													
													<th><?=CAMPUS?></th>
													
													<? $res_type = $db->Execute("select FIELD_NAME, IF(TAB = 'info',1,2) as TAB_ORDER from S_CUSTOM_FIELDS WHERE SECTION = 1 AND S_CUSTOM_FIELDS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 ORDER BY TAB_ORDER ASC, PK_CUSTOM_FIELDS ASC");
													while (!$res_type->EOF) { ?>
														<th><?=$res_type->fields['FIELD_NAME']?></th>
													<?	$res_type->MoveNext();
													} 
													if($_GET['t'] == 100){ ?>
													<th><?=ERROR?></th>
													<th><?=STATUS?></th>
													<th>
														<input type="checkbox" id="CHECK_ALL" onclick="fun_check_all()" >
													</th>
													<? } ?>
													
												</tr>
											</thead>
											<tbody>
												<? $cond = " S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ACTIVE = 1 AND IS_ACTIVE_ENROLLMENT = 1 ";	
												if($_GET['t'] == 1 || $_GET['t'] == 2 || $_GET['t'] == 3 || $_GET['t'] == 5 || $_GET['t'] == 6){
													//Admissions should show admissions that have Yes in the admissions columns
													/*$sts = "";
													$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 1 ");
													while (!$res_type->EOF) {
														if($sts != '')
															$sts .= ',';
															
														$sts .= $res_type->fields['PK_STUDENT_STATUS'];
														
														$res_type->MoveNext();
													}
													
													$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($sts) ";*/
													
													$cond = $_SESSION['WHERE'];
													
													
												} else if($_GET['t'] == 100){
													$cond .= " AND PK_MAP_MASTER = '$_GET[id]' AND PK_MAP_MASTER > 0 ";
												}

												if($_GET['arch'] == 1)
													$cond .= " AND S_STUDENT_MASTER.ARCHIVED = 1 ";
												else
													$cond .= " AND S_STUDENT_MASTER.ARCHIVED = 0 "; 
												

												//DIAM - 395
												if ($_GET['id'] != '' && $_GET['stud_map'] != 1) // DIAM-696, Add cond not equal to
												{
													$ids = explode(",",$_GET['id']);
													
													$s_id = array();
													foreach($ids as $id)
													{
														$id1 = explode("-",$id);
														$s_id[] = $id1[1];					
														//print_r($output);echo "<br>";
													}
													$string_version = implode(',', $s_id);
													$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($string_version) ";
												}
												//echo $cond;
												//End DIAM - 395

												//Ticket #1054
												/*$GROUP_BY = " S_STUDENT_MASTER.PK_STUDENT_MASTER ";
												if($_GET['me'] == "true")
													$GROUP_BY = " S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";*/
													
												$GROUP_BY = " GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ";												
												if($_SESSION['GROUP_BY'] != '')
													$GROUP_BY = $_SESSION['GROUP_BY'];
													
												/* Ticket # 1762 - contact source change */
												/* Ticket # 1769 - gender change */
												$query = "SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_MASTER.LAST_NAME, S_STUDENT_MASTER.FIRST_NAME, S_STUDENT_MASTER.MIDDLE_NAME, S_STUDENT_MASTER.OTHER_NAME AS STU_OTHER_NAME, S_STUDENT_MASTER.PATERNAL_LAST_NAME AS PATERNAL_LAST_NAME, S_STUDENT_MASTER.MATERNAL_LAST_NAME AS MATERNAL_LAST_NAME, S_STUDENT_MASTER.COMMENTS AS COMMENTS, S_STUDENT_MASTER.SSN AS SSN, IF(S_STUDENT_MASTER.SSN_VERIFIED = 1,'Yes','No') AS SSN_VERIFIED, IF(S_STUDENT_MASTER.DATE_OF_BIRTH != '0000-00-00',DATE_FORMAT( S_STUDENT_MASTER.DATE_OF_BIRTH, '%Y-%m-%d'),'') AS DATE_OF_BIRTH, Z_GENDER.GENDER AS GENDER, S_STUDENT_MASTER.DRIVERS_LICENSE, Z_STATES_DRIVERS_LICENSE.STATE_NAME AS STATES_DRIVERS_LICENSE_STATE, Z_MARITAL_STATUS_STUD.MARITAL_STATUS AS STU_MARITAL_STATUS, Z_COUNTRY_CITIZEN.NAME AS COUNTRY_CITIZEN, Z_CITIZENSHIP.CITIZENSHIP, PLACE_OF_BIRTH, Z_STATES_OF_RESIDENCY.STATE_NAME AS STATE_OF_RESIDENCY, S_STUDENT_ACADEMICS.STUDENT_ID, S_STUDENT_ACADEMICS.ADM_USER_ID, M_HIGHEST_LEVEL_OF_EDU.HIGHEST_LEVEL_OF_EDU AS HIGHEST_LEVEL_OF_EDU, IF(PREVIOUS_COLLEGE = 1,'Yes','No') AS PREVIOUS_COLLEGE, S_STUDENT_MASTER.BADGE_ID, IF(FERPA_BLOCK = 1,'Yes',IF(FERPA_BLOCK = 2,'No','')) AS FERPA_BLOCK, S_STUDENT_MASTER.IPEDS_ETHNICITY AS IPEDS_ETHNICITY, '' AS RACE,IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d' )) AS TERM_DATE,M_CAMPUS_PROGRAM.CODE, M_STUDENT_STATUS.STUDENT_STATUS AS STUDENT_STATUS, IF(S_STUDENT_ENROLLMENT.STATUS_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_ENROLLMENT.STATUS_DATE,'%Y-%m-%d' )) AS STATUS_DATE, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS EMP_NAME, M_LEAD_CONTACT_SOURCE.LEAD_CONTACT_SOURCE, M_LEAD_SOURCE.LEAD_SOURCE, IF(CONTRACT_SIGNED_DATE = '0000-00-00','',DATE_FORMAT(CONTRACT_SIGNED_DATE,'%Y-%m-%d' )) AS CONTRACT_SIGNED_DATE, IF(CONTRACT_END_DATE = '0000-00-00','',DATE_FORMAT(CONTRACT_END_DATE,'%Y-%m-%d' )) AS CONTRACT_END_DATE, IF(ENTRY_DATE = '0000-00-00','',DATE_FORMAT(ENTRY_DATE,'%Y-%m-%d' )) AS ENTRY_DATE, IF(ENTRY_TIME = '00-00-00','',DATE_FORMAT(ENTRY_TIME,'%h:%i %p' )) AS ENTRY_TIME, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE,'%Y-%m-%d' )) AS EXPECTED_GRAD_DATE, IF(ORIGINAL_EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(ORIGINAL_EXPECTED_GRAD_DATE,'%Y-%m-%d' )) AS ORIGINAL_EXPECTED_GRAD_DATE, CONCAT(M_ENROLLMENT_STATUS.CODE,' - ',M_ENROLLMENT_STATUS.DESCRIPTION) AS FULL_PART_TIME, CONCAT(MES.CODE,' - ',MES.DESCRIPTION) AS ORIGINAL_ENROLLMENT_STATUS, CONCAT(M_RESIDENCY_TUITION_STATUS.CODE,' - ',M_RESIDENCY_TUITION_STATUS.DESCRIPTION) AS RESIDENCY_TUITION_STATUS, M_RESIDENCY_TUITION_STATUS.CODE AS M_CODE, M_RESIDENCY_TUITION_STATUS.DESCRIPTION AS M_DESCRIPTION, SESSION, CONCAT(M_FUNDING.FUNDING,' - ',M_FUNDING.DESCRIPTION) AS FUNDING, STUDENT_GROUP, '' AS CAMPUS, IF(FT_PT_EFFECTIVE_DATE != '0000-00-00',DATE_FORMAT(FT_PT_EFFECTIVE_DATE, '%Y-%m-%d'),'') AS FT_PT_EFFECTIVE_DATE, IF(MIDPOINT_DATE != '0000-00-00',DATE_FORMAT(MIDPOINT_DATE, '%Y-%m-%d'),'') AS MIDPOINT_DATE, M_FIRST_TERM.FIRST_TERM AS FIRST_TERM, IF(REENTRY = 1,'Yes','No') AS REENTRY, IF(TRANSFER_IN = 1,'Yes','No') AS TRANSFER_IN, IF(TRANSFER_OUT = 1,'Yes','No') AS TRANSFER_OUT, DISTANCE_LEARNING, PLACEMENT_STATUS, IF(STRF_PAID_DATE != '0000-00-00',DATE_FORMAT(STRF_PAID_DATE, '%Y-%m-%d'),'') AS STRF_PAID_DATE, IF(GRADE_DATE != '0000-00-00',DATE_FORMAT(GRADE_DATE, '%Y-%m-%d'),'') AS GRADE_DATE, IF(LDA != '0000-00-00',DATE_FORMAT(LDA, '%Y-%m-%d'),'') AS LDA, IF(DETERMINATION_DATE != '0000-00-00',DATE_FORMAT(DETERMINATION_DATE, '%Y-%m-%d'),'') AS DETERMINATION_DATE, IF(DROP_DATE != '0000-00-00',DATE_FORMAT(DROP_DATE, '%Y-%m-%d'),'') AS DROP_DATE, DROP_REASON, IF(IMPORT_STATUS = 1,'Success',IF(IMPORT_STATUS = 2,'Error',IF(IMPORT_STATUS = 3,'Duplicate',''))) AS IMPORT_STATUS , IMPORT_ERROR
												FROM 
												S_STUDENT_MASTER 
												LEFT JOIN Z_GENDER On Z_GENDER.PK_GENDER = S_STUDENT_MASTER.GENDER  
												LEFT JOIN S_STUDENT_CONTACT On S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
												LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
												LEFT JOIN M_HIGHEST_LEVEL_OF_EDU ON M_HIGHEST_LEVEL_OF_EDU.PK_HIGHEST_LEVEL_OF_EDU = S_STUDENT_ACADEMICS.PK_HIGHEST_LEVEL_OF_EDU 
												LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
												LEFT JOIN M_LEAD_CONTACT_SOURCE ON M_LEAD_CONTACT_SOURCE.PK_LEAD_CONTACT_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_CONTACT_SOURCE 
												LEFT JOIN M_FIRST_TERM ON M_FIRST_TERM.PK_FIRST_TERM = S_STUDENT_ENROLLMENT.FIRST_TERM 
												LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
												LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER
												LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
												LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM
												LEFT JOIN M_DROP_REASON ON M_DROP_REASON.PK_DROP_REASON = S_STUDENT_ENROLLMENT.PK_DROP_REASON
												LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE 
												LEFT JOIN Z_STATES AS Z_STATES_DRIVERS_LICENSE ON Z_STATES_DRIVERS_LICENSE.PK_STATES = S_STUDENT_MASTER.PK_DRIVERS_LICENSE_STATE 
												LEFT JOIN Z_MARITAL_STATUS AS Z_MARITAL_STATUS_STUD ON Z_MARITAL_STATUS_STUD.PK_MARITAL_STATUS = S_STUDENT_MASTER.PK_MARITAL_STATUS 
												LEFT JOIN Z_COUNTRY AS Z_COUNTRY_CITIZEN ON Z_COUNTRY_CITIZEN.PK_COUNTRY = S_STUDENT_MASTER.PK_COUNTRY_CITIZEN 
												LEFT JOIN Z_CITIZENSHIP ON Z_CITIZENSHIP.PK_CITIZENSHIP = S_STUDENT_MASTER.PK_CITIZENSHIP 
												LEFT JOIN Z_STATES AS Z_STATES_OF_RESIDENCY ON Z_STATES_OF_RESIDENCY.PK_STATES = S_STUDENT_MASTER.PK_STATE_OF_RESIDENCY 
												
												LEFT JOIN M_LEAD_SOURCE ON S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE = M_LEAD_SOURCE.PK_LEAD_SOURCE
												LEFT JOIN M_ENROLLMENT_STATUS ON S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS = M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS
												LEFT JOIN M_ENROLLMENT_STATUS AS MES ON S_STUDENT_ENROLLMENT.ORIGINAL_ENROLLMENT_STATUS = MES.PK_ENROLLMENT_STATUS -- DIAM-2366
												LEFT JOIN M_RESIDENCY_TUITION_STATUS ON S_STUDENT_ENROLLMENT.PK_RESIDENCY_TUITION_STATUS = M_RESIDENCY_TUITION_STATUS.PK_RESIDENCY_TUITION_STATUS -- DIAM-2370
												LEFT JOIN M_SESSION ON S_STUDENT_ENROLLMENT.PK_SESSION = M_SESSION.PK_SESSION
												LEFT JOIN M_FUNDING ON S_STUDENT_ENROLLMENT.PK_FUNDING = M_FUNDING.PK_FUNDING
												LEFT JOIN M_STUDENT_GROUP ON S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP = M_STUDENT_GROUP.PK_STUDENT_GROUP
												LEFT JOIN M_DISTANCE_LEARNING ON S_STUDENT_ENROLLMENT.PK_DISTANCE_LEARNING = M_DISTANCE_LEARNING.PK_DISTANCE_LEARNING
												LEFT JOIN M_PLACEMENT_STATUS ON S_STUDENT_ENROLLMENT.PK_PLACEMENT_STATUS = M_PLACEMENT_STATUS.PK_PLACEMENT_STATUS
 
												WHERE  $cond $GROUP_BY  ORDER BY S_STUDENT_MASTER.LAST_NAME ASC, S_STUDENT_MASTER.FIRST_NAME ASC ";
												/* Ticket # 1762 */
												/* Ticket # 1769 - gender change */
												
												$_SESSION['REPORT_QUERY'] = $query;
												$res_type = $db->Execute($query);
												//echo $query;exit;
												
												$no = $res_type->RecordCount();
												$nr = $no;
												
												$pn 			= $_GET['pn'];
												$itemsPerPage 	= $_GET['ipp'];
												
												if($pn == '')
													$pn = '1';
													
												if($itemsPerPage == '')
													$itemsPerPage = 25;
													
												if($nr > 0){
													$lastPage = ceil($nr / $itemsPerPage);
													if ($pn < 1) { // If it is less than 1
														$pn = 1; // force if to be 1
													} else if ($pn > $lastPage) { // if it is greater than $lastpage
														$pn = $lastPage; // force it to be $lastpage's value
													}

													$sub1 = $pn - 1;
													$add1 = $pn + 1;
													
													$disabled1 = "";
													$disabled2 = "";
													
													$onclick1  = 'onclick="ajax_notification('.$sub1.')"';
													$onclick2  = 'onclick="ajax_notification('.$add1.')"';
													
													if($pn == 1) {
														$disabled1 = "l-btn-disabled";
														$onclick1  = "";
													}
													
													if($pn == $lastPage) {
														$disabled2 = "l-btn-disabled";
														$onclick2  = "";
													}
													
													$paginationDisplay = ""; // Initialize the pagination output variable
													
													$paginationDisplay .= '<li><a href="javascript:void(0)" '.$onclick1.' class="l-btn l-btn-plain '.$disabled1.' " style="height: 13px !important; border: none !important;" ><span class="l-btn-left"><span class="l-btn-text"><span class="l-btn-empty ti-angle-left">&nbsp;</span></span></span></a>';
													
													$paginationDisplay .= '<li><a href="javascript:void(0)" '.$onclick2.' class="l-btn l-btn-plain '.$disabled2.'" style="height: 13px !important; border: none !important;" ><span class="l-btn-left"><span class="l-btn-text"><span class="l-btn-empty ti-angle-right">&nbsp;</span></span></span></a>';
													
													$limit = 'LIMIT ' .($pn - 1) * $itemsPerPage .',' .$itemsPerPage;
												}
												$res_type = $db->Execute($query." ".$limit);
												
												$pn2 = $pn - 1;
												$i  = ($itemsPerPage * $pn2);
												$kl = $i + 1;
												while (!$res_type->EOF) { 
													$i++; 
													$PK_STUDENT_MASTER 		= $res_type->fields['PK_STUDENT_MASTER'];
													$PK_STUDENT_ENROLLMENT	= $res_type->fields['PK_STUDENT_ENROLLMENT']; ?>
													<tr>
														<td class="text-nowrap" ><?=$i?></td>
														<td class="text-nowrap" ><?=$res_type->fields['FIRST_NAME']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['MIDDLE_NAME']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['LAST_NAME']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['STU_OTHER_NAME']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['PATERNAL_LAST_NAME']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['MATERNAL_LAST_NAME']?></td>
														<td class="text-nowrap" >
															<? if($res_type->fields['SSN'] != ''){ 
																$SSN_1 	 = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$res_type->fields['SSN']);
																$SSN_ARR = explode("-",$SSN_1);
																echo 'xxx-xx-'.$SSN_ARR[2];
															} ?>
														</td>
														<td class="text-nowrap" ><?=$res_type->fields['SSN_VERIFIED']?></td>
														<!-- DIAM-1184 -->
														<td class="" style="word-break: break-all !important;" ><?=$res_type->fields['COMMENTS']?></td>
														<!-- End DIAM-1184 -->
														<? $res_cont = $db->Execute("SELECT ADDRESS,ADDRESS_1,CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, IF(HOME_PHONE_INVALID = 1,'Yes','No') AS HOME_PHONE_INVALID, WORK_PHONE, IF(WORK_PHONE_INVALID = 1,'Yes','No') AS WORK_PHONE_INVALID, CELL_PHONE, IF(OPT_OUT = 1,'Yes','No') AS OPT_OUT , IF(CELL_PHONE_INVALID = 1,'Yes','No') AS CELL_PHONE_INVALID, OTHER_PHONE, IF(OTHER_PHONE_INVALID = 1,'Yes','No') AS OTHER_PHONE_INVALID, EMAIL, IF(USE_EMAIL = 1,'Yes','No') AS USE_EMAIL , IF(EMAIL_INVALID = 1,'Yes','No') AS EMAIL_INVALID, EMAIL_OTHER, IF(EMAIL_OTHER_INVALID = 1,'Yes','No') AS EMAIL_OTHER_INVALID FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); ?>
														<td class="text-nowrap" ><?=$res_cont->fields['ADDRESS']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['ADDRESS_1']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['CITY']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['STATE_CODE']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['ZIP']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['COUNTRY']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['CELL_PHONE']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['OPT_OUT']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['CELL_PHONE_INVALID']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['HOME_PHONE']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['HOME_PHONE_INVALID']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['WORK_PHONE']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['WORK_PHONE_INVALID']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['OTHER_PHONE']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['OTHER_PHONE_INVALID']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['EMAIL']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['USE_EMAIL']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['EMAIL_INVALID']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['EMAIL_OTHER']?></td>
														<td class="text-nowrap" ><?=$res_cont->fields['EMAIL_OTHER_INVALID']?></td>
														
														<td class="text-nowrap" ><?=$res_type->fields['DATE_OF_BIRTH']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['GENDER']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['DRIVERS_LICENSE']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['STATES_DRIVERS_LICENSE_STATE']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['STU_MARITAL_STATUS']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['COUNTRY_CITIZEN']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['CITIZENSHIP']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['PLACE_OF_BIRTH']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['STATE_OF_RESIDENCY']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['STUDENT_ID']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['ADM_USER_ID']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['HIGHEST_LEVEL_OF_EDU']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['PREVIOUS_COLLEGE']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['BADGE_ID']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['FERPA_BLOCK']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['IPEDS_ETHNICITY']?></td>
														<td class="text-nowrap" >
															<? $race = '';
															$res_race = $db->Execute("select RACE FROM S_STUDENT_RACE, Z_RACE WHERE S_STUDENT_RACE.PK_RACE = Z_RACE.PK_RACE AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
															while (!$res_race->EOF){
																if($race != '')
																	$race .= ', ';
																$race .= $res_race->fields['RACE'];
																
																$res_race->MoveNext();
															} 
															echo $race; ?>
														</td>
														<td class="text-nowrap" ><?=$res_type->fields['TERM_DATE']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['CODE']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['STUDENT_STATUS']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['STATUS_DATE']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['EMP_NAME']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['LEAD_SOURCE']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['LEAD_CONTACT_SOURCE']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['CONTRACT_SIGNED_DATE']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['CONTRACT_END_DATE']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['ENTRY_DATE']?></td>
														<td class="text-nowrap" >
															<? /* Ticket # 1623 */
															$ENTRY_TIME = $res_type->fields['ENTRY_TIME'];
															if($ENTRY_TIME != '' && $ENTRY_TIME != '00:00:00') {
																$ENTRY_TIME = date("H:i", strtotime($ENTRY_TIME));
																$ENTRY_TIME = convert_to_user_date(date("Y-m-d ").$ENTRY_TIME,'h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
																echo $ENTRY_TIME;
															} 
															/* Ticket # 1623 */ ?>
														</td>
														<? if($_GET['t'] != 1 && $_GET['t'] != 100){ ?>
														<td class="text-nowrap" ><?=$res_type->fields['ORIGINAL_EXPECTED_GRAD_DATE']?></td>
														<? } ?>
														<td class="text-nowrap" ><?=$res_type->fields['EXPECTED_GRAD_DATE']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['ORIGINAL_ENROLLMENT_STATUS']?></td> <!-- DIAM-2366 -->
														<td class="text-nowrap" ><?=$res_type->fields['FULL_PART_TIME']?></td>
														
														<? if($_GET['t'] != 1 && $_GET['t'] != 100){ ?>
														<td class="text-nowrap" ><?=$res_type->fields['FT_PT_EFFECTIVE_DATE']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['MIDPOINT_DATE']?></td>
														<? } ?>
														<td class="text-nowrap" ><?=$res_type->fields['SESSION']?></td>
														<? if($_GET['t'] != 1 && $_GET['t'] != 100){ ?>
														<td class="text-nowrap" ><?=$res_type->fields['FIRST_TERM']?></td>
														<? } ?>	
														<td class="text-nowrap" >
															<?
															$M_CODE = $res_type->fields['M_CODE'];
															$M_DESCRIPTION = $res_type->fields['M_DESCRIPTION'];
															if($res_type->fields['RESIDENCY_TUITION_STATUS'] != "")
															{
																$RESIDENCY_TUITION_STATUS = $M_DESCRIPTION. " (". $M_CODE. ")";
															}
															else{
																$RESIDENCY_TUITION_STATUS = 'Not Set';
															}
															echo $RESIDENCY_TUITION_STATUS;
															?>
														</td> <!-- DIAM-2370 -->
														<? if($_GET['t'] != 1 && $_GET['t'] != 100){ ?>
														<td class="text-nowrap" ><?=$res_type->fields['REENTRY']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['TRANSFER_IN']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['TRANSFER_OUT']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['DISTANCE_LEARNING']?></td>
														<? } ?>
														<td class="text-nowrap" ><?=$res_type->fields['FUNDING']?></td>
														<? if($_GET['t'] != 1 && $_GET['t'] != 100){ ?>
														<td class="text-nowrap" ><?=$res_type->fields['PLACEMENT_STATUS']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['STRF_PAID_DATE']?></td>
														<? } ?>
														<td class="text-nowrap" ><?=$res_type->fields['STUDENT_GROUP']?></td>
														<? if($_GET['t'] != 1 && $_GET['t'] != 100){ ?>
														<td class="text-nowrap" ><?=$res_type->fields['GRADE_DATE']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['LDA']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['DETERMINATION_DATE']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['DROP_DATE']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['DROP_REASON']?></td>
														<? } ?>
														<td class="text-nowrap" >
															<? $campus = '';
															$res_campus = $db->Execute("select OFFICIAL_CAMPUS_NAME FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_CAMPUS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
															while (!$res_campus->EOF){
																if($campus != '')
																	$campus .= ', ';
																$campus .= $res_campus->fields['OFFICIAL_CAMPUS_NAME'];
																
																$res_campus->MoveNext();
															} 
															echo $campus; ?>
														</td>
														
														<? $res_cust = $db->Execute("select PK_CUSTOM_FIELDS,FIELD_NAME,PK_DATA_TYPES, PK_USER_DEFINED_FIELDS, TAB, IF(TAB = 'info',1,2) as TAB_ORDER from S_CUSTOM_FIELDS WHERE SECTION = 1 AND S_CUSTOM_FIELDS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 ORDER BY TAB_ORDER ASC, PK_CUSTOM_FIELDS ASC");
														while (!$res_cust->EOF) { 
															$PK_CUSTOM_FIELDS 		= $res_cust->fields['PK_CUSTOM_FIELDS'];
															$PK_USER_DEFINED_FIELDS = $res_cust->fields['PK_USER_DEFINED_FIELDS'];
															
															$cust_en_cond = "";
															if(strtolower($res_cust->fields['TAB']) == 'other')
																$cust_en_cond = " AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
															
															$res_1 = $db->Execute("select FIELD_VALUE from S_STUDENT_CUSTOM_FIELDS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' $cust_en_cond "); 
															$FIELD_VALUE = $res_1->fields['FIELD_VALUE']; 
															
															if($res_cust->fields['PK_DATA_TYPES'] == 4) {
																if($FIELD_VALUE != '')
																	$FIELD_VALUE = date("m/d/Y",strtotime($FIELD_VALUE));
															} else if($res_cust->fields['PK_DATA_TYPES'] == 2) {
																$res_dd = $db->Execute("select OPTION_NAME from S_USER_DEFINED_FIELDS_DETAIL WHERE PK_USER_DEFINED_FIELDS_DETAIL = '$FIELD_VALUE' ");
																$FIELD_VALUE = $res_dd->fields['OPTION_NAME']; 
															} else if($res_cust->fields['PK_DATA_TYPES'] == 3) {
																$res_dd = $db->Execute("select OPTION_NAME from S_USER_DEFINED_FIELDS_DETAIL WHERE PK_USER_DEFINED_FIELDS_DETAIL IN ($FIELD_VALUE) ");
																$FIELD_VALUE = '';
																while (!$res_dd->EOF) { 
																	if($FIELD_VALUE != '')
																		$FIELD_VALUE .= ', ';
																		
																	$FIELD_VALUE .= $res_dd->fields['OPTION_NAME']; 
																	$res_dd->MoveNext();
																}
															} ?>
															<td class="text-nowrap" ><?=$FIELD_VALUE?></td>
														<?	$res_cust->MoveNext();
														} if($_GET['t'] == 100){ ?>
														<td class="text-nowrap" ><?=$res_type->fields['IMPORT_ERROR']?></td>
														<td class="text-nowrap" ><?=$res_type->fields['IMPORT_STATUS']?></td>
														<td class="text-nowrap" >
															<input type="checkbox" id="CHECK_ALL_<?=$PK_STUDENT_MASTER?>" name="PK_STUDENT_MASTER[]" value="<?=$PK_STUDENT_MASTER?>" >
														</td>
														<? } ?>
													</tr>
												<?	$res_type->MoveNext();
												} ?>
											</tbody>
										</table>
									</div>
								</div>
								
								<? if($paginationDisplay != '') {  ?>
								<br />
								<div id="paginator1" class="datepaginator">
									<ul class="pagination">
										<li>
											<select class="pagination-page-list" onchange="ajax_notification(1)" id="ipp" >
												<option <? if($itemsPerPage == 10) echo "selected"; ?> value="10" >10</option>
												<option <? if($itemsPerPage == 25) echo "selected"; ?> value="25" >25</option>
												<option <? if($itemsPerPage == 100) echo "selected"; ?> value="100" >100</option>
												<option <? if($itemsPerPage == 500) echo "selected"; ?> value="500" >500</option>
											</select>
										</li>
										
										<?=$paginationDisplay?>
										
										<? if($nr > 0){ ?>
											<li><div class="pagination-info1 float-right col-xs-5 " ><strong><?=$kl.' - '.$i ?> of <?=$no ?></strong></div></li>
										<? } ?>
									</ul>
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
						<div class="form-group">
							<?=DELETE_MESSAGE_GENERAL?>
						</div>
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
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
	<script type="text/javascript" >

	function fun_check_all(){
		var str = '';
		if(document.getElementById('CHECK_ALL').checked == true)
			str = true;
		else
			str = false;
			
		var dd = document.getElementsByName('PK_STUDENT_MASTER[]');
		for(var i = 0 ; i < dd.length ; i++){
			dd[i].checked = str
		}
	}

	function delete_row(id){
		jQuery(document).ready(function($) {
			var str = '';
			var dd = document.getElementsByName('PK_STUDENT_MASTER[]');
			for(var i = 0 ; i < dd.length ; i++){
				if(dd[i].checked == true){
					if(str != '')
						str += ',';
					str += dd[i].value;
				}
			}
			if(str != '')
				$("#deleteModal").modal()
			else
				alert('<?=SELECT_LEAD_ERROR?>');
		});
	}
	function conf_delete(val,id){
		jQuery(document).ready(function($) {
			if(val == 1) {
				var str = '';
				var dd = document.getElementsByName('PK_STUDENT_MASTER[]');
				for(var i = 0 ; i < dd.length ; i++){
					if(dd[i].checked == true){
						if(str != '')
							str += ',';
						str += dd[i].value;
					}
				}
				window.location.href = 'student_data_view?act=del&t=100&id=<?=$_GET['id']?>&iid='+str;
			}
			$("#deleteModal").modal("hide");
		});
	}
	
	function ajax_notification(pn){
		var ipp = document.getElementById('ipp').value
		window.location.href = "student_data_view?t=<?=$_GET['t']?>&id=<?=$_GET['id']?>&me=<?=$_GET['me']?>&arch=<?=$_GET['arch']?>&pn="+pn+'&ipp='+ipp;
	}
	</script>
	
	<script src="../backend_assets/node_modules/date-paginator/moment.min.js"></script>
	<script src="../backend_assets/node_modules/date-paginator/bootstrap-datepaginator.min.js"></script>
    <script type="text/javascript">
    var datepaginator = function() {
        return {
            init: function() {
                $("#paginator1").datepaginator(),
				$("#paginator2").datepaginator({
					size: "large"
				}),
				$("#paginator3").datepaginator({
					size: "small"
				})
            }
        }
    }();
    jQuery(document).ready(function() {
        datepaginator.init()
    });
</body>

</html>
