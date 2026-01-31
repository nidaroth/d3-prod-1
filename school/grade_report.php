<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	$stud_id = "";
	foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT) {
		if($stud_id != '')
			$stud_id .= ',';
		$stud_id .= $_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT];
	}
	$PK_STUDENT_MASTER		= $stud_id;
	$PK_STUDENT_ENROLLMENT 	= implode(",",$_POST['PK_STUDENT_ENROLLMENT']);
	
	if($_POST['REPORT_TYPE'] == 1) {

		$_GET['id'] = $PK_STUDENT_MASTER;
		$_GET['eid'] = $PK_STUDENT_ENROLLMENT;
		$_GET['show'] = $_POST['SHOW'];
		$_GET['type'] = 1;
		$_GET['p'] = 'report';
		$_GET['exclude_tc'] = $_POST['EXCLUDE_TRANSFERS_COURSE'];
		$_GET['report_type'] = $_POST['REPORT_OPTION'];
		$_GET['download_via_js']='yes';
		require_once('course_offering_grade_book_progress_report_pdf.php');
		//Ticket # 1219
	} else if($_POST['REPORT_TYPE'] == 2) {

		$_GET['p'] = 'r';
		$_GET['eid']= $PK_STUDENT_ENROLLMENT;
		$_GET['exclude_tc'] = $_POST['EXCLUDE_TRANSFERS_COURSE'];
		$_GET['min_gpa']= $_POST['MIN_GPA'];
		$_GET['max_gpa']= $_POST['MAX_GPA'];
		$_GET['campus']= implode(",",$_POST['PK_CAMPUS']);
		$_GET['format']= $_POST['FORMAT'];
		$_GET['download_via_js']='yes';
		require_once('gpa_analysis.php');

		// header("location:gpa_analysis?p=r&eid=".$PK_STUDENT_ENROLLMENT."&exclude_tc=".$_POST['EXCLUDE_TRANSFERS_COURSE']."&min_gpa=".$_POST['MIN_GPA']."&max_gpa=".$_POST['MAX_GPA'].'&campus='.implode(",",$_POST['PK_CAMPUS']).'&format='.$_POST['FORMAT']);	
	} else if($_POST['REPORT_TYPE'] == 3) {
		
		/* Ticket # 1979
		$res_course = $db->Execute("SELECT PK_COURSE_OFFERING FROM S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) GROUP BY PK_COURSE_OFFERING ");
		while (!$res_course->EOF) {
			$PK_COURSE_OFFERING_ARR[] = $res_course->fields['PK_COURSE_OFFERING'];
			$res_course->MoveNext();
		}
	
		header("location:grade_sheet?p=r&eid=".$PK_STUDENT_ENROLLMENT.'&pk_co='.implode(",",$PK_COURSE_OFFERING_ARR));	
		Ticket # 1979 */
		//Ticket # 669
		require_once('grade_sheet.php');
		//header("location:grade_sheet?p=r&pk_co=" . implode(",", $_POST['GS_PK_COURSE_OFFERING']));
		//Ticket # 669		
	} else if($_POST['REPORT_TYPE'] == 4) {
		$_GET['eid']=$PK_STUDENT_ENROLLMENT;
		$_GET['id']=$PK_STUDENT_MASTER;
		$_GET['report_type']=$_POST['GRADE_BOOK_REPORT_TYPE'];
		$_GET['download_via_js']='yes';
		require_once('grade_book_report_card_pdf.php');
		// header("location:grade_book_report_card_pdf?eid=".$PK_STUDENT_ENROLLMENT."&id=".$PK_STUDENT_MASTER."&report_type=".$_POST['GRADE_BOOK_REPORT_TYPE']);
	} else if($_POST['REPORT_TYPE'] == 5) {
		//header("location:course_offering_grade_book_progress_report_pdf?id=".$stud_id.'&report_type='.$_POST['REPORT_TYPE_1']);
	} else if($_POST['REPORT_TYPE'] == 6) {
		header("location:course_offering_grade_book_analysis_report?co_id=".$_POST['PK_COURSE_OFFERING_1'].'&format='.$_POST['FORMAT'].'&campus='.implode(",",$_POST['PK_CAMPUS']));
	}  else if($_POST['REPORT_TYPE'] == 7) { /* Ticket # 527 */
		$PK_STUDENT_MASTER_ARR = array();
		foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT) {
			$PK_STUDENT_MASTER_ARR[$_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT]] = $_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT];
		}
		$PK_STUDENT_MASTER		= implode(",",$PK_STUDENT_MASTER_ARR);
		$PK_STUDENT_ENROLLMENT 	= implode(",",$_POST['PK_STUDENT_ENROLLMENT']);

		$_GET['eid']=$PK_STUDENT_ENROLLMENT;
		$_GET['id']=$PK_STUDENT_MASTER;
		$_GET['campus']=implode(",",$_POST['PK_CAMPUS']);
		$_GET['exclude_tc']=$_POST['EXCLUDE_TRANSFERS_COURSE'];
		$_GET['download_via_js']='yes';
		require_once('satisfactory_progress_report.php'); 


		// header("location:satisfactory_progress_report?eid=".$PK_STUDENT_ENROLLMENT.'&id='.$PK_STUDENT_MASTER.'&campus='.implode(",",$_POST['PK_CAMPUS'])."&exclude_tc=".$_POST['EXCLUDE_TRANSFERS_COURSE']);
	} /* Ticket # 527 */
	else if($_POST['REPORT_TYPE'] == 8) { /* Ticket # 1574 */
		$PK_STUDENT_MASTER_ARR = array();
		foreach($_POST['PK_STUDENT_ENROLLMENT'] as $PK_STUDENT_ENROLLMENT) {
			$PK_STUDENT_MASTER_ARR[$_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT]] = $_POST['PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT];
		}
		$PK_STUDENT_MASTER		= implode(",",$PK_STUDENT_MASTER_ARR);
		$PK_STUDENT_ENROLLMENT 	= implode(",",$_POST['PK_STUDENT_ENROLLMENT']);
		$_GET['eid'] = $PK_STUDENT_ENROLLMENT;
		$_GET['download_via_js']='yes';
		require_once('program_course_progress_report_pdf.php');
		// header("location:program_course_progress_report_pdf?eid=".$PK_STUDENT_ENROLLMENT);
		/* Ticket # 1574 */
	} else if($_POST['REPORT_TYPE'] == 9) {
		//echo "<pre>";print_r($_POST);exit;;
		$_GET['co_id'] = implode(",",$_POST['SEL_PK_COURSE_OFFERING']);
		$_GET['campus'] = implode(",",$_POST['TERM_PK_CAMPUS']);
		$_GET['term'] = implode(",",$_POST['COURSE_PK_TERM']);
		$_GET['format'] = $_POST['FORMAT'];
		$_GET['download_via_js']='yes';
		require_once('final_grade_incomplete_report.php');
		// header("location:final_grade_incomplete_report?co_id=".implode(",",$_POST['SEL_PK_COURSE_OFFERING']).'&campus='.implode(",",$_POST['TERM_PK_CAMPUS']).'&term='.implode(",",$_POST['COURSE_PK_TERM']).'&format='.$_POST['FORMAT']);	
	} else if($_POST['REPORT_TYPE'] == 10) {
		//echo "<pre>";print_r($_POST);exit;;
		//header("location:final_grade_analysis_by_student_report?co_id=".implode(",",$_POST['FGA_COURSE_PK_COURSE_OFFERING']).'&campus='.implode(",",$_POST['PK_CAMPUS']).'&term='.implode(",",$_POST['FGA_COURSE_PK_TERM']).'&grade='.implode(",",$_POST['FGA_FINAL_GRADE']).'&eid='.$PK_STUDENT_ENROLLMENT.'&format='.$_POST['FORMAT']);	
		//DIAM-1753
		// echo $PK_STUDENT_ENROLLMENT;exit;
		$_GET['co_id']=implode(",",$_POST['PK_COURSE_OFFERING_GPA1']);
		$_GET['campus']=implode(",",$_POST['PK_CAMPUS']);
		$_GET['term']=$_POST['PK_TERM_MASTER_5'];
		$_GET['grade']=implode(",",$_POST['FGA_FINAL_GRADE']);
		$_GET['eid']=$PK_STUDENT_ENROLLMENT;
		$_GET['format']=$_POST['FORMAT'];
		$_GET['FORMAT']=$_POST['FORMAT'];
		$_GET['download_via_js']='yes';
		require_once('final_grade_analysis_by_student_report.php');
		//header("location:final_grade_analysis_by_student_report?co_id=".implode(",",$_POST['PK_COURSE_OFFERING_GPA1']).'&campus='.implode(",",$_POST['PK_CAMPUS']).'&term='.implode(",",$_POST['PK_TERM_MASTER_5']).'&grade='.implode(",",$_POST['FGA_FINAL_GRADE']).'&eid='.$PK_STUDENT_ENROLLMENT.'&format='.$_POST['FORMAT']);	
		//DIAM-1753
	} else if($_POST['REPORT_TYPE'] == 11) { // DIAM-2233
		header("location:course_offering_grade_book_analysis_by_student_id_report?co_id=".$_POST['PK_COURSE_OFFERING_1'].'&format='.$_POST['FORMAT'].'&campus='.implode(",",$_POST['PK_CAMPUS']));
	}else if($_POST['REPORT_TYPE'] == 12) { // DIAM-1599
		require_once('grade_book_report.php');
	}
	
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
	<title><?=MNU_GRADES?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		.dropdown-menu>li>a { white-space: nowrap; max-width: 90%} /* Ticket # 1740  */
		#advice-required-entry-FGA_COURSE_PK_TERM, #advice-required-entry-FGA_COURSE_PK_COURSE_OFFERING, #advice-required-entry-GS_PK_COURSE_OFFERING, #advice-required-entry-PK_COURSE_OFFERING_GPA1, #advice-required-entry-FGA_FINAL_GRADE{position: absolute;top: 38px;}
		#advice-required-entry-term_begin_start_date,#advice-required-entry-term_begin_end_date{position: absolute;top: 60px;}
		
		.lds-ring {
			position: absolute;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			margin: auto;
			width: 64px;
			height: 64px;
		}

		.lds-ring div {
			box-sizing: border-box;
			display: block;
			position: absolute;
			width: 51px;
			height: 51px;
			margin: 6px;
			border: 6px solid #0066ac;
			border-radius: 50%;
			animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
			border-color: #007bff transparent transparent transparent;
		}

		.lds-ring div:nth-child(1) {
			animation-delay: -0.45s;
		}

		.lds-ring div:nth-child(2) {
			animation-delay: -0.3s;
		}

		.lds-ring div:nth-child(3) {
			animation-delay: -0.15s;
		}

		@keyframes lds-ring {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}

		#loaders {
			position: fixed;
			width: 100%;
			z-index: 9999;
			bottom: 0;
			background-color: #2c3e50;
			display: block;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			opacity: 0.6;
			display: none;
		}	
		/* DIAM-1753 */
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
   <div id="loaders" style="display: none;"> <!--DIAM-1753-->
		<div class="lds-ring">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
		</div>
	</div>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_GRADES?>
						</h4>
                    </div>
                </div>
				
				<div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels" method="post" name="form1" id="form1" >
									<div class="row" style="padding-bottom:10px;" >
										<div class="col-md-3 " style="max-width: 26%;flex: 0 0 26%;" >
											<b><?=GRADE_TYPE?></b>
											<select id="REPORT_TYPE" name="REPORT_TYPE"  class="form-control" onchange="show_filters(this.value)" >
												<option value="6">Course Offering Grade Book Analysis</option><!-- Ticket # 1472 -->
												<option value="11"><?=MNU_CO_GRADE_BOOK_ANALYSIS_BY_SSN?></option><!-- DIAM-2233 -->
												<option value="1"><?=MNU_COURSE_OFFERING_GRADE_BOOK_PROGRESS_REPORT?></option>
												<option value="10">Final Grade Analysis By Student</option>
												<option value="9">Final Grade Incomplete</option><!-- Ticket # 1471 -->
												<option value="2"><?=MNU_GPA_ANALYSIS?></option>
												<option value="12"><?=MNU_GRADE_BOOK?></option><!-- DIAM-1599 -->
												<option value="3"><?=MNU_GRADE_SHEET?></option>
												<option value="8">Program Course Progress</option><!-- Ticket # 1574 -->
												<option value="4"><?=MNU_PROGRAM_GRADE_BOOK_REPORT_CARD?></option>
												<!-- <option value="5"><?=MNU_REPORT_CARD?></option> -->
												<option value="7">Satisfactory Progress Report Card</option><!-- Ticket # 527 -->
											</select>
										</div>
										
										<!-- Ticket # 1219 -->
										<div class="col-md-2 " id="REPORT_OPTION_DIV" >
											<b><?=REPORT_OPTION?></b>
											<select id="REPORT_OPTION" name="REPORT_OPTION"  class="form-control" >
												<option value="1" >Detailed Report</option>
												<option value="2" >Summary Report</option>
											</select>
										</div>
										<!-- Ticket # 1219 -->
										
									</div>
									<div class="row" style="padding-bottom:10px;" >
										<div class="col-md-2 align-self-center " id="ENROLLMENT_TYPE_div" style="display: inline;">
											<select id="ENROLLMENT_TYPE" name="ENROLLMENT_TYPE" class="form-control">
												<option value="1" selected>All Enrollments</option>
												<option value="2">Current Enrollment</option>
											</select>
										</div>
									</div>
									<div class="row" style="padding-bottom:10px;" >
										<!-- Ticket # 1472  -->
										<div class="col-md-2" id="PK_CAMPUS_DIV"  >
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 " style="max-width: 20%;flex: 0 0 20%;" id="PK_TERM_MASTER_1_DIV" >
											<select id="PK_TERM_MASTER_1" name="PK_TERM_MASTER_1"  class="form-control required-entry" onchange="get_course_offering_1()" >
												<option value=""><?=TERM ?></option>
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_COURSE_1_DIV" >
											<select id="PK_COURSE_1" name="PK_COURSE_1" class="form-control required-entry" onchange="get_course_offering_1();" >
												<option value=""><?=COURSE ?></option>
												<? /* Ticket # 1740  */
												$res_type = $db->Execute("select PK_COURSE, COURSE_CODE, TRANSCRIPT_CODE, COURSE_DESCRIPTION from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by COURSE_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_COURSE']?>" ><?=$res_type->fields['COURSE_CODE'].' - '.$res_type->fields['TRANSCRIPT_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} /* Ticket # 1740  */ ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_COURSE_OFFERING_1_DIV" >
											<select id="PK_COURSE_OFFERING_1" name="PK_COURSE_OFFERING_1" class="form-control required-entry" >
												<option value=""><?=COURSE_OFFERING?></option>
											</select>
										</div>
										
										<div class="col-md-2 " id="btn_div_1" style="display:none" > <!-- Ticket # 527 -->
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info" id="btn_2" style="display:none" ><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info" id="btn_3" style="display:none" ><?=EXCEL?></button>
										</div>
										
										<!-- Ticket # 1472  -->
										
										<div class="col-md-2 "  id="PK_TERM_MASTER_DIV" >
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_CAMPUS_PROGRAM_DIV" >
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_STUDENT_STATUS_DIV" >
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_STUDENT_GROUP_DIV" >
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by STUDENT_GROUP ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
									</div>
									
									<div class="row" style="padding-bottom:10px;" >
										<div class="col-md-2" id="TERM_PK_CAMPUS_DIV" >
											<select id="TERM_PK_CAMPUS" name="TERM_PK_CAMPUS[]" multiple class="form-control" onchange="get_course_term_from_campus();clear_search()" > 
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2" id="COURSE_PK_TERM_DIV" >
											<select id="COURSE_PK_TERM" name="COURSE_PK_TERM[]" multiple class="form-control" onchange="get_course_from_term();clear_search()" >
												<? $res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']." - ".$res_type->fields['END_DATE_1']." - ".$res_type->fields['TERM_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									</div>

									<!-- DIAM-1419 -->
									<div class="row" style="padding-bottom:10px;" >

										<div class="col-md-2" id="PK_CAMPUS_DIV_GPA" style="">
												<select id="PK_CAMPUS_GPA" name="PK_CAMPUS_GPA[]" multiple class="form-control" onchange="get_term_from_campus_by_date(1);"> <? //get_term_from_campus() ?>
													<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS,ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC,CAMPUS_CODE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($res_type->RecordCount() == 1) echo "selected"; ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['CAMPUS_CODE'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
														</option>
													<? $res_type->MoveNext();
													} ?>
												</select>
											</div>	
											<!--DIAM-1753 -->
											<div class="col-md-4" style="bottom:21px" id="TERM_DATE_GPA_DIV">
													<b  style="margin-bottom:5px">&nbsp;</b>
													<div class="d-flex " style="margin-bottom:5px">
													<div><input type="text" class="form-control date" name="term_begin_start_date" field="term_begin_start_date" id="term_begin_start_date"  placeholder="Course Term Start Date" value="" ></div>&nbsp;&nbsp;
													<div><input type="text" class="form-control date" name="term_begin_end_date"  field="term_begin_end_date" id="term_begin_end_date"   placeholder="Course Term End Date" value="" >
														</div>
													</div>
											</div>
											<div  id="PK_TERM_MASTER_5_DIV" style="display:none;"></div>
											<div class="col-md-3" style="bottom:21px;max-width:320px;display:none;">
													<b  style="margin-bottom:5px">Term End Date Range</b>
													<div class="d-flex ">
													<input type="text" class="form-control date" name="term_end_start_date" id="term_end_start_date"  placeholder="Start Date">
													<input type="text" class="form-control date" name="term_end_end_date" id="term_end_end_date"   placeholder="End Date">							

												</div>
											</div>

											<div class="col-md-2 " id="PK_COURSE_OFFERING_GPA_DIV" >
												<select id="PK_COURSE_OFFERING_GPA" name="PK_COURSE_OFFERING_GPA" class="form-control"> 
												<option value=""><?=COURSE_OFFERING?></option>
												</select>
											</div>
											<!-- DIAM-1753 -->
											<div class="col-md-2" id="FGA_FINAL_GRADE_DIV" >
											<select id="FGA_FINAL_GRADE" name="FGA_FINAL_GRADE[]" multiple class="form-control required-entry" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_GRADE, GRADE from S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by GRADE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_GRADE']?>" ><?=$res_type->fields['GRADE'] ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2 " id="btn_div_GPAS1" style="display:none" > <!-- Ticket # 1723 -->
											<button type="button" onclick="search()" class="btn waves-effect waves-light btn-info" id="search_btn1" ><?=SEARCH?></button>
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info" id="btn_gpa1" style="display:none" ><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info" id="btn_gpa2" style="display:none" ><?=EXCEL?></button>
										</div>
										<!-- DIAM-1753 -->

										</div>
									<!-- DIAM-1419 -->
									<div class="row">
										<div class="col-md-2" id="COURSE_PK_COURSE_DIV" > <!-- Ticket # 1979 -->
											<select id="COURSE_PK_COURSE" name="COURSE_PK_COURSE[]" multiple class="form-control" onchange="get_course_offering_2();clear_search()" >
												<? /*$res_type = $db->Execute("select PK_COURSE,COURSE_CODE, TRANSCRIPT_CODE,COURSE_DESCRIPTION from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by COURSE_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_COURSE']?>" ><?=$res_type->fields['COURSE_CODE'].' - '.$res_type->fields['TRANSCRIPT_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												}*/ ?>
											</select>
										</div>
										
										<div class="col-md-2" id="COURSE_PK_COURSE_OFFERING_DIV" > <!-- Ticket # 1979 -->
											<select id="COURSE_PK_COURSE_OFFERING" name="COURSE_PK_COURSE_OFFERING[]" multiple class="form-control" >
												<? /* $res_type = $db->Execute("select PK_COURSE_OFFERING, COURSE_CODE, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, SESSION_NO, SESSION, TRANSCRIPT_CODE, COURSE_DESCRIPTION from S_COURSE, S_COURSE_OFFERING LEFT JOIN M_SESSION on M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.ACTIVE = 1 AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE ORDER BY S_TERM_MASTER.BEGIN_DATE DESC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC ");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_COURSE_OFFERING'] ?>" <? if($res_type->fields['PK_COURSE_OFFERING'] == $PK_COURSE_OFFERING) echo "selected"; ?> >
														<? echo $res_type->fields['COURSE_CODE']." (".substr($res_type->fields['SESSION'],0,1)."-".$res_type->fields['SESSION_NO'].") ".$res_type->fields['TRANSCRIPT_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION']." - ".$res_type->fields['TERM_BEGIN_DATE']; ?>
													</option>
												<?	$res_type->MoveNext();
												} */ ?>
											</select>
										</div>
										
										<div class="col-md-2" id="PK_SESSION_DIV" >
											<select id="PK_SESSION" name="PK_SESSION[]" multiple class="form-control"  onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_SESSION, SESSION, ACTIVE from M_SESSION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, DISPLAY_ORDER ASC");
												while (!$res_type->EOF) { 
													$option_label = substr($res_type->fields['SESSION'],0,1).'-'.$res_type->fields['SESSION'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_SESSION'] ?>" <? if($PK_SESSION == $res_type->fields['PK_SESSION']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2" id="SESSION_NO_DIV" >
											<input id="SESSION_NO" name="SESSION_NO" value="" type="text" class="form-control" placeholder="<?=SESSION_NO?>" >
										</div>
										
										<div class="col-md-2" id="PK_COURSE_OFFERING_STATUS_DIV" >
											<select id="PK_COURSE_OFFERING_STATUS" name="PK_COURSE_OFFERING_STATUS[]" multiple class="form-control" onchange="clear_search()" >
												<? /* Ticket #1149  */
												$res_type = $db->Execute("select PK_COURSE_OFFERING_STATUS,COURSE_OFFERING_STATUS from M_COURSE_OFFERING_STATUS WHERE 1 = 1 order by COURSE_OFFERING_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_COURSE_OFFERING_STATUS'] ?>" ><?=$res_type->fields['COURSE_OFFERING_STATUS']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2" id="FGA_COURSE_PK_TERM_DIV" >
											<select id="FGA_COURSE_PK_TERM" name="FGA_COURSE_PK_TERM[]" multiple class="form-control required-entry" onchange="get_course_offering_from_term();clear_search()" >
												<? $res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']." - ".$res_type->fields['END_DATE_1']." - ".$res_type->fields['TERM_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2" id="FGA_COURSE_COURSE_OFFERING_DIV" >
											<select id="FGA_COURSE_PK_COURSE_OFFERING" name="FGA_COURSE_PK_COURSE_OFFERING[]" multiple class="form-control required-entry" >
												<option value=""></option>
											</select>
										</div>
										
										
										<div class="col-md-2 " id="PK_COURSE_DIV" >
											<select id="PK_COURSE" name="PK_COURSE[]" multiple class="form-control" onchange="get_course_offering(this.value);clear_search()" >
												<? /* Ticket # 1740  */
												$res_type = $db->Execute("select PK_COURSE, COURSE_CODE, TRANSCRIPT_CODE, COURSE_DESCRIPTION from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by COURSE_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_COURSE']?>" ><?=$res_type->fields['COURSE_CODE'].' - '.$res_type->fields['TRANSCRIPT_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} /* Ticket # 1740  */ ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_COURSE_OFFERING_DIV" >
											<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control" >
												<option value=""><?=COURSE_OFFERING_PAGE_TITLE?></option>
											</select>
										</div>
								
										<div class="col-md-2 align-self-center" id="SHOW_div" style="max-width: 20%;flex: 0 0 20%;" >
											<select id="SHOW" name="SHOW" class="form-control" >
												<option value="1">Both Completed and In Progress</option>
												<option value="2">Completed Only</option>
												<option value="3">In Progress</option>
											</select>
										</div>
										
										<div class="col-md-2 align-self-center" style="max-width: 20%;flex: 0 0 20%;display:none" id="GRADE_BOOK_REPORT_TYPE_div" >
											<select id="GRADE_BOOK_REPORT_TYPE" name="GRADE_BOOK_REPORT_TYPE" class="form-control" >
												<option value="1">Detail View</option>
												<option value="2">Detail View With Attendance</option>
												<option value="3">Summary View</option>
												<option value="4">Summary View With Attendance</option>
											</select>
										</div>
										
										<!-- Ticket #1361 -->
										<div class="col-md-2 align-self-center" style="display:none" id="MIN_GPA_div" >
											<input type="text" id="MIN_GPA" name="MIN_GPA" class="form-control" placeholder="Minimum GPA" />
										</div>
										
										<div class="col-md-2 align-self-center" style="display:none" id="MAX_GPA_div" >
											<input type="text" id="MAX_GPA" name="MAX_GPA" class="form-control" placeholder="Maximum GPA" />
										</div>
										<!-- Ticket #1361 -->
										
										<div class="col-md-2" id="EXCLUDE_TRANSFERS_COURSE_DIV" >
											<div class="custom-control custom-checkbox mr-sm-12">
												<input type="checkbox" class="custom-control-input" id="EXCLUDE_TRANSFERS_COURSE" name="EXCLUDE_TRANSFERS_COURSE" value="1" >
												<label class="custom-control-label" for="EXCLUDE_TRANSFERS_COURSE" ><?=EXCLUDE_TRANSFERS_COURSE?></label>
											</div>
										</div>
										
										<!-- Ticket # 1979 -->
										<div class="col-md-2" id="GS_PK_CAMPUS_DIV" >
											<select id="GS_PK_CAMPUS" name="GS_PK_CAMPUS[]" multiple class="form-control" onchange="get_course_term_from_campus();clear_search()" > 
												<? $res_type = $db->Execute("select CAMPUS_CODE, PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2" id="GS_PK_TERM_DIV" >
											<select id="GS_PK_TERM" name="GS_PK_TERM[]" multiple class="form-control" onchange="get_course_from_term();clear_search()" >
												<? $res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1']." - ".$res_type->fields['END_DATE_1']." - ".$res_type->fields['TERM_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2" id="GS_PK_COURSE_DIV" >
											<select id="GS_PK_COURSE" name="GS_PK_COURSE[]" multiple class="form-control" onchange="get_course_offering_2();clear_search()" >
											</select>
										</div>
										
										<div class="col-md-2" id="GS_PK_COURSE_OFFERING_DIV" >
											<select id="GS_PK_COURSE_OFFERING" name="GS_PK_COURSE_OFFERING[]" multiple class="form-control required-entry" >
											</select>
										</div>
										<!-- Ticket # 1979 -->
										
										<div class="col-md-2 align-self-center ">
											<button type="button" onclick="search()" class="btn waves-effect waves-light btn-info" id="search_btn" ><?=SEARCH?></button>
										
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info" id="btn_1" style="display:none" ><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info" id="btn_4" style="display:none" ><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									
									<!-- Ticket # 1552 -->
									<div class="row" style="margin-top:10px" >
										<div class="col-md-2 " id="SEARCH_TXT_DIV" style="display:none" >
											<input type="text" class="form-control" id="SEARCH_TXT" name="SEARCH_TXT" placeholder="&#xF002; <?=SEARCH?>" style="font-family: FontAwesome;" onkeypress="do_search(event)" >
										</div>
									</div>
									<!-- Ticket # 1552 -->
									
									<br />
									<div id="student_div" >
										<? /*$_REQUEST['show_check'] 	= 1;
										$_REQUEST['show_count']		= 1;
										$_REQUEST['group_by']		= '';
										$_REQUEST['ENROLLMENT']		= 1;
										require_once('ajax_search_student_for_reports.php');*/ ?>
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
		jQuery(document).ready(function($) { 
			show_filters(6)
		});
		
		/* Ticket # 1552 */
		function do_search(e){
			if (e.keyCode == 13) {
				search();
			}
		}
		/* Ticket # 1552 */
		
		function clear_search(){
			document.getElementById('student_div').innerHTML = '';
			show_btn()
		}
		
		function get_course_offering(val){
			jQuery(document).ready(function($) { 
				var data  = 'val='+$('#PK_COURSE').val()+'&multiple=0';
				var value = $.ajax({
					url: "ajax_get_course_offering",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
						document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
						document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"
						$("#PK_COURSE_OFFERING option[value='']").remove();
						
						document.getElementById('PK_COURSE_OFFERING').setAttribute("onchange", "clear_search()");
						
						$('#PK_COURSE_OFFERING').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
							nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
							numberDisplayed: 2,
							nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
						});
					}		
				}).responseText;
			});
		}
		function get_course_offering_session(){
		}
		
		/* Ticket # 1472 */
		function search(){
			jQuery(document).ready(function($) {
				if(document.getElementById('REPORT_TYPE').value == 6 || document.getElementById('REPORT_TYPE').value == 11) { //<!-- DIAM-2233 -->
					document.getElementById('student_div').innerHTML = ''
				} else if(document.getElementById('REPORT_TYPE').value == 9) {
					error = "";
					
					if($('#TERM_PK_CAMPUS').val() == '') {
						if(error != '')
							error += "\n";
						error += "Please Select Campus"
					}
					if(error != ""){
						alert(error)
						return false;
					}
						
					var data  = 'PK_CAMPUS='+$('#TERM_PK_CAMPUS').val()+'&PK_TERM_MASTER='+$('#COURSE_PK_TERM').val()+'&PK_COURSE='+$('#COURSE_PK_COURSE').val()+'&PK_COURSE_OFFERING='+$('#COURSE_PK_COURSE_OFFERING').val()+'&PK_SESSION='+$('#PK_SESSION').val()+'&SESSION_NO='+$('#SESSION_NO').val()+'&PK_COURSE_OFFERING_STATUS='+$('#PK_COURSE_OFFERING_STATUS').val()+'&show_check=1'; //Ticket # 1552;;
					var value = $.ajax({
						url: "ajax_search_course_offering_for_reports",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							document.getElementById('student_div').innerHTML = data
							show_btn()
						}		
					}).responseText;
				} else {
					if(document.getElementById('REPORT_TYPE').value == 10) {
						//DIAM-1753
						var valid = new Validation('form1', {onSubmit:false});
						var result = valid.validate();
						if(result==true){

						if($('#PK_COURSE_OFFERING_GPA1').length==0)
							{
								var PK_COURSE_OFFERING_GPA = $('#PK_COURSE_OFFERING_GPA').val();
							}
							else
							{
								var PK_COURSE_OFFERING_GPA = $('#PK_COURSE_OFFERING_GPA1').val();
							}

							//var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_COURSE='+$('#PK_COURSE').val()+'&PK_COURSE_OFFERING='+PK_COURSE_OFFERING_GPA+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_CAMPUS_GPA='+$('#PK_CAMPUS_GPA').val()+'&show_check=1&show_count=1&group_by=&ENROLLMENT=1'+'&SEARCH_TXT='+$('#SEARCH_TXT').val(); //Ticket # 1552;
							//DIAM-1753
							var PK_TERM_MASTER_5 = '';
							var GRADE_TYPE = '';
							if($('#PK_TERM_MASTER_5').length!=0){
								var PK_TERM_MASTER_5 = $('#PK_TERM_MASTER_5').val();
							}
							if($('#FGA_FINAL_GRADE').val()!=""){
								var GRADE_TYPE='&GRADE_TYPE=FGAS';
							}
							//DIAM-1753
						var data  = 'PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM='+PK_TERM_MASTER_5+'&PK_COURSE_OFFERING='+PK_COURSE_OFFERING_GPA+'&FINAL_GRADE='+$('#FGA_FINAL_GRADE').val()+'&show_check=1&show_count=1&group_by=&ENROLLMENT=1&type=fga'+GRADE_TYPE;

						}else{
							return false;
						}
						//DIAM-1753
						error = "";
						//DIAM-1753
						/*if($('#FGA_COURSE_PK_TERM').val() == '') {
							if(error != '')
								error += "\n";
							error += "Please Select Term"
						}
					
						if($('#FGA_COURSE_PK_COURSE_OFFERING').val() == '') {
							if(error != '')
								error += "\n";
							error += "Please Select Course Offering"
						}*/ 																	
						// //DIAM-1753
						
						// if($('#FGA_FINAL_GRADE').val() == '') {
						// 	if(error != '')
						// 		error += "\n";
						// 	error += "Please Select Grade"
						// }
						
						// if(error != ""){
						// 	alert(error)
						// 	return false;
						// }
					} else{

						if(document.getElementById('REPORT_TYPE').value == 2
						){

							if($('#PK_COURSE_OFFERING_GPA1').length==0)
							{
								var PK_COURSE_OFFERING_GPA = $('#PK_COURSE_OFFERING_GPA').val();
							}
							else
							{
								var PK_COURSE_OFFERING_GPA = $('#PK_COURSE_OFFERING_GPA1').val();
							}
							try {
								var ENROLLMENT_TYPE_RO_2 = $('#ENROLLMENT_TYPE').val();
							} catch (error) {
								// do nothing
							}
							if(ENROLLMENT_TYPE_RO_2 != 2){
								ENROLLMENT_TYPE_RO_2 = 1;
							}
							var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_COURSE='+$('#PK_COURSE').val()+'&PK_COURSE_OFFERING='+PK_COURSE_OFFERING_GPA+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_CAMPUS_GPA='+$('#PK_CAMPUS_GPA').val()+'&show_check=1&show_count=1&group_by=&ENROLLMENT='+ENROLLMENT_TYPE_RO_2+'&SEARCH_TXT='+$('#SEARCH_TXT').val(); //Ticket # 1552;
						}
						else
						{

							var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_COURSE='+$('#PK_COURSE').val()+'&PK_COURSE_OFFERING='+$('#PK_COURSE_OFFERING').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&show_check=1&show_count=1&group_by=&ENROLLMENT=1'+'&SEARCH_TXT='+$('#SEARCH_TXT').val(); //Ticket # 1552;
						}

					} //DIAM-1419
						
					var value = $.ajax({
						url: "ajax_search_student_for_reports",	
						type: "POST",		 
						data: data,		
						async: true,
						cache: false,
						beforeSend: function() {
							if(document.getElementById('REPORT_TYPE').value == 10)
							   document.getElementById('loaders').style.display = 'block';
							
						},
						success: function (data) {	
							document.getElementById('student_div').innerHTML = data
							show_btn()
							document.getElementById('loaders').style.display = 'none';
						}		
					}).responseText;
				}
			});
		}
		/* Ticket # 1472 */
		
		function fun_select_all(){
			var str = '';
			if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
				str = true;
			else
				str = false;
			
			if(document.getElementById('REPORT_TYPE').value == 9) {
				var SEL_PK_COURSE_OFFERING = document.getElementsByName('SEL_PK_COURSE_OFFERING[]')
				for(var i = 0 ; i < SEL_PK_COURSE_OFFERING.length ; i++){
					SEL_PK_COURSE_OFFERING[i].checked = str
				}
			} else {
				var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
				for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
					PK_STUDENT_ENROLLMENT[i].checked = str
				}
			}
			
			get_count()
		}
		function get_count(){
			var tot = 0
			if(document.getElementById('REPORT_TYPE').value == 9) {
				var SEL_PK_COURSE_OFFERING = document.getElementsByName('SEL_PK_COURSE_OFFERING[]')
				for(var i = 0 ; i < SEL_PK_COURSE_OFFERING.length ; i++){
					if(SEL_PK_COURSE_OFFERING[i].checked == true)
						tot++;
				}
			} else {
				var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
				for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
					if(PK_STUDENT_ENROLLMENT[i].checked == true)
						tot++;
				}
			}
			document.getElementById('SELECTED_COUNT').innerHTML = tot
			show_btn()
		}
		
		function show_btn(){
			
			var flag = 0;
			if(document.getElementById('REPORT_TYPE').value == 9) {
				var SEL_PK_COURSE_OFFERING = document.getElementsByName('SEL_PK_COURSE_OFFERING[]')
				for(var i = 0 ; i < SEL_PK_COURSE_OFFERING.length ; i++){
					if(SEL_PK_COURSE_OFFERING[i].checked == true) {
						flag++;
						break;
					}
				}
			} else {
				var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
				for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
					if(PK_STUDENT_ENROLLMENT[i].checked == true) {
						flag++;
						break;
					}
				}
			}
			
			if(flag == 1) {		
				
				if(document.getElementById('REPORT_TYPE').value == 10){
					document.getElementById('btn_gpa1').style.display 	= 'inline';
					document.getElementById('btn_gpa2').style.display 	= 'inline';

				}else{
				document.getElementById('btn_1').style.display = 'inline';
				if(document.getElementById('REPORT_TYPE').value == 2 || document.getElementById('REPORT_TYPE').value == 9 ) 
					document.getElementById('btn_4').style.display 	= 'inline';
				}
			} else {
				if(document.getElementById('REPORT_TYPE').value == 10){
					document.getElementById('btn_gpa1').style.display 	= 'none';
					document.getElementById('btn_gpa2').style.display 	= 'none';
					document.getElementById('btn_1').style.display 	= 'none';
				}else{
				if(document.getElementById('REPORT_TYPE').value == 3 || document.getElementById('REPORT_TYPE').value == 12)// DIAM-1599
					document.getElementById('btn_1').style.display = 'inline';
				else
					document.getElementById('btn_1').style.display = 'none';
				if(document.getElementById('REPORT_TYPE').value == 2 || document.getElementById('REPORT_TYPE').value == 9 ) 
					document.getElementById('btn_4').style.display 	= 'none';
				}
			}
		}
		
		/* Ticket # 1219 Ticket # 1472 */
		function show_filters(val){
			document.getElementById('ENROLLMENT_TYPE_div').style.display 	= 'none';
			document.getElementById('EXCLUDE_TRANSFERS_COURSE_DIV').style.display 	= 'none';
			document.getElementById('SHOW_div').style.display 						= 'none';
			document.getElementById('GRADE_BOOK_REPORT_TYPE_div').style.display 	= 'none';
			document.getElementById('REPORT_OPTION_DIV').style.display 				= 'none';
			document.getElementById('PK_CAMPUS_DIV').style.display 					= 'none';
			
			document.getElementById('PK_STUDENT_STATUS_DIV').style.display 			= 'none';
			document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display 			= 'none';
			document.getElementById('PK_TERM_MASTER_DIV').style.display 			= 'none';
			document.getElementById('PK_STUDENT_GROUP_DIV').style.display 			= 'none';
			document.getElementById('PK_COURSE_DIV').style.display 					= 'none';
			document.getElementById('PK_COURSE_OFFERING_DIV').style.display 		= 'none';
			document.getElementById('PK_TERM_MASTER_1_DIV').style.display 			= 'none';
			
			document.getElementById('PK_COURSE_1_DIV').style.display 				= 'none';
			document.getElementById('PK_COURSE_OFFERING_1_DIV').style.display 		= 'none';
			document.getElementById('btn_2').style.display 							= 'none';
			document.getElementById('btn_3').style.display 							= 'none';
			document.getElementById('btn_div_1').style.display 						= 'none'; //Ticket # 527
			document.getElementById('btn_div_GPAS1').style.display 						= 'none'; //DIAM-1753
			
			document.getElementById('PK_COURSE_OFFERING_1').className = 'form-control';
			
			document.getElementById('PK_SESSION_DIV').style.display 				= 'none';
			document.getElementById('SESSION_NO_DIV').style.display 				= 'none';
			document.getElementById('PK_COURSE_OFFERING_STATUS_DIV').style.display 	= 'none';
			document.getElementById('TERM_PK_CAMPUS_DIV').style.display 			= 'none';
			document.getElementById('COURSE_PK_TERM_DIV').style.display 			= 'none';
			document.getElementById('COURSE_PK_COURSE_DIV').style.display 			= 'none'; //Ticket # 1979
			document.getElementById('COURSE_PK_COURSE_OFFERING_DIV').style.display 	= 'none'; //Ticket # 1979
			document.getElementById('student_div').innerHTML						= '';
			document.getElementById('search_btn').style.display	 					= 'inline';
			
			/* Ticket # 1361  */
			document.getElementById('MIN_GPA_div').style.display 							= 'none';
			document.getElementById('MAX_GPA_div').style.display 							= 'none';
			document.getElementById('btn_4').style.display 									= 'none';
			/* Ticket # 1361  */
			
			document.getElementById('SEARCH_TXT_DIV').style.display 	= 'none'; //Ticket # 1552 
			document.getElementById('SEARCH_TXT').value 				= ''; //Ticket # 1552 
			
			document.getElementById('FGA_COURSE_PK_TERM_DIV').style.display 			= 'none';
			document.getElementById('FGA_COURSE_COURSE_OFFERING_DIV').style.display 	= 'none';
			document.getElementById('FGA_FINAL_GRADE_DIV').style.display 				= 'none';
			document.getElementById('FGA_COURSE_PK_TERM').className 					= 'form-control';
			document.getElementById('FGA_COURSE_PK_COURSE_OFFERING').className 			= 'form-control';
			document.getElementById('FGA_FINAL_GRADE').className 						= 'form-control';
			
			/* Ticket # 1979 */
			document.getElementById('GS_PK_CAMPUS_DIV').style.display 			= 'none';
			document.getElementById('GS_PK_TERM_DIV').style.display 			= 'none';
			document.getElementById('GS_PK_COURSE_DIV').style.display 			= 'none';
			document.getElementById('GS_PK_COURSE_OFFERING_DIV').style.display 	= 'none';
			/* Ticket # 1979 */
			document.getElementById('PK_CAMPUS_DIV_GPA').style.display 			= 'none'; // DIAM-1419
			document.getElementById('PK_COURSE_OFFERING_GPA_DIV').style.display 			= 'none'; // DIAM-1419
			document.getElementById('TERM_DATE_GPA_DIV').style.display 			= 'none'; // DIAM-1419
			
			show_btn()
			
			if(val == 1) {
				document.getElementById('SHOW_div').style.display 						= 'block';
				document.getElementById('EXCLUDE_TRANSFERS_COURSE_DIV').style.display 	= 'block';
				document.getElementById('REPORT_OPTION_DIV').style.display 				= 'block';
				
				document.getElementById('PK_STUDENT_STATUS_DIV').style.display 			= 'block';
				document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display 			= 'block';
				document.getElementById('PK_TERM_MASTER_DIV').style.display 			= 'block';
				document.getElementById('PK_STUDENT_GROUP_DIV').style.display 			= 'block';
				document.getElementById('PK_COURSE_DIV').style.display 					= 'block';
				document.getElementById('PK_COURSE_OFFERING_DIV').style.display 		= 'block';
				
				document.getElementById('SEARCH_TXT_DIV').style.display 	= 'block'; // Ticket # 1552
			} else if(val == 2) {
				document.getElementById('ENROLLMENT_TYPE_div').style.display 	= 'block';
				document.getElementById('EXCLUDE_TRANSFERS_COURSE_DIV').style.display = 'block';
				
				document.getElementById('PK_STUDENT_STATUS_DIV').style.display 			= 'block';
				document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display 			= 'block';
				document.getElementById('PK_TERM_MASTER_DIV').style.display 			= 'block';
				document.getElementById('PK_STUDENT_GROUP_DIV').style.display 			= 'block';
				//document.getElementById('PK_COURSE_DIV').style.display 					= 'block'; // DIAM-1419
				//document.getElementById('PK_COURSE_OFFERING_DIV').style.display 		= 'block'; // DIAM-1419
				document.getElementById('PK_CAMPUS_DIV_GPA').style.display 			= 'block'; // DIAM-1419
				document.getElementById('PK_COURSE_OFFERING_GPA_DIV').style.display 			= 'block'; // DIAM-1419
				document.getElementById('TERM_DATE_GPA_DIV').style.display 			= 'block'; // DIAM-1419

				/* Ticket # 1361  */
				document.getElementById('MIN_GPA_div').style.display 					= 'block';
				document.getElementById('MAX_GPA_div').style.display 					= 'block';
				document.getElementById('PK_CAMPUS_DIV').style.display 					= 'block';
				/* Ticket # 1361  */
				//document.getElementById('term_begin_start_date').className 			= 'form-control date'; // DIAM-1753
				//document.getElementById('term_begin_end_date').className 			= 'form-control date'; // DIAM-1753
				if(document.getElementById('advice-required-entry-term_begin_start_date'))
				{
					document.getElementById('advice-required-entry-term_begin_start_date').remove(); // DIAM-1753
				}
				if(document.getElementById('advice-required-entry-term_begin_end_date'))
				{
					document.getElementById('advice-required-entry-term_begin_end_date').remove(); // DIAM-1753
				}

				document.getElementById('SEARCH_TXT_DIV').style.display 	= 'block'; // Ticket # 1552
			} else if(val == 3 || val == 12) { /* Ticket # 1772 */ // DIAM-1599
				/* Ticket # 1979 
				document.getElementById('PK_STUDENT_STATUS_DIV').style.display 			= 'block';
				document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display 			= 'block';
				document.getElementById('PK_TERM_MASTER_DIV').style.display 			= 'block';
				document.getElementById('PK_STUDENT_GROUP_DIV').style.display 			= 'block';
				document.getElementById('PK_COURSE_DIV').style.display 					= 'block';
				document.getElementById('PK_COURSE_OFFERING_DIV').style.display 		= 'block';
				document.getElementById('PK_CAMPUS_DIV').style.display 					= 'block';
				*/
				/* Ticket # 1772 */
				
				/* Ticket # 1979 */
				document.getElementById('GS_PK_CAMPUS_DIV').style.display 			= 'block';
				document.getElementById('GS_PK_TERM_DIV').style.display 			= 'block';
				document.getElementById('GS_PK_COURSE_DIV').style.display 			= 'block';
				document.getElementById('GS_PK_COURSE_OFFERING_DIV').style.display 	= 'block';
				document.getElementById('search_btn').style.display 				= 'none';
				if(val!= 12) //<!-- DIAM-1599 -->
					document.getElementById('btn_3').style.display 		= 'inline';
				else	
					document.getElementById('btn_1').style.display 						= 'inline';
				/* Ticket # 1979 */
			} else if(val == 4) {
				document.getElementById('GRADE_BOOK_REPORT_TYPE_div').style.display = 'block';
				
				document.getElementById('PK_STUDENT_STATUS_DIV').style.display 			= 'block';
				document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display 			= 'block';
				document.getElementById('PK_TERM_MASTER_DIV').style.display 			= 'block';
				document.getElementById('PK_STUDENT_GROUP_DIV').style.display 			= 'block';
				document.getElementById('PK_COURSE_DIV').style.display 					= 'block';
				document.getElementById('PK_COURSE_OFFERING_DIV').style.display 		= 'block';
				
				document.getElementById('SEARCH_TXT_DIV').style.display 	= 'block'; // Ticket # 1552
			} else if(val == 6 || val == 11) { //<!-- DIAM-2233 -->
				document.getElementById('PK_CAMPUS_DIV').style.display 				= 'block';
				document.getElementById('PK_TERM_MASTER_1_DIV').style.display 		= 'block';
				document.getElementById('PK_COURSE_1_DIV').style.display 			= 'block';
				document.getElementById('PK_COURSE_OFFERING_1_DIV').style.display 	= 'block';
				document.getElementById('student_div').innerHTML 					= ''
				
				document.getElementById('btn_div_1').style.display 	= 'block'; //Ticket # 527
				document.getElementById('btn_2').style.display 		= 'inline';
				document.getElementById('btn_3').style.display 		= 'inline';
				document.getElementById('search_btn').style.display = 'none';
			} else if(val == 7) { /* Ticket # 527 */
				document.getElementById('PK_CAMPUS_DIV').style.display 					= 'block';
				document.getElementById('PK_STUDENT_STATUS_DIV').style.display 			= 'block';
				document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display 			= 'block';
				document.getElementById('PK_TERM_MASTER_DIV').style.display 			= 'block';
				document.getElementById('PK_STUDENT_GROUP_DIV').style.display 			= 'block';
				document.getElementById('PK_COURSE_DIV').style.display 					= 'block';
				document.getElementById('PK_COURSE_OFFERING_DIV').style.display 		= 'block';
				document.getElementById('EXCLUDE_TRANSFERS_COURSE_DIV').style.display 	= 'block';
				
				document.getElementById('SEARCH_TXT_DIV').style.display 	= 'block'; // Ticket # 1552
			} /* Ticket # 527 */
			else if(val == 8) { /* Ticket # 1574 */
				document.getElementById('PK_CAMPUS_DIV').style.display 					= 'block';
				document.getElementById('PK_TERM_MASTER_DIV').style.display 			= 'block';
				document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display 			= 'block';
				document.getElementById('PK_STUDENT_STATUS_DIV').style.display 			= 'block';
				document.getElementById('PK_STUDENT_GROUP_DIV').style.display 			= 'block';
			} else if(val == 9) { /* Ticket # 1471 */
				document.getElementById('TERM_PK_CAMPUS_DIV').style.display 			= 'block';
				document.getElementById('COURSE_PK_TERM_DIV').style.display 			= 'block';
				document.getElementById('COURSE_PK_COURSE_DIV').style.display 			= 'block'; // Ticket # 1979
				document.getElementById('COURSE_PK_COURSE_OFFERING_DIV').style.display 	= 'block'; // Ticket # 1979
				document.getElementById('PK_COURSE_OFFERING_STATUS_DIV').style.display 	= 'block';
				document.getElementById('PK_SESSION_DIV').style.display 				= 'block';
				document.getElementById('SESSION_NO_DIV').style.display 				= 'block';
			} else if(val == 10) { 
				document.getElementById('PK_CAMPUS_DIV').style.display 					= 'block';
				document.getElementById('PK_TERM_MASTER_DIV').style.display 			= 'block';
				document.getElementById('PK_CAMPUS_PROGRAM_DIV').style.display 			= 'block';
				document.getElementById('PK_STUDENT_STATUS_DIV').style.display 			= 'block';
				document.getElementById('PK_STUDENT_GROUP_DIV').style.display 			= 'block';
				
				document.getElementById('FGA_COURSE_PK_TERM_DIV').style.display 			= 'none'; // DIAM-1753
				document.getElementById('FGA_COURSE_COURSE_OFFERING_DIV').style.display 	= 'none'; // DIAM-1753
				document.getElementById('FGA_FINAL_GRADE_DIV').style.display 				= 'block';

				document.getElementById('PK_CAMPUS_DIV_GPA').style.display 			= 'none'; // DIAM-1753
				document.getElementById('PK_COURSE_OFFERING_GPA_DIV').style.display 			= 'block'; // DIAM-1753
				document.getElementById('TERM_DATE_GPA_DIV').style.display 			= 'block'; // DIAM-1753
				//document.getElementById('term_begin_start_date').className 			= 'form-control date required-entry'; // DIAM-1753
				//document.getElementById('term_begin_end_date').className 			= 'form-control date required-entry'; // DIAM-1753
				document.getElementById('btn_div_GPAS1').style.display 	= 'block'; //DIAM-1753
				document.getElementById('search_btn').style.display	 					= 'none';//DIAM-1753
				document.getElementById('FGA_COURSE_PK_TERM').className 					= 'form-control required-entry';
				document.getElementById('FGA_COURSE_PK_COURSE_OFFERING').className 			= 'form-control required-entry';
				document.getElementById('FGA_FINAL_GRADE').className 						= 'form-control required-entry';
			} 
		}
		/* Ticket # 1219 Ticket # 1472 */
		
		/* Ticket # 1472 */
		function get_course_offering_1(){
			jQuery(document).ready(function($) {
				if($('#PK_TERM_MASTER_1').val() != '' && $('#PK_COURSE_1').val() != '') {
					var data  = 'PK_TERM_MASTER='+$('#PK_TERM_MASTER_1').val()+'&PK_COURSE='+$('#PK_COURSE_1').val();
					var value = $.ajax({
						url: "ajax_get_course_offering_from_term",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							data = data.replace('PK_COURSE_OFFERING"', 'PK_COURSE_OFFERING_1"');
							data = data.replace('PK_COURSE_OFFERING"', 'PK_COURSE_OFFERING_1"');
							document.getElementById('PK_COURSE_OFFERING_1_DIV').innerHTML = data
							document.getElementById('PK_COURSE_OFFERING_1').className = 'form-control required-entry';
						}		
					}).responseText;
				}
			});
		}
		
		function get_course_offering_from_term(){
			jQuery(document).ready(function($) {
				if($('#FGA_COURSE_PK_TERM').val() != '') {
					var data  = 'PK_TERM_MASTER='+$('#FGA_COURSE_PK_TERM').val();
					var value = $.ajax({
						url: "ajax_get_course_offering_from_term",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							data = data.replace('id="PK_COURSE_OFFERING"', 'id="FGA_COURSE_PK_COURSE_OFFERING"');
							document.getElementById('FGA_COURSE_COURSE_OFFERING_DIV').innerHTML = data
							document.getElementById('FGA_COURSE_PK_COURSE_OFFERING').className = 'required-entry';
							
							document.getElementById('FGA_COURSE_PK_COURSE_OFFERING').setAttribute('multiple', true);
							document.getElementById('FGA_COURSE_PK_COURSE_OFFERING').name = "FGA_COURSE_PK_COURSE_OFFERING[]"
							$("#FGA_COURSE_PK_COURSE_OFFERING option[value='']").remove();
							
							document.getElementById('FGA_COURSE_PK_COURSE_OFFERING').setAttribute("onchange", "clear_search()");
							
							$('#FGA_COURSE_PK_COURSE_OFFERING').multiselect({
								includeSelectAllOption: true,
								allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
								nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
								numberDisplayed: 2,
								nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
							});
						}		
					}).responseText;
				}
			});
		}
		
		function get_course_details(){
		}
		function submit_form(val){
			jQuery(document).ready(function($) {
				var valid = new Validation('form1', {onSubmit:false});
				var result = valid.validate();
				document.getElementById('FORMAT').value = val
				show_only_selected();
				var RT = document.getElementById('REPORT_TYPE').value;
				if(result == true){ 
					if(RT == 1 || RT == 2 || RT == 4 || RT == 7 || RT == 8 || RT == 9 || RT == 10){
						var serialized_array = $('#form1').serialize();
							// console.log(serialized_array);
							var value = $.ajax({
							url: "grade_report",	
							type: "POST",		 
							data: serialized_array,		
							async: true,
							cache: false,
							beforeSend: function() {
								document.getElementById('loaders').style.display = 'block'; 
							},
							success: function (data) {	
								/* Ticket # 1979 */
								// console.log("success");
								// console.log(data);
								const text = window.location.href;
								const word = '/school';
								const textArray = text.split(word); // ['This is ', ' text...']
								const result = textArray.shift();

								downloadDataUrlFromJavascript(data.filename, result + '/school/' + data.path);
								search();
								document.getElementById('loaders').style.display = 'none'; 

							}		
						}).responseText;
					return;
					}
					
					
					document.form1.submit();
					if(RT!=12) // DIAM-1599
						search();
				}
			});
		}
		function downloadDataUrlFromJavascript(filename, dataUrl) {

			// Construct the 'a' element
			var link = document.createElement("a");
			link.download = filename;
			link.target = "_blank";

			// Construct the URI
			link.href = dataUrl;
			document.body.appendChild(link);
			link.click();

			// Cleanup the DOM
			document.body.removeChild(link);
			delete link;
		}
		/* Ticket # 1472 */
		
		function get_course_term_from_campus(){
			jQuery(document).ready(function($) {
				/* Ticket # 1979 */
				if(document.getElementById('REPORT_TYPE').value == 3 || document.getElementById('REPORT_TYPE').value == 12) { // DIAM-1599
					var data  = 'PK_CAMPUS='+$('#GS_PK_CAMPUS').val();
				} else {
					var data  = 'PK_CAMPUS='+$('#TERM_PK_CAMPUS').val();
				}
				/* Ticket # 1979 */
					
				var value = $.ajax({
					url: "ajax_get_term_from_campus",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						/* Ticket # 1979 */
						if(document.getElementById('REPORT_TYPE').value == 3 || document.getElementById('REPORT_TYPE').value == 12) { // DIAM-1599
							var term_id = 'GS_PK_TERM';
						} else {
							var term_id = 'COURSE_PK_TERM';
						}
						/* Ticket # 1979 */
						
						data = data.replace('id="PK_TERM_MASTER"', 'id="'+term_id+'"');
						document.getElementById(term_id+'_DIV').innerHTML 	= data;
						document.getElementById(term_id).className 			= '';
						document.getElementById(term_id).name 				= term_id+"[]"
						document.getElementById(term_id).setAttribute('multiple', true);
						document.getElementById(term_id).setAttribute("onchange", "get_course_from_term()");
						
						$("#"+term_id+" option[value='']").remove();
						
						$('#'+term_id).multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=TERM?>',
							nonSelectedText: '<?=TERM?>',
							numberDisplayed: 2,
							nSelectedText: '<?=TERM?> selected'
						});
						
					}		
				}).responseText;
			});
		}
		
		function get_course_from_term(){
			jQuery(document).ready(function($) {
			
				/* Ticket # 1979 */
				if(document.getElementById('REPORT_TYPE').value == 3 || document.getElementById('REPORT_TYPE').value == 12) { // DIAM-1599
					var data  = 'PK_TERM='+$('#GS_PK_TERM').val();
				} else {
					var data  = 'PK_TERM='+$('#COURSE_PK_TERM').val();
				}
				/* Ticket # 1979 */
				
				var value = $.ajax({
					url: "ajax_get_course_from_term",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						/* Ticket # 1979 */
						if(document.getElementById('REPORT_TYPE').value == 3 || document.getElementById('REPORT_TYPE').value == 12) { // DIAM-1599
							var course_id = 'GS_PK_COURSE';
						} else {
							var course_id = 'COURSE_PK_COURSE';
						}
						/* Ticket # 1979 */
						
						data = data.replace('id="PK_COURSE"', 'id="'+course_id+'"');
						document.getElementById(course_id+'_DIV').innerHTML 		= data;
						document.getElementById(course_id).className 				= '';
						document.getElementById(course_id).name 					= course_id+"[]"
						document.getElementById(course_id).setAttribute('multiple', true);
						document.getElementById(course_id).setAttribute("onchange", "get_course_offering_2()");
						
						$("#"+course_id+" option[value='']").remove();
						
						$('#'+course_id).multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=COURSE?>',
							nonSelectedText: '<?=COURSE?>',
							numberDisplayed: 2,
							nSelectedText: '<?=COURSE?> selected'
						});
						
					}		
				}).responseText;
			});
		}
		function get_course_offering_2(){
			jQuery(document).ready(function($) { 
				/* Ticket # 1979 */
				if(document.getElementById('REPORT_TYPE').value == 3 || document.getElementById('REPORT_TYPE').value == 12) { // DIAM-1599
					var data  = 'PK_COURSE='+$('#GS_PK_COURSE').val()+'&multiple=1&PK_TERM_MASTER='+$('#GS_PK_TERM').val();
				} else {
					var data  = 'PK_COURSE='+$('#COURSE_PK_COURSE').val()+'&multiple=1&PK_TERM_MASTER='+$('#COURSE_PK_TERM').val();
				}
				/* Ticket # 1979 */
				
				var url	  = "ajax_get_course_offering_units_in_progress";
				
				var value = $.ajax({
					url: url,	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						
						/* Ticket # 1979 */
						if(document.getElementById('REPORT_TYPE').value == 3 || document.getElementById('REPORT_TYPE').value == 12) { // DIAM-1599
							var course_off_id = 'GS_PK_COURSE_OFFERING';
						} else {
							var course_off_id = 'COURSE_PK_COURSE_OFFERING';
						}
						
						
						data = data.replace('id="PK_COURSE_OFFERING"', 'id="'+course_off_id+'"');
						document.getElementById(course_off_id+'_DIV').innerHTML 	= data;
						document.getElementById(course_off_id).className 	  		= '';
						document.getElementById(course_off_id).setAttribute('multiple', true);
						document.getElementById(course_off_id).name = course_off_id+"[]"
						$("#"+course_off_id+" option[value='']").remove();
						
						if(document.getElementById('REPORT_TYPE').value == 3)
							document.getElementById(course_off_id).className = 'required-entry';
						
						document.getElementById(course_off_id).setAttribute("onchange", "clear_search()");
						
						$('#'+course_off_id).multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=COURSE_OFFERING?>',
							nonSelectedText: '<?=COURSE_OFFERING?>',
							numberDisplayed: 2,
							nSelectedText: '<?=COURSE_OFFERING?> selected'
						});
						
						/* Ticket # 1979 */
						
						var dd = document.getElementsByClassName('multiselect-native-select');
						for(var i = 0 ; i < dd.length ; i++){
							dd[i].style.width = '100%' ;
						}
					}		
				}).responseText;
			});
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_COURSE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_CODE?>',
			nonSelectedText: '<?=COURSE_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_CODE?> selected'
		});
		$('#PK_STUDENT_GROUP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=GROUP_CODE?>',
			nonSelectedText: '<?=GROUP_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=GROUP_CODE?> selected'
		});
		$('#PK_TERM_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FIRST_TERM?>',
			nonSelectedText: '<?=FIRST_TERM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=FIRST_TERM?> selected'
		});
		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROGRAM?>',
			nonSelectedText: '<?=PROGRAM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STATUS?>',
			nonSelectedText: '<?=STATUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STATUS?> selected'
		});
		$('#PK_COURSE_OFFERING').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
			nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
		});
		
		/* Ticket # 1472 */
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS ?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		/* Ticket # 1472 */
		
		$('#TERM_PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS ?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		$('#COURSE_PK_TERM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=TERM?>',
			nonSelectedText: '<?=TERM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=TERM?> selected'
		});
		
		$('#COURSE_PK_COURSE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE?>',
			nonSelectedText: '<?=COURSE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE?> selected'
		});
		
		$('#COURSE_PK_COURSE_OFFERING').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_OFFERING?>',
			nonSelectedText: '<?=COURSE_OFFERING?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_OFFERING?> selected'
		});
		
		$('#PK_SESSION').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=SESSION?>',
			nonSelectedText: '<?=SESSION?>',
			numberDisplayed: 2,
			nSelectedText: '<?=SESSION?> selected'
		});
		
		$('#PK_COURSE_OFFERING_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_OFFERING_STATUS_1?>',
			nonSelectedText: '<?=COURSE_OFFERING_STATUS_1?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_OFFERING_STATUS_1?> selected'
		});
		
		
		$('#FGA_COURSE_PK_TERM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=TERM?>',
			nonSelectedText: '<?=TERM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=TERM?> selected'
		});
		
		$('#FGA_COURSE_PK_COURSE_OFFERING').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_OFFERING?>',
			nonSelectedText: '<?=COURSE_OFFERING?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_OFFERING?> selected'
		});
		
		$('#FGA_FINAL_GRADE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=GRADE?>',
			nonSelectedText: '<?=GRADE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=GRADE?> selected'
		});
		
		/* Ticket # 1979 */
		$('#GS_PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS ?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
		$('#GS_PK_TERM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=TERM?>',
			nonSelectedText: '<?=TERM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=TERM?> selected'
		});
		
		$('#GS_PK_COURSE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE?>',
			nonSelectedText: '<?=COURSE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE?> selected'
		});
		
		$('#GS_PK_COURSE_OFFERING').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_OFFERING?>',
			nonSelectedText: '<?=COURSE_OFFERING?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_OFFERING?> selected'
		});
						
		/* Ticket # 1979 */
		/* DIAM-1419 */
		$('#PK_CAMPUS_GPA').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Course <?=CAMPUS?>',
			nonSelectedText: 'Course <?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?>'
		});
		$('#PK_COURSE_OFFERING_GPA').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_OFFERING?>',
			nonSelectedText: '<?=COURSE_OFFERING?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_OFFERING?> selected'
		});
		/* DIAM-1419 */
		
	});
	</script>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
	//DIAM-1419

	function get_course_offeringgpa(val) {
			jQuery(document).ready(function($) {
				//document.getElementById('loaders').style.display = 'block';
				//set_notification=false; // DIAM-1753
				 if (val== 1) { 
					var data = 'PK_TERM_MASTER=' + $('#PK_TERM_MASTER_5').val() + '&dont_show_term=2' + '&PK_CAMPUS=' + $('#PK_CAMPUS_GPA').val();
					var url = "ajax_get_course_offering_from_term"; /* Ticket # 1341   */
				}else {
					//var data = 'val=' + $('#PK_COURSE').val() + '&multiple=0';
					//var url = "ajax_get_course_offering";
				}

				var value = $.ajax({
					url: url,
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						//alert(data)
						 if (val == 1) { 
							data = data.replace('id="PK_COURSE_OFFERING"', 'id="PK_COURSE_OFFERING_GPA1"');
							document.getElementById('PK_COURSE_OFFERING_GPA_DIV').innerHTML = data;

							if(document.getElementById('REPORT_TYPE').value != 10) {
							document.getElementById('PK_COURSE_OFFERING_GPA1').className = 'required-entry';
							}
							document.getElementById('PK_COURSE_OFFERING_GPA1').setAttribute('multiple', true);
							document.getElementById('PK_COURSE_OFFERING_GPA1').name = "PK_COURSE_OFFERING_GPA1[]"
							$("#PK_COURSE_OFFERING_GPA1 option[value='']").remove();

							$('#PK_COURSE_OFFERING_GPA1').multiselect({
								includeSelectAllOption: true,
								allSelectedText: 'All <?= COURSE_OFFERING ?>',
								nonSelectedText: '<?= COURSE_OFFERING ?>',
								numberDisplayed: 2,
								nSelectedText: '<?= COURSE_OFFERING ?> selected'
							});

						}

						var dd = document.getElementsByClassName('multiselect-native-select');
						for (var i = 0; i < dd.length; i++) {
							dd[i].style.width = '100%';
						}
						document.getElementById('loaders').style.display = 'none';
						set_notification=true; // DIAM-1753

					}
				}).responseText;
			});
		}
	
	function get_term_from_campus_by_date(report_type) {

		jQuery(document).ready(function($) {
			if ($('#term_begin_start_date').val() === '' || $('#term_begin_end_date').val() === '') return false;
			document.getElementById('loaders').style.display = 'block';
			set_notification=false; // DIAM-1753

			var PK_CAMPUS = '';
			if (report_type == 1) { 
				PK_CAMPUS = $('#PK_CAMPUS_GPA').val();
			} 

			var data = 'report_type=' + report_type + '&PK_CAMPUS=' + PK_CAMPUS + '&TREM_BEGIN_START_DATE=' + $('#term_begin_start_date').val() + '&TREM_BEGIN_END_DATE=' + $('#term_begin_end_date').val() + '&TREM_END_START_DATE=' + $('#term_end_start_date').val() + '&TREM_END_END_DATE=' + $('#term_end_end_date').val();
			var value = $.ajax({
				url: "ajax_get_attendance_term_from_campus_by_date",
				type: "POST",
				data: data,
				async: false,
				cache: false,
				success: function(data) {
						document.getElementById('PK_TERM_MASTER_5_DIV').innerHTML = data;
						get_course_offeringgpa(report_type);
						//set_notification=true; // DIAM-1753
						//document.getElementById('loaders').style.display = 'none';
				}
			});

		});

	}

	function show_only_selected(){
		//RUN DELETE ONLY IF ANY SINGLE IS SELECTED  
		//alert(jQuery(".delete_if_not_selected:checked").length);
		if( jQuery(".delete_if_not_selected:checked").length> 0)
		{
			jQuery(".delete_if_not_selected:not(:checked)").parent().parent().remove();
		} 
	}

	//DIAM-1419
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});

		//DIAM-1419
		$('#term_begin_start_date').datepicker({
			autoclose: true,
			todayHighlight: true,
			orientation: "bottom auto"
			}).on('change', function(sdate) {
			get_term_from_campus_by_date(1);
		});

		$('#term_begin_end_date').datepicker({
			autoclose: true,
			todayHighlight: true,
			orientation: "bottom auto"
			}).on('change', function(edate) {
			get_term_from_campus_by_date(1);
		});
		//DIAM-1419

	});
	</script>
</body>

</html>
