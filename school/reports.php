<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(has_report_access() == 0){
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
	<title><?=MNU_REPORTS?> | <?=$title?></title>
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
							<?=MNU_REPORTS?>
						</h4>
                    </div>
                </div>
				
                <div class="row">
					<div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									
									<? if(check_access('REPORT_ADMISSION') == 1){ ?>
									<div class="col-md-3">
										<?=MNU_ADMISSION?> 
										<ul >
											<li><a href="admissions_rep_statistics" ><?=MNU_ADMISSIONS_REP_STATISTICS?></a></li>
											<li><a href="lead_source_statistics" ><?=MNU_LEAD_SOURCE_STATISTICS?></a></li>
											<li><a href="lead_task_report"><?=MNU_LEAD_TASK_REPORT?></a></li>
										</ul>
									</div>
									<? } ?>
									
									<? if(check_access('REPORT_REGISTRAR') == 1){ ?>
									<div class="col-md-3">	
										<?=MNU_REGISTRAR?> 
										<ul >
											<li><a href="academic_calendar_pdf" ><?=MNU_ACADEMIC_CALENDAR?></a></li>
											<li><a href="attendance_reports" ><?=MNU_ATTENDANCE ?></a></li> <!-- Ticket # 1194 -->
											<li><a href="course_report" ><?=MNU_COURSE ?></a></li>
											<!-- <li><a href="course_offering_grade_book_transcript_report" ><?=MNU_COURSE_OFFERING_GRADE_BOOK_TRANSCRIPT ?></a></li> Ticket #1193-->
											<li><a href="drop_report" ><?=MNU_DROP_REPORT ?></a></li>
											<!-- DIAM-1199 -->
											<?php if (check_access('REPORT_REGISTRAR') == 1 && has_wvjc_access($_SESSION['PK_ACCOUNT'])) { ?>
											<li><a href="student_enrollment_review" ><?=MNU_ENROLLMENT_REVIEW?></a></li> 
											<? } ?>
											<!-- DIAM-1199 -->
											<!-- DIAM-1313-->
											<?php if (check_access('REPORT_REGISTRAR') == 1 && has_wvjc_access($_SESSION['PK_ACCOUNT'])) { ?>
											<li><a href="enrollment_status_by_term" ><?=MNU_ENROLLMENT_STATUS_BY_TERM?></a></li>
											<? } ?>
											<!-- DIAM-1313-->
											<li><a href="expected_graduates" ><?=MNU_EXPECTED_GRADUATES?></a></li>
											<li><a href="grade_report" ><?=MNU_GRADES?></a></li> <!-- Ticket # 1195 -->
											<li><a href="graduates" ><?=MNU_GRADUATES?></a></li>
											<li><a href="instructor_schedule" ><?=MNU_INSTRUCTOR_SCHEDULE?></a></li>
											<li><a href="last_day_in_class_report" ><?=MNU_LAST_DAY_IN_CLASS?></a></li>
											<li><a href="loa_report" ><?=MNU_LOA_REPORT?></a></li>
											<li><a href="other_education_report" ><?=MNU_OTHER_EDUCATION ?></a></li> <!-- Ticket # 1572 -->
											
											<li><a href="probation_report" ><?=MNU_PROBATION_REPORT?></a></li><!-- Ticket #1469 -->
											<!-- <li><a href="program_course_progress_report" ><?=MNU_PROGRAM_COURSE_PROGRESS?></a></li>Ticket #1574 -->
											
											<li><a href="repeat_course" ><?=MNU_REPEAT_COURSE?></a></li><!-- Ticket #1710 -->
											<li><a href="student_report_card" ><?=MNU_REPORT_CARD?></a></li>
											<li><a href="requirement_report" ><?=MNU_REQUIREMENTS?></a></li>											
											<li><a href="students_by_status" ><?=MNU_STUDENTS_BY_STATUS?></a></li><!-- Ticket #1200 -->
											<li><a href="student_schedule_report" ><?=MNU_STUDENT_SCHEDULE?></a></li>
											<li><a href="student_summar_pdf" ><?=MNU_STUDENT_SUMMARY?></a></li>
											<li><a href="student_tests" ><?=MNU_STUDENT_TESTS?></a></li><!-- Ticket # 1546  -->
											<!--<li><a href="student_transcript"><?=STUDENT_TRANSCRIPT?></a></li> Ticket #1193-->
											<!--<li><a href="student_transcript_list"><?=STUDENT_TRANSCRIPT_LIST?></a></li> Ticket #1193-->
											<!--<li><a href="student_transcript_list_numeric_grade"><?=STUDENT_TRANSCRIPT_LIST_NUMBER_GRADE?></a></li> Ticket #1193-->
											<li><a href="transcripts"><?=MNU_TRANSCRIPTS?></a></li><!-- Ticket # 1193 -->
											<li><a href="transfer_credit_report"><?=MNU_TRANSFER_CREDIT?></a></li><!-- Ticket # 1571 -->
											<!--<li><a href="student_transcript?uno=1"><?=STUDENT_UNOFFICIAL_TRANSCRIPT?></a></li> Ticket #1193-->
											<?
											// if(av_check_access('sap_report'))
											// {
											?>
											<li><a href="sap_global_report" >SAP Lite</a></li><!--  DIMA-23 -->
											<li><a href="units_attempted_by_term"><?=MNU_UNITS_ATTEMPTED_BY_TERM?></a></li><!-- // DIAM-2195 -->
											<?
											// }
											?>
											
										</ul>
									</div>
									<? } ?>

                                    <? if(check_access('REPORT_FINANCE') == 1){ ?>
                                    <div class="col-md-3">
                                        <?=MNU_FINANCE?>
                                        <ul >
                                            <li><a href="balance_sheet"><?=MNU_BALANCE_SHEET?></a></li>
                                            <!-- *** NUEVO: REPORTE CAMPUS IVY *** -->
                                            <?php if($_SESSION['PK_ACCOUNT'] == 63) { ?>
                                                <li><a href="campus_ivy_sync_report">Campus Ivy Sync Report</a></li>
                                            <?php } ?>
                                            <!-- *** FIN NUEVO *** -->
                                            <li><a href="disbursed_funds"><?=MNU_DISBUSED_FUNDS?></a></li>
                                            <li><a href="packaging_summary"><?=MNU_PACKAGING_SUMMARY?></a></li>
                                            <li><a href="payments_due_report"><?=MNU_PAYMENT_DUE_1?></a></li>
                                            <li><a href="projected_funds"><?=MNU_PROJECTED_FUNDS?></a></li>
                                            <li><a href="repackage_date"><?=MNU_REPACKAGE_DATE?></a></li>
                                            <li><a href="title_iv"><?=MNU_TITLE_IV_RECIPIENTS?></a></li>
                                            <li><a href="title_iv_detail"><?=MNU_TITLE_IV_RECIPIENTS_DETAIL?></a></li>
                                            <li><a href="title_iv_student_info"><?=MNU_TITLE_IV_RECIPIENTS_STUDENT_INFO?></a></li>
                                            <li><a href="title_iv_recipients_by_category_report"><?=MNU_TITLE_IV_RECIPIENTS_BY_CATEGORY?></a></li>
                                            <li><a href="manage_title_iv_recipients_by_category_setup"><?=MNU_TITLE_IV_RECIPIENTS_BY_CATEGORY_SETUP?></a></li>
                                            <li><a href="unapproved_disbursements"><?=MNU_UNAPPROVED_DISBURSEMENT?></a></li>
                                        </ul>
                                    </div>
                                    <? } ?>
									
									
									<? if(check_access('REPORT_ACCOUNTING') == 1){ ?>
									<div class="col-md-3">	
										<?=MNU_ACCOUNTING?> 
										<ul >
											<li><a href="account_ledger_report"><?=MNU_ACCOUNTING_LEDGER_EXPORT ?></a></li>
											<li><a href="ledger_worksheet"><?=MNU_LEDGER_WORKSHEET ?></a></li><!-- Ticket # 1625 -->
											<li><a href="negative_balance"><?=MNU_NEGATIVE_BALANCE?></a></li>
											<li><a href="past_due"><?=MNU_PAST_DUE?></li>
											<li><a href="student_balance"><?=MNU_STUDENT_BALANCE?></a></li>
											<li><a href="student_invoice"><?=MNU_STUDENT_INVOICE?></a></li>
											<li><a href="student_ledger"><?=MNU_STUDENT_LEDGER?></a></li>
											<li><a href="transaction_detail"><?=MNU_TRANSACTION_DETAIL?></a></li>
											<li><a href="transaction_summary"><?=MNU_TRANSACTION_SUMMARY?></a></li>
										</ul>
									</div>
									<? } ?>
									
									<? if(check_access('REPORT_PLACEMENT') == 1){ ?>
									<div class="col-md-3">	
										<?=MNU_PLACEMENT?> 
										<ul >
											<li><a href="companies"><?=MNU_COMPANIES ?></a></li>
											<li><a href="company_event_report"><?=MNU_COMPANY_EVENTS ?></a></li>
											<li><a href="graduate_employment"><?=MNU_GRADUATE_EMPLOYMENT?></a></li>
											<li><a href="open_job_pdf"><?=MNU_OPEN_JOBS?></a></li>
											<li><a href="student_detail"><?=MNU_STUDENT_DETAIL?></a></li>
										</ul>
									</div>
									<? } ?>
									
									<? if(check_access('REPORT_CUSTOM_REPORT') == 1){ ?>
									<div class="col-md-3">	
										<?=MNU_GENERAL?>
										<ul >
											<li><a href="activity_report"><?=MNU_ACTIVITY_REPORT?></a></li><!-- Ticket # 1053 -->
											<li><a href="manage_custom_report"><?=MNU_CUSTOM_REPORT?></a></li>
											<li><a href="letter_generator" ><?=MNU_LETTER_GENERATOR?></a></li> <!-- Ticket # 1055 -->
											<li><a href="lead_documents_not_received_with_notes" ><?=MNU_LEAD_DOC_NOT_RECEIVED_WITH_NOTES?></a></li>
											<li><a href="duplicate_email_report" ><?=MNU_DUPLICATE_EMAIL?></a></li>
											<li><a href="duplicate_phone_report" ><?=MNU_DUPLICATE_PHONE?></a></li>
											<li><a href="duplicate_ssn_report" ><?=MNU_DUPLICATE_SSN?></a></li>
											<li><a href="manage_user_activity"><?=MNU_USER_ACTIVITY?></a></li>
											<?php if($_SESSION['PK_ACCOUNT'] == 15 && 1 == 2) { ?>
											<li><a href="manage_student_report_selection"><?=MNU_STUDENT_REPORT_SELECTION?></a></li>
											<li><a href="manage_company_report_selection">Company Report Selection</a></li>
											<?php } ?>
										</ul>
									</div>
									<? } ?>
									
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
</body>

</html>
