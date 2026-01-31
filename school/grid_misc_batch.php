<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) && $_POST['sort'] != 'CAMPUS' ? strval($_POST['sort']) : 'PK_MISC_BATCH_MASTER';  
$sort = $_POST['sort'] == 'CAMPUS' ? 'CAMPUS_CODES' : $sort ;  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " S_MISC_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
// if(isset($_REQUEST['PK_CAMPUS_IDS_DRP'])){
// 	if(!empty($_REQUEST['PK_CAMPUS_IDS_DRP'])){
// 		$PK_CAMPUS_IDS_DRP = implode(',',$_REQUEST['PK_CAMPUS_IDS_DRP']);
// 		if($PK_CAMPUS_IDS_DRP != ''){
// 			$where .= " AND MISC_BATCH_PK_CAMPUS IN ($PK_CAMPUS_IDS_DRP) ";
// 		}
// 	}
// }

#Additional filters 

$PK_BATCH_STATUS =  $_REQUEST['PK_BATCH_STATUS'];
$BATCH_START_DATE = $_REQUEST['BATCH_START_DATE'];
$BATCH_END_DATE = $_REQUEST['BATCH_END_DATE'];
$POSTED_START_DATE = $_REQUEST['POSTED_START_DATE'];
$POSTED_END_DATE = $_REQUEST['POSTED_END_DATE'];

if($PK_BATCH_STATUS != ''){
	$where .= " AND S_MISC_BATCH_MASTER.PK_BATCH_STATUS IN ($PK_BATCH_STATUS) ";
}
if($BATCH_START_DATE != ''){
	$where .= " AND BATCH_DATE >= '".date('Y-m-d', strtotime($BATCH_START_DATE))."' ";
}
if($BATCH_END_DATE != ''){
	$where .= " AND BATCH_DATE <= '".date('Y-m-d', strtotime($BATCH_END_DATE))."' ";
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
				$where .= " FIND_IN_SET($PK_CAMPUS_IDS , MISC_BATCH_PK_CAMPUS ) ";
			}else{
				$where .= " OR FIND_IN_SET($PK_CAMPUS_IDS ,MISC_BATCH_PK_CAMPUS ) ";
			}
			
			$multiple_camp_flag = true;
		} 
		$where .= " ) ";
	}
}

if($SEARCH != '')
	$where .= " AND (DESCRIPTION like '%$SEARCH%' OR BATCH_NO like '%$SEARCH%' OR BATCH_STATUS like '%$SEARCH%')";
	
$rs = mysql_query("SELECT DISTINCT(PK_MISC_BATCH_MASTER) FROM S_MISC_BATCH_MASTER LEFT JOIN M_BATCH_STATUS On M_BATCH_STATUS.PK_BATCH_STATUS = S_MISC_BATCH_MASTER.PK_BATCH_STATUS WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);


// $query = "SELECT PK_MISC_BATCH_MASTER, BATCH_DATE, BATCH_NO, DESCRIPTION ,CREDIT, DEBIT, BATCH_STATUS, S_MISC_BATCH_MASTER.PK_BATCH_STATUS, MISC_BATCH_PK_CAMPUS, POSTED_DATE, (DEBIT - CREDIT) as BALANCE FROM S_MISC_BATCH_MASTER LEFT JOIN M_BATCH_STATUS On M_BATCH_STATUS.PK_BATCH_STATUS = S_MISC_BATCH_MASTER.PK_BATCH_STATUS WHERE " . $where ." order by $sort $order " ;
$query = 
"SELECT 
PK_MISC_BATCH_MASTER, 
BATCH_DATE, 
BATCH_NO, 
DESCRIPTION, 
CREDIT, 
DEBIT, 
BATCH_STATUS, 
S_MISC_BATCH_MASTER.PK_BATCH_STATUS, 
MISC_BATCH_PK_CAMPUS, 
POSTED_DATE, 
(S_MISC_BATCH_MASTER.DEBIT - S_MISC_BATCH_MASTER.CREDIT) as BALANCE,
GROUP_CONCAT(S_CAMPUS.CAMPUS_CODE ORDER BY S_CAMPUS.CAMPUS_CODE ASC SEPARATOR ', ') AS CAMPUS_CODES
FROM 
S_MISC_BATCH_MASTER
LEFT JOIN 
M_BATCH_STATUS ON M_BATCH_STATUS.PK_BATCH_STATUS = S_MISC_BATCH_MASTER.PK_BATCH_STATUS
LEFT JOIN 
S_CAMPUS ON FIND_IN_SET(S_CAMPUS.PK_CAMPUS, S_MISC_BATCH_MASTER.MISC_BATCH_PK_CAMPUS)
WHERE  
 $where 
GROUP BY 
S_MISC_BATCH_MASTER.PK_MISC_BATCH_MASTER, 
S_MISC_BATCH_MASTER.BATCH_DATE, 
S_MISC_BATCH_MASTER.BATCH_NO, 
S_MISC_BATCH_MASTER.DESCRIPTION, 
S_MISC_BATCH_MASTER.CREDIT, 
S_MISC_BATCH_MASTER.DEBIT, 
BATCH_STATUS, 
M_BATCH_STATUS.PK_BATCH_STATUS, 
S_MISC_BATCH_MASTER.MISC_BATCH_PK_CAMPUS, 
S_MISC_BATCH_MASTER.POSTED_DATE
ORDER BY 
$sort $order
";
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
$_SESSION['QUERY'] = $query; // DIAM-2158 --> 
	
$items = array();
while($row = mysql_fetch_array($rs)){

	if($row['BATCH_DATE'] != '' && $row['BATCH_DATE'] != '0000-00-00')
		$row['BATCH_DATE'] = date('m/d/Y',strtotime($row['BATCH_DATE']));
	else
		$row['BATCH_DATE'] = '';
		
	if($row['POSTED_DATE'] != '' && $row['POSTED_DATE'] != '0000-00-00')
		$row['POSTED_DATE'] = date('m/d/Y',strtotime($row['POSTED_DATE']));
	else
		$row['POSTED_DATE'] = '';
		
	$CAMPUS = '';
	$MISC_BATCH_PK_CAMPUS = $row['MISC_BATCH_PK_CAMPUS'];
	if($MISC_BATCH_PK_CAMPUS != '') {
		$order_campus = 'ASC';
		if($sort == 'CAMPUS' ){
			$order_campus = $order;
		}
		$rs_camp = mysql_query("SELECT CAMPUS_CODE from S_CAMPUS WHERE PK_CAMPUS IN ($MISC_BATCH_PK_CAMPUS) ORDER By CAMPUS_CODE $order_campus ");	
		while($row_camp = mysql_fetch_array($rs_camp)){
			if($CAMPUS != '')
				$CAMPUS .= ', ';
			$CAMPUS .= $row_camp['CAMPUS_CODE'];
		}
	}
	$row['CAMPUS'] = $row['CAMPUS_CODES'];

	$style = "";
	if($row['PK_BATCH_STATUS'] == 3 || $row['PK_BATCH_STATUS'] == 1) {
		$style = "color:red";
	}
	$row['BATCH_NO'] 		= '<span style="'.$style.'" >'.$row['BATCH_NO'].'</span>';
	$row['BATCH_STATUS']	= '<span style="'.$style.'" >'.$row['BATCH_STATUS'].'</span>';
	$row['BATCH_DATE'] 		= '<span style="'.$style.'" >'.$row['BATCH_DATE'].'</span>';
	$row['DESCRIPTION'] 	= '<span style="'.$style.'" >'.$row['DESCRIPTION'].'</span>';
	$row['CREDIT'] 			= '<span style="'.$style.'" >$ '.$row['CREDIT'].'</span>';
	$row['DEBIT']  			= '<span style="'.$style.'" >$ '.$row['DEBIT'].'</span>';
	$row['POSTED_DATE']  	= '<span style="'.$style.'" >'.$row['POSTED_DATE'].'</span>';
	$row['BALANCE']  		= '<span style="'.$style.'" >$ '.$row['BALANCE'].'</span>';
		
	$str  = '&nbsp;<a href="misc_batch?id='.$row['PK_MISC_BATCH_MASTER'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	$str  .= '&nbsp;<a href="misc_payment_pdf?id='.$row['PK_MISC_BATCH_MASTER'].'" target="_blank" title="'.PDF.'" class="btn pdf-color btn-circle"><i class="mdi mdi-file-pdf"></i> </a>'; //Ticket # 1494
	$str  .= '&nbsp;<a href="misc_payment_excel?id='.$row['PK_MISC_BATCH_MASTER'].'" target="_blank" title="'.EXCEL.'" class="btn excel-color btn-circle"><i class="mdi mdi-file-excel" aria-hidden="true"></i></a>'; //DIAM-2158
	if($row['PK_BATCH_STATUS'] != 2)
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_MISC_BATCH_MASTER'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	else
		$str .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);