<? $current_page1 = $_SERVER['PHP_SELF'];
$parts1 = explode('/', $current_page1);
$current_page = $parts1[count($parts1) - 1]; 

if($current_page == 'index.php')
	$dash_active  = 'class="active"'; 
else if($current_page == 'manage_education_type.php' || $current_page == 'education_type.php' || $current_page == 'setup.php' || $current_page == 'manage_earning_type.php' || $current_page == 'earning_type.php' || $current_page == 'manage_grant_type.php' || $current_page == 'grant_type.php' || $current_page == 'manage_citizenship.php' || $current_page == 'citizenship.php' || $current_page == 'manage_marital_status.php' || $current_page == 'marital_status.php' || $current_page == 'manage_student_status.php' || $current_page == 'student_status.php' || $current_page == 'manage_departments.php' || $current_page == 'sales_channel.php' || $current_page == 'manage_body_types.php' || $current_page == 'body_types.php' || $current_page == 'manage_return_policy.php' || $current_page == 'departments.php' || $current_page == 'manage_attendance_code.php' || $current_page == 'attendance_code.php' || $current_page == 'manage_drop_reason.php' || $current_page == 'drop_reason.php' || $current_page == 'manage_funding.php' || $current_page == 'funding.php' || $current_page == 'manage_contact_types.php' || $current_page == 'contact_types.php' || $current_page == 'manage_note_types.php' || $current_page == 'note_types.php' || $current_page == 'manage_race.php' || $current_page == 'race.php' || $current_page == 'manage_pre_fix.php' || $current_page == 'pre_fix.php' || $current_page == 'manage_document_type.php' || $current_page == 'document_type.php' || $current_page == 'manage_task_type.php' || $current_page == 'task_type.php' || $current_page == 'manage_task_status.php' || $current_page == 'task_status.php' || $current_page == 'manage_lead_source.php' || $current_page == 'lead_source.php' || $current_page == 'manage_lead_contact_type.php' || $current_page == 'lead_contact_type.php' || $current_page == 'manage_student_contact_type.php' || $current_page == 'student_contact_type.php' || $current_page == 'manage_student_relationship.php' || $current_page == 'student_relationship.php' || $current_page == 'smtp_settings.php' || $current_page == 'manage_ticket_status.php' || $current_page == 'ticket_status.php' || $current_page == 'manage_ticket_priority.php' || $current_page == 'ticket_priority.php' || $current_page == 'manage_note_status.php' || $current_page == 'note_status.php' || $current_page == 'manage_note_priority.php' || $current_page == 'note_priority.php' || $current_page == 'manage_help.php' || $current_page == 'help.php'  || $current_page == 'manage_employee_note_types.php' || $current_page == 'employee_note_types.php' || $current_page == 'manage_tuition_type.php' || $current_page == 'tuition_type.php' || $current_page == 'manage_grade_scale.php' || $current_page == 'grade_scale.php' || $current_page == 'manage_housing_type.php' || $current_page == 'housing_type.php' || $current_page == 'manage_dependent_status.php' || $current_page == 'dependent_status.php' || $current_page == 'manage_ge_disclosure.php' || $current_page == 'ge_disclosure.php' || $current_page == 'manage_fee_type.php' || $current_page == 'fee_type.php' || $current_page == 'manage_session.php' || $current_page == 'session.php' || $current_page == 'manage_distance_learning.php' || $current_page == 'distance_learning.php' || $current_page == 'manage_highest_level_of_edu.php' || $current_page == 'highest_level_of_edu.php' || $current_page == 'manage_transcript_status.php' || $current_page == 'transcript_status.php' || $current_page == 'manage_attendance_type.php' || $current_page == 'attendance_type.php' || $current_page == 'manage_grade_book_type.php' || $current_page == 'grade_book_type.php' || $current_page == 'manage_ledger_type.php' || $current_page == 'ledger_type.php' || $current_page == 'manage_title_iv_type.php' || $current_page == 'title_iv_type.php' || $current_page == 'manage_title_iv_special.php' || $current_page == 'title_iv_special.php' || $current_page == 'manage_90_10_group.php' || $current_page == '90_10_group.php' || $current_page == 'manage_legacy_ipeds.php' || $current_page == 'legacy_ipeds.php' || $current_page == 'manage_placement_status.php' || $current_page == 'placement_status.php' || $current_page == 'manage_act_measure.php' || $current_page == 'act_measure.php' || $current_page == 'manage_atb_code.php' || $current_page == 'atb_code.php' || $current_page == 'manage_atb_admin_code.php' || $current_page == 'atb_admin_code.php' || $current_page == 'manage_atb_test_code.php' || $current_page == 'atb_test_code.php' || $current_page == 'manage_sat_measure.php' || $current_page == 'sat_measure.php' || $current_page == 'manage_award_year.php' || $current_page == 'award_year.php' || $current_page == 'manage_end_year.php' || $current_page == 'end_year.php' || $current_page == 'manage_event_type.php' || $current_page == 'event_type.php' || $current_page == 'manage_probation_type.php' || $current_page == 'probation_type.php' || $current_page == 'manage_probation_level.php' || $current_page == 'probation_level.php' || $current_page == 'manage_probation_status.php' || $current_page == 'probation_status.php' || $current_page == 'manage_help_category.php' || $current_page == 'help_category.php' || $current_page == 'manage_help_sub_category.php' || $current_page == 'help_sub_category.php' || $current_page == 'manage_event_other.php' || $current_page == 'event_other.php' || $current_page == 'manage_course_offering_student_status.php' || $current_page == 'course_offering_student_status.php' || $current_page == 'manage_credit_transfer_status.php' || $current_page == 'credit_transfer_status.php' || $current_page == 'manage_ipeds_category.php' || $current_page == 'ipeds_category.php' || $current_page == 'manage_placement_company_status.php' || $current_page == 'placement_company_status.php' || $current_page == 'manage_placement_student_status_category.php' || $current_page == 'placement_student_status_category.php' || $current_page == 'manage_school_enrollment_status.php' || $current_page == 'school_enrollment_status.php' || $current_page == 'manage_ticket_category.php' || $current_page == 'ticket_category.php' || $current_page == 'manage_lead_source_group.php' || $current_page == 'ead_source_group.php' || $current_page == 'manage_degree_cert.php' || $current_page == 'degree_cert.php' || $current_page == 'manage_coa_category.php' || $current_page == 'coa_category.php' || $current_page == 'manage_special.php' || $current_page == 'special.php' || $current_page == 'manage_course_offering_status.php' || $current_page == 'course_offering_status.php' || $current_page == 'manage_ecm_ledger.php' || $current_page == 'ecm_ledger.php' || $current_page == 'manage_ecm_ledger_type.php' || $current_page == 'ecm_ledger_type.php' || $current_page == 'manage_email_template.php' || $current_page == 'email_template.php' || $current_page == 'manage_title_iv_recipients_category.php' || $current_page == 'title_iv_recipients_category.php' || $current_page == 'manage_90_10_category.php' || $current_page == '90_10_category.php' || $current_page == 'manage_isir_setup.php' || $current_page == 'isir_setup.php' || $current_page == 'manage_ipeds_program_award_level.php' || $current_page == 'ipeds_program_award_level.php' || $current_page == 'manage_release_notes.php' || $current_page == 'release_notes.php' || $current_page == 'manage_release_notes_category.php' || $current_page == 'release_notes_category.php' || $current_page == 'manage_release_notes_type.php' || $current_page == 'release_notes_type.php' || $current_page == 'manage_sap_result.php' || $current_page == 'sap_result.php' || $current_page == 'manage_va_student.php' || $current_page == 'va_student.php' || $current_page == 'manage_eligable_citizen.php' || $current_page == 'eligable_citizen.php' || $current_page == 'manage_dependancy_override.php' || $current_page == 'dependancy_override.php' || $current_page == 'manage_text_template.php' || $current_page == 'text_template.php' || $current_page == 'manage_gender.php' || $current_page == 'gender.php') 
	$setup_active  = 'class="active"'; 
else if($current_page == 'accounts.php' || $current_page == 'manage_accounts.php')	
	$accounts_active  = 'class="active"';
else if($current_page == 'student.php')	
	$student_active  = 'class="active"';	
else if($current_page == 'update_course_offering_all.php' || $current_page == 'update_course_offering.php' || $current_page == 'update_course_offering_grade_book_exists.php' || $current_page == 'update_course_offering_grade_info.php' || $current_page == 'update_course_offering_all_grade_book_exists.php' || $current_page == 'update_default_grade.php' )	
	$tool_active  = 'class="active"'; 
$menu_ib_count = $db->Execute("SELECT PK_INTERNAL_EMAIL_RECEPTION FROM Z_INTERNAL_EMAIL_RECEPTION WHERE VIWED = 0 AND SELF_ADDED = 0 AND PK_USER = '$_SESSION[PK_USER]' GROUP BY INTERNAL_ID");	
?>
<header class="topbar">
	<nav class="navbar top-navbar navbar-expand-md navbar-dark">
		<div class="navbar-header">
			<a class="navbar-brand" href="index">
				<b>
					<img src="../backend_assets/images/DDlogo_Trans.png" alt="homepage" class="dark-logo" />
					<img src="../backend_assets/images/DDlogo_Trans.png" alt="homepage" class="light-logo" />
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
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle waves-effect waves-dark" href="manage_ticket" aria-haspopup="true" aria-expanded="false"> <i class="fas fa-ticket-alt"></i>
						<div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
					</a>
				</li>
				
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle waves-effect waves-dark" href="my_mails" aria-haspopup="true" aria-expanded="false">
						<i class="ti-email"></i>
						<div class="notify"> 
							<? if($menu_ib_count->RecordCount() > 0) { ?> 
								<span class="heartbit" ></span> <span class="point"></span>
							<? } ?>
						</div>
					</a>
				</li>
				
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle waves-effect waves-dark" href="" id="2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="icon-note"></i>
						<div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
					</a>
					<div class="dropdown-menu mailbox dropdown-menu-right animated bounceInDown" aria-labelledby="2">
						<ul>
							<li>
								<div class="drop-title">Notifications</div>
							</li>
							
							<li>
								<div class="message-center">
									<!-- Message -->
									<a href="javascript:void(0)">
										<div class="user-img"> <img src="../backend_assets/images/users/1.jpg" alt="user" class="img-circle"> <span class="profile-status online pull-right"></span> </div>
										<div class="mail-contnet">
											<h5>Pavan kumar</h5> <span class="mail-desc">Just see the my admin!</span> <span class="time">9:30 AM</span> </div>
									</a>
									<!-- Message -->
									<a href="javascript:void(0)">
										<div class="user-img"> <img src="../backend_assets/images/users/2.jpg" alt="user" class="img-circle"> <span class="profile-status busy pull-right"></span> </div>
										<div class="mail-contnet">
											<h5>Sonu Nigam</h5> <span class="mail-desc">I've sung a song! See you at</span> <span class="time">9:10 AM</span> </div>
									</a>
									<!-- Message -->
									<a href="javascript:void(0)">
										<div class="user-img"> <img src="../backend_assets/images/users/3.jpg" alt="user" class="img-circle"> <span class="profile-status away pull-right"></span> </div>
										<div class="mail-contnet">
											<h5>Arijit Sinh</h5> <span class="mail-desc">I am a singer!</span> <span class="time">9:08 AM</span> </div>
									</a>
									<!-- Message -->
									<a href="javascript:void(0)">
										<div class="user-img"> <img src="../backend_assets/images/users/4.jpg" alt="user" class="img-circle"> <span class="profile-status offline pull-right"></span> </div>
										<div class="mail-contnet">
											<h5>Pavan kumar</h5> <span class="mail-desc">Just see the my admin!</span> <span class="time">9:02 AM</span> </div>
									</a>
								</div>
							</li>
							<li>
								<a class="nav-link text-center link" href="javascript:void(0);"> <strong>See all Notifications</strong> <i class="fa fa-angle-right"></i> </a>
							</li>
						</ul>
					</div>
				</li>
				
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle waves-effect waves-dark" href="help_docs" aria-haspopup="true" aria-expanded="false"> <i class="mdi mdi-help"></i>
						<div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
					</a>
				</li>

				<li class="nav-item dropdown u-pro">
					<a class="nav-link dropdown-toggle waves-effect waves-dark profile-pic" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="../backend_assets/images/users/1.jpg" alt="user" class=""> <span class="hidden-md-down"><?=$_SESSION['NAME']?> &nbsp;<i class="fa fa-angle-down"></i></span> </a>
					<div class="dropdown-menu dropdown-menu-right animated flipInY">
						<!-- text-->
						<a href="profile" class="dropdown-item"><i class="ti-user"></i> My Profile</a>
						<a href="change_password" class="dropdown-item"><i class="ti-user"></i> Change Password</a>
						<div class="dropdown-divider"></div>
						<!-- text-->
						<a href="../logout" class="dropdown-item"><i class="fa fa-power-off"></i> Logout</a>
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
						<li><a href="profile"><i class="ti-user"></i> My Profile</a></li>
						<li><a href="change_password" class="dropdown-item"><i class="ti-user"></i> Change Password</a></li>
						<li><a href="../logout"><i class="fa fa-power-off"></i> Logout</a></li>
					</ul>
				</li>
				<li class="nav-small-cap">--- PERSONAL</li>
				<li <?=$dash_active?> ><a class="waves-effect waves-dark" href="index" ><i class="icon-speedometer"></i><span class="hide-menu">Dashboard</span></a></li>
				
				<li <?=$accounts_active?> ><a class="waves-effect waves-dark" href="manage_accounts" ><i class="ti-layout-grid2"></i><span class="hide-menu">Accounts</span></a></li>
				
				<li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-pie-chart"></i><span class="hide-menu">Reports <span class="badge badge-pill badge-cyan ml-auto">4</span></span></a>
					<ul aria-expanded="false" class="collapse">
						<li><a href="manage_active_users">Active Users </a></li>
						<li><a href="manage_user_activity">User Activity</a></li>
					</ul>
				</li>
				
				<li <?=$setup_active?> ><a class="waves-effect waves-dark" href="setup" ><i class="ti-settings"></i><span class="hide-menu">Setup</span></a></li>
				
				<li ><a class="waves-effect waves-dark" href="../api-document" target="_blank" ><i class="ti-settings"></i><span class="hide-menu">API Document</span></a></li>
				
				<li <?=$tool_active?> > <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="ti-pie-chart"></i><span class="hide-menu">Tools </span></a>
					<ul aria-expanded="false" class="collapse">
						<li><a href="encrypt_ssn">Encrypt SSN </a></li><!-- Ticket # 1286  -->
						<li><a href="update_course_offering_all">Update All Course Offering </a></li>
						<li><a href="update_course_offering">Update Course Offering</a></li>
						<li><a href="update_course_offering_grade_book_exists">Update Course Offering If Grade Book Data Exists</a></li>
						<li><a href="update_course_offering_all_grade_book_exists">Update All Course Offering If Grade Book Data Exists</a></li>
						<li><a href="update_course_offering_grade_info">Update Course Offering Grade Info</a></li>
						
						<li><a href="update_default_grade">Update Default Grade</a></li>
						
					</ul>
				</li>
			</ul>
		</nav>
		<!-- End Sidebar navigation -->
	</div>
	<!-- End Sidebar scroll-->
</aside>