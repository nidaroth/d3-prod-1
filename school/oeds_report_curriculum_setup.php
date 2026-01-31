<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/oeds.php");
require_once("get_department_from_t.php");
require_once("../global/Models/S_OEDS_SETUP.php");

require_once("check_access.php");

if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}
$msg = '';	

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	
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
	<title><?=OEDS_CURRiCULUM_SETUP?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		.option_red > a > label{color:red !important}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
	   <div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles">
					<div class="col-md-12 align-self-center">
						<h4 class="text-themecolor"><?= OEDS_CURRiCULUM_SETUP?> </h4>
					</div>
				</div>
				<div class="row">
				<div class="col-12">
                        <div class="card" style="margin-bottom: 0px !important;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">  
									<?php echo flash(); ?>
                                    </div>
                                    <div class="col-md-4" style="text-align: right;">    
                                        <button type="button" onclick="window.location.href='oeds_report'" class="btn waves-effect waves-light btn-info">Go To Report</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form class="floating-labels m-t-10" method="post" name="form1" id="form1">
									<div class="row">
										<div class="col-md-12">
											<table class="table table-bordered" style="width">
												<thead>
													<tr>
														<th width="16%">Program Groups</th>
														<th>Program Hours</th>
														<th width="9%">OEDS Curriculum Code</th>
													</tr>
												</thead>
												<tbody>


													<?php

													$groups = $db->Execute("SELECT * FROM M_PROGRAM_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");

													while (!$groups->EOF) {
												
														# code...
														$CODE = $groups->fields['PROGRAM_GROUP'];

														echo 	'<tr>															
																<td  width="16%"> 
																'.$CODE.'
																</td>
																<td width="9%">
																<input id="PROGRAM_HOURS_'.$groups->fields['PK_PROGRAM_GROUP'].'" name="PROGRAM_HOURS[]" class="form-control allow_numeric" value="">
																</td>
																<td> 
																<input id="OEDS_CURRICULUM_CODE_'.$states->fields['PK_PROGRAM_GROUP'].'" name="OEDS_CURRICULUM_CODE[]" type="text" class="form-control allow_alpha_numeric" value="">
																
															 </td>
															</tr>';
														$groups->MoveNext();
													}


													?>

												</tbody>
											</table>
										</div>
									</div>


									<div class="row">
										<div class="col-md-12">
											<div class="form-group m-b-5" style="text-align:right">
												<button type="submit" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>

												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='oeds_report'"><?= CANCEL ?></button>

											</div>
										</div>
									</div>
								</form>
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
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
					
	$(".allow_numeric").on("input", function(evt) 
	{
		var self = $(this);
		self.val(self.val().replace(/\D/g, ""));
		if ((evt.which < 48 || evt.which > 57)) 
		{
			evt.preventDefault();
		}
	});


	$(".allow_alpha_numeric").on("input", function(e) 
	{
		var k = e.keyCode || e.which;
		var ok = k >= 65 && k <= 90 || // A-Z
			k >= 96 && k <= 105 || // a-z
			k >= 35 && k <= 40 || // arrows
			k == 9 || //tab
			k == 46 || //del
			k == 8 || // backspaces
			(!e.shiftKey && k >= 48 && k <= 57); // only 0-9 (ignore SHIFT options)

		if(!ok || (e.ctrlKey && e.altKey)){
			e.preventDefault();
		}
	});

        
	});
	</script>
</body>

</html>