<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course-offering.php");

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
	<title><?=STUDENT_PAGE_TITLE?> | <?=COURSE_OFFERING?></title>
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
                        <h4 class="text-themecolor"><?=COURSE_OFFERING?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<ul class="nav nav-tabs customtab" role="tablist">
								<li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#settingsTab" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down">Settings</span></a> </li>
								
								<li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#scheduleTab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">Schedule</span></a> </li>
								
                               
                            </ul>
                            <!-- Tab panes -->
                           <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" autocomplete="off" >
                                <div class="tab-content">
                                    <div class="tab-pane active" id="settingsTab" role="tabpanel">
                                        <div class="p-20">
                                        	<div class="row">
	                                            <div class="col-sm-6 pt-25">
		                                            <div class="col-12 col-sm-12 form-group">
		                                                <select id="PK_COURSE" name="PK_COURSE" class="form-control">
		                                                    <option value=""></option>
		                                                    <option value="1">1</option>
		                                                    <option value="2">2</option>
		                                                </select>

		                                                <span class="bar"></span> 
		                                                    <label for="PK_COURSE">Course</label>
		                                            </div>
		                                            <div class="col-12 col-sm-12 form-group">
		                                                <select id="PK_TERM" name="PK_TERM" class="form-control">
		                                                    <option value=""></option>
		                                                    <option value="1">1</option>
		                                                    <option value="2">2</option>
		                                                </select>

		                                                <span class="bar"></span> 
		                                                    <label for="PK_TERM">Term</label>
		                                            </div>
		                                            <div class="col-12 col-sm-12 form-group">
		                                                <select id="PK_COURSE_STATUS" name="PK_COURSE_STATUS" class="form-control">
		                                                    <option value=""></option>
		                                                    <option value="1">1</option>
		                                                    <option value="2">2</option>
		                                                </select>

		                                                <span class="bar"></span> 
		                                                    <label for="PK_COURSE_STATUS">Course Status</label>
		                                            </div>
		                                            <div class="col-12 col-sm-12 form-group">
		                                                <select id="PK_INSTRUCTOR" name="PK_INSTRUCTOR" class="form-control">
		                                                    <option value=""></option>
		                                                    <option value="1">1</option>
		                                                    <option value="2">2</option>
		                                                </select>
		                                                <span class="bar"></span> 
		                                                    <label for="PK_INSTRUCTOR">Instructor</label>
		                                            </div>
		                                            <div class="col-12 col-sm-12 form-group">
		                                                <select id="PK_ASSISTANT" name="PK_ASSISTANT" class="form-control">
		                                                    <option value=""></option>
		                                                    <option value="1">1</option>
		                                                    <option value="2">2</option>
		                                                </select>
		                                                <span class="bar"></span> 
		                                                    <label for="PK_ASSISTANT">Assistant</label>
		                                            </div>
		                                            <div class="col-12 col-sm-12 form-group">
		                                                <select id="PK_CAMPUS" name="PK_CAMPUS" class="form-control">
		                                                    <option value=""></option>
		                                                    <option value="1">1</option>
		                                                    <option value="2">2</option>
		                                                </select>
		                                                <span class="bar"></span> 
		                                                    <label for="PK_CAMPUS">Campus</label>
		                                            </div>
		                                            <div class="col-12 col-sm-12 form-group">
		                                                <select id="PK_ROOM" name="PK_ROOM" class="form-control">
		                                                    <option value=""></option>
		                                                    <option value="1">1</option>
		                                                    <option value="2">2</option>
		                                                </select>
		                                                <span class="bar"></span> 
		                                                    <label for="PK_ROOM">Room</label>
		                                            </div>                                  
		                                            <div class="col-12 col-sm-12 form-group">
		                                                <input id="CLASS_SIZE" name="CLASS_SIZE" type="text" class="form-control" value="">
		                                                <span class="bar"></span> 
		                                                <label for="CLASS_SIZE">Class Size</label>
		                                            </div>
		                                            <div class="col-12 col-sm-12 form-group">
		                                                <select id="PK_SESSION" name="PK_SESSION" class="form-control">
		                                                    <option selected></option>
		                                                </select>
		                                                <span class="bar"></span> 
		                                                <label for="PK_SESSION">Session</label>
		                                            </div>
		                                            <div class="col-12 col-sm-12 form-group">
		                                                <input id="SESSION_NO" name="SESSION_NO" type="text" class="form-control" value="">
		                                                <span class="bar"></span> 
		                                                <label for="SESSION_NO">Session No</label>
		                                            </div>
		                                            <div class="col-12 col-sm-12 form-group">
		                                                <select id="PK_ATTENDENCE_TYPE" name="PK_ATTENDENCE_TYPE" class="form-control" >
		                                                    <option value="" ></option>
		                                                </select>
		                                                <span class="bar"></span> 
		                                                <label for="PK_ATTENDENCE_TYPE">Attendence Type</label>
		                                            </div>
		                                            <div class="col-12 col-sm-12 form-group">
		                                                <select id="PK_DEFAULT_ATTENDENCE_CODE" name="PK_DEFAULT_ATTENDENCE_CODE" class="form-control">
		                                                    <option value=""></option>
		                                                    <option value="1">1</option>
		                                                    <option value="2">2</option>
		                                                </select>
		                                                <span class="bar"></span> 
		                                                    <label for="PK_DEFAULT_ATTENDENCE_CODE">Default Attendence Code</label>
		                                            </div>                                                       
		                                             <div class="d-flex">
		                                                <div class="col-12 col-sm-12 ">
		                                                    <button type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_CONTINUE?></button>
		                                                    <button type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_EXIT?></button>
		                                                    <button type="button" class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
		                                                </div>
		                                            </div>
	                                            </div>
                                            </div>
                                        </div>									
                                    </div>
                                    <div class="tab-pane" id="scheduleTab" role="tabpanel">
                                    	<div class="p-20">
                                        	<div class="row">
	                                            <div class="col-12">
                                            		<div class="row">
														<div class="col-md-12 align-self-center text-right">
															<div class="d-flex justify-content-end align-items-center">
																<a href="shedule_class_date.php" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</a>&nbsp;&nbsp;
															</div>
														</div>
													</div>
	                                            	<div class="table-responsive p-20">
														<table class="table table-hover">
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
															</tbody>
														</table>
													</div>
	                                            </div>
                                        	</div>
                                    	</div>
                                    </div>
                                    <input type="hidden" name="SAVE_CONTINUE" id="SAVE_CONTINUE" value="0" />
                                    <input type="hidden" id="current_tab" name="current_tab" value="0" >
                                    <input type="hidden" name="FORM_NAME" value="form1" >
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