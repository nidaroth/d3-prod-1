<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10; 
$sort = isset($_POST['sort']) && $_POST['sort'] != 'CAMPUS' ? strval($_POST['sort']) : 'S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER';  
$sort = $_POST['sort'] == 'CAMPUS' ? 'CAMPUS_CODES' : $sort;  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " S_TUITION_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
	
if($SEARCH != '')
	$where .= " AND (BATCH_NO like '%$SEARCH%' OR BATCH_STATUS like '%$SEARCH%')";
#Additional filters 
if(isset($_REQUEST['PK_CAMPUS_IDS_DRP'])){
	$multiple_camp_flag = false;
	if(!empty($_REQUEST['PK_CAMPUS_IDS_DRP'])){
		$where .= " AND (";
		foreach ($_REQUEST['PK_CAMPUS_IDS_DRP'] as $PK_CAMPUS_IDS) {
			# code...
			if(!$multiple_camp_flag){
				$where .= " FIND_IN_SET($PK_CAMPUS_IDS , TUITION_BATCH_PK_CAMPUS ) ";
			}else{
				$where .= " OR FIND_IN_SET($PK_CAMPUS_IDS ,TUITION_BATCH_PK_CAMPUS ) ";
			}
			
			$multiple_camp_flag = true;
		} 
		$where .= " ) ";
	}
}
$PK_BATCH_STATUS = $_REQUEST['PK_BATCH_STATUS'];
$BATCH_START_DATE = $_REQUEST['BATCH_START_DATE'];
$BATCH_END_DATE = $_REQUEST['BATCH_END_DATE'];
$POSTED_START_DATE = $_REQUEST['POSTED_START_DATE'];
$POSTED_END_DATE = $_REQUEST['POSTED_END_DATE'];

if($PK_BATCH_STATUS != ''){
	$where .= " AND S_TUITION_BATCH_MASTER.PK_BATCH_STATUS IN ($PK_BATCH_STATUS) ";
}
if($BATCH_START_DATE != ''){
	$where .= " AND TRANS_DATE >= '".date('Y-m-d', strtotime($BATCH_START_DATE))."' ";
}
if($BATCH_END_DATE != ''){
	$where .= " AND TRANS_DATE <= '".date('Y-m-d', strtotime($BATCH_END_DATE))."' ";
}
if($POSTED_START_DATE != ''){
	$where .= " AND POSTED_DATE >= '".date('Y-m-d', strtotime($POSTED_START_DATE))."' "; 
}
if($POSTED_END_DATE != ''){
	$where .= " AND POSTED_DATE <= '".date('Y-m-d', strtotime($POSTED_END_DATE))."' ";
} 
	
$rs = mysql_query("SELECT DISTINCT(S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER) FROM S_TUITION_BATCH_MASTER LEFT JOIN S_TUITION_BATCH_DETAIL ON S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_MASTER = S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER LEFT JOIN M_BATCH_STATUS On M_BATCH_STATUS.PK_BATCH_STATUS = S_TUITION_BATCH_MASTER.PK_BATCH_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER =S_TUITION_BATCH_MASTER.PK_TERM_MASTER WHERE " . $where. " GROUP BY S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
// $query = "SELECT S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER,TRANS_DATE, BATCH_NO, POSTED_DATE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS TERM_MASTER ,BATCH_STATUS, IF(TYPE = 1,'Program', IF(TYPE = 2,'Course',IF(TYPE = 7,'Estimated Fees By Program', IF(TYPE = 9,'Estimated Fees By Student','')))) AS TYPE, S_TUITION_BATCH_MASTER.PK_BATCH_STATUS, TUITION_BATCH_PK_CAMPUS, SUM(AMOUNT) as DEBIT FROM S_TUITION_BATCH_MASTER LEFT JOIN S_TUITION_BATCH_DETAIL ON S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_MASTER = S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER LEFT JOIN M_BATCH_STATUS On M_BATCH_STATUS.PK_BATCH_STATUS = S_TUITION_BATCH_MASTER.PK_BATCH_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_TUITION_BATCH_MASTER.PK_TERM_MASTER WHERE " . $where ." GROUP BY S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER  order by $sort $order " ;
$query = "SELECT 
S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER,
TRANS_DATE,
BATCH_NO,
POSTED_DATE,
IF(BEGIN_DATE = '0000-00-00', '', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y')) AS TERM_MASTER,
BATCH_STATUS,
IF(
	TYPE = 1, 'Program',
	IF(TYPE = 2, 'Course', IF(TYPE = 7, 'Estimated Fees By Program', IF(TYPE = 9, 'Estimated Fees By Student', '')))
) AS TYPE,
S_TUITION_BATCH_MASTER.PK_BATCH_STATUS,
TUITION_BATCH_PK_CAMPUS,
SUM(AMOUNT) as DEBIT,
GROUP_CONCAT(S_CAMPUS.CAMPUS_CODE ORDER BY S_CAMPUS.CAMPUS_CODE ASC SEPARATOR ', ') AS CAMPUS_CODES
FROM 
S_TUITION_BATCH_MASTER
LEFT JOIN 
S_TUITION_BATCH_DETAIL ON S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_MASTER = S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER
LEFT JOIN 
M_BATCH_STATUS ON M_BATCH_STATUS.PK_BATCH_STATUS = S_TUITION_BATCH_MASTER.PK_BATCH_STATUS
LEFT JOIN 
S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_TUITION_BATCH_MASTER.PK_TERM_MASTER
LEFT JOIN 
S_CAMPUS ON FIND_IN_SET(S_CAMPUS.PK_CAMPUS, S_TUITION_BATCH_MASTER.TUITION_BATCH_PK_CAMPUS)
WHERE  
$where
GROUP BY 
S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER
ORDER BY 
$sort $order;
";

$query = "SELECT 
S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER,
TRANS_DATE,
BATCH_NO,
POSTED_DATE,
IF(BEGIN_DATE = '0000-00-00', '', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y')) AS TERM_MASTER,
BATCH_STATUS,
IF(
	TYPE = 1, 'Program',
	IF(TYPE = 2, 'Course', IF(TYPE = 7, 'Estimated Fees By Program', IF(TYPE = 9, 'Estimated Fees By Student', '')))
) AS TYPE,
S_TUITION_BATCH_MASTER.PK_BATCH_STATUS,
TUITION_BATCH_PK_CAMPUS,
SUM(AMOUNT) as DEBIT,
(
	SELECT GROUP_CONCAT(CAMPUS_CODE ORDER BY CAMPUS_CODE ASC SEPARATOR ', ')
	FROM S_CAMPUS
	WHERE FIND_IN_SET(S_CAMPUS.PK_CAMPUS, S_TUITION_BATCH_MASTER.TUITION_BATCH_PK_CAMPUS)
) AS CAMPUS_CODES
FROM 
S_TUITION_BATCH_MASTER
LEFT JOIN 
S_TUITION_BATCH_DETAIL ON S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_MASTER = S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER
LEFT JOIN 
M_BATCH_STATUS ON M_BATCH_STATUS.PK_BATCH_STATUS = S_TUITION_BATCH_MASTER.PK_BATCH_STATUS
LEFT JOIN 
S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_TUITION_BATCH_MASTER.PK_TERM_MASTER
WHERE  
 $where 
GROUP BY 
S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER
ORDER BY 
$sort $order
";


// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
$_SESSION['QUERY'] = $query; // DIAM-2158 --> 
	
$items = array();
while($row = mysql_fetch_array($rs)){

	if($row['TRANS_DATE'] != '' && $row['TRANS_DATE'] != '0000-00-00')
		$row['TRANS_DATE'] = date('m/d/Y',strtotime($row['TRANS_DATE']));
	else
		$row['TRANS_DATE'] = '';
	
	if($row['POSTED_DATE'] != '' && $row['POSTED_DATE'] != '0000-00-00')
		$row['POSTED_DATE'] = date('m/d/Y',strtotime($row['POSTED_DATE']));
	else
		$row['POSTED_DATE'] = '';	
		
	$style = "";
	if($row['PK_BATCH_STATUS'] == 3 || $row['PK_BATCH_STATUS'] == 1) {
		$style = "color:red";
	}
	
	$CAMPUS = '';
	$TUITION_BATCH_PK_CAMPUS = $row['TUITION_BATCH_PK_CAMPUS'];
	if($TUITION_BATCH_PK_CAMPUS != '') {
		$rs_camp = mysql_query("SELECT CAMPUS_CODE from S_CAMPUS WHERE PK_CAMPUS IN ($TUITION_BATCH_PK_CAMPUS) ORDER BY CAMPUS_CODE");	
				while($row_camp = mysql_fetch_array($rs_camp)){
			if($CAMPUS != '')
				$CAMPUS .= ', ';
			$CAMPUS .= $row_camp['CAMPUS_CODE'];
		}
	}
	$row['CAMPUS'] = $row['CAMPUS_CODES'];
	
	$row['BATCH_NO'] 		= '<span style="'.$style.'" >'.$row['BATCH_NO'].'</span>';
	$row['TYPE']			= '<span style="'.$style.'" >'.$row['TYPE'].'</span>';
	$row['BATCH_STATUS'] 	= '<span style="'.$style.'" >'.$row['BATCH_STATUS'].'</span>';
	$row['TRANS_DATE'] 		= '<span style="'.$style.'" >'.$row['TRANS_DATE'].'</span>';
	$row['TERM_MASTER'] 	= '<span style="'.$style.'" >'.$row['TERM_MASTER'].'</span>';
	$row['CAMPUS'] 			= '<span style="'.$style.'" >'.$row['CAMPUS'].'</span>';
	$row['POSTED_DATE'] 	= '<span style="'.$style.'" >'.$row['POSTED_DATE'].'</span>';
	$row['DEBIT'] 			= '<span style="'.$style.'" >$ '.number_format_value_checker($row['DEBIT'],2).'</span>';

	$str   = '&nbsp;<a href="tuition_batch?id='.$row['PK_TUITION_BATCH_MASTER'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	$str  .= '&nbsp;<a href="tuition_payment_pdf?id='.$row['PK_TUITION_BATCH_MASTER'].'" target="_blank" title="'.PDF.'" class="btn pdf-color btn-circle"><i class="mdi mdi-file-pdf"></i> </a>'; //Ticket # 1496
	$str  .= '&nbsp;<a href="tuition_payment_excel?id='.$row['PK_TUITION_BATCH_MASTER'].'" target="_blank" title="'.EXCEL.'" class="btn excel-color btn-circle"><i class="mdi mdi-file-excel" aria-hidden="true"></i>
	</a>'; //DIAM-2158
	if($row['PK_BATCH_STATUS'] != 2)
		$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_TUITION_BATCH_MASTER'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	else
		$str .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);