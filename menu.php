<? $current_page1 = $_SERVER['PHP_SELF'];
$parts1 = explode('/', $current_page1);
$current_page = $parts1[count($parts1) - 1];
$home_active 	= "";
$aboutus_active = "";
$signin_active 	= "";
$signup_active 	= "";
if ($current_page == 'index.php' || $current_page == '')
	$home_active  = 'class="active"';
else if ($current_page == 'about-us.php')
	$aboutus_active  = 'class="active"';
else if ($current_page == 'signin.php')
	$signin_active  = 'class="active"';
else if ($current_page == 'signup.php')
	$signup_active  = 'class="active"';
else if ($current_page == 'api-document.php')
	$api_active  = 'class="active"';
?>
<header class="topbar">
	<nav class="navbar top-navbar navbar-expand-md navbar-dark">
		<div class="navbar-header">
			<a class="navbar-brand" href="index">
				<b>
					<img src="backend_assets/images/DDlogo_FullColor_333.png" alt="homepage" class="dark-logo" />
					<img src="backend_assets/images/DDlogo_FullColor_333.png" alt="homepage" class="light-logo" />
				</b>
			</a>
		</div>
		<div class="navbar-collapse">
			<ul class="navbar-nav ml-auto">
				<li class="nav-item"> <a class="nav-link sidebartoggler waves-effect waves-dark" href="javascript:void(0)">
						<img src="assets/images/menu.png">
					</a> </li>
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
				<li <?= $home_active; ?>><a class="waves-effect waves-dark" href="index"><span class="hide-menu">Home</span></a>
				<li <?= $aboutus_active; ?>><a class="waves-effect waves-dark" href="about-us"><span class="hide-menu">About us</span></a></li>
				<li <?= $aboutus_active; ?>><a class="waves-effect waves-dark" href="#"><span class="hide-menu">Blog</span></a></li>
				<li <?= $aboutus_active; ?>><a class="waves-effect waves-dark" href="#"><span class="hide-menu">Support Portal</span></a></li>
				<li <?= $aboutus_active; ?>><a class="waves-effect waves-dark" href="#"><span class="hide-menu">Webinars</span></a></li>
				<li <?= $signin_active; ?>><a class="waves-effect waves-dark" href="signin"><span class="hide-menu">Sign In</span></a></li>
				<li <?= $signup_active; ?>><a class="waves-effect waves-dark" href="signup"><span class="hide-menu">Sign Up</span></a></li>

				<li><a href="https://support.diamondsis.com/portal/en/home" target="_blank">Support</a></li>
			</ul>
			<ul style="padding-right: 15px; font-weight: 500;">
				<li>Call Us Today! (213) 545 2718 &nbsp;|&nbsp; sales@diamondsis.com &nbsp;|&nbsp;
			</ul>
		</nav>
		<!-- End Sidebar navigation -->
	</div>
	<!-- End Sidebar scroll-->
</aside>