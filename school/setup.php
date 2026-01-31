<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(has_setup_access() == 0){ 
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
	<title><?=MNU_SETUP?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_SETUP?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<? if(check_access('SETUP_SCHOOL') == 1){ ?>
								<div class="row">
									<div class="col-md-2">
										<h4 class="text-themecolor"><?=MNU_SCHOOL?></h4>
										<ul style="padding-left: 0;" >
											<li><a href="manage_academic_calendar"><?=MNU_ACADEMIC_CALENDAR?></a></li></a></li>
											<!-- <li><a href="card_x_settings"><?=MNU_CARD_X_SETTINGS?></a></li></a></li> Ticket #1791 -->
											<li><a href="manage_collateral"><?=MNU_COLLATERAL?></a></li></a></li>
											<li><a href="manage_document_type" ><?=MNU_DOCUMENT_TYPE?></a></li> <!-- Ticket #853 -->
											<li><a href="manage_custom_fields"><?=MNU_CUSTOM_FIELDS?></a></li></a></li>
											<li><a href="manage_departments"><?=MNU_DEPARTMENT?></a></li>
											<li><a href="manage_note_status?t=1"><?=MNU_EMPLOYEE_NOTE_STATUS?></a></li></a></li>
											<li><a href="manage_employee_note_types"><?=MNU_EMPLOYEE_NOTE_TYPES?></a></li>
											<li><a href="manage_employee?t=1"><?=MNU_EMPLOYEE?></a></li>
											<li><a href="manage_notification_settings"><?=MNU_NOTIFICATION_SETTINGS?></a></li>
											<!--<li><a href="manage_employee?t=1"><?=$_SESSION['EMPLOYEE_LABEL']?></a></li>-->
											<li><a href="manage_pdf_footer"><?=MNU_PDF_FOOTER?></a></li>
											<!--<<li><a href="manage_region"><?=MNU_REGION?></a></li>-->
											<li><a href="manage_room"><?=MNU_ROOM?></a></li>
											<li><a href="manage_session"><?=MNU_SESSION?></a></li>
											<li><a href="school_requirements"><?=MNU_SCHOOL_REQUIREMENTS?></a></li></a></li>
											<li><a href="school_profile"><?=MNU_SCHOOL_PROFILE?></a></li>
											<li><a href="manage_user_defined_fields"><?=MNU_USER_DEFINED_FIELDS?></a></li></a></li>
										</ul>
									</div>
									<? } ?>
									
									<? if(check_access('SETUP_ADMISSION') == 1 || check_access('SETUP_COMMUNICATION') == 1){ ?>
									<div class="col-md-2">
										<? if(check_access('SETUP_ADMISSION') == 1){ ?>
										<h4 class="text-themecolor"><?=MNU_ADMISSIONS?></h4>
										<ul style="padding-left: 0;">
											<li>
												<a href="enrollment_mandatory_fields" style="float: left;" ><?=MNU_ENROLLMENT_MANDATE?></a>
											</li>
											<li>
												<a href="manage_lead_contact_source" style="float: left;" ><?=MNU_LEAD_CONTACT_SOURCE?></a>
											</li>
											<li>
												<a href="manage_lead_source" ><?=MNU_LEAD_SOURCE?></a>
											</li>
											<li >
												<a href="manage_lead_source_group" ><?=MNU_LEAD_SOURCE_GROUP?></a>
											</li>
										</ul>
										<? } ?>
									
										<? if(check_access('SETUP_COMMUNICATION') == 1){ ?>
										<h4 class="text-themecolor"><?=MNU_COMMUNICATION?></h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_agreement_library"><?=MNU_AGREEMENT?></a></li></a></li>
											<li><a href="manage_notification_sent"><?=MNU_ALL_NOTIFICATIONS?></a></li></a></li>
											<li><a href="manage_announcement"><?=MNU_ANNOUNCEMENT?></a></li></a></li>
											<li><a href="manage_email_template"><?=MNU_EMAIL_TEMPLATE?></a></li></a></li>
											<li><a href="manage_pdf_template"><?=MNU_MAIL_TEMPLATE?></a></li></a></li>
											<li><a href="manage_smtp_settings"><?=MNU_SMTP_SETTINGS?></a></li>
											<!-- <li><a href="manage_employee?t=2"><?=MNU_TEACHER?></a></li> -->
											<li><a href="manage_text_template"><?=MNU_TEXT_TEMPLATE?></a></li>
										</ul>
										<? } ?>
									</div>
									<? } ?>
									
									<? if(check_access('SETUP_TASK_MANAGEMENT') == 1 || check_access('SETUP_STUDENT') == 1){ ?>
									<div class="col-md-2" style="max-width: 13%;">
										<? if(check_access('SETUP_STUDENT') == 1){ ?>
										<h4 class="text-themecolor"><?=MNU_STUDENT?></h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_note_types?t=2"><?=MNU_EVENT_TYPES?></a></li>
											<li><a href="manage_note_status?t=3"><?=MNU_STUDENT_EVENT_STATUS?></a></li></a></li>
											<li><a href="manage_event_other?t=2"><?=MNU_STUDENT_EVENT_OTHER?></a></li>
											<li><a href="manage_note_types?t=1"><?=MNU_NOTE_TYPES?></a></li>
											<li><a href="manage_note_status?t=2"><?=MNU_STUDENT_NOTE_STATUS?></a></li></a></li>
											<li>
												<a href="manage_student_status" ><?=MNU_STUDENT_STATUS?></a>
											</li>
											<li>
												<a href="manage_questionnaire" style="float: left;" ><?=MNU_QUESTIONNAIRE?></a>
											</li>
										</ul>
										<? } ?>
									
										<? if(check_access('SETUP_TASK_MANAGEMENT') == 1){ ?>
										<h4 class="text-themecolor"><?=MNU_TASK_MANAGEMENT?></h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_task_status"><?=MNU_TASK_STATUS?></a></li>
											<li><a href="manage_task_type"><?=MNU_TASK_TYPE?></a></li>
											<li><a href="manage_event_other?t=1"><?=MNU_TASK_OTHER?></a></li>
										</ul>
										<? } ?>
										
										
									</div>
									<? } ?>
									
									<? if(check_access('SETUP_FINANCE') == 1){ ?>
									<div class="col-md-2" style="max-width: 13%;" >
										<h4 class="text-themecolor"><?=MNU_FINANCIAL_AID?></h4>
										<ul style="padding-left: 0;">
										<?php if (has_wvjc_access($_SESSION['PK_ACCOUNT'])) { ?>
											<!-- <li><a href="manage_default_cohort_year"><?=MNU_DEFAULT_COHORT_YEAR?></a></li>
											<li><a href="manage_default_source"><?=MNU_DEFAULT_SOURCE?></a></li>
											<li><a href="manage_default_status"><?=MNU_DEFAULT_STATUS?></a></li>
											<li><a href="manage_default_type"><?=MNU_DEFAULT_TYPE?></a></li> -->
											<?php } ?>
											<li><a href="manage_funding"><?=MNU_FUNDING?></a></li>
											<li><a href="manage_guarantor"><?=MNU_GUARANTOR?></a></li>
											<li><a href="lender_master"><?=MNU_LENDER_MASTER?></a></li>
											<li><a href="manage_servicer"><?=MNU_SERVICER?></a></li>
										</ul>
									</div>
									<? } ?>
									
									<? if(check_access('SETUP_REGISTRAR') == 1){ ?>
									<div class="col-md-2" style="max-width: 13%;">
										<h4 class="text-themecolor"><?=MNU_REGISTRAR?></h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_attendance_activity_type"><?=MNU_ATTENDANCE_ACTIVITY_TYPE?></a></li><!-- Ticket # 1037 -->
											<li><a href="attendnace_code"><?=MNU_ATTENDNACE_CODE?></a></li>
											<li><a href="manage_course"><?=MNU_COURSE?></a></li>
											<!--<li><a href="manage_course_offering"><?=MNU_COURSE_OFFERING?></a></li>-->
											<li><a href="manage_course_offering_student_status"><?=MNU_COURSE_OFFERING_STUDENT_STATUS?></a></li>
											<li><a href="manage_drop_reason"><?=MNU_DROP_REASON?></a></li>
											<li><a href="manage_enrollment_status_scale"><?=MNU_ENROLLMENT_STATUS_SCALE?></a></li>
											<li><a href="manage_grade_book_type"><?=MNU_GRADE_BOOK_TYPE?></a></li>
											<li><a href="manage_grade_scale"><?=MNU_GRADE_SCALE_SETUP?></a></li>
											<li><a href="grade"><?=MNU_GRADE_SETUP?></a></li>
											<li><a href="manage_program"><?=MNU_PROGRAM?></a></li>
											<li><a href="manage_grade_book_code"><?=MNU_GRADEBOOK_CODE?></a></li>
											<li><a href="manage_program_group"><?=MNU_PROGRAM_GROUP?></a></li>
											<?
											// if(av_check_access('sap_report'))
											// {
											?>
											<li><a href="manage_sap_group"><?=MNU_SAP_GROUP?></a></li>
											<li><a href="manage_sap_scale"><?=MNU_SAP_SCALE_SETUP?></a></li>
											<li><a href="manage_sap_warning"><?=MNU_SAP_WARNING?></a></li>
											<?
											// }
											?>
											<li><a href="manage_student_group"><?=MNU_STUDENT_GROUP?></a></li></a></li>
											<li><a href="manage_term_master"><?=MNU_TERM_MASTER?></a></li></a></li>
											<li><a href="manage_term_block"><?=MNU_TERM_BLOCK?></a></li></a></li>
											<li><a href="manage_transcript_group"><?=MNU_TRANSCRIPT_GROUP?></a></li>
											<li><a href="manage_credit_transfer_status"><?=MNU_CREDIT_TRANSFER_STATUS?></a></li>
										</ul>
									</div>
									<? } ?>
									
									<? if(check_access('SETUP_ACCOUNTING') == 1){ ?>
									<div class="col-md-2" style="max-width: 13%;">
										<h4 class="text-themecolor"><?=MNU_ACCOUNTING?></h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_fee_type"><?=MNU_AR_FEE_TYPE?></a></li>
											<li><a href="manage_ar_leder_code"><?=MNU_LEDGER_CODE?></a></li>
											<li><a href="manage_ledger_code_group">Ledger Code Group</a></li>
											<li><a href="manage_payment_type"><?=MNU_AR_PAYMENT_TYPE?></a></li>
										</ul>
									</div>
									<? } ?>
									
									<? if(check_access('SETUP_PLACEMENT') == 1){ ?>
									<div class="col-md-2" style="max-width: 13%;">
										<h4 class="text-themecolor"><?=MNU_PLACEMENT?></h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_placement_status"><?=MNU_PLACEMENT_STATUS?></a></li>
											<li><a href="manage_placement_company_event_type"><?=MNU_PLACEMENT_COMPANY_EVENT_TYPE?></a></li>
											<li><a href="manage_placement_company_question_group"><?=MNU_PLACEMENT_COMPANY_QUESTION_GROUP?></a></li>
											<li><a href="manage_company_source"><?=MNU_COMPANY_SOURCE?></a></li> <!-- Ticket # 1117  -->
											<li><a href="manage_placement_company_status"><?=MNU_PLACEMENT_COMPANY_STATUS?></a></li>
											<li><a href="manage_placement_company_questionnaire"><?=MNU_PLACEMENT_COMPANY_QUESTIONNAIRE?></a></li>
											<!--<li><a href="manage_placement_student_note_status"><?=MNU_PLACEMENT_STUDENT_NOTE_STATUS?></a></li> Ticket # 1030-->
											<!--<li><a href="manage_placement_student_note_type"><?=MNU_PLACEMENT_STUDENT_NOTE_TYPE?></a></li>
											<li><a href="manage_placement_student_status"><?=MNU_PLACEMENT_STUDENT_STATUS?></a></li>
											<li><a href="manage_placement_student_questionnaire"><?=MNU_PLACEMENT_STUDENT_QUESTIONNAIRE?></a></li>-->
											<li><a href="manage_soc_code"><?=MNU_SOC_CODE?></a></li>
											<li><a href="manage_placement_type"><?=MNU_PLACEMENT_TYPE?></a></li>
											<li><a href="manage_placement_verification_source"><?=MNU_PLACEMENT_VERIFICATION_SOURCE?></a></li>
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
