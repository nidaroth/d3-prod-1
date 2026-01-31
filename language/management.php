<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3 && $_SESSION['PK_ROLES'] != 4 && $_SESSION['PK_ROLES'] != 5)){ 
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
	<title><?=MNU_MANAGEMENT?> | <?=$title?></title>
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
							<?=MNU_MANAGEMENT?>
						</h4>
                    </div>
                </div>
				
                <div class="row">
					 <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								
								<div class="row">
									<div class="col-md-4">
										<?=MNU_BATCH_PROCESS?>
										<ul >
											<li><a href="manage_batch_payment" target="_blank" ><?=MNU_PAYMENT?></a></li></a></li>
											<li><a href="manage_misc_batch" target="_blank" ><?=MNU_MISC?></a></li></a></li>
											<li><a href="manage_tuition_batch" ><?=MNU_TUITION_BATCH?></a></li>
										</ul>
									</div>
									
									<div class="col-md-4">
										<?=MNU_ATTENDANCE?>
										<ul >
											<li><a href="attendance_entry" ><?=MNU_ATTENDANCE?></a></li>
											<li><a href="upload_attendance" ><?=MNU_UPLOAD_ATTENDANCE?></a></li>
											<li><a href="non_scheduled_attendance" ><?=MNU_NON_SCHEDULED_ATTENDANCE?></a></li>
										</ul>
									</div>
									
									<div class="col-md-4">
										<?=MNU_GRADES?>
										<ul >
											<li><a href="final_grade_input" ><?=MNU_FINAL_GRADE?></a></li>
											<li><a href="transfer_credit" ><?=MNU_TRANSFER_CREDIT?></a></li>
										</ul>
									</div>
									
									<div class="col-md-4">
										<?=MNU_MAIL_MERGE?>
										<ul >
											<li><a href="letter_generator" ><?=MNU_LETTER_GENERATOR?></a></li>
										</ul>
									</div>
									
									<div class="col-md-4">
										<?=BULK_UPDATE?>
										<ul >
											<li><a href="student_bulk_update?t=1" target="_blank" ><?=CAMPUS?></a></li></a></li>
											<li><a href="student_bluk_login" target="_blank" ><?=MNU_CREATE_STUDENT_LOGIN?></a></li></a></li>
										</ul>
									</div>
									<div class="col-md-4">
										<?=DUPLICATE_REPORT?>
										<ul >
											<li><a href="duplicate_ssn_report" target="_blank" ><?=SSN?></a></li></a></li>
											<li><a href="duplicate_email_report" target="_blank" ><?=EMAIL?></a></li></a></li>
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