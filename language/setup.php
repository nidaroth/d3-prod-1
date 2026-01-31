<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
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
	<title><?=MNU_SETUP?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_SETUP?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<? if($_SESSION['PK_ROLES'] == 2){ ?>
								<div class="row">
									<div class="col-md-2">
										<h4 class="text-themecolor"><?=MNU_SCHOOL?></h4>
										<ul style="padding-left: 0;" >
											<li><a href="manage_announcement.php"><?=MNU_ANNOUNCEMENT?></a></li></a></li>
											<? if($_SESSION['PK_ROLES'] == 3){ ?>
												<li><a href="campus.php"><?=MNU_CAMPUS_PROFILE?></a></li>
											<? } ?>
											<li><a href="manage_custom_fields.php"><?=MNU_CUSTOM_FIELDS?></a></li></a></li>
											<li><a href="manage_departments.php"><?=MNU_DEPARTMENT?></a></li>
											<li><a href="manage_note_status.php?t=1"><?=MNU_EMPLOYEE_NOTE_STATUS?></a></li></a></li>
											<li><a href="manage_employee_note_types.php"><?=MNU_EMPLOYEE_NOTE_TYPES?></a></li>
											<li><a href="manage_employee.php?t=1"><?=MNU_EMPLOYEE?></a></li>
											<li><a href="manage_region.php"><?=MNU_REGION?></a></li>
											<li><a href="manage_room.php"><?=MNU_ROOM?></a></li>
											<li><a href="school_requirements.php"><?=MNU_SCHOOL_REQUIREMENTS?></a></li></a></li>
											<? if($_SESSION['PK_ROLES'] == 2){ ?>
												<li><a href="school_profile.php"><?=MNU_SCHOOL_PROFILE?></a></li>
											<? } ?>
											<!-- <li><a href="manage_employee.php?t=2"><?=MNU_TEACHER?></a></li> -->
											<li><a href="manage_term_block.php"><?=MNU_TERM_BLOCK?></a></li></a></li>
											<li><a href="manage_term_master.php"><?=MNU_TERM_MASTER?></a></li></a></li>
											<li><a href="manage_user_defined_fields.php"><?=MNU_USER_DEFINED_FIELDS?></a></li></a></li>
										</ul>
									</div>
									
									<div class="col-md-2">
										<h4 class="text-themecolor"><?=MNU_ADMISSIONS?></h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_document_type.php"><?=MNU_DOCUMENT_TYPE?></a></li>
											<li><a href="enrollment_mandatory_fields.php"><?=MNU_ENROLLMENT_MANDATE?></a></li>
											<li><a href="manage_lead_contact_source.php"><?=MNU_LEAD_CONTACT_SOURCE?></a></li>
											<li><a href="manage_lead_source.php"><?=MNU_LEAD_SOURCE?></a></li>
											<li><a href="manage_task_status.php"><?=MNU_TASK_STATUS?></a></li>
											<li><a href="manage_task_type.php"><?=MNU_TASK_TYPE?></a></li>
										</ul>
									</div>
									
									<div class="col-md-2" style="max-width: 13%;">
										<h4 class="text-themecolor"><?=MNU_STUDENT?></h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_note_status.php?t=2"><?=MNU_STUDENT_NOTE_STATUS?></a></li></a></li>
											<li><a href="manage_note_types.php"><?=MNU_NOTE_TYPES?></a></li>
											<li><a href="manage_student_status.php"><?=MNU_STUDENT_STATUS?></a></li>
											<li><a href="manage_transcript_group.php"><?=MNU_TRANSCRIPT_GROUP?></a></li>
										</ul>
									</div>
									
									<div class="col-md-2" style="max-width: 13%;" >
										<h4 class="text-themecolor"><?=MNU_FINANCIAL_AID?></h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_funding.php"><?=MNU_FUNDING?></a></li>
											<li><a href="manage_guarantor.php"><?=MNU_GUARANTOR?></a></li>
											<li><a href="lender_master.php"><?=MNU_LENDER_MASTER?></a></li>
										</ul>
									</div>
									
									<div class="col-md-2" style="max-width: 13%;">
										<h4 class="text-themecolor"><?=MNU_REGISTRAR?></h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_course.php"><?=MNU_COURSE?></a></li>
											<li><a href="manage_drop_reason.php"><?=MNU_DROP_REASON?></a></li>
											<li><a href="manage_grade_book_type.php"><?=MNU_GRADE_BOOK_TYPE?></a></li>
											<li><a href="manage_program.php"><?=MNU_PROGRAM?></a></li>
											<li><a href="manage_program_group.php"><?=MNU_PROGRAM_GROUP?></a></li>
											<li><a href="manage_questionnaire.php"><?=MNU_QUESTIONNAIRE?></a></li>
											<li><a href="manage_student_group.php"><?=MNU_STUDENT_GROUP?></a></li></a></li>
										</ul>
									</div>
									
									<div class="col-md-2" style="max-width: 13%;">
										<h4 class="text-themecolor"><?=MNU_ACCOUNTING?></h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_ar_leder_code.php"><?=MNU_LEDGER_CODE?></a></li>
										</ul>
									</div>
									
									<div class="col-md-2" style="max-width: 13%;">
										<h4 class="text-themecolor"><?=MNU_PLACEMENT?></h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_placement_status.php"><?=MNU_PLACEMENT_STATUS?></a></li>
										</ul>
									</div>
									
								</div>
								<? } else if($_SESSION['PK_ROLES'] == 3){ ?>
								<div class="row">
									<div class="col-md-2">
										<h4 class="text-themecolor"><?=MNU_SCHOOL?></h4>
										<ul style="padding-left: 0;">
											<li><a href="manage_announcement.php"><?=MNU_ANNOUNCEMENT?></a></li></a></li>
											<li><a href="manage_program.php"><?=MNU_PROGRAM?></a></li>
											<li><a href="manage_room.php"><?=MNU_ROOM?></a></li>
										</ul>
									</div>
								</div>
								<? } ?>
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