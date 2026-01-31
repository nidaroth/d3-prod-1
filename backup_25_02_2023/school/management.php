<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(has_management_access() == 0){
	header("location:../index");
	exit;
}
$res_add_on = $db->Execute("SELECT COE,ECM,_1098T,_90_10,IPEDS,POPULATION_REPORT, BPPE, CUSTOM_QUERIES, FISAP, TWC FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); //Ticket # 1295  Ticket # 1778
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
									<? if(check_access('MANAGEMENT_ADMISSION') == 1){ ?>
									<div class="col-md-3">
										<?=MNU_ADMISSIONS?> 
										<ul >
											<li><a href="lead_documents_not_received_with_notes" ><?=MNU_LEAD_DOC_NOT_RECEIVED_WITH_NOTES?></a></li>
											<li><a href="student_upload" ><?=MNU_LEAD_UPLOAD?></a></li>
											<li><a href="lead_task_report"><?=MNU_LEAD_TASK_REPORT?></a></li>
										</ul>
									</div>
									<? } ?>
									
									<? if(check_access('MANAGEMENT_REGISTRAR') == 1){ ?>
									<div class="col-md-3">
										<?=MNU_REGISTRAR?>
										<ul >
											<li><a href="attendance" ><?=MNU_ATTENDANCE?></a></li><!-- Ticket # 1850-->
											<!--<li><a href="upload_attendance" ><?=MNU_UPLOAD_ATTENDANCE?></a></li> Ticket # 925-->
											<li><a href="student_bulk_update?t=1" ><?=MNU_BULK_UPDATE_CAMPUS?></a></li>
											<li >
												<a href="manage_course_offering" style="float: left;" ><?=MNU_COURSE_OFFERING?></a>
											</li>
											<li  ><a href="course_offering_copy_by_term" ><?=COURSE_OFFERING_COPY_BY_TERM?></a></li><!-- Ticket # 1503-->
											<li  ><a href="final_grade_input" ><?=MNU_FINAL_GRADE?></a></li>
											<li><a href="student_bulk_login" ><?=MNU_CREATE_STUDENT_LOGIN?></a></li>
											<li><a href="manage_student_portal_user" ><?=MNU_STUDENT_PORTAL_USER?></a></li>
											<li><a href="create_student_id" ><?=MNU_STUDENT_ID?></a></li><!-- Ticket # 352-->
										</ul>
									</div>
									<? } ?>
									
									<? if(check_access('MANAGEMENT_FINANCE') == 1){ ?>
									<div class="col-md-2">
										<?=MNU_FINANCIAL_AID?>
										<ul >
											<li><a href="manage_isir"><?=MNU_ISIR?></a></li>
										</ul>
									</div>
									<? } ?>
									
									<? //Ticket # 1000
									$res_pay = $db->Execute("select ENABLE_DIAMOND_PAY from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
									if(check_access('MANAGEMENT_ACCOUNTING') == 1 || $res_pay->fields['ENABLE_DIAMOND_PAY'] == 1){ ?>
									<div class="col-md-2">
										<? if(check_access('MANAGEMENT_ACCOUNTING') == 1){ ?>
										<?=MNU_ACCOUNTING?>
										<ul >
											<? if($res_add_on->fields['_1098T'] == 1){ ?>
											<li><a href="_1098T"  ><?=MNU_1098T?></a></li>
											<li><a href="manage_1098T_ein"  ><?=MNU_1098T_EIN?></a></li>
											<li><a href="_1098T_setup"  ><?=MNU_1098T_SETUP?></a></li>
											<? } ?>
											<li><a href="earnings_calculation"  ><?=MNU_EARNINGS_CALCULATION?></a></li> <!-- Ticket # 1485 -->
											<li><a href="manage_earnings_setup"  ><?=MNU_EARNINGS_SETUP?></a></li><!-- Ticket # 1485 -->
											<li><a href="manage_misc_batch"  ><?=MNU_MISC_BATCH?></a></li>
											<li><a href="manage_batch_payment" ><?=MNU_PAYMENT_BATCH?></a></li>
											<li><a href="manage_tuition_batch" ><?=MNU_TUITION_BATCH?></a></li>
											<li><a href="unposted_batches" ><?=MNU_UNPOSTED_BATCHES?></a></li>
										</ul>
										<? } ?>
										
										<? if($res_pay->fields['ENABLE_DIAMOND_PAY'] == 1 && check_access('MANAGEMENT_DIAMOND_PAY') == 1 ){ //Ticket # 1940  ?>
										<?=MNU_DIAMOND_PAY?>
										<ul >
											<li><a href="diamond_pay_transaction"  ><?=MNU_DIAMOND_PAY_TRANSACTION?></a></li>
											<li><a href="expected_transaction" ><?=MNU_EXPECTED_TRANSACTION?></a></li>
											<li><a href="failed_transactions" ><?=MNU_FAILED_TRANSACTIONS?></a></li>
											<li><a href="recurring_payment_credit_card_details" ><?=MNU_RECURRING_PAYMENT_CREDIT_CARD_DETAILS?></a></li>
										</ul>
										<? } ?>
										
									</div>
									<? } ?>
									
									<? if(check_access('MANAGEMENT_PLACEMENT') == 1){ ?>
									<div class="col-md-2">
										<?=MNU_PLACEMENT?>
										<ul >
											<li><a href="manage_company" ><?=MNU_COMPANY?></a></li>
											<li><a href="placement_rate_report" ><?=MNU_PLACEMENT_RATE_REPORT?></a></li> <!-- Ticket # 1716 -->
										</ul>
									</div>
									<? } ?>
								</div>
								
								<hr />
								<div class="row">
									<? /* Ticket # 1911  */
									if(check_access('MANAGEMENT_BULK_UPDATE') == 1){ ?>
									<div class="col-md-3">
										<?=MNU_BULK_UPDATES?>
										<ul >
											<li><a href="bulk_text"  ><?=MNU_BULK_TEXT?></a></li>
											<li><a href="student_bulk_update?t=2"  ><?=MNU_CREATE_EVENTS?></a></li>
											<li><a href="student_bulk_update?t=3" ><?=MNU_CREATE_NOTES?></a></li>
											<li><a href="student_bulk_update?t=4" ><?=MNU_CREATE_TASKS?></a></li>
											
											<li><a href="bulk_update_notes?event=1"  ><?=MNU_UPDATE_EVENTS?></a></li>
											<li><a href="bulk_update_notes?event=0" ><?=MNU_UPDATE_NOTES?></a></li>
											<li><a href="bulk_update_task" ><?=MNU_UPDATE_TASKS?></a></li>
										</ul>
									</div>
									<? } /* Ticket # 1911  */ ?>
									
									<? /*if(check_access('MANAGEMENT_UPLOADS') == 1){ ?>
									<div class="col-md-3">
										<?=UPLOADS?>
										<ul >
										</ul>
									</div>
									<? }*/ ?>
									
									<? if(($res_add_on->fields['_90_10'] == 1 && check_access('MANAGEMENT_90_10') == 1) || ($res_add_on->fields['IPEDS'] == 1 && check_access('MANAGEMENT_IPEDS') == 1) || ($res_add_on->fields['POPULATION_REPORT'] == 1 && check_access('MANAGEMENT_POPULATION_REPORT') == 1) || ($res_add_on->fields['FISAP'] == 1 && check_access('MANAGEMENT_FISAP') == 1) ) { ?>
									<div class="col-md-4">
										<?=MNU_COMPLIANCE?>
										<ul >
											<? if($res_add_on->fields['_90_10'] == 1 && check_access('MANAGEMENT_90_10') == 1){ ?>
											<li><?=MNU_90_10_CALCULATION_DISCLOSURE?></li>
											<ul >
												<li><a href="90_10_calculation_disclosure"  ><?=MNU_90_10_CALCULATION_DISCLOSURE ?></a></li>
												<li><a href="90_10_calculation_disclosure_by_ledger"  ><?=MNU_90_10_CALCULATION_DISCLOSURE_BY_LEDGER?></a></li>
												<li><a href="90_10_calculation_disclosure_by_student"  ><?=MNU_90_10_CALCULATION_DISCLOSURE_BY_STUDENT?></a></li>
												<li><a href="90_10_calculation_disclosure_setup"  ><?=MNU_90_10_CALCULATION_DISCLOSURE_SETUP?></a></li><!-- Ticket # 1732 -->
												<li><a href="manage_90_10_setup"  ><?=MNU_90_10_REPORT_SETUP?></a></li>
											</ul>
											<? } ?>
											
											<? /* Ticket # 1778  */
											if($res_add_on->fields['FISAP'] == 1 && check_access('MANAGEMENT_FISAP') == 1){ ?>
												<li><a href="FISAP_report" ><?=MNU_FISAP_REPORT?></a></li>
											<? } 
											/* Ticket # 1778  */?>
											
											<? if($res_add_on->fields['IPEDS'] == 1 && check_access('MANAGEMENT_IPEDS') == 1){ ?>
											<li><?=MNU_IPEDS_FALL_COLLECTIONS?></li>
											<ul >
												<li><a href="ipeds_fall_collections_setup"  ><?=MNU_IPEDS_FALL_COLLECTIONS_SETUP?></a></li>
												<li><a href="ipeds_fall_12_enrollment"  ><?=MNU_IPEDS_FALL_ENROLLMENT?></a></li>
												<li><a href="ipeds_fall_completions"  ><?=MNU_IPEDS_FALL_COMPLETIONS?></a></li>
											</ul>
											<!-- <li><?=MNU_IPEDS_SPRING_COLLECTIONS?></li>
											<ul >
												<li><a href="ipeds_spring_collection_setup"  ><?=MNU_IPEDS_SPRING_COLLECTION_SETUP?></a></li>
											</ul> -->
											
											<!-- Ticket # 916 -->
											<li><?=MNU_IPEDS_WINTER_COLLECTIONS?></li>
											<ul >
												<!--li><a href="ipeds_winter_collection"  ><?=MNU_IPEDS_WINTER_COLLECTION?></a></li>--><!-- Ticket # 916 -->
												<li><a href="ipeds_winter_collection_setup"  ><?=MNU_IPEDS_WINTER_COLLECTION_SETUP?></a></li>
											</ul>
											<? } ?>
											
											<? if($res_add_on->fields['POPULATION_REPORT'] == 1 && check_access('MANAGEMENT_POPULATION_REPORT') == 1){ ?>
											<li><a href="population_report"  ><?=MNU_POPULATION_REPORT?></a></li><!-- Ticket # 1932  -->
											<!--<ul >
												<li><a href="population_report_setup"  ><?=MNU_POPULATION_REPORT_SETUP?></a></li>
												<li><a href="population_report"  ><?=MNU_POPULATION_REPORT?></a></li>
											</ul> Ticket # 1932  -->
											<? } ?>
										</ul>
									</div>
									<? } ?>
									
									<!-- Ticket # 1295 -->
									<? if($res_add_on->fields['CUSTOM_QUERIES'] == 1){ ?>
									<div class="col-md-3">
										<?=MNU_CUSTOM_QUERIES?>
										<ul >
											<? if(check_access('MANAGEMENT_CUSTOM_QUERY') == 1){ ?>
											<li><a href="manage_custom_queries"  ><?=MNU_QUERIES?></a></li>
											<? } ?>
										</ul>
									</div>
									<? } ?>
									<!-- Ticket # 1295 -->
									
								</div>
								
								<? $res_ethink = $db->Execute("SELECT ENABLE_ETHINK, ENABLE_CANVAS, ENABLE_LSQ FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
								if(($res_add_on->fields['COE'] == 1 && check_access('MANAGEMENT_ACCREDITATION') == 1) || $res_ethink->fields['ENABLE_ETHINK'] == 1 || $res_ethink->fields['ENABLE_CANVAS'] == 1 || $res_ethink->fields['ENABLE_LSQ'] == 1 || ($res_add_on->fields['ECM'] == 1 && check_access('MANAGEMENT_TITLE_IV_SERVICER') == 1 ) ) { ?>
									<hr />
									<div class="row">
										<? if(($res_add_on->fields['COE'] == 1 && check_access('MANAGEMENT_ACCREDITATION') == 1) || ($res_add_on->fields['BPPE'] == 1 && check_access('MANAGEMENT_ACCREDITATION') == 1) ) { ?>
										<div class="col-md-3">
											<?=MNU_ACCREDITATION?> 
											<ul >
												<? if($res_add_on->fields['COE'] == 1 && check_access('MANAGEMENT_ACCREDITATION') == 1 ) { ?>
												<li><?=MNU_COE?></li>
												<ul >
													<li><a href="cpl_setup" ><?=MNU_CPL_SETUP?></a></li>
													<li><a href="cpl_report" ><?=MNU_CPL_REPORT?></a></li>
													<li><a href="fte_calculation" ><?=MNU_FTE_CALCULATION?></a></li>
													<li><a href="units_report" ><?=MNU_UNITS_REPORT?></a></li>
												</ul>
												<? } ?>
												
												<? /* Ticket # 1780 */
												if($res_add_on->fields['BPPE'] == 1 && check_access('MANAGEMENT_ACCREDITATION') == 1 ) { ?>
												<li><a href="bppe_school_performance_fact_sheets" ><?=MNU_BPPE?></a></li>
												<? } /* Ticket # 1780 */ ?>
												
												<? /* Ticket # 1803 */
												if($res_add_on->fields['TWC'] == 1) { ?>
												<li><a href="TWC_report" ><?=MNU_TWC?></a></li>
												<? } /* Ticket # 1803 */ ?>
											</ul>
										</div>
										<? } ?>
										
										<? if($res_ethink->fields['ENABLE_ETHINK'] == 1 || $res_ethink->fields['ENABLE_CANVAS'] == 1 || $res_ethink->fields['ENABLE_LSQ'] == 1 ) { ?>
										<div class="col-md-3">
											<?=MNU_LMS?><!-- Ticket # 1617  -->
											<? if($res_ethink->fields['ENABLE_ETHINK'] == 1) { ?>
											<ul >
												<li><?=MNU_MOODLE?></li>
												<ul >
													<!--<li><a href="attendance_report_ethink" ><?=MNU_ATTENDANCE_REPORT ?></a></li>-->
													<li><a href="send_course_offering_ethink" ><?=MNU_SEND_COURSE_OFFERING ?></a></li>
													<li><a href="send_course_offering_instructor_ethink" ><?=MNU_SEND_COURSE_OFFERING_INSTRUCTOR ?></a></li>
													<li><a href="send_student_ethink" ><?=MNU_SEND_STUDENTS ?></a></li>
													<li><a href="send_student_course_offering_ethink" ><?=MNU_SEND_STUDENT_ENROLLMENTS ?></a></li>
													<!--<li><a href="import_attendance_ethink" ><?=MNU_IMPORT_ATTENDANCE ?></a></li>-->
													<li><a href="import_grade_ethink" ><?=MNU_IMPORT_GRADE ?></a></li>
												</ul>
											</ul>
											<? } ?>
											
											<? if($res_ethink->fields['ENABLE_CANVAS'] == 1) { ?>
											<ul >
												<li><?=MNU_CANVAS?></li>
												<ul >
													<li><a href="send_employee_canvas" ><?=MNU_SEND_INSTRUCTOR ?></a></li>
													<li><a href="send_student_canvas" ><?=MNU_SEND_STUDENTS ?></a></li>
													<li><a href="send_term_canvas" ><?=MNU_SEND_TERM ?></a></li>
													<li><a href="send_course_offering_canvas" ><?=MNU_SEND_COURSE_OFFERING ?></a></li>
													<li><a href="send_student_course_offering_canvas" ><?=MNU_SEND_STUDENT_ENROLLMENTS ?></a></li>
													<li><a href="send_employee_course_offering_canvas" ><?=MNU_SEND_COURSE_OFFERING_INSTRUCTOR ?></a></li>
													<!--<li><a href="import_attendance_canvas" ><?=MNU_IMPORT_ATTENDANCE ?></a></li>
													<li><a href="import_grade_canvas" ><?=MNU_IMPORT_GRADE ?></a></li>-->
												</ul>
											</ul>
											<? } ?>
											
											<? /* Ticket # 1870 */
											if($res_ethink->fields['ENABLE_LSQ'] == 1) { ?>
											<ul >
												<li><?=MNU_LSQ?></li>
												<ul >
													<li><a href="import_lsq_lead" ><?=MNU_IMPORT_LSQ_LEAD ?></a></li>
													<li><a href="import_lsq_student_lead" ><?=MNU_IMPORT_STUDENT_NOTES ?></a></li>
												</ul>
											</ul>
											<? } /* Ticket # 1870 */ ?>
										</div>
										<? } ?>
										
										<? if($res_add_on->fields['ECM'] == 1 && check_access('MANAGEMENT_TITLE_IV_SERVICER') == 1 ) { ?>
										<div class="col-md-4">
											<?=MNU_TITLE_IV_SERVICER?>
											<ul >
												<li><?=MNU_ECM?></li>
												<ul >
													<li><a href="manage_ecm_ledger"  ><?=MNU_ECM_SETUP?></a></li>
													<li><a href="ecm_import"  ><?=MNU_ECM_TRANSACTION_IMPORT?></a></li>
												</ul>
											</ul>
										</div>
										<? } ?>
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
