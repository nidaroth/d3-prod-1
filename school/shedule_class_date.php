<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/shedule_class_date.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3) ){ 
	header("location:../index.php");
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
	<link href="../backend_assets/node_modules/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet">
	<link href="../backend_assets/dist/css/pages/user-card.css" rel="stylesheet">
	<title><?=STUDENT_PAGE_TITLE?> | <?=SHEDULE_CLASS_DATE?></title>
	<style>
		#advice-validate-one-required-by-name-PK_DOCUMENT_TYPE{position: absolute;top: 24px;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-2 align-self-center">
                        <h4 class="text-themecolor"><?=SHEDULE_CLASS_DATE?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels mt-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" autocomplete="off" >
                                <div class="p-20">
                                	<div class="row">
                                		<div class="col-12 col-md-5">
                                			<div class="row input-daterange">
	                                            <div class="col-5 form-group">
	                                                <input type="text" id="START_DATE" name="START_DATE" class="form-control date">
	                                                <span class="bar"></span> 
                                                    <label for="START_DATE"><?=START_DATE?></label>
	                                            </div>
	                                            <div class="col-2 d-flex align-items-center justify-content-center form-group">to</div>
	                                            <div class="col-5 form-group">
	                                                <input type="text" id="END_DATE" name="END_DATE" class="form-control date">
	                                                <span class="bar"></span> 
                                                    <label for="END_DATE"><?=END_DATE?></label>
	                                            </div>
                                            </div>
                                            <div class="row">
	                                            <div class="col-4 form-group">
	                                                <input type="text" id="START_TIME" name="START_TIME" class="form-control timepicker">
	                                                <span class="bar"></span> 
                                                    <label for="START_TIME"><?=START_TIME?></label>
	                                            </div>
	                                            <div class="col-4 form-group">
	                                                <input type="text" id="END_TIME" name="END_TIME" class="form-control timepicker">
	                                                <span class="bar"></span> 
                                                    <label for="END_TIME"><?=END_TIME?></label>
	                                            </div>
	                                            <div class="col-4 form-group">
	                                                <input type="text" id="TOTAL_HOURS" name="TOTAL_HOURS" class="form-control" disabled>
	                                                <span class="bar"></span> 
                                                    <label for="TOTAL_HOURS"><?=HOURS?></label>
	                                            </div>
                                            </div>
                                            <div class="row">
	                                            <div class="days-row">
		                                            <div class="col-3 form-group custom-control custom-checkbox form-group">
		                                            	<input type="checkbox" class="custom-control-input" id="CHK_SUNDAY">
	                                                    <label class="custom-control-label" for="CHK_SUNDAY"><?=SUNDAY?></label>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text" class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text"class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text"class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
	                                            </div>
	                                            <div class="days-row">
		                                            <div class="col-3 form-group custom-control custom-checkbox form-group">
		                                            	<input type="checkbox" class="custom-control-input" id="CHK_MONDAY">
	                                                    <label class="custom-control-label" for="CHK_MONDAY"><?=MONDAY?></label>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text" class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text"class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text"class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
	                                            </div>
	                                            <div class="days-row">
		                                            <div class="col-3 form-group custom-control custom-checkbox form-group">
		                                            	<input type="checkbox" class="custom-control-input" id="CHK_TUESDAY">
	                                                    <label class="custom-control-label" for="CHK_TUESDAY"><?=TUESDAY?></label>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text" class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text"class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text"class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
	                                            </div>
	                                            <div class="days-row">
		                                            <div class="col-3 form-group custom-control custom-checkbox form-group">
		                                            	<input type="checkbox" class="custom-control-input" id="CHK_WEDNESDAY">
	                                                    <label class="custom-control-label" for="CHK_WEDNESDAY"><?=WEDNESDAY?></label>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text" class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text"class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text"class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
	                                            </div>
	                                            <div class="days-row">
		                                            <div class="col-3 form-group custom-control custom-checkbox form-group">
		                                            	<input type="checkbox" class="custom-control-input" id="CHK_THURSDAY">
	                                                    <label class="custom-control-label" for="CHK_THURSDAY"><?=THURSDAY?></label>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text" class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text"class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text"class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
	                                            </div>
	                                            <div class="days-row">
		                                            <div class="col-3 form-group custom-control custom-checkbox form-group">
		                                            	<input type="checkbox" class="custom-control-input" id="CHK_FRIDAY">
	                                                    <label class="custom-control-label" for="CHK_FRIDAY"><?=FRIDAY?></label>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text" class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text"class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text"class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
	                                            </div>
	                                            <div class="days-row">
		                                            <div class="col-3 form-group custom-control custom-checkbox form-group">
		                                            	<input type="checkbox" class="custom-control-input" id="CHK_SATURDAY">
	                                                    <label class="custom-control-label" for="CHK_SATURDAY"><?=SATURDAY?></label>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text" class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text"class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
		                                            <div class="col-3 form-group">
		                                                <input type="text"class="form-control">
		                                                <span class="bar"></span>
		                                            </div>
	                                            </div>
                                            </div>
                                            <div class="row">
                                            	<div class="days-row">
	                                            	<div class="col-12 form-group custom-control custom-checkbox form-group">
		                                            	<input type="checkbox" class="custom-control-input" id="CHK_SHEDULE_HOLIDAYS">
	                                                    <label class="custom-control-label" for="CHK_SHEDULE_HOLIDAYS"><?=CHK_SHEDULE_HOLIDAYS?></label>
		                                            </div>
	                                            </div>
                                            </div>
                                            <div class="row">
                                            	<div class="days-row">
	                                            	<div class="col-12 form-group custom-control custom-checkbox form-group">
		                                            	<input type="checkbox" class="custom-control-input" id="CHK_SHEDULE_OVERWRITE">
	                                                    <label class="custom-control-label" for="CHK_SHEDULE_OVERWRITE"><?=CHK_SHEDULE_OVERWRITE?></label>
		                                            </div>
	                                            </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-7">
                                        	<div class="table-responsive p-20">
												<table class="table table-hover table_shedule_class_date">
													<thead>
														<tr>
															<th><?=DATE?></th>
															<th><?=DD?></th>
															<th><?=START_TIME?></th>
															<th><?=END_TIME?></th>
															<th><?=HOURS_ROOM?></th>
															<th><?=COMPLETE?></th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td>
																<div class="form-group">
					                                                <input type="text" id="" name="" class="form-control date">
					                                                <span class="bar"></span> 
				                                                    <label for=""></label>
					                                            </div>
															</td>
															<td>
																<div class="form-group">
					                                                <input type="text" id="" name="" class="form-control">
					                                                <span class="bar"></span> 
				                                                    <label for=""></label>
					                                            </div>
															</td>
															<td>
																<div class="form-group">
					                                                <input type="text" id="" name="" class="form-control timepicker">
					                                                <span class="bar"></span> 
				                                                    <label for=""></label>
					                                            </div>
															</td>
															<td>
																<div class="form-group">
					                                                <input type="text" id="" name="" class="form-control timepicker">
					                                                <span class="bar"></span> 
				                                                    <label for=""></label>
					                                            </div>
															</td>
															<td>
																<div class="form-group">
					                                                <input type="text" id="" name="" class="form-control" disabled>
					                                                <span class="bar"></span> 
				                                                    <label for=""></label>
					                                            </div>
															</td>
															<td></td>
														</tr>
													</tbody>
												</table>
											</div>
                                        </div>
                                	</div>
                            	</div>
                            </form>                           
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
		
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript">
	<? if($_GET['tab'] != '') { ?>
		var current_tab = '<?=$_GET['tab']?>';
	<? } else { ?>
		var current_tab = 'infoTab';
	<? } ?>
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
		
		$('.timepicker').inputmask(
			"hh:mm t", {
				placeholder: "HH:MM AM/PM", 
				insertMode: false, 
				showMaskOnHover: false,
				hourFormat: 12
			}
		);
		
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			current_tab = $(e.target).attr("href") // activated tab
			//alert(current_tab)
		});
	});
	</script>
</body>

</html>