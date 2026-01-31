<? require_once("../language/menu.php");
require_once("check_access.php");

$current_page1 = $_SERVER['PHP_SELF'];
$parts1 = explode('/', $current_page1);
$current_page = $parts1[count($parts1) - 1]; 

if($current_page == 'index.php')
	$dash_active  = 'class="active"'; 
else if($current_page == 'manage_education_type.php' || $current_page == 'education_type.php' || $current_page == 'setup.php' || $current_page == 'manage_earning_type.php' || $current_page == 'earning_type.php' || $current_page == 'manage_grant_type.php' || $current_page == 'grant_type.php' || $current_page == 'manage_citizenship.php' || $current_page == 'citizenship.php' || $current_page == 'manage_marital_status.php' || $current_page == 'marital_status.php' || $current_page == 'manage_student_status.php' || $current_page == 'student_status.php' || $current_page == 'manage_departments.php' || $current_page == 'sales_channel.php' || $current_page == 'manage_body_types.php' || $current_page == 'body_types.php' || $current_page == 'manage_return_policy.php' || $current_page == 'departments.php' || $current_page == 'manage_drop_reason.php' || $current_page == 'drop_reason.php' || $current_page == 'manage_funding.php' || $current_page == 'funding.php' || $current_page == 'manage_contact_types.php' || $current_page == 'contact_types.php' || $current_page == 'manage_note_types.php' || $current_page == 'note_types.php' || $current_page == 'manage_document_type.php' || $current_page == 'document_type.php' || $current_page == 'manage_questionnaire.php' || $current_page == 'questionnaire.php' || $current_page == 'manage_task_type.php' || $current_page == 'task_type.php' || $current_page == 'manage_task_status.php' || $current_page == 'task_status.php' || $current_page == 'manage_lead_contact_source.php' || $current_page == 'lead_contact_source.php' || $current_page == 'manage_lead_source.php' || $current_page == 'lead_source.php' || $current_page == 'manage_program.php' || $current_page == 'program.php' || $current_page == 'employee_note_types.php' || $current_page == 'manage_note_status.php' || $current_page == 'note_status.php' || $current_page == 'manage_employee_note_types.php' || $current_page == 'note_status.php' || $current_page == 'manage_program_group.php' || $current_page == 'program_group.php' || $current_page == 'manage_list_of_fees.php' || $current_page == 'list_of_fees.php' || $current_page == 'manage_employee.php' || $current_page == 'employee.php' || $current_page == 'manage_course.php' || $current_page == 'course_master.php' || $current_page == 'manage_term_block.php' || $current_page == 'term_block.php' || $current_page == 'manage_custom_fields.php' || $current_page == 'custom_fields.php' || $current_page == 'manage_announcement.php' || $current_page == 'announcement.php' || $current_page == 'school_profile.php' || $current_page == 'campus.php' || $current_page == 'enrollment_mandatory_fields.php' || $current_page == 'manage_region.php' || $current_page == 'region.php' || $current_page == 'manage_student_group.php' || $current_page == 'student_group.php' || $current_page == 'school_requirements.php' || $current_page == 'manage_user_defined_fields.php' || $current_page == 'user_defined_fields.php' || $current_page == 'manage_ar_leder_code.php' || $current_page == 'ar_leder_code.php' || $current_page == 'manage_guarantor.php' || $current_page == 'guarantor.php' || $current_page == 'manage_transcript_status.php' || $current_page == 'transcript_status.php' || $current_page == 'lender_master.php' || $current_page == 'manage_grade_book_type.php' || $current_page == 'grade_book_type.php'  || $current_page == 'manage_placement_status.php' || $current_page == 'placement_status.php' || $current_page == 'course_offering_schedule.php' || $current_page == 'manage_soc_code.php' || $current_page == 'soc_code.php' || $current_page == 'manage_award_letter_text.php' || $current_page == 'award_letter_text.php' || $current_page == 'manage_academic_calendar.php' || $current_page == 'academic_calendar.php' || $current_page == 'manage_notification_settings.php' || $current_page == 'notification_settings.php' || $current_page == 'manage_agreement_library.php' || $current_page == 'agreement_library.php' || $current_page == 'manage_fee_type.php' || $current_page == 'fee_type.php' || $current_page == 'manage_payment_type.php' || $current_page == 'payment_type.php' || $current_page == 'smtp_settings.php' || $current_page == 'text_settings.php' || $current_page == 'manage_email_template.php' || $current_page == 'email_template.php' || $current_page == 'manage_text_template.php' || $current_page == 'text_template.php' || $current_page == 'manage_pdf_template.php' || $current_page == 'pdf_template.php' || $current_page == 'grade.php' || $current_page == 'manage_grade_scale.php' || $current_page == 'grade_scale.php' || $current_page == 'manage_course_offering_student_status.php' || $current_page == 'course_offering_student_status.php' || $current_page == 'manage_credit_transfer_status.php' || $current_page == 'credit_transfer_status.php' || $current_page == 'manage_placement_company_status.php' || $current_page == 'placement_company_status.php' || $current_page == 'manage_placement_company_event_type.php' || $current_page == 'placement_company_event_type.php' || $current_page == 'manage_placement_company_question_group.php' || $current_page == 'placement_company_question_group.php' || $current_page == 'manage_placement_student_note_status.php' || $current_page == 'placement_student_note_status.php' || $current_page == 'manage_placement_student_note_type.php' || $current_page == 'placement_student_note_type.php' || $current_page == 'manage_placement_student_event_status.php' || $current_page == 'placement_student_event_status.php' || $current_page == 'manage_placement_student_event_type.php' || $current_page == 'placement_student_event_type.php' || $current_page == 'manage_placement_verification_source.php' || $current_page == 'placement_verification_source.php' || $current_page == 'manage_placement_type.php' || $current_page == 'placement_type.php' || $current_page == 'manage_placement_company_questionnaire.php' || $current_page == 'placement_company_questionnaire.php' || $current_page == 'manage_placement_student_event_other.php' || $current_page == 'placement_student_event_other.php' || $current_page == 'manage_placement_student_questionnaire.php' || $current_page == 'placement_student_questionnaire.php' || $current_page == 'manage_placement_student_status.php' || $current_page == 'placement_student_status.php' || $current_page == 'manage_servicer.php' || $current_page == 'servicer.php' || $current_page == 'manage_enrollment_status_scale.php' || $current_page == 'enrollment_status_scale.php' || $current_page == 'manage_pdf_footer.php' || $current_page == 'pdf_footer.php' || $current_page == 'manage_lead_source_group.php' || $current_page == 'lead_source_group.php'  || $current_page == 'manage_grade_book_code.php' || $current_page == 'grade_book_code.php' || $current_page == 'manage_sap_group.php' || $current_page == 'sap_group.php' || $current_page == 'manage_sap_warning.php' || $current_page == 'sap_warning.php' || $current_page == 'attendnace_code.php' || $current_page == 'manage_attendance_activity_type.php' || $current_page == 'attendance_activity_type.php' || $current_page == 'course.php' || $current_page == 'manage_event_other.php' || $current_page == 'event_other.php' || $current_page == 'manage_company_source.php' || $current_page == 'company_source.php' || $current_page == 'consolidation_tool.php' || $current_page == 'employee_report.php'  ) 
	$setup_active  = 'class="active"';
else if(($current_page == 'manage_student.php' || $current_page == 'student.php') && $_GET['t'] == '' )	
	$student_active  = 'class="active"';
else if(($current_page == 'manage_student.php' || $current_page == 'student.php' || $current_page == 'student_task.php' || $current_page == 'student_notes.php' || $current_page == 'student_contact.php' || $current_page == 'student_document.php' || $current_page == 'student_loa.php' || $current_page == 'student_probation.php' ) && $_GET['t'] == 1 )	
	$admission_active  = 'class="active"';
else if(($current_page == 'manage_student.php' || $current_page == 'student.php' || $current_page == 'student_task.php' || $current_page == 'student_notes.php' || $current_page == 'student_contact.php' || $current_page == 'student_document.php' || $current_page == 'student_loa.php' || $current_page == 'student_probation.php') && $_GET['t'] == 3 )	
	$financial_aid_active  = 'class="active"';	
else if(($current_page == 'manage_student.php' || $current_page == 'student.php' || $current_page == 'student_task.php' || $current_page == 'student_notes.php' || $current_page == 'student_contact.php' || $current_page == 'student_document.php' || $current_page == 'student_loa.php' || $current_page == 'student_probation.php' ) && $_GET['t'] == 2 )	
	$registrar_active  = 'class="active"';	
else if(($current_page == 'manage_student.php' || $current_page == 'student.php' || $current_page == 'student_task.php' || $current_page == 'student_notes.php' || $current_page == 'student_contact.php' || $current_page == 'student_document.php' || $current_page == 'student_loa.php' || $current_page == 'student_probation.php' ) && $_GET['t'] == 5 )	
	$accounting_active  = 'class="active"';
else if(($current_page == 'manage_student.php' || $current_page == 'student.php' || $current_page == 'student_task.php' || $current_page == 'student_notes.php' || $current_page == 'student_contact.php' || $current_page == 'student_document.php' || $current_page == 'student_loa.php' || $current_page == 'student_probation.php' ) && $_GET['t'] == 6 )	
	$placement_active  = 'class="active"';	
	
else if($current_page == 'reports.php' || $current_page == 'lead_task_report.php' || $current_page == 'manage_custom_report.php' || $current_page == 'custom_report.php' || $current_page == 'projected_funds.php' || $current_page == 'disbursed_funds.php' || $current_page == 'duplicate_ssn_report.php' || $current_page == 'duplicate_email_report.php' || $current_page == 'letter_generator.php' || $current_page == 'student_summar_pdf.php' || $current_page == 'student_transcript_list.php' || $current_page == 'student_transcript.php' || $current_page == 'student_balance.php' || $current_page == 'student_attendance_analysis_report.php' || $current_page == 'lead_documents_not_received_with_notes.php' || $current_page == 'account_ledger_report.php' || $current_page == 'attendance_report_form.php' || $current_page == 'attendance_with_loa_report.php' || $current_page == 'course_offering_grade_book_progress_report.php' || $current_page == 'course_offering_grade_book_transcript_report.php' || $current_page == 'grade_book_report_card.php' || $current_page == 'requirement_report.php' || $current_page == 'student_schedule_report.php' || $current_page == 'student_invoice.php' || $current_page == 'student_transcript_list_numeric_grade.php' || $current_page == 'admissions_rep_statistics.php' || $current_page == 'lead_source_statistics.php' || $current_page == 'negative_balance.php' || $current_page == 'packaging_summary.php' || $current_page == 'repackage_date.php' || $current_page == 'loa_report.php' || $current_page == 'transaction_summary.php' || $current_page == 'transaction_detail.php' || $current_page == 'graduates.php' || $current_page == 'expected_graduates.php' || $current_page == 'student_detail.php' || $current_page == 'graduate_employment.php' || $current_page == 'academic_calendar_pdf.php' || $current_page == 'title_iv.php' || $current_page == 'title_iv_detail.php' || $current_page == 'manage_title_iv_recipients_by_category_setup.php' || $current_page == 'duplicate_phone_report.php' || $current_page == 'attendance_incomplete_report.php' || $current_page == 'gpa_analysis.php' || $current_page == 'attendance_daily_absents_by_course.php' || $current_page == 'drop_report.php' || $current_page == 'attendance_daily_sign_in_sheet.php' || $current_page == 'title_iv_recipients_by_category_setup.php' || $current_page == 'companies.php' || $current_page == 'grade_sheet.php' || $current_page == 'student_ledger.php' || $current_page == 'instructor_schedule.php' || $current_page == 'attendance_absences_by_course.php' || $current_page == 'attendance_makeup_report.php' || $current_page == 'attendance_tardy_hours_report.php' || $current_page == 'course_offering_by_term.php' || $current_page == 'student_report_card.php' || $current_page == 'attendance_roster.php' || $current_page == 'activity_report.php' || $current_page == 'past_due.php' || $current_page == 'company_event_report.php' || $current_page == 'transcripts.php' || $current_page == 'attendance_reports.php' || $current_page == 'grade_report.php' || $current_page == 'program_course_progress.php' || $current_page == 'students_by_status.php' || $current_page == 'course_report.php' || $current_page == 'program_course_progress_report.php' || $current_page == 'student_report_selection.php' || $current_page == 'manage_student_report_selection.php' || $current_page == 'unapproved_disbursements.php' || $current_page == 'probation_report.php' || $current_page == 'student_tests.php' || $current_page == 'ledger_worksheet.php' || $current_page == 'payments_due_report.php' || $current_page == 'balance_sheet.php' || $current_page == 'repeat_course.php' || $current_page == 'other_education_report.php' || $current_page == 'transfer_credit_report.php' ) 
	$report_active  = 'class="active"';
else if($current_page == 'management.php' || $current_page == 'student_bulk_update.php' || $current_page == 'manage_course_offering.php' || $current_page == 'course_offering.php' || $current_page == 'manage_company.php' || $current_page == 'company.php' || $current_page == 'manage_misc_batch.php' || $current_page == 'misc_batch.php' || $current_page == 'manage_batch_payment.php' || $current_page == 'batch_payment.php' || $current_page == 'manage_tuition_batch.php' || $current_page == 'tuition_batch.php' || $current_page == 'attendance_entry.php' || $current_page == 'non_scheduled_attendance.php' || $current_page == 'upload_attendance.php' || $current_page == 'final_grade_input.php' || $current_page == 'student_bulk_login.php' || $current_page == 'manage_student_portal_user.php' || $current_page == 'cpl_report.php' || $current_page == 'fte_calculation.php' || $current_page == 'cpl_setup.php' || $current_page == 'manage_ecm_ledger.php' || $current_page == 'ecm_ledger.php' || $current_page == 'time_clock_import.php' || $current_page == 'send_course_offering_ethink.php' || $current_page == 'send_course_offering_ethink_result.php' || $current_page == 'send_student_ethink.php' || $current_page == 'send_student_ethink_result.php' || $current_page == 'send_student_course_offering_ethink.php' || $current_page == 'send_student_course_offering_ethink_result.php' || $current_page == 'bulk_update_notes.php' || $current_page == 'update_notes.php' || $current_page == 'bulk_update_task.php' || $current_page == 'update_task.php' || $current_page == 'manage_isir.php' || $current_page == 'isir_student.php' || $current_page == 'isir.php' || $current_page == 'unposted_batches.php' || $current_page == 'manage_90_10_setup.php' || $current_page == '90_10_setup.php' || $current_page == '90_10_calculation_disclosure_setup.php' || $current_page == 'population_report_setup.php' || $current_page == '90_10_calculation_disclosure_by_student.php' || $current_page == '90_10_calculation_disclosure_by_ledger.php' || $current_page == '90_10_calculation_disclosure.php' || $current_page == 'ipeds_fall_collections_setup.php' || $current_page == 'bulk_text.php' || $current_page == 'ipeds_fall_12_enrollment.php' || $current_page == 'ipeds_fall_completions.php' || $current_page == 'bppe_report_setup.php' || $current_page == 'diamond_pay_transaction.php' || $current_page == 'expected_transaction.php' || $current_page == 'recurring_payment_credit_card_details.php' || $current_page == 'recurring_payment_credit_card_details.php' || $current_page == 'bppe_school_performance_fact_sheets.php' || $current_page == 'ipeds_spring_collection_setup.php' || $current_page == 'ipeds_winter_collection_setup.php' || $current_page == 'send_term_canvas.php' || $current_page == 'send_term_canvas_result.php' || $current_page == 'send_employee_canvas.php' || $current_page == 'send_employee_canvas_result.php' || $current_page == 'send_course_offering_canvas.php' || $current_page == 'send_course_offering_canvas_result.php' || $current_page == '_1098T_setup.php' || $current_page == 'student_bulk_update.php' || $current_page == 'create_student_id.php' || $current_page == '_1098T.php' || $current_page == 'manage_1098T_ein.php' || $current_page == '_1098T_ein.php' || $current_page == 'attendance_report_ethink.php' || $current_page == 'ipeds_winter_collection.php' || $current_page == 'send_course_offering_instructor_ethink.php' || $current_page == 'population_report.php' || $current_page == 'manage_custom_queries.php' || $current_page == 'custom_queries.php' || $current_page == 'manage_earnings_setup.php' || $current_page == 'earnings_setup.php' || $current_page == 'manage_course_offering.php' || $current_page == 'accsc_employment_verification_source_report.php' || $current_page == 'accsc_employment_verification_source_report_setup.php' || $current_page == 'accsc_graduation_and_employment_chart.php' || $current_page == 'accsc_graduation_and_employment_chart_setup.php' || $current_page == 'accsc_licensure_certification_exam_pass_rates.php' || $current_page == 'accsc_licensure_certification_exam_pass_rates_setup.php' || $current_page == 'earnings_calculation.php' || $current_page == 'earnings_report.php' || $current_page == 'placement_rate_report.php' || $current_page == 'placement_rate_report_setup.php' || $current_page == 'google_classroom_connect.php' || $current_page == 'google_connect_success.php' || $current_page == 'send_course_offering_g_classroom.php' || $current_page == 'send_course_offering_g_classroom_result.php' || $current_page == 'send_employee_course_offering_g_classroom.php' || $current_page == 'send_course_offering_instructor_g_classroom_result.php' || $current_page == 'send_student_course_offering_g_classroom.php' || $current_page == 'send_student_course_offering_g_classroom_result.php' || $current_page == 'FISAP_setup.php' || $current_page == 'FISAP_report.php' || $current_page == 'TWC_report.php' || $current_page == 'TWC_setup.php' || $current_page == 'course_offering_grade_book_import.php' || $current_page == 'course_offering_grade_book_import_review.php' || $current_page == 'course_offering_grade_book_import_map_column.php' || $current_page == 'course_offering_grade_book_import_map_result.php' || $current_page == 'manage_course_offering_grade_book_import_review.php' || $current_page == 'attendance.php' || $current_page == 'grade_book_import_template.php' || $current_page == 'import_lsq_lead.php' || $current_page == 'import_lsq_student_lead.php' ) 
	$management_active  = 'class="active"';	

$menu_ib_count = $db->Execute("SELECT PK_INTERNAL_EMAIL_RECEPTION FROM Z_INTERNAL_EMAIL_RECEPTION WHERE VIWED = 0 AND PK_USER = '$_SESSION[PK_USER]' AND SELF_ADDED = 0 GROUP BY INTERNAL_ID");	
?>
<header class="topbar">
	<nav class="navbar top-navbar navbar-expand-md navbar-dark">
		<div class="navbar-header">
			<a class="navbar-brand" href="index">
				<b>
					<? $res_logo = $db->Execute("SELECT LOGO, ENABLE_INTERNAL_MESSAGE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); //ticket #967   ?>
					<? if($res_logo->fields['LOGO'] == '') {?>
						<img src="../backend_assets/images/DDlogo_Trans.png" alt="homepage" class="dark-logo" />
						<img src="../backend_assets/images/DDlogo_Trans.png" alt="homepage" class="light-logo" />
					<? } else { ?>
						<img src="<?=$res_logo->fields['LOGO']?>" alt="homepage" class="dark-logo" style="max-height: 66px;" />
						<img src="<?=$res_logo->fields['LOGO']?>" alt="homepage" class="light-logo" style="max-height: 66px;" />
					<? } ?>
				</b>
		</div>
		<div class="navbar-collapse">
			<ul class="navbar-nav mr-auto">
				<!-- This is  -->
				<li class="nav-item"> <a class="nav-link nav-toggler d-block d-md-none waves-effect waves-dark" href="javascript:void(0)"><i class="ti-menu"></i></a> </li>
				<li class="nav-item"> <a class="nav-link sidebartoggler d-none waves-effect waves-dark" href="javascript:void(0)"><i class="icon-menu"></i></a> </li>
			</ul>
			
			<ul class="navbar-nav my-lg-0">
				<!-- ============================================================== -->
				<!-- Comment -->
				<!-- ============================================================== -->
				<li class="nav-item d-flex align-items-center school-name <? if($_SESSION['CAMPUS_NAME'] != ''){ ?> campus-active <? } ?> " >
					<span><?=$_SESSION['SCHOOL_NAME']?></span>
				</li>
				
				<? /*if($_SESSION['CAMPUS_NAME'] != ''){ ?>
				<li class="nav-item d-flex align-items-center campus-name">
					<span><?=$_SESSION['CAMPUS_NAME']?></span>
				</li>
				<? }*/ ?>
				
				<? if(check_access('ADMISSION_ACCESS') == 3){ ?>
				<li class="nav-item dropdown">
					<button onclick="javascript:window.location.href='student?n=1&t=1'" type="button" class="btn btn-success mr-2" style="margin-top: 17px;" ><?=MNU_NEW_LEAD?></button>
				</li>
				<? } ?>
				
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle waves-effect waves-dark" href="https://support.diamondsis.com/" target="_blank" aria-haspopup="true" aria-expanded="false"> <i class="fas fa-ticket-alt"></i>
						<div class="notify"></div>
					</a>
				</li>
				
				<li class="nav-item dropdown" id="NOTIFICATIONS_li_id" >
					<? require_once("ajax_notification_menu.php"); ?>
				</li>
				
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle waves-effect waves-dark" href="help_docs" aria-haspopup="true" aria-expanded="false"> <i class="mdi mdi-help"></i>
						<div class="notify"></div>
					</a>
				</li>
			
				<!-- ticket #967  -->
				<? $res_emp = $db->Execute("SELECT INTERNAL_MESSAGE_ENABLED FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' ");
				if($res_logo->fields['ENABLE_INTERNAL_MESSAGE'] == 1 && $res_emp->fields['INTERNAL_MESSAGE_ENABLED'] == 1) {?>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle waves-effect waves-dark" href="my_mails" aria-haspopup="true" aria-expanded="false">
						<!-- Ticket # 967 -->
						<i class="mdi mdi-comment-outline"></i>
						<div class="notify"> 
							<? if($menu_ib_count->RecordCount() > 0) { ?> 
								<span class="heartbit" ></span> <span class="point"></span>
							<? } ?>
						</div>
					</a>
				</li>
				<? } ?>
				<!-- ticket #967  -->
				
				<li class="nav-item dropdown">
					<div class="custom-control custom-switch" style="margin-top: 22px;" >
						<input type="checkbox" class="custom-control-input" id="TURN_OFF_ASSIGNMENTS_MASTER" value="1" name="TURN_OFF_ASSIGNMENTS_MASTER" onclick="set_available()" <? if($_SESSION['TURN_OFF_ASSIGNMENTS'] == 0) echo "checked"; ?>>
						<label class="custom-control-label" for="TURN_OFF_ASSIGNMENTS_MASTER" style="color:#FFFFFF !important;" >Available</label>
					</div>
				</li>

				<li class="nav-item dropdown u-pro">
					<a class="nav-link dropdown-toggle waves-effect waves-dark profile-pic" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<? if($_SESSION['PROFILE_IMAGE'] != ''){ ?>
							<img src="<?=$_SESSION['PROFILE_IMAGE']?>" alt="user" class=""> 
						<? } else { ?>
							<img src="../backend_assets/images/user.png" alt="user" class=""> 
						<? } ?>
						<span class="hidden-md-down"><?=$_SESSION['NAME']?> &nbsp;<i class="fa fa-angle-down"></i></span>
					</a>
					<div class="dropdown-menu dropdown-menu-right animated flipInY">
						<!-- text-->
						<a href="profile" class="dropdown-item"><i class="ti-user"></i> <?=MNU_MY_PROFILE?></a>
						<a href="change_password" class="dropdown-item"><i class="ti-lock"></i> <?=MNU_CHANGE_PASSWORD?></a>
						<a href="manage_to_do" class="dropdown-item"><i class="icon-note"></i> <?=MNU_TO_DO_LIST?></a>
						<? /* Ticket # 1870  */
						$res_ethink_head = $db->Execute("SELECT ENABLE_LSQ FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
						if($res_ethink_head->fields['ENABLE_LSQ'] == 1) { ?>
						<a href="https://login.leadsquared.com" class="dropdown-item" target="_blank" > <?=MNU_LSQ_SINGLE_SIGN_ON?></a>
						<? }
						/* Ticket # 1870  */ ?>
						<div class="dropdown-divider"></div>
						<!-- text-->
						<a href="../logout" class="dropdown-item"><i class="fa fa-power-off"></i> <?=MNU_LOGOUT?></a>
						<!-- text-->
					</div>
				</li>
			</ul>
		</div>
	</nav>
</header>

<aside class="left-sidebar">
	<!-- Sidebar scroll-->
	<div class="scroll-sidebar">
		<!-- Sidebar navigation-->
		<nav class="sidebar-nav">
			<ul id="sidebarnav">
				<li class="user-pro"> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><img src="../backend_assets/images/users/1.jpg" alt="user-img" class="img-circle"><span class="hide-menu"><?=$_SESSION['NAME']?></span></a>
					<ul aria-expanded="false" class="collapse">
						<li><a href="javascript:void(0)"><i class="ti-user"></i> <?=MNU_MY_PROFILE?></a></li>
						<li><a href="../logout"><i class="fa fa-power-off"></i> <?=MNU_LOGOUT?></a></li>
					</ul>
				</li>
				<li class="nav-small-cap">--- PERSONAL</li>
				
				<li <?=$dash_active?> ><a class="waves-effect waves-dark" href="index" ><i class="icon-speedometer"></i><span class="hide-menu"><?=MNU_DASHBOARD?></span></a></li>
				
				<? if(check_access('ADMISSION_ACCESS') != 0){ ?>
				<li <?=$admission_active?> ><a class="waves-effect waves-dark" href="manage_student?t=1" ><i class="mdi mdi-account-plus"></i><span class="hide-menu"><?=MNU_ADMISSION?></span></a></li>
				<? } ?>
				
				<? if(check_access('REGISTRAR_ACCESS') != 0){ ?>
				<li <?=$registrar_active?> ><a class="waves-effect waves-dark" href="manage_student?t=2" ><i class="mdi mdi-buffer"></i><span class="hide-menu"><?=MNU_REGISTRAR?></span></a></li>
				<? } ?>
				
				<? if(check_access('FINANCE_ACCESS') != 0){ ?>
				<li <?=$financial_aid_active?> ><a class="waves-effect waves-dark" href="manage_student?t=3" ><i class="mdi mdi-currency-usd"></i><span class="hide-menu"><?=MNU_FINANCIAL_AID?></span></a></li>
				<? } ?>
				
				<? if(check_access('ACCOUNTING_ACCESS') != 0){ ?>
				<li <?=$accounting_active?> ><a class="waves-effect waves-dark" href="manage_student?t=5" ><i class="fab fa-accusoft"></i><span class="hide-menu"><?=MNU_ACCOUNTING?></span></a></li>
				<? } ?>
				
				<? if(check_access('PLACEMENT_ACCESS') != 0){ ?>
				<li <?=$placement_active?> ><a class="waves-effect waves-dark" href="manage_student?t=6" ><i class="mdi mdi-briefcase"></i><span class="hide-menu"><?=MNU_PLACEMENT?></span></a></li>
				<? } ?>
				
				<? if(has_report_access() == 1){ ?>
				<li <?=$report_active?> ><a class="waves-effect waves-dark" href="reports" ><i class="icon-chart"></i><span class="hide-menu"><?=MNU_REPORTS?></span></a></li>
				<? } ?>
				
				<? if(has_management_access() == 1){ ?>
				<li <?=$management_active?> ><a class="waves-effect waves-dark" href="management" ><i class="mdi mdi-apps"></i><span class="hide-menu"><?=MNU_MANAGEMENT?></span></a></li>
				<? } ?>
			
				<? if(has_setup_access() == 1){ ?>
					<li <?=$setup_active?> ><a class="waves-effect waves-dark" href="setup" ><i class="mdi mdi-brightness-7"></i><span class="hide-menu"><?=MNU_SETUP?></span></a></li>
				<? } ?>
			</ul>
		</nav>
		<!-- End Sidebar navigation -->
	</div>
	<!-- End Sidebar scroll-->
</aside>