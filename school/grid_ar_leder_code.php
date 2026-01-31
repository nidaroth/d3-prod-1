<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'ACTIVE DESC, CODE ASC';  
$order = isset($_POST['order']) ? strval($_POST['order']) : '';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$TYPE	= isset($_REQUEST['TYPE']) ? mysql_real_escape_string($_REQUEST['TYPE']) : '';

$ACTIVE			= isset($_REQUEST['ACTIVE']) ? mysql_real_escape_string($_REQUEST['ACTIVE']) : '';
$INVOICE		= isset($_REQUEST['INVOICE']) ? mysql_real_escape_string($_REQUEST['INVOICE']) : '';
$AWARD_LETTER	= isset($_REQUEST['OFFER_LETTER']) ? mysql_real_escape_string($_REQUEST['OFFER_LETTER']) : '';
$TITLE_IV		= isset($_REQUEST['TITLE_IV']) ? mysql_real_escape_string($_REQUEST['TITLE_IV']) : '';
$QUICK_PAYMENT	= isset($_REQUEST['QUICK_PAYMENT']) ? mysql_real_escape_string($_REQUEST['QUICK_PAYMENT']) : '';

$DIAMOND_PAY	= isset($_REQUEST['DIAMOND_PAY']) ? mysql_real_escape_string($_REQUEST['DIAMOND_PAY']) : '';

$offset = ($page-1)*$rows;
	
$result = array();
$where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	
if($SEARCH != '')
	$where .= " AND (LEDGER_DESCRIPTION  like '%$SEARCH%' OR CODE like '%$SEARCH%' OR LEDGER_DESCRIPTION like '%$SEARCH%' OR INVOICE_DESCRIPTION like '%$SEARCH%' OR GL_CODE_DEBIT like '%$SEARCH%' OR GL_CODE_CREDIT like '%$SEARCH%')";
	
$sub_where = "";
if($ACTIVE == "true") {
	if($sub_where != '')
		$sub_where .= " OR ";
	$sub_where .= " ACTIVE = '1' ";
}
if($INVOICE == "true") {
	if($sub_where != '')
		$sub_where .= " OR ";
	$sub_where .= "  INVOICE = '1' ";
}
if($AWARD_LETTER == "true") {
	if($sub_where != '')
		$sub_where .= " OR ";
	$sub_where .= "  AWARD_LETTER = '1' ";
}
if($TITLE_IV == "true") {
	if($sub_where != '')
		$sub_where .= " OR ";
	$sub_where .= "  TITLE_IV = '1' ";
}
if($QUICK_PAYMENT == "true") {
	if($sub_where != '')
		$sub_where .= " OR ";
	$sub_where .= "  QUICK_PAYMENT = '1' ";	
}
if($DIAMOND_PAY == "true") {
	if($sub_where != '')
		$sub_where .= " OR ";
	$sub_where .= "  DIAMOND_PAY = '1' ";	
}

if($sub_where != '')
	$where .= " AND (".$sub_where.") ";

if($TYPE != '')	
	$where .= " AND TYPE = '$TYPE' ";

$rs = mysql_query("SELECT DISTINCT(PK_AR_LEDGER_CODE) FROM M_AR_LEDGER_CODE WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION,LEDGER_DESCRIPTION,INVOICE_DESCRIPTION,GL_CODE_DEBIT,GL_CODE_CREDIT, IF(AWARD_LETTER = 1, 'Yes', 'No') AS AWARD_LETTER_1, IF(INVOICE = 1, 'Yes', 'No') AS INVOICE_1,IF(TITLE_IV = 1, 'Yes', 'No') AS TITLE_IV_1, IF(TYPE = 1, 'Award', IF(TYPE = 2,	 'Fee', '')) AS TYPE, IF(QUICK_PAYMENT = 1, 'Yes', 'No') AS QUICK_PAYMENT, IF(DIAMOND_PAY = 1, 'Yes', 'No') AS DIAMOND_PAY,  IF(ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE_1 FROM M_AR_LEDGER_CODE WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	

$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="ar_leder_code?id='.$row['PK_AR_LEDGER_CODE'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	/*$res_check1 = $db->Execute("select PK_CAMPUS_PROGRAM_AWARD from M_CAMPUS_PROGRAM_AWARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$row[PK_AR_LEDGER_CODE]' ");
	$res_check2 = $db->Execute("select PK_CAMPUS_PROGRAM_FEE from M_CAMPUS_PROGRAM_FEE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$row[PK_AR_LEDGER_CODE]' ");
	$res_check3 = $db->Execute("select PK_COURSE_FEE from S_COURSE_FEE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$row[PK_AR_LEDGER_CODE]' ");
	$res_check4 = $db->Execute("select PK_MISC_BATCH_DETAIL from S_MISC_BATCH_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$row[PK_AR_LEDGER_CODE]' ");
	$res_check5 = $db->Execute("select PK_PAYMENT_BATCH_MASTER from S_PAYMENT_BATCH_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE like '%$row[PK_AR_LEDGER_CODE]%' ");
	$res_check6 = $db->Execute("select PK_STUDENT_APPROVED_AWARD_SUMMARY from S_STUDENT_APPROVED_AWARD_SUMMARY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$row[PK_AR_LEDGER_CODE]' ");
	$res_check7 = $db->Execute("select PK_STUDENT_AWARD from S_STUDENT_AWARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$row[PK_AR_LEDGER_CODE]' ");
	$res_check8 = $db->Execute("select PK_STUDENT_DISBURSEMENT from S_STUDENT_DISBURSEMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$row[PK_AR_LEDGER_CODE]' ");
	$res_check9 = $db->Execute("select PK_STUDENT_FEE_BUDGET from S_STUDENT_FEE_BUDGET WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$row[PK_AR_LEDGER_CODE]' ");
	$res_check10 = $db->Execute("select PK_STUDENT_LEDGER from S_STUDENT_LEDGER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$row[PK_AR_LEDGER_CODE]' ");
	$res_check11 = $db->Execute("select PK_TUITION_BATCH_DETAIL from S_TUITION_BATCH_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$row[PK_AR_LEDGER_CODE]' ");
	
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 && $res_check4->RecordCount() == 0 && $res_check5->RecordCount() == 0 && $res_check6->RecordCount() == 0 && $res_check7->RecordCount() == 0 && $res_check8->RecordCount() == 0 && $res_check9->RecordCount() == 0 && $res_check10->RecordCount() == 0 && $res_check11->RecordCount() == 0) */
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_AR_LEDGER_CODE'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);