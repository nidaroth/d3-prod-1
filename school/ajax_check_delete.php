<? require_once("../global/config.php"); 
require_once("check_access.php");

if(check_access('SETUP_STUDENT') == 0 ){
	header("location:../index");
	exit;
}

if($_REQUEST['type'] == "note_type"){
	$res_check = $db->Execute("select PK_STUDENT_NOTES from S_STUDENT_NOTES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_NOTE_TYPE = '$_REQUEST[id]' ");
	if($res_check->RecordCount() == 0)
		echo "a";
	else
		echo "b";
} else if($_REQUEST['type'] == "task_status"){
	$res_check = $db->Execute("select PK_STUDENT_TASK from S_STUDENT_TASK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TASK_STATUS = '$_REQUEST[id]' ");
	if($res_check->RecordCount() == 0)
		echo "a";
	else
		echo "b";
} else if($_REQUEST['type'] == "task_type"){
	$res_check1 = $db->Execute("select PK_EVENT_TEMPLATE from S_EVENT_TEMPLATE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TASK_TYPE = '$_REQUEST[id]' ");
	$res_check2 = $db->Execute("select PK_STUDENT_OTHER_EDU from S_STUDENT_OTHER_EDU WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TASK_TYPE = '$_REQUEST[id]' ");
	$res_check3 = $db->Execute("select PK_STUDENT_TASK from S_STUDENT_TASK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TASK_TYPE = '$_REQUEST[id]' ");
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 )
		echo "a";
	else
		echo "b";
} else if($_REQUEST['type'] == "term_master"){
	$res_check1 = $db->Execute("select PK_COURSE_OFFERING from S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$_REQUEST[id]' ");
	$res_check2 = $db->Execute("select PK_STUDENT_COURSE from S_STUDENT_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$_REQUEST[id]' ");
	$res_check3 = $db->Execute("select PK_STUDENT_ENROLLMENT from S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$_REQUEST[id]' ");
	$res_check4 = $db->Execute("select PK_STUDENT_FEE_BUDGET from S_STUDENT_FEE_BUDGET WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$_REQUEST[id]' ");
	$res_check5 = $db->Execute("select PK_TUITION_BATCH_MASTER from S_TUITION_BATCH_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$_REQUEST[id]' ");
	
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 && $res_check4->RecordCount() == 0 && $res_check5->RecordCount() == 0)
		echo "a";
	else
		echo "b";
} else if($_REQUEST['type'] == "ledger_code"){
	$res_check1 = $db->Execute("select PK_CAMPUS_PROGRAM_AWARD from M_CAMPUS_PROGRAM_AWARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_REQUEST[id]' ");
	$res_check2 = $db->Execute("select PK_CAMPUS_PROGRAM_FEE from M_CAMPUS_PROGRAM_FEE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_REQUEST[id]' ");
	$res_check3 = $db->Execute("select PK_COURSE_FEE from S_COURSE_FEE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_REQUEST[id]' ");
	$res_check4 = $db->Execute("select PK_MISC_BATCH_DETAIL from S_MISC_BATCH_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_REQUEST[id]' ");
	$res_check5 = $db->Execute("select PK_PAYMENT_BATCH_MASTER from S_PAYMENT_BATCH_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE like '%$_REQUEST[id]%' ");
	$res_check6 = $db->Execute("select PK_STUDENT_APPROVED_AWARD_SUMMARY from S_STUDENT_APPROVED_AWARD_SUMMARY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_REQUEST[id]' ");
	$res_check7 = $db->Execute("select PK_STUDENT_AWARD from S_STUDENT_AWARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_REQUEST[id]' ");
	$res_check8 = $db->Execute("select PK_STUDENT_DISBURSEMENT from S_STUDENT_DISBURSEMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_REQUEST[id]' ");
	$res_check9 = $db->Execute("select PK_STUDENT_FEE_BUDGET from S_STUDENT_FEE_BUDGET WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_REQUEST[id]' ");
	$res_check10 = $db->Execute("select PK_STUDENT_LEDGER from S_STUDENT_LEDGER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_REQUEST[id]' ");
	$res_check11 = $db->Execute("select PK_TUITION_BATCH_DETAIL from S_TUITION_BATCH_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_REQUEST[id]' ");
	////////////////////////
	
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 && $res_check4->RecordCount() == 0 && $res_check5->RecordCount() == 0 && $res_check6->RecordCount() == 0 && $res_check7->RecordCount() == 0 && $res_check8->RecordCount() == 0 && $res_check9->RecordCount() == 0 && $res_check10->RecordCount() == 0 && $res_check11->RecordCount() == 0)
		echo "a";
	else
		echo "b";
} else if($_REQUEST['type'] == "student_group"){
	$res_check1 = $db->Execute("select PK_ENROLL_MANDATE_FIELDS from S_ENROLL_MANDATE_FIELDS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_GROUP = '$_REQUEST[id]' ");
	$res_check2 = $db->Execute("select PK_STUDENT_ENROLLMENT from S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_GROUP = '$_REQUEST[id]' ");
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0)
		echo "a";
	else
		echo "b";
}