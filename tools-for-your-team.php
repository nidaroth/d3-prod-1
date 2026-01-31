<!DOCTYPE html>
<html lang="en">

<head>
	<title>Tools for your Team | <?=$title?></title>
	
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
   <? require_once("css.php"); ?>
   <style>
   .overview-grid {
		border-left: 1px solid #dee3e5;
		border-bottom: 1px solid #dee3e5;
		padding: 35px 55px;
	}
	.overview-grid:nth-child(7), .overview-grid:nth-child(8), .overview-grid:nth-child(9) {
		border-bottom: 1px solid transparent;
	}
	.overview-grid:nth-child(1), .overview-grid:nth-child(4), .overview-grid:nth-child(7) {
		border-left: 1px solid transparent;
	}
	.overview-grid h4 {
		margin: 10px 0;
		font-weight: 600;
	}
	.overview-grid a{
		color: #212529;
	}
	.overview-grid i {
		font-size: 75px;
	}
	.overview-grid p {
		color: #a1a2a3;
	}
	.overview-desc h4 {
		line-height: 2;
    	font-size: 1.5rem;
	}
	@media (min-width: 768px) {
		.w-80-md {
			width: 80%;
			margin: auto;
		}
	}
   </style>
</head>

<body class="horizontal-nav skin-default card-no-border">
    <? require_once("loader.php"); ?>
    <section id="wrapper">
		<? require_once("menu.php"); ?>
		<div class="card-group">
			<div class="card" style="min-height: 500px;">
				<div class="card-header">
                    <h1>Tools for your Team</h1>
                </div>
				<div class="card-body">
					<div class="py-4 overview-desc">
						<div class="w-80-md">
                            <h2 class="text-center">Admissions</h2>
							<p>SRM not CRM</p>
							<p>You are a college, not a sales organization, so you need a Student Relationship Manager, not a Customer Relationship Manager.  What's the difference?</p>
							<p>You qualify your students in unique ways and you vernacular or language is different.  You have Admissions Reps, not sales people and you are encouraging a relationship unique to a College and Student.  This is a nurturing relationship.</p>
                            <div class="mt-30 row">
                                <div class="col-12 col-md-6">
                                    <h3>For senior managers</h3>
                                    <p>Check-in questions like "what are you working on this week?" and "what did you work on today?" gives you a quick, easy way to chat about the broad brush strokes as well as the details.  Perfect for keeping you finger on the pulse of your organization.</p>
                                </div>
                                <div class="col-12 col-md-6">
                                    <img src="assets/images/senior-managers-group-chat.png" alt="">
                                </div>
                            </div>
                            <div class="mt-30 row">
                                <div class="col-12 col-md-6">
                                    <img src="assets/images/for-department-heads.png" alt="">
                                </div>
                                <div class="col-12 col-md-6">
                                    <h3>For Department Heads</h3>
                                    <p>Diamond's activity and task views gives you one place to see all the tasks that are overdue, all the work' that's due today and in the next few days.  Metrics keep you up to date with rolling forecasts and anticipated workloads.</p>
                                </div>
                            </div>
                            <div class="mt-30 text-cener">
                                <h2>The only tool you need to run your college.</h2>
                                <p>Diamond Cloud Workspace is all-in-one student information software.  It’s a comprehensive solution for for all your needs.  Admissions, Business Office and Career Services departments.</p>
                            </div>
						</div>
					</div>
				</div>
			</div>
		</div>
    </section>
	<? require_once("footer.php"); ?>
	<? require_once("js.php"); ?>
    <script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#to-recover').on("click", function() {
				$("#loginform").slideUp();
				$("#recoverform").fadeIn();
			});
		});
    </script>
    
	<script src="backend_assets/dist/js/validation_prototype.js"></script>
	<script src="backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('loginform');
	</script>
</body>

</html>