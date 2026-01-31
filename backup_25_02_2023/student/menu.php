<?require_once "../language/menu.php";

$current_page1 = $_SERVER['PHP_SELF'];
$parts1 = explode('/', $current_page1);
$current_page = $parts1[count($parts1) - 1];

if ($current_page == 'index.php') {
    $dash_active = 'class="active"';
} else if ($current_page == 'profile.php') {
    $my_profile_active = 'class="active"';
} else if ($current_page == 'schedule.php') {
    $schedule_active = 'class="active"';
} else if ($current_page == 'payments.php') {
    $payments_active = 'class="active"';
} else if ($current_page == 'enrollment.php') {
    $enrollment_active = 'class="active"';
} else if ($current_page == 'academic_review.php') {
    $academic_review_active = 'class="active"';
} else if ($current_page == 'attendance_summary.php') {
    $attendance_summary_active = 'class="active"';
} else if ($current_page == 'ledger.php') {
    $ledger_active = 'class="active"';
}
$menu_ib_count = $db->Execute("SELECT PK_INTERNAL_EMAIL_RECEPTION FROM Z_INTERNAL_EMAIL_RECEPTION WHERE VIWED = 0 AND PK_USER = '$_SESSION[PK_USER]' GROUP BY INTERNAL_ID");

/* Ticket # 1198  */
$res_sp_acc = $db->Execute("SELECT * FROM Z_ACCOUNT_STUDENT_PORTAL_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if(!isset($_SESSION['ACADEMIC_REVIEW'])){
	$_SESSION['ACADEMIC_REVIEW'] 				= $res_sp_acc->fields['ACADEMIC_REVIEW'];
	$_SESSION['ACADEMIC_REVIEW_BY_TERM'] 		= $res_sp_acc->fields['ACADEMIC_REVIEW_BY_TERM'];
	$_SESSION['COSMETOLOGY_GRADE_BOOK_LABS'] 	= $res_sp_acc->fields['COSMETOLOGY_GRADE_BOOK_LABS'];
	$_SESSION['COSMETOLOGY_GRADE_BOOK_SUMMARY'] = $res_sp_acc->fields['COSMETOLOGY_GRADE_BOOK_SUMMARY'];
	$_SESSION['COSMETOLOGY_GRADE_BOOK_TEST'] 	= $res_sp_acc->fields['COSMETOLOGY_GRADE_BOOK_TEST'];
	$_SESSION['GRADE_BOOK'] 					= $res_sp_acc->fields['GRADE_BOOK'];
	$_SESSION['PROGRAM_COURSE_PROGRESS'] 		= $res_sp_acc->fields['PROGRAM_COURSE_PROGRESS'];
	$_SESSION['ATTENDANCE_REVIEW'] 				= $res_sp_acc->fields['ATTENDANCE_REVIEW'];
	$_SESSION['ATTENDANCE_SUMMARY'] 			= $res_sp_acc->fields['ATTENDANCE_SUMMARY'];
	$_SESSION['FINANCIAL_AID_AWARDS'] 			= $res_sp_acc->fields['FINANCIAL_AID_AWARDS'];
	$_SESSION['PAYMENT_SCHEDULE'] 				= $res_sp_acc->fields['PAYMENT_SCHEDULE'];
	$_SESSION['STUDENT_LEDGER'] 				= $res_sp_acc->fields['STUDENT_LEDGER'];
	$_SESSION['SCHEDULE'] 						= $res_sp_acc->fields['SCHEDULE'];
}
if ($current_page == 'academic_review.php' && $_SESSION['ACADEMIC_REVIEW'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'academic_review_by_term.php' && $_SESSION['ACADEMIC_REVIEW_BY_TERM'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'cosmetology_grade_book_labs.php' && $_SESSION['COSMETOLOGY_GRADE_BOOK_LABS'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'cosmetology_grade_book_summary.php' && $_SESSION['COSMETOLOGY_GRADE_BOOK_SUMMARY'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'cosmetology_grade_book_test.php' && $_SESSION['COSMETOLOGY_GRADE_BOOK_TEST'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'grade_book.php' && $_SESSION['GRADE_BOOK'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'program_course_progress.php' && $_SESSION['PROGRAM_COURSE_PROGRESS'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'attendance_review.php' && $_SESSION['ATTENDANCE_REVIEW'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'attendance_summary.php' && $_SESSION['ATTENDANCE_SUMMARY'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'financial_aid_awards.php' && $_SESSION['FINANCIAL_AID_AWARDS'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'payments.php' && $_SESSION['PAYMENT_SCHEDULE'] != 1) {
	header("location:index");
	exit;
} else if ($current_page == 'ledger.php' && $_SESSION['STUDENT_LEDGER'] != 1) {
	header("location:index");
	exit;
}  else if ($current_page == 'schedule.php' && $_SESSION['SCHEDULE'] != 1) {
	header("location:index");
	exit;
}
/* Ticket # 1198  */
?>
<header class="topbar">
	<nav class="navbar top-navbar navbar-expand-md navbar-dark">
		<div class="navbar-header">
			<a class="navbar-brand" href="index">
				<b>
					<? $res_logo = $db->Execute("SELECT LOGO, _1098T_TAX_FORM, ENABLE_INTERNAL_MESSAGE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); //ticket #967 ?>
					<? if ($res_logo->fields['LOGO'] == '') {?>
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
				<li class="nav-item d-flex align-items-center school-name " >
					<span style="line-height: 30px;" ><?=$_SESSION['SCHOOL_NAME']?></span>
				</li>

				<!--<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle waves-effect waves-dark" href="help_docs" aria-haspopup="true" aria-expanded="false"> <i class="mdi mdi-help"></i>
						<div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
					</a>
				</li>-->
				
				<!-- ticket #967  -->
				<? if($res_logo->fields['ENABLE_INTERNAL_MESSAGE'] == 1) {?>
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
				<li class="user-pro"> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
					<?if ($_SESSION['PROFILE_IMAGE'] != '') {?>
						<img src="<?=$_SESSION['PROFILE_IMAGE']?>" alt="user" class="">
					<?} else {?>
						<img src="../backend_assets/images/user.png" alt="user" class="">
					<?}?>
					<span class="hide-menu"><?=$_SESSION['NAME']?></span></a>
					<ul aria-expanded="false" class="collapse">
						<li><a href="javascript:void(0)"><i class="ti-user"></i> <?=MNU_MY_PROFILE?></a></li>
						<li><a href="../logout"><i class="fa fa-power-off"></i> <?=MNU_LOGOUT?></a></li>
					</ul>
				</li>

				<li <?=$dash_active?> ><a class="waves-effect waves-dark" href="index" ><i class="icon-speedometer"></i><span class="hide-menu"><?=MNU_DASHBOARD?></span></a></li>
						
				<li <?=$my_profile_active?> ><a class="waves-effect waves-dark" href="profile" ><i class="fas fa-user"></i><span class="hide-menu"><?=MNU_MY_PROFILE?></span></a></li>
				
				<li ><a class="waves-effect waves-dark" href="../school/create_student_id?s=1" ><span class="hide-menu"><?=DIGITAL_STUDENT_ID ?></span></a></li>
				
				<? if($_SESSION['ACADEMIC_REVIEW'] == 1 || $_SESSION['ACADEMIC_REVIEW_BY_TERM'] == 1 || $_SESSION['COSMETOLOGY_GRADE_BOOK_LABS'] == 1 || $_SESSION['COSMETOLOGY_GRADE_BOOK_SUMMARY'] == 1 || $_SESSION['COSMETOLOGY_GRADE_BOOK_TEST'] == 1 || $_SESSION['GRADE_BOOK'] == 1 || $_SESSION['PROGRAM_COURSE_PROGRESS'] == 1){ ?>
				<li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="mdi mdi-book-open-variant"></i><span class="hide-menu"><?=MNU_ACADEMIC ?> </span></a>
					<ul aria-expanded="false" class="collapse">
						<!-- Ticket #990 -->
						<!-- <li ><a href="enrollment" ><?=MNU_COURSE_ENROLLMENT?></a></li> -->
						<? if($_SESSION['ACADEMIC_REVIEW'] == 1){ ?>
						<li ><a href="academic_review" ><?=MNU_ACADEMIC_REVIEW?></a></li>
						<? }
						if($_SESSION['ACADEMIC_REVIEW_BY_TERM'] == 1){ ?>
						<li ><a href="academic_review_by_term" ><?=MNU_ACADEMIC_REVIEW_BY_TERM?></a></li>
						<? }
						if($_SESSION['COSMETOLOGY_GRADE_BOOK_LABS'] == 1){ ?>
						<li ><a href="cosmetology_grade_book_labs" ><?=MNU_COSMETOLOGY_GRADE_BOOK_LABS?></a></li>
						<? }
						if($_SESSION['COSMETOLOGY_GRADE_BOOK_SUMMARY'] == 1){ ?>
						<li ><a href="cosmetology_grade_book_summary" ><?=MNU_COSMETOLOGY_GRADE_BOOK_SUMMARY?></a></li>
						<? }
						if($_SESSION['COSMETOLOGY_GRADE_BOOK_TEST'] == 1){ ?>
						<li ><a href="cosmetology_grade_book_test" ><?=MNU_COSMETOLOGY_GRADE_BOOK_TEST?></a></li>
						<? }
						if($_SESSION['GRADE_BOOK'] == 1){ ?>
						<li ><a href="grade_book" ><?=MNU_GRADE_BOOK?></a></li>
						<? }
						if($_SESSION['PROGRAM_COURSE_PROGRESS'] == 1){ ?>
						<li ><a href="program_course_progress" ><?=MNU_PROGRAM_COURSE_PROGRESS?></a></li>
						<? } ?>
					</ul>
				</li>
				<? } ?>
				
				<? if($_SESSION['ATTENDANCE_REVIEW'] == 1 || $_SESSION['ATTENDANCE_SUMMARY'] == 1){ ?>
				<li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="mdi mdi-vector-circle"></i><span class="hide-menu"><?=MNU_ATTENDANCE ?> </span></a>
					<ul aria-expanded="false" class="collapse">
						<? if($_SESSION['ATTENDANCE_REVIEW'] == 1){ ?>
						<li ><a href="attendance_review" ><?=MNU_ATTENDANCE_REVIEW?></a></li>
						<? }
						if($_SESSION['ATTENDANCE_SUMMARY'] == 1){ ?>
						<li ><a href="attendance_summary" ><?=MNU_ATTENDANCE_SUMMARY?></a></li>
						<? } ?>
					</ul>
				</li>
				<? } ?>
				
				<? if($_SESSION['FINANCIAL_AID_AWARDS'] == 1 || $_SESSION['PAYMENT_SCHEDULE'] == 1 || $_SESSION['STUDENT_LEDGER'] == 1 || $res_logo->fields['_1098T_TAX_FORM'] == 1){ //Ticket # 1321 ?>
				<!-- Ticket #1048 -->
				<li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="fas fa-dollar-sign"></i><span class="hide-menu"><?=MNU_FINANCE ?> </span></a>
					<ul aria-expanded="false" class="collapse">
						<? if ($res_logo->fields['_1098T_TAX_FORM'] == 1) {?>
							<li ><a href="_1098T" ><?=MNU_1098T_TAX_FORM ?></a></li>
						<? }
						if($_SESSION['FINANCIAL_AID_AWARDS'] == 1){ ?>
						<li ><a href="financial_aid_awards" ><?=MNU_FINANCIAL_AID_AWARDS ?></a></li>
						<? }
						if($_SESSION['PAYMENT_SCHEDULE'] == 1){ ?>
						<li ><a href="payments" ><?=MNU_PAYMENT_SCHEDULE?></a></li>
						<? }
						if($_SESSION['STUDENT_LEDGER'] == 1){ ?>
						<li ><a href="ledger" ><?=MNU_STUDENT_LEDGER?></a></li>
						<? } ?>
					</ul>
				</li>
				<!-- Ticket #1048 -->
				<? } ?>
				
				<? if($_SESSION['SCHEDULE'] == 1){ ?>
				<li <?=$schedule_active?> ><a class="waves-effect waves-dark" href="schedule" ><i class="fas fa-calendar-alt"></i><span class="hide-menu"><?=MNU_SCHEDULE?></span></a></li>
				<? } ?>
			</ul>
		</nav>
		<!-- End Sidebar navigation -->
	</div>
	<!-- End Sidebar scroll-->
</aside>