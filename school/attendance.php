<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/attendance_entry.php");
require_once("../language/menu.php");
require_once("function_attendance.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
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
	<style>
		.table th, .table td {padding: 7px;}
	
		.tableFixHead          { overflow-y: auto; height: 500px; }
		.tableFixHead thead th { position: sticky; top: 0; }
		.tableFixHead thead th { background:#E8E8E8; z-index: 999;}
	</style>
	<title><?=MNU_ATTENDANCE ?> | <?=$title?></title>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_ATTENDANCE?>
						</h4>
                    </div>
                </div>				
				<div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
								<div class="row">
									<div class="col-sm-12 pt-25">
										<?=MNU_ATTENDANCE ?>
										<ul >
											<li ><a href="attendance_entry" style="float: left;" ><?=MNU_ATTENDANCE_BY_SCHEDLED_CLASS_MEETING?></a></li>
											<li ><a href="non_scheduled_attendance" style="float: left;" ><?=MNU_NON_SCHEDULED_ATTENDANCE?></a></li>
											<li  ><a href="time_clock_import" ><?=MNU_TIME_CLOCK?></a></li>
											<li  ><a href="manage_time_clock_import_review" ><?=MNU_TIME_CLOCK_REVIEW?></a></li>
										</ul>
									</div>
								</div>
                            </div>
                        </div>
                    </div>
				</div>				
            </div>
        </div>
        <? require_once("footer.php"); ?>		
    </div>
    <? require_once("js.php"); ?>
	
</body>
</html>