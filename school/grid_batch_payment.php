<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) && $_POST['sort'] != 'CAMPUS' ? strval($_POST['sort']) : 'PK_PAYMENT_BATCH_MASTER';  
$sort = $_POST['sort'] == 'CAMPUS' ? 'CAMPUS_CODES' : $sort;   
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " S_PAYMENT_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
#Additional filters 

// if(isset($_REQUEST['PK_CAMPUS_IDS_DRP'])){
// 	if(!empty($_REQUEST['PK_CAMPUS_IDS_DRP'])){
// 		$PK_CAMPUS_IDS_DRP = implode(',',$_REQUEST['PK_CAMPUS_IDS_DRP']);
// 		if($PK_CAMPUS_IDS_DRP != ''){
// 			$where .= " AND BATCH_PK_CAMPUS IN ($PK_CAMPUS_IDS_DRP) ";
// 		}
// 	}
// }

$PK_BATCH_STATUS = $_REQUEST['PK_BATCH_STATUS'];
$BATCH_START_DATE = $_REQUEST['BATCH_START_DATE'];
$BATCH_END_DATE = $_REQUEST['BATCH_END_DATE'];
$POSTED_START_DATE = $_REQUEST['POSTED_START_DATE'];
$POSTED_END_DATE = $_REQUEST['POSTED_END_DATE'];

if($PK_BATCH_STATUS != ''){
	$where .= " AND S_PAYMENT_BATCH_MASTER.PK_BATCH_STATUS IN ($PK_BATCH_STATUS) ";
}
if($BATCH_START_DATE != ''){
	$where .= " AND DATE_RECEIVED >= '".date('Y-m-d', strtotime($BATCH_START_DATE))."' ";
}
if($BATCH_END_DATE != ''){
	$where .= " AND DATE_RECEIVED <= '".date('Y-m-d', strtotime($BATCH_END_DATE))."' ";
}
if($POSTED_START_DATE != ''){
	$where .= " AND POSTED_DATE >= '".date('Y-m-d', strtotime($POSTED_START_DATE))."' "; 
}
if($POSTED_END_DATE != ''){
	$where .= " AND POSTED_DATE <= '".date('Y-m-d', strtotime($POSTED_END_DATE))."' ";
} 
if(isset($_REQUEST['PK_CAMPUS_IDS_DRP'])){
	$multiple_camp_flag = false;
	if(!empty($_REQUEST['PK_CAMPUS_IDS_DRP'])){
		$where .= " AND (";
		foreach ($_REQUEST['PK_CAMPUS_IDS_DRP'] as $PK_CAMPUS_IDS) {
			# code...
			if(!$multiple_camp_flag){
				$where .= " FIND_IN_SET($PK_CAMPUS_IDS , BATCH_PK_CAMPUS ) ";
			}else{
				$where .= " OR FIND_IN_SET($PK_CAMPUS_IDS ,BATCH_PK_CAMPUS ) ";
			}
			
			$multiple_camp_flag = true;
		} 
		$where .= " ) ";
	}
}

if($SEARCH != '')
	$where .= " AND (CHECK_NO like '%$SEARCH%' OR BATCH_NO like '%$SEARCH%' OR BATCH_STATUS like '%$SEARCH%' OR COMMENTS like '%$SEARCH%') ";
	
$rs = mysql_query("SELECT DISTINCT(PK_PAYMENT_BATCH_MASTER) FROM S_PAYMENT_BATCH_MASTER LEFT JOIN M_BATCH_STATUS On M_BATCH_STATUS.PK_BATCH_STATUS = S_PAYMENT_BATCH_MASTER.PK_BATCH_STATUS  WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
// $query = "SELECT PK_PAYMENT_BATCH_MASTER,DATE_RECEIVED,BATCH_NO,POSTED_DATE, CHECK_NO,AMOUNT,BATCH_STATUS, S_PAYMENT_BATCH_MASTER.PK_BATCH_STATUS, COMMENTS, PK_AR_LEDGER_CODE, BATCH_PK_CAMPUS FROM S_PAYMENT_BATCH_MASTER LEFT JOIN M_BATCH_STATUS On M_BATCH_STATUS.PK_BATCH_STATUS = S_PAYMENT_BATCH_MASTER.PK_BATCH_STATUS WHERE " . $where ." order by $sort $order " ;
$query = "SELECT 
PK_PAYMENT_BATCH_MASTER, 
DATE_RECEIVED, 
BATCH_NO, 
POSTED_DATE, 
CHECK_NO, 
AMOUNT, 
BATCH_STATUS, 
S_PAYMENT_BATCH_MASTER.PK_BATCH_STATUS, 
COMMENTS, 
PK_AR_LEDGER_CODE, 
BATCH_PK_CAMPUS,
GROUP_CONCAT(S_CAMPUS.CAMPUS_CODE ORDER BY S_CAMPUS.CAMPUS_CODE ASC SEPARATOR ', ') AS CAMPUS_CODES
FROM 
S_PAYMENT_BATCH_MASTER
LEFT JOIN 
M_BATCH_STATUS ON M_BATCH_STATUS.PK_BATCH_STATUS = S_PAYMENT_BATCH_MASTER.PK_BATCH_STATUS
LEFT JOIN 
S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_PAYMENT_BATCH_MASTER.BATCH_PK_CAMPUS
WHERE 
 $where 
GROUP BY 
PK_PAYMENT_BATCH_MASTER, 
DATE_RECEIVED, 
BATCH_NO, 
POSTED_DATE, 
CHECK_NO, 
AMOUNT, 
BATCH_STATUS, 
S_PAYMENT_BATCH_MASTER.PK_BATCH_STATUS, 
COMMENTS, 
PK_AR_LEDGER_CODE, 
BATCH_PK_CAMPUS
ORDER BY 
$sort $order
";
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
$_SESSION['QUERY'] = $query; // DIAM-2158 --> 
	
$items = array();
while($row = mysql_fetch_array($rs)){

	/*$PK_AR_LEDGER_CODE = $row['PK_AR_LEDGER_CODE'];
	
	$CODE = '';
	$rs_led = mysql_query("SELECT CODE from M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE) ");	
	while($row_led = mysql_fetch_array($rs_led)){
		if($CODE != '')
			$CODE .= ', ';
		$CODE .= $row_led['CODE'];
	}
	$row['CODE'] = $CODE;*/
	
	$CODE = '';
	$res_led = $db->Execute("SELECT CODE FROM S_PAYMENT_BATCH_DETAIL, M_AR_LEDGER_CODE, S_STUDENT_DISBURSEMENT WHERE PK_PAYMENT_BATCH_MASTER = '$row[PK_PAYMENT_BATCH_MASTER]' AND S_PAYMENT_BATCH_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT = S_PAYMENT_BATCH_DETAIL.PK_STUDENT_DISBURSEMENT GROUP BY S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE ORDER BY CODE ASC "); 
	while (!$res_led->EOF) { 
		if($CODE != '')
			$CODE .= ', ';
		$CODE .= $res_led->fields['CODE'];
		
		$res_led->MoveNext();
	}
	$row['CODE'] = $CODE; // DIAM-2158 --> 
	
	$CAMPUS = '';
	$BATCH_PK_CAMPUS = $row['BATCH_PK_CAMPUS'];
	
	if($BATCH_PK_CAMPUS != '') {
		$rs_camp = mysql_query("SELECT CAMPUS_CODE from S_CAMPUS WHERE PK_CAMPUS IN ($BATCH_PK_CAMPUS) ORDER BY CAMPUS_CODE ASC ");	
		while($row_camp = mysql_fetch_array($rs_camp)){
			if($CAMPUS != '')
				$CAMPUS .= ', ';
			$CAMPUS .= $row_camp['CAMPUS_CODE'];
		}
	}
	$row['CAMPUS'] = $row['CAMPUS_CODES'];
	
	if($row['DATE_RECEIVED'] != '' && $row['DATE_RECEIVED'] != '0000-00-00')
		$row['DATE_RECEIVED'] = date('m/d/Y',strtotime($row['DATE_RECEIVED']));
	else
		$row['DATE_RECEIVED'] = '';
		
	if($row['POSTED_DATE'] != '' && $row['POSTED_DATE'] != '0000-00-00')
		$row['POSTED_DATE'] = date('m/d/Y',strtotime($row['POSTED_DATE']));
	else
		$row['POSTED_DATE'] = '';
		
	$style = "";
	if($row['PK_BATCH_STATUS'] == 3 || $row['PK_BATCH_STATUS'] == 1) {
		$style = "color:red";
	}
	
	$row['BATCH_NO'] 		= '<span style="'.$style.'" >'.$row['BATCH_NO'].'</span>';
	$row['BATCH_STATUS']	= '<span style="'.$style.'" >'.$row['BATCH_STATUS'].'</span>';
	$row['DATE_RECEIVED'] 	= '<span style="'.$style.'" >'.$row['DATE_RECEIVED'].'</span>';
	$row['CODE'] 			= '<span style="'.$style.'" >'.$row['CODE'].'</span>';
	$row['CHECK_NO'] 		= '<span style="'.$style.'" >'.$row['CHECK_NO'].'</span>';
	$row['AMOUNT']  		= '<span style="'.$style.'" >$ '.number_format_value_checker($row['AMOUNT'], 2).'</span>';

	$str  = '&nbsp;<a href="batch_payment?id='.$row['PK_PAYMENT_BATCH_MASTER'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	$str  .= '&nbsp;<a href="batch_payment_pdf?id='.$row['PK_PAYMENT_BATCH_MASTER'].'" target="_blank" title="'.PDF.'" class="btn pdf-color btn-circle"><i class="mdi mdi-file-pdf"></i> </a>'; //Ticket # 1495
	$str  .= '&nbsp;<a href="batch_payment_excel?id='.$row['PK_PAYMENT_BATCH_MASTER'].'" target="_blank" title="'.EXCEL.'" class="btn excel-color btn-circle"><i class="mdi mdi-file-excel" aria-hidden="true"></i>
	</a>';  
	
	if($row['PK_BATCH_STATUS'] != 2)
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_PAYMENT_BATCH_MASTER'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	else {
		$str .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
	// print_r($items);exit;
}
$result["rows"] = $items;
echo json_encode($result);