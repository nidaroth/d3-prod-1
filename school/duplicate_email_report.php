<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/duplicate_email_report.php");
require_once("check_access.php");

if(check_access('REPORT_CUSTOM_REPORT') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	if($_POST['FORMAT'] == 1)
		header("location:duplicate_email_report_pdf");
	else if($_POST['FORMAT'] == 2)
		header("location:duplicate_email_report_excel");
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
	<title><?=DUPLICATE_EMAIL_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor">
						<? echo DUPLICATE_EMAIL_PAGE_TITLE ?> </h4>
                    </div>
                </div>
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row" >
										<div class="col-md-2 ">
											<div class="custom-control custom-checkbox mr-sm-2">
												<input type="checkbox" class="custom-control-input" id="EXCLUDE" name="EXCLUDE" value="1" onclick="excelude_email()" >
												<label class="custom-control-label" for="EXCLUDE" ><?=EXCLUDE_EMAIL?></label>
											</div>
										</div>
										<div class="col-md-2 ">
											<input class="form-control" type="text" value="" name="EMAIL" id="EMAIL" placeholder="<?=EMAIL?>" onchange="excelude_email()" >
										</div>
										<div class="col-md-2 ">
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									<br />
									<div id="EMAIL_DIV" >
										<? include("ajax_get_duplicate_email.php"); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		function excelude_email(){
			jQuery(document).ready(function($) { 
				if(document.getElementById('EXCLUDE').checked == false)
					document.getElementById('EMAIL').value = '';
				
				var EMAIL = document.getElementById('EMAIL').value;
				
				var data  = 'EMAIL='+EMAIL;
				var value = $.ajax({
					url: "ajax_get_duplicate_email",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('EMAIL_DIV').innerHTML = data;
					}		
				}).responseText;
			});
		}
	</script>
	
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	function submit_form(val){
		jQuery(document).ready(function($) {
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true){ 
				document.getElementById('FORMAT').value = val
				document.form1.submit();
			}
		});
	}
	/* Ticket #1432 */
	jQuery(document).ready(function($) { 
		$(window).keydown(function(event){
			if(event.keyCode == 13) {
				event.preventDefault();
				return false;
			}
		});
	});	
	/* Ticket #1432 */
	</script>

</body>

</html>