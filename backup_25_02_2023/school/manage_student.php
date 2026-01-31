<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("check_access.php");

$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

if($_GET['t'] == 1 && $ADMISSION_ACCESS == 0) {
	header("location:../index");
	exit;
} else if($_GET['t'] == 2 && $REGISTRAR_ACCESS 	 == 0) {
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

function track_field_change($data){
	global $db;
	
	$STUDENT_TRACK_CHANGES['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
	$STUDENT_TRACK_CHANGES['PK_STUDENT_MASTER'] 	= $data['PK_STUDENT_MASTER'];
	$STUDENT_TRACK_CHANGES['PK_STUDENT_ENROLLMENT'] = $data['PK_STUDENT_ENROLLMENT'];
	$STUDENT_TRACK_CHANGES['ID'] 					= $data['ID'];
	$STUDENT_TRACK_CHANGES['FIELD_NAME'] 			= $data['FIELD_NAME'];
	$STUDENT_TRACK_CHANGES['OLD_VALUE'] 			= $data['OLD_VALUE'];
	$STUDENT_TRACK_CHANGES['NEW_VALUE'] 			= $data['NEW_VALUE'];
	$STUDENT_TRACK_CHANGES['CHANGED_BY'] 			= $_SESSION['PK_USER'];
	$STUDENT_TRACK_CHANGES['CHANGED_ON'] 			= date("Y-m-d H:i:s");
	
	db_perform('S_STUDENT_TRACK_CHANGES', $STUDENT_TRACK_CHANGES, 'insert');
}

$current_page1 = $_SERVER['PHP_SELF'];
$parts1 = explode('/', $current_page1);
$current_page = $parts1[count($parts1) - 1];
if($current_page != $_SESSION['PREVIOUS_PAGE'] || $_SESSION['top_t'] != $_GET['t'] || $_GET['clear'] == 1){
	$_SESSION['SORT_FIELD'] 	 = 'TRIM(S_STUDENT_MASTER.LAST_NAME) ASC, TRIM(S_STUDENT_MASTER.FIRST_NAME) ASC , TRIM(S_STUDENT_MASTER.MIDDLE_NAME) ASC';
	$_SESSION['SORT_ORDER'] 	 = '';
	$_SESSION['PAGE'] 			 = 1;
	$_SESSION['rows'] 			 = 25;
	$_SESSION['PREVIOUS_PAGE'] 	 = $current_page;
	$_SESSION['top_t'] 	 		 = $_GET['t'];
	$_SESSION['SRC_SEARCH'] 			 		= '';
	$_SESSION['SRC_SHOW_UNASSIGNED'] 		 	= '';
	$_SESSION['SRC_SHOW_ARCHIVED'] 		 		= '';
	$_SESSION['SRC_SHOW_ENROLLED_ONLY'] 		= '';
	$_SESSION['SRC_LEAD_START_DATE'] 		 	= '';
	$_SESSION['SRC_LEAD_END_DATE'] 		 		= '';
	$_SESSION['SRC_LDA_START_DATE'] 		 	= '';
	$_SESSION['SRC_LDA_END_DATE'] 				= '';
	$_SESSION['SRC_SHOW_MULTIPLE_ENROLLMENT'] 	= '';
	$_SESSION['SRC_EXPECTED_GRAD_START_DATE'] 	= '';
	$_SESSION['SRC_EXPECTED_GRAD_END_DATE'] 	= '';
	$_SESSION['SRC_GRAD_START_DATE'] 		 	= '';
	$_SESSION['SRC_GRAD_END_DATE'] 		 		= '';
	$_SESSION['SRC_SSN'] 		 				= '';
	
	$_SESSION['SRC_SEARCH_INACTIVE_FILTERS'] 		= '';
	$_SESSION['SRC_PK_SESSION'] 					= '';
	$_SESSION['SRC_DETERMINATION_START_DATE'] 		= '';
	$_SESSION['SRC_DETERMINATION_END_DATE'] 		= '';
	$_SESSION['SRC_DROP_START_DATE'] 				= '';
	$_SESSION['SRC_DROP_END_DATE'] 			= '';
	
	/* Ticket # 1824 */
	$_SESSION['SRC_ID'] 			= '';
	$_SESSION['SRC_ID_TYPE'] 		= '';
	/* Ticket # 1824 */
	
	if($_GET['clear'] == 1){
		$db->Execute("UPDATE Z_USER_FILTER SET PK_LEAD_SOURCE='', PK_CAMPUS='', PK_STUDENT_STATUS='', PK_CAMPUS_PROGRAM='', PK_TERM_MASTER='', PK_FUNDING='', PK_PLACEMENT_STATUS='', SEARCH_PAST_STUDENT=0, SHOW_LEAD=0, PK_REPRESENTATIVE = '', PK_SESSION = '', PK_STUDENT_GROUP = '', EMPLOYED = '' WHERE PK_USER = '$_SESSION[PK_USER]' AND PAGE_T = '$_GET[t]' "); //Ticket #1149  
	}
	
	$res_s = $db->Execute("select * from Z_USER_FILTER WHERE PK_USER = '$_SESSION[PK_USER]' AND PAGE_T = '$_GET[t]' ");
	$_SESSION['SRC_PK_REPRESENTATIVE'] 		= $res_s->fields['PK_REPRESENTATIVE'];
	$_SESSION['SRC_PK_LEAD_SOURCE'] 		= $res_s->fields['PK_LEAD_SOURCE'];
	$_SESSION['SRC_PK_CAMPUS'] 				= $res_s->fields['PK_CAMPUS'];
	$_SESSION['SRC_PK_STUDENT_STATUS'] 		= $res_s->fields['PK_STUDENT_STATUS'];
	$_SESSION['SRC_PK_CAMPUS_PROGRAM'] 		= $res_s->fields['PK_CAMPUS_PROGRAM'];
	$_SESSION['SRC_PK_TERM_MASTER'] 		= $res_s->fields['PK_TERM_MASTER'];
	$_SESSION['SRC_PK_FUNDING'] 			= $res_s->fields['PK_FUNDING'];
	$_SESSION['SRC_PK_PLACEMENT_STATUS'] 	= $res_s->fields['PK_PLACEMENT_STATUS'];
	$_SESSION['SRC_EMPLOYED'] 				= $res_s->fields['EMPLOYED'];
	$_SESSION['SRC_PK_SESSION'] 			= $res_s->fields['PK_SESSION'];
	$_SESSION['PK_STUDENT_GROUP'] 			= $res_s->fields['PK_STUDENT_GROUP'];
	
	if($res_s->fields['SEARCH_PAST_STUDENT'] == 1)
		$_SESSION['SRC_SEARCH_PAST_STUDENT'] = 'true';
	else
		$_SESSION['SRC_SEARCH_PAST_STUDENT'] = '';
		
	
	if($res_s->fields['SHOW_LEAD'] == 1)
		$_SESSION['SRC_SHOW_LEAD'] = 'true';
	else
		$_SESSION['SRC_SHOW_LEAD'] = '';
	
	if($_GET['clear'] == 1){
		header("location:manage_student?t=".$_GET['t']);
		exit;
	}
}

if($_GET['act'] == 'del')	{
	/*
	if($_GET['t'] == 1 || $_GET['t'] == 2 || $_GET['t'] == 3 ){
		$PK_STUDENT_MASTER_ARR = explode(",",$_GET['id']);
		foreach($PK_STUDENT_MASTER_ARR as $PK_STUDENT_MASTER){
			$db->Execute("DELETE FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_ACADEMICS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_RACE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_CAMPUS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_STATUS_LOG WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_CONTACT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_TASK WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_NOTES WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_DOCUMENTS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_REQUIREMENT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_QUESTIONNAIRE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_OTHER_EDU WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_CUSTOM_FIELDS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_TEST WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_ATB_TEST WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_ACT_TEST WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_SAT_TEST WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			
			$db->Execute("DELETE FROM S_STUDENT_COURSE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_SCHEDULE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_GRADE WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$db->Execute("DELETE FROM S_STUDENT_DISBURSEMENT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
	}
	header("location:manage_student?t=".$_GET['t']);
	*/
}  else if($_GET['act'] == 'assign')	{
	if($ADMISSION_ACCESS == 3){
		$res_tra = $db->Execute("select CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_GET[e]' ");
		$NEW_REP = $res_tra->fields['NAME'];
		
		$ids = explode(",",$_GET['id']);
		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, S_STUDENT_ENROLLMENT  WHERE S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$id1[1]' ");
			$CUR_REP = $res_tra->fields['NAME'];
				
			$db->Execute("UPDATE S_STUDENT_ENROLLMENT SET PK_REPRESENTATIVE = '$_GET[e]' WHERE PK_STUDENT_MASTER = '$id1[0]' AND PK_STUDENT_ENROLLMENT = '$id1[1]' AND  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
			
			$track_data['ID'] 			 			= '';
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['FIELD_NAME']		 		= REPRESENTATIVE;
			$track_data['OLD_VALUE'] 	 			= $CUR_REP;
			$track_data['NEW_VALUE'] 	 			= $NEW_REP;
			track_field_change($track_data);
		}
		
		header("location:manage_student?t=".$_GET['t']);
	}
} else if($_GET['act'] == 'approve') {
	if($ADMISSION_ACCESS == 2 || $ADMISSION_ACCESS == 3){

		$res = $db->Execute("SELECT PK_STUDENT_STATUS, CONCAT(STUDENT_STATUS,' - ',DESCRIPTION) AS NAME FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS_MASTER = '13' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
		$NEW_STS = $res->fields['NAME'];
		
		$ids = explode(",",$_GET['id']);
		foreach($ids as $id){
			$id1 = explode("-",$id);
			
			$res_tra = $db->Execute("select CONCAT(STUDENT_STATUS,' - ',DESCRIPTION) AS NAME from M_STUDENT_STATUS, S_STUDENT_ENROLLMENT WHERE M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS AND  PK_STUDENT_ENROLLMENT = '$id1[1]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_STS = $res_tra->fields['NAME'];
			
			$STUDENT_ENROLLMENT['PK_STUDENT_STATUS'] = $res->fields['PK_STUDENT_STATUS'];
			$STUDENT_ENROLLMENT['STATUS_DATE'] 		 = date("Y-m-d");
			db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'update'," PK_STUDENT_ENROLLMENT = '$id1[1]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
			
			$STUDENT_STATUS_LOG['PK_STUDENT_STATUS'] 		= $STUDENT_ENROLLMENT['PK_STUDENT_STATUS'];
			$STUDENT_STATUS_LOG['PK_STUDENT_MASTER'] 		= $id1[0];
			$STUDENT_STATUS_LOG['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$STUDENT_STATUS_LOG['PK_ACCOUNT']  				= $_SESSION['PK_ACCOUNT'];
			$STUDENT_STATUS_LOG['CHANGED_BY']  				= $_SESSION['PK_USER'];
			$STUDENT_STATUS_LOG['CHANGED_ON']  				= date("Y-m-d H:i");
			db_perform('S_STUDENT_STATUS_LOG', $STUDENT_STATUS_LOG, 'insert');
			
			$track_data['ID'] 			 			= '';
			$track_data['PK_STUDENT_MASTER'] 		= $id1[0];
			$track_data['PK_STUDENT_ENROLLMENT'] 	= $id1[1];
			$track_data['GLOBAL_CHANGE'] 			= 0;
			$track_data['FIELD_NAME']	 			= STATUS;
			$track_data['OLD_VALUE'] 	 			= $CUR_STS;
			$track_data['NEW_VALUE'] 	 			= $NEW_STS;
			track_field_change($track_data);
		}
		
		header("location:manage_student?t=".$_GET['t']);
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
	<title>
		<?	if($_GET['t'] == 1) echo LEAD_PAGE_TITLE; else echo STUDENT_PAGE_TITLE; ?> | <?=$title?>
	</title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<style>
	.datagrid-header td {font-size: 14px !important; vertical-align: bottom;}
	
	.fixed {
		position: fixed; top: 65px;
	}
	
	/* .multiselect-selected-text {white-space: pre-wrap} */
	/* .multiselect{height: 10px !important;} */
	
	.dropdown-menu>li>a { white-space: nowrap; }
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
				<div class="row page-titles" style="padding-bottom: 10px;" >
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<? if($_GET['t'] == 1)
								echo MNU_ADMISSION;
							else if($_GET['t'] == '' || $_GET['t'] == 2)
								echo MNU_REGISTRAR;
							else if($_GET['t'] == 3)
								echo MNU_FINANCIAL_AID; 
							else if($_GET['t'] == 5)
								echo MNU_ACCOUNTING;
							else if($_GET['t'] == 6)
								echo MNU_PLACEMENT; ?>
						</h4>
                    </div>
					<div class="col-md-7 align-self-center text-right">
					
						<!-- Ticket # 1180 -->
                        <div class="d-flex justify-content-end align-items-center">
							
							<a href="manage_student?t=<?=$_GET['t']?>&clear=1" class="btn btn-info d-none d-lg-block m-l-15"><i class="fas fa-newspaper"></i> <?=CLEAR_FILTER?></a>
							
							<? if($REGISTRAR_ACCESS == 3 || $ADMISSION_ACCESS == 3){ ?>
								<a href="javascript:void(0)" onclick="bulk_update_popup()" style="display:none !important" id="BULK_UPDATE_LINK" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=BULK_UPDATE?></a> 
							<? } ?>

							<!--<a href="javascript:void(0)" onclick="bulk_delete()" style="display:none !important" id="BULK_DELETE_LINK" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=BULK_DELETE?></a> -->
							
							<a href="javascript:void(0)" onclick="show_data_view()" class="btn btn-info d-none d-lg-block m-l-15"><i class="fas fa-newspaper"></i> <?=DATA_VIEW?></a>
							
							<? if($_GET['t'] == 1 && $ADMISSION_ACCESS == 3) { ?>
								<a href="student?n=1&t=1" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>
							<? } ?>
                        </div>
						<!-- Ticket # 1180 -->
                    </div>
				</div>
				
                <div class="row">
					<div class="col-md-12">
						<? if($_GET['t'] == 1){ ?>
							<table style="width:100%" >
							<tr>
								<td style="width:85%" >
									<table style="width:100%" >
										<tr>
											<td style="width:15.65%" >
												<? /* Ticket #1261 */
												if($_SESSION['SRC_PK_CAMPUS'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($_SESSION[SRC_PK_CAMPUS]) ");
													$count = $res_chk->RecordCount(); 
												} 
												/* Ticket #1261 */ ?>
												<a href="javascript:void(0)" onclick="select_filter('camp')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=CAMPUS.' <span id="camp_LBL">('.$count.' Selected)</span>'?> </a>  <!-- Ticket #1149 -->
											</td>
											
											<td style="width:18.65%" >
												<? /* Ticket #1261 */
												if($_SESSION['SRC_PK_TERM_MASTER'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_TERM_MASTER from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER IN ($_SESSION[SRC_PK_TERM_MASTER]) ");
													$count = $res_chk->RecordCount(); 
												}
												/* Ticket #1261 */ ?>
												<a href="javascript:void(0)" onclick="select_filter('term')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=FIRST_TERM_DATE.' <span id="term_LBL">('.$count.' Selected)</span>'?> </a>  <!-- Ticket #1149 --> 
											</td>
											
											<td style="width:14.65%" >
												<? /* Ticket #1261 */
												if($_SESSION['SRC_PK_CAMPUS_PROGRAM'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_CAMPUS_PROGRAM from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_PROGRAM IN ($_SESSION[SRC_PK_CAMPUS_PROGRAM]) ");
													$count = $res_chk->RecordCount(); 
												}
												/* Ticket #1261 */ ?>
												<a href="javascript:void(0)" onclick="select_filter('prog')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=PROGRAM.' <span id="prog_LBL">('.$count.' Selected)</span>'?> </a>  <!-- Ticket #1149 -->
											</td>
											
											<td style="width:13.65%" >
												<? if($_SESSION['SRC_PK_SESSION'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_SESSION, SESSION from M_SESSION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_SESSION IN ($_SESSION[SRC_PK_SESSION]) ");
													$count = $res_chk->RecordCount(); 
												} ?>
												<a href="javascript:void(0)" onclick="select_filter('session')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=SESSION.' <span id="session_LBL">('.$count.' Selected)</span>'?> </a>
											</td>
											
											<td style="width:18.65%" >
												<div id="status_div" >
													<? /* Ticket #1261 */
													if($_SESSION['SRC_PK_STUDENT_STATUS'] == '') 
														$count = 0;
													else {
														$res_chk = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_STATUS IN ($_SESSION[SRC_PK_STUDENT_STATUS]) ");
														$count = $res_chk->RecordCount(); 
													}
													/* Ticket #1261 */ ?>
													<a href="javascript:void(0)" onclick="select_filter('stu_sts')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=STUDENT_STATUS.' <span id="stu_sts_LBL">('.$count.' Selected)</span>'?> </a>  <!-- Ticket #1149 -->
												</div>
											</td>
											
											<td style="width:18.65%" >
												<? if($_SESSION['SRC_PK_STUDENT_GROUP'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_STUDENT_GROUP, STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_GROUP IN ($_SESSION[SRC_PK_STUDENT_GROUP]) ");
													$count = $res_chk->RecordCount(); 
												} ?>
												<a href="javascript:void(0)" onclick="select_filter('student_group')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=STUDENT_GROUP.' <span id="student_group_LBL">('.$count.' Selected)</span>'?> </a>
											</td>
										</tr>
										
										<tr>
											<td >
												<? /* Ticket #1261 */
												if($_SESSION['SRC_PK_LEAD_SOURCE'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_LEAD_SOURCE from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_LEAD_SOURCE IN ($_SESSION[SRC_PK_LEAD_SOURCE]) ");
													$count = $res_chk->RecordCount(); 
												} 
												/* Ticket #1261 */?>
												<a href="javascript:void(0)" onclick="select_filter('ls')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=LEAD_SOURCE.' <span id="ls_LBL">('.$count.' Selected)</span>'?> </a> <!-- Ticket #1149 -->
											</td>
											<td >
												<? /* Ticket #1261 */
												if($_SESSION['SRC_PK_REPRESENTATIVE'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = 2 AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER IN ($_SESSION[SRC_PK_REPRESENTATIVE]) ");
													$count = $res_chk->RecordCount();
												}
												/* Ticket #1261 */ ?>
												<a href="javascript:void(0)" onclick="select_filter('rep')" style="margin-left: 0;padding: 7px 0px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=ADMISSION_REP.' <span id="rep_LBL">('.$count.' Selected)</span>'?> </a>  <!-- Ticket #1149 -->
											</td>
											
											<td >
												<input type="text" class="form-control date" id="LEAD_START_DATE" name="LEAD_START_DATE" placeholder="<?=LEAD_START_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_LEAD_START_DATE']?>" >
											</td>
											<td >
												<input type="text" class="form-control date" id="LEAD_END_DATE" name="LEAD_END_DATE" placeholder="<?=LEAD_END_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_LEAD_END_DATE']?>" >
											</td>
											
											<td >
												<!-- Ticket # 1824 -->
												<table style="width:100%" >
													<tr>
														<td style="width:50%" >
															<? $ID_TYPE_ARR = array();
															if($_SESSION['SRC_ID_TYPE'] != '') {
																$ID_TYPE_ARR = explode(",", $_SESSION['SRC_ID_TYPE']);
															} ?>
															<select name="ID_TYPE[]" multiple id="ID_TYPE"  class="form-control" style="margin-top: 10px;" onchange="doSearch()" >
																<option value="1" <? if(in_array(1,$ID_TYPE_ARR)) echo "selected"; ?> >ADM User ID</option>
																<option value="2" <? if(in_array(2,$ID_TYPE_ARR)) echo "selected"; ?> >Badge ID</option>
																<option value="3" <? if(in_array(3,$ID_TYPE_ARR)) echo "selected"; ?> >Previous SIS Lead ID/No</option>
																<option value="4" <? if(in_array(4,$ID_TYPE_ARR)) echo "selected"; ?> >Previous SIS Student ID/No</option>
																<option value="5" <? if(in_array(5,$ID_TYPE_ARR)) echo "selected"; ?> >Student ID</option>
																<option value="6" <? if(in_array(6,$ID_TYPE_ARR)) echo "selected"; ?> >SSN</option>
															</select>
														</td>
														<td style="width:50%" >
															<input type="text" class="form-control" id="ID" name="ID" placeholder="<?=ID?>" onchange="doSearch()" value="<?=$_SESSION['SRC_ID']?>" onkeypress="search(event)" > <!-- Ticket # 1432 --><!-- Ticket # 1632 -->
														</td>
													</tr>
												</table>
												<!-- Ticket # 1824 -->
											</td>
											<td >
												<input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome;" onkeypress="search(event)" value="<?=$_SESSION['SRC_SEARCH']?>" >
											</td>
										</tr>
										
									</table>
								</td>
								<td style="width:15%" >
									<input type="checkbox" id="SHOW_ARCHIVED" value="1" onclick="doSearch()" <? if($_SESSION['SRC_SHOW_ARCHIVED'] == 'true') echo "checked"; ?> >
									<?=SHOW_ARCHIVED?><br />
									
									<input type="checkbox" id="SHOW_UNASSIGNED" value="1" onclick="doSearch()" <? if($_SESSION['SRC_SHOW_UNASSIGNED'] == 'true') echo "checked"; ?> >
									<?=SHOW_UNASSIGNED?><br />
									
									<input type="checkbox" id="SEARCH_PAST_STUDENT" value="1" onclick="get_status(2)" <? if($_SESSION['SRC_SEARCH_PAST_STUDENT'] == 'true') echo "checked"; ?> >
									<?=INCLUDE_REGISTRAR_STUDENTS?>
								</td>
							</tr>
						</table>
						<? } else if($_GET['t'] == 2 || $_GET['t'] == 3 || $_GET['t'] == 5){ ?>
						<table style="width:100%" >
							<tr>
								<td style="width:85%" >
									<table style="width:100%" >
										<tr>
											<td style="width:14.65%" >
												<? /* Ticket #1261 */
												if($_SESSION['SRC_PK_CAMPUS'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($_SESSION[SRC_PK_CAMPUS]) ");
													$count = $res_chk->RecordCount(); 
												} 
												/* Ticket #1261 */ ?>
												<a href="javascript:void(0)" onclick="select_filter('camp')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=CAMPUS.' <span id="camp_LBL">('.$count.' Selected)</span>'?> </a>  <!-- Ticket #1149 -->
											</td>
											
											<td style="width:18.65%" >
												<? /* Ticket #1261 */
												if($_SESSION['SRC_PK_TERM_MASTER'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_TERM_MASTER from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER IN ($_SESSION[SRC_PK_TERM_MASTER]) ");
													$count = $res_chk->RecordCount(); 
												}
												/* Ticket #1261 */ ?>
												<a href="javascript:void(0)" onclick="select_filter('term')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=FIRST_TERM_DATE.' <span id="term_LBL">('.$count.' Selected)</span>'?> </a>  <!-- Ticket #1149 --> 
											</td>
											
											<td style="width:14.65%" >
												<? /* Ticket #1261 */
												if($_SESSION['SRC_PK_CAMPUS_PROGRAM'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_CAMPUS_PROGRAM from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_PROGRAM IN ($_SESSION[SRC_PK_CAMPUS_PROGRAM]) ");
													$count = $res_chk->RecordCount(); 
												}
												/* Ticket #1261 */ ?>
												<a href="javascript:void(0)" onclick="select_filter('prog')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=PROGRAM.' <span id="prog_LBL">('.$count.' Selected)</span>'?> </a>  <!-- Ticket #1149 -->
											</td>
											
											<td style="width:14.65%" >
												<? if($_SESSION['SRC_PK_SESSION'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_SESSION, SESSION from M_SESSION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_SESSION IN ($_SESSION[SRC_PK_SESSION]) ");
													$count = $res_chk->RecordCount(); 
												} ?>
												<a href="javascript:void(0)" onclick="select_filter('session')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=SESSION.' <span id="session_LBL">('.$count.' Selected)</span>'?> </a>
											</td>
											
											<td style="width:18.65%" >
												<div id="status_div" >
													<? /* Ticket #1261 */
													if($_SESSION['SRC_PK_STUDENT_STATUS'] == '') 
														$count = 0;
													else {
														$res_chk = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_STATUS IN ($_SESSION[SRC_PK_STUDENT_STATUS]) ");
														$count = $res_chk->RecordCount(); 
													}
													/* Ticket #1261 */ ?>
													<a href="javascript:void(0)" onclick="select_filter('stu_sts')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=STUDENT_STATUS.' <span id="stu_sts_LBL">('.$count.' Selected)</span>'?> </a>  <!-- Ticket #1149 -->
												</div>
											</td>
											
											<td style="width:18.65%" >
												<? if($_SESSION['SRC_PK_STUDENT_GROUP'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_STUDENT_GROUP, STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_GROUP IN ($_SESSION[SRC_PK_STUDENT_GROUP]) ");
													$count = $res_chk->RecordCount(); 
												} ?>
												<a href="javascript:void(0)" onclick="select_filter('student_group')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=STUDENT_GROUP.' <span id="student_group_LBL">('.$count.' Selected)</span>'?> </a>
											</td>
										</tr>
										<tr>
											<td >
												<input type="text" class="form-control date" id="DETERMINATION_START_DATE" name="DETERMINATION_START_DATE" placeholder="<?=DETERMINATION_START_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_DETERMINATION_START_DATE']?>" >
											</td>
											<td >
												<input type="text" class="form-control date" id="DETERMINATION_END_DATE" name="DETERMINATION_END_DATE" placeholder="<?=DETERMINATION_END_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_DETERMINATION_END_DATE']?>" >
											</td>
											
											<td >
												<input type="text" class="form-control date" id="DROP_START_DATE" name="DROP_START_DATE" placeholder="<?=DROP_START_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_DROP_START_DATE']?>" >
											</td>
											<td >
												<input type="text" class="form-control date" id="DROP_END_DATE" name="DROP_END_DATE" placeholder="<?=DROP_END_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_DROP_END_DATE']?>" >
											</td>
											
											<td >
												<input type="text" class="form-control date" id="EXPECTED_GRAD_START_DATE" name="EXPECTED_GRAD_START_DATE" placeholder="<?=EXPECTED_GRAD_START_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_EXPECTED_GRAD_START_DATE']?>" >
											</td>
											<td >
												<input type="text" class="form-control date" id="EXPECTED_GRAD_END_DATE" name="EXPECTED_GRAD_END_DATE" placeholder="<?=EXPECTED_GRAD_END_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_EXPECTED_GRAD_END_DATE']?>" >
											</td>
										</tr>
										<tr>
											<td >
												<input type="text" class="form-control date" id="GRAD_START_DATE" name="GRAD_START_DATE" placeholder="<?=GRAD_START_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_GRAD_START_DATE']?>" >
											</td>
											<td >
												<input type="text" class="form-control date" id="GRAD_END_DATE" name="GRAD_END_DATE" placeholder="<?=GRAD_END_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_GRAD_END_DATE']?>" >
											</td>
											
											<td >
												<input type="text" class="form-control date" id="LDA_START_DATE" name="LDA_START_DATE" placeholder="<?=LDA_START_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_LDA_START_DATE']?>" >
											</td>
											<td >
												<input type="text" class="form-control date" id="LDA_END_DATE" name="LDA_END_DATE" placeholder="<?=LDA_END_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_LDA_END_DATE']?>" >
											</td>
											
											<td >
												<!-- Ticket # 1824 -->
												<table style="width:100%" >
													<tr>
														<td style="width:50%" >
															<? $ID_TYPE_ARR = array();
															if($_SESSION['SRC_ID_TYPE'] != '') {
																$ID_TYPE_ARR = explode(",", $_SESSION['SRC_ID_TYPE']);
															} ?>
															<select name="ID_TYPE[]" multiple id="ID_TYPE"  class="form-control" style="margin-top: 10px;" onchange="doSearch()" >
																<option value="1" <? if(in_array(1,$ID_TYPE_ARR)) echo "selected"; ?> >ADM User ID</option>
																<option value="2" <? if(in_array(2,$ID_TYPE_ARR)) echo "selected"; ?> >Badge ID</option>
																<option value="3" <? if(in_array(3,$ID_TYPE_ARR)) echo "selected"; ?> >Previous SIS Lead ID/No</option>
																<option value="4" <? if(in_array(4,$ID_TYPE_ARR)) echo "selected"; ?> >Previous SIS Student ID/No</option>
																<option value="5" <? if(in_array(5,$ID_TYPE_ARR)) echo "selected"; ?> >Student ID</option>
																<option value="6" <? if(in_array(6,$ID_TYPE_ARR)) echo "selected"; ?> >SSN</option>
															</select>
														</td>
														<td style="width:50%" >
															<input type="text" class="form-control" id="ID" name="ID" placeholder="<?=ID?>" onchange="doSearch()" value="<?=$_SESSION['SRC_ID']?>" onkeypress="search(event)" > <!-- Ticket # 1432 --><!-- Ticket # 1632 -->
														</td>
													</tr>
												</table>
												<!-- Ticket # 1824 -->
											</td>
											<td >
												<input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome;" onkeypress="search(event)" value="<?=$_SESSION['SRC_SEARCH']?>" >
											</td>
										</tr>
										
									</table>
								</td>
								<td style="width:15%" >
									<input type="checkbox" id="SHOW_ARCHIVED" value="1" onclick="doSearch()" <? if($_SESSION['SRC_SHOW_ARCHIVED'] == 'true') echo "checked"; ?> >
									<?=SHOW_ARCHIVED?><br />
									
									<? if($_GET['t'] == 2){ ?>
										<input type="checkbox" id="SHOW_ENROLLED_ONLY" value="1" onclick="doSearch()" <? if($_SESSION['SRC_SHOW_ENROLLED_ONLY'] == 'true') echo "checked"; ?> >
										<?=SHOW_ENROLLED_ONLY?><br />
									<? } else { ?>
										<input type="checkbox" id="SHOW_LEAD" value="1" onclick="get_status(1);" <? if($_SESSION['SRC_SHOW_LEAD'] == 'true') echo "checked"; ?> >
										<?=SHOW_LEAD?><br />
									<? } ?>
									
									<input type="checkbox" id="SHOW_MULTIPLE_ENROLLMENT" value="1" onclick="doSearch();" <? if($_SESSION['SRC_SHOW_MULTIPLE_ENROLLMENT'] == 'true') echo "checked"; ?> >
									<?=SHOW_MULTIPLE_ENROLLMENT ?>
								</td>
							</tr>
						</table>
						
						
						<? } else if($_GET['t'] == 6){ ?>
						<table style="width:100%" >
							<tr>
								<td style="width:85%" >
									<table style="width:100%" >
										<tr>
											<td style="width:14.65%" >
												<? /* Ticket #1261 */
												if($_SESSION['SRC_PK_CAMPUS'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($_SESSION[SRC_PK_CAMPUS]) ");
													$count = $res_chk->RecordCount(); 
												} 
												/* Ticket #1261 */ ?>
												<a href="javascript:void(0)" onclick="select_filter('camp')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=CAMPUS.' <span id="camp_LBL">('.$count.' Selected)</span>'?> </a>  <!-- Ticket #1149 -->
											</td>
											
											<td style="width:18.65%" >
												<? /* Ticket #1261 */
												if($_SESSION['SRC_PK_TERM_MASTER'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_TERM_MASTER from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER IN ($_SESSION[SRC_PK_TERM_MASTER]) ");
													$count = $res_chk->RecordCount(); 
												}
												/* Ticket #1261 */ ?>
												<a href="javascript:void(0)" onclick="select_filter('term')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=FIRST_TERM_DATE.' <span id="term_LBL">('.$count.' Selected)</span>'?> </a>  <!-- Ticket #1149 --> 
											</td>
											
											<td style="width:14.65%" >
												<? /* Ticket #1261 */
												if($_SESSION['SRC_PK_CAMPUS_PROGRAM'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_CAMPUS_PROGRAM from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_PROGRAM IN ($_SESSION[SRC_PK_CAMPUS_PROGRAM]) ");
													$count = $res_chk->RecordCount(); 
												}
												/* Ticket #1261 */ ?>
												<a href="javascript:void(0)" onclick="select_filter('prog')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=PROGRAM.' <span id="prog_LBL">('.$count.' Selected)</span>'?> </a>  <!-- Ticket #1149 -->
											</td>
											
											<td style="width:14.65%" >
												<? if($_SESSION['SRC_PK_SESSION'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_SESSION, SESSION from M_SESSION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_SESSION IN ($_SESSION[SRC_PK_SESSION]) ");
													$count = $res_chk->RecordCount(); 
												} ?>
												<a href="javascript:void(0)" onclick="select_filter('session')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=SESSION.' <span id="session_LBL">('.$count.' Selected)</span>'?> </a>
											</td>
											
											<td style="width:18.65%" >
												<div id="status_div" >
													<? /* Ticket #1261 */
													if($_SESSION['SRC_PK_STUDENT_STATUS'] == '') 
														$count = 0;
													else {
														$res_chk = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_STATUS IN ($_SESSION[SRC_PK_STUDENT_STATUS]) ");
														$count = $res_chk->RecordCount(); 
													}
													/* Ticket #1261 */ ?>
													<a href="javascript:void(0)" onclick="select_filter('stu_sts')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=STUDENT_STATUS.' <span id="stu_sts_LBL">('.$count.' Selected)</span>'?> </a>  <!-- Ticket #1149 -->
												</div>
											</td>
											
											<td style="width:18.65%" >
												<? if($_SESSION['SRC_PK_STUDENT_GROUP'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_STUDENT_GROUP, STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_GROUP IN ($_SESSION[SRC_PK_STUDENT_GROUP]) ");
													$count = $res_chk->RecordCount(); 
												} ?>
												<a href="javascript:void(0)" onclick="select_filter('student_group')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=STUDENT_GROUP.' <span id="student_group_LBL">('.$count.' Selected)</span>'?> </a>
											</td>
										</tr>
										<tr>
											<td >
												<? if($_SESSION['SRC_EMPLOYED'] == '') 
													$count = 0;
												else
													$count = count(explode(",",$_SESSION['SRC_EMPLOYED'])); ?>
												<a href="javascript:void(0)" onclick="select_filter('employed')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=EMPLOYED.' <span id="employed_LBL">('.$count.' Selected)</span>'?> </a>  <!-- Ticket #1149 -->
											</td>
											<td >
												<? if($_SESSION['SRC_PK_PLACEMENT_STATUS'] == '') 
													$count = 0;
												else {
													$res_chk = $db->Execute("select PK_PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_PLACEMENT_STATUS IN ($_SESSION[SRC_PK_PLACEMENT_STATUS]) ");
													$count = $res_chk->RecordCount();
												} 
												/* Ticket #1261 */ ?>
												<a href="javascript:void(0)" onclick="select_filter('ps')" style="margin-left: 0;padding: 7px 3px;" class="btn btn-info d-none d-lg-block m-l-15"> <?=PLACEMENT_STATUS.' <span id="ps_LBL">('.$count.' Selected)</span>'?> </a>  <!-- Ticket #1149 -->
											</td>
											
											<td >
												<input type="text" class="form-control date" id="EXPECTED_GRAD_START_DATE" name="EXPECTED_GRAD_START_DATE" placeholder="<?=EXPECTED_GRAD_START_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_EXPECTED_GRAD_START_DATE']?>" >
											</td>
											<td >
												<input type="text" class="form-control date" id="EXPECTED_GRAD_END_DATE" name="EXPECTED_GRAD_END_DATE" placeholder="<?=EXPECTED_GRAD_END_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_EXPECTED_GRAD_END_DATE']?>" >
											</td>
											
											<td >
												<input type="text" class="form-control date" id="DETERMINATION_START_DATE" name="DETERMINATION_START_DATE" placeholder="<?=DETERMINATION_START_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_DETERMINATION_START_DATE']?>" >
											</td>
											<td >
												<input type="text" class="form-control date" id="DETERMINATION_END_DATE" name="DETERMINATION_END_DATE" placeholder="<?=DETERMINATION_END_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_DETERMINATION_END_DATE']?>" >
											</td>
										</tr>
										<tr>
											<td >
												<input type="text" class="form-control date" id="GRAD_START_DATE" name="GRAD_START_DATE" placeholder="<?=GRAD_START_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_GRAD_START_DATE']?>" >
											</td>
											<td >
												<input type="text" class="form-control date" id="GRAD_END_DATE" name="GRAD_END_DATE" placeholder="<?=GRAD_END_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_GRAD_END_DATE']?>" >
											</td>
											
											<td >
												<input type="text" class="form-control date" id="LDA_START_DATE" name="LDA_START_DATE" placeholder="<?=LDA_START_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_LDA_START_DATE']?>" >
											</td>
											<td >
												<input type="text" class="form-control date" id="LDA_END_DATE" name="LDA_END_DATE" placeholder="<?=LDA_END_DATE?>" onchange="doSearch()" value="<?=$_SESSION['SRC_LDA_END_DATE']?>" >
											</td>
											
											<td >
												<!-- Ticket # 1824 -->
												<table style="width:100%" >
													<tr>
														<td style="width:50%" >
															<? $ID_TYPE_ARR = array();
															if($_SESSION['SRC_ID_TYPE'] != '') {
																$ID_TYPE_ARR = explode(",", $_SESSION['SRC_ID_TYPE']);
															} ?>
															<select name="ID_TYPE[]" multiple id="ID_TYPE"  class="form-control" style="margin-top: 10px;" onchange="doSearch()" >
																<option value="1" <? if(in_array(1,$ID_TYPE_ARR)) echo "selected"; ?> >ADM User ID</option>
																<option value="2" <? if(in_array(2,$ID_TYPE_ARR)) echo "selected"; ?> >Badge ID</option>
																<option value="3" <? if(in_array(3,$ID_TYPE_ARR)) echo "selected"; ?> >Previous SIS Lead ID/No</option>
																<option value="4" <? if(in_array(4,$ID_TYPE_ARR)) echo "selected"; ?> >Previous SIS Student ID/No</option>
																<option value="5" <? if(in_array(5,$ID_TYPE_ARR)) echo "selected"; ?> >Student ID</option>
																<option value="6" <? if(in_array(6,$ID_TYPE_ARR)) echo "selected"; ?> >SSN</option>
															</select>
														</td>
														<td style="width:50%" >
															<input type="text" class="form-control" id="ID" name="ID" placeholder="<?=ID?>" onchange="doSearch()" value="<?=$_SESSION['SRC_ID']?>" onkeypress="search(event)" > <!-- Ticket # 1432 --><!-- Ticket # 1632 -->
														</td>
													</tr>
												</table>
												<!-- Ticket # 1824 -->
											</td>
											<td >
												<input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  style="font-family: FontAwesome;" onkeypress="search(event)" value="<?=$_SESSION['SRC_SEARCH']?>" >
											</td>
										</tr>
										
									</table>
								</td>
								<td style="width:15%" >
									<input type="checkbox" id="SHOW_ARCHIVED" value="1" onclick="doSearch()" <? if($_SESSION['SRC_SHOW_ARCHIVED'] == 'true') echo "checked"; ?> >
									<?=SHOW_ARCHIVED?><br />
									
									<input type="checkbox" id="SHOW_MULTIPLE_ENROLLMENT" value="1" onclick="doSearch();" <? if($_SESSION['SRC_SHOW_MULTIPLE_ENROLLMENT'] == 'true') echo "checked"; ?> >
									<?=SHOW_MULTIPLE_ENROLLMENT ?>
								</td>
							</tr>
						</table>
						
						<? } ?>
					</div>
					
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12" >
										<? if($_SESSION['SORT_FIELD'] != '')
											$sort = 'sortName = "'.$_SESSION['SORT_FIELD'].'" sortOrder="'.$_SESSION['SORT_ORDER'].'" ';
										else
											$sort = '' ?>
										
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" nowrap="false" autoRowHeight="true"    
										url="grid_student?t=<?=$_GET['t']?>" toolbar="#tb" pagination="true"  pageNumber="<?=$_SESSION['PAGE']?>" pageSize="<?=$_SESSION['rows']?>" <?=$sort?>  >
											<thead >
												<tr>
													<th field="PK_STUDENT_MASTER" width="150px" hidden="true" sortable="true" ></th>
													<th field="PK_STUDENT_ENROLLMENT" width="150px" hidden="true" sortable="true" ></th>
													<th field="PK_STUDENT_STATUS_MASTER" width="150px" hidden="true" sortable="true" ></th>
													
													<? if($_GET['t'] == 1){ ?>
														<th field="SELECT" width="20px" sortable="true" >
															<input type="checkbox" id="CHECK_ALL" onclick="select_all(<?=$_GET['t']?>)" >
														</th>
														<th field="NAME" width="175px" align="left" sortable="true" ><?=NAME?></th>
														<th field="STUDENT_ID" width="100px" align="left" sortable="true" ><?=STUDENT_ID?></th>
														<th field="CAMPUS_CODE" width="100px" align="left" sortable="true" ><?=CAMPUS?></th>
														<th field="BEGIN_DATE" width="90px" align="left" sortable="true" ><?=FIRST_TERM_DATE_1?></th>
														<th field="PROGRAM" width="100px" align="left" sortable="true" ><?=PROGRAM?></th>
														<th field="SESSION" width="70px" align="left" sortable="true" ><?=SESSION?></th>
														<th field="STUDENT_STATUS" width="100px" align="left" sortable="true" ><?=STATUS?></th>
														<th field="STUDENT_GROUP" width="100px" align="left" sortable="true" ><?=STUDENT_GROUP_1?></th>
														<th field="REPRESENTATIVE" width="130px" align="left" sortable="true" ><?=ADMISSION_REP?></th>
														<th field="ENTRY_DATE" width="90px" align="left" sortable="true" ><?=ENTRY_DATE_1?></th>
														<th field="LEAD_SOURCE" width="90px" align="left" sortable="true" ><?=LEAD_SOURCE_1?></th>
														
														<th field="EC" width="40px"  sortable="false" ><?=EC?></th>
													<? } else if($_GET['t'] == 2){ ?>
														<th field="SELECT" width="20px" sortable="true" >
															<input type="checkbox" id="CHECK_ALL" onclick="select_all(<?=$_GET['t']?>)" >
														</th>
														<th field="NAME" width="225px" align="left" sortable="true" ><?=NAME?></th>
														<th field="STUDENT_ID" width="150px" align="left" sortable="true" ><?=STUDENT_ID?></th>
														<th field="CAMPUS_CODE" width="100px" align="left" sortable="true" ><?=CAMPUS?></th>
														<th field="BEGIN_DATE" width="100px" align="left" sortable="true" ><?=FIRST_TERM_DATE_1?></th>
														<th field="PROGRAM" width="100px" align="left" sortable="true" ><?=PROGRAM?></th>
														<th field="SESSION" width="100px" align="left" sortable="true" ><?=SESSION?></th>
														<th field="STUDENT_STATUS" width="150px" align="left" sortable="true" ><?=STATUS?></th>
														<th field="STUDENT_GROUP" width="120px" align="left" sortable="true" ><?=STUDENT_GROUP?></th>
														<th field="APPROVE" width="100px" align="left" sortable="true" ><?=APPROVE?></th>
														<th field="EC" width="40px"  sortable="false" ><?=EC?></th>
													<? } else if($_GET['t'] == 3){ ?>
														<th field="SELECT" width="20px" sortable="true" >
															<input type="checkbox" id="CHECK_ALL" onclick="select_all(<?=$_GET['t']?>)" >
														</th>
														<th field="NAME" width="225px" align="left" sortable="true" ><?=NAME?></th>
														<th field="STUDENT_ID" width="100px" align="left" sortable="true" ><?=STUDENT_ID?></th>
														<th field="CAMPUS_CODE" width="100px" align="left" sortable="true" ><?=CAMPUS?></th>
														<th field="BEGIN_DATE" width="90px" align="left" sortable="true" ><?=FIRST_TERM_DATE_1?></th>
														<th field="FUNDING" width="100px" align="left" sortable="true" ><?=FUNDING?></th>
														<th field="PROGRAM" width="100px" align="left" sortable="true" ><?=PROGRAM?></th>
														<th field="SESSION" width="70px" align="left" sortable="true" ><?=SESSION?></th>
														
														<th field="STUDENT_STATUS" width="150px" align="left" sortable="true" ><?=STATUS?></th>
														<th field="STUDENT_GROUP" width="120px" align="left" sortable="true" ><?=STUDENT_GROUP?></th>
														<th field="BALANCE" width="100px" align="right" sortable="true" ><?=BALANCE?></th>
														<th field="EC" width="40px"  sortable="false" ><?=EC?></th>
													<? } else if($_GET['t'] == 3 || $_GET['t'] == 5){ ?>
														<th field="SELECT" width="20px" sortable="true" >
															<input type="checkbox" id="CHECK_ALL" onclick="select_all(<?=$_GET['t']?>)" >
														</th>
														<th field="NAME" width="225px" align="left" sortable="true" ><?=NAME?></th>
														<th field="STUDENT_ID" width="100px" align="left" sortable="true" ><?=STUDENT_ID?></th>
														<th field="CAMPUS_CODE" width="100px" align="left" sortable="true" ><?=CAMPUS?></th>
														<th field="BEGIN_DATE" width="90px" align="left" sortable="true" ><?=FIRST_TERM_DATE_1?></th>
														<th field="FUNDING" width="100px" align="left" sortable="true" ><?=FUNDING?></th>
														<th field="PROGRAM" width="100px" align="left" sortable="true" ><?=PROGRAM?></th>
														<th field="SESSION" width="70px" align="left" sortable="true" ><?=SESSION?></th>
														
														<th field="STUDENT_STATUS" width="150px" align="left" sortable="true" ><?=STATUS?></th>
														<th field="STUDENT_GROUP" width="120px" align="left" sortable="true" ><?=STUDENT_GROUP?></th>
														<th field="BALANCE" width="100px" align="right" sortable="true" ><?=BALANCE?></th>
														<th field="EC" width="40px"  sortable="false" ><?=EC?></th>
														
													<? } else if($_GET['t'] == 6){ ?>
														<th field="SELECT" width="20px" sortable="true" >
															<input type="checkbox" id="CHECK_ALL" onclick="select_all(<?=$_GET['t']?>)" >
														</th>
														<th field="NAME" width="225px" align="left" sortable="true" ><?=NAME?></th>
														<th field="STUDENT_ID" width="100px" align="left" sortable="true" ><?=STUDENT_ID?></th>
														<th field="CAMPUS_CODE" width="100px" align="left" sortable="true" ><?=CAMPUS?></th>
														<th field="BEGIN_DATE" width="90px" align="left" sortable="true" ><?=FIRST_TERM_DATE_1?></th>
														<th field="PROGRAM" width="100px" align="left" sortable="true" ><?=PROGRAM?></th>
														<th field="SESSION" width="70px" align="left" sortable="true" ><?=SESSION?></th>
														<th field="STUDENT_STATUS" width="130px" align="left" sortable="true" ><?=STATUS?></th>
														<th field="STUDENT_GROUP" width="120px" align="left" sortable="true" ><?=STUDENT_GROUP?></th>
														<th field="EMPLOYED_1" width="80px" align="left" sortable="true" ><?=EMPLOYED?></th>
														<th field="PLACEMENT_STATUS" width="130px" align="left" sortable="true" ><?=PLACEMENT_STATUS?></th>
														<th field="EC" width="40px"  sortable="false" ><?=EC?></th>
													<? } ?>
													<th field="ACTION" width="180px"  sortable="false" ><?=OPTIONS?></th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
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
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" ><?=NO?></button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=ASSIGN_REPRESENTATIVE?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<div class="col-12 col-sm-6 form-group">
								<select id="PK_REPRESENTATIVE" name="PK_REPRESENTATIVE" class="form-control" >
									<option value="" ><?=REPRESENTATIVE?></option>
									<? $res_type = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.ACTIVE = 1 AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = 2 AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND TURN_OFF_ASSIGNMENTS = 0 order by CONCAT(FIRST_NAME,' ',LAST_NAME) ASC");
									while (!$res_type->EOF) { ?>
										<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" ><?=$res_type->fields['NAME']?></option>
									<?	$res_type->MoveNext();
									} ?>
								</select>
							</div>
							<input type="hidden" id="ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_assign_rep(1)" class="btn waves-effect waves-light btn-info"><?=ASSIGN?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_assign_rep(0)" ><?=CANCEL?></button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=BULK_APPROVE?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<div class="col-12 col-sm-12 form-group">
								<?=APPROVE_CONFIRM?>
							</div>
							<input type="hidden" id="BULK_APPROVE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_approve_stu(1)" class="btn waves-effect waves-light btn-info"><?=APPROVE?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_approve_stu(0)" ><?=CANCEL?></button>
					</div>
				</div>
			</div>
		</div>
		
		<? require_once("show_enable_payment_popup.php") ?>
		
    </div>
	<? require_once("js.php"); ?>
	
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script type="text/javascript" src="../backend_assets/dist/js/jquery.easyui.min.js"></script>
	<script src="../backend_assets/dist/js/jquery-ui.js"></script> 
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});

	});
	
	// Ticket #1013
	function doSearch(){		
		show_bulk()
		
		jQuery(document).ready(function($) {
			$('#tt').datagrid('load',{
				SEARCH  			: $('#SEARCH').val(),
				SHOW_UNASSIGNED		: $('#SHOW_UNASSIGNED').is(":checked"),
				SHOW_ARCHIVED		: $('#SHOW_ARCHIVED').is(":checked"),
				SHOW_ENROLLED_ONLY	: $('#SHOW_ENROLLED_ONLY').is(":checked"),
				LEAD_START_DATE		: $('#LEAD_START_DATE').val(),
				LEAD_END_DATE		: $('#LEAD_END_DATE').val(),
				LDA_START_DATE		: $('#LDA_START_DATE').val(),
				LDA_END_DATE		: $('#LDA_END_DATE').val(),
				EMPLOYED 			: $('#EMPLOYED').val(),
				
				SHOW_MULTIPLE_ENROLLMENT	: $('#SHOW_MULTIPLE_ENROLLMENT').is(":checked"),
				EXPECTED_GRAD_START_DATE 	: $('#EXPECTED_GRAD_START_DATE').val(),
				EXPECTED_GRAD_END_DATE 		: $('#EXPECTED_GRAD_END_DATE').val(),
				GRAD_START_DATE 			: $('#GRAD_START_DATE').val(),
				GRAD_END_DATE 				: $('#GRAD_END_DATE').val(),
				SSN							: $('#SSN').val(),
				
				DETERMINATION_START_DATE	: $('#DETERMINATION_START_DATE').val(),
				DETERMINATION_END_DATE		: $('#DETERMINATION_END_DATE').val(),
				DROP_START_DATE				: $('#DROP_START_DATE').val(),
				DROP_END_DATE				: $('#DROP_END_DATE').val(),
				
				ID				: $('#ID').val(), //Ticket # 1824
				ID_TYPE			: $('#ID_TYPE').val(), //Ticket # 1824
			});
		});	
	}
	// Ticket #1013
	
	function search(e){
		if (e.keyCode == 13) {
			doSearch();
		}
	}
	$(function(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid({
				onClickCell: function(rowIndex, field, value){
					$('#tt').datagrid('selectRow',rowIndex);
					if(field != 'ACTION' && field != 'REPRESENTATIVE' && field != 'SELECT' && field != 'APPROVE' && field != 'BULK_DELETE' ){
						var selected_row = $('#tt').datagrid('getSelected');
						var tab = ''
						if($('#SHOW_MULTIPLE_ENROLLMENT').is(":checked") == true)
							tab = '&tab=otherTab'
						window.location.href='student?id='+selected_row.PK_STUDENT_MASTER+'&eid='+selected_row.PK_STUDENT_ENROLLMENT+'&t=<?=$_GET['t']?>'+tab;
					}
				}
			});
			
			$('#tt').datagrid({
				view: $.extend(true,{},$.fn.datagrid.defaults.view,{
					onAfterRender: function(target){
						$.fn.datagrid.defaults.view.onAfterRender.call(this,target);
						$('.datagrid-header-inner').width('100%') 
						$('.datagrid-btable').width('100%') 
						$('.datagrid-body').css({'overflow-y': 'hidden'});
						show_bulk()
					}
				})
			});

		});
	});
	jQuery(document).ready(function($) {
		$(window).resize(function() {
			$('#tt').datagrid('resize');
			$('#tb').panel('resize');
		}) 
	});
	function delete_row(id){
		jQuery(document).ready(function($) {
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
		});
	}
	function conf_delete(val,id){
		if(val == 1)
			window.location.href = 'manage_student?act=del&t=<?=$_GET['t']?>&id='+$("#DELETE_ID").val();
		else
			$("#deleteModal").modal("hide");
	}
	
	function select_all(type){
		
		var str = '';
		if(document.getElementById('CHECK_ALL').checked == true) {
			str = true;
		} else {
			str = false;
		}
		
		var CHK_PK_STUDENT_MASTER = document.getElementsByName('CHK_PK_STUDENT_MASTER[]')
		for(var i = 0 ; i < CHK_PK_STUDENT_MASTER.length ; i++){
			CHK_PK_STUDENT_MASTER[i].checked = str
		}
		
		show_bulk()
	}
	
	/* Ticket # 1180 */
	function show_bulk(){
		var flag = 0;
		var CHK_PK_STUDENT_MASTER = document.getElementsByName('CHK_PK_STUDENT_MASTER[]')
		for(var i = 0 ; i < CHK_PK_STUDENT_MASTER.length ; i++){
			if(CHK_PK_STUDENT_MASTER[i].checked == true) {
				flag = 1;
				break
			}
		}
		
		if(document.getElementById('BULK_UPDATE_LINK'))
			document.getElementById('BULK_UPDATE_LINK').setAttribute('style', 'display:none !important');
		
		if(flag == 1){
			if(document.getElementById('BULK_UPDATE_LINK'))
				document.getElementById('BULK_UPDATE_LINK').setAttribute('style', 'display:block !important');
		}	
	}
	
	function bulk_update_popup(){
		var w = 700;
		var h = 550;
		// var id = common_id;
		var left = (screen.width/2)-(w/2);
		var top = (screen.height/2)-(h/2);
		var ids = '';
		var CHK_PK_STUDENT_MASTER = document.getElementsByName('CHK_PK_STUDENT_MASTER[]')
		for(var i = 0 ; i < CHK_PK_STUDENT_MASTER.length ; i++){
			if(CHK_PK_STUDENT_MASTER[i].checked == true){
				if(ids != '')
					ids += ','
				ids += CHK_PK_STUDENT_MASTER[i].value
			}
		}
		
		var SHOW_ARCHIVED = 0
		if(document.getElementById('SHOW_ARCHIVED'))
			if(document.getElementById('SHOW_ARCHIVED').checked == true)
				SHOW_ARCHIVED = 1
				
		var SHOW_ENROLLED_ONLY = 0
		if(document.getElementById('SHOW_ENROLLED_ONLY'))
			if(document.getElementById('SHOW_ENROLLED_ONLY').checked == true)
				SHOW_ENROLLED_ONLY = 1

		if(ids != '') {
			var parameter = 'toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,width='+w+', height='+h+', top='+top+', left='+left;
			window.open('student_update?id='+ids+'&t=<?=$_GET['t']?>&SHOW_ARCHIVED='+SHOW_ARCHIVED+'&SHOW_ENROLLED_ONLY='+SHOW_ENROLLED_ONLY,'',parameter);
			return false;
		}
	}
	
	/* Ticket # 1180 */
	
	function approve_stu(id){
		jQuery(document).ready(function($) {
			$("#approveModal").modal()
			$("#BULK_APPROVE_ID").val(id)
		});
	}
	function conf_approve_stu(val,id){
		if(val == 1)
			window.location.href = 'manage_student?act=approve&t=<?=$_GET['t']?>&id='+$("#BULK_APPROVE_ID").val();
		else
			$("#approveModal").modal("hide");
	}
	
	function assign_rep(id){
		jQuery(document).ready(function($) {
			$("#assignModal").modal()
			$("#ID").val(id)
		});
	}
	function conf_assign_rep(val,id){
		if(val == 1)
			window.location.href = 'manage_student?act=assign&t=<?=$_GET['t']?>&e='+$("#PK_REPRESENTATIVE").val()+'&id='+$("#ID").val();
		else
			$("#assignModal").modal("hide");
	}
	
	function consolidate(sid,eid){
		var w = 1200;
   		var h = 550;
   		// var id = common_id;
		var left = (screen.width/2)-(w/2);
		var top = (screen.height/2)-(h/2);
   		var parameter = 'toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,width='+w+', height='+h+', top='+top+', left='+left;
   		window.open('consolidate_students?sid='+sid+'&eid='+eid,'',parameter);
   		return false;
	}
	
	function refresh_win(win){
		win.close();
		doSearch()
	}
	
	function get_status(type){
		jQuery(document).ready(function($) {
			var q = "";
			if(type == 1) {
				var SHOW_LEAD = '';
				if(document.getElementById('SHOW_LEAD').checked == true)
					SHOW_LEAD = 1;
				else
					SHOW_LEAD = 0;
					
				q = '&SHOW_LEAD='+SHOW_LEAD
			} else {
				if(document.getElementById('SEARCH_PAST_STUDENT').checked == true)
					do_not_show_admission = 1;
				else
					do_not_show_admission = 0;
					
				q = '&do_not_show_admission='+do_not_show_admission
			}
				
			var data  = "btn=1";
			var value = $.ajax({
				url: "select_filter?type=stu_sts&close=1&t=<?=$_GET['t']?>"+q,	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					//alert(data)
					doSearch();
					document.getElementById('stu_sts_LBL').innerHTML = ' ('+data+' Selected)'
				}		
			}).responseText;
				
		});
		
	}

	//Ticket #1054
	function show_data_view(){
		var q = "?t=<?=$_GET['t']?>"
		if(document.getElementById('SHOW_ARCHIVED')) {
			if(document.getElementById('SHOW_ARCHIVED').checked == true)
				q += '&arch=1';
		}
		q += "&me="+$('#SHOW_MULTIPLE_ENROLLMENT').is(":checked")
		window.location.href = "student_data_view"+q;
	}
	
	</script>

	<script type="text/javascript">
		//var newsletterSubscriberFormDetail = new VarienForm('form1');
		
		function frm_submit(val){
			document.getElementById('TYPE').value = val;
			document.form1.submit();
		}
		
	</script>

	<script type="text/javascript">
		function select_filter(type){
			var q = '';
			if(type == 'stu_sts'){
				if(document.getElementById('SEARCH_PAST_STUDENT')){
					var do_not_show_admission = '';
					if(document.getElementById('SEARCH_PAST_STUDENT').checked == true)
						do_not_show_admission = 1;
					else
						do_not_show_admission = 0;
						
					q 	+= '&do_not_show_admission='+do_not_show_admission
				} else if(document.getElementById('SHOW_LEAD')){
					var SHOW_LEAD = '';
					if(document.getElementById('SHOW_LEAD').checked == true)
						SHOW_LEAD = 1;
					else
						SHOW_LEAD = 0;
						
					q 	+= '&SHOW_LEAD='+SHOW_LEAD
				} 
			}
			
			var w = 1200;
			var h = 550;
			// var id = common_id;
			var left = (screen.width/2)-(w/2);
			var top = (screen.height/2)-(h/2);
			var parameter = 'toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,width='+w+', height='+h+', top='+top+', left='+left;
			window.open('select_filter?t=<?=$_GET['t']?>&type='+type+q,'',parameter);
			return false;
		}
		function doSearch_1(win,count,type){
			win.close();
			doSearch()
			document.getElementById(type+'_LBL').innerHTML = ' ('+count+' Selected)'
		}
		
		$(window).scroll(function() {
			if ($(window).scrollTop() >= 250) {
				$('.datagrid-header').addClass('fixed');
			} else {
				$('.datagrid-header').removeClass('fixed');
			}
		});
	</script>	
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#ID_TYPE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Selected',
			nonSelectedText: 'ID Type',
			numberDisplayed: 0,
			nSelectedText: ' Selected'
		});
	});
	</script>	
	
	
</body>

</html>