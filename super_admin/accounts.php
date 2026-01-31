<? require_once("../global/config.php"); 
require_once("../global/image_fun.php");
require_once("../language/school_profile.php"); 
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}
if($_GET['act'] == 'logo')	{
	$res = $db->Execute("SELECT LOGO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_GET[id]' ");
	unlink($res->fields['LOGO']);
	$db->Execute("UPDATE Z_ACCOUNT SET LOGO = '' WHERE PK_ACCOUNT = '$_GET[id]' ");
		
	header("location:accounts?id=".$_GET['id']);
} else if($_GET['act'] == 'camp_del'){
	$db->Execute("DELETE FROM S_CAMPUS WHERE PK_CAMPUS = '$_GET[iid]' ");
	
	header("location:accounts?id=".$_GET['id'].'&tab=campusTab');
} else if($_GET['act'] == 'user_del'){
	$db->Execute("DELETE FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_GET[iid]' ");
	$db->Execute("DELETE FROM S_EMPLOYEE_CONTACT WHERE PK_EMPLOYEE_MASTER = '$_GET[iid]' ");
	$db->Execute("DELETE FROM Z_USER WHERE ID = '$_GET[iid]' AND PK_USER_TYPE = 2 ");
	$db->Execute("DELETE FROM S_EMPLOYEE_CAMPUS WHERE PK_EMPLOYEE_MASTER = '$_GET[iid]' "); 
	
	header("location:accounts?id=".$_GET['id'].'&tab=usersTab');
} else if($_GET['act'] == 'user_cont'){
	$db->Execute("DELETE FROM S_SCHOOL_CONTACT WHERE PK_SCHOOL_CONTACT = '$_GET[iid]' ");
	
	header("location:accounts?id=".$_GET['id'].'&tab=contactTab');
} else if($_GET['act'] == 'from_no'){
	$db->Execute("DELETE FROM S_TEXT_SETTINGS WHERE PK_TEXT_SETTINGS = '$_GET[iid]' ");
	
	header("location:accounts?id=".$_GET['id'].'&tab=communications_tab');
} else if($_GET['act'] == 'custom_query'){ /* Ticket # 1295 */
	/*$db->Execute("DELETE FROM M_CUSTOM_QUERY WHERE PK_CUSTOM_QUERY = '$_GET[iid]' ");
	$db->Execute("DELETE FROM M_CUSTOM_QUERY_ACCOUNT WHERE PK_CUSTOM_QUERY = '$_GET[iid]' ");
	
	header("location:accounts?id=".$_GET['id'].'&tab=customQueriesTab');*/
} else if($_GET['act'] == 'custom_query_account'){
	$db->Execute("DELETE FROM M_CUSTOM_QUERY_ACCOUNT WHERE PK_CUSTOM_QUERY_ACCOUNT = '$_GET[iid]' ");
	
	header("location:accounts?id=".$_GET['id'].'&tab=customQueriesTab');
} else if($_GET['act'] == 'cq'){
	$CUSTOM_QUERY_ACCOUNT['PK_CUSTOM_QUERY'] 	= $_GET['iid'];
	$CUSTOM_QUERY_ACCOUNT['PK_ACCOUNT'] 		= $_GET['id'];
	db_perform('M_CUSTOM_QUERY_ACCOUNT', $CUSTOM_QUERY_ACCOUNT, 'insert');
	
	header("location:accounts?id=".$_GET['id'].'&tab=customQueriesTab');
}
/* Ticket # 1295 */

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$SAVE_CONTINUE = $_POST['SAVE_CONTINUE'];
	$current_tab   = $_POST['current_tab'];
	unset($_POST['SAVE_CONTINUE']);
	unset($_POST['current_tab']);
	unset($_POST['CUSTOM_QUERY']); //Ticket # 1304
	
	$ABHES 	= $_POST['ABHES'];
	$ACCET 	= $_POST['ACCET'];
	$ACCSC 	= $_POST['ACCSC'];
	$ACICS  = $_POST['ACICS'];
	$BPPE 	= $_POST['BPPE'];
	$CIE 	= $_POST['CIE'];
	$COE 	= $_POST['COE'];
	$DEAC 	= $_POST['DEAC'];
	$NACCAS = $_POST['NACCAS'];
	$OEDS 	= $_POST['OEDS'];
	$TWC 	= $_POST['TWC'];
	$ECM 	= $_POST['ECM'];
	$GUESTVISION 	= $_POST['GUESTVISION']; 
	$CUSTOM_QUERIES 	= $_POST['CUSTOM_QUERIES']; //Ticket # 1295
	$_1098T 			= $_POST['_1098T'];
	$_4807G 			= $_POST['_4807G'];
	$_90_10 			= $_POST['_90_10'];
	$IPEDS 				= $_POST['IPEDS'];
	$POPULATION_REPORT 	= $_POST['POPULATION_REPORT'];
	
	$SID_ARR 				= $_POST['SID'];
	$TOKEN_ARR 				= $_POST['TOKEN'];
	$FROM_NO_ARR 			= $_POST['FROM_NO'];
	$PK_TEXT_SETTINGS_ARR 	= $_POST['PK_TEXT_SETTINGS'];
	
	$ETHINK_TOKEN 				= $_POST['ETHINK_TOKEN'];
	$ETHINK_URL 				= $_POST['ETHINK_URL'];
	$DEFAULT_LMS_CATEGORY_CODE 	= $_POST['DEFAULT_LMS_CATEGORY_CODE']; //Ticket # 1473
	
	$CANVAS_ACCOUNT_ID 	= $_POST['CANVAS_ACCOUNT_ID'];
	$CANVAS_TOKEN 		= $_POST['CANVAS_TOKEN'];
	$CANVAS_URL 		= $_POST['CANVAS_URL'];
	
	$FISAP 	= $_POST['FISAP']; //Ticket # 1778
	unset($_POST['FISAP']); //Ticket # 1778
	
	unset($_POST['CUSTOM_QUERIES']); //Ticket # 1295
	unset($_POST['ABHES']);
	unset($_POST['ACCET']);
	unset($_POST['ACCSC']);
	unset($_POST['BPPE']);
	unset($_POST['CIE']);
	unset($_POST['COE']);
	unset($_POST['DEAC']);
	unset($_POST['NACCAS']);
	unset($_POST['OEDS']);
	unset($_POST['TWC']);
	unset($_POST['ECM']);
	unset($_POST['ETHINK_TOKEN']);
	unset($_POST['ETHINK_URL']);
	unset($_POST['DEFAULT_LMS_CATEGORY_CODE']); //Ticket # 1473
	unset($_POST['CANVAS_ACCOUNT_ID']);
	unset($_POST['CANVAS_TOKEN']);
	unset($_POST['CANVAS_URL']);
	
	unset($_POST['SID']);
	unset($_POST['TOKEN']);
	unset($_POST['FROM_NO']);
	unset($_POST['PK_TEXT_SETTINGS']);
	
	unset($_POST['_1098T']);
	unset($_POST['_4807G']);
	unset($_POST['_90_10']);
	unset($_POST['IPEDS']);
	unset($_POST['POPULATION_REPORT']);
	
	/* Ticket # 1870 */
	$ENABLE_LSQ 		= $_POST['ENABLE_LSQ'];
	$LSQ_ACCESS_KEY 	= $_POST['LSQ_ACCESS_KEY'];
	$LSQ_SECRET_KEY 	= $_POST['LSQ_SECRET_KEY'];
	$LSQ_USER_NAME 		= $_POST['LSQ_USER_NAME'];
	$LSQ_PASSWORD 		= $_POST['LSQ_PASSWORD'];
	$LSQ_BASE_URL 		= $_POST['LSQ_BASE_URL'];
	
	unset($_POST['ENABLE_LSQ']);
	unset($_POST['LSQ_ACCESS_KEY']);
	unset($_POST['LSQ_SECRET_KEY']);
	unset($_POST['LSQ_USER_NAME']);
	unset($_POST['LSQ_PASSWORD']);
	unset($_POST['LSQ_BASE_URL']);
	/* Ticket # 1870 */

	//DIAM-742
	unset($_POST['BY_USER']);
	unset($_POST['START_DATE']);
	unset($_POST['END_DATE']);
	unset($_POST['FORMAT']);
	unset($_POST['ACICS']);
	unset($_POST['GUESTVISION']);

	
	//DIAM-742
	
	$ACCOUNT = $_POST;
	$ACCOUNT['ENABLE_LSQ'] 				= $ENABLE_LSQ; //Ticket # 1870
	$ACCOUNT['AUTO_GENERATE_STUD_ID'] = 1;
	$ACCOUNT['HAS_STUDENT_PORTAL'] 		= $_POST['HAS_STUDENT_PORTAL'];
	$ACCOUNT['HAS_INSTRUCTOR_PORTAL'] 	= $_POST['HAS_INSTRUCTOR_PORTAL'];
	$ACCOUNT['ENABLE_DIAMOND_PAY'] 		= $_POST['ENABLE_DIAMOND_PAY'];
	$ACCOUNT['ENABLE_UNPOST_BATCH']		= $_POST['ENABLE_UNPOST_BATCH']; // DIAM-987

	$ACCOUNT['ENABLE_ETHINK'] 			= $_POST['ENABLE_ETHINK'];
	$ACCOUNT['ENABLE_CANVAS'] 			= $_POST['ENABLE_CANVAS'];
	//echo "<pre>";print_r($ACCOUNT);exit;
	
	/* Ticket # 1304 */
	if($_GET['id'] == '')
		$HAS_STUDENT_PORTAL_flag = 1;
	else {
		$result = $db->Execute("SELECT HAS_STUDENT_PORTAL FROM Z_ACCOUNT where PK_ACCOUNT = '$_GET[id]'");
		if($result->fields['HAS_STUDENT_PORTAL'] == 1)
			$HAS_STUDENT_PORTAL_flag = 0;
		else
			$HAS_STUDENT_PORTAL_flag = 1;
	}
	
	if($_GET['id'] == '')
		$HAS_INSTRUCTOR_PORTAL_flag = 1;
	else {
		$result = $db->Execute("SELECT HAS_INSTRUCTOR_PORTAL FROM Z_ACCOUNT where PK_ACCOUNT = '$_GET[id]'");
		if($result->fields['HAS_INSTRUCTOR_PORTAL'] == 1)
			$HAS_INSTRUCTOR_PORTAL_flag = 0;
		else
			$HAS_INSTRUCTOR_PORTAL_flag = 1;
	}
	/* Ticket # 1304 */
	
	if($_GET['id'] == ''){
	
		$API_KEY = '';
		do {
			$API_KEY = generateRandomString(40);
			$result = $db->Execute("SELECT PK_ACCOUNT FROM Z_ACCOUNT where API_KEY = '$API_KEY'");
		} while ($result->RecordCount() > 0);
	
		/* Ticket # 1304 */
		$ACCOUNT['CHECK_SSN']						= 1;
		$ACCOUNT['STUDENT_MFA']						= 1;
		$ACCOUNT['EMPLOYEE_MFA']					= 1;
		$ACCOUNT['EMP_DEFAULT_PASSWORD'] 			= 'Employees123!';
		$ACCOUNT['GRADE_DISPLAY_TYPE']				= 3;
		$ACCOUNT['STUDENT_ID_BARCODE_TYPE'] 		= 1;
		$ACCOUNT['STUDENT_ID_PROGRAM_DISPLAY_TYPE']	= 1;
		$ACCOUNT['_1098T_TAX_FORM']					= 1;
		$ACCOUNT['_480G_TAX_FORM']					= 1;
		/* Ticket # 1304 */
		
		$ACCOUNT['API_KEY']   	= $API_KEY;
		$ACCOUNT['PK_PANELS']   = 2;
		$ACCOUNT['PK_TIMEZONE'] = 4;
		$ACCOUNT['CREATED_BY']  = $_SESSION['ADMIN_PK_USER'];
		$ACCOUNT['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('Z_ACCOUNT', $ACCOUNT, 'insert');
		$PK_ACCOUNT = $db->insert_ID();
		
		$s3ClientWrapper = new s3ClientWrapper();
		
		// mkdir('../backend_assets/school/school_'.$PK_ACCOUNT,0777, true);
		// chmod('../backend_assets/school/school_'.$PK_ACCOUNT, 0777);
		$s3ClientWrapper->createFolder('backend_assets/school/school_'.$PK_ACCOUNT.'/');
		
		// mkdir('../backend_assets/school/school_'.$PK_ACCOUNT.'/student',0777, true);
		// chmod('../backend_assets/school/school_'.$PK_ACCOUNT.'/student', 0777);
		$s3ClientWrapper->createFolder('backend_assets/school/school_'.$PK_ACCOUNT.'/student/');
		
		// mkdir('../backend_assets/school/school_'.$PK_ACCOUNT.'/employee',0777, true);
		// chmod('../backend_assets/school/school_'.$PK_ACCOUNT.'/employee', 0777);
		$s3ClientWrapper->createFolder('backend_assets/school/school_'.$PK_ACCOUNT.'/employee/');
		
		// mkdir('../backend_assets/school/school_'.$PK_ACCOUNT.'/other',0777, true);
		// chmod('../backend_assets/school/school_'.$PK_ACCOUNT.'/other', 0777);
		$s3ClientWrapper->createFolder('backend_assets/school/school_'.$PK_ACCOUNT.'/other/');
		
		// mkdir('../backend_assets/school/school_'.$PK_ACCOUNT.'/collateral',0777, true);
		// chmod('../backend_assets/school/school_'.$PK_ACCOUNT.'/collateral', 0777);
		$s3ClientWrapper->createFolder('backend_assets/school/school_'.$PK_ACCOUNT.'/collateral/');
		
		// mkdir('../backend_assets/school/school_'.$PK_ACCOUNT.'/email_attachments',0777, true);
		// chmod('../backend_assets/school/school_'.$PK_ACCOUNT.'/email_attachments', 0777);
		$s3ClientWrapper->createFolder('backend_assets/school/school_'.$PK_ACCOUNT.'/email_attachments/');
		
		/* Ticket # 1304 */
		$ACCOUNT_REPORTS['_1098T'] 				= 1;
		$ACCOUNT_REPORTS['_4807G'] 				= 1;
		$ACCOUNT_REPORTS['_90_10'] 				= 1;
		$ACCOUNT_REPORTS['IPEDS'] 				= 1;
		$ACCOUNT_REPORTS['POPULATION_REPORT'] 	= 1;
		/* Ticket # 1304 */
		
		$ACCOUNT_REPORTS['PK_ACCOUNT'] 			= $PK_ACCOUNT;
		$ACCOUNT_REPORTS['CREATED_BY']  		= $_SESSION['ADMIN_PK_USER'];
		$ACCOUNT_REPORTS['CREATED_ON']  		= date("Y-m-d H:i");
		db_perform('Z_ACCOUNT_REPORTS', $ACCOUNT_REPORTS, 'insert');

		$res_m = $db->Execute("select * from M_DOCUMENT_TYPE_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$DOCUMENT_TYPE['PK_DOCUMENT_TYPE_MASTER'] 	= $res_m->fields['PK_DOCUMENT_TYPE_MASTER'];
			$DOCUMENT_TYPE['DOCUMENT_TYPE'] 			= $res_m->fields['DOCUMENT_TYPE'];
			$DOCUMENT_TYPE['CODE'] 						= $res_m->fields['CODE'];
			$DOCUMENT_TYPE['ACTIVE'] 					= 1;
			$DOCUMENT_TYPE['PK_ACCOUNT'] 				= $PK_ACCOUNT;
			$DOCUMENT_TYPE['CREATED_ON'] 				= date("Y-m-d H:i:s");
			$DOCUMENT_TYPE['CREATED_BY'] 				= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_DOCUMENT_TYPE', $DOCUMENT_TYPE, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_DEPARTMENT_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$DEPARTMENT_MASTER['PK_DEPARTMENT_MASTER'] 		= $res_m->fields['PK_DEPARTMENT_MASTER'];
			$DEPARTMENT_MASTER['DEPARTMENT'] 				= $res_m->fields['DEPARTMENT'];
			$DEPARTMENT_MASTER['DESCRIPTION'] 				= $res_m->fields['DESCRIPTION'];
			$DEPARTMENT_MASTER['ACTIVE'] 					= 1;
			$DEPARTMENT_MASTER['PK_ACCOUNT'] 				= $PK_ACCOUNT;
			$DEPARTMENT_MASTER['CREATED_ON'] 				= date("Y-m-d H:i:s");
			$DEPARTMENT_MASTER['CREATED_BY'] 				= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_DEPARTMENT', $DEPARTMENT_MASTER, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_CREDIT_TRANSFER_STATUS_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$CREDIT_TRANSFER_STATUS_MASTER['PK_CREDIT_TRANSFER_STATUS_MASTER'] 	= $res_m->fields['PK_CREDIT_TRANSFER_STATUS_MASTER'];
			$CREDIT_TRANSFER_STATUS_MASTER['CREDIT_TRANSFER_STATUS'] 			= $res_m->fields['CREDIT_TRANSFER_STATUS'];
			$CREDIT_TRANSFER_STATUS_MASTER['DESCRIPTION'] 						= $res_m->fields['DESCRIPTION'];
			$CREDIT_TRANSFER_STATUS_MASTER['SHOW_ON_TRANSCRIPT'] 				= $res_m->fields['SHOW_ON_TRANSCRIPT'];
			$CREDIT_TRANSFER_STATUS_MASTER['ACTIVE'] 							= 1;
			$CREDIT_TRANSFER_STATUS_MASTER['PK_ACCOUNT'] 						= $PK_ACCOUNT;
			$CREDIT_TRANSFER_STATUS_MASTER['CREATED_ON'] 						= date("Y-m-d H:i:s");
			$CREDIT_TRANSFER_STATUS_MASTER['CREATED_BY'] 						= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_CREDIT_TRANSFER_STATUS', $CREDIT_TRANSFER_STATUS_MASTER, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_ECM_LEDGER_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$ECM_LEDGER_MASTER['PK_ECM_LEDGER_MASTER'] 		= $res_m->fields['PK_ECM_LEDGER_MASTER'];
			$ECM_LEDGER_MASTER['DESCRIPTION'] 				= $res_m->fields['DESCRIPTION'];
			$ECM_LEDGER_MASTER['ACTIVE'] 					= 1;
			$ECM_LEDGER_MASTER['PK_ACCOUNT'] 				= $PK_ACCOUNT;
			$ECM_LEDGER_MASTER['CREATED_ON'] 				= date("Y-m-d H:i:s");
			$ECM_LEDGER_MASTER['CREATED_BY'] 				= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_ECM_LEDGER', $ECM_LEDGER_MASTER, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_DROP_REASON_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$DROP_REASON['PK_DROP_REASON_MASTER'] 	= $res_m->fields['PK_DROP_REASON_MASTER'];
			$DROP_REASON['DROP_REASON'] 			= $res_m->fields['DROP_REASON'];
			$DROP_REASON['DESCRIPTION'] 			= $res_m->fields['DESCRIPTION'];
			$DROP_REASON['ACTIVE'] 					= 1;
			$DROP_REASON['PK_ACCOUNT'] 				= $PK_ACCOUNT;
			$DROP_REASON['CREATED_ON'] 				= date("Y-m-d H:i:s");
			$DROP_REASON['CREATED_BY'] 				= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_DROP_REASON', $DROP_REASON, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_SESSION_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$SESSION['PK_SESSION_MASTER'] 		= $res_m->fields['PK_SESSION_MASTER'];
			$SESSION['SESSION'] 				= $res_m->fields['SESSION'];
			$SESSION['SESSION_ABBREVIATION'] 	= $res_m->fields['SESSION_ABBREVIATION'];
			$SESSION['DISPLAY_ORDER'] 			= $res_m->fields['DISPLAY_ORDER'];
			$SESSION['COLOR'] 					= $res_m->fields['COLOR'];
			$SESSION['ACTIVE'] 					= 1;
			$SESSION['PK_ACCOUNT'] 				= $PK_ACCOUNT;
			$SESSION['CREATED_ON'] 				= date("Y-m-d H:i:s");
			$SESSION['CREATED_BY'] 				= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_SESSION', $SESSION, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_GUARANTOR_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$GUARANTOR['PK_GUARANTOR_MASTER'] 	= $res_m->fields['PK_GUARANTOR_MASTER'];
			$GUARANTOR['GUARANTOR'] 			= $res_m->fields['GUARANTOR'];
			$GUARANTOR['DESCRIPTION'] 			= $res_m->fields['DESCRIPTION'];
			$GUARANTOR['ACTIVE'] 				= 1;
			$GUARANTOR['PK_ACCOUNT'] 			= $PK_ACCOUNT;
			$GUARANTOR['CREATED_ON'] 			= date("Y-m-d H:i:s");
			$GUARANTOR['CREATED_BY'] 			= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_GUARANTOR', $GUARANTOR, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_GRADE_BOOK_TYPE_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$GRADE_BOOK_TYPE['PK_GRADE_BOOK_TYPE_MASTER'] 	= $res_m->fields['PK_GRADE_BOOK_TYPE_MASTER'];
			$GRADE_BOOK_TYPE['GRADE_BOOK_TYPE'] 	= $res_m->fields['GRADE_BOOK_TYPE'];
			$GRADE_BOOK_TYPE['DESCRIPTION'] 		= $res_m->fields['DESCRIPTION'];
			$GRADE_BOOK_TYPE['ACTIVE'] 				= 1;
			$GRADE_BOOK_TYPE['PK_ACCOUNT'] 			= $PK_ACCOUNT;
			$GRADE_BOOK_TYPE['CREATED_ON'] 			= date("Y-m-d H:i:s");
			$GRADE_BOOK_TYPE['CREATED_BY'] 			= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_GRADE_BOOK_TYPE', $GRADE_BOOK_TYPE, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_FUNDING_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$FUNDING['PK_FUNDING_MASTER'] 	= $res_m->fields['PK_FUNDING_MASTER'];
			$FUNDING['FUNDING'] 			= $res_m->fields['FUNDING'];
			$FUNDING['DESCRIPTION'] 		= $res_m->fields['DESCRIPTION'];
			$FUNDING['ACTIVE'] 				= 1;
			$FUNDING['PK_ACCOUNT'] 			= $PK_ACCOUNT;
			$FUNDING['CREATED_ON'] 			= date("Y-m-d H:i:s");
			$FUNDING['CREATED_BY'] 			= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_FUNDING', $FUNDING, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_PLACEMENT_STATUS_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$PLACEMENT_STATUS['PK_PLACEMENT_STATUS_MASTER'] 			= $res_m->fields['PK_PLACEMENT_STATUS_MASTER'];
			$PLACEMENT_STATUS['PLACEMENT_STATUS'] 						= $res_m->fields['PLACEMENT_STATUS'];
			$PLACEMENT_STATUS['EMPLOYED'] 								= $res_m->fields['EMPLOYED'];
			$PLACEMENT_STATUS['PK_PLACEMENT_STUDENT_STATUS_CATEGORY'] 	= $res_m->fields['PK_PLACEMENT_STUDENT_STATUS_CATEGORY'];
			$PLACEMENT_STATUS['ACTIVE'] 								= 1;
			$PLACEMENT_STATUS['PK_ACCOUNT'] 							= $PK_ACCOUNT;
			$PLACEMENT_STATUS['CREATED_ON'] 							= date("Y-m-d H:i:s");
			$PLACEMENT_STATUS['CREATED_BY'] 							= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_PLACEMENT_STATUS', $PLACEMENT_STATUS, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_STUDENT_STATUS_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$PK_DEPARTMENT_MASTER = $res_m->fields['PK_DEPARTMENT_MASTER'];
			$res_d = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE PK_DEPARTMENT_MASTER = '$PK_DEPARTMENT_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
			
			$STUDENT_STATUS['PK_STUDENT_STATUS_MASTER'] = $res_m->fields['PK_STUDENT_STATUS_MASTER'];
			$STUDENT_STATUS['STUDENT_STATUS'] 			= $res_m->fields['STUDENT_STATUS'];
			$STUDENT_STATUS['DESCRIPTION'] 				= $res_m->fields['DESCRIPTION'];
			$STUDENT_STATUS['ADMISSIONS'] 				= $res_m->fields['ADMISSIONS'];
			$STUDENT_STATUS['PK_END_DATE'] 				= $res_m->fields['PK_END_DATE'];
			$STUDENT_STATUS['FA_STATUS'] 				= $res_m->fields['FA_STATUS'];
			$STUDENT_STATUS['POST_TUITION'] 			= $res_m->fields['POST_TUITION'];
			$STUDENT_STATUS['DOC_28_1'] 				= $res_m->fields['DOC_28_1'];
			$STUDENT_STATUS['CLASS_ENROLLMENT'] 		= $res_m->fields['CLASS_ENROLLMENT'];
			$STUDENT_STATUS['ALLOW_ATTENDANCE'] 		= $res_m->fields['ALLOW_ATTENDANCE'];
			$STUDENT_STATUS['_1098T'] 					= $res_m->fields['_1098T'];
			$STUDENT_STATUS['_4807G'] 					= $res_m->fields['_4807G'];
			$STUDENT_STATUS['COMPLETED'] 				= $res_m->fields['COMPLETED'];
			$STUDENT_STATUS['ACTIVE'] 					= 1;
			$STUDENT_STATUS['PK_ACCOUNT'] 				= $PK_ACCOUNT;
			$STUDENT_STATUS['CREATED_ON'] 				= date("Y-m-d H:i:s");
			$STUDENT_STATUS['CREATED_BY'] 				= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_STUDENT_STATUS', $STUDENT_STATUS, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_NOTE_TYPE_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$PK_DEPARTMENT_MASTER = $res_m->fields['PK_DEPARTMENT_MASTER'];
			if($PK_DEPARTMENT_MASTER == -1)
				$NOTE_TYPE['PK_DEPARTMENT'] = -1;
			else { 
				$res_d = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE PK_DEPARTMENT_MASTER = '$PK_DEPARTMENT_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
				$NOTE_TYPE['PK_DEPARTMENT'] = $res_d->fields['PK_DEPARTMENT'];
			}
			
			$NOTE_TYPE['PK_NOTE_TYPE_MASTER'] 	= $res_m->fields['PK_NOTE_TYPE_MASTER'];
			$NOTE_TYPE['NOTE_TYPE'] 			= $res_m->fields['NOTE_TYPE'];
			$NOTE_TYPE['DESCRIPTION'] 			= $res_m->fields['DESCRIPTION'];
			$NOTE_TYPE['TYPE'] 					= $res_m->fields['TYPE'];
			$NOTE_TYPE['ACTIVE'] 				= 1;
			$NOTE_TYPE['PK_ACCOUNT'] 			= $PK_ACCOUNT;
			$NOTE_TYPE['CREATED_ON'] 			= date("Y-m-d H:i:s");
			$NOTE_TYPE['CREATED_BY'] 			= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_NOTE_TYPE', $NOTE_TYPE, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_EMPLOYEE_NOTE_TYPE_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$EMPLOYEE_NOTE_TYPE_MASTER['PK_EMPLOYEE_NOTE_TYPE_MASTER'] 	= $res_m->fields['PK_EMPLOYEE_NOTE_TYPE_MASTER'];
			$EMPLOYEE_NOTE_TYPE_MASTER['EMPLOYEE_NOTE_TYPE'] 			= $res_m->fields['EMPLOYEE_NOTE_TYPE'];
			$EMPLOYEE_NOTE_TYPE_MASTER['DESCRIPTION'] 					= $res_m->fields['DESCRIPTION'];
			$EMPLOYEE_NOTE_TYPE_MASTER['ACTIVE'] 						= 1;
			$EMPLOYEE_NOTE_TYPE_MASTER['PK_ACCOUNT'] 					= $PK_ACCOUNT;
			$EMPLOYEE_NOTE_TYPE_MASTER['CREATED_ON'] 					= date("Y-m-d H:i:s");
			$EMPLOYEE_NOTE_TYPE_MASTER['CREATED_BY'] 					= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_EMPLOYEE_NOTE_TYPE', $EMPLOYEE_NOTE_TYPE_MASTER, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_TASK_TYPE_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$TASK_TYPE_MASTER['PK_TASK_TYPE_MASTER'] 	= $res_m->fields['PK_TASK_TYPE_MASTER'];
			$TASK_TYPE_MASTER['TASK_TYPE'] 				= $res_m->fields['TASK_TYPE'];
			$TASK_TYPE_MASTER['DESCRIPTION'] 			= $res_m->fields['DESCRIPTION'];
			$TASK_TYPE_MASTER['PK_DEPARTMENT'] 			= -1;
			$TASK_TYPE_MASTER['ACTIVE'] 				= 1;
			$TASK_TYPE_MASTER['PK_ACCOUNT'] 			= $PK_ACCOUNT;
			$TASK_TYPE_MASTER['CREATED_ON'] 			= date("Y-m-d H:i:s");
			$TASK_TYPE_MASTER['CREATED_BY'] 			= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_TASK_TYPE', $TASK_TYPE_MASTER, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_TASK_STATUS_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$TASK_STATUS_MASTER['PK_TASK_STATUS_MASTER'] 	= $res_m->fields['PK_TASK_STATUS_MASTER'];
			$TASK_STATUS_MASTER['TASK_STATUS'] 				= $res_m->fields['TASK_STATUS'];
			$TASK_STATUS_MASTER['DESCRIPTION'] 				= $res_m->fields['DESCRIPTION'];
			$TASK_STATUS_MASTER['ACTIVE'] 					= 1;
			$TASK_STATUS_MASTER['PK_DEPARTMENT'] 			= -1;
			$TASK_STATUS_MASTER['PK_ACCOUNT'] 				= $PK_ACCOUNT;
			$TASK_STATUS_MASTER['CREATED_ON'] 				= date("Y-m-d H:i:s");
			$TASK_STATUS_MASTER['CREATED_BY'] 				= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_TASK_STATUS', $TASK_STATUS_MASTER, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_LEAD_SOURCE_GROUP_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$LEAD_SOURCE_GROUP['PK_LEAD_SOURCE_GROUP_MASTER'] 	= $res_m->fields['PK_LEAD_SOURCE_GROUP_MASTER'];
			$LEAD_SOURCE_GROUP['LEAD_SOURCE_GROUP'] 			= $res_m->fields['LEAD_SOURCE_GROUP'];
			$LEAD_SOURCE_GROUP['DESCRIPTION'] 					= $res_m->fields['DESCRIPTION'];
			$LEAD_SOURCE_GROUP['ACTIVE'] 						= 1;
			$LEAD_SOURCE_GROUP['PK_ACCOUNT'] 					= $PK_ACCOUNT;
			$LEAD_SOURCE_GROUP['CREATED_ON'] 					= date("Y-m-d H:i:s");
			$LEAD_SOURCE_GROUP['CREATED_BY'] 					= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_LEAD_SOURCE_GROUP', $LEAD_SOURCE_GROUP, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_LEAD_SOURCE_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
		
			$PK_LEAD_SOURCE_GROUP_MASTER = $res_m->fields['PK_LEAD_SOURCE_GROUP_MASTER'];
			$res_gm = $db->Execute("select PK_LEAD_SOURCE_GROUP from M_LEAD_SOURCE_GROUP WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_LEAD_SOURCE_GROUP_MASTER = '$PK_LEAD_SOURCE_GROUP_MASTER' ");
			
			$LEAD_SOURCE_MASTER['PK_LEAD_SOURCE_MASTER'] 	= $res_m->fields['PK_LEAD_SOURCE_MASTER'];
			$LEAD_SOURCE_MASTER['PK_LEAD_SOURCE_GROUP'] 	= $res_gm->fields['PK_LEAD_SOURCE_GROUP'];
			$LEAD_SOURCE_MASTER['DESCRIPTION'] 				= $res_m->fields['DESCRIPTION'];
			$LEAD_SOURCE_MASTER['LEAD_SOURCE'] 				= $res_m->fields['LEAD_SOURCE'];
			$LEAD_SOURCE_MASTER['ACTIVE'] 					= 1;
			$LEAD_SOURCE_MASTER['PK_ACCOUNT'] 				= $PK_ACCOUNT;
			$LEAD_SOURCE_MASTER['CREATED_ON'] 				= date("Y-m-d H:i:s");
			$LEAD_SOURCE_MASTER['CREATED_BY'] 				= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_LEAD_SOURCE', $LEAD_SOURCE_MASTER, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_LEAD_CONTACT_SOURCE_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$LEAD_CONTACT_SOURCE_MASTER['PK_LEAD_CONTACT_SOURCE_MASTER'] 	= $res_m->fields['PK_LEAD_CONTACT_SOURCE_MASTER'];
			$LEAD_CONTACT_SOURCE_MASTER['LEAD_CONTACT_SOURCE'] 				= $res_m->fields['LEAD_CONTACT_SOURCE'];
			$LEAD_CONTACT_SOURCE_MASTER['DESCRIPTION'] 				= $res_m->fields['DESCRIPTION'];
			$LEAD_CONTACT_SOURCE_MASTER['ACTIVE'] 					= 1;
			$LEAD_CONTACT_SOURCE_MASTER['PK_ACCOUNT'] 				= $PK_ACCOUNT;
			$LEAD_CONTACT_SOURCE_MASTER['CREATED_ON'] 				= date("Y-m-d H:i:s");
			$LEAD_CONTACT_SOURCE_MASTER['CREATED_BY'] 				= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_LEAD_CONTACT_SOURCE', $LEAD_CONTACT_SOURCE_MASTER, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_STUDENT_CONTACT_TYPE_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$STUDENT_CONTACT_TYPE['PK_STUDENT_CONTACT_TYPE_MASTER'] = $res_m->fields['PK_STUDENT_CONTACT_TYPE_MASTER'];
			$STUDENT_CONTACT_TYPE['STUDENT_CONTACT_TYPE'] 			= $res_m->fields['STUDENT_CONTACT_TYPE'];
			$STUDENT_CONTACT_TYPE['ACTIVE'] 						= 1;
			$STUDENT_CONTACT_TYPE['PK_ACCOUNT'] 					= $PK_ACCOUNT;
			$STUDENT_CONTACT_TYPE['CREATED_ON'] 					= date("Y-m-d H:i:s");
			$STUDENT_CONTACT_TYPE['CREATED_BY'] 					= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_STUDENT_CONTACT_TYPE', $STUDENT_CONTACT_TYPE, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_PLACEMENT_COMPANY_STATUS_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$PLACEMENT_COMPANY_STATUS['PK_PLACEMENT_COMPANY_STATUS_MASTER'] = $res_m->fields['PK_PLACEMENT_COMPANY_STATUS_MASTER'];
			$PLACEMENT_COMPANY_STATUS['PLACEMENT_COMPANY_STATUS'] 			= $res_m->fields['PLACEMENT_COMPANY_STATUS'];
			$PLACEMENT_COMPANY_STATUS['DESCRIPTION'] 						= $res_m->fields['DESCRIPTION'];
			$PLACEMENT_COMPANY_STATUS['ACTIVE'] 							= 1;
			$PLACEMENT_COMPANY_STATUS['PK_ACCOUNT'] 						= $PK_ACCOUNT;
			$PLACEMENT_COMPANY_STATUS['CREATED_ON'] 						= date("Y-m-d H:i:s");
			$PLACEMENT_COMPANY_STATUS['CREATED_BY'] 						= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_PLACEMENT_COMPANY_STATUS', $PLACEMENT_COMPANY_STATUS, 'insert');
			
			$res_m->MoveNext();
		}
			
		$res_m = $db->Execute("select * from M_NOTE_STATUS_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$PK_DEPARTMENT_MASTER = $res_m->fields['PK_DEPARTMENT_MASTER'];
			if($PK_DEPARTMENT_MASTER == -1)
				$NOTE_TYPE['PK_DEPARTMENT'] = -1;
			else { 
				$res_d = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE PK_DEPARTMENT_MASTER = '$PK_DEPARTMENT_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
				$NOTE_STATUS['PK_DEPARTMENT'] = $res_d->fields['PK_DEPARTMENT'];
			}
			
			$NOTE_STATUS['PK_NOTE_STATUS_MASTER'] 	= $res_m->fields['PK_NOTE_STATUS_MASTER'];
			$NOTE_STATUS['NOTE_STATUS'] 			= $res_m->fields['NOTE_STATUS'];
			$NOTE_STATUS['TYPE'] 					= $res_m->fields['TYPE'];
			$NOTE_STATUS['ACTIVE'] 					= 1;
			$NOTE_STATUS['PK_ACCOUNT'] 				= $PK_ACCOUNT;
			$NOTE_STATUS['CREATED_ON'] 				= date("Y-m-d H:i:s");
			$NOTE_STATUS['CREATED_BY'] 				= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_NOTE_STATUS', $NOTE_STATUS, 'insert');
			
			$res_m->MoveNext();
		}
		
		$res_m = $db->Execute("select * from M_COURSE_OFFERING_STUDENT_STATUS_MASTER WHERE ACTIVE = '1' ");
		while (!$res_m->EOF) {
			$COURSE_OFFERING_STUDENT_STATUS['PK_COURSE_OFFERING_STUDENT_STATUS_MASTER'] = $res_m->fields['PK_COURSE_OFFERING_STUDENT_STATUS_MASTER'];
			$COURSE_OFFERING_STUDENT_STATUS['COURSE_OFFERING_STUDENT_STATUS'] 			= $res_m->fields['COURSE_OFFERING_STUDENT_STATUS'];
			$COURSE_OFFERING_STUDENT_STATUS['DESCRIPTION'] 								= $res_m->fields['DESCRIPTION'];
			$COURSE_OFFERING_STUDENT_STATUS['POST_TUITION'] 							= $res_m->fields['POST_TUITION'];
			$COURSE_OFFERING_STUDENT_STATUS['SHOW_ON_TRANSCRIPT'] 						= $res_m->fields['SHOW_ON_TRANSCRIPT'];
			$COURSE_OFFERING_STUDENT_STATUS['SHOW_ON_REPORT_CARD'] 						= $res_m->fields['SHOW_ON_REPORT_CARD'];
			$COURSE_OFFERING_STUDENT_STATUS['CALCULATE_SAP'] 							= $res_m->fields['CALCULATE_SAP'];
			$COURSE_OFFERING_STUDENT_STATUS['MAKE_AS_DEFAULT'] 							= $res_m->fields['MAKE_AS_DEFAULT'];
			$COURSE_OFFERING_STUDENT_STATUS['ACTIVE'] 									= 1;
			$COURSE_OFFERING_STUDENT_STATUS['PK_ACCOUNT'] 								= $PK_ACCOUNT;
			$COURSE_OFFERING_STUDENT_STATUS['CREATED_ON'] 								= date("Y-m-d H:i:s");
			$COURSE_OFFERING_STUDENT_STATUS['CREATED_BY'] 								= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_COURSE_OFFERING_STUDENT_STATUS', $COURSE_OFFERING_STUDENT_STATUS, 'insert');
			
			$res_m->MoveNext();
		}

		$res_m = $db->Execute("select PK_TASK_TYPE from M_TASK_TYPE WHERE ACTIVE = '1' AND PK_ACCOUNT = '$PK_ACCOUNT' AND PK_TASK_TYPE_MASTER = 16");
		$EVENT_TEMPLATE['PK_EVENT_TYPE'] = 1;
		$EVENT_TEMPLATE['CONTENT'] 		 = '{Student Name}';
		$EVENT_TEMPLATE['CREATE_TASK']   = 1;
		$EVENT_TEMPLATE['PK_TASK_TYPE']  = $res_m->fields['PK_TASK_TYPE'];
		$EVENT_TEMPLATE['PK_ACCOUNT']    = $PK_ACCOUNT;
		$EVENT_TEMPLATE['CREATED_BY']    = $_SESSION['ADMIN_PK_USER'];
		$EVENT_TEMPLATE['CREATED_ON']    = date("Y-m-d H:i");
		db_perform('S_EVENT_TEMPLATE', $EVENT_TEMPLATE, 'insert');
		
		$res_m = $db->Execute("select PK_TASK_TYPE from M_TASK_TYPE WHERE ACTIVE = '1' AND PK_ACCOUNT = '$PK_ACCOUNT' AND PK_TASK_TYPE_MASTER = 9");
		$EVENT_TEMPLATE['PK_EVENT_TYPE'] = 2;
		$EVENT_TEMPLATE['CONTENT'] 		 = '{Student Name}';
		$EVENT_TEMPLATE['CREATE_TASK']   = 1;
		$EVENT_TEMPLATE['PK_TASK_TYPE']  = $res_m->fields['PK_TASK_TYPE'];
		$EVENT_TEMPLATE['PK_ACCOUNT']    = $PK_ACCOUNT;
		$EVENT_TEMPLATE['CREATED_BY']    = $_SESSION['ADMIN_PK_USER'];
		$EVENT_TEMPLATE['CREATED_ON']    = date("Y-m-d H:i");
		db_perform('S_EVENT_TEMPLATE', $EVENT_TEMPLATE, 'insert');
		
		$res_m = $db->Execute("select PK_TASK_TYPE from M_TASK_TYPE WHERE ACTIVE = '1' AND PK_ACCOUNT = '$PK_ACCOUNT' AND PK_TASK_TYPE_MASTER = 20");
		$EVENT_TEMPLATE['PK_EVENT_TYPE'] = 4;
		$EVENT_TEMPLATE['CONTENT'] 		 = '{Student Name}';
		$EVENT_TEMPLATE['CREATE_TASK']   = 1;
		$EVENT_TEMPLATE['PK_TASK_TYPE']  = $res_m->fields['PK_TASK_TYPE'];
		$EVENT_TEMPLATE['PK_ACCOUNT']    = $PK_ACCOUNT;
		$EVENT_TEMPLATE['CREATED_BY']    = $_SESSION['ADMIN_PK_USER'];
		$EVENT_TEMPLATE['CREATED_ON']    = date("Y-m-d H:i");
		db_perform('S_EVENT_TEMPLATE', $EVENT_TEMPLATE, 'insert');
		
		$res_m = $db->Execute("select PK_TASK_TYPE from M_TASK_TYPE WHERE ACTIVE = '1' AND PK_ACCOUNT = '$PK_ACCOUNT' AND PK_TASK_TYPE_MASTER = 13");
		$EVENT_TEMPLATE['PK_EVENT_TYPE'] = 4;
		$EVENT_TEMPLATE['CONTENT'] 		 = '{Student Name} - {Task Type} - (Task Status)';
		$EVENT_TEMPLATE['CREATE_TASK']   = 1;
		$EVENT_TEMPLATE['PK_TASK_TYPE']  = $res_m->fields['PK_TASK_TYPE'];
		$EVENT_TEMPLATE['PK_ACCOUNT']    = $PK_ACCOUNT;
		$EVENT_TEMPLATE['CREATED_BY']    = $_SESSION['ADMIN_PK_USER'];
		$EVENT_TEMPLATE['CREATED_ON']    = date("Y-m-d H:i");
		db_perform('S_EVENT_TEMPLATE', $EVENT_TEMPLATE, 'insert');
		
		/* Ticket # 1304 */
		$ENROLL_MANDATE_FIELDS['FIRST_NAME'] 			= 1;
		$ENROLL_MANDATE_FIELDS['LAST_NAME'] 			= 1;
		$ENROLL_MANDATE_FIELDS['PK_TERM_BLOCK']			= 1;
		$ENROLL_MANDATE_FIELDS['PK_CAMPUS_PROGRAM'] 	= 1;
		$ENROLL_MANDATE_FIELDS['PK_STUDENT_STATUS'] 	= 1;
		$ENROLL_MANDATE_FIELDS['PK_SESSION'] 			= 1;
		$ENROLL_MANDATE_FIELDS['EXPECTED_GRAD_DATE'] 	= 1;
		$ENROLL_MANDATE_FIELDS['PK_CAMPUS'] 			= 1;
		$ENROLL_MANDATE_FIELDS['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
		$ENROLL_MANDATE_FIELDS['CREATED_BY'] 			= $_SESSION['PK_USER'];
		$ENROLL_MANDATE_FIELDS['CREATED_ON'] 			= date("Y-m-d H:i");
		db_perform('S_ENROLL_MANDATE_FIELDS', $ENROLL_MANDATE_FIELDS, 'insert');
		/* Ticket # 1304 */
		
	} else {
		// DVB 04 12 2025
		$ACCOUNT['CAMPUSIQ_TOKEN'] 	  = $_POST['CAMPUSIQ_TOKEN']??'';;
		$ACCOUNT['CAMPUSIQ_ENABLE'] 	  = $_POST['CAMPUSIQ_ENABLE']??'';;
		// múltiples dashboards
		if (!empty($_POST['CAMPUSIQ_DASHBOARDPK']) && is_array($_POST['CAMPUSIQ_DASHBOARDPK'])) {
		    // Guardamos como "id1,id2,id3"
		    $ACCOUNT['CAMPUSIQ_DASHBOARDPK'] = implode(',', $_POST['CAMPUSIQ_DASHBOARDPK']);
		} else {
		    $ACCOUNT['CAMPUSIQ_DASHBOARDPK'] = '';
		}
		// DVB END		

		$PK_ACCOUNT = $_GET['id'];
		$ACCOUNT['ACTIVE'] 	  = $_POST['ACTIVE'];
		$ACCOUNT['EDITED_BY'] = $_SESSION['ADMIN_PK_USER'];
		$ACCOUNT['EDITED_ON'] = date("Y-m-d H:i");
		unset($ACCOUNT['STUD_NO']);
		unset($_POST['ACICS']);

		db_perform('Z_ACCOUNT', $ACCOUNT, 'update'," PK_ACCOUNT = '$PK_ACCOUNT'");

		$ACCOUNT_REPORTS['ABHES'] 			= $ABHES;
		$ACCOUNT_REPORTS['ACCET'] 			= $ACCET;
		$ACCOUNT_REPORTS['ACCSC'] 			= $ACCSC;
		$ACCOUNT_REPORTS['ACICS'] 			= $ACICS;
		$ACCOUNT_REPORTS['BPPE'] 			= $BPPE;
		$ACCOUNT_REPORTS['CIE'] 			= $CIE;
		$ACCOUNT_REPORTS['COE'] 			= $COE;
		$ACCOUNT_REPORTS['DEAC'] 			= $DEAC;
		$ACCOUNT_REPORTS['NACCAS'] 			= $NACCAS;
		$ACCOUNT_REPORTS['OEDS'] 			= $OEDS;
		$ACCOUNT_REPORTS['TWC'] 			= $TWC;
		$ACCOUNT_REPORTS['ECM'] 			= $ECM;
		$ACCOUNT_REPORTS['GUESTVISION'] 	= $GUESTVISION;
		
		$ACCOUNT_REPORTS['CUSTOM_QUERIES'] 	= $CUSTOM_QUERIES; //Ticket # 1295
		
		$ACCOUNT_REPORTS['_1098T'] 				= $_1098T;
		$ACCOUNT_REPORTS['_4807G'] 				= $_4807G;
		$ACCOUNT_REPORTS['_90_10'] 				= $_90_10;
		$ACCOUNT_REPORTS['IPEDS'] 				= $IPEDS;
		$ACCOUNT_REPORTS['FISAP'] 				= $FISAP; //Ticket # 1778
		$ACCOUNT_REPORTS['POPULATION_REPORT'] 	= $POPULATION_REPORT;

		$ACCOUNT_REPORTS['PK_ACCOUNT'] 	= $PK_ACCOUNT;
		$ACCOUNT_REPORTS['EDITED_BY']   = $_SESSION['ADMIN_PK_USER'];
		$ACCOUNT_REPORTS['EDITED_ON']   = date("Y-m-d H:i");
		db_perform('Z_ACCOUNT_REPORTS', $ACCOUNT_REPORTS, 'update'," PK_ACCOUNT = '$PK_ACCOUNT'");
	}
	
	/* Ticket # 1304 */
	if($_POST['HAS_STUDENT_PORTAL'] == 1 && $HAS_STUDENT_PORTAL_flag == 1) {
		$STUDENT_PORTAL_SETTINGS['ACADEMIC_REVIEW'] 				= 1;
		$STUDENT_PORTAL_SETTINGS['ACADEMIC_REVIEW_BY_TERM'] 		= 1;
		$STUDENT_PORTAL_SETTINGS['COSMETOLOGY_GRADE_BOOK_LABS'] 	= 1;
		$STUDENT_PORTAL_SETTINGS['COSMETOLOGY_GRADE_BOOK_SUMMARY'] 	= 1;
		$STUDENT_PORTAL_SETTINGS['COSMETOLOGY_GRADE_BOOK_TEST'] 	= 1;
		$STUDENT_PORTAL_SETTINGS['GRADE_BOOK'] 						= 1;
		$STUDENT_PORTAL_SETTINGS['PROGRAM_COURSE_PROGRESS'] 		= 1;
		$STUDENT_PORTAL_SETTINGS['ATTENDANCE_REVIEW'] 				= 1;
		$STUDENT_PORTAL_SETTINGS['ATTENDANCE_SUMMARY'] 				= 1;
		$STUDENT_PORTAL_SETTINGS['FINANCIAL_AID_AWARDS'] 			= 1;
		$STUDENT_PORTAL_SETTINGS['PAYMENT_SCHEDULE'] 				= 1;
		$STUDENT_PORTAL_SETTINGS['STUDENT_LEDGER'] 					= 1;
		$STUDENT_PORTAL_SETTINGS['SCHEDULE'] 						= 1;
		$res = $db->Execute("SELECT PK_ACCOUNT_STUDENT_PORTAL_SETTINGS FROM Z_ACCOUNT_STUDENT_PORTAL_SETTINGS WHERE PK_ACCOUNT = '$_GET[id]' ");
		if($res->RecordCount() == 0) {
			$STUDENT_PORTAL_SETTINGS['PK_ACCOUNT'] 	= $_GET['id'];
			$STUDENT_PORTAL_SETTINGS['CREATED_BY'] 	= $_SESSION['PK_USER'];
			$STUDENT_PORTAL_SETTINGS['CREATED_ON'] 	= date("Y-m-d H:i");
			db_perform('Z_ACCOUNT_STUDENT_PORTAL_SETTINGS', $STUDENT_PORTAL_SETTINGS, 'insert');
		} else {
			$PK_ACCOUNT_STUDENT_PORTAL_SETTINGS = $res->fields['PK_ACCOUNT_STUDENT_PORTAL_SETTINGS'];
			$STUDENT_PORTAL_SETTINGS['EDITED_BY'] = $_SESSION['PK_USER'];
			$STUDENT_PORTAL_SETTINGS['EDITED_ON'] = date("Y-m-d H:i");
			db_perform('Z_ACCOUNT_STUDENT_PORTAL_SETTINGS', $STUDENT_PORTAL_SETTINGS, 'update'," PK_ACCOUNT_STUDENT_PORTAL_SETTINGS = '$PK_ACCOUNT_STUDENT_PORTAL_SETTINGS' ");
		}
	}
	
	if($_POST['HAS_INSTRUCTOR_PORTAL'] == 1 && $HAS_INSTRUCTOR_PORTAL_flag == 1) {
		$INSTRUCTOR_PORTAL_SETTINGS['ATTENDANCE_ENTRY'] 			= 1;
		$INSTRUCTOR_PORTAL_SETTINGS['ATTENDANCE_REVIEW'] 			= 1;
		$INSTRUCTOR_PORTAL_SETTINGS['DAILY_ROSTER'] 				= 1;
		$INSTRUCTOR_PORTAL_SETTINGS['FINAL_GRADE'] 					= 1;
		$INSTRUCTOR_PORTAL_SETTINGS['GRADE_BOOK_ENTRY'] 			= 1;
		$INSTRUCTOR_PORTAL_SETTINGS['GRADE_BOOK_SETUP'] 			= 1;
		$INSTRUCTOR_PORTAL_SETTINGS['PROGRAM_GRADE_BOOK'] 			= 1;
		$INSTRUCTOR_PORTAL_SETTINGS['SAVE_GRADE_BOOK_AS_FINAL'] 	= 1;
		$INSTRUCTOR_PORTAL_SETTINGS['STUDENTS'] 					= 1;
		$INSTRUCTOR_PORTAL_SETTINGS['COURSE_HISTORY'] 				= 1;
		
		$res = $db->Execute("SELECT PK_ACCOUNT_INSTRUCTOR_PORTAL_SETTINGS FROM Z_ACCOUNT_INSTRUCTOR_PORTAL_SETTINGS WHERE PK_ACCOUNT = '$_GET[id]' ");
		if($res->RecordCount() == 0) {
			$INSTRUCTOR_PORTAL_SETTINGS['PK_ACCOUNT'] 	= $_GET['id'];
			$INSTRUCTOR_PORTAL_SETTINGS['CREATED_BY'] 	= $_SESSION['PK_USER'];
			$INSTRUCTOR_PORTAL_SETTINGS['CREATED_ON'] 	= date("Y-m-d H:i");
			db_perform('Z_ACCOUNT_INSTRUCTOR_PORTAL_SETTINGS', $INSTRUCTOR_PORTAL_SETTINGS, 'insert');
		} else {
			$PK_ACCOUNT_INSTRUCTOR_PORTAL_SETTINGS = $res->fields['PK_ACCOUNT_INSTRUCTOR_PORTAL_SETTINGS'];
			$INSTRUCTOR_PORTAL_SETTINGS['EDITED_BY'] = $_SESSION['PK_USER'];
			$INSTRUCTOR_PORTAL_SETTINGS['EDITED_ON'] = date("Y-m-d H:i");
			db_perform('Z_ACCOUNT_INSTRUCTOR_PORTAL_SETTINGS', $INSTRUCTOR_PORTAL_SETTINGS, 'update'," PK_ACCOUNT_INSTRUCTOR_PORTAL_SETTINGS = '$PK_ACCOUNT_INSTRUCTOR_PORTAL_SETTINGS' ");
		}
	}
	/* Ticket # 1304 */
	
	if($_POST['ENABLE_ETHINK'] == 1){
		$res = $db->Execute("SELECT PK_ACCOUNT_ETHINK_SETTINGS FROM Z_ACCOUNT_ETHINK_SETTINGS WHERE PK_ACCOUNT = '$_GET[id]' "); 
		$ETHINK_SETTINGS['TOKEN'] 						= $ETHINK_TOKEN;
		$ETHINK_SETTINGS['URL'] 						= $ETHINK_URL;
		$ETHINK_SETTINGS['DEFAULT_LMS_CATEGORY_CODE'] 	= $DEFAULT_LMS_CATEGORY_CODE;  //Ticket # 1473
		if($res->RecordCount() == 0) {
			$ETHINK_SETTINGS['PK_ACCOUNT'] 	 = $PK_ACCOUNT;
			$ETHINK_SETTINGS['CREATED_BY']   = $_SESSION['ADMIN_PK_USER'];
			$ETHINK_SETTINGS['CREATED_ON']   = date("Y-m-d H:i");
			db_perform('Z_ACCOUNT_ETHINK_SETTINGS', $ETHINK_SETTINGS, 'insert');
		} else {
			$ETHINK_SETTINGS['EDITED_BY']   = $_SESSION['ADMIN_PK_USER'];
			$ETHINK_SETTINGS['EDITED_ON']   = date("Y-m-d H:i");
			db_perform('Z_ACCOUNT_ETHINK_SETTINGS', $ETHINK_SETTINGS, 'update'," PK_ACCOUNT = '$PK_ACCOUNT'");
		}
	} else {
		$db->Execute("DELETE FROM Z_ACCOUNT_ETHINK_SETTINGS WHERE PK_ACCOUNT = '$_GET[id]' "); 
	}
	
	if($_POST['ENABLE_CANVAS'] == 1){
		$res = $db->Execute("SELECT PK_ACCOUNT_CANVAS_SETTINGS FROM Z_ACCOUNT_CANVAS_SETTINGS WHERE PK_ACCOUNT = '$_GET[id]' "); 
		$CANVAS_SETTINGS['ACCOUNT_ID'] 	= $CANVAS_ACCOUNT_ID;
		$CANVAS_SETTINGS['TOKEN'] 		= $CANVAS_TOKEN;
		$CANVAS_SETTINGS['URL'] 		= $CANVAS_URL;
		if($res->RecordCount() == 0) {
			$CANVAS_SETTINGS['PK_ACCOUNT'] 	 = $PK_ACCOUNT;
			$CANVAS_SETTINGS['CREATED_BY']   = $_SESSION['ADMIN_PK_USER'];
			$CANVAS_SETTINGS['CREATED_ON']   = date("Y-m-d H:i");
			db_perform('Z_ACCOUNT_CANVAS_SETTINGS', $CANVAS_SETTINGS, 'insert');
		} else {
			$CANVAS_SETTINGS['EDITED_BY']   = $_SESSION['ADMIN_PK_USER'];
			$CANVAS_SETTINGS['EDITED_ON']   = date("Y-m-d H:i");
			db_perform('Z_ACCOUNT_CANVAS_SETTINGS', $CANVAS_SETTINGS, 'update'," PK_ACCOUNT = '$PK_ACCOUNT'");
		}
	} else {
		$db->Execute("DELETE FROM Z_ACCOUNT_CANVAS_SETTINGS WHERE PK_ACCOUNT = '$_GET[id]' "); 
	}

	if(!empty($PK_TEXT_SETTINGS_ARR)){
		$i = 0;
		foreach($PK_TEXT_SETTINGS_ARR as $PK_TEXT_SETTINGS){
			$TEXT_SETTINGS['FROM_NO']  	= $FROM_NO_ARR[$i];
			$TEXT_SETTINGS['SID']   	= $SID_ARR[$i];
			$TEXT_SETTINGS['TOKEN'] 	= $TOKEN_ARR[$i];
			if($TEXT_SETTINGS['FROM_NO'] != '' && $TEXT_SETTINGS['SID'] != '' && $TEXT_SETTINGS['TOKEN'] != '' ) { //Ticket # 1304
				if($PK_TEXT_SETTINGS == '') {
					$TEXT_SETTINGS['PK_ACCOUNT']   		= $PK_ACCOUNT;
					$TEXT_SETTINGS['CREATED_ON']   		= date("Y-m-d H:i");
					$TEXT_SETTINGS['CREATED_BY']   		= $_SESSION['ADMIN_PK_USER'];
					db_perform('S_TEXT_SETTINGS', $TEXT_SETTINGS, 'insert' );
					$PK_TEXT_SETTINGS = $db->insert_ID();
					
					/* Ticket # 1304 */
					$res_11 = $db->Execute("SELECT PK_TEXT_SETTINGS FROM S_TEXT_SETTINGS WHERE PK_ACCOUNT = '$_GET[id]' "); 
					if($res_11->RecordCount() == 1){
						$res_11 = $db->Execute("SELECT GROUP_CONCAT(PK_DEPARTMENT) as PK_DEPARTMENT FROM M_DEPARTMENT WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND ACTIVE = 1 "); 
						
						$EVENT_TEMPLATE['RECIPIENTS_DEPARTMENT'] 	= $res_11->fields['PK_DEPARTMENT'];
						$EVENT_TEMPLATE['PK_TEXT_SETTINGS'] 		= $PK_TEXT_SETTINGS;
						$EVENT_TEMPLATE['PK_EVENT_TYPE'] 			= 17;
						$EVENT_TEMPLATE['CONTENT'] 		 			= "{Student Name} - {Text Message}";
						$EVENT_TEMPLATE['PK_ACCOUNT']  				= $PK_ACCOUNT;
						$EVENT_TEMPLATE['CREATED_BY']  				= $_SESSION['PK_USER'];
						$EVENT_TEMPLATE['CREATED_ON']  				= date("Y-m-d H:i");
						db_perform('S_EVENT_TEMPLATE', $EVENT_TEMPLATE, 'insert');
						$PK_EVENT_TEMPLATE = $db->Insert_ID();
						
						$res_11 = $db->Execute("SELECT PK_CAMPUS FROM S_CAMPUS WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND ACTIVE = 1 "); 
						while (!$res_11->EOF) {
							$TEMPLATE_CAMPUS['PK_ACCOUNT'] 			= $PK_ACCOUNT;
							$TEMPLATE_CAMPUS['PK_EVENT_TEMPLATE'] 	= $PK_EVENT_TEMPLATE;
							$TEMPLATE_CAMPUS['PK_CAMPUS'] 			= $res_11->fields['PK_CAMPUS'];
							$TEMPLATE_CAMPUS['CREATED_BY']  		= $_SESSION['PK_USER'];
							$TEMPLATE_CAMPUS['CREATED_ON'] 			= date("Y-m-d H:i");
							db_perform('S_EVENT_TEMPLATE_CAMPUS', $TEMPLATE_CAMPUS, 'insert');
							
							$res_11->MoveNext();
						}
						
						$res_11 = $db->Execute("SELECT S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) AS NAME, EMPLOYEE_ID FROM S_EMPLOYEE_MASTER LEFT JOIN S_EMPLOYEE_CAMPUS ON S_EMPLOYEE_CAMPUS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$PK_ACCOUNT' AND  S_EMPLOYEE_MASTER.ACTIVE = 1  GROUP BY S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER ORDER BY CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) ASC"); 
						while (!$res_11->EOF) {
							$TEMPLATE_RECIPIENTS['PK_ACCOUNT'] 			= $PK_ACCOUNT;
							$TEMPLATE_RECIPIENTS['PK_EVENT_TEMPLATE'] 	= $PK_EVENT_TEMPLATE;
							$TEMPLATE_RECIPIENTS['PK_EMPLOYEE_MASTER'] 	= $res_11->fields['PK_EMPLOYEE_MASTER'];
							$TEMPLATE_RECIPIENTS['CREATED_BY']  		= $_SESSION['PK_USER'];
							$TEMPLATE_RECIPIENTS['CREATED_ON'] 			= date("Y-m-d H:i");
							db_perform('S_EVENT_TEMPLATE_RECIPIENTS', $TEMPLATE_RECIPIENTS, 'insert');
							
							$res_11->MoveNext();
						}
					}
					/* Ticket # 1304 */
					
				} else {
					$TEXT_SETTINGS['EDITED_ON']   = date("Y-m-d H:i");
					$TEXT_SETTINGS['EDITED_BY']   = $_SESSION['ADMIN_PK_USER'];
					db_perform('S_TEXT_SETTINGS', $TEXT_SETTINGS, 'update'," PK_TEXT_SETTINGS = '$PK_TEXT_SETTINGS' " );
				}
			} //Ticket # 1304 
			$i++;
		}
	}
	
	/*rmdir('../backend_assets/school/school_'.$PK_ACCOUNT.'/student');
	rmdir('../backend_assets/school/school_'.$PK_ACCOUNT.'/employee');
	rmdir('../backend_assets/school/school_'.$PK_ACCOUNT.'/other');
	rmdir('../backend_assets/school/school_'.$PK_ACCOUNT);*/
	
	// $file_dir_1 = '../backend_assets/school/school_'.$PK_ACCOUNT.'/other/';
	$file_dir_1 = '../backend_assets/tmp_upload/';
	if(!empty($_FILES['LOGO'])){
		$name     = $_FILES['LOGO']['name'];
		$name	  = str_replace("#","_",$name);
		$name	  = str_replace("&","_",$name);
		$tmp_name = $_FILES['LOGO']['tmp_name'];
		$tmp_name = $_FILES['LOGO']['tmp_name'];
		if (trim($name)!=""){				
			$extn   = explode(".",$name);
			$iindex	= count($extn) - 1;
			$rand_string = time().rand(10000,99999);
			$name1 = str_replace(".".$extn[$iindex],"",$name);
			$file11 = 'logo_'.$_SESSION['ADMIN_PK_USER'].$rand_string.".".$extn[$iindex];						
			$newfile1 = $file_dir_1.$file11;	

			if(strtolower($extn[$iindex]) != 'php' || strtolower($extn[$iindex]) != 'js' || strtolower($extn[$iindex]) != 'html'){
			
				$newfile1    = $file_dir_1.$file11;
				$image_path  = $newfile1;
						
				move_uploaded_file($tmp_name, $image_path);
				
				$size = getimagesize($file_dir_1.$file11);
				$new_w = 400;
				$new_h = 400;
				
				if($size['0'] > $new_w || $size['1'] >  $new_h) {
					thumb_gallery($file11,$file11,$new_w,$new_h,$file_dir_1,1);
				}

				// Upload file to S3 bucket
				$key_file_name = 'backend_assets/school/school_'.$PK_ACCOUNT.'/other/'.$file11;
				$s3ClientWrapper = new s3ClientWrapper();
				$url = $s3ClientWrapper->uploadFile($key_file_name, $image_path);
				
				// $ACCOUNT1['LOGO'] = $image_path ;
				$ACCOUNT1['LOGO'] = $url ;
				db_perform('Z_ACCOUNT', $ACCOUNT1, 'update'," PK_ACCOUNT = '$PK_ACCOUNT'");

				// delete tmp file
				unlink($image_path);
			}
		}
	}
	
	/* Ticket # 1870 */
	if($ACCOUNT['ENABLE_LSQ'] == 1){
		$res = $db->Execute("SELECT PK_ACCOUNT_LSQ_SETTINGS FROM Z_ACCOUNT_LSQ_SETTINGS  WHERE PK_ACCOUNT = '$PK_ACCOUNT' ");
		
		$LSQ_SETTINGS['ACCESS_KEY'] 	= $LSQ_ACCESS_KEY;
		$LSQ_SETTINGS['SECRET_KEY'] 	= $LSQ_SECRET_KEY;
		$LSQ_SETTINGS['USER_NAME'] 		= $LSQ_USER_NAME;
		$LSQ_SETTINGS['PASSWORD'] 		= $LSQ_PASSWORD;
		$LSQ_SETTINGS['BASE_URL'] 		= $LSQ_BASE_URL;
		if($res->RecordCount() == 0) {
			$LSQ_SETTINGS['PK_ACCOUNT'] 	= $PK_ACCOUNT;
			$LSQ_SETTINGS['CREATED_ON'] 	= date("Y-m-d H:i:s");
			$LSQ_SETTINGS['CREATED_BY'] 	= $_SESSION['ADMIN_PK_USER'];
			db_perform('Z_ACCOUNT_LSQ_SETTINGS', $LSQ_SETTINGS , 'insert');
		} else {
			db_perform('Z_ACCOUNT_LSQ_SETTINGS', $LSQ_SETTINGS , 'update', " PK_ACCOUNT = '$PK_ACCOUNT'" );
		}
		
		$res_m_1 = $db->Execute("select * from M_NOTE_TYPE_MASTER WHERE PK_NOTE_TYPE_MASTER = 10 ");
		$res_m_2 = $db->Execute("select PK_NOTE_TYPE from M_NOTE_TYPE WHERE PK_NOTE_TYPE_MASTER = 10 AND PK_ACCOUNT = '$PK_ACCOUNT' ");
		if($res_m_1->RecordCount() > 0 && $res_m_2->RecordCount() == 0) {
			$PK_DEPARTMENT_MASTER = $res_m_1->fields['PK_DEPARTMENT_MASTER'];
			if($PK_DEPARTMENT_MASTER == -1)
				$NOTE_TYPE_2['PK_DEPARTMENT'] = -1;
			else { 
				$res_d = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE PK_DEPARTMENT_MASTER = '$PK_DEPARTMENT_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
				$NOTE_TYPE_2['PK_DEPARTMENT'] = $res_d->fields['PK_DEPARTMENT'];
			}
			
			$NOTE_TYPE_2['PK_NOTE_TYPE_MASTER'] 	= $res_m_1->fields['PK_NOTE_TYPE_MASTER'];
			$NOTE_TYPE_2['NOTE_TYPE'] 				= $res_m_1->fields['NOTE_TYPE'];
			$NOTE_TYPE_2['DESCRIPTION'] 			= $res_m_1->fields['DESCRIPTION'];
			$NOTE_TYPE_2['TYPE'] 					= $res_m_1->fields['TYPE'];
			$NOTE_TYPE_2['ACTIVE'] 					= 1;
			$NOTE_TYPE_2['PK_ACCOUNT'] 				= $PK_ACCOUNT;
			$NOTE_TYPE_2['CREATED_ON'] 				= date("Y-m-d H:i:s");
			$NOTE_TYPE_2['CREATED_BY'] 				= $_SESSION['ADMIN_PK_USER'];
			db_perform('M_NOTE_TYPE', $NOTE_TYPE_2, 'insert');
			
			$res_m_1->MoveNext();
		}
	} else {
		$db->Execute("DELETE FROM Z_ACCOUNT_LSQ_SETTINGS  WHERE PK_ACCOUNT = '$PK_ACCOUNT' ");
	}
	
	/* Ticket # 1870 */
	
//echo "<pre>";print_r($ACCOUNT);exit;	
	if($SAVE_CONTINUE == 0)
		header("location:manage_accounts");
	else
		header("location:accounts?id=".$PK_ACCOUNT.'&tab='.str_replace("#","",$current_tab));
}

if($_GET['id'] == ''){
	$SCHOOL_NAME	= '';
	$ADDRESS 		= '';
	$ADDRESS_1 		= '';
	$CITY	 		= '';
	$PK_STATES	 	= '';
	$ZIP	 		= '';
	$PK_COUNTRY	 	= '';
	$PHONE	 		= '';
	$FAX	 		= '';
	$EMAIL	 		= '';
	$WEBSITE	 	= '';
	$LOGO	 		= '';
	$STUD_CODE 		= '';
	$STUD_NO 		= 10000;
	$STU_DEFAULT_PASSWORD	= '';
	$HAS_STUDENT_PORTAL 	= '';
	$HAS_INSTRUCTOR_PORTAL 	= '';
	$ENABLE_UNPOST_BATCH 	= ''; // DIAM-987

	$ENABLE_DIAMOND_PAY		= '';
	$ENABLE_ETHINK			= '';
	$ENABLE_CANVAS			= '';
	
	/* Ticket # 1870  */
	$ENABLE_LSQ	= '';
	$LSQ_ACCESS_KEY = '';
	$LSQ_SECRET_KEY = '';
	$LSQ_USER_NAME 	= '';
	$LSQ_PASSWORD 	= '';
	$LSQ_BASE_URL	= '';
	/* Ticket # 1870  */
} else {
	$res = $db->Execute("SELECT * FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_GET[id]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_accounts");
		exit;
	}
	
	$SCHOOL_NAME 	= $res->fields['SCHOOL_NAME'];
	$ADDRESS 		= $res->fields['ADDRESS'];
	$ADDRESS_1 		= $res->fields['ADDRESS_1'];
	$CITY  			= $res->fields['CITY'];
	$PK_STATES  	= $res->fields['PK_STATES'];
	$ZIP  			= $res->fields['ZIP'];
	$PK_COUNTRY  	= $res->fields['PK_COUNTRY'];
	$PHONE  		= $res->fields['PHONE'];
	$FAX  			= $res->fields['FAX'];
	$EMAIL  		= $res->fields['EMAIL'];
	$WEBSITE  		= $res->fields['WEBSITE'];
	$LOGO  			= $res->fields['LOGO'];
	$STUD_CODE 		= $res->fields['STUD_CODE'];
	$STUD_NO 		= $res->fields['STUD_NO'];
	$STU_DEFAULT_PASSWORD	= $res->fields['STU_DEFAULT_PASSWORD'];
	$HAS_STUDENT_PORTAL 	= $res->fields['HAS_STUDENT_PORTAL'];
	$HAS_INSTRUCTOR_PORTAL 	= $res->fields['HAS_INSTRUCTOR_PORTAL'];
	$ENABLE_UNPOST_BATCH 	= $res->fields['ENABLE_UNPOST_BATCH']; // DIAM-987
	$ENABLE_DIAMOND_PAY 	= $res->fields['ENABLE_DIAMOND_PAY'];
	$API_KEY 				= $res->fields['API_KEY'];
	$ENABLE_ETHINK			= $res->fields['ENABLE_ETHINK'];
	$ENABLE_CANVAS			= $res->fields['ENABLE_CANVAS'];
	$ACTIVE					= $res->fields['ACTIVE'];


	// dvb 17 11 2025
	$CAMPUSIQ_TOKEN = $res->fields['CAMPUSIQ_TOKEN']??'';
	$CAMPUSIQ_DASHBOARDPK = $res->fields['CAMPUSIQ_DASHBOARDPK']??'';
	$CAMPUSIQ_ENABLE = $res->fields['CAMPUSIQ_ENABLE']??'';
	
	/* Ticket # 1870  */
	$ENABLE_LSQ	= $res->fields['ENABLE_LSQ'];
	$res_lsq = $db->Execute("SELECT * FROM Z_ACCOUNT_LSQ_SETTINGS  WHERE PK_ACCOUNT = '$_GET[id]' ");
	$LSQ_ACCESS_KEY = $res_lsq->fields['ACCESS_KEY'];
	$LSQ_SECRET_KEY = $res_lsq->fields['SECRET_KEY'];
	$LSQ_USER_NAME 	= $res_lsq->fields['USER_NAME'];
	$LSQ_PASSWORD 	= $res_lsq->fields['PASSWORD'];
	$LSQ_BASE_URL	= $res_lsq->fields['BASE_URL'];
	/* Ticket # 1870  */
	
	$res = $db->Execute("SELECT * FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_GET[id]' "); 
	$ABHES 	= $res->fields['ABHES'];
	$ACCET 	= $res->fields['ACCET'];
	$ACCSC 	= $res->fields['ACCSC'];
	$ACICS 	= $res->fields['ACICS'];

	
	$BPPE 	= $res->fields['BPPE'];
	$CIE 	= $res->fields['CIE'];
	$COE 	= $res->fields['COE'];
	$DEAC 	= $res->fields['DEAC'];
	$NACCAS = $res->fields['NACCAS'];
	$OEDS 	= $res->fields['OEDS'];
	$TWC 	= $res->fields['TWC'];
	$ECM 	= $res->fields['ECM'];
	$GUESTVISION = $res->fields['GUESTVISION'];
	
	$CUSTOM_QUERIES 	= $res->fields['CUSTOM_QUERIES']; //Ticket # 1295
	$_1098T 			= $res->fields['_1098T'];
	$_4807G 			= $res->fields['_4807G'];
	$_90_10 			= $res->fields['_90_10'];
	$IPEDS 				= $res->fields['IPEDS'];
	$FISAP 				= $res->fields['FISAP']; //Ticket # 1778
	$POPULATION_REPORT 	= $res->fields['POPULATION_REPORT'];
	
	$res = $db->Execute("SELECT * FROM Z_ACCOUNT_ETHINK_SETTINGS WHERE PK_ACCOUNT = '$_GET[id]' "); 
	$ETHINK_TOKEN 				= $res->fields['TOKEN'];
	$ETHINK_URL 				= $res->fields['URL'];
	$DEFAULT_LMS_CATEGORY_CODE 	= $res->fields['DEFAULT_LMS_CATEGORY_CODE']; //Ticket # 1473
	
	$res = $db->Execute("SELECT * FROM Z_ACCOUNT_CANVAS_SETTINGS WHERE PK_ACCOUNT = '$_GET[id]' "); 
	$CANVAS_TOKEN 		= $res->fields['TOKEN'];
	$CANVAS_URL 		= $res->fields['URL'];
	$CANVAS_ACCOUNT_ID	= $res->fields['ACCOUNT_ID'];
}


if($_GET['tab'] == '' || $_GET['tab'] == 'homeTab' )
	$home_tab = 'active';
else if($_GET['tab'] == 'sysConf')
	$sysconf_tab = 'active';
else if($_GET['tab'] == 'campusTab')
	$campus_tab = 'active';
else if($_GET['tab'] == 'usersTab')
	$user_tab = 'active';
else if($_GET['tab'] == 'contactTab')
	$contact_tab = 'active';
else if($_GET['tab'] == 'communications_tab')
	$communications_tab = 'active';
else if($_GET['tab'] == 'customQueriesTab') // Ticket # 1295
	$customQueriesTab = 'active';
else
	$home_tab = 'active';
	
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
	<title>Accounts | <?=$title?></title>
	<style>
		input::-webkit-outer-spin-button,
		input::-webkit-inner-spin-button {
		  -webkit-appearance: none;
		  margin: 0;
		}

		/* Firefox */
		input[type=number] {
		  -moz-appearance: textfield;
		}
		.no-records-found{display:none;}
		option.option_red {
			color: red !important;
		}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Accounts</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<ul class="nav nav-tabs customtab" role="tablist">
                                <li class="nav-item"> <a class="nav-link <?=$home_tab?>" data-toggle="tab" href="#homeTab" role="tab"><span class="hidden-sm-up"><i class="ti-homeTab"></i></span> <span class="hidden-xs-down">General</span></a> </li>
								<? if($_GET['id'] != ''){ ?>
								<li class="nav-item"> <a class="nav-link <?=$sysconf_tab?>" data-toggle="tab" href="#sysConf" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">System Configuration</span></a> </li>
								<li class="nav-item"> <a class="nav-link <?=$communications_tab?>" data-toggle="tab" href="#communications_tab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">Communications</span></a> </li>
                                <li class="nav-item"> <a class="nav-link <?=$campus_tab?>" data-toggle="tab" href="#campusTab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down">Campus</span></a> </li>
                                <li class="nav-item"> <a class="nav-link <?=$user_tab?>" data-toggle="tab" href="#usersTab" role="tab"><span class="hidden-sm-up"><i class="ti-email"></i></span> <span class="hidden-xs-down">User</span></a> </li>
								<li class="nav-item"> <a class="nav-link <?=$contact_tab?>" data-toggle="tab" href="#contactTab" role="tab"><span class="hidden-sm-up"><i class="ti-email"></i></span> <span class="hidden-xs-down">Contacts</span></a> </li>
								
								<li class="nav-item"> <a class="nav-link <?=$customQueriesTab?>" data-toggle="tab" href="#customQueriesTab" role="tab"><span class="hidden-sm-up"><i class="ti-email"></i></span> <span class="hidden-xs-down">Custom Queries</span></a> </li> <!-- Ticket # 1295 -->
								<? } ?>
                            </ul>
                            <!-- Tab panes -->
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
								<div class="tab-content">
									<div class="tab-pane <?=$home_tab?>" id="homeTab" role="tabpanel">
										<div class="p-20">
		                                    
		                                    
		                                    <div class="row">
		                                    	<div class="col-sm-6">
		                                    		<div class="d-flex">
				                                    	<div class="col-12 col-sm-6 form-group">
				                                    		<input id="SCHOOL_NAME" name="SCHOOL_NAME" type="text" class="form-control required-entry" value="<?=$SCHOOL_NAME?>">
					                                        <span class="bar"></span> 
					                                        <label for="SCHOOL_NAME">School Name</label>
				                                    	</div>
				                                    </div>
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<input id="ADDRESS" name="ADDRESS" type="text" class="form-control" value="<?=$ADDRESS?>">
															<span class="bar"></span>
															<label for="ADDRESS">Address</label>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<input id="ADDRESS_1" name="ADDRESS_1" type="text" class="form-control" value="<?=$ADDRESS_1?>">
															<span class="bar"></span>
															<label for="ADDRESS_1">Address 2</label>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="CITY" name="CITY" type="text" class="form-control" value="<?=$CITY?>">
															<span class="bar"></span> 
															 <label for="CITY">City</label>
														</div>
														<div class="col-12 col-sm-6 form-group">
															<select id="PK_STATES" name="PK_STATES" class="form-control required-entry" >  <!-- onchange="get_country(this.value,'PK_COUNTRY')" -->
																<option selected></option>
																<? $res_type = $db->Execute("select PK_STATES, STATE_NAME from Z_STATES WHERE ACTIVE = '1' ORDER BY STATE_NAME ASC ");
																while (!$res_type->EOF) { ?>
																	<option value="<?=$res_type->fields['PK_STATES'] ?>" <? if($PK_STATES == $res_type->fields['PK_STATES']) echo "selected"; ?> ><?=$res_type->fields['STATE_NAME']?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
															<span class="bar"></span> 
															<label for="STATE">State</label>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="ZIP" name="ZIP" type="text" class="form-control" value="<?=$ZIP?>">
															<span class="bar"></span> 
															 <label for="ZIP">Zip</label>
														</div>	
														<div class="col-12 col-sm-6 form-group" id="PK_COUNTRY_LABEL" >
															<div id="PK_COUNTRY_DIV" >
																<select id="PK_COUNTRY" name="PK_COUNTRY" class="form-control">
																	<option selected></option>
																	  <?$res_type1 = $db->Execute("select PK_COUNTRY, NAME from Z_COUNTRY WHERE ACTIVE = '1' ORDER BY NAME ASC ");
																       while (!$res_type1->EOF) { ?>
																	   <option value="<?=$res_type1->fields['PK_COUNTRY'] ?>" <? if($PK_COUNTRY == $res_type1->fields['PK_COUNTRY']) echo "selected"; ?> ><?=$res_type1->fields['NAME']?></option>
																	    <?	$res_type1->MoveNext();
																	    }
																	    ?>
																</select>
															</div>
															<span class="bar"></span> 
															<label for="COUNTRY">Country</label>
														</div>	                                        
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="STUD_CODE" name="STUD_CODE" type="text" class="form-control required-entry" value="<?=$STUD_CODE?>" onblur="check_duplicate_stud_code()" >
															<span class="bar"></span> 
															<label for="STUD_CODE">Student ID Default Code</label>
															<div id="STUD_CODE_div" style="display:none;color:#ff0000;">Student ID Default Code Exists</div>
														</div>	
														<div class="col-12 col-sm-6 form-group" >
															<input id="STUD_NO" name="STUD_NO" type="number" class="form-control" value="<?=$STUD_NO?>" <? if($_GET['id'] != ''){ ?> disabled <? } ?>  min="0">
															<span class="bar"></span> 
															<label for="STUD_NO">Student ID Starting Number</label>
															<div id="STUD_NO_div" style="display:none;color:#ff0000;">Cannot use this Number as this will create duplicate Student ID in future</div>
														</div>	                                        
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="STU_DEFAULT_PASSWORD" name="STU_DEFAULT_PASSWORD" type="text" class="form-control" value="<?=$STU_DEFAULT_PASSWORD?>">
															<span class="bar"></span> 
															<label for="STU_DEFAULT_PASSWORD">Student Default Password</label>
														</div>
													</div>
													
												</div>
												<div class="col-sm-6">
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<input id="PHONE" name="PHONE" type="text" class="form-control phone-inputmask" value="<?=$PHONE?>" >
															<span class="bar"></span> 
															 <label for="PHONE">Phone</label>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<input id="EMAIL" name="EMAIL" type="text" class="form-control validate-email" value="<?=$EMAIL?>">
															<span class="bar"></span> 
															 <label for="EMAIL">Email</label>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<input id="FAX" name="FAX" type="text" class="form-control phone-inputmask" value="<?=$FAX?>">
															<span class="bar"></span> 
															 <label for="FAX">Fax</label>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<input id="WEBSITE" name="WEBSITE" type="text" class="form-control validate-url" value="<?=$WEBSITE?>" onfocus="set_web_default(1)" onblur="set_web_default(0)" >
															<span class="bar"></span> 
															<label for="WEBSITE">Website</label>
														</div>
													</div>
													<div class="form-group col-12">
														<? if($LOGO == '') { ?>
														<label>Logo</label>
														<div class="input-group">
															<div class="input-group-prepend" style="margin-top: 5px;" >
																<span class="input-group-text">Logo</span>
															</div>
															<div class="custom-file">
																<input type="file" name="LOGO" id="LOGO" class="custom-file-input" id="inputGroupFile01">
																<label class="custom-file-label" for="inputGroupFile01">Choose file</label>
															</div>
														</div>
														<? } else { ?>
														<table>
															<tr>
																<td valign="top">Logo&nbsp;</td>
																<td><img src="<?=$LOGO?>" style="height:80px;" /></td>
																<td>
																	<a href="javascript:void(0)" onclick="delete_row('','logo')" >
																		<i class="icon-trash round_red icon_size" title="Delete"></i>
																	</a>
																</td>
															</tr>
														</table>
														<? } ?>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-8 form-group">
															<input id="API_KEY" type="text" class="form-control" value="<?=$API_KEY?>" >
															<span class="bar"></span> 
															<label for="API_KEY">API Key</label>
														</div>
														<div class="col-12 col-sm-4 form-group">
															<a href="javascript:void(0)" onclick="generate_key()" >Generate API Key</a>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-8 form-group">
															<div class="row form-group">
																<div class="custom-control col-md-3">Active</div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
																	<label class="custom-control-label" for="customRadio11">Yes</label>
																</div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
																	<label class="custom-control-label" for="customRadio22">No</label>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
		                                   
		                                    <div class="row">
												<div class="col-3 col-sm-3">
												</div>
												
												<div class="col-9 col-sm-9">
													<input type="hidden" name="SAVE_CONTINUE" id="SAVE_CONTINUE" value="0" />
													<input type="hidden" id="current_tab" name="current_tab" value="0" >
								
													<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info">Save & Continue</button>
													
													<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info">Save & Exit</button>
													
													<button type="button" onclick="window.location.href='manage_accounts'"  class="btn waves-effect waves-light btn-dark">Cancel</button>
												</div>
		                                    </div>
										</div>
									</div>
									<? if($_GET['id'] != ''){ ?>
									<div class="tab-pane <?=$communications_tab?>" id="communications_tab" role="tabpanel">
										<div class="p-20">
		                                    <div class="row">
		                                    	<div class="col-sm-8">
		                                    	
													<div id="FROM_NO_DIV">
														<? $from_count = 0;
														$res_type = $db->Execute("select PK_TEXT_SETTINGS from S_TEXT_SETTINGS WHERE PK_ACCOUNT = '$_GET[id]' ");
														while (!$res_type->EOF) {
															$_REQUEST['from_count'] 		= $from_count;
															$_REQUEST['PK_TEXT_SETTINGS'] 	= $res_type->fields['PK_TEXT_SETTINGS'];
															include("add_from_no.php");
															
															$from_count++;
															$res_type->MoveNext();
														} ?>
													</div>
													
													<div class="row">
														<div class="col-12 col-sm-8 form-group">
															<button onclick="add_from_no()" style="float:right" type="button" class="btn waves-effect waves-light btn-info">Add Another From Number</button>
														</div>
													</div>
												</div>
												<div class="col-sm-4">
												</div>
											</div>
											<div class="row form-group">
												<div class="col-3 col-sm-3">
												</div>
												
												<div class="col-9 col-sm-9">
													<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info">Save & Continue</button>
													
													<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info">Save & Exit</button>
													
													<button type="button" onclick="window.location.href='manage_accounts'"  class="btn waves-effect waves-light btn-dark">Cancel</button>
												</div>
											</div>
										</div>
									</div>
									
									<div class="tab-pane <?=$sysconf_tab?>" id="sysConf" role="tabpanel">
										
										
										<div class="d-flex">
											<div class="col-md-6 form-group">
												<div class="d-flex">
													<div class="col-md-12">
														<h4><b>Diamond Add-Ons</b></h4>
													</div>
												</div>
												<br />
												
												<!-- Ticket # 1870  -->
												<div class="d-flex">
													<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="ENABLE_LSQ" name="ENABLE_LSQ" value="1" <? if($ENABLE_LSQ == 1) echo "checked"; ?> onclick="show_lsq_fields()" >
														<label class="custom-control-label" for="ENABLE_LSQ">Enable Diamond CRM Powered by LeadSquared</label>
													</div>
												</div>
												<div class="d-flex lsq" <? if($ENABLE_LSQ != 1) { ?> style="display:none !important;" <? } ?> >
													<div class="col-12 col-sm-12 form-group" >
														<input id="LSQ_BASE_URL" name="LSQ_BASE_URL" type="text" class="form-control" value="<?=$LSQ_BASE_URL?>" >
														<label for="LSQ_BASE_URL">Base URL</label>
													</div>
												</div>
												<div class="d-flex lsq" <? if($ENABLE_LSQ != 1) { ?> style="display:none !important;" <? } ?> >
													<div class="col-12 col-sm-12 form-group" >
														<input id="LSQ_ACCESS_KEY" name="LSQ_ACCESS_KEY" type="text" class="form-control" value="<?=$LSQ_ACCESS_KEY?>" >
														<label for="LSQ_ACCESS_KEY">Access Token</label>
													</div>
												</div>
												<div class="d-flex lsq" <? if($ENABLE_LSQ != 1) { ?> style="display:none !important;" <? } ?> >
													<div class="col-12 col-sm-12 form-group"  >
														<input id="LSQ_SECRET_KEY" name="LSQ_SECRET_KEY" type="text" class="form-control" value="<?=$LSQ_SECRET_KEY?>" >
														<label for="LSQ_SECRET_KEY">Secret Key</label>
													</div>
												</div>
												<div class="d-flex lsq" <? if($ENABLE_LSQ != 1) { ?> style="display:none !important;" <? } ?> >
													<div class="col-12 col-sm-12 form-group"  >
														<input id="LSQ_USER_NAME" name="LSQ_USER_NAME" type="text" class="form-control" value="<?=$LSQ_USER_NAME?>" >
														<label for="LSQ_USER_NAME">User Name</label>
													</div>
												</div>
												<div class="d-flex lsq" <? if($ENABLE_LSQ != 1) { ?> style="display:none !important;" <? } ?> >
													<div class="col-12 col-sm-12 form-group"  >
														<input id="LSQ_PASSWORD" name="LSQ_PASSWORD" type="text" class="form-control" value="<?=$LSQ_PASSWORD?>" >
														<label for="LSQ_PASSWORD">Password</label>
													</div>
												</div>
												<!-- Ticket # 1870  -->

												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="HAS_STUDENT_PORTAL" name="HAS_STUDENT_PORTAL" value="1" <? if($HAS_STUDENT_PORTAL == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="HAS_STUDENT_PORTAL">Enable Student Portal</label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox " >
														<input type="checkbox" class="custom-control-input" id="HAS_INSTRUCTOR_PORTAL" name="HAS_INSTRUCTOR_PORTAL" value="1" <? if($HAS_INSTRUCTOR_PORTAL == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="HAS_INSTRUCTOR_PORTAL">Enable Instructor Portal</label>
													</div>
												</div>
												<br />

												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox " >
														<input type="checkbox" class="custom-control-input" id="ENABLE_UNPOST_BATCH" name="ENABLE_UNPOST_BATCH" value="1" <? if($ENABLE_UNPOST_BATCH == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="ENABLE_UNPOST_BATCH">Enable Unpost Batch</label>
													</div>
												</div>

												<!-- DIAM-2101 -->
												<hr />			
												<!-- <div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="ENABLE_DIAMOND_PAY" name="ENABLE_DIAMOND_PAY" value="1" <? if($ENABLE_DIAMOND_PAY == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="ENABLE_DIAMOND_PAY">Enable Diamond Pay</label>
													</div>
												</div> -->

												<div class="d-flex">
													<div class="col-md-12 form-group">
														<h4><b>Enable Diamond Pay</b></h4>
													</div>
												</div>
                                                <div class="d-flex">
                                                    <div class="col-4 col-sm-4 custom-control custom-checkbox">
                                                        <input type="radio" id="ENABLE_DIAMOND_PAY" name="ENABLE_DIAMOND_PAY" value="1" class="custom-control-input" <? if($ENABLE_DIAMOND_PAY == 1) echo "checked"; ?>>
                                                        <label class="custom-control-label" for="ENABLE_DIAMOND_PAY">Enable Plugnpay</label>
                                                    </div>
                                                    <div class="col-4 col-sm-4 custom-control custom-checkbox">
                                                        <input type="radio" id="ENABLE_DIAMOND_PAY22" name="ENABLE_DIAMOND_PAY" value="2" <? if($ENABLE_DIAMOND_PAY == 2) echo "checked"; ?> class="custom-control-input">
                                                        <label class="custom-control-label" for="ENABLE_DIAMOND_PAY22">Enable Stax Connect</label>
                                                    </div>
                                                    <input style="display:none;" type="radio" id="ENABLE_DIAMOND_PAY33" name="ENABLE_DIAMOND_PAY" value="0" class="custom-control-input" <? if($ENABLE_DIAMOND_PAY == 0) echo "checked"; ?>>


                                                    <!-- aca lo debees poner luis -->
                                                    <!-- CyberSource Option -->
                                                    <div class="col-4 col-sm-4 custom-control custom-checkbox">
                                                        <input type="radio" id="ENABLE_DIAMOND_PAY_CYBERSOURCE" name="ENABLE_DIAMOND_PAY" value="3" <? if($ENABLE_DIAMOND_PAY == 3) echo "checked"; ?> class="custom-control-input">
                                                        <label class="custom-control-label" for="ENABLE_DIAMOND_PAY_CYBERSOURCE">Enable CyberSource</label>
                                                    </div>
                                                    <!--  -->
                                                </div>
												<!-- End DIAM-2101 -->

												<hr />
												
												<div class="d-flex">
													<div class="col-md-12">
														<h4><b>Accreditation Reports</b></h4>
													</div>
												</div>
												<br />
												
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="ABHES" name="ABHES" value="1" <? if($ABHES == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="ABHES">ABHES</label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="ACCET" name="ACCET" value="1" <? if($ACCET == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="ACCET">ACCET</label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="ACCSC" name="ACCSC" value="1" <? if($ACCSC == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="ACCSC">ACCSC</label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="ACICS" name="ACICS" value="1" <? if($ACICS == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="ACICS">ACICS</label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="BPPE" name="BPPE" value="1" <? if($BPPE == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="BPPE">BPPE</label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="CIE" name="CIE" value="1" <? if($CIE == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="CIE">CIE</label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="COE" name="COE" value="1" <? if($COE == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="COE">COE</label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="DEAC" name="DEAC" value="1" <? if($DEAC == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="DEAC">DEAC</label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="NACCAS" name="NACCAS" value="1" <? if($NACCAS == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="NACCAS">NACCAS</label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="OEDS" name="OEDS" value="1" <? if($OEDS == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="OEDS">OEDS</label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="TWC" name="TWC" value="1" <? if($TWC == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="TWC">TWC</label>
													</div>
												</div>

												<!-- campúps iq 17 11 2025 -->
												<div class="d-flex">
												    <div class="col-md-12">
												        <h4><b>Campus IQ</b></h4>
												    </div>
												</div>
												<br >
												<!-- CAMPUS IQ DVB 04 12 2025 -->
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="CAMPUSIQ_ENABLE" name="CAMPUSIQ_ENABLE" value="1" <? if($CAMPUSIQ_ENABLE == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="CAMPUSIQ_ENABLE">Enable Campus IQ</label>
													</div>
												</div>

												<div class="d-flex">
												    <div class="col-12 col-sm-12 form-group"  style="display: none;">
												        <input id="CAMPUSIQ_TOKEN" name="CAMPUSIQ_TOKEN" type="text" class="form-control" value="<?=$CAMPUSIQ_TOKEN?>">
												        <label for="CAMPUSIQ_TOKEN">Token</label>
												    </div>

												</div>
												<!-- CAMPUS IQ DVB 04 12 2025 -->
												<div class="col-md-2 form-group">
													<?php
													$res_dashboard = $db->Execute("SELECT * FROM CAMPUSIQ_DASHBOARDS WHERE ACTIVE = 1 ORDER BY DASHBOARDID ASC");

													// Convierte valor guardado en BD a arreglo
													$selectedDashboards = [];
													if (!empty($CAMPUSIQ_DASHBOARDPK)) {
														$selectedDashboards = array_filter(explode(',', $CAMPUSIQ_DASHBOARDPK));
													}
													?>
													
													<select id="CAMPUSIQ_DASHBOARDPK" 
															name="CAMPUSIQ_DASHBOARDPK[]" 
															class="form-control" 
															style="width: auto;height: 200px;" 
															multiple="multiple">

														<?php while (!$res_dashboard->EOF) { 
															$dashId   = $res_dashboard->fields['DASHBOARDID'];
															$dashName = $res_dashboard->fields['NAME'];
															$selected = in_array($dashId, $selectedDashboards) ? 'selected' : '';
														?>
															<option value="<?=$dashId?>" <?=$selected?>>
																<?=$dashName?> <?=$dashId?>
															</option>
														<?php 
															$res_dashboard->MoveNext();
														} ?>
													</select>
													<span class="bar"></span> 
													<label for="CAMPUSIQ_DASHBOARDPK" style="width: 400px" >Dashboard ID (ctrl + click to select many)</label>
												</div>
												<!-- CAMPUS IQ DVB 04 12 2025 -->
												
											</div>
											
											<div class="col-md-6 form-group">
												<div class="col-md-6 ">
													<div class="d-flex">
														<div class="col-md-12 form-group">
															<h4><b>Compliance Reports</b></h4>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="_1098T" name="_1098T" value="1" <? if($_1098T == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="_1098T">1098T</label>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="_4807G" name="_4807G" value="1" <? if($_4807G == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="_4807G">480.7G</label>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="_90_10" name="_90_10" value="1" <? if($_90_10 == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="_90_10">90/10</label>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="FISAP" name="FISAP" value="1" <? if($FISAP == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="FISAP">FISAP</label>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="IPEDS" name="IPEDS" value="1" <? if($IPEDS == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="IPEDS">IPEDS</label>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-6 col-sm-6 custom-control custom-checkbox " >
															<input type="checkbox" class="custom-control-input" id="POPULATION_REPORT" name="POPULATION_REPORT" value="1" <? if($POPULATION_REPORT == 1) echo "checked"; ?> >
															<label class="custom-control-label" for="POPULATION_REPORT">Population Report</label>
														</div>
													</div>
												</div>
												<hr />
											
												<div class="d-flex">
													<div class="col-md-12 form-group">
														<h4><b>Title IV Servicer</b></h4>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox" >
														<input type="checkbox" class="custom-control-input" id="ECM" name="ECM" value="1" <? if($ECM == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="ECM">Enable ECM</label>
													</div>
												</div>
												<hr />
												<div class="d-flex">
													<div class="col-md-12 form-group">
														<h4><b>Time Clock</b></h4>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox" >
														<input type="checkbox" class="custom-control-input" id="GUESTVISION" name="GUESTVISION" value="1" <? if($GUESTVISION == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="GUESTVISION">GuestVision</label>
													</div>
												</div>
												<hr />
												
												<div class="d-flex " >
													<div class="col-md-12 form-group">
														<h4><b>Learning Management System</b></h4>
													</div>
												</div>
												
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="ENABLE_ETHINK" name="ENABLE_ETHINK" value="1" <? if($ENABLE_ETHINK == 1) echo "checked"; ?> onclick="show_ethink_fields()" >
														<label class="custom-control-label" for="ENABLE_ETHINK">Moodle - Ethink</label>
													</div>
												</div>
												
												<div class="d-flex ethink" <? if($ENABLE_ETHINK != 1) { ?> style="display:none !important;" <? } ?> >
													<div class="col-12 col-sm-12 form-group" >
														<input id="ETHINK_TOKEN" name="ETHINK_TOKEN" type="text" class="form-control" value="<?=$ETHINK_TOKEN?>" >
														<label for="ETHINK_TOKEN">Token</label>
													</div>
												</div>
												<div class="d-flex ethink" <? if($ENABLE_ETHINK != 1) { ?> style="display:none !important;" <? } ?> >
													<div class="col-12 col-sm-12 form-group"  >
														<input id="ETHINK_URL" name="ETHINK_URL" type="text" class="form-control" value="<?=$ETHINK_URL?>" >
														<label for="ETHINK_URL">URL</label>
													</div>
												</div>
												<!-- Ticket #1473 -->
												<div class="d-flex ethink" <? if($ENABLE_ETHINK != 1) { ?> style="display:none !important;" <? } ?> >
													<div class="col-12 col-sm-12 form-group"  >
														<input id="DEFAULT_LMS_CATEGORY_CODE" name="DEFAULT_LMS_CATEGORY_CODE" type="text" class="form-control" value="<?=$DEFAULT_LMS_CATEGORY_CODE?>" >
														<label for="DEFAULT_LMS_CATEGORY_CODE">Default LMS Category Code</label>
													</div>
												</div>
												<!-- Ticket #1473 -->
												<hr /><br />
												
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox form-group" >
														<input type="checkbox" class="custom-control-input" id="ENABLE_CANVAS" name="ENABLE_CANVAS" value="1" <? if($ENABLE_CANVAS == 1) echo "checked"; ?> onclick="show_canvas_fields()" >
														<label class="custom-control-label" for="ENABLE_CANVAS">Canvas</label>
													</div>
												</div>
												
												<div class="d-flex canvas" <? if($ENABLE_CANVAS != 1) { ?> style="display:none !important;" <? } ?> >
													<div class="col-12 col-sm-12 form-group" >
														<input id="CANVAS_ACCOUNT_ID" name="CANVAS_ACCOUNT_ID" type="text" class="form-control" value="<?=$CANVAS_ACCOUNT_ID?>" >
														<label for="CANVAS_ACCOUNT_ID">Account ID</label>
													</div>
												</div>
												<div class="d-flex canvas" <? if($ENABLE_CANVAS != 1) { ?> style="display:none !important;" <? } ?> >
													<div class="col-12 col-sm-12 form-group" >
														<input id="CANVAS_TOKEN" name="CANVAS_TOKEN" type="text" class="form-control" value="<?=$CANVAS_TOKEN?>" >
														<label for="CANVAS_TOKEN">Token</label>
													</div>
												</div>
												<div class="d-flex canvas" <? if($ENABLE_CANVAS != 1) { ?> style="display:none !important;" <? } ?> >
													<div class="col-12 col-sm-12 form-group"  >
														<input id="CANVAS_URL" name="CANVAS_URL" type="text" class="form-control" value="<?=$CANVAS_URL?>" >
														<label for="CANVAS_URL">URL</label>
													</div>
												</div>
												<hr />
												<!-- Ticket # 1295 -->
												<div class="d-flex">
													<div class="col-md-12 form-group">
														<h4><b>Custom Queries</b></h4>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-6 col-sm-6 custom-control custom-checkbox" >
														<input type="checkbox" class="custom-control-input" id="CUSTOM_QUERIES" name="CUSTOM_QUERIES" value="1" <? if($CUSTOM_QUERIES == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="CUSTOM_QUERIES">Queries</label>
													</div>
												</div>
												<!-- Ticket # 1295 -->
											</div>
										</div>
										
										<div class="row form-group">
											<div class="col-3 col-sm-3">
											</div>
											
											<div class="col-9 col-sm-9">
												<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info">Save & Continue</button>
												
												<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info">Save & Exit</button>
												
												<button type="button" onclick="window.location.href='manage_accounts'"  class="btn waves-effect waves-light btn-dark">Cancel</button>
											</div>
										</div>
									</div>
									
									<div class="tab-pane <?=$campus_tab?>" id="campusTab" role="tabpanel">
										<div class="row">
											<div class="col-md-10 align-self-center">
											</div>  
											<div class="col-md-2 align-self-center text-right">
												<div class="d-flex justify-content-end align-items-center">
													<a href="campus?s_id=<?=$_GET['id']?>" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</a>&nbsp;&nbsp;
												</div>
											</div>
										</div>
										<div class="table-responsive p-20">
											<!-- Ticket # 1322 -->
											<table class="table table-hover">
												<thead>
													<tr>
														<th>#</th>
														<th>Official Campus Name</th>
														<th>Campus Name</th>
														<th>Campus Code</th>
														<th>City</th>
														<th>State</th>
														<th>Phone</th>
														<th>Primary</th>
														<th>Active</th>
														<th>Options</th>
													</tr>
												</thead>
												<tbody>
													<? $res_type = $db->Execute("select PK_CAMPUS, OFFICIAL_CAMPUS_NAME,CAMPUS_NAME, CAMPUS_CODE, PHONE, CITY, STATE_CODE, IF(PRIMARY_CAMPUS = 1,'Yes','') AS PRIMARY_CAMPUS, IF(S_CAMPUS.ACTIVE = 1, 'Yes', 'No') as ACTIVE from S_CAMPUS LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_CAMPUS.PK_STATES WHERE PK_ACCOUNT = '$_GET[id]'");
													$i = 0;
													while (!$res_type->EOF) { 
														$i++; ?>
														<tr>
															<td><?=$i?></td>
															<td><?=$res_type->fields['OFFICIAL_CAMPUS_NAME']?></td>
															<td><?=$res_type->fields['CAMPUS_NAME']?></td>
															<td><?=$res_type->fields['CAMPUS_CODE']?></td>
															<td><?=$res_type->fields['CITY']?></td>
															<td><?=$res_type->fields['STATE_CODE']?></td>
															<td><?=$res_type->fields['PHONE']?></td>
															<td><?=$res_type->fields['PRIMARY_CAMPUS']?></td>
															<td><?=$res_type->fields['ACTIVE']?></td>
															<td>
																<a href="campus?id=<?=$res_type->fields['PK_CAMPUS']?>&s_id=<?=$_GET['id']?>" title="Edit" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>
																<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_CAMPUS']?>','campus')" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i> </a>
															</td>
														</tr>
													<?	$res_type->MoveNext();
													} ?>
												</tbody>
											</table>
											<!-- Ticket # 1322 -->
										</div>
									</div>
									<div class="tab-pane <?=$user_tab?>" id="usersTab" role="tabpanel">
										<div class="row">
											<div class="col-md-10 align-self-center">
											</div>  
											<div class="col-md-2 align-self-center text-right">
												<div class="d-flex justify-content-end align-items-center">
													<a href="user?s_id=<?=$_GET['id']?>" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</a>&nbsp;&nbsp;
												</div>
											</div>
										</div>
										<div class="table-responsive p-20">
											<table class="table table-hover">
												<thead>
													<tr>
														<th>#</th>
														<th>Name</th>
														<th>Role</th>
														<th>Email</th>
														<th>Cell Phone</th>
														<th>Options</th>
													</tr>
												</thead>
												<tbody>
													<? $res_type = $db->Execute("SELECT CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME ,EMAIL,CELL_PHONE, USER_ID,ROLES ,S_EMPLOYEE_MASTER.ACTIVE, S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER FROM S_EMPLOYEE_MASTER, S_EMPLOYEE_CONTACT, Z_USER LEFT JOIN Z_ROLES ON Z_ROLES.PK_ROLES = Z_USER.PK_ROLES WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_GET[id]' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_EMPLOYEE_CONTACT.PK_EMPLOYEE_MASTER AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE = 2");
													$i = 0;
													while (!$res_type->EOF) { 
														$i++; ?>
														<tr>
															<td><?=$i?></td>
															<td><?=$res_type->fields['NAME']?></td>
															<td><?=$res_type->fields['ROLES']?></td>
															<td><?=$res_type->fields['EMAIL']?></td>
															<td><?=$res_type->fields['CELL_PHONE']?></td>
															<td>
																<a href="user?id=<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>&s_id=<?=$_GET['id']?>" title="Edit" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>
																<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>','user')" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i> </a>
															</td>
														</tr>
													<?	$res_type->MoveNext();
													} ?>
												</tbody>
											</table>
										</div>
									</div>
									<div class="tab-pane <?=$contact_tab?>" id="contactTab" role="tabpanel">
										<div class="row">
											<div class="col-md-10 align-self-center">
											</div>  
											<div class="col-md-2 align-self-center text-right">
												<div class="d-flex justify-content-end align-items-center">
													<a href="contact?s_id=<?=$_GET['id']?>" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</a>&nbsp;&nbsp;
												</div>
											</div>
										</div>
										<div class="table-responsive p-20">
											<table class="table table-hover">
												<thead>
													<tr>
														<th>#</th>
														<th>Name</th>
														<th>Contact Type</th>
														<th>Campus</th>
														<th>Email</th>
														<th>Phone</th>
														<th>Options</th>
													</tr>
												</thead>
												<tbody>
													<? $res_type = $db->Execute("SELECT PK_SCHOOL_CONTACT,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME ,EMAIL,CONTACT_TYPES , S_SCHOOL_CONTACT.PHONE, IF(S_SCHOOL_CONTACT.PK_CAMPUS = -1 , 'School Level' , OFFICIAL_CAMPUS_NAME ) AS OFFICIAL_CAMPUS_NAME FROM S_SCHOOL_CONTACT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_SCHOOL_CONTACT.PK_CAMPUS  LEFT JOIN M_CONTACT_TYPES ON S_SCHOOL_CONTACT.PK_CONTACT_TYPES = M_CONTACT_TYPES.PK_CONTACT_TYPES WHERE S_SCHOOL_CONTACT.PK_ACCOUNT = '$_GET[id]' AND SHOW_DSIS = 1 ");
													$i = 0;
													while (!$res_type->EOF) { 
														$i++; ?>
														<tr>
															<td><?=$i?></td>
															<td><?=$res_type->fields['NAME']?></td>
															<td><?=$res_type->fields['CONTACT_TYPES']?></td>
															<td><?=$res_type->fields['OFFICIAL_CAMPUS_NAME']?></td>
															<td><?=$res_type->fields['EMAIL']?></td>
															<td><?=$res_type->fields['PHONE']?></td>
															<td>
																<a href="contact?id=<?=$res_type->fields['PK_SCHOOL_CONTACT']?>&s_id=<?=$_GET['id']?>" title="Edit" class="btn btn-secondary btn-circle"><i class="far fa-edit"></i> </a>
																<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_SCHOOL_CONTACT']?>','contact')" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i> </a>
															</td>
														</tr>
													<?	$res_type->MoveNext();
													} ?>
												</tbody>
											</table>
										</div>
									</div>
									
									<!-- Ticket # 1295 -->
									<div class="tab-pane <?=$customQueriesTab?>" id="customQueriesTab" role="tabpanel">
										<div class="row">
											<div class="col-md-1 align-self-center text-right"></div>
											<div class="col-md-9 align-self-center">
												<div class="row form-group" style="margin-left: 0;" >
													<div class="custom-control custom-radio col-md-2">
														<input type="radio" id="CUSTOM_QUERY_1" name="CUSTOM_QUERY" value="1" checked class="custom-control-input" onchange="show_custom_query()" >
														<label class="custom-control-label" for="CUSTOM_QUERY_1" >Custom Query</label>
													</div>
													<div class="custom-control custom-radio col-md-3">
														<input type="radio" id="CUSTOM_QUERY_2" name="CUSTOM_QUERY" value="2" class="custom-control-input" onchange="show_custom_query()" >
														<label class="custom-control-label" for="CUSTOM_QUERY_2" >Custom Query Account</label>
													</div>
												</div>
											</div>  
											<div class="col-md-2 align-self-center text-right">
												<!--<div class="d-flex justify-content-end align-items-center">
													<a href="custom_query?s_id=<?=$_GET['id']?>" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> Create New</a>&nbsp;&nbsp;
												</div>-->
											</div>
										</div>
										<div class="table-responsive p-20" id="CUSTOM_QUERY_1_DIV" >
											<div class="row">
												<div class="col-md-9 align-self-center"></div>
												<div class="col-md-3 align-self-center">
													<input id="NOTES_CUSTOM_QUERY" type="text" class="form-control" placeholder="Search" onkeypress="search_cq(event)" >
												</div>
											</div>
											<div id="custom_query_div" >
												<? require_once("ajax_custom_query.php"); ?>
											</div>
										</div>
										
										<div class="table-responsive p-20" id="CUSTOM_QUERY_2_DIV" style="display:none" >
											<table class="table table-hover">
												<thead>
													<tr>
														<th>#</th>
														<th>PK Custom Query Account</th>
														<th>Name</th>
														<!--<th>Options</th>-->
													</tr>
												</thead>
												<tbody>
													<? $res_type = $db->Execute("SELECT PK_CUSTOM_QUERY_ACCOUNT,CUSTOM_NAME  FROM M_CUSTOM_QUERY_ACCOUNT, M_CUSTOM_QUERY WHERE M_CUSTOM_QUERY_ACCOUNT.PK_CUSTOM_QUERY = M_CUSTOM_QUERY.PK_CUSTOM_QUERY AND PK_ACCOUNT = '$_GET[id]' ORDER BY CUSTOM_NAME ASC ");
													$i = 0;
													while (!$res_type->EOF) { 
														$i++; ?>
														<tr>
															<td><?=$i?></td>
															<td><?=$res_type->fields['PK_CUSTOM_QUERY_ACCOUNT']?></td>
															<td><?=$res_type->fields['CUSTOM_NAME']?></td>
															<!--<td>
																<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_CUSTOM_QUERY_ACCOUNT']?>','custom_query_account')" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i> </a>
															</td>-->
														</tr>
													<?	$res_type->MoveNext();
													} ?>
												</tbody>
											</table>
										</div>
									</div>
									<!-- Ticket # 1295 -->
									
									<? } ?>
								</div>
							</form>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
		
		<div class="modal" id="APIModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group" ><?=GENERATE_API_KEY_CONFIRM_MSG?></div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_generate_key(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_generate_key(0)" ><?=NO?></button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1">Delete Confirmation</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group" id="delete_message" ></div>
						<input type="hidden" id="DELETE_ID" value="0" />
						<input type="hidden" id="DELETE_TYPE" value="0" />
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
	
	<script type="text/javascript">
	<? if($_GET['tab'] != '') { ?>
		current_tab = '<?=$_GET['tab']?>';
	<? } else { ?>
		current_tab = 'homeTab';
	<? } ?>
	jQuery(document).ready(function($) {
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			current_tab = $(e.target).attr("href") // activated tab
			//alert(current_tab)
		});
		
		<? if($_GET['id'] != ''){ ?>
			//get_country(<?=$PK_STATES?>,'PK_COUNTRY')
		<? } 
		if($from_count == 0){ ?>
			add_from_no()
		<? } ?>
	});
	</script>
	
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
		
		function validate_form(val){
			document.getElementById('current_tab').value   = current_tab;
			document.getElementById("SAVE_CONTINUE").value = val;
			
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true)
				document.form1.submit();
		}
		
		function get_country(val,id){
			jQuery(document).ready(function($) { 
				var data  = 'state='+val+'&id='+id;
				var value = $.ajax({
					url: "ajax_get_country_from_state",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data);
						document.getElementById(id+'_LABEL').classList.add("focused");
						document.getElementById(id).innerHTML = data;
					}		
				}).responseText;
			});
		}
		
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				if(type == 'logo')
					document.getElementById('delete_message').innerHTML = 'Are you sure you want to Delete this Logo?';
				else if(type == 'campus')
					document.getElementById('delete_message').innerHTML = 'Are you sure you want to Delete this Campus?';
				else if(type == 'user')
					document.getElementById('delete_message').innerHTML = 'Are you sure you want to Delete this User?';	
				else if(type == 'contact')
					document.getElementById('delete_message').innerHTML = 'Are you sure you want to Delete this Contact?';	
				else if(type == 'from_no')
					document.getElementById('delete_message').innerHTML = 'Are you sure you want to Delete this From #?';	
				else if(type == 'custom_query') /* Ticket # 1295 */
					document.getElementById('delete_message').innerHTML = 'Are you sure you want to Delete this Custom Query?';	
				else if(type == 'custom_query_account')
					document.getElementById('delete_message').innerHTML = 'Are you sure you want to Delete this Custom Query From THis Account #?';	/* Ticket # 1295 */
					
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'logo')
						window.location.href = 'accounts?act=logo&id=<?=$_GET['id']?>';
					else if($("#DELETE_TYPE").val() == 'campus')
						window.location.href = 'accounts?act=camp_del&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
					else if($("#DELETE_TYPE").val() == 'user')
						window.location.href = 'accounts?act=user_del&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
					else if($("#DELETE_TYPE").val() == 'contact')
						window.location.href = 'accounts?act=user_cont&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
					else if($("#DELETE_TYPE").val() == 'from_no') {
						var id = $("#DELETE_ID").val();
						if(document.getElementById('PK_TEXT_SETTINGS_'+id).value == '') {
							$("#PK_TEXT_SETTINGS_DIV_"+id).remove();
							$("#deleteModal").modal("hide");
						} else {
							window.location.href = 'accounts?act=from_no&id=<?=$_GET['id']?>&iid='+document.getElementById('PK_TEXT_SETTINGS_'+id).value;
						}
					} else if($("#DELETE_TYPE").val() == 'custom_query') /* Ticket # 1295 */
						window.location.href = 'accounts?act=custom_query&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
					else if($("#DELETE_TYPE").val() == 'custom_query_account')
						window.location.href = 'accounts?act=custom_query_account&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();	/* Ticket # 1295 */
				} else
					$("#deleteModal").modal("hide");
			});
		}
		function set_web_default(val){
			if(val == 1) {
				if(document.getElementById('WEBSITE').value == '')
					document.getElementById('WEBSITE').value = 'https://';
			} else {
				if(document.getElementById('WEBSITE').value == 'https://')
					document.getElementById('WEBSITE').value = '';
			}
		}
		function check_duplicate_stud_code(){
			jQuery(document).ready(function($) {
				var STUD_CODE = document.getElementById('STUD_CODE').value
				if (STUD_CODE != ""){
					var data="STUD_CODE="+STUD_CODE+'&type=STUD_CODE&id=<?=$_GET['id']?>';
					$.ajax({
						type: "POST",
						url:"../check_duplicate",
						data:data,
						success: function(result1){ 
							if(result1 == 1){
								document.getElementById('STUD_CODE_div').style.display = "block";
								document.getElementById('STUD_CODE').value = "";
								return false;
							}else{
								document.getElementById('STUD_CODE_div').style.display = "none";
							}
						}
					});
				}
			});	
		}
		function check_duplicate_stud_no(){
			jQuery(document).ready(function($) {
				var STUD_NO = document.getElementById('STUD_NO').value
				if (STUD_NO != ""){
					var data="STUD_NO="+STUD_NO+'&type=STUD_NO&id=<?=$_GET['id']?>&k=<?=$_SESSION['PK_ACCOUNT']?>';
					$.ajax({
						type: "POST",
						url:"../check_duplicate",
						data:data,
						success: function(result1){ 
							if(result1 == 1){
								document.getElementById('STUD_NO_div').style.display = "block";
								document.getElementById('STUD_NO').value = "";
								return false;
							}else{
								document.getElementById('STUD_NO_div').style.display = "none";
							}
						}
					});
				}
			});	
		}
		function generate_key(val) {
			jQuery(document).ready(function($) { 
				$("#APIModal").modal()
			});
		}
		
		function conf_generate_key(val) {
			jQuery(document).ready(function($) { 
				if(val == 1) {
					var data  = 'id=<?=$_GET['id']?>';
					var value = $.ajax({
						url: "generate_key",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('API_KEY').value = data;
						}		
					}).responseText;
				}
				$("#APIModal").modal("hide");
			});
		}
		
		var from_count = '<?=$from_count?>';
		function add_from_no(){
			jQuery(document).ready(function($) { 
				var data  = 'from_count='+from_count;
				var value = $.ajax({
					url: "add_from_no",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						from_count++;
						$("#FROM_NO_DIV").append(data);
						
						$('.floating-labels .form-control').on('focus blur', function (e) {
							$(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
						}).trigger('blur');
					}		
				}).responseText;
			});
		}
		function show_ethink_fields(){
			var str = '';
			if(document.getElementById('ENABLE_ETHINK').checked == true)
				str = 'block';
			else
				str = 'none';

			var item = document.getElementsByClassName('ethink')
			for (var i = 0; i < item.length; i++) {
				item[i].setAttribute('style', 'display:'+str+' !important');
			}
		}
		function show_canvas_fields(){
			var str = '';
			if(document.getElementById('ENABLE_CANVAS').checked == true)
				str = 'block';
			else
				str = 'none';

			var item = document.getElementsByClassName('canvas')
			for (var i = 0; i < item.length; i++) {
				item[i].setAttribute('style', 'display:'+str+' !important');
			}
		}
		
		/* Ticket # 1870 */
		function show_lsq_fields(){
			var str = '';
			if(document.getElementById('ENABLE_LSQ').checked == true)
				str = 'block';
			else
				str = 'none';

			var item = document.getElementsByClassName('lsq')
			for (var i = 0; i < item.length; i++) {
				item[i].setAttribute('style', 'display:'+str+' !important');
			}
		}
		/* Ticket # 1870 */
		
		/* Ticket # 1295 */
		function show_custom_query(){
			if(document.getElementById('CUSTOM_QUERY_1').checked == true){
				document.getElementById('CUSTOM_QUERY_1_DIV').style.display = 'block'
				document.getElementById('CUSTOM_QUERY_2_DIV').style.display = 'none'
			} else {
				document.getElementById('CUSTOM_QUERY_1_DIV').style.display = 'none'
				document.getElementById('CUSTOM_QUERY_2_DIV').style.display = 'block'
			}
		}
		
		function search_cq(e){
			//console.log(e)
			if (e.keyCode == 13 || e == '') {
				jQuery(document).ready(function($) { 
					var search 	 = $("#NOTES_CUSTOM_QUERY").val();
		
					var data  = 'search='+search;
					var value = $.ajax({
						url: "ajax_custom_query",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							document.getElementById('custom_query_div').innerHTML = data;
						}		
					}).responseText;
				});
			}
		}
		/* Ticket # 1295 */
	</script>

</body>

</html>
