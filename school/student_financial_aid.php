<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("get_department_from_t.php");
require_once("check_access.php");

$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
if($FINANCE_ACCESS != 2 && $FINANCE_ACCESS != 3){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$STUDENT_FINANCIAL['ACADEMIC_YEAR'] 			= $_POST['ACADEMIC_YEAR_1'];
	$STUDENT_FINANCIAL['PROGRAM_LENGTH'] 			= $_POST['PROGRAM_LENGTH'];
	$STUDENT_FINANCIAL['PROGRAM_COST'] 				= $_POST['PROGRAM_COST'];
	$STUDENT_FINANCIAL['UPDATED'] 					= $_POST['UPDATED'];
	$STUDENT_FINANCIAL['REPACKAGE_DATE'] 			= $_POST['REPACKAGE_DATE'];
	$STUDENT_FINANCIAL['NEED'] 						= $_POST['NEED'];
	$STUDENT_FINANCIAL['COA'] 						= $_POST['COA'];
	$STUDENT_FINANCIAL['EFC_NO'] 					= $_POST['EFC_NO'];
	$STUDENT_FINANCIAL['AUTOMATIC_ZERO_EFC'] 		= $_POST['AUTOMATIC_ZERO_EFC'];
	$STUDENT_FINANCIAL['YEAR_ROUND_PELL'] 			= $_POST['YEAR_ROUND_PELL'];
	$STUDENT_FINANCIAL['VA_STUDENT'] 				= $_POST['VA_STUDENT'];
	$STUDENT_FINANCIAL['ELIGIBLE_CITIZEN'] 			= $_POST['ELIGIBLE_CITIZEN'];
	$STUDENT_FINANCIAL['SELECTED_FOR_VERIFICATION'] = $_POST['SELECTED_FOR_VERIFICATION'];
	$STUDENT_FINANCIAL['PK_DEPENDENT_STATUS'] 		= $_POST['PK_DEPENDENT_STATUS'];
	$STUDENT_FINANCIAL['IS_FOREIGN'] 				= $_POST['IS_FOREIGN'];
	$STUDENT_FINANCIAL['OVERRIDE'] 					= $_POST['OVERRIDE'];
	$STUDENT_FINANCIAL['PROFESSIONAL_JUDGEMENT'] 	= $_POST['PROFESSIONAL_JUDGEMENT'];
	$STUDENT_FINANCIAL['NO_OF_DEPENDENTS'] 			= $_POST['NO_OF_DEPENDENTS'];
	$STUDENT_FINANCIAL['DEPENDENTS_IN_COLLEGE'] 	= $_POST['DEPENDENTS_IN_COLLEGE'];
	$STUDENT_FINANCIAL['STUDENT_INCOME'] 			= $_POST['STUDENT_INCOME'];
	$STUDENT_FINANCIAL['PARENT_INCOME'] 			= $_POST['PARENT_INCOME'];
	$STUDENT_FINANCIAL['STUDENT_CONTRIBUTION'] 		= $_POST['STUDENT_CONTRIBUTION'];
	$STUDENT_FINANCIAL['PARENT_CONTRIBUTION'] 		= $_POST['PARENT_CONTRIBUTION'];
	$STUDENT_FINANCIAL['INCOME_LEVEL'] 				= $_POST['INCOME_LEVEL'];
	$STUDENT_FINANCIAL['I551N0'] 					= $_POST['I551N0'];
	$STUDENT_FINANCIAL['PK_MARITAL_STATUS'] 		= $_POST['PK_MARITAL_STATUS'];
	$STUDENT_FINANCIAL['MARITAL_STATUS_DATE'] 		= $_POST['MARITAL_STATUS_DATE'];
	$STUDENT_FINANCIAL['ISIR_PROCESSED_DATE'] 		= $_POST['ISIR_PROCESSED_DATE'];
	$STUDENT_FINANCIAL['ISIR_SIGNED_DATE'] 			= $_POST['ISIR_SIGNED_DATE'];
	$STUDENT_FINANCIAL['ISIR_TRANS_NO'] 			= $_POST['ISIR_TRANS_NO'];
	$STUDENT_FINANCIAL['ISIR_CLEAR_PAY'] 			= $_POST['ISIR_CLEAR_PAY'];
	$STUDENT_FINANCIAL['PK_AWARD_YEAR'] 			= $_POST['PK_AWARD_YEAR'];
	$STUDENT_FINANCIAL['ACADEMIC_YEAR_BEGIN'] 		= $_POST['ACADEMIC_YEAR_BEGIN'];
	$STUDENT_FINANCIAL['ACADEMIC_YEAR_END'] 		= $_POST['ACADEMIC_YEAR_END'];
	$STUDENT_FINANCIAL['SCHOOL_CODE'] 				= $_POST['SCHOOL_CODE'];
	$STUDENT_FINANCIAL['AY_MONTH'] 					= $_POST['AY_MONTH'];

	if($STUDENT_FINANCIAL['REPACKAGE_DATE'] != '')
		$STUDENT_FINANCIAL['REPACKAGE_DATE'] = date("Y-m-d",strtotime($STUDENT_FINANCIAL['REPACKAGE_DATE']));
	
	if($STUDENT_FINANCIAL['MARITAL_STATUS_DATE'] != '')
		$STUDENT_FINANCIAL['MARITAL_STATUS_DATE'] = date("Y-m-d",strtotime($STUDENT_FINANCIAL['MARITAL_STATUS_DATE']));
		
	if($STUDENT_FINANCIAL['ISIR_PROCESSED_DATE'] != '')
		$STUDENT_FINANCIAL['ISIR_PROCESSED_DATE'] = date("Y-m-d",strtotime($STUDENT_FINANCIAL['ISIR_PROCESSED_DATE']));
		
	if($STUDENT_FINANCIAL['ISIR_SIGNED_DATE'] != '')
		$STUDENT_FINANCIAL['ISIR_SIGNED_DATE'] = date("Y-m-d",strtotime($STUDENT_FINANCIAL['ISIR_SIGNED_DATE']));	
		
	if($STUDENT_FINANCIAL['ACADEMIC_YEAR_BEGIN'] != '')
		$STUDENT_FINANCIAL['ACADEMIC_YEAR_BEGIN'] = date("Y-m-d",strtotime($STUDENT_FINANCIAL['ACADEMIC_YEAR_BEGIN']));
		
	if($STUDENT_FINANCIAL['ACADEMIC_YEAR_END'] != '')
		$STUDENT_FINANCIAL['ACADEMIC_YEAR_END'] = date("Y-m-d",strtotime($STUDENT_FINANCIAL['ACADEMIC_YEAR_END']));
		
	if($_GET['id'] == ''){
		$STUDENT_FINANCIAL['PK_STUDENT_ENROLLMENT']  = $_GET['eid'];
		$STUDENT_FINANCIAL['PK_STUDENT_MASTER'] 	 = $_GET['sid'];
		$STUDENT_FINANCIAL['PK_ACCOUNT'] 			 = $_SESSION['PK_ACCOUNT'];
		$STUDENT_FINANCIAL['CREATED_BY']  			 = $_SESSION['PK_USER'];
		$STUDENT_FINANCIAL['CREATED_ON']  			 = date("Y-m-d H:i");
		db_perform('S_STUDENT_FINANCIAL', $STUDENT_FINANCIAL, 'insert');
		$PK_STUDENT_FINANCIAL = $db->insert_ID();
	} else {
		$STUDENT_FINANCIAL['EDITED_BY']  = $_SESSION['PK_USER'];
		$STUDENT_FINANCIAL['EDITED_ON']  = date("Y-m-d H:i");
		db_perform('S_STUDENT_FINANCIAL', $STUDENT_FINANCIAL, 'update'," PK_STUDENT_FINANCIAL = '$_GET[id]' ");
		$PK_STUDENT_FINANCIAL = $_GET['id'];
	}
	
	
	$i = 0;
	foreach($_POST['PK_STUDENT_FINANCIAL_ACADEMY'] as $PK_STUDENT_FINANCIAL_ACADEMY){

		$STUDENT_FINANCIAL_ACADEMY  = array();
		$STUDENT_FINANCIAL_ACADEMY['PERIOD'] 		= $_POST['PERIOD'][$i];
		$STUDENT_FINANCIAL_ACADEMY['PERIOD_BEGIN'] 	= $_POST['PERIOD_BEGIN'][$i];
		$STUDENT_FINANCIAL_ACADEMY['PERIOD_END'] 	= $_POST['PERIOD_END'][$i];
		
		if($STUDENT_FINANCIAL_ACADEMY['PERIOD_BEGIN'] != '')
			$STUDENT_FINANCIAL_ACADEMY['PERIOD_BEGIN'] = date("Y-m-d",strtotime($STUDENT_FINANCIAL_ACADEMY['PERIOD_BEGIN']));
			
		if($STUDENT_FINANCIAL_ACADEMY['PERIOD_END'] != '')
			$STUDENT_FINANCIAL_ACADEMY['PERIOD_END'] = date("Y-m-d",strtotime($STUDENT_FINANCIAL_ACADEMY['PERIOD_END']));
		
		if($PK_STUDENT_FINANCIAL_ACADEMY == '') {
			$STUDENT_FINANCIAL_ACADEMY['PK_STUDENT_FINANCIAL']  = $PK_STUDENT_FINANCIAL;
			$STUDENT_FINANCIAL_ACADEMY['PK_STUDENT_MASTER'] 	= $_GET['sid'];
			$STUDENT_FINANCIAL_ACADEMY['PK_STUDENT_ENROLLMENT'] = $_GET['eid'];
			$STUDENT_FINANCIAL_ACADEMY['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
			$STUDENT_FINANCIAL_ACADEMY['CREATED_BY']  			= $_SESSION['PK_USER'];
			$STUDENT_FINANCIAL_ACADEMY['CREATED_ON']  			= date("Y-m-d H:i");
			db_perform('S_STUDENT_FINANCIAL_ACADEMY', $STUDENT_FINANCIAL_ACADEMY, 'insert');
			$PK_STUDENT_FINANCIAL_ACADEMY_ARR[] = $db->insert_ID();
		} else {
			$STUDENT_FINANCIAL_ACADEMY['EDITED_BY']  = $_SESSION['PK_USER'];
			$STUDENT_FINANCIAL_ACADEMY['EDITED_ON']  = date("Y-m-d H:i");
			db_perform('S_STUDENT_FINANCIAL_ACADEMY', $STUDENT_FINANCIAL_ACADEMY, 'update'," PK_STUDENT_FINANCIAL_ACADEMY = '$PK_STUDENT_FINANCIAL_ACADEMY' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_FINANCIAL = '$PK_STUDENT_FINANCIAL' ");
			$PK_STUDENT_FINANCIAL_ACADEMY_ARR[] = $PK_STUDENT_FINANCIAL_ACADEMY;
		}
		
		$i++;
	}
	
	$cond = "";
	if(!empty($PK_STUDENT_FINANCIAL_ACADEMY_ARR))
		$cond = " AND PK_STUDENT_FINANCIAL_ACADEMY NOT IN (".implode(",",$PK_STUDENT_FINANCIAL_ACADEMY_ARR).") ";
	
	$db->Execute("DELETE FROM S_STUDENT_FINANCIAL_ACADEMY WHERE PK_STUDENT_MASTER = '$_GET[sid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND PK_STUDENT_FINANCIAL = '$PK_STUDENT_FINANCIAL' $cond "); 

	/*$res_fin = $db->Execute("select PK_STUDENT_FINANCIAL_ACADEMY FROM S_STUDENT_FINANCIAL_ACADEMY WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if($res_fin->RecordCount() == 0){
		$STUDENT_FINANCIAL_ACADEMY['PK_STUDENT_ENROLLMENT']  = $_GET['eid'];
		$STUDENT_FINANCIAL_ACADEMY['PK_STUDENT_MASTER'] 	 = $PK_STUDENT_MASTER;
		$STUDENT_FINANCIAL_ACADEMY['PK_ACCOUNT'] 			 = $_SESSION['PK_ACCOUNT'];
		$STUDENT_FINANCIAL_ACADEMY['CREATED_BY']  			 = $_SESSION['PK_USER'];
		$STUDENT_FINANCIAL_ACADEMY['CREATED_ON']  			 = date("Y-m-d H:i");
		db_perform('S_STUDENT_FINANCIAL_ACADEMY', $STUDENT_FINANCIAL_ACADEMY, 'insert');
	} else {
		$STUDENT_FINANCIAL_ACADEMY['EDITED_BY']  = $_SESSION['PK_USER'];
		$STUDENT_FINANCIAL_ACADEMY['EDITED_ON']  = date("Y-m-d H:i");
		db_perform('S_STUDENT_FINANCIAL_ACADEMY', $STUDENT_FINANCIAL_ACADEMY, 'update'," PK_STUDENT_FINANCIAL_ACADEMY = '".$res_fin->fields['PK_STUDENT_FINANCIAL_ACADEMY']."' ");
	}*/
	header("location:student?id=".$_GET['sid'].'&tab=financialAidTab&eid='.$_GET['eid'].'&t='.$_GET['t']);
}
if($_GET['id'] == ''){
	$res = $db->Execute("SELECT PK_CAMPUS_PROGRAM FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND PK_STUDENT_MASTER = '$_GET[sid]' "); 
	$PK_CAMPUS_PROGRAM = $res->fields['PK_CAMPUS_PROGRAM'];
	
	$res_prog = $db->Execute("SELECT MONTHS,SUM(AMOUNT) AS AMOUNT FROM M_CAMPUS_PROGRAM LEFT JOIN M_CAMPUS_PROGRAM_FEE ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = M_CAMPUS_PROGRAM_FEE.PK_CAMPUS_PROGRAM AND M_CAMPUS_PROGRAM_FEE.ACTIVE = 1 WHERE M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' ");
										
	$PROGRAM_LENGTH				= $res_prog->fields['MONTHS'];
	$PROGRAM_COST				= $res_prog->fields['AMOUNT'];
	
	$SCHOOL_CODE				= '';
	$UPDATED					= '';
	$REPACKAGE_DATE				= '';
	$NEED						= '';
	$COA						= '';
	$EFC_NO						= '';
	$AUTOMATIC_ZERO_EFC			= 2;
	$YEAR_ROUND_PELL 			= 2;
	$VA_STUDENT 				= 2;
	$ELIGIBLE_CITIZEN 			= 2;
	$SELECTED_FOR_VERIFICATION 	= 2;
	$PK_DEPENDENT_STATUS		= '';
	$IS_FOREIGN 				= 2;
	$OVERRIDE					= '';
	$PROFESSIONAL_JUDGEMENT 	= 2;
	$NO_OF_DEPENDENTS			= '';
	$DEPENDENTS_IN_COLLEGE		= '';
	$STUDENT_INCOME				= '';
	$PARENT_INCOME				= '';
	$STUDENT_CONTRIBUTION		= '';
	$PARENT_CONTRIBUTION		= '';
	$INCOME_LEVEL				= '';
	$I551N0						= '';
	$PK_MARITAL_STATUS			= '';
	$MARITAL_STATUS_DATE		= '';
	$ISIR_PROCESSED_DATE		= '';
	$ISIR_SIGNED_DATE			= '';
	$ISIR_TRANS_NO				= '';
	$ISIR_CLEAR_PAY 			= 2;
	$PREVIOUS_COLLEGE 			= 2;
	$ACADEMIC_YEAR_BEGIN		= '';
	$ACADEMIC_YEAR_END			= '';
	$ACADEMIC_YEAR				= '';

	$PK_AWARD_YEAR			= '';
	$ACADEMIC_YEAR_BEGIN	= '';
	$ACADEMIC_YEAR_END		= '';
	$AY_MONTH				= '';
} else {
	$cond = "";
	
	$res_fin = $db->Execute("select * FROM S_STUDENT_FINANCIAL WHERE PK_STUDENT_MASTER = '$_GET[sid]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_FINANCIAL = '$_GET[id]' ");
	
	if($res_fin->RecordCount() == 0){
		header("location:student?id=".$_GET['sid'].'&tab=financialAidTab&t='.$_GET['t'].'&eid='.$_GET['eid']);
		exit;
	}
	
	$SCHOOL_CODE				= $res_fin->fields['SCHOOL_CODE'];
	$PROGRAM_LENGTH				= $res_fin->fields['PROGRAM_LENGTH'];
	$PROGRAM_COST				= $res_fin->fields['PROGRAM_COST'];
	$UPDATED					= $res_fin->fields['UPDATED'];
	$REPACKAGE_DATE				= $res_fin->fields['REPACKAGE_DATE'];
	$NEED						= $res_fin->fields['NEED'];
	$COA						= $res_fin->fields['COA'];
	$EFC_NO						= $res_fin->fields['EFC_NO'];
	$AUTOMATIC_ZERO_EFC			= $res_fin->fields['AUTOMATIC_ZERO_EFC'];
	$YEAR_ROUND_PELL 			= $res_fin->fields['YEAR_ROUND_PELL'];
	$VA_STUDENT 				= $res_fin->fields['VA_STUDENT'];
	$ELIGIBLE_CITIZEN 			= $res_fin->fields['ELIGIBLE_CITIZEN'];
	$SELECTED_FOR_VERIFICATION 	= $res_fin->fields['SELECTED_FOR_VERIFICATION'];
	$PK_DEPENDENT_STATUS		= $res_fin->fields['PK_DEPENDENT_STATUS'];
	$IS_FOREIGN 				= $res_fin->fields['IS_FOREIGN'];
	$OVERRIDE					= $res_fin->fields['OVERRIDE'];
	$PROFESSIONAL_JUDGEMENT 	= $res_fin->fields['PROFESSIONAL_JUDGEMENT'];
	$NO_OF_DEPENDENTS			= $res_fin->fields['NO_OF_DEPENDENTS'];
	$DEPENDENTS_IN_COLLEGE		= $res_fin->fields['DEPENDENTS_IN_COLLEGE'];
	$STUDENT_INCOME				= $res_fin->fields['STUDENT_INCOME'];
	$PARENT_INCOME				= $res_fin->fields['PARENT_INCOME'];
	$STUDENT_CONTRIBUTION		= $res_fin->fields['STUDENT_CONTRIBUTION'];
	$PARENT_CONTRIBUTION		= $res_fin->fields['PARENT_CONTRIBUTION'];
	$INCOME_LEVEL				= $res_fin->fields['INCOME_LEVEL'];
	$I551N0						= $res_fin->fields['I551N0'];
	$PK_MARITAL_STATUS			= $res_fin->fields['PK_MARITAL_STATUS'];
	$MARITAL_STATUS_DATE		= $res_fin->fields['MARITAL_STATUS_DATE'];
	$ISIR_PROCESSED_DATE		= $res_fin->fields['ISIR_PROCESSED_DATE'];
	$ISIR_SIGNED_DATE			= $res_fin->fields['ISIR_SIGNED_DATE'];
	$ISIR_TRANS_NO				= $res_fin->fields['ISIR_TRANS_NO'];
	$ISIR_CLEAR_PAY 			= $res_fin->fields['ISIR_CLEAR_PAY'];
	$PK_AWARD_YEAR				= $res_fin->fields['PK_AWARD_YEAR'];
	$ACADEMIC_YEAR_BEGIN		= $res_fin->fields['ACADEMIC_YEAR_BEGIN'];
	$ACADEMIC_YEAR_END			= $res_fin->fields['ACADEMIC_YEAR_END'];
	$AY_MONTH					= $res_fin->fields['AY_MONTH'];
	$ACADEMIC_YEAR				= $res_fin->fields['ACADEMIC_YEAR'];
	
	if($REPACKAGE_DATE == '0000-00-00')
		$REPACKAGE_DATE = '';
	else
		$REPACKAGE_DATE = date("m/d/Y",strtotime($REPACKAGE_DATE));
		
	if($MARITAL_STATUS_DATE == '0000-00-00')
		$MARITAL_STATUS_DATE = '';
	else
		$MARITAL_STATUS_DATE = date("m/d/Y",strtotime($MARITAL_STATUS_DATE));
		
	if($ISIR_PROCESSED_DATE == '0000-00-00')
		$ISIR_PROCESSED_DATE = '';
	else
		$ISIR_PROCESSED_DATE = date("m/d/Y",strtotime($ISIR_PROCESSED_DATE));
		
	if($ISIR_SIGNED_DATE == '0000-00-00')
		$ISIR_SIGNED_DATE = '';
	else
		$ISIR_SIGNED_DATE = date("m/d/Y",strtotime($ISIR_SIGNED_DATE));
		
	if($ACADEMIC_YEAR_BEGIN == '0000-00-00')
		$ACADEMIC_YEAR_BEGIN = '';
	else
		$ACADEMIC_YEAR_BEGIN = date("m/d/Y",strtotime($ACADEMIC_YEAR_BEGIN));
		
	if($ACADEMIC_YEAR_END == '0000-00-00')
		$ACADEMIC_YEAR_END = '';
	else
		$ACADEMIC_YEAR_END = date("m/d/Y",strtotime($ACADEMIC_YEAR_END));
	
}

$res = $db->Execute("SELECT IMAGE,FIRST_NAME,LAST_NAME,MIDDLE_NAME,OTHER_NAME FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$_GET[sid]' AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
$IMAGE					= $res->fields['IMAGE'];
$FIRST_NAME 			= $res->fields['FIRST_NAME'];
$LAST_NAME 				= $res->fields['LAST_NAME'];
$MIDDLE_NAME	 		= $res->fields['MIDDLE_NAME'];
$OTHER_NAME	 			= $res->fields['OTHER_NAME'];

$res = $db->Execute("SELECT STATUS_DATE,STUDENT_STATUS,CODE,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_MASTER = '$_GET[sid]' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' "); 
$STATUS_DATE 	 = $res->fields['STATUS_DATE'];
$STUDENT_STATUS	 = $res->fields['STUDENT_STATUS'];
$CAMPUS_PROGRAM  = $res->fields['CODE'];
$FIRST_TERM_DATE = $res->fields['BEGIN_DATE_1'];

if($STATUS_DATE != '0000-00-00')
	$STATUS_DATE = date("m/d/Y",strtotime($STATUS_DATE));
else
	$STATUS_DATE = '';

$has_warning_notes 	= 0;
$warning_notes 		= '';

$res_note = $db->Execute("select NOTES,DEPARTMENT FROM S_STUDENT_NOTES LEFT JOIN M_DEPARTMENT ON M_DEPARTMENT.PK_DEPARTMENT = S_STUDENT_NOTES.PK_DEPARTMENT, M_NOTE_TYPE WHERE PK_NOTE_TYPE_MASTER = 1 AND S_STUDENT_NOTES.PK_NOTE_TYPE = M_NOTE_TYPE.PK_NOTE_TYPE AND S_STUDENT_NOTES.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_NOTES.PK_STUDENT_MASTER = '$_GET[sid]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND SATISFIED = 0 ");
	
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
$res_probation = $db->Execute("select PK_STUDENT_PROBATION FROM S_STUDENT_PROBATION WHERE PK_PROBATION_STATUS = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$_GET[sid]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' ");
if($res_probation->RecordCount() > 0) {
	$has_warning_notes = 1;
	if($warning_notes != '')
		$warning_notes .= '<br />';
		
	$warning_notes .= 'On Probation';
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
	<title><?=FINANCIAL_PAGE_TITLE?> | <?=$title?></title>
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
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles" <? if($has_warning_notes == 1){ ?> style="background-color: #d12323 !important;color: #fff;" <? } ?> >
                    <div class="col-md-3 align-self-center">
                        <h4 class="text-themecolor" <? if($has_warning_notes == 1){ ?> style="color: #fff;" <? } ?> ><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=FINANCIAL_PAGE_TITLE?> </h4>
                    </div>
					<div class="col-md-1 align-self-center" >
						<? if($IMAGE != '') { ?>
							<div class="row el-element-overlay">
								<div class="card" style="margin-bottom: 0;" >
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
							<!--<img src="<?=$IMAGE?>" style="height: 80px;" />-->
						<? } ?>
					</div>
					<div class="col-md-3 align-self-center">
						<?=$FIRST_NAME.' '.$MIDDLE_NAME.' '.$LAST_NAME?><br />
						<? if($STATUS_DATE != '') echo $STATUS_DATE.'<br />'; ?>
						<? if($STUDENT_STATUS != '') echo $STUDENT_STATUS.'<br />'; ?>
						<? if($CAMPUS_PROGRAM != '') echo $CAMPUS_PROGRAM.' - '.$FIRST_TERM_DATE; ?>
					</div>
					<div class="col-md-5 align-self-center">
						<?=$warning_notes?>
					</div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
									<div class="row">  
										<div class="col-sm-4"> 
											
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<select id="ACADEMIC_YEAR_1" name="ACADEMIC_YEAR_1" class="form-control">
														<option>Select</option>
														<? for($i = 1; $i <= 12 ; $i++){ ?>
															<option value="<?=$i?>" <? if($ACADEMIC_YEAR == $i) echo "selected"; ?> ><?=$i?></option>
														<? } ?>
													</select>
													<label for="ACADEMIC_YEAR_1"><?=ACADEMIC_YEAR_1?></label> 
												</div>
											
												<div class="col-12 col-sm-6 form-group"> 
													<select id="PK_AWARD_YEAR" name="PK_AWARD_YEAR" placeholder="Select" class="form-control">
														<option>Select</option>
														<? $res_type = $db->Execute("select PK_AWARD_YEAR,AWARD_YEAR from M_AWARD_YEAR WHERE ACTIVE = 1 order by PK_AWARD_YEAR DESC");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_AWARD_YEAR']?>" <? if($PK_AWARD_YEAR == $res_type->fields['PK_AWARD_YEAR']) echo "selected"; ?> ><?=$res_type->fields['AWARD_YEAR']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
													<label for="PK_AWARD_YEAR"><?=AWARD_YEAR?></label> 
													<input type="hidden" id="HAS_FINANCIAL_FORM" name="HAS_FINANCIAL_FORM" value="1" />
												</div>
											</div>
											<div class="row">
												<div class="col-sm-12 ">
													<div class="d-flex theme-h-border"></div>
												</div>
											</div>
											<br />
											<div class="d-flex">   
												<div class="row">
													<div class="row">
														<div class="d-flex pd-2 mb-3" style="text-align: center;width:25rem;">  
														<div class="col-12 col-sm-4 form-group" style="text-align: left;"> 
															<label for="ACADEMIC"><?=ACADEMIC?></label>
														</div>			
														<div class="col-12 col-sm-4 form-group"> 
															<label for="ACADEMIC_YEAR_BEGIN"><?=ACADEMIC_YEAR_BEGIN?></label>
														</div>
														<div class="col-12 col-sm-4 form-group"> 
															<label for="ACADEMIC_YEAR_END"><?=ACADEMIC_YEAR_END?></label>						
														</div>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-12 col-sm-4 form-group"> 
															<label for="ACADEMIC_YEAR"><?=ACADEMIC_YEAR?></label>
														</div>			
														<div class="col-12 col-sm-4 form-group"> 
															<input id="ACADEMIC_YEAR_BEGIN" name="ACADEMIC_YEAR_BEGIN" value="<?=$ACADEMIC_YEAR_BEGIN?>" type="text" class="form-control date1" />
														</div>
														<div class="col-12 col-sm-4 form-group"> 
															<input id="ACADEMIC_YEAR_END" name="ACADEMIC_YEAR_END" value="<?=$ACADEMIC_YEAR_END?>" type="text" class="form-control date2" />
														</div>
													</div>
													
													<div class="row " style="width:100%">
														<div class="col-12 col-sm-4"> 
															<label for="PERIOD"><?=PERIOD?></label>
														</div>
														<div class="col-12 col-sm-4"> 
															<a href="javascript:void(0)" onclick="add_period()" ><i class="fa fa-plus-circle"></i></a>
														</div>
													</div>
													<div id="period_div" >
														<? $period_count = 1;
														$res = $db->Execute("select PK_STUDENT_FINANCIAL_ACADEMY from S_STUDENT_FINANCIAL_ACADEMY WHERE PK_STUDENT_MASTER = '$_GET[sid]' AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
														while (!$res->EOF) { 
															$_REQUEST['period_count'] 					= $period_count;
															$_REQUEST['PK_STUDENT_FINANCIAL_ACADEMY'] 	= $res->fields['PK_STUDENT_FINANCIAL_ACADEMY'];
															$_REQUEST['eid'] 							= $_GET['eid'];
															$_REQUEST['sid'] 							= $_GET['sid'];
															
															include("ajax_student_fa_period.php");
															$period_count++;
															
															$res->MoveNext();
														} ?>
													</div>
													
												</div>  
											</div>
											
											<div class="row">
												<div class="col-sm-12 ">
													<div class="d-flex theme-h-border"></div>
												</div>
											</div>
											<br />
											
											<div class="d-flex">  
												<div class="col-12 col-sm-4 form-group">
													<label for="AY_MONTH"><?=AY_MONTH?></label>
												</div>
												<div class="col-12 col-sm-4 form-group"> 
													<input id="AY_MONTH" name="AY_MONTH" value="<?=$AY_MONTH?>" type="text" class="form-control" />
												</div>
											</div>
											
											<div class="row">
												<div class="col-sm-12 ">
													<div class="d-flex theme-h-border"></div>
												</div>
											</div>
											<br />
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<input id="PROGRAM_LENGTH" name="PROGRAM_LENGTH" value="<?=$PROGRAM_LENGTH?>" type="text" class="form-control" />
													<span class="bar"></span>
													<label for="PROGRAM_LENGTH"><?=PROGRAM_LENGTH?></label>
												</div>
												<div class="col-12 col-sm-6 form-group"> 
													<input id="PROGRAM_COST" name="PROGRAM_COST" value="<?=$PROGRAM_COST?>" type="currency" class="form-control" />
													<span class="bar"></span>
													<label for="PROGRAM_COST"><?=PROGRAM_COST?></label>
												</div>
											</div>
										</div>
										<div class="col-sm-4 theme-v-border" style="padding-top: 25px;" >
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<input id="SCHOOL_CODE" name="SCHOOL_CODE" value="<?=$SCHOOL_CODE?>" type="text" class="form-control" />
													<span class="bar"></span>
													<label for="SCHOOL_CODE"><?=SCHOOL_CODE?></label>
												</div>
												
												<div class="col-12 col-sm-6 form-group"> 
													<select id="PK_DEPENDENT_STATUS" name="PK_DEPENDENT_STATUS" class="form-control">
														<option></option>
														<? $res_type = $db->Execute("select PK_DEPENDENT_STATUS,CODE,DESCRIPTION from M_DEPENDENT_STATUS WHERE ACTIVE = 1 order by DESCRIPTION ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_DEPENDENT_STATUS']?>" <? if($PK_DEPENDENT_STATUS == $res_type->fields['PK_DEPENDENT_STATUS']) echo "selected"; ?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
													<span class="bar"></span>
													<label for="PK_DEPENDENT_STATUS"><?=DEPENDENCY_STATUS?></label>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<input id="EFC_NO" name="EFC_NO" value="<?=$EFC_NO?>" type="number" min="0" oninput="validity.valid||(value='');" class="form-control" />
													<span class="bar"></span>
													<label for="EFC_NO"><?=EFC_NO?></label>
												</div>
												
												<div class="col-12 col-sm-6 form-group"> 
													<select id="AUTOMATIC_ZERO_EFC" name="AUTOMATIC_ZERO_EFC" class="form-control">
														<option ></option>
														<option value="1" <? if($AUTOMATIC_ZERO_EFC == 1) echo "selected"; ?> >Yes</option>
														<option value="2" <? if($AUTOMATIC_ZERO_EFC == 2) echo "selected"; ?> >No</option>
													</select>
													<span class="bar"></span>
													<label for="AUTO_ZERO_EFC"><?=AUTO_ZERO_EFC?></label>
												</div>
											</div>
											
											<!--<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<select id="YEAR_ROUND_PELL" name="YEAR_ROUND_PELL" class="form-control">
														<option ></option>
														<option value="1" <? if($YEAR_ROUND_PELL == 1) echo "selected"; ?> >Yes</option>
														<option value="2" <? if($YEAR_ROUND_PELL == 2) echo "selected"; ?> >No</option>
													</select>
													<span class="bar"></span>
													<label for="YEAR_ROUND_PELL"><?=YEAR_ROUND_PELL?></label>
												</div>
											</div>-->
											
											<div class="row">
												<div class="col-sm-12 ">
													<div class="d-flex theme-h-border"></div>
												</div>
											</div>
											<br />
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<input id="ISIR_TRANS_NO" name="ISIR_TRANS_NO" value="<?=$ISIR_TRANS_NO?>" type="number" min="0" oninput="validity.valid||(value='');" class="form-control" />
													<span class="bar"></span>
													<label for="ISIR_TRANS_NO"><?=ISIR_TRANS_NO?></label>
												</div>
												<div class="col-12 col-sm-6 form-group">   
													<select id="ISIR_CLEAR_PAY" name="ISIR_CLEAR_PAY" class="form-control">
														<option ></option>
														<option value="1" <? if($ISIR_CLEAR_PAY == 1) echo "selected"; ?> >Yes</option>
														<option value="2" <? if($ISIR_CLEAR_PAY == 2) echo "selected"; ?> >No</option>
													</select>
													<span class="bar"></span>
													<label for="ISIR_CLEAR_PAY"><?=ISIR_CLEAR_PAY?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<input id="ISIR_PROCESSED_DATE" name="ISIR_PROCESSED_DATE" value="<?=$ISIR_PROCESSED_DATE?>" type="text" class="form-control date" />
													<span class="bar"></span>
													<label for="ISIR_PROCESSED_DATE"><?=ISIR_PROCESSED_DATE?></label>
												</div>
												<!--<div class="col-12 col-sm-6 form-group"> 
													<input id="ISIR_SIGNED_DATE" name="ISIR_SIGNED_DATE" value="<?=$ISIR_SIGNED_DATE?>" type="text" class="form-control date" />
													<span class="bar"></span>
													<label for="ISIR_SIGNED_DATE"><?=ISIR_SIGNED_DATE?></label>
												</div>-->
											</div>
											<div class="row">
												<div class="col-sm-12 ">
													<div class="d-flex theme-h-border"></div>
												</div>
											</div>
											<br />
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<select id="SELECTED_FOR_VERIFICATION" name="SELECTED_FOR_VERIFICATION" class="form-control">
														<option ></option>
														<option value="1" <? if($SELECTED_FOR_VERIFICATION == 1) echo "selected"; ?> >Yes</option>
														<option value="2" <? if($SELECTED_FOR_VERIFICATION == 2) echo "selected"; ?> >No</option>
													</select>
													<span class="bar"></span>
													<label for="SELECTED_FOR_VERIFICATION"><?=SELECTED_FOR_VERIFICATION?></label>
												</div>
												
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<select id="PROFESSIONAL_JUDGEMENT" name="PROFESSIONAL_JUDGEMENT" class="form-control">
														<option ></option>
														<option value="1" <? if($PROFESSIONAL_JUDGEMENT == 1) echo "selected"; ?> >Yes</option>
														<option value="2" <? if($PROFESSIONAL_JUDGEMENT == 2) echo "selected"; ?> >No</option>
													</select>
													<span class="bar"></span>
													<label for="PROFESSIONAL_JUDGEMENT"><?=PROFESSIONAL_JUDGEMENT?></label>
												</div>
												<div class="col-12 col-sm-6 form-group"> 
													<select id="OVERRIDE" name="OVERRIDE" class="form-control">
														<option ></option>
														<option value="1" <? if($OVERRIDE == 1) echo "selected"; ?> >Yes</option>
														<option value="2" <? if($OVERRIDE == 2) echo "selected"; ?> >No</option>
													</select>
													<span class="bar"></span>
													<label for="OVERRIDE"><?=OVERRIDE?></label>
												</div>
											</div>
											
											<div class="row">
												<div class="col-sm-12 ">
													<div class="d-flex theme-h-border"></div>
												</div>
											</div>
											<br />
											
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<input id="NO_OF_DEPENDENTS" name="NO_OF_DEPENDENTS" value="<?=$NO_OF_DEPENDENTS?>" type="number" class="form-control" />
													<span class="bar"></span>
													<label for="NO_OF_DEPENDENTS"><?=NO_OF_DEPENDENTS?></label>
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<select id="PK_MARITAL_STATUS" name="PK_MARITAL_STATUS" class="form-control">
														<option selected></option>
														<? $res_type = $db->Execute("select * from Z_MARITAL_STATUS WHERE ACTIVE = 1 order by MARITAL_STATUS ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_MARITAL_STATUS']?>"  <? if($res_type->fields['PK_MARITAL_STATUS'] == $PK_MARITAL_STATUS) echo "selected"; ?> ><?=$res_type->fields['MARITAL_STATUS']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
													<span class="bar"></span>
													<label for="MARITAL_STATUS"><?=MARITAL_STATUS?></label>
												</div>
												<div class="col-12 col-sm-6 form-group"> 
													<input id="MARITAL_STATUS_DATE" name="MARITAL_STATUS_DATE" value="<?=$MARITAL_STATUS_DATE?>" type="text" class="form-control date" />
													<span class="bar"></span>
													<label for="MARITAL_STATUS_DATE"><?=MARITAL_STATUS_DATE?></label>
												</div>
											</div>
											<div class="row">
												<div class="col-sm-12 ">
													<div class="d-flex theme-h-border"></div>
												</div>
											</div>
											<br />
											
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<select id="ELIGIBLE_CITIZEN" name="ELIGIBLE_CITIZEN" class="form-control">
														<option ></option>
														<option value="1" <? if($ELIGIBLE_CITIZEN == 1) echo "selected"; ?> >Yes</option>
														<option value="2" <? if($ELIGIBLE_CITIZEN == 2) echo "selected"; ?> >No</option>
													</select>
													<span class="bar"></span>
													<label for="ELIGIBLE_CITIZEN"><?=ELIGIBLE_CITIZEN?></label>
												</div>
												<div class="col-12 col-sm-6 form-group"> 
													<input id="I551N0" name="I551N0" value="<?=$I551N0?>" type="text" class="form-control" />
													<span class="bar"></span>
													<label for="I551N0"><?=I551N0?></label>
												</div>
											</div>
											
											<div class="row">
												<div class="col-sm-12 ">
													<div class="d-flex theme-h-border"></div>
												</div>
											</div>
											<br />
											
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group">   
													<select id="DEGREE_CERT" name="DEGREE_CERT" class="form-control">
														<option></option>
													</select>
													<span class="bar"></span>
													<label for="DEGREE_CERT"><?=DEGREE_CERT?></label>
												</div>
												<div class="col-12 col-sm-6 form-group">   
													<select id="STUDENT_DEGREE" name="STUDENT_DEGREE" class="form-control">
														<option></option>
													</select>
													<span class="bar"></span>
													<label for="STUDENT_DEGREE"><?=STUDENT_DEGREE?></label>
												</div>
											</div>
											
											<div class="row">
												<div class="col-sm-12 ">
													<div class="d-flex theme-h-border"></div>
												</div>
											</div>
											<br />
											
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<select id="VA_STUDENT" name="VA_STUDENT" class="form-control">
														<option ></option>
														<option value="1" <? if($VA_STUDENT == 1) echo "selected"; ?> >Yes</option>
														<option value="2" <? if($VA_STUDENT == 2) echo "selected"; ?> >No</option>
													</select>
													<span class="bar"></span>
													<label for="VA_STUDENT"><?=VA_STUDENT?></label>
												</div>
												<!--<div class="col-12 col-sm-6 form-group"> 
													<select id="IS_FOREIGN" name="IS_FOREIGN" class="form-control">
														<option ></option>
														<option value="1" <? if($IS_FOREIGN == 1) echo "selected"; ?> >Yes</option>
														<option value="2" <? if($IS_FOREIGN == 2) echo "selected"; ?> >No</option>
													</select>
													<span class="bar"></span>
													<label for="IS_FOREIGN"><?=FOREIGN?></label>
												</div>-->
											</div>
											
											<!--<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<input id="DEPENDENTS_IN_COLLEGE" name="DEPENDENTS_IN_COLLEGE" value="<?=$DEPENDENTS_IN_COLLEGE?>" type="text" class="form-control" />
													<span class="bar"></span>
													<label for="DEPENDENTS_IN_COLLEGE"><?=DEPENDENTS_IN_COLLEGE?></label>
												</div>
											</div>-->
										</div>  	
										<div class="col-sm-4 theme-v-border" style="padding-top: 25px;" >
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<input id="COA" name="COA" value="<?=$COA?>" type="currency" class="form-control" />
													<span class="bar"></span>
													<label for="COA"><?=COA?></label>
												</div>
												
												<div class="col-12 col-sm-6 form-group"> 
													<select id="COA_CATEGORY" name="COA_CATEGORY" class="form-control">
														<option></option>
													</select>
													<span class="bar"></span>
													<label for="COA_CATEGORY"><?=COA_CATEGORY?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<input id="NEED" name="NEED" value="<?=$NEED?>" type="currency" class="form-control" />
													<span class="bar"></span>
													<label for="NEED"><?=NEED?></label>
												</div>
											</div>
											<div class="row">
												<div class="col-sm-12 ">
													<div class="d-flex theme-h-border"></div>
												</div>
											</div>
											<br />
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<input id="INCOME_LEVEL" name="INCOME_LEVEL" value="<?=$INCOME_LEVEL?>" type="number" min="0" oninput="validity.valid||(value='');" class="form-control" />
													<span class="bar"></span>
													<label for="INCOME_LEVEL"><?=INCOME_LEVEL?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<input id="STUDENT_INCOME" name="STUDENT_INCOME" value="<?=$STUDENT_INCOME?>" type="number" min="0" oninput="validity.valid||(value='');" class="form-control" />
													<span class="bar"></span>
													<label for="STUDENT_INCOME"><?=STUDENT_INCOME?></label>
												</div>
												<div class="col-12 col-sm-6 form-group"> 
													<input id="STUDENT_CONTRIBUTION" name="STUDENT_CONTRIBUTION" value="<?=$STUDENT_CONTRIBUTION?>" type="number" min="0" oninput="validity.valid||(value='');" class="form-control" />
													<span class="bar"></span>
													<label for="STUDENT_CONTRIBUTION"><?=STUDENT_CONTRIBUTION?></label>
												</div>
												
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group"> 
													<input id="PARENT_INCOME" name="PARENT_INCOME" value="<?=$PARENT_INCOME?>" type="number" min="0" oninput="validity.valid||(value='');" class="form-control" />
													<span class="bar"></span>
													<label for="PARENT_INCOME"><?=PARENT_INCOME?></label>
												</div>
												<div class="col-12 col-sm-6 form-group"> 
													<input id="PARENT_CONTRIBUTION" name="PARENT_CONTRIBUTION" value="<?=$PARENT_CONTRIBUTION?>" type="number" min="0" oninput="validity.valid||(value='');" class="form-control" />
													<span class="bar"></span>
													<label for="PARENT_CONTRIBUTION"><?=PARENT_CONTRIBUTION?></label>
												</div>
											</div>
											<div class="row">
												<div class="col-sm-12 ">
													<div class="d-flex theme-h-border"></div>
												</div>
											</div>
											<br />
											
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group">     
													<select id="LENDER" name="LENDER" class="form-control">
														<option></option>
													</select>
													<span class="bar"></span>
													<label for="LENDER"><?=LENDER?></label>
												</div>
												<div class="col-12 col-sm-6 form-group"> 
													<input id="REPACKAGE_DATE" name="REPACKAGE_DATE" value="<?=$REPACKAGE_DATE?>" type="text" class="form-control date" />
													<span class="bar"></span>
													<label for="REPACKAGE_DATE"><?=REPACKAGE_DATE?></label>
												</div>
											</div>
											
											<div class="row">
												<div class="col-sm-12 ">
													<div class="d-flex theme-h-border"></div>
												</div>
											</div>
											<br />
											
											<div class="d-flex">
												<div class="col-12 col-sm-6 form-group">   
													<select id="FA_ADVISOR" name="FA_ADVISOR" value="<?=$FA_ADVISOR?>" class="form-control">
														<option></option>
													</select>
													<span class="bar"></span>
													<label for="FA_ADVISOR"><?=FA_ADVISOR?></label>
												</div>
												<div class="col-12 col-sm-6 form-group"> 
													<input id="UPDATED" name="UPDATED" value="<?=$UPDATED?>" type="text" class="form-control" />
													<span class="bar"></span>
													<label for="UPDATED"><?=UPDATED?></label>
												</div>
											</div>
											
											<? $res_type = $db->Execute("select PK_CUSTOM_FIELDS,FIELD_NAME,PK_DATA_TYPES, PK_USER_DEFINED_FIELDS from S_CUSTOM_FIELDS WHERE S_CUSTOM_FIELDS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND TAB = 'Financial Aid' AND SECTION = 1 AND PK_DEPARTMENT = '$PK_DEPARTMENT' "); 
											while (!$res_type->EOF) { ?>
											<div class="d-flex ">
												<div class="col-12 col-sm-12 form-group">
													<? $PK_CUSTOM_FIELDS 	= $res_type->fields['PK_CUSTOM_FIELDS'];
													$PK_USER_DEFINED_FIELDS = $res_type->fields['PK_USER_DEFINED_FIELDS'];
													
													$res_1 = $db->Execute("select FIELD_VALUE from S_STUDENT_CUSTOM_FIELDS WHERE PK_STUDENT_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' "); ?>
													
													<input name="PK_CUSTOM_FIELDS[]" type="hidden" value="<?=$PK_CUSTOM_FIELDS?>" />
													<input name="FIELD_NAME[]" type="hidden" value="<?=$res_type->fields['FIELD_NAME']?>" />
													<input name="PK_DATA_TYPES[]" type="hidden" value="<?=$res_type->fields['PK_DATA_TYPES']?>" />
													
													<? $date_cls = "";
													if($res_type->fields['PK_DATA_TYPES'] == 1 || $res_type->fields['PK_DATA_TYPES'] == 4) { 
														$FIELD_VALUE = $res_1->fields['FIELD_VALUE'];
														if($res_type->fields['PK_DATA_TYPES'] == 4) {
															$date_cls = "date"; 
															if($FIELD_VALUE != '')
																$FIELD_VALUE = date("m/d/Y",strtotime($FIELD_VALUE));
														} ?>
															
														<input name="CUSTOM_FIELDS_<?=$PK_CUSTOM_FIELDS?>" id="CUSTOM_FIELDS_<?=$res_type->fields['PK_CUSTOM_FIELDS']?>" type="text" class="form-control <?=$date_cls?>" value="<?=$FIELD_VALUE?>" />
														
														<span class="bar"></span> 
														<label for="CUSTOM_FIELDS_<?=$res_type->fields['PK_CUSTOM_FIELDS']?>"><?=$res_type->fields['FIELD_NAME']?></label>
														
													<? } else if($res_type->fields['PK_DATA_TYPES'] == 2) { ?>
														<select name="CUSTOM_FIELDS_<?=$PK_CUSTOM_FIELDS?>" id="CUSTOM_FIELDS_<?=$res_type->fields['PK_CUSTOM_FIELDS']?>" class="form-control" >
															<option value=""></option>
															<? $res_dd = $db->Execute("select * from S_USER_DEFINED_FIELDS_DETAIL WHERE ACTIVE = '1' AND PK_USER_DEFINED_FIELDS = '$PK_USER_DEFINED_FIELDS' ORDER BY OPTION_NAME ASC ");
															while (!$res_dd->EOF) { ?>
																<option value="<?=$res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL']?>" <? if($res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL'] == $res_1->fields['FIELD_VALUE']) echo 'selected = "selected"';?> ><?=$res_dd->fields['OPTION_NAME']?></option>
															<?	$res_dd->MoveNext();
															}	?>
														</select>
														
														<span class="bar"></span> 
														<label for="CUSTOM_FIELDS_<?=$res_type->fields['PK_CUSTOM_FIELDS']?>"><?=$res_type->fields['FIELD_NAME']?></label>
														
													<? } else if($res_type->fields['PK_DATA_TYPES'] == 3) {
														$OPTIONS = explode(",",$res_1->fields['FIELD_VALUE']);
														$res_dd = $db->Execute("select * from S_USER_DEFINED_FIELDS_DETAIL WHERE ACTIVE = '1' AND PK_USER_DEFINED_FIELDS = '$PK_USER_DEFINED_FIELDS' ORDER BY OPTION_NAME ASC "); ?>
														<div class="col-12 col-sm-6 focused">
															<span class="bar"></span> 
															<label for="CAMPUS"><?=$res_type->fields['FIELD_NAME']?></label>
														</div>
														<? while (!$res_dd->EOF) { 
															$checked = '';
															foreach($OPTIONS as $OPTION){
																if($OPTION == $res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL']) {
																	$checked = 'checked="checked"';
																	break;
																}
															} ?>
															<div class="d-flex">
																<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
																	<input type="checkbox" class="custom-control-input" id="CUSTOM_FIELDS_<?=$PK_CUSTOM_FIELDS?>_<?=$res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL']?>" name="CUSTOM_FIELDS_<?=$PK_CUSTOM_FIELDS?>[]" value="<?=$res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL']?>" <?=$checked?> >
																	<label class="custom-control-label" for="CUSTOM_FIELDS_<?=$PK_CUSTOM_FIELDS?>_<?=$res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL']?>"><?=$res_dd->fields['OPTION_NAME']?></label>
																</div>
															</div>
															
														<?	$res_dd->MoveNext();
														}
													} ?>
													
													
												</div>
											</div>
											<?	$res_type->MoveNext();
											} ?>
											
											<div class="row">
												<div class=" col-sm-12">
													<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
													<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='student?id=<?=$_GET['sid']?>&tab=financialAidTab&t=<?=$_GET['t']?>&eid=<?=$_GET['eid']?>'" ><?=CANCEL?></button>
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
	
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
		
		jQuery('.date1').datepicker({
			todayHighlight: true,
			orientation: "bottom auto",
			autoclose: true,
		});
		$('.date1').datepicker().on('hide', function(e) {
			if(document.getElementById('ACADEMIC_YEAR_BEGIN').value != '') {
				var minDate = $("#ACADEMIC_YEAR_BEGIN").val();
				$('#ACADEMIC_YEAR_END').datepicker('setStartDate', minDate);
				
				document.getElementById('ACADEMIC_YEAR_END').focus();
				$("#ACADEMIC_YEAR_BEGIN").parent().addClass("focused")
			} else
				$("#ACADEMIC_YEAR_BEGIN").parent().removeClass("focused")
		});
		
		jQuery('.date2').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
		
		<? if($ACADEMIC_YEAR_BEGIN != ''){ ?>
			var minDate = $("#ACADEMIC_YEAR_BEGIN").val();
			$('#ACADEMIC_YEAR_END').datepicker('setStartDate', minDate);
		<? } ?>
		
		$('.timepicker').inputmask(
			"hh:mm t", {
				placeholder: "HH:MM AM/PM", 
				insertMode: false, 
				showMaskOnHover: false,
				hourFormat: 12
			}
		);
	});
	
	var period_count = '<?=$period_count?>';
	function add_period(){
		jQuery(document).ready(function($) { 
			var data  = 'period_count='+period_count+'&eid=<?=$_GET['eid']?>&sid=<?=$_GET['sid']?>';
			var value = $.ajax({
				url: "ajax_student_fa_period",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					//alert(data)
					$('#period_div').append(data);
					period_count++;
					
					jQuery('.date').datepicker({
						todayHighlight: true,
						orientation: "bottom auto"
					});
				}		
			}).responseText;
		});
	}
	
	function delete_row(id,type){
		jQuery(document).ready(function($) {
			if(type == 'period')
				document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.PERIOD?>?';
				
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
			$("#DELETE_TYPE").val(type)
		});
	}
	function conf_delete(val,id){
		jQuery(document).ready(function($) {
			if(val == 1) {
				if($("#DELETE_TYPE").val() == 'period') {
					var id = $("#DELETE_ID").val();
					$("#period_div_"+id).remove();
				}
			}
			$("#deleteModal").modal("hide");
		});
	}
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
	</script>
	
	<script src="../backend_assets/node_modules/Magnific-Popup-master/dist/jquery.magnific-popup.js"></script>
    <script src="../backend_assets/node_modules/Magnific-Popup-master/dist/jquery.magnific-popup-init.js"></script>
</body>

</html>