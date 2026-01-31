<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
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
	<title>Setup | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Setup</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row" style="background-color: #ddecf9;margin-bottom: 10px;" >
									<div class="col-md-12">
										<h3 class="text-themecolor" style="background-color: #ddecf9;margin-top: 5px;" >Application Level</h4>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-2" style="max-width: 12%;" >
										<h4 class="text-themecolor">General</h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_admin_users">Admin Users</a></li>
											<li><a href="manage_announcement">Announcements</a></li>
											<!-- dvb 14 11 2024 pop up -->
												<li><a href="manage_announcement_popup">Announcements POP UP</a></li>
											<!-- end  -->
											<li><a href="manage_countries">Countries</a></li>
											<li><a href="manage_email_template">Email Template</a></li>
											<li><a href="manage_distance_learning">Distance Learning</a></li>
											<li><a href="manage_event_type">Event Types</a></li>
											<li><a href="manage_end_year">End Year</a></li>
											<li><a href="manage_gender">Gender</a></li>
											<li><a href="manage_help_category">Help Category</a></li>
											<li><a href="manage_help_sub_category">Help Subcategory</a></li>
											<li><a href="manage_help">Knowledge Base</a></li>
											<li><a href="manage_marital_status">Marital Status</a></li>
											<li><a href="manage_probation_level">Probation Level</a></li>
											<li><a href="manage_probation_status">Probation Status</a></li>
											<li><a href="manage_probation_type">Probation Type</a></li>
											<li><a href="smtp_settings">SMTP Settings</a></li>
											<li><a href="manage_states">States</a></li>
											<li><a href="manage_ticket_status">Ticket Status</a></li>
											<li><a href="manage_ticket_priority">Ticket Priority</a></li>
											<li><a href="manage_ticket_category">Ticket Category</a></li>
											<li><a href="text_settings">Text Settings</a></li>
											<li><a href="manage_text_template">Text Template</a></li>
											<li><a href="manage_release_notes">Release Notes</a></li>
											<li><a href="manage_release_notes_category">Release Notes Category</a></li>
											<li><a href="manage_release_notes_type">Release Notes Type</a></li>
											<!-- CAMPUS IQ DVB 05 12 205 -->
											<li><a href="manage_campusiq">Campus IQ Dashboard</a></li>
										</ul>
									</div>
									
									<div class="col-md-2" >
										<h4 class="text-themecolor">School</h4>
										<ul style="padding-left: 0;">
											<!--<li><a href="manage_90_10_group">90/10 Group</a></li>-->
											<li><a href="manage_contact_types">Contact Type</a></li>
											<li><a href="manage_credential_level">Credential Level</a></li>
											<li><a href="manage_dependent_status">Dependent Status</a></li>
											<li><a href="manage_earning_type">Earning Types</a></li>
											<li><a href="manage_education_type">Education Types</a></li>
											<li><a href="manage_enrollment_status">Enrollment Status</a></li>
											<li><a href="manage_fee_type">Fee Type</a></li>
											<!--<li><a href="manage_ge_disclosure">GE Disclosure</a></li>
											<li><a href="manage_in_field">In Field</a></li>-->
											<li><a href="manage_ipeds_category">IPEDS Category</a></li>
											<!--<li><a href="manage_legacy_ipeds">Legacy IPEDs</a></li>
											<li><a href="manage_ledger_type">Ledger Type</a></li>-->
											<li><a href="manage_note_priority">Note Priority </a></li>
											<li><a href="manage_pay_type">Pay Type</a></li>
											<li><a href="manage_special_program_indicator">Special Program Indicator</a></li>
											<li><a href="manage_student_contact_type">Student Contact Type</a></li>
											<li><a href="manage_student_relationship">Student Relationship </a></li>
											
											<!--<li><a href="manage_title_iv_special">Title IV Special</a></li>
											<li><a href="manage_title_iv_type">Title IV Type</a></li>-->
										</ul>
									</div>
									
									<div class="col-md-2" style="max-width: 10%;">
										<h4 class="text-themecolor">Student</h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_citizenship">Citizenship</a></li>
											<li><a href="manage_highest_level_of_edu">Highest Level Of Edu.</a></li>
											<!--<li><a href="manage_pre_fix">Prefix</a></li>-->
											<li><a href="manage_race">Race</a></li>
										</ul>
									</div>
									
									<div class="col-md-2" style="max-width: 12%;">
										<h4 class="text-themecolor">Admissions</h4>
										<ul style="padding-left: 0;">
											
										</ul>
										
										<h4 class="text-themecolor">Compliance</h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_ipeds_program_award_level">IPEDS Program Award Level</a></li>
										</ul>
									</div>	
									
									<div class="col-md-2" style="max-width: 12%;">
										<h4 class="text-themecolor">Finance</h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_award_year">Award Year</a></li>
											<li><a href="manage_coa_category">COA Category</a></li>
											<li><a href="manage_degree_cert">Degree/Cert</a></li>
											<li><a href="manage_dependancy_override">Dependancy Override</a></li>
											<li><a href="manage_eligable_citizen">Eligable Citizen</a></li>
											<!--<li><a href="manage_grant_type">Grant Types</a></li>-->
											<li><a href="manage_housing_type">Housing Type</a></li>
											<li><a href="manage_isir_setup">ISIR Setup</a></li>
											<li><a href="manage_va_student">VA Student</a></li>
										</ul>
									</div>
									
									<div class="col-md-2" style="max-width: 12%;">
										<h4 class="text-themecolor">Registrar</h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_act_measure">ACT Measure</a></li>
											<li><a href="manage_atb_code">ATB Code</a></li>
											<li><a href="manage_atb_admin_code">ATB Admin Code</a></li>
											<li><a href="manage_atb_test_code">ATB Test Code</a></li>
											<li><a href="manage_attendance_code">Attendance Code</a></li>
											<li><a href="manage_attendance_type">Attendance Type</a></li>
											<li><a href="manage_course_offering_status">Course Offering Status</a></li>
											<li><a href="manage_course_offering_student_status">Course Offering Student Status</a></li>
											<!--<li><a href="manage_grade_scale">Grade Scale</a></li>-->
											<li><a href="manage_sap_result">SAP Result</a></li>
											<li><a href="manage_sat_measure">SAT Measure</a></li>
											<li><a href="manage_school_enrollment_status">School Enrollment Status</a></li>
											<li><a href="manage_transcript_status">Transcript Status</a></li>
										</ul>
									</div>
									
									<div class="col-md-2" style="max-width: 12%;">
										<h4 class="text-themecolor">Accounting</h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_tuition_type">Tuition Type</a></li>
										</ul>
										
										<h4 class="text-themecolor">Accrediting</h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_special">COE CPL Special</a></li>
										</ul>
										
									</div>
									
									<div class="col-md-2" style="max-width: 12%;">
										<h4 class="text-themecolor">Placement</h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_placement_student_status_category">Placement Student Status Category</a></li>
										</ul>
									</div>	
									
									<div class="col-md-4" > 
										<h4 class="text-themecolor">Title IV Servicer</h4>
										<ul style="padding-left: 0;">
											<li>
												Educational Compliance Management(ECM)
												<ul>
													<li><a href="manage_ecm_ledger_type">ECM Type </a></li>
													<li><a href="manage_ecm_ledger">Ledger Codes </a></li>
												</ul>
											</li>
											<li><a href="manage_title_iv_recipients_category">Title IV Recipients Category </a></li>
											<li><a href="manage_90_10_category">90/10 Compliance Category </a></li>
										</ul>
									</div>
								</div>
								
								<div class="row" style="background-color: #ddecf9;margin-bottom: 10px;" >
									<div class="col-md-12">
										<h3 class="text-themecolor" style="background-color: #ddecf9;margin-top: 5px;" >School Level</h4>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-2" style="max-width: 12%;" >
										<h4 class="text-themecolor">General</h4>
										<li><a href="manage_session">Session</a></li>
									</div>
									
									<div class="col-md-2"  >
										<h4 class="text-themecolor">School</h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_departments">Departments</a></li>
											<li><a href="manage_note_status?t=1">Employee Note Status </a></li>
											<li><a href="manage_employee_note_types">Employee Note Types</a></li>
										</ul>
									</div>
									
									<div class="col-md-2" style="max-width: 10%;">
										<h4 class="text-themecolor" >Student</h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_note_status?t=3">Student Event Status </a></li>
											<li><a href="manage_event_other?t=2">Student Event Other</a></li>
											<li><a href="manage_note_types?t=2">Student Event Types</a></li>
											<li><a href="manage_note_types?t=1">Student Note Types</a></li>
											<li><a href="manage_note_status?t=2">Student Note Status </a></li>
											<li><a href="manage_student_status">Student Status</a></li>
										</ul>
										
										<h4 class="text-themecolor" >Task Management</h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_event_other?t=1">Task Other</a></li>
											<li><a href="manage_task_status">Task Status</a></li>
											<li><a href="manage_task_type">Task Type</a></li>
										</ul>
									</div>
									
									<div class="col-md-2" style="max-width: 12%;">
										<h4 class="text-themecolor">Admissions</h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_document_type">Document Type</a></li>
											<li><a href="manage_lead_contact_source">Lead Contact Source</a></li>
											<li><a href="manage_lead_source">Lead Source</a></li>
											<li><a href="manage_lead_source_group">Lead Source Group</a></li>
										</ul>
									</div>
									
									<div class="col-md-2" style="max-width: 12%;">
										<h4 class="text-themecolor">Finance</h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_funding">Fundings</a></li>
											<li><a href="manage_guarantor">Guarantor</a></li>
										</ul>
									</div>
									
									<div class="col-md-2" style="max-width: 12%;">
										<h4 class="text-themecolor">Registrar</h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_credit_transfer_status">Credit Transfer Status</a></li>
											<li><a href="manage_drop_reason">Drop Reason</a></li>
											<li><a href="manage_grade_book_type">Grade Book Type</a></li>
										</ul>
									</div>
									
									<div class="col-md-2" style="max-width: 12%;">
										<h4 class="text-themecolor">Accounting</h4>
									</div>
									
									<div class="col-md-2" style="max-width: 12%;">
										<h4 class="text-themecolor">Placement</h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_placement_company_status">Placement Company Status</a></li>
											<li><a href="manage_placement_status">Placement Status</a></li>
										</ul>
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
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>

</body>

</html>