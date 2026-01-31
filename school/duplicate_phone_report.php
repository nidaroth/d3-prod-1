<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/duplicate_phone_report.php");
require_once("check_access.php");

if(check_access('REPORT_CUSTOM_REPORT') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	if($_POST['FORMAT'] == 1)
		header("location:duplicate_phone_report_pdf?type=".$_POST['PHONE_TYPE']);
	else if($_POST['FORMAT'] == 2)
		header("location:duplicate_phone_report_excel?type=".$_POST['PHONE_TYPE']);
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
	<title><?=DUPLICATE_PHONE_PAGE_TITLE?> | <?=$title?></title>
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
						<? echo DUPLICATE_PHONE_PAGE_TITLE ?> </h4>
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
												<input type="checkbox" class="custom-control-input" id="EXCLUDE" name="EXCLUDE" value="1" onclick="excelude_phone()" >
												<label class="custom-control-label" for="EXCLUDE" ><?=EXCLUDE_PHONE?></label>
											</div>
										</div>
										<div class="col-md-2 ">
											<input class="form-control phone-inputmask" type="text" value="" name="PHONE" id="PHONE" placeholder="<?=PHONE?>" onchange="excelude_phone()" >
										</div>
										<div class="col-md-4 ">
											<div class="row form-group">
												<div class="custom-control custom-radio col-md-4">
													<input type="radio" id="PHONE_TYPE_1" name="PHONE_TYPE" checked value="1" class="custom-control-input">
													<label class="custom-control-label" for="PHONE_TYPE_1" onclick="excelude_phone()" ><?=CELL_PHONE ?></label>
												</div>
												<div class="custom-control custom-radio col-md-4">
													<input type="radio" id="PHONE_TYPE_2" name="PHONE_TYPE" value="2" class="custom-control-input">
													<label class="custom-control-label" for="PHONE_TYPE_2" onclick="excelude_phone()" ><?=HOME_PHONE ?></label>
												</div>
												<div class="custom-control custom-radio col-md-4">
													<input type="radio" id="PHONE_TYPE_3" name="PHONE_TYPE" value="3"  class="custom-control-input">
													<label class="custom-control-label" for="PHONE_TYPE_3" onclick="excelude_phone()" ><?=OTHER_PHONE ?></label>
												</div>
											</div>
										</div>
										<div class="col-md-2 ">
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									<br />
									<div id="PHONE_DIV" >
										<? $_REQUEST['PHONE_TYPE'] = 1;
										include("ajax_get_duplicate_phone.php"); ?>
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
		
		function excelude_phone(){
			jQuery(document).ready(function($) { 
				if(document.getElementById('EXCLUDE').checked == false)
					document.getElementById('PHONE').value = '';
				
				var PHONE = document.getElementById('PHONE').value;
				
				var data  = 'PHONE='+PHONE+'&PHONE_TYPE='+$("input[name='PHONE_TYPE']:checked").val();
				var value = $.ajax({
					url: "ajax_get_duplicate_phone",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PHONE_DIV').innerHTML = data;
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