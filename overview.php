<!DOCTYPE html>
<html lang="en">

<head>
	<title>Overview | <?=$title?></title>
	
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
	.overview-grid:nth-child(4), .overview-grid:nth-child(5), .overview-grid:nth-child(6) {
		border-bottom: none !important;
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
                    <h1>Overview</h1>
                </div>
				<div class="card-body">
					<div class="py-4 overview-desc">
						<div class="w-80-md">
							<h1>20 Years in the Making</h1>
							<p>An essential part of every school and your student's record keeping is how your people stay informed, so nothing falls through the cracks and everyone knows what to do.  Diamond SIS makes it easy to see the big picture and the nitty gritty.  Whether you are the CEO or Campus Director, a Registrar or Financial Aid Director, Admissions or Career Service manager, Diamond SIS has the tools you need.</p>
							<p>Diamond was founded over 20 years ago. If you think about the computer and software business of 20 years ago, you know it was one of very basic data collecting and then making it minimally useful to the user.  Today software like Diamond does much more.  Built on a foundation of compliance reporting, for archived transcripts and so on, now we are entering a new era, one of collaboration and the tools to effective communicate with your students and internally with your team.  Companies like Slack and Microsoft Teams are showing us the way forward for stand alone products, but work requires a much more coordinated approach and that requires software that is highly integrated and available to the senior manager, the department head and daily user.  Software has promoted Management Information to help you make good decisions.  It now supports the Department Head who also needs Management Information, but also visibility into the work that is being done.  The tasks, the ‘how to's', the flow of new students and existing, this data reflects the condition of your department.  So with the launch of our latest version, we are moving to an new era and accepting the unique challenges of the need for information at all levels of the organization, the tools for collaboration and communications in the external and internal environment.</p>
							<p><b><i>A note from Jim Queen, Diamond's owner/CEO:</i></b>  This next phase will power a better school.  The work of the worker who is ‘chained to their desk' is rapidly changing.  By enabling and embracing remote work, we allow parents to be better parents, we enable their ability to be more engaged with their children's lives without the pressure and stress of ‘having to get to the office' and importantly, we enable people to work who can't get to the office—maybe they have a special physical condition or they are on maternity leave or they have an ailing parent of child they need to care for.  If we can enable that kind of world, then we are fulfilling our mission to create a better world. In a lot of ways, we are still the Diamond D that Dianna and Barre founded, built on a foundation of record keeping and compliance reporting.  We are still very, very good at these fundamentals.  We still take your expertise in running a college and blend it with our ability to create software (and we love your feedback), but now, we are embracing a new era of collaboration, communication, in person and remote work, a more diversified workforce, and I hope a more humane one, too, that makes strong and significant contributions to an ever improving society of teaching and learning. </p>
							<h4>Keep everyone in the loop, reduce interruptions and meeting time.</h4>
							<h4>Use Diamond SIS to collaborate and keep track of all the work that needs to be done.</h4>
							<p>If your team is working remotely or together or just on another campus, Diamond SIS will help your team collaborate and stay on top of the work that needs to be done.  Learn more about our collaboration tools.</p>

						</div>
					</div>
					<div class="w-80-md px-15">
						<div class="row">
							<div class="col-12 col-md-4 text-center overview-grid">
								<a href="Diamond-SIS.php" class="d-block">
									<i class="ti-pie-chart"></i>
									<h4>Diamond SIS</h4>
									<p>Education's most comprehensive student information software.</p>
								</a>
							</div>
							<div class="col-12 col-md-4 text-center overview-grid">
								<a href="Diamond-ADM.php" class="d-block">
									<i class="ti-pie-chart"></i>
									<h4>Diamond ADM</h4>
									<p>Automated Document Management for virtual enrollment, in-person enrollment documentation and for creating a centralized document repository.</p>
								</a>
							</div>
							<div class="col-12 col-md-4 text-center overview-grid">
								<a href="Diamond-Pay.php" class="d-block">
									<i class="ti-pie-chart"></i>
									<h4>Diamond Pay</h4>
									<p>A complete system to take payments, set up recurring payments, manage past due and problem payments.</p>
								</a>
							</div>
							<div class="col-12 col-md-4 text-center overview-grid">
								<a href="Diamond-Accessories.php" class="d-block">
									<i class="ti-pie-chart"></i>
									<h4>Diamond Accessories</h4>
									<p>Student and Instructor Portals.</p>
								</a>
							</div>
							<div class="col-12 col-md-4 text-center overview-grid">
								<a href="api-3rd-party-integrations.php" class="d-block">
									<i class="ti-pie-chart"></i>
									<h4>API-3rd Party Integrations</h4>
									<p></p>
								</a>
							</div>
							<div class="col-12 col-md-4 text-center overview-grid">
								<a href="compliance-reporting.php" class="d-block">
									<i class="ti-pie-chart"></i>
									<h4>Partners</h4>
									<p></p>
								</a>
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