<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}
if($_GET['act'] == 'del')	{
	$db->Execute("DELETE FROM M_ACADEMIC_CALENDAR WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_ACADEMIC_CALENDAR_SESSION WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_AR_FEE_TYPE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_AR_PAYMENT_TYPE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_ATB_ADMIN_CODE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_CAMPUS_PROGRAM_AWARD WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_CAMPUS_PROGRAM_CAMPUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_COURSE_OFFERING_STUDENT_STATUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_COURSE_OFFERING_STUDENT_STATUS_MASTER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_CREDIT_TRANSFER_STATUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_ENROLLMENT_STATUS_SCALE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_ENROLLMENT_STATUS_SCALE_MASTER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_EVENT_OTHER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_GRADE_BOOK_CODE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_LEAD_SOURCE_GROUP WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_PLACEMENT_COMPANY_EVENT_TYPE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_PLACEMENT_COMPANY_QUESTIONNAIRE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_PLACEMENT_COMPANY_QUESTION_GROUP WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_PLACEMENT_COMPANY_STATUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_PLACEMENT_STUDENT_EVENT_OTHER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_PLACEMENT_STUDENT_EVENT_STATUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_PLACEMENT_STUDENT_EVENT_TYPE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_PLACEMENT_STUDENT_NOTE_STATUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_PLACEMENT_STUDENT_NOTE_TYPE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_PLACEMENT_STUDENT_QUESTIONNAIRE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_PLACEMENT_STUDENT_STATUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_PLACEMENT_TYPE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_PLACEMENT_VERIFICATION_SOURCE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_SERVICER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_SESSION WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_SOC_CODE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_AGREEMENT_LIBRARY WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_AGREEMENT_LIBRARY_CAMPUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_AWARD_LETTER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_CARD_X_SETTINGS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COLLATERAL WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COMPANY WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COMPANY_CONTACT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COMPANY_EVENT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COMPANY_JOB WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COURSE_CAMPUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COURSE_COREQUISITES WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COURSE_OFFERING_WAITING_LIST WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COURSE_PREREQUISITE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_CPL_REPORT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_CPL_REPORT_RUN WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_CPL_SETUP WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_CUSTOM_FIELDS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_CUSTOM_REPORT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_CUSTOM_REPORT_DETAIL WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_EMAIL_LOG WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_EMAIL_TEMPLATE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_EMPLOYEE_NOTES_DOCUMENTS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_EVENT_TEMPLATE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_EVENT_TEMPLATE_RECIPIENTS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_GRADE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_GRADE_SCALE_DETAIL WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_GRADE_SCALE_MASTER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_INSTRUCTOR_STUDENT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_MISC_BATCH_DETAIL WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_MISC_BATCH_MASTER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_NOTIFICATION_SETTINGS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_NOTIFICATION_SETTINGS_DETAIL WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_PAYMENT_BATCH_DETAIL WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_PAYMENT_BATCH_MASTER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_PDF_FOOTER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_PDF_FOOTER_CAMPUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_PDF_TEMPLATE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_PDF_TEMPLATE_CAMPUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_PROGRAM_GRADE_BOOK WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_APPROVED_AWARD_SUMMARY WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_ATTENDANCE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_AWARD WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_BULK_LOGIN WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_CREDIT_CARD WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_CREDIT_CARD_PAYMENT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_CREDIT_TRANSFER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_DISBURSEMENT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_FEE_BUDGET WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_FINAL_GRADE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_GRADE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_JOB WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_LEDGER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_LOA WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_OTHER_EDU WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_PLACEMENT_EVENTS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_PLACEMENT_NOTES WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_PROBATION WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_PROGRAM_GRADE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_PROGRAM_GRADE_BOOK_INPUT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_SCHEDULE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_TRACK_CHANGES WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_WAIVER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_TEXT_LOG WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_TEXT_TEMPLATE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_TO_DO_LIST WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_TUITION_BATCH_DETAIL WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_TUITION_BATCH_MASTER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_USER_DEFINED_FIELDS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM Z_ANNOUNCEMENT_FOR WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM Z_EMAIL_TEMPLATE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM Z_INTERNAL_EMAIL WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM Z_INTERNAL_EMAIL_ATTACHMENT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM Z_INTERNAL_EMAIL_RECEPTION WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM Z_INTERNAL_EMAIL_STARRED WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM Z_USER_ACCESS WHERE PK_ACCOUNT = '$_GET[id]' ");

	$db->Execute("DELETE FROM M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_CAMPUS_PROGRAM_ANALYTICS_SETUP WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_CAMPUS_PROGRAM_AY WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_CAMPUS_PROGRAM_COURSE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_CAMPUS_PROGRAM_FEE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_CAMPUS_PROGRAM_REQUIREMENT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_CAMPUS_ROOM WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_DEPARTMENT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_DOCUMENT_TYPE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_DROP_REASON WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_EMPLOYEE_NOTE_TYPE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_FUNDING WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_GRADE_BOOK_TYPE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_GUARANTOR WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_LEAD_CONTACT_SOURCE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_LEAD_SOURCE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_NOTE_STATUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_NOTE_TYPE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_PROGRAM_GROUP WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_QUESTIONNAIRE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_REGION WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_TASK_STATUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_TASK_TYPE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM M_TRANSCRIPT_GROUP WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_CAMPUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COURSE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COURSE_FEE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COURSE_GRADE_BOOK WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COURSE_OFFERING_SCHEDULE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_CUSTOM_FIELDS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_EMPLOYEE_CAMPUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_EMPLOYEE_CONTACT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_EMPLOYEE_CUSTOM_FIELDS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_EMPLOYEE_DEPARTMENT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_EMPLOYEE_MASTER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_EMPLOYEE_NOTES WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_EMPLOYEE_RACE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ENROLL_MANDATE_FIELDS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_1 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_2 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_3 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_4 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_5 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_6 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_7 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_8 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_9 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_10 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_11 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_12 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_13 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_14 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_15 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_16 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_17 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_18 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_19 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_ISIR_MASTER_DETAIL_20 WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_LENDER_MASTER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_SCHOOL_CONTACT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_SCHOOL_REQUIREMENT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_ACADEMICS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_ACT_TEST WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_ATB_TEST WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_CONTACT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_CUSTOM_FIELDS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_DOCUMENTS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_DOCUMENTS_DEPARTMENT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_FINANCIAL WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_FINANCIAL_ACADEMY WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_MASTER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_NOTES WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_NOTES_DOCUMENTS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_QUESTIONNAIRE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_RACE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_REQUIREMENT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_SAT_TEST WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_STATUS_LOG WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_TASK WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_TASK_DOCUMENTS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_STUDENT_TEST WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_TERM_BLOCK WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM S_TERM_MASTER WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM Z_ANNOUNCEMENT WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM Z_ANNOUNCEMENT_CAMPUS WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM Z_ANNOUNCEMENT_EMPLOYEE WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM Z_EMAIL_ACCOUNT WHERE PK_ACCOUNT = '$_GET[id]' ");
	//$db->Execute("DELETE FROM Z_TICKET WHERE PK_ACCOUNT = '$_GET[id]' ");
	$db->Execute("DELETE FROM Z_USER WHERE PK_ACCOUNT = '$_GET[id]' ");

	header("location:manage_accounts");
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title>Accounts | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-7 align-self-center">
                        <h4 class="text-themecolor">Accounts </h4>
                    </div>
					<div class="col-md-3 align-self-center text-right">
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; Search"  style="font-family: FontAwesome" onkeypress="search(event)">
					</div>  
                    <div class="col-md-2 align-self-center text-right">
                        <div class="d-flex justify-content-end align-items-center">
                            <a href="accounts" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_accounts"
										toolbar="#tb" pagination="true" pageSize = 25 >
											<thead>
												<tr>
													<th field="SCHOOL_NAME" width="250px" align="left" sortable="true" >School Name</th>
													<th field="STUD_CODE" width="220px" align="left" sortable="true" >Student ID Default Code</th>
													
													<th field="CITY" width="150px" align="left" sortable="true" >City</th>
													<th field="STATE_CODE" width="100px" align="left" sortable="true" >State</th>
													<th field="PHONE" width="100px" align="left" sortable="true" >Phone</th>
													<th field="WEBSITE" width="300px" align="left" sortable="true" >Website</th>
													<th field="PK_ACCOUNT" width="110px" sortable="true" >PK Account</th>
													<th field="ACTION" width="150px" align="center" sortable="false" >Options</th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>

        <? require_once("footer.php"); ?>
		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1">Delete Confirmation</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							Are you sure want to Delete this Record?
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info">Yes</button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" >No</button>
					</div>
				</div>
			</div>
		</div>
    </div>
	<? require_once("js.php"); ?>
	
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script type="text/javascript" src="../backend_assets/dist/js/jquery.easyui.min.js"></script>
	<script src="../backend_assets/dist/js/jquery-ui.js"></script> 
	<script type="text/javascript">
	function doSearch(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid('load',{
				SEARCH  : $('#SEARCH').val(),
			});
		});	
	}
	function search(e){
		if (e.keyCode == 13) {
			doSearch();
		}
	}
	$(function(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid({
				onClickCell: function(rowIndex, field, value){
					$('#tt').datagrid('selectRow',rowIndex);
					if(field != 'ACTION' ){
						var selected_row = $('#tt').datagrid('getSelected');
						window.location.href='accounts?id='+selected_row.PK_ACCOUNT;
					}
				}
			});
			
			$('#tt').datagrid({
				view: $.extend(true,{},$.fn.datagrid.defaults.view,{
					onAfterRender: function(target){
						$.fn.datagrid.defaults.view.onAfterRender.call(this,target);
						$('.datagrid-header-inner').width('100%') 
						$('.datagrid-btable').width('100%') 
						$('.datagrid-body').css({'overflow-y': 'hidden'});
					}
				})
			});

		});
	});
	jQuery(document).ready(function($) {
		$(window).resize(function() {
			$('#tt').datagrid('resize');
			$('#tb').panel('resize');
		}) 
	});
	function delete_row(id){
		jQuery(document).ready(function($) {
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
		});
	}
	function conf_delete(val,id){
		if(val == 1)
			window.location.href = 'manage_accounts?act=del&id='+$("#DELETE_ID").val();
		else
			$("#deleteModal").modal("hide");
	}
	</script>

</body>

</html>