<!DOCTYPE html>
<html lang="en">

<head>
	<title>Partners | <?=$title?></title>
	
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
   <? require_once("css.php"); ?>
</head>

<body class="horizontal-nav skin-default card-no-border">
    <? require_once("loader.php"); ?>
    <section id="wrapper">
		<? require_once("menu.php"); ?>
		<div class="card-group">
			<div class="card" style="min-height: 500px;">
				<div class="card-header">
                    <h1>Partners</h1>
                </div>
				<div class="card-body">
					<div class="py-4 overview-desc">
						<div class="w-80-md">
						Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
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