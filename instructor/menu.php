<?require_once "../language/menu.php";

$res_acc1 = $db->Execute("SELECT HAS_INSTRUCTOR_PORTAL FROM  Z_ACCOUNT where PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_acc1->fields['HAS_INSTRUCTOR_PORTAL'] == 0) {
	header("location:../index");
	exit;
}

$current_page1 = $_SERVER['PHP_SELF'];
$parts1 = explode('/', $current_page1);
$current_page = $parts1[count($parts1) - 1];

if ($current_page == 'index.php') {
    $dash_active = 'class="active"';
} else if ($current_page == 'attendance_entry.php') {
    $attendance_active = 'class="active"';
} else if ($current_page == 'manage_student.php' || $current_page == 'student.php') {
    $student_active = 'class="active"';
} else if ($current_page == 'course_history.php') {
    $course_history_active = 'class="active"';
}
$menu_ib_count = $db->Execute("SELECT PK_INTERNAL_EMAIL_RECEPTION FROM Z_INTERNAL_EMAIL_RECEPTION WHERE VIWED = 0 AND PK_USER = '$_SESSION[PK_USER]' AND SELF_ADDED = 0 GROUP BY INTERNAL_ID");

/* Ticket # 1296  */
$res_ip_acc = $db->Execute("SELECT * FROM Z_ACCOUNT_INSTRUCTOR_PORTAL_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if(!isset($_SESSION['ACADEMIC_REVIEW'])){
	$_SESSION['ATTENDANCE_ENTRY'] 			= $res_ip_acc->fields['ATTENDANCE_ENTRY'];
	$_SESSION['ATTENDANCE_ENTRY_NON_SCH'] 	= $res_ip_acc->fields['ATTENDANCE_ENTRY_NON_SCH'];
	$_SESSION['ATTENDANCE_REVIEW'] 			= $res_ip_acc->fields['ATTENDANCE_REVIEW'];
	$_SESSION['DAILY_ROSTER'] 				= $res_ip_acc->fields['DAILY_ROSTER'];
	$_SESSION['FINAL_GRADE'] 				= $res_ip_acc->fields['FINAL_GRADE'];
	$_SESSION['GRADE_BOOK_ENTRY'] 			= $res_ip_acc->fields['GRADE_BOOK_ENTRY'];
	$_SESSION['GRADE_BOOK_SETUP'] 			= $res_ip_acc->fields['GRADE_BOOK_SETUP'];
	$_SESSION['PROGRAM_GRADE_BOOK'] 		= $res_ip_acc->fields['PROGRAM_GRADE_BOOK'];
	$_SESSION['SAVE_GRADE_BOOK_AS_FINAL'] 	= $res_ip_acc->fields['SAVE_GRADE_BOOK_AS_FINAL'];
	$_SESSION['STUDENTS'] 					= $res_ip_acc->fields['STUDENTS'];
	$_SESSION['COURSE_HISTORY'] 			= $res_ip_acc->fields['COURSE_HISTORY'];
}
if ($current_page == 'attendance_entry.php' && $_SESSION['ATTENDANCE_ENTRY'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'non_scheduled_attendance_entry.php' && $_SESSION['ATTENDANCE_ENTRY_NON_SCH'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'attendance_detail.php' && $_SESSION['ATTENDANCE_REVIEW'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'daily_roster.php' && $_SESSION['DAILY_ROSTER'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'final_grade.php' && $_SESSION['FINAL_GRADE'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'grade_book_entry.php' && $_SESSION['GRADE_BOOK_ENTRY'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'grade_book_setup.php' && $_SESSION['GRADE_BOOK_SETUP'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'points_sessions_entry.php' && $_SESSION['PROGRAM_GRADE_BOOK'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'save_grade_book_as_final.php' && $_SESSION['SAVE_GRADE_BOOK_AS_FINAL'] != 1) {
	header("location:index");
	exit;
} else if (($current_page == 'manage_student.php' || $current_page == 'student.php' || $current_page == 'student_task.php' || $current_page == 'student_notes.php') && $_SESSION['STUDENTS'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'course_history.php' && $_SESSION['COURSE_HISTORY'] != 1) {
	header("location:index");
	exit;
}
/* Ticket # 1296  */
?>
<header class="topbar">
	<nav class="navbar top-navbar navbar-expand-md navbar-dark">
		<div class="navbar-header">
			<a class="navbar-brand" href="index">
				<b>
					<?$res_logo = $db->Execute("SELECT LOGO, ENABLE_INTERNAL_MESSAGE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");?> <!-- ticket #967  -->
					<?if ($res_logo->fields['LOGO'] == '') {?>
						<img src="../backend_assets/images/DDlogo_Trans.png" alt="homepage" class="dark-logo" />
						<img src="../backend_assets/images/DDlogo_Trans.png" alt="homepage" class="light-logo" />
					<?} else {?>
						<img src="<?=$res_logo->fields['LOGO']?>" alt="homepage" class="dark-logo" style="max-height: 66px;" />
						<img src="<?=$res_logo->fields['LOGO']?>" alt="homepage" class="light-logo" style="max-height: 66px;" />
					<?}?>
				</b>
			</a>
		</div>
		<div class="navbar-collapse">
			<ul class="navbar-nav mr-auto">
				<!-- This is  -->
				<li class="nav-item"> <a class="nav-link nav-toggler d-block d-md-none waves-effect waves-dark" href="javascript:void(0)"><i class="ti-menu"></i></a> </li>
				<li class="nav-item"> <a class="nav-link sidebartoggler d-none waves-effect waves-dark" href="javascript:void(0)"><i class="icon-menu"></i></a> </li>
			</ul>

			<ul class="navbar-nav my-lg-0" style="max-height: 70px;" >
				<!-- ============================================================== -->
				<!-- Comment -->
				<!-- ============================================================== -->
				<li class="nav-item d-flex align-items-center school-name <?if ($_SESSION['CAMPUS_NAME'] != '') {?> campus-active <?}?> " style="overflow:hidden" >
					<span style="line-height: 30px;" ><?=$_SESSION['SCHOOL_NAME']?></span>
				</li>

				<?if ($_SESSION['CAMPUS_NAME'] != '') {?>
				<li class="nav-item d-flex align-items-center campus-name" style="overflow:hidden" >
					<span style="line-height: 30px;" ><?=$_SESSION['CAMPUS_NAME']?></span>
				</li>
				<?}?>

				<!--<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle waves-effect waves-dark" href="manage_ticket" aria-haspopup="true" aria-expanded="false"> <i class="fas fa-ticket-alt"></i>
						<div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
					</a>
				</li>-->
				
				<!-- ticket #967  -->
				<? $res_emp = $db->Execute("SELECT INTERNAL_MESSAGE_ENABLED FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' ");
				if($res_logo->fields['ENABLE_INTERNAL_MESSAGE'] == 1 && $res_emp->fields['INTERNAL_MESSAGE_ENABLED'] == 1) { ?>
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

				<!-- Ticket #990
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle waves-effect waves-dark" href="" id="2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="icon-note"></i>
						<div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
					</a>
					<div class="dropdown-menu mailbox dropdown-menu-right animated bounceInDown" aria-labelledby="2">
						<ul>
							<li>
								<div class="drop-title"><?=MNU_NOTIFICATIONS?></div>
							</li>
							<li>
								<div class="message-center">
									
								</div>
							</li>
						</ul>
					</div>
				</li>
				-->
				
				<!--<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle waves-effect waves-dark" href="help_docs" aria-haspopup="true" aria-expanded="false"> <i class="mdi mdi-help"></i>
						<div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
					</a>
				</li>-->

				<li class="nav-item dropdown u-pro">
					<a class="nav-link dropdown-toggle waves-effect waves-dark profile-pic" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<?if ($_SESSION['PROFILE_IMAGE'] != '') {?>
							<img src="<?=$_SESSION['PROFILE_IMAGE']?>" alt="user" class="">
						<?} else {?>
							<img src="../backend_assets/images/user.png" alt="user" class="">
						<?}?>
						<span class="hidden-md-down"><?=$_SESSION['NAME']?> &nbsp;<i class="fa fa-angle-down"></i></span>
					</a>
					<div class="dropdown-menu dropdown-menu-right animated flipInY">
						<!-- text-->
						<!--<a href="profile" class="dropdown-item"><i class="ti-user"></i> <?=MNU_MY_PROFILE?></a>-->
						<a href="change_password" class="dropdown-item"><i class="ti-lock"></i> <?=MNU_CHANGE_PASSWORD?></a>
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

<aside class="left-sidebar" id="instructor">
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

				<li <?=$dash_active?> ><a class="waves-effect waves-dark" href="index" ><i class="icon-speedometer"></i><span class="hide-menu"><?=MNU_DASHBOARD?></span></a></li>

				<? if($_SESSION['ATTENDANCE_ENTRY'] == 1 || $_SESSION['ATTENDANCE_REVIEW'] == 1 || $_SESSION['DAILY_ROSTER'] == 1){ ?>
				<li <?=$attendance_active?> class="nav-item dropdown"><a class="nav-link dropdown-toggle waves-effect waves-dark" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="manage_student?t=1" ><i class="mdi mdi-account-plus"></i><span class="hide-menu"><?=MNU_ATTENDANCE?></span></a>
					<div class="dropdown-menu dropdown-menu-right animated fadeInDown">
						<? if($_SESSION['ATTENDANCE_ENTRY'] == 1){ ?>
						<a href="attendance_entry" class="dropdown-item"><i class="ti-layers"></i> <?=MNU_ATTENDANCE_ENTRY?></a>
						<? }
						if($_SESSION['ATTENDANCE_ENTRY_NON_SCH'] == 1){ ?>
						<a href="non_scheduled_attendance_entry" class="dropdown-item"><i class="ti-layers"></i> <?=MNU_ATTENDANCE_ENTRY_NON_SCHEDULED?></a>
						<? }
						if($_SESSION['ATTENDANCE_REVIEW'] == 1){ ?>
						<a href="attendance_detail" class="dropdown-item"><i class="ti-layers-alt"></i> <?=MNU_ATTENDANCE_REVIEW?></a>
						<? }
						if($_SESSION['DAILY_ROSTER'] == 1){ ?>
						<a href="daily_roster" class="dropdown-item"><i class="ti-brush-alt"></i> <?=MNU_DAILY_ROSTER?></a>
						<? } ?>
					</div>
				</li>
				<? } ?>

				<? if($_SESSION['FINAL_GRADE'] == 1 || $_SESSION['GRADE_BOOK_ENTRY'] == 1 || $_SESSION['GRADE_BOOK_SETUP'] == 1 || $_SESSION['PROGRAM_GRADE_BOOK'] == 1 || $_SESSION['SAVE_GRADE_BOOK_AS_FINAL'] == 1){ ?>
				<li <?=$admission_active?> class="nav-item dropdown"><a class="nav-link dropdown-toggle waves-effect waves-dark" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="manage_student?t=1" ><i class="mdi mdi-account-plus"></i><span class="hide-menu"><?=MNU_GRADES?></span></a>
					<div class="dropdown-menu dropdown-menu-right animated fadeInDown">
						<? if($_SESSION['FINAL_GRADE'] == 1){ ?>
						<a href="final_grade" class="dropdown-item"><i class="ti-layers"></i> <?=MNU_FINAL_GRADE?></a>
						<? }
						if($_SESSION['GRADE_BOOK_ENTRY'] == 1){ ?>
						<a href="grade_book_entry" class="dropdown-item"><i class="ti-layers-alt"></i> <?=MNU_GRADE_BOOK_ENTRY?></a>
						<? }
						if($_SESSION['GRADE_BOOK_SETUP'] == 1){ ?>
						<a href="grade_book_setup" class="dropdown-item"><i class="ti-brush-alt"></i> <?=MNU_GRADE_BOOK_SETUP?></a>
						<? }
						if($_SESSION['PROGRAM_GRADE_BOOK'] == 1){ ?>
						<a href="points_sessions_entry" class="dropdown-item"><i class="ti-layers"></i> <?=MNU_POINTS_SESSIONS_ENTRY?></a>
						<? }
						if($_SESSION['SAVE_GRADE_BOOK_AS_FINAL'] == 1){ ?>
						<a href="save_grade_book_as_final" class="dropdown-item"><i class="ti-brush-alt"></i> <?=MNU_SAVE_GRADE_BOOK_AS_FINAL?></a>
						<? } ?>
					</div>
				</li>
				<? } ?>

				<? if($_SESSION['STUDENTS'] == 1){ ?>
				<li <?=$student_active?> ><a class="waves-effect waves-dark" href="manage_student" ><i class="fas fa-address-card"></i><span class="hide-menu"><?=STUDENTS?></span></a></li>
				<? }
				if($_SESSION['COURSE_HISTORY'] == 1){ ?>
				<li <?=$course_history_active?> ><a class="waves-effect waves-dark" href="course_history" ><i class="ti-layers"></i><span class="hide-menu"><?=MNU_COURSE_HISTORY?></span></a></li>
				<? } ?>
			</ul>
		</nav>
		<!-- End Sidebar navigation -->
	</div>
	<!-- End Sidebar scroll-->
</aside>