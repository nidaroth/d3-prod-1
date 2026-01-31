<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/student.php");
require_once("check_access.php");

$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

if ($_GET['t'] == 1 && $ADMISSION_ACCESS == 0) {
	header("location:../index");
	exit;
} else if ($_GET['t'] == 2 && $REGISTRAR_ACCESS == 0) {
	header("location:../index");
	exit;
} else if ($_GET['t'] == 3 && $FINANCE_ACCESS == 0) {
	header("location:../index");
	exit;
} else if ($_GET['t'] == 5 && $ACCOUNTING_ACCESS == 0) {
	header("location:../index");
	exit;
} else if ($_GET['t'] == 6 && $PLACEMENT_ACCESS == 0) {
	header("location:../index");
	exit;
}



if ($_SESSION['PK_ACCOUNT'] == 67) {
	// ini_set('display_errors', 1);
	// ini_set('display_startup_errors', 1);
	// error_reporting(E_ALL);
	// // echo 'here';exit;
	// ini_set('memory_limit', '1024M');
}


$res_pay = $db->Execute("select ENABLE_DIAMOND_PAY from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

$page = isset($_POST['page']) ? intval($_POST['page']) : $_SESSION['PAGE'];
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : $_SESSION['rows'];
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : $_SESSION['SORT_FIELD'];
$order = isset($_POST['order']) ? strval($_POST['order']) : $_SESSION['SORT_ORDER'];

$_SESSION['rows'] 		= $rows;
$_SESSION['PAGE'] 		= $page;
$_SESSION['SORT_FIELD'] = $sort;
$_SESSION['SORT_ORDER'] = $order;

$SEARCH 				= isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : $_SESSION['SRC_SEARCH'];
$SHOW_UNASSIGNED		= isset($_REQUEST['SHOW_UNASSIGNED']) ? mysql_real_escape_string($_REQUEST['SHOW_UNASSIGNED']) : $_SESSION['SRC_SHOW_UNASSIGNED'];
$SHOW_ARCHIVED			= isset($_REQUEST['SHOW_ARCHIVED']) ? mysql_real_escape_string($_REQUEST['SHOW_ARCHIVED']) : $_SESSION['SRC_SHOW_ARCHIVED'];
$SHOW_ENROLLED_ONLY		= isset($_REQUEST['SHOW_ENROLLED_ONLY']) ? mysql_real_escape_string($_REQUEST['SHOW_ENROLLED_ONLY']) : $_SESSION['SRC_SHOW_ENROLLED_ONLY'];
$SEARCH_PAST_STUDENT	= $_SESSION['SRC_SEARCH_PAST_STUDENT'];
$SHOW_LEAD				= $_SESSION['SRC_SHOW_LEAD'];
$LEAD_START_DATE		= isset($_REQUEST['LEAD_START_DATE']) ? mysql_real_escape_string($_REQUEST['LEAD_START_DATE']) : $_SESSION['SRC_LEAD_START_DATE'];
$LEAD_END_DATE			= isset($_REQUEST['LEAD_END_DATE']) ? mysql_real_escape_string($_REQUEST['LEAD_END_DATE']) : $_SESSION['SRC_LEAD_END_DATE'];
$LDA_START_DATE			= isset($_REQUEST['LDA_START_DATE']) ? mysql_real_escape_string($_REQUEST['LDA_START_DATE']) : $_SESSION['SRC_LDA_START_DATE'];
$LDA_END_DATE			= isset($_REQUEST['LDA_END_DATE']) ? mysql_real_escape_string($_REQUEST['LDA_END_DATE']) : $_SESSION['SRC_LDA_END_DATE'];

$SHOW_MULTIPLE_ENROLLMENT	= isset($_REQUEST['SHOW_MULTIPLE_ENROLLMENT']) ? mysql_real_escape_string($_REQUEST['SHOW_MULTIPLE_ENROLLMENT']) : $_SESSION['SRC_SHOW_MULTIPLE_ENROLLMENT'];
$EXPECTED_GRAD_START_DATE 	= isset($_REQUEST['EXPECTED_GRAD_START_DATE']) ? ($_REQUEST['EXPECTED_GRAD_START_DATE']) : $_SESSION['SRC_EXPECTED_GRAD_START_DATE'];
$EXPECTED_GRAD_END_DATE 	= isset($_REQUEST['EXPECTED_GRAD_END_DATE']) ? ($_REQUEST['EXPECTED_GRAD_END_DATE']) : $_SESSION['SRC_EXPECTED_GRAD_END_DATE'];
$GRAD_START_DATE 			= isset($_REQUEST['GRAD_START_DATE']) ? ($_REQUEST['GRAD_START_DATE']) : $_SESSION['SRC_GRAD_START_DATE'];
$GRAD_END_DATE 			 	= isset($_REQUEST['GRAD_END_DATE']) ? ($_REQUEST['GRAD_END_DATE']) : $_SESSION['SRC_GRAD_END_DATE'];
$SSN						= isset($_REQUEST['SSN']) ? ($_REQUEST['SSN']) : $_SESSION['SRC_SSN'];

/* Ticket # 1650 */
$DETERMINATION_START_DATE		= isset($_REQUEST['DETERMINATION_START_DATE']) ? mysql_real_escape_string($_REQUEST['DETERMINATION_START_DATE']) : $_SESSION['SRC_DETERMINATION_START_DATE'];
$DETERMINATION_END_DATE			= isset($_REQUEST['DETERMINATION_END_DATE']) ? mysql_real_escape_string($_REQUEST['DETERMINATION_END_DATE']) : $_SESSION['SRC_DETERMINATION_END_DATE'];
$DROP_START_DATE				= isset($_REQUEST['DROP_START_DATE']) ? mysql_real_escape_string($_REQUEST['DROP_START_DATE']) : $_SESSION['SRC_DROP_START_DATE'];
$DROP_END_DATE					= isset($_REQUEST['DROP_END_DATE']) ? mysql_real_escape_string($_REQUEST['DROP_END_DATE']) : $_SESSION['SRC_DROP_END_DATE'];
/* Ticket # 1650 */

$offset = ($page - 1) * $rows;

$result = array();
$where = " S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER ";

$table = "";
/*if($_SESSION['PK_ROLES'] == 3 || $_SESSION['PK_ROLES'] == 4 || $_SESSION['PK_ROLES'] == 5) {
	$table  = ", S_STUDENT_CAMPUS";
	$where .= " AND S_STUDENT_CAMPUS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_CAMPUS.PK_CAMPUS = '$_SESSION[PK_CAMPUS]' ";
}*/

if ($LEAD_START_DATE != '') {
	$_SESSION['SRC_LEAD_START_DATE'] = $LEAD_START_DATE;
	$LEAD_START_DATE = date("Y-m-d", strtotime($LEAD_START_DATE));
} else {
	$_SESSION['SRC_LEAD_START_DATE'] = '';
}

if ($LEAD_END_DATE != '') {
	$_SESSION['SRC_LEAD_END_DATE'] = $LEAD_END_DATE;
	$LEAD_END_DATE = date("Y-m-d", strtotime($LEAD_END_DATE));
} else {
	$_SESSION['SRC_LEAD_END_DATE'] = '';
}

if ($LEAD_START_DATE != '' && $LEAD_END_DATE != '') {
	$where .= " AND ENTRY_DATE BETWEEN '$LEAD_START_DATE' AND '$LEAD_END_DATE' ";
} else if ($LEAD_START_DATE != '') {
	$where .= " AND ENTRY_DATE >= '$LEAD_START_DATE' ";
} else if ($LEAD_END_DATE != '') {
	$where .= " AND ENTRY_DATE <='$LEAD_END_DATE' ";
}

/* Ticket # 1824 */
$ID			= isset($_REQUEST['ID']) ? mysql_real_escape_string($_REQUEST['ID']) : $_SESSION['SRC_ID'];
$ID_TYPE	= isset($_REQUEST['ID_TYPE']) ? ($_REQUEST['ID_TYPE']) : explode(",", $_SESSION['SRC_ID_TYPE']);

if (!empty($ID_TYPE) && $ID != '') {
	$ID = trim($ID);
	$sub_where = "";

	$_SESSION['SRC_ID']		 = $ID;
	$_SESSION['SRC_ID_TYPE'] = implode(",", $ID_TYPE);
	foreach ($ID_TYPE as $ID_TYPE_1) {
		if ($ID_TYPE_1 == 1) {
			if ($sub_where != '')
				$sub_where .= " OR ";
			$sub_where .= " ADM_USER_ID like '%$ID%' ";
		} else if ($ID_TYPE_1 == 2) {
			if ($sub_where != '')
				$sub_where .= " OR ";
			$sub_where .= " BADGE_ID like '%$ID%' ";
		} else if ($ID_TYPE_1 == 3) {
			if ($sub_where != '')
				$sub_where .= " OR ";
			$sub_where .= " OLD_DSIS_LEAD_ID like '%$ID%' ";
		} else if ($ID_TYPE_1 == 4) {
			if ($sub_where != '')
				$sub_where .= " OR ";
			$sub_where .= " OLD_DSIS_STU_NO like '%$ID%' ";
		} else if ($ID_TYPE_1 == 5) {
			if ($sub_where != '')
				$sub_where .= " OR ";
			$sub_where .= " STUDENT_ID like '%$ID%' ";
		} else if ($ID_TYPE_1 == 6) {
			if ($sub_where != '')
				$sub_where .= " OR ";

			$SSN = $ID;
			$SSN = preg_replace('/[^0-9]/', '', $SSN);

			if (strlen($SSN) == 4) {
				$SSN  = 'xxxxx' . $SSN;
				$SSN1 = $SSN;

				$SSN1 = $SSN1[0] . $SSN1[1] . $SSN1[2] . '-' . $SSN1[3] . $SSN1[4] . '-' . $SSN1[5] . $SSN1[6] . $SSN1[7] . $SSN1[8];
				$SSN1 = my_encrypt('', $SSN1);
				$SSN1 = substr($SSN1, 8);

				$sub_where .= " S_STUDENT_MASTER.SSN like '%$SSN1' ";
			} else {
				$SSN1 = $SSN;
				$SSN1 = $SSN1[0] . $SSN1[1] . $SSN1[2] . '-' . $SSN1[3] . $SSN1[4] . '-' . $SSN1[5] . $SSN1[6] . $SSN1[7] . $SSN1[8];
				$SSN1 = my_encrypt('', $SSN1);

				$sub_where .= " S_STUDENT_MASTER.SSN = '$SSN1' ";
			}
		}
	}
	if ($sub_where != '')
		$where .= " AND (" . $sub_where . ") ";
}
//echo $where;exit;
/* Ticket # 1824 */

if ($LDA_START_DATE != '') {
	$_SESSION['SRC_LDA_START_DATE'] = $LDA_START_DATE;
	$LDA_START_DATE = date("Y-m-d", strtotime($LDA_START_DATE));
} else {
	$_SESSION['SRC_LDA_START_DATE'] = '';
}

if ($LDA_END_DATE != '') {
	$_SESSION['SRC_LDA_END_DATE'] = $LDA_END_DATE;
	$LDA_END_DATE = date("Y-m-d", strtotime($LDA_END_DATE));
} else {
	$_SESSION['SRC_LDA_END_DATE'] = '';
}

if ($LDA_START_DATE != '' && $LDA_END_DATE != '') {
	$where .= " AND LDA BETWEEN '$LDA_START_DATE' AND '$LDA_END_DATE' ";
} else if ($LDA_START_DATE != '') {
	$where .= " AND LDA >= '$LDA_START_DATE' ";
} else if ($LDA_END_DATE != '') {
	$where .= " AND LDA <='$LDA_END_DATE' ";
}

if ($EXPECTED_GRAD_START_DATE != '') {
	$_SESSION['SRC_EXPECTED_GRAD_START_DATE'] = $EXPECTED_GRAD_START_DATE;
	$EXPECTED_GRAD_START_DATE = date("Y-m-d", strtotime($EXPECTED_GRAD_START_DATE));
} else {
	$_SESSION['SRC_EXPECTED_GRAD_START_DATE'] = '';
}

if ($EXPECTED_GRAD_END_DATE != '') {
	$_SESSION['SRC_EXPECTED_GRAD_END_DATE'] = $EXPECTED_GRAD_END_DATE;
	$EXPECTED_GRAD_END_DATE = date("Y-m-d", strtotime($EXPECTED_GRAD_END_DATE));
} else {
	$_SESSION['SRC_EXPECTED_GRAD_END_DATE'] = '';
}

if ($EXPECTED_GRAD_START_DATE != '' && $EXPECTED_GRAD_END_DATE != '') {
	$where .= " AND EXPECTED_GRAD_DATE BETWEEN '$EXPECTED_GRAD_START_DATE' AND '$EXPECTED_GRAD_END_DATE' ";
} else if ($EXPECTED_GRAD_START_DATE != '') {
	$where .= " AND EXPECTED_GRAD_DATE >= '$EXPECTED_GRAD_START_DATE' ";
} else if ($EXPECTED_GRAD_END_DATE != '') {
	$where .= " AND EXPECTED_GRAD_DATE <='$EXPECTED_GRAD_END_DATE' ";
}

if ($GRAD_START_DATE != '') {
	$_SESSION['SRC_GRAD_START_DATE'] = $GRAD_START_DATE;
	$GRAD_START_DATE = date("Y-m-d", strtotime($GRAD_START_DATE));
} else {
	$_SESSION['SRC_GRAD_START_DATE'] = '';
}

if ($GRAD_END_DATE != '') {
	$_SESSION['SRC_GRAD_END_DATE'] = $GRAD_END_DATE;
	$GRAD_END_DATE = date("Y-m-d", strtotime($GRAD_END_DATE));
} else {
	$_SESSION['SRC_GRAD_END_DATE'] = '';
}

if ($GRAD_START_DATE != '' && $GRAD_END_DATE != '') {
	$where .= " AND GRADE_DATE BETWEEN '$GRAD_START_DATE' AND '$GRAD_END_DATE' ";
} else if ($GRAD_START_DATE != '') {
	$where .= " AND GRADE_DATE >= '$GRAD_START_DATE' ";
} else if ($GRAD_END_DATE != '') {
	$where .= " AND GRADE_DATE <='$GRAD_END_DATE' ";
}

/* DIAM - 108, Added date formate for filteration */
if ($DETERMINATION_START_DATE != '' && $DETERMINATION_END_DATE != '') {
	$DETERMINATION_START_DATE = date("Y-m-d", strtotime($DETERMINATION_START_DATE));
	$DETERMINATION_END_DATE   = date("Y-m-d", strtotime($DETERMINATION_END_DATE));
	$where .= " AND DETERMINATION_DATE BETWEEN '$DETERMINATION_START_DATE' AND '$DETERMINATION_END_DATE' ";
} else if ($DETERMINATION_START_DATE != '') {
	$DETERMINATION_START_DATE = date("Y-m-d", strtotime($DETERMINATION_START_DATE));
	$where .= " AND DETERMINATION_DATE >= '$DETERMINATION_START_DATE' ";
} else if ($DETERMINATION_END_DATE != '') {
	$DETERMINATION_END_DATE   = date("Y-m-d", strtotime($DETERMINATION_END_DATE));
	$where .= " AND DETERMINATION_DATE <='$DETERMINATION_END_DATE' ";
}

if ($DROP_START_DATE != '' && $DROP_END_DATE != '') {
	$DROP_START_DATE  = date("Y-m-d", strtotime($DROP_START_DATE));
	$DROP_END_DATE    = date("Y-m-d", strtotime($DROP_END_DATE));
	$where .= " AND DROP_DATE BETWEEN '$DROP_START_DATE' AND '$DROP_END_DATE' ";
} else if ($DROP_START_DATE != '') {
	$DROP_START_DATE  = date("Y-m-d", strtotime($DROP_START_DATE));
	$where .= " AND DROP_DATE >= '$DROP_START_DATE' ";
} else if ($DROP_END_DATE != '') {
	$DROP_END_DATE    = date("Y-m-d", strtotime($DROP_END_DATE));
	$where .= " AND DROP_DATE <='$DROP_END_DATE' ";
}
/* End DIAM - 108 */

if ($SEARCH != '') {
	$sub = "";
	$MOBILE_NO = preg_replace('/[^0-9]/', '', $SEARCH);
	if ($MOBILE_NO != '') {
		//$sub = " OR (REPLACE(REPLACE(REPLACE(REPLACE(CELL_PHONE, '(', ''), ')', ''), '-', ''),' ','') LIKE '$MOBILE_NO') OR (REPLACE(REPLACE(REPLACE(REPLACE(HOME_PHONE, '(', ''), ')', ''), '-', ''),' ','') LIKE '$MOBILE_NO') OR (REPLACE(REPLACE(REPLACE(REPLACE(WORK_PHONE, '(', ''), ')', ''), '-', ''),' ','') LIKE '$MOBILE_NO') OR (REPLACE(REPLACE(REPLACE(REPLACE(WORK_PHONE, '(', ''), ')', ''), '-', ''),' ','') LIKE '$OTHER_PHONE') ";
		$sub = " OR (REPLACE(REPLACE(REPLACE(REPLACE(CELL_PHONE, '(', ''), ')', ''), '-', ''),' ','') = '$MOBILE_NO') OR (REPLACE(REPLACE(REPLACE(REPLACE(HOME_PHONE, '(', ''), ')', ''), '-', ''),' ','') = '$MOBILE_NO') OR (REPLACE(REPLACE(REPLACE(REPLACE(WORK_PHONE, '(', ''), ')', ''), '-', ''),' ','') = '$MOBILE_NO') ";
	}

	$where .= " AND (STUDENT_ID like '%$SEARCH%' OR CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) like '%$SEARCH%' $sub OR S_STUDENT_CONTACT.EMAIL like '%$SEARCH%' OR S_STUDENT_CONTACT.EMAIL_OTHER like '%$SEARCH%'  )";

	$_SESSION['SRC_SEARCH'] = $SEARCH;
} else {
	$_SESSION['SRC_SEARCH'] = '';
}

if ($_SESSION['SRC_PK_STUDENT_STATUS'] != '') {
	$where .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($_SESSION[SRC_PK_STUDENT_STATUS]) ";
}

if ($_SESSION['SRC_PK_LEAD_SOURCE'] != '') { // Ticket #1013
	$where .= " AND S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE IN ($_SESSION[SRC_PK_LEAD_SOURCE]) ";
}

if ($_SESSION['SRC_PK_TERM_MASTER'] != '') { // Ticket #1013
	$where .= " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER IN ($_SESSION[SRC_PK_TERM_MASTER]) ";
}

if ($_SESSION['SRC_PK_CAMPUS_PROGRAM'] != '') {
	$where .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN ($_SESSION[SRC_PK_CAMPUS_PROGRAM]) ";
}

if ($_SESSION['SRC_PK_REPRESENTATIVE'] != '') {
	$where .= " AND S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE IN ($_SESSION[SRC_PK_REPRESENTATIVE]) ";
}

if ($_SESSION['SRC_PK_CAMPUS'] != '' || $_SESSION['PK_ROLES'] == 3) {

	if ($_SESSION['SRC_PK_CAMPUS'] != '') {
		$where .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($_SESSION[SRC_PK_CAMPUS]) ";
	} else {
		$where .= " AND (S_STUDENT_CAMPUS.PK_CAMPUS IN ($_SESSION[PK_CAMPUS],0)  ) ";
		$_SESSION['SRC_PK_CAMPUS'] = '';
	}
}

if ($_SESSION['SRC_PK_FUNDING'] != '') { // Ticket #1013
	$where .= " AND S_STUDENT_ENROLLMENT.PK_FUNDING IN ($_SESSION[SRC_PK_FUNDING]) ";
}

if ($_SESSION['SRC_PK_PLACEMENT_STATUS'] != '') {
	$where .= " AND S_STUDENT_ENROLLMENT.PK_PLACEMENT_STATUS IN ($_SESSION[SRC_PK_PLACEMENT_STATUS]) ";
}

/* Ticket # 1650 */
if ($_SESSION['SRC_PK_SESSION'] != '') {
	$where .= " AND S_STUDENT_ENROLLMENT.PK_SESSION IN ($_SESSION[SRC_PK_SESSION]) ";
}

if ($_SESSION['SRC_PK_STUDENT_GROUP'] != '') {
	$where .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP IN ($_SESSION[SRC_PK_STUDENT_GROUP]) ";
}
/* Ticket # 1650 */

if ($_SESSION['SRC_EMPLOYED'] != '') {
	$EMPLOYED_ARR = explode(",", $_SESSION['SRC_EMPLOYED']);

	$str1 = "";
	$str2 = "";
	foreach ($EMPLOYED_ARR as $EMPLOYED_1) {
		if ($EMPLOYED_1 == 1 || $EMPLOYED_1 == 2) {
			if ($str1 != '')
				$str1 .= ',';

			if ($EMPLOYED_1 == 1)
				$str1 .= "1";
			else if ($EMPLOYED_1 == 2)
				$str1 .= "0";
		} else if ($EMPLOYED_1 == 3) {
			$str2 = " S_STUDENT_ENROLLMENT.PK_PLACEMENT_STATUS = 0 ";
		}
	}

	if ($str1 != "" && $str2 != "")
		$where .= " AND (EMPLOYED IN ($str1) OR $str2) ";
	else if ($str1 != "")
		$where .= " AND EMPLOYED IN ($str1) ";
	else if ($str2 != "")
		$where .= " AND $str2 ";
}

if ($SHOW_UNASSIGNED == "true") {
	$where .= " AND PK_REPRESENTATIVE = 0 ";
	$_SESSION['SRC_SHOW_UNASSIGNED'] = $SHOW_UNASSIGNED;
} else
	$_SESSION['SRC_SHOW_UNASSIGNED'] = '';

if ($SHOW_ARCHIVED == "true") {
	$where .= " AND S_STUDENT_MASTER.ARCHIVED = 1 ";
	$_SESSION['SRC_SHOW_ARCHIVED'] = $SHOW_ARCHIVED;
} else {
	$where .= " AND S_STUDENT_MASTER.ARCHIVED = 0 ";
	$_SESSION['SRC_SHOW_ARCHIVED'] = '';
}

$qs = "";
$group_by = " GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ";
if ($SHOW_MULTIPLE_ENROLLMENT == "true") {
	$qs = "&tab=otherTab";
	$group_by = " GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
	$_SESSION['SRC_SHOW_MULTIPLE_ENROLLMENT'] = $SHOW_MULTIPLE_ENROLLMENT;
} else {
	if ($SHOW_ENROLLED_ONLY != "true")
		$where .= " AND IS_ACTIVE_ENROLLMENT = 1 ";
	$_SESSION['SRC_SHOW_MULTIPLE_ENROLLMENT'] = '';
}

if ($SHOW_ENROLLED_ONLY == "true")
	$_SESSION['SRC_SHOW_ENROLLED_ONLY'] = $SHOW_ENROLLED_ONLY;
else
	$_SESSION['SRC_SHOW_ENROLLED_ONLY'] = '';


if ($_GET['t'] == 1) {
	//Admissions should show admissions that have Yes in the admissions columns
	$sts = "";
	if ($SEARCH_PAST_STUDENT == "true") {
		/*$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 ");
		while (!$res_type->EOF) {
			if($sts != '')
				$sts .= ',';
				
			$sts .= $res_type->fields['PK_STUDENT_STATUS'];
			
			$res_type->MoveNext();
		}*/
		$where .= " AND ADMISSIONS = 0 ";
	} else {
		/*$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 1 ");
		while (!$res_type->EOF) {
			if($sts != '')
				$sts .= ',';
				
			$sts .= $res_type->fields['PK_STUDENT_STATUS'];
			
			$res_type->MoveNext();
		}*/
		$where .= " AND ADMISSIONS = 1 ";
	}

	//$where .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($sts) ";
} else if ($_GET['t'] == 2 || $_GET['t'] == 3 || $_GET['t'] == 5 || $_GET['t'] == 6) {
	//Registrar, Finicial Aid, Accounting and Placement to should show admissions that have NO in the admissions columns

	if ($SHOW_ENROLLED_ONLY == "true") {
		//$sts_cond = " AND PK_STUDENT_STATUS_MASTER = 5 ";
		$where .= " AND PK_STUDENT_STATUS_MASTER = 5 AND ADMISSIONS = 1 ";
	} else if ($SHOW_LEAD == "true") {
		//$sts_cond = " AND ADMISSIONS = 1 ";
		$where .= " AND ADMISSIONS = 1 ";
	} else {
		//$sts_cond = " AND ADMISSIONS = 0 ";
		$where .= " AND ADMISSIONS = 0 ";
	}

	/*$sts = "";
	$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $sts_cond ");
	while (!$res_type->EOF) {
		if($sts != '')
			$sts .= ',';
			
		$sts .= $res_type->fields['PK_STUDENT_STATUS'];
		
		$res_type->MoveNext();
	}
	$where .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($sts) ";*/
} /* else if($_GET['t'] == 3){
	$sts = "";
	$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_STATUS_MASTER IN (5,13) ");
	while (!$res_type->EOF) {
		if($sts != '')
			$sts .= ',';
			
		$sts .= $res_type->fields['PK_STUDENT_STATUS'];
		
		$res_type->MoveNext();
	}
	
	$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_STATUS_MASTER = 1");
	$where .= " AND (S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN ($sts) OR (S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS = '".$res_type->fields['PK_STUDENT_STATUS']."' AND PK_CAMPUS_PROGRAM > 0 ) ) ";
} */

// DIAM-1302
$param = "";
$JOIN = "";
if ($_GET['t'] == 3 || $_GET['t'] == 5) {
	// if($SHOW_MULTIPLE_ENROLLMENT == "true"){
	// 	$where .= " AND S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_MASTER.PK_STUDENT_ENROLLMENT ";
	// }
	// else{
	// 	$where .= "";
	// }

	// $where .= " AND ( PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 )";

	// $res_ledger = $db->Execute("select SUM(CREDIT) as CREDIT, SUM(DEBIT) as DEBIT from S_STUDENT_LEDGER WHERE PK_STUDENT_MASTER = '$row[PK_STUDENT_MASTER]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) $led_cond ");
	// $row['BALANCE'] = $res_ledger->fields['DEBIT'] - $res_ledger->fields['CREDIT'];
	$param    = ", (SUM(S_STUDENT_LEDGER.CREDIT) - SUM(S_STUDENT_LEDGER.DEBIT)) AS BALANCE ";
	$JOIN     = " LEFT JOIN S_STUDENT_LEDGER ON S_STUDENT_LEDGER.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER";
}
// End DIAM-1302

$group_by = " GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
$_SESSION['WHERE'] 	  = $where;
$_SESSION['GROUP_BY'] = $group_by;

$fields 	= "";
$left_join 	= "";

$count_query = "SELECT DISTINCT(S_STUDENT_MASTER.PK_STUDENT_MASTER) 
FROM 
S_STUDENT_MASTER 
LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
$JOIN
AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 
$table 
, S_STUDENT_ACADEMICS
, S_STUDENT_ENROLLMENT 
$addTable
LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION 
LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
LEFT JOIN M_PLACEMENT_STATUS ON M_PLACEMENT_STATUS.PK_PLACEMENT_STATUS = S_STUDENT_ENROLLMENT.PK_PLACEMENT_STATUS 
LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE
WHERE " . $where . " " . $group_by;

//echo $count_query;exit;

$rs = mysql_query($count_query) or die(mysql_error());
$result["total"] = mysql_num_rows($rs);

$query = "SELECT DISTINCT(S_STUDENT_MASTER.PK_STUDENT_MASTER) ,PK_REPRESENTATIVE, S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,LEAD_SOURCE,S_STUDENT_MASTER.LOGIN_CREATED, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS NAME,S_STUDENT_MASTER.ARCHIVED,PK_STUDENT_STATUS_MASTER, CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,', ',S_EMPLOYEE_MASTER.FIRST_NAME) AS REPRESENTATIVE , STUDENT_ID, S_TERM_MASTER.BEGIN_DATE,FUNDING,  IF(S_STUDENT_MASTER.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE,STUDENT_STATUS, PLACEMENT_STATUS, IF(EXPECTED_GRAD_DATE = '0000-00-00','', DATE_FORMAT(EXPECTED_GRAD_DATE,'%m/%d/%Y')) AS EXPECTED_GRAD_DATE , IF(GRADE_DATE = '0000-00-00','', DATE_FORMAT(GRADE_DATE,'%m/%d/%Y')) AS GRADE_DATE, M_CAMPUS_PROGRAM.CODE as PROGRAM, CAMPUS_CODE,  IF(ORIGINAL_EXPECTED_GRAD_DATE = '0000-00-00','', DATE_FORMAT(ORIGINAL_EXPECTED_GRAD_DATE,'%m/%d/%Y')) AS ORIGINAL_EXPECTED_GRAD_DATE, IF(EMPLOYED = 1, 'Yes', IF(EMPLOYED = 0, 'No', '')) as EMPLOYED_1, SUBSTRING(SESSION, 1, 1) AS SESSION, STUDENT_GROUP,  ENTRY_DATE, LEAD_SOURCE $param
FROM 
S_STUDENT_MASTER 
LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
$JOIN
AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 
$table 
, S_STUDENT_ACADEMICS
, S_STUDENT_ENROLLMENT 
$addTable
LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION 
LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP  
LEFT JOIN M_LEAD_SOURCE ON M_LEAD_SOURCE.PK_LEAD_SOURCE = S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
LEFT JOIN M_PLACEMENT_STATUS ON M_PLACEMENT_STATUS.PK_PLACEMENT_STATUS = S_STUDENT_ENROLLMENT.PK_PLACEMENT_STATUS 
LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE 
WHERE " . $where . " " . $group_by . " order by $sort $order ";
// echo $query;exit;	
//$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	

$rs = mysql_query($query . " limit $offset,$rows") or die(mysql_error());
// echo $query. " limit $offset,$rows;";exit;
$items = array();
$BALANCE = 0;
while ($row = mysql_fetch_array($rs)) {

	$res_ec = $db->Execute("SELECT count(PK_STUDENT_ENROLLMENT) as EC FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '" . $row['PK_STUDENT_MASTER'] . "' ");
	$row['EC'] = $res_ec->fields['EC'];

	$row['NAME'] = '<a href="student?id=' . $row['PK_STUDENT_MASTER'] . '&eid=' . $row['PK_STUDENT_ENROLLMENT'] . '&t=' . $_GET['t'] . $qs . '" >' . $row['NAME'] . '</a>';

	if ($row['BEGIN_DATE'] != '0000-00-00' && $row['BEGIN_DATE'] != '')
		$row['BEGIN_DATE'] = date("m/d/Y", strtotime($row['BEGIN_DATE']));
	else
		$row['BEGIN_DATE'] = '';

	if ($row['ENTRY_DATE'] != '0000-00-00' && $row['ENTRY_DATE'] != '')
		$row['ENTRY_DATE'] = date("m/d/Y", strtotime($row['ENTRY_DATE']));
	else
		$row['ENTRY_DATE'] = '';

	// DIAM-1302
	// if($_GET['t'] == 3 || $_GET['t'] == 5){

	// 	$BALANCE = $row['DEBIT'] - $row['CREDIT'];

	// 	if($BALANCE < 0)
	// 		$row['BALANCE'] = "<span style='color:red'>$ ".number_format_value_checker($BALANCE, 2)."</span>";
	// 	else
	// 		$row['BALANCE'] = number_format_value_checker($BALANCE, 2);
	// }
	if ($_GET['t'] == 3 || $_GET['t'] == 5) {
		if ($SHOW_MULTIPLE_ENROLLMENT == "true")
			$led_cond = " AND PK_STUDENT_ENROLLMENT = '$row[PK_STUDENT_ENROLLMENT]' ";
		else
			$led_cond = "";
		$res_ledger = $db->Execute("select SUM(CREDIT) as CREDIT, SUM(DEBIT) as DEBIT from S_STUDENT_LEDGER WHERE PK_STUDENT_MASTER = '$row[PK_STUDENT_MASTER]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) $led_cond ");

		$row['BALANCE'] = $res_ledger->fields['DEBIT'] - $res_ledger->fields['CREDIT'];
		if ($row['BALANCE'] < 0)
			$row['BALANCE'] = "<span style='color:red'>$ " . number_format_value_checker($row['BALANCE'], 2) . "</span>";
		else
			$row['BALANCE'] = "$ " . number_format_value_checker($row['BALANCE'], 2);
	}
	// End DIAM-1302

	if (($row['PK_REPRESENTATIVE'] == 0 || $row['PK_REPRESENTATIVE'] == '') && ($ADMISSION_ACCESS == 3 || $FINANCE_ACCESS == 3 || $ACCOUNTING_ACCESS == 3 || $PLACEMENT_ACCESS == 3))
		$row['REPRESENTATIVE'] = '<a href="javascript:void(0);" onclick="assign_rep(\'' . $row['PK_STUDENT_MASTER'] . '-' . $row['PK_STUDENT_ENROLLMENT'] . '\')" title="' . ASSIGN . '" >' . ASSIGN . '</a>';

	if ($_GET['t'] == 2 && $SHOW_ENROLLED_ONLY == "true" && ($ADMISSION_ACCESS == 2 || $ADMISSION_ACCESS == 3 || $REGISTRAR_ACCESS == 2 || $REGISTRAR_ACCESS == 3)) //Ticket # 1928 
		$row['APPROVE'] = '<a href="javascript:void(0);" onclick="approve_stu(\'' . $row['PK_STUDENT_MASTER'] . '-' . $row['PK_STUDENT_ENROLLMENT'] . '\')" title="' . APPROVE . '" >' . APPROVE . '</a>';

	$str  = '&nbsp;<a href="student?id=' . $row['PK_STUDENT_MASTER'] . '&eid=' . $row['PK_STUDENT_ENROLLMENT'] . '&t=' . $_GET['t'] . $qs . '" title="' . EDIT . '" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>&nbsp;';

	$str  .= '<a href="student?id=' . $row['PK_STUDENT_MASTER'] . '&eid=' . $row['PK_STUDENT_ENROLLMENT'] . '&tab=taskTab&t=' . $_GET['t'] . '" title="' . TASK . '" class="btn blue-color btn-circle"><i class="fas fa-clipboard-list"></i> </a>';

	$str  .= '&nbsp;<a target="_blank" href="lead_info_pdf?id=' . $row['PK_STUDENT_MASTER'] . '&eid=' . $row['PK_STUDENT_ENROLLMENT'] . '&t=' . $_GET['t'] . '" title="' . PDF . '" class="btn pdf-color btn-circle"><i class="mdi mdi-file-pdf"></i> </a>';

	if ($SEARCH_PAST_STUDENT == "true") {
	}

	if ($_SESSION['ADMIN_PK_USER'] > 0 && $row['LOGIN_CREATED'] == 1) {
		$str .= '&nbsp;<a href="student_login?id=' . $row['PK_STUDENT_MASTER'] . '" title="Login" class="btn btn-info btn-circle"><i class="mdi mdi-login-variant"></i></a>';
	}

	$row['ACTION'] = $str;


	$row['SELECT'] = '<input type="checkbox" name="CHK_PK_STUDENT_MASTER[]" id="CHK_PK_STUDENT_MASTER_' . $row['PK_STUDENT_MASTER'] . '" value="' . $row['PK_STUDENT_MASTER'] . '-' . $row['PK_STUDENT_ENROLLMENT'] . '" onchange="show_bulk(\'' . $_GET['t'] . '\')" >';


	/*if($_GET['t'] == 1 || $_GET['t'] == 2 || $_GET['t'] == 3){
		$row['BULK_DELETE'] = '<input type="checkbox" name="CHK_BULK_DELETE[]" id="CHK_BULK_DELETE_'.$row['PK_STUDENT_MASTER'].'" value="'.$row['PK_STUDENT_MASTER'].'" onchange="show_delete_button()" >';
	}*/

	array_push($items, $row);
}
$result["rows"] = $items;

echo json_encode($result);
